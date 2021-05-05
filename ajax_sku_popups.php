<?php
/*
REVISION HISTORY
=================
3/6/2008 12:30:09 PM gary
- change the date(M) to date(m) to get current month value(integer)

11/6/2008 5:25:39 PM andy
- add cost history popup

1/12/2009 5:29:49 PM yinsee
- change comparison of "grn.status" to "grn.status<2"

1/13/2009 1:20:37 PM yinsee
- excel export for inventory history

3/11/2009 1:06:21 PM yinsee
- fix sales-trend month name

4/28/2009 5:29:00 PM Andy
- Show branch description at find inventory DO

11/13/2009 3:09:22 PM Andy
- change grn status from <2 to status=1,active to active=1
- change if($t['grn']||$t['stock_check']) to if(isset($t['grn'])||isset($t['stock_check'])), cuz if zero qty it won't update

12/17/2009 4:51:36 PM Andy
- Fix No GRA Total Bugs

2/3/2010 10:42:54 AM Andy
- Fix Last cost problem at SKU Listing.
- Fix GRN Cost problem

3/10/2010 11:20:49 AM Andy
- add consignment modules enhancement, branch follow HQ cost under consignment modules
- add 3 configs (H2B, B2H, B2B) to manage whether DO need to update cost or not

5/31/2010 2:54:17 PM Andy
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.

6/17/2010 12:42:41 PM Alex
- add ajax_size_color_matrix for adding items in matrix.

7/2/2010 3:21:34 PM Andy
- Fix wrong inventory when showing item stock balance for all branches

7/8/2010 4:41:04 PM Andy
- Show Un-finalized POS in SKU item inventory

7/9/2010 3:39:30 PM Andy
- Fix consignment module show all pos under un-finalized bugs, if consignment module will not have unfinalized pos.

7/27/2010 10:25:08 AM Andy
- Cost calculation change to if grn cost is zero, will not change the latest cost.

8/12/2010 3:36:08 PM Alex
- add branch checking for size_color

8/13/2010 10:02:53 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/19/2010 3:00:50 PM Andy
- Add config control to no inventory sku.

11/4/2010 10:13:05 AM Alex
- fix size color looping bugs
- add total of stock

11/16/2010 2:11:39 PM Alex
- fix show adjustment bugs by add date filter

11/29/2010 10:33:38 AM Andy
- Fix wrong month label for item sales trend.

1/24/2011 12:34:38 PM Alex
- add branch ids checking at show_sales_trend()

1/27/2011 2:49:24 PM Justin
- Added the highlight row feature while viewing promotion history

1/27/2011 3:01:40 PM Alex
- add checking active branch only at show_sales_trend()

3/22/2011 4:43:57 PM Andy
- Fix SKU promotion history get the wrong branch promotion.

6/22/2011 5:26:15 PM Alex
- add checking PO block list only for ajax_size_color_matrix()

6/24/2011 3:22:54 PM Andy
- Make all branch default sort by sequence, code. 

6/28/2011 6:09:11 PM Justin/Andy
- Fixed the wrong calculation for Average Cost.
- Enhanced to have different calculation while with/without GRN future.

8/16/2011 11:40:21 AM Justin
- Added the checking of active=1 for DO and Adjustment.

8/25/2011 4:24:06 PM Andy
- Add checking for user vendor permission when show PO Cost History.

9/5/2011 11:42:12 AM Justin
- Added checking for GRR active=1 when viewing GRN documents.
- Added to use different php file while calling GRN documents.

10/7/2011 4:25:01 PM Andy
- Add can "show parent sales trend".

10/14/2011 5:50:41 PM Andy
- Fix parent sales trend calculation.

10/17/2011 11:08:41 AM Alex
- Modified the Ctn and Pcs round up to base on config set.

11/25/2011 5:00:06 PM Alex
- add show inactive branch

2/3/2012 9:34:23 AM Alex
- fix inactive branch and order branch by sequence and then code

2/29/2012 1:45:15 PM Alex
- change fetchrow to fetchassoc
- fix the cost and qty to use global_cost_decimal_points or global_qty_decimal_point 

3/8/2012 10:51:29 AM Alex
- fix bugs ajax_size_color_matrix when subbranch add matrix in PO

3/21/2012 2:20:21 PM Andy
- Fix bugs on items sales trend diagram.

3/26/2012 5:15:47 PM Andy
- Change sales trend to only show current month + last 11 month.

4/3/2012 11:33:55 AM Alex
- add total all branch stock balance => size_color()

5/8/2012 2:49:43 PM Justin
- Enhanced to show debtor code & description whenever viewing inventory item from DO (credit sales).

6/15/2012 3:23:56 PM Justin
- Added to hide master cost price while current logged on branch was franchise.

8/29/2012 4:18 PM Andy
- Add new AVG cost calculation, only available if the item not fresh market, not consignment module and got config "sku_use_avg_cost_as_last_cost".
- Add new GRN cost calculation, which will affect parent/child. only available if the item not fresh market, not consignment module and got config "sku_update_cost_by_parent_child".

10/15/2012 12:29 PM Andy
- Reduce memory usage for sku_po_history.

12/19/2012 2:55:00 PM Fithri
- sku cost history if use avg cost as last cost, the last cost show at masterfile use grn last cost

2/28/2013 4:02 PM Andy
- Fix get cost sequence to calculate parent and then child, and always process stock take first, then follow by grn and others.

3/25/2013 10:30 AM Andy
- Enhanced new parent/child avg cost calculation.

3/27/2013 5:00 PM Andy
- Enhanced to skip zero qty grn for group calculation.
- Enhanced to check group total pcs before and after grn, if either them are negative will use grn cost as latest avg cost.

4/23/2013 3:59 PM Fithri
- fix link for view grn from sku listing iventory

7/9/2013 4:53 PM Justin
- Enhanced gra script to pickup those waiting for approval items.

7/10/2013 10:18 AM Andy
- Enhance cost calculation to consider got negative qty, so it will only calculate stock balance but won't affect cost.
- Standardize all gra.returned checking.

9/2/2013 5:10 PM Justin
- Bug fixed on inventory for GRN(nA) is inaccurate and missing of date filter.

12/31/2013 3:45 PM Andy
- Change to check finalized=0 for those sales not yet finalized.

5/12/2014 3:00 PM Fithri
- add new config "po_hide_hq_cost_history" that hide HQ cost history when view cost history from sub branches

4/12/2016 2:43 PM Andy
- Fix view inventory by parent will calculate wrong when got stock check.
- Change load all branch inventory to get from sku_items_cost, no more load all details.

5/6/2016 10:24 AM Andy
- Fix all branch inventory dint show when no config for no_inventory_sku.

5/10/2016 2:26 PM Andy
- Fix unfinalized pos no load at branch inventory history.

5/11/2016 2:40 PM Andy
- Fix unfinalize pos qty wrong.

5/20/2016 1:37 PM Andy
- Fix inventory stock check calculation error.

5/23/2016 11:29 AM Andy
- Fix inventory branch calculation error.

5/27/2016 11:12 AM Andy
- Enhanced view all branch inventory to all recently 1 year of data, or data after stock check.

5/30/2016 9:38 AM Andy
- Fix query error when on mysql 5.0.

05/31/2016 10:20 Edwin
- Bug fixed on qty2 calculation error in Item Inventory.

05/31/2016 11:30 Edwin
- Added show full item inventory data when $config.sku_inventory_all_show_full_data is enabled.

9/7/2016 10:35 AM Andy
- Fixed wrong sku description when show sku parent inventory.

4/10/2017 11:17 AM Qiu Ying
- Enhanced to add IBT GRN in Parent SKU Inventory & SKU Item Inventory

7/14/2017 2:12 PM Andy
- Fix cost calculation error if turn on parent and child average cost. (Method A and B)

9/7/2017 5:42 PM Andy
- Change to use grn cost as average cost if found average cost or total pcs is negative.

9/28/2017 3:11 PM Justin
- Enhanced the sales trend formula to calculate by day, not month.
- Enhanced show sales trend on TPL instead of using PHP to print it out.

10/2/2017 12:38 PM Andy
- Fixed if stock qty or total avg cost is negative before grn, take the grn cost to replace avg cost.

10/27/2017 11:23 AM Justin
- Bug fixed on month shows wrongly while building the chart.

11/10/2017 11:52 AM Justin
- Bug fixed on showing wrong figures by month.

12/11/2017 10:33 AM Justin
- Bug fixed on year showing wrongly.

1/12/2018 6:03 PM Andy
- Enhanced cost calculation to check work order.

1/29/2018 1:58 PM Justin
- Bug fixed on showing month wrongly for sales trend when current date is end of January.

1/30/2018 10:01 AM Justin
- Bug fixed on sales trend did not show in full 12 months sales.
- Enhanced to show 12 or 13 months base on sales requirements.
- Enhanced to show day indicator for oldest and latest month.

4/17/2018 3:46 PM Andy
- Added Foreign Currency feature.

8/29/2018 12:14 PM Andy
- Enhanced to calculate grn cost by multiply the grr tax percent.
*/
include("include/common.php");
$maintenance->check(130);
if (!$sessioninfo) die("<ul><li>Session Timeout. Please Login</li></ul>");
$branch_id = intval($_REQUEST['branch_id']);
if ($branch_id ==''){
	$branch_id = $sessioninfo['branch_id'];
}

switch($_REQUEST['a'])
{
	case 'sku_po_history':
	    $id = intval($_REQUEST['id']);
	    $filter = array();
	    $filter[] = "po.approved=1 and po.active=1 and po_items.sku_item_id=$id";
	    if($sessioninfo['vendor_ids']){
			$filter[] = "po.vendor_id in (".$sessioninfo['vendor_ids'].")";
		}
		$filter = "where ".join(' and ', $filter);
	    $con->sql_query("select qty,qty_loose,foc, foc_loose,order_price, po_date, branch.report_prefix, b2.report_prefix as report_prefix2, uom.code as uom, vendor.description as vendor, po_items.selling_price, po_items.tax, po_items.discount , po_items.remark, po_items.remark2, branch.id as br1, b2.id as br2, po.currency_code, po.currency_rate
from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
left join uom on po_items.order_uom_id = uom.id
left join vendor on po.vendor_id = vendor.id
left join branch on po.branch_id = branch.id
left join branch b2 on po.po_branch_id = b2.id
$filter
order by po.po_date desc, po.id desc") or die(mysql_error());
		$po_history = array();
		while($r = $con->sql_fetchassoc()){
			
			if ($config['po_hide_hq_cost_history'] && ($r['br1'] == '1' || $r['br2'] == '1')) {
				continue;
			}
			
			$po_history[] = $r;
		}
		$con->sql_freeresult();
		//echo"<pre>";print_r($po_history);echo"</pre>";
	    $smarty->assign("po_history",$po_history);
		$smarty->display("popup.cost_history.tpl");
	    exit;

	case 'sku_sales_trend':
		// show sales for 1, 3, 6 and 12 months
		show_sales_trend2();
		exit;

	case 'sku_inventory_find':
		// display all related PO, GRN, ADJUSTMENT for the given sku_item
		show_inventory_find();
		exit;

	case 'sku_get_inventory' :
		get_inventory();
		exit;
	case 'sku_cost_history':
	    sku_cost_history();
		 exit;
	case 'promotion_history':
	    promotion_history();
	    exit;
	case 'size_color':
	    size_color();
	    exit;
	case 'ajax_color_size_matrix':
		$id = intval($_REQUEST['id']);
	    ajax_size_color_matrix($id,$branch_id,$_REQUEST['type']);
	    exit;
	default:
		print "<ul>";
        print "<li>Unhandled Request<span class=informal>";
		print_r($_REQUEST);
		print "</span></li>";
        print "</ul>";
        exit;

}

function show_sales_trend()
{
	global $con, $sessioninfo;

	$id = intval($_REQUEST['id']);
	$branch_ids=$_REQUEST['branch_ids'];
	$use_parent = mi($_REQUEST['use_parent']);
	
	if ($branch_ids)    $br_filter=" and branch.id in ($branch_ids)";

	/*if (BRANCH_CODE != 'HQ')
	{
		$branch_filter = " and branch_id = $sessioninfo[branch_id]";
	}*/
	
	$item = array();
	$sid_list = array();
	$con->sql_query("select * from sku_items where id = $id");
	$item = $con->sql_fetchassoc();
	$sid_list[] = mi($item['id']);
	$con->sql_freeresult();
	
	if (!$item || !$sid_list) die("Invalid sku item #$id");

	if($use_parent){	// show by parent
		$con->sql_query("select * from sku_items where sku_id=".mi($item['sku_id']));
		while($r = $con->sql_fetchassoc()){
			$sid = mi($r['id']);
			if(!in_array($sid, $sid_list)){
				$sid_list[] = $sid;
			}
			if($r['is_parent'])	$item = $r; 
		}
		$con->sql_freeresult();
		
		$sum_qty = "sum(sc.qty*uom.fraction)";
	}else{
		$sum_qty = "sum(sc.qty)";
	}
	
	$current_dt = date("Y-m-d");
	$curr_m = date("m");
	$curr_y = date("Y");
	$curr_m -= 11;
	if($curr_m<1){
		$curr_m+=12;
		$curr_y--;
	}
	$dt = $curr_y.'-'.$curr_m.'-1';
	//$dt = date('Y-m-1', strtotime('-1 year'));
	
	$sql = "select sc.year, sc.month, sum(sc.amount) as amount, $sum_qty as qty 
	from sku_items_sales_cache_b%d sc 
	left join sku_items si on si.id=sc.sku_item_id
	left join uom on uom.id=si.packing_uom_id
	where sc.sku_item_id in (".join(',', $sid_list).") and sc.date > ".ms($dt)." group by year, month";
	if (BRANCH_CODE != 'HQ'){
		print "<h2>Sales Trend of item $item[sku_item_code] (".BRANCH_CODE.")</h2>";
		$query = sprintf($sql, $sessioninfo['branch_id']);
	}
	else
	{
		$tb=$con->sql_query("select count(*) as total_branch from branch where active=1");
        $tb_b = $con->sql_fetchassoc($tb);
        $total_branch = $tb_b['total_branch'];

		$con->sql_query("select id, code from branch where active=1 $br_filter order by branch.sequence, branch.code");
		$total_select=$con->sql_numrows();

		while($bid = $con->sql_fetchassoc()){
			$tmp[] = sprintf($sql, $bid['id']);
			$br_code[] = $bid['code'];
		}
		$con->sql_freeresult();
		
		if ($total_branch == $total_select)
			$branches_codes="All Branches";
		else
		    $branches_codes=join(", ",$br_code);

		$query = join(" union all ", $tmp);
		
		//if($sessioninfo['u']=='wsatp')	print $query;
		//print "<h2>Sales Trend of item $item[0] (All Branches)</h2>";
		print "<h2>Sales Trend of item $item[sku_item_code] (".$branches_codes.")</h2>";
	}
	$con->sql_query($query);

	if ($con->sql_numrows()<=0)
	{
		print "-- The item does not have sales for the past 12 months --";
		return;
	}
	$cm = date("Y")*12+date("m");
	while($r=$con->sql_fetchassoc())
	{
		$tm = $r['year']*12+$r['month'];
		foreach(array(1,3,6,12) as $mm)
		{
			if ($cm - $tm <= $mm)
			{
				$data['qty'][$mm] += $r['qty'];
				$data['amount'][$mm] += $r['amount'];
			}
		}
		$datam['qty'][12-($cm-$tm)] += $r['qty'];
		$datam['amount'][12-($cm-$tm)] += $r['amount'];
	}
	$con->sql_freeresult();
	@ksort($datam['qty']);
	@ksort($datam['amount']);
	//if($sessioninfo['u']=='wsatp')	echo"<pre>";print_r($datam);echo"</pre>";

	print "<div style='margin-right:10px;float:left;'><table cellpadding=0 cellspacing=0 border=0>";
	print "<tr class=small align=center style='color:#999'>";
	//for($i=11;$i>=0;$i--) print "<td width=30>".date("My", strtotime("-$i month"))."</td>";
	$curr_y = mi(date("Y", strtotime($dt)));
	$curr_m = mi(date("m", strtotime($dt)));
	if($curr_m<1){
        $curr_m+=12;
        $curr_y--;
	}
	for($i = 1;$i<=12;$i++){
        print "<td width=30>".date("My", strtotime($curr_y.'-'.$curr_m.'-1',"-$i month"))."</td>";
        $curr_m++;
        if($curr_m>12){
            $curr_m = 1;
            $curr_y++;
		}
	}
	print "</tr>";
	print "<tr><td colspan=12><canvas id=cv1 width=480 height=120></canvas></td></tr>";
	print "<tr class=small align=center>";
	$t = 0;
	for($i=1;$i<=12;$i++) {
		$t += intval($datam[qty][$i]);
		print "<td width=30><font color=#cc0022>".intval($datam[qty][$i])."</font><br /><font color=#0055bb>$t</font></td>";
	}
	print "</tr>";
	print "</table></div>";

	print "<table cellspacing=1 cellpadding=4 border=0 style='padding:5px;border:1px solid #ccc;float:left;'>";
	print "<tr bgcolor=#ffee99><td>&nbsp;</td><th>Total Qty</th><th>Per Mth</th></tr>";
	foreach(array(1,3,6,12) as $mm)
	{
	    $qty  = number_format($data[qty][$mm]);
	    //$amt  = number_format($data[amount][$mm],2);
	    $avg = number_format($data[qty][$mm] / $mm, 2);

		print "<tr><td>$mm month".($mm>1?"s":"")." ago</td><td>$qty</td><td>$avg</td></tr>";
	}
	print "</table>";


	print "<script>var ldata = ".json_encode($datam['qty']).";
	function plot_me()
	{
	    var cvheight = document.getElementById('cv1').height;
	    var cvwidth = document.getElementById('cv1').width;
	    var ctx = document.getElementById('cv1').getContext('2d');
	    var cvstep = parseInt(cvwidth / 12);

		// fill bkg

	  var lingrad = ctx.createLinearGradient(0,0,0,150);
	  lingrad.addColorStop(0, '#f7f7f7');
	  lingrad.addColorStop(1, '#fff');
	  ctx.fillStyle = lingrad;
	  ctx.fillRect(0,0,cvwidth,cvheight);

		// find max, sun pien draw lines
	    max = 0; total = 0;

	    ctx.beginPath();
	    for (var m=1;m<=12;m++)
	    {
	        h = parseInt(ldata[m]);
	        if (isNaN(h)) h=0;

			total += h;
			if (max<h) max = h;

			ctx.moveTo(m*cvstep+0.5,0);
			ctx.lineTo(m*cvstep+0.5,cvheight);
		}
		ctx.lineWidth = 1;
		ctx.strokeStyle = '#eee';
		ctx.stroke();
		max = max * 1.1 / cvheight;
		total = total * 1.1 / cvheight;


		ctx.lineWidth = 5;
		ctx.lineCap = 'round';
		ctx.lineJoint = 'round';
	    // draw monthly
	    ctx.beginPath();
	    for (var m=1;m<=12;m++)
	    {
	        h = parseInt(ldata[m]);
	        if (isNaN(h)) h=0;
	        if (m==1)
	        	ctx.moveTo((m-0.5)*(cvstep),cvheight-h/max);
			else
				ctx.lineTo((m-0.5)*(cvstep),cvheight-h/max);
		}
		ctx.strokeStyle = '#c02';
		ctx.stroke();

		// draw total
	    ctx.beginPath();
	    t = 0;
	    for (var m=1;m<=12;m++)
	    {
	        h = parseInt(ldata[m]);
	        if (isNaN(h)) h=0;
	        t+=h;
			if (m==1)
				ctx.moveTo((m-0.5)*(cvstep),cvheight-t/total);
			else
				ctx.lineTo((m-0.5)*(cvstep),cvheight-t/total);
		}
		ctx.strokeStyle = '#05a';
		ctx.stroke();

		// frame
		ctx.lineWidth = 1;
		ctx.strokeStyle = '#ccc';
	  	ctx.strokeRect(0,0,cvwidth,cvheight);
	}
	plot_me();
	</script>";
}

function show_sales_trend2()
{
	global $con, $sessioninfo, $smarty;

	$id = intval($_REQUEST['id']);
	$branch_ids=$_REQUEST['branch_ids'];
	$use_parent = mi($_REQUEST['use_parent']);
	
	if ($branch_ids)    $br_filter=" and branch.id in ($branch_ids)";
	
	$item = array();
	$sid_list = array();
	$con->sql_query("select * from sku_items where id = $id");
	$item = $con->sql_fetchassoc();
	$sid_list[] = mi($item['id']);
	$con->sql_freeresult();
	
	if (!$item || !$sid_list) die("Invalid sku item #$id");

	if($use_parent){	// show by parent
		$con->sql_query("select * from sku_items where sku_id=".mi($item['sku_id']));
		while($r = $con->sql_fetchassoc()){
			$sid = mi($r['id']);
			if(!in_array($sid, $sid_list)){
				$sid_list[] = $sid;
			}
			if($r['is_parent'])	$item = $r; 
		}
		$con->sql_freeresult();
		
		$sum_qty = "sum(sc.qty*uom.fraction)";
	}else{
		$sum_qty = "sum(sc.qty)";
	}
	
	//$dt = date('Y-m-d', strtotime('-1 year'));
	$curr_d = date("d");
	$curr_m = date("m");
	$curr_y = date("Y");
	$curr_m -= 12;
	if($curr_m<1){
		$curr_m+=12;
		$curr_y--;
	}

	$dt = "$curr_y-$curr_m-$curr_d";
	
	if(date("d", strtotime("$dt +1 day")) == 1){
		$dt = date("Y-m-d", strtotime("$dt +1 day"));
		$date_filter = "sc.date >= ".ms($dt);
		$month_loop_count = 12;
		$first_day = 1;
	}else{
		$date_filter = "sc.date > ".ms($dt);
		$month_loop_count = 13;
		$first_day = date("d", strtotime("$dt +1 day"));
	}
	
	$sql = "select sc.date, sc.year, sc.month, sum(sc.amount) as amount, $sum_qty as qty 
	from sku_items_sales_cache_b%d sc 
	left join sku_items si on si.id=sc.sku_item_id
	left join uom on uom.id=si.packing_uom_id
	where sc.sku_item_id in (".join(',', $sid_list).") and $date_filter 
	group by sc.date";
	
	if (BRANCH_CODE != 'HQ'){
		$branches_codes = BRANCH_CODE;
		$query = sprintf($sql, $sessioninfo['branch_id']);
	}
	else
	{
		$tb=$con->sql_query("select count(*) as total_branch from branch where active=1");
        $tb_b = $con->sql_fetchassoc($tb);
        $total_branch = $tb_b['total_branch'];

		$con->sql_query("select id, code from branch where active=1 $br_filter order by branch.sequence, branch.code");
		$total_select=$con->sql_numrows();

		while($bid = $con->sql_fetchassoc()){
			$tmp[] = sprintf($sql, $bid['id']);
			$br_code[] = $bid['code'];
		}
		$con->sql_freeresult();
		
		if ($total_branch == $total_select)
			$branches_codes="All Branches";
		else
		    $branches_codes=join(", ",$br_code);

		$query = join(" union all ", $tmp);
	}
	$q1 = $con->sql_query($query);

	$cm = date("Y")*12+date("m");
	$curr_times = strtotime(date("Y-m-d"));
	//$curr_times = strtotime("2017-06-10"); // testing purpose
	while($r=$con->sql_fetchassoc($q1)){
		$tm = $r['year']*12+$r['month'];
		$sales_times = strtotime($r['date']);
		$times_diff = $curr_times - $sales_times;
		foreach(array(1,3,6,12) as $mm){
			$st_times = $mm * 30 * strtotime("+1 day", 0); // convert sales trend month into seconds
			if ($times_diff <= $st_times){
				$data['qty'][$mm] += $r['qty'];
				$data['amount'][$mm] += $r['amount'];
			}else{
				$data['qty'][$mm] += 0;
				$data['amount'][$mm] += 0;
			}
		}
		$datam['qty'][$month_loop_count-($cm-$tm)] += $r['qty'];
		$datam['amount'][$month_loop_count-($cm-$tm)] += $r['amount'];
	}
	$con->sql_freeresult($q1);
	
	if($data){
		@ksort($datam['qty']);
		@ksort($datam['amount']);
		ksort($data['qty']);
		ksort($data['amount']);
	}
	
	
	$smarty->assign("item", $item);
	$smarty->assign("branches_codes", $branches_codes);
	$smarty->assign("data", $data);
	$smarty->assign("datam", $datam);
	$smarty->assign("json_datam", json_encode($datam['qty']));
	$smarty->assign("month_loop_count", json_encode($month_loop_count));

	$curr_y = mi(date("Y", strtotime($dt)));
	$curr_m = mi(date("m", strtotime($dt)));
	for($i=1;$i<=$month_loop_count;$i++){
		if($i==1) $date_list[$i] = date("jS My", strtotime($curr_y.'-'.$curr_m.'-'.$first_day,"-$i month"));
		elseif($i == $month_loop_count) $date_list[$i] = date("jS My", strtotime($curr_y.'-'.$curr_m.'-'.$curr_d,"-$i month"));
		else $date_list[$i] = date("My", strtotime($curr_y.'-'.$curr_m.'-1',"-$i month"));
        $curr_m++;
        if($curr_m>12){
            $curr_m = 1;
            $curr_y++;
		}
	}

	$smarty->assign("date_list", $date_list);
	$smarty->display("masterfile_sku_items.sales_trend.tpl");
}

function show_inventory_find()
{
	global $con, $smarty, $sessioninfo, $config;

	$sku_item_id = intval($_REQUEST['id']);
	$sku_id = intval($_REQUEST['sku_id']);

	if($_REQUEST['id_type']=='sku_id'){
		$where = "sku_id = $sku_id";
	}
	else{
		$where = "sku_items.id = $sku_item_id";
	}

	$branch_id = get_request_branch();

	$con->sql_query("select report_prefix from branch where id = $branch_id");
	$prefix = $con->sql_fetchrow();
	$con->sql_freeresult();
	$dt = ms($_REQUEST['dt']);
	switch($_REQUEST['type'])
	{
		case 'grn':
			if(isset($_REQUEST['is_ibt'])){
				$ibt = "and is_ibt = " . $_REQUEST['is_ibt'];
			}
			if($config['use_grn_future']) $open_php = 'goods_receiving_note.php';
			else $open_php = 'goods_receiving_note_approval.account.php';
			
			// need to use this for view
			$open_php = 'goods_receiving_note.php';
			
			print "<h1>Goods Receiving Note</h1>";
			$query = "select grn.id, '', vendor.description, sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty
				from grn_items
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				left join vendor on grn.vendor_id = vendor.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date = $dt
				$ibt
				group by 1,2,3";
			break;
		case 'grn2':
			if($config['use_grn_future']) $open_php = 'goods_receiving_note.php';
			else $open_php = 'goods_receiving_note_approval.account.php';
			
			// need to use this for view
			$open_php = 'goods_receiving_note.php';
			
			print "<h1>Goods Receiving Note (not approved)</h1>";
			$query = "select grn.id, '', vendor.description, sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty
				from grn_items
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				left join vendor on grn.vendor_id = vendor.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=0 and grn.status<2 and grn.active=1 and grr.rcv_date = $dt
				group by 1,2,3";
			break;
		case 'do':
			$open_php = 'do.php';
			print "<h1>DO</h1>";
			$query = "select do.id, do.do_no, if(do_branch_id>0,branch.code,do.open_info), sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, if(do.do_type = 'credit_sales', concat(d.code, ' - ', d.description), branch.description) as description, do.do_type
				from do_items
				left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
				left join uom on do_items.uom_id=uom.id
				left join branch on do_branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				left join debtor d on d.id = do.debtor_id
				where $where and do_items.branch_id=$branch_id and do.checkout=1 and do.approved=1 and do.status<2 and do.active=1 and do_date = $dt
				group by 1,2,3";
			break;
		case 'do2':
			$open_php = 'do.php';
			print "<h1>DO (not checkout)</h1>";
			$query = "select do.id, do.do_no, if(do_branch_id>0,branch.code,do.open_info), sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, if(do.do_type = 'credit_sales', concat(d.code, ' - ', d.description), branch.description) as description, do.do_type
				from do_items
				left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
				left join uom on do_items.uom_id=uom.id
				left join branch on do_branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				left join debtor d on d.id = do.debtor_id
				where $where and do_items.branch_id=$branch_id and do.approved=1 and do.checkout=0 and do.status<2 and do.active=1 and do_date = $dt
				group by 1,2,3";
			break;


		case 'adj':
			$open_php = 'adjustment.php';
			print "<h1>Adjustment</h1>";
			$query = "select adjustment.id, '', adjustment_type, sum(qty) as qty
				from adjustment_items
				left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
				left join sku_items on sku_item_id = sku_items.id
				where $where and adjustment_items.branch_id = $branch_id and adjustment.approved=1 and adjustment.status<2 and adjustment.active=1 and adjustment.adjustment_date = $dt
				group by 1,2,3";
			break;

		case 'adj2':
			$open_php = 'adjustment.php';
			print "<h1>Adjustment (not approved)</h1>";
			$query = "select adjustment.id, '', adjustment_type, sum(qty) as qty
				from adjustment_items
				left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
				left join sku_items on sku_item_id = sku_items.id
				where $where and adjustment_items.branch_id = $branch_id and adjustment.approved=0 and adjustment.status<2 and adjustment.active=1 and adjustment.adjustment_date = $dt
				group by 1,2,3";
			break;

		case 'gra':
			$open_php = 'goods_return_advice.php';
			print "<h1>Goods Return Advice</h1>";
			$query = "select gra.id, '', vendor.description, sum(qty) as qty
				from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
				left join sku_items on sku_item_id = sku_items.id
				left join vendor on gra.vendor_id = vendor.id
				where $where and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned=1 and gra.return_timestamp ".(strtotime($_REQUEST['dt'])>0 ? "between $dt and date_add($dt, interval 1 day)" : " = $dt") . "
				group by 1,2,3";
			break;
		case 'gra2':
			$open_php = 'goods_return_advice.php';
			print "<h1>Goods Return Advice (not approved)</h1>";
			$query = "select gra.id, '', if (vendor.description is null, vendor2.description, vendor.description), sum(qty) as qty
				from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
				left join sku_items on sku_item_id = sku_items.id
				left join vendor on gra.vendor_id = vendor.id
				left join vendor vendor2 on gra_items.vendor_id = vendor2.id
				where $where and gra_items.branch_id = $branch_id and (gra.id is null or (gra.status in (0,2) and gra.returned=0)) and gra_items.added ".(strtotime($_REQUEST['dt'])>0 ? "between $dt and date_add($dt, interval 1 day)" : " = $dt") . "
				group by 1,2,3";
			break;
		case 'cn':
		    $open_php = 'consignment.credit_note.php';
		    print "<h1>Consignment Credit Note</h1>";
		    $query = "select cn.id, inv_no, branch.code, sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty,branch.description,cn.branch_id as link_bid
				from cn_items
				left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
				left join uom on cn_items.uom_id=uom.id
				left join branch on cn.branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and cn.to_branch_id=$branch_id and cn.active=1 and cn.status=1 and cn.approved=1 and cn.date = $dt
				group by 1,2,3";
		    break;
        case 'cn2':
		    $open_php = 'consignment.credit_note.php';
		    print "<h1>Consignment Credit Note (not approved)</h1>";
		    $query = "select cn.id, ifnull(inv_no,concat('ID#',cn.id)), branch.code, sum(cn_items.ctn *uom.fraction + cn_items.pcs) as qty,branch.description,cn.branch_id as link_bid
				from cn_items
				left join cn on cn.id=cn_items.cn_id and cn.branch_id=cn_items.branch_id
				left join uom on cn_items.uom_id=uom.id
				left join branch on cn.branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and cn.to_branch_id=$branch_id and cn.active=1 and cn.status<2 and cn.approved=0 and cn.date = $dt
				group by 1,2,3";
		    break;
        case 'dn':
		    $open_php = 'consignment.debit_note.php';
		    print "<h1>Consignment Debit Note</h1>";
		    $query = "select dn.id, inv_no, branch.code, sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty,branch.description,dn.branch_id as link_bid
				from dn_items
				left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
				left join uom on dn_items.uom_id=uom.id
				left join branch on dn.branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and dn.to_branch_id=$branch_id and dn.active=1 and dn.status=1 and dn.approved=1 and dn.date = $dt
				group by 1,2,3";
		    break;
        case 'dn2':
		    $open_php = 'consignment.debit_note.php';
		    print "<h1>Consignment Debit Note (not approved)</h1>";
		    $query = "select dn.id, ifnull(inv_no,concat('ID#',dn.id)), branch.code, sum(dn_items.ctn *uom.fraction + dn_items.pcs) as qty,branch.description,dn.branch_id as link_bid
				from dn_items
				left join dn on dn.id=dn_items.dn_id and dn.branch_id=dn_items.branch_id
				left join uom on dn_items.uom_id=uom.id
				left join branch on dn.branch_id = branch.id
				left join sku_items on sku_item_id = sku_items.id
				where $where and dn.to_branch_id=$branch_id and dn.active=1 and dn.status<2 and dn.approved=0 and dn.date = $dt
				group by 1,2,3";
		    break;
	}
	print "Click on document number to open in new window.";
	print "<table width=100% cellspacing=0 cellpadding=4 border=0 class=tb>";
	print "<tr bgcolor=#ffee99><td>Doc No.</th><th width=80%>Description</th><th>Qty (Pcs)</th>";
	$con->sql_query($query) or die(mysql_error());
	while($r=$con->sql_fetchrow())
	{
	    $link_bid = $branch_id;
		if ($r[1]=='') {
			$r[1] = $prefix[0].sprintf("%05d",$r[0]);
		}
		if ($_REQUEST['type']=='do' || $_REQUEST['type']=='do2')
		{
			$rtest = unserialize($r[2]);
			if ($rtest) // $r[1] is serialized
			{
				$r[2] = $rtest['name']."-".$r['description']."<br>".$rtest['address'];
			}elseif($r['description'] && $r['do_type'] == 'credit_sales'){
				$r[2] = $r['description'];
			}
		}
		
		if ($r[2]=='') $r[2] = '&nbsp;';

		if($_REQUEST['type']=='cn'||$_REQUEST['type']=='cn2'||$_REQUEST['type']=='dn'||$_REQUEST['type']=='dn2')    $link_bid = $r['link_bid'];
		if ($r[0])
			print "<tr><td><a href=\"$open_php?a=view&id=$r[0]&branch_id=$link_bid&highlight_item_id=$sku_item_id\" target=_blank>".$r[1]."</a></td><td>$r[2]</td><td>".smarty_qty_nf($r[3])."</td></tr>";
		else
			print "<tr><td nowrap>- not assigned -<td>$r[2]</td><td>".smarty_qty_nf($r[3])."</td></tr>";
	}
	$con->sql_freeresult();
	print "</table>";
//	print_r($_REQUEST);
}

function get_inventory()
{
	global $con, $smarty, $sessioninfo;
	global $query_list;

	$bid = intval($_REQUEST['branch_id']);
	$sku_id = intval($_REQUEST['sku_id']);
	$sku_item_id = intval($_REQUEST['sku_item_id']);

	if (BRANCH_CODE == 'HQ')
	{
		$con->sql_query("select id,code,description,active from branch order by sequence,code");
		$smarty->assign("branch", $con->sql_fetchrowset());
	}
	else
	{
		$con->sql_query("select id,code,description,active from branch where code = ".ms(BRANCH_CODE));
		$smarty->assign("branch", $con->sql_fetchrowset());
		//$bid = $sessioninfo['branch_id'];
	}

	if (isset($_REQUEST['output_excel']))
	{
	    include("include/excelwriter.php");
	    $smarty->assign('no_header_footer', true);

	    Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=arms'.time().'.xls');

		print ExcelWriter::GetHeader();
	}

	if (!$bid)
	{
		$start = microtime(true);
		//$start = time();
		get_inventory_all($sku_id, $sku_item_id);
		$smarty->display("masterfile_sku.inventory_all.tpl");
		$finish = microtime(true);
		//$finish = time();
		/*if ($sessioninfo['id']==1) {
			//print "$start<br />";
			//print "$finish<br />";
			print '<br />';
			foreach ($query_list as $ql) {
				print "$ql<br /><br />";
			}
			print 'Total time : <b>'.($finish-$start).'</b><br /><br />';
		}*/
	}
	else
	{
		get_inventory_branch($bid, $sku_id, $sku_item_id);
		$smarty->display("masterfile_sku.inventory_branch.tpl");
	}

	if (isset($_REQUEST['output_excel']))
	{
		print ExcelWriter::GetFooter();
	}
}

function group_by_branch($data){
	if (!$data) return false;
	$ret = array();
	foreach($data as $r)
	{
		$ret[$r['branch_id']] = $r;
		$ret['total'] += $r['qty'];
	}

	return $ret;
}

function get_inventory_branch($branch_id, $sku_id, $sku_item_id){
	global $con, $smarty, $config;

	if ($sku_id){
		$is_parent_inventory = 1;
		$where = "sku_id = $sku_id";
		$select_qty=" sum(qty*u1.fraction) as qty ";
		$form['type']='sku_id';
		
		$con->sql_query("select id, sku_item_code from sku_items where sku_id=$sku_id order by is_parent desc, id");
		while($r = $con->sql_fetchassoc()){
			$form['item_list'][$r['id']] = $r;
		}
		$con->sql_freeresult();
	}
	else{
		$where = "sku_items.id = $sku_item_id";
		$select_qty=" sum(qty) as qty ";
		$form['type']='sku_item_id';
	}
	$form['is_parent_inventory'] = $is_parent_inventory;

    // get sku_items
	$con->sql_query("select * from sku_items where $where order by id limit 1");
	$sku = $con->sql_fetchrow();
	$con->sql_freeresult();
	$smarty->assign("sku", $sku);

	// check this sku is without inventory or not
	if($config['enable_no_inventory_sku']){
        $no_inventory = get_sku_no_inventory($sku['sku_id']);
		$smarty->assign('no_inventory', $no_inventory);
		$form['no_inventory'] = $no_inventory == 'yes' ? 1 : 0;
	}

	$data = array();
	$con->sql_query("select sku_items.id as sid, sum(sc.qty) as qty, sc.date, sc.cost, u1.fraction, sc.sku_item_code
from stock_check sc
join sku_items using (sku_item_code)
left join uom u1 on u1.id=packing_uom_id
where $where and branch_id = $branch_id
group by date, sid
order by date, sid");
	while($r=$con->sql_fetchassoc())
	{
		if($is_parent_inventory){
			$data[$r['date']]['stock_check'] += ($r['qty']*$r['fraction']);
			$data[$r['date']]['stock_check_by_item'][$r['sid']]['sku_item_code'] = $r['sku_item_code'];
			$data[$r['date']]['stock_check_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['date']]['stock_check_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			$data[$r['date']]['stock_check_by_item'][$r['sid']]['total_cost'] += ($r['qty']*$r['cost']);
			
			if($data[$r['date']]['stock_check_by_item'][$r['sid']]['real_qty']){
				$data[$r['date']]['stock_check_by_item'][$r['sid']]['cost'] = round($data[$r['date']]['stock_check_by_item'][$r['sid']]['total_cost']/$data[$r['date']]['stock_check_by_item'][$r['sid']]['real_qty'], $config['global_cost_decimal_points']);
			}else{
				$data[$r['date']]['stock_check_by_item'][$r['sid']]['cost'] = $r['cost'];
			}			
		}else{
			$data[$r['date']]['stock_check'] += $r['qty'];
			$data[$r['date']]['stock_check_cost'] = $r['cost'];
		}	
	}
	$con->sql_freeresult();
	
	// pos
	/*$con->sql_query("select $select_qty, pos.date as dt, pf.finalized
from pos_items pi
left join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
left join sku_items on sku_items.id=pi.sku_item_id
left join uom u1 on u1.id=packing_uom_id
left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
where $where and pos.branch_id = $branch_id and pos.cancel_status=0
group by dt");
	while($r=$con->sql_fetchassoc()){
	    if($r['finalized']||$config['consignment_modules'])	$data[$r['dt']]['pos'] = $r['qty'];
	    else    $data[$r['dt']]['pos2'] = $r['qty'];
	}
	$con->sql_freeresult();*/
	
	// finalized pos
	/*$con->sql_query("select $select_qty, s.date as dt
from sku_items_sales_cache_b".$branch_id." s
left join sku_items on sku_items.id=s.sku_item_id
left join uom u1 on u1.id=packing_uom_id
where $where
group by dt");
	while($r=$con->sql_fetchassoc()){
	    $data[$r['dt']]['pos'] = $r['qty'];
	}
	$con->sql_freeresult();*/
	
	$con->sql_query("select sku_items.id as sid, s.qty, s.date as dt, u1.fraction
from sku_items_sales_cache_b".$branch_id." s
left join sku_items on sku_items.id=s.sku_item_id
left join uom u1 on u1.id=packing_uom_id
where $where
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['pos'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['pos_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['pos_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['pos'] += $r['qty'];
		}
	}
	$con->sql_freeresult();
	
	if(!$config['consignment_modules']){
		// unfinalize pos
		$con->sql_query("select sku_items.id as sid, sum(pi.qty) as qty, pos.date as dt, u1.fraction
	from pos_items pi
	join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
	join sku_items on sku_items.id=pi.sku_item_id
	left join uom u1 on u1.id=packing_uom_id
	left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
	where $where and pos.branch_id = $branch_id and pos.cancel_status=0 and pf.finalized=0
	group by dt, sid");
		while($r=$con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data[$r['dt']]['pos2'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['pos2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['pos2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}else{
				$data[$r['dt']]['pos2'] += $r['qty'];
			}
		}
		$con->sql_freeresult();
	}
	

	$con->sql_query("select sku_items.id as sid, sum(gra_items.qty) as qty, date(return_timestamp) as dt, u1.fraction
from gra_items
left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where $where and gra_items.branch_id = $branch_id and gra.status = 0 and gra.returned = 1
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['gra'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['gra_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['gra_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['gra'] = $r['qty'];
		}
		
	}
	$con->sql_freeresult();
	
	$con->sql_query("select sku_items.id as sid, sum(gra_items.qty) as qty, date(gra_items.added) as dt, u1.fraction
from gra_items
left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where $where and gra_items.branch_id = $branch_id  and (gra.id is null or (gra.status in (0,2) and gra.returned=0))
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['gra2'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['gra2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['gra2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['gra2'] = $r['qty'];
		}
		
	}
	$con->sql_freeresult();
	
	/*if ($sku_id){
		$select_do_qty=" sum((do_items.ctn*uom.fraction*u1.fraction) + (do_items.pcs*u1.fraction)) as qty ";
	}
	else{
		$select_do_qty=" sum(do_items.ctn*uom.fraction + do_items.pcs) as qty ";
	}*/
	//FROM DO
	$con->sql_query("select sku_items.id as sid, sum(do_items.ctn*uom.fraction + do_items.pcs) as qty, date(do.do_date) as dt, u1.fraction
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
left join uom on do_items.uom_id=uom.id
where do_items.branch_id=$branch_id and $where and do.checkout = 1 and do.approved = 1 and do.status<2 and do.active=1 
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['do'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['do_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['do_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['do'] = $r['qty'];	
		}
		
	}
	$con->sql_freeresult();
	
	$con->sql_query("select sku_items.id as sid, sum(do_items.ctn*uom.fraction + do_items.pcs) as qty, date(do.do_date) as dt, u1.fraction
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
left join uom on do_items.uom_id=uom.id
where do_items.branch_id=$branch_id and $where and do.approved = 1 and do.checkout = 0 and do.status<2 and do.active=1 
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['do2'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['do2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['do2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['do2'] = $r['qty'];
		}
		
	}
	
	//FROM ADJUSTMENT
	$con->sql_query("select sku_items.id as sid, sum(adjustment_items.qty) as qty, date(adjustment_date) as dt, u1.fraction
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where adjustment_items.branch_id = $branch_id and $where and adjustment.approved=1 and adjustment.status<2 and adjustment.active=1 
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['adjustment'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['adjustment_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['adjustment_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['adjustment'] = $r['qty'];
		}
		
	}
	$con->sql_freeresult();
	
	$con->sql_query("select sku_items.id as sid, sum(adjustment_items.qty) as qty, date(adjustment_date) as dt, u1.fraction
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where adjustment_items.branch_id = $branch_id and $where and adjustment.approved=0 and adjustment.status<2 and adjustment.active=1 
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['adjustment2'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['adjustment2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['adjustment2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['adjustment2'] = $r['qty'];
		}
		
	}
	$con->sql_freeresult();
	
	// GRN
	//if ($sku_id){
	//	$select_grn_qty=" sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, (grn_items.ctn *rcv_uom.fraction*u1.fraction) + (grn_items.pcs*u1.fraction), (grn_items.acc_ctn *rcv_uom.fraction*u1.fraction) + (grn_items.acc_pcs*u1.fraction))) as qty ";
	//}
	//else{
		$select_grn_qty=" sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty ";
	//}

	$con->sql_query("select sku_items.id as sid, $select_grn_qty, grr.rcv_date as dt, u1.fraction,grn.is_ibt
from grn_items
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
left join sku_items on grn_items.sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where $where and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 
group by dt, sid, grn.is_ibt");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			if($r["is_ibt"]){
				$data[$r['dt']]['grn_ibt'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['grn_by_item'][$r['sid']]['qty_ibt'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['grn_by_item'][$r['sid']]['real_qty_ibt'] += $r['qty'];
			}else{
				$data[$r['dt']]['grn'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['grn_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['grn_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}
		}else{
			if($r["is_ibt"]){
				$data[$r['dt']]['grn_ibt'] = $r['qty'];
			}else{
				$data[$r['dt']]['grn'] = $r['qty'];
			}
		}
		
	}
	$con->sql_freeresult();
	
	//GRN2
	$con->sql_query("select sku_items.id as sid, $select_grn_qty, grr.rcv_date as dt, u1.fraction
from grn_items
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
left join sku_items on grn_items.sku_item_id = sku_items.id
left join uom u1 on u1.id=packing_uom_id
where $where and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=0 and grn.status<2 and grn.active=1 
group by dt, sid");
	while($r=$con->sql_fetchassoc()){
		if($is_parent_inventory){
			$data[$r['dt']]['grn2'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['grn2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
			$data[$r['dt']]['grn2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
		}else{
			$data[$r['dt']]['grn2'] = $r['qty'];
		}
		
	}
	$con->sql_freeresult();

	if($config['consignment_modules']){
	    $bid = $branch_id;
        //if ($sku_id){
		//	$select_str = " sum((cni.ctn *uom.fraction*u1.fraction) + (cni.pcs*u1.fraction)) as qty ";
		//}
		//else{
			$select_str= " sum(cni.ctn *uom.fraction + cni.pcs) as qty ";
		//}

        //FROM Credit Note
		$con->sql_query("select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
from cn_items cni
left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on cni.uom_id=uom.id
left join uom u1 on u1.id=packing_uom_id
where $where and cn.to_branch_id = $bid and cn.active=1 and cn.approved=1 and cn.status=1 
group by dt, sid",false,false);

		while($r=$con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data[$r['dt']]['cn'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['cn_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['cn_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}else{
				$data[$r['dt']]['cn'] = $r['qty'];
			}
			
		}
		$con->sql_freeresult();

		//FROM Credit Note WITHOUT Approve
		$con->sql_query("select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
from cn_items cni
left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on cni.uom_id=uom.id
left join uom u1 on u1.id=packing_uom_id
where $where and cn.to_branch_id = $bid and cn.active=1 and cn.approved=0 and cn.status<2 
group by dt, sid", false, false);
		while($r=$con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data[$r['dt']]['cn2'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['cn2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['cn2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}else{
				$data[$r['dt']]['cn2'] = $r['qty'];
			}
			
		}
		$con->sql_freeresult();

		//FROM Debit Note
		$con->sql_query("select sku_items.id as sid, $select_str, dn.date as dt, u1.fraction
from dn_items cni
left join dn on dn.id=cni.dn_id and dn.branch_id=cni.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on cni.uom_id=uom.id
left join uom u1 on u1.id=packing_uom_id
where $where and dn.to_branch_id = $bid and dn.active=1 and dn.approved=1 and dn.status=1 
group by dt, sid", false, false);
		while($r=$con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data[$r['dt']]['dn'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['dn_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['dn_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}else{
				$data[$r['dt']]['dn'] = $r['qty'];
			}
			
		}
		$con->sql_freeresult();

		//FROM Debit Note WITHOUT Approve
		$con->sql_query("select sku_items.id as sid, $select_str, dn.date as dt, u1.fraction
from dn_items cni
left join dn on dn.id=cni.dn_id and dn.branch_id=cni.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on cni.uom_id=uom.id
left join uom u1 on u1.id=packing_uom_id
where $where and dn.to_branch_id = $bid and dn.active=1 and dn.approved=0 and dn.status<2 
group by dt, sid", false, false);
		while($r=$con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data[$r['dt']]['dn2'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['dn2_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
				$data[$r['dt']]['dn2_by_item'][$r['sid']]['real_qty'] += $r['qty'];
			}else{
				$data[$r['dt']]['dn2'] = $r['qty'];
			}
			
		}
		$con->sql_freeresult();
	}

	ksort($data);
	reset($data);
	
	if(!$form['no_inventory']){
		$data = generate_stock_balance_history($form, $data);
	}
	
	//echo"<pre>";print_r($data);echo"</pre>";
	$smarty->assign("form", $form);
	$smarty->assign("data", $data);
}

function get_inventory_all($sku_id, $sku_item_id)
{
	global $con, $smarty, $config, $sessioninfo;
	global $query_list;
	$query_list = array();
	$more_than = 0.0001;
	// sales

	$form = array();
	if ($sku_id){
		$is_parent_inventory = 1;
		$form['is_parent_inventory'] = 1;
		$where = "sku_items.sku_id = $sku_id";
		$select_qty=" sum(qty*u1.fraction) as qty ";
		
		$con->sql_query("select si.id, si.sku_item_code, uom.fraction as uom_fraction
			from sku_items si 
			left join uom on uom.id=si.packing_uom_id
			where si.sku_id=$sku_id order by si.is_parent desc, si.id");
		while($r = $con->sql_fetchassoc()){
			$form['item_list'][$r['id']] = $r;
		}
		$con->sql_freeresult();
	}
	else{
		$where = "sku_items.id = $sku_item_id";
		$select_qty=" sum(qty) as qty ";
	}

	// get sku_items
	$start = microtime(true);
	$con->sql_query($abc="select * from sku_items where $where order by is_parent desc, id limit 1");
	$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
	$sku = $con->sql_fetchrow();
	$smarty->assign("sku", $sku);

	// check this sku is without inventory or not
	$show_inventory = true;
	if($config['enable_no_inventory_sku']){
        $no_inventory = get_sku_no_inventory($sku['sku_id']);
		$smarty->assign('no_inventory', $no_inventory);
		if($no_inventory == 'yes')	$show_inventory = false;
		$form['no_inventory'] = $show_inventory ? 1 : 0;
	}

	$br = $smarty->get_template_vars("branch");
	/*$data = array();
	if($show_inventory){
		foreach ($br as $brow){
			$bid = $brow['id'];
			
			$sql = "select sic.qty, u1.fraction, sic.last_update, sic.changed
			from sku_items
			left join uom u1 on u1.id=packing_uom_id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=sku_items.id
			where $where and branch_id=$bid";
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				if($sku_id){
					// parent stock
					$data[$bid]['qty'] += $r['qty']*$r['fraction'];
					if(!$data[$bid]['last_update'] || $data[$bid]['last_update'] < $r['last_update']){
						$data[$bid]['last_update'] = $r['last_update'];
					}
					if($r['changed'] && !$data[$bid]['changed'])	$data[$bid]['changed'] = $r['changed'];
					
					$data['total']['qty'] += $r['qty']*$r['fraction'];
					if($r['changed'] && !$data['total']['changed'])	$data['total']['changed'] = $r['changed'];
				}else{
					$data[$bid]['qty'] = $r['qty'];
					$data[$bid]['last_update'] = $r['last_update'];
					$data[$bid]['changed'] = $r['changed'];
					
					$data['total']['qty'] += $r['qty'];
					if($r['changed'] && !$data['total']['changed'])	$data['total']['changed'] = $r['changed'];
				}				
			}
			$con->sql_freeresult($q1);
		}
	}
	//print_r($data);
	$smarty->assign('data', $data);
	return;*/
	
	$min_date = date("Y-m-d", strtotime("-1 year"));
	//print "min_date = $min_date";
	
    //set date to 0 if show full data enabled and stock check is absent
    if($config['sku_inventory_all_show_full_data']) $min_date = 0;
    
	$data = array();
	foreach ($br as $brow)
	{
		$bid = $brow['id'];
		$branch_min_date = $min_date;
		
		$start = microtime(true);
		
		// check maximum stock check date
		$con->sql_query("select sc.sku_item_code, sku_items.id as sid, max(sc.date) as last_sc_date
                        from stock_check sc
                        left join sku_items using (sku_item_code)
                        where $where and sc.branch_id=$bid and sc.date>=".ms($min_date)."group by sid
                        order by last_sc_date desc
                        limit 1");
        
		$max_sc = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($max_sc && $max_sc['last_sc_date']){
			$data['branch_data'][$bid]['last_sc_date'] = $branch_min_date = $max_sc['last_sc_date'];
		}	
		
		// -1 day of selected date
		$yesterday_of_min_date = date("Y-m-d",strtotime("-1 day", strtotime($branch_min_date)));
		
        //skip check stock balance when show full data is enabled
        if(!$config['sku_inventory_all_show_full_data'] || $max_sc) {
            // check whether stock balance table exists
            $sb_tbl = "stock_balance_b".$bid."_".date("Y",strtotime($yesterday_of_min_date));
            $sb_exists = $con->sql_query_false("explain $sb_tbl");
            $con->sql_freeresult();
            
            $q_sc = $con->sql_query($abc="select sku_items.id as sid, sum(stock_check.qty) as qty, date, cost, u1.fraction
                                          from stock_check
                                          join sku_items using (sku_item_code)
                                          left join uom u1 on u1.id=packing_uom_id
                                          where $where and branch_id=$bid and stock_check.date=".ms($branch_min_date)."
                                          group by sid, date
                                          order by sid");
            
            while($r = $con->sql_fetchassoc($q_sc)){
                if(!$r['date'])	continue;
                
                if($is_parent_inventory){
                    $data['branch_data'][$bid]['open_qty'] += ($r['qty']*$r['fraction']);
                    $data['branch_data'][$bid]['qty'] += ($r['qty']*$r['fraction']);
                    $data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
                    $data['branch_data'][$bid]['stock_check_by_item'][$r['sid']]['qty'] += ($r['qty']*$r['fraction']);
                }else{
                    $data['branch_data'][$bid]['open_qty'] = $r['qty'];
                    $data['branch_data'][$bid]['qty'] = $r['qty'];
                    $data['branch_data'][$bid]['qty2'] = $r['qty'];
                }	
            }
            $con->sql_freeresult($q_sc);
            
            // get stock balance if no stock check
            if($is_parent_inventory){
                if($sb_exists){
                    // loop each item
                    foreach($form['item_list'] as $sid => $si){
                        // no stock check for this item
                        if(!isset($data['branch_data'][$bid]['stock_check_by_item'][$sid])){
                            // get from stock balance
                            $con->sql_query("select qty from $sb_tbl where sku_item_id=$sid and ".ms($yesterday_of_min_date)." between from_date and to_date");
                            $sb = $con->sql_fetchassoc();
                            $con->sql_freeresult();
                            
                            $data['branch_data'][$bid]['open_qty'] += $sb['qty'] * $si['uom_fraction'];
                            $data['branch_data'][$bid]['qty'] += $sb['qty'] * $si['uom_fraction'];
                            $data['branch_data'][$bid]['qty2'] += $sb['qty'] * $si['uom_fraction'];
                        }
                    }
                }			
            }else{
                // no data for starting date
                if(!isset($data['branch_data'][$bid]['qty'])){
                    if($sb_exists){
                        // get from stock balance
                        $con->sql_query("select qty from $sb_tbl where sku_item_id=$sku_item_id and ".ms($yesterday_of_min_date)." between from_date and to_date");
                        $sb = $con->sql_fetchassoc();
                        $con->sql_freeresult();
                        
                        $data['branch_data'][$bid]['open_qty'] = $sb['qty'];
                        $data['branch_data'][$bid]['qty'] = $sb['qty'];
                        $data['branch_data'][$bid]['qty2'] = $sb['qty'];
                    }
                }
            }
        }
        
		// finalized pos
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, s.qty, s.date as dt, u1.fraction
                              from sku_items_sales_cache_b".$bid." s
                              left join sku_items on sku_items.id=s.sku_item_id
                              left join uom u1 on u1.id=packing_uom_id
                              where $where and s.date>=".ms($branch_min_date)."
                              group by dt, sid");
        
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration **</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['pos'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty'] -= ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['pos'] += $r['qty'];
				$data['branch_data'][$bid]['qty'] -= $r['qty'];
				$data['branch_data'][$bid]['qty2'] -= $r['qty'];
			}
		}
		$con->sql_freeresult();
		
		// un-finalize pos
		if(!$config['consignment_modules']){
			$con->sql_query($abc="select sku_items.id as sid, sum(pi.qty) as qty, pos.date as dt, u1.fraction
                                  from pos_items pi
                                  join pos on pos.id=pi.pos_id and pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id
                                  join sku_items on sku_items.id=pi.sku_item_id
                                  left join uom u1 on u1.id=packing_uom_id
                                  join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
                                  where $where and pos.branch_id=$bid and pos.date>=".ms($branch_min_date)." and pos.cancel_status=0 and pf.finalized=0
                                  group by dt, sid");
			//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration **</span></b><br />$abc";
			while($r = $con->sql_fetchassoc()){
				if($is_parent_inventory){
					$data['branch_data'][$bid]['pos2'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['pos2'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] -= $r['qty'];
				}
			}
			$con->sql_freeresult();
		}

		//FROM GRA
		$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(gra_items.qty) as qty, date(return_timestamp) as dt, u1.fraction
                              from gra_items
                              left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where $where and gra_items.branch_id = $bid and gra.status=0 and gra.returned=1 and gra.return_timestamp >= ".ms($branch_min_date)."
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['gra'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty'] -= ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['gra'] += $r['qty'];
				$data['branch_data'][$bid]['qty'] -= $r['qty'];
				$data['branch_data'][$bid]['qty2'] -= $r['qty'];
			}
		}
		$con->sql_freeresult();

		//FROM GRA2
		$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(gra_items.qty) as qty, date(return_timestamp) as dt, u1.fraction
                              from gra_items
                              left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where $where and gra_items.branch_id = $bid and (gra.id is null or (gra.status in (0,2) and gra.returned=0))
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['gra2'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['gra2'] += $r['qty'];
				$data['branch_data'][$bid]['qty2'] -= $r['qty'];
			}
		}
		$con->sql_freeresult();

		//FROM DO
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(do_items.ctn*uom.fraction + do_items.pcs) as qty, date(do.do_date) as dt, u1.fraction
                              from do_items
                              left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              left join uom on do_items.uom_id=uom.id
                              where do_items.branch_id=$bid and $where and do.checkout=1 and do.approved=1 and do.status<2 and do.active=1 and do.do_date >= ".ms($branch_min_date)."
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['do'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty'] -= ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['do'] += $r['qty'];
				$data['branch_data'][$bid]['qty'] -= $r['qty'];
				$data['branch_data'][$bid]['qty2'] -= $r['qty'];
			}
		}
		$con->sql_freeresult();

		//FROM DO WITHOUT CHECKOUT
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(do_items.ctn*uom.fraction + do_items.pcs) as qty, date(do.do_date) as dt, u1.fraction
                              from do_items
                              left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              left join uom on do_items.uom_id=uom.id
                              where do_items.branch_id=$bid and $where and do.approved=1 and do.checkout=0 and do.status<2 and do.active=1 and do.do_date >= ".ms($branch_min_date)."
                              group by dt,sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['do2'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['do2'] += $r['qty'];
				$data['branch_data'][$bid]['qty2'] -= $r['qty'];
			}
		}
		$con->sql_freeresult();

		//FROM ADJUSTMENT
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(adjustment_items.qty) as qty, date(adjustment_date) as dt, u1.fraction
                              from adjustment_items
                              left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where adjustment_items.branch_id = $bid and $where and adjustment.approved=1 and adjustment.status<2 and adjustment.active=1 and adjustment.adjustment_date >= ".ms($branch_min_date)."
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['adjustment'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['adjustment'] += $r['qty'];
				$data['branch_data'][$bid]['qty'] += $r['qty'];
				$data['branch_data'][$bid]['qty2'] += $r['qty'];
			}
		}
		$con->sql_freeresult();
		
		//FROM ADJUSTMENT WITHOUT APPROVED
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, sum(adjustment_items.qty) as qty, date(adjustment_date) as dt, u1.fraction
                              from adjustment_items
                              left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
                              left join sku_items on sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where adjustment_items.branch_id = $bid and $where and adjustment.approved=0 and adjustment.status<2 and adjustment.active=1 and adjustment.adjustment_date >= ".ms($branch_min_date)."
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['adjustment2'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['adjustment2'] += $r['qty'];
				$data['branch_data'][$bid]['qty2'] += $r['qty'];
			}
		}
		$con->sql_freeresult();

		// grn	
		$select_grn_qty=" sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty ";
	
		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, $select_grn_qty, grr.rcv_date as dt, u1.fraction, grn.is_ibt
                              from grn_items
                              left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
                              left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
                              left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
                              left join sku_items on grn_items.sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where $where and grn_items.branch_id = $bid and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date >= ".ms($branch_min_date)."
                              group by dt, sid, grn.is_ibt");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				if($r["is_ibt"]){
					$data['branch_data'][$bid]['grn_ibt'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['grn'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
				}
				
			}else{
				if($r["is_ibt"]){
					$data['branch_data'][$bid]['grn_ibt'] += $r['qty'];
					$data['branch_data'][$bid]['qty'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] += $r['qty'];
				}else{
					$data['branch_data'][$bid]['grn'] += $r['qty'];
					$data['branch_data'][$bid]['qty'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] += $r['qty'];
				}
				
			}
		}
		$con->sql_freeresult();

		//$start = microtime(true);
		$con->sql_query($abc="select sku_items.id as sid, $select_grn_qty, grr.rcv_date as dt, u1.fraction
                              from grn_items
                              left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
                              left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
                              left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
                              left join sku_items on grn_items.sku_item_id = sku_items.id
                              left join uom u1 on u1.id=packing_uom_id
                              where $where and grn_items.branch_id = $bid and grr.active=1 and grn.approved=0 and grn.status<2 and grn.active=1 and grr.rcv_date >= ".ms($branch_min_date)."
                              group by dt, sid");
		//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
		while($r = $con->sql_fetchassoc()){
			if($is_parent_inventory){
				$data['branch_data'][$bid]['grn2'] += ($r['qty']*$r['fraction']);
				$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
			}else{
				$data['branch_data'][$bid]['grn2'] += $r['qty'];
				$data['branch_data'][$bid]['qty2'] += $r['qty'];
			}
		}
		$con->sql_freeresult();
		
        if($config['consignment_modules']){
			$select_str= " sum(cni.ctn *uom.fraction + cni.pcs) as qty ";

	        //FROM Credit Note
			//$start = microtime(true);
			$con->sql_query($abc="select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
                                  from cn_items cni
                                  left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
                                  left join sku_items on sku_item_id = sku_items.id
                                  left join uom on cni.uom_id=uom.id
                                  left join uom u1 on u1.id=packing_uom_id
                                  where $where and cn.to_branch_id = $bid and cn.active=1 and cn.approved=1 and cn.status=1 and cn.date>=".ms($branch_min_date)."
                                  group by dt, sid");
			//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
			while($r = $con->sql_fetchassoc()){
				if($is_parent_inventory){
					$data['branch_data'][$bid]['cn'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['cn'] += $r['qty'];
					$data['branch_data'][$bid]['qty'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] += $r['qty'];
				}
			}
			$con->sql_freeresult();

			//FROM Credit Note WITHOUT Approve
			//$start = microtime(true);
			$con->sql_query($abc="select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
                                  from cn_items cni
                                  left join cn on cn.id=cni.cn_id and cn.branch_id=cni.branch_id
                                  left join sku_items on sku_item_id = sku_items.id
                                  left join uom on cni.uom_id=uom.id
                                  left join uom u1 on u1.id=packing_uom_id
                                  where $where and cn.to_branch_id = $bid and cn.active=1 and cn.approved=0 and cn.status<2 and cn.date>=".ms($branch_min_date)."
                                  group by dt, sid", false, false);
			//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
			while($r = $con->sql_fetchassoc()){
				if($is_parent_inventory){
					$data['branch_data'][$bid]['cn2'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] += ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['cn2'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] += $r['qty'];
				}
			}
			$con->sql_freeresult();

			//FROM Debit Note
			//$start = microtime(true);
			$con->sql_query($abc="select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
                                  from dn_items cni
                                  left join dn on dn.id=cni.dn_id and dn.branch_id=cni.branch_id
                                  left join sku_items on sku_item_id = sku_items.id
                                  left join uom on cni.uom_id=uom.id
                                  left join uom u1 on u1.id=packing_uom_id
                                  where $where and dn.to_branch_id = $bid and dn.active=1 and dn.approved=1 and dn.status=1 and dn.date>=".ms($branch_min_date)."
                                  group by dt, sid", false, false);
			//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
			while($r = $con->sql_fetchassoc()){
				if($is_parent_inventory){
					$data['branch_data'][$bid]['dn'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty'] -= ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['dn'] += $r['qty'];
					$data['branch_data'][$bid]['qty'] -= $r['qty'];
					$data['branch_data'][$bid]['qty2'] -= $r['qty'];
				}
			}
			$con->sql_freeresult();

			//FROM Debit Note WITHOUT Approve
			//$start = microtime(true);
			$con->sql_query($abc="select sku_items.id as sid, $select_str, cn.date as dt, u1.fraction
                                  from dn_items cni
                                  left join dn on dn.id=cni.dn_id and dn.branch_id=cni.branch_id
                                  left join sku_items on sku_item_id = sku_items.id
                                  left join uom on cni.uom_id=uom.id
                                  left join uom u1 on u1.id=packing_uom_id
                                  where $where and dn.to_branch_id = $bid and dn.active=1 and dn.approved=0 and dn.status<2 and dn.date>=".ms($branch_min_date)."
                                  group by dt, sid", false, false);
			//$finish = microtime(true);$duration = $finish - $start;if ($duration >= $more_than) $query_list[] = "<b><span style='color:red'>$duration</span></b><br />$abc";
			while($r = $con->sql_fetchassoc()){
				if($is_parent_inventory){
					$data['branch_data'][$bid]['dn2'] += ($r['qty']*$r['fraction']);
					$data['branch_data'][$bid]['qty2'] -= ($r['qty']*$r['fraction']);
				}else{
					$data['branch_data'][$bid]['dn2'] += $r['qty'];
					$data['branch_data'][$bid]['qty2'] -= $r['qty'];
				}
			}
			$con->sql_freeresult();
		}

		// all branch total
		$data['total']['grn'] += $data['branch_data'][$bid]['grn'];
		$data['total']['grn_ibt'] += $data['branch_data'][$bid]['grn_ibt'];
		$data['total']['pos'] += $data['branch_data'][$bid]['pos'];
		$data['total']['gra'] += $data['branch_data'][$bid]['gra'];
		$data['total']['do'] += $data['branch_data'][$bid]['do'];
		$data['total']['adjustment'] += $data['branch_data'][$bid]['adjustment'];
		$data['total']['qty'] += $data['branch_data'][$bid]['qty'];
		if($config['consignment_modules']){
			$data['total']['cn'] += $data['branch_data'][$bid]['cn'];
			$data['total']['dn'] += $data['branch_data'][$bid]['dn'];
		}
		
		// all branch total 2
		$data['total']['grn2'] += $data['branch_data'][$bid]['grn2'];
		$data['total']['pos2'] += $data['branch_data'][$bid]['pos2'];
		$data['total']['gra2'] += $data['branch_data'][$bid]['gra2'];
		$data['total']['do2'] += $data['branch_data'][$bid]['do2'];
		$data['total']['adjustment2'] += $data['branch_data'][$bid]['adjustment2'];
		$data['total']['qty2'] += $data['branch_data'][$bid]['qty2'];
		if($config['consignment_modules']){
			$data['total']['cn2'] += $data['branch_data'][$bid]['cn2'];
			$data['total']['dn2'] += $data['branch_data'][$bid]['dn2'];
		}
	}
	//print_r($data);
	$smarty->assign('min_date', $min_date);
	$smarty->assign('data', $data);
	$smarty->assign('form', $form);
}

function sku_cost_history(){
	global $con,$smarty, $no_inventory, $config;

	$sku_item_id = intval($_REQUEST['sku_item_id']);
    $branch_id = intval($_REQUEST['branch_id']);

	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	$sku_update_cost_by_parent_child = mi($config['sku_update_cost_by_parent_child']);

    // get sku_items
	$con->sql_query("select * from sku_items where id=$sku_item_id");
	$sku_items = $con->sql_fetchassoc();
	$con->sql_freeresult();
	// check this sku is without inventory or not
	if($config['enable_no_inventory_sku'])	$no_inventory = get_sku_no_inventory($sku_items['sku_id']);

    $q = "select si.id, si.sku_id, si.description, si.sku_item_code, si.cost_price, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market, sku.is_bom
    from sku_items si
    left join sku on sku.id=si.sku_id
	left join category_cache cc on cc.category_id=sku.category_id
    left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id = $branch_id where si.id = ".mi($sku_item_id)." order by si.id";
    $rs1 = $con->sql_query($q, false, false);

	//$total_n = $con->sql_numrows($rs1);
	//print $q;
	print "<br />";
	
	$r = $con->sql_fetchassoc($rs1);
	if(($sku_use_avg_cost_as_last_cost || $sku_update_cost_by_parent_child) && $r['is_fresh_market']=='no' && !$config['consignment_modules']){
		run_history_by_sku($r);
	}else{
		run_history($r);
	}
			
	/*while($r = $con->sql_fetchassoc($rs1))
	{
		run_history($r);
	}
	$con->sql_freeresult();*/
}

function promotion_history()
{
    global $con,$smarty;

	$sku_item_id = intval($_REQUEST['sku_item_id']);
    $branch_id = intval($_REQUEST['branch_id']);

    $con->sql_query("select * from sku_items where id = $sku_item_id", false, false);
	$t = $con->sql_fetchrow();
	if(!$t)  die('Invalid SKU');
	
    //$code = "'%i:$branch_id%'";
    $code = '%"'.get_branch_code($branch_id).'"%';
  
    $q = "select p.branch_id,p.date_from,p.date_to,p.time_from,p.time_to,pi.promo_id,p.title,pi.member_disc_p,pi.member_disc_a ,pi.member_min_item,pi.member_qty_from,pi.member_qty_to,pi.non_member_disc_p,pi.non_member_disc_a,pi.non_member_min_item,pi.non_member_qty_from ,pi.non_member_qty_to
	from promotion p
	LEFT JOIN promotion_items pi on p.id=pi.promo_id and p.branch_id = pi.branch_id
	where pi.sku_item_id=".ms($sku_item_id)." and p.approved='1' and p.active='1' and p.status='1' and p.promo_type='discount' and p.promo_branch_id like ".ms($code);

    $rs1 = $con->sql_query($q, false, false);
  	$total_n = $con->sql_numrows();
  	//print $q;
  	while($r = $con->sql_fetchassoc($rs1)){
    	  //$data[$r['date']]['price']+= $r['price'];
    		//$data[$r['date']]['discount']+= $r['discount'];
    		$data[$r['promo_id']]['qty'] += $r['qty'];
    		$data[$r['promo_id']]['sku']= $sku_item_id;
    		$data[$r['promo_id']]['brn_id']= $r['branch_id'];

    		$data[$r['promo_id']]['date_from'] = $r['date_from'];
    		$data[$r['promo_id']]['date_to'] = $r['date_to'];

    		$data[$r['promo_id']]['time_from'] = $r['time_from'];
    		$data[$r['promo_id']]['time_to'] = $r['time_to'];

    		$data[$r['promo_id']]['promo_title'] = $r['title'];
    		$data[$r['promo_id']]['mem_disc_p'] += $r['member_disc_p'];
    		$data[$r['promo_id']]['mem_disc_a'] += $r['member_disc_a'];
    		$data[$r['promo_id']]['mem_min_item'] += $r['member_min_item'];
    		$data[$r['promo_id']]['mem_qty_from'] += $r['member_qty_from'];
    		$data[$r['promo_id']]['mem_qty_to'] += $r['member_qty_to'];

    		$data[$r['promo_id']]['non_mem_disc_p'] += $r['non_member_disc_p'];
    		$data[$r['promo_id']]['non_mem_disc_a'] += $r['non_member_disc_a'];
    		$data[$r['promo_id']]['non_mem_min_item'] += $r['non_member_min_item'];
    		$data[$r['promo_id']]['non_mem_qty_from'] += $r['non_member_qty_from'];
    		$data[$r['promo_id']]['non_mem_qty_to'] += $r['non_member_qty_to'];
  	}
	$con->sql_freeresult();
	//$smarty->assign('data', $data);
	//$smarty->display('masterfile_sku_items.promotions.tpl');
	//return;
	
  print "<h3>$t[description]</h3>";
	print "<table width=100% class=report_table><tr class=header><th rowspan=2>Date From</th>
  <th rowspan=2>Time From</th><th rowspan=2>Date To</th><th rowspan=2>Time To</th><th rowspan=2>Promo Title</th><th colspan=5>Member</th><th colspan=5>Non Member</th></tr>";
	print "<tr class=header><th>Discount</th><th>Price</th><th>Min Items</th><th>Qty From</th><th>Qty To</th><th>Discount</th><th>Price</th><th>Min Items</th><th>Qty From</th><th>Qty To</th></tr>";

	if($data)
	{
      foreach($data as $promo_id=>$v)
    	{
    	    $arr_item = array('mem_disc_p','mem_disc_a','mem_min_item','mem_qty_from','mem_qty_to','non_mem_disc_p','non_mem_disc_a','non_mem_min_item','non_mem_qty_from','non_mem_qty_to');

    	    foreach($arr_item as $val)
    	    {
              if($v[$val]=='0')
                $v[$val]='-';
          }

          print  "<tr><td nowrap>$v[date_from]</td><td nowrap>$v[time_from]</td><td nowrap>$v[date_to]</td><td nowrap>$v[time_to]</td><td><a href=promotion.php?a=view&id=$promo_id&branch_id=$v[brn_id]&highlight_item_id=$v[sku] target=blank>$v[promo_title]</a></td><td align=right>".number_format($v['mem_disc_p'],2)."</td><td align=right>".number_format($v['mem_disc_a'],2)."</td><td align=right>$v[mem_min_item]</td><td align=right>$v[mem_qty_from]</td><td align=right>$v[mem_qty_to]</td><td align=right>".number_format($v['non_mem_disc_p'],2)."</td><td align=right>".number_format($v['non_mem_disc_a'],2)."</td><td align=right>$v[non_mem_min_item]</td><td align=right>$v[non_mem_qty_from]</td><td align=right>$v[non_mem_qty_to]</td></tr>";
      }
  }
	print "</table>";
}

function run_history($r)
{
	global $con, $config, $no_inventory, $sessioninfo, $LANG;
    $branch_id = intval($_REQUEST['branch_id']);

	$sku_item_id = $r['id'];
	$cost = $r['cost_price'];
    $master_cost = doubleval($r['cost_price']);

	$where = "sku_items.id = $sku_item_id ";

	$data = array();

	if($config['consignment_modules']&&$branch_id>1){   // cost changed history from HQ
	    $con->sql_query("select * from sku_items_cost_history where branch_id=1 and sku_item_id=$sku_item_id and date>0 order by date");
		while($r=$con->sql_fetchassoc())
		{
			$data[$r['date']]['hq_cost_changed'] = $r['grn_cost'];
			$data[$r['date']]['hq_avg_cost_changed'] = $r['avg_cost'];
		}
		$con->sql_freeresult();
	}
	$con->sql_query("select sum(qty) as qty, sum(qty*cost) as cost, date from stock_check left join sku_items using (sku_item_code) where $where and branch_id = $branch_id group by date order by date", false, false);
	while($r=$con->sql_fetchassoc())
	{
		$data[$r['date']]['stock_check'] = $r['qty'];
		$data[$r['date']]['stock_check_cost'] = $r['cost'];
	}

	$con->sql_query("select qty, date as dt from sku_items_sales_cache_b$branch_id where sku_item_id = $sku_item_id", false, false);
	while($r=$con->sql_fetchassoc())
	{
		$data[$r['dt']]['pos'] = $r['qty'];
	}
    $con->sql_freeresult();

	$con->sql_query("select sum(qty) as qty, date(return_timestamp) as dt from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id left join sku_items on sku_item_id = sku_items.id where $where and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned=1 group by dt", false, false);
	while($r=$con->sql_fetchassoc())
	{
		$data[$r['dt']]['gra'] = $r['qty'];
	}
    $con->sql_freeresult();

	//FROM DO
	$con->sql_query("select sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join sku_items on sku_item_id = sku_items.id
left join uom on do_items.uom_id=uom.id
where $where and do_items.branch_id = $branch_id and do.approved=1 and do.checkout=1 and do.status<2 and do.active=1 group by dt", false, false);
	while($r=$con->sql_fetchassoc())
	{
		$data[$r['dt']]['do'] = $r['qty'];
	}
    $con->sql_freeresult();

	//FROM ADJUSTMENT
	/*$con->sql_query("select sum(qty) as qty, adjustment_date as dt
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
left join sku_items on sku_item_id = sku_items.id
where $where and adjustment_items.branch_id = $branch_id and adjustment.approved=1 and adjustment.status<2 and adjustment.active=1 group by dt", false, false);*/
	$con->sql_query("select adji.adjustment_id, adji.sku_item_id as sid, adj.adjustment_date as dt, adj.module_type, sum(if(adji.qty>0,adji.qty,0)) as positive_qty, sum(if(adji.qty<0,adji.qty,0)) as negative_qty, wo.id as wo_id, woi.finish_cost, woi.line_total_finish_cost, wo.wo_no
	from adjustment_items adji
	left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
	left join work_order wo on wo.branch_id=adj.branch_id and wo.adj_id=adj.id and adj.module_type='work_order' and wo.active=1 and wo.status=1 and wo.completed=1
	left join work_order_items_in woi on woi.branch_id=wo.branch_id and woi.work_order_id=wo.id and woi.sku_item_id=adji.sku_item_id
	where adji.sku_item_id=$sku_item_id and adji.branch_id = $branch_id and adj.approved=1 and adj.status=1 and adj.active=1
	group by adjustment_id, sid, dt, module_type
	order by dt, adjustment_id");

	$work_order_data = array();
	while($r=$con->sql_fetchassoc())
	{
		if($r['module_type'] == 'work_order' && $r['wo_id']){
			if($r['negative_qty']<0){	// negative is transfer out, not affect cost
				$data[$r['dt']]['adj'] += $r['negative_qty'];
			}
			if($r['positive_qty']>0){	// positive is transfer in, will affect cost
				$tmp = array();
				$tmp['qty'] = $r['positive_qty'];
				$tmp['cost'] = $r['finish_cost'];
				$tmp['total_cost'] = $r['line_total_finish_cost'];
				$tmp['link'] = sprintf("<a target=_blank href=\"/work_order.php?a=view&highlight_in_sid=$r[sid]&branch_id=$branch_id&id=%d\">%s</a>",trim($r['wo_id']),trim($r['wo_no']));
				$data[$r['dt']]['wo_list'][] = $tmp;
				
				// store by grn data
				$wo_key = $branch_id."_".$r['wo_id'];
				$work_order_data[$r['dt']][$wo_key]['qty'] += $tmp['qty'];
				$work_order_data[$r['dt']][$wo_key]['cost'] += $tmp['cost'];
				$work_order_data[$r['dt']][$wo_key]['total_cost'] += $tmp['total_cost'];
				$work_order_data[$r['dt']][$wo_key]['link'] = $tmp['link'];	
			}	
		}else{
			// normal adjustment
			$data[$r['dt']]['adj'] += $r['positive_qty']+$r['negative_qty'];
		}
	}
	$con->sql_freeresult();

	// grn
	/*$sql = "select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	sum(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost,
		group_concat(DISTINCT grn.id order by 1) as grn_id,
		grr.rcv_date as dt
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where $where and grn_items.branch_id = $branch_id and grn.approved=1 and grn.status<2 and grn.active group by dt";*/
		
		$sql = "select (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost,
		grn.id as grn_id, grr.rcv_date as dt, grn.grr_id, grn.is_future,
		gi.type, do.do_type, do.branch_id as do_from_branch_id, grr.currency_code, if(grr.currency_rate<0,1,grr.currency_rate) as currency_rate, grr.tax_percent as grr_tax_percent
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
		left join do on do.do_no=gi.doc_no and gi.type='DO'
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where $where and grn_items.branch_id = $branch_id and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1";
		//print $sql;

	$sql1 = $con->sql_query($sql, false, false);
	while($r=$con->sql_fetchassoc($sql1))
	{
		$data[$r['dt']]['grn'] += $r['qty'];
	    $count_this_grn = false;
	    
	    if($r['qty'] > 0){
	    	if($r['is_future']){
				$sql2 = $con->sql_query("select type, 
										 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.do_type, do.branch_id as do_from_branch_id
										 from grr_items gi
										 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
										 left join do on do.do_no=gi.doc_no and gi.type='DO'
										 where gi.grr_id = $r[grr_id] and gi.branch_id = $branch_id
										 group by type_asc
										 order by type_asc asc
										 limit 1");
	
				$gi_info = $con->sql_fetchassoc($sql2);
				$con->sql_freeresult();
				$r['type'] = $gi_info['type'];
				$r['do_type'] = $gi_info['do_type'];
				$r['do_from_branch_id'] = $gi_info['do_from_branch_id'];
			}
	
		    
	
			if($r['type']!='DO'){
	            $count_this_grn = true;
			}else{  // document type = DO
			    if(!$r['do_type']||!$r['do_from_branch_id'])  $count_this_grn = true; // DO from outside
			    else{   // inter transfer DO
	                if($config['grn_do_hq2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']==1)   $count_this_grn = true;
	                if($config['grn_do_branch2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
	                if($config['grn_do_branch2hq_update_cost']&&$branch_id==1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
				}
	
			}
	    }		

	    if($count_this_grn){
			if($r['currency_code']){	// Store Foreign Currency Cost
				$data[$r['dt']]['currency_grn_cost'][$r['currency_code']]['grn_cost'] += $r['cost']+($r['cost']*$r['grr_tax_percent']/100);
			}
			
			$base_cost = $r['cost']*$r['currency_rate'];
			$base_tax = ($base_cost*$r['grr_tax_percent']/100);
	      	
	      	$data[$r['dt']]['grn_cost'] += $base_cost+$base_tax;
	      	$data[$r['dt']]['grn_tax'] += $base_tax;
			$data[$r['dt']]['grn_qty_to_divide'] += $r['qty'];
	      	$data[$r['dt']]['grn_cost_need_update'] = true;
	    }

		$links[$r['dt']][$r['grn_id']] = sprintf("<a target=_blank href=\"/goods_receiving_note.php?a=view&highlight_item_id=$sku_item_id&branch_id=$branch_id&id=%d\">GRN%05d</a>",trim($r['grn_id']),trim($r['grn_id']));
		if($r['currency_code']){
			$links[$r['dt']][$r['grn_id']] = "<span title='Exchange Rate ".$r['currency_rate']."'>[".$r['currency_code']."] ".$links[$r['dt']][$r['grn_id']]."</span>";
		}

        $data[$r['dt']]['links'] = join(", ",$links[$r['dt']]);
	}
	$con->sql_freeresult();
	ksort($data);
	reset($data);

	$qty = 0;
	$con->sql_query("select * from sku_items where id = $sku_item_id", false, false);
	$t=$con->sql_fetchassoc();
	
	$cost = doubleval($t['cost_price']);
	$avg_cost = $cost;
	$avg_total_cost = 0;
	$avg_total_qty = 0;

	$last3mth = date('Y-m-d', strtotime("-3 month"));
	$l90d_grn = 0;
	$l90d_pos = 0;
	$last1mth = date('Y-m-d', strtotime("-1 month"));
	$l30d_grn = 0;
	$l30d_pos = 0;
	
	$global_cost_decimal_points=$config['global_cost_decimal_points'];
	$global_qty_decimal_points=$config['global_qty_decimal_points'];

	print "<h3>$t[description]</h3>";
	print "<h4>Costing method: <u>Last Cost by Individual SKU (No Parent & Childs Calculation)</u></h4>
	<ul>
		<li> Stock Take cost replace Last Cost and Average Cost</li>
		<li> GRN cost replace Last Cost and calculates Average Cost</li>
		<li> Average Cost is calculated as:<br />
			 if balance quantity <= 0,<br />
			 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Average Cost = GRN cost<br />
			 otherwise<br />
			 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Average Cost = (Total GRN+Work Order cost) / (Total GRN+Work Oder Qty) (since the previous reset)</li>
	";
	
	if($no_inventory == 'yes'){
		print '<li> This sku is marked as "No Inventory", stock balance will always show as "N/A".</li>';
	}
	if($config['foreign_currency']){
		print '<li>'.$LANG['BASE_CURRENCY_CONVERT_NOTICE'].'</li>';
	}
	print "</ul>";
		
	
	
	print "<table width=100% class=report_table><tr class=header><th>Date</th><th>Type</th><th>Last<br />Cost</th><th>Average<br />Cost</th><th>Qty B/F</th><th>IN</th><th>Balance (C/F)</th><th>Link</th></tr>";
	
	$has_opening_cost = false;
	if($sessioninfo['branch_type'] != "franchise"){
		print  "<tr><td>-</td><td>Masterfile / Opening</td><td align=right>".number_format($cost,$global_cost_decimal_points)."</td><td align=right>".number_format($avg_cost,$global_cost_decimal_points)."</td><td align=right>-</td><td align=right>-</td><td align=right>-</td><td>&nbsp;</td></tr>";
		$has_opening_cost = true;
	}
	
	if($data){
		foreach ($data as $d => $t)
		{
			$hq_cost_changed = false;
			if ($d >= $last3mth)
			{
				$l90d_grn += $t['grn'];
				$l90d_pos += $t['pos'];
			}
			if ($d >= $last1mth)
			{
				$l30d_grn += $t['grn'];
				$l30d_pos += $t['pos'];
			}
			//print "$d ";
			//print "<br>";
			if (isset($t['stock_check']))
			{
				
				if ($t['stock_check_cost']==0) $t['stock_check_cost']=$cost*$t['stock_check'];
				//print "\tSCHK: $t[stock_check] @ ".($t['stock_check']?$t['stock_check_cost']/$t['stock_check']:0);
				//print "<br>";
				$avg_total_qty = $t['stock_check'];
				if ($t['stock_check']>0)
				{
					//$avg_total_cost = $t['stock_check_cost'];
					if($config['consignment_modules']&&$branch_id>1){
						//get_hq_cost($sku_item_id, $d, $hq_cost, $hq_avg_cost, $master_cost);
						//$cost = $hq_cost;
						//$avg_cost = $hq_avg_cost;
						$avg_total_cost = $avg_total_qty * $avg_cost;
					}else{
						$cost =  round($t['stock_check_cost'] / $t['stock_check'], 5);
						//$avg_cost =  round($avg_total_cost/$avg_total_qty, 5);mc rasamas 25552
						//$avg_total_cost = $t['stock_check_cost'];
						$avg_total_cost = $avg_total_qty * $avg_cost;
					}
					//$cost =  $t['stock_check_cost'] / $t['stock_check'];
					//$avg_cost =  $avg_total_cost / $avg_total_qty;
				}
				else
				{
					//$cost =  0;
					//$avg_cost = 0;
					
				}

				$cf = $qty;
				$qty = $t['stock_check'];
				
				if($cf<=0 || $qty<=0){
					$avg_cost =  $cost;
					$avg_total_cost = $avg_total_qty*$avg_cost;
				}
					
				print "<tr><td>$d</td><td>Stock Take</td><td align=right>".number_format($cost,$global_cost_decimal_points)."</td><td align=right>".number_format($avg_cost,$global_cost_decimal_points)."</td><td align=right>".number_format($cf,$global_qty_decimal_points)."</td><td align=right>-</td><td align=right>".number_format($qty,$global_qty_decimal_points)."</td><td>&nbsp;</td></tr>";
			}
			if ($t['grn'])
			{

				//print "\tGRN: $t[grn] @ ".($t['grn_cost']/$t['grn']);
				//print "<br>";
				$grn_tax = 0;
				if($config['consignment_modules']&&$branch_id>1){
					// branch no need update cost if is consignment
				}elseif($t['grn_cost_need_update']){
					if ($qty<=0)	// reset to GRN cost if stock level below or equal zero
					{
						//print "\treset AVG_COST";
						//print "<br>";
						$avg_total_cost = $t['grn_cost'];
						$avg_total_qty = $t['grn_qty_to_divide'];
					}
					else
					{
						$avg_total_cost += $t['grn_cost'];
						$avg_total_qty += $t['grn_qty_to_divide'];
					}

					if($t['grn_qty_to_divide']){
						if($t['grn_cost'])	$cost = round($t['grn_cost'] / $t['grn_qty_to_divide'],5);
						if($t['grn_tax'])	$grn_tax = round($t['grn_tax'] / $t['grn_qty_to_divide'],5);
						if($no_inventory=='yes')    $avg_cost = $cost;
						else{
							// old
							$avg_cost =  $avg_total_cost / $avg_total_qty;
						}	
					}
				}
				//print "grn_qty_to_divide = $t[grn_qty_to_divide], avg_cost=$avg_cost, avg_total_cost=$avg_total_cost, avg_total_qty=$qty<br />";
				$cf = $qty;
				$qty += $t['grn'];
				print "<tr><td>$d</td><td>GRN</td>";
				
				// Last Cost
				$str_cost = number_format($cost, $global_cost_decimal_points);
				print "<td align=right>";
				if($t['currency_grn_cost']){
					print "<span class='converted_base_amt'>".$str_cost."*</span>";
				}else{
					print $str_cost;
				}
				if($grn_tax>0){
					print "<br /><span class='small'>Incl Tax ".number_format($grn_tax, $global_cost_decimal_points)."</span>";
				}
				print "</td>";
				
				// Average Cost
				print "<td align=right>".number_format($avg_cost, $global_cost_decimal_points)."</td>";
				print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($cf, $global_qty_decimal_points))."</td>";
				print "<td align=right>".number_format($t['grn'], $global_qty_decimal_points)."</td>";
				print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($qty, $global_qty_decimal_points))."</td>";
				print "<td class=small>$t[links]</td></tr>";
			}
			
			if(isset($work_order_data[$d])){
				//print "$d<br>";
				//print_r($work_order_data[$d]);
				
				foreach($work_order_data[$d] as $wo_key=>$wo){
					$cf = $qty;
					$qty += $wo['qty'];
					$cost = round($wo['cost'], $global_cost_decimal_points);
					$avg_total_cost += round($wo['cost'], $global_cost_decimal_points);
					$avg_total_qty += $wo['qty'];
					$avg_cost =  $avg_total_cost / $avg_total_qty;
					
					print "<tr><td>$d</td><td>Work Order</td>";
					print "<td align=right>".number_format($cost, $global_cost_decimal_points)."</td><td align=right>".number_format($avg_cost, $global_cost_decimal_points)."</td>";
					print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($cf, $global_qty_decimal_points))."</td>";
					print "<td align=right>".number_format($wo['qty'], $global_qty_decimal_points)."</td>";
					print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($qty, $global_qty_decimal_points))."</td>";
					print "<td class=small>".$wo['link']."</td></tr>";
				}				
			}
			if ($t['gra']) {
				//print "\tGRA: $t[gra]";
				$qty -= $t['gra'];
			}
			if ($t['pos']) {
				//print "\tSold: $t[pos]";
				$qty -= $t['pos'];
			}
			if ($t['do']) {
				//print "\tDO: $t[do]";
				$qty -= $t['do'];
			}
			if ($t['adj']) {
				//print "\tAdj: $t[adj]";
				$qty += $t['adj'];
			}
			if($t['hq_cost_changed']){
				if($cost!=$t['hq_cost_changed']){
					$hq_cost_changed = true;
					$cost = $t['hq_cost_changed'];
					if($no_inventory=='yes')    $avg_cost = $cost;
					else	$avg_cost = $t['hq_avg_cost_changed'];
					$avg_total_cost = $avg_cost * $avg_total_qty;
					print "<tr><td>$d</td><td>HQ Cost Changed</td><td align=right>".number_format($cost,$global_cost_decimal_points)."</td><td align=right>".number_format($avg_cost,$global_cost_decimal_points)."</td><td align=right>&nbsp;</td><td align=right>&nbsp;</td><td align=right>".number_format($qty,$global_qty_decimal_points)."</td><td class=small>&nbsp;</td></tr>";
				}

			}

			//if ($qty <= 0) $avg_cost = 0;
			//print "\tBalance: $qty\tCost: $cost\tAvg_Cost: $avg_cost ($avg_total_cost / $avg_total_qty)\n";
			//print "<br>";
			if (isset($t['grn']) || isset($t['stock_check']) || ($hq_cost_changed&&$config['consignment_modules'])){
				//print("insert into sku_items_cost_history (branch_id, sku_item_id, grn_cost, avg_cost, qty, date) values ($branch_id, $sku_item_id, $cost, $avg_cost, $qty, '$d')\n");
			}

			$last_date = $d;
		}
	}elseif(!$data && !$has_opening_cost){
		print "<tr align=\"center\"><td colspan=\"8\">History not found</td></tr>";
	}

	print "</table>";
}

function get_hq_cost($sku_item_id, $date, &$cost, &$avg_cost, $master_cost){
	global $con;

	$con->sql_query("select * from sku_items_cost_history where branch_id=1 and sku_item_id='$sku_item_id' and date<='$date' order by date desc limit 1");
	$sich = $con->sql_fetchassoc();
	if($sich){
        $cost = $sich['grn_cost'];
        $avg_cost = $sich['avg_cost'];
	}else{
        $cost = $master_cost;
        $avg_cost = $master_cost;
	}
	$con->sql_freeresult();
}

function size_color(){
	global $con, $smarty,$sessioninfo, $config;
	
	$global_cost_decimal_points=$config['global_cost_decimal_points'];
	$global_qty_decimal_points=$config['global_qty_decimal_points'];
	
	//set header used
	$size[]="&nbsp;";
	$size[]="&nbsp;";
	$br_filter='';

	if (BRANCH_CODE!='HQ')	$br_filter=" and branch.id=".$sessioninfo['branch_id'];
	else{
		$brsql = "select id,code from branch where active=1 order by sequence,code";
		$brsource=$con->sql_query($brsql);
		while($br = $con->sql_fetchassoc($brsource)){
			$branches[$br['id']]=$br['id'];
			$br_codes[$br['code']]=$br['code'];
		}
		$con->sql_freeresult();
		
		$br_filter=" and branch_id in (".join(',',$branches) .")";
	}

	//get color and size
	$sql = "select si.id, si.color, si.size, si.cost_price as grn_cost
			from sku_items si
			where si.sku_id=(select si2.sku_id from sku_items si2 where si2.id=".$_REQUEST['sku_item_id'].") and si.active=1";//print "$sql<br /><br />";

	$run_sql = $con->sql_query($sql);

	while($r = $con->sql_fetchassoc($run_sql)){

		$sid=$r['id'];
		$col=$r['color'];
     	$siz=$r['size'];
   	    $master_cost=$r['grn_cost'];

		//check whther size or color is empty
		if (empty($col) || $col=="" || is_null($col))  continue; //$c="empty";
		if (empty($siz) || $siz=="" || is_null($siz))  continue; //$s="empty";

		//get stock
		$sql2 = "select branch.code, sum(sic.qty) as qty, sic.grn_cost
				from sku_items_cost sic
				left join branch on sic.branch_id=branch.id
				where sic.sku_item_id=$sid $br_filter
				group by branch.code, sic.sku_item_id";//print "$sql2<br /><br />";

		$run_sql2 = $con->sql_query($sql2);


		if ($con->sql_numrows($run_sql2)<=0){

			$qty=0;

			//set qty to zero
			foreach ($br_codes as $bcode){
				$quantity[$bcode][$siz][$col]['qty'] += $qty;
				$quantity[$bcode]['Total'][$col]['qty'] += $qty;
				$quantity[$bcode][$siz]['Total']['qty'] += $qty;
				$quantity[$bcode]['Total']['Total']['qty'] += $qty;

				$cost[$bcode][$siz][$col]['cost'] = $master_cost;
			}
			
   			if (!$color_exist[$col]){
				$color[]= $col;
                $color_exist[$col]=1;
			}

			if (!$size_exist[$siz]){
				$size[]= $siz;
                $size_exist[$siz]=1;
			}
		}else{
			while($r2 = $con->sql_fetchrow($run_sql2)){

				if ($r2['grn_cost'])
		     		$grn_cost=$r2['grn_cost'];
				else
				    $grn_cost=$master_cost;

				$qty=$r2['qty'];
				$code=$r2['code'];

				//check empty or null
				if (empty($qty) || $qty=="" || is_null($qty)) $qty=0;

				if (!$color_exist[$col]){
					$color[]= $col;
					
	                $color_exist[$col]=1;
				}

				if (!$size_exist[$siz]){
					$size[]= $siz;
	                $size_exist[$siz]=1;
				}

				$quantity['All'][$siz][$col]['qty'] += $qty;
				$quantity['All']['Total'][$col]['qty'] += $qty;
				$quantity['All'][$siz]['Total']['qty'] += $qty;
				$quantity['All']['Total']['Total']['qty'] += $qty;
				$cost['All'][$siz][$col]['cost'] = "-";
				
				$quantity[$code][$siz][$col]['qty'] += $qty;
				
				//print "quantity[$code][$siz][$col]['qty'] add $qty<br />";
				$ex[$code][$siz][$col]++;
				$ey[$code][$siz][$col][] = $qty;
				
				$quantity[$code]['Total'][$col]['qty'] += $qty;
				$quantity[$code][$siz]['Total']['qty'] += $qty;
				$quantity[$code]['Total']['Total']['qty'] += $qty;
				
				$cost[$code][$siz][$col]['cost'] = number_format($grn_cost,$global_cost_decimal_points);
				$ez[$code][$siz][$col][] = number_format($grn_cost,$global_cost_decimal_points);
				
				$tt[$code][$siz][$col] = array($code,$siz,$col,$sid);
				
				//Set qty and cost to default if no data
				foreach ($br_codes as $bcode){
					$quantity[$bcode][$siz][$col]['qty'] += 0;
					$quantity[$bcode]['Total'][$col]['qty'] += 0;
					$quantity[$bcode][$siz]['Total']['qty'] += 0;
					$quantity[$bcode]['Total']['Total']['qty'] += 0;

					if (!($cost[$bcode][$siz][$col]['cost']))
						$cost[$bcode][$siz][$col]['cost'] = $master_cost ? number_format($master_cost,$global_cost_decimal_points) : "";

				}
	//				$sku_item_id[$siz][$col]=$r['id'];
			}
		}
	}
	$con->sql_freeresult();

//	print "<pre>";print_r($cost);print "</pre>";
	//print "<pre>";print_r($ex);print "</pre>";

	$color[]= "Total";
	$size[]= "Total";

	$get_descrip="select description from sku_items si where si.sku_id=(select sku_id from sku_items where id=".$_REQUEST['sku_item_id'].") and is_parent=1";
	$run_descrip = $con->sql_query($get_descrip);
	while ($descrip = $con->sql_fetchassoc($run_descrip))
	{
        $description=$descrip['description'];
	}
	$con->sql_freeresult();

	print "<h2>$description</h2>";

	if (!$color_exist){
		print "<h4>No Data</h4>";
		return;
	}
	if (!$size_exist){
		print "<h4>No Data</h4>";
		return;
	}

	if ($quantity)
	{
		foreach ($quantity as $code => $rest )
		{
	        print "<h3>$code</h3>";


	 		print "<table class='input_matrix' cellspacing=2 border=0>";

			foreach ($size as $no_size => $row_size)
			{

			    print "<tr>";

				if ($no_size==0){
					print "<td bgcolor='#00dddd' rowspan=2>".$row_size."</td>";
			    }

				elseif ($no_size>=2){
					$cc="alt='size'";
	//				if ($row_size=="empty") print "<td ".$cc.">(no_size)</td>";
					print "<td ".$cc.">".$row_size."</td>";
				}

				foreach ($color as $no_color => $col_color)
				{
				    if ($sessioninfo['privilege']['SHOW_COST'])   $sell_on="colspan=2";

				    if ($no_size==0){
				    //    if ($col_color=="empty")	print "<td ".$sell_on." alt='color'>(no_color)</td>";
						if ($col_color!='Total')
							print "<td ".$sell_on." alt='color'>$col_color</td>";
						else
							print "<td alt='color'>$col_color</td>";
					}
					elseif ($no_size==1){

						print "<td alt='color'>Stock<br />Balance</td>";
					    if ($sessioninfo['privilege']['SHOW_COST'] && $col_color!='Total')
							print "<td alt='color'>Cost<br />Price</td>";
					}
					else{

					    //Check whether quantity is negatif
				        if ($rest[$row_size][$col_color]['qty'] < 0 )   $qty_color="style='color:#f00;'";
				    	    else $qty_color="style='color:#222;'";
				        if ($cost[$code][$row_size][$col_color]['cost'] < 0 )   $cost_color="style='color:#f00;'";
				    	    else $cost_color="style='color:#222;'";

							$repeated = ($ex[$code][$row_size][$col_color] > 1) ? true : false;
							
							$costlist = $qtylist = $tooltip1 = $tooltip2 = '';
							if ($repeated)
							{
								$ttdata = $tt[$code][$row_size][$col_color];
								
								$sql3 = "select branch.code, si.id, si.sku_item_code, si.description, sic.qty, sic.grn_cost from sku_items_cost sic left join sku_items si on sic.sku_item_id = si.id left join branch on sic.branch_id = branch.id where si.sku_id = (select sku_id from sku_items where id = ".mi($ttdata[3]).") and size=".ms($ttdata[1])." and color=".ms($ttdata[2])." and branch.code = ".ms($ttdata[0]);
								$res3 = $con->sql_query($sql3);
								while ($row3 = $con->sql_fetchassoc($res3))
								{
									$tooltip1 .= ($row3['qty'].' - '.$row3['sku_item_code'].' '.'('.$row3['description'].')'."\n");
									$tooltip2 .= (number_format($row3['grn_cost'],$global_cost_decimal_points).' - '.$row3['sku_item_code'].' '.'('.$row3['description'].')'."\n");
								}
								
								$min = min($ey[$code][$row_size][$col_color]);
								$max = max($ey[$code][$row_size][$col_color]);
								$qtylist = ($min == $max) ? $min : "<a style=\"text-decoration: none !important;\" title=\"$tooltip1\"><span style=\"color:blue;font-size:0.8em;\"><b>$min</b> to <b>$max</b></font></a>";
								
								$min = min($ez[$code][$row_size][$col_color]);
								$max = max($ez[$code][$row_size][$col_color]);
								$costlist = ($min == $max) ? $min : "<a style=\"text-decoration: none !important;\" title=\"$tooltip2\"><span style=\"color:blue;font-size:0.8em;\"><b>$min</b> to <b>$max</b></font></a>";
								
							}
							
							print "<td nowrap class='r' ".$qty_color." alt='data'>".($qtylist ? $qtylist : ($rest[$row_size][$col_color]['qty']))."</td>"; //xxx
							if ($sessioninfo['privilege']['SHOW_COST'] && $col_color!='Total')
							{
								print "<td nowrap class='r' ".$cost_color." alt='data'>".($costlist ? $costlist : ($cost[$code][$row_size][$col_color]['cost']))."</td>"; //xxx
							}

					}
				}

			    print "</tr>";

			}

			print "</table>";
			print "<br />";
		}
	}
}

function ajax_size_color_matrix($id, $branch_id,$type){
	global $con, $smarty,$sessioninfo,$config;

	$form=$_REQUEST;

	$si_id=intval($form['sku_item_id']);
	if ($type=="po"){
		$tbl="tmp_po_items";
		$filter="po_id=".$id;
		$config_set = $config['po_item_allow_duplicate'];
		if($form['deliver_to'])	$branch_code = join(",",$form['deliver_to']);
		else $branch_code = $form['branch_id'];
	}
	elseif ($type=="do"){
		$tbl="tmp_do_items";
		$filter="do_id=".$id;
		$config_set = $config['do_item_allow_duplicate'];
		if($form['deliver_branch'])	$branch_code = join(",",$form['deliver_branch']);
		else $branch_code = $form['branch_id'];
	}

	if (!$config_set){
		if (isset($id)){
			$idlist=array();
			$q1=$con->sql_query("select sku_item_id from $tbl where branch_id=$branch_id and $filter and user_id=$sessioninfo[id]");
			while($r1=$con->sql_fetchrow($q1)){
				$idlist[]=$r1[0];
			}
			$con->sql_freeresult();
			if ($idlist)
				$idlist="and si.id not in (".join(",",$idlist).")";
			else
				$idlist='';
		}
		else $idlist='';

	}

	$size[]="&nbsp;";
	$size[]="&nbsp;";
	$items=array();
	$q2=$con->sql_query("select sku_id from sku_items where id=$si_id");

	$r2=$con->sql_fetchassoc($q2);
	$con->sql_freeresult();
	$sku_id=$r2['sku_id'];

	//get branch code
	$sql1 = "select id, code
			from branch
			where id in ($branch_code)
			order by id";

    $run_sql1 = $con->sql_query($sql1);
	while($r3 = $con->sql_fetchassoc($run_sql1)){
        $branch[$r3['id']]=$r3['code'];
	}
	$con->sql_freeresult();

	//get sku item list
	$sql2 = "select id, color, size, description, block_list, is_parent
			from sku_items si
			where si.active=1 and si.sku_id=$sku_id  $idlist
			group by color,size
			order by color,size";

    $run_sql2 = $con->sql_query($sql2);

    $color=array();
	while($r4 = $con->sql_fetchassoc($run_sql2)){
		//check whther size or color is empty
  		if (empty($r4['color']) || $r4['color']=="" || is_null($r4['color']))  continue; //$c="empty";
			else $c=$r4['color'];

		if (empty($r4['size']) || $r4['size']=="" || is_null($r4['size']))  continue; //$s="empty";
			else $s=$r4['size'];

		//check block list for PO only
		if ($type=="po"){
			$block = unserialize($r4['block_list']);
		}

		foreach ($branch as $b_id => $b_code){
			if (isset($block[$b_id]))   continue;
			else $sku_item_id[$b_id][$s][$c]['id']=$r4['id'];
		}
	
		//avoid duplicate color and size
		if (!in_array($c,$color)) $color[]= $c;
		if (!in_array($s,$size)) $size[]= $s;
		if ($r4['is_parent']==1) $description=$r4['description'];
		else $description=$form['sku'];
	}
	$con->sql_freeresult();
	
    $smarty->assign('description',$description);
	$smarty->assign('type',$type);
    $smarty->assign('branch',$branch);
    $smarty->assign('size',$size);
    $smarty->assign('color',$color);
    $smarty->assign('quantity',$quantity);
    $smarty->assign('sku_item_id',$sku_item_id);

	$smarty->display("color_size_matrix.tpl");
}

function run_history_by_sku($sku){
	global $branch_id, $con, $hqcon, $verbose, $config, $selected_filter_from_date, $no_inventory, $sessioninfo, $smarty;
	
	$branch_id = intval($_REQUEST['branch_id']);
	$sku_id = mi($sku['sku_id']);
	$sku_item_id = $sku['id'];
	$sku_item_code = $sku['sku_item_code'];
	$avg_cost = $cost = $sku['cost_price'];
    $master_cost = doubleval($sku['cost_price']);
  
	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	$sku_update_cost_by_parent_child = mi($config['sku_update_cost_by_parent_child']);
	
	$si_info_list = array();
	$grn_data = array();
	$stock_take_data = array();
	$work_order_data = array();
	
	// get sid info
	$q_si = $con->sql_query("select si.id,si.packing_uom_id, uom.fraction as packing_uom_fraction, si.cost_price, si.selling_price, sku.is_bom, si.sku_item_code, si.mcode, si.artno, si.description, uom.code as packing_uom_code
	from sku_items si
	left join sku on sku.id=si.sku_id
	left join uom on uom.id=si.packing_uom_id
	where sku_id=$sku_id
	order by si.is_parent,si.id");
	while($r = $con->sql_fetchassoc($q_si)){
		$si_info_list[$r['id']]['info'] = $r;
		
		//$si_info_list[$r['id']]['display_last_cost'] = 
		$si_info_list[$r['id']]['cost'] = $si_info_list[$r['id']]['avg_cost'] = $r['cost_price'];
	}
	$con->sql_freeresult($q_si);
	$str_sid_list = trim(join(',', array_keys($si_info_list)));
	if(!$str_sid_list)	return false;	// no sku item id ?
	
	$data = array();
	
	
	// stock check
	$con->sql_query("select si.id as sid, sc.qty, sc.cost, sc.date 
	from stock_check sc 
	join sku_items si using (sku_item_code) 
	where si.id in ($str_sid_list) and branch_id = $branch_id 
	order by date");
	while($r=$con->sql_fetchassoc()){
		$data[$r['date']][$r['sid']]['stock_check'] += $r['qty'];
		//$data[$r['date']][$r['sid']]['stock_check_cost'] = $r['cost'];
		
		$c = trim(round($r['cost'], $config['global_cost_decimal_points']));
		if($c > 0){
			$stock_take_data[$r['date']]['item_list'][$r['sid']]['got_cost'][$c]['qty'] += $r['qty'];
		}else{
			$stock_take_data[$r['date']]['item_list'][$r['sid']]['no_cost']['qty'] += $r['qty'];
		}
	}
	$con->sql_freeresult();
	
	// POS
	$con->sql_query("select sku_item_id as sid, qty, date as dt 
	from sku_items_sales_cache_b$branch_id 
	where sku_item_id in ($str_sid_list)");
	while($r=$con->sql_fetchassoc()){
		$data[$r['dt']][$r['sid']]['pos'] = $r['qty'];
	}
	$con->sql_freeresult();
	
	// GRA
	$con->sql_query("select gra_items.sku_item_id as sid, sum(qty) as qty, date(return_timestamp) as dt 
	from gra_items 
	join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
	where gra_items.sku_item_id in ($str_sid_list) and gra_items.branch_id = $branch_id and gra.status=0 and gra.returned=1 group by sid, dt");
	while($r=$con->sql_fetchassoc())
	{
		$data[$r['dt']][$r['sid']]['gra'] = $r['qty'];
	}
	$con->sql_freeresult();
	
	//FROM DO
	$con->sql_query("select do_items.sku_item_id as sid, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as dt
from do_items
left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id
left join uom on do_items.uom_id=uom.id
where do_items.sku_item_id in ($str_sid_list) and do_items.branch_id = $branch_id and do.approved=1 and do.checkout=1 and do.status=1 and do.active=1 group by sid,dt");
	while($r=$con->sql_fetchassoc()){
		$data[$r['dt']][$r['sid']]['do'] = $r['qty'];
	}
	$con->sql_freeresult();
		
	//FROM ADJUSTMENT
	/*$con->sql_query("select adjustment_items.sku_item_id as sid, sum(qty) as qty, adjustment_date as dt
from adjustment_items
left join adjustment on adjustment.id=adjustment_items.adjustment_id and adjustment.branch_id=adjustment_items.branch_id
where adjustment_items.sku_item_id in ($str_sid_list) and adjustment_items.branch_id = $branch_id and adjustment.approved=1 and adjustment.status=1 and adjustment.active=1 group by sid, dt");*/
	$con->sql_query("select adji.adjustment_id, adji.sku_item_id as sid, adj.adjustment_date as dt, adj.module_type, sum(if(adji.qty>0,adji.qty,0)) as positive_qty, sum(if(adji.qty<0,adji.qty,0)) as negative_qty, wo.id as wo_id, woi.finish_cost, woi.line_total_finish_cost, wo.wo_no
	from adjustment_items adji
	left join adjustment adj on adj.id=adji.adjustment_id and adj.branch_id=adji.branch_id
	left join work_order wo on wo.branch_id=adj.branch_id and wo.adj_id=adj.id and adj.module_type='work_order' and wo.active=1 and wo.status=1 and wo.completed=1
	left join work_order_items_in woi on woi.branch_id=wo.branch_id and woi.work_order_id=wo.id and woi.sku_item_id=adji.sku_item_id
	where adji.sku_item_id in ($str_sid_list) and adji.branch_id = $branch_id and adj.approved=1 and adj.status=1 and adj.active=1
	group by adjustment_id, sid, dt, module_type
	order by dt, adjustment_id");
	while($r=$con->sql_fetchassoc()){
		if($r['module_type'] == 'work_order' && $r['wo_id']){
			if($r['negative_qty']<0){	// negative is transfer out, not affect cost
				$data[$r['dt']][$r['sid']]['adj'] += $r['negative_qty'];
			}
			if($r['positive_qty']>0){	// positive is transfer in, will affect cost
				$tmp = array();
				$tmp['qty'] = $r['positive_qty'];
				$tmp['cost'] = $r['finish_cost'];
				$tmp['total_cost'] = $r['line_total_finish_cost'];
				$tmp['link'] = sprintf("<a target=_blank href=\"/work_order.php?a=view&highlight_in_sid=$r[sid]&branch_id=$branch_id&id=%d\">%s</a>",trim($r['wo_id']),trim($r['wo_no']));
				$data[$r['dt']][$r['sid']]['wo_list'][] = $tmp;
				
				// store by grn data
				$wo_key = $branch_id."_".$r['wo_id'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['qty'] += $tmp['qty'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['cost'] += $tmp['cost'];
				$work_order_data[$r['dt']][$wo_key]['item_list'][$r['sid']]['total_cost'] += $tmp['total_cost'];
				$work_order_data[$r['dt']][$wo_key]['link'] = $tmp['link'];	
			}	
		}else{
			// normal adjustment
			$data[$r['dt']][$r['sid']]['adj'] += $r['positive_qty']+$r['negative_qty'];
		}		
	}
	$con->sql_freeresult();
	
	// grn
	$sql = "select grn_items.sku_item_id as sid, (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost, grr.rcv_date as dt, grn.grr_id, grn.is_future,grn_items.grn_id,
	gi.type, do.do_type, do.branch_id as do_from_branch_id,grr.vendor_id, grr.currency_code, if(grr.currency_rate<0,1,grr.currency_rate) as currency_rate, grr.tax_percent as grr_tax_percent
	from grn_items
	left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
	left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
	left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
	left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
	left join do on do.do_no=gi.doc_no and gi.type='DO'
	where grn_items.sku_item_id in ($str_sid_list) and grn_items.branch_id = $branch_id and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 order by grr.rcv_date, grr.id";

	$sql1 = $con->sql_query($sql);

	while($r=$con->sql_fetchassoc($sql1)){
		//$data[$r['dt']]['grn'] += $r['qty'];
	    $count_this_grn = false;
	    
		//if($r['qty']<=0)	continue;
		
		if($r['qty'] > 0){
			if($r['is_future']){
				$sql2 = $con->sql_query("select type, 
									 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.do_type, do.branch_id as do_from_branch_id
									 from grr_items gi
									 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
									 left join do on do.do_no=gi.doc_no and gi.type='DO'
									 where gi.grr_id = $r[grr_id] and gi.branch_id = $branch_id
									 group by type_asc
									 order by type_asc asc
									 limit 1");
	
				$gi_info = $con->sql_fetchassoc($sql2);
				$con->sql_freeresult($sql2);
				$r['type'] = $gi_info['type'];
				$r['do_type'] = $gi_info['do_type'];
				$r['do_from_branch_id'] = $gi_info['do_from_branch_id'];
			}
	
		    
	
		    if($r['type']!='DO'){
	            $count_this_grn = true;
			}else{  // document type = DO
			    if(!$r['do_type']||!$r['do_from_branch_id'])  $count_this_grn = true; // DO from outside
			    else{   // inter transfer DO
	                if($config['grn_do_hq2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']==1)   $count_this_grn = true;
	                if($config['grn_do_branch2branch_update_cost']&&$branch_id>1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
	                if($config['grn_do_branch2hq_update_cost']&&$branch_id==1&&$r['do_from_branch_id']>1)   $count_this_grn = true;
				}
			}
		}
		
	    /*if(($config['grn_do_transfer_update_cost']&&$r['type']=='DO'&&$r['do_type']=='transfer')||($config['grn_do_all_update_cost']&&$r['type']=='DO')){
	      $count_this_grn = true;
		}else{
		    if(($config['grn_do_branch_update_cost']&&$r['type']=='DO'&&$branch_id>1)||($r['type']!='DO')){
	        	$count_this_grn = true;
	      	}
		}*/

		$tmp = array();
		$tmp['cost'] = $r['cost'] * $r['currency_rate'];	// this cost alrdy multiply qty
		$tmp['tax'] = $tmp['cost'] * $r['grr_tax_percent'] / 100;	// calculate tax
		$tmp['cost_before_tax'] = $tmp['cost'];	// capture the cost before tax
		$tmp['cost'] += $tmp['tax'];	// add tax into cost
		$tmp['qty'] = $r['qty'];
		$tmp['count_this_grn'] = $count_this_grn;
		
		$tmp['link'] = sprintf("<a target=_blank href=\"/goods_receiving_note.php?a=view&highlight_item_id=$r[sid]&branch_id=$branch_id&id=%d\">GRN%05d</a>",trim($r['grn_id']),trim($r['grn_id']));
		
		$data[$r['dt']][$r['sid']]['grn_list'][] = $tmp;	// grn hv to save 1 by 1
		
		// store by grn data
		$grn_key = $branch_id."_".$r['grn_id'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['qty'] += $tmp['qty'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_cost'] += $tmp['cost'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_tax'] += $tmp['tax'];
		$grn_data[$r['dt']][$grn_key]['item_list'][$r['sid']]['total_cost_before_tax'] += $tmp['cost_before_tax'];
		$grn_data[$r['dt']][$grn_key]['count_this_grn'] = $count_this_grn;
		$grn_data[$r['dt']][$grn_key]['link'] = $tmp['link'];
		
		if($r['currency_code']){
			$grn_data[$r['dt']][$grn_key]['ori_cost'] = $r['cost'];
			$grn_data[$r['dt']][$grn_key]['ori_tax'] = $r['cost'] * $r['grr_tax_percent'] / 100;
			$grn_data[$r['dt']][$grn_key]['currency_code'] = $r['currency_code'];
			$grn_data[$r['dt']][$grn_key]['currency_rate'] = $r['currency_rate'];
		}
	}
	$con->sql_freeresult($sql1);
	//print_r($grn_data);
	//print_r($stock_take_data);
	
	////////// START CALCULATION ////////////
	$global_cost_decimal_points=$config['global_cost_decimal_points'];
	$global_qty_decimal_points=$config['global_qty_decimal_points'];

	//print "<h3>$sku[description]</h3>";
	//print "<table width=100% class=report_table><tr class=header><th>Date</th><th>Type</th><th>Last<br />Cost</th><th>Average<br />Cost</th><th>Qty B/F</th><th>IN</th><th>Balance (C/F)</th><th>Link</th></tr>";
	
	$has_opening_cost = false;
	if($sessioninfo['branch_type'] != "franchise"){
		//print  "<tr><td>-</td><td>Masterfile / Opening</td><td align=right>".number_format($cost,$global_cost_decimal_points)."</td><td align=right>".number_format($avg_cost,$global_cost_decimal_points)."</td><td align=right>-</td><td align=right>-</td><td align=right>-</td><td>&nbsp;</td></tr>";
		$has_opening_cost = true;
	}
	
	ksort($data);
	reset($data);
	$display_data = array();
	
	// master
	foreach($si_info_list as $sid => $r){
		$display_data['master'][$sid]['cost'] = $r['cost'];
	}
	
	if($data){
		foreach ($data as $d => $daily_sid_list){	// loop for each transaction date
		    $d2 = date('Y-m-d',strtotime('-1 day',strtotime($d)));
			$got_cost_changed = false;
			$got_qty_changed = false;
			
			$got_stock_take = false;
			$this_sku_got_stock_take = false;
			$lc = $cf = 0;

			// Old Stock Take Style
			/*foreach($si_info_list as $sid => $si_info){
				if(isset($daily_sid_list[$sid])){
					
					$t = $daily_sid_list[$sid];
				//foreach($daily_sid_list as $sid => $t){
					// got stock check for this item
					
					if($sessioninfo['id'] == 1){
						print "$d<br>";
						if($d == '2017-03-09'){
							print "is 2017-03-09";
							print_r($t);
						}
					}
					
					if (isset($t['stock_check'])){
						$cf = $si_info_list[$sid]['qty'];
						
						// stock check with zero cost, replace it with latest cost
						if ($t['stock_check_cost']==0) $t['stock_check_cost'] = $si_info_list[$sid]['cost'] * $t['stock_check'];				
						
						// update stock check qty into qty
						$si_info_list[$sid]['qty'] = $t['stock_check'];
								
						// get each qty cost
						if($t['stock_check']){
							$si_info_list[$r['id']]['display_last_cost'] = $si_info_list[$sid]['avg_cost'] = $si_info_list[$sid]['cost'] = $t['stock_check_cost']/$t['stock_check'];
						}				
						
						$params = array();
						if($sku_use_avg_cost_as_last_cost)	$params['update_avg_pcs_cost'] = 1;
						else	$params['update_use_item_grn_cost'] = array('sid'=>$sid, 'cost' => $si_info_list[$sid]['cost']);
						
						// update all child cost 
						//get_and_update_parent_child_total($si_info_list, $params);
						
						$got_stock_take = true;
						if($sid == $sku_item_id){
							$this_sku_got_stock_take = true;
							if ($t['stock_check']) $tmp_lc = $t['stock_check_cost']/$t['stock_check'];
							else $tmp_lc = 0;
							
							if (!$lc = $tmp_lc) {
								if (!$lc = $last_grn_cost) {
									if (!$lc = $last_stock_take) {
										$lc = $last_stock_take = $cost;
									}
									else {
										$last_grn_cost = 0;
										$last_stock_take = $lc;
									}
								}
								else {
									$last_grn_cost = 0;
									$last_stock_take = $lc;
								}
							}
							else {
								$last_grn_cost = 0;
								$last_stock_take = $lc;
							}
							
							//print "<tr><td>$d</td><td>Stock Take</td><td align=right>".number_format($lc,$global_cost_decimal_points)."</td><td align=right>".number_format($si_info_list[$sid]['avg_cost'],$global_cost_decimal_points)."</td><td align=right>".number_format($cf,$global_qty_decimal_points)."</td><td align=right>-</td><td align=right>".number_format($si_info_list[$sid]['qty'],$global_qty_decimal_points)."</td><td>&nbsp;</td></tr>";					
						}
					}
				}
			}
			if($got_stock_take){			
				// update all child cost 
				get_and_update_parent_child_total($si_info_list, $params);
				
				
				print "<tr><td>$d</td><td>Stock Take".(!$this_sku_got_stock_take ? " (other sku)" : "")."</td><td align=right>".number_format($lc,$global_cost_decimal_points)."</td><td align=right>".number_format($si_info_list[$sku_item_id]['avg_cost'],$global_cost_decimal_points)."</td><td align=right>".number_format($cf,$global_qty_decimal_points)."</td><td align=right>-</td><td align=right>".number_format($si_info_list[$sku_item_id]['qty'],$global_qty_decimal_points)."</td><td>&nbsp;</td></tr>";
			}*/
			
			// New Stock Take Style
			if(isset($stock_take_data[$d])){	// this date got stock take
				//$cf = $si_info_list[$sku_item_id]['qty'];	// get the qty before stock take
				
				calculate_group_sku_stock_take($d, $si_info_list, $stock_take_data[$d], $display_data);
				
				//print "<tr><td>$d</td><td>Stock Take".(!isset($stock_take_data[$d]['item_list'][$sku_item_id]) ? " (other sku)" : "")."</td><td align=right>".number_format($si_info_list[$sku_item_id]['cost'], $global_cost_decimal_points)."</td><td align=right>".number_format($si_info_list[$sku_item_id]['avg_cost'], $global_cost_decimal_points)."</td><td align=right>".number_format($cf, $global_qty_decimal_points)."</td><td align=right>-</td><td align=right>".number_format($si_info_list[$sku_item_id]['qty'],$global_qty_decimal_points)."</td><td>&nbsp;</td></tr>";
			}
			
			// old GRN style
			/*foreach($si_info_list as $sid => $si_info){
				if(isset($daily_sid_list[$sid])){
					$t = $daily_sid_list[$sid];
				//foreach($daily_sid_list as $sid => $t){
					// grn
					if ($t['grn_list'])
					{
						foreach($t['grn_list'] as $grn){
							$cf = $si_info_list[$sku_item_id]['qty'];
							
							// item total cost before include grn
							$item_total_cost = round($si_info_list[$sid]['qty'] * $si_info_list[$sid]['avg_cost'], 5);
		
							$qty_b4_grn = $si_info_list[$sid]['qty'];
							
							if($grn['count_this_grn']){
								//if($sessioninfo['u']=='admin'){
									$params = array();
									$params['grn_item']['sid'] = $sid;
									$params['grn'] = $grn;
									calculate_group_sku_grn_avg_cost($si_info_list, $params);
								//}
							}										
							// increase qty
							$si_info_list[$sid]['qty'] += $grn['qty'];
							
							// this grn need update cost
							if($grn['count_this_grn']){	
								$params = array();
								if($sku_use_avg_cost_as_last_cost){
									$params['update_avg_pcs_cost'] = 1;							
								}	
								else{
									if($grn['qty']){
										$grn_cost = $grn['cost'] / $grn['qty'];
										if($grn_cost)	$si_info_list[$sid]['cost'] = $grn_cost;
									}
									$params['update_use_item_grn_cost'] = array('sid'=>$sid, 'cost' => $si_info_list[$sid]['cost']);
								}	
								
								if(!isset($si_info_list[$sku_item_id]['dsp_grn_cost']))	$si_info_list[$sku_item_id]['dsp_grn_cost'] = $si_info_list[$sku_item_id]['cost'];
								
								if($grn['qty']){
									$grn_cost = $grn['cost'] / $grn['qty'];
									if($grn_cost) {
										$si_info_list[$sid]['dsp_grn_cost'] = $grn_cost;
										if ($sku_item_id == $sid) $last_grn_cost = $grn_cost;
									}
									
									//$params['grn'][$sid]['grn_cost'] = $grn_cost;
									//$params['grn'][$sid]['qty'] = $grn['qty'];
								}
								
								// update all child cost 
								get_and_update_parent_child_total($si_info_list, $params);
								
								// display cost changed even from other parent/child
								print "<tr><td>$d</td><td>GRN ".($sku_item_id != $sid ? "(other sku)" : "")."</td><td align=right>".number_format($si_info_list[$sku_item_id]['dsp_grn_cost'],$global_cost_decimal_points)."</td><td align=right>".number_format($si_info_list[$sku_item_id]['avg_cost'],$global_cost_decimal_points)."</td>";
								print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($cf,$global_qty_decimal_points))."</td>";
								print "<td align=right>".number_format(($sid == $sku_item_id ? $grn['qty'] : 0),$global_qty_decimal_points)."</td>";
				
								print "<td align=right>".($no_inventory=='yes' ? 'N/A' : number_format($si_info_list[$sku_item_id]['qty'],$global_qty_decimal_points))."</td>";
								print "<td class=small>$grn[link]</td></tr>";
							}
						}				
					}
				}
			}*/
			
			// new GRN style
			if(isset($grn_data[$d])){	// this date got grn
				calculate_group_sku_grn($d, $si_info_list, $grn_data[$d], $display_data);
			}
			
			// Work Order
			if(isset($work_order_data[$d])){	// this date got work order
				calculate_group_sku_work_order($d, $si_info_list, $work_order_data[$d], $display_data);
			}
			
			foreach($daily_sid_list as $sid => $t){
				if ($t['gra']) {	
					$si_info_list[$sid]['qty'] -= $t['gra'];
				}
				if ($t['pos']) {		
					$si_info_list[$sid]['qty'] -= $t['pos'];
				}
				if ($t['do']) {	
					$si_info_list[$sid]['qty'] -= $t['do'];
				}
				if ($t['adj']) {
					$si_info_list[$sid]['qty'] += $t['adj'];
				}
			}
		}
	}elseif(!$data && !$has_opening_cost){
		//print "<tr align=\"center\"><td colspan=\"8\">History not found</td></tr>";
	}

	/*print "</table>
	<br />
	<h4>Costing method:</h4>
	<ul>
		<li> Stock Take cost replace Last Cost and Average Cost</li>
		<li> GRN cost replace Last Cost and calculates Average Cost</li>
		<li> Cost will update to parent/child based on packing uom fraction.</li>";
	if($config['sku_use_avg_cost_as_last_cost']){
		print "<li>Average cost between parent & child will replace as last cost</li>";
	}
	print "</ul>";*/
	
	//print_r($display_data);
	$smarty->assign('sku', $sku);
	$smarty->assign('no_inventory', $no_inventory);
	$smarty->assign('sku_item_id', $sku_item_id);
	$smarty->assign('si_info_list', $si_info_list);
	$smarty->assign('display_data', $display_data);
	$smarty->display('masterfile_sku.cost_history.tpl');
}

///////////// THIS IS OLD METHOD AND NO LONGER USE ///////////////
/*
function get_and_update_parent_child_total(&$si_info_list, $params = array()){
	global $config, $sessioninfo;
	$sku_use_avg_cost_as_last_cost = mi($config['sku_use_avg_cost_as_last_cost']);
	
	if(!$si_info_list)	return;
	$tmp_cron_txt = 'tmp_cron.txt';
	
	$update_avg_pcs_cost = mi($params['update_avg_pcs_cost']);
	
	if(isset($params['update_use_item_grn_cost']))	$update_use_item_grn_cost = $params['update_use_item_grn_cost'];
	
	$ret = array(
		'total_cost' => 0,
		'total_pcs' => 0,
		'avg_pcs_cost' => 0
	);
	
	foreach($si_info_list as $sku_item_id => $si_info){
		$add_qty = $si_info['qty'];
		$add_cost = $si_info['avg_cost'];
		
		if($si_info['qty']<=0){
			//if(isset($params['grn'][$sku_item_id])){	// this item got just receive grn
			//	$add_qty = $params['grn'][$sku_item_id]['qty'];
			//	$add_cost = $params['grn'][$sku_item_id]['grn_cost'];
			//}else	continue;
			continue;
		}
		
		$ret['total_pcs'] += $add_qty * $si_info['info']['packing_uom_fraction'];
		$ret['total_cost'] += $add_qty * $add_cost;
		
	}
	
	if($ret['total_pcs']){
		$ret['avg_pcs_cost'] = round($ret['total_cost'] / $ret['total_pcs'], 5);
	}
	
	if($sessioninfo['u']=='admin'){
		//print "before\n";
		//print_r($si_info_list);
		//print_r($ret);
		//print_r($params);
	}
	
	if($update_use_item_grn_cost || $update_avg_pcs_cost){
		if($update_use_item_grn_cost){	// update by using item latest grn cost
			$sid = mi($update_use_item_grn_cost['sid']);
			$grn_cost = $update_use_item_grn_cost['cost'];
			
			$pcs_cost = $grn_cost / $si_info_list[$sid]['info']['packing_uom_fraction'];
			
			foreach($si_info_list as $sku_item_id => $si_info){
				if($sku_item_id == $sid)	continue;
				
				$si_info_list[$sku_item_id]['cost'] = round($si_info['info']['packing_uom_fraction'] * $pcs_cost, 5);
			}
		}
		
		// avg
		foreach($si_info_list as $sku_item_id => $si_info){	// need update avg cost as well
			$last_cost = $si_info_list[$sku_item_id]['avg_cost'];

			if($si_info['info']['is_bom']) $si_info_list[$sku_item_id]['avg_cost'] = $si_info['dsp_grn_cost'];
			else $si_info_list[$sku_item_id]['avg_cost'] = round($si_info['info']['packing_uom_fraction'] * $ret['avg_pcs_cost'], 5);
			
			if($update_avg_pcs_cost){
				$si_info_list[$sku_item_id]['cost'] = $si_info_list[$sku_item_id]['avg_cost'];
			}
			
			if(!$si_info_list[$sku_item_id]['avg_cost']){
				$si_info_list[$sku_item_id]['avg_cost'] = $last_cost;	// use back the cost before update if all item is zero pcs
				if($update_avg_pcs_cost){
					$si_info_list[$sku_item_id]['cost'] = $si_info_list[$sku_item_id]['avg_cost'];
				}
			}
		}
	}
	
	if($sessioninfo['u']=='admin'){
		//print "after\n";
		//print_r($si_info_list);
		//print_r($ret);
	}
	
	return $ret;
}

function calculate_group_sku_grn_avg_cost(&$si_info_list, $params = array()){
	global $con, $sessioninfo, $config;
	
	if(!$params['grn'])	return;
	
	$group_pcs = 0;
	$group_total_avg_cost = 0;
	
	$grn_item_sid = $params['grn_item']['sid'];
	$grn = $params['grn'];
	
	if($sessioninfo['level']>=9999){
		//print "<br>==============<br>";
	}
	
	foreach($si_info_list as $sku_item_id => $si_info){
		$group_pcs += $si_info['qty'] * $si_info['info']['packing_uom_fraction'];
		$group_total_avg_cost += $si_info['qty'] * $si_info['avg_cost'];
	
		if($sessioninfo['level']>=9999){
			//print "sku item id = $sku_item_id, qty = ".$si_info['qty'].", avg cost = ".$si_info['avg_cost'].", pcs = ".($si_info['qty'] * $si_info['info']['packing_uom_fraction']).", total avg cost = ".($si_info['qty'] * $si_info['avg_cost'])."<br>";
		}	
	}
	
	$group_pcs = round($group_pcs, 5);
	$group_total_avg_cost = round($group_total_avg_cost, 5);
	
	if($sessioninfo['level']>=9999){
		//print_r($si_info_list);
		//print "group_pcs = $group_pcs, group_total_avg_cost = $group_total_avg_cost<br>";
	}
	$group_pcs_b4_grn = $group_pcs;
	
	$grn_total_cost = $grn['cost'];
	$grn_total_pcs = $grn['qty'] * $si_info_list[$grn_item_sid]['info']['packing_uom_fraction'];
	$grn_pcs_cost = round($grn_total_cost / $grn_total_pcs, 5);
	
	$group_pcs += $grn_total_pcs;
	$group_total_avg_cost += $grn_total_cost;
	
	if($sessioninfo['level']>=9999){
		//print "GRN total pcs = $grn_total_pcs, grn total cost = $grn_total_cost<br>";
		//print "New group_pcs = $group_pcs, new group_total_avg_cost = $group_total_avg_cost<br>";
	}
	
	$pcs_cost = $group_total_avg_cost / $group_pcs;
	if($sessioninfo['level']>=9999){
		//print "new pcs avg cost = ($group_total_avg_cost / $group_pcs) = $pcs_cost<br>";
	}
	if($pcs_cost <= 0 || $group_pcs <= 0 || $group_pcs_b4_grn <= 0){
		if($sessioninfo['level']>=9999){
			//print "$pcs_cost is <= zero or $group_pcs is <=0 or $group_pcs_b4_grn <= 0, use grn pcs cost = $grn_pcs_cost<br>";
		}
		$pcs_cost = $grn_pcs_cost;
	}
	if($sessioninfo['level']>=9999){
		//print "pcs_cost = $pcs_cost<br>";
	}
	foreach($si_info_list as $sku_item_id => $si_info){
		$si_info_list[$sku_item_id]['avg_cost'] = $si_info['info']['packing_uom_fraction'] * $pcs_cost;
	}
}
*/

function calculate_group_sku_grn($d, &$si_info_list, $grn_info, &$display_data){
	global $config, $sessioninfo;
	
	if(!isset($grn_info))	return;
	
	//print "<br>GRN<br>";
	//print_r($grn_info);
	foreach($grn_info as $grn_key => $grn){
		// this GRN no touch the cost
		if(!$grn['count_this_grn']){
			foreach($grn['item_list'] as $sid => $grn_details){
				$si_info_list[$sid]['qty'] += $grn_details['qty'];
			}
			continue;
		}
		
		$entry = array('type'=>'grn', 'link'=>$grn['link']);
		if($grn['currency_code']){
			$entry['ori_cost'] = $grn['ori_cost'];
			$entry['currency_code'] = $grn['currency_code'];
			$entry['currency_rate'] = $grn['currency_rate'];
		}	
		
		// BEFORE
		foreach($si_info_list as $sid => $si){
			$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
			$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
			$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
			$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
			$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
			
			$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
			$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
			$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
			
			$entry['before']['total']['cost'] = $display_data['current']['cost'];
			$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
		}
		
		// ACTION
		$total_grn_cost = 0;
		$total_grn_pcs = 0;
		$pcs_cost = 0;
		$total_grn_tax = 0;
		$total_grn_cost_before_tax = 0;
		
		foreach($grn['item_list'] as $sid => $grn_details){
			
			$entry['action']['item_list'][$sid]['qty'] += $grn_details['qty'];
			
			// last cost
			$entry['action']['item_list'][$sid]['cost'] = round($grn_details['total_cost']/$grn_details['qty'], $config['global_cost_decimal_points']);
			if($entry['action']['item_list'][$sid]['cost']<= 0){	// cost less than zero?
				$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			}
			$entry['action']['item_list'][$sid]['total_cost'] = $entry['action']['item_list'][$sid]['qty']*$entry['action']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['action']['item_list'][$sid]['avg_cost'] = $entry['action']['item_list'][$sid]['cost'];
			$entry['action']['item_list'][$sid]['total_avg_cost'] = $entry['action']['item_list'][$sid]['total_cost'];
			
			// get total qty and cost of GRN
			if($grn_details['total_cost']){
				$total_grn_cost += $grn_details['total_cost'];
				$total_grn_pcs += $grn_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
			}
			
			if($grn_details['total_tax']){
				$total_grn_tax += $grn_details['total_tax'];
				$total_grn_cost_before_tax += $grn_details['total_cost_before_tax'];
			}
		}
		if($total_grn_pcs>0){	// this grn will update cost
			$pcs_cost = round($total_grn_cost / $total_grn_pcs, $config['global_cost_decimal_points']);
		}
		
		// BALANCE
		foreach($si_info_list as $sid => $si){
			if(isset($entry['action']['item_list'][$sid])){
				// take from ACTION
				$entry['balance']['item_list'][$sid]['qty'] = $entry['before']['item_list'][$sid]['qty']+$entry['action']['item_list'][$sid]['qty'];
				
				// cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
				}else{
					$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				}
				$entry['balance']['item_list'][$sid]['total_cost'] += round($entry['balance']['item_list'][$sid]['qty']*$entry['balance']['item_list'][$sid]['cost'], $config['global_cost_decimal_points']);
				
				// avg cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['before']['item_list'][$sid]['total_avg_cost']+($pcs_cost * $si['info']['packing_uom_fraction'] * $entry['action']['item_list'][$sid]['qty']), $config['global_cost_decimal_points']);
					
					if($entry['balance']['item_list'][$sid]['qty']){
						$entry['balance']['item_list'][$sid]['avg_cost'] = round($entry['balance']['item_list'][$sid]['total_avg_cost'] / $entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					}else{
						$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					}
					
				}else{
					$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
				}
			}else{
				// take from BEFORE
				$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
			}
		
			$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
			$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
			
		}
		//$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = 0;
		if($entry['balance']['total']['qty']>0){
			$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		}
		
		
		if($pcs_cost){
			$entry['after']['pcs_cost'] = $pcs_cost;
			$entry['after']['total_grn_cost'] = $total_grn_cost;
			$entry['after']['total_grn_pcs'] = $total_grn_pcs;
			$entry['after']['total_grn_tax'] = $total_grn_tax;
			$entry['after']['total_grn_cost_before_tax'] = $total_grn_cost_before_tax;
		}
		
		//if($sessioninfo['id'] == 1){
			if($entry['balance']['total']['avg_cost'] <= 0 || $entry['balance']['total']['qty'] <= 0 || $entry['before']['total']['qty'] <= 0 || $entry['before']['total']['total_avg_cost'] <= 0){	// average cost or total pcs is negative
				if($pcs_cost){
					$entry['balance']['total']['old_avg_cost'] = $entry['balance']['total']['avg_cost'];	// record down the negative value
					$entry['balance']['total']['avg_cost'] = $pcs_cost;	// use grn cost as average cost
				}
			}
		//}
		
		
		// AfTER
		foreach($si_info_list as $sid => $si){
			$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
			
			// cost
			if($pcs_cost){	// this grn will update cost
				$entry['after']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
			}else{
				$entry['after']['item_list'][$sid]['cost'] = $entry['balance']['item_list'][$sid]['cost'];
			}
			$entry['after']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['balance']['total']['avg_cost']*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
			
			$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
			$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
			$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
			//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
			//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
			
			$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
			$entry['after']['total']['avg_cost'] = $entry['balance']['total']['avg_cost'];
			$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
		}
	
		$display_data['current']['cost'] = $entry['after']['total']['cost'];
		$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
		$display_data['date_list'][$d][] = $entry;
	}
}

function calculate_group_sku_stock_take($d, &$si_info_list, $stock_take_info, &$display_data){
	global $config;
	
	if(!isset($stock_take_info['item_list']))	return;
	$entry = array('type'=>'stock_take');
	
	// BEFORE
	foreach($si_info_list as $sid => $si){
		$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
		$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
		$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
		$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
		$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
		
		$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
		$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
		$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
		
		$entry['before']['total']['cost'] = $display_data['current']['cost'];
		$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
	}
	
	// ACTION
	$total_sc_cost = 0;
	$total_sc_pcs = 0;
	$pcs_cost = 0;
		
	foreach($stock_take_info['item_list'] as $sid => $sc){
		if($sc['got_cost']){
			foreach($sc['got_cost'] as $tmp_cost => $sc_details){
				$row_cost = $sc_details['qty']*$tmp_cost;
				$entry['action']['item_list'][$sid]['qty'] += $sc_details['qty'];
				$entry['action']['item_list'][$sid]['total_cost'] += $row_cost;
				$entry['action']['item_list'][$sid]['total_avg_cost'] += $row_cost;
				if($row_cost){
					$total_sc_cost += $row_cost;
					$total_sc_pcs += $sc_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
					
					// got qty, cost can average
					$entry['action']['item_list'][$sid]['cost'] = round($entry['action']['item_list'][$sid]['total_cost']/$entry['action']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					$entry['action']['item_list'][$sid]['avg_cost'] = round($entry['action']['item_list'][$sid]['total_avg_cost']/$entry['action']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					
				}else{
					// no qty, need to get previous cost
					//if(!$entry['action']['item_list'][$sid]['cost'])	$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
					//if(!$entry['action']['item_list'][$sid]['avg_cost'])	$entry['action']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['action']['item_list'][$sid]['cost'] = 0;
					$entry['action']['item_list'][$sid]['avg_cost'] = 0;
				}				
				
				// store all the stock take row
				$entry['action']['item_list'][$sid]['sc_list'][] = array('qty'=>$sc_details['qty'], 'cost'=>$tmp_cost);
			}
		}
		if($sc['no_cost']){
			$entry['action']['item_list'][$sid]['qty'] += $sc['no_cost']['qty'];
			//$entry['action']['item_list'][$sid]['cost'] = $si_info_list[$sid]['cost'];
			//$entry['action']['item_list'][$sid]['avg_cost'] = $si_info_list[$sid]['avg_cost'];
			//$entry['action']['item_list'][$sid]['total_cost'] += $sc_details['qty']*$si_info_list[$sid]['cost'];
			//$entry['action']['item_list'][$sid]['total_avg_cost'] += $sc_details['qty']*$si_info_list[$sid]['avg_cost'];
		}
		
	}
	
	// BALANCE
	foreach($si_info_list as $sid => $si){
		if(isset($entry['action']['item_list'][$sid])){
			// take from ACTION
			$entry['balance']['item_list'][$sid] = $entry['action']['item_list'][$sid];
			
			if(!$entry['balance']['item_list'][$sid]['cost']){
				$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				$entry['balance']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['cost']*$entry['balance']['item_list'][$sid]['qty'];
			}
			
			if(!$entry['balance']['item_list'][$sid]['avg_cost']){
				$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
				$entry['balance']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'];
			}
		}else{
			// take from BEFORE
			$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
		}
		
		$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
		$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
		$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
		
	}
	// fix warning message
	if($entry['balance']['total']['qty'] > 0){
		$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
	}else{
		$entry['balance']['total']['cost'] = 0;
		$entry['balance']['total']['avg_cost'] = 0;
	}	
	
	if($total_sc_pcs > 0){	// stock take got change cost
		$pcs_cost = round($total_sc_cost / $total_sc_pcs, $config['global_cost_decimal_points']);
		$entry['after']['pcs_cost'] = $pcs_cost;
		$entry['after']['total_sc_cost'] = $total_sc_cost;
		$entry['after']['total_sc_pcs'] = $total_sc_pcs;
	}	
	
	// AfTER
	foreach($si_info_list as $sid => $si){
		$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
		
		if($pcs_cost > 0){			
			$entry['after']['item_list'][$sid]['cost'] = $pcs_cost*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['avg_cost'] = $pcs_cost*$si['info']['packing_uom_fraction'];
		}else{
			$entry['after']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
		}
		$entry['after']['item_list'][$sid]['total_cost'] = $entry['after']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
		$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
		
		$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
		$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
		$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
		//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
		//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
		
		$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
		
		if($pcs_cost > 0){
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['avg_cost'] = $pcs_cost;
		}else{
			$entry['after']['total']['cost'] = $entry['before']['total']['cost'];
			$entry['after']['total']['avg_cost'] = $entry['before']['total']['avg_cost'];
		}		
		
		$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
		$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
	}
	
	$display_data['current']['cost'] = $entry['after']['total']['cost'];
	$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
	$display_data['date_list'][$d][] = $entry;
	
	//print "<br>Stock Take: $d<br>";
	//print_r($stock_take_info);
	//print_r($display_data);
}

function calculate_group_sku_work_order($d, &$si_info_list, $wo_info, &$display_data){
	global $config, $sessioninfo;
	
	if(!isset($wo_info))	return;
	
	//print "<br>Work Order<br>";
	//print_r($wo_info);
	//return;
	foreach($wo_info as $wo_key => $wo){		
		$entry = array('type'=>'work_order', 'link'=>$wo['link']);
		
		// BEFORE
		foreach($si_info_list as $sid => $si){
			$entry['before']['item_list'][$sid]['qty'] = $si['qty'];
			$entry['before']['item_list'][$sid]['cost'] = $si['cost'];
			$entry['before']['item_list'][$sid]['avg_cost'] = $si['avg_cost'];
			$entry['before']['item_list'][$sid]['total_cost'] = $si['qty']*$si['cost'];
			$entry['before']['item_list'][$sid]['total_avg_cost'] = $si['qty']*$si['avg_cost'];
			
			$entry['before']['total']['qty'] += $si['qty']*$si['info']['packing_uom_fraction'];
			$entry['before']['total']['total_cost'] += $entry['before']['item_list'][$sid]['total_cost'];
			$entry['before']['total']['total_avg_cost'] += $entry['before']['item_list'][$sid]['total_avg_cost'];
			
			$entry['before']['total']['cost'] = $display_data['current']['cost'];
			$entry['before']['total']['avg_cost'] = $display_data['current']['avg_cost'];
		}
		
		// ACTION
		$total_wo_cost = 0;
		$total_wo_pcs = 0;
		$pcs_cost = 0;
		
		foreach($wo['item_list'] as $sid => $wo_details){
			
			$entry['action']['item_list'][$sid]['qty'] += $wo_details['qty'];
			
			// last cost
			$entry['action']['item_list'][$sid]['cost'] = round($wo_details['total_cost']/$wo_details['qty'], $config['global_cost_decimal_points']);
			if($entry['action']['item_list'][$sid]['cost']<= 0){	// cost less than zero?
				$entry['action']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
			}
			$entry['action']['item_list'][$sid]['total_cost'] = $entry['action']['item_list'][$sid]['qty']*$entry['action']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['action']['item_list'][$sid]['avg_cost'] = $entry['action']['item_list'][$sid]['cost'];
			$entry['action']['item_list'][$sid]['total_avg_cost'] = $entry['action']['item_list'][$sid]['total_cost'];
			
			// get total qty and cost
			if($wo_details['total_cost']){
				$total_wo_cost += $wo_details['total_cost'];
				$total_wo_pcs += $wo_details['qty']*$si_info_list[$sid]['info']['packing_uom_fraction'];
			}			
		}
		if($total_wo_pcs>0){	// this grn will update cost
			$pcs_cost = round($total_wo_cost / $total_wo_pcs, $config['global_cost_decimal_points']);
		}
		
		// BALANCE
		foreach($si_info_list as $sid => $si){
			if(isset($entry['action']['item_list'][$sid])){
				// take from ACTION
				$entry['balance']['item_list'][$sid]['qty'] = $entry['before']['item_list'][$sid]['qty']+$entry['action']['item_list'][$sid]['qty'];
				
				// cost
				if($pcs_cost){	// this work order will update cost
					$entry['balance']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
				}else{
					$entry['balance']['item_list'][$sid]['cost'] = $entry['before']['item_list'][$sid]['cost'];
				}
				$entry['balance']['item_list'][$sid]['total_cost'] += round($entry['balance']['item_list'][$sid]['qty']*$entry['balance']['item_list'][$sid]['cost'], $config['global_cost_decimal_points']);
				
				// avg cost
				if($pcs_cost){	// this grn will update cost
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['before']['item_list'][$sid]['total_avg_cost']+($pcs_cost * $si['info']['packing_uom_fraction'] * $entry['action']['item_list'][$sid]['qty']), $config['global_cost_decimal_points']);
					
					if($entry['balance']['item_list'][$sid]['qty']){
						$entry['balance']['item_list'][$sid]['avg_cost'] = round($entry['balance']['item_list'][$sid]['total_avg_cost'] / $entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
					}else{
						$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					}
					
				}else{
					$entry['balance']['item_list'][$sid]['avg_cost'] = $entry['before']['item_list'][$sid]['avg_cost'];
					$entry['balance']['item_list'][$sid]['total_avg_cost'] = round($entry['balance']['item_list'][$sid]['avg_cost']*$entry['balance']['item_list'][$sid]['qty'], $config['global_cost_decimal_points']);
				}
			}else{
				// take from BEFORE
				$entry['balance']['item_list'][$sid] = $entry['before']['item_list'][$sid];
			}
		
			$entry['balance']['total']['qty'] += $entry['balance']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['balance']['total']['total_cost'] += $entry['balance']['item_list'][$sid]['total_cost'];
			$entry['balance']['total']['total_avg_cost'] += $entry['balance']['item_list'][$sid]['total_avg_cost'];
			
		}
		//$entry['balance']['total']['cost'] = round($entry['balance']['total']['total_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		$entry['balance']['total']['avg_cost'] = round($entry['balance']['total']['total_avg_cost'] / $entry['balance']['total']['qty'], $config['global_cost_decimal_points']);
		
		if($pcs_cost){
			$entry['after']['pcs_cost'] = $pcs_cost;
			$entry['after']['total_wo_cost'] = $total_wo_cost;
			$entry['after']['total_wo_pcs'] = $total_wo_pcs;
		}
		
		//if($sessioninfo['id'] == 1){
			if($entry['balance']['total']['avg_cost'] <= 0 || $entry['balance']['total']['qty'] <= 0 || $entry['before']['total']['qty'] <= 0 || $entry['before']['total']['total_avg_cost'] <= 0){	// average cost or total pcs is negative
				if($pcs_cost){
					$entry['balance']['total']['old_avg_cost'] = $entry['balance']['total']['avg_cost'];	// record down the negative value
					$entry['balance']['total']['avg_cost'] = $pcs_cost;	// use grn cost as average cost
				}
			}
		//}
		
		
		// AfTER
		foreach($si_info_list as $sid => $si){
			$entry['after']['item_list'][$sid]['qty'] = $entry['balance']['item_list'][$sid]['qty'];
			
			// cost
			if($pcs_cost){	// this grn will update cost
				$entry['after']['item_list'][$sid]['cost'] = $pcs_cost * $si['info']['packing_uom_fraction'];
			}else{
				$entry['after']['item_list'][$sid]['cost'] = $entry['balance']['item_list'][$sid]['cost'];
			}
			$entry['after']['item_list'][$sid]['total_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['cost'];
			
			// avg cost
			$entry['after']['item_list'][$sid]['avg_cost'] = $entry['balance']['total']['avg_cost']*$si['info']['packing_uom_fraction'];
			$entry['after']['item_list'][$sid]['total_avg_cost'] = $entry['balance']['item_list'][$sid]['qty']*$entry['after']['item_list'][$sid]['avg_cost'];
			
			$si_info_list[$sid]['qty'] = $entry['after']['item_list'][$sid]['qty'];
			$si_info_list[$sid]['cost'] = $entry['after']['item_list'][$sid]['cost'];
			$si_info_list[$sid]['avg_cost'] = $entry['after']['item_list'][$sid]['avg_cost'];
			//$si_info_list[$sid]['total_cost'] = $entry['after']['item_list'][$sid]['total_cost'];
			//$si_info_list[$sid]['total_avg_cost'] = $entry['after']['item_list'][$sid]['total_avg_cost'];
			
			$entry['after']['total']['qty'] += $entry['after']['item_list'][$sid]['qty']*$si['info']['packing_uom_fraction'];
			$entry['after']['total']['cost'] = $pcs_cost;
			$entry['after']['total']['total_cost'] += $entry['after']['item_list'][$sid]['total_cost'];
			$entry['after']['total']['avg_cost'] = $entry['balance']['total']['avg_cost'];
			$entry['after']['total']['total_avg_cost'] += $entry['after']['item_list'][$sid]['total_avg_cost'];
		}
	
		$display_data['current']['cost'] = $entry['after']['total']['cost'];
		$display_data['current']['avg_cost'] = $entry['after']['total']['avg_cost'];
		$display_data['date_list'][$d][] = $entry;
	}
}

function generate_stock_balance_history($form, $data){
	if($data){
		$bal = 0;
		$bal2 = 0;
		foreach($data as $dt => $dt_data){
			if($form['is_parent_inventory']){
				// use parent calculation
				
				// stock check by item
				if(isset($dt_data['stock_check_by_item'])){
					foreach($dt_data['stock_check_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] = $r['qty'];
						$form['sb2_by_item'][$sid]['qty'] = $r['qty'];
					}
					// mark un-stock check item
					foreach($form['item_list'] as $sid => $si){
						if(!isset($dt_data['stock_check_by_item'][$sid])){
							$data[$dt]['un_stock_check_by_item'][$sid]['qty'] = $form['sb_by_item'][$sid]['qty'];
						}
					}
					
					$data[$dt]['stock_check'] = 0;
					foreach($form['sb_by_item'] as $sid => $r){
						$data[$dt]['stock_check'] += $r['qty'];
					}
				}
				
				// grn by item
				if(isset($dt_data['grn_by_item'])){
					foreach($dt_data['grn_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						//print "qty: " . $r['qty'] . ", ibt: " . $r['qty_ibt'] ."<br />";
						$form['sb_by_item'][$sid]['qty'] += $r['qty'];
						$form['sb_by_item'][$sid]['qty'] += $r['qty_ibt'];
					}
				}
				
				// grn2 by item
				if(isset($dt_data['grn2_by_item'])){
					foreach($dt_data['grn2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] += $r['qty'];
					}
				}
				
				// pos by item
				if(isset($dt_data['pos_by_item'])){
					foreach($dt_data['pos_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				// pos2 by item
				if(isset($dt_data['pos2_by_item'])){
					foreach($dt_data['pos2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				
				// gra by item
				if(isset($dt_data['gra_by_item'])){
					foreach($dt_data['gra_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				// gra2 by item
				if(isset($dt_data['gra2_by_item'])){
					foreach($dt_data['gra2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				
				// do by item
				if(isset($dt_data['do_by_item'])){
					foreach($dt_data['do_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				// do2 by item
				if(isset($dt_data['do2_by_item'])){
					foreach($dt_data['do2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				
				// adjustment by item
				if(isset($dt_data['adjustment_by_item'])){
					foreach($dt_data['adjustment_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] += $r['qty'];
					}
				}
				// adjustment2 by item
				if(isset($dt_data['adjustment2_by_item'])){
					foreach($dt_data['adjustment2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] += $r['qty'];
					}
				}
				
				// cn by item
				if(isset($dt_data['cn_by_item'])){
					foreach($dt_data['cn_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] += $r['qty'];
					}
				}
				// cn2 by item
				if(isset($dt_data['cn2_by_item'])){
					foreach($dt_data['cn2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] += $r['qty'];
					}
				}
				
				// dn by item
				if(isset($dt_data['dn_by_item'])){
					foreach($dt_data['dn_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
				// dn2 by item
				if(isset($dt_data['dn2_by_item'])){
					foreach($dt_data['dn2_by_item'] as $sid => $r){
						// reset stock balance to stock check qty
						$form['sb2_by_item'][$sid]['qty'] -= $r['qty'];
					}
				}
			}
			
			// stock check
			if(isset($data[$dt]['stock_check'])){
				$bal = $data[$dt]['stock_check'];
                $bal2 = 0;
			}
			
			// grn
			if(isset($data[$dt]['grn'])){
				$bal += $data[$dt]['grn'];
			}
			
			if(isset($data[$dt]['grn_ibt'])){
				$bal += $data[$dt]['grn_ibt'];
			}
			
			// grn2
			if(isset($data[$dt]['grn2'])){
				$bal2 += $data[$dt]['grn2'];
			}
			
			// pos
			if(isset($data[$dt]['pos'])){
				$bal -= $data[$dt]['pos'];
			}
			// pos2
			if(isset($data[$dt]['pos2'])){
				$bal2 -= $data[$dt]['pos2'];
			}
			
			// gra
			if(isset($data[$dt]['gra'])){
				$bal -= $data[$dt]['gra'];
			}
			// gra2
			if(isset($data[$dt]['gra2'])){
				$bal2 -= $data[$dt]['gra2'];
			}
			
			// do
			if(isset($data[$dt]['do'])){
				$bal -= $data[$dt]['do'];
			}
			// do2
			if(isset($data[$dt]['do2'])){
				$bal2 -= $data[$dt]['do2'];
			}
			
			// adjustment
			if(isset($data[$dt]['adjustment'])){
				$bal += $data[$dt]['adjustment'];
			}
			// adjustment2
			if(isset($data[$dt]['adjustment2'])){
				$bal2 += $data[$dt]['adjustment2'];
			}
			
			// cn
			if(isset($data[$dt]['cn'])){
				$bal += $data[$dt]['cn'];
			}
			// cn2
			if(isset($data[$dt]['cn2'])){
				$bal2 += $data[$dt]['cn2'];
			}
			
			// dn
			if(isset($data[$dt]['dn'])){
				$bal -= $data[$dt]['dn'];
			}
			// dn2
			if(isset($data[$dt]['dn2'])){
				$bal2 -= $data[$dt]['dn2'];
			}
			
			$data[$dt]['bal'] = $bal;
			$data[$dt]['bal2'] = $bal + $bal2;
		}
	}
	
	return $data;
}
?>