<?php
$f = fopen("e:/diff.txt","r");
//$o = fopen("g:/merge.txt","w");
$d = fopen("e:/diff_filter.txt","w");
$keep = '';
while ($l = fgets($f))
{
	$hd = substr($l,0,4);
	$l = substr($l,4);
//	fputs($o, $l);
	if ($hd == ' <! ')
	{
	    $f1++;
	    $keep = $l;
	}
	elseif ($hd == ' !> ')
	{
		$f2++;
	    if ($keep == '')
		{
			//fputs($o, $l);
			fputs($d, "BRANCH ".$l);
			fputs($d, "\n");
		}
		else
		{
		    /*$a = '';
		    while ($a=='')
			{
				print "Please select to use 1) or 2)\n";
				print "1) $keep";
				print "2) $l";
				print "\n";
				$a = fgets(STDIN);
			}
			if ($a == 1)
			{
				fputs($o, $keep);
			}
			else
			{
			    fputs($o, $l);
			}*/
			fputs($d, "GROUP  " . $keep);
		    fputs($d, "GROUP  " . $l);
		    fputs($d, "\n");
			$keep = '';
		}
	}
	elseif ($hd == '    ')
	{
	    if ($keep)
	    {
	    	fputs($d, "ARMS   " . $keep);
	    	fputs($d, "\n");
	    	$keep = '';
	    }
	//	fputs($o, $l);
	}
}
fclose($f);
//fclose($o);
fclose($d);

print "Total = $f1/$f2\n";
?>
