<?php

$c = 0;
$out = "";
if (isset($_REQUEST['dept']))
{
	$str = strval($_REQUEST['dept']);
	do_search(file("MDEPT.dat"), $str);
}
elseif (isset($_REQUEST['sect']))
{
	$str = strval($_REQUEST['sect']);
	do_search(file("MSECT.dat"), $str);
}
elseif (isset($_REQUEST['cat']))
{
	$str = strval($_REQUEST['cat']);
	do_search(file("MCAT.dat"), $str);
}
elseif (isset($_REQUEST['brand']))
{
	$str = strval($_REQUEST['brand']);
	do_search(file("MBRAND.dat"), $str);
}
else
{
	print "<ul>";
	print "<li>Unhandled Request";
	print_r($_REQUEST);
	print "</li></ul>";

}

function do_search($lines, $str)
{
	print "<ul>";
	foreach ($lines as $line)
	{
	    $m = split(",", $line);
	    if (stristr($m[0],$str) == $m[0] || stristr($m[1],$str) == $m[1] || stristr($m[1]," ".$str))
	    {
	    	$out .= "<li title=\"$m[0]\">$m[0] - $m[1]</li>";
	    	$c++;
	    	if ($c>=10)
			{
			    print "<li><span class=informal><i>Showing first 10 matches</i></span></li>";
			    break;
			}
		}
	}
	if (!$c) print "<li title=\"\"><span class=informal>No Matches for $str</span></li>";
	print $out;
	print "</ul>";
}
?>
