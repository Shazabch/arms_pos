<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
define("TERMINAL",1);
include("config.php");
if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	require("include/mysqli.php");
}else{
	require("include/mysql.php");
}
require_once('include/functions.php');

ini_set('memory_limit', '256M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}
include_once('../../include/db.php');
if (!$con->db_connect_id) { die('cannot connect '.mysql_error()); }

$agrs = $_SERVER['argv'];

$dir = "export_data";
if (!is_dir($dir)){
	mkdir($dir);
	chmod($dir, 0777);
}



switch($agrs[1]){
	case 'export_item_day_sales':
		export_item_day_sales($agrs);
		exit;
	case 'export_cust_invoice':
		export_cust_invoice($agrs);
		exit;
	case 'export_total_sales':
		export_total_sales($agrs);
		exit;
	case 'export_stock_balance':
		export_stock_balance($agrs);
		exit;
	case 'export_all':
		export_item_day_sales($agrs);
		export_cust_invoice($agrs);
		export_total_sales($agrs);
		export_stock_balance($agrs);
		exit;
	default:
	    print "Invalid Action.\n";
	    exit;	
}

function load_bid_list(){
	global $con, $bid_list;
	
	$con->sql_query("select id from branch order by id");
	while($r = $con->sql_fetchassoc()){
		$bid_list[] = mi($r['id']);
	}
	$con->sql_freeresult();
}

function export_item_day_sales($agrs){
	global $con, $bid_list, $dir;
	
	while($mode = array_shift($agrs)){
		switch($mode){
			case '-date':	// get custom date
				$tmp = strtotime(array_shift($agrs));
				if(!$tmp)	die("Invalid Date\n");
				$date = date("Y-m-d", $tmp);
				break;
		}
	}
	if(!$date)	$date = date("Y-m-d", strtotime("-1 day", time()));
	print "Date = $date\n";
	$date_str = date("d-M-y", strtotime($date));
	
	if(!$bid_list)	load_bid_list();	// get branch list
	
	print "Export Item Day Sales\n";
	$f = fopen($dir."/Item_Day_Sales.csv", "w");
	fwrite($f, "location_id,item_id,sales_date,sales_qty,sales_amt,sales_nn_amt,promo_sales_amt\n");	// header
	
	foreach($bid_list as $bid){	// loop for each branch
		$item_list = array();
		
		print "\nBranch ID = $bid";
		
		$sql = "select pi.qty,(pi.price-pi.discount) as price, pi.promotion_id, ifnull(sic.grn_cost, si.cost_price) as cost,si.artno,si.id as sku_item_id
		from pos_items pi
		left join pos on pos.branch_id=pi.branch_id and pos.counter_id=pi.counter_id and pos.date=pi.date and pos.id=pi.pos_id
		left join sku_items_cost sic on sic.branch_id=pi.branch_id and sic.sku_item_id=pi.sku_item_id
		left join sku_items si on si.id=pi.sku_item_id
		where pos.cancel_status=0 and pos.branch_id=$bid and pos.date=".ms($date)." order by si.artno";
		$q1 = $con->sql_query($sql);
		
		$total_row = $con->sql_numrows($q1);
		$curr_row = 0;
		print ", Total rows = $total_row\n";
		
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			
			$sid = mi($r['sku_item_id']);
			if(!$sid)	continue;
			
			$sales_amt = $promo_sales_amt = 0;
			
			$sales_cost = $r['qty']*$r['cost'];
			
			if($r['promotion_id']>0){	// got promo
				$promo_sales_amt = $r['price'];
			}else{
				$sales_amt = $r['price'];
			}
			
			$item_list[$sid]['item_id'] = $r['artno'];
			$item_list[$sid]['sales_amt'] += $sales_amt;
			$item_list[$sid]['promo_sales_amt'] += $promo_sales_amt;
			$item_list[$sid]['sales_cost'] += $sales_cost;
			$item_list[$sid]['sales_qty'] += $r['qty'];
		}
		$con->sql_freeresult($q1);
		
		if($item_list){
			foreach($item_list as $sid => $r){
				fwrite($f, "$bid,$r[item_id],$date_str,$r[sales_qty],$r[sales_amt],$r[sales_cost],$r[promo_sales_amt]\n");
			}
		}
	}
	fclose($f);
	print "\nDone.\n";
}

function export_cust_invoice($agrs){
	global $con, $bid_list, $dir;
	
	while($mode = array_shift($agrs)){
		switch($mode){
			case '-date':	// get custom date
				$tmp = strtotime(array_shift($agrs));
				if(!$tmp)	die("Invalid Date\n");
				$date = date("Y-m-d", $tmp);
				break;
		}
	}
	if(!$date)	$date = date("Y-m-d", strtotime("-1 day", time()));
	print "Date = $date\n";
	$date_str = date("d-M-y", strtotime($date));
	
	if(!$bid_list)	load_bid_list();	// get branch list
	
	print "Export Cust Invoice\n";
	$f = fopen($dir."/Cust_Invoice.csv", "w");
	fwrite($f, "location_id,till_no,invoice_no,cust_id,invoice_date,Invoice_Nett\n");	// header
	
	foreach($bid_list as $bid){	// loop for each branch
		$item_list = array();
		
		print "\nBranch ID = $bid";
		
		$sql = "select cs.network_name, pos.receipt_no, pos.member_no,pos.amount
		from pos
		left join counter_settings cs on cs.branch_id=pos.branch_id and cs.id=pos.counter_id
		where pos.branch_id=$bid and pos.cancel_status=0 and pos.date=".ms($date)." order by pos.receipt_no";
		
		$q1 = $con->sql_query($sql);
		
		$total_row = $con->sql_numrows($q1);
		$curr_row = 0;
		print ", Total rows = $total_row\n";
		
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			
			if(!$r['member_no'])	$r['member_no'] = 0;
			fwrite($f, "$bid,$r[network_name],$r[receipt_no],$r[member_no],$date_str,$r[amount]\n");
		}
		$con->sql_freeresult($q1);
	}
	fclose($f);
	
	print "\nDone.\n";
}

function export_total_sales($agrs){
	global $con, $dir, $bid_list;

	while($mode = array_shift($agrs)){
	switch($mode){
		case '-date':
			$tmp = strtotime(array_shift($agrs));
			if(!$tmp) die("Invalid Date\n");
			$date = date("Y-m-d", $tmp);
			break;
		}
	}
	
	if(!$date)	$date = date("Y-m-d", strtotime("-1 day", time()));
	print "Date = $date\n";
	
	if(!$bid_list)	load_bid_list();
	
	$header = "location_id,invoice_date,CountOfinvoice_no\n";
	$f = fopen($dir."/Total_Invoice.csv", "w");
	fwrite($f, $header);	// header
	
	$data = $item = array();
	$curr_date = date("d-M-y", strtotime($date));
	foreach($bid_list as $bid){
		print "\nBranch ID = $bid";

		$q1 = $con->sql_query("select count(*) as ttl_pos, branch_id from pos where date = ".ms($date)." and branch_id = ".mi($bid)." and cancel_status=0 group by branch_id");
		
		$total_row = $con->sql_numrows($q1);
		$curr_row = 0;
		print ", Total rows = $total_row\n";
		
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
			$row = array();
			$row[0] = $r['branch_id'];
			$row[1] = $curr_date;
			$row[2] = $r['ttl_pos'];
			
			$item = join(",", $row)."\n";
			fwrite($f, $item);	// write row
		}
		$con->sql_freeresult($q1);
	}
	fclose($f);
	
	print "Cron for Total Invoice - Done\n";
}

function export_stock_balance($agrs){
	global $con, $dir, $bid_list;
	
	if(!$bid_list)	load_bid_list();
	
	$header = "location_id,Year,item_id,stock,stock_amt\n";
	$f = fopen($dir."/Stock_Balance.csv", "w");
	fwrite($f, $header);	// header
	
	file_put_contents($dir."/Stock_Balance.csv",$header);
	
	$y = date("Y");
	$data = $item = array();
	foreach($bid_list as $bid){
		print "\nBranch ID = $bid";
	
		$q1 = $con->sql_query("select * from sku_items_cost where branch_id = ".mi($bid));
		
		$total_row = $con->sql_numrows($q1);
		$curr_row = 0;
		print ", Total rows = $total_row\n";
		
		while($r = $con->sql_fetchassoc($q1)){
			$curr_row++;
			print "\r$curr_row / $total_row . . .";
	
			$row = $item = array();
			$row[0] = $r['branch_id'];
			$row[1] = $y;
			$row[2] = $r['sku_item_id'];
			$row[3] = $r['qty'];
			$row[4] = mf($r['qty']*$r['grn_cost']);
			
			$item = join(",", $row)."\n";
			fwrite($f, $item);	// write row
		}
		$con->sql_freeresult($q1);
	}
	fclose($f);
	
	print "Cron for Stock Balance - Done\n";
}

?>
