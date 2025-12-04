<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Include Traits
require_once(__DIR__ . '/traits/WarehouseSettingsTrait.php');
require_once(__DIR__ . '/traits/WarehouseItemsTrait.php');
require_once(__DIR__ . '/traits/WarehouseInventoryTrait.php');
require_once(__DIR__ . '/traits/WarehouseTransfersTrait.php');
require_once(__DIR__ . '/traits/WarehouseReportsTrait.php');
require_once(__DIR__ . '/traits/WarehouseOrderReturnsTrait.php');

class Warehouse_model extends App_Model {
    use WarehouseSettingsTrait;
    use WarehouseItemsTrait;
    use WarehouseInventoryTrait;
    use WarehouseTransfersTrait;
    use WarehouseReportsTrait;
    use WarehouseOrderReturnsTrait;

    public function __construct() {
        parent::__construct();
    }

    /**
     * RESTORED: Add Activity Log
     */
    public function add_activity_log($data) {
        $this->db->insert(db_prefix() . 'wh_activity_log', $data);
        return true;
    }

    /**
     * RESTORED: Cron Job Logic for Notifications
     */
    public function items_send_notification_inventory_warning() {
        $inventory_cronjob_notification_recipients = get_option('inventory_cronjob_notification_recipients');
        if(empty($inventory_cronjob_notification_recipients)) return;

        // Logic to check inventory levels vs minimums
        $this->db->select('commodity_id, warehouse_id, sum(inventory_number) as inventory_number');
        $this->db->group_by(['commodity_id', 'warehouse_id']);
        $inventory = $this->db->get(db_prefix().'inventory_manage')->result_array();

        $string_notification = '';

        foreach ($inventory as $item) {
             $min_data = $this->get_inventory_min_cron($item['commodity_id']);
             if($min_data && $item['inventory_number'] <= $min_data->inventory_number_min){
                 $item_info = $this->get_commodity($item['commodity_id']);
                 $string_notification .= $item_info->description . ' is low on stock. Current: '.$item['inventory_number'].'<br>';
             }
        }

        if(!empty($string_notification)){
            $staff_ids = explode(',', $inventory_cronjob_notification_recipients);
            foreach($staff_ids as $staffid){
                add_notification([
                    'description' => 'Inventory Warning',
                    'touserid' => $staffid,
                    'additional_data' => serialize([$string_notification]),
                ]);
            }
        }
    }
    
    public function get_inventory_min_cron($id) {
    	$this->db->where('commodity_id', $id);
    	return $this->db->get(db_prefix() . 'inventory_commodity_min')->row();
    }

    /**
     * RESTORED: PDF Generation Methods
     */
    public function stock_import_pdf($purchase) {
		return app_pdf('purchase', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Purchase_pdf.php'), $purchase);
	}

    public function stock_export_pdf($delivery) {
		return app_pdf('delivery', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Delivery_pdf.php'), $delivery);
	}

    /**
     * RESTORED: Auto Create Delivery from Invoice
     */
    public function auto_create_goods_delivery_with_invoice($invoice_id, $is_update = false) {
        $this->db->where('id', $invoice_id);
        $invoice = $this->db->get(db_prefix().'invoices')->row();
        if(!$invoice) return false;

        // Simplified logic to create delivery header
        $data = [
            'goods_delivery_code' => 'AUTODEL-' . $invoice_id,
            'invoice_id' => $invoice_id,
            'customer_code' => $invoice->clientid,
            'date_add' => date('Y-m-d'),
            'addedfrom' => $invoice->addedfrom,
            'approval' => 1, // Auto approve
            'total_money' => $invoice->subtotal
        ];
        
        if($is_update){
             $this->db->where('invoice_id', $invoice_id);
             $this->db->update(db_prefix() . 'goods_delivery', $data);
        } else {
             $this->db->insert(db_prefix() . 'goods_delivery', $data);
        }
        return true;
    }

     /**
     * RESTORED: Cancel/Uncancel Invoice Logic
     */
    public function inventory_cancel_invoice($invoice_id) {
         $this->db->where('invoice_id', $invoice_id);
         $delivery = $this->db->get(db_prefix().'goods_delivery')->row();
         if($delivery){
             // Logic to revert stock would go here (revert_goods_delivery)
             // For now, we just delete the delivery record to prevent duplicates
             $this->db->where('id', $delivery->id);
             $this->db->delete(db_prefix().'goods_delivery');
         }
    }
    
    public function invoice_update_delete_goods_delivery_detail($invoice_id){
        // Cleanup details on update
        $this->db->where('invoice_id', $invoice_id);
        $delivery = $this->db->get(db_prefix().'goods_delivery')->row();
        if($delivery){
             $this->db->where('goods_delivery_id', $delivery->id);
             $this->db->delete(db_prefix().'goods_delivery_detail');
        }
    }
    
    // --- Helpers for Traits fallback ---
    public function get_commodity($id = false) {
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'items')->row();
		}
		return $this->db->get(db_prefix() . 'items')->result_array();
	}
    
    public function get_warehouse($id = false) {
		if (is_numeric($id)) {
			$this->db->where('warehouse_id', $id);
			return $this->db->get(db_prefix() . 'warehouse')->row();
		}
		return $this->db->get(db_prefix() . 'warehouse')->result_array();
	}
}
