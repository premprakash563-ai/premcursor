<?php
/**
 * Document review helpers: approve / reject / request re-upload.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class BS_DocumentReviewService
{
    public function setStatus($docId, $status, $note = '')
    {
        $allowed = ['uploaded', 'approved', 'rejected', 'reupload_requested'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $doc = BeanFactory::getBean('BS_OrderDocuments', $docId);
        if (empty($doc) || empty($doc->id)) {
            return false;
        }

        global $current_user;
        $doc->status = $status;
        $doc->review_note = $note;
        $doc->reviewer_id = !empty($current_user->id) ? $current_user->id : $doc->reviewer_id;
        $doc->save();

        if (in_array($status, ['rejected', 'reupload_requested'], true) && !empty($doc->order_id)) {
            $order = BeanFactory::getBean('BS_Orders', $doc->order_id);
            if (!empty($order) && file_exists('custom/include/BS/Notifications/BS_NotificationService.php')) {
                require_once 'custom/include/BS/Notifications/BS_NotificationService.php';
                if (class_exists('BS_NotificationService')) {
                    $notifier = new BS_NotificationService();
                    $msg = $note ?: ('Document ' . ($doc->doc_code ?: $doc->name) . ' requires attention (' . $status . ')');
                    $notifier->documentsRequired($order, $msg);
                }
            }
        }

        return true;
    }
}
