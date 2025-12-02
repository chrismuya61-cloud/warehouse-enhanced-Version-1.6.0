<?php
trait WarehouseInventoryTrait {
    public function get_quantity_inventory($warehouse_id, $commodity_id) {
        $sql = 'SELECT warehouse_id, commodity_id, sum(inventory_number) as inventory_number from ' . db_prefix() . 'inventory_manage where warehouse_id = ' . $this->db->escape_str($warehouse_id) . ' AND commodity_id = ' . $this->db->escape_str($commodity_id) .' group by warehouse_id, commodity_id';
        $result = $this->db->query($sql)->row();
        if(!$result){ $result = new stdClass(); $result->inventory_number = 0; }
        return $result;
    }

    public function add_inventory_manage($data, $status) {
        $this->db->where('warehouse_id', $data['warehouse_id']);
        $this->db->where('commodity_id', $data['commodity_code']);
        $this->db->where('bin_location_id', $data['bin_location_id'] ?? 0);
        
        if(isset($data['lot_number']) && $data['lot_number'] != '') $this->db->where('lot_number', $data['lot_number']);
        else $this->db->group_start()->where('lot_number', '')->or_where('lot_number', null)->group_end();

        $existing = $this->db->get(db_prefix().'inventory_manage')->row();

        if ($existing) {
            $new_qty = ($status == 1) ? $existing->inventory_number + $data['quantities'] : $existing->inventory_number - $data['quantities'];
            $this->db->where('id', $existing->id)->update(db_prefix() . 'inventory_manage', ['inventory_number' => $new_qty]);
        } else {
            if ($status == 1) {
                $insert = [
                    'warehouse_id' => $data['warehouse_id'], 'commodity_id' => $data['commodity_code'],
                    'inventory_number' => $data['quantities'], 'bin_location_id' => $data['bin_location_id'] ?? 0,
                    'purchase_price' => $data['unit_price'] ?? 0, 'lot_number' => $data['lot_number'] ?? ''
                ];
                $this->db->insert(db_prefix() . 'inventory_manage', $insert);
            }
        }
        return true;
    }

    // STOCK TAKE LOGIC
    public function add_stock_take($data) {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $data['status'] = 0;
        $this->db->insert(db_prefix().'wh_stock_take', $data);
        return $this->db->insert_id();
    }

    public function save_stock_take_inventory($id, $items) {
        $this->db->where('stock_take_id', $id)->delete(db_prefix().'wh_stock_take_inventory');
        $batch = [];
        foreach($items as $i) {
            $sys = (float)$i['system_quantity'];
            $phy = (float)$i['physical_quantity'];
            
            if($sys != $phy || $phy > 0){
                $batch[] = [
                    'stock_take_id'=>$id, 
                    'commodity_id'=>$i['commodity_id'], 
                    'bin_location_id'=>$i['bin_location_id']??0,
                    'system_quantity'=>$sys, 
                    'physical_quantity'=>$phy, 
                    'adjustment_type'=>($phy > $sys ? 'gain' : 'loss'), 
                    'adjustment_value'=>abs($phy-$sys)
                ];
            }
        }
        if(!empty($batch)) $this->db->insert_batch(db_prefix().'wh_stock_take_inventory', $batch);
        return true;
    }

    public function submit_stock_take_approval($id) {
        $this->db->where('id', $id)->update(db_prefix().'wh_stock_take', ['status' => 1]);
        return $this->approve_stock_take_adjustment($id);
    }

    public function approve_stock_take_adjustment($id) {
        $adjs = $this->db->where('stock_take_id', $id)->get(db_prefix().'wh_stock_take_inventory')->result_array();
        $head = $this->db->where('id', $id)->get(db_prefix().'wh_stock_take')->row();
        
        foreach($adjs as $adj){
            if($adj['adjustment_type'] == 'none') continue;
            $data = ['warehouse_id'=>$head->warehouse_id, 'commodity_code'=>$adj['commodity_id'], 'quantities'=>$adj['adjustment_value'], 'bin_location_id'=>$adj['bin_location_id']];
            $this->add_inventory_manage($data, ($adj['adjustment_type']=='gain'?1:2));
        }
        return true;
    }
}