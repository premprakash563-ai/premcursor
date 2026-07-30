<?php

$module_name = 'BS_OrderDocuments';
$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'form' => [
            'buttons' => [
                'EDIT',
                'DELETE',
                [
                    'customCode' => '<input type="button" class="button" value="Approve" onclick="window.location.href=\'index.php?module=BS_OrderDocuments&action=approve&record={$fields.id.value}\';">',
                ],
                [
                    'customCode' => '<input type="button" class="button" value="Reject" onclick="window.location.href=\'index.php?module=BS_OrderDocuments&action=reject&record={$fields.id.value}\';">',
                ],
                [
                    'customCode' => '<input type="button" class="button" value="Request Re-upload" onclick="window.location.href=\'index.php?module=BS_OrderDocuments&action=request_reupload&record={$fields.id.value}\';">',
                ],
            ],
        ],
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
            ['assigned_user_name', 'date_entered'],
        ],
    ],
];
