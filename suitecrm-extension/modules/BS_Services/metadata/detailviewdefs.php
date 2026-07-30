<?php

$module_name = 'BS_Services';
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
            ['category', 'delivery_days'],
            ['price', 'gst_percent'],
            ['assigned_user_name', 'date_entered'],
            ['required_documents'],
            ['description'],
        ],
    ],
];
