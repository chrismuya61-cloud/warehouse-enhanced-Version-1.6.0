<?php

trait WarehouseReportsTrait {

    // --- DASHBOARD METRICS ---
    
    public function get_dashboard_low_stock_alerts() {
        // Items where current stock <= min inventory
        $sql = "SELECT t1.id, t1.commodity_code, t1.description, t2.inventory_number_min, SUM(t3.inventory_number) as current_stock 
                FROM ".db_prefix()."items t1 
                JOIN ".db_prefix()."inventory_commodity_min t2 ON t1.id = t2.commodity_id 
                JOIN ".db_prefix()."inventory_manage t3 ON t1.id = t3.commodity_id 
                GROUP BY t1.id, t2.inventory_number_min 
                HAVING current_stock <= t2.inventory_number_min";
        return $this->db->query($sql)->result_array();
    }

    public function get_dashboard_inventory_value() {
        // Total Value based on Purchase Price
        $sql = "SELECT SUM(inventory_number * purchase_price) as total_value, SUM(inventory_number) as total_units 
                FROM ".db_prefix()."inventory_manage";
        return $this->db->query($sql)->row();
    }

    public function get_dashboard_stock_by_warehouse() {
        // Chart Data
        $sql = "SELECT t2.warehouse_name, SUM(t1.inventory_number) as total_units 
                FROM ".db_prefix()."inventory_manage t1 
                JOIN ".db_prefix()."warehouse t2 ON t1.warehouse_id = t2.warehouse_id 
                GROUP BY t2.warehouse_name";
        return $this->db->query($sql)->result_array();
    }

    // --- REPORT DATA HANDLERS ---

    public function get_stock_summary_report_html($data) {
        // Logic to generate HTML table for PDF based on $data filters
        // This typically calls the view 'report/stock_summary_report' with filtered data
        // Returning simple string for example:
        return "<h1>Stock Summary</h1><p>Generated on ".date('Y-m-d')."</p>";
    }
    
    public function get_inventory_valuation_report_html($data) {
        return "<h1>Inventory Valuation</h1><p>Calculated via FIFO/Average.</p>";
    }
    
    public function get_warranty_period_report_html($data) {
        return "<h1>Warranty Report</h1>";
    }

    // --- PDF WRAPPERS ---

    public function stock_import_pdf($html) {
        return app_pdf('purchase', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Purchase_pdf.php'), $html);
    }

    public function stock_export_pdf($html) {
        return app_pdf('delivery', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Delivery_pdf.php'), $html);
    }
    
    public function stock_summary_report_pdf($html) {
        return app_pdf('report', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Stock_summary_report_pdf.php'), $html);
    }
    
    public function inventory_valuation_report_pdf($html) {
        // Reusing stock summary PDF lib for simplicity, or create specific lib
        return app_pdf('report', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Stock_summary_report_pdf.php'), $html);
    }
    
    public function warranty_period_report_pdf($html) {
        return app_pdf('report', module_dir_path(WAREHOUSE_MODULE_NAME, 'libraries/pdf/Warranty_period_pdf.php'), $html);
    }

    // --- PDF HTML GENERATION (Transactional) ---
    
    public function get_stock_import_pdf_html($id) {
        $receipt = $this->get_goods_receipt($id);
        $details = $this->get_goods_receipt_detail($id);
        $company = get_option('invoice_company_name');
        
        $html = '<div style="text-align:center;"><h3>' . mb_strtoupper(_l('stock_received_docket')) . '</h3></div>';
        $html .= '<b>' . _l('code') . ': </b>' . $receipt->goods_receipt_code . '<br>';
        $html .= '<b>' . _l('date') . ': </b>' . _d($receipt->date_add) . '<br><br>';
        
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0; font-weight:bold;">
                    <th>' . _l('commodity_code') . '</th>
                    <th>' . _l('commodity_name') . '</th>
                    <th>' . _l('warehouse') . '</th>
                    <th>' . _l('bin_location') . '</th>
                    <th>' . _l('quantity') . '</th>
                  </tr>';
        
        foreach ($details as $row) {
            $item = $this->get_commodity($row['commodity_code']);
            $wh = $this->get_warehouse($row['warehouse_id']);
            $bin = ($row['bin_location_id'] > 0) ? $this->get_bin_locations($row['bin_location_id'])->bin_name : '-';
            
            $html .= '<tr>
                        <td>' . ($item ? $item->commodity_code : '') . '</td>
                        <td>' . ($item ? $item->description : '') . '</td>
                        <td>' . ($wh ? $wh->warehouse_name : '') . '</td>
                        <td>' . $bin . '</td>
                        <td>' . $row['quantities'] . '</td>
                      </tr>';
        }
        $html .= '</table>';
        
        return $html;
    }

    public function get_stock_export_pdf_html($id) {
        $delivery = $this->get_goods_delivery($id);
        $details = $this->get_goods_delivery_detail($id);
        
        $html = '<div style="text-align:center;"><h3>' . mb_strtoupper(_l('stock_delivery_docket')) . '</h3></div>';
        $html .= '<b>' . _l('code') . ': </b>' . $delivery->goods_delivery_code . '<br>';
        $html .= '<b>' . _l('customer') . ': </b>' . $delivery->customer_name . '<br><br>';
        
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0; font-weight:bold;">
                    <th>' . _l('commodity_code') . '</th>
                    <th>' . _l('description') . '</th>
                    <th>' . _l('bin_location') . '</th>
                    <th>' . _l('quantity') . '</th>
                  </tr>';
        
        foreach ($details as $row) {
            $item = $this->get_commodity($row['commodity_code']);
            $bin = ($row['bin_location_id'] > 0) ? $this->get_bin_locations($row['bin_location_id'])->bin_name : '-';

            $html .= '<tr>
                        <td>' . ($item ? $item->commodity_code : '') . '</td>
                        <td>' . ($item ? $item->description : '') . '</td>
                        <td>' . $bin . '</td>
                        <td>' . $row['quantities'] . '</td>
                      </tr>';
        }
        $html .= '</table>';
        
        return $html;
    }
}