<?php
/*
5/14/2009 1:07:44 PM yinsee
- filter active=1 only

7/27/2011 5:10:43 PM Justin
- Amended the cost round up to base on config.

2/8/2013 3:28 PM Fithri
- Adjustment Summary is not tally with detail

1/29/2015 3:14 PM Andy
- Add Adj In Total Cost & Adj Out Total Cost.

7/13/2015 5:25 PM Joo Chia
- Add in filter by adjustment type in show_report().

10/13/2015 2:00 PM Andy
- Change the item cost to multiply the cost in adjustment_items, not cost history.
- Change the cost column calculation to use average.'

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

10/24/2019 1:04 PM William
- Fixed bug when filter by adjustment type and department, the result will wrong.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

ini_set('memory_limit', '256M');
set_time_limit(0);

$smarty->assign("PAGE_TITLE", "Adjustment Summary");
init_selection();

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

$smarty->assign("form", $_REQUEST);
$smarty->assign('branch_id',$branch_id);
$smarty->display("adjustment.summary.tpl");

function show_report(){
	global $con, $smarty, $sessioninfo, $branch_id, $config;

	//for printing purpose
	$con->sql_query("select * from branch where id=$branch_id");
	$r0=$con->sql_fetchrow();
	$smarty->assign("p_branch",$r0);
	
	$from_Date=$_REQUEST['from'];
	$to_Date=$_REQUEST['to'];
	
	$title="Date: $from_Date - $to_Date";	
	$title .= " / ";	
	if ($_REQUEST['department_id']){
		$con->sql_query("select description from category where id=".mi($_REQUEST['department_id']));
		$v = $con->sql_fetchrow();
		$title .= "Department: $v[0]";
	}
	else{
		$title .= "Department: All";
	}
	$title .= " / ";

	if ($_REQUEST['brand_id']>0){
		$con->sql_query("select description from brand where id =".mi($_REQUEST['brand_id']));
		$v = $con->sql_fetchrow();
		$title .= "Brand: $v[0]";
	}
	elseif ($_REQUEST['brand_id']==='0'){
		$title .= "Brand: UN-BRANDED";
	}
	else{
		$title .= "Brand: All";
	}
	
	$title .= " / ";

	if ($_REQUEST['adjustment_type'] != ''){
		$title .= "Adjustment Type: ".trim($_REQUEST['adjustment_type']);
	} else {
		$title .= "Adjustment Type: All";
	}
	
	$smarty->assign("title", "SKU Adjustment Report ($title)");	

	$where = array();
	if ($_REQUEST['department_id']){
		$where[] = " category.department_id = ".mi($_REQUEST['department_id']);
		$by_department=1;
	}
	else{
		$where[] = "category.department_id in ($sessioninfo[department_ids])";	
	}

	if ($_REQUEST['brand_id']){
		$where[] = " sku.brand_id = ".mi($_REQUEST['brand_id']);
	}
	
	if ($_REQUEST['adjustment_type']){
		$where[] = " adj.adjustment_type = ".ms($_REQUEST['adjustment_type']);
	}
	
	$where = join(" and ", $where);
	
	if(!$by_department){
		$q2=$con->sql_query("select adj.*, adj_i.qty,adj_i.cost, adj.dept_id, category.description as dept, adj_i.sku_item_id 
from adjustment adj 
left join adjustment_items adj_i on adj_i.adjustment_id=adj.id and adj_i.branch_id=adj.branch_id 
left join sku_items si on si.id=sku_item_id 
left join sku on sku.id=si.sku_id 
left join category on adj.dept_id = category.id 
where adj.branch_id=$branch_id and adjustment_date between ".ms($from_Date)." and ".ms($to_Date)." and adj.approved and adj.status<2 and qty is not null and $where ");
//group by adj.dept_id, adj.id

		while ($r2=$con->sql_fetchrow($q2)){
			$temp[$r2['dept_id']]['no_adj']++;						
			if($r2['qty']<0){
				$temp[$r2['dept_id']]['adj_out']+=abs($r2['qty']);			
				$temp[$r2['dept_id']]['negatif_cost'] += -(round($r2['qty']*$r2['cost'], $config['global_cost_decimal_points']));
			}
			else{
				$temp[$r2['dept_id']]['adj_in']+=$r2['qty'];	
				$temp[$r2['dept_id']]['positif_cost'] += round($r2['qty']*$r2['cost'], $config['global_cost_decimal_points']);		
			}	
			//if($temp[$r2['dept_id']]['adj_in'] || $temp[$r2['dept_id']]['adj_out']){
				$temp[$r2['dept_id']]['dept_id']=$r2['dept_id'];
				$temp[$r2['dept_id']]['dept']=$r2['dept'];	 
			//}
			$adj=$temp;
		}

		$smarty->assign('adj',$adj);
		$smarty->display("adjustment.summary.top.tpl");	
	}
	else{
/*
		$q1=$con->sql_query("select sku_items.*, sku_items.id as sku_item_id
from sku_items 
left join sku on sku_items.sku_id = sku.id 
left join category on sku.category_id = category.id 
where $where") or die(mysql_error());
*/
/*
		$q1=$con->sql_query("select adj_i.sku_item_id, si.sku_item_code,si.artno,si.mcode,si.description
from adjustment adj 
left join adjustment_items adj_i on adj_i.adjustment_id=adj.id and adj_i.branch_id=adj.branch_id 
left join sku_items si on si.id=sku_item_id 
left join sku on sku.id=si.sku_id 
left join category on adj.dept_id = category.id 
where adj.branch_id=$branch_id and adjustment_date between ".ms($from_Date)." and ".ms($to_Date)." and adj.approved and adj.status<2 and qty is not null and $where ");

		while($r1=$con->sql_fetchrow($q1)){
			$temp_id[$r1['sku_item_id']]=$r1;
		}
		if (!$temp_id) return false;	
		$where_id = "sku_item_id in (" . join(",", array_keys($temp_id)) . ")";*/
		
		$q2=$con->sql_query("select adj_i.qty, adj_i.sku_item_id, adj_i.cost, si.sku_item_code,si.artno,si.mcode,si.description
from adjustment_items adj_i 
left join adjustment adj on adj.id=adjustment_id and adj.branch_id=adj_i.branch_id 
left join sku_items si on si.id=sku_item_id 
left join sku on sku.id=si.sku_id 
left join category on adj.dept_id = category.id 
where adj.branch_id=$branch_id and adjustment_date between ".ms($from_Date)." and ".ms($to_Date)." and adj.approved and adj.status<2 and qty is not null and $where");

		while ($r2=$con->sql_fetchrow($q2)){			
			$temp_info[$r2['sku_item_id']]['sku_item_id'] = $r2['sku_item_id'];
			$temp_info[$r2['sku_item_id']]['sku_item_code'] = $r2['sku_item_code'];
			$temp_info[$r2['sku_item_id']]['artno'] = $r2['artno'];
			$temp_info[$r2['sku_item_id']]['mcode'] = $r2['mcode'];
			$temp_info[$r2['sku_item_id']]['description'] = $r2['description'];
			if($r2['qty']<0){
				$temp[$r2['sku_item_id']]['adj_out']+=abs($r2['qty']);
				$temp[$r2['sku_item_id']]['adj_out_cost']+=round(abs($r2['qty']*$r2['cost']), $config['global_cost_decimal_points']);
			}
			else{
				$temp[$r2['sku_item_id']]['adj_in']+=$r2['qty'];				
				$temp[$r2['sku_item_id']]['adj_in_cost']+=round($r2['qty']*$r2['cost'], $config['global_cost_decimal_points']);
			}	
			if($temp[$r2['sku_item_id']]['adj_in'] || $temp[$r2['sku_item_id']]['adj_out']){
				//$get_cost[$r2['sku_item_id']]=1;
				$items[$r2['sku_item_id']]['info']=$temp_info[$r2['sku_item_id']];			
				$items[$r2['sku_item_id']]['adj']=$temp[$r2['sku_item_id']];
				
				// use avg cost
				$items[$r2['sku_item_id']]['info']['cost_price'] = round(($items[$r2['sku_item_id']]['adj']['adj_in_cost'] + $items[$r2['sku_item_id']]['adj']['adj_out_cost']) / ($items[$r2['sku_item_id']]['adj']['adj_in'] + $items[$r2['sku_item_id']]['adj']['adj_out']), $config['global_cost_decimal_points']);
			}
		}

		/*$q3=$con->sql_query("select sku_items_cost_history.sku_item_id as sid , sku_items_cost_history.grn_cost
from sku_items_cost_history 
where branch_id=$branch_id and date <= ".ms($from_Date)." and date > 0 and $where_id");
		while($r3=$con->sql_fetchrow($q3)){
			if($r3['grn_cost']>0 && $get_cost[$r3['sid']]){
				$items[$r3['sid']]['info']['cost_price']=$r3['grn_cost'];				
			}
		}

		if($items){
			foreach($items as $sid => $r){
				$items[$sid]['adj']['adj_in_cost'] = round($r['adj']['adj_in']*$r['info']['cost_price'], $config['global_cost_decimal_points']);
				$items[$sid]['adj']['adj_out_cost'] = round($r['adj']['adj_out']*$r['info']['cost_price'], $config['global_cost_decimal_points']);
			}
			
			//print_r($items);
		}*/
		//echo"<pre>";print_r($items);echo"</pre>";
		$smarty->assign('items',$items);	
		$smarty->display("adjustment.summary.detail.tpl");		

	}
}

function init_selection(){
	global $con, $smarty, $sessioninfo, $branch_id, $config;
	
	if (BRANCH_CODE == 'HQ'){
		$con->sql_query("select id, code from branch");
		$smarty->assign('branch',$con->sql_fetchrowset());
		$con->sql_freeresult();
	}
	
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$depts = "id in (0)";
		else
			$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$depts = 1;
	}
	// show department option
	$con->sql_query("select id, description from category where active=1 and level = 2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	$con->sql_freeresult();
	
	// show brand option
	$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
	$con->sql_query("select id, description from brand where $br active=1 order by description");
	$smarty->assign("brand", $con->sql_fetchrowset());
	$con->sql_freeresult();
	
	// show adjustment type option
	foreach ($config['adjustment_type_list'] as $type_list) {
			$adj_type_list[]['adjustment_type'] = strtoupper($type_list['name']);
	}
	
	$con->sql_query("select distinct adjustment_type from adjustment");
	while ($ar1 = $con->sql_fetchassoc()){
		
		$matched = false;
		
		foreach ($config['adjustment_type_list'] as $type_list) {
			if (strtoupper($type_list['name']) == strtoupper($ar1['adjustment_type'])) {
				$matched = true;
				break;
			}
		}
		
		if (!$matched){
			$adj_type_list[]['adjustment_type'] = strtoupper($ar1['adjustment_type']);
		}
	}
	$con->sql_freeresult();
	
	$smarty->assign("adj_type_list", $adj_type_list);
	
	if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
	if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
}
?>
