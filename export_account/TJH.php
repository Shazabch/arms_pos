<?php
/*
2016-03-08 5:30pm Kee Kee
- Cash Sales cannot contain goods return amount

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php

2017-01-25 14:58 Qiu Ying
- Bug fixed on Cash Sales should show all transaction include receipt item with 0 amount

2017-07-27 13:47 Qiu Ying
- Bug fixed on tax rate cannot be shown on account receivable

9/20/2018 1:20 PM Andy
- Enhanced to show Tax Code "NR" as empty if company is not gst registered.
*/
include_once("ExportModule.php");
class TJH extends ExportModule
{
	const NAME="TJH Accounting";
	static $export_with_sdk=false;
	static $show_all_column=true;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array("CustomerCode"=>0, "VoucherNo"=>0, "Reference"=>0, "Date"=>0,
								  "Description"=>0, "Category"=>0, "SalesmanCode"=>0, "Tax"=>0,
								  "Dept"=>0, "Job"=>0, "ItemAmount"=>1, "TaxAmount"=>1,
								  "TotalAmount"=>1, "Index"=>0, "TaxRate"=>0, "PaymentType"=>0,
								  "PaymentAmt"=>1, "PaymenType2"=>0, "PaymentAmt2"=>1, "PaymenType3"=>0,
								  "PaymentAmt3"=>1, "Unit"=>0, "Quantity"=>1,"Type"=>0,"CashbillVoucherNo"=>0);

	var $ExportFileName = array(
        "cs" => "%s/tjh_cs_%s.txt",
		"ar" => "%s/tjh_ar_%s.txt",
		"cn" => "%s/tjh_cn_%s.txt"
    );
	var $ExportDateFormat = "d/m/Y";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config, $appCore;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>false,'ar'=>false,'cn'=>true);
		else
			$dataType=array('cs'=>true,'ap'=>false,'ar'=>true,'cn'=>true);

		if($config['consignment_modules']){
			$dataType['cs']=false;
			$dataType['ap']=false;
		}

        return array(
			"module_name" => __CLASS__,
			"export_with_sdk"=>self::$export_with_sdk,
			"show_all_column"=>self::$show_all_column,
			"settings"=>  array (
				'customer_code' => array(
					"name"=>"Customer Code",
					"account"=> array (
						'account_code' => 'CASH',
						'account_name' => 'CASH',
					),
					"help"=>"For POS Cash Sales only"
				),
				"non_gst"=>array(
					"editable"=>false,
					"Name"=>"No Register GST",
					"gst_info"=>array(
						"code" => $appCore->gstManager->getTextNR(),
						"rate"=>0
					)
				),
				"index"=>array(
					"editable"=>false,
					"Name"=>"Index",
					"index_info"=> 1,
				),
				"payment"=>array(
					"editable"=>false,
					"Name"=>"Payment Type",
					"type"=>"CASH"
				)
			),
			"dataType"=> $dataType,
			"groupby"=>self::$groupby
		);
    }

	function preset_account_column($dataType)
	{
		switch($dataType)
		{
			case 'cs':
			case 'ar':
			case 'cn':
					if($this->sys=='lite') $this->tvExportCol = array_merge($this->ExportFileHeader,array("dummy"=>0));
					else $this->tvExportCol = $this->ExportFileHeader;
				break;
			default:
					$this->tvExportCol = array();
				break;
		}
	}

	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);

			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));

			switch(strtolower($groupBy))
			{
				case 'daily summary':

					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, account_code, account_name, customer_code, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0
											group by pos_date, batchno, acc_type, account_code, account_name, tax_code");
					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$paymentType=array();
						$branch_id=$result['batchno'];
						$posDate=$result['pos_date'];						
						$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description, acc_type
															 from ".$this->tmpTable."
															 where `type` = 'debit' and ((TotalAmount > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
															 and pos_date=".ms($posDate)."
															 and (tablename='pos' or tablename='membership')
															 and description not in ('Mix & Match Total Disc')
															 and batchno=".mi($branch_id)."
															 group by description");
						while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
						{
							$paymentType[strtolower($resultPayment['acc_type'])] = $resultPayment['TotalAmount'];
						}
						$tmpSalesDb->sql_freeresult($retPayment);

						$batchNo = $this->create_batch_no(date("ymd",strtotime($posDate)),$bn);
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = $batchNo;
						$upd["Reference"] = $branch_id;
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$posDate);
						$upd["Description"] = "SALES";
						$upd["Category"] = "SALES";
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
						$upd["Dept"] = 0;
						$upd["Job"] = 0;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
						$upd["Index"] = $this->accSettings['index']['index_info'];
						$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
						if(count(array_keys($paymentType)) > 3)
						{
							$payment = 0;

							foreach($paymentType as $type=>$amount)
							{
								if($type!=="cash" && $type!=="credit_card")
								{
									$payment += $amount;
									unset($paymentType[$type]);
								}
							}
							$paymentType['Other'] = $payment;
						}
						$pt = array_keys($paymentType);
						for($p = 0; $p<3; $p++)
						{
							$tmp = $p+1;
							if(isset($pt[$p]))
							{
								$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
								if($p==0)
								{
									$upd["PaymenType"] = $type;
									$upd["PaymentAmt"] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
								else
								{

									$upd["PaymentType".$tmp] = $type;
									$upd["PaymentAmt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
							}
							else
							{
								if($p==0)
								{
									$upd["PaymenType"] = $this->accSettings['payment']['type'];
									$upd["PaymentAmt"] = "";
								}
								else
								{
									$upd["PaymentType".$tmp] = "";
									$upd["PaymentAmt".$tmp] = "";
								}
							}
						}
						$upd["Unit"] = "Unit";
						$upd["Quantity"] = "1";
						$upd["Type"] = "cashbill";
						$upd["CashbillVoucherNo"] = "";
						//my_fputcsv($fp, $upd);

						if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
						if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
						$tmpSalesDetail[$posDate][$branch_id][]=$upd;

						unset($upd,$paymentType);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date, batchno, tax_code, taxRate, doc_no, ref_no,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount
														from ".$this->tmpTable."
														where `type` = 'credit'
														and tablename='do'
														group by pos_date, batchno, ref_no, acc_type, account_code, account_name, tax_code
														order by pos_date");
						$bn = 1;
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$paymentType=array();
							$branch_id=$result['batchno'];
							$posDate=$result['pos_date'];
							$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description
																 from ".$this->tmpTable."
																 where `type` = 'debit'
																 and pos_date=".ms($posDate)."
																 and tablename='do'
																 and description not in ('Mix & Match Total Disc')
																 and batchno=".mi($branch_id)."
																 group by description");
							while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
							{
								$paymentType[strtolower($resultPayment['description'])] = $resultPayment['TotalAmount'];
							}
							$tmpSalesDb->sql_freeresult($retPayment);

							$batchNo = $this->create_batch_no(date("ymd",strtotime($posDate)),$bn);
							$upd["CustomerCode"] = $result['customer_code'];
							$upd["VoucherNo"] = $result['doc_no'];
							$upd["Reference"] = $branch_id;
							$upd["Date"] = $this->set_date($this->ExportDateFormat,$posDate);
							$upd["Description"] = "SALES";
							$upd["Category"] = "SALES";
							$upd["SalesmanCode"] = 0;
							$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
							$upd["Dept"] = 0;
							$upd["Job"] = 0;
							$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
							$upd["Index"] = $this->accSettings['index']['index_info'];
							$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
							if(count(array_keys($paymentType)) > 3)
							{
								$payment = 0;

								foreach($paymentType as $type=>$amount)
								{
									if($type!=="cash" && $type!=="credit_card")
									{
										$payment += $amount;
										unset($paymentType[$type]);
									}
								}
								$paymentType['Other'] = $payment;
							}
							$pt = array_keys($paymentType);
							for($p = 0; $p<3; $p++)
							{
								$tmp = $p+1;
								if(isset($pt[$p]))
								{
									$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
									if($p==0)
									{
										$upd["PaymenType"] = $type;
										$upd["PaymentAmt"] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
									else
									{

										$upd["PaymentType".$tmp] = $type;
										$upd["PaymentAmt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
								}
								else
								{
									if($p==0)
									{
										$upd["PaymenType"] = $this->accSettings['payment']['type'];
										$upd["PaymentAmt"] = "";
									}
									else
									{
										$upd["PaymentType".$tmp] = "";
										$upd["PaymentAmt".$tmp] = "";
									}
								}
							}
							$upd["Unit"] = "Unit";
							$upd["Quantity"] = "1";
							$upd["Type"] = "cashbill";
							$upd["CashbillVoucherNo"] = "";
							//my_fputcsv($fp, $upd);

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id][]=$upd;

							unset($upd,$paymentType);
							$bn++;
						}
						$tmpSalesDb->sql_freeresult($ret);
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);

					foreach($tmpSalesDetail as $posDate=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								my_fputcsv($fp, $upd);
							}
						}
					}
					unset($tmpSalesDetail);

					break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno,acc_type, account_code, account_name, customer_code, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0 
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, acc_type, account_code, account_name, tax_code");
					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$paymentType=array();
						$branch_id=$result['batchno'];
						$posDate=$result['ym'];
						$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description
															 from ".$this->tmpTable."
															 where `type` = 'debit' and ((TotalAmount > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
															 and ym=".ms($posDate)."
															 and (tablename='pos' or tablename='membership')
															 and description not in ('Mix & Match Total Disc')
															 and batchno=".mi($branch_id)."
															 group by description");
						while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
						{
							$paymentType[strtolower($resultPayment['description'])] = $resultPayment['TotalAmount'];
						}
						$tmpSalesDb->sql_freeresult($retPayment);

						$batchNo = $this->create_batch_no(date("ymd",strtotime($posDate)),$bn);
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = $batchNo;
						$upd["Reference"] = $branch_id;
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$posDate);
						$upd["Description"] = "SALES";
						$upd["Category"] = "SALES";
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
						$upd["Dept"] = 0;
						$upd["Job"] = 0;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
						$upd["Index"] = $this->accSettings['index']['index_info'];
						$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
						if(count(array_keys($paymentType)) > 3)
						{
							$payment = 0;

							foreach($paymentType as $type=>$amount)
							{
								if($type!=="cash" && $type!=="credit_card")
								{
									$payment += $amount;
									unset($paymentType[$type]);
								}
							}
							$paymentType['Other'] = $payment;
						}
						$pt = array_keys($paymentType);
						for($p = 0; $p<3; $p++)
						{
							$tmp = $p+1;
							if(isset($pt[$p]))
							{
								$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
								if($p==0)
								{
									$upd["PaymenType"] = $type;
									$upd["PaymentAmt"] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
								else
								{

									$upd["PaymentType".$tmp] = $type;
									$upd["PaymentAmt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
							}
							else
							{
								if($p==0)
								{
									$upd["PaymenType"] = $this->accSettings['payment']['type'];
									$upd["PaymentAmt"] = "";
								}
								else
								{
									$upd["PaymentType".$tmp] = "";
									$upd["PaymentAmt".$tmp] = "";
								}
							}
						}
						$upd["Unit"] = "Unit";
						$upd["Quantity"] = "1";
						$upd["Type"] = "cashbill";
						$upd["CashbillVoucherNo"] = "";
						//my_fputcsv($fp, $upd);

						if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
						if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
						$tmpSalesDetail[$posDate][$branch_id][]=$upd;

						unset($upd,$paymentType);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date, batchno, tax_code, taxRate, doc_no, ref_no,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount
														from ".$this->tmpTable."
														where `type` = 'credit'
														and tablename='do'
														group by pos_date, batchno, ref_no, acc_type, account_code, account_name, tax_code
														order by pos_date");
						$bn = 1;
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$paymentType=array();
							$branch_id=$result['batchno'];
							$posDate=$result['pos_date'];
							$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description
																 from ".$this->tmpTable."
																 where `type` = 'debit'
																 and pos_date=".ms($posDate)."
																 and tablename='do'
																 and batchno=".mi($branch_id)."
																 and description not in ('Mix & Match Total Disc')
																 group by description");
							while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
							{
								$paymentType[strtolower($resultPayment['description'])] = $resultPayment['TotalAmount'];
							}
							$tmpSalesDb->sql_freeresult($retPayment);

							$batchNo = $this->create_batch_no(date("ymd",strtotime($posDate)),$bn);
							$upd["CustomerCode"] = $result['customer_code'];
							$upd["VoucherNo"] = $result['doc_no'];
							$upd["Reference"] = $branch_id;
							$upd["Date"] = $this->set_date($this->ExportDateFormat,$posDate);
							$upd["Description"] = "SALES";
							$upd["Category"] = "SALES";
							$upd["SalesmanCode"] = 0;
							$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
							$upd["Dept"] = 0;
							$upd["Job"] = 0;
							$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
							$upd["Index"] = $this->accSettings['index']['index_info'];
							$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
							if(count(array_keys($paymentType)) > 3)
							{
								$payment = 0;

								foreach($paymentType as $type=>$amount)
								{
									if($type!=="cash" && $type!=="credit_card")
									{
										$payment += $amount;
										unset($paymentType[$type]);
									}
								}
								$paymentType['Other'] = $payment;
							}
							$pt = array_keys($paymentType);
							for($p = 0; $p<3; $p++)
							{
								$tmp = $p+1;
								if(isset($pt[$p]))
								{
									$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
									if($p==0)
									{
										$upd["PaymenType"] = $type;
										$upd["PaymentAmt"] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
									else
									{

										$upd["PaymentType".$tmp] = $type;
										$upd["PaymentAmt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
								}
								else
								{
									if($p==0)
									{
										$upd["PaymenType"] = $this->accSettings['payment']['type'];
										$upd["PaymentAmt"] = "";
									}
									else
									{
										$upd["PaymentType".$tmp] = "";
										$upd["PaymentAmt".$tmp] = "";
									}
								}
							}
							$upd["Unit"] = "Unit";
							$upd["Quantity"] = "1";
							$upd["Type"] = "cashbill";
							$upd["CashbillVoucherNo"] = "";
							//my_fputcsv($fp, $upd);

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id][]=$upd;

							unset($upd,$paymentType);
							$bn++;
						}
						$tmpSalesDb->sql_freeresult($ret);
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);
					foreach($tmpSalesDetail as $posDate=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								my_fputcsv($fp, $upd);
							}
						}
					}
					unset($tmpSalesDetail);

					break;				
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date, tax_code, taxRate, doc_no, ref_no,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount
														from ".$this->tmpTable."
														where `type` = 'credit' and has_credit_notes = 0  
														group by pos_date, ref_no, acc_type, account_code, account_name, tax_code
														order by pos_date");

					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$paymentType=array();
						$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description
													   from ".$this->tmpTable."
													   where `type` = 'debit'
													   and pos_date=".ms($result['pos_date'])."
													   and ref_no = ".ms($result['ref_no'])."
													   and description not in ('Mix & Match Total Disc')
													   group by description");
						while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
						{
							$paymentType[strtolower($resultPayment['description'])] = $resultPayment['TotalAmount'];
						}
						$tmpSalesDb->sql_freeresult($retPayment);
						$batchNo = $this->create_batch_no(date("ymd",strtotime($result['pos_date'])),$bn);
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = $result['doc_no'];
						$upd["Reference"] = $result['ref_no'];
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["Description"] = "SALES";
						$upd["Category"] = "SALES";
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
						$upd["Dept"] = 0;
						$upd["Job"] = 0;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
						$upd["Index"] = $this->accSettings['index']['index_info'];
						$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
						if(count(array_keys($paymentType)) > 3)
						{
							$payment = 0;

							foreach($paymentType as $type=>$amount)
							{
								if($type!=="cash" && $type!=="credit_card")
								{
									$payment += $amount;
									unset($paymentType[$type]);
								}
							}
							$paymentType['Other'] = $payment;
						}
						$pt = array_keys($paymentType);
						for($p = 0; $p<3; $p++)
						{
							$tmp = $p+1;
							if(isset($pt[$p]))
							{
								$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
								if($p==0)
								{
									$upd["PaymenType"] = $type;
									$upd["PaymentAmt"] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
								else
								{

									$upd["PaymentType".$tmp] = $type;
									$upd["PaymentAmt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
								}
							}
							else
							{
								if($p==0)
								{
									$upd["PaymenType"] = $this->accSettings['payment']['type'];
									$upd["PaymentAmt"] = "";
								}
								else
								{
									$upd["PaymentType".$tmp] = "";
									$upd["PaymentAmt".$tmp] = "";
								}
							}
						}
						$upd["Unit"] = "Unit";
						$upd["Quantity"] = 1;
						$upd["Type"] = "cashbill";
						$upd["CashbillVoucherNo"] = "";
						my_fputcsv($fp, $upd);
						unset($upd,$paymentType);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
			}
			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_payable($tmpSalesDb,$groupBy,$dateTo)
	{
		//SKIP TJH in not has account payable
	}

	function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);

			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp,array_keys($this->ExportFileHeader));

			$ret = $this->sql_query($tmpSalesDb, "select do_date, batchno, inv_no, customer_code, customer_name, account_code, account_name,
                                tax_code, tax_account_code, tax_account_name, currencyrate, foreign_currency_code, terms,taxRate,
                                round(sum(ItemAmount),2) as ItemAmount,
                                round(sum(TaxAmount),2) as TaxAmount,
                                round(sum(TotalAmount),2) as TotalAmount,
                                round(sum(ItemFAmount),2) as ItemFAmount,
                                round(sum(TaxFAmount),2) as TaxFAmount,
                                round(sum(TotalFAmount),2) as TotalFAmount
                                from ".$this->tmpTable."
                                group by do_date, inv_no, customer_code, account_code, account_name, tax_code
                                order by do_date, inv_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $this->create_batch_no(date("ymd",strtotime($result['do_date'])),$bn);
				$upd["CustomerCode"] = $result['customer_code'];
				$upd["VoucherNo"] = $result['inv_no'];
				$upd["Reference"] = $result['batchno'];
				$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
				$upd["Description"] = "SALES";
				$upd["Category"] = "SALES";
				$upd["SalesmanCode"] = 0;
				$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
				$upd["Dept"] = 0;
				$upd["Job"] = 0;
				$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd["Index"] = $this->accSettings['index']['index_info'];
				$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['taxRate']);
				$upd["PaymenType"] = $this->accSettings['payment']['type'];
				$upd["PaymentAmt"] = $upd["TotalAmount"];
                $upd["PaymenType2"] = "";
				$upd["PaymentAmt2"] = "";
                $upd["PaymenType3"] = "";
				$upd["PaymentAmt3"] = "";
				$upd["Unit"] = "Unit";
				$upd["Quantity"] = "1";
				$upd["Type"] = "ar";
				$upd["CashbillVoucherNo"] = "";
				my_fputcsv($fp, $upd);
				unset($upd);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));

			$ret = $this->sql_query($tmpSalesDb, "select return_receipt_no, credit_note_no, batchno, date, currencyrate, tax_code, tax_rate,
													customer_code, customer_name, reason, account_code,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount
													from ".$this->tmpTable."
													group by date, return_receipt_no, credit_note_no, tax_code, tax_rate
													order by date, return_receipt_no, credit_note_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['credit_note_no'];
				$returnBatchNo = $result['return_receipt_no'];
				$upd["CustomerCode"] = $result['customer_code'];
				$upd["VoucherNo"] = $batchNo;
				$upd["Reference"] = $result['batchno'];
				$upd["Date"] =  $this->set_date($this->ExportDateFormat,$result['date']);;
				$upd["Description"] = "Credit Notes (Goods Return)";
				$upd["Category"] = $result['account_code'];
				$upd["SalesmanCode"] = 0;
				$upd["Tax"] = trim($result['tax_code'])!=""?$result['tax_code']:$this->accSettings['non_gst']['gst_info']['code'];
				$upd["Dept"] = 0;
				$upd["Job"] = 0;
				$upd["ItemAmount"] = $this->selling_price_currency_format(abs($result['ItemAmount']));
				$upd["TaxAmount"] = $this->selling_price_currency_format(abs($result['TaxAmount']));
				$upd["TotalAmount"] = $this->selling_price_currency_format(abs($result['TotalAmount']));
				$upd["Index"] = $this->accSettings['index']['index_info'];
				$upd["TaxRate"] = sprintf($LANG['GST_RATE'],$result['tax_rate']);							
				$upd["PaymenType"] = "";				
				$upd["PaymentAmt"] = "";
				$upd["PaymenType2"] = "";
				$upd["PaymentAmt2"] = "";
				$upd["PaymenType3"] = "";
				$upd["PaymentAmt3"] = "";			
				$upd["Unit"] = "Unit";
				$upd["Quantity"] = "1";
				$upd["Type"] = "cn";
				$upd["CashbillVoucherNo"] = $returnBatchNo;		
				my_fputcsv($fp,$upd);
				unset($upd);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode){

	}

	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		/*already inside cash sales*/
	}

	
	/*function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		//todo
	}*/
}
?>
