<?php
/**
 * Logic hooks for BS_Orders — assignment + status history.
 */

$hook_version = 1;
$hook_array = [];

$hook_array['before_save'][] = [
    1,
    'BS Capture Old Status',
    'custom/include/BS/Orders/BS_OrderStatusHook.php',
    'BS_OrderStatusHook',
    'captureOldStatus',
];

$hook_array['after_save'][] = [
    1,
    'BS Auto Assign Employee',
    'custom/include/BS/Assignment/BS_OrderAssignmentHook.php',
    'BS_OrderAssignmentHook',
    'autoAssign',
];

$hook_array['after_save'][] = [
    2,
    'BS Log Status History',
    'custom/include/BS/Orders/BS_OrderStatusHook.php',
    'BS_OrderStatusHook',
    'logStatusChange',
];
