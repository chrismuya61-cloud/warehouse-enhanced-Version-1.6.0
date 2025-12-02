<?php
trait WarehouseTransfersController {
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
    
    public function manage_delivery($id='') { $this->load->view('manage_goods_delivery/manage_delivery'); }
    public function manage_purchase($id='') { $this->load->view('manage_goods_receipt/manage_purchase'); }
}