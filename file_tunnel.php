<?php
ini_set("memory_limit", "128M");
ini_set("display_errors",0);

if (preg_match('/http:\/\/([^:]+):(\d+)\//',$_REQUEST['f'],$match))
{
	$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	$try = @socket_connect($socket, $match[1], $match[2]);
	
	if (!$try || !$socket) // cannot connect to server
	{
		header("Content-type: text/html");
		print "$match[1] cannot be connected for the time being.";
		exit;
	}
	@socket_close($socket);
}

header('Content-type: image/jpeg');
readfile($_REQUEST['f']);
exit;
?>
