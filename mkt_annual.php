<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT_ANNUAL_VIEW') and !privilege('MKT_ANNUAL_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_ANNUAL_VIEW / EDIT', BRANCH_CODE), "/index.php");

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT_ANNUAL_%'");
while ($r = $con->sql_fetchrow()){
	$mkt_annual_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt_annual_privilege", $mkt_annual_privilege);
//echo"<pre>";print_r($mkt_annual_privilege);echo"</pre>";

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id =get_request_branch();
if(!$branch_id){
	$branch_id=$sessioninfo['branch_id'];
}

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
	    case 'save_edit':
	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	    	$form=$_REQUEST;
	    	$month=intval($form['month']);
	    	$year=intval($form['year']);
	    	$form['branch_id']=$branch_id;
     		$form['cat_root_id']=intval($form['line_id']);
	    	$field=$form['field'];
	    	$value=$form['value'];
	    	$form[$field]=$value;
	    	
			if (!privilege('MKT_ANNUAL_EDIT')) {
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_ANNUAL_EDIT', BRANCH_CODE), "/mkt_annual.php?branch_id=$branch_id&year=$year");
			}

	    	if($field=='total_target' or $field=='total_forecast'){
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
           			$con->sql_query("update mkt_review set $field=$value where month='$month' and year='$year' and branch_id=$branch_id");
				}
				else{
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id',$field)));
				}
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r2 = $con->sql_fetchrow();
			}
			
			elseif($field=='line_contribute'){

	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
					$line=unserialize($r['line_contribute']);

					$line[$form['cat_root_id']] = $value;

					$value=sz($line);
           			$con->sql_query("update mkt_review set $field=$value where month='$month' and year='$year' and branch_id=$branch_id");
				}
				else{
				    $form['line_contribute']=$value;
					$form['line_contribute']=serialize($form['line_contribute']);
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id','line_contribute')));
				}
	    		//$r2[$field]="$form[value]";$form[line_contribute]
				$r2[$field]="$form[line_contribute]";
			}
			elseif ($field=='adjustment'){
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r = $con->sql_fetchrow();
	    		if($r){
           			$con->sql_query("update mkt_review set $field=$value where month='$month' and year='$year' and branch_id=$branch_id");
				}
				else{
					$con->sql_query("insert into mkt_review ".mysql_insert_by_field($form,array('month','year','branch_id',$field)));
				}
	    		$con->sql_query("select * from mkt_review where month='$month' and year='$year' and branch_id=$branch_id");
	    		$r2 = $con->sql_fetchrow();
			}
	        if ($form['value'] == '')
	            print "&nbsp;";
			else
	        	print number_format($r2[$field],2);
	        exit;

	    case 'approve':
 	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	    	$year=intval($_REQUEST['year']);

			if (!privilege('MKT_ANNUAL_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_ANNUAL_EDIT', BRANCH_CODE), "/mkt_annual.php?branch_id=$branch_id&year=$year");
			//get current year data for (month 4-12)
			$s_m=$config['mkt_started_month']-1;
			$c1=$con->sql_query("select * from mkt_review where year='$year' and branch_id=$branch_id and month>$s_m");
			while($r=$con->sql_fetchrow($c1)){
				$con->sql_query("update mkt_review set approve=1 where year='$year' and branch_id=$branch_id and month=$r[month]");
			}
			//get next year data for (month 1-3)
			$s_m=$config['mkt_started_month'];
			$year=$year+1;
			$c2=$con->sql_query("select * from mkt_review where year=$year and branch_id=$branch_id and month<$s_m");
			while($r2=$con->sql_fetchrow($c2)){
				$con->sql_query("update mkt_review set approve=1 where year='$year' and branch_id=$branch_id and month=$r2[month]");
			}
	    	break;

	    case 'confirm':
 	    	//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	    	$year=intval($_REQUEST['year']);

			if (!privilege('MKT_ANNUAL_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_ANNUAL_EDIT', BRANCH_CODE), "/mkt_annual.php?branch_id=$branch_id&year=$year");
			//get current year data for (month 4-12)
			$s_m=$config['mkt_started_month']-1;
			$c1=$con->sql_query("select * from mkt_review where year='$year' and branch_id=$branch_id and month>$s_m");
			while($r=$con->sql_fetchrow($c1)){
				$con->sql_query("update mkt_review set status=1 where year='$year' and branch_id=$branch_id and month=$r[month]");
			}
			//get next year data for (month 1-3)
			$s_m=$config['mkt_started_month'];
			$year=$year+1;
			$c2=$con->sql_query("select * from mkt_review where year=$year and branch_id=$branch_id and month<$s_m");
			while($r2=$con->sql_fetchrow($c2)){
				$con->sql_query("update mkt_review set status=1 where year='$year' and branch_id=$branch_id and month=$r2[month]");
			}
	    	break;
	    	
	    case 'search':
			break;
		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

get_detail();
$smarty->display("mkt_annual.tpl");
exit;

function get_detail(){

	global $smarty,$con,$sessioninfo,$year,$config;
	
	$branch_id =get_request_branch();
	$smarty->assign("branch_id", $branch_id);
	
	if ($_REQUEST['year'])
	    $year=$_REQUEST['year'];
	else
		$year=date("Y");

	$smarty->assign('PAGE_TITLE', 'Annual Planner and Review for Year '.$year);
 	if ($_REQUEST['id'])
	    $id=mi($_REQUEST['id']);
	else
		$id=1;

	$smarty->assign("branch_id", $branch_id);
	$smarty->assign("year", $year);
	
	$arr_month = array (
		"1" => "January",
		"2" => "February",
		"3" => "March",
		"4" => "April",
		"5" => "May",
		"6" => "June",
		"7" => "July",
 		"8" => "August",
  		"9" => "September",
   		"10" => "October",
    	"11" => "November",
		"12" => "December",
	);
	$smarty->assign("arr_month", $arr_month);
	
	//get current year data for (month 4-12)
	$s_m=$config['mkt_started_month']-1;
	$c1=$con->sql_query("select * from mkt_review where year='$year' and branch_id=$branch_id and month>$s_m");
	while($r=$con->sql_fetchrow($c1)){
	    if(!$r['approve']){
		    if($r['status']){
				$smarty->assign("status",1);
			}
		}
		else{
			$smarty->assign("approve",1);
		}
	
		$c2=$con->sql_query("select sum(normal_forecast) as normal_forecast,sum(sales_target) as sales_target,sum(sales_achieve) as sales_achieve from mkt_review_items where branch_id=$branch_id and YEAR(date)=$year and MONTH(date)=$r[month]");
		$r1=$con->sql_fetchrow($c2);
		$line[$year][$r['month']]['forecast']=$r1['normal_forecast'];
		$line[$year][$r['month']]['target']=$r1['sales_target'];
		$line[$year][$r['month']]['sales']=$r1['sales_achieve'];
		$line[$year][$r['month']]['adjustment']=$r['adjustment'];
		$line[$year][$r['month']]['total_target']=$r['total_target'];
		$line[$year][$r['month']]['total_forecast']=$r['total_forecast'];

	    $ls=unserialize($r[line_contribute]);
	    if($ls){
		error_reporting(0);
						
			foreach($ls as $k=>$v){
		 		$c3=$con->sql_query("select sum(sales_amount) as sum_amt from mkt_review_contribute where branch_id=$branch_id and YEAR(date)=$year and MONTH(date)=$r[month] and cat_root_id=$k order by date");

				$r3=$con->sql_fetchrow($c3);
				$line[$year][$r['month']][$k]['act']=$r3['sum_amt'];

				$line[$year][$r['month']][$k]['amt']=$v;

				$line['total_amt'][$k]+=($r['total_target']*$v/100);
				$line['total_pct'][$k]+=$v;
				$line['total_act'][$k]+=$r3['sum_amt'];
				$total_sales+=$r3['sum_amt'];
			}
		}
		$line[$year][$r['month']]['sales']=$total_sales;
		$total_sales=0;
	}
	
	//get next year data for (month 1-3)
	$s_m=$config['mkt_started_month'];
	$year=$year+1;
	$c1=$con->sql_query("select * from mkt_review where year=$year and branch_id=$branch_id and month<$s_m");
	while($r=$con->sql_fetchrow($c1)){
		$c2=$con->sql_query("select sum(normal_forecast) as normal_forecast, sum(sales_target) as sales_target, sum(sales_achieve) as sales_achieve from mkt_review_items where branch_id=$branch_id and YEAR(date)=$year and MONTH(date)=$r[month]");
		$r1=$con->sql_fetchrow($c2);
		$line[$year][$r['month']]['forecast']=$r1['normal_forecast'];
		$line[$year][$r['month']]['target']=$r1['sales_target'];
		$line[$year][$r['month']]['sales']=$r1['sales_achieve'];
 		$line[$year][$r['month']]['adjustment']=$r['adjustment'];
		$line[$year][$r['month']]['total_target']=$r['total_target'];
		$line[$year][$r['month']]['total_forecast']=$r['total_forecast'];

	    $ls=unserialize($r[line_contribute]);
	    if($ls){
		error_reporting(0);
			foreach($ls as $k=>$v){
		 		$c3=$con->sql_query("select sum(sales_amount) as sum_amt from mkt_review_contribute where branch_id=$branch_id and YEAR(date)=$year and MONTH(date)=$r[month] and cat_root_id=$k order by date");
				$r3=$con->sql_fetchrow($c3);
				$line[$year][$r['month']][$k]['act']=$r3['sum_amt'];

				$line[$year][$r['month']][$k]['amt']=$v;

				$line['total_amt'][$k]+=($r['total_target']*$v/100);
				$line['total_pct'][$k]+=$v;
				$line['total_act'][$k]+=$r3['sum_amt'];
				$total_sales+=$r3['sum_amt'];
			}
		}
		$line[$year][$r['month']]['sales']=$total_sales;
		$total_sales=0;
	}
	//echo"<pre>";print_r($line);echo"</pre>";
	$smarty->assign("line",$line);

	$con->sql_query("select * from category where root_id=0 order by id");
	$smarty->assign("category",$con->sql_fetchrowset());
}

?>
