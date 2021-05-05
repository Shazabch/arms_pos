<?php
/*
9/1/2010 3:13:32 PM Andy
- Add allowed user list for SKU monitoring Group.
- Add Import by text for SKU monitoring Group.

1/27/2011 3:47:28 PM Andy
- Add department checking for SKU monitoring group items.
- Add filter available user list by branch.

6/24/2011 4:54:21 PM Andy
- Make all branch default sort by sequence, code.

13/2/2017 10:19:22 AM Zhi Kai
- Change the name of 'SKU Monitroing Group Master File' to ' SKU Monitoring Group'.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_SKU_MORN_GRP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_MORN_GRP', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect("Module is for HQ only", "/index.php");

include_once('masterfile_sku_monitoring_group.include.php');

class SKU_MONITORING_GROUP extends Module{
	var $table_pagesize = 30;
	
    function _default(){
		global $con;
        $this->load_group_list(true);
        $this->display();
    }
  
    function open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);

		$this->load_branches();
		
		// load department list
		$con->sql_query("select * from category where level=2 order by id");
		$smarty->assign('depts', $con->sql_fetchrowset());
		if($id){    // load group details
            $con->sql_query("select smg.*, c.description as dept_name
			from sku_monitoring_group smg
			left join category c on c.id=smg.dept_id
			where smg.id=$id");
			$form = $con->sql_fetchrow();
			$form['allowed_user'] = unserialize($form['allowed_user']);
			
			if($form){
				$con->sql_query("select smgi.*,si.sku_item_code,si.description
				from sku_monitoring_group_items smgi
				left join sku_items si on si.id=smgi.sku_item_id
				where sku_monitoring_group_id=$id
				order by si.sku_item_code");
				$smarty->assign('items', $con->sql_fetchrowset());
				$smarty->assign('form', $form);
				
				$smarty->assign('available_users', $this->load_available_users($form['dept_id']));
			}
		}
		$this->display('masterfile_sku_monitoring_group.open.tpl');
	}
	
	function load_group_list($sqlonly = false){
		global $con, $smarty;

        $str = trim($_REQUEST['str']);
		$s = mi($_REQUEST['s']);
		$table_pagesize = $this->table_pagesize;

		if($str)    $filter[] = "smg.group_name like ".ms('%'.$str.'%');
		if($filter) $filter = "where ".join(' and ', $filter);
		else    $filter = '';

		$con->sql_query("select count(*) from sku_monitoring_group smg $filter");
		$total_rows = $con->sql_fetchfield(0);
		$total_page = ceil($total_rows/$table_pagesize);
		if($s>$total_page)  $s = 0;
		$start_size = $s * $table_pagesize;
		
		$sql = "select smg.*,user.u,(select count(*) from sku_monitoring_group_items smgi where smgi.sku_monitoring_group_id=smg.id) as sku_count, c.description as dept_name
		from sku_monitoring_group smg
		left join user on user.id=smg.user_id
		left join category c on c.id=smg.dept_id
		$filter
		order by smg.group_name limit $start_size, $table_pagesize";
		
		$con->sql_query($sql);
		$smarty->assign('table', $con->sql_fetchrowset());
		$smarty->assign('total_page', $total_page);
		$smarty->assign('total_rows', $total_rows);
		if(!$sqlonly)   $this->display('masterfile_sku_monitoring_group.table.tpl');
	}
	
	private function load_branches(){
		global $con, $smarty;
		
	    $branches = array();
        $con->sql_query("select * from branch order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $branches);
	}
	
	function ajax_add_item_row($output = true){
        global $con, $smarty;

        $sku_item_id_arr = $_REQUEST['sku_code_list'];
        if(!$sku_item_id_arr)   return;

		$q1 = $con->sql_query("select * from sku_items where id in(".join(',', $sku_item_id_arr).")");
		while($r = $con->sql_fetchrow($q1)){
		    $ret .= "<option value='".mi($r['id'])."'>$r[sku_item_code] - $r[description]</option>";
		    
		}
		if($output)	print $ret;
		return $ret;
	}
	
	function save_group(){
		global $con, $sessioninfo;

		$upd = array();
		$id = mi($_REQUEST['id']);
		$upd['group_name'] = trim($_REQUEST['group_name']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['dept_id'] = mi($_REQUEST['dept_id']);
		$upd['start_monitoring_date'] = trim($_REQUEST['start_monitoring_date']);
		$upd['allowed_user'] = serialize($_REQUEST['allowed_user']);
		$upd['changed'] = 1;
		
		$sku_item_id_list = $_REQUEST['sku_item_id_list'];
		$real_sku_item_id_list = array();
		if(!$upd['group_name']) die("Please enter Group Name.");
		
		if(!strtotime($upd['start_monitoring_date']))  die("Please key in start monitoring date.");

        if($sku_item_id_list){
		    foreach($sku_item_id_list as $key=>$sid){
				if(!mi($sid)||in_array($sid, $real_sku_item_id_list))   continue;

				$real_sku_item_id_list[] = $sid;
			}
		}
		// create header
		if($real_sku_item_id_list){
            // check duplicate
		    $con->sql_query("select smgi.*,si.sku_item_code
			from sku_monitoring_group_items smgi
			left join sku_items si on si.id=smgi.sku_item_id
			where smgi.sku_item_id in (".join(',', $real_sku_item_id_list).") and smgi.sku_monitoring_group_id<>$id");
		    $str = array();
		    while($r = $con->sql_fetchrow()){
				$str[] = $r['sku_item_code']." already used in other replacement item group.";
			}
			if($str){
				print join("\n", $str);
				exit;
			}
		}
		
		if(!$id){
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['user_id'] = $sessioninfo['id'];
			$con->sql_query("insert into sku_monitoring_group ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
		}else{
        	$con->sql_query("update sku_monitoring_group set ".mysql_update_by_field($upd)." where id=$id");
		}
		
		// create items
		if($real_sku_item_id_list){
			$con->sql_query("delete from sku_monitoring_group_items where sku_monitoring_group_id=$id");
			foreach($real_sku_item_id_list as $sid){
				$item = array();
				$item['sku_monitoring_group_id'] = $id;
				$item['sku_item_id'] = $sid;
				$con->sql_query("insert into sku_monitoring_group_items ".mysql_insert_by_field($item));
			}
		}else   $con->sql_query("delete from sku_monitoring_group_items where sku_monitoring_group_id=$id");
		
		log_br($sessioninfo['id'], 'SKU Monitoring Group', $id, "SKU Monitoring Group ID# $id Saved.");
		print "OK";
	}
	
	function ajax_delete_group(){
		global $con, $sessioninfo;
		$id = mi($_REQUEST['id']);
		if(!$id)    die('Invalid Group ID');
		log_br($sessioninfo['id'], 'SKU Monitoring Group', $id, "SKU Monitoring Group ID# $id Deleted.");

		$con->sql_query("delete from sku_monitoring_group_items where sku_monitoring_group_id=$id");
		$con->sql_query("delete from sku_monitoring_group_batch_items where sku_monitoring_group_id=$id");
		$con->sql_query("delete from sku_monitoring_group where id=$id");
		print "OK";
	}

	function ajax_load_group_batch(){
        global $con, $sessioninfo, $smarty;
		$id = mi($_REQUEST['id']);
		if(!$id)    return; // invalid group id
		$con->sql_query("select * from sku_monitoring_group where id=$id");
		$form = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if(!$form)  return; // invalid
		
		// get batch list
		$con->sql_query("select sku_monitoring_group_id, year, month,count(*) as sku_count from sku_monitoring_group_batch_items where sku_monitoring_group_id=$id group by sku_monitoring_group_id, year,month order by year desc,month desc");
		$smarty->assign('batch_list', $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		// get sku list
		$con->sql_query("select smgbi.sku_monitoring_group_id, smgbi.sku_item_id, si.sku_item_code, si.artno,si.description, count(*) as batch_count
from sku_monitoring_group_batch_items smgbi
left join sku_items si on si.id=smgbi.sku_item_id
where smgbi.sku_monitoring_group_id=$id
group by smgbi.sku_item_id
order by si.sku_item_code");
		$smarty->assign('item_list', $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		$smarty->assign('form', $form);
		$this->display('masterfile_sku_monitoring_group.batch.tpl');
	}
	
	function regen_batch(){
		$id = mi($_REQUEST['id']);
		if(!$id)    die('Invalid Group ID');
		$success = regen_sku_monitoring_group_batch($id);
		if($success===true)	print "OK";
		else    print $success;
	}
	
	function ajax_load_batch_items(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		$y = mi($_REQUEST['year']);
		$m = mi($_REQUEST['month']);
		
		$sql = "select smgbi.sku_item_id,si.sku_item_code,si.description,si.artno
from sku_monitoring_group_batch_items smgbi
left join sku_items si on si.id=smgbi.sku_item_id
where sku_monitoring_group_id=$id and year=$y and month=$m";
		$con->sql_query($sql);
		$smarty->assign('item_list', $con->sql_fetchrowset());
		$con->sql_freeresult();
		$this->display('masterfile_sku_monitoring_group.batch.item_list.tpl');
	}
	
	function ajax_load_items_batch(){
        global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$sid = mi($_REQUEST['sid']);
		
		$sql = "select year, month
from sku_monitoring_group_batch_items smgbi
where sku_monitoring_group_id=$id and sku_item_id=$sid order by year desc,month desc";
		$con->sql_query($sql);
		$smarty->assign('batch_list', $con->sql_fetchrowset());
		$con->sql_freeresult();
		$this->display('masterfile_sku_monitoring_group.batch.item_list.tpl');
	}
	
	function ajax_import_sku_by_text(){
        global $con;
		$txt = trim($_REQUEST['txt']);
		$dept_id = mi($_REQUEST['dept_id']);
		if(!$dept_id)   die('Please select department.');
		
		if(!$txt){
			print "No item match";
			exit;
		}
		$temp = preg_split("/\s*[\n\r,]+\s*/", $txt);
		foreach($temp as $t){
		    if(trim($t)=='')    continue;
			$text_array[] = ms($t);
		}

		if(!$text_array)    die("No item match");
		
		$item_list = join(',',$text_array);
		$filter[] = "(si.mcode in ($item_list) or si.sku_item_code in ($item_list))";
		$filter[] = "c.department_id=$dept_id";
		$filter = join(' and ',$filter);
		$sql = "select si.id, si.sku_item_code, si.description
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		where $filter";
		$con->sql_query($sql);
		$count = 0;
		
		while($r = $con->sql_fetchrow()){
		    $sku_code_list[] = $r['id'];
			$count++;
		}
		if($count>0){
		    $_REQUEST['sku_code_list'] = $sku_code_list;
		    $ret['html'] = $this->ajax_add_item_row(false);
			$ret['msg'] ="$count item(s) matched";
		}else{
	        $ret['msg'] ="No item match";
		}
		
		print json_encode($ret);
	}

	function ajax_load_available_users(){
	    global $con, $smarty;
		$dept_id = mi($_REQUEST['dept_id']);
		$default_bid = mi($_REQUEST['default_bid']);
		$smg_id = mi($_REQUEST['smg_id']);
		
		if($smg_id){
			$con->sql_query("select allowed_user from sku_monitoring_group where id=$smg_id");
			$form = $con->sql_fetchrow();
			$form['allowed_user'] =unserialize($form['allowed_user']);
			$smarty->assign('allowed_user', $form['allowed_user']);
		}
		
		$smarty->assign('available_users', $this->load_available_users($dept_id, $default_bid));
		$this->display('masterfile_sku_monitoring_group.open.allowed_users.tpl');
	}
	
	private function load_available_users($dept_id, $default_bid = 0){
		global $con;
		$dept_id = mi($dept_id);
		if(!$dept_id)   return false;   // invalid dept id
		if($default_bid)    $filter[] = "default_branch_id=".mi($default_bid);
		$filter[] = "active=1 and level<9999 and departments like ".ms('%i:'.$dept_id.';%');
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select id, u from user $filter");
		$available_users = $con->sql_fetchrowset();
		$con->sql_freeresult();
		return $available_users;
	}
}

$SKU_MONITORING_GROUP = new SKU_MONITORING_GROUP('SKU Monitoring Group');
?>
