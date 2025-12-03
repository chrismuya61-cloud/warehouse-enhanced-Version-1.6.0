<?php
trait WarehouseOrderReturnsController {

    public function manage_order_return() {
        if (!has_permission('wh_receipt_return_order', '', 'view') && !has_permission('wh_receipt_return_order', '', 'view_own')) {
            access_denied('warehouse');
        }
        $data['title'] = _l('inventory_receipt_inventory_delivery_returns_goods');
        $this->load->view('order_returns/manage_order_return', $data);
    }

    public function order_return($id = '') {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                if (!has_permission('wh_receipt_return_order', '', 'create')) access_denied();
                $id = $this->warehouse_model->add_order_return($data);
                if ($id) {
                    set_alert('success', _l('added_successfully'));
                    redirect(admin_url('warehouse/manage_order_return'));
                }
            } else {
                if (!has_permission('wh_receipt_return_order', '', 'edit')) access_denied();
                $success = $this->warehouse_model->update_order_return($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully'));
                }
                redirect(admin_url('warehouse/manage_order_return'));
            }
        }

        $this->load->model('staff_model');
        $this->load->model('clients_model');
        $data['staffs'] = $this->staff_model->get();
        $data['warehouses'] = $this->warehouse_model->get_warehouse();
        
        if ($id != '') {
            $data['order_return'] = $this->warehouse_model->get_order_return($id);
            $data['title'] = _l('edit_order_return');
        } else {
            $data['title'] = _l('add_order_return');
        }

        $this->load->view('order_returns/add_edit_order_return', $data);
    }
}