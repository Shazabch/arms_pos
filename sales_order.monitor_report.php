<?php
/*
08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/20/2017 10:25 AM Justin
- Enhanced to have privilege checking.

9/2/2020 9:00 AM William
- Bug fixed report unable to export.
 */
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
if (!privilege('SO_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SO_REPORT', BRANCH_CODE), "/index.php");
$maintenance->check(1);

include("sales_order.include.php");

class SALES_ORDER_MONITOR_REPORT extends Module{
	var $branch_id = 0;
	
	function __construct($title){
		global $con, $smarty;
		
		if(!$_REQUEST['skip_init_load'])    init_so_selection();
		
		$this->init_value();
		
		parent::__construct($title);
	}
	
	function _default(){
		global $smarty, $sessioninfo;
		
		$this->get_area_batch_code(true);
		if(isset($_REQUEST['area']) && $_REQUEST['area']!='NO_DATA')	$this->get_batch_code(true);
		
		if($_REQUEST['show_report']){
			$this->generate_report();
			
			if($_REQUEST['export_excel']){
				include("include/excelwriter.php");
				$smarty->assign('no_header_footer', true);
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=sales_order_monitor_report'.time().'.xls');
				print ExcelWriter::GetHeader();
				log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Sales Order Monitor Report To Excel");
			}
		}
		
		$this->display();
	}
	
	function connect_report_server(){
		global $con_multi;
		
		if(!$con_multi)	$con_multi = new mysql_multi();
	}
	
	private function init_value(){
		global $con, $smarty, $sessioninfo;
		
		if(!isset($_REQUEST['date_from']))	$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month", time()));
		if(!isset($_REQUEST['date_to']))	$_REQUEST['date_to'] = date("Y-m-d");
		
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
	}
	
	function get_area_batch_code($sqlonly = false){
		global $con, $smarty;
		
		if($this->branch_id){
			$sql = "Select distinct d.area, so.batch_code 
					from sales_order so
	   				left join debtor d on d.id=so.debtor_id
					where so.active=1 and so.status=1 and so.approved=1 and so.branch_id=$this->branch_id
					order by d.area, so.batch_code";
			$con->sql_query($sql);		
			$area_list = $batch_code_list = array();
			while($r = $con->sql_fetchassoc()){
				if(!in_array($r['area'],$area_list))	$area_list[] = $r['area'];
				if(!in_array($r['batch_code'],$batch_code_list))	$batch_code_list[] = $r['batch_code'];
			}
			$con->sql_freeresult();
			$smarty->assign('area_list', $area_list);
			$smarty->assign('batch_code_list', $batch_code_list);
		}
		
		if(!$sqlonly){
			$ret = array();
			$ret['ok'] = 1;
			$ret['area_html'] = $smarty->fetch('sales_order.monitor_report.area_list.tpl');
			$ret['batch_code_html'] = $smarty->fetch('sales_order.monitor_report.batch_code_list.tpl');
			print json_encode($ret);
		}
	}
	
	function get_batch_code($sqlonly = false){
		global $con, $smarty;
		
		if($this->branch_id && isset($_REQUEST['area']) && $_REQUEST['area']!='NO_DATA'){
			$filter = array();
			$filter[] = "so.active=1 and so.status=1 and so.approved=1 and so.branch_id=$this->branch_id";
			if($_REQUEST['area']!='all')	$filter[] = "d.area=".ms($_REQUEST['area']);
			
			$filter = "where ".join(' and ', $filter);
			
			$sql = "Select distinct so.batch_code 
					from sales_order so
	   				left join debtor d on d.id=so.debtor_id
					$filter
					order by so.batch_code";
			$con->sql_query($sql);		
			$batch_code_list = array();
			while($r = $con->sql_fetchassoc()){
				if(!in_array($r['batch_code'],$batch_code_list))	$batch_code_list[] = $r['batch_code'];
			}
			$con->sql_freeresult();
			$smarty->assign('batch_code_list', $batch_code_list);
		}
		
		if(!$sqlonly){
			$ret = array();
			$ret['ok'] = 1;
			$ret['batch_code_html'] = $smarty->fetch('sales_order.monitor_report.batch_code_list.tpl');
			print json_encode($ret);
		}
	}
	
	private function generate_report(){
		global $con, $smarty, $con_multi;
		
		//print_r($_REQUEST);
		
		$bid = mi($this->branch_id);
		$date_from = trim($_REQUEST['date_from']);
		$date_to = trim($_REQUEST['date_to']);
		$area = trim($_REQUEST['area']);
		$batch_code = trim($_REQUEST['batch_code']);
		
		// checking
		$err = array();
		if(!$bid)	$err[] = "Please select branch.";
		if(strtotime($date_from) > strtotime($date_to))	$err[] = "Date from cannot more than Date to.";
		else{
			if(!strtotime($date_from))	$err[] = "Please select Date from.";
			if(!strtotime($date_to))	$err[] = "Please select Date to.";
		}
		
		if($area=='NO_DATA')	$err[] = "Please select area.";
		if($batch_code=='NO_DATA')	$err[] = "Please select batch code.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$report_title = array();
		$report_title[] = "Date from $date_from to $date_to";
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Area: ".($area=='all' ? 'All' : $area);
		$report_title[] = "Batch Code: ".($batch_code=='all' ? 'All' : $batch_code);
		
		$this->connect_report_server();
		
		$filter = array();
		$filter[] = "so.branch_id=$bid";
		$filter[] = "so.order_date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "so.active=1 and so.status=1 and so.approved=1";
		if($area!='all')	$filter[] = "d.area=".ms($area);
		if($batch_code!='all')	$filter[] = "so.batch_code=".ms($batch_code);
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select soi.*,si.sku_item_code,si.artno,si.mcode,si.description,uom.fraction, (soi.ctn*uom.fraction)+soi.pcs as total_qty, round(((soi.ctn*uom.fraction)+soi.pcs)*(soi.selling_price/uom.fraction)-soi.item_discount_amount,2) as total_amt, so.order_no,d.description as debtor_desc
from sales_order so
join sales_order_items soi on soi.branch_id=so.branch_id and soi.sales_order_id=so.id
left join debtor d on d.id=so.debtor_id
left join sku_items si on si.id=soi.sku_item_id
left join uom on uom.id=soi.uom_id
$filter
order by so.order_no";
		//print $sql;
		$q1 = $con_multi->sql_query($sql);
		$this->data = array();
		while($r = $con_multi->sql_fetchassoc($q1)){
			$so_key = $r['branch_id'].'-'.$r['sales_order_id'];
			
			// sku item info
			if(!isset($this->data['si_info'][$r['sku_item_id']])){
				$this->data['si_info'][$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
				$this->data['si_info'][$r['sku_item_id']]['artno'] = $r['artno'];
				$this->data['si_info'][$r['sku_item_id']]['mcode'] = $r['mcode'];
				$this->data['si_info'][$r['sku_item_id']]['description'] = $r['description'];
			}
			
			// sales order info
			if(!isset($this->data['so_info'][$so_key])){
				$this->data['so_info'][$so_key]['branch_id'] = $r['branch_id'];
				$this->data['so_info'][$so_key]['sales_order_id'] = $r['sales_order_id'];
				$this->data['so_info'][$so_key]['order_no'] = $r['order_no'];
				$this->data['so_info'][$so_key]['debtor_desc'] = $r['debtor_desc'];
			}
			
			$po_info = get_sales_order_items_po_info($r['branch_id'], $r['id']);
			$this->data['by_so'][$so_key][$r['id']]['sid'] = $r['sku_item_id'];
			if($po_info)	$this->data['by_so'][$so_key][$r['id']]['po_info'] = $po_info;
			
			/*if(!is_array($this->data['by_items'][$r['sku_item_id']]['po_info']['arr_list'])){
				$this->data['by_items'][$r['sku_item_id']]['po_info']['arr_list'] = array();
				$this->data['by_items'][$r['sku_item_id']]['po_info']['arr_list'][] = $po_info;
			}*/
			
			// sales order data
			$this->data['by_so'][$so_key][$r['id']]['so_total_qty'] += $r['total_qty'];
			//$this->data['by_items'][$r['sku_item_id']]['so_total_qty'] += $r['total_qty'];
			//$this->data['by_items'][$r['sku_item_id']]['so_total_amt'] += round($r['total_amt'], 2);
			
			// po data
			$this->data['by_so'][$so_key][$r['id']]['po_total_qty'] += $r['total_purchase_qty'];
			//$this->data['by_items'][$r['sku_item_id']]['po_total_qty'] += $po_info['total_purchase_qty'];
			//$this->data['by_items'][$r['sku_item_id']]['po_total_amt'] += round($po_info['total_purchase_qty']*$po_info['selling_price'], 2);
	
			// grn qty - get grn info by PO
			if($po_info && $po_info['po_no'] && $po_info['po_branch_id']){
				$grn_bid = mi($po_info['po_branch_id']);
				
				$q_grn = $con_multi->sql_query("select (if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn *rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as qty
				from grn_items gi
				left join grn on grn.branch_id=gi.branch_id and grn.id=gi.grn_id
				left join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
				left join uom rcv_uom on gi.uom_id=rcv_uom.id
				join grr_items gri on gri.branch_id=grr.branch_id and gri.grr_id=grr.id and gri.type='PO'
				where gi.branch_id=".mi($po_info['po_branch_id'])." and gi.po_item_id=".mi($po_info['poi_id'])." and gri.doc_no=".ms($po_info['po_no']));
				$tmp = $con_multi->sql_fetchassoc($q_grn);
				$con_multi->sql_freeresult($q_grn);
				
				$this->data['by_so'][$so_key][$r['id']]['grn_total_qty'] += $tmp['qty'];
				//$this->data['by_items'][$r['sku_item_id']]['grn_total_qty'] += $tmp['qty'];
			}
				
			// DO data
			$this->data['by_so'][$so_key][$r['id']]['do_total_qty'] += $r['do_qty'];
			//$this->data['by_items'][$r['sku_item_id']]['do_total_qty'] += $r['do_qty'];
			
		}
		$con_multi->sql_freeresult($q1);
	
		//print_r($this->data);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$con_multi->close_connection();	
	}
}

$SALES_ORDER_MONITOR_REPORT = new SALES_ORDER_MONITOR_REPORT('Sales Order Monitor Report');
?>
