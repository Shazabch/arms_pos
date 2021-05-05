<?php
/*
9/15/2008 1:09:44 PM yinsee
- remove cost reloading during load_adj 
- tweak printing

6/23/5:00 PM Andy
- add checking on $config['adj_alt_print_template'] to allow custom print

1/13/2010 5:00:28 PM Andy
- Add config to allow adjustment customize print item per page

2/4/2010 11:02:40 AM Andy
- Add sql to select branch info when load adjustment

5/31/2010 4:11:20 PM Alex
- Add $config['reset_date_limit']

11/9/2010 10:56:54 AM Andy
- Add checking for canceled/deleted and prevent it to be edit.
- Change redirect code to use function.

1/25/2011 10:41:47 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

3/9/2011 2:33:03 PM Justin
- Added config to assign cost and selling price.

6/24/2011 2:47:31 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 10:45:02 AM Andy
- Fix when save, item missing if user open multiple adjustment.
- Fix auto clear all temp items when user enter adjustment list page.

7/27/2011 4:24:32 PM Justin
- Added to pick up sku item's doc decimal point.

9/30/2011 2:14:03 PM Andy
- Add check tmp version 89.

10/14/2011 10:36:32 AM Andy
- Fix adjustment duplicate items if user open multiple tab to create new adjustment.

10/27/2011 4:06:32 PM Justin
- Added to pick up timer id during load adjustment.
- Re-aligned some of the script to make it well align.

11/9/2011 11:32:53 AM Andy
- Add need user to click continue before they can insert adjustment item.
- Add to block user to change adjustment branch after they click "continue".

5/23/2012 12:24:23 PM Justin
- Removed some checking for update sku item cost since it will create bug.

8/7/2012 4:52 PM Justin
- Enhanced to capture packing uom code.

3/28/2013 12:02 PM Andy
- when reset adjustment, change to select sku_item_id to update changed=1 instead of using sub query.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/26/2013 2:14 PM Andy
- Enhance to load more_info when select approval history.

1/28/2014 11:39 AM Justin
- Enhanced to have serial no feature.

5/20/2014 10:28 AM Justin
- Bug fixed on system insert empty S/N into database.

2/10/2015 5:50 PM Andy
- Add global adjustment variable $adj_attachment_folder_name.

4/25/2017 5:31 PM Justin
- Enhanced branch group selection to filter by user's adjustment access permission.

6/2/2017 4:01 PM Justin
- Bug fixed on branch couldn't see from view mode if user doesn't have privilege for that branch.

6/7/2017 2:51 PM Justin
- Bug fixed on branch group will filter out all branches for consignment modules customers.
- Bug fixed on branch group will not align properly when it is consignment customers.

1/12/2018 3:11 PM Andy
- Enhanced to check work order when load adjustment.

10/30/2018 3:49 PM Justin
- Enhanced to get company no when loading adjustment.

9/3/2019 4:03 PM William
- Enhanced to added new function load_attachment_image to load the image list.
- check old file data using from database and copy the using data to new file.

1/8/2020 3:34 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

10/22/2020 5:49 PM Andy
- Added to get Old Code for Print Adjustment.
- Enhanced Adjustment Printing to can choose what sku fields to show (ARMS Code / MCode / Art No / Link Code).
*/
$maintenance->check(244);
$maintenance->check(89, true);

$adj_attachment_folder_name = 'adj_attachment';
if($smarty)	$smarty->assign('', $adj_attachment_folder_name);

function load_adj($use_tmp = true, $load = true, $check_owner = true,$approval_screen = false){
	global $con, $smarty, $sessioninfo, $branch_id, $LANG, $config, $item_timer_id, $adj_attachment_folder_name, $appCore;

	$adj_id=mi($_REQUEST['id']);
	if(!$item_timer_id)	$item_timer_id = time();	// mark current time
	
	if($adj_id>0 && $load){
		$q1=$con->sql_query("select adj.*, user.u as user, bah.approvals, b1.report_prefix, b1.description as b_description, b1.address as b_address, b1.phone_1 as b_phone_1, b1.phone_2 as b_phone_2, b1.phone_3 as b_phone_3, cat.description as dept, b1.company_no as b_company_no
		from adjustment adj 
		left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
		left join branch b1 on b1.id=adj.branch_id
		left join user on user.id=adj.cancelled_by
		left join category cat on cat.id=dept_id
		where adj.id=$adj_id and adj.branch_id=$branch_id");
    	$r1 = $con->sql_fetchrow($q1);		
    
		$sk=$con->sql_query("select sku_items.sku_item_code,sku_items.mcode,sku_items.description,sku_items.artno from sku_items left join adjustment_items on adjustment_items.sku_item_id = sku_items.id where adjustment_items.adjustment_id =".ms($adj_id)." and adjustment_items.branch_id=".ms($branch_id));
		
		$ex = $con->sql_fetchrow($sk);
	  
		$smarty->assign("ex",$ex);
	  
		if (!$r1){
			/*$smarty->assign("url", "/adjustment.php");
			$smarty->assign("title", "Adjustment");
			$smarty->assign("subject", sprintf($LANG['ADJUSTMENT_NOT_FOUND'], $adj_id));
			$smarty->display("redir.tpl");*/
			display_redir("/adjustment.php", "Adjustment", sprintf($LANG['ADJUSTMENT_NOT_FOUND'], $adj_id));
			exit;
		}
		
		if ($check_owner && $r1['user_id'] != $sessioninfo['id'] && $sessioninfo['level']<9999){
			/*$smarty->assign("url", "/adjustment.php");
			$smarty->assign("title", "Adjustment");
			$smarty->assign("subject", sprintf($LANG['ADJUSTMENT_NO_ACCESS'], $adj_id));
			$smarty->display("redir.tpl");*/
			display_redir("/adjustment.php", "Adjustment", sprintf($LANG['ADJUSTMENT_NO_ACCESS'], $adj_id));
			exit;
		}
		/*if (preg_match("/^\|$sessioninfo[id]\|/", $r1['approvals'])){
			$r1['is_approval'] = 1;
		}*/
		$params = array();
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $r1['approval_history_id'];
		$params['branch_id'] = $branch_id;
		$params['check_is_approval'] = true;
		$is_approval = check_is_last_approval_by_id($params, $con);
		if($is_approval)  $r1['is_approval'] = 1;
		
		if ($r1['approval_history_id']>0){
			$q0=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table = 'ADJUSTMENT' and i.branch_id = $branch_id and i.approval_history_id = $r1[approval_history_id] 
order by i.timestamp");
			$approval_history = array();
			while($r = $con->sql_fetchassoc($q0)){
				$r['more_info'] = unserialize($r['more_info']);
				$approval_history[] = $r;
			}
			$con->sql_freeresult($q0);
			$smarty->assign("approval_history", $approval_history);
		}		
		if($approval_screen){
			$r1['approval_screen']=1;	
		}			

		if(!$r1['timer_id']) $r1['timer_id'] = $item_timer_id;
    	
    	//echo"<pre>";print_r($r1);echo"</pre>";

		if($use_tmp){
			$con->sql_query("delete from tmp_adjustment_items where user_id = $sessioninfo[id] and adjustment_id=$adj_id and branch_id = $branch_id");
			$q2 = $con->sql_query("select * from adjustment_items where adjustment_id=$adj_id and branch_id = $branch_id order by id");	
			while($r2=$con->sql_fetchrow($q2)){				
				$r2['id'] = $appCore->generateNewID("tmp_adjustment_items", "branch_id=".mi($branch_id));
			    $r2['user_id'] = $sessioninfo['id'];
			    $r2['adjustment_id'] = $adj_id;
				$r2['timer_id'] = $item_timer_id;
			    
				$con->sql_query("insert into tmp_adjustment_items " . mysql_insert_by_field($r2, array('id', 'adjustment_id','branch_id','user_id','sku_item_id','cost','qty','selling_price','stock_balance', 'timer_id', 'serial_no')));
			}	
		}
		
		// work order
		if($r1['module_type'] == 'work_order'){
			// get work order id
			$con->sql_query("select id, wo_no from work_order where branch_id=$branch_id and adj_id=$adj_id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$r1['wo_id'] = mi($tmp['id']);
				$r1['wo_no'] = $tmp['wo_no'];
			}	
		}
		//check old image file data using from database and copy the using data to new image file
		$new_filepath = "new";
		if($r1['adj_attachment_name'] && !is_dir($adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$new_filepath)){
			//create a new file for old image files.
			mkdir($adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$new_filepath);
			chmod($adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$new_filepath,0777);
			$old_photo_list = load_attachment_image($branch_id,$adj_id,true);
			foreach($old_photo_list as $file){
				if($r1['adj_attachment_name'] == $file){
					$old_file = $adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$file;
					$new_file = $adj_attachment_folder_name."/".$branch_id."/".$adj_id."/".$new_filepath."/".$file;
					rename($old_file,$new_file);
				}
			}
		}
		
		//get all image data from new file
		$r1['adj_attachment_filename'] = load_attachment_image($branch_id,$adj_id);
	}else{
		$timer_id = $item_timer_id;
		
		// get the refresh data
		$refresh_data = array('branch_id','adjustment_date','adjustment_type','dept_id','remark');
		foreach($refresh_data as $tmp_col){
			if(isset($_REQUEST[$tmp_col]))	$r1[$tmp_col] = $_REQUEST[$tmp_col];
		}
    	$smarty->assign("timer_id", $timer_id);
	}
	$smarty->assign("form", $r1);

	if($use_tmp){
		/*$q3=$con->sql_query("select tai.*, sku_items.sku_item_code, sku_items.description as description, sku_items.artno, sku_items.mcode
from tmp_adjustment_items tai
left join sku_items on tai.sku_item_id=sku_items.id
where adjustment_id = $adj_id and tai.branch_id = $branch_id and user_id = $sessioninfo[id] order by tai.id");*/
		$filter_timer_id = $item_timer_id ? " and timer_id=".mi($item_timer_id) : '';

        $q3=$con->sql_query("select tai.*, si.sku_item_code, si.description as description, si.artno, si.mcode, si.doc_allow_decimal, 
							puom.code as packing_uom_code, sku.have_sn, si.link_code
							from tmp_adjustment_items tai
							left join sku_items si on tai.sku_item_id=si.id
							left join sku on sku.id = si.sku_id
							left join uom puom on puom.id = si.packing_uom_id
							where adjustment_id = $adj_id and user_id = $sessioninfo[id] $filter_timer_id order by tai.id");
	}else{
		$q3=$con->sql_query("select ai.*, si.sku_item_code, si.description as description, si.artno, si.mcode, si.doc_allow_decimal, 
							puom.code as packing_uom_code, sku.have_sn, si.link_code
							from adjustment_items ai
							left join sku_items si on ai.sku_item_id=si.id
							left join sku on sku.id = si.sku_id
							left join uom puom on puom.id = si.packing_uom_id
							where adjustment_id = $adj_id and ai.branch_id = $branch_id order by ai.id");
	}
	
	while($r = $con->sql_fetchassoc($q3)){
		$r['serial_no'] = unserialize($r['serial_no']);
		if($r['serial_no']){
			$temp_bid = array();
			$ttl_sn = 0;
			$sn_count = 0;

			$r['sn'] = $sn_list = explode("\n", $r['serial_no']);
			foreach($sn_list as $dummy=>$sn){
				if(trim($sn)) $sn_count++;
			}
			$r['ttl_sn'] = $sn_count;
		}
		
		$items[] = $r;
	}
	$con->sql_freeresult($q3);
	
	//$items = $con->sql_fetchrowset();
	//print_r($items);
	
    $smarty->assign("adjust_items", $items);
    $item_per_page = $config['adj_print_item_per_page']?$config['adj_print_item_per_page']:35;
  
	// added by andy  
	$con->sql_query("select * from branch where id=$branch_id");
	$smarty->assign('from_branch', $con->sql_fetchrow());

	if($config['adjustment_use_custom_print']){
		$smarty->assign('cost_enable', $_REQUEST['cost_enable']);
		$smarty->assign('sp_enable', $_REQUEST['sp_enable']);
	}

	if($_REQUEST['a']=='print'){
		// Default SKU Code if not selected
		if(!isset($_REQUEST['print_col']))	$_REQUEST['print_col'] = $config['adj_print_col_list'];
		
	    $smarty->assign("item_per_page", $item_per_page);
		$totalpage = ceil(count($items)/$item_per_page);
		for ($i=0,$page=1;$i<count($items);$i+=$item_per_page,$page++){
	        $smarty->assign("page", "Page $page of $totalpage");
	        $smarty->assign("start_counter", $i);
	        $smarty->assign("adjust_items", array_slice($items,$i,$item_per_page));
	        if($config['adj_alt_print_template'])   $smarty->display($config['adj_alt_print_template']);
			else	$smarty->display("adjustment.print.tpl");
			$smarty->assign("skip_header",1);
		}
		
		if(items){
			foreach($items as $key=>$r){
				$max_sn += count($r['serial_no']['sn']);
			}

			if($max_sn){
				$totalpage = ceil(count($max_sn)/$item_per_page);
				for($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
					$smarty->assign("PAGE_SIZE", $item_per_page);
					$smarty->assign("is_lastpage", ($page >= $totalpage));
					$smarty->assign("page", "Page $page of $totalpage");
					$smarty->assign("start_counter", $i);
					$tmp_items = array_slice($items,$i,$item_per_page);
					$smarty->assign("items", $tmp_items);
					
					$smarty->display("adjustment.sn.print.tpl");
				}
			}
		}
	}

    return $r1;
}

/* remove to standardized pm notification standard
function send_pm_to_user($adj_id,$aid,$status){

	global $con, $sessioninfo, $smarty, $branch_id, $approval_status;
	// get the PM list
	$con->sql_query("select notify_users 
	from branch_approval_history where id = $aid and branch_id = $branch_id");
	$r = $con->sql_fetchrow();
	
	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);
	
	// send pm
	send_pm($to, "Adjustment Approval (ID#$adj_id) $approval_status[$status]", "adjustment.php?a=view&id=$adj_id&branch_id=$branch_id");
}
*/

function init_selection(){
	global $con, $sessioninfo, $smarty, $depts, $config;
	// manager and above can see all department
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$depts = "id in (0)";
		else
			$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$depts = 1;
	}
	// show department option
	$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	
	/*
	$file_name="type_list.dat";
	$handle = fopen($file_name, "r");	
	while ($line = fgetcsv($handle,4096)){
		$tmp_name['name'] = trim($line[0]);
		$type_list[]=$tmp_name;
	}
	$smarty->assign("type_list", $type_list);
	*/

	$smarty->assign("type_list", $config['adjustment_type_list']);
}

function update_sku_item_cost($id,$branch_id){
	global $sessioninfo, $con;
	
	$sid_list = array();
	$con->sql_query("select distinct sku_item_id
	from adjustment_items
	where branch_id=$branch_id and adjustment_id=$id");
	while($r = $con->sql_fetchassoc()){
		$sid_list[] = mi($r['sku_item_id']);
	}
	$con->sql_freeresult();
	
	if($sid_list){
		$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',', $sid_list).")");
	}
}

function load_branch_group($id=0){
	global $con,$smarty, $sessioninfo, $branch_id, $config;

	$branch_group = array();

	// check whether select all or specified group
	$bg_filter = $bgi_filter = array();
	if($id>0){
		$bg_filter[] = "where id=".mi($id);
		$bgi_filter[] = "bgi.branch_group_id=".mi($id);
	}

	// have to filter with user's permission
	if(!$config['consignment_modules']){
		$q1 = $con->sql_query("select * 
							   from user_privilege 
							   where user_id = ".mi($sessioninfo['id'])." and privilege_code = 'ADJ' and allowed=1
							   group by branch_id");
		
		while($r = $con->sql_fetchassoc($q1)){
			$blist[] = $r['branch_id'];
		}
		$con->sql_freeresult($q1);
		
		if($branch_id && !in_array($branch_id, $blist)){
			$blist[] = $branch_id;
		}
		
		if($blist) $bgi_filter[] = "bgi.branch_id in (".join(",", $blist).")";
	}
	if($bgi_filter) $sub_filter = "where ".join(' and ', $bgi_filter);

	// load items
	$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id $sub_filter order by branch.sequence, branch.code",false,false);
	while($r = $con->sql_fetchassoc($q1)){
        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}

	$con->sql_freeresult($q1);
	
	if($branch_group['items']){
		$bg_filter[] = "id in (".join(",", array_keys($branch_group['items'])).")";
	
		if($bg_filter) $main_filter = "where ".join(' and ', $bg_filter);
		
		// load header
		$q1 = $con->sql_query("select * from branch_group $main_filter",false,false);

		if($con->sql_numrows($q1)<=0) return;
		while($r = $con->sql_fetchassoc($q1)){
			$branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}

	$smarty->assign('branch_group',$branch_group);
	return $branch_group;
}

function do_reset($adj_id,$branch_id){
	global $con,$sessioninfo,$config,$smarty,$LANG;
	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;

	if($sessioninfo['level']<$required_level){
        js_redirect(sprintf('Forbidden', 'Adjustment', BRANCH_CODE), "/adjustment.php");
	}

	//add reset config
	$check_date = strtotime($_REQUEST['adjustment_date']);

	if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));



		if ($check_date<$reset_limit){
		   	$errm['top'][] = $LANG['ADJUSTMENT_DATE_RESET_LIMIT'];
		   	$_REQUEST['a'] = "view";
			$smarty->assign('errm',$errm);
			
		
			return true;
		}

	}
	
	$form=load_adj();
	
	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];
	$status = 0;

	if($aid){
        $upd = array();
		$upd['approval_history_id'] = $aid;
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['status'] = $status;
		$upd['log'] = $_REQUEST['reason'];

		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		$con->sql_query("update branch_approval_history set status=$status where id = $aid and branch_id = $branch_id") or die(mysql_error());
	}
	

	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;
	
	update_sku_item_cost($adj_id,$branch_id);
	
	$con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where id=$adj_id and branch_id=$branch_id") or die(mysql_error());

    log_br($sessioninfo['id'], 'Adjustment', $adj_id, sprintf("Adjustment Reset (#$form[id])",$adj_id));
    
	header("Location: /adjustment.php?t=reset&save_id=$adj_id");
	exit;
}

function check_must_can_edit($branch_id, $adj_id, $is_approval_screen = false){
	global $con, $LANG;

    $con->sql_query("select active, status, approved from adjustment where branch_id=".mi($branch_id)." and id=".mi($adj_id));

	if($r = $con->sql_fetchrow()){  // invoice exists
		if(!$r['active']){  // inactive
            display_redir($_SERVER['PHP_SELF'], "Adjustment", sprintf($LANG['ADJUSTMENT_INACTIVE'], $adj_id));
		}elseif ($r['status']==4 || $r['status']==5){    // canceled or deleted
		    display_redir($_SERVER['PHP_SELF'], "Adjustment", sprintf($LANG['ADJUSTMENT_ALREADY_CANCELED_OR_DELETED'], $adj_id));
		}else{
		    if($is_approval_screen){
				if($r['approved']){    // confimred or approved
				    display_redir($_SERVER['PHP_SELF'], "Adjustment", sprintf($LANG['ADJUSTMENT_ALREADY_CONFIRM_OR_APPROVED'], $adj_id));
				}
			}elseif(($r['status']>0 && $r['status'] !=2) || $r['approved']){    // confimred or approved
			    display_redir($_SERVER['PHP_SELF'], "Adjustment", sprintf($LANG['ADJUSTMENT_ALREADY_CONFIRM_OR_APPROVED'], $adj_id));
			}
		}
	}else{
        display_redir($_SERVER['PHP_SELF'], "Adjustment", sprintf($LANG['ADJUSTMENT_NOT_FOUND'], $adj_id)); // invoice not found
	}
	$con->sql_freeresult();
}

function manage_serial_no($params){
	global $con, $config, $sessioninfo, $smarty;
	
	$id = $params['id'];
	$timer_id = $params['timer_id'];
	$bid = $params['branch_id'];
	$skip_error = $params['skip_error'];
	$use_tmp = $params['use_tmp'];
	$filters = array();
	
	// use tmp table data if found got set
	if($use_tmp){
		$item_tbl = "tmp_adjustment_items";
	}else $item_tbl = "adjustment_items";
	
	if(!$id){
		$filters[] = "timer_id = ".mi($timer_id);
	}else{
		$filters[] = "adjustment_id = ".mi($id);
	}
	
	$filter = join(" and ", $filters);
	
	if($params['skip_sn_error']){
		// check to make sure the following adjustment is fully approved
		$q1 = $con->sql_query("select * from adjustment where id = ".mi($id)." and branch_id = ".mi($bid)." and approved = 1 and active = 1 and status = 1");
		
		if($con->sql_numrows($q1) == 0) return; // found it is not being approved, exit...
		$con->sql_freeresult($q1);
	}
	
	// pickup all adjustment items which contains S/N 
	$q1 = $con->sql_query("select * from $item_tbl where $filter and branch_id = ".mi($bid)." and serial_no is not null and serial_no != ''");
	
	// check whether the S/N still match the condition
	$sn_list = $sn_error = array();
	if($con->sql_numrows($q1) > 0){
		while($r = $con->sql_fetchassoc($q1)){
			$r['serial_no'] = unserialize($r['serial_no']);

			$tmp_sn_list = explode("\n", $r['serial_no']);
			
			foreach($tmp_sn_list as $dummy=>$sn){
				$sn = trim($sn);
				if(!$sn) continue;
				$r['sn'] = $sn;
				$q2 = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($r['sku_item_id'])." and serial_no = ".ms($sn));
				$sn_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				if($r['qty'] > 0){ // need to insert S/N
					// check if the S/N has been inserted before approve
					if($sn_info){ // found it is already existed, capture it as error
						$sn_error['duplicate'][] = $sn;
						$duplicate_sn[] = $sn;
						continue;
					}
					
					$r['is_update'] = false;
					$r['is_insert'] = true;
				}else{ // need to update S/N to inactive
					if(!$sn_info){ // S/N info not found
						$sn_error['invalid'][] = $sn;
						continue;
					}elseif(!$sn_info['active']){ // S/N has been deactivate before this
						$sn_error['inactive'][] = $sn;
						continue;
					}elseif($sn_info['status'] == "Sold"){ // S/N has been sold
						$sn_error['sold'][] = $sn;
						continue;
					}
				
					$r['is_update'] = true;
					$r['is_insert'] = false;
				}
				
				$sn_list[] = $r;
			}
		}
	}
	
	if($sn_error && !$params['skip_sn_error']){ //duplicate S/N
		return $sn_error;
	}
	
	if(!$params['skip_sn_error']) return;
	
	// found having S/N that need to insert
	if($sn_list){
		foreach($sn_list as $dummy=>$sn_info){
			if($sn_info['is_insert']){ // insert as new S/N
				$ins = array();
				$ins['serial_no'] = $sn_info['sn'];
				$ins['sku_item_id'] = $sn_info['sku_item_id'];
				$ins['branch_id'] = $ins['located_branch_id'] = $sn_info['branch_id'];
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				$ins['created_by'] = $sessioninfo['id'];
				$ins['active'] = 1;
				$remarks = "Created from Adjustment";
				
				$q2 = $con->sql_query("insert into pos_items_sn ".mysql_insert_by_field($ins));
			}else{ // update S/N into inactive
				$ins = array();
				$ins['last_update'] = "CURRENT_TIMESTAMP";
				$ins['active'] = 0;
				$remarks = "Deactivated from Adjustment";
				
				$q2 = $con->sql_query("update pos_items_sn set ".mysql_update_by_field($ins)." where sku_item_id = ".mi($sn_info['sku_item_id'])." and serial_no = ".ms($sn_info['sn']));
			}
		
			if($con->sql_affectedrows($q1) > 0){
				// insert S/N history
				$his_ins = array();
				$his_ins['pisn_id'] = $sn_info['id'];
				$his_ins['branch_id'] = $his_ins['located_branch_id'] = $sn_info['branch_id'];
				$his_ins['sku_item_id'] = $sn_info['sku_item_id'];
				$his_ins['serial_no'] = $sn_info['sn'];
				$his_ins['remark'] = $remarks;
				$his_ins['status'] = "Available";
				$his_ins['active'] = $ins['active'];
				$his_ins['added'] = "CURRENT_TIMESTAMP";
				$his_ins['user_id'] = $sessioninfo['id'];

				$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
			}
		}
	}
	
	$con->sql_freeresult($q1);
}

//get attachment image from folder 
function load_attachment_image($branch_id, $adj_id, $is_oldfile = false){
	global $sessioninfo, $branch_id, $config, $adj_attachment_folder_name;
	if($sessioninfo['branch_id'] == $branch_id || $config['single_server_mode']){
		$server_path = '';
	}else{
		// manually get server path if user does not provide
		$server_path = get_image_path($branch_id);
	}
	if($server_path)	$server_path .= "/";
	if(!$is_oldfile) $new_filepath = "/new";
	$photo_list = array();
	$abs_path = $server_path.$adj_attachment_folder_name."/".$branch_id."/".$adj_id.$new_filepath;
	if($abs_path){
		foreach  (array_merge(glob("$abs_path/*.[jJ][pP][gG]"),glob("$abs_path/*.[jJ][pP][eE][gG]"),glob("$abs_path/*.[zZ][iI][pP]"),glob("$abs_path/*.[pP][dD][fF]")) as $f){
			$f = str_replace("$abs_path/", "", $f);
			$photo_list[] = $f;
		}
	}
	return $photo_list;
}
?>
