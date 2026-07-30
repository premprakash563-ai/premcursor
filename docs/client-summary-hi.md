# Client summary — SuiteCRM pe kaise banega

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

## Is PR me kya hai

- Poora SRD + gap analysis + architecture + data model
- SuiteCRM extension scaffolding (`BS_Services`, `BS_Orders`, `BS_OrderDocuments`)
- Assignment engine + notification service + SQL schema + sample services
- Portal API OpenAPI contract
- Assignment unit tests (pass)
