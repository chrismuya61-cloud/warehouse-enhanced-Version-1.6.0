<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// --------------------------------------------------------------------------
// 1. RESTORED CORE TABLES (Required for Module Functionality)
// --------------------------------------------------------------------------

// Warehouse Master Table
if (!$CI->db->table_exists(db_prefix() . 'warehouse')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "warehouse` (
      `warehouse_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_code` varchar(100) NULL,
      `warehouse_name` text NULL,
      `warehouse_address` text NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      `city` TEXT NULL,
      `state` TEXT NULL,
      `zip_code` TEXT NULL,
      `country` TEXT NULL,
      `hide_warehouse_when_out_of_stock` INT(11) NULL DEFAULT '0' COMMENT '1: yes 0: no',
      PRIMARY KEY (`warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Settings Tables (Commodity Type, Unit, Size, etc.)
$settings_tables = [
    'ware_commodity_type' => 'commodity_type_id',
    'ware_unit_type' => 'unit_type_id',
    'ware_size_type' => 'size_type_id',
    'ware_style_type' => 'style_type_id',
    'ware_body_type' => 'body_type_id',
    'wh_brand' => 'id',
    'wh_model' => 'id',
    'wh_series' => 'id',
];

foreach ($settings_tables as $table => $pk) {
    if (!$CI->db->table_exists(db_prefix() . $table)) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . $table . '` (
          `' . $pk . '` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` text NULL,
          `code` varchar(100) NULL,
          `order` int(10) NULL,
          `display` int(1) NULL DEFAULT 1,
          `note` text NULL,
          PRIMARY KEY (`' . $pk . '`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }
}

// Approval & Activity Logs
if (!$CI->db->table_exists(db_prefix() . 'wh_approval_setting')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_approval_setting` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `related` VARCHAR(255) NOT NULL,
    `setting` LONGTEXT NOT NULL,
    PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_approval_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_approval_details` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `rel_id` INT(11) NOT NULL,
      `rel_type` VARCHAR(45) NOT NULL,
      `staffid` VARCHAR(45) NULL,
      `approve` VARCHAR(45) NULL,
      `note` TEXT NULL,
      `date` DATETIME NULL,
      `approve_action` VARCHAR(255) NULL,
      `reject_action` VARCHAR(255) NULL,
      `approve_value` VARCHAR(255) NULL,
      `reject_value` VARCHAR(255) NULL,
      `staff_approve` INT(11) NULL,
      `action` VARCHAR(45) NULL,
      `sender` INT(11) NULL,
      `date_send` DATETIME NULL,
      PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_activity_log')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wh_activity_log` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `rel_id` INT(11) NOT NULL,
      `rel_type` VARCHAR(45) NOT NULL,
      `staffid` INT(11) NULL,
      `date` DATETIME NULL,
      `note` TEXT NULL,
      PRIMARY KEY (`id`));');
}

// --------------------------------------------------------------------------
// 2. TRANSACTIONAL TABLES (Receipts, Delivery, Packing Lists, etc.)
// --------------------------------------------------------------------------

if (!$CI->db->table_exists(db_prefix() . 'goods_receipt')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_receipt` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `supplier_code` varchar(100) NULL,
      `supplier_name` text NULL,
      `deliver_name` text NULL,
      `buyer_id` int(11) NULL,
      `description` text NULL,
      `pr_order_id` int(11) NULL,
      `date_c` date NULL,
      `date_add` date NULL,
      `goods_receipt_code` varchar(100) NULL,
      `warehouse_id` int(11) NULL,
      `total_tax_money` DECIMAL(15,2) DEFAULT 0.00,
      `total_goods_money` DECIMAL(15,2) DEFAULT 0.00,
      `value_of_inventory` DECIMAL(15,2) DEFAULT 0.00,
      `total_money` DECIMAL(15,2) DEFAULT 0.00,
      `approval` INT(11) NULL DEFAULT 0,
      `addedfrom` INT(11) NULL,
      `expiry_date` DATE NULL,
      `invoice_no` text NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'goods_delivery')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goods_delivery` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `rel_type` int(11) NULL,
      `rel_document` int(11) NULL,
      `customer_code` text NULL,
      `customer_name` varchar(100) NULL,
      `to_` varchar(100) NULL,
      `address` varchar(100) NULL,
      `description` text NULL,
      `staff_id` int(11) NULL,
      `date_c` date NULL,
      `date_add` date NULL,
      `goods_delivery_code` varchar(100) NULL,
      `warehouse_id` int(11) NULL,
      `total_money` varchar(200) NULL,
      `total_discount` varchar(100) NULL,
      `after_discount` varchar(100) NULL,
      `approval` INT(11) NULL DEFAULT 0,
      `addedfrom` INT(11) NULL,
      `delivery_status` VARCHAR(100) NULL DEFAULT 'ready_for_packing',
      `shipping_fee` DECIMAL(15,2) NULL DEFAULT 0.00,
      `additional_discount` DECIMAL(15,2) NULL DEFAULT 0.00,
      `sub_total` DECIMAL(15,2) NULL DEFAULT 0.00,
      `type_of_delivery` VARCHAR(100) NULL DEFAULT 'total',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'internal_delivery_note')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "internal_delivery_note` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `internal_delivery_name` text NULL,
      `description` text NULL,
      `staff_id` int(11) NULL,
      `date_c` date NULL,
      `date_add` date NULL,
      `internal_delivery_code` varchar(100) NULL,
      `approval` INT(11) NULL DEFAULT 0,
      `addedfrom` INT(11) NULL,
      `total_amount` decimal(15,2) null,
      `datecreated` datetime null,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_loss_adjustment')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_loss_adjustment` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `type` varchar(15) NULL,
      `addfrom` int(11) NULL,
      `reason` LONGTEXT NULL,
      `time` datetime NULL,
      `date_create` date NOT NULL,
      `status` int NOT NULL,
      `warehouses` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_packing_lists')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_packing_lists` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `delivery_note_id` INT(11) NULL,
        `packing_list_number` VARCHAR(100) NULL,
        `packing_list_name` VARCHAR(200) NULL,
        `width` DECIMAL(15,2) NULL DEFAULT '0.00',
        `height` DECIMAL(15,2) NULL DEFAULT '0.00',
        `lenght` DECIMAL(15,2) NULL DEFAULT '0.00',
        `weight` DECIMAL(15,2) NULL DEFAULT '0.00',
        `volume` DECIMAL(15,2) NULL DEFAULT '0.00',
        `clientid` INT(11) NULL,
        `subtotal` DECIMAL(15,2) NULL DEFAULT '0.00',
        `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
        `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
        `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
        `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
        `billing_street` varchar(200) DEFAULT NULL,
        `billing_city` varchar(100) DEFAULT NULL,
        `billing_state` varchar(100) DEFAULT NULL,
        `billing_zip` varchar(100) DEFAULT NULL,
        `billing_country` int(11) DEFAULT NULL,
        `shipping_street` varchar(200) DEFAULT NULL,
        `shipping_city` varchar(100) DEFAULT NULL,
        `shipping_state` varchar(100) DEFAULT NULL,
        `shipping_zip` varchar(100) DEFAULT NULL,
        `shipping_country` int(11) DEFAULT NULL,
        `client_note` TEXT NULL,
        `admin_note` TEXT NULL,
        `approval` INT(11) NULL DEFAULT 0,
        `datecreated` DATETIME NULL,
        `staff_id` INT(11) NULL,
        `delivery_status` VARCHAR(100) NULL DEFAULT 'wh_ready_to_deliver',
        `shipping_fee` DECIMAL(15,2) NULL DEFAULT '0.00',
        `type_of_packing_list` VARCHAR(100) NULL DEFAULT 'total',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// --------------------------------------------------------------------------
// 3. 1.6.0 ENHANCEMENTS (Bin Locations & Stock Take)
// --------------------------------------------------------------------------

if (!$CI->db->table_exists(db_prefix() . 'wh_bin_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_bin_locations` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` int(11) NOT NULL,
      `bin_name` varchar(100) NOT NULL,
      `description` text NULL,
      PRIMARY KEY (`id`), INDEX (`warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_stock_take')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_stock_take` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `reference_no` varchar(100) NOT NULL,
      `warehouse_id` int(11) NOT NULL,
      `stock_take_date` date NOT NULL,
      `status` int(1) NOT NULL DEFAULT 0,
      `created_by` int(11) NOT NULL,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wh_stock_take_inventory')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_stock_take_inventory` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `stock_take_id` int(11) NOT NULL,
      `commodity_id` int(11) NOT NULL,
      `bin_location_id` int(11) DEFAULT 0,
      `system_quantity` DECIMAL(15,2) DEFAULT 0.00,
      `physical_quantity` DECIMAL(15,2) DEFAULT 0.00,
      `adjustment_type` varchar(50) NULL,
      `adjustment_value` DECIMAL(15,2) DEFAULT 0.00,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// --------------------------------------------------------------------------
// 4. COLUMN UPDATES & ALTERATIONS
// --------------------------------------------------------------------------

// Inventory Management Columns
if (!$CI->db->table_exists(db_prefix() . 'inventory_manage')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "inventory_manage` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` int(11) NOT NULL,
      `commodity_id` int(11) NOT NULL,
      `inventory_number` varchar(100) NULL,
      `date_manufacture` date NULL,
      `expiry_date` date NULL,
      `lot_number` varchar(100) NULL,
      `purchase_price` DECIMAL(15,2) DEFAULT 0.00,
      PRIMARY KEY (`id`, `commodity_id`, `warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Add bin location to relevant tables
$tables_to_update = ['inventory_manage', 'goods_receipt_detail', 'goods_delivery_detail', 'goods_transaction_detail'];
foreach ($tables_to_update as $table) {
    if ($CI->db->table_exists(db_prefix() . $table) && !$CI->db->field_exists('bin_location_id', db_prefix() . $table)) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . $table . '` ADD COLUMN `bin_location_id` INT(11) DEFAULT 0;');
    }
}

// Add critical item columns
if ($CI->db->table_exists(db_prefix() . 'items')) {
    $columns = [
        'commodity_code' => 'varchar(100) NULL',
        'warehouse_id' => 'int(11) NULL',
        'origin' => 'varchar(100) NULL',
        'color_id' => 'int(11) NULL',
        'style_id' => 'int(11) NULL',
        'model_id' => 'int(11) NULL',
        'size_id' => 'int(11) NULL',
        'unit_id' => 'int(11) NULL',
        'sku_code' => 'varchar(200) NULL',
        'purchase_price' => 'decimal(15,2) NULL',
        'guarantee' => 'text NULL',
        'without_checking_warehouse' => 'int(11) NULL DEFAULT 0'
    ];

    foreach ($columns as $column => $definition) {
        if (!$CI->db->field_exists($column, db_prefix() . 'items')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "items` ADD COLUMN `$column` $definition;");
        }
    }
    
    // Fix purchase price type
    $CI->db->query("ALTER TABLE `" . db_prefix() . "items` MODIFY COLUMN `purchase_price` DECIMAL(15,2) DEFAULT 0.00");
}

// Default Options
if (get_option('next_stock_take_number') == '') add_option('next_stock_take_number', 1, 1);
if (get_option('stock_take_number_prefix') == '') add_option('stock_take_number_prefix', 'ST-', 1);
if (get_option('inventory_received_number_prefix') == '') add_option('inventory_received_number_prefix', 'NK', 1);
if (get_option('next_inventory_received_mumber') == '') add_option('next_inventory_received_mumber', 1, 1);
if (get_option('inventory_delivery_number_prefix') == '') add_option('inventory_delivery_number_prefix', 'XK', 1);
if (get_option('next_inventory_delivery_mumber') == '') add_option('next_inventory_delivery_mumber', 1, 1);

// --------------------------------------------------------------------------
// WARRANTY MANAGEMENT TABLES
// --------------------------------------------------------------------------

if (!$CI->db->table_exists(db_prefix() . 'wh_warranty_claims')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_warranty_claims` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `detail_id` int(11) NOT NULL COMMENT 'Link to goods_delivery_detail',
      `commodity_id` int(11) NOT NULL,
      `customer_id` int(11) NOT NULL,
      `claim_date` date NOT NULL,
      `issue_description` text NULL,
      `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, in_progress, resolved, rejected',
      `resolution_note` text NULL,
      `staff_id` int(11) NOT NULL,
      `date_created` datetime NOT NULL,
      `invoice_id` INT(11) DEFAULT 0,
      `expense_id` INT(11) DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Update table if it exists (Idempotency)
    if (!$CI->db->field_exists('invoice_id', db_prefix() . 'wh_warranty_claims')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_warranty_claims` ADD COLUMN `invoice_id` INT(11) DEFAULT 0;');
    }
    if (!$CI->db->field_exists('expense_id', db_prefix() . 'wh_warranty_claims')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_warranty_claims` ADD COLUMN `expense_id` INT(11) DEFAULT 0;');
    }
}

// --------------------------------------------------------------------------
// LICENSE MANAGEMENT TABLES
// --------------------------------------------------------------------------

if (!$CI->db->table_exists(db_prefix() . 'wh_licences')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_licences` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `serial_number` varchar(200) NOT NULL,
      `commodity_id` int(11) NOT NULL,
      `customer_id` int(11) NOT NULL,
      `invoice_id` int(11) DEFAULT 0,
      `delivery_id` int(11) DEFAULT 0,
      `licence_key` text NULL,
      `licence_type` varchar(50) DEFAULT 'temporary' COMMENT 'temporary, permanent',
      `validity_start_date` date NULL,
      `validity_end_date` date NULL,
      `status` varchar(50) DEFAULT 'draft' COMMENT 'draft, active, expired, suspended',
      `notes` text NULL,
      `date_created` datetime NOT NULL,
      `staff_id` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    // Columns needed for tracking billing status
    if (!$CI->db->field_exists('invoice_id', db_prefix() . 'wh_warranty_claims')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_warranty_claims` ADD COLUMN `invoice_id` INT(11) DEFAULT 0;');
    }
    if (!$CI->db->field_exists('expense_id', db_prefix() . 'wh_warranty_claims')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'wh_warranty_claims` ADD COLUMN `expense_id` INT(11) DEFAULT 0;');
    }
}
            
