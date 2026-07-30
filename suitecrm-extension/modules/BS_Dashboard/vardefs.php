<?php
// Placeholder bean table not required — controller renders custom view.
$dictionary['BS_Dashboard'] = [
    'table' => 'bs_dashboard',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'bool',
            'default' => '0',
        ],
        'name' => [
            'name' => 'name',
            'type' => 'varchar',
            'len' => 255,
        ],
    ],
];
