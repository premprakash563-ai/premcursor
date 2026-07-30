# Business Service CRM on SuiteCRM

Client SRD implementation package for **Business Service CRM & Client Management System** on **SuiteCRM 7.15.1**.

## What's in this repo

| Path | Description |
|---|---|
| [`docs/SRD.md`](docs/SRD.md) | Full client software requirements |
| [`docs/suitecrm-gap-analysis.md`](docs/suitecrm-gap-analysis.md) | Native vs custom capability map |
| [`docs/architecture.md`](docs/architecture.md) | System architecture & modules |
| [`docs/data-model-and-roles.md`](docs/data-model-and-roles.md) | Schema + role permission matrix |
| [`docs/implementation-roadmap.md`](docs/implementation-roadmap.md) | Phased delivery plan |
| [`suitecrm-extension/`](suitecrm-extension/) | SuiteCRM custom modules, assignment engine, hooks |
| [`portal-api/openapi.yaml`](portal-api/openapi.yaml) | Customer portal / website REST contract |
| [`tests/`](tests/) | Assignment logic unit tests |

## SuiteCRM strategy (short) — v7.15.1

- **Use SuiteCRM 7.15.1** for Admin + Employee ops: Accounts, Contacts, Cases, Documents, ACL, audit, email, reports base.
- **Custom modules (`BS_*`)** via classic beans + `custom/Extension` + Quick Repair (not SuiteCRM 8 extensions).
- **Custom portal + website** for customers (OTP, booking, timeline, chat, invoices) — customers are Contacts, not CRM Users.
- **Must-build customs:** auto-assignment (10/day round-robin), India payments/GST invoice, live chat, WhatsApp/SMS adapters.

## Quick start

1. Read [`docs/suitecrm-gap-analysis.md`](docs/suitecrm-gap-analysis.md) and [`docs/implementation-roadmap.md`](docs/implementation-roadmap.md).
2. Install SuiteCRM; deploy [`suitecrm-extension/`](suitecrm-extension/) per its README.
3. Import `suitecrm-extension/install/schema.sql` + `seed_services.sql`.
4. Merge `install/config_override.sample.php` into `config_override.php`.
5. Run assignment tests:

```bash
php tests/AssignmentEngineLogicTest.php
```

## Phase 1 focus (next build)

1. Complete remaining bean stubs (OrderDocuments, StatusHistory, Payments).
2. Admin workload dashlet.
3. Portal API skeleton implementing `portal-api/openapi.yaml`.
4. Razorpay + GST invoice PDF.
