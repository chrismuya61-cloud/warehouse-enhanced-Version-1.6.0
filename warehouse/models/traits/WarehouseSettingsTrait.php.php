<?php
trait WarehouseSettingsTrait {
    public function get_bin_locations($id = false) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wh_bin_locations')->row();
        }
        $this->db->select('t1.*, t2.warehouse_name');
        $this->db->from(db_prefix() . 'wh_bin_locations t1');
        $this->db->join(db_prefix() . 'warehouse t2', 't1.warehouse_id = t2.warehouse_id', 'left');
        return $this->db->get()->result_array();
    }

    public function add_bin_location($data) {
        $this->db->insert(db_prefix() . 'wh_bin_locations', $data);
        return $this->db->insert_id();
    }

    public function update_bin_location($data, $id) {
        $this->db->where('id', $id)->update(db_prefix() . 'wh_bin_locations', $data);
        return true;
    }
    
    public function get_warehouse() {
         return $this->db->get(db_prefix() . 'warehouse')->result_array();
    }
}