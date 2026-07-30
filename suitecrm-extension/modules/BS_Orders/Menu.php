<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('BS_Orders', 'edit', true)) {
    $module_menu[] = [
        'index.php?module=BS_Orders&action=EditView&return_module=BS_Orders&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'] ?? 'Create Order',
        'Create',
        'BS_Orders',
    ];
}
if (ACLController::checkAccess('BS_Orders', 'list', true)) {
    $module_menu[] = [
        'index.php?module=BS_Orders&action=index',
        $mod_strings['LNK_LIST'] ?? 'View Orders',
        'List',
        'BS_Orders',
    ];
}
