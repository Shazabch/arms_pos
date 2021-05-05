<?php
	ini_set('display_errors',0);
	error_reporting(0);
	
	if (strstr($_REQUEST['uf'],'config.php') || strstr($_REQUEST['f'],'config.php')) die("you can't load config.php");
	
	set_time_limit(0);
	// update single file?
	if (isset($_REQUEST['uf']))
	{
	    $f = $_REQUEST['uf'];
	    $f = trim($f);
	    $f = str_replace("\\", "/", $f);

		if (!is_dir(dirname($f))) mkdir(dirname($f),0777);
		$port = 6112;
		if (isset($_REQUEST['p'])) $port = intval($_REQUEST['p']);
	    $HOST = $_SERVER["REMOTE_ADDR"].":$port";
	    // write to temp and move over when done
	    $temp = tempnam('/tmp/',$f);
	    $src = @copy("http://$HOST/updater.php?f=$f",$temp);
	    if (file_exists($f) && !unlink($f)) { readfile("ui/cancel.png"); exit; }
	    rename($temp, $f);
	    chmod($f,0777);
	    header("Content-type: image/png");
		if ($src)
			readfile("ui/approved.png");
		else
		    readfile("ui/cancel.png");
	    exit;
	}
	 
	// user request for file
	if (isset($_REQUEST['f']))
	{
	    readfile($_REQUEST['f']);
	    exit;
	}
	
	if (isset($_REQUEST['zap']) && $_REQUEST['p']=='991234')
	{
	    header("Content-type: image/png");
	    if (@unlink($_REQUEST['zap']))
	        readfile("ui/approved.png");
		else
		    readfile("ui/cancel.png");
	    exit;
	}
?>
