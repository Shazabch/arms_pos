<?php
/*
6/8/2011 9:23:04 AM Andy
- Add log record when export.

6/10/2011 6:27:07 PM Andy
- Add new export format.
- Fix exported file wrong date name.

6/16/2011 11:22:52 AM Andy
- Modified export format for "Format 2".

7/6/2011 6:13:37 PM Andy
- Fix invoice amount bugs.

3/5/2012 12:01:21 PM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.

5/23/2016 11:54 AM Andy
- Fix unable to download invoice or DN.
*/
include("include/common.php");
include("web_bridge.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('WB') || !privilege('WB_AP_TRANS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB/WB_AP_TRANS', BRANCH_CODE), "/index.php");

class WEB_BRIDGE_AP_TRANS extends Module{
	var $branches = array();
	var $branches_group = array();
	var $branch_id = 0;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
        if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['load_summary']){
			$this->load_summary();
		}
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo;
	    	
	    if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
	    if(BRANCH_CODE=='HQ' && !isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
	    
		$con->sql_query("select * from branch where active=1 and id>0");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group header
		$con->sql_query("select * from branch_group",false,false);
		while($r = $con->sql_fetchassoc()){
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();

		if($this->branches_group){
            // load branch group items
			$con->sql_query("select bgi.*,branch.code,branch.description
			from branch_group_items bgi
			left join branch on bgi.branch_id=branch.id
			where branch.active=1");
			while($r = $con->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
	}
	
	private function load_summary(){
		global $con, $smarty, $sessioninfo;
		
		$bid = mi($this->branch_id);
		$grr_date = trim($_REQUEST['date']);
		$report_title = array();
		
		if(!$bid)	$err[] = "Please select branch.";
		if(!strtotime($grr_date))	$err[] = "Please select date";
		
		$ap_settings = load_ap_settings();
		if(!$ap_settings['posting_account_code']['value']){
			$err[] = "Please setup Posting Account Code at Settings Module first.";
		}
		if(!$ap_settings['project_code']['value']){
			$err[] = "Please setup Project Code at Settings Module first.";
		}
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$filter = array();
		$filter[] = "grr.branch_id=$bid and grr.rcv_date=".ms($grr_date);
		$filter[] = "gri.type in ('INVOICE','OTHER')";
		$filter[] = "grr.active=1";
		
		$filter = "where ".join(' and ', $filter);
		
		$bcode = get_branch_code($bid);
		$report_title[] = "Branch: $bcode";
		$report_title[] = "GRR Date: $grr_date";
		
		$sql = "select grr.id as grr_id,vendor.code as vendorcode, vendor.description as vendor_desc, gri.doc_no, gri.type, grr.rcv_date, (select group_concat(doc_no) from grr_items where type='PO' and grr_id=grr.id and branch_id=grr.branch_id) as po_no, vendor.term, gri.amount
from grr_items gri 
left join grr on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
left join vendor on grr.vendor_id = vendor.id
$filter
order by vendorcode";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$output = '';
		$desc_list = array('I'=>'Purchase', 'CN'=>'Credit Note', 'DN'=>'Debit Note');
		$project_code = $ap_settings['project_code']['value'];
		if($project_code=='FOLLOW_BRANCH_CODE')	$project_code = $bcode;
		
		$this->data = array();
		
		// header
		$output .= "vendorcode, vendor_desc, doc_no, export_type, rcv_date, term, export_desc, amount, posting_account, bcode, project_code\n"; 
		while($r = $con->sql_fetchassoc($q1)){
			$type = 'I';
			if($r['type']=='OTHER'){
				if(preg_match("/^CN/i", $r['doc_no']))	$type = 'CN';
				elseif(preg_match("/^DN/i", $r['doc_no']))	$type = 'DN';
				else	$type = 'DN';
			}
			
			$r['export_type'] = $type;
			$r['export_desc'] = $desc_list[$type];
			$r['posting_account'] = $ap_settings['posting_account_code']['value'];//610-000
			$r['bcode'] = $bcode;
			
			$output .= "\"".$r['vendorcode']."\",\"".$r['vendor_desc']."\",\"".$r['doc_no']."\",\"".$r['export_type']."\",\"".$r['rcv_date']."\"";
			$output .= ",\"".$r['term']."\",\"".$r['export_desc']."\",\"".$r['amount']."\",\"".$r['posting_account']."\",\"".$r['bcode']."\", \"".$project_code."\"";
			
			$output .= "\n";
			
			$this->data['items'][] = $r;
		}
		$con->sql_freeresult($q1);
		
		if($this->data){
			check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/tmp");
			check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/tmp/web_bridge");
			
			
			//$filename = "ap_trans_".$sessioninfo['id']."_".time().".csv";
			$filename = "ap_trans_raw_data-".$bcode.'-'.date("Ymd", strtotime($grr_date)).".csv";
			$filepath = $_SERVER['DOCUMENT_ROOT']."/tmp/web_bridge/$filename";
			file_put_contents($filepath, $output);
			$this->data['filename'] = $filename;
			
			//log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Export AP Trans, Branch: $bcode, GRR Date: $grr_date");
		}
		
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function download_export_file(){
		global $con, $smarty, $sessioninfo;
		$allowed_format = array('csv1', 'txt1_invoice', 'txt1_dn');
		
		//print_r($_REQUEST);	
		
		$raw_filename = trim($_REQUEST['rf']);
		$export_format = trim($_REQUEST['format']);
		
		if(!$raw_filename)	js_redirect('Invalid file.', $_SERVER['PHP_SELF']);
		if(!in_array($export_format, $allowed_format))	js_redirect('Invalid export format.', $_SERVER['PHP_SELF']);
		
		$f = fopen($_SERVER['DOCUMENT_ROOT']."/tmp/web_bridge/$raw_filename","rt");
		if(!$f)	js_redirect('Invalid file.', $_SERVER['PHP_SELF']);
		list($dummy, $bcode, $grr_date) = explode("-", str_replace(".csv", "", $raw_filename), 3);
		$grr_date = substr($grr_date, 0, 4).'-'.substr($grr_date, 4, 2).'-'.substr($grr_date, 6, 2);
		
		// get header
		$line = fgetcsv($f);
		if($line){
			$colname_data = array();
			foreach($line as $key=>$colname){
				$colname_data[$key] = trim($colname);
			}
		}
		
		// construct data for export
		$data = array();
		while($temp = fgetcsv($f)){
			$row = array();
			foreach($temp as $key=>$value){
				$row[$colname_data[$key]] = $value;
			}
			switch($export_format){
				case 'txt1_invoice':
					if($row['export_type']!='I')	continue 2;	// only invoice
					break;
				case 'txt1_dn':
					if($row['export_type']!='DN')	continue 2;	// only DN
					break;
			}
			$data[] = $row;
		}
		
		if(!$data)	js_redirect('There is no data to export.', $_SERVER['PHP_SELF']);
		
		$smarty->assign('data', $data);
		$smarty->assign('export_format', $export_format);
		$output = $smarty->fetch('web_bridge.ap_trans.file.tpl');
		
		/*foreach($data as $r){
			$output_col = array();
			switch($export_format){
				case 'csv1':
					$output_col = array($r['vendorcode'], $r['vendor_desc'], $r['doc_no'], $r['export_type'], $r['rcv_date'], $r['term'], $r['export_desc'], number_format($r['amount'],2), $r['posting_account'], $r['bcode']);
					break;
				case 'txt1_invoice':
					if($r['export_type']!='I')	continue;	// only invoice
					$output_col = array($r['vendorcode'], $r['doc_no'], date("d/m/y", strtotime($r['rcv_date'])), date("d/m/y", strtotime($r['rcv_date'])), $r['term'], $r['vendor_desc'], '' ,'','','1',number_format($r['amount'],2), 'F', $r['doc_no'], $r['posting_account'], 'Trade Purchase', '', number_format($r['amount'],2));
					break;
				case 'txt1_dn':
					if($r['export_type']!='DN')	continue;	// only invoice
					$output_col = array($r['vendorcode'], $r['doc_no'], date("d/m/y", strtotime($r['rcv_date'])), date("d/m/y", strtotime($r['rcv_date'])), $r['term'], $r['vendor_desc'], '','','','1',number_format($r['amount'],2), 'F', $r['doc_no'], $r['posting_account'], 'Trade Purchase', '', number_format($r['amount'],2));
					break;
			}
			
			if($output_col){
				if($export_format=='txt1_invoice' || $export_format=='txt1_dn'){
					$output .= join(";", $output_col).";\n";
				}
				else{
					$output .= "\"".join("\",\"", $output_col)."\"\n";
				}
			}
		}*/
		
		if(!$output)	js_redirect('There is no data to export.', $_SERVER['PHP_SELF']);
		
		$export_filename = "ap_trans_".$bcode."_".date("Ymd", strtotime($grr_date)).".csv";
		if($export_format=='txt1_invoice'){
			$export_filename = "ap_trans_".$bcode."_I_".date("Ymd", strtotime($grr_date)).".txt";
		}elseif($export_format=='txt1_dn'){
			$export_filename = "ap_trans_".$bcode."_DN_".date("Ymd", strtotime($grr_date)).".txt";
		}
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Export AP Trans, Branch: $bcode, GRR Date: $grr_date, Format: $export_format");
		
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=".$export_filename);
		print $output;
	}
}

$WEB_BRIDGE_AP_TRANS = new WEB_BRIDGE_AP_TRANS('Web Bridge: AP Trans');
?>
