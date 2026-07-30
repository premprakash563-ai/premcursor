<?php
/**
 * Register logic hooks for BS_Orders.
 * Copy to: custom/Extension/modules/BS_Orders/Ext/LogicHooks/bs_assignment.php
 */

$hook_version = 1;
$hook_array = [];

$hook_array['after_save'][] = [
    1,
    'BS Auto Assign Employee',
    'custom/include/BS/Assignment/BS_OrderAssignmentHook.php',
    'BS_OrderAssignmentHook',
    'autoAssign',
];
