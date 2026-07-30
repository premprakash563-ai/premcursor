<?php
/**
 * Module Loader manifest — SuiteCRM 7.15.1
 * Package as zip with this file at the root of the archive.
 */

$manifest = [
    'name' => 'Business Service CRM (BS)',
    'description' => 'Custom modules and auto-assignment for Business Service CRM & Client Management',
    'version' => '0.1.0',
    'author' => 'Prem Cursor',
    'acceptable_sugar_versions' => [
        'exact_matches' => [],
        'regex_matches' => ['^7\\.15\\..*$', '^7\\.14\\..*$'],
    ],
    'acceptable_sugar_flavors' => ['CE', 'PRO', 'ENT'],
    'is_uninstallable' => true,
    'published_date' => '2026-07-30',
    'type' => 'module',
    'readme' => 'README.md',
];

$installdefs = [
    'id' => 'BS_BusinessServiceCRM',
    'copy' => [
        [
            'from' => '<basepath>/modules/BS_Services',
            'to' => 'modules/BS_Services',
        ],
        [
            'from' => '<basepath>/modules/BS_Orders',
            'to' => 'modules/BS_Orders',
        ],
        [
            'from' => '<basepath>/modules/BS_OrderDocuments',
            'to' => 'modules/BS_OrderDocuments',
        ],
        [
            'from' => '<basepath>/lib/Assignment',
            'to' => 'custom/include/BS/Assignment',
        ],
        [
            'from' => '<basepath>/lib/Notifications',
            'to' => 'custom/include/BS/Notifications',
        ],
        [
            'from' => '<basepath>/custom/Extension/application/Ext/Include',
            'to' => 'custom/Extension/application/Ext/Include',
        ],
        [
            'from' => '<basepath>/custom/Extension/application/Ext/Language',
            'to' => 'custom/Extension/application/Ext/Language',
        ],
        [
            'from' => '<basepath>/custom/Extension/modules/BS_Orders',
            'to' => 'custom/Extension/modules/BS_Orders',
        ],
        [
            'from' => '<basepath>/custom/Extension/modules/Users',
            'to' => 'custom/Extension/modules/Users',
        ],
    ],
];
