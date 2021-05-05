<?php
/*
8/7/2007 5:06:38 PM yinsee
- move sel() function to common.php
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$smarty->assign('PAGE_TITLE', "SKU Summary (TRY)");
$smarty->display("header.tpl");

?>
<h1>SKU Summary (TRY)</h1>
<script>
function showsub(root_id)
{
	document.f.root_id.value = root_id
	document.f.submit();
}
</script>
<?
prepare_form();
if ($_REQUEST['load']) generate_table();

$smarty->display("footer.tpl");

function prepare_form(){
	global $con;
	
	if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
	if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
?>
<p>
<form name=f class="stdframe" style="background:#fff">
<input type=hidden name=load value=1>
<input type=hidden name=root_id value="<?=$_REQUEST['root_id']?>">
<?
	$con->sql_query("select id as value, code as title from branch order by id");
	print "Branch  ";
	sel($con->sql_fetchrowset(), "branch_id");
?>
&nbsp;&nbsp; <b>Date From</b> <input type="text" name="from" value="<?=$_REQUEST['from']?>" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="<?=$_REQUEST['to']?>" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

&nbsp;&nbsp; <input type=submit value='Show Report'>
</form>
</p>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">


    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

</script>
<?
}

function generate_table()
{
	global $con,$sessioninfo;
	
	$root_id = intval($_REQUEST['root_id']);
	
	$sub = $con->sql_query("select id,description,tree_str from category where root_id = $root_id");
	$ti = $con->sql_fetchrow();

	//sql statement conditions
	if ($_REQUEST['branch_id']>0) $filter[] = "pos_transaction.branch_id = ".intval($_REQUEST['branch_id']);
	if (isset($_REQUEST['from'])) $filter[] = "pos_transaction.timestamp >= ".ms($_REQUEST['from']);
	if (isset($_REQUEST['to'])) $filter[] = "date(pos_transaction.timestamp) <= ".ms($_REQUEST['to']);
	$filter[] = "category.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
	
	if ($filter) $filter = join(' and ', $filter);

	$con->sql_rowseek(0);
	$tb = array();
	while($r = $con->sql_fetchrow($sub))
	{
		$con->sql_query("select sum(pos_transaction.qty) as t_qty, sum(pos_transaction.amount) as amt from pos_transaction left join sku_items on pos_transaction.sku_item_code = sku_items.sku_item_code left join sku on sku_items.sku_id = sku.id left join category on sku.category_id = category.id where $filter and (category.id = $r[id] or category.tree_str like '$r[tree_str]($r[id])%')") or die(mysql_error());

		// store to table variable
	    if ($con->sql_numrows()>0)
		{
			$tb[$r['description']]['id'] = $r['id'];
			while($t = $con->sql_fetchrow())
			{
			    $tb[$r['description']][$t['dt']] = $t['amt'];
			    $tb[$r['description']]['total']+=$t['amt'];
			    $tb[$r['description']]['total_qty']+=$t['t_qty'];
			    $uq_cols[$t['dt']] = 1;
			}
		}
		$con->sql_query("select sum(grn_items.ctn) as qty,sum(grn_items.ctn*grn_items.cost) as amt from grn_items left join grn on (grn_items.grn_id = grn.id and grn_items.branch_id = grn.branch_id) left join grr on (grn.grr_id = grr.id and grn.branch_id = grr.branch_id) where grn_items.branch_id='$_REQUEST[branch_id]'") or die(mysql_error());
		// and grr.rcv_date >='$_REQUEST[from]' and grr.rcv_date<='$_REQUEST[to]'
		// store to table variable
	    if ($con->sql_numrows()>0)
		{
			//$tb[$r['description']]['id'] = $r['id'];
			while($t1 = $con->sql_fetchrow())
			{
			    $tb[$r['description']][$t1['dt']] = $t1['amt'];
			    $tb[$r['description']]['total_order_amt']+=$t1['amt'];
			    $tb[$r['description']]['total_order_qty']+=$t1['qty'];
			    //$uq_cols[$t['dt']] = 1;
			}
		}
	}
	
	echo "<pre>";print_r($tb);echo "</pre>";
	print "<p>&#187; <a href=\"javascript:void(showsub(0))\">ROOT</a> / " . get_category_tree($ti['id'], $ti['tree_str'], $dummy, " / ", true, 'javascript:void(showsub(', '))');

	if (!$tb)
	{
		print "<p>No data</p>";
		return;
	}
	ksort($uq_cols);
	reset($uq_cols);

	print "<br /><font class=small>Click on a sub-category for further detail.</font></p>";

	//start printout table
	print "<table class=\"sortable tb\" id=_t cellspacing=0 cellpadding=2 border=0>";
	print "<tr><th align=left>&nbsp;</th>";
	/*foreach ($uq_cols as $dt=>$dummy)
	{
	    $ymd = split("-", $dt);
		print "<th valign=bottom>";
		if ($lastm != $ymd[1] || $lasty != $ymd[0])
		{
		    print "<span class=small>$ymd[1]/$ymd[0]</span><br />";
		    $lastm = $ymd[1]; $lasty = $ymd[0];
		}
		print "$ymd[2]</th>";
	}*/
	print "<th>Total Qrder Qty</th>";
	print "<th>Total Order</th>";
	print "<th>Total Sold Qty</th>";
	print "<th>Total Sold</th>";
	print "</tr>";
	
	foreach ($tb as $cat=>$v)
	{
		//echo "<pre>";print_r($cat);echo "</pre>";
		print "<tr><th align=left><a href=\"javascript:void(showsub($v[id]))\">$cat**</a></th>";
		/*foreach ($uq_cols as $dt=>$dummy)
		{
		    if ($v[$dt]==0)
				print "<td>&nbsp;</td>";
			else
			    print "<td class=small align=right>".number_format($v[$dt],2)."</td>";
			$total[$dt] += $v[$dt];
			$tb_total += $v[$dt];
		}*/
		if ($v['total']==0)
			print "<td>&nbsp;</td>";
		else
 			print "<td class=small align=right>".number_format($v['total_order_qty'])."</td>";
			print "<td class=small align=right>".number_format($v['total_order_amt'],2)."</td>";
 			print "<td class=small align=right>".number_format($v['total_qty'])."</td>";
			print "<td class=small align=right>".number_format($v['total'],2)."</td>";
		print "</tr>";
	}
	/*print "<tr class=sortbottom><th align=right>Total</th>";
	foreach ($uq_cols as $dt=>$dummy)
	{
		if ($total[$dt]==0)
			print "<td>&nbsp;</td>";
		else
		    print "<td class=small align=right>".number_format($total[$dt],2)."</td>";
	}
	print "<td class=small align=right>".number_format($tb_total,2)."</td>";
	print "</tr>";*/
	print "</table>";
}

?>
