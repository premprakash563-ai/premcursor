# Prayas notification fix

## Problem

Payments are recorded in the **current month** only. The old logic deleted a notification only when `payments.month = notifications.month`, so an October payment cleared October even when older months were still pending.

## Fix (payments table untouched)

Notification logic **does not read or change** the `payments` table.

1. **20th cron (header):** add current-month notification if missing.
2. **On payment save:** `floor(amount / monthly_ms)` oldest notifications are deleted.

### Example

4 pending months (July–October), `monthly_ms = 2000`, user pays **₹4000 in October**:

- `floor(4000 / 2000) = 2`
- Delete 2 oldest notifications → July + August removed
- September + October still pending

If 8 months pending and user pays ₹8000 → 4 oldest notifications removed.

## Files

- `include/notification_logic.php`
- `include/payment_hooks.php`
- `header.php`

## `payment_add.php` integration

After inserting the payment:

```php
require_once 'include/payment_hooks.php';
payment_add_after_save($pdo, $member_id, (float) $amount);
```

## `header.php` change

Replace old sync + payments-based DELETE with:

```php
require_once 'include/notification_logic.php';
ms_sync_current_month_notifications($pdo, $current_day, $current_month, $current_year);
```

Remove this old block completely:

```php
$del_sql = "DELETE n FROM notifications n
            INNER JOIN payments p ON n.member_id = p.member_id
            AND n.month = p.month AND n.year = p.year";
$pdo->query($del_sql);
```
