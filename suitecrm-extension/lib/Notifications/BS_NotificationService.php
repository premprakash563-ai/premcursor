<?php
/**
 * Notification facade — Email + In-App; SMS/WhatsApp adapters optional.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_NotificationService
{
    public function orderAssigned($order, $userId)
    {
        $this->notifyUser($userId, 'order_assigned', [
            'order_id' => $order->id,
            'order_name' => $order->name,
            'message' => 'New project assigned: ' . $order->name,
        ]);
        if (!empty($order->contact_id)) {
            $this->notifyContact($order->contact_id, 'employee_assigned', [
                'order_id' => $order->id,
                'order_name' => $order->name,
                'message' => 'An expert has been assigned to your order ' . $order->name,
            ]);
        }
    }

    public function unassignedQueueAlert($orderId)
    {
        foreach ($this->getAdminUserIds() as $adminId) {
            $this->notifyUser($adminId, 'unassigned_order', [
                'order_id' => $orderId,
                'message' => 'Order could not be auto-assigned. Manual assignment required.',
            ]);
        }
    }

    public function statusChanged($order, $from, $to)
    {
        if (!empty($order->contact_id)) {
            $this->notifyContact($order->contact_id, 'status_changed', [
                'order_id' => $order->id,
                'from' => $from,
                'to' => $to,
                'message' => sprintf('Order %s status: %s → %s', $order->name, $from, $to),
            ]);
        }
        if (!empty($order->assigned_user_id)) {
            $this->notifyUser($order->assigned_user_id, 'status_changed', [
                'order_id' => $order->id,
                'from' => $from,
                'to' => $to,
                'message' => sprintf('Order %s status updated to %s', $order->name, $to),
            ]);
        }
    }

    public function documentsRequired($order, $note = '')
    {
        if (empty($order->contact_id)) {
            return;
        }
        $this->notifyContact($order->contact_id, 'documents_required', [
            'order_id' => $order->id,
            'message' => $note ?: ('Additional documents required for ' . $order->name),
        ]);
        if (!empty($order->assigned_user_id)) {
            $this->notifyUser($order->assigned_user_id, 'documents_required', [
                'order_id' => $order->id,
                'message' => $note ?: ('Documents pending on order ' . $order->name),
            ]);
        }
    }

    /** Public wrappers for schedulers */
    public function notifyUserPublic($userId, $event, array $payload)
    {
        $this->notifyUser($userId, $event, $payload);
    }

    public function getAdminUserIdsPublic()
    {
        return $this->getAdminUserIds();
    }

    protected function notifyUser($userId, $event, array $payload)
    {
        $cfg = $GLOBALS['sugar_config']['bs_notifications'] ?? [];
        if (!isset($cfg['in_app']) || !empty($cfg['in_app'])) {
            $this->writeInApp('user', $userId, $event, $payload);
        }
        if (!isset($cfg['email']) || !empty($cfg['email'])) {
            $this->sendEmailToUser($userId, $event, $payload);
        }
        $this->dispatchOptionalChannels('user', $userId, $event, $payload);
    }

    protected function notifyContact($contactId, $event, array $payload)
    {
        $cfg = $GLOBALS['sugar_config']['bs_notifications'] ?? [];
        if (!isset($cfg['in_app']) || !empty($cfg['in_app'])) {
            $this->writeInApp('contact', $contactId, $event, $payload);
        }
        if (!isset($cfg['email']) || !empty($cfg['email'])) {
            $this->sendEmailToContact($contactId, $event, $payload);
        }
        $this->dispatchOptionalChannels('contact', $contactId, $event, $payload);
    }

    protected function writeInApp($recipientType, $recipientId, $event, array $payload)
    {
        global $db;
        if (!$this->tableExists('bs_notifications')) {
            return;
        }

        $id = create_guid();
        $now = $GLOBALS['timedate']->nowDb();
        $msg = $payload['message'] ?? $event;
        $orderId = $payload['order_id'] ?? '';
        $assigned = ($recipientType === 'user') ? $recipientId : '';

        // Prefer bean if module registered
        if (class_exists('BeanFactory')) {
            $bean = BeanFactory::newBean('BS_Notifications');
            if (!empty($bean)) {
                $bean->id = $id;
                $bean->new_with_id = true;
                $bean->name = substr($event . ': ' . $msg, 0, 150);
                $bean->recipient_type = $recipientType;
                $bean->recipient_id = $recipientId;
                $bean->event_code = $event;
                $bean->message = $msg;
                $bean->payload_json = json_encode($payload);
                $bean->is_read = 0;
                $bean->related_order_id = $orderId;
                $bean->assigned_user_id = $assigned;
                $bean->save();
                return;
            }
        }

        $db->query(sprintf(
            "INSERT INTO bs_notifications
             (id, name, date_entered, date_modified, deleted, assigned_user_id,
              recipient_type, recipient_id, event_code, message, payload_json, is_read, related_order_id)
             VALUES (%s, %s, %s, %s, 0, %s, %s, %s, %s, %s, %s, 0, %s)",
            $db->quoted($id),
            $db->quoted(substr($event . ': ' . $msg, 0, 150)),
            $db->quoted($now),
            $db->quoted($now),
            $db->quoted($assigned),
            $db->quoted($recipientType),
            $db->quoted($recipientId),
            $db->quoted($event),
            $db->quoted($msg),
            $db->quoted(json_encode($payload)),
            $db->quoted($orderId)
        ));
    }

    protected function sendEmailToUser($userId, $event, array $payload)
    {
        $user = BeanFactory::getBean('Users', $userId);
        if (empty($user) || empty($user->email1)) {
            return;
        }
        $this->sendMail($user->email1, $this->subjectFor($event), $payload['message'] ?? $event);
    }

    protected function sendEmailToContact($contactId, $event, array $payload)
    {
        $contact = BeanFactory::getBean('Contacts', $contactId);
        if (empty($contact) || empty($contact->email1)) {
            return;
        }
        $this->sendMail($contact->email1, $this->subjectFor($event), $payload['message'] ?? $event);
    }

    protected function sendMail($to, $subject, $body)
    {
        require_once 'include/SugarPHPMailer.php';
        $mail = new SugarPHPMailer();
        $mail->setMailerForSystem();
        $mail->From = $GLOBALS['sugar_config']['notify_fromaddress'] ?? 'noreply@example.com';
        $mail->FromName = $GLOBALS['sugar_config']['notify_fromname'] ?? 'CRM';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->IsHTML(false);
        $mail->prepForOutbound();
        $mail->AddAddress($to);
        @$mail->Send();
    }

    protected function subjectFor($event)
    {
        $map = [
            'order_assigned' => 'New project assigned',
            'employee_assigned' => 'Expert assigned to your order',
            'unassigned_order' => 'Unassigned order alert',
            'status_changed' => 'Order status updated',
            'documents_required' => 'Documents required',
            'idle_escalation' => 'Idle order escalation',
        ];
        return $map[$event] ?? ('CRM: ' . $event);
    }

    protected function dispatchOptionalChannels($type, $id, $event, array $payload)
    {
        $GLOBALS['log']->debug("[BS_NotificationService] optional channels {$type}/{$id}/{$event}");
    }

    protected function getAdminUserIds()
    {
        global $db;
        $ids = [];
        $res = $db->query("SELECT id FROM users WHERE deleted = 0 AND status = 'Active' AND is_admin = 1");
        while ($row = $db->fetchByAssoc($res)) {
            $ids[] = $row['id'];
        }
        return $ids;
    }

    protected function tableExists($table)
    {
        global $db;
        $res = $db->query('SHOW TABLES LIKE ' . $db->quoted($table));
        return (bool) $db->fetchByAssoc($res);
    }
}
