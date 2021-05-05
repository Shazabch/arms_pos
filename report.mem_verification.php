<?php

/*

9/4/2012 13:40:00 PM Fithri
 - Add 'Total' column

*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('RPT_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'RPT_MEMBERSHIP', BRANCH_CODE), "/index.php");

$dt1 = strval($_REQUEST['dt1']);
$dt2 = strval($_REQUEST['dt2']);

if ($_REQUEST['a'] != '')
{
	switch ($_REQUEST['a'])
	{
	    case 'ajax_refresh' :
			print_table();
			exit;

	    default:
	        print_r($_REQUEST);
			exit;
	}
}

// by defauilt show table
$smarty->assign("PAGE_TITLE", "Membership Verification Report");
$smarty->display("header.tpl");

print "<h1>Membership Verification Report</h1>";

do_form();

print "<div id=udiv class=stdframe>";
print_table();
print "</div>";

$smarty->display("footer.tpl");

function print_table()
{
	require_once("include/gdgraph.php");
	global $con, $dt1, $dt2;

	// get unverified count
	$con->sql_query($abc="select count(*) as c, branch.code as b from membership left join branch on membership.apply_branch_id = branch.id where blocked_by = 0 and verified_by = 0 group by b");
	//sprint $abc;
	$unverified = array();
	$unique_br = array();
	while ($r = $con->sql_fetchrow())
	{
	    $unique_br[$r['b']] = 1;
	    $unverified[$r['b']] = $r['c'];
	}

	// get verified count
	$con->sql_query("select count(*) as c, DATE(verified_date) as d, branch.code as b from membership left join branch on membership.apply_branch_id = branch.id where (verified_date >= ".ms($dt1)." and verified_date <= ".ms($dt2).") and verified_by > 0 group by b, d");
	$verified = array();
	$unique_date = array();
	$vtotal = array();
	while ($r = $con->sql_fetchrow())
	{
	    $unique_date[$r['d']] = 1;
	    $unique_br[$r['b']] = 1;
	    $verified[$r['b']][$r['d']] = $r['c'];
	    $vtotal[$r['b']]+=$r['c'];
	}

	print "<table cellspacing=10><tr><td valign=top>";
	print "<table class=body border=0 cellspacing=1 cellpadding=4>";
	print "<tr bgcolor=#ffcc66><td>Date</td>";
	foreach (array_keys($unique_br) as $br)
	{
	    if (empty($br)) $br='NOT SET';
		print "<th>$br</th>";
	    $colors[$br] = get_color($br);
	}
	print "<th>TOTAL</th>";
	print "</tr>";
	ksort($unique_date);
	reset($unique_date);
	foreach (array_keys($unique_date) as $dt)
	{
	    print "<tr bgcolor=#ffffff>";
	    print "<td>$dt</td>";
		$td = 0;
		foreach (array_keys($unique_br) as $br)
		{
		    $v = intval($verified[$br][$dt]);
			$td += $v;
		    $arr[$br][] = $v;
		    print "<td>$v</td>";
		}
		print "<td>$td</td>";
	    print "</tr>";
	}
	print "<tr bgcolor=#ffcc66><td>Total Verified</td>";
	$tv = 0;
	foreach (array_keys($unique_br) as $br)
	{
		$v = intval($vtotal[$br]);
		$tv += $v;
		$p1[$br] = get_color($br);
		array_unshift($p1[$br], $v);
		$thickness[$br] = 10;
	    print "<td>$v</td>";
	}
	print "<td>$tv</td>";
	print "</tr>";
	print "<tr bgcolor=#ffcc66><td>Total Unverified</td>";
	$tuv = 0;
	foreach (array_keys($unique_br) as $br)
	{
		$v = intval($unverified[$br]);
		$tuv += $v;
		$p2[$br] = get_color($br);
		array_unshift($p2[$br], $v);
	    print "<td>$v</td>";
	}
	print "<td>$tuv</td>";
	print "</tr>";
	print "</table>";

//$w, $h, $tmp, $t="", $l=false, $bg_c_r=255, $bg_c_g=255, $bg_c_b=255, $l_c_r=0, $l_c_g=0, $l_c_b=0, $str_c_r=0, $str_c_g=0, $str_c_b=0,$l_x=NULL,$l_y=NULL,$l_border=true,$trans_back=false, $l_thickness=1


	print "</td><td valign=top>";
	
	/*
	echo '<pre>';
	print_r($unique_br);
	echo '</pre>';
	*/
	
	$gdg = new GDGraph(250,200,"tmp/g1_$sessioninfo[id].png","",false,255,255,255,0,0,0,0,0,0,NULL,NULL,true,true);
	$gdg->title = "Total Verified";
	$gdg->pie_graph($p1, 80, true, 0, false, $thickness,3);
	print "<img src=$gdg->tmpfile>";

	$gdg = new GDGraph(250,200,"tmp/g2_$sessioninfo[id].png","",false,255,255,255,0,0,0,0,0,0,NULL,NULL,true,true);
	$gdg->title = "Total Unverified";
	$gdg->pie_graph($p2, 80, true, 0, false, $thickness,3);
	print "<img src=$gdg->tmpfile><br />";


	$gdg = new GDGraph(500,500,"tmp/g0_$sessioninfo[id].png","",false,255,255,255,0,0,0,0,0,0,NULL,NULL,true,true);
	$gdg->title = "Verification History";
	$gdg->yformat = "%d";
	$gdg->legend = true;
	$gdg->line_graph($arr,$colors,array(),"","",true,1,9);
	print "<img src=$gdg->tmpfile><br />";

	print "</td></tr></table>";
}

function do_form()
{
	global $dt1, $dt2;

	if ($dt1 == '') $dt1 = date('Y-m-d', strtotime('-1 month'));
	if ($dt2 == '') $dt2 = date('Y-m-d');

	// calendar plugin
?>
<script>
function update_table()
{
	new Ajax.Updater("udiv", "<?=$_SERVER['PHP_SELF']?>", {
	    parameters: 'a=ajax_refresh&'+Form.serialize('f_a')
	});
}

</script>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<p>
<form id=f_a>
From <input type="text" name="dt1" id="dt1" readonly="1" value="<?=$dt1?>" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_dt1" style="cursor: pointer;" title="Select Date"/>

To <input type="text" name="dt2" id="dt2" readonly="1" value="<?=$dt2?>" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_dt2" style="cursor: pointer;" title="Select Date"/>

<input type=button onclick="update_table()" value="Update">
</form>
</p>

<script type="text/javascript">


    Calendar.setup({
        inputField     :    "dt1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_dt1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "dt2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_dt2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true //,
        //onUpdate       :    load_data
    });

</script>

<?

}
?>
