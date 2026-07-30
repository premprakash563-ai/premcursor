# SuiteCRM Gap Analysis — Business Service CRM

**Target platform:** SuiteCRM **7.15.1** (classic Sugar 7 modules)  
**Goal:** Map every SRD capability to **Native**, **Configure**, or **Custom build**.

Legend: ✅ Native / configure · ⚙️ Partial (needs extension) · ❌ Custom required

---

## 1. User Roles

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Super Admin | ✅ | Role + Security Groups (Admin / Super Admin) |
| Employee panel (scoped to own projects) | ⚙️ | Role + Security Groups + ownership; hide other users’ records via ACL |
| Customer portal (register, OTP, book, chat) | ❌ | Custom **Customer Portal** (React/Next or SuiteCRM portal theme) + REST/GraphQL API |
| OTP verification | ❌ | Custom OTP service (SMS/Email) + portal auth |

**Verdict:** Admin/Employee map well to SuiteCRM users. Customers should **not** be SuiteCRM Users; use Contacts/Accounts + separate portal auth.

---

## 2. Website Integration

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Browse/compare services & pricing | ❌ | Public website (WordPress / Next.js) reading `BS_Services` via API |
| Book + application form + uploads | ❌ | Public booking flow → creates Account/Contact + `BS_Orders` |
| Auto CRM account after order | ⚙️ | Workflow / logic hook: create Contact + portal credentials |

---

## 3. Products / Services

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Service catalog (name, price, docs, SLA, category) | ⚙️ | Custom module `BS_Services` (Products module is sales-oriented; custom fits compliance services better) |
| Required documents checklist | ❌ | Related module `BS_DocRequirements` or JSON field + portal UI |

---

## 4. Order Workflow

| Step | SuiteCRM | Approach |
|---|---|---|
| Create order from booking | ⚙️ | `BS_Orders` (+ optional Quote/Invoice link) |
| Application form data | ❌ | `BS_Applications` or order custom fields + dynamic form builder |
| Payment gate | ❌ | Payment gateway integration module |
| Auto assign + notify | ❌ | Logic hook / scheduled job + notification service |
| Chat + completion + invoice | ⚙️/❌ | Custom chat; AOS Invoices or custom `BS_Invoices` |

---

## 5. Automatic Employee Assignment

| Rule | SuiteCRM | Approach |
|---|---|---|
| Max 10 new enquiries/employee/day | ❌ | Custom `AssignmentEngine` |
| Round-robin among Online employees | ❌ | Same engine + employee availability status |
| Skip Offline / Leave / Inactive | ⚙️ | User status + Leave module + assignment filter |
| Manual reassign + workload view | ⚙️ | Admin UI + report / dashlet |

**This is a core custom component** — see `suitecrm-extension/lib/Assignment/`.

---

## 6. Live Chat

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Real-time chat, files, receipts, typing | ❌ | WebSocket service (Node/Soketi) + `BS_ChatThreads` / `BS_ChatMessages` |
| Admin monitor all chats | ❌ | Admin chat console ACL |

Native SuiteCRM has no production-grade live chat. Integrate dedicated chat backend; store history in CRM.

---

## 7. Project Status Tracking

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Fixed status pipeline (9 stages) | ⚙️ | Dropdown on `BS_Orders` + Workflow / Process + timeline UI on portal |
| Customer-visible timeline | ❌ | Portal component reading status history (`BS_StatusHistory`) |

---

## 8. Document Management

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Upload / download | ✅ | Documents module + notes attachments |
| Approve / Reject / Re-upload request | ❌ | Custom fields + status on `BS_OrderDocuments` + notifications |

---

## 9. Notifications

| Channel | SuiteCRM | Approach |
|---|---|---|
| Email | ✅ | Outbound email + Workflow |
| In-app | ⚙️ | Alerts / custom notification center |
| SMS / WhatsApp | ❌ | Gateway adapters (MSG91, Twilio, WhatsApp Cloud API) |

---

## 10–13. Dashboard, Reports, Performance, Excel

| Requirement | SuiteCRM | Approach |
|---|---|---|
| KPI dashboard | ⚙️ | Dashlets + custom charts (Chart.js) querying orders/revenue |
| Period reports + Excel/CSV/PDF | ⚙️ | Reports module + custom export actions / PhpSpreadsheet / Dompdf |
| Employee performance KPIs | ❌ | Custom report module + metrics table |
| Customer import/export | ✅ | Import wizard + list export; extend for orders/services |

---

## 14. Global Search

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Name, mobile, email, order ID, GST, PAN, employee, service | ⚙️ | Global Search config + custom searchable fields; optional Elasticsearch |

---

## 15–16. Payments & Invoices

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Razorpay / PhonePe / Paytm / UPI / Bank | ❌ | `BS_Payments` + gateway adapters |
| GST invoice + QR + logo | ⚙️ | AOS Invoices extended **or** custom invoice PDF (India GST template) |

---

## 17–19. Tickets, Attendance, Audit

| Requirement | SuiteCRM | Approach |
|---|---|---|
| Support tickets | ✅ | Cases module (+ portal) |
| Attendance / leave (optional) | ❌ | Lightweight `BS_Attendance` / `BS_Leave` or HR plugin |
| Audit logs | ⚙️ | Audit module + custom action logging for portal/API |

---

## 20. Security

| Requirement | SuiteCRM | Approach |
|---|---|---|
| SSL, password hash, RBAC, sessions | ✅ | Platform defaults |
| OTP login, reCAPTCHA, optional 2FA | ⚙️/❌ | Portal + SuiteCRM auth plugins |

---

## 21–22. APIs & Automation

| Requirement | SuiteCRM | Approach |
|---|---|---|
| REST API | ✅ | SuiteCRM V8 API / GraphQL (v8) |
| External verifications & accounting | ❌ | Integration adapters |
| Auto assignment, invoice, reminders, escalation | ⚙️ | Schedulers + logic hooks + custom jobs |

---

## Summary Scorecard

| Area | Fit |
|---|---|
| Admin CRM (clients, orders, docs, cases, reports base) | **Strong** — build on SuiteCRM |
| Employee workspace | **Good** — roles + custom dashlets |
| Customer portal + website booking | **Custom** |
| Auto assignment (10/day RR) | **Custom** |
| Live chat | **Custom / 3rd party** |
| India payments + GST invoice | **Custom** |
| WhatsApp/SMS/OTP | **Custom adapters** |

### Recommendation

Use **SuiteCRM as the operational CRM core** (employees, admins, accounts, orders, documents, cases, audit, reporting). Build a **Customer Portal + Public Booking Site** on top of SuiteCRM APIs. Implement **AssignmentEngine**, **Payment/Invoice India pack**, and **Chat service** as first-class custom packages.

Do **not** try to force customers into SuiteCRM User licenses as portal users — use Contact-linked portal auth.
