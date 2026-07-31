# Client summary — SuiteCRM pe kaise banega

## Target version

**SuiteCRM 7.15.1** (confirmed)

## Short verdict

SuiteCRM **admin + employee CRM** ke liye strong base hai. Client SRD ka customer portal, live chat, India payments, aur auto-assignment **custom** banana hoga.

## Teeno roles

| Role | SuiteCRM me |
|---|---|
| Super Admin | SuiteCRM Admin user + full ACL |
| Employee | SuiteCRM User — sirf apne assigned orders |
| Customer | SuiteCRM User **nahi** — Contact + alag Portal login (OTP) |

## Pehle kya banana hai (recommended)

1. **Services + Orders + Documents** modules (is repo me stubs ready)
2. **Auto assignment** — 10 enquiry/day, round-robin (engine ready)
3. **Customer portal + website booking**
4. **Razorpay + GST invoice**
5. **Live chat**
6. **Dashboard / reports / Excel export**

Detail: `docs/implementation-roadmap.md`

## Is PR / package me kya hai (v0.2.0 — Phase 1)

- Services / Orders / Order Documents / Status History modules
- Auto-assignment (10/day) + admin reassign
- Document Approve / Reject / Re-upload actions
- Status history logging + Employee Workload dashlet
- ACL setup guide: `docs/phase1-acl-setup.md`
- Module Loader zip: `BS_BusinessServiceCRM-0.2.0.zip`

Abhi nahi: customer portal, payments, live chat, full reports (Phase 3+)
