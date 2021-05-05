<?php
include("include/common.php");
$maintenance->check(1);

if(isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
		case 'import_sb':
		    import_sb();
		    break;
		case 'compare_sb':
		    compare_sb();
		    break;
		case 'clear_data':
		    clear_data();
		    break;
	}
}

load_available_sb_date();
init_table();
$smarty->display('report.compare_stock_balance.tpl');


function init_table(){ /* moved to maintenance.php */ }

function import_sb(){
	global $con, $smarty;
	
	$fp = fopen($_FILES['sb']['tmp_name'], "r");
    //fgets($fp);	// skip 1st header line

	$delete_date = array();
	
    while (($data = fgetcsv($fp)) !== FALSE) {
	    if($data[0]!=""){
            $upd = array();
			$upd['from_date'] = $data[0];
			$upd['to_date'] = $data[1];
			$upd['sku_item_code'] = $data[2];
			$upd['cost'] = $data[3];
			$upd['selling'] = $data[4];
			$upd['open_bal'] = $data[5];
			$upd['open_bal_val'] = $data[6];
			$upd['closing_bal'] = $data[7];
			$upd['closing_bal_val'] = $data[8];
			
			$date_str = $upd['from_date']."_".$upd['to_date'];
			if(!$delete_date[$date_str]){
                $delete_date[$date_str] = $date_str;
                $con->sql_query("delete from compare_sb where from_date=".ms($upd['from_date'])." and to_date=".ms($upd['to_date'])) or die(mysql_error());
			}
			$con->sql_query("select count(*) from compare_sb where sku_item_code=".ms($upd['sku_item_code'])." and from_date=".ms($upd['from_date'])." and to_date=".ms($upd['to_date'])) or die(mysql_error());
			$c = $con->sql_fetchfield(0);
			if($c>0)    $duplicated[$upd['sku_item_code']] = $c;

			$sql = "replace into compare_sb ".mysql_insert_by_field($upd);
			//die($sql);
			$con->sql_query($sql) or die(mysql_error());
			$row++;
			
			$total+= $upd['closing_bal'];
			//print "$row, ".$upd['closing_bal'].", now $total<br>";
		}
	}
    fclose($fp);
    //print_r($duplicated);
    //print "Total: $total";
    
    $smarty->assign('row_imported',$row);
    $smarty->assign('duplicated',$duplicated);
}

function load_available_sb_date(){
	global $con,$smarty;
	
	$con->sql_query("select distinct from_date,to_date from compare_sb order by from_date") or die(mysql_error());
	$smarty->assign('available_date',$con->sql_fetchrowset());
}

function compare_sb(){
	global $con,$smarty;
	
	$sb1 = $_REQUEST['sb1'];
	$sb2 = $_REQUEST['sb2'];
	$compare_type = $_REQUEST['compare_type'];
	
	// sb1
	$temp = split(",",$sb1);
	$from_date1 = $temp[0];
	$to_date1 = $temp[1];
	$sql = "select sku_item_code,open_bal,open_bal_val,closing_bal,closing_bal_val from compare_sb where from_date=".ms($from_date1)." and to_date=".ms($to_date1);
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
	    $r['open_bal'] = mi($r['open_bal']);
	    $r['open_bal_val'] = mf($r['open_bal_val']);
	    $r['closing_bal'] = mi($r['closing_bal']);
	    $r['closing_bal_val'] = mf($r['closing_bal_val']);
	    $table[$r['sku_item_code']]['sku_item_code'] = $r['sku_item_code'];
		$table[$r['sku_item_code']]['sb1'] = $r;
		
		$total['sb1']['open_bal'] += $r['open_bal'];
		$total['sb1']['open_bal_val'] += $r['open_bal_val'];
		$total['sb1']['closing_bal'] += $r['closing_bal'];
		$total['sb1']['closing_bal_val'] += $r['closing_bal_val'];
	}
	
	// sb2
	$temp = split(",",$sb2);
	$from_date2 = $temp[0];
	$to_date2 = $temp[1];
	$sql = "select sku_item_code,open_bal,open_bal_val,closing_bal,closing_bal_val from compare_sb where from_date=".ms($from_date2)." and to_date=".ms($to_date2);
	$con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow()){
	    $r['open_bal'] = mi($r['open_bal']);
	    $r['open_bal_val'] = mf($r['open_bal_val']);
	    $r['closing_bal'] = mi($r['closing_bal']);
	    $r['closing_bal_val'] = mf($r['closing_bal_val']);
	    $table[$r['sku_item_code']]['sku_item_code'] = $r['sku_item_code'];
		$table[$r['sku_item_code']]['sb2'] = $r;
		
		$total['sb2']['open_bal'] += $r['open_bal'];
		$total['sb2']['open_bal_val'] += $r['open_bal_val'];
		$total['sb2']['closing_bal'] += $r['closing_bal'];
		$total['sb2']['closing_bal_val'] += $r['closing_bal_val'];
	}
	//print_r($table);
	
	// checking
	if($table){
		foreach($table as $sku_item_code=>$r){
		    if($compare_type=='closing_opening'){
                if(($r['sb2']['open_bal']!=$r['sb1']['closing_bal'])||($r['sb2']['open_bal_val']!=$r['sb1']['closing_bal_val'])){
					$table[$sku_item_code]['diff'] = true;
					$diff++;
				}
			}elseif($compare_type=='opening'){
                if(($r['sb2']['open_bal']!=$r['sb1']['open_bal'])||($r['sb2']['open_bal_val']!=$r['sb1']['open_bal_val'])){
					$table[$sku_item_code]['diff'] = true;
					$diff++;
				}
			}elseif($compare_type=='closing'){
                if(($r['sb2']['closing_bal']!=$r['sb1']['closing_bal'])||($r['sb2']['closing_bal_val']!=$r['sb1']['closing_bal_val'])){
					$table[$sku_item_code]['diff'] = true;
					$diff++;
				}
			}
			
		}
	}
	
	$smarty->assign('diff',$diff);
	$smarty->assign('table',$table);
	$smarty->assign('total',$total);
}

function clear_data(){
	global $con;
	
	$con->sql_query("truncate compare_sb") or die(mysql_error());
}
?>
