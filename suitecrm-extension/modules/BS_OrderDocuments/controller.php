<?php
/**
 * BS_OrderDocuments controller — approve / reject / request re-upload.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class BS_OrderDocumentsController extends SugarController
{
    public function action_approve()
    {
        $this->review('approved');
    }

    public function action_reject()
    {
        $note = isset($_REQUEST['review_note']) ? $_REQUEST['review_note'] : 'Document rejected';
        $this->review('rejected', $note);
    }

    public function action_request_reupload()
    {
        $note = isset($_REQUEST['review_note']) ? $_REQUEST['review_note'] : 'Please re-upload document';
        $this->review('reupload_requested', $note);
    }

    protected function review($status, $note = '')
    {
        global $current_user;
        if (empty($current_user) || empty($current_user->id)) {
            sugar_die('Not authorized');
        }

        $record = isset($_REQUEST['record']) ? $_REQUEST['record'] : '';
        if (empty($record)) {
            sugar_die('Document id required');
        }

        $doc = BeanFactory::getBean('BS_OrderDocuments', $record);
        if (empty($doc) || empty($doc->id)) {
            sugar_die('Document not found');
        }

        // Employee may only review docs on their assigned orders (admins bypass)
        if (empty($current_user->is_admin) && !empty($doc->order_id)) {
            $order = BeanFactory::getBean('BS_Orders', $doc->order_id);
            if (empty($order) || $order->assigned_user_id !== $current_user->id) {
                ACLController::displayNoAccess(true);
                sugar_cleanup(true);
            }
        }

        require_once 'custom/include/BS/Documents/BS_DocumentReviewService.php';
        $svc = new BS_DocumentReviewService();
        $ok = $svc->setStatus($record, $status, $note);

        if ($ok) {
            SugarApplication::appendSuccessMessage('Document marked as ' . $status);
        } else {
            SugarApplication::appendErrorMessage('Could not update document status');
        }

        $return = !empty($doc->order_id)
            ? 'index.php?module=BS_Orders&action=DetailView&record=' . urlencode($doc->order_id)
            : 'index.php?module=BS_OrderDocuments&action=DetailView&record=' . urlencode($record);
        SugarApplication::redirect($return);
    }
}
