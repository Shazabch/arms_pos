<?php
/*
1/21/2011 5:22:39 PM Alex
- change use report_server

2/14/2011 12:23:46 PM Andy
- Fix SQL error when left join.

6/24/2011 6:32:51 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:41:25 PM Andy
- Change split() to use explode()

11/14/2011 4:31:25 PM Andy
- Add vendor filter

12/19/2012 6:00 PM Justin
- Enhanced to show items that zero sales with stock balance.

1/24/2013 12:04 PM Justin
- Bug fixed on zero sales with stocks did not work properly.

2/4/2013 5:34 PM Justin
- Converted this report to extend module instead of report.
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/22/2013 1:59 PM Fithri
- bugfix - when select all branch, the qty is over the filter qty

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/9/2014 9:40 AM Fithri
- filter out SKU without inventory

6/6/2014 4:10 PM Justin
- Enhanced to have "Use GRN".

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

08/12/2016 16:30 Edwin
- Enhanced on show "stock balance" in report.

3/14/2017 5:08 PM Justin
- Bug fixed on report showing wrong information while branch filter choose as "all".

3/17/2017 3:37 PM Andy
- Reconstruct program structure.

3/27/2017 10:25 AM Andy
- Fixed to use temporary table.

4/3/2017 2:06 PM Andy
- Fixed sales qty zero when tick "Zero Sales with Stocks".

4/10/2017 4:06 PM Andy
- Fixed sales qty error when show report and select only one branch.

2/24/2020 9:32 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
//$con = new sql_db('agrofresh-hq.dyndns.org','arms_slave','arms_slave','armshq');
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class slow_moving_items_report extends Module{
	var $branches = array();
	var $branch_group = array();
	var $vendors = array();
	
	var $is_multi_branch = false;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		// load branch
		$q1 = $con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult($q1);
		$smarty->assign('branches',$this->branches);

		// load branch group items
		$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description
		from branch_group_items bgi
		left join branch on bgi.branch_id=branch.id
		where branch.active=1
		order by branch.sequence, branch.code");
		while($r = $con_multi->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
			$this->branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
			$this->branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult($q1);
		
		// load branch group header
		$con_multi->sql_query("select * from branch_group",false,false);
		while($r = $con_multi->sql_fetchassoc()){
			if(!$this->branch_group['items'][$r['id']]) continue;
            $this->branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		$smarty->assign('branch_group',$this->branch_group);
		
		// load vendor
		if($sessioninfo['vendor_ids']){
			$filter_vendor = "where id in ($sessioninfo[vendor_ids])";
		}
		
		$q1 = $con_multi->sql_query("select id,description from vendor $filter_vendor order by description") or die(mysql_error());
		$temp = $con_multi->sql_fetchrowset($q1);
		$con_multi->sql_freeresult($q1);
		foreach($temp as $r){
            $this->vendors[$r['id']] = $r;
		}
		$smarty->assign("vendor",$this->vendors);
        
		// default date
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month", strtotime($_REQUEST['date_to'])));
		
		parent::__construct($title);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    
		if($_REQUEST['show_report']){
			$this->prepare_report_data();
			$this->generate_report();
			if($this->export_excel){
				include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}			
		}

		$this->display();
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi;
		
		$sort_order = "";
		if($this->one_more_level)
		{
	        $str_category2_join ="left join category_cache cc2 on cc2.category_id=sku.category_id
							left join category c2 on cc2.p".$this->one_more_level." = c2.id ";
	        $str_p = "cc2.p".$this->one_more_level." as p,c2.description as cname,";
	        $sort_order = " order by p";
    	}
		
		$sb_str = "";
		// construct stock balance query
		foreach($this->branch_id_list as $bid){
			$sb_tbl = "stock_balance_b".$bid."_".$this->sb_year;
			$col_name = "sb".$bid;
			
			if($this->group_by_sku){
				$si_filter ="si2.sku_id=si.sku_id";
				$qty_str = "sum(sb.qty*u2.fraction) as qty";
				$uom_join = "left join uom u2 on si2.packing_uom_id=u2.id";
			}else{
				$si_filter = "si2.id=si.id";
				$qty_str = "sb.qty as qty";
			}
			
			$sb_str .= ",(select $qty_str
				from $sb_tbl sb
				join sku_items si2 on si2.id=sb.sku_item_id
				$uom_join
				where $si_filter and ".ms($this->date_to)." between sb.from_date and sb.to_date) as $col_name";
		}
		
		if($this->group_by_sku){
			if($sort_order)	$sort_order .= ",";
			else $sort_order = "order by ";
			$sort_order .= "si.is_parent desc";
            $sql = "select si.sku_id,si.id as sku_item_id,si.sku_item_code,si.mcode,si.artno, si.description,".$str_p."sum(pos.qty*uom.fraction) as qty,uom.fraction, (select group_concat(si2.id) from sku_items si2 where si2.sku_id=si.sku_id group by si2.sku_id) as si_id_list $sb_str
				   from tmp_si_sales_cache pos
				   join sku_items si on si.sku_id=pos.ref_id
				   left join sku on sku_id = sku.id 
				   ".$str_category2_join."
				   left join category_cache cc on cc.category_id=sku.category_id
				   left join uom on si.packing_uom_id=uom.id 
				   group by si.sku_id ".$sort_order;
        }else{
            $sql = "select si.sku_item_code,si.mcode,si.artno,si.id as sku_item_id,si.description,".$str_p."sum(pos.qty) as qty $sb_str
				   from tmp_si_sales_cache pos
				   join sku_items si on si.id=pos.ref_id
				   left join sku on sku_id = sku.id
				   ".$str_category2_join."
				   left join category_cache cc on cc.category_id=sku.category_id
				   group by si.id ".$sort_order;
		}
		
		//print "$sql<br>";
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$key = $this->group_by_sku ? $r['sku_id'] : $r['sku_item_id'];
			
			$stock_bal = 0;
			// get stock balance
			foreach($this->branch_id_list as $bid){
				$col_name = "sb".$bid;
				
				$stock_bal += $r[$col_name];
			};
			
			if($this->zero_sales_with_stock){			  
				if(!$stock_bal) continue;
			}
			
			$this->category[$r['p']]['id'] = $r['p'];
			$this->category[$r['p']]['cname'] = $r['cname'];
			
			$this->sku[$key]['sku_item_code'] = $r['sku_item_code'];
			$this->sku[$key]['description'] = $r['description'];
			$this->sku[$key]['mcode'] = $r['mcode'];
			$this->sku[$key]['artno'] = $r['artno'];
			
			
			$this->table[$r['p']][$key]['qty'] += $r['qty'];
			$this->table[$r['p']][$key]['stock_bal'] += $stock_bal;
			 
			$this->total[$r['p']]['qty'] += $r['qty'];
			$this->total[$r['p']]['stock_bal'] += $stock_bal;
			$this->total['total']['qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult($q1);
		
		$smarty->assign('table',$this->table);
		$smarty->assign('total',$this->total);
		$smarty->assign('sku',$this->sku);
	  	$smarty->assign('category',$this->category);
	}

	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
				
		$this->date_to = $_REQUEST['date_to'];
		$this->date_from = $_REQUEST['date_from'];
		$this->sb_year = date("Y", strtotime($_REQUEST['date_to']));
		$this->vendor_id = mi($_REQUEST['vendor_id']);
		$this->use_grn = mi($_REQUEST['use_grn']);
		$this->group_by_sku = mi($_REQUEST['group_sku']);
		$this->zero_sales_with_stock = mi($_REQUEST['zero_sales_with_stock']);
		$this->quantity = mf($_REQUEST['quantity']);
		$this->exclude_inactive_sku = mi($_REQUEST['exclude_inactive_sku']);
		if($_REQUEST['export_excel']){
			$this->export_excel = true;
		}
		
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$this->branch_id = mi($_REQUEST['branch_id']);
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con_multi->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con_multi->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					$this->branch_id_list[] = $r['id'];
				}
				$con_multi->sql_freeresult($q1);
				$this->branch_name = "Region: ".$region;
			}elseif($this->branch_id<0){ // branch group selected
				$this->bgid = abs($this->branch_id);
				if($this->branch_group){
					foreach($this->branch_group['items'][$this->bgid] as $bid=>$b){
						if($config['masterfile_branch_region'] && get_branch_code($bid) != "HQ" && !check_user_regions($bid)) continue;
						$this->branch_id_list[] = $bid;
					}
				}
				$this->branch_name = "Branch Group: ".$this->branch_group['header'][$this->bgid]['code'];
			}elseif($this->branch_id){  // single branch selected
			    $this->branch_id_list[] = $this->branch_id;
                $this->branch_name = "Branch: ".get_branch_code($this->branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
					if($config['masterfile_branch_region'] && get_branch_code($bid) != "HQ" && !check_user_regions($bid)) continue;
                    $this->branch_id_list[] = $bid;
				}
				$this->branch_name = "Branch: All";
			}
		}else{  // Branches mode
            $this->branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->branch_name = "Branch: ".BRANCH_CODE;
		}
		
		if(count($this->branch_id_list) > 1){
			$this->is_multi_branch = true;
		}else{
			//$filter[] = "pos.qty<=".mf($this->quantity);
			$this->having[] = "qty<=".mf($this->quantity);
		}
		
		// Get Category ID before process
        $category_id = $_REQUEST['category_id'];
        
	    if($category_id)
	    {
	        $con_multi->sql_query("select level,description from category where id=".mi($category_id)) or die(mysql_error());

    		$level = $con_multi->sql_fetchfield(0);
    		$category_name = $con_multi->sql_fetchfield(1);
			$con_multi->sql_freeresult();

    		// check one more level for grouping
    		$con_multi->sql_query("select max(level) from category") or die(mysql_error());
    		$max_level = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
    		if($level<$max_level)	$one_more_level = $level+1;
    		else    $one_more_level = $level;

    		$filter[] = "cc.p$level=".mi($category_id);
	    }
		$filter[] = "cc.p2 in ($sessioninfo[department_ids])";
		$filter[] = "((sku.no_inventory='inherit' and cc.no_inventory='no') or sku.no_inventory='no')";
		if($this->exclude_inactive_sku)	$filter[] = 'si.active=1';
		
		if(!$this->zero_sales_with_stock){
			$filter[] = "pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		}else{
			$this->pos_date_filter = " and pos.date between ".ms($this->date_from)." and ".ms($this->date_to);
		}
		
		if($filter) $filter = "where ".join(" and ",$filter);
		
		$this->filter = $filter;
		
		$this->category_id = $category_id;
		$this->one_more_level = $one_more_level;
		$this->cat_name = $category_name;
		
		// Report Title
		$report_titles = array();
		
		$report_titles[] = "Date from ".$this->date_from." to ".$this->date_to;
		
		if($this->branch_name)
		  $report_titles[] = $this->branch_name;
		else  
		  $report_titles[] = "Branch: All";
		
		if($this->cat_name)
		  $report_titles[] = $this->cat_name;
    	else
		  $report_titles[] = "Category: All";

		if($this->quantity){
			$report_titles[] = "Quantity less or equal to: ".mi($this->quantity);
		}
		
		if($this->vendor_id){
			$report_titles[] = "Vendor: ".$this->vendors[$this->vendor_id]['description'];
			if($this->use_grn) $report_titles[] = "Use GRN: Yes";
		}else{
			$report_titles[] = "Vendor: All";
		}
		
		if($this->group_by_sku){
			$report_titles[] = "Group By SKU: Yes";
		}
		
		if($this->zero_sales_with_stock){
			$report_titles[] = "Zero Sales with Stock: Yes";
		}

		if($this->exclude_inactive_sku){
			$report_titles[] = "Exclude Inactive SKU: Yes";
		}

		$report_title = join("&nbsp;&nbsp;&nbsp;", $report_titles);
    
    	$smarty->assign('report_title',$report_title);
	}
	
	private function prepare_report_data(){
		global $con, $sessioninfo, $config, $con_multi;
		
		// prepare filter
		$this->process_form();
		
		$filter = $this->filter;
		$one_more_level = $this->one_more_level;
		$quantity = $this->quantity;
        $group_by_sku = $this->group_by_sku;
		if($this->having)	$having = "having ".join(' and ', $this->having);
		
		//$con_multi->sql_query_false("drop table tmp_si_sales_cache");
		$con_multi->sql_query("create temporary table if not exists tmp_si_sales_cache(
					ref_id int(11) primary key, 
					qty double)");

		foreach($this->branch_id_list as $bid){
			$vd_filter = "";
			if ($this->vendor_id && $this->use_grn){
				$vd_filter = " and si.id in (select vsh.sku_item_id from vendor_sku_history_b".$bid." vsh where vendor_id=".mi($this->vendor_id)." and (".ms($this->date_from)." between vsh.from_date and vsh.to_date or ".ms($this->date_to)." between vsh.from_date and vsh.to_date or vsh.from_date between ".ms($this->date_from)." and ".ms($this->date_to)."))";
			}elseif($this->vendor_id){
				$vd_filter = " and sku.vendor_id=".mi($this->vendor_id);
			}
			
			

			$tbl = "sku_items_sales_cache_b".$bid;
			if($group_by_sku){
				$sql = "select tmp.ref_id, tmp.qty 
						from 
						(
							select si.sku_id as ref_id, ifnull(sum(pos.qty*u.fraction),0) as qty
							from sku_items si
							left join $tbl pos on pos.sku_item_id=si.id $this->pos_date_filter
							left join sku on si.sku_id = sku.id
							left join category_cache cc on cc.category_id=sku.category_id
							left join uom u on si.packing_uom_id=u.id 
							$filter $vd_filter group by si.sku_id $having
						) as tmp";
			}else{
				$sql = "select tmp.ref_id, tmp.qty 
						from 
						(
							select si.id as ref_id, ifnull(sum(pos.qty),0) as qty
							from sku_items si
							left join $tbl pos on pos.sku_item_id=si.id $this->pos_date_filter
							left join sku on si.sku_id = sku.id
							left join category_cache cc on cc.category_id=sku.category_id
							$filter $vd_filter group by si.id $having
						) as tmp";
			}
			
			//print "$sql<br>";
			// do insertion onto tmp table
			$con_multi->sql_query("insert into tmp_si_sales_cache $sql on duplicate key update tmp_si_sales_cache.qty = tmp_si_sales_cache.qty + tmp.qty");
		}
		
		if($this->is_multi_branch){
			// delete those data which is more than the required minimum sales quantity
			$con_multi->sql_query("delete from tmp_si_sales_cache where qty > ".mf($this->quantity));
		}		
	}
}
//$con_multi = new mysql_multi();
$report = new slow_moving_items_report('Slow Moving Items');
//$con_multi->close_connection();
?>
