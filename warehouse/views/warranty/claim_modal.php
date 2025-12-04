<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="warranty_claim_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('file_claim'); ?></h4>
            </div>
            
            <?php echo form_open(admin_url('warehouse/add_edit_claim'), ['id' => 'warranty-claim-form']); ?>
            <div class="modal-body">
                
                <input type="hidden" name="detail_id" value="<?php echo $detail_id; ?>">
                <input type="hidden" name="commodity_id" value="<?php echo $commodity_id; ?>">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <?php echo _l('commodity_name'); ?>: <strong><?php echo isset($item) ? $item->description : ''; ?></strong>
                        </div>

                        <?php echo render_date_input('claim_date', 'claim_date', date('Y-m-d')); ?>

                        <?php echo render_textarea('issue_description', 'issue_description', ''); ?>
                        
                        <div class="form-group">
                            <label for="status"><?php echo _l('claim_status'); ?></label>
                            <select name="status" id="status" class="selectpicker" data-width="100%">
                                <option value="pending" selected><?php echo _l('pending'); ?></option>
                                <option value="in_progress"><?php echo _l('in_progress'); ?></option>
                                <option value="resolved"><?php echo _l('resolved'); ?></option>
                                <option value="rejected"><?php echo _l('reject'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
