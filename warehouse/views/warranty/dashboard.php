<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-success"><?php echo $total_active; ?></h3>
                        <span class="text-muted"><?php echo _l('total_active_warranties'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-warning"><?php echo $expiring_soon; ?></h3>
                        <span class="text-muted"><?php echo _l('warranty_expiring_soon'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-danger"><?php echo $total_expired; ?></h3>
                        <span class="text-muted"><?php echo _l('total_expired_warranties'); ?></span>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-info"><?php echo $open_claims; ?></h3>
                        <span class="text-muted"><?php echo _l('open_claims'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
             <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('warehouse/warranty_list'); ?>" class="btn btn-info mright5"><?php echo _l('warranty_list'); ?></a>
                            <a href="<?php echo admin_url('warehouse/warranty_claims'); ?>" class="btn btn-default"><?php echo _l('warranty_claims'); ?></a>
                        </div>
                        <hr class="hr-panel-heading" />
                         <h4 class="no-margin"><?php echo _l('warranty_list'); ?></h4>
                         <div class="clearfix"></div>
                         <br>
                        <?php render_datatable([
                            _l('commodity_name'),
                            _l('serial_number'),
                            _l('customer'),
                            _l('warranty_end_date'),
                            _l('time_remaining'),
                            _l('status'),
                            _l('options'),
                        ], 'warranty-list'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       initDataTable('.table-warranty-list', admin_url + 'warehouse/warranty_list', undefined, undefined, 'undefined', [3, 'asc']);
   });
</script>
</body>
</html>
