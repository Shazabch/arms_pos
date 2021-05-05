<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($sessioninfo['id']!=1 || BRANCH_CODE != 'HQ')   js_redirect('This module is only accessible by top user.', "/index.php");

$maintenance->check(60);

class PRIVILEGE_MANAGER extends Module{
	var $pv_list = array();
	var $privilege_master = array();
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $privilege_groupname, $privilege_prefix_group;

		if(!$_REQUEST['skip_init'])	$this->init_selection();
        $smarty->assign('privilege_prefix_group', $privilege_prefix_group);
		$smarty->assign('privilege_groupname', $privilege_groupname);
        
		parent::__construct($title);
	}

	private function init_selection(){
	    global $con, $sessioninfo, $smarty, $privilege_groupname, $privilege_prefix_group;

	    // try to parse INI file
		$this->pv_list = parse_privilege_manager_ini();
        $smarty->assign('pv_list', $this->pv_list);
        
        // system privilege
        $con->sql_query("select * from privilege");
        while($r = $con->sql_fetchassoc()){
			$privileges[$r['code']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('privileges', $privileges);
		
		$con->sql_query("select * from privilege_master");
		while($r = $con->sql_fetchassoc()){
			$this->privilege_master[$r['privilege_group']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('privilege_master', $this->privilege_master);
	}

	function _default(){
	    global $con, $sessioninfo, $smarty;

		//print_r($this->pv_list);
	    $this->display();
	}
	
	function save_privilege(){
        global $con, $sessioninfo, $smarty;
        
        //print_r($_REQUEST);exit;
        
        $form = $_REQUEST;
        
        // GROUP
        if($form['active']){
			foreach($form['active'] as $pv_grp=>$allowed){   // loop for all group
			    // update privilege master
			    $upd['privilege_group'] = $pv_grp;
			    $upd['active'] = mi($allowed);
			    $con->sql_query("replace into privilege_master ".mysql_insert_by_field($upd));
			    
			    // get all the privilege under this group
				$pv_list = $this->pv_list[$pv_grp];
				if(!$pv_list)   continue;
				
				foreach($pv_list as $pv_code=>$pv_info){
                    $this->update_privilege($pv_code, $pv_grp, $allowed);   // update each privilege
				}
			}
		}
		
        // others
        if($form['others']){
			foreach($form['others'] as $pv_code => $allowed){
				$allowed = mi($allowed);
				$this->update_privilege($pv_code, 'others', $allowed);
			}
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	private function update_privilege($pv_code, $pv_group, $allowed){
        global $con, $sessioninfo, $smarty;
        
		if(!$pv_code)   return false;   // no code
		$pv_info = $this->pv_list[$pv_group][$pv_code];
		if(!$pv_info)   return false;   // cant get this privilege info
		
		if($allowed){
		    // user privilege
		    // turn on the privilege for user if the user previously got this privilege
            $con->sql_query("update user_privilege set allowed=1 where privilege_code=".ms($pv_code));
            
            // privilege - put back privilege to database
            $upd = array();
            $upd['code'] = $pv_code;
            $upd['description'] = $pv_info['desc'];
            $upd['branch_only'] = $pv_info['branch_only'];
            $upd['hq_only'] = $pv_info['hq_only'];
            
            $con->sql_query("replace into privilege ".mysql_insert_by_field($upd));
		}else{
		    // user privilege - mark not allow privilege for user, do not remove so if next time this privilege is turn on back, the privilege will restore
            $con->sql_query("update user_privilege set allowed=0 where privilege_code=".ms($pv_code));
            
            // privilege - delete from database
            $con->sql_query("delete from privilege where code=".ms($pv_code));
		}
		
	}
}

$PRIVILEGE_MANAGER = new PRIVILEGE_MANAGER('Privilege Manager');

?>
