<?php
/*
1/29/2021 10:10 AM William
- Bug fixed total sku qty not accurate.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '2048M');
class SKU_SALES_AND_PURCHASE_PROFIT_MARGIN extends Module{
	var $report_header = array();
	var $err = array();
	var $tmp_tbl = "tmp_sku_sales_purchase_profit_margin_";
	var $branch_id_list = array();
	var $group_by_sku = false;
	
	public function __construct($title){
		global $con_multi, $appCore, $smarty;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		//branches
		$con_multi->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		} 
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
	    if($this->branch_group)  return $this->branch_group;
		$this->branch_group = array();
		
		// load header
		$con_multi->sql_query("select * from branch_group");
		while($r = $con_multi->sql_fetchrow()){
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();		
		
		$con_multi->sql_query("select bgi.*,branch.code,branch.description 
			from branch_group_items bgi 
			left join branch on bgi.branch_id=branch.id 
			where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc()){
	        $this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branch_group['have_group'][$r['branch_id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branch_group',$this->branch_group);
		
		//years
		$q1 = $con_multi->sql_query("select year(min(date)) as min_year, year(max(date)) as max_year from pos where date>0");
		while ($r = $con_multi->sql_fetchassoc($q1)){
            $min_year = $r['min_year'];
            $max_year = $r['max_year'];
		}
		$con_multi->sql_freeresult($q1);
		
		$count_year = $max_year - $min_year;
		
		for($i=0; $i<=$count_year; $i++){
			$tmp_year = $min_year+$i;
			$year[$tmp_year][0] = $tmp_year;
			$year[$tmp_year]['year'] = $tmp_year;
		}
		
		// show year
		$q1 = $con_multi->sql_query("select year(min(do_date)) as min_year, year(max(do_date)) as max_year from do where do_date>0");
		while ($r = $con_multi->sql_fetchassoc($q1)){
            $min_year = $r['min_year'];
            $max_year = $r['max_year'];
		}
		$con_multi->sql_freeresult($q1);
		
		$count_year = $max_year - $min_year;
		
		for($i=0; $i<=$count_year; $i++){
			$tmp_year = $min_year+$i;
			if(!$year[$tmp_year]){
				$year[$tmp_year][0] = $tmp_year;
				$year[$tmp_year]['year'] = $tmp_year;
			}
		}
		
		ksort($year);
		$smarty->assign("years", $year);
		
		
		//month list
		$months = $appCore->monthsList;
		$smarty->assign('months', $months);
		
		// sku type
		$con_multi->sql_query("select * from sku_type where active=1");
		while ($r = $con_multi->sql_fetchassoc()){
			$this->sku_types[$r['code']] =$r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("sku_types", $this->sku_types);
		
		
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function _default(){
		global $smarty;
		
		if($_REQUEST['show_report']){
			$this->prepare_report_data();
			if(!$this->err){
				if($_REQUEST['export_excel']){
					$this->generate_excel();
				}else{
					$this->load_report();
				}
			}else{
				$smarty->assign('err', $this->err);
			}
		}
		$this->display();
	}
	
	private function prepare_report_data(){
		global $smarty, $config, $sessioninfo, $con_multi, $appCore;
		
		//$form = $_REQUEST;
		$filter = array();
		
		//year
		$y = mi($_REQUEST['year']);
		$this->report_header[]="Year: ".$y;
		
		//month
		$m = mi($_REQUEST['month']);
		$this->report_header[]="Month: ".$appCore->monthsList[$m];
		
		//branch
		if(BRANCH_CODE == 'HQ'){			
			if($_REQUEST['branch_id_list']){
				$branch_list = array();
				foreach($_REQUEST['branch_id_list'] as $bid){
					if(!isset($this->branches[$bid])){
						$this->err[] = "Branch ID#$bid is invalid";
					}else{
						$this->branch_id_list[$bid] = $bid;
						$branch_list[] = $this->branches[$bid]['code'];
					}
				}
				if($branch_list)   $this->report_header[] = "Branch: ".implode(", ",$branch_list);
			}else{
				$this->err[] = "Please select at least one branch.";
			}
		}else{
			$this->branch_id_list = array($sessioninfo['branch_id'] => $sessioninfo['branch_id']);
		}
		
		//category
		if($_REQUEST['all_category']){
			$this->report_header[] = "Category: All";
			$filter[] = "dept.department_id in ($sessioninfo[department_ids])";
		}elseif($_REQUEST['category_id'] > 0){
			$con_multi->sql_query("select category_cache.*, category.level,category.description as cname 
			from category_cache 
			left join category on category_id = category.id 
			where category_id=".mi($_REQUEST['category_id']));
			$ccache = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$filter[] = " ccache.p$ccache[level] = ".mi($_REQUEST['category_id']);
		}
		
		if($_REQUEST['group_by_sku']) $this->group_by_sku = true;
		
		if($this->group_by_sku)  $str_id = "si.sku_id";
		else  $str_id = "si.id";
		
		// sku list
		if($_REQUEST['sku_code_list']){
			$sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
			// select sku item id list
			$sid_list = array();
			$con_multi->sql_query("select $str_id as sid, si.* from sku_items si where si.sku_item_code in ($sku_code_list)");
			while($r = $con_multi->sql_fetchassoc()){
				$sid_list[] = mi($r['sid']);
				$group_item[] = $r;
			}
			$con_multi->sql_freeresult();
			
			// filter sku
			if($sid_list){
				if($this->group_by_sku)   $filter[] = "si.sku_id in (".join(',', $sid_list).")";
				else  $filter[] = "si.id in (".join(',', $sid_list).")";
			}
		}
		$smarty->assign('group_item', $group_item);
		
		if(!$_REQUEST['all_category'] && !$_REQUEST['category_id'] && !$sid_list){
			$this->err[] = "Please select at least one category or sku.";
		}
		
		if($this->err){
			$smarty->assign('err', $this->err);
			$this->display();
			exit;
		}
		
		//sku status
		$where_active = 1;
		if($_REQUEST['status'] == 'all')  $this->report_header[] = "SKU Status: All";
		else{
			$where_active = "si.active =".mi($_REQUEST['status']);
			$filter[] = "si.active =".mi($_REQUEST['status']);
			if($_REQUEST['status'] == 1)  $this->report_header[] = "SKU Status: Active";
			else $this->report_header[] = "Status: Inactive";
		}
		
		// sku type
		$sku_type = trim($_REQUEST['sku_type']);
		if($sku_type){
			$filter[] = "sku.sku_type=".ms($sku_type);
			$this->report_header[] = "SKU Type: ".$this->sku_types[$sku_type]['description'];
		}else{
			$this->report_header[] = "SKU Type: All";
		}
		
		$tmp_tbl_name = $this->tmp_tbl.time();
		$this->tmp_tbl = $tmp_tbl_name;
		$create_table_qry ="create temporary table if not exists $tmp_tbl_name(
			id int not null default 0,
			branch_id int not null default 0,
			opening_qty	double not null,
			opening_cost double not null,
			grn_qty double not null,
			grn_cost double not null,
			adj_in_qty double not null,
			adj_out_qty double not null,
			stock_take_adj_qty double not null,
			do_qty double not null,
			do_sales_amt double not null,
			pos_qty double not null,
			pos_sales_amt double not null,
			primary key (id, branch_id)
		) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
		$con_multi->sql_query($create_table_qry);

		
		if($filter){
			$where=implode(" and ", $filter);
		}
		if (!$where) $where=1;
		
		$from_date = $y."-".$m."-01";
		$to_date = date("Y-m-t",strtotime($from_date));
		
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$bid = mi($bid);
				$tbl_sb_from = "stock_balance_b".$bid."_".$y;
				
				$con_multi->sql_query("insert into $tmp_tbl_name (id, branch_id) 
				select distinct($str_id), $bid
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category_cache ccache on ccache.category_id=sku.category_id
				left join category dept on dept.id = ccache.category_id
				where $where and $where_active group by $str_id");
				
				
				//opening qty and cost
				$q1=$con_multi->sql_query($qry="select si.sku_item_code, $str_id as sid, ifnull(sb1.cost,
					si.cost_price) as opening_cost, sum(sb1.qty) as opening_qty, u1.fraction as uom_fraction
					from sku_items si
					left join sku on si.sku_id=sku.id
					left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$bid
					left join $tbl_sb_from sb1 on sb1.sku_item_id=si.id and ((".ms($from_date)." between sb1.from_date and sb1.to_date))
					left join category_cache ccache on ccache.category_id=sku.category_id
					left join category dept on dept.id = ccache.category_id
					left join uom u1 on u1.id=si.packing_uom_id
					where $where and $where_active
					group by si.id");
				while($r1 = $con_multi->sql_fetchassoc($q1)){
					if($r1['opening_qty'] || $r1['opening_cost']){
						$opening_qty = $r1['opening_qty'];
						$opening_cost = $r1['opening_cost'] * $opening_qty;
						if($this->group_by_sku){
							$opening_qty = $r1['opening_qty']* $r1['uom_fraction'];
						}
						$con_multi->sql_query("update $tmp_tbl_name set opening_qty=opening_qty+".mf($opening_qty).", opening_cost=opening_cost+".mf($opening_cost)." where branch_id=$bid and id=".mi($r1['sid']));
					}
				}
				$con_multi->sql_freeresult($q1);
				//echo $qry;
				
				//GRN
				$q2=$con_multi->sql_query("select $str_id as sid, uom2.fraction as uom_fraction,
					sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
					sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
					(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)), (grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) * if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)*if(grr.currency_rate<0,1,grr.currency_rate)) as total_rcv_cost
					from grn_items
					left join sku_items si on si.id = grn_items.sku_item_id
					left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
					left join uom uom2 on uom2.id = si.packing_uom_id
					left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
					join $tmp_tbl_name tmp_tbl on tmp_tbl.id=$str_id and tmp_tbl.branch_id=grn.branch_id
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					where grn.branch_id=$bid and rcv_date between ".ms($from_date)." and ".ms($to_date)."
					and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1 and $where_active
					group by sid, grn.branch_id");
				while($r2 = $con_multi->sql_fetchassoc($q2)){
					if($r2['qty'] || $r2['total_rcv_cost']){
						$grn_qty = $r2['qty'];
						if($this->group_by_sku){
							$grn_qty = $r2['uom_fraction']*$r2['qty'];
						}
						$grn_cost = $r2['total_rcv_cost'];
						$con_multi->sql_query("update $tmp_tbl_name set grn_qty=grn_qty+".mf($grn_qty).", grn_cost=grn_cost+".mf($grn_cost)." where branch_id=$bid and id=".mi($r2['sid']));
					}
				}
				$con_multi->sql_freeresult($q2);
				
				
				//ADJ
				$q3=$con_multi->sql_query("select $str_id as sid, sum(qty) as qty, if(qty>=0,'p','n') as type,
					uom.fraction as uom_fraction
					from adjustment_items ai
					left join sku_items si on si.id = ai.sku_item_id
					left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
					left join uom on uom.id=si.packing_uom_id
					join $tmp_tbl_name tmp_tbl on tmp_tbl.id=$str_id and tmp_tbl.branch_id=adj.branch_id
					where ai.branch_id =$bid and adjustment_date between ".ms($from_date)." and ".ms($to_date)." 
					and adj.approved=1 and adj.status=1 and adj.active=1 and $where_active
					group by type, sid");
				while($r3 = $con_multi->sql_fetchassoc($q3)){
					if($r3['type'] == 'p'){
						$qty = mf($r3['qty']);
						if($this->group_by_sku && $r3['uom_fraction']){
							$qty *= $r3['uom_fraction'];
						}
						if($qty != 0){
							$con_multi->sql_query("update $tmp_tbl_name set adj_in_qty=adj_in_qty+".mf($qty)." where id=".mi($r3['sid'])." and branch_id=$bid");
						}
					}
					elseif($r3['type']=='n'){
						$qty2 =mf($r3['qty']);
						if($this->group_by_sku && $r3['uom_fraction']){
							$qty2 *= $r3['uom_fraction'];
						}
						if($qty2 != 0){
							$con_multi->sql_query("update $tmp_tbl_name set adj_out_qty=adj_out_qty+".mf($qty2)." where id=".mi($r3['sid'])." and branch_id=$bid");
						}
					}
				}
				$con_multi->sql_freeresult($q3);
				
				
				// check if got stock take at opening	
				$q4=$con_multi->sql_query("select $str_id as sid, sc.cost as cost, sc.qty, sc.date
						from stock_check sc
						right join sku_items si on si.sku_item_code=sc.sku_item_code
						join $tmp_tbl_name tmp_tbl on tmp_tbl.id=$str_id and sc.branch_id=tmp_tbl.branch_id
						where sc.branch_id=$bid and sc.date between ".ms($from_date)." and ".ms($to_date)." and $where_active");
				$sc_balance_val = array();
				while($r4 = $con_multi->sql_fetchrow($q4)){
					$sc_balance_val[$r4['date']][$r4['sid']]['qty']+= $r4['qty'];
				}
				$con_multi->sql_freeresult($q4);
				
				// get adjustment qty and value before stock check
				if($sc_balance_val){
					foreach ($sc_balance_val as $sc_date => $tmp_balance_val_list){
						$minus_1_day=strtotime("-1 day",strtotime($sc_date));
						$sb_year=date("Y",$minus_1_day);
						$sc1day_date=ms(date("Y-m-d",$minus_1_day));
						$sb_tbl="stock_balance_b$bid"."_".$sb_year;
						$sql = "select $str_id as sid, sc.qty as qty, uom.fraction as uom_fraction
								from $sb_tbl sc
								right join sku_items si on si.id=sc.sku_item_id
								left join uom on uom.id=si.packing_uom_id
								where $sc1day_date between sc.from_date and sc.to_date and $where_active
								and $str_id in (".join(",", array_keys($tmp_balance_val_list)).")
								group by sid";
						
						$q5=$con_multi->sql_query($sql) or die(mysql_error());
						while($r5 = $con_multi->sql_fetchassoc($q5)){
							$sid = $r5['sid'];
							
							$sc_qty = $sc_balance_val[$sc_date][$sid]['qty'];
							$qty_b4_sc = $r5['qty'];
							
							$adj_qty = mf($sc_qty - $qty_b4_sc);
							if($this->group_by_sku){
								$adj_qty = mf($sc_qty - $qty_b4_sc) * $r5['uom_fraction'];
							}
							$stock_take_adjust_qty += $adj_qty;
							
							$con_multi->sql_query("update $tmp_tbl_name set stock_take_adj_qty=stock_take_adj_qty+".mf($stock_take_adjust_qty)." where id=".mi($r5['sid'])." and branch_id=$bid");
						}
						$con_multi->sql_freeresult($q5);	
					}
					unset($sc_balance_val);
				}
				
				
				//POS
				$si_sales_cache_tbl="sku_items_sales_cache_b".$bid;
				$q6=$con_multi->sql_query("select $str_id as sid, sum(qty) as qty, sum(pos.amount) as amount, uom.fraction as uom_fraction
					from $si_sales_cache_tbl pos
					left join sku_items si on si.id=pos.sku_item_id
					left join uom on uom.id=si.packing_uom_id
					join $tmp_tbl_name tmp_tbl on tmp_tbl.id=$str_id and tmp_tbl.branch_id=$bid
					where date between ".ms($from_date)." and ".ms($to_date)." and $where_active
					group by sid");
				while($r6 = $con_multi->sql_fetchassoc($q6)){
					if($r6['qty'] || $r6['amount']){
						$pos_qty = $r6['qty'];
						if($this->group_by_sku){
							$pos_qty = $r6['qty']*$r6['uom_fraction'];
						}
						$pos_sales_amt = $r6['amount'];
						$con_multi->sql_query("update $tmp_tbl_name set pos_qty=".mf($pos_qty).", pos_sales_amt=".mf($pos_sales_amt)." where branch_id=$bid and id=".mi($r6['sid']));
					}
				}
				$con_multi->sql_freeresult($q6);
				
				
				//DO
				$q7=$con_multi->sql_query("select $str_id as sid, sum(di.ctn *uom.fraction + di.pcs) as qty,
					sum(di.inv_line_gross_amt2) as amount, u2.fraction as uom_fraction
					from do_items di
					left join sku_items si on si.id = di.sku_item_id
					left join uom on di.uom_id=uom.id
					left join uom u2 on u2.id=si.packing_uom_id
					left join do on do_id = do.id and di.branch_id = do.branch_id
					join $tmp_tbl_name tmp_tbl on tmp_tbl.id=$str_id and tmp_tbl.branch_id = do.branch_id
					where di.branch_id=$bid and do_date between ".ms($from_date)." and ".ms($to_date)."
					and do.approved=1 and do.checkout=1 and do.status < 2  and $where_active
					group by sid");
				while($r7 = $con_multi->sql_fetchassoc($q7)){
					if($r7['qty'] || $r7['amount']){
						$do_qty = $r7['qty'];
						if($this->group_by_sku){
							$do_qty = $r7['qty']*$r7['uom_fraction'];
						}
						$do_sales_amt = $r7['amount'];
						$con_multi->sql_query("update $tmp_tbl_name set do_qty=".mf($do_qty).", do_sales_amt=".mf($do_sales_amt)." where branch_id=$bid and id=".mi($r7['sid']));
					}
				}
				$con_multi->sql_freeresult($q7);
			}
		}
		$smarty->assign('report_header', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_header));
	}
	
	private function load_report(){
		global $smarty, $config, $sessioninfo, $con_multi;
		
		if($this->group_by_sku){
			$str_id = "si.sku_id";
			$join = "left join sku_items si on $str_id = tmp_tbl.id and si.is_parent = 1";
		}else{
			$str_id = "si.id";
			$join = "left join sku_items si on $str_id = tmp_tbl.id";
		}
		
		$table = $total = array();
		$tmp_tbl_name = $this->tmp_tbl;
		
		$q1=$con_multi->sql_query("select tmp_tbl.id as id, si.sku_item_code, si.mcode, si.artno, 
		si.link_code, si.description, sum(tmp_tbl.opening_qty) as opening_qty, sum(tmp_tbl.opening_cost) as opening_cost, 
		sum(tmp_tbl.grn_qty) as grn_qty, sum(tmp_tbl.grn_cost) as grn_cost, sum(tmp_tbl.adj_in_qty) as adj_in_qty, 
		sum(tmp_tbl.adj_out_qty) as adj_out_qty, sum(tmp_tbl.stock_take_adj_qty) as stock_take_adj_qty,
		sum(tmp_tbl.do_qty) as do_qty, sum(tmp_tbl.do_sales_amt) as do_sales_amt, sum(tmp_tbl.pos_qty) as pos_qty,
		sum(tmp_tbl.pos_sales_amt) as pos_sales_amt
		from $tmp_tbl_name tmp_tbl
		$join
		where  
		(tmp_tbl.opening_qty <> 0 or tmp_tbl.opening_cost <> 0 or tmp_tbl.grn_qty <> 0 or tmp_tbl.grn_cost <> 0 
		or tmp_tbl.adj_in_qty <> 0 or tmp_tbl.adj_out_qty <> 0 or tmp_tbl.stock_take_adj_qty <> 0 
		or tmp_tbl.do_qty <> 0 or tmp_tbl.do_sales_amt <> 0 or tmp_tbl.pos_qty <> 0 or tmp_tbl.pos_sales_amt <> 0)
		group by id");
		while($r = $con_multi->sql_fetchassoc($q1)){
			//print_r($r);
			$sid = mi($r['id']);
			if($r['grn_cost'] != 0 && $r['grn_qty'] != 0){
				$r['grn_avg_cost'] = $r['grn_cost']/$r['grn_qty'];
			}
			$r['total_sku_qty'] = $r['opening_qty']+$r['grn_qty'] + $r['adj_in_qty'] + $r['adj_out_qty']+$r['stock_take_adj_qty'];
			$r['adj_out_qty'] = abs($r['adj_out_qty']);
			$r['total_sku_cost'] = $r['grn_cost'] + $r['opening_cost'];
			if($r['total_sku_cost'] != 0 && $r['total_sku_qty'] != 0){
				$r['avg_sku_cost'] = $r['total_sku_cost']/$r['total_sku_qty'];
			}
			$r['total_sales_qty'] = $r['do_qty']+ $r['pos_qty'];
			$r['total_sales_avg_cost'] = $r['total_sales_qty'] * $r['avg_sku_cost'];
			$r['total_sales'] = $r['do_sales_amt'] + $r['pos_sales_amt'];
			$r['gp'] = $r['total_sales'] - ($r['avg_sku_cost'] * $r['total_sales_qty']);
			if(($r['gp']*100) != 0 && $r['total_sales'] != 0){
				$r['gp_percent'] = (($r['gp']*100)/$r['total_sales']);
			}
			$table[$sid] = $r;
			
			//total
			$total['opening_qty'] += $r['opening_qty'];
			$total['opening_cost'] += $r['opening_cost'];
			$total['grn_qty'] += $r['grn_qty'];
			$total['grn_cost'] += $r['grn_cost'];
			$total['adj_in_qty'] += $r['adj_in_qty'];
			$total['adj_out_qty'] += $r['adj_out_qty'];
			$total['stock_take_adj_qty'] += $r['stock_take_adj_qty'];
			$total['do_qty'] += $r['do_qty'];
			$total['do_sales_amt'] += $r['do_sales_amt'];
			$total['pos_qty'] += $r['pos_qty'];
			$total['pos_sales_amt'] += $r['pos_sales_amt'];
			$total['total_sku_qty'] += $r['total_sku_qty'];
			$total['total_sku_cost'] += $r['total_sku_cost'];
			$total['total_sales_qty'] += $r['total_sales_qty'];
			$total['total_sales'] += $r['total_sales'];
			$total['gp'] += $r['gp'];
		}
		$con_multi->sql_freeresult($q1);
		
		if($total){
			if($total['grn_cost'] != 0 && $total['grn_qty'] != 0){
				$total['grn_avg_cost'] = $total['grn_cost']/$total['grn_qty'];
			}
			if($total['total_sku_cost'] != 0 && $total['total_sku_qty'] != 0){
				$total['avg_sku_cost'] = $total['total_sku_cost']/$total['total_sku_qty'];
			}
			$total['total_sales_avg_cost'] = $total['total_sales_qty'] * $total['avg_sku_cost'];
			if(($total['gp']*100) != 0 && $total['total_sales'] != 0){
				$total['gp_percent'] = (($total['gp']*100)/$total['total_sales']);
			}
		}
		
		$smarty->assign("total", $total);
		$smarty->assign("table", $table);
	}
	
	private function generate_excel(){
		global $smarty, $sessioninfo;
		
		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=sku_sales_purchase_profit_margin'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->load_report();
		$this->display();
		print ExcelWriter::GetFooter();
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "SKU Sales & Purchase Profit Margin Special Calculation Report");
		
		exit;
	}
}
$SKU_SALES_AND_PURCHASE_PROFIT_MARGIN = new SKU_SALES_AND_PURCHASE_PROFIT_MARGIN('SKU Sales & Purchase Profit Margin Special Calculation Report');
?>