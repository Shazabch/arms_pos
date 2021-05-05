<?php
/*
//Warning: Calculation here only work for stock check that got tick 0 qty for other sku items only. For correct one still in progress in cron.csa_report.backup.php

12/23/2010 5:02:54 PM Alex
- add generate previous month
1/3/2011 4:32:51 PM Alex
- fix bugs get previous year opening stock
1/10/2011 2:49:12 PM Alex
- add starting date input
2/16/2011 5:58:35 PM Alex
- change no grn vendor to 'Other'
2/21/2011 10:46:59 AM Alex
- Consignment items use masterfile vendor not GRN
3/3/2011 2:51:02 PM Alex
- remove check GRN vendor at GRA
3/4/2011 5:25:24 PM Alex
- rounding each calculation
3/7/2011 6:32:57 PM Alex
- revert back to 1987 version to remove rounding
3/18/2011 5:52:47 PM Alex
- fix grn cost amount bugs
3/28/2011 6:16:20 PM Alex
- fix price change amount bugs to get po items selling price else get grn selling price
5/6/2011 2:04:59 PM Alex
- recalculate closing stock for outright
5/30/2011 2:08:05 PM Alex
- change stock take variance as (stock check - opening stock)
5/31/2011 5:11:23 PM Alex
- fix calculation bugs if got ',' in price
6/1/2011 10:09:45 AM Alex
- get previous month opening stock if found any finalized at calculate_closing_stock()
- unable to regenerate cache if < 3 month
6/2/2011 11:44:58 AM Alex
- fix stock check variance cost, price bugs
6/9/2011 2:54:41 PM Alex
- stock take variance no take latest selling. If zero cost or selling still calculate.
6/15/2011 11:59:52 AM Alex
- add storing FRESH MARKET WEIGHT cost
6/15/2011 4:15:52 PM Alex
- add check sku or category cache no_inventory for system opening stock
7/27/2011 10:46:30 AM Alex
- exclude "Rebate" calculation
8/2/2011 9:42:11 AM Alex
- add recalculation function.
11/8/2011 10:17:38 AM Alex
- add starting day for all data except stock check
11/14/2011 11:17:23 AM Alex
- temporarily put default starting date for 2011 October only. Will be remove later
11/15/2011 3:05:11 PM Alex
- fix wrong figure of actual opening stock 
11/22/2011 10:12:43 AM Alex
- check if department finalize, calculate it with stock check
12/5/2011 10:47:46 AM Alex
- fix stock variance calculation bugs
1/11/2012 3:01:54 PM Justin
- Redirected create table "stock_balance_b..." to create from functions.php.
2/17/2012 11:10:30 AM Alex
- add get days before stock check data 
3/5/2012 12:09:52 PM Alex
- fix no reset $po_selling bugs
3/6/2012 5:55:18 PM Alex
- fix gra get data bugs 
3/12/2012 6:14:42 PM Alex
- fix missing got data signal
3/13/2012 5:31:17 PM Alex
- skip more than 3 month data and continue generated other
3/19/2012 10:52:26 AM Alex
- show item list
3/19/2012 3:33:56 PM Alex
- fix system stock opening bugs
4/12/2012 10:45:02 AM Alex
- add an argument to generate tmp file
4/16/2012 2:16:21 PM Alex
- enhance calculate_closing_stock can store closing stock data into tmp database
4/16/2012 6:49:53 PM Alex
- fix calculate_closing_stock bugs
4/17/2012 2:28:35 PM Alex
- fix get wrong opening stock while in tmp mode 
4/19/2012 11:21:59 AM Alex
- cannot regenerate previous month when over 4th cutoff day 
3/14/2013 12:25 PM Justin
- Bug fixed on promotion amount does not sum up those negative discount.
6/3/2015 2:02 PM Justin
- Enhanced to use vendor SKU history by branch.
6/5/2015 1:52 PM Justin
- Enhanced actual sales need to include disc_amt2.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

//Tips to generate csa report
if NOW is April 2012, once regenerate October 2011, u will need to regenerate November 2011, December 2011, January 2012 ..... tll NOW

===========================Sample==========================
php cron.csa_report.php -branch (BRANCH CODE) [-year (YEAR) -month (MONTH)] [-tmp] [-remake | -remake -force | -recalculate (BRANCH CODE)] 

-tmp: generate tmp_* cache file and use tmp_csa_report table in database (Warning: make sure table is exists)

-recalculate: recalculate the calculation of closing stock

-remake: regenerate new cache file with 3 months duration from now
-----> additional option -force: void checking and force it to generate

Sample command:

//generate 1 month data with ignore checking
php cron.csa_report.php -branch TMERAH -year 2011 -month 10 -remake -force

//generate whole year data till now date with ignore checking 
php cron.csa_report.php -branch TMERAH -year 2011 -remake -force	==> Warning, careful when write

//recalculate a month
php cron.csa_report.php -branch TMERAH -year 2011 -month 10 -recalculate

//recalculate a year
php cron.csa_report.php -branch TMERAH -year 2011 -recalculate

//generate tmp cache file
===> create tmp_csa_report for current branch by ur own based on csa_report table
php cron.csa_report.php -branch TMERAH -year 2011 -month 10 -tmp -remake -force ===>simply add '-tmp' add center


*/
define('TERMINAL',1);
define('DISP_ERR',1);
include("include/common.php");

ob_end_clean();
ini_set('memory_limit', '512M');

print "Starting memory:".memory_get_usage()."\n";

set_time_limit(0);
$use_tmp_file = false;
$regenerate= false;
$arg = $_SERVER['argv'];
array_shift($arg);
$con_multi=$con;

$no_grn_vendor=array();
$vendor=array();
$fresh=array();
$got_data=false;
$sku_items=array();
$fsku_items=array();
$stc_sku_items=array();
$stc_fsku_items=array();

if (!is_dir('csa_report_cache'))	mkdir('csa_report_cache',0777);

while($a = array_shift($arg))
{
	switch ($a)
	{
		case '-year':
			$input_data['year'] = array_shift($arg);
			break;
		case '-month':
			$input_data['month'] = str_pad(array_shift($arg), 2, "0", STR_PAD_LEFT);
			break;
		case '-day':
			$input_data['day'] = str_pad(array_shift($arg), 2, "0", STR_PAD_LEFT);
			break;
		case '-branch':
			$branch_code = array_shift($arg);
 			if ($branch_code)   $filter_branch= " and code=".ms($branch_code);
			break;
		case '-remake':
		    $regenerate = true;
		    break;
		case '-force':
			$force = true;
			break;	
		case '-tmp':
			$use_tmp_file=true;
			break;
		case '-cron':
		    $starting_date=array_shift($arg);
		    if (!$starting_date)	$starting_date=$config['csa_start_opening'];
		    check_file($starting_date);
			exit;
		case '-test':
		    $starting_date=array_shift($arg);
		    print $starting_date;
		    exit;
		case '-previous':
		    $starting_date=array_shift($arg);
		    if (!$starting_date)	$starting_date=$config['csa_start_opening'];
		    regenerate_previous_month($starting_date);
		    exit;
		case '-recalculate':
			if ($config['csa_start_opening']){	print "got config\n";}
			else{	print "no config. Start date from 2011-01-01 \n";}

			$date_start=$config['csa_start_opening'] ? $config['csa_start_opening']: "2011-01-01"; 
			$branch_code = $branch_code ? $branch_code : array_shift($arg);
			
			if ($branch_code)   $filter_branch= " and code=".ms($branch_code);
			else{ print "No branch code";exit;}
			
			$con->sql_query("select * from branch where active=1 $filter_branch order by sequence");			

			$b = $con->sql_fetchassoc();
			$form['bid']=$b['id'];
			$year_now = date("Y");
			$month_now = date("m");

			if ($use_tmp_file)	$tmp_file="tmp_";
									
			if ($input_data){
				$form['year']=$input_data['year'];
				$form['month']=$input_data['month'];
				
				$saved_file="csa_report_cache/".$tmp_file."rpt_csa_b".$form['bid']."_".$form['year'].str_pad($form['month'], 2, "0", STR_PAD_LEFT);				
				calculate_closing_stock($form, $saved_file, $use_tmp_file);
			
			}else{							
				list($startyear,$startmonth,$startday)=explode("-",$date_start);
				
				for ($year=$startyear;$year<=$year_now;$year++){
					if ($startyear != $year_now) $end_month=12;
					else	$end_month=$month_now;
				
					for ($month=$startmonth;$month<=$end_month;$month++){
						$form['year']=$year;
						$form['month']=$month;
						
						$saved_file="csa_report_cache/".$tmp_file."rpt_csa_b".$form['bid']."_".$year.str_pad($month, 2, "0", STR_PAD_LEFT);				
						calculate_closing_stock($form, $saved_file, $use_tmp_file);
					}
				}
			}
		    $startmonth=1;
		    exit;
		default:
			die("Unknown option: $a\n");
	}
}

input_settings($input_data,$filter_branch, $regenerate, $force, $use_tmp_file);

function input_settings($input_data='',$filter_branch='', $regenerate=false, $force=false, $use_tmp_file=false){
	global $con, $config;
	//use temp file
	if ($use_tmp_file)	$tmp_name="tmp_";
	
	// Get branch id
	$con->sql_query("select * from branch where active=1 $filter_branch order by sequence");

	while($b = $con->sql_fetchassoc()){
		$branches[$b['id']] = $b['code'];
	}

	$now_year = date('Y');
	$now_month = date('n');

	//Condition to run the cron
	if (!$input_data['year'] and $input_data['month']) {    //no year only
		print "\nMissing year.";
		exit;
	}elseif (!$input_data['year'] and !$input_data['month']){       //no year and no month

		if (!$config['csa_start_opening']){
			$con->sql_query("select min(year(date)) as min_year from pos having min_year > 0 order by max_year desc");
	
			while ($y = $con->sql_fetchassoc()){
		        $min_year = $y['min_year'];
			}
		}else{
			$min_year = date("Y", strtotime($config['csa_start_opening']));
		}
		
	    $max_year = $now_year;
	        
		$count_year = $max_year - $min_year;

		for($i=0; $i<=$count_year; $i++){
			$years[$i]['year'] = $min_year+$i;

			if ($now_year == $years[$i]['year']){
				$loop_month = $now_month;
			}else{
	            $loop_month = 12;
			}

			for($j=1; $j<=$loop_month;$j++){
				$months[$years[$i]['year']][$j]['month'] = $j;
			}
		}
		$con->sql_freeresult();

	}elseif ($input_data['year'] and !$input_data['month']){    //no month
	    $years[$input_data['year']]['year']=$input_data['year'];
		if ($now_year == $input_data['year']){
			$loop_month = $now_month;
		}else{
	        $loop_month = 12;
		}
		
		for($j=1; $j<=$loop_month; $j++){
			$months[$input_data['year']][$j]['month'] = $j;
		}

	}elseif ($input_data['year'] and $input_data['month']){     //both have
	    $years[$input_data['year']]['year']=$input_data['year'];
		$months[$input_data['year']][0]['month']=$input_data['month'];
	}

	// Create new row if not exist
	foreach ($branches as $bid => $code){

		print "\nBranch $code\n";

		foreach ($years as $mm_year){
			$year = $mm_year['year'];

			foreach ($months[$year] as $mm_month){
	            $month=$mm_month['month'];
				//set file name
				$saved_file="csa_report_cache/".$tmp_name."rpt_csa_b".$bid."_".$year.str_pad($month, 2, "0", STR_PAD_LEFT);
				print "Saving into directory $saved_file";
				$form['day']=$input_data['day'];
				$form['month']=$month;
				$form['year']=$year;
				$form['bid']=$bid;
				
				regenerate_report($form, $saved_file, $regenerate, $force, $use_tmp_file);
			}
		}
	}
}

function check_file($starting_date){
	global $con;
	
	//check flag files
    $ret = file_exists_2("csa_report_cache/*.csa_cache");

	if (!$ret){
		print "No File found.\n";
		exit;
	}

	$directory=explode("\n",$ret);

	$num_files=count($directory)-1;
	print "$num_files file(s) found. Generating.....\n";

	foreach ($directory as $dir){
	    if (!$dir) continue;
	    
	    list($dummy,$file)=explode('/',$dir);
	    list($branch_code, $year, $month, $dummy)=explode('.',$file);

		print "Flag file: $dir\n";

		$con->sql_query("select * from branch where code=".ms($branch_code));
		$r=$con->sql_fetchassoc();
        $con->sql_freeresult();

		$saved_file="csa_report_cache/rpt_csa_b".$r['id']."_".$year.str_pad($month, 2, "0", STR_PAD_LEFT);

		//condition to generate report
        $form['bid']=$r['id'];
		$form['year']=$year;
		$form['month']=$month;

		$generate_r=check_date_generate($year,$month,$starting_date);
		
		if ($generate_r)
	        regenerate_report($form, $saved_file, true);
		    
		unlink($dir);

		if (is_cached($dir)){
		    print "Unable to delete $dir. Please change current file permission permission.";
		    continue;
		}
	}
}

function file_exists_2($pattern){
   $ret = shell_exec("ls ".$pattern);
   return ($ret);
}

function regenerate_previous_month($starting_date){
	global $con;
	$now_year = date('Y');
	$now_month = date('n')-1;

	if (!$now_month){
        $now_year-=1;
        $now_month=12;
	}

	$con->sql_query("select * from branch where code=".ms(BRANCH_CODE));
	$r=$con->sql_fetchassoc();
    $con->sql_freeresult();

	$saved_file="csa_report_cache/rpt_csa_b".$r['id']."_".$now_year.str_pad($now_month, 2, "0", STR_PAD_LEFT);

	//condition to generate report
    $form['bid']=$r['id'];
	$form['year']=$now_year;
	$form['month']=$now_month;

	$generate_r=check_date_generate($now_year,$now_month,$starting_date);

	if ($generate_r)
	    regenerate_report($form, $saved_file, true);
}

function regenerate_report($form, $file_name, $regenerate, $force = false, $use_tmp_file = false){

	if (!$force){
		$now_year = date('Y');
		$now_month = date('n');

		$diff_year=$now_year-$form['year'];

		if ($diff_year > 1){
			$diff_month=100;
		}elseif ($diff_year == 1){
			$diff_month=$now_month+12-$form['month'];
		}else{
			$diff_month=$now_month-$form['month'];
		}
		
		if ($diff_month>3){
			print "\nNot allow to regenerate more than 3 month\n";
			return;
		}
	}

	if (is_cached($file_name))
	{
		if ($regenerate){
			print "\nDelete $file_name";
		    unlink($file_name);

			if (is_cached($file_name)){
			    print "Unable to delete $file_name. Please change current file permission permission.";
			    return;
			}

			print "\nGenerating $file_name ......\n";
			generate_report($form,$file_name,$use_tmp_file);
		}else{
			print "\n$file_name existed. Skip to next......\n";
			//calculate_closing_stock($form,$file_name);
			return;
		}
	}else{
		print "\nGenerating $file_name ......\n";
		generate_report($form,$file_name,$use_tmp_file);
	}
}

function generate_report($form,$file_name,$use_tmp_file=false)
{
    global $con, $config,$con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items,$stc_sku_items,$stc_fsku_items;
	/*
		data needed
		->branch_id
		->Year
		->Month
	*/
    //$con_multi=new mysql_multi();
    
    $tmp_file = $use_tmp_file ? "tmp_" : "";
    
	$starttime=microtime(true);

	$con_multi=$con;

	$dept_sql="Select c.id, c.root_id from category c";

	$con->sql_query($dept_sql);
	while ($dept=$con->sql_fetchassoc()){
	    $get_rootid[$dept['id']]=$dept['root_id'];
	}

	$got_data=false;

    $bid = $form['bid'];

	$start_day=$form['day'];
 	$days =	days_of_month($form['month'], $form['year']);
	$month=$form['month'];
	$year=$form['year'];

	if ($config['csa_start_opening']){
		list($y,$m,$d)=explode("-",$config['csa_start_opening']);
		if ($year<$y || ($year==$y && $month < $m)){	
			print "Not allow to generate cache that before starting date\n";	
			return;
		}
	}

	$to_month=$month+1;
	if ($to_month>12){
		$to_year=$year+1;
		$to_month=1;
	}else $to_year=$year;

    $from_date=ms($year."-".$month."-01");    
    $to_date=ms($year."-".$month."-".$days);
    $adj_start_date=$start_day ? ms($year."-".$month."-".$start_day) : $from_date;

	//temporarily only. will remove later    
    //if ($year == '2011' && $month == '10')	$adj_start_date = ms($year."-".$month."-03");
	
    $time_date=$to_year."-".$to_month."-01";
    $time_date = ms(date("Y-m-d H:i:s",strtotime("-1 seconds",strtotime($time_date))));
    
	//get system opening stock
	get_system_opening_stock($bid,($year."-".$month."-01"),$data_cat,$data_ven,$data_fre,$data_item_ven,$data_item_fre,$tmp_file);

	$from_date_opening = date('Y-m-d', strtotime("-1 day", strtotime($year."-".$month."-01")));
	list($o_year,$o_month,$o_day)=explode("-",$from_date_opening);

	print "Get previous month closing stock year: $o_year, month: $o_month\n";
	$data_id=$con->sql_query("select finalized from ".$tmp_file."csa_report where year=$o_year and month=$o_month and branch_id=$bid");
	if($con->sql_numrows($data_id)>0){
		while ($op=$con->sql_fetchassoc($data_id)){
			$finalize_data = unserialize($op['finalized']);
		}
	}
	$con_multi->sql_freeresult($data_id);
	
	foreach ($finalize_data as $rid => $other){
		foreach ($other as $cid => $dummy){
			$opening_arr[$cid]=$dummy;
		}
	}
	unset($finalize_data, $rid, $other, $cid, $dummy);
	
	$data_id=$con->sql_query("select closing_notf_out_cost, closing_notf_out_selling, closing_fresh_selling from ".$tmp_file."csa_report where year=$o_year and month=$o_month and branch_id=$bid");
	if($con->sql_numrows($data_id)>0){
		unset($opening_data);
		while ($op=$con->sql_fetchassoc($data_id)){
			$cls['cost'] = unserialize($op['closing_notf_out_cost']);
			$cls['selling'] = unserialize($op['closing_notf_out_selling']);
			$cls['fselling'] = unserialize($op['closing_fresh_selling']);
		}
	}
	$con_multi->sql_freeresult($data_id);

	foreach($cls['cost'] as $cid=>$data){
		if (!$opening_arr[$cid]) continue;
		foreach($data as $vid=> $other){
			$opening_data[$cid][$vid]['cost']+=$other['O']['cp'];
		}
	}

	foreach($cls['selling'] as $cid=>$data){
		if (!$opening_arr[$cid]) continue;
		foreach($data as $vid=> $other){
			$opening_data[$cid][$vid]['price']+=$other['O']['sp'];
		}
	}

	foreach($cls['fselling'] as $cid=>$other){
		if (!$opening_arr[$cid]) continue;
		$fresh[$cid]=1;
		$fresh[$get_rootid[$cid]]=1;		
		$opening_data[$cid]['FRESH']['price']+=$other['F']['sp'];
	}
	unset($cls);
	print "Done\n";	

    //------------------->Stock take variance
	//get latest date from stock check
	$sql="select max(date) as max_date from stock_check where branch_id=$bid and date between $from_date and $to_date";
	$res_id=$con_multi->sql_query($sql);
	if ($con_multi->sql_numrows($res_id)>0){
		$r=$con_multi->sql_fetchassoc($res_id);
		if ($r['max_date']){
			$stc_date=$r['max_date'];
		}
	}
	
	print "Stock take variance...$stc_date";
	if ($stc_date){
	    $st_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
		(select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.sku_item_id=si.id and sc.date between vsh.from_date and vsh.to_date order by vsh.to_date desc limit 1) as vsh_vendor_id, s.vendor_id as s_vendor_id ,si.id as sku_item_id, sc.date,c.department_id, s.sku_type, si.selling_price as si_selling_price, sum(sc.qty * sc.cost) as sc_cost_price, sum(sc.qty * sc.selling) as sc_selling_price
	from sku_items si
	left join stock_check sc on sc.sku_item_code=si.sku_item_code 
	left join sku s on si.sku_id=s.id
	left join category c on s.category_id=c.id
	left join category_cache cc on cc.category_id=c.id and cc.category_id=c.id
	where s.sku_type='OUTRIGHT' and sc.date = ".ms($stc_date)." and sc.branch_id=$bid
	group by c.department_id, vsh_vendor_id,si.id,is_fresh";
	//print $st_sql;
	    $st_query=$con_multi->sql_query($st_sql);
		if ($con_multi->sql_numrows($st_query)>0){
			$num_rows=$con_multi->sql_numrows($st_query);
			print "Total=$num_rows \n";
			while ($st_db=$con_multi->sql_fetchassoc($st_query)){
				print "$num_rows...\r";
	
	 			if ($st_db['vsh_vendor_id'] >0  )    $st_vendor_id=$st_db['vsh_vendor_id'];
				else{
					$no_grn_vendor[$st_db['department_id']][$st_db['s_vendor_id']][$st_db['sku_type']]=1;
					$st_vendor_id = 0;
				}
									
	 			if ($st_db['is_fresh']=='no'){
					$st_selling_price=$st_db['sc_selling_price']-$opening_data[$st_db['department_id']][$st_vendor_id]['price'];
					$st_cost_price=$st_db['sc_cost_price']-$opening_data[$st_db['department_id']][$st_vendor_id]['cost'];
				    $st_arr[$st_db['department_id']][$st_db['sku_type']]['cost_price']+=$st_cost_price;
				    $st_arr[$st_db['department_id']][$st_db['sku_type']]['selling_price']+=$st_selling_price;
				    				    
				    unset($opening_data[$st_db['department_id']][$st_vendor_id]);
				    
	//              $st_arr[$st_db['department_id']][$st_db['sku_type']]=1;
				}elseif ($st_db['is_fresh']=='yes'){
					$st_selling_price=$st_db['sc_selling_price']-$opening_data[$st_db['department_id']]['FRESH']['price'];
	//				$st_cost_price=$st_db['sc_cost_price'];
	
	//		    	$st_arr_f[$st_db['department_id']]['FRESH']['cost_price']+=$st_cost_price;
			    	$fst_arr[$st_db['department_id']]['FRESH']['selling_price']+=$st_selling_price;
			    	
			    	if (!empty($st_selling_price)){
						//$fsku_items[$st_db['department_id']]['FRESH'][$st_db['sku_item_id']]['descrip']=1;
				    	$fsku_items[$st_db['department_id']]['FRESH'][$st_db['sku_item_id']]=$st_db['sku_item_id'];
						$stc_fsku_items[$st_db['department_id']]['FRESH'][$st_db['sku_item_id']]=$st_db['sku_item_id'];	
			    		$item_fst_arr[$st_db['department_id']]['FRESH'][$st_db['sku_item_id']]['selling_price']+=$st_selling_price;
			    	}
	//  			$fresh[$st_db['department_id']]=1;
	//  			$fresh[$get_rootid[$st_db['department_id']]]=1;
	//		    	$fst_arr[$st_db['department_id']]['FRESH']=1;
	
					unset($opening_data[$st_db['department_id']]['FRESH']);
				}
	
	//			$st_selling_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['si_selling_price'];
	//			$st_cost_price=($st_db['sc_qty']-$st_db['start_qty'])*$st_db['sc_cost_price'];

		    	if(!empty($st_cost_price) || !empty($st_selling_price)){
		 			if ($st_db['is_fresh']=='no'){
				    	$vendor[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['descrip']=1;
				    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['cost_price']+=$st_cost_price;
				    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]['selling_price']+=$st_selling_price;
				    
			    		//$sku_items[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']][$st_db['sku_item_id']]['descrip']=1;
			    		$stc_sku_items[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']][$st_db['sku_item_id']]=$st_db['sku_item_id'];
			    		$sku_items[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']][$st_db['sku_item_id']]=$st_db['sku_item_id'];
				    	$item_vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']][$st_db['sku_item_id']]['cost_price']+=$st_db['sc_cost_price'];
				    	$item_vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']][$st_db['sku_item_id']]['selling_price']+=$st_db['sc_selling_price'];
				    	
	//			    	$vst_arr[$st_db['department_id']][$st_vendor_id][$st_db['sku_type']]=1;
					}
				}
	
		    	$num_rows-=1;
			}
			$got_data=true;
	
	/*
		if ($st_arr_f){
			foreach ($st_arr_f as $dept_id => $f_st){
	//			$fst_arr[$dept_id]['FRESH']['cost_price']+=$f_st['FRESH']['cost_price']-$opening_data[$dept_id]['FRESH']['cost'];
		    	$fst_arr[$dept_id]['FRESH']['selling_price']+=$f_st['FRESH']['selling_price']-$opening_data[$dept_id]['FRESH']['price'];
			}
			
	//		print_r($fst_arr);
			
			unset($st_arr_f);
		}
	*/
			if ($opening_data){
				foreach ($opening_data as $cid => $data){
					foreach ($data as $vsh => $other){
						$st_selling_price=0-$other['price'];
						if (ms($vsh) == "'FRESH'"){
							$fst_arr[$cid]['FRESH']['selling_price']=+$st_selling_price;
						}else{

							$st_cost_price=0-$other['cost'];
						
						    $st_arr[$cid]['OUTRIGHT']['cost_price']+=$st_cost_price;
						    $st_arr[$cid]['OUTRIGHT']['selling_price']+=$st_selling_price;
						    
					    	$vst_arr[$cid][$vsh]['OUTRIGHT']['cost_price']+=$st_cost_price;
					    	$vst_arr[$cid][$vsh]['OUTRIGHT']['selling_price']+=$st_selling_price;
						}
					}
				}
			}
			unset($opening_data);
		
		}
		
		$con_multi->sql_freeresult($st_query);
	
		$data_cat['stv']=$st_arr;
		$data_ven['vstv']=$vst_arr;
		$data_fre['fstv']=$fst_arr;

		$data_item_ven['item_vstv']=$item_vst_arr;
		$data_item_fre['item_fstv']=$item_fst_arr;
//		$item_ven['vstv']=$item_vst_arr;
//		$item_fre['fstv']=$item_fst_arr;

		unset($st_arr,$vst_arr,$fst_arr);
		unset($item_vst_arr,$item_fst_arr);

		//now get total before the stock check
		if (strtotime($stc_date) > strtotime($year."-".$month."-01")){
			
			$date_before_end_date=ms(date("Y-m-d",strtotime("-1 day",strtotime($stc_date))));
			$time_before_end_date=ms(date("Y-m-d H:i:s",strtotime("-1 seconds",strtotime($stc_date))));
			
			print "\nStart Getting data from date $from_date to $time_before_end_date\n";
			
			get_stock_receive($bid,$from_date,$date_before_end_date,$tmp_data_cat,$tmp_data_ven,$tmp_data_fre,$tmp_data_item_ven,$tmp_data_item_fre);
			get_adjustment($bid,$from_date,$date_before_end_date,$tmp_data_cat,$tmp_data_ven,$tmp_data_fre,$tmp_data_item_ven,$tmp_data_item_fre);
			get_return_stock($bid,$from_date,$time_before_end_date,$tmp_data_cat,$tmp_data_ven,$tmp_data_fre,$tmp_data_item_ven,$tmp_data_item_fre);	
			get_promotion_sales_amount($bid,$from_date,$date_before_end_date,$tmp_data_cat,$tmp_data_ven,$tmp_data_fre,$tmp_data_item_ven,$tmp_data_item_fre);

		}

		//start from stock check date
		$adj_start_date=ms($stc_date);
	}
	
	print "Done\n";
	
	get_stock_receive($bid,$adj_start_date,$to_date,$data_cat,$data_ven,$data_fre,$data_item_ven,$data_item_fre);
	get_adjustment($bid,$adj_start_date,$to_date,$data_cat,$data_ven,$data_fre,$data_item_ven,$data_item_fre);
	get_return_stock($bid,$adj_start_date,$time_date,$data_cat,$data_ven,$data_fre,$data_item_ven,$data_item_fre);
  	get_promotion_sales_amount($bid,$adj_start_date,$to_date,$data_cat,$data_ven,$data_fre,$data_item_ven,$data_item_fre);

	add_to_data_from_source($data_cat,$tmp_data_cat);
	add_to_data_from_source($data_ven,$tmp_data_ven);
	add_to_data_from_source($data_fre,$tmp_data_fre);
	add_to_data_from_source($data_item_ven,$tmp_data_item_ven);
	add_to_data_from_source($data_item_fre,$tmp_data_item_fre);

	print "Get Vendor Data...\n";

	//--------------------> Assign Vendor
	if ($vendor){
		foreach ($vendor as $dept_id => $vsd){
			foreach ($vsd as $vid => $sd){
			    foreach ($sd as $sku_type => $descrip){
					if (!$vid){
						$vendor[$dept_id][0][$sku_type]['descrip']="Other";
						$check_vendor[$dept_id][$sku_type]=1;
						continue;
					}  
					
					$vendor_id[$sku_type][$vid]=$vid;
				}

				if($vendor_id){
					foreach ($vendor_id as $type => $iid){
						$get_vendor_id= join (',',$iid);
						$get_vendor="select id,description from vendor where id in ($get_vendor_id)";
						$vendor_query=$con->sql_query($get_vendor);

						while ($ven_db=$con->sql_fetchassoc($vendor_query)){
							$vendor[$dept_id][$ven_db['id']][$type]['descrip']=$ven_db['description'];

							// check exist for each department
						    $check_vendor[$dept_id][$type]=1;
					    }
			    		$con->sql_freeresult($vendor_query);
		    		}
		    		unset($vendor_id);
				}
			}
		}
	
		//put Other vendor at behind
		foreach ($vendor as $dept_id => $vsd){
	        if ($vendor[$dept_id][0]){
	            $dummy=$vendor[$dept_id][0];
	            unset($vendor[$dept_id][0]);
	            $vendor[$dept_id][0]=$dummy;
			}
		}
	}
	/*
	will get this info in report
	if ($fsku_items){
		foreach ($fsku_items as $dept_id => $tsd){
			foreach ($tsd as $type => $id){
			    foreach ($id as $sid => $dummy){
			    	$sku_item_id[$sid]=$sid;
				}

				if ($sku_item_id){						
					$get_items="select id,receipt_description from sku_items where id in (".join(',',$sku_item_id).") order by receipt_description";
					
					$items_query=$con->sql_query($get_items);

					unset($fsku_items[$dept_id][$type]);
					while ($sku_db=$con->sql_fetchassoc($items_query)){
						$fsku_items[$dept_id][$type][$sku_db['id']]['descrip']=$sku_db['receipt_description'];
				    }
		    		$con->sql_freeresult($items_query);
				}
				unset($sku_item_id);			
			}
		}
	}

	if ($sku_items){
		foreach ($sku_items as $dept_id => $vsid){
			if ($dept_id != 2) continue;
			foreach ($vsid as $vid => $sid){
			    foreach ($sid as $sku_type => $id){
					foreach ($id as $sid => $dummy){					
						$sku_item_id[$sid]=$sid;
					}

					if ($sku_item_id){					
						$get_items="select id,receipt_description from sku_items where id in (".join(',',$sku_item_id).") order by receipt_description";
						$items_query=$con->sql_query($get_items);
						
						unset($sku_items[$dept_id][$vid][$sku_type]);
						while ($sku_db=$con->sql_fetchassoc($items_query)){
							$sku_items[$dept_id][$vid][$sku_type][$sku_db['id']]['descrip']=$sku_db['receipt_description'];
					    }
			    		$con->sql_freeresult($items_query);
					}
					unset($sku_item_id);
				}
			}
		}
//		print_r($sku_items[2]);
	}
	*/

    $data_fre['fresh']=$fresh;
	$data_ven['vendor']=$vendor;
	$data_ven['check_vendor']=$check_vendor;
	$data_item_ven['sku_items']=$sku_items;
	$data_item_ven['stc_sku_items']=$stc_sku_items;
	$data_item_fre['fsku_items']=$fsku_items;
	$data_item_fre['stc_fsku_items']=$stc_fsku_items;
	unset($fresh);
	unset($vendor);
	unset($check_vendor);

	print "Done\n";
	
	print "Saving Data...\n";
	
	$time_created=date("Y-m-d H:i:s");
	$con->sql_query("insert into ".$tmp_file."csa_report (branch_id,year,month,generated_timestamp)
					values($bid,$year,$month,".ms($time_created).") on duplicate key update
					generated_timestamp=".ms($time_created));

	$file[]=$time_created."\n";
	$file[]=serialize($data_cat)."\n";
	$file[]=serialize($data_ven)."\n";
	$file[]=serialize($data_fre)."\n";
	$file[]=$got_data."\n";
	$file[]=serialize($no_grn_vendor)."\n";
	$file[]=$stc_date."\n";
	$file[]=serialize($data_item_ven)."\n";
	$file[]=serialize($data_item_fre)."\n";
	file_put_contents($file_name, $file);
	chmod($file_name, 0777);
	
	print "Done\n";
	$endtime=microtime(true);

	$usage = $endtime - $starttime;
	
	print "Time usage: $usage \n\n";
	
	calculate_closing_stock($form,$file_name,$use_tmp_file);
}

function get_system_opening_stock($bid,$from_date,&$data_cat,&$data_ven,&$data_fre,&$data_item_ven,&$data_item_fre,$tmp_file=''){
 	global $con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items;
    //------------------->SYStem Opening Stock
	list($year,$month,$day)= explode("-",$from_date);

	print "System Opening Stock...";
	$from_date_opening = date('Y-m-d', strtotime("-1 day", strtotime($year."-".$month."-01")));
 
	list($o_year,$o_month,$o_day)=explode("-",$from_date_opening);
    $stock_balance = "stock_balance_b".$bid."_".$o_year;
	print "Getting data from $stock_balance \n";

	$from_date_opening=ms($from_date_opening);

	$prms = array();
	$prms['tbl'] = $stock_balance;
	initial_branch_sb_table($prms);

    $os_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
	(select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.sku_item_id=si.id and ".ms($from_date)." between vsh.from_date and vsh.to_date order by vsh.to_date desc limit 1) as vsh_vendor_id, s.vendor_id as s_vendor_id, c.department_id, si.id as sku_item_id, s.sku_type,sb.sku_item_id, sb.qty as qty,sb.cost as cost_price, si.selling_price
			from sku_items si
			left join $stock_balance sb on sb.sku_item_id=si.id and $from_date_opening between sb.from_date and sb.to_date
			left join sku s on si.sku_id=s.id
			left join category c on s.category_id=c.id
			left join category_cache cc on s.category_id=cc.category_id and cc.category_id=c.id
			where s.sku_type='OUTRIGHT' and sb.sku_item_id>0 and ((s.no_inventory='inherit' and cc.no_inventory='no') or s.no_inventory='no')";

    $os_query=$con_multi->sql_query($os_sql);

	if ($con_multi->sql_numrows($os_query)>0){
		$num_rows=$con_multi->sql_numrows($os_query);
		print "Total=$num_rows \n";
		while ($os_db=$con_multi->sql_fetchassoc($os_query)){
			print "$num_rows...\r";

			if ($os_db['vsh_vendor_id'] >0  )    $os_vendor_id=$os_db['vsh_vendor_id'];
			else{
			    $no_grn_vendor[$os_db['department_id']][$os_db['s_vendor_id']][$os_db['sku_type']] = 1;
				$os_vendor_id = 0;
			}

			$get_price="select price from sku_items_price_history
						where branch_id=$bid and sku_item_id=$os_db[sku_item_id] and added <= $from_date_opening
						order by added desc limit 1";
	    	$os_query2=$con_multi->sql_query($get_price);
	    	$os_db2=$con_multi->sql_fetchassoc($os_query2);
   			$con_multi->sql_freeresult($os_query2);

	    	if (!$os_db2['price']){
				$os_selling_price=$os_db['qty']*$os_db['selling_price'];
			}else{
	            $os_selling_price=$os_db['qty']*$os_db2['price'];
			}

			$os_cost_price=$os_db['qty'] * $os_db['cost_price'];
			
			if ($os_db['is_fresh']=='no'){
				$os_arr[$os_db['department_id']][$os_db['sku_type']]['cost_price']+=$os_cost_price;
		    	$os_arr[$os_db['department_id']][$os_db['sku_type']]['selling_price']+=$os_selling_price;
			}elseif ($os_db['is_fresh']=='yes'){
				if (!empty($os_cost_price)  || !empty($os_selling_price) ){
				    $fresh[$os_db['department_id']]=1;
				    $fresh[$get_rootid[$os_db['department_id']]]=1;
			    	$fos_arr[$os_db['department_id']]['FRESH']['cost_price']+=$os_cost_price;
			    	$fos_arr[$os_db['department_id']]['FRESH']['selling_price']+=$os_selling_price;
			    	
			    	//$fsku_items[$os_db['department_id']]['FRESH'][$os_db['sku_item_id']]['descrip']=1;
			    	$fsku_items[$os_db['department_id']]['FRESH'][$os_db['sku_item_id']]=$os_db['sku_item_id'];
					$item_fos_arr[$os_db['department_id']]['FRESH'][$os_db['sku_item_id']]['cost_price']+=$os_cost_price;
			    	$item_fos_arr[$os_db['department_id']]['FRESH'][$os_db['sku_item_id']]['selling_price']+=$os_selling_price;
				}
			}

			if (!empty($os_cost_price)  || !empty($os_selling_price) ){
				if ($os_db['is_fresh']=='no'){
			    	$vendor[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']]['descrip']=1;
			    	$vos_arr[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']]['cost_price']+=$os_cost_price;
			    	$vos_arr[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']]['selling_price']+=$os_selling_price;

					//$sku_items[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']][$os_db['sku_item_id']]['descrip']=1;
					$sku_items[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']][$os_db['sku_item_id']]=$os_db['sku_item_id'];
			    	$item_vos_arr[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']][$os_db['sku_item_id']]['cost_price']+=$os_cost_price;
			    	$item_vos_arr[$os_db['department_id']][$os_vendor_id][$os_db['sku_type']][$os_db['sku_item_id']]['selling_price']+=$os_selling_price;
					
//			    	$opening_data[$os_db['department_id']][$os_vendor_id]['qty']+=$os_db['qty'];
//			    	$opening_data[$os_db['department_id']][$os_vendor_id]['cost']+=$os_cost_price;
//					$opening_data[$os_db['department_id']][$os_vendor_id]['price']+=$os_selling_price;			
				}else{
//			    	$opening_data[$os_db['department_id']]['FRESH']['qty']+=$os_db['qty'];
//			    	$opening_data[$os_db['department_id']]['FRESH']['cost']+=$os_cost_price;
//					$opening_data[$os_db['department_id']]['FRESH']['price']+=$os_selling_price;			
				}
	    	}
	    	
	    	$num_rows-=1;
		}
		$got_data=true;
	}
	
	$con_multi->sql_freeresult($os_query);

	$data_cat['os']=$os_arr;
	$data_ven['vos']=$vos_arr;
	$data_fre['fos']=$fos_arr;

	$data_item_ven['item_vos']=$item_vos_arr;
	$data_item_fre['item_fos']=$item_fos_arr;
//	$item_ven['vos']=$item_vos_arr;
//	$item_fre['fos']=$item_fos_arr;

	save_opening_to_closing($bid, $year, $month, $vos_arr, $fos_arr,$tmp_file);

	unset($os_arr,$vos_arr,$fos_arr);
	unset($item_vos_arr,$item_fos_arr);

	print "Done\n";

}

function get_stock_receive($bid,$adj_start_date,$to_date,&$data_cat,&$data_ven,&$data_fre,&$data_item_ven,&$data_item_fre){
 	global $con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items,$stc_sku_items,$stc_fsku_items;

   //------------------->Stock Received and GRN PENDING

// and grn.authorized=1 --->>> Will Be added. COZ column juz created at 7/15/2010
	print "Stock Received and GRN PENDING...$adj_start_date";
    $sr_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
	grn.vendor_id, c.department_id, s.sku_type,si.id as sku_item_id,
	sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
	sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as selling_price,
	sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
	(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
	(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
	if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)) as cost_price
from grn_items
left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
left join grn on grn_items.grn_id=grn.id and grn_items.branch_id=grn.branch_id
left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
left join sku_items si on grn_items.sku_item_id = si.id
left join sku s on si.sku_id = s.id
left join category c on s.category_id=c.id
left join category_cache cc on s.category_id = cc.category_id and cc.category_id=c.id
where s.sku_type='OUTRIGHT' and grn_items.sku_item_id>0 and grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and grr.rcv_date between $adj_start_date and $to_date and grn_items.branch_id = $bid
group by si.id,grn.vendor_id,is_fresh";

    $sr_query=$con_multi->sql_query($sr_sql);

	if ($con_multi->sql_numrows($sr_query)>0){
		$num_rows=$con_multi->sql_numrows($sr_query);
		print "Total=$num_rows \n";

		while ($sr_db=$con_multi->sql_fetchassoc($sr_query)){
			print "$num_rows...\r";
		
 			$sr_vendor_id=$sr_db['vendor_id'];

			if ($sr_db['is_fresh']=='no'){
		    	$sr_arr[$sr_db['department_id']][$sr_db['sku_type']]['cost_price']+=$sr_db['cost_price'];
		    	$sr_arr[$sr_db['department_id']][$sr_db['sku_type']]['selling_price']+=$sr_db['selling_price'];
			}elseif ($sr_db['is_fresh']=='yes'){
				if(!empty($sr_db['cost_price']) || !empty($sr_db['selling_price'])){
	  			    $fresh[$sr_db['department_id']]=1;
	  			    $fresh[$get_rootid[$sr_db['department_id']]]=1;
			    	$fsr_arr[$sr_db['department_id']]['FRESH']['cost_price']+=$sr_db['cost_price'];
			    	$fsr_arr[$sr_db['department_id']]['FRESH']['selling_price']+=$sr_db['selling_price'];

					//$fsku_items[$sr_db['department_id']]['FRESH'][$sr_db['sku_item_id']]['descrip']=1;
					$fsku_items[$sr_db['department_id']]['FRESH'][$sr_db['sku_item_id']]=$sr_db['sku_item_id'];
					$stc_fsku_items[$sr_db['department_id']]['FRESH'][$sr_db['sku_item_id']]=$sr_db['sku_item_id'];
			    	$item_fsr_arr[$sr_db['department_id']]['FRESH'][$sr_db['sku_item_id']]['cost_price']+=$sr_db['cost_price'];
			    	$item_fsr_arr[$sr_db['department_id']]['FRESH'][$sr_db['sku_item_id']]['selling_price']+=$sr_db['selling_price'];
				}
			}

			if(!empty($sr_db['cost_price']) || !empty($sr_db['selling_price'])){
				if ($sr_db['is_fresh']=='no'){
		    		$vendor[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']]['descrip']=1;
			    	$vsr_arr[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']]['cost_price']+=$sr_db['cost_price'];
			    	$vsr_arr[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']]['selling_price']+=$sr_db['selling_price'];
			    	
			    	//$sku_items[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']][$sr_db['sku_item_id']]['descrip']=1;
			    	$sku_items[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']][$sr_db['sku_item_id']]=$sr_db['sku_item_id'];
			    	$stc_sku_items[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']][$sr_db['sku_item_id']]=$sr_db['sku_item_id'];
			    	$item_vsr_arr[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']][$sr_db['sku_item_id']]['cost_price']+=$sr_db['cost_price'];
			    	$item_vsr_arr[$sr_db['department_id']][$sr_vendor_id][$sr_db['sku_type']][$sr_db['sku_item_id']]['selling_price']+=$sr_db['selling_price'];
				}
			}
	    	$num_rows-=1;
		}
		$got_data=true;
	}
	$con_multi->sql_freeresult($sr_query);

	$data_cat['sr']=$sr_arr;
	$data_ven['vsr']=$vsr_arr;
	$data_fre['fsr']=$fsr_arr;
	$data_item_ven['item_vsr']=$item_vsr_arr;
	$data_item_fre['item_fsr']=$item_fsr_arr;

//	$item_ven['vsr']=$item_vsr_arr;
//	$item_fre['fsr']=$item_fsr_arr;

	unset($sr_arr,$vsr_arr,$fsr_arr);
	unset($item_vsr_arr,$item_fsr_arr);

	print "Done\n";
}

function get_adjustment($bid,$adj_start_date,$to_date,&$data_cat,&$data_ven,&$data_fre,&$data_item_ven,&$data_item_fre){
 	global $con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items,$stc_sku_items,$stc_fsku_items;
	//------------------->Adjustment
	print "Adjustment...$adj_start_date";
    $adj_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
		(select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.sku_item_id=si.id and adj.adjustment_date between vsh.from_date and vsh.to_date order by vsh.to_date desc limit 1) as vsh_vendor_id, si.id as sku_item_id,s.vendor_id as s_vendor_id ,c.department_id, s.sku_type,sum(ai.qty * ai.cost) as cost_price, sum(ai.qty*ai.selling_price) as selling_price
	from sku_items si
	left join adjustment_items ai on ai.sku_item_id=si.id and ai.branch_id=$bid
	left join adjustment adj on ai.adjustment_id=adj.id and ai.branch_id=adj.branch_id
	left join sku s on si.sku_id=s.id
	left join category c on s.category_id=c.id
	left join category_cache cc on cc.category_id=c.id and cc.category_id=c.id
	where s.sku_type='OUTRIGHT' and ai.sku_item_id>0  and adj.status=1 and adj.approved=1 and adj.adjustment_date between $adj_start_date and $to_date
	group by si.id,s_vendor_id,vsh_vendor_id";
	
    $adj_query=$con_multi->sql_query($adj_sql);

	if ($con_multi->sql_numrows($adj_query)>0){
		$num_rows=$con_multi->sql_numrows($adj_query);
		print "Total=$num_rows \n";
		while ($adj_db=$con_multi->sql_fetchassoc($adj_query)){
			print "$num_rows...\r";

 			if ($adj_db['vsh_vendor_id'] >0  )    $adj_vendor_id=$adj_db['vsh_vendor_id'];
			else{
				$no_grn_vendor[$adj_db['department_id']][$adj_db['s_vendor_id']][$adj_db['sku_type']]=1;
				$adj_vendor_id = 0;
			}

 			if ($adj_db['is_fresh']=='no'){
		    	$adj_arr[$adj_db['department_id']][$adj_db['sku_type']]['cost_price']+=$adj_db['cost_price'];
		    	$adj_arr[$adj_db['department_id']][$adj_db['sku_type']]['selling_price']+=$adj_db['selling_price'];
			}elseif ($adj_db['is_fresh']=='yes'){
			    if (!empty($adj_db['cost_price']) || !empty($adj_db['selling_price'])){
	  			    $fresh[$adj_db['department_id']]=1;
	  			    $fresh[$get_rootid[$adj_db['department_id']]]=1;
			    	$fadj_arr[$adj_db['department_id']]['FRESH']['cost_price']+=$adj_db['cost_price'];
			    	$fadj_arr[$adj_db['department_id']]['FRESH']['selling_price']+=$adj_db['selling_price'];

					//$fsku_items[$adj_db['department_id']]['FRESH'][$adj_db['sku_item_id']]['descrip']=1;
					$fsku_items[$adj_db['department_id']]['FRESH'][$adj_db['sku_item_id']]=$adj_db['sku_item_id'];
					$stc_fsku_items[$adj_db['department_id']]['FRESH'][$adj_db['sku_item_id']]=$adj_db['sku_item_id'];
			    	$item_fadj_arr[$adj_db['department_id']]['FRESH'][$adj_db['sku_item_id']]['cost_price']+=$adj_db['cost_price'];
			    	$item_fadj_arr[$adj_db['department_id']]['FRESH'][$adj_db['sku_item_id']]['selling_price']+=$adj_db['selling_price'];
				}
			}

			if (!empty($adj_db['cost_price']) || !empty($adj_db['selling_price'])){
	 			if ($adj_db['is_fresh']=='no'){
			    	$vendor[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']]['descrip']=1;
			    	$vadj_arr[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']]['cost_price']+=$adj_db['cost_price'];
			    	$vadj_arr[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']]['selling_price']+=$adj_db['selling_price'];
					
					//$sku_items[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']][$adj_db['sku_item_id']]['descrip']=1;
					$sku_items[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']][$adj_db['sku_item_id']]=$adj_db['sku_item_id'];
					$stc_sku_items[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']][$adj_db['sku_item_id']]=$adj_db['sku_item_id'];
			    	$item_vadj_arr[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']][$adj_db['sku_item_id']]['cost_price']+=$adj_db['cost_price'];
			    	$item_vadj_arr[$adj_db['department_id']][$adj_vendor_id][$adj_db['sku_type']][$adj_db['sku_item_id']]['selling_price']+=$adj_db['selling_price'];
				}

			}
	    	$num_rows-=1;
		}
		$got_data=true;
	}

	$con_multi->sql_freeresult($adj_query);

	$data_cat['adj']=$adj_arr;
	$data_ven['vadj']=$vadj_arr;
	$data_fre['fadj']=$fadj_arr;
	$data_item_ven['item_vadj']=$item_vadj_arr;
	$data_item_fre['item_fadj']=$item_vadj_arr;

//	$item_ven['vadj']=$item_vadj_arr;
//	$item_fre['fadj']=$item_vadj_arr;

	unset($adj_arr,$vadj_arr,$fadj_arr);
	unset($item_vadj_arr,$item_vadj_arr);

	print "Done\n";	
}
	
function get_return_stock($bid,$adj_start_date,$time_date,&$data_cat,&$data_ven,&$data_fre,&$data_item_ven,&$data_item_fre){
 	global $con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items,$stc_sku_items,$stc_fsku_items;
    //------------------->Return Stock
	print "Return Stock...$adj_start_date";
    $rs_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
	gi.vendor_id ,c.department_id, s.sku_type, si.id as sku_item_id, sum(gi.qty*gi.cost) as cost_price, sum(gi.qty*gi.selling_price) as selling_price, count(if(gi.batchno=0, gi.id, null)) as not_allow_checkout
	from sku_items si
	left join gra_items gi on gi.sku_item_id=si.id and gi.branch_id=$bid
	left join gra on gi.gra_id=gra.id and gi.branch_id=gra.branch_id
	left join sku s on si.sku_id=s.id
	left join category c on s.category_id=c.id
	left join category_cache cc on cc.category_id=c.id
	where s.sku_type='OUTRIGHT' and gi.sku_item_id>0 and gi.checkout=1 and gra.returned=1 and gra.status<1 and gra.return_timestamp between $adj_start_date and $time_date
	group by si.id,gi.vendor_id,c.department_id,s.sku_type,is_fresh
	having not_allow_checkout=0
	";
	//print $rs_sql;

    $rs_query=$con_multi->sql_query($rs_sql);

	if ($con_multi->sql_numrows($rs_query)>0){
		$num_rows=$con_multi->sql_numrows($rs_query);
		print "Total=$num_rows \n";
				
		while ($rs_db=$con_multi->sql_fetchassoc($rs_query)){
			print "$num_rows...\r";

 			$rs_vendor_id=$rs_db['vendor_id'];

 			if ($rs_db['is_fresh']=='no'){
		    	$rs_arr[$rs_db['department_id']][$rs_db['sku_type']]['cost_price']+=$rs_db['cost_price'];
		    	$rs_arr[$rs_db['department_id']][$rs_db['sku_type']]['selling_price']+=$rs_db['selling_price'];
			}elseif ($rs_db['is_fresh']=='yes'){
			    if(!empty($rs_db['cost_price']) || !empty($rs_db['selling_price'])){
	 			    $fresh[$rs_db['department_id']]=1;
	 			    $fresh[$get_rootid[$rs_db['department_id']]]=1;
			    	$frs_arr[$rs_db['department_id']]['FRESH']['cost_price']+=$rs_db['cost_price'];
			    	$frs_arr[$rs_db['department_id']]['FRESH']['selling_price']+=$rs_db['selling_price'];
			    	
			    	//$fsku_items[$rs_db['department_id']]['FRESH'][$rs_db['sku_item_id']]['descrip'] = 1;
			    	$fsku_items[$rs_db['department_id']]['FRESH'][$rs_db['sku_item_id']] = $rs_db['sku_item_id'];
			    	$stc_fsku_items[$rs_db['department_id']]['FRESH'][$rs_db['sku_item_id']] = $rs_db['sku_item_id'];
			    	$item_frs_arr[$rs_db['department_id']]['FRESH'][$rs_db['sku_item_id']]['cost_price']+=$rs_db['cost_price'];
			    	$item_frs_arr[$rs_db['department_id']]['FRESH'][$rs_db['sku_item_id']]['selling_price']+=$rs_db['selling_price'];
				}
			}

			if(!empty($rs_db['cost_price']) || !empty($rs_db['selling_price'])){
	 			if ($rs_db['is_fresh']=='no'){
			    	$vendor[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']]['descrip']=1;
			    	$vrs_arr[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']]['cost_price']+=$rs_db['cost_price'];
			    	$vrs_arr[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']]['selling_price']+=$rs_db['selling_price'];

					//$sku_items[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']][$rs_db['sku_item_id']]['descrip'] = 1;
					$sku_items[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']][$rs_db['sku_item_id']] = $rs_db['sku_item_id'];
					$stc_sku_items[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']][$rs_db['sku_item_id']] = $rs_db['sku_item_id'];
					$item_vrs_arr[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']][$rs_db['sku_item_id']]['cost_price']+=$rs_db['cost_price'];
			    	$item_vrs_arr[$rs_db['department_id']][$rs_vendor_id][$rs_db['sku_type']][$rs_db['sku_item_id']]['selling_price']+=$rs_db['selling_price'];

				}
			}
	    	$num_rows-=1;
		}
		$got_data=true;
	}
	$con_multi->sql_freeresult($rs_query);

	$data_cat['rs']=$rs_arr;
	$data_ven['vrs']=$vrs_arr;
	$data_fre['frs']=$frs_arr;

	$data_item_ven['item_vrs']=$item_vrs_arr;
	$data_item_fre['item_frs']=$item_frs_arr;

	unset($rs_arr,$vrs_arr,$frs_arr);
	unset($item_vrs_arr,$item_frs_arr);

	print "Done\n";
}

function get_promotion_sales_amount($bid,$adj_start_date,$to_date,&$data_cat,&$data_ven,&$data_fre,&$data_item_ven,&$data_item_fre){
 	global $con_multi,$no_grn_vendor,$vendor,$fresh,$got_data,$sku_items,$fsku_items,$stc_sku_items,$stc_fsku_items;
	$filter_date=" and pi.date between $adj_start_date and $to_date";
	//and pi.year=$year and pi.month=$month	

    //------------------->Promotion Amount AND Actual Sales AND Price Change Amount
	print "Promotion Amount AND Actual Sales AND Price Change Amount...$adj_start_date";
	$pa_sql="select if(s.is_fresh_market='inherit',cc.is_fresh_market,s.is_fresh_market) as is_fresh,
	(select vsh.vendor_id from vendor_sku_history_b".mi($bid)." vsh where vsh.sku_item_id=si.id and pi.date between vsh.from_date and vsh.to_date order by vsh.to_date desc limit 1) as vsh_vendor_id, s.vendor_id as s_vendor_id ,c.department_id, s.sku_type, pi.date, pi.sku_item_id,
	pi.qty, pi.cost as cost_price,(pi.disc_amt+pi.disc_amt2) as discount,
(pi.amount) as selling_price, if(pi.disc_amt<>0 or pi.disc_amt2<>0, 1, 0) as is_promo
from sku_items si
left join sku_items_sales_cache_b$bid pi on pi.sku_item_id=si.id
left join sku s on si.sku_id=s.id
left join category c on s.category_id=c.id
left join category_cache cc on s.category_id=cc.category_id and cc.category_id=c.id
where pi.sku_item_id>0 $filter_date";
	//print $pa_sql;

    $pa_query=$con_multi->sql_query($pa_sql);

	if ($con_multi->sql_numrows($pa_query)>0){
		$num_rows=$con_multi->sql_numrows($pa_query);
		print "Total=$num_rows \n";

		while ($pa_db=$con_multi->sql_fetchassoc($pa_query)){
			print "$num_rows...\r";
			if ($pa_db['sku_type']=='OUTRIGHT'){
	 			if ($pa_db['vsh_vendor_id'] >0)    $pa_vendor_id=$pa_db['vsh_vendor_id'];
				else{
	                $no_grn_vendor[$pa_db['department_id']][$pa_db['s_vendor_id']][$pa_db['sku_type']]=1;
					$pa_vendor_id = 0;
				}
			}else{
			    //CONSIGNMENT
                $pa_vendor_id=$pa_db['s_vendor_id'];
			}

	    	if ($pa_db['is_promo']){
				//Promotion AMount
	 			if ($pa_db['is_fresh']=='no'){
	 			    if(!empty($pa_db['discount'])){
						$pa_arr[$pa_db['department_id']][$pa_db['sku_type']]['selling_price']+=$pa_db['discount'];
 			    		$vpa_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['selling_price']+=$pa_db['discount'];
 			    		
 			    		//$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['descrip']=1;
 			    		$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
 			    		$stc_sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
 			    		$item_vpa_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['selling_price']+=$pa_db['discount'];
 			    		
					}
				}elseif ($pa_db['is_fresh']=='yes'){
					if(!empty($pa_db['discount'])){
		 			    $fresh[$pa_db['department_id']]=1;
		 			    $fresh[$get_rootid[$pa_db['department_id']]]=1;
						$fpa_arr[$pa_db['department_id']]['FRESH']['selling_price']+=$pa_db['discount'];

 			    		//$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['descrip']=1;
 			    		$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
 			    		$stc_fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
						$item_fpa_arr[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['selling_price']+=$pa_db['discount'];
					}
				}
			}else{
				//Price change amount
				$pca_sql="select if(pi.selling_price is null,gi.selling_price,pi.selling_price) as selling_price
							from grn_items gi
							left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
							left join grr on grr.id=grn.grr_id and grr.branch_id=grn.branch_id
							left join grr_items grri on grri.grr_id=grr.id and grri.id=grn.grr_item_id and grri.branch_id=grr.branch_id
							left join po on po.po_no=grri.doc_no and grri.type='PO' and  po.status=1 and po.approved=1 and po.delivered=1
 							left join po_items pi on pi.po_id=po.id and pi.branch_id=po.branch_id
								and pi.sku_item_id=gi.sku_item_id and pi.id=gi.po_item_id
							where gi.sku_item_id=$pa_db[sku_item_id] and gi.branch_id=$bid 
								and grr.rcv_date<".ms($pa_db['date'])." and grn.status=1 and grn.approved=1	and grr.status=1
							order by grr.id desc,grr.rcv_date desc limit 1";

		    	$pca_query=$con_multi->sql_query($pca_sql);
		    	// fix forget reset selling to 0 while looping
		    	$po_selling=0;
		    	if ($con_multi->sql_numrows($pca_query)>0){
			    	$pca_db=$con_multi->sql_fetchassoc($pca_query);
					$po_selling=$pca_db['selling_price'];
				}
	   			$con_multi->sql_freeresult($pca_query);

 			    $dif_selling = $po_selling * $pa_db['qty'];
				$total_selling = $dif_selling - $pa_db['selling_price'];
				//print $dif_selling."vs".$pa_db['selling_price']."\n";

	 			if ($pa_db['is_fresh']=='no'){
					if(!empty($total_selling)){
						$pca_arr[$pa_db['department_id']][$pa_db['sku_type']]['selling_price']+=$total_selling;
                        $vpca_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['selling_price']+=$total_selling;
  
   			    		//$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['descrip']=1;
						$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
						$stc_sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];                      
                        $item_vpca_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['selling_price']+=$total_selling;
                    }
				}
				elseif ($pa_db['is_fresh']=='yes'){
					if(!empty($total_selling)){
		 			    $fresh[$pa_db['department_id']]=1;
		 			    $fresh[$get_rootid[$pa_db['department_id']]]=1;
						$fpca_arr[$pa_db['department_id']]['FRESH']['selling_price']+=$total_selling;
						
 			    		//$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['descrip']=1;
 			    		$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
 			    		$stc_fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
						$item_fpca_arr[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['selling_price']+=$total_selling;
					}
				}
			}

			//Actual Sales
 			if ($pa_db['is_fresh']=='no'){
 			    if ($pa_db['sku_type'] == 'OUTRIGHT'){
		    		$as_arr[$pa_db['department_id']][$pa_db['sku_type']]['cost_price']+=$pa_db['cost_price'];
		  		  	$as_arr[$pa_db['department_id']][$pa_db['sku_type']]['selling_price']+=$pa_db['selling_price'];
	  		  	}elseif ($pa_db['sku_type'] == 'CONSIGN'){
	  		  	    //consign items get selling price only
		  		  	$as_arr[$pa_db['department_id']][$pa_db['sku_type']]['selling_price']+=$pa_db['selling_price'];
				}
			}elseif ($pa_db['is_fresh']=='yes'){
				if( !empty($pa_db['cost_price']) || !empty($pa_db['selling_price'])){
	 			    $fresh[$pa_db['department_id']]=1;
	 			    $fresh[$get_rootid[$pa_db['department_id']]]=1;
		  		  	$fas_arr[$pa_db['department_id']]['FRESH']['cost_price']+=$pa_db['cost_price'];
		  		  	$fas_arr[$pa_db['department_id']]['FRESH']['selling_price']+=$pa_db['selling_price'];

		    		//$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['descrip']=1;
		    		$fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
		    		$stc_fsku_items[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
		  		  	$item_fas_arr[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['cost_price']+=$pa_db['cost_price'];
		  		  	$item_fas_arr[$pa_db['department_id']]['FRESH'][$pa_db['sku_item_id']]['selling_price']+=$pa_db['selling_price'];
				}
			}

			//vendor side
			if(!empty($pa_db['cost_price']) || !empty($pa_db['selling_price'])){
	 			if ($pa_db['is_fresh']=='no'){
			    	$vendor[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['descrip']=1;
			    	//$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['descrip']=1;
			    	$sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
			    	$stc_sku_items[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]=$pa_db['sku_item_id'];
	 			    if ($pa_db['sku_type'] == 'OUTRIGHT'){
						$vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['cost_price']+=$pa_db['cost_price'];
			  		  	$vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['selling_price']+=$pa_db['selling_price'];

						$item_vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['cost_price']+=$pa_db['cost_price'];
			  		  	$item_vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['selling_price']+=$pa_db['selling_price'];
	  		  		}elseif ($pa_db['sku_type'] == 'CONSIGN'){

						$vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']]['selling_price']+=$pa_db['selling_price'];
						
						$item_vas_arr[$pa_db['department_id']][$pa_vendor_id][$pa_db['sku_type']][$pa_db['sku_item_id']]['selling_price']+=$pa_db['selling_price'];
              		}
				}
			}
	    	$num_rows-=1;
		}
		$got_data=true;
	}
	$con_multi->sql_freeresult($pa_query);

	$data_cat['acs']=$as_arr;
	$data_ven['vacs']=$vas_arr;
	$data_fre['facs']=$fas_arr;
	unset($as_arr,$vas_arr,$fas_arr);

	$data_item_ven['item_vacs']=$item_vas_arr;
	$data_item_fre['item_facs']=$item_fas_arr;
	unset($item_vas_arr,$item_fas_arr);

	$data_cat['pa']=$pa_arr;
	$data_ven['vpa']=$vpa_arr;
	$data_fre['fpa']=$fpa_arr;
	unset($pa_arr,$vpa_arr,$fpa_arr);

	$data_item_ven['item_vpa']=$item_vpa_arr;
	$data_item_fre['item_fpa']=$item_fpa_arr;
	unset($item_vpa_arr,$item_fpa_arr);

	$data_cat['pca']=$pca_arr;
	$data_ven['vpca']=$vpca_arr;
	$data_fre['fpca']=$fpca_arr;
	unset($pca_arr,$vpca_arr,$fpca_arr);

	$data_item_ven['item_vpca']=$item_vpca_arr;
	$data_item_fre['item_fpca']=$item_fpca_arr;
	unset($item_vpca_arr,$item_fpca_arr);
	print "Done\n";
}

function add_to_data_from_source(&$main_data,$source_data){
	//print_r($source_data);
	//sr,adj
	//rs,acs, pa, pca
	
	foreach ($source_data as $id => $ctother){
		if (preg_match("/sr|adj/", $id))	$status="minus";
		else	$status="plus";
		
		foreach ($ctother as $cid => $tother){ //category id
			foreach ($tother as $type => $other){	//sku type or vendor id
				foreach ($other as $t_cost_selling => &$cost_selling){ //sku type or price
					if (is_array($cost_selling)){
						foreach ($cost_selling as $t_cost_or_selling => $cost_or_selling){	//sku item id or price	
							if (is_array($cost_or_selling)){
								foreach ($cost_or_selling as $item_t_cost_or_selling => $item_cost_or_selling){
									//for item only
									$main_data[$id][$cid][$type][$t_cost_selling][$t_cost_or_selling][$item_t_cost_or_selling]+=$item_cost_or_selling;

									if ($status=='plus')
										$tmp_data[$cid][$type][$t_cost_selling][$t_cost_or_selling][$item_t_cost_or_selling]+=$item_cost_or_selling;
									else
										$tmp_data[$cid][$type][$t_cost_selling][$t_cost_or_selling][$item_t_cost_or_selling]-=$item_cost_or_selling;
								}
							}else{
								//add main data n source data

								$main_data[$id][$cid][$type][$t_cost_selling][$t_cost_or_selling]+=$cost_or_selling;
	
								//get total of a category
								if ($status=='plus'){
									$tmp_data[$cid][$type][$t_cost_selling][$t_cost_or_selling]+=$cost_or_selling;
								}else{
									$tmp_data[$cid][$type][$t_cost_selling][$t_cost_or_selling]-=$cost_or_selling;
								}
							}
						}
						
					}else{

						$main_data[$id][$cid][$type][$t_cost_selling]+=$cost_selling;

						if ($status=='plus'){
							$tmp_data[$cid][$type][$t_cost_selling]+=$cost_selling;
						}else{
							$tmp_data[$cid][$type][$t_cost_selling]-=$cost_selling;
						}
					}
				}
			}
		}
		
		//store into new data
		$main_data["b_stc_".$id] = $ctother;
	}

	$main_data["stc_opening_variance"] = $tmp_data; 
	
	unset($tmp_data);

}

function calculate_closing_stock($form,$file_name,$use_tmp_file=false){
	global $con;
	
	print "====Recalculate closing stock use $file_name====\n";

    $bid = mi($form['bid']);
	$year=mi($form['year']);
	$month=mi($form['month']);

	$cos_month = $month - 1;
	
	//date checking
	if (!$cos_month){
        $cos_year = $year - 1;
        $cos_month = 12;
	}else{
    	$cos_year = $year;
	}
	
	if ($use_tmp_file)	$tmp_file="tmp_";

/*
	//get current month finalized	
	$fin_query=$con->sql_query("select finalized from csa_report where year=$year and month=$month and branch_id=$bid and (finalized is not null or finalized !='')");
	if ($con->sql_numrows($fin_query)<=0){
		print "$file_name haven't finalized";
		return;
	}else{
		while ($fin_db=$con->sql_fetchassoc($fin_query)){
			$finalized=unserialize($fin_db['finalized']);
			if ($finalized){
				foreach ($finalized as $rid => $other){
					foreach ($other as $cid => $dummy){
						$save['finalized'][$cid]=$dummy;
					}
				}
			}
		}
	}
	
	$con->sql_freeresult($fin_query);
*/
	//get previous closing stock as opening stock if finalized
	$cos_query=$con->sql_query("select * from ".$tmp_file."csa_report where year=$cos_year and month=$cos_month and branch_id=$bid and (closing_notf_out_cost is not null or closing_notf_out_selling is not null)");

	if ($con->sql_numrows($cos_query)<=0){
		print "$file_name do not have previous month closing stock\n";
	}else{	
		while ($cos_db=$con->sql_fetchassoc($cos_query)){
		    $save['closing_notf_out_cost']=unserialize($cos_db['closing_notf_out_cost']);
		    $save['closing_notf_out_selling']=unserialize($cos_db['closing_notf_out_selling']);
		    //$save['closing_fresh_selling']=unserialize($cos_db['closing_fresh_selling']);
		}
		
	}

	$con->sql_freeresult($cos_query);

	//get input data from database====> Check this data got saved or not
	$data_id=$con->sql_query("select * from ".$tmp_file."csa_report where year=$year and month=$month and branch_id=$bid and (other_notf_out_selling is not null or other_notf_cons_selling is not null or other_fresh_selling is not null)");
	if ($con->sql_numrows($data_id)<=0){
		print "$file_name do not have save data\n";	
	}else{
		$in=$con->sql_fetchassoc($data_id);
			
		//OUTRIGHT
	  	$save['grn_notf_out_cost']=unserialize($in['grn_notf_out_cost']);
	  	$save['grn_notf_out_selling']=unserialize($in['grn_notf_out_selling']);
//	    $save['rebate_notf_out_selling']=unserialize($in['rebate_notf_out_selling']);
	  	$save['idt_notf_out_cost']=unserialize($in['idt_notf_out_cost']);
	    $save['idt_notf_out_selling']=unserialize($in['idt_notf_out_selling']);
	
		//FRESH
		$save['grn_fresh_selling']=unserialize($in['grn_fresh_selling']);
//		$save['rebate_fresh_selling']=unserialize($in['rebate_fresh_selling']);
		$save['idt_fresh_selling']=unserialize($in['idt_fresh_selling']);
		$save['other_fresh_selling']=unserialize($in['other_fresh_selling']);
		$save['closing_fresh_selling']=unserialize($in['closing_fresh_selling']);	
	}
	$con->sql_freeresult($data_id);

	$file_data = file($file_name);
	$data_cat=unserialize($file_data[1]);    //department data
	$data_ven=unserialize($file_data[2]);    //vendor data
	$data_fre=unserialize($file_data[3]);   //Is fresh department
	$stc_date=$file_data[6];   //Is fresh department

	//system opening stock
	$vos_arr=$data_ven['vos'];
	$fos_arr=$data_fre['fos'];
		
	//opening stock before
	$vcov_arr=$data_ven['stc_opening_variance'];
	$fcov_arr=$data_fre['stc_opening_variance'];
	
	//stock receive
	$vsr_arr=$data_ven['vsr'];
	$fsr_arr=$data_fre['fsr'];
	
	//adjustment
	$vadj_arr=$data_ven['vadj'];
	$fadj_arr=$data_fre['fadj'];
	
	//stock take variance
	$vst_arr=$data_ven['vstv'];
	$fst_arr=$data_fre['fstv'];
	
	//return stock
	$vrs_arr=$data_ven['vrs'];
	$frs_arr=$data_fre['frs'];
	
	//promotion, price change and actual sales
	$vpa_arr=$data_ven['vpa'];
	$fpa_arr=$data_fre['fpa'];
	
	$vpca_arr=$data_ven['vpca'];
	$fpca_arr=$data_fre['fpca'];
	
	$vacs_arr=$data_ven['vacs'];
	$facs_arr=$data_fre['facs'];
	
  	$vendors=$data_ven['vendor'];
	$fresh=$data_fre['fresh'];
	
	unset($data_cat);
	unset($data_ven);
	unset($data_fre);
	unset($file_data);
	
	//find missing vendor.
	
	if ($save['closing_notf_out_cost'] || $save['closing_notf_out_selling']){
	    if ($save['closing_notf_out_cost']){
			foreach ($save['closing_notf_out_cost'] as $cid => $closing){
				foreach ($closing as $vid => $other ){
					if (!$vendors[$cid][$vid]['OUTRIGHT']['descrip']){
						$vendors[$cid][$vid]['OUTRIGHT']['descrip']=1;
					}
				}
			}
		}
	}

	//starting calculation closing stock
	foreach ($vendors as $cid => $vtd){
//		if (!$save['finalized'][$cid])	continue;
		foreach ($vtd as $vid => $dummy){
			//OUTRIGHT
				$type='OUTRIGHT';
				//cost
				$stv['stv']=$st_arr[$cid][$type];
				
				//opening stock
			    $cc['cos']=mf($save['closing_notf_out_cost'][$cid][$vid]['O']['cp']);
			    
			    if ($stc_date || $vst_arr[$cid][$vid][$type]['cost_price']){
				    //stock take variance
					$cc['stv']=mf($vst_arr[$cid][$vid][$type]['cost_price']);
				
					//stock check
					$cc['stc']=$cc['cos']+$cc['stv'];
					
					// stock before stock check
					$cc['cov']=mf($vcov_arr[$cid][$vid][$type]['cost_price']);
					
					$cc['aos'] = $cc['stc']+$cc['cov'];
				}else{
					$cc['aos'] = $cc['cos'];  
				}

				$cc['sr']=mf($vsr_arr[$cid][$vid][$type]['cost_price']);
			  	$cc['grn']=mf($save['grn_notf_out_cost'][$cid][$vid]['O']['cp']);
				$cc['adj']=mf($vadj_arr[$cid][$vid][$type]['cost_price']);		
				  					
				$cc['as']=$cc['aos']+$cc['sr']+$cc['grn']+$cc['adj'];

			  	$cc['idt']=mf($save['idt_notf_out_cost'][$cid][$vid]['O']['cp']);
				$cc['rs']=mf($vrs_arr[$cid][$vid][$type]['cost_price']);
				$cc['acs']=mf($vacs_arr[$cid][$vid][$type]['cost_price']);
				
				$update['closing_notf_out_cost'][$cid][$vid]['O']['cp']=strval($cc['as']-$cc['rs']+$cc['idt']-$cc['acs']);

				unset($cc);
				
				//selling
			    $ss['cos']=mf($save['closing_notf_out_selling'][$cid][$vid]['O']['sp']);
			    
				if ($stc_date || $vst_arr[$cid][$vid][$type]['selling_price']){
					$ss['stv']=mf($vst_arr[$cid][$vid][$type]['selling_price']);
					
					$ss['stc']=$ss['cos']+$ss['stv'];
				
					$ss['cov']=mf($vcov_arr[$cid][$vid][$type]['selling_price']);
					
					$ss['aos'] = $ss['stc'] + $ss['cov']; 
				}else{
					$ss['aos'] = $ss['cos'];
				}
				
			  	$ss['grn']=mf($save['grn_notf_out_selling'][$cid][$vid]['O']['sp']);
				$ss['sr']=mf($vsr_arr[$cid][$vid][$type]['selling_price']);				
				$ss['adj']=mf($vadj_arr[$cid][$vid][$type]['selling_price']);
				
				//actual stock
				$ss['as']=$ss['aos']+$ss['sr']+$ss['grn']+$ss['adj'];

				//inter dept transfer
			    $ss['idt']=mf($save['idt_notf_out_selling'][$cid][$vid]['O']['sp']);
//			    $ss['rebate']=mf($save['rebate_notf_out_selling'][$cid][$vid]['O']['sp']);
				//return stock
				$ss['rs']=mf($vrs_arr[$cid][$vid][$type]['selling_price']);				
				//promotion amount
				$ss['pa']=mf($vpa_arr[$cid][$vid][$type]['selling_price']);
				//price change amount
				$ss['pca']=mf($vpca_arr[$cid][$vid][$type]['selling_price']);
				//actual sales
				$ss['acs']=mf($vacs_arr[$cid][$vid][$type]['selling_price']);

				$update['closing_notf_out_selling'][$cid][$vid]['O']['sp']=strval($ss['as']-$ss['rs']+$ss['idt']-$ss['pa']-$ss['pca']-$ss['acs']+$ss['rebate']);		

				unset($ss);
				unset($stv);
			//CONSIGNMENT
				//no closing stock for CONSIGNMENT
		}
	}
	
	$upd['closing_notf_out_cost']=serialize($update['closing_notf_out_cost']);
	$upd['closing_notf_out_selling']=serialize($update['closing_notf_out_selling']);
	unset($update);
		
	$con->sql_query("update ".$tmp_file."csa_report set ".mysql_update_by_field($upd)." where year=$year and month=$month and branch_id=$bid");

	print "Finish calculation\n";

	unset($save);
	unset($vendors);	
}

function check_date_generate($now_year,$now_month,$starting_date){
	$generate_r=false;
	
	list($s_year,$s_month,$s_day)=explode("-",$starting_date);

	// 1st check date must more than config starting date
    $yy=$now_year-$s_year;
	if ($yy >= 1)   $generate_r = true;
	elseif ($yy == 0){
		if ($s_month <= $now_month)	$generate_r = true;
	}
	
	if (!$generate_r)	return;

	$now_timestamp=strtotime($now_year."-".$now_month."-01");

	//2nd after 4th of month, no more regenerate of previous or later month
	$current_timestamp = time();
	$server_timestamp = strtotime(date("Y",$current_timestamp)."-".date("m",$current_timestamp)."-01");
	$server_day = intval(date("d",$current_timestamp));
	
	if ($server_day > 4){
		//more than 4th cannot regenerate previous month, regenrate current month only	
		if ($now_timestamp != $server_timestamp){
			$generate_r = false;
			print "\nOver 4th cutoff day liao.\n";
		}
	}else{
		//can regenerate 1 month before nia
		$previous_month_timestamp=strtotime("-1 month",$current_timestamp);
		$server2_timestamp = strtotime(date("Y",$previous_month_timestamp)."-".date("m",$previous_month_timestamp)."-01");

		if ($now_timestamp != $server_timestamp && $now_timestamp != $server2_timestamp){
			$generate_r = false;
			print "\nCannot regenerate over 1 month.\n";
		}
	}
	return $generate_r;
}

function is_cached($directory)
{
    if (file_exists($directory))	return true;
	else	return false;
}

function save_opening_to_closing($bid,$year,$month, $vos_arr, $fos_arr,$tmp_file=''){
	global $con,$LANG, $config;
	$cos_month = $month - 1;

	if (!$config['csa_start_opening']){
		print "Missing config for starting opening";
		return;
	}

	list($y,$m,$d)=explode("-",$config['csa_start_opening']);
	if ($year != $y || $month != $m) return;
	
	//date checking
	if (!$cos_month){
          $cos_year = $year - 1;
          $cos_month = 12;
	}else{
          $cos_year = $year;
	}

    $data['branch_id']=$bid;
    $data['year']=$cos_year;
    $data['month']=$cos_month;
/*
	$cos_sql = "select * from csa_report where branch_id=$bid and year=$cos_year and month=$cos_month and start_opening=1";
    $cos_query=$con->sql_query($cos_sql,false,false);
	//if it is the starting point, dun replace the data.
	if ($con->sql_numrows()>0)  return;
*/	
	foreach ($vos_arr as $dept_id => $vs_cs){
	    foreach ($vs_cs as $vendor_id => $other ){
              $save['closing_notf_out_cost'][$dept_id][$vendor_id]['O']['cp'] = strval($other['OUTRIGHT']['cost_price']);
              $save['closing_notf_out_selling'][$dept_id][$vendor_id]['O']['sp'] = strval($other['OUTRIGHT']['selling_price']);
		}
	}

	foreach ($fos_arr as $dept_id => $fclosing){
        $save['closing_fresh_selling'][$dept_id]['F']['sp'] = strval($fclosing['FRESH']['selling_price']);
	}

	$data['closing_notf_out_cost']=serialize($save['closing_notf_out_cost']);
	$data['closing_notf_out_selling']=serialize($save['closing_notf_out_selling']);
	$data['closing_fresh_selling']=serialize($save['closing_fresh_selling']);

	$con->sql_query("select * from category group by department_id");
	while ($r=$con->sql_fetchassoc()){
		$confirm_all[$r['root_id']][$r['department_id']]=1;
	}

    $ser_conf = serialize($confirm_all);

	$data['confirmed']=$ser_conf;
	$data['finalized']=$ser_conf;
	$data['reviewed']=$ser_conf;
	$data['start_opening']=1;

	$con->sql_query("update ".$tmp_file."csa_report set start_opening=0 where start_opening=1 and branch_id=$bid");

	$con->sql_query("insert into ".$tmp_file."csa_report " . mysql_insert_by_field($data) ." on duplicate key update
					".mysql_update_by_field($data));

	print $LANG['CSA_STARTING_OPENING']."\n";
}
	
print "Finish....\n";

print "Ending memory:".memory_get_usage()."\n";

exit;

// find Generate Report into cache
?>
