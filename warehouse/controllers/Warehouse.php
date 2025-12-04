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

	// Add inside class Warehouse extends AdminController { ... }

    /**
     * Returns the Warranty Claim Modal View
     */
    public function get_claim_modal($detail_id, $commodity_id, $customer_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data['detail_id'] = $detail_id;
        $data['commodity_id'] = $commodity_id;
        $data['customer_id'] = $customer_id;
        
        // Optional: Fetch item/customer details for display if needed
        $data['item'] = $this->warehouse_model->get_commodity($commodity_id);
        
        $this->load->view('warehouse/warranty/claim_modal', $data);
    }
	// --------------------------------------------------------------------------
    // WARRANTY MANAGEMENT & CLAIMS
    // --------------------------------------------------------------------------

    public function warranty_dashboard()
    {
        if (!has_permission('wh_warranty', '', 'view')) access_denied('Warranty');
        
        $data['title'] = _l('warranty_dashboard');
        
        // Fetch Stats
        $data['total_active'] = $this->warehouse_model->get_warranty_count('active');
        $data['expiring_soon'] = $this->warehouse_model->get_warranty_count('expiring');
        $data['total_expired'] = $this->warehouse_model->get_warranty_count('expired');
        $data['open_claims'] = $this->warehouse_model->get_claim_count('pending');
        
        $this->load->view('warehouse/warranty/dashboard', $data);
    }

    public function warranty_list()
    {
        if (!has_permission('wh_warranty', '', 'view')) access_denied('Warranty');
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('warehouse', 'warranty/table_warranty_list'));
        }
        
        $data['title'] = _l('warranty_list');
        $this->load->view('warehouse/warranty/manage_list', $data);
    }

    public function warranty_claims()
    {
        if (!has_permission('wh_warranty', '', 'view')) access_denied('Warranty');
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('warehouse', 'warranty/table_warranty_claims'));
        }
        
        $data['title'] = _l('warranty_claims');
        $this->load->view('warehouse/warranty/manage_claims', $data);
    }

    public function add_edit_claim($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                $success = $this->warehouse_model->add_warranty_claim($data);
                $message = $success ? _l('added_successfully') : _l('problem_adding');
            } else {
                $success = $this->warehouse_model->update_warranty_claim($data, $id);
                $message = $success ? _l('updated_successfully') : _l('problem_updating');
            }
            set_alert($success ? 'success' : 'danger', $message);
            redirect(admin_url('warehouse/warranty_claims'));
        }
    }

    public function get_claim_modal($detail_id, $commodity_id, $customer_id)
    {
        if (!$this->input->is_ajax_request()) show_404();

        $data['detail_id'] = $detail_id;
        $data['commodity_id'] = $commodity_id;
        $data['customer_id'] = $customer_id;
        
        $data['item'] = $this->warehouse_model->get_commodity($commodity_id);
        
        $this->db->where('id', $detail_id);
        $detail = $this->db->get(db_prefix() . 'goods_delivery_detail')->row();
        $data['serial_number'] = $detail ? $detail->serial_number : '';
        
        $this->load->view('warehouse/warranty/claim_modal', $data);
    }

    public function convert_claim_to_invoice($claim_id)
    {
        if (!has_permission('invoices', '', 'create')) access_denied('Create Invoice');

        $this->load->model('invoices_model');
        $claim = $this->warehouse_model->get_warranty_claim($claim_id);

        if (!$claim) {
            set_alert('danger', _l('claim_not_found'));
            redirect(admin_url('warehouse/warranty_claims'));
        }

        $item = $this->warehouse_model->get_commodity($claim->commodity_id);
        
        $this->db->where('id', $claim->detail_id);
        $delivery_detail = $this->db->get(db_prefix() . 'goods_delivery_detail')->row();
        $serial_ref = $delivery_detail ? $delivery_detail->serial_number : 'N/A';
        
        $new_invoice_data = [
            'clientid' => $claim->customer_id,
            'number' => get_option('next_invoice_number'),
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+30 days')),
            'currency' => $this->currencies_model->get_base_currency()->id,
            'subtotal' => 0.00,
            'total' => 0.00,
            'billing_street' => '', 
            'adminnote' => 'Generated from Warranty Claim #' . $claim_id,
            'newitems' => [
                1 => [
                    'description' => $item->description . ' (Warranty Service)',
                    'long_description' => 'Service for Claim on: ' . _d($claim->claim_date) . '. Serial Number: ' . $serial_ref . '. Issue: ' . $claim->issue_description,
                    'qty' => 1,
                    'rate' => 0.00, 
                    'unit' => '',
                    'taxname' => []
                ]
            ]
        ];

        $id = $this->invoices_model->add($new_invoice_data);
        
        if ($id) {
            $this->warehouse_model->update_warranty_claim(['invoice_id' => $id], $claim_id);
            set_alert('success', _l('invoice_created_successfully'));
            redirect(admin_url('invoices/invoice/' . $id));
        } else {
            set_alert('danger', _l('invoice_creation_failed'));
            redirect(admin_url('warehouse/warranty_claims'));
        }
    }

    public function create_expense_from_claim($claim_id)
    {
        if (!has_permission('expenses', '', 'create')) access_denied('Create Expense');
        $claim = $this->warehouse_model->get_warranty_claim($claim_id);
        $url = admin_url('expenses/expense?customer_id=' . $claim->customer_id . '&claim_ref=' . $claim_id);
        redirect($url);
    }
        
        // Load modal view logic here or handle via JS
    }

// --------------------------------------------------------------------------
    // LICENSE MANAGEMENT
    // --------------------------------------------------------------------------

    public function licence_management()
    {
        if (!has_permission('wh_licences', '', 'view')) access_denied('Licences');
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('warehouse', 'licences/table_licences'));
        }
        
        $data['title'] = _l('licence_management');
        $this->load->view('warehouse/licences/manage', $data);
    }

    public function add_edit_licence($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') {
                $success = $this->warehouse_model->add_licence($data);
                $message = $success ? _l('added_successfully') : _l('problem_adding');
            } else {
                $success = $this->warehouse_model->update_licence($data, $id);
                $message = $success ? _l('updated_successfully') : _l('problem_updating');
            }
            set_alert($success ? 'success' : 'danger', $message);
            redirect(admin_url('warehouse/licence_management'));
        }
    }

    public function get_licence_modal($id = '')
    {
        if (!$this->input->is_ajax_request()) show_404();
        
        if($id != ''){
            $data['licence'] = $this->warehouse_model->get_licence($id);
        }
        
        // For Manual Creation dropdowns
        $this->load->model('clients_model');
        $data['clients'] = $this->clients_model->get();
        
        $this->load->view('warehouse/licences/licence_modal', $data);
    }
    
    // AJAX: Get Serials by Customer/Invoice for Manual Creation
    public function get_available_serials_for_licence() {
        $clientid = $this->input->post('clientid');
        $invoiceid = $this->input->post('invoiceid');
        
        $serials = $this->warehouse_model->get_serials_for_licensing($clientid, $invoiceid);
        echo json_encode($serials);
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
