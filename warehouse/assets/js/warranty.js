// modules/warehouse/assets/js/warranty.js

function new_warranty_claim(detail_id, commodity_id, customer_id) {
    "use strict";
    
    // Construct the URL to fetch the modal
    var url = admin_url + 'warehouse/get_claim_modal/' + detail_id + '/' + commodity_id + '/' + customer_id;

    // Use Perfex's requestGet to load the view
    requestGet(url).done(function(response) {
        // Inject response into the global modal wrapper
        $('#_medium_modal_wrapper').html(response);
        
        // Initialize the modal
        $('#warranty_claim_modal').modal({
            show: true,
            backdrop: 'static'
        });

        // Re-initialize Perfex form plugins (Datepickers, Selects, Validation)
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
