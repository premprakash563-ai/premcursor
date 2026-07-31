<?php
/**
 * Track status changes + clear "new enquiry" after first progress move.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_OrderStatusHook
{
    /**
     * before_save — capture old status
     */
    public function captureOldStatus($bean, $event, $arguments)
    {
        if (empty($bean->id)) {
            $bean->bs_old_status = null;
            return;
        }
        $existing = BeanFactory::getBean('BS_Orders', $bean->id);
        $bean->bs_old_status = $existing ? $existing->status : null;
    }

    /**
     * after_save — write BS_StatusHistory when status changes
     */
    public function logStatusChange($bean, $event, $arguments)
    {
        if (empty($bean->id) || empty($bean->status)) {
            return;
        }

        $old = isset($bean->bs_old_status) ? $bean->bs_old_status : null;
        $new = $bean->status;

        if ($old === $new) {
            return;
        }

        // First real progress beyond submitted → no longer counts as "new enquiry" for daily cap
        if (!empty($old) && $old !== $new && !empty($bean->is_new_enquiry)
            && $new !== 'application_submitted' && $new !== 'payment_pending') {
            $bean->is_new_enquiry = 0;
            // Avoid recursion: direct DB update
            global $db;
            $db->query(sprintf(
                'UPDATE bs_orders SET is_new_enquiry = 0 WHERE id = %s',
                $db->quoted($bean->id)
            ));
        }

        $history = BeanFactory::newBean('BS_StatusHistory');
        if (empty($history)) {
            // Fallback insert if module not yet repaired
            $this->insertHistoryRow($bean, $old, $new);
            return;
        }

        $history->name = ($bean->name ?: $bean->id) . ': ' . ($old ?: 'new') . ' → ' . $new;
        $history->order_id = $bean->id;
        $history->from_status = $old;
        $history->to_status = $new;
        $history->assigned_user_id = $bean->assigned_user_id;
        $history->note = '';
        $history->save();

        if (file_exists('custom/include/BS/Notifications/BS_NotificationService.php')) {
            require_once 'custom/include/BS/Notifications/BS_NotificationService.php';
            if (class_exists('BS_NotificationService')) {
                $notifier = new BS_NotificationService();
                $notifier->statusChanged($bean, $old ?: '', $new);
            }
        }
    }

    protected function insertHistoryRow($bean, $old, $new)
    {
        global $db, $current_user;
        if (!$this->tableExists('bs_status_history')) {
            return;
        }
        $id = create_guid();
        $now = $GLOBALS['timedate']->nowDb();
        $uid = !empty($current_user->id) ? $current_user->id : '';
        $db->query(sprintf(
            "INSERT INTO bs_status_history
             (id, name, date_entered, date_modified, modified_user_id, created_by, deleted,
              assigned_user_id, order_id, from_status, to_status, note)
             VALUES (%s, %s, %s, %s, %s, %s, 0, %s, %s, %s, %s, '')",
            $db->quoted($id),
            $db->quoted(($bean->name ?: $bean->id) . ': ' . ($old ?: 'new') . ' → ' . $new),
            $db->quoted($now),
            $db->quoted($now),
            $db->quoted($uid),
            $db->quoted($uid),
            $db->quoted($bean->assigned_user_id ?: $uid),
            $db->quoted($bean->id),
            $db->quoted($old),
            $db->quoted($new)
        ));
    }

    protected function tableExists($table)
    {
        global $db;
        $res = $db->query('SHOW TABLES LIKE ' . $db->quoted($table));
        return (bool) $db->fetchByAssoc($res);
    }
}
