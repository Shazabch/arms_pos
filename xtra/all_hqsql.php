<?
if ($_SERVER['SERVER_NAME'] != 'maximus')
{
	header("Location: /");
	exit;
}
?>
<h1>HQ SQL</h1>
<form method=post>
sql:<br>
<textarea name=query style="height:20%;width:100%"><?=htmlentities($_REQUEST['sql'])?></textarea><br>
<input type=submit>
</form>
<?
if (preg_match('/drop.*database/i',$_REQUEST['query'])) die("No DROP DATABASE allowed");
if ($_REQUEST['uid']=='585858' && $_REQUEST['query']!='')
{
	foreach (split(",", "akadhq,cwmhq,pkthq,smarthq,wshq,gmarkhq,jwthq,cutemaree,pktent,upwell,agrofresh,growthmart,smo,wshqkb,minotex,metrohouse") as $b)
	{
		run($b);
	}
}
function run($site)
{
    $script = "quiksql.php?uid=585858&sql=".urlencode($_REQUEST['query']);
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
	else if ($site == 'metrohouse')
	{
		$url="http://metrohouse-hq.dyndns.org:4000";
	}
		

    print "<div><h1>$site</h1>$url/$script<br /><iframe style='width:100%;height:300px;' src='$url/$script'></iframe></div>";
}
?>
