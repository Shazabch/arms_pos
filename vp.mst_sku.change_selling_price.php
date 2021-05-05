<?php
/*
2/25/2013 3:26 PM Justin
- Enhanced to capture user ID for price history.

5/12/2017 10:23 AM Justin
- Enhanced to prompt error message and redirect user back to main page due to customer no longer using this module.

5/12/2017 10:23 AM Justin
- Enhanced to comment out the redirect user back to main page.
*/
include('include/common.php');

//js_redirect("This module no longer available, please contact MIS for further assistance.", "/index.php");
$maintenance->check(137);

if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SKU_CHANGE_SELLING_PRICE extends Module{
	var $bid = 0;
	
	function __construct($title){
		global $con, $smarty, $vp_session;
		
		$this->bid = $vp_session['branch_id'];
		
		parent::__construct($title);
	}
	
	function _default(){
		if($_REQUEST['show_sku']){
			$this->show_sku();
		}
		$this->display();
	}
	
	private function show_sku(){
		global $con, $smarty, $vp_session, $config;
		
		//print_r($_REQUEST);
		
		$sid = mi($_REQUEST['sid']);
		$bid = mi($this->bid);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$sid)	$err[] = "Please search and select 1 SKU first.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$this->data = array();
		
		$con->sql_query("select sku_id from sku_items where id=$sid");
		$sku_id = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		$sql = "select si.id as sku_item_id, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, ifnull(sip.price, si.selling_price) as selling_price, uom.fraction as packing_uom_fraction, si.is_parent
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join uom on uom.id=si.packing_uom_id
		where si.sku_id=$sku_id
		order by si.is_parent desc, si.sku_item_code";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$this->data['items'][$r['sku_item_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($this->data);
		$smarty->assign('data', $this->data);
	}
	
	function ajax_update_selling_price(){
		global $con, $smarty, $vp_session, $config;
		
		//print_r($_REQUEST);
		
		$selling_price_list = $_REQUEST['selling_price'];
		$bid = mi($this->bid);
		
		if(!is_array($selling_price_list) || !$selling_price_list)	die("Invalid Items.");
		if(!$bid)	die("Invalid Branch.");
		
		$sid_list = array_keys($selling_price_list);
		
		$sql = "select si.id as sku_item_id, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, ifnull(sip.price, si.selling_price) as selling_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code, ifnull(sic.grn_cost, si.cost_price) as cost_price
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		where si.id in (".join(',', $sid_list).")
		order by si.is_parent, si.sku_item_code";
		$q1 = $con->sql_query($sql);
		$si_info_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$si_info_list[$r['sku_item_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($si_info_list);
		$time = date("Y-m-d: H:i:s");
		
		foreach($selling_price_list as $sid => $selling_price){
			$si_info = $si_info_list[$sid];
			
			if($si_info['selling_price'] == $selling_price)	continue;	// same price, no need update
			
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['sku_item_id'] = $sid;
			$upd['added'] = $time;
			$upd['price'] = $selling_price;
			$upd['cost'] = $si_info['cost_price'];
			$upd['source'] = 'VP';
			$upd['user_id'] = $vp_session['vp']['link_user_id'];
			$upd['trade_discount_code'] = $si_info['trade_discount_code'];
			$upd['update_by_vendor_id'] = $vp_session['id'];
			
			$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($upd));
			
			$upd2 = array();
			$upd2['branch_id'] = $bid;
			$upd2['sku_item_id'] = $sid;
			$upd2['last_update'] = $time;
			$upd2['price'] = $selling_price;
			$upd2['cost'] = $si_info['cost_price'];
			$upd2['trade_discount_code'] = $si_info['trade_discount_code'];
			
			$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd2));
			
			$con->sql_query("update sku_items set lastupdate = ".ms($time)." where id = $sid"); 
			
			log_vp($vp_session['id'], "MASTERFILE_SKU", $sid, "Price Change for $si_info[sku_item_code] to $selling_price (Discount: $si_info[trade_discount_code], Branch $bid)");
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
}

$SKU_CHANGE_SELLING_PRICE = new SKU_CHANGE_SELLING_PRICE('SKU Change Selling Price');
?>
