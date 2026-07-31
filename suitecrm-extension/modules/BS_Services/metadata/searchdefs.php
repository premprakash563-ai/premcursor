<?php

$module_name = 'BS_Services';
$searchdefs[$module_name] = [
    'templateMeta' => [
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => ['label' => '10', 'field' => '30'],
    ],
    'layout' => [
        'basic_search' => [
            'name',
            ['name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'],
        ],
        'advanced_search' => [
            'name',
            'category',
            'status',
            'assigned_user_id',
        ],
    ],
];
