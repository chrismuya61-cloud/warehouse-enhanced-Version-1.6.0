<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(__DIR__ . '/traits/WarehouseSettingsTrait.php');
require_once(__DIR__ . '/traits/WarehouseItemsTrait.php');
require_once(__DIR__ . '/traits/WarehouseInventoryTrait.php');
require_once(__DIR__ . '/traits/WarehouseTransfersTrait.php');
require_once(__DIR__ . '/traits/WarehouseReportsTrait.php');

class Warehouse_model extends App_Model {
    use WarehouseSettingsTrait;
    use WarehouseItemsTrait;
    use WarehouseInventoryTrait;
    use WarehouseTransfersTrait;
    use WarehouseReportsTrait;

    public function __construct() {
        parent::__construct();
        $this->load->helper('warehouse/warehouse');
    }

    public function add_activity_log($data) {
        $this->db->insert(db_prefix() . 'wh_activity_log', $data);
        return true;
    }
}