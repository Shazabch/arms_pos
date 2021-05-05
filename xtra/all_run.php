<form>
Script to run: <input name=script> <input type=submit>
</form>
<?
if ($_REQUEST['script']!='')
{

	foreach (split(",", "akadhq,cwmhq,pkthq,smarthq,wshq,gmarkhq,jwthq,cutemaree,pktent,upwell,agrofresh,growthmart,smo") as $b)
	{
		run($b);
	}
/*	foreach (split(",", "akadhq,akadbaling,akadgurun,akaddungun,akadkangar,akadjitra,akadtmerah,hiwayhq,hiwaybs,hiwaybh,cwmhq,pkthq,smarthq,wshq,jwthq,agrofresh") as $b)
	{
		run($b);
	}*/
}

function run($site)
{
    $script = $_REQUEST['script'];
	$port = 4000;
	$url= "http://$site.dyndns.org:$port";
	
	if ($site == 'jwthq')
		{
			$url= "http://jwt-hq.arms.com.my";
		}
		else if($site=='cwmhq'){
			$url= "http://cwm-hq.arms.com.my:4000";
		}
		else if ($site == 'wshq')
		{
			$url= "http://ws-hq.arms.com.my:4000";
		}
		else if ($site == 'pkthq') {
			$url="http://hq.12shoppkt.com:4000";
		}
		else if ($site == 'smarthq')
		{
			$url= "http://smarthq.dyndns.org:4000";
		}
		else if ($site == 'akadhq')
		{
			$url= "http://hq.aneka.com.my:4000";
		}
		else if ($site == 'gmarkhq')
		{
			$url= "http://gmark-hq.arms.com.my:4000";
		}
		else if ($site == 'cutemaree')
		{
			$url= "http://cutemaree.dyndns.org:4000";
		}
		else if ($site == 'pktent')
		{
			$url= "http://pktent.no-ip.org:4000";
		}
		else if ($site == 'upwell')
		{
			$url= "http://upwell-hq.dyndns.org:4000";
		}
		else if ($site == 'agrofresh')
		{
			$url= "http://agrofresh-hq.dyndns.org";
		}
		else if ($site == 'growthmart')
		{
			$url= "http://growthmart.dyndns.org";
		}
		else if ($site == 'smo')
		{
			$url= "http://smo-hq.arms.com.my";
		}
		else if ($site == 'wshqkb')
		{
			$url="http://wshqkb.no-ip.org";
		}
		else if ($site == 'minotex')
		{
			$url="http://minotex-hq.dyndns.org";
		}

    print "<div style='float:left;'>$url/$script<br /><iframe style='width:300px;height:300px;' src='$url/$script'></iframe></div>";
}
?>
