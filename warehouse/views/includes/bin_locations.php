<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
            <h4 class="no-margin"><?php echo _l('wh_bin_locations'); ?></h4>
            <hr class="hr-panel-heading" />
            
            <a href="#" onclick="new_bin_location(); return false;" class="btn btn-info mbot15"><?php echo _l('add_new'); ?></a>

            <table class="table dt-table">
                <thead>
                    <th><?php echo _l('warehouse_name'); ?></th>
                    <th><?php echo _l('bin_name'); ?></th>
                    <th><?php echo _l('options'); ?></th>
                </thead>
                <tbody>
                    <?php if(isset($bin_locations)){
                        foreach($bin_locations as $bin){ ?>
                        <tr>
                            <td><?php echo $bin['warehouse_name']; ?></td>
                            <td><?php echo $bin['bin_name']; ?></td>
                            <td>
                                <button onclick="edit_bin_location(<?php echo $bin['id']; ?>, '<?php echo $bin['bin_name']; ?>', <?php echo $bin['warehouse_id']; ?>); return false;" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></button>
                                <a href="<?php echo admin_url('warehouse/delete_bin_location/'.$bin['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="bin_location_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('warehouse/bin_location')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('wh_bin_location'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id">
                <div class="form-group">
                    <label for="warehouse_id"><?php echo _l('warehouse_name'); ?></label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control selectpicker" data-live-search="true">
                        <?php foreach($warehouses as $wh){ ?>
                            <option value="<?php echo $wh['warehouse_id']; ?>"><?php echo $wh['warehouse_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php echo render_input('bin_name', 'bin_name'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
    function new_bin_location(){
        $('#bin_location_modal input[name="id"]').val('');
        $('#bin_location_modal input[name="bin_name"]').val('');
        $('#bin_location_modal').modal('show');
    }
    function edit_bin_location(id, name, wh){
        $('#bin_location_modal input[name="id"]').val(id);
        $('#bin_location_modal input[name="bin_name"]').val(name);
        $('#bin_location_modal select[name="warehouse_id"]').val(wh).change();
        $('#bin_location_modal').modal('show');
    }
</script>