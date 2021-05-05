<?php
/*
9/10/2020 11:42 AM William
- Bug fixed Batch No Transaction Details Report.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
if (!$config['enable_sn_bn']) js_redirect($LANG['REPORT_CONFIG_NOT_FOUND'], "/index.php");
class BATCH_NO_TRANSACTION_DETAILS_REPORT extends Module{
	public function __construct($title){
		global $con_multi, $appCore, $smarty;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		// date
		if (!$_REQUEST['date']) $_REQUEST['date'] = date('Y-m-d');
		
		//branch
		if (BRANCH_CODE == 'HQ'){
			$q1 = $con_multi->sql_query("select id, code from branch where active=1 order by sequence, code");
			while($r = $con_multi->sql_fetchassoc($q1)){
				$branches[$r['id']] = $r;
			}
			$con_multi->sql_freeresult($q1);
			$smarty->assign("branches", $branches);
			$this->branches = $branches;

			$q1 = $con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 order by branch.sequence, branch.code");
			while($r = $con_multi->sql_fetchassoc($q1)){
				if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
				$branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
				$branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con_multi->sql_freeresult();
			
			$q1 = $con_multi->sql_query("select * from branch_group",false,false);
			if($con_multi->sql_numrows()<=0) return;
			while($r = $con_multi->sql_fetchassoc($q1)){
				if(!$branch_group['items'][$r['id']]) continue;
				$branch_group['header'][$r['id']] = $r;
			}
			$con_multi->sql_freeresult();
			$smarty->assign('branch_group',$branch_group);
		}
		
		$smarty->assign("form", $_REQUEST);
		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
		exit;
	}
	
	public function show_report(){
        global $smarty, $sessioninfo, $con_multi;
		
		$form = $_REQUEST;
		$report_header = $where = $err = array();
		
		$branch_id = mi($form['branch_id']);
		$date= $form['date'];
		$days= mi($form['days']);
		$view_type = $form['view_type'];
		$category_id = $form['category_id'];
		$times = strtotime($date);
		
		//filter by branch group
		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			$get_branch_group_code = $con_multi->sql_query("select branch_group_items.branch_id, branch_group.code
													  from branch_group 
													  join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
													  where id = $bg_id");
			while($bg = $con_multi->sql_fetchrow($get_branch_group_code)){
				$bid_list[] = $bg['branch_id'];
				$bg_code = $bg['code'];
			}
			$con_multi->sql_freeresult($get_branch_group_code);

			$report_header[] = "Branch Group: ".$bg_code;
			$bid_list = join(",",$bid_list);
			$branch_list = $bid_list; 
			$filter[] = "sbi.branch_id in($bid_list)";
		}elseif($form['branch_id'] > 0){
			$filter[] = "sbi.branch_id=".mi($form['branch_id']);
			$branch_list = mi($form['branch_id']);
			if (BRANCH_CODE == 'HQ')  $report_header[] = "Branch: ".$this->branches[$form['branch_id']]['code'];
		}else{
			$report_header[] = "Branch: All";
		}
		
		//date
		if($date)   $report_header[] = "Date: $date";
		else   $err[] = "Invalid Date";
		
		//
		if($view_type == 1){ // more than how many days
			$report_header[] = "Warranty Expired: More Than $days Day(s)";
			$defined_date = date("Y-m-d",strtotime("-$days days", $times));
			$filter[] = "sbi.expired_date <= ".ms($defined_date);
		}elseif($view_type == 2){ // is after how many days 
			$report_header[] = "Warranty Expired: After $days Day(s)";
			$defined_date = date("Y-m-d",strtotime("+$days days", $times));
			$filter[] = "sbi.expired_date >= ".ms($defined_date);
		}else{ // is within current date to how many days
			$report_header[] = "Warranty Expired: Within $days Day(s)";
			$defined_date = date("Y-m-d",strtotime("+$days days", $times));
			$filter[] = "sbi.expired_date between ".ms($date)." and ".ms($defined_date);
		}
		
		//exclude_inactive_sku
		if($form['exclude_inactive_sku']){
			$filter[] = "si.active=1";
		}
		
		if($form['all_category']){
			$report_header[] = "Category: All";
			$filter[] = "dept.department_id in ($sessioninfo[department_ids])";
		}elseif($form['category_id'] > 0){
			$filter[] = "sku.category_id = ".mi($category_id);
		}else{
			$err[] = "Invalid Category";
		}
		
		if($err){
			$smarty->assign("err", $err);
			$this->_default();
		}
		
		$filter[] = "grn.batch_status = 1";
		$where=implode(" and ", $filter);
		$tmp_data = array();
		//get parent batch_no, if child missing use the parent replace
		$q1=$con_multi->sql_query("select sbi.grn_id, sbi.grn_item_id, sbi.batch_no, sbi.qty, sbi.expired_date, sbi.sku_item_id,
		si.id as sid, si.sku_item_code as sku_item_code, branch.id as bid, branch.code as b_code, branch.report_prefix,
		adj.id as adj_id, adj.adjustment_date as adj_date, gi.sku_item_id as parent_sku_item_id, if(ai.qty>=0,'p','n') as type, ai.qty as adj_qty,
		grr.rcv_date as grn_date
		from sku_batch_items sbi
		left join grn on grn.id = sbi.grn_id and grn.branch_id =sbi.branch_id
		left join grn_items gi on gi.id = sbi.grn_item_id and gi.branch_id= sbi.branch_id and gi.grn_id = grn.id
		left join grr on grr.id = grn.grr_id and grr.branch_id = sbi.branch_id
		left join adjustment adj on adj.grn_id = grn.id and adj.branch_id = grn.branch_id
		left join adjustment_items ai on ai.adjustment_id=adj.id and adj.branch_id = ai.branch_id and ai.sku_item_id = sbi.sku_item_id
		left join sku_items si on si.id = sbi.sku_item_id
		left join sku on sku.id = si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join category dept on dept.id=cc.p2
		left join branch on branch.id = sbi.branch_id
		where $where group by parent_sku_item_id, sku_item_id, sbi.expired_date, sbi.batch_no
		order by sbi.expired_date desc");
		while($r = $con_multi->sql_fetchassoc($q1)){
			$parent_sku_item_id = mi($r['parent_sku_item_id']);
			$sku_item_id = mi($r['sku_item_id']);
			
			if($parent_sku_item_id && $sku_item_id){
				$branch_id = mi($r['bid']);
				$sku_item_code = $r['sku_item_code'];
				$adjustment_id = mi($r['adj_id']);
				$grn_id = mi($r['grn_id']);
				$grn_doc_link = "../goods_receiving_note.php?a=view&id=$grn_id&branch_id=$branch_id";
				$batch_qty = mf($r['qty']);
				$grn_formatted=sprintf("%05d",$grn_id);
				$grn_doc_no = $r['report_prefix'].$grn_formatted;
				$grn_date = $r['grn_date'];
				
				
				if($adjustment_id && $r['type']=='p'){
					$adj_data = array();
					$formatted=sprintf("%05d",$adjustment_id);
					
					$adj_data['doc_type'] = "Adjustment";
					$adj_data['grn_doc_no'] = $grn_doc_no;
					$adj_data['batch_qty'] = $batch_qty;
					$adj_data['grn_doc_link'] = $grn_doc_link;
					$adj_data['grn_date'] = $grn_date;
					$adj_data['doc_no'] = $r['report_prefix'].$formatted;
					$adj_data['doc_link'] ="../adjustment.php?a=view&id=$adjustment_id&branch_id=$branch_id";
					$adj_data['stock_in'] = mf($r['adj_qty']);
					$adj_data['expired_date'] = $r['expired_date'];
					$adj_data['first_child'] = 1;
					$adj_data['sku_item_code'] = $sku_item_code;
					$adj_data['batch_no'] = $r['batch_no'];
					$adj_data['date'] = $r['adj_date'];
					$adj_data['b_code'] = $r['b_code'];
					$tmp_data[$parent_sku_item_id][$sku_item_id][] = $adj_data;
					unset($adj_data);
				}

				$tmp_filter = array();
				//Adjustment data
				if(adjustment_id)  $tmp_filter[] = "adj.id <> $adjustment_id";
				if($branch_list)  $tmp_filter[] = "ai.branch_id in($branch_list)";
				$tmp_filter[] = "adj.status =1 and adj.approved =1";
				$tmp_filter[] = "ai.sku_item_id =$sku_item_id ";
				$tmp_filter=implode(" and ", $tmp_filter);
				$q2=$con_multi->sql_query("select adj.id as adj_id, ai.qty as qty, if(ai.qty>=0,'p','n') as type, 
				adj.adjustment_date as date, sbi.expired_date, sbi.batch_no, sbi.sku_item_id as sku_item_id,
				branch.id as bid, branch.code as b_code, branch.report_prefix
				from adjustment_items ai
				left join adjustment adj on ai.adjustment_id=adj.id and adj.branch_id = ai.branch_id
				left join sku_batch_items sbi on ai.sku_item_id = sbi.sku_item_id
				left join branch on branch.id = ai.branch_id
				where $tmp_filter group by sku_item_id, adj_id");
				while($r2 = $con_multi->sql_fetchassoc($q2)){
					$r2['doc_type'] = "Adjustment";
					$r2['grn_doc_no'] = $grn_doc_no;
					$r2['batch_qty'] = $batch_qty;
					$r2['grn_doc_link'] = $grn_doc_link;
					$r2['grn_date'] = $grn_date;
					
					$adj_id = mi($r2['adj_id']);
					$formatted=sprintf("%05d",$adj_id);
					$r2['doc_link'] = "../adjustment.php?a=view&id=$adj_id&branch_id=".mi($r2['bid']);
					$r2['doc_no'] = $r2['report_prefix'].$formatted;
					$r2['sku_item_code'] = $sku_item_code;
					if($r2['type']=='p'){
						$r2['stock_in'] = mf($r2['qty']);
					}elseif($r2['type']=='n'){
						$r2['stock_out'] = abs(mf($r2['qty']));
					}
					$tmp_data[$parent_sku_item_id][$sku_item_id][] = $r2;
				}
				$con_multi->sql_freeresult($q2);
				unset($tmp_filter);
					
					
				// DO data
				if($branch_list)  $tmp_filter[] = "do_items.branch_id in($branch_list)";
				$tmp_filter[] = "do_items.sku_item_id = $sku_item_id";
				$tmp_filter[] = "do.status =1 and do.approved =1 and do.checkout=1";
				$tmp_filter=implode(" and ", $tmp_filter);
				$q3 = $con_multi->sql_query("select do.id as do_id, do.do_no as do_no, 
				branch.id as bid, branch.code as b_code, branch.report_prefix, do_items.sku_item_id,
				sum(do_items.ctn *uom.fraction + do_items.pcs) as qty, do.do_date as date, sbi.expired_date, sbi.batch_no
				from do_items
				left join do on do_id = do.id and do_items.branch_id = do.branch_id
				left join uom on uom.id = do_items.uom_id
				left join sku_batch_items sbi on do_items.sku_item_id = sbi.sku_item_id
				left join branch on branch.id = do_items.branch_id
				where $tmp_filter group by do_items.sku_item_id, do_id");
				while($r3 = $con_multi->sql_fetchassoc($q3)){
					$r3['doc_type'] = "DO";
					$r3['grn_doc_no'] = $grn_doc_no;
					$r3['batch_qty'] = $batch_qty;
					$r3['grn_doc_link'] = $grn_doc_link;
					$r3['grn_date'] = $grn_date;
					
					$do_id = mi($r3['do_id']);
					$bid = mi($r3['bid']);
					$formatted=sprintf("%05d",$do_id);
					$r3['sku_item_code'] = $sku_item_code;
					$r3['doc_link'] = "../do.php?a=view&id=$do_id&branch_id=".mi($r3['bid']);
					$r3['doc_no'] = $r3['report_prefix'].$formatted;
					if($r3['do_no'])  $r3['doc_no'] = $r3['do_no'];
					$r3['stock_out'] = $r3['qty'];
					$tmp_data[$parent_sku_item_id][$sku_item_id][] = $r3;
				}
				$con_multi->sql_freeresult($q3);
				unset($tmp_filter);
					
				// POS data
				if($branch_list)  $tmp_filter[] = "pi.branch_id in($branch_list)";
				$tmp_filter[] = "pi.sku_item_id = $sku_item_id";
				$tmp_filter = implode(" and ", $tmp_filter);
				$q4 = $con_multi->sql_query("select pi.id pi_id, branch.code as b_code, pi.sku_item_id ,sum(pi.qty) as qty,
				pi.date as date, sbi.expired_date, sbi.batch_no
				from pos_items pi
				left join sku_batch_items sbi on pi.sku_item_id = sbi.sku_item_id
				left join pos on pos.id = pi.pos_id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
				join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
				left join branch on branch.id = pi.branch_id
				where $tmp_filter group by date, sku_item_id, pi_id");
				while($r4 = $con_multi->sql_fetchassoc($q4)){
					$r4['doc_type'] = "POS";
					$r4['grn_doc_no'] = $grn_doc_no;
					$r4['batch_qty'] = $batch_qty;
					$r4['grn_doc_link'] = $grn_doc_link;
					$r4['grn_date'] = $grn_date;
					
					$r4['stock_out'] = $r4['qty'];
					$r4['sku_item_code'] = $sku_item_code;
					$tmp_data[$parent_sku_item_id][$sku_item_id][] = $r4;
				}
				$con_multi->sql_freeresult($q4);
				unset($tmp_filter);
			}
		}
		$con_multi->sql_freeresult($q1);
		
		$table = array();
		if($tmp_data){
			$curr_time = strtotime(date("Y-m-d"));
			foreach($tmp_data as $parent_sku_item_id=>$sku_item_list){
				//parent sku info
				if(!$table[$parent_sku_item_id]['parent_sku_info']){
					$con_multi->sql_query("select sku_item_code, description, mcode, artno from sku_items where id=".mi($parent_sku_item_id));
					$parent_sku_info = $con_multi->sql_fetchrow();
					$con_multi->sql_freeresult();
					$table[$parent_sku_item_id]['parent_sku_info'] = $parent_sku_info;
				}
				foreach($sku_item_list as $sku_item_id=>$data_list){
					//sort data by date, if not the first child
					usort($data_list, array($this,"sort_date"));
					foreach($data_list as $key=>$val){
						$expired_date = $val['expired_date'];
						
						// batch info: batch no, expired_date, 	Days Expired, Days Remaining
						if(!$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']){
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['grn_doc_no'] = $val['grn_doc_no'];
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['batch_qty'] = $val['batch_qty'];
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['grn_doc_link'] = $val['grn_doc_link'];
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['grn_date'] = $val['grn_date'];

							$expired_time = strtotime($expired_date);
							$expired_date_within = mi(($curr_time - $expired_time)/86400);
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['batch_no'] = $val['batch_no'];
							$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['expired_date'] = $val['expired_date'];
							if($expired_date_within > 0)   $table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['day_expired']= $expired_date_within;
							else  $table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['batch_info']['day_remaining']= abs($expired_date_within);
						}
						$table[$parent_sku_item_id]['sku_item_list'][$sku_item_id][$expired_date]['data_list'] = $data_list;
					}
				}
			}
		}
		unset($tmp_data);
		
		$smarty->assign("report_header",join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_header));
		$smarty->assign("table", $table);
		$this->display();
	}
	
	function sort_date($a, $b){
		if($a['first_child'] || $b['first_child']){    //put the first child data on front
			if(strtotime($a['date']) >= strtotime($b['date']) && $b['first_child'])  return 1;	
			if(strtotime($b['date']) >= strtotime($a['date']) && $a['first_child'])  return 0;	
		}else{
			return strtotime($a['date']) - strtotime($b['date']);
		}
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		
		include("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=batch_no_transaction_detail_report'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();

		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Batch No Transaction Details Report To Excel");
		exit;
	}
}
$BATCH_NO_TRANSACTION_DETAILS_REPORT = new BATCH_NO_TRANSACTION_DETAILS_REPORT('Batch No Transaction Details Report');
?>