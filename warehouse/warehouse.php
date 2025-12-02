<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Warehouse
Description: Enterprise Inventory Management (v1.6.0)
Version: 1.6.0
Author: GreenTech Solutions
*/

define('WAREHOUSE_MODULE_NAME', 'warehouse');
define('WAREHOUSE_MODULE_UPLOAD_FOLDER', module_dir_path(WAREHOUSE_MODULE_NAME, 'uploads'));
define('COMMODITY_ERROR', module_dir_path(WAREHOUSE_MODULE_NAME, 'uploads/import_item_error/'));
define('COMMODITY_EXPORT', module_dir_path(WAREHOUSE_MODULE_NAME, 'uploads/export_item/'));
define('WAREHOUSE_PRINT_ITEM', 'modules/warehouse/uploads/print_item/');

hooks()->add_action('admin_init', 'warehouse_permissions');
hooks()->add_action('admin_init', 'warehouse_menu_items');
hooks()->add_action('app_admin_head', 'warehouse_add_head_components');
hooks()->add_action('app_admin_footer', 'warehouse_load_js');

// Integration Hook: Create Receipt when PO is approved
hooks()->add_action('after_purchase_order_approve', 'wh_automations_create_goods_receipt_from_po');
// Integration Hook: Create Delivery when Invoice is created
hooks()->add_action('after_invoice_added', 'warehouse_create_goods_delivery');

function warehouse_permissions() {
    $caps = ['view', 'view_own', 'create', 'edit', 'delete'];
    register_staff_capabilities('warehouse_item', $caps, _l('warehouse_items'));
    register_staff_capabilities('wh_stock_import', $caps, _l('stock_import'));
    register_staff_capabilities('wh_stock_export', $caps, _l('stock_export'));
    register_staff_capabilities('wh_stock_take', $caps, _l('wh_stock_take'));
    register_staff_capabilities('wh_dashboard', ['view'], _l('wh_dashboard'));
    register_staff_capabilities('wh_setting', $caps, _l('warehouse_settings'));
}

function warehouse_menu_items() {
    $CI = &get_instance();
    if (has_permission('warehouse_item', '', 'view') || has_permission('wh_dashboard', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('warehouse', [
            'name' => _l('warehouse'),
            'icon' => 'fa fa-snowflake',
            'position' => 30,
        ]);
        
        if(has_permission('wh_dashboard', '', 'view')){
             $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wh_dashboard', 'name' => _l('wh_dashboard'), 'href' => admin_url('warehouse/visual_dashboard'), 'position' => 1
            ]);
        }
        
        $CI->app_menu->add_sidebar_children_item('warehouse', [
            'slug' => 'wa_commodity_list', 'name' => _l('items'), 'href' => admin_url('warehouse/commodity_list'), 'position' => 5
        ]);
        
        $CI->app_menu->add_sidebar_children_item('warehouse', [
            'slug' => 'wa_manage_goods_receipt', 'name' => _l('stock_import'), 'href' => admin_url('warehouse/manage_purchase'), 'position' => 10
        ]);

        $CI->app_menu->add_sidebar_children_item('warehouse', [
            'slug' => 'wa_manage_goods_delivery', 'name' => _l('stock_export'), 'href' => admin_url('warehouse/manage_delivery'), 'position' => 15
        ]);

        if(has_permission('wh_stock_take', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_stock_take', 'name' => _l('wh_stock_take'), 'href' => admin_url('warehouse/manage_stock_take'), 'position' => 20
            ]);
        }
        
        $CI->app_menu->add_sidebar_children_item('warehouse', [
            'slug' => 'wa_setting', 'name' => _l('settings'), 'href' => admin_url('warehouse/setting'), 'position' => 30
        ]);
    }
}

function warehouse_load_js(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (strpos($viewuri, '/admin/warehouse/visual_dashboard') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/dashboard/visual_dashboard.js.php').'?v=' . time().'"></script>';
    }
    // Load shared JS for other pages
    if (strpos($viewuri, '/admin/warehouse') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
    }
}

function warehouse_add_head_components(){
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/styles.css') . '" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '" rel="stylesheet" type="text/css" />';
}

function wh_automations_create_goods_receipt_from_po($pur_order_id) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    // Automate Receipt creation
    $CI->warehouse_model->create_goods_receipt_from_po($pur_order_id);
}

function warehouse_create_goods_delivery($invoice_id) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(get_option('auto_create_goods_delivery') == 1){
        $CI->warehouse_model->auto_create_goods_delivery_with_invoice($invoice_id);
    }
}