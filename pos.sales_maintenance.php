<?php

/*
1/13/2011 4:11:09 PM Justin
- Add checking if found $config['counter_collection_server'] will popup windows to use remote server.

6/24/2011 5:14:27 PM Andy
- Make all branch default sort by sequence, code.

11/15/2011 11:38:09 AM Andy
- Add when change pos date will also update pos_goods_return, pos_delete_items, pos_items_sn and pos_mix_match_usage.

7/9/2014 5:12 PM Fithri
- add option to alter timestamp by hour, minute & second

1/23/2015 9:59 AM Justin
- Enhanced to update date for timestamp field.

12/07/2015 12:53 AM DingRen
- Add when change pos date will also update pos_deposit_status, pos_deposit_status_history, pos_items_changes, pos_credit_note.

1/5/2016 10:20 AM Andy
- Fix wrong date format checking when search by Wrong Date.

5/5/2017 4:12 PM Justin
- Enhanced the system not to update the pos ID when it is not moving the POS date to another.

9/13/2017 5:55 PM Andy
- Combine submit_wrong_date() and submit_time_range() into one function submit_change().
- Enhanced to check and change receipt_no.

11/14/2019 5:55 PM Justin
- Bug fixed on add/minus timestamp will keep adding or minus for pos_drawer, pos_cash_history and pos_cash_domination tables.

12/30/2019 11:45 AM Justin
- Bug fixed on system will not update the date from timestamp for pos_drawer, pos_cash_history and pos_cash_domination tables if user does not add/deduct hours, minutes or minutes.
*/

if (isset($_REQUEST['remote'])==1)
{
	// clear login_branch cookie
	setcookie('arms_login_branch', '');
	unset($_COOKIE['arms_login_branch']);
}
include("include/common.php");

if (isset($_REQUEST['remote'])==1)
{
	$_SESSION['is_remote'] = 1;

	$uid = mi($_REQUEST['id']);
	$bid = mi($_REQUEST['branch']);

	// make sure user can login this branch
	$con->sql_query("select * from user_privilege where user_id=$uid and branch_id=$bid and privilege_code='LOGIN'");
	$user = $con->sql_fetchrow();
	if (!$user) { die("You do not have permission."); }

    	$con->sql_query("delete from session where ssid = ".ms($ssid));
	$con->sql_query("replace into session (user_id, ssid) values ($uid, ".ms($ssid).")");

	// set login branch and redirect
	setcookie('arms_login_branch', get_branch_code($bid));
	header("Location: $_SERVER[PHP_SELF]");
	exit;
}

if($config['counter_collection_server']){
	$smarty->assign('no_menu_templates', 1);
	$smarty->display('header.tpl');
	print "<script>open_from_dc('".$config['counter_collection_server']."/pos.sales_maintenance.php?','".$sessioninfo['id']."','".$sessioninfo['branch_id']."', 'Sales Maintenance');</script>";
	print "Please refer to popup.";
	$smarty->display('footer.tpl');
	exit;
}

//ini_set("display_errors",1);
if (isset($config['sales_maintenance_server']))
{
	$pos_server = $config['sales_maintenance_server'];

	//redirect
	
	header("Location: $pos_server");
	exit;
}

/*
if (BRANCH_CODE != 'HQ')
$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");
$con = new sql_db('hq.12shoppkt.com','arms_slave','arms_slave','arms_pkt');
*/

$maintenance->check(101);

class SalesMaintenance extends Module
{
	var $branch_id;
	
	function __construct($title, $template='')
	{
		global $con, $smarty;
		

		$con->sql_query("select * from branch order by sequence, code");
		while ($r = $con->sql_fetchrow())
		{
			$branches[$r['code']] = $r;
		}
		$smarty->assign("branches", $branches);

		if (BRANCH_CODE != 'HQ')
			$this->branch_id = $branches[BRANCH_CODE]['id'];
		else
			$this->branch_id = mi($_REQUEST['branch_id']);
			
		if ($_REQUEST['date'] == '') $_REQUEST['date'] = date('d/m/Y');
		parent::__construct($title, $template='');
		
	}
	
	function get_counter()
	{
	

		global $con;
		
		$con->sql_query("select * from counter_settings where branch_id = ".mi($this->branch_id));
		$counters = $con->sql_fetchrowset();
		
		print "<select name='counter_id'>";
		foreach($counters as $c)
		{
			print "<option value='".$c['id']."' ";
			if ($c['id'] == $_REQUEST['counter_id']) print "selected";
			print ">".$c['network_name']."</option>";
		}
		print "</select>";
	}

	function _default()
	{
			//print ceil(123/1000)*1000;
		$this->display();	
	}
	
	function submit_wrong_date()
	{
		global $con, $smarty;
		
		if(isset($_REQUEST['date_change']))
		{
		
		    $date =	dmy_to_time($_REQUEST['date_change']);
			
			if($date == "")
			{
				$smarty->assign("msg",'Invalid change date format');
				$this->display();
				exit;
			}
			
			$date=dmy_to_sqldate($_REQUEST['date_change']);
			$dts = preg_split("/[\/\-]/", $date);
			if(!checkdate($dts[1],$dts[2],$dts[0])){
				$smarty->assign("msg",'Invalid change date format');
				$this->display();
				exit;
			}
		}
		
		$seconds_to_add = (mi($_REQUEST['add_hour'])*60*60) + (mi($_REQUEST['add_minute'])*60) + mi($_REQUEST['add_second']);
		$seconds_to_add = abs($seconds_to_add);
		if ($_REQUEST['add_minus'] == 'minus') $seconds_to_add = $seconds_to_add * -1;
		/*
		print "$seconds_to_add sec <br />";
		print '<pre>';print_r($_REQUEST);print '</pre>';
		die;
		*/

		$new_date = dmy_to_sqldate($_REQUEST['date_change']);
		$old_date = dmy_to_sqldate($_REQUEST['date']);
		
		$cond=array();
		$cond[]="branch_id = ".mi($this->branch_id);
		$cond[]="counter_id = ".mi($_REQUEST['counter_id']);
		$cond[]="date = ".ms($old_date);

		$where="where ".implode(" and ",$cond);

		print "Old Date = $old_date";
		print "New Date = $new_date";
		exit;
		
		
		if (isset($_REQUEST['id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				

				$con->sql_query("update pos set id = ".mi($new_id).", date = ".ms($new_date).", start_time = DATE_ADD(start_time, INTERVAL DATEDIFF(".ms($new_date).", start_time) DAY), start_time = start_time + interval $seconds_to_add second, end_time = DATE_ADD(end_time, INTERVAL DATEDIFF(".ms($new_date).", end_time) DAY), end_time = end_time + interval $seconds_to_add second, pos_time = end_time ".$where." and id = ".mi($id));
				$con->sql_query("update pos_items set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				$con->sql_query("update pos_payment set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// pos_goods_return - from part
    			$con->sql_query("update pos_goods_return set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
    			// pos_goods_return - to part
				$con->sql_query("update pos_goods_return set return_pos_id = ".mi($new_id).", return_date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and return_counter_id = ".mi($_REQUEST['counter_id'])." and return_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and return_pos_id = ".mi($id));

				// pos_delete_items
				$con->sql_query("update pos_delete_items set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// pos_items_sn
				$con->sql_query("update pos_items_sn set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				// sn_info
				$con->sql_query("update sn_info set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// pos_mix_match_usage
				$con->sql_query("update pos_mix_match_usage set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				// pos_deposit_status
				$con->sql_query("update pos_deposit_status set deposit_pos_id = ".mi($new_id).", deposit_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($_REQUEST['counter_id'])." and deposit_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and deposit_pos_id = ".mi($id));

				// pos_deposit_status
				$con->sql_query("update pos_deposit_status set pos_id = ".mi($new_id).", date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and pos_id = ".mi($id));

				// pos_deposit_status_history
				$con->sql_query("update pos_deposit_status_history set deposit_pos_id = ".mi($new_id).", deposit_pos_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($_REQUEST['counter_id'])." and deposit_pos_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and deposit_pos_id = ".mi($id));

				// pos_deposit_status_history
				$con->sql_query("update pos_deposit_status_history set pos_id = ".mi($new_id).", pos_date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and pos_id = ".mi($id));

				// pos_deposit
				$con->sql_query("update pos_deposit set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				//pos_items_changes
				$con->sql_query("update pos_items_changes set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				//pos_credit_note
				$con->sql_query("update pos_credit_note set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
			}
		}

		
		if (isset($_REQUEST['pos_drawer_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_drawer where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_drawer_id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_drawer set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}
		
		if (isset($_REQUEST['pos_cash_domination_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_cash_domination_id'] as $id => $r)
			{			
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_cash_domination set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}
		
		if (isset($_REQUEST['pos_receipt_cancel_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_receipt_cancel where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_receipt_cancel_id'] as $id => $r)
			{			
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_receipt_cancel set id = ".mi($new_id).", date = ".ms($new_date).", cancelled_time = DATE_ADD(cancelled_time, INTERVAL DATEDIFF(".ms($new_date).", cancelled_time) DAY), cancelled_time = cancelled_time + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}
		
		if (isset($_REQUEST['pos_cash_history_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_cash_history where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_cash_history_id'] as $id => $r)
			{			
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_cash_history set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}
		
		header("Location: /pos.sales_maintenance.php");

	}


	function submit_time_range()
	{
		global $con, $smarty;


        if(isset($_REQUEST['date_change']))
		{
		    
			$date = dmy_to_time($_REQUEST['date_change']);

			if($date == "")
			{
				$smarty->assign("msg",'Invalid change date format');
				$this->display();
				exit;
			}

			$date=dmy_to_sqldate($_REQUEST['date_change']);
			$dts = preg_split("/[\/\-]/", $date);
			if(!checkdate($dts[1],$dts[2],$dts[0])){
				$smarty->assign("msg",'Invalid change date format');
				$this->display();
				exit;
			}

		}

		$seconds_to_add = (mi($_REQUEST['add_hour'])*60*60) + (mi($_REQUEST['add_minute'])*60) + mi($_REQUEST['add_second']);
		$seconds_to_add = abs($seconds_to_add);
		if ($_REQUEST['add_minus'] == 'minus') $seconds_to_add = $seconds_to_add * -1;
		/*
		print "$seconds_to_add sec <br />";
		print '<pre>';print_r($_REQUEST);print '</pre>';
		die;
		*/

		$cond=array();
		$cond[]="branch_id = ".mi($this->branch_id);
		$cond[]="counter_id = ".mi($_REQUEST['counter_id']);
		$cond[]="date = ".ms(dmy_to_sqldate($_REQUEST['date']));

		$where="where ".implode(" and ",$cond);

		if (isset($_REQUEST['id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);

				$con->sql_query("update pos set id = ".mi($new_id).", date = ".ms($new_date).", start_time = DATE_ADD(start_time, INTERVAL DATEDIFF(".ms($new_date).", start_time) DAY), start_time = start_time + interval $seconds_to_add second, end_time = DATE_ADD(end_time, INTERVAL DATEDIFF(".ms($new_date).", end_time) DAY), end_time = end_time + interval $seconds_to_add second, pos_time = end_time ".$where." and id = ".mi($id));
    			$con->sql_query("update pos_items set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
    			$con->sql_query("update pos_payment set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
    			
    			// pos_goods_return - from part
    			$con->sql_query("update pos_goods_return set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
    			// pos_goods_return - to part
				$con->sql_query("update pos_goods_return set return_pos_id = ".mi($new_id).", return_date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and return_counter_id = ".mi($_REQUEST['counter_id'])." and return_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and return_pos_id = ".mi($id));
				
				// pos_delete_items
				$con->sql_query("update pos_delete_items set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// pos_items_sn
				$con->sql_query("update pos_items_sn set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// sn_info
				$con->sql_query("update sn_info set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
				
				// pos_mix_match_usage
				$con->sql_query("update pos_mix_match_usage set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				// pos_deposit_status
				$con->sql_query("update pos_deposit_status set deposit_pos_id = ".mi($new_id).", deposit_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($_REQUEST['counter_id'])." and deposit_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and deposit_pos_id = ".mi($id));

				// pos_deposit_status
				$con->sql_query("update pos_deposit_status set pos_id = ".mi($new_id).", date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and pos_id = ".mi($id));

				// pos_deposit_status_history
				$con->sql_query("update pos_deposit_status_history set deposit_pos_id = ".mi($new_id).", deposit_pos_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($_REQUEST['counter_id'])." and deposit_pos_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and deposit_pos_id = ".mi($id));

				// pos_deposit_status_history
				$con->sql_query("update pos_deposit_status_history set pos_id = ".mi($new_id).", pos_date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and pos_id = ".mi($id));

				// pos_deposit
				$con->sql_query("update pos_deposit set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				//pos_items_changes
				$con->sql_query("update pos_items_changes set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));

				//pos_credit_note
				$con->sql_query("update pos_credit_note set pos_id = ".mi($new_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($id));
			}
		}


		if (isset($_REQUEST['pos_drawer_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_drawer where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_drawer_id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_drawer set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}

		if (isset($_REQUEST['pos_cash_domination_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_cash_domination_id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_cash_domination set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}

		if (isset($_REQUEST['pos_receipt_cancel_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_receipt_cancel where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_receipt_cancel_id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_receipt_cancel set id = ".mi($new_id).", date = ".ms($new_date).", cancelled_time = DATE_ADD(cancelled_time, INTERVAL DATEDIFF(".ms($new_date).", cancelled_time) DAY), cancelled_time = cancelled_time + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}

		if (isset($_REQUEST['pos_cash_history_id']))
		{
			if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])){
				$q1 = $con->sql_query("select max(id) as max from pos_cash_history where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms(dmy_to_sqldate($_REQUEST['date_change'])));
				$max = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			foreach ($_REQUEST['pos_cash_history_id'] as $id => $r)
			{
				if(dmy_to_sqldate($_REQUEST['date']) != dmy_to_sqldate($_REQUEST['date_change'])) $new_id = $id + (ceil($max['max']/1000)+1)*1000;
				else $new_id = $id;
				$new_date = dmy_to_sqldate($_REQUEST['date_change']);
				$con->sql_query("update pos_cash_history set id = ".mi($new_id).", date = ".ms($new_date).", timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY), timestamp = timestamp + interval $seconds_to_add second ".$where." and id = ".mi($id));
			}
		}

		header("Location: /pos.sales_maintenance.php");

	}
	function wrong_date()
	{
		global $con, $smarty;
		
		//print '<pre>';print_r($_REQUEST);print '</pre>';
		
		if ($_REQUEST['l'] != 'all'){
			$pos_sql = " and DATE_FORMAT(pos_time,'%Y-%m-%d') <> ".ms(dmy_to_sqldate($_REQUEST['date']));
			$pos_drawer_sql = " and DATE_FORMAT(timestamp,'%Y-%m-%d') <> ".ms(dmy_to_sqldate($_REQUEST['date']));
			$pos_cash_domination_sql = " and DATE_FORMAT(timestamp,'%Y-%m-%d') <> ".ms(dmy_to_sqldate($_REQUEST['date']));
			$pos_receipt_cancel_sql = " and DATE_FORMAT(cancelled_time,'%Y-%m-%d') <> ".ms(dmy_to_sqldate($_REQUEST['date']));
			$pos_cash_history_sql = " and DATE_FORMAT(timestamp,'%Y-%m-%d') <> ".ms(dmy_to_sqldate($_REQUEST['date']));
		}
		
		$cond=array();
		$cond[]="branch_id = ".mi($this->branch_id);
		$cond[]="counter_id = ".mi($_REQUEST['counter_id']);
		$cond[]="date = ".ms(dmy_to_sqldate($_REQUEST['date']));

		$where="where ".implode(" and ",$cond);

		$q1 = $con->sql_query("select * from pos ".$where.$pos_sql);
		$items = $con->sql_fetchrowset($q1);
		$con->sql_fetchassoc($q1);
		$smarty->assign("items", $items);

		$q1 = $con->sql_query("select * from pos_drawer ".$where.$pos_drawer_sql);
		$pos_drawer = $con->sql_fetchrowset($q1);
		$con->sql_fetchassoc($q1);
		$smarty->assign("pos_drawer", $pos_drawer);

		$q1 = $con->sql_query("select * from pos_cash_domination ".$where.$pos_cash_domination_sql);
		$pos_cash_domination = $con->sql_fetchrowset($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("pos_cash_domination", $pos_cash_domination);

		//$q1 = $con->sql_query("select * from pos_receipt_cancel ".$where.$pos_receipt_cancel_sql);
		//$pos_receipt_cancel = $con->sql_fetchrowset($q1);
		//$con->sql_freeresult($q1);
		//$smarty->assign("pos_receipt_cancel", $pos_receipt_cancel);

		$q1 = $con->sql_query("select * from pos_cash_history ".$where.$pos_cash_history_sql);
		$pos_cash_history = $con->sql_fetchrowset($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("pos_cash_history", $pos_cash_history);

		$this->display();
	}
	function time_range()
	{
		global $con, $smarty;

		if($_REQUEST['from_time'] <>"" && $_REQUEST['to_time'] <>""){
			$cond=array();
			$cond[]="branch_id = ".mi($this->branch_id);
			$cond[]="counter_id = ".mi($_REQUEST['counter_id']);
			$cond[]="date = ".ms(dmy_to_sqldate($_REQUEST['date']));

			$where="where ".implode(" and ",$cond);

			$from_time = ms(dmy_to_sqldate($_REQUEST['date'])." ".$_REQUEST['from_time']);
			$to_time = ms(dmy_to_sqldate($_REQUEST['date'])." ".$_REQUEST['to_time']);

			$q1 = $con->sql_query("select * from pos ".$where." and pos_time between ".$from_time." and ".$to_time."");
			$items = $con->sql_fetchrowset($q1);
			$con->sql_freeresult($q1);
			$smarty->assign("items", $items);

			$q1 = $con->sql_query("select * from pos_drawer ".$where." and timestamp between ".$from_time." and ".$to_time."");
			$pos_drawer = $con->sql_fetchrowset($q1);
			$con->sql_freeresult($q1);
			$smarty->assign("pos_drawer", $pos_drawer);

			$q1 = $con->sql_query("select * from pos_cash_domination ".$where." and timestamp between ".$from_time." and ".$to_time."");
			$pos_cash_domination = $con->sql_fetchrowset($q1);
			$con->sql_freeresult($q1);
			$smarty->assign("pos_cash_domination", $pos_cash_domination);

			//$q1 = $con->sql_query("select * from pos_receipt_cancel ".$where." and cancelled_time between ".$from_time." and ".$to_time."");
			//$pos_receipt_cancel = $con->sql_fetchrowset($q1);
			//$con->sql_freeresult($q1);
			//$smarty->assign("pos_receipt_cancel", $pos_receipt_cancel);

			$q1 = $con->sql_query("select * from pos_cash_history ".$where." and timestamp between ".$from_time." and ".$to_time."");
			$pos_cash_history = $con->sql_fetchrowset($q1);
			$con->sql_freeresult($q1);
			$smarty->assign("pos_cash_history", $pos_cash_history);
		}else{
			$smarty->assign("msg", 'Please insert time');
		}

		$this->display();
	}
		
	
	function get_branch_id()
	{
		global $con;

		$q1 = $con->sql_query("select * from branch where code = ".ms(BRANCH_CODE));
		$branch = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		return $branch['id'];
	}
	
	function submit_change(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		$err = array();
		
		// old date
		$old_date = dmy_to_sqldate($form['date']);
		if(!$old_date){
			$err []= "Invalid old date format";
		}
		
		// new date
		if(isset($form['date_change']))
		{			
			$new_date = dmy_to_sqldate($form['date_change']);
			$dts = preg_split("/[\/\-]/", $new_date);
			if(!checkdate($dts[1],$dts[2],$dts[0])){
				$err[] = "Invalid change date format";
			}
		}else{
			$err[] = "Invalid change date format";
		}
		
		$seconds_to_add = (mi($form['add_hour'])*60*60) + (mi($form['add_minute'])*60) + mi($form['add_second']);
		$seconds_to_add = abs($seconds_to_add);
		if ($form['add_minus'] == 'minus') $seconds_to_add = $seconds_to_add * -1;
		
		// is change to different date
		if($old_date != $new_date)	$diff_date = true;
		
		if(!$diff_date && !$seconds_to_add)	$err[] = "Cannot move data. The Date From and To are same.";
		
		if($err){
			$smarty->assign("msg", join('<br>', $err));
			$this->display();
			exit;
		}
		
		print "Old Date = $old_date<br>";
		print "New Date = $new_date";
		
		
		$cond = array();
		$cond[]="branch_id = ".mi($this->branch_id);
		$cond[]="counter_id = ".mi($form['counter_id']);
		$cond[]="date = ".ms($old_date);
		$where = "where ".implode(" and ",$cond);
		//print $where;
		//exit;
		// pos_id
		if (isset($form['id'])){
			// get max pos_id
			if($diff_date){
				// need to change pos_id
				$q1 = $con->sql_query("select max(id) as max_pos_id from pos 
					where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($new_date));
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$max_pos_id = mi($tmp['max_pos_id']);
				$available_next_pos_id = $max_pos_id+1000;
				
				$str_timestamp_update = "start_time = DATE_ADD(start_time, INTERVAL DATEDIFF(".ms($new_date).", start_time) DAY), end_time = DATE_ADD(end_time, INTERVAL DATEDIFF(".ms($new_date).", end_time) DAY)";
			}
			if($seconds_to_add){
				if($str_timestamp_update)	$str_timestamp_update .= ", ";
				$str_timestamp_update .= "start_time = start_time + interval $seconds_to_add second, end_time = end_time + interval $seconds_to_add second";
			}
			if($str_timestamp_update)	$str_timestamp_update .= ", pos_time = end_time";
			
			foreach ($form['id'] as $pos_id => $dummy){
				// update for pos
				$upd = array();
				if($diff_date){
					//$new_pos_id = $pos_id + (ceil($max_pos_id/1000)+1)*1000;
					if($pos_id < $available_next_pos_id){
						$new_pos_id = $available_next_pos_id++;
					}else{
						$new_pos_id = $pos_id;
					}
					if($new_pos_id != $pos_id)	$upd['id'] = $new_pos_id;
					$upd['date'] = $new_date;
					
					// get current receipt_no
					$curr_receipt_no = $new_receipt_no = $form['receipt_no'][$pos_id];
					// check whether the receipt_no already exists
					$con->sql_query("select id from pos where branch_id=".mi($this->branch_id)." and counter_id=".mi($form['counter_id'])." and date=".ms($new_date)." and receipt_no=".ms($curr_receipt_no));
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					// receipt duplicated
					if($tmp){
						// get max receipt_no
						$con->sql_query("select max(receipt_no) as max_receipt_no from pos where branch_id=".mi($this->branch_id)." and counter_id=".mi($form['counter_id'])." and date=".ms($new_date));
						$tmp = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						$new_receipt_no = $tmp['max_receipt_no']+1;
						$upd['receipt_no'] = $new_receipt_no;
						$upd['receipt_ref_no'] = generate_receipt_ref_no($this->branch_id, $form['counter_id'], $new_date, $new_receipt_no);
					}
				}
				$str_update = "";
				if($upd){
					$str_update = mysql_update_by_field($upd);
				}
				if($str_timestamp_update){
					if($str_update)	$str_update .= ", ";
					$str_update .= $str_timestamp_update;
				}	
				
				$con->sql_query("update pos set $str_update ".$where." and id=".mi($pos_id));
				
				if($diff_date){
					// pos_items
					$con->sql_query("update pos_items set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_payment
					$con->sql_query("update pos_payment set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_delete_items
					$con->sql_query("update pos_delete_items set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_items_sn
					$con->sql_query("update pos_items_sn set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// sn_info
					$con->sql_query("update sn_info set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_mix_match_usage
					$con->sql_query("update pos_mix_match_usage set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_deposit_status
					$con->sql_query("update pos_deposit_status set deposit_pos_id = ".mi($new_pos_id).", deposit_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($form['counter_id'])." and deposit_date = ".ms($old_date)." and deposit_pos_id = ".mi($pos_id));
					// pos_deposit_status
					$con->sql_query("update pos_deposit_status set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($old_date)." and pos_id = ".mi($pos_id));
					// pos_deposit_status_history
					$con->sql_query("update pos_deposit_status_history set deposit_pos_id = ".mi($new_pos_id).", deposit_pos_date = ".ms($new_date)." where deposit_branch_id = ".mi($this->branch_id)." and deposit_counter_id = ".mi($form['counter_id'])." and deposit_pos_date = ".ms($old_date)." and deposit_pos_id = ".mi($pos_id));
					// pos_deposit_status_history
					$con->sql_query("update pos_deposit_status_history set pos_id = ".mi($new_pos_id).", pos_date = ".ms($new_date)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and pos_date = ".ms($old_date)." and pos_id = ".mi($pos_id));
					// pos_deposit
					$con->sql_query("update pos_deposit set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					//pos_items_changes
					$con->sql_query("update pos_items_changes set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					//pos_credit_note
					$con->sql_query("update pos_credit_note set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_member_point_adjustment
					$con->sql_query("update pos_member_point_adjustment set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// membership_promotion_items
					$q2 = $con->sql_query("select * from membership_promotion_items ".$where." and pos_id=".mi($pos_id));
					while($r = $con->sql_fetchassoc($q2)){
						unset($r['id']);
						$r['pos_id'] = $new_pos_id;
						$r['date'] = $new_date;
						$con->sql_query("insert into membership_promotion_items ".mysql_insert_by_field($r));
					}
					$con->sql_freeresult($q2);
					$con->sql_query("delete from membership_promotion_items ".$where." and pos_id=".mi($pos_id));
					// membership_promotion_mix_n_match_items
					$q2 = $con->sql_query("select * from membership_promotion_mix_n_match_items ".$where." and pos_id=".mi($pos_id));
					while($r = $con->sql_fetchassoc($q2)){
						unset($r['id']);
						$r['pos_id'] = $new_pos_id;
						$r['date'] = $new_date;
						$con->sql_query("insert into membership_promotion_mix_n_match_items ".mysql_insert_by_field($r));
					}
					$con->sql_freeresult($q2);
					$con->sql_query("delete from membership_promotion_mix_n_match_items ".$where." and pos_id=".mi($pos_id));
					
					// pos_receipt_cancel
					$str_upd = "date=".ms($new_date).", cancelled_time = DATE_ADD(cancelled_time, INTERVAL DATEDIFF(".ms($new_date).", cancelled_time) DAY)";
					if($curr_receipt_no != $new_receipt_no)	$str_upd .= ", receipt_no=".ms($new_receipt_no);
					if($seconds_to_add){
						$str_upd .= ", cancelled_time = cancelled_time + interval $seconds_to_add second";
					}
					$con->sql_query("update pos_receipt_cancel set $str_upd ".$where." and receipt_no=".ms($curr_receipt_no));
					// pos_goods_return
					$con->sql_query("update pos_goods_return set pos_id = ".mi($new_pos_id).", date = ".ms($new_date)." ".$where." and pos_id = ".mi($pos_id));
					// pos_goods_return - return
					$str_upd = "return_pos_id=".mi($new_pos_id).",return_date=".ms($new_date);
					if($curr_receipt_no != $new_receipt_no)	$str_upd .= ", return_receipt_no=".ms($new_receipt_no);
					$con->sql_query("update pos_goods_return set $str_upd "." where return_branch_id=".mi($this->branch_id)." and return_counter_id=".mi($form['counter_id'])." and return_date=".ms($old_date)." and return_pos_id=".ms($pos_id));
				}				
			}
		}
		
		// pos_drawer
		if (isset($form['pos_drawer_id']))
		{
			$str_timestamp_update = '';
			if($diff_date){
				$q1 = $con->sql_query("select max(id) as max_id from pos_drawer where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($new_date));
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$available_next_id = mi($tmp['max_id'])+1000;
				
				$str_timestamp_update = "timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY)";
			}
			foreach ($form['pos_drawer_id'] as $id => $r)
			{
				$upd = array();
				if($diff_date){
					if($id < $available_next_id){
						$new_id = $available_next_id++;
					}else{
						$new_id = $id;
					}
					if($new_id != $id)	$upd['id'] = $new_id;
					$upd['date'] = $new_date;
				}
				
				$tmp_str_timestamp_update = "";
				// if user got set to change date
				if($str_timestamp_update){
					$tmp_str_timestamp_update = $str_timestamp_update;
				}
				
				// if user got set to add hours, minutes or seconds
				if($seconds_to_add){
					if($tmp_str_timestamp_update){
						$tmp_str_timestamp_update .= ", ";
					}
					
					$tmp_str_timestamp_update .= "timestamp = timestamp + interval $seconds_to_add second";
				}
				
				$str_update = "";
				if($upd){
					$str_update = mysql_update_by_field($upd);
				}
				if($tmp_str_timestamp_update){
					if($str_update)	$str_update .= ", ";
					$str_update .= $tmp_str_timestamp_update;
				}
				
				$con->sql_query("update pos_drawer set $str_update ".$where." and id = ".mi($id));
			}
		}
		
		// pos_cash_domination
		if (isset($form['pos_cash_domination_id']))
		{
			$str_timestamp_update = '';
			if($diff_date){
				$q1 = $con->sql_query("select max(id) as max_id from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($new_date));
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$available_next_id = mi($tmp['max_id'])+1000;
				
				$str_timestamp_update = "timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY)";
			}
			foreach ($form['pos_cash_domination_id'] as $id => $r)
			{			
				$upd = array();
				if($diff_date){
					if($id < $available_next_id){
						$new_id = $available_next_id++;
					}else{
						$new_id = $id;
					}
					if($new_id != $id)	$upd['id'] = $new_id;
					$upd['date'] = $new_date;
				}
				
				$tmp_str_timestamp_update = "";
				// if user got set to change date
				if($str_timestamp_update){
					$tmp_str_timestamp_update = $str_timestamp_update;
				}
				
				// if user got set to add hours, minutes or seconds
				if($seconds_to_add){
					if($tmp_str_timestamp_update){
						$tmp_str_timestamp_update .= ", ";
					}
					
					$tmp_str_timestamp_update .= "timestamp = timestamp + interval $seconds_to_add second";
				}
				
				$str_update = "";
				if($upd){
					$str_update = mysql_update_by_field($upd);
				}
				if($tmp_str_timestamp_update){
					if($str_update)	$str_update .= ", ";
					$str_update .= $tmp_str_timestamp_update;
				}
				
				$con->sql_query("update pos_cash_domination set $str_update ".$where." and id = ".mi($id));
			}
		}
		
		// pos_cash_history
		if (isset($form['pos_cash_history_id']))
		{
			$str_timestamp_update = '';
			if($diff_date){
				$q1 = $con->sql_query("select max(id) as max_id from pos_cash_history where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($new_date));
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$available_next_id = mi($tmp['max_id'])+1000;
				
				$str_timestamp_update = "timestamp = DATE_ADD(timestamp, INTERVAL DATEDIFF(".ms($new_date).", timestamp) DAY)";
			}
			foreach ($_REQUEST['pos_cash_history_id'] as $id => $r)
			{			
				$upd = array();
				if($diff_date){
					if($id < $available_next_id){
						$new_id = $available_next_id++;
					}else{
						$new_id = $id;
					}
					if($new_id != $id)	$upd['id'] = $new_id;
					$upd['date'] = $new_date;
				}
				
				$tmp_str_timestamp_update = "";
				// if user got set to change date
				if($str_timestamp_update){
					$tmp_str_timestamp_update = $str_timestamp_update;
				}
				
				// if user got set to add hours, minutes or seconds
				if($seconds_to_add){
					if($tmp_str_timestamp_update){
						$tmp_str_timestamp_update .= ", ";
					}
					
					$tmp_str_timestamp_update .= "timestamp = timestamp + interval $seconds_to_add second";
				}
				
				$str_update = "";
				if($upd){
					$str_update = mysql_update_by_field($upd);
				}
				if($tmp_str_timestamp_update){
					if($str_update)	$str_update .= ", ";
					$str_update .= $tmp_str_timestamp_update;
				}
				
				$con->sql_query("update pos_cash_history set $str_update ".$where." and id = ".mi($id));
			}
		}
		
		header("Location: /pos.sales_maintenance.php");
	}
}

$sales_maintenance = new SalesMaintenance('Sales Maintenance');
?>
