<?php
/**
 * Module Loader manifest — SuiteCRM 7.15.1 (Dashboard polish 0.3.2)
 * Theme CSS is inlined via hook (works even when /custom/themes URL is 404).
 */

$manifest = array(
    'name' => 'Business Service CRM (BS)',
    'description' => 'Clean professional dashboard + UI polish (inline theme CSS)',
    'version' => '0.3.2',
    'author' => 'Prem Cursor',
    'acceptable_sugar_versions' => array(
        'exact_matches' => array('6.5.25'),
        'regex_matches' => array('^6\\.5\\.[0-9]+$'),
    ),
    'acceptable_suitecrm_versions' => array(
        'exact_matches' => array('7.15.1', '7.15.0'),
        'regex_matches' => array('^7\\.15\\..*$', '^7\\.14\\..*$'),
    ),
    'acceptable_sugar_flavors' => array('CE', 'PRO', 'ENT'),
    'is_uninstallable' => true,
    'published_date' => '2026-07-30',
    'type' => 'module',
    'readme' => 'README.md',
);

$installdefs = array(
    'id' => 'BS_BusinessServiceCRM',
    'copy' => array(
        array('from' => '<basepath>/modules/BS_Services', 'to' => 'modules/BS_Services'),
        array('from' => '<basepath>/modules/BS_Orders', 'to' => 'modules/BS_Orders'),
        array('from' => '<basepath>/modules/BS_OrderDocuments', 'to' => 'modules/BS_OrderDocuments'),
        array('from' => '<basepath>/modules/BS_StatusHistory', 'to' => 'modules/BS_StatusHistory'),
        array('from' => '<basepath>/modules/BS_Leave', 'to' => 'modules/BS_Leave'),
        array('from' => '<basepath>/modules/BS_Notifications', 'to' => 'modules/BS_Notifications'),
        array('from' => '<basepath>/modules/Home/Dashlets/BS_AdminDashboardDashlet', 'to' => 'modules/Home/Dashlets/BS_AdminDashboardDashlet'),
        array('from' => '<basepath>/lib/Assignment', 'to' => 'custom/include/BS/Assignment'),
        array('from' => '<basepath>/lib/Notifications', 'to' => 'custom/include/BS/Notifications'),
        array('from' => '<basepath>/lib/Orders', 'to' => 'custom/include/BS/Orders'),
        array('from' => '<basepath>/lib/Documents', 'to' => 'custom/include/BS/Documents'),
        array('from' => '<basepath>/lib/Theme', 'to' => 'custom/include/BS/Theme'),
        array('from' => '<basepath>/custom/themes/SuiteP/css', 'to' => 'custom/themes/SuiteP/css'),
        array('from' => '<basepath>/themes/SuiteP/css', 'to' => 'themes/SuiteP/css'),
        array('from' => '<basepath>/custom/Extension/application/Ext/Include', 'to' => 'custom/Extension/application/Ext/Include'),
        array('from' => '<basepath>/custom/Extension/application/Ext/Language', 'to' => 'custom/Extension/application/Ext/Language'),
        array('from' => '<basepath>/custom/Extension/application/Ext/LogicHooks', 'to' => 'custom/Extension/application/Ext/LogicHooks'),
        array('from' => '<basepath>/custom/Extension/modules/BS_Orders', 'to' => 'custom/Extension/modules/BS_Orders'),
        array('from' => '<basepath>/custom/Extension/modules/Users', 'to' => 'custom/Extension/modules/Users'),
        array('from' => '<basepath>/custom/Extension/modules/Schedulers', 'to' => 'custom/Extension/modules/Schedulers'),
    ),
    'post_execute' => array(
        '<basepath>/scripts/post_install.php',
    ),
);
