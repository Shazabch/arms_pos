<?php
/*
10/30/2015 10:56 PM DingRen
- POS Print Full Tax Invoice record print count and add log when print

05/05/2016 11:00 Edwin
- Enhanced on auto fill in member info in tax invoice remark based on membership number.
- Show company register number after company name in tax invoice.
- Added new print option: Official Receipt for Inti.

05/12/2016 17:30 Edwin
- Added "Search Last Receipt" based on branch

07/19/2016 11:00 Edwin
- Bugs fixed on show member name if card number is empty.

10/26/2016 11:21 AM
- Change "Print full tax invoice" to "Print Full Tax Invoice"

11/30/2016 4:20 PM Andy
- Enhanced to always have default tax invoice remark value. (Name, Address, BRN, GST Reg No)

1/3/2017 17:00 Qiu Ying
- Bug fixed on print full tax invoice the invoice number does not match with the receipt number

1/25/2017 17:37 Kee Kee
- Fixed print full tax invoice with Deposit receive

2/16/2017 14:12 Qiu Ying
- Bug fixed on security deposit receive document title is wrong
- Bug fixed on GST Summary for Claim Deposit not show when the deposit contains items 

4/26/2017 9:51 AM Justin
- Enhanced to remove active filtering for counter list.

4/26/2017 10:59 AM Khausalya
- Enhanced changes from RM to use config setting. 

4/26/2017 3:11 PM Justin
- Bug fixed on currency symbol print as empty.

11/16/2017 9:38 AM Kee Kee
- Added GST Relief Clause remark

2/8/2018 3:11 PM Justin
- Enhanced to turn payment type "Debit" and "Others" to show out similar to receipt print from POS counter.

9/4/2018 5:09 PM Andy
- Enhanced "Print Full Tax Invoice" to able to print non-gst transaction.

9/28/2018 5:14 PM Andy
- Enhanced "Print Official Receipt" to able to print non-gst transaction.

10/4/2018 11:50 AM Andy
- Fixed item amount wrong if got item discount.

10/12/2018 5:32 PM Justin
- Bug fixed on wording "Currency_Adjust".
- Bug fixed on the Amount Change.
- Bug fixed on the Foreign Currency compatible issue.

6/19/2019 5:42 PM William
- Pick up "vertical_logo" and "vertical_logo_no_company_name" from Branch for logo and hide company name setting.
- Pick up "setting_value" from system_settings for logo setting.

10/12/2020 4:02 PM William
- Enhanced to check "is_tax_registered" or "is_gst", when got tax.
*/

include("include/common.php");
include("counter_collection.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_PRINT_FULL_TAX_INVOICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FRONTEND_PRINT_FULL_TAX_INVOICE', BRANCH_CODE), "/index.php");
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',0);
class POS_PRINT_FULL_TAX extends Module{
  var $title="Print Full Tax Invoice";
  var $tax_invoice_remark_list = array();
  
  function __construct(){
	global $config,$sessioninfo,$appCore,$smarty;
	//$this->tax_invoice_remark_list = $appCore->gstManager->getTaxInvoiceRemarkList();
	
	//$smarty->assign('tax_invoice_remark_list', $this->tax_invoice_remark_list);
	
    parent::__construct($this->title);
  }

  function _default(){
	global $smarty,$sessioninfo,$config;

    if(BRANCH_CODE == 'HQ'){
      $this->load_branches();
    }

    $this->display('pos.print_full_tax_invoice.tpl');
  }

  private function load_branches(){
    global $smarty, $con, $sessioninfo;

	$con->sql_query("select branch.* from branch where branch.active=1 order by branch.sequence");

	while($r = $con->sql_fetchrow()){
	  $branches[$r['id']] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("branches", $branches);
  }

  function load_receipt(){
    global $con, $sessioninfo;

	$result=array();
	
	if($_REQUEST['type'] == 'search') {
	  $con->sql_query("select * from pos where receipt_ref_no=".ms($_REQUEST['ref_no']));
	}elseif($_REQUEST['type'] == 'last_receipt') {
	  $con->sql_query("select * from pos where branch_id=".mi($_REQUEST['branch_id'])." and cancel_status = 0 order by date desc, id desc limit 1" );
	}
	$r = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	if($r){
	  if($_REQUEST['type'] == 'search' && BRANCH_CODE != 'HQ' && ($r['branch_id'] != $sessioninfo['branch_id'])){
		$result['msg']="This receipt is not belong to this branch.";
	  }else {
		$result = array('branch_id'=>$r['branch_id'],'counter_id'=>$r['counter_id'],'date'=>$r['date'],'receipt_no'=>$r['receipt_no'], 'card_no'=>$r['member_no']);  
	  }
	}else {
      $result['msg']="Receipt not found.";
    }
	echo json_encode($result);
  }

  function load_counter(){
    global $con, $sessioninfo;

    $branch_id=mi($_REQUEST['branch_id']);

	// removed active=1 since we still need to let user to print full tax invoice from inactive counters
    $con->sql_query("select * from counter_settings where branch_id=".$branch_id);

	while($r = $con->sql_fetchrow()){
	  $branches[$r['id']] = $r;
	}
	$con->sql_freeresult();
    echo json_encode($branches);
  }

  function save_remark(){
	global $smarty, $con, $sessioninfo, $config;

	$result=array();
	$form=$_REQUEST;
	$pos=$this->get_pos($form);

	if($pos){
	  if(BRANCH_CODE != 'HQ') $form['branch_id'] = $sessioninfo['branch_id'];

	  $where=array();
	  if($form['ref_no']!="") $where[] = "receipt_ref_no=".ms($form['ref_no']);
	  $where[] = "branch_id=".mi($form['branch_id']);
	  $where[] = "counter_id=".mi($form['counter_id']);
	  $where[] = "date=".ms($form['date']);
	  $where[] = "receipt_no=".ms($form['receipt_no']);

	  $where=" where ".join(" and ",$where);

	  //$upd['is_print_full_tax_invoice'] = 1;
      $upd['print_full_tax_invoice_remark'] = serialize($form['remark']);

	  $con->sql_query("update pos set ".mysql_update_by_field($upd).$where);

	  unset($form['remark'],$form['a']);
	  $result['msg']="OK";
	  $result['url']=http_build_query($form);
	}
	else{
	  $result['msg']="Receipt not found.";
	}
	echo json_encode($result);
  }

  function check(){
	global $smarty, $con, $sessioninfo, $config, $appCore;

	$result=array();
	$form=$_REQUEST;
	$pos=$this->get_pos($form);
	$data = array();
	
	if ($pos){
	  if($pos['cancel_status']){
		$result['msg']="Receipt cancelled.";
	  }
	  /*elseif(!$pos['is_gst']){
		$result['msg']="This is not a GST receipt.";
	  }*/
	  else{
		$result['msg']="OK";
		$data['is_gst'] = $pos['is_gst'];
		$data['is_tax_registered'] = $pos['is_tax_registered'];
		if($data['is_gst']){
			$data['got_tax'] = $data['is_gst'];
		}elseif($data['is_tax_registered']){
			$data['got_tax'] = $data['is_tax_registered'];
		}
		
		// Default Remark list
		$data['remark_list'] = array('Name'=>'', 'Address'=>'', 'BRN'=>'');	
		
		if($data['is_gst'] || $data['is_tax_registered']){	// GST
			// Load GST Remark List
			$this->tax_invoice_remark_list = $appCore->gstManager->getTaxInvoiceRemarkList();
			
			// Append into Remark
			foreach($this->tax_invoice_remark_list as $remark_key){
				if($remark_key == 'GST Reg No') $remark_key = 'Tax Reg No';
				if(!isset($data['remark_list'][$remark_key])){
					$data['remark_list'][$remark_key] = '';
				}
			}
		}
		
		// This transaction already got remark key in
		if($pos['print_full_tax_invoice_remark']){
			$print_full_tax_invoice_remark = @unserialize($pos['print_full_tax_invoice_remark']);
			foreach($print_full_tax_invoice_remark as $remark_key => $v){
				$data['remark_list'][$remark_key] = $v;
			}
		}else{
			// got member card
			if($config['full_tax_invoice_use_member_info'] && $pos['member_no']){
				// Auto get membership info
				$con->sql_query("select * from membership where card_no=".ms($pos['member_no']));
				$member = $con->sql_fetchrow();
				$con->sql_freeresult();
				
				if(!$member){
					$con->sql_query("select nric from membership_history where card_no=".ms($pos['member_no'])." limit 1");
					$tmp = $con->sql_fetchrow();
					$con->sql_freeresult();
					
					if($tmp['nric']){
						$con->sql_query("select * from membership where nric=".ms($tmp['nric']));
						$member = $con->sql_fetchrow();
						$con->sql_freeresult();
					}
				}
				
				if($member){
					$data['remark_list']['Name'] = $member['name'];
					$data['remark_list']['Address'] = $member['address'];
					$data['remark_list']['BRN'] = $member['card_no'];
				}
				
			}
		}
		
		$smarty->assign('data', $data);
		$result['html'] = $smarty->fetch("pos.print_full_tax_invoice.remark.tpl");
	  }
	}
	else{
	  $result['msg']="Receipt not found.";
	}
	echo json_encode($result);
  }

  function show(){
    global $smarty, $con, $sessioninfo, $config;

    $form=$_REQUEST;
    $pos=$this->get_pos($form);

		if($pos)
		{
			$pos['pos_more_info']=unserialize($pos['pos_more_info']);

			$service_charges_total=0;
			if(isset($pos['pos_more_info']['service_charges'])){
				$service_charges_total=$pos['service_charges'];
				$service_charges_info=array();
				$service_charges_info['item']=$pos['pos_more_info']['service_charges']['item_list'];
				$service_charges_info['rate']=$pos['pos_more_info']['service_charges']['service_charges_rate'];
				$service_charges_info['sc_gst_detail']=$pos['pos_more_info']['service_charges']['sc_gst_detail'];
				$service_charges_info['amount']=$pos['service_charges']-$pos['service_charges_gst_amt'];
				$service_charges_info['gst_amount']=$pos['service_charges_gst_amt'];
				$service_charges_info['total']=$pos['service_charges'];
			}

			if(isset($pos['is_special_exemption']) && $pos['is_special_exemption']){
				$special_exemption=unserialize($pos['special_exempt_remark']);
			}

			$con->sql_query("select * from pos_settings where branch_id=".mi($form['branch_id']));
			while($r = $con->sql_fetchrow()){
				$pos_settings[$r['setting_name']] = $r['setting_value'];
			}

			$con->sql_query("select * from branch where id=".mi($form['branch_id']));
			$branch=$con->sql_fetchassoc();

			//get admin logo system_settings and branch logo setting 
			$system_settings = array();
			$setting_list = array('logo_vertical', 'verticle_logo_no_company_name');
			foreach($setting_list as $setting_name){
				$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
				$r = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$system_settings[$setting_name] = $r['setting_value'];
			}
			$qry1 = $con->sql_query("select is_vertical_logo,vertical_logo_no_company_name from branch where id=".mi($form['branch_id']));
			$r1 = $con->sql_fetchassoc($qry1);
			$con->sql_freeresult($qry1);
			if($r1['is_vertical_logo'] == 1){
				$system_settings['verticle_logo_no_company_name'] = $r1['vertical_logo_no_company_name'];
				$system_settings['logo_vertical'] = $r1['is_vertical_logo'];
			}
			$smarty->assign("system_settings",$system_settings);
			

			if($system_settings['logo_vertical'] == 1){
				$receipt_header=array();
				if($system_settings['verticle_logo_no_company_name'] != 1){
					$receipt_header[]=$branch['description']." (".$branch['company_no'].")";
					$branch_address[]=$branch['address'];
					$receipt_header=array_merge($receipt_header,$branch_address);
					if($pos['is_gst'])	$receipt_header[]="Tax Reg No.: ".$branch['gst_register_no'];
				}else{
					$receipt_header=$branch['address'];
					if($pos['is_gst'])	$receipt_header[]="Tax Reg No.: ".$branch['gst_register_no'];
				}
			}else{
				$receipt_header=array();
				$receipt_header[]=$branch['description']." (".$branch['company_no'].")";
				$receipt_header=array_merge ($receipt_header,explode("\n",$branch['address']));
				if($pos['is_gst'])	$receipt_header[]="Tax Reg No.: ".$branch['gst_register_no'];
			}

			$default_currency=($pos_settings['currency_symbol']!="")?$pos_settings['currency_symbol']:$config["arms_currency"]["symbol"];

			$where=array();
			$where[] = "branch_id=".mi($form['branch_id']);
			$where[] = "counter_id=".mi($form['counter_id']);
			$where[] = "date=".ms($form['date']);
			$where[] = "pos_id=".mi($pos['id']);
		  
			if(isset($pos['deposit']) && $pos['deposit'])
			{
				$where=" where ".join(" and ",$where);			
				$con->sql_query("select * from pos_deposit".$where);
				$deposit = $con->sql_fetchrow();
				$con->sql_freeresult();
				if(isset($deposit['item_list']) && $deposit['item_list'])
				{
					$inserted_item = unserialize($deposit['item_list']);
					$items = $this->print_tax_invoice_item($inserted_item,1);
				}
				else{
					$inserted_item = array();
				}
				$smarty->assign("is_security_deposit_type",$pos['pos_more_info']['is_security_deposit_type']);
			}
			else{
				$where=" where ".join(" and ",$where);
				$con->sql_query("select * from pos_items".$where);
				$inserted_item = $con->sql_fetchrowset();
				$con->sql_freeresult();
				$items = $this->print_tax_invoice_item($inserted_item);
			}

			$q1 = $con->sql_query("select * from pos_payment".$where);
			
			while($r = $con->sql_fetchassoc($q1)){
				if($r['type'] == "Currency_Adjust") $r['type'] = "Currency Adjust";
				$payment[] = $r;
			}
			$con->sql_freeresult($q1);

			$payment = $this->print_tax_invoice_trans_summary($pos,$items,$payment,$pos_settings);

			$con->sql_query("select * from pos_credit_note".$where);
			$credit_notes=array();
			while($r = $con->sql_fetchrow())
			{
				$r['company_name'] = $branch['description'];
				$r['gst_register_number'] = $branch['gst_register_no'];
				$r['customer_info'] = unserialize($r['customer_infor']);
				$r['item_infor'] = unserialize($r['item_infor']);
				$credit_notes[$r['return_date']][$r['return_receipt_no']] = $r;
			}

			$gst_arr=array();
			if(isset($pos['deposit']) && $pos['deposit'])
			{
				$gstInfo = unserialize($deposit['gst_info']);
				$gst=sprintf("%s @%.01f%%",$gstInfo['indicator_receipt'],$gstInfo['rate']);
				if(!isset($gst_arr[$gst])) $gst_arr[$gst]=array('before_tax_price'=>0,'total_gst'=>0);
				$gst_arr[$gst]['before_tax_price'] += (round($deposit['deposit_amount'],2)-round($deposit['gst_amount'],2));
				$gst_arr[$gst]['total_gst']+=$deposit['gst_amount'];
			}
			else{
				foreach($inserted_item as $key=>$val) 
				{
					if($val['quantity']<0){
						continue;
					}

					$gst=sprintf("%s @%.01f%%",$val['tax_indicator'],$val['tax_rate']);
					if(!isset($gst_arr[$gst])) $gst_arr[$gst]=array('before_tax_price'=>0,'total_gst'=>0);
					$gst_arr[$gst]['before_tax_price']+=$val['before_tax_price'];
					$gst_arr[$gst]['total_gst']+=$val['tax_amount'];
				}
				
				if(isset($pos['pos_more_info']['deposit']) && $pos['pos_more_info']['deposit']){
					foreach($pos['pos_more_info']['deposit'] as $key => $item){
						if(!$item['pos_more_info']['is_security_deposit_type']){
							$gstInfo = unserialize($item['gst_info']);
							$gst=sprintf("%s @%.01f%%",$gstInfo['indicator_receipt'],$gstInfo['rate']);
							$gst.=" ( Deposit )";
							if(!isset($gst_arr[$gst])) $gst_arr[$gst]=array('before_tax_price'=>0,'total_gst'=>0);
							$gst_arr[$gst]['before_tax_price'] -= (round($item['amount'],2)-round($item['gst_amount'],2));
							$gst_arr[$gst]['total_gst']-=$item['gst_amount'];
						}
					}
				}

				if($service_charges_total>0) {
					if($pos['is_special_exemption']){
						$gst=sprintf("%s @%.01f%%",$special_exemption['tax_detail']['indicator_receipt'],$special_exemption['tax_detail']['rate']);
					}
					else
						$gst=sprintf("%s @%.01f%%",$service_charges_info['sc_gst_detail']['indicator_receipt'],$service_charges_info['sc_gst_detail']['rate']);
					if(!isset($gst_arr[$gst])) $gst_arr[$gst]=array('before_tax_price'=>0,'total_gst'=>0);
					$gst_arr[$gst]['before_tax_price']+=$service_charges_total-round($service_charges_info['gst_amount'],2);
					$gst_arr[$gst]['total_gst']+=$service_charges_info['gst_amount'];
				}

			}
			
			if(trim($pos['receipt_remark']) !== "") {
				$receipt_remark = unserialize($pos['receipt_remark']);
			}

			$where=array();
			$where[] = "branch_id=".mi($form['branch_id']);
			$where[] = "counter_id=".mi($form['counter_id']);
			$where[] = "date=".ms($form['date']);
			$where[] = "id=".mi($pos['id']);

			$where=" where ".join(" and ",$where);
			$upd=array();
			$upd['is_print_full_tax_invoice'] = $pos['is_print_full_tax_invoice']+1;
			$con->sql_query("update pos set ".mysql_update_by_field($upd).$where);

			log_br($sessioninfo['id'], 'POS', $pos['id'], "Print Full Tax Invoice receipt_ref_no#".$pos['receipt_ref_no']." Print count: ".($pos['is_print_full_tax_invoice']+1));

			if(trim($pos['print_full_tax_invoice_remark']) !== "") $smarty->assign("customer_info", unserialize($pos['print_full_tax_invoice_remark']));
			if($credit_notes) $smarty->assign("credit_note_info",$credit_notes);
			if($receipt_remark) $smarty->assign("receipt_remark",$receipt_remark);
			if($pos['is_special_exemption']>0) $smarty->assign("special_exemption",$special_exemption);
			if(isset($pos['pos_more_info']['se_clause_remark']) && trim($pos['pos_more_info']['se_clause_remark'])!="") $smarty->assign("se_clause_remark",$pos['pos_more_info']['se_clause_remark']);
			if($service_charges_total>0) $smarty->assign("service_charges_info",$service_charges_info);
			$smarty->assign("member_no",$pos['member_no']);
			$smarty->assign("is_deposit",$pos['deposit']);
			$smarty->assign("gst_arr",$gst_arr);
			$smarty->assign("default_currency",$default_currency);
			$smarty->assign("receipt_header",$receipt_header);
			$smarty->assign("invoice_no",$pos['receipt_no']);
			$smarty->assign("receipt_ref_no",$pos['receipt_ref_no']);
			$smarty->assign("date",date("d M Y",strtotime($pos['start_time'])));
			if($pos['is_print_full_tax_invoice']) $smarty->assign("is_duplicate_copy","Duplicate Copy");
			$smarty->assign("items",$items);
			$smarty->assign("payment",$payment);
			$smarty->assign("pos",$pos);
			
			if ($form['type'] == "print_official_receipt" && $config['full_tax_invoice_print_official_receipt']){
				// Official Receipt
				$this->display($config['full_tax_invoice_print_official_receipt']);
			}else{
				if($pos['is_gst'] || $pos['is_tax_registered']){
					// GST Tax Invoice
					$this->display('pos.print_full_tax_invoice.print.tpl');
				}else{
					// Normal Invoice
					$tpl = 'pos.print_full_tax_invoice.print_invoice.tpl';
					if($config['full_tax_invoice_alt_print_template'])	$tpl = $config['full_tax_invoice_alt_print_template'];
					
					$this->display($tpl);
				}
			}
		}
		else{
			echo "Receipt not found.";
		}
  }

  private function get_pos($form){
	global $smarty, $con, $sessioninfo, $config;

	if(BRANCH_CODE != 'HQ') $form['branch_id'] = $sessioninfo['branch_id'];

	$where=array();
    if($form['ref_no']!="") $where[] = "receipt_ref_no=".ms($form['ref_no']);
    $where[] = "branch_id=".mi($form['branch_id']);
    $where[] = "counter_id=".mi($form['counter_id']);
    $where[] = "date=".ms($form['date']);
    $where[] = "receipt_no=".ms($form['receipt_no']);

	$where=" where ".join(" and ",$where);

    $con->sql_query("select * from pos".$where);
    $pos = $con->sql_fetchassoc();

	return $pos;
  }

	private function print_tax_invoice_item($inserted_item,$is_deposit = false)
	{
		$items = array();
		$i=0;
		
		if($is_deposit)
		{
			foreach($inserted_item as $item) 
			{
				$qty = $item['quantity'];
				$temp_item = array();
				$price = (isset($item['fixed_price'])?$item['fixed_price']:$item['item']['selling_price']);
				if($price=="*")
				{
					$temp_item['remark'] = "*";
					$total_price += ($item['item']['selling_price']*$item['quantity']);	
					$item['price'] = ($item['item']['selling_price']*$item['quantity']);			
				}
				else
				{					
					$total_price += ($price*$item['quantity']);
					$item['price'] = ($price*$item['quantity']);
				}
				
				$temp_item['no'] = $i+1;
				$temp_item['barcode'] = $item['barcode'];
				$temp_item['receipt_description'] = $item['item']['receipt_description'];
				$temp_item['qty'] = $qty;
				
				$temp_item['unit_price'] = $item['price']/$qty;
				$temp_item['sub_total'] = $temp_item['amount'] = $total_price;
				$items[] = $temp_item;
				$i++;
			}
		}
		else{
			foreach($inserted_item as $item) {
				  $temp_item = array();

				  if(isset($item['remark'])){
					  $temp_item['receipt_description'].="<br/ ><small>".$item['remark']."</small>";
				  }

				  $qty = $item['qty'];
				  $sub_total=$item['price'];

				  $temp_item['no'] = $i+1;
				  $temp_item['barcode'] = $item['barcode'];
				  $temp_item['receipt_description'] = $item['sku_description'];
				  $temp_item['indicator'] = $item['tax_indicator'];
				  $temp_item['qty'] = $qty;
				  $temp_item['unit_price'] = $item['price']/$qty;
				  $temp_item['sub_total'] = $sub_total;
				  $temp_item['disc'] = $item['discount'];

				  $sub_total-=$temp_item['disc'];

				  $item['price']=($item['price']-$item['discount']);

				  if($temp_item['inclusive_tax']){
					//$item['before_tax_price']=$item['price']/(($item['tax_rate']+100)/100);
					//$item['tax_amount']=$item['price']-$item['before_tax_price'];
				  }
				  else{
					//$item['before_tax_price']=$item['price']/(($item['tax_rate']+100)/100);
					//$item['tax_amount']=$item['price']-$item['before_tax_price'];
				  }

				  $temp_item['total_ecl_gst'] = $item['before_tax_price'];
				  $temp_item['total_gst'] = $item['tax_amount'];
				  //$temp_item['amount'] = $item['before_tax_price']+$item['tax_amount'];
				  $temp_item['amount'] = $item['price'];

				  $items[] = $temp_item;
				  $i++;
			  }
      
		}
		return $items;
	}

  function print_tax_invoice_trans_summary($pos,$items,$payment,$pos_settings)
  {
		global $config;

		$issuer_identifier = array(array("Diners", 300000, 305999, 14), array("Diners", 360000, 369999, 14), array("Diners", 380000, 389999, 14), array("AMEX", 340000, 349999, 15), array("AMEX", 370000, 379999, 15), array("VISA", 400000, 499999, 13), array("VISA", 400000, 499999, 16), array("Master", 510000, 559999, 16), array("Discover", 601100, 601199, 16), array("Debit"));

		foreach($issuer_identifier as $idx=>$c) {
			$credit_cards[$c[0]] = 1;
		}

		$pay_list=array();
		$round_adjustment=0;
		$got_discount=0;
		$total_amount=$pos['amount'];
		$round_amount=$total_amount;
		$default_currency=($pos_settings['currency_symbol']!="")?$pos_settings['currency_symbol']:$config["arms_currency"]["symbol"];
		$pos_settings['currency']=unserialize($pos_settings['currency']);

		if($pos['deposit'])
		{
			if($items)
			{
				$total_amount = 0;
				foreach($items as $item)
				{
					$total_amount += $item['amount'];
				}
				$pay_list[] = array(sprintf("%s (%s)",'Total',$default_currency), number_format($total_amount,2));
			}
			else{
				//$pay_list[] = array(sprintf("%s (%s)",'Total',$default_currency), number_format($pos['amount'],2));
			}
			foreach($payment as $p)
			{
				if($p['type']== "Discount" || $p['type']=="Mix & Match Total Disc") { continue; }
				//====================For type and remark============
				if ((!isset($p['remark'])) || ($p['remark'] == ""))
				{
					$str_left1 = "";
					$str_left2 = $p['type'].":";
				}
				else
				{
					//$str_left1 = $p['type'];
					//special for coupon module to show percentage
					if ($p['type'] == "Coupon" && substr($p['remark'],-5,1) == "O"){
						$coup_per=mf(substr($p['remark'],-4,4)/100);
						$str_left2 =  $p['type']." ".$p['remark']." ($coup_per%)".":";
					}
					elseif(in_array($p['type'],array_keys($credit_cards)))
					{
						$change = substr($p['remark'],0,-4);
						$p['remark'] = str_replace($change,"xxxxxxxxxxxx",$p['remark']);
						$str_left2 =  $p['type']." ".$p['remark'].":";
					}
					else
						$str_left2 =  $p['type']." ".$p['remark'].":";
				}

				/********************** cash and other currency payment ****************************************/
				if ($p['type'] == "Cash" || (isset($pos_settings['currency']) && in_array($p['type'],array_keys($pos_settings['currency']))))
				{
					if(isset($p['pos_id']) && isset($pos_settings['currency']))
					{
						if (array_key_exists($p['type'],$pos_settings['currency']))
						$p['amount'] /= $pos_settings['currency'][$p['type']];
					}

					//$str_left2 .= $this->default_currency;

					$cash += $p['amount'];
				}

				if($str_left2 != "Rounding:")
				{
					if($items)
						$pay_list[] = array(sprintf("%s(%s)",$str_left1.$str_left2,$default_currency), number_format($p['amount'],2));
					else
						$pay_list[] = array(sprintf("%s %s ",$str_left1.$str_left2,$default_currency), number_format($p['amount'],2));
					$total_pay += $p['amount'];
				}
			}
		}
		else
		{
			$service_charges_total=0;
			if(isset($pos['pos_more_info']['service_charges']))
			{
				$service_charges_total=$pos['service_charges'];
				$service_charges_info=array();
				$service_charges_info['item']=$pos['pos_more_info']['service_charges']['item_list'];
				$service_charges_info['rate']=$pos['pos_more_info']['service_charges']['service_charges_rate'];
				if(isset($pos['pos_more_info']['service_charges']['sc_gst_detail'])){
					$service_charges_info['sc_gst_detail']=$pos['pos_more_info']['service_charges']['sc_gst_detail'];
					$service_charges_info['gst_amount']=$pos['service_charges_gst_amt'];
				}
			}

			foreach($payment as $pay_info)
			{
				if ($pay_info['type'] == 'Discount' || $pay_info['type'] == 'Mix & Match Total Disc'){
					$got_discount++;
				}
				if ($pay_info['type'] == 'Rounding'){
					$total_amount-=$pay_info['amount'];
					$round_adjustment=$pay_info['amount'];
				}
			}

			if($service_charges_total>0) 
			{
				$total_amount+=$service_charges_total;
				$round_amount+=$service_charges_total;
			}

			if($pos['is_gst'] || $pos['is_tax_registered'])	$str_incl_gst = ' Incl. Tax';
			
			if (($round_adjustment == 0 && $got_discount == 0) && $service_charges_total==0){
				$pay_list[] = array(sprintf("%s (%s)",'Total'.$str_incl_gst,$default_currency), number_format($total_amount,2));
			}
			else {
				$pay_list[] = array(sprintf("%s (%s)",'Subtotal'.$str_incl_gst, $default_currency),number_format($total_amount,2));
			}
			
			

			foreach($payment as $p) 
			{
				if($p['type']== "Discount" || $p['type']=="Mix & Match Total Disc") 
				{
					$disc_amount = number_format($p['amount'],2);
					if($p['remark'] == ""){
					  $remark = ($default_currency." ".number_format($p['amount'],2));
					}
					else {
					  $remark = $p['remark'];
					}

					if(isset($p['more_info']) && $p['more_info']!=""){
					  $p['more_info']=unserialize($p['more_info']);
					  $remark .= " - ". $p['more_info']['remark'];
					}

					$round_amount = $round_amount - $disc_amount;
					$total_rdisc+=$disc_amount;
					$pay_list[] = array('Disc: '.$remark, number_format(($p['amount']*-1),2));
				}
			}

			if($round_adjustment) $pay_list[] = array(sprintf("%s (%s)",'Rounding',$default_currency), number_format($round_adjustment,2));

			if($got_discount > 0 || $round_adjustment != 0 || $service_charges_total>0){
				$pay_list[] = array(sprintf("%s (%s)",'Total'.$str_incl_gst,$default_currency), number_format($round_amount,2));
			}

			$cash = 0;
			$total_pay = 0;

			foreach($payment as $p)
			{
				if($p['type']== "Discount" || $p['type']=="Mix & Match Total Disc") { continue; }
				//====================For type and remark============
				if ((!isset($p['remark'])) || ($p['remark'] == ""))
				{
					$str_left1 = "";
					$str_left2 = $p['type'].":";
				}
				else
				{
					//$str_left1 = $p['type'];
					//special for coupon module to show percentage
					if ($p['type'] == "Coupon" && substr($p['remark'],-5,1) == "O"){
						$coup_per=mf(substr($p['remark'],-4,4)/100);
						$str_left2 =  $p['type']." ".$p['remark']." ($coup_per%):";
					}
					elseif(in_array($p['type'],array_keys($credit_cards)) || strtolower($p['type'])=="others")
					{
						$change = substr($p['remark'],0,-4);
						$p['remark'] = str_replace($change,"xxxxxxxxxxxx",$p['remark']);
						$str_left2 =  $p['type']." ".$p['remark']." :";
					}
					else
						$str_left2 =  $p['type']." ".$p['remark']." :";
				}

				/********************** cash and other currency payment ****************************************/
				if ($p['type'] == "Cash" || isset($config['foreign_currency'][$p['type']]))
				{
					// check is foreign currency
					$currency_arr = pp_is_currency($p['remark'], $p['amount']);

					$p['amount'] = round($currency_arr['rm_amt'], 2);

					//$cash += $p['amount'];
				}

				if($str_left2 != "Rounding:")
				{
					$pay_list[] = array(sprintf("%s (%s)",$str_left1.$str_left2,$default_currency), number_format($p['amount'],2));
					$total_pay += $p['amount'];
				}
			}

			//Start print change & saving
			/*$change = $total_pay - $round_amount;

			if ($cash < $change) $change = $cash;

			if($change<=0) $change = 0;

			$change = round($change,2);*/

			$pay_list[] = array(sprintf("%s (%s)",'Change',$default_currency), number_format($pos['amount_change'],2));

			//$total_discount = $total_discount-$round_adjustment;

			foreach($items as $key=>$val) {
				if(isset($val['disc']) && $val['disc']) {
					$total_discount += $val['disc'];
				}
			}

			if(($total_discount+$total_rdisc)>0) {
				$pay_list[] = array(sprintf("%s (%s)",'Savings',$default_currency), number_format(($total_discount+$total_rdisc),2));
			}
		}
		return $pay_list;
	}
}

$app=new POS_PRINT_FULL_TAX();

?>
