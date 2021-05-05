<?php
/*
5/4/2011 10:12:59 AM Justin
- Fixed the Sales Target show errors while not using HQ to create auto fill.
- Added the order by for branch by sequence.

6/27/2011 10:18:27 AM Andy
- Make all branch default sort by sequence, code.

1/31/2012 10:41:32 AM Justin
- Fixed the wrong display of current branch code when filter branch from HQ.

10/22/2018 5:10 PM Justin
- Enhanced to add "CONCESS" into SKU Type ENUM list when creating new table.
- Enhanced the module to compatible with new SKU Type.

2/2/2021 3:31 PM Andy
- Fixed Auto Fill will have bug if get last year Feb 29 sales as target for this year. 
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$months = array(1=>'January',	2=>'February',	3=>'March',	4=>'April',	5=>'May',	6=>'June',	7=>'July',	8=>'August',	9=>'September',	10=>'October',	11=>'November',	12=>'December');

//print_r($_REQUEST);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
	    case 'load_table':
			load_table();
			break;
		case 'save':
		    save_cell();
		    exit;
		case 'changeTD':
		    changeTD();
		    exit;
		case 'auto_fill_sales_target':
		    auto_fill_sales_target();
		    break;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

get_branch_list();
get_distinct_year();
get_sku_type();

$smarty->assign('PAGE_TITLE', 'Sales Target');
$smarty->assign('months', $months);
$smarty->display("sales_target.tpl");
exit;

function get_distinct_year(){
	global $con,$smarty;
	
	$con->sql_query('select distinct(year(date)) as year from pos where date>0') or die(mysql_error());
	$smarty->assign('year_list',$con->sql_fetchrowset());
}

function get_branch_list(){
	global $con,$smarty;

	$con->sql_query('select * from branch order by sequence,code') or die(mysql_error());
	$smarty->assign('branch_list',$con->sql_fetchrowset());
}

function get_sku_type(){
    global $con,$smarty;

	$con->sql_query('select * from sku_type order by code') or die(mysql_error());
	$smarty->assign('sku_type',$con->sql_fetchrowset());
}

function generate_dates($fr, $to, $keyfmt, $valuefmt){
	for($d=strtotime($fr);$d<=strtotime($to);$d+=86400){
		$ret[date($keyfmt,$d)]['date'] = date($valuefmt,$d);
		$ret[date($keyfmt,$d)]['month'] = date("m",$d);
		$ret[date($keyfmt,$d)]['day'] = date("d",$d);
		$ret[date($keyfmt,$d)]['sun'] = date("D",$d);
	}
	return $ret;
}

function get_category_list(){
    global $con,$smarty,$sessioninfo;

	if($sessioninfo['level']>=9999){
        $con->sql_query('select * from category where level=2 order by description') or die(mysql_error());
	}else{
        $con->sql_query("select * from category where id in ($sessioninfo[department_ids]) order by description");
	}
    
	$smarty->assign('cat_list',$con->sql_fetchrowset());
}

function load_table(){
	global $smarty,$con;
	
	$year = intval($_REQUEST['year']);
	$month = intval($_REQUEST['month']);
	if (BRANCH_CODE == 'HQ'){
        $branch = intval($_REQUEST['branch']);
	}else{
		$branch = get_request_branch(true);
	}
	
	$con->sql_query("create table if not exists sales_target_b$branch ( `date` date NOT NULL default '0000-00-00', `year` int(11) NOT NULL default '0', `month` int(11) NOT NULL default '0', `sku_type` enum('CONSIGN','OUTRIGHT','CONCESS') NOT NULL default 'CONSIGN', `department_id` int(11) NOT NULL default '0', `target` double default NULL, PRIMARY KEY (`date`,`year`,`month`,`department_id`,`sku_type`), KEY `sku_type` (`sku_type`,`year`,`month`) )");
	if($month==0){
		$month=1;
		$start_date = $year."-".$month."-1";
	    $end_date =date("Y-m-d",strtotime("+1 year",strtotime($start_date))-86400);

	    $sql = "select * from sales_target_b$branch where date between ".ms($start_date)." and ".ms($end_date);
	}else{
        $start_date = $year."-".$month."-1";
	    $end_date =date("Y-m-d",strtotime("+1 month",strtotime($start_date))-86400);

	    $sql = "select * from sales_target_b$branch where month=$month and year=$year";
	}

	//print $sql;
	$con->sql_query($sql) or die(mysql_error());

	while($r = $con->sql_fetchrow()){
		$table[$r['department_id']][$r['sku_type']][$r['date']] = $r['target'];
		$total['row'][$r['department_id']][$r['sku_type']] += $r['target'];
		$total['column'][$r['sku_type']][$r['date']] += $r['target'];
		$total['total'][$r['sku_type']]['total'] += $r['target'];
	}

	//print_r($table);
	$smarty->assign('total',$total);
	$smarty->assign('table',$table);
	$branch_code = get_branch_code($branch);
	$smarty->assign('branch_code', $branch_code);
	$date_label = generate_dates($start_date,$end_date,"Y-m-d","Y-m-d");
	$smarty->assign('date_label',$date_label);

	get_category_list();
}

function save_cell(){
    global $smarty,$con;
    
    $date = $_REQUEST['date'];
    $category_id = intval($_REQUEST['category_id']);
    $sku_type = $_REQUEST['sku_type'];
    
    if (BRANCH_CODE == 'HQ'){
        $branch_id = intval($_REQUEST['branch_id']);
	}else{
		$branch_id = get_request_branch(true);
	}
    
    $filter[] = "date=".ms($date);
    $filter[] = "department_id=".mi($category_id);
    $filter[] = "sku_type=".ms($sku_type);
    
    $filter = join(" and ",$filter);
    
    $upd['target'] = intval($_REQUEST['target']);
    $upd['date'] = $date;
	$upd['department_id'] = $category_id;
	$upd['sku_type'] = $sku_type;
	$upd['year'] = date('Y',strtotime($date));
	$upd['month'] = date('m',strtotime($date));
		
    $sql = "replace into sales_target_b$branch_id ".mysql_insert_by_field($upd);
    $con->sql_query($sql) or die(mysql_error());
	
    print number_format($upd['target']);
}

function changeTD(){
    global $smarty,$con;
    
	$start_date = $_REQUEST['start_date'];
	$end_date = $_REQUEST['end_date'];
	$category_id = intval($_REQUEST['category_id']);
    $sku_type = $_REQUEST['sku_type'];
    $upd['target'] = intval(str_replace(',', '', $_REQUEST['target']));

    if (BRANCH_CODE == 'HQ'){
        $branch_id = intval($_REQUEST['branch_id']);
	}else{
		$branch_id = get_request_branch(true);
	}
	
    $filter[] = "date>=".ms($start_date);
    $filter[] = "date<=".ms($end_date);
    $filter[] = "department_id=".mi($category_id);
    $filter[] = "sku_type=".ms($sku_type);

    $filter = join(" and ",$filter);

	$date = $start_date;
	
	while($date<=$end_date){
        $upd['date'] = $date;
		$upd['department_id'] = $category_id;
		$upd['sku_type'] = $sku_type;
		$upd['year'] = date('Y',strtotime($date));
		$upd['month'] = date('m',strtotime($date));

		$sql = "replace into sales_target_b$branch_id ".mysql_insert_by_field($upd);
		
		//die("Error: ".$sql);
		$con->sql_query($sql) or die(mysql_error());
		$date = date("Y-m-d",strtotime($date)+86400);
	}
}

function auto_fill_sales_target(){
	global $con,$smarty,$sessioninfo;
	
	$year = intval($_REQUEST['year']);
	$month = intval($_REQUEST['month']);
	$branch_id = intval($_REQUEST['branch']);
	// if found no branch id it is not from HQ, take from session info
	if(!$branch_id && BRANCH_CODE != "HQ") $branch_id = $sessioninfo['branch_id'];
	$target_per = intval($_REQUEST['target_per']);
	$replace_type = $_REQUEST['replace_type'];
	$round = intval($_REQUEST['nearest_round_up']);
	$last_year = $year-1;
	
	if($round<=0||$round>1000){
        $round = 100;
	}
	
	$filter = array();
	$filter[] = "sku_type in ('CONSIGN','OUTRIGHT','CONCESS')";
	$filter[] = "year=".$last_year;
	if($replace_type != 'year') $filter[] = "month=$month";
	$filter[] = "date_format(concat_ws('-',$year,month,day(date)), '%Y')>0";
	$filter = "where ".join(' and ', $filter);
	
	$tbl_name = "category_sales_cache_b".$branch_id;
	
	$per = (100+$target_per)/100;
	
	$sql = "replace into sales_target_b$branch_id (select concat_ws('-',$year,month,day(date)) as date,$year,month,cs.sku_type,cc.p2, ceil((sum(cs.amount)*$per)/$round)*$round as amt
from $tbl_name cs left join category_cache cc using(category_id) $filter group by p2,sku_type,date)";
	//print $sql;exit;
	$con->sql_query($sql) or die(mysql_error());
	//print $sql;
	$log_info = "Update Sales Target using auto fill in: year: $year";
	if($replace_type != 'year') $log_info.=" month=$month";
	
	log_br($sessioninfo['id'],"Sales Target Generate: Auto Fill In",'',$log_info);
	load_table();
}
?>
