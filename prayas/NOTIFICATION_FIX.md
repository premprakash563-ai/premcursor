# Prayas notification fix

## Problem

Payments are always recorded in the **current month**, but pending MS can span multiple older months. The old logic deleted a notification only when `payments.month = notifications.month`, so an October payment cleared October even when July/August were still unpaid.

## Fix

FIFO allocation:

1. Build all due months for a member (from join/start date through today; current month becomes due on the 20th).
2. Sum all payments for that member.
3. Apply payment total to the **oldest due months first**.
4. Remaining months stay in `notifications`.

### Example

| Month | Due |
| --- | --- |
| July | ₹2000 |
| August | ₹2000 |
| September | ₹2000 |
| October | ₹2000 |

User pays **₹4000 in October** → July + August cleared, **September + October still pending**.

## Files

- `include/notification_logic.php` — FIFO sync helpers
- `include/payment_hooks.php` — call after saving a payment
- `header.php` — uses `ms_sync_all_notifications()` instead of month-matched DELETE

## Integrate in `payment_add.php`

After inserting the payment:

```php
require_once 'include/payment_hooks.php';
payment_add_after_save($pdo, $member_id, (float) $amount);
```

If your `payments` table has no `amount` column, each payment row is treated as one full `monthly_ms`.

## Optional member fields

For accurate due-month history, set one of these on `members`:

- `ms_start_month` + `ms_start_year` (recommended)
- `join_date`
- `created_at`

Without them, the helper falls back to the member's first payment month.
