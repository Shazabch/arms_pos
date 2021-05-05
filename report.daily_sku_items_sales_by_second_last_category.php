<?php
/*
10/6/2010 5:24:25 PM Andy
- Fix sql error cause column qty ambigous (sku_items_sales_cache and pwp_sales_cache).

1/19/2011 3:46:45 PM Alex
- change use report_server

6/24/2011 6:08:39 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:31:54 PM Andy
- Change split() to use explode()

5/9/2012 10:14:07 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

06/10/2016 10:00 Edwin
- Enhanced on show branch group in report.

7/11/2017 9:55 AM Justin
- Bug fixed on GP calculation is wrong.
- Optmised the report to remove unnecessory memory usage.

10/16/2018 5:37 PM Justin
- Bug fixed on system couldn't differentiate SKU items with "Un-categorised" or "No 4th Level Category".

12/4/2018 11:32 AM Justin
- Bug fixed on viewing the report by day will skip all the sales from "No 4th Level Category".

2/19/2020 10:30 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");

class DailySales extends Report
{
	var $where;
	
	function run($bid,$tbl_name)
	{
	    global $con,$smarty,$sessioninfo, $con_multi;
		
		$tbl_sc = $tbl_name['sku_items_sales_cache'];
		$tbl_pwp = $tbl_name['pwp_sales_cache'];
		
		if($_REQUEST['view_type']=='yearly'){
			$sql ="select pos.year,pos.month,sum(pos.qty) as qty,sum(pos.amount) as amount,p4 as p,c4.description as cname,c4.root_id,max(pos.amount/pos.qty) as highest_price,min(pos.amount/pos.qty) as lowest_price,sum(pos.cost) as cost,sum(pwp.amount) as pwp_amount,sum(pwp.cost) as pwp_cost, c.root_id as mst_root_id
			from $tbl_sc pos
			left join sku_items on sku_item_id = sku_items.id
			left join sku on sku_id = sku.id
			left join category_cache using (category_id)
			left join category c4 on category_cache.p4 = c4.id
			left join category c on sku.category_id = c.id
			left join $tbl_pwp pwp using(sku_item_code,date)
			where ".$this->filter." group by p,month,year order by p,year,month";
		}else{
	        $sql = "select date,pos.month,day(date) as day,sum(pos.qty) as qty,sum(pos.amount) as amount,p4 as p,c4.description as cname,c4.root_id,max(pos.amount/pos.qty) as highest_price,min(pos.amount/pos.qty) as lowest_price,sum(pos.cost) as cost,sum(pwp.amount) as pwp_amount,sum(pwp.cost) as pwp_cost, c.root_id as mst_root_id
			from $tbl_sc pos
			left join sku_items on sku_item_id = sku_items.id
			left join sku on sku_id = sku.id
			left join category_cache using (category_id)
			left join category c4 on category_cache.p4 = c4.id
			left join category c on sku.category_id = c.id
			left join $tbl_pwp pwp using(sku_item_code,date)
			where ".$this->filter." group by p,date order by p,date";
		}
		
        $q1 = $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";
		if($con_multi->sql_numrows($q1)>0){
		    $last_root_id = 'null';
		    
			while($r = $con_multi->sql_fetchassoc($q1)){
                if($_REQUEST['view_type']=='yearly'){
                    $lbl = sprintf("%02d%02d", $r['year'], $r['month']);
                }else{
                    $lbl = sprintf("%02d%02d", $r['month'], $r['day']);
				}
			    
                if($last_root_id!=$r['root_id']){
					if(!$r['mst_root_id']){ // this SKU item has no category
						$r['root_id'] = 'uncategorised';
						$this->category[$r['root_id']]['id']=$r['root_id'];
						$this->category[$r['root_id']]['name']="Un-categorised";
                    }elseif($r['root_id']==''){ // this SKU item has category but it is under 3th category or higher
						$this->category[$r['root_id']]['id']=$r['root_id'];
						$this->category[$r['root_id']]['name']='No 4th Level Category';
					}else{
                        $temp = $con_multi->sql_query('select description from category where id='.mi($r['root_id'])) or die(mysql_error());
						$temp_table = $con_multi->sql_fetchassoc($temp);
						$con_multi->sql_freeresult($temp);
						$last_root_id = $r['root_id'];
						$description = $temp_table['description'];

						$this->category[$r['root_id']]['id']=$r['root_id'];
						$this->category[$r['root_id']]['name']=$description;
					}
				}
				
				$this->category[$r['root_id']]['qty'][$lbl]+=$r['qty'];
	            $this->category[$r['root_id']]['qty']['total']+=$r['qty'];
	            $this->category[$r['root_id']]['amount'][$lbl]+=$r['amount'];
	            $this->category[$r['root_id']]['amount']['total']+=$r['amount'];
	            
	            $this->category[$r['root_id']]['cost_total'] += $r['cost'];
	            $this->category[$r['root_id']]['cost_amt'] = ($this->category[$r['root_id']]['amount']['total']-$this->category[$r['root_id']]['cost_total']);
	            
	            if($this->category[$r['root_id']]['amount']['total']>0){
                    $this->category[$r['root_id']]['cost_per'] = ((($this->category[$r['root_id']]['amount']['total']-$this->category[$r['root_id']]['cost_total'])/$this->category[$r['root_id']]['amount']['total'])*100);
				}else{
                    $this->category[$r['root_id']]['cost_per'] = 0;
				}
				
				if($this->category[$r['root_id']]['qty']['total']>0){
                    $this->category[$r['root_id']]['avg_sprice']=$this->category[$r['root_id']]['amount']['total']/$this->category[$r['root_id']]['qty']['total'];
				}else{
                    $this->category[$r['root_id']]['avg_sprice'] = 0;
				}
				
	            if($this->category[$r['root_id']]['highest_price']<$r['highest_price']){
                    $this->category[$r['root_id']]['highest_price'] = $r['highest_price'];
				}
				if($this->category[$r['root_id']]['lowest_price']==''){
				    $this->category[$r['root_id']]['lowest_price'] = $r['lowest_price'];
				}else if($this->category[$r['root_id']]['lowest_price']>$r['lowest_price']){
                    $this->category[$r['root_id']]['lowest_price'] = $r['lowest_price'];
				}
				
				$this->category[$r['root_id']]['pwp']+=$r['pwp_amount'];
				$this->category[$r['root_id']]['pwp_cost']+=$r['pwp_cost'];
				$this->category[$r['root_id']]['pwp_gp_amount']=$this->category[$r['root_id']]['pwp']-$this->category[$r['root_id']]['pwp_cost'];
				
				if($this->category[$r['root_id']]['pwp']>0){
                    $this->category[$r['root_id']]['pwp_gp_per']=(($this->category[$r['root_id']]['pwp_gp_amount']/$this->category[$r['root_id']]['pwp'])*100);
				}

	            $this->category['total']['qty'][$lbl]+=$r['qty'];
	            $this->category['total']['qty']['total']+=$r['qty'];
	            $this->category['total']['amount'][$lbl]+=$r['amount'];
	            $this->category['total']['amount']['total']+=$r['amount'];
	            $this->category['total']['cost_total']+=$r['cost'];
	            $this->category['total']['cost_amt']=$this->category['total']['amount']['total']-$this->category['total']['cost_total'];
	            if($this->category['total']['amount']['total']>0){
                    $this->category['total']['cost_per'] = ((($this->category['total']['amount']['total']-$this->category['total']['cost_total'])/$this->category['total']['amount']['total'])*100);
				}else{
                    $this->category['total']['cost_per'] = 0;
				}
				
				if($this->category['total']['qty']['total']>0){
				    $this->category['total']['avg_sprice']=$this->category['total']['amount']['total']/$this->category['total']['qty']['total'];
				}else{
                    $this->category['total']['avg_sprice'] = 0;
				}
				
				if($this->category['total']['highest_price']<$r['highest_price']){
                    $this->category['total']['highest_price'] = $r['highest_price'];
				}
				
                if($this->category['total']['lowest_price']==''){
				    $this->category['total']['lowest_price'] = $r['lowest_price'];
				}else if($this->category['total']['lowest_price']>$r['lowest_price']){
                    $this->category['total']['lowest_price'] = $r['lowest_price'];
				}
				
				$this->category['total']['pwp']+=$r['pwp_amount'];
                $this->category['total']['pwp_cost']+=$r['pwp_cost'];
				$this->category['total']['pwp_gp_amount']=$this->category['total']['pwp']-$this->category['total']['pwp_cost'];

				if($this->category['total']['pwp']>0){
                    $this->category['total']['pwp_gp_per']=(($this->category['total']['pwp_gp_amount']/$this->category['total']['pwp'])*100);
				}
				
                $this->sku[$r['p']]['category_id']=$r['p'];
				$this->sku[$r['p']]['description']=$r['cname'];
                
				$this->table[$r['root_id']][$r['p']]['qty'][$lbl]+=$r['qty'];
				$this->table[$r['root_id']][$r['p']]['amount'][$lbl]+=$r['amount'];
			    $this->table[$r['root_id']][$r['p']]['qty']['total'] += $r['qty'];
			    $this->table[$r['root_id']][$r['p']]['amount']['total'] += $r['amount'];
			    $this->table[$r['root_id']][$r['p']]['cost_total'] += ($r['cost']);
			    $this->table[$r['root_id']][$r['p']]['cost_amt'] = ($this->table[$r['root_id']][$r['p']]['amount']['total']-$this->table[$r['root_id']][$r['p']]['cost_total']);
			    
			    if($this->table[$r['root_id']][$r['p']]['amount']['total']>0){
                    $this->table[$r['root_id']][$r['p']]['cost_per'] = (($this->table[$r['root_id']][$r['p']]['amount']['total']-$this->table[$r['root_id']][$r['p']]['cost_total'])/$this->table[$r['root_id']][$r['p']]['amount']['total'])*100;
				}else{
                    $this->table[$r['root_id']][$r['p']]['cost_per'] = 0;
				}
			    
			    if($this->table[$r['root_id']][$r['p']]['qty']['total']>0){
                    $this->table[$r['root_id']][$r['p']]['avg_sprice']=$this->table[$r['root_id']][$r['p']]['amount']['total']/$this->table[$r['root_id']][$r['p']]['qty']['total'];
				}else{
                    $this->table[$r['root_id']][$r['p']]['avg_sprice'] = 0;
				}
				
				if($this->table[$r['root_id']][$r['p']]['highest_price']<$r['highest_price']){
                    $this->table[$r['root_id']][$r['p']]['highest_price'] = $r['highest_price'];
				}

                if($this->table[$r['root_id']][$r['p']]['lowest_price']==''){
				    $this->table[$r['root_id']][$r['p']]['lowest_price'] = $r['lowest_price'];
				}else if($this->table[$r['root_id']][$r['p']]['lowest_price']>$r['lowest_price']){
                    $this->table[$r['root_id']][$r['p']]['lowest_price'] = $r['lowest_price'];
				}
				$this->table[$r['root_id']][$r['p']]['pwp'] += $r['pwp_amount'];
				$this->table[$r['root_id']][$r['p']]['pwp_cost']+=$r['pwp_cost'];
				$this->table[$r['root_id']][$r['p']]['pwp_gp_amount']=$this->table[$r['root_id']][$r['p']]['pwp']-$this->table[$r['root_id']][$r['p']]['pwp_cost'];

				if($this->table[$r['root_id']][$r['p']]['pwp']>0){
                    $this->table[$r['root_id']][$r['p']]['pwp_gp_per']=(($this->table[$r['root_id']][$r['p']]['pwp_gp_amount']/$this->table[$r['root_id']][$r['p']]['pwp'])*100);
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
            $arrange_type = 'amount';
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
		
		$bid  = get_request_branch(true);
        $branch_group = $this->branch_group;
		    
        if($_REQUEST['branch_id'] < 0) {   // is branch group
			$bg_id = abs($_REQUEST['branch_id']);
			foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($tmp_bid);
				$tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".mi($tmp_bid);
				$this->run($tmp_bid,$tbl_name);
				$branch_name =  get_branch_code($tmp_bid);
			}
			$branch_name = $branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);

			if($bid>0){ // selected single branch
			    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($bid);
			    $tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".mi($bid);
				$this->run($bid,$tbl_name);
				$branch_name =  get_branch_code($bid);
			}else{  // all
                $branch_name = "All";
				$b0 = $con_multi->sql_query("select id from branch where active=1 order by sequence,code");
				while($b = $con_multi->sql_fetchrow($b0))
				{
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($b['id']);
			    	$tbl_name['pwp_sales_cache'] = "pwp_sales_cache_b".mi($b['id']);
					$this->run($b['id'],$tbl_name);
				}
				$con_multi->sql_freeresult($b0);
				/*if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
						$tbl_name['pwp_sales_cache'] = "pwp_sales_cache_bg".mi($bg_id);
						$this->run($bg_id+10000,$tbl_name);
					}
				}*/
			}
		}
    
		$report_title = "Branch: ".$branch_name."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;From: ".$this->months[$_REQUEST['month']]." ".$_REQUEST['year'];
		
		$smarty->assign('report_title',$report_title);
		$smarty->assign('label',$this->label);
		$smarty->assign('sku',$this->sku);
		$smarty->assign('table',$this->table);
		$smarty->assign('category',$this->category);
		$smarty->assign('branch_name',$branch_name);
	}
	
	function process_form()
	{
		// do my own form process
		global $con,$smarty,$sessioninfo;
		
		// call parent
		parent::process_form();
		
		$this->load_branch_group();
		$branch_group = $this->branch_group;
		
		$department_id = intval($_REQUEST['department_id']);
	    $start_month = $_REQUEST['month'];
	    $start_year = $_REQUEST['year'];
		$start_date = $start_year."-".$start_month."-1";

		$smarty->assign('date_msg',$this->months[$start_month]." ".$start_year);
		$this->filter = array();
		
		$this->filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';

		if($department_id!=0){
			$this->filter[] = "p2=".mi($department_id);
		}else{
			$this->filter[] =  "p2 in ($sessioninfo[department_ids])";
		}

		if($_REQUEST['view_type']=='yearly'){
		    $end_date =date("Y-m-d",strtotime("+1 year",strtotime($start_date)));
			$this->filter[] = "pos.date between ".ms($start_date)." and ".ms($end_date);
		}else{
		    $end_date =date("Y-m-d",strtotime("+1 month",strtotime($start_date))-86400);
            $this->filter[] = "pos.month=".mi($start_month);
            $this->filter[] = "pos.year=".mi($start_year);
			//$this->filter[] = "c4.description<>''";
		}
		$this->filter = join(" and ", $this->filter);
		
		if($_REQUEST['view_type']=='yearly'){
	        $this->label = $this->generate_months($start_date, $end_date, 'Ym', 'M');
	    }else{
            $this->label = $this->generate_dates($start_date, $end_date, 'md', 'd');
		}
		ksort($this->label);
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

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new DailySales('Sales by 4th Level Category');
//$con_multi->close_connection();
?>
