<?php
/*
12/7/2012 5:36 PM Justin
- Bug fixed on system still picking up all items to do adjustment for those sku with fresh market.
*/
ini_set("display_errors",0);
ini_set('memory_limit', '256M');
set_time_limit(0);
include("include/common.php");

session_start();
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE', BRANCH_CODE), "/index.php");

class ST_ZERO_NEGATIVE_STOCK extends Module{
    function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty, $config, $promo_control_type;
		
		if(!$_REQUEST['date']) $_REQUEST['date'] = date("Y-m-d");
		
		$con->sql_query_false("select * from branch order by sequence,code", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
	    global $con, $smarty;

		$this->display();
	}
	
	function run(){
		global $con, $smarty, $sessioninfo, $LANG;
		
		$rows_insert = 0;
		$err = $this->validate_data();
		
		if($err){
			$smarty->assign("err", $err);
			$this->display();
			exit;
		}
		
		$bid = $_REQUEST['branch_id'];
		$date = $_REQUEST['date'];
		$location = $_REQUEST['location'];
		$shelf = $_REQUEST['shelf'];
		$sb_date = date("Y-m-d", strtotime("-1 day", strtotime($date)));
		$sb_year = date("Y", strtotime($sb_date));
		
		// loads all sku items, when sku is fresh market then only load is parent sku item
		$q = $con->sql_query("select si.id, if(sku.is_fresh_market = 'inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market, si.is_parent
							  from sku_items si
							  left join sku on sku.id = si.sku_id
							  left join category_cache cc on cc.category_id = sku.category_id
							  having case when is_fresh_market = 'yes' then is_parent=1 else 1=1 end
							  order by si.id");
		
		while($r = $con->sql_fetchassoc($q)){
			if($r['is_fresh_market'] == "yes") $is_fresh_market = 1;
			else $is_fresh_market = 0;
			// check negative stocks
			$q2 = $con->sql_query("select sb.qty
								   from stock_balance_b".$bid."_".$sb_year." sb
								   where sb.sku_item_id=".mi($r['id'])." and ".ms($sb_date)." between sb.from_date and sb.to_date
								   limit 1");
			$sb_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($sb_info['qty'] < 0){ // found system got negative stock for this item
				$ins = array();
				$ins['branch_id'] = $bid;
				$ins['date'] = $date;
				$ins['location'] = $location;
				$ins['shelf'] = $shelf;
				$ins['user_id'] = $sessioninfo['id'];
				$ins['sku_item_id'] = $r['id'];
				$ins['qty'] = 0;
				
				$temp = get_sku_item_cost_selling($bid, $r['id'], $date, array('cost'));
				$ins['cost_price'] = mf($temp['cost']); 
				$ins['imported'] = 0;
				$ins['is_fresh_market'] = $is_fresh_market;
				
				$q3 = $con->sql_query("replace into stock_take_pre ".mysql_insert_by_field($ins));
				if($con->sql_affectedrows($q3) > 0) $rows_insert++;
			}
		}
		
		if($rows_insert){
			$url = "admin.stock_take.php?branch_id=".$bid."&date=".$date."&location=".$location."&shelf=".$shelf;
			$smarty->assign("url", $url);
		}else{
			$err[] =sprintf($LANG['ST_NS_NO_RECORD'], get_branch_code($bid));
			$smarty->assign("err", $err);
		}
		
		$this->display();
	}
	
	function validate_data(){
		global $LANG;

		$form = $_REQUEST;
		
		if(!$form['date'] || !$form['location'] || !$form['shelf']) $err[] = $LANG['ST_NS_INVALID_INFO'];
		
		return $err;
	}
}

$ST_ZERO_NEGATIVE_STOCK = new ST_ZERO_NEGATIVE_STOCK('Stock Take - Zerolize Negative Stock');
?>
