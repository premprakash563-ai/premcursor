<?php

$module_name = 'BS_Orders';
$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'form' => [
            'buttons' => [
                'EDIT',
                'DUPLICATE',
                'DELETE',
                [
                    'customCode' => '{if $is_admin}<input type="button" class="button" value="Reassign…" onclick="var u=prompt(\'Enter Employee User ID to reassign:\'); if(u){ window.location.href=\'index.php?module=BS_Orders&action=reassign&record={$fields.id.value}&user_id=\'+encodeURIComponent(u);}">{/if}',
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
            ['name', 'status'],
            ['account_name', 'contact_name'],
            ['service_name', 'payment_status'],
            ['amount', 'gst_amount'],
            ['total_amount', 'source'],
            ['gstin', 'pan'],
            ['assigned_user_name', 'assigned_at'],
            ['is_new_enquiry', 'date_entered'],
            ['application_json'],
            ['description'],
        ],
    ],
];
