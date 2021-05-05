<?php
/*
6/19/2018 1:30 PM Andy
- Fixed to only search active sku.
*/
include("include/common.php");
//if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!intranet_or_login()) js_redirect($LANG['ACCESS_DENIED_NEED_LOGIN_OR_INTRANET'], "/index.php");
//$maintenance->check(12);

class REPLACEMENT_ITEMS_POPUP extends Module{
	var $settings = array(
	    'sid'=> 0,
		'exclude_self'=> 0,
		'can_confirm_item'=> 0
	);
	
	function _default(){

	}
	
	function load_available_sku(){
		global $con, $smarty, $sessioninfo;
		
		$this->settings['sid'] = mi($_REQUEST['sid']);
		$this->settings['can_confirm_item'] = mi($_REQUEST['can_confirm_item']);
		$this->settings['exclude_self'] = mi($_REQUEST['exclude_self']);
		$this->settings['can_click_item_row'] = mi($_REQUEST['can_click_item_row']);
		
		$con->sql_query("select * from ri_items where sku_item_id=".mi($this->settings['sid']));
		$temp = $con->sql_fetchrow();
		if($temp){
			$ri_id = mi($temp['ri_id']);
			$filter = array();
			$filter[] = "rii.ri_id=$ri_id";
			if($this->settings['exclude_self'])   $filter[] = "rii.sku_item_id<>".mi($this->settings['sid']);
			$filter[] = "si.active=1";
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "select rii.*,ifnull(si.artno,si.mcode) as artno_mcode,si.sku_item_code,si.description,sic.qty,sic.grn_cost as cost,ifnull(sip.price, si.selling_price) as selling_price
			from ri_items rii
			left join sku_items si on si.id=rii.sku_item_id
			left join sku_items_cost sic on sic.branch_id=".mi($sessioninfo['branch_id'])." and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=".mi($sessioninfo['branch_id'])." and sip.sku_item_id=si.id
			$filter";
			
			$con->sql_query($sql);
			$smarty->assign('replacement_items', $con->sql_fetchrowset());
		}
		$smarty->assign('settings', $this->settings);
		$this->display('replacement_items_popup.list.tpl');
	}
}

$REPLACEMENT_ITEMS_POPUP = new REPLACEMENT_ITEMS_POPUP('Replacement Item');
?>
