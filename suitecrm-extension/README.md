# SuiteCRM Extension — Business Service CRM

Drop this package into a SuiteCRM installation and run Quick Repair & Rebuild (v7) or cache clear (v8).

## Contents

| Path | Purpose |
|---|---|
| `modules/BS_*` | Custom module stubs (beans, vardefs, language) |
| `lib/Assignment` | Round-robin auto-assignment engine |
| `lib/Notifications` | Notification channel interfaces |
| `custom/Extension` | Logic hooks registration samples |
| `install/schema.sql` | Reference DDL for custom tables |
| `install/seed_services.sql` | Sample India business services |

## Install (SuiteCRM 7.x)

1. Copy `modules/BS_*` → `{suitecrm}/modules/`
2. Copy `lib/` → `{suitecrm}/custom/include/BS/`
3. Merge `custom/Extension/` into `{suitecrm}/custom/Extension/`
4. Import `install/schema.sql` (or let Module Builder / repair create tables from vardefs)
5. Admin → Repair → Quick Repair and Rebuild → execute SQL if prompted
6. Create roles **Super Admin** / **Employee** and ACL as in `docs/data-model-and-roles.md`
7. Set config `bs_assignment.max_new_enquiries_per_day = 10`

## Install (SuiteCRM 8.x)

Prefer converting beans to an Extension package under `extensions/`. Keep `lib/Assignment` logic identical; wire hooks via Symfony event subscribers.

## Modules Included (stubs)

- BS_Services
- BS_Orders
- BS_OrderDocuments
- BS_StatusHistory
- BS_AssignmentLog
- BS_Payments

Chat, invoices, leave, attendance: schema reserved in `install/schema.sql`; full beans can be added in later phases.
