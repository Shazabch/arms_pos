<?php
/*
1/21/2011 5:22:39 PM Alex
- change use report_server

*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SKUPromotion extends Report
{
	var $where;
	var $groupby;
	var $select;
	
	function generate_report()
	{
		global $con_multi, $smarty, $sessioninfo;
		$where = $this->where;
		$con_multi->sql_query("select si.receipt_description, b.description as brand, p.title,p.date_from, p.date_to, p.time_from, p.time_to, u.l, p.promo_branch_id
						from promotion p
						left join promotion_items pi on p.branch_id = pi.branch_id and p.id = pi.promo_id
						left join sku_items si on pi.sku_item_id = si.id
						left join brand b on pi.brand_id = b.id
						left join user u on p.user_id = u.id
						where ($where[date_from] or $where[date_to]) and p.promo_branch_id regexp 'i:".$_REQUEST['branch_id']."' and p.active = 1 and p.approved = 1 and p.status = 1");
		
		while($r = $con_multi->sql_fetchrow())
		{
			$data[] = $r;
		}
/*		print "<pre>";
		print_r($data);
		print "</pre>";
*/		$smarty->assign('data',$data);
	}
	
	function process_form()
	{
		global $config;
		
		$where = array();
		// call parent
		parent::process_form();
		
		$where['date_from'] = "date_from between ".ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']);
		$where['date_to'] = "date_to between ".ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']);
		$where['branch_id'] = $_REQUEST['branch_id']?"branch_id = ".mi($_REQUEST['branch_id']):1;
		
		$this->where = $where;
	}	
	
	function start_date_of_week($year, $week)
	{
		$Jan1 = mktime(1,1,1,1,1,$year);
		$Offset = (11-date('w',$Jan1))%7-3;
		$start_date = strtotime(($week-1) . ' weeks '.$Offset.' days', $Jan1);
		return $start_date;
	}

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}
$con_multi = new mysql_multi();
$report = new SKUPromotion('SKU Promotion Report');
$con_multi->close_connection();
?>
