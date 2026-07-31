<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

$module_menu[] = [
    'index.php?entryPoint=bs_operations_board',
    $mod_strings['LNK_BOARD'] ?? 'Open Operations Board',
    'Home',
    'BS_Dashboard',
];
