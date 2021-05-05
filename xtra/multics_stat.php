<?php
	header("Content-type: text/plain");
	if (!file_exists("data.new"))
	    print "OK";
	else
	{
        $ft = time() - filemtime("data.new");
	    print intval($ft/60) >= 30 ? "Multics Synchronization has not been running for more than
		" .  intval($ft/60) . " minutes, Please inform MIS immediately" : "OK";
	}
?>
