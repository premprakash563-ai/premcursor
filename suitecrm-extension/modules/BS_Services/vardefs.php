<?php

$dictionary['BS_Services'] = [
    'table' => 'bs_services',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => [
        'price' => [
            'name' => 'price',
            'vname' => 'LBL_PRICE',
            'type' => 'currency',
            'dbType' => 'decimal',
            'len' => '26,6',
            'required' => true,
        ],
        'gst_percent' => [
            'name' => 'gst_percent',
            'vname' => 'LBL_GST_PERCENT',
            'type' => 'decimal',
            'len' => '5,2',
            'default' => '18.00',
        ],
        'delivery_days' => [
            'name' => 'delivery_days',
            'vname' => 'LBL_DELIVERY_DAYS',
            'type' => 'int',
            'default' => 7,
        ],
        'category' => [
            'name' => 'category',
            'vname' => 'LBL_CATEGORY',
            'type' => 'varchar',
            'len' => 100,
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'bs_service_status_list',
            'default' => 'active',
            'len' => 50,
        ],
        'required_documents' => [
            'name' => 'required_documents',
            'vname' => 'LBL_REQUIRED_DOCUMENTS',
            'type' => 'text',
            'comment' => 'Comma-separated doc codes or JSON checklist',
        ],
    ],
    'indices' => [
        ['name' => 'idx_bs_services_status', 'type' => 'index', 'fields' => ['status']],
        ['name' => 'idx_bs_services_category', 'type' => 'index', 'fields' => ['category']],
    ],
    'optimistic_locking' => true,
];

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('BS_Services', 'BS_Services', ['basic', 'assignable', 'security_groups']);
