<?php
/*
6/24/2011 6:31:42 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:40:11 PM Andy
- Change split() to use explode()

8/1/2011 5:37:08 PM Andy
- Add config to sku monitoring report.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/21/2020 5:46 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
if (!$config['enable_sku_monitoring']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
$maintenance->check(23);
//$con = new sql_db('hq.aneka.com.my','arms_slave','arms_slave','armshq');

class sku_monitoring extends Report
{
	var $bid_list = array();

	function run_report($bid,$bid_filter)
	{
	    global $con, $con_multi;
	    if(!in_array($bid,$this->bid_list))	$this->bid_list[] = $bid;

		$details = $this->details;
		$filter_sku = $this->filter_sku;
		$where_id = $this->where_id;
		$where_id2 = $this->where_id2;
		$sku_id_list = $this->sku_id_list;

		$table = $this->table;
		$label = $this->label;
		$total = $this->total;
		$balance = $this->balance;
		$grn_cost = $this->grn_cost;

		if($details){
		    // Cost History
		    $sql = "select sku_items_cost_history.*, sku_items_cost_history.sku_item_id as sid ,
(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid_filter and sh.date <'$this->date_from') as stock_date
from
sku_items_cost_history
where branch_id=$bid_filter and date <'$this->date_from' and date > 0 and $where_id
having stock_date=date order by null ";
			//print $sql;
		    $q_ch = $con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow($q_ch)){
				$balance[$r['sid']]['cost_history'][$bid] += $r['qty'];
				$balance[$r['sid']]['cost_history']['total'] += $r['qty'];
				$balance['total']['cost_history']['total'] += $r['qty'];

				$grn_cost[$r['sid']]['pcs'][$bid] = $r['grn_cost'];

				/*$balance[$r['sid']]['grn_cost_history'][$bid] += ($r['grn_cost']*$r['qty']);
				$balance[$r['sid']]['grn_cost_history']['total'] += ($r['grn_cost']*$r['qty']);

				$balance['total']['grn_cost_history'][$bid] += ($r['grn_cost']*$r['qty']);
				$balance['total']['grn_cost_history']['total'] += ($r['grn_cost']*$r['qty']);*/
			}
			$con_multi->sql_freeresult($q_ch);

			// get master cost price
			$sql = "select id,cost_price from sku_items where id in (".join(',',$sku_id_list).")";
			$q_mc = $con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow($q_mc)){
                if(!$grn_cost[$r['id']]['pcs'][$bid]){
                    $grn_cost[$r['id']]['pcs'][$bid] = $r['cost_price'];
				}
			}
			$con_multi->sql_freeresult($q_mc);

		    // GRN
			$sql = "select grn_items.sku_item_id as sid,
sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
sum(grn_items.cost/rcv_uom.fraction*if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as total_rcv_cost,
if(grr.rcv_date>='$this->date_from',0,1) as bal,

(rcv_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$bid_filter and sh.date <'$this->date_from')) as dont_count

from grn_items
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn.branch_id=$bid_filter and rcv_date <= '$this->date_to' and $where_id and grn.approved=1 and grn.status and grn.active
group by bal, dont_count, sid order by null";
			//print $sql;
            $con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow()){
                if($r['dont_count']==0&&$r['bal']==1){
                    $balance[$r['sid']]['grn'][$bid] += $r['qty'];
                    $balance[$r['sid']]['grn']['total'] += $r['qty'];
				}
			}
			$con_multi->sql_freeresult();

			//ADJ = get adj in and adj out
			$sql = "select
			year(adjustment_date) as year,month(adjustment_date) as month,si.sku_item_code,
		ai.sku_item_id as sid,
		sum(qty) as qty,
		if(adjustment_date>='$this->date_from',0,1) as bal,
		if(qty>=0,'p','n') as type,

		(adjustment_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = ai.sku_item_id and sh.branch_id=$bid_filter and sh.date <'$this->date_from')) as dont_count

		from adjustment_items ai
		left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
		left join sku_items si on si.id=ai.sku_item_id
		where ai.branch_id =$bid and adjustment_date <= '$this->date_to' and $where_id and adj.approved and adj.status<2
		group by bal, type,dont_count, sid order by null";
			//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow()){
				if(!$r['dont_count']){
				    if($r['bal']){
                        $balance[$r['sid']]['adj'][$bid] += $r['qty'];
				    	$balance[$r['sid']]['adj']['total'] += $r['qty'];
					}else{
                        $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

		                $table[$r['sku_item_code']]['adj'][$lbl][$bid] += $r['qty'];
		                $table[$r['sku_item_code']]['adj']['total'][$bid] += $r['qty'];

		                $total['total']['adj'][$lbl][$bid] += $r['qty'];
		                $total['total']['adj']['total'][$bid] += $r['qty'];

		                //////////////////////////////////////

		                $table[$r['sku_item_code']]['adj'][$lbl]['total'] += $r['qty'];
		                $table[$r['sku_item_code']]['adj']['total']['total'] += $r['qty'];

		                $total['total']['adj'][$lbl]['total'] += $r['qty'];
		                $total['total']['adj']['total']['total'] += $r['qty'];
					}
				}
			}
			$con_multi->sql_freeresult();

			//DO get do qty
			$sql = "select
			year(do_date) as year,month(do_date) as month,si.sku_item_code,
		do_items.sku_item_id as sid,
		sum(do_items.ctn *uom.fraction + do_items.pcs) as qty,
		if(do_date>='$this->date_from',0,1) as bal,

		(do_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = do_items.sku_item_id and sh.branch_id=$bid_filter and sh.date <'$this->date_from')) as dont_count

		from do_items
		left join uom on do_items.uom_id=uom.id
		left join do on do_id = do.id and do_items.branch_id = do.branch_id
		left join sku_items si on si.id=do_items.sku_item_id
		where do_items.branch_id=$bid_filter and do_date <= '$this->date_to' and $where_id and do.approved and do.checkout and do.status<2
		group by bal,dont_count, sid order by null";
		//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow()){
				if(!$r['dont_count']){
				    if($r['bal']){
                        $balance[$r['sid']]['do'][$bid] += $r['qty'];
						$balance[$r['sid']]['do']['total'] += $r['qty'];
					}else{
                        $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

		                $table[$r['sku_item_code']]['do'][$lbl][$bid] += $r['qty'];
		                $table[$r['sku_item_code']]['do']['total'][$bid] += $r['qty'];

		                $total['total']['do'][$lbl][$bid] += $r['qty'];
		                $total['total']['do']['total'][$bid] += $r['qty'];

		                //////////////////////////////////////

		                $table[$r['sku_item_code']]['do'][$lbl]['total'] += $r['qty'];
		                $table[$r['sku_item_code']]['do']['total']['total'] += $r['qty'];

		                $total['total']['do'][$lbl]['total'] += $r['qty'];
		                $total['total']['do']['total']['total'] += $r['qty'];
					}
				}
			}
			$con_multi->sql_freeresult();

			//GRA get the gra qty.
			$sql = "select year(return_timestamp) as year,month(return_timestamp) as month,si.sku_item_code,
		gra_items.sku_item_id as sid,
		sum(qty) as qty,
		if(return_timestamp>='$this->date_from',0,1) as bal,

		(date(return_timestamp) <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = gra_items.sku_item_id and sh.branch_id=$bid_filter and sh.date <'$this->date_from')) as dont_count

		from gra_items
		left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
		left join sku_items si on si.id=gra_items.sku_item_id
		where gra.branch_id=$bid_filter and return_timestamp <= '$this->date_to' and $where_id and gra.status=0 and gra.returned
		group by bal,dont_count, sid order by null";
			//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());

			while($r = $con_multi->sql_fetchrow()){
				if(!$r['dont_count']){
				    if($r['bal']){
                        $balance[$r['sid']]['gra'][$bid] += $r['qty'];
				    	$balance[$r['sid']]['gra']['total'] += $r['qty'];
					}else{
                        $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

		                $table[$r['sku_item_code']]['gra'][$lbl][$bid] += $r['qty'];
		                $table[$r['sku_item_code']]['gra']['total'][$bid] += $r['qty'];

		                $total['total']['gra'][$lbl][$bid] += $r['qty'];
		                $total['total']['gra']['total'][$bid] += $r['qty'];

		                //////////////////////////////////////

		                $table[$r['sku_item_code']]['gra'][$lbl]['total'] += $r['qty'];
		                $table[$r['sku_item_code']]['gra']['total']['total'] += $r['qty'];

		                $total['total']['gra'][$lbl]['total'] += $r['qty'];
		                $total['total']['gra']['total']['total'] += $r['qty'];
					}
				}
			}
			$con_multi->sql_freeresult();

	        // POS

	        $tbl="sku_items_sales_cache_b".$bid_filter;
	        $sql = "select
		si.id as sid,
		sum(qty) as qty,
		if(date>='$this->date_from',0,1) as bal,

		(date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id =si.id and sh.branch_id=$bid_filter and sh.date <'$this->date_from')) as dont_count

		from $tbl pos
		left join sku_items si on si.id=pos.sku_item_id
		where date < '$this->date_to' and $where_id2
		group by si.id, bal, dont_count order by null";
			//print $sql;
			$con_multi->sql_query($sql);

		    //print $con->sql_numrows();

			while($r = $con_multi->sql_fetchrow()){
				if(!$r['dont_count']&&$r['bal']){
				    $balance[$r['sid']]['pos'][$bid] += $r['qty'];
				    $balance[$r['sid']]['pos']['total'] += $r['qty'];
				}
			}
			$con_multi->sql_freeresult();

			// others
/*
			$sql = "select sku_item_code,gra_items.sku_item_id,sum(gra_items.cost) as cost,year(return_timestamp) as year ,month(return_timestamp) as month from gra_items left join gra on gra_items.id=gra.id and gra_items.branch_id=gra.branch_id left join sku_items on gra_items.sku_item_id=sku_items.id where gra_items.checkout=1 and gra_items.branch_id=$bid_filter and gra.return_timestamp between ".ms($this->date_from)." and ".ms($this->date_to)." and $filter_sku group by sku_item_id";
			$con->sql_query($sql) or die(mysql_error());
			//print $sql;
			while($r = $con->sql_fetchrow()){
                $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

                $table[$r['sku_item_code']]['gra'][$lbl][$bid] += $r['cost'];
                $table[$r['sku_item_code']]['gra']['total'][$bid] += $r['cost'];

                $total['total']['gra'][$lbl][$bid] += $r['cost'];
                $total['total']['gra']['total'][$bid] += $r['cost'];

                //////////////////////////////////////

                $table[$r['sku_item_code']]['gra'][$lbl]['total'] += $r['cost'];
                $table[$r['sku_item_code']]['gra']['total']['total'] += $r['cost'];

                $total['total']['gra'][$lbl]['total'] += $r['cost'];
                $total['total']['gra']['total']['total'] += $r['cost'];
			}*/
		}

		// QTY in
		$sql = "select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	sum(
	  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
	  *
	  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
	  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
	  )
	) as cost,
grn_items.sku_item_id,sku_items.sku_item_code,sku_items.description as sku_description,
		year(grr.rcv_date) as year,
		month(grr.rcv_date) as month,
		grr.rcv_date as dt
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join sku_items on grn_items.sku_item_id = sku_items.id
		where grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to)." and grn_items.branch_id=$bid_filter and grn.approved=1 and grn.status<2 and grn.active and $filter_sku group by sku_item_id,year,month";
		//print $sql."<br>";
		$con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows()>0){
		    foreach($con_multi->sql_fetchrowset() as $r){
		        $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

		        $table[$r['sku_item_code']]['description'] = $r['sku_description'];
		        $table[$r['sku_item_code']]['sku_item_id'] = $r['sku_item_id'];

		        $table[$r['sku_item_code']]['qty_in'][$lbl][$bid] += $r['qty'];
				$table[$r['sku_item_code']]['grn_cost'][$lbl][$bid] += $r['cost'];

		        $table[$r['sku_item_code']]['qty_in']['total'][$bid] += $r['qty'];
		        $table[$r['sku_item_code']]['grn_cost']['total'][$bid] += $r['cost'];

		        $total['total']['qty_in'][$lbl][$bid] += $r['qty'];
		        $total['total']['qty_in']['total'][$bid] += $r['qty'];

		        $total['total']['grn_cost'][$lbl][$bid] += $r['cost'];
		        $total['total']['grn_cost']['total'][$bid] += $r['cost'];

		        ///////////////////////////////////////

		        $table[$r['sku_item_code']]['qty_in'][$lbl]['total'] += $r['qty'];
				$table[$r['sku_item_code']]['grn_cost'][$lbl]['total'] += $r['cost'];

		        $table[$r['sku_item_code']]['qty_in']['total']['total'] += $r['qty'];
		        $table[$r['sku_item_code']]['grn_cost']['total']['total'] += $r['cost'];

		        $total['total']['qty_in'][$lbl]['total'] += $r['qty'];
		        $total['total']['qty_in']['total']['total'] += $r['qty'];

		        $total['total']['grn_cost'][$lbl]['total'] += $r['cost'];
		        $total['total']['grn_cost']['total']['total'] += $r['cost'];
			}
		}
		$con_multi->sql_freeresult();

		// All Content / POS
		$filter = array();

		$filter[] = 'pos.date between '.ms($this->date_from).' and '.ms($this->date_to);
		$filter[] = $filter_sku;

		$filter = join(" and ",$filter);

		$sql = "select date,year(date) as year,month(date) as month,sku_item_code,sku_item_id,sku_items.description,sum(qty) as qty,sum(amount) as amount,sum(cost) as cost
from sku_items_sales_cache_b$bid_filter pos
left join sku_items on sku_item_id = sku_items.id
where $filter group by year,month,sku_item_code";
		//die($sql);
        $con_multi->sql_query($sql) or die(mysql_error());

		//$con->sql_query($sql);

		while($r = $con_multi->sql_fetchrow()){
            $lbl = sprintf("%04d%02d", $r['year'],$r['month']);

            $table[$r['sku_item_code']]['description'] = $r['description'];
            $table[$r['sku_item_code']]['sku_item_id'] = $r['sku_item_id'];

            $table[$r['sku_item_code']]['qty_out'][$lbl][$bid] += $r['qty'];
            $table[$r['sku_item_code']]['selling'][$lbl][$bid] += $r['amount'];
            $table[$r['sku_item_code']]['cost'][$lbl][$bid] += $r['cost'];

            $table[$r['sku_item_code']]['profit_amount'][$lbl][$bid] = $table[$r['sku_item_code']]['selling'][$lbl][$bid]-$table[$r['sku_item_code']]['cost'][$lbl][$bid];

			if($table[$r['sku_item_code']]['selling'][$lbl][$bid]!=0){
                $table[$r['sku_item_code']]['profit_per'][$lbl][$bid] = ($table[$r['sku_item_code']]['profit_amount'][$lbl][$bid]/$table[$r['sku_item_code']]['selling'][$lbl][$bid])*100;
			}

            $table[$r['sku_item_code']]['qty_out']['total'][$bid] += $r['qty'];
            $table[$r['sku_item_code']]['selling']['total'][$bid] += $r['amount'];
            $table[$r['sku_item_code']]['cost']['total'][$bid] += $r['cost'];
            $table[$r['sku_item_code']]['profit_amount']['total'][$bid] = $table[$r['sku_item_code']]['selling']['total'][$bid]-$table[$r['sku_item_code']]['cost']['total'][$bid];

			if($table[$r['sku_item_code']]['selling']['total'][$bid]!=0){
                $table[$r['sku_item_code']]['profit_per']['total'][$bid] = ($table[$r['sku_item_code']]['profit_amount']['total'][$bid]/$table[$r['sku_item_code']]['selling']['total'][$bid])*100;
			}

            $total['total']['qty_out'][$lbl][$bid] += $r['qty'];
            $total['total']['selling'][$lbl][$bid] += $r['amount'];
            $total['total']['cost'][$lbl][$bid] += $r['cost'];
            $total['total']['profit_amount'][$lbl][$bid] = $total['total']['selling'][$lbl][$bid]-$total['total']['cost'][$lbl][$bid];
            $total['total']['profit_per'][$lbl][$bid] = ($total['total']['profit_amount'][$lbl][$bid]/$total['total']['selling'][$lbl][$bid])*100;

            $total['total']['qty_out']['total'][$bid] += $r['qty'];
            $total['total']['selling']['total'][$bid] += $r['amount'];
            $total['total']['cost']['total'][$bid] += $r['cost'];
            $total['total']['profit_amount']['total'][$bid] = $total['total']['selling']['total'][$bid]-$total['total']['cost']['total'][$bid];
            $total['total']['profit_per']['total'][$bid] = ($total['total']['profit_amount']['total'][$bid]/$total['total']['selling']['total'][$bid])*100;

            ///////////////////////////////////////////////

            $table[$r['sku_item_code']]['qty_out'][$lbl]['total'] += $r['qty'];
            $table[$r['sku_item_code']]['selling'][$lbl]['total'] += $r['amount'];
            $table[$r['sku_item_code']]['cost'][$lbl]['total'] += $r['cost'];

            $table[$r['sku_item_code']]['profit_amount'][$lbl]['total'] = $table[$r['sku_item_code']]['selling'][$lbl]['total']-$table[$r['sku_item_code']]['cost'][$lbl]['total'];

			if($table[$r['sku_item_code']]['selling'][$lbl]['total']!=0){
                $table[$r['sku_item_code']]['profit_per'][$lbl]['total'] = ($table[$r['sku_item_code']]['profit_amount'][$lbl]['total']/$table[$r['sku_item_code']]['selling'][$lbl]['total'])*100;
			}

            $table[$r['sku_item_code']]['qty_out']['total']['total'] += $r['qty'];
            $table[$r['sku_item_code']]['selling']['total']['total'] += $r['amount'];
            $table[$r['sku_item_code']]['cost']['total']['total'] += $r['cost'];
            $table[$r['sku_item_code']]['profit_amount']['total']['total'] = $table[$r['sku_item_code']]['selling']['total']['total']-$table[$r['sku_item_code']]['cost']['total']['total'];

			if($table[$r['sku_item_code']]['selling']['total']['total']!=0){
                $table[$r['sku_item_code']]['profit_per']['total']['total'] = ($table[$r['sku_item_code']]['profit_amount']['total']['total']/$table[$r['sku_item_code']]['selling']['total']['total'])*100;
			}

            $total['total']['qty_out'][$lbl]['total'] += $r['qty'];
            $total['total']['selling'][$lbl]['total'] += $r['amount'];
            $total['total']['cost'][$lbl]['total'] += $r['cost'];
            $total['total']['profit_amount'][$lbl]['total'] = $total['total']['selling'][$lbl]['total']-$total['total']['cost'][$lbl]['total'];
            $total['total']['profit_per'][$lbl]['total'] = ($total['total']['profit_amount'][$lbl]['total']/$total['total']['selling'][$lbl]['total'])*100;

            $total['total']['qty_out']['total']['total'] += $r['qty'];
            $total['total']['selling']['total']['total'] += $r['amount'];
            $total['total']['cost']['total']['total'] += $r['cost'];
            $total['total']['profit_amount']['total']['total'] = $total['total']['selling']['total']['total']-$total['total']['cost']['total']['total'];
            $total['total']['profit_per']['total']['total'] = ($total['total']['profit_amount']['total']['total']/$total['total']['selling']['total']['total'])*100;
		}

		// Adjustment

		/*$filter = array();

		$filter[] = 'adjustment_date between '.ms($this->date_from).' and '.ms($this->date_to);
		$filter[] = 'ad.branch_id='.mi($bid);
		$filter[] = 'adjustment.approved=1';
		$filter[] = "ad.sku_item_id in (".join(" , ", $sku_id_list).")";

		$filter = join(" and ",$filter);

		$sql = "select sku_item_code,sku_items.description as sname,ad.*,year(adjustment_date) as year,month(adjustment_date) as month,ad.sku_item_id from adjustment_items ad left join adjustment on ad.adjustment_id=adjustment.id and ad.branch_id=adjustment.branch_id left join sku_items on ad.sku_item_id=sku_items.id where $filter";
		$con->sql_query($sql) or die(mysql_error());

		while($r = $con->sql_fetchrow()){
            $table[$r['sku_item_code']]['description'] = $r['sname'];
            $table[$r['sku_item_code']]['sku_item_id'] = $r['sku_item_id'];

            $table[$r['sku_item_code']]['adj'][$lbl][$bid] += $r['qty'];
            $table[$r['sku_item_code']]['adj']['total'][$bid] += $r['qty'];

            $total['total']['adj'][$lbl][$bid] += $r['qty'];
            $total['total']['adj']['total'][$bid] += $r['qty'];

            ///////////////////////////////////

            $table[$r['sku_item_code']]['adj'][$lbl]['total'] += $r['qty'];
            $table[$r['sku_item_code']]['adj']['total']['total'] += $r['qty'];

            $total['total']['adj'][$lbl]['total'] += $r['qty'];
            $total['total']['adj']['total']['total'] += $r['qty'];
		}*/

		// DO
		/*$filter = array();

		$filter[] = 'do_date between '.ms($this->date_from).' and '.ms($this->date_to);
		$filter[] = 'do_items.branch_id='.mi($bid);
		$filter[] = 'do.checkout=1';
		$filter[] = 'do.approved=1';
		$filter[] = "do_items.sku_item_id in (".join(" , ", $sku_id_list).")";

		$filter = join(" and ",$filter);

		$sql = "select sku_item_code,sku_items.description as sname,do_items.*,year(do_date) as year,month(do_date) as month,do_items.sku_item_id from do_items left join do on do_items.do_id=do.id and do_items.branch_id=do.branch_id left join sku_items on do_items.sku_item_id=sku_items.id where $filter";

		$con->sql_query($sql) or die(mysql_error());

		while($r = $con->sql_fetchrow()){
            $table[$r['sku_item_code']]['description'] = $r['sname'];
            $table[$r['sku_item_code']]['sku_item_id'] = $r['sku_item_id'];

            $table[$r['sku_item_code']]['do'][$lbl][$bid] += $r['qty'];
            $table[$r['sku_item_code']]['do']['total'][$bid] += $r['qty'];

            $total['total']['do'][$lbl][$bid] += $r['qty'];
            $total['total']['do']['total'][$bid] += $r['qty'];

            //////////////////////////////

            $table[$r['sku_item_code']]['do'][$lbl]['total'] += $r['qty'];
            $table[$r['sku_item_code']]['do']['total']['total'] += $r['qty'];

            $total['total']['do'][$lbl]['total'] += $r['qty'];
            $total['total']['do']['total']['total'] += $r['qty'];
		}*/

		$con_multi->sql_freeresult();
		//print_r($table);

		$this->table = $table;
		$this->label = $label;
		$this->total = $total;
		$this->balance = $balance;
		$this->grn_cost = $grn_cost;
	}

	function generate_report()
	{
		global $con_multi, $smarty;

		$branch_group = $this->branch_group;

/*		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = split("[,]",$_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $bid=>$b){
                $this->run_report($bid,$bid);
			}
			$branch_name = $branch_group['header'][$bg_id]['code'];
			$this->is_bg_id = $bg_id;
			$smarty->assign('is_bg_id',$bg_id);
		}else{
*/            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $this->run_report($bid,$bid);
	            $branch_name = BRANCH_CODE;
			}else{

				$b_id = $_REQUEST['branch_id'];
				$bgp_id = $_REQUEST['group_branch_id'];
				if ($bgp_id)
				{
					foreach ($bgp_id as $bb2)
					{
						list($dummy,$bid2) = explode(",",$bb2);
						$bb3[] = $bid2;
					}

					$bgp_id = $bb3;
				}
				$c_bid = count($b_id) + count($bgp_id);

				if($c_bid>1){
	                //$branch_name = "All";

	                $q_b = $con_multi->sql_query("select * from branch where id not in (select branch_id from branch_group_items) and id in (".implode(",",$b_id).") order by sequence,code");
	                while($r = $con_multi->sql_fetchrow($q_b)){
	                	$branch_name[] = $r['code'];
			            $this->run_report($r['id'],$r['id']);
					}
					$con_multi->sql_freeresult($q_b);
				/*	print "<pre>";
					print_r($branch_group);
					print "</pre>";
				*/
					if($branch_group['header']){
						if (is_array($bgp_id))
						{
							foreach($branch_group['header'] as $bg_id=>$bg){
								if (!in_array($bg_id,$bgp_id)) continue;
								$branch_name[] = $bg['code'];
							    foreach($branch_group['items'][$bg_id] as $bid=>$b){
	                                $this->run_report($bg_id+10000,$bid);
								}
							}
						}
					}
					$this->is_all_branch = true;
				}else{
					//print_r($branch_group);
					if (count($b_id)>0)
					{
		            $this->run_report($b_id[0],$b_id[0]);
					$branch_name = get_branch_code($b_id[0]);
					}
		            if (count($bgp_id)>0)
		            {
						foreach($branch_group['header'] as $bg_id=>$bg){
							if (!in_array($bg_id,$bgp_id)) continue;
							$branch_name[] = $bg['code'];
						    foreach($branch_group['items'][$bg_id] as $bid=>$b){
                                $this->run_report($bg_id+10000,$bid);
							}
						}
					}
				}
			}
			if (is_array($branch_name)) $branch_name = implode(",",$branch_name);

//		}

		if($this->details){
            $this->generate_details_info();
		}
		krsort($this->label);

		if($this->details){
			$this->generate_enhance_info();
		}

		$table = $this->table;
		$label = $this->label;
		$total = $this->total;
		$balance = $this->balance;

		$b_id = $_REQUEST['branch_id'];
		$bgp_id = $_REQUEST['group_branch_id'];
		if (is_array($bgp_id))
		{
			foreach ($bgp_id as $b)
			{
				list($dummy,$b2) = explode(",",$b);
				$bg_id2[] = $b2;
			}
		}
		$bgp_id = $bg_id2;

		// build showing branch
		$show_branch = array();
		if($this->is_bg_id>0){  // if selected branch group
		    // jusr display the branch in this group
			foreach($branch_group['items'][$this->is_bg_id] as $bid=>$b){
                $show_branch[$bid] = $b;
			}
		}elseif($this->is_all_branch){  // is selected all branch
			if($branch_group['header']){
				if (is_array($bgp_id))
				{
					foreach($branch_group['header'] as $bg_id=>$bg){
						if (!in_array($bg_id,$bgp_id)) continue;

	                    $show_branch[$bg_id+10000] = $bg;
					}
				}
			}
			$q_b = $con_multi->sql_query("select * from branch where id not in (select branch_id from branch_group_items) and id in (".implode(",",$b_id).") order by sequence,code");
	        while($r = $con_multi->sql_fetchrow($q_b)){
	            $show_branch[$r['id']] = $r;
			}
			$con_multi->sql_freeresult($q_b);
		}

		//print_r($this->table);
		//count($branch_group['header'])+count($smarty->get_template_vars('branches')) == count($show_branch)
		if (count($show_branch)>1)
		{
			$smarty->assign("show_all",1);
		}

    $report_title = "Branch: ".$branch_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SKU Group: ".$this->sku_group."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: from ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
    $smarty->assign('report_title',$report_title);

		$smarty->assign('show_branch',$show_branch);

		$smarty->assign('date_length',"from $this->date_from to $this->date_to");
		$smarty->assign('date_to',$this->date_to);
		$smarty->assign('label',$label);
		$smarty->assign('table',$table);
		$smarty->assign('balance',$balance);
		$smarty->assign('total',$total);
		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('grn_cost',$this->grn_cost);
	}

	function load_branch_group($id=0){
		global $con, $smarty, $con_multi;
		parent::load_branch_group($id);
		$con_multi->sql_query("select * from branch where active=1 and id not in (select branch_id from branch_group_items) order by sequence,code");
		while($r = $con_multi->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign("branches", $branches);
	}

	function generate_details_info(){
	    $table = $this->table;
		$label = $this->label;
		$total = $this->total;
		$balance = $this->balance;

		ksort($label);
		for($i=0; $i<=count($this->bid_list); $i++){
			if($i==count($this->bid_list)){
				$bid = 'total';
			}else{
				$bid = $this->bid_list[$i];
			}
			//print $bid."<br>";
			foreach($table as $sku_item_code=>$r){
			    $first_month = true;
			    $sku_item_id = $r['sku_item_id'];

				foreach($label as $lbl=>$dummy){
				    if($first_month){
	                    $table[$sku_item_code]['balance'][$lbl][$bid] = $balance[$sku_item_id]['cost_history'][$bid]+$balance[$sku_item_id]['grn'][$bid]+$balance[$sku_item_id]['adj'][$bid]-$balance[$sku_item_id]['do'][$bid]-$balance[$sku_item_id]['gra'][$bid]-$balance[$sku_item_id]['pos'][$bid];

	                    $table[$sku_item_code]['balance']['total'][$bid] += $table[$sku_item_code]['balance'][$lbl][$bid];

	                    $total['total']['balance'][$lbl][$bid] += $table[$sku_item_code]['balance'][$lbl][$bid];
	                    //$total['total']['balance']['total'] += $table[$sku_item_code]['balance'][$lbl];

	                    $last_month_balance = $table[$sku_item_code]['balance'][$lbl][$bid];

	                    $next_month_balance = $last_month_balance + $table[$sku_item_code]['qty_in'][$lbl][$bid] - $table[$sku_item_code]['qty_out'][$lbl][$bid] - $table[$sku_item_code]['gra'][$lbl][$bid] + $table[$sku_item_code]['adj'][$lbl][$bid] - $table[$sku_item_code]['do'][$lbl][$bid];
	                    $first_month = false;
					}elseif(!$first_month&&!$table[$sku_item_code]['balance'][$lbl][$bid]){
	                    $table[$sku_item_code]['balance'][$lbl][$bid] = $next_month_balance;

	                    $table[$sku_item_code]['balance']['total'][$bid] += $table[$sku_item_code]['balance'][$lbl][$bid];

	                    $total['total']['balance'][$lbl][$bid] += $table[$sku_item_code]['balance'][$lbl][$bid];

	                    $last_month_balance = $table[$sku_item_code]['balance'][$lbl][$bid];
	                    $next_month_balance = $last_month_balance + $table[$sku_item_code]['qty_in'][$lbl][$bid] - $table[$sku_item_code]['qty_out'][$lbl][$bid] - $table[$sku_item_code]['gra'][$lbl][$bid] + $table[$sku_item_code]['adj'][$lbl][$bid] - $table[$sku_item_code]['do'][$lbl][$bid];
	                    //if($bid>10000) print $bid.$lbl." = ".$last_month_balance."-".$table[$sku_item_code]['qty_out'][$lbl][$bid]."=".$next_month_balance."<br>";
					}

	                // Quantity Sales Percent
	                if(($table[$sku_item_code]['qty_in'][$lbl][$bid]+$table[$sku_item_code]['balance'][$lbl][$bid])!=0){
	                    $table[$sku_item_code]['qty_sales_per'][$lbl][$bid] = ($table[$sku_item_code]['qty_out'][$lbl][$bid]/($table[$sku_item_code]['qty_in'][$lbl][$bid]+$table[$sku_item_code]['balance'][$lbl][$bid]))*100;
					}

	                if(($table[$sku_item_code]['qty_in']['total'][$bid]+$table[$sku_item_code]['balance']['total'][$bid])!=0){
	                    $table[$sku_item_code]['qty_sales_per']['total'][$bid] = ($table[$sku_item_code]['qty_out']['total'][$bid]/($table[$sku_item_code]['qty_in']['total'][$bid]+$table[$sku_item_code]['balance']['total'][$bid]))*100;

	                }

	                if(($total['total']['qty_in'][$lbl][$bid]+$total['total']['balance'][$lbl][$bid])!=0){
	                    $total['total']['qty_sales_per'][$lbl][$bid] = ($total['total']['qty_out'][$lbl][$bid]/($total['total']['qty_in'][$lbl][$bid]+$total['total']['balance'][$lbl][$bid]))*100;
	                }

	                if(($total['total']['qty_in']['total'][$bid]+$total['total']['balance']['total'][$bid])!=0){
	                    $total['total']['qty_sales_per']['total'][$bid] = ($total['total']['qty_out']['total'][$bid]/($total['total']['qty_in']['total'][$bid]+$total['total']['balance']['total'][$bid]))*100;
	                }

	                // Quantity GRA Percent

	                if(($table[$sku_item_code]['qty_in'][$lbl][$bid]+$table[$sku_item_code]['balance'][$lbl][$bid])!=0){
	                    $table[$sku_item_code]['gra_per'][$lbl][$bid] = ($table[$sku_item_code]['gra'][$lbl][$bid]/($table[$sku_item_code]['qty_in'][$lbl][$bid]+$table[$sku_item_code]['balance'][$lbl][$bid]))*100;
					}

	                if(($table[$sku_item_code]['qty_in']['total'][$bid]+$table[$sku_item_code]['balance']['total'][$bid])!=0){
	                    $table[$sku_item_code]['gra_per']['total'][$bid] = ($table[$sku_item_code]['gra']['total'][$bid]/($table[$sku_item_code]['qty_in']['total'][$bid]+$table[$sku_item_code]['balance']['total'][$bid]))*100;

	                }

	                if(($total['total']['qty_in'][$lbl][$bid]+$total['total']['balance'][$lbl][$bid])!=0){
	                    $total['total']['gra_per'][$lbl][$bid] = ($total['total']['gra'][$lbl][$bid]/($total['total']['qty_in'][$lbl][$bid]+$total['total']['balance'][$lbl][$bid]))*100;
	                }

	                if(($total['total']['qty_in']['total'][$bid]+$total['total']['balance']['total'][$bid])!=0){
	                    $total['total']['gra_per']['total'][$bid] = ($total['total']['gra']['total'][$bid]/($total['total']['qty_in']['total'][$bid]+$total['total']['balance']['total'][$bid]))*100;
	                }
				}
				//print "b ".$bid." last = ".$next_month_balance."<br>";
				$table[$sku_item_code]['balance']['last'][$bid] = $next_month_balance;
				$total['total']['balance']['last'][$bid] += $next_month_balance;
			}
		}

		$this->table = $table;
		$this->label = $label;
		$this->total = $total;
		$this->balance = $balance;

		//print_r($table);

	}

	function getSKU_item_id_list(){
		global $con,$smarty,$con_multi;
		$table = $this->table;

		$sku_group = $_REQUEST['sku_group'];

		$temp = explode('|' , $sku_group);

		$sku_group_id = $temp[0];
		$branch_id = $temp[1];
		$user_id = $temp[2];

		$con_multi->sql_query("select sku_items.id,sku_items.sku_item_code,description from sku_group_item s left join sku_items using(sku_item_code) where sku_group_id=$sku_group_id and branch_id=$branch_id and user_id=$user_id") or die(mysql_error());

		while($r = $con_multi->sql_fetchrow()){
			$sku_id_list[$r['id']] = $r['id'];
			$table[$r['sku_item_code']] = array();
			$table[$r['sku_item_code']]['description'] = $r['description'];
			$table[$r['sku_item_code']]['sku_item_id'] = $r['id'];
		}
		$con_multi->sql_freeresult();

		$smarty->assign('sku_group_id',$sku_group_id);
		$smarty->assign('branch_id',$branch_id);
		$smarty->assign('user_id',$user_id);

		$con_multi->sql_query("select description from sku_group where sku_group_id=$sku_group_id and branch_id=$branch_id and user_id=$user_id") or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();

		$smarty->assign('sku_group_name',$temp['description']);
		$this->sku_group = $temp['description'];
		$this->table = $table;

		return $sku_id_list;
	}

	function process_form()
	{
	    global $con,$smarty,$sessioninfo;
		// do my own form process

		// call parent
		parent::process_form();

		$this->details = $_REQUEST['details'];

		$this->date_to = $_REQUEST['date_to'];
		$this->date_from = $_REQUEST['date_from'];

		$sku_id_list = $this->getSKU_item_id_list();
		//print_r($sku_id_list);

		$filter_sku = "sku_items.id in (".join(" , ", $sku_id_list).")";
		$where_id = "sku_item_id in (".join(" , " , ($sku_id_list)).")";
		$where_id2 = "si.id in (" . join(",", $sku_id_list) . ")";

		$this->sku_id_list = $sku_id_list;
		$this->filter_sku = $filter_sku;
		$this->where_id = $where_id;
		$this->where_id2 = $where_id2;

		$label = $this->generate_months($this->date_from, $this->date_to, 'Ym', 'Y/m',true);

		$this->label = $label;
	}

	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-3 month"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}

	private function generate_enhance_info(){
		$label = $this->label;
		$table = $this->table;
		$total = $this->total;
		$balance = $this->balance;
		$grn_cost = $this->grn_cost;

		ksort($label);
		foreach($label as $key=>$dummy){
			$lbl = $key;    // get the first key only
			break;
		}
		//print($lbl);die();

		//print_r($table);

		for($i=0; $i<=count($this->bid_list); $i++){
		    if($i==count($this->bid_list)){
				$bid = 'total';
			}else{
				$bid = $this->bid_list[$i];
			}

		    foreach($table as $sku_item_code=>&$items){
		        $sku_item_id = $items['sku_item_id'];

		        if($items['qty_out']['total'][$bid]){
                    $openning_cost = ($items['cost']['total'][$bid]/$items['qty_out']['total'][$bid])*$items['balance'][$lbl][$bid];
                    //print_r($items);die();
                    //print $items['cost']['total'][$bid]."/".$items['qty_out']['total'][$bid]."*".$items['balance'][$lbl][$bid]."<br>";
                    //$openning_cost = $items['cost']['total'][$bid]."/".$items['qty_out']['total'][$bid]."*".$items['balance'][$lbl][$bid];
				}
		        else    $openning_cost = 0;

		        if($items['qty_out']['total'][$bid]){
                    $balance_stock_amt = (($items['cost']['total'][$bid]/$items['qty_out']['total'][$bid])*$items['qty_in']['total'][$bid]) + $openning_cost;
                    //$balance_stock_amt = $items['cost']['total'][$bid]."/".$items['qty_out']['total'][$bid]."*".$items['qty_in']['total'][$bid]."+".$openning_cost;
                    //print $items['cost']['total'][$bid]."/".$items['qty_out']['total'][$bid]."*".$items['qty_in'][$lbl][$bid]."+".$openning_cost."<br>";
				}

		        else    $balance_stock_amt = $openning_cost;

		        // balance_stock_amt

		        /*if($bid=='total'){
                    $grn_cost_history = $grn_cost[$sku_item_id]['total']['total'];
				}else{
                    $grn_cost_history = $items['balance'][$lbl][$bid] * $grn_cost[$sku_item_id]['pcs'][$bid];
				}*/
		        //$balance_stock_amt = $grn_cost_history + $items['grn_cost'][$lbl][$bid];
				//print "<br>".$grn_cost_history."+".$items['grn_cost'][$lbl][$bid]."=".$balance_stock_amt;

				// sales amt
				$sales_amt = $items['selling']['total'][$bid];

				// cost on qty sold
				$cost_on_qty_sold = $items['cost']['total'][$bid];

				// profit amt
				$profit_amt = $sales_amt - $cost_on_qty_sold;

				// profit percent
				if($sales_amt!=0)	$profit_per = ($profit_amt/$sales_amt)*100;
				else $profit_per = 0;

				// closing stock amt
				$closing_stock_amt = $balance_stock_amt - $cost_on_qty_sold;

				// sold on amout percent
				if($balance_stock_amt!=0)    $sold_on_amt_per = ($cost_on_qty_sold / $balance_stock_amt)*100;
				else    $sold_on_amt_per = 0;

				// profit and sales weighted average
				$profit_sales_avg = ($profit_per * $sold_on_amt_per)/100;

				// Sales - purchase
				$sales_minus_perchase = $sales_amt - $balance_stock_amt;

				// save all
				$items['openning_bal'][$bid] = $openning_cost;
				$items['balance_stock_amt'][$bid] = $balance_stock_amt;
				$items['sales_amt'][$bid] += $sales_amt;
				$items['cost_on_qty_sold'][$bid] += $cost_on_qty_sold;
				$items['profit_amt'][$bid] += $profit_amt;
				$items['profit_per2'][$bid] += $profit_per;
				$items['closing_stock_amt'][$bid] += $closing_stock_amt;
				$items['sold_on_amt_per'][$bid] += $sold_on_amt_per;
				$items['profit_sales_avg'][$bid] += $profit_sales_avg;
				$items['sales_minus_perchase'][$bid] += $sales_minus_perchase;

				/*
				if($bid!='total'){
                    $grn_cost[$sku_item_id]['total']['total'] += $grn_cost_history;
					$grn_cost['total']['total']['total'] += $grn_cost_history;

					$grn_cost[$sku_item_id]['total'][$bid] += $grn_cost_history;
					$grn_cost['total']['total'][$bid] += $grn_cost_history;
				}
				*/
			}
		}
		//print_r($table);

		// for last row - total
		for($i=0; $i<=count($this->bid_list); $i++){
		    if($i==count($this->bid_list)){
				$bid = 'total';
			}else{
				$bid = $this->bid_list[$i];
			}
			$temp_items = $total['total'];

			if($total['total']['qty_out']['total'][$bid]){
                $openning_cost = ($total['total']['cost']['total'][$bid]/$total['total']['qty_out']['total'][$bid])*$total['total']['balance'][$lbl][$bid];
                //$openning_cost = $total['total']['cost']['total'][$bid]."/".$total['total']['qty_out']['total'][$bid]."*".$total['total']['balance'][$lbl][$bid];
			}
	        else    $openning_cost = 0;

	        if($temp_items['qty_out']['total'][$bid])
	        	$balance_stock_amt = ($total['total']['cost']['total'][$bid]/$total['total']['qty_out']['total'][$bid])*$total['total']['qty_in']['total'][$bid] + $openning_cost;
	        	//$balance_stock_amt = $total['total']['cost']['total'][$bid]."/".$total['total']['qty_out']['total'][$bid]."*".$total['total']['qty_in']['total'][$bid]."+opp";
	        else    $balance_stock_amt = $openning_cost;

			//$grn_cost_history = $grn_cost['total']['total'][$bid];
			// balance_stock_amt

		    //$balance_stock_amt = $grn_cost_history + $items['grn_cost'][$lbl][$bid];
            //print "<br>".$grn_cost_history."+".$items['grn_cost'][$lbl][$bid]."=".$balance_stock_amt;

			// sales amt
			$sales_amt = $temp_items['selling']['total'][$bid];

			// cost on qty sold
			$cost_on_qty_sold = $temp_items['cost']['total'][$bid];

			// profit amt
			$profit_amt = $sales_amt - $cost_on_qty_sold;

			// profit percent
			if($sales_amt!=0)	$profit_per = ($profit_amt/$sales_amt)*100;
			else $profit_per = 0;

			// closing stock amt
			$closing_stock_amt = $balance_stock_amt - $cost_on_qty_sold;

			// sold on amout percent
			if($balance_stock_amt!=0)    $sold_on_amt_per = ($cost_on_qty_sold / $balance_stock_amt)*100;
			else    $sold_on_amt_per = 0;

			// profit and sales weighted average
			$profit_sales_avg = ($profit_per * $sold_on_amt_per)/100;

			// Sales - purchase
			$sales_minus_perchase = $sales_amt - $balance_stock_amt;

            $total['total']['openning_bal'][$bid] = $openning_cost;
			$total['total']['balance_stock_amt'][$bid] = $balance_stock_amt;
			$total['total']['sales_amt'][$bid] += $sales_amt;
			$total['total']['cost_on_qty_sold'][$bid] += $cost_on_qty_sold;
			$total['total']['profit_amt'][$bid] += $profit_amt;
			$total['total']['profit_per2'][$bid] += $profit_per;
			$total['total']['closing_stock_amt'][$bid] += $closing_stock_amt;
			$total['total']['sold_on_amt_per'][$bid] += $sold_on_amt_per;
			$total['total']['profit_sales_avg'][$bid] += $profit_sales_avg;
			$total['total']['sales_minus_perchase'][$bid] += $sales_minus_perchase;
		}
		//print "<br>";
		//print_r($total);
		$this->table = $table;
		$this->total = $total;
		$this->grn_cost = $grn_cost;
	}
}

$report = new sku_monitoring('SKU Monitoring');

?>
