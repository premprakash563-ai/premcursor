<?php
/**
 * User custom fields for assignment availability.
 * Copy to: custom/Extension/modules/Users/Ext/Vardefs/bs_employee_meta.php
 */

$dictionary['User']['fields']['availability_c'] = [
    'name' => 'availability_c',
    'vname' => 'LBL_AVAILABILITY',
    'type' => 'enum',
    'options' => 'bs_availability_list',
    'default' => 'online',
    'len' => 50,
    'reportable' => true,
    'source' => 'custom_fields',
];

$dictionary['User']['fields']['last_assigned_at_c'] = [
    'name' => 'last_assigned_at_c',
    'vname' => 'LBL_LAST_ASSIGNED_AT',
    'type' => 'datetime',
    'reportable' => true,
    'source' => 'custom_fields',
];

$dictionary['User']['fields']['max_enquiries_override_c'] = [
    'name' => 'max_enquiries_override_c',
    'vname' => 'LBL_MAX_ENQUIRIES_OVERRIDE',
    'type' => 'int',
    'default' => null,
    'reportable' => true,
    'source' => 'custom_fields',
];
