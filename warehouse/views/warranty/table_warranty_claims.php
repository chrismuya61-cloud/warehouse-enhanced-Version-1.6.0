<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'claim_date',
    db_prefix() . 'items.description as item_name', 
    db_prefix() . 'goods_delivery_detail.serial_number as serial_number',
    db_prefix() . 'clients.company',
    'issue_description',
    'status',
    'invoice_id', 
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wh_warranty_claims';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.id = ' . db_prefix() . 'wh_warranty_claims.commodity_id',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'wh_warranty_claims.customer_id',
    'LEFT JOIN ' . db_prefix() . 'goods_delivery_detail ON ' . db_prefix() . 'goods_delivery_detail.id = ' . db_prefix() . 'wh_warranty_claims.detail_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['expense_id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];
    $row[] = _d($aRow['claim_date']);
    $row[] = $aRow['item_name'];
    $row[] = '<span class="label label-tag">' . $aRow['serial_number'] . '</span>';
    $row[] = $aRow[db_prefix() . 'clients.company'];
    $row[] = substr($aRow['issue_description'], 0, 50) . '...';

    $statusClass = 'default';
    if ($aRow['status'] == 'resolved') $statusClass = 'success';
    elseif ($aRow['status'] == 'in_progress') $statusClass = 'info';
    elseif ($aRow['status'] == 'rejected') $statusClass = 'danger';
    $row[] = '<span class="label label-' . $statusClass . '">' . _l($aRow['status']) . '</span>';

    $options = '';
    $options .= icon_btn('#', 'fa fa-pencil-square-o', 'btn-default', ['onclick' => 'edit_warranty_claim(' . $aRow['id'] . '); return false;']);

    if ($aRow['invoice_id'] == 0) {
        $options .= icon_btn(admin_url('warehouse/convert_claim_to_invoice/' . $aRow['id']), 'fa fa-file-text-o', 'btn-success', [
            'data-toggle' => 'tooltip', 
            'title' => _l('convert_to_invoice'),
            'onclick' => "return confirm('"._l('confirm_invoice_creation')."');"
        ]);
    } else {
        $options .= icon_btn(admin_url('invoices/invoice/' . $aRow['invoice_id']), 'fa fa-file-text', 'btn-info', ['data-toggle' => 'tooltip', 'title' => _l('view_invoice')]);
    }
    $options .= icon_btn(admin_url('warehouse/create_expense_from_claim/' . $aRow['id']), 'fa fa-money', 'btn-warning', ['data-toggle' => 'tooltip', 'title' => _l('log_expense')]);

    $row[] = $options;
    $output['aaData'][] = $row;
}
