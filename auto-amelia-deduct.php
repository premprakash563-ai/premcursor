<?php
/*
Plugin Name: Auto Amelia MyCred Deduction (Parent–Student Smart)
Description: Automatically deduct MyCred credits for unpaid Amelia bookings. Runs immediately on booking create (front + back end), with a 5-minute WP-Cron safety net. Parent–student smart split (student first → parent fallback). Allows unlimited negative balance.
Version: 3.4
Author: Custom Integration
*/

if (!defined('ABSPATH')) exit;

/**
 * Custom WP-Cron interval: every 5 minutes (safety-net catch-up only).
 */
add_filter('cron_schedules', 'auto_amelia_mycred_deduction_cron_schedules');
function auto_amelia_mycred_deduction_cron_schedules($schedules) {
    if (!isset($schedules['every_five_minutes'])) {
        $schedules['every_five_minutes'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display'  => 'Every Five Minutes',
        ];
    }
    return $schedules;
}

/**
 * Schedule on activation; clear on deactivation.
 */
register_activation_hook(__FILE__, 'auto_amelia_mycred_deduction_activate');
register_deactivation_hook(__FILE__, 'auto_amelia_mycred_deduction_deactivate');

function auto_amelia_mycred_deduction_activate() {
    // Clear any older 15-min schedule from previous versions, then set 5-min.
    auto_amelia_mycred_deduction_clear_cron();
    wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_deduction_cron');
}

function auto_amelia_mycred_deduction_deactivate() {
    auto_amelia_mycred_deduction_clear_cron();
}

function auto_amelia_mycred_deduction_clear_cron() {
    $timestamp = wp_next_scheduled('auto_amelia_mycred_deduction_cron');
    while ($timestamp) {
        wp_unschedule_event($timestamp, 'auto_amelia_mycred_deduction_cron');
        $timestamp = wp_next_scheduled('auto_amelia_mycred_deduction_cron');
    }
}

/**
 * Safety net: if the plugin was updated in place (activation hook did not
 * re-fire), ensure a 5-minute cron event is registered. Also migrates off
 * any leftover non-5-minute schedule from older versions.
 */
add_action('init', 'auto_amelia_mycred_deduction_ensure_scheduled');
function auto_amelia_mycred_deduction_ensure_scheduled() {
    $timestamp = wp_next_scheduled('auto_amelia_mycred_deduction_cron');
    if (!$timestamp) {
        wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_deduction_cron');
        return;
    }

    // If an old interval is still scheduled, reschedule to 5 minutes.
    $cron = _get_cron_array();
    $hook = 'auto_amelia_mycred_deduction_cron';
    $need_reschedule = true;

    if (is_array($cron)) {
        foreach ($cron as $events) {
            if (!isset($events[$hook])) {
                continue;
            }
            foreach ($events[$hook] as $event) {
                if (isset($event['schedule']) && $event['schedule'] === 'every_five_minutes') {
                    $need_reschedule = false;
                }
            }
        }
    }

    if ($need_reschedule) {
        auto_amelia_mycred_deduction_clear_cron();
        wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_deduction_cron');
    }
}

// ---------------------------------------------------------------
// TRIGGERS
// 1) Immediate: Amelia booking / appointment created (front + admin)
// 2) Catch-up: 5-minute WP-Cron (missed hooks, races, old unpaid)
// Never on every page load (that caused the flood).
// ---------------------------------------------------------------
add_action('amelia_after_booking_added', 'auto_amelia_mycred_deduction_on_booking', 20, 1);
add_action('amelia_after_appointment_added', 'auto_amelia_mycred_deduction_on_booking', 20, 3);
add_action('auto_amelia_mycred_deduction_cron', 'auto_amelia_mycred_deduction_run');

/**
 * Booking-time trigger. Same request me dono Amelia hooks fire ho sakte
 * hain — static guard se process ek hi baar chalta hai. Locks still
 * protect against real concurrent runs.
 */
function auto_amelia_mycred_deduction_on_booking() {
    static $already_ran = false;
    if ($already_ran) {
        return;
    }
    $already_ran = true;

    auto_amelia_mycred_deduction_run();
}

function auto_amelia_mycred_deduction_run() {
    global $wpdb;

    // ============================================================
    // GLOBAL LOCK: Prevent concurrent execution across requests.
    // MySQL GET_LOCK is server-wide — if another request is already
    // running this function, we skip entirely (timeout = 0).
    // ============================================================
    $lock_acquired = $wpdb->get_var("SELECT GET_LOCK('amelia_mycred_deduction', 0)");
    if (!$lock_acquired) {
        return; // Another request is already processing — skip
    }

    try {
        _auto_amelia_mycred_deduction_process();
    } finally {
        $wpdb->query("SELECT RELEASE_LOCK('amelia_mycred_deduction')");
    }
}

function _auto_amelia_mycred_deduction_process() {
    global $wpdb;

    // 1. Fetch all Amelia customers
    $customers = $wpdb->get_results("SELECT id, externalId FROM {$wpdb->prefix}amelia_users WHERE externalId IS NOT NULL");
    if (empty($customers)) return;

    $mycred_log_table = defined('MYCRED_LOG_TABLE') ? MYCRED_LOG_TABLE : "{$wpdb->prefix}myCRED_log";

    foreach ($customers as $customer) {

        $wp_user_id  = intval($customer->externalId);
        $customer_id = intval($customer->id);

        if ($wp_user_id <= 0) continue;

        $user = get_userdata($wp_user_id);
        if (!$user) continue;

        $roles      = (array)$user->roles;
        $is_student = in_array('student', $roles, true);
        $is_parent  = in_array('parent', $roles, true);

        // Parent ID lookup for students
        $parent_id = 0;
        if ($is_student) {
            $parent_id = intval(get_user_meta($wp_user_id, 'parent_id', true));
        }

        // 2. Fetch unpaid bookings
        // Exclude settled payments: status = 'paid' (credit-paid rows often
        // have amount = 0, which previously rematched forever via p.amount = 0).
        // Also exclude rows this plugin itself settled (gateway = 'mycred-auto').
        $unpaid_bookings = $wpdb->get_results($wpdb->prepare("
            SELECT b.id AS booking_id, b.price AS booking_price,
                   p.id AS payment_id, p.amount AS payment_amount, p.status AS payment_status
            FROM {$wpdb->prefix}amelia_customer_bookings b
            LEFT JOIN {$wpdb->prefix}amelia_payments p ON p.customerBookingId = b.id
            WHERE b.customerId = %d
              AND (p.status = 'pending' OR p.status IS NULL)
              AND (p.gateway IS NULL OR p.gateway <> 'mycred-auto')
        ", $customer_id));

        if (empty($unpaid_bookings)) continue;

        foreach ($unpaid_bookings as $booking) {
            $booking_id = intval($booking->booking_id);
            $needed     = floatval($booking->booking_price ?? 0);

            // ============================================================
            // DUPLICATE CHECK: Per-booking MySQL lock + log table check.
            // The per-booking lock prevents the race condition where two
            // concurrent requests both see 0 log entries and both deduct.
            // ============================================================
            $booking_lock_name = 'amelia_booking_' . $booking_id;
            $booking_lock = $wpdb->get_var($wpdb->prepare(
                "SELECT GET_LOCK(%s, 0)", $booking_lock_name
            ));
            if (!$booking_lock) {
                continue; // Another request is processing this exact booking
            }

            // Check AFTER acquiring lock — now we're guaranteed no race
            $log_exists = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$mycred_log_table}
                WHERE ref = %s AND ref_id = %d
            ", 'amelia_booking_payment', $booking_id));

            if ($log_exists > 0) {
                // Already handled in myCred — ensure Amelia payment is settled
                // so this booking never rematches the unpaid query.
                _auto_amelia_mark_payment_settled($booking->payment_id, $booking_id, $needed);
                $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $booking_lock_name));
                continue;
            }

            // ZERO CASE → terminal: settle the payment row so the unpaid
            // query never rematches. Do not write a 0-point myCred entry
            // (myCred may discard it, leaving no durable marker).
            if ($needed <= 0) {
                _auto_amelia_mark_payment_settled($booking->payment_id, $booking_id, 0);
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("AUTO AMELIA MYCRED: zero-price booking {$booking_id} marked settled (no deduction)");
                }
                $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $booking_lock_name));
                continue;
            }

            // -----------------------
            // APPLY DEDUCTION
            // -----------------------

            if ($is_parent) {
                // CASE 1: PARENT BOOKING → deduct from parent
                deduct_mycred($wp_user_id, $needed, $booking_id, "Parent Unpaid booking payment");

            } elseif ($is_student && $parent_id > 0) {
                // CASE 2: STUDENT BOOKING → student first, then parent fallback
                $student_balance = mycred_get_users_balance($wp_user_id);
                $remaining       = $needed;

                // Step 1: Deduct from student if they have credits
                if ($student_balance > 0) {
                    $deduct_from_student = min($student_balance, $needed);
                    deduct_mycred($wp_user_id, $deduct_from_student, $booking_id, "Student partial Unpaid booking payment");
                    $remaining -= $deduct_from_student;
                }

                // Step 2: Fallback to parent (always allowed, negative balance OK)
                if ($remaining > 0) {
                    deduct_mycred($parent_id, $remaining, $booking_id, "Parent fallback Unpaid booking payment");
                }

            } else {
                // CASE 3: INDIVIDUAL STUDENT (no parent) → student pays
                deduct_mycred($wp_user_id, $needed, $booking_id, "Unpaid booking payment");
            }

            // Mark Amelia payment as paid (gateway tags rows this plugin settled)
            _auto_amelia_mark_payment_settled($booking->payment_id, $booking_id, $needed);

            // Release per-booking lock
            $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $booking_lock_name));
        }
    }
}

/**
 * Mark (or insert) an Amelia payment row as settled by this plugin.
 * Sets status = 'paid' and gateway = 'mycred-auto' so the unpaid query
 * no longer matches — including zero-amount credit-paid bookings.
 */
function _auto_amelia_mark_payment_settled($payment_id, $booking_id, $amount) {
    global $wpdb;

    $table = "{$wpdb->prefix}amelia_payments";

    if ($payment_id) {
        $wpdb->update(
            $table,
            [
                'status'  => 'paid',
                'amount'  => $amount,
                'gateway' => 'mycred-auto',
            ],
            ['id' => $payment_id]
        );
    } else {
        $wpdb->insert($table, [
            'customerBookingId' => $booking_id,
            'amount'            => $amount,
            'status'            => 'paid',
            'gateway'           => 'mycred-auto',
            'dateTime'          => current_time('mysql'),
        ]);
    }
}

/**
 * Helper function for clean deduction
 */
function deduct_mycred($user_id, $amount, $booking_id, $title) {
    if (!function_exists('mycred_add') && !function_exists('mycred_subtract')) return;

    // Guard: callers should not pass ≤ 0; zero bookings are settled upstream.
    if ($amount <= 0) {
        return;
    }

    mycred_subtract(
        'amelia_booking_payment',
        $user_id,
        $amount,
        $title,
        $booking_id
    );

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("AUTO AMELIA MYCRED: DEDUCT {$amount} from {$user_id} for booking {$booking_id}");
    }
}
