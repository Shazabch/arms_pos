<?php
/*
10/20/2011 2:56:56 PM Alex
- move load_counter() & load_branches() to here

7/19/2012 10:46 PN Andy
- order branch list by sequence,code.

10/17/2012 11:02 AM Andy
- Add free result for some sql.

4/14/2015 1:34 PM Andy
- Enhance to filter out branch in config "sales_report_branches_exclude".
*/

function load_counter(){
	global $con,$smarty,$sessioninfo;
	
	if (BRANCH_CODE!='HQ'){
		$filter[] = "c.branch_id=".mi($sessioninfo['branch_id']);
	}
	$filter[] = "c.active=1";
	$filter = join(' and ',$filter);
	$sql = "select c.*,branch.code from counter_settings c left join branch on c.branch_id=branch.id where $filter order by branch.sequence,branch.code,network_name";
	$q_c = $con->sql_query($sql) or die(mysql_error());
	$counters = array();
	while($r = $con->sql_fetchassoc()){
		$counters[] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign('counters', $counters);
	
	return $counters;
}

function load_branches(){
	global $con,$smarty, $config;
	
	$q_b = $con->sql_query("select id,code from branch order by sequence,code") or die(mysql_error());
	while($r = $con->sql_fetchrow($q_b)){
		if($config['sales_report_branches_exclude'] && is_array($config['sales_report_branches_exclude']) && in_array($r['code'],$config['sales_report_branches_exclude'])){
			continue;
		}
		$branches[$r['id']] = $r['code'];
	}
	$smarty->assign('branches',$branches);
	$con->sql_freeresult();
	
	return $branches;
}
?>
