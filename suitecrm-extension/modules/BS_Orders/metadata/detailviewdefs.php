<?php

$module_name = 'BS_Orders';
$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'form' => ['buttons' => ['EDIT', 'DUPLICATE', 'DELETE']],
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
