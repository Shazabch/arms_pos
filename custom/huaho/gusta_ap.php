<?php
/*
7/23/2018 10:16 AM Andy
- Hua Ho Gusta Accounting AP Format.

7/25/2018 12:26 PM Andy
- Enhanced to have log.

7/31/2018 12:36 PM Andy
- change filename from location code to "C00001".
- Enhanced to use fputcsv_eol to compatible to windows notepad.

10/16/2018 11:14 AM Andy
- Enhanced to have prefix_alphabet for column narration.

3/12/2019 10:03 AM Andy
- Added 'L010' for One City, prefix_alphabet is 'OC'.
*/
include("../../include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

/*
Location Code
1.) L000 = HQ
2.) L001 = QLAP
3.) L002 = DELIMA
4.) L003 = G2
5.) L004 = MINI MART
6.) L005 = TUTONG
7.) L006 = MULAUT
8.) L007 = RIMBA STORE
9.) L008 = HUA HO (MULAUT STORE)
10.) L009 = BEBATIK
11.) L010 = ONE CITY
*/

class GUSTA_AP extends Module{
	var $branches = array();
	var $branch_id = 0;
	var $date_from = '';
	var $date_to = '';
	var $location_list = array(
		1 => 'L000',
		2 => 'L007',
		3 => 'L008',
		4 => 'L001',
		5 => 'L002',
		6 => 'L003',
		7 => 'L004',
		8 => 'L005',
		9 => 'L006',
		10 => 'L009',
		11 => 'L010'
	);
	var $prefix_alphabet = array(
		2 => 'RS',
		3 => 'MS',
		4 => 'KP',
		5 => 'MD',
		6 => 'GD',
		7 => 'MM',
		8 => 'TT',
		9 => 'ML',
		10 => 'BK',
		11 => 'OC'
	);
	
	var $folder_path ='';
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->folder_path = $_SERVER['DOCUMENT_ROOT'].'/attachments/GUSTA_AP';
		
		$this->init_selection();
		
		parent::__construct($title);
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo;
	    
		// Branch
		$con->sql_query("select * from branch where active=1 and id>0");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
	}
	
	function _default(){
		global $con, $smarty, $sessioninfo;
		
		if($_REQUEST['export_file']){
			$this->generate_export_file();
		}
	
		$smarty->display("huaho/gusta_ap.tpl");
	}
	
	private function generate_export_file(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		//print_r($_REQUEST);
		$err = array();
		
		// Branch ID
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
			if(!$this->branch_id)	$this->branch_id = mi($sessioninfo['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		$bcode = $this->branches[$this->branch_id]['code'];
		
		// Date From
		$this->date_from = trim($_REQUEST['date_from']);
		if(!$this->date_from){
			$err[] = "Date From is Empty.";
		}else{
			if(!$appCore->isValidDateFormat($this->date_from)){
				$err[] = "Invalid Date From Format.";
			}
		}
		
		$this->date_to = trim($_REQUEST['date_to']);
		if(!$this->date_to){
			$err[] = "Date To is Empty.";
		}else{
			if(!$appCore->isValidDateFormat($this->date_to)){
				$err[] = "Invalid Date To Format.";
			}
		}
		
		if(!$err){
			if(strtotime($this->date_from) > strtotime($this->date_to)){
				$err[] = "Date From cannot over Date To.";
			}
		}
		
		if(!$this->location_list[$this->branch_id]){
			$err[] = "No Location Code for this branch.";
		}else{
			$location_code = trim($this->location_list[$this->branch_id]);
		}
		
		
		//print_r($err);
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		
		check_and_create_dir($this->folder_path);
		$branch_path = $this->folder_path."/".$this->branch_id;
		check_and_create_dir($branch_path);
		
		$start_time = time();
		
		$filter = array();
		$filter[] = "grr.branch_id=".$this->branch_id." and grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = "gri.type in ('INVOICE')";
		$filter[] = "grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1";
		$filter = "where ".join(' and ', $filter);
		
		/*
			1) LocCode           -  nvarchar(6)                  - Branch code
			2) Location            -  nvarchar(30)                - Branch Name
			3) InvNo                -  nvarchar(50)                - Supplier Invoice No. (from GRR)
			4) Narration           -  nvarchar(200)              - GRR No. / GRR Date 
			5) InvDate             -  nvarchar(dd/mm/yyyy) - Invoice Date
			6) SupplierCode    - nvarchar(6)                   - Vendor Code
			7) SupplierName   - nvarchar(60)                 - Vendor Name
			8) CreditDays        - integer(3)                      - Vendor Terms (30 / 60 / 90 or Nil)
			9) Description        - nvarchar(100)               - GRR Invoice Remark 
			10) InvAmount       - Decimal(15,2)                - Invoice Amount
		*/
		
		$sql = "select grr.id as grr_id,vendor.code as vendor_code, vendor.description as vendor_desc, gri.doc_no, gri.doc_date, gri.remark as doc_remark, gri.type, grr.rcv_date, vendor.term as vendor_term, grr.grr_amount
from grr
join grr_items gri on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
join grn on grn.grr_id=grr.id and grn.branch_id=grr.branch_id
left join vendor on grr.vendor_id = vendor.id
$filter
order by grr.id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$total_row = mi($con->sql_numrows());
		if($total_row <= 0){	// No Data
			return;
		}
		
		$filename = 'gusta_ap_'.$sessioninfo['id'].'_'.time();
		$tmp_file_path = '/tmp/'.$filename;
		
		$f = fopen($tmp_file_path, 'w');
		
		while($r = $con->sql_fetchassoc($q1)){
			$row = array();
			$row[] = $location_code;
			$row[] = $this->branches[$this->branch_id]['description'];
			$row[] = $r['doc_no'];
			$row[] = trim($this->prefix_alphabet[$this->branch_id]).$r['grr_id'].'/'.date("dmY", strtotime($r['rcv_date']));
			$row[] = date("dmY", strtotime($r['doc_date']));
			$row[] = $r['vendor_code'];
			$row[] = $r['vendor_desc'];
			$row[] = $r['vendor_term']>0 ? $r['vendor_term'] : 'Nil';
			$row[] = $r['doc_remark'];
			$row[] = $r['grr_amount'];
			
			//fputcsv($f, $row);
			$this->fputcsv_eol($f, $row);
		}
		$con->sql_freeresult($q1);
		
		fclose($f);
		
		
		
		$end_time = time();
		
		// Copy file to actual attachment folder
		$real_filename = 'StockReceive_Gusta_C00001_'.date("dmY_Hi", $start_time).'_'.date("dmY_Hi", $end_time).'.csv';
		$final_file_path = $branch_path."/".$real_filename;
		
		rename($tmp_file_path, $final_file_path);
		
		
		$data = array();
		//$data['file_path'] = $final_file_path;
		$data['filename'] = $real_filename;
		$data['url_path'] = str_replace($_SERVER['DOCUMENT_ROOT'], "", $final_file_path);
		
		log_br($sessioninfo['id'], 'WEB_BRIDGE', '', "Export ".$this->title.", Branch: $bcode, GRR Date: ".$this->date_from." to ".$this->date_to."<br />Filename: ".$data['url_path']);
		
		$smarty->assign('data', $data);
	}
	
	private function fputcsv_eol($handle, $array, $delimiter = ',', $enclosure = '"', $eol = "\r\n") {
		$return = fputcsv($handle, $array, $delimiter, $enclosure);
		if($return !== FALSE && "\n" != $eol && 0 === fseek($handle, -1, SEEK_CUR)) {
			fwrite($handle, $eol);
		}
		return $return;
	}
}

$GUSTA_AP = new GUSTA_AP('GUSTA AP');
?>