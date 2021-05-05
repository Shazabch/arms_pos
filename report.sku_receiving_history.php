<?php
/*
12/12/2018 1:51 PM Justin
- Bug fixed on GST amount calculated wrongly.
- Bug fixed on item amount should not include tax amount.
- Bug fixed on GRN items with zero qty will show out.

5/16/2019 11:58 AM William
- Pickup report_prefix for enhance "GRR".
*/
include('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

class SKU_RECEIVING_HISTORY extends Module{
	var $branches = array();
	var $branch_group = array();
	
	function __construct($title){
		global $con, $smarty;
		
		//branches
		$q1 = $con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while ($r = $con->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		} 
		$con->sql_freeresult($q1);
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
		
	    $filter[] = "grn.active=1 and grr.active=1 and grn.status=1 and grn.approved=1";
	    if($sessioninfo['vendor_ids']){
			$filter[] = "grn.vendor_id in (".$sessioninfo['vendor_ids'].")";
		}
		$filter[] = "grn.branch_id in (".join(',', $bid_list).")";
		$filter[] = "gi.sku_item_id in (".join(',', $sid_list).")";
		$filter[] = "grr.rcv_date between ".ms($date_from)." and ".ms($date_to);
		
		$return_pcs="";
		if(!$config['use_grn_future_allow_generate_gra']) $return_pcs=" - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))";
			
		$filter = "where ".join(' and ', $filter);
		$sql = "select gi.sku_item_id, gi.branch_id, gi.grn_id, gi.id as grn_item_id, grr.rcv_date, rcv_uom.code as grn_uom_code, packing_uom.code as packing_uom_code, 
				grn.vendor_id, v.code as vendor_code, v.description as vendor_desc, grn.is_under_gst, grr.currency_code, grr.currency_rate, si.selling_price,
				si.sku_item_code, si.mcode, si.artno, si.link_code, si.description as item_desc, grn.acc_action,
				(if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) as item_qty,
				(if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) as item_cost_price,
				round((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction, 2) as item_gross_amt,
				if(gi.acc_gst_id > 0, gi.acc_gst_rate, gi.gst_rate) as item_gst_rate,branch.report_prefix 
				from grn_items gi
				left join branch on gi.branch_id = branch.id
				left join grn on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				left join uom rcv_uom on gi.uom_id = rcv_uom.id
				left join vendor v on grn.vendor_id = v.id
				left join sku_items si on si.id=gi.sku_item_id
				left join uom packing_uom on packing_uom.id=si.packing_uom_id
				$filter
				having item_qty != 0
				order by si.sku_item_code, grr.rcv_date desc, grr.id desc";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			
			//get selling price for GRN
			$price_date = date("Y-m-d",strtotime("+1 day",strtotime($r['rcv_date'])));
			$q2=$con->sql_query("select siph.price as selling_price
								 from sku_items_price_history siph
								 left join sku_items on sku_items.id=sku_item_id
								 where sku_item_id = ".mi($r['sku_item_id'])." and siph.branch_id = ".mi($r['branch_id'])." and siph.added < ".ms($price_date)." 
								 order by siph.added desc limit 1");
			$sp_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($sp_info['selling_price']) $r['selling_price'] = $sp_info['selling_price'];
			
			// SKU Information
			if(!isset($this->data['si_info'][$sid])){
				$this->data['si_info'][$sid]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$sid]['mcode'] = $r['mcode'];
				$this->data['si_info'][$sid]['artno'] = $r['artno'];
				$this->data['si_info'][$sid]['link_code'] = $r['link_code'];
				$this->data['si_info'][$sid]['item_desc'] = $r['item_desc'];
				$this->data['si_info'][$sid]['packing_uom_code'] = $r['packing_uom_code'];
			}
			
			if($r['is_under_gst']){
				$got_gst = 1;
				$item_gst_amt = round(($r['item_cost_price'] * $r['item_gst_rate'] / 100) * $r['item_qty'], 2);
				$item_amt = round($r['item_gross_amt'] + $item_gst_amt, 2);
			}else{
				$item_gst_amt = 0;
				$item_amt = $r['item_gross_amt'];
			}
			
			// Remove data to save memory
			unset($r['sku_item_code'], $r['mcode'], $r['artno'], $r['link_code'], $r['item_desc'], $r['packing_uom_code']);
			
			if($r['currency_code'])	$got_foreign_currency = 1;
			if($r['currency_rate']<=0)	$r['currency_rate'] = 1;
			
			$r['base_order_price'] = $r['item_cost_price']*$r['currency_rate'];
			$r['base_nett_amt'] = round($r['item_gross_amt']*$r['currency_rate'], 2);
			$r['base_gst_amt'] = round($item_gst_amt*$r['currency_rate'], 2);
			$r['base_amt_incl_gst'] = round($item_amt*$r['currency_rate'], 2);
						
			$this->data['data'][$sid][] = $r;
			
			
			// Item Total
			$this->data['si_info'][$sid]['item_qty'] += $r['item_qty'];
			$this->data['si_info'][$sid]['item_nett_amt'] += $r['base_nett_amt'];
			$this->data['si_info'][$sid]['item_gst_amt'] += $r['base_gst_amt'];
			$this->data['si_info'][$sid]['item_amt_incl_gst'] += $r['base_amt_incl_gst'];
			
			// Grand Total
			$this->data['total']['total_qty'] += $r['item_qty'];
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

$SKU_RECEIVING_HISTORY = new SKU_RECEIVING_HISTORY('SKU Receiving History');
?>