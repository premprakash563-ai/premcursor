<?php

$dictionary['BS_StatusHistory'] = [
    'table' => 'bs_status_history',
    'audited' => false,
    'fields' => [
        'order_id' => [
            'name' => 'order_id',
            'type' => 'id',
            'vname' => 'LBL_ORDER_ID',
            'required' => true,
        ],
        'order_name' => [
            'name' => 'order_name',
            'rname' => 'name',
            'id_name' => 'order_id',
            'vname' => 'LBL_ORDER',
            'type' => 'relate',
            'link' => 'bs_orders',
            'table' => 'bs_orders',
            'module' => 'BS_Orders',
            'source' => 'non-db',
        ],
        'bs_orders' => [
            'name' => 'bs_orders',
            'type' => 'link',
            'relationship' => 'bs_orders_status_history',
            'source' => 'non-db',
            'vname' => 'LBL_ORDER',
        ],
        'from_status' => [
            'name' => 'from_status',
            'vname' => 'LBL_FROM_STATUS',
            'type' => 'enum',
            'options' => 'bs_order_status_list',
            'len' => 50,
        ],
        'to_status' => [
            'name' => 'to_status',
            'vname' => 'LBL_TO_STATUS',
            'type' => 'enum',
            'options' => 'bs_order_status_list',
            'len' => 50,
            'required' => true,
        ],
        'note' => [
            'name' => 'note',
            'vname' => 'LBL_NOTE',
            'type' => 'text',
        ],
    ],
    'relationships' => [
        'bs_orders_status_history' => [
            'lhs_module' => 'BS_Orders',
            'lhs_table' => 'bs_orders',
            'lhs_key' => 'id',
            'rhs_module' => 'BS_StatusHistory',
            'rhs_table' => 'bs_status_history',
            'rhs_key' => 'order_id',
            'relationship_type' => 'one-to-many',
        ],
    ],
    'indices' => [
        ['name' => 'idx_bs_sh_order', 'type' => 'index', 'fields' => ['order_id', 'date_entered']],
    ],
];

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('BS_StatusHistory', 'BS_StatusHistory', ['basic', 'assignable']);
