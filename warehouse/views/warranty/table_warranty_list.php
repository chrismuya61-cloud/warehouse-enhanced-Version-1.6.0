<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'items.description as item_name', // Adjust based on your item table structure
    'serial_number',
    db_prefix() . 'clients.company',
    'guarantee_period',
    'guarantee_period as time_remaining', // Placeholder for calculation
    'guarantee_period as status', // Placeholder
    db_prefix() . 'goods_delivery_detail.id as detail_id'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'goods_delivery_detail';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.id = ' . db_prefix() . 'goods_delivery_detail.commodity_code',
    'LEFT JOIN ' . db_prefix() . 'goods_delivery ON ' . db_prefix() . 'goods_delivery.id = ' . db_prefix() . 'goods_delivery_detail.goods_delivery_id',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'goods_delivery.customer_code',
];
$where        = ['AND guarantee_period IS NOT NULL AND guarantee_period != ""'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'goods_delivery_detail.commodity_code', db_prefix() . 'goods_delivery.customer_code']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Item Name
    $row[] = $aRow['item_name'];

    // Serial
    $row[] = $aRow['serial_number'];

    // Customer
    $row[] = $aRow[db_prefix() . 'clients.company'];

    // Expiry Date
    $row[] = _d($aRow['guarantee_period']);

    // Time Remaining Calculation
    $now = time(); 
    $your_date = strtotime($aRow['guarantee_period']);
    $datediff = $your_date - $now;
    $days_remaining = round($datediff / (60 * 60 * 24));

    if($days_remaining < 0) {
        $row[] = '<span class="text-danger">Expired (' . abs($days_remaining) . ' days ago)</span>';
        $status = '<span class="label label-danger">' . _l('warranty_expired') . '</span>';
    } else {
        $row[] = '<span class="text-success">' . $days_remaining . ' days left</span>';
        $status = '<span class="label label-success">' . _l('warranty_active') . '</span>';
    }

    // Status
    $row[] = $status;

    // Options (File Claim Button)
    $options = icon_btn('#', 'fa fa-exclamation-triangle', 'btn-warning', [
        'onclick' => 'new_warranty_claim(' . $aRow['detail_id'] . ', '.$aRow[db_prefix() . 'goods_delivery_detail.commodity_code'].', '.$aRow[db_prefix() . 'goods_delivery.customer_code'].'); return false;',
        'data-toggle' => 'tooltip',
        'title' => _l('file_claim')
    ]);

    $row[] = $options;

    $output['aaData'][] = $row;
}
