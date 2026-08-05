<?php
/*
Plugin Name: Auto Amelia MyCred Refund (Parent–Student Smart)
Description: Automatically refunds MyCred credits when Amelia bookings are canceled or rejected. Supports parent–student relationship. Runs on cancel hooks + 5-minute WP-Cron (not every page load). Uses MySQL GET_LOCK to prevent double refunds.
Version: 1.3
Author: Custom Integration
*/

if (!defined('ABSPATH')) exit;

/**
 * Custom WP-Cron interval: every 5 minutes (safety-net catch-up only).
 */
add_filter('cron_schedules', 'auto_amelia_mycred_refund_cron_schedules');
function auto_amelia_mycred_refund_cron_schedules($schedules) {
    if (!isset($schedules['every_five_minutes'])) {
        $schedules['every_five_minutes'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display'  => 'Every Five Minutes',
        ];
    }
    return $schedules;
}

register_activation_hook(__FILE__, 'auto_amelia_mycred_refund_activate');
register_deactivation_hook(__FILE__, 'auto_amelia_mycred_refund_deactivate');

function auto_amelia_mycred_refund_activate() {
    auto_amelia_mycred_refund_clear_cron();
    wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_refund_cron');
}

function auto_amelia_mycred_refund_deactivate() {
    auto_amelia_mycred_refund_clear_cron();
}

function auto_amelia_mycred_refund_clear_cron() {
    $timestamp = wp_next_scheduled('auto_amelia_mycred_refund_cron');
    while ($timestamp) {
        wp_unschedule_event($timestamp, 'auto_amelia_mycred_refund_cron');
        $timestamp = wp_next_scheduled('auto_amelia_mycred_refund_cron');
    }
}

/**
 * Ensure 5-minute cron exists (in-place updates skip activation hook).
 */
add_action('init', 'auto_amelia_mycred_refund_ensure_scheduled');
function auto_amelia_mycred_refund_ensure_scheduled() {
    $timestamp = wp_next_scheduled('auto_amelia_mycred_refund_cron');
    if (!$timestamp) {
        wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_refund_cron');
        return;
    }

    $cron = _get_cron_array();
    $hook = 'auto_amelia_mycred_refund_cron';
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
        auto_amelia_mycred_refund_clear_cron();
        wp_schedule_event(time(), 'every_five_minutes', 'auto_amelia_mycred_refund_cron');
    }
}

// ---------------------------------------------------------------
// TRIGGERS
// 1) Immediate: Amelia cancel / status update (front + admin)
// 2) Catch-up: 5-minute WP-Cron
// Never on every page load (wp_loaded flood).
// ---------------------------------------------------------------
add_action('amelia_after_booking_canceled', 'auto_amelia_mycred_refund_on_booking_event', 20, 3);
add_action('amelia_after_appointment_status_updated', 'auto_amelia_mycred_refund_on_booking_event', 20, 2);
add_action('AmeliaAppointmentBookingCanceled', 'auto_amelia_mycred_refund_on_booking_event', 20, 3);
add_action('AmeliaAppointmentBookingStatusUpdated', 'auto_amelia_mycred_refund_on_booking_event', 20, 3);
add_action('auto_amelia_mycred_refund_cron', 'auto_amelia_mycred_refund_run');

/**
 * Booking-time trigger. Same request me multiple Amelia hooks fire ho
 * sakte hain — static guard se process ek hi baar chalta hai.
 */
function auto_amelia_mycred_refund_on_booking_event() {
    static $already_ran = false;
    if ($already_ran) {
        return;
    }
    $already_ran = true;

    auto_amelia_mycred_refund_run();
}

function auto_amelia_mycred_refund_run() {
    global $wpdb;

    // ===== GLOBAL LOCK: prevent concurrent runs =====
    $global_lock = 'amelia_mycred_refund';
    $got_global = $wpdb->get_var($wpdb->prepare("SELECT GET_LOCK(%s, 0)", $global_lock));
    if ($got_global != 1) {
        return; // Another instance is already running
    }

    try {
        _auto_amelia_mycred_refund_process();
    } finally {
        $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $global_lock));
    }
}

function _auto_amelia_mycred_refund_process() {
    global $wpdb;

    $mycred_log_table = defined('MYCRED_LOG_TABLE') ? MYCRED_LOG_TABLE : "{$wpdb->prefix}myCRED_log";

    // Canceled/rejected bookings still marked paid (not yet refunded).
    // Include both Amelia spellings: canceled / cancelled.
    $cancelled_bookings = $wpdb->get_results("
        SELECT b.id AS booking_id, b.customerId, b.status AS booking_status,
               p.id AS payment_id, p.amount AS paid_amount, p.status AS payment_status,
               p.gateway AS payment_gateway
        FROM {$wpdb->prefix}amelia_customer_bookings b
        INNER JOIN {$wpdb->prefix}amelia_payments p ON p.customerBookingId = b.id
        WHERE (b.status = 'canceled' OR b.status = 'cancelled' OR b.status = 'rejected')
          AND p.status = 'paid'
    ");

    if (empty($cancelled_bookings)) {
        return;
    }

    foreach ($cancelled_bookings as $booking) {
        $booking_id  = intval($booking->booking_id);
        $customer_id = intval($booking->customerId);
        $payment_id  = intval($booking->payment_id);

        // ===== PER-BOOKING LOCK =====
        $booking_lock = 'amelia_refund_booking_' . $booking_id;
        $got_booking = $wpdb->get_var($wpdb->prepare("SELECT GET_LOCK(%s, 0)", $booking_lock));
        if ($got_booking != 1) {
            continue; // Another process is handling this booking
        }

        try {
            // Re-check payment status inside lock (another worker may have refunded).
            $current_status = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM {$wpdb->prefix}amelia_payments WHERE id = %d",
                $payment_id
            ));
            if ($current_status !== 'paid') {
                continue;
            }

            // Get linked WP user
            $wp_user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT externalId FROM {$wpdb->prefix}amelia_users WHERE id = %d",
                $customer_id
            ));
            $wp_user_id = intval($wp_user_id);

            if ($wp_user_id <= 0) {
                // No WP user — still settle payment so this row never rematches.
                _auto_amelia_mark_payment_refunded($payment_id);
                continue;
            }

            $parent_id = intval(get_user_meta($wp_user_id, 'parent_id', true));

            // Duplicate check INSIDE the lock
            $student_refunded = (int) $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM {$mycred_log_table}
                WHERE ref = %s AND ref_id = %d AND user_id = %d
            ", 'amelia_booking_refund', $booking_id, $wp_user_id));

            $parent_refunded = 0;
            if ($parent_id > 0) {
                $parent_refunded = (int) $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM {$mycred_log_table}
                    WHERE ref = %s AND ref_id = %d AND user_id = %d
                ", 'amelia_booking_refund', $booking_id, $parent_id));
            }

            $parent_amount  = 0;
            $student_amount = 0;

            // Refund exactly what was deducted (parent share / student share).
            if ($parent_id > 0 && $parent_refunded === 0) {
                $parent_cred = $wpdb->get_row($wpdb->prepare("
                    SELECT creds FROM {$mycred_log_table}
                    WHERE ref = %s AND ref_id = %d AND user_id = %d
                    ORDER BY id DESC
                    LIMIT 1
                ", 'amelia_booking_payment', $booking_id, $parent_id));
                $parent_amount = $parent_cred ? abs(floatval($parent_cred->creds)) : 0;
            }

            if ($student_refunded === 0) {
                $student_cred = $wpdb->get_row($wpdb->prepare("
                    SELECT creds FROM {$mycred_log_table}
                    WHERE ref = %s AND ref_id = %d AND user_id = %d
                    ORDER BY id DESC
                    LIMIT 1
                ", 'amelia_booking_payment', $booking_id, $wp_user_id));
                $student_amount = $student_cred ? abs(floatval($student_cred->creds)) : 0;
            }

            if (function_exists('mycred_add')) {
                if ($student_amount > 0 && $student_refunded === 0) {
                    mycred_add(
                        'amelia_booking_refund',
                        $wp_user_id,
                        $student_amount,
                        'Refund for student share in canceled/rejected Amelia booking',
                        $booking_id
                    );
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("AUTO AMELIA MYCRED REFUND: {$student_amount} to STUDENT {$wp_user_id} for booking {$booking_id}");
                    }
                }

                if ($parent_id > 0 && $parent_amount > 0 && $parent_refunded === 0) {
                    mycred_add(
                        'amelia_booking_refund',
                        $parent_id,
                        $parent_amount,
                        'Refund for parent share in canceled/rejected Amelia booking',
                        $booking_id
                    );
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("AUTO AMELIA MYCRED REFUND: {$parent_amount} to PARENT {$parent_id} for booking {$booking_id}");
                    }
                }
            }

            // Terminal: leave the unpaid/paid refund set so cron never rematches.
            _auto_amelia_mark_payment_refunded($payment_id);

        } finally {
            $wpdb->query($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $booking_lock));
        }
    }
}

/**
 * Mark Amelia payment as refunded (terminal for the unpaid/paid query).
 *
 * @param int $payment_id Payment row ID.
 */
function _auto_amelia_mark_payment_refunded($payment_id) {
    global $wpdb;

    if (!$payment_id) {
        return;
    }

    $wpdb->update(
        "{$wpdb->prefix}amelia_payments",
        ['status' => 'refunded'],
        ['id' => $payment_id]
    );
}

/**
 * Manual debug trigger — admins only (replaces open ?refund_debug=run).
 * Usage (logged-in admin): /?amelia_mycred_refund_debug=1
 */
add_action('init', 'auto_amelia_mycred_refund_debug_endpoint');
function auto_amelia_mycred_refund_debug_endpoint() {
    if (!isset($_GET['amelia_mycred_refund_debug']) || $_GET['amelia_mycred_refund_debug'] !== '1') {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Forbidden', 'auto-amelia-mycred-refund'), 403);
    }

    auto_amelia_mycred_refund_run();

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('AUTO AMELIA MYCRED REFUND: manual admin debug run completed');
    }

    wp_die(
        esc_html__('Refund scan completed.', 'auto-amelia-mycred-refund'),
        esc_html__('Amelia MyCred Refund', 'auto-amelia-mycred-refund'),
        ['response' => 200]
    );
}
