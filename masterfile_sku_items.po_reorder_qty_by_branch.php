<?php
/*
12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

12/16/2019 1:07 PM William
- Added new "Moq Qty" to PO Reorder Qty by Branch.
*/

include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_PO_REORDER_QTY_BY_BRANCH')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_PO_REORDER_QTY_BY_BRANCH', BRANCH_CODE), "/index.php");

class PO_REORDER_QTY_BY_BRANCH_MODULE extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

		if(BRANCH_CODE != "HQ") $filter[] = "id = ".mi($sessioninfo['branch_id']);
		if($filter) $filter = " and ".join(" and ", $filter);

		$sql = "select id,code from branch where active=1 $filter order by sequence,code";

		$con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branch_list[$r['id']]=$r;
		}
		$this->branch_list = $branch_list;
		$smarty->assign('branches',$branch_list);
		
		$q1 = $con->sql_query("select u.* 
							 from user u
							 left join user_privilege up on u.id=up.user_id 
							 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

		while($r = $con->sql_fetchassoc($q1)){
			$users[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		$smarty->assign("users", $users);
		
 		parent::__construct($title);
	}
	
    function _default(){
		global $con, $smarty;
	    $this->display();
	}
	
	function search(){
        global $con, $smarty, $config, $sessioninfo;

		$total_rows=0;
        $form = $_REQUEST;
		
		if(BRANCH_CODE != "HQ") $filter[] = "sipr.branch_id = ".mi($sessioninfo['branch_id']);
		
		if($filter) $filter = " and ".join(" and ", $filter);

		$sql = $con->sql_query("select sipr.*, si.id as sku_item_id, si.sku_item_code, si.description, si.mcode, si.artno, si.link_code, si.doc_allow_decimal
								from sku_items si
								left join sku_items_po_reorder sipr on sipr.sku_item_id = si.id $filter
								where si.sku_id = (select tmp.sku_id from sku_items tmp where tmp.id = ".mi($form['sku_item_id']).")
								order by si.sku_item_code");
		
		while($r = $con->sql_fetchassoc($sql)){
			if(!$si_info[$r['sku_item_id']]){
				$si_info[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
				$si_info[$r['sku_item_id']]['description'] = $r['description'];
				$si_info[$r['sku_item_id']]['doc_allow_decimal'] = $r['doc_allow_decimal'];
			}
			$items[$r['sku_item_id']][$r['branch_id']] = $r;
		}
		
		$smarty->assign('si_info',$si_info);
		$smarty->assign('items',$items);
		if($form['show_type']){
			$this->display();
		}else{
			$smarty->display('masterfile_sku_items.po_reorder_qty_by_branch.items.tpl');
		}
	}

	function save(){
		global $con, $smarty, $sessioninfo;
		$form=$_REQUEST;
		
		$err = $this->validate_data($form);
		
		if($err){
			$ret = $tmp = array();
			$tmp['err'] = "<li>".join("<li>", $err);
			$ret[] = $tmp;
			print json_encode($ret);
			exit;
		}

		// insert/update into the tables
		$logs = "";
		foreach($form['min_qty'] as $sid=>$bid_list){
			foreach($bid_list as $bid=>$min_qty){
				$max_qty = $form['max_qty'][$sid][$bid];
				$moq_qty = $form['moq_qty'][$sid][$bid];
				$notify_user_id = $form['notify_user_id'][$sid][$bid];
				$sku_item_id = $form['item_id'][$sid][$bid];
				$si_code = $form['si_code'][$sid];
				
				// is new and need to add
				$ins_blist = $upd_blist = array();
				if(!$sku_item_id){
					if(!$min_qty && !$max_qty) continue; // if found it's not set, skip it
					
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['sku_item_id'] = $sid;
					$ins['user_id'] = $sessioninfo['id'];
					$ins['min_qty'] = $min_qty;
					$ins['max_qty'] = $max_qty;
					$ins['moq_qty'] = $moq_qty;
					$ins['notify_user_id'] = $notify_user_id;
					$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
					
					$con->sql_query("replace into sku_items_po_reorder ".mysql_insert_by_field($ins));
					
					//$ins_blist[$bid]['item_id'] = $con->sql_nextid();
					$ins_blist[$bid]['bcode'] = $this->branch_list[$bid]['code'];
					$ins_blist[$bid]['min_qty'] = $min_qty;
					$ins_blist[$bid]['max_qty'] = $max_qty;
					$ins_blist[$bid]['moq_qty'] = $moq_qty;
					$ins_blist[$bid]['notify_user_id'] = $notify_user_id;
				}else{ // is existing and need to update
					$q1 = $con->sql_query("select * from sku_items_po_reorder where sku_item_id = ".mi($sku_item_id)." and branch_id = ".mi($bid));
					$item_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					if($item_info['min_qty'] != $min_qty || $item_info['max_qty'] != $max_qty || $item_info['notify_user_id'] != $notify_user_id){
						$upd = array();
						$upd['min_qty'] = $min_qty;
						$upd['max_qty'] = $max_qty;
						$upd['moq_qty'] = $moq_qty;
						$upd['notify_user_id'] = $notify_user_id;
						$upd['last_update'] = "CURRENT_TIMESTAMP";
						
						$con->sql_query("update sku_items_po_reorder set ".mysql_update_by_field($upd)." where sku_item_id = ".mi($sku_item_id)." and branch_id = ".mi($bid));
						
						$upd_blist[$bid]['sku_item_id'] = $sku_item_id;
						$upd_blist[$bid]['bcode'] = $this->branch_list[$bid]['code'];
						$upd_blist[$bid]['old_min_qty'] = $item_info['min_qty'];
						$upd_blist[$bid]['min_qty'] = $min_qty;
						$upd_blist[$bid]['old_max_qty'] = $item_info['max_qty'];
						$upd_blist[$bid]['max_qty'] = $max_qty;
						$upd_blist[$bid]['old_moq_qty'] = $item_info['moq_qty'];
						$upd_blist[$bid]['moq_qty'] = $moq_qty;
						$upd_blist[$bid]['old_notify_user_id'] = $item_info['notify_user_id'];
						$upd_blist[$bid]['notify_user_id'] = $notify_user_id;
					}
				}
				
				// capture update log
				if($ins_blist){
					$logs .= "Inserted SKU Item [".$si_code."]";
					foreach($ins_blist as $tmp_bid => $r){
						$logs .= " SKU Item ID#".mi($r['sku_item_id'])." & BID#".mi($tmp_bid)." (Min Qty:".mi($r['min_qty']).", Max Qty:".mi($r['max_qty']).", Moq Qty:".mi($r['moq_qty']).", Notify User ID#".mi($r['notify_user_id']).")<br />";
					}
				}
				
				if($upd_blist){
					$logs .= "Updated SKU Item [".$si_code."]";
					foreach($upd_blist as $tmp_bid => $r){
						$logs .= " SKU Item ID#".mi($r['sku_item_id'])." & BID#".mi($tmp_bid)." (Min Qty:".mi($r['old_min_qty'])."=>".mi($r['min_qty']).", Max Qty:".mi($r['old_max_qty'])."=>".mi($r['max_qty']).",  Moq Qty:".mi($r['old_moq_qty'])."=>".mi($r['moq_qty']).", Notify User ID#".mi($r['old_notify_user_id'])."=>".mi($r['notify_user_id']).")<br />";
					}
				}
			}
		}
		$logs = substr_replace($logs,"",-6);
		if($logs) log_br($sessioninfo['id'], 'MASTER_PO_REORDER', $sid, $logs);			

		$tmp['ok'] = 1;
		$ret = $tmp;
		print json_encode($ret);
	}

	function validate_data($form){
		global $LANG;
		
		if(!$form) return;

		foreach($form['min_qty'] as $sid=>$bid_list){
			$si_code = $form['si_code'][$sid];
			
			$mm_err_blist = $user_err_blist = $moq_err_blist = array();
			foreach($bid_list as $bid=>$min_qty){
				$max_qty = $form['max_qty'][$sid][$bid];
				$moq_qty = $form['moq_qty'][$sid][$bid];
				$notify_user_id = $form['notify_user_id'][$sid][$bid];
				
				// found that the min qty is larger than max qty
				if(($min_qty || $max_qty) && $max_qty <= $min_qty){
					$mm_err_blist[] = $this->branch_list[$bid]['code'];
				}elseif(($moq_qty || $max_qty) && $max_qty < $moq_qty){
					$moq_err_blist[] = $this->branch_list[$bid]['code'];
				}elseif(!$min_qty && !$max_qty && $notify_user_id){ // found no set min & max but have notify person
					$user_err_blist[] = $this->branch_list[$bid]['code'];
				}
			}
			if($mm_err_blist) $err[] = $si_code." - ".sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "for Branch ".join(", ", $mm_err_blist));
			if($moq_err_blist) $err[] = $si_code." - ".sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "for Branch ".join(", ", $moq_err_blist));
			if($user_err_blist) $err[] = $si_code." - ".sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "for Branch ".join(", ", $user_err_blist));
		}
		
		return $err;
	}
}

$PO_REORDER_QTY_BY_BRANCH_MODULE = new PO_REORDER_QTY_BY_BRANCH_MODULE('PO Reorder Qty by Branch');
?>
