<script>
    init_datepicker();
    init_selectpicker();
    
    // Trigger when Customer Changes to load serials
    $('select[name="customer_id"]').on('change', function(){
        var id = $(this).val();
        // Reset
        $('select[name="serial_number"]').html('').selectpicker('refresh');
        
        $.post(admin_url + 'warehouse/get_available_serials_for_licence', {clientid: id}).done(function(res){
             res = JSON.parse(res);
             var html = '<option value=""></option>';
             
             $.each(res, function(i, item){
                 // STORE INVOICE STATUS IN DATA ATTRIBUTE
                 var status_txt = (item.invoice_status == 2) ? 'Paid' : 'Unpaid/Pending';
                 html += '<option value="'+item.serial_number+'" data-inv-status="'+item.invoice_status+'" data-inv-num="'+item.invoice_number+'">'+item.serial_number+' ('+item.description+') - Inv: '+item.invoice_number+' ['+status_txt+']</option>';
             });
             
             $('select[name="serial_number"]').html(html).selectpicker('refresh');
        });
    });

    // INTELLIGENT PROMPT LOGIC
    $('select[name="serial_number"]').on('change', function(){
        var selected = $(this).find('option:selected');
        var invStatus = selected.data('inv-status');
        var invNum = selected.data('inv-num');

        if(invStatus !== undefined) {
            if(invStatus == 2) {
                // Invoice is PAID
                $('select[name="licence_type"]').selectpicker('val', 'permanent');
                alert_float('success', 'Invoice #'+invNum+' is PAID. License set to PERMANENT.');
            } else {
                // Invoice is UNPAID/PENDING
                $('select[name="licence_type"]').selectpicker('val', 'temporary');
                alert_float('warning', 'Invoice #'+invNum+' is PENDING. License set to TEMPORARY.');
            }
        }
    });
</script>
