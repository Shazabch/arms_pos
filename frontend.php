<?
/*
Revision History
----------------
9 Apr 2007 - yinsee
- added branch_id column to counter_settings and counter_inventory table for synchronize

12/30/2010 4:34:50 PM Andy
- Check counter limit when adding new counter.

7/11/2011 3:56:59 PM Andy
- Replace htmlentities() to htmlspecialchars()

9/7/2011 2:02:41 PM Andy
- Add show counter name in LOG when active/deactive counter.

10/05/2011 11:31:09 AM Kee Kee
- Add Mprice Privilege in counter settings

10/13/2011 4:47:55 PM Andy
- Add setting to turn on/off "Print Receipt Reference Code".

11/09/2011 11:15:00 AM Kee Kee
- set "Print receipt reference code" default is not allow.

01/16/2012 5:34:00 PM Kee Kee
- set "Deposit Settings" default is not allow

03/13/2012 9:10:00 AM Kee Kee
- add "Return Policy" settings
- set "Return Policy" default is not allow
- set Allow to print Receipt Ref No as default.

08/07/2012 4:00 PM Kee Kee
- add "Adjust Member Point settings" in membership settings

11/12/2012 1:57 PM Kee Kee
- Added "Hold Bill Slot" in pos settings

1/24/2013 4:53 PM Andy
- Change to always check counter limit when add counter.

7/9/2013 2:04 PM Justin
- Enhanced while activating counter also check counter limit.

9/14/2015 3.25 PM DingRen
- add checking for edit function to prevent call from illegal way.

9/17/2015 5:43 PM Andy
- Rename from "Front End Setup" to "Counters Setup".

9/24/2015 4:40 PM DingRen
- Fix wrong prompt message

11/18/2015 2:24 PM Andy
- Fix check counter limit when active/inactivate counter.

01/21/2016 10:13 AM Edwin
- Change popup save/edit, reload table by using ajax
- Network name not allow to edit except user_id = 1
- Add temporary counter with date from/to.

4/5/2016 10:08 AM Andy
- Fix load wrong branch counter when edit.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

10/30/2017 5:53 PM Justin
- Enhanced to allow user to unset counter_status (need privilege).

11/1/2018 10:21 AM Justin
- Enhanced to avoid normal user from trying to modify temporary counter info.

9/28/2020 6:30 PM William
- Enhanced to pick up suite device list for fnb.

11/9/2020 5:04 PM William
- Enhanced to pick up suite device list for suite type is 'pos'.

02/16/2021 4:32 PM Rayleen
- Get current pos_setting for "is_self_checkout" if user is not admin to make sure current settings is not overwritten

3/16/2021 5:00 PM Shane
- Added default value "counter_pb_mode"

3/19/2021 3:34 PM Andy
- Enhanced to automatically get counter_settings max id when insert new counter.

4/16/2021 3:41 PM Shane
- Do not allow multiple counters of same branch to set as POS Backend Mode.

4/16/2021 3:57 PM Andy
- Increased maintenance checking to 495.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FRONTEND_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(495);

$smarty->assign("PAGE_TITLE", "Counters Setup");
foreach($config['sku_multiple_selling_price'] as $mprice)
{
	if(preg_match("/^(member)|(member[0-9])$/i",$mprice)==false)
	{
		$mp[] = $mprice;
	}
}
$smarty->assign("mprice",$mp);
$smarty->assign("mprice_colspan",count($mp));

if (isset($_REQUEST['a'])){
    switch ($_REQUEST['a'])
	{
        case 'open':
            open();
            exit;
        case 'save':
            save();
            exit;
        case 'toggle_status':
            toggle_status();
            exit;
        case 'load_table':
            load_table();
            exit;
		case 'check_counter_limit':
			check_counter_limit();
			exit;
		case 'ajax_location_list':
			ajax_location_list();
			exit;
		case 'ajax_unset_counter_status':
			ajax_unset_counter_status();
			exit;
        default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
    }
}

load_table(true);
$smarty->display("frontend.index.tpl");

function load_table($assign_only=false)
{
	global $con, $smarty, $sessioninfo;

	$q1 = $con->sql_query("select cs.*, ci.*, u.u as `current_user`, cst.id as cst_id
						   from counter_settings cs
						   left join counter_inventory ci on cs.id = ci.counter_id and cs.branch_id = ci.branch_id 
						   left join user u on u.id = ci.user_id 
						   left join counter_status cst on cst.id = cs.id and cst.branch_id = cs.branch_id
						   where cs.branch_id = ".mi($sessioninfo['branch_id'])."
						   order by cs.network_name");

	$records = array();
	while ($r = $con->sql_fetchassoc($q1))
	{
		$r['inventory'] = unserialize($r['inventory']);
		$r['pos_settings'] = unserialize($r['pos_settings']);
		$r['membership_settings'] = unserialize($r['membership_settings']);
		$r['mprice_settings'] = unserialize($r['mprice_settings']);
		//array_push($records, $r);
		$records[] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("counters", $records);
	if (!$assign_only) $smarty->display("frontend.table.tpl");
}

function open(){
    global $con, $smarty, $sessioninfo;
	$id = intval($_REQUEST['id']);
	$form['branch_id'] = intval($sessioninfo[branch_id]);
	
	// open an existing data
	if($id>0){
		// load header
		$con->sql_query("select * from counter_settings where id=$id and branch_id=".mi($form['branch_id'])) or die(mysql_error());
		$form = $con->sql_fetchrow();
		if(!$form){
			print "Error: Invalid Counter ID";
			exit;
		}
	}
	$form['membership_settings'] = unserialize($form['membership_settings']);
	$form['pos_settings'] = unserialize($form['pos_settings']);
	$form['mprice_settings'] = unserialize($form['mprice_settings']);

	//Default counter_pb_mode
	if(!isset($form['pos_settings']['counter_pb_mode'])){
		$form['pos_settings']['counter_pb_mode'] = -1;
	}

	$con->sql_query("select id, network_name from counter_settings where branch_id = $sessioninfo[branch_id] order by network_name");
	$invCounter = $con->sql_fetchrowset();
	$con->sql_freeresult();
	
	//device list
	$device_list = array();
	$con->sql_query("select guid, device_name from suite_device where device_type in('arms_fnb', 'pos') and branch_id=".mi($sessioninfo['branch_id']));
	while($r = $con->sql_fetchassoc()){
		$guid = $r['guid'];
		$device_list[$guid] = $r['device_name'];
	}
	$con->sql_freeresult();
	
	$smarty->assign('device_list', $device_list);
	$smarty->assign('invCounter', $invCounter);
	$smarty->assign('form', $form);
	$smarty->display('frontend.open.tpl');
}

function save() {
    global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;
    
    $form = $_REQUEST;
	$id = intval($form['id']);

    //check for error and store data in upd
    $err = validate_data($form);

	if($err){
		$err = implode("\n", $err);
		print $err; // got error
		return;
	}
    
	// means user is not admin, ensure the temporary counter information remain unchanged
	if($sessioninfo['id'] != 1){
		if($id > 0){
			$q1 = $con->sql_query("select * from counter_settings where id=".mi($id)." and branch_id=".mi($form['branch_id'])) or die(mysql_error());
			$counter_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$counter_info['pos_settings'] = unserialize($counter_info['pos_settings']);

			// in case user trying to use inspect element to change the value
			$form['pos_settings']['temporary_counter']['allow'] = $counter_info['pos_settings']['temporary_counter']['allow'];
			$form['pos_settings']['temporary_counter']['date_from'] = $counter_info['pos_settings']['temporary_counter']['date_from'];
			$form['pos_settings']['temporary_counter']['date_to'] = $counter_info['pos_settings']['temporary_counter']['date_to'];
			$form['pos_settings']['is_self_checkout'] = $counter_info['pos_settings']['is_self_checkout'];
			$form['counter_pb_mode'] = $counter_info['counter_pb_mode'];
		}else{
			unset($form['pos_settings']['temporary_counter']);
		}
	}

	if(!$form['pos_settings']['temporary_counter']['allow']) {
		$form['pos_settings']['temporary_counter']['allow'] = "0";
	}

	
    if($id>0) {
		
		$con->sql_query("select * from counter_settings where id = $id and branch_id = $sessioninfo[branch_id]");
		$old_form = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		$changed_fields = array();
		if($old_form['network_name'] != $form['network_name'])
			$changed_fields[] = "Network name: ".$old_form['network_name']." => ".$form['network_name'];
		if($old_form['location'] != $form['location'])
			$changed_fields[] = "Location: ".$old_form['location']." => ".$form['location'];
		if($old_form['membership_settings'] != serialize($form['membership_settings']))
			$changed_fields[] = "Membership Settings";
		if($old_form['pos_settings'] != serialize($form['pos_settings']))
			$changed_fields[] = "Pos Settings";
		if($old_form['mprice_settings'] != serialize($form['mprice_settings']))
			$changed_fields[] = "MPrice Settings";
		
		if($changed_fields){
			log_br($sessioninfo['id'], 'FRONTEND', $id, 'Counter update information, '. $old_form['network_name'] . ". Changes: ".join(",", $changed_fields));
		}
		$upd = array();
		if($sessioninfo['id'] == 1){
			$upd['network_name'] = $form['network_name'];
			$upd['suite_device_guid'] = $form['suite_device_guid'];
			$upd['counter_pb_mode'] = $form['counter_pb_mode'];
		}
		$upd['location'] = $form['location'];
		$upd['membership_settings'] = serialize($form['membership_settings']);
		$upd['pos_settings'] = serialize($form['pos_settings']);
		$upd['mprice_settings'] = serialize($form['mprice_settings']);
		
		$con->sql_query("update counter_settings set ".mysql_update_by_field($upd)." where id = $id and branch_id = $sessioninfo[branch_id]");
		
        print "Ok";
    }
    else {
		if(check_counter_limit()) {
			$ins = array();
			$ins['id'] = $appCore->generateNewID("counter_settings", "branch_id=".mi($sessioninfo['branch_id']));
			$ins['suite_device_guid'] = $form['suite_device_guid'];
			$ins['network_name'] = $form['network_name'];
			$ins['location'] = $form['location'];
			$ins['membership_settings'] = serialize($form['membership_settings']);
			$ins['pos_settings'] = serialize($form['pos_settings']);
			$ins['branch_id'] = $sessioninfo['branch_id'];
			$ins['mprice_settings'] = serialize($form['mprice_settings']);
			$ins['counter_pb_mode'] = $form['counter_pb_mode'];

			$con->sql_query("insert into counter_settings ".mysql_insert_by_field($ins));
			$new_counter_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'FRONTEND', $new_counter_id, 'Counter create ' . ms($form['network_name']));
            print "Ok";
        }
        else 
            print $LANG['MST_COUNTER_HIT_LIMIT'];
    }
}

function validate_data(&$form) {
    global $LANG, $con, $sessioninfo;
    
	$err = array();
    $form['network_name'] = strtoupper($form['network_name']);
    
    $con->sql_query("select * from counter_settings where branch_id = $sessioninfo[branch_id] and id <> ".intval($form['id'])." && network_name = " . ms($form['network_name']));
	if ($con->sql_numrows() > 0)
		 $err[] =sprintf($LANG['MST_NETWORK_NAME_DUPLICATE'], $form['network_name']);
	
	if($form['suite_device_guid']){
		$con->sql_query("select * from counter_settings where branch_id = $sessioninfo[branch_id] and id <> ".intval($form['id'])." and suite_device_guid = " . ms($form['suite_device_guid']));
		if ($con->sql_numrows() > 0) $err[] ="This Fnb device has been used.";
		$con->sql_freeresult();
	}

	if($form['counter_pb_mode']){
		$con->sql_query("select * from counter_settings where branch_id = $sessioninfo[branch_id] and id <> ".intval($form['id'])." and counter_pb_mode = 1");
		$tmp_err = array();
		while($r = $con->sql_fetchrow()){
			$tmp_err[] = $r['network_name'];
		}
		if($tmp_err){
			$err[] ="This branch already has POS Backend Counter (".implode(",",$tmp_err).").";
		}
		$con->sql_freeresult();
	}
		
	return $err;
}

function toggle_status() {
    global $con, $sessioninfo;
    
    $id = mi($_REQUEST['id']);
	$status = mi($_REQUEST['status']);
    $netw_name = $con->sql_fetchassoc($con->sql_query("select network_name from counter_settings where branch_id = $sessioninfo[branch_id] and id = $id"));
	$con->sql_freeresult();
	
    if($status == 0 || check_counter_limit()) {
        $con->sql_query("update counter_settings set active =$status where id = $id and branch_id = $sessioninfo[branch_id]") or die(mysql_error());
		
		if ($status == 0)
			log_br($sessioninfo['id'], 'FRONTEND', 0, 'Counter deactivate: Counter Name#'.$netw_name['network_name']);
		else
			log_br($sessioninfo['id'], 'FRONTEND', 0, 'Counter activate: Counter Name#'.$netw_name['network_name']);	
        print "Ok";
    }
	else
		print "Reached maximum counter limit, unable to activate.";
}

function check_counter_limit() {
    global $con, $sessioninfo;
    
    // get counter_limit
    $con->sql_query("select counter_limit from branch where id=".mi($sessioninfo['branch_id']));
    $counter_limit = mi($con->sql_fetchfield(0));
    $con->sql_freeresult();
    
    // check current added counter
	$con->sql_query("select count(*) from counter_settings where branch_id=".mi($sessioninfo['branch_id'])." and active=1");
	$curr_used = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
    
    if($curr_used>=$counter_limit)
		if(isset($_REQUEST['check']))
			print 'false';
		else
			return false;
		
    return true;
}
function ajax_location_list() {
	global $con, $smarty;
    
    $v = trim($_REQUEST['value']);

    $LIMIT = 50;
    // call with limit
	$result1 = $con->sql_query("select distinct(location) as location from counter_settings where location like ".ms('%'.replace_special_char($v).'%')." order by location limit ".($LIMIT+1));
    print "<ul>";
	if ($con->sql_numrows($result1) > 0)
	{
	    if ($con->sql_numrows($result1) > $LIMIT)
			print "<li><span class=informal>Showing first $LIMIT locations...</span></li>";

		// generate list.
		while ($r = $con->sql_fetchrow($result1))
		{
			$out .= "<li title=".htmlspecialchars($r['location'])."><span>".htmlspecialchars($r['location']);
			$out .= "</span>";
			$out .= "</li>";
		}
    }
    else
       print "<LI title=\"0\"><span class=informal>No Matches for $v</span></LI>";
	
	print $out;
    print "</li>";
	exit;
}

function ajax_unset_counter_status(){
	global $con, $smarty, $sessioninfo;
	
	$form = $_REQUEST;
	
	$q1 = $con->sql_query("select * from counter_status where id = ".mi($form['counter_id'])." and branch_id = ".mi($sessioninfo['branch_id']));
	
	if($con->sql_numrows($q1) > 0){
		$q2 = $con->sql_query("delete from counter_status where id = ".mi($form['counter_id'])." and branch_id = ".mi($sessioninfo['branch_id']));

		if($con->sql_affectedrows($q2) > 0){
			$ret = array();
			$ret['ok'] = 1;
			
			print json_encode($ret);
		}else die("Nothing to delete.");
	}else die("No Record Found.");
	$con->sql_freeresult($q1);
}
?>