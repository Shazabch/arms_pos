<?php
/*
12/28/2016 3:52 PM Andy
- Fixed sometime will sum up multiple time due to one cn got multiple return receipt.

1/6/2017 09:40 AM Qiu Ying
- Fixed bug on GST Credit Note Report show wrong info when make multiple goods return in a single receipt

1/9/2017 11:10 AM Qiu Ying
- Fixed bug on GST Credit Note Report show wrong info when make multiple goods return in a single receipt

1/9/2017 17:40 Qiu Ying
- Bug fixed on Return Receipt Info not showing

2/25/2020 9:48 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
include_once('pos_report.include.php');

$maintenance->check(92);

ini_set('memory_limit', '512M');
set_time_limit(0);

class GST_CREDIT_NOTE_REPORT extends Module{
	function __construct($title) {
		global $con, $config, $smarty, $sessioninfo, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
        
		$this->branch_list = load_branches();
		$this->title = $title;
		
        $form['date_from'] = date('Y-m-d',strtotime('-30 day',time()));
		$form['date_to'] = date('Y-m-d');		
		
		$smarty->assign('form',$form);
		parent::__construct($title);
	}
	
	function _default() {
		$this->display();
	}
	
	function show_report() {
		global $smarty, $sessioninfo;
		$form = $_REQUEST;
		$this->form = $form;
		
		$this->generate_report();
		
		if($form['export_excel']) {
			include_once("include/excelwriter.php");
			log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");
			Header('Content-Type: application/msexcel');
			Header('Content-Disposition: attachment;filename=gst_credit_note_report_'.time().'.xls');
			print ExcelWriter::GetHeader();
			$smarty->assign('no_header_footer', 1);	
		}
		$this->form['form_submit'] = 1;
		
		$smarty->assign('form',$this->form);
		$this->display();
	}
	
	function generate_report() {
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		$err = array();
		if (strtotime($this->form['date_from']) > strtotime($this->form['date_to'])) {
			$err[] = 'Date From cannot be later than Date To';
		}
		
		if($err) {
			$smarty->assign('err', $err);
			return;
		}
		
		if(strtotime($this->form['date_to']) > strtotime("+ 30 day", strtotime($this->form['date_from']))){
			$this->form['date_from'] = date('Y-m-d',strtotime('-30 day',strtotime($this->form['date_to'])));
		}
		
		if($this->form['branch_id'] != '') {
			$filter = " and pcn.branch_id = ".mi($this->form['branch_id']);
		}else{
			$filter = " and pcn.branch_id in (".join(', ', array_keys($this->branch_list)).")";
		}
		
		$pcn_query = $con_multi->sql_query("select pcn.branch_id, pcn.pos_id, pcn.counter_id, pcn.date, pcn.credit_note_no, pcn.credit_note_ref_no, cs.network_name, p.receipt_no, p.receipt_ref_no
									 from pos_credit_note pcn
									 join pos p on p.branch_id=pcn.branch_id and p.id=pcn.pos_id and p.counter_id = pcn.counter_id and p.date = pcn.date
									 join counter_settings cs on cs.id=p.counter_id and cs.branch_id=p.branch_id
									 where p.cancel_status = 0 and pcn.date between ".ms($this->form['date_from'])." and ".ms($this->form['date_to'])." $filter
									 group by pcn.branch_id, pcn.pos_id, pcn.counter_id, pcn.date
									 order by pcn.date, pcn.credit_note_no");
		while($r = $con_multi->sql_fetchassoc($pcn_query)) {
			$where = array();
			$where[] = "pgr.pos_id = ".ms($r['pos_id']);
			$where[] = "pgr.branch_id = ".ms($r['branch_id']);
			$where[] = "pgr.counter_id = ".ms($r['counter_id']);
			$where[] = "pgr.date = ".ms($r['date']);
			$cond = "where ".implode(" and ",$where);
			
			// get items
			$tmp_return_receipt_ref_no = "";
			$pi_query = $con_multi->sql_query("select si.sku_item_code, si.mcode, si.artno, si.description, abs(round(pi.price-pi.discount-pi.discount2,2)) as amt_inc_gst, abs(round(pi.tax_amount,2)) as gst_amt, abs(round(pi.before_tax_price,2)) as amt_before_gst,
			rcs.network_name as return_network_name, pgr.return_receipt_no, rp.receipt_ref_no as return_receipt_ref_no, pgr.return_date, pi.discount, pi.discount2
										from pos_items pi
										join sku_items si on pi.sku_item_id=si.id
										join pos_goods_return pgr on pgr.branch_id=pi.branch_id and pgr.pos_id=pi.pos_id and pgr.counter_id=pi.counter_id and pgr.date=pi.date and pgr.item_id=pi.item_id
										join pos rp on rp.branch_id=pgr.branch_id and rp.id=pgr.return_pos_id and rp.counter_id = pgr.return_counter_id and rp.date = pgr.return_date
										join counter_settings rcs on rcs.id=pgr.return_counter_id and rcs.branch_id=pgr.branch_id
										" . $cond);
			while($s = $con_multi->sql_fetchassoc($pi_query)) {
				$r['amt_inc_gst'] += $s['amt_inc_gst'];
				$r['amt_before_gst'] += $s['amt_before_gst'];
				$r['gst_amt'] += $s['gst_amt'];
				
				// header info for return receipt_no
				$return_info = array();
				if (!$tmp_return_receipt_ref_no || ($tmp_return_receipt_ref_no != $s['return_receipt_ref_no'])){
					$return_info['return_network_name'] = $s['return_network_name'];
					$return_info['return_receipt_no'] = $s['return_receipt_no'];
					$return_info['return_receipt_ref_no'] = $s['return_receipt_ref_no'];
					$return_info['return_date'] = $s['return_date'];
					$r['return_info'][] = $return_info;
					$tmp_return_receipt_ref_no = $s['return_receipt_ref_no'];
				}
				
				if($this->form['report_type'] == 'detail') {
					$r['item'][] = $s;
				}
			}
			$con_multi->sql_freeresult($pi_query);
						
			$data[$r['branch_id']][] = $r;
			
			$total[$r['branch_id']]['amt_inc_gst'] += $r['amt_inc_gst'];
			$total[$r['branch_id']]['gst_amt'] += $r['gst_amt'];
			$total[$r['branch_id']]['amt_before_gst'] += $r['amt_before_gst'];
		}
		$con_multi->sql_freeresult($pcn_query);
		
		$report_title = array();
		$report_title[] = "Branch: ".($this->form['branch_id']?$this->branch_list[$this->form['branch_id']]:'All');
		$report_title[] = "Date: From ".$this->form['date_from']." to ".$this->form['date_to'];
		$report_title = join(" &nbsp;&nbsp;&nbsp; ", $report_title);
		
		if($this->form['report_type'] == 'detail') {
			$smarty->assign('show_detail', 1);
		}
		$smarty->assign('report_title', $report_title);
		$smarty->assign('data', $data);
		$smarty->assign('total', $total);
	}
}

$GST_CREDIT_NOTE_REPORT = new GST_CREDIT_NOTE_REPORT('GST Credit Note Report');
?>