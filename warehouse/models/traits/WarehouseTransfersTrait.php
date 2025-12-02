<?php
trait WarehouseTransfersTrait {

    // Bulk Creation Logic (Partial Support)
    public function bulk_create_goods_delivery($warehouse_id) {
        $invoices = $this->db->query("SELECT * FROM ".db_prefix()."invoices WHERE status != 5 AND id NOT IN (SELECT rel_document FROM ".db_prefix()."goods_delivery WHERE rel_type=1)")->result_array();
        $results = ['success' => [], 'failed' => []];

        foreach ($invoices as $inv) {
            $items = $this->db->where('rel_id', $inv['id'])->where('rel_type','invoice')->get(db_prefix().'itemable')->result_array();
            $new_items = [];
            
            foreach($items as $item){
                 // Logic to find item ID by name
                 $item_id = $this->get_itemid_from_name($item['description']);
                 if($item_id){
                     $inv_qty = $this->get_quantity_inventory($warehouse_id, $item_id);
                     if($inv_qty && $inv_qty->inventory_number >= $item['qty']){
                         $new_items[] = ['commodity_code'=>$item_id, 'quantities'=>$item['qty'], 'unit_price'=>$item['rate']];
                     }
                 }
            }

            if(!empty($new_items)){
                $data = ['rel_type'=>1, 'rel_document'=>$inv['id'], 'warehouse_id'=>$warehouse_id, 'newitems'=>$new_items];
                $this->add_goods_delivery($data);
                $results['success'][] = $inv['id'];
            } else {
                $results['failed'][] = $inv['id'];
            }
        }
        return $results;
    }

    public function auto_match_transaction($inputs, $wh_id) {
        $matched = []; $unmatched = [];
        foreach ($inputs as $in) {
            $in = trim($in); if(!$in) continue;
            $sql = "SELECT t2.id FROM ".db_prefix()."inventory_manage t1 JOIN ".db_prefix()."items t2 ON t1.commodity_id=t2.id WHERE t1.warehouse_id='$wh_id' AND t1.inventory_number>0 AND (t2.commodity_code='$in' OR t2.commodity_barcode='$in') LIMIT 1";
            $m = $this->db->query($sql)->row();
            if ($m) $matched[]=['commodity_id'=>$m->id, 'quantity'=>1]; else $unmatched[]=$in;
        }
        return ['matched'=>$matched, 'unmatched'=>$unmatched];
    }

    public function add_goods_delivery($data) {
        if(isset($data['newitems'])) { $items = $data['newitems']; unset($data['newitems']); }
        $data['goods_delivery_code'] = get_warehouse_option('inventory_delivery_number_prefix').get_warehouse_option('next_inventory_delivery_mumber');
        $data['addedfrom'] = get_staff_user_id();
        $this->db->insert(db_prefix().'goods_delivery', $data);
        $id = $this->db->insert_id();
        
        if ($id) {
            foreach ($items as $item) {
                $item['goods_delivery_id'] = $id;
                $this->db->insert(db_prefix().'goods_delivery_detail', $item);
            }
            $this->update_inventory_setting(['next_inventory_delivery_mumber' => get_warehouse_option('next_inventory_delivery_mumber')+1]);
        }
        return $id;
    }
}