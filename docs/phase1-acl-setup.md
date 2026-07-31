# Phase 1 — Admin / Employee ACL setup (SuiteCRM 7.15.1)

After installing the module package and Quick Repair:

## 1. Roles

Admin → Role Management → Create:

### Super Admin
- All modules: **Admin** access (or use system Admin users)

### Employee
| Module | Access | Delete | Edit | List | View | Import | Export |
|---|---|---|---|---|---|---|---|
| BS_Services | Enabled | None | None | All | All | None | None |
| BS_Orders | Enabled | None | Owner | Owner | Owner | None | None |
| BS_OrderDocuments | Enabled | None | Owner | Owner | Owner | None | None |
| BS_StatusHistory | Enabled | None | None | Owner | Owner | None | None |
| Accounts | Enabled | None | Owner | Owner | Owner | None | None |
| Contacts | Enabled | None | Owner | Owner | Owner | None | None |
| Cases | Enabled | None | Owner | Owner | Owner | None | None |
| Documents | Enabled | None | Owner | Owner | Owner | None | None |

**Owner** = only records where `Assigned to` = that employee.

## 2. Assign role to users

Admin → User Management → each employee → Roles → Employee.

## 3. Security Groups (optional but recommended)

Enable Security Suite settings if available:
- Inheritance from Assigned To User
- Additive / Strict as per policy

## 4. Employee availability fields

After Repair, each User should show custom fields:
- Availability (`online` / `offline` / `on_leave`)
- Last Assigned At
- Max Enquiries Override

Set offline/on leave employees so auto-assignment skips them.

## 5. Smoke test checklist

1. Admin creates **Service** (e.g. GST Registration)
2. Admin creates **Service Order** without assignee → auto-assigns eligible employee
3. Employee logs in → sees only own orders
4. Employee changes **status** → Status History row created
5. Employee creates **Order Document** → Approve / Reject / Request Re-upload buttons work
6. Admin opens Home → add dashlet **Employee Workload**
7. Admin opens order → **Reassign…** with another user id

## 6. Support tickets

Use built-in **Cases** module. Link case to Account/Contact of the order client. Employee role above already scopes Cases to Owner.
