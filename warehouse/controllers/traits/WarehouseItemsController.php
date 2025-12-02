<?php

trait WarehouseItemsController {

    /**
     * List all commodities (Items)
     */
    public function commodity_list($id = '') {
        if (!has_permission('warehouse_item', '', 'view') && !has_permission('warehouse_item', '', 'view_own')) {
            access_denied('warehouse');
        }

        $this->load->model('departments_model');
        $this->load->model('staff_model');

        // Prepare Filter Data
        $data['units'] = $this->warehouse_model->get_unit_add_commodity();
        $data['commodity_types'] = $this->warehouse_model->get_commodity_type_add_commodity();
        $data['commodity_groups'] = $this->warehouse_model->get_commodity_group_add_commodity();
        $data['warehouses'] = $this->warehouse_model->get_warehouse_add_commodity();
        $data['taxes'] = get_taxes();
        $data['styles'] = $this->warehouse_model->get_style_add_commodity();
        $data['models'] = $this->warehouse_model->get_body_add_commodity();
        $data['sizes'] = $this->warehouse_model->get_size_add_commodity();
        
        // Filter Dropdowns
        $data['warehouse_filter'] = $this->warehouse_model->get_warehouse();
        $data['sub_groups'] = $this->warehouse_model->get_sub_group();
        $data['colors'] = $this->warehouse_model->get_color_add_commodity();
        $data['item_tags'] = $this->warehouse_model->get_item_tag_filter();
        
        $data['title'] = _l('commodity_list');
        
        // AJAX Optimization for large datasets
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= wh_ajax_on_total_items()) {
             // Standard Load
             // Note: Ensure wh_get_grouped is available in WarehouseItemsTrait (Model)
             // If not, we fall back to empty and let AJAX load it
             $data['items'] = []; 
             $data['ajaxItems'] = true; 
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        
        $data['proposal_id'] = $id;
        $this->load->view('commodity_list', $data);
    }

    /**
     * Table Data for Commodity List
     */
    public function table_commodity_list() {
        $this->app->get_table_data(module_views_path('warehouse', 'table_commodity_list'));
    }

    /**
     * Add or Edit Commodity
     */
    public function add_commodity_list($id = '') {
        if ($this->input->post()) {
            $data = $this->input->post();
            
            if (!$this->input->post('id')) {
                // INSERT
                if (!has_permission('warehouse_item', '', 'create')) access_denied('warehouse');
                
                $id = $this->warehouse_model->add_commodity($data);
                if ($id) {
                    // Handle Attachments
                    if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
                        $this->warehouse_model->add_attachment_to_database($id, 'commodity_item_file', $_FILES['file']);
                    }
                    
                    set_alert('success', _l('added_successfully') . ' ' . _l('commodity_list'));
                }
            } else {
                // UPDATE
                if (!has_permission('warehouse_item', '', 'edit')) access_denied('warehouse');
                
                $id = $data['id'];
                unset($data['id']);
                
                $success = $this->warehouse_model->update_commodity($data, $id);
                
                if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
                     $this->warehouse_model->add_attachment_to_database($id, 'commodity_item_file', $_FILES['file']);
                }
                
                if ($success) {
                    set_alert('success', _l('updated_successfully') . ' ' . _l('commodity_list'));
                }
            }
            redirect(admin_url('warehouse/commodity_list'));
        }
    }

    /**
     * Delete Commodity
     */
    public function delete_commodity($id) {
        if (!has_permission('warehouse_item', '', 'delete') && !is_admin()) {
            access_denied('warehouse');
        }
        
        if (!$id) redirect(admin_url('warehouse/commodity_list'));

        $response = $this->warehouse_model->delete_commodity($id);
        
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('commodity')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('commodity_list')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('commodity_list')));
        }
        redirect(admin_url('warehouse/commodity_list'));
    }

    /**
     * View Commodity Details (View Only Mode)
     */
    public function view_commodity_detail($id) {
        if (!has_permission('warehouse_item', '', 'view') && !has_permission('warehouse_item', '', 'view_own')) {
             access_denied('warehouse');
        }

        $data['id'] = $id;
        $data['commodity_item'] = $this->warehouse_model->get_commodity($id);
        
        // Get Inventory details per warehouse for this item
        // Ensure this method exists in InventoryTrait
        $data['inventory_commodity'] = $this->warehouse_model->get_inventory_commodity($id); 
        
        $data['commodity_file'] = $this->warehouse_model->get_warehourse_attachments($id);
        $data['title'] = _l('view_commodity_detail');
        
        $this->load->view('view_commodity_detail', $data);
    }

    /**
     * Get Commodity Data via AJAX (Used in Modals)
     */
    public function get_commodity_data_ajax($id) {
        $data['id'] = $id;
        $data['commodites'] = $this->warehouse_model->get_commodity($id);
        $data['inventory_commodity'] = $this->warehouse_model->get_inventory_commodity($id);
        $data['commodity_file'] = $this->warehouse_model->get_warehourse_attachments($id);
        $this->load->view('commodity_detail', $data);
    }

    /**
     * Import Items from Excel
     */
    public function import_xlsx_commodity() {
        if (!has_permission('warehouse_item', '', 'create')) {
            access_denied('warehouse');
        }

        if ($this->input->post()) {
            // Standard Perfex File Upload Logic
            // This delegates to the Model to parse the file
            // See WarehouseItemsTrait::import_xlsx_commodity
             if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
                $rows = $this->warehouse_model->import_xlsx_commodity($_FILES['file_csv']);
                if($rows > 0){
                    set_alert('success', _l('import_success'));
                } else {
                    set_alert('warning', _l('import_failed_or_duplicate'));
                }
             }
             redirect(admin_url('warehouse/commodity_list'));
        }
        
        $data['title'] = _l('import_excel');
        $this->load->view('items/import_excel', $data);
    }
    
    /**
     * Handle Item Attachments
     */
    public function add_commodity_attachment($id) {
        handle_commodity_attachments($id);
    }

    public function delete_commodity_file($id, $rel_id) {
        if (!has_permission('warehouse_item', '', 'delete') && !is_admin()) {
             access_denied('warehouse');
        }
        
        $this->warehouse_model->delete_commodity_file($id);
        redirect(admin_url('warehouse/view_commodity_detail/' . $rel_id));
    }
}