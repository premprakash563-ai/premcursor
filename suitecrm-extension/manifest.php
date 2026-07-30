<?php
/**
 * Module Loader manifest — SuiteCRM 7.15.1 (Phase 1: Catalog & Orders MVP)
 *
 * IMPORTANT:
 * - acceptable_sugar_versions → $sugar_version (6.5.25 on SuiteCRM 7.15.1)
 * - acceptable_suitecrm_versions → $suitecrm_version (7.15.1)
 * Install via Admin → Developer Tools → Module Loader (NOT Upgrade Wizard).
 */

$manifest = array(
    'name' => 'Business Service CRM (BS)',
    'description' => 'Phase 1: Services, Orders, Documents, Status History, auto-assign, workload dashlet',
    'version' => '0.2.0',
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
        array('from' => '<basepath>/lib/Assignment', 'to' => 'custom/include/BS/Assignment'),
        array('from' => '<basepath>/lib/Notifications', 'to' => 'custom/include/BS/Notifications'),
        array('from' => '<basepath>/lib/Orders', 'to' => 'custom/include/BS/Orders'),
        array('from' => '<basepath>/lib/Documents', 'to' => 'custom/include/BS/Documents'),
        array('from' => '<basepath>/custom/Extension/application/Ext/Include', 'to' => 'custom/Extension/application/Ext/Include'),
        array('from' => '<basepath>/custom/Extension/application/Ext/Language', 'to' => 'custom/Extension/application/Ext/Language'),
        array('from' => '<basepath>/custom/Extension/modules/BS_Orders', 'to' => 'custom/Extension/modules/BS_Orders'),
        array('from' => '<basepath>/custom/Extension/modules/Users', 'to' => 'custom/Extension/modules/Users'),
    ),
);
