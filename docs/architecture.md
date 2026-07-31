# Architecture — Business Service CRM on SuiteCRM

## System Context

```text
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│ Public Website  │────▶│  Portal API      │────▶│  SuiteCRM Core      │
│ (services/book) │     │  (auth, orders,  │     │  Accounts/Contacts  │
└─────────────────┘     │   docs, chat)    │     │  BS_* custom mods   │
                        └────────┬─────────┘     │  Users / Roles      │
┌─────────────────┐              │               │  Cases / Documents  │
│ Customer Portal │──────────────┤               └──────────┬──────────┘
└─────────────────┘              │                          │
┌─────────────────┐              │               ┌──────────▼──────────┐
│ Employee UI     │──────────────┼──────────────▶│ Assignment Engine   │
│ (SuiteCRM +     │              │               │ Chat Service        │
│  custom views)  │              │               │ Notify Adapters     │
└─────────────────┘              │               │ Payment Gateways    │
┌─────────────────┐              │               └─────────────────────┘
│ Admin SuiteCRM  │──────────────┘
└─────────────────┘
```

## Custom Modules (BS_ prefix)

| Module | Purpose |
|---|---|
| `BS_Services` | Sellable compliance/business services |
| `BS_ServiceCategories` | Categories (optional; or dropdown) |
| `BS_DocTemplates` | Required docs per service |
| `BS_Orders` | Customer service orders / projects |
| `BS_Applications` | Form answers for an order |
| `BS_OrderDocuments` | Uploaded files + approve/reject |
| `BS_StatusHistory` | Timeline events |
| `BS_Payments` | Gateway transactions |
| `BS_Invoices` | GST invoices (or extend AOS_Invoices) |
| `BS_ChatThreads` | 1:1 customer–assignee threads |
| `BS_ChatMessages` | Messages + attachments meta |
| `BS_Notifications` | In-app notification inbox |
| `BS_Leave` | Employee leave (optional) |
| `BS_Attendance` | Login/logout hours (optional) |
| `BS_AssignmentLog` | Who got what, why (audit) |

## Core Relationships

```text
Account (Client)
  └── Contact (Customer users of portal)
        └── BS_Orders (n)
              ├── BS_Services (1)
              ├── assigned_user_id → Employee (User)
              ├── BS_Applications (1)
              ├── BS_OrderDocuments (n)
              ├── BS_StatusHistory (n)
              ├── BS_Payments (n)
              ├── BS_Invoices (0..1)
              └── BS_ChatThreads (1)
                    └── BS_ChatMessages (n)

BS_Services
  └── BS_DocTemplates (n)
```

## Order Status Enum

Stored on `BS_Orders.status` and appended to `BS_StatusHistory`:

1. `application_submitted`
2. `documents_received`
3. `under_verification`
4. `processing`
5. `department_submission`
6. `approval_pending`
7. `completed`
8. `certificate_ready`
9. `delivered`

Also operational statuses: `payment_pending`, `cancelled`, `on_hold`.

## Auto-Assignment Algorithm

```text
ON BS_Orders after_save (new + unassigned):
  candidates = Users WHERE
    role = Employee
    AND status = Active
    AND availability NOT IN (Offline, OnLeave)
    AND deleted = 0
  FOR EACH candidate ordered by last_assigned_at ASC (round-robin):
    IF count(new BS_Orders assigned to user WHERE DATE(date_entered)=TODAY) < 10:
      assign user
      write BS_AssignmentLog
      notify employee + customer
      RETURN
  ELSE:
    park in Unassigned queue + alert Super Admin
```

Configurable: `max_new_enquiries_per_day` (default 10).

## Security Model

| Role | SuiteCRM User? | Access |
|---|---|---|
| Super Admin | Yes | All modules, all records, chat monitor, reassign |
| Employee | Yes | Own `assigned_user_id` orders/docs/chat only |
| Customer | No (Contact + portal token) | Own orders via API only |

Security Groups: one group per employee optional; prefer **owner-based ACL** for orders.

## Notification Events

| Event | Email | In-App | SMS/WA (opt) |
|---|---|---|---|
| Order received | ✓ | ✓ | ✓ |
| Employee assigned | ✓ | ✓ | ✓ |
| Documents required / rejected | ✓ | ✓ | ✓ |
| Status changed | ✓ | ✓ | |
| Payment pending | ✓ | ✓ | ✓ |
| Project completed | ✓ | ✓ | ✓ |
| Invoice generated | ✓ | ✓ | |

## Tech Stack Suggestion

| Layer | Choice |
|---|---|
| CRM | SuiteCRM **7.15.1** (PHP 8.1–8.3, MySQL 8 / MariaDB) |
| Portal / Website | Next.js or Laravel + Blade |
| Realtime chat | Soketi / Laravel Reverb / Node WS |
| Payments | Razorpay first (UPI+cards); PhonePe/Paytm adapters later |
| PDF invoices | Dompdf / mPDF with GST template |
| Excel | PhpSpreadsheet |
| Jobs | SuiteCRM Schedulers + queue (Redis) |

## File Layout in This Repo

```text
docs/                      Requirements & plans (target: SuiteCRM 7.15.1)
suitecrm-extension/        Module Loader package for SuiteCRM 7.15.1
  manifest.php             Installer manifest
  modules/                 Classic bean modules
  lib/Assignment/          Round-robin engine → custom/include/BS/
  lib/Notifications/       Channel adapters
  custom/Extension/        Hooks, Include, language, user fields
  install/                 SQL seed, config defaults
portal-api/                Spec for portal REST endpoints
scripts/build-package.sh   Zip builder for Module Loader
```
