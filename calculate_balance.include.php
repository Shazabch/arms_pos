<?

function run_balance($usebranch, $branch_id , $tbl_name, $from_date, $to_date=false){
	global $con, $balance;
		
	$balance=array();
	$branch_filter='';
	
	if($branch_id!='') $branch_filter=" where id=$branch_id ";
	if(!$to_date) $to_date=$from_date;
	
	$con->sql_query("select id, code from branch $branch_filter order by id");
	$branches = $q2->sql_fetchrowset();
	$con->sql_freeresult();

	$q1=$con->sql_query("select * from sku_items order by id") or die(mysql_error());
	while($r1=$con->sql_fetchrow($q1)){						
		
		foreach($branches as $r2){
			
			calculate_bal($r1['id'],$r2['id'], $from_date, $to_date);
			
			if($usebranch) $update['branch_id']=$branch_id;				
			$update['sku_item_id']=$r1['id'];
			$update['date']=ms($to_date);
			$update['qty']=$balance[$r1['id']][$branch_id]['qty'];
			$update['grn_cost']=round($balance[$r1['id']][$branch_id]['grn_cost'],2);
			if(!$update['grn_cost']){
				$update['grn_cost']=round($r1['cost_price'],2);
			}
			$con->sql_query("replace into $tbl_name values (".join(",",$update).")");	
			if($con){	
				print "\n".date("[H:i:s m.d.y]")."\nDATE : $update[date]\nSKU ID :$update[sku_item_id] ($r1[sku_item_code])\nBRANCH : $r2[code]\nBALANCE : $update[qty]\n\n";				
			}			
		}
	}
}

function calculate_bal($sid,$branch_id,$from_date, $to_date){
	global $con, $balance;

	$balance[$sid][$branch_id]['qty']=0;
		
	//get the history balance
	$q3=$con->sql_query("select sku_items_cost_history.*, sku_items_cost_history.sku_item_id as sid ,
(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <='$from_date') as stock_date
from 
sku_items_cost_history
where branch_id=$branch_id and date <= '$from_date' and date > 0 and sku_item_id=$sid 
having stock_date = date order by null ");
	while($r3=$con->sql_fetchrow($q3)){
		if($r3['qty']){
			$balance[$sid][$branch_id]['qty']+=$r3['qty'];
			print "history balance in $r2[code]: $r3[qty]\n";		
		}
	}
	$con->sql_freeresult($q3);
	
	//GRN = get the rcvd qty, rcvd cost and grn qty
	$q4=$con->sql_query("select grn_items.sku_item_id as sid, 
sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, 

sum(grn_items.cost/rcv_uom.fraction*if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as total_rcv_cost,

(rcv_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <='$from_date')) as dont_count

from grn_items 
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id  
where grn.branch_id =$branch_id and rcv_date <='$to_date' and sku_item_id=$sid and grn.approved=1 and grn.status<2 and grn.active 
group by dont_count, sid order by null");
	while($r4=$con->sql_fetchrow($q4)){
		if($r4['qty']){
			$balance[$sid][$branch_id]['grn_cost']=$r4['total_rcv_cost']/$r4['qty'];
		}					
		if(!$r4['dont_count'] && $r4['qty']){			
			$balance[$sid][$branch_id]['qty']+=$r4['qty'];
			print "GRN balance in $r2[code]: $r4[qty]\n";
		}
	}
	$con->sql_freeresult($q4);
	
	//ADJ = get adj in and adj out
	$q5=$con->sql_query("select 
ai.sku_item_id as sid, 
sum(qty) as qty,

(adjustment_date < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = ai.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_date')) as dont_count

from adjustment_items ai
left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
where ai.branch_id =$branch_id and adjustment_date <= '$to_date' and sku_item_id=$sid and adj.approved and adj.status<2 
group by dont_count, sid order by null");
	while($r5=$con->sql_fetchrow($q5)){
		if(!$r5['dont_count'] && $r5['qty']){			
			$balance[$sid][$branch_id]['qty']+=$r5['qty'];
			print "ADJ balance in $r2[code]: $r5[qty]\n";
		}
	}
	$con->sql_freeresult($q5);

	//DO get do qty
	$q6=$con->sql_query("select 
do_items.sku_item_id as sid, 
sum(do_items.ctn *uom.fraction + do_items.pcs) as qty,  

(do_date < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = do_items.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_date')) as dont_count

from do_items 
left join uom on do_items.uom_id=uom.id
left join do on do_id = do.id and do_items.branch_id = do.branch_id
where do_items.branch_id=$branch_id and do_date <= '$to_date' and sku_item_id=$sid and do.approved and do.checkout and do.status<2 
group by dont_count, sid order by null");
	while($r6=$con->sql_fetchrow($q6)){
		if(!$r6['dont_count'] && $r6['qty']){
			$balance[$sid][$branch_id]['qty']+=$r6['qty'];
			print "DO balance in $r2[code]: $r6[qty]\n";
		}
	}
	$con->sql_freeresult($q6);
	
	//GRA get the gra qty.
	$q7=$con->sql_query("select 
gra_items.sku_item_id as sid, 
sum(qty) as qty, 

(return_timestamp < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = gra_items.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_date')) as dont_count

from gra_items 
left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
where gra.branch_id=$branch_id and return_timestamp <= '$to_date' and sku_item_id=$sid and gra.status=0 and gra.returned 
group by dont_count, sid order by null");
	while($r7=$con->sql_fetchrow($q7)){
		if(!$r7['dont_count'] && $r7['qty']){
			$balance[$sid][$branch_id]['qty']-=$r7['qty'];
			print "GRA balance in $r2[code]: $r7[qty]\n";
		}
	}
	$con->sql_freeresult($q7);
	
	$pos_tbl="sku_items_sales_cache_b".$branch_id;
	//POS qty
	$q8=$con->sql_query("select 
si.id as sid, 
sum(qty) as qty, 

(pos.date < (select max(date) from sku_items_cost_history sh where sh.sku_item_id =si.id and sh.branch_id=$branch_id and sh.date <='$from_date')) as dont_count 

from $pos_tbl pos 
left join sku_items si on si.id=pos.sku_item_id 
where pos.date<='$to_date' and si.id=$sid  
group by si.id, dont_count order by null");
	while($r8=$con->sql_fetchrow($q8)){
		if(!$r8['dont_count'] && $r8['qty']){
			$balance[$sid][$branch_id]['qty']-=$r8['qty'];
			print "POS balance in $r2[code]: $r8[qty]\n";		
		}
	}
	$con->sql_freeresult($q8);
}
?>
