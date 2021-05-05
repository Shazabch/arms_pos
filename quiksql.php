<?php
/*
2/6/2013 10:24 AM Andy
- Change password and only user id 1 able to access without passowrd.

1/8/2016 11:00 AM Andy
- Change UID.

8/18/2016 1:43 PM Andy
- Change UID to aa5757.

8/14/2017 2:15 PM Andy
- Change UID to ar1757.

2/23/2018 10:05 AM Andy
- Change UID to arx182.

10/22/2018 10:03 AM Andy
- Change UID to Ar1819.

2/25/2019 11:44 AM Andy
- Change Quick SQL to check the ID in default config.
*/
include("include/common.php");

if (!defined('MASTER_QUIK_UID') || $_REQUEST['uid']!== MASTER_QUIK_UID) // no uid cannot
{
	if ($_SERVER['SERVER_NAME'] != 'maximus' && $sessioninfo['id'] != 1)
	{
		header("Location: /");
		exit;
	}
}

//ini_set("display_errors", 1);
//error_reporting(E_ALL);

if (isset($_REQUEST['sql'])) $_REQUEST['sql'] = str_replace('whe-re', 'where', $_REQUEST['sql']);
$s = (isset($_REQUEST['sql'])) ? stripslashes($_REQUEST['sql']) : "";
?>
<script>
function fixwhere()
{
document.forms[0].sql.value = document.forms[0].sql.value.replace(/where/ig,'whe-re');
}
</script>
<style>
table { font: 11px Arial; }
</style>

<form method=post onsubmit="fixwhere()">
sql:<br>
<textarea name=sql style="height:20%;width:100%"><?=htmlentities($_REQUEST['sql'])?></textarea><br>
<input type=submit>
</form>
<?

if (preg_match('/drop.*database/i',$s)) die("No DROP DATABASE allowed");

if ($s)
{
	$con->sql_query($s) or print("<font color=red>" . mysql_error() . "</font><br>");

print "<pre>";
print "SQL: $query_count<br />$query_time</pre>";

	if ($con->sql_numrows())
	{
		print "Rows returned: " . $con->sql_numrows();
		// print into table
		print "<table border=1 width=600><tr>";
		for ($i = 0; $i < $con->sql_numfields(); $i++)
		{
			$k = $con->sql_fieldname($i);
			$fieldname[$i] = $k;
			print "<td><b>$k</b></td>";
		}
		print "</tr>";
		while($row = $con->sql_fetchrow())
		{
			print "<tr>";
			for ($i = 0; $i < $con->sql_numfields(); $i++)
			{
				$v = $row[$fieldname[$i]];
				print "<td>$v</td>";
			}
			print "</tr>";
		}
		print "</table>";
	}
	else
		print "Rows affected: " . $con->sql_affectedrows();
}
?>
