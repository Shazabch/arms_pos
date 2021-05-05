<?php
/*
2/26/2010 3:40:10 PM Andy
- change memory limit to 512M

1/25/2011 2:17:05 PM Alex
- change use report_server
- fix date bugs

6/24/2011 6:20:23 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:43:29 PM Andy
- Change split() to use explode()

11/2/2011 5:58:32 PM Justin
- Fixed the bugs when show all data from all branches, system will only sum up the last branch's qty and amount for each row.

5/9/2012 10:19:40 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4/29/2016 3:08 PM Andy
- Add vendor code and vendor description.
- Add MCode and Artno.

5/9/2017 9:56 AM Justin
- Optimised the scripts to prevent memory reach maximum limit.

5/22/2017 5:45 PM Justin
- Bug fixed on PHP showing lots of warning message.

11/7/2018 11:33 AM Justin
- Enhanced the report to allow user to choose show data by Quantity, Sales, Cost, GP or GP(%).

2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.

9/26/2019 4:56 PM William
- Fix bug sku item description and old code not show parent sku items description and old code, when group by sku.

2/19/2020 11:59 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class MonthlySalesReportByCategory extends Report
{
	var $data_row_list = array("quantity"=>"Quantity", "amount"=>"Sales Amount");
	
	function __construct($title){
		global $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		$this->init();
 		parent::__construct($title);
	}
	
	function init() {
		global $smarty, $sessioninfo;
		
		if($sessioninfo['privilege']['SHOW_COST']){
			$this->data_row_list['cost'] = "Cost";
			$this->data_row_list['gp'] = "GP";
			$this->data_row_list['gp_perc'] = "GP(%)";
		}
		
		$smarty->assign("data_row_list", $this->data_row_list);
	}
	
	function run_report($bid,$tbl_name)
	{
	    global $con_multi,$sessioninfo;
		
		$tbl = $tbl_name['sku_items_sales_cache'];
        if($this->group_by_sku){
            $sql="select is_parent,year,month,sku_items.sku_id as sku_id,sku_items.description,sum(pos.qty) as quantity,sum(pos.amount) as amount, v.code as vcode, v.description as v_desc, sum(pos.cost) as cost, sku_items.link_code
			from $tbl pos
			left join sku_items on sku_item_id = sku_items.id
			left join sku on sku_id = sku.id
			left join category_cache using (category_id)
			left join vendor v on v.id=sku.vendor_id
			where ".$this->filter."
			group by sku_items.sku_id,year,month
			order by year,month,amount desc";
        }else{
            $sql="select year,month,sku_item_code,sku_item_id,sku_items.description,sum(pos.qty) as quantity,sum(pos.amount) as amount, v.code as vcode, v.description as v_desc, sku_items.mcode, sku_items.artno, sku_items.link_code, sum(pos.cost) as cost
			from $tbl pos
			left join sku_items on sku_item_id = sku_items.id
			left join sku on sku_id = sku.id
			left join category_cache using (category_id)
			left join vendor v on v.id=sku.vendor_id
			where ".$this->filter."
			group by sku_items.id,year,month
			order by year,month,amount desc";
		}

        $q1 = $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
		if($con_multi->sql_numrows($q1)>0){
            while($r = $con_multi->sql_fetchassoc($q1)){
	            $lbl = sprintf("%04d%02d", $r['year'], $r['month']);
			    $this->label[$lbl] = $this->months[$r['month']] ." " . $r['year'];

                if($this->group_by_sku){
                    $key = $r['sku_id'];
                }else{
					$key = $r['sku_item_id'];
				}
				
				if($key==''){
                    $r['description'] = 'Un-categorized';
				}
				$this->table[$key]['quantity'][$lbl]+=$r['quantity'];
				$this->table[$key]['amount'][$lbl]+=$r['amount'];
				$this->table[$key]['cost'][$lbl]+=$r['cost'];
				$this->table[$key]['gp'][$lbl]+=round($r['amount'] - $r['cost'], 2);

				if(!$this->group_by_sku){
				    $this->table[$key]['sku_item_id'] = $r['sku_item_id'];
			    	$this->table[$key]['sku_item_code'] = $r['sku_item_code'];
					$this->table[$key]['mcode'] = $r['mcode'];
					$this->table[$key]['artno'] = $r['artno'];
				}else{
                    $this->table[$key]['sku_id'] = $key;
					$this->table[$key]['is_parent'] = $r['is_parent'];
				}
				$this->table[$key]['link_code'] = $r['link_code'];

				$this->table[$key]['vendor_description'] = ($r['vcode'] ? $r['vcode'].' - ' : '').$r['v_desc'];
			    $this->table[$key]['description'] = $r['description'];
			    $this->table[$key]['quantity']['total'] += $r['quantity'];
			    $this->table[$key]['amount']['total'] += $r['amount'];
			    $this->table[$key]['cost']['total'] += $r['cost'];
			    $this->table[$key]['gp']['total'] += round($r['amount'] - $r['cost'], 2);
				
				
				if($this->table[$key]['gp'][$lbl] > 0){
					$this->table[$key]['gp_perc'][$lbl] = round($this->table[$key]['gp'][$lbl] / $this->table[$key]['amount'][$lbl] * 100, 2);
					$this->table[$key]['gp_perc']['total'] = round($this->table[$key]['gp']['total'] / $this->table[$key]['amount']['total'] * 100, 2);
				}
	        }
		}
		$con_multi->sql_freeresult($q1);
	}
	
	function sort_table($a,$b)
	{
	    if(isset($_REQUEST['quantity_amount_type'])){
	        $arrange_type = $this->quantity_amount_type;
	    }else{
            $arrange_type = key($this->data_row_list);
		}
	    
	    if ($a[$arrange_type]['total']==$b[$arrange_type]['total']) return 0;
	    if($this->order_type=="bottom"){
            return ($a[$arrange_type]['total']<$b[$arrange_type]['total']) ? -1 : 1;
		}else{
            return ($a[$arrange_type]['total']>$b[$arrange_type]['total']) ? -1 : 1;
		}
        
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi;
		
	    $minimum_transaction = intval($_REQUEST['min_tran']);
	    $minimum_amount = doubleval($_REQUEST['min_amount']);
	    $filter_number = intval($_REQUEST['filter_number']);
	    
		if($filter_number > 1000 || $filter_number < 1){
			$filter_number = 1000;
		}
				
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			foreach($this->branch_group['items'][$bg_id] as $tmp_bid=>$b){
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$tmp_bid;
	            $this->run_report($tmp_bid,$tbl_name);
			}
			$branch_name = $this->branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_name = "All";
	                $q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	                while($r = $con_multi->sql_fetchassoc($q_b)){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
				}else{
	                $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_name = get_branch_code($bid);
				}
			}
		}

		if($this->table){
			foreach(array_keys($this->table) as $k)
			{
				$a1=array();$a2=array();
				foreach($this->label as $lbl=>$dummy)
				{
					$a1[$lbl] = doubleval($this->table[$k]['amount'][$lbl]);
				}
				if (min($a1)<$minimum_amount) { unset($this->table[$k]); continue; }
				foreach($this->label as $lbl=>$dummy)
				{
					$a2[$lbl] = doubleval($this->table[$k]['quantity'][$lbl]);
				}
				 if (min($a2)<$minimum_transaction) unset($this->table[$k]);
			}
			if($this->group_by_sku){
				foreach($this->table as $key=>$value){
					if($value['is_parent'] != 1){
						$q2 = $con_multi->sql_query("select link_code,description from sku_items where sku_id=".mi($key)." and is_parent=1");
						$r2 = $con_multi->sql_fetchassoc($q2);
						$con_multi->sql_freeresult($q2);
						$this->table[$key]['description'] = $r2['description'];
						$this->table[$key]['link_code'] = $r2['link_code'];
					}
				}
			}
			usort($this->table, array($this,"sort_table"));

			
			for($i=0; $i<$filter_number; $i++){
				foreach($this->label as $idx=>$dummy){
					$this->table2['amount'][$idx]+=$this->table[$i]['amount'][$idx];
					$this->table2['quantity'][$idx]+=$this->table[$i]['quantity'][$idx];
					$this->table2['cost'][$idx]+=$this->table[$i]['cost'][$idx];
					$this->table2['gp'][$idx]+=$this->table[$i]['gp'][$idx];
					$this->table2['amount']['total']+=$this->table[$i]['amount'][$idx];
					$this->table2['quantity']['total']+=$this->table[$i]['quantity'][$idx];
					$this->table2['cost']['total']+=$this->table[$i]['cost'][$idx];
					$this->table2['gp']['total']+=$this->table[$i]['gp'][$idx];
					
					if($this->table2['gp'][$idx] > 0){
						$this->table2['gp_perc'][$idx] = round($this->table2['gp'][$idx] / $this->table2['amount'][$idx] * 100, 2);
						$this->table2['gp_perc']['total'] = round($this->table2['gp']['total'] / $this->table2['amount']['total'] * 100, 2);
					}
				}
			}

			ksort($this->label);
		}

	    $rpt_title[] = "Branch: ".$branch_name;
		$rpt_title[] = "Date: From $this->date_from to $this->date_to";
	    $rpt_title[] = "Category: $this->cat_desc";

		$report_title = join('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$rpt_title);

	    $smarty->assign('report_title',$report_title);
		
		// calculate the rowspan for both mcode and artno
		// artno always get the highest rowspan if the data row is odd number
		$mcode_rowspan = floor(count($this->data_row)/2);
		$artno_rowspan = ceil(count($this->data_row)/2);
		
		// give at least 1 rowspan for mcode
		if(!$mcode_rowspan) $mcode_rowspan = 1;
		$first_data_type = key($this->data_row);
		
		// get which data row should mcode display out
		$data_row = array_keys($this->data_row);
		$mcode_display_row = $data_row[$mcode_rowspan];
    
		$smarty->assign('label',$this->label);
		$smarty->assign('table',$this->table);
		$smarty->assign('table2',$this->table2);
		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('minimum_transaction',$minimum_transaction);
		$smarty->assign('minimum_amount',$minimum_amount);
		$smarty->assign('filter_number',$filter_number);
		$smarty->assign('mcode_rowspan',$mcode_rowspan);
		$smarty->assign('artno_rowspan',$artno_rowspan);
		$smarty->assign('first_data_type',$first_data_type);
		$smarty->assign('mcode_display_row',$mcode_display_row);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$this->group_by_sku = $_REQUEST['group_sku'];
        $this->order_type = $_REQUEST['order_type'];
        $this->data_row = $_REQUEST['data_row'];
        
	    $this->filter = array();

	    if($_REQUEST['all_category']!='on'){
            $category_id = intval($_REQUEST['category_id']);
		    $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			$this->cat_desc = $temp['description'];

			$this->filter[] = "p$level=$category_id";
		}else{
		    $this->cat_desc = "All";
            $this->filter[] =  "p2 in ($sessioninfo[department_ids])";
		}

	    $this->filter[] = 'pos.date between '.ms($this->date_from).' and '.ms($this->date_to);
		$this->filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';

		$this->filter = join(' and ',$this->filter);
	}	

	function default_values()
	{
	    $view_type = $_REQUEST['view_type'];
	    if($view_type=="day"){
                $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		}else{
            $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
		}
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}
//$con_multi = new mysql_multi();
$report = new MonthlySalesReportByCategory('Monthly SKU Items Sales Ranking by Category');
//$con_multi->close_connection();
?>
