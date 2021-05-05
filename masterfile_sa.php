<?php
/*
3/2/2012 4:40:43 PM Justin
- Fixed the sql error while user trying to load sales target list.

4/15/2013 3:28 PM Justin
- Modified the checking method for S/A Code.
- Modified the years of sales target to range from last until next year.

5/30/2013 11:37 AM Justin
- Enhanced to check new privilege "MST_SALES_AGENT".

6/30/2015 3:48 PM Justin
- Bug fixed on save commission will not save the user id and last update.
- Bug fixed on system will create another duplicated commission for S/A if user press F5 to refresh the page after saved the commission.

8/18/2017 5:01 PM Andy
- Enhanced to able to filter by code, name and status.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

10/22/2019 3:05 PM Andy
- Added Sales Agent Photo.

10/24/2019 4:48 PM Justin
- Bug fixed on inactive commission will still allow user to re-activate.

11/22/2019 5:18 PM Justin
- Enhanced to have new options "Position" and "Leader".
*/
include("include/common.php");
include("masterfile_sa_commission.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT', BRANCH_CODE), "/index.php");
if(!$config['sa_ticket_expired_days']) $config['sa_ticket_expired_days'] = 1; // mark 1 day as default if cannot found config
$maintenance->check(416);

class MASTERFILE_SALES_AGENT extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		/*$con->sql_query("select id, description from category where level=2 and active=1 order by description");
		$smarty->assign("dept", $con->sql_fetchrowset());*/
		// load position list
		$q1 = $con->sql_query("select * from sa_position where active=1 order by code, description");
		while($r = $con->sql_fetchassoc($q1)){
			$position_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("position_list", $position_list);
		unset($position_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->load_sa_list();
	    $this->display();
	    exit;
	}
	
	private function load_sa_list(){
		global $con, $smarty, $sessioninfo;
		
		$code_or_name = trim($_REQUEST['code_or_name']);
		$status = trim($_REQUEST['status']);
		
		$filter = array();
		if($status !== ""){
			if($status === "1"){
				$filter[] = "sa.active=1";
			}elseif($status === "0"){
				$filter[] = "sa.active=0";
			}
		}
		if($code_or_name){
			$code_or_name = replace_special_char($code_or_name);
			$filter[] = "(sa.code like ".ms("%".$code_or_name."%")." or sa.name like ".ms("%".$code_or_name."%").")";
		}
		if($filter){
			$filter = "where ".join(' and ', $filter);
		}else{
			$filter = "";
		}
		
		$q1 = $con->sql_query("select sa.*, 0 as ticket_expired, sp.code position_code, sp.description as position_desc
							   from sa
							   left join sa_position sp on sp.id = sa.position_id
							   $filter
							   order by sa.id asc");
		
		$current_time = strtotime(date("Y-m-d H:i:s"));
		$sa_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$commission_list[$r['id']] = $this->load_commission_list($r['id']);
			$valid_before_time = strtotime($r['ticket_valid_before']);
			if($current_time > $valid_before_time) $r['ticket_expired'] = 1;
			$sa_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("commission_list", $commission_list);
		$smarty->assign("sa_list", $sa_list);
	}
	
	function ajax_reload_sa_list(){
		global $con, $smarty, $sessioninfo;
		$this->load_sa_list();
		
		$ret = array();
		$ret['html'] = $smarty->fetch("masterfile_sa.list.tpl");
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
		
		$ins = array();
		$ins['code'] = strtoupper($config['masterfile_sa_code_prefix']).strtoupper(trim($form['code']));
		$ins['name'] = strtoupper(trim($form['name']));
		$ins['company_code'] = strtoupper(trim($form['company_code']));
		$ins['company_name'] = strtoupper(trim($form['company_name']));
		$ins['address'] = trim($form['address']);
		$ins['phone_1'] = trim($form['phone_1']);
		$ins['email'] = trim($form['email']);
		$ins['ticket_no'] = trim($form['ticket_no']);
		$ins['ticket_valid_before'] = $form['ticket_valid_before'];
		$ins['position_id'] = $form['position_id'];
		$ins['active'] = 1;
		$ins['added'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("insert into sa ".mysql_insert_by_field($ins));
		$sa_id = $con->sql_nextid();
		
		// found user got add leaders
		if($form['leader_list']){
			foreach($form['leader_list'] as $sa_leader_id){
				$ins = array();
				$ins['sa_id'] = $sa_id;
				$ins['sa_leader_id'] = $sa_leader_id;
				
				$con->sql_query("replace into sa_leader ".mysql_insert_by_field($ins));
			}
		}
		
		log_br($sessioninfo['id'], 'SALES_AGENT', $sa_id, "Added Sales Agent: ID#".$sa_id);
	}

	function edit(){
		global $con, $smarty, $LANG, $config;

		$form = $_REQUEST;
		$ret = array();
		
		if(!$form['sa_id']) die("Invalid SA ID");
		
		$q1 = $con->sql_query("select *, 0 as ticket_expired from sa where id = ".mi($form['sa_id']));
		if($con->sql_numrows($q1) > 0){
			$ret['sa_info'] = $con->sql_fetchrow($q1);
			if($config['masterfile_sa_code_prefix']) $ret['sa_info']['code'] = preg_replace("/".$config['masterfile_sa_code_prefix']."/", "", $ret['sa_info']['code'], 1);
			$current_time = strtotime(date("Y-m-d H:i:s"));
			$valid_before_time = strtotime($ret['sa_info']['ticket_valid_before']);
			if($valid_before_time <= 0) $ret['sa_info']['ticket_valid_before'] = "";
			if($current_time > $valid_before_time) $ret['sa_info']['ticket_expired'] = 1;
			
			// load sales agent leader list
			$q2 = $con->sql_query("select sa.id, sa.code
								   from sa_leader sal
								   left join sa on sa.id = sal.sa_leader_id
								   where sal.sa_id = ".mi($form['sa_id']));
			
			if($con->sql_numrows($q2) > 0){
				// put every leader into the html list
				$leader_tpl_list = array();
				while($r = $con->sql_fetchassoc($q2)){
					$smarty->assign("sa", $r);
					$leader_tpl_list[] = $smarty->fetch("masterfile_sa.leader.tpl");
				}
				$con->sql_freeresult($q2);
				$ret['sa_info']['leader_list'] = join("&nbsp;", $leader_tpl_list);
				unset($leader_tpl_list);
			}
			
			$ret['ok'] = 1;
		}else{
			$ret['failed_msg'] = "Cannot found SA record.";
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
		$upd['code'] = strtoupper($config['masterfile_sa_code_prefix']).strtoupper(trim($form['code']));
		$upd['name'] = strtoupper(trim($form['name']));
		$upd['company_code'] = strtoupper(trim($form['company_code']));
		$upd['company_name'] = strtoupper(trim($form['company_name']));
		$upd['address'] = trim($form['address']);
		$upd['phone_1'] = trim($form['phone_1']);
		$upd['email'] = trim($form['email']);
		$upd['ticket_no'] = trim($form['ticket_no']);
		$upd['ticket_valid_before'] = $form['ticket_valid_before'];
		$upd['position_id'] = $form['position_id'];
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("update sa set ".mysql_update_by_field($upd)." where id = ".mi($form['id']));
		log_br($sessioninfo['id'], 'SALES_AGENT', $form['id'], "Updated Sales Agent: ID#".$form['id']);
		
		// found user got add leaders
		if($form['leader_list']){
			$con->sql_query("delete from sa_leader where sa_id = ".mi($form['id']));
			foreach($form['leader_list'] as $sa_leader_id){
				$ins = array();
				$ins['sa_id'] = $form['id'];
				$ins['sa_leader_id'] = $sa_leader_id;
				
				$con->sql_query("replace into sa_leader ".mysql_insert_by_field($ins));
			}
		}
		
		if($form['old_ticket_no'] && $form['old_ticket_no'] != $form['ticket_no']){ // means it had been changed during edit screen
			log_br($sessioninfo['id'], 'SALES_AGENT', $form['id'], "Regenerated Ticket from #".$form['old_ticket_no']." become #".$form['ticket_no']." for ID#".mi($form['id']));
		}
	}

	function activation(){
		global $con, $smarty, $sessioninfo;
		
		$form=$_REQUEST;
		
		if(!$form['sa_id']) die("No such record!");
		
		$con->sql_query("update sa set active = ".mi($form['value']).", last_update = CURRENT_TIMESTAMP where id = ".mi($form['sa_id']));
		if($form['value'] == 1) $msg = "Activated";
		else $msg = "Deactivated";
		log_br($sessioninfo['id'], 'SALES_AGENT', $form['sa_id'], "$msg for Sales Agent: ID#".$form['sa_id']);
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = array();
		$errm = "";
		
		$tmp_sa_code = $form['code'];

		if(!trim($tmp_sa_code)){
			$err[] = sprintf($LANG['SA_CODE_INVALID'], "", "empty");
		}else{
			if($form['id']) $filter = " and id != ".mi($form['id']); // from update
			$q1 = $con->sql_query("select code from sa where code =".ms(trim($form['code'])).$filter);
			
			if($con->sql_numrows($q1) > 0){ // means code existed
				$err[] = sprintf($LANG['SA_CODE_INVALID'], " [".trim($form['code'])."]", "existed in database");
			}
			$con->sql_freeresult($q1);
		}

		/*if($config['masterfile_sa_code_prefix']){
			if(!preg_match("/^".$config['masterfile_sa_code_prefix']."/", $form['code'])) $err[] = sprintf($LANG['SA_CODE_INVALID'], " [".trim($form['code'])."]", "invalid, need to start with ".$config['masterfile_sa_code_prefix']);
		}*/
		
		if(!trim($form['name'])) $err[] = $LANG['SA_NAME_EMPTY'];
		
		if ($form['email'] && !preg_match(EMAIL_REGEX, $form['email'])) $err[] = $LANG['SA_EMAIL_PATTERN_INVALID'];

		if(count($err) > 0){
			$err_msg = "<ul>";
			foreach($err as $row=>$errm){
				$err_msg .= "<li>".$errm."</li>"; 
			}
			$err_msg .= "</ul>";
		}
		
		return $err_msg;
	}
	
	function ticket_activation(){
		global $con, $LANG, $sessioninfo, $config;

		$form = $_REQUEST;
		$sa_id = $form['sa_id'];
		$status = $form['value'];
		$ret = array();

		if($status){ // activation
			$ticket_no = sprintf("%02d%02d%02d", rand()%100,rand()%100,rand()%100);
			$valid_before = date("Y-m-d H:i:s", mktime(0,0,0,date("m"), date("d")+$config['sa_ticket_expired_days']+1, date("Y")));

			if($sa_id){ // is activate for those existing S/A
				$con->sql_query("update sa set ticket_no = ".mi($ticket_no).", ticket_valid_before = ".ms($valid_before).", last_update = 'CURRENT_TIMESTAMP' where id = ".mi($sa_id));
				if($con->sql_affectedrows()>0){
					log_br($sessioninfo['id'], 'SALES_AGENT', $sa_id, "Generated Ticket #".$ticket_no." for ID#".mi($sa_id));
					$ret['ok'] = 1;
				}
			}else{
				$ret['ticket_no'] = $ticket_no;
				$ret['valid_before'] = $valid_before;
				$ret['ok'] = 1;
			}
		}elseif(!$status && $sa_id){ // termination
			$q1 = $con->sql_query("select * from sa where id = ".mi($sa_id));
			$sa_info = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);

			if(is_array($sa_info)){
				$con->sql_query("update sa set ticket_no = '', ticket_valid_before = '', last_update = 'CURRENT_TIMESTAMP' where id = ".mi($sa_info['id']));

				log_br($sessioninfo['id'], 'SALES_AGENT', $sa_info['id'], "Deactivated Ticket #".$sa_info['ticket_no']." for ID#".mi($sa_info['id']));
				$ret['ok'] = 1;
			}
		}

		print json_encode($ret);
	}

	function load_commission_list($sa_id){
		global $con, $smarty, $sessioninfo;

		$q1 = $con->sql_query("select *, b.code as branch_code
							   from sa_commission sac
							   left join sa_commission_settings sas on sas.sac_id = sac.id and sas.branch_id = sac.branch_id
							   left join branch b on b.id = sac.branch_id
							   where sas.sa_id = ".mi($sa_id)." and sas.active = 1");

		$commission_list = array();
		while($r = $con->sql_fetchrow($q1)){
			$commission_list[] = $r;
		}

		return $commission_list;
	}

	function ajax_load_commission_by_branch_list(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		if(BRANCH_CODE != "HQ") $filter = "id = ".mi($sessioninfo['branch_id'])." and";
		$q1 = $con->sql_query("select * from branch where $filter active=1 order by sequence, code");
		while($r1 = $con->sql_fetchrow($q1)){
			$q2 = $con->sql_query("select sac.*, sas.active, sas.id as sac_setting_id
								   from sa_commission_settings sas
								   left join sa_commission sac on sac.id = sas.sac_id and sac.branch_id = sas.branch_id
								   where sac.active=1 and sas.sa_id = ".mi($form['sa_id'])." and sas.branch_id = ".mi($r1['id']));
			$sac_info = $con->sql_fetchrow($q2);
			
			$r1['sac_id'] = $sac_info['id'];
			$r1['sac_title'] = $sac_info['title'];
			$r1['sac_active'] = $sac_info['active'];
			$r1['sac_setting_id'] = $sac_info['sac_setting_id'];
			$branch_list[$r1['id']] = $r1;
		}
		$smarty->assign("branch_list", $branch_list);
		$con->sql_freeresult($q1);
		
		$ret['html'] = $smarty->fetch("masterfile_sa.commission_list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_load_commission_list(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		
		if($form['sac_branch_id']) $branch_id = $form['sac_branch_id'];
		else $branch_id = $sessioninfo['branch_id'];

		$filter = array();
		$filter[] = "sac.branch_id = ".mi($branch_id)." and sac.active=1";

		if($form['search_str']){
			$search_str = replace_special_char($form['search_str']);
			$filter[] = "(sac.title like ".ms("%".$search_str."%")." or sac.id = ".mi($search_str)." or u.u like ".ms("%".$search_str."%").")";
		}

		$q2 = $con->sql_query("select sac.*, u.u as username, if(sas.sa_id is not null, 1, 0) as commission_used
							   from sa_commission sac
							   left join sa_commission_settings sas on sas.sac_id = sac.id and sas.branch_id = sac.branch_id and sas.sa_id = ".mi($form['sa_id'])."
							   left join user u on u.id = sac.user_id
							   where ".join(" and ", $filter)."
							   order by sac.title
							   limit 50");

		while($r2 = $con->sql_fetchrow($q2)){
			$sac_list[] = $r2;
		}

		$con->sql_freeresult($q2);
		$smarty->assign('sac_list', $sac_list);
		$smarty->assign('branch_id', $branch_id);
		$smarty->assign('search_str', $form['search_str']);
		$ret['html'] = $smarty->fetch("masterfile_sa.commission_list_row.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_load_commission_items_list(){
		global $con, $smarty;
		$form = $_REQUEST;

		$date_list = array();
		$sac_items[$form['sac_id']] = load_commission_items($form['sac_id'], $form['branch_id'], $date_list);
		$saci_date_list[$form['sac_id']] = $date_list;

		$smarty->assign("sac_items", $sac_items);
		$smarty->assign("sac_list", $sac);
		$smarty->assign("saci_date_list", $saci_date_list);
		$smarty->assign("sa_id", $form['sa_id']);
		$smarty->assign("sac_id", $form['sac_id']);
		$ret['html'] = $smarty->fetch("masterfile_sa.commission_list_row.item_details.tpl");
		$ret['ok'] = 1;
		
		if($is_load_header) return $ret;
		else print json_encode($ret);
	}
	
	function save_commission(){
		global $con, $sessioninfo;
		$form = $_REQUEST;

		foreach($form['branch_sac'] as $bid=>$curr_sac_id){
			$active = $form['branch_sac_active'][$bid];
			$prv_sac_id = $form['branch_prv_sac'][$bid];
			$sac_setting_id = $form['branch_sac_setting'][$bid];
			$msg = "";
			
			$q1 = $con->sql_query("select * from sa_commission_settings where branch_id = ".mi($bid)." and sa_id = ".mi($form['sa_id'])." and sac_id = ".mi($curr_sac_id));
			$comm_settings = $con->sql_fetchassoc($q1);

			if($con->sql_numrows($q1) > 0 || ($prv_sac_id && $prv_sac_id != $curr_sac_id)){ // found the previous record is the same as current one, do updates only
				$sas_upd = array();
				$sas_upd['sac_id'] = $curr_sac_id;
				$sas_upd['active'] = $active;
				$sas_upd['user_id'] = $sessioninfo['id'];
				$sas_upd['last_update'] = "CURRENT_TIMESTAMP";
				$q1 = $con->sql_query("update sa_commission_settings set ".mysql_update_by_field($sas_upd)." where sac_id = ".mi($prv_sac_id)." and branch_id = ".mi($bid)." and sa_id = ".mi($form['sa_id'])." and id = ".mi($sac_setting_id));
			}elseif($con->sql_numrows($q1) == 0 && !$prv_sac_id && $active){ // is insert
				$sas_ins = array();
				$sas_ins['sac_id'] = $curr_sac_id;
				$sas_ins['branch_id'] = $bid;
				$sas_ins['sa_id'] = $form['sa_id'];
				$sas_ins['active'] = $active;
				$sas_ins['user_id'] = $sessioninfo['id'];
				$sas_ins['added'] = "CURRENT_TIMESTAMP";
				
				$q1 = $con->sql_query("insert into sa_commission_settings ".mysql_insert_by_field($sas_ins));
			}
		}
		
		if($msg){
			log_br($sessioninfo['id'], 'SALES_AGENT', $form['sa_id'], "Updated Commission for Sales Agent#".$form['sa_id']);
		}
		
		$this->_default();
	}
	
	function ajax_load_sales_target(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		
		$branches_list = array();
		if(BRANCH_CODE != "HQ"){
			$b_filter = "and id = ".mi($sessioninfo['branch_id']);
			$sast_filter = " and branch_id = ".mi($sessioninfo['branch_id']);
		}
		
		$q1 = $con->sql_query("select * from branch where active=1 $b_filter order by sequence, code");
		$branches_list = $con->sql_fetchrowset($q1);
		$con->sql_freeresult($q1);
		
		$curr_yr = date('Y');
		
		$yrs = $mth_value = array();
		for($i=$curr_yr-1; $i<=$curr_yr+1; $i++){
			$yrs[] = $i;
		}
		
		$q1 = $con->sql_query("select * from sa_sales_target where year = ".mi($curr_yr)." and sa_id = ".mi($form['sa_id']).$sast_filter);
		
		while($r = $con->sql_fetchrow($q1)){
			$mth_value[$r['branch_id']] = unserialize($r['value']);
		}

		$smarty->assign("branches_list", $branches_list);
		$smarty->assign("curr_yr", $curr_yr);
		$smarty->assign("yrs", $yrs);
		$smarty->assign("mth_value", $mth_value);
		$smarty->assign("sa_id", $form['sa_id']);

		$ret['sa_id'] = $form['sa_id'];
		$ret['html'] = $smarty->fetch("masterfile_sa.sales_target_list.tpl");
		$ret['ok'] = 1;

		print json_encode($ret);
	}
	
	function ajax_save_sales_target(){
		global $con, $smarty, $sessioninfo;
		$form = $_REQUEST;

		foreach($form['yr'] as $bid=>$yr){
			if(count($form['mth'][$bid]) > 0){
				$q1 = $con->sql_query("select * from sa_sales_target where sa_id = ".mi($form['sa_id'])." and branch_id = ".mi($bid)." and year = ".mi($yr));

				if($con->sql_numrows($q1) > 0){ // do update
					$upd = array();
					$upd['value'] = serialize($form['mth'][$bid]);
					$upd['last_update'] = "CURRENT_TIMESTAMP";
					$con->sql_query("update sa_sales_target set ".mysql_update_by_field($upd)." where sa_id = ".mi($form['sa_id'])." and branch_id = ".mi($bid)." and year = ".mi($yr));
				}else{ // do insert
					$ins = array();
					$ins['sa_id'] = $form['sa_id'];
					$ins['branch_id'] = $bid;
					$ins['year'] = $yr;
					$ins['value'] = serialize($form['mth'][$bid]);
					$ins['user_id'] = $sessioninfo['id'];
					$ins['added'] = "CURRENT_TIMESTAMP";
					$con->sql_query("insert into sa_sales_target ".mysql_insert_by_field($ins));
				}
				$con->sql_freeresult($q1);
			}
		}

		if($msg){
			log_br($sessioninfo['id'], 'SALES_AGENT', $form['sa_id'], "Updated Sales Target for Sales Agent#".$form['sa_id']);
		}
	}
	
	function ajax_load_st_month_value(){
		global $con, $smarty, $sessioninfo;
		$form = $_REQUEST;

		$mth_value = $ret = array();
		$q1 = $con->sql_query("select * from sa_sales_target where sa_id = ".mi($form['sa_id'])." and branch_id = ".mi($form['branch_id'])." and year = ".mi($form['curr_yr']));
		
		if($con->sql_numrows($q1) > 0){
			$st_info = $con->sql_fetchrow($q1);
			$mth_value = unserialize($st_info['value']);

			foreach($mth_value as $mth=>$value){
				$ret[$mth]['mth'] = $value;
			}
		}
		$con->sql_freeresult($q1);
		
		// if cannot found it, prefix it to load zero value
		if(!$ret){
			for($i=1; $i<=12; $i++){
				$ret[$i]['mth'] = "";
			}
		}
		
		print json_encode($ret);
	}
	
	function ajax_show_sa_photo(){
		global $con, $smarty, $LANG, $config;

		$sa_id = mi($_REQUEST['sa_id']);
		if(!$sa_id) die("Invalid SA ID");
		
		$q1 = $con->sql_query("select * from sa where id = $sa_id");
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_sa.show_photo.tpl');
		print json_encode($ret);
	}
	
	function upload_sa_photo(){
		global $con, $smarty, $LANG, $sessioninfo, $appCore; 
		
		$sa_id = mi($_REQUEST['sa_id']);
		if(!$sa_id){
			print "<script>parent.alert('Invalid Sales Agent ID');parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded_failed();</script>";exit;
		}
		
		// Create Folder
		$folder = "attch/sa_photo";
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Sales Agent Folder');parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded_failed();</script>";exit;
			}
		}
		//print_r($_FILES);
		$fname = 'sa_photo';
		
		// Check File
		$result = $appCore->isValidUploadImageFile($_FILES[$fname]);
		if(!$result['ok']){
			print "<script>parent.alert(".jsstring($result['error']).");parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded_failed();</script>";
			exit;
		}
		$ext = trim($result['ext']);
		if(!$ext){
			print "<script>parent.alert('Invalid File Extension');parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded_failed();</script>";
			exit;
		}
		
		$final_path = $folder."/".$sa_id.".jpg";
		
		// Move File to Actual Folder
		if(move_uploaded_file($_FILES[$fname]['tmp_name'], $final_path)){
			$file_uploaded = true;
		}
		else{
			$file_uploaded = false;
		}
		
		// Call Back
		if($file_uploaded){
			// update sa
			$upd = array();
			$upd['photo_url'] = $final_path;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update sa set ".mysql_update_by_field($upd)." where id=$sa_id");
			
			log_br($sessioninfo['id'], 'SALES_AGENT', $sa_id, "Sales Agent Photo Updated: ID#".$sa_id);
						
			print "<script>parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded('$final_path?".time()."');</script>";
		}else{
			print "<script>parent.alert('".$LANG['POS_SETTINGS_CANT_MOVE_FILE']."');parent.SALES_AGENT_PHOTO_DIALOG.photo_uploaded_failed();</script>";
		}
	}
	
	function ajax_add_leader(){
		global $con, $smarty, $appCore;
		
		$sa_id = mi($_REQUEST['sa_id']);
		$sa = $appCore->salesAgentManager->getSA($sa_id);
		
		if(!$sa){
			die('Invalid Sales Agent ID');
		}
		
		$smarty->assign('sa', $sa);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_sa.leader.tpl');
		
		print json_encode($ret);
	}
}

$MASTERFILE_SALES_AGENT=new MASTERFILE_SALES_AGENT("Sales Agent Master File");

?>
