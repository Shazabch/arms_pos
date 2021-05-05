<?php
/*
2016-07-15 5:35 PM
- Arrange GAF file Record Type
2016-10-21 11:51 AM Kee Kee
- Pass branch id to create_gaf_file function
2016-11-07 13:27 PM Kee Kee
- Add Export Account Receiver and Credit Note into a document
2017-01-04 13:53 PM Kee Kee
- Added Branch Code into all export account data function

2017-02-06 10:33 AM Qiu Ying
- Bug fixed on all temporary table name must start with tmp_

2017-03-09 17:33 Qiu Ying
- Enhanced to check and not allow user to run mulitple task on the same time

2017-03-16 17:16 Qiu Ying
- Enhanced to block exporting sales if got pos sales not yet finalise

3/9/2018 3:44 PM Andy
- Enhanced to assign branchCode to $ExportAcc when export account payable.

10/30/2018 1:39 PM Andy
- Fxied get_payment() should pass the 4th argument.
*/
define("TERMINAL",1);
require("include/common.php");
require("acc_export.include.php");

register_shutdown_function( "fatal_handler" );

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

load_account_file($path,$accountings);

if(php_sapi_name() === 'cli'){
	$id=$_SERVER['argv'][1];
	$doc_root=dirname(__FILE__);
}
else{
	$id=$_REQUEST['id'];
	$doc_root=$_SERVER['DOCUMENT_ROOT'];
}

if(intval($id)>0){
	$form = load_export_schedule($id);
	$form['debug']=$_REQUEST['debug'];
	$form['schedule_id']=$id;

	if($_REQUEST['reset']){
		if($form["data_type"] == "cs" || $form["data_type"] == "cn" || $form["data_type"] == "cscn" || $form["data_type"] == "gaf"){
			$is_finalised = check_finalised_sales($form["branch_id"], $form["date_from"], $form["date_to"]);
			if(!$is_finalised){
				die("Please make sure you have finalise all the sales within the selected date before exporting.");
			}
		}
		
		$sql=$con->sql_query("select count(*) as num from ExportAccSchedule
						where active = 1 and started = 1 and completed = 0");
		$count = $con->sql_fetchrow($sql);
		$con->sql_freeresult($sql);
		if($count["num"] > 0){
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
}
else{
	die("Invalid ID.");
}

$folder=$doc_root."/export_account_data/";
if(!is_dir($folder)){
	mkdir($folder,0777,true);
	chmod($folder,0777);
}

$folder=$folder.$id."/";

if(!is_dir($folder)){
	mkdir($folder,0777,true);
	chmod($folder,0777);
}

$debug_file=$folder."debug_".$form['session_time'].".html";
$error_file=$folder."error.html";
$form['folder']=$folder;

unlink($error_file);
if(file_exists($folder."data_".$old_session_time)) unlink($folder."data_".$old_session_time);
if(file_exists($folder."data2_".$old_session_time)) unlink($folder."data2_".$old_session_time);
if(file_exists($folder."debug_".$old_session_time.".html")) unlink($folder."debug_".$old_session_time.".html");

if(file_exists($folder."data_".$form['session_time'])) unlink($folder."data_".$form['session_time']);
if(file_exists($folder."data2_".$form['session_time'])) unlink($folder."data2_".$form['session_time']);
if(file_exists($debug_file)) unlink($debug_file);

$moduleName = $form['export_type'];
$ClassName=$accountings[$moduleName]['module_name'];

$ClassFile = $path.$ClassName.".php";
if(!file_exists($ClassFile)) {
	file_put_contents($debug_file,"<pre>".$ClassName." Not found"."</pre>",FILE_APPEND);
	die();
}

try{
	include_once($ClassFile);
} catch (Exception $e) {
	file_put_contents($debug_file,"<pre>".$e->getMessage()."</pre>",FILE_APPEND);
	die();
}

$form['class']=$ClassName;

$upd=array();
$upd["started"]=1;
$upd["completed"]=0;
$upd["status"]="Start";
$upd["error_text"]="";
$upd["start_time"]=date("Y-m-d H:i:s");
$upd["end_time"]="0000-00-00 00:00:00";
$upd["session_time"]=$form['session_time'];
$upd["file_size"]=0;

update_export_schedule($id,$upd);

$ExportAcc = new $ClassName('pos');
$ExportAcc->accSettings = "";
$ExportAcc->dateFrom = date("Y-m-d 00:00:00",strtotime($form['date_from']));
$ExportAcc->dateTo = date("Y-m-d 23:59:59",strtotime($form['date_to']));
$ExportAcc->accSettings = $accountings[$moduleName]['settings'];
if(isset($form['debug'])){
	$ExportAcc->debug=$form['debug'];
	$ExportAcc->debug_file=$debug_file;
}
$ExportAcc->folder = $folder;
$ExportAcc->session_time = $form['session_time'];
$ExportAcc->batchno = $batchno;

switch($form['data_type']){
	case "gaf":
		$ret = GAF($ExportAcc,$form);
		break;
	case "cs":
		$ret = CS($ExportAcc,$form);
		break;
	case 'ap':
		$ret = AP($ExportAcc,$form);
		break;
	case 'ar':
		$ret = AR($ExportAcc,$form);
		break;
	case 'cn':
		$ret = CN($ExportAcc,$form);
		break;
	case 'dn':
		$ret = DN($ExportAcc,$form);
		break;
	case "cscn":
		$ret = CSCN($ExportAcc,$form);
		break;
	case "arcn":
		$ret = ARCN($ExportAcc,$form);
		break;
}

$upd=array();
$upd["status"]="Complete";
$upd["completed"]=1;
if(file_exists($ExportAcc->tmpFile)) $upd["file_size"]=filesize($ExportAcc->tmpFile);
$upd["end_time"]=date("Y-m-d H:i:s");

update_export_schedule($id,$upd);

file_put_contents($debug_file,"<pre>Complete</pre>",FILE_APPEND);
die();

function GAF($ExportAcc,$form){
	global $con;

	$ret = AP($ExportAcc,$form);
	$ret = CS($ExportAcc,$form);	
	$ret = AR($ExportAcc,$form);
	
	$ExportAcc->tmpGAFFile = $ExportAcc->folder."gaf_".$form['session_time'];

	$ExportAcc->create_gaf_file("1.0",$form['branch_id']);
}

function CS($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_Sales_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	$ExportAcc->tmpPaymentFile = $ExportAcc->folder."data2_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];
	
	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Cash Sales data");
	$ExportAcc->create_cash_sales($con);
	$ExportAcc->update_cash_sales($con,$con,$con,$where);
	
	update_export_schedule($form['schedule_id'],"status","Generating Cash Sales data");
	$total = $ExportAcc->get_cash_sales($con,$form['groupby'],$form['date_to'], $form['branch_code']);
	$totalPT = $ExportAcc->get_payment($con,$form['groupby'],$form['date_to'], $form['branch_code']);

	if($form['class']=='SageUBS') $ExportAcc->export_account_data();
	//print $ExportAcc->tmpTable."\n";
	$ExportAcc->clear_db($con);
}

function AP($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_Purchase_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Account Payable data");
	$ExportAcc->create_account_payable($con);
	$ExportAcc->update_account_payable($con,$con,$con,$con,$where,$form['branch_code']);

	update_export_schedule($form['schedule_id'],"status","Generating Account Payable data");
	$ExportAcc->branchCode = $form['branch_code'];
	$total = $ExportAcc->get_account_payable($con,$form['groupby'],$form['date_to']);

	if($form['class']=='SageUBS') $ExportAcc->export_account_data();
	
	$ExportAcc->clear_db($con);
}

function AR($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_Receiver_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Account Receivable data");
	$ExportAcc->create_account_receiver($con);
	$ExportAcc->update_account_receiver($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating Account Receivable data");
	$total = $ExportAcc->get_account_receiver($con,$form['groupby'],$form['date_to'],$form['branch_code']);

	if($form['class']=='SageUBS') $ExportAcc->export_account_data();

	$ExportAcc->clear_db($con);
}

function CN($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_CreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Credit Note data");
	$ExportAcc->create_credit_note($con);
	$ExportAcc->update_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating Credit Note data");
	$total = $ExportAcc->get_account_credit_note($con,$form['groupby'],$form['date_to'],$form['branch_code']);
		
	if($form['class']=='SageUBS') $ExportAcc->export_account_data();
	
	$ExportAcc->clear_db($con);
}

function DN($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_DebitNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where=array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Debit Note data");
	$ExportAcc->create_debit_note($con);
	$ExportAcc->update_debit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating Debit Note data");
	$total = $ExportAcc->get_account_debit_note($con,$form['groupby'],$form['date_to'],$form['branch_code']);
	
	if($form['class']=='SageUBS') $ExportAcc->export_account_data();

	$ExportAcc->clear_db($con);
}

function CSCN($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_CashSalesCreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Cash Sales & Credit Note data");
	$ExportAcc->create_cash_sales_credit_note($con);
	$ExportAcc->update_cash_sales_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating Cash Sales & Credit Note data");
	$total = $ExportAcc->get_account_cash_sales_n_credit_note($con,$form['groupby'],$form['date_to']);
	
	if($form['class']=='SageUBS') $ExportAcc->export_account_data();
	//print $ExportAcc->tmpTable."\n";
	$ExportAcc->clear_db($con);
}

function ARCN($ExportAcc,$form){
	global $con;

	$ExportAcc->tmpTable = "tmp_ExportAcc_CreditSalesCreditNote_".$form['session_time'];
	$ExportAcc->tmpFile = $ExportAcc->folder."data_".$form['session_time'];
	if($form['class']=='SageUBS') $ExportAcc->tmpExportFileName = $ExportAcc->folder."dbf_".$form['session_time'];

	$where = array();
	$where[] = "p.branch_id=".mi($form['branch_id']);

	update_export_schedule($form['schedule_id'],"status","Preparing Sales & Credit Notes data");
	$ExportAcc->create_sales_credit_note($con);
	$ExportAcc->update_sales_credit_note($con,$con,$con,$where);

	update_export_schedule($form['schedule_id'],"status","Generating Sales & Credit Notes data");
	$total = $ExportAcc->get_account_sales_n_credit_note($con,$form['groupby'],$form['date_to']);
	
	if($form['class']=='SageUBS') $ExportAcc->export_account_data();
	
	$ExportAcc->clear_db($con);
}
?>
