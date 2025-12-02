<?php

trait WarehouseReportsController {

    /**
     * Main Reports View
     */
    public function manage_report() {
        if (!has_permission('wh_report', '', 'view')) {
            access_denied('warehouse');
        }

        $data['group'] = $this->input->get('group');
        $data['title'] = _l('als_report');
        
        // Define Report Tabs
        $data['tab'][] = 'stock_summary_report';
        $data['tab'][] = 'inventory_valuation_report';
        $data['tab'][] = 'warranty_period_report';
        $data['tab'][] = 'stock_balance_report'; // If you have this view

        if ($data['group'] == '') {
            $data['group'] = 'stock_summary_report';
        }
        
        // Load data specific to the selected report
        if($data['group'] == 'stock_summary_report'){
            $data['warehouses'] = $this->warehouse_model->get_warehouse();
        } elseif ($data['group'] == 'inventory_valuation_report') {
            $data['warehouses'] = $this->warehouse_model->get_warehouse();
        }
        // ... other report data loading ...

        $data['tabs']['view'] = 'report/' . $data['group'];
        $this->load->view('report/manage_report', $data);
    }

    // --- TABLE DATA GENERATORS ---
    // These functions return JSON data for the DataTables in the reports

    public function table_stock_summary_report() {
        $this->app->get_table_data(module_views_path('warehouse', 'report/table_stock_summary_report'));
    }
    
    public function table_inventory_valuation_report() {
        $this->app->get_table_data(module_views_path('warehouse', 'report/table_inventory_valuation_report'));
    }
    
    public function table_warranty_period_report() {
        $this->app->get_table_data(module_views_path('warehouse', 'report/table_warranty_period'));
    }

    // --- PDF GENERATORS ---
    // These use the PDF HTML logic we put in WarehouseReportsTrait (Model)

    public function stock_summary_report_pdf() {
        $data = $this->input->post();
        if (!$data) { redirect(admin_url('warehouse/manage_report?group=stock_summary_report')); }

        // Call Model to generate HTML
        $html = $this->warehouse_model->get_stock_summary_report_html($data);
        
        // Generate PDF
        $pdf = $this->warehouse_model->stock_summary_report_pdf($html);
        $pdf->Output('stock_summary_report.pdf', 'I');
    }

    public function inventory_valuation_report_pdf() {
        $data = $this->input->post();
        if (!$data) { redirect(admin_url('warehouse/manage_report?group=inventory_valuation_report')); }

        $html = $this->warehouse_model->get_inventory_valuation_report_html($data);
        $pdf = $this->warehouse_model->inventory_valuation_report_pdf($html);
        $pdf->Output('inventory_valuation_report.pdf', 'I');
    }
    
    public function warranty_period_report_pdf() {
        $data = $this->input->post();
        if (!$data) { redirect(admin_url('warehouse/manage_report?group=warranty_period_report')); }

        $html = $this->warehouse_model->get_warranty_period_report_html($data);
        $pdf = $this->warehouse_model->warranty_period_report_pdf($html);
        $pdf->Output('warranty_period_report.pdf', 'I');
    }

    // --- TRANSACTION PDFS (Receipts/Deliveries) ---
    // Note: These are often accessed via the Table action buttons

    public function stock_import_pdf($id) {
        if (!has_permission('wh_stock_import', '', 'view') && !has_permission('wh_stock_import', '', 'view_own')) {
            access_denied('warehouse');
        }
        
        $html = $this->warehouse_model->get_stock_import_pdf_html($id); // This uses the code we fixed in the previous step
        $pdf = $this->warehouse_model->stock_import_pdf($html);
        $pdf->Output('goods_receipt_'. $id .'.pdf', 'I');
    }

    public function stock_export_pdf($id) {
        if (!has_permission('wh_stock_export', '', 'view') && !has_permission('wh_stock_export', '', 'view_own')) {
             access_denied('warehouse');
        }
        
        $html = $this->warehouse_model->get_stock_export_pdf_html($id);
        $pdf = $this->warehouse_model->stock_export_pdf($html);
        $pdf->Output('goods_delivery_'. $id .'.pdf', 'I');
    }
    
    public function barcode_print($id) {
        // Logic to print barcode PDF for an item
        $data['id'] = $id;
        $data['item'] = $this->warehouse_model->get_commodity($id);
        $this->load->view('print_barcode_pdf', $data);
    }
}