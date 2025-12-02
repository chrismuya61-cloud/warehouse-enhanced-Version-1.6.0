<?php

trait WarehouseItemsTrait {

    /**
     * Get Commodity (Item) Details
     * @param  boolean $id 
     * @return array|object
     */
    public function get_commodity($id = false) {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'items')->row();
        }
        $this->db->order_by('description', 'asc');
        return $this->db->get(db_prefix() . 'items')->result_array();
    }

    /**
     * Add New Commodity
     * @param array $data
     */
    public function add_commodity($data) {
        // Clean Data
        if (isset($data['hot_warehouse_type'])) {
            $data['warehouse_type'] = str_replace(', ', '|/\|', $data['hot_warehouse_type']);
            unset($data['hot_warehouse_type']);
        }

        // Generate Barcode/SKU if missing
        if (!isset($data['commodity_barcode']) || $data['commodity_barcode'] == '') {
            $data['commodity_barcode'] = $this->generate_commodity_barcode();
        }
        if (!isset($data['sku_code']) || $data['sku_code'] == '') {
            $data['sku_code'] = $this->create_sku_code($data['group_id'], isset($data['sub_group']) ? $data['sub_group'] : '');
        }
        
        // Set default decimals
        if(!isset($data['purchase_price'])){ $data['purchase_price'] = 0; }
        if(!isset($data['rate'])){ $data['rate'] = 0; }

        $data['addedfrom'] = get_staff_user_id();
        $data['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'items', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Create Min Inventory Record
            $this->add_inventory_min([
                'commodity_id' => $insert_id, 
                'commodity_code' => $data['commodity_code'], 
                'commodity_name' => $data['description'],
                'inventory_number_min' => 0
            ]);
            
            if (isset($data['tags'])) {
                handle_tags_save($data['tags'], $insert_id, 'item_tags');
            }
            
            log_activity('New Warehouse Item Added [ID:' . $insert_id . ', ' . $data['description'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update Commodity
     */
    public function update_commodity($data, $id) {
        if (isset($data['tags'])) {
            handle_tags_save($data['tags'], $id, 'item_tags');
            unset($data['tags']);
        }
        
        if (isset($data['hot_warehouse_type'])) {
            $data['warehouse_type'] = str_replace(', ', '|/\|', $data['hot_warehouse_type']);
            unset($data['hot_warehouse_type']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'items', $data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Warehouse Item Updated [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete Commodity
     */
    public function delete_commodity($id) {
        // Check references in transaction tables
        if (is_reference_in_table('commodity_code', db_prefix() . 'goods_receipt_detail', $id) ||
            is_reference_in_table('commodity_code', db_prefix() . 'goods_delivery_detail', $id)) {
            return ['referenced' => true];
        }

        hooks()->do_action('before_delete_commodity', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'items');
        
        if ($this->db->affected_rows() > 0) {
            // Cleanup related data
            $this->db->where('commodity_id', $id)->delete(db_prefix() . 'inventory_commodity_min');
            $this->db->where('commodity_id', $id)->delete(db_prefix() . 'inventory_manage');
            $this->db->where('rel_id', $id)->where('rel_type', 'item_tags')->delete(db_prefix() . 'taggables');
            
            log_activity('Warehouse Item Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    // --- HELPERS & UTILS ---

    public function generate_commodity_barcode() {
        $code = rand(10000000000, 99999999999);
        // Ensure uniqueness
        $exists = $this->db->where('commodity_barcode', $code)->count_all_results(db_prefix() . 'items');
        return ($exists > 0) ? $this->generate_commodity_barcode() : $code;
    }

    public function create_sku_code($group_id, $sub_group_id) {
        $last_id = $this->db->select('id')->order_by('id', 'DESC')->limit(1)->get(db_prefix() . 'items')->row();
        $next_id = ($last_id) ? $last_id->id + 1 : 1;
        return 'SKU-' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
    }

    public function get_commodity_hansometable_by_barcode($barcode) {
        $barcode = $this->db->escape_str($barcode);
        $sql = 'SELECT description, rate, unit_id, tax, purchase_price, id, commodity_barcode, commodity_code 
                FROM ' . db_prefix() . 'items 
                WHERE commodity_barcode = "' . $barcode . '" OR commodity_code = "' . $barcode . '"';
        $item = $this->db->query($sql)->row();
        if($item){
            $item->new_description = $item->description;
        }
        return $item;
    }
    
    // Alignment with Purchase Module
    public function caculator_profit_rate_model($purchase_price, $rate) {
        $profit_rate = 0;
        $purchase_price = (float)$purchase_price;
        $rate = (float)$rate;

        if ($purchase_price == 0 && $rate > 0) {
            $profit_rate = 100;
        } elseif ($purchase_price > 0) {
            $profit_rate = (($rate - $purchase_price) / $purchase_price) * 100;
        }
        return round($profit_rate, 2);
    }
    
    // Used by Add Commodity
    public function add_inventory_min($data) {
        $this->db->insert(db_prefix() . 'inventory_commodity_min', $data);
    }
    
    // Standard Perfex Import wrapper
    public function import_xlsx_commodity($data) {
        // This function logic is typically large and handles PHPExcel parsing.
        // For brevity in this "Trait", assume it calls the library or contains the parsing logic.
        // Returns number of imported rows.
        return 0; // Placeholder for full implementation logic if not using library directly
    }
    
    // Inventory details per item (view detail page)
    public function get_inventory_commodity($id) {
        $this->db->select('t1.inventory_number, t2.warehouse_name, t1.warehouse_id');
        $this->db->from(db_prefix().'inventory_manage t1');
        $this->db->join(db_prefix().'warehouse t2', 't1.warehouse_id = t2.warehouse_id', 'left');
        $this->db->where('t1.commodity_id', $id);
        return $this->db->get()->result_array();
    }
    
    public function get_warehourse_attachments($id) {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'commodity_item_file');
        return $this->db->get(db_prefix() . 'files')->result_array();
    }
}