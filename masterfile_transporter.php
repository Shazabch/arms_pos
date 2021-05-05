<?php
/*
9/3/2010 10:49:55 AM Andy
- Fix transporter cannot edit.

7/8/2011 2:39:11 PM Andy
- Add config checking for transporter module.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_transporter_masterfile'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MST_TRANSPORTER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_TRANSPORTER', BRANCH_CODE), "/index.php");

include('consignment.include.php');

class MST_TRANSPORTER extends Module{
    function __construct($title){
        global $con, $smarty, $sessioninfo;

        parent::__construct($title);
	}

	function _default(){
	    $this->reload_table(true);
		$this->display();
	}
	
	function open(){
		global $con, $smarty;
		
		$id = mi($_REQUEST['id']);
		if($id){
			$con->sql_query("select * from consignment_transporter where id=$id");
			$smarty->assign('form', $con->sql_fetchrow());
		}
		$smarty->display('masterfile_transporter.open.tpl');
	}
	
	function save(){
        global $con, $smarty, $sessioninfo;
        
        $upd = array();
        $id = mi($_REQUEST['id']);
        $upd['code'] = trim($_REQUEST['code']);
        $upd['company_name'] = trim($_REQUEST['company_name']);
        $upd['description'] = trim($_REQUEST['description']);
        $upd['last_update'] = 'CURRENT_TIMESTAMP';
        
        // checking for code
        $con->sql_query("select count(*) from consignment_transporter where code=".ms($upd['code'])." and id<>$id");
        if($con->sql_fetchfield(0)) die("Transporter Code '$upd[code]' already used.");
		
        if($id>0){
			$con->sql_query("update consignment_transporter set ".mysql_update_by_field($upd)." where id=$id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id updated");
		}else{
            $upd['added'] = 'CURRENT_TIMESTAMP';
            $con->sql_query("insert into consignment_transporter ".mysql_insert_by_field($upd));
            log_br($sessioninfo['id'], 'MASTERFILE', $id, "New Transporter '$upd[code]' added");
		}
		print "OK";
	}
	
	function reload_table($sql_only = false){
        global $con, $smarty;
        
        $con->sql_query("select * from consignment_transporter order by code");
        $smarty->assign('table', $con->sql_fetchrowset());
        if(!$sql_only)  $smarty->display('masterfile_transporter.table.tpl');
	}
	
	function toggle_status(){
        global $con, $smarty, $sessioninfo;
        
        $id = mi($_REQUEST['id']);
        $status = mi($_REQUEST['status']);
        log_br($sessioninfo['id'], 'MASTERFILE', $id, "Transporter ID #$id status updated, set to ".($status? 'active':'in-active'));
        $upd = array('active'=>$status, 'last_update'=>'CURRENT_TIMESTAMP');
        $con->sql_query("update consignment_transporter set ".mysql_update_by_field($upd)." where id=$id");
        $this->reload_table();
	}
}

$MST_TRANSPORTER = new MST_TRANSPORTER('Transporter Master File');
?>
