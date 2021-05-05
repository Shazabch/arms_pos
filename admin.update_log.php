<?
/*
1/20/2016 3:02 PM Andy
- Remove the checking of membership folders.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$files = array();
pushdir("include/libs");
pushdir("price_checker");
pushdir("price_checker/templates");

//pushdir("../membership");
//pushdir("../membership/include");

pushdir(".");
pushdir("templates");
pushdir("templates/cheque_formats");
pushdir("ui");
pushdir("include");
pushdir("js");
pushdir("images");


$smarty->display("header.tpl");
arsort($files);
foreach ($files as $fn => $time) {
    if (date("Ymd",$time)!=$lastdate)
	{
		print "</ul><h2>";
		print date("Y-m-d",$time);
		print "</h2><ul>";
	} 
	echo "<li> <font color=#cccccc>".date("h:i:s A", $time)."</font> $fn <font color=blue>(".@filesize($fn)." bytes)</font>";
	$lastdate = date("Ymd",$time);
}
$smarty->display("footer.tpl");

function pushdir($sdir)
{
	global $files;
	$dr = $_SERVER['DOCUMENT_ROOT'];

	if ($dir = opendir("$dr/$sdir")) {
	  while (($file = readdir($dir)) !== false) {
	    if (!is_dir("$dr/$sdir/$file"))
	    {
	        $files["$dr/$sdir/$file"] = @filemtime("$dr/$sdir/$file");
		}
	  }
	  closedir($dir);
	}
}

?>
