<?php

$dictionary['BS_OrderDocuments'] = [
    'table' => 'bs_order_documents',
    'audited' => true,
    'fields' => [
        'order_id' => [
            'name' => 'order_id',
            'type' => 'id',
            'vname' => 'LBL_ORDER_ID',
            'required' => true,
        ],
        'doc_code' => [
            'name' => 'doc_code',
            'vname' => 'LBL_DOC_CODE',
            'type' => 'varchar',
            'len' => 50,
            'required' => true,
        ],
        'filename' => [
            'name' => 'filename',
            'vname' => 'LBL_FILENAME',
            'type' => 'varchar',
            'len' => 255,
        ],
        'file_path' => [
            'name' => 'file_path',
            'vname' => 'LBL_FILE_PATH',
            'type' => 'varchar',
            'len' => 500,
        ],
        'mime_type' => [
            'name' => 'mime_type',
            'vname' => 'LBL_MIME_TYPE',
            'type' => 'varchar',
            'len' => 100,
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'bs_doc_review_list',
            'default' => 'uploaded',
            'len' => 50,
            'audited' => true,
        ],
        'reviewer_id' => [
            'name' => 'reviewer_id',
            'type' => 'id',
            'vname' => 'LBL_REVIEWER_ID',
        ],
        'review_note' => [
            'name' => 'review_note',
            'vname' => 'LBL_REVIEW_NOTE',
            'type' => 'text',
        ],
        'document_id' => [
            'name' => 'document_id',
            'type' => 'id',
            'vname' => 'LBL_DOCUMENT_ID',
            'comment' => 'Optional link to SuiteCRM Documents module',
        ],
    ],
    'indices' => [
        ['name' => 'idx_bs_od_order', 'type' => 'index', 'fields' => ['order_id']],
        ['name' => 'idx_bs_od_status', 'type' => 'index', 'fields' => ['status']],
    ],
];

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('BS_OrderDocuments', 'BS_OrderDocuments', ['basic', 'assignable', 'security_groups']);
