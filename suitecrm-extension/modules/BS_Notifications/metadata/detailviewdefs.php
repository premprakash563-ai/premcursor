<?php

$module_name = 'BS_Notifications';
$viewdefs[$module_name]['DetailView'] = [
    'templateMeta' => [
        'form' => ['buttons' => ['DELETE']],
        'maxColumns' => '2',
        'widths' => [['label' => '10', 'field' => '30'], ['label' => '10', 'field' => '30']],
    ],
    'panels' => [
        'default' => [
            ['name', 'event_code'],
            ['message'],
            ['is_read', 'date_entered'],
            ['related_order_id', 'assigned_user_name'],
        ],
    ],
];
