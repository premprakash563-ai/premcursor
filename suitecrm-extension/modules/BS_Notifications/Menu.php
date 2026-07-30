<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('BS_Notifications', 'list', true)) {
    $module_menu[] = [
        'index.php?module=BS_Notifications&action=index',
        $mod_strings['LNK_LIST'] ?? 'View Notifications',
        'List',
        'BS_Notifications',
    ];
}
