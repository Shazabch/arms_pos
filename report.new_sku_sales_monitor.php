<?php
/*
2/20/2012 12:04:11 PM Justin
- Fixed the bugs where causing branch group not functional.

5/30/2013 10:25 AM Justin
- Bug fixed on system shows sql error while view in sub branch.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4/21/2017 11:34 AM Justin
- Enhanced to pickup DO amt and qty.
- Enhanced to split GRN into vendor and IBT qty.
- Enhanced to have DO date (from/to) filtering.

2/21/2020 3:40 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");
include("include/class.report.php");
include("masterfile_sa_commission.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class NEW_SKU_SALES_MONITOR extends Module{
   function __construct($title){
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if (!$_REQUEST['apply_date_from']) $_REQUEST['apply_date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['apply_date_to']) $_REQUEST['apply_date_to'] = date('Y-m-d');
		if (!$_REQUEST['sales_date_from']) $_REQUEST['sales_date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['sales_date_to']) $_REQUEST['sales_date_to'] = date('Y-m-d');
		if (!$_REQUEST['rcv_date_from']) $_REQUEST['rcv_date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['rcv_date_to']) $_REQUEST['rcv_date_to'] = date('Y-m-d');
		if (!$_REQUEST['do_date_from']) $_REQUEST['do_date_from'] = date('Y-m-d', strtotime("-1 month"));
		if (!$_REQUEST['do_date_to']) $_REQUEST['do_date_to'] = date('Y-m-d');

		// load branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
		
		// pre-load departments
		$con_multi->sql_query("select * from category where id in ($sessioninfo[department_ids]) and level = 2 order by description");
		$smarty->assign("departments", $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();

		// pre-load brands
		$con_multi->sql_query("select id,description from brand order by description");
		$smarty->assign('brands', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		$smarty->assign('brand_groups', get_brand_group());

		// pre-load vendors
		$con_multi->sql_query("select id,description from vendor order by description");
		$smarty->assign('vendors', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
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
        global $con, $smarty, $sessioninfo, $config, $con_multi;

		/*$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}*/

		$apply_sku = $si_parent_list = array();
		if($this->filter) $fitler = "and ".join(" and ", $this->filter);

		/*if($this->use_grn){
			// select those sku of this grn vendor between this date
			$vsh_filter = $grn_sid_list = array();
			$vsh_filter[] = "vsh.branch_id=".mi($bid)." and vsh.source='grn'";
			$vsh_filter[] = "vsh.added between ".ms($this->rcv_date_from)." and ".ms($this->rcv_date_to);
			$vsh_filter[] = "vsh.vendor_id=".mi($this->vendor_id);
			$vsh_filter = join(' and ', $vsh_filter);
			
			$sql = $con_multi->sql_query("select distinct(sku_item_id) as sid
										  from vendor_sku_history vsh 
										  left join sku_items si on si.id=vsh.sku_item_id
										  left join sku on si.sku_id = sku.id
										  left join category_cache cc on cc.category_id=sku.category_id
										  where $vsh_filter $filter");
			while($r = $con_multi->sql_fetchassoc($sql)){
				$grn_sid_list[] = mi($r['sid']);
			}
			$con_multi->sql_freeresult($sql);
			
			$q1 = $con_multi->sql_query("select sku.id, si.id as sku_item_id, sku.vendor_id as master_vendor_id,
										 (select vsh.vendor_id from vendor_sku_history vsh where vsh.sku_item_id=si.id and vsh.branch_id=".mi($bid)." and vsh.added <= ".ms($this->rcv_date_from)." order by vsh.added desc limit 1) as last_grn_vendor_id, si.description, si.mcode, si.is_parent
										 from sku_items_sales_cache_b$bid pos
										 join sku_items si on si.id=pos.sku_item_id
										 left join sku on sku.id=si.sku_id
										 left join category c on c.id=sku.category_id
										 left join category_cache cc on cc.category_id=c.id
										 where pos.date between ".ms($this->rcv_date_from)." and ".ms($this->rcv_date_to)." 
										 $filter
										 group by pos.sku_item_id");
		}else{*/
		$q1 = $con_multi->sql_query($sql="select sku.id, si.id as sku_item_id, si.sku_item_code, si.description, si.mcode, si.is_parent
									 from `sku`
									 left join sku_items si on si.sku_id = sku.id
									 left join category c on c.id = sku.category_id
									 left join category_cache cc on cc.category_id = c.id
									 where date_format(sku.added, '%Y-%m-%d') between ".ms($this->apply_date_from)." and ".ms($this->apply_date_to)."
									 $fitler
									 order by sku.id, sku.added, si.sku_item_code");//print "$sql<br /><br />";//xx
		//}

		while($r1 = $con_multi->sql_fetchassoc($q1)){
			/*if($this->use_grn){
				if(($r1['last_grn_vendor_id'] != $this->vendor_id) && !in_array($r1['sku_item_id'], $grn_sid_list)){
					if(!$config['use_grn_last_vendor_include_master']){
						continue;
					}elseif($r1['master_vendor_id'] != $this->vendor_id){
						continue;
					}
				}
			}*/
			$apply_sku[] = $r1['id'];
	
			if(!$r1['is_parent']){
				if(!$si_parent_list[$r1['id']]){
					$tmp_q1 = $con_multi->sql_query("select si.sku_item_code, si.description, si.mcode
													 from sku 
													 left join sku_items si on si.sku_id = sku.id 
													 where sku.id = ".mi($r1['id'])." and si.is_parent = 1
													 limit 1");
					$parent_info = $con_multi->sql_fetchassoc($tmp_q1);
					$con_multi->sql_freeresult($tmp_q1);
					if(!$parent_info) continue;
					$si_parent_list[$r1['id']]['sku_item_code'] = $parent_info['sku_item_code'];
					$si_parent_list[$r1['id']]['description'] = $parent_info['description'];
					$si_parent_list[$r1['id']]['mcode'] = $parent_info['mcode'];
				}
				$this->sku_table[$r1['id']]['sku_item_code'] = $si_parent_list[$r1['id']]['sku_item_code'];
				$this->sku_table[$r1['id']]['description'] = $si_parent_list[$r1['id']]['description'];
				$this->sku_table[$r1['id']]['mcode'] = $si_parent_list[$r1['id']]['mcode'];
			}else{
				$this->sku_table[$r1['id']]['sku_item_code'] = $si_parent_list[$r1['id']]['sku_item_code'] = $r1['sku_item_code'];
				$this->sku_table[$r1['id']]['description'] = $si_parent_list[$r1['id']]['description'] = $r1['description'];
				$this->sku_table[$r1['id']]['mcode'] = $si_parent_list[$r1['id']]['mcode'] = $r1['mcode'];
			}
			if(!$r1['is_parent'] || !$this->group_by_sku){
				$this->table[$r1['id']][$r1['sku_item_id']]['sku_item_code'] = $r1['sku_item_code'];
				$this->table[$r1['id']][$r1['sku_item_id']]['description'] = $r1['description'];
				$this->table[$r1['id']][$r1['sku_item_id']]['mcode'] = $r1['mcode'];
			}
		}
		$con_multi->sql_freeresult($q1);

		if(count($apply_sku) > 0){
			// sales from sku items cache
			$q2 = $con_multi->sql_query("select *
										 from `sku_items_sales_cache_b$bid` sisc
										 left join sku_items si on si.id = sisc.sku_item_id
										 where sisc.date between ".ms($this->sales_date_from)." and ".ms($this->sales_date_to)." and si.sku_id in (".join(",", $apply_sku).")
										 order by si.sku_item_code");

			while($r2 = $con_multi->sql_fetchassoc($q2)){
				$this->sku_table[$r2['sku_id']]['sales_qty'] += $r2['qty'];
				$this->sku_table[$r2['sku_id']]['sales_amt'] += $r2['amount'];
				$this->sku_table[$r2['sku_id']]['cost'] += $r2['cost'];
				if(!$r2['is_parent'] || !$this->group_by_sku){
					$this->table[$r2['sku_id']][$r2['sku_item_id']]['sales_qty'] += $r2['qty'];
					$this->table[$r2['sku_id']][$r2['sku_item_id']]['sales_amt'] += $r2['amount'];
					$this->table[$r2['sku_id']][$r2['sku_item_id']]['cost'] += $r2['cost'];
				}
			}
			$con_multi->sql_freeresult($q2);
			
			// qty from grn
			$q3 = $con_multi->sql_query("select if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn * u.fraction + gi.pcs, gi.acc_ctn *u.fraction + gi.acc_pcs) as rcv_qty, gi.sku_item_id, si.sku_id, si.is_parent, grn.is_ibt
										 from grn
										 left join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
										 left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
										 left join sku_items si on si.id = gi.sku_item_id
										 left join uom u on u.id = gi.uom_id
										 where grn.branch_id = ".mi($bid)." and grr.rcv_date between ".ms($this->rcv_date_from)." and ".ms($this->rcv_date_to)." and si.sku_id in (".join(",", $apply_sku).") and grn.active=1 and grn.status=1 and grn.approved=1 and grr.active=1
										 order by si.sku_item_code");

			while($r3 = $con_multi->sql_fetchassoc($q3)){
				if($r3['is_ibt']) $this->sku_table[$r3['sku_id']]['ibt_qty'] += $r3['rcv_qty'];
				else $this->sku_table[$r3['sku_id']]['vd_qty'] += $r3['rcv_qty'];
				if(!$r3['is_parent'] || !$this->group_by_sku){
					if($r3['is_ibt']) $this->table[$r3['sku_id']][$r3['sku_item_id']]['ibt_qty'] += $r3['rcv_qty'];
					else $this->table[$r3['sku_id']][$r3['sku_item_id']]['vd_qty'] += $r3['rcv_qty'];
				}
			}
			$con_multi->sql_freeresult($q3);
			
			// qty from DO
			$q4 = $con_multi->sql_query("select di.inv_line_gross_amt2, di.line_gross_amt, di.sku_item_id, di.cost, di.cost_price, ((di.ctn*uom.fraction)+di.pcs) as qty, 
										si.is_parent, si.sku_id
										from do
										left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
										left join uom on uom.id = di.uom_id
										left join sku_items si on si.id = di.sku_item_id
										where do.do_date between ".ms($this->do_date_from)." and ".ms($this->do_date_to)." and 
										si.sku_id in (".join(",", $apply_sku).") and do.branch_id = ".mi($bid)." and
										do.active=1 and do.status=1 and do.approved=1 and do.checkout=1
										order by si.sku_item_code");

			while($r4 = $con_multi->sql_fetchassoc($q4)){
				if($r4['inv_line_gross_amt2'] > 0){
					$inv_gross_amt = $r4['inv_line_gross_amt2'];
					//$inv_gst_amt = $r4['inv_line_gst_amt2'];
					//$inv_amt = $r4['inv_line_amt2'];
				}else{
					$inv_gross_amt = $r4['line_gross_amt'];
					//$inv_gst_amt = $r4['line_gst_amt'];
					//$inv_amt = $r4['line_amt'];
				}
				
				$this->sku_table[$r4['sku_id']]['do_qty'] += $r4['qty'];
				$this->sku_table[$r4['sku_id']]['do_amt'] += $inv_gross_amt;
				///$this->sku_table[$r4['sku_id']]['cost'] += $r4['cost'];
				if(!$r4['is_parent'] || !$this->group_by_sku){
					$this->table[$r4['sku_id']][$r4['sku_item_id']]['do_qty'] += $r4['qty'];
					$this->table[$r4['sku_id']][$r4['sku_item_id']]['do_amt'] += $inv_gross_amt;
					//$this->table[$r4['sku_id']][$r4['sku_item_id']]['cost'] += $r4['cost'];
				}
			}
			$con_multi->sql_freeresult($q4);
			
		}
		//$con_multi->close_connection();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->process_form();
		$this->generate_report();

        include_once("include/excelwriter.php");
    	$smarty->assign('no_header_footer', true);
    	$filename = "sa_commission_".time().".xls";
    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Activation Report To Excel($filename)");
    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}
	
    function generate_report(){
		global $con, $smarty, $con_multi;

		$this->table = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Apply Date From ".strtoupper($this->apply_date_from)." to ".strtoupper($this->apply_date_to);
		$this->report_title[] = "Sales Date From ".strtoupper($this->sales_date_from)." to ".strtoupper($this->sales_date_to);
		$this->report_title[] = "Received Date From ".strtoupper($this->rcv_date_from)." to ".strtoupper($this->rcv_date_to);
		$this->report_title[] = "DO Date From ".strtoupper($this->do_date_from)." to ".strtoupper($this->do_date_to);

		if($this->department_id){
			$con_multi->sql_query("select description from category where id = ".mi($this->department_id));
			$dept_header = $con_multi->sql_fetchfield(0);
			$con_multi->sql_freeresult();
		}else{
			$dept_header = "All";
		}
		$this->report_title[] = "Department: ".$dept_header;

		/*
		if($this->brand_id > 0){
			$con->sql_query("select description from brand where id = ".mi($this->brand_id));
			$brand_header = $con->sql_fetchfield(0);
			$con->sql_freeresult();
		}elseif($this->brand_id == "0"){
			$brand_header = "UNBRANDED";
		}else{
			$brand_header = "All";
		}
		*/
		$brand_header = get_brand_title($this->brand_id);
		$this->report_title[] = "Brand: ".$brand_header;
		
		if($this->vendor_id){
			$con_multi->sql_query("select code, description from vendor where id = ".mi($this->vendor_id));
			$vd = $con_multi->sql_fetchrow();
			$vd_header = $vd['code']." - ".$vd['description'];
			$con_multi->sql_freeresult();
		}else{
			$vd_header = "All";
		}
		$this->report_title[] = "Master Vendor: ".$vd_header;

		if(!$this->group_by_sku) $gb_desc = "No";
		else $gb_desc = "Yes";
		$this->report_title[] = "Group By SKU: ".$gb_desc;
		
		/*if($this->use_grn) $ug_desc = "Yes";
		else $ug_desc = "No";
		
		$this->report_title[] = "Group By SKU: ".$ug_desc;*/
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));

		$smarty->assign('sku_table', $this->sku_table);
		$smarty->assign('table', $this->table);
		$smarty->assign('category', $this->category);
	}
	
	function process_form(){
	    global $con, $smarty, $sessioninfo;

		// sku apply date validation
		if(!$_REQUEST['apply_date_from']){
			if($_REQUEST['apply_date_to']) $_REQUEST['apply_date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['apply_date_to'])));
			else{
				$_REQUEST['apply_date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['apply_date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['apply_date_to'] || strtotime($_REQUEST['apply_date_from']) > strtotime($_REQUEST['apply_date_to'])){
			$_REQUEST['apply_date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['apply_date_from'])));
		}

		// check if the date is more than 1 month
		$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['apply_date_from'])));
    	if(strtotime($_REQUEST['apply_date_to'])>strtotime($end_date)) $_REQUEST['apply_date_to'] = $end_date;

		// sales date validation
		if(!$_REQUEST['sales_date_from']){
			if($_REQUEST['sales_date_to']) $_REQUEST['sales_date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['sales_date_to'])));
			else{
				$_REQUEST['sales_date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['sales_date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['sales_date_to'] || strtotime($_REQUEST['sales_date_from']) > strtotime($_REQUEST['sales_date_to'])){
			$_REQUEST['sales_date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['sales_date_from'])));
		}

		// check if the date is more than 1 month
		//$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['sales_date_from'])));
    	//if(strtotime($_REQUEST['sales_date_to'])>strtotime($end_date)) $_REQUEST['sales_date_to'] = $end_date;

		// grn received date validation
		if(!$_REQUEST['rcv_date_from']){
			if($_REQUEST['rcv_date_to']) $_REQUEST['rcv_date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['rcv_date_to'])));
			else{
				$_REQUEST['rcv_date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['rcv_date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['rcv_date_to'] || strtotime($_REQUEST['rcv_date_from']) > strtotime($_REQUEST['rcv_date_to'])){
			$_REQUEST['rcv_date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['rcv_date_from'])));
		}
		
		// do received date validation
		if(!$_REQUEST['do_date_from']){
			if($_REQUEST['do_date_to']) $_REQUEST['do_date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['do_date_to'])));
			else{
				$_REQUEST['do_date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['do_date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['do_date_to'] || strtotime($_REQUEST['do_date_from']) > strtotime($_REQUEST['do_date_to'])){
			$_REQUEST['do_date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['do_date_from'])));
		}

		// check if the date is more than 1 month
		//$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['rcv_date_from'])));
    	//if(strtotime($_REQUEST['rcv_date_to'])>strtotime($end_date)) $_REQUEST['rcv_date_to'] = $end_date;
		
		$this->apply_date_from = $_REQUEST['apply_date_from'];
		$this->apply_date_to = $_REQUEST['apply_date_to'];
		$this->sales_date_from = $_REQUEST['sales_date_from'];
		$this->sales_date_to = $_REQUEST['sales_date_to'];
		$this->rcv_date_from = $_REQUEST['rcv_date_from'];
		$this->rcv_date_to = $_REQUEST['rcv_date_to'];
		$this->do_date_from = $_REQUEST['do_date_from'];
		$this->do_date_to = $_REQUEST['do_date_to'];
		$this->department_id = $_REQUEST['department_id'];
		$this->brand_id = $_REQUEST['brand_id'];
		$this->vendor_id = $_REQUEST['vendor_id'];
		//$this->use_grn = $_REQUEST['use_grn'];
		$this->group_by_sku = $_REQUEST['group_by_sku'];
		
		if(strtotime($this->apply_date_from) > strtotime($this->sales_date_to) || strtotime($this->apply_date_from) > strtotime($this->rcv_date_to)){
			$err_msg[] = "Apply Date From cannot greater than Received or Sales Date To";
		}

		if(strtotime($this->rcv_date_from) > strtotime($this->sales_date_to) || strtotime($this->rcv_date_to) < strtotime($this->apply_date_from)){
			$err_msg[] = "Received Date From cannot greater than Apply or Sales Date To";
		}

		if(strtotime($this->sales_date_to) < strtotime($this->rcv_date_from) || strtotime($this->sales_date_to) < strtotime($this->apply_date_from)){
			$err_msg[] = "Received Date From cannot greater than Received or Apply Date From";
		}
		
		if($err_msg){
			$smarty->assign("err", $err_msg);
			$this->display();
			exit;
		}
		
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

		if($this->department_id) $this->filter[] = "cc.p2 = ".mi($this->department_id);
		if($this->brand_id) $this->filter[] = "sku.brand_id in (".join(',',process_brand_id($this->brand_id)).")";
		if($this->vendor_id) $this->filter[] = "sku.vendor_id = ".mi($this->vendor_id);
		$this->filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		//parent::process_form();
	}

	function load_branch_group($id=0){
		global $con,$smarty,$con_multi;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con_multi->sql_query("select * from branch_group $where",false,false);
		if($con_multi->sql_numrows()<=0) return;
		while($r = $con_multi->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		

		// load items
		$con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con_multi->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con_multi->sql_freeresult();
		
		$this->branch_group = $branch_group;
		$smarty->assign('branch_group',$branch_group);
		return $branch_group;
	}
}

$NEW_SKU_SALES_MONITOR = new NEW_SKU_SALES_MONITOR('New SKU Sales Monitoring Report');
?>
