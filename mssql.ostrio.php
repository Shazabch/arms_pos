<?php
/*
2/10/2020 2:33 PM Andy
- Changed UID
- Moved the error_reporting line to above db include.
- Fixed php short open tag.

6/18/2020 3:28 PM Andy
- Changed UID
*/
if ($_REQUEST['uid']!=="q2020ne") // no uid cannot
{
	if ($_SERVER['SERVER_NAME'] != 'maximus')
	{
		//header("Location: /");
		exit;
	}
}

ini_set("display_errors", 1);
error_reporting(E_ALL);

include('ostrio.db.php');
if(!isset($db_ostrio)){
	die('OSTrio DB Not Setup');
}
$con2 = new PDO('dblib:host='.$db_ostrio[0].';dbname='.$db_ostrio[3], $db_ostrio[1], $db_ostrio[2]);

//require_once('msdb.php');

$s = (isset($_REQUEST['sql'])) ? stripslashes($_REQUEST['sql']) : "";
?>
<style>
table { font: 11px Arial; }
</style>

<form method=post>
sql:<br>
<textarea name=sql rows=5 cols=50 style="width:100%;height:100px;"><?=htmlspecialchars($s)?></textarea><br>
<input type=submit>
</form>
<?php

if ($s)
{
	

	//$con->sql_query($s) or print("<font color=red>" . mysql_error() . "</font><br>");
	$ret = $con2->query($s);
	if(!$ret){
		$err_info = $con2->errorInfo();
		print $err_info[2];
		die();
	}

	$print_header = true;
	$row_count = 0;
	if ($ret->rowCount())
	{
		//print "Rows returned: " . $ret->rowCount();

		while($row = $ret->fetch())
		{
		    if($print_header){
       			// print into table
				print "<table border=1 width=600><tr>";
				/*for ($i = 0; $i < count($row); $i++)
				{
					$fieldname[$i] = $row[$i];
					print "<td><b>$k[name]</b></td>";
				}*/
				$i=0;
				foreach($row as $k=>$dummy){
					if(!is_numeric($k)){
						$fieldname[$i] = $k;
						print "<td><b>$k</b></td>";
						$i++;
					}
				}
				print "</tr>";
				$print_header = false;
			}
			print "<tr>";
			for ($i = 0; $i < $ret->columnCount(); $i++)
			{
				$v = $row[$fieldname[$i]];
				print "<td>$v</td>";
			}
			print "</tr>";
			$row_count++;
		}
		print "</table>";
		print "Rows returned: " . $row_count;
	}
	else
		print "Rows affected: " . $ret->rowCount();
}
?>
