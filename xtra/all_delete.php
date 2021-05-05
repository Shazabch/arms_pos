<?
set_time_limit(0);

if ($_REQUEST['fn']!='') push($_REQUEST['fn']);

print "<form method=post>
filename: <input name=fn value=$_REQUEST[fn]> <input type=submit name=ok value=OK><br /><br />";
foreach (split(",", "akadhq,akadbaling,akadgurun,akaddungun,akadkangar,akadjitra,akadtmerah,pktpt,hiwayhq,jwthq") as $b)
{
	print "<input id=_$b type=checkbox name=\"branches[$b]\" value=1 checked> <label for=_$b>$b</label> ";
}
print "<br />";
print "</form>";



function push($file)
{
	foreach(array_keys($_REQUEST['branches']) as $site)
	{

		$port = 4000;
		$updateport = 6112;
		$url= "http://$site.no-ip.org:$port";

		if ($site == 'pktpt')
		{
            $url= "http://$site.no-ip.info:$port";
		}

		// push file to site
		print "<span><img src='$url/updater.php?zap=$file&p=991234'>$site</span>&nbsp;";
	}
}
?>
