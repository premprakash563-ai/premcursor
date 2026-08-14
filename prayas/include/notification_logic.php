<?php

/**
 * MS payment notifications use FIFO allocation:
 * payments recorded in the current month settle the oldest due months first.
 */

function ms_get_total_paid(PDO $pdo, int $member_id, float $monthly_ms): float
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE member_id = ?');
    $stmt->execute([$member_id]);
    $sum = (float) $stmt->fetchColumn();

    if ($sum > 0) {
        return $sum;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE member_id = ?');
    $stmt->execute([$member_id]);
    $count = (int) $stmt->fetchColumn();

    return $count * $monthly_ms;
}

function ms_is_month_due(int $month, int $year, int $current_day, int $current_month, int $current_year): bool
{
    if ($year < $current_year) {
        return true;
    }

    if ($year > $current_year) {
        return false;
    }

    if ($month < $current_month) {
        return true;
    }

    return $month === $current_month && $current_day >= 20;
}

function ms_member_start_period(PDO $pdo, array $member): array
{
    if (!empty($member['ms_start_month']) && !empty($member['ms_start_year'])) {
        return [(int) $member['ms_start_month'], (int) $member['ms_start_year']];
    }

    if (!empty($member['join_date'])) {
        $join = new DateTime($member['join_date']);

        return [(int) $join->format('n'), (int) $join->format('Y')];
    }

    if (!empty($member['created_at'])) {
        $created = new DateTime($member['created_at']);

        return [(int) $created->format('n'), (int) $created->format('Y')];
    }

    $stmt = $pdo->prepare('SELECT MIN(year * 100 + month) AS period FROM payments WHERE member_id = ?');
    $stmt->execute([(int) $member['id']]);
    $period = (int) $stmt->fetchColumn();

    if ($period > 0) {
        return [$period % 100, (int) floor($period / 100)];
    }

    return [(int) date('n'), (int) date('Y')];
}

function ms_build_due_months(
    int $start_month,
    int $start_year,
    int $current_day,
    int $current_month,
    int $current_year
): array {
    $due_months = [];
    $cursor = new DateTime(sprintf('%04d-%02d-01', $start_year, $start_month));
    $end = new DateTime(sprintf('%04d-%02d-01', $current_year, $current_month));

    while ($cursor <= $end) {
        $month = (int) $cursor->format('n');
        $year = (int) $cursor->format('Y');

        if (ms_is_month_due($month, $year, $current_day, $current_month, $current_year)) {
            $due_months[] = ['month' => $month, 'year' => $year];
        }

        $cursor->modify('+1 month');
    }

    return $due_months;
}

function ms_apply_fifo(float $total_paid, float $monthly_ms, array $due_months): array
{
    if ($monthly_ms <= 0) {
        return $due_months;
    }

    $pool = $total_paid;
    $pending = [];

    foreach ($due_months as $due_month) {
        if ($pool >= $monthly_ms) {
            $pool -= $monthly_ms;
            continue;
        }

        $pending[] = $due_month;
    }

    return $pending;
}

function ms_sync_member_notifications(
    PDO $pdo,
    array $member,
    int $current_day,
    int $current_month,
    int $current_year
): void {
    $member_id = (int) $member['id'];
    $monthly_ms = (float) ($member['monthly_ms'] ?? 0);

    if ($monthly_ms <= 0) {
        return;
    }

    [$start_month, $start_year] = ms_member_start_period($pdo, $member);
    $due_months = ms_build_due_months($start_month, $start_year, $current_day, $current_month, $current_year);
    $total_paid = ms_get_total_paid($pdo, $member_id, $monthly_ms);
    $pending_months = ms_apply_fifo($total_paid, $monthly_ms, $due_months);

    $pdo->prepare("DELETE FROM notifications WHERE member_id = ? AND type = 'ms'")
        ->execute([$member_id]);

    $insert = $pdo->prepare(
        "INSERT INTO notifications (member_id, month, year, type, status) VALUES (?, ?, ?, 'ms', '0')"
    );

    foreach ($pending_months as $pending) {
        $insert->execute([$member_id, $pending['month'], $pending['year']]);
    }
}

function ms_sync_all_notifications(
    PDO $pdo,
    int $current_day,
    int $current_month,
    int $current_year
): void {
    $members = $pdo->query('SELECT id, monthly_ms, ms_start_month, ms_start_year, join_date, created_at FROM members')
        ->fetchAll(PDO::FETCH_ASSOC);

    foreach ($members as $member) {
        ms_sync_member_notifications($pdo, $member, $current_day, $current_month, $current_year);
    }
}

function ms_apply_payment_to_notifications(
    PDO $pdo,
    int $member_id,
    float $payment_amount,
    float $monthly_ms
): void {
    if ($monthly_ms <= 0 || $payment_amount <= 0) {
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM notifications
         WHERE member_id = ? AND type = 'ms'
         ORDER BY year ASC, month ASC"
    );
    $stmt->execute([$member_id]);
    $notification_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pool = $payment_amount;
    $to_delete = [];

    foreach ($notification_ids as $notification_id) {
        if ($pool >= $monthly_ms) {
            $to_delete[] = (int) $notification_id;
            $pool -= $monthly_ms;
            continue;
        }

        break;
    }

    if ($to_delete === []) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($to_delete), '?'));
    $delete = $pdo->prepare("DELETE FROM notifications WHERE id IN ($placeholders)");
    $delete->execute($to_delete);
}
