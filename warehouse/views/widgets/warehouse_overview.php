<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$CI = &get_instance();
$CI->load->model('warehouse/warehouse_model');
$stats = $CI->warehouse_model->get_dashboard_overview_data();
?>
<div class="widget" id="widget-warehouse_overview" data-name="Warehouse Overview">
    <div class="panel_s user-data">
        <div class="panel-body">
            <div class="widget-dragger"></div>
            <h4 class="no-margin font-bold"><i class="fa fa-cubes"></i> Warehouse Overview</h4>
            <hr />
            <div class="row">
                <div class="col-md-3 border-right text-center">
                    <h3 class="bold text-danger"><?php echo $stats['low_stock']; ?></h3>
                    <span class="text-muted text-danger">Low Stock Items</span><br>
                    <?php if($stats['low_stock'] > 0){ ?>
                        <a href="<?php echo admin_url('warehouse/auto_create_pr_from_low_stock'); ?>" class="btn btn-xs btn-info mtop10">Auto Create PR</a>
                    <?php } ?>
                </div>
                <div class="col-md-3 border-right text-center">
                    <h3 class="bold text-warning"><?php echo $stats['expiring_licenses']; ?></h3>
                    <span class="text-muted">Expiring Licenses</span><br>
                    <a href="<?php echo admin_url('warehouse/licenses'); ?>" class="btn btn-xs btn-default mtop10">View</a>
                </div>
                <div class="col-md-3 border-right text-center">
                    <h3 class="bold text-info"><?php echo $stats['pending_deliveries']; ?></h3>
                    <span class="text-muted">Pending Verification</span><br>
                    <a href="<?php echo admin_url('warehouse/manage_delivery'); ?>" class="btn btn-xs btn-default mtop10">Check</a>
                </div>
                <div class="col-md-3 text-center">
                    <h3 class="bold text-success"><?php echo $stats['pending_internal']; ?></h3>
                    <span class="text-muted">Pending Transfers</span><br>
                    <a href="<?php echo admin_url('warehouse/manage_internal_delivery'); ?>" class="btn btn-xs btn-default mtop10">View</a>
                </div>
            </div>
        </div>
    </div>
</div>