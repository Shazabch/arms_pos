<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!sop_check_privilege('SOP_YMP') && !sop_check_privilege('SOP_YMP_EDIT')) js_redirect(sprintf($SOP_LANG['SOP_NO_PRIVILEGE'], 'SOP_YMP'), "index.php");

include_once('yearly_marketing_plan.include.php');

class YEARLY_MARKETING_PLAN extends Module{
	var $allow_edit = false;
	var $branches = array();
	var $default_marketing_plan_list_size = 10;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(sop_check_privilege('SOP_YMP_EDIT'))	$this->allow_edit = true;
		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		
		$smarty->assign('allow_edit', $this->allow_edit);
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
	
	function load_marketing_plan_list($sqlonly = false){
		global $con, $smarty, $config, $sessioninfo;
		
		$filter = array();
		
		$t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']-1);  // always minus 1 so the rows start from 0
		if($p<0)    $p = 0; // prevent negative pagenum
        $size = $config['sop_yearly_marketing_plan_list_size']>0 ? $config['sop_yearly_marketing_plan_list_size']>0 : $this->default_marketing_plan_list_size;
		$start = $p*$size;
		
		$filter[] = "mar_p.active=1";
		switch($t){
			case 1:	// saved
				$filter[] = "mar_p.status=0 and mar_p.approved=0";
				break;
			case 2: // waiting for approve
				$filter[] = "mar_p.status=1 and mar_p.approved=0";
				break;
            case 3: // rejected
			    $filter[] = "mar_p.status=2 and mar_p.approved=0";
			    break;
			case 4: // terminated
			    $filter[] = "mar_p.status=5 and mar_p.approved=0";
			    break;
            case 5: // approved
			    $filter[] = "mar_p.status=1 and mar_p.approved=1";
			    break;
			case -1: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				break;
			default:
				die('Invalid Page');
		}
		
		// create filter string
		//if(BRANCH_CODE != 'HQ')	$filter[] = "mar_p.for_branch_id like ".ms('%"'.$sessioninfo['branch_id'].'"%');
		if($filter) $filter = "where ".join(' and ', $filter);
		else    $filter = '';
		
		// check total rows num
		$sql = "select count(*)
			from ".DATABASE_NAME.".marketing_plan mar_p
			left join user on user.id=mar_p.user_id
			$filter";
			//print $sql;
		$con->sql_query($sql);
		$total_rows = $con->sql_fetchfield(0);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 1;
		}
		$limit = "limit $start, $size";
        $total_page = ceil($total_rows/$size);
        
		$sql = "select mar_p.*, user.u as username, u2.u as last_update_by_username,ah.approvals, ah.approval_order_id
			from ".DATABASE_NAME.".marketing_plan mar_p
			left join user on user.id=mar_p.user_id
			left join user u2 on u2.id=mar_p.last_update_by
			left join ".DATABASE_NAME.".approval_history ah on ah.id = mar_p.approval_history_id
			$filter
			order by mar_p.last_update desc
			$limit";
		//print $sql;
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
		    $r['for_branch_id'] = unserialize($r['for_branch_id']);
			$marketing_plan_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('marketing_plan_list', $marketing_plan_list);
		$smarty->assign('total_page', $total_page);
		$smarty->assign('start_count', $start);
		
		if(!$sqlonly){
            $this->display('yearly_marketing_plan.marketing_plan_list.tpl');
		}
	}
	
	function open_marketing_plan($id = 0){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id>0){  // load marketing plan data
			$con->sql_query("select * from ".DATABASE_NAME.".marketing_plan where id=$id");
			$form = $con->sql_fetchassoc();
		}else{
		    $con->sql_query("select max(year) from ".DATABASE_NAME.".marketing_plan");  // get the max year
		    $max_year = mi($con->sql_fetchfield(0));
		    if(!$max_year)  $max_year = date('Y');
		    else    $max_year++;
		    
		    $form['title'] = $form['remark'] = "Marketing Plan ".$max_year;
		    $con->sql_freeresult();
		    
		    // default information
			$form['year'] = $max_year;
			$form['date_from'] = $max_year.'-01-01';
			$form['date_to'] = $max_year.'-12-31';
		}
		
		$smarty->assign('form', $form);
		$this->display('yearly_marketing_plan.dialog.tpl');
	}
	
	function save_marketing_plan(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		//print_r($_REQUEST);
		$form = $_REQUEST;
		
		// construct array to save
		$upd = array();
		$id = mi($form['id']);
		$upd['title'] = trim($form['title']);
		$upd['year'] = mi($form['year']);
		$upd['date_from'] = trim($form['date_from']);
		$upd['date_to'] = trim($form['date_to']);
		$upd['remark'] = trim($form['remark']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['last_update_by'] = $sessioninfo['id'];
		
		// validate form data
		$err = array();
		if(!$upd['title'])  $err[] = "Please enter title";
		if(!$upd['year'])   $err[] = "Please enter year";
		elseif($upd['year']<2008)   $err[] = "Year cannot less than 2008.";
		
		if(!$upd['date_from'])  $err[] = "Please enter date from";
		if(!$upd['date_to'])   $err[] = "Please enter date to";
		
		if($upd['date_from']&&$upd['date_to'] && strtotime($upd['date_to'])<strtotime($upd['date_from'])){
			$err[] = "\"Date to\" cannot early than \"Date from\"";
		}
		
		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
		if(!YMP_HQ_EDIT) $err[] = $SOP_LANG['HQ_ONLY'];
		
		// check duplicate year
		if(!$err){
			$con->sql_query("select id from ".DATABASE_NAME.".marketing_plan where year=".mi($upd['year'])." and id<>".mi($id));
			if($con->sql_numrows()>0)   $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_YEAR_ALREADY_USED'], $upd['year']);
			$con->sql_freeresult();
		}
		
		if($err){   // found error
			sop_display_error($err);
		}
		
		if($id){ // existing marketing plan, use update
		    $upd['id'] = $id;
		    // get old color
		    $con->sql_query("select calendar_color from ".DATABASE_NAME.".marketing_plan where id=".mi($upd['id']));
		    $calendar_color = $con->sql_fetchfield(0);
		    $con->sql_freeresult();

		    // update information
			$con->sql_query("update ".DATABASE_NAME.".marketing_plan set ".mysql_update_by_field($upd)." where id=$upd[id]");
		}else{  // new plan, use insert
			$upd['user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into ".DATABASE_NAME.".marketing_plan ".mysql_insert_by_field($upd));
			$upd['id'] = $con->sql_nextid();    // get the new id
		}
		
		// check calendar color, this is to prevent duplicate color show in calendar
		$is_unique_color = false;
		$new_calendar_color = $old_calendar_color = $calendar_color;
		
		while(!$new_calendar_color || !$is_unique_color){
		    if(!$new_calendar_color){   // if no color, assign it new color
		    	$color_array = get_random_color();
                $new_calendar_color = $color_array[0];
			}	
            // check whether got other marketing plan using this color
            $con->sql_query("select id from ".DATABASE_NAME.".marketing_plan where calendar_color=".ms($new_calendar_color)." and id<>".mi($upd['id'])." limit 1");
            
            $temp = $con->sql_fetchrow();
            $con->sql_freeresult();
            
            if(!$temp){
                $is_unique_color = true;
			}else{  // got others using this color, try new color
                $color_array = get_random_color();
                $new_calendar_color = $color_array[0];
			}
		}
		if($new_calendar_color != $old_calendar_color){    // found got color change
			$con->sql_query("update ".DATABASE_NAME.".marketing_plan set calendar_color=".ms($new_calendar_color)." where id=".mi($upd['id']));
		}
		log_br($sessioninfo['id'], 'SOP YMP', $upd['id'], "Marketing Plan Saved: ID#".$upd['id']);
		
		$ret['id'] = $upd['id'];
		
		print json_encode($ret);    // return json to browser
	}
	
	function delete_marketing_plan(){
		global $con, $smarty, $sessioninfo, $SOP_LANG;
		
		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
		if(!YMP_HQ_EDIT) $err[] = $SOP_LANG['HQ_ONLY'];
				
		$id = mi($_REQUEST['id']);
		if(!$id)    $err[] = "Invalid Marketing Plan ID";
		
		if($err){   // found error
			sop_display_error($err);
		}
		$con->sql_query("delete from ".DATABASE_NAME.".marketing_plan where id=$id");
		log_br($sessioninfo['id'], 'SOP YMP', $id, "Marketing Plan Deleted: ID#".$id);
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function open_marketing_plan_details(){
		global $con, $smarty, $SOP_LANG, $sessioninfo;

		$id = mi($_REQUEST['id']);
		if(!$id)    display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $id));  // invalid id

		$form = load_marketing_plan_header($id);   // load header

		if($form){
		    // inactive
			if(!$form['active'])    display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INACTIVE'], $id));
		}else{
		    // not found
            display_redir($_SERVER['PHP_SELF'], $this->title, sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $id));  // invalid id
		}

		// load promotion plan list
		$form['promotion_plan_list'] = load_promotion_plan_list($id);
		//print_r($form);
		$this->update_title($this->title.' - '.$form['title']);    // update title
		$smarty->assign('form', $form);
		$this->display("yearly_marketing_plan.marketing_plan_details.tpl");
	}

	function open_promotion_plan(){
    	global $con, $smarty, $SOP_LANG, $sessioninfo;

    	$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
    	$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		
    	if(!$marketing_plan_id) die(sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id));   // no marketing id given

    	// check marketing plan header
    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header

    	if(!$marketing_plan)    die(sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id));   // invalid marketing id
		if(!$marketing_plan['active'])  die(sprintf($SOP_LANG['SOP_MARKETING_PLAN_INACTIVE'], $marketing_plan_id)); // marketing plan inactive

		if($promotion_plan_id>0){   // existing promotion plan
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header

            if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));   // promotion not found
		}else{ // make new promotion
		    if(!YMP_HQ_EDIT) die($SOP_LANG['HQ_ONLY']);  // only hq user can create new promotion
		    
		    // get max id of promotion
			$con->sql_query("select count(*) from ".DATABASE_NAME.".marketing_plan_promotion where marketing_plan_id=$marketing_plan_id");
			$max_id = $con->sql_fetchfield(0);
			$con->sql_freeresult();

			// make promotion default title and date
			$promotion_plan['marketing_plan_id'] = $marketing_plan_id;
			$promotion_plan['title'] = $promotion_plan['description'] = 'Promotion Plan #'.($max_id+1);
			$promotion_plan['date_from'] = $marketing_plan['date_from'];
			$promotion_plan['date_to'] = $marketing_plan['date_to'];
   			$promotion_plan['active'] = 1;
		}

		//print_r($promotion_plan);
		$smarty->assign('marketing_plan', $marketing_plan);
		$smarty->assign('promotion_plan', $promotion_plan);
		$this->display('yearly_marketing_plan.marketing_plan_details.promotion_dialog.tpl');
	}

	function save_promotion_plan(){
		global $con, $smarty, $SOP_LANG, $sessioninfo;

		$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		
		$form = $_REQUEST;

		// construct array to save
		$upd = array();
		$upd['title'] = trim($form['title']);
		$upd['date_from'] = trim($form['date_from']);
		$upd['date_to'] = trim($form['date_to']);
		$upd['description'] = trim($form['description']);
		$upd['for_branch_id_list'] = ($form['for_branch_id_list']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';

        // validate form data
		$err = array();
		if(!$upd['title'])  $err[] = "Please enter title";
		if(!$upd['date_from'])  $err[] = "Please enter date from";
		if(!$upd['date_to'])   $err[] = "Please enter date to";
		if(!$upd['for_branch_id_list'])   $err[] = "Please enter branch";

		if($upd['date_from']&&$upd['date_to'] && strtotime($upd['date_to'])<strtotime($upd['date_from'])){
			$err[] = "\"Date to\" cannot early than \"Date from\"";
		}
		
		if(!$marketing_plan_id) sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);

		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
		if(!YMP_HQ_EDIT)    $err[] = $SOP_LANG['HQ_ONLY'];
		
		if(!$upd['for_branch_id_list']) $upd['for_branch_id_list'] = array();
		if(!$err){
			if($promotion_plan_id>0){
                $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
                // promotion not found
            	if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));
				   
            	// check branch own details
            	if($promotion_plan['branch_own_info']){
					foreach($promotion_plan['branch_own_info'] as $bid=>$r){
						if(!in_array($bid, $upd['for_branch_id_list'])){
                            $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_BRANCH_USED'], $this->branches[$bid]['code']);
						} 
					}
				}
			}
		}
		if($err){   // found error
			sop_display_error($err);
		}

		// serialize
        $upd['for_branch_id_list'] = serialize($upd['for_branch_id_list']);
        
        if($promotion_plan_id>0){ // existing, use update
		    // get old color
		    $con->sql_query("select calendar_color from ".DATABASE_NAME.".marketing_plan_promotion where id=$promotion_plan_id");
		    $calendar_color = $con->sql_fetchfield(0);
		    $con->sql_freeresult();

		    // update information
			$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set ".mysql_update_by_field($upd)." where id=$promotion_plan_id and marketing_plan_id=$marketing_plan_id");
		}else{  // new, use insert
			$upd['created_by_user_id'] = $sessioninfo['id'];
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$upd['marketing_plan_id'] = $marketing_plan_id;
            
			$con->sql_query("insert into ".DATABASE_NAME.".marketing_plan_promotion ".mysql_insert_by_field($upd));
			$promotion_plan_id = $con->sql_nextid();    // get the new id
		}
		$upd['id'] = $promotion_plan_id;

		// check calendar color, this is to prevent duplicate color show in calendar
		$is_unique_color = false;
		$new_calendar_color = $old_calendar_color = $calendar_color;

		while(!$new_calendar_color || !$is_unique_color){
		    if(!$new_calendar_color){   // if no color, assign it new color
		    	$color_array = get_random_color();
                $new_calendar_color = $color_array[0];
			}
            // check whether got other promotion plan using this color
            $con->sql_query("select id from ".DATABASE_NAME.".marketing_plan_promotion where calendar_color=".ms($new_calendar_color)." and marketing_plan_id=$marketing_plan_id and id<>$promotion_plan_id limit 1");

            $temp = $con->sql_fetchrow();
            $con->sql_freeresult();

            if(!$temp){
                $is_unique_color = true;
			}else{  // got others using this color, try new color
                $color_array = get_random_color();
                $new_calendar_color = $color_array[0];
			}
		}
		if($new_calendar_color != $old_calendar_color){    // found got color change
			$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set calendar_color=".ms($new_calendar_color)." where marketing_plan_id=$marketing_plan_id and id=$promotion_plan_id");
		}
		
		log_br($sessioninfo['id'], 'SOP YMP', $upd['id'], "Marketing Promotion Plan Saved: Promotion Plan ID#$promotion_plan_id");

		$smarty->assign('no_branch_data_row', 1);   // dont show branch data row
		$ret['id'] = $promotion_plan_id;
		$ret['html'] = load_promotion_plan_header($promotion_plan_id, true);   // get the row html

		print json_encode($ret);    // return json to browser
	}

	function update_promotion_activation(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
		if(!YMP_HQ_EDIT)    $err[] = $SOP_LANG['HQ_ONLY'];
		
	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		
		$active = mi($_REQUEST['active']);
		
		// validate
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}

        if(!$promotion_plan_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);

        if($err){   // found error
			sop_display_error($err);
		}

		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set active=$active where marketing_plan_id=$marketing_plan_id and id=$promotion_plan_id");
		$status = $active ? 'Activated' : 'Deactivated';
		log_br($sessioninfo['id'], 'SOP YMP', $promotion_plan_id, "Promotion $status: Promotion Plan ID#$promotion_plan_id");

        if($marketing_plan['approved']){    // regenerate user activity if this marketing plan is approved
            $usr_act = array();
			$usr_act['marketing_plan_id'] = $marketing_plan_id;
			$usr_act['promotion_plan_id'] = $promotion_plan_id;
			generate_user_activity_table($usr_act); // regenerate user activity entry
		}

	    print "OK";
	}

	function delete_promotion_plan(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
		if(!YMP_HQ_EDIT)    $err[] = $SOP_LANG['HQ_ONLY'];
		
		$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
        
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}

		if($err){   // found error
			sop_display_error($err);
		}
		
		$filter = array();
		$filter[] = "marketing_plan_id=$marketing_plan_id";
		$filter[] = "id=$promotion_plan_id";
		$filter = "where ".join(' and ', $filter);
		
		// delete promotion
		$con->sql_query("delete from ".DATABASE_NAME.".marketing_plan_promotion $filter");
		log_br($sessioninfo['id'], 'SOP YMP', $id, "Promotion Plan Deleted: Promotion Plan ID#$promotion_plan_id");
		
		// delete activities...
		/*$q_act = $con->sql_query("select reference_id, created_branch_id, id
		from sop_marketing_plan_promotion_activity
		where sop_marketing_plan_created_branch_id=$created_branch_id and sop_promotion_plan_id=$promotion_plan_id");
		while($r = $con->sql_fetchassoc($q_act)){
		    if($marketing_plan['approved']){
				// delete from user activity
				$con->sql_query("delete from sop_marketing_plan_promotion_user_activity where activity_reference_id=".ms($r['reference_id']));
			}
			// delete activity
			$con->sql_query("delete from sop_marketing_plan_promotion_activity where reference_id=".ms($r['reference_id']));
			
			// delete activity cache
			$con->sql_query("delete from sop_marketing_plan_promotion_activity_cache where created_branch_id=".mi($r['created_branch_id'])." and sop_activity_id=".mi($r['id']));
		}
		$con->sql_freeresult($q_act);*/

		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function open_promotion_plan_own_details(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    // escape integer
	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
        $branch_id = mi($_REQUEST['branch_id']);
        
        $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
        if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));   // promotion not found
        
        if($branch_id){ // edit
            if(!YMP_HQ_EDIT && !in_array($branch_id, $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])){    // no privilege to edit
				die($SOP_LANG['HQ_ONLY']);
			}
            $promotion_plan_own_data = $promotion_plan['branch_own_info'][$branch_id];
            if(!$promotion_plan_own_data)   die('Branch Info not found.');
		}else{  // add new branch own data
            if($promotion_plan['for_branch_id_list']){  // check available branches
                if(count($promotion_plan['for_branch_id_list'])<=1){
					die('There are only 1 branch in this promotion, you no need to create own branch data.');
				}
				foreach($promotion_plan['for_branch_id_list'] as $bid){
					if(!isset($promotion_plan['branch_own_info'][$bid])){
	                    if(YMP_HQ_EDIT || in_array($bid, $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])){
	                        $promotion_plan_own_data['available_branches'][] = $bid;
						}
					}
				}
			}else{  // no available branch
				die('No Available Branch Found!');
			}
			
			//$promotion_plan_own_data['title'] = $promotion_plan['title'];
            $promotion_plan_own_data['date_from'] = $promotion_plan['date_from'];
            $promotion_plan_own_data['date_to'] = $promotion_plan['date_to'];
            //$promotion_plan_own_data['description'] = $promotion_plan['description'];
            $promotion_plan_own_data['promotion_plan_id'] = $promotion_plan_id;
		}
        
		//print_r($promotion_plan_own_data);
		$smarty->assign('promotion_plan_own_data', $promotion_plan_own_data);
		$this->display('yearly_marketing_plan.marketing_plan_details.promotion_own_details_dialog.tpl');
	}
	
	function save_promotion_plan_own_details(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
		$branch_id = mi($_REQUEST['branch_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		
		$form = $_REQUEST;

		// construct array to save
		$upd = array();
		$upd['branch_id'] = $branch_id;
		$upd['promotion_plan_id'] = $promotion_plan_id;
		//$upd['title'] = trim($form['title']);
		$upd['date_from'] = trim($form['date_from']);
		$upd['date_to'] = trim($form['date_to']);
		//$upd['description'] = trim($form['description']);
		$upd['last_update'] = date('Y-m-d H:i:s');
		$upd['last_update_by_user_id'] = $sessioninfo['id'];
		$upd['last_update_by_user_u'] = $sessioninfo['u'];
		
        // validate form data
		$err = array();
		if(!$upd['branch_id'])  $err[] = "Invalid branch id";
		//if(!$upd['title'])  $err[] = "Please enter title";
		if(!$upd['date_from'])  $err[] = "Please enter date from";
		if(!$upd['date_to'])   $err[] = "Please enter date to";

		if($upd['date_from']&&$upd['date_to'] && strtotime($upd['date_to'])<strtotime($upd['date_from'])){
			$err[] = "\"Date to\" cannot early than \"Date from\"";
		}

		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
        if(!YMP_HQ_EDIT && !in_array($branch_id, $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])) $err[] = $SOP_LANG['HQ_ONLY'];
        
        $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
        if(!$promotion_plan)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);   // promotion not found
        
		if($err){   // found error
			sop_display_error($err);
		}

        if(!isset($promotion_plan['branch_own_info'][$branch_id])){   // new information
            $upd['added'] = date('Y-m-d H:i:s');
            $upd['created_by_user_u'] = $sessioninfo['u'];
            $upd['created_by_user_id'] = $sessioninfo['id'];
            $upd['active'] = 1;
            $promotion_plan['branch_own_info'][$branch_id] = array();
		}
		
        $promotion_plan['branch_own_info'][$branch_id] = array_merge($promotion_plan['branch_own_info'][$branch_id], $upd);
 
		$upd2['branch_own_info'] = serialize($promotion_plan['branch_own_info']);
		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set ".mysql_update_by_field($upd2)." where id=$promotion_plan_id");		
		log_br($sessioninfo['id'], 'SOP YMP', $promotion_plan_id, "Promotion Plan Own Details Saved: Branch ID#$branch_id, Promotion Plan ID#$promotion_plan_id");
		
		$ret['ok'] = 1;
		$ret['promotion_plan_id'] = $promotion_plan_id;
		$ret['branch_id'] = $branch_id;
		$ret['html'] = load_promotion_plan_own_data_header($promotion_plan_id, $branch_id, true);
		
		print json_encode($ret);
	}
	
	function delete_promotion_plan_own_details(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

		$branch_id = mi($_REQUEST['branch_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);

		if(!$branch_id) $err[] = 'Invalid Branch ID';
		if(!$promotion_plan_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
		else{
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
            if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));   // promotion not found
		}
		
		if($err){   // found error
			sop_display_error($err);
		}
		
		$upd2['branch_own_info'] = $promotion_plan['branch_own_info'];
		unset($upd2['branch_own_info'][$branch_id]);
		$upd2['branch_own_info'] = serialize($upd2['branch_own_info']);
		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set ".mysql_update_by_field($upd2)." where id=$promotion_plan_id");
		log_br($sessioninfo['id'], 'SOP YMP', $promotion_plan_id, "Promotion Plan Own Details Deleted: Branch ID#$branch_id, Promotion Plan ID#$promotion_plan_id");
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function update_promotion_branch_activation(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
	    $branch_id = mi($_REQUEST['branch_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$active = mi($_REQUEST['active']);
		
		if(!$branch_id) $err[] = 'Invalid Branch ID';
		if(!$promotion_plan_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
		else{
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
            if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));   // promotion not found
		}
		
		if($err){   // found error
			sop_display_error($err);
		}
		
		$upd2['branch_own_info'] = $promotion_plan['branch_own_info'];
		$upd2['branch_own_info'][$branch_id]['active'] = $active;
		$upd2['branch_own_info'] = serialize($upd2['branch_own_info']);
		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set ".mysql_update_by_field($upd2)." where id=$promotion_plan_id");
		log_br($sessioninfo['id'], 'SOP YMP', $promotion_plan_id, "Promotion Plan Own Details ".($active?'Activated':'Deactivated').": Branch ID#$branch_id, Promotion Plan ID#$promotion_plan_id");
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function open_promotion_activity_list(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

        $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$root_id = mi($_REQUEST['root_id']);
		$show_row_only = mi($_REQUEST['show_row_only']);

		// validate
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}
        if(!$promotion_plan_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        else{
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
            // promotion not found
            if(!$promotion_plan)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);   
		}


		if($err){   // found error
			sop_display_error($err);
		}

		// load promotion activity list
		if($branch_id>0){
            $params = array(
			    'marketing_plan_id' => $marketing_plan_id,
			    'promotion_plan_id' => $promotion_plan_id,
				'branch_id' => $branch_id,
				'root_id' => $root_id
			);
			$promotion_activity_list = load_promotion_activity_list($params);
			//print_r($promotion_activity_list);
			$smarty->assign('promotion_activity_list', $promotion_activity_list);
		}
		
		
		$smarty->assign('selected_branch_id', $branch_id);
		$smarty->assign('marketing_plan', $marketing_plan);
		$smarty->assign('promotion_plan', $promotion_plan);
		

		if($show_row_only){    // show row only
		    $ret['ok'] = 1; // send success notice
			if($promotion_activity_list){
			    foreach($promotion_activity_list as $r){
                    $smarty->assign('promotion_activity', $r);
					$ret['html'] .= $smarty->fetch('yearly_marketing_plan.marketing_plan_details.activity_dialog.tree_row.tpl');
				}
			}
			print json_encode($ret);
		}else{  // show whole dialog
            $this->display('yearly_marketing_plan.marketing_plan_details.activity_dialog.tpl');
		}
	}

	function add_new_promotion_activity(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

        $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$root_id = mi($_REQUEST['root_id']);

		// validate
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
        if(!$promotion_plan_id)
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        else{
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
            // promotion not found
            if(!$promotion_plan)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);   
		}

        if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
        if(!YMP_HQ_EDIT && !in_array($branch_id, $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])) $err[] = $SOP_LANG['HQ_ONLY'];
        
        if($root_id && !$err){
            // check parent activity
			$con->sql_query("select *
			from ".DATABASE_NAME.".marketing_plan_promotion_activity
			where promotion_plan_id=$promotion_plan_id and branch_id=$branch_id and id=$root_id");
			$parent_promotion_plan = $root_promotion_plan = $con->sql_fetchassoc();
			$con->sql_freeresult();

			if(!$root_promotion_plan)   $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $root_id);

			// construct new tree str
			while($root_promotion_plan){
			    $tree_str[] = '('.$root_promotion_plan['id'].')';

                $con->sql_query("select id,root_id
				from ".DATABASE_NAME.".marketing_plan_promotion_activity
				where promotion_plan_id=$promotion_plan_id and branch_id=$branch_id and id=".mi($root_promotion_plan['root_id']));
				$root_promotion_plan = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}
			$tree_str[] = '(0)';
			$tree_str = join('', array_reverse($tree_str));
		}else   $tree_str = '(0)';

		if($err){   // found error
			sop_display_error($err);
		}

		// get max id
		$con->sql_query("select count(*) from ".DATABASE_NAME.".marketing_plan_promotion_activity
		where promotion_plan_id=$promotion_plan_id and branch_id=$branch_id");
		$max_id = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();

		$upd = array();
		$upd['promotion_plan_id'] = $promotion_plan_id;
		$upd['branch_id'] = $branch_id;
		$upd['level'] = mi($parent_promotion_plan['level'])+1;
		$upd['root_id'] = $root_id;
		$upd['title'] = 'Activity #'.($max_id+1);
		$upd['date_from'] = $promotion_plan['date_from'];
		$upd['date_to'] = $promotion_plan['date_to'];
		$upd['owner_user_id'] = $sessioninfo['id'];
		$upd['tree_str'] = $tree_str;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['reference_id'] = 'b'.$branch_id.'_'.time();
		
		$con->sql_query("insert into ".DATABASE_NAME.".marketing_plan_promotion_activity ".mysql_insert_by_field($upd));
		$upd['id'] = $con->sql_nextid();

		$params = array(
			'marketing_plan_id' => $marketing_plan_id,
			'promotion_plan_id' => $promotion_plan_id,
			'branch_id' => $branch_id,
			'activity_id' => $upd['id']
		);
		regenerate_promotion_activity_cache($params);   // update activity cache
		log_br($sessioninfo['id'], 'SOP YMP', $upd['id'], "Promotion Activity Added: Branch ID#$branch_id, Promotion Plan ID#$promotion_plan_id, ID#".$upd['id']);
		$ret = $upd;
		print json_encode($ret);
	}
	
	function open_promotion_activity(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$activity_id = mi($_REQUEST['activity_id']);

		// validate
		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}

        if(!$promotion_plan_id)
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        else{
            $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
            // promotion not found
            if(!$promotion_plan)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);   
		}

        if(!in_array($branch_id, $sessioninfo['SOP_YMP_ALLOWED_BRANCHES'])) $err[] = $SOP_LANG['HQ_ONLY'];
                
		if($err){   // found error
			sop_display_error($err);
		}

		// load data
		$promotion_activity = load_promotion_activity_header($branch_id, $activity_id);

		$smarty->assign('marketing_plan', $marketing_plan);
		$smarty->assign('promotion_plan', $promotion_plan);
		$smarty->assign('promotion_activity', $promotion_activity);
		$this->display('yearly_marketing_plan.marketing_plan_details.activity_dialog.open_promotion_activity.tpl');
	}
	
	function move_promotion_activity(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

        $is_move_to_top = false;
	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$from_activity_id = mi($_REQUEST['from_activity_id']);
		$to_activity_id = mi($_REQUEST['to_activity_id']);

		$common_filter = "branch_id=$branch_id";

   		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
        if(!$promotion_plan_id)
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        if(!$from_activity_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $from_activity_id);
        else{
            // load activity
	        $con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity
			where $common_filter and id=$from_activity_id");
	        $from_promotion_activity = $con->sql_fetchassoc();
	        $con->sql_freeresult();
	        if(!$from_promotion_activity)	$err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $from_activity_id);
		}
        if(!$to_activity_id)    $is_move_to_top = true;
        else{
            $con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity
			where $common_filter and id=$to_activity_id");
	        $to_promotion_activity = $con->sql_fetchassoc();
	        $con->sql_freeresult();
	        if(!$to_promotion_activity)	$err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $to_activity_id);
		}
		if($from_activity_id>0 && $to_activity_id>0 && $from_activity_id==$to_activity_id){
            $err[] = $SOP_LANG['SOP_PROMOTION_ACTIVITY_CANNOT_MOVE_TO_SELF'];   // cannot move to self
		}

		if($err){   // found error
			sop_display_error($err);
		}

		if(!$is_move_to_top){
            // check got tree cache or not
			$con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity_cache where $common_filter and activity_id=$from_activity_id");
			$from_act_cache = $con->sql_fetchassoc();
			$con->sql_freeresult();

	  		$con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity_cache where $common_filter and activity_id=$to_activity_id");
			$to_act_cache = $con->sql_fetchassoc();
			$con->sql_freeresult();

			// no cache found = error
			if(!$from_act_cache)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_ID_TREE_CORRUPT'], $from_activity_id);
			if(!$to_act_cache)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_ID_TREE_CORRUPT'], $to_activity_id);

			if($err)	sop_display_error($err);    // found error again

			// check can move or not
			// cannot move to current parent
			if($from_promotion_activity['root_id']==$to_promotion_activity['id']){
	            $err[] = $SOP_LANG['SOP_PROMOTION_ACTIVITY_ALREADY_IN_TARGET_PARENT'];
	            sop_display_error($err);
			}

			// cannot move to child
			$from_level = mi($from_promotion_activity['level']);
			foreach($to_act_cache as $column_name=>$value){
				if(preg_match("/^p/", $column_name)){
					if(is_numeric(substr($column_name, 1))){
	                    $lv = mi(substr($column_name, 1));
	                    $act_id = $value;
						if($lv > $from_level){
							if($value==$from_promotion_activity['id']){ // found it is move to children activity
	                            $err[] = $SOP_LANG['SOP_PROMOTION_ACTIVITY_CANNOT_MOVE_TO_OWN_CHILD'];
	            				sop_display_error($err);
							}
						}
					}
				}
			}
		}else{
			if($from_promotion_activity['root_id']==0){
                $err[] = $SOP_LANG['SOP_PROMOTION_ACTIVITY_ALREADY_ON_TOP'];
	            sop_display_error($err);
			}
		}

		// update activity
		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion_activity set root_id=$to_activity_id where $common_filter and id=$from_activity_id");
        log_br($sessioninfo['id'], 'SOP YMP', $activity_id, "Promotion Activity Moved: Branch ID#$created_branch_id, ID#".$from_activity_id);
        
		$params['marketing_plan_id'] = $marketing_plan_id;
		$params['promotion_plan_id'] = $promotion_plan_id;
        $params['branch_id'] = $branch_id;
        
		$this->snyc_activity_hier($params); // create tree_str and cache

		// get the row html
		$ret['activity_row'] = load_promotion_activity_header($branch_id, $from_activity_id);
		$ret['ok'] = 1;
		print json_encode($ret);
	}

	private function snyc_activity_hier($params, $sync=1, $root_id = 0, $tree_array = array(0), $level = 1){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    if(!$params)    die('No params, cannot sync.');
	    $marketing_plan_id = mi($params['marketing_plan_id']);
	    $promotion_plan_id = mi($params['promotion_plan_id']);
		$branch_id = mi($params['branch_id']);
		
	    $tree_str = "(".join(")(",$tree_array).")";

		$act_tbl = DATABASE_NAME.'.marketing_plan_promotion_activity';
		$common_filter = "branch_id=$branch_id";
		$res = $con->sql_query("select id
		from $act_tbl
		where $common_filter and root_id=$root_id");

		while($r = $con->sql_fetchrow($res)){
			if ($sync){
	        	$con->sql_query("update $act_tbl set level=$level, tree_str=".ms($tree_str)." where $common_filter and id=$r[0]");
	        }

			$new_tree = $tree_array ;
			$new_tree[] = $r[0];
			$this->snyc_activity_hier($params, $sync, $r[0], $new_tree, $level+1);
		}

		if ($sync && $root_id==0){
		    $new_params['marketing_plan_id'] = $marketing_plan_id;
		    $new_params['promotion_plan_id'] = $promotion_plan_id;
		    $new_params['branch_id'] = $branch_id;
		    regenerate_promotion_activity_cache($new_params);  // recreate cache
		}
	}

	function save_promotion_activity(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$activity_id = mi($_REQUEST['activity_id']);

		// validate
   		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
   		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}
        if(!$promotion_plan_id)
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
        if(!$activity_id || !$branch_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $activity_id);

        $form = $_REQUEST;
        $upd = array();
		$upd['title'] = trim($form['title']);
		$upd['date_from'] = $form['date_from'];
		$upd['date_to'] = $form['date_to'];
		$upd['pic_user_id_list'] = $form['pic_user_id_list'];
		$upd['remark'] = trim($form['remark']);
		$upd['budget'] = mf($form['budget']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['active'] = mi($form['active']);
		
		// check required field
		if(!$upd['title'])  $err[] = "Please enter title.";
		if(!$upd['date_from'])  $err[] = "Please select date from.";
		if(!$upd['date_to'])  $err[] = "Please select date to.";

        if($err){   // found error
			sop_display_error($err);
		}

		// some field need to serialize
		$upd['pic_user_id_list'] = serialize($upd['pic_user_id_list']);

		// update
		$con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion_activity set ".mysql_update_by_field($upd)."
		where branch_id=$branch_id and id=$activity_id");
        log_br($sessioninfo['id'], 'SOP YMP', $activity_id, "Promotion Activity Saved: Branch ID#$branch_id, ID#".$activity_id);
        
		if($marketing_plan['approved']){    // regenerate user activity if this marketing plan is approved
			$usr_act = array();
			$usr_act['marketing_plan_id'] = $marketing_plan_id;
			$usr_act['promotion_plan_id'] = $promotion_plan_id;
			$usr_act['branch_id'] = $branch_id;
			$usr_act['activity_id'] = $activity_id;
			
			generate_user_activity_table($usr_act); // regenerate user activity entry
		}

		$ret['ok'] = 1;
		print json_encode($ret);
	}

	function delete_promotion_activity(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$activity_id = mi($_REQUEST['activity_id']);

		// validate
   		if(!$marketing_plan_id)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
   		else{
            // check marketing plan header
	    	$marketing_plan = load_marketing_plan_header($marketing_plan_id);   // load header
	    	if(!$marketing_plan)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		}
        if(!$promotion_plan_id)
			$err[] = sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id);
			
        if(!$activity_id || !$branch_id)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $activity_id);
		else{
			// load activity
            $con->sql_query("select * from ".DATABASE_NAME.".marketing_plan_promotion_activity where branch_id=$branch_id and id=$activity_id");
	        $promotion_activity = $con->sql_fetchassoc();
	        $con->sql_freeresult();
	        if(!$promotion_activity)    $err[] = sprintf($SOP_LANG['SOP_PROMOTION_ACTIVITY_INVALID_ID'], $activity_id); // not found
		}

        if($err){   // found error
			sop_display_error($err);
		}

		if($marketing_plan['approved']){    // regenerate user activity if this marketing plan is approved
            $usr_act = array();
			$usr_act['marketing_plan_id'] = $marketing_plan_id;
			$usr_act['promotion_plan_id'] = $promotion_plan_id;
			$usr_act['branch_id'] = $branch_id;
			$usr_act['activity_id'] = $activity_id;
			$usr_act['delete_only'] = 1;
			$usr_act['include_sub'] = 1;
			generate_user_activity_table($usr_act); // regenerate user activity entry
		}
		
        $px = 'p'.$promotion_activity['level'];
        
        $mar_act = DATABASE_NAME.".marketing_plan_promotion_activity";
        $mar_act_cache = DATABASE_NAME.".marketing_plan_promotion_activity_cache";
        
        $sql = "delete from $mar_act , $mar_act_cache
using $mar_act , $mar_act_cache
where $mar_act.branch_id=$mar_act_cache.branch_id and $mar_act.id=$mar_act_cache.activity_id and
$mar_act.promotion_plan_id=$promotion_plan_id and
$mar_act.branch_id=$branch_id and
$mar_act_cache.$px=".mi($promotion_activity['id']);
		
		$con->sql_query($sql);

        // record log file
        log_br($sessioninfo['id'], 'SOP YMP', $activity_id, "Promotion Activity Deleted: Branch ID#$branch_id, ID#".$activity_id);

        $ret['ok'] = 1;
		print json_encode($ret);
	}

	function confirm_marketing_plan(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
	    $form = load_marketing_plan_header($marketing_plan_id); // try to load the form
	    if(!$form)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);

	    // check approval flow
	    $params = array();
	    $params['branch_id'] = $sessioninfo['branch_id'];
	    $params['type'] = 'YEARLY_MARKETING_PLAN';
	    $params['user_id'] = $sessioninfo['id'];
	    $params['reftable'] = 'sop_marketing_plan';
        $params['database'] = DATABASE_NAME;
        
	    if($form['approval_history_id'])    $params['curr_flow_id'] = $form['approval_history_id'];

	    $astat = check_and_create_approval2($params, $con); // get approval flow
	    $is_last_approval = false;
	    if(!$astat){    // approval flow not found
			$err[] = $SOP_LANG['SOP_MARKETING_PLAN_NO_APPROVAL_FLOW'];
		}
		else{   // approval flow found
			$form['approval_history_id'] = $astat[0]; // get the id
   			if($astat[1]=='|') $is_last_approval=true;  // check whether is last approval
		}
        if($is_last_approval){  // it is last approval
			send_pm_to_user($marketing_plan_id, $form['approval_history_id'], 1, "Fully Approved");

			$usr_act = array();
			$usr_act['marketing_plan_id'] = $marketing_plan_id;
			generate_user_activity_table($usr_act); // regenerate user activity entry
        }
        
        if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
        if(!YMP_HQ_EDIT) $err[] = $SOP_LANG['HQ_ONLY'];

	    if($err)    sop_display_error($err);

	    $upd = array();
	    $upd['last_update'] = 'CURRENT_TIMESTAMP';
	    $upd['last_update_by'] = $sessioninfo['id'];
	    $upd['status'] = 1;
	    $upd['approval_history_id'] = $form['approval_history_id'];

	    if($is_last_approval)   $upd['approved'] = 1;
	    $con->sql_query("update ".DATABASE_NAME.".marketing_plan set ".mysql_update_by_field($upd)." where id=$marketing_plan_id");

	    // record log file
        log_br($sessioninfo['id'], 'SOP YMP', $marketing_plan_id, "Marketing Plan Confirm: ID#".$marketing_plan_id);

		$ret['ok'] = 1;
		if($is_last_approval)   $ret['approved'] = 1;
		print json_encode($ret);
	}

	private function reset_revoke_marketing_plan($marketing_plan_id, $params = array()){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($marketing_plan_id);
	    $form = load_marketing_plan_header($marketing_plan_id); // try to load the form

	    // validate
	    if(!$form)    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);
		else{
			if(!$form['active'])    $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INACTIVE'], $marketing_plan_id);
			if($form['status']==5)  $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_ALREADY_TERMINATED'], $marketing_plan_id);
		}

		if(!$this->allow_edit)  $err[] = "You have no edit privilege.";
        if(!YMP_HQ_EDIT) $err[] = $SOP_LANG['HQ_ONLY'];
                
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
	    $upd['last_update_by'] = $sessioninfo['id'];
	    $con->sql_query("update ".DATABASE_NAME.".marketing_plan set ".mysql_update_by_field($upd)." where id=$marketing_plan_id");

	    if($form['approved']){    // regenerate user activity if this marketing plan is approved
            $usr_act = array();
			$usr_act['marketing_plan_id'] = $marketing_plan_id;
			generate_user_activity_table($usr_act); // regenerate user activity entry
		}
	}

	function revoke_marketing_plan(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
	    $params['log'] = 'Revoke';
	    $this->reset_revoke_marketing_plan($marketing_plan_id, $params);

	    // record log file
        log_br($sessioninfo['id'], 'SOP YMP', $marketing_plan_id, "Marketing Plan Revoke: ID#".$marketing_plan_id);

        $ret['ok'] = 1;
		print json_encode($ret);
	}

	function reset_marketing_plan(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

	    $marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
	    $params['log'] = trim($_REQUEST['comment']);
	    $this->reset_revoke_marketing_plan($marketing_plan_id, $params);

	    // record log file
        log_br($sessioninfo['id'], 'SOP YMP', $marketing_plan_id, "Marketing Plan Reset: ID#".$marketing_plan_id);

        $ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function update_promotion_plan_calendar_color(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
        $new_color = trim($_REQUEST['new_color']);
        
        $promotion_plan = load_promotion_plan_header($promotion_plan_id);   // load promotion header
        // promotion not found
        if(!$promotion_plan)    die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_ID'], $promotion_plan_id));
        if(!$new_color) die($SOP_LANG['SOP_PROMOTION_PLAN_INVALID_CALENDAR_COLOR']);

        // check duplicate color
        $con->sql_query("select id from ".DATABASE_NAME.".marketing_plan_promotion where marketing_plan_id=".mi($promotion_plan['marketing_plan_id'])." and calendar_color=".ms($new_color)." and id<>$promotion_plan_id");
        if($con->sql_numrows()>0)   die(sprintf($SOP_LANG['SOP_PROMOTION_PLAN_CALENDAR_COLOR_USED'], $new_color));
        $con->sql_freeresult();

        // update
        $upd = array();
        $upd['calendar_color'] = $new_color;
        $con->sql_query("update ".DATABASE_NAME.".marketing_plan_promotion set ".mysql_update_by_field($upd)." where id=$promotion_plan_id");

        // record log file
        log_br($sessioninfo['id'], 'SOP YMP', $year, "Promotion Plan ID#$promotion_plan_id calendar color change from $promotion_plan[calendar_color] to $new_color");
        print "OK";
	}
	
    /*
	function send_promotion_activity_notify(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;

        // escape all params
		$marketing_plan_id = mi($_REQUEST['marketing_plan_id']);
		$marketing_plan_created_branch_id = mi($_REQUEST['marketing_plan_created_branch_id']);
	    $promotion_plan_id = mi($_REQUEST['promotion_plan_id']);
	    $activity_created_branch_id = mi($_REQUEST['activity_created_branch_id']);
	    $activity_id = mi($_REQUEST['activity_id']);
	    $include_sub_activity = mi($_REQUEST['include_sub_activity']);

		if(!$activity_created_branch_id)    $activity_created_branch_id = $sessioninfo['branch_id'];

		if(!$marketing_plan_id) $err[] = sprintf($SOP_LANG['SOP_MARKETING_PLAN_INVALID_ID'], $marketing_plan_id);

        if(!$this->allow_edit)  $err[] = "You have no edit privilege.";

		if($err){   // found error
		    sop_display_error($err);
		}

		$params['marketing_plan_id'] = $marketing_plan_id;
		if($promotion_plan_id && $marketing_plan_created_branch_id){
		    $params['marketing_plan_created_branch_id'] = $marketing_plan_created_branch_id;
            $params['promotion_plan_id'] = $promotion_plan_id;
		}
		$params['activity_created_branch_id'] = $activity_created_branch_id;
		if($activity_id){
            $params['activity_id'] = $activity_id;
		}
		if($include_sub_activity)  $params['include_sub_activity'] = $include_sub_activity;

		$sent_ret = send_promotion_activity_notify($params);

		if(!$sent_ret['ok'])    sop_display_error($sent_ret['err']);

		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	*/
	
	/*
	function regenerate_promotion_activity_cache(){
	    $upd['marketing_plan_id'] = 8;
	    $upd['promotion_plan_id'] = 1;
	    $upd['activity_id'] = 2;
        regenerate_promotion_activity_cache();
	}

	function generate_user_activity_table(){
	    global $con;

	    $q1 = $con->sql_query("select id from sop_marketing_plan");
	    while($r = $con->sql_fetchrow($q1)){
            $params['marketing_plan_id'] = $r['id'];
        	generate_user_activity_table($params);
		}
		$con->sql_freeresult($q1);
	}*/
}

$YEARLY_MARKETING_PLAN = new YEARLY_MARKETING_PLAN('Yearly Marketing Plan');
?>
