<?php

$module_name = 'BS_Orders';
$listViewDefs[$module_name] = [
    'NAME' => [
        'width' => '15',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ],
    'ACCOUNT_NAME' => [
        'width' => '15',
        'label' => 'LBL_ACCOUNT',
        'default' => true,
        'module' => 'Accounts',
        'id' => 'ACCOUNT_ID',
        'link' => true,
        'related_fields' => ['account_id'],
    ],
    'SERVICE_NAME' => [
        'width' => '15',
        'label' => 'LBL_SERVICE',
        'default' => true,
        'module' => 'BS_Services',
        'id' => 'SERVICE_ID',
        'link' => true,
        'related_fields' => ['service_id'],
    ],
    'STATUS' => [
        'width' => '12',
        'label' => 'LBL_STATUS',
        'default' => true,
    ],
    'PAYMENT_STATUS' => [
        'width' => '10',
        'label' => 'LBL_PAYMENT_STATUS',
        'default' => true,
    ],
    'TOTAL_AMOUNT' => [
        'width' => '10',
        'label' => 'LBL_TOTAL_AMOUNT',
        'default' => true,
        'currency_format' => true,
    ],
    'ASSIGNED_USER_NAME' => [
        'width' => '12',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ],
    'DATE_ENTERED' => [
        'width' => '12',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
    ],
];
