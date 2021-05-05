<?php
/*
2016-03-07 11:36am Kee Kee
- update column acc_is_exported with 1 when document/pos has export to account
- Remove to import consignment modules debit note
- Change pos credit note no to credit_note_ref_no (to avoid credit note no duplicate)

2016-06-23 12:35 PM Kee Kee
- Fixed Get Cash Sales DO Data cannot filter by branch

2016-06-27 3:33 PM Kee Kee
- Fixed export credit note error, skip insert -ve qty into cash sales tables if credit note exists

2016-06-28 2:36PM Kee Kee
- Fixed GAF Cash Sales wrong calculation

2016-07-15 5:35PM Kee Kee
- Fixed GAF Credit Sales record show wrong tax code

2016-07-15 5:35PM Kee Kee
- Fixed Cash Sales (Do Cash Sales) record assign wrong tax code

2016-09-21 3:43 PM Kee Kee
- Export purchase must export invoice document and date must use doc_date instead use rvc_date

2016-09-22 2:02 PM Kee Kee
- Set Purchase account code/name with use Account & GAF Export instead set with null value when vendor's Purchase account code/name is empty
- Set Purchase Debit Note account code with Purchase Return instead use Purchase account code/name (unless cannot found Purchase Return)

2016-10-05 3:58 PM Kee Kee
- Fixed able to export to accounting without account verification in GRN stage issue

2016-10-10 9:15 AM Kee Kee
- Remove export membership_redemption data as cash sales

2016-10-11 11:10 AM Kee Kee
- Fixed cannot get pos sales data issue (should use date instead use pos time)

2016-10-12 17:50 PM Kee Kee
- Fixed cannot get current grr invoice issue

2016-10-20 14:40 PM Kee Kee
- Fixed Grr Amount Wrong Issue

2016-10-21 11:58 PM Kee Kee
- Fixed cannot get company information

2016-10-24 15:24 PM Kee Kee
- Export Cash Sales and Credit Notes (POS Counter) together

2016-10-25 15:36 PM Kee Kee
- Add customer_brn, customer_gst_no into export credit sales & transfer DO

2016-11-02 17:35 PM Kee Kee
- Export Cash Sales DO and Credit Notes

2016-11-07 13:30 PM Kee Kee
- Export Sales DO and Credit Notes

2016-11-11 10:58 AM Kee Kee
- Fixed failed get DO Sales Open Items amount

2016-11-11 11:30 AM Kee Kee
- Fixed 'Purchase' account code does not transfer in account export where the Account & GAF Export Setting was set the account code but the vendor master file is leave blank.

2016-11-16 12:50PM Kee Kee
- Fixed Account export for deposit claims not balance

2016-12-06 15:11 PM Kee Kee
- Fixed Export Cash Sales and system crash issue

2016-12-19 17:00 PM Kee Kee 
- Fixed Credit Note double entry

2016-12-21 15:37 PM Kee Kee 
- Fixed 1 credit note with 2 different Invoice No and 1 of return Invoice information missing

2016-12-23 16:52 PM Kee Kee
- Added Purchase Retun and Sales Return into Export Accounting Settings

2017-01-04 13:49 PM Kee Kee
- Added branchCode into Export Account data function (for example: get_cash_sales)
- Set "job_code" as branch code when "JOB as Branch Code" setting is enable

2017-01-09 Kee Kee
- Fixed Problem with the double entry for goods return export to Million Accounting

2017-01-18 15:19 PM Kee Kee
- Fixed Credit Note No no show in GAF file 

2017-01-20 15:12 Qiu Ying
- Bug fixed on always show positive for negative value

2017-01-25 15:15 PM Kee Kee
- Fixed use Purchase Account code which use own branch instead of HQ 

2017-02-17 10:00 AM Qiu Ying
- Bug fixed on full tax invoice printed but the GAF does not capture the detail 

2017-02-27 13:28 PM Kee Kee
- Bug fixed on no filter date in get_credit_note()
- Fixed filter date issue in get_credit_note()

2017-03-01 17:53 Qiu Ying
- Bug fixed on goods exchange not fulfilled to MFRS and GST requirement

2017-03-07 14:30 Qiu Ying
- Bug fixed on assign wrong sales credit notes account code & account name in Sales Credit Note

2017-03-08 11:18 Qiu Ying
- Bug fixed on the position and the document type for cash received in tax invoice is wrong

5/8/2017 8:31 AM Khausalya
- Enhanced changes from MYR to use config setting. 

5/25/2017 16:31 Qiu Ying
- Enhanced to export credit note with multiple invoice

6/20/2017 11:14 AM Qiu Ying
- Bug fixed on amount showing wrong when export credit note

11/9/2017 4:56 PM Andy
- Added "Second Tax Code" for AP.
- Fixed get_pos_payment to skip adjustment payment.

1/25/2018 11:29 AM Andy
- Fixed debtor description new line error.

9/14/2018 10:42 AM Andy
- Enhanced to show Tax Code "NR" as empty if company is not gst registered.

11/2/2018 5:33 PM Andy
- Fixed Consignment Invoice items amount calculation error.

11/13/2018 5:18 PM Andy
- Fixed Consignment Credit Note amount calculation error.
*/

abstract class ExportModule
{
	var $sys=null;
	var $db;
	var $tmpTable = null;
	var $tmpFile = null;
	var $tmpPaymentFile2 = null;
	var $tmpExportFileName = null;
	var $tmpExportFileNamePT = null;
	var $folder = null;
	var $accSettings = array();
	var $dataFrom = null;
	var $dataTo = null;
	var $debug=0;
	var $debug_file=0;
	var $batchno="";

	function __construct($sys='lite'){
		$this->sys=$sys;
	}

	abstract function preset_account_column($dataType);
	//abstract function export_account_data();
	abstract function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode);
	abstract function get_account_payable($tmpSalesDb,$groupBy,$dateTo);
	abstract function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode);
	abstract function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode);
	abstract function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode);
	abstract function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode);
	abstract static function get_name();
	abstract static function get_property();

	function clear_db($tmpSalesDb){
		if($this->tmpTable) $this->sql_query($tmpSalesDb,"drop table if exists ".$this->tmpTable);
	}

	function create_cash_sales($tmpSalesDb){
		$this->sql_query($tmpSalesDb,"drop table if exists ".$this->tmpTable);

		$this->sql_query($tmpSalesDb,"create table if not exists ".$this->tmpTable."(
						`tablename` varchar(20),
						`batchno` varchar(20),
						`branch_id` integer default 0,
						`counter_id` integer default 0,
						`id` integer,
						`pos_date` date,
						`doc_no` char(20),
						`ref_no` char(30),
						`ym` date,
						`type` char(20),
						`acc_type` char(20),
						`description` char(150),
						`arms_code` char(50),
						`qty` double default 0,
						`unit_price` double default 0,
						`ItemAmount` double default 0,
						`TaxAmount` double default 0,
						`TotalAmount` double default 0,
						`tax_code` char(10),
						`taxRate` double default 0,
						`customer_code` char(50),
						`customer_name` char(50),
						`account_code` char(50),
						`account_name` char(50),
						`tax_account_code` char(50),
						`tax_account_name` char(50),
						`cancelled` char(1) default 'F',
						`transferable` char(1) default 'T',
						`customer_remark` text,
						`credit_note_no` char(50),
						`credit_note_ref_no` char(50),
						`has_credit_notes` char(1) default 0,
						primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
	}

	function update_cash_sales($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config, $appCore;

		$credit_card = $this->credit_cards_type();
	
		$ret=$this->get_pos($pos_db, $where);
		if($pos_db->sql_numrows($ret) > 0){
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
			while($rPos = $this->sql_fetchrow($pos_db, $ret)){				
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);

				$rPos['pos_date'] = strtotime($rPos['date']);
				$receipt_no = $rPos['receipt_no'];

				if(trim($rPos['print_full_tax_invoice_remark'])!="" && trim($rPos['print_full_tax_invoice_remark'])!="N;"){
					$customer_remark = $rPos['print_full_tax_invoice_remark'];
				}
				elseif((trim($rPos['print_full_tax_invoice_remark'])=="" || trim($rPos['print_full_tax_invoice_remark'])=="N;") && trim($rPos['special_exempt_remark'])!=""){
					$customer_remark = $rPos['special_exempt_remark'];
				}
				else{
					$customer_remark = "";
				}
				if(isset($rPos['pos_more_info'])) $rPos['pos_more_info']=unserialize($rPos['pos_more_info']);
	
				$receipt_ref_no = $rPos['receipt_ref_no'];
				$posDate = date("Y-m-d",$rPos['pos_date']);
				$posYM = date("Y-m-01",$rPos['pos_date']);
	
				$ret1 = $this->get_pos_payment($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1)>0){
					while($rPayment = $this->sql_fetchrow($pos_db, $ret1)){
						if(in_array($rPayment['type'],array_keys($credit_card))){
							$description = 'credit_card';
						}
						else{
							$description = strtolower($rPayment['type']);
						}
		
						if($description=='mix & match total disc' || $description=='discount') continue;
		
						$acc=$this->accSettings[$description]['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
		
						$type=($description=='rounding')?"credit":"debit";
		
						if($description=='cash') $rPayment['amount']-=$rPos['amount_change'];
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPayment["branch_id"])) $upd["branch_id"] = $rPayment['branch_id'];
						$upd["counter_id"] = $rPayment['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = $type;
						$upd["acc_type"] = $description;
						$upd["description"] = $rPayment['type'];
						$upd["ItemAmount"] = $rPayment['amount'];
						$upd["TotalAmount"] = $rPayment['amount'];
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd, $sku, $acc, $description, $type);
						$rIdx++;
					}
		
					unset($rPayment);
				}
				$pos_db->sql_freeresult($ret1);
				
				$short_over = round($rPos['amount_tender']-$rPos['amount']-$rPos['amount_change']-$rPos['service_charges'],2);
				if($short_over!=0){
					$cus_acc=$this->accSettings['customer_code']['account'];
					$acc_short=isset($this->accSettings['short'])?$this->accSettings['short']['account']:array("account_code"=>"","account_name"=>"");
					$acc_over=isset($this->accSettings['over'])?$this->accSettings['over']['account']:array("account_code"=>"","account_name"=>"");
		
					$acc=($short_over>0)?$acc_over:$acc_short;
		
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					if(isset($rPos["counter_id"])) $upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'credit';
					$upd["acc_type"] = ($short_over>0)?"over":"short";
					$upd["description"] = ($short_over>0)?"Over":"Short";
					$upd["ItemAmount"] = $short_over;
					$upd["TotalAmount"] = $short_over;
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
					$upd["customer_remark"] = $customer_remark;
		
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $acc, $description, $type);
					$rIdx++;
				}
	
				if(isset($rPos['deposit']) && $rPos['deposit']){
					$ret1 = $this->get_pos_deposit($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$acc=$this->accSettings['deposit']['account'];
							$cus_acc=$this->accSettings['customer_code']['account'];
							$rItem['gst_info']=unserialize($rItem['gst_info']);
							
							$upd = array();
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["doc_no"] = $receipt_no;
							$upd["ref_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = "credit";
							$upd["acc_type"] = "deposit";
							$upd["description"] = "Deposit";
							$upd["qty"] = 1;
							$upd["unit_price"] = round($rItem['deposit_amount'], 2);
							if(isset($rItem['gst_info']['code']) && $rItem['gst_info']['rate']!=""){
								$upd["tax_code"] = $rItem['gst_info']['code'];
								$upd["taxRate"] = $rItem['gst_info']['rate'];
								$upd["ItemAmount"] = $rItem['deposit_amount']-$rItem['gst_amount'];
								$upd["TaxAmount"] = $rItem['gst_amount'];
								$upd["TotalAmount"] = $upd["unit_price"];
							}else{
								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
							}
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
			
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							$upd["customer_remark"] = $customer_remark;
						
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd, $acc_tax);
							$rIdx++;
						}
					}
					$pos_db->sql_freeresult($ret1);
				}
				else{
					$ret1=$this->get_pos_items($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							
							$has_credit_note = 0;
							if($rItem['qty']<0) {
								$retCN = $this->get_credit_note($pos_db,$where,false,false,$rItem);
								if($pos_db->sql_numrows($retCN)>0)
								{
									$creditNoteInfo = $pos_db->sql_fetchrow($retCN);									
									$has_credit_note = 1;
									
								}
								$pos_db->sql_freeresult($retCN);
							}
							if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);
							$discount = $rItem['discount']+$rItem['discount2'];
		
							$acc = $this->accSettings['sales']['account'];
							$cus_acc=$this->accSettings['customer_code']['account'];
			
							$upd = array();
							if(isset($creditNoteInfo) && $creditNoteInfo)
							{
								$upd['credit_note_no'] = $creditNoteInfo['credit_note_no'];
								$upd['credit_note_ref_no'] = $creditNoteInfo['credit_note_ref_no'];
								unset($creditNoteInfo);
							}
							$upd['has_credit_notes'] = $has_credit_note;
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["doc_no"] = $receipt_no;
							$upd["ref_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = "credit";
							$upd["acc_type"] = "sales";
							$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
							$upd["arms_code"] = $sku['arms_code'];
							$upd["qty"] = $rItem['qty'];
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$tax_amount=round($rItem['tax_amount'],2);
								$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["taxRate"] = $rItem['tax_rate'];
								$upd["ItemAmount"] = $rItem['before_tax_price'];
								$upd["TaxAmount"] = $tax_amount;
								$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
							}else{
								$upd["unit_price"] = $rItem['price']/$rItem['qty'];
								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
							}
							
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							$upd["customer_remark"] = $customer_remark;
			
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							$rIdx++;
						}
						unset($rItem, $upd, $sku, $discount, $tax_amount, $acc_tax);
					}
					$pos_db->sql_freeresult($ret1);
				}
	
				if(isset($rPos['pos_more_info']['deposit'])){
					foreach($rPos['pos_more_info']['deposit'] as $deposit){
						if(isset($deposit['gst_info'])) $deposit['gst_info']=unserialize($deposit['gst_info']);
		
						$acc=$this->accSettings['deposit']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($deposit["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = 'debit';
						$upd["acc_type"] = 'deposit';
						$upd["description"] = "Deposit";
						$upd["ItemAmount"] = 0-$deposit['amount'];
						$upd["TotalAmount"] = 0-$deposit['amount'];
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "deposit";
						$upd["description"] = "Deposit";
						$upd["qty"] = 1;
						$upd["unit_price"] = 0-round($deposit['amount'], 2);
						if(isset($deposit['gst_info']['code']) && $deposit['gst_info']['rate']!=""){
							$upd["tax_code"] = $deposit['gst_info']['code'];
							$upd["taxRate"] = $deposit['gst_info']['rate'];
							$upd["ItemAmount"] = 0-($deposit['amount']-$deposit['gst_amount']);
							$upd["TaxAmount"] = 0-$deposit['gst_amount'];
							$upd["TotalAmount"] = $upd["unit_price"];
						}else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
		
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd, $acc_tax);
						$rIdx++;
					}
					unset($deposit);
				}
	
				if(isset($rPos['service_charges']) && $rPos['service_charges']>0){
					$acc=$this->accSettings['service_charge']['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "service_charge";
					$upd["description"] = "Service Charge";
					if(isset($rPos['pos_more_info']['service_charges']['sc_gst_detail'])){
						$upd["unit_price"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["tax_code"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['code'];
						$upd["taxRate"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['rate'];
						$upd["ItemAmount"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["TaxAmount"] = $rPos['service_charges_gst_amt'];
						$upd["TotalAmount"] = $rPos['service_charges'];
					}
					else{
						$upd["tax_code"] = $appCore->gstManager->getTextNR();
						$upd["unit_price"] = $rPos['service_charges'];
						$upd["ItemAmount"] = $upd["TotalAmount"] = $rPos['service_charges'];
					}
		
					if(isset($this->accSettings[$upd["tax_code"]])){
						$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
						$upd["tax_account_code"]=$acc_tax['account_code'];
						$upd["tax_account_name"]=$acc_tax['account_name'];
					}
					
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
					$upd["customer_remark"] = $customer_remark;
		
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $discount, $tax_amount, $acc_tax, $acc);
					$rIdx++;
				}
				$cond[] = "branch_id = ".ms($rPos['branch_id']);
				$cond[] = "counter_id = ".ms($rPos['counter_id']);
				$cond[] = "id = ".ms($rPos['id']);
				$cond[] = "date = ".ms($rPos['date']);				
				$where1 = implode(" and ",$cond);
				$this->sql_query($pos_db,"update pos set acc_is_exported = 1 where ".$where1);				
				unset($cond);
				//unset($where);
				
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);

		/*$ret=$this->get_membership_redemption($pos_db,$where);
		if($pos_db->sql_numrows($ret)>0){
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret)){				
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);

				$rPos['date']=strtotime($rPos['date']);

				$receipt_no = $rPos['redemption_no'];
				$receipt_ref_no = $rPos['redemption_no'];
				$posDate = date("Y-m-d",$rPos['date']);
				$posYM = date("Y-m-01",$rPos['date']);
				$customer_remark = $this->get_membership($pos_db, $rPos);

				$ret1=$this->get_membership_redemption_items($pos_db,$rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
						$acc=$this->accSettings['sales']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
		
						$upd = array();
						$upd["tablename"] = "membership";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = 'sales';
						$upd["description"] = 'Membership Redemption';
						$upd["qty"] = $rItem['qty'];

						$rItem['tax_code'] = $rItem['gst_code'];
						$rItem['tax_rate'] = $rItem['gst_rate'];
						$rItem['tax_amount'] = $rItem['line_gst_amt'];
						$rItem['before_tax_price'] = $rItem['line_gross_amt'];

						if($rItem['tax_code'] && $rItem['tax_rate']!=""){
							$tax_amount=round($rItem['tax_amount'],2);
							$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
							$upd["tax_code"] = $rItem['tax_code'];
							$upd["taxRate"] = $rItem['tax_rate'];
							$upd["ItemAmount"] = $rItem['before_tax_price'];
							$upd["TaxAmount"] = $tax_amount;
							$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
						}else{
							$upd["tax_code"] = "NR";
							$upd["unit_price"] = $rItem['price']/$rItem['qty'];
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = serialize($customer_remark);

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
				}
				$pos_db->sql_freeresult($ret1);

				if($rPos['total_amount'] > 0){
					$acc=$this->accSettings['cash']['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];

					if(in_array($rPayment['type'],array_keys($credit_card))){
						$description = 'credit_card';
					}
					else{
						$description = strtolower($rPayment['type']);
					}
					$type=($description=='rounding')?"credit":"debit";

					$upd = array();
					$upd["tablename"] = "membership";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'debit';
					$upd["acc_type"] = 'cash';
					$upd["description"] = 'Membership Redemption';
					$upd["ItemAmount"] = $rPos['total_amount'];
					$upd["TotalAmount"] = $rPos['total_amount'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
	
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $acc);
					$rIdx++;
				}
				
				$cond[] = "branch_id = ".ms($rPos['branch_id']);
				$cond[] = "id = ".ms($rPos['id']);
				$cond[] = "redemption_no = ".ms($rPos['redemption_no']);
				$cond[] = "card_no = ".ms($rPos['card_no']);
				$cond[] = "nric = ".ms($rPos['nric']);
				$where1 = "where ".implode(" and ",$cond);				
				$this->sql_query($pos_db,"update membership_redemption set acc_is_exported = 1 ".$where1);
				unset($cond,$where1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
		//end membership_redemption*/
	
		$ret=$this->get_do($pos_db,$where);
		if($pos_db->sql_numrows($ret)>0){
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret)){
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);
				$orgDoDate = $rPos['do_date'];
				$rPos['do_date'] = strtotime($rPos['do_date']);
				$rPos['open_info'] = unserialize($rPos['open_info']);

				$receipt_no = $rPos['inv_no'];
				$receipt_ref_no = $rPos['do_no'];
				$posDate = date("Y-m-d",$rPos['do_date']);
				$posYM = date("Y-m-01",$rPos['do_date']);
				$customer_remark=$rPos['open_info']['name'];

				$ret1=$this->get_do_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
						$acc=$this->accSettings['sales']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["taxRate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}

				$pos_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
						$acc=$this->accSettings['sales']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["taxRate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}

				$pos_db->sql_freeresult($ret1);

				if($rPos['total_inv_amt'] > 0){
					$acc=$this->accSettings['cash']['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'debit';
					$upd["acc_type"] = 'cash';
					$upd["description"] = 'CASH';
					$upd["ItemAmount"] = $rPos['total_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
					$upd["customer_remark"] = $customer_remark;

					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $acc,$cond,$where1);
					$rIdx++;
				}

				if($rPos['total_round_inv_amt'] != 0){
					$acc=$this->accSettings["rounding"]['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "rounding";
					$upd["description"] = "";
					$upd["ItemAmount"] = $rPos['total_round_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_round_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
					$upd["customer_remark"] = $customer_remark;

					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $sku, $acc, $type,$cond,$where1);
					$rIdx++;
				}
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);		
	}

	function create_account_payable($tmpSalesDb){
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
								`branch_id` integer,
								`id` integer,
								`batchno` varchar(20),
								`inv_no` char(150),
								`ref_no` char(150),
								`vendor_code` char(150),
								`vendor_id` char(150),
								`vendor_name` char(150),
								`vendor_brn` char(150),
								`vendor_gstno` char(150),
								`inv_date` date,
								`posting_date` date,
								`sku_desc` char(150),
								`gl_code` char(150),
								`gl_name` text,
								`department` char(150) default 0,
								`job_code` char(150) default 0,
								`taxCode` char(150),
								`taxRate` double default 0,
								`ItemAmount` double,
								`TaxAmount` double,
								`TotalAmount` double,
								`currency_code` char(150) default 'XXX',
								`currency_amount` double default 0,
								`currency_gst_amount` double default 0,
								`receive_qty` double,
								`vendor_terms` char(100),
								`item_cost` double,
								`ym` date,
								`type` char(10),
								`arms_code` char(50),
								`account_code` char(100),
								`account_name` char(100),
								`tax_account_code` char(100),
								`tax_account_name` char(100),
								second_tax_code char(30),
								primary key(`branch_id`,`id`))");
		$tmpSalesDb->sql_freeresult();
	}

	function update_account_payable($tmpSalesDb,$grn_db=null,$vendor_db=null,$sku_db=null,$where=array(),$branchCode)
	{		
		global $config, $appCore;
		$ret = $this->get_grn($grn_db,$where);
		$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);
		if($grn_db->sql_numrows($ret)>0)
		{
			
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($result = $this->sql_fetchrow($grn_db, $ret))
			{
				if($result['vendor_id']>0) $vendor = $this->get_vendor($vendor_db,$result);

				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings'] = $this->accSettings;
				load_setting($accountings,$FormatType,$result['branch_id']);
				$this->accSettings = $accountings[$FormatType]['settings'];
				unset($accountings);
				if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
					$job = $branchCode;
				else
					$job = 0;
				$ref_no="GRR".sprintf("%05d",$result['grr_id']);
				$ym=date("Y-m-d",strtotime($result['doc_date']));
				
				$result['total_purchase_price_inc_gst'] = $result['amount'];
				$result['total_purchase_price_excl_gst'] = $result['amount']-$result['gst_amount'];
				$result['total_gst_amount'] = $result['gst_amount'];
				
				$invoice_date = $result['doc_date'];
				$posting_date = $result['rcv_date'];
				
				if(isset($result['branch_id'])) $upd["branch_id"] = $result['branch_id'];
				$upd["id"] = $rIdx;
				$upd["batchno"] = $this->get_batchno($invoice_date);
				$upd["inv_no"] = $result['doc_no'];
				$upd["ref_no"] = $ref_no;
				$upd["vendor_code"] = strval($vendor['code']);
				$upd["vendor_id"] = $vendor['id'];
				$upd["vendor_name"] = (isset($vendor['company_name']))?$vendor['company_name']:"";
				$upd["vendor_brn"] = (isset($vendor['company_no']))?$vendor['company_no']:"";
				$upd["vendor_gstno"] = (isset($vendor['gst_register_no']))?$vendor['gst_register_no']:"";
				$upd["vendor_terms"] = (isset($vendor['vendor_terms_code']))?$vendor['vendor_terms_code']:"";
				$upd["inv_date"] = $invoice_date;
				$upd["posting_date"] = $posting_date;
				$upd["arms_code"] = $sku['arms_code'];
				$upd["sku_desc"] = $sku['sku_desc'];
				$upd["gl_code"] = (isset($vendor['account_payable_code']) && trim($vendor['account_payable_code'])!="")?$vendor['account_payable_code']:$this->accSettings['purchase']['account']['account_code'];
				$upd["gl_name"] = (isset($vendor['account_payable_name']) && trim($vendor['account_payable_code'])!="")?$vendor['account_payable_name']:$this->accSettings['purchase']['account']['account_name'];
				$upd["receive_qty"] = $result['receive_qty'];
				$upd["item_cost"] = $result['item_cost'];
				if($result['gst_id'] == 0){
					$upd["second_tax_code"] = $upd["taxCode"] = $appCore->gstManager->getTextNR();
					$upd["ItemAmount"] = $result['total_purchase_price_excl_gst'];
					$upd["TaxAmount"] = $this->selling_price_currency_format(0);
					$upd["TotalAmount"] =  $result['total_purchase_price_excl_gst'];
				}
				else{
					$upd["taxCode"] = $result['gst_code'];
					$upd["second_tax_code"] = $result['second_tax_code']?$result['second_tax_code']:$result['taxCode'];
					$upd["taxRate"] = $result['gst_rate'];
					$upd["ItemAmount"] = $result['total_purchase_price_excl_gst'];
					$upd["TaxAmount"] = $result['total_gst_amount'];
					$upd["TotalAmount"] = $result['total_purchase_price_inc_gst'];
				}

				if(isset($this->accSettings[$upd["taxCode"]]['account'])){
					$acc_tax = $this->accSettings[$upd["taxCode"]]['account'];
					$upd["tax_account_code"] =  $acc_tax['account_code'];
					$upd["tax_account_name"] =  $acc_tax['account_name'];
				}
				$upd["ym"] = $ym;
				$upd["type"] = $result['type'];
				$upd["job_code"] = $job;
				$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable.mysql_insert_by_field($upd));
				$rIdx++;
				
				unset($upd,$acc_tax);
				
				$grn_db->sql_freeresult($ret1);
				$cond[] = "branch_id = ".ms($result['branch_id']);
				$cond[] = "id = ".ms($result['grr_id']);
				$cond[] = "rcv_date = ".ms($result['rcv_date']);
				$where1 = "where ".implode(" and ",$cond);
				$this->sql_query($grn_db,"update grr set acc_is_exported = 1 ".$where1);
				unset($vendor,$invoice_date,$cond,$where1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
	}

    function create_account_receiver($tmpSalesDb){
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
									`branch_id` integer,
									`id` integer,
									`batchno` varchar(20),
									`customer_code` char(150),
									`customer_name` char(150),
									`customer_brn` char(150),
									`customer_gst_no` char(150),
									`inv_no` char(150),
									`docnoex` char(150),
									`do_date` date,
									`terms` char(150),
									`currencyrate` double default 1,
									`acc_description` char(150),
									`sku_description` char(150),
									`sku_cat_desc` char(150),
									`qty` double,
									`uom` char(150) default 'UNIT',
									`suomqty` double,
									`unit_price` double,
									`disc` char(150),
									`tax_code` char(150),
									`ItemAmount` double,
									`TaxAmount` double,
									`TotalAmount` double,
									`acc_sales_code` char(150),
									`remark1` text,
									`remark2` text,
									`country` char(150),
									`address` text,
									`ItemFAmount` double,
									`TaxFAmount` double,
									`TotalFAmount` double,
									`foreign_currency_code` char(150) default 'XXX',
									`foreign_currency_amount` double default 0,
									`foreign_currency_gst_amount` double default '0',
									`taxRate` double,
									`customer_remark` text,
									`arms_code` char(20),
									`selling_price_inc_gst` double,
									`ym` date,
									`account_code` char(100),
									`account_name` char(100),
									`tax_account_code` char(100),
									`tax_account_name` char(100),
									primary key(`branch_id`,`id`,`do_date`,`inv_no`))");
		$tmpSalesDb->sql_freeresult();
	}

	function update_account_receiver($tmpSalesDb,$do_db,$sku_db=null,$where=array()){
		global $config, $appCore;

		$ret=$this->get_do($do_db,$where,array('transfer','credit_sales'));
		if($do_db->sql_numrows($ret)>0)
		{
			$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);

			while($rDo = $this->sql_fetchrow($do_db, $ret))
			{
				$orgDoDate = $rDo['do_date'];
				$rDo['do_date']=strtotime($rDo['do_date']);
				$doDate = date("Y-m-d",$rDo['do_date']);

				if($rDo['do_type']=='transfer'){
					$branch=$this->get_branch($do_db,$rDo['do_branch_id']);
					$debtor=array('code'=>$branch['account_code_debtor'],
								'description'=>$branch['description'],
								'address'=>$branch['address'],
								'terms'=>$branch['con_terms'],
								'account_receivable_code'=>$branch['account_receivable_code'],
								'account_receivable_name'=>$branch['account_receivable_name'],
								'brn'=>$branch['company_no'],
								'gst_register_no'=>$branch['gst_register_no']);
				}
				else{
					$debtor=$this->get_debtor($do_db,$rDo['debtor_id']);
					$customer_remark = $debtor['description'];
					$customer_brn = $debtor['company_no'];
				}
				
				$rDo['total']=0;
				$ret1=$this->get_do_items($do_db,$rDo);

				if($do_db->sql_numrows($ret1)>0){
					while($rItem = $this->sql_fetchrow($do_db, $ret1)){
						$FormatType=$this->get_name();
						$accountings[$FormatType]['settings']=$this->accSettings;
						load_setting($accountings,$FormatType,$rDo['branch_id']);
						$this->accSettings=$accountings[$FormatType]['settings'];
						unset($accountings);

						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

						$upd["branch_id"] = $rDo['branch_id'];
						$upd["id"] = $rIdx;
						$upd["batchno"] = $this->get_batchno($doDate);

						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["acc_sales_code"] = $debtor['account_receivable_code'];
							$upd["acc_description"] = $debtor['account_receivable_name'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
								$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["acc_description"] = $this->accSettings['credit_sales']['account']['account_name'];
			
								$upd["account_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account']['account_name'];
							}
						}

						$upd["inv_no"] = $rDo['inv_no'];
						$upd["do_date"] = $doDate;
						$upd["sku_description"] = $sku['sku_desc'];
						$upd["sku_cat_desc"] = $sku['category_desc'];
						$upd["arms_code"] = $sku['arms_code'];

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$upd["qty"] = $rItem['row_qty'];

						//if($rItem['gst_code'] && $rItem['gst_rate']>0){
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["taxRate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["taxRate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}

						$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['row_qty']),2);

						$upd["customer_remark"] = $customer_remark;

						$upd["ym"] = date("Y-m-01",$rDo['do_date']);
					
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
					}
					$cond[] = "id = ".ms($rDo['id']);
					$cond[] = "branch_id = ".ms($rDo['branch_id']);
					$cond[] = "do_no = ".ms($rDo['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($do_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}	
				$do_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($do_db,$rDo);

				if($do_db->sql_numrows($ret1)>0){
					while($rItem = $this->sql_fetchrow($do_db, $ret1)){
						$FormatType=$this->get_name();
						$accountings[$FormatType]['settings']=$this->accSettings;
						load_setting($accountings,$FormatType,$rDo['branch_id']);
						$this->accSettings=$accountings[$FormatType]['settings'];
						unset($accountings);

						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

						$upd["branch_id"] = $rDo['branch_id'];
						$upd["id"] = $rIdx;
						$upd["batchno"] = $this->get_batchno($doDate);

						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["acc_sales_code"] = $debtor['account_receivable_code'];
							$upd["acc_description"] = $debtor['account_receivable_name'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
								$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["acc_description"] = $this->accSettings['credit_sales']['account']['account_name'];
			
								$upd["account_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account']['account_name'];
							}
						}

						$upd["inv_no"] = $rDo['inv_no'];
						$upd["do_date"] = $doDate;
						$upd["sku_description"] = $sku['sku_desc'];
						$upd["sku_cat_desc"] = $sku['category_desc'];
						$upd["arms_code"] = $sku['arms_code'];

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$upd["qty"] = $rItem['row_qty'];

						//if($rItem['gst_code'] && $rItem['gst_rate']>0){
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["taxRate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["taxRate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}

						$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['row_qty']),2);

						$upd["customer_remark"] = $customer_remark;

						$upd["ym"] = date("Y-m-01",$rDo['do_date']);
					
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
					}
					$cond[] = "id = ".ms($rDo['id']);
					$cond[] = "branch_id = ".ms($rDo['branch_id']);
					$cond[] = "do_no = ".ms($rDo['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($do_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}	
				$do_db->sql_freeresult($ret1);
			}
		}

		if($config['consignment_modules']){
			$ret=$this->get_ci($do_db);
			if($do_db->sql_numrows($ret)>0){
				$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);

				while($rDo = $this->sql_fetchrow($do_db, $ret)){
					$FormatType=$this->get_name();
					$accountings[$FormatType]['settings']=$this->accSettings;
					load_setting($accountings,$FormatType,$rDo['branch_id']);
					$this->accSettings=$accountings[$FormatType]['settings'];
					unset($accountings);

					if($rDo['discount_percent']>0){
						$total_discount=$rDo['sub_total_amt'];
						$this->cal_discount($total_discount,$rDo['discount_percent']);
						$total_discount_per=$total_discount/$rDo['sub_total_amt'];
					}
					else{
						$total_discount=0;
						$total_discount_per=0;
					}
					$orgDoDate = $rDo['ci_date'];
					$rDo['ci_date']=strtotime($rDo['ci_date']);
					$doDate = date("Y-m-d",$rDo['ci_date']);

					$debtor=$this->get_branch($do_db,$rDo['ci_branch_id']);

					$rDo['total']=0;
					$ret1=$this->get_ci_items($do_db,$rDo);
					
					if($do_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($do_db, $ret1)){
							if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

							$upd["branch_id"] = $rDo['branch_id'];
							$upd["id"] = $rIdx;
							$upd["batchno"] = $this->get_batchno($doDate);

							if(isset($debtor)){
								$upd["customer_code"] = $debtor["code"];
								$upd["customer_name"] = $debtor["description"];
								$upd["address"] = $debtor["address"];
								$upd["terms"] = $debtor["con_terms"];
								$upd["acc_sales_code"] = $debtor['account_receivable_code'];
								$upd["acc_description"] = $debtor['account_receivable_name'];
								$upd["account_code"] = $debtor['account_receivable_code'];
								$upd["account_name"] = $debtor['account_receivable_name'];
							}
							else{
								$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_code']:"";
								$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_name']:"";
								$upd["address"] = "";

								if(isset($this->accSettings['terms']) && $upd["terms"]=="") $upd["terms"] = $this->accSettings['terms']['account']['account_code'];
								if(isset($this->accSettings['credit_sales'])) {
									$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account']['account_code'];
									$upd["acc_description"] = $this->accSettings['credit_sales']['account']['account_name'];
									$upd["account_code"] = $this->accSettings['credit_sales']['account']['account_code'];
									$upd["account_name"] = $this->accSettings['credit_sales']['account']['account_code'];
								}
							}

							$upd["inv_no"] = $rDo['ci_no'];
							$upd["do_date"] = $doDate;
							$upd["sku_description"] = $sku['sku_desc'];
							$upd["sku_cat_desc"] = $sku['category_desc'];
							$upd["arms_code"] = $sku['arms_code'];

							$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$upd["qty"] = $rItem['row_qty'];

							$upd["unit_price"] = round($rItem['cost_price'],2);
							if($rItem['gst_code'] && $rItem['gst_rate']!=""){
								$upd["tax_code"] = $rItem['gst_code'];
								$upd["taxRate"] = $rItem['gst_rate'];
							}else{
								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["taxRate"] = 0;
							}
							
							$upd["ItemAmount"] = round($rItem['item_amt2'],2);
							$upd["TaxAmount"] = round($rItem['item_gst2'],2);
							$upd["TotalAmount"] = round($rItem["item_gst_amt2"],2);

							$upd["ItemFAmount"] = round($rItem['item_foreign_amt2'],2);
							$upd["TaxFAmount"]=round($rItem['item_foreign_gst_amt2']-$rItem['item_foreign_amt2'],2);
							$upd["TotalFAmount"] = round($rItem['item_foreign_gst_amt2'],2);
			
							$upd["foreign_currency_gst_amount"]=round($rItem['item_foreign_gst_amt2'],2);
							$upd["foreign_currency_amount"]=round($rItem['item_foreign_amt2'],2);
			
							$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['qty']),2);
							
							/*else{
								$ItemAmount=$rItem['cost_price']*$upd["qty"];
				
								if($rDo['discount_selling_price_percent']!=""){
									$this->cal_discount($ItemAmount,$rDo['discount_selling_price_percent']);
								}
				
								if($rDo['discount_item_row_percent']!=""){
									$this->cal_discount($ItemAmount,$rDo['discount_item_row_percent']);
								}
				
								if($total_discount_per>0){
									$ItemAmount=round($ItemAmount*$total_discount_per,4);
								}

								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["taxRate"] = 0;
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $ItemAmount;

								$ItemFAmount=$rItem['foreign_cost_price']*$upd["qty"];

								if($rDo['discount_selling_price_percent']!=""){
									$this->cal_discount($ItemFAmount,$rDo['discount_selling_price_percent']);
								}

								if($rDo['discount_item_row_percent']!=""){
									$this->cal_discount($ItemFAmount,$rDo['discount_item_row_percent']);
								}

								if($total_discount_per>0){
									$ItemFAmount=round($ItemFAmount*$total_discount_per,4);
								}

								$upd["TaxFAmount"] = 0;
								$upd["ItemFAmount"] = $upd["TotalFAmount"] = $ItemFAmount;
				
								$upd["foreign_currency_gst_amount"]=round($ItemFAmount,2);
								$upd["foreign_currency_amount"]=round($ItemFAmount,2);
								$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['qty']),2);
							}*/

							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}

							if($rDo['exchange_rate']>0) $upd["currencyrate"]=$rDo['exchange_rate'];

							if(isset($debtor['currency_code'])) $upd['foreign_currency_code']=$debtor['currency_code'];
							$upd["customer_remark"] = "";

							$upd["ym"] = date("Y-m-01",$rDo['ci_date']);

							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd);
							$rIdx++;
						}
						$cond[] = "id = ".ms($rDo['id']);
						$cond[] = "branch_id = ".ms($rDo['branch_id']);
						$cond[] = "ci_no = ".ms($rDo['ci_no']);
						$cond[] = "ci_date = ".ms($orgDoDate);
						$where1 = "where ".implode(" and ",$cond);											
						$this->sql_query($do_db,"update ci set acc_is_exported = 1 ".$where1);
						unset($cond,$where1);
					}					
				}
			}
		}
	}

	function create_credit_note($tmpSalesDb){
		global $config;
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
								`branch_id` integer,
								`counter_id` integer,
								`id` integer,
								`batchno` varchar(20),
								`date` date,
								`ym` date,
								`return_receipt_no` char(30),								  
								`credit_note_no` char(30),								  
								`sku_description` char(150),
								`sku_cat_desc` char(150),
								`arms_code` char(20),
								`uom` char(20) default 'UNIT',
								`qty` double,
								`currency_code` char(10) default '" . $config["arms_currency"]["code"] . "',
								`currencyrate` double default 1,
								`tax_code` char(5),
								`tax_rate` double,
								`ItemAmount` double,
								`TaxAmount` double,
								`TotalAmount` double,
								`ItemFAmount` double,
								`TaxFAmount` double,
								`TotalFAmount` double,
								`reason` text,
								`terms` char(20),
								`customer_code` char(150),
								`customer_name` char(150),
								`account_code` char(100),
								`account_name` char(100),
								`tax_account_code` char(100),
								`tax_account_name` char(100),
								`reason_code` char(100),
								`reason_description` text,
								 primary key(`branch_id`,`counter_id`,`id`,`date`,`return_receipt_no`,`credit_note_no`))");
		$tmpSalesDb->sql_freeresult();
	}

	function update_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array()){
	  global $LANG, $config, $appCore;
	  
		$i=1;
		$ret = $this->get_credit_note($pos_db, $where,false,false,false,true);
		if($pos_db->sql_numrows($ret) > 0)
		{
			while($rCN = $this->sql_fetchrow($pos_db, $ret))
			{	
				$retItem = $this->get_credit_note_pos_items($pos_db,$where,$rCN);
				if($pos_db->sql_numrows($retItem)>0)
				{
					$FormatType = $this->get_name();
					$accountings[$FormatType]['settings'] = $this->accSettings;
					load_setting($accountings, $FormatType, $rCN['branch_id']);
					$this->accSettings = $accountings[$FormatType]['settings'];
					unset($accountings, $FormatType);
		
					$rCN['pos_time'] = strtotime($rCN['pos_time']);
					$credit_note_no = (trim($rCN['credit_note_ref_no'])!=""?$rCN['credit_note_ref_no']:$rCN['credit_note_no']);
					if(isset($rCN['branch_id'])) $upd['branch_id'] = $rCN['branch_id'];
					if(isset($rCN['counter_id'])) $upd['counter_id'] = $rCN['counter_id'];
					while($rCNItem = $this->sql_fetchrow($pos_db, $retItem))
					{
						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rCNItem['sku_item_id']);
						if(isset($rCNItem['return_receipt_ref_no']) && trim($rCNItem['return_receipt_ref_no'])!="") {
							$return_receipt_no = $rCNItem['return_receipt_ref_no'];
						}
						else{
							$return_receipt_no = sprintf("%s%s",date("ymd",strtotime($rCNItem['return_date'])),$rCNItem['return_receipt_no']);
						}
							
						if(isset($rCN['goods_return_reason']) && trim($rCN['goods_return_reason'])!="")
						{
							list($grrCode,$grrDesc) = explode("-",$rCN['goods_return_reason']);
						}
						$upd['id'] = $i;
						$upd["batchno"] = $this->get_batchno(date("Y-m-d",$rCN['pos_time']));
						$upd['date'] = date("Y-m-d",$rCN['pos_time']);
						$upd['ym'] = date("Y-m-01",$rCN['pos_time']);
						$upd['return_receipt_no'] = $return_receipt_no;
						$upd['credit_note_no'] = $credit_note_no;
						$upd['sku_description'] = $sku['sku_desc'];
						$upd['sku_cat_desc'] = $sku['category_desc'];
						$upd['arms_code'] = $sku['arms_code'];
						$upd['uom'] = "Unit";
						$upd['qty'] = $rCNItem['qty'];
						$upd['reason_code'] = ((isset($grrCode))?$grrCode:"FGRT");
						$upd['reason_description'] = ((isset($grrDesc))?$grrDesc:"Faulty Item");
						$upd['reason'] = ((isset($rCNItem['goods_return_reason']) && trim($rCNItem['goods_return_reason'])!="")?$rCNItem['goods_return_reason']:"Faulty Item");				
						$upd['currencyrate'] = 1;
						if(isset($this->accSettings['terms']['account']))
						{
							$account = $this->accSettings['terms']['account'];
							$upd['terms'] = $account["account_code"];
						}
						
						if($rCNItem['tax_code'] && $rCNItem['tax_rate']!="")
						{
							$tax_amount=round($rCNItem['tax_amount'],2);
							
							$upd["tax_code"] = $rCNItem['tax_code'];
							$upd["tax_rate"] = $rCNItem['tax_rate'];
							$upd["ItemAmount"] = $rCNItem['before_tax_price'];
							$upd["TaxAmount"] = $tax_amount;
							$upd["TotalAmount"] = ($upd["ItemAmount"] + $upd["TaxAmount"]);
							
							unset($tax_amount);
						}
						else
						{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["tax_rate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $rCNItem['before_tax_price'];
						}
						if(isset($this->accSettings["sales_return"]['account']))
							$account = $this->accSettings["sales_return"]['account'];
						else
							$account = $this->accSettings["sales"]['account'];
						$upd["account_code"] = $account['account_code'];
						$upd["account_name"] = $account['account_name'];
						$account = $this->accSettings['customer_code']['account'];
						$upd['customer_code'] = $account['account_code'];
						$upd['customer_name'] = $account['account_name'];
						if(isset($this->accSettings[$upd["tax_code"]])){
							$account = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$account['account_code'];
							$upd["tax_account_name"]=$account['account_name'];
						}
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$i++;
					}
					
					$cond[] = "credit_note_no = ".ms($rCN['credit_note_no']);
					$cond[] = "branch_id = ".ms($rCN['branch_id']);
					$cond[] = "counter_id = ".ms($rCN['counter_id']);
					$cond[] = "date = ".ms($rCN['date']);
					$cond[] = "pos_id = ".ms($rCN['pos_id']);
					$where1 = "where ".implode(" and ",$cond);
					$this->sql_query($pos_db,"update pos_credit_note set acc_is_exported = 1 ".$where1);
					unset($upd, $return_receipt_no, $credit_note_no, $account, $rCN,$cond,$where1);
				}
				$pos_db->sql_freeresult($retItem);
			}
		}
		$pos_db->sql_freeresult($ret);
  
		if($config['consignment_modules']){
		  $ret = $this->get_credit_note($pos_db, $where, true);
			if($pos_db->sql_numrows($ret) > 0){
				while($rCN = $this->sql_fetchrow($pos_db, $ret)){
					$FormatType=$this->get_name();
					$accountings[$FormatType]['settings']=$this->accSettings;
					load_setting($accountings,$FormatType,$rCN['branch_id']);
					$this->accSettings=$accountings[$FormatType]['settings'];
					unset($accountings);
					
					$branch=$this->get_branch($pos_db,$rCN['ci_branch_id']);
					$rCN['pos_time']=strtotime($rCN['ci_date']);
					$return_receipt_no = $rCN['inv_no'];
					$credit_note_no = $return_receipt_no;
					
					$ret1 = $this->get_credit_note_items($pos_db, $rCN);
		
					if($pos_db->sql_numrows($ret1) > 0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
							
							$branch = $this->get_branch($pos_db, $rCN['to_branch_id']);

							$debtor = array(
									'customer_code'=>$branch['account_code'],
									'customer_name'=>$branch['description'],
									'terms'=>$branch['con_terms'],
									'currency_code'=>$branch['currency_code']);
				
							$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$rItem['tax_code'] = $rItem['gst_code'];
							$rItem['tax_rate'] = $rItem['gst_rate'];
				
							//if($rItem['tax_code'] != "" && $rItem['tax_rate'] != ""){
								$rItem['tax_amount'] = $rItem['item_gst2'];
								$rItem['before_tax_price'] = $rItem['item_amt2'];
				
								if($rCN['exchange_rate']>1){
									$rItem['exchange_rate'] = $rCN['exchange_rate'];
									$rItem['tax_amount_f'] = $rItem['item_foreign_gst2'];
									$rItem['before_tax_price_f'] = $rItem['item_foreign_amt2'];
								}
							/*}
							else{
								if($rCN['discount'] != ""){
									$total_discount = $rCN['sub_total_amt'];
									$this->cal_discount($total_discount, $rCN['discount']);
									$total_discount_per = $total_discount / $rCN['sub_total_amt'];
								}
								else{
									$total_discount = 0;
									$total_discount_per = 1;
								}

								$rItem['tax_amount'] = 0;
								$rItem['before_tax_price'] = ($rItem['cost_price'] * $rItem['qty']) - $rItem['discount_amt'];
				
								$rItem['before_tax_price'] = $rItem['before_tax_price'] * $total_discount_per;
				
								if($rCN['exchange_rate']>1){
									$rItem['exchange_rate'] = $rCN['exchange_rate'];
									$rItem['tax_amount_f'] = 0;
									$rItem['before_tax_price_f'] = ($rItem['foreign_cost_price'] * $rItem['qty']) - $rItem['foreign_discount_amt'];;
				
									$rItem['before_tax_price_f'] = $rItem['before_tax_price_f'] * $total_discount_per;
								}
				
								unset($total_discount, $total_discount_per);
							}*/
				
							unset($branch);
							
							$upd['branch_id'] = $rCN['branch_id'];
							$upd['id'] = $i;
							$upd["batchno"] = $this->get_batchno($rCN['date']);
							$upd['date'] = $rCN['date'];
							$upd['ym'] = date("Y-m-01",strtotime($rCN['date']));
							$upd['return_receipt_no'] = $return_receipt_no;
							$upd['credit_note_no'] = $credit_note_no;
							$upd['sku_description'] = $sku['sku_desc'];
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd['arms_code'] = $sku['arms_code'];
							$upd['uom'] = "Unit";
							$upd['qty'] = $rItem['row_qty'];
							
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["tax_rate"] = $rItem['tax_rate'];
							}else{
								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["tax_rate"] = 0;
							}
							
							$upd["ItemAmount"] = $rItem['before_tax_price'];
							$upd["TaxAmount"] = $rItem['tax_amount'];
							$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
												
							if($rItem['exchange_rate']>1){
								$upd["currencyrate"] = $rItem['exchange_rate'];
								$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
								$upd["TaxFAmount"] = $rItem['tax_amount_f'];
								$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
							}
					
							$upd['reason'] = $rCN['remark'];
							$upd['terms'] = $debtor['terms'];
							$upd['currency_code'] = $debtor['currency_code'];
							$upd['customer_code'] = $debtor['customer_code'];
							$upd['customer_name'] = $debtor['customer_name'];
							
							if(isset($this->accSettings['sales'])){
								$upd["account_code"] = $this->accSettings['sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['sales']['account']['account_name'];
							}
					
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"] = $acc_tax['account_code'];
								$upd["tax_account_name"] = $acc_tax['account_name'];
							}
								
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd);
							$i++;
						}
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "inv_no = ".ms($rCN['inv_no']);
						$cond[] = "date = ".ms($rCN['date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cn set acc_is_exported=1 ".$where1);
						unset($cond,$where);
					}
				}
			}
		}
		else		
		{
			$ret = $this->get_credit_note($pos_db, $where, false, true);
			if($pos_db->sql_numrows($ret) > 0)
			{
				while($rCN = $this->sql_fetchrow($pos_db, $ret))
				{					
					$FormatType=$this->get_name();
					$accountings[$FormatType]['settings']=$this->accSettings;
					load_setting($accountings,$FormatType,$rCN['branch_id']);
					$this->accSettings=$accountings[$FormatType]['settings'];
					unset($accountings);
										
					$rCN['pos_time']=strtotime($rCN['cn_date']);
					$credit_note_no = $rCN['cn_no'];
					$ret1 = $this->get_credit_note_items($pos_db, $rCN,true);
					$customer = $this->accSettings['customer_code']['account'];
					$terms = $this->accSettings['credit_term']['account'];
					if($pos_db->sql_numrows($ret1) > 0)
					{
						while($rItem = $this->sql_fetchrow($pos_db, $ret1))
						{
							$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
							
							//To get invoice_no
							$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
							if($pos_db->sql_numrows($retDo)>0)						
							{
								$rInv = $pos_db->sql_fetchrow($retDo);
								if($rInv['do_type']=='transfer'){
									$branch=$this->get_branch($pos_db,$rInv['do_branch_id']);
									$debtor=array('code'=>$branch['account_code_debtor'],
												'description'=>$branch['description'],
												'address'=>$branch['address'],
												'terms'=>$branch['con_terms'],
												'account_receivable_code'=>$branch['account_receivable_code'],
												'account_receivable_name'=>$branch['account_receivable_name']);
								}
								else{
									$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);
									
									$customer_remark = $debtor['description'];
								}								
							}
							else{
								$debtor = array(
									'code'=>$customer['account_code'],
									'description'=>$customer['account_name'],
									'terms'=>$terms['account_code'],
									'currency_code'=>$config["arms_currency"]["code"]);
							}
							
							$return_receipt_no = $rItem['inv_no'];
							$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$rItem['tax_code'] = $rItem['gst_code'];
							$rItem['tax_rate'] = $rItem['gst_rate'];
							$rItem['tax_amount'] = $rItem['line_gst_amt2'];
							$rItem['before_tax_price'] = $rItem['line_gross_amt2'];				
							
							$upd['branch_id'] = $rCN['branch_id'];
							$upd['id'] = $i;
							$upd["batchno"] = $this->get_batchno($rCN['cn_date']);
							$upd['date'] = $rCN['cn_date'];
							$upd['ym'] = date("Y-m-01",strtotime($rCN['cn_date']));
							$upd['return_receipt_no'] = $return_receipt_no;
							$upd['credit_note_no'] = $credit_note_no;
							$upd['sku_description'] = $sku['sku_desc'];
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd['arms_code'] = $sku['arms_code'];
							$upd['uom'] = "Unit";
							$upd['qty'] = $rItem['row_qty'];
							
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["tax_rate"] = $rItem['tax_rate'];
							}else{
								$upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["tax_rate"] = 0;
							}
							
							$upd["ItemAmount"] = $rItem['before_tax_price'];
							$upd["TaxAmount"] = $rItem['tax_amount'];
							$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
												
							if($rItem['exchange_rate']>1){
								$upd["currencyrate"] = $rItem['exchange_rate'];
								$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
								$upd["TaxFAmount"] = $rItem['tax_amount_f'];
								$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
							}
					
							$upd['reason'] = $rCN['remark'];
							$upd['terms'] = $debtor['terms'];
							$upd['currency_code'] = ((isset($debtor['currency_code']) && trim($debtor['currency_code'])!="")?$debtor['currency_code']:$config["arms_currency"]["code"]);
							$upd['customer_code'] = $debtor['code'];
							$upd['customer_name'] = $debtor['description'];
							
							if(isset($this->accSettings['sales_return'])){
								$upd["account_code"] = $this->accSettings['sales_return']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['sales_return']['account']['account_name'];
							}
							elseif(isset($debtor['account_receivable_code']))
							{
								$upd["account_code"] = $debtor['account_receivable_code'];
								$upd["account_name"] = $debotr['account_receivable_name'];
							}
						
							elseif(isset($this->accSettings['sales'])){
								$upd["account_code"] = $this->accSettings['sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['sales']['account']['account_name'];
							}
					
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"] = $acc_tax['account_code'];
								$upd["tax_account_name"] = $acc_tax['account_name'];
							}
								
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd,$debtor);
							$i++;
						}
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "cn_no = ".ms($rCN['cn_no']);
						$cond[] = "cn_date = ".ms($rCN['cn_date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cnote set acc_is_exported=1 ".$where1);
						unset($cond,$where1);
					}				
				}
			}
		}
	}
	
	function create_debit_note($tmpSalesDb){
	  $this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
	  $this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
									`branch_id` integer,
									`counter_id` integer,
									`id` integer,
									`batchno` varchar(20),
									`date` date,
									`ym` date,
									`invoice_no` char(50),
									`sku_description` char(50),
									`sku_cat_desc` char(50),
									`arms_code` char(30),
									`uom` char(150) default 'UNIT',
									`qty` double,
									`tax_code` char(5),
									`tax_rate` double,
									`ItemAmount` double,
									`TaxAmount` double,
									`TotalAmount` double,
									`reason` text,
									`currency_code` char(10),
									`currencyrate` double default 1,									
									`ItemFAmount` double,
									`TaxFAmount` double,
									`TotalFAmount` double,
									`terms` char(30),								  
									`customer_code` char(150),
									`customer_name` char(150),
									`account_code` char(100),
									`account_name` char(100),
									`tax_account_code` char(100),
									`tax_account_name` char(100),
									`reason_code` char(100),
									primary key(`branch_id`,`counter_id`,`id`,`date`,`invoice_no`))");
	  $tmpSalesDb->sql_freeresult();
	}

	function update_debit_note($tmpSalesDb, $pos_db, $sku_db=null, $where=array()){
	  global $LANG, $config;

		$ret = $this->get_debit_note($pos_db, $where);
		if($pos_db->sql_numrows($ret) > 0){
			while($rDN = $this->sql_fetchrow($pos_db, $ret)){
				$ret1 = $this->get_debit_note_items($pos_db, $rDN);

				if($pos_db->sql_numrows($ret1) > 0){
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
						$this->insert_debit_notes($tmpSalesDb, $pos_db, $sku_db, $rDN, $rItem);
					}
					$cond[] = "branch_id = ".ms($rDN['branch_id']);
					$cond[] = "id = ".ms($rDN['id']);
					$cond[] = "dn_no = ".ms($rDN['dn_no']);
					$cond[] = "dn_date = ".ms($rDN['dn_date']);
					$where1 = "where ".implode(" and ",$cond);
					$this->sql_query($pos_db,"update dnote set acc_is_exported = 1 ".$where1);
					unset($where1,$cond);
				}
			}
		}

		//Remove to import consignment modules debit note
		/*if($config['consignment_modules']){
			$ret = $this->get_debit_note($pos_db, $where, true);
			if($pos_db->sql_numrows($ret) > 0){
				while($rDN = $this->sql_fetchrow($pos_db, $ret)){
					$ret1 = $this->get_debit_note_items($pos_db, $rDN, true);

					if($pos_db->sql_numrows($ret1) > 0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$this->insert_debit_notes($tmpSalesDb, $pos_db, $sku_db, $rDN, $rItem);
						}
					}
				}
			}
		}*/
	}

	function create_cash_sales_credit_note($tmpSalesDb)
	{
		global $config;
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
										`tablename` varchar(20),
										`batchno` varchar(20),
										`branch_id` integer default 0,
										`counter_id` integer default 0,
										`id` integer,
										`pos_date` date,
										`doc_no` char(20),
										`ref_no` char(30),
										`ym` date,
										`type` char(20),
										`acc_type` char(20),
										`description` char(150),
										`return_receipt_no` char(30),	
										`credit_note_no` char(30),
										`sku_cat_desc` char(150),
										`arms_code` char(20),
										`uom` char(20) default 'UNIT',
										`qty` double,
										`unit_price` double,
										`currency_code` char(10) default '" . $config["arms_currency"]["code"] . "',
										`currencyrate` double default 1,
										`tax_code` char(5),
										`tax_rate` double,
										`ItemAmount` double,
										`TaxAmount` double,
										`TotalAmount` double,
										`ItemFAmount` double,
										`TaxFAmount` double,
										`TotalFAmount` double,
										`reason` text,
										`terms` char(20),
										`customer_code` char(150),
										`customer_name` char(150),
										`account_code` char(100),
										`account_name` char(100),
										`tax_account_code` char(100),
										`tax_account_name` char(100),
										`reason_code` char(100),
										`reason_description` text,
										`cancelled` char(1) default 'F',
										`transferable` char(1) default 'T',
										`customer_remark` text,
										`cn_remark` text,
										`is_credit_notes` char(1) default 0,
										`is_refund`	tinyint(1) default 0,
										second_tax_code char(30),
										primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_cash_sales_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config, $appCore;
		$credit_card = $this->credit_cards_type();
		$second_tax_code_list = $this->get_second_tax_code_list();
		
		// Cash Sales from POS Counter
		$ret=$this->get_pos($pos_db, $where);
		if($pos_db->sql_numrows($ret) > 0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
			while($rPos = $this->sql_fetchrow($pos_db, $ret))
			{
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);

				$rPos['pos_date']=strtotime($rPos['date']);
				$receipt_no = $rPos['receipt_no'];
				if(trim($rPos['print_full_tax_invoice_remark'])!="" && trim($rPos['print_full_tax_invoice_remark'])!="N;"){
					$customer_remark = $rPos['print_full_tax_invoice_remark'];
				}
				elseif((trim($rPos['print_full_tax_invoice_remark'])=="" || trim($rPos['print_full_tax_invoice_remark'])=="N;") && trim($rPos['special_exempt_remark'])!=""){
					$customer_remark = $rPos['special_exempt_remark'];
				}
				else{
					$customer_remark = "";
				}
				
				if(isset($rPos['pos_more_info'])) $rPos['pos_more_info'] = unserialize($rPos['pos_more_info']);
				
				$receipt_ref_no = $rPos['receipt_ref_no'];
				$posDate = date("Y-m-d",$rPos['pos_date']);
				$posYM = date("Y-m-01",$rPos['pos_date']);
				$is_cn = 0;
				$is_refund = 0;
				$retCN = $this->get_credit_note($pos_db,$where,false,false,array(),false,$rPos);
				if($pos_db->sql_numrows($retCN)>0)
				{
					while($rCN = $pos_db->sql_fetchassoc($retCN))
					{
						$is_cn = 1;
						$rCN['pos_time'] = strtotime($rCN['pos_time']);
						$credit_note_no = (trim($rCN['credit_note_ref_no'])!=""?$rCN['credit_note_ref_no']:$rCN['credit_note_no']);
						if(isset($rCN['branch_id'])) $upd['branch_id'] = $rCN['branch_id'];
						if(isset($rCN['counter_id'])) $upd['counter_id'] = $rCN['counter_id'];
						//$itemInfor = unserialize($rCN['item_infor']);
						$totalItemAmount = 0;
						$totalAmount = 0;
						$totalGSTAmount = 0;
						$retItem = $this->get_credit_note_pos_items($pos_db,$where,$rCN);
						if($pos_db->sql_numrows($retItem)>0)
						{
							while($rItem = $this->sql_fetchrow($pos_db, $retItem))
							{
								if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
								if(isset($rItem['return_receipt_ref_no']) && trim($rItem['return_receipt_ref_no'])!="") {
									$return_receipt_no = $rItem['return_receipt_ref_no'];
								}
								else{
									$return_receipt_no = sprintf("%s%s",date("ymd",strtotime($rItem['return_date'])),$rItem['return_receipt_no']);
								}
									
								if(isset($rItem['goods_return_reason']) && trim($rItem['goods_return_reason'])!="")
								{
									list($grrCode,$grrDesc) = explode("-",$rItem['goods_return_reason']);
								}
								
								if(isset($this->accSettings['sales_return']))
									$acc=$this->accSettings['sales_return']['account'];
								else
									$acc=$this->accSettings['sales']['account'];
								
								$upd = array();
								$upd['is_credit_notes'] = 1;
								$upd["tablename"] = "pos";
								$upd["batchno"] = $this->get_batchno($posDate);
								if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
								$upd["counter_id"] = $rPos['counter_id'];
								$upd["id"] = $rIdx;
								$upd["pos_date"] = $posDate;
								$upd["ym"] = $posYM;
								$upd["ref_no"] = $receipt_no;
								$upd["doc_no"] = $receipt_ref_no;
								$upd['credit_note_no'] = $credit_note_no;
								$upd['return_receipt_no'] = ((isset($rItem['return_receipt_ref_no']) && trim($rItem['return_receipt_ref_no'])!="")?$rItem['return_receipt_ref_no']:$rItem['return_receipt_no']);
								$upd["type"] = "debit";
								$upd["acc_type"] = "sales return";
								$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
								$upd['sku_cat_desc'] = $sku['category_desc'];
								$upd["arms_code"] = $sku['arms_code'];
								$upd["qty"] = $rItem['qty'];
								if($rItem['tax_code'] && $rItem['tax_rate']!=""){
									$tax_amount=round($rItem['tax_amount'],2);
									$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
									$upd["tax_code"] = $rItem['tax_code'];
									$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
									$upd["tax_rate"] = $rItem['tax_rate'];
									$upd["ItemAmount"] = $rItem['before_tax_price'];
									$upd["TaxAmount"] = $tax_amount;
									$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
								}else{
									$upd["unit_price"] = $rItem['price']/$rItem['qty'];
									$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
									$upd["TaxAmount"] = 0;
									$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
								}
								if(isset($this->accSettings[$upd["tax_code"]])){
									$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
									$upd["tax_account_code"]=$acc_tax['account_code'];
									$upd["tax_account_name"]=$acc_tax['account_name'];
								}
								$upd["customer_code"] = $cus_acc['account_code'];
								$upd["customer_name"] = $cus_acc['account_name'];
								$upd["account_code"] = $acc['account_code'];
								$upd["account_name"] = $acc['account_name'];
								$upd["customer_remark"] = $customer_remark;
				
								$tmp["branch_id"] = $rItem["branch_id"];
								$tmp["counter_id"] = $rItem["counter_id"];
								$tmp["id"] = $rItem["pos_id"];
								$tmp["date"] = $rItem["date"];
								$tmp_ret=$this->get_pos_items($pos_db, $tmp);
								if($pos_db->sql_numrows($tmp_ret)== 1)
								{
									$upd["is_refund"] = 1;
									$is_refund = 1;
								}
								unset($tmp);
								$pos_db->sql_freeresult($tmp_ret);
								
								$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
								$rIdx++;
								$totalItemAmount += $upd["ItemAmount"];
								$totalAmount += $upd["TotalAmount"];
								$totalGSTAmount += $upd["TaxAmount"];
								unset($upd);
							}
							
			
							$cond[] = "credit_note_no = ".ms($rCN['credit_note_no']);
							$cond[] = "branch_id = ".ms($rCN['branch_id']);
							$cond[] = "counter_id = ".ms($rCN['counter_id']);
							$cond[] = "date = ".ms($rCN['date']);
							$cond[] = "pos_id = ".ms($rCN['pos_id']);
							$where1 = "where ".implode(" and ",$cond);
							$this->sql_query($pos_db,"update pos_credit_note set acc_is_exported = 1 ".$where1);
							unset($upd, $return_receipt_no, $account, $rCN,$cond,$where1);
						}
						$pos_db->sql_freeresult($retItem);
						unset($retItem);
					}
				}
				$pos_db->sql_freeresult($retCN);
				unset($retCN);

				$ret1 = $this->get_pos_payment($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1)>0)
				{
					while($rPayment = $this->sql_fetchrow($pos_db, $ret1))
					{	
						if(strtolower($rPayment['type'])!="deposit") 
						{
							if(in_array($rPayment['type'],array_keys($credit_card))){
								$description = 'credit_card';
							}
							else{
								$description = strtolower($rPayment['type']);
							}
		
							if($description=='mix & match total disc' || $description=='discount') continue;
							$acc=$this->accSettings[$description]['account'];
							$cus_acc=$this->accSettings['customer_code']['account'];
							
							if($description=='cash') 
							{
								$rPayment['amount'] -= $rPos['amount_change'];
							}
							
							$upd = array();
							if($is_cn)
							{
								if($description=='cash' && $rPayment['amount'] ==0) continue;
								$type = ($description=='cash')?"debit":"credit";
								$upd['is_credit_notes'] = 1;
								$upd['credit_note_no'] = $credit_note_no;
								if($is_refund){
									$upd['is_refund'] = 1;
									$type = ($description=='cash')?"credit":"debit";
								}
							}
							else
							{
								$type = ($description=='rounding')?"credit":"debit";
							}

							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPayment["branch_id"])) $upd["branch_id"] = $rPayment['branch_id'];
							$upd["counter_id"] = $rPayment['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = $type;
							$upd["acc_type"] = $description;
							$upd["description"] = $rPayment['type'];
							$upd["ItemAmount"] = $rPayment['amount'];
							$upd["TotalAmount"] = $rPayment['amount'];
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd, $sku, $acc, $description, $type);
							$rIdx++;
						}
					}
		
					unset($rPayment);
				}
				$pos_db->sql_freeresult($ret1);
				
				if(isset($rPos['pos_more_info']['deposit']))
				{
					//print_r($rPos['pos_more_info']['deposit']);
					foreach($rPos['pos_more_info']['deposit'] as $deposit)
					{
						if(isset($deposit['gst_info'])) $deposit['gst_info'] = unserialize($deposit['gst_info']);
		
						$acc = $this->accSettings['deposit']['account'];
						$cus_acc = $this->accSettings['customer_code']['account'];

						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["ref_no"] = $receipt_no;
						$upd["doc_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "debit";
						$upd["acc_type"] = "deposit";
						$upd["description"] = "Deposit";
						$upd["qty"] = 1;
						$upd["unit_price"] = round($deposit['amount'], 2);
						if(isset($deposit['gst_info']['code']) && $deposit['gst_info']['rate']!=""){
							$upd["tax_code"] = $deposit['gst_info']['code'];
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["tax_rate"] = $deposit['gst_info']['rate'];
							$upd["ItemAmount"] = ($deposit['amount']-$deposit['gst_amount']);
							$upd["TaxAmount"] = $deposit['gst_amount'];
							$upd["TotalAmount"] = $upd["unit_price"];
						}else{
							$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
		
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd, $acc_tax);
						$rIdx++;
					}
				}
				
				$short_over=round($rPos['amount_tender']-$rPos['amount']-$rPos['amount_change']-$rPos['service_charges'],2);
				if($short_over!=0){				
					$cus_acc=$this->accSettings['customer_code']['account'];
					$acc_short=isset($this->accSettings['short'])?$this->accSettings['short']['account']:array("account_code"=>"","account_name"=>"");
					$acc_over=isset($this->accSettings['over'])?$this->accSettings['over']['account']:array("account_code"=>"","account_name"=>"");
		
					$acc=($short_over>0)?$acc_over:$acc_short;
		
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					if(isset($rPos["counter_id"])) $upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["ref_no"] = $receipt_no;
					$upd["doc_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'credit';
					$upd["acc_type"] = ($short_over>0)?"over":"short";
					$upd["description"] = ($short_over>0)?"Over":"Short";
					$upd["ItemAmount"] = $short_over;
					$upd["TotalAmount"] = $short_over;
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
	
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $acc, $description, $type);
					$rIdx++;
				}
				
				if(isset($rPos['deposit']) && $rPos['deposit']){
					$ret1 = $this->get_pos_deposit($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$acc=$this->accSettings['deposit']['account'];
							$cus_acc=$this->accSettings['customer_code']['account'];
							$rItem['gst_info']=unserialize($rItem['gst_info']);
							
							$upd = array();
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = "credit";
							$upd["acc_type"] = "deposit";
							$upd["description"] = "Deposit";
							$upd["qty"] = 1;
							$upd["unit_price"] = round($rItem['deposit_amount'], 2);
							if(isset($rItem['gst_info']['code']) && $rItem['gst_info']['rate']!=""){
								$upd["tax_code"] = $rItem['gst_info']['code'];
								$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
								$upd["tax_rate"] = $rItem['gst_info']['rate'];
								$upd["ItemAmount"] = $rItem['deposit_amount']-$rItem['gst_amount'];
								$upd["TaxAmount"] = $rItem['gst_amount'];
								$upd["TotalAmount"] = $upd["unit_price"];
							}else{
								$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
							}
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
			
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
						
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd, $acc_tax);
							$rIdx++;
						}
					}
					$pos_db->sql_freeresult($ret1);
				}
				else{
					$ret1=$this->get_pos_items($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0)
					{
						while($rItem = $this->sql_fetchrow($pos_db, $ret1))
						{
							$has_credit_note = ($rItem['qty']<0 && $rPos['is_gst'])?1:0;	
							if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);
							$discount=$rItem['discount']+$rItem['discount2'];
							
							if($has_credit_note)
							{
								continue;
								if(isset($this->accSettings['sales_return']))
									$acc=$this->accSettings['sales_return']['account'];
								else
									$acc=$this->accSettings['sales']['account'];
							}
							else
								$acc=$this->accSettings['sales']['account'];
							$cus_acc=$this->accSettings['customer_code']['account'];
			
							$upd = array();
							$upd['is_credit_notes'] = $has_credit_note;
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							if($has_credit_note)
							{
								$retGoodsReturn = $this->get_goods_return($pos_db,$where,$rItem);
								/*$retCN = $this->get_credit_note($pos_db,$where,false,false,$rItem);
								if($pos_db->sql_numrows($retCN)>0){
									$rCNItem = $this->sql_fetchrow($pos_db, $retCN);
									$upd['credit_note_no'] = $rCNItem['credit_note_ref_no'];
									$upd['return_receipt_no'] = $rCNItem['return_receipt_no'];
									$upd["type"] = "debit";
									$upd["acc_type"] = "sales return";
								}
								$pos_db->sql_freeresult($retCN);*/
							}
							else{
								$upd["type"] = "credit";
								$upd["acc_type"] = "sales";
							}
							$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd["arms_code"] = $sku['arms_code'];
							$upd["qty"] = $rItem['qty'];
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$tax_amount=round($rItem['tax_amount'],2);
								$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
								$upd["tax_rate"] = $rItem['tax_rate'];
								$upd["ItemAmount"] = $rItem['before_tax_price'];
								$upd["TaxAmount"] = $tax_amount;
								$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
							}else{
								$upd["unit_price"] = $rItem['price']/$rItem['qty'];
								$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
							}
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							$upd["customer_remark"] = $customer_remark;
			
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							$rIdx++;
							
						}
						unset($rItem, $upd, $sku, $discount, $tax_amount, $acc_tax);
					}
					$pos_db->sql_freeresult($ret1);
				}

				if(isset($rPos['service_charges']) && $rPos['service_charges']>0){
					$acc=$this->accSettings['service_charge']['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["ref_no"] = $receipt_no;
					$upd["doc_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "service_charge";
					$upd["description"] = "Service Charge";
					if(isset($rPos['pos_more_info']['service_charges']['sc_gst_detail'])){
						$upd["unit_price"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["tax_code"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['code'];
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						$upd["tax_rate"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['rate'];
						$upd["ItemAmount"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["TaxAmount"] = $rPos['service_charges_gst_amt'];
						$upd["TotalAmount"] = $rPos['service_charges'];
					}
					else{
						$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
						$upd["unit_price"] = $rPos['service_charges'];
						$upd["ItemAmount"] = $upd["TotalAmount"] = $rPos['service_charges'];
					}
		
					if(isset($this->accSettings[$upd["tax_code"]])){
						$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
						$upd["tax_account_code"]=$acc_tax['account_code'];
						$upd["tax_account_name"]=$acc_tax['account_name'];
					}
		
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
		
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $discount, $tax_amount, $acc_tax, $acc);
					$rIdx++;
				}
					
				$cond[] = "branch_id = ".ms($rPos['branch_id']);
				$cond[] = "counter_id = ".ms($rPos['counter_id']);
				$cond[] = "id = ".ms($rPos['id']);
				$cond[] = "date = ".ms($rPos['date']);				
				$where1 = implode(" and ",$cond);
				$this->sql_query($pos_db,"update pos set acc_is_exported = 1 where ".$where1);				
				unset($cond);
				//unset($where);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
		
		//DO Cash Sales
		$ret = $this->get_do($pos_db,$where);
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret))
			{
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);
				$orgDoDate = $rPos['do_date'];
				$rPos['do_date'] = strtotime($rPos['do_date']);
				$rPos['open_info'] = unserialize($rPos['open_info']);

				$receipt_no = $rPos['inv_no'];
				$receipt_ref_no = $rPos['do_no'];
				$posDate = date("Y-m-d",$rPos['do_date']);
				$posYM = date("Y-m-01",$rPos['do_date']);
				$customer_remark=$rPos['open_info']['name'];

				$ret1=$this->get_do_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						$acc=$this->accSettings['sales']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				
				$pos_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						$acc=$this->accSettings['sales']['account'];
						$cus_acc=$this->accSettings['customer_code']['account'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				$pos_db->sql_freeresult($ret1);
				
				if($rPos['total_inv_amt'] > 0){
					$acc=$this->accSettings['cash']['account'];
					$cus_acc=$this->accSettings['customer_code']['account'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'debit';
					$upd["acc_type"] = 'cash';
					$upd["description"] = 'CASH';
					$upd["ItemAmount"] = $rPos['total_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];

					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $acc,$cond,$where1);
					$rIdx++;
				}

				if($rPos['total_round_inv_amt'] != 0)
				{
					$acc = $this->accSettings["rounding"]['account'];
					$cus_acc = $this->accSettings['customer_code']['account'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "rounding";
					$upd["description"] = "";
					$upd["ItemAmount"] = $rPos['total_round_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_round_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
				
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $sku, $acc, $type,$cond,$where1);
					$rIdx++;
				}
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);	
		
		//Credit Notes 
		$org_where = $where;
		//$where[] = "do.do_type = ".ms("open");
		$ret = $this->get_credit_note($pos_db, $where, false,true);
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rCN = $this->sql_fetchrow($pos_db, $ret))
			{					
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings'] = $this->accSettings;
				load_setting($accountings,$FormatType,$rCN['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);
				
				$credit_note_no = $rCN['cn_no'];
				
				$customer = $this->accSettings['customer_code']['account'];
				$terms = $this->accSettings['credit_term']['account'];
				$ret1 = $this->get_credit_note_items($pos_db, $rCN ,true);
				if($pos_db->sql_numrows($ret1) > 0)
				{
					$is_exported = false;
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($rItem["do_type"] != 'open'){
							continue;
						}
						$is_exported = true;
						$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
						
						//To get invoice_no
						$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
						if($pos_db->sql_numrows($retDo)>0)						
						{
							$rInv = $pos_db->sql_fetchrow($retDo);
							$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);	
							$customer_remark = $debtor['description'];
						}
						else{
							$debtor = array(
								'code'=>$customer['account_code'],
								'description'=>$customer['account_name'],
								'terms'=>$terms['account_code'],
								'currency_code'=>$config["arms_currency"]["code"]);
						}
						$return_receipt_no = $rItem['inv_no'];
						$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$rItem['tax_code'] = $rItem['gst_code'];
						$rItem['tax_rate'] = $rItem['gst_rate'];
						$rItem['tax_amount'] = $rItem['line_gst_amt2'];
						$rItem['before_tax_price'] = $rItem['line_gross_amt2'];				
						
						$upd['tablename'] = "do_cn";
						$upd['branch_id'] = $rCN['branch_id'];
						$upd['id'] = $i;
						$upd["batchno"] = $this->get_batchno($rCN['cn_date']);
						$upd['pos_date'] = $rCN['cn_date'];
						$upd['ym'] = date("Y-m-01",strtotime($rCN['cn_date']));
						$upd["type"] = "debit";
						$upd["acc_type"] = "sales return";
						$upd['return_receipt_no'] = $return_receipt_no;
						$upd['doc_no'] = $upd['credit_note_no'] = $credit_note_no;
						$upd['description'] = $sku['sku_desc'];
						$upd['sku_cat_desc'] = $sku['category_desc'];
						$upd['arms_code'] = $sku['arms_code'];
						$upd['uom'] = "Unit";
						$upd['qty'] = $rItem['row_qty'];
				
						if(trim($rItem['gst_id'])!=""){
							$upd["tax_code"] = $rItem['tax_code'];
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["tax_rate"] = $rItem['tax_rate'];
						}
						else
						{
							$upd["second_tax_code"] = $upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["tax_rate"] = 0;
						}
								
						$upd["ItemAmount"] = $rItem['before_tax_price'];
						$upd["TaxAmount"] = $rItem['tax_amount'];
						$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
						
						if(isset($this->accSettings['sales_return']))
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account']['account_code'];
							$upd["account_name"] = $this->accSettings['sales_return']['account']['account_name'];
						}
						elseif(isset($debtor['account_receivable_code']))
						{
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debotr['account_receivable_name'];
						}
						else
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account']['account_code'];
							$upd["account_name"] = $this->accSettings['sales']['account']['account_name'];
						}						
						
						if($rItem['exchange_rate']>1){
							$upd["currencyrate"] = $rItem['exchange_rate'];
							$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
							$upd["TaxFAmount"] = $rItem['tax_amount_f'];
							$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
						}
						
						$upd['reason'] = $rCN['remark'];
						$upd['terms'] = $debtor['terms'];
						$upd['currency_code'] = ((isset($debtor['currency_code']) && trim($debtor['currency_code'])!="")?$debtor['currency_code']:$config["arms_currency"]["code"]);
						$upd['customer_code'] = $debtor['code'];
						$upd['customer_name'] = $debtor['description'];

						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"] = $acc_tax['account_code'];
							$upd["tax_account_name"] = $acc_tax['account_name'];
						}
									
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd,$debtor);
						$i++;
					}

					if($is_exported){
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "cn_no = ".ms($rCN['cn_no']);
						$cond[] = "cn_date = ".ms($rCN['cn_date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cnote set acc_is_exported=1 ".$where1);
						unset($cond,$where1);
					}
				}
				$pos_db->sql_freeresult($ret1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
	}
	
	function create_sales_credit_note($tmpSalesDb)
	{
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
										`tablename` varchar(20),
										`batchno` varchar(20),
										`branch_id` integer default 0,
										`counter_id` integer default 0,
										`id` integer,
										`pos_date` date,
										`doc_no` char(20),
										`ref_no` char(30),
										`ym` date,
										`type` char(20),
										`acc_type` char(20),
										`description` char(150),
										`return_receipt_no` char(30),	
										`credit_note_no` char(30),
										`sku_cat_desc` char(150),
										`arms_code` char(20),
										`uom` char(20) default 'UNIT',
										`qty` double,
										`unit_price` double,
										`currency_code` char(10) default '" . $config["arms_currency"]["code"] . "',
										`currencyrate` double default 1,
										`tax_code` char(5),
										`tax_rate` double,
										`ItemAmount` double,
										`TaxAmount` double,
										`TotalAmount` double,
										`ItemFAmount` double,
										`TaxFAmount` double,
										`TotalFAmount` double,
										`reason` text,
										`terms` char(20),
										`customer_code` char(150),
										`customer_name` char(150),
										`customer_brn` char(150),
										`customer_gst_no` char(150),
										`address` text,
										`account_code` char(100),
										`account_name` char(100),
										`tax_account_code` char(100),
										`tax_account_name` char(100),
										`reason_code` char(100),
										`reason_description` text,
										`cancelled` char(1) default 'F',
										`transferable` char(1) default 'T',
										`customer_remark` text,
										`cn_remark` text,
										`is_credit_notes` char(1) default 0,
										primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_sales_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config, $appCore;
		$credit_card = $this->credit_cards_type();
		
		$ret = $this->get_do($pos_db,$where,array('transfer','credit_sales'));
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret))
			{
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings']=$this->accSettings;
				load_setting($accountings,$FormatType,$rPos['branch_id']);
				$this->accSettings = $accountings[$FormatType]['settings'];
				unset($accountings);
				$orgDoDate = $rPos['do_date'];
				$rPos['do_date'] = strtotime($rPos['do_date']);
				$rPos['open_info'] = unserialize($rPos['open_info']);

				$receipt_no = $rPos['inv_no'];
				$receipt_ref_no = $rPos['do_no'];
				$posDate = date("Y-m-d",$rPos['do_date']);
				$posYM = date("Y-m-01",$rPos['do_date']);
				$customer_remark=$rPos['open_info']['name'];
				if($rPos['do_type']=='transfer'){
					$branch=$this->get_branch($pos_db,$rPos['do_branch_id']);
					$debtor=array('code'=>$branch['account_code_debtor'],
								'description'=>$branch['description'],
								'address'=>$branch['address'],
								'terms'=>$branch['con_terms'],
								'account_receivable_code'=>$branch['account_receivable_code'],
								'account_receivable_name'=>$branch['account_receivable_name'],
								'brn'=>$branch['company_no'],
								'gst_register_no'=>$branch['gst_register_no']);
				}
				else{
					$debtor = $this->get_debtor($pos_db,$rPos['debtor_id']);
					$customer_remark = $debtor['description'];
					$customer_brn = $debtor['company_no'];
				}
				
				$ret1=$this->get_do_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
					
						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
					
								$upd["account_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account']['account_name'];
							}
						}

						//$upd["account_code"] = $acc['account_code'];
						//$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				
				$pos_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
					
						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
					
								$upd["account_code"] = $this->accSettings['credit_sales']['account']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account']['account_name'];
							}
						}

						//$upd["account_code"] = $acc['account_code'];
						//$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				
				$pos_db->sql_freeresult($ret1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);	
		
		//Credit Notes 
		$org_where = $where;
		//$where[] = "do.do_type in ('".implode("','",array('transfer','credit_sales'))."')";
		$ret = $this->get_credit_note($pos_db, $where, false,true);
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rCN = $this->sql_fetchrow($pos_db, $ret))
			{					
				$FormatType=$this->get_name();
				$accountings[$FormatType]['settings'] = $this->accSettings;
				load_setting($accountings,$FormatType,$rCN['branch_id']);
				$this->accSettings=$accountings[$FormatType]['settings'];
				unset($accountings);
				
				$credit_note_no = $rCN['cn_no'];
				
				//$customer = $this->accSettings['customer_code']['account'];
				//$terms = $this->accSettings['credit_term']['account'];
				
				//To get invoice_no
				
				$ret1 = $this->get_credit_note_items($pos_db, $rCN ,true);
				if($pos_db->sql_numrows($ret1) > 0)
				{
					$is_exported = false;
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($rItem["do_type"] == 'open'){
							continue;
						}
						$is_exported = true;
						$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
						if($pos_db->sql_numrows($retDo)>0)						
						{
							$rInv = $pos_db->sql_fetchrow($retDo);
							if($rInv['do_type']=='transfer')
							{
								$branch = $this->get_branch($pos_db,$rInv['do_branch_id']);
								$debtor=array('code'=>$branch['account_code_debtor'],
											'description'=>$branch['description'],
											'address'=>$branch['address'],
											'terms'=>$branch['con_terms'],
											'account_receivable_code'=>$branch['account_receivable_code'],
											'account_receivable_name'=>$branch['account_receivable_name'],
											'brn'=>$branch['company_no'],
											'gst_register_no'=>$branch['gst_register_no']);
							}
							else
							{
								$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);		
							}
							$customer_remark = $debtor['description'];
						}
						else{
							$debtor = array(
								'code'=>$customer['account_code'],
								'description'=>$customer['account_name'],
								'terms'=>$terms['account_code'],
								'currency_code'=>$config["arms_currency"]["code"]);
						}
						
						$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
						$return_receipt_no = $rItem['inv_no'];
						$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$rItem['tax_code'] = $rItem['gst_code'];
						$rItem['tax_rate'] = $rItem['gst_rate'];
						$rItem['tax_amount'] = $rItem['line_gst_amt2'];
						$rItem['before_tax_price'] = $rItem['line_gross_amt2'];				
						
						$upd['tablename'] = "do_cn";
						$upd['branch_id'] = $rCN['branch_id'];
						$upd['id'] = $i;
						$upd["batchno"] = $this->get_batchno($rCN['cn_date']);
						$upd['pos_date'] = $rCN['cn_date'];
						$upd['ym'] = date("Y-m-01",strtotime($rCN['cn_date']));
						$upd["type"] = "debit";
						$upd["acc_type"] = "sales return";
						$upd['return_receipt_no'] = $return_receipt_no;
						$upd['doc_no'] = $upd['credit_note_no'] = $credit_note_no;
						$upd['description'] = $sku['sku_desc'];
						$upd['sku_cat_desc'] = $sku['category_desc'];
						$upd['arms_code'] = $sku['arms_code'];
						$upd['uom'] = "Unit";
						$upd['qty'] = $rItem['row_qty'];
				
						if(trim($rItem['gst_id'])!=""){
							$upd["tax_code"] = $rItem['tax_code'];
							$upd["tax_rate"] = $rItem['tax_rate'];
						}
						else
						{
							$upd["tax_code"] = $appCore->gstManager->getTextNR();
							$upd["tax_rate"] = 0;
						}
								
						$upd["ItemAmount"] = $rItem['before_tax_price'];
						$upd["TaxAmount"] = $rItem['tax_amount'];
						$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
						
						if(isset($this->accSettings['sales_return']))
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account']['account_code'];
							$upd["account_name"] = $this->accSettings['sales_return']['account']['account_name'];
						}
						elseif(isset($debtor['account_receivable_code']))
						{
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debotr['account_receivable_name'];
						}
						else
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account']['account_code'];
							$upd["account_name"] = $this->accSettings['sales']['account']['account_name'];
						}						
						
						if($rItem['exchange_rate']>1){
							$upd["currencyrate"] = $rItem['exchange_rate'];
							$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
							$upd["TaxFAmount"] = $rItem['tax_amount_f'];
							$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
						}
						
						$upd['reason'] = $rCN['remark'];
						$upd['terms'] = $debtor['terms'];
						$upd['currency_code'] = ((isset($debtor['currency_code']) && trim($debtor['currency_code'])!="")?$debtor['currency_code']:$config["arms_currency"]["code"]);
						$upd['customer_code'] = $debtor['code'];
						$upd['customer_name'] = $debtor['description'];

						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
							$upd["tax_account_code"] = $acc_tax['account_code'];
							$upd["tax_account_name"] = $acc_tax['account_name'];
						}
									
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd,$debtor);
						$i++;
					}	
					
					if($is_exported){
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "cn_no = ".ms($rCN['cn_no']);
						$cond[] = "cn_date = ".ms($rCN['cn_date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cnote set acc_is_exported=1 ".$where1);
						unset($cond,$where1);
					}
				}
				$pos_db->sql_freeresult($ret1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
	}
	
	private function insert_debit_notes($tmpSalesDb, $pos_db, $sku_db, $rDN, $rItem)
	{
		global $LANG, $config, $appCore;

		$i = $this->get_max_id($tmpSalesDb, $this->tmpTable);

		$FormatType = $this->get_name();
		$accountings[$FormatType]['settings'] = $this->accSettings;
		load_setting($accountings, $FormatType, $rDN['branch_id']);
		$this->accSettings = $accountings[$FormatType]['settings'];
		unset($accountings, $FormatType);

		$sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

		if($config['consignment_modules']){
			$rDN['reason'] = $rDN['remark'];
			$branch = $this->get_branch($pos_db, $rDN['to_branch_id']);

			$debtor = array(
				'customer_code'=>$branch['account_code'],
				'customer_name'=>$branch['description'],
				'terms'=>$branch['con_terms'],
				'currency_code'=>$branch['currency_code']);

			$return_receipt_no = $rDN['inv_no'];
			$rItem['uom'] = $rItem['uom_description'];
			$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
			$rItem['tax_code'] = $rItem['gst_code'];
			$rItem['tax_rate'] = $rItem['gst_rate'];

			if($rItem['tax_code'] != "" && $rItem['tax_rate'] != ""){
				$rItem['tax_amount'] = $rItem['item_gst2'];
				$rItem['before_tax_price'] = $rItem['item_amt2'];

				if($rDN['exchange_rate']>1){
					$rItem['exchange_rate'] = $rDN['exchange_rate'];
					$rItem['tax_amount_f'] = $rItem['item_foreign_gst2'];
					$rItem['before_tax_price_f'] = $rItem['item_foreign_amt2'];
				}
			}
			else{
				if($rDN['discount'] != ""){
					$total_discount = $rDN['sub_total_amt'];
					$this->cal_discount($total_discount, $rDN['discount']);
					$total_discount_per = $total_discount / $rDN['sub_total_amt'];
				}
				else{
					$total_discount = 0;
					$total_discount_per = 1;
				}

				$rItem['tax_amount'] = 0;
				$rItem['before_tax_price'] = ($rItem['cost_price'] * $rItem['qty']) - $rItem['discount_amt'];

				$rItem['before_tax_price'] = $rItem['before_tax_price'] * $total_discount_per;

				if($rDN['exchange_rate']>1){
					$rItem['exchange_rate'] = $rDN['exchange_rate'];
					$rItem['tax_amount_f'] = 0;
					$rItem['before_tax_price_f'] = ($rItem['foreign_cost_price'] * $rItem['qty']) - $rItem['foreign_discount_amt'];;

					$rItem['before_tax_price_f'] = $rItem['before_tax_price_f'] * $total_discount_per;
				}

				unset($total_discount, $total_discount_per);
			}

			unset($branch);
		}
		else{
			$vendor = $this->get_vendor($pos_db,$rDN);

			$debtor = array(
					'customer_code' => $vendor['code'],
					'customer_name' => $vendor['description'],
					'terms' => $vendor['term'],
					'currency_code'=>$config["arms_currency"]["code"]);

			$rDN['date'] = $rDN['dn_date'];
			$rDN['reason'] = $rDN['remark'];

			$return_receipt_no = $rDN['dn_no'];
			$rItem['uom'] = "Unit";
			$rItem['tax_code'] = $rItem['gst_code'];
			$rItem['tax_rate'] = $rItem['gst_rate'];

			$rItem['tax_amount'] = $rItem['item_gst_amount'];
			$rItem['before_tax_price'] = $rItem['item_gross_amount'];

			unset($vendor);
		}

		if(isset($rDN['branch_id'])) $rDN['branch_id'] = $rDN['branch_id'];
		if(isset($rDN['counter_id'])) $rDN['counter_id'] = $rDN['counter_id'];
		$upd['id'] = $i;
		$upd["batchno"] = $this->get_batchno($rDN['date']);
		$upd['date'] = $rDN['date'];
		$upd['ym'] = date("Y-m-01",strtotime($rDN['date']));
		$upd['invoice_no'] = $return_receipt_no;
		$upd['sku_description'] = $sku['sku_desc'];
		$upd['sku_cat_desc'] = $sku['category_desc'];
		$upd['arms_code'] = $sku['arms_code'];
		$upd['uom'] = $rItem['uom'];
		$upd['qty'] = $rItem['qty'];

		if($rItem['tax_code'] && $rItem['tax_rate']!=""){
			$upd["tax_code"] = $rItem['tax_code'];
			$upd["tax_rate"] = $rItem['tax_rate'];
		}else{
			$upd["tax_code"] = $appCore->gstManager->getTextNR();
			$upd["tax_rate"] = 0;
		}

		$upd["ItemAmount"] = $rItem['before_tax_price'];
		$upd["TaxAmount"] = $rItem['tax_amount'];
		$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
		$upd["reason"] = $rDN['reason'];
		$upd["reason_code"] = "WIDV"; //Wrong Item Delivered

		if($rItem['exchange_rate']>1){
			$upd["currencyrate"] = $rItem['exchange_rate'];
			$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
			$upd["TaxFAmount"] = $rItem['tax_amount_f'];
			$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
		}

		$upd['terms'] = $debtor['terms'];
		$upd['currency_code'] = $debtor['currency_code'];
		$upd['customer_code'] = $debtor['customer_code'];
		$upd['customer_name'] = $debtor['customer_name'];

		if(isset($this->accSettings['purchase_return'])){
			$upd["account_code"] = $this->accSettings['purchase_return']['account']['account_code'];
			$upd["account_name"] = $this->accSettings['purchase_return']['account']['account_name'];
		}
		elseif(isset($this->accSettings['purchase'])){
			$upd["account_code"] = $this->accSettings['purchase']['account']['account_code'];
			$upd["account_name"] = $this->accSettings['purchase']['account']['account_name'];
		}


		if(isset($this->accSettings[$upd["tax_code"]])){
			$acc_tax = $this->accSettings[$upd["tax_code"]]['account'];
			$upd["tax_account_code"] = $acc_tax['account_code'];
			$upd["tax_account_name"] = $acc_tax['account_name'];
		}

		$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
		unset($upd, $branch, $sku, $return_receipt_no, $debit_note_no, $account, $acc_tax, $rDN,$rItem);
	}

	protected function sql_query($con,$sql)
	{
		$this->show_debug($sql);

		$ret=$con->sql_query($sql,false,false);

		$err=$con->sql_error($ret);

		if($err['code']!=0){
			echo '<script>parent.show_error("'.str_replace(array("\t","\n","\r"),"",$err['message']).'")</script>';die();
		}

		return $ret;
	}

	protected function sql_fetchrow($con,$ret){
	  	return $con->sql_fetchassoc($ret);
	}

	protected function selling_price_currency_format($str){
		/*if(function_exists("selling_price_currency_format")){
			return selling_price_currency_format($str);
		}*/
		return number_format($str,2,".","");
	}

	protected function round_curreny($str){
		if(function_exists("round_curreny")){
			return round_curreny($str);
		}
		return round($str,2);
	}

	protected function credit_cards_type(){
		if(function_exists("credit_cards_type")){
			return credit_cards_type();
		}

		global $config,$pos_config;

		$cc=$pos_config['issuer_identifier'];

		$credit_card = array();
		foreach($cc as $c){
			$credit_card[$c[0]] = 1;
		}
		$credit_card['Others'] = 1;
		return $credit_card;
	}

	protected function replace_separator($str,$separator,$file_format){
		$regular_expression = "/[".$separator."]/";
		if(preg_match($regular_expression,$str)){
			$replace_with = ($file_format=="csv"?"\\".$separator:" ");
			return preg_replace($regular_expression,$replace_with,$str);
		}
		return $str;
	}

	protected function create_batch_no($date,$batch_no){
		global $LANG,$config;

		$receipt = sprintf("%04s",$batch_no);

		return sprintf($LANG['SET_RECEIPT_NO_PREFIX'],$date,$receipt);
	}

	protected function monthly_summary_date_checking($date,$dateTo){
		$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($date))));
		if(strtotime($date) > strtotime($dateTo)){
			$date = $dateTo;
		}

		return $date;
	}

	protected function set_date($date_format,$date){
		return date($date_format,strtotime($date));
	}

	protected function get_company_info($branch_id)
	{
        global $con,$sessioninfo;

        $q1 = $this->sql_query($con, "select * from branch where id=".mi($branch_id));
        $ret = $this->sql_fetchrow($con, $q1);
        $con->sql_freeresult($q1);
        $gst = array();
        $gst['gst_company_name'] = $ret['description'];
		$gst['gst_company_business_register_number'] = $ret['company_no'];
		$gst['gst_register_no'] = $ret['gst_register_no'];

        return $gst;
	}
	
	protected function calculate_financial_year_period($start_date,$current_date)
	{		
		$ts1 = strtotime($start_date);
		$ts2 = strtotime($current_date);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);


		$diff_period = intval(((($year2 - $year1) * 12) + ($month2 - $month1)) + 1);		
		return $diff_period;
	}

	private function cal_discount(&$new_amt,$discount="")
	{
		$discount_arr = explode("+", $discount);

		if($discount_arr[0]){
			$new_amt = $new_amt - round(($new_amt*($discount_arr[0]/100)),2);
		}

		if($discount_arr[1])
		{
			$new_amt = $new_amt - round(($new_amt*($discount_arr[1]/100)),2);
		}
		$new_amt=round($new_amt,2);
	}

	function show_debug($msg){
		if($this->debug){
		  if(is_string($msg)) $msg=trim(preg_replace('/\t+/', '', $msg));
			file_put_contents($this->debug_file,"<pre>".$msg."</pre>",FILE_APPEND);
		}
	}

	private function get_batchno($date){

		$batchno="";

		foreach($this->batchno as $batch){
			if(strtotime($date)>=strtotime($batch['date_from']) && strtotime($date)<=strtotime($batch['date_to'])){

				$batchno=$batch['batchno'];
				break;
			}
		}

		return $batchno;
	}

	private function get_max_id($db, $table){
		$ret = $this->sql_query($db, "select max(id) as max from ".$table);
		$max = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		return ($max['max']+1);
	}

	private function get_sku($db=null, $sku_item_id){
		$sql = "select si.sku_item_code as arms_code, si.receipt_description as sku_desc, c.description as category_desc
						from sku_items si
						join sku s on si.sku_id = s.id
						join category c on s.category_id = c.id
						where si.id = ".mi($sku_item_id);

		$ret = $this->sql_query($db, $sql);
		$sku = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		return $sku;
	}

	private function get_pos($pos_db,$where=array()){
		global $config;

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms(date("Y-m-d",strtotime($this->dateFrom)));
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms(date("Y-m-d",strtotime($this->dateTo)));

		$where[] = "cancel_status = 0";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from pos p $cond order by pos_time, branch_id, receipt_no";
	
		return $this->sql_query($pos_db, $sql);
	}

    private function get_pos_items($pos_db,$rPos){
        $sql="select * from pos_items
			  where branch_id = ".mi($rPos['branch_id'])."
			  and counter_id = ".mi($rPos['counter_id'])."
			  and pos_id = ".mi($rPos['id'])."
			  and date = ".ms($rPos['date']);

		return $this->sql_query($pos_db, $sql);
    }

	private function get_pos_deposit($pos_db,$rPos){
		$sql = "select * from pos_deposit
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date']);

		return $this->sql_query($pos_db, $sql);
	}

	private function get_pos_deposit_status($pos_db,$rPos){
		$sql="select pd.* from pos_deposit_status pds
			  left join pos_deposit pd on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_pos_id = pd.pos_id and pds.deposit_date = pd.date
			  where pds.branch_id = ".mi($rPos['branch_id'])."
			  and pds.counter_id = ".mi($rPos['counter_id'])."
			  and pds.pos_id = ".mi($rPos['id'])."
			  and pds.date = ".ms($rPos['date'])."
			  and pds.receipt_no = ".ms($rPos['receipt_no']);

		return $this->sql_query($pos_db, $sql);
	}

    private function get_pos_payment($pos_db,$rPos){
	  	$sql = "select * from pos_payment
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date'])."
				and adjust=0";

	  return $this->sql_query($pos_db, $sql);
    }

	private function get_pos_credit_note($pos_db,$rPos){
	  	$sql = "select * from pos_credit_note
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date']);


		return $this->sql_query($pos_db, $sql);
	}

	private function get_membership_redemption($pos_db,$where=array()){
		global $config;

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);

		$where[] = "active = 1";
		$where[] = "verified = 1";
		$where[] = "status = 0";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from membership_redemption p $cond order by date";		
		
		return $this->sql_query($pos_db, $sql);
	}

	private function get_membership_redemption_items($pos_db,$rMem){
		$sql = "select * from membership_redemption_items
				where membership_redemption_id = ".mi($rMem['id'])."
				and branch_id = ".mi($rMem['branch_id']);

		return $this->sql_query($pos_db, $sql);
	}

	private function get_membership($pos_db,$rMem){
		$sql = "select *, branch.code as apply_branch_code, branch.ip as icfile_ip
						   from membership
						   left join branch on membership.apply_branch_id = branch.id
						   where membership.nric=".ms($rMem['nric']);
		$ret=$this->sql_query($pos_db, $sql);
		$membership = $this->sql_fetchrow($pos_db, $ret);
		$pos_db->sql_freeresult($ret);

		return $membership;
	}

	private function get_vendor($vendor_db,$result){
	  	$sql = "select *,description as company_name, term as vendor_terms_code
							  from vendor where id = ".ms($result['vendor_id']);		
		$ret=$this->sql_query($vendor_db, $sql);
		$vendor = $this->sql_fetchrow($vendor_db, $ret);
		$vendor_db->sql_freeresult($ret);

		return $vendor;
	}

	private function get_vendor_by_do($con,$doc_no){
		$do=$this->get_do_by_doc_no($con,$doc_no);

		$branch=$this->get_branch($con,$do['branch_id']);

		return $debtor=array('code'=>$branch['account_code'],
							'description'=>$branch['description'],
							'address'=>$branch['address'],
							'terms'=>$branch['con_terms'],
							'account_payable_code'=>$vendor['account_payable_code'],
							'account_payable_name'=>$vendor['account_payable_name'],
							);
	}

	private function get_grn($grn_db,$where=array()){
		global $config;
		$cond = "";
		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "gi.doc_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "gi.doc_date <= ".ms($this->dateTo);

		$where[] = "p.active = 1";
		$where[] = "p.status = 1";
		$where[]=  "gi.type = ".ms("INVOICE");
		$where[]=  "gn.active = 1";
		$where[]=  "gn.status = 1";
		$where[]=  "gn.approved = 1";
		
		if($where) $cond = "where ".implode(" and ",$where);
		
		$sql = "select p.*,gi.*,gn.*, gi.amount as amount, if(gst.second_tax_code='' or gst.second_tax_code is null, gi.gst_code, gst.second_tax_code) as second_tax_code
				from grr p 
				join grr_items gi on p.id = gi.grr_id and p.branch_id = gi.branch_id
				join grn gn on gn.grr_id = gi.grr_id and gn.branch_id = gi.branch_id
				left join gst on gst.id=gi.gst_id
				$cond order by gi.doc_date";
		return $this->sql_query($grn_db, $sql);
	}

	
	/*private function get_grn_items($grn_db,$result){
	    $sql = "select * from grr_items
				where branch_id = ".mi($result['branch_id'])."
				and grr_id = ".ms($result['id'])."
				and type in ('INVOICE','DO','OTHER')";
		
		return $this->sql_query($grn_db, $sql);
	}*/

    private function get_invoice($db,$result){
		$sql = "select grn.* from grr left join grn on grr.id=grn.grr_id and grr.branch_id=grn.branch_id where grr.id = ".mi($result['id'])." and grr.branch_id=".ms($result['branch_id'])." limit 1";

		$ret=$this->sql_query($db, $sql);
		$invoice = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);


		if($invoice)
			return $invoice;
		else
			return 0;
	}

	private function get_do_by_doc_no($con,$doc_no){
		$sql="select * from do where do_no=".ms($doc_no);

		return $this->sql_query($con, $sql);
	}

	private function get_do($db,$where=array(),$type='open'){

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.do_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.do_date <= ".ms($this->dateTo);
		
		if(is_array($type)){
			 $where[]="do_type in ('".implode("','",$type)."')";
		}
		else $where[]="do_type = ".ms($type);
		$where[]="status = 1";
		$where[]="active = 1";
		$where[]="approved = 1";
		$where[]="checkout = 1";
		$where[]="(inv_no is not null and inv_no != \"\")";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from do p $cond order by do_date";		
		return $this->sql_query($db, $sql);
    }

	private function get_do_items($db,$rDo){
		$sql = "select doi.*, rcv_uom.fraction as fraction
							  from do_items doi
							  left join uom rcv_uom on doi.uom_id = rcv_uom.id
							  where doi.branch_id = ".mi($rDo['branch_id'])."
							  and do_id = ".mi($rDo['id']);
		return $this->sql_query($db, $sql);
	}
	
	private function get_do_open_items($db,$rDo){
		$sql = "select doi.*
							  from do_open_items doi
							  where doi.branch_id = ".mi($rDo['branch_id'])."
							  and do_id = ".mi($rDo['id']);
		return $this->sql_query($db, $sql);
	}

	private function get_ci($db,$where=array(),$type='sales'){
		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "ci_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "ci_date <= ".ms($this->dateTo);
		$where[]="type in ('".$type."')";
		$where[]="status = 1";
		$where[]="active = 1";
		$where[]="approved = 1";
		$where[]='ci_no <> ""';

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from ci $cond order by last_update";

		return $this->sql_query($db, $sql);
	}

	private function get_ci_items($db,$rDo){

		$sql="select coi.*, rcv_uom.fraction as fraction
							  from ci_items coi
							  left join uom rcv_uom on coi.uom_id = rcv_uom.id
							  where coi.branch_id = ".mi($rDo['branch_id'])."
							  and ci_id = ".mi($rDo['id']);

		return $this->sql_query($db, $sql);
	}

	private function get_debtor($db,$debtor_id){
		$ret=$this->sql_query($db,"select * from debtor where id=".mi($debtor_id));
		$debtor = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		if(isset($debtor)){
			$debtor["description"] = preg_replace("~[\r\n]~", " ", $debtor["description"]);	// fix new line error
		}
		
		return $debtor;
	}

	private function get_branch($db,$branch_id=0){
		global $config;

		$ret=$this->sql_query($db, "select * from branch where id=".mi($branch_id));
		$branch = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		if(isset($config['masterfile_branch_region']) && isset($config['masterfile_branch_region'][$branch['region']])){
			$branch['currency_code']=$this->fix_currency($config['masterfile_branch_region'][$branch['region']]['currency']);
		}

		return $branch;
	}

	private function fix_currency($currency_code){
		global $config;
		if(strtolower($currency_code)=="rm"){
			$currency_code=$config["arms_currency"]["code"];
		}

		return $currency_code;
	}

	private function get_goods_return($pos_db,$where=array(),$posItem = array())
	{
		global $config;

		$cond = "";
		$where = array();
		$where[] = "pgr.pos_id = ".$posItem['pos_id'];
		$where[] = "pgr.branch_id = ".$posItem['branch_id'];
		$where[] = "pgr.counter_id = ".$posItem['counter_id'];
		$where[] = "pgr.item_id = ".$posItem['item_id'];
		$cond = "where ".implode(" and ",$where);		
		$sql = "select * from pos_goods_return pgr $cond";		
	}

	//private function get_credit_note($pos_db,$where=array(),$consignment=false,$is_backCN = false,$posItem = array())
	private function get_credit_note($pos_db,$where=array(),$consignment=false,$is_backCN = false,$posItem = array(),$from_update_cn = false,$posInfo = array())
	{
		global $config;

		$cond = "";

		if($consignment){
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);
			$where[] = "p.status = 1";
			$where[] = "p.active = 1";
			$where[] = "p.approved = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from cn p $cond order by p.date";
		}
		elseif($is_backCN)
		{
			$where[] = "p.status = 1";
			$where[] = "p.active = 1";
			$where[] = "p.approved = 1";
			
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.cn_date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.cn_date <= ".ms($this->dateTo);
			$cond = "where ".implode(" and ",$where);
			$sql = "select p.* from cnote p ".$cond;
		}
		else{	
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);
			$where[]="cancel_status = 0";
			if(!$from_update_cn && !$posInfo)
			{
				if(!$skip_filter_qty) $where[]="pi.qty < 0";
				if($posItem)
				{
					$where[] = "pi.pos_id = ".$posItem['pos_id'];
					$where[] = "pi.branch_id = ".$posItem['branch_id'];
					$where[] = "pi.counter_id = ".$posItem['counter_id'];
					$where[] = "pi.item_id = ".$posItem['item_id'];
				}
	
			}
			else{
				if($posInfo)
				{
					$where[] = "pcn.pos_id = ".$posInfo['id'];
					$where[] = "pcn.branch_id = ".$posInfo['branch_id'];
					$where[] = "pcn.counter_id = ".$posInfo['counter_id'];
					$where[] = "pcn.date = ".ms($posInfo['date']);
				}
			}
						
			$cond = "where ".implode(" and ",$where);
			if(!$from_update_cn && !$posInfo)
			{
				$sql = "select * from pos_credit_note pcn
					join pos_goods_return pgr on
					pcn.pos_id = pgr.pos_id and 
					pcn.date=pgr.date and 
					pcn.counter_id = pgr.counter_id and 
					pcn.branch_id = pgr.branch_id
					join pos_items pi on 
					pgr.pos_id = pi.pos_id and 
					pi.item_id = pgr.item_id and 
					pi.counter_id = pgr.counter_id and 
					pi.branch_id = pgr.branch_id and 
					pi.date = pgr.date
					join pos p on 
					p.id = pi.pos_id and 
					p.counter_id = pi.counter_id and 
					p.branch_id = pi.branch_id and 
					p.date = pi.date
					$cond
					order by pcn.date, pcn.credit_note_no";
			//print $sql."\n";
			}
			else{
				$sql = "select pcn.credit_note_no, 
							   pcn.credit_note_ref_no, 
							   pcn.branch_id,
							   pcn.counter_id,
							   pcn.pos_id,
							   pcn.date,
							   pcn.company_name,
							   pcn.address,
							   pcn.gst_register_number,
							   pcn.customer_infor,
							   pcn.item_infor,
							   p.start_time,
							   p.end_time,
							   p.pos_time 
							   from pos_credit_note pcn
				join pos p on 
				p.id = pcn.pos_id and 
				p.counter_id = pcn.counter_id and 
				p.branch_id = pcn.branch_id and 
				p.date = pcn.date
				$cond
				group by pcn.branch_id,pcn.counter_id,pcn.pos_id,pcn.date, pcn.credit_note_no
				order by pcn.date, pcn.credit_note_no";
			}
		}
	
		return $this->sql_query($pos_db, $sql);
	}
	
	private function get_credit_note_pos_items($pos_db,$where,$posItem,$itemInfo = array())
	{
		$where = array();
		$where[] = "pgr.pos_id = ".ms($posItem['pos_id']);
		$where[] = "pgr.branch_id = ".ms($posItem['branch_id']);
		$where[] = "pgr.counter_id = ".ms($posItem['counter_id']);
		$where[] = "pgr.date = ".ms($posItem['date']);
		if($itemInfo)
		{
			$where[] = "pgr.return_pos_id = ".ms($itemInfo['pos_id']);
			if(isset($itemInfo['branch_id']))
				$where[] = "pgr.return_branch_id = ".ms($itemInfo['branch_id']);
			else
				$where[] = "pgr.return_branch_id = ".ms($posItem['branch_id']);
			$where[] = "pgr.return_counter_id = ".ms($itemInfo['counter_id']);
			$where[] = "pgr.return_date = ".ms($itemInfo['date']);
			$where[] = "pgr.return_item_id = ".ms($itemInfo['item_id']);
		}
		
		$cond = "where ".implode(" and ",$where);			
		$sql = "select * from pos_goods_return pgr 
					join pos_items pi on pgr.pos_id = pi.pos_id and pi.item_id = pgr.item_id and pi.counter_id = pgr.counter_id and pi.branch_id = pgr.branch_id and pi.date = pgr.date
					$cond";	
					
		return $this->sql_query($pos_db, $sql);
	}

	private function get_credit_note_items($db,$rCn=array(),$is_backCN = false)
	{
		if($is_backCN)
		{
			if($rCn['return_type'] == "multiple_inv"){
				$str_col = " cni.return_inv_no as 'inv_no'";
				$str_join = "left join do on do.id = cni.return_do_id and do.branch_id = c.branch_id";
			}else{
				$str_col = " c.inv_no";
				$str_join = "left join do on do.id = c.do_id and do.branch_id = c.branch_id";
			}
			
			$sql = "select cni.*,rcv_uom.fraction as fraction, c.return_type, c.do_id, c.inv_date, do.do_type, $str_col from cnote_items cni
					left join uom rcv_uom on cni.uom_id = rcv_uom.id
					left join cnote c on c.branch_id = cni.branch_id and c.id = cni.cnote_id
					$str_join
					where cni.branch_id = ".ms($rCn['branch_id']). " and cni.cnote_id = ".ms($rCn['id']);
		}
		else{
			$sql="select cni.*,rcv_uom.fraction as fraction
					from cn_items cni
					left join uom rcv_uom on cni.uom_id = rcv_uom.id		
					where branch_id=".ms($rCn['branch_id'])." and cn_id=".mi($rCn['id']);
		}
		
			
		return $this->sql_query($db, $sql);
	}
	
	private function get_debit_note($pos_db,$where=array(),$consignment=false){
		global $config;

		$cond = "";

		if($consignment){
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);

			$where[]="p.status = 1";
			$where[]="p.active = 1";
			$where[]="p.approved = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from dn p $cond order by p.date";
		}
		else{
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.dn_date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.dn_date <= ".ms($this->dateTo);

			$where[]="p.ref_table in('grn','gra')";
			$where[]="p.active = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from dnote p $cond order by p.dn_date";		
		}

		return $this->sql_query($pos_db, $sql);
	}

	private function get_debit_note_items($db,$rDn=array(),$consignment=false)
	{
		if($consignment){
			$sql = "select *, rcv_uom.fraction as fraction, rcv_uom.description as uom
					from dn_items di
					left join uom rcv_uom on di.uom_id = rcv_uom.id
					where di.dn_id=".mi($rDn['id'])."
					and di.branch_id=".mi($rDn['branch_id']);
		}
		else{
			$sql = "select *
					from dnote_items
					where branch_id = ".mi($rDn['branch_id'])."
					and dnote_id = ".mi($rDn['id']);			
		}

		return $this->sql_query($db, $sql);
	}
	
	private function get_cn_inv_infor($db,$rCN)
	{
		if($rCN["return_type"] == "multiple_inv"){
			$filter[] = "id = " . ms($rCN['return_do_id']);
			$filter[] = "do_date = " . ms($rCN['return_inv_date']);
		}else{
			$filter[] = "id = ". ms($rCN['do_id']);
			$filter[] = "do_date = ". ms($rCN['inv_date']);
		}
		
		$str_filter = implode(" and ",$filter);
		$sql = "select * from do
				where branch_id = ".ms($rCN['branch_id']) . "
				and inv_no = " . ms($rCN['inv_no']) . " and " . $str_filter;
		return $this->sql_query($db, $sql);		
	}
	
	function get_second_tax_code_list(){
		global $con;
		$sql = "select code, second_tax_code from gst";
		$ret = $this->sql_query($con, $sql);
		$list = array();
		while($r = $this->sql_fetchrow($con,$ret)){
			$list[$r["code"]] = $r["second_tax_code"];
		}
		$con->sql_freeresult($ret);
		
		return $list;
	}
}

?>
