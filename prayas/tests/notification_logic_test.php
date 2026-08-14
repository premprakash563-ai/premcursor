<?php

require_once __DIR__ . '/../include/notification_logic.php';

function assert_same(array $expected, array $actual, string $label): void
{
    if ($expected === $actual) {
        echo "PASS: $label\n";
        return;
    }

    echo "FAIL: $label\n";
    echo '  expected: ' . json_encode($expected) . "\n";
    echo '  actual:   ' . json_encode($actual) . "\n";
    exit(1);
}

$due_months = [
    ['month' => 7, 'year' => 2025],
    ['month' => 8, 'year' => 2025],
    ['month' => 9, 'year' => 2025],
    ['month' => 10, 'year' => 2025],
];

$pending = ms_apply_fifo(4000, 2000, $due_months);
assert_same(
    [
        ['month' => 9, 'year' => 2025],
        ['month' => 10, 'year' => 2025],
    ],
    $pending,
    '4000 in October clears July and August first'
);

$pending = ms_apply_fifo(2000, 2000, $due_months);
assert_same(
    [
        ['month' => 8, 'year' => 2025],
        ['month' => 9, 'year' => 2025],
        ['month' => 10, 'year' => 2025],
    ],
    $pending,
    '2000 clears only the oldest due month'
);

$pending = ms_apply_fifo(8000, 2000, $due_months);
assert_same([], $pending, '8000 clears all four due months');

$due_before_20th = ms_build_due_months(7, 2025, 15, 10, 2025);
assert_same(
    [
        ['month' => 7, 'year' => 2025],
        ['month' => 8, 'year' => 2025],
        ['month' => 9, 'year' => 2025],
    ],
    $due_before_20th,
    'Before the 20th, current month is not due yet'
);

$due_after_20th = ms_build_due_months(7, 2025, 20, 10, 2025);
assert_same($due_months, $due_after_20th, 'On the 20th, current month becomes due');

echo "All notification FIFO tests passed.\n";
