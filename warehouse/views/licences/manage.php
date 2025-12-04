<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="add_licence(); return false;" class="btn btn-info mright5"><?php echo _l('new_licence'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable([
                            _l('serial_number'),
                            _l('customer'),
                            _l('item'),
                            _l('licence_type'),
                            _l('validity_end_date'),
                            _l('status'),
                            _l('options'),
                        ], 'licences'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       initDataTable('.table-licences', admin_url + 'warehouse/licence_management');
   });
   function add_licence() {
       var url = admin_url + 'warehouse/get_licence_modal';
       requestGet(url).done(function(response) {
           $('#_medium_modal_wrapper').html(response);
           $('#licence_modal').modal('show');
       });
   }
   function edit_licence(id) {
       var url = admin_url + 'warehouse/get_licence_modal/' + id;
       requestGet(url).done(function(response) {
           $('#_medium_modal_wrapper').html(response);
           $('#licence_modal').modal('show');
       });
   }
</script>
</body>
</html>