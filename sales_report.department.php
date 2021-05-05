<?php
/*
Revision History
----------------
8/29/2007 12:07:00 PM - yinsee
- copy from vendor monthly sales report

11/1/2007 3:22:41 PM gary
- page title.

2/24/2009 1:51:00 PM Andy
- add branch group function

4/21/2010 2:53:46 PM Andy
- Brand, Vendor and Department Sales Report now change to use live pos data.

5/3/2010 11:50:13 AM Andy
- Fix a bugs that Brand, Vendor and Department Sales Report get cancelled sales.

7/13/2010 3:56:49 PM Andy
- Change report to only show finalized sales.

4/11/2011 12:28:05 PM Alex
- remove checking on exclude HQ branch

6/20/2011 2:50:33 PM Alex
- remove 'all' in departments 

6/27/2011 10:16:05 AM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:48:54 PM Andy
- Change split() to use explode()

3/15/2012 10:26:07 AM Andy
- Modify prepare_sql() to pass by branch_id.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)
*/
include("include/common.php");

//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');

$maintenance->check(1);

if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");
$smarty->assign('PAGE_TITLE', "Department Monthly Sales Report");

$_SRTITLE = 'Department';
//$_SRTABLE = 'category';
//$_SRTABLE_ID = 'category_id';
include("sales_report.generic.php");

function get_print_title($cat)
{
	/*global $con;
	$title = '';

	$con->sql_query("select description from vendor where id = ".intval($_REQUEST['vendor_id']));
	$b = $con->sql_fetchrow();
	$title .= 'Vendor : ' . $b['description'];

 	$title .= $cat;
 	return $title;*/
 	return $cat;
}

function prepare_sql($bid)
{
    global $_SRTITLE, $_SRTABLE, $_SRTABLE_ID, $sessioninfo,$con,$branch_group;

	/*if (BRANCH_CODE=='HQ')
	{
	    $branch_id = intval($_REQUEST['branch_id']);
	    /////////////////////////////////////////////////////////////
	    if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $bid=>$r){
			    $ids[] = $bid;
			}
			$filter[] = "pos.branch_id in (".join(',',$ids).")";
		}else{
			if ($branch_id>0)
			{
				$filter[] = "pos.branch_id=$branch_id";
			}
			else
			{
			    // if it is all branch, no filter.
			}
		}
	    /////////////////////////////////////////////////////////////
	}
	else	$filter[] = "pos.branch_id = ".intval($sessioninfo['branch_id']);*/
	$filter[] = "pos.branch_id=".mi($bid);

	if (isset($_REQUEST['from'])) $filter[] = "pos.date >= ".ms($_REQUEST['from']);
	if (isset($_REQUEST['to'])) $filter[] = "pos.date < date_add(".ms($_REQUEST['to']).",interval 1 day)";
    $filter[] = "pos.cancel_status=0 and pf.finalized=1";
    
	if($_REQUEST['department_id'] !="")
	{
      $filter[] = "category.department_id = ".intval($_REQUEST['department_id']);
  	}

	if($_REQUEST['sku_type']!="")
	{
      $filter[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
  }

	if ($_REQUEST['price_type']!='All') $filter[] = "pi.trade_discount_code = ".ms($_REQUEST['price_type']);

	if ($filter) $filter = join(' and ', $filter);
	return $filter;

}

function prepare_form()
{
	global $con, $sessioninfo, $_SRTITLE, $_SRTABLE, $_SRTABLE_ID,$branch_group, $config;

	if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
	if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
?>
<script>
function do_print()
{
	document.f.target = 'ifprint';
	document.f.print.value = 1;
	document.f.submit();

	document.f.target = '';
	document.f.print.value = 0;
}

function do_excel()
{
	document.f.target = 'ifprint';
	document.f.excelxml.value = 1;
	document.f.submit();

	document.f.target = '';
	document.f.excelxml.value = 0;
}

</script>

<h1><?=$_SRTITLE?> Monthly Sales Report</h1>
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
<p>
<form name=f class="stdframe" style="background:#fff">
<input type=hidden name=load value=1>
<input type=hidden name=print value=0>
<input type=hidden name=excelxml value=0>
<input type=hidden name=root_id value="<?=$_REQUEST['root_id']?>">
<?
	if (BRANCH_CODE=='HQ'){
		$con->sql_query("select * from branch order by sequence,code");
		print "<b>Branch</b>  ";
		print "<select name=branch_id>";
		print "<option value=''>-- All --</option>";
		while($r = $con->sql_fetchrow()){
			if ($config['sales_report_branches_exclude']) {
				$branch_code = $r['code'];
				if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
					// print "$branch_code skipped66<br />";
					continue;
				}
			}
		    $selected = '';
			if(!$branch_group['have_group'][$r['id']]){
			    if($_REQUEST['branch_id']==$r['id'])    $selected = 'selected';
				print "<option value='$r[id]' $selected>$r[code] - $r[description]</option>";
			}
		}
		if($branch_group['header']){
		    print "<optgroup label='Branch Group'>";
			foreach($branch_group['header'] as $bgid=>$bg){
				if ($config['sales_report_branches_exclude']) {
					$branch_code = $r['code'];
					if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
						// print "$branch_code skipped77<br />";
						continue;
					}
				}
			    $selected = '';
			    if($_REQUEST['branch_id']=="bg,$bgid")    $selected = 'selected';
			    print "<option class=\"bg\" value=\"bg,$bgid\" $selected>$bg[code]</option>";
			    
			    foreach($branch_group['items'][$bgid] as $r){
			        $selected = '';
			    	if($_REQUEST['branch_id']==$r['branch_id'])    $selected = 'selected';
					print "<option class=\"bg_item\" value=\"$r[branch_id]\" $selected>$r[code] - $r[description]</option>";
				}
			}
			print "</optgroup>";
		}
		print "</select>&nbsp;&nbsp;";
	}

	//$con->sql_query("select distinct(sku_type) from sku");
	$con->sql_query("select * from sku_type");
	print "<b>SKU Type</b>  ";
	sel($con->sql_fetchrowset(), "sku_type", true);

	$con->sql_query("select code as price_type from trade_discount_type order by code");
	print "&nbsp;&nbsp; <b>Price Type</b>  ";
	sel($con->sql_fetchrowset(), "price_type", true, 'All');

	$con->sql_query("select id as value, description as title from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by 2");
	print "&nbsp;&nbsp; <b>Department</b>  ";
	sel($con->sql_fetchrowset(), "department_id", false);
?>
<br /><br />
<b>Date From</b> <input type="text" name="from" value="<?=$_REQUEST['from']?>" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="<?=$_REQUEST['to']?>" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
&nbsp;&nbsp; <input type=checkbox name=by_monthly <?=$_REQUEST['by_monthly']?"checked":""?>> <b>Group Monthly</b>
&nbsp;&nbsp; <label><input type="checkbox" name="exclude_inactive_sku" value="1" <?=$_REQUEST['exclude_inactive_sku']?"checked":""?> /><b>Exclude inactive SKU</b></label>
&nbsp;&nbsp; <input type=submit value='Show Report'> <input type=button value=Print onclick="alert('Note: This report should be printed on A4 Lanscape');do_print();"> 
<?
if($sessioninfo['privilege']['EXPORT_EXCEL'] == "1")
{
?>
<input type=button value="Export to Excel" onclick="do_excel();">
<?
}
?>
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
?>
