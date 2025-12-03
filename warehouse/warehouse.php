<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Warehouse
Description: Enterprise Inventory Management (v1.6.0 Enhanced)
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

/**
 * Register module permissions
 */
function warehouse_permissions() {
    $caps = ['view', 'view_own', 'create', 'edit', 'delete'];
    $caps_view = ['view', 'view_own'];
    $caps_global = ['view'];

    // Core 1.6.0 Permissions
    register_staff_capabilities('warehouse_item', $caps, _l('warehouse_items'));
    register_staff_capabilities('wh_stock_import', $caps, _l('stock_import'));
    register_staff_capabilities('wh_stock_export', $caps, _l('stock_export'));
    register_staff_capabilities('wh_stock_take', $caps, _l('wh_stock_take'));
    register_staff_capabilities('wh_dashboard', ['view'], _l('wh_dashboard'));
    register_staff_capabilities('wh_setting', $caps, _l('warehouse_settings'));

    // RESTORED Permissions from 1.3.9
    register_staff_capabilities('wh_packing_list', $caps, _l('wh_packing_lists'));
    register_staff_capabilities('wh_internal_delivery_note', $caps, _l('internal_delivery_note'));
    register_staff_capabilities('wh_loss_adjustment', $caps, _l('loss_adjustment'));
    register_staff_capabilities('wh_receipt_return_order', $caps, _l('inventory_receipt_inventory_delivery_returns_goods'));
    register_staff_capabilities('wh_warehouse', $caps_global, _l('_warehouse'));
    register_staff_capabilities('wh_warehouse_history', $caps_global, _l('warehouse_history'));
    register_staff_capabilities('wh_report', $caps_global, _l('report'));
}

/**
 * Register sidebar menu items
 */
function warehouse_menu_items() {
    $CI = &get_instance();
    
    // Check if user has ANY warehouse permission
    if (has_permission('warehouse_item', '', 'view') || 
        has_permission('wh_dashboard', '', 'view') || 
        has_permission('wh_stock_import', '', 'view') || 
        has_permission('wh_stock_export', '', 'view') || 
        has_permission('wh_packing_list', '', 'view') || 
        has_permission('wh_internal_delivery_note', '', 'view') ||
        has_permission('wh_loss_adjustment', '', 'view') ||
        has_permission('wh_receipt_return_order', '', 'view') ||
        has_permission('wh_report', '', 'view')) {

        $CI->app_menu->add_sidebar_menu_item('warehouse', [
            'name' => _l('warehouse'),
            'icon' => 'fa fa-snowflake',
            'position' => 30,
        ]);
        
        // 1. Dashboard
        if(has_permission('wh_dashboard', '', 'view')){
             $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wh_dashboard', 'name' => _l('wh_dashboard'), 'href' => admin_url('warehouse/visual_dashboard'), 'position' => 1
            ]);
        }
        
        // 2. Items
        if (has_permission('warehouse_item', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_commodity_list', 'name' => _l('items'), 'href' => admin_url('warehouse/commodity_list'), 'position' => 5
            ]);
        }
        
        // 3. Stock Import
        if (has_permission('wh_stock_import', '', 'view') || has_permission('wh_stock_import', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_goods_receipt', 'name' => _l('stock_import'), 'href' => admin_url('warehouse/manage_purchase'), 'position' => 10
            ]);
        }

        // 4. Stock Export
        if (has_permission('wh_stock_export', '', 'view') || has_permission('wh_stock_export', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_goods_delivery', 'name' => _l('stock_export'), 'href' => admin_url('warehouse/manage_delivery'), 'position' => 15
            ]);
        }

        // 5. Packing Lists (RESTORED)
        if (has_permission('wh_packing_list', '', 'view') || has_permission('wh_packing_list', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_packing_list', 'name' => _l('wh_packing_lists'), 'href' => admin_url('warehouse/manage_packing_list'), 'position' => 16
            ]);
        }

        // 6. Internal Delivery (RESTORED)
        if (has_permission('wh_internal_delivery_note', '', 'view') || has_permission('wh_internal_delivery_note', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_internal_delivery', 'name' => _l('internal_delivery_note'), 'href' => admin_url('warehouse/manage_internal_delivery'), 'position' => 17
            ]);
        }

        // 7. Loss & Adjustment (RESTORED)
        if (has_permission('wh_loss_adjustment', '', 'view') || has_permission('wh_loss_adjustment', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_loss_adjustment', 'name' => _l('loss_adjustment'), 'href' => admin_url('warehouse/loss_adjustment'), 'position' => 18
            ]);
        }

        // 8. Order Returns (RESTORED)
        if (has_permission('wh_receipt_return_order', '', 'view') || has_permission('wh_receipt_return_order', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_order_return', 'name' => _l('inventory_receipt_inventory_delivery_returns_goods'), 'href' => admin_url('warehouse/manage_order_return'), 'position' => 19
            ]);
        }

        // 9. Stock Take
        if(has_permission('wh_stock_take', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_stock_take', 'name' => _l('wh_stock_take'), 'href' => admin_url('warehouse/manage_stock_take'), 'position' => 20
            ]);
        }

        // 10. Reports
        if (has_permission('wh_report', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_report', 'name' => _l('report'), 'href' => admin_url('warehouse/manage_report'), 'position' => 25
            ]);
        }
        
        // 11. Settings
        if(has_permission('wh_setting', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_setting', 'name' => _l('settings'), 'href' => admin_url('warehouse/setting'), 'position' => 30
            ]);
        }
    }
}

function warehouse_load_js(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (strpos($viewuri, '/admin/warehouse/visual_dashboard') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/dashboard/visual_dashboard.js.php').'?v=' . time().'"></script>';
    }
    // Load shared JS
    if (strpos($viewuri, '/admin/warehouse') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/signature_pad.min.js') . '"></script>';
    }
}

function warehouse_add_head_components(){
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/styles.css') . '" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/chosen.css') . '" rel="stylesheet" type="text/css" />';
    echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.js') . '"></script>';
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