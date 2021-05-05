<?php
/*
3/25/2014 2:33 PM Andy
- New Custom Report (Stock Movement Status Report) for Sbakery.

3/25/2014 3:04 PM Andy
- Add in missing stock balance not up to date checking.

3/27/2014 3:48 PM Andy
- Add print report feature.
- Fix export excel bug.
*/
include("../../include/common.php");
$maintenance->check(209);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM1')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM1', BRANCH_CODE), "/index.php");

class STOCK_MOVEMENT_STATUS_REPORT extends Module{
	var $branches = array();
	var $brands = array();
	var $sku_narrow_down_list = array(
		'with_activity' => 'Display only the SKU with activities',
		'show_all' => 'Display all selected SKU'
	);
	var $sort_list = array(
		'sku_item_code' => 'ARMS Code',
		'mcode' => 'MCode',
		'artno' => 'Art No.',
		'sku_desc' => 'Description'
	);
	var $print_per_page = 10;
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $smarty;
		
		$smarty->assign('sku_narrow_down_list', $this->sku_narrow_down_list);
		$smarty->assign('sort_list', $this->sort_list);
		$smarty->assign('print_per_page', $this->print_per_page);
		
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
		
		// branches
		$this->branches = array();
		$q1 = $con->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('branches',$this->branches);
		
		// brand
		$this->brands = array();
		$q1 = $con->sql_query("select * from brand where active=1 order by description");
		while($r = $con->sql_fetchassoc($q1)){
			$this->brands[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('brands',$this->brands);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $sessioninfo, $smarty;
		
	 	if($_REQUEST['load_report']){
	 		if($_REQUEST['export_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			
		 	$this->load_report();
		}
	 	$this->display('sbakery/report.stock_movement_status.tpl');
	}
	
	private function load_report(){
		global $con, $sessioninfo, $smarty, $con_multi;
		
		//print_r($_REQUEST);
		
		$filter = array();
		$this->sort_by = trim($_REQUEST['sort_by']);
		$this->sort_order = trim($_REQUEST['sort_order']);
		
		// sku item id list
		$sid_list = $group_item = array();
		$report_title = array();
		if(isset($_REQUEST['sku_code_list'])){
		    $sku_code_list = join(",",$_REQUEST['sku_code_list']);
		    // select sku item id list
	     	$con->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)");
	     	
			while($r = $con->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('group_item',$group_item);
		}
		
		// branch
		if(BRANCH_CODE == 'HQ'){
			$bid = mi($_REQUEST['branch_id']);
		}else{
			$bid = mi($sessioninfo['branch_id']);
		}
		if(!$bid)	$err[] = "Please select branch.";
		else	$report_title[] = "Branch: ".get_branch_code($bid);
		
		// date
		$date = trim($_REQUEST['date']);
		if(!$date)	$err[] = "Please select date.";
		else	$report_title[] = "Date: $date";
		
		// brand
		$brand_id = mi($_REQUEST['brand_id']);
		if($brand_id){
			$filter[] = "brand.id=$brand_id";
			
			$report_title[] = "Brand: ".$this->brands[$brand_id]['description'];
		}	
		
		// sku filter
		$sku_filter = trim($_REQUEST['sku_filter']);
		if($sku_filter == 'sku'){
			if(!$sid_list) $err[] = "Please select at least 1 SKU.";
			
			$filter['sid'] = "si.id in (".join(',', $sid_list).")";
			$report_title[] = "SKU Filter: By SKU";
		}elseif($sku_filter == 'cat'){
			if($_REQUEST['all_category']){
				$all_category = true;
				$report_title[] = "SKU Filter: By Category (All)";
			}else{
				$cat_id = mi($_REQUEST['category_id']);
				if(!$cat_id)	$err[] = "Please select category.";

				// get category info				
				$cat_info = get_category_info($cat_id);
				
				$filter[] = "cc.p".$cat_info['level']."=$cat_id";
				$report_title[] = "SKU Filter: By Category (".$cat_info['description'].")";
			}
		}else{
			$err[] = "Please select SKU Filter Type.";
		}
		$filter[] = "si.active=1";
		
		// sku narrow down
		$sku_narrow_down = trim($_REQUEST['sku_narrow_down']);
		$report_title[] = "(".$this->sku_narrow_down_list[$sku_narrow_down].")";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$data = array();
		$data['si_list'] = array();
		
		$con_multi= new mysql_multi();	// connect to report server
		
		$si_filter = $filter;
		$si_filter = "where ".join(' and ', $si_filter);
		
		// stock check
		$q1 = $con_multi->sql_query("select si.id as sid, sum(sc.qty) as qty, sum(sc.qty*sc.cost) as cost
		from stock_check sc 
		left join sku_items si using (sku_item_code) 
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id
		$si_filter and sc.branch_id=$bid and sc.date=".ms($date)."
		group by sid");
		while($r = $con_multi->sql_fetchassoc($q1))
		{
			$data['si_list'][$r['sid']]['data']['stock_check']['qty'] = $r['qty'];
			$data['si_list'][$r['sid']]['data']['stock_check']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult($q1);
		
		// grn
		$sql = "select si.id as sid, (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
		(
		  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
		  *
		  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
		  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
		  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
		  )
		) as cost, grn.is_future,
		gi.type
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
		left join sku_items si on grn_items.sku_item_id = si.id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id
		$si_filter and grn_items.branch_id=$bid and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date=".ms($date)." group by sid";

		$q2 = $con_multi->sql_query($sql);

		while($r = $con_multi->sql_fetchassoc($q2))
		{
			$data['si_list'][$r['sid']]['data']['grn']['qty'] = $r['qty'];
			$data['si_list'][$r['sid']]['data']['grn']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult($q2);
		
		// POS
		$q3 = $con_multi->sql_query("select si.id as sid, sc.qty, sc.cost
		from sku_items_sales_cache_b$bid sc
		left join sku_items si on sc.sku_item_id = si.id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id
		$si_filter and date=".ms($date));
		while($r = $con_multi->sql_fetchassoc($q3))
		{
			$data['si_list'][$r['sid']]['data']['pos']['qty'] = $r['qty'];
			$data['si_list'][$r['sid']]['data']['pos']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult($q3);
		
		// GRA
		$q4 = $con_multi->sql_query("select si.id as sid, sum(gi.qty) as qty, sum(gi.qty*gi.cost) as cost
		from gra_items gi
		left join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id 
		left join sku_items si on gi.sku_item_id = si.id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id
		$si_filter and gi.branch_id=$bid and gra.status=0 and gra.returned=1 and gra.return_timestamp between ".ms($date)." and ".ms($date." 23:59:59")."
		group by sid");
		while($r = $con_multi->sql_fetchassoc($q4))
		{
			$data['si_list'][$r['sid']]['data']['gra']['qty'] = $r['qty'];
			$data['si_list'][$r['sid']]['data']['gra']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult($q4);
		
		//FROM DO
		$q5 = $con_multi->sql_query("select si.id as sid, sum(di.ctn *uom.fraction + di.pcs) as qty, sum(di.cost*((di.ctn * uom.fraction) + di.pcs)) as cost
		from do_items di
		left join do on do.id=di.do_id and do.branch_id=di.branch_id
		left join uom on di.uom_id=uom.id
		left join sku_items si on di.sku_item_id = si.id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id	
		$si_filter and di.branch_id=$bid and do.approved=1 and do.checkout=1 and do.status=1 and do.active=1 and do.do_date=".ms($date)." group by sid");
		while($r = $con_multi->sql_fetchassoc($q5))
		{
			$data['si_list'][$r['sid']]['data']['do']['qty'] = $r['qty'];
			$data['si_list'][$r['sid']]['data']['do']['cost'] = $r['cost'];
		}
		$con_multi->sql_freeresult($q5);
		
		//FROM ADJUSTMENT
		$q6 = $con_multi->sql_query("select si.id as sid, ai.qty, ai.cost
		from adjustment_items ai
		left join adjustment on adjustment.id=ai.adjustment_id and adjustment.branch_id=ai.branch_id
		left join sku_items si on ai.sku_item_id = si.id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join category_cache cc on cc.category_id=c.id
		left join brand on brand.id=sku.brand_id
		$si_filter and ai.branch_id=$bid and adjustment.approved=1 and adjustment.status=1 and adjustment.active=1 and adjustment_date=".ms($date));
		while($r = $con_multi->sql_fetchassoc($q6))
		{
			if($r['qty']>0){
				$data['si_list'][$r['sid']]['data']['adj_in']['qty'] += $r['qty'];
				$data['si_list'][$r['sid']]['data']['adj_in']['cost'] += $r['qty']*$r['cost'];
			}else{
				$data['si_list'][$r['sid']]['data']['adj_out']['qty'] += abs($r['qty']);
				$data['si_list'][$r['sid']]['data']['adj_out']['cost'] += abs($r['qty'])*$r['cost'];
			}
			
		}
		$con_multi->sql_freeresult($q6);
		
		// narrow down the sku list, filter out those without any activity
		if($sku_narrow_down == 'with_activity'){
			// loop for each sku_items
			foreach($data['si_list'] as $sid => $r){
				if(!$r['data']){
					// drop this sku from data
					unset($data['si_list'][$sid]);
				}
			}
		}
		
		// construct stock in & out
		if($data['si_list']){
			foreach($data['si_list'] as $sid => $si){	// loop for each item
			
				// stock in 
				$data['si_list'][$sid]['data']['stock_in']['qty'] = $si['data']['grn']['qty'] + $si['data']['adj_in']['qty'];
				$data['si_list'][$sid]['data']['stock_in']['cost'] = $si['data']['grn']['cost'] + $si['data']['adj_in']['cost'];
				
				// stock out 
				$data['si_list'][$sid]['data']['stock_out']['qty'] = $si['data']['gra']['qty'] + $si['data']['do']['qty'] + $si['data']['adj_out']['qty'];
				$data['si_list'][$sid]['data']['stock_out']['cost'] = $si['data']['gra']['cost'] + $si['data']['do']['cost'] + $si['data']['adj_out']['cost'];
			}
		}
		
		if($data['si_list'] || $sku_narrow_down != 'with_activity'){
			$start_counter = 0;
			$limit = 5000;
			
			if($sku_narrow_down == 'with_activity'){
				//$tmp_sid_list = $sid_list;
				$tmp_sid_list = array_keys($data['si_list']);
				$tmp_sid_lenght = count($tmp_sid_list);
			}
			
			$stock_opening_day = date("Y-m-d", strtotime("-1 day", strtotime($date)));
			$stock_closing_day = $date;
			
			do{
				//print "start = $start_counter<br>";
				$stop = true;
				$tmp_filter = $filter;
				$limt_str = '';
				
				if($sku_narrow_down == 'with_activity'){
					$use_sid_list = array_slice($tmp_sid_list, $start_counter, $limit);
					$tmp_filter['sid'] = "si.id in (".join(',', $use_sid_list).")";
				}else{
					$limt_str = "limit $start_counter, $limit";
				}
				
				$tmp_filter = "where ".join(' and ', $tmp_filter);
				$q_si = $con->sql_query($q = "select si.id as sid, si.mcode, si.sku_item_code, si.artno, si.description as sku_desc, packing_uom.code as packing_uom_code, si.selling_price as master_selling, si.cost_price as master_cost, if(sic.changed is null or sic.changed,1,0) as changed
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join category_cache cc on cc.category_id=c.id
				left join brand on brand.id=sku.brand_id
				left join uom packing_uom on packing_uom.id=si.packing_uom_id
				left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
				$tmp_filter
				$limt_str");
				//die($q);
				$row_selected = $con->sql_numrows($q_si);
				while($r = $con->sql_fetchassoc($q_si)){
					$sid = mi($r['sid']);
					
					// get cost and selling
					$tmp = get_sku_item_cost_selling($bid, $sid, $date, array('cost', 'selling'));
					$r['cost'] = $tmp['cost'];
					$r['selling'] = $tmp['selling'];
					
					// get opening stock
					if($tmp = $this->get_sku_stock($bid, $sid, $stock_opening_day)){
						$r['opening']['qty'] = $tmp['qty'];
						$r['opening']['cost'] = $tmp['cost'];
						$r['opening']['total_cost'] = $tmp['qty']*$tmp['cost'];
					}else{
						//$r['opening']['qty'] = 0;	// remove this row to save memory
						$r['opening']['cost'] = $r['cost'];	// use master cost
					}
					
					// get closing stock
					if($tmp = $this->get_sku_stock($bid, $sid, $stock_closing_day)){
						$r['closing']['qty'] = $tmp['qty'];
						$r['closing']['cost'] = $tmp['cost'];
						$r['closing']['total_cost'] = $tmp['qty']*$tmp['cost'];
					}else{
						$r['closing']['cost'] = $r['cost'];	// use master cost
					}
					
					$data['si_list'][$sid]['info'] = $r;
				}
				$con->sql_freeresult($q_si);
				
				if($sku_narrow_down == 'with_activity'){
					if($start_counter+$limit<$tmp_sid_lenght)	$stop = false;	// still got item
				}else{
					if($row_selected)	$stop = false;	// still got item
				}
				
				$start_counter+=$limit;
			}while(!$stop);
		}
		if(!$data['si_list'])	unset($data['si_list']);
		else{
			if($this->sort_by){
				uasort($data['si_list'], array($this, "sort_sku_items"));
			}
			
			// calculate total
			foreach($data['si_list'] as $sid => $r){
				// opening
				$data['total']['opening']['qty'] += $r['info']['opening']['qty'];
				$data['total']['opening']['total_cost'] += $r['info']['opening']['total_cost'];
				
				// stock in
				$data['total']['stock_in']['qty'] += $r['data']['stock_in']['qty'];
				$data['total']['stock_in']['cost'] += $r['data']['stock_in']['cost'];
				
				// pos
				$data['total']['pos']['qty'] += $r['data']['pos']['qty'];
				$data['total']['pos']['cost'] += $r['data']['pos']['cost'];
				
				// stock out
				$data['total']['stock_out']['qty'] += $r['data']['stock_out']['qty'];
				$data['total']['stock_out']['cost'] += $r['data']['stock_out']['cost'];
				
				// closing
				$data['total']['closing']['qty'] += $r['info']['closing']['qty'];
				$data['total']['closing']['total_cost'] += $r['info']['closing']['total_cost'];
			}
		}
		//print_r($data);
		
		$smarty->assign('data', $data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
	}
	
	private function get_sku_stock($bid, $sid, $date){
		global $con;
		
		//print "bid = $bid, sid = $sid, date = $date<br>";
		$bid = mi($bid);
		$sid = mi($sid);
		if(!$bid || !$sid)	return false;
		
		$tbl = "stock_balance_b".mi($bid)."_".date("Y", strtotime($date));
		
		if(!isset($this->sb_tbl_exists[$tbl])){
			// check table exists
			$this->sb_tbl_exists[$tbl] = $con->sql_query("explain $tbl");
		}
		
		// table exists
		if($this->sb_tbl_exists[$tbl]){
			$q1 = $con->sql_query("select qty,cost from $tbl where sku_item_id=$sid and ".ms($date)." between from_date and to_date");
			$ret = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			return $ret;
		}
		
		return false;
	}
	
	private function sort_sku_items($a, $b){
		if($a['info'][$this->sort_by] == $b['info'][$this->sort_by])	return 0;
		
		if($this->sort_order == 'asc'){
			return $a['info'][$this->sort_by] > $b['info'][$this->sort_by] ? 1 : -1;
		}else{
			return $a['info'][$this->sort_by] > $b['info'][$this->sort_by] ? -1 : 1;
		}
		
	}
}

$STOCK_MOVEMENT_STATUS_REPORT = new STOCK_MOVEMENT_STATUS_REPORT("Stock Movement Status Report");
?>
