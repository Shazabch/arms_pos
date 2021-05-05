<?php
/*
2/16/2009 2:40:54 PM yinsee (request by tommy pkt)
- user 9999 can enter with URL 

4/26/2010 3:40:56 PM Andy
- stock check pivot report add sku type filter

3/30/2012 5:56:12 PM Justin
- Added new report title date from and to.

2/17/2020 4:42 PM William
- Enhanced to change $con connection to use $con_multi.

3/6/2020 9:50 AM Andy
- Fixed sometime report listing will not show any data.

12/07/2020 5:34 PM Rayleen
- Add link code in pivot_fields array

12/21/2020 5:59 PM Andy
- Fixed show report doesn't work with Firefox 84.0
*/
include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999 && !privilege('STOCK_CHECK_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");

include("include/excelwriter.php");

// create table stock_check (branch_id int, location char(15), scanned_by char(15), date_scan date,shelf_no char(15),item int, sku_item_code char(12),selling double,qty double,cost double, index(branch_id), index(sku_item_code), index(location), index(shelf_no), department_id int, category_id int, brand_id int, tree_str text, index(department_id), index(category_id), index(brand_id), index(tree_str(25)))

$PIVOT_FIELDS = array(
	"Branch" => "branch.code",
	"Location" => "stock_check.location",
	"Scanned_By" => "stock_check.scanned_by",
	"Date" => "stock_check.date",
	"Shelf_No" => "stock_check.shelf_no",
	"ARMS_Code" => "stock_check.sku_item_code",
	"Selling" => "stock_check.selling",
	"Total_Selling" => "stock_check.qty*stock_check.selling",
	"Qty" => "stock_check.qty",
	"Cost" => "stock_check.cost",
	"Total_Cost" => "stock_check.qty*stock_check.cost",
	"Department" => "dept.description",
	"SKU" => "sku_items.description",
	"Brand" => "if(brand.description is null,'UNBRANDED',brand.description)",
	//"Vendor" => "vendor.description",
	"Article_No" => "sku_items.artno",
	"MCode" => "sku_items.mcode",
	"SKU_Type"=>"sku.sku_type",
	str_replace(' ', '_', $config['link_code_name'])=>"sku_items.link_code",
);

$PIVOT_ENTRY_FIELDS = array("ARMS_Code", "Article_No");


$FIELD_DROPDOWN_SQL = array(
	"SKU_Type" => "select distinct(sku_type) from sku order by 1",
	"Department" => "select description from category where level=2 and id in (".join(",",array_keys($sessioninfo['departments'])).") order by 1",
	"Brand" => "select description from brand order by 1",
	"Vendor" => "select description from vendor order by 1",
	"Branch" => "select code from branch order by 1",
);

// link the only required table
function link_tables($datas)
{
	global $TABLE_JOIN;

	$TABLE_JOIN = "stock_check";
	if (strstr($datas, "sku.") || strstr($datas, "sku_items.") || strstr($datas,"category.") || strstr($datas,"dept.")) $TABLE_JOIN .= " left join sku_items using (sku_item_code) left join sku on sku_items.sku_id = sku.id";
	if (strstr($datas,"category.") || strstr($datas,"dept.")) $TABLE_JOIN .= " left join category on sku.category_id = category.id ";
	if (strstr($datas,"dept.")) $TABLE_JOIN .= " left join category dept on category.department_id = dept.id";
	if (strstr($datas,"branch.")) $TABLE_JOIN .= " left join branch on stock_check.branch_id = branch.id";
	if (strstr($datas,"vendor.")) $TABLE_JOIN .= " left join vendor on sku.vendor_id = vendor.id";
	if (strstr($datas,"brand.")) $TABLE_JOIN .= " left join brand on sku.brand_id = brand.id";
	

 	//print "<li> $datas ".strstr($datas,"branch.")."- $TABLE_JOIN<br /><br />";
}

//	check_login();
	if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
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
		case 'list':
		    list_pivot();
		    exit;
 	    case 'delete' :
	        if (BRANCH_CODE != 'HQ' || $sessioninfo['level']<9999)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
			$con->sql_query("delete from pivot_table_sc where id = ".intval($_REQUEST['id']));
		case '' :
	    case 'new' :
	        if (BRANCH_CODE != 'HQ' || $sessioninfo['level']<9999)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
			new_pivot();
	        exit;
		case 'edit':
		    if (BRANCH_CODE != 'HQ' || $sessioninfo['level']<9999)  js_redirect('Not SUPERUSER or not running from HQ', "/index.php");
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
		    preg_match("/^(.*)\(([^()]+)\)$/", $col, $matches);
		    $ret[] = "$matches[1](".$PIVOT_FIELDS[$matches[2]].") as $matches[2]";
		}
		return join($sep, $ret);
	}
	function get_field_dropdown_sql($f)
 	{
 	    global $FIELD_DROPDOWN_SQL, $PIVOT_FIELDS, $TABLE_JOIN;
 	    // use custom SQL
		if (isset($FIELD_DROPDOWN_SQL[$f]))
			return $FIELD_DROPDOWN_SQL[$f];
		
		link_tables($PIVOT_FIELDS[$f]);
		return "select distinct $PIVOT_FIELDS[$f] as $f from $TABLE_JOIN order by 1";
	}
	
	function load_pivot($preview = false, $temp_form = false, $excel = false)
	{
	   	global $con, $config, $sessioninfo, $form, $PIVOT_FIELDS, $TABLE_JOIN, $LANG;
		global $fout;

	    if ($preview)
		{
		    $form = $temp_form;
		    $limit = "limit 0,50";
		}
		else
		{
		    $limit = '';
			$id = intval($_REQUEST['id']);
		    $con->sql_query("select * from pivot_table_sc where id = $id");
	        $form = $con->sql_fetchrow();
			$con->sql_freeresult();
		    $form['l_pg'] = unserialize($form['page']);
		    $form['l_rows'] = unserialize($form['rows']);
		    $form['l_cols'] = unserialize($form['cols']);
		    $form['l_data'] = unserialize($form['data']);
		    
		    // check privilege
		   	if ($sessioninfo['level']<9999 && !privilege('STOCK_CHECK_REPORT'))
		   	{
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");
			}
		}

	    if (!$form['l_rows'] || !$form['l_cols'] || !$form['l_data'])
	    {
	        print "<h5>Error: Pivot is incomplete.</h5>";
	        return;
		}

		// process page filters
		$pg_filter = '';
		$pf = array('stock_check.qty>0');
		if (BRANCH_CODE != 'HQ') $pf[] = "stock_check.branch_id = $sessioninfo[branch_id]";
		if ($_REQUEST['from']>0) $pf[] = "stock_check.date >= ".ms($_REQUEST['from']);
		if ($_REQUEST['to']>0) $pf[] = "stock_check.date <= ".ms($_REQUEST['to']);
		if ($sessioninfo['level']!=500 && $sessioninfo['level']!=9999) $pf[] = "dept.id in (".join(",",array_keys($sessioninfo['departments'])).")";

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

		if (!$excel && !$preview && isset($_REQUEST['render'])) print "<p class=noprint><img src=ui/xml.gif align=absmiddle border=0> <a href=\"?$_SERVER[QUERY_STRING]&a=excelxml\" target=hf>Export to Excel</a><iframe width=1 height=1 name=hf style='visibility:hidden'></iframe></p>";

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
			global $PIVOT_ENTRY_FIELDS;
			if ($form['l_pg'])
			{
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
						print "<span class=nowrap><b>$pg</b> <select name=\"filter[$pg]\">";
						if ($config['pivot_dropdown_show_all']) print "<option value=\"\">All</option>";
				    	if ($pg == 'Brand') print "<option value=\"UNBRANDED\">UNBRANDED</option>";

						$sql = get_field_dropdown_sql($pg);
						$con->sql_query($sql);
					    while($r=$con->sql_fetchrow())
					    {
					        //$r[0] = str_replace(" 00:00:00","",$r[0]);
					        print "<option value=\"$r[0]\"";
					        if ($_REQUEST["filter"][$pg] == $r[0])
							{
								print " selected";
							}
							print ">$r[0]</option>";
						}
						$con->sql_freeresult();
					    print "</select></span>&nbsp;&nbsp;";
				    }

				}
			}
			if (!$preview)
			{
?>
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
<?php
			}
			print "</p>";
		}

		
		if (!$preview && !$excel && !isset($_REQUEST['render'])) return;

		// select all data
		if ($pf) $pg_filter = "where " . join(" and ", $pf);
		link_tables("$ldata $lrows $lcols $pg_filter");
		$sql = "select $ldata, $lrows, $lcols from $TABLE_JOIN $pg_filter group by $groupby $limit";

		$con->sql_query($sql);
		if (!$excel) {
			print "\n<!-- DEBUG SQL: $sql\n";
			print "Total records: " . $con->sql_numrows() . " -->\n";
	    }
	    
	    global $table, $uq_cols, $uq_rows, $row_total;

		// generate the pivot table
		// this part is beyond understanding now....
	    $table = array();
		while ($data = $con->sql_fetchrow())
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
		$con->sql_freeresult();

		if (!$uq_cols)
		{
		    fwrite($fout, "<p>- No Data -</p>");
		    return;
		}
		deep_sort($uq_cols, false);
		@reset($uq_cols);
		
		// print table
		print "<span id=can_sort></span>\n";
		fwrite($fout, "<table id=pivot class=\"tb\" border=0 cellspacing=0 cellpadding=2>");
		fwrite($fout, "<tr>\n");

		$rowspan = count($form['l_cols']) ;
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
		if ($rowspan<2 && count($form['l_data'])<2)
		{
			print "<script>init_sortable();</script>\n";
		}
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
	    if (!is_array($arr)) return 0;
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
		global $con, $sessioninfo, $con_multi;
		$con_multi->sql_query("select title from pivot_table_sc where id=$id");
		if ($r = $con_multi->sql_fetchrow()){
			$con_multi->sql_freeresult();
		    return $r[0];
		}else
		    return "Invalid Table ID";
	}

	function new_pivot($id = 0)
	{
	    global $con, $sessioninfo, $smarty;

	    switch($_REQUEST['sa'])
	    {
	        case 'save' :
				$id = intval($_REQUEST['id']);
				$con->sql_query("replace into pivot_table_sc (id, user_id, title, rpt_group, page, rows, cols, data) values ($id, $sessioninfo[id], ".ms($_REQUEST['title']).",  ".ms($_REQUEST['rpt_group']).", ".ms(serialize($_REQUEST['l_pg'])).", ".ms(serialize($_REQUEST['l_rows'])).", ".ms(serialize($_REQUEST['l_cols'])).", ".ms(serialize($_REQUEST['l_data'])).")");
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
		    $con->sql_query("select * from pivot_table_sc where id = $id");
		    $form = $con->sql_fetchrow();
			$con->sql_freeresult();
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
		$con->sql_query("select id, title from pivot_table_sc order by title");
		$smarty->assign("pivots", $con->sql_fetchrowset());
		$con->sql_freeresult();
		$smarty->assign("fields", array_keys($PIVOT_FIELDS));
        $smarty->display('new_pivot_table.tpl');
	}

	function list_pivot()
	{
		global $LANG, $con, $smarty, $sessioninfo, $con_multi;

		if ($sessioninfo['level']<9999 && !privilege('STOCK_CHECK_REPORT'))
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_CHECK_REPORT', BRANCH_CODE), "/index.php");


		$smarty->assign("PAGE_TITLE", "Stock Check Report");
		$smarty->display("header.tpl");
		print "<h1>Stock Check Report</h1><ul>";
		if ($sessioninfo['level']>=9999) print "<li> Admin user can <a href=\"?a=\">click here</a> to create more reports.<br />";
		print "<li> Select a report below to view.</ul><ol>";
		
		$con_multi->sql_query("select * from pivot_table_sc order by title");
		while($p = $con_multi->sql_fetchrow())
		{
		    print "<li> <a href=?a=load&id=$p[id]>$p[title]</a>";
		}
		print "</ol>";
		$con_multi->sql_freeresult();
		$smarty->display("footer.tpl");
	}

?>
