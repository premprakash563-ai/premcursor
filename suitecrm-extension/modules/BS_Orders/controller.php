<?php
/**
 * BS_Orders controller — reassign action for admins.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/Controller/SugarController.php';

class BS_OrdersController extends SugarController
{
    /**
     * Admin manual reassign: ?module=BS_Orders&action=reassign&record=ID&user_id=USER
     */
    public function action_reassign()
    {
        global $current_user;

        if (empty($current_user) || empty($current_user->id)) {
            sugar_die('Not authorized');
        }
        if (empty($current_user->is_admin)) {
            ACLController::displayNoAccess(true);
            sugar_cleanup(true);
        }

        $record = isset($_REQUEST['record']) ? $_REQUEST['record'] : '';
        $userId = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';

        if (empty($record) || empty($userId)) {
            SugarApplication::appendErrorMessage('Order and employee are required for reassignment.');
            SugarApplication::redirect('index.php?module=BS_Orders&action=DetailView&record=' . urlencode($record));
            return;
        }

        $order = BeanFactory::getBean('BS_Orders', $record);
        if (empty($order) || empty($order->id)) {
            sugar_die('Order not found');
        }

        require_once 'custom/include/BS/Assignment/BS_AssignmentEngine.php';
        $engine = new BS_AssignmentEngine();
        $engine->reassign($order, $userId, 'manual');

        SugarApplication::appendSuccessMessage('Order reassigned successfully.');
        SugarApplication::redirect('index.php?module=BS_Orders&action=DetailView&record=' . urlencode($record));
    }
}
