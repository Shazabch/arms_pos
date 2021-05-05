<?php
/*
*/
include("include/common.php");
include("masterfile_sa_commission.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(180);

class MASTERFILE_BRANCH_ADDITIONAL_SP extends Module{
	function __construct($title){
		global $con, $smarty;
		/*$con->sql_query("select id, description from category where level=2 and active=1 order by description");
		$smarty->assign("dept", $con->sql_fetchrowset());*/

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty;

		// load branches
		$q1 = $con->sql_query("select * from branch where active=1 and region is null or region = '' order by sequence, code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);

		$this->load_region();
		$this->load_data();
		$smarty->assign("branches", $this->branches);
		$smarty->assign("region", $this->region);
		$smarty->assign("branch_data", $this->branch_data);
		$smarty->assign("region_data", $this->region_data);
		
	    $this->display();
	    exit;
	}
	
	function load_data(){
		global $con, $smarty;

		$q1 = $con->sql_query("select * from branch_additional_sp");
		
		while($r = $con->sql_fetchassoc($q1)){
			$this->branch_data[$r['branch_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$q1 = $con->sql_query("select * from branch_region_additional_sp");
		
		while($r = $con->sql_fetchassoc($q1)){
			$this->region_data[$r['region_code']] = $r;
		}
		$con->sql_freeresult($q1);
	}
	
	function save(){
		global $con, $smarty, $sessioninfo;
		$form = $_REQUEST;

		foreach($form['b_add_selling_price'] as $bid=>$val){
			$q1 = $con->sql_query("select * from branch_additional_sp where branch_id = ".mi($bid));
			
			if($con->sql_numrows($q1) == 0 && $val==0) continue;
			
			if($con->sql_numrows($q1) == 0){ // is newly added
				$ins = array();
				$ins['branch_id'] = $bid;
				$ins['additional_sp'] = $val;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				$ins['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("insert into branch_additional_sp ".mysql_insert_by_field($ins));
			}else{ // it's maintained the previous value
				$upd = array();
				$upd['additional_sp'] = $val;
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				$upd['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("update branch_additional_sp set ".mysql_update_by_field($upd)." where branch_id = ".mi($bid));
			}
		}
		
		
		foreach($form['region_add_selling_price'] as $region_code=>$val){
			$region_code = ucase($region_code);
			$q1 = $con->sql_query("select * from branch_region_additional_sp where region_code = ".ms($region_code));
			
			if($con->sql_numrows($q1) == 0 && $val==0) continue;
			
			if($con->sql_numrows($q1) == 0){ // is newly added
				$ins = array();
				$ins['region_code'] = $region_code;
				$ins['additional_sp'] = $val;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				$ins['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("insert into branch_region_additional_sp ".mysql_insert_by_field($ins));
			}else{ // it's maintained the previous value
				$upd = array();
				$upd['additional_sp'] = $val;
				$upd['last_update'] = "CURRENT_TIMESTAMP";
				$upd['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("update branch_region_additional_sp set ".mysql_update_by_field($upd)." where region_code = ".ms($region_code));
			}
			$con->sql_freeresult($q1);
		}

		//log_br($sessioninfo['id'], 'BRANCH_ADDITIONAL_SP', 0, "Masterfile Branch Additional Selling Price was updated by $sessioninfo[u]");
		
		$this->_default();
		$this->display();
		exit;
	}
	
	function load_region(){
		global $con, $smarty, $config;
	    if(isset($this->region) || !$config['masterfile_branch_region'])  return;
		$this->region = array();
		
		foreach($config['masterfile_branch_region'] as $region_code=>$rg){
			$region_code = ucase($region_code);
			$q1 = $con->sql_query("select * from branch where region = ".ms($region_code));
			while($r = $con->sql_fetchassoc($q1)){
				$this->region[$region_code][$r['id']] = $r;
			}
			$con->sql_freeresult($q1);
		}
	}
}

$MASTERFILE_BRANCH_ADDITIONAL_SP=new MASTERFILE_BRANCH_ADDITIONAL_SP("Masterfile Branch Additional Selling Price");

?>
