<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-invoices">
	<div class="panel-body">
		<h4 class="no-margin section-text"><?php echo _l('wh_shipments'); ?></h4>
	</div>
</div>
<div class="panel_s">
	<div class="panel-body">
	<?php
// Simple logic to count statuses for the UI
$total = count($shipments);
$delivered = 0;
$processing = 0;
foreach($shipments as $s) {
    if($s['shipment_status'] == 'delivered') $delivered++;
    else $processing++;
}
?>
<div class="row mbot20">
    <div class="col-md-4 col-xs-12">
        <div class="shipment-stat-box">
            <span class="stat-label text-muted"><?php echo _l('total_shipments'); ?></span>
            <h4 class="bold no-margin"><?php echo $total; ?></h4>
        </div>
    </div>
    <div class="col-md-4 col-xs-6">
        <div class="shipment-stat-box border-right border-left">
            <span class="stat-label text-info"><i class="fa fa-truck"></i> <?php echo _l('processing'); ?></span>
            <h4 class="bold no-margin"><?php echo $processing; ?></h4>
        </div>
    </div>
    <div class="col-md-4 col-xs-6">
        <div class="shipment-stat-box">
            <span class="stat-label text-success"><i class="fa fa-check-circle"></i> <?php echo _l('delivered'); ?></span>
            <h4 class="bold no-margin"><?php echo $delivered; ?></h4>
        </div>
    </div>
</div>
<hr />
		<!-- <?php get_template_part('invoices_stats'); ?> -->
		<hr />
		<table class="table dt-table table-invoices" data-order-col="1" data-order-type="desc">
			<thead>
				<tr>
					<th class="th-invoice-number"><?php echo _l('wh_shipment_number'); ?></th>
					<th class="th-invoice-date"><?php echo _l('datecreated'); ?></th>
					<th class="th-invoice-duedate"><?php echo _l('status_label'); ?></th>
					
				</tr>
			</thead>
			<tbody>
				<?php foreach($shipments as $shipment){ ?>
					<tr>
						<?php if($shipment['shipment_hash'] != null && new_strlen($shipment['shipment_hash'] ?? '') > 0){ ?>
							<td data-order="<?php echo $shipment['shipment_number']; ?>"><a href="<?php echo site_url('warehouse/warehouse_client/shipment_detail_hash/' . $shipment['shipment_hash']); ?>" class="invoice-number"><?php echo $shipment['shipment_number']; ?></a></td>
						<?php }else{ ?>
							<td data-order="<?php echo $shipment['shipment_number']; ?>"><a href="<?php echo site_url('warehouse/warehouse_client/shipment_detail/' . $shipment['goods_delivery_id']); ?>" class="invoice-number"><?php echo $shipment['shipment_number']; ?></a></td>
						<?php } ?>

						<td data-order="<?php echo $shipment['datecreated']; ?>"><?php echo _dt($shipment['datecreated']); ?></td>
						<td><?php echo format_shipment_status($shipment['shipment_status'], 'inline-block', true); ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
