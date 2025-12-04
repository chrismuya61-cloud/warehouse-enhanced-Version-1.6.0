<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'serial_number',
    db_prefix() . 'clients.company',
    db_prefix() . 'items.description',
    'licence_type',
    'validity_end_date',
    'status',
    db_prefix() . 'wh_licences.id as id'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wh_licences';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'wh_licences.customer_id',
    'LEFT JOIN ' . db_prefix() . 'items ON ' . db_prefix() . 'items.id = ' . db_prefix() . 'wh_licences.commodity_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['serial_number'];
    $row[] = $aRow['company'];
    $row[] = $aRow['description'];
    $row[] = _l($aRow['licence_type']);
    
    $date = $aRow['validity_end_date'];
    $row[] = $date ? _d($date) : '-';

    $status = $aRow['status'];
    $label = 'default';
    if($status == 'active') $label = 'success';
    if($status == 'expired') $label = 'danger';
    if($status == 'draft') $label = 'warning';
    
    $row[] = '<span class="label label-'.$label.'">'._l($status).'</span>';

    $options = icon_btn('#', 'fa fa-pencil-square-o', 'btn-default', ['onclick' => 'edit_licence(' . $aRow['id'] . '); return false;']);
    $row[] = $options;

    $output['aaData'][] = $row;
}
