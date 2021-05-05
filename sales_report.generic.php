<?php
/*
Revision History
================
6/7/2007 10:30:00 AM - yinsee
- add set_time_limit(0);

6/26/2007 6:02:06 PM -gary
-added avoid warning occured when no data in array $uq_pages.

7/20/2007 4:54:54 PM - yinsee
- request by SLLEE - use pos_transaction.price_type in report
  >> removed if (pos_transaction.price_type<>'',pos_transaction.price_type,if(trade_discount_type>0,sku.default_trade_discount_code,''))
  
8/7/2007 5:04:47 PM yinsee
- move "sel()" function to common.php

10/16/2007 1:02:05 PM gary
- remove assign page title.

11/1/2007 3:16:33 PM gary
- divide each price_type.

9/16/2009 4:07:43 PM Andy
- add opening, in ,out and closing for each item

11/11/2009 4:57:32 PM Andy
- edit get stock balance function
- add show all items & group by sku features
- fix duplicate sum of closing amt if item split into 2 types in same month

4/21/2010 2:53:59 PM Andy
- Brand, Vendor and Department Sales Report now change to use live pos data.

7/13/2010 3:56:49 PM Andy
- Optimize Report generation speed. (change sql and add index)
- Change report to only show finalized sales.

1/17/2011 11:05:32 AM Alex
- change use excelwriter header
- set smo report style

3/18/2011 5:52:47 PM Alex
- fix grn cost amount bugs

4/11/2011 12:28:05 PM Alex
- remove checking on exclude HQ branch

6/27/2011 10:17:01 AM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:51:22 PM Andy
- Change split() to use explode()

10/4/2011 3:23:54 PM Andy
- Fix month sorting bugs.
- Fix closing stock different bugs.

10/14/2011 2:13:19 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

11/24/2011 12:31:21 PM Andy
- Fix wrong opening if the date from not start at "1". (this problem will cause wrong closing too)
- Fix adjustment cannot show in report. 

4/2/2012 10:02:09 AM Andy
- Change Report to use sku vendor from cache table.
- Modify prepare_sql() to pass by branch_id.
 
1/4/2012 3:45 PM Andy
- Fix group by sku + group by month will cause wrong closing.

1/21/2013 10:47:00 AM Fithri
- radio button to show by selling price (sales amount)

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

8/27/2013 2:04 PM Justin
- Enhanced to follow sequence by price type, sku item code and mcode.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/9/2014 9:40 AM Fithri
- filter out SKU without inventory

7/22/2014 5:35 PM Justin
- Enhanced to have total cost, GP and GP(%).

7/25/2014 4:47 PM Justin
- Bug fixed on amount become 1.00 whenever the qty is more or equal to one thousand.
*/
include_once("include/excelwriter.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

set_time_limit(0);

if ($_REQUEST['excelxml']){
//	ob_flush();
//		ob_start();

	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_PRINTTITLE To Excel");

	Header('Content-Type: application/msexcel');
	Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
	print ExcelWriter::GetHeader();
	print "<h1>$_PRINTTITLE</h1>\n";
	get_dept_title();
	print "<b>".$smarty->get_template_vars("subtitle_m")."</b>";
}
?>

<style>
.before_sc{
	color: red;
    font-style: italic;
}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
<?php

$branch_group = load_branch_group();

if (!$_REQUEST['print'] && !$_REQUEST['excelxml'])
{
	$smarty->display("header.tpl");
	prepare_form();
}

if ($_REQUEST['load']) 
{

	generate_table();
  
/*
	if ($_REQUEST['excelxml']) {
	  
		$output = ob_get_contents();
		$file=str_replace("\\", "/", tempnam($_SERVER['DOCUMENT_ROOT']."tmp","tmp"));
		 log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_PRINTTITLE To Excel");
		$excel=new ExcelWriter($file);
		if($excel==false) die($excel->error);
	    $fout = $excel->fp;
	   
	    fwrite($fout, "<h1>$_PRINTTITLE</h1>\n");
	    fwrite($fout, "<b>".$smarty->get_template_vars("subtitle_m")."</b>");
	    fwrite($fout, $output);
	    $excel->close();
		echo "<SCRIPT>document.location='../../getxml.php?f=$file';</SCRIPT>";
	}
*/

}

if (!$_REQUEST['print']) $smarty->display("footer.tpl");

function get_balance($y, $m, $codes){
	global $con, $stock_data, $sku_info, $sessioninfo, $branch_group;
	if(BRANCH_CODE=='HQ'){
	
	  if($_REQUEST['branch_id']=="")
    {
        $res = $con->sql_query("select id from branch order by sequence,code");
        while($r = $con->sql_fetchrow($res))
        {
            $branch_ids[]=$r['id'];
        }
       
    }    
    elseif(strpos($_REQUEST['branch_id'],'bg,')===0)
    {   // is branch group
  			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
  			foreach($branch_group['items'][$bg_id] as $bid=>$r){
  			    $branch_ids[] = $bid;
  			}
		}else{  // single branch
  			//$branch_id = intval($_REQUEST['branch_id']);
  			$branch_ids[] = intval($_REQUEST['branch_id']);
		}
	
	}	
	else   $branch_ids[] = intval($sessioninfo['branch_id']);
	
	$lbl = sprintf('%04d%02d', $y, $m);
	$last_m = $m-1;
	$last_y = $y;
	if($last_m<1){
        $last_m = 12;
        $last_y --;
	}
	$last_lbl = sprintf('%04d%02d', $last_y, $last_m);
	list($selected_from_y,$selected_from_m,$selected_from_d) = explode('-',$_REQUEST['from']);
	
    foreach($codes as $code => $dummy){
        //list($sku_item_code,$price_type) = split("/", $code);
        list($price_type,$sku_item_code) = explode("/", $code);
        if(!$sku_info[$sku_item_code]){
			$con->sql_query("select * from sku_items where sku_item_code=".ms($sku_item_code)) or die(mysql_error());
			
			//print "select * from sku_items where sku_item_code=".ms($sku_item_code);
			
			$sku_info[$sku_item_code] = $con->sql_fetchrow();
		}
				
		$sku = $sku_info[$sku_item_code];
		$sid = $sku['id'];
		
		if($y==$selected_from_y&&$m==$selected_from_m)	$default_from_Date = $_REQUEST['from'];
		else	$default_from_Date = $y.'-'.$m.'-1';
		$to_Date = $y.'-'.sprintf('%02d',$m).'-'.sprintf('%02d',days_of_month($m,$y));
		if($to_Date>$_REQUEST['to']) $to_Date = $_REQUEST['to'];
		
		$where_id = "sku_item_id=$sid";
	
		// already calculate
		if(isset($stock_data[$lbl][$sid]['opening'])&&isset($stock_data[$lbl][$sid]['closing']))    continue;
		
		if($sid)
		{
        $loop_index = 0;
    		foreach($branch_ids as $branch_id){
    		    $from_Date = $default_from_Date;
    		    $transaction_min_date = $from_Date;
                // check got last closing, if hv just use it to continue
    			if($stock_data[$last_lbl][$sid]['closing'])   $stock_data[$lbl][$sid]['opening'] = $stock_data[$last_lbl][$sid]['closing'];
    			else{
    				// no last closing, retrieve it
    
    				// try to get from stock check
    				$sql = "select *,day(date) as day from stock_check where branch_id=$branch_id and sku_item_code=".ms($sku_item_code)." and date=(select max(date) from stock_check where branch_id=$branch_id and sku_item_code=".ms($sku_item_code)." and date between ".ms($from_Date)." and ".ms($to_Date).")";
    				//print $sql."<br>";
    				$q_sc = $con->sql_query($sql) or die(mysql_error());
    				if($con->sql_numrows($q_sc)>0){
    			        while($r = $con->sql_fetchrow($q_sc)){
    			            $stock_data[$lbl][$sid]['sc'] = $r['day'];
    						$stock_data[$lbl][$sid]['opening'] += $r['qty'];
    						$sc_date = $r['date'];
    					}
    					// move start date to stock check date
    					$from_Date = $sc_date;
    					$transaction_min_date = $sc_date;
    				}else{  // no stock check, take from cost history
    						  
    					/*$q2=$con->sql_query("select sku_items_cost_history.*, sku_items_cost_history.sku_item_id as sid ,
    				(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <='$from_Date') as stock_date
    				from
    				sku_items_cost_history
    				left join sku_items on sku_item_id = sku_items.id
    				where branch_id=$branch_id and date <= '$from_Date' and date > 0 and $where_id
    				having stock_date=date order by null ") or die(mysql_error());

						while($r2=$con->sql_fetchrow($q2)){
    						$stock_data[$lbl][$sid]['history_bal']+=$r2['qty'];
    					}*/
    				    $q2 = $con->sql_query("select sich.sku_item_id as sid, sich.qty,sich.date as sb_date
from sku_items_cost_history sich
where branch_id=$branch_id and date <= '$from_Date' and date > 0 and $where_id order by date desc limit 1");
    				    $temp = $con->sql_fetchrow($q2);
    					$stock_data[$lbl][$sid]['history_bal'] += $temp['qty'];
    					$transaction_min_date = $temp['sb_date'];
    				}
    				/*$sb_tbl = "stock_balance_b".$branch_id."_".date("Y", strtotime($from_Date));
    				$sql = "select qty from $sb_tbl where ".ms($from_Date)." between from_date and to_date";
    				$con->sql_query($sql);
    				if($con->sql_numrows())	$stock_data[$lbl][$sid]['opening'] = $con->sql_fetchfield(0);
    				$con->sql_freeresult();*/
    			}
    
    			//GRN = get the rcvd qty, rcvd cost and grn qty
    			$q4=$con->sql_query("select grn_items.sku_item_id as sid,
    		sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
    		sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
	(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
	if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)) as total_rcv_cost,
    		if(grr.rcv_date>='$from_Date',0,1) as bal,
    
    		(rcv_date <= '$transaction_min_date') as dont_count
    
    		from grn_items
    		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
    		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
    		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
    		where grn.branch_id=$branch_id and rcv_date <= '$to_Date' and $where_id and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
    		group by bal, dont_count, sid order by null") or die(mysql_error());
    
    			while($r4=$con->sql_fetchrow($q4)){
    				if(!$r4['dont_count']){
    					if($r4['bal']){
    						$stock_data[$lbl][$sid]['grn_bal'] = $r4['qty'];
    					}
    					else{
    						$stock_data[$lbl][$sid]['rcv_qty'] = $r4['qty'];
    					}
    				}
    			}
    
    			//ADJ = get adj in and adj out
    			$q5=$con->sql_query("select
    		ai.sku_item_id as sid,
    		sum(qty) as qty,
    		if(adjustment_date>='$from_Date',0,1) as bal,
    		if(qty>=0,'p','n') as type,
    
    		(adjustment_date <= '$transaction_min_date') as dont_count
    
    		from adjustment_items ai
    		left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
    		where ai.branch_id =$branch_id and adjustment_date <= '$to_Date' and $where_id and adj.active=1 and adj.approved=1 and adj.status=1
    		group by bal, type,dont_count, sid order by null") or die(mysql_error());
    
    			while($r5=$con->sql_fetchrow($q5)){
    				if(!$r5['dont_count']){
    					if($r5['bal']){
    						$stock_data[$lbl][$sid]['adj_bal']+=$r5['qty'];
    					}
    					else{
    						if($r5['type']=='p'){
    							$stock_data[$lbl][$sid]['adj_in']+=$r5['qty'];
    						}
    						elseif($r5['type']=='n'){
    							$stock_data[$lbl][$sid]['adj_out']+=abs($r5['qty']);
    						}
    					}
    				}
    			}
    
    			//DO get do qty
    			$q6=$con->sql_query("select
    		do_items.sku_item_id as sid,
    		sum(do_items.ctn *uom.fraction + do_items.pcs) as qty,
    		if(do_date>='$from_Date',0,1) as bal,
    
    		(do_date <= '$transaction_min_date') as dont_count
    
    		from do_items
    		left join uom on do_items.uom_id=uom.id
    		left join do on do_id = do.id and do_items.branch_id = do.branch_id
    		where do_items.branch_id=$branch_id and do_date <= '$to_Date' and $where_id and do.active=1 and do.approved=1 and do.checkout=1 and do.status=1
    		group by bal,dont_count, sid order by null") or die(mysql_error());
    
    			while($r6=$con->sql_fetchrow($q6)){
    				if(!$r6['dont_count']){
    					if($r6['bal']){
    						$stock_data[$lbl][$sid]['do_bal']+=$r6['qty'];
    					}
    					else{
    						$stock_data[$lbl][$sid]['do_qty']+=$r6['qty'];
    					}
    				}
    			}
    
    			//GRA get the gra qty.
    			$q7=$con->sql_query("select
    		gra_items.sku_item_id as sid,
    		sum(qty) as qty,
    		if(return_timestamp>='$from_Date',0,1) as bal,
    
    		(date(return_timestamp) <= '$transaction_min_date') as dont_count
    
    		from gra_items
    		left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
    		where gra.branch_id=$branch_id and return_timestamp <= '$to_Date' and $where_id and gra.status=0 and gra.returned=1
    		group by bal,dont_count, sid order by null") or die(mysql_error());
    
    			while($r7=$con->sql_fetchrow($q7)){
    				if(!$r7['dont_count']){
    					if($r7['bal']){
    						$stock_data[$lbl][$sid]['gra_bal']+=$r7['qty'];
    					}
    					else{
    						$stock_data[$lbl][$sid]['gra_qty']+=$r7['qty'];
    					}
    				}
    			}
    
    			$tbl="sku_items_sales_cache_b".$branch_id;
    			$q8=$con->sql_query("select
    		si.id as sid,
    		sum(qty) as qty,
    		if(date>='$from_Date',0,1) as bal,
    
    		(date <= '$transaction_min_date') as dont_count
    
    		from $tbl pos
    		left join sku_items si on si.id=pos.sku_item_id
    		where date <= '$to_Date' and si.id=$sid
    		group by si.id, bal, dont_count order by null") or die(mysql_error());

    		while($r8=$con->sql_fetchrow($q8)){
    				if(!$r8['dont_count']){
    					if($r8['bal']){
    						$stock_data[$lbl][$sid]['pos_bal']+=$r8['qty'];
    					}
    					else{
    						$stock_data[$lbl][$sid]['pos_qty']+=$r8['qty'];
    					}
    				}
    			}
    		}
    
	        if(!$stock_data[$last_lbl][$sid]['closing']){
	            $stock_data[$lbl][$sid]['opening'] += $stock_data[$lbl][$sid]['history_bal']+$stock_data[$lbl][$sid]['grn_bal']+$stock_data[$lbl][$sid]['adj_bal']-$stock_data[$lbl][$sid]['do_bal']-$stock_data[$lbl][$sid]['gra_bal']-	$stock_data[$lbl][$sid]['pos_bal'];
			}

    		$stock_data[$lbl][$sid]['in'] = $stock_data[$lbl][$sid]['rcv_qty']+$stock_data[$lbl][$sid]['adj_in'];
    		$stock_data[$lbl][$sid]['out'] = $stock_data[$lbl][$sid]['adj_out']+$stock_data[$lbl][$sid]['do_qty']+$stock_data[$lbl][$sid]['gra_qty'];
    		$stock_data[$lbl][$sid]['closing'] = $stock_data[$lbl][$sid]['opening']+$stock_data[$lbl][$sid]['in']-$stock_data[$lbl][$sid]['out']-$stock_data[$lbl][$sid]['pos_qty'];
    	}
 	}
}

function generate_table()
{
	global $smarty, $sessioninfo, $con, $stock_data, $sku_info, $all_sku_items, $sku_id_list, $con_multi,$branch_group, $config;
	global $_SRTITLE, $_SRTABLE, $_SRTABLE_ID,$_PRINTTITLE;
	
	$con_multi= new mysql_multi();

  
	$bid_list = array();
	if (BRANCH_CODE=='HQ')
	{
	    $tmp_bid = intval($_REQUEST['branch_id']);
	    /////////////////////////////////////////////////////////////
	    if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $bid=>$r){
				if ($config['sales_report_branches_exclude']) {
					$branch_code = $r['code'];
					if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
						// print "$branch_code skipped<br />";
						continue;
					}
				}
			    $bid_list[] = $bid;
			}
		}else{
			if ($tmp_bid>0)
			{
				$bid_list[] = $tmp_bid;
			}
			else
			{
			    // if it is all branch, no filter.
			    $con->sql_query("select id, code from branch where active=1 order by sequence,code");
			    while($r = $con->sql_fetchassoc()){
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $r['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							// print "$branch_code skipped<br />";
							continue;
						}
					}
					$bid_list[] = mi($r['id']);
				}
				$con->sql_freeresult();
			}
		}
	    /////////////////////////////////////////////////////////////
	}
	else $bid_list[] = $sessioninfo['branch_id'];
	if(!$bid_list)	return;
	
	// make a list of all sku items need to show
	if($all_sku_items)  $sku_item_id_need_list = array_keys($all_sku_items);
                    
	foreach($bid_list as $bid){
		$filter = prepare_sql($bid);
		if (!$filter) continue;
		 
		$use_grn_xtra_join = '';
		// if it is use grn
		if($_REQUEST['GRN'] && $_REQUEST['vendor_id']){
			$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=pi.sku_item_id and pi.date between vsh.from_date and vsh.to_date and vsh.vendor_id=".mi($_REQUEST['vendor_id']);
			//$use_grn_xtra_col = ",vsh.vendor_id as last_grn_vendor_id, last_ven.description last_vend_desc";
		}
		
		$active_sku = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		$sql = "select sum(pi.price-pi.discount) as amt, sum(pi.qty) as qty, sku.sku_type, month(pos.date) as month, year(pos.date) as year,
		pos.date as dt, si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description,
		pi.trade_discount_code as price_type, if (sp.price, sp.price, si.selling_price) as selling_price,pi.sku_item_id,
		si.sku_id
	from pos
	left join pos_items pi on pi.branch_id=pos.branch_id and pi.pos_id=pos.id and pi.date=pos.date and pi.counter_id=pos.counter_id
	left join sku_items si on si.id = pi.sku_item_id
	left join sku_items_price sp on sp.sku_item_id = pi.sku_item_id and sp.branch_id=pi.branch_id
	left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = pos.branch_id
	left join sku on si.sku_id = sku.id
	left join category on sku.category_id = category.id
	left join category_cache cc on cc.category_id=sku.category_id
	left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
	$use_grn_xtra_join
	where $filter and $active_sku and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')
	group by dt, si.sku_item_code, price_type
	order by price_type, si.sku_item_code, si.mcode";//print "<span>$sql</span><br /><br /><br />";
		$result1 = $con_multi->sql_query($sql) or die(mysql_error());//xx
	
		// store to table variable
		while($r = $con_multi->sql_fetchrow($result1))
		{
		    //if ($r['qty']==0 && $r['amt']==0) continue;
			//$key = $r['sku_item_code']."/".$r['price_type'];
			$key = $r['price_type']."/".$r['sku_item_code'];
			$date_key = $r['year']."-".sprintf("%02d", $r['month']);
			$tmp = get_sku_item_cost_selling($bid, $r['sku_item_id'], $r['dt'], array("cost"));
			
		    if (!isset($tb[$key]['data']))	$tb[$key]['data'] = $r;
		    
		    $tb[$key][$r['dt']] += $r['qty'];
		    $tb[$key]['selling_price'] = $r['selling_price'];
		    $tb[$key]['amount'][$date_key] += $r['amt'];
		    $tb[$key]['qty'][$date_key] += $r['qty'];
		    $tb[$key]['ttl_cost'][$date_key] += $r['qty'] * $tmp['cost'];
	
		    $uq_cols[$r['dt']] = 1;
		    $uq_pages[$date_key][$key] = 1;
	
	     	$sku_item_id_used_list[$date_key][$r['sku_item_id']] = $r['sku_item_id'];
	     	if($_REQUEST['group_by_sku'])	$sku_id_list[$r['sku_id']] = $r['sku_id'];
		}
		$con_multi->sql_freeresult($result1);
	}
	
	if(!$tb){
		print "<p>No data</p>";
		return;
	}
	/*$sql = "select sum(pi.price-pi.discount) as amt, sum(pi.qty) as qty, sku.sku_type, month(pos.date) as month, year(pos.date) as year,
	pos.date as dt, si.sku_item_code, ifnull(si.artno,si.mcode) as artno_mcode, si.description,
	pi.trade_discount_code as price_type, if (sp.price, sp.price, si.selling_price) as selling_price,pi.sku_item_id,
	si.sku_id
from pos
left join pos_items pi on pi.branch_id=pos.branch_id and pi.pos_id=pos.id and pi.date=pos.date and pi.counter_id=pos.counter_id
left join sku_items si on si.id = pi.sku_item_id
left join sku_items_price sp on sp.sku_item_id = pi.sku_item_id and sp.branch_id=pi.branch_id
left join sku on si.sku_id = sku.id
left join category on sku.category_id = category.id
left join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date
where $filter
group by dt, si.sku_item_code, price_type order by price_type";
	$result1 = $con_multi->sql_query($sql) or die(mysql_error());


	if (!$con_multi->sql_numrows($result1))
	{
		print "<p>No data</p>";
		return;
	}
	

	// make a list of all sku items need to show
	if($all_sku_items)  $sku_item_id_need_list = array_keys($all_sku_items);
	// store to table variable
	while($r = $con_multi->sql_fetchrow($result1))
	{
	    //if ($r['qty']==0 && $r['amt']==0) continue;
		//$key = $r['sku_item_code']."/".$r['price_type'];
		$key = $r['price_type']."/".$r['sku_item_code'];
		$date_key = $r['year']."-".sprintf("%02d", $r['month']);
		
	    if (!isset($tb[$key]['data']))
			$tb[$key]['data'] = $r;
	    $tb[$key][$r['dt']] = $r['qty'];
	    $tb[$key]['selling_price'] = $r['selling_price'];
	    $tb[$key]['amount'][$date_key] += $r['amt'];
	    $tb[$key]['qty'][$date_key] += $r['qty'];

	    $uq_cols[$r['dt']] = 1;
	    $uq_pages[$date_key][$key] = 1;

     	$sku_item_id_used_list[$date_key][$r['sku_item_id']] = $r['sku_item_id'];
     	if($_REQUEST['group_by_sku'])	$sku_id_list[$r['sku_id']] = $r['sku_id'];
	}
	$con_multi->sql_freeresult($result1);*/
	
	@ksort($uq_pages);
	@ksort($uq_cols);
	@ksort($tb);

    // get parent sku
	if($_REQUEST['group_by_sku']&&$sku_id_list){
		$active_sku = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
	    $sql = "select si.*,sku.sku_type
from sku_items si
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where si.sku_id in (".join(',',$sku_id_list).") and is_parent=1 and $active_sku and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')
order by si.sku_item_code, si.mcode";//xx
//print "<span>$sql</span><br /><br /><br />";
		//print($sql);
		$q_2 = $con->sql_query($sql) or die(mysql_error());
		while($r = $con->sql_fetchrow($q_2)){
			$sku_parent_list[$r['sku_id']] = $r['id'];
			if(!$all_sku_items[$r['id']])   $all_sku_items[$r['id']] = $r;
			if(!$sku_info[$r['sku_item_code']]) $sku_info[$r['sku_item_code']] = $r;
			$sku_item_id_need_list[$r['id']] = $r['id'];
		}
		$con->sql_freeresult($q_2);
	}
	//print_r($sku_parent_list);
	
	// add missed item to list if show all
	if($uq_pages&&$sku_item_id_need_list){
		foreach($uq_pages as $ym=>$codes){
		    $sku_item_id_left_list = array();
            list($y,$m) = explode("-",$ym);
            //$price_type_date = $y."-".sprintf('%02d',$m)."-".sprintf('%02d',days_of_month($m,$y));
            //if($price_type_date>$_REQUEST['to'])    $price_type_date = $_REQUEST['to'];
            $price_type_date = $y."-".sprintf('%02d',$m)."-01";
            
            // check left sku in month
            foreach($sku_item_id_need_list as $sku_item_id){
                if(!$sku_item_id_used_list[$ym][$sku_item_id])   $sku_item_id_left_list[$sku_item_id] = $sku_item_id;
			}
			
			// if no sku left
			if(!$sku_item_id_left_list) continue;
            
			$active_sku = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
			
            $sql = "select si.id as sid,si.sku_item_code,ifnull((select trade_discount_code from sku_items_price_history siph
			where siph.sku_item_id=si.id and added<".ms($price_type_date)." order by added desc limit 1),sku.default_trade_discount_code) as price_type,
			ifnull((select price from sku_items_price_history siph where siph.sku_item_id=si.id and added<".ms($price_type_date)." order by added desc limit 1),si.selling_price) as selling_price,
			si.sku_id
from sku_items si
left join sku on si.sku_id=sku.id
left join category_cache cc on cc.category_id=sku.category_id
where si.id in (".join(',',$sku_item_id_left_list).") and $active_sku and ((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')
order by price_type, si.sku_item_code, si.mcode";//xx
//print "<span>$sql</span><br /><br /><br />";
			$q_1 = $con->sql_query($sql) or die(mysql_error());
			while($r = $con->sql_fetchrow($q_1)){
			    $code = $r['price_type']."/".$r['sku_item_code'];
			    $upd = array();
			    $upd['sku_type'] = $all_sku_items[$r['sid']]['sku_type'];
			    $upd['description'] = $all_sku_items[$r['sid']]['description'];
			    $upd['artno_mcode'] = $all_sku_items[$r['sid']]['artno'];
			    $upd['selling_price'] = $all_sku_items[$r['sid']]['selling_price'];
			    $tb[$code]['data'] = $upd;
                $uq_pages[$ym][$code] = 1;
                
                if($_REQUEST['group_by_sku'])	$sku_id_list[$r['sku_id']] = $r['sku_id'];
			}
			$con->sql_freeresult($q_1);
			// re-sort price type
			@ksort($uq_pages[$ym]);
		}
	}

	// get stock balance
	//print_r($uq_pages);
	//print_r($tb);
	// make pos qty
	if($tb){
		foreach($tb as $code=>$data){

            list($price_type,$sku_item_code) = explode("/", $code);
            if(!$sku_info[$sku_item_code]){ // get sku items information
				$con->sql_query("select * from sku_items where sku_item_code=".ms($sku_item_code)) or die(mysql_error());
				$sku_info[$sku_item_code] = $con->sql_fetchrow();
				$con->sql_freeresult();
			}
			$sid = mi($sku_info[$sku_item_code]['id']);

			if(!$sid)   continue;
			
			if($data['qty']){
				foreach($data['qty'] as $ym=>$qty){
					list($y,$m) = explode("-",$ym);
					$lbl = sprintf("%04d%02d", $y, $m);
					$stock_data[$lbl][$sid]['pos_qty'] += $qty;
				}
			}
		}
		//print_r($stock_data);
	}
	if($uq_pages){
	    // consturct an array to get stock balance
	    foreach($uq_pages as $ym=>$codes){
	        list($y,$m) = explode("-",$ym);
	        $date_key = sprintf("%04d%02d", $y, $m);
	        foreach($codes as $code => $dummy){ // loop monthly to get each sku item code
	            list($price_type,$sku_item_code) = explode("/", $code);
		        if(!$sku_info[$sku_item_code]){ // get sku items information
					$con->sql_query("select * from sku_items where sku_item_code=".ms($sku_item_code)) or die(mysql_error());
					$sku_info[$sku_item_code] = $con->sql_fetchrow();
					$con->sql_freeresult();
				}
				$sid = mi($sku_info[$sku_item_code]['id']);
				if(!$sid)   continue;
				$sb_item_arr[$date_key][$sid] = 1;
	        }
	        
	    }
	    //print_r($sb_item_arr);
	    if($sb_item_arr)	get_stock_balance($sb_item_arr);
        /*foreach($uq_pages as $ym=>$codes){
            list($y,$m) = split("-",$ym);
	        // added by andy
			get_balance($y, $m, $uq_pages[$ym]);
		}*/
	}
	$con_multi->close_connection();
	//print_r($uq_pages);

	// group by sku
	if($_REQUEST['group_by_sku']&&$uq_pages){
		if($sessioninfo['u']=='admin'){
			//print_r($sku_parent_list);
		}
		foreach($uq_pages as $ym=>$codes){
		    list($y,$m) = explode("-",$ym);
		    $DAYS_OF_MONTH = days_of_month($m,$y);
		    $lbl = sprintf('%04d%02d',$y,$m);
		    
			foreach($codes as $code=>$dummy){
				list($price_type, $sku_item_code) = explode("/",$code);
				$sid = $sku_info[$sku_item_code]['id'];
				$sku_id = $sku_info[$sku_item_code]['sku_id'];
				$parent_sku_item_id = $sku_parent_list[$sku_id];
				$parent_sku_item_code = $all_sku_items[$parent_sku_item_id]['sku_item_code'];
				$new_code = $price_type."/".$parent_sku_item_code;
				
				// create parent info
				if (!isset($tb2[$new_code]['data'])){
				    $upd = array();
					$upd['sku_type'] = $all_sku_items[$parent_sku_item_id]['sku_type'];
				    $upd['description'] = $all_sku_items[$parent_sku_item_id]['description'];
				    $upd['artno_mcode'] = $all_sku_items[$parent_sku_item_id]['artno'];
				    $upd['selling_price'] = $all_sku_items[$parent_sku_item_id]['selling_price'];
                    $tb2[$new_code]['data'] = $upd;
				}	
				
				// join daily pos qty
				for ($i=1;$i<=$DAYS_OF_MONTH;$i++)
				{
			    	$dt = sprintf("%d-%02d-%02d",$y,$m,$i);
			    	if(isset($tb[$code][$dt]))	$tb2[$new_code][$dt] += $tb[$code][$dt];

				}

				// add total pos qty & amt by item
				$tb2[$new_code]['amount'][$ym] += $tb[$code]['amount'][$ym];
			    $tb2[$new_code]['qty'][$ym] += $tb[$code]['qty'][$ym];
			    $tb2[$new_code]['ttl_cost'][$ym] += $tb[$code]['ttl_cost'][$ym];
			    
			    // join stock data (checking to don't add self data)
			    if($sid!=$parent_sku_item_id){
                    $stock_data[$lbl][$parent_sku_item_id]['opening'] += $stock_data[$lbl][$sid]['opening'];
		            $stock_data[$lbl][$parent_sku_item_id]['in'] += $stock_data[$lbl][$sid]['in'];
		            $stock_data[$lbl][$parent_sku_item_id]['out'] += $stock_data[$lbl][$sid]['out'];
		            $stock_data[$lbl][$parent_sku_item_id]['closing'] += $stock_data[$lbl][$sid]['closing'];
				}

			    // because it is group by item, so pre-calculate closing amt
				if($tb[$code]['qty'][$ym]){
	                $selling_price = $tb[$code]['amount'][$ym]/$tb[$code]['qty'][$ym];
				}else{
                    $selling_price = $all_sku_items[$sid]['selling_price'];
				}
			    $tb2[$new_code]['total_selling_price'][$ym] += ($selling_price*$stock_data[$lbl][$sid]['closing']);
			    
			    // register item entry
			    if(!$_REQUEST['show_all']){
			        // if not show all, hide zero qty & zero amt row
					if(!$tb2[$new_code]['qty'][$ym]&&!$tb2[$new_code]['amount'][$ym]){
                        continue;
					}   
				}

			    $uq_pages2[$ym][$new_code] = 1;
			}
			
		}
		$uq_pages = $uq_pages2;
		$tb = $tb2;

		unset($tb2);
		unset($uq_pages2);
		
	}

	//add here to group by month
	if($_REQUEST['by_monthly']&&$uq_pages){
	    $y_before='';
	    @ksort($uq_pages);
		foreach($uq_pages as $ym=>$codes){
		    list($y,$m) = explode("-",$ym);
		    $DAYS_OF_MONTH = days_of_month($m,$y);
		    $lbl = sprintf('%04d%02d',$y,$m);

			if ($y_before != $y)	$sid_arr=array();

			foreach($codes as $code=>$dummy){
				list($price_type, $sku_item_code) = explode("/",$code);
				$sid = $sku_info[$sku_item_code]['id'];
				$sku_id = $sku_info[$sku_item_code]['sku_id'];
				$parent_sku_item_id = $sku_parent_list[$sku_id];
				
				// join daily pos qty
				for ($i=1;$i<=$DAYS_OF_MONTH;$i++)
				{
			    	$dt = sprintf("%d-%02d-%02d",$y,$m,$i);
			    	$new_dt= sprintf("%d-%02d",$y,$m);
			    	$tb[$code][$new_dt] += $tb[$code][$dt];
					unset($tb[$code][$dt]);

				}

				// add total pos qty & amt by item
				$tb[$code]['amount'][$y] += $tb[$code]['amount'][$ym];
			    $tb[$code]['qty'][$y] += $tb[$code]['qty'][$ym];

				if ($tb[$code]['total_selling_price'][$ym])
				    $tb[$code]['total_selling_price'][$y]+=$tb[$code]['total_selling_price'][$ym];

			    // join stock data (checking to don't add self data)
				if (!isset($sid_arr[$sid])){
	                $new_stock_data[$y][$sid]['opening'] = $stock_data[$lbl][$sid]['opening'];
	                $sid_arr[$sid]=$sid;
				}

				if($_REQUEST['group_by_sku'])	$sid = $parent_sku_item_id;	//use parent data
				
	            $new_stock_data[$y][$sid]['in'] += $stock_data[$lbl][$sid]['in'];
	            $new_stock_data[$y][$sid]['out'] += $stock_data[$lbl][$sid]['out'];
                $new_stock_data[$y][$sid]['pos_qty'] += $stock_data[$lbl][$sid]['pos_qty'];
				
				// closing alrdy pre-calculated if group by sku
				if($_REQUEST['group_by_sku'])	$new_stock_data[$y][$sid]['closing'] =  $stock_data[$lbl][$sid]['closing'];
				
			    $uq_pages2[$y][$code] = 1;
			    $months[$y][$m]=1;
			}

			//recalculate closing amount
			if(!$_REQUEST['group_by_sku']){
				foreach ($sid_arr as $s_id){
					$new_stock_data[$y][$s_id]['closing'] = $new_stock_data[$y][$s_id]['opening']+$new_stock_data[$y][$s_id]['in']-$new_stock_data[$y][$s_id]['out']-$new_stock_data[$y][$s_id]['pos_qty'];
				}
			}
			
			$y_before=$y;
			
		}
		
		$stock_data=$new_stock_data;
		$uq_pages = $uq_pages2;
		unset($uq_pages2);
	}

	//print_r($stock_data);

	//print_r($uq_pages);

	if ($_REQUEST['print'] || $_REQUEST['excelxml'])
	{
		get_dept_title();
	}
	
	if ($_REQUEST['print'])
 	{
 	    // assign report title and subtitle

 	    if (BRANCH_CODE=='HQ' && $_REQUEST['branch_id']>0)
 	    {
 	        $con->sql_query("select * from branch where id = ".intval($_REQUEST['branch_id']));
		}
		else
		{
		    $con->sql_query("select * from branch where id = ".$sessioninfo['branch_id']);
		}
		$smarty->assign("branch", $con->sql_fetchrow());

		global $_PRINTTITLE;
        $smarty->assign("title", "$_PRINTTITLE");
 	    
 	    // calculate page count
 	    define('PAGE_SIZE', 35);
 	    $smarty->assign('PAGE_SIZE',PAGE_SIZE);
 	    
 	    $pt = 0;
		foreach($uq_pages as $k)
		{
			$pt += ceil(count($k)/PAGE_SIZE);
		}
 	    $smarty->assign("page_total", $pt);
	}

	$page_n = 0;
	
	//print_r($uq_pages);
	//print_r($tb);
	//if have $uq_pages only run foreach to avoid warning appear.
	if($uq_pages){
	//		$smarty->display("sales_report.generic.table.tpl");
		foreach($uq_pages as $page=>$codes)
		{
		    $closing_amt_added = array();
		    
			if ($_REQUEST['by_monthly']){

			    $lbl = $page;
				$y = $page;
				$smarty->assign("subtitle_r", intval($y));
				if (!$_REQUEST['print']) print "<h3><br />" . intval($y) . "</h3>";
			}else{
			    list($y,$m) = explode("-",$page);
			    $lbl = sprintf('%04d%02d',$y,$m);

				$smarty->assign("subtitle_r", str_month($m) . " " . intval($y));
				if (!$_REQUEST['print']) print "<h3><br />" . str_month($m) . " " . intval($y) . "</h3>";

			    $DAYS_OF_MONTH = days_of_month($m,$y);
			}

			    $smarty->assign('lbl',$lbl);

		    // added by andy
		    //get_balance($y, $m, $codes);
			$hd = '<table class="sortable tb xsmall" id=_t style="" cellspacing=0 cellpadding=2 border=0 width=100%>
			<tr  bgcolor="#e2e3e5">
			    <th>&nbsp;</th>
				<th>ARMS Code</th>
				<th>T</th>
				<th>Art No</th>
				<th>SKU Description</th>
				<th>Type</th>
				<th>Opening</th>';
			if ($_REQUEST['by_monthly']){
				foreach ($months[$y] as $m => $dummy){
				    $hd .= "<th>".str_month($m)."</th>";
				}

			}else{
				for ($i=1;$i<=$DAYS_OF_MONTH;$i++)
				{
				    $hd .= "<th>$i</th>";
				}
			}
			if (defined('smo_report')){
				$hd .= '
				<th>T.Qty</th>
				<th>In</th>
				<th>Out</th>
				<th>Closing</th>';
			}else{
				$hd .= '
				<th>T.Qty</th>
				<th>Amount</th>
				<th>In</th>
				<th>Out</th>
				<th>Closing</th>
				<th>S/Price</th>
				<th>Closing Amt</th>';
				
			}
			
			if(privilege('SHOW_COST')){
				$hd .= "<th>Total Cost</th>
						<th>GP</th>
						<th>GP(%)</th>";
			}
			
			$hd .= "</tr>";

			$counter=0;
			$total_row=count($codes);	
			$row = 0;
	        $total = array();
	        $total_amount = array();
	        $totalqty = 0; $totalamt = 0; $totalcost = 0; $totalgp = 0;
			if (!$_REQUEST['print']) print $hd;

            $total = array();
            $total_amount = array();
            
   			if ($_REQUEST['by_monthly']){
    	       	@ksort($codes);
			}
			foreach($codes as $code => $dummy)
			{
			    $rows = $tb[$code];
			    
			    if ($_REQUEST['print'] and $row % PAGE_SIZE == 0)
			    {
			        $page_n++;
					if ($row>0)
					{
						print "</table>";
						$smarty->display("report_footer.landscape.tpl");
					}

					$smarty->assign("page_n", $page_n);
					$smarty->display("report_header.landscape.tpl");
					print $hd;

					$smarty->assign("skip_header",1);
				}

				$row ++;
				//list($arms_code,$price_type) = split("/", $code);
				list($price_type,$arms_code) = explode("/", $code);
				$sid = $sku_info[$arms_code]['id'];
				
				if($sid)
				{

              		$data = $rows['data'];
    			    $data['sku_type'] = substr($data['sku_type'],0,1);
    			    if ($_REQUEST['print']) $data['description'] = substr($data['description'],0,50);

    			    if($price_type==''){
    					$price_type=" ";	
    				}			    
    			    //START DIVIDE BY PRICE TYPE
    				if(!$last_sku_type){
    					$last_sku_type=$price_type;
    					$counter++;				
    				}
    			    if($price_type!=$last_sku_type){
    					echo "<tr bgcolor='#e2e3e5'><th align=right colspan=6>$last_sku_type Subtotal</th>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['opening'])."</td>";
	        			if ($_REQUEST['by_monthly']){
							foreach ($months[$y] as $m => $dummy){
	  					    if ($total_by_price_type[$m]>0)
	    					        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($amount_by_price_type[$m],2)."</td>";
	    					        else print "<td align=center>".smarty_qty_nf($total_by_price_type[$m])."</td>";
	    						else
	    							print "<td>&nbsp;</td>";
	    						$total_by_price_type[$m]=0;
	    						$amount_by_price_type[$m]=0;
	 						}
						}else{
	    					for ($i=1;$i<=$DAYS_OF_MONTH;$i++){
	    					    if ($total_by_price_type[$i]>0)
	    					        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($amount_by_price_type[$i],2)."</td>";
	    					        else print "<td align=center>".smarty_qty_nf($total_by_price_type[$i])."</td>";
	    						else
	    							print "<td>&nbsp;</td>";
	    						$total_by_price_type[$i]=0;
	    						$amount_by_price_type[$i]=0;
	    					}
						}
    					print "<td align=right>".smarty_qty_nf($totalqty_by_price_type)."</td>";
                        if (!defined('smo_report'))
	    					printf ("<td align=right>%.2f</td>", $totalamt_by_price_type);            //======>smo dun want this

    					print "<td align=center>".smarty_qty_nf($total_by_price['in'])."</td>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['out'])."</td>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['closing'])."</td>";
    					if (!defined('smo_report')){
	    					print "<td>&nbsp;</td>";            //======>smo dun want this
							print "<td align=right>".number_format($total_by_price['closing_bal'],2)."</td>"; //======>smo dun want this
						}
    					print "</tr>";
    						
    					$totalqty_by_price_type=0;
    					$totalamt_by_price_type=0;
    					$last_sku_type=$price_type;	
    					$counter++;
    					$total_by_price = array();
    				}
    				//END DIVIDE BY PRICE TYPE
    				
    				$data['sku_type'] = $data['sku_type'] ? $data['sku_type'] : "&nbsp;";
    				$selling_price = $rows['amount'][$page]/$rows['qty'][$page];
    				$cost = $rows['ttl_cost'][$page]/$rows['qty'][$page];
    				
    				print "
    				<tr>
    				    <td align=right>$row.</td>
    					<td>$arms_code</td>
    					<td align=center>".$data['sku_type']."</td>
    			        <td>$data[artno_mcode]&nbsp;</td>
    			        <td width=20%>".substr($data['description'],0,30)."</td>
    			        <td>$price_type&nbsp;</td>
    					<td align=center>".smarty_qty_nf($stock_data[$lbl][$sid]['opening'])."".$stock_data[$lbl][$sid]['sc']."</td>";
    					$sc_day = $stock_data[$lbl][$sid]['sc'];
	        			if ($_REQUEST['by_monthly']){
							foreach ($months[$y] as $m => $dummy){
		    			    	$dt = sprintf("%d-%02d",$y,$m);

		    				    if (isset($rows[$dt]))
		    				        if ($_REQUEST['report_type'] == 'amt') print "<td align=center class=\"\">".number_format($selling_price*$rows[$dt],2)."</td>";
		    				        else print "<td align=center class=\"\">".smarty_qty_nf($rows[$dt])."</td>";
		    					else
		    						print "<td class=\"\">&nbsp;</td>";
		    					$total[$m] += $rows[$dt];
		    					$total_amount[$m] += round($selling_price*$rows[$dt],2);
		    					$total_by_price_type[$m] += $rows[$dt];
		    					$amount_by_price_type[$m] += round($selling_price*$rows[$dt],2);

							}
						}else{
		    				for ($i=1;$i<=$DAYS_OF_MONTH;$i++)
		    				{
		    			    	$dt = sprintf("%d-%02d-%02d",$y,$m,$i);
		    			    	if($sc_day>0&&$i<$sc_day)   $before_sc_class = "before_sc";
		    			    	else    $before_sc_class = '';

		    				    if (isset($rows[$dt]))
		    				        if ($_REQUEST['report_type'] == 'amt') print "<td align=center class=\"$before_sc_class\">".number_format($selling_price*$rows[$dt],2)."</td>";
		    				        else print "<td align=center class=\"$before_sc_class\">".smarty_qty_nf($rows[$dt])."</td>";
		    					else
		    						print "<td class=\"$before_sc_class\">&nbsp;</td>";
		    					$total[$i] += $rows[$dt];
		    					$total_amount[$i] += round($selling_price*$rows[$dt],2);
		    					$total_by_price_type[$i] += $rows[$dt];
		    					$amount_by_price_type[$i] += round($selling_price*$rows[$dt],2);
		    				}
						}
    				//$selling_price = $rows['qty'][$page]? $rows['amount'][$page]/$rows['qty'][$page] : 0;
    				/*if($rows['total_selling_price'][$page]){    // closing amt already pre-calculated
                        $closing_bal = $rows['total_selling_price'][$page];
                        $selling_price = $data['selling_price'];
    				}else{
                        if( $rows['qty'][$page]){
    	                    $selling_price = $rows['amount'][$page]/$rows['qty'][$page];
    					}else{
    	                    $selling_price = $data['selling_price'];
    					}
    					$closing_bal = $stock_data[$lbl][$sid]['closing'] * $selling_price;
    				}*/
    				
    				$closing_bal = $stock_data[$lbl][$sid]['closing'] * $selling_price;
    				$row_gp = $rows['amount'][$page]-$rows['ttl_cost'][$page];
					$row_gp_per = $row_gp/$rows['amount'][$page]*100;
    				
    				print "<td align=right>".smarty_qty_nf($rows['qty'][$page])."</td>";
                    if (!defined('smo_report'))
 		   				print "<td align=right>".number_format($rows['amount'][$page],2)."</td>";            //======>smo dun want this

    				print "<td align=center>".smarty_qty_nf($stock_data[$lbl][$sid]['in'])."</td>";
    				print "<td align=center>".smarty_qty_nf($stock_data[$lbl][$sid]['out'])."</td>";
    				print "<td align=center>".smarty_qty_nf($stock_data[$lbl][$sid]['closing'])."</td>";
	                if (!defined('smo_report')){
	    				print "<td align=right>".@number_format($selling_price,2)."</td>";            //======>smo dun want this
	    //				print "<td align=right>".@number_format($data['selling_price'],2)."</td>";
	    			    print "<td align=right>".number_format($closing_bal,2)."</td>";            //======>smo dun want this
                    }

					if(privilege('SHOW_COST')){
						print "<td align=right>".number_format($rows['ttl_cost'][$page], $config['global_cost_decimal_points'])."</td>";
						print "<td align=right>".number_format($row_gp, $config['global_cost_decimal_points'])."</td>";
						print "<td align=right>".number_format($row_gp_per, 2)."</td>";
					}

    				$totalqty += $rows['qty'][$page];
    				$totalamt += $rows['amount'][$page];
    				$totalcost += $rows['ttl_cost'][$page];
    				$totalgp += $row_gp;
    				
    				$totalqty_by_price_type += $rows['qty'][$page];
    				$totalamt_by_price_type += $rows['amount'][$page];
    				$totalcost_by_price_type += $rows['ttl_cost'][$page];
    				$totalgp_by_price_type += $row_gp;
    				
    				if(!$closing_amt_added[$sid]){
                        $total['opening'] += $stock_data[$lbl][$sid]['opening'];
    		            $total['in'] += $stock_data[$lbl][$sid]['in'];
    		            $total['out'] += $stock_data[$lbl][$sid]['out'];
    		            $total['closing'] += $stock_data[$lbl][$sid]['closing'];
    					$total['closing_bal'] += $closing_bal;
    
    					$closing_amt_added[$sid] = $sid;
    				}
    				
    				
    	            $total_by_price['opening'] += $stock_data[$lbl][$sid]['opening'];
    	            $total_by_price['in'] += $stock_data[$lbl][$sid]['in'];
    	            $total_by_price['out'] += $stock_data[$lbl][$sid]['out'];
    	            $total_by_price['closing'] += $stock_data[$lbl][$sid]['closing'];
    	            $total_by_price['closing_bal'] += $closing_bal;
    	            
    				print "</tr>";
    				
    				//START DIVIDE BY PRICE TYPE (LAST ROW)
    				if($counter>1 && $total_row==$row){
    					echo "<tr bgcolor='#e2e3e5'><th align=right colspan=6>$price_type Subtotal</th>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['opening'])."</td>";
	        			if ($_REQUEST['by_monthly']){
							foreach ($months[$y] as $m => $dummy){
	    					    if ($total_by_price_type[$m]>0)
	    					        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($amount_by_price_type[$m],2)."</td>";
	    					        else print "<td align=center>".smarty_qty_nf($total_by_price_type[$m])."</td>";
	    						else
	    							print "<td>&nbsp;</td>";
	    						$total_by_price_type[$m]=0;
	    						$amount_by_price_type[$m]=0;

							}
						}else{
	    					for ($i=1;$i<=$DAYS_OF_MONTH;$i++){
	    					    if ($total_by_price_type[$i]>0)
	    					        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($amount_by_price_type[$i],2)."</td>";
	    					        else print "<td align=center>".smarty_qty_nf($total_by_price_type[$i])."</td>";
	    						else
	    							print "<td>&nbsp;</td>";
	    						$total_by_price_type[$i]=0;
	    						$amount_by_price_type[$i]=0;
	    					}
						}
    					print "<td align=right>".smarty_qty_nf($totalqty_by_price_type)."</td>";
                        if (!defined('smo_report'))
							printf ("<td align=right>%.2f</td>", $totalamt_by_price_type);            //======>smo dun want this

    					print "<td align=center>".smarty_qty_nf($total_by_price['in'])."</td>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['out'])."</td>";
    					print "<td align=center>".smarty_qty_nf($total_by_price['closing'])."</td>";
                        if (!defined('smo_report')){
	    					print "<td>&nbsp;</td>";            //======>smo dun want this
	    					print "<td align=right>".number_format($total_by_price['closing_bal'],2)."</td>"; //======>smo dun want this
	    					print "<td align=right>".number_format($totalcost_by_price_type, $config['global_cost_decimal_points'])."</td>";
    					}
						if(privilege("SHOW_COST")){
	    					print "<td align=right>".number_format($totalgp_by_price_type, $config['global_cost_decimal_points'])."</td>";
							$totalgpper_by_price_type = $totalgp_by_price_type / $totalamt_by_price_type * 100;
	    					print "<td align=right>".number_format($totalgpper_by_price_type, 2)."</td>";
						}
    					print "</tr>";
    					$last_sku_type='';
    					$counter=0;	
    					$totalqty_by_price_type=$totalamt_by_price_type=$totalcost_by_price_type=$totalgp_by_price_type=$totalgpper_by_price_type=0;	
    				}
    				//END DIVIDE BY PRICE TYPE (LAST ROW)		     
        		}
				
			}
			print "<tr class=sortbottom><th align=right colspan=6>Total</th>";
			print "<td align=center>".smarty_qty_nf($total['opening'])."</td>";
   			if ($_REQUEST['by_monthly']){
				foreach ($months[$y] as $m => $dummy){
				    if ($total[$m]>0)
				        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($total_amount[$m],2)."</td>";
				        else print "<td align=center>".smarty_qty_nf($total[$m])."</td>";
					else
						print "<td>&nbsp;</td>";
				}
			}else{
				for ($i=1;$i<=$DAYS_OF_MONTH;$i++)
				{
				    if ($total[$i]>0)
				        if ($_REQUEST['report_type'] == 'amt') print "<td align=center>".number_format($total_amount[$i],2)."</td>";
				        else print "<td align=center>".smarty_qty_nf($total[$i])."</td>";
					else
						print "<td>&nbsp;</td>";
				}
			}
			print "<td align=right>".smarty_qty_nf($totalqty)."</td>";
            if (!defined('smo_report')) print "<td align=right>".number_format($totalamt, 2)."</td>";

			print "<td align=center>".smarty_qty_nf($total['in'])."</td>";
			print "<td align=center>".smarty_qty_nf($total['out'])."</td>";
			print "<td align=center>".smarty_qty_nf($total['closing'])."</td>";
            if (!defined('smo_report')){
				print "<td>&nbsp;</td>";            //======>smo dun want this
				print "<td align=right>".number_format($total['closing_bal'],2)."</td>";            //======>smo dun want this
			}
			if(privilege("SHOW_COST")){
				print "<td align=right>".number_format($totalcost, $config['global_cost_decimal_points'])."</td>";
				print "<td align=right>".number_format($totalgp, $config['global_cost_decimal_points'])."</td>";
				$totalgpper = $totalgp / $totalamt * 100;
				print "<td align=right>".number_format($totalgpper, 2)."</td>";
			}
			print "</tr>";
			print "</table>";
			if ($_REQUEST['print'] && $page_n < $pt) $smarty->display("report_footer.landscape.tpl");
		}
	}

	//print_r($stock_data);
	if ($_REQUEST['print']) {
?>
<br />
<table>
<tr>
<th width=150 align=left>Checking By</td><td width=50>&nbsp;</td>
<th align=left width=150>Approved By</td><td width=50>&nbsp;</td>
<th align=left width=150>Confirm By</td><td width=50>&nbsp;</td>
</tr>
</table>
<table style="padding-top:70px">
<tr>
<td align=left width=150 style="border-top:1px solid black">Promoter / Staff </td>
<td width=50>&nbsp;</td>
<td align=left width=150 style="border-top:1px solid black">Branch Manager</td>
<td width=50>&nbsp;</td>
<td align=left width=150 style="border-top:1px solid black">Line Officer</td>
</tr>
</table>
<table>
<tr>
<td width=150 align=left>Date :</td><td width=50>&nbsp;</td>
<td align=left width=150>Date :</td><td width=50>&nbsp;</td>
<td align=left width=150>Date :</td><td width=50>&nbsp;</td>
</tr>
</table>
<br>
<?
	$smarty->display("report_footer.landscape.tpl");
	}
}

function load_branch_group($id=0){
		global $con,$smarty;

		// initial table
		$con->sql_query("CREATE TABLE if not exists `branch_group` ( `id` int(11) NOT NULL auto_increment, code char(10),`description` char(100), PRIMARY KEY (`id`),unique(code) )") or die(mysql_error());
		$con->sql_query("CREATE TABLE if not exists `branch_group_items` ( `branch_group_id` int(11) NOT NULL default '0', `branch_id` int(11) NOT NULL default '0', PRIMARY KEY (`branch_group_id`,branch_id),unique(branch_id) ) ") or die(mysql_error());

		$branch_group = array();

		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "where bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}


		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id $where2 order by branch.sequence, branch.code");
		while($r = $con->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}


		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
}

function get_stock_balance($sb_item_arr){
	global $con, $stock_data, $sku_info, $sessioninfo, $branch_group, $con_multi;
	if(BRANCH_CODE=='HQ'){

	  if($_REQUEST['branch_id']=="")
    {
        $res = $con->sql_query("select id from branch order by sequence,code");
        while($r = $con->sql_fetchrow($res))
        {
            $branch_ids[]=$r['id'];
        }

    }
    elseif(strpos($_REQUEST['branch_id'],'bg,')===0)
    {   // is branch group
  			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
  			foreach($branch_group['items'][$bg_id] as $bid=>$r){
  			    $branch_ids[] = $bid;
  			}
		}else{  // single branch
  			//$branch_id = intval($_REQUEST['branch_id']);
  			$branch_ids[] = intval($_REQUEST['branch_id']);
		}

	}
	else   $branch_ids[] = intval($sessioninfo['branch_id']);
	
	if(!$sb_item_arr)   return;
	$already_get_opening = array();

	$sku_last_closing = array();
	
	foreach($sb_item_arr as $date_key=>$sid_list){
		$y = substr($date_key, 0, 4);
		$m = substr($date_key, 4, 2);
		$from_date = $y."-".$m."-1";
		$to_date = $y."-".$m."-".days_of_month($m, $y);
		if(strtotime($from_date)<strtotime($_REQUEST['from'])) $from_date = $_REQUEST['from'];
		if(strtotime($to_date)>strtotime($_REQUEST['to'])) $to_date = $_REQUEST['to'];
        $lbl = date("Ym", strtotime($from_date));

		// loop items to get opening qty
        foreach($sid_list as $sid=>$dummy){
            if(!$already_get_opening[$sid]){
                foreach($branch_ids as $bid){
				    $opening_date = date("Y-m-d", strtotime("-1 day", strtotime($from_date)));
					$opening_y = date("Y", strtotime($opening_date));
					$con_multi->sql_query("select qty from stock_balance_b".$bid."_".$opening_y." where sku_item_id=$sid and ".ms($opening_date)." between from_date and to_date limit 1",false,false);
					if($con_multi->sql_numrows()>0){
		                $stock_data[$lbl][$sid]['opening'] = $con_multi->sql_fetchfield(0);
					}
					$con_multi->sql_freeresult();
					$already_get_opening[$sid] = 1;
				}
			}else{
				//$stock_data[$lbl][$sid]['opening'] = $stock_data[$last_lbl][$sid]['closing'];
				$stock_data[$lbl][$sid]['opening'] = $sku_last_closing[$sid];
			}   
		}
        
        // GRN
		$con_multi->sql_query("select grn_items.sku_item_id as sid,year(rcv_date) as y, month(rcv_date) as m,
	sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty
	from grn_items
	left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
	left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
	left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
	where grn.branch_id in (".join(',', $branch_ids).") and rcv_date between '$from_date' and '$to_date' and grn_items.sku_item_id in (".join(',',array_keys($sid_list)).") and grn.approved=1 and grn.status=1 and grn.active=1 and grr.active=1
	group by sid,y,m") or die(mysql_error());
		while($r = $con_multi->sql_fetchrow()){
		    $lbl = sprintf("%04d%02d", $r['y'], $r['m']);
			$stock_data[$lbl][$r['sid']]['rcv_qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult();

        // ADJ
		$con_multi->sql_query("select ai.sku_item_id as sid, sum(qty) as qty, year(adjustment_date) as y, month(adjustment_date) as m
	from adjustment_items ai
	left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
	where ai.branch_id in (".join(',', $branch_ids).") and adjustment_date between '$from_date' and '$to_date' and ai.sku_item_id in (".join(',',array_keys($sid_list)).") and adj.active=1 and adj.approved=1 and adj.status=1
	group by sid,y,m") or die(mysql_error());

		while($r = $con_multi->sql_fetchrow()){
		    $lbl = sprintf("%04d%02d", $r['y'], $r['m']);
			//if($r['type']=='p')	$stock_data[$lbl][$r['sid']]['adj_in'] += $r['qty'];
			//elseif($r['type']=='n')	$stock_data[$lbl][$r['sid']]['adj_out'] += abs($r['qty']);
			if($r['qty']>0)	$stock_data[$lbl][$r['sid']]['adj_in'] += $r['qty'];
			else	$stock_data[$lbl][$r['sid']]['adj_out'] += abs($r['qty']);
		}
		$con_multi->sql_freeresult();
		
		// DO
		$con_multi->sql_query("select do_items.sku_item_id as sid, sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, year(do_date) as y, month(do_date) as m
	from do_items
	left join uom on do_items.uom_id=uom.id
	left join do on do_id = do.id and do_items.branch_id = do.branch_id
	where do_items.branch_id in (".join(',', $branch_ids).") and do_date between '$from_date' and '$to_date' and do_items.sku_item_id in (".join(',',array_keys($sid_list)).") and do.active=1 and do.approved=1 and do.checkout=1 and do.status=1
	group by sid,y,m") or die(mysql_error());

		while($r = $con_multi->sql_fetchrow()){
		    $lbl = sprintf("%04d%02d", $r['y'], $r['m']);
			$stock_data[$lbl][$r['sid']]['do_qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult();
		
		// GRA
		$con_multi->sql_query("select gra_items.sku_item_id as sid, sum(qty) as qty, year(return_timestamp) as y, month(return_timestamp) as m
		from gra_items
	left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
	where gra.branch_id in (".join(',', $branch_ids).") and return_timestamp between '$from_date' and '$to_date' and gra_items.sku_item_id in (".join(',',array_keys($sid_list)).") and gra.status=0 and gra.returned=1
	group by sid,y,m") or die(mysql_error());

		while($r = $con_multi->sql_fetchrow()){
		    $lbl = sprintf("%04d%02d", $r['y'], $r['m']);
			$stock_data[$lbl][$r['sid']]['gra_qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult();
    	$last_lbl = $lbl;
    	foreach($sid_list as $sid=>$dummy){
            $stock_data[$lbl][$sid]['in'] = $stock_data[$lbl][$sid]['rcv_qty']+$stock_data[$lbl][$sid]['adj_in'];
			$stock_data[$lbl][$sid]['out'] = $stock_data[$lbl][$sid]['adj_out']+$stock_data[$lbl][$sid]['do_qty']+$stock_data[$lbl][$sid]['gra_qty'];
			$stock_data[$lbl][$sid]['closing'] = $stock_data[$lbl][$sid]['opening']+$stock_data[$lbl][$sid]['in']-$stock_data[$lbl][$sid]['out']-$stock_data[$lbl][$sid]['pos_qty'];
			
			$sku_last_closing[$sid] = $stock_data[$lbl][$sid]['closing'];
		}
	}
	
	if($sessioninfo['u']=='admin'){
		//print_r($stock_data);
	}
}

function get_dept_title(){
	global $con,$smarty;
    if ($_REQUEST['department_id'])
	{
		$con->sql_query("select description from category where id = ".intval($_REQUEST['department_id']));
		$c = $con->sql_fetchrow();
		$cat = "&nbsp;&nbsp; Department: $c[0]";
	}
	else
	    $cat = '';

    $smarty->assign("subtitle_m", get_print_title($cat));
    
}

?>
