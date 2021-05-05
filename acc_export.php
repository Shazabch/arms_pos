<?php
/*
03/07/2016 11:32 Kee Kee
- Added date validation checking, when create export to account schedule
- Change Credit Notes to Sales Credit Notes
- Change Debit Notes to Purchase Debit Notes

05/20/2016 16:00 Edwin
- Bug fixed on show others branch account when log on specific branch 

06/21/2016 10:42 Kee Kee
- Remove SageUBS and Inter Application (IA) from accounting software

09/21/2016 10:59 Kee Kee
- Change "Account Export" to "Account & GAF Export"

09/22/2016 2:11 PM Kee Kee
- Skip loop $data['other'] when $data['other'] not exists

11/01/2016 9:27 AM Kee Kee
- Unable to regenerate GAF file once the file has been generated

11/07/2016 9:17 AM Kee Kee
- Added "Credit/IBT Sales & Credit Notes" 

2017-01-04 13:55 PM Kee Kee
- Added Branch Code into all export account data function

2017-03-09 17:33 Qiu Ying
- Enhanced to check and not allow user to run mulitple task on the same time
- Enhanced to add "change back to active" for archive

2017-03-16 17:16 Qiu Ying
- Enhanced to block exporting sales if got pos sales not yet finalise
- Bug fixed on if duplicate task found, the archive task still able to generate. 

2017-04-21 10:41 AM Qiu Ying
- Bug fixed on error message shown in file when download empty file

11/9/2017 4:56 PM Andy
- Fixed Save Other Accounting Settings bugs.

1/25/2018 11:29 AM Andy
- Turn on display error.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ACCOUNT_EXPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ACCOUNT_EXPORT', BRANCH_CODE), "/index.php");
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors',1);
ini_set('memory_limit', '1024M');
set_time_limit(0);
include("acc_export.include.php");
class GSTExport extends Module{
	var $title="Account & GAF Export";
	var $path="./export_account/";
	var $accountings=array();
	var $groupby=array("Daily Summary","Monthly Summary","Receipt");
	var $err=array();
	var $is_consignment=0;
	var $skip=array("Biztrak","Mr Accounting ERP","SageUBS","Inter Application (IA)","Auto Count");
	var $doc_root="";
	var $extra_file_name="";
	var $global_type=array("gaf"=>"GAF",
					"cs"=>"Cash Sales",
					"cscn"=>"Cash Sales & Credit Notes",
					"arcn"=>"Sales & Credit Notes",
					"ar"=>"Account Receivable (Sales)",
					"cn"=>"Sales Credit Notes",
					"ap"=>"Purchase",
					"dn"=>"Purchase Debit Notes");
	var $folder = "export_account_data/";

	function __construct()
	{
		global $config,$sessioninfo,$path;
		$this->is_consignment=(($config['consignment_modules'])?1:0);

		$this->psth=$path;
		$this->folder=$_SERVER['DOCUMENT_ROOT']."/".$this->folder;

		parent::__construct($this->title);
	}

	function _default()
	{
		global $smarty,$sessioninfo,$config;

		unset($_SESSION['download_files']);
		load_account_file($this->path,$this->accountings,$this->skip);

		if(BRANCH_CODE == 'HQ' && !$this->is_consignment){
			$this->load_branches();
		}

		$form['date_from']=date("Y-m-01", strtotime('last month'));
		$form['date_to']=date("Y-m-t",strtotime($form['date_from']));
		$form['data_type']='cs';
		if(BRANCH_CODE == 'HQ' && !$this->is_consignment){
			$form['branch_id'][]=1;
		}
		else{
			$form['branch_id'][]=$sessioninfo['branch_id'];
		}
		$form['extra_file_name']=time()."_".$sessioninfo['id']."_".$sessioninfo['branch_id'];
		$smarty->assign("form",$form);

		$smarty->assign("accountings",$this->accountings);
		$smarty->assign("global_type",$this->global_type);
		$smarty->assign("groupby",$this->groupby);
		$smarty->assign("is_consignment",$this->is_consignment);
		$this->display('acc_export.tpl');
	}

	function setting()
	{
		global $smarty;

		load_account_file($this->path,$this->accountings,$this->skip);

		if(isset($_POST['load']) && $_POST['load']){
			$this->list_setting();
		}

		$smarty->assign("accountings",$this->accountings);
		$smarty->display('acc_export.setting.tpl');
	}

	private function list_setting()
	{
		global $smarty,$con,$sessioninfo;

		$form=$_POST;

		if(BRANCH_CODE=='HQ') 
			$branch_id=1;
		else 
			$branch_id=$sessioninfo['branch_id'];

		if($form['export_type']!=""){
			load_setting($this->accountings,$form['export_type'],$branch_id);

			$accSettings=$this->accountings[$form['export_type']]['settings'];

			$gstAcc=array();
			$normalAcc=array();

			foreach($accSettings as $key=>$acc){
				if(isset($acc['editable']) && $acc['editable']==false) continue;

				if(isset($acc['gst']) && $acc['gst']){
					$gstAcc[$key]=$acc;
				}
				elseif(is_array($acc['account']))
					$normalAcc[$key]=$acc;
				else{
					if($acc['date']) $acc['data']=$acc['date'];
					$otherAcc[$key]=$acc;
				}
			}
		
			$smarty->assign("export_type",$form['export_type']);
			$smarty->assign("gstAcc",$gstAcc);
			$smarty->assign("normalAcc",$normalAcc);
			$smarty->assign("otherAcc",$otherAcc);
			$smarty->assign("use_own_settings",($this->accountings[$form['export_type']]['use_own_settings'])?$this->accountings[$form['export_type']]['use_own_settings']:0);
		}

		$smarty->assign("form",$form);
	}

	function save_setting()
	{
		global $smarty,$con,$sessioninfo;

		$form=$_POST;
		$FormatType=$form['export_type'];

		$data=$form['data'];
		if(BRANCH_CODE=='HQ') $branch_id=1;
		else $branch_id=$sessioninfo['branch_id'];

		$where=" and branch_id=".mi($branch_id);
		$con->sql_query("delete from ExportAccSettings where format=".ms($FormatType).$where);

		if(BRANCH_CODE=='HQ' || $form['use_own_settings']){
			foreach($data['normal'] as $k=>$acc){
				if($k=='custom'){
					foreach($data['normal'][$k]['name'] as $k2=>$acc2){
						if($acc2=="") continue;
						$upd["format"] = $FormatType;
						$upd["key"] = $acc2;
						$upd["name"] = $acc2;
						$upd["account_code"] = $data['normal'][$k]['account_code'][$k2];
						$upd["account_name"] = $data['normal'][$k]['account_name'][$k2];
						$upd["is_gst"] = 0;
						$upd["updated_timestamp"] = "CURRENT_TIMESTAMP";
						$upd["branch_id"]=$branch_id;
						$upd["custom"]=1;
						$con->sql_query("replace into ExportAccSettings ".mysql_insert_by_field($upd));
						unset($upd);
					}
				}
				else{
					$upd["format"] = $FormatType;
					$upd["key"] = $k;
					$upd["name"] = $acc['name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
					$upd["is_gst"] = 0;
					$upd["updated_timestamp"] = "CURRENT_TIMESTAMP";
					$upd["branch_id"]=$branch_id;
					$con->sql_query("replace into ExportAccSettings ".mysql_insert_by_field($upd));
					unset($upd);
				}
			}

			if($data['gst']){
				foreach($data['gst'] as $k=>$acc){
					if($k=='custom'){
						foreach($data['gst'][$k]['name'] as $k2=>$acc2){
							if($acc2=="") continue;
							$upd["format"] = $FormatType;
							$upd["key"] = $acc2;
							$upd["name"] = $acc2;
							$upd["account_code"] = $data['gst'][$k]['account_code'][$k2];
							$upd["account_name"] = $data['gst'][$k]['account_name'][$k2];
							$upd["is_gst"] = 1;
							$upd["updated_timestamp"] = "CURRENT_TIMESTAMP";
							$upd["branch_id"]=$branch_id;
							$upd["custom"]=1;
							$con->sql_query("replace into ExportAccSettings ".mysql_insert_by_field($upd));
							unset($upd);
						}
					}
					else{
						$upd["format"] = $FormatType;
						$upd["key"] = $k;
						$upd["name"] = $acc['name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["is_gst"] = 1;
						$upd["updated_timestamp"] = "CURRENT_TIMESTAMP";
						$upd["branch_id"]=$branch_id;
						$con->sql_query("replace into ExportAccSettings ".mysql_insert_by_field($upd));
						unset($upd);
					}
				}
			}

			if(isset($data['other']))
			{
				foreach($data['other'] as $k=>$acc)
				{
					$upd["format"] = $FormatType;
					$upd["key"] = $k;
					$upd["name"] = $acc['name'];
					if($k=="job_as_branch_code")
					{
						$upd['setting_value'] = (isset($acc['data'])?1:0);
					}
					else{
						$upd['setting_value'] = trim($acc['data']);
					}				
					
					$upd["is_gst"] = 0;
					$upd["updated_timestamp"] = "CURRENT_TIMESTAMP";
					$upd["branch_id"]=$branch_id;
					$upd['is_other'] = 1;
					$con->sql_query("replace into ExportAccSettings ".mysql_insert_by_field($upd));
				}
			}
		
			$smarty->assign("msg","Saved.");
		}
	
		$this->setting();
	}

	function load_schedule()
	{
		global $smarty,$con,$sessioninfo;

		$this->check_file_size();
		
		if(BRANCH_CODE != 'HQ')	$where[] = "branch_id = ".$sessioninfo['branch_id'];
		
		if(!isset($_REQUEST['schedule_type']) || $_REQUEST['schedule_type']=='active'){
			$where[]="e.active=1";
			$where[]="e.archived=0";
		}elseif($_REQUEST['schedule_type']=='archive'){
			$where[]="e.active=1";
			$where[]="e.archived=1";
		}

		if($_REQUEST['batchno'] && $_REQUEST['batchno']!="") $where[]="batchno like ".ms('%'.$_REQUEST['batchno'].'%');

		$where=" where ".implode(" and ",$where);

		$q=$con->sql_query("select e.*,b.code as branch_code,u.u from ExportAccSchedule e
						left join user u on u.id=e.user_id
						left join branch b on b.id=e.branch_id
						$where order by e.added desc");

		$list=$con->sql_fetchrowset($q);

		$smarty->assign("global_type",$this->global_type);
		$smarty->assign("list",$list);
		$smarty->display('acc_export.list.tpl');
	}

	function create_schedule()
	{
		global $smarty,$con,$sessioninfo;

		$result=array();
		$form=$_POST;

		if($form['data_type']=="gaf") $form['groupby']="-";
		
		$dateFrom = strtotime($form['date_from']);
		$dateTo = strtotime($form['date_to']);
		if($dateTo < $dateFrom)
		{
			$result['status']='Error';
			$result['msg']="Date From cannot greater than Date To.";
		}
		else{
			$is_finalised = true;
			if($form["data_type"] == "cs" || $form["data_type"] == "cn" || $form["data_type"] == "cscn" || $form["data_type"] == "gaf"){
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
					$is_running = false;
					$sql=$con->sql_query("select count(*) as num from ExportAccSchedule
									where active = 1 and started = 1 and completed = 0");
					$count = $con->sql_fetchrow($sql);
					$con->sql_freeresult($sql);
					if($count["num"] > 0){
						$is_running = true;
					}
					if(!$is_running){
						$id = create_export_schedule($form);
						$result['status']='OK';
						$result['id']=$id;
					}else{
						$result['status']='Error';
						$result['msg']="There is a task still generating. Please wait the process to complete before generating a new one.";
					}
				}
			}
		}
		
		echo json_encode($result);
	}

	function remove_schedule()
	{
		$id=$_REQUEST['id'];

		update_export_schedule($id,"active",0);
	}

	function archive(){
		$id=$_REQUEST['id'];

		update_export_schedule($id,"archived",1);
	}
	
	function reactivate(){
		$id=$_REQUEST['id'];

		update_export_schedule($id,"archived",0);
	}

	function download(){
		load_account_file($this->path,$this->accountings,$this->skip);

		$id = $_REQUEST['id'];
		$form = load_export_schedule($id);

		$moduleName = $form['export_type'];
		$ClassName=$this->accountings[$moduleName]['module_name'];
		$branch_code = get_branch_code($form['branch_id']);
		$ClassFile = $this->path.$ClassName.".php";
		if(!file_exists($ClassFile)) {
			die($ClassName." Not found");
		}

		try{
			include_once($ClassFile);
		} catch (Exception $e) {
			die($e->getMessage());
		}

		$ExportAcc = new $ClassName('pos');

		if($ClassName=='SageUBS'){
			if(isset($_REQUEST['file2'])) die("exit");

			$file=$this->folder.$form['id']."/dbf_".$form['session_time'];
			$file_name=$ExportAcc->ExportFileName[$form['data_type']];
		}
		elseif($moduleName=='GST Audit File (GAF)'){
			if(isset($_REQUEST['file2'])) die("exit");

			$file=$this->folder.$form['id']."/gaf_".$form['session_time'];
			$file_name=$ExportAcc->ExportFileName[$form['data_type']];
		}else{
			if(isset($_REQUEST['file2'])){
				$file=$this->folder.$form['id']."/data2_".$form['session_time'];

				if(!file_exists($file)) die("File not exist.");

				$file_name=$ExportAcc->ExportFileName['pt'];
			}
			else{
				$file=$this->folder.$form['id']."/data_".$form['session_time'];
				$file_name=$ExportAcc->ExportFileName[$form['data_type']];
			}
		}
		
		if(!file_exists($file)){
			print "<script type='text/javascript'>
			alert('File is empty, cannot be downloaded.');
			</script>";
			exit;
		}
		
		if($form['started'] && $form['completed']){
			$file_info= pathinfo($file_name);

			$file_name = str_replace(".".$file_info['extension'],"",$file_name)."_".$branch_code.".".$file_info['extension'];

			ob_start();
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			switch(strtolower($file_info['extension'])){
				case 'pdf':
					header('Content-type: application/pdf');
					$type='inline';
					break;
				case 'csv':
					header('Content-type: text/csv');
					break;
				default:
					$download=true;
			}
			if($download) $type="attachment";

			header('Content-Disposition: attachment; filename="'.basename(sprintf($file_name,$_SERVER['DOCUMENT_ROOT'],$form['batchno'])).'"');
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($file));
			header('Accept-Ranges: bytes');
			ob_clean();
			flush();
			//echo ( chr(0xEF) . chr(0xBB) . chr(0xBF) );
			echo file_get_contents($file);
		}
		else{
			die("Error");
		}
	}

	function show_debug(){
		$id=$_REQUEST['id'];

		$form=load_export_schedule($id);
	
		$filename=$this->folder.$form['id']."/debug_".$form['session_time'].".html";
		if(file_exists($filename)){
			echo file_get_contents($filename);
		}
		else{
			echo "File not found.";
		}
	}

	function check_login(){
		die("OK");
	}

	private function load_branches(){
		global $smarty, $con, $sessioninfo;

		$con->sql_query("select branch.* from branch where branch.active=1 order by branch.sequence");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}

		$con->sql_freeresult();

		$smarty->assign("branches", $branches);
	}
  
	private function check_file_size(){
		global $smarty,$con,$sessioninfo;

		$q=$con->sql_query("select * from ExportAccSchedule where active=1 and started=1 and completed=0");

		while($r=$con->sql_fetchrow($q)){
			$filename=$this->folder.$r['id']."/data_".$r['session_time'];

			$error_filename=$this->folder.$r['id']."/error.html";

			if(file_exists($error_filename)){
				$upd=array();
				$upd["started"]=0;
				$upd["completed"]=0;
				$upd["status"]="Error";
				$upd["start_time"]="0000-00-00 00:00:00";
				$upd["end_time"]="0000-00-00 00:00:00";
				$upd["file_size"]=0;
				$upd["error_text"]=file_get_contents($error_filename);
				update_export_schedule($r['id'],$upd);
			}
			elseif(file_exists($filename)) update_export_schedule($r['id'],"file_size",filesize($filename));
		}
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

$GSTExport = new GSTExport();
?>
