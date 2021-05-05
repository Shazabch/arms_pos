<?php
/*
segi membership expire point report yinsee
*/
include("../../include/common.php");

//if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class MEMBER_POINT_EXPIRE_REPORT extends Module{

	function _default()
	{
		global $con, $smarty, $sessioninfo;
	
		$con->sql_query("select sum(a) as a, sum(s) as s, sum(r) as r, y, m from tmp_point_calculations group by y, m order by y, m");
		while($r = $con->sql_fetchrow())
		{
			$r['exp'] = date('Y-m-d', strtotime("+13 month", strtotime("$r[y]-$r[m]-1")));
			$data[$r['y']."/".$r['m']] = $r;
			$totalpoints += $r['r'];
		}
		$con->sql_query("select sum(points) as p, date(date) as d, remark from membership_points where type='EXPIRED' group by remark ");			while($r = $con->sql_fetchrow())
		{
			list($m,$y) = explode("/", $r['remark']);
			$data[intval($y)."/".intval($m)]['p'] = $r['p'];
			$data[intval($y)."/".intval($m)]['d'] = $r['d'];
			$totalflush += $r['p'];
			$lastflushdate = $r['d'];
		}
	
		$smarty->assign("data", array_values($data));
		$smarty->assign("totalrows", count($data));
		$smarty->assign("totalpoints", $totalpoints);
		$smarty->assign("totalflush", $totalflush);
		$smarty->assign("lastflushdate", $lastflushdate);
		$smarty->display("segi/report.member_point_expire.tpl");
	}
	

}

new MEMBER_POINT_EXPIRE_REPORT('Member Point Summary by Year and Months');
?>
