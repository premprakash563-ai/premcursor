<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

$module_menu[] = [
    'index.php?module=BS_Dashboard&action=index',
    $mod_strings['LNK_BOARD'] ?? 'Open Operations Board',
    'Home',
    'BS_Dashboard',
];
