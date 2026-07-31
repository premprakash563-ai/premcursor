# Data Model & Role Matrix

## Tables (logical)

### bs_services
| Field | Type | Notes |
|---|---|---|
| id | char(36) | PK |
| name | varchar | Service name |
| description | text | |
| price | decimal(18,2) | Base price (ex-GST or as agreed) |
| gst_percent | decimal(5,2) | Default 18 |
| delivery_days | int | SLA days |
| category | varchar | |
| status | enum | active / inactive |
| assigned_user_id | char(36) | Optional default owner |
| date_entered / modified | datetime | SuiteCRM std |

### bs_doc_templates
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| service_id | char(36) | FK BS_Services |
| doc_code | varchar | AADHAAR, PAN, GST, … |
| label | varchar | |
| is_mandatory | bool | |
| allowed_mime | varchar | pdf,jpg,png |

### bs_orders
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| name | varchar | Auto: ORD-YYYYMMDD-#### |
| account_id | char(36) | Client |
| contact_id | char(36) | Customer |
| service_id | char(36) | |
| status | varchar | Pipeline status |
| payment_status | enum | pending/paid/partial/refunded |
| amount | decimal | |
| gst_amount | decimal | |
| total_amount | decimal | |
| assigned_user_id | char(36) | Employee |
| assigned_at | datetime | |
| is_new_enquiry | bool | Counts toward daily cap until first status move |
| gstin | varchar | Customer GSTIN |
| pan | varchar | |
| source | varchar | website/portal/admin |
| date_entered | datetime | |

### bs_applications
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| form_json | longtext | Dynamic field answers |
| submitted_at | datetime | |

### bs_order_documents
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| doc_code | varchar | |
| filename | varchar | |
| file_path | varchar | |
| mime_type | varchar | |
| status | enum | uploaded/approved/rejected/reupload_requested |
| reviewer_id | char(36) | |
| review_note | text | |
| document_id | char(36) | Link to SuiteCRM Documents if used |

### bs_status_history
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| from_status | varchar | |
| to_status | varchar | |
| changed_by | char(36) | |
| note | text | |
| date_entered | datetime | |

### bs_payments
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| gateway | varchar | razorpay/phonepe/paytm/upi/bank |
| gateway_ref | varchar | |
| amount | decimal | |
| status | enum | created/success/failed/refunded |
| paid_at | datetime | |
| raw_payload | longtext | |

### bs_invoices
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| invoice_no | varchar | Unique GST series |
| invoice_date | date | |
| taxable | decimal | |
| cgst/sgst/igst | decimal | |
| total | decimal | |
| qr_payload | text | UPI/e-invoice QR data |
| pdf_path | varchar | |

### bs_assignment_log
| Field | Type | Notes |
|---|---|---|
| id | char(36) | |
| order_id | char(36) | |
| user_id | char(36) | |
| reason | varchar | auto_rr / manual / reassign |
| daily_count_at_assign | int | |
| date_entered | datetime | |

### Employee availability (users_cstm or bs_employee_meta)
| Field | Type | Notes |
|---|---|---|
| availability | enum | online/offline/on_leave |
| last_assigned_at | datetime | Round-robin pointer |
| max_enquiries_override | int | Optional per-user cap |

---

## Role × Permission Matrix

| Capability | Super Admin | Employee | Customer (Portal) |
|---|---|---|---|
| Manage users / roles | ✓ | | |
| Manage all clients | ✓ | Own only | Own profile |
| CRUD services | ✓ | Read | Read (active) |
| View all orders | ✓ | Assigned only | Own only |
| Reassign order | ✓ | | |
| Change order status | ✓ | Assigned | View timeline |
| Approve/reject docs | ✓ | Assigned | Upload / re-upload |
| Live chat | Monitor all | Assigned thread | Own thread |
| Payments config | ✓ | | Pay own order |
| Invoices | ✓ | View assigned | Download own |
| Cases / tickets | ✓ | Reply assigned | Create / view own |
| Reports & export | ✓ | Limited own KPIs | |
| Leave request | Manage | Create own | |
| Audit logs | ✓ | | |
| Website integration settings | ✓ | | |

---

## Indexes (performance)

- `bs_orders(assigned_user_id, date_entered)` — daily workload count  
- `bs_orders(status, date_entered)` — dashboards  
- `bs_orders(name)` unique — order search  
- `bs_orders(pan), bs_orders(gstin)` — global search  
- `bs_payments(gateway_ref)` — webhook idempotency  
- `bs_assignment_log(user_id, date_entered)` — audit / RR debug  
