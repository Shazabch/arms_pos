<?php
/*
1/17/2011 2:52:16 PM Alex
- change use report_server

6/24/2011 5:53:07 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
set_time_limit(0);
ini_set("memory_limit", "128M");

$smarty->assign('PAGE_TITLE', "Aged Stock Balance Report");

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'ajax_load_sku':
	    	generate_sku_table();
	    	exit;
	    	
	    case 'ajax_load_category':
	    	generate_category_table();
			exit;
			
	    case 'category':
	    	break;
	    	
	    default:
	    	echo "<h1>Unhandled Request</h1>";
	    	print_r($_REQUEST);
	    	exit;
	}
}
$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}

$smarty->display("header.tpl");
prepare_form();
if ($_REQUEST['a']=='category'){
	generate_category_table();
}
$smarty->display("footer.tpl");

$con_multi->close_connection();
exit;


function prepare_form(){
	global $con;
	
	if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
	if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
?>
<h1>Aged Stock Balance Report</h1>

<style>
#show_sku {
	padding:10px 0;
}
</style>
<script>
function show_sub(root_id){
	document.f.root_id.value = root_id
	document.f.a.value = 'category';
	document.f.submit();
}

function expand_sub(root_id, indent, el){	
	el.onClick='';
	el.src = '/ui/clock.gif';
	new Ajax.Request('report.aged_stock.php?'+Form.serialize(document.f)+"&a=ajax_load_category&ajax=1&root_id="+root_id+"&indent="+indent,{
		onComplete: function(e) {
			new Insertion.After($('r'+root_id), e.responseText);
			el.remove();	
		},
	});
}

function show_sku(root_id){
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('show_sku','report.aged_stock.php?'+Form.serialize(document.f)+"&a=ajax_load_sku&root_id="+root_id,{evalScripts:true});
}
</script>

<p>
<form name=f class="stdframe" style="background:#fff">
<input type=hidden name=a value="category">
<input type=hidden name=root_id value="<?=$_REQUEST['root_id']?>">

<b>Date From</b> 
<input type="text" name="from" value="<?=$_REQUEST['from']?>" id="added1" readonly="1" size=12>
<img align=absmiddle src="/ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;
  
<b>To</b> 
<input type="text" name="to" value="<?=$_REQUEST['to']?>" id="added2" readonly="1" size=12>
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp; 

<?
if (BRANCH_CODE == 'HQ'){
	$con->sql_query("select id as value, code as title from branch order by sequence,code");
	echo "<b>Branch</b>";
	sel($con->sql_fetchrowset(), "branch_id");
}
?>
&nbsp;&nbsp; 

<input type=submit value='Show Report'>
</form>
</p>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue">
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",
        ifFormat       :    "%Y-%m-%d",
        button         :    "t_added1",
        align          :    "Bl",
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "added2",
        ifFormat       :    "%Y-%m-%d",
        button         :    "t_added2",
        align          :    "Bl",
        singleClick    :    true

    });
</script>
<?
}

function generate_category_table(){
	global $con,$con_multi,$sessioninfo;
	
	$root_id = intval($_REQUEST['root_id']);
	if ($root_id==0){
		$pf = "p1";
		$uncat_name = 'Un-categorized';
	}
	else{
		$con->sql_query("select id,level,tree_str,description from category where id = $root_id");
		$root = $con->sql_fetchrow();
		$pf = "p".($root['level']+1);
		$filter[] = "p$root[level] = $root_id";
		$uncat_name = $root['description'];
	}

	if (!$_REQUEST['ajax']){
		echo "<p>&#187; <a href=\"javascript:void(show_sub(0))\">ROOT</a> / " . get_category_tree($root['id'], $root['tree_str'], $dummy, " / ", true, 'javascript:void(show_sub(', '))');
	}

    $con->sql_query("select id,description from category where root_id = $root_id or id=$root_id");
    while($r=$con->sql_fetchrow()){
        $category[$r['id']] = $r['description'];
	}
	//echo"<pre>";print_r($category);echo"</pre>";
	
	if ($sessioninfo['level']<1000){
		$filter[] = "c.p2 in ($sessioninfo[department_ids])";	
	}

	$branch_id=get_request_branch(true);		
	if ($branch_id>0){
		$filter[] = "simb.branch_id = ".intval($branch_id);	
	}
	
	$from_Date=date('Y-m-1', strtotime("$_REQUEST[from]"));		
	$to_Date=date('Y-m-1', strtotime("$_REQUEST[to]"));

	$filter[] = "simb.date between ".ms($from_Date)." and ".ms($to_Date);

	if ($filter){
 		$filter = join(' and ', $filter);	
	}
	
	$tb = array();	

	$con_multi->sql_query("select $pf as cat_id, sum(simb.qty) as qty, sum(simb.qty*simb.grn_cost) as amt, year(date) as year, month(date) as month
from sku_items_monthly_balance simb  
left join sku_items si on si.id=sku_item_id 
left join sku on sku.id=si.sku_id 
left join category_cache c using (category_id) 
where $filter group by $pf,year,month order by $pf,year,month") or die(mysql_error());
	while($t = $con_multi->sql_fetchrow()){
		$t['dt'] = sprintf("%02d/%02d", $t['year'], $t['month']);
		$tb[$t['cat_id']][$t['dt']]['amt'] = $t['amt'];
    	$tb[$t['cat_id']]['total']['amt']+=$t['amt'];
		$tb[$t['cat_id']][$t['dt']]['qty'] = $t['qty'];
    	$tb[$t['cat_id']]['total']['qty']+=$t['qty'];
	}

	foreach (array_keys($tb) as $id){
		$tb[$id]['id'] = $id;
	    if (!$category[$id]){
	        $tb[$id]['have_subcat'] = false;
            $tb[$id]['description'] = $uncat_name;
		}
	    else{
			$tb[$id]['have_subcat'] = check_subcat($id);
		    $tb[$id]['description'] = $category[$id];
	    }
	}
	//echo"<pre>";print_r($tb);echo"</pre>";
		
	if (!$tb){
		echo "<p>No data</p>";
		return;
	}
	
	$d1 = strtotime($from_Date);
	$d2 = strtotime($to_Date);
	$uq_cols = array();
	while($d1<=$d2){
		$uq_cols[date('Y/m',$d1)] = 1;
		$d1 += 86400;
	}
	
	if (!$_REQUEST['ajax']){
		echo "<br><font class=small>Click on a sub-category for further detail. Click <img src=/ui/icons/table.png align=absmiddle>  to display SKU in the category.</font></p>";
		echo "<table class=\"sortable tb\" id=_t cellspacing=0 cellpadding=2 border=0>";
		echo "<tr><th align=left>&nbsp;</th>";
		foreach ($uq_cols as $dt=>$dummy){
			echo "<th valign=bottom>";
			echo "$dt</th>";
		}
		echo "<th>Total</th><td style='background:#900;'>&nbsp;</td>";
		$last=0;$lastm=0;
		foreach ($uq_cols as $dt=>$dummy){
			echo "<th valign=bottom>";
			echo "$dt</th>";
		}
		echo "<th>Total</th>";
		echo "</tr>";
	}

	foreach ($tb as $id=>$v){
		echo "<tr id=r$id><th nowrap align=left>";
		echo "<img src=/ui/icons/table.png align=absmiddle onclick=\"show_sku('".($id?$id:$root_id)."')\" title=\"Show SKU\">";
		echo "<img src=/ui/pixel.gif width=".($_REQUEST['indent']*16)." height=1>";
		if ($v['have_subcat']){
			echo " <img onclick=\"expand_sub('$id',".($_REQUEST['indent']+1).",this)\" src=/ui/expand.gif>";
			echo " <a href=\"javascript:void(show_sub('$id'))\">$v[description]</a></th>";
		}
		else
		    echo " $v[description]</th>";

		foreach ($uq_cols as $dt=>$dummy){
		    if ($v[$dt]['qty']==0)
				echo "<td>&nbsp;</td>";
			else
			    echo "<td class=small align=right>".number_format($v[$dt]['qty'])."</td>";
			$total[$dt] += $v[$dt]['qty'];
			$tb_total += $v[$dt]['qty'];
			$totala[$dt] += $v[$dt]['amt'];
			$tb_totala += $v[$dt]['amt'];
		}
		echo "<td class=small align=right>".number_format($v['total']['qty'])."</td>";
		echo "<td style='background:#900;'>&nbsp;</td>";
		foreach ($uq_cols as $dt=>$dummy){
		    if ($v[$dt]['amt']==0)
				echo "<td>&nbsp;</td>";
			else
			    echo "<td class=small align=right>".number_format($v[$dt]['amt'],2)."</td>";
		}
		echo "<td class=small align=right>".number_format($v['total']['amt'],2)."</td>";
		echo "</tr>";
	}
	
	if (!$_REQUEST['ajax']){
		echo "<tr class=sortbottom><th align=right>Total</th>";
		foreach ($uq_cols as $dt=>$dummy){
		    echo "<td class=small align=right>".number_format($total[$dt])."</td>";
		}
		echo "<td class=small align=right>".number_format($tb_total)."</td>";
		echo "<td style='background:#900;'>&nbsp;</td>";
		foreach ($uq_cols as $dt=>$dummy){
		    echo "<td class=small align=right>".number_format($totala[$dt],2)."</td>";
		}
		echo "<td class=small align=right>".number_format($tb_totala,2)."</td>";
		echo "</tr>";
	
		echo "</table>";
		echo "<div id=show_sku></div>";
	}
}


function generate_sku_table(){
	global $con,$con_multi,$sessioninfo;
	
	$root_id = intval($_REQUEST['root_id']);
	if ($root_id==0){
		$root['description'] = 'Uncategorized';
		echo "<h4>$root[description] SKU</h4>";
	}
	else{
		$con->sql_query("select id,level,description from category where id = $root_id");
		$root = $con->sql_fetchrow();
		$pf = "p".($root['level']+1);
		$filter[] = "p$root[level] = $root_id";
		echo "<h4>SKU under category $root[description] (with sales)</h4>";
	}

	$branch_id=get_request_branch(true);
	if ($branch_id>0){
 		$filter[] = "simb.branch_id = ".intval($branch_id);	
	}

	$from_Date=date('Y-m-1', strtotime("$_REQUEST[from]"));		
	$to_Date=date('Y-m-1', strtotime("$_REQUEST[to]"));	
	//$to_Date = date("Y-m-1",strtotime("+1 day",strtotime($_REQUEST['to'])));
	$filter[] = "simb.date between ".ms($from_Date)." and ".ms($to_Date);
	
	if ($root_id==0){
		$filter[] = "c.p0 is null";
	}
	else{
		if ($sessioninfo['level']<9999){
 			$filter[] = "c.p2 in ($sessioninfo[department_ids])";		
		}
	}		
	if ($filter) $filter = join(' and ', $filter);	
	
	$tb = array();
	$con_multi->sql_query("select sum(simb.qty) as qty, sum(simb.qty*simb.grn_cost) as amt, year(date) as year, month(date) as month, sku_items.sku_item_code, description 
from sku_items_monthly_balance simb 
left join sku_items on simb.sku_item_id=sku_items.id 
left join sku on sku_items.sku_id = sku.id 
left join category_cache c on sku.category_id = c.category_id 
where $filter 
group by sku_item_code, year, month order by sku_item_code, year, month") or die(mysql_error());
	while($t = $con_multi->sql_fetchrow()){
		$t['dt'] = sprintf("%02d/%02d", $t['year'], $t['month']);		
        $tb[$t['sku_item_code']][$t['dt']]['qty'] = $t['qty'];
        $tb[$t['sku_item_code']][$t['dt']]['amt'] = $t['amt'];
        $tb[$t['sku_item_code']]['total']['qty'] += $t['qty'];
        $tb[$t['sku_item_code']]['total']['amt'] += $t['amt'];
        $tb[$t['sku_item_code']]['description'] = $t['description'];		
	}
	//echo"<pre>";print_r($tb);echo"</pre>";
	if (!$tb){
		echo "<p>No data</p>";
		return;
	}

	$d1 = strtotime($_REQUEST['from']);
	$d2 = strtotime($_REQUEST['to']);
	$uq_cols = array();
	while($d1<=$d2){
		$uq_cols[date('Y/m',$d1)] = 1;
		$d1 += 86400;
	}

	echo "<table id=tb_sku class=\"sortable tb\" id=_t cellspacing=0 cellpadding=2 border=0>";
	echo "<tr><th align=left>ARMS Code</th><th align=left>Description</th>";
	foreach ($uq_cols as $dt=>$dummy){
	    echo "<th valign=bottom>";
		echo "$dt</th>";
	}
	echo "<th>Total</th><td style='background:#900;'>&nbsp;</td>";
	$last=0;$lastm=0;
	foreach ($uq_cols as $dt=>$dummy){
		echo "<th valign=bottom>";
		echo "$dt</th>";
	}
	echo "<th>Total</th>";
	echo "</tr>";
	foreach ($tb as $id=>$v){
		echo "<tr><th align=left>$id</th><th nowrap align=left>$v[description]&nbsp;</th>";

		foreach ($uq_cols as $dt=>$dummy){
		    if ($v[$dt]['qty']){
				echo "<td class=small align=right>".number_format($v[$dt]['qty'])."</td>";
			}
			else{
				echo "<td>&nbsp;</td>"; //<td>&nbsp;</td>";
			}
			
			$total[$dt] += $v[$dt]['qty'];
			$totala[$dt] += $v[$dt]['amt'];
			$tb_total += $v[$dt]['qty'];
			$tb_totala += $v[$dt]['amt'];
		}
		echo "<td class=small align=right>".number_format($v['total']['qty'])."</td>";
		echo "<td style='background:#900;'>&nbsp;</td>";
		foreach ($uq_cols as $dt=>$dummy){
		    if ($v[$dt]['qty']){
				echo "<td class=small align=right>".number_format($v[$dt]['amt'],2)."</td>";
			}
			else{
				echo "<td>&nbsp;</td>";
			}
		}
		echo "<td class=small align=right>".number_format($v['total']['amt'],2)."</td>";
		
		echo "</tr>";
	}
	echo "<tr class=sortbottom><td>&nbsp;</td><th align=right>Total</td>";
	foreach ($uq_cols as $dt=>$dummy){
	    echo "<td class=small align=right>".number_format($total[$dt])."</td>";
	}
	echo "<td class=small align=right>".number_format($tb_total)."</td>";
	echo "<td style='background:#900;'>&nbsp;</td>";
	foreach ($uq_cols as $dt=>$dummy){
	    echo "<td class=small align=right>".number_format($totala[$dt],2)."</td>";
	}
	echo "<td class=small align=right>".number_format($tb_totala,2)."</td>";
	echo "</tr>";
	echo "</table>";
	echo "\n<script>ts_makeSortable($('tb_sku'));</script>\n";
}

function check_subcat($id){
	global $con;
	$con->sql_query("select count(*) from category where root_id = $id");
	$c=$con->sql_fetchrow();
	if ($c[0]>0) return true;
	return false;
}
?>
