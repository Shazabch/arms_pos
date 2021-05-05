<?php
/*
2/2/2009 2:44:45 PM - yinsee
- remove the time counter status time checking 

11/25/2010 5:57:23 PM Andy
- Fix pos live getting wrong over amount.

1/13/2011 10:11:38 AM Justin
- Simplified the query from calculating total amounts and transactions
- Fixed the wrong calculation for total transactions during show all counters window.
- Add checking if found $config['counter_collection_server'] will popup windows to use remote server.

3/29/2011 11:46:54 AM Justin
- Disabled the item details since redirected below functions to counter_collection.php
- Modified the transaction detail to use template from counter collection

6/24/2011 5:15:46 PM Andy
- Make all branch default sort by sequence, code. 

10/3/2011 11:45:28 AM Andy
- Add column "Current Login User".
- Add can unset "current login user".

11/18/2011 10:10:22 AM Andy
- Add checking for privilege "POS_FORCE_LOGOUT" to allow user to unset "Login User".

1/12/2012 10:07:32 AM Justin
- Added to count prune status.

3/29/2012 11:16:50 AM Andy
- Add Total Discount Amount column.

9/18/2012 1:46 PM Justin
- Enhanced to highlight counter if the last ping for each counter has > 30 minutes.

11/2/2012 2:35:00 PM Fithri
- when view in payment detail from counter, the cancelled receipt is filter off.

12/12/2012 12:18 PM Justin
- Enhanced to exclude branch sales base on the config 'sales_report_branches_exclude'.
- Enhanced to redirect user to main page if found it is login into subbranch and it is under excluded list.

1/8/2014 2:05 PM Fithri
- check for type (POS) when getting data for "drawer open" count

1/13/2014 5:52 PM Fithri
- show db outdated (sync error), if any, for counter
- fix bug unset status 'Current Login User' when viewing from branch

4/3/2015 5:12 PM Andy
- Fix branches sales wrong total sales/discount/transaction, need to filter excluded branch.

3/2/2017 3:33 PM Andy
- Enhanced to combine counter collection error and counter error.
- Fixed not to update counter lastping when unset user login status.
- Add maintenance checking version to 311.

4/19/2017 9:18 AM Justin
- Enhanced to show out inactive counter as if the user had logged on to this counter.

5/12/2017 8:21 AM Qiu Ying
- Enhanced to show all counter sales by branch

10/5/2017 9:53 AM Andy
- Change to get counter error from posManager.
- Enhanced to get counter sync error from pos_transaction_sync_server_counter_tracking.
- Added Sync Server Error.
- Change online status checking, no matter got user login or not, show online as long as last ping is within 30min.
- Raise maintenance checking version to 320.

10/17/2017 2:20 PM Andy
- Fixed online status checking at All Branch All Counter list.

9/24/2019 5:36 PM Andy
- Added column "Counter Version" and "OS".

2/18/2020 1:11 PM William
- Enhanced to change $con connection to use $con_multi.

1/15/2021 11:42 AM William
- Enhanced to get active suite device.
*/
include("include/common.php");

$maintenance->check(320);

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
// check if user is access to subbranch but it is under excluded list, prompt error
if(BRANCH_CODE != "HQ" && $config['sales_report_branches_exclude']&& in_array(BRANCH_CODE, $config['sales_report_branches_exclude'])){
	js_redirect(sprintf($LANG['BRANCH_EXCLUDED'], 'POS Live', BRANCH_CODE), "/index.php");
}

if (isset($_REQUEST['remote'])==1)
{
	$_SESSION[$_SERVER['HTTP_HOST']]['is_remote'] = 1;

	$uid = mi($_REQUEST['id']);
	$bid = mi($_REQUEST['branch']);

	// make sure user can login this branch
	$con_multi->sql_query("select * from user_privilege where user_id=$uid and branch_id=$bid and privilege_code='LOGIN'");
	$user = $con_multi->sql_fetchrow();
	$con_multi->sql_freeresult();
	if (!$user) { die("You do not have permission."); }

    $con->sql_query("delete from session where ssid = ".ms($ssid));
	$con->sql_query("replace into session (user_id, ssid) values ($uid, ".ms($ssid).")");

	// set login branch and redirect
	setcookie('arms_login_branch', get_branch_code($bid));
	header("Location: $_SERVER[PHP_SELF]");
	exit;
}

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_BACKEND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_BACKEND', BRANCH_CODE), "/index.php");

if($config['counter_collection_server']){
	$smarty->assign('no_menu_templates', 1);
	$smarty->display('header.tpl');
	print "<script>open_from_dc('".$config['counter_collection_server']."/pos_live.php?','".$sessioninfo['id']."','".$sessioninfo['branch_id']."', 'POS Live');</script>";
	print "Please refer to popup.";
	$smarty->display('footer.tpl');
	exit;
}

//$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");
//$con = new sql_db('gmark-hq.arms.com.my:4001','arms','4383659','armshq');

if(BRANCH_CODE == 'HQ'){
	$bid = intval($_REQUEST['branch_id']);
}else{
	if(!isset($_REQUEST['a'])){
        $_REQUEST['a'] = 'load_counter';
        $_REQUEST['submits'] = 'submit';
	}
	
	$bid = get_request_branch(true);
}

$counter_id = intval($_REQUEST['counter_id']);
$network_name = $_REQUEST['network_name'];

if(isset($_REQUEST['date'])){
	$f_date = $_REQUEST['date'];
}else{
    $f_date = date('Y-m-d');
    $_REQUEST['date'] = $f_date;
//$f_date = '2008-9-30';
}
	//print_r($_REQUEST);
if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
	    case 'load_counter':
			load_counter();
			break;
		case 'refresh_counter':
            refresh_counter();
		    exit;
		case 'get_cc_tracking_error':
			get_cc_tracking_error();
			print $smarty->fetch('pos_live.cc_tracking_error.tpl');
			exit;
		case 'payment_details':
		    payment_details();
		    exit;
		case 'tran_details':
		    tran_details();
		    exit;
		case 'item_details':
		    item_details();
		    exit;
		case 'load_branchs_table':
		    get_branch_list();
		    load_branchs_table();
		    exit;
		case 'load_counter_table':
		    load_counter_table();
		    exit;
		case 'load_all_branches_all_counter':
		    load_all_branches_all_counter();
		    exit;
		case 'ajax_unset_login_status':
			ajax_unset_login_status();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}
get_branch_list();
get_cc_tracking_error();

if (!isset($_REQUEST['branch_id']) && BRANCH_CODE=='HQ')
{	
	$_REQUEST['branch_id'] = -1;
	$bid=-1;
	load_counter();
}
$smarty->assign('PAGE_TITLE', 'POS Live');
$smarty->display("pos_live.tpl");
exit;

function get_cc_tracking_error()
{
	global $con, $smarty, $f_date, $bid, $appCore;

	$counters_error = $appCore->posManager->getCounterError($bid);
	$smarty->assign("counters_error",$counters_error);
	
	$ss_error = $appCore->posManager->getSyncServerError($bid);
	$smarty->assign("ss_error",$ss_error);
	
}

function get_branch_list(){
	global $con,$smarty,$config,$con_multi;
    $sql = "select id,code from branch where active=1 order by sequence,code";
	/*if (defined('HQ_HAVE_SALES'))
		$sql = "select id,code from branch where active=1";
	else
		$sql = "select id,code from branch where code<>'HQ' and active=1";*/

	$con_multi->sql_query($sql) or die(mysql_error());
	while($r = $con_multi->sql_fetchrow()){
		if($config['sales_report_branches_exclude'] && in_array($r['code'], $config['sales_report_branches_exclude'])) continue;
		$branch_list[$r['id']]=$r;
	}
	$con_multi->sql_freeresult();
	$smarty->assign('branch_list',$branch_list);
}

function sort_branches_asc($a,$b)
{
	if (!is_numeric($a['sort'])){
	    return strcmp($a['sort'], $b['sort']);
	}

	if ($a['sort']>$b['sort'])
	{
		return 1;
	}
	if ($a['sort']<$b['sort'])
	{
		return -1;
	}

	return 0;
}

function sort_branches_desc($a,$b)
{
    if (!is_numeric($a['sort'])){
		return strcmp($b['sort'], $a['sort']);
	}
	
	if ($a['sort']>$b['sort'])
	{
	    return -1;
	}
	if ($a['sort']<$b['sort'])
	{
	    return 1;
	}
	return 0;
}

function load_branchs_table($sqlonly = false){
    global $con,$smarty,$bid,$counter_id,$f_date,$sessioninfo,$con_multi;

	// get branch list
	$blist = $smarty->get_template_vars('branch_list');
	
	$filter[] = 'pos.cancel_status=0';
	$filter[] = 'pos.date='.ms($f_date);
	$filter[] = "branch.active=1";
	$filter[] = "branch.id in (".join(',', array_keys($blist)).")";
    $filter = join(' and ',$filter);
    
    $sql = "select branch_id,sum(amount) as amount ,count(*) as tran,code, sum((select sum(pp.amount) 
		from pos_payment pp
		where pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.date=pos.date and pp.pos_id=pos.id and pp.type in ('Discount', 'Mix & Match Total Disc'))) as disc_amt
	from pos left join branch on pos.branch_id=branch.id 
	where $filter group by branch_id";
	//print $sql;
	$con_multi->sql_query($sql) or die(mysql_error());

	while($r = $con_multi->sql_fetchrow()){
		$all_branchs[$r['branch_id']]['amount'] = $r['amount'];
		$all_branchs[$r['branch_id']]['tran'] = $r['tran'];
		$all_branchs[$r['branch_id']]['code'] = $r['code'];
		$all_branchs[$r['branch_id']]['disc_amt'] = $r['disc_amt'];
		
		$all_branchs['total']['amount']+=$r['amount'];
		$all_branchs['total']['tran']+=$r['tran'];
		$all_branchs['total']['disc_amt']+=$r['disc_amt'];
	}
	$con_multi->sql_freeresult();
	
	/*$sql = "select pos.* , branch.code as bcode
	from pos 
	left join branch on pos.branch_id=branch.id 
	where $filter 
	order by branch.sequence,branch.code";
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		$bid = mi($r['branch_id']);
		$cid = mi($r['counter_id']);
		$dt = $r['date'];
		$pos_id = mi($r['id']);
		
		if(!isset($all_branchs[$bid]['code'])){
			$all_branchs[$bid]['code'] = $r['bcode'];
		}
		// get discount amt
		$con->sql_query("select sum(amount) as disc_amt 
		from pos_payment
		where branch_id=$bid and counter_id=$cid and date=".ms($dt)." and pos_id=$pos_id and type in ('Discount', 'Mix & Match Total Disc')");
		$disc_amt = $con->sql_fetchfield('disc_amt');
		$con->sql_freeresult();
			
		$amt = round($r['amount'] - $disc_amt,2);
		
		// total amount
		$all_branchs[$bid]['amount'] += $amt;
		$all_branchs['total']['amount']+=$amt;
		
		// transaction count
		$all_branchs[$bid]['tran']++;
		$all_branchs['total']['tran']++;
	}
	$con->sql_freeresult($q1);*/
	
	// ----sort branch list
	$newlist = array();
	$sort_column = isset($_COOKIE['_tbsort_branch'])?$_COOKIE['_tbsort_branch']:'amount';
	$sort_order = isset($_COOKIE['_tbsort_branch_order'])?$_COOKIE['_tbsort_branch_order']:'asc';
	
	foreach($blist as $r)
	{
		if ($sort_column == 'code')
			$r['sort'] = $r['code'];
	    else
			$r['sort'] = $all_branchs[$r['id']][$sort_column];
	    $newlist[] = $r;
	}
	usort($newlist, 'sort_branches_'.$sort_order);
	$smarty->assign('branch_list',$newlist);
	$smarty->assign('sort_column',$sort_column);
	$smarty->assign('sort_order',$sort_order);
	// done----

	$smarty->assign('all_branchs',$all_branchs);
	if(!$sqlonly){
		$smarty->display('pos_live.all_branchs.table.tpl');
	}
}

function load_counter_table($sqlonly = false){
    global $con,$smarty,$bid,$counter_id,$f_date,$con_multi;

    $filter[] = 'cs.branch_id='.mi($bid);
	//$filter[] = 'cs.active=1';
	$filter[] = '(cs.pos_settings like'.ms('%allow_pos%').' or sd.active=1)';

	$filter = join(' and ', $filter);
	
	$sql = "select cs.*,cst.lastping,cst.lasterr,cst.status,user.u as user_cu, cst.user_id, cst.revision, cst.revision_type, sd.active as suite_device_active
	from counter_settings cs
	left join counter_status cst on cst.id=cs.id and cst.branch_id=cs.branch_id
	left join user on user.id=cst.user_id
	left join suite_device sd on sd.guid = cs.suite_device_guid and sd.branch_id = cs.branch_id
	where $filter order by id";
	//print $sql;
	$q1 = $con_multi->sql_query($sql);
	$counters = array();
	while($r = $con_multi->sql_fetchassoc($q1)){
		if(!$r['active'] && !$r['user_cu']) continue; // skip this record as if doesn't have user login and counter is inactive
		
		if($r['revision_type']){
			list($r['program_type'], $r['os_type']) = explode(" ", $r['revision_type']);
		}
		$counters[] = $r;
	}
	$con_multi->sql_freeresult($q1);
	$table = array();
	
	if(count($counters)>0){
		foreach($counters as $r){
			refresh_counter($r['id'],$r['branch_id'],$table);
		}
	}
	
	$sort_column = isset($_COOKIE['_tbsort_counter'])?$_COOKIE['_tbsort_counter']:'total_amount';
	$sort_order = isset($_COOKIE['_tbsort_counter_order'])?$_COOKIE['_tbsort_counter_order']:'asc';
	
	//print "$sort_column , $sort_order";

    if(count($counters)>0){
		$curr_time = time();
		foreach($counters as $r)
		{
		    if ($sort_column == 'network_name'){
                $r['sort'] = $r['network_name'];
			}else{
                $r['sort'] = $table[$r['id']][$sort_column];
			}
		    
			// check last ping, if > 30 mins then need to highlight
			$lastping_time = strtotime($r['lastping']);
			$time_left = $curr_time - $lastping_time;
			if($time_left > 0 && $time_left != $curr_time){
				$minutes = $time_left/60;
				if($minutes > 30){
					//$r['need_highlight'] = true;
					if($r['status'])	$r['status'] = 'offline';
				}
				//if($r['user_id']<=0)	$r['status'] = 'offline';
			}			
		    $newlist[] = $r;
		}
		
		$counters = $newlist;
		unset($newlist);
		
		usort($counters, 'sort_branches_'.$sort_order);
		
		//print_r($counters);
		$smarty->assign('counter', $counters);
	}
	
	$bcode = getBranchCode($bid);

	$smarty->assign('bcode',$bcode);
	//$smarty->assign('counter',$counters);
	$smarty->assign('table',$table);
	$smarty->assign('sqlonly',$sqlonly);
	$smarty->assign('sort_column',$sort_column);
	$smarty->assign('sort_order',$sort_order);
	
	if($_REQUEST['ajax']==1){
        $smarty->display('pos_live.counter.tpl');
        exit;
	}
	
	if(!$sqlonly){
		$smarty->display('pos_live.all_counters.tpl');
	}
}

function load_all_branches_all_counter(){
	global $con,$smarty,$con_multi;
	
	$sql = "select b.code,cs.*,cst.lastping,cst.lasterr,cst.status,user.u as user_cu, cst.user_id
	from counter_settings cs
	left join counter_status cst on cst.id=cs.id and cst.branch_id=cs.branch_id
	left join user on user.id=cst.user_id
	left join branch b on cs.branch_id = b.id
	where cs.pos_settings like '%allow_pos%' and b.active = 1
	order by cs.branch_id, cs.id";
	
	$q1 = $con_multi->sql_query($sql);
	$counters = array();
	while($r = $con_multi->sql_fetchassoc($q1)){
		if(!$r['active'] && !$r['user_cu']) continue; // skip this record as if doesn't have user login and counter is inactive
		$counters[] = $r;
	}
	$con_multi->sql_freeresult($q1);
	$table = array();
	
	if(count($counters)>0){
		foreach($counters as $r){
			refresh_counter($r['id'],$r['branch_id'],$table,true);
		}
	}
	
    if(count($counters)>0){
		$curr_time = time();
		foreach($counters as $r)
		{
			$lastping_time = strtotime($r['lastping']);
			$time_left = $curr_time - $lastping_time;
			if($time_left > 0 && $time_left != $curr_time){
				$minutes = $time_left/60;
				if($minutes > 30){
					if($r['status'])	$r['status'] = 'offline';
				}
				//if($r['user_id']<=0)	$r['status'] = 'offline';
			}			
		    $newlist[] = $r;
		}
		
		$smarty->assign('counter',$newlist);
	}
	
	$smarty->assign('table',$table);
	$smarty->assign('load_all',true);
	$smarty->display('pos_live.all_counters.tpl');
}

function load_counter(){
    global $con,$smarty,$bid,$counter_id,$f_date;
	
	if($bid==-1&&BRANCH_CODE=='HQ'){
	    get_branch_list();
	    load_branchs_table(true);
	}else{
        load_counter_table(true);
	}
}

function refresh_counter($counter_id,$bid,&$table,$load_all=false){
    global $con,$smarty,$f_date,$network_name;
	
	$data = array();
	
	load_total_amount_and_transaction($counter_id,$bid,$data);
	load_last_user($counter_id,$bid,$data); // last user and last transaction time
	load_cash_in_drawer($counter_id,$bid,$data);
	load_drawer_open_count($counter_id,$bid,$data);
	if($load_all){
		$table[$bid][$counter_id]=$data;
		$table[$bid]['total']['total_amount']+=$data['total_amount'];
		$table[$bid]['total']['total_disc_amt']+=$data['total_disc_amt'];
		$table[$bid]['total']['total_transaction']+=$data['total_transaction'];
		$table[$bid]['total']['variance']+=$data['variance'];
		
		$table["grand_total"]['total_amount'] += $data['total_amount'];
		$table["grand_total"]['total_disc_amt'] += $data['total_disc_amt'];
		$table["grand_total"]['total_transaction'] += $data['total_transaction'];
		$table["grand_total"]['variance'] += $data['variance'];
	}else{
		$table[$counter_id]=$data;
		//$smarty->assign('data',$data);
		//$smarty->assign('bid',$bid);
		//$smarty->assign('counter_id',$counter_id);
		//$smarty->assign('network_name',$network_name);
		$table['total']['total_amount']+=$data['total_amount'];
		$table['total']['total_disc_amt']+=$data['total_disc_amt'];
		$table['total']['total_transaction']+=$data['total_transaction'];
		$table['total']['cash_in_drawer']+=$data['cash_in_drawer'];
		$table['total']['drawer_open_count']+=$data['drawer_open_count'];
		$table['total']['cancelled_bill']+=$data['cancelled_bill'];
		$table['total']['prune_count']+=$data['prune_count'];
	}
}

function load_total_amount_and_transaction($counter_id,$bid,&$data){
    global $con,$smarty,$f_date,$con_multi;
    
    $filter[] = 'p.branch_id='.mi($bid);
    $filter[] = 'p.counter_id='.mi($counter_id);
    //$filter[] = 'date='.ms(date('Y-m-d'));
    $filter[] = 'p.date='.ms($f_date);
    //$filter[] = 'cancel_status=0';
    
    $filter = join(' and ', $filter);

	$q1 = $con_multi->sql_query("select p.branch_id,p.id,p.counter_id,p.date,round(p.amount, 2) as amount, p.cancel_status, p.prune_status,
					 (round(p.amount_tender-p.amount_change,2)-round(p.amount,2)) as over,
					 (select sum(amount) 
						from pos_payment pp
						where pp.branch_id=p.branch_id and pp.counter_id=p.counter_id and pp.date=p.date 
						and pp.pos_id=p.id and pp.type in ('Discount', 'Mix & Match Total Disc')
					 ) as disc_amt, f.variance
					 from pos p
					 left join pos_counter_finalize f on p.branch_id = f.branch_id and p.counter_id = f.counter_id and p.date = f.date
					 where $filter") or die(mysql_error());
	
	while($r = $con_multi->sql_fetchrow($q1)){
		if($r["variance"]){
			$tmp = unserialize($r["variance"]);
			
			$data['variance'] = $tmp["nett_sales"]["amt"];
		}
		if($r['cancel_status']!=1){
			if($r['amount'] != $r['over']) $data['total_amount'] += $r['over'];
			
			$data['total_amount'] += $r['amount'];
			$data['total_transaction']++;
			$data['total_disc_amt'] += $r['disc_amt'];
		}else{
			$data['cancelled_bill']++;
			 if($r['prune_status']) $data['prune_count']++;
		}
	}
	$con_multi->sql_freeresult($q1);
	
}

function load_last_user($counter_id,$bid,&$data){
    global $con,$smarty,$f_date,$con_multi;
    
    $filter[] = 'branch_id='.mi($bid);
    $filter[] = 'counter_id='.mi($counter_id);
    //$filter[] = 'date='.ms(date('Y-m-d'));
    $filter[] = 'date='.ms($f_date);
    $filter[] = 'cancel_status=0';
    
    $filter = join(' and ', $filter);
    
    $con_multi->sql_query("select pos.*,user.u from pos left join user on pos.cashier_id=user.id where $filter order by end_time desc limit 1") or die(mysql_error());
    $temp = $con_multi->sql_fetchrow();
	$con_multi->sql_freeresult();
    $data['user_id'] = $temp['cashier_id'];
    $data['user_u'] = $temp['u'];
    $data['last_tran'] = $temp['end_time'];
}

function load_cash_in_drawer($counter_id,$bid,&$data){
   global $con,$smarty,$f_date,$con_multi;

    $filter[] = 'p.branch_id='.mi($bid);
    $filter[] = 'p.counter_id='.mi($counter_id);
    //$filter[] = 'date='.ms(date('Y-m-d'));
    $filter[] = 'p.date='.ms($f_date);
    $filter[] = "p.type='Cash'";
    $filter[] = "pos.cancel_status=0";

    $filter = join(' and ', $filter);
    
    $sql= "select sum(p.amount) as total,sum(pos.amount_change) as amount_change from pos_payment p left join pos on p.pos_id=pos.id and p.branch_id=pos.branch_id and p.counter_id=pos.counter_id and p.date = pos.date where $filter";
    $con_multi->sql_query($sql) or die(mysql_error());
    if($con_multi->sql_numrows()>0){
        $temp = $con_multi->sql_fetchrow();
        $data['cash_in_drawer'] += ($temp['total']-$temp['amount_change']);
	}
	$con_multi->sql_freeresult();
	
	$filter = array();
	
	$filter[] = 'branch_id='.mi($bid);
    $filter[] = 'counter_id='.mi($counter_id);
    //$filter[] = 'date='.ms(date('Y-m-d'));
    $filter[] = 'date='.ms($f_date);
    //$filter[] = "(type='OUT' or type='IN')";

    $filter = join(' and ', $filter);
    
    $sql = "select * from pos_cash_history where $filter";
    $con_multi->sql_query($sql) or die(mysql_error());
    
    while($r = $con_multi->sql_fetchrow()){
		/*if($r['type']=='IN'){
            $data['cash_in_drawer'] += $r['amount'];
		}elseif($r['type']=='OUT'){
            $data['cash_in_drawer'] -= $r['amount'];
		}*/
		
		$data['cash_in_drawer'] += $r['amount'];
	}
	$con_multi->sql_freeresult();
}

function load_drawer_open_count($counter_id,$bid,&$data){
    global $con,$smarty,$f_date,$con_multi;
    
    $filter[] = 'branch_id='.mi($bid);
    $filter[] = 'counter_id='.mi($counter_id);
    $filter[] = 'date='.ms($f_date);
	$filter[] = "type = 'POS'";
    $filter = join(' and ', $filter);
    
    $sql = "select count(*) as open_count from pos_drawer where $filter";
    $con_multi->sql_query($sql) or die(mysql_error());
	if($con_multi->sql_numrows()>0){
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$data['drawer_open_count'] = $temp['open_count'];
	}
	$con_multi->sql_freeresult();
}

function payment_details(){
    global $con,$smarty,$bid,$counter_id,$f_date,$con_multi;
	
	$filter[] = 'pos.branch_id='.mi($bid);
    $filter[] = 'pos.counter_id='.mi($counter_id);
    //$filter[] = 'pos.date='.ms(date('Y-m-d'));
    $filter[] = 'pos.date='.ms($f_date);
    $filter[] = 'pp.type<>'.ms('Cancel');
	$filter[] = 'pos.cancel_status<>1';
    
    $filter = join(' and ', $filter);
    
	$con_multi->sql_query("select pos.*,pp.type as pay_type,pp.amount as pay_amount from pos left join pos_payment pp on (pos.branch_id=pp.branch_id and pos.counter_id=pp.counter_id and pos.id=pp.pos_id and pos.date=pp.date) where $filter") or die(mysql_error());
	
	while($r = $con_multi->sql_fetchrow()){
	    if($r['pay_type']=='Cash'){
			$amt = $r['pay_amount']-$r['amount_change'];
		}else{
			$amt = $r['pay_amount'];
		}
		$payment_details[$r['pay_type']]['amount']+=$amt;
	}
	$con_multi->sql_freeresult();
	
	$smarty->assign('payment_details',$payment_details);
	$smarty->display('pos_live.payment_details.tpl');
}

function tran_details(){
   global $con,$smarty,$bid,$counter_id,$f_date,$con_multi;
	
	$filter[] = 'pos.branch_id='.mi($bid);
    $filter[] = 'pos.counter_id='.mi($counter_id);
    //$filter[] = 'pos.date='.ms(date('Y-m-d'));
    $filter[] = 'pos.date='.ms($f_date);
    //$filter[] = 'cancel_status=0';
    
    $filter = join(' and ', $filter);
    
	$con_multi->sql_query("select pos.*,pos.amount as payment_amount, user.u from pos left join user on pos.cashier_id=user.id where $filter order by end_time") or die(mysql_error());

	$smarty->assign('items',$con_multi->sql_fetchrowset());
	$con_multi->sql_freeresult();
	$smarty->assign('not_cc', 1);
	$smarty->display('counter_collection.sales_details.tpl');
}

// not using anymore since redirected to counter_collection.php
/*function item_details(){
    global $con,$smarty,$bid,$counter_id,$f_date;

	$filter[] = 'branch_id='.mi($bid);
    $filter[] = 'counter_id='.mi($counter_id);
    $filter[] = 'date='.ms($_REQUEST['date']);
    $filter[] = 'pos_id='.mi(intval($_REQUEST['id']));

    $filter = join(' and ', $filter);
    // get items
	$sql = "select pos_items.*,sku_items.description,sku_items.sku_item_code,sku_items.mcode from pos_items left join sku_items on pos_items.sku_item_id=sku_items.id where $filter";
	$con->sql_query($sql) or die(mysql_error());
	$temp = $con->sql_fetchrowset();
	foreach($temp as $r){
	    $r['selling_price'] = $r['price']-$r['discount'];
	    if($r['price']!=0){
            $r['discount_per'] = ($r['discount']/$r['price'])*100;
		}
		$table[] = $r;
		
		$total['qty'] += $r['qty'];
		$total['price'] += $r['price'];
		$total['discount'] += $r['discount'];
		$total['selling_price'] = $total['price']-$total['discount'];
		if($total['price']!=0){
            $total['discount_per'] = ($total['discount']/$total['price'])*100;
		}
	}
	// get receipt details
	$filter = array();
	$filter[] = 'pos.branch_id='.mi($bid);
    $filter[] = 'pos.counter_id='.mi($counter_id);
    $filter[] = 'pos.date='.ms($_REQUEST['date']);
    $filter[] = 'pos.id='.mi(intval($_REQUEST['id']));
    $filter = join(' and ', $filter);
    $sql = "select pos.*,user.u from pos left join user on pos.cashier_id=user.id where $filter";
    $q_rec = $con->sql_query($sql) or die(mysql_error());
    $receipt_detail = $con->sql_fetchrow($q_rec);
    
	$smarty->assign('item_details',$table);
	$smarty->assign('total',$total);
	$smarty->assign('receipt_detail',$receipt_detail);
	$smarty->display('pos_live.item_details.tpl');
}*/

function getBranchCode($bid){
	global $con,$con_multi;
	
	$con_multi->sql_query('select code from branch where id='.mi($bid)) or die(mysql_error());
	$temp = $con_multi->sql_fetchrow();
	$con_multi->sql_freeresult();
	return $temp['code'];
}

function ajax_unset_login_status(){
	global $con, $sessioninfo, $LANG;
	
	if (!privilege('POS_FORCE_LOGOUT')) die(sprintf($LANG['NO_PRIVILEGE'], 'POS_FORCE_LOGOUT', BRANCH_CODE));
	
	$bid = mi($_REQUEST['bid']);
	$cid = mi($_REQUEST['cid']);
	
	$con->sql_query("update counter_status set user_id=-1,lastping=lastping where branch_id=$bid and id=$cid and user_id>0");
	if($con->sql_affectedrows()){
		log_br($sessioninfo['id'], 'COUNTER STATUS', $cid, "Unset Counter Login Status, Branch ID#$bid, Counter ID#$cid");
	}
	print "OK";
}
?>
