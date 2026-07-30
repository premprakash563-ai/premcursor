<?php

$dictionary['BS_Notifications'] = [
    'table' => 'bs_notifications',
    'audited' => false,
    'fields' => [
        'recipient_type' => [
            'name' => 'recipient_type',
            'vname' => 'LBL_RECIPIENT_TYPE',
            'type' => 'enum',
            'options' => 'bs_recipient_type_list',
            'len' => 20,
            'default' => 'user',
        ],
        'recipient_id' => [
            'name' => 'recipient_id',
            'vname' => 'LBL_RECIPIENT_ID',
            'type' => 'id',
        ],
        'event_code' => [
            'name' => 'event_code',
            'vname' => 'LBL_EVENT_CODE',
            'type' => 'varchar',
            'len' => 50,
        ],
        'message' => [
            'name' => 'message',
            'vname' => 'LBL_MESSAGE',
            'type' => 'text',
        ],
        'payload_json' => [
            'name' => 'payload_json',
            'vname' => 'LBL_PAYLOAD',
            'type' => 'longtext',
        ],
        'is_read' => [
            'name' => 'is_read',
            'vname' => 'LBL_IS_READ',
            'type' => 'bool',
            'default' => '0',
        ],
        'related_order_id' => [
            'name' => 'related_order_id',
            'type' => 'id',
            'vname' => 'LBL_RELATED_ORDER_ID',
        ],
    ],
    'indices' => [
        ['name' => 'idx_bs_notif_recipient', 'type' => 'index', 'fields' => ['recipient_type', 'recipient_id', 'is_read']],
    ],
];

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('BS_Notifications', 'BS_Notifications', ['basic', 'assignable']);
