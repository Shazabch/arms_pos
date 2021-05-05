<?php
/*
 *
12/14/2015 1:57 PM DingRen
- add Include 0 Qty SKU filter

3/5/2019 10:45 AM Andy
- Fixed filter by category bug.
- Fixed filter by sku bug.
- Rename "Stock Balance Detail by Day Report" to "Stock Balance Detail by SKU Report".
- Enhanced to have date range filter.

3/12/2019 6:03 PM Andy
- Fixed opening stock zero if the opening date is 1 Jan.

3/13/2019 11:55 AM Andy
- Enhanced to show * for item stock not up to date.
- Enhanced to have Stock Take Adjust.
- Fixed report should not show "No Inventory" SKU.
- Fixed report title "Date From / To" cannot show.

7/19/2019 2:57 PM William
- Enhanced branch filter can filter by "All".

2/24/2020 9:44 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class SB_DETAIL_BY_DAY extends Module{
    function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if (!$_REQUEST['date_from'] || !$_REQUEST['date_to']) $_REQUEST['date_from'] = $_REQUEST['date_to'] = date('Y-m-d');

		// load branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->load_branch_group();
		
		$q1 = $con_multi->sql_query("select * from sku_group order by code, description");
		$sku_group = $con_multi->sql_fetchrowset($q1);
		
		$con_multi->sql_freeresult($q1);
		$smarty->assign('sku_group', $sku_group);
		
		if(!$_REQUEST['branch_id']) $_REQUEST['branch_id'] = "";
		if(!$_REQUEST['date']) $_REQUEST['date'] = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
		
    	parent::__construct($title);
    }
	
	function _default(){
		global $smarty, $sessioninfo;
		
		if($_REQUEST['load_report']){
			$this->show_report();
			
			if($_REQUEST['output_excel']){
				include_once("include/excelwriter.php");
				$smarty->assign('no_header_footer', true);
				$filename = "sa_performance_".time().".xls";
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename='.$filename);

				print ExcelWriter::GetHeader();
				$this->display();
				print ExcelWriter::GetFooter();
			}
		}
		$this->display();
		exit;
	}

	function show_report(){
		if($this->process_form()){
			$this->generate_report();
		}
		
		//$this->display();
	}

	private function run_report($branch_id_list){
        global $con, $smarty, $sessioninfo, $con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/
		
		if($this->filter) $filter = " where ".join(" and ", $this->filter);
		
		$q1 = $con_multi->sql_query("select si.id as sku_item_id, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description
							   from sku_items si
							   left join sku on sku.id = si.sku_id
							   left join category_cache cc on cc.category_id = sku.category_id
							   $filter");
							   
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$sid = $r1['sku_item_id'];
			//if($this->sku_code_list){
			//	$sku_info['sku_item_code'] = $r1['sku_item_code'];
			//	$sku_info['description'] = $r1['description'];
			//	$this->category[] = $sku_info;
			//}
			//if($this->cat_filter){
			//	$q2 = $con->sql_query("select * from category_cache cc where ".join(" and ", $this->cat_filter));
			//	if($con->sql_numrows($q2) == 0) continue;
			//}
		
			//Closing cost
			/*$sql = "select sich.date,sich.sku_item_id as sid, ifnull(sich.grn_cost,sku_items.cost_price) as grn_cost,
					(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=".mi($bid)." and sh.date <=".ms($this->date).") as stock_date
					from
					sku_items_cost_history sich
					left join sku_items on sich.sku_item_id = sku_items.id
					where branch_id=".mi($bid)." and sich.date <= ".ms($this->date)." and sich.date > 0 and sich.sku_item_id = ".mi($r1['sku_item_id'])."
					having stock_date=sich.date
					order by null";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
			    $sid = $r2['sid'];
	            $sku[$sid]['closing_cost'] = $r2['grn_cost'];
			}
			$con_multi->sql_freeresult($q2);*/
		  foreach($branch_id_list as $bid){
			// opening & closing stock balance
			$tbl_from = "stock_balance_b".$bid."_".$this->year_from;
			$tbl_to = "stock_balance_b".$bid."_".$this->year_to;
			$sql = "select ob.qty as ob_qty, cb.qty as cb_qty, sic.changed
					from sku_items si
					left join ".$tbl_from." ob on ob.sku_item_id=si.id and ".ms($this->opening_date)." between ob.from_date and ob.to_date
					left join ".$tbl_to." cb on cb.sku_item_id=si.id and ".ms($this->date_to)." between cb.from_date and cb.to_date
					left join sku on si.sku_id = sku.id
					left join category_cache using (category_id)
					left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
					where si.id = ".mi($sid);
			if(!$_REQUEST['include_0_sku']){
				$sql.=" and (ob.qty != 0 or cb.qty != 0)";
			}
			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->table[$sid]['ob_qty'] += $r2['ob_qty'];
				$this->table[$sid]['cb_qty'] += $r2['cb_qty'];
				if($r2['changed']){
					$this->table[$sid]['changed'] = 1;
				}
				
			}
			$con_multi->sql_freeresult($q2);
			
			// stock check for opening balance
			$sql = "select si.id as sid, sum(sc.qty) as qty, sc.date,
					sum(if(".ms($_REQUEST['hq_cost']).", si.hq_cost, sc.cost) * sc.qty) as cost
					from stock_check sc
					join sku_items si on si.sku_item_code=sc.sku_item_code
					where sc.branch_id=".mi($bid)." and sc.date = ".ms($this->date_from)." and si.id = ".mi($sid)."
					group by sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$sc_adj_qty = $r2['qty'] - $this->table[$sid]['ob_qty'];
				$this->table[$sid]['sc_adj_qty'] += $sc_adj_qty;
				$this->table[$sid]['ob_qty'] += $sc_adj_qty;
				$this->got_opening_sc = true;
			}
			$con_multi->sql_freeresult($q2);
			
			// GRN
			$sql = "select grn_items.sku_item_id as sid,
					sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
					sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
					(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
					(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
					if(grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)) as total_rcv_cost
					from grn_items
					left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
					left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
					left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
					where grn.branch_id=".mi($bid)." and grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to)." and grn_items.sku_item_id = ".mi($sid)." and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
					group by sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->table[$sid]['grn_qty'] += $r2['qty'];
				//$this->table[$sid]['grn_cost'] += $r2['total_rcv_cost'];
			}
			$con_multi->sql_freeresult($q2);
				
			//GRA
			$sql = "select
					gra_items.sku_item_id as sid,
					sum(qty) as qty, sum(qty * gra_items.cost) as total_gra_cost
					from gra_items
					left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
					where gra.branch_id=".mi($bid)." and gra.return_timestamp between ".ms($this->date_from)." and ".ms($this->date_to)." and gra_items.sku_item_id = ".mi($sid)." and gra.status=0 and gra.returned=1
					group by sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->table[$sid]['gra_qty'] += $r2['qty'];
				//$table[$sid]['gra_cost'] += $r2['total_gra_cost'];
			}
			$con_multi->sql_freeresult($q2);

			// POS
			$sql = "select
					si.id as sid,
					sum(qty) as qty,sum(pos.cost) as total_pos_cost
					from sku_items_sales_cache_b$bid pos
					left join sku_items si on si.id=pos.sku_item_id
					where pos.date between ".ms($this->date_from)." and ".ms($this->date_to)." and pos.sku_item_id = ".mi($sid)."
					group by sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->table[$sid]['pos_qty'] += $r2['qty'];
			}
			$con_multi->sql_freeresult($q2);
				
			// DO
			$sql = "select
					do_items.sku_item_id as sid,
					sum(do_items.ctn *uom.fraction + do_items.pcs) as qty
					from do_items
					left join uom on do_items.uom_id=uom.id
					left join do on do_id = do.id and do_items.branch_id = do.branch_id
					where do_items.branch_id=".mi($bid)." and do.do_date between ".ms($this->date_from)." and ".ms($this->date_to)." and do_items.sku_item_id = ".mi($sid)." and do.approved=1 and do.checkout=1 and do.status<2
					group by sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->table[$sid]['do_qty'] += $r2['qty'];
				//$this->table[$sid]['do_cost'] += $r2['qty'] * $sku[$sid]['closing_cost'];
			}
			$con_multi->sql_freeresult($q2);

			// ADJ
			$sql = "select
					ai.sku_item_id as sid,
					sum(qty) as qty,
					if(qty>=0,'p','n') as type
					from adjustment_items ai
					left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
					where ai.branch_id = ".mi($bid)." and adj.adjustment_date between ".ms($this->date_from)." and ".ms($this->date_to)." and ai.sku_item_id = ".mi($sid)." and adj.approved=1 and adj.status<2
					group by type, sid";

			$q2 = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q2)){
				if($r2['type']=='p'){
					$this->table[$sid]['adj_in_qty'] += $r2['qty'];
				}elseif($r2['type']=='n'){
					$this->table[$sid]['adj_out_qty'] += abs($r2['qty']);
				}
			}
			$con_multi->sql_freeresult($q2);
			
			// Stock Take Adjust
			$sql = "select sum(sc.qty) as qty, sc.date as d
					from stock_check sc
					join sku_items si on si.sku_item_code=sc.sku_item_code
					where sc.branch_id=".mi($bid)." and sc.date > ".ms($this->date_from)." and sc.date<=".ms($this->date_to)." and si.id = ".mi($sid)."
					group by d
					order by d desc";

			$q_sc = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($q_sc)){
				// Get Stock Take Date
				$stock_take_date = $r2['d'];
				
				// Get Closing Stock before stock take
				$minus_1_day = date("Y-m-d", strtotime("-1 day", strtotime($stock_take_date)));
				$sb_year = date("Y", strtotime($minus_1_day));
				$sb_tbl = "stock_balance_b$bid"."_".$sb_year;
				
				$q_sb = $con_multi->sql_query("select sb.qty
								from $sb_tbl sb
								where ".ms($minus_1_day)." between sb.from_date and sb.to_date 
								and sb.sku_item_id=$sid");
				$sb = $con_multi->sql_fetchassoc($q_sb);
				$con_multi->sql_freeresult($q_sb);
				
				$sc_adj_qty2 = $r2['qty'] - $sb['qty'];
				$this->table[$sid]['sc_adj_qty2'] += $sc_adj_qty2;
				// Only store last stock take qty
				if(!isset($this->table[$sid]['sc_date'])){
					$this->table[$sid]['sc_date'] = $r2['d'];
					$this->table[$sid]['sc_qty'] = $r2['qty'];
				}					
				
				$this->got_sc_adj = true;
			}
			
			if($this->table[$sid]){
				$this->table[$sid]['sku_item_code'] = $r1['sku_item_code'];
				$this->table[$sid]['mcode'] = $r1['mcode'];
				$this->table[$sid]['artno'] = $r1['artno'];
				$this->table[$sid]['description'] = $r1['description'];
			}
			$con_multi->sql_freeresult($q_sc);
		  }
		}

		//print_r($this->table);
		$con_multi->sql_freeresult($q1);
		//$con_multi->close_connection();
	}
	
    function generate_report(){
		global $con, $smarty, $con_multi;

		$this->table = array();
		$this->got_opening_sc = false;		
		$this->run_report($this->branch_id_list);
		
		$this->report_title[] = "Date From: ".$this->date_from." to ".$this->date_to;
		
		/*$q1 = $con->sql_query("select * from sku_group where sku_group_id = ".mi($this->sku_group_id));
		$sg = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$sku_group_desc = $sg['code']." - ".$sg['description'];
		$this->report_title[] = "SKU Group: ".$sku_group_desc;*/
		
		if($this->category_id){
			$con_multi->sql_query("select * from category where id = ".mi($this->category_id));
			$cate_info = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();
			$category_desc = $cate_info['code']." - ".$cate_info['description'];
		}else $category_desc = "All";
		$this->report_title[] = "Category: ".$category_desc;
		
        $smarty->assign("report_title", join("&nbsp;&nbsp;&nbsp;&nbsp;", $this->report_title));
		$smarty->assign("got_opening_sc", $this->got_opening_sc);
		//$smarty->assign("category", $this->category);
		$smarty->assign("table", $this->table);
		$smarty->assign("got_sc_adj", $this->got_sc_adj);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo, $appCore, $con_multi;

		//print_r($_REQUEST);

		$this->branch_id = $_REQUEST['branch_id'];
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->opening_date = date("Y-m-d", strtotime("-1 day", strtotime($this->date_from)));
		$this->year_from = date("Y", strtotime($this->opening_date));
		$this->year_to = date("Y", strtotime($this->date_to));
		//$this->sku_group_id = $_REQUEST['sku_group_id'];
		$this->category_id = $_REQUEST['category_id'];
		$this->all_category = $_REQUEST['all_category'];
		//$this->sku_code_list = $_REQUEST['sku_code_list_2'];

		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$this->branch_id_list = array(mi($_REQUEST['branch_id']));
			if($this->branch_id > 0){
				$this->report_title[] = "Branch: ".get_branch_code($this->branch_id);
			}else{
				$this->report_title[] = "Branch: All";
				$this->branch_id_list = array_keys($this->branches);
			}
		}else{  // Branches mode
			$this->branch_id_list = array(mi($sessioninfo['branch_id']));
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}
		
		// Check Date
		if(!$appCore->isValidDateFormat($this->date_from)){
			$err[] = "Invalid Date From";
		}
		if(!$appCore->isValidDateFormat($this->date_to)){
			$err[] = "Invalid Date To";
		}
		if(!$err){
			if(strtotime($this->date_to) < strtotime($this->date_from)){
				$err[] = "Date To cannot ealier than Date From";
			}
		}		
		
		$this->sid_list = array();
		if(isset($_REQUEST['sku_code_list'])){
			// select sku item id list
			$con_multi->sql_query("select * from sku_items where sku_item_code in (".join(",", array_map("ms", $_REQUEST['sku_code_list'])).")") or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
				$this->sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$con_multi->sql_freeresult();
			
			$this->filter[] = "si.id in (".join(',', $this->sid_list).")";
			$smarty->assign('group_item',$group_item);
			
			$this->report_title[] = "Filtered by Selected SKU";
		}

		if(!$this->all_category && (!$this->sid_list && !$this->category_id)) $err[] = "Please select a SKU";
		
		$this->filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		
		if($err){
			$smarty->assign("err", $err);
			//$this->display();
			return false;
		}
		
		if($this->category_id){
			//$this->filter[] = "sku.category_id = ".mi($this->category_id);
          	$con_multi->sql_query("select level from category where id=".mi($this->category_id));
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			/*// check one more level for grouping
			$con->sql_query("select max(level) from category");
			$max_level = $con->sql_fetchfield(0);
			if($level<$max_level)	$this->one_more_level = $level+1;
			else    $this->one_more_level = $level;*/
			
			$this->filter[] = "cc.p$level=".mi($this->category_id);
		}
		
		//$this->filter = array();
		//$this->filter[] = "tbl.date = ".ms($this->date);
		//if($this->sales_type) $this->filter[] = "ssc.sales_type = ".ms($this->sales_type);
		//if($this->department_id) $this->filter[] = "c.department_id = ".ms($this->sku_type);
		//if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);
		//if($this->sa_id) $this->filter[] = "sa.id = ".mi($this->sa_id);
		//parent::process_form();
		
		return true;
	}

	function load_branch_group($id=0){
		global $con,$smarty,$con_multi;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con_multi->sql_query("select * from branch_group $where",false,false);
		if($con_multi->sql_numrows()<=0) return;
		while($r = $con_multi->sql_fetchassoc()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con_multi->sql_fetchassoc()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult();
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
	}
	
	/*function load_sku_group(){
		global $con, $sessioninfo, $smarty;
		
		$q1 = $con->sql_query("select * from sku_group order by code, description");
		
		$sg = "<select name=\"sku_group_id\">";
		if($con->sql_numrows($q1) > 0){
			while($r = $con->sql_fetchassoc($q1)){
				$sg .= "<option value=".mi($r['sku_group_id']);
				if ($r['sku_group_id'] == $_REQUEST['sku_group_id']) $sg .= " selected";
				$sg .= ">".$r['code']." - ".$r['description']."</option>";
			}
		}else{
			$sg .= "<option value=\"\">No SKU Group Found</option>";
		}
		$sg .= "</select>";
		
		if(!$_REQUEST['ajax']) $smarty->assign("sku_group", $sg);
		else{
			print $sg;
			exit;
		}
	}*/
}

$SB_DETAIL_BY_DAY = new SB_DETAIL_BY_DAY('Stock Balance Detail by SKU Report');
?>
