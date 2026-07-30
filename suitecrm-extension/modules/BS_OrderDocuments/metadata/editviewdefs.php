<?php

$module_name = 'BS_OrderDocuments';
$viewdefs[$module_name]['EditView'] = [
    'templateMeta' => [
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        'default' => [
            ['name', 'doc_code'],
            ['order_id', 'status'],
            ['filename', 'mime_type'],
            ['file_path'],
            ['review_note'],
            ['description'],
            ['assigned_user_name'],
        ],
    ],
];
