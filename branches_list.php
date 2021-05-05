<?php
/*
6/24/2011 3:43:22 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['consignment_modules'])	js_redirect(sprintf("No Consignment Module", 'Branches List', BRANCH_CODE), "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$con->sql_query("select * from branch order by sequence,code") or die(mysql_error());
while($r = $con->sql_fetchrow()){
	$branches_info[$r['id']] = $r;
}

$q_bg = $con->sql_query("select * from branch_group order by code") or die(mysql_error());
while($bg = $con->sql_fetchrow($q_bg)){
	$bg_info[$bg['id']] = $bg;
	$q_b = $con->sql_query("select bgi.* from branch_group_items bgi left join branch on branch.id=bgi.branch_id where bgi.branch_group_id=".mi($bg['id'])." order by branch.sequence, branch.code") or die(mysql_error());
	while($b = $con->sql_fetchrow($q_b)){
		$branches_group[$bg['id']][$b['branch_id']] = $b;
		$branches_have_group[$b['branch_id']] = $b['branch_id'];
	}
}

$smarty->assign('branches_group',$branches_group);
$smarty->assign('bg_info',$bg_info);
$smarty->assign('branches_info',$branches_info);
$smarty->assign('branches_have_group',$branches_have_group);
$smarty->display('branches_list.tpl');

/*
if($branches_group){
	print "<table border=1 style='font-size:12px;' width='100%'>";
	foreach($branches_group as $bg_id=>$bg){
		print "<tr>";
		print "<th colspan=2>".$bg_info[$bg_id]['code']." - ".$bg_info[$bg_id]['description']."</th>";
		print "</tr>";
		foreach($bg as $bid=>$b){
			if($branches_info[$bid]['active'])	print "<tr>";
			else 	print "<tr style='color:grey;'>";
			print "<td>".$branches_info[$bid]['code']."</td><td>".$branches_info[$bid]['description']."</td>";
			print "</tr>";
		}
	}
	print "</table>";
}

if(count($branches_info)>count($branches_have_group)){
	print "<hr><table border=1 style='font-size:12px;' width='100%'>";
	foreach($branches_info as $bid=>$b){
		if(!$branches_have_group[$bid]){
			if($b['active'])	print "<tr>";
			else 	print "<tr style='color:grey;'>";
			print "<td>".$b['code']."</td><td>".$b['description']."</td>";
			print "</tr>";
		}
	}
	print "</table>";
	
	print "Grey = Inactive";
}
*/

?>

