<?php
// Pure PHP unit test for assignment selection rules (no SuiteCRM bootstrap).
// Run: php tests/AssignmentEngineLogicTest.php

function assert_true($cond, $msg)
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
    echo "OK: $msg\n";
}

/**
 * Mirrors BS_AssignmentEngine selection: skip over-cap and unavailable; RR by last_assigned.
 */
function pickAssignee(array $employees, $maxPerDay = 10)
{
    usort($employees, function ($a, $b) {
        $la = $a['last_assigned_at'] ?? '1970-01-01';
        $lb = $b['last_assigned_at'] ?? '1970-01-01';
        if ($la === $lb) {
            return strcmp($a['id'], $b['id']);
        }
        return strcmp($la, $lb);
    });

    foreach ($employees as $e) {
        if (($e['status'] ?? '') !== 'Active') {
            continue;
        }
        $avail = $e['availability'] ?? 'online';
        if (in_array($avail, ['offline', 'on_leave'], true)) {
            continue;
        }
        if (!empty($e['on_approved_leave'])) {
            continue;
        }
        $cap = !empty($e['max_override']) ? (int) $e['max_override'] : $maxPerDay;
        if ((int) $e['today_new'] >= $cap) {
            continue;
        }
        return $e['id'];
    }
    return null;
}

$pool = [
    ['id' => 'A', 'status' => 'Active', 'availability' => 'online', 'today_new' => 10, 'last_assigned_at' => '2026-07-30 09:00:00'],
    ['id' => 'B', 'status' => 'Active', 'availability' => 'online', 'today_new' => 3, 'last_assigned_at' => '2026-07-30 10:00:00'],
    ['id' => 'C', 'status' => 'Active', 'availability' => 'on_leave', 'today_new' => 0, 'last_assigned_at' => '2026-07-29 08:00:00'],
];

$pick = pickAssignee($pool, 10);
assert_true($pick === 'B', 'Skips A at cap and C on leave; picks B');

$pool2 = [
    ['id' => 'A', 'status' => 'Active', 'availability' => 'online', 'today_new' => 10, 'last_assigned_at' => '2026-07-30 09:00:00'],
    ['id' => 'B', 'status' => 'Active', 'availability' => 'offline', 'today_new' => 0, 'last_assigned_at' => '2026-07-29 08:00:00'],
];
assert_true(pickAssignee($pool2, 10) === null, 'Returns null when nobody eligible');

$pool3 = [
    ['id' => 'A', 'status' => 'Active', 'availability' => 'online', 'today_new' => 0, 'last_assigned_at' => '2026-07-30 11:00:00'],
    ['id' => 'B', 'status' => 'Active', 'availability' => 'online', 'today_new' => 0, 'last_assigned_at' => '2026-07-30 09:00:00'],
];
assert_true(pickAssignee($pool3, 10) === 'B', 'Round-robin prefers earlier last_assigned_at');

$pool4 = [
    ['id' => 'A', 'status' => 'Active', 'availability' => 'online', 'today_new' => 5, 'max_override' => 5, 'last_assigned_at' => '2026-07-29 08:00:00'],
    ['id' => 'B', 'status' => 'Active', 'availability' => 'online', 'today_new' => 9, 'last_assigned_at' => '2026-07-30 12:00:00'],
];
assert_true(pickAssignee($pool4, 10) === 'B', 'Respects per-user max override');

echo "All assignment logic tests passed.\n";
