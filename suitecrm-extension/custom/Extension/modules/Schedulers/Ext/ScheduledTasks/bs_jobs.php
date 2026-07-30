<?php
/**
 * SuiteCRM Schedulers — Phase 2 automation jobs.
 * Path after install: custom/Extension/modules/Schedulers/Ext/ScheduledTasks/bs_jobs.php
 */

$job_strings[] = 'bsDocumentReminders';
$job_strings[] = 'bsIdleOrderEscalation';

/**
 * Remind for orders with pending/rejected documents older than N days.
 */
function bsDocumentReminders()
{
    global $db, $sugar_config;
    $days = isset($sugar_config['bs_automation']['document_reminder_days'])
        ? (int) $sugar_config['bs_automation']['document_reminder_days']
        : 2;
    if ($days < 1) {
        $days = 2;
    }

    require_once 'custom/include/BS/Notifications/BS_NotificationService.php';
    $notifier = new BS_NotificationService();

    $sql = sprintf(
        "SELECT DISTINCT o.id
         FROM bs_orders o
         INNER JOIN bs_order_documents d ON d.order_id = o.id AND d.deleted = 0
         WHERE o.deleted = 0
           AND o.status NOT IN ('delivered','cancelled','completed')
           AND d.status IN ('uploaded','rejected','reupload_requested')
           AND d.date_entered <= DATE_SUB(NOW(), INTERVAL %d DAY)
         LIMIT 100",
        $days
    );
    $res = $db->query($sql);
    $count = 0;
    while ($row = $db->fetchByAssoc($res)) {
        $order = BeanFactory::getBean('BS_Orders', $row['id']);
        if (!empty($order) && !empty($order->id)) {
            $notifier->documentsRequired(
                $order,
                'Reminder: documents are still pending for order ' . $order->name
            );
            $count++;
        }
    }
    return true;
}

/**
 * Escalate idle orders with no status change for N days.
 */
function bsIdleOrderEscalation()
{
    global $db, $sugar_config;
    $days = isset($sugar_config['bs_automation']['idle_escalation_days'])
        ? (int) $sugar_config['bs_automation']['idle_escalation_days']
        : 3;
    if ($days < 1) {
        $days = 3;
    }

    require_once 'custom/include/BS/Notifications/BS_NotificationService.php';
    $notifier = new BS_NotificationService();

    $sql = sprintf(
        "SELECT id, name, assigned_user_id, date_modified
         FROM bs_orders
         WHERE deleted = 0
           AND status NOT IN ('delivered','cancelled','completed','certificate_ready')
           AND date_modified <= DATE_SUB(NOW(), INTERVAL %d DAY)
         LIMIT 100",
        $days
    );
    $res = $db->query($sql);
    while ($row = $db->fetchByAssoc($res)) {
        $msg = sprintf(
            'Escalation: order %s idle for %d+ days (status unchanged).',
            $row['name'],
            $days
        );
        if (!empty($row['assigned_user_id'])) {
            $notifier->notifyUserPublic($row['assigned_user_id'], 'idle_escalation', [
                'order_id' => $row['id'],
                'order_name' => $row['name'],
                'message' => $msg,
            ]);
        }
        foreach ($notifier->getAdminUserIdsPublic() as $adminId) {
            $notifier->notifyUserPublic($adminId, 'idle_escalation', [
                'order_id' => $row['id'],
                'order_name' => $row['name'],
                'message' => $msg,
            ]);
        }
    }
    return true;
}
