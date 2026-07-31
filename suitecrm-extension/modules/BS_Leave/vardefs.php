<?php

$dictionary['BS_Leave'] = [
    'table' => 'bs_leave',
    'audited' => true,
    'fields' => [
        'date_start' => [
            'name' => 'date_start',
            'vname' => 'LBL_DATE_START',
            'type' => 'date',
            'required' => true,
        ],
        'date_end' => [
            'name' => 'date_end',
            'vname' => 'LBL_DATE_END',
            'type' => 'date',
            'required' => true,
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'bs_leave_status_list',
            'default' => 'pending',
            'len' => 50,
            'audited' => true,
        ],
    ],
    'indices' => [
        ['name' => 'idx_bs_leave_user', 'type' => 'index', 'fields' => ['assigned_user_id', 'status']],
    ],
];

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('BS_Leave', 'BS_Leave', ['basic', 'assignable', 'security_groups']);
