<?php
include("include/common.php");
$maintenance->check(110);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");

class LOCK_PRICE_HISTORY extends Module{
	function __construct($title, $template='')
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		if(!isset($_REQUEST['date_from'])&&!isset($_REQUEST['date_to'])){
			$_REQUEST['date_from'] = date('Y-m-d',strtotime('-1 month',time()));
			$_REQUEST['date_to'] = date('Y-m-d');
			$_REQUEST['all_category'] = 1;
		}
		parent::__construct($title);
	}
	
	function _default()
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		$this->load_cashier();
		$this->load_branches();
		$this->load_counters();
		if($_REQUEST['load_report']){
			$this->load_report();
		}
		$smarty->display('pos_report.lock_price_report.tpl');
	}
	
	private function load_cashier()
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		$sql = "select * from user Where active=1 order by u";
		$con->sql_query($sql)or die(mysql_error());
		while($r = $con->sql_fetchrow())
		{
			$cashier[$r['id']] = $r;
		}
		
		$smarty->assign('cashier',$cashier);
	}
	
	private function load_branches()
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		$q_b = $con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow($q_b)){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches',$branches);
	}
	
	private function load_counters()
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		if(BRANCH_CODE!="HQ")
		{
			$branch = "and branch_id=".mi($sessioninfo['branch_id']);
		}
		
		$sql = "select * from counter_settings Where active=1 ".$branch." order by network_name";
		$con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchrow())
		{
			$counters[$r['id']."-".$r['network_name']] = $r;
		}
		
		$smarty->assign("counters",$counters);
	}
	
	private function load_report()
	{
		global $con, $sessioninfo, $pos_config, $smarty;
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$cashier_id = $_REQUEST['cashier_id'];
		$branch_id = $_REQUEST['branch_id'];
		$counter_id = $_REQUEST['counter_id'];
		$category_id = $_REQUEST['category_id'];
		$category_all = $_REQUEST['all_category'];
		if($cashier_id)
		{
			$user = " and user.id=".mi($cashier_id);
			$con->sql_query("select * from user Where id=".mi($cashier_id));
			$ret = $con->sql_fetchrow();
			$report_header['user'] = $ret['u'];
		}
		else
		{
			$report_header['user'] = "All";
		}
		if(!$date_from)
		{
			$error[] = "Please key in date from";
		}
		
		if(!preg_match("/^(((19|20)\d\d)[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01]))$/",$date_from))
		{
			$error[] = "Invalid date format for date from.";
		}
		
		if(!$date_to)
		{
			$error[] = "Please key in date to";
		}
		
		if(!preg_match("/^(((19|20)\d\d)[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01]))$/",$date_to))
		{
			$error[] = "Invalid date format for date to.";
		}
		
		if(strtotime($date_from)>strtotime($date_to))
		{
			$error[] = "Date from must smaller than date to.";
		}
		else
		{
			$report_header['date_from'] = date("d M Y",strtotime($date_from));
			$report_header['date_to'] = date("d M Y",strtotime($date_to));
		}
		
		if (BRANCH_CODE!='HQ') 
		{
			$branch = "and branch_id =".mi($sessioninfo[branch_id]);
			$con->sql_query("select * from branch Where id=".mi($branch_id));
			$br = $con->sql_fetchrow();
			$report_header['branch'] = $br['code'];
			$con->sql_freeresult();
		}
		elseif($branch_id && BRANCH_CODE=='HQ')
		{
			$branch = " and branch.id=".mi($branch_id);
			$con->sql_query("select * from branch Where id=".mi($branch_id));
			$br = $con->sql_fetchrow();
			$report_header['branch'] = $br['code'];
			$con->sql_freeresult();
		}
		else
		{
			$report_header['branch'] = "All";
		}
		
		if($counter_id)
		{
			list($cid,$cname) = explode("-",$counter_id);
			$counter = " and counter_settings.id=".mi($cid)." and network_name=".ms($cname);
			$report_header['counter'] = $cname;
		}
		else
		{
			$report_header['counter'] = "All";
		}
		
		if(!$category_id && !$category_all)
		{
			$error[] = "Please key in category";
		}
		elseif($category_id)
		{
			$con->sql_query("select level,description from category where id = ".mi($category_id));
			$ret = $con->sql_fetchrow();
			$level = "p".$ret['level'];
			$category = " and $level=".mi($category_id);
			$report_header['category'] = $ret['description'];
			$con->sql_freeresult();
		}
		else
		{	
			$report_header['category'] = "All";
		}
		$sql = "Select lph.*, lp.sku_item_id as item_id, branch.code as branch,sku_items.sku_item_code as arms_code, sku_items.description as item_description, sku_items.selling_price as selling_price,user.u as username,counter_settings.network_name as counter 
		from sku_items_temp_price_history as lph 
		left join sku_items_temp_price as lp on lph.branch_id = lp.branch_id and lph.sku_item_id = lp.sku_item_id and lph.temp_price = lp.temp_price and lph.active = lp.active and lph.added_datetime = lp.lastupdate
		left join branch on lph.branch_id=branch.id 
		left join user on lph.temp_by = user.id
		left join counter_settings on lph.counter_id = counter_settings.id and branch.id = counter_settings.branch_id
		left join sku_items on lph.sku_item_id = sku_items.id left join sku on sku_items.sku_id = sku.id left join category_cache on sku.category_id = category_cache.category_id left join category on category_cache.category_id = category.id Where lph.added_date between ".ms($date_from)." and ".ms($date_to).$user.$branch.$counter.$category." order by added_datetime desc";

		if(!$error)
		{
			$ret = $con->sql_query($sql);
			while($r = $con->sql_fetchrow($ret))
			{
				$temp_price[] = $r;
			}
			unset($r);
			$smarty->assign("temp_price_items_table",$temp_price);
			$smarty->assign("error","");
			$smarty->assign("report_header",$report_header);
			unset($temp_price);
		}
		else
		{
			$smarty->assign("error",$error);
		}
	}
}
$LOCK_PRICE_HISTORY = new LOCK_PRICE_HISTORY('Lock Price Report');
?>