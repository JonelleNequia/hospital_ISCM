-- Evergreen Medical Hospital ISCM schema
-- Import this via MySQL Workbench or the mysql CLI.

CREATE DATABASE IF NOT EXISTS hospital_scms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_scms;

-- ROLES & USERS --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES
  ('Admin'),
  ('Procurement'),
  ('Receiver'),
  ('InventoryClerk'),
  ('Pharmacy'),
  ('DeptReq'),
  ('Auditor')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('ACTIVE','DISABLED') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- MASTER DATA ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO departments (code,name) VALUES
  ('ER','Emergency Room'),
  ('ICU','Intensive Care'),
  ('PHARM','Pharmacy Services'),
  ('SURG','Surgical Ward')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE IF NOT EXISTS locations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  type ENUM('STORES','WARD','PHARMACY','QUARANTINE','OTHER') NOT NULL DEFAULT 'STORES',
  status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO locations (code,name,type) VALUES
  ('MAIN','Main Warehouse','STORES'),
  ('PHARM','Central Pharmacy','PHARMACY'),
  ('ER','Emergency Room Store','WARD'),
  ('QUAR','Quarantine Cage','QUARANTINE')
ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type);

CREATE TABLE IF NOT EXISTS suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact VARCHAR(120),
  phone VARCHAR(64),
  email VARCHAR(150),
  address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO suppliers (name,contact,phone,email,address) VALUES
  ('MediCore Distribution','Jane Santos','(02) 8123-4567','sales@medicore.local','31 Wack Wack, Mandaluyong'),
  ('Northwind Pharma','Luis Dizon','(02) 8989-1111','hello@northwind.local','Bonifacio Global City'),
  ('LifeLine Surgical','Cathy Reyes','(045) 998-7766','orders@lifeline.local','Angeles, Pampanga')
ON DUPLICATE KEY UPDATE contact = VALUES(contact), phone = VALUES(phone),
  email = VALUES(email), address = VALUES(address);

CREATE TABLE IF NOT EXISTS items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100),
  uom VARCHAR(20) NOT NULL DEFAULT 'EA',
  is_controlled TINYINT(1) NOT NULL DEFAULT 0,
  expiry_required TINYINT(1) NOT NULL DEFAULT 1,
  lot_tracking TINYINT(1) NOT NULL DEFAULT 1,
  min_stock INT UNSIGNED NOT NULL DEFAULT 0,
  max_stock INT UNSIGNED NOT NULL DEFAULT 0,
  reorder_point INT UNSIGNED NOT NULL DEFAULT 0,
  lead_time_days INT UNSIGNED NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO items (sku,name,category,uom,is_controlled,expiry_required,lot_tracking,min_stock,max_stock,reorder_point,lead_time_days,active)
VALUES
  ('MED-0001','Paracetamol 500mg Tablet','Pharmacy','TAB',0,1,1,100,1000,200,7,1),
  ('MED-0002','IV Cannula 22G','Medical Supplies','EA',0,0,0,50,400,80,14,1),
  ('MED-0003','N95 Respirator','PPE','EA',0,0,0,200,2000,400,10,1)
ON DUPLICATE KEY UPDATE
  category = VALUES(category),
  uom = VALUES(uom),
  is_controlled = VALUES(is_controlled),
  expiry_required = VALUES(expiry_required),
  lot_tracking = VALUES(lot_tracking),
  min_stock = VALUES(min_stock),
  max_stock = VALUES(max_stock),
  reorder_point = VALUES(reorder_point),
  lead_time_days = VALUES(lead_time_days),
  active = VALUES(active);

-- PURCHASING ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchase_orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  po_no VARCHAR(50) NOT NULL UNIQUE,
  supplier_id INT UNSIGNED NOT NULL,
  status ENUM('DRAFT','SUBMITTED','PARTIAL_RECEIVED','RECEIVED','CANCELLED') NOT NULL DEFAULT 'SUBMITTED',
  expected_date DATE NULL,
  notes TEXT,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
    ON UPDATE CASCADE,
  CONSTRAINT fk_po_user FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS po_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  po_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  qty_ordered INT UNSIGNED NOT NULL,
  qty_received INT UNSIGNED NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_poi_po FOREIGN KEY (po_id) REFERENCES purchase_orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_poi_item FOREIGN KEY (item_id) REFERENCES items(id)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shipments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  po_id INT UNSIGNED NOT NULL,
  carrier VARCHAR(120),
  tracking_no VARCHAR(120),
  status ENUM('CREATED','IN_TRANSIT','OUT_FOR_DELIVERY','DELAYED','DELIVERED','CANCELLED') NOT NULL DEFAULT 'CREATED',
  eta DATETIME NULL,
  shipped_at DATETIME NULL,
  delivered_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ship_po FOREIGN KEY (po_id) REFERENCES purchase_orders(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_ship_po ON shipments (po_id);
CREATE INDEX idx_ship_tracking ON shipments (tracking_no);

CREATE TABLE IF NOT EXISTS shipment_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  event_time DATETIME NOT NULL,
  remarks VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ship_events FOREIGN KEY (shipment_id) REFERENCES shipments(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_ship_events_time ON shipment_events (shipment_id, event_time);

-- RECEIVING / STOCK --------------------------------------------------------
CREATE TABLE IF NOT EXISTS goods_receipts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grn_no VARCHAR(50) NOT NULL UNIQUE,
  po_id INT UNSIGNED NOT NULL,
  received_by INT UNSIGNED NOT NULL,
  status ENUM('DRAFT','POSTED','VOID') NOT NULL DEFAULT 'POSTED',
  remarks VARCHAR(255),
  received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_grn_po FOREIGN KEY (po_id) REFERENCES purchase_orders(id),
  CONSTRAINT fk_grn_user FOREIGN KEY (received_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS goods_receipt_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grn_id INT UNSIGNED NOT NULL,
  po_item_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  lot_no VARCHAR(80) NOT NULL DEFAULT '',
  expiry_date DATE NULL,
  qty_received INT UNSIGNED NOT NULL,
  location_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_grni_grn FOREIGN KEY (grn_id) REFERENCES goods_receipts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_grni_poi FOREIGN KEY (po_item_id) REFERENCES po_items(id),
  CONSTRAINT fk_grni_item FOREIGN KEY (item_id) REFERENCES items(id),
  CONSTRAINT fk_grni_loc FOREIGN KEY (location_id) REFERENCES locations(id)
) ENGINE=InnoDB;
CREATE INDEX idx_grni_grn_item ON goods_receipt_items (grn_id, item_id);

CREATE TABLE IF NOT EXISTS grn_discrepancies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grn_id INT UNSIGNED NOT NULL,
  grn_item_id INT UNSIGNED NULL,
  po_item_id INT UNSIGNED NULL,
  item_id INT UNSIGNED NULL,
  qty_rejected INT UNSIGNED NOT NULL,
  reason VARCHAR(40) NOT NULL,
  disposition VARCHAR(40) NOT NULL,
  notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_grn_disc_grn FOREIGN KEY (grn_id) REFERENCES goods_receipts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_grn_disc_item FOREIGN KEY (grn_item_id) REFERENCES goods_receipt_items(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_grn_disc_po_item FOREIGN KEY (po_item_id) REFERENCES po_items(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_grn_disc_item_id FOREIGN KEY (item_id) REFERENCES items(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS item_lots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  lot_no VARCHAR(80) NOT NULL DEFAULT '',
  expiry_date DATE NULL,
  location_id INT UNSIGNED NOT NULL,
  qty_on_hand INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lot_item FOREIGN KEY (item_id) REFERENCES items(id),
  CONSTRAINT fk_lot_loc FOREIGN KEY (location_id) REFERENCES locations(id),
  UNIQUE KEY uniq_item_lot_loc (item_id, lot_no, location_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_movements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  lot_id INT UNSIGNED NULL,
  movement_type VARCHAR(32) NOT NULL,
  qty INT UNSIGNED NOT NULL,
  from_location_id INT UNSIGNED NULL,
  to_location_id INT UNSIGNED NULL,
  ref_type VARCHAR(32) NULL,
  ref_id INT UNSIGNED NULL,
  moved_by INT UNSIGNED NULL,
  moved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_item FOREIGN KEY (item_id) REFERENCES items(id),
  CONSTRAINT fk_mov_lot FOREIGN KEY (lot_id) REFERENCES item_lots(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_mov_from FOREIGN KEY (from_location_id) REFERENCES locations(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_mov_to FOREIGN KEY (to_location_id) REFERENCES locations(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_mov_user FOREIGN KEY (moved_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE INDEX idx_mov_item_time ON inventory_movements (item_id, moved_at);

-- DEPARTMENT REQUESTS / ISSUANCE ------------------------------------------
CREATE TABLE IF NOT EXISTS requisitions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  req_no VARCHAR(50) NOT NULL UNIQUE,
  department_id INT UNSIGNED NOT NULL,
  requested_by INT UNSIGNED NOT NULL,
  status ENUM('SUBMITTED','APPROVED','PARTIALLY_ISSUED','FULFILLED','CANCELLED') NOT NULL DEFAULT 'SUBMITTED',
  priority ENUM('LOW','NORMAL','HIGH') NOT NULL DEFAULT 'NORMAL',
  remarks VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_req_dept FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_req_user FOREIGN KEY (requested_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS requisition_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  requisition_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  qty_requested INT UNSIGNED NOT NULL,
  qty_issued INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_req_items_header FOREIGN KEY (requisition_id) REFERENCES requisitions(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_req_items_item FOREIGN KEY (item_id) REFERENCES items(id)
) ENGINE=InnoDB;
CREATE INDEX idx_req_items_req ON requisition_items (requisition_id);

CREATE TABLE IF NOT EXISTS issues (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_no VARCHAR(50) NOT NULL UNIQUE,
  requisition_id INT UNSIGNED NOT NULL,
  issued_by INT UNSIGNED NOT NULL,
  issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_issue_req FOREIGN KEY (requisition_id) REFERENCES requisitions(id),
  CONSTRAINT fk_issue_user FOREIGN KEY (issued_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS issue_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  lot_id INT UNSIGNED NOT NULL,
  qty_issued INT UNSIGNED NOT NULL,
  CONSTRAINT fk_issue_items_header FOREIGN KEY (issue_id) REFERENCES issues(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_issue_items_item FOREIGN KEY (item_id) REFERENCES items(id),
  CONSTRAINT fk_issue_items_lot FOREIGN KEY (lot_id) REFERENCES item_lots(id)
) ENGINE=InnoDB;

-- BILLING REPORTS: audit table for deliveries to external billing systems
CREATE TABLE IF NOT EXISTS billing_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grn_id INT UNSIGNED NOT NULL,
  sent_at DATETIME NOT NULL,
  status ENUM('PENDING','SENT','FAILED') NOT NULL DEFAULT 'PENDING',
  http_code INT NULL,
  response TEXT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_billing_grn FOREIGN KEY (grn_id) REFERENCES goods_receipts(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- END OF FILE --------------------------------------------------------------
