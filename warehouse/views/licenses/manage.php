<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left display-block" onclick="new_license(); return false;">New License</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <table class="table dt-table" data-order-col="0" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Invoice</th>
                                    <th>Client</th>
                                    <th>Serial</th>
                                    <th>License Key</th>
                                    <th>Type</th>
                                    <th>Issue Date</th>
                                    <th>Expiry</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $this->load->model('warehouse/warehouse_model');
                                $licenses = $this->warehouse_model->get_licenses();
                                foreach($licenses as $license){ 
                                    $status_label = $license['license_type'] == 'permanent' ? 'label-success' : 'label-warning';
                                ?>
                                <tr>
                                    <td><?php echo $license['id']; ?></td>
                                    <td><a href="<?php echo admin_url('invoices/list_invoices/'.$license['invoice_id']); ?>"><?php echo format_invoice_number($license['invoice_id']); ?></a></td>
                                    <td><a href="<?php echo admin_url('clients/client/'.$license['client_id']); ?>"><?php echo $license['client_name']; ?></a></td>
                                    <td><?php echo $license['serial_number']; ?></td>
                                    <td><code><?php echo $license['license_key']; ?></code></td>
                                    <td><span class="label <?php echo $status_label; ?>"><?php echo _l($license['license_type']); ?></span></td>
                                    <td><?php echo _d($license['issue_date']); ?></td>
                                    <td><?php echo $license['expiry_date'] ? _d($license['expiry_date']) : 'N/A'; ?></td>
                                    <td>
                                        <a href="#" onclick="edit_license(<?php echo htmlspecialchars(json_encode($license)); ?>); return false;" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                        <a href="<?php echo admin_url('warehouse/delete_license/'.$license['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="license_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('warehouse/licenses')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">License</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="client_id" id="client_id">
                <div class="form-group">
                    <label for="invoice_id">Invoice</label>
                    <select name="invoice_id" id="invoice_id" class="selectpicker" data-width="100%" data-live-search="true" onchange="invoice_change(this)">
                        <option value=""></option>
                        <?php foreach($invoices as $inv){ ?>
                        <option value="<?php echo $inv['id']; ?>"><?php echo format_invoice_number($inv['id']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('item_id', 'Item ID/Name', '', 'text'); ?></div>
                    <div class="col-md-6"><?php echo render_input('serial_number', 'Serial Number'); ?></div>
                </div>
                <div class="form-group">
                    <label for="license_type">Type</label>
                    <select name="license_type" id="license_type" class="selectpicker" data-width="100%">
                        <option value="temporary">Temporary</option>
                        <option value="permanent">Permanent</option>
                    </select>
                </div>
                <?php echo render_textarea('license_key', 'License Key'); ?>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('issue_date', 'Issue Date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_date_input('expiry_date', 'Expiry Date'); ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info">Submit</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function new_license(){ $('#license_modal').modal('show'); $('#id').val(''); }
    function edit_license(data){
        $('#license_modal').modal('show');
        $('#id').val(data.id);
        $('#invoice_id').selectpicker('val', data.invoice_id);
        $('#item_id').val(data.item_id);
        $('#serial_number').val(data.serial_number);
        $('#license_key').val(data.license_key);
        $('#license_type').selectpicker('val', data.license_type);
        $('#issue_date').val(data.issue_date);
        $('#expiry_date').val(data.expiry_date);
    }
    function invoice_change(select){
        if($(select).val() != ''){
            $.get(admin_url + 'warehouse/get_invoice_license_info/' + $(select).val(), function(response){
                var data = JSON.parse(response);
                $('#client_id').val(data.client_id);
                $('#license_type').selectpicker('val', data.license_type);
            });
        }
    }
</script>
</body>
</html>