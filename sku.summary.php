<?php
/* this script is retired */

/*
Update History
==============

10/31/2007 10:27:40 AM yinsee
- shorten the SQL to calculate total GRN items qty
*/
// SKU Summary

// interprate command line as first parameter as selected action
if ($_SERVER['argv'][1]=='run_history')
{
	ini_set("magic_quotes_gpc", "Off");
	define('TERMINAL',1);
	ini_set("display_errors",0);
	$_REQUEST['a'] = $_SERVER['argv'][1];
}

include("include/common.php");
ini_set('display_errors',0);

if (!isset($_SERVER['argv'][1]) && !$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'run_history':
			ob_end_flush();
			set_time_limit(0);
	        $run_opt = $_SERVER['argv'][2];

	        $con->sql_query("drop table if exists sku_items_inventory_history");
	        $con->sql_query("CREATE TABLE `sku_items_inventory_history` ( `branch_id` int(11) NOT NULL default '0', `sku_item_id` int(11) default NULL, `category_id` int(11) default NULL, `date` DATE default NULL, `pos` int(11) NOT NULL default '0', `amount` double default NULL, `selling` double default NULL, `grn` int(11) NOT NULL default '0', `cost` double default NULL, `gra` int(11) NOT NULL default '0', `adjust` int(11) NOT NULL default '0', KEY `date` (`date`), KEY `branch_id` (`branch_id`), KEY `sku_item_id` (`sku_item_id`), KEY `category_id` (`category_id`) ) ");

			// load pos
			$con->sql_query("drop table if exists temp_sku_pos");
			$rs=$con->sql_query("create table temp_sku_pos select branch_id, sku_items.id as sku_item_id, sku.category_id, date(pos_transaction.timestamp) as date, sum(qty) as pos, sum(amount) as amount, sum(amount)/sum(qty)as selling from pos_transaction left join sku_items on pos_transaction.sku_item_code = sku_items.sku_item_code left join sku on sku_id = sku.id where not sku_items.id is null group by branch_id, sku_item_id, year, month, day order by date, branch_id");
			print "\naffected rows: " . $con->sql_affectedrows();
			
			$con->sql_query("drop table if exists temp_sku_gra");
			$rs=$con->sql_query("create table temp_sku_gra select sum(qty) as qty, date(gra.return_timestamp) as date, gra_items.sku_item_id, gra_items.branch_id, category_id from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id left join sku_items on sku_item_id = sku_items.id left join sku on sku_id = sku.id where gra.status=0 and gra.returned group by 2, gra_items.branch_id, gra_items.sku_item_id");
			print "\naffected rows: " . $con->sql_affectedrows();
			
			$con->sql_query("drop table if exists temp_sku_grn");
			$rs=$con->sql_query("create table temp_sku_grn select grn_items.branch_id,sku_item_id,category_id, if(grr.rcv_date,grr.rcv_date,grr.added) as date,
			sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn, grn_items.acc_ctn)*rcv_uom.fraction+if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.pcs, grn_items.acc_pcs)) as qty,
			sum((if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn, grn_items.acc_ctn)*rcv_uom.fraction+if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.pcs, grn_items.acc_pcs))/rcv_uom.fraction*if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)) as total_cost
			from grn_items 
			left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
			left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
			left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
			left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id 
			left join sku_items on grn_items.sku_item_id = sku_items.id 
			left join sku on sku_items.sku_id = sku.id 
			where grn.approved=1 
			group by branch_id,sku_item_id,date
			having qty > 0 and sku_item_id>0");
			print "\naffected rows: " . $con->sql_affectedrows();
	
			$con->sql_query("alter table temp_sku_pos add index(date,branch_id,sku_item_id)");
			$con->sql_query("alter table temp_sku_gra add index(date,branch_id,sku_item_id)");
			$con->sql_query("alter table temp_sku_grn add index(date,branch_id,sku_item_id)");


			$con->sql_query("drop table if exists temp_sku_pos_grn");
			$con->sql_query("create table temp_sku_pos_grn as
			
			select p.branch_id, p.sku_item_id, p.category_id, p.date, p.pos, p.amount, p.selling, g.qty as grn, g.total_cost/g.qty as cost from temp_sku_pos p left join temp_sku_grn g using(date,branch_id,sku_item_id)  
union 
select g.branch_id, g.sku_item_id, g.category_id, g.date, p.pos, p.amount, p.selling, g.qty as grn, g.total_cost/g.qty as cost from temp_sku_grn g left join temp_sku_pos p using(date,branch_id,sku_item_id)"); 
			$con->sql_query("alter table temp_sku_pos_grn add index(date,branch_id,sku_item_id)");
	
			//$con->sql_query("truncate sku_items_inventory_history");
			$con->sql_query("insert into sku_items_inventory_history
				(branch_id, sku_item_id, category_id, date, pos, amount, selling, grn, cost, gra) 
				select p.branch_id, p.sku_item_id, p.category_id, p.date, p.pos, p.amount, p.selling, p.grn, p.cost, g.qty as gra from temp_sku_pos_grn p left join temp_sku_gra g using(date,branch_id,sku_item_id)  
				union 
				select g.branch_id, g.sku_item_id, g.category_id, g.date, p.pos, p.amount, p.selling, p.grn, p.cost, g.qty as gra from temp_sku_gra g left join temp_sku_pos_grn p using(date,branch_id,sku_item_id)"); 
			
			$con->sql_query("drop table temp_sku_gra,temp_sku_grn,temp_sku_pos,temp_sku_pos_grn");
			
			print "\n\n$query_count\n$query_time";
	        exit;

		case 'show':
		    set_time_limit(0);
			show_report();
			break;
	}
}
$con->sql_query("select id,code from branch order by id");
$smarty->assign("branches", $con->sql_fetchrowset());
$smarty->assign('PAGE_TITLE', 'SKU Summary');
$smarty->display("sku.summary.tpl");

function get_sku_cost($id)
{
	global $con;
	$con->sql_query("select cost_price from sku_items where id=$id");
	$c = $con->sql_fetchrow();
	return mf($c[0]);
}

function show_report()
{
	global $con, $table, $sessioninfo, $smarty;
	
	if (BRANCH_CODE == 'HQ')
		$branch_id = intval($_REQUEST['branch_id']);
	else
	    $branch_id = $sessioninfo['branch_id'];

	$dt1 = ms($_REQUEST['from']);
	$dt2 = ms($_REQUEST['to']);

	$root_id=intval($_REQUEST['category_id']);
	if ($root_id>0)
	{
		$sub = $con->sql_query("select * from category where id=$root_id");
		$c=$con->sql_fetchrow();
		$cat_id=$c['id'];
		$pf = 'p'.($c['level']);
		$pt = 'p'.($c['level']+1);
		$filter = "and $pf=$cat_id";
	}
	else
	{
		$filter = "";
		$pt='p1';
		$cat_id=0;
	}

	// load all categoryname under selected root
    $con->sql_query("select id,description from category where root_id = $cat_id or id=$cat_id");
    while($r=$con->sql_fetchrow())
    {
        $category[$r['id']] = $r['description'];
	}
//	print_r($category);
    
/*	$con->sql_query("select sum(pos) as pos, sum(grn) as grn, sum(gra) as gra, sum(amount) as amt, sum(pos*cost) as pos_cost, sum(grn*cost) as grn_cost, (date < $dt1) as is_before, if($pt,$pt,c.category_id) as cat_id from sku_items_inventory_history s left join category_cache c on s.category_id = c.category_id
	where branch_id = $branch_id
		and date <= $dt2
		$filter
	group by is_before, cat_id
	");
*/
	$qty_sum=0;
	while($r=$con->sql_fetchrow())
	{
		if (!$r['is_before'])
		{
			$ret[$r['cat_id']]['cur_pos']['qty'] = $r['pos'];
			$ret[$r['cat_id']]['cur_pos']['selling'] = $r['amt'];
			$ret[$r['cat_id']]['cur_pos']['cost'] = $r['pos_cost'];
			$ret[$r['cat_id']]['cur_grn']['qty'] = $r['grn'];
			$ret[$r['cat_id']]['cur_grn']['cost'] = $r['grn_cost'];
			$ret[$r['cat_id']]['cur_gra']['qty'] = $r['gra'];
		}
		else
		{
			$ret[$r['cat_id']]['pre_pos']['qty'] = $r['pos'];
			$ret[$r['cat_id']]['pre_pos']['selling'] = $r['amt'];
			$ret[$r['cat_id']]['pre_pos']['cost'] = $r['pos_cost'];
			$ret[$r['cat_id']]['pre_grn']['qty'] = $r['grn'];
			$ret[$r['cat_id']]['pre_grn']['cost'] = $r['grn_cost'];
			$ret[$r['cat_id']]['pre_gra']['qty'] = $r['gra'];
		}
	}
/*	print "<pre>";
	print_r($ret);
    print "</pre>";
*/
	foreach (array_keys($ret) as $id)
	{
	    if (!$category[$id])
	    {
	        $ret[$id]['have_subcat'] = false;
            $ret[$id]['description'] = "Un-categorized";
		}
	    else
		{
			$ret[$id]['have_subcat'] = check_subcat($id);
		    $ret[$id]['description'] = $category[$id];
	    }
	}
	
	$smarty->assign("table", $ret);
}

function check_subcat($id)
{
	global $con;
	$con->sql_query("select count(*) from category where root_id = $id");
	$c=$con->sql_fetchrow();
	if ($c[0]>0) return true;
	return false;
}
/*

function get_subcat($c)
{
	global $con, $sessioninfo;

	//return "$c[id] $c[tree_str]";
	$qty_sum = 0;

	if (BRANCH_CODE == 'HQ')
		$branch_id = intval($_REQUEST['branch_id']);
	else
	    $branch_id = $sessioninfo['branch_id'];

	$dt1 = ms($_REQUEST['from']);
	$dt2 = ms($_REQUEST['to']);
	$x = ms("$c[tree_str]($c[id])%");

	$pf = 'p'.($c['level']+1);

	$con->sql_query("select sum(pos) as pos, sum(grn) as grn, sum(gra) as gra, sum(amount) as amt, sum(grn*cost) as cost, (date < $dt1) as is_before, $pf as cat_id from sku_items_inventory_history s left join category_cache c on s.category_id = c.category_id
	where branch_id = $branch_id
		and date <= $dt2
	group by is_before, $pf
	");

	$qty_sum=0;
	while($r=$con->sql_fetchrow())
	{
		if (!$r['is_before'])
		{
			$ret[$r['cat_id']]['cur_pos']['qty'] = $r['pos'];
			$ret[$r['cat_id']]['cur_pos']['selling'] = $r['amt'];
			$ret[$r['cat_id']]['cur_grn']['qty'] = $r['grn'];
			$ret[$r['cat_id']]['cur_grn']['cost'] = $r['cost'];
			$ret[$r['cat_id']]['cur_gra']['qty'] = $r['gra'];
		}
		else
		{
			$ret[$r['cat_id']]['pre_pos']['qty'] = $r['pos'];
			$ret[$r['cat_id']]['pre_pos']['selling'] = $r['amt'];
			$ret[$r['cat_id']]['pre_grn']['qty'] = $r['grn'];
			$ret[$r['cat_id']]['pre_grn']['cost'] = $r['cost'];
			$ret[$r['cat_id']]['pre_gra']['qty'] = $r['gra'];
		}
		$qty_sum+=$r['grn']+$r['pos']+$r['gra'];
	}

	if ($qty_sum)
		return $ret;
	else
		return false;
}*/
?>
