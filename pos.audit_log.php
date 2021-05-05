<?php
/*
9/28/2016 16:18 Qiu Ying
- Add log when download report

2/26/2020 11:42 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_AUDIT_LOG')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FRONTEND_AUDIT_LOG', BRANCH_CODE), "/index.php");

class POS_AUDIT_LOG extends Module{
	var $file_folder = "tmp/audit_log";
	
	 function __construct($title){
		global $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		$this->init();
 		parent::__construct($title);
	}
    
    function _default(){
		$this->display();
	}
	
	function init(){
        global $con, $smarty, $sessioninfo, $con_multi;
		
		if(!is_dir($this->file_folder))	check_and_create_dir($this->file_folder);
		
		$files = scandir($this->file_folder);
		for($i=2; $i<count($files); $i++) {
		  if(strtotime("-1 week") > filemtime($this->file_folder."/".$files[$i])) {
			   unlink($this->file_folder."/".$files[$i]);
		  }
		}
		
        $form = $_REQUEST;
		
        if (BRANCH_CODE!='HQ'){
		  $filter[] = "c.branch_id=".mi($sessioninfo['branch_id']);
        }
        $filter[] = "c.active=1";
        $filter = join(' and ',$filter);
        $con_multi->sql_query("select c.*, branch.code from counter_settings c left join branch on c.branch_id=branch.id where $filter order by branch.sequence, branch.code, network_name") or die(mysql_error());
        $counters = array();
        while($r = $con_multi->sql_fetchassoc())  $counters[] = $r;
        $con_multi->sql_freeresult();
        $smarty->assign('counters', $counters);
        
        if(!isset($form['date_from']) && !isset($form['date_to'])){
		  $form['date_from'] = date('Y-m-d',strtotime('-7 day'));
		  $form['date_to'] = date('Y-m-d');
        }
        
        $smarty->assign('form', $form);
	}
	 
	 function load_data(){
		  global $con, $smarty, $sessioninfo, $con_multi;
		  
		  $form = $_REQUEST;
		  
		  $filter = $item_list = array();
		  $mode = '';
		  if($form['counter_id']){
			   $filter[] = "al.branch_id = ".$form['branch_id'];
			   $filter[] = "al.counter_id = ".$form['counter_id'];
			   if($form['date']) {
					$filter[] = "al.date = ".ms($form['date']);
					$mode = 'date';
			   }else{
					$filter[] = "al.date between ".ms($form['date_from'])." and ".ms($form['date_to']);
					$mode = 'counter';
			   }
		  }else{
			   //show report or download all
			   if($form['counters']) {
					list($branch_id, $counter_id) = explode("|", $form['counters']);
					$filter[] = "al.branch_id = $branch_id";
					if($counter_id != 'all')    $filter[] = "al.counter_id = $counter_id";
					$mode = 'all';
			   }
			   
			   if(strtotime($form['date_to']) > strtotime("+ 30 day", strtotime($form['date_from']))){
					$form['date_from'] = date('Y-m-d',strtotime('-30 day',strtotime($form['date_to'])));
			   }
			   $filter[] = "al.date between ".ms($form['date_from'])." and ".ms($form['date_to']);
		  }
		  if($filter) $filter = join(' and ', $filter);
		  
		  $gr_name =array();
		  $group_file = $zip_file = '';
		  
		  $con_multi->sql_query("select al.*, b.code, cs.network_name
						  from pos_transaction_audit_log al
						  left join branch b on b.id=al.branch_id
						  left join counter_settings cs on cs.id=al.counter_id and cs.branch_id=al.branch_id
						  where $filter order by al.branch_id, al.counter_id, al.date");
		  
		  //no data found
		  if($con_multi->sql_numrows() < 1){
			   $this->display();
			   exit;
		  }
		  
		  $times = time();
		  while($r = $con_multi->sql_fetchassoc()){
			   if($form['type'] == 'show') {
					$item_list[$r['branch_id']."_".$r['counter_id']]['branch_id'] = $r['branch_id'];
					$item_list[$r['branch_id']."_".$r['counter_id']]['branch_code'] = $r['code'];
					$item_list[$r['branch_id']."_".$r['counter_id']]['counter_id'] = $r['counter_id'];
					$item_list[$r['branch_id']."_".$r['counter_id']]['counter_name'] = $r['network_name'];
					$item_list[$r['branch_id']."_".$r['counter_id']]['info'][$r['date']] = $r['audit_log'];
			   }elseif($form['type'] == 'download'){
					$content = '';
					$group_file = $r['code']."_".$r['network_name'];
					if(!in_array($group_file, $gr_name))	$gr_name[] = $group_file;
					$date = str_replace("-", "", $r['date']);
					$file = $group_file."_".$date."_".$times.".txt";
					$output = $this->file_folder."/".$file;
					$content = $r['audit_log'];
					$content = str_replace("\r\n", "\n", $content);
					$content = str_replace("\n", "\r\n", $content);
					file_put_contents($output, $content);
			   }
		  }
		  $con_multi->sql_freeresult();
		  
		  if($form['type'] == 'show') {
			   $smarty->assign('item_list', $item_list);
			   $smarty->assign('form', $form);
			   $this->display();
		  }elseif($form['type'] == 'download'){
			   if($mode == 'date') {
					header("Content-type: text/plain");
					header("Content-Disposition: attachment;filename=$file");
					readfile($output);
					log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Audit Log to TXT Format");
			   }else {
					$parent_zip = "AUDIT_LOG_".$times;
					for($i=0; $i<count($gr_name); $i++){
						 $zip_file = $gr_name[$i]."_".$times;
						 exec("cd ".$this->file_folder."; zip -9 $zip_file.zip $gr_name[$i]*$times.txt");	
					}
					if($mode == 'counter'){
						 header("Content-type: application/zip");
						 header("Content-Disposition: attachment; filename=$zip_file.zip");
						 readfile($this->file_folder."/$zip_file.zip");
					}
					else {
						 exec("cd ".$this->file_folder."; zip -9 $parent_zip.zip *$times.zip");
						 header("Content-type: application/zip");
						 header("Content-Disposition: attachment; filename=$parent_zip.zip");
						 readfile($this->file_folder."/$parent_zip.zip");
					}
					log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Audit Log to ZIP File Format");
			   }
		  }
	 }
}

$POS_AUDIT_LOG = new POS_AUDIT_LOG("Audit Log");
?>