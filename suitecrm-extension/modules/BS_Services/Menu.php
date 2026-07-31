<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('BS_Services', 'edit', true)) {
    $module_menu[] = [
        'index.php?module=BS_Services&action=EditView&return_module=BS_Services&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'] ?? 'Create Service',
        'Create',
        'BS_Services',
    ];
}
if (ACLController::checkAccess('BS_Services', 'list', true)) {
    $module_menu[] = [
        'index.php?module=BS_Services&action=index',
        $mod_strings['LNK_LIST'] ?? 'View Services',
        'List',
        'BS_Services',
    ];
}
