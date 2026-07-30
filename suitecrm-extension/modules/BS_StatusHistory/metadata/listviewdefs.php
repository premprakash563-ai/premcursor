<?php

$module_name = 'BS_StatusHistory';
$listViewDefs[$module_name] = [
    'NAME' => [
        'width' => '20',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ],
    'ORDER_NAME' => [
        'width' => '20',
        'label' => 'LBL_ORDER',
        'default' => true,
        'module' => 'BS_Orders',
        'id' => 'ORDER_ID',
        'link' => true,
        'related_fields' => ['order_id'],
    ],
    'FROM_STATUS' => [
        'width' => '15',
        'label' => 'LBL_FROM_STATUS',
        'default' => true,
    ],
    'TO_STATUS' => [
        'width' => '15',
        'label' => 'LBL_TO_STATUS',
        'default' => true,
    ],
    'DATE_ENTERED' => [
        'width' => '15',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
    ],
    'ASSIGNED_USER_NAME' => [
        'width' => '12',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ],
];
