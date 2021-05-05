<?php
/*
1/17/2011 4:06:17 PM Alex
- change use report_server

2/25/2011 2:29:12 PM Andy
- Optimize report speed.

3/25/2011 4:18:05 PM Andy
- remove some testing script

7/1/2011 10:50:06 AM Andy
- Change report table "tmp_sid_list" to use mysql temporary table. 

10/14/2011 4:19:40 PM Alex
- Change use mf() check quantity instead of intval()

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

7/14/2015 4:30 PM Joo Chia
- Add in filter by vendor id and show selected vendor name above report
- Fix to show department as 'All' above report if select '-- All --'

10/10/2016 13:21 Qiu Ying
- Fix rowspan bug when export report

2/19/2020 11:15 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
//$con = new sql_db('smo-hq.arms.com.my','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_PERFORMANCE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_PERFORMANCE', BRANCH_CODE), "/index.php");
//$con = new sql_db('hq.aneka.com.my','arms_slave','arms_slave','armshq');
//ini_set("display_errors", 1);
//ini_set('memory_limit','128M');

class block_sku extends Report
{
 var $category = array();
	var $sku = array();
	var $tmp_sid_list_tbl_name = '';
	
	function run($bid,&$table,&$total)
	{
	    global $con, $con_multi, $sessioninfo;
  		$standard_qty = $this->qty;
		$block = $this->block;
		$dept_id = $this->dept_id;
		$sku_type = $this->sku_type;
		$vend_id = $this->vend_id;
		
  		$block_serial = "%i:$bid;s:2:\"on\";%";

  		$filter[] = "date between ".ms($this->date_from)." and ".ms($this->date_to);
		//$filter[] = "p2=".mi($dept_id);
		if (!$this->all_dept) $si_filter[] = "p2=".mi($dept_id);
		if($sku_type){
			$si_filter[] = "sku.sku_type=".ms($sku_type);
		}
		
		if($block==1){
			$si_filter[] = "si.block_list like ".ms($block_serial);
		}else{
			$si_filter[] = "(si.block_list not like ".ms($block_serial)." or si.block_list is null or si.block_list='')";
		}
		
		if($vend_id){
			$si_filter[] = "sku.vendor_id=".mi($vend_id);
		}
		
		$filter = join(" and ",$filter);
		$si_filter = join(' and ', $si_filter);

		// old method
		/*$sql = "select year,month,sku_item_code,mcode,artno,pos.sku_item_id,sku_items.description,p3 as p,category.description as cname,sum(qty) as qty,sku_items.block_list as b,
		(select vendor_id from vendor_sku_history where sku_item_id = sku_items.id and added <=".ms($this->date_from)." and branch_id=$bid order by added desc limit 1) as vendor_id1,
		(select vendor_id from po_items left join po on po_items.po_id=po.id and po_items.branch_id=po.branch_id where sku_item_id = sku_items.id and added <=".ms($this->date_from)." and po_items.branch_id=$bid and approved=1 order by added desc limit 1) as vendor_id2,
		sku.vendor_id as vendor_id3
from sku_items_sales_cache_b$bid pos
left join sku_items on pos.sku_item_id=sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter group by sku_item_code,year,month order by p,sku_item_code";*/

		if(!$con_multi->sql_query("explain ".$this->tmp_sid_list_tbl_name,false,false)){  // no such table, need create
			$con_multi->sql_query("create temporary table if not exists ".$this->tmp_sid_list_tbl_name." (
				select si.id
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				where $si_filter
			)");
		}

		$q_si = $con_multi->sql_query("select id from ".$this->tmp_sid_list_tbl_name);
		$total_sid_count = $con_multi->sql_numrows($q_si);
		$sid_count = 0;
		$sid_list = array();
		while($si = $con_multi->sql_fetchassoc($q_si)){
            $sid_list[] = mi($si['id']);
            $sid_count ++;
            if(count($sid_list)>=1000){
                //$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'before '.$sid_count.' of '.$total_sid_count, 'timestamp'=>'CURRENT_TIMESTAMP')));
                $this->generate_sku_data($table, $total, $bid, $sid_list);
                $sid_list = array();
                //$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> $sid_count.' of '.$total_sid_count, 'timestamp'=>'CURRENT_TIMESTAMP')));
                
                // try send some data to browser to keep connection alive
                //print "<span style='display:none'>&nbspl</span>";
                //ob_flush();
			}  
		}
		if($sid_list){
            //$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'before '.$sid_count.' of '.$total_sid_count, 'timestamp'=>'CURRENT_TIMESTAMP')));
		    $this->generate_sku_data($table, $total, $bid, $sid_list);
            //$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> $sid_count.' of '.$total_sid_count, 'timestamp'=>'CURRENT_TIMESTAMP')));
		}  
		$con_multi->sql_freeresult($q_si);
		
		//print_r($total);
	}
	
	private function generate_sku_data(&$table, &$total, $bid, $sid_list){
	    global $con, $con_multi, $sessioninfo;
	    
  		$block_serial = "%i:$bid;s:2:\"on\";%";
        $standard_qty = $this->qty;
        
  		$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
  		$filter[] = "pos.sku_item_id in (".join(',', $sid_list).")";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		$filter = join(" and ",$filter);
		
	    $sql = "select year,month,sku_item_code,mcode,artno,pos.sku_item_id,sku_items.description,p3 as p,category.description as cname,sum(qty) as qty, sku.vendor_id as vendor_id
from sku_items_sales_cache_b$bid pos
left join sku_items on pos.sku_item_id=sku_items.id
left join sku on sku_id = sku.id
left join category_cache using (category_id)
left join category on category_cache.p3 = category.id
where $filter group by sku_item_code,year,month order by p,sku_item_code";
		if($sessioninfo['u']=='wsatp'){
			//print "$sql<br /><br />";
			//return;
		}
        $q1 = $con_multi->sql_query($sql) or die(sql_error());//print "$sql<br /><br />";//xx
		if($con_multi->sql_numrows($q1)>0){
		    while($r = $con_multi->sql_fetchrow($q1)){
		        $lbl = sprintf('%04d%02d',$r['year'],$r['month']);
		        $sid = mi($r['sku_item_id']);
		        
          		//$lable[$lbl] = $this->months[$r['month']]." ".$r['year'];
		        $key = $r['sku_item_code'];

				$this->category[$r['p']]['id'] = $r['p'];
                $this->category[$r['p']]['cname'] = $r['cname'];

                $this->sku[$key]['code'] = $key;
                $this->sku[$key]['description'] = $r['description'];
                $this->sku[$key]['mcode'] = $r['mcode'];
                $this->sku[$key]['artno'] = $r['artno'];

                $q_v = $con_multi->sql_query("select vendor_id
				from vendor_sku_history
				where sku_item_id = $sid and added<=".ms($this->date_from)." and branch_id=$bid
				order by added desc limit 1");
				$grn_vendor = $con_multi->sql_fetchrow($q_v);
				$con_multi->sql_freeresult($q_v);
				
				if($grn_vendor){    // use grn vendor
                    $this->sku[$key]['vendor_id'] = $grn_vendor['vendor_id'];
					$this->sku[$key]['vendor_type'] = 'grn';
				}else{
                    $q_v = $con_multi->sql_query("select vendor_id
					from po_items
					left join po on po_items.po_id=po.id and po_items.branch_id=po.branch_id
					where sku_item_id = $sid and po.po_date <=".ms($this->date_from)." and po_items.branch_id=$bid and po.approved=1 and po.active=1
					order by po.po_date desc limit 1");
					$po_vendor = $con_multi->sql_fetchrow($q_v);
					$con_multi->sql_freeresult($q_v);
					if($po_vendor){ // use po vendor
                        $this->sku[$key]['vendor_id'] = $po_vendor['vendor_id'];
                    	$this->sku[$key]['vendor_type'] = 'po';
					}else{  // use master vendor
                        $this->sku[$key]['vendor_id'] = $r['vendor_id'];
                    	$this->sku[$key]['vendor_type'] = 'master';
					}
				}

                $table[$r['p']][$key]['code'] = $key;
				$table[$r['p']][$key]['qty'][$lbl] += $r['qty'];
				$table[$r['p']][$key]['qty']['total'] += $r['qty'];

				if($standard_qty<=0){
                    $total[$r['p']]['qty'][$lbl] += $r['qty'];
					$total[$r['p']]['qty']['total'] += $r['qty'];
					$total['total']['qty'][$lbl] += $r['qty'];
					$total['total']['qty']['total'] += $r['qty'];
				}
			}
		}
		$con_multi->sql_freeresult($q1);
	}
	
	function sort_table($a,$b)
	{
	    if ($a['qty']['total']==$b['qty']['total']) return 0;
        return ($a['qty']['total']>$b['qty']['total']) ? -1 : 1;
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi, $sessioninfo;
      
        /*$con->sql_query("create table if not exists tmp_report_process_monitor(
			id int auto_increment,
			type char(20),
			user_id int,
			info char(100),
			timestamp timestamp,
			primary key(type, user_id, id)
		)");*/
		//$con->sql_query("delete from tmp_report_process_monitor where user_id=".mi($sessioninfo['id'])." and type='block_sku'");
		
        //$con_multi= new mysql_multi();
        //$con_multi = $con;
  		$bid  = get_request_branch(true);

		$this->date_to = $_REQUEST['date_to'];
		$this->date_from = $_REQUEST['date_from'];

        $mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
    
		// Get department ID
        $this->dept_id = $_REQUEST['dept_id'];
		if ($this->dept_id){
			$con_multi->sql_query("select description from category where id=".mi($this->dept_id)) or die(mysql_error());
			$dept_name = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		} else {
			$dept_name = "All";
		}

        // get Min or max Qty
        $this->min_or_max = $_REQUEST['min_or_max'];
        $this->qty = mf($_REQUEST['quantity']);
        $this->monthly_or_total = $_REQUEST['monthly_or_total'];
        $this->sku_type = $_REQUEST['sku_type'];
        
		// get Block or Unblocl (1=block), (0=unblock)
		$this->block = intval($_REQUEST['block']);
		
		// get Vendor ID
		$this->vend_id = $_REQUEST['v_id'];
		if($this->vend_id){
			$con_multi->sql_query("select description from vendor where id=".mi($this->vend_id)) or die(mysql_error());
			$vendor_name = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		} else {
			$vendor_name = "All";
		}
		
		
		// start process
  		if($bid==0){
			$branch_name = "All";
			$b0 = $con_multi->sql_query("select id from branch");
			while($b = $con_multi->sql_fetchrow($b0))
			{
				$this->run($b['id'],$table,$total);
			}
			$con_multi->sql_freeresult($b0);
		}else{
			$branch_name =  get_branch_code($bid);
			$this->run($bid,$table,$total);
		}
		
		// generate month/year label
		$label = $this->generate_months($this->date_from, $this->date_to, 'Ym', 'M Y', true);

		// check for min qty and caculate total
		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'before calculate min max', 'timestamp'=>'CURRENT_TIMESTAMP')));
		$standard_qty = $this->qty;
		if($standard_qty>0){
		    if(count($table)>0){
                foreach($table as $dept_id=>$items){
					foreach($items as $code=>$r){
					    $qualify = true;
					    if($this->monthly_or_total=='monthly'){
                            foreach($label as $lbl=>$dummy){
							    if($this->min_or_max=='max'){
	                                if($r['qty'][$lbl]>$standard_qty){
										$qualify = false;
										break;
									}
								}else{
	                                if($r['qty'][$lbl]<$standard_qty){
										$qualify = false;
										break;
									}
								}

							}
						}else{
							if($this->min_or_max=='max'){
                                if($r['qty']['total']>$standard_qty){
									$qualify = false;
								}
							}else{
                                if($r['qty']['total']<$standard_qty){
									$qualify = false;
								}
							}
						}
						
						if($qualify){
							$table2[$dept_id][$code] = $r;
							foreach($label as $lbl=>$dummy){
								$total[$dept_id]['qty'][$lbl] += $r['qty'][$lbl];
								$total[$dept_id]['qty']['total'] += $r['qty'][$lbl];
								$total['total']['qty'][$lbl] += $r['qty'][$lbl];
								$total['total']['qty']['total'] += $r['qty'][$lbl];
							}
						}
					}
				}
			}
		}else{
			$table2 = $table;
		}
		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'after calculate min max', 'timestamp'=>'CURRENT_TIMESTAMP')));
		
		// count item in category and sort total
		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'before sortable', 'timestamp'=>'CURRENT_TIMESTAMP')));
		if(count($table2)>0){
            foreach($table2 as $dept_id=>$items){
	            $this->category[$dept_id]['item_count'] = count($items);
	            usort($items, array($this,"sort_table"));
	            //print_r($items);
	            //die();
	            $table2[$dept_id] = $items;
			}
		}
		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'after sortable', 'timestamp'=>'CURRENT_TIMESTAMP')));

 	//print_r($this->category);
		//die();
		
		//$con_multi->close_connection();
		
		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'before show table', 'timestamp'=>'CURRENT_TIMESTAMP')));
		$smarty->assign('label',$label);
		$smarty->assign('date_length',"from $this->date_from to $this->date_to");
		$smarty->assign('table',$table2);
		$smarty->assign('total',$total);
		$smarty->assign('sku',$this->sku);
		$smarty->assign('category',$this->category);
		$smarty->assign('dept_name',$dept_name);
  		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('vendor_name',$vendor_name);
		//var_dump($this->all_dept);
  		//$con->sql_query("insert into tmp_report_process_monitor ".mysql_insert_by_field(array('user_id'=>$sessioninfo['id'], 'type'=>'block_sku', 'info'=> 'show table', 'timestamp'=>'CURRENT_TIMESTAMP')));
	}

	function process_form()
	{
		global $smarty;
		// do my own form process
		$this->tmp_sid_list_tbl_name = 'tmp_sid_list_'.time();
		$this->all_dept = (mi($_REQUEST['dept_id']) == 0) ? true: false;
		
		$is_output_excel = false;
		if (isset($_REQUEST['output_excel']))
		{
			$is_output_excel = true;
			$smarty->assign("is_output_excel", $is_output_excel);
		}
		// call parent
		parent::process_form();
	}
	
	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$report = new block_sku('Block SKU Report');
//$con_multi->close_connection();
?>
