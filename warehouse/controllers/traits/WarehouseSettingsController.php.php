<?php
trait WarehouseSettingsController {
    public function setting() {
        if (!has_permission('wh_setting', '', 'view')) access_denied();
        $data['group'] = $this->input->get('group');
        $data['tab'][] = 'bin_locations';
        if ($data['group'] == 'bin_locations') {
            $data['bin_locations'] = $this->warehouse_model->get_bin_locations();
            $data['warehouses'] = $this->warehouse_model->get_warehouse();
        }
        $data['tabs']['view'] = 'includes/' . $data['group'];
        $this->load->view('manage_setting', $data);
    }

    public function bin_location() {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$data['id']) $this->warehouse_model->add_bin_location($data);
            else $this->warehouse_model->update_bin_location($data, $data['id']);
            redirect(admin_url('warehouse/setting?group=bin_locations'));
        }
    }
}