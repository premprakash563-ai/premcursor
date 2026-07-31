# CRM Software Requirement Document (SRD)

**Project Name:** Business Service CRM & Client Management System

## Project Overview

The system will allow customers to order business services online, automatically assign employees to projects, provide live communication, track project progress, and generate business reports through a centralized CRM.

---

## 1. User Roles

### Super Admin
- Full control over the CRM
- Manage all employees, clients, products/services, orders
- View reports; export Excel/PDF
- Manage chat, notifications, website integration
- Assign/reassign projects manually
- View employee workload and revenue reports

### Employee Panel
Dedicated login. Features: Dashboard, Assigned Clients/Projects, Live Chat, Upload/Request Documents, Change Project Status, Internal Notes, Daily Task List, Leave Request, Notification Center. **Cannot access other employees’ projects.**

### Customer Panel
Register/Login, OTP Verification, Dashboard, Book Service, Orders, Live Chat, Upload/Download Documents, Invoice Download, Payment History, Project Status Timeline, Support Ticket, Profile Management.

---

## 2. Website Integration

Browse services, pricing, compare, select, optional cart, book directly, application form, document upload, optional payment, auto CRM account after order.

## 3. Products / Services

Unlimited services (GST Registration, Company Registration, MSME, Trademark, FSSAI, etc.) with: Name, Description, Price, Required Documents, Delivery Time, Category, Status.

## 4. Order Workflow

Select service → Application form → Upload docs → Payment (if required) → Auto create order → Auto assign employee → Notify employee & customer → Live chat → Completion → Invoice.

## 5. Automatic Employee Assignment

- Max **10 new client enquiries per employee per day**
- Round-robin among available employees
- Skip Offline / On Leave / Inactive
- Admin manual reassign + workload visibility

## 6. Live Chat

Customer ↔ Assigned Employee: messaging, files/images/PDF, emoji, read receipts, typing indicator, history, alerts. Admin can monitor all chats.

## 7. Project Status Tracking

Application Submitted → Documents Received → Under Verification → Processing → Department Submission → Approval Pending → Completed → Certificate Ready → Delivered

## 8. Document Management

Customer uploads (Aadhaar, PAN, Passport, GST, Company, Bank, custom). Employee: View, Download, Approve, Reject, Request Re-upload.

## 9. Notification System

Email, SMS (optional), WhatsApp (optional), In-App. Events: Order Received, Documents Required, Employee Assigned, Project Completed, Payment Pending, Invoice Generated.

## 10–13. Admin Dashboard, Reports, Performance, Excel I/O

Dashboards (orders, revenue, employees, payments, top services, performance, CSAT, activity). Reports daily/weekly/monthly/yearly with Excel/CSV/PDF export. Employee KPIs. Import/export customers, orders, employees, services, revenue, payments.

## 14–20. Search, Payments, Invoices, Tickets, Attendance, Audit, Security

Global search; Razorpay/PhonePe/Paytm/UPI/Bank Transfer; GST invoices with QR; support tickets; optional attendance/leave; audit logs; SSL, OTP, reCAPTCHA, RBAC, sessions, optional 2FA.

## 21–23. APIs, Automation, Future Modules

WhatsApp/SMS/Email/GST/PAN/Aadhaar/Payment/Tally/Zoho/Drive APIs. Automations for account creation, assignment, invoices, reminders, escalation, daily/monthly reports. Future: Sales CRM, leads, campaigns, affiliate, multi-branch, mobile app, AI, e-sign, SLA, etc.

## Final Objective

Scalable, secure, automated CRM: online service orders, balanced auto-assignment, real-time communication, document management, admin analytics and reporting.
