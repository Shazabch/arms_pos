<?
set_time_limit(0);

//load file from file.csv
if (file_exists('file.csv') && $_REQUEST['csv'])
{
	$fh = fopen('file.csv', "r");
	while (($r = fgetcsv($fh, 1000)) !== FALSE) {
	    $csvfile[$r[0]] = 1;
	}
	fclose($fh);
}

/*
print "<pre>";
print_r($data);
print "</pre>";
*/
// read from update.time if time param is not passed.
if ($_REQUEST['ok']!='LATEST' && isset($_REQUEST['time']))
	$ff[0] = $_REQUEST['time'];
else
	$ff = file("update.time");

if (is_int($ff[0]))
	$dd = $ff[0];
else
	$dd = strtotime($ff[0]);
if ($dd==0) $dd = strtotime("-1 day");


if ($_REQUEST['singlefile']!='' && file_exists($_SERVER['DOCUMENT_ROOT']."/".$_REQUEST['singlefile']))
{
	$f = $_REQUEST['singlefile'];
	print "<p>Updating single file $f</p>";
	push($f);
}

print "start from $ff[0] ($dd)<br />";

print "<hr noshade size=1>";
print "<form method=post onsubmit=\"return confirm('OK?');\">";

//pushdir("include/libs",$dd);
pushdir("price_checker",$dd);
pushdir("price_checker/templates",$dd);
pushdir("pricechecker",$dd);
pushdir("pricechecker/templates",$dd);
pushdir("shuttle_sg15",$dd);
//pushdir("../membership",$dd);
//pushdir("../membership/include",$dd);
pushdir(".",$dd);
pushdir("templates",$dd);
pushdir("pda",$dd);
pushdir("templates/gmark",$dd);
pushdir("templates/minotex",$dd);
pushdir("templates/ws",$dd);
pushdir("templates/smo",$dd);
pushdir("pda/templates",$dd);
pushdir("templates/aneka",$dd);
pushdir("templates/pkt",$dd);
pushdir("templates/wshqkb",$dd);
pushdir("templates/metrohouse",$dd);
pushdir("templates/cutemaree",$dd);

pushdir("xtra",$dd);
/*pushdir("pda/include",$dd);
pushdir("pda/ui",$dd);
pushdir("pda/ui/gif",$dd);
*/
//pushdir("cutemaree",$dd);
//pushdir("templates/cheque_formats",$dd);
//pushdir("ui",$dd);
pushdir("include",$dd);
pushdir("js",$dd);
pushdir("js/jscalendar",$dd);
//pushdir("images",$dd);

print "<hr noshade size=1>";

print "<input type=hidden name=singlefile><input type=submit name=ok value=OK> <input type=submit name=ok value=REFRESH> <input type=submit name=ok value=LATEST>";
print "<input name=csv type=checkbox ".($_REQUEST['csv']?"checked":"")."> Filter with file.csv";
print "<div id=cbs>";
print "<input type=checkbox onclick=check_all(this.checked)> <font color=blue>All</font>";
foreach (split(",", "akadhq,akadbaling,akadgurun,akaddungun,akadkangar,akadjitra,akadtmerah,cwmhq,pkthq,smarthq,wshq,gmarkhq,gmarktest,jwthq,cutemaree,pktent,upwell,agrofresh,growthmart,smodc,smohq,wshqkb,minotex,metrohouse") as $b)	//citymarthq,
{
	print "<input id=_$b type=checkbox name=\"branches[$b]\" value=1 ".($_REQUEST['branches'][$b]?"checked":"")."> <label for=_$b>$b</label> ";
}
print "</div>";
print "<br />time: <input name=time value=\"$ff[0]\">";
print "</form>";

// if OK was clicked, save the update.time
if ($_REQUEST['ok']=='OK')
{
	$ff = fopen("update.time","w"); fputs($ff,date("Y-m-d H:i:s", time()));fclose($ff);
	exit;
}

function pushdir($sdir,$dd)
{
	global $csvfile;
	$dr = $_SERVER['DOCUMENT_ROOT'];

	if ($dir = @opendir("$dr/$sdir")) {
	  while (($file = readdir($dir)) !== false) {
		//$file = strtolower($file);
		//print $file.'<br />';
		// skip PO
	 	//if(!preg_match("/^admin.stock_take/",$file)) continue;


	    if (!is_dir("$dr/$sdir/$file") && preg_match("/\.(sh|conf|htaccess|js|phpw*|css|tpl|gif|png|jpg|dll|glade)$/", $file) && $file != 'Thumbs.db' && $file != "config.php")
	    {
	        if (@filemtime("$dr/$sdir/$file")>=$dd)
	        {
	        	$checked = (!$_REQUEST['f'] || in_array("$sdir/$file",$_REQUEST['f'])) ? "checked" : "";
//filter file from file.csv
	 	if (is_array($csvfile))
		{
			if (!in_array("$sdir/$file",array_keys($csvfile)) && !in_array($file,array_keys($csvfile)))
   			{
				continue;
			}
			elseif ($_REQUEST['ok']!='OK')
			$checked = 'checked';

		}
	    		print "<li> <img border=0 src=/ui/icons/transmit.png onclick=\"document.forms[0].singlefile.value='$sdir/$file';document.forms[0].submit();\"> <input type=checkbox name=f[] $checked value=\"$sdir/$file\"> $sdir/$file " . @filemtime("$dr/$sdir/$file");
	    		if ($checked && $_REQUEST['ok']=='OK') push("$sdir/$file");
	    	}
		}
	  }
	  closedir($dir);
	}
}

function push($file)
{
	foreach(array_keys($_REQUEST['branches']) as $site)
	{
		$port = 4000;
		$updateport = 6112;
		$url= "http://$site.dyndns.org:$port";
		
		/*if ($site == 'jwthq')
		{
			$url= "http://jwt-hq.arms.com.my";
		}
		else*/

		if($site=='cwmhq'){
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
		else if ($site == 'gmarktest')
		{
			$url= "http://gmark-hq.arms.com.my:3000";
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
		else if ($site == 'smodc')
		{
			$url= "http://smo-hq.arms.com.my";
		}
		else if ($site == 'smohq')
		{
			$url= "http://smohq.arms.com.my";
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

		// mkt, shift record are for aneka only
		if (preg_match("/(aneka|mkt|shift_record)/",$file) && !preg_match("/^akad/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		elseif (preg_match("/(voucher)/",$file) && !preg_match("/^akad|pkt/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		elseif (preg_match("/\/consignment[_.]/",$file) && !preg_match("/^(cutemaree|metrohouse)/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		/*elseif (preg_match("/^(.*\/)*(do|do_approval\.)/",$file) && !preg_match("/^(cwm|gmark)/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}*/
		// mkt, shift record are for aneka only
		elseif (preg_match("/^(.*\/)*(pkt)/",$file) && !preg_match("/^pkt/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		elseif (preg_match("/^(.*\/)*(cwm)/",$file) && !preg_match("/^cwm/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		elseif (preg_match("/^(.*\/)*(gmark)/",$file) && !preg_match("/^gmark/",$site))
		{
		    // delete from site (show in red color)
			print "<span><img src='$url/updater.php?zap=$file&p=991234'> <font color=red>$site</font></span>&nbsp;";
		}
		else
		{
			// push file to site
			print "<span><img src='$url/updater.php?uf=$file&p=$updateport'> $site</span>&nbsp;";
		}
	}
}
?>
<script>
function check_all(val)
{
	 n = document.getElementById('cbs').getElementsByTagName('input');
	 for(i=0;i<n.length;i++)
	 {
	 	n[i].checked = val;
	 }
}
</script>
