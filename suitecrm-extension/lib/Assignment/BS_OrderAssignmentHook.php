<?php
/**
 * Logic hook: auto-assign on new BS_Orders when unassigned.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/include/BS/Assignment/BS_AssignmentEngine.php';

class BS_OrderAssignmentHook
{
    /**
     * after_save on BS_Orders
     */
    public function autoAssign($bean, $event, $arguments)
    {
        if (empty($bean->id) || !empty($bean->deleted)) {
            return;
        }
        // Only on create (or explicit flag)
        $isNew = empty($arguments['isUpdate']) || !empty($bean->bs_force_auto_assign);
        if (!$isNew) {
            return;
        }
        if (!empty($bean->assigned_user_id)) {
            return;
        }

        $engine = new BS_AssignmentEngine();
        $engine->assignOrder($bean);
    }
}
