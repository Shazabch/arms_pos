<?
include("include/common.php");
if ($sessioninfo['level']!=500 && $sessioninfo['level']<9999) header("Location: /");

set_time_limit(0);

pushdir(".");

print "<form><input type=submit name=ok value=OK> <input type=submit name=ok value=REFRESH>";
foreach (split(",", "hq,gurun,baling,dungun,kangar,jitra,tmerah") as $b)
{
	if(BRANCH_CODE != strtoupper($b))
	print "<input id=_$b type=checkbox name=\"branches[$b]\" value=1 checked> <label for=_$b>$b</label> ";
}
print "</form>";

function pushdir($sdir)
{
	$dr = $_SERVER['DOCUMENT_ROOT'];

	if ($dir = opendir("$dr/$sdir")) {
	  while (($file = readdir($dir)) !== false) {
	    if (!is_dir("$dr/$sdir/$file") && preg_match("/\.dat$/", $file))
	    {
    		print "<li> $sdir/$file ";
    		if ($_REQUEST['ok']=='OK') push("$sdir/$file");
		}
	  }
	  closedir($dir);
	}
}

function push($file)
{
	global $config;
	foreach(array_keys($_REQUEST['branches']) as $site)
	{
		$url = sprintf($config['no_ip_string'], strtolower($site));
		{
			// push file to site
			print "<span><img src='file_tunnel.php?f=".urlencode($url."updater.php?uf=$file&p=4000")."'> $site</span>&nbsp;";
		}
	}
}
?>
