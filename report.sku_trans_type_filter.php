<?php
/*
2/24/2020 9:26 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '1024M');
set_time_limit(0);

class Sku_Transaction_Type extends Module{
	function __construct($title){
		global $con, $smarty, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		//branch
		if (BRANCH_CODE == 'HQ'){
			$branches=array();
			$q1 = $con_multi->sql_query("select id, code from branch where active=1 order by sequence, code") or die(mysql_error());
			
			while($r = $con_multi->sql_fetchassoc($q1)){
				if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
				$branches[$r['id']] = $r;
			}
			$con_multi->sql_freeresult($q1);
			$smarty->assign('branch',$branches);
			$this->branches = $branches;
		}
		
		// sku type
		$con_multi->sql_query("select * from sku_type") or die(mysql_error());
		$smarty->assign("sku_type", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		//show date
		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month +1 day"));
		
		//Transaction type
		$transaction_type = array();
		$transaction_type['do'] = "DO";
		$transaction_type['gra'] = "GRA";
		$transaction_type['grn'] = "GRN";
		$transaction_type['pos'] = "POS";
		$transaction_type['adj'] = "Adjustment";
		$transaction_type['sc'] = "Stock Take";
		$smarty->assign("transaction_type", $transaction_type);
		$this->transaction_type = $transaction_type;
		
		$smarty->assign("PAGE_TITLE", $title);
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
		exit;
	}
	
	function show_report(){
		global $sessioninfo, $smarty, $con, $config, $con_multi;
		$form = $_REQUEST;
		$report_header = array();
		$where = array();
		$err = array();
		
		//error checking
		if($form['to']<$form['from'])  $err[] = "Invalid Date Range";
		$limit_date = strtotime("+1 year", strtotime($form['from']));
		if (strtotime($form['to']) >= $limit_date){
			$err[] = "Maximum Only Show 1 year Transaction Data.";
		}
		
		$report_header[] = "Date: $form[from] to $form[to]";
		$from_date=$form['from'];
		$to_date=$form['to'];
		$to_date_timestamp = date('Y-m-d', strtotime("+1 day", strtotime($to_date)));
		
		//category
		if($form['all_category']){
			$report_header[] = "Category: All";
		}elseif($form['category_id'] > 0){
			$con_multi->sql_query("select category_cache.*, category.level,category.description as cname from category_cache left join category on category_id = category.id where category_id=".mi($form['category_id']));
			$ccache = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			if (!$ccache) die("Error: Please regenerate category_cache (Masterfile -> Category).");
			$where[] = " ccache.p$ccache[level] = ".mi($_REQUEST['category_id']);
		}else{
			$err[] = "Invalid Category";
		}
		
		//Branch filter
		if($form['branch_id']){
			$branches =array();
			$bid = $form['branch_id'];
			$branches = $this->branches;
			$report_header[] = "Branch: ".$branches[$bid]['code'];
		}else{
			$bid = $sessioninfo['branch_id'];
		}
		
		//Sku type
		if($form['sku_type']){
			$where[] = "sku.sku_type =".ms($form['sku_type']);
			$report_header[] = "SKU Type: ".$form['sku_type'];
		}else $report_header[] = "SKU Type: All";
		
		
		//Sku Status
		$where_active = 1;
		if($form['sku_status'] == 'all') $report_header[] = "SKU Status: All";
		else{
			$where_active = "si.active =".mi($form['sku_status']);
			$where[] = "si.active =".mi($form['sku_status']);
			if($form['sku_status'] == 1) $report_header[] = "SKU Status: Active";
			else $report_header[] = "SKU Status: Inactive";
		}
		
		//Bom Type
		if($form['is_bom']=='') $report_header[] = "BOM Type: All";
		else{
			$where[] = "sku.is_bom =".mi($form['is_bom']);
			if($form['is_bom'] == 1) $report_header[] = "BOM Type: Yes";
			else $report_header[] = "BOM Type: No";
		}
		
		//No Inventory
		if($form['no_inventory']=='') $report_header[] = "No Inventory: All";
		else{
			if($form['no_inventory']==1){
				$where[] = "((sku.no_inventory='yes' and ccache.no_inventory='yes') or sku.no_inventory='yes')";
				$report_header[] = "No Inventory: No";
			}else{
				$where[] = "((sku.no_inventory='inherit' and ccache.no_inventory='no') or sku.no_inventory='no')";
				$report_header[] = "No Inventory: Yes";
			}
		}
		
		//check transaction type
		if(!$form['with_trans_type'] && !$form['without_trans_type']){
			$err[] = "Must select at least one Transaction Type.";
		}
		
		//trans_type
		$transaction_type = array();
		$transaction_type = $this->transaction_type;
		if($form['with_trans_type']){
			$with_trans_type_title = array();
			foreach($form['with_trans_type'] as $key=>$type){
				$with_trans_type_title[] = $transaction_type[$type];
			}
			$report_header[] = "With Transaction Type: ".implode(", ",$with_trans_type_title);
			$report_header[] = "Condition: ".ucfirst($form['condition']);
		}
		
		if($form['without_trans_type']){
			$without_trans_type_title = array();
			foreach($form['without_trans_type'] as $key=>$type){
				$without_trans_type_title[] = $transaction_type[$type];
			}
			$report_header[] = "Without Transaction Type: ".implode(", ",$without_trans_type_title);
		}
		
		if($err){
			$smarty->assign("err", $err);
			$this->_default();
			exit;
		}
		
        list($y,$m,$d) = explode("-",$to_date);
        $tbl_sb_2 = "stock_balance_b".mi($bid)."_".mi($y);
		$prms = array();
		$prms['tbl'] = $tbl_sb_2;
		initial_branch_sb_table($prms);
		
		$where = join(" and ", $where);
		if (!$where) $where=1;
		
		$group_by_sku = false;
		if($form['group_by_sku']) $group_by_sku = true;
		
		// create temporary table
		$tmp_tbl_name = "tmp_sku_trans_type_filter_".time();
		$con_multi->sql_query("create temporary table $tmp_tbl_name (
			id int not null default 0 primary key,
			gra_qty double not null default 0,
			do_qty double not null default 0,
			grn_qty double not null default 0,
			pos_qty double not null default 0,
			sc_qty double not null default 0,
			adj_in double not null default 0,
			adj_out double not null default 0,
			sb_qty double not null default 0 ) ENGINE=innodb DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci");
			
		// insert all sku_item_id into temporary table
		if($group_by_sku){
			$str_id = "si.sku_id";
		}else{
			$str_id = "si.id";
		}
		$con_multi->sql_query("insert into $tmp_tbl_name (id) select distinct($str_id)
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join category_cache ccache on ccache.category_id=sku.category_id
			where $where");
		
		
		//GRA
		$q1 = $con_multi->sql_query("select gra_items.qty, $str_id as id, uom.fraction as uom_fraction
		from gra_items
		left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
		left join sku_items si on si.id=gra_items.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
		where gra.branch_id=$bid and return_timestamp between ".ms($from_date)." and ".ms($to_date_timestamp)." and gra.status=0 and gra.returned=1 and $where_active");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$qty = mf($r['qty']);
			if($group_by_sku && $r['uom_fraction']){
				$qty *= $r['uom_fraction'];
			}
			// Update GRA qty
			if($qty != 0){
				$con_multi->sql_query("update $tmp_tbl_name set gra_qty = gra_qty+ ".mf($qty)." where id=".mi($r['id']));
			}
		}
		$con_multi->sql_freeresult($q1);
		
		
		//Adjustment
		$q2 = $con_multi->sql_query("select $str_id as id, sum(qty) as qty, if(qty>=0,'p','n') as type, uom.fraction as uom_fraction
		from adjustment_items ai
		left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
		left join sku_items si on si.id=ai.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
		where ai.branch_id =$bid and adjustment_date between ".ms($from_date)." and ".ms($to_date)." and adj.approved=1 and adj.status<2 and $where_active
		group by type, si.id");
		while($r2 = $con_multi->sql_fetchassoc($q2)){
			if($r2['type']=='p'){
				$qty = mf($r2['qty']);
				if($group_by_sku && $r2['uom_fraction']){
					$qty *= $r2['uom_fraction'];
				}
				if($qty != 0){
					$con_multi->sql_query("update $tmp_tbl_name set adj_in = adj_in+".mf($qty)." where id=".mi($r2['id']));
				}
			}
			elseif($r2['type']=='n'){
				$qty2 = abs(mf($r2['qty']));
				if($group_by_sku && $r2['uom_fraction']){
					$qty2 *= $r2['uom_fraction'];
				}
				if($qty2 != 0){
					$con_multi->sql_query("update $tmp_tbl_name set adj_out = adj_out+".abs(mf($qty2))." where id=".mi($r2['id']));
				}
			}
		}
		$con_multi->sql_freeresult($q2);
		
		
		//DO
		$q3 = $con_multi->sql_query("select $str_id as id, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, uom2.fraction as uom_fraction
		from do_items
		left join uom on do_items.uom_id=uom.id
		left join do on do_id = do.id and do_items.branch_id = do.branch_id
		left join sku_items si on si.id=do_items.sku_item_id 
		left join uom uom2 on uom2.id=si.packing_uom_id
		join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
		where do_items.branch_id=$bid and do_date between ".ms($from_date)." and ".ms($to_date)." and do.approved=1 and do.checkout=1 and do.status<2 and $where_active group by si.id");
		while($r3 = $con_multi->sql_fetchassoc($q3)){
			$qty = mf($r3['qty']);
			if($group_by_sku && $r3['uom_fraction']){
				$qty *= $r3['uom_fraction'];
			}
			if($qty != 0){
				$con_multi->sql_query("update $tmp_tbl_name set do_qty = do_qty+".mf($qty)." where id=".mi($r3['id']));
			}
		}
		$con_multi->sql_freeresult($q3);
		
		
		//POS
		$tbl="sku_items_sales_cache_b".$bid;
		$q4 = $con_multi->sql_query("select $str_id as id, sum(qty) as qty, uom.fraction as uom_fraction
				from $tbl pos
				left join sku_items si on si.id=pos.sku_item_id
				left join uom on uom.id=si.packing_uom_id
				join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
				where date between ".ms($from_date)." and ".ms($to_date)." and $where_active group by si.id");
		while($r4 = $con_multi->sql_fetchassoc($q4)){
			$qty = mf($r4['qty']);
			if($group_by_sku && $r4['uom_fraction']){
				$qty *= $r4['uom_fraction'];
			}
			if($qty != 0){
				$con_multi->sql_query("update $tmp_tbl_name set pos_qty = pos_qty+".mf($qty)." where id=".mi($r4['id']));
			}
		}
		$con_multi->sql_freeresult($q4);
		
		
		//GRN
		$q5 = $con_multi->sql_query("select $str_id as id,
		sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, uom.fraction as uom_fraction
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
		left join sku_items si on si.id=grn_items.sku_item_id
		left join uom on uom.id=si.packing_uom_id
		join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
		where grn.branch_id=$bid and rcv_date between ".ms($from_date)." and ".ms($to_date)." and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1 and $where_active
		group by si.id");
		while($r5 = $con_multi->sql_fetchassoc($q5)){
			$qty = mf($r5['qty']);
			if($group_by_sku && $r5['uom_fraction']){
				$qty *= $r5['uom_fraction'];
			}
			if($qty != 0){
				$con_multi->sql_query("update $tmp_tbl_name set grn_qty = grn_qty+".mf($qty)." where id=".mi($r5['id']));
			}
		}
		$con_multi->sql_freeresult($q5);
		
		
		//stock check
		$get_max_date = $con_multi->sql_query("select max(sc.date) as max_date, si.id as sid, si.sku_item_code from stock_check sc 
				left join sku_items si on sc.sku_item_code=si.sku_item_code
				join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
				where branch_id=$bid and sc.date between ".ms($from_date)." and ".ms($to_date)." and $where_active group by si.id");
		while($r = $con_multi->sql_fetchassoc($get_max_date)){
			$arr_max_date[$r['max_date']][$r['sid']] = $r['sid'];
		}
		$con_multi->sql_freeresult($get_max_date);
		if ($arr_max_date){
			foreach ($arr_max_date as $max_date => $arr_sids){
				$filter_sids= " si.id in (".join(",",$arr_sids).")";
				$q6 = $con_multi->sql_query("select $str_id as id, sc.qty as qty
						from stock_check sc
						left join sku_items si on sc.sku_item_code=si.sku_item_code
						where sc.branch_id=$bid and sc.date =".ms($max_date)." and $filter_sids and $where_active");
				while($r6 = $con_multi->sql_fetchassoc($q6)){
					$qty = mf($r6['qty']);
					if($qty != 0){
						$con_multi->sql_query("update $tmp_tbl_name set sc_qty = sc_qty+".mf($qty)." where id=".mi($r6['id']));
					}
				}
				$con_multi->sql_freeresult($q6);
			}
		}
		
		//stock balance
		$q7 = $con_multi->sql_query("select $str_id as id, sb2.qty as qty, uom.fraction as uom_fraction
			from sku_items si 
			left join uom on si.packing_uom_id = uom.id
			join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
			left join $tbl_sb_2 sb2 on sb2.sku_item_id=si.id and ((".ms($to_date)." between sb2.from_date and sb2.to_date))
			and $where_active order by si.id");
		while($r7 = $con_multi->sql_fetchassoc($q7)){
			$qty = mf($r7['qty']);
			if($group_by_sku && $r7['uom_fraction']){
				$qty *= $r7['uom_fraction'];
			}
			if($qty != 0){
				$con_multi->sql_query("update $tmp_tbl_name set sb_qty = sb_qty+".mf($qty)." where id=".mi($r7['id']));
			}
		}
		$con_multi->sql_freeresult($q7);
		
		
		//filter by transaction type
		$filter = array();
		if($form['with_trans_type']){
			$condition = $form['condition'];
			foreach($form['with_trans_type'] as $trans_type=>$val){
				if($trans_type != 'adj') $filter[] = "tmp_si.".$trans_type."_qty <> '' ";
				else $filter[] = "(tmp_si.".$trans_type."_in <> ''  or "."tmp_si.".$trans_type."_out <> '')";
			}
			$filter = join(" ".$condition." ", $filter);
		}
		if($form['without_trans_type']){
			foreach($form['without_trans_type'] as $trans_type=>$val){
				if($trans_type != 'adj') $filter2[] = "tmp_si.".$trans_type."_qty = '' ";
				else $filter2[] = "tmp_si.".$trans_type."_in = ''  and "."tmp_si.".$trans_type."_out = '' ";
			}
			$filter2 = join(" and ", $filter2);
		}
		if (!$filter) $filter=1;
		if (!$filter2) $filter2=1;

		
		$is_parent = 1;
		if($group_by_sku) $is_parent = "si.is_parent=1";
		$q_r = $con_multi->sql_query($qry="select tmp_si.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, sku.is_bom
			from sku_items si 
			join $tmp_tbl_name tmp_si on tmp_si.id=$str_id
			left join sku on si.sku_id = sku.id
			where ($filter) and $filter2 and $is_parent group by $str_id order by $str_id");
		while($r = $con_multi->sql_fetchassoc($q_r)){
			$table[] = $r;
			//calculate total
			$total['sb_qty'] += $r['sb_qty'];
			$total['sc_qty'] += $r['sc_qty'];
			$total['do_qty'] += $r['do_qty'];
			$total['grn_qty'] += $r['grn_qty'];
			$total['adj_in'] += $r['adj_in'];
			$total['adj_out'] += $r['adj_out'];
			$total['gra_qty'] += $r['gra_qty'];
			$total['pos_qty'] += $r['pos_qty'];
		}
		$con_multi->sql_freeresult($q_r);
		
		$smarty->assign("report_header",join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_header));
		$smarty->assign("table",$table);
		$smarty->assign('total',$total);
		$this->display();
	}
	
	function output_excel()
	{
		global $smarty, $sessioninfo;
		
		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=sku_transaction_type_filter_'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();

		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export SKU Transaction Type Filter To Excel");
		exit;
	}
}
$Sku_Transaction_Type = new Sku_Transaction_Type("SKU Transaction Type Filter");
?>