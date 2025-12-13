<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_140 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        
        // 1. License Management Table
        if (!$CI->db->table_exists(db_prefix() . 'wh_licenses')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'wh_licenses` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `client_id` int(11) NOT NULL,
              `invoice_id` int(11) NOT NULL,
              `item_id` int(11) NOT NULL,
              `serial_number` varchar(255) DEFAULT NULL,
              `license_key` text NOT NULL,
              `license_type` varchar(50) NOT NULL COMMENT "temporary, permanent",
              `issue_date` date NOT NULL,
              `expiry_date` date DEFAULT NULL,
              `description` text DEFAULT NULL,
              `created_at` datetime NOT NULL,
              `addedfrom` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // 2. Mobile Scanner Session Tables
        if (!$CI->db->table_exists(db_prefix() . 'wh_stock_take_sessions')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'wh_stock_take_sessions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `token` varchar(64) NOT NULL,
              `staff_id` int(11) NOT NULL,
              `created_at` datetime NOT NULL,
              `is_active` int(1) NOT NULL DEFAULT 1,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'wh_stock_take_scans')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'wh_stock_take_scans` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `session_id` int(11) NOT NULL,
              `barcode` varchar(100) NOT NULL,
              `qty` decimal(15,2) NOT NULL DEFAULT 1,
              `scanned_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        // 3. Inter-CRM Transfer Columns
        if (!$CI->db->field_exists('wh_remote_crm_url', db_prefix() . 'warehouse')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'warehouse` ADD COLUMN `wh_remote_crm_url` VARCHAR(255) NULL DEFAULT NULL;');
        }
        if (!$CI->db->field_exists('wh_remote_crm_token', db_prefix() . 'warehouse')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'warehouse` ADD COLUMN `wh_remote_crm_token` VARCHAR(255) NULL DEFAULT NULL;');
        }
        if (!$CI->db->field_exists('wh_remote_warehouse_code', db_prefix() . 'warehouse')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'warehouse` ADD COLUMN `wh_remote_warehouse_code` VARCHAR(50) NULL DEFAULT NULL COMMENT "Code of the warehouse in the remote CRM";');
        }

        // 4. Bin Location for Inventory
        if (!$CI->db->field_exists('bin_location', db_prefix() . 'inventory_manage')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'inventory_manage` ADD COLUMN `bin_location` VARCHAR(100) NULL DEFAULT NULL AFTER `inventory_number`;');
        }
    }
}