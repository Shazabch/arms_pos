<?php
/*

*/

$config['dat_format'] = '%d/%m/%Y';

if(!isset($config['link_code_name']))	$config['link_code_name'] = 'Old Code';
if(!isset($config['membership_cardname']))	$config['membership_cardname'] = 'ARMS';
if(!isset($config['scanned_ic_path']))	$config['scanned_ic_path'] = "icfiles.save/";

if(!defined('ARMS_SKU_CODE_PREFIX'))	define('ARMS_SKU_CODE_PREFIX', '28%06d');

if(!isset($config['sku_application_enable_variety']))	$config['sku_application_enable_variety'] = 1;
if(!isset($config['membership_length']))	$config['membership_length'] = 20;
if(!isset($config['global_cost_decimal_points'])) $config['global_cost_decimal_points'] = 4;
if(!isset($config['global_qty_decimal_points'])) $config['global_qty_decimal_points'] = 3;
	
$config['global_gst_start_date'] = "2015-04-01";

if(!isset($config['arms_currency']))	$config['arms_currency'] = array('code'=>'MYR', 'symbol'=>'RM', 'name'=>'Ringgit Malaysia', 'country'=>'Malaysia','rounding'=>0.05);
if(!isset($config['cash_domination_notes']))	$config['cash_domination_notes'] = array(
 '1 Cts'=>array('value'=>0.01,'label'=>'1 Cts','active'=>1),
 '5 Cts'=>array('value'=>0.05,'label'=>'5 Cts','active'=>1),
 '10 Cts'=>array('value'=>0.10,'label'=>'10 Cts','active'=>1),
 '20 Cts'=>array('value'=>0.20,'label'=>'20 Cts','active'=>1),
 '50 Cts'=>array('value'=>0.50,'label'=>'50 Cts','active'=>1),
 'RM 1'=>array('value'=>1,'label'=>'RM 1','active'=>1),
 'RM 2'=>array('value'=>2,'label'=>'RM 2','active'=>1),
 'RM 5'=>array('value'=>5,'label'=>'RM 5','active'=>1),
 'RM 10'=>array('value'=>10,'label'=>'RM 10','active'=>1),
 'RM 20'=>array('value'=>20,'label'=>'RM 20','active'=>1),
 'RM 50'=>array('value'=>50,'label'=>'RM 50','active'=>1),
 'RM 100'=>array('value'=>100,'label'=>'RM 100','active'=>1)
 );

?>