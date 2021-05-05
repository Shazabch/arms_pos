<?php
/*
10/16/2018 2:33 PM Andy
- Fixed rounding issue when have foreign currency.

11/27/2018 9:19 AM Justin
- Reverted to the previous version where it does not have GRN enhancements.

11/27/2018 2:30 PM Andy
- Fixed search multiple sku query slow issue.
*/
include('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_REPORT', BRANCH_CODE), "/index.php");
if (!privilege('PO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO', BRANCH_CODE), "/index.php");

class SKU_PURCHASE_HISTORY extends Module{
	var $branches = array();
	var $branch_group = array();
	
	function __construct($title){
		global $con, $smarty;
		
		//branches
		$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while ($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		} 
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		$this->load_branch_group();
		
		// Date From / To
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
			
		}
		parent::__construct($title);
	}
	
	private function load_branch_group(){
		global $con,$smarty;
		
	    if($this->branch_group)  return $this->branch_group;
		$this->branch_group = array();
		
		// load header
		$con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchrow()){
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi 
			left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con->sql_fetchassoc()){
	        $this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branch_group['have_group'][$r['branch_id']] = $r;
		}
		$con->sql_freeresult();

		//print_r($this->branch_group);
		$smarty->assign('branch_group',$this->branch_group);
	}
	
	function _default(){
		global $sessioninfo, $smarty;
		
		if(isset($_REQUEST['load_report'])){
			$this->load_report();
			if($_REQUEST['export_excel']){
				include_once("include/excelwriter.php");
				//log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
	    $this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);
		
		// SKU
		$sid_list = array();
		if(isset($_REQUEST['sku_code_list'])){
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
			// select sku item id list
			$con->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)");
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('group_item',$group_item);
		}
		
		// Branch
		$bid_list = array();
		if(BRANCH_CODE == 'HQ'){
			$bid_list = $_REQUEST['branch_id_list'];
		}else{
			$bid_list[] = $sessioninfo['branch_id'];
		}
		
		// Date
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		
		// Validate
		$err = array();
		if(!$bid_list)	$err[] = "Please select at least one branch.";
		if(!$sid_list)	$err[] = "Please add at least one SKU.";
		if(strtotime($date_to) < strtotime($date_from))	$err[] = "Date To cannot earlier than Date From";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$got_gst = $got_foreign_currency = 0;
		$this->data = array();
		$filter = array();
		$report_title = array();
		$bcode_list = array();
		foreach($bid_list as $bid){
			$bcode_list[] = $this->branches[$bid]['code'];
		}
		$report_title[] = "Branch: ".join(', ', $bcode_list);
		$report_title[] = "Date: From $date_from to $date_to";
		
	    $filter[] = "po.active=1 and po.status=1 and po.approved=1";
	    if($sessioninfo['vendor_ids']){
			$filter[] = "po.vendor_id in (".$sessioninfo['vendor_ids'].")";
		}
		$filter[] = "po.branch_id in (".join(',', $bid_list).")";
		$filter[] = "pi.sku_item_id in (".join(',', $sid_list).")";
		$filter[] = "po.po_date between ".ms($date_from)." and ".ms($date_to);
		
		// Dont show HQ PO
		if ($config['po_hide_hq_cost_history']) {
			$filter[] = "(po.branch_id<>1 and po.po_branch_id<>1)";
		}
			
		$filter = "where ".join(' and ', $filter);
		$sql = "select pi.sku_item_id, pi.branch_id, pi.po_id, pi.id as po_item_id, po.po_no, pi.qty, pi.qty_loose, ((pi.qty*uom.fraction)+pi.qty_loose) as total_qty, pi.foc, pi.foc_loose, ((pi.foc*uom.fraction)+pi.foc_loose) as total_foc, pi.order_price, po.po_date, uom.code as po_uom_code, packing_uom.code as packing_uom_code, po.vendor_id, v.code as vendor_code, v.description as vendor_desc, po.is_under_gst, pi.selling_price, pi.tax, pi.tax_amt, pi.discount, pi.discount_amt , pi.remark, pi.remark2, po.currency_code, po.currency_rate, pi.item_nett_amt, pi.item_gst_amt, pi.item_amt_incl_gst, pi.cost_gst_code, pi.cost_gst_rate, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description as item_desc
			from po_items pi
			left join po on pi.po_id = po.id and pi.branch_id = po.branch_id
			left join uom on pi.order_uom_id = uom.id
			left join vendor v on po.vendor_id = v.id
			left join sku_items si on si.id=pi.sku_item_id
			left join uom packing_uom on packing_uom.id=si.packing_uom_id
			$filter
			order by si.sku_item_code, po.po_date desc, po.id desc";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			
			// SKU Information
			if(!isset($this->data['si_info'][$sid])){
				$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
				$this->data['si_info'][$sid]['artno'] = $r['artno'];
				$this->data['si_info'][$sid]['link_code'] = $r['link_code'];
				$this->data['si_info'][$sid]['item_desc'] = $r['item_desc'];
				$this->data['si_info'][$sid]['packing_uom_code'] = $r['packing_uom_code'];
			}
			
			if($r['is_under_gst'])	$got_gst = 1;
			
			// Remove data to save memory
			unset($r['sku_item_code'], $r['mcode'], $r['artno'], $r['link_code'], $r['item_desc'], $r['packing_uom_code']);
			
			if($r['currency_code'])	$got_foreign_currency = 1;
			if($r['currency_rate']<=0)	$r['currency_rate'] = 1;
			
			$r['base_order_price'] = $r['order_price']*$r['currency_rate'];
			$r['base_nett_amt'] = round($r['item_nett_amt']*$r['currency_rate'], 2);
			$r['base_gst_amt'] = round($r['item_gst_amt']*$r['currency_rate'], 2);
			$r['base_amt_incl_gst'] = round($r['item_amt_incl_gst']*$r['currency_rate'], 2);
						
			$this->data['data'][$sid][] = $r;
			
			
			// Item Total
			$this->data['si_info'][$sid]['total_qty'] += $r['total_qty'];
			$this->data['si_info'][$sid]['total_foc'] += $r['total_foc'];
			$this->data['si_info'][$sid]['item_nett_amt'] += $r['base_nett_amt'];
			$this->data['si_info'][$sid]['item_gst_amt'] += $r['base_gst_amt'];
			$this->data['si_info'][$sid]['item_amt_incl_gst'] += $r['base_amt_incl_gst'];
			
			// Grand Total
			$this->data['total']['total_qty'] += $r['total_qty'];
			$this->data['total']['total_foc'] += $r['total_foc'];
			$this->data['total']['base_nett_amt'] += $r['base_nett_amt'];
			$this->data['total']['base_gst_amt'] += $r['base_gst_amt'];
			$this->data['total']['base_amt_incl_gst'] += $r['base_amt_incl_gst'];
		}
		$con->sql_freeresult($q1);
		
		//print_r($this->data);
		if($_REQUEST['export_excel']){
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." (".join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title).")");
		}
		$smarty->assign('got_gst', $got_gst);
		$smarty->assign('got_foreign_currency', $got_foreign_currency);
		$smarty->assign('report_title', join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('data', $this->data);
		
		
	}
}

$SKU_PURCHASE_HISTORY = new SKU_PURCHASE_HISTORY('SKU Purchase History');
?>