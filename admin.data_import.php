<?php
/* dump SKU for ATP front-end */

include("include/common.php");
if ($sessioninfo['level']<9999) header("Location: /");

if (!$_FILES['f'] || $_FILES['f']['error'])
{
	show_form();
}

// check and perform sql-insertion
$f = $_FILES['f'];
$fp = fopen($f['tmp_name'], "r");

$hdr = fgetcsv($fp, 5000, "|");
if ($hdr[0] != 'ARMS') show_form("<font color=blue>Invalid File Header $hdr[0]</font>");
$tb = $hdr[1];
$lastid = $hdr[2];

// column names
$cols = fgetcsv($fp, 5000, "|");
hashcheck_line($cols);

// TRANSACTION not supported by myisam tables
$con->sql_query("START TRANSACTION");
$err = 0;
while($data = fgetcsv($fp, 5000, "|"))
{
	hashcheck_line($data);
	$vv = '';
	foreach($data as $d)
	{
		if ($vv != '') $vv .= ",";
		$vv .= ms($d);
	}
	if (!$con->sql_query("insert into $tb (".join(",",$cols).") values (".$vv.")",false,false))
	{
		$err = 1;
		print "<li> " . mysql_error();
	}
}
if ($err)
{
	// TRANSACTION not supported by myisam tables
	$con->sql_query("ROLLBACK");
	print "<p>Please fix the errors and try again. <a href=\"$_SERVER[PHP_SELF]\">Click here to continue</a></p>";
	exit;
}
// TRANSACTION not supported by myisam tables
$con->sql_query("COMMIT");	
print "<p>Import successfully. <a href=\"$_SERVER[PHP_SELF]\">Click here to continue</a></p>";

function show_form($msg = '')
{
	global $smarty;
	
	$smarty->assign("PAGE_TITLE", "Admin DB Import");
	$smarty->display("header.tpl");
	
	print $msg;
?>
<form enctype="multipart/form-data" method=post>
<h1>ARMS Database Import</h1>
Select File to Import <input type=file name=f size=30>
<input type=submit value="Upload">
</form>
<?
	$smarty->display("footer.tpl");
	exit;
}

function hashcheck_line(&$c)
{
	$a = array_pop($c);
//	print "<li>checksum $a vs ".md5(join("|",$c)."|");
	if ($a != md5(join("|",$c)."|")) show_form("Checksum failed");
//	print " - Ok";
}
?>
