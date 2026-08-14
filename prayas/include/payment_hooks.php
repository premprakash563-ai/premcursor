<?php
/**
 * Call this right after saving a payment in payment_add.php.
 *
 * Example:
 *   require_once 'include/payment_hooks.php';
 *   payment_add_after_save($pdo, $member_id, (float) $amount);
 */

require_once __DIR__ . '/notification_logic.php';

function payment_add_after_save(PDO $pdo, int $member_id, float $amount): void
{
    $stmt = $pdo->prepare('SELECT monthly_ms FROM members WHERE id = ?');
    $stmt->execute([$member_id]);
    $monthly_ms = (float) $stmt->fetchColumn();

    ms_apply_payment_to_notifications($pdo, $member_id, $amount, $monthly_ms);
}
