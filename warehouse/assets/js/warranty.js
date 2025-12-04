function new_warranty_claim(detail_id, commodity_id, customer_id) {
    "use strict";
    var url = admin_url + 'warehouse/get_claim_modal/' + detail_id + '/' + commodity_id + '/' + customer_id;
    requestGet(url).done(function(response) {
        $('#_medium_modal_wrapper').html(response);
        $('#warranty_claim_modal').modal({
            show: true,
            backdrop: 'static'
        });
        init_selectpicker();
        init_datepicker();
        appValidateForm($('#warranty-claim-form'), {
            claim_date: 'required',
            issue_description: 'required'
        });
    }).fail(function(data) {
        alert_float('danger', 'Error loading modal');
    });
}
