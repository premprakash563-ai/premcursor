<?php
/**
 * Call this right after saving a payment in payment_add.php
 *
 * Example:
 *   require_once 'include/notification_logic.php';
 *   ms_apply_payment_to_notifications($pdo, $member_id, (float)$amount, (float)$monthly_ms);
 *   ms_sync_member_notifications($pdo, $member, (int)date('j'), (int)date('n'), (int)date('Y'));
 */

require_once __DIR__ . '/notification_logic.php';

function payment_add_after_save(PDO $pdo, int $member_id, float $amount): void
{
    $stmt = $pdo->prepare('SELECT id, monthly_ms, ms_start_month, ms_start_year, join_date, created_at FROM members WHERE id = ?');
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        return;
    }

    $monthly_ms = (float) ($member['monthly_ms'] ?? 0);
    ms_apply_payment_to_notifications($pdo, $member_id, $amount, $monthly_ms);
    ms_sync_member_notifications($pdo, $member, (int) date('j'), (int) date('n'), (int) date('Y'));
}
