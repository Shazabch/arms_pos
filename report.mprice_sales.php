<?php
/*
1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

2/21/2020 2:41 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");

class MPRICE_SALES_REPORT extends Module{

	function __construct($title){
		global $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		$this->init_selection();
		parent::__construct($title);
	}
	
	function init_selection(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$branch_list = array();
		$sku_type_list = array();
		
		//retrieve branch list
		$bq = $con_multi->sql_query("select id, code from branch where active = 1");
		while ($bd = $con_multi->sql_fetchassoc($bq)) {
			$branch_list[$bd['id']] = $bd;
		}
		$con_multi->sql_freeresult($bq);
		$this->branch_list = $branch_list;
		$smarty->assign('branch_list',$branch_list);
		
		//retrieve sku type list
		$stq = $con_multi->sql_query("select * from sku_type");
		while($std = $con_multi->sql_fetchassoc($stq)){
			$this->sku_type_list[] = $std;
		}
		$con_multi->sql_freeresult($stq);
		$smarty->assign('sku_type_list', $this->sku_type_list);
		
		//sort field and sort order
		$sort_field_list = array("si.sku_item_code"=>'ARMS Code', "si.mcode"=>'MCode', "si.artno"=>'Art NO', "si.description"=>'Description', "pi.mprice_type"=>'MPrice');
		$sort_order_list = array("asc"=>'Ascending', "desc"=>'Descending');
		$smarty->assign("sort_field_list", $sort_field_list);
		$smarty->assign("sort_order_list", $sort_order_list);
	}
	
	function _default(){
		global $smarty;
		
		$form = $_REQUEST;
		
		if(!isset($form['date_from'])&&!isset($form['date_to'])){
			$form['date_from'] = date('Y-m-d',strtotime('-7 day',time()));
			$form['date_to'] = date('Y-m-d');
		}elseif(strtotime($form['date_to']) > strtotime("+ 30 day", strtotime($form['date_from']))){
			$form['date_to'] = date('Y-m-d',strtotime('+30 day',strtotime($form['date_from'])));
		}

		$this->form = $form;
		
		if ($form['form_submit']) {
			$this->generate_report();
			if ($form['export_excel']) {
				include_once("include/excelwriter.php");
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=mprice_sales_report_'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		$smarty->assign('form',$this->form);
		$this->display();
	}
	
	function generate_report(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		if (strtotime($this->form['date_from']) > strtotime($this->form['date_to'])) {
			$smarty->assign('date_error','Date From cannot be later than Date To');
			return;
		}
		
		$filter = array();
		$filter[] = "where p.branch_id = ".$this->form['branch_id'];
		$filter[] = "p.date between ".ms($this->form['date_from'])." and ".ms($this->form['date_to']);
		
		if ($this->form['sku_type']) {
			$filter[] = "sku.sku_type = ".ms($this->form['sku_type']);
		}
		
		if(isset($this->form['sku_code_list'])) {
			$sku_code_list = join(",", array_map("ms", $this->form['sku_code_list']));
		    // select sku item id list
		 	$con_multi->sql_query("select * from sku_items where sku_item_code in ($sku_code_list)") or die(mysql_error());
			while($r = $con_multi->sql_fetchassoc()){
				$sid_list[] = mi($r['id']);
				$group_item[] = $r;
			}
			$sid_list = join(", ", $sid_list);
			$filter[] = "pi.sku_item_id in ($sid_list)";
			$con_multi->sql_freeresult();
		}
		
		if($this->form['show_finalized']) {
			$show_finalized = true;
			$filter[] = "pf.finalized=1";
		}
		else	$show_finalized = false;
		
		$filter[] = "pi.mprice_type <> '' and p.cancel_status=0";
		$filter = join(" and ", $filter);
		
		$sorting = "order by date, ".$this->form['sort_field']." ".$this->form['sort_order'].($this->form['sort_field'] == 'pi.mprice_type' ? "":", pi.mprice_type");
		
		$sql = "select si.sku_item_code, si.mcode, si.artno, si.description, pi.date, pi.mprice_type, sum(pi.qty) as qty, sum(pi.price-pi.discount-pi.discount2-pi.tax_amount) as amt, sum(pi.tax_amount) as gst_amt, pi.sku_item_id, sku.sku_type, pf.finalized
				from pos_items pi
				join pos p on p.branch_id=pi.branch_id and p.date=pi.date and p.counter_id=pi.counter_id and p.id=pi.pos_id
				join sku_items si on si.id=pi.sku_item_id
				left join sku on sku.id=si.sku_id
				join pos_finalized pf on pf.branch_id=pi.branch_id and pf.date=pi.date  
				$filter group by pi.sku_item_id, pi.date, pi.mprice_type $sorting";
		
		$group_total = $total = $tmp_total = array();
		$show_gst_amt = 0;
		
		$query = $con_multi->sql_query($sql);
		while ($q = $con_multi->sql_fetchrow($query)) {
			$total_by_date[$q['date']]['finalized'] = $q['finalized'];
			$total_by_date[$q['date']]['qty'] += $q['qty'];
			$total_by_date[$q['date']]['amt'] += $q['amt'];
			$total_by_date[$q['date']]['gst_amt'] += $q['gst_amt'];
			$total['mprice_total'][$q['mprice_type']]['qty'] += $q['qty'];
			$total['mprice_total'][$q['mprice_type']]['amt'] += $q['amt'];
			$total['mprice_total'][$q['mprice_type']]['gst_amt'] += $q['gst_amt'];
			$total['total']['qty'] += $q['qty'];
			$total['total']['amt'] += $q['amt'];
			$total['total']['gst_amt'] += $q['gst_amt'];
			
			if ($q['gst_amt'] > 0)	$show_gst_amt = 1;
			
			//get cost, gp amount and gp%
			if($q['finalized'] == 1) {
				$gp_sql = $con_multi->sql_query("select (cost/qty) as item_cost
											from sku_items_sales_cache_b".$this->form['branch_id']."
											where date=".ms($q['date'])." and sku_item_id=".$q['sku_item_id']);
				
				$gp_data = $con_multi->sql_fetchassoc($gp_sql);
				$con_multi->sql_freeresult($gp_sql);
				$q['cost'] = $gp_data['item_cost'] * $q['qty'];
				$q['gp_amt'] = $q['amt'] - $q['cost'];
				
				$total_by_date[$q['date']]['cost'] += $q['cost'];
				$total_by_date[$q['date']]['gp_amt'] += $q['gp_amt'];
				$total['mprice_total'][$q['mprice_type']]['cost'] += $q['cost'];
				$total['mprice_total'][$q['mprice_type']]['gp_amt'] += $q['gp_amt'];
				$total['total']['cost'] += $q['cost'];
				$total['total']['gp_amt'] += $q['gp_amt'];	
			}
			$data[] = $q;
		}
		$con_multi->sql_freeresult($query);
		if($total['mprice_total'])	ksort($total['mprice_total']);
		
		$report_title = array();
		$report_title[] = "Branch: ".$this->branch_list[$this->form['branch_id']]['code'];
		$report_title[] = "SKU Type: ".($this->form['sku_type']?$this->form['sku_type']:"All");
		$report_title[] = "Date: From ".$this->form['date_from']." to ".$this->form['date_to'];
		if($show_finalized)	$report_title[] = "(Finalized POS only)";
		$report_title = join(" &nbsp;&nbsp;&nbsp; ", $report_title);
		
		$smarty->assign("report_title", $report_title);
		$smarty->assign("group_item", $group_item);
		$smarty->assign("show_finalized", $show_finalized);
		$smarty->assign("show_gst_amt", $show_gst_amt);
		$smarty->assign("data", $data);
		$smarty->assign("total_by_date", $total_by_date);
		$smarty->assign("total", $total);
	}
}

$MPRICE_SALES_REPORT = new MPRICE_SALES_REPORT('MPrice Sales Report');
?>
