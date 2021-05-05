<?php
/*
2016-03-08 05:30pm Kee Kee
- Cash Sales cannot contain goods return amount

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2016-08-10 4:16 PM Kee Kee
- Assign wrong customer code in export credit notes data

2017-01-04 14:00 PM Kee Kee
- Set "job_code" as branch code when "JOB as Branch Code" setting is enable

2017-04-28 7:47 AM Qiu Ying
- Bug fixed on result combine different tax code in one row
*/
include_once("ExportModule.php");
class MrAccounting extends ExportModule
{
	const NAME="Mr Accounting";
	static $export_with_sdk=false;
	static $show_all_column=true;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader   = array("*CustomerCode"=>0,"VoucherNo"=>0,"*ReferenceNo"=>0,"*Date"=>0,"Description"=>0,"*Category/GLCode"=>0,"SalesmanCode"=>0,"*Tax"=>0,"Dept"=>0,"Job"=>0,"*ItemAmount"=>1,"*TaxAmount"=>1,"*TotalAmount"=>1);
	var $ExportFileHeaderAP = array("*VoucherNumber"=>0,"*SuppCode"=>0,"*Reference"=>0,"*Date"=>0,"Description"=>0,"*GLCode"=>0,"Department"=>0,"Job"=>0,"*TaxType"=>0,"*ItemAmount"=>1,"*TaxAmount"=>1,"*TotalAmount"=>1);
	var $ExportFileHeaderCN = array("*CustomerCode"=>0,"VoucherNo"=>0,"*ReferenceNo"=>0,"*Date"=>0,"Description"=>0,"*Category/GLCode"=>0,"SalesmanCode"=>0,"*Tax"=>0,"Dept"=>0,"Job"=>0,"*ItemAmount"=>1,"*TaxAmount"=>1,"*TotalAmount"=>1);
	var $ExportFileHeaderPT = array("*Customer Code"=>0,"Voucher No"=>0,"Bank Code"=>0,"*Description"=>0,"*Receipt Date"=>0,"*Cheque No"=>0,"*Amount"=>1);

	var $ExportFileName = array(
        "cs" => "%s/MACashSales%s.csv",
		"ap" => "%s/MAAccountPayable%s.csv",
		"ar" => "%s/MAAccountReceiver%s.csv",
		"cn" => "%s/MACreditNote%s.csv",
		"dn" => "%s/MADebitNote%s.csv",
		"pt" => "%s/MAPaymentType%s.csv",
    );
	var $ExportDateFormat = "j/n/Y";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>false,'cn'=>true,'pt'=>true);
		else
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>true,'cn'=>true,'dn'=>true);

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
				'purchase'=> array(
					"name"=>"Purchase",
					"account"=> array (
						'account_code' => '51100',
						'account_name' => 'Purchase',
					)
				),
				'sales'=> array(
					"name"=>"Sales",
					"account"=> array (
						'account_code' => '51100',
						'account_name' => 'Sales 1',
					)
				),
				'cash' => array (
					"name"=>"Cash",
					"account"=>array(
						'account_code' => '11100',
						'account_name' => 'Cash On Hand',
					)
				),
				'credit_card' => array (
					"name"=>"Credit Card",
					"account"=>array(
						'account_code' => '11310',
						'account_name' => 'Bank 1',
					)
				),
				'coupon' => array (
					"name"=>"Coupon",
					"account"=>array(
						'account_code' => '11320',
						'account_name' => 'Bank 2',
					)
				),
				'voucher' => array(
					"name"=>"Voucher",
					"account"=>array(
						'account_code' => '11330',
						'account_name' => 'Bank 3',
					)
				),
				'check' => array (
					"name"=>"Check",
					"account"=>array(
						'account_code' => '11310',
						'account_name' => 'Bank 1',
					)
				),
				'deposit' => array (
					"name"=>"Deposit",
					"account"=>array(
						'account_code' => '11400',
						'account_name' => 'Bank Deposit / Fix Deposit',
					)
				),
				"non_gst"=>array(
					"editable"=>false,
					"name"=>"No Register GST",
						"gst_info"=>array(
						"code"=>"NR",
						"rate"=>0
						)
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '11400',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '11400',
						'account_name' => 'Over',
					)
				),
				'rounding' => array (
					"name"=>"Rounding",
					"account"=>array(
						'account_code' => '11400',
						'account_name' => 'Rounding',
					)
				),
				'job_as_branch_code' => array (
					"name"=>"JOB as Branch Code",
					"data"=>0,
				),
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
				if($this->sys=='lite')
					$this->tvExportCol = array_merge($this->ExportFileHeader,array("dummy"=>0));
				else
					$this->tvExportCol = $this->ExportFileHeader;
				break;
			case 'ap':
			case 'dn':
				if($this->sys=='lite')
					$this->tvExportCol = array_merge($this->ExportFileHeaderAP,array("dummy"=>0));
				else
					$this->tvExportCol = $this->ExportFileHeaderAP;
					break;
			case 'cn':
				if($this->sys=='lite')
					$this->tvExportCol = array_merge($this->ExportFileHeaderCN,array("dummy"=>0));
				else
					$this->tvExportCol = $this->ExportFileHeaderCN;
					break;
			case 'pt':
				if($this->sys=='lite')
					$this->tvExportCol = array_merge($this->ExportFileHeaderPT,array("dummy"=>0));
				else
					$this->tvExportCol = $this->ExportFileHeaderPT;
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
			if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
				$job = $branchCode;
			else
				$job = 0;
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);	
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, customer_code, account_code, account_name, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0 
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, acc_type, account_code, account_name, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = "";
						$upd["Reference"] = "";
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["Description"] = $result['account_name'];
						$upd["Category"] = $result['account_code'];
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = (($result['tax_code']=="NR" || $result['tax_code']=="")?"OS":$result['tax_code']);
						$upd["Dept"] = 0;
						$upd["Job"] = $job;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);

						$tmpSalesDetail[$result['pos_date']][$result['batchno']][]=$upd;
						unset($upd);
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, acc_type,
											customer_code, account_code, account_name, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit'
											and tablename='do'											
											group by pos_date, batchno, doc_no, acc_type, account_code, account_name, tax_code");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$upd["CustomerCode"] = $result['customer_code'];
							$upd["VoucherNo"] =  $result['doc_no'];
							$upd["Reference"] = $result['receipt_no'];
							$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["Description"] = $result['account_name'];
							$upd["Category"] = $result['account_code'];
							$upd["SalesmanCode"] = 0;
							$upd["Tax"] = (($result['tax_code']=="NR" || $result['tax_code']=="")?"OS":$result['tax_code']);
							$upd["Dept"] = 0;
							$upd["Job"] = $job;
							$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
							$tmpSalesDetail[$result['pos_date']][$result['batchno']][]=$upd;
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret);
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);
					$bn = 1;
					
					foreach($tmpSalesDetail as $pos_date=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								$batchNo = $this->create_batch_no(date("ymd",strtotime($pos_date)),$bn);
								if($upd["VoucherNo"]=="") $upd["VoucherNo"] = $batchNo;
								$upd["Reference"] = $branch_id;
								my_fputcsv($fp, $upd);
								$bn++;
							}
						}
					}
					unset($tmpSalesDetail, $pos_date, $val, $upd, $bn, $batchNo, $ret, $result);

					break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno, acc_type, customer_code, account_code, account_name, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0 
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, acc_type, account_code, account_name, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = "";
						$upd["Reference"] = "";
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['ym']);
						$upd["Description"] = $result['account_name'];
						$upd["Category"] = $result['account_code'];
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = (($result['tax_code']=="NR" || $result['tax_code']=="")?"OS":$result['tax_code']);
						$upd["Dept"] = 0;
						$upd["Job"] = $job;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);

						$tmpSalesDetail[$result['ym']][$result['batchno']][]=$upd;
						unset($upd);
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, acc_type,
											customer_code, account_code, account_name, tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit'
											and tablename='do'
											group by ym, doc_no, acc_type, account_code, account_name, tax_code");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$upd["CustomerCode"] = $result['customer_code'];
							$upd["VoucherNo"] = "";
							$upd["Reference"] = $result['receipt_no'];
							$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["Description"] = $result['account_name'];
							$upd["Category"] = $result['account_code'];
							$upd["SalesmanCode"] = 0;
							$upd["Tax"] = (($result['tax_code']=="NR" || $result['tax_code']=="")?"OS":$result['tax_code']);
							$upd["Dept"] = 0;
							$upd["Job"] = $job;
							$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);

							$tmpSalesDetail[$result['pos_date']][$result['batchno']][]=$upd;
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret);
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);
					$bn = 1;
					foreach($tmpSalesDetail as $pos_date=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								$batchNo = $this->create_batch_no(date("ymd",strtotime($pos_date)),$bn);
								if($upd["VoucherNo"]=="") $upd["VoucherNo"] = $batchNo;
								$upd["Reference"] = $branch_id;
								my_fputcsv($fp, $upd);
								$bn++;
							}
						}
					}
					unset($tmpSalesDetail, $pos_date, $val, $upd, $bn, $batchNo, $ret, $result);

					break;				
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select batchno, customer_code, doc_no, ref_no, pos_date,
												  description, tax_code, account_code, tax_code,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TaxAmount),2) as TaxAmount,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  where `type` = 'credit' and has_credit_notes = 0 
													group by pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name, tax_code
												  order by pos_date, ref_no");

					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$upd["CustomerCode"] = $result['customer_code'];
						$upd["VoucherNo"] = $result['ref_no'];
						$upd["Reference"] = $result['doc_no'];
						$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["Description"] = "SALES";
						$upd["Category"] = $result['account_code'];
						$upd["SalesmanCode"] = 0;
						$upd["Tax"] = (($result['tax_code']=="NR" || $result['tax_code']=="")?"OS":$result['tax_code']);
						$upd["Dept"] = 0;
						$upd["Job"] = $job;
						$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
						my_fputcsv($fp, $upd);

						unset($upd);
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
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeaderAP));

			$ret = $this->sql_query($tmpSalesDb, "select vendor_code, inv_no, ref_no, inv_date, gl_code, taxCode,
										  'Purchase' as sku_desc,job_code,department,
										  round(sum(ItemAmount),2) as ItemAmount,
										  round(sum(TaxAmount),2) as TaxAmount,
										  round(sum(TotalAmount),2) as TotalAmount
										  from ".$this->tmpTable."
										  group by inv_date, inv_no, vendor_code, taxCode
										  order by inv_date, inv_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$upd["VoucherNo"] = $result['inv_no'];
				$upd["SupplierCode"] = $result['vendor_code'];
				$upd["ReferenceNo"] = $result['ref_no'];
				$upd["VoucherDate"] = $this->set_date($this->ExportDateFormat,$result['inv_date']);
				$upd["Description"] = $result['sku_desc'];
				$upd["GLCode"] = $result['gl_code'];
				$upd["Department"] = $result['department'];
				$upd["Job"] = $result['job_code'];
				$upd["TaxType"] = $result['taxCode'];
				$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
				my_fputcsv($fp, $upd);

				unset($upd);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
				$job = $branchCode;
			else
				$job = 0;
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));

			$ret = $this->sql_query($tmpSalesDb, "select do_date, batchno, inv_no, customer_code, customer_name, account_code, account_name,
                                tax_code, tax_account_code, tax_account_name, currencyrate, foreign_currency_code, terms,
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
                //$batchNo = $this->create_batch_no(date("ymd",strtotime($result['do_date'])),$bn);
				$upd["CustomerCode"] = $result['customer_code'];
				$upd["VoucherNo"] = $result['inv_no'];
				$upd["Reference"] = $result['batchno'];
				$upd["Date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
				$upd["Description"] = "SALES";
				$upd["Category"] = "SALES";
				$upd["SalesmanCode"] = 0;
				$upd["Tax"] = ($result['tax_code']=="NR"?"OS":$result['tax_code']);
				$upd["Dept"] = 0;
				$upd["Job"] = $job;
				$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
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
			if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
				$job = $branchCode;
			else
				$job = 0;
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeaderCN));

			$ret = $this->sql_query($tmpSalesDb, "select date, tax_code, account_code,customer_code,
											return_receipt_no, credit_note_no,
											sum(ItemAmount) as ItemAmount,
											sum(TaxAmount) as TaxAmount,
											sum(TotalAmount) as TotalAmount
											from ".$this->tmpTable."
											group by date, return_receipt_no, credit_note_no, tax_code
											order by date, return_receipt_no, credit_note_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['credit_note_no'];
				$returnBatchNo = $result['return_receipt_no'];
				$upd["*CustomerCode"] = $result['customer_code'];
				$upd["*VoucherNo"] = $batchNo;
				$upd["*ReferenceNo"] = $returnBatchNo;
				$upd["*VoucherDate"] = $this->set_date($this->ExportDateFormat,$result['date']);
				$upd["*Description"] = "Credit Notes (Goods Return)";
				$upd["*Category"] = $result['account_code'];
				$upd["SalesmanCode"] = 0;
				$upd["*TaxType"] = $result['tax_code'];
				$upd["DepartmentCode"] = 0;
				$upd["JobCode"] = $job;
				$upd["*ItemAmount"] = $this->selling_price_currency_format(abs($result['ItemAmount']));
				$upd["*TaxAmount"] = $this->selling_price_currency_format(abs($result['TaxAmount']));
				$upd["*TotalAmount"] = $this->selling_price_currency_format(abs($result['TotalAmount']));
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
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
				$job = $branchCode;
			else
				$job = 0;
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeaderAP));

			$ret = $this->sql_query($tmpSalesDb, "select date, invoice_no, tax_code, currencyrate, currency_code,
													customer_code, customer_name,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount
													from ".$this->tmpTable."
													group by date, invoice_no, tax_code
													order by date, invoice_no");

			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$returnBatchNo = $result['return_receipt_no'];
				$upd["VoucherNumber"] = $result['invoice_no'];;
				$upd["SuppCode"] = $result['customer_code'];
				$upd["ReferenceNo"] = $result['invoice_no'];;
				$upd["VoucherDate"] = $this->set_date($this->ExportDateFormat,$result['date']);
				$upd["Description"] = "Debit Notes";
				$upd["GLCode"] = $result['customer_code'];
				$upd["DepartmentCode"] = 0;
				$upd["JobCode"] = $job;
				$upd["TaxType"] = $result['tax_code'];
				$upd["ItemAmount"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["TaxAmount"] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd["TotalAmount"] = $this->selling_price_currency_format($result['TotalAmount']);
				my_fputcsv($fp,$upd);
				unset($upd);
			}
			$tmpSalesDb->sql_freeresult($ret);
			/*$ret = $this->sql_query($tmpSalesDb, "select pos_date as dn_date, return_date, customer_code, tax_code,
											receipt_no as return_receipt_no,
											debit_note_no as dn_no,
											sum(ItemAmount) as ItemAmount,
											sum(TaxAmount) as TaxAmount,
											sum(TotalAmount) as TotalAmount
											from ".$this->tmpTable."
											group by dn_date, dn_no, return_date, receipt_no
											order by dn_date, dn_no, return_date, receipt_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['dn_no'];
				$returnBatchNo = $result['return_receipt_no'];
				$upd["VoucherNumber"] = $batchNo;
				$upd["SuppCode"] = $result['customer_code'];
				$upd["ReferenceNo"] = $returnBatchNo;
				$upd["VoucherDate"] = $this->set_date($this->ExportDateFormat,$result['dn_date']);
				$upd["Description"] = "Debit Notes";
				$upd["GLCode"] = $result['customer_code'];
				$upd["DepartmentCode"] = 0;
				$upd["JobCode"] = 0;
				$upd["TaxType"] = $result['tax_code'];
				$upd["ItemAmount"] = $this->selling_price_currency_format(abs($result['ItemAmount']));
				$upd["TaxAmount"] = $this->selling_price_currency_format(abs($result['TaxAmount']));
				$upd["TotalAmount"] = $this->selling_price_currency_format(abs($result['TotalAmount']));
				my_fputcsv($fp,$upd);
				unset($upd);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);*/

			fclose($fp);
		}
		return $total['total'];
	}

	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$credit_card = $this->credit_cards_type();
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
        $tmpSalesDetail=array();
		if($total['total']>0)
		{
			if(isset($this->accSettings['job_as_branch_code']) && $this->accSettings['job_as_branch_code']['data'])		
				$job = $branchCode;
			else
				$job = 0;
			if(file_exists($this->tmpPaymentFile)) unlink($this->tmpPaymentFile);
			$fp = fopen($this->tmpPaymentFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeaderPT));

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, acc_type, customer_code, account_code,
											account_name, tax_code, description,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by pos_date, branch_id, acc_type, account_code, account_name");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						if(isset($credit_card[$result['description']])) $result['description']="Credit Card";
						$upd["*Customer Code"] = $result['customer_code'];
						$upd["Voucher No"] = "";
						//$upd["*Reference No"] = "";
						$upd["Bank Code"] =$result['account_code'];
						$upd["*Description"] = $result['description'];
						$upd["*Receipt Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["*Cheque No"] = "0";
						//$upd["*Cheque Date"] = "";
						$upd["*Amount"] = $this->selling_price_currency_format($result['TotalAmount']);
						//$upd["Currency Code"] = "MYR";
						//$upd["Currency Rate"] = "1";
						$tmpSalesDetail[$result['pos_date']][$result['branch_id']][]=$upd;
					}

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, doc_no, acc_type, customer_code, account_code,
											account_name, tax_code, description,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and tablename='do'
											group by pos_date, branch_id, doc_no, acc_type, account_code, account_name");

						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							if(isset($credit_card[$result['description']])) $result['description']="Credit Card";
							$upd["*Customer Code"] = $result['customer_code'];
							$upd["Voucher No"] = $result['doc_no'];
							//$upd["*Reference No"] = "";
							$upd["Bank Code"] =$result['account_code'];
							$upd["*Description"] = "";
							$upd["*Receipt Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["*Cheque No"] = "0";
							//$upd["*Cheque Date"] = "";
							$upd["*Amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							//$upd["Currency Code"] = "MYR";
							//$upd["Currency Rate"] = "1";
							$tmpSalesDetail[$result['pos_date']][$result['branch_id']][]=$upd;
						}
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);
					$bn = 1;
					foreach($tmpSalesDetail as $pos_date=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								$batchNo = $this->create_batch_no(date("ymd",strtotime($pos_date)),$bn);
								if($upd["Voucher No"]=="") $upd["Voucher No"] = $batchNo;
								//if($upd["*Reference No"]=="") $upd["*Reference No"] = $batchNo;
								my_fputcsv($fp, $upd);
								$bn++;
							}
						}
					}
					unset($tmpSalesDetail);

					break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select ym, branch_id, acc_type, customer_code, account_code,
											account_name, tax_code, description,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by ym, branch_id, acc_type, account_code, account_name");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						if(isset($credit_card[$result['description']])) $result['description']="Credit Card";
						$upd["*Customer Code"] = $result['customer_code'];
						$upd["Voucher No"] = "";
						//$upd["*Reference No"] = "";
						$upd["Bank Code"] =$result['account_code'];
						$upd["*Description"] = $result['description'];
						$upd["*Receipt Date"] = $this->set_date($this->ExportDateFormat,$result['ym']);
						$upd["*Cheque No"] = "0";
						//$upd["*Cheque Date"] = "";
						$upd["*Amount"] = $this->selling_price_currency_format($result['TotalAmount']);
						//$upd["Currency Code"] = "MYR";
						//$upd["Currency Rate"] = "1";
						$tmpSalesDetail[$result['ym']][$result['branch_id']][]=$upd;
					}

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select ym, branch_id, doc_no, acc_type, customer_code, account_code,
											account_name, tax_code, description,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and tablename='do'
											group by ym, branch_id, doc_no, acc_type, account_code, account_name");

						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							if(isset($credit_card[$result['description']])) $result['description']="Credit Card";
							$upd["*Customer Code"] = $result['customer_code'];
							$upd["Voucher No"] = $result['doc_no'];
							//$upd["*Reference No"] = "";
							$upd["Bank Code"] =$result['account_code'];
							$upd["*Description"] = $result['description'];
							$upd["*Receipt Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["*Cheque No"] = "0";
							//$upd["*Cheque Date"] = "";
							$upd["*Amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							//$upd["Currency Code"] = "MYR";
							//$upd["Currency Rate"] = "1";
							$tmpSalesDetail[$result['ym']][$result['branch_id']][]=$upd;
						}
					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);
					$bn = 1;
					foreach($tmpSalesDetail as $pos_date=>$branches){
						foreach($branches as $branch_id=>$val){
							foreach($val as $upd){
								$batchNo = $this->create_batch_no(date("ymd",strtotime($pos_date)),$bn);
								if($upd["Voucher No"]=="") $upd["Voucher No"] = $batchNo;
								my_fputcsv($fp, $upd);
								$bn++;
							}
						}
					}
					unset($tmpSalesDetail);

					break;
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, doc_no, ref_no, acc_type,
											customer_code, account_code, account_name, tax_code, description,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											group by pos_date, ref_no, acc_type, account_code, account_name
											order by pos_date");

					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						if(isset($credit_card[$result['description']])) $result['description']="Credit Card";
						$upd["*Customer Code"] = $result['customer_code'];
						$upd["Voucher No"] = $result['doc_no'];
						//$upd["*Reference No"] = $result['ref_no'];
						$upd["Bank Code"] = $result['account_code'];
						$upd["*Description"] = $result['description'];
						$upd["*Receipt Date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["*Cheque No"] = "0";
						//$upd["*Cheque Date"] = "";
						$upd["*Amount"] = $this->selling_price_currency_format($result['TotalAmount']);
						//$upd["Currency Code"] = "MYR";
						//$upd["Currency Rate"] = "1";
						my_fputcsv($fp, $upd);
					}

					break;
			}
			fclose($fp);
		}
		unset($credit_card);
		return $total['total'];
	}

	/*function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		//todo
	}*/
}
?>
