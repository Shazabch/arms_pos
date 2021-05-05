<?php
/*
11/15/2017 10:13 AM Justin
- Removed the testing data.
*/

include('include/common.php');
$maintenance->check(169);

if(!$vp_session) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class DAILY_SKU_SB_REPORT extends Module{
	var $bid = 0;

	function __construct($title){
		global $con, $smarty, $vp_session, $config;

		$this->bid = $vp_session['branch_id'];
		
		parent::__construct($title);
	}

	function _default(){
		global $vp_session, $smarty;
		
		if(!isset($_REQUEST['date'])) $_REQUEST['date'] = date("Y-m-d");
		
		if($_REQUEST['load_report']){
			if($_REQUEST['submit_type']=='excel'){	// export excel
				include_once("include/excelwriter.php");
				log_vp($vp_session['id'], "VENDOR REPORT", 0, "Export ".$this->title);
	
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->load_report();
		}
		$this->display();
	}

	private function load_report($params = array()){
		global $con, $smarty, $vp_session, $config, $con_multi;
		
		//print_r($vp_session);
		
		$bid = mi($this->bid);
		$date = $_REQUEST['date'];
		
		$dt1 = strtotime($date);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date || !$dt1)	$err[] = "Invalid Date.";
		
		if(!$err && date("Y", strtotime($date))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
        //$sales_report_profit = doubleval($vp_session['vp']['sales_report_profit'][$bid]);
        
        if(isset($params['sku_group_bid']))	$sku_group_bid = $params['sku_group_bid'];
        if(isset($params['sku_group_id']))	$sku_group_id = $params['sku_group_id'];
		
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$sql = "select si.id as sku_item_id, si.sku_item_code, si.mcode, si.description, ifnull(sip.price, si.selling_price) as selling_price, sic.changed
				from sku_group_item sgi
				join sku_items si on si.sku_item_code=sgi.sku_item_code
				left join sku on sku.id=si.sku_id
				left join category c on c.id=sku.category_id
				left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
				left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($bid)."
				where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id
				order by si.sku_item_code";
		
		//print $sql;
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$this->data = array();
		
		// construct opening stock balance table names
		$opening_sb_date = date("Y-m-d", strtotime("$date -1 day")); // get previous day as opening stock bal
		$osb_y = date("Y", strtotime($opening_sb_date));
		$opening_sb_tbl = "stock_balance_b".mi($bid)."_".mi($osb_y);
		
		// construct closing stock balance table names
		$csb_y = date("Y", strtotime($date));
		$closing_sb_tbl = "stock_balance_b".mi($bid)."_".mi($csb_y);
		
		// sales table
		$sales_tbl = "sku_items_sales_cache_b".$bid;
		
		$q1 = $con_multi->sql_query($sql);
		$got_opening_sc = false;
		while($r = $con_multi->sql_fetchassoc($q1)){
			$sid = mi($r['sku_item_id']);
			$this->data['details'][$sid]['sku_item_code'] = $r['sku_item_code'];
			$this->data['details'][$sid]['mcode'] = $r['mcode'];
			$this->data['details'][$sid]['description'] = $r['description'];
			$this->data['details'][$sid]['changed'] = $r['changed'];

			// load selling price 
			$q2 = $con_multi->sql_query("select siph.price 
										 from sku_items_price_history siph USE INDEX(bsa)
										 where siph.branch_id = ".mi($bid)." and siph.added < ".ms($date)." and siph.sku_item_id = ".mi($sid)."
										 order by siph.added desc 
										 limit 1");
			$sp_info = $con_multi->sql_fetchassoc($q2);
			$con_multi->sql_freeresult($q2);
			
			if($sp_info['price']) $this->data['details'][$sid]['selling_price'] = $sp_info['price'];
			else $this->data['details'][$sid]['selling_price'] = $r['selling_price'];
			
			// load opening balance
			$q2 = $con_multi->sql_query("select sb.qty
										 from $opening_sb_tbl sb
										 where ".ms($opening_sb_date)." between sb.from_date and sb.to_date and sb.sku_item_id = ".mi($sid));

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$this->data['details'][$sid]['opening_sb']['qty'] += $r1['qty'];
				$this->data['total']['opening_sb']['qty'] += $r1['qty'];
			}
			$con_multi->sql_freeresult($q2);

			// load closing balance
			$q2 = $con_multi->sql_query("select sb.qty
										 from $closing_sb_tbl sb
										 where ".ms($date)." between sb.from_date and sb.to_date and sb.sku_item_id = ".mi($sid));

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$this->data['details'][$sid]['closing_sb']['qty'] += $r1['qty'];
				$this->data['total']['closing_sb']['qty'] += $r1['qty'];
			}
			$con_multi->sql_freeresult($q2);
			
			// load POS
			$q2 = $con_multi->sql_query("select sum(qty) as qty
										 from $sales_tbl tbl 
										 where tbl.date = ".ms($date)." and tbl.sku_item_id = ".mi($sid)."
										 group by tbl.sku_item_id");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$this->data['details'][$sid]['pos']['qty'] += $r1['qty'];
				$this->data['total']['pos']['qty'] += $r1['qty'];
			}
			$con_multi->sql_freeresult($q2);

			// load Adjustment
			$q2 = $con_multi->sql_query("select sum(qty) as qty, if(qty>=0,'p','n') as type 
										 from adjustment_items ai 
										 left join adjustment adj on ai.adjustment_id = adj.id and ai.branch_id = adj.branch_id 
										 where ai.branch_id = ".mi($bid)." and adj.adjustment_date = ".ms($date)." and ai.sku_item_id = ".mi($sid)." and adj.approved=1 and adj.status<2
										 group by type, ai.sku_item_id");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				if($r1['type'] == 'p'){
					$this->data['details'][$sid]['adj_in']['qty'] += $r1['qty'];
					$this->data['total']['adj_in']['qty'] += $r1['qty'];
				}elseif($r1['type'] == 'n'){
					$this->data['details'][$sid]['adj_out']['qty'] += abs($r1['qty']);
					$this->data['total']['adj_out']['qty'] += abs($r1['qty']);
				}
			}
			$con_multi->sql_freeresult($q2);
			
			// load GRN
			$q2 = $con_multi->sql_query("select sum(if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn * u.fraction + gi.pcs, gi.acc_ctn * u.fraction + gi.acc_pcs)) as qty, grn.is_ibt
										 from grn_items gi
										 left join grn on gi.grn_id = grn.id and gi.branch_id = grn.branch_id 
										 left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id 
										 left join uom u on gi.uom_id=u.id 
										 where grn.branch_id = ".mi($bid)." and grr.rcv_date = ".ms($date)." and gi.sku_item_id = ".mi($sid)." and grn.approved=1 and grn.status=1 
										 and grn.active=1 and grr.active=1 
										 group by gi.sku_item_id, grn.is_ibt");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				if($r1['is_ibt']){
					$this->data['details'][$sid]['ibt_grn']['qty'] += $r1['qty'];
					$this->data['total']['ibt_grn']['qty'] += $r1['qty'];
				}else{
					$this->data['details'][$sid]['vendor_grn']['qty'] += $r1['qty'];
					$this->data['total']['vendor_grn']['qty'] += $r1['qty'];
				}
			}
			$con_multi->sql_freeresult($q2);
			
			// load GRA
			$q2 = $con_multi->sql_query("select sum(gi.qty) as qty 
										 from gra_items gi
										 left join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id 
										 where gra.branch_id = ".mi($bid)." and gra.return_timestamp between ".ms($date." 00:00:00")." and ".ms($date." 23:59:59")." 
										 and gi.sku_item_id = ".mi($sid)." and gra.status=0 and gra.returned=1
										 group by gi.sku_item_id");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$this->data['details'][$sid]['gra']['qty'] += $r1['qty'];
				$this->data['total']['gra']['qty'] += $r1['qty'];
			}
			$con_multi->sql_freeresult($q2);
			
			// load DO
			$q2 = $con_multi->sql_query("select sum(di.ctn * u.fraction + di.pcs) as qty
										 from do_items di
										 left join do on di.do_id = do.id and di.branch_id = do.branch_id 
										 left join uom u on di.uom_id = u.id 
										 where di.branch_id=".mi($bid)." and do.do_date = ".ms($date)." and di.sku_item_id = ".mi($sid)." and do.approved=1 and do.checkout=1 and do.status<2 
										 group by di.sku_item_id");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$this->data['details'][$sid]['do']['qty'] += $r1['qty'];
				$this->data['total']['do']['qty'] += $r1['qty'];
			}
			$con_multi->sql_freeresult($q2);
			
			// load stock check
			$q2 = $con_multi->sql_query("select sum(sc.qty) as qty, sb.qty as sb_qty
										 from stock_check sc 
										 left join sku_items si on sc.sku_item_code = si.sku_item_code
										 left join $opening_sb_tbl sb on si.id=sb.sku_item_id and ".ms($opening_sb_date)." between sb.from_date and sb.to_date 
										 where sc.branch_id=".mi($bid)." and sc.date = ".ms($date)." and si.id = ".mi($sid)."
										 group by si.id");

			while($r1 = $con_multi->sql_fetchassoc($q2)){
				$got_opening_sc = true;

				$sc_adj_qty = $r1['qty'] - $r1['sb_qty'];

				$this->data['details'][$sid]['sc']['qty'] += $r1['qty'];
				$this->data['total']['sc']['qty'] += $r1['qty'];
				
				// add stock check into opening balance
				$this->data['details'][$sid]['opening_sb']['qty'] += $sc_adj_qty;
				$this->data['total']['opening_sb']['qty'] += $sc_adj_qty;

				$this->data['details'][$sid]['sc_adj']['qty'] += $sc_adj_qty;
				$this->data['total']['sc_adj']['qty'] += $sc_adj_qty;
			}
			$con_multi->sql_freeresult($q2);
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();

		//print_r($this->data);
		
		$smarty->assign('got_opening_sc', $got_opening_sc);
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date: ".$date;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$DAILY_SKU_SB_REPORT = new DAILY_SKU_SB_REPORT('Daily SKU Stock Balance Report');
?>
