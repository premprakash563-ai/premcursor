<?php

$module_name = 'BS_Leave';
$viewdefs[$module_name]['EditView'] = [
    'templateMeta' => [
        'maxColumns' => '2',
        'widths' => [['label' => '10', 'field' => '30'], ['label' => '10', 'field' => '30']],
    ],
    'panels' => [
        'default' => [
            ['name', 'status'],
            ['date_start', 'date_end'],
            ['assigned_user_name'],
            ['description'],
        ],
    ],
];
