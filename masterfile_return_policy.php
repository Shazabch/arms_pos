<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
//if (!privilege('MST_RETURN_POLICY)) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_RETURN_POLICY', BRANCH_CODE), "/index.php");
$maintenance->check(130);

class MASTERFILE_RETURN_POLICY extends Module{
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		/*$con->sql_query("select id, description from category where level=2 and active=1 order by description");
		$smarty->assign("dept", $con->sql_fetchrowset());*/
		$duration_condition_list = array(1=>'More Than', 2=>'Every');
		$smarty->assign("condition_list", $duration_condition_list);
		$date_type_list = array(1=>'Day(s)', 2=>'Week(s)', 3=>'Month(s)');
		$smarty->assign("date_type_list", $date_type_list);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$q1 = $con->sql_query("select rp.*
							   from return_policy rp
							   order by rp.id, rp.branch_id asc");
		$rp_list = array();
		while($r = $con->sql_fetchrow($q1)){
			$r['durations'] = unserialize($r['durations']);
			$r['charges'] = unserialize($r['charges']);
			$rp_list[] = $r;
		}
		$con->sql_freeresult($q1);

		$smarty->assign("rp_list", $rp_list);

	    $this->display();
	    exit;
	}
	
	function add(){
		global $con, $smarty, $sessioninfo;

		$form=$_REQUEST;
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		$duration_count = $charges_count = 0;
		$ins = $duration_items = $charges_items = array();
		$ins['branch_id'] = mi($sessioninfo['branch_id']);
		$ins['title'] = strtoupper(trim($form['title']));
		$ins['duration_condition'] = mi($form['duration_condition']);
		if(count($form['duration_item_durations']) > 0){
			foreach($form['duration_item_durations'] as $key=>$durations){
				$type = $form['duration_item_type'][$key];
				$rate = $form['duration_item_rate'][$key];
				if($type == 1) $multply = 1; // by day
				elseif($type == 2) $multply = 7; // by week
				else $multply = 30;	// by month
				$duration_count = $durations * $multply;
				$duration_items[$duration_count]['durations'] = $durations;
				$duration_items[$duration_count]['type'] = $type;
				$duration_items[$duration_count]['rate'] = $rate;
			}
		}
		sort($duration_items);
		$ins['durations'] = serialize($duration_items);
		$ins['expiry_durations'] = mi($form['expiry_durations']);
		$ins['expiry_type'] = mi($form['expiry_type']);
		$ins['charges_condition'] = mi($form['charges_condition']);
		if($form['charges_choice'] && count($form['charges_item_durations']) > 0){
			foreach($form['charges_item_durations'] as $key=>$durations){
				$type = $form['charges_item_type'][$key];
				$rate = $form['charges_item_rate'][$key];
				if($type == 1) $multply = 1; // by day
				elseif($type == 2) $multply = 7; // by week
				else $multply = 30;	// by month
				$charges_count = $durations * $multply;
				$charges_items[$charges_count]['durations'] = $durations;
				$charges_items[$charges_count]['type'] = $type;
				$charges_items[$charges_count]['rate'] = $rate;
			}
		}
		sort($charges_items);
		$ins['charges'] = serialize($charges_items);
		$ins['receipt_remark'] = trim($form['receipt_remark']);
		$ins['max_charges'] = mf($form['max_charges']);
		$ins['active'] = mi($form['active']);
		$ins['user_id'] = $sessioninfo['id'];
		$ins['added'] = "CURRENT_TIMESTAMP";
		
		$con->sql_query("insert into return_policy ".mysql_insert_by_field($ins));
		$rp_id = $con->sql_nextid();
		log_br($sessioninfo['id'], 'RETURN_POLICY', $rp_id, "Added Return Privacy: ID#".$rp_id);
	}

	function edit(){
		global $con, $smarty, $LANG;

		$form = $_REQUEST;
		$ret = array();
		
		if(!$form['rp_id']) die("Invalid Return Policy ID");
		
		$q1 = $con->sql_query("select * from return_policy where id = ".mi($form['rp_id'])." and branch_id = ".mi($form['branch_id']));
		if($con->sql_numrows($q1) > 0){
			$ret['rp_info'] = $con->sql_fetchrow($q1);
			$ret['rp_info']['durations'] = unserialize($ret['rp_info']['durations']);
			$ret['rp_info']['charges'] = unserialize($ret['rp_info']['charges']);
			$ret['ok'] = 1;
		}else{
			$ret['failed_msg'] = "Cannot found Return Policy record.";
		}
		print json_encode($ret);
	}
	
	function update(){
		global $con, $smarty, $sessioninfo;
		
		$form=$_REQUEST;
		
		$err = $this->validate();
		if(count($err) > 0){
			print $err;
			exit;
		}
		
		$duration_count = $charges_count = 0;
		$upd = array();
		unset($duration_items, $charges_items);
		$upd['title'] = strtoupper(trim($form['title']));
		$upd['duration_condition'] = mi($form['duration_condition']);
		if(count($form['duration_item_durations']) > 0){
			foreach($form['duration_item_durations'] as $key=>$durations){
				$type = $form['duration_item_type'][$key];
				$rate = $form['duration_item_rate'][$key];
				if($type == 1) $multiply = 1; // by day
				elseif($type == 2) $multiply = 7; // by week
				else $multiply = 30;	// by month
				$duration_count = $durations * $multiply;
				$duration_items[$duration_count]['durations'] = $durations;
				$duration_items[$duration_count]['type'] = $type;
				$duration_items[$duration_count]['rate'] = $rate;
			}
		}
		if($duration_items){
			ksort($duration_items);
			$duration_items = serialize($duration_items);
		}
		$upd['durations'] = $duration_items;
		$upd['expiry_durations'] = mi($form['expiry_durations']);
		$upd['expiry_type'] = mi($form['expiry_type']);
		$upd['charges_condition'] = mi($form['charges_condition']);
		if($form['charges_choice'] && count($form['charges_item_durations']) > 0){
			foreach($form['charges_item_durations'] as $key=>$durations){
				$type = $form['charges_item_type'][$key];
				$rate = $form['charges_item_rate'][$key];
				if($type == 1) $multiply = 1; // by day
				elseif($type == 2) $multiply = 7; // by week
				else $multiply = 30;	// by month
				$charges_count = $durations * $multiply;
				$charges_items[$charges_count]['durations'] = $durations;
				$charges_items[$charges_count]['type'] = $type;
				$charges_items[$charges_count]['rate'] = $rate;
			}
		}
		if($charges_items){
			ksort($charges_items);
			$charges_items = serialize($charges_items);
		}
		$upd['charges'] = $charges_items;
		$upd['receipt_remark'] = trim($form['receipt_remark']);
		$upd['max_charges'] = mf($form['max_charges']);
		$upd['active'] = mi($form['active']);
		$upd['last_update'] = "CURRENT_TIMESTAMP";
		$con->sql_query("update return_policy set ".mysql_update_by_field($upd)." where id = ".mi($form['id'])." and branch_id = ".mi($form['bid']));
		log_br($sessioninfo['id'], 'RETURN_POLICY', $form['id'], "Updated Return Policy: ID#".$form['id'].", BRANCH_ID#".$form['bid']);
	}

	function activation(){
		global $con, $smarty, $sessioninfo;
		
		$form=$_REQUEST;
		
		if(!$form['rp_id']) die("No such record!");
		
		$con->sql_query("update return_policy set active = ".mi($form['value']).", last_update = CURRENT_TIMESTAMP where id = ".mi($form['rp_id'])." and branch_id = ".mi($form['branch_id']));
		if($form['value'] == 1) $msg = "Activated";
		else $msg = "Deactivated";
		log_br($sessioninfo['id'], 'RETURN_POLICY', $form['rp_id'], "$msg for Return Policy: ID#".$form['rp_id'].", BRANCH_ID#".$form['branch_id']);
	}
	
	function validate(){
		global $con, $LANG, $config;
		$form=$_REQUEST;
		$err = $duration_items = $charges_items = array();
		$errm = $type_dec = "";
		$duration_count = $charges_count = 0;

		if(!trim($form['title'])){
			$err[] = sprintf($LANG['MRP_TITLE_EMPTY']);
		}else{
			$con->sql_query("select * from return_policy where id != ".mi($form['id'])." and branch_id = ".mi($form['bid'])." and title = ".ms(trim($form['title'])));
			
			if($con->sql_numrows() > 0) $err[] = sprintf($LANG['MRP_TITLE_EXISTED']);
			$con->sql_freeresult();
		}

		if(count($form['duration_item_durations']) == 0){
			$err[] = sprintf($LANG['MRP_DURATION_EMPTY'], "");
		}else{
			// check whether the setting of duration having duplication
			foreach($form['duration_item_durations'] as $key=>$durations){
				$type = $form['duration_item_type'][$key];
				if(isset($duration_items['durations'][$durations]) && isset($duration_items['type'][$type])){
					if($duration_items['type'][$type] == 1) $type_desc = "Day(s)";
					elseif($duration_items['type'][$type] == 2) $type_desc = "Week(s)";
					else $type_desc = "Month(s)";
					$err[] = sprintf($LANG['MRP_DURATION_DUPLICATE'], "", $duration_items['durations'][$durations], $type_desc);
				}else{
					$duration_items['durations'][$durations] = $durations;
					$duration_items['type'][$type] = $type;
				}
				$duration_count++;
			}

			// check as if user intends to enter more than 1 duration settings for "every"
			if($form['duration_condition'] == 2 && $duration_count>1) $err[] = sprintf($LANG['MRP_CONDITION_EVERY_INVALID'], "Duration", "Duration");
		}

		if($form['charges_choice']){
			// show error msg when having duration settings but without expire duration
			if(!$form['expiry_durations']) $err[] = $LANG['MRP_EXPIRY_DATE_EMPTY'];
		
			if(count($form['charges_item_durations']) == 0){
				$err[] = sprintf($LANG['MRP_DURATION_EMPTY'], "Charges");
			}else{
				// check whether the setting of duration having duplication
				foreach($form['charges_item_durations'] as $key=>$durations){
					$type = $form['charges_item_type'][$key];
					if(isset($charges_items['durations'][$durations]) && isset($charges_items['type'][$type])){
						if($charges_items['type'][$type] == 1) $type_desc = "Day(s)";
						elseif($charges_items['type'][$type] == 2) $type_desc = "Week(s)";
						else $type_desc = "Month(s)";
						$err[] = sprintf($LANG['MRP_DURATION_DUPLICATE'], "Charges", $charges_items['durations'][$durations], $type_desc);
					}else{
						$charges_items['durations'][$durations] = $durations;
						$charges_items['type'][$type] = $type;
					}
					$charges_count++;
				}


				// check as if user intends to enter more than 1 duration settings for "every"
				if($form['charges_condition'] == 2 && $charges_count>1) $err[] = sprintf($LANG['MRP_CONDITION_EVERY_INVALID'], "Charges", "Charges");
			}
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

$MASTERFILE_RETURN_POLICY=new MASTERFILE_RETURN_POLICY("Return Policy Master File");

?>
