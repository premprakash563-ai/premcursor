<?php

/**
 * MS notifications work only on the notifications table.
 * payments table is NOT read or changed by this logic.
 *
 * On each payment: floor(amount / monthly_ms) oldest pending months are removed.
 * Example: 4 pending months, pay 4000 @ 2000/month -> 2 oldest notifications deleted.
 */

function ms_months_cleared_by_payment(float $payment_amount, float $monthly_ms): int
{
    if ($monthly_ms <= 0 || $payment_amount <= 0) {
        return 0;
    }

    return (int) floor($payment_amount / $monthly_ms);
}

function ms_sync_current_month_notifications(
    PDO $pdo,
    int $current_day,
    int $current_month,
    int $current_year
): void {
    if ($current_day < 20 || $current_day > 31) {
        return;
    }

    $sync_sql = "INSERT INTO notifications (member_id, month, year, type, status)
                 SELECT m.id, :month, :year, 'ms', '0'
                 FROM members m
                 WHERE NOT EXISTS (
                     SELECT 1 FROM notifications n
                     WHERE n.member_id = m.id
                       AND n.month = :month
                       AND n.year = :year
                       AND n.type = 'ms'
                 )";

    $stmt = $pdo->prepare($sync_sql);
    $stmt->execute([
        'month' => $current_month,
        'year' => $current_year,
    ]);
}

function ms_apply_payment_to_notifications(
    PDO $pdo,
    int $member_id,
    float $payment_amount,
    float $monthly_ms
): int {
    $months_to_clear = ms_months_cleared_by_payment($payment_amount, $monthly_ms);

    if ($months_to_clear <= 0) {
        return 0;
    }

    $stmt = $pdo->prepare(
        'SELECT id FROM notifications
         WHERE member_id = ? AND type = \'ms\'
         ORDER BY year ASC, month ASC
         LIMIT ' . $months_to_clear
    );
    $stmt->execute([$member_id]);
    $notification_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($notification_ids === []) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($notification_ids), '?'));
    $delete = $pdo->prepare("DELETE FROM notifications WHERE id IN ($placeholders)");
    $delete->execute($notification_ids);

    return count($notification_ids);
}
