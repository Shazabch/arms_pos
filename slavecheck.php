<?php
// auto repair sql slave
// 12/12/2009 06:23:33 PM  yinsee
//
/*
11/18/2020 11:21 AM Andy
- Fixed do not change master if it is table crashed.
*/
print date('r')."\t";
$con = new PDO('mysql:host=localhost','root','web0x');
$r = $con->query('show slave status')->fetch();

if ($r['Slave_SQL_Running']=='Yes') { print "Slave running fine\n"; exit; } // no problem 
print_r($r);

if ($r['Last_Error'] || $r['Last_Errno'])	// got error
{
	$execpos = $r['Exec_Master_Log_Pos'];
	$execbin = $r['Relay_Master_Log_File'];
	//print_r($r);exit;
	//mail("yinsee@wsatp.com","Sync auto repair to $execbin, $execpos", var_export($r,true));

    $c = explode(".",$execbin);
	if ($execpos<=4 || $c[1]<=0) { print "Error in position, $execbin, $execpos\n";exit; }
	
	if(strpos($r['Last_Error'], "marked as crashed")>0){
		print "Table Crashed and auto repair failed.\n";
	}elseif ($execpos && $execbin)	// make sure both have value
	{	
		$con->exec("stop slave");
		$con->exec("change master to master_log_file='$execbin',master_log_pos=$execpos");
		$con->exec("start slave");
		print "1] Sync auto repair to $execbin, $execpos (change master to master_log_file='$execbin',master_log_pos=$execpos)\n";

	}
	else
	{
		print "Don't know how to fix\n";
	}
}
else if ($r['Slave_IO_Running']=='No')
{
	//master info structure	
	$execpos = $r['Exec_Master_Log_Pos'];
	$execbin = $r['Relay_Master_Log_File'];

//	mail("yinsee@wsatp.com","Sync auto repair to $execbin, $execpos", var_export($r,true));

    $c = explode(".",$execbin);
	if ($execpos<=4 || $c[1]<=0) { print "Error in position, $execbin, $execpos\n";exit; }

	if ($execpos && $execbin)	// make sure both have value
	{
		$con->exec("stop slave");
		//$con->exec("flush logs");
		$con->exec("reset slave");
		$con->exec("change master to master_log_file='$execbin',master_log_pos=$execpos");
		$con->exec("start slave");
print "2] Slave reset to $execbin, $execpos (change master to master_log_file='$execbin',master_log_pos=$execpos)\n";

	}
	else
	{
		print "Don't know how to fix\n";
	}
}
else
print "Don't know what error\n";
?>


