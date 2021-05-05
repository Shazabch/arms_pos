<?php
/*
3/8/2010 10:33:06 AM Andy
- Fix don't show expand image if category have no more sub-category
- Fix incorrect total due to decimal qty problem
- Change the report total qty not to include the last day sales, cuz the last day actually is already over the report date range

4/28/2010 10:55:20 AM Andy
- Change Report to use pos and pos_items to find the highest and lowest selling price.
- Fix wrong gross profit percent bugs
- Fix main category cannot show CM figures bugs

5/4/2010 4:53:17 PM Andy+Alex
- Fix Show correct results

1/24/2011 4:09:07 PM Alex
- change use report_server

6/24/2011 6:27:45 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:37:02 PM Andy
- Change split() to use explode()

5/9/2012 11:15:58 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Bug fixed on sql error once clicked on show itemise table.
- Enhanced to have export feature for itemise table.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

5/27/2016 4:10 PM Andy
- Fix sql error when expand sub category.

4/26/2017 14:27 Qiu Ying
- Bug fixed on export SKU Items reports cannot view by WPS office software.

6/23/2017 2:30 PM Andy
- Fix sql error on show items details.

2/19/2020 9:57 AM William
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
	    global $con,$con_multi,$smarty,$sessioninfo;

		if($bid>10000){ // branch group
		    $bgid = $bid-10000;
			foreach($this->branch_group['items'][$bgid] as $r){
                $bid_list[] = mi($r['branch_id']);
			}
		}else{
            $bid_list[] = $bid; // single branch
		}
		
	    $table = $this->table;
		$label = $this->label;
		$category = $this->category;
		$sku = $this->sku;

	    $filter = $this->filter;
	    $tbl = $tbl_name['sku_items_sales_cache'];
      
	    $sql = "select pos.year,pos.month,sum(qty) as qty,sum(pos.amount) as amount,p3 as p,category.description as cname,root_id,sum(cost) as cost
				from $tbl pos
				left join sku_items si on sku_item_id = si.id
				left join sku on sku_id = sku.id
				left join category_cache using (category_id)
				left join category on category_cache.p3 = category.id
				where $filter group by p,month,year order by p,year,month";

	    /*$sql2 = "select pos.year,pos.month,p3 as p,category.description as cname,root_id,max(amount) as highest_sales,min(amount) as lowest_sales
	from $tbl pos
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category_cache using (category_id)
	left join category on category_cache.p3 = category.id
	where $filter and amount>0 group by p,month,year order by p,year,month";*/
	
	    $sql2 = "select year(pos.date) as year, month(pos.date) as month,p3 as p,category.description as cname,category.root_id,max((price-discount)/pi.qty) as highest_sales, min((price-discount)/pi.qty) as lowest_sales
				from pos
				left join pos_items pi on pi.pos_id=pos.id and pi.branch_id=pos.branch_id and pi.counter_id=pos.counter_id and pi.date=pos.date
				left join sku_items si on pi.sku_item_id = si.id
				left join sku on sku_id = sku.id
				left join category_cache using (category_id)
				left join category on category_cache.p3 = category.id
				where $filter and pi.price>0 and pos.cancel_status=0 and pos.branch_id in (".join(',', $bid_list).")
				group by p,month,year order by p,year,month";
		//print $filter;
		//print $sql2;

		
		
        $last_lbl = array_pop(array_keys($label));

        $q1 = $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";
        //if($sessioninfo['id']==1)   print $sql;
		if($con_multi->sql_numrows($q1)>0){
            foreach($con_multi->sql_fetchrowset($q1) as $r){
                $lbl = sprintf("%02d%02d", $r['year'], $r['month']);
				if(!$r['p']&&$r['cname']==''){
					$r['p']=0;
					$r['cname']='Un-categorized';
				}
				if(!isset($category[$r['p']]['got_parent'])){
					$con_multi->sql_query("select * from category where root_id=".mi($r['p'])." limit 1");
					if($con_multi->sql_numrows()>0)   $category[$r['p']]['got_parent'] = true;
					else    $category[$r['p']]['got_parent'] = false;
					$con_multi->sql_freeresult();
				}

				$category[$r['p']]['qty'][$lbl]+=$r['qty'];
	            $category[$r['p']]['qty']['total']+=$r['qty'];
	            $category[$r['p']]['amount'][$lbl]+=$r['amount'];
	            $category[$r['p']]['amount']['total']+=$r['amount'];
                $category[$r['p']]['name']=$r['cname'];
                $category[$r['p']]['cost'][$lbl]+=$r['cost'];
	            $category[$r['p']]['cost']['total']+=$r['cost'];

	            $category[$r['p']]['cost']['cm'][$last_lbl]=$category[$r['p']]['amount'][$last_lbl]-$category[$r['p']]['cost'][$last_lbl];
                $category[$r['p']]['cost']['accum']= $category[$r['p']]['amount']['total']-$category[$r['p']]['cost']['total'];

                if($category[$r['p']]['qty'][$lbl]!=0){
                    $category[$r['p']]['avg'][$lbl]=($category[$r['p']]['amount'][$lbl]/$category[$r['p']]['qty'][$lbl]);
				}else{
                    $category[$r['p']]['avg'][$lbl] = 0;
				}

				if($category[$r['p']]['qty']['total']!=0){
                    $category[$r['p']]['avg']['total']=($category[$r['p']]['amount']['total']/$category[$r['p']]['qty']['total']);
				}else{
                    $category[$r['p']]['avg']['total'] = 0;
				}

	            $category['total']['qty'][$lbl]+=$r['qty'];
	            $category['total']['qty']['total']+=$r['qty'];
	            $category['total']['amount'][$lbl]+=$r['amount'];
	            $category['total']['amount']['total']+=$r['amount'];
	            $category['total']['cost'][$lbl]+=$r['cost'];
	            $category['total']['cost']['total']+=$r['cost'];

	            $category['total']['cost']['cm'][$last_lbl]=$category['total']['amount'][$last_lbl]-$category['total']['cost'][$last_lbl];
	            $category['total']['cost']['accum']= $category['total']['amount']['total']-$category['total']['cost']['total'];
                
                if($category['total']['qty'][$lbl]!=0){
                    $category['total']['avg'][$lbl]=($category['total']['amount'][$lbl]/$category['total']['qty'][$lbl]);
				}else{
                    $category['total']['avg'][$lbl] = 0;
				}

				if($category['total']['qty']['total']!=0){
                    $category['total']['avg']['total']=($category['total']['amount']['total']/$category['total']['qty']['total']);
				}else{
                    $category['total']['avg']['total'] = 0;
				}

                $sku[$r['p']]['category_id']=$r['p'];
				$sku[$r['p']]['description']=$r['cname'];

				$table[$r['p']]['qty'][$lbl]+=$r['qty'];
				$table[$r['p']]['amount'][$lbl]+=$r['amount'];
			    $table[$r['p']]['qty']['total'] += $r['qty'];
			    $table[$r['p']]['amount']['total'] += $r['amount'];
			    $table[$r['p']]['cost'][$lbl]+=$r['cost'];
				$table[$r['p']]['cost']['total']+=$r['cost'];

	        }
		}
		$con_multi->sql_freeresult($q1);

		$con_multi->sql_query($sql2) or die(sql_error());//print "$sql2<br /><br />";
		if($con_multi->sql_numrows()>0){
            foreach($con_multi->sql_fetchrowset() as $r){
                $lbl = sprintf("%02d%02d", $r['year'], $r['month']);
                if($r['p']==''&&$r['cname']==''){
					$r['p']=0;
					$r['cname']='Un-categorized';
				}

                if($category[$r['p']]['highest_sales'][$lbl]<$r['highest_sales']){
                    $category[$r['p']]['highest_sales'][$lbl] = $r['highest_sales'];
				}
				if($category[$r['p']]['highest_sales']['total']<$r['highest_sales']){
                    $category[$r['p']]['highest_sales']['total'] = $r['highest_sales'];
				}
				if($category[$r['p']]['lowest_sales'][$lbl]==''||$category[$r['p']]['lowest_sales'][$lbl]>$r['lowest_sales']){
                    $category[$r['p']]['lowest_sales'][$lbl] = $r['lowest_sales'];
				}
				if($category[$r['p']]['lowest_sales']['total']==''||$category[$r['p']]['lowest_sales']['total']>$r['lowest_sales']){
                    $category[$r['p']]['lowest_sales']['total'] = $r['lowest_sales'];
				}

				if($category['total']['highest_sales'][$lbl]<$r['highest_sales']){
                    $category['total']['highest_sales'][$lbl] = $r['highest_sales'];
				}
				if($category['total']['highest_sales']['total']<$r['highest_sales']){
                    $category['total']['highest_sales']['total'] = $r['highest_sales'];
				}
				if($category['total']['lowest_sales'][$lbl]==''||$category['total']['lowest_sales'][$lbl]>$r['lowest_sales']){
                    $category['total']['lowest_sales'][$lbl] = $r['lowest_sales'];
				}
				if($category['total']['lowest_sales']['total']==''||$category['total']['lowest_sales']['total']>$r['lowest_sales']){
                    $category['total']['lowest_sales']['total'] = $r['lowest_sales'];
				}
           	}
        }
		$con_multi->sql_freeresult();

		$this->table = $table;
		$this->label = $label;
		$this->category = $category;
		$this->sku = $sku;
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
		global $con, $smarty, $sessioninfo, $con_multi;
		$branch_group = $this->branch_group;

		$this->date_from = date("Y-m-d",strtotime("-15 day",strtotime(date("Y-m-d"))));
		$this->date_to = date("Y-m-d",strtotime("-1 day",strtotime(date("Y-m-d"))));

		$table = array(); $label = array();$table2=array();$category=array();$sku=array();
		$branch_group = $this->branch_group;
		
        if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
            list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
            
            foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
            	$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($tmp_bid);
				$this->run($tmp_bid,$tbl_name);
            }
            
			$branch_name = $branch_group['header'][$bg_id]['code'];
        }else{
            $bid  = get_request_branch(true);
            if($bid==0){
				$branch_name = "All";
				$b0 = $con_multi->sql_query("select id from branch where active=1 order by sequence,code");
				while($b = $con_multi->sql_fetchrow($b0))
				{
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$b['id'];
					$this->run($b['id'],$tbl_name);
				}
				$con_multi->sql_freeresult($b0);
				/*if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
						$this->run($bg_id+10000,$tbl_name);
					}
				}*/
			}else{
				$branch_name =  get_branch_code($bid);
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
				$this->run($bid,$tbl_name);
			}
		}

		$table = $this->table;
		$label = $this->label;
		$category = $this->category;
		$sku = $this->sku;
    
		ksort($label);
   
	    $rpt_title[] = "Branch: ".$branch_name;
		$rpt_title[] = "From: ".$this->months[$_REQUEST['month']]." ".$_REQUEST['year'];
	    $rpt_title[] = "Category: $this->cat_desc";

		$report_title = join('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$rpt_title);
	
		//if($sessioninfo['id']==1)   print_r($label);
		//print_r($category);
		$smarty->assign('report_title',$report_title);
		$smarty->assign('label',$label);
		$smarty->assign('sku',$sku);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
		$smarty->assign('branch_name',$branch_name);
	}

	function process_form()
	{
		// do my own form process
		global $con,$smarty,$sessioninfo,$con_multi;

		// call parent
		parent::process_form();

		$start_month = $_REQUEST['month'];
	    $start_year = $_REQUEST['year'];
		$start_date = $start_year."-".$start_month."-1";

		$filter = array();
		if($_REQUEST['all_category']=='on'){
			$filter[] = "p2 in ($sessioninfo[department_ids])";
			$this->cat_desc = "All";
		}else{
            $category_id = $_REQUEST['category_id'];
/*
			if($_REQUEST['filter_cat']){
	            $con->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
				$temp = $con->sql_fetchrow();
				$level = $temp['level'];

				$filter[] = "p$level=".mi($category_id);
			}
*/
			$con_multi->sql_query("select level, description from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			$this->cat_desc=$temp['description'];
			$filter[] = "p$level=".mi($category_id);
		}

		$smarty->assign('date_msg',$this->months[$start_month]." ".$start_year);

 	    $end_date =date("Y-m-d",strtotime("+1 year",strtotime($start_date)));

		$filter[] = "pos.date>=".ms($start_date)." and pos.date<".ms($end_date);
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		$filter = join(" and ", $filter);

		$label = $this->generate_months($start_date, $end_date, 'Ym', 'M Y');

		$this->label = $label;
		$this->filter = $filter;
	
		$smarty->assign('filter',$filter);
		$smarty->assign('start_date',$start_date);
		$smarty->assign('end_date',$end_date);
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

 function make_sql($bid,$tbl_name,$filter,$code,$level,$one_more_level){
     	$tbl = $tbl_name['sku_items_sales_cache'];
    
        if($bid>10000){ // branch group
		    $bgid = $bid-10000;
			foreach($this->branch_group['items'][$bgid] as $r){
                $bid_list[] = mi($r['branch_id']);
			}
		}else{
            $bid_list[] = $bid; // single branch
		}
		
        $sql = "select pos.year,pos.month,sum(qty) as qty,sum(pos.amount) as amount,p$one_more_level as p,sum(cost) as cost
		from $tbl pos
		left join sku_items si on sku_item_id = si.id
		left join sku on sku_id = sku.id
		left join category_cache using (category_id)
		where $filter and p$level=$code group by p,month,year order by p,year,month";

        /*$sql2 = "select pos.year,pos.month,p$one_more_level as p,max(amount) as highest_sales,min(amount) as lowest_sales
	from $tbl pos
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category_cache using (category_id)
	where $filter and amount>0 and p$level=$code group by p,month,year order by p,year,month";*/
	
	    $sql2 = "select year(pos.date) as year, month(pos.date) as month,p$one_more_level  as p,max((price-discount)/pi.qty) as highest_sales, min((price-discount)/pi.qty) as lowest_sales
from pos
left join pos_items pi on pi.pos_id=pos.id and pi.branch_id=pos.branch_id and pi.counter_id=pos.counter_id and pi.date=pos.date
left join sku_items si on pi.sku_item_id = si.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
where $filter and pi.price>0 and pos.cancel_status=0 and pos.branch_id in (".join(',', $bid_list).") and p$level=$code
group by p,month,year order by p,year,month";

	    return array('sql1'=>$sql,'sql2'=>$sql2);
 }

	function load_child()
	{
		global $con, $con_multi;
		$code = $_REQUEST['code'];
		$branch_id = $_REQUEST['branch_id'];
		$filter = $_REQUEST['filter'];
		$start_date = $_REQUEST['start_date'];
		$end_date = $_REQUEST['end_date'];
		$ln = $_REQUEST['ln']+1;
		$parent_id = $_REQUEST['parent_id'];
		$branch_group = $this->load_branch_group();
		
		// get subcats
		$con_multi->sql_query("select * from category where root_id = ".mi($code)." order by description");
		$subcats = $con_multi->sql_fetchrowset();
		$con_multi->sql_freeresult();

		//$filter .= "and category.description<>''";
		$con_multi->sql_query('select * from category where id='.mi($code)) or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$level = $temp['level'];

  		// check one more level for grouping
  		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
  		$max_level = $con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
  		if($level<$max_level)	$one_more_level = $level+1;
  		else    $one_more_level = $level;

        if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
            list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
            
            foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
            	$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($tmp_bid);
				$sql_list[] = $this->make_sql($tmp_bid,$tbl_name,$filter,$code,$level,$one_more_level);
            }
        }else{
            $bid = get_request_branch(true);
            if($bid==0){
				$branch_name = "All";
				$b0 = $con_multi->sql_query("select id from branch where active=1 order by sequence,code");
				while($b = $con_multi->sql_fetchrow($b0))
				{
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$b['id'];
    				$sql_list[] = $this->make_sql($b['id'],$tbl_name,$filter,$code,$level,$one_more_level);
				}
				$con_multi->sql_freeresult($b0);
				/*if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
						$sql_list[] = $this->make_sql($bg_id+10000,$tbl_name,$filter,$code,$level,$one_more_level);

					}
				}*/
			}else{
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
    			$sql_list[] = $this->make_sql($bid,$tbl_name,$filter,$code,$level,$one_more_level);
			}
		}

		foreach($sql_list as $ss){
		    $sql = $ss['sql1'];
		    $sql2 = $ss['sql2'];

			$con_multi->sql_query($sql) or die(mysql_error());//print "$sql<br /><br />";

			$label = $this->generate_months($start_date, $end_date, 'Ym', 'M Y');
			$last_lbl = array_pop(array_keys($label));

			if($con_multi->sql_numrows()>0){
		            foreach($con_multi->sql_fetchrowset() as $r){
	                $lbl = sprintf("%02d%02d", $r['year'], $r['month']);

	                if($r['p']==''&&$r['cname']==''){
						$r['p']=0;
						$r['cname']='Un-categorized';
					}

					$category[$r['p']]['qty'][$lbl]+=$r['qty'];
		            $category[$r['p']]['qty']['total']+=$r['qty'];
		            $category[$r['p']]['amount'][$lbl]+=$r['amount'];
		            $category[$r['p']]['amount']['total']+=$r['amount'];
	                $category[$r['p']]['name']=$r['cname'];

	                $category[$r['p']]['cost'][$lbl]+=$r['cost'];
		            $category[$r['p']]['cost']['total']+=$r['cost'];

		            $category[$r['p']]['cost']['cm'][$last_lbl]=$category[$r['p']]['amount'][$last_lbl]-$category[$r['p']]['cost'][$last_lbl];
	                $category[$r['p']]['cost']['accum']= $category[$r['p']]['amount']['total']-$category[$r['p']]['cost']['total'];

	                if($category[$r['p']]['qty'][$lbl]!=0){
	                    $category[$r['p']]['avg'][$lbl]=($category[$r['p']]['amount'][$lbl]/$category[$r['p']]['qty'][$lbl]);
					}else{
	                    $category[$r['p']]['avg'][$lbl] = 0;
					}

					if($category[$r['p']]['qty']['total']!=0){
	                    $category[$r['p']]['avg']['total']=($category[$r['p']]['amount']['total']/$category[$r['p']]['qty']['total']);
					}else{
	                    $category[$r['p']]['avg']['total'] = 0;
					}


		            $category['total']['qty'][$lbl]+=$r['qty'];
		            $category['total']['qty']['total']+=$r['qty'];
		            $category['total']['amount'][$lbl]+=$r['amount'];
		            $category['total']['amount']['total']+=$r['amount'];

		            $category['total']['cost'][$lbl]+=$r['cost'];
		            $category['total']['cost']['total']+=$r['cost'];

		            $category['total']['cost']['cm'][$last_lbl]=$category['total']['amount'][$last_lbl]-$category['total']['cost'][$last_lbl];
		            $category['total']['cost']['accum']= $category['total']['amount']['total']-$category['total']['cost']['total'];

		            if($category['total']['qty'][$lbl]!=0){
	                    $category['total']['avg'][$lbl]=($category['total']['amount'][$lbl]/$category['total']['qty'][$lbl]);
					}else{
	                    $category['total']['avg'][$lbl] = 0;
					}

					if($category['total']['qty']['total']!=0){
	                    $category['total']['avg']['total']=($category['total']['amount']['total']/$category['total']['qty']['total']);
					}else{
	                    $category['total']['avg']['total'] = 0;
					}

	                $sku[$r['p']]['category_id']=$r['p'];
					$sku[$r['p']]['description']=$r['cname'];

					$table[$r['p']]['qty'][$lbl]+=$r['qty'];
					$table[$r['p']]['amount'][$lbl]+=$r['amount'];
				    $table[$r['p']]['qty']['total'] += $r['qty'];
				    $table[$r['p']]['amount']['total'] += $r['amount'];
		        }
		        //$label = $this->generate_months($start_date, $end_date, 'Ym', 'M Y');
			}
			$con_multi->sql_freeresult();

			$con_multi->sql_query($sql2) or die(sql_error());//print "$sql2<br /><br />";
			if($con_multi->sql_numrows()>0){
	            foreach($con_multi->sql_fetchrowset() as $r){
	                $lbl = sprintf("%02d%02d", $r['year'], $r['month']);
	                if($r['p']==''&&$r['cname']==''){
						$r['p']=0;
						$r['cname']='Un-categorized';
					}

	                if($category[$r['p']]['highest_sales'][$lbl]<$r['highest_sales']){
	                    $category[$r['p']]['highest_sales'][$lbl] = $r['highest_sales'];
					}
					if($category[$r['p']]['highest_sales']['total']<$r['highest_sales']){
	                    $category[$r['p']]['highest_sales']['total'] = $r['highest_sales'];
					}
					if($category[$r['p']]['lowest_sales'][$lbl]==''||$category[$r['p']]['lowest_sales'][$lbl]>$r['lowest_sales']){
	                    $category[$r['p']]['lowest_sales'][$lbl] = $r['lowest_sales'];
					}
					if($category[$r['p']]['lowest_sales']['total']==''||$category[$r['p']]['lowest_sales']['total']>$r['lowest_sales']){
	                    $category[$r['p']]['lowest_sales']['total'] = $r['lowest_sales'];
					}

					if($category['total']['highest_sales'][$lbl]<$r['highest_sales']){
	                    $category['total']['highest_sales'][$lbl] = $r['highest_sales'];
					}
					if($category['total']['highest_sales']['total']<$r['highest_sales']){
	                    $category['total']['highest_sales']['total'] = $r['highest_sales'];
					}
					if($category['total']['lowest_sales'][$lbl]==''||$category['total']['lowest_sales'][$lbl]>$r['lowest_sales']){
	                    $category['total']['lowest_sales'][$lbl] = $r['lowest_sales'];
					}
					if($category['total']['lowest_sales']['total']==''||$category['total']['lowest_sales']['total']>$r['lowest_sales']){
	                    $category['total']['lowest_sales']['total'] = $r['lowest_sales'];
					}
	           	}
	        }
			$con_multi->sql_freeresult();
		}


		if(count($label)>0){
            ksort($label);
		}
		$pixel_width = 10*$ln;
  		$class = "c".$level;
		$class2 = $class."_2";

		if(count($table)>0){
            foreach($subcats as $cat){
                $idx=$cat['id'];
				$r=$table[$idx];

				$tbody_id = $parent_id."_".$idx;
				// Description and Expand image
	            print "<tbody id=$tbody_id><tr><td class=$class nowrap>";
	            print "<img width=$pixel_width height=5px src='/ui/pixel.gif'>";
	            // Check whether got child or not
	            $con_multi->sql_query('select * from category where root_id='.mi($idx)) or die(mysql_error());
	            if($idx==0){
					print $cat['description'];
				}else{
				    if(count($r)>0){
				        print "&nbsp;<img src='/ui/icons/table.png' onclick='load_sku({$idx},this);' align=absmiddle>";
					}else{
                        print "<img width=20px height=5px src='/ui/pixel.gif'>";
					}
                    print $cat['description'];
				    if($con_multi->sql_numrows()>0){
				        if(count($r)>0){
	                    print "&nbsp;<img src='/ui/expand.gif' onclick='load_child({$idx},this,$ln);' align=absmiddle>";
	                    }
					}else{
	                    //print "&nbsp;<img src='/ui/icons/table.png' onclick='load_sku({$idx},this);' align=absmiddle>";
					}
					$con_multi->sql_freeresult();

				}

				print "</td>";

				// End of Description and Expand image

				// Qunatity
				$count=0;
				$temp=0;

				foreach($label as $lbl=>$day){
					$count++;
					$temp += $category[$idx]['qty'][$lbl];

					if($count==9){
						print "<td class=\"r $class\">";
						if($temp!=0){
							print $temp;
						}else{
							print "-";
						}
						print "</td>";
					}else if($count>9){
                        print "<td class=\"r $class\">";
						if($category[$idx]['qty'][$lbl]!=0){
							print $category[$idx]['qty'][$lbl];
						}else{
							print "-";
						}
						print "</td>";
					}
				}
				print "<td class=\"r $class\">".$category[$idx]['qty']['total']."</td>";
				// End of Quantity

				// Amount
				$count=0;
				$temp=0;

				foreach($label as $lbl=>$day){
					$count++;
					$temp += $category[$idx]['amount'][$lbl];

					if($count==9){
						print "<td class=\"r $class2\">";
						if($temp!=0){
                            print sprintf("%.02f",$temp);
						}else{
							print "-";
						}
						print "</td>";
					}else if($count>9){
                        print "<td class=\"r $class2\">";
                        if($category[$idx]['amount'][$lbl]!=0){
                            print sprintf("%.02f",$category[$idx]['amount'][$lbl]);
						}else{
							print "-";
						}

						print "</td>";
					}
				}
				print "<td class=\"r $class2\">".sprintf("%.02f",$category[$idx]['amount']['total'])."</td>";
				// End of Amount

				// C.Month
				print "<td class=\"r $class\">";
				if($category['total']['amount'][$last_lbl]!=0){
				    $temp = (($category[$idx]['amount'][$last_lbl]/$category['total']['amount'][$last_lbl])*100);
				    if($temp!=0){
                        print sprintf("%.02f",$temp)."%";
					}else{
						print "-";
					}
				}else{
					print "-";
				}
				print "</td>";
				// End of C.Month

				// Accum
				print "<td class=\"r $class\">";
				if($category['total']['amount']['total']!=0){
				    $temp = (($category[$idx]['amount']['total']/$category['total']['amount']['total'])*100);
				    if($temp!=0){
                        print sprintf("%.02f",$temp)."%";
					}else{
						print "-";
					}
				}else{
					print "-";
				}
				print "</td>";
				// End of Accum
                if (privilege('SHOW_REPORT_GP')){
					// Gross Profit
	                print "<td class=\"r $class2\">";            // CM.Amt
	                if($category[$idx]['cost']['cm'][$last_lbl]!=0){
	                    print sprintf("%.02f",$category[$idx]['cost']['cm'][$last_lbl]);
					}else{
						print "-";
					}
					print "</td>";
					print "<td class=\"r $class2\">";            // CM %
					if($category['total']['amount'][$last_lbl]!=0){
					    $temp = $category[$idx]['cost']['cm'][$last_lbl]/$category['total']['amount'][$last_lbl];
					    print sprintf("%.02f",$temp*100)."%";
					}else{
						print "-";
					}
	                print "</td>";
	                print "<td class=\"r $class2\">";            // Accum.Amt
	                if($category[$idx]['cost']['accum']!=0){
	                    print sprintf("%.02f",$category[$idx]['cost']['accum']);
					}else{
						print "-";
					}
					print "</td>";
					print "<td class=\"r $class2\">";            // Accum %
					if($category['total']['amount']['total']!=0){
					    $temp = $category[$idx]['cost']['accum']/$category['total']['amount']['total'];
					    print sprintf("%.02f",$temp*100)."%";
					}else{
						print "-";
					}
	                print "</td>";
					// End of Gross Profit
				}
				
				// AVG S.price
				print "<td class=\"r $class\">";            // AVG CM
                if($category[$idx]['avg'][$last_lbl]!=0){
                    print sprintf("%.02f",$category[$idx]['avg'][$last_lbl]);
				}else{
					print "-";
				}
				print "</td>";
				print "<td class=\"r $class\">";            // AVG Accum
                if($category[$idx]['avg']['total']!=0){
                    print sprintf("%.02f",$category[$idx]['avg']['total']);
				}else{
					print "-";
				}
				print "</td>";
				// End of AVG S.price

				// S.Price
				print "<td class=\"r $class2\">";        // S.Price CM High
				if($category[$idx]['highest_sales'][$last_lbl]!=0){
                    print sprintf("%.02f",$category[$idx]['highest_sales'][$last_lbl]);
				}else{
					print "-";
				}
				print "</td>";
				print "<td class=\"r $class2\">";        // S.Price CM Low
				if($category[$idx]['lowest_sales'][$last_lbl]!=0){
                    print sprintf("%.02f",$category[$idx]['lowest_sales'][$last_lbl]);
				}else{
					print "-";
				}
				print "</td>";
				print "<td class=\"r $class2\">";        // S.Price AM High
				if($category[$idx]['highest_sales']['total']!=0){
                    print sprintf("%.02f",$category[$idx]['highest_sales']['total']);
				}else{
					print "-";
				}
				print "</td>";
				print "<td class=\"r $class2\">";        // S.Price AM Low
				if($category[$idx]['lowest_sales']['total']!=0){
                    print sprintf("%.02f",$category[$idx]['lowest_sales']['total']);
				}else{
					print "-";
				}
				print "</td>";
				// End of S.Price
				print "</tr></tbody>";
			}
		}
	}

	function make_sku_sql($bid,$tbl_name,$filter){
	    $tbl = $tbl_name['sku_items_sales_cache'];
	    
	    if($bid>10000){ // branch group
		    $bgid = $bid-10000;
			foreach($this->branch_group['items'][$bgid] as $r){
                $bid_list[] = mi($r['branch_id']);
			}
		}else{
            $bid_list[] = $bid; // single branch
		}
		
        $sql = "select sku_item_code as p,sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,si.description,sum(cost) as cost
from $tbl pos
left join sku_items si on sku_item_id = si.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.category_id = category.id
where $filter group by sku_item_id";

        /*$sql2 = "select sku_item_code as p,sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,sum(cost) as cost,max(amount) as highest_sales,min(amount) as lowest_sales
from $tbl pos
left join sku_items on sku_item_id = sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.category_id = category.id
where $filter group by sku_item_id";*/

		$sql2 = "select sku_item_code as p,sku_item_id,si.description,max((price-discount)/pi.qty) as highest_sales,min((price-discount)/pi.qty) as lowest_sales
from pos
left join pos_items pi on pi.pos_id=pos.id and pi.branch_id=pos.branch_id and pi.counter_id=pos.counter_id and pi.date=pos.date
left join sku_items si on pi.sku_item_id = si.id
left join sku on si.sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.category_id = category.id
where $filter and pos.cancel_status=0 and pi.price>0 and pos.branch_id in (".join(',', $bid_list).") group by pi.sku_item_id";
        //print $sql2;

        return array('sql1'=>$sql,'sql2'=>$sql2);
	}
	
	function view_sku_by_second_last(){
		global $con,$con_multi,$smarty,$sessioninfo;

		$code = $_REQUEST['hidden_code'];
		$branch_id = $_REQUEST['hidden_branch_id'];
		$filter = $_REQUEST['hidden_filter'];
		$start_date = $_REQUEST['hidden_start_date'];
		$end_date = $_REQUEST['hidden_end_date'];
		$date_msg = $_REQUEST['hidden_date_msg'];
        $branch_name =  get_branch_code($branch_id);
		$branch_group = $this->load_branch_group();
		
        if($code==0){
			$code="null";
			$level=0;
			$category_name = 'Un-categorized';

			$filter .= " and p$level is $code";
		}else{
            $con_multi->sql_query('select * from category where id='.mi($code)) or die(mysql_error());
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$level = $temp['level'];
			$category_name = $temp['description'];

    		// check one more level for grouping
    		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

        	$filter .= " and p$level = $code";
		}

        if(strpos($_REQUEST['hidden_branch_id'],'bg,')===0){   // is branch group
            list($dummy,$bg_id) = explode(",",$_REQUEST['hidden_branch_id']);
            
            foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
            	$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".mi($tmp_bid);
				$sql_list[] = $this->make_sku_sql($tmp_bid,$tbl_name,$filter);
            }
        }else{
            $bid = intval($_REQUEST['hidden_branch_id']);
            if($bid==0){
				$branch_name = "All";
				$b0 = $con_multi->sql_query("select id from branch where active=1 order by sequence,code");
				while($b = $con_multi->sql_fetchrow($b0))
				{
				    $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$b['id'];
    				$sql_list[] = $this->make_sku_sql($b['id'],$tbl_name,$filter);
				}
				$con_multi->sql_freeresult($b0);
				/*if($branch_group['header']){
					foreach($branch_group['header'] as $bg_id=>$bg){
                        $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_bg".mi($bg_id);
						$sql_list[] = $this->make_sku_sql($bg_id+10000,$tbl_name,$filter);

					}
				}*/
			}else{
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
    			$sql_list[] = $this->make_sku_sql($bid,$tbl_name,$filter);
			}
		}
		
		foreach($sql_list as $ss){
			$sql = $ss['sql1'];
			$sql2 = $ss['sql2'];
			//if($sessioninfo['id']==1)   print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());//print "$sql<br /><br />";

			if($con_multi->sql_numrows()>0){
		        foreach($con_multi->sql_fetchrowset() as $r){
					if($r['p']==''&&$r['description']==''){
						$r['p']='Un-categorized';
						$r['description']='Un-categorized';
					}

		            $category['total']['qty']+=$r['qty'];
		            $category['total']['amount']+=$r['amount'];
		            $category['total']['cost']+=$r['cost'];
		            $category['total']['cost_amt']=$category['total']['amount']-$category['total']['cost'];

		            if($category['total']['amount']!=0){
	                    $category['total']['cost_per']=($category['total']['cost_amt']/$category['total']['amount'])*100;
					}else{
	                    $category['total']['cost_per'] = 0;
					}

					if($category['total']['qty']!=0){
	                    $category['total']['avg']=$category['total']['amount']/$category['total']['qty'];
					}

		            $sku[$r['p']]['sku_item_code']=$r['p'];
					$sku[$r['p']]['description']=$r['description'];

					$table[$r['p']]['qty']+=$r['qty'];
	       			$table[$r['p']]['amount']+= $r['amount'];
	       			$table[$r['p']]['cost']+= $r['cost'];
	       			$table[$r['p']]['cost_amt']=$table[$r['p']]['amount']-$table[$r['p']]['cost'];

	       			if($table[$r['p']]['amount']!=0){
	                    $table[$r['p']]['cost_per']=($table[$r['p']]['cost_amt']/$table[$r['p']]['amount'])*100;
					}else{
	                    $table[$r['p']]['cost_per'] = 0;
					}

					if($table[$r['p']]['qty']!=0){
	                    $table[$r['p']]['avg']=$table[$r['p']]['amount']/$table[$r['p']]['qty'];
					}else{
	                    $table[$r['p']]['avg'] = 0;
					}

	            }
	        }
			$con_multi->sql_freeresult();

	        $con_multi->sql_query($sql2) or die(mysql_error());//print "$sql2<br /><br />";

			if($con_multi->sql_numrows()>0){
		        foreach($con_multi->sql_fetchrowset() as $r){
		            if($r['p']==''&&$r['description']==''){
						$r['p']='Un-categorized';
						$r['description']='Un-categorized';
					}

		            if($table[$r['p']]['highest_sales']<$r['highest_sales']){
	                    $table[$r['p']]['highest_sales'] = $r['highest_sales'];
					}

					if($table[$r['p']]['lowest_sales']==''||$table[$r['p']]['lowest_sales']>$r['lowest_sales']){
	                    $table[$r['p']]['lowest_sales'] = $r['lowest_sales'];
					}

					if($category['total']['highest_sales']<$r['highest_sales']){
	                    $category['total']['highest_sales'] = $r['highest_sales'];
					}

					if($category['total']['lowest_sales']==''||$category['total']['lowest_sales']>$r['lowest_sales']){
	                    $category['total']['lowest_sales'] = $r['lowest_sales'];
					}
		        }
		 	}
			$con_multi->sql_freeresult();
		}
        
        //print_r($category);

        $smarty->assign('category',$category);
        $smarty->assign('sku',$sku);
        $smarty->assign('table',$table);
        $smarty->assign('branch_code',$branch_name);
        $smarty->assign('category_name',$category_name);
		$smarty->display('report.quaterly_sales_by_sku_category.view_sku.tpl');
	}
	
	function export_itemise_info(){
		global $config, $smarty, $con, $sessioninfo;

		include_once("include/excelwriter.php");
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Quarterly Sales by Category / SKU Items Report To Excel (Itemize)");
		$smarty->assign('no_header_footer', 1);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->view_sku_by_second_last();
		print ExcelWriter::GetFooter();
		exit;
	}
}

//$con_multi = new mysql_multi();
$report = new DailySales('Quarterly Sales by Category / SKU Items');
//$con_multi->close_connection();
?>
