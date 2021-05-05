<?php
/*

*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
//if (!privilege('MST_RETURN_POLICY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_RETURN_POLICY', BRANCH_CODE), "/index.php");
//include("masterfile_sa_commission.include.php");
$maintenance->check(130);

class MASTERFILE_RP_CONFIGURE extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(BRANCH_CODE != "HQ"){
			$b_filter = "branch_id in (".mi($sessioninfo['branch_id']).", 1)";
		}else $b_filter = "branch_id = ".mi($sessioninfo['branch_id']);
		
		$con->sql_query("select return_policy.*, b.code as branch_code from return_policy left join branch b on b.id = branch_id where return_policy.active=1 and $b_filter order by b.sequence, b.code, return_policy.title");

		while($r = $con->sql_fetchrow()){
			$rp_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('rp_list', $rp_list);
		
		$con->sql_query("select sku_group.*, b.code as branch_code from sku_group left join branch b on b.id = branch_id order by b.sequence, b.code, sku_group.code");
		while($r = $con->sql_fetchrow()){
			$sg_list[$r['sku_group_id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('sg_list', $sg_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		$sac_list = array();
		
		if(!$this->is_error){
			if(BRANCH_CODE != "HQ"){
				$b_filter = "branch_id in (".mi($sessioninfo['branch_id']).", 1)";
			}else $b_filter = "branch_id = ".mi($sessioninfo['branch_id']);
			$q1 = $con->sql_query("select * 
								   from return_policy_setup
								   where status=1 and $b_filter 
								   order by branch_id desc, id");
			while($r = $con->sql_fetchrow($q1)){
				$is_item = true;
				if(BRANCH_CODE != "HQ" && $r['branch_id'] == 1){
					if(!$existed_items[$r['ref_id']][$r['type']]){
						if(!$tmp_id) $tmp_id = strtotime(date("Y-m-d H:i:s"));
						else $tmp_id++;
						$r['id'] = $tmp_id;
						$r['from_hq'] = 1;
					}else{
						$is_item = false;
					}
				}else{
					$existed_items[$r['ref_id']][$r['type']] = 1;
				}

				if($r['type'] == 1){ // by category
					$q2 = $con->sql_query("select id as category_id, description as cat_desc from category where id=".mi($r['ref_id']));
				}elseif($r['type'] == 2){ // by sku item
					$q2 = $con->sql_query("select si.id as sku_item_id,si.sku_item_code,si.artno,si.description, si.is_parent
										   from sku_items si
										   where si.id=".mi($r['ref_id']));
				}else{ // by sku group
					$q2 = $con->sql_query("select sg.sku_group_id as sg_id, sg.branch_id as sg_branch_id, sg.code as sg_code, sg.description as sg_desc
										   from sku_group sg
										   where sg.sku_group_id=".mi($r['ref_id'])." and sg.branch_id=".mi($r['ref_branch_id']));
				}
				$info = $con->sql_fetchrow($q2);
				$r = array_merge($r, $info);

				$setup = array();
				$setup = unserialize($r['setup']);
				
				if($setup){
					foreach($setup as $member_type=>$f){
						if($f['id'] && $f['bid']){
							$rp_id = $f['id'];
							$rp_branch_id = $f['bid'];
							$q3 = $con->sql_query("select * from return_policy where id = ".mi($rp_id)." and branch_id = ".mi($rp_branch_id));
							$rp_info = $con->sql_fetchrow($q3);
							$branch_code = get_branch_code($rp_info['branch_id']);
							$setup[$member_type]['title'] = $rp_info['title'];
							$setup[$member_type]['branch_code'] = $branch_code;
						}elseif($f['id'] == "inherit"){
							$title = "";
							if($r['type'] >= 2){
								if($r['is_parent']) $title = " (Follow Category)";
								else $title = " (Follow SKU)";
							}
							$setup[$member_type]['title'] = ucfirst($f['id']).$title;
						}
					}
				}

				if(is_new_id($r['id'])){
					$r['hq_info']['setup'] = $setup;
					$r['setup'] = array();
				}else $r['setup'] = $setup;
	
				if($is_item){
					$items[] = $r;
				}else{
					foreach($items as $row=>$f){
						if($f['ref_id'] != $r['ref_id'] || $f['type'] != $r['type']) continue;
						
						$hq_info['hq_info'] = $r;
						$items[$row] = array_merge($items[$row], $hq_info);
					}
				}
			}
			$smarty->assign("items", $items);
		}

	    $this->display();
	}

	function ajax_toggle_commission_status(){
		global $con, $smarty, $sessioninfo;
		
		$con->sql_query("update sa_commission set active=".mi($_REQUEST['status'])." where id = ".mi($_REQUEST['id'])." and branch_id = ".mi($sessioninfo['branch_id']));

		if($con->sql_affectedrows() > 0){
			print "OK";
		}
	}
	
	function ajax_add_rp_configuration(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		
		$item = $info = array();
		if($form['condition_type'] == "cat"){ // add configuration by sku item
			$con->sql_query("select id as category_id, description as cat_desc, 1 as type
							 from category 
							 where id=".mi($form['category_id']));
		}elseif($form['condition_type'] == "si"){ // add configuration by category
			$con->sql_query("select si.id as sku_item_id,si.sku_item_code,si.artno,si.description, si.is_parent, 2 as type
							 from sku_items si
							 where si.id=".mi($form['sku_item_id']));
		}else{
			$sg_info = explode(",", $form['sg_id']);
			$sg_id = $sg_info[0];
			$sg_branch_id = $sg_info[1];
			$con->sql_query("select sg.sku_group_id as sg_id, sg.branch_id as sg_branch_id, sg.code as sg_code, sg.description as sg_desc, 3 as type
							 from sku_group sg
							 where sg.sku_group_id=".mi($sg_id)." and sg.branch_id=".mi($sg_branch_id));
		}
		$info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$id = strtotime(date("Y-m-d H:i:s"));
		$info['id'] = $id;
		$info['active'] = 1;
		$item = $info;

		if(is_array($item)){
			$smarty->assign("item", $item);
			$ret['ok'] = 1;
			$ret['id'] = $id;
			$ret['html'] = $smarty->fetch("masterfile_return_policy_configure.table.row.tpl");
		}else{
			$ret['err_msg'] = sprintf($LANG['SAC_ITEM_NOT_FOUND'], $id);
		}
		print json_encode($ret);
	}
	
	function save_rp_configuration(){
		global $con, $smarty, $sessioninfo;

		$form=$_REQUEST;
		$err = $this->validate($items);
		$is_deleted_items = array();

		if($err){
			$smarty->assign("err", $err);
			$smarty->assign("items", $items);
			$this->is_error = true;
			//$this->open_commission();
			//print_r($err);
		}else{
			foreach($form['setup'] as $id=>$member_type_list){
				if($form['is_deleted'][$id]){ // is not newly added items and it's being deleted
					if(is_new_id($id)) continue;
					$is_deleted_items[] = $id;
					continue;
				}

				unset($setup);
				foreach($member_type_list as $member_type=>$field){
					if(!$form['rp_id'][$id][$member_type]) continue;
					$setup[$member_type]['id'] = $form['rp_id'][$id][$member_type];
					$setup[$member_type]['bid'] = $form['rp_branch_id'][$id][$member_type];
				}
				if(count($setup)>0) $setup = serialize($setup);
				
				if(is_new_id($id)){ // insert commission item
					$ins = array();
					$ins['branch_id'] = $sessioninfo['branch_id'];
					$ins['ref_branch_id'] = $sessioninfo['branch_id'];
					if($form['category_id'][$id]){
						$ins['ref_id'] = $form['category_id'][$id];
						$ins['type'] = 1;
					}elseif($form['sku_item_id'][$id]){
						$ins['ref_id'] = $form['sku_item_id'][$id];
						$ins['type'] = 2;
					}else{
						$ins['ref_id'] = $form['sg_id'][$id];
						$ins['ref_branch_id'] = $form['sg_branch_id'][$id];
						$ins['type'] = 3;
					}
					$ins['setup'] = $setup;
					$ins['active'] = $form['active'][$id];
					$ins['user_id'] = $sessioninfo['id'];
					$ins['added'] = "CURRENT_TIMESTAMP";

					$con->sql_query("insert into return_policy_setup ".mysql_insert_by_field($ins));
					log_br($sessioninfo['id'], 'RETURN_POLICY_SETUP', $id, "Added R/P Setup Item: ID#".mi($id)." BRANCH_ID#".mi($sessioninfo['branch_id']));
				}else{ // update commission item
					$upd = array();
					$upd['setup'] = $setup;
					$upd['active'] = $form['active'][$id];
					$upd['last_update'] = "CURRENT_TIMESTAMP";
					$con->sql_query("update return_policy_setup set ".mysql_update_by_field($upd)." where id = ".mi($id)." and branch_id = ".mi($sessioninfo['branch_id']));
					if($con->sql_affectedrows()>0){
						log_br($sessioninfo['id'], 'RETURN_POLICY_SETUP', $id, "Updated R/P Setup Item: ID#".mi($id)." BRANCH_ID#".mi($sessioninfo['branch_id']));
					}
				}
			}
			
			if(count($is_deleted_items) > 0){
				$del_q = $con->sql_query("update return_policy_setup set status=0, last_update=CURRENT_TIMESTAMP where id in (".join(",", $is_deleted_items).") and branch_id = ".mi($sessioninfo['branch_id']));
				
				if($con->sql_affectedrows($del_q)>0){
					foreach($is_deleted_items as $r=>$del_id){
						log_br($sessioninfo['id'], 'RETURN_POLICY_SETUP', $del_id, "Deleted R/P Setup Item: ID#".mi($del_id)." BRANCH_ID#".mi($sessioninfo['branch_id']));
					}
				}
			}

			//$smarty->assign("status", "save");
			//$smarty->assign("id", $form['id']);
		}
		$this->_default();
	}

	function validate(&$items){
		global $con, $LANG, $sessioninfo;
		$form = $_REQUEST;
		
		$cat_id_list = $si_id_list = $sg_id_list = array();
		foreach($form['setup'] as $id=>$member_type_list){
			$item['id'] = $id;
			$item['type'] = $form['type'][$id];
			if($item['type'] == 1){
				$item['category_id'] = $form['category_id'][$id];
				$con->sql_query("select description from category where id=".mi($item['category_id']));
				$item['cat_desc'] = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}elseif($item['type'] == 2){
				$item['sku_item_id'] = $form['sku_item_id'][$id];
				$con->sql_query("select si.sku_item_code, artno, description from sku_items si where si.id=".mi($item['sku_item_id']));
				$item['sku_item_code'] = $con->sql_fetchfield(0);
				$item['artno'] = $con->sql_fetchfield(1);
				$item['description'] = $con->sql_fetchfield(2);
				$con->sql_freeresult();
			}else{
				$sg_info = explode(",", $form['sg_id'][$id]);
				$item['sg_id'] = $form['sg_id'][$id];
				$item['sg_branch_id'] = $form['sg_branch_id'][$id];
				$con->sql_query("select sg.code, description from sku_group sg where sg.sku_group_id=".mi($item['sg_id'])." and sg.branch_id = ".mi($item['sg_branch_id']));
				$item['sg_code'] = $con->sql_fetchfield(0);
				$item['sg_desc'] = $con->sql_fetchfield(1);
				$con->sql_freeresult();
			}
			$setup = array();
			foreach($member_type_list as $member_type=>$field){
				if(!$form['rp_id'][$id][$member_type]) continue;
				$setup[$member_type]['title'] = $form['setup'][$id][$member_type];
				$setup[$member_type]['id'] = $form['rp_id'][$id][$member_type];
				$setup[$member_type]['bid'] = $form['rp_branch_id'][$id][$member_type];
			}
			$item['setup'] = $setup;
			$item['is_deleted'] = $form['is_deleted'][$id];
			$item['is_parent'] = $form['is_parent'][$id];
			$item['active'] = $form['active'][$id];
			$item['status'] = $form['status'][$id];
			$item['from_hq'] = $form['from_hq'][$id];
			
			if(!$item['is_deleted']){
				// check whether the item is being duplicate or not
				if($item['type'] == 1){ // by category
					if($cat_id_list[$item['category_id']]) $err[] = sprintf($LANG['RPC_ITEM_EXISTED'], "Category", $item['cat_desc']);
					else $cat_id_list[$item['category_id']] = 1;
				}elseif($item['type'] == 2){ // by sku item
					if($si_id_list[$item['sku_item_id']]) $err[] = sprintf($LANG['RPC_ITEM_EXISTED'], "SKU Item", $item['sku_item_code']);
					else $si_id_list[$item['sku_item_id']] = 1;
				}else{ // by sku group
					if($sg_id_list[$item['sg_id']][$item['sg_branch_id']]) $err[] = sprintf($LANG['RPC_ITEM_EXISTED'], "SKU Group", $item['sg_code']." - ".$item['sg_desc']);
					else $sg_id_list[$item['sg_id']][$item['sg_branch_id']] = 1;
				}
			}
			
			$items[] = $item;
		}
		return $err;
	}

	function ajax_load_rp_list(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;

		if(BRANCH_CODE != "HQ"){
			$b_filter = "rp.branch_id in (".mi($sessioninfo['branch_id']).", 1)";
		}else $b_filter = "rp.branch_id = ".mi($sessioninfo['branch_id']);

		$q1 = $con->sql_query("select rp.*, b.code as branch_code from return_policy rp left join branch b on b.id = rp.branch_id where rp.active=1 and $b_filter order by b.sequence, b.code, rp.title");

		while($r = $con->sql_fetchrow($q1)){
			$rp_list[] = $r;
		}
		$con->sql_freeresult($q1);

		$smarty->assign("rp_list", $rp_list);
		$smarty->assign("id", $form['id']);
		$smarty->assign("mt", $form['mt']);
		$smarty->assign("type", $form['type']);
		$smarty->assign("is_parent", $form['is_parent']);
		$ret['html'] = $smarty->fetch("masterfile_return_policy_configure.rp_list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
}

$MASTERFILE_RP_CONFIGURE=new MASTERFILE_RP_CONFIGURE("Return Policy Configuration Master File");

?>
