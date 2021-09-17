<?php
/*
REVISION HISTORY
+++++++++++++++++
9/27/2007 12:15:18 PM gary
- added function get_last_grn_cost to get SKU cost price.

11/21/2007 6:19:36 PM yinsee
- added function smarty_get_selling_cost_balance to get latest cost and selling

1/11/2008 11:27:58 AM yinsee
- add function smarty_function_array_sum_by_key to calculate the total for each array column.

2008-10-7 1:21:00 PM Andy
- add darrow function to show arrow up or down at sorttable header

2008-10-28 3:06:00 PM Andy
- add capture id and value into smarty function dropdown

4/26/2010 4:37:54 PM Andy
- A little bit reduce memory usage for function smarty_get_selling_cost_balance()

7/8/2010 5:09:05 PM Andy
- Add new smarty modifier "num_format", it is exactly same to number_format but will not show decimal point if number given is integer.

8/5/2010 6:33:42 PM Andy
- Change checking from "===" to "==" in most of the smarty function.

9/14/2010 2:39:46 PM Andy
- Add smarty_input.

10/18/2010 6:47:52 PM Alex
- add cost checking at smarty_get_selling_cost_balance() to trigger display a red star

1/18/2011 4:09:11 PM Yin See
- add $_SERVER['DOCUMENT_ROOT']

2/21/2011 3:35:40 PM Andy
- Add smarty function var_export.

6/15/2011 5:02:40 PM Andy
- Add smarty function smarty_array_assign

10/11/2011 4:49:23 PM Alex
- add smarty function smarty_decimal_qty

10/25/2011 11:39:43 AM Justin
- Added new smarty function smarty_rounding

10/25/2011 2:06:23 PM Andy
- Fix "smarty_get_selling_cost_balance" to get master cost as grn cost if found no grn cost.

11/22/2011 5:50:33 PM Andy
- Add new smarty modified "str_replace".

3/1/2012 4:19:34 PM Andy
- Fix smarty array_assign. 

4/2/2013 11:59 AM Justin
- Fixed the rounding error.

9/30/2013 3:14 PM Andy
- Add new smarty function smarty_load_and_show_gpm_broadcast_message().

3/7/2014 5:35 PM Justin
- Added new function smarty_receipt_no_prefix_format.

8/21/2015 2:38 PM Andy
- Added new smarty function getSKUItems.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

8/2/2017 4:19 PM Andy
- Change smarty_get_selling_cost_balance() to use sql_fetchassoc()

2/1/2018 10:55 AM Justin
- Added new function smarty_weight_nf.

3/21/2019 3:11 PM Andy
- Added new smarty function "smarty_show_sku_photo".

11/11/2019 5:22 PM Andy
- Added new smarty function "smarty_show_duration".
*/

include("libs/Smarty.class.php");
$smarty = new Smarty;
$smarty->template_dir = $_SERVER['DOCUMENT_ROOT'].'/templates';
$smarty->compile_dir = $_SERVER['DOCUMENT_ROOT'].'/templates_c';
$smarty->config_dir = $_SERVER['DOCUMENT_ROOT'].'/templates';
$smarty->debug=false;
              
// pagination hack for firefox 2.0.0.15 bug! (ARGH!!!!!@#*(!@_#*!@(#*) 
$smarty->register_prefilter("printarea_hack"); 
function printarea_hack($tpl_source, &$smarty) 
{ 
    return str_replace("<div class=printarea>","<div class=printarea><div style=\"height:1px;line-height:1px;\">&nbsp;</div>",$tpl_source); 
} 


// my custom smarty functions

$smarty->register_function("get_category_tree", "smarty_function_get_category_tree");
function smarty_function_get_category_tree($params)
{
	return get_category_tree($params['id'], $params['tree_str'], $dummy);
}

// my custom smarty functions

$smarty->register_modifier("date_add", "smarty_date_add");
function smarty_date_add($string,$add)
{
	if (!is_numeric($string)) $string=strtotime($string);
	return strtotime($add,$string);
}



$smarty->register_function("get_selling_price", "smarty_get_selling_price");
function smarty_get_selling_price($params, &$smarty)
{
	global $con;
	$id = intval($params['id']);
	$bid = intval($params['branch_id']);
	$con->sql_query("select if (p.price is null, sku_items.selling_price, p.price) as selling from sku_items left join sku_items_price p on p.sku_item_id = sku_items.id and p.branch_id = $bid where sku_items.id = $id");
	$r = $con->sql_fetchrow();
	if ($params['assign']) 
		$smarty->assign($params['assign'], $r['selling']); 
	else
		return number_format($r['selling'],2);
}

// return grn cost, avg cost, balance etc
$smarty->register_function("get_selling_cost_balance", "smarty_get_selling_cost_balance");
function smarty_get_selling_cost_balance($params, &$smarty)
{
	global $con;
	if (!$params['assign']) die("Error: no 'assign' in get_cost_balance");
	 
	$id = intval($params['id']);
	$bid = intval($params['branch_id']);
	$con->sql_query("select if (p.price is null, sku_items.selling_price, p.price) as selling, if(p.price is null,sku.default_trade_discount_code,p.trade_discount_code) as discount_code,c.*,if(c.grn_cost is null,0 ,1) as got_cost,
	sku_items.packing_uom_id,sku_items.ctn_1_uom_id,sku_items.ctn_2_uom_id,uom1.fraction as packing_uom_fraction,uom2.fraction as ctn_1_fraction,uom3.fraction as ctn_2_fraction,
	uom2.code as ctn_1_code,uom3.code as ctn_2_code,ifnull(c.grn_cost,sku_items.cost_price) as grn_cost
	 from sku_items
	 left join sku_items_cost c on c.sku_item_id = sku_items.id and c.branch_id = $bid
	 left join sku_items_price p on p.sku_item_id = sku_items.id and p.branch_id = $bid
	 left join sku on sku.id=sku_items.sku_id
	 left join uom uom1 on uom1.id=sku_items.packing_uom_id
	left join uom uom2 on uom2.id=sku_items.ctn_1_uom_id
	left join uom uom3 on uom3.id=sku_items.ctn_2_uom_id
	 where sku_items.id = $id");
	$r = $con->sql_fetchassoc();
	$con->sql_freeresult();
	$smarty->assign($params['assign'], $r); 
	unset($r);
}

$smarty->register_function("get_last_grn_cost", "smarty_get_last_grn_cost");
function smarty_get_last_grn_cost($params, &$smarty)
{
	global $con;
	$id = intval($params['id']);
	$bid = intval($params['branch_id']);
/*
	$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3)
	from grn_items
	left join uom on uom_id = uom.id
	left join sku_items on sku_item_id = sku_items.id
	left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
	left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
	where
	grn_items.branch_id=$bid and grn.approved
	and sku_items.id=$id order by grr.rcv_date desc limit 1");
	$r = $con->sql_fetchrow();
	print '['.$r[0].']';
	*/
	$con->sql_query("select cost_price from vendor_sku_history where source='GRN' and sku_item_id=$id and branch_id=$bid order by added desc, ref_id desc limit 1");	
	$r = $con->sql_fetchrow();
	
	if ($params['assign']) 
		$smarty->assign($params['assign'], $r[0]); 
	else
		return number_format($r[0],3);
}

$smarty->register_function("count", "smarty_function_count");
function smarty_function_count($params, &$smarty)
{
	$count = count($params['var']);
	if (isset($params['multi']))
	{
	    $count *= intval($params['multi']);
	}
	if (isset($params['offset']))
	{
	    $count += intval($params['offset']);
	}
	
	if(isset($params['assign'])){
		$smarty->assign($params['assign'], $count); 
	}else{
		return $count;
	}
}

$smarty->register_block("repeat", "smarty_block_repeat");
function smarty_block_repeat($params, $content)
{
	$out = '';
	if (isset($params['s']))
	{
	    for ($i=$params['s'];$i<=$params['e']; $i++)
	        $out .= $content;
    }
	else
	    for ($i=0;$i<$params['n']; $i++)
    	    $out .= $content;

    return $out;
}

$smarty->register_modifier("ifzero", "smarty_modifier_ifzero");
function smarty_modifier_ifzero($string, $replace = "&nbsp;",$append='')
{
	if ($string == 0)
		return $replace;
	else
	    return $string.$append;
}

$smarty->register_modifier("ifempty", "smarty_modifier_ifempty");
function smarty_modifier_ifempty($string, $replace = "&nbsp;")
{
	if (trim($string) === '')
		return $replace;
	else
	    return $string;
}

$smarty->register_function("array_find_key", "smarty_modifier_findkey");
function smarty_modifier_findkey($params)
{
	foreach ($params['array'] as $r)
	{
	    if ($r[$params['key']] == $params['find'])
	        return $r[$params['return']];
	}
	return $params['default'];
}

$smarty->register_modifier("substr", "substr");

$smarty->register_function("array_sum_by_key", "smarty_function_array_sum_by_key");
function smarty_function_array_sum_by_key($params){
	global $smarty;
	$t = 0;
	$k = "['".preg_replace("/\s*,\s*/","']['",$params['keys'])."']";
	
	if (is_array($params['array']))
	{ 
		foreach ($params['array'] as $r){
			eval("\$t += \$r$k;");
		}
	}
	if (isset($params['assign']))
		$smarty->assign($params['assign'],$t);
	else
		return $t;
}

$smarty->register_modifier("round2", "smarty_round2");
function smarty_round2($str)
{
	return sprintf("%.2f",round(mf($str),2));
}

$smarty->register_modifier("round3", "smarty_round3");
function smarty_round3($str)
{
	return sprintf("%.3f",round(mf($str),3));
}


$smarty->register_function("dropdown", "smarty_dropdown");
function smarty_dropdown($params, &$smarty)
{
	if (!isset($params['selected']) && isset($params['name']))
	{
		$params['selected'] = $_REQUEST[$params['name']];
	}
	
	if (isset($params['key']))
	{
		if (!isset($params['value'])) die("Smarty Dropdown Error: You must provide Key and Value");
	}
	

	print "<select name=\"$params[name]\" class=\"form-control select2\" ";
	foreach ($params as $p=>$v)
	{
		// append class, style, onchange, onclick, onXXX
		if (preg_match("/^(on|title|class|style|id)/", $p)) print " $p=\"$v\" ";
	}
	print ">\n";
	if (isset($params['all']))
	{
		print "<option value=\"\">$params[all]</option>\n";
	}
	if (isset($params['values']))
	{
		if (file_exists($params['values']))
		{
			//print "using file $params[values]";
			foreach(file($params['values']) as $l)
				$values[] = trim($l);	
			$params['values'] = $values;
		}
		elseif (!is_array($params['values']))
		{
		    $params['values'] = preg_split("/\|/", $params['values']);
		}
		if (isset($params['key']))
		{
			foreach($params['values'] as $r)
			{
				$k = $r[$params['key']];
				$v = $r[$params['value']];
				if (isset($params['exclude']) && ($params['exclude'] == $k || preg_match($params['exclude'],$k))) continue;
				$selected = ($params['selected'] == $k) ? "selected" : "";
				print "<option value=\"$k\" $selected>$v</option>\n";
			}
		
		}
		elseif ($params['is_assoc'])
		{
			foreach($params['values'] as $k=>$v)
			{
				if (isset($params['exclude']) && ($params['exclude'] == $k || preg_match($params['exclude'],$k))) continue;
				$selected = ($params['selected'] == $k) ? "selected" : "";
				print "<option value=\"$k\" $selected>$v</option>\n";
			}
		}
		else
		{
			foreach($params['values'] as $k)
			{
				if (isset($params['exclude']) && ($params['exclude'] == $k || preg_match($params['exclude'],$k))) continue;
				$selected = ($params['selected'] == $k) ? "selected" : "";
				print "<option value=\"$k\" $selected>$k</option>\n";
			}
		}
	}
	else if (isset($params['start']) && isset($params['end']))
	{
		$s = intval($params['start']);
		$e = intval($params['end']);
		if ($s>$e) $step=-1; else $step=1;

		do
		{
			if (isset($params['exclude']) && ($params['exclude'] == $s || preg_match($params['exclude'],$s))) continue;
			$selected = ($params['selected'] == $s) ? "selected" : "";
			print "<option value=\"$s\" $selected>$s</option>\n";
			$s+=$step;
		} while($s-$e!=$step);
	}
	print "</select>";
}

// generate sorting arrow up or down or blank by $_COOKIE['_tbsort_'+grp]
$smarty->register_function("darrow", "smarty_darrow");
function smarty_darrow($params, &$smarty)
{
	if (!isset($params['grp'])) die("Error: Params grp missing");
	if (!isset($params['col'])) die("Error: Params col missing");

	$col = $_COOKIE['_tbsort_'.$params['grp']];
	$order = $_COOKIE['_tbsort_'.$params['grp'].'_order'];
	
	if($col==$params['col']){
        return ($order == 'asc' ? '&#x25B4;' : '&#x25BE;');
	}else{
		return '';
	}
	
	/*if (preg_match("/^$params[col] (asc|desc)$/", $order, $matches))
	{
		return ($matches[1] == 'asc' ? '&#x25B4;' : '&#x25BE;');
	}
	else
	{
		return "";
	}*/
}

$smarty->register_modifier("abs", "smarty_abs");
function smarty_abs($str)
{
	return abs($str);
}

$smarty->register_modifier("strtotime", "smarty_strtotime");
function smarty_strtotime($str)
{
	return strtotime($str);
}

$smarty->register_modifier("num_format", "smarty_num_format");
function smarty_num_format($str, $float_digit = 0)
{
	$str = round($str, $float_digit);
	return (strpos($str,'.')>0) ? number_format($str, $float_digit) : number_format($str);
}

$smarty->register_function("input", "smart_input");
function smart_input($params, &$smarty)
{
	static $counter = 0;
	$viewmode = $smarty->get_template_vars("open_mode");
	if ($viewmode == 'view' && $params['type'] == 'hidden') return '';
	if ($viewmode == 'view')
		$ret = "<span";
	else{
		if(isset($params['id']))
            $ret = "<input id=".$params['id'];
		else
			$ret = "<input ";
//       		$ret = "<input id=_si[$counter]";
	}


	foreach($params as $k=>$v)
	{
		$k = strtolower($k);
		// only show class in viewing mode
		if ($viewmode == 'view' && $k!='class') continue;
		// checked should not be displayed if checked is false
		if ($k=='checked') { if ($params['checked']==$params['value']) $ret .= ' checked'; continue; }
		$ret .= " $k=\"$v\"";
	}
	$ret .= ">";

	if ($viewmode == 'view')
	{
		if ($params['type']!='checkbox' && $params['type']!='radio')
		{
			$ret .= $params['value'];
		}
		elseif ($params['type']=='radio')
		{
			if ($params['checked']==$params['value'])
			{
				$ret .= "&nbsp;[X]";
			}
			else
			{
				$ret .= "&nbsp;[&nbsp;&nbsp;]";
			}
		}
		elseif ($params['type']=='checkbox' && $params['checked'])
		{
			$ret .= "&nbsp;[X]";
		}
		else
			$ret .= 'No';
		$ret .= "</span>";
	}

	$counter++;
	return $ret;
}

$smarty->register_function("json_encode", "smarty_json_encode");
function smarty_json_encode($params, &$smarty){
	print json_encode($params['var']);
}

$smarty->register_function("var_export", "smarty_var_export");
function smarty_var_export($params, &$smarty)
{
 	$var = $params['var'];
	print var_export($var);
}

$smarty->register_function("array_assign", "smarty_array_assign");
function smarty_array_assign($params, &$smarty)
{
	$array_name = $params['array_name'];	// get array name
	$tmp_data = $smarty->get_template_vars($array_name);	// get the array from smarty
	//print "get data = ";
	//print_r($tmp_data);
	//print "<br />";
	// construct eval string
	$eval_str = '$tmp_data';	
	for($i = 1; $i <= 10; $i++){	// check array key, maximum 10 level
		$k = 'key'.$i;
		if(!isset($params[$k]) || $params[$k]==='')	break;
		
		$eval_str .= '["'.$params[$k].'"]';
	}
	$value = $params['value'];
	
	//print "array_name = $array_name<br />";
	//print "value = $value<br />";
	$eval_str .= ' = $value;';
	//print "$eval_str<br />";
	eval($eval_str);
	//print "after eval = ";
	//print_r($tmp_data);
	//print "<br />";
		
	$smarty->assign($array_name, $tmp_data);
}

$smarty->register_modifier("decimal_qty", "smarty_decimal_qty");
function smarty_decimal_qty($str)
{
	global $config;

	// $str === Sku items [doc_allow_decimal]

	$default_mi=true;
	if ($str)	$default_mi=false;
	
	if ($default_mi)	return "mi(this);";
	else				return "this.value=float(round(this.value, ".$config['global_qty_decimal_points']."));";
}

$smarty->register_modifier("rounding", "smarty_rounding");
function smarty_rounding($str)
{
	return round(mf($str) * 2, 1)/2;
}

$smarty->register_modifier("str_replace", "smarty_str_replace");
function smarty_str_replace($str, $target_char, $replace_char)
{
	return str_replace($target_char, $replace_char, $str);
}


// load to show GPM broadcast message 
$smarty->register_function("load_and_show_gpm_broadcast_message", "smarty_load_and_show_gpm_broadcast_message");
function smarty_load_and_show_gpm_broadcast_message($params, &$smarty)
{
	global $con, $sessioninfo;
	
	if(!$sessioninfo)	exit;
	
	$msg_list = array();
	$q1 = $con->sql_query("select * from gpm_broadcast_msg where active=1 and CURRENT_TIMESTAMP<expire_timestamp order by id");
	while($r = $con->sql_fetchassoc($q1)){
		$r['allowed_branch'] = unserialize($r['allowed_branch']);
		if(!$r['allowed_branch'][$sessioninfo['branch_id']])	continue;	// not for this branch
		
		$msg_list[] = $r;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign($params['var'], $msg_list);
}

$smarty->register_function("receipt_no_prefix_format", "smarty_receipt_no_prefix_format");
function smarty_receipt_no_prefix_format($params, &$smarty)
{
	global $con, $sessioninfo, $config;
	
	$bid = mi($params['branch_id']);
	$cid = mi($params['counter_id']);
	$receipt_no = mi($params['receipt_no']);
	
	// print default receipt no if no config set
	if(!$config['receipt_no_prefix_format']){
		print $receipt_no;
		return;
	}
	
	if($config['receipt_no_prefix_format']['use_branch_id']){
		$prefix_receipt_no = str_pad($bid, $config['receipt_no_prefix_format']['use_branch_id'], "0", STR_PAD_LEFT);
	}else{
		$prefix_receipt_no = get_branch_code($bid);
	}
	
	if($config['receipt_no_prefix_format']['use_counter_id']){
		$prefix_receipt_no .= str_pad($cid, $config['receipt_no_prefix_format']['use_counter_id'], "0", STR_PAD_LEFT);
	}else{
		$counter_info = get_counter_info($bid, $cid);
		$prefix_receipt_no .= $counter_info['network_name'];
	}
	
	$prefix_receipt_no .= str_pad($receipt_no, 6, "0", STR_PAD_LEFT);

	print $prefix_receipt_no;
}

$smarty->register_function("get_logo_url", "smarty_get_logo_url");
function smarty_get_logo_url($params, &$smarty)
{
	/*
	priority for logo images is :
		1- config['report_logo_by_branch']
		2- branch logo, set in masterfile branch
		3- smarty config (site.conf)
		4- ui/logo.png
	*/
	
	global $config, $sessioninfo;
	
	$url = false;
	$bid = mi($params['bid']);
	if (!$bid) $bid = $_REQUEST['branch_id'] ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
	
	if ($config['report_logo_by_branch'])
	{
		$bcode = get_branch_code($bid);
		$url = $config['report_logo_by_branch'][$params['mod']][$bcode];
		if ($url) return $url;
	}
	
	if (file_exists($f = 'ui/branch_logo-'.$bid.'.png')) return $f;
	if ($r = $smarty->get_config_vars('LOGO_IMAGE')) return $r;
	return 'ui/logo.png';
}

$smarty->register_function("getSKUItems", "smarty_getSKUItems");
function smarty_getSKUItems($params, &$smarty)
{
	global $appCore;

	$si = $appCore->skuManager->getSKUItemsInfo($params['sid']);

	if ($params['assign']){
		$smarty->assign($params['assign'], $si);
	}
}

$smarty->register_modifier("weight_nf", "smarty_weight_nf");
function smarty_weight_nf($str)
{
	global $config;
	
	if($str == 0 || $str == "") return 0;
	return (strpos($str,'.')>0) ? number_format($str, $config['global_weight_decimal_points']) : number_format($str);
	//return number_format($str, $config['global_qty_decimal_points']);
}

$smarty->register_function("show_sku_photo", "smarty_show_sku_photo");
function smarty_show_sku_photo($params, &$smarty){
	global $appCore;
	
	$sid = mi($params['sku_item_id']);
	$photo_info = $appCore->skuManager->getSKUItemPhotos($sid);
	$photo_info['sid'] = $sid;
	//print_r($photo_info);
	
	$smarty->assign('photo_info', $photo_info);
	$smarty->assign('container_id', $params['container_id']);
	$smarty->assign('show_as_first_image', $params['show_as_first_image']);
	$smarty->display('shared_sku_photo.tpl');
}

$smarty->register_function("show_duration", "smarty_show_duration");
function smarty_show_duration($params, &$smarty){	
	$total_second = mi($params['seconds']);
	
	// Day
	$d = floor($total_second / 60 / 60 / 24);
	$total_second -= $d * 24 * 60 *60;
	
	// Hour
	$h = floor($total_second / 60 / 60);
	$total_second -= $h * 60 *60;
	
	// Minute
	$m = floor($total_second / 60);
	$total_second -= $m * 60;
	
	// Second
	$s = $total_second;
	
	$str = '';
	$display_type = '';
	if(isset($params['display_type'])) $display_type = trim($params['display_type']);
	switch($display_type){
		case 'short':
			if($d>0)	$str .= $d."d ";
			if($h>0)	$str .= $h."h ";
			if($m>0)	$str .= $m."m ";
			if($s>0 && !$params['no_seconds'])	$str .= $s."s";
			break;
		default:
			if($d>0)	$str .= "$d day ";
			if($h>0)	$str .= "$h hour ";
			if($m>0)	$str .= "$m minutes ";
			if($s>0 && !$params['no_seconds'])	$str .= "$s seconds ";
			break;
	}
	
	
	if($params['assign']){
		$smarty->assign($params['assign'], $str);
		exit;
	}
	print $str;
}
?>
