<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $title; ?></title>
    <link href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <style>
        body { background: #f0f2f5; padding: 20px; font-family: sans-serif; }
        .scanner-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        #reader { width: 100%; margin-bottom: 20px; }
        .last-scan { margin-top: 20px; padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 5px; display:none; }
        .error-scan { margin-top: 20px; padding: 10px; background: #ffebee; color: #c62828; border-radius: 5px; display:none; }
        input[type="text"] { font-size: 18px; text-align: center; }
    </style>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
</head>
<body>
<div class="container">
    <div class="scanner-container">
        <h4><i class="glyphicon glyphicon-barcode"></i> Stock Take Scanner</h4>
        <p class="text-muted">Point camera at barcode or enter manually</p>
        <div id="reader"></div>
        <div class="form-group">
            <input type="text" id="manual_barcode" class="form-control" placeholder="Scan or type...">
        </div>
        <div id="feedback" class="last-scan"></div>
        <div id="error" class="error-scan"></div>
    </div>
</div>
<script>
    var token = '<?php echo $token; ?>';
    var isProcessing = false;

    function onScanSuccess(decodedText, decodedResult) {
        if(isProcessing) return;
        processBarcode(decodedText);
    }

    var html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess);

    $('#manual_barcode').keypress(function(event){
        if((event.keyCode ? event.keyCode : event.which) == '13'){
            processBarcode($(this).val());
            $(this).val('');
        }
    });

    function processBarcode(code){
        isProcessing = true;
        $('#feedback').hide();
        $('#error').hide();

        $.post('<?php echo site_url("warehouse/warehouse_remote_scanner/scan_item"); ?>', {
            token: token,
            barcode: code
        }, function(response){
            var data = JSON.parse(response);
            if(data.status){
                $('#feedback').text(data.message + ' (Qty: ' + data.count + ')').show().fadeOut(3000);
            } else {
                $('#error').text(data.message).show();
            }
            setTimeout(function(){ isProcessing = false; }, 1500);
        });
    }
</script>
</body>
</html>