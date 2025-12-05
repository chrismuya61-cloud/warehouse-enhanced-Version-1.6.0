function add_licence() {
    new_licence();
}
function new_licence(id = '') {
    "use strict";
    var url = admin_url + 'warehouse/get_licence_modal/' + id;

    requestGet(url).done(function(response) {
        $('#_medium_modal_wrapper').html(response);
        $('#licence_modal').modal({
            show: true,
            backdrop: 'static'
        });
        
        init_selectpicker();
        init_datepicker();
        
        // Manual creation form validation
        appValidateForm($('#licence-form'), {
            claim_date: 'required',
            serial_number: {
                required: {
                    depends: function(element) {
                        return $('select[name="customer_id"]').val() != '';
                    }
                }
            }
        });

        // INTELLIGENT PROMPT LOGIC
        $('select[name="serial_number"]').on('change', function(){
            var selected = $(this).find('option:selected');
            var invStatus = selected.data('inv-status');
            var invNum = selected.data('inv-num');

            if(invStatus !== undefined) {
                // Perfex Status ID 2 usually means PAID
                if(invStatus == 2) { 
                    $('select[name="licence_type"]').selectpicker('val', 'permanent');
                    alert_float('success', 'Invoice #'+invNum+' is PAID. License set to PERMANENT.');
                } else {
                    $('select[name="licence_type"]').selectpicker('val', 'temporary');
                    alert_float('warning', 'Invoice #'+invNum+' is PENDING. License set to TEMPORARY.');
                }
            } else {
                 $('select[name="licence_type"]').selectpicker('val', 'temporary');
            }
        }).trigger('change');
        
        // Auto-fetch serials on customer change
        $('select[name="customer_id"]').on('change', function(){
            var id = $(this).val();
            $.post(admin_url + 'warehouse/get_available_serials_for_licencing', {clientid: id}).done(function(res){
                 res = JSON.parse(res);
                 var html = '<option value=""></option>';
                 $.each(res, function(i, item){
                     var status_txt = (item.invoice_status == 2) ? 'Paid' : 'Unpaid/Pending';
                     html += '<option value="'+item.serial_number+'" data-inv-status="'+item.invoice_status+'" data-inv-num="'+item.invoice_number+'" data-commodity-id="'+item.commodity_id+'">'+item.serial_number+' ('+item.description+') - Inv: '+item.invoice_number+' ['+status_txt+']</option>';
                 });
                 $('select[name="serial_number"]').html(html).selectpicker('refresh');
            });
        });
    });
}
