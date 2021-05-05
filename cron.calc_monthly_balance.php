<?
define("TERMINAL",1);
include("include/common.php");
include("calculate_balance.include.php");
set_time_limit(0);
ini_set("memory_limit", "128M");
ob_end_clean();

$mon=1;
$arg = $_SERVER['argv'];
$branch = BRANCH_CODE;
$allbranch=false;
array_shift($arg);

while($a = array_shift($arg)){
	switch ($a){
		case '-allbranch':
			$allbranch = true;
			break;
		case '-branch':
			$branch = array_shift($arg);
			break;
		case '-month':
			$mon = array_shift($arg);
			break;	
		default:
			die("Unknown option: $a\n");
	}
}

if($allbranch){
	$branch_id='';
}
else{
	$q1=$con->sql_query("select id from branch where code=".ms($branch));
	$r1=$con->sql_fetchrow($q1);
	$branch_id=$r1['id'];
}
run_latest();

function run_latest(){
	//global $con, $balance, $mon, $branch_id;
	global $con, $mon, $branch_id;
		
	//$balance=array();
	//$set_limit="limit 10";
	
	//if($branch_id!='') $branch_filter=" where id=$branch_id ";
	
	for($i=0;$i<=$mon;$i++){
			
		$from_Date=date('Y-m-1', strtotime("-".($i+1)." month"));		
		$to_Date=date('Y-m-1', strtotime("-".$i." month"));
		
		run_balance(true, $branch_id, "sku_items_monthly_balance",$from_Date, $to_Date);

		/*
		$q1=$con->sql_query("select * from sku_items order by null $set_limit") or die(mysql_error());
		
		while($r1=$con->sql_fetchrow($q1)){
							
			$q2=$con->sql_query("select id, code from branch $branch_filter order by id");
			while($r2=$con->sql_fetchrow($q2)){
				$branch_id=$r2['id'];
				process($r1['id'],$branch_id,$from_Date,$to_Date);	
				
				if($balance[$r1['id']][$branch_id]['qty']){
					$update['branch_id']=$branch_id;
					$update['sku_item_id']=$r1['id'];
					$update['date']=ms($to_Date);
					$update['qty']=$balance[$r1['id']][$branch_id]['qty'];
					$update['grn_cost']=round($r1['cost_price'],2);
					
					$con->sql_query("replace into sku_items_monthly_balance values (".join(",",$update).")");	
					if($con){	
						print "\n".date("[H:i:s m.d.y]")."\nDATE : $to_Date\nSKU ID :$update[sku_item_id] ($r1[sku_item_code])\nBRANCH : $r2[code]\nBALANCE : $update[qty]\n\n";				
					}			
				}
								
			}
		}
		*/
	}
}

function process($sid,$branch_id,$from_Date,$to_Date){
	global $con, $balance;

	$balance[$sid][$branch_id]['qty']=0;
		
	//get the history balance
	$q3=$con->sql_query("select sku_items_cost_history.*, sku_items_cost_history.sku_item_id as sid ,
(select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <='$from_Date') as stock_date
from 
sku_items_cost_history
where branch_id=$branch_id and date <= '$from_Date' and date > 0 and sku_item_id=$sid 
having stock_date = date order by null ");
	while($r3=$con->sql_fetchrow($q3)){
		if($r3['qty']){
			$balance[$sid][$branch_id]['qty']+=$r3['qty'];
			//print "history balance in $r2[code]: $r3[qty]\n";		
		}
	}
	
	//GRN = get the rcvd qty, rcvd cost and grn qty
	$q4=$con->sql_query("select grn_items.sku_item_id as sid, 
sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, 

(rcv_date <= (select max(date) from sku_items_cost_history sh where sh.sku_item_id = sid and sh.branch_id=$branch_id and sh.date <='$from_Date')) as dont_count

from grn_items 
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id  
where grn.branch_id =$branch_id and rcv_date <= '$to_Date' and sku_item_id=$sid and grn.approved=1 and grn.status<2 and grn.active 
group by dont_count, sid order by null");
	while($r4=$con->sql_fetchrow($q4)){				
		if(!$r4['dont_count'] && $r4['qty']){			
			$balance[$sid][$branch_id]['qty']+=$r4['qty'];
			//print "GRN balance in $r2[code]: $r4[qty]\n";
		}
	}
	
	//ADJ = get adj in and adj out
	$q5=$con->sql_query("select 
ai.sku_item_id as sid, 
sum(qty) as qty,

(adjustment_date < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = ai.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_Date')) as dont_count

from adjustment_items ai
left join adjustment adj on adjustment_id = adj.id and ai.branch_id = adj.branch_id
where ai.branch_id =$branch_id and adjustment_date <= '$to_Date' and sku_item_id=$sid and adj.approved and adj.status<2 
group by dont_count, sid order by null");
	while($r5=$con->sql_fetchrow($q5)){
		if(!$r5['dont_count'] && $r5['qty']){			
			$balance[$sid][$branch_id]['qty']+=$r5['qty'];
			//print "ADJ balance in $r2[code]: $r5[qty]\n";
		}
	}			

	//DO get do qty
	$q6=$con->sql_query("select 
do_items.sku_item_id as sid, 
sum(do_items.ctn *uom.fraction + do_items.pcs) as qty,  

(do_date < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = do_items.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_Date')) as dont_count

from do_items 
left join uom on do_items.uom_id=uom.id
left join do on do_id = do.id and do_items.branch_id = do.branch_id
where do_items.branch_id=$branch_id and do_date <= '$to_Date' and sku_item_id=$sid and do.approved and do.checkout and do.status<2 
group by dont_count, sid order by null");
	while($r6=$con->sql_fetchrow($q6)){
		if(!$r6['dont_count'] && $r6['qty']){
			$balance[$sid][$branch_id]['qty']+=$r6['qty'];
			//print "DO balance in $r2[code]: $r6[qty]\n";
		}
	}
	
	//GRA get the gra qty.
	$q7=$con->sql_query("select 
gra_items.sku_item_id as sid, 
sum(qty) as qty, 

(return_timestamp < (select max(date) from sku_items_cost_history sh where sh.sku_item_id = gra_items.sku_item_id and sh.branch_id=$branch_id and sh.date <='$from_Date')) as dont_count

from gra_items 
left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id
where gra.branch_id=$branch_id and return_timestamp <= '$to_Date' and sku_item_id=$sid and gra.status=0 and gra.returned 
group by dont_count, sid order by null");
	while($r7=$con->sql_fetchrow($q7)){
		if(!$r7['dont_count'] && $r7['qty']){
			$balance[$sid][$branch_id]['qty']-=$r7['qty'];
			//print "GRA balance in $r2[code]: $r7[qty]\n";
		}
	}
	
	//POS qty
	$q8=$con->sql_query("select 
si.id as sid, 
sum(qty) as qty, 

(timestamp < (select max(date) from sku_items_cost_history sh where sh.sku_item_id =si.id and sh.branch_id=$branch_id and sh.date <='$from_Date')) as dont_count 

from pos_transaction pos 
left join sku_items si on si.sku_item_code=pos.sku_item_code 
where pos.branch_id=$branch_id and timestamp <= '$to_Date' and si.id=$sid  
group by si.id, dont_count order by null");
	while($r8=$con->sql_fetchrow($q8)){
		if(!$r8['dont_count'] && $r8['qty']){
			$balance[$sid][$branch_id]['qty']-=$r8['qty'];
			//print "POS balance in $r2[code]: $r8[qty]\n";		
		}
	}
}


?>
