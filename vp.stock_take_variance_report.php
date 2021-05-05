<?php

/*
1/16/2013 9:39:00 AM Fithri
- add column to show items category (level 3)
- can sort by category or description

1/17/2013 9:55:00 AM Fithri
- enhanced show level 4 category instead of level 3.

1/23/2013 10:00:00 AM Fithri
- location use branch code, shelf use vendor code

1/23/2013 3:48:00 PM Fithri
- add cost & Variance Cost (Cost * Variance Qty) column

6/6/2013 2:42 PM Andy
- Change report to filter the date base on link_username instead of link_user_id
*/

ini_set("display_errors",0);
ini_set('memory_limit', '256M');
set_time_limit(0);
include("include/common.php");

session_start();
if (!$vp_login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(1);

class Stock_Take_Variance_Report extends Module
{

	function __construct($title, $template='') {
		global $config, $smarty,$con, $vp_session;
		
		$this->default_location = BRANCH_CODE;
		$this->default_shelf = $vp_session['code'];
		
		$this->branch_id = $vp_session['branch_id'];
		$this->date = $_REQUEST['date'];
		$this->sort_by = $_REQUEST['sort_by'];
		$this->uid = $vp_session['vp']['link_user_id'];
		
		$smarty->assign('config',$config);
		parent::__construct($title, $template);
	}
	
	function _default() {
		global $config, $smarty,$con, $vp_session;
		$smarty->assign("date", $this->get_date());
		$smarty->display('vp.stock_take_variance_report.tpl');
	}
	
	function dump($what) {
		if (empty($what)) {
			print 'dumped an empty variable<br />';
			return;
		}
		print '<pre>';
		print_r($what);
		print '</pre>';
	}
	
	function get_date() {
		global $config, $smarty,$con, $vp_session;
		
		$dl = $con->sql_query("select
							distinct(date) as date
							from stock_check
							where
							branch_id=$this->branch_id
							and scanned_by = ".ms($vp_session['vp']['link_username'])."
							and location = '$this->default_location'
							and shelf_no = '$this->default_shelf'
							and stock_check.is_fresh_market=0
							order by date desc
							");
		while ($row = $con->sql_fetchassoc($dl)) $data[] = $row['date'];
		$con->sql_freeresult($dl);
		return $data;

	}

	function load_report() {
		global $config, $smarty,$con, $vp_session;
		$smarty->assign("stdate", $this->date);
		$smarty->assign("records", $this->get_data());
		$smarty->display('vp.stock_take_variance_report.table.tpl');
	}
	
	function get_data() {
	
		global $config, $smarty,$con, $vp_session;
		
		$date = explode('-',$this->date);
		$year = $date[0];
		
		$sql = "select
				sum(sc.qty) as qty,
				sc.cost,
				sku_items.id as sku_item_id,
				sku_items.mcode,
				sku_items.description,
				sku_items.sku_item_code,
				sku_items.artno,
				ifnull(sb.qty,0) as sb_qty,
				c.description as category
				from stock_check sc
				left join sku_items ON sc.sku_item_code=sku_items.sku_item_code
				left join stock_balance_b".$this->branch_id."_$year sb on ((".ms($this->date)." - interval 1 day) between sb.from_date and sb.to_date) and sku_items.id = sb.sku_item_id
				left join sku on sku_items.sku_id = sku.id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category c on c.id = cc.p4
				where sc.date =".ms($this->date)." 
				and sc.location = ".ms($this->default_location)."
				and sc.shelf_no =".ms($this->default_shelf)."
				and sc.branch_id=$this->branch_id
				and sc.is_fresh_market=0
				group by sc.sku_item_code
				";
		$con->sql_query($sql);
		$table = $con->sql_fetchrowset();
		$con->sql_freeresult();
		
		if ($table) usort($table,array($this,'sortf'));
		return $table;
	}
	
	function sortf($a,$b) {
		if ($this->sort_by) {
			if (!empty($a[$this->sort_by]) || !empty($b[$this->sort_by])) {
				return ($a[$this->sort_by] < $b[$this->sort_by]) ? -1 : 1;
			}
		}
	}

}

$stvr = new Stock_Take_Variance_Report ('Stock Take');

?>
