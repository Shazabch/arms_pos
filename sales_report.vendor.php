<?php
/*
Revision History
----------------
3 Apr 2007 - yinsee
- added "Price type" selection (request by sllee@aneka)

6/26/2007 6:00:06 PM -gary
- added Branch should follow GRN receiving vendor. (added filter in sql whr sku_items.id)

2/24/2009 1:32:00 PM Andy
- add branch group function

11/11/2009 3:21:50 PM Andy
- Add show all items & group by sku feature

4/21/2010 2:53:38 PM Andy
- Brand, Vendor and Department Sales Report now change to use live pos data.

5/3/2010 11:50:13 AM Andy
- Fix a bugs that Brand, Vendor and Department Sales Report get cancelled sales.

6/15/2010 10:27:07 AM Andy
- Fix a bugs which occur when tick "Use GRN"

7/13/2010 3:56:49 PM Andy
- Change report to only show finalized sales.

4/11/2011 12:28:05 PM Alex
- remove checking on exclude HQ branch

5/6/2011 10:14:19 AM Justin
- Added the Use GRN to show only the selected vendor.
- Changed the wording from "Use GRN" become "Use Last GRN".

10:11 AM 5/25/2011 Justin
- Modified the Use GRN filter.
- Amended the date to always take for 1 month from date from if found date to is earlier than date from.

2:31 PM 5/27/2011 Justin
- Added the filter for "Use Last GRN" that to check against with last Vendor.

6/27/2011 10:17:50 AM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:52:11 PM Andy
- Change split() to use explode()

8/1/2011 2:44:20 PM Andy
- Fix report always only load 1 month sales.

11/1/2011 12:20:43 PM Justin
- Added filter for vendor listing to base on user's sessioninfo.

11/16/2011 1:34:58 PM Andy
- Change "Use GRN" query.
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.
- Fix toggle "Use GRN" checkbox error.

11/24/2011 2:28:55 PM Andy
- Change "Use GRN" popup information message.

2/29/2012 4:11:49 PM Alex
- change use report server

3/1/2012 3:56:07 PM Andy
- Change report to show by selected department.

3/15/2012 10:26:19 AM Andy
- Modify prepare_sql() to pass by branch_id.
- Check maintenance version 119.

1/21/2013 10:47:00 AM Fithri
- radio button to show by selling price (sales amount)

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

8/27/2013 2:04 PM Justin
- Enhanced to follow sequence by price type, sku item code and mcode.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)
*/
include("include/common.php");

$maintenance->check(119);
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', "Daily Vendor Sales Report");
$_PRINTTITLE = "Daily Vendor Sales Report";
$_SRTITLE = 'Vendor';
$_SRTABLE = 'vendor';
$_SRTABLE_ID = 'vendor_id';

include("sales_report.generic.php");

function get_print_title($cat)
{
	global $con;
	$title = '';

	$con->sql_query("select description from vendor where id = ".intval($_REQUEST['vendor_id']));
	$b = $con->sql_fetchrow();
	$title .= 'Vendor : ' . $b['description'];

 	$title .= $cat;
 	return $title;
}

function prepare_sql($bid)
{
    global $_SRTITLE, $_SRTABLE, $_SRTABLE_ID, $sessioninfo,$con, $LANG,$branch_group, $all_sku_items, $sku_id_list, $sku_info,$config;

	$con_multi= new mysql_multi();

	//$end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($_REQUEST['from']))));
	//if(strtotime($_REQUEST['to'])>strtotime($end_date)) $_REQUEST['to'] = $end_date;
	if(strtotime($_REQUEST['from'])>strtotime($_REQUEST['to'])){
		print "Date to cannot early than date from";
		return false;
		//$_REQUEST['to'] = date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($_REQUEST['from']))));
	}
	
	if($_REQUEST['vendor_id']){
		if($_REQUEST['GRN'])
		{
			// find items that we receive by
			/*$vid = intval($_REQUEST['vendor_id']);
			$dstart = ms($_REQUEST['from']);
			if (!$bid) { print $LANG['REPORT_NO_BRANCH_SELECTED']; return false; }
			if (!strtotime($_REQUEST['to'])) { print $LANG['REPORT_NO_START_DATE_SELECTED']; return false; }
	
			if(isset($_REQUEST['sku_type']) && $_REQUEST['sku_type'] != ''){
				$where[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
			}if(isset($_REQUEST['price_type']) && $_REQUEST['price_type'] != '' && $_REQUEST['price_type'] != 'All'){
				$sub_query = ", ifnull((select trade_discount_code 
										from sku_items_price_history siph 
										where
										siph.sku_item_id = sku_items.id 
										and siph.added between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to'])."
										and siph.branch_id = ".mi($bid)."
										order by added desc limit 1)
										, sku.default_trade_discount_code) as price_type";
			
				$having = "having price_type = ".ms($_REQUEST['price_type']);
			}if(isset($_REQUEST['department_id']) && $_REQUEST['department_id'] != ''){
				$where[] = "cc.p2 = ".mi($_REQUEST['department_id']);
			}
			
			$filter_vsh = $where;
			$where = $where ? join(" and ", $where) : 1;*/
			
			// select those sku of this grn vendor between this date
			/*$vsh_filter = array();
			$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
			$vsh_filter[] = "vsh.added between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']);
			$vsh_filter[] = "vsh.vendor_id=".mi($vid);
			$vsh_filter = join(' and ', $vsh_filter);
			
			$sql = "select distinct(sku_item_id) as sid
			from vendor_sku_history vsh 
			left join sku_items si on si.id=vsh.sku_item_id
			left join sku on si.sku_id = sku.id
			left join category_cache cc on cc.category_id=sku.category_id
			where $where and $vsh_filter";
			$con_multi->sql_query($sql) or die(mysql_error());
			$grn_sid_list = array();
			while($r = $con_multi->sql_fetchassoc()){
				$grn_sid_list[] = mi($r['sid']);
			}
			$con_multi->sql_freeresult();*/
					
			// get all the selected items
			/*$sales_tbl = 'sku_items_sales_cache_b'.$bid;
			
			$filter_vsh[] = "pos.date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']);
			$filter_vsh[] = "vsh.vendor_id=$vid";
			$filter_vsh = join(' and ', $filter_vsh);
			
			$items = array();*/
			/*$sql = "select si.id as sku_item_id, (select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=si.id and vsh.branch_id=$bid and vsh.added <= ".ms($_REQUEST['from'])." order by vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id
			from $sales_tbl pos
			join sku_items si on si.id=pos.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=c.id
			where $filter_vsh
			group by sku_item_id";*/
			
			/*$sql = "select si.id as sku_item_id
			from $sales_tbl pos
			join sku_items si on si.id=pos.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join category_cache cc on cc.category_id=c.id
			join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=si.id and (".ms($_REQUEST['from'])." between vsh.from_date and vsh.to_date or ".ms($_REQUEST['to'])." between vsh.from_date and vsh.to_date or vsh.from_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']).")
			where $filter_vsh
			group by sku_item_id";
			
			$q_vsh_sales = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q_vsh_sales)){
				if(($r['last_grn_vendor_id'] != $vid) && !in_array($r['sku_item_id'], $grn_sid_list)){
					if(!$config['use_grn_last_vendor_include_master']){
						continue;
					}elseif($r['master_vendor_id'] != $vid){
						continue;
					}
				}
						
				$items[$r['sku_item_id']] = 1;
			}
			$con_multi->sql_freeresult($q_vsh_sales);
			$con_multi->close_connection();*/
			// find items that we receive by - Justin
			/*$sql = $con->sql_query("select distinct(vsh.sku_item_id) as sku_item_id, sku_items.sku_item_code, 
									sku_items.description $sub_query
									from vendor_sku_history vsh 
									left join sku_items on sku_items.id = vsh.sku_item_id
									left join sku on sku.id = sku_items.sku_id
									left join category c on c.id = sku.category_id
									left join category_cache cc on cc.category_id=sku.category_id
									where vsh.branch_id = ".mi($bid)." and vsh.vendor_id = ".mi($vid)." and vsh.added < ".ms($_REQUEST['to'])." $where
									$having 
									order by sku_items.sku_item_code");
	
			if ($con->sql_numrows($sql)<=0){
				print $LANG['REPORT_NO_ITEMS_FOR_THIS_VENDOR'];
				return false;
			}
	
			while($r=$con->sql_fetchrow($sql)){
				$sql1 = $con->sql_query("select * from vendor_sku_history vsh where vsh.branch_id = ".mi($bid)." and vsh.added < ".ms($_REQUEST['to'])." and sku_item_id = ".mi($r[0])." order by added desc limit 1");
				
				if($con->sql_numrows($sql1) > 0){
					$tmp_vsh = $con->sql_fetchrow($sql1);
					if($tmp_vsh['vendor_id'] != $vid) continue;
				}else continue;
				//if ($items[$r[0]]) continue;
				$items[$r[0]] = 1;
				//print "<li> $r[0] $r[1]";
			}*/
			/*if(!$items){
				print $LANG['REPORT_NO_ITEMS_FOR_THIS_VENDOR'];
				return false;
			}
			$filter[] = "si.id in (".join(",", array_keys($items)).")";
			$filter2[] = "si.id in (".join(",", array_keys($items)).")";*/
			
			$filter[] = "vsh.vendor_id=".intval($_REQUEST['vendor_id']);
		}else{
			$filter[] = "sku.vendor_id = ".intval($_REQUEST['vendor_id']);
			$filter2[] = "sku.vendor_id = ".intval($_REQUEST['vendor_id']);
		}
	}
	

	$filter[] = "pos.branch_id=".mi($bid);
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
				$ids[] = $branch_id;
			}
			else
			{
			    // if it is all branch, no filter.
			    // but currently there r no selection for all branches
			}
		}
	    /////////////////////////////////////////////////////////////
	}
	else $filter[] = "pos.branch_id = ".intval($sessioninfo['branch_id']);*/

	if (isset($_REQUEST['from'])) $filter[] = "pos.date >= ".ms($_REQUEST['from']);
	if (isset($_REQUEST['to'])) $filter[] = "pos.date < date_add(".ms($_REQUEST['to']).",interval 1 day)";
	$filter[] = "pos.cancel_status=0 and pf.finalized=1";
	
	if($_REQUEST['sku_type']!="")
	{
     	$filter[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
		$filter2[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);
	}

	if ($_REQUEST['department_id']!=''){
        $filter[] = "category.department_id = ".intval($_REQUEST['department_id']);
        $filter2[] = "category.department_id = ".intval($_REQUEST['department_id']);
	}else{
        $filter[] = "category.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
        $filter2[] = "category.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
	}

	if ($_REQUEST['price_type']!='All'){
        $filter[] = "pi.trade_discount_code = ".ms($_REQUEST['price_type']);
	}

	if ($filter) $filter = join(' and ', $filter);

	// if show all
	if($_REQUEST['show_all']){
	    if($filter2)    $filter2 = ' where '.join(' and ',$filter2);
	    if($having)     $having = ' having '.join(' and ',$having);
		if ($ids){
			foreach($ids as $bid){
	            $sql = "select si.*,sku.sku_type
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category on  category.id=sku.category_id
				$filter2 
				order by si.sku_item_code, si.mcode
				$having";
				$q_1 = $con->sql_query($sql) or die(mysql_error());
				while($r = $con->sql_fetchrow($q_1)){
	                $all_sku_items[$r['id']] = $r;
	                $sku_info[$r['sku_item_code']] = $r;
	                $sku_id_list[$r['sku_id']] = $r['sku_id'];
				}
				$con->sql_freeresult($q_1);
			}
		}
	}

	return $filter;
}

function prepare_form()
{
	global $con, $sessioninfo, $_SRTITLE, $_SRTABLE, $_SRTABLE_ID,$branch_group,$LANG, $config;

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

function check_use_grn(){
	var allow_use_grn = true;
	if(document.f['branch_id']){
		if(document.f['branch_id'].value.indexOf('bg')>=0 || !document.f['branch_id'].value)	allow_use_grn = false;
	}
	if(document.f['show_all'].checked)	allow_use_grn = false;
	
	if(allow_use_grn){
		$('GRN_id').disabled = false;
	}else{
		$('GRN_id').disabled = true;
		$('GRN_id').checked = false;
	}
	
	document.f['show_all'].disabled = (document.f['GRN'].checked);
	
	if(document.f['show_all'].disabled){
		document.f['show_all'].checked = false;
	}
}

function check_form(){
	/*if(document.f['department_id'].value<=0){
		alert('Please select department.');
		return false;
	}*/
	return true;
}

function show_all_sku_changed(){
	document.f['GRN'].disabled = (document.f['show_all'].checked);
	
	if(document.f['GRN'].disabled){
		document.f['GRN'].checked = false;
	}
}
</script>

<h1>Daily <?=$_SRTITLE?> Sales Report</h1>
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
<p>
<form name=f class="stdframe" style="background:#fff" onSubmit="return check_form();" method="post">
<input type=hidden name=load value=1>
<input type=hidden name=print value=0>
<input type=hidden name=excelxml value=0>
<input type=hidden name=root_id value="<?=$_REQUEST['root_id']?>">
<?
	if (BRANCH_CODE=='HQ'){
	
		/*$con->sql_query("select id as value, branch.code as title from branch where code <> 'HQ' order by id");
		print "Branch  ";
		sel($con->sql_fetchrowset(), "branch_id", false);
		print "&nbsp;&nbsp; ";*/
		$con->sql_query("select * from branch order by sequence,code");
		print "<b>Branch</b>  ";
		print "<select name=branch_id onChange='check_use_grn();'>";
		print "<option value=''>-- All --</option>";
		while($r = $con->sql_fetchrow()){
			if ($config['sales_report_branches_exclude']) {
				$branch_code = $r['code'];
				if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
					// print "$branch_code skipped23<br />";
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
							// print "$branch_code skipped56<br />";
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

	$filter = ($sessioninfo['vendors']) ? "where id in (".join(",",array_keys($sessioninfo['vendors'])).")" : "";
	$con->sql_query("select id as value, description as title from $_SRTABLE $filter order by title");

	print "<b>$_SRTITLE</b>  ";
	sel($con->sql_fetchrowset(), "$_SRTABLE_ID", false);

	//$con->sql_query("select distinct(sku_type) from sku");
	$con->sql_query("select * from sku_type");
	print "<br /><br /><b>SKU Type</b>  ";
	sel($con->sql_fetchrowset(), "sku_type", true);

	$con->sql_query("select code as price_type from trade_discount_type order by code");
	print "&nbsp;&nbsp; <b>Price Type</b>  ";
	sel($con->sql_fetchrowset(), "price_type", true, 'All');

	$con->sql_query("select id as value, description as title from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by 2");
	print "&nbsp;&nbsp; <b>Department</b>  ";
	sel($con->sql_fetchrowset(), "department_id", true);
?>
<br /><br />
<!-- Date From -->
<b>Date From</b> <input type="text" name="from" value="<?=$_REQUEST['from']?>" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp;

<!-- Date To --> 
<b>To</b> <input type="text" name="to" value="<?=$_REQUEST['to']?>" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>&nbsp;&nbsp;

<!-- Group Monthly -->
<input type=checkbox name=by_monthly id="by_monthly_id" <?=$_REQUEST['by_monthly']?"checked":""?>> <label for="by_monthly_id"><b>Group Monthly</b></label>&nbsp;&nbsp; 

<!-- Use GRN -->
<input type=checkbox <?=$_REQUEST['GRN']?"checked":""?> name=GRN id="GRN_id" onChange="check_use_grn();"> <label for="GRN_id"><b>Use GRN</b></label> [<a href="javascript:void(0)" onclick="alert('<? print jsstring($LANG['USE_GRN_INFO']);?>')">?</a>]&nbsp;&nbsp; 

<!-- Show All Items -->
<input type="checkbox" name="show_all" id="show_all_id" <?=$_REQUEST['show_all']?"checked":""?> onChange="show_all_sku_changed();" /> <label for="show_all_id"><b>Show All Items</b></label>&nbsp;&nbsp; 

<!-- Group by SKU -->
<input type="checkbox" name="group_by_sku" id="group_by_sku_id" <?=$_REQUEST['group_by_sku']?"checked":""?> /> <label for="group_by_sku_id"><b>Group by SKU</b></label>&nbsp;&nbsp; 

<label><input type="checkbox" name="exclude_inactive_sku" value="1" <?=$_REQUEST['exclude_inactive_sku']?"checked":""?> /><b>Exclude inactive SKU</b></label>

<br />

<b>Report Type: </b>
<label><input name="report_type" type="radio" value="qty" <?=($_REQUEST['report_type'] != 'amt')?"checked":""?> />Sales Qty</label>
<label><input name="report_type" type="radio" value="amt" <?=($_REQUEST['report_type'] == 'amt')?"checked":""?> />Sales Amount</label>
&nbsp;&nbsp;&nbsp;

<!-- Print -->
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

<script>
show_all_sku_changed();
</script>
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
	check_use_grn();

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
