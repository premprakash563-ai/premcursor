<?php

$module_name = 'BS_Services';
$listViewDefs[$module_name] = [
    'NAME' => [
        'width' => '25',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ],
    'CATEGORY' => [
        'width' => '15',
        'label' => 'LBL_CATEGORY',
        'default' => true,
    ],
    'PRICE' => [
        'width' => '10',
        'label' => 'LBL_PRICE',
        'default' => true,
        'currency_format' => true,
    ],
    'DELIVERY_DAYS' => [
        'width' => '10',
        'label' => 'LBL_DELIVERY_DAYS',
        'default' => true,
    ],
    'STATUS' => [
        'width' => '10',
        'label' => 'LBL_STATUS',
        'default' => true,
    ],
    'ASSIGNED_USER_NAME' => [
        'width' => '15',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ],
    'DATE_ENTERED' => [
        'width' => '12',
        'label' => 'LBL_DATE_ENTERED',
        'default' => false,
    ],
];
