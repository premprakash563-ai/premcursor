<?php

require_once __DIR__ . '/../include/notification_logic.php';

function assert_int(int $expected, int $actual, string $label): void
{
    if ($expected === $actual) {
        echo "PASS: $label\n";
        return;
    }

    echo "FAIL: $label\n";
    echo "  expected: $expected\n";
    echo "  actual:   $actual\n";
    exit(1);
}

assert_int(2, ms_months_cleared_by_payment(4000, 2000), '4000 clears 2 months at 2000 each');
assert_int(1, ms_months_cleared_by_payment(2000, 2000), '2000 clears 1 month');
assert_int(4, ms_months_cleared_by_payment(8000, 2000), '8000 clears 4 months');
assert_int(2, ms_months_cleared_by_payment(4999, 2000), '4999 clears 2 full months only');
assert_int(0, ms_months_cleared_by_payment(1500, 2000), '1500 does not clear a full month');
assert_int(0, ms_months_cleared_by_payment(4000, 0), 'zero monthly_ms clears nothing');

echo "All notification payment-clear tests passed.\n";
