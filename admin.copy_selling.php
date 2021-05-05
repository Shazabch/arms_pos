<?php
/*
8/5/2010 12:14:36 PM yinsee
- add mprice and qprice to the copy.
- mprice and qprice items will follow selling price items (when "clear existing" not ticked)

12/9/2010 3:42:18 PM Andy
- Add department filter. (base on user department privilege)

6/24/2011 2:51:18 PM Andy
- Make all branch default sort by sequence, code.

3/22/2013 12:27 PM Justin
- Enhanced to allow user add additional selling price.

7/5/2013 4:06 PM Andy
- Remove error_reporting(E_ALL) from Copy Selling Price Module.

10/2/2013 5:25 PM Justin
- Bug fixed on system showing SQL error while copy selling price without ticking "Clear existing selling price".

11/7/2013 3:07 PM 1Justin
- Enhanced to capture log.

2/26/2014 10:53 AM Fithri
- includes mqprice when copy selling price in Admin Copy Selling Price

4/3/2015 4:12 PM Andy
- Fix copy selling price sql error.

5/13/2015 2:13 PM Justin
- Bug fixed on copy selling price will cause mysql errors.

5/22/2018 11:57 AM Justin
- Enhanced to insert log using log_br.
*/

include("include/common.php");
if ($sessioninfo['level']<9999) header("Location: /");
ini_set('display_errors',1);
//error_reporting(E_ALL);

class admin_copy_selling extends Module
{
	function __construct($title){
	    global $con, $smarty, $sessioninfo;

		// load departments
	    $con->sql_query("select id, description from category where level=2 and active=1  and id in (".$sessioninfo['department_ids'].") order by description");
	    while($r = $con->sql_fetchassoc()){
			$depts[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$smarty->assign('depts', $depts);
		parent::__construct($title);
	}
	
	function _default()
	{
		$this->display();
	}
	
	function copy_selling()
	{
		global $con, $smarty, $LANG, $sessioninfo, $config;
		$bfrom = mi($_REQUEST['from_branch']);
		$bto = mi($_REQUEST['to_branch']);
		$bcode = get_branch_code($bfrom);
		$dept_id = mi($_REQUEST['dept_id']);
		
		$filters = array();
		if($dept_id){
			$q1 = $con->sql_query("select * from category where id = ".mi($dept_id));
			$dept_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$dept_filter = $filters[] = "c.department_id=$dept_id";
		}else $dept_filter = $filters[] = "c.department_id in (".$sessioninfo['department_ids'].")";
		
		// create sku department table
		$tmp_cat_sku = "copy_cat_sku_".session_id()."_".time();
		$sql = "create temporary table $tmp_cat_sku (select si.id
from sku_items si
left join sku on sku.id=si.sku_id
left join category c on c.id=sku.category_id
where $dept_filter)";
		$con->sql_query($sql);

		if ($bfrom==$bto)
		{
			$smarty->assign("msg", "<font color=red>".$LANG['ADMIN_COPY_SELLING_CANNOT_COPY_SAME_BRANCH']."</font>");
			$this->display();
			exit;
		}
		if (isset($_REQUEST['clear']) && $_REQUEST['clear'])
		{
		    // get all sku item id list
		    $q_sid = $con->sql_query("select id from $tmp_cat_sku");
		    $sid_list = array();
		    while($r = $con->sql_fetchassoc($q_sid)){ // loop all sku item id
		        $sid_list[] = mi($r['id']);
				if(count($sid_list)>1000){  // delete 1000 item per query
				    $sku_filter = " and sku_item_id in (".join(',', $sid_list).")";
                    $con->sql_query("delete from sku_items_price where branch_id = $bto $sku_filter");
					$con->sql_query("delete from sku_items_price_history where branch_id = $bto $sku_filter");

					$con->sql_query("delete from sku_items_mprice where branch_id = $bto $sku_filter");
					$con->sql_query("delete from sku_items_mprice_history where branch_id = $bto $sku_filter");

					$con->sql_query("delete from sku_items_qprice where branch_id = $bto $sku_filter");
					$con->sql_query("delete from sku_items_qprice_history where branch_id = $bto $sku_filter");
					$sid_list = array();
				}
			}
			$con->sql_freeresult($q_sid);
			
			if($sid_list){
			    $sku_filter = " and sku_item_id in (".join(',', $sid_list).")";
                $con->sql_query("delete from sku_items_price where branch_id = $bto $sku_filter");
				$con->sql_query("delete from sku_items_price_history where branch_id = $bto $sku_filter");

				$con->sql_query("delete from sku_items_mprice where branch_id = $bto $sku_filter");
				$con->sql_query("delete from sku_items_mprice_history where branch_id = $bto $sku_filter");

				$con->sql_query("delete from sku_items_qprice where branch_id = $bto $sku_filter");
				$con->sql_query("delete from sku_items_qprice_history where branch_id = $bto $sku_filter");
				$sid_list = array();
			}
		}
		else
		{
			$filters[] = "sku_item_id not in (select sku_item_id from sku_items_price where branch_id=$bto)";
		}
		
		$msg = "";
		
		$additional_sp = 0;
		if($config['masterfile_branch_enable_additional_sp']){
			$q1 = $con->sql_query("select * from branch_additional_sp where branch_id = ".mi($bto));
			$basp_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$additional_sp = $basp_info['additional_sp'];
		}

		if($filters) $filter = join(" and ", $filters);
		else $filter = "1=1";
		
		$tmp = "copy_".session_id()."_".time();
		$con->sql_query("create temporary table $tmp (select 
			$bto as branch_id, 
			si.id as sku_item_id, 
			CURRENT_TIMESTAMP as last_update, 
			(ifnull(sip.price, si.selling_price) +".mf($additional_sp).") as price, 
			ifnull(sip.cost, si.cost_price) as cost, 
			ifnull(sip.trade_discount_code, 
			sku.default_trade_discount_code) as trade_discount_code,
			if(sip.price is null,si.selling_foc, sip.selling_price_foc) as selling_price_foc,
			if(sip.price is null,'',sip.selling_price_more_info) as selling_price_more_info
		from sku_items si
		left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = $bfrom
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		where $filter)
		") or die(mysql_error());
		//print "created temp table $tmp, ";
		$msg .= "<li> {$con->sql_affectedrows()} selling price copied";
		
		//$con->sql_query("update $tmp set branch_id=$bto, last_update=CURRENT_TIMESTAMP") or die(mysql_error());
		$con->sql_query("insert into sku_items_price 
		(branch_id, sku_item_id, last_update, price, cost, trade_discount_code, selling_price_foc, selling_price_more_info)
		select branch_id, sku_item_id, last_update, price, cost, trade_discount_code, selling_price_foc, selling_price_more_info from $tmp") or die(mysql_error());
		$con->sql_query("insert into sku_items_price_history (branch_id,sku_item_id,added,price,cost,trade_discount_code,source,user_id,selling_price_foc,selling_price_more_info) 
		select branch_id,sku_item_id,last_update,price,cost,trade_discount_code,'$bcode',$sessioninfo[id],selling_price_foc,selling_price_more_info from $tmp");
		$con->sql_query("drop table $tmp") or die(mysql_error());

		// mprice
		$con->sql_query("create temporary table $tmp (select simp.branch_id, simp.sku_item_id, simp.type, simp.last_update, (simp.price+".mf($additional_sp).") as price, simp.trade_discount_code
		from sku_items_mprice simp
		left join sku_items si on si.id=simp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		where simp.branch_id=$bfrom and $filter)
		") or die(mysql_error());
		$msg .= "<li> {$con->sql_affectedrows()} mutliple-price copied";
		
		//print "created temp table $tmp, ";
		$con->sql_query("update $tmp set branch_id=$bto, last_update=CURRENT_TIMESTAMP") or die(mysql_error());
		$con->sql_query("replace into sku_items_mprice (branch_id,sku_item_id,type,last_update,price,trade_discount_code) select branch_id,sku_item_id,type,last_update,price,trade_discount_code from $tmp") or die(mysql_error());
		$con->sql_query("replace into sku_items_mprice_history (branch_id,sku_item_id,type,added,price,trade_discount_code,user_id) select branch_id,sku_item_id,type,last_update,price,trade_discount_code,$sessioninfo[id] from $tmp") or die(mysql_error());
		$con->sql_query("drop table $tmp") or die(mysql_error());

		// qprice
		$con->sql_query("create temporary table $tmp (select siqp.*
		from sku_items_qprice siqp
		left join sku_items si on si.id=siqp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		where siqp.branch_id=$bfrom and $filter)
		") or die(mysql_error());
		$msg .= "<li> {$con->sql_affectedrows()} quantity-price copied";
		
		//print "created temp table $tmp, ";
		$con->sql_query("update $tmp set branch_id=$bto, last_update=CURRENT_TIMESTAMP") or die(mysql_error());
		$con->sql_query("replace into sku_items_qprice (branch_id,sku_item_id,min_qty,price,last_update) select branch_id,sku_item_id,min_qty,price,last_update from $tmp") or die(mysql_error());
		$con->sql_query("replace into sku_items_qprice_history (branch_id,sku_item_id,min_qty,price,added,user_id) select branch_id,sku_item_id,min_qty,price,last_update,$sessioninfo[id] from $tmp") or die(mysql_error());
		$con->sql_query("drop table $tmp") or die(mysql_error());
		
		// mqprice
		$con->sql_query("create temporary table $tmp (select simqp.*
		from sku_items_mqprice simqp
		left join sku_items si on si.id=simqp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		where simqp.branch_id=$bfrom and $filter)
		") or die(mysql_error());
		$msg .= "<li> {$con->sql_affectedrows()} multiple-quantity-price copied";
		
		//print "created temp table $tmp, ";
		$con->sql_query("update $tmp set branch_id=$bto, last_update=CURRENT_TIMESTAMP") or die(mysql_error());
		$con->sql_query("replace into sku_items_mqprice (branch_id,sku_item_id,min_qty,type,price,last_update) select branch_id,sku_item_id,min_qty,type,price,last_update from $tmp") or die(mysql_error());
		$con->sql_query("replace into sku_items_mqprice_history (branch_id,sku_item_id,min_qty,type,price,added,user_id) select branch_id,sku_item_id,min_qty,type,price,last_update,$sessioninfo[id] from $tmp") or die(mysql_error());
		$con->sql_query("drop table $tmp") or die(mysql_error());

		$logs = array();
		$logs[] = "Selling Price copied from ".get_branch_code($bfrom)." to ".get_branch_code($bto);
		if($dept_info) $logs[] = "Department: ".$dept_info['description'];
		$clear_all = "No";
		if($_REQUEST['clear']){
			$clear_all = "Yes";
		}
		$logs[] = "Clear all existing Selling Price: ".$clear_all;
		$log = join(", ", $logs);
		
		log_br($sessioninfo['id'], 'COPY_SELLING_PRICE', '', $log);
		
        $smarty->assign("msg", "<font color=blue>$msg</font>");
		$this->display();
	}
}

$con->sql_query("select id,code from branch where active order by sequence,code");
$smarty->assign("branches", $con->sql_fetchrowset());
$app = new admin_copy_selling("Copy Selling Price");

?>
