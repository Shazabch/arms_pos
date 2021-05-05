<?php
/*
11/17/2010 3:17:42 PM Andy
- Add cron to generate report cache data.
- Change report to use cache data.
*/
$maintenance->check(23);

function load_group_header($id){
	global $con, $hqcon;
    $id = mi($id);
	if(!$id)    return false;
	
	if(!$hqcon)	$hqcon = connect_hq();
	
	$hqcon->sql_query("select * from sku_monitoring_group where id=$id");
	$form = $hqcon->sql_fetchrow();
	$hqcon->sql_freeresult();
	return $form;
}

function regen_sku_monitoring_group_batch($id){
	global $con, $sessioninfo, $hqcon;
	
	if(!$hqcon)	$hqcon = connect_hq();
	$id = mi($id);
	if(!$id)    die('Invalid ID');

	// get group details
	$form = load_group_header($id);
	if(!$form)  die('Invalid ID');
	
	// get group items
	$hqcon->sql_query("select sku_item_id from sku_monitoring_group_items where sku_monitoring_group_id=$id");
	$sid_list = array();
	while($r = $hqcon->sql_fetchrow()){
        $sid_list[] = mi($r['sku_item_id']);
	}
	$hqcon->sql_freeresult();
	
	// delete batch first
	$hqcon->sql_query("delete from sku_monitoring_group_batch_items where sku_monitoring_group_id=$id");
	
	if(!$sid_list)  return 'No item in group.';
	foreach($sid_list as $sid){ // loop for each sku items
	    regen_sku_monitoring_group_batch_item($id, $sid, $form);
	}
	$hqcon->sql_query("update sku_monitoring_group set changed=0 where id=$id");
	log_br($sessioninfo['id'], 'SKU Monitoring Group', $id, "SKU Monitoring Group ID# $id Batch Regenerated.");
	return true;
}

function regen_sku_monitoring_group_batch_item($group_id, $sid, $form = ''){
	global $con, $sessioninfo, $hqcon;
	$sid = mi($sid);
	$group_id = mi($group_id);
	if(!$group_id||!$sid)  return;  // invalid group id or sku item id
	
	if(!$hqcon)	$hqcon = connect_hq();
	
	if(!$form){ // get group details
        $form = load_group_header($group_id);
		if(!$form)  return;
	}
	// delete batch first
	$hqcon->sql_query("delete from sku_monitoring_group_batch_items where sku_monitoring_group_id=$group_id and sku_item_id=$sid");
	
	// get grn year, month list (not ibt)
	$sql = "select distinct year(grr.rcv_date) as y, month(grr.rcv_date) as m
from grn_items gi
left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join po on po.po_no=gri.doc_no and gri.type='PO'
where grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and (po.po_no is null or po.is_ibt=0) and gi.sku_item_id=$sid and grr.rcv_date>=".ms($form['start_monitoring_date'])." order by y,m";
	$q1 = $hqcon->sql_query($sql);
	while($r = $hqcon->sql_fetchrow($q1)){
	    $upd = array();
	    $upd['sku_monitoring_group_id'] = $group_id;
	    $upd['year'] = $r['y'];
	    $upd['month'] = $r['m'];
	    $upd['sku_item_id'] = $sid;
	    $upd['date'] = $r['y'].'-'.$r['m'].'-1';
		$hqcon->sql_query("replace into sku_monitoring_group_batch_items ".mysql_insert_by_field($upd));
	}
	$hqcon->sql_freeresult($q1);
}

function update_sku_monitoring_group_items_changed($po_no, $branch_id, $grn_id){
	global $con, $sessioninfo;
	$branch_id = mi($branch_id);
	$grn_id = mi($grn_id);
	
	if(!$po_no||!$branch_id||!$grn_id)  return; // cannot proceed without one of these parameter
	
	$con->sql_query("select is_ibt from po where po_no=".ms($po_no));
	$is_ibt = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	if(!$is_ibt)    return; // not ibt adj
	
	// get all grn sku item id
	$sid_arr = array();
	$con->sql_query("select distinct sku_item_id as sid from grn_items where branch_id=$branch_id and grn_id=$grn_id");
	while($r = $con->sql_fetchrow()){
        $sid_arr[] = mi($r['sid']);
	}
	$con->sql_freeresult();
	
	// get sku monitoring group id
	$group_id_arr = array();
	$con->sql_query("select distinct sku_monitoring_group_id from sku_monitoring_group_items where sku_item_id in (".join(',',$sid_arr).")");
	while($r = $con->sql_fetchrow()){
        $group_id_arr[] = mi($r[0]);
	}
	$con->sql_freeresult();
	
	// update changed=1
	$con->sql_query("update sku_monitoring_group set changed=1 where id in (".join(',',$group_id_arr).")");
}

function generate_sku_monitoring_group_report_data($bid, $sku_monitoring_group_id){
	global $con;
	
	// escape interger
	$bid = mi($bid);
	$sku_monitoring_group_id = mi($sku_monitoring_group_id);
	$end_date = date("Y-m-d");
	
	if(!$bid || !$sku_monitoring_group_id)  return false;
	
	if(defined('TERMINAL')){
	    //print "\nGenerating report data... Branch ID#$bid, SKU Monitoring Group ID#$sku_monitoring_group_id";
	}
	$starttime = microtime(true);
	$total_rows = 0;
	
	$tbl_name = "sku_monitoring_2_report_cache_b".$bid;
	$con->sql_query("create table if not exists $tbl_name (
		sku_item_id int not null,
		year int not null,
		month tinyint not null,
		date date not null,
		got_repeat tinyint not null default 0,
		opening_qty double,
		opening_total_cost double,
		grn_qty double,
		grn_total_cost double,
		stock_check_adj_qty double,
		pos_qty double,
		pos_total_cost double,
		pos_total_amt double,
		gra_qty double,
		adj_qty double,
		do_qty double,
		ibt_adj_qty double,
		variances_disc_qty double,
		variances_disc_amt double,
		variances_markup_qty double,
		variances_markup_amt double,
		variances_markdown_qty double,
		variances_markdown_amt double,
		primary key(sku_item_id, year, month),
		index sku_item_id_n_date(sku_item_id, date)
	)");
	
	$ibt_cache_tbl = 'sku_monitoring_2_ibt_cache';
	$con->sql_query("create table if not exists $ibt_cache_tbl(
        sku_item_id int not null,
		year int not null,
		month tinyint not null,
		date date not null,
		by_branch_id int,
		grn_qty double,
		grn_total_cost double,
		primary key (sku_item_id, year, month, by_branch_id),
		index sku_item_id_n_date_n_by_branch_id (sku_item_id, date, by_branch_id)
	)");
	
	$max_ym = date("Ym");   // max date key
	
	// get all items
	$sql = "select smgi.*, (select min(smgbi.date) from sku_monitoring_group_batch_items smgbi where smgbi.sku_monitoring_group_id=smgi.sku_monitoring_group_id and smgbi.sku_item_id=smgi.sku_item_id) as start_date,si.selling_price
from sku_monitoring_group_items smgi
left join sku_items si on si.id=smgi.sku_item_id
where smgi.sku_monitoring_group_id=$sku_monitoring_group_id
having start_date>0";
	$q_sku = $con->sql_query($sql);
	
	while($sku = $con->sql_fetchassoc($q_sku)){
	    $data = array();
	    $ibt_data = array();
	    
	    $sid = mi($sku['sku_item_id']);
	    $start_date = $sku['start_date'];
	    
	    $current_y = $start_y = mi(date('Y', strtotime($start_date)));
	    $current_m = $start_m = mi(date('m', strtotime($start_date)));
	    
	    //if(defined('TERMINAL'))	print "\nSKU item id: ".$sid;
	    
		// delete cache
		//if(defined('TERMINAL'))	print "\nDeleting cache...";
		$con->sql_query("delete from $tbl_name where sku_item_id=$sid and date>".ms($start_date));
        $total_rows += $con->sql_affectedrows();
        
        $con->sql_query("delete from $ibt_cache_tbl where sku_item_id=$sid and by_branch_id=$bid and date>".ms($start_date));
        $total_rows += $con->sql_affectedrows();
        
		// generate blank data first
		$current_ym = sprintf("%04d%02d", $current_y, $current_m);
		while($current_ym <= $max_ym){  // loop until today
            $data[$current_ym]['year'] = $current_y;
            $data[$current_ym]['month'] = $current_m;
            $data[$current_ym]['date'] = $current_y.'-'.$current_m.'-1';
            
            $current_m++;
            if($current_m>12){
                $current_m = 1;
                $current_y++;
			}
			$current_ym = sprintf("%04d%02d", $current_y, $current_m);
		}
		
		if(!$data)  continue;   // already in latest month
		
		// get next grn date, mark it as repeat
		//if(defined('TERMINAL')) print "\nChecking repeat...";
		$m2 = $start_m+1;
		$y2 = $start_y;
		if($m2>12){
			$m2 = 1;
			$y2++;
		}
		$repeat_date_from = $y2.'-'.$m2.'-1';
        $sql = "select distinct year(grr.rcv_date) as y, month(grr.rcv_date) as m
from grn_items gi
left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join po on po.po_no=gri.doc_no and gri.type='PO'
where grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and (po.po_no is null or po.is_ibt=0) and gi.sku_item_id=$sid and grr.rcv_date>=".ms($repeat_date_from);
		$q_repeat = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_repeat)){
			$date_key = sprintf("%04d%02d", $r['y'], $r['m']);
			$data[$date_key]['got_repeat'] = 1;
			$total_rows ++;
		}
		$con->sql_freeresult($q_repeat);

		// get opening for each month
		//if(defined('TERMINAL')) print "\nGetting opening...";
		foreach($data as $date_key=>$r){
			$sb_tbl = 'stock_balance_b'.$bid.'_'.$r['year'];
			$sql = "select if(from_date=".ms($r['date']).",start_qty,qty) as qty, cost
			from $sb_tbl
			where sku_item_id=$sid and ".ms($r['date'])." between from_date and to_date limit 1";
			$q_opening = $con->sql_query($sql,false,false);
			$temp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$data[$date_key]['opening_qty'] += $temp['qty'];
			$data[$date_key]['opening_total_cost'] += ($temp['qty']*$temp['cost']);
			unset($temp);
			$total_rows++;
		}
		
		// get GRN data and ibt adj
		//if(defined('TERMINAL')) print "\nGetting grn and ibt adjustment...";
		$sql = "select grr.rcv_date,po.is_ibt,
            sum(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*uom.fraction + gi.pcs, gi.acc_ctn*uom.fraction+gi.acc_pcs)) as qty,
            sum(
			  if(gi.acc_cost is null, gi.cost, gi.acc_cost)
			  *
			  if (gi.acc_ctn is null and gi.acc_pcs is null,
			  	gi.ctn + gi.pcs / uom.fraction,
			  	gi.acc_ctn + gi.acc_pcs / uom.fraction
			  )
			) as total_cost
from grn_items gi
left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join uom on uom.id=gi.uom_id
left join po on po.po_no=gri.doc_no and gri.type='PO'
where grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and gi.branch_id=$bid and gi.sku_item_id=$sid and grr.rcv_date between ".ms($start_date)."  and ".ms($end_date)." group by grr.rcv_date,po.is_ibt";
		$q_grn = $con->sql_query($sql);
        while($r = $con->sql_fetchassoc($q_grn)){
            $date_key = date("Ym", strtotime($r['rcv_date']));
            if($r['is_ibt']){
                $ibt_data[$date_key]['grn_qty'] += mf($r['qty'])*-1;
            	$ibt_data[$date_key]['grn_total_cost'] += mf($r['total_cost'])*-1;
			}
            $data[$date_key]['grn_qty'] += mf($r['qty']);
            $data[$date_key]['grn_total_cost'] += mf($r['total_cost']);
            $total_rows++;
		}
		$con->sql_freeresult($q_grn);
		
		// get stock check adjustment
		//if(defined('TERMINAL')) print "\nGetting stock check adjustment...";
		$sql = "select date, sum(sc.qty) as qty from
stock_check sc
left join sku_items si using(sku_item_code)
where si.id=$sid and sc.branch_id=$bid and sc.date between ".ms($start_date)." and ".ms($end_date)." group by date";
        $q_sc = $con->sql_query($sql);
        while($r = $con->sql_fetchassoc($q_sc)){
            $date_key = date("Ym", strtotime($r['date']));
			$sb_date = date("Y-m-d", strtotime("-1 day", strtotime($r['date'])));
			$sb_year = date("Y", strtotime($sb_date));
			// find stock balance to get the adjustment figure
			$q_sb = $con->sql_query("select qty from stock_balance_b".$bid."_".$sb_year." where sku_item_id=$sid and ".ms($sb_date)." between from_date and to_date limit 1",false,false);
			$sb_qty = $con->sql_fetchfield(0);
			$con->sql_freeresult($q_sb);
			// stock check - stock balance = stock check adjustment
			$data[$date_key]['stock_check_adj_qty'] += mf($r['qty']-$sb_qty);
			$total_rows++;
		}
		$con->sql_freeresult($q_sc);

        // find POS
        //if(defined('TERMINAL')) print "\nGetting POS...";
		$sql = "select date,sum(amount) as amt, sum(cost) as cost, sum(qty) as qty from sku_items_sales_cache_b$bid
where sku_item_id=$sid and date between ".ms($start_date)." and ".ms($end_date)." group by date";
		$q_pos = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q_pos)){
            $date_key = date("Ym", strtotime($r['date']));
            $data[$date_key]['pos_qty'] += mf($r['qty']);
            $data[$date_key]['pos_total_cost'] += mf($r['cost']);
            $data[$date_key]['pos_total_amt'] += mf($r['amt']);
            $total_rows++;
		}
		$con->sql_freeresult($q_pos);
		
		// GRA
		//if(defined('TERMINAL')) print "\nGetting GRA...";
		$sql = "select year(return_timestamp) as y, month(return_timestamp) as m,sum(qty) as qty
from gra_items gi
left join gra on gi.gra_id=gra.id and gi.branch_id=gra.branch_id
where gi.sku_item_id=$sid and gi.branch_id=$bid and gra.status=0 and gra.returned=1 and return_timestamp between ".ms($start_date)." and ".ms($end_date." 23:59:59")." group by y,m";
        $q_gra = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_gra)){
            $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
            $data[$date_key]['gra_qty'] += mf($r['qty']);
            $total_rows++;
		}
		$con->sql_freeresult($q_gra);
		
		// ADJ
		//if(defined('TERMINAL')) print "\nGetting ADJ...";
		$sql = "select year(adj.adjustment_date) as y, month(adj.adjustment_date) as m, sum(qty) as qty
from adjustment_items ai
left join adjustment adj on adj.id=ai.adjustment_id and adj.branch_id=ai.branch_id
where ai.branch_id=$bid and ai.sku_item_id=$sid and adj.active=1 and adj.approved=1 and adj.status=1 and adj.adjustment_date between ".ms($start_date)." and ".ms($end_date)." group by y,m";
        $q_adj = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_adj)){
            $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
            $data[$date_key]['adj_qty'] += mf($r['qty']);
            $total_rows++;
		}
		$con->sql_freeresult($q_adj);
		
		// DO
		//if(defined('TERMINAL')) print "\nGetting DO...";
		$sql = "select year(do_date) as y, month(do_date) as m, sum(di.ctn *uom.fraction+di.pcs) as qty
from do_items di
left join do on do.id=di.do_id and do.branch_id=di.branch_id
left join uom on di.uom_id=uom.id
where di.sku_item_id=$sid and di.branch_id=$bid and do.approved=1 and do.active=1 and do.checkout=1 and do.status=1 and do.do_date between ".ms($start_date)." and ".ms($end_date)." group by y,m";
        $q_do = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_do)){
            $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
            $data[$date_key]['do_qty'] += mf($r['qty']);
            $total_rows++;
		}
		$con->sql_freeresult($q_do);
		
		// IBT Adj
		//if(defined('TERMINAL')) print "\nGetting IBT ADJ...";
		$sql = "select year(po.po_date) as y, month(po.po_date) as m, sum(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*grn_uom.fraction + gi.pcs, gi.acc_ctn*grn_uom.fraction+gi.acc_pcs)) as grn_qty,
sum(pi.qty*po_uom.fraction+pi.qty_loose) as po_qty
from po
left join po_items pi on pi.po_id=po.id and pi.branch_id=po.branch_id
left join grr_items gri on gri.type='PO' and gri.doc_no=po.po_no
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join grn on grn.grr_item_id=gri.id and grn.branch_id=gri.branch_id
left join grn_items gi on gi.branch_id=grn.branch_id and gi.grn_id=grn.id and  gi.sku_item_id=pi.sku_item_id
left join uom grn_uom on grn_uom.id=gi.uom_id
left join uom po_uom on po_uom.id=pi.order_uom_id
where po.branch_id=$bid and po.active=1 and po.approved=1 and po.po_date between ".ms($start_date)." and ".ms($end_date)." and grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and pi.sku_item_id=$sid and gi.sku_item_id=$sid group by y,m having po_qty-grn_qty<>0";
        $q_ibt_adj = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_ibt_adj)){
            $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
            $data[$date_key]['ibt_adj_qty'] += mf($r['po_qty']-$r['grn_qty']);
            $total_rows++;
		}
		$con->sql_freeresult($q_ibt_adj);
		
		// variances
		//if(defined('TERMINAL')) print "\nGetting Variances...";
		$sql = "select year(pi.date) as y,month(pi.date) as m,pi.sku_item_id,pi.qty,pi.price,pi.discount
from pos_items pi
left join pos on pos.branch_id=pi.branch_id and pos.id=pi.pos_id and pos.counter_id=pi.counter_id and pos.date=pi.date
where pos.branch_id=$bid and pos.cancel_status=0 and pi.sku_item_id=$sid and pos.date between ".ms($start_date)." and ".ms($end_date)." and pi.qty>0";

        $q_variances = $con->sql_query($sql);
        $hq_selling = round($sku['selling_price'],2);   // get HQ selling price to compare whether mark up or down
		while($r = $con->sql_fetchrow($q_variances)){
            $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
            if($r['discount']){ // got discount
                $data[$date_key]['variances_disc_qty'] += mf($r['qty']);
                $data[$date_key]['variances_disc_amt'] += mf($r['price']-$r['discount']);
			}else{
				$single_pcs_selling_price = round(mf(($r['price']-$r['discount'])/$r['qty']),2);
				if($single_pcs_selling_price>$hq_selling){
                    $data[$date_key]['variances_markup_qty'] += mf($r['qty']);
                	$data[$date_key]['variances_markup_amt'] += mf($r['price']-$r['discount'])-($hq_selling*$r['qty']);
				}elseif($single_pcs_selling_price<$hq_selling){
                    $data[$date_key]['variances_markdown_qty'] += mf($r['qty']);
                	$data[$date_key]['variances_markdown_amt'] += ($hq_selling*$r['qty'])-mf($r['price']-$r['discount']);
				}
			}
            $total_rows++;
		}
		$con->sql_freeresult($q_variances);
			
		// insert data into database
		foreach($data as $r){
		    $r['sku_item_id'] = $sid;
            $con->sql_query("replace into $tbl_name ".mysql_insert_by_field($r));
            $total_rows++;
		}
		
		if($ibt_data){
			foreach($ibt_data as $date_key=>$r){
				$r['year'] = substr($date_key, 0, 4);
				$r['month'] = substr($date_key, 4, 2);
				$r['date'] = $r['year'].'-'.$r['month'].'-1';
				$r['by_branch_id'] = $bid;
				
				$con->sql_query("replace into $ibt_cache_tbl ".mysql_insert_by_field($r));
            	$total_rows++;
			}
		}
	}
	$con->sql_freeresult($q_sku);
	
	$endtime = microtime(true);
	if(defined('TERMINAL')){
        //print "\nTotal $total_rows rows processed. ".($endtime-$starttime)." seconds used.\n";
	}
}
?>
