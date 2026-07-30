# Implementation Roadmap — SuiteCRM Business Service CRM

Phased delivery. Each phase is independently demoable.

---

## Phase 0 — Foundation
- Install / use SuiteCRM **7.15.1**; deploy `suitecrm-extension/` via Module Loader or manual copy
- Configure company, email SMTP, SSL, roles: Super Admin / Employee
- Enable Audit, Security Groups, REST API
- Deploy this repo’s `suitecrm-extension/` package
- Seed sample services (GST, Company, MSME, Trademark, FSSAI)

**Exit:** Admin can log in; custom modules visible.

---

## Phase 1 — Catalog & Orders (MVP Admin/Employee)
- `BS_Services` + required document templates
- `BS_Orders` + application JSON + status dropdown + status history
- Manual assign / reassign UI
- Document upload with approve / reject / re-upload
- Cases for support tickets
- Employee ACL: only assigned orders

**Exit:** Admin creates order → assigns employee → employee updates status & docs.

---

## Phase 2 — Auto Assignment & Notifications
- `AssignmentEngine` (max 10/day, round-robin, skip offline/leave/inactive)
- Employee availability field + leave module (minimal)
- Workload dashlet for Admin
- Email + in-app notifications for order/assign/status/docs

**Exit:** New order auto-assigns; admin sees workload; emails fire.

---

## Phase 3 — Customer Portal & Website Booking
- Portal auth: register / login / OTP
- Browse services, book, application form, document upload
- Auto create Account + Contact + Order
- Order list, timeline, invoice download, profile
- Support ticket from portal

**Exit:** Full customer self-service path without admin data entry.

---

## Phase 4 — Payments & GST Invoices
- Razorpay (cards + UPI) first
- Payment history on order
- Auto invoice PDF (logo, GSTIN, line items, tax breakup, QR)
- Bank transfer manual confirmation flow
- PhonePe / Paytm adapters (optional follow-up)

**Exit:** Paid order → invoice generated → customer download.

---

## Phase 5 — Live Chat
- Chat service (WebSocket) + CRM message store
- Customer ↔ assigned employee only
- File/image/PDF share, read receipts, typing, history
- Admin monitor console

**Exit:** Real-time chat on assigned projects.

---

## Phase 6 — Dashboards, Reports, Excel
- Admin KPI dashlets (today/month orders, revenue, pending, employees online, top services)
- Reports: product-wise, revenue, employee performance, payments, GST
- Export Excel / CSV / PDF
- Global search fields (GST, PAN, Order ID)
- Employee performance metrics

**Exit:** Admin weekly ops from CRM alone.

---

## Phase 7 — Automation & Hardening
- Idle project escalation (configurable days)
- Pending document reminders
- Daily / monthly auto reports to admin email
- reCAPTCHA on portal, session hardening, optional 2FA for admin
- Audit coverage for portal actions
- API stubs for WhatsApp / SMS / GST-PAN verify

**Exit:** SRD core + automation checklist complete.

---

## Out of Scope for v1 (Future Modules)
Sales CRM extras, affiliate/franchise, mobile apps, AI verification, e-sign, multi-language, Tally/Zoho deep sync — design APIs so these can plug in later.

---

## Suggested Build Order Inside Code

1. Module defs + install SQL (`suitecrm-extension/`)
2. AssignmentEngine + logic hook
3. Portal API contract (`portal-api/openapi.yaml`)
4. Payment + invoice adapters
5. Chat integration contract
6. Dashlets / reports

---

## Acceptance Checklist (Core SRD)

- [ ] Three roles behave per matrix
- [ ] Unlimited services with required docs
- [ ] Website/portal booking creates CRM order + customer
- [ ] Auto-assign respects 10/day + availability
- [ ] Status timeline visible to customer
- [ ] Docs approve/reject/re-upload
- [ ] Notifications on key events
- [ ] Payment + GST invoice
- [ ] Live chat assignee-only; admin can monitor
- [ ] Admin dashboard + Excel/PDF reports
- [ ] Global search by client/order/GST/PAN
- [ ] Audit of critical actions
