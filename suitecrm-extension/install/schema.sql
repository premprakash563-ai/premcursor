-- Reference DDL for Business Service CRM custom tables (MySQL 8 / MariaDB 10.4+)
-- SuiteCRM Quick Repair may also create module tables from vardefs; this file documents full schema.

CREATE TABLE IF NOT EXISTS bs_services (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  price DECIMAL(26,6) NULL,
  gst_percent DECIMAL(5,2) DEFAULT 18.00,
  delivery_days INT DEFAULT 7,
  category VARCHAR(100) NULL,
  status VARCHAR(50) DEFAULT 'active',
  required_documents TEXT NULL,
  KEY idx_bs_services_status (status),
  KEY idx_bs_services_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_orders (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  account_id CHAR(36) NULL,
  contact_id CHAR(36) NULL,
  service_id CHAR(36) NULL,
  status VARCHAR(50) DEFAULT 'application_submitted',
  payment_status VARCHAR(50) DEFAULT 'pending',
  amount DECIMAL(26,6) NULL,
  gst_amount DECIMAL(26,6) NULL,
  total_amount DECIMAL(26,6) NULL,
  assigned_at DATETIME NULL,
  is_new_enquiry TINYINT(1) DEFAULT 1,
  gstin VARCHAR(20) NULL,
  pan VARCHAR(20) NULL,
  source VARCHAR(50) DEFAULT 'admin',
  application_json LONGTEXT NULL,
  KEY idx_bs_orders_assigned_date (assigned_user_id, date_entered),
  KEY idx_bs_orders_status (status),
  KEY idx_bs_orders_name (name),
  KEY idx_bs_orders_pan (pan),
  KEY idx_bs_orders_gstin (gstin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_order_documents (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  order_id CHAR(36) NULL,
  doc_code VARCHAR(50) NULL,
  filename VARCHAR(255) NULL,
  file_path VARCHAR(500) NULL,
  mime_type VARCHAR(100) NULL,
  status VARCHAR(50) DEFAULT 'uploaded',
  reviewer_id CHAR(36) NULL,
  review_note TEXT NULL,
  document_id CHAR(36) NULL,
  KEY idx_bs_od_order (order_id),
  KEY idx_bs_od_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_status_history (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  order_id CHAR(36) NULL,
  from_status VARCHAR(50) NULL,
  to_status VARCHAR(50) NULL,
  note TEXT NULL,
  KEY idx_bs_sh_order (order_id, date_entered)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_assignment_log (
  id CHAR(36) NOT NULL PRIMARY KEY,
  order_id CHAR(36) NULL,
  user_id CHAR(36) NULL,
  reason VARCHAR(50) NULL,
  daily_count_at_assign INT DEFAULT 0,
  date_entered DATETIME NULL,
  deleted TINYINT(1) DEFAULT 0,
  KEY idx_bs_al_user_date (user_id, date_entered),
  KEY idx_bs_al_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_payments (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  order_id CHAR(36) NULL,
  gateway VARCHAR(50) NULL,
  gateway_ref VARCHAR(100) NULL,
  amount DECIMAL(26,6) NULL,
  status VARCHAR(50) DEFAULT 'created',
  paid_at DATETIME NULL,
  raw_payload LONGTEXT NULL,
  UNIQUE KEY uq_bs_pay_gateway_ref (gateway, gateway_ref),
  KEY idx_bs_pay_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_invoices (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  order_id CHAR(36) NULL,
  invoice_no VARCHAR(50) NULL,
  invoice_date DATE NULL,
  taxable DECIMAL(26,6) NULL,
  cgst DECIMAL(26,6) NULL,
  sgst DECIMAL(26,6) NULL,
  igst DECIMAL(26,6) NULL,
  total DECIMAL(26,6) NULL,
  qr_payload TEXT NULL,
  pdf_path VARCHAR(500) NULL,
  UNIQUE KEY uq_bs_inv_no (invoice_no),
  KEY idx_bs_inv_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_notifications (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  modified_user_id CHAR(36) NULL,
  created_by CHAR(36) NULL,
  description TEXT NULL,
  deleted TINYINT(1) DEFAULT 0,
  assigned_user_id CHAR(36) NULL,
  recipient_type VARCHAR(20) NULL,
  recipient_id CHAR(36) NULL,
  event_code VARCHAR(50) NULL,
  message TEXT NULL,
  payload_json LONGTEXT NULL,
  is_read TINYINT(1) DEFAULT 0,
  related_order_id CHAR(36) NULL,
  KEY idx_bs_notif_recipient (recipient_type, recipient_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_chat_threads (
  id CHAR(36) NOT NULL PRIMARY KEY,
  order_id CHAR(36) NULL,
  contact_id CHAR(36) NULL,
  employee_id CHAR(36) NULL,
  last_message_at DATETIME NULL,
  deleted TINYINT(1) DEFAULT 0,
  date_entered DATETIME NULL,
  UNIQUE KEY uq_bs_chat_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_chat_messages (
  id CHAR(36) NOT NULL PRIMARY KEY,
  thread_id CHAR(36) NULL,
  sender_type VARCHAR(20) NULL,
  sender_id CHAR(36) NULL,
  body TEXT NULL,
  attachment_path VARCHAR(500) NULL,
  mime_type VARCHAR(100) NULL,
  is_read TINYINT(1) DEFAULT 0,
  date_entered DATETIME NULL,
  deleted TINYINT(1) DEFAULT 0,
  KEY idx_bs_msg_thread (thread_id, date_entered)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bs_leave (
  id CHAR(36) NOT NULL PRIMARY KEY,
  name VARCHAR(255) NULL,
  assigned_user_id CHAR(36) NULL,
  date_start DATE NULL,
  date_end DATE NULL,
  status VARCHAR(50) DEFAULT 'pending',
  deleted TINYINT(1) DEFAULT 0,
  date_entered DATETIME NULL,
  date_modified DATETIME NULL,
  KEY idx_bs_leave_user (assigned_user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee meta on users_cstm (SuiteCRM custom fields). Run via Studio or:
ALTER TABLE users_cstm
  ADD COLUMN IF NOT EXISTS availability_c VARCHAR(50) DEFAULT 'online',
  ADD COLUMN IF NOT EXISTS last_assigned_at_c DATETIME NULL,
  ADD COLUMN IF NOT EXISTS max_enquiries_override_c INT NULL;
