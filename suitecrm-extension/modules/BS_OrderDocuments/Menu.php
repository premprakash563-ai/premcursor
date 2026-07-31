<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('BS_OrderDocuments', 'list', true)) {
    $module_menu[] = [
        'index.php?module=BS_OrderDocuments&action=index',
        $mod_strings['LNK_LIST'] ?? 'View Order Documents',
        'List',
        'BS_OrderDocuments',
    ];
}
if (ACLController::checkAccess('BS_OrderDocuments', 'edit', true)) {
    $module_menu[] = [
        'index.php?module=BS_OrderDocuments&action=EditView&return_module=BS_OrderDocuments&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'] ?? 'Create Order Document',
        'Create',
        'BS_OrderDocuments',
    ];
}
