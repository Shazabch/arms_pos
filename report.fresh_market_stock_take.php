<?php
/*
9/3/2010 3:22:44 PM Andy
- add privilege checking "FM_REPORT".

2/10/2011 2:50:04 PM Andy
- Add calculate GRA on fresh market cost.
- Add can preview fresh market stock take report.

6/24/2011 6:11:36 PM Andy
- Make all branch default sort by sequence, code.

7/24/2014 12:12 PM Justin
- Bug fixed on filtering SKU Type will cause sql error.

11/24/2016 10:54 AM Andy
- Fixed bugs when child sku got stock take. Change to always get parent sku only.

1/25/2019 2:46 PM Andy
- Fixed Fresh Market cost need to deduct DO cost.

2/25/2020 4:40 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
set_time_limit(0);
$maintenance->check(27);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FM_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FM_REPORT', BRANCH_CODE), "/index.php");

class FRESH_MARKET_STOCK_TAKE_REPORT extends Module{
    var $branch_id;
	var $can_select_branch = false;

	function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
	    if(BRANCH_CODE=='HQ'){
	        $this->can_select_branch = true;
            $smarty->assign('can_select_branch', $this->can_select_branch);
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		parent::__construct($title);
	}
	
	function _default(){
	    $this->init_load();
		if($this->can_select_branch)    $this->load_branches();
		$this->load_date(true);
		$this->load_pre_date(true);
		
		if($_REQUEST['load_report'])    $this->load_report();
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $con_multi;
		
		$con_multi->sql_query("select * from sku_type order by code");
		$smarty->assign('sku_type', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
	}
	
	function load_branches(){
		global $con, $smarty, $con_multi;

		$con_multi->sql_query("select * from branch order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches', $branches);
		return $branches;
	}
	
	function ajax_reload_date(){
        global $con, $smarty;
        
        $ret = array();
        $ret['st_date'] = $this->load_date();
        $ret['pre_st_date'] = $this->load_pre_date();
        
        print json_encode($ret);
	}
	
	private function load_date($sqlonly = false){
		global $con, $smarty, $con_multi;
		
		$branch_id = mi($this->branch_id);
		$con_multi->sql_query("select distinct date from stock_check where branch_id=$branch_id and is_fresh_market=1 order by date desc");
		while($r = $con_multi->sql_fetchrow()){
			$date[] = $r[0];
		}
		$con_multi->sql_freeresult();
		$smarty->assign('date', $date);
		if(!$sqlonly){
		    $smarty->assign('date_list', $date);
		    return $smarty->fetch('report.fresh_market_stock_take.date_sel.tpl');
		}
		return $date;
	}
	
	private function load_pre_date($sqlonly = false){
        global $con, $smarty, $con_multi;

		$branch_id = mi($this->branch_id);
		$con_multi->sql_query("select distinct date from stock_take_pre stp where branch_id=$branch_id and is_fresh_market=1 and imported=0 order by date desc");
		while($r = $con_multi->sql_fetchrow()){
			$pre_date[] = $r[0];
		}
		$con_multi->sql_freeresult();
		$smarty->assign('pre_date', $pre_date);
		if(!$sqlonly){
			$smarty->assign('date_list', $pre_date);
			$smarty->assign('sel_name', 'pre_date');
		    return $smarty->fetch('report.fresh_market_stock_take.date_sel.tpl');
		}
		return $pre_date;
	}
	
	private function load_report(){
		global $con, $smarty, $con_multi;
		
		$branch_id = mi($this->branch_id);
		// 1 = real stock check, 2 = preview, if not 2 must is 1
		$stock_take_type = mi($_REQUEST['stock_take_type']) == 2 ? 2 : 1;
		if($stock_take_type==2)    $is_preview = true;
		$date = $is_preview ? trim($_REQUEST['pre_date']) : trim($_REQUEST['date']);
		$sku_type = trim($_REQUEST['sku_type']);
		
		
		if(!$date)  $err[] = "Please select stock take date";   // check for date
		else{
			// check whether it is the first stock check
			$con_multi->sql_query("select min(date) from stock_check where branch_id=$branch_id and is_fresh_market=1");
			$min_date = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
			
			// return if it is first stock check
			if(strtotime($date)<=strtotime($min_date))  $err[] = "The stock take date selected is the first or early than first real fresh market stock take, the report cannot compare to its last stock take.";
			
			if(!$err){
			    // check whether items in this stock check have last stock check to compare
                $filter = array();
				$filter[] = "sc.branch_id=$branch_id and sc.is_fresh_market=1";
				$filter[] = "sc.date=".ms($date);
				$filter[] = "si.is_parent=1";
				if($sku_type)   $filter[] = "sku.sku_type=".ms($sku_type);
				//$filter[] = "sc3.qty is not null";
				$filter = "where ".join(' and ', $filter);

                //$con_multi= new mysql_multi();
				if($is_preview){
					$sql = "select si.id as sku_item_id, si.sku_id, si.sku_item_code, si.artno,si.mcode, si.description as sku_desc, sc.date as sc_date ,sum(sc.qty) as sc_qty, si.cost_price as master_cost, si.selling_price as master_selling
from stock_take_pre sc
left join sku_items si on si.id=sc.sku_item_id
left join sku on sku.id = si.sku_id
$filter and sc.imported=0
group by si.sku_id order by si.is_parent desc, si.sku_item_code";
				}else{
                    $sql = "select si.id as sku_item_id, si.sku_id, sc.sku_item_code, si.artno,si.mcode, si.description as sku_desc, sc.date as sc_date, sum(sc.selling*sc.qty) as sc_selling,sum(sc.cost*sc.qty) as sc_cost,sum(sc.qty) as sc_qty, si.cost_price as master_cost, si.selling_price as master_selling
from stock_check sc
left join sku_items si on si.sku_item_code=sc.sku_item_code
left join sku on sku.id = si.sku_id
$filter
group by si.sku_id order by si.is_parent desc, sc.sku_item_code";
	            }

				$q1 = $con_multi->sql_query($sql);
				while($r = $con_multi->sql_fetchassoc($q1)){
				    if(!$r['sku_id'])  continue;
				    
				    if($is_preview){
						// get last cost
						$con_multi->sql_query("select grn_cost from sku_items_cost_history where branch_id=$branch_id and sku_item_id=".mi($r['sku_item_id'])." and date<=".ms($date)." order by date desc limit 1");
						$temp = $con_multi->sql_fetchrow();
						$con_multi->sql_freeresult();
						
						$cost = $temp ? $temp['grn_cost'] : $r['master_cost'];
						$r['sc_cost'] = $r['sc_qty'] * $cost;
						
						// get last selling
						// temporary inactive due to no show selling price
						/*$con_multi->sql_query("select price from sku_items_price_history where branch_id=$branch_id and sku_item_id=".mi($r['sku_item_id'])." and added<=".ms($date)." order by added desc limit 1");
						$temp = $con_multi->sql_fetchrow();
						$con_multi->sql_freeresult();

						$selling = $temp ? $temp['price'] : $r['master_selling'];
						$r['sc_selling'] = $r['sc_qty'] * $selling;*/
					}
					
				    // get last stock check
				    $con_multi->sql_query("select sc.date, sum(sc.selling*sc.qty) as sc_selling,sum(sc.cost*sc.qty) as sc_cost,sum(sc.qty) as sc_qty
					from stock_check sc
					left join sku_items si on si.sku_item_code=sc.sku_item_code
					where sc.branch_id=$branch_id and sc.is_fresh_market=1 and sc.date<".ms($date)." and si.sku_id=".mi($r['sku_id'])."
					group by sc.date
					order by sc.date desc limit 1");
					$temp = $con_multi->sql_fetchrow();
					$con_multi->sql_freeresult();
					if($temp){
						$r['last_sc_date'] = $temp['date'];
						$r['last_sc_qty'] = $temp['sc_qty'];
						$r['last_sc_cost'] = $temp['sc_cost'];
						$r['last_sc_selling'] = $temp['sc_selling'];
					}
					
					$this->data[$r['sku_id']]['sc'] = $r;
				}
				$con_multi->sql_freeresult($q1);

				
				
				if(!$this->data) $err[] = "No items in stock take to compare.";   // all items have no stock check
			}
		}
		//print_r($this->data);exit;
		if($err){
		    //if($con_multi)  $con_multi->close_connection();
			$smarty->assign('err', $err);
			return;
		}

		foreach($this->data as $sku_id=>$item){
			$sku_id = mi($sku_id);
			if(!$sku_id)   continue;
			
			if(trim($item['sc']['last_sc_qty'])===''){   // if no last stock check
			    $this->data2[$sku_id] = $item; // reconstruct array, put those item with no stock take to another array
			    unset($this->data[$sku_id]);
                continue;
			}

			$last_sc_date = date("Y-m-d", strtotime($item['sc']['last_sc_date']));
			
			// get sid list
			$con_multi->sql_query("select si.id
					from sku_items si
					where si.sku_id=$sku_id");
            $sid_list = array();
		    while($r = $con_multi->sql_fetchrow()){
		        $sid = mi($r['id']);
		        $sid_list[] = $sid;
			}
			$con_multi->sql_freeresult();
	
	        if(!$sid_list)  continue; // no item?
    		$sid_str = join(',', $sid_list);
    		
			// GRN
			$sql = "select (if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as qty,
		(
		  if (gi.acc_cost is null, gi.cost, gi.acc_cost)
		  *
		  if (gi.acc_ctn is null and gi.acc_pcs is null,
		  	gi.ctn + gi.pcs / rcv_uom.fraction,
		  	gi.acc_ctn + gi.acc_pcs / rcv_uom.fraction
		  )
		) as cost
			from grn_items gi
			left join uom rcv_uom on gi.uom_id=rcv_uom.id
			left join grn on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
			left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
			where gi.branch_id=$branch_id and gi.sku_item_id in ($sid_str) and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date>=".ms($last_sc_date)." and grr.rcv_date<".ms($date);
			
			$con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
				$this->data[$sku_id]['grn']['qty'] += $r['qty'];
				$this->data[$sku_id]['grn']['total_cost'] += $r['cost'];
			}
			$con_multi->sql_freeresult();
			
			// ADJ
			$con_multi->sql_query("select sum(qty) as qty, sum(qty*cost) as total_cost
	from adjustment_items adji
	left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
	where adji.sku_item_id in ($sid_str) and adji.branch_id=$branch_id and adj.active=1 and adj.approved=1 and adj.status=1 and adj.adjustment_date>=".ms($last_sc_date)." and adj.adjustment_date<".ms($date));
			while($r = $con_multi->sql_fetchrow()){
				$this->data[$sku_id]['adj']['qty'] += $r['qty'];
				$this->data[$sku_id]['adj']['total_cost'] += $r['total_cost'];
			}
			$con_multi->sql_freeresult();
			
			// POS
			$tbl = "sku_items_sales_cache_b".$branch_id;
			$con_multi->sql_query("select sum(qty) as qty, sum(amount) as amt, sum(cost) as total_cost from $tbl tbl where sku_item_id in ($sid_str) and date>=".ms($last_sc_date)." and date<".ms($date));
			while($r = $con_multi->sql_fetchrow()){
				$this->data[$sku_id]['pos']['qty'] += $r['qty'];
				$this->data[$sku_id]['pos']['total_cost'] += $r['total_cost'];
				$this->data[$sku_id]['pos']['amt'] += $r['amt'];
			}
			$con_multi->sql_freeresult();
			
			// GRA
			$q_gra = $con_multi->sql_query("select sum(qty) as qty, sum(cost) as total_cost
			from gra_items gi
			left join gra on gi.gra_id = gra.id and gi.branch_id = gra.branch_id
			where gi.sku_item_id in (".$sid_str.") and gi.branch_id = $branch_id and gra.status=0 and gra.returned=1 and return_timestamp>=".ms($last_sc_date)." and return_timestamp<".ms($date));
			while($r = $con_multi->sql_fetchrow($q_gra)){
			    $this->data[$sku_id]['gra']['qty'] += $r['qty'];
				$this->data[$sku_id]['gra']['cost'] += $r['total_cost'];
			}
			$con_multi->sql_freeresult($q_gra);
			
			// DO
			$con_multi->sql_query("select sum((di.ctn *uom.fraction) + di.pcs) as qty, sum((di.ctn *uom.fraction) + di.pcs*di.cost) as total_cost
	from do_items di
	join do on do.id=di.do_id and do.branch_id=di.branch_id
	left join uom on di.uom_id=uom.id
	where di.sku_item_id in ($sid_str) and di.branch_id=$branch_id and do.active=1 and do.approved=1 and do.status=1 and do.checkout=1 and do.do_date>=".ms($last_sc_date)." and do.do_date<".ms($date));
			while($r = $con_multi->sql_fetchrow()){
				$this->data[$sku_id]['do']['qty'] += $r['qty'];
				$this->data[$sku_id]['do']['total_cost'] += $r['total_cost'];
			}
			$con_multi->sql_freeresult();
		}
        //$con_multi->close_connection();
        
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($branch_id);
        $report_title[] = "Stock Take Date: ".$date.($is_preview?' (Preview)':'');
        $report_title[] = "SKU Type: ".($sku_type ? $sku_type : 'All');
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('data2', $this->data2);
	}
}

$FRESH_MARKET_STOCK_TAKE_REPORT = new FRESH_MARKET_STOCK_TAKE_REPORT('Fresh Market Stock Take Report');
?>
