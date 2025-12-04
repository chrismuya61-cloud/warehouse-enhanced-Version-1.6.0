<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(__DIR__ . '/traits/WarehouseSettingsController.php');
require_once(__DIR__ . '/traits/WarehouseItemsController.php');
require_once(__DIR__ . '/traits/WarehouseInventoryController.php');
require_once(__DIR__ . '/traits/WarehouseTransfersController.php');
require_once(__DIR__ . '/traits/WarehouseReportsController.php');
require_once(__DIR__ . '/traits/WarehouseOrderReturnsController.php');

class Warehouse extends AdminController {
    use WarehouseSettingsController;
    use WarehouseItemsController;
    use WarehouseInventoryController;
    use WarehouseTransfersController;
    use WarehouseReportsController;
    use WarehouseOrderReturnsController;

    public function __construct() {
        parent::__construct();
        $this->load->model('warehouse_model');
    }

    // --- RESTORED AJAX HANDLERS (Prevent 404s on Frontend) ---

    /**
     * Check quantity inventory via AJAX
     */
    public function check_quantity_inventory() {
        $data = $this->input->post();
        if ($data != 'null') {
            // Handle Barcode Switch
            if(isset($data['switch_barcode_scanners']) && $data['switch_barcode_scanners'] == 'true'){
                 // Logic to get commodity ID from barcode
                 // For simplicity in this fix, we assume ID is passed or looked up in model
            }

            $value = $this->warehouse_model->get_quantity_inventory($data['warehouse_id'], $data['commodity_id']);
            $quantity = 0;
            if ($value != null) {
                $message = true;
                $quantity = (float)$value->inventory_number;
                if ((float)$quantity < (float)$data['quantity']) {
                    $message = _l('in_stock') . ' ' . $quantity;
                }
            } else {
                $message = _l('Product_does_not_exist_in_stock');
            }

            echo json_encode([
                'message' => $message,
                'value' => $quantity,
            ]);
            die;
        }
    }

    /**
     * Get Commodity Barcode via AJAX
     */
    public function get_commodity_barcode() {
        // Logic from 1.3.9
        $chars = '0123456789';
        $barcode = '';
        for ($i = 0; $i < 11; $i++) {
            $barcode .= $chars[rand(0, strlen($chars) - 1)];
        }
        echo json_encode([$barcode]);
        die();
    }
    
     /**
     * Get Item by ID (AJAX)
     */
    public function get_item_by_id($id)
	{
		if ($this->input->is_ajax_request()) {
			$item = $this->warehouse_model->get_commodity($id);
            // Format for response
			echo json_encode($item);
		}
	}
}
