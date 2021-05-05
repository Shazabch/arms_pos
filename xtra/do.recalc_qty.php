<?php
define('TERMINAL',1);
include("../config.php");

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);
define('IN_TRANSACTION', 3);

require("../include/mysql.php");

$con = connect_db($db_default_connection[0], $db_default_connection[1],$db_default_connection[2], $db_default_connection[3]);
//$con = connect_db("localhost", "root", "", "cwm_arm2");

function connect_db($server, $u, $p, $db){
	$con = new sql_db($server, $u, $p, $db, false);
	if(!$con->db_connect_id){
		print date("[H:i:s m.d.y] ");
		print("Error: Could not connect to database $db@$server\n");
		return false;
	}
	return $con;
}

function mysql_insert_by_field($arr, $fields = false, $null_if_empty=0){
	$ret = '';
	if (!is_array($fields)){
		$fields = array_keys($arr);
	}

	foreach ($fields as $f){
		if (is_numeric($f)) continue;
		$newf[] = "`$f`";
		$v = $arr[$f];
		if ($ret != '') $ret .= ',';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= $v;
		else
			$ret .= ms($v,$null_if_empty);
	}
	$ret = '(' . join(",", $newf) . ') values (' . $ret . ')';
	return $ret;
}

function ms($str,$null_if_empty=0){
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	return "'" . (trim($str)) . "'";
}


$con->sql_query("create table backup2_grr select * from grr");
$con->sql_query("create table backup2_grr_items select * from grr_items");
$con->sql_query("create table backup2_grn select * from grn");
$con->sql_query("create table backup2_grn_items select * from grn_items");

$flog = fopen("do_log_qty.txt", "w");

//$today = date("Y-m-d");
$q1=$con->sql_query("select do_no, di.*, count(*) as total
from do_items di  
left join do on do.id=do_id and do.branch_id=di.branch_id 
where do.approved and do.do_branch_id and (di.ctn or di.pcs)>0 and do_date<='2008-04-08'
group by di.sku_item_id, di.do_id, di.branch_id 
having total >1 
order by di.branch_id, di.do_id");
while ($r1=$con->sql_fetchrow($q1)){
	
	//delete from the grn_items
	$q2=$con->sql_query("select grn.id as grn_id, grn.branch_id as branch_id, grr_i.id as grr_item_id, grn.grr_id as grr_id  
from grr_items grr_i
left join grn on grn.grr_item_id=grr_i.id and grr_i.branch_id=grn.branch_id 
left join grn_items grn_i on grn.id=grn_i.grn_id and grn.branch_id=grn_i.branch_id 
where grr_i.doc_no='$r1[do_no]' and grr_i.type='do' and sku_item_id=$r1[sku_item_id]");
	$r2=$con->sql_fetchrow($q2);
	
	if($r2){
		$con->sql_query("delete from grn_items where grn_id=$r2[grn_id] and branch_id=$r2[branch_id] and sku_item_id=$r1[sku_item_id]");	
	
		
		echo "delete from grn_items where grn_id=$r2[grn_id] and branch_id=$r2[branch_id] and sku_item_id=$r1[sku_item_id]\n";
		fwrite($flog, "delete from grn_items where grn_id=$r2[grn_id] and branch_id=$r2[branch_id] and sku_item_id=$r1[sku_item_id] | DO:($r1[do_id],$r1[branch_id])\n");
		
		//insert grn_items
		$q3=$con->sql_query("select * from do_items where do_id=$r1[do_id] and branch_id=$r1[branch_id] and sku_item_id=$r1[sku_item_id]");	
		while ($r3=$con->sql_fetchrow($q3)){
	
			$grn_items['branch_id']=$r2['branch_id'];	
			$grn_items['grn_id']=$r2['grn_id'];
			
			$grn_items['sku_item_id']=$r3['sku_item_id'];
			$grn_items['artno_mcode']=$r3['artno_mcode'];
			$grn_items['uom_id']=$r3['uom_id'];
			$grn_items['cost']=$r3['cost_price'];
			$grn_items['ctn']=$r3['ctn'];
			$grn_items['pcs']=$r3['pcs'];
			
			$q4=$con->sql_query("select if(sp.price is null, selling_price, sp.price) as selling 
	from sku_items 
	left join sku on sku.id=sku_items.sku_id left join sku_items_price sp on sku_items.id = sp.sku_item_id and sp.branch_id=$grn_items[branch_id] where sku_items.id=$grn_items[sku_item_id]");
			$r4 = $con->sql_fetchrow($q4);
			
			$grn_items['selling_uom_id']=1;
			$grn_items['selling_price']=$r4['selling'];
							
			$con->sql_query("insert into grn_items " . mysql_insert_by_field($grn_items, array('branch_id', 'grn_id', 'sku_item_id', 'artno_mcode', 'uom_id','cost', 'ctn', 'pcs','selling_uom_id','selling_price')));
			
			echo "insert into grn_items : grn_id=$r2[grn_id] and branch_id=$r2[branch_id]\n";		
			fwrite($flog, "insert into grn_items : grn_id=$r2[grn_id], branch_id=$r2[branch_id], sku_item_id=$grn_items[sku_item_id]\n");		
		}
		
		//update total ctn, total pcs and total amount.
		$con->sql_query("select sum(pcs) as pcs, sum(ctn) as ctn, sum((grn_items.ctn*grn_items.cost)+(grn_items.cost/rcv_uom.fraction*grn_items.pcs)) as cost
	from grn_items 
	left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
	where grn_id=$r2[grn_id] and branch_id=$r2[branch_id]") or die(mysql_error());
	    $t = $con->sql_fetchrow();
	
	    $con->sql_query("update grn set last_update=last_update, amount='$t[cost]', final_amount='$t[cost]' where id=$r2[grn_id] and branch_id=$r2[branch_id]");
	    
	    $con->sql_query("update grr_items set ctn='$t[ctn]', pcs='$t[pcs]', amount='$t[cost]' where id=$r2[grr_item_id] and branch_id=$r2[branch_id]");
	    
	    $con->sql_query("update grr set last_update=last_update, grr_ctn='$t[ctn]', grr_pcs='$t[pcs]', grr_amount='$t[cost]' where id=$r2[grr_id] and branch_id=$r2[branch_id]");
	}
	else{
		fwrite($flog,"ERROR on select from grn to delete and update. (DO:$r1[do_id],$r1[branch_id])\n");
	}	
	fwrite($flog,"\n");
}

fclose($flog);
?>
