<?php
trait WarehouseInventoryController {
    
    // --- EXISTING 1.6.0 METHODS ---
    public function visual_dashboard() {
        if (!has_permission('wh_dashboard', '', 'view')) access_denied();
        $data['title'] = _l('wh_dashboard');
        $data['low_stock_alerts'] = $this->warehouse_model->get_dashboard_low_stock_alerts();
        $data['inventory_value_data'] = $this->warehouse_model->get_dashboard_inventory_value();
        $data['stock_by_warehouse_data'] = $this->warehouse_model->get_dashboard_stock_by_warehouse();
        $this->load->view('dashboard/visual_dashboard', $data);
    }

    public function stock_take($id = '') {
        if (!has_permission('wh_stock_take', '', 'view')) access_denied();
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                $id = $this->warehouse_model->add_stock_take($data);
                redirect(admin_url('warehouse/stock_take/' . $id));
            } else {
                $this->warehouse_model->save_stock_take_inventory($id, $data['items']);
                if($this->input->post('approval') == 1) {
                    $this->warehouse_model->submit_stock_take_approval($id);
                    redirect(admin_url('warehouse/manage_stock_take'));
                }
                redirect(admin_url('warehouse/stock_take/' . $id));
            }
        }
        $data['warehouses'] = $this->warehouse_model->get_warehouse();
        $data['title'] = _l('wh_stock_take');
        if($id != ''){
            $data['stock_take'] = $this->db->where('id', $id)->get(db_prefix().'wh_stock_take')->row();
            $data['stock_take_detail'] = $this->db->query("SELECT t1.*, t2.commodity_code, t2.description as commodity_name, t3.bin_name FROM ".db_prefix()."wh_stock_take_inventory t1 LEFT JOIN ".db_prefix()."items t2 ON t1.commodity_id = t2.id LEFT JOIN ".db_prefix()."wh_bin_locations t3 ON t1.bin_location_id = t3.id WHERE stock_take_id = ".$id)->result_array();
        }
        $this->load->view('manage_stock_take/stock_take', $data);
    }

    public function get_inventory_for_stock_take($warehouse_id) {
        if (!$this->input->is_ajax_request()) show_404();
        $items = $this->warehouse_model->get_inventory_by_warehouse($warehouse_id);
        $html = '';
        foreach($items as $k=>$item){
             $html .= '<tr><td><input type="hidden" name="items['.$k.'][commodity_id]" value="'.$item['commodity_id'].'">'.$item['commodity_id'].'</td>
                       <td><input type="hidden" name="items['.$k.'][system_quantity]" value="'.$item['inventory_number'].'">'.$item['inventory_number'].'</td>
                       <td><input type="number" name="items['.$k.'][physical_quantity]" class="form-control" value="'.$item['inventory_number'].'"></td></tr>';
        }
        echo json_encode(['html' => $html]);
    }

    // --- RESTORED METHODS FOR LOSS ADJUSTMENT ---

    public function loss_adjustment() {
        if (!has_permission('wh_loss_adjustment', '', 'view') && !has_permission('wh_loss_adjustment', '', 'view_own')) {
            access_denied('warehouse');
        }
        $data['title'] = _l('loss_adjustment');
        $this->load->view('loss_adjustment/manage', $data);
    }

    public function add_loss_adjustment($id = '') {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                if (!has_permission('wh_loss_adjustment', '', 'create')) access_denied();
                $id = $this->warehouse_model->add_loss_adjustment($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                    redirect(admin_url('warehouse/loss_adjustment'));
                }
            } else {
                if (!has_permission('wh_loss_adjustment', '', 'edit')) access_denied();
                $success = $this->warehouse_model->update_loss_adjustment($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
                redirect(admin_url('warehouse/loss_adjustment'));
            }
        }

        $data['warehouses'] = $this->warehouse_model->get_warehouse();
        
        if ($id != '') {
            $data['loss_adjustment'] = $this->warehouse_model->get_loss_adjustment($id);
            $data['title'] = _l('edit_loss_adjustment');
        } else {
            $data['title'] = _l('add_loss_adjustment');
        }

        $this->load->view('loss_adjustment/add_loss_adjustment', $data);
    }
}