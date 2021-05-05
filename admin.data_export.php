<?php
/* dump SKU for ATP front-end */

include("include/common.php");
if ($sessioninfo['level']<9999) header("Location: /");

if ($_REQUEST['id'] == '' || $_REQUEST['tb'] == '')
{
	show_form();
}
$lastid = intval($_REQUEST['id']);
$tb = $_REQUEST['tb'];

$con->sql_query("select * from $tb where id > $lastid");
if ($con->sql_numrows() <= 0)
{
	show_form("<font color=blue>No record to export</font><br>");
}

header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=\"$tb-$lastid.txt\"");

print "ARMS|$tb|$lastid\n";
$line = '';
for ($i=0;$i<$con->sql_numfields();$i++)
{
	$line .= $con->sql_fieldname($i) . "|";
}
print $line.md5($line)."\n";

while($r = $con->sql_fetchrow())
{
	$line = '';
	for ($i=0;$i<$con->sql_numfields();$i++)
	{
		$line .= $r[$i] . "|";
	}
	print $line.md5($line)."\n";
}

function show_form($msg = '')
{
	global $smarty;
	
	$smarty->assign("PAGE_TITLE", "Admin DB Export");
	$smarty->display("header.tpl");
	print $msg;
?>
<form>
<h1>ARMS Database Export</h1>
Select Table to export
<select name=tb>
<option value="vendor">Vendor</option>
</select><br /><br />
Enter last ID# being exported #<input name=id>
<input type=submit value="Dump">
</form>
SKU will be dump in a text file.
<?
	$smarty->display("footer.tpl");
	exit;
}
?>
