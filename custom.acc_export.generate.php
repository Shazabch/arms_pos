<?php
/*
12/5/2018 5:55 PM Andy
- Fixed Account Setting cannot get branch settings.
*/
define("TERMINAL",1);
require("include/common.php");
require("custom.custom_acc_export.include.php");

register_shutdown_function("fatal_handler");

function fatal_handler() {
	global $folder,$debug_file,$error_file;
	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$error = error_get_last();

	if( $error !== NULL && $error["type"]==1) {
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];

		$content = "
		<table>
		<thead><th>Item</th><th>Description</th></thead>
		<tbody>
		<tr>
		  <th>Error</th>
		  <td><pre>$errstr</pre></td>
		</tr>
		<tr>
		  <th>Errno</th>
		  <td><pre>$errno</pre></td>
		</tr>
		<tr>
		  <th>File</th>
		  <td>$errfile</td>
		</tr>
		<tr>
		  <th>Line</th>
		  <td>$errline</td>
		</tr>
		<tr>
		  <th>Trace</th>
		  <td><pre>$error</pre></td>
		</tr>
		</tbody>
		</table>";
		
		file_put_contents($debug_file,$content,FILE_APPEND);
		file_put_contents($error_file,$content);
	}
}

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',0);
ini_set('memory_limit', '1024M');
set_time_limit(0);
ignore_user_abort(true);


if(php_sapi_name() === 'cli'){
	$id=$_SERVER['argv'][1];
	$branch_id=$_SERVER['argv'][2];
	$doc_root=dirname(__FILE__);
}
else{
	$id=$_REQUEST['id'];
	$branch_id=$_REQUEST['branch_id'];
	$doc_root=$_SERVER['DOCUMENT_ROOT'];
}

$accountings = load_account_file($branch_id);

if(intval($id)>0){
	$form = load_export_schedule($id, $branch_id);
	$form['debug']=$_REQUEST['debug'];
	$form['schedule_id']=$id;

	if($_REQUEST['reset']){
		if($form["data_type"] == "cash_sales" || $form["data_type"] == "cn_sales" || $form["data_type"] == "cash_sales_cn" || $form["data_type"] == "payment"){
			$is_finalised = check_finalised_sales($form["branch_id"], $form["date_from"], $form["date_to"]);
			if(!$is_finalised){
				die("Please make sure you have finalise all the sales within the selected date before exporting.");
			}
		}
		
		$is_running = check_pending_task();
		if($is_running){
			die("There is a task still generating. Please wait the process to complete before generating a new one.");
		}
		$old_session_time=$form['session_time'];
		$form['session_time']=time();
	}
	else{
		if(!$form['session_time']) $form['session_time']=time();

		if($form['started']) die("Task in Progress");
	}

	if(!$form['active']) die('Schedule removed.');

	$batchno = get_batchno($form);
	$form['branch_code'] = get_branch_code($form['branch_id']);
	
	if(!$config["single_server_mode"]){
		if($form['branch_code'] != BRANCH_CODE){
			die("Only can export own branch data");
		}
	}
}
else{
	die("Invalid ID.");
}

$folder=$doc_root."/export_account_data/";
if(!is_dir($folder)){
	mkdir($folder,0777,true);
	chmod($folder,0777);
}

$folder=$folder."custom_b". $branch_id . "_" . $id ."/";

if(!is_dir($folder)){
	mkdir($folder,0777,true);
	chmod($folder,0777);
}

$debug_file=$folder."debug_".$form['session_time'].".html";
$error_file=$folder."error.html";
$form['folder']=$folder;

unlink($error_file);
if(file_exists($folder."data_".$old_session_time)) unlink($folder."data_".$old_session_time);
if(file_exists($folder."debug_".$old_session_time.".html")) unlink($folder."debug_".$old_session_time.".html");

if(file_exists($folder."data_".$form['session_time'])) unlink($folder."data_".$form['session_time']);
if(file_exists($debug_file)) unlink($debug_file);

$moduleName = $form['data_type'];

if($moduleName == "cash_sales" || $moduleName == "payment"){
	$ClassName = "CustomCashSales";
}elseif($moduleName == "purchase"){
	$ClassName = "CustomPurchase";
}elseif($moduleName == "dn_purchase"){
	$ClassName = "CustomDebitNotePurchase";
}elseif($moduleName == "cn_sales"){
	$ClassName = "CustomCreditNoteSales";
}elseif($moduleName == "cash_sales_cn"){
	$ClassName = "CustomCashSalesAndCreditNote";
}elseif($moduleName == "sales_n_cn"){
	$ClassName = "CustomSalesAndCreditNote";
}elseif($moduleName == "credit_sales"){
	$ClassName = "CustomCreditSales";
}

$ClassFile = $ClassName.".php";

if(!file_exists($ClassFile)) {
	file_put_contents($debug_file,"<pre>".$ClassName." Not found"."</pre>",FILE_APPEND);
	die();
}

try{
	include_once($ClassFile);
}catch (Exception $e){
	file_put_contents($debug_file,"<pre>".$e->getMessage()."</pre>",FILE_APPEND);
	die();
}

$upd=array();
$upd["started"]=1;
$upd["completed"]=0;
$upd["status"]="Start";
$upd["error_text"]="";
$upd["start_time"]=date("Y-m-d H:i:s");
$upd["end_time"]="0000-00-00 00:00:00";
$upd["session_time"]=$form['session_time'];
$upd["file_size"]=0;
update_export_schedule($id,$upd, null,$branch_id);

$ExportAcc = new $ClassName('pos');
$ExportAcc->dateFrom = date("Y-m-d 00:00:00",strtotime($form['date_from']));
$ExportAcc->dateTo = date("Y-m-d 23:59:59",strtotime($form['date_to']));
$ExportAcc->accSettings = $accountings;

if(isset($form['debug'])){
	$ExportAcc->debug=$form['debug'];
	$ExportAcc->debug_file=$debug_file;
}
$ExportAcc->folder = $folder;
$ExportAcc->session_time = $form['session_time'];
$ExportAcc->batchno = $batchno;

switch($form['data_type']){
	case "cash_sales":
		$ret = CS($ExportAcc,$form);
		break;
	case 'purchase':
		$ret = AP($ExportAcc,$form);
		break;
	case 'credit_sales':
		$ret = AR($ExportAcc,$form);
		break;
	case 'cn_sales':
		$ret = CN($ExportAcc,$form);
		break;
	case 'dn_purchase':
		$ret = DN($ExportAcc,$form);
		break;
	case "cash_sales_cn":
		$ret = CSCN($ExportAcc,$form);
		break;
	case "payment":
		$ret = Payment($ExportAcc,$form);
		break;
	case "sales_n_cn":
		$ret = ARCN($ExportAcc,$form);
		break;
}

function CS($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_CashSales_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_cash_sales($con);
	$ExportAcc->update_cash_sales($con,$con,$con,$where);
	
	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	
	$total = $ExportAcc->get_cash_sales($con,$form);
	$ExportAcc->clear_db($con);
}

function Payment($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_Payment_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_cash_sales($con);
	$ExportAcc->update_cash_sales($con,$con,$con,$where);
	
	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	
	$total = $ExportAcc->get_payment($con,$form);
	$ExportAcc->clear_db($con);
}

function AP($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_Purchase_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];

	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_account_payable($con);
	$ExportAcc->update_account_payable($con,$con,$con,$con,$where,$form['branch_code']);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_payable($con,$form);
	$ExportAcc->clear_db($con);
}

function DN($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_PurchaseDebitNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_debit_note($con);
	$ExportAcc->update_debit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_debit_note($con,$form);
	
	$ExportAcc->clear_db($con);
}

function CN($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_SalesCreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	
	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_credit_note($con);
	$ExportAcc->update_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_credit_note($con,$form);
	
	$ExportAcc->clear_db($con);
}

function CSCN($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_CashSalesCreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	
	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_cash_sales_credit_note($con);
	$ExportAcc->update_cash_sales_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_cash_sales_n_credit_note($con,$form);
	
	$ExportAcc->clear_db($con);
}

function ARCN($ExportAcc,$form){
	global $con, $data_type_option;
	
	$ExportAcc->tmpTable = "tmp_CustomExportAcc_SalesAndCreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	
	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_sales_credit_note($con);
	$ExportAcc->update_sales_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_sales_n_credit_note($con,$form);

	$ExportAcc->clear_db($con);
}

function AR($ExportAcc,$form){
	global $con, $data_type_option;

	$ExportAcc->tmpTable = "tmp_CustomExportAcc_CreditSales_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$ExportAcc->create_account_receiver($con);
	$ExportAcc->update_account_receiver($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating " . $data_type_option[$form["data_type"]] . " Data", $form['branch_id']);
	$total = $ExportAcc->get_account_receiver($con,$form);
	
	$ExportAcc->clear_db($con);
}

$upd=array();
$upd["status"]="Complete";
$upd["completed"]=1;
if(file_exists($ExportAcc->tmpFile)) $upd["file_size"]=filesize($ExportAcc->tmpFile);
$upd["end_time"]=date("Y-m-d H:i:s");

update_export_schedule($id,$upd,null,$branch_id);

file_put_contents($debug_file,"<pre>Complete</pre>",FILE_APPEND);
die();
?>
