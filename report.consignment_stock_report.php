<?php
/*
1/6/2010 4:51:49 PM Andy
- Add config 'ci_use_split_artno' to check whether split artno

1/22/2010 1:43:51 PM Andy
- change report to use stock_balance table, no longer use stock_closing

9/23/2010 5:01:06 PM Andy
- Add group data by branch group.
- Price type, selling and cost will show using hq if group by branch.

9/24/2010 6:47:44 PM yinsee
- fuck Andy ;p
- SQL error when tick hide-zero-qty
- size column show full artno
- add secondary sorting (if selected sort order same, will sort by Artno)

1/18/2011 6:35:25 PM Alex
- change use report_server

2/8/2011 11:01:36 AM Andy
- Fix report always show HQ cost & selling no matter choose branch group or single branch.
- Add show selected year & month in report title.

5/12/2011 12:04:33 PM Alex
- add use hq cost only

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.
*/
include("include/common.php");
//$con = new sql_db('cutemaree.dyndns.org:4001','arms','990506','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class Consignment_HQ_Stock_Report extends Report
{
	var $branch_id_list = array();
	
	function run($bid){
		global $con, $smarty, $config, $con_multi;
		
		$filter = $this->filter;
		if($this->hide_zero){
			//$filter[] = 'sc.qty<>0';
			$filter .= ' and sb.qty<>0';
		}

		$next_month_date = $this->next_month_date;
		
    $tbl = "stock_balance_b".$bid."_".date('Y',strtotime($this->date_to));
    
    if ($_REQUEST['hq_cost'])	$extrasql=", si.hq_cost as cost_price";
    else	$extrasql=",sb.cost as cost_price";
    
    $sql = "select si.id as sid,si.description,mcode,sku_item_code,artno,sb.qty, ifnull((select price from sku_items_price_history siph where siph.sku_item_id=si.id and siph.branch_id=$bid and siph.added<".ms($next_month_date)." order by siph.added desc limit 1),si.selling_price) as selling_price, ifnull((select trade_discount_code from sku_items_price_history siph where siph.sku_item_id=si.id and siph.branch_id=$bid and siph.added<".ms($next_month_date)." order by siph.added desc limit 1),sku.default_trade_discount_code) as discount_code $extrasql
from sku_items si
left join $tbl sb on si.id=sb.sku_item_id and ((".ms($this->date_to)." between sb.from_date and sb.to_date) or (".ms($this->date_to).">=sb.from_date and sb.is_latest=1)) 
left join sku on sku.id=si.sku_id 
left join category_cache cc on cc.category_id=sku.category_id
where $filter 
group by sid
order by description";
		//print $sql;
		$con_multi->sql_query($sql) or die(mysql_error());
		while($r = $con_multi->sql_fetchassoc()){
		    if($config['ci_use_split_artno']){
		    	list($r['artno_code'],$r['artno_size']) = preg_split("/\s+/",$r['artno'],2);
		    }
		    
		    $total_cost_price = $r['cost_price']*$r['qty'];
		    $total_selling_price = $r['selling_price']*$r['qty'];
		    
			$this->table[$r['sid']]['qty'] += $r['qty'];
			$this->table[$r['sid']]['total_cost_price'] += $total_cost_price;
			$this->table[$r['sid']]['total_selling_price'] += $total_selling_price;
			$this->table[$r['sid']]['discount_code'] = $r['discount_code'];
			$this->table[$r['sid']]['artno_code'] = $r['artno_code'];
			$this->table[$r['sid']]['artno_size'] = $r['artno_size'];
			$this->table[$r['sid']]['artno'] = $r['artno'];
			$this->table[$r['sid']]['sku_item_code'] = $r['sku_item_code'];
			$this->table[$r['sid']]['mcode'] = $r['mcode'];
			$this->table[$r['sid']]['description'] = $r['description'];
			$this->table[$r['sid']]['sid'] = $r['sid'];
			$this->table[$r['sid']]['cost_price'] = $r['cost_price'];
			$this->table[$r['sid']]['selling_price'] = $r['selling_price'];

			$this->total['selling_price'] += $r['selling_price'];
			$this->total['cost_price'] += $r['cost_price'];
			$this->total['qty'] += $r['qty'];
			$this->total['total_cost_price'] += $total_cost_price;
			$this->total['total_selling_price'] += $total_selling_price;
		}
		$con_multi->sql_freeresult();
	}
	
	function generate_report()
	{
		global $con, $smarty;
		
		if($this->branch_id_list){
            foreach($this->branch_id_list as $bid){
	            $this->run($bid);
			}

			if($this->use_hq_cost_selling){ // rerun to show hq cost and selling
				$this->replace_using_hq_cost_selling($_REQUEST['hq_cost'] ? true : false);
			}
		}
		
		if(!empty($this->sort_by)&&$this->table)    $this->sort_data();
		
		$smarty->assign('table',$this->table);
		$smarty->assign('total',$this->total);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo;
		// do my own form process
		
		// call parent
		parent::process_form();
		$this->sort_by = $_REQUEST['sort_by'];
		$this->sort_order = $_REQUEST['sort_order'];
		$branch_id = mi($_REQUEST['branch_id']);
		$this->hide_zero = $_REQUEST['hide_zero'];
		$this->year = mi($_REQUEST['year']);
		$this->month = mi($_REQUEST['month']);
		$this->date_to = $this->year.'-'.$this->month.'-'.days_of_month($this->month,$this->year);

		$year = $this->year;
		$month = $this->month;

		$month2 = $month+1;
		$year2 = $year;
		if($month2>12){
			$month2 = 1;
			$year2++;
		}
		$this->next_month_date = $year2."-".$month2."-1";
		
		$con->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
		$lv = $con->sql_fetchrow();
		$level = $lv['level'];
		$cat_desc = $lv['description'];

		if($_REQUEST['all_category']=='on'){
			$where['category'] = "p2 in ($sessioninfo[department_ids])";
			$cat_desc = "All";
		}else{
            $where['category'] = $_REQUEST['category_id']?"p$level = ".mi($_REQUEST['category_id']):1;
		}
		
		if($branch_id>0){
			$this->branch_id_list = array($branch_id);
			$con->sql_query("select code from branch where id=".mi($branch_id));
			$report_title[] = "Branch: ".$con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else{
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					$this->branch_id_list[] = $r['id'];
				}
				$con->sql_freeresult($q1);
				$report_title[] = "Region: ".$region;
			}elseif($branch_id<0){ // is branch group
				$bgid = abs($branch_id);
				$branch_group = $this->load_branch_group();
				$report_title[] = "Branch Group: ".$branch_group['header'][$bgid]['code'];
				foreach($branch_group['items'][$bgid] as $bid=>$r){
					$this->branch_id_list[] = $bid;
				}
			}
		}
		
		if(count($this->branch_id_list)>1)  $this->use_hq_cost_selling = true;  // if group by branch, show hq selling and cost
  		$this->where = $where;
		
		$filter[] = $where['category'];
		/*if($this->hide_zero){
			//$filter[] = 'sc.qty<>0';
			$filter[] = 'sb.qty<>0';
		}*/
		
		$this->filter = join(' and ',$filter);

		$report_title[] = "Category: ".$cat_desc;
		$report_title[] = "Year: $year";
		$report_title[] = "Month: ".str_month($month);
		
		//$smarty->assign('cat_desc',$cat_desc);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;' , $report_title));
		$smarty->assign('use_hq_cost_selling', $this->use_hq_cost_selling);
        
	}	

	function default_values()
	{

	}
	
	private function sort_data(){
		usort($this->table,array($this,'start_sort'));
	}
	
	function start_sort($a,$b){
		$col = $this->sort_by;
		$sort_order = $this->sort_order;
		
	    if ($a[$col]==$b[$col]) {
	        // sort artno if the selected sort key is same
			if($sort_order=='desc')
				return ($a['artno']>$b['artno']) ? -1 : 1 ;
		    else
				return ($a['artno']>$b['artno']) ? 1 : -1 ;
	        
		}

		if($sort_order=='desc')
			return ($a[$col]>$b[$col]) ? -1 : 1;
	    else
			return ($a[$col]>$b[$col]) ? 1 : -1;
	}
	
	private function replace_using_hq_cost_selling($hq_cost_only=false){
		global $con;
		
		// renew the total
		
		$this->total['selling_price'] = 0;
		//if use hq_cost, no reset
		$this->total['cost_price'] = 0;	
			
		$filter = $this->filter;
		$next_month_date = $this->next_month_date;
	    
		if ($hq_cost_only)	$extrasql=", si.hq_cost as cost_price";		
	    else $extrasql=", si.cost_price as cost_price";
	    
		$sql = "select si.id as sid,si.selling_price,sku.default_trade_discount_code $extrasql
from sku_items si
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where $filter";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q1)){
		    $sid = mi($r['sid']);
		    if (!isset($this->table[$sid])) continue;
		    
			// get selling and price type
			$con->sql_query("select price,trade_discount_code from sku_items_price_history where branch_id=1 and added<".ms($next_month_date)." and sku_item_id=$sid order by added desc limit 1");
			$siph = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			if($siph){
				$this->table[$sid]['selling_price'] = mf($siph['price']);
				$this->table[$sid]['discount_code'] = trim($siph['trade_discount_code']);
			}else{
                $this->table[$sid]['selling_price'] = mf($r['selling_price']);  // master
			}
			
			if(!$this->table[$sid]['discount_code'])    $this->table[$sid]['discount_code'] = $r['default_trade_discount_code']; // master

			// recalculate total
			$this->total['selling_price'] += $this->table[$sid]['selling_price'];
			
		    if (!$hq_cost_only){
				// get cost
				$con->sql_query("select grn_cost from sku_items_cost_history where branch_id=1 and date<=".ms($next_month_date)." and sku_item_id=$sid order by date desc limit 1");
				$sich = $con->sql_fetchrow();
				$con->sql_freeresult();
				
				if($sich){
	                $this->table[$sid]['cost_price'] = mf($sich['grn_cost']);
				}else{
	                $this->table[$sid]['cost_price'] = mf($r['cost_price']);  // master
				}
			}else{
				$this->table[$sid]['cost_price'] = mf($r['cost_price']);  // HQ Cost
			}
							
			// recalculate total
			$this->total['cost_price'] += $this->table[$sid]['cost_price'];
		}
		$con->sql_freeresult($q1);
	}
}

$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}

$report = new Consignment_HQ_Stock_Report('Consignment Stock Balance Report');
$con_multi->close_connection();
?>
