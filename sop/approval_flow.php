<?php
include_once('include/common.php');
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MST_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_APPROVAL', BRANCH_CODE), "index.php");

class MASTERFILE_APPROVAL_FLOW extends Module{
	var $branches = array();
	var $depts = array();
	var $sku_types = array();
	var $approval_order =array();
	var $flow_type = array(
		'YEARLY_MARKETING_PLAN' => array(
			'label'=>'Yearly Marketing Plan',
			'need_department' => 0,
			'need_sku_type' => 0
		),
		'FESTIVAL_DATE' => array(
			'label' => 'Festival Date',
			'need_department' => 0,
			'need_sku_type' => 0
		)
	);
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load

		$smarty->assign('flow_type', $this->flow_type);
		parent::__construct($title);
	}
	
	private function init_load(){
		global $con, $smarty, $sessioninfo, $config;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select id,code from branch where active=1 order by sequence", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);

		// load department
		$con->sql_query("select id, description from category where level=2 and active=1 order by description");
		while($r = $con->sql_fetchassoc()){
			$this->depts[$r['id']] = $r;
		}
        $con->sql_freeresult();
		$smarty->assign('depts', $this->depts);
		
		// load sku type
		$con->sql_query("select * from sku_type where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			$this->sku_types[] = $r;
		}
        $con->sql_freeresult();
		$smarty->assign('sku_types', $this->sku_types);
		
		// load approval order
		if(!$config['approval_flow_use_all_order'])    $filter = "where id in (1,3)";  // follow sequence, anyone
		else    $filter = '';
		$con->sql_query("select * from approval_order $filter order by id");
		while($r = $con->sql_fetchassoc()){
		  $approval_order[$r['id']] = $r;
		}
		$smarty->assign("approval_order", $approval_order);
	}

	function _default(){
	    global $con, $smarty;

		$this->display();
	}
	
	function open_approval_flow(){
	    global $con, $smarty, $sessioninfo, $SOP_LANG;
	    
	    $id = mi($_REQUEST['approval_flow_id']);
	    
	    // select all user
	    $con->sql_query("select id, u, active from user where template=0 order by u");
	    while($r = $con->sql_fetchassoc()){
			$users[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
	    if($id>0){
			$con->sql_query("select * from ".DATABASE_NAME.".approval_flow where id=$id");
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		$form['available_users'] = $users;
		
		if($form['approvals']){
            $form['approvals'] = split("[|]", trim($form['approvals'], "|"));
            if($form['approvals']){
                foreach($form['approvals'] as $key=>$uid){
                    if(!$uid)   unset($form['approvals'][$key]);
					else	unset($form['available_users'][$uid]);
				}
            }
		}	
		if($form['notify_users']){
		    $form['notify_users'] = split("[|]", trim($form['notify_users'], "|"));
            if($form['notify_users']){
                foreach($form['notify_users'] as $key=>$uid){
                    if(!$uid)   unset($form['notify_users'][$key]);
					unset($form['available_users'][$uid]);
				}
            }
		}   
		//print_r($form);
		$smarty->assign('form', $form);
		$smarty->assign('users', $users);
		$this->display('approval_flow.dialog.tpl');
	}
	
	function save_approval_flow(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $id = mi($_REQUEST['approval_flow_id']);
        
        $upd = array();
        $upd['aorder'] = $_REQUEST['approval_order'];
        $upd['type'] = $_REQUEST['flow_type'];
        $upd['branch_id'] = mi($_REQUEST['branch_id']);
        $upd['sku_category_id'] = mi($_REQUEST['dept_id']);
        $upd['sku_type'] = trim($_REQUEST['sku_type']);
        $upd['approvals'] = $_REQUEST['approvals'];
        $upd['notify_users'] = $_REQUEST['notify_users'];
        
        // validate
        $check_duplicate_filter = array();
        if(!$upd['aorder']) $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_APPROVAL_ORDER'];    // no approval order
        else{   // check got approval user or not
			if($upd['aorder']!=4){ // 4 = no need approval
				if(!is_array($upd['approvals']) || !$upd['approvals'])  $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_APPROVAL_USERS'];
			}
		}
		if(!$upd['branch_id'])  $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_BRANCH'];    // no branch id
		if(!$upd['type'])   $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_APPROVAL_TYPE']; // no approval type
		else{
			if(!$this->flow_type[$upd['type']]) $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_INVALID_APPROVAL_TYPE'];  // invalid approval type
			else{
			    if($this->flow_type[$upd['type']]['need_department']){
					if(!$upd['sku_category_id']){
	                    $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_DEPARTMENT'];    // need department
					}else	$check_duplicate_filter[] = "sku_category_id=".mi($upd['sku_category_id']);
				}
				if($this->flow_type[$upd['type']]['need_sku_type']){
                    if(!$upd['sku_type']){
	                    $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_NEED_SKU_TYPE'];  // need sku type
					}else	$check_duplicate_filter[] = "sku_type=".ms($upd['sku_type']);
				}
			}
		}
		$check_duplicate_filter[] = "branch_id=".mi($upd['branch_id']);
		$check_duplicate_filter[] = "type=".ms($upd['type']);
		$check_duplicate_filter[] = "id<>$id";
		$check_duplicate_filter = "where ".join(' and ', $check_duplicate_filter);
		// check for duplicate entry
		$con->sql_query("select id from ".DATABASE_NAME.".approval_flow $check_duplicate_filter");
		$temp = $con->sql_fetchrow();
		$con->sql_freeresult();
		if($temp)   $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_ALREADY_EXISTS'];    // same approval flow already exists
        
        if($err)	sop_display_error($err);	// found error
        
        if($upd['approvals'])	$upd['approvals'] = '|'.join('|', $upd['approvals']).'|';
        else    $upd['approvals'] = '|';
    	if($upd['notify_users'])	$upd['notify_users'] = '|'.join('|', $upd['notify_users']).'|';
    	else    $upd['notify_users'] = '|';
        
        if($id>0){  // existing
			$con->sql_query("update ".DATABASE_NAME.".approval_flow set ".mysql_update_by_field($upd)." where id=$id");
		}else{  // new
            $con->sql_query("insert into ".DATABASE_NAME.".approval_flow ".mysql_insert_by_field($upd));
            $id = $con->sql_nextid();
		}
		
		if(!$id){   // no ID mean save failed
            $err[] = $SOP_LANG['SOP_APPROVAL_FLOW_SAVE_FAILED'];
            sop_display_error($err);
		}
			
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function reload_approval_flow_list(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $type = trim($_REQUEST['flow_type']);
        $bid = mi($_REQUEST['branch_id']);
        $dept_id = mi($_REQUEST['dept_id']);
        $sku_type = trim($_REQUEST['sku_type']);
        
        $filter = array();
		if(!$type)  die($SOP_LANG['SOP_APPROVAL_FLOW_NEED_APPROVAL_TYPE']);
		
		$filter[] = "af.type=".ms($type);
		if($bid)    $filter[] = "af.branch_id=$bid";
		if($dept_id)    $filter[] = "af.sku_category_id=$dept_id";
		if($sku_type)   $filter[] = "af.sku_type=".ms($sku_type);
		
		$filter = "where ".join(' and ', $filter);
		$sql = "select af.*, c.description as cat_description
		from ".DATABASE_NAME.".approval_flow af
		left join category c on c.id=af.sku_category_id
		$filter";
		//print $sql;
		$con->sql_query($sql);
		$approval_flow_list = array();
		while($r = $con->sql_fetchassoc()){
            $approval_flow_list[] = $r;
		}
		$smarty->assign('approval_flow_list', $approval_flow_list);
		$con->sql_freeresult();
		
		$this->display('approval_flow.list.tpl');
	}
	
	function toggle_activate_approval_flow(){
        global $con, $smarty, $sessioninfo, $SOP_LANG;
        
        $id = mi($_REQUEST['approval_flow_id']);
        $active = mi($_REQUEST['active']);
        
        if(!$id){
            sop_display_error(array($SOP_LANG['SOP_APPROVAL_FLOW_INVALID']));
		}

		$con->sql_query("update ".DATABASE_NAME.".approval_flow set active=$active where id=$id");
		$ret['ok'] = 1;
		print json_encode($ret);
	}
}

$MASTERFILE_APPROVAL_FLOW = new MASTERFILE_APPROVAL_FLOW('Approval Flow');
?>
