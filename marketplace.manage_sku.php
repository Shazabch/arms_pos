<?php
/*
11/21/2019 10:47 AM Andy
- Added maintenance checking v423.

1/21/2019 2:53 PM William
 - Enhanced to show image and weight of sku items and added new checking when marketplace add sku item.
 
3/2/2020 11:33 AM William
- Enhanced to change checking of marketplace add sku item.

3/11/2020 2:37 PM Andy
- Enhanced to set sku_items last_update when add or active/deactive sku in marketplace.

4/8/2020 1:38 PM William
- Remove checking of photo required when add marketplace.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MARKETPLACE_MANAGE_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MARKETPLACE_MANAGE_SKU', BRANCH_CODE), "/index.php");
$maintenance->check(423);

class MKTPLACE_SKU_MANAGE extends Module{
	var $status_list = array(0=>"All", 1=>"Yes", 2=>"No");
	
	function __construct($title){
		global $con, $smarty;
		
		$this->init_load();
		
		parent::__construct($title);
	}
	
	function _default(){
		$this->load_si_list();
	    $this->display();
	}
	
	private function init_load(){
		global $smarty;
		
		$smarty->assign("status_list", $this->status_list);
	}
	
	private function load_si_list(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		$filter = array();
		
		// found user was filtering debtor
		if($form['status']){
			if($form['status'] == 2) $filter[] = "mpsi.active=0";
			else $filter[] = "mpsi.active=1";
		}
		
		if($filter) $str_filter = "where ".join(' and ', $filter);
		$is_under_gst = 0;

		// get sku item id in this page
		$q1 = $con->sql_query("select si.*, mpsi.sku_item_id, mpsi.active as is_mpsi_active, mpsi.last_update as mpsi_last_update, mpsi.added as mpsi_added
							  from marketplace_sku_items mpsi
							  left join sku_items si on si.id = mpsi.sku_item_id
							  $str_filter
							  order by mpsi.active desc, mpsi.last_update desc");
		while($r = $con->sql_fetchassoc($q1)){
			// check if the additional desc was serialised, unserialise it and join it with \n
			/*if($r['additional_description']){
				$additional_description = unserialize($r['additional_description']);
				$r['additional_description'] = join("\n", $additional_description);
				unset($additional_description);
			}*/
			
			$form['si_list'][$r['sku_item_id']] = $r;
		}
		$form['si_count'] = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		unset($str_filter);
		
		//print_r($form);
		$smarty->assign('form', $form);
		
		return $form;
	}
	
	function ajax_reload_si_list($added = true, $sku_item_code_list = array()){
		global $con, $smarty, $sessioninfo;
		
		$this->init_load();
		$form = $this->load_si_list();
		//$smarty->assign('form', $form);
		
		$ret = array();
		$ret['added'] = $added;
		$ret['sku_item_code_list'] = $sku_item_code_list;
		$ret['ok'] = 1;
		if($this->si_err_list){
			$ret['is_error'] = true; // found got errors attached
			$smarty->assign("si_err_list", $this->si_err_list);
		}
		
		$ret['html'] = $smarty->fetch('marketplace.manage_sku.items.tpl');
		
		die(json_encode($ret));
	}
	
	function ajax_add_sku_items(){
		global $con, $smarty, $sessioninfo, $appCore, $config;
		
		$form = $_REQUEST;
		$this->errm = array();
		
		// no sku items were selected for add
		if(!$form['sku_code_list']){
			die("No SKU item were added");
		}

		$this->si_err_list = $this->si_err_msg = $this->sku_item_code_list = array();
		$this->validate_data();
		
		// found having errors
		if($this->si_err_list){
			$this->ajax_reload_si_list();
			return;
		}
		unset($this->si_err_list);
		
		$ttl_mpsi_added = 0;
		foreach($form['sku_code_list'] as $dummy=>$sku_item_code){
			// select sku item info
			$q1 = $con->sql_query("select si.* from sku_items si where si.sku_item_code = ".ms($sku_item_code));
			$si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$sid_list = array();
			if($form['add_parent_child_sku']){ // if user add sku item by parent child
				$q1 = $con->sql_query("select si.* from sku_items si where si.sku_id = ".mi($si_info['sku_id']));
				
				while($r = $con->sql_fetchassoc($q1)){
					$si_list[$r['id']] = $r;
				}
				$con->sql_freeresult($q1);
			}else{ // add specific sku item only
				$si_list[$si_info['id']] = $si_info;
			}
			unset($si_info);
			
			$invalid_column = array();
			foreach($si_list as $sid=>$si){
				if(!$si['description'] || $si['description'] == '') $invalid_column[] = "Description";
				if(!$si['marketplace_description'] || $si['marketplace_description'] == '') $invalid_column[] = "Marketplace Description";
				if($si['weight_kg'] <= 0)  $invalid_column[] = "Weight";
				if($si['length'] <= 0)  $invalid_column[] = "Length";
				if($si['height'] <= 0)  $invalid_column[] = "Height";
				if($si['width'] <= 0)  $invalid_column[] = "Width";
				if($invalid_column){
					$invalid_column = join(", ",$invalid_column);
					$this->si_err_msg[$si['sku_item_code']] = "SKU [$si[sku_item_code]] must have (".$invalid_column.") before add.";
					unset($invalid_column);
					continue;
				}
				
				// try to select if the sku item have already existed
				$q1 = $con->sql_query("select mpsi.*
									   from marketplace_sku_items mpsi
									   where mpsi.sku_item_id = ".mi($sid));
				
				// found the sku item already existed on the marketplace, skip it
				if($con->sql_numrows($q1) > 0){
					// capture some info to let user know?
					if(in_array($si['sku_item_code'], $form['sku_code_list'])) $this->existed_si_list[$sid] = $si;
					continue;
				}
				$con->sql_freeresult($q1);
				
				// proceed to insert sku item for marketplace if non-existed
				$ins = array();
				$ins['sku_item_id'] = $si['id'];
				$ins['active'] = 1;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("insert into marketplace_sku_items ".mysql_insert_by_field($ins));
				$con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id=".mi($si['id']));
				unset($ins);
				$ttl_mpsi_added++;
				$this->sku_item_code_list[] = $si['sku_item_code'];
				log_br($sessioninfo['id'], 'MARKETPLACE', $si['id'], "Added new SKU item for Marketplace: SKU ITEM ID#".$si['id']);
			}
			unset($si_list);
		}
		
		$smarty->assign("err_msg", $this->si_err_msg);
		// assign the result after done the insertion
		$result = array();
		$result['existed_si_list'] = $this->existed_si_list;
		$result['ttl_mpsi_added'] = $ttl_mpsi_added;
		
		$smarty->assign("result", $result);
		$added = true;
		if($ttl_mpsi_added <= 0) $added = false;
		// reload the sku item list
		$this->ajax_reload_si_list($added, $this->sku_item_code_list);
		
		/*$ret = array();
		$ret['ok'] = 1;
		
		die(json_encode($ret));*/
	}
	
	function ajax_active_changed(){
		global $con, $smarty, $sessioninfo, $appCore, $config;
		
		$form = $_REQUEST;
		
		// if no item were checked and sku item ID not found
		if(!$form['chk_si_list'] && !$form['sku_item_id']){
			die("Invalid SKU ITEM ID or no item were selected.");
		}
		
		$sku_item_list = array();
		if($form['chk_si_list']){ // it is on list of sku items
			$sku_item_list = $form['chk_si_list'];
		}else{ // only individual sku item being clicked
			$sku_item_list[$form['sku_item_id']] = 1;
		}
		
		// means set sku items to active
		if($form['active']) $status_desc = "Activated";
		else $status_desc = "Deactivated";
		
		$ttl_upd_count = 0;
		
		foreach($sku_item_list as $sid=>$active){
			if(!$active) continue;
			// check if user trying to set the status which already in same state
			$q1 = $con->sql_query("select * from marketplace_sku_items where sku_item_id = ".mi($sid)." and active = ".mi($form['active']));
			if($con->sql_numrows($q1) > 0) continue;
			$con->sql_freeresult($q1);
			
			$upd = array();
			$upd['active'] = $form['active'];
			$upd['last_update'] = "CURRENT_TIMESTAMP";
			
			$q1 = $con->sql_query("update marketplace_sku_items set ".mysql_update_by_field($upd)." where sku_item_id = ".mi($sid));
			$con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id=".mi($sid));
			
			if($con->sql_affectedrows($q1) > 0){
				$ttl_upd_count++;
			}
			
			//log_br($sessioninfo['id'], 'MARKETPLACE', $sid, $status_desc." SKU item for Marketplace: SKU ITEM ID#".$sid);
		}
		
		if($ttl_upd_count > 0){
			// reload the sku item list
			$this->ajax_reload_si_list();
		}else die("No SKU item(s) were ".$status_desc);
	}
	
	function validate_data(){
		global $con, $config, $LANG, $appCore;
		
		$form = $_REQUEST;

		foreach($form['sku_code_list'] as $dummy=>$sku_item_code){
		}
	}
}

$MKTPLACE_SKU_MANAGE = new MKTPLACE_SKU_MANAGE('Marketplace - Manage SKU');
?>