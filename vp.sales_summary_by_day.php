<?php
/*
8/2/2012 9:59 AM Andy
- fix report to sort by date.

8/2/2012 2:40 PM Andy
- Add cost and gp.

8/11/2012 yinsee
- add sales_report_profit

9/6/2012 4:17 PM Andy
- Add can email report to vendor by terminal.

9/7/2012 10:12 AM Andy
- Changes to get last 7 days sales instead of 60 days.
- Add -email_to parameter for alternative email to address.

10/3/2012 12:06 PM Justin
- Enhanced to lookup sales report profit percentage by branch.

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/18/2013 4:09 PM Andy
- Change all $mailer->send() to to call phpmailer_send().

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

1/7/2020 5:41 PM Justin
- Removed the IsMail since it causes customers who are using smtp couldn't send out email.
*/

/*
cron
php vp.sales_summary_by_day.php email_sales -branch_id 1 -vendor_id 571 -email_to justin@wsatp.com

or all
php vp.sales_summary_by_day.php email_sales -email_to andyloh@wsatp.com
*/
include('include/common.php');
$maintenance->check(161);

if(is_using_terminal()){
	define('TERMINAL', 1);
	fix_terminal_smarty();
	$agrs = $_SERVER['argv'];
	$_REQUEST['a'] = $agrs[1];
	if(!$_REQUEST['a'])	die("Invalid Action\n");
}else{
	if(!$vp_session)	js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}


class SALES_SUMMARY_REPORT_BY_DAY extends Module{
	var $bid = 0;

	function __construct($title){
		global $con, $smarty, $vp_session, $config;

		$this->bid = $vp_session['branch_id'];
		
		parent::__construct($title);
	}

	function _default(){
		global $vp_session, $smarty;
		
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day", strtotime($_REQUEST['date_to'])));
		}
		
		if($_REQUEST['load_report']){
			if($_REQUEST['submit_type']=='excel'){	// export excel
				include_once("include/excelwriter.php");
				log_vp($vp_session['id'], "VENDOR REPORT", 0, "Export ".$this->title);
	
				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			$this->load_report();
		}
		$this->display();
	}

	private function load_report($params = array()){
		global $con, $smarty, $vp_session, $config, $con_multi;
		
		//print_r($vp_session);
		
		$bid = mi($this->bid);
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		
		$dt1 = strtotime($date_from);
		$dt2 = strtotime($date_to);
		
		$err = array();
		if(!$bid)	$err[] = "Invalid Branch.";
		if(!$date_from || !$dt1)	$err[] = "Invalid Date From.";
		if(!$date_to || !$dt2)	$err[] = "Invalid Date To.";
		
		if(!$err && $dt1 > $dt2)	$err[] = "Date to cannot early then date from.";
		if(!$err && date("Y", strtotime($date_from))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$time_diff = $dt2 - $dt1;
		$date_diff = mi($time_diff/86400);
		if(!$err && $date_diff>90)	$err[] = "Report maximum show 90 days of transaction.";
		
		$sku_group_bid = mi($vp_session['sku_group_bid']);
		$sku_group_id = mi($vp_session['sku_group_id']);
        //$sales_report_profit = doubleval($vp_session['vp']['sales_report_profit'][$bid]);
        
        if(isset($params['sku_group_bid']))	$sku_group_bid = $params['sku_group_bid'];
        if(isset($params['sku_group_id']))	$sku_group_id = $params['sku_group_id'];
        if(isset($params['sales_report_profit_by_date_list']))	$sales_report_profit_by_date_list = $params['sales_report_profit_by_date_list'];
		
		if(!$sku_group_bid || !$sku_group_id)	$err[] = "Error on SKU Group setup, please contact admin to solve this.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$tbl = "sku_items_sales_cache_b".$bid;
		
		$sql = "select tbl.sku_item_id, tbl.date, tbl.amount, tbl.qty, si.sku_item_code,si.mcode, si.artno, si.link_code,si.description, c.department_id,sku.vendor_id, sku.brand_id, sku.trade_discount_type,sku.default_trade_discount_code
		from sku_group_item sgi
		join sku_items si on si.sku_item_code=sgi.sku_item_code
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		join $tbl tbl on tbl.sku_item_id=si.id
		where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id and tbl.date between ".ms($date_from)." and ".ms($date_to)." order by tbl.date";
		
		//print $sql;
		if(!$con_multi)	$con_multi= new mysql_multi();
		
		$this->data = array();
		$last_date = '';
		$tmp_item_trade_discount_code = array();
		
		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			// get srp by date
			$sales_report_profit_by_date = get_vp_sales_report_profit_by_date($r['date'], $sales_report_profit_by_date_list);
		
			if($r['date'] != $last_date){
				$last_date = $r['date'];
				$tmp_item_trade_discount_code = array();
			}
			
			$sid = mi($r['sku_item_id']);
			
			if ($sales_report_profit_by_date > 0){
				$discount_rate = $sales_report_profit_by_date;
			}else{
				if(!isset($tmp_item_trade_discount_code[$sid])){	// get trade discount code by date
					// get discount rate
					if($r['trade_discount_type'] == 1){
						$brand_vendor_id = $r['brand_id'];
						$brand_vendor_commission = 'brand';
					}else{
						$brand_vendor_id = $r['vendor_id'];
						$brand_vendor_commission = 'vendor';
					}
				
					// get trade discount code
					$tmp = get_sku_item_cost_selling($bid, $sid, $r['date'], array('trade_discount_code'));
					$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $tmp['trade_discount_code'];
					
					// if no discount code, get master 
					if(!$tmp_item_trade_discount_code[$sid]['trade_discount_code'])	$tmp_item_trade_discount_code[$sid]['trade_discount_code'] = $r['default_trade_discount_code'];
					
					// get discount rate at that time
					$tmp_discount_rate = get_consignment_discount_rate($bid, $r['date'], $tmp_item_trade_discount_code[$sid]['trade_discount_code'], $r['department_id'], $brand_vendor_commission, $brand_vendor_id);
					$tmp_item_trade_discount_code[$sid]['discount_rate'] = $tmp_discount_rate;
				}
				
				//$trade_discount_code = $tmp_item_trade_discount_code[$sid]['trade_discount_code'];
				$discount_rate = mf($tmp_item_trade_discount_code[$sid]['discount_rate']);
			}
			
			$amt = round($r['amount'], 2);
			$cost = $amt-($amt*$discount_rate/100);
			
			$key = $r['date'];
					
			$this->data['data'][$key]['amt'] += $amt;
			$this->data['data'][$key]['qty'] += $r['qty'];
			$this->data['data'][$key]['cost'] += $cost;
			
			$this->data['total']['amt'] += $amt;
			$this->data['total']['qty'] += $r['qty'];
			$this->data['total']['cost'] += $cost;
		}
		$con_multi->sql_freeresult($q1);
		
		$con_multi->close_connection();

		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Date: ".$date_from." to ".$date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function email_sales(){
		global $con, $smarty, $agrs;
		
		if(!is_using_terminal())	die("Invalid Access.");
		
		while($mode = array_shift($agrs)){
			switch($mode){
				case '-vendor_id':
					$tmp_vid = mi(array_shift($agrs));
					if(!$tmp_vid)	die("Invalid Vendor ID\n");
					break;
				case '-branch_id':
					$tmp_bid = mi(array_shift($agrs));
					if(!$tmp_bid)	die("Invalid Branch ID\n");
					break;
				case '-email_to':
					$tmp_email_contact = trim(array_shift($agrs));
					if(!$tmp_email_contact)	die("No Email Address\n");
					break;
			}
		}
	
		$_REQUEST['date_to'] = date("Y-m-d", strtotime("-1 day", time()));
		$_REQUEST['date_from'] = date("Y-m-d", strtotime("-6 day", strtotime($_REQUEST['date_to'])));
		$_REQUEST['load_report'] = 1;
		$smarty->assign('is_email', 1);
		$smarty->assign('no_header_footer', 1);
		
		$date_str = "From ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
		print "$date_str\n";
		
		$filename = "sales_summary_by_day.html";
		
		include_once("include/class.phpmailer.php");
		$mailer = new PHPMailer(true);
		$mailer->FromName = "ARMS";
		$mailer->From = "arms@segigroup.com";
		$mailer->Subject = "ARMS - Sales Summary by Day ($date_str)";
		$mailer->IsHTML(true);
		
		$filter = array();
		$filter[] = "v.active_vendor_portal=1";
		if($tmp_vid)	$filter[] = "vpi.vendor_id=$tmp_vid";
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select vpi.*, v.code, v.description
from vendor_portal_info vpi
join vendor v on v.id=vpi.vendor_id
$filter order by vpi.vendor_id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($vendor = $con->sql_fetchassoc($q1)){
			$vendor['sku_group_info'] = unserialize($vendor['sku_group_info']);
			$vendor['sales_report_profit'] = unserialize($vendor['sales_report_profit']);
			//print_r($vendor['sku_group_info']);
			
			if(!$vendor['sku_group_info'])	continue;	// no sku group info
			
			// get info by branch
			$vpbi = array();
			$q2 = $con->sql_query("select branch_id, contact_email,sales_report_profit_by_date,sales_bonus_by_step from vendor_portal_branch_info where vendor_id=".$vendor['vendor_id']." and active=1 and contact_email<>''");
			while($r = $con->sql_fetchassoc($q2)){
				if($r['contact_email']){
					$r['contact_email_list'] = explode(",", $r['contact_email']);
					if($r['contact_email_list']){
						foreach($r['contact_email_list'] as $key => $tmp_email){
							$r['contact_email_list'][$key] = trim($tmp_email);
						}
					}
				}
				
				$vpbi[$r['branch_id']] = $r;
			}
			$con->sql_freeresult();
			
			foreach($vendor['sku_group_info'] as $bid=> $sgi_info){
				if(!$vpbi[$bid] || !$vpbi[$bid]['contact_email_list'] || !is_array($vpbi[$bid]['contact_email_list']))	continue;
				//if($tmp_bid && $tmp_bid != $bid)	continue;	// got branch id filter
				
				list($sgi_bid, $sgi_id) = explode("|", $sgi_info);
				if(!$sgi_bid || !$sgi_id)	continue;
				
				print "Loading Vendor ID: $vendor[vendor_id] - $vendor[description], Branch ID: $bid,  sgi_bid: $sgi_bid, sgi_id: $sgi_id";
				
				$this->bid = $bid;
				
				$this->data = array();
				
				$params = array();
				$params['sku_group_bid'] = $sgi_bid;
				$params['sku_group_id'] = $sgi_id;
				$params['sales_report_profit_by_date_list'] = unserialize($vpbi[$bid]['sales_report_profit_by_date']);
				
				$this->load_report($params);
				
				if($this->data){	// got data to send
					file_put_contents($filename, $smarty->fetch('vp.sales_summary_by_day.tpl'));
				
					$mailer->ClearAddresses();
					$mailer->ClearAttachments();
					
					if($tmp_email_contact){
						$mailer->AddAddress($tmp_email_contact);
						if ($mailer->ValidateAddress($tmp_email_contact)) $proceed_send_email = true;
					}else{
						foreach($vpbi[$bid]['contact_email_list'] as $tmp_email){
							if ($tmp_email) {
								$mailer->AddAddress($tmp_email, $vendor['description']);
								if ($mailer->ValidateAddress($tmp_email)) $proceed_send_email = true;
							}
						}
					}
					$mailer->AddBCC("yinsee@wsatp.com");
					$mailer->AddAttachment($filename, $filename);
					$mailer->Body = "Kindly please refer to attachment.";
	
					// send the mail
					if ($proceed_send_email) {
						if(phpmailer_send($mailer, $mailer_info)){
							print " - Send Successfully\n";
						}else{
							//print " - Send Failed ($mailer->ErrorInfo)\n";
							print " - Send Failed ($mailer_info[err])\n";
						}
					}
				}else{	// no sales, no need send
					print " - No Data\n";
				}
			}
		}
		$con->sql_freeresult($q1);
	}
}

$SALES_SUMMARY_REPORT_BY_DAY = new SALES_SUMMARY_REPORT_BY_DAY('Sales Summary by Day');
?>
