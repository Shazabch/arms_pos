<?php
/*
1/19/2011 3:46:45 PM Alex
- change use report_server

3/7/2011 9:54:03 AM Justin
- Fixed the bugs where it generate errors whenever filter by Use GRN.

5/25/2011 4:53:20 PM Alex
- exclude normal sales

6/24/2011 6:09:27 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:32:24 PM Andy
- Change split() to use explode()

11/16/2011 3:29:44 PM Andy
- Change "Use GRN" query.
- Add checking for config.use_grn_last_vendor_include_master, if found config then last GRN only check master vendor.

3/12/2012 5:40:10 PM Andy
- Change Report to use sku vendor from cache table.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

12/17/2013 4:17 PM Justin
- Enhanced to use pos_items instead of sales cache table.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

11/28/2014 11:42 AM Andy
- Enhance report to show the sales amount using the amount-discount-discount2-tax_amount.(discount2 is receipt and mix & match discount, tax_amount is gst)
- Enhance the report discount to include discount2. (mix and match and receipt discount)

12/14/2015 3:00 PM Andy
- Fix discount percent to check exclude tax amount.

2/20/2020 9:34 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

// set records to display on each table default by following
$smarty->assign("record_chop", 50);

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
// show brand option
$br = ($sessioninfo['brands']) ? "id in (".join(",",array_keys($sessioninfo['brands'])).") and" : "";
$con_multi->sql_query("select id, description from brand where $br active=1 order by description") or die(mysql_error());
$smarty->assign("brand", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();
$smarty->assign("brand_group", get_brand_group());

//show price type option
$con_multi->sql_query("select code as type from trade_discount_type order by code") or die (mysql_error());
$smarty->assign("price_type", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

class BRAND_VENDOR_DISCOUNTED_SALES_REPORT extends Report{
	private function run_report($bid,$tbl_name){
        global $con, $smarty, $sessioninfo, $con_multi,$config;

		$filter = array();
        $from_date = $this->date_from;
		$to_date = $this->date_to;
		$vendor_id = $this->vendor_id;
		$use_grn = $this->use_grn;
		$department_id = $this->department_id;
		$brand_id = $this->brand_id;
        $sku_type = $this->sku_type;
        $price_type = $this->price_type;
		$view_type = $this->view_type;
		$tbl_name = "pos_items";
		
		if($bid){
			$sub_where = 'siph.branch_id in ('.$bid.') and ';
			$filter[] = "p.branch_id = ".mi($bid);
			//$with_grn = 'vsh.branch_id in ('.$bid.') and ';
		}

		if($sessioninfo['level']<9999){
			$filter[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";
		}
		if($department_id){
			$filter[] = "cc.p2 = ".ms($department_id);
		}
		
		if($brand_id != ''){
			$filter[] = "sku.brand_id in (".join(',',process_brand_id($brand_id)).")";
		}
		if($sku_type){
			$filter[] = "sku.sku_type = '".$sku_type."'";
		}
		if($vendor_id){
			if(!$use_grn)	$filter[] = 'sku.vendor_id = '.ms($vendor_id);
		}
		
		if($price_type){
			$sub_query = ", ifnull((select trade_discount_code 
					 		from sku_items_price_history siph 
							where $sub_where
							siph.sku_item_id = sku_items.id 
							and siph.added between ".$from_date." and ".$to_date."
							order by added desc limit 1)
							, sku.default_trade_discount_code) as price_type";
			$having = 'having price_type = "'.$price_type.'"';
		}

		if($use_grn && $vendor_id){
			// select those sku of this grn vendor between this date
			/*$vsh_filter = array();
			$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
			$vsh_filter[] = "vsh.added between ".ms($from_date)." and ".ms($to_date);
			$vsh_filter[] = "vsh.vendor_id=".mi($vendor_id);
			$vsh_filter = join(' and ', $vsh_filter);
			
			$sql = "select distinct(sku_item_id) as sid
			from vendor_sku_history vsh 
			left join sku_items on sku_items.id=vsh.sku_item_id
			left join sku on sku_items.sku_id = sku.id
			left join category_cache cc on cc.category_id=sku.category_id
			where $filter $vsh_filter";
			//print $sql;
			$con_multi->sql_query($sql) or die(mysql_error());
			$grn_sid_list = array();
			while($r = $con_multi->sql_fetchassoc()){
				$grn_sid_list[] = mi($r['sid']);
			}
			$con_multi->sql_freeresult();*/
			
			$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=pi.sku_item_id and pi.date between vsh.from_date and vsh.to_date and vsh.vendor_id=$vendor_id";
			//$filter[] = "vsh.vendor_id=$vendor_id";
		}
		
		$filter[] = "p.cancel_status=0";
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'sku_items.active=1' : '1';
		
		if($filter){
			$filter = join(' and ', $filter). " and ";
		}else	$filter = '';

		if($this->show_normal){
			if ($having){
				$having.="and discount > 0";
			}else{
				$having="having discount > 0";
			}
		}

		if($view_type == 'detail'){
			$order_by = "sku_items.sku_item_code,";
		}else{
			$order_by = "vendor.description,";
		}

		//for($i=0; $i<count($tbl_name); $i++){
			// stock sold query
			/*if($use_grn){
				$ven_sql=",(select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=sku_items.id and vsh.branch_id=$bid and vsh.added <= ".ms($from_date)." order by vsh.added desc limit 1) as last_grn_vendor_id,sku.vendor_id as master_vendor_id";
			}*/
			$sql = "select pi.sku_item_id, sku_items.sku_item_code, sku_items.artno, sku_items.description as sku_desc, 
					sku_items.artno, sku.vendor_id, vendor.description as vd_desc, 
					sku.brand_id, brand.description as brand_desc, 
					pi.trade_discount_code as price_type,
					(pi.price-pi.discount-pi.discount2-tax_amount) as amount, (pi.discount+pi.discount2) as disc_amt, pi.qty,
					round(coalesce(((pi.discount+pi.discount2)/pi.price)*100, 0), 2) as discount
					from $tbl_name pi
					left join pos p on p.id = pi.pos_id and p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date
					left join `sku_items` on sku_items.id = pi.sku_item_id
					left join `sku` on sku.id = sku_items.sku_id
					left join `vendor` on vendor.id = sku.vendor_id
					left join `brand` on brand.id = sku.brand_id
					left join `category_cache` cc using(category_id) 
					left join `category` c on c.id = cc.p2
					$use_grn_xtra_join
					where $filter
					p.date >= ".ms($from_date)." and p.date <= ".ms($to_date)."
					$having
					order by $order_by price_type, pi.discount";//print $sql;

			$sales = $con_multi->sql_query($sql);

			while($r = $con_multi->sql_fetchrow($sales)){
				if($use_grn && $vendor_id){
	        		/*if(($r['last_grn_vendor_id'] != $vendor_id) && !in_array($r['sku_item_id'], $grn_sid_list)){
						if(!$config['use_grn_last_vendor_include_master']){
							continue;
						}elseif($r['master_vendor_id'] != $vendor_id){
							continue;
						}
					}*/
					$r['vendor_id'] = $vendor_id;
					$r['vd_desc'] = $this->vd_name;
	        	}
		        	
				// set the discount becomes zero if found -0.00xxxx
				if($r['discount'] <= 0){
					$r['discount'] = 0;
				}

				if($view_type == 'detail'){
					$this->table[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
					$this->table[$r['sku_item_id']]['description'] = $r['sku_desc'];
					$this->table[$r['sku_item_id']]['artno'] = $r['artno'];
					$this->table[$r['sku_item_id']]['price_type'] = $r['price_type'];
					$this->table[$r['sku_item_id']][$r['discount']]['amount'] += $r['amount'];
					$this->table[$r['sku_item_id']][$r['discount']]['disc_amt'] += $r['disc_amt'];
					$this->table[$r['sku_item_id']][$r['discount']]['qty'] += $r['qty'];
					$this->row_total[$r['sku_item_id']]['amount'] += $r['amount'];
					$this->row_total[$r['sku_item_id']]['disc_amt'] += $r['disc_amt'];
					$this->row_total[$r['sku_item_id']]['qty'] += $r['qty'];
				}else{
					if(!$r['vd_desc']) $r['vd_desc'] = "Unnamed Vendor";
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']]['vendor_description'] = $r['vd_desc'];
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']]['brand'] = $r['brand_desc'];
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']]['price_type'] = $r['price_type'];
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']][$r['discount']]['amount'] += $r['amount'];
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']][$r['discount']]['disc_amt'] += $r['disc_amt'];
					$this->table[$r['vendor_id']][$r['brand_id']][$r['price_type']][$r['discount']]['qty'] += $r['qty'];
					$this->row_total[$r['vendor_id']][$r['brand_id']][$r['price_type']]['amount'] += $r['amount'];
					$this->row_total[$r['vendor_id']][$r['brand_id']][$r['price_type']]['disc_amt'] += $r['disc_amt'];
					$this->row_total[$r['vendor_id']][$r['brand_id']][$r['price_type']]['qty'] += $r['qty'];
					
					// Pick up the list that use to loop the query
					$this->header_list[$r['vendor_id']][$r['brand_id']][$r['price_type']] = $r['price_type'];
				}
				$this->col_total[$r['discount']]['amount'] += $r['amount'];
				$this->col_total[$r['discount']]['disc_amt'] += $r['disc_amt'];
				$this->col_total[$r['discount']]['qty'] += $r['qty'];
				$this->grand_total['amount'] += $r['amount'];
				$this->grand_total['disc_amt'] += $r['disc_amt'];
				$this->grand_total['qty'] += $r['qty'];
				$this->percentage[$r['discount']] = $r['discount'];
			}
			$con_multi->sql_freeresult($sales);
		//}
	}
	
    function generate_report(){
		global $con, $smarty, $config, $con_multi;

		$branch_group = $this->branch_group;

		$report_title[] = "Date : ".$this->date_from." to ".$this->date_to;
		if($this->vendor_id){
			$con_multi->sql_query("select description from vendor where id = ".mi($this->vendor_id));
		}
		$vd_name = ($this->vendor_id) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
		$report_title[] = "Vendor: ".$vd_name;
		$this->vd_name = $vd_name;
		
		
		
		
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			/*$tbl_name[] = "sku_items_sales_cache_bg".$bg_id;
			$get_branch_group_code = $con->sql_query("select branch_group_items.branch_id, branch_group.code 
													  from branch_group 
													  join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
													  where id = $bg_id");

			while($bg = $con->sql_fetchrow($get_branch_group_code)){
				$bid[] = $bg['branch_id'];
				$bg_code = $bg['code'];
			}

			$report_title[] = "Branch Group: ".$bg_code;
			$this->run_report(join(",",$bid),$tbl_name);*/
			
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $tmp_bid=>$b){
				
					if ($config['sales_report_branches_exclude']) {
						$branch_code = $b['code'];
						if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
							// print "$branch_code skipped<br />";
							continue;
						}
					}
				
					$tbl_name = "sku_items_sales_cache_b".$tmp_bid;
					$this->run_report($tmp_bid,$tbl_name);
				}
			}	
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $tbl_name = "sku_items_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_code = BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
	                $bg_sql = "select * from branch where active=1 order by sequence,code";
					
					$q_b = $con_multi->sql_query($bg_sql);
					while($r = $con_multi->sql_fetchrow($q_b)){
						if ($config['sales_report_branches_exclude']) {
							$branch_code = $r['code'];
							if (in_array($branch_code,$config['sales_report_branches_exclude'])) {
								// print "$branch_code skipped<br />";
								continue;
							}
						}
                        $tbl_name = "sku_items_sales_cache_b".$r['id'];
                        $this->run_report($bid,$tbl_name);
					}
					$con_multi->sql_freeresult($q_b);
					
					/*if($branch_group['header']){
						foreach($branch_group['header'] as $bg_id=>$bg){
                            $tbl_name[] = "sku_items_sales_cache_bg".$bg_id;
						}
					}
					$this->run_report('',$tbl_name);*/
				}else{	// is a particular branch
	                $tbl_name = "sku_items_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}
		
		if($this->header_list){
			foreach ($this->header_list as $vd_id => $brand){
				foreach ($brand as $brand_id => $price_type){
					foreach ($price_type as $price_type_code){
						$this->vd_count[$vd_id] += 1;
						$this->brand_count[$vd_id][$brand_id] += 1;
					}
				}
			}
		}

		
		$use_grn = ($this->use_grn) ? "Yes" : "No";
		$report_title[] = "Use GRN: ".$use_grn;
		$con_multi->sql_query("select description from category where id = ".mi($this->department_id));
		$department_code = ($this->department_id) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
		$report_title[] = "Department: ".$department_code;
		$sku_type = ($this->sku_type) ? $this->sku_type : "All";
		//$con->sql_query("select description from brand where id = ".mi($this->brand_id));
		$brand_name = get_brand_title($this->brand_id);
		$report_title[] = "Brand: ".$brand_name;
		$report_title[] = "SKU Type: ".$sku_type;
		$price_type = ($this->price_type) ? $this->price_type : "All";
		$report_title[] = "Price Type: ".$price_type;
        $report_title[] = "View By: ".ucwords($this->view_type);

		if($this->percentage) asort($this->percentage);
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
        $smarty->assign('percentage', $this->percentage);
        $smarty->assign('col_total', $this->col_total);
        $smarty->assign('row_total', $this->row_total);
        $smarty->assign('grand_total', $this->grand_total);
        $smarty->assign('header_list', $this->header_list);
        $smarty->assign('brand_count', $this->brand_count);
        $smarty->assign('vd_count', $this->vd_count);
		$smarty->assign('table', $this->table);
	}
	
	function process_form(){
	    global $con, $smarty;

        $this->bid  = get_request_branch();
        $this->date_from = $_REQUEST['date_from'];
        $this->date_to = $_REQUEST['date_to'];
		$end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
    	$this->date_to = $_REQUEST['date_to'];
        $this->vendor_id = $_REQUEST['vendor_id'];
        $this->use_grn = $_REQUEST['use_grn'];
    	$this->department_id = $_REQUEST['department_id'];
    	$this->brand_id = $_REQUEST['brand_id'];
        $this->sku_type = $_REQUEST['sku_type'];
        $this->price_type = $_REQUEST['price_type'];
        $this->view_type = $_REQUEST['view_type'];
        $this->show_normal = $_REQUEST['show_normal'];
		// call parent
		parent::process_form();
	}
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/

$BRAND_VENDOR_DISCOUNTED_SALES_REPORT = new BRAND_VENDOR_DISCOUNTED_SALES_REPORT('Brand / Vendor Discounted Sales Report');
/*$con_multi->close_connection();*/
?>
