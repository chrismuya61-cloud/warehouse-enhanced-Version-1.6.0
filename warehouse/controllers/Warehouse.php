<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(__DIR__ . '/traits/WarehouseSettingsController.php');
require_once(__DIR__ . '/traits/WarehouseItemsController.php');
require_once(__DIR__ . '/traits/WarehouseInventoryController.php');
require_once(__DIR__ . '/traits/WarehouseTransfersController.php');
require_once(__DIR__ . '/traits/WarehouseReportsController.php');

class Warehouse extends AdminController {
    use WarehouseSettingsController;
    use WarehouseItemsController;
    use WarehouseInventoryController;
    use WarehouseTransfersController;
    use WarehouseReportsController;

    public function __construct() {
        parent::__construct();
        $this->load->model('warehouse_model');
    }
}