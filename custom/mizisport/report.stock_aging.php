<?php
/*
8/13/2010 10:04:44 AM Andy
- Hide Item with "SKU without inventory".

8/3/2010 12:59:07 PM Justin
- Created GRA, DO, Adjustment, DN and CN qty columns and totals of GRA, DO, Adjustment, CN and DN.
- Check config first while process CN and DN queries.
- Added config check on the report printing for CN and DN columns.
- Amended the Total Balance to add GRA, DO, Adjustment, CN and DN qty.

10/25/2010 5:44:43 PM Alex
- fix add 1 day on date checking while compare with timestamp

3/7/2011 10:04:11 AM Justin
- Fixed the Use GRN errors.

3:36 PM 5/18/2011 3:36:11 PM Justin
- Fixed the SKU Item Code and Description missing when report printing.

6/24/2011 6:37:16 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:43:21 PM Andy
- Change split() to use explode()

7/27/2011 2:29:21 PM Justin
- Fixed the sql query while filter by SKU Group.

8/10/2011 9:54:21 AM Justin
- Fixed the missing figures of GRA, DO and Adjustment.

11/15/2011 3:47:15 PM Andy
- Change "Use GRN" query.
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

3/15/2012 3:33:54 PM Andy
- Change when update sales cache getting last grn vendor method.

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.

8/28/2012 14:55:00 PM Fithri
 - Add 'Added' column
 
9/12/2012 12:52 PM Justin
- Enhanced the pagination.

9/20/2012 6:06 PM Justin
- Bug fixed on pagination problem that cannot work properly.

9/27/2012 10:19:00 AM Fithri
- stock aging report add can filter by added date, can choose no filter

10/9/2012 2:07 PM Justin
- Bug fixed on percentage were calculated wrongly.
- Bug fixed on pagination that missed to print some of the SKU items.

10/11/2012 4:23 PM Justin
- Added new filter "Launch Date".
*/
include("../../include/common.php");
include("../../include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "../../index.php");

//if ($_REQUEST['group_by_branch'])

class Stock_Aging_Report extends Report
{
	function run_report($bid, $tbl_name){
        global $con, $smarty, $sessioninfo;
        
        $con_multi = new mysql_multi();

        $date_from = $this->date_from;
        $date_to = $this->date_to;
        $stock_age = $this->stock_age;
		$sa_where = $this->sa_where;
		$sa_having = $this->sa_having;
		$qty_sold_where = $this->qty_sold_where;
		$excel_fmt = $this->excel_fmt;
		$j = 0;
		$array_slice = 0;

		if($bid!=''){
			$by_bid = "and grn_items.branch_id in (".$bid.")";
			$gra_by_bid = "and gra_items.branch_id in (".$bid.")";
			$do_by_bid = "and do_items.branch_id in (".$bid.")";
			$ai_by_bid = "and ai.branch_id in (".$bid.")";
			$cn_by_bid = "and cn_items.branch_id in (".$bid.")";
			$dn_by_bid = "and dn_items.branch_id in (".$bid.")";
		}
		
		$navigation = 1;
		if($this->next) $this->next += 1;
		//elseif($this->prev) $this->prev -= 1;
		
		if($this->sku_item_list){
			do{
				if(!$_REQUEST['group_by_branch'] && !$excel_fmt){
					if((!$this->next && !$this->prev) || $this->next){
						//print $this->sku_item_list[$this->next]."+".$array_slice."<br />";
						$sku_item_list = array_slice($this->sku_item_list, $this->next+$array_slice, 150);
					}elseif($this->prev){
						$array_start = $_SESSION['stock_aging'][$sessioninfo['id']][$this->time_id]['page_info'][$this->page_no]['idx_from'];
						$array_end = $_SESSION['stock_aging'][$sessioninfo['id']][$this->time_id]['page_info'][$this->page_no]['idx_to'];
						//if($array_slice) $array_slice += 1;
						$sku_item_list = array_slice($this->sku_item_list, $array_start+$array_slice, 150);
						//print_r($sku_item_list);
						//exit;
					}
				}else $sku_item_list = $this->sku_item_list;

				if(!$sku_item_list) continue;

				for($i=0; $i<count($tbl_name); $i++){
					// stock sold query
					$sql = "select sisc.sku_item_id,
							sum(ifnull(sisc.qty, 0)) as qty_sold
							from $tbl_name[$i] sisc
							left join sku_items si on si.id = sisc.sku_item_id
							where sisc.sku_item_id in (".join(',', $sku_item_list).")
							group by sisc.sku_item_id
							order by si.sku_item_code";
					//echo "$sql<br /><br />";
					
					$qty_sold = $con_multi->sql_query($sql);
		
					while($s = $con_multi->sql_fetchrow($qty_sold)){
						if ($_REQUEST['group_by_branch']) {
							$b = str_replace("sku_items_sales_cache_b",'',$tbl_name[$i]);
							$this->table[$b]['branch_id'] = $b;
							$this->table[$b]['qty_sold'] += $s['qty_sold'];
						}else {
							$sku_key = $s['sku_item_id'];
							$this->table[$sku_key]['sku_item_id'] = $sku_key;
							$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
							$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
							$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
							$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
							$this->table[$sku_key]['qty_sold'] += $s['qty_sold'];
						}
					}
				}
				
				// stock receive query
				$pcs_str = "(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, ifnull(grn_items.ctn,0)*uom.fraction + grn_items.pcs, ifnull(grn_items.acc_ctn,0)*uom.fraction + grn_items.acc_pcs))";
				
				if ($_REQUEST['group_by_branch']) {
					$gr = 'group by grn_items.branch_id';
					$or = 'order by grn_items.branch_id';
				}
				else {
					$gr = 'group by grn_items.sku_item_id';
					$or = 'order by si.sku_item_code, grn_items.sku_item_id';
				}
				
				$sql = "select grn_items.sku_item_id, grn_items.branch_id,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(".ms($date_to).", '%Y-%m'), $pcs_str, 0)) as 30_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(date_sub(".ms($date_to).", interval 1 month), '%Y-%m'), $pcs_str, 0)) as 60_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(date_sub(".ms($date_to).", interval 2 month), '%Y-%m'), $pcs_str, 0)) as 90_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(date_sub(".ms($date_to).", interval 3 month), '%Y-%m'), $pcs_str, 0)) as 120_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(date_sub(".ms($date_to).", interval 4 month), '%Y-%m'), $pcs_str, 0)) as 150_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') = date_format(date_sub(".ms($date_to).", interval 5 month), '%Y-%m'), $pcs_str, 0)) as 180_qty_rcv,
						sum(if(date_format(grr.rcv_date, '%Y-%m') < date_format(date_sub(".ms($date_to).", interval 5 month), '%Y-%m'), $pcs_str, 0)) as above_180_qty_rcv
						from grn_items
						left join grn on grn.id = grn_items.grn_id and grn.branch_id = grn_items.branch_id
						left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
						left join uom on uom.id = grn_items.uom_id
						left join sku_items si on si.id = grn_items.sku_item_id
						where grn_items.sku_item_id in (".join(',', $sku_item_list).") $by_bid
						$gr 
						$or";
						//echo 'A'.count($sku_item_list).'<br />';
						//echo $sql."<br /><br />";
				
				/*if ($_REQUEST['group_by_branch']) {
					$sql1 = "select sku_item_id
							from grn_items
							left join grn on grn.id = grn_items.grn_id and grn.branch_id = grn_items.branch_id
							left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
							left join uom on uom.id = grn_items.uom_id
							where grn_items.sku_item_id in (".join(',', $sku_item_list).")
							group by grn_items.sku_item_id";
					
					$skuid = $con_multi->sql_query($sql1);
					
					while($r1 = $con_multi->sql_fetchrow($skuid)){
						$sku_key = $r1['sku_item_id'];
						$sku_item_id[$sku_key] = $r1['sku_item_id'];
					}

				}*/
				
				$qty_rcv = $con_multi->sql_query($sql);
				
				while($r = $con_multi->sql_fetchrow($qty_rcv)){
				
					if ($_REQUEST['group_by_branch']) {
						$sku_key = $r['branch_id'];
						$this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
					}
					else {
						$sku_key = $r['sku_item_id'];
						$this->table[$sku_key]['sku_item_id'] = $r['sku_item_id'];
						$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
						$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
						$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
						$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
					}
					
					$this->table[$sku_key]['30_qty_rcv'] += $r['30_qty_rcv'];
					$this->table[$sku_key]['60_qty_rcv'] += $r['60_qty_rcv'];
					$this->table[$sku_key]['90_qty_rcv'] += $r['90_qty_rcv'];
					$this->table[$sku_key]['120_qty_rcv'] += $r['120_qty_rcv'];
					$this->table[$sku_key]['150_qty_rcv'] += $r['150_qty_rcv'];
					$this->table[$sku_key]['180_qty_rcv'] += $r['180_qty_rcv'];
					$this->table[$sku_key]['above_180_qty_rcv'] += $r['above_180_qty_rcv'];
					
				}
				
				$con_multi->sql_freeresult();

				if($stock_age){
					foreach($this->table as $sku_key=>$t){
						if($stock_age==2){
							if(($t['60_qty_rcv']+$t['90_qty_rcv']+$t['120_qty_rcv']+$t['150_qty_rcv']+$t['180_qty_rcv']+$t['above_180_qty_rcv'])-$t['qty_sold'] <= 0){

								unset($this->table[$sku_key]);
							}
						}elseif ($stock_age==3){
							if(($t['90_qty_rcv']+$t['120_qty_rcv']+$t['150_qty_rcv']+$t['180_qty_rcv']+$t['above_180_qty_rcv'])-$t['qty_sold'] <= 0){
								unset($this->table[$sku_key]);
							}
						}elseif ($stock_age==4){
							if(($t['120_qty_rcv']+$t['150_qty_rcv']+$t['180_qty_rcv']+$t['above_180_qty_rcv'])-$t['qty_sold'] <= 0){
								unset($this->table[$sku_key]);
							}
						}elseif ($stock_age==5){
							if(($t['150_qty_rcv']+$t['180_qty_rcv']+$t['above_180_qty_rcv'])-$t['qty_sold'] <= 0){
								unset($this->table[$sku_key]);
							}
						}elseif ($stock_age==6){
							if(($t['above_180_qty_rcv'])-$t['qty_sold'] <= 0){
								unset($this->table[$sku_key]);
							}
						}
					}
				}
				
				if($sku_item_list){
					// GRA Qty
					if ($_REQUEST['group_by_branch']) {
						$col = 'gra_items.branch_id, sum(qty) as qty';
						$grp = 'gra_items.branch_id';
					}else {
						$col = 'sku_items.id, sum(qty) as qty';
						$ord = 'order by sku_items.sku_item_code';
						$grp = 'sku_items.id';
					}
					$con_multi->sql_query($gra="select $col
										   from gra_items
										   left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
										   left join sku_items on sku_item_id = sku_items.id
										   where sku_items.id in (".join(',', $sku_item_list).") $gra_by_bid
										   and gra.status=0 and gra.returned=1 and gra.return_timestamp <= ".ms($this->date_to_timestamp)."
										   group by $grp
										   $ord");//print "$gra<br /><br />";

					while($r=$con_multi->sql_fetchrow())
					{
						if ($_REQUEST['group_by_branch']) {
							if (!isset($this->table[$r['branch_id']]['branch_id'])) $this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
							$this->table[$r['branch_id']]['gra_qty'] += $r['qty'];
						}
						else{
							$sku_key = $r['id'];
							$this->table[$sku_key]['sku_item_id'] =  $sku_key;
							$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
							$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
							$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
							$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
							$this->table[$sku_key]['gra_qty'] += $r['qty'];
						}
					}
					$con_multi->sql_freeresult();

					// DO Qty
					if ($_REQUEST['group_by_branch']) {
						$col = 'do_items.branch_id, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty';
						$grp = 'do_items.branch_id';
					}else{
						$col = 'sku_items.id, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty';
						$ord = 'order by sku_items.sku_item_code';
						$grp = 'sku_items.id';
					}
					$con_multi->sql_query("select $col
										   from do_items
										   left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
										   left join sku_items on sku_item_id = sku_items.id
										   left join uom on do_items.uom_id=uom.id
										   where sku_items.id in (".join(',', $sku_item_list).") $do_by_bid
										   and do.approved=1 and do.checkout=1 and do.status<2 and do.do_date<=".ms($date_to)."
										   group by $grp
										   $ord");

					while($r=$con_multi->sql_fetchrow())
					{
						if ($_REQUEST['group_by_branch']) {
							if (!isset($this->table[$r['branch_id']]['branch_id'])) $this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
							$this->table[$r['branch_id']]['do_qty'] += $r['qty'];
						}
						else{
							$sku_key = $r['id'];
							$this->table[$sku_key]['sku_item_id'] =  $sku_key;
							$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
							$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
							$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
							$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
							$this->table[$sku_key]['do_qty'] += $r['qty'];
						}
					}
					$con_multi->sql_freeresult();

					//ADJUSTMENT Qty
					if ($_REQUEST['group_by_branch']) {
						$col = 'ai.branch_id, sum(qty) as qty';
						$grp = 'ai.branch_id';
					}
					else {
						$col = 'sku_items.id, sum(qty) as qty';
						$ord = 'order by sku_items.sku_item_code';
						$grp = 'sku_items.id';
					}
					$con_multi->sql_query("select $col
										   from adjustment_items ai
										   left join adjustment a on a.id=ai.adjustment_id and a.branch_id=ai.branch_id
										   left join sku_items on sku_item_id = sku_items.id
										   where sku_items.id in (".join(',', $sku_item_list).") $ai_by_bid
										   and a.approved=1 and a.status<2 and a.adjustment_date<=".ms($date_to)."
										   group by $grp
										   $ord");

					while($r=$con_multi->sql_fetchrow())
					{
						if ($_REQUEST['group_by_branch']) {
							if (!isset($this->table[$r['branch_id']]['branch_id'])) $this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
							$this->table[$r['branch_id']]['adj_qty'] += $r['qty'];
						}
						else{
							$sku_key = $r['id'];
							$this->table[$sku_key]['sku_item_id'] =  $sku_key;
							$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
							$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
							$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
							$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
							$this->table[$sku_key]['adj_qty'] += $r['qty'];
						}
					}
					$con_multi->sql_freeresult();

					// If found config while process the CN and DN queries
					if($config['consignment_modules']){
						// CN Qty
						$con_multi->sql_query("select sku_items.id, sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty, 
											   cn.branch_id
											   from cn_items
											   left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
											   left join sku_items on sku_item_id = sku_items.id
											   left join uom on cn_items.uom_id=uom.id
											   where sku_items.id in (".join(',', $sku_item_list).") $cn_by_bid
											   and cn.active=1 and cn.approved=1 and cn.status=1 and cn.date<=".ms($date_to)."
											   group by sku_items.id
											   order by sku_items.sku_item_code");

						while($r=$con->sql_fetchrow()){

							if ($_REQUEST['group_by_branch']) {
								if (!isset($this->table[$r['branch_id']]['branch_id'])) $this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
								$this->table[$r['branch_id']]['dn_qty'] += $r['qty'];
							}
							else{
								$sku_key = $r['id'];
								$this->table[$sku_key]['sku_item_id'] =  $sku_key;
								$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
								$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
								$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
								$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
								$this->table[$sku_key]['dn_qty'] += $r['qty'];
							}
						}
						$con_multi->sql_freeresult();

						// DN Qty
						$con_multi->sql_query("select sku_items.id, sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty, 
											   dn.branch_id
											   from dn_items
											   left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
											   left join sku_items on sku_item_id = sku_items.id
											   left join uom on dn_items.uom_id=uom.id
											   where sku_items.id in (".join(',', $sku_item_list).") $dn_by_bid
											   and dn.active=1 and dn.approved=1 and dn.status=1 and dn.date<=".ms($date_to)."
											   group by sku_items.id
											   order by sku_items.sku_item_code");

						while($r=$con_multi->sql_fetchrow()){
							if ($_REQUEST['group_by_branch']) {
								if (!isset($this->table[$r['branch_id']]['branch_id'])) $this->table[$r['branch_id']]['branch_id'] = $r['branch_id'];
								$this->table[$r['branch_id']]['dn_qty'] += $r['qty'];
							}
							else{
								$sku_key = $r['id'];
								$this->table[$sku_key]['sku_item_code'] = $this->sku_item_code[$sku_key]['sku_item_code'];
								$this->table[$sku_key]['description'] = $this->sku_item_code[$sku_key]['description'];
								$this->table[$sku_key]['added'] = $this->sku_item_code[$sku_key]['added'];
								$this->table[$sku_key]['launch_date'] = $this->sku_item_code[$sku_key]['launch_date'];
								$this->table[$sku_key]['dn_qty'] += $r['qty'];
							}
						}
						$con_multi->sql_freeresult();
					}
				}
				
				if(!$_REQUEST['group_by_branch'] && !$excel_fmt){
					if(count($this->table) < 100){
						//$this->table = array_slice($this->table, 0, 100);
						$array_slice += 150;
						if(count($sku_item_list) == 150) continue;
						//unset($this->table);
					}else{
						ksort($this->table);
						$this->table = array_slice($this->table, 0, 100);
					}
					if((!$this->prev && !$this->next) || $this->next){
						if(!$this->next){
							$first_sid = $this->table[0]['sku_item_id'];
							$this->prev_key = array_search($first_sid, $this->sku_item_list);
						}else $this->prev_key = $this->next;
						$last_sid = $this->table[99]['sku_item_id'];
						//print $last_sid."<br />";
						$this->next_key = array_search($last_sid, $this->sku_item_list);
						//print $this->sku_item_list[$this->next_key];
					}else{
						$this->prev_key = $array_start;
						//print_r($this->table);
						$this->next_key = $array_end;
					}
					
					if(!$this->page_no) $this->page_no = 1;

					if((!$this->next && !$this->prev) || $this->next){
						if($this->page_no == 1) $this->prev_key = 0;
						$_SESSION['stock_aging'][$sessioninfo['id']][$this->time_id]['page_info'][$this->page_no]['idx_from'] = $this->prev_key;
						$_SESSION['stock_aging'][$sessioninfo['id']][$this->time_id]['page_info'][$this->page_no]['idx_to'] = $this->next_key;
					}
				}
			}while(count($this->table) < 100 && count($sku_item_list) == 150);
		}
    	$con_multi->close_connection();
	}
	
	function generate_report(){
		global $con, $smarty, $sessioninfo;
		$this->count = 0;
		$where = $this->where;
		$branch_group = $this->branch_group;

		$year = $_REQUEST['year'];
		$month = $_REQUEST['month'];
		
		$this->load_branch_data();
		
		$con->sql_query("select id, description from category");
		while($r = $con->sql_fetchrow()){
			$cat[$r['id']] = $r['description'];
		}
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					$tbl_name[] = "sku_items_sales_cache_b".mi($bid);
					$grp_bid[] = $bid;
				}
			}
			$this->run_report(join(",", $grp_bid),$tbl_name);
			$report_title[] = "Branch Group: ".$branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $tbl_name[] = "sku_items_sales_cache_b".mi($bid);
	            $this->run_report($bid,$tbl_name);
	            $report_title[] = "Branch: ".BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
	                $bg_sql = "select * from branch where active=1 order by sequence,code";
					
					$q_b = $con->sql_query($bg_sql);
					while($r = $con->sql_fetchrow($q_b)){
                        $tbl_name[] = "sku_items_sales_cache_b".mi($r['id']);
					}
					$this->run_report('',$tbl_name);
				}else{	// is a particular branch
	                $tbl_name[] = "sku_items_sales_cache_b".mi($bid);
		            $this->run_report($bid,$tbl_name);
					$report_title[] = "Branch: ".get_branch_code($bid);
				}
			}
		}
		
		$report_title[] = "Year: ".$year;
		$report_title[] = "Month: ".date("M", strtotime($this->date_from));
		
		// decide the different report title to be display on the template if SKU group found
		if($this->sku_group_desc){
			$report_title[] = "SKU Group: ".$this->sku_group_desc;
		}else{
			$con->sql_query("select description from category where id = ".mi($_REQUEST['category_id']));
			$cat = $con->sql_fetchrow();

			$cat_desc = $cat['description'];
			$report_title[] = "Category: ".$cat_desc;
			
			$con->sql_query("select description from vendor where id = ".mi($_REQUEST['vendors']));
			$vd = $con->sql_fetchrow();
			$vd_name = $vd['description'];
			
			$vd_name = ($_REQUEST['vendors']) ? $vd_name : "All";
			$report_title[] = "Vendor: ".$vd_name;

			$use_grn = ($_REQUEST['use_grn']) ? "Yes" : "No";
			$report_title[] = "Use GRN: ".$use_grn;

			$con->sql_query("select description from brand where id = ".mi($_REQUEST['brands']));
			$brand = $con->sql_fetchrow();
			$brand_name = $brand['description'];

			$brand_name = ($_REQUEST['brands']) ? $brand_name : "All";
			$report_title[] = "Brand: ".$brand_name;

			$sku_type = ($_REQUEST['sku_type']) ? $_REQUEST['sku_type'] : "All";
			$report_title[] = "SKU Type: ".$sku_type;

			$price_type = ($_REQUEST['price_type']) ? $_REQUEST['price_type'] : "All";
			$report_title[] = "Price Type: ".$price_type;
			
			if (isset($_REQUEST['filter_by_date']) && !empty($_REQUEST['filter_by_date'])) {
				$title_df = (isset($_REQUEST['sku_date_from']) && !empty($_REQUEST['sku_date_from'])) ? $_REQUEST['sku_date_from']:'-';
				$title_dt = (isset($_REQUEST['sku_date_to']) && !empty($_REQUEST['sku_date_to'])) ? $_REQUEST['sku_date_to']:'-';
				$report_title[] = "SKU Date From: $title_df &nbsp;&nbsp; To: $title_dt";
			}
			
			if ($_REQUEST['launch_date_from'] || $_REQUEST['launch_date_to']) {
				$title_df = (isset($_REQUEST['sku_date_from']) && !empty($_REQUEST['sku_date_from'])) ? $_REQUEST['sku_date_from']:'-';
				$title_dt = (isset($_REQUEST['sku_date_to']) && !empty($_REQUEST['sku_date_to'])) ? $_REQUEST['sku_date_to']:'-';
				if($_REQUEST['launch_date_from'] && $_REQUEST['launch_date_to'])
					$report_title[] = "Launch Date From: $_REQUEST[launch_date_from] &nbsp;&nbsp; To: $_REQUEST[launch_date_to]";
				elseif($_REQUEST['launch_date_from'] && !$_REQUEST['launch_date_to'])
					$report_title[] = "Launch Date Start From: $_REQUEST[launch_date_from]";
				else
					$report_title[] = "Launch Date End Before: $_REQUEST[launch_date_to]";
			}
		}
		
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));

		$smarty->assign('prev', $this->prev_key);
		$smarty->assign('next', $this->next_key);
		$smarty->assign('page_no', $this->page_no);
		$smarty->assign('table', $this->table);
		$this->default_values();
		
		/*
		echo '<pre>';
		print_r(count($this->table));
		print_r($this->table));
		echo '</pre>';
		*/
		
	}
	
	function getSKU_item_id_list(){
		global $con,$smarty,$sessioninfo,$config;

		$where = array();
		$use_grn = (isset($_REQUEST['use_grn']) && $_REQUEST['use_grn'] != '') ? 1 : 0;
		$use_sku_group = (isset($_REQUEST['sku_group']) && $_REQUEST['sku_group'] != '') ? 1 : 0;
		
		$date_filter = '';
		if (isset($_REQUEST['filter_by_date']) && !empty($_REQUEST['filter_by_date'])) {
			$sku_date_from = $_REQUEST['sku_date_from'];
			$sku_date_to = $_REQUEST['sku_date_to'];
			if ($sku_date_from) $df[] = " si.added >= ".ms($sku_date_from." 00:00:00");
			if ($sku_date_to) $df[] = " si.added <= ".ms($sku_date_to."  23:59:59");
		}
		if ($df) $date_filter .= ' and '.join(' and ', $df);
		
		//2 options for filtering: SKU Group or Optional filters
    	if($use_sku_group){
			$sku_group = $_REQUEST['sku_group'];
			
			// split the SKU group since it has the multiple IDs
			$temp = explode('|' , $sku_group);
	
			$sku_group_id = $temp[0];
			$branch_id = $temp[1];
			$user_id = $temp[2];
			
			$sql = "select si.id as sku_item_id,si.sku_item_code,si.description 
					from sku_group_item s 
					left join sku_items si using(sku_item_code)
					left join sku on sku.id = si.sku_id
					left join category_cache cc on cc.category_id=sku.category_id
					where sku_group_id=$sku_group_id and branch_id=$branch_id and user_id=$user_id and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";//print $sql;

			$smarty->assign('sku_group_id',$sku_group_id);
			$smarty->assign('branch_id',$branch_id);
			$smarty->assign('user_id',$user_id);
		
			$con->sql_query("select description 
							 from sku_group 
							 where sku_group_id=$sku_group_id and branch_id=$branch_id and user_id=$user_id") or die(mysql_error());
			$temp = $con->sql_fetchrow();

			$this->sku_group_desc = $temp[0];
			
		}else{
			// this is when the user is from HQ
			
			foreach($config['sku_extra_info'] as $col=>$f){
				if($f['description'] == "Launch Date"){
					$launch_date = "sei.".$col;
				}
			}
			
			$bid = 0;
			if(BRANCH_CODE=='HQ'){
				if(isset($_REQUEST['branch_id']) && $_REQUEST['branch_id'] != ''){
					if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
						list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
						$con->sql_query("select group_concat(branch_id) as bg_id from branch_group_items where branch_group_id = $bg_id");
						$bg = $con->sql_fetchrow();
						$sub_where = "siph.branch_id in (".$bg['bg_id'].") and ";
						$with_grn = " and branch_id in (".$bg['bg_id'].")";
					}else{	// is a particular branch
						$sub_where = "siph.branch_id = ".mi($_REQUEST['branch_id']);
						$with_grn = " and vsh.branch_id = ".mi($_REQUEST['branch_id']);
						$bid = $_REQUEST['branch_id'];
					}
				}
			}else{	// else it is from its own branch
				$sub_where = "siph.branch_id = ".mi($sessioninfo['branch_id']);
				$with_grn = " and vsh.branch_id = ".mi($sessioninfo['branch_id']);
				$bid = $sessioninfo['branch_id'];
			}
			
			// check if the level is lower than 9999 then do the following action
			if ($sessioninfo['level']<9999)
				$where[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";
			
			// filter by optional
			if(isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != ''){
				$cat_info = get_category_info($_REQUEST['category_id']);
				$where[] = "cc.p".mi($cat_info['level'])."=".mi($_REQUEST['category_id']);
				//$where[] = "(c.id = ".mi($_REQUEST['category_id'])." or c.tree_str like ".ms('%('.$_REQUEST['category_id'].')%').")";
			}if(isset($_REQUEST['vendors']) && $_REQUEST['vendors'] != ''){
				$vendor_id = mi($_REQUEST['vendors']);
				if(!$use_grn){	// no use grn
					$where[] = "sku.vendor_id = ".mi($vendor_id);
				}
			}if(isset($_REQUEST['brands']) && $_REQUEST['brands'] != ''){
				$where[] = "sku.brand_id = ".ms($_REQUEST['brands']);
			}if(isset($_REQUEST['sku_type']) && $_REQUEST['sku_type'] != ''){
				$where[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
			}if(isset($_REQUEST['price_type']) && $_REQUEST['price_type'] != ''){
				$sub_query = ", ifnull((select trade_discount_code 
								 		from sku_items_price_history siph 
										where $sub_where
										siph.sku_item_id = sku_items.id 
										and siph.added between ".ms($this->date_from)." and ".ms($this->date_to_timestamp)."
										order by added desc limit 1)
										, sku.default_trade_discount_code) as price_type";
			
				$having = "having price_type = ".ms($_REQUEST['price_type']);
			}if((isset($_REQUEST['launch_date_from']) && $_REQUEST['launch_date_from'] != "") || (isset($_REQUEST['launch_date_to']) && $_REQUEST['launch_date_to'] != "")){
				if($_REQUEST['launch_date_from']) $where[] = $launch_date." >= ".ms($_REQUEST['launch_date_from']);
				if($_REQUEST['launch_date_to']) $where[] = $launch_date." <= ".ms($_REQUEST['launch_date_to']);
			}
			
			// set limit the query output to show in 100 records maximum per page
			if (isset($_REQUEST['sz']))
				$sz = intval($_REQUEST['sz']);
			else
				$sz = 100;
			if (isset($_REQUEST['s'])){
				$pg_start = intval($_REQUEST['s']);	// display only 100 per page
			}else{
				$pg_start = 0;
			}
			
			
			$vid = mi($_REQUEST['vendors']);
			if($use_grn && $vid && $bid>0){
				// select those sku of this grn vendor between this date
				/*$vsh_filter = array();
				$vsh_filter[] = "vsh.branch_id=".mi($_REQUEST['branch_id'])." and vsh.source='grn'";
				$vsh_filter[] = "vsh.added between ".ms($this->date_from)." and ".ms($this->date_to_timestamp);
				$vsh_filter[] = "vsh.vendor_id=".mi($vendor_id);
				$vsh_filter = join(' and ', $vsh_filter);
				
				$sql = "select distinct(sku_item_id) as sid
				from vendor_sku_history vsh 
				left join sku_items si on si.id=vsh.sku_item_id
				left join sku on si.sku_id = sku.id
				left join category_cache cc on cc.category_id=sku.category_id
				left join category c on c.id=sku.category_id
				where $where and $vsh_filter";
				$con->sql_query($sql) or die(mysql_error());
				$grn_sid_list = array();
				while($r = $con->sql_fetchassoc()){
					$grn_sid_list[] = mi($r['sid']);
				}
				$con->sql_freeresult();
				
				// find items that we receive by
				$ven_sql = ",(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=si.id and vsh.branch_id=".mi($_REQUEST['branch_id'])." and vsh.added <= ".ms($this->date_from)." order by vsh.branch_id, vsh.sku_item_id, vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id";*/
				
				$where[] = "si.id in (select vsh.sku_item_id from vendor_sku_history_b".mi($bid)." vsh where vendor_id=".mi($vid)." and (".ms($this->date_from)." between vsh.from_date and vsh.to_date or ".ms($this->date_to)." between vsh.from_date and vsh.to_date or vsh.from_date between ".ms($this->date_from)." and ".ms($this->date_to)."))";
			}
			
			$where[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$where = join(" and ", $where);
			
			$sql = "select si.id as sku_item_id, si.sku_item_code, si.description, si.added, $launch_date as launch_date $sub_query $ven_sql
						from sku_items si
						left join sku on sku.id = si.sku_id 
						left join category c on c.id = sku.category_id
						left join category_cache cc on cc.category_id=sku.category_id
						left join sku_extra_info sei on sei.sku_item_id = si.id
						where $where 
						$date_filter 
						group by si.id 
						$having 
						order by si.id";//print "$sql<br /><br />";
		}
		
		//print $sql.'<br /><br />';
		$sku_item_list = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($sku_item_list)){
			/*if(!$use_sku_group && $use_grn){
				if(($r['last_grn_vendor_id'] != $vendor_id) && !in_array($r['sku_item_id'], $grn_sid_list)){
					if(!$config['use_grn_last_vendor_include_master']){
						continue;
					}elseif($r['master_vendor_id'] != $vendor_id){
						continue;
					}
				}
			}*/
			$sku_id_list[] = $r['sku_item_id'];
			
			$this->sku_item_code[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			$this->sku_item_code[$r['sku_item_id']]['description'] = $r['description'];
			$this->sku_item_code[$r['sku_item_id']]['added'] = $r['added'];
			$this->sku_item_code[$r['sku_item_id']]['launch_date'] = $r['launch_date'];
		}
		
		
		//echo '<pre>';
		//print_r($sku_id_list);
		//echo '</pre>';
		
		return $sku_id_list;
	}
	
	function process_form()
	{
		global $con,$smarty,$sessioninfo;
		
		if(isset($_REQUEST['filter_by_date']) && !empty($_REQUEST['filter_by_date'])){
			if(empty($_REQUEST['sku_date_from']) && empty($_REQUEST['sku_date_to'])){
                $this->err[] = "Please select at least one of SKU Added Date from / to";
			}
			elseif(!empty($_REQUEST['sku_date_from']) && !empty($_REQUEST['sku_date_to'])){
				if (strtotime($_REQUEST['sku_date_from']) > strtotime($_REQUEST['sku_date_to']))
					$this->err[] = "SKU Added Date-To must be older than Date-From";
			}
		}
		
		if(!empty($_REQUEST['launch_date_from']) && !empty($_REQUEST['launch_date_to'])){
			if (strtotime($_REQUEST['launch_date_from']) > strtotime($_REQUEST['launch_date_to']))
				$this->err[] = "Date End from Launch Date must be older than Date Start";
		}
		
		// call parent
		parent::process_form();

		$this->year = $_REQUEST['year'];
		$this->month = $_REQUEST['month'];
		$this->stock_age = $_REQUEST['stock_age'];

		// set previous and next variables
		$this->prev = $_REQUEST['prev'];
		$this->next = $_REQUEST['next'];
		$this->time_id = $_REQUEST['time_id'];
		if(!$_REQUEST['pagination']){
			unset($_REQUEST['page_no']);
			unset($_SESSION['stock_aging'][$sessioninfo['id']][$this->time_id]);
		}
		$this->page_no = $_REQUEST['page_no'];

		$this->lastdayofmonth = $this->daysinmonth($this->year,$this->month);
		$this->date_from = ("$this->year-$this->month-1");
		$this->date_to = ("$this->year-$this->month-".$this->lastdayofmonth);
		$this->date_to_timestamp = date('Y-m-d', strtotime("+1 day", strtotime($this->date_to)));

		$this->sku_item_list = $this->getSKU_item_id_list();

		$qty_sold_where = "and sisc.date <= ".ms($this->date_to);
		
		if(isset($_REQUEST['output_excel'])){
			$this->excel_fmt = 1;
			$smarty->assign("print_excel", '1');
		}

		$this->sa_where = $sa_where;
		$this->sa_having = $sa_having;
		$this->qty_sold_where = $qty_sold_where;
		$smarty->assign('cat_desc',$cat_desc);
	}
	
	// return last day of a month
	function daysinmonth($year,$month){
		return date("j",mktime(0,0,0,$month+1,0, $year));	
	}
	
	function load_branch_data(){
		global $con,$smarty;
		
		$con->sql_query("select id,code,description from branch order by sequence, code");
	
		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
	
		$smarty->assign("branches1", $branches);
	}
	
	function default_values(){
		global $con, $smarty, $sessioninfo;
		//show vendor option
		$vd = ($sessioninfo['vendors']) ? "id in (".join(",",array_keys($sessioninfo['vendors'])).") and" : "";
		$con->sql_query("select id, description from vendor where $vd active order by description") or die(mysql_error());
		$smarty->assign("vendor", $con->sql_fetchrowset());

		//show price type option
		$con->sql_query("select code as type from trade_discount_type order by code") or die (mysql_error());
		$smarty->assign("price_type", $con->sql_fetchrowset());

		// show brand option
		$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
		$con->sql_query("select id, description from brand where $br active order by description") or die(mysql_error());
		$smarty->assign("brand", $con->sql_fetchrowset());

		// set the stock age
		$sa_desc = array(0 => ' All ', 2 => '2 Months', 3 => '3 Months', 4 => '4 Months', 5 => '5 Months', 6 => '6 Months');
		$sa_val = array(0 => 0,  2 => 1,  3 => 2,  4 => 3,  5 => 4,  6 => 5);
		$smarty->assign('sa_desc', $sa_desc);
		$smarty->assign('sa_val', $sa_val);

		if (!$_REQUEST['month']) $_REQUEST['month'] = date('m');
		if (!$_REQUEST['year']) $_REQUEST['year'] = date('Y');
		if (!$_REQUEST['time_id']) $_REQUEST['time_id'] = time();
		if (!$_REQUEST['page_no']) $_REQUEST['page_no'] = 1;
	}
	
	function __construct($title, $template='')
	{
		$this->default_values();
		parent::__construct($title, "../templates/mizisport/report.stock_aging.tpl");
	}
}

$report = new Stock_Aging_Report('Stock Aging Report');
?>
