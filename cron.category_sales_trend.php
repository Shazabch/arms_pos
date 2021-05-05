<?php
/*
12/6/2019 4:09 PM William
- Added new cron "Category Sales Trend"

1/22/2020 5:16 PM Andy
- Increase memory_limit to 2G.

1/23/2020 9:01 AM William
- Reduce used memory of php.
*/

define('TERMINAL',1);
include("include/common.php");
ob_end_clean();
ini_set('memory_limit', '2G');
set_time_limit(0);

$argv = $_SERVER['argv'];
$CRON_CATEGORY_SALES_TREND = new CRON_CATEGORY_SALES_TREND();
$CRON_CATEGORY_SALES_TREND->start();

class CRON_CATEGORY_SALES_TREND{
	var $b_list = array();
	var $fp_path = '';
	var $calculate_month = 12;
	
	function __construct(){
		$this->fp_path = dirname(__FILE__)."/".basename(__FILE__, '.php').".running";
		// use this prevent wrong "include" path
		
		$this->fp = fopen($this->fp_path, "w");
		chmod($this->fp_path, 0777);
		
		$this->mark_start_process();
	}
	
	function __destruct(){
		$this->mark_close_process();
	}
	
	private function mark_start_process(){
		if(!is_writable(dirname($this->fp_path))){
			print "The folder '".dirname($this->fp_path)."' permission not allow this process to be run, please contact system admin.\n";
			exit;
		}
		
		if(!flock($this->fp, LOCK_EX | LOCK_NB)){
			print "Other process is running, please wait for them to finish.\n";
			exit;
		}
	}
	
	private function mark_close_process(){
		flock($this->fp, LOCK_UN);
	}

	function filter_argv(){
		global $argv, $con, $config;
		
		$dummy = array_shift($argv);
		$this->b_list = array();
		
		while($cmd = array_shift($argv)){
			list($cmd_head, $cmd_value) = explode("=", $cmd, 2);
			if($cmd_head == "-branch"){
				$branch_filter = 'where active=1';
				if($cmd_value == 'all'){
					if(!$config['single_server_mode']){
						$bcode = BRANCH_CODE;
						$branch_filter .= ' and code='.ms($bcode);
					}
				}else{
					$bcode_list = array_map("ms", explode(",", trim($cmd_value)));
					$branch_filter .= ' and code in ('.join(",", $bcode_list).")";
				}

				$con->sql_query("select id,code from branch $branch_filter order by sequence,code");
				while($r = $con->sql_fetchassoc()){
					$this->b_list[] = $r;
				}
				$con->sql_freeresult();
			}elseif($cmd_head == "-month"){
				$month = mi($cmd_value);
				$this->calculate_month = $month;
			}else{
				print "Unknown command $cmd\n";
				exit;
			}
		}
	}
	
	function check_argv(){
		if(!$this->b_list)	die("Branch not found.\n");
	}
	
	function start(){
		print "Start\n";
		$this->filter_argv();
		$this->check_argv();
		$this->category_sales_trend_process();
	}
	
	function category_sales_trend_process(){
		foreach($this->b_list as $b){
			$this->monthly_calculate($b, $this->calculate_month);
		}
		print "Done.\n";
	}
	
	function monthly_calculate($b, $calculate_month){
		print "Branch: ".$b['code']."\n";
		print "Branch ID: ".$b['id']."\n";
		print "\n";
		
		for($m=1;$m <= $calculate_month;$m++){
			$this->category_sales_trend_by_branch($b, $m);
		}		
	}
	
	private function category_sales_trend_by_branch($b, $calculate_month){
		global $con;
		
		$bid = mi($b['id']);
		
		$con->sql_begin_transaction();
		$con->sql_query("delete from category_sales_trend_cache where branch_id=$bid and recent_month = $calculate_month");
		
		$from_Date=date('Y-m-d', strtotime("-".$calculate_month." month"));
		$to_Date=date('Y-m-d', strtotime("-1 day",strtotime("-".($calculate_month - 1)." month"))); 
		$month_to = $month_from = $category_sales_trend = array();
		//print "$calculate_month, from $from_Date to $to_Date\n";return;
		
		$q2 = $con->sql_query($qry = "select cscb.*, category.level, category.root_id, category.tree_str from category_sales_cache_b".$bid." cscb 
		left join category on cscb.category_id=category.id
		where cscb.date between ".ms($from_Date)." and ".ms($to_Date));
		$total_row = $con->sql_numrows($q2);
		if($total_row > 0){
			//get all category sales cache branch data 
			while($r2 = $con->sql_fetchassoc($q2)){
				$category_sales_trend[$r2['category_id']][] = $r2;
			}
			
			$upd = $category_tree = array();			
			foreach($category_sales_trend as $category_id=>$item_list){
				foreach($item_list as $key=> $val){
					//sum same category id and same recent month 
					$upd[$category_id]['category_id'] = $category_sales_trend[$category_id][$key]['category_id'];
					$upd[$category_id]['branch_id'] = $bid;
					$upd[$category_id]['recent_month'] = $calculate_month;
					$upd[$category_id]['qty'] += $category_sales_trend[$category_id][$key]['qty'];
					$upd[$category_id]['amount'] += $category_sales_trend[$category_id][$key]['amount'];
					$upd[$category_id]['cost'] += $category_sales_trend[$category_id][$key]['cost'];
					
					$category_tree_list = str_replace(")(", ",", $category_sales_trend[$category_id][$key]['tree_str']);	
					$remove_word = array("(", ")");
					$category_tree[$category_id] = explode(",",str_replace($remove_word, "",$category_tree_list));
					// get parent category_id
					foreach($category_tree[$category_id] as $key2=>$category_tree_id){
						if($category_tree_id != 0){
							//sum same parent category id and same recent month 
							$upd[$category_tree_id]['category_id'] = $category_tree_id;
							$upd[$category_tree_id]['branch_id'] = $bid;
							$upd[$category_tree_id]['recent_month'] = $calculate_month;
							$upd[$category_tree_id]['qty'] += $category_sales_trend[$category_id][$key]['qty'];
							$upd[$category_tree_id]['amount'] += $category_sales_trend[$category_id][$key]['amount'];
							$upd[$category_tree_id]['cost'] += $category_sales_trend[$category_id][$key]['cost'];
						}
					}
				}
			}			
			
			//insert new data to category_sales_trend_cache
			foreach($upd as $category_list=> $value){
				$con->sql_query("insert into category_sales_trend_cache".mysql_insert_by_field($value));
			}
			
			unset($category_sales_trend);
		}
		$con->sql_freeresult($q2);
		$con->sql_commit();
	}
}
?>