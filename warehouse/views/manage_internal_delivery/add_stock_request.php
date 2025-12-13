<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open(admin_url('warehouse/create_stock_request'), array('id'=>'add_stock_request')); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12"><h4 class="no-margin font-bold"><i class="fa fa-refresh"></i> Request Stock (Pull)</h4><hr /></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Request From (Remote Warehouse)</label>
                                <select name="from_stock_name" class="selectpicker" data-width="100%" data-live-search="true" required>
                                    <option value=""></option>
                                    <?php foreach($warehouses as $wh){ ?>
                                        <option value="<?php echo $wh['warehouse_id']; ?>"><?php echo $wh['warehouse_code'] . ' - ' . $wh['warehouse_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Receive Into (Local Warehouse)</label>
                                <select name="to_stock_name" class="selectpicker" data-width="100%" data-live-search="true" required>
                                    <option value=""></option>
                                    <?php foreach($warehouses as $wh){ ?>
                                        <option value="<?php echo $wh['warehouse_id']; ?>"><?php echo $wh['warehouse_code'] . ' - ' . $wh['warehouse_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <br>
                        <table class="table items">
                            <thead><tr><th>Item</th><th width="15%">Quantity</th><th width="5%"></th></tr></thead>
                            <tbody id="request_items">
                                <tr class="main">
                                    <td><select name="newitems[0][commodity_code]" class="selectpicker" data-width="100%" data-live-search="true"><option value=""></option><?php foreach($commodity_code_name as $item){ ?><option value="<?php echo $item['id']; ?>"><?php echo $item['label']; ?></option><?php } ?></select></td>
                                    <td><input type="number" name="newitems[0][quantities]" class="form-control" value="1"></td>
                                    <td><button type="button" class="btn btn-success" onclick="add_request_row()"><i class="fa fa-plus"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-right"><button type="submit" class="btn btn-info">Send Request</button></div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var item_index = 1;
    function add_request_row(){
        var html = '<tr><td><select name="newitems['+item_index+'][commodity_code]" class="selectpicker" data-width="100%" data-live-search="true">' + $('select[name="newitems[0][commodity_code]"]').html() + '</select></td><td><input type="number" name="newitems['+item_index+'][quantities]" class="form-control" value="1"></td><td><button type="button" class="btn btn-danger" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td></tr>';
        $('#request_items').append(html);
        $('#request_items').find('.selectpicker').last().selectpicker('refresh');
        item_index++;
    }
</script>
</body>
</html>