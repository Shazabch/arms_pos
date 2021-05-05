<?php
/*
4/6/2017 14:26 Qiu Ying
- Enhanced to on order export format list according to alphabetical order

4/21/2017 10:41 AM Qiu Ying
- Bug fixed on error message shown in file when download empty file
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CUSTOM_ACC_EXPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CUSTOM_ACC_EXPORT', BRANCH_CODE), "/index.php");
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',1);
ini_set('memory_limit', '1024M');
set_time_limit(0);
include('custom.custom_acc_export.include.php');

class CUSTOM_ACC_EXPORT extends Module{
	var $folder = "export_account_data/";
	var $is_consignment=0;
	
	function __construct($title)
	{
		$this->folder=$_SERVER['DOCUMENT_ROOT']."/".$this->folder;
		parent::__construct($title);
	}

	function _default()
	{
		global $con, $smarty, $sessioninfo, $config;
		$this->is_consignment=(($config['consignment_modules'])?1:0);
		$form['date_from']=date("Y-m-01", strtotime('last month'));
		$form['date_to']=date("Y-m-t",strtotime($form['date_from']));
		
		if($config["single_server_mode"]){
			if(BRANCH_CODE == 'HQ' && !$this->is_consignment){
				$this->load_branches();
				$con->sql_freeresult();
			}
			else{
				$form['branch_id'][]=$sessioninfo['branch_id'];
				$filter = " and (branch_id = 1 or branch_id = " . mi($sessioninfo['branch_id']) . ")";
			}
		}else{
			$form['branch_id'][]=$sessioninfo['branch_id'];
			$filter = " and branch_id = " . mi($sessioninfo['branch_id']);
		}
		
		$con->sql_query("select  CONCAT(branch_id,'-',id) as id, title from custom_acc_export_format 
						where active = 1 $filter
						order by title");
		$smarty->assign("format_list", $con->sql_fetchrowset());
		$smarty->assign("form", $form);
		$smarty->assign("is_consignment",$this->is_consignment);
		$con->sql_freeresult();
		$this->display('custom.acc_export.tpl');
	}
	
	function create_schedule(){
		global $smarty,$con,$sessioninfo;

		$result=array();
		$form=$_POST;

		$dateFrom = strtotime($form['date_from']);
		$dateTo = strtotime($form['date_to']);
		if($dateTo < $dateFrom)
		{
			$result['status']='Error';
			$result['msg']="Date From cannot greater than Date To.";
		}
		else{
			$is_finalised = true;
			list($format_bid, $format_id) = explode("-",$form['export_format']);
			$sql=$con->sql_query("select data_type from custom_acc_export_format
					where id = " . mi($format_id) . " and branch_id = " . mi($format_bid));
			$ret = $con->sql_fetchrow($sql);
			$con->sql_freeresult($sql);
			$data_type = $ret["data_type"];

			if($data_type == "cash_sales" || $data_type == "cn_sales" || $data_type == "cash_sales_cn" || $data_type == "payment"){
				$is_finalised = check_finalised_sales($form["branch_id"], $form["date_from"], $form["date_to"]);
				if(!$is_finalised){
					$result['status']='Error';
					$result['msg'] = "Please make sure you have finalise all the sales within the selected date before exporting.";
				}
			}
			
			if($is_finalised){
				$exist = search_export_schedule($form);

				if($exist){
					$result['status']='EXIST';
					$result['data'] = $exist;
				}
				else{
					$is_running = check_pending_task();
					
					if(!$is_running){
						$ret = create_export_schedule($form);
						$result['status']='OK';
						$result['id']=$ret['id'];
						$result['branch_id']=$ret['branch_id'];
						log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', 0, "Created Custom Accounting Export Schedule (Branch ID: " . $result['branch_id'] . ", Schedule ID: " . $result['id'] .")");
					}else{
						$result['status']='Error';
						$result['msg']="There is a task still generating. Please wait the process to complete before generating a new one.";
					}
				}
			}
		}
		echo json_encode($result);
	}
	
	function load_schedule()
	{
		global $smarty,$con,$sessioninfo,$config;
		
		$this->check_file_size();
		
		if(BRANCH_CODE != 'HQ' || !$config["single_server_mode"])	$where[] = "cae.branch_id = ".$sessioninfo['branch_id'];
		
		if(!isset($_REQUEST['schedule_type']) || $_REQUEST['schedule_type']=='active'){
			$where[]="cae.active=1";
			$where[]="cae.archived=0";
		}elseif($_REQUEST['schedule_type']=='archive'){
			$where[]="cae.active=1";
			$where[]="cae.archived=1";
		}

		if($_REQUEST['batchno'] && $_REQUEST['batchno']!="") $where[]="batchno like ".ms('%'.$_REQUEST['batchno'].'%');

		$where=" where ".implode(" and ",$where);

		$q=$con->sql_query("select cae.*,b.code as branch_code,u.u, caef.title 
						from custom_acc_export cae
						left join user u on u.id=cae.user_id
						left join branch b on b.id=cae.branch_id
						left join custom_acc_export_format caef on caef.id = cae.format_id and caef.branch_id = cae.format_branch_id
						$where order by cae.generate_on desc");
		$list=$con->sql_fetchrowset($q);
		
		$smarty->assign("list",$list);
		$smarty->display('custom.acc_export.list.tpl');
	}
	
	function remove_schedule()
	{
		global $sessioninfo;
		$id=$_REQUEST['id'];
		$branch_id=$_REQUEST['branch_id'];
		
		update_export_schedule($id,"active",0, $branch_id);
		log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', 0, "Removed Custom Accounting Export Schedule (Branch ID: " . $branch_id . ", Schedule ID: " . $id .")");
	}
	
	function download(){
		global $sessioninfo;
		$id = $_REQUEST['id'];
		$branch_id = $_REQUEST['branch_id'];
		$form = load_export_schedule($id, $branch_id);
		
		$batch_no = $form["batchno"];
		$data_type = $form["data_type"];
		$file_extension = $form["file_format"];
		$session_time = $form["session_time"];
		$branch_code = get_branch_code($form['branch_id']);
		
		$path="./export_account_data/custom_b". $branch_id ."_".$id."/";
		$ClassName = "data_" . $session_time;
		$ClassFile = $path.$ClassName;
		
		if(!file_exists($ClassFile)) {
			print "<script type='text/javascript'>
			alert('File is empty, cannot be downloaded.');
			</script>";
			exit;
		}
		
		if($form['started'] && $form['completed']){	
			ob_start();
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			switch(strtolower($file_info['extension'])){
				case 'csv':
					header('Content-type: text/csv');
					break;
				case 'txt':
					header('Content-type: text/plain');
					break;
			}

			header('Content-Disposition: attachment; filename="' . $data_type."_".$batch_no."_".$branch_code.".".$file_extension . '"');
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($ClassFile));
			header('Accept-Ranges: bytes');
			ob_clean();
			flush();
			echo file_get_contents($ClassFile);
			log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $id, "Downloaded Custom Accounting Export Schedule (Branch ID: " . $branch_id . ", Schedule ID: " . $id .")");
		}
		else{
			die("Error");
		}
	}
	
	function archive(){
		global $sessioninfo;
		$id=$_REQUEST['id'];
		$branch_id=$_REQUEST['branch_id'];

		update_export_schedule($id,"archived",1,$branch_id);
		log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $id, "Archived Custom Accounting Export Schedule (Branch ID: " . $branch_id . ", Schedule ID: " . $id .")");
	}
	
	function reactivate(){
		global $sessioninfo;
		$id=$_REQUEST['id'];
		$branch_id=$_REQUEST['branch_id'];

		update_export_schedule($id,"archived",0,$branch_id);
		log_br($sessioninfo['id'], 'CUSTOM ACC EXPORT', $id, "Activated Custom Accounting Export Schedule (Branch ID: " . $branch_id . ", Schedule ID: " . $id .")");
	}
	
	function show_debug(){
		$id=$_REQUEST['id'];
		$branch_id=$_REQUEST['branch_id'];
		
		$form = load_export_schedule($id,$branch_id);
		
		$filename=$this->folder."custom_b". $branch_id . "_" .$form['id']."/debug_".$form['session_time'].".html";
		if(file_exists($filename)){
			echo file_get_contents($filename);
		}
		else{
			echo "File Not Found.";
		}
	}
	
	private function check_file_size(){
		global $smarty,$con,$sessioninfo;

		$q=$con->sql_query("select * from custom_acc_export where active=1 and started=1 and completed=0");

		while($r=$con->sql_fetchrow($q)){
			$filename=$this->folder."custom_b". $r['branch_id'] ."_".$r['id']."/data_".$r['session_time'];

			$error_filename=$this->folder."custom_b" . $r['branch_id'] . "_".$r['id']."/error.html";

			if(file_exists($error_filename)){
				$upd=array();
				$upd["started"]=0;
				$upd["completed"]=0;
				$upd["status"]="Error";
				$upd["start_time"]="0000-00-00 00:00:00";
				$upd["end_time"]="0000-00-00 00:00:00";
				$upd["file_size"]=0;
				$upd["error_text"]=file_get_contents($error_filename);
				update_export_schedule($r['id'],$upd, null, $r['branch_id']);
			}
			elseif(file_exists($filename)) update_export_schedule($r['id'],"file_size",filesize($filename),$r['branch_id']);
		}
	}
	
	private function load_branches(){
		global $smarty, $con, $sessioninfo;
		
		$con->sql_query("select * from branch where active=1 order by sequence");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}

		$con->sql_freeresult();

		$smarty->assign("branch", $branches);
	}
	
	function check_login(){
		die("OK");
	}
}

$smarty->register_modifier("formatBytes", "smarty_modifier_formatBytes");
function smarty_modifier_formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return number_format(round($bytes, $precision)) . ' ' . $units[$pow];
}
$CUSTOM_ACC_EXPORT = new CUSTOM_ACC_EXPORT('Custom Accounting Export');
