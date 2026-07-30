<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('BS_Leave', 'edit', true)) {
    $module_menu[] = [
        'index.php?module=BS_Leave&action=EditView&return_module=BS_Leave&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'] ?? 'Create Leave Request',
        'Create',
        'BS_Leave',
    ];
}
if (ACLController::checkAccess('BS_Leave', 'list', true)) {
    $module_menu[] = [
        'index.php?module=BS_Leave&action=index',
        $mod_strings['LNK_LIST'] ?? 'View Leave Requests',
        'List',
        'BS_Leave',
    ];
}
