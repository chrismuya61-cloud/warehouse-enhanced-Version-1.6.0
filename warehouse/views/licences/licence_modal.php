<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="licence_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo isset($licence) ? _l('edit_licence') : _l('new_licence'); ?></h4>
            </div>
            <?php echo form_open(admin_url('warehouse/add_edit_licence/' . (isset($licence) ? $licence->id : '')), ['id'=>'licence-form']); ?>
            <div class="modal-body">
                
                <?php if(!isset($licence)){ ?>
                    <div class="row">
                        <div class="col-md-12">
                             <?php echo render_select('customer_id', $clients, ['userid', 'company'], 'customer'); ?>
                        </div>
                         <div class="col-md-12">
                             <div class="form-group">
                                <label>Available Serials</label>
                                <select name="serial_number" id="serial_number" class="form-control selectpicker"></select>
                             </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info">Serial: <strong><?php echo $licence->serial_number; ?></strong></div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_textarea('licence_key', 'Licence Key', isset($licence) ? $licence->licence_key : ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_date_input('validity_start_date', 'Start Date', isset($licence) ? _d($licence->validity_start_date) : _d(date('Y-m-d'))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_date_input('validity_end_date', 'End Date', isset($licence) ? _d($licence->validity_end_date) : ''); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_select('licence_type', [['id'=>'temporary','name'=>'Temporary'],['id'=>'permanent','name'=>'Permanent']], ['id','name'], 'Type', isset($licence)?$licence->licence_type:'temporary'); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_select('status', [['id'=>'draft','name'=>'Draft'],['id'=>'active','name'=>'Active'],['id'=>'expired','name'=>'Expired']], ['id','name'], 'Status', isset($licence)?$licence->status:'draft'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
    init_datepicker();
    init_selectpicker();
    
    // Auto-fetch serials on customer change
    $('select[name="customer_id"]').on('change', function(){
        var id = $(this).val();
        $.post(admin_url + 'warehouse/get_available_serials_for_licensing', {clientid: id}).done(function(res){
             res = JSON.parse(res);
             var html = '<option value=""></option>';
             $.each(res, function(i, item){
                 html += '<option value="'+item.serial_number+'">'+item.serial_number+' ('+item.description+')</option>';
             });
             $('select[name="serial_number"]').html(html).selectpicker('refresh');
        });
    });
</script>
