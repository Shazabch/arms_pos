<?
/*
Revision history
=================
12/4/2007 2:40:14 PM gary
- added adjustment approval flow.
11/3/2009 10:50:52 AM edward
- show all approval flow 'type' in view log.

1/15/2010 4:24:16 PM Andy
- add more approver order selection in settings

5/31/2010 2:54:17 PM Andy
- Add 2 New Approval Flow : Credit Note Approval and Debit Note Approval.(Need Privileges to Access)

8/18/2010 11:50:04 AM Justin
- Added new approval flow called "MEMBERSGIP_REDEMPTION"

6/6/2010 01:50:04 PM Justin
- Added new approval flow called "GOODS_RECEIVING_NOTE_VERIFY"

6/6/2010 02:50:04 PM Justin
- Deleted new approval flow called "GOODS_RECEIVING_NOTE_VERIFY" since no longer using

6/24/2011 3:27:03 PM Andy
- Make all branch default sort by sequence, code.

6/26/2012 3:06:21 PM Justin
- Fixed bug of showing sql error while access.

9/14/2012 10:16:00 PM
- Approval flows - change delimiter for Notify Users to ','

7/16/2013 4:49 PM Andy
- Enhance the edit approval to have selection of send PM/Email/SMS, and also minimum document amount.
- Change the search user to search by autocomplete.

10/22/2018 4:26 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

6/3/2019 9:42 AM Andy
- Added new approval flow "CYCLE COUNT".
*/
include("include/common.php");
$maintenance->check(208);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_APPROVAL', BRANCH_CODE), "/index.php");

// sets of approval flows
$flow_set = array (
	array("type" => "SKU_APPLICATION", 'description' => 'SKU APPLICATION'),
	array("type" => "ADJUSTMENT", 'description' => 'ADJUSTMENT'),
	array("type" => "PURCHASE_ORDER", 'description' => 'PURCHASE ORDER'),
	array("type" => "PURCHASE_ORDER_REQUEST", 'description' => 'PURCHASE ORDER REQUEST'),
	array("type" => "GOODS_RECEIVING_NOTE", 'description' => 'GOODS RECEIVING NOTE'),
	//array("type" => "GOODS_RETURN_ADVICE", "approvals"=>0),
	array("type" => "PROMOTION", 'description' => 'PROMOTION'),
	array("type" => "MEMBERSHIP_REDEMPTION", 'description' => 'MEMBERSHIP REDEMPTION'),
	//array("type" => "COUNTER_COLLECTION", 'description' => ''),
	array("type" => "CYCLE_COUNT", 'description' => 'CYCLE COUNT'),
);

if($config['customize_approval_flow']){
	$flow_set = array_merge($flow_set, $config['customize_approval_flow']);
}
$smarty->assign("flow_set", $flow_set);
$con->sql_query("select * from approval_order order by id");
while($r = $con->sql_fetchrow()){
  $aorder[$r['id']] = $r;
}
$smarty->assign("aorder", $aorder);

load_sku_type_list();

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
	    case 'ajax_reload_table':
	    
	        if ($_REQUEST['type'] != '')
				$sql = "approval_flow.type = " . ms($_REQUEST['type']);
   			else
   			    $sql = "approval_flow.type = 'SKU_APPLICATION'";

	        if (intval($_REQUEST['branch_id'])>0)
	        {
	            if ($sql != '') $sql .= ' and ';
				$sql .= ' branch_id = ' . mi($_REQUEST['branch_id']);
			}
	        if (intval($_REQUEST['sku_category_id'])>0)
	        {
	            if ($sql != '') $sql .= ' and ';
				$sql .= ' sku_category_id = ' . mi($_REQUEST['sku_category_id']);
			}
	        if ($_REQUEST['sku_type'] != 'ALL')
	        {
	            if ($sql != '') $sql .= ' and ';
				$sql .= ' sku_type = ' . ms($_REQUEST['sku_type']);
			}
   			load_table("where $sql");
			exit;
			
		case 'a':
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("insert into approval_flow " . mysql_insert_by_field($form, array('branch_id', 'type', 'aorder', 'approvals', 'notify_users', 'sku_category_id', 'sku_type')));
				$id = $con->sql_nextid();
				//select desc,dept and branch.
				$con->sql_query("select af.type, c.description, b.code from approval_flow af left join category c on af.sku_category_id = c.id left join branch b on b.id = af.branch_id where af.id=$id");
				$r = $con->sql_fetchrow();

				log_br($sessioninfo['id'], 'APPROVAL FLOW', $id, 'Create New:'.' ID#' . $id. "  /".$r['type'].'/   Dept: '.$r['description'].'/    Branch: '.$r['code']);
				//load_table();
				print "<script>parent.window.hidediv('ndiv');parent.window.reload_table();alert('$LANG[MSTAPPROVAL_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
			
		case 'e':
			$con->sql_query("select * from approval_flow where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid Approval-Flow ID: $id');</script>\n";
				exit;
			}
			$form = $con->sql_fetchrow();

			IRS_fill_form("f_b", array('branch_id', 'type', 'aorder', '_approvals', '_notify_users', 'sku_category_id', 'sku_type'), $form);
			print "<script>parent.window.aorder_changed();</script>";
			exit;
			
		case 'v':
			$con->sql_query("update approval_flow set active = ".mb($_REQUEST['v'])." where id = $id");
			//select desc,dept and branch.
			$con->sql_query("select af.type, c.description, b.code from approval_flow af left join category c on af.sku_category_id = c.id left join branch b on b.id = af.branch_id where af.id=$id");
			$r = $con->sql_fetchrow();

			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'APPROVAL FLOW', 0,' Activate: '.' ID#' . $id. "  /".$r['type'].'/   Dept: '.$r['description'].'/    Branch: '.$r['code']);
			else
				log_br($sessioninfo['id'], 'APPROVAL FLOW', 0,' Deactivate: '.' ID#' . $id. "  /".$r['type'].'/   Dept: '.$r['description'].'/    Branch: '.$r['code']);
			//load_table();
			print "<script>parent.window.reload_table();</script>";
			exit;
			
		case 'u':
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				//select desc,dept and branch.
				$con->sql_query("select af.type, c.description, b.code from approval_flow af left join category c on af.sku_category_id = c.id left join branch b on b.id = af.branch_id where af.id=$id");
				$r = $con->sql_fetchrow();
				// store basic info
				$con->sql_query("update approval_flow set branch_id = ".mi($form['branch_id']).", type = ".ms($form['type']).", aorder = ".mi($form['aorder']).", approvals = ".ms($form['approvals']).", notify_users = ".ms($form['notify_users']).", sku_category_id = " . mi($form['sku_category_id']) . ", sku_type = " .ms($form['sku_type']). " where id = $id");

				if ($con->sql_affectedrows())
				{
					// code changed
					$changes = "";
					//print "<script>alert('".($form["changed_fields"])."');</script>";
					foreach (preg_split("/\|/", $form["changed_fields"]) as $ff)
					{
						// strip array
						
						
						$ff = preg_replace("/\[.*\]/", '', $ff);
						
						if ($ff == 'src_approvals' || $ff == 'src_notify') continue;
						if ($ff != "") $uqf[$ff] = 1;
					}
    				//exit;
					$changes .= "\nEdited fields: " ."(".join(" ", array_keys($uqf)) . ")";

					log_br($sessioninfo['id'], 'APPROVAL FLOW', 0, 'Update information: ' .' ID#' . $id. "  /".$r['type'].'/   Dept: '.$r['description'].'/    Branch: '.$r['code']. $changes);
					//load_table();
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');parent.window.reload_table();alert('$LANG[MSTAPPROVAL_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;
		case 'open_approval_flow':
			open_approval_flow();
			exit;
		case 'ajax_add_approval_user':
			ajax_add_approval_user();
			exit;
		case 'ajax_add_notify_user':
			ajax_add_notify_user();
			exit;
		case 'ajax_save_approval_flow':
			ajax_save_approval_flow();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}


load_table("where approval_flow.type = 'SKU_APPLICATION'", true);

$con->sql_query("select id, code from branch where active=1 order by sequence, code");
$smarty->assign("branches", $con->sql_fetchrowset());
$con->sql_query("select id, u from user where active=1 and not template order by u");
$smarty->assign("users", $con->sql_fetchrowset());
$con->sql_query("select id, description from category where level=2 and active=1 order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

$smarty->assign("PAGE_TITLE", "Approval Flows");
$smarty->display("approval_flow_index.tpl");
exit;


function load_table($sql = '', $just_sql=false)
{
	global $con, $smarty, $flow_set;

	$rs = $con->sql_query("select approval_flow.*, branch.code as branch_code, category.description as dept from ((approval_flow left join branch on approval_flow.branch_id = branch.id) left join category on approval_flow.sku_category_id = category.id) $sql order by branch.sequence, branch.code, dept, approval_flow.type");
	$rr = array();
	while ($r = $con->sql_fetchrow($rs))
	{
	    /*switch($r['aorder']){
	       case 1: // follow sequence
          $r['approvals'] = get_user_list($r['approvals'], " > ");break;
         case 2:
          $r['approvals'] = get_user_list($r['approvals'], ", ");break;
         case 3:
          $r['approvals'] = get_user_list($r['approvals'], " | ");break;
        default:
          $r['approvals'] = get_user_list($r['approvals']);break;
      }*/ 
      $r['approvals'] = get_user_list($r['approvals'], " > ", $r['aorder'])
	                                                                        ;
	    $r['notify_users'] = get_user_list($r['notify_users'], ", ",0);
		array_push($rr, $r);
	}
	$smarty->assign("flows", $rr);
	
	$flow_desc = array();
	foreach ($flow_set as $fs) {
		$flow_desc[$fs['type']] = $fs['description'];
	}
	/*
	print '<pre>';
	print_r($flow_desc);
	print '</pre>';
	*/
	$smarty->assign("flow_desc", $flow_desc);
	
    if ($just_sql) return;
	$smarty->display("approval_flow_table.tpl");
}

function validate_data(&$form)
{
	global $LANG, $con, $id;
    $errm = array();
    $form['approvals'] = '|'.join('|', $form['sel_approvals']).'|';
    $form['notify_users'] = '|'.join('|', $form['sel_notify']).'|';
	/*if ($form['type'] != 'SKU_APPLICATION')
	{
		$form['sku_type'] = '';
	}*/

    $con->sql_query("select * from approval_flow where id <> $id and type = ".ms($form['type'])." and branch_id = ".mi($form['branch_id'])." and sku_category_id = " . mi($form['sku_category_id']) . " and sku_type = " .ms($form['sku_type']));
    
	if ($con->sql_numrows() > 0)
	{
		$errm[] = $LANG['MSTAPPROVAL_BRANCH_DEPT_DUPLICATE'];
	}
	return $errm;
}

function get_user_info($user_id_list){
	global $con, $smarty;
	
	if(!$user_id_list || !is_array($user_id_list))	return;
	
	$users_info = array();
	
	$con->sql_query("select user.id,user.u,branch.code as default_branch_code
					from user
					left join branch on branch.id=user.default_branch_id 
					where user.id in (".join(',', $user_id_list).")");
	while($r = $con->sql_fetchassoc()){
		$users_info[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign('users_info', $users_info);
	
	return $users_info;
}

function open_approval_flow(){
	global $con, $smarty;
	
	$id = mi($_REQUEST['id']);

	if($id > 0){
		$con->sql_query("select * from approval_flow where id = $id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$form) die("Invalid Approval Flow ID: $id");
		
		$form['approval_settings'] = unserialize($form['approval_settings']);
		
		$users_info = array();
		$user_id_list = array();
		
		if($form['approvals']){
			$tmp = explode("|", $form['approvals']);
			foreach($tmp as $uid){
				$uid = mi($uid);

				if($uid>0){
					$form['approvals_info'][$uid]['user_id'] = $uid;
					
					if(!in_array($uid, $user_id_list))	$user_id_list[] = $uid;
					
					$con->sql_query("select user.id,user.u,branch.code as default_branch_code
					from user
					left join branch on branch.id=user.default_branch_id 
					where user.id=$uid");
					$users_info[$uid] = $con->sql_fetchassoc();
					$con->sql_freeresult();
				}
			}
		}
		
		if($form['notify_users']){
			$tmp = explode("|", $form['notify_users']);
			foreach($tmp as $uid){
				$uid = mi($uid);

				if($uid>0){
					$form['notify_users_info'][$uid]['user_id'] = $uid;
					
					if(!in_array($uid, $user_id_list))	$user_id_list[] = $uid;
					
					if(!isset($users_info[$uid])){
						$con->sql_query("select user.id,user.u,branch.code as default_branch_code
						from user
						left join branch on branch.id=user.default_branch_id 
						where user.id=$uid");
						$users_info[$uid] = $con->sql_fetchassoc();
						$con->sql_freeresult();
					}					
				}
			}
		}
		
		if($user_id_list)	$users_info = get_user_info($user_id_list);
		//print_r($form);
	}else{
		// new 
		$form['approval_settings']['owner'] = array('pm'=>1, 'email'=>1, 'sms'=>1);
	}
	
	// load branches
	$branch_list = array();
	$con->sql_query("select id, code,active from branch order by sequence, code");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("branch_list", $branch_list);
	
	// load dept
	$dept_list = array();
	$con->sql_query("select id, description,active from category where level=2 order by description");
	while($r = $con->sql_fetchassoc()){
		$dept_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("dept_list", $dept_list);
	
	$smarty->assign('form', $form);
	$smarty->assign('users_info', $users_info);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('approval_flow.open.tpl');
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	
	print json_encode($ret);

}

function ajax_add_approval_user(){
	global $con, $smarty, $sessioninfo;
	
	$form = $_REQUEST;
	$user_id = mi($form['user_id']);
	if(!$user_id)	die('Invalid User');
	
	$ret = array();
	$ret['ok'] = 1;
	
	get_user_info(array($user_id));
	
	$form['approval_settings']['approval'][$user_id] = array('pm'=>1, 'email'=>1, 'sms'=>1);
	
	$smarty->assign('form', $form);
	$smarty->assign('user_id', $user_id);
	$ret['html'] = $smarty->fetch('approval_flow.open.approval_user.tpl');
	
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	print json_encode($ret);
}

function ajax_add_notify_user(){
	global $con, $smarty, $sessioninfo;
	
	$form = $_REQUEST;
	$user_id = mi($form['user_id']);
	if(!$user_id)	die('Invalid User');
	
	$ret = array();
	$ret['ok'] = 1;
	
	get_user_info(array($user_id));
	
	$form['approval_settings']['notify'][$user_id] = array('pm'=>1, 'email'=>1, 'sms'=>1);
	
	$smarty->assign('form', $form);
	$smarty->assign('user_id', $user_id);
	$ret['html'] = $smarty->fetch('approval_flow.open.notify_user.tpl');
	
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	print json_encode($ret);
}

function ajax_save_approval_flow(){
	global $con, $smarty, $sessioninfo, $LANG;
	
	$form = $_REQUEST;
	$id = mi($form['id']);
	//print_r($form);
	
	if($form['is_save_as'])	$id = 0;	// same as create new
	
	if(!$form['approval_user_id'])	$form['approval_user_id'] = array();
	if(!$form['notify_user_id'])	$form['notify_user_id'] = array();
	
	$upd = array();
	$upd['branch_id'] = $form['branch_id'];
	$upd['type'] = $form['type'];
	$upd['aorder'] = $form['aorder'];
	$upd['sku_category_id'] = $form['sku_category_id'];
	$upd['sku_type'] = $form['sku_type'];
	$upd['approvals'] = '|'.join('|', $form['approval_user_id']).'|';
    $upd['notify_users'] = '|'.join('|', $form['notify_user_id']).'|';
    $upd['approval_settings'] = $form['approval_settings'];
    
    $upd['approval_settings'] = serialize($upd['approval_settings']);
    
	// check duplicate
	$con->sql_query("select * from approval_flow where id <> $id and type = ".ms($upd['type'])." and branch_id = ".mi($upd['branch_id'])." and sku_category_id = " . mi($upd['sku_category_id']) . " and sku_type = " .ms($upd['sku_type']));
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
    
	if ($tmp)
	{
		die($LANG['MSTAPPROVAL_BRANCH_DEPT_DUPLICATE']);
	}
	
	
	if($id>0){	// exists
		$con->sql_query("update approval_flow set ".mysql_update_by_field($upd)." where id=$id");
		log_br($sessioninfo['id'], 'APPROVAL FLOW', $id, 'Update information: ' .' ID#' . $id. "  /".$upd['type'].'/   Dept ID: '.$upd['sku_category_id'].'/    Branch ID: '.$upd['branch_id']);
	}else{	// create new
		$con->sql_query("insert into approval_flow ".mysql_insert_by_field($upd));
		$id = $con->sql_nextid();
		log_br($sessioninfo['id'], 'APPROVAL FLOW', $id, 'Create New:'.' ID#' . $id. "  /".$upd['type'].'/   Dept ID: '.$upd['sku_category_id'].'/    Branch ID: '.$upd['branch_id']);
		
	}
	
	$ret = array();
	$ret['ok'] = 1;
	
	$ret = array_map(utf8_encode, $ret);	// must put this before json encode
	print json_encode($ret);
}
?>
