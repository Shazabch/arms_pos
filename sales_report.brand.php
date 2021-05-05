<?php
/*
Revision History
----------------
3 Apr 2007 - yinsee
- added "Price type" selection (request by sllee@aneka)

10/16/2007 1:02:05 PM gary
- change page title.

2/24/2009 1:17:00 PM Andy
- add branch group function

4/21/2010 2:53:19 PM Andy
- Fix brand filter bugs.
- Brand, Vendor and Department Sales Report now change to use live pos data.

5/3/2010 11:50:13 AM Andy
- Fix a bugs that Brand, Vendor and Department Sales Report get cancelled sales.

7/13/2010 3:56:49 PM Andy
- Change report to only show finalized sales.

4/11/2011 12:28:05 PM Alex
- remove checking on exclude HQ branch

6/27/2011 10:14:00 AM Andy
- Make all branch default sort by sequence, code.

7/4/2011 7:04:20 PM Alex 
- fix bugs unable to show sku type select box

7/6/2011 2:48:22 PM Andy
- Change split() to use explode()

11/1/2011 12:20:43 PM Justin
- Added filter for brand listing to base on user's sessioninfo.

3/15/2012 10:25:47 AM Andy
- Modify prepare_sql() to pass by branch_id.
- Check maintenance version 119.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

15/05/2014 17:10:09 Andy
- Fix error when filter by user profile selected brands.

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

8/18/2014 2:11 PM Justin
- Enhanced to add privilege checking for export excel.
*/

include("include/common.php");
$maintenance->check(119);

if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', "Daily Brand Sales Report");
$_PRINTTITLE = "Daily Brand Sales Report";
$_SRTITLE = 'Brand';
$_SRTABLE = 'brand';
$_SRTABLE_ID = 'brand_id';

include("sales_report.generic.php");

function get_print_title($cat)
{
	global $con;
	$title = '';

	/*
	$con->sql_query("select description from brand where id = ".intval($_REQUEST['brand_id']));
	$b = $con->sql_fetchrow();
	$title .= 'Brand : ' . $b['description'];
	*/
	$title .= get_brand_title($_REQUEST['brand_id']);

 	$title .= $cat.'<br />';

	$con->sql_query("select description from vendor where id = ".intval($_REQUEST['vendor_id']));
	$b = $con->sql_fetchrow();
	$title .= 'Vendor : ' . $b['description'];

	return $title;
}



function prepare_sql($bid)
{
	global $_SRTITLE, $_SRTABLE, $_SRTABLE_ID, $sessioninfo,$branch_group;
	              
	/*if (BRANCH_CODE=='HQ')
	{
	    $branch_id = intval($_REQUEST['branch_id']);
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
	}
	else	$filter[] = "pos.branch_id = ".intval($sessioninfo['branch_id']);   // single branch selected*/
	$filter[] = "pos.branch_id=".mi($bid);
	
	if (isset($_REQUEST['from'])) $filter[] = "pos.date >= ".ms($_REQUEST['from']);
	if (isset($_REQUEST['to'])) $filter[] = "pos.date < date_add(".ms($_REQUEST['to']).",interval 1 day)";
	$filter[] = "pos.cancel_status=0 and pf.finalized=1";
	
	if($_REQUEST['brand_id'])	$filter[] = "sku.brand_id in (".join(',',process_brand_id($_REQUEST['brand_id'])).") ";
	elseif($sessioninfo['brands']) $filter[] ="sku.brand_id in (".join(",",array_keys($sessioninfo['brands'])).")";

	if ($_REQUEST['department_id']!='')
		$filter[] = "category.department_id = ".intval($_REQUEST['department_id']);
	else
		$filter[] = "category.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";

	if($_REQUEST['sku_type'])
	{
      $filter[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
  }

	if ($_REQUEST['price_type']!='All') $filter[] = "pi.trade_discount_code=".ms($_REQUEST['price_type']);

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

<h1> Daily <?=$_SRTITLE?> Sales Report</h1>
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
<p>
<form name=f class="stdframe" style="background:#fff">
<input type=hidden name=load value=1>
<input type=hidden name=print value=0>
<input type=hidden name=excelxml value=0>
<input type=hidden name=root_id value="<?=$_REQUEST['root_id']?>">
<?
	if (BRANCH_CODE=='HQ')
	{
		/*$con->sql_query("select id as value, branch.code as title from branch where code <> 'HQ' order by id");
		print "Branch  ";
		sel($con->sql_fetchrowset(), "branch_id", false);
		print "&nbsp;&nbsp; ";*/
		
		$con->sql_query("select * from branch order by sequence,code");
		print "<b>Branch</b>  ";
		print "<select name=branch_id>";
		print "<option value=''>-- All --</option>";
		while($r = $con->sql_fetchrow()){
			if ($config['sales_report_branches_exclude']) {
				$branch_code = $r['code'];
				if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
					//print "$branch_code skipped<br />";
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
			    $selected = '';
			    if($_REQUEST['branch_id']=="bg,$bgid")    $selected = 'selected';
			    print "<option class=\"bg\" value=\"bg,$bgid\" $selected>$bg[code]</option>";

			    foreach($branch_group['items'][$bgid] as $r){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $r['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							//print "$branch_code skipped<br />";
							continue;
						}
					}
			        $selected = '';
			    	if($_REQUEST['branch_id']==$r['branch_id'])    $selected = 'selected';
					print "<option class=\"bg_item\" value=\"$r[branch_id]\" $selected>$r[code] - $r[description]</option>";
				}
			}
			print "</optgroup>";
		}
		print "</select>&nbsp;&nbsp;";
	}

	$filter = ($sessioninfo['brands']) ? "where id in (".join(",",array_keys($sessioninfo['brands'])).")" : "";

	print "<b>$_SRTITLE</b>  ";
	//sel($con->sql_fetchrowset(), "$_SRTABLE_ID", true);
	print '<select name="brand_id">';
	print '<option value="">All</option>';
	if ($brand_group = get_brand_group()) {
		print '<optgroup label="Brand Group">';
		foreach ($brand_group as $bgk => $bgv) {
			$selected = ($_REQUEST['brand_id'] == $bgk) ? 'selected':'';
			print '<option '.$selected.' value="'.$bgk.'">'.$bgv.'</option>';
		}
		print '</optgroup>';
	}
	print '<optgroup label="Brand">';
	$con->sql_query("select id as value, description as title from $_SRTABLE $filter order by title");
	while ($r = $con->sql_fetchassoc()) {
		$selected = ($_REQUEST['brand_id'] == mi($r['value'])) ? 'selected':'';
		print '<option '.$selected.' value="'.mi($r['value']).'">'.$r['title'].'</option>';
	}
	print '</optgroup>';
	print '</select>';

/*	$con->sql_query("select id as value, description as title from vendor order by title");
	print "&nbsp;&nbsp; Vendor  ";
	sel($con->sql_fetchrowset(), "vendor_id", false);
*/

	//$con->sql_query("select distinct(sku_type) from sku");
	$con->sql_query("select * from sku_type");
	print "<br /><br /><b>SKU Type</b>";

	sel($con->sql_fetchrowset(), "sku_type", true);

	$con->sql_query("select code as pricy_type from trade_discount_type order by code");
	print "&nbsp;&nbsp; <b>Price Type</b> ";
	sel($con->sql_fetchrowset(), "price_type", true, 'All');

	$con->sql_query("select id as value, description as title from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by 2");
	print "&nbsp;&nbsp; <b>Department</b>  ";
	sel($con->sql_fetchrowset(), "department_id", true);
?>
<br /><br />
<b>Date From</b> <input type="text" name="from" value="<?=$_REQUEST['from']?>" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="<?=$_REQUEST['to']?>" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
&nbsp;&nbsp; <input type=checkbox name=by_monthly <?=$_REQUEST['by_monthly']?"checked":""?>> <b>Group Monthly</b>
&nbsp;&nbsp; <label><input type="checkbox" name="exclude_inactive_sku" value="1" <?=$_REQUEST['exclude_inactive_sku']?"checked":""?> /><b>Exclude inactive SKU</b></label>
<br/><br/>
<input type=submit value='Show Report'> <input type=button value=Print onclick="alert('Note: This report should be printed on A4 Lanscape');do_print();"> 
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
