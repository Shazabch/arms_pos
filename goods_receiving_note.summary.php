<?
set_time_limit(0);
/*
Revision History
================
4/20/07 5:29:47 PM yinsee
- use GRR Received date instead of GRN Added date for date range comparison

7/9/2007 3:57:37 PM gary
- get total_selling and cost from grn_items

10/29/2007 4:43:04 PM gary
- add vendor filter.

1/7/2008 3:24:48 PM gary
- get doc_no from grr_items. 

3/19/2010 3:04:57 PM Andy
- Add note to let user know how system indicate the department.

10/27/2010 4:06:32 PM Alex
- Fix figure bugs by recalculating grn items
- add show cost privilege

1/27/2011 5:57:12 PM Alex
- add show by ibt or not
- fix show figure bugs by add filter grr items bugs while joining

3/14/2011 11:49:37 AM Alex
- change from grouping by grn department to item department

3/18/2011 4:48:42 PM Alex
- fix GRN figure bugs by adding grouping by branch id
- fix grn cost amount bugs

6/24/2011 4:11:14 PM Andy
- Make all branch default sort by sequence, code.

6/24/2011 6:11:14 PM Justin
- Added checking for GRN future to show multiple document numbers.

6/29/2011 9:47:21 AM Justin
- Added new filter "document type" to filter GRR Doc No.

6/26/2012 4:42 PM Andy
- Add allow to show "Itemize" grn if choose "All Department".

7/2/2012 3:03 PM Andy
- Add to show "Vendor Code".

7/4/2012 9:46:34 AM Justin
- Fixed bugs of showing "array" for document no.

7/17/2012 3:04 PM Justin
- Added to pickup Account ID.

7/24/2012 10:35 AM Andy
- Add print and export excel function.
- Fix document type filter bug

8/23/2012 5:35 PM Justin
- Added to pickup branch code.
- Enhanced to take out the sub query of related invoice to put it as stand alone query.

7/14/2014 3:29 PM Justin
- Bug fixed on system does not pick return qty for GRN amount.

11/18/2014 4:42 PM Justin
- Enhanced to have GST Info.

4/18/2015 10:11 AM Justin
- Bug fixed on GST amount calculate wrongly while it is not under GST.

9/23/2015 9:57 AM DingRen
- when enable use_grn_future_allow_generate_gra do not deduct return ctn/pcs.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

11/23/2017 4:33 PM Justin
- Bug fixed on "Doc No" is wrongly matched with DO number, it should also check against the delivery branch (do_branch_id).

4/25/2018 10:50 AM Justin
- Enhanced to show foreign currency.

9/7/2018 3:56 PM Justin
- Enhanced to load GST information base on cost instead of selling price.

9/13/2018 11:11 AM Justin
- Bug fixed on GST calculation is wrong when viewing the report by department.

9/19/2018 1:43 PM Justin
- Bug fixed on show mysql errors.

5/16/2019 10:27 AM William
- Pickup report_prefix for enhance "GRR".

8/26/2019 2:46 PM Andy
- Fixed when show by all department and using itemise, don't filter doc_type.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'show') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$smarty->assign("PAGE_TITLE", "GRN Summary");

// manager and above can see all department
if ($sessioninfo['level'] < 9999){
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else{
	$depts = 1;
}

// show department option
$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

// show branch option
$con->sql_query("select id, code from branch order by sequence,code");
$smarty->assign("branch", $con->sql_fetchrowset());

//show vendor option
if ($sessioninfo['vendors']){
	$vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
}
$con->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendor", $con->sql_fetchrowset());

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

$doc_type = array("PO", "INVOICE", "DO", "OTHER");
$smarty->assign("doc_type", $doc_type);

if($_REQUEST['from']){
	show_report();
}

if($_REQUEST['export_excel']){
	include_once("include/excelwriter.php");
    log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export GRN Summary");

	Header('Content-Type: application/msexcel');
	Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
	print ExcelWriter::GetHeader();
	$smarty->assign('no_header_footer', 1);
}

$smarty->display("goods_receiving_note.summary.tpl");

function show_report(){
	global $con, $smarty, $sessioninfo, $config;
		
	$where = array();
	$by_department=0;	
	$have_fc=0;

	if ($_REQUEST['user_id']) $where[] = " grn.user_id = ".mi($_REQUEST['user_id']);
	//$where[] = " grn.status < 2 and grn.active=1 and grr.rcv_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']);
	$where[] = " grr.active=1 and grn.active=1 and grn.status=1";
	$where[] = " grr.rcv_date between ".ms($_REQUEST['from'])." and ".ms($_REQUEST['to']);

	// if no branch selector, only allow own branch
	/*if (!isset($_REQUEST['branch_id']) || BRANCH_CODE != 'HQ'){
		$bid = $sessioninfo['branch_id'];
		$smarty->assign('_br', BRANCH_CODE);
	}
	*/
	
	if ($_REQUEST['show_by'] == "ibt"){
		//IBT only
		$joins="left join grr_items grr_i on grn.grr_item_id = grr_i.id and grn.branch_id=grr_i.branch_id
				left join do on do.do_no=grr_i.doc_no and grr_i.type='DO'";
		
		$where[]="(do.branch_id is not null or do.branch_id!='')";
	}elseif ($_REQUEST['show_by'] == "not_ibt"){
        //Outsider only
		$joins="left join grr_items grr_i on grn.grr_item_id = grr_i.id and grn.branch_id=grr_i.branch_id
				left join do on do.do_no=grr_i.doc_no and grr_i.type='DO'";

   		$where[]="(do.branch_id is null or do.branch_id='')";
	}

	//if no branch selector, only allow own branch
	$bid = mi($_REQUEST['branch_id']);
	if ($bid =='' && BRANCH_CODE!='HQ'){
		$bid = $sessioninfo['branch_id'];
	}	
	if($bid){ 
		$where[] = " grn.branch_id=".mi($bid);
	}
		
	
		
	if ($_REQUEST['department_id']){
		$where[] = " category.department_id = ".mi($_REQUEST['department_id']);
		$by_department=1;
		
		if($_REQUEST['doc_type']){
			$where[] = " if(grn.is_future = 0, grr_i.type=".ms($_REQUEST['doc_type']).", 1=1)";
			$check_doc_type = true;
		}
	}
	else{
		if ($sessioninfo['level']<9999){	
			$where[] = " category.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}
		if($_REQUEST['itemize'])	$by_department = 1;
	}
	if($_REQUEST['vendor_id']){
		$where[] = " grn.vendor_id=".mi($_REQUEST['vendor_id']);	
	}

	$where = join(" and ", $where);
	if (!$where) $where = "1";
	
	$smarty->assign('by_department', $by_department);

	$return_pcs="";
	if(!$config['use_grn_future_allow_generate_gra']) $return_pcs=" - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))";
	
	if (!$by_department){
		/*$con->sql_query("select sum(total_selling) as total_selling, sum(final_amount) as final_amount, count(*) as cnt, c.p1 as department_id, category.description as dept
from grn
left join grn_items gi on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
left join sku_items si on si.id=gi.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache c on sku.category_id = c.category_id
left join category on c.p1 = category.id
where $where group by c.p1");*/ 
		$grn = $total = $curr_code_dept_list = array();
		$sql="select
			round(
			(if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction +
			ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) *
			(grn.grn_tax/100), 0), 2) as final_amount,
			round((if(gi.acc_ctn is null and gi.acc_pcs is null, ifnull(gi.ctn,0) * rcv_uom.fraction + ifnull(gi.pcs,0), ifnull(gi.acc_ctn,0) * rcv_uom.fraction + ifnull(gi.acc_pcs,0))$return_pcs) * gi.selling_price / sell_uom.fraction, 2) as selling,
			round((if(gi.acc_ctn is null and gi.acc_pcs is null, ifnull(gi.ctn,0) * rcv_uom.fraction + ifnull(gi.pcs,0), ifnull(gi.acc_ctn,0) * rcv_uom.fraction + ifnull(gi.acc_pcs,0))$return_pcs) * if(grn.is_under_gst = 1, gi.gst_selling_price, gi.selling_price) / sell_uom.fraction, 2) as gst_selling,
			category.department_id, category.description as dept, grr.currency_code, grr.currency_rate, grn.is_under_gst, round(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction + ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) * (grn.grn_tax/100), 0)) * (if(gi.acc_gst_id > 0, gi.acc_gst_rate, gi.gst_rate) / 100), 2) as gst_amount
			from grn
			left join grn_items gi on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
			left join uom rcv_uom on gi.uom_id=rcv_uom.id
			left join uom sell_uom on gi.selling_uom_id=sell_uom.id
			left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
			left join sku_items on gi.sku_item_id = sku_items.id
			left join sku on sku_items.sku_id = sku.id
			left join category_cache c on sku.category_id = c.category_id
			left join category on category.id = c.p2
			$joins
			where $where";

//print $sql;

		$q1 = $con->sql_query($sql);
		
		$is_under_gst = 0;
		while($r = $con->sql_fetchassoc($q1)){
			if($r['is_under_gst']){
				$is_under_gst = 1;
			}
			
			// foreign currency
			if($r['currency_code']){
				$fc_list[$r['currency_code']] = $r['currency_code'];
				$grn[$r['department_id']]['foreign_amt'][$r['currency_code']] += $r['final_amount'];
				$total['foreign_amt'][$r['currency_code']] += $r['final_amount'];
				$myr_final_amt = $r['final_amount'] * $r['currency_rate'];
				$curr_code_dept_list[$r['department_id']] = true;
			}else{
				$myr_final_amt = $r['final_amount'];
			}
			
			$grn[$r['department_id']]['dept'] = $r['dept'];
			$grn[$r['department_id']]['final_amount'] += $myr_final_amt;
			$grn[$r['department_id']]['total_gst'] += $r['gst_amount'];
			$grn[$r['department_id']]['total_gst_amount'] += ($myr_final_amt + $r['gst_amount']);
			$grn[$r['department_id']]['total_selling'] += $r['selling'];
			$grn[$r['department_id']]['cnt']++;
			
			$total['final_amount'] += $myr_final_amt;
			$total['total_gst'] += $r['gst_amount'];
			$total['total_gst_amount'] += ($myr_final_amt + $r['gst_amount']);
			$total['total_selling'] += $r['selling'];
			$total['cnt']++;
		}
		//$smarty->display("goods_receiving_note.summary.top.tpl");
	}
	else{	
	    $sql="select grn.*, grr.rcv_date, grr_i.doc_no, grr_i.type as doc_type, if (vendor.id, vendor.description, b1.description) as vendor, do.branch_id as do_bid, do.id as do_id, po.branch_id as po_bid , po.id as po_id,
   		sum(round((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction + ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) * (grn.grn_tax/100), 0), 2)) as final_amount,
        sum((if(gi.acc_ctn is null and gi.acc_pcs is null, ifnull(gi.ctn,0) * rcv_uom.fraction + ifnull(gi.pcs,0), ifnull(gi.acc_ctn,0) * rcv_uom.fraction + ifnull(gi.acc_pcs,0))$return_pcs) * gi.selling_price / sell_uom.fraction) as total_selling, vendor.code as vendor_code, b.code as branch_code,
		if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
		grr.currency_code, grr.currency_rate,
		sum(round(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction + ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs)$return_pcs) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) * (grn.grn_tax/100), 0)) * (if(gi.acc_gst_id > 0, gi.acc_gst_rate, gi.gst_rate) / 100), 2)) as gst_amount ,branch.report_prefix
		from grn_items gi
		left join branch on gi.branch_id = branch.id
		left join grn on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
		left join uom rcv_uom on gi.uom_id=rcv_uom.id
		left join uom sell_uom on gi.selling_uom_id=sell_uom.id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join vendor on grn.vendor_id = vendor.id
		left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gi.branch_id
		left join grr_items grr_i on grn.grr_item_id = grr_i.id and grn.branch_id=grr_i.branch_id
		left join do on do.do_no=grr_i.doc_no and grr_i.type='DO' and do.do_branch_id = grn.branch_id
		left join branch b1 on b1.id=do.branch_id
		left join po on po.po_no=grr_i.doc_no and grr_i.type='PO' and po.po_branch_id = grn.branch_id and po.is_ibt = 1
		left join sku_items si on gi.sku_item_id = si.id
		left join sku on si.sku_id = sku.id
		left join category_cache c on sku.category_id = c.category_id
		left join category on category.id = c.p2
		left join branch b on b.id = grn.branch_id 
		where $where 
		group by grn.branch_id,grn.id 
		order by vendor, grn.added";

		$sql_result = $con->sql_query($sql);

		$is_under_gst = 0;
		while($r1=$con->sql_fetchassoc($sql_result)){
			if($r1['is_future']){
				$sql2 = $con->sql_query("select group_concat(distinct doc_no separator ',') as doc_no, type as doc_type, 
										 case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc, do.branch_id as do_bid, do.id as do_id, po.branch_id as po_bid
										 from grr_items gi
										 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id
										 left join do on do.do_no=gi.doc_no and gi.type='DO'
										 left join po on po.po_no=gi.doc_no and gi.type='PO'
										 where gi.grr_id = ".mi($r1['grr_id'])." and gi.branch_id = ".mi($r1['branch_id'])."
										 group by type_asc
										 order by type_asc ASC limit 1");

				$r2=$con->sql_fetchassoc($sql2);
				$con->sql_freeresult($sql2);

				if($r2['doc_type'] == "PO" || $r2['doc_type'] == "DO"){
					if(explode(",", $r2['doc_no'])){
						$splt_doc_no = explode(",", $r2['doc_no']);
						$r1['doc_no'] = $splt_doc_no;
					}else{
						$r1['doc_no'][] = $r2['doc_no'];
					}
					
					if($r2['doc_type'] == "PO"){
						foreach($r1['doc_no'] as $doc_no){
							$sql3 = $con->sql_query("select * from po where po.po_no = ".ms($doc_no));
							$po_info = $con->sql_fetchassoc($sql3);
							$con->sql_freeresult($sql3);
							$r1['set_po_id'][] = $po_info['id'];
							$r1['set_po_bid'][] = $po_info['branch_id'];
						}
					}
				}else $r1['doc_no'] = $r2['doc_no'];
				
				if(is_array($r1['doc_no'])) $r1['doc_no'] = join(",", $r1['doc_no']);
				$r1['doc_type'] = $r2['doc_type'];
				
				if($check_doc_type && $_REQUEST['doc_type'] && $r1['doc_type'] != $_REQUEST['doc_type']) continue;
			}
			
			if($config['grn_summary_show_related_invoice']){
				$q1 = $con->sql_query("select group_concat(gi.doc_no order by 1 separator ', ') as related_invoice from grr_items gi where gi.type='INVOICE' and gi.grr_id=".mi($r1['grr_id'])." and gi.branch_id=".mi($r1['branch_id']));
				$tmp = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$r1['related_invoice'] = $tmp['related_invoice'];
			}
			
			if($r1['is_under_gst']){
				$is_under_gst = 1;
			}
			
			if($r1['currency_code']){
				$have_fc = 1;
				$total['itemise'][$r1['id']][$r1['branch_id']]['foreign_amt'] = $r1['final_amount'];
				$myr_final_amt = $r1['final_amount'] * $r1['currency_rate'];
			}else{
				$myr_final_amt = $r1['final_amount'];
			}
			
			// store all the grn
			$grn[$r1['id']][$r1['branch_id']] = $r1;
			
			// grn amount info
			$total['itemise'][$r1['id']][$r1['branch_id']]['final_amount'] = $myr_final_amt;
			$total['itemise'][$r1['id']][$r1['branch_id']]['total_selling'] = $r1['total_selling'];
			/*$total['itemise'][$r1['id']][$r1['branch_id']]['total_gst_selling'] = $r1['total_gst_selling'];
			$row_gst = round($r1['total_gst_selling']-$r1['total_selling'], 2);*/
			$total['itemise'][$r1['id']][$r1['branch_id']]['total_gst'] = $r1['gst_amount'];
			$total['itemise'][$r1['id']][$r1['branch_id']]['total_gst_amount'] = $myr_final_amt + $r1['gst_amount'];
			
			// grand total
			$total['final_amount'] += $myr_final_amt;
			$total['total_selling'] += $r1['total_selling'];
			/*$total['total_gst_selling'] += $r1['total_gst_selling'];*/
			$total['total_gst'] += $r1['gst_amount'];
			$total['total_gst_amount'] += $myr_final_amt + $r1['gst_amount'];
		}
	}
	$con->sql_freeresult($sql_result);

	//echo"<pre>";print_r($grn['remark']);echo"</pre>";
	$smarty->assign("is_under_gst", $is_under_gst);
	$smarty->assign("have_fc", $have_fc);
	$smarty->assign("grn", $grn);
	$smarty->assign("total", $total);
	if($fc_list){
		sort($fc_list);
		$smarty->assign("fc_list", $fc_list);
		$smarty->assign("curr_code_dept_list", $curr_code_dept_list);
	}
	
	//$smarty->display("goods_receiving_note.summary.detail.tpl");
}
?>
