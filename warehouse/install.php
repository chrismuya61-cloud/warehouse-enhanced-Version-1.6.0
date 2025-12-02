<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

// 1. Core Tables (Ensure existence)
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
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// 2. NEW: Bin Locations
if (!$CI->db->table_exists(db_prefix() . 'wh_bin_locations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "wh_bin_locations` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` int(11) NOT NULL,
      `bin_name` varchar(100) NOT NULL,
      `description` text NULL,
      PRIMARY KEY (`id`), INDEX (`warehouse_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// 3. NEW: Stock Take
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

// 4. Add Bin Location & Decimal fixes to existing tables
$tables_to_update = ['inventory_manage', 'goods_receipt_detail', 'goods_delivery_detail', 'goods_transaction_detail'];
foreach ($tables_to_update as $table) {
    if (!$CI->db->field_exists('bin_location_id', db_prefix() . $table)) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . $table . '` ADD COLUMN `bin_location_id` INT(11) DEFAULT 0;');
    }
}

// 5. Ensure Purchase Price is Decimal
if ($CI->db->table_exists(db_prefix() . 'items')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "items` MODIFY COLUMN `purchase_price` DECIMAL(15,2) DEFAULT 0.00");
}

// Options
if (get_option('next_stock_take_number') == '') add_option('next_stock_take_number', 1, 1);
if (get_option('stock_take_number_prefix') == '') add_option('stock_take_number_prefix', 'ST-', 1);