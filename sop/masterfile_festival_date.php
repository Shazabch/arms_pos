<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SOP_MST_FEST_DATE')) js_redirect(sprintf($SOP_LANG['SOP_NO_PRIVILEGE'], 'SOP_MST_FEST_DATE'), "index.php");

include_once('festival_date.include.php');

class MASTERFILE_FESTIVAL_DATE extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load

		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;

		$this->display();
	}

	private function init_load(){
		global $con, $smarty;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select * from branch order by sequence", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
	}
	
	function open(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $year = mi($_REQUEST['year']);
	    if(!$year){
			header("Location: $_SERVER[PHP_SELF]");
			return;
		}
		
		open_festival_sheet($year);
	}
	
	function view(){
		$this->open();
	}
	
	function add_new_festival_sheet(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $year = mi($_REQUEST['year']);
        
        if($year<2010)  $err[] = "Year cannot less than 2010.";
        if($year>2099)  $err[] = "Year must start with 20xx.";
        
        // check duplicate
        $con->sql_query("select year from ".DATABASE_NAME.".festival_sheet where year=$year");
        if($con->sql_numrows()>0)   $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_DUPLICATE'], $year);
        $con->sql_freeresult();
        
        if($err)    sop_display_error($err);    // found error
        
        $upd = array();
        $upd['year'] = $year;
        $upd['user_id'] = $sessioninfo['id'];
        $upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
        $q_success = $con->sql_query("insert into ".DATABASE_NAME.".festival_sheet ".mysql_insert_by_field($upd));
        
        log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $id, "Festival Sheet added: Year#$year");
        
        $ret = array();
        $ret['ok'] = 1;
        print json_encode($ret);
	}
	
	function load_festival_list(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $filter = array();

		$t = mi($_REQUEST['t']);

		switch($t){
			case 1:	// saved
				$filter[] = "fest.status=0 and fest.approved=0";
				break;
			case 2: // waiting for approve
				$filter[] = "fest.status=1 and fest.approved=0";
				break;
            case 3: // rejected
			    $filter[] = "fest.status=2 and fest.approved=0";
			    break;
            case 4: // approved
			    $filter[] = "fest.status=1 and fest.approved=1";
			    break;
			case -1: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				break;
			default:
				die('Invalid Page');
		}
		
		// create filter string
		if($filter) $filter = "where ".join(' and ', $filter);
		else    $filter = '';

		$sql = "select fest.*, user.u as username, ah.approvals, ah.approval_order_id
			from ".DATABASE_NAME.".festival_sheet fest
			left join user on user.id=fest.user_id
			left join ".DATABASE_NAME.".approval_history ah on ah.id = fest.approval_history_id
			$filter
			order by fest.last_update desc";
		//print $sql;
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$festival_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('festival_list', $festival_list);

        $this->display('masterfile_festival_date.list.tpl');
	}
	
	function delete_festival(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $year = mi($_REQUEST['year']);
        
        $con->sql_query("delete from ".DATABASE_NAME.".festival_sheet where year=$year");
        if(!$con->sql_affectedrows()){
			$err[] = sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_NOT_FOUND'], $year);
		}
		if($err)    sop_display_error($err);
		
		log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $id, "Festival Sheet Deleted: Year#$year");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function open_festival_date(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $id = mi($_REQUEST['id']);
        $year = mi($_REQUEST['year']);
        
        if($id>0){
            $festival_date = load_festival_date($id);
            if(!$festival_date)	die(sprintf($SOP_LANG['SOP_FESTIVAL_DATE_INVALID_ID'], $id));   // no festival date
		}else{
		    $festival_date = array();
			$festival_date['year'] = $year;
		}
		
		$smarty->assign('festival_date', $festival_date);
		$this->display('masterfile_festival_date.open.festival_date_dialog.tpl');
	}

	function save_festival_date(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $id = mi($_REQUEST['id']);
        
        $upd = array();
        $upd['year'] = mi($_REQUEST['year']);
        $upd['title'] = trim($_REQUEST['title']);
        $upd['date_from'] = date("Y-m-d", strtotime($_REQUEST['date_from']));
        $upd['date_to'] = date("Y-m-d", strtotime($_REQUEST['date_to']));
        $upd['last_update'] = 'CURRENT_TIMESTAMP';
        
        // checking
        if(!$upd['year'])   $err[] = "Please enter year";
        if(!$upd['title'])   $err[] = "Please enter title";
        if(!$upd['date_from'])  $err[] = "Please enter date from";
		if(!$upd['date_to'])   $err[] = "Please enter date to";
		
		if($upd['date_from'] && $upd['date_to'] && strtotime($upd['date_to'])<strtotime($upd['date_from'])){
			$err[] = "\"Date to\" cannot early than \"Date from\"";
		}
		if($err){   // found error
			sop_display_error($err);
		}
		
		if($id>0){  // update
		    $con->sql_query("update ".DATABASE_NAME.".festival_date set ".mysql_update_by_field($upd)." where id=$id");
		}else{  // new
		    $upd['added'] = 'CURRENT_TIMESTAMP';
		    $upd['user_id'] = $sessioninfo['id'];
		    
		    $con->sql_query("insert into ".DATABASE_NAME.".festival_date ".mysql_insert_by_field($upd));
		    $id = mi($con->sql_nextid());
		    
		    // got error, adding new festival date failed
		    if(!$id)    sop_display_error(array('Unexpected Error: Failed to add new festival date'));
		    
            // assign new color
            $color_assigned = false;
            while(!$color_assigned){
                // get new color
                $color_array = get_random_color();
                $temp = array();
            	$temp['calendar_color'] = $color_array[0];

				// update color into mysql
            	$color_assigned = $con->sql_query("update ".DATABASE_NAME.".festival_date set ".mysql_update_by_field($temp)." where id=$id");
			}
		}
		
		log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $id, "Festival Date Saved: ID#$id");
		
		$ret['id'] = $id;
		$ret['html'] = load_festival_date($id, true);   // get the row html
		print json_encode($ret);    // return json to browser
	}
	
	function delete_festival_date(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $festival_date_id = mi($_REQUEST['festival_date_id']);
        
        if(!$festival_date_id)    $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_DATE_INVALID_ID'], $festival_date_id);

		if($err){   // found error
			sop_display_error($err);
		}
		
		$con->sql_query("delete from ".DATABASE_NAME.".festival_date where id=$festival_date_id");
		log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $festival_date_id, "Festival Date Deleted: ID#$festival_date_id");
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function update_festival_date_activation(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $festival_date_id = mi($_REQUEST['festival_date_id']);
		$active = mi($_REQUEST['active']);
		
        if(!$festival_date_id)    $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_DATE_INVALID_ID'], $festival_date_id);
		else{
			$con->sql_query("update ".DATABASE_NAME.".festival_date set active=$active where id=$festival_date_id");
			$affected = $con->sql_affectedrows();
			
			if(!$affected){
                $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_DATE_NOT_UPDATE'], $festival_date_id);
			}
		}
		// got error
        if($err)    sop_display_error($err);
        
        $status = $active ? 'Activated' : 'Deactivated';
		log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $festival_date_id, "Festival Date $status: ID#$festival_date_id");
		
		print "OK";
	}
	
	function confirm_festival_sheet(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $year = mi($_REQUEST['year']);
        
        $form = load_festival_header($year);
        if(!$form)  $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_NOT_FOUND'], $year);
        
        if($form && $form['status']>0)  $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_ALREADY_CONFIRM'], $year);
        
        if($form && !$err){
            // check approval flow
		    $params = array();
		    $params['branch_id'] = $sessioninfo['branch_id'];
		    $params['type'] = 'FESTIVAL_DATE';
		    $params['user_id'] = $sessioninfo['id'];
		    $params['reftable'] = 'festival_sheet';
		    $params['force_use_app_his'] = 1;
	        $params['database'] = DATABASE_NAME;

		    if($form['approval_history_id'])    $params['curr_flow_id'] = $form['approval_history_id'];
		    
		    $astat = check_and_create_approval2($params, $con); // get approval flow
		    $is_last_approval = false;
		    if(!$astat){    // approval flow not found
				$err[] = $SOP_LANG['SOP_FESTIVAL_YEAR_NO_APPROVAL_FLOW'];
			}
			else{   // approval flow found
				$form['approval_history_id'] = $astat[0]; // get the id
	   			if($astat[1]=='|') $is_last_approval=true;  // check whether is last approval
			}
	        if($is_last_approval){  // it is last approval
	            send_festival_sheet_approval_pm($form, 'Fully Approved');
	        }
		}
		
        // got error
        if($err)    sop_display_error($err);
        
        $upd = array();
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['status'] = 1;
	    $upd['approval_history_id'] = $form['approval_history_id'];
	    if($is_last_approval)   $upd['approved'] = 1;
	    
	    $con->sql_query("update ".DATABASE_NAME.".festival_sheet set ".mysql_update_by_field($upd)." where year=$year");

	    // record log file
        log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $year, "Festival Sheet Confirm: Year#".$year);

		$ret['ok'] = 1;
		if($is_last_approval)   $ret['approved'] = 1;
		print json_encode($ret);
	}
	
	private function reset_revoke_festival_sheet($year, $params){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $year = mi($year);
        
        $form = load_festival_header($year);
        if(!$form)  $err[] = sprintf($SOP_LANG['SOP_FESTIVAL_YEAR_NOT_FOUND'], $year);
        
        if($err)    sop_display_error($err);
        
        $approval_history_id = $form['approval_history_id'];
		$status = 0;

		if($approval_history_id){   // update approval flow
            $ahi = array();
			$ahi['approval_history_id'] = $approval_history_id;
			$ahi['user_id'] = $sessioninfo['id'];
			$ahi['status'] = $status;
			$ahi['log'] = $params['log'];

			$con->sql_query("insert into ".DATABASE_NAME.".approval_history_items ".mysql_insert_by_field($ahi));
			$con->sql_query("update ".DATABASE_NAME.".approval_history set status=$status , approved_by='', approvals='' where id = $approval_history_id");
		}


		$upd['status'] = $status;
		$upd['approved'] = 0;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $con->sql_query("update ".DATABASE_NAME.".festival_sheet set ".mysql_update_by_field($upd)." where year=$year");
	}
	
	function reset_festival_sheet(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $year = mi($_REQUEST['year']);
        $params['log'] = trim($_REQUEST['comment']);
	    $this->reset_revoke_festival_sheet($year, $params);

	    // record log file
        log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $year, "Festival Sheet Reset: Year#".$year);

        $ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function revoke_festival_sheet(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        $year = mi($_REQUEST['year']);
        $params['log'] = 'Revoke';
	    $this->reset_revoke_festival_sheet($year, $params);

	    // record log file
        log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $year, "Festival Sheet Revoke: Year#".$year);

        $ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function update_festival_date_calendar_color(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $festival_date_id = mi($_REQUEST['festival_date_id']);
        $new_color = trim($_REQUEST['new_color']);
        
        $festival_date = load_festival_date($festival_date_id);
        if(!$festival_date)	die(sprintf($SOP_LANG['SOP_FESTIVAL_DATE_INVALID_ID'], $festival_date_id));   // no festival date
        if(!$new_color) die($SOP_LANG['SOP_FESTIVAL_DATE_INVALID_CALENDAR_COLOR']);
        
        // check duplicate color
        $con->sql_query("select id from ".DATABASE_NAME.".festival_date where year=".mi($festival_date['year'])." and calendar_color=".ms($new_color)." and id<>$festival_date_id");
        if($con->sql_numrows()>0)   die(sprintf($SOP_LANG['SOP_FESTIVAL_DATE_CALENDAR_COLOR_USED'], $new_color));
        $con->sql_freeresult();
        
        // update
        $upd = array();
        $upd['calendar_color'] = $new_color;
        $con->sql_query("update ".DATABASE_NAME.".festival_date set ".mysql_update_by_field($upd)." where id=$festival_date_id");
        
        // record log file
        log_br($sessioninfo['id'], 'SOP MST FESTIVAL', $year, "Festival date ID#$festival_date_id calendar color change from $festival_date[calendar_color] to $new_color");
        print "OK";
	}
}

$MASTERFILE_FESTIVAL_DATE = new MASTERFILE_FESTIVAL_DATE('Festival Date Master File');
?>
