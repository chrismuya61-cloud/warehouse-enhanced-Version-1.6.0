<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Warehouse
Description: Enterprise Inventory Management (v1.6.0 Enhanced & Nulled)
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

// Client Portal Hooks
hooks()->add_action('customers_navigation_end', 'init_shipment_portal_menu');
hooks()->add_action('app_customers_portal_head', 'warehouse_client_add_head_components');

// Integration Hooks
hooks()->add_action('after_purchase_order_approve', 'wh_automations_create_goods_receipt_from_po');
hooks()->add_action('after_invoice_added', 'warehouse_create_goods_delivery');
hooks()->add_action('invoice_marked_as_cancelled', 'wh_invoice_marked_as_cancelled');
hooks()->add_action('invoice_unmarked_as_cancelled', 'wh_invoice_unmarked_as_cancelled');
hooks()->add_action('after_invoice_updated', 'wh_update_goods_delivery');

// License Expiration Check
hooks()->add_action('after_cron_run', 'wh_check_licence_expiration');

function wh_check_licence_expiration() {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    $CI->warehouse_model->cron_check_licence_expiration();
}

/**
 * Register module permissions
 */
function warehouse_permissions() {
    $caps = ['view', 'view_own', 'create', 'edit', 'delete'];
    $caps_global = ['view'];

    // Core 1.6.0 Permissions
    register_staff_capabilities('warehouse_item', $caps, _l('warehouse_items'));
    register_staff_capabilities('wh_stock_import', $caps, _l('stock_import'));
    register_staff_capabilities('wh_stock_export', $caps, _l('stock_export'));
    register_staff_capabilities('wh_stock_take', $caps, _l('wh_stock_take'));
    register_staff_capabilities('wh_dashboard', ['view'], _l('wh_dashboard'));
    register_staff_capabilities('wh_setting', $caps, _l('warehouse_settings'));
    register_staff_capabilities('wh_warranty', $caps, _l('warranty_management'));
    register_staff_capabilities('wh_licences', $caps, _l('licence_management'));

    // RESTORED Permissions
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
        
        if(has_permission('wh_dashboard', '', 'view')){
             $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wh_dashboard', 'name' => _l('wh_dashboard'), 'href' => admin_url('warehouse/visual_dashboard'), 'position' => 1
            ]);
        }
        
        if (has_permission('warehouse_item', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_commodity_list', 'name' => _l('items'), 'href' => admin_url('warehouse/commodity_list'), 'position' => 5
            ]);
        }
        
        if (has_permission('wh_stock_import', '', 'view') || has_permission('wh_stock_import', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_goods_receipt', 'name' => _l('stock_import'), 'href' => admin_url('warehouse/manage_purchase'), 'position' => 10
            ]);
        }

        if (has_permission('wh_stock_export', '', 'view') || has_permission('wh_stock_export', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_goods_delivery', 'name' => _l('stock_export'), 'href' => admin_url('warehouse/manage_delivery'), 'position' => 15
            ]);
        }

        if (has_permission('wh_packing_list', '', 'view') || has_permission('wh_packing_list', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_packing_list', 'name' => _l('wh_packing_lists'), 'href' => admin_url('warehouse/manage_packing_list'), 'position' => 16
            ]);
        }

        if (has_permission('wh_internal_delivery_note', '', 'view') || has_permission('wh_internal_delivery_note', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_internal_delivery', 'name' => _l('internal_delivery_note'), 'href' => admin_url('warehouse/manage_internal_delivery'), 'position' => 17
            ]);
        }

        if (has_permission('wh_loss_adjustment', '', 'view') || has_permission('wh_loss_adjustment', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_loss_adjustment', 'name' => _l('loss_adjustment'), 'href' => admin_url('warehouse/loss_adjustment'), 'position' => 18
            ]);
        }

        if (has_permission('wh_receipt_return_order', '', 'view') || has_permission('wh_receipt_return_order', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_order_return', 'name' => _l('inventory_receipt_inventory_delivery_returns_goods'), 'href' => admin_url('warehouse/manage_order_return'), 'position' => 19
            ]);
        }

        if(has_permission('wh_stock_take', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_manage_stock_take', 'name' => _l('wh_stock_take'), 'href' => admin_url('warehouse/manage_stock_take'), 'position' => 20
            ]);
        }

        if (has_permission('wh_report', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_report', 'name' => _l('report'), 'href' => admin_url('warehouse/manage_report'), 'position' => 25
            ]);
        }
        
        if(has_permission('wh_setting', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('warehouse', [
                'slug' => 'wa_setting', 'name' => _l('settings'), 'href' => admin_url('warehouse/setting'), 'position' => 30
            ]);
        }
        if (has_permission('wh_warranty', '', 'view') || has_permission('wh_warranty', '', 'view_own')) {
    $CI->app_menu->add_sidebar_children_item('warehouse', [
        'slug'     => 'wa_warranty_management',
        'name'     => _l('warranty_management'),
        'icon'     => 'fa fa-shield', // Shield icon for warranty
        'href'     => admin_url('warehouse/warranty_dashboard'),
        'position' => 25, // Adjust position as needed
    ]);
}
        if (has_permission('wh_licences', '', 'view') || has_permission('wh_licences', '', 'view_own')) {
    $CI->app_menu->add_sidebar_children_item('warehouse', [
        'slug'     => 'wa_licence_management',
        'name'     => _l('licence_management'),
        'icon'     => 'fa fa-key',
        'href'     => admin_url('warehouse/licence_management'),
        'position' => 26,
    ]);
}
    }
}

function warehouse_load_js(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    // Dashboard
    if (strpos($viewuri, '/admin/warehouse/visual_dashboard') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/dashboard/visual_dashboard.js.php').'?v=' . time().'"></script>';
    }

    // General Warehouse Pages (Handsontable & Signature)
    if (strpos($viewuri, '/admin/warehouse') !== false) {
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/signature_pad.min.js') . '"></script>';
    }

    // Specific Page Loaders (Restored from 1.3.9 & New 1.6.0)
    if (strpos($viewuri, '/admin/warehouse/setting') !== false) {
        echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/manage_setting.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=approval_setting') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/approval_setting.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=brand') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/brand.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=model') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/model.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=series') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/series.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=warehouse_custom_fields') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/warehouse_custom_fields.js').'?v=' . time().'"></script>';
        if (strpos($viewuri, 'group=colors') !== false) echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/color.js').'?v=' . time().'"></script>';
    }

    if (strpos($viewuri, '/admin/warehouse/manage_purchase') !== false) { 
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/manage_purchase.js').'?v=' . time().'"></script>';
    }

    if (strpos($viewuri, '/admin/warehouse/manage_report') !== false) { 
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/stock_summary_report.js').'?v=' . time().'"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/inventory_valuation_report.js').'?v=' . time().'"></script>';
    }

    if (strpos($viewuri, '/admin/warehouse/manage_stock_take') !== false) { 
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/manage_stock_take.js').'?v=' . time().'"></script>';
    }

    if (strpos($viewuri, '/admin/warehouse/loss_adjustment') !== false) { 
        echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/loss_adjustment_manage.js').'?v=' . time().'"></script>';
    }

    // Lightbox for Items
    if (strpos($viewuri, '/admin/warehouse/view_commodity_detail') !== false || strpos($viewuri, '/admin/warehouse/commodity_list') !== false) { 
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/simplelightbox/simple-lightbox.min.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/simplelightbox/simple-lightbox.jquery.min.js') . '"></script>';
         echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/simplelightbox/masonry-layout-vanilla.min.js') . '"></script>';
    }
    function warehouse_load_js(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    // Add this block for Warranty pages
    if (strpos($viewuri, '/warehouse/warranty_list') !== false || 
        strpos($viewuri, '/warehouse/warranty_dashboard') !== false || 
        strpos($viewuri, '/warehouse/warranty_claims') !== false) {
        
        echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/js/warranty.js').'?v=' . time().'"></script>';
    }
}
function warehouse_add_head_components(){
    $viewuri = $_SERVER['REQUEST_URI'];

    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/styles.css') . '" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/chosen.css') . '" rel="stylesheet" type="text/css" />';
    echo '<script src="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.js') . '"></script>';

    if (strpos($viewuri, '/admin/warehouse/setting') !== false) {
        echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/body.css')  .'?v=' . time(). '"  rel="stylesheet" type="text/css" />';
        if (strpos($viewuri, 'group=approval_setting') !== false) echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/approval_setting.css')  .'?v=' . time(). '"  rel="stylesheet" type="text/css" />';
        if (strpos($viewuri, 'group=warehouse_custom_fields') !== false) echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/warehouse_custom_fields.css')  .'?v=' . time(). '"  rel="stylesheet" type="text/css" />';
    }

    if (strpos($viewuri, '/admin/warehouse/manage_report') !== false) {
        echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/report.css')  .'?v=' . time(). '"  rel="stylesheet" type="text/css" />';
    }
    
    if (strpos($viewuri, '/admin/warehouse/commodity_list') !== false || strpos($viewuri, '/admin/warehouse/view_commodity_detail') !== false) {
        echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/simplelightbox/simple-lightbox.min.css') . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/plugins/simplelightbox/masonry-layout-vanilla.min.css') . '"  rel="stylesheet" type="text/css" />';
    }
}

// --- Helper Functions & Automations ---

function wh_automations_create_goods_receipt_from_po($pur_order_id) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    $CI->warehouse_model->auto_create_goods_receipt_with_purchase_order(['id' => $pur_order_id]);
}

function warehouse_create_goods_delivery($invoice_id) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(get_option('auto_create_goods_delivery') == 1){
        $CI->warehouse_model->auto_create_goods_delivery_with_invoice($invoice_id);
    }
}

function wh_invoice_marked_as_cancelled($invoice_id) {
    if($invoice_id && get_option('cancelled_invoice_reverse_inventory_delivery_voucher') == 1 ){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $CI->warehouse_model->inventory_cancel_invoice($invoice_id);
    }
}

function wh_invoice_unmarked_as_cancelled($invoice_id) {
    if($invoice_id && get_option('uncancelled_invoice_create_inventory_delivery_voucher') == 1 ){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $CI->warehouse_model->auto_create_goods_delivery_with_invoice($invoice_id);
    }
}

function wh_update_goods_delivery($invoice_id) {
    if($invoice_id){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        if(get_option('cancelled_invoice_reverse_inventory_delivery_voucher') == 1 ){
            $CI->warehouse_model->invoice_update_delete_goods_delivery_detail($invoice_id);
        }
        if(get_option('auto_create_goods_delivery') == 1){
            $CI->warehouse_model->auto_create_goods_delivery_with_invoice($invoice_id, true);
        }
    }
}

// --- Client Portal Menu ---
function init_shipment_portal_menu() {
    if(is_client_logged_in() && get_option('wh_display_shipment_on_client_portal') == 1){
        echo '<li class="customers-nav-item"><a href="'.site_url('warehouse/warehouse_client/shipments').'">'._l("wh_shipments").'</a></li>';
    }
}

function warehouse_client_add_head_components() {
    $viewuri = $_SERVER['REQUEST_URI'];
    if (strpos($viewuri, '/warehouse/warehouse_client/shipment_detail') !== false) {
       echo '<link href="' . module_dir_url(WAREHOUSE_MODULE_NAME, 'assets/css/shipments/order_status.css')  .'?v=' . time(). '"  rel="stylesheet" type="text/css" />'; 
    }
}
// ==============================================================================
//  MISSING HOOKS RESTORED FOR VERSION 1.6.0 (Append to warehouse/warehouse.php)
// ==============================================================================

// 1. Invoice Integration Hooks (View Delivery Notes on Invoice)
hooks()->add_action('after_invoice_view_as_client_link', 'warehouse_module_init_tab');
hooks()->add_action('invoice_add_good_delivery_tab_content', 'warehouse_module_init_tab_content');

// 2. Cron Job Hook (Inventory Warnings)
hooks()->add_action('after_cron_run', 'items_send_notification_inventory_warning');

// 3. Omni Sales Integration Hooks
hooks()->add_action('omni_order_detail_header', 'omni_order_detail_add_button_header');
hooks()->add_action('omni_sales_after_invoice_added', 'wh_omni_sales_after_invoice_added');
hooks()->add_action('omni_sales_after_delivery_note_added', 'wh_omni_sales_after_delivery_note_added');

// 4. Task Relation Hook (Link Tasks to Stock Import/Export)
hooks()->add_action('task_related_to_select', 'warehouse_task_related_to_select');


// ==============================================================================
//  HELPER FUNCTIONS FOR RESTORED HOOKS
// ==============================================================================

/**
 * Renders the "Goods Delivery" tab title on the Invoice View
 */
function warehouse_module_init_tab($invoice_id){
    if (has_permission('wh_stock_export', '', 'view')) {
        echo '<li role="presentation"><a href="' . admin_url('warehouse/manage_delivery_filter/'.$invoice_id->id).'" >'._l('goods_delivery_tab').'</a></li>';
    }
}

/**
 * Renders the content (table of delivery notes) for the Invoice Tab
 */
function warehouse_module_init_tab_content($invoice_id){
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    
    // Ensure the model method exists or handle gracefully
    if(method_exists($CI->warehouse_model, 'get_goods_delivery_from_invoice')){
        $array_goods_delivery = $CI->warehouse_model->get_goods_delivery_from_invoice($invoice_id);
        
        echo '<div role="tabpanel" class="tab-pane" id="tab_goods_delivery">';
        echo '<table class="table dt-table border table-striped">';
        echo '<thead><th>'._l('goods_delivery_code').'</th><th>'._l('accounting_date').'</th><th>'._l('total_money').'</th><th>'._l('status').'</th></thead>';
        echo '<tbody>';
        foreach ($array_goods_delivery as $value) {
            echo '<tr>';
            echo '<td><a href="' . admin_url('warehouse/manage_delivery/' . $value['id'] ).'">' . $value['goods_delivery_code'] . '</a></td>';
            echo '<td>'._d($value['date_add']).'</td>';
            echo '<td>'.app_format_money((float)($value['after_discount']),'').'</td>'; 
            $status = ($value['approval'] == 1) ? _l('approved') : _l('not_yet_approve');
            echo '<td>'.$status.'</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}

/**
 * Cron Job function to check low stock and send notifications
 */
function items_send_notification_inventory_warning($manually) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(method_exists($CI->warehouse_model, 'items_send_notification_inventory_warning')){
        $CI->warehouse_model->items_send_notification_inventory_warning();
    }
}

/**
 * Adds "Stock Import" and "Stock Export" options to Task Relations
 */
function warehouse_task_related_to_select($value) {
    $selected = ($value == 'stock_import') ? 'selected' : '';
    echo "<option value='stock_import' ".$selected.">"._l('stock_import')."</option>";
    
    $selected = ($value == 'stock_export') ? 'selected' : '';
    echo "<option value='stock_export' ".$selected.">"._l('stock_export')."</option>";
}

/**
 * Adds "View Shipment" button to Omni Sales Order header
 */
function omni_order_detail_add_button_header($order){
    $CI = &get_instance();
    if(!$CI->db->table_exists(db_prefix().'wh_omni_shipments')) return;
    
    $CI->load->model('warehouse/warehouse_model');
    $shipment = $CI->warehouse_model->get_shipment_by_order($order->id);
    if(isset($shipment)){
        echo '<a href="'.admin_url('warehouse/shipment_detail/' .$order->id).'" class="btn btn-primary mleft5 pull-right">'._l('wh_shipment').'</a>';
    }
}

/**
 * Auto-create shipment when Omni Sales invoice is added
 */
function wh_omni_sales_after_invoice_added($order_id) {
    if(is_numeric($order_id)){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $CI->warehouse_model->create_shipment_from_order($order_id);
    }
    return true;
}

/**
 * Update shipment status when Omni Sales delivery note is added
 */
function wh_omni_sales_after_delivery_note_added($order_id) {
    if($order_id){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $shipment = $CI->warehouse_model->get_shipment_by_order($order_id);
        
        if(!$shipment){
             $shipment_id = $CI->warehouse_model->create_shipment_from_order($order_id);
        } else {
             $shipment_id = $shipment->id;
        }

        if(is_numeric($shipment_id)){
             $CI->warehouse_model->update_shipment_status($shipment_id, ['shipment_status' => 'processing_order']);
        }
    }
    return true;
}
// ==============================================================================
//  1.6.0 RESTORATION PATCH - APPEND TO END OF warehouse.php
// ==============================================================================

// 1. CRON JOB & WARNINGS (Restores automated emails)
hooks()->add_action('after_cron_run', 'items_send_notification_inventory_warning');
hooks()->add_action('after_cron_settings_last_tab', 'wh_cron_settings_tab');
hooks()->add_action('after_cron_settings_last_tab_content', 'wh_cron_settings_tab_content');
hooks()->add_filter('before_settings_updated', 'warehouse_cronjob_settings_update');
register_merge_fields('warehouse/merge_fields/inventory_warning_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'inventory_warning_register_other_merge_fields');

// 2. INVOICE INTEGRATION (Restores Tabs & Filters)
hooks()->add_action('after_invoice_view_as_client_link', 'warehouse_module_init_tab');
hooks()->add_action('invoice_add_good_delivery_tab_content', 'warehouse_module_init_tab_content');
hooks()->add_filter('before_admin_view_create_invoice', 'wh_before_admin_view_create_invoice');
hooks()->add_filter('admin_invoice_ajax_search_item', 'wh_admin_invoice_ajax_search_item', 10, 2);
hooks()->add_action('before_invoice_deleted', 'warehouse_before_invoice_deleted');

// 3. OMNI SALES INTEGRATION (Restores Shipment Buttons)
hooks()->add_action('omni_order_detail_header', 'omni_order_detail_add_button_header');
hooks()->add_action('omni_sales_after_invoice_added', 'wh_omni_sales_after_invoice_added');
hooks()->add_action('omni_sales_after_delivery_note_added', 'wh_omni_sales_after_delivery_note_added');

// 4. TASK & CUSTOM FIELD INTEGRATION
hooks()->add_action('task_related_to_select', 'warehouse_task_related_to_select');
hooks()->add_action('after_custom_fields_select_options','init_warehouse_customfield');

// ------------------------------------------------------------------------------
//  HELPER FUNCTIONS
// ------------------------------------------------------------------------------

function items_send_notification_inventory_warning($manually) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(method_exists($CI->warehouse_model, 'items_send_notification_inventory_warning')){
        $CI->warehouse_model->items_send_notification_inventory_warning();
    }
}

function wh_cron_settings_tab() {
    get_instance()->load->view('warehouse/cronjob_tab/settings_tab');
}

function wh_cron_settings_tab_content() {
    get_instance()->load->view('warehouse/cronjob_tab/settings_tab_content');
}

function warehouse_cronjob_settings_update($data) {
    if(isset($data['inventory_cronjob_notification_recipients'])){
        $data['settings']['inventory_cronjob_notification_recipients'] = implode(',', $data['inventory_cronjob_notification_recipients']);
        unset($data['inventory_cronjob_notification_recipients']);
    }
    return $data;
}

function inventory_warning_register_other_merge_fields($for) {
    $for[] = 'inventory_warning';
    return $for;
}

function warehouse_module_init_tab($invoice_id){
    if (has_permission('wh_stock_export', '', 'view')) {
        echo '<li role="presentation"><a href="' . admin_url('warehouse/manage_delivery_filter/'.$invoice_id->id).'" >'._l('goods_delivery_tab').'</a></li>';
    }
}

function warehouse_module_init_tab_content($invoice_id){
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(method_exists($CI->warehouse_model, 'get_goods_delivery_from_invoice')){
        $array_goods_delivery = $CI->warehouse_model->get_goods_delivery_from_invoice($invoice_id);
        echo '<div role="tabpanel" class="tab-pane" id="tab_goods_delivery">';
        echo '<table class="table dt-table border table-striped">';
        echo '<thead><th>'._l('goods_delivery_code').'</th><th>'._l('accounting_date').'</th><th>'._l('total_money').'</th><th>'._l('status').'</th></thead>';
        echo '<tbody>';
        foreach ($array_goods_delivery as $value) {
            echo '<tr>';
            echo '<td><a href="' . admin_url('warehouse/manage_delivery/' . $value['id'] ).'">' . $value['goods_delivery_code'] . '</a></td>';
            echo '<td>'._d($value['date_add']).'</td>';
            echo '<td>'.app_format_money((float)($value['after_discount']),'').'</td>'; 
            $status = ($value['approval'] == 1) ? _l('approved') : _l('not_yet_approve');
            echo '<td>'.$status.'</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}

function wh_before_admin_view_create_invoice($items) {
    if(count($items) > 0){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        // Filters items to show only those with "can_be_sold" status or available stock
        if(method_exists($CI->warehouse_model, 'wh_get_grouped')){
            return $CI->warehouse_model->wh_get_grouped('can_be_sold');
        }
    }
    return $items;
}

function wh_admin_invoice_ajax_search_item($data, $search) {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    if(method_exists($CI->warehouse_model, 'wh_commodity_code_search')){
        return $CI->warehouse_model->wh_commodity_code_search($search, 'rate', 'can_be_sold');
    }
    return $data;
}

function warehouse_before_invoice_deleted($invoice_id) {
    if($invoice_id){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        // Reverses inventory if the invoice is deleted
        if(method_exists($CI->warehouse_model, 'inventory_cancel_invoice')){
            $CI->warehouse_model->inventory_cancel_invoice($invoice_id);
        }
    }
    return true;
}

function omni_order_detail_add_button_header($order){
    $CI = &get_instance();
    if(!$CI->db->table_exists(db_prefix().'wh_omni_shipments')) return;
    $CI->load->model('warehouse/warehouse_model');
    $shipment = $CI->warehouse_model->get_shipment_by_order($order->id);
    if(isset($shipment)){
        echo '<a href="'.admin_url('warehouse/shipment_detail/' .$order->id).'" class="btn btn-primary mleft5 pull-right">'._l('wh_shipment').'</a>';
    }
}

function wh_omni_sales_after_invoice_added($order_id) {
    if(is_numeric($order_id)){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $CI->warehouse_model->create_shipment_from_order($order_id);
    }
    return true;
}

function wh_omni_sales_after_delivery_note_added($order_id) {
    if($order_id){
        $CI = &get_instance();
        $CI->load->model('warehouse/warehouse_model');
        $shipment = $CI->warehouse_model->get_shipment_by_order($order_id);
        if(!$shipment){
             $CI->warehouse_model->create_shipment_from_order($order_id);
        }
    }
    return true;
}

function warehouse_task_related_to_select($value) {
    $selected = ($value == 'stock_import') ? 'selected' : '';
    echo "<option value='stock_import' ".$selected.">"._l('stock_import')."</option>";
    $selected = ($value == 'stock_export') ? 'selected' : '';
    echo "<option value='stock_export' ".$selected.">"._l('stock_export')."</option>";
}

function init_warehouse_customfield($custom_field = ''){
    $select = '';
    if($custom_field != ''){
        if($custom_field->fieldto == 'warehouse_name'){
            $select = 'selected';
        }
    }
    echo '<option value="warehouse_name" '.$select.' >'. _l('_warehouse').'</option>';
}

// ---------------------------------------------------------
// LICENSE MANAGEMENT HOOKS AND PERMISSIONS
// ---------------------------------------------------------

// 1. Permissions Registration
hooks()->add_action('admin_init', function() {
    register_staff_capabilities('wh_licences', ['view', 'view_own', 'create', 'edit', 'delete'], _l('licence_management'));
});

// 2. Menu Item
function wh_licence_menu_item(){
    $CI = &get_instance();
    if (has_permission('wh_licences', '', 'view') || has_permission('wh_licences', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('warehouse', [
            'slug'     => 'wa_licence_management',
            'name'     => _l('licence_management'),
            'icon'     => 'fa fa-key',
            'href'     => admin_url('warehouse/licence_management'),
            'position' => 26,
        ]);
    }
}
hooks()->add_action('admin_init', 'wh_licence_menu_item');

// 3. Cron Hook for Expiration Notifications
hooks()->add_action('after_cron_run', 'wh_check_licence_expiration');

function wh_check_licence_expiration() {
    $CI = &get_instance();
    $CI->load->model('warehouse/warehouse_model');
    // Ensure method exists before calling
    if(method_exists($CI->warehouse_model, 'cron_check_licence_expiration')){
        $CI->warehouse_model->cron_check_licence_expiration();
    }
}
