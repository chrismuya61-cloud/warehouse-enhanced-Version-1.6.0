<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                             <a href="<?php echo admin_url('warehouse/warranty_dashboard'); ?>" class="btn btn-default mright5"><?php echo _l('warranty_dashboard'); ?></a>
                            <h4 class="no-margin pull-right"><?php echo _l('warranty_claims'); ?></h4>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable([
                            _l('id'),
                            _l('claim_date'),
                            _l('commodity_name'),
                            _l('serial_number'),
                            _l('customer'),
                            _l('issue_description'),
                            _l('status'),
                            _l('options'),
                        ], 'warranty-claims'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       initDataTable('.table-warranty-claims', admin_url + 'warehouse/warranty_claims');
   });
</script>
</body>
</html>
