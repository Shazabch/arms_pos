<?php
/*
9/3/2013 2:07 PM Andy
- Add touch file when upload.

2/19/2014 11:36 PM Andy
- commit and reupload

2/21/2018 5:32 PM Andy
- Enhanced to check svn_type and use port 6113 for php7.
*/
ini_set('display_errors', 1);

// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
         
//Flush (send) the output buffer and turn off output buffering
//ob_end_flush();
while (@ob_end_flush());
         
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
 
//prevent apache from buffering it for deflate/gzip
//header("Content-type: text/plain");
//header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
 
for($i = 0; $i < 1000; $i++)
{
echo ' ';
}
         
//ob_flush();flush();
?>

<script type="text/javascript">
function update_status(status, params){
	var ret = {'a':'upload_status', 'server_name': server_name, 'status': status};
	if(params)	ret['params'] = params;
	
	window.parent.postMessage(ret,'*');
}
</script>

<?php
//print_r($_SERVER);

//error_reporting(0);
//ob_flush();flush();

if(!$_REQUEST['server_name'])	die('No Server Identify');
$server_name = trim($_REQUEST['server_name']);

print "<small>Identify server: <b>$server_name</b></small>";

if(!$_REQUEST['tgz_filename'])	die('No file to upload');
$tgz_filename = trim($_REQUEST['tgz_filename']);

if(!$_REQUEST['file_info_list'])	die('No file list provided');
$file_info_list = $_REQUEST['file_info_list'];

if(!$_REQUEST['total_filesize'])	die('Cant get filesize info');
$total_filesize = intval($_REQUEST['total_filesize']);

$svn_type = isset($_REQUEST['svn_type']) ? trim($_REQUEST['svn_type']) : '';

print "<script>var server_name='$server_name';update_status('connected');</script>";
//ob_flush();flush();

print "<small>&nbsp;&nbsp;&nbsp;&nbsp;Identify file: <b>$tgz_filename</b></small><br />";

print "<small>SVN Type: <b>".($svn_type=='php7'?'ARMS_ENCODED_PHP7':'ARMS_Server')."</b></small><br />";

$HOST = $_SERVER["REMOTE_ADDR"];
$port = 6112;
if($svn_type == 'php7')	$port = 6113;
$err = array();

if(preg_match("/^10.1.1./", $HOST)){	// local testing mode
	$HOST = 'maximus';
}//else{	// online server
	$HOST .= ":$port";
//}
$file_path = 'ARMS_UPLOAD_FILE/'.$tgz_filename;
//$path = "http://$HOST/ARMS_UPLOAD_FILE/$tgz_filename";
$path = "http://$HOST/updater.php?f=$file_path";

print "<small style='color:blue;'>Copying File $path</small><br>";

//print_r($_SERVER);
print "<script>update_status('uploading');</script>";
print "<img src='ui/clock.gif' id='img_uploading' />";

//ob_flush();flush();

$temp_name = $tgz_filename;
$src = copy($path, $temp_name);
print "<script>document.getElementById('img_uploading').remove();</script>";

if(!file_exists($temp_name))	$err[] = "Copy failed, the file $temp_name not found.";
else{
	if(filesize($temp_name) != $total_filesize)	 $err[] = "Filesize different: copied ".intval(filesize($temp_name)).", Source $total_filesize.";
	
	if(!$err){
		exec("tar xzf $temp_name");
	}
	unlink($temp_name);
}

print "<br />";
if(!$err){
	foreach($file_info_list as $filename => $file_info){
		print "$filename: ";
		if(!file_exists($filename)){
			$err[] = "$filename Not Found.";
			//print "<font color='red'>Not Found</font><br />";
			continue;
		}
		
		if(filesize($filename) != $file_info['filesize']){
			$err[] = "$filesize Filesize not match.";
			//print "<font color='red'>Filesize not match</font><br />";
			continue;
		}
		
		chmod($filename,0777);
		touch($filename);
		print "<font color='blue'>OK</font> (".number_format(round(filesize($filename)/1024, 3), 3)." kb)<br />";
	}
}

if($err){
	print "<ul style='color:red;'>";
	foreach($err as $e){
		print "<li>$e</li>";
	}
	print "</ul>";
	print "<script>update_status('failed');</script>";
}else{
	print "<script>update_status('done');</script>";
}

//ob_flush();flush();
?>


