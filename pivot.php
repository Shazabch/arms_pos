<?php
/*
REVISION HISTORY
================
11/6/2007 4:44:48 PM gary
- get markon's cost price from grn_cost in pos_transaction

11/7/2007 11:49:25 AM gary
- add average_cost column.

12/7/2007 2:36:04 PM gary
- modify the cost price get from pos_transaction grn_cost.

1/3/2008 6:08:32 PM yinsee
- add brand group , counter id, transaction id

3/24/2008 11:15:37 AM yinsee
- allow $sessioninfo['level']=450 to view all department items (include uncat)

6/15/2010 1:13:01 PM Andy
- Add POS Data latest date notification.

7/12/2010 2:38:42 PM Andy
- Remove POS Data latest date notification.
- Convert pivot report to use pos & pos_items.
- Those field and data related to cost are not suppport now.

7/13/2010 1:11:51 PM yinsee
- fix wrong calculation for discount %
- add selling_price column

7/13/2010 4:49:14 PM Andy
- Add filter cancelled receipt and finalized pos.

1/24/2011 11:36:05 AM Andy
- Optimize report dropdown pre-loading speed.(year, month, day, hour, sales type, price type and race)
- Add checking & ignore those field which not suitable to load for dropdown like arms code, timestamp. and also add a notice for user once report found those field in page filter.

6/15/2011 12:29:12 PM  Justin
- Added mcode field.

3/2/2012 3:15:43 PM Justin
- Added new report title date from and to.

12/27/2012 1:25 PM yinsee
- Add category (level 3).

4/29/2013 10:21 AM Andy
- Change to only allow user id =1 to add/edit/delete pivot report.

12/31/2020 3:54 PM Andy
- Fixed show report doesn't work with Firefox 84.0
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

include("include/excelwriter.php");

set_time_limit(0);
ini_set('memory_limit', '256M');

/*$PIVOT_FIELDS = array(
	"Timestamp" => "pos_transaction.timestamp",
	"Year" => "pos_transaction.year",
	"Month" => "pos_transaction.month",
	"Day" => "pos_transaction.day",
	"Hour" => "pos_transaction.hour",
	"ARMS_Code" => "pos_transaction.sku_item_code",
	"Qty" => "pos_transaction.qty",
	//"Selling_Price" => "round((pos_transaction.amount+pos_transaction.disc_amt)/pos_transaction.qty,2)",
	"Amount" => "pos_transaction.amount",
	"Unit_Price" => "round(pos_transaction.amount/pos_transaction.qty,2)",
	//"Unit_Cost" => "round(sku_items.cost_price,3)",	
	"Unit_Cost" => "round(pos_transaction.grn_cost,3)",	
	//"Cost" => "round(sku_items.cost_price*pos_transaction.qty,3)",	
	"Cost" => "round(pos_transaction.grn_cost*abs(pos_transaction.qty),3)",
	"MarkOn" => "pos_transaction.amount-(abs(pos_transaction.grn_cost)*pos_transaction.qty)",
	//"MarkOn_Pct" => "concat(round(100*SUM(pos_transaction.amount-(sku_items.cost_price*pos_transaction.qty))/SUM(pos_transaction.amount),2),'%')",
	"MarkOn_Pct" => "concat(round(100*SUM(pos_transaction.amount-(abs(pos_transaction.grn_cost)*pos_transaction.qty))/SUM(pos_transaction.amount),2),'%')",	
	"Discount" => "if(pos_transaction.discount=0,'Normal',concat(pos_transaction.discount,'%'))",
	"Discount_Amount" => "pos_transaction.disc_amt",
	"Price_Type" => "pos_transaction.price_type", //if (pos_transaction.price_type<>'',pos_transaction.price_type,sku.default_trade_discount_code)",
	"Sales_Type" => "if(pos_transaction.sales_type='A','Member','Non-member')",
	"Race" => "pos_transaction.race",
	"PWP_Type" => "pos_transaction.pwp",
	"SKU_Type" => "sku.sku_type",
	"Department" => "dept.description",
	"SKU" => "sku_items.description",
	"Branch" => "branch.code",
	"Branch_Name" => "branch.description",
	"Brand" => "if(brand.description is null,'UNBRANDED',brand.description)",
	"Brand_Group" => "brgroup.description",
	"Vendor" => "vendor.description",
	"Article_No" => "sku_items.artno",
//	"Average_Cost" => "pos_transaction.avg_cost",
	"Card_No" => "pos_transaction.card_no",
	"Counter_No" => "pos_transaction.counter_id",
	"Transaction_No" => "pos_transaction.transaction_id"
);*/

$PIVOT_FIELDS = array(
	"Timestamp" => "pos.pos_time",
	"Year" => "year(pos.date)",
	"Month" => "month(pos.date)",
	"Day" => "day(pos.date)",
	"Hour" => "hour(pos.pos_time)",
	"ARMS_Code" => "sku_items.sku_item_code",
	"Qty" => "pos_items.qty",
	"Amount" => "pos_items.price-pos_items.discount",
	"Unit_Price" => "round((pos_items.price-pos_items.discount)/pos_items.qty,2)",
	"Selling_Price"=>"round(pos_items.price/pos_items.qty,2)",
	"Discount" => "if(pos_items.discount=0,'Normal',concat(pos_items.discount/pos_items.price*100,'%'))",
	"Discount_Amount" => "pos_items.discount",
	"Price_Type" => "pos_items.trade_discount_code",
	"Sales_Type" => "if(pos.member_no<>'','Member','Non-member')",
	"Race" => "pos.race",
	"SKU_Type" => "sku.sku_type",
	"Department" => "dept.description",
	"Category" => "cat3.description",
	"SKU" => "sku_items.description",
	"Branch" => "branch.code",
	"Branch_Name" => "branch.description",
	"Brand" => "if(brand.description is null,'UNBRANDED',brand.description)",
	"Brand_Group" => "brgroup.description",
	"Vendor" => "vendor.description",
	"Article_No" => "sku_items.artno",
	"Card_No" => "pos.member_no",
	"Counter_No" => "counter_settings.network_name",
	"Transaction_No" => "pos.id",
	"MCode" => "sku_items.mcode"
);

$ALLOWED_PG_FIELDS = array(
	"Year", "Month", "Day", "Price_Type", "Sales_Type", "Race", "SKU_Type", "Department", "Branch", "Brand", "Vendor", "Counter_No"
);

$PIVOT_ENTRY_FIELDS = array("ARMS_Code", "Article_No");

$FIELD_DROPDOWN_SQL = array(
	"SKU_Type" => "select code as sku_type from sku_type",
	"Department" => "select description from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by 1",
	"Category" => "select description from category where level=3 and root_id in (".join(",",array_keys($sessioninfo['departments'])).") order by 1",
	"Brand" => "select description from brand order by 1",
	"Vendor" => "select description from vendor order by 1",
	"Branch" => "select code from branch order by 1",
	"Branch_Name" => "select description from branch order by 1",
	"Brand_Group" => "select description from brgroup order by 1",
	"Price_Type" => "select code from trade_discount_type order by 1",
	"Counter_No" => "select distinct network_name from counter_settings order by 1"
);

// link the only required table
function link_tables($datas)
{
	global $TABLE_JOIN;

	// dont break line for this sql
	$TABLE_JOIN = "pos left join pos_items on pos_items.branch_id=pos.branch_id and pos_items.date=pos.date and pos_items.counter_id=pos.counter_id and pos_items.pos_id=pos.id left join pos_finalized on pos_finalized.branch_id=pos.branch_id and pos_finalized.date=pos.date";
	
	if (strstr($datas, "sku.") || strstr($datas, "brand.") || strstr($datas,"brgroup.") || strstr($datas, "vendor.") || strstr($datas,"category.") || strstr($datas, "dept.") || strstr($datas, "cat3.")) 
		$TABLE_JOIN .= " left join sku_items on sku_items.id=pos_items.sku_item_id left join sku on sku_items.sku_id = sku.id";
	else if (strstr($datas, "sku_items."))
		$TABLE_JOIN .= " left join sku_items on sku_items.id=pos_items.sku_item_id";
	
	if (strstr($datas,"cat3.")) $TABLE_JOIN .= " left join category_cache using (category_id) left join category cat3 on category_cache.p3 = cat3.id ";
	
	if (strstr($datas,"category.") || strstr($datas,"dept.")) $TABLE_JOIN .= " left join category on sku.category_id = category.id ";
	
	if (strstr($datas,"dept.")) $TABLE_JOIN .= " left join category dept on category.department_id = dept.id";
	
	if (strstr($datas,"branch.")) $TABLE_JOIN .= " left join branch on pos.branch_id = branch.id";
	
	if (strstr($datas,"vendor.")) $TABLE_JOIN .= " left join vendor on sku.vendor_id = vendor.id";
	
	if (strstr($datas,"brand.")) 
		$TABLE_JOIN .= " left join brand on sku.brand_id = brand.id";

	if (strstr($datas,"brgroup.")) 
		$TABLE_JOIN .= " left join brand_brgroup using (brand_id) left join brgroup on brgroup_id = brgroup.id";
    if (strstr($datas,"counter_settings."))
		$TABLE_JOIN .= " left join counter_settings on counter_settings.branch_id=pos.branch_id and counter_settings.id=pos.counter_id";
}

//	check_login();

	$smarty->assign("PAGE_TITLE", "Pivot Table");
    $graph_function = '';
	$fout = fopen("php://output","w");

	switch ($_REQUEST['a'])
	{
	    case 'excelxml':
			$file=str_replace("\\", "/", tempnam($_SERVER['DOCUMENT_ROOT']."tmp","tmp"));
			$excel=new ExcelWriter($file);
   			if($excel==false) die($excel->error);
		    $fout = $excel->fp;
		    fwrite($fout, "<h1>".get_table_title(intval($_REQUEST['id']))."</h1>");
		    load_pivot(false,false,true);
		    $excel->close();
			echo "<SCRIPT>document.location='getxml.php?f=$file';</SCRIPT>";
			/*Header('Content-Type: application/msexcel');
			Header('Content-Length: '.filesize($file));
			Header('Content-Disposition: attachment;filename=pivot'.time().'.xls');
			readfile($file);
			//Remove file
			unlink($file);*/
			exit;
	    case 'load' :
	        $title = get_table_title(intval($_REQUEST['id']));
	    	$smarty->assign("PAGE_TITLE", $title);
	    	$smarty->display("header.tpl");
			print "<h1>$title</h1>";
	        load_pivot();
			$smarty->display('footer.tpl');
		    exit;

 	    case 'delete' :
	        if (BRANCH_CODE != 'HQ' || $sessioninfo['id']!=1)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
			$con->sql_query("delete from pivot_table where id = ".intval($_REQUEST['id']));
		case '' :
	    case 'new' :
	        if (BRANCH_CODE != 'HQ' || $sessioninfo['id']!=1)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
			new_pivot();
	        exit;
		case 'edit':
		    if (BRANCH_CODE != 'HQ' || $sessioninfo['id']!=1)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
			new_pivot(intval($_REQUEST['id']));
	        exit;

		default:
	        print "<h1>Unhandled Request</h1>";
			print_r($_REQUEST);
			exit;
	}
	exit;

	function join_as($sep, $cols)
	{
		global $PIVOT_FIELDS;

		if (!$cols) return '';
		$ret = array();
		foreach($cols as $col)
		{
		    $ret[] = "$PIVOT_FIELDS[$col] as $col";
		}
		return join($sep, $ret);
	}
	
	function join_fas($sep, $cols)
	{
		global $PIVOT_FIELDS;

		if (!$cols) return '';
		$ret = array();
		foreach($cols as $col)
		{
		    if (isset($PIVOT_FIELDS[$col]))
			{
				$ret[] = "$PIVOT_FIELDS[$col] as $col";
		    }
			else	
		    {
				preg_match("/^(.*)\(([^()]+)\)$/", $col, $matches);
		    	$ret[] = "$matches[1](".$PIVOT_FIELDS[$matches[2]].") as $matches[2]";
		    }
		}
		return join($sep, $ret);
	}
	function get_field_dropdown_sql($f)
 	{
 	    global $FIELD_DROPDOWN_SQL, $PIVOT_FIELDS, $TABLE_JOIN, $ALLOWED_PG_FIELDS, $con;
 	    if(!in_array($f, $ALLOWED_PG_FIELDS))   return false;   // invalid page filter
 	    
 	    // use custom SQL
		if (isset($FIELD_DROPDOWN_SQL[$f]))
			return $FIELD_DROPDOWN_SQL[$f];

		switch(strtolower($f)){
			case 'year':
			    // get max pos year
			    $con->sql_query("select max(date) as max_d,min(date) as min_d from pos");
			    $r = $con->sql_fetchrow();
			    $max_d = mi(date("Y", strtotime($r['max_d'])));
			    $min_d = mi(date("Y", strtotime($r['min_d'])));
			    $ret = array();
			    while($min_d<=$max_d){
			        $ret[] = array($min_d);
                    $min_d++;
				}
				return $ret;
			    break;
			case 'month':
			    $ret = array();
			    for($i=1; $i <= 12; $i++){
					$ret[] = array($i);
				}
			    return $ret;
			    break;
			case 'day':
			    $ret = array();
			    for($i=1; $i <= 31; $i++){
					$ret[] = array($i);
				}
			    return $ret;
			    break;
			case 'hour':
			    $ret = array();
			    for($i=0; $i <= 23; $i++){
					$ret[] = array($i);
				}
			    return $ret;
			    break;
            case 'sales_type':
			    return array(array('Member'), array('Non-member'));
			    break;
			case 'race':
			    return array(array(''), array('B'), array('C'), array('I'), array('M'), array('O'));
			    break;
		}
		
		link_tables($PIVOT_FIELDS[$f]);
		return "select distinct $PIVOT_FIELDS[$f] as $f from $TABLE_JOIN order by 1";
	}
	
	function load_pivot($preview = false, $temp_form = false, $excel = false)
	{
	   	global $con, $config, $sessioninfo, $form, $PIVOT_FIELDS, $TABLE_JOIN, $LANG, $PIVOT_ENTRY_FIELDS;
		global $fout, $ALLOWED_PG_FIELDS;


	    if ($preview)
		{
		    $form = $temp_form;
		    //$con->sql_query("drop table if exists tmp_pivot_preview");
			//$con->sql_query("create temporary table if not exists tmp_pivot_preview select * from pos_transaction limit 0,5000");
			$con->sql_query("create temporary table if not exists tmp_pos_preview select * from pos limit 1000");
			$con->sql_query("create temporary table if not exists tmp_pos_items_preview (select pos_items.*
			from tmp_pos_preview
			left join pos_items on pos_items.branch_id=tmp_pos_preview.branch_id and pos_items.date=tmp_pos_preview.date and pos_items.counter_id=tmp_pos_preview.counter_id and pos_items.pos_id=tmp_pos_preview.id)");
		}
		else
		{
			//$con->sql_query("select max(timestamp) from pos_transaction");
			//$max_pos_time = $con->sql_fetchfield(0);
			$id = intval($_REQUEST['id']);
		    $con->sql_query("select * from pivot_table where id = $id");
	        $form = $con->sql_fetchrow();
		    $form['l_pg'] = unserialize($form['page']);
		    $form['l_rows'] = unserialize($form['rows']);
		    $form['l_cols'] = unserialize($form['cols']);
		    $form['l_data'] = unserialize($form['data']);
		    
		    // check privilege
		   	if (!privilege('PIVOT_'.strtoupper($form['rpt_group']))) 
		   	{
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PIVOT_'.strtoupper($form['rpt_group']), BRANCH_CODE), "/index.php");
			}
		}

	    if (!$form['l_rows'] || !$form['l_cols'] || !$form['l_data'])
	    {
	        print "<h5>Error: Pivot is incomplete.</h5>";
	        return;
		}
		// check those column already cannot be use (Unit_Cost, Cost, MarkOn, MarkOn_Pct, PWP_Type)
		$expired_cols = array('Unit_Cost','Cost', 'MarkOn', 'MarkOn_Pct', 'PWP_Type');
		foreach(array('l_rows'=>'Rows', 'l_cols'=>'Column', 'l_data'=>'Data') as $type=>$type_name){
            if($form[$type]){
				foreach($form[$type] as $col){
					foreach($expired_cols as $exp_col){
						if(strpos($col, $exp_col)!==false){
	                        print "<h5>$type_name Error: '$col' already not support, pleaser remove it from your pivot report.</h5>";
		        			return;
						}
					}
				}
			}
		}
		


		// process page filters
		$pg_filter = '';
		
		$pf = array();
		if (BRANCH_CODE != 'HQ') $pf[] = "pos.branch_id = $sessioninfo[branch_id]";
		if ($_REQUEST['from']>0) $pf[] = "pos.pos_time >= ".ms($_REQUEST['from']);
		if ($_REQUEST['to']>0) $pf[] = "pos.pos_time < date_add(".ms($_REQUEST['to']).", interval 1 day)";

		if(!$preview){  // real data need to filter cancel status and pos finalized
			$pf[] = "pos.cancel_status=0 and pos_finalized.finalized=1";
		}
		
		if ($sessioninfo['level']!=450 && $sessioninfo['level']!=500 && $sessioninfo['level']!=9999) $pf[] = "dept.id in (".join(",",array_keys($sessioninfo['departments'])).")";
 
		if ($form['l_pg'] || $_REQUEST['category_id']>0)
		{
	        fwrite($fout, "<h3>");
	        if ($_REQUEST['from'] && $_REQUEST['to']){
	            fwrite($fout, "Date: From $_REQUEST[from] To $_REQUEST[to] &nbsp; ");
			}
	        if ($form['l_pg'])
			{
				foreach($form['l_pg'] as $pg)
		        {
		            /// skip branch option if not in HQ
					if ($pg == 'Branch' && BRANCH_CODE != 'HQ') continue;
		            
		            // skip imcompatible field
		            if(!in_array($pg, $ALLOWED_PG_FIELDS))  continue;
		            
					if ($_REQUEST['filter'][$pg] != '')
						$pf[] = "$PIVOT_FIELDS[$pg] = " . ms($_REQUEST["filter"][$pg]);

		            fwrite($fout, "$pg: " .
		  				(($_REQUEST['filter'][$pg] != '') ? $_REQUEST['filter'][$pg] : "All")
					    .  " &nbsp; ");
				}
			}
	        if ($_REQUEST['category_id']>0)
	        {
	            $cid = intval($_REQUEST['category_id']);
	            $pf[] = "(sku.category_id = $cid or category.tree_str like '%($cid)%')";
	            fwrite($fout, "Category: $_REQUEST[category_tree] &nbsp; ");
			}
			fwrite($fout, "</h3>");
    	}
    	
		if (!$excel && !$preview && isset($_REQUEST['render'])) print "<p class=noprint><img src=ui/xml.gif align=absmiddle border=0> <a href=\"pivot.php?$_SERVER[QUERY_STRING]&a=excelxml\" target=hf>Export to Excel</a><iframe width=1 height=1 name=hf style='visibility:hidden'></iframe></p>";

        // prepare SQL
        $lrows = join_as(",", $form['l_rows']);
        $lcols = join_as(",", $form['l_cols']);
        $ldata = join_fas(",", $form['l_data']);
        $ldata = str_replace('UNIQUE(', 'COUNT(DISTINCT ', $ldata);
        $groupby = join(",", $form['l_rows']) .",".join(",", $form['l_cols']);
        
		if (!$excel)
	    {
		    print "<p>";
			if (!$preview)
			{
			    if ($_REQUEST['category']=='') $_REQUEST['category'] = 'Enter keyword to search';
				print "<form name=f_a class=noprint style=\"line-height:24px\">";
				print "<input type=hidden name=a value=load>";
				print "<input type=hidden name=render value=1>";
				print "<input type=hidden name=id value=$id>";
				print "
					<p class=stdframe style='background:#fff;'>
					<b>Category</b>
					<input type=radio name=s1 value=0 ".($_REQUEST['s1'] ? "":"checked")." onclick=\"Element.hide('csel');category_id.value=0;\"> All
					<input type=radio name=s1 value=1 ".($_REQUEST['s1'] ? "checked":"")." onclick=\"Element.show('csel');category.focus();\"> Selected
					<span id=csel " . ($_REQUEST['s1']==0? "style='display:none'":"") .  ">
					<input readonly name=category_id size=1 value=\"$_REQUEST[category_id]\">
					<input type=hidden name=category_tree value=\"$_REQUEST[category_tree]\">
					<input id=autocomplete_category name=category value=\"$_REQUEST[category]\" onfocus=this.select() size=50><br />
					<span id=str_cat_tree class=small style=\"color:#00f;margin-left:90px;\">$_REQUEST[category_tree] &nbsp;</span>
					<div id=autocomplete_category_choices class=autocomplete style=\"width:600px !important\"></div>
					</span>
					<br>
					<b>Date From</b> <input name=from value=\"$_REQUEST[from]\" id=added1 size=12 />
					<img align=absmiddle src=ui/calendar.gif id=t_added1 style=\"cursor: pointer;\" title=\"Select Date\"/>
					&nbsp; <b>To</b> <input type=text name=to value=\"$_REQUEST[to]\" id=added2 size=12 />
					<img align=absmiddle src=ui/calendar.gif id=t_added2 style=\"cursor: pointer;\" title=\"Select Date\"/>
					&nbsp;&nbsp;&nbsp;
					<input type=checkbox name=col_header ".(isset($_REQUEST['col_header'])?"checked":"")." <b>Show Column Header</b>
					</p>";
			}
			// show the page filter
			if ($form['l_pg'])
			{
			    // check those field cannot use in page filter
			    $err_field = array();
			    foreach($form['l_pg'] as $pg){
			        
					if(!in_array($pg, $ALLOWED_PG_FIELDS)){
					    $err_field[] = $pg;
					}
				}
				if($err_field){
				    print "<span style='color:red;'>Followings field cannot be use in page filter.</span>";
					print "<ul style='color:red;'>";
					foreach($err_field as $ef){
						print "<li>$ef</li>";
					}
					print "</ul>";
				}
				
				foreach($form['l_pg'] as $pg)
				{
				    // no branch id for non-HQ
					if ($pg == 'Branch' && BRANCH_CODE != 'HQ') continue;
					
					if (in_array($pg,$PIVOT_ENTRY_FIELDS))
					{
					    print "<span class=nowrap><b>$pg</b> <input name=\"filter[$pg]\" value=\"{$_REQUEST['filter'][$pg]}\"></span>";
					}
					else
					{
					    $sql_or_arr = get_field_dropdown_sql($pg);
						if(!$sql_or_arr){
                            continue;
						}
						print "<span class=nowrap><b>$pg</b> <select name=\"filter[$pg]\">";
						if ($config['pivot_dropdown_show_all']) print "<option value=\"\">All</option>";
				    	if ($pg == 'Brand') print "<option value=\"UNBRANDED\">UNBRANDED</option>";

						if(!is_array($sql_or_arr)){
                            $con->sql_query($sql_or_arr);
                            $dropdown_arr = $con->sql_fetchrowset();
						}else{
							$dropdown_arr = $sql_or_arr;
						}
						
					    foreach($dropdown_arr as $r)
					    {
					        //$r[0] = str_replace(" 00:00:00","",$r[0]);
					        print "<option value=\"$r[0]\"";
					        if ($_REQUEST["filter"][$pg] == $r[0])
							{
								print " selected";
							}
							print ">$r[0]</option>";
						}
					    print "</select></span>&nbsp;&nbsp;";
				    }
				}
			}
			
			if (!$preview)
			{
?>
				<style>
				.nowrap { white-space:nowrap;padding-right:10px; }
				</style>

				<script>
				<!--
				function show_child(id)
				{
					// reactivate the auto-completer with child of the category
					setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
				}

				function sel_category(obj,have_child)
				{
					var str = new String(obj.value);
					str.replace('<span class=sh>', '');
					str.replace('</span>', '');
					document.f_a.category_tree.value = str;
					$('str_cat_tree').innerHTML = str;
					obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
				}

				var category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=-1", {
					afterUpdateElement: function (obj,li)
					{
					    this.defaultParams = '';
						var s = li.title.split(',');
						document.f_a.category_id.value = s[0];
						sel_category(obj,s[1]);
					}});
				-->
				
				function init_sortable()
				{
					$('pivot').rows[$('pivot').rows.length-1].className = 'sortbottom';
					$('pivot').className = 'tb sortable';
				    ts_makeSortable($('pivot'));
				    $('can_sort').innerHTML = "<p><font color=blue>This table is sortable. Click on a column header to sort.</font></p>";
				}

				function show_report_clicked(){
					var btn_show_report = $('btn_show_report');
					btn_show_report.disabled = true;
					btn_show_report.value = 'Please wait...';
					document.f_a.submit();
				}
				</script>
				
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
				<input type="button" value="Show Report" id="btn_show_report" onclick="show_report_clicked();">
				</form>
<?
			}

			print "</p>";
		}
		
		if (!$preview && !$excel && !isset($_REQUEST['render'])) return;

		// select all data
		if ($pf) $pg_filter = "where " . join(" and ", $pf);
		link_tables("$ldata $lrows $lcols $pg_filter");
		$sql = "select $ldata, $lrows, $lcols from $TABLE_JOIN $pg_filter group by $groupby";
		
		if ($preview)
		{
			$sql = str_replace("pos.", "tmp_pos_preview.", $sql);
			$sql = str_replace("pos_items.", "tmp_pos_items_preview.", $sql);
			$sql = str_replace("pos ", "tmp_pos_preview ", $sql);
			$sql = str_replace("pos_items ", "tmp_pos_items_preview ", $sql);
			//$con->sql_query("explain tmp_pos_preview");
			//print_r($con->sql_fetchrowset());
			$con_multi = $con;
			$con_multi->sql_query($sql);
		}
		else
		{
		    if($max_pos_time)   print "<p style='color:red;'>* POS Data is until $max_pos_time<br></p>";
			//$con->sql_query($sql);
			$con_multi= new mysql_multi();
			if($con_multi){
				$con_multi->sql_query($sql);		
			}
			$con_multi->close_connection();
		}
		if (!$excel) {
			print "\n<!-- DEBUG SQL: $sql\n";
			print "Total records: " . $con_multi->sql_numrows() . " -->\n";
	    }
	    
	    global $table, $uq_cols, $uq_rows, $row_total;

		// generate the pivot table
		// this part is beyond understanding now....
	    $table = array();
		while ($data = $con_multi->sql_fetchrow())
		{
		    $evstring = '$table';
		    $evstring2 = '$uq_cols';
		    $evstring3 = '';
		    foreach ($form['l_rows'] as $r)
		    {
		        $r = str_replace("'", "\\'", $r);
		    	$evstring .= "[\$data['".$r."']]";
		    	$evstring3 .= "[\$data['".$r."']]";
		    }
		    foreach ($form['l_cols'] as $r)
		    {
		        $r = str_replace("'", "\\'", $r);
		    	$evstring .= "[\$data['".$r."']]";
		    	$evstring2 .= "[\$data['".$r."']]";
		    }
	        $n = 0;
	        $total = array();
		    foreach ($form['l_data'] as $r)
		    {
		        $r = str_replace("'", "\\'", $r);
			    eval($evstring . "['$r'] = \$data[$n];");
			    eval($evstring2 . "['$r'] = 1;");
			    $total[$n] +=$data[$n];
		        $n++;
		    }
		    eval('$uq_rows'.$evstring3 . " = 1;");
		    //eval('$sortvalue[$data[\''.$form['l_rows'][0].'\']] += $total[0];');
		    //for ($i=0;$i<count($form['l_data']);$i++) eval('$uq_rows' .$evstring3 . "[$i] += \$total[$i];");
		    for ($i=0;$i<count($form['l_data']);$i++) eval('$row_total' .$evstring3 . "[$i] += \$total[$i];");
		}

		if (!$uq_cols)
		{
		    fwrite($fout, "<p>- No Data -</p>");
		    return;
		}
		deep_sort($uq_cols,false);
		@reset($uq_cols);

		// print table
		print "<span id=can_sort></span>\n";
		fwrite($fout, "<table id=pivot class=\"tb\" border=0 cellspacing=0 cellpadding=2>");
		fwrite($fout, "<tr>\n");

		$rowspan = count($form['l_cols']);
		if ($_REQUEST['col_header']) $rowspan++ ;
		foreach  ($form['l_rows'] as $r)
		{
			fwrite($fout, "\t<th valign=bottom rowspan=$rowspan>".str_replace("_"," ",($r==''?"&nbsp;":$r))."</th>\n");
		}
		// print column headers
		$loop_cmd = 'fwrite($fout, "\t<th colspan=".get_rowspan($v).">".($k==""?"&nbsp;":$k)."</th>\n");';

		$cmd = '';
		for ($i=0;$i<$rowspan;$i++)
		{
			if ($i>0) fwrite($fout, "<tr>\n");
		    $cmd .= 'foreach ($v as $k=>$v) ';
		 	$v = $uq_cols;
		 	eval($cmd . $loop_cmd);
		 	if ($i==0)
			{
			    for($x=0;$x<count($form['l_data']);$x++) fwrite($fout, "<th valign=bottom rowspan=$rowspan>Total</th>");
			}
		 	fwrite($fout, "</tr>\n");
		}
		fwrite($fout, "<tr>");
		r_row($uq_rows,'',$excel,count($form['l_data']));
		//print "\n<tr class=sortbottom>";
		global $col_total, $grand_total;
		for($i=0;$i<count($form['l_rows'])-1;$i++)  print"<td>&nbsp;</td>";
		fwrite($fout, "<th align=right>TOTAL</th>");
		foreach($col_total as $t)
		{
			fwrite($fout, "<th align=right>".str_replace(".00","",number_format($t,2))."</th>");
		}
		for($x=0;$x<count($form['l_data']);$x++) fwrite($fout, "<th align=right>".str_replace(".00","",number_format($grand_total[$x],2))."</th>");
		fwrite($fout, "</tr>");
		fwrite($fout, "</table>\n");
		if ($rowspan<2 && count($form['l_data'])<1)
		{
			print "<script>init_sortable();</script>\n";
		}
		/*print_r($form['l_cols']);
		print_r($form['l_data']);*/
	}

	function r_row($arr, $str = '',$excel=false, $datacount)
	{
	 	global $table, $uq_cols, $total_n, $grand_total, $row_total;
		global $fout;
		// use rowspan
		if (is_array($arr))
	    {
	        $prev = '';
			foreach ($arr as $k=>$v)
			{
			    $rs = get_rowspan($v);
			    if (!$rs) $rs=1;
				fwrite($fout, "<td valign=top rowspan=$rs nowrap>".($k==''?"&nbsp;":$k)."</td>");
			    r_row($v, $str."['".str_replace("'", "\\'", $k)."']",$excel,$datacount);
			}
		}
		else
		{
			// fill data
			$total_n = 0;
			fill_data($str, $uq_cols, $excel, $datacount);
			for ($x=0;$x<$datacount;$x++)
			{
			    eval('$rt = $row_total'.$str."[$x];");
				fwrite($fout, "<th align=right>".str_replace(".00","",number_format($rt,2))."</th>");
				$grand_total[$x] += $rt;
			}
			fwrite($fout, "</tr>\n<tr>");
		}
	}

	function fill_data($str, $arr, $excel, $datacount)
	{
	    global $table, $form, $fout,$col_total,$total_n,$x;
	    $x=0;
	    foreach ($arr as $k=>$v)
	    {
			if (is_array($v))
			{
			    $k = str_replace("'", "\\'", $k);
			    fill_data($str."['$k']", $v, $excel, $datacount);
			}
			else
			{
			    $r = str_replace("'", "\\'", $k);
				eval("\$tbd = \$table$str"."['$r'];");

				// blank
			    if ($tbd === '' or $tbd == 0)
			    {
				    fwrite($fout, "<td>&nbsp;</td>");
				}
				else
				{
					if (preg_match('/COUNT|MIN|MAX|AVG|VARIANCE|STD|SUM/', $k) && !preg_match('|\d+/\d+/\d+|',$k))
					{
					    fwrite($fout, "<td align=right>".str_replace(".00","",number_format($tbd,2))."</td>");
					}
					else
					{
						fwrite($fout, "<td>$tbd</td>");
					}
				}
				
                $col_total[$total_n] += $tbd;
	            $total_n++;
            }
            $x = ($x+1)%$datacount;
		}
	}

	function get_rowspan($arr)
	{
	    //print "row span of $level";
	    if (!is_array($arr)) return 1;
	    // count the number of items at bottom-most
	    $total = 0;
		foreach ($arr as $k=>$v)
		{
		    $ret = get_rowspan($v);
		    if ($ret == 0) $ret = count($v);
		    $total += $ret;
		}
		return $total;
	}

	function delete_pivot()
	{
	    global $con, $sessioninfo;
		print "The pivot table has been deleted.";
	}

	function get_table_title($id)
	{
		global $con, $sessioninfo;
		$con->sql_query("select title from pivot_table where id=$id");
		if ($r = $con->sql_fetchrow())
		    return $r[0];
		else
		    return "Invalid Table ID";
	}

	function new_pivot($id = 0)
	{
	    global $con, $sessioninfo, $smarty;
	    switch($_REQUEST['sa'])
	    {
	        case 'save' :
				$id = intval($_REQUEST['id']);
				$con->sql_query("replace into pivot_table (id, user_id, title, rpt_group, page, rows, cols, data) values ($id, $sessioninfo[id], ".ms($_REQUEST['title']).",  ".ms($_REQUEST['rpt_group']).", ".ms(serialize($_REQUEST['l_pg'])).", ".ms(serialize($_REQUEST['l_rows'])).", ".ms(serialize($_REQUEST['l_cols'])).", ".ms(serialize($_REQUEST['l_data'])).")");
				print $con->sql_nextid();
				exit;

			case 'preview_table':
			    $form['l_pg'] = $_REQUEST['l_pg'];
			    $form['l_rows'] = $_REQUEST['l_rows'];
			    $form['l_cols'] = $_REQUEST['l_cols'];
			    $form['l_data'] = $_REQUEST['l_data'];
			    load_pivot(true, $form);
	    	    exit;
		}

		if ($id > 0)
		{
		    $con->sql_query("select * from pivot_table where id = $id");
		    $form = $con->sql_fetchrow();
		    $form['l_pg'] = unserialize($form['page']);
		    $form['l_rows'] = unserialize($form['rows']);
		    $form['l_cols'] = unserialize($form['cols']);
		    $form['l_data'] = unserialize($form['data']);
		    $smarty->assign("form", $form);
		    $smarty->assign("title", 'Edit Pivot Table');
		}
		else
		{
		    $smarty->assign("title", 'New Pivot Table');
		}
		/*$con->sql_query("explain data");
		$fields = $con->sql_fetchrowset();
		array_push($fields, array("Field" => "Year"));
		array_push($fields, array("Field" => "Month"));
		array_push($fields, array("Field" => "Week"));*/
		global $PIVOT_FIELDS;
		$smarty->assign("fields", array_keys($PIVOT_FIELDS));
        $smarty->display('new_pivot_table.tpl');
	}
?>
