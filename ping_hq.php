<?php
define('SKIP_BROWSER', 1);
include("config.php");

while(1)
{
	// ping host and write output into logfile
	//$fp = fopen("pinghq.log", "a");
	print date("[H:i:s m.d.y] ");
	$pingresult = join('', file("http://" . HQ_IP . "/ping_client.php?b=" . BRANCH_CODE."&p=".BRANCH_HTTP_PORT));
	print $pingresult . "\n";

	sleep(60);
}

/*
fwrite($fp, date("F j, Y, g:i a", time()) . " : Ping host returned '" .  strip_tags($pingresult) . "'\n");
fclose($fp);

print "<pre>$pingresult</pre>";
*/
?>
