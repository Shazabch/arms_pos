<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT', BRANCH_CODE), "/index.php");
$maintenance->check(420);

class MASTERFILE_SA_POSITION_SETUP extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->load_position_list();
	    $this->display();
	    exit;
	}
	
	private function load_position_list(){
		global $con, $smarty, $sessioninfo;
		
		$code_or_description = trim($_REQUEST['code_or_description']);
		$status = trim($_REQUEST['status']);
		
		$filters = array();
		
		// code or description filter
		if($code_or_description){
			//$description = replace_special_char($description);
			$filters[] = "(sp.code = ".ms($code_or_description)." or sp.description like ".ms("%".$code_or_description."%").")";
		}
		
		// active filter
		if($status !== ""){
			if($status === "1"){
				$filters[] = "sp.active=1";
			}elseif($status === "0"){
				$filters[] = "sp.active=0";
			}
		}
		
		$filter = "";
		if($filters) $filter = "where ".join(' and ', $filters);
		
		$q1 = $con->sql_query("select sp.*, u.u as added_by
							   from sa_position sp
							   left join user u on u.id = sp.user_id
							   $filter
							   order by sp.id");
		
		$current_time = strtotime(date("Y-m-d H:i:s"));
		$sa_position_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$sa_position_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("sa_position_list", $sa_position_list);
		unset($filters, $filter, $sa_position_list);
	}
	
	function ajax_reload_position_list(){
		global $con, $smarty, $sessioninfo;
		$this->load_position_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("masterfile_sa.position_setup.list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function add(){
		global $con, $smarty, $sessioninfo, $config;

		$form=$_REQUEST;
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		// select max id from the table
		$max_id = 0;
		$q1 = $con->sql_query("select max(id) as max_id from sa_position");
		$id_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$id_info['max_id']) $max_id = 1;
		else $max_id = $id_info['max_id']+1;
		
		$ins = array();
		$ins['id'] = $max_id;
		$ins['code'] = trim($form['code']);
		$ins['description'] = trim($form['description']);
		$ins['user_id'] = $sessioninfo['id'];
		$ins['active'] = 1;
		$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("insert into sa_position ".mysql_insert_by_field($ins));
		$sa_id = $con->sql_nextid();
		log_br($sessioninfo['id'], 'SA_POSITION_SETUP', $sa_id, "Added S/A Position Table: ID#".$sa_id);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}

	function edit(){
		global $con, $smarty, $LANG, $config;

		$form = $_REQUEST;
		$ret = array();
		
		if(!$form['position_id']) die("Invalid POSITION ID");
		
		$q1 = $con->sql_query("select * from sa_position where id = ".mi($form['position_id']));
		if($con->sql_numrows($q1) > 0){
			$ret['sa_position_info'] = $con->sql_fetchrow($q1);
			$ret['ok'] = 1;
		}else{
			$ret['failed_msg'] = "Cannot found Position record.";
		}
		print json_encode($ret);
	}
	
	function update(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form=$_REQUEST;
		
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		$upd = array();
		$upd['code'] = trim($form['code']);
		$upd['description'] = trim($form['description']);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update sa_position set ".mysql_update_by_field($upd)." where id = ".mi($form['id']));
		log_br($sessioninfo['id'], 'SA_POSITION_SETUP', $form['id'], "Updated S/A Position Table: ID#".$form['id']);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}

	function activation(){
		global $con, $smarty, $sessioninfo;
		
		$form=$_REQUEST;
		
		if(!$form['position_id']) die("Invalid POSITION ID");
		
		$con->sql_query("update sa_position set active = ".mi($form['value']).", last_update = CURRENT_TIMESTAMP where id = ".mi($form['position_id']));
		if($form['value'] == 1) $msg = "Activated";
		else $msg = "Deactivated";
		log_br($sessioninfo['id'], 'SA_POSITION_SETUP', $form['position_id'], "$msg for S/A Position Table: ID#".$form['position_id']);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = array();
		$errm = "";
		
		if(!trim($form['code'])) $err[] = sprintf($LANG['SA_KPI_SETUP_FIELD_EMPTY'], "Code");
		else{ // check if the description are duplicated
			$filters = array();
			$filter = "";
			if($form['id']) $filters[] = "id != ".mi($form['id']); // this will trigger when do update
			$filters[] = "code = ".ms($form['code']);
			
			if($filters) $filter = "where ".join(" and ", $filters);
			$q1 = $con->sql_query("select * from sa_position ".$filter);
			
			if($con->sql_numrows($q1) > 0){ // means code existed
				$err[] = sprintf($LANG['SA_KPI_SETUP_EXISTED'], $form['code']);
			}
			$con->sql_freeresult($q1);
			
			unset($filters, $filter);
		}

		if(count($err) > 0){
			$err_msg = "<ul>";
			foreach($err as $row=>$errm){
				$err_msg .= "<li>".$errm."</li>"; 
			}
			$err_msg .= "</ul>";
		}
		
		return $err_msg;
	}
}

$MASTERFILE_SA_POSITION_SETUP=new MASTERFILE_SA_POSITION_SETUP("Sales Agent - Position Table");

?>
