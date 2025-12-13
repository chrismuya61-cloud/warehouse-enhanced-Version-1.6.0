<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warehouse_remote_scanner extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('warehouse/warehouse_model');
    }

    public function session($token)
    {
        $session = $this->warehouse_model->get_scanner_session($token);
        if (!$session || $session->is_active == 0) {
            show_error('Invalid or expired scanning session.', 403);
        }
        $data['token'] = $token;
        $data['title'] = 'Mobile Stock Take Scanner';
        $this->load->view('warehouse/mobile_scanner/scan', $data);
    }

    public function scan_item()
    {
        if ($this->input->is_ajax_request()) {
            $token = $this->input->post('token');
            $barcode = trim($this->input->post('barcode'));
            
            $session = $this->warehouse_model->get_scanner_session($token);
            if (!$session || $session->is_active == 0) {
                echo json_encode(['status' => false, 'message' => 'Session expired']);
                die;
            }

            // 1. Try finding by Product Barcode/Code
            $item = $this->warehouse_model->get_commodity_hansometable_by_barcode($barcode);
            if (!$item) {
                $this->db->where('commodity_code', $barcode);
                $item = $this->db->get(db_prefix().'items')->row();
            }

            // 2. If not found, Try finding by Serial Number
            $is_serial = false;
            if (!$item) {
                $this->db->select('commodity_id, serial_number');
                $this->db->where('serial_number', $barcode);
                $serial_record = $this->db->get(db_prefix().'wh_inventory_serial_numbers')->row();
                
                if ($serial_record) {
                    $item = $this->warehouse_model->get_commodity($serial_record->commodity_id);
                    $is_serial = true;
                }
            }

            if($item){
                $result = $this->warehouse_model->add_scan_item($session->id, $barcode, $is_serial);
                
                if($result['status']){
                    echo json_encode([
                        'status' => true, 
                        'message' => 'Scanned: ' . $item->description . ($is_serial ? ' (SN)' : ''),
                        'count' => $result['qty']
                    ]);
                } else {
                    echo json_encode(['status' => false, 'message' => $result['message']]);
                }
            } else {
                echo json_encode(['status' => false, 'message' => 'Not found: ' . $barcode]);
            }
        }
    }
}