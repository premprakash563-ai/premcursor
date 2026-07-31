<?php
/**
 * Round-robin employee assignment for BS_Orders.
 * Max N new enquiries per employee per calendar day (default 10).
 * Skips Inactive, Offline, On Leave users.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_AssignmentEngine
{
    /** @var int */
    protected $maxPerDay;

    public function __construct($maxPerDay = null)
    {
        $cfg = isset($GLOBALS['sugar_config']['bs_assignment']['max_new_enquiries_per_day'])
            ? (int) $GLOBALS['sugar_config']['bs_assignment']['max_new_enquiries_per_day']
            : 10;
        $this->maxPerDay = $maxPerDay !== null ? (int) $maxPerDay : $cfg;
        if ($this->maxPerDay < 1) {
            $this->maxPerDay = 10;
        }
    }

    /**
     * Assign an order to the next eligible employee.
     *
     * @param SugarBean $order BS_Orders bean
     * @return string|null assigned user id or null if queued unassigned
     */
    public function assignOrder($order)
    {
        if (empty($order) || empty($order->id)) {
            return null;
        }
        if (!empty($order->assigned_user_id)) {
            return $order->assigned_user_id;
        }

        $candidates = $this->getEligibleEmployees();
        foreach ($candidates as $userId) {
            $count = $this->countTodaysNewEnquiries($userId);
            $cap = $this->getUserCap($userId);
            if ($count >= $cap) {
                continue;
            }
            $this->applyAssignment($order, $userId, 'auto_rr', $count);
            return $userId;
        }

        $this->logUnassigned($order->id);
        return null;
    }

    /**
     * Manual reassignment by admin.
     */
    public function reassign($order, $userId, $reason = 'manual')
    {
        if (empty($order) || empty($userId)) {
            return false;
        }
        $count = $this->countTodaysNewEnquiries($userId);
        $this->applyAssignment($order, $userId, $reason, $count);
        return true;
    }

    /**
     * Workload snapshot for admin dashlet.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getWorkloadReport()
    {
        global $db;
        $today = $GLOBALS['timedate']->nowDbDate();
        $sql = "
            SELECT u.id, u.user_name, u.first_name, u.last_name,
                   COALESCE(uc.availability_c, 'online') AS availability,
                   (
                     SELECT COUNT(*) FROM bs_orders o
                     WHERE o.assigned_user_id = u.id
                       AND o.deleted = 0
                       AND o.is_new_enquiry = 1
                       AND DATE(o.date_entered) = " . $db->quoted($today) . "
                   ) AS today_new,
                   (
                     SELECT COUNT(*) FROM bs_orders o2
                     WHERE o2.assigned_user_id = u.id
                       AND o2.deleted = 0
                       AND o2.status NOT IN ('delivered','cancelled')
                   ) AS open_projects
            FROM users u
            LEFT JOIN users_cstm uc ON uc.id_c = u.id
            WHERE u.deleted = 0 AND u.status = 'Active' AND u.employee_status = 'Active'
              AND (u.is_admin = 0 OR u.is_admin IS NULL)
            ORDER BY today_new ASC, u.last_name ASC
        ";
        $rows = [];
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    protected function getEligibleEmployees()
    {
        global $db;
        $sql = "
            SELECT u.id
            FROM users u
            LEFT JOIN users_cstm uc ON uc.id_c = u.id
            WHERE u.deleted = 0
              AND u.status = 'Active'
              AND u.employee_status = 'Active'
              AND (u.is_admin = 0 OR u.is_admin IS NULL)
              AND COALESCE(uc.availability_c, 'online') NOT IN ('offline', 'on_leave')
              AND NOT EXISTS (
                  SELECT 1 FROM bs_leave l
                  WHERE l.assigned_user_id = u.id
                    AND l.deleted = 0
                    AND l.status = 'approved'
                    AND CURDATE() BETWEEN l.date_start AND l.date_end
              )
            ORDER BY COALESCE(uc.last_assigned_at_c, '1970-01-01') ASC, u.id ASC
        ";
        $ids = [];
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $ids[] = $row['id'];
        }
        return $ids;
    }

    protected function countTodaysNewEnquiries($userId)
    {
        global $db;
        $today = $GLOBALS['timedate']->nowDbDate();
        $sql = sprintf(
            "SELECT COUNT(*) AS c FROM bs_orders
             WHERE deleted = 0 AND assigned_user_id = %s
               AND is_new_enquiry = 1
               AND DATE(date_entered) = %s",
            $db->quoted($userId),
            $db->quoted($today)
        );
        $row = $db->fetchByAssoc($db->query($sql));
        return (int) ($row['c'] ?? 0);
    }

    protected function getUserCap($userId)
    {
        global $db;
        $sql = 'SELECT max_enquiries_override_c FROM users_cstm WHERE id_c = ' . $db->quoted($userId);
        $row = $db->fetchByAssoc($db->query($sql));
        if (!empty($row['max_enquiries_override_c']) && (int) $row['max_enquiries_override_c'] > 0) {
            return (int) $row['max_enquiries_override_c'];
        }
        return $this->maxPerDay;
    }

    protected function applyAssignment($order, $userId, $reason, $dailyCount)
    {
        global $db;

        $order->assigned_user_id = $userId;
        $order->assigned_at = $GLOBALS['timedate']->nowDb();
        if (!isset($order->is_new_enquiry) || $order->is_new_enquiry === '' || $order->is_new_enquiry === null) {
            $order->is_new_enquiry = 1;
        }
        $order->save();

        $now = $GLOBALS['timedate']->nowDb();
        $db->query(sprintf(
            "UPDATE users_cstm SET last_assigned_at_c = %s WHERE id_c = %s",
            $db->quoted($now),
            $db->quoted($userId)
        ));

        $id = create_guid();
        $db->query(sprintf(
            "INSERT INTO bs_assignment_log
             (id, order_id, user_id, reason, daily_count_at_assign, date_entered, deleted)
             VALUES (%s, %s, %s, %s, %d, %s, 0)",
            $db->quoted($id),
            $db->quoted($order->id),
            $db->quoted($userId),
            $db->quoted($reason),
            (int) $dailyCount,
            $db->quoted($now)
        ));

        if (class_exists('BS_NotificationService')) {
            $notifier = new BS_NotificationService();
            $notifier->orderAssigned($order, $userId);
        }
    }

    protected function logUnassigned($orderId)
    {
        $GLOBALS['log']->fatal('[BS_AssignmentEngine] No eligible employee for order ' . $orderId);
        if (class_exists('BS_NotificationService')) {
            $notifier = new BS_NotificationService();
            $notifier->unassignedQueueAlert($orderId);
        }
    }
}
