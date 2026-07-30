<?php
/**
 * Suggested sugar_config entries — merge into config_override.php
 */

$sugar_config['bs_assignment'] = [
    'max_new_enquiries_per_day' => 10,
    'park_unassigned' => true,
    'alert_admins_on_unassigned' => true,
];

$sugar_config['bs_notifications'] = [
    'email' => true,
    'in_app' => true,
    'sms' => false,
    'whatsapp' => false,
];

$sugar_config['bs_payments'] = [
    'default_gateway' => 'razorpay',
    'razorpay' => [
        'key_id' => '',
        'key_secret' => '',
        'webhook_secret' => '',
    ],
];

$sugar_config['bs_invoice'] = [
    'prefix' => 'INV',
    'company_gstin' => '',
    'company_name' => '',
    'logo_path' => '',
];

$sugar_config['bs_automation'] = [
    'idle_escalation_days' => 3,
    'document_reminder_days' => 2,
    'daily_report_hour' => 9,
];
