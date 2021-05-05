<?php

/*
5/4/2010 5:27:47 PM Alex
- Fix date and total result

1/24/2011 5:19:34 PM Alex
- change use report_server
- add department privilege filter

2/23/2011 2:37:59 PM Justin
- Modified the end date to calculate 3 months instead of 1 month.

7/6/2011 2:36:18 PM Andy
- Change split() to use explode()

5/9/2012 11:03:46 AM Andy
- Fix invalid branch group SQL.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Bug fixed on sql error once clicked on show itemise table.
- Enhanced to have export feature for itemise table.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/28/2014 3:43 PM Andy
- Fix php warning when submit the report without select any category.

2/19/2020 9:54 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE != 'HQ') js_redirect($LANG['REPORT_IS_HQ_ONLY'], "/index.php");

class DailySales extends Report
{
	var $where;
	
	function run($bid, $tbl_name, $lbl)
	{
	    global $con_multi,$smarty;
	    
	    $label = $this->label;
		$sku = $this->sku;
		$table = $this->table;
		$category = $this->category;
	    $filter = $this->filter;
	    $tbl = $tbl_name['sku_items_sales_cache'];
	    $sql = "select pos.year,pos.month,sum(qty) as qty,sum(pos.amount) as amount,p3 as p,category.description as cname,root_id,sum(cost) as cost
	from $tbl pos
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category_cache using (category_id)
	left join category on category_cache.p3 = category.id
	where $filter group by p,month,year order by p,year,month";

        //$lbl = $bid;
        //$label[$lbl] = get_branch_code($bid);
        
        $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";
		if($con_multi->sql_numrows()>0){
            foreach($con_multi->sql_fetchrowset() as $r){
				if($r['p']==''&&$r['cname']==''){
					$r['p']=0;
					$r['cname']='Un-categorized';
				}
				
				$category[$r['p']]['qty'][$lbl]+=$r['qty'];
	            $category[$r['p']]['qty']['total']+=$r['qty'];
	            $category[$r['p']]['amount'][$lbl]+=$r['amount'];
	            $category[$r['p']]['amount']['total']+=$r['amount'];
                $category[$r['p']]['name']=$r['cname'];
                
	            $category['total']['qty'][$lbl]+=$r['qty'];
	            $category['total']['qty']['total']+=$r['qty'];
	            $category['total']['amount'][$lbl]+=$r['amount'];
	            $category['total']['amount']['total']+=$r['amount'];
				
                $sku[$r['p']]['category_id']=$r['p'];
				$sku[$r['p']]['description']=$r['cname'];
                
				$table[$r['p']]['qty'][$lbl]+=$r['qty'];
				$table[$r['p']]['amount'][$lbl]+=$r['amount'];
			    $table[$r['p']]['qty']['total'] += $r['qty'];
			    $table[$r['p']]['amount']['total'] += $r['amount'];
	        }
		}
		$con_multi->sql_freeresult();
		
		$this->label = $label;
		$this->sku = $sku;
		$this->table = $table;
		$this->category = $category;
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
		global $con, $smarty,$start_date,$end_date;
		
		$multi_category_id = $_REQUEST['multi_category_id'];
		$branch_id = $_REQUEST['branch_id'];
		$branch_group = $this->branch_group;
		
		if(count($multi_category_id)>0){
		    if(count($branch_id)>0){
		        foreach($branch_id as $bid){
				    if($bid<0){   // is branch group
					    $bg_id = $bid * -1;
					    foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
					    	$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$tmp_bid;
			            	$this->run($tmp_bid,$tbl_name,$bg_id+10000);
			            	//$this->label[$tmp_bid] = get_branch_code($tmp_bid);
					    }

			            $this->label[$bg_id+10000] = $branch_group['header'][$bg_id]['code'];
			        }else{
			            $tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
			            $this->run($bid,$tbl_name, $bid);
			            $this->label[$bid] = get_branch_code($bid);
					}
				}
			}else{
	            $this->err[] = "Please select Branch";
	            $smarty->assign("err", $this->err);
			}
		}else{
            $this->err[] = "Please select Category";
	        $smarty->assign("err", $this->err);
		}

		$label = $this->label;
		$sku = $this->sku;
		$table = $this->table;
		$category = $this->category;
		
		if($label)
		{
        ksort($label);
    }
		
		$report_title = "From: ".$start_date." to ".$end_date;
		
		$smarty->assign('report_title',$report_title);
		$smarty->assign('label',$label);
		$smarty->assign('sku',$sku);
		$smarty->assign('table',$table);
		$smarty->assign('category',$category);
	}
	
	function process_form()
	{
		// do my own form process
		global $con,$smarty,$start_date,$end_date,$sessioninfo,$con_multi;
		
		// call parent
		parent::process_form();
		
		$start_date = $_REQUEST['date'];
	    $end_date =date("Y-m-d",strtotime("-1 day",strtotime("+3 month",strtotime($start_date))));

		$level = intval($_REQUEST['current_category_level']);
		$multi_category_id = $_REQUEST['multi_category_id'];
		if(!$multi_category_id)	$multi_category_id = array();
		$sku_type = $_REQUEST['sku_type'];

		$filter = array();

		$filter[] = "p$level in (".join(",",$multi_category_id).")";
		$filter[] = "p2 in ($sessioninfo[department_ids])";
		$filter[] = "pos.date between ".ms($start_date)." and ".ms($end_date);

		if($sku_type!='all'){
			$filter[] = "sku_type=".ms($sku_type);
		}

		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		$filter = join(" and ", $filter);

		$root_id = $_REQUEST['current_root_id'];
		
		$con_multi->sql_query("select level from category where id=".mi($root_id)) or die(mysql_error());

		$c_level = $con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
		if($c_level==1){
            $filter_dept =  "and id in ($sessioninfo[department_ids])";
		}

		if(!$c_level){
			$sql="select c.* from category c
							left join category_cache cc on cc.p1 = c.id
							where c.level=1 and cc.p2 in ($sessioninfo[department_ids])
							group by c.id";
		}else{
			$sql = "select * from category where root_id=".mi($root_id)." $filter_dept";
		}
		
		$con_multi->sql_query($sql) or die(mysql_error());
		$category_list = $con_multi->sql_fetchrowset();
		$con_multi->sql_freeresult();
		$smarty->assign('category_list',$category_list);
		$con_multi->sql_query('select description,level from category where id='.mi($root_id)) or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$root_category = $temp['description'];
		$level = $temp['level']+1;

        $this->filter = $filter;
        
		$smarty->assign("root_id",$root_id);
		$smarty->assign("root_category",$root_category);
		$smarty->assign("level",$level);
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
	
	function load_child()
	{
		global $con, $con_multi;
		$_REQUEST['branch_id'] = $_REQUEST['branch_id'];
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
    		
		//$branch_list = split('[,]', $branch_id);
		$bid_list = array();
		$bid_to_lbl = array();
		foreach($branch_id as $bid){
		    if($bid<0){   // is branch group
			    $bg_id = $bid * -1;
			    foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
			    	$bid_list[] = $tmp_bid;
					$bid_to_lbl[$tmp_bid] = $bg_id+10000;
			    }
			    $label[$bg_id+10000] = $branch_group['header'][$bg_id]['code'];
		    }else{
		    	$bid_list[] = $bid;
		    	$bid_to_lbl[$bid] = $bid;
		    	$label[$bid] = get_branch_code($bid);
			}		
        }
        
        if($bid_list){
			foreach($bid_list as $bid){
				$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
		        $lbl = $bid_to_lbl[$bid];
		        
		        $tbl = $tbl_name['sku_items_sales_cache'];
				$sql = "select pos.year,pos.month,sum(qty) as qty,sum(pos.amount) as amount,p$one_more_level as p,sum(cost) as cost
				from $tbl pos
				left join sku_items on sku_item_id = sku_items.id
				left join sku on sku_id = sku.id
				left join category_cache using (category_id)
				where $filter and p$level=$code group by p,month,year order by p,year,month";
	
				$con_multi->sql_query($sql) or die(mysql_error());//print "$sql<br /><br />";
	
				if($con_multi->sql_numrows()>0){
			            foreach($con_multi->sql_fetchrowset() as $r){
	
		                if($r['p']==''&&$r['cname']==''){
							$r['p']=0;
							$r['cname']='Un-categorized';
						}
	
						$category[$r['p']]['qty'][$lbl]+=$r['qty'];
			            $category[$r['p']]['qty']['total']+=$r['qty'];
			            $category[$r['p']]['amount'][$lbl]+=$r['amount'];
			            $category[$r['p']]['amount']['total']+=$r['amount'];
		                $category[$r['p']]['name']=$r['cname'];
	
			            $category['total']['qty'][$lbl]+=$r['qty'];
			            $category['total']['qty']['total']+=$r['qty'];
			            $category['total']['amount'][$lbl]+=$r['amount'];
			            $category['total']['amount']['total']+=$r['amount'];
	
		                $sku[$r['p']]['category_id']=$r['p'];
						$sku[$r['p']]['description']=$r['cname'];
	
						$table[$r['p']]['qty'][$lbl]+=$r['qty'];
						$table[$r['p']]['amount'][$lbl]+=$r['amount'];
					    $table[$r['p']]['qty']['total'] += $r['qty'];
					    $table[$r['p']]['amount']['total'] += $r['amount'];
			        }
				}
				$con_multi->sql_freeresult();
			}
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
					}
					$con_multi->sql_freeresult();
				}
	            
				print "</td>";
				
				// End of Description and Expand image
				$needchange = 1;
				
				foreach($label as $lbl=>$b){
				    if($needchange == 1){
                        print "<td class=\"r $class2\">";
					}else{
                        print "<td class=\"r $class\">";
					}

                    if($category[$idx]['qty'][$lbl]!=0){
                        print $category[$idx]['qty'][$lbl];
					}else{
                        print "-";
					}
                    print "</td>";
                    
                    if($needchange == 1){
                        print "<td class=\"r $class2\">";
                        $needchange = 0;
					}else{
                        print "<td class=\"r $class\">";
                        $needchange = 1;
					}
					
                    if($category[$idx]['amount'][$lbl]!=0){
                        print sprintf("%.02f",$category[$idx]['amount'][$lbl]);
					}else{
                        print "-";
					}
                    print "</td>";
				}
				
					print "<td class=\"r $class\">";
                    if($category[$idx]['qty']['total']!=0){
                        print $category[$idx]['qty']['total'];
					}else{
                        print "-";
					}
                    print "</td>";

                    print "<td class=\"r $class\">";
                    if($category[$idx]['amount']['total']!=0){
                        print sprintf("%.02f",$category[$idx]['amount']['total']);
					}else{
                        print "-";
					}
                    print "</td>";
				print "</tr></tbody>";
			}
		}
	}
	
	function view_sku_by_second_last(){
		global $con,$con_multi,$smarty;
		
		$code = $_REQUEST['hidden_code'];
		$branch_id = explode(",",$_REQUEST['hidden_branch_id']);
		$filter = $_REQUEST['hidden_filter'];
		$start_date = $_REQUEST['hidden_start_date'];
		$end_date = $_REQUEST['hidden_end_date'];

        $branch_group = $this->load_branch_group();
        
        $branch_list = $branch_id;
        
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
			
        $bid_list = $bid_to_lbl = array();
        foreach($branch_list as $bid){
		    if($bid<0){   // is branch group
			    $bg_id = $bid * -1;
			    
			    foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
			    	$bid_list[] = $tmp_bid;
					$bid_to_lbl[$tmp_bid] = $bg_id+10000;    	
			    }
			    
		        $label[$bg_id+10000] = $branch_group['header'][$bg_id]['code'];
		        $code = $branch_group['header'][$bg_id]['code'];
		    }else{
		    	$bid_list[] = $bid;
		    	$bid_to_lbl[$bid] = $bid;
		        $label[$bid] = get_branch_code($bid);
                $code = get_branch_code($bid);
			}
			
            $branch_name .=  ", ".$code;
        }
        
        foreach($bid_list as $bid){
        	$tbl_name['sku_items_sales_cache'] = "sku_items_sales_cache_b".$bid;
			$lbl = $bid_to_lbl[$bid];
			
        	$tbl = $tbl_name['sku_items_sales_cache'];
	        $sql = "select sku_item_code as p,sku_item_id,sum(pos.qty) as qty,sum(pos.amount) as amount,sku_items.description,sum(cost) as cost
	from $tbl pos
	left join sku_items on sku_item_id = sku_items.id
	left join sku on sku_id = sku.id
	left join category_cache using (category_id)
	left join category on category_cache.category_id = category.id
	where $filter group by sku_item_id";

	        $con_multi->sql_query($sql) or die(mysql_error());//print "$sql<br /><br />";

			if($con_multi->sql_numrows()>0){
		        foreach($con_multi->sql_fetchrowset() as $r){
		            if($r['p']==''&&$r['description']==''){
						$r['p']='Un-categorized';
						$r['description']='Un-categorized';
					}
					
	                $category[$r['p']]['qty'][$lbl]+=$r['qty'];
		            $category[$r['p']]['qty']['total']+=$r['qty'];
		            $category[$r['p']]['amount'][$lbl]+=$r['amount'];
		            $category[$r['p']]['amount']['total']+=$r['amount'];

		            $category['total']['qty'][$lbl]+=$r['qty'];
		            $category['total']['qty']['total']+=$r['qty'];
		            $category['total']['amount'][$lbl]+=$r['amount'];
		            $category['total']['amount']['total']+=$r['amount'];

	                $sku[$r['p']]['sku_item_code']=$r['p'];
					$sku[$r['p']]['description']=$r['description'];

					$table[$r['p']]['qty'][$lbl]+=$r['qty'];
					$table[$r['p']]['amount'][$lbl]+=$r['amount'];
				    $table[$r['p']]['qty']['total'] += $r['qty'];
				    $table[$r['p']]['amount']['total'] += $r['amount'];

	            }
	        }
        	$con_multi->sql_freeresult();
        }
        
        if(count($label)>0){
            ksort($label);
		}
		
        $smarty->assign('category',$category);
        $smarty->assign('sku',$sku);
        $smarty->assign('table',$table);
        $smarty->assign('label',$label);
        $smarty->assign('category_name',$category_name);
		$smarty->display('report.quaterly_branch_sales_by_sku_category.view_sku.tpl');
	}
	
	function load_cat(){
		global $con,$smarty,$sessioninfo,$con_multi;
		
		$root_id = $_REQUEST['root_id'];

		if(isset($_REQUEST['go_back'])&&$_REQUEST['go_back']==1){
            $con_multi->sql_query("select root_id from category where id=".mi($root_id)) or die(mysql_error());
            $temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
            $root_id = $temp['root_id'];
		}
		// get current catergory level
		$con_multi->sql_query("select level from category where id=".mi($root_id)) or die(mysql_error());
		$c_level = $con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
		if($c_level==1){
            $filter_dept =  "and id in ($sessioninfo[department_ids])";
		}

		if(!$c_level){
			$sql="select c.* from category c
							left join category_cache cc on cc.p1 = c.id
							where c.level=1 and cc.p2 in ($sessioninfo[department_ids])
							group by c.id";
		}else{
			$sql = "select * from category where root_id=".mi($root_id)." $filter_dept";
		}

		$con_multi->sql_query($sql) or die(mysql_error());
		$smarty->assign('category_list',$con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		$con_multi->sql_query('select description,level from category where id='.mi($root_id)) or die(mysql_error());
		$temp = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		$root_category = $temp['description'];
		$level = $temp['level']+1;
			
		$smarty->assign("root_id",$root_id);
		$smarty->assign("root_category",$root_category);
		$smarty->assign("level",$level);
		$smarty->display('category_multiple.tpl');
	}
	
	function export_itemise_info(){
		global $config, $smarty, $con, $sessioninfo;

		include_once("include/excelwriter.php");
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Quarterly Sales by Category / SKU Items Report To Excel (Itemize)");

		$smarty->assign('no_header_footer', 1);
		$this->view_sku_by_second_last();
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
		print ExcelWriter::GetHeader();
	}
}
//$con_multi = new mysql_multi();
$report = new DailySales('Quarterly Branch Sales by Category / SKU Items');
//$con_multi->close_connection();
?>
