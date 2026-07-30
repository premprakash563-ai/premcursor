# SuiteCRM Extension — Business Service CRM

**Target:** SuiteCRM **7.15.1** (Sugar 7 classic modules)

Drop this package into a SuiteCRM 7.15.1 installation and run **Admin → Repair → Quick Repair and Rebuild**.

## Contents

| Path | Purpose |
|---|---|
| `modules/BS_*` | Custom module beans, vardefs, metadata, language |
| `lib/Assignment` | Round-robin auto-assignment engine |
| `lib/Notifications` | Notification channel interfaces |
| `custom/Extension` | Logic hooks, user fields, module registration |
| `install/schema.sql` | Reference DDL for custom tables |
| `install/seed_services.sql` | Sample India business services |
| `manifest.php` | Module Loader package manifest |

## Install on SuiteCRM 7.15.1

> **Use Module Loader, not Upgrade Wizard.**  
> Path: Admin → Developer Tools → **Module Loader**  
> URL tip: `index.php?module=Administration&action=UpgradeWizard` is wrong; Module Loader is under Developer Tools.

### Option A — Manual copy (dev)

1. Copy `modules/BS_*` → `{suitecrm}/modules/`
2. Copy `lib/*` → `{suitecrm}/custom/include/BS/`
3. Merge `custom/Extension/` into `{suitecrm}/custom/Extension/`
4. Copy `install/config_override.sample.php` keys into `{suitecrm}/config_override.php`
5. Admin → Repair → **Quick Repair and Rebuild** → execute any suggested SQL
6. Optionally import `install/seed_services.sql`
7. Create roles **Super Admin** / **Employee** and ACL (see `docs/data-model-and-roles.md`)
8. Set `bs_assignment.max_new_enquiries_per_day = 10`

### Option B — Module Loader zip

```bash
cd suitecrm-extension
zip -r ../BS_BusinessServiceCRM.zip . -x '*.git*'
```

Then: Admin → Module Loader → upload `BS_BusinessServiceCRM.zip` → Install → Quick Repair and Rebuild.

## PHP / stack notes (7.15.1)

- PHP 8.1–8.3 recommended for SuiteCRM 7.15.x
- MySQL 8 / MariaDB 10.4+
- Classic bean modules + `custom/Extension` + Quick Repair (not SuiteCRM 8 Symfony extensions)

## Modules included

- `BS_Services` — service catalog
- `BS_Orders` — service orders / projects (+ reassign action, workload dashlet)
- `BS_OrderDocuments` — KYC/doc review (approve / reject / re-upload)
- `BS_StatusHistory` — status timeline

Also in package: auto-assignment engine, status hooks, notification service, SQL schema/seed.
