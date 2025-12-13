<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warehouse_api extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('warehouse/warehouse_model');
    }

    public function receive_stock_transfer()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$payload) {
            $this->response(['status' => false, 'message' => 'Empty payload'], 400);
            return;
        }

        $warehouse_code = isset($payload['target_warehouse_code']) ? $payload['target_warehouse_code'] : '';
        
        $this->db->where('warehouse_code', $warehouse_code);
        $local_warehouse = $this->db->get(db_prefix().'warehouse')->row();

        if (!$local_warehouse) {
            $this->response(['status' => false, 'message' => 'Target Warehouse not found: ' . $warehouse_code], 404);
            return;
        }

        $data = [];
        $data['goods_receipt_code'] = $this->warehouse_model->create_goods_code();
        $data['date_c'] = date('Y-m-d');
        $data['date_add'] = date('Y-m-d H:i:s');
        $data['warehouse_id'] = $local_warehouse->warehouse_id;
        $data['description'] = 'Auto-Import from Remote CRM Transfer (Ref: ' . $payload['source_reference'] . ')';
        $data['addedfrom'] = 1; 
        $data['approval'] = 1; 

        $details = [];
        foreach ($payload['items'] as $item) {
            $local_item = $this->find_item($item['commodity_code'], $item['commodity_barcode']);
            
            if ($local_item) {
                $details[] = [
                    'commodity_code' => $local_item->id,
                    'quantities' => $item['quantity'],
                    'unit_price' => $item['rate'],
                    'tax' => '',
                    'lot_number' => $item['lot_number'],
                    'expiry_date' => $item['expiry_date'],
                    'serial_number' => $item['serial_number'],
                    'note' => 'Remote Transfer',
                ];
            }
        }

        if (empty($details)) {
            $this->response(['status' => false, 'message' => 'No matching items found.'], 400);
            return;
        }

        $insert_data = [
            'goods_receipt_code' => $data['goods_receipt_code'],
            'date_c' => $data['date_c'],
            'date_add' => $data['date_add'],
            'warehouse_id_m' => $data['warehouse_id'],
            'description' => $data['description'],
            'approval' => 1,
            'project' => '',
            'type' => '',
            'department' => '',
            'requester' => '',
            'expiry_date' => '',
            'hot_purchase' => 'true'
        ];
        
        $this->db->insert(db_prefix() . 'goods_receipt', $insert_data);
        $receipt_id = $this->db->insert_id();

        foreach ($details as $detail) {
            $this->db->insert(db_prefix() . 'goods_receipt_detail', [
                'goods_receipt_id' => $receipt_id,
                'commodity_code' => $detail['commodity_code'],
                'quantities' => $detail['quantities'],
                'unit_price' => $detail['unit_price'],
                'serial_number' => $detail['serial_number'],
            ]);

            $this->warehouse_model->add_inventory_manage([
                'warehouse_id' => $data['warehouse_id'],
                'commodity_code' => $detail['commodity_code'],
                'quantities' => $detail['quantities'],
                'lot_number' => $detail['lot_number'],
                'expiry_date' => $detail['expiry_date'],
                'serial_number' => $detail['serial_number'],
                'date_manufacture' => null,
            ], 1); 
        }

        $this->response(['status' => true, 'message' => 'Stock Received successfully.', 'receipt_id' => $receipt_id]);
    }

    public function receive_stock_request()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$payload) {
            $this->response(['status' => false, 'message' => 'Empty payload'], 400);
            return;
        }

        $source_code = isset($payload['source_warehouse_code']) ? $payload['source_warehouse_code'] : '';
        $requester_code = isset($payload['requester_warehouse_code']) ? $payload['requester_warehouse_code'] : '';

        $this->db->where('warehouse_code', $source_code);
        $source_warehouse = $this->db->get(db_prefix().'warehouse')->row();

        $this->db->where('warehouse_code', $requester_code);
        $requester_warehouse = $this->db->get(db_prefix().'warehouse')->row();

        if (!$source_warehouse || !$requester_warehouse) {
            $this->response(['status' => false, 'message' => 'Warehouse mapping not found'], 404);
            return;
        }

        $data = [];
        $data['internal_delivery_code'] = $this->warehouse_model->create_goods_delivery_code(); 
        $data['date_c'] = date('Y-m-d');
        $data['date_add'] = date('Y-m-d H:i:s');
        $data['from_stock_name'] = $source_warehouse->warehouse_id;
        $data['to_stock_name'] = $requester_warehouse->warehouse_id;
        $data['description'] = 'Remote Stock Request from ' . $requester_code;
        $data['staff_id'] = 1;
        $data['approval'] = 0; 
        $data['addedfrom'] = 1;
        $data['total_amount'] = 0;

        $this->db->insert(db_prefix() . 'internal_delivery_note', $data);
        $delivery_id = $this->db->insert_id();

        $total_amount = 0;
        foreach ($payload['items'] as $item) {
            $local_item = $this->find_item($item['commodity_code'], $item['commodity_barcode']);
            
            if ($local_item) {
                $unit_price = $local_item->purchase_price; 
                $amount = $unit_price * $item['quantity'];
                $total_amount += $amount;

                $this->db->insert(db_prefix() . 'internal_delivery_note_detail', [
                    'internal_delivery_id' => $delivery_id,
                    'commodity_code' => $local_item->id,
                    'quantities' => $item['quantity'],
                    'unit_price' => $unit_price,
                    'into_money' => $amount,
                    'note' => 'Requested via API',
                ]);
            }
        }
        
        $this->db->where('id', $delivery_id);
        $this->db->update(db_prefix().'internal_delivery_note', ['total_amount' => $total_amount]);

        $this->response(['status' => true, 'message' => 'Stock Request created successfully. ID: ' . $data['internal_delivery_code']]);
    }

    private function find_item($code, $barcode) {
        $this->db->where('commodity_code', $code);
        $item = $this->db->get(db_prefix().'items')->row();
        if ($item) return $item;

        if ($barcode) {
            $this->db->where('commodity_barcode', $barcode);
            return $this->db->get(db_prefix().'items')->row();
        }
        return false;
    }

    private function response($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        die;
    }
}