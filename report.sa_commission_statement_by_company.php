<?php
/*
2/20/2012 12:04:11 PM Justin
- Fixed the bugs where causing branch group not functional.

2/23/2012 3:00:43 PM Justin
- Fixed the commission calculation bugs when by item all is commission by "Flat".

2/29/2012 2:13:43 PM Justin
- Fixed the error that system could not find the trade discount code.

3/5/2012 11:49:22 AM Justin
- Added new filter to skip those goods return items from POS.

3/21/2012 11:52:43 AM Justin
- Fixed the bugs that branch ID get zero when logged on as sub branch.

2/20/2013 3:59 PM Justin
- Enhanced to calculate average sales amount by number for SA base on config.

3/26/2013 11:22 AM Justin
- Bug fixed on nothing print out for statement.

4/26/2013 3:57 PM Justin
- Enhanced to pickup sales target.
- Bug fixed on some times system won't retrieve and show those SA which without company.
- Reconstructed the way how to pick up Commission Aging.

5/12/2014 5:13 PM Justin
- Enhanced to have total qty column.

6/16/2015 11:57 AM Justin
- Bug fixed on category checking for commission that will cause to sum up wrong sales amount.

6/18/2015 10:20 AM Justin
- Bug fixed on sales amount did not deduct from mix & match.

6/26/2015 5:52 PM Justin
- Bug fixed on commission amount have calculated wrongly.

6/1/2016 2:45 PM Justin
- Bug fixed on wrong total cost calculation.

2/3/2017 2:59 PM Andy
- Fixed to exclude gst from POS Amount.
- Change to use inv_line_gross_amt2 for DO Amount.

6/13/2017 10:29 AM Justin
- Bug fixed on cost did not average by S/A count when found config is turned on.

6/21/2017 10:38 AM Justin
- Bug fixed on cost calculation always round to 2 decimal points instead of following config setting.

6/29/2017 10:07 AM Justin
- Bug fixed on system will replace the previous receipt if having 2 same receipt number under 1 month.

12/22/2017 1:46 PM Justin
- Enhanced the report to show data no longer base on monthly basis but daily.

10/22/2018 5:15 PM Justin
- Bug fixed on commission range checking issue.

3/26/2019 5:27 PM Justin
- Enhanced to load the certain functions from appCore.

10/22/2019 10:45 AM Justin
- Enhanced to calculate sales agent sales base on ratio set from POS counter (v202).
- Enhanced the sales calculation to compatible with old and new version of POS counter.

10/24/2019 4:48 PM Justin
- Bug fixed on sales range by amount or qty is not working properly.

11/6/2019 5:05 PM Justin
- Bug fixed on the commission calculate bugs for sales / qty by range.
- Enhanced to load commission by sales/qty range data from newly created table.

11/22/2019 11:43 AM Justin
- Bug fixed on sales agent name couldn't show out.

1/2/2020 2:26 PM Justin
- Bug fixed on sales won't be show out on flat rate table while commission's condition does not meet.
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class SA_COMMISSION_STATEMENT_BY_COMPANY extends Module{
    function __construct($title){
        global $con, $smarty;

		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		
		// pre-load sales agent
		$con->sql_query("select * from sa order by code, name");
		$sa = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('sa', $sa);

		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		$this->branches_group = $this->load_branch_group();
		
    	parent::__construct($title);
    }

	function _default(){
		$this->display();
		exit;
	}

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display();
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		if($this->filter) $filter = " and ".join(" and ", $this->filter);
		if($this->ext_filter) $ext_filter = " and ".join(" and ", $this->ext_filter);

		// by flat rate
		$sql = array();
		$sql[] = "select ssc.year, ssc.month, ssc.amount, ssc.qty, ssc.commission_amt, ssc.sa_id, sa.company_code, 
				  sa.company_name, b.code as branch_code, sa.code as sa_code, sa.name as sa_name, ssc.use_commission_ratio, sast.value as st_list
				  from sa_sales_cache_b".mi($bid)." ssc
				  left join sa on sa.id = ssc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = ssc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where ssc.date between ".ms($this->date_from)." and ".ms($this->date_to).$filter.$ext_filter;

		// commission by qty/sales range
		$sql[] = "select srsc.year, srsc.month, srsc.amount, srsc.qty, srsc.commission_amt, srsc.sa_id, sa.company_code,
				  sa.company_name, b.code as branch_code, sa.code as sa_code, sa.name as sa_name, srsc.use_commission_ratio, sast.value as st_list
				  from sa_range_sales_cache_b".mi($bid)." srsc
				  left join sa on sa.id = srsc.sa_id
				  left join sa_sales_target sast on sast.sa_id = sa.id and sast.year = srsc.year and sast.branch_id = ".mi($bid)."
				  left join branch b on b.id = ".mi($bid)."
				  where concat(srsc.year, srsc.month) between ".date("Ym", strtotime($this->date_from))." and ".date("Ym", strtotime($this->date_to)).$filter;
				  
		$all_sql = join(" UNION ALL ", $sql)." order by company_code, company_name, branch_code, year, month, sa_code";
		$q1 = $con_multi->sql_query($all_sql);

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$ym = $r1['year'].$r1['month'];
			$r1['company_code'] = trim($r1['company_code']);
			$st_list = unserialize($r1['st_list']);
			if(!$r1['company_code']) $r1['company_code'] = "Untitled Company";
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['company_name'] = $r1['company_name'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['sa_code'] = $r1['sa_code'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['sa_name'] = $r1['sa_name'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['month'] = $r1['month'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['year'] = $r1['year'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['branch_code'] = $r1['branch_code'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['sales_amt'] += $r1['amount'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['sales_qty'] += $r1['qty'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['cost'] += $r1['cost'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['commission_amt'] += $r1['commission_amt'];
			$this->table[$r1['company_code']][$bid][$ym][$r1['sa_id']]['sales_target'] = $st_list[mi($r1['month'])];
			if($r1['use_commission_ratio']) $this->use_comm_ratio = true;
		}
		
		//print_r($this->sac_table);
		$con_multi->sql_freeresult($q1);
		$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sac_statement_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
    function generate_report(){
		global $con, $smarty;

		$this->table = array();
		$this->use_comm_ratio = false;
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date From ".strtoupper($this->date_from)." to ".strtoupper($this->date_to);
		
		if(!$this->sales_type) $sales_type = "All";
		else{
			if($this->sales_type = "open") $sales_type = "Cash Sales";
			elseif($this->sales_type = "credit_sales") $sales_type = "Credit Sales";
			else $sales_type = "POS";
		}
		$this->report_title[] = "Sales Type: ".$sales_type;
		// pre-load sales agent
		/*if($this->department_id){
			$con->sql_query("select description from category where id = ".mi($this->department_id));
			$dept = $con->sql_fetchrow();
			$dept_desc = $dept['description'];
			$con->sql_freeresult();
		}else{
			$dept_desc = "All";
		}

		$this->report_title[] = "Department: ".$dept_desc;
		$sku_type = ($this->sku_type) ? $this->sku_type : "All";
		$this->report_title[] = "SKU Type: ".$sku_type;*/

		if($this->sa_id){
			$con->sql_query("select name from sa where id = ".mi($this->sa_id));
			$sa_desc = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else $sa_desc = "All";
		
		$this->report_title[] = "Sales Agent: ".$sa_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		//$smarty->assign('sac_table', $this->sac_table);
		$smarty->assign('table', $this->table);
		$smarty->assign('use_comm_ratio', $this->use_comm_ratio);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['date_from']){
			if($_REQUEST['date_to']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['date_to'])));
			else{
				$_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['date_to'] || strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['date_from'])));
		}

		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 year",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;

		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->department_id = $_REQUEST['department_id'];
		$this->sku_type = $_REQUEST['sku_type'];
		$this->sales_type = $_REQUEST['sales_type'];
		$this->sa_id = $_REQUEST['sa_id'];

		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = $this->ext_filter = array();

		if($this->sales_type) $this->ext_filter[] = "ssc.sales_type = ".ms($this->sales_type);
		//if($this->department_id) $this->filter[] = "c.department_id = ".ms($this->sku_type);
		//if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);
		if($this->sa_id) $this->filter[] = "sa.id = ".mi($this->sa_id);
		//parent::process_form();
	}
	
	function load_branch_group($id=0){
		global $con,$smarty;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchassoc()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();	

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchassoc()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult();	
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
	
	function print_statement(){
		global $con, $smarty, $config, $appCore;
		$form = $_REQUEST;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}
		
		$sql = $do_filters = $pos_filters = $data = $comm_data = $sa_info = $range_data = $range_total = array();
		$use_comm_ratio = false;
		// from branch info
		$q1 = $con->sql_query("select * from branch where id = ".mi($form['branch_id']));
		$from_branch = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("from_branch", $from_branch);
		
		// from company
		if($form['company_code'] == "Untitled Company") $form['company_code'] = "";
		
		if($form['company_code']) $cc_filter = "company_code = ".ms($form['company_code']);
		else $cc_filter = "(company_code = ".ms($form['company_code'])." or company_code is null)";
		
		$q1 = $con->sql_query("select * from sa where $cc_filter order by id desc limit 1");
		$company_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("company_info", $company_info);

		$date_from = $form['date_from'];
		//$mth_from = date("m", strtotime($this->date_from));
		//$end_date = date("Y-m", strtotime($form['date_to']))."-01";
		$date_to = $form['date_to'];

		//if($this->department_id) $this->filter[] = "c.department_id = ".ms($this->sku_type);
		//if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);

		if(($form['sales_type'] && $form['sales_type'] != 'pos') || !$form['sales_type']){
			$do_filters[] = "do.do_date between ".ms($date_from)." and ".ms($date_to);
			if($form['sales_type']) $do_filters[] = "do.do_type =".ms($form['sales_type']);
			if($form['sa_id']) $do_filters[] = "((do.mst_sa != '' and do.mst_sa is not null and do.mst_sa like '%s:".strlen(mi($form['sa_id'])).":\"".mi($form['sa_id'])."\";%') or (di.dtl_sa != '' and di.dtl_sa is not null and di.dtl_sa like '%s:".strlen(mi($form['sa_id'])).":\"".mi($form['sa_id'])."\";%'))";
			else $do_filters[] = "((do.mst_sa != '' and do.mst_sa is not null) or (di.dtl_sa != '' and di.dtl_sa is not null))";
			if($do_filters) $do_filter = " and ".join(" and ", $do_filters);
			$sql[] = "select 'DO' as type, do_type, do.mst_sa, di.dtl_sa, di.do_id as mst_id, do.do_date as date, 
					  di.inv_line_gross_amt2 as cost_price, di.cost, do.do_markup,do.markup_type, 0 as pi_amount, 
					  uom.fraction, ((di.ctn*uom.fraction)+di.pcs) as qty, di.sku_item_id, sku.category_id, c.level as cat_level, sku.brand_id, 
					  sku.sku_type, sku.vendor_id, do.do_no as doc_no, di.item_discount,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, 0 as sales_cache_qty
					  from `do`
					  left join `do_items` di on di.do_id = do.id and di.branch_id = do.branch_id
					  left join `uom` on uom.id = di.uom_id
					  left join `sku_items` si on si.id = di.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($form['branch_id'])."
					  where do.branch_id = ".mi($form['branch_id'])." and do.active=1 and do.approved=1 and do.checkout=1 and do.do_type != 'transfer'
					  $filter $do_filter";
		}

		if($form['sales_type'] == 'pos' || !$form['sales_type']){
			$pos_filters[] = "pos.date between ".ms($date_from)." and ".ms($date_to);
			if($form['sa_id']) $pos_filters[] = "((pos.receipt_sa != '' and pos.receipt_sa is not null and pos.receipt_sa like '%s:".strlen(mi($form['sa_id'])).":\"".mi($form['sa_id'])."\";%') or (pi.item_sa != '' and pi.item_sa is not null and pi.item_sa like '%s:".strlen(mi($form['sa_id'])).":\"".mi($form['sa_id'])."\";%'))";
			else $pos_filters[] = "((pos.receipt_sa != '' and pos.receipt_sa is not null) or (pi.item_sa != '' and pi.item_sa is not null))";
			if($pos_filters) $pos_filter = " and ".join(" and ", $pos_filters);
			$sql[] = "select 'POS' as type, '' as do_type, pos.receipt_sa as mst_sa, pi.item_sa as dtl_sa, pos.id as mst_id, 
					  pos.date, 0 as cost_price, sisc.cost, 0 as do_markup, '' as markup_type, 
					  (pi.price-pi.discount-pi.discount2-pi.tax_amount) as pi_amount, uom.fraction, pi.qty, pi.sku_item_id, sku.category_id, c.level as cat_level,
					  sku.brand_id, sku.sku_type, sku.vendor_id, pos.receipt_no as doc_no, 0 as item_discount,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, sisc.qty as sales_cache_qty
					  from `pos`
					  left join `pos_items` pi on pi.pos_id = pos.id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
					  left join `sku_items` si on si.id = pi.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `uom` on uom.id = si.packing_uom_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($form['branch_id'])."
					  left join `sku_items_sales_cache_b".mi($form['branch_id'])."` sisc on sisc.sku_item_id = si.id and sisc.date = pos.date
					  join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
					  where pos.branch_id = ".mi($form['branch_id'])." and pos.cancel_status=0
					  $filter $pos_filter";
		}

		$all_sql = join(" UNION ALL ", $sql)." order by date";
//print $all_sql;
		$q1 = $con_multi->sql_query($all_sql);

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$sa_list = array();
			$row_cost = $row_amt_ctn = $row_amt_pcs = $row_amt = 0;

			if($r1['mst_sa']) $sa_list = unserialize($r1['mst_sa']);
			else $sa_list = unserialize($r1['dtl_sa']);
			
			if(count($sa_list) == 0) continue;

			$org_sa_list = $sa_list;
			
			if($r1['type'] == "DO"){
				$row_amt = round($r1['cost_price'],2);
				$row_cost = $r1['cost'] * $r1['qty'] / $r1['fraction'];
			}else{
				$row_cost = $r1['cost'] / $r1['sales_cache_qty'] * $r1['qty'];
				$row_amt = round($r1['pi_amount'],2);
			}

			$row_qty = $r1['qty'];
	
			// check if the receipt contains ratio
			$prms = array();
			$prms['sa_list'] = $org_sa_list;
			$prms['sales_amount'] = $row_amt;
			$sa_ratio_result = array();
			$sa_ratio_result = $appCore->salesAgentManager->posSAHandler($prms);
			unset($prms);
			
			// check if the sales agent got set with ratio then use the ratio to calculate the sales amount for each sales agent
			$sa_ratio_sales_list = array();
			if($r1['mst_sa']){
				if($sa_ratio_result['use_ratio']){
					$use_comm_ratio = true;
					$sa_ratio_sales_list = $sa_ratio_result['sa_ratio_sales_list'];
				}elseif(count($org_sa_list) > 1 && $config['sa_calc_average_sales']){ // otherwise check if turn on config to calculate average sales for all sales agent
					$row_cost = round($row_cost / count($org_sa_list), $config['global_cost_decimal_points']);
					$row_amt = round($row_amt / count($org_sa_list), 2);
				}
			}

			$sa_id_filter_list = join("','", $org_sa_list);
			// need to use array keys as sales agent ID as if POS counter are using v202 to store the sa ID
			if($sa_ratio_result['id_list_existed']){
				$sa_id_filter_list = join("','", array_keys($org_sa_list));
			}
			
			$do_type = "";
			if($r1['do_type'] == "open") $do_type = "Cash Sales";
			elseif($r1['do_type'] == "credit_sales") $do_type = "Credit Sales";
			elseif($r1['do_type'] == "transfer") $do_type = "Transfer";
			
			$ym = date("Ym", strtotime($r1['date']));
			$d = date("d", strtotime($r1['date']));
			$q3 = $con_multi->sql_query("select * from sa where id in ('".$sa_id_filter_list."') and $cc_filter order by code, name");

			$sa_list = array();
			while($r3 = $con_multi->sql_fetchassoc($q3)){
				$sa_list[] = $r3['id'];
				$sa_info[$ym][$r3['id']]['code'] = $r3['code'];
				$sa_info[$ym][$r3['id']]['name'] = $r3['name'];
			}
			$con->sql_freeresult($q3);
			
			if(count($sa_list) == 0) continue;

			foreach($sa_list as $r=>$tmp_sa_id){
				if(!$tmp_sa_id || ($form['sa_id'] && $form['sa_id'] != $tmp_sa_id)){
					unset($sa_list[$r]);
					continue;
				}
				
				// use the sales amount calculated by using ratio
				if($sa_ratio_result['use_ratio']){
					$row_amt = $sa_ratio_sales_list[$sa_id]['sales_amt'];
				}
				
				$is_flat_rate_comm = $is_sales_qty_range_comm = false;
				$q2 = $con_multi->sql_query("select *, sa.id as sa_id, saci.id as saci_id, saci.date_from
											from sa
											join sa_commission_settings sas on sas.sa_id = sa.id and sas.branch_id = ".mi($form['branch_id'])."
											join sa_commission sac on sac.id = sas.sac_id and sac.branch_id = sas.branch_id
											join sa_commission_items saci on saci.sac_id = sac.id and saci.branch_id = sac.branch_id
											where sa.id = ".mi($tmp_sa_id)." and saci.date_from <= ".ms($r1['date'])." and (saci.date_to is null or saci.date_to = '' or saci.date_to >= ".ms($r1['date']).") and sac.active = 1 and saci.active = 1 and sas.active = 1
											order by sa.code");

				if($con_multi->sql_numrows($q2) > 0){
					while($r2 = $con_multi->sql_fetchassoc($q2)){
						if($r2['commission_method'] != "Flat"){					
							// do not sum up the commission again as if it is being calculated previously
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['result'] == "failed") continue;
						}
						
						// check if the conditions are met
						$conditions = unserialize($r2['conditions']);
						$condition_met = $appCore->salesAgentManager->check_commission_conditions($conditions, $r1);
						if(!$condition_met) continue;
						
						if($r2['commission_method'] != "Flat"){ // is set by sales or qty range
							$commission_value_list = unserialize($r2['commission_value']);
							
							$prms = array();
							$prms['sales_type'] = $form['sales_type'];
							$prms['sac_date'] = $r1['date'];
							$prms['conditions'] = $conditions;
							$prms['commission_method'] = $r2['commission_method'];
							$prms['commission_value_list'] = $commission_value_list;

							// here is where we start to sum up the entire month for commission method - range setup...
							if(!$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]) $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']] = $appCore->salesAgentManager->check_range_commission($r2['sa_id'], $form['branch_id'], $prms);
							
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['result'] == "failed") continue;
							elseif($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['claimed'] == true){
								$is_sales_qty_range_comm = true; // mark this transaction contains sales / qty range commission
								continue;
							}else{
								$commission_value = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['commission_value'];
								// replace the sales amount with monthly sales amount in case the commission value was set by percentage
								$row_amt = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['ttl_sales_amt'];
								$row_qty = $sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['ttl_sales_qty'];
							}

						}else $commission_value = $r2['commission_value']; // is set by flat

						if(!$commission_value) continue;
						
						$commission_values = explode("+", $commission_value);
						$commission_amt = 0;
						foreach($commission_values as $cm_value){
							// check if the commission either by percentage or amount
							if(preg_match("/%/", $cm_value)){ // is by percentage
								$cv = str_replace("%", "", $cm_value);
								$commission_amt += round(($row_amt-$commission_amt) * ($cv/100), 2);
							}else $commission_amt += $cm_value;
						}
						
						// construct different array to store and show commission set by sales/qty range
						if($r2['commission_method'] != "Flat"){
							// use sum up as if it was the first time of getting the commssion
							// mark this commission as claimed once added into the range data list
							if($sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['result'] == "passed"){
								$range_data[$ym][$r2['sa_id']]['code'] = $r2['code'];
								$range_data[$ym][$r2['sa_id']]['name'] = $r2['name'];
								$range_data[$ym][$r2['sa_id']]['amt'] += $row_amt;
								$range_data[$ym][$r2['sa_id']]['qty'] += $row_qty;
								$range_data[$ym][$r2['sa_id']]['commission_amt'] += $commission_amt;
								$range_total[$ym]['amt'] += $row_amt;
								$range_total[$ym]['qty'] += $row_qty;
								$range_total[$ym]['commission_amt'] += $commission_amt;
								
								// mark this commission by sales / qty range become claimed
								$sa_range_commission[$r2['sa_id']][$r2['saci_id']][$form['branch_id']]['claimed'] = true;
							}
							
							// stop to include the sales into the flat list
							$is_sales_qty_range_comm = true;
							continue;
						}

						// check the highest commission value
						if($r2['commission_method'] != "Flat" && $r2['commission_method'] != "Flat" && $commission_amt > $top_commission_amt[$r1['doc_no']]) $top_commission_amt[$r1['doc_no']] = $commission_amt;
						else $top_commission_amt[$r1['doc_no']] += $commission_amt;
						$ttl_commission_amt[$r1['doc_no']] += $commission_amt;
						//$this->sac_table[$r1['doc_no']][$r2['sa_id']]['commission_value'] = $commission_value;

						// store up the list for flat rate
						$data[$ym][$r2['sa_id']][$d][$r1['doc_no']]['commission_amt'] += $commission_amt;
						$is_flat_rate_comm = true;
					}
				}
				$con_multi->sql_freeresult($q2);
				
				// need to store the sales and qty value into flat rate table as if couldn't find the commission
				// receipt must not be cancelled and matched with commission by qty or sales range
				// sum up as flat rate sales as if it has commission or couldn't find any commission from flat rate and commission by sales / qty range
				if($is_flat_rate_comm || (!$is_flat_rate_comm && !$is_sales_qty_range_comm)){
					// use the sales amount calculated by using ratio
					if($sa_ratio_result['use_ratio']){
						$row_amt = $sa_ratio_sales_list[$tmp_sa_id]['sales_amt'];
					}
					
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['date'] = $r1['date'];
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['type'] = $r1['type'];
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['do_type'] = $do_type;
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['cost'] += $row_cost;
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['amt'] += $row_amt;
					$data[$ym][$tmp_sa_id][$d][$r1['doc_no']]['qty'] += $row_qty;
				}
			}

			$comm_data[$ym][$r1['doc_no']]['date'] = $r1['date'];
			//$this->table[$r1['doc_no']]['ttl_commission_value'] += $ttl_commission_value;

			$comm_data[$ym][$r1['doc_no']]['cost'] += $row_cost;
			$comm_data[$ym][$r1['doc_no']]['final_amount'] += $row_amt;
			$comm_data[$ym][$r1['doc_no']]['top_commission_amt'] = $top_commission_amt[$r1['doc_no']];
			$comm_data[$ym][$r1['doc_no']]['ttl_commission_amt'] = $ttl_commission_amt[$r1['doc_no']];
			//if(!$this->table[$r1['doc_no']]) $this->table[$r1['doc_no']] = $r1;
			//$prv_mst_id= $r1['mst_id'];
		}
		$con_multi->sql_freeresult($q1);

		$filters = $ym_list = $aging_info = array();
		if($form['company_code']) $filters[] = "sa.company_code = ".ms($form['company_code']);
		else $filters[] = "(sa.company_code = ".ms($form['company_code'])." or sa.company_code is null)";
		if($form['sales_type']) $ext_filters[] = "ssc.sales_type = ".ms($form['sales_type']);
		if($form['sa_id']) $filters[] = "sa.id = ".mi($form['sa_id']);
		
		if($filters) $filter = " and ".join(" and ", $filters);
		if($ext_filters) $ext_filter = " and ".join(" and ", $ext_filters);
		
		// flat rate
		$sql = array();
		$sql[] = "select ssc.date, ssc.year, ssc.month, ssc.amount, ssc.qty, ssc.commission_amt, ssc.sa_id
				  from sa_sales_cache_b".mi($form['branch_id'])." ssc
				  left join sa on sa.id = ssc.sa_id
				  where ssc.date <= ".ms($date_to).$filter.$ext_filter;

		// commission by qty/sales range
		$sql[] = "select 0 as date, srsc.year, srsc.month, srsc.amount, srsc.qty, srsc.commission_amt, srsc.sa_id
				  from sa_range_sales_cache_b".mi($form['branch_id'])." srsc
				  left join sa on sa.id = srsc.sa_id
				  where concat(srsc.year, srsc.month) <= ".date("Ym", strtotime($date_to)).$filter;
				  
		$all_sql = join(" UNION ALL ", $sql);
		$q1 = $con_multi->sql_query($all_sql);

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			//$st_list = unserialize($r1['st_list']);
			//if($st_list[mi($r1['month'])] > $r1['amount']) $r1['commission_amt'] = 0;
			
			// it is commissiong by qty/sales range, need to build up the date formate
			if(!$r1['date']) $sales_date = $r1['year']."-".$r1['month']."-01";
			else $sales_date = $r1['date'];

			if(date("Ym", strtotime($sales_date)) == date("Ym", strtotime($date_from))){
				$aging_info['curr_mth_amt'] += $r1['amount'];
				$aging_info['curr_mth_qty'] += $r1['qty'];
				$aging_info['curr_mth_comm_amt'] += $r1['commission_amt'];
			}elseif(date("Ym", strtotime($sales_date)) == date("Ym", strtotime($date_from." -1 month"))){
				$aging_info['2nd_mth_amt'] += $r1['amount'];
				$aging_info['2nd_mth_qty'] += $r1['qty'];
				$aging_info['2nd_mth_comm_amt'] += $r1['commission_amt'];
			}elseif(date("Ym", strtotime($sales_date)) == date("Ym", strtotime($date_from." -2 month"))){
				$aging_info['3rd_mth_amt'] += $r1['amount'];
				$aging_info['3rd_mth_qty'] += $r1['qty'];
				$aging_info['3rd_mth_comm_amt'] += $r1['commission_amt'];
			}elseif(date("Ym", strtotime($sales_date)) == date("Ym", strtotime($date_from." -3 month"))){
				$aging_info['4th_mth_amt'] += $r1['amount'];
				$aging_info['4th_mth_qty'] += $r1['qty'];
				$aging_info['4th_mth_comm_amt'] += $r1['commission_amt'];
			}elseif(date("Ym", strtotime($sales_date)) == date("Ym", strtotime($date_from." -4 month"))){
				$aging_info['5th_mth_amt'] += $r1['amount'];
				$aging_info['5th_mth_qty'] += $r1['qty'];
				$aging_info['5th_mth_comm_amt'] += $r1['commission_amt'];
			}elseif(date("Ym", strtotime($sales_date)) <= date("Ym", strtotime($date_from." -5 month"))){
				$aging_info['5th_mth_above_amt'] += $r1['amount'];
				$aging_info['5th_mth_above_qty'] += $r1['qty'];
				$aging_info['5th_mth_above_comm_amt'] += $r1['commission_amt'];
			}
			$ym_list[$r1['year']][$r1['month']] = $r1['month'];
		}

		$con_multi->sql_freeresult($q1);
		$con_multi->close_connection();

		foreach($ym_list as $tmp_yr=>$mth_list){
			foreach($mth_list as $tmp_mth){
				$tmp_mth = str_pad($tmp_mth, 2, "0", STR_PAD_LEFT);
				$ym = $tmp_yr.$tmp_mth;
				if(!$data[$ym] && !$range_data[$ym]) continue;
				$smarty->assign("data", $data[$ym]);
				$smarty->assign("range_data", $range_data[$ym]);
				$smarty->assign("range_total", $range_total[$ym]);
				$smarty->assign("comm_data", $comm_data[$ym]);
				$smarty->assign("year", $tmp_yr);
				$smarty->assign("month", $tmp_mth);
				$smarty->assign("sa_info", $sa_info[$ym]);
				$smarty->assign("aging_info", $aging_info);
				$smarty->assign("use_comm_ratio", $use_comm_ratio);
				$smarty->display("report.sa_commission_statement_by_company.print.tpl");
			}
		}
		
		unset($data, $range_data, $range_total, $comm_data, $tmp_yr, $tmp_mth, $sa_info, $aging_info, $use_comm_ratio);
	}
}

$SA_COMMISSION_STATEMENT_BY_COMPANY = new SA_COMMISSION_STATEMENT_BY_COMPANY('Sales Agent Commission Statement by Company Report');
?>
