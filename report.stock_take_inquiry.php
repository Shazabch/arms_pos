<?php
/*
7/16/2019 10:51 AM William
- Added new module "Stock Take Inquiry".

8/6/2019 9:29 AM William
- Fixed bug Cost Variance group by sku.

8/9/2019 9:55 AM William
- Fixed bug Cost Variance.

8/9/2019 10:17 AM William
- Fixed bug no inventory result can show when search.

2/18/2020 8:58 AM William
- Enhanced to change $con connection to use $con_multi.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_CHECK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");
class REPORT_STOCK_TAKE_INQUIRY extends Module{
	function __construct($title){
		global $con, $smarty, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		$this->init_data();
		parent::__construct($title);
	}
	
	function init_data(){
		global $con, $smarty, $con_multi;
		
		//branch
		$branches = array();
		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while ($r = $con_multi->sql_fetchassoc()) $branches[$r['id']] = $r;
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$branches);
		
		//sku items
		if($_REQUEST['sku_code_list_2']){
			$code_list = $_REQUEST['sku_code_list_2'];
			$list = explode(",",$code_list);
			for($i=0; $i<count($list); $i++){
				$con_multi->sql_query("select description from sku_items where sku_item_code=".ms($list[$i])) or die(sql_error());
				$temp = $con_multi->sql_fetchrow();
				$category[$list[$i]]['sku_item_code']=$list[$i];
				$category[$list[$i]]['description']=$temp['description'];
				$list[$i]="'".$list[$i]."'";
				$con_multi->sql_freeresult();
			}
			$smarty->assign('category',$category);
		}
	}
	
	function _default(){
		$this->display();
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=stock_take_inquiry_'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
		exit;
	}
	
	function show_report(){
		global $smarty, $sessioninfo, $config, $con, $con_multi;
		$form = $_REQUEST;
		$err = array();
		
		$branch_id = mi($form['branch_id']);
		$from = $form['from']; 
		$to = $form['to']; 
		$parent_child_filter = $form['parent_child_filter']; 
		$group_by_sku = $form['group_by_sku'];
		$smarty->assign("group_by_sku", $group_by_sku);
		
		//check error
		if(empty($_REQUEST['sku_code_list_2'])) $err[] = "Please Select SKU.";
		if(!$branch_id) $err[] = "Please Select Branch.";
		if($err){
			$smarty->assign('err',$err);
			$this->display();
			exit;
		}
		
		//sku list
		if (!empty($_REQUEST['sku_code_list_2'])) {
			$code_list = $_REQUEST['sku_code_list_2'];
			$list = explode(",",$code_list);
			foreach($list as $lkey=>$lval) $list[$lkey] = ms($lval);
			$list = join(',',$list);
			
				$q1 = $con_multi->sql_query($sql="select sku_id from sku_items where sku_item_code in($list)");
				while($r1 = $con_multi->sql_fetchassoc($q1)){
					$sku_id[] = join(',',$r1);
				}
				$con_multi->sql_freeresult($q1);
				$sku_id = join(',',$sku_id);
				$sku_id2 = "si.sku_id in ($sku_id)";
			
		}
		$table = array();

		if($form['group_by_sku']){
			$sql1="select si.* from stock_check sc
			left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			where (sc.date between ".ms($from)." and ".ms($to).") and  sc.branch_id=$branch_id and $sku_id2 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$result = $con_multi->sql_query($sql1);
			while($r2 = $con_multi->sql_fetchassoc($result)){
				$table[$r2['sku_id']]['sku_id'] = $r2['sku_id'];
			}
			$con_multi->sql_freeresult($result);
			$sql="select si.* from stock_check sc
			left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			where sc.branch_id=$branch_id and $sku_id2 and si.is_parent=1 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$result = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($result)){
			  if($r2['sku_id'] == $table[$r2['sku_id']]['sku_id']){
				$table[$r2['sku_id']]['sku_item_code'] = $r2['sku_item_code'];
				$table[$r2['sku_id']]['artno'] = $r2['artno'];
				$table[$r2['sku_id']]['mcode'] = $r2['mcode'];
				$table[$r2['sku_id']]['link_code'] = $r2['link_code'];
				$table[$r2['sku_id']]['description']= $r2['description'];
			  }
			}
			$con_multi->sql_freeresult($result);
			$sql="select sc.location,sc.sku_item_code,sc.shelf_no,sc.cost as sc_cost,
			sc.scanned_by,sc.date,sc.qty, si.id as sid,si.sku_id as sku_id,sc.selling as sc_selling_price,
			uom.fraction as uom_fraction from stock_check sc
			left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			left join uom on uom.id = si.packing_uom_id
			where (sc.date between ".ms($from)." and ".ms($to).") and sc.branch_id=$branch_id and $sku_id2 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$r1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($r1)){
				$table[$r['sku_id']]['stock_take'][$r['date']]['sku_item_code'] = $r['sku_item_code'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['date'] = $r['date'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['scanned_by'] = $r['scanned_by'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['selling_price'] = $r['selling_price'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['qty'] += $r['qty']*$r['uom_fraction'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['sc_cost'] = $r['sc_cost'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['qty2'] = $r['qty'];
				$table[$r['sku_id']]['stock_take'][$r['date']]['qty3'] = $r['qty']*$r['uom_fraction'];

				if(!$table[$r['sku_id']]['stock_take'][$r['date']]['selling_price']){
					$sql3 =$con_multi->sql_query("select si.id, ifnull(siph.price, si.selling_price) as selling_price
					from sku_items si
					left join sku_items_price_history siph on si.id=siph.sku_item_id and siph.branch_id=$branch_id 
					and siph.added<".ms($r['date'])."
					left join sku on sku.id=si.sku_id
					left join category_cache cc on cc.category_id=sku.category_id
					where si.id=".mi($r['sid'])." and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')
					order by siph.added desc limit 1
					");
					$r3 = $con_multi->sql_fetchassoc($sql3);
					$con_multi->sql_freeresult($sql3);
					$table[$r['sku_id']]['stock_take'][$r['date']]['selling_price'] = $r3['selling_price'];
				}
				
				//Variance
				$sid = $r['sid'];
				$sku_id = $r['sku_id'];
				$date = $r['date'];
				$balance_date = date('Y-m-d',strtotime('-1 day',strtotime($r['date'])));
				$sb_tbl = "stock_balance_b".$branch_id."_".date('Y',strtotime($balance_date));
				$sb_qty = 0;
				$sb_cost = 0;

				$sql2 ="select ifnull(sb.qty,0) as sb_qty, sb.cost as sb_cost,si.sku_id,si.sku_item_code,sc.date
				from sku_items si 
				left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($balance_date)." between sb.from_date and sb.to_date) or (".ms($balance_date).">=from_date and sb.is_latest=1)) 
				left join sku on sku.id=si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$branch_id and sc.date=".ms($r['date'])."
				where sb.sku_item_id in ($sid) and (sb.qty<>0 or sc.qty is not null) and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";				
				$r2 = $con_multi->sql_query($sql2); 	
				$r = $con_multi->sql_fetchassoc($r2);
				if($r['sku_item_code'] != $table[$sku_id]['stock_take'][$date]['sku_item_code2']){
					$table[$sku_id]['stock_take'][$date]['sb_qty']= $r['sb_qty'];
					$table[$sku_id]['stock_take'][$date]['sb_cost']= $r['sb_cost'];
					$table[$sku_id]['stock_take'][$date]['sku_item_code2'] = $r['sku_item_code'];
				}else{
					$table[$sku_id]['stock_take'][$date]['sb_qty']= 0;
					$table[$sku_id]['stock_take'][$date]['sb_cost']= 0;
				}
				$con_multi->sql_freeresult($r2);
				$table[$sku_id]['stock_take'][$date]['stock_balance2'] = $table[$sku_id]['stock_take'][$date]['sb_qty'];
				$table[$sku_id]['stock_take'][$date]['variance'] += $table[$sku_id]['stock_take'][$date]['qty3'] - $table[$sku_id]['stock_take'][$date]['sb_qty'];
				$table[$sku_id]['stock_take'][$date]['stock_balance'] += $table[$sku_id]['stock_take'][$date]['sb_qty'];
				$table[$sku_id]['stock_take'][$date]['price_variance'] += ($table[$sku_id]['stock_take'][$date]['qty2'] - $table[$sku_id]['stock_take'][$date]['stock_balance2']) * $table[$sku_id]['stock_take'][$date]['selling_price'];
				$table[$sku_id]['stock_take'][$date]['total_cost'] += ($table[$sku_id]['stock_take'][$date]['sc_cost']* $table[$sku_id]['stock_take'][$date]['qty2'])- ($table[$sku_id]['stock_take'][$date]['sb_qty'] * $table[$sku_id]['stock_take'][$date]['sb_cost']);
			}
			$con_multi->sql_freeresult($r1);
		}else{
			$sql="select si.*,si.id as sid from stock_check sc 
			left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			where (sc.date between ".ms($from)." and ".ms($to).")  and sc.branch_id=$branch_id and $sku_id2 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$result = $con_multi->sql_query($sql);
			while($r2 = $con_multi->sql_fetchassoc($result)){
				$table[$r2['sid']]['sku_item_code'] = $r2['sku_item_code'];
				$table[$r2['sid']]['artno'] = $r2['artno'];
				$table[$r2['sid']]['mcode'] = $r2['mcode'];
				$table[$r2['sid']]['link_code'] = $r2['link_code'];
				$table[$r2['sid']]['description']= $r2['description'];
				$table[$r2['sid']]['is_parent'] = $r2['is_parent'];
			}
			$con_multi->sql_freeresult($result);
			$sql1="select sum(sc.cost*sc.qty)/sum(sc.qty) as sc_cost,si.id as sid ,sc.date
			from stock_check sc left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			where (sc.date between ".ms($from)." and ".ms($to).")  and sc.branch_id=$branch_id and $sku_id2 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no') group by si.id,sc.date";
			$r1 = $con_multi->sql_query($sql1);
			while($r = $con_multi->sql_fetchassoc($r1)){
				$table[$r['sid']]['stock_take'][$r['date']]['cost'] = $r['sc_cost'];
			}
			$con_multi->sql_freeresult($r1);
			$sql="select sc.location as sc_location,sc.sku_item_code,sc.shelf_no as sc_shelf_no,
			sc.date, sc.qty,sc.selling as sc_selling_price, si.id as sid 
			from stock_check sc left join sku_items si using (sku_item_code)
			left join sku on sku.id=si.sku_id
			left join category_cache cc on cc.category_id=sku.category_id
			where (sc.date between ".ms($from)." and ".ms($to).")  and sc.branch_id=$branch_id and $sku_id2 and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
			$r1 = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($r1)){
				$table[$r['sid']]['stock_take'][$r['date']]['sku_item_code'] = $r['sku_item_code'];
				$table[$r['sid']]['stock_take'][$r['date']]['date'] = $r['date'];
				$table[$r['sid']]['stock_take'][$r['date']]['location'] = $r['sc_location'];
				$table[$r['sid']]['stock_take'][$r['date']]['shelf_no'] = $r['sc_shelf_no'];
				$table[$r['sid']]['stock_take'][$r['date']]['selling_price'] = $r['sc_selling_price'];
				$table[$r['sid']]['stock_take'][$r['date']]['qty'] += $r['qty'];
				
				if(!$r['sc_selling_price']){
					$sql3 =$con_multi->sql_query("select si.id, ifnull(siph.price, si.selling_price) as selling_price
					from sku_items si
					left join sku_items_price_history siph on si.id=siph.sku_item_id and siph.branch_id=$branch_id 
					left join sku on sku.id=si.sku_id
					left join category_cache cc on cc.category_id=sku.category_id
					and siph.added<".ms($r['date'])."
					where si.id=".mi($r['sid'])." and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')
					order by siph.added desc limit 1
					");
					$r3 = $con_multi->sql_fetchassoc($sql3);
					$con_multi->sql_freeresult($sql3);
					$table[$r['sid']]['stock_take'][$r['date']]['selling_price'] = $r3['selling_price'];
				}
				
				//Variance
				$sid = $r['sid'];
				$date = $r['date'];
				$sc_qty = $table[$r['sid']]['stock_take'][$r['date']]['qty'];
				$sc_selling_price = $table[$r['sid']]['stock_take'][$r['date']]['selling_price'];
				$sb_qty = 0;
				$sb_cost = 0;
				$balance_date = date('Y-m-d',strtotime('-1 day',strtotime($r['date'])));
				$sb_tbl = "stock_balance_b".$branch_id."_".date('Y',strtotime($balance_date));
				
				
				$sql2 ="select ifnull(sb.qty,0) as sb_qty, sb.cost as sb_cost,si.sku_id,si.sku_item_code,sc.selling as sc_selling_price
				from sku_items si 
				left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($balance_date)." between sb.from_date and sb.to_date) or (".ms($balance_date).">=from_date and sb.is_latest=1)) 
				left join sku on sku.id=si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				left join stock_check sc on sc.sku_item_code=si.sku_item_code and sc.branch_id=$branch_id and sc.date=".ms($r['date'])."
				where sb.sku_item_id in ($sid) and (sb.qty<>0 or sc.qty is not null) and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";		
				
				$r2 = $con_multi->sql_query($sql2); 	
				while($r = $con_multi->sql_fetchassoc($r2)){
					$sb_qty = $r['sb_qty'];
					$sb_cost = $r['sb_cost'];
				}
				$con_multi->sql_freeresult($r2);
				$table[$sid]['stock_take'][$date]['stock_balance'] = $sb_qty;
				$table[$sid]['stock_take'][$date]['variance'] = $sc_qty - $sb_qty;
				$table[$sid]['stock_take'][$date]['price_variance'] = ($sc_qty - $sb_qty) * $sc_selling_price;
				$table[$sid]['stock_take'][$date]['total_cost'] = ($sc_qty * $table[$sid]['stock_take'][$date]['cost']) - ($sb_qty * $sb_cost);
			}
			$con_multi->sql_freeresult($r1);
		}
		
		//report title
		$report_title = array();
		if($branch_id) $bcode = get_branch_code($branch_id);
		else $bcode = get_branch_code($sessioninfo['branch_id']);
		$report_title[] = "Branch: ".$bcode;
		$report_title[] = "From: ".$from;
		$report_title[] = "To: ".$to;

		if($form['group_by_sku']) $report_title[] = "Group By SKU: Yes";
		else $report_title[] = "Group By SKU: No";
		
		$smarty->assign("report_title", join("&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign('table', $table);
		$this->display();
	}
}
$REPORT_STOCK_TAKE_INQUIRY = new REPORT_STOCK_TAKE_INQUIRY('Stock Take Inquiry');
?>
