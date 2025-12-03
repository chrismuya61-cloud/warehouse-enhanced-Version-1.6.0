<?php
trait WarehouseTransfersController {
    
    // --- EXISTING 1.6.0 METHODS ---
    public function auto_match_inventory() {
        if (!$this->input->is_ajax_request()) show_404();
        $res = $this->warehouse_model->auto_match_transaction($this->input->post('inputs'), $this->input->post('warehouse_id'));
        echo json_encode($res);
    }

    public function bulk_export_action() {
        if (!has_permission('wh_stock_export', '', 'create')) access_denied();
        if ($this->input->post()) {
            $report = $this->warehouse_model->bulk_create_goods_delivery($this->input->post('warehouse_id'));
            set_alert('info', 'Generated: ' . count($report['success']) . ' | Failed: ' . count($report['failed']));
            redirect(admin_url('warehouse/manage_delivery'));
        }
    }

    public function goods_delivery($id = '') {
        if ($this->input->post()) {
            $id = $this->warehouse_model->add_goods_delivery($this->input->post());
            redirect(admin_url('warehouse/manage_delivery/' . $id));
        }
        $data['warehouses'] = $this->warehouse_model->get_warehouse();
        $this->load->view('manage_goods_delivery/delivery', $data);
    }
    
    public function manage_delivery($id='') { 
        // Logic to load delivery view
        $this->load->model('clients_model');
        $data['title'] = _l('stock_export');
        $this->load->view('manage_goods_delivery/manage_delivery', $data); 
    }
    
    public function manage_purchase($id='') { 
        $data['title'] = _l('stock_import');
        $this->load->view('manage_goods_receipt/manage_purchase', $data); 
    }

    // --- RESTORED METHODS FOR PACKING LISTS & INTERNAL DELIVERY ---

    /**
     * Manage Packing Lists
     */
    public function manage_packing_list() {
        if (!has_permission('wh_packing_list', '', 'view') && !has_permission('wh_packing_list', '', 'view_own')) {
            access_denied('warehouse');
        }

        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();
        $data['get_goods_delivery'] = $this->warehouse_model->get_goods_delivery();
        $data['title'] = _l('wh_packing_lists');
        
        $this->load->view('packing_lists/manage_packing_list', $data);
    }

    public function packing_list($id = '') {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                // Add
                if (!has_permission('wh_packing_list', '', 'create')) access_denied('warehouse');
                $id = $this->warehouse_model->add_packing_list($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('wh_packing_list')));
                    redirect(admin_url('warehouse/manage_packing_list'));
                }
            } else {
                // Update
                if (!has_permission('wh_packing_list', '', 'edit')) access_denied('warehouse');
                $success = $this->warehouse_model->update_packing_list($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('wh_packing_list')));
                }
                redirect(admin_url('warehouse/manage_packing_list'));
            }
        }

        // Prepare View Data
        $data['goods_deliveries'] = $this->warehouse_model->get_goods_delivery();
        $data['staffs'] = $this->staff_model->get();
        
        if ($id != '') {
            $data['packing_list'] = $this->warehouse_model->get_packing_list($id);
            $data['title'] = _l('edit_packing_list');
        } else {
            $data['title'] = _l('add_packing_list');
        }

        $this->load->view('packing_lists/add_edit_packing_list', $data);
    }

    /**
     * Manage Internal Delivery Notes
     */
    public function manage_internal_delivery() {
        if (!has_permission('wh_internal_delivery_note', '', 'view') && !has_permission('wh_internal_delivery_note', '', 'view_own')) {
            access_denied('warehouse');
        }
        
        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();
        $data['title'] = _l('internal_delivery_note');
        
        $this->load->view('manage_internal_delivery/manage', $data);
    }

    public function internal_delivery($id = '') {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                if (!has_permission('wh_internal_delivery_note', '', 'create')) access_denied();
                $id = $this->warehouse_model->add_internal_delivery($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                    redirect(admin_url('warehouse/manage_internal_delivery'));
                }
            } else {
                if (!has_permission('wh_internal_delivery_note', '', 'edit')) access_denied();
                $success = $this->warehouse_model->update_internal_delivery($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
                redirect(admin_url('warehouse/manage_internal_delivery'));
            }
        }

        $data['warehouses'] = $this->warehouse_model->get_warehouse();
        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();

        if ($id != '') {
            $data['internal_delivery'] = $this->warehouse_model->get_internal_delivery($id);
            $data['title'] = _l('edit_internal_delivery');
        } else {
            $data['title'] = _l('add_internal_delivery');
        }

        $this->load->view('manage_internal_delivery/add_internal_delivery', $data);
    }
}