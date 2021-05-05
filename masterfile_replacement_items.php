<?php
/*
13/2/2017 10:26 AM Zhi Kai
- Change title of 'Replacement Item Master File' to 'Replacement Items'.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

4/1/2020 3:28 PM William
- Enhanced to insert id manually for ri_items table that use auto increment.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_SKU_RELP_ITEM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_RELP_ITEM', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect("Module is for HQ only", "/index.php");

//$maintenance->check(12);

class REPLACEMENT_ITEMS extends Module{
	var $table_pagesize = 30;
	
    function _default(){
        $this->load_group_list(true);
        $this->display();
    }
    
    function load_group_list($sqlonly = false){
		global $con, $smarty;
		$str = trim($_REQUEST['str']);
		$s = mi($_REQUEST['s']);
		$table_pagesize = $this->table_pagesize;
		
		if($str)    $filter[] = "ri.group_name like ".ms('%'.replace_special_char($str).'%');
		if($filter) $filter = "where ".join(' and ', $filter);
		else    $filter = '';
		
		$con->sql_query("select count(*) from ri $filter");
		$total_rows = $con->sql_fetchfield(0);
		$total_page = ceil($total_rows/$table_pagesize);
		if($s>$total_page)  $s = 0;
		$start_size = $s * $table_pagesize;
		
		$sql = "select ri.*,user.u,(select count(*) from ri_items where ri_items.ri_id=ri.id) as sku_count
		from ri
		left join user on user.id=ri.user_id
		$filter
		order by ri.group_name limit $start_size, $table_pagesize";
		//print $sql;
		$con->sql_query($sql);
		$smarty->assign('table', $con->sql_fetchrowset());
		$smarty->assign('total_page', $total_page);
		$smarty->assign('total_rows', $total_rows);
		
		if(!$sqlonly)   $this->display('masterfile_replacement_items.table.tpl');
	}
    
    function open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select * from ri where id=$id");
			$form = $con->sql_fetchrow();
			if($form){
				$con->sql_query("select rii.*,si.sku_item_code,si.description
				from ri_items rii
				left join sku_items si on si.id=rii.sku_item_id
				where ri_id=$id
				order by si.sku_item_code");
				$smarty->assign('items', $con->sql_fetchrowset());
				$smarty->assign('form', $form);
			}
		}
		$this->display('masterfile_replacement_items.open.tpl');
	}

	function ajax_add_item_row(){
        global $con, $smarty;

        $sku_item_id_arr = $_REQUEST['sku_code_list'];
        if(!$sku_item_id_arr)   return;

		$q1 = $con->sql_query("select * from sku_items where id in(".join(',', $sku_item_id_arr).")");
		while($r = $con->sql_fetchrow($q1)){
			print "<option value='".mi($r['id'])."'>$r[sku_item_code] - $r[description]</option>";
		}
	}
	
	function save_group(){
		global $con, $sessioninfo, $appCore;
		
		$upd = array();
		$id = mi($_REQUEST['id']);
		$upd['group_name'] = trim($_REQUEST['group_name']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$sku_item_id_list = $_REQUEST['sku_item_id_list'];
		$real_sku_item_id_list = array();
		if(!$upd['group_name']) die("Please enter Group Name.");

        if($sku_item_id_list){
		    foreach($sku_item_id_list as $key=>$sid){
				if(!mi($sid)||in_array($sid, $real_sku_item_id_list))   continue;
				
				$real_sku_item_id_list[] = $sid;
			}
		}
		
		if($real_sku_item_id_list){
            // check duplicate
		    $con->sql_query("select rii.*,si.sku_item_code
			from ri_items rii
			left join sku_items si on si.id=rii.sku_item_id
			where rii.sku_item_id in (".join(',', $real_sku_item_id_list).") and rii.ri_id<>$id");
		    $str = array();
		    while($r = $con->sql_fetchrow()){
				$str[] = $r['sku_item_code']." already used in other replacement item group.";
			}
			if($str){
				print join("\n", $str);
				exit;
			}
		}
		
		// create header
		if(!$id){
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['user_id'] = $sessioninfo['id'];
			$con->sql_query("insert into ri ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
		}else{
        	$con->sql_query("update ri set ".mysql_update_by_field($upd)." where id=$id");
		}
		// create items
		if($real_sku_item_id_list){
        	$con->sql_query("delete from ri_items where ri_id=$id");
			foreach($real_sku_item_id_list as $sid){
				$item = array();
				$item['id'] = $appCore->generateNewID("ri_items", "ri_id=".mi($id));
				$item['ri_id'] = $id;
				$item['sku_item_id'] = $sid;
				$con->sql_query("insert into ri_items ".mysql_insert_by_field($item));
			}
		}else   $con->sql_query("delete from ri_items where ri_id=$id");
		log_br($sessioninfo['id'], 'Replacement Item', $id, "Replacement Item ID# $id Saved.");
		print "OK";
	}
	
	function ajax_delete_group(){
		global $con, $sessioninfo;
		$id = mi($_REQUEST['id']);
		if(!$id)    die('Invalid Group ID');
		log_br($sessioninfo['id'], 'Replacement Item', $id, "Replacement Item ID# $id Deleted.");
		
		$con->sql_query("delete from ri_items where ri_id=$id");
		$con->sql_query("delete from ri where id=$id");
		print "OK";
	}
}

$REPLACEMENT_ITEMS = new REPLACEMENT_ITEMS('Replacement Items');
?>
