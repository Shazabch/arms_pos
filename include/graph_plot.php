<?
$fontwidth = array();
$fontheight = array();

function simplebargraph($data, $graphname, $xlabel, $xcol, $ylabel, $yvalue_format, $ycol, $filename, $colorscheme = false, $graphsetting = false, $print_data = false)
{
	if (!$colorscheme['bar_fill_'.$ycol])
		$colorscheme['bar_fill_'.$ycol] = '#0099ff';


	if($data){
		SimpleNBarGraph($data, $graphname, $xlabel, $xcol, array($ylabel), $yvalue_format, array($ycol), 0, $filename, $colorscheme, $graphsetting, $print_data);
	}
}

// draw a N-1 bar chart
function SimpleNBarGraph($data, $graphname, $xlabel, $xcol, $ylabels, $yvalue_format, $ycols, $legend_per_row, $filename, $colorscheme = false, $graphsetting = false, $print_data = false)
{

	global $fontwidth, $fontheight;

	$n = count($data); // count how many data we want to display
	$bars = count($ycols);

	$fontwidth[1] = 5;
	$fontwidth[2] = 6;
	$fontwidth[3] = 7;
	$fontwidth[4] = 8;
	$fontwidth[5] = 9;

	$fontheight[1] = 10;
	$fontheight[2] = 10;
	$fontheight[3] = 10;
	$fontheight[4] = 10;
	$fontheight[5] = 11;

	$yaxis_format = '';
	//$yaxis_format = $graphsetting['yaxis_format'] ? $graphsetting['yaxis_format'] : '';
	$color_callback = $graphsetting['color_callback'] ? $graphsetting['color_callback'] : false;
	$label_callback = $graphsetting['label_callback'] ? $graphsetting['label_callback'] : false;
	$canvas_width = $graphsetting['canvas_width'] ? $graphsetting['canvas_width'] : 960;
	$canvas_height = $graphsetting['canvas_height'] ? $graphsetting['canvas_height'] : 600;
	$graph_width = $graphsetting['graph_width'] ? $graphsetting['graph_width'] : 900;
	$graph_height = $graphsetting['graph_height'] ? $graphsetting['graph_height'] : 560;
	//$Y_LINES = $graphsetting['Y_LINES'] ? $graphsetting['Y_LINES'] : 10;
	$font_yvalue = $graphsetting['font_yvalue'] ? $graphsetting['font_yvalue'] : 1;
	$font_ylabel = $graphsetting['font_ylabel'] ? $graphsetting['font_ylabel'] : 5;
	$font_xvalue = $graphsetting['font_xvalue'] ? $graphsetting['font_xvalue'] : 2;
	$font_xlabel = $graphsetting['font_xlabel'] ? $graphsetting['font_xlabel'] : 5;
	$font_barvalue = $graphsetting['font_xlabel'] ? $graphsetting['font_xlabel'] : 2;
	$font_graphname = $graphsetting['font_graphname'] ? $graphsetting['font_graphname'] : 5;
	$font_legendlabel = $graphsetting['font_legendlabel'] ? $graphsetting['font_legendlabel'] : 3;
	$textup_yvalue = $graphsetting['textup_yvalue'] ? $graphsetting['textup_yvalue'] : 0;
	$textup_xvalue = $graphsetting['textup_xvalue']? $graphsetting['textup_xvalue'] : 0;
	$textup_barvalue = $graphsetting['textup_barvalue'] ? $graphsetting['textup_barvalue'] : 0;
	$show_legend = $graphsetting['show_legend'] ? 1 : ($bars > 1) ? 1 : 0;

	if(!$data){
		return;
	}
	// override default legend
	if ($graphsetting['legend'])
	{
		$show_legend = 2;
		$legend_per_row =  $graphsetting['legend_per_row'] ? $graphsetting['legend_per_row'] : 3;
	}

	if ($graphsetting['maxy'])
		$maxy = $graphsetting['maxy'];
	else
	{
		$maxy = -9999999999;

		foreach ($data as $row)
		{
			foreach ($ycols as $ycol)
			{
				if ($maxy < $row[$ycol]) $maxy = $row[$ycol];
			}
		}
		$maxy = $maxy * 1.05;	// add 5 % to top
	}

	if ($show_legend!=1) // show legend
	{
		$graph_width -= 30;
		$offset_x = ($canvas_width - $graph_width) / 2 + 25;
	}
	else
	{
		$offset_x = ($canvas_width - $graph_width) - 5;
	}
	$x_step = $graph_width / ($n*($bars+1)+1); // width of each bar

	// calculate $y_step, must be in duplicate of 1,2,5
	// Y_LINES must be > 5

	
	$Y_LINES = 1;
	$a = sprintf("%e", $maxy);
	preg_match("/(\d+.\d+)(e.*)/", $a, $matches);
	$round1 = $matches[1];

	if ($round1 / 0.5 > 5)
	{
		$y_step = 0.5 * doubleval("1$matches[2]");
	}
	elseif  ($round1 / 0.2 > 5)
	{
		$y_step = 0.2 * doubleval("1$matches[2]");
	}
	else
	{
		$y_step = 0.1 * doubleval("1$matches[2]");
	}
	$Y_LINES = ceil($maxy / $y_step);
	$maxy = $Y_LINES * $y_step;

	// prepare values
	$img = imagecreate($canvas_width,$canvas_height);
	imagefilledrectangle($img,0,0,$canvas_width,$canvas_height,imagecolorallocate($img, 255, 255, 255));

	if ($graphname != '')
	{
		$graph_height -= 60;
		$offset_y = 30; //($canvas_height - $graph_height) / 2;
	}
	else
	{
		$graph_height -= 30;
		$offset_y = 0; //($canvas_height - $graph_height) / 2;
	}
	//print "SL: $show_legend";
	//print "LR: $legend_per_row";
	//print "GH: $graph_height";
	//print "B: $bars";


	if ($legend_per_row > 0)
	{
		if ($show_legend == 1){
			$graph_height -= (30+(ceil($bars/$legend_per_row)*15));
		}elseif ($show_legend == 2){
			$graph_height -= (30+(ceil(count($graphsetting['legend'])/$legend_per_row)*15));
		}
	}

	//print "LR2: $legend_per_row";
	//print "GH2: $graph_height";
	//print "B2: $bars";

	if ($textup_xvalue) $graph_height -= 30;


	$graph_bottom = $offset_y + $graph_height;
	$graph_right = $offset_x + $graph_width;

	// colors
	$clr = array();
	make_colors($img, $clr);

	// override default colors
	if ($colorscheme)
	{
		foreach ($colorscheme as $name => $hexvalue)
		{

			$clr['bar_fill_'.$name] = rgb2color($img, $hexvalue);
			//imagecolorallocate($img, hexdec(substr($hexvalue,1,2)), hexdec(substr($hexvalue,3,2)), hexdec(substr($hexvalue,5,2)));
		}
	}


	$y = $graph_bottom;
	$y_val = 0;
	$dash_style = array ($clr['grey'], $clr['grey'], $clr['grey'], $clr['grey'], $clr['white'], $clr['white'], $clr['white'], $clr['white']);

	// draw horizontal lines and Y-labels
	for ($i=0; $i<=$Y_LINES; $i++)
	{
		if ($i > 0 && $i < $Y_LINES)
		{
			imagesetstyle ($img, $dash_style);
			imageline($img, $offset_x-2, $y, $graph_right+2, $y, IMG_COLOR_STYLED);
		}
		$val = number_format($y_val);
		/*if ($yaxis_format === '')
			$val = number_format($y_val);
		else
			$val = sprintf($yaxis_format, $y_val);
*/

if($maxy < 10)
	$val = sprintf('%.2f', $y_val);
else
	$val = $y_val;

		if (!$textup_yvalue)
		{
			$x = $offset_x - strlen($val)*$fontwidth[$font_yvalue] - 5;
			if ($x < 0) $x = 0;
			imagestring($img, $font_yvalue, $x, $y - 3, $val, $clr['label']);
		}
		else
		{
			imagestringup($img, $font_yvalue, $offset_x-15, $y + (strlen($val)*$fontwidth[$font_yvalue]/2), $val, $clr['label']);
		}
		if($graph_height && $Y_LINES){
			$y -= ($graph_height / $Y_LINES);
			$y_val += $y_step;
		}
	}

	$x = $x_step + $offset_x;
	// draw vertical bars and X-labels
	foreach ($data as $row)
	{
		// bottom label
		if (!$textup_xvalue)
		{
			//imagestring($img, $font_xvalue, $x + ($bars*$x_step - (strlen($row[$xcol])*$fontwidth[$font_xvalue]))/2, $graph_bottom + 10, $row[$xcol], $clr['label']);
			imagestring2($img, $font_xvalue, $x - $x_step/2, $x_step*($bars+1), $graph_bottom + 2, $label_callback ? $label_callback($row[$xcol]) : $row[$xcol], $clr['label']);
		}
		else
		{
			imagestringup2($img, $font_xvalue, $x - $x_step/2, $x_step*($bars+1), $graph_bottom + 2, 20, $label_callback ? $label_callback($row[$xcol]) : $row[$xcol], $clr['label']);
		}

		$n = 0;
		foreach ($ycols as $ycol)
		{
			$n = $n+1;
			// bottom tick
			imageline($img, $x, $graph_bottom+2, $x, $graph_bottom-2, $clr['black']);
			imageline($img, $x + $x_step, $graph_bottom+2, $x + $x_step, $graph_bottom-2, $clr['black']);

			if ($row[$ycol] > 0)
			{
				// bar
				$bar_height = $row[$ycol] / $maxy * $graph_height;
				imagefilledrectangle($img, $x, $graph_bottom - $bar_height, $x + $x_step, $graph_bottom, $color_callback ? rgb2color($img, $color_callback($row[$xcol])) : ($clr['bar_fill_'.$ycol] ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]));
				imagerectangle($img, $x, $graph_bottom - $bar_height, $x + $x_step, $graph_bottom, $clr['black']);

				// bar value (font-3 width is 7)

				if($yvalue_format == '')
					$yvalue_format='%d';

				if($row[$ycol] >= 1000)
					$yvalue_format='%d';
				elseif($row[$ycol] >= 100)
					$yvalue_format='%.1f';
				elseif($row[$ycol] >= 1)
					$yvalue_format='%.2f';
				elseif($row[$ycol] >= 0.01)
					$yvalue_format='%.3f';
				else
					$yvalue_format='%.5f';


				$val = sprintf($yvalue_format, $row[$ycol]); //coke


				if (!$textup_barvalue)
				{
					imagestring($img, $font_barvalue, $x + ($x_step - (strlen($val)*$fontwidth[$font_barvalue]))/2, $graph_bottom - $bar_height - 15, $val, $clr['bar_value']);
				}
				else
				{
					imagestringup($img, $font_barvalue, $x, $graph_bottom - $bar_height - 5, $val, $clr['bar_value']);
				}
			}
			$x += $x_step;
		}
		$x += $x_step;
	}

	// outer frame
	//imagerectangle($img,0,0,$canvas_width-1,$canvas_height-1,$clr['black']);
	imagerectangle($img,$offset_x,$offset_y,$graph_right,$graph_bottom,$clr['black']);

	// labels
	if ($show_legend!=1)
	{
		$ylabel = $ylabels[0];
		imagestringup($img, $font_ylabel, 10, $canvas_height - ($canvas_height - (strlen($ylabel)*$fontwidth[$font_ylabel])) / 2, $ylabel, $clr['black']);
	}

	if (!$textup_xvalue)
		imagestring($img, $font_xlabel, ($canvas_width - (strlen($xlabel)*$fontwidth[$font_xlabel])) / 2, $graph_bottom + 40, $xlabel, $clr['black']);
	else
		imagestring($img, $font_xlabel, ($canvas_width - (strlen($xlabel)*$fontwidth[$font_xlabel])) / 2, $graph_bottom + 60, $xlabel, $clr['black']);

	if ($graphname) imagestring($img, $font_graphname, ($canvas_width - (strlen($graphname)*$fontwidth[$font_graphname])) / 2, 10, $graphname, $clr['black']);

	// legend
	if ($legend_per_row > 0)
	{
		$legend_x = $offset_x+10;
		$legend_y = $graph_bottom + 80;
		$n = 0;

		if ($show_legend == 1)
		{
			foreach ($ycols as $ycol)
			{
				$n = $n+1;
				$ylabel = array_shift($ylabels);
				imagefilledrectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, $clr['bar_fill_'.$ycol] ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]);
				imagerectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, $clr['black']);
				imagestring($img, $font_legendlabel, $legend_x + 25, $legend_y, $ylabel, $clr['black']);

				$legend_x += intval($graph_width/$legend_per_row);
				if ($legend_x > $graph_width + $offset_x)
				{
					$legend_x = $offset_x+10;
					$legend_y += 15;
				}
			}
		}
		else
		{
			foreach ($graphsetting['legend'] as $ylabel => $color)
			{
				$n = $n+1;
				imagefilledrectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, rgb2color($img, $color));
				imagerectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, $clr['black']);
				imagestring($img, $font_legendlabel, $legend_x + 25, $legend_y, $ylabel, $clr['black']);

				$legend_x += intval($graph_width/$legend_per_row);
				if ($legend_x > $graph_width + $offset_x)
				{
					$legend_x = $offset_x+10;
					$legend_y += 15;
				}
			}
		}

		// legend box
		imagerectangle($img,$offset_x,$graph_bottom+70,$graph_right,$canvas_height-10,$clr['black']);
	}

	imagegif($img, $filename);
	print "<img src=$filename>";

	if ($print_data)
	{
		print "<table border=1>";
		print "<tr><td>&nbsp;</td>";
		foreach ($ycols as $ycol)
		{
			print "<td><b>$ycol</b></td>";
		}
		print "</tr>";
		foreach ($data as $row)
		{
			print "<tr><td><b>$row[$xcol]</b></td>";
			foreach ($ycols as $ycol)
			{
				print "<td>$row[$ycol]</td>";
			}
			print "</tr>";
		}
		print "</table>";
		print "<hr>";
	}
}

function imagestring2($img, $size, $x, $w, $y, $str, $color)
{

	global $fontwidth, $fontheight;
	//imagerectangle($img, $x, $y, $x+$w, $y+30, $color);

	$words = preg_split("/[\s\n]+/", $str);
	array_push($words, "*******");

	$lastword = "";
	foreach ($words as $word)
	{
		$oldw = $lastword;
		if ($lastword != '') $lastword .= " ";
		$lastword .= $word;

		$curr_w = strlen($lastword)*$fontwidth[$size];

		if ($oldw != "" && ($curr_w > $w || $word == "*******"))
		{
			$xc = $x + ($w - (strlen($oldw)*$fontwidth[$size])) / 2;
			imagestring($img, $size, $xc, $y, $oldw, $color);
			$y += $fontheight[$size];
			$lastword = $word;
		}

	}
}

// draw a Mountain and Stacked and Line bar combo chart
/*
	$ylabels = array(
		field1 => "Mountain", field2 => "Mountain", field1 => "Stacked", field2 => "Line" ...
	);
*/
function ComplexNGraph($data, $graphname, $xlabel, $xcol, $ylabels, $yvalue_format, $ycols, $legend_per_row, $filename, $colorscheme = false, $graphsetting = false, $print_data = false)
{
	global $fontwidth, $fontheight;

	$fontwidth[1] = 5;
	$fontwidth[2] = 6;
	$fontwidth[3] = 7;
	$fontwidth[4] = 8;
	$fontwidth[5] = 9;

	$fontheight[1] = 10;
	$fontheight[2] = 10;
	$fontheight[3] = 10;
	$fontheight[4] = 10;
	$fontheight[5] = 11;

	$canvas_width = $graphsetting['canvas_width'] ? $graphsetting['canvas_width'] : 960;
	$canvas_height = $graphsetting['canvas_height'] ? $graphsetting['canvas_height'] : 600;
	$graph_width = $graphsetting['graph_width'] ? $graphsetting['graph_width'] : $canvas_width-40;
	$graph_height = $graphsetting['graph_height'] ? $graphsetting['graph_height'] : $canvas_height-40;
	$Y_LINES = !empty($graphsetting['Y_LINES']) ? $graphsetting['Y_LINES'] : 10;
	$font_yvalue = !empty($graphsetting['font_yvalue']) ? $graphsetting['font_yvalue'] : 1;
	$font_ylabel = !empty($graphsetting['font_ylabel']) ? $graphsetting['font_ylabel'] : 5;
	$font_xvalue = !empty($graphsetting['font_xvalue']) ? $graphsetting['font_xvalue'] : 2;
	$font_xlabel = !empty($graphsetting['font_xlabel']) ? $graphsetting['font_xlabel'] : 5;
	$font_barvalue = !empty($graphsetting['font_xlabel']) ? $graphsetting['font_xlabel'] : 2;
	$font_graphname = !empty($graphsetting['font_graphname']) ? $graphsetting['font_graphname'] : 5;
	$font_legendlabel = !empty($graphsetting['font_legendlabel']) ? $graphsetting['font_legendlabel'] : 3;
	$textup_yvalue = !empty($graphsetting['textup_yvalue']) ? $graphsetting['textup_yvalue'] : 0;
	$textup_xvalue = !empty($graphsetting['textup_xvalue']) ? $graphsetting['textup_xvalue'] : 0;
	$textup_barvalue = !empty($graphsetting['textup_barvalue']) ? $graphsetting['textup_barvalue'] : 0;
	//$yaxis_format = $graphsetting['yaxis_format'] ? $graphsetting['yaxis_format'] : '';

	$ylabel_name = array_keys($ylabels);
	$ylabel_type = array_values($ylabels);

	if ($graphsetting['maxy'])
		$maxy = $graphsetting['maxy'];
	else
	{
		$maxy = -9999999999;
		foreach ($data as $row)
		{
			$n = 0;
			$stackedsize = 0;
			foreach ($ycols as $ycol)
			{
				if ($ylabel_type[$n] == 'Stacked')
				{
					$stackedsize += $row[$ycol];
				}
				$n=$n+1;
			}

			foreach ($ycols as $ycol)
			{
				if ($maxy < $row[$ycol]) $maxy = $row[$ycol];
			}
			if ($maxy < $stackedsize) $maxy = $stackedsize;
		}
		$maxy = $maxy * 1.05;	// add 5 % to top
	}

	$n = count($data)+1; // count how many data we want to display
	$bars = count($ycols);

	if ($bars > 1) // show legend
		$offset_x = ($canvas_width - $graph_width) - 5;
	else
	{
		$graph_width -= 30;
		$offset_x = ($canvas_width - $graph_width) / 2 + 25;
	}
	$x_step = $graph_width / $n; // / ($n*($bars+1)+1); // width of each bar


    if (empty($graphsetting['Y_LINES']))
    {
		$Y_LINES = 0;
		$a = sprintf("%e", $maxy);
		preg_match("/(\d+.\d+)(e.*)/", $a, $matches);
		$round1 = $matches[1];

		if ($round1 / 0.5 > 5)
		{
			$y_step = 0.5 * doubleval("1$matches[2]");
		}
		elseif  ($round1 / 0.2 > 5)
		{
			$y_step = 0.2 * doubleval("1$matches[2]");
		}
		else
		{
			$y_step = 0.1 * doubleval("1$matches[2]");
		}
		$Y_LINES = ceil($maxy / $y_step);
		$maxy = $Y_LINES * $y_step;
    }
    else
    {
        $y_step = $maxy / $Y_LINES;
	}
	// prepare values
	$img = imagecreate($canvas_width,$canvas_height);
	imagefilledrectangle($img,0,0,$canvas_width,$canvas_height,imagecolorallocate($img, 255, 255, 255));

	if ($graphname != '')
	{
		$graph_height -= 50;
		$offset_y = ($canvas_height - $graph_height) / 2;
	}
	else
	{
	    $offset_y = 5;
		$graph_height -= 10;
	}

	if ($legend_per_row > 0)
	{
		$graph_height -= (30+(ceil($bars/$legend_per_row)*15));
	}
	if ($textup_xvalue) $graph_height -= 30;

	$graph_bottom = $offset_y + $graph_height;
	$graph_right = $offset_x + $graph_width;

	// colors
	$clr = array();
	make_colors($img, $clr);

	// override default colors
	if ($colorscheme)
	{
		foreach ($colorscheme as $name => $hexvalue)
		{
		    if (is_Array($hexvalue))
			    $clr['bar_fill_'.$name] = imagecolorallocate($img, $hexvalue[0], $hexvalue[1], $hexvalue[2]);
			else
				$clr['bar_fill_'.$name] = imagecolorallocate($img, hexdec(substr($hexvalue,1,2)), hexdec(substr($hexvalue,3,2)), hexdec(substr($hexvalue,5,2)));
		}
	}

	$y = $graph_bottom;
	$y_val = 0;
	$dash_style = array ($clr['grey'], $clr['grey'], $clr['grey'], $clr['grey'], $clr['white'], $clr['white'], $clr['white'], $clr['white']);

	// draw horizontal lines and Y-labels
	for ($i=0; $i<=$Y_LINES; $i++)
	{
		if ($i > 0 && $i < $Y_LINES)
		{
			imagesetstyle ($img, $dash_style);
			imageline($img, $offset_x-2, $y, $graph_right+2, $y, IMG_COLOR_STYLED);
		}
		$val = number_format($y_val);
		/*if ($yaxis_format === '')
			$val = number_format($y_val);
		else
			$val = sprintf($yaxis_format, $y_val);
		*/
		if (!$textup_yvalue)
		{
			$x = $offset_x - strlen($val)*$fontwidth[$font_yvalue] - 5;
			if ($x < 0) $x = 0;
			imagestring($img, $font_yvalue, $x, $y - 3, $val, $clr['label']);
		}
		else
		{
			imagestringup($img, $font_yvalue, $offset_x-15, $y + (strlen($val)*$fontwidth[$font_yvalue]/2), $val, $clr['label']);
		}

		$y -= ($graph_height / $Y_LINES);
		$y_val += $y_step;
	}

	$x = $offset_x + $x_step;
	// draw vertical bars and X-labels
	foreach ($data as $row)
	{
		// bottom label
		if (!$textup_xvalue)
		{
			//imagestring($img, $font_xvalue, $x + ($bars*$x_step - (strlen($row[$xcol])*$fontwidth[$font_xvalue]))/2, $graph_bottom + 10, $row[$xcol], $clr['label']);
			imagestring2($img, $font_xvalue, $x - $x_step/2, $x_step , $graph_bottom + 2, $row[$xcol], $clr['label']);
		}
		else
		{
			imagestringup2($img, $font_xvalue, $x - $x_step/2, $x_step, $graph_bottom + 2, 20, $row[$xcol], $clr['label']);
		}
		// bottom tick
		imageline($img, $x, $graph_bottom+2, $x, $graph_bottom-2, $clr['black']);
//		imageline($img, $x + $x_step, $graph_bottom+2, $x + $x_step, $graph_bottom-2, $clr['black']);

		$x += $x_step;
	}

	$n = 0;
	// set all heights to zero
	$bar_heights = array();
	$line_heights = array();
	foreach ($ycols as $ycol)
	{
		$n = $n+1;
		$x = $offset_x;
		$y = 0;
		if ($ylabel_type[$n-1] == 'Mountain')
		{
			// draw vertical bars and X-labels
			$vertices = array($x, $graph_bottom);	// start point
			foreach ($data as $row)
			{
				$bar_height = $row[$ycol] / $maxy * $graph_height;
				$x += $x_step;
				array_push($vertices, $x, $graph_bottom-$bar_height);
			}
			array_push($vertices, $graph_right, $graph_bottom); // end point

			imagefilledpolygon($img, $vertices, count($data)+2, !empty($clr['bar_fill_'.$ycol]) ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]);
			imagepolygon($img, $vertices, count($data)+2, $clr['black']);
		}
		elseif ($ylabel_type[$n-1] == 'Stacked')
		{
			$idx = 0;
			foreach ($data as $row)
			{
				$bar_height = $row[$ycol] / $maxy * $graph_height;
				if (empty($bar_heights[$idx])) $bar_heights[$idx] = 0;

				$x += $x_step;

				if ($bar_height>0)
				{
					imagefilledrectangle($img, $x-$x_step/4, $graph_bottom - $bar_heights[$idx] - $bar_height, $x + $x_step/4, $graph_bottom - $bar_heights[$idx], !empty($clr['bar_fill_'.$ycol]) ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]);
					imagerectangle($img, $x-$x_step/4, $graph_bottom - $bar_heights[$idx] - $bar_height, $x + $x_step/4, $graph_bottom - $bar_heights[$idx], $clr['black']);
					imagestring($img, $font_yvalue, $x-$x_step/4+5, $graph_bottom - $bar_heights[$idx] - $bar_height + 5, sprintf($yvalue_format,$row[$ycol]), $clr['black']);
	    			$bar_heights[$idx] += $bar_height;
				}
				$idx++;
			}
		}
		elseif ($ylabel_type[$n-1] == 'Line')
		{
			$idx = 0;
			foreach ($data as $row)
			{
				$bar_height = $row[$ycol] / $maxy * $graph_height;
				$line_heights[$ycol][$idx] = $bar_height;

				$x += $x_step;
				if ($idx > 0)
				{
					$prevh = $line_heights[$ycol][$idx-1];
					imagesetthickness($img, 5);
					imageline($img, $x - $x_step, $graph_bottom - $prevh, $x, $graph_bottom - $bar_height, !empty($clr['bar_fill_'.$ycol]) ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]);
					imagesetthickness($img, 1);
				}
				$idx++;
			}
		}
	}

	// outer frame
	//imagerectangle($img,0,0,$canvas_width-1,$canvas_height-1,$clr['black']);
	imagerectangle($img,$offset_x,$offset_y,$graph_right,$graph_bottom,$clr['black']);

	if (!$textup_xvalue)
		imagestring($img, $font_xlabel, ($canvas_width - (strlen($xlabel)*$fontwidth[$font_xlabel])) / 2, $graph_bottom + 30, $xlabel, $clr['black']);
	else
		imagestring($img, $font_xlabel, ($canvas_width - (strlen($xlabel)*$fontwidth[$font_xlabel])) / 2, $graph_bottom + 60, $xlabel, $clr['black']);

	imagestring($img, $font_graphname, ($canvas_width - (strlen($graphname)*$fontwidth[$font_graphname])) / 2, 20, $graphname, $clr['black']);

	// legend
	if ($legend_per_row > 0)
	{
		$legend_x = $offset_x+10;
		$legend_y = $graph_bottom + 60;
		$n = 0;
		foreach ($ycols as $ycol)
		{
			$n++;
			$ylabel = $ylabel_name[$n-1];
			imagefilledrectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, !empty($clr['bar_fill_'.$ycol]) ? $clr['bar_fill_'.$ycol] : $clr['bar_fill_'.$n]);
			imagerectangle($img, $legend_x, $legend_y, $legend_x + 20, $legend_y + 12, $clr['black']);
			imagestring($img, $font_legendlabel, $legend_x + 25, $legend_y, $ylabel, $clr['black']);

			$legend_x += intval($graph_width/$legend_per_row);
			if ($legend_x > $graph_width + $offset_x)
			{
				$legend_x = $offset_x+10;
				$legend_y += 15;
			}
		}

		// legend box
		imagerectangle($img,$offset_x,$graph_bottom+50,$graph_right,$canvas_height-10,$clr['black']);
	}

	imagegif($img, $filename);
	if ($graphsetting['write_html']) {
		$random_count = rand(0000,9999);
		print "<img src=$filename?$random_count>";
	}

	if ($print_data)
	{
		print "<table border=1>";
		print "<tr><td>&nbsp;</td>";
		foreach ($ycols as $ycol)
		{
			print "<td><b>$ycol</b></td>";
		}
		print "</tr>";
		foreach ($data as $row)
		{
			print "<tr><td><b>$row[$xcol]</b></td>";
			foreach ($ycols as $ycol)
			{
				print "<td>$row[$ycol]</td>";
			}
			print "</tr>";
		}
		print "</table>";
		print "<hr>";
	}
}


function imagestringup2($img, $size, $x, $w, $y, $h, $str, $color)
{
	global $fontwidth, $fontheight;
	//imagerectangle($img, $x, $y, $x+$w, $y+30, $color);

	$words = preg_split("/[\s\n]+/", $str);
	array_push($words, "*******");

// estimate rows
	$rowcount = 0;
	$lastword = "";
	foreach ($words as $word)
	{
		$oldw = $lastword;
		if ($lastword != '') $lastword .= " ";
		$lastword .= $word;

		$curr_h = strlen($lastword)*$fontwidth[$size];

		if ($oldw != "" && ($curr_h > $h || $word == "*******"))
		{
			$rowcount++;
		}
	}

// real stuff
	$x = $x + ($w - $rowcount*$fontheight[$size])/2;
	$lastword = "";
	foreach ($words as $word)
	{
		$oldw = $lastword;
		if ($lastword != '') $lastword .= " ";
		$lastword .= $word;

		$curr_h = strlen($lastword)*$fontwidth[$size];

		if ($oldw != "" && ($curr_h > $h || $word == "*******"))
		{
			$yc = $y + (strlen($oldw)*$fontwidth[$size]);
			imagestringup($img, $size, $x, $yc, $oldw, $color);
			$x += $fontheight[$size];
			$lastword = $word;
		}

	}
/*
	$yc = $y + (strlen($str)*$fontwidth[$size]);
	imagestringup($img, $size, $x, $yc, $str, $color);
*/
}

function rgb2color($img, $hexvalue)
{
	 return imagecolorallocate($img, hexdec(substr($hexvalue,1,2)), hexdec(substr($hexvalue,3,2)), hexdec(substr($hexvalue,5,2)));
}


function make_colors($img, &$clr)
{
	$clr['bar_fill_1'] = rgb2color($img, "#00FF00");
	$clr['bar_fill_2'] = rgb2color($img, "#FF0000");
	$clr['bar_fill_3'] = rgb2color($img, "#3366FF");
	$clr['bar_fill_4'] = rgb2color($img, "#FFFF00");
	$clr['bar_fill_5'] = rgb2color($img, "#FF00FF");
	$clr['bar_fill_6'] = rgb2color($img, "#C0C0C0");
	$clr['bar_fill_7'] = rgb2color($img, "#FF6600");
	$clr['bar_fill_8'] = rgb2color($img, "#000080");
	$clr['bar_fill_9'] = rgb2color($img, "#CCCC66");
	$clr['bar_fill_10'] = rgb2color($img, "#FFFF40");

	$clr['label'] = imagecolorallocate($img, 0, 0, 200);
	$clr['bar_value'] = imagecolorallocate($img, 200, 0, 0);
	$clr['white'] = imagecolorallocate($img, 255, 255, 255);
	$clr['black'] = imagecolorallocate($img, 0, 0, 0);
	$clr['grey'] = imagecolorallocate($img, 100, 100, 100);
	$clr['blue'] = imagecolorallocate($img, 0, 150, 200);
}
?>
