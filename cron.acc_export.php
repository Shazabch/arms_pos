<?php
define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '512M');
set_time_limit(0);

// check if myself is running, exit if yes
if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
	@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps x\n";
}else{
	@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
	print "Checking other process using ps ax\n";
}

if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}



$con->sql_query("select * from ExportAccSchedule where active=1 and started=0");
$result=$con->sql_fetchrowset();
if($result){

    foreach($result as $s){
        //file_get_contents();
		echo passthru('php ./acc_export.generate.php '.$s['id'].' > /dev/null 2> /dev/null &');
		sleep(2);
		//pclose(popen('php ./acc_export.generate.php '.$s['id'],'r'));
	}
}
echo "Done\n";

?>
