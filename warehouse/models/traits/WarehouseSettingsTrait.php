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
	// --- LEGACY SETTINGS SUPPORT (Units, Colors, Brands, etc) ---

    public function add_unit($data) {
        $this->db->insert(db_prefix() . 'ware_unit_type', $data);
        return $this->db->insert_id();
    }
    public function get_units($id = '') {
        if (is_numeric($id)) {
            $this->db->where('unit_type_id', $id);
            return $this->db->get(db_prefix() . 'ware_unit_type')->row();
        }
        return $this->db->get(db_prefix() . 'ware_unit_type')->result_array();
    }
    public function delete_unit($id) {
        $this->db->where('unit_type_id', $id);
        $this->db->delete(db_prefix() . 'ware_unit_type');
        return true;
    }

    public function add_commodity_type($data) {
        $this->db->insert(db_prefix() . 'ware_commodity_type', $data);
        return $this->db->insert_id();
    }
    public function get_commodity_type($id = '') {
        if (is_numeric($id)) {
            $this->db->where('commodity_type_id', $id);
            return $this->db->get(db_prefix() . 'ware_commodity_type')->row();
        }
        return $this->db->get(db_prefix() . 'ware_commodity_type')->result_array();
    }
    public function delete_commodity_type($id) {
        $this->db->where('commodity_type_id', $id);
        $this->db->delete(db_prefix() . 'ware_commodity_type');
        return true;
    }

    public function add_size($data) {
        $this->db->insert(db_prefix() . 'wh_sizes', $data);
        return $this->db->insert_id();
    }
    public function get_size($id = '') {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'wh_sizes')->row();
        }
        return $this->db->get(db_prefix() . 'wh_sizes')->result_array();
    }
    public function delete_size($id) {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'wh_sizes');
        return true;
    }
    
    // (Repeat similar logic for 'colors', 'styles', 'models' if your business uses them)
}