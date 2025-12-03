<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><?php echo _l('wh_dashboard'); ?></h4>
                        <hr />
                        
                        <div class="row">
    <div class="col-md-4">
        <div class="panel_s widget-card-enhanced gradient-1">
            <div class="panel-body text-white">
                <div class="widget-icon"><i class="fa fa-money"></i></div>
                <div class="widget-details">
                    <h3 class="no-margin font-bold"><?php echo app_format_money($inventory_value_data->total_value ?? 0, get_base_currency()->symbol); ?></h3>
                    <span class="text-uppercase opacity-70"><?php echo _l('wh_total_inventory_value'); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel_s widget-card-enhanced gradient-2">
            <div class="panel-body text-white">
                <div class="widget-icon"><i class="fa fa-cubes"></i></div>
                <div class="widget-details">
                    <h3 class="no-margin font-bold"><?php echo number_format($inventory_value_data->total_units ?? 0); ?></h3>
                    <span class="text-uppercase opacity-70"><?php echo _l('wh_total_units_in_stock'); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel_s widget-card-enhanced gradient-3">
            <div class="panel-body text-white">
                <div class="widget-icon"><i class="fa fa-exclamation-triangle"></i></div>
                <div class="widget-details">
                    <h3 class="no-margin font-bold"><?php echo count($low_stock_alerts); ?></h3>
                    <span class="text-uppercase opacity-70"><?php echo _l('wh_low_stock_items'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
                            <div class="col-md-4">
                                <div class="widget-box-card bg-info text-white" style="padding:20px; border-radius:5px; color:white; background:#03a9f4;">
                                    <h3 class="no-margin"><?php echo number_format($inventory_value_data->total_units ?? 0); ?></h3>
                                    <span style="font-size:14px;"><?php echo _l('wh_total_units_in_stock'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="widget-box-card bg-warning text-white" style="padding:20px; border-radius:5px; color:white; background:#ff6f00;">
                                    <h3 class="no-margin"><?php echo count($low_stock_alerts); ?></h3>
                                    <span style="font-size:14px;"><?php echo _l('wh_low_stock_items'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <p class="bold text-center"><?php echo _l('wh_stock_distribution_by_warehouse'); ?></p>
                                <div style="height:350px;">
                                    <canvas id="wh_chart"></canvas>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <p class="bold text-danger text-center"><?php echo _l('wh_low_stock_alerts'); ?></p>
                                <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('item'); ?></th>
                                                <th><?php echo _l('min_qty'); ?></th>
                                                <th><?php echo _l('current_qty'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(count($low_stock_alerts) > 0){ 
                                                foreach($low_stock_alerts as $item){ ?>
                                                <tr>
                                                    <td><a href="<?php echo admin_url('warehouse/view_commodity_detail/'.$item['id']); ?>"><?php echo $item['description']; ?></a></td>
                                                    <td><?php echo $item['inventory_number_min']; ?></td>
                                                    <td class="text-danger bold"><?php echo $item['current_stock']; ?></td>
                                                </tr>
                                            <?php } } else { ?>
                                                <tr><td colspan="3" class="text-center"><?php echo _l('no_alerts'); ?></td></tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url('assets/plugins/Chart.js/Chart.min.js'); ?>"></script>
<?php require 'modules/warehouse/assets/js/dashboard/visual_dashboard.js.php'; ?>
</body>
</html>