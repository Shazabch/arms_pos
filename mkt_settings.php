<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT_SETTING')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_SETTING', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'MKT Settings');

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'save':
	    	//print_r($_REQUEST);
	        foreach($_REQUEST['settings'] as $bid => $bsettings)
	        {
	            foreach($bsettings as $did => $dsettings)
		        {
		            $dsettings['branch_id'] =  $bid;
		            $dsettings['dept_id'] =  $did;
		            $con->sql_query("replace into mkt_settings ".mysql_insert_by_field($dsettings, array('branch_id', 'dept_id', 'min_offer', 'max_offer', 'min_brand', 'max_brand'))) or die(mysql_error());
				}
			}
			print "Saved.";
	        exit;
	        
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;

	}
}

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

// get the allowed line and department for this user
$con->sql_query("select line.id as line_id, line.description as line, dept.id as dept_id, dept.description as dept from category dept left join category line on dept.root_id = line.id where dept.level=2 and dept.active and line.active order by line,dept");

$lines = array();
while($r=$con->sql_fetchrow())
{
	$lines[$r['line']]['id'] = $r['line_id'];
	$lines[$r['line']]['dept'][] = array('description'=>$r['dept'], 'id'=>$r['dept_id']);
}
$smarty->assign("lines", $lines);

// load setting
$con->sql_query("select * from mkt_settings");
while($r=$con->sql_fetchrow())
{
	$tb[$r['branch_id']][$r['dept_id']] = $r;
}
$form['settings'] = $tb;
$smarty->assign("form",$form);
$smarty->display("mkt_settings.tpl");
?>
