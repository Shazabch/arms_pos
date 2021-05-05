<?php
/*
1/24/2020 1:09 PM Justin
- Enhanced to allow user can create KPI for multiple Positions.
- Enhanced to show Position column while showing all positions.
- Bug fixed where user can no longer create the similar KPI description when it was existed from other position.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT_KPI_SETUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT_KPI_SETUP', BRANCH_CODE), "/index.php");
$maintenance->check(420);

class MASTERFILE_SA_KPI_SETUP extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
		// load position list
		$q1 = $con->sql_query("select * from sa_position where active=1 order by code, description");
		while($r = $con->sql_fetchassoc($q1)){
			$this->position_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("position_list", $this->position_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->load_kpi_list();
	    $this->display();
	    exit;
	}
	
	private function load_kpi_list(){
		global $con, $smarty, $sessioninfo;
		
		$position_id = trim($_REQUEST['position_id']);
		$description = trim($_REQUEST['description']);
		$status = trim($_REQUEST['status']);
		
		$filters = array();
		
		// user level filter
		$show_position = 1;
		if($position_id !== ""){
			$filters[] = "ski.position_id=".mi($position_id);
			$show_position = 0;
		}
		
		// description filter
		if($description){
			//$description = replace_special_char($description);
			$filters[] = "ski.description like ".ms("%".$description."%");
		}
		
		// active filter
		if($status !== ""){
			if($status === "1"){
				$filters[] = "ski.active=1";
			}elseif($status === "0"){
				$filters[] = "ski.active=0";
			}
		}
		
		$filter = "";
		if($filters) $filter = "where ".join(' and ', $filters);
		
		$q1 = $con->sql_query("select ski.*, u.u as added_by
							   from sa_kpi_items ski
							   left join user u on u.id = ski.user_id
							   $filter
							   order by ski.id");
		
		$current_time = strtotime(date("Y-m-d H:i:s"));
		$sa_kpi_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$r['position_desc'] = $this->position_list[$r['position_id']]['description'];
			$sa_kpi_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("sa_kpi_list", $sa_kpi_list);
		$smarty->assign("show_position", $show_position);
		unset($filters, $filter, $sa_kpi_list);
	}
	
	function ajax_reload_kpi_list(){
		global $con, $smarty, $sessioninfo;
		$this->load_kpi_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("masterfile_sa.kpi_setup.list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function add(){
		global $con, $smarty, $sessioninfo, $config;

		$form=$_REQUEST;
		$err = $this->validate('add');
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		// select max id from the table
		foreach($form['position_id_list'] as $position_id){
			$max_id = 0;
			$q1 = $con->sql_query("select max(id) as max_id from sa_kpi_items where position_id = ".mi($position_id));
			$id_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if(!$id_info['max_id']) $max_id = 1;
			else $max_id = $id_info['max_id']+1;
			unset($id_info);
			
			$ins = array();
			$ins['id'] = $max_id;
			$ins['position_id'] = $position_id;
			$ins['description'] = trim($form['description']);
			$ins['additional_description'] = trim($form['additional_description']);
			$ins['scores'] = mf($form['scores']);
			$ins['user_id'] = $sessioninfo['id'];
			$ins['active'] = 1;
			$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
			
			$con->sql_query("insert into sa_kpi_items ".mysql_insert_by_field($ins));
			$kpi_id = $con->sql_nextid();
			log_br($sessioninfo['id'], 'SA_KPI_SETUP', $kpi_id, "Added S/A KPI Table: KPI ID#".$kpi_id.", POSITION ID#".$position_id);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}

	function edit(){
		global $con, $smarty, $LANG, $config;

		$form = $_REQUEST;
		$ret = array();
		
		if(!$form['kpi_id'] || !$form['position_id']) die("Invalid POSITION or KPI ID");
		
		$q1 = $con->sql_query("select * from sa_kpi_items where position_id = ".mi($form['position_id'])." and id = ".mi($form['kpi_id']));
		if($con->sql_numrows($q1) > 0){
			$ret['sa_kpi_info'] = $con->sql_fetchrow($q1);
			$ret['sa_kpi_info']['position_desc'] = $this->position_list[$ret['sa_kpi_info']['position_id']]['description'];
			$ret['ok'] = 1;
		}else{
			$ret['failed_msg'] = "Cannot found KPI record.";
		}
		print json_encode($ret);
	}
	
	function update(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form=$_REQUEST;
		
		$err = $this->validate('update');
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		$upd = array();
		$upd['description'] = trim($form['description']);
		$upd['additional_description'] = trim($form['additional_description']);
		$upd['scores'] = mf($form['scores']);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update sa_kpi_items set ".mysql_update_by_field($upd)." where position_id = ".mi($form['position_id'])." and id = ".mi($form['id']));
		log_br($sessioninfo['id'], 'SA_KPI_SETUP', $form['id'], "Updated S/A KPI Table: ID#".$form['id']);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}

	function activation(){
		global $con, $smarty, $sessioninfo;
		
		$form=$_REQUEST;
		
		if(!$form['kpi_id'] || !$form['position_id']) die("Invalid POSITION or KPI ID");
		
		$con->sql_query("update sa_kpi_items set active = ".mi($form['value']).", last_update = CURRENT_TIMESTAMP where position_id = ".mi($form['position_id'])." and id = ".mi($form['kpi_id']));
		if($form['value'] == 1) $msg = "Activated";
		else $msg = "Deactivated";
		log_br($sessioninfo['id'], 'SA_KPI_SETUP', $form['kpi_id'], "$msg for S/A KPI Table: ID#".$form['kpi_id'].", POSITION ID#".$form['position_id']);
		
		$ret = array();
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function validate($upd_type=""){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = array();
		$errm = "";
		
		if(!$upd_type) return;
		
		$kpi_description = trim($form['description']);
		
		if(!$form['position_id'] && !$form['position_id_list']) $err[] = sprintf($LANG['SA_KPI_SETUP_FIELD_EMPTY'], "Position");
		if(!trim($form['description'])) $err[] = sprintf($LANG['SA_KPI_SETUP_FIELD_EMPTY'], "Description");
		
		if(!$err){ // check if the description are duplicated
			$filters = array();
			$filter = "";
			if($form['id']) $filters[] = "id != ".mi($form['id']); // this will trigger when do update
			$filters[] = "description = ".ms($kpi_description);
			
			$position_id_list = array();
			if($upd_type == "update") $position_id_list[$form['position_id']] = $form['position_id'];
			else{
				$position_id_list = $form['position_id_list'];
			}
			
			foreach($position_id_list as $position_id){
				$filter = "";
				$filters = array();
				if($form['id']) $filters[] = "id != ".mi($form['id']); // this will trigger when do update
				$filters[] = "description = ".ms($kpi_description);
				$filters[] = "position_id = ".mi($position_id);
				$filter = "where ".join(" and ", $filters);
				
				$q1 = $con->sql_query("select * from sa_kpi_items ".$filter);
				
				if($con->sql_numrows($q1) > 0){ // means code existed
					$err[] = sprintf($LANG['SA_KPI_SETUP_EXISTED'], $kpi_description, $this->position_list[$position_id]['description']);
				}
				$con->sql_freeresult($q1);
				
				unset($filter);
			}
			unset($filters);
		}
		
		if(!trim($form['scores'])) $err[] = sprintf($LANG['SA_KPI_SETUP_FIELD_EMPTY'], "Max Scores");

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

$MASTERFILE_SA_KPI_SETUP=new MASTERFILE_SA_KPI_SETUP("Sales Agent - KPI Table");

?>
