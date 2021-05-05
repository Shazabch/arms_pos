<?php 
/*
2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php
*/
include_once("ExportModule.php");
class Biztrak extends ExportModule
{
	const NAME="Biztrak";
	static $export_with_sdk=false;
	static $show_all_column=true;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");
	
	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array("Reference Number 3",
									"Serial Number",
									"Party Code",
									"Cheque Number",
									"Remarks",
									"Reference Number 1",
									"Transaction Date",
									"Bank Code",
									"Item Number",
									"GL Account Code",
									"Currency Code",
									"Transaction Amount",
									"Base Amount",
									"Description",
									"Salesman Code",
									"Freight Amount",
									"Due Date",
									"Credit Term",
									"Reference Number 2",
									"Reference Number 4",
									"Note",
									"Received Date",
									"Bank Charges",
									"Custom Text (Header) Level 1",
									"Custom Text (Header) Level 2",
									"Custom Text (Header) Level 3",
									"Custom Text (Header) Level 4",
									"Custom Text (Header) Level 5",
									"Custom Text (Header) Level 6",
									"Custom Text (Header) Level 7",
									"Custom Text (Header) Level 8",
									"Custom Text (Header) Level 9",
									"Custom Text (Details) Level 1",
									"Custom Text (Details) Level 2",
									"Custom Text (Details) Level 3",
									"Custom Text (Details) Level 4",
									"Custom Text (Details) Level 5",
									"Custom Text (Details) Level 6",
									"Custom Text (Details) Level 7",
									"Custom Text (Details) Level 8",
									"Custom Text (Details) Level 9",
									"Analysis Code Level 1",
									"Analysis Code Level 2",
									"Analysis Code Level 3",
									"Analysis Code Level 4",
									"Analysis Code Level 5",
									"Analysis Code Level 6",
									"Analysis Code Level 7",
									"Analysis Code Level 8",
									"Analysis Code Level 9",
									"Cheque Status",
									"Presented Date",
									"Party Address 1",
									"Party Address 2",
									"Party Address 3",
									"Party Address 4",
									"Party Address 5",
									"Party Postcode",
									"Default Party Address",
									"Currency Rate",
									"Tax Calculation Method",
									"Header Tax Schedule Code",
									"Detail Tax Schedule Code",
									"Transaction Amount (Tax Incl)",
									"Transaction Amount In Base Currency (Tax Incl)",
									"Bank Amount");
	var $ExportFileHeaderAP = array("Reference Number 3",
									"Serial Number",
									"Party Code",
									"Cheque Number",
									"Remarks",
									"Reference Number 1",
									"Transaction Date",
									"Bank Code",
									"Item Number",
									"GL Account Code",
									"Currency Code",
									"Transaction Amount",
									"Base Amount",
									"Description",
									"Salesman Code",
									"Freight Amount",
									"Due Date",
									"Credit Term",
									"Reference Number 2",
									"Reference Number 4",
									"Note",
									"Received Date",
									"Bank Charges",
									"Custom Text Level 1",
									"Custom Text Level 2",
									"Custom Text Level 3",
									"Custom Text Level 4",
									"Custom Text Level 5",
									"Custom Text Level 6",
									"Custom Text Level 7",
									"Custom Text Level 8",
									"Custom Text Level 9",
									"Custom Text Level 1",
									"Custom Text Level 2",
									"Custom Text Level 3",
									"Custom Text Level 4",
									"Custom Text Level 5",
									"Custom Text Level 6",
									"Custom Text Level 7",
									"Custom Text Level 8",
									"Custom Text Level 9",
									"Analysis Code Level 1",
									"Analysis Code Level 2",
									"Analysis Code Level 3",
									"Analysis Code Level 4",
									"Analysis Code Level 5",
									"Analysis Code Level 6",
									"Analysis Code Level 7",
									"Analysis Code Level 8",
									"Analysis Code Level 9",
									"Cheque Status",
									"Presented Date",
									"Party Address 1",
									"Party Address 2",
									"Party Address 3",
									"Party Address 4",
									"Party Address 5",
									"Party Postcode",
									"Default Party Address",
									"Currency Rate",
									"Tax Calculation Method",
									"Header Tax Schedule Code",
									"Detail Tax Schedule Code",
									"Transaction Amount (Tax Incl)",
									"Transaction Amount In Base Currency (Tax Incl)");
	var $ExportFileHeaderAR = array("Reference Number 3",
									"Serial Number",
									"Party Code",
									"Cheque Number",
									"Remarks",
									"Reference Number 1",
									"Transaction Date",
									"Bank Code",
									"Item Number",
									"GL Account Code",
									"Currency Code",
									"Transaction Amount",
									"Base Amount",
									"Description",
									"Salesman Code",
									"Freight Amount",
									"Due Date",
									"Credit Term",
									"Reference Number 2",
									"Reference Number 4",
									"Note",
									"Received Date",
									"Bank Charges",
									"Custom Text Level 1",
									"Custom Text Level 2",
									"Custom Text Level 3",
									"Custom Text Level 4",
									"Custom Text Level 5",
									"Custom Text Level 6",
									"Custom Text Level 7",
									"Custom Text Level 8",
									"Custom Text Level 9",
									"Custom Text Level 1",
									"Custom Text Level 2",
									"Custom Text Level 3",
									"Custom Text Level 4",
									"Custom Text Level 5",
									"Custom Text Level 6",
									"Custom Text Level 7",
									"Custom Text Level 8",
									"Custom Text Level 9",
									"Analysis Code Level 1",
									"Analysis Code Level 2",
									"Analysis Code Level 3",
									"Analysis Code Level 4",
									"Analysis Code Level 5",
									"Analysis Code Level 6",
									"Analysis Code Level 7",
									"Analysis Code Level 8",
									"Analysis Code Level 9",
									"Cheque Status",
									"Presented Date",
									"Party Address 1",
									"Party Address 2",
									"Party Address 3",
									"Party Address 4",
									"Party Address 5",
									"Party Postcode",
									"Default Party Address",
									"Currency Rate",
									"Tax Calculation Method",
									"Header Tax Schedule Code",
									"Detail Tax Schedule Code",
									"Transaction Amount (Tax Incl)",
									"Transaction Amount In Base Currency (Tax Incl)",
									"Standard Industry Code");
	var $ScreenColCS = array("Reference Number 3" => 0,
						"Serial Number" => 0,
						"Reference Number 1" => 0,
						"Transaction Date" => 0,
						"Item Number" => 0,
						"GL Account Code" => 0,
						"Currency Code" => 0,
						"Transaction Amount" => 1,
						"Base Amount" => 1,
						"Description" => 0,
						"Currency Rate" => 1,
						"Tax Calculation Method" => 0,
						"Header Tax Schedule Code" => 0,
						"Detail Tax Schedule Code" => 0,
						"Transaction Amount (Tax Incl)" => 1,
						"Transaction Amount In Base Currency (Tax Incl)" => 1,
						"Bank Amount" => 1,
						"dummy" => 0);
	var $ScreenColAP = array("Reference Number 3" => 0,
						"Serial Number" => 0,
						"Party Code" => 0,
						"Reference Number 1" => 0,
						"Transaction Date" => 0,
						"Item Number" => 1,
						"GL Account Code" => 0,
						"Transaction Amount" => 1,
						"Base Amount" => 1,
						"Description" => 0,
						"Tax Calculation Method" => 0,
						"Detail Tax Schedule Code" => 0,
						"Transaction Amount (Tax Incl)"  => 1,
						"Transaction Amount In Base Currency (Tax Incl)" => 1,
						"dummy" => 0);
	var $ScreenColAR = array("Reference Number 3" => 0,
						"Serial Number" => 0,
						"Reference Number 1" => 0,
						"Transaction Date" => 0,
						"Item Number" => 0,
						"GL Account Code" => 0,
						"Currency Code" => 0,
						"Transaction Amount" => 1,
						"Base Amount" => 1,
						"Description" => 0,
						"Currency Rate" => 1,
						"Tax Calculation Method" => 0,
						"Header Tax Schedule Code" => 0,
						"Detail Tax Schedule Code" => 0,
						"Transaction Amount (Tax Incl)" => 1,
						"Transaction Amount In Base Currency (Tax Incl)" => 1,
						"dummy" => 0);

	var $ExportFileName = array(
        "cs" => "%s/BiztrakCashSales%s.csv",
		"ap" => "%s/BiztrakAccountPayable%s.csv",
		"ar" => "%s/BiztrakAccountReceive%s.csv",
		"cn" => "%s/BiztrakCreditNotes%s.csv",
		"dn" => "%s/BiztrakDebitNotes%s.csv"
    );
	
	var $ExportDateFormat = "Ymd";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>false,'cn'=>true);
		else
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>true,'cn'=>true);

		if($config['consignment_modules']){
			$dataType['cs']=false;
			$dataType['dn']=true;
		}

        return array(
			"module_name" => __CLASS__,
			"export_with_sdk"=>self::$export_with_sdk,
			"show_all_column"=>self::$show_all_column,
			"settings"=>  array (
				'bank_account_code' => array(
					"name"=>"Bank Account Code",
					"account"=> array (
						'account_code' => 'BA8010',
						'account_name' => 'CIMB',
					)				
				),
				'sales'=> array(
					"name"=>"Sales",
					"account"=> array (
						'account_code' => 'PT2005',
						'account_name' => 'Sales',
					)
				),
				'credit_note'=>array(
					"name"=>"Credit Note",
					"account"=>array(
						'account_code' => 'PT2005',
						'account_code' => 'Sales',
					)
				),
				'debit_note'=>array(
					"name"=>"Debit Note",
					"account"=>array(
						'account_code' => 'PT2005',
						'account_code' => 'Sales',
					)
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
			case 'cn':
			case 'cs':
					$this->tvExportCol = $this->ScreenColCS;
				break;
			case 'ap':
			case 'dn':
					$this->tvExportCol = $this->ScreenColAP;
				break;
			case 'ar':
					$this->tvExportCol = $this->ScreenColAR;
				break;
			default:
					$this->tvExportCol = array();
				break;
		}
	}	
	
	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$tmpSalesDb->sql_query("select count(*) as total from ".$this->tmpTable);
		$total = $tmpSalesDb->sql_fetchrow();
		$tmpSalesDb->sql_freeresult();
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeader);

			switch(strtolower($groupBy))
			{
				case 'daily summary':					
					$ret = $tmpSalesDb->sql_query("select pos_date,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  group by pos_date
												  order by pos_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['sales']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $this->create_batch_no(date("ymd",strtotime($result['pos_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TaxAmount),2) as TaxAmount,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."												  
												  where pos_date = ".ms($result['pos_date'])."
												  group by tax_code");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{							
							$upd["reference number_3"] = "CS";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = 1;
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}						
							$upd["bank_amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							my_fputcsv($fp, $upd);
							unset($upd);
							
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;					
				case 'monthly summary':
					$ret = $tmpSalesDb->sql_query("select ym, tax_code,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TaxAmount),2) as TaxAmount,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  group by ym, tax_code
												  order by ym");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['sales']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($result['ym']))));
						if(strtotime($date) > strtotime($dateTo)){
							$date = $dateTo;
						}
						$batchNo = $this->create_batch_no(date("Ym",strtotime($result['ym'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."												  
												  where ym = ".ms($result['ym'])."
												  group by tax_code");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{							
							$upd["reference number_3"] = "CS";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$date);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = 1;
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}						
							$upd["bank_amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							my_fputcsv($fp, $upd);
							unset($upd);
							
						}			
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'itemize':
					$ret = $tmpSalesDb->sql_query("select receipt_no, pos_date,
												sum(TotalAmount) as TotalAmount										  
												from ".$this->tmpTable."
												group by pos_date, receipt_no
												order by pos_date, receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$retItem = $tmpSalesDb->sql_query("select sku_description, tax_code,
												ItemAmount,
												TaxAmount,
												TotalAmount,
												from ".$this->tmpTable."
												where pos_date=".ms($result['pos_date'])."
												and receipt_no = ".ms($result['receipt_no']));
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$salesAcc = $this->accSettings['sales']['account'];
							$bankAcc = $this->accSettings['bank_account_code']['account'];				
							$upd["reference number_3"] = "CS";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $result['receipt_no'];
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = $resultItem['sku_description'];
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = 1;
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}						
							$upd["bank_amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							my_fputcsv($fp, $upd);
							unset($upd);
							$itemNo++;							
						}						
						$tmpSalesDb->sql_freeresult($retItem);
						unset($upd);
						$bn++;
					}					
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'receipt':
					$ret = $tmpSalesDb->sql_query("select receipt_no, pos_date,
												  sum(ItemAmount) as ItemAmount,
												  sum(TaxAmount) as TaxAmount,
												  sum(TotalAmount) as TotalAmount												  
												  from ".$this->tmpTable."
												  group by pos_date, receipt_no
												  order by pos_date, receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$retItem = $tmpSalesDb->sql_query("select tax_code,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount
												from ".$this->tmpTable."
												where pos_date= ".ms($result['pos_date'])."
												and receipt_no = ".ms($result['receipt_no'])."
												group by tax_code");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$salesAcc = $this->accSettings['sales']['account'];
							$bankAcc = $this->accSettings['bank_account_code']['account'];				
							$upd["reference number_3"] = "CS";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $result['receipt_no'];
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = 1;
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}						
							$upd["bank_amount"] = $this->selling_price_currency_format($result['TotalAmount']);
							my_fputcsv($fp, $upd);
							unset($upd);
							$itemNo++;							
						}
						$bn++;
						$tmpSalesDb->sql_freeresult($retItem);
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
		$tmpSalesDb->sql_query("select count(*) as total from ".$this->tmpTable);
		$total = $tmpSalesDb->sql_fetchrow();
		$tmpSalesDb->sql_freeresult();
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeaderAP);

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $tmpSalesDb->sql_query("select vendor_code, inv_date, gl_code,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												group by inv_date, vendor_code
												order by inv_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{						
						$batchNo = sprintf($LANG['SET_RECEIPT_NO_PREFIX'],date("ymd",strtotime($result['inv_date'])),sprintf("%04d",$bn));
						$retItem = $tmpSalesDb->sql_query("select taxCode, vendor_terms,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where vendor_code = ".ms($result['vendor_code'])."
												and inv_date = ".ms($result['inv_date'])."
												group by taxCode");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = $result['vendor_code'];
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['inv_date']);
							$upd["bank_code"] = "";
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $result['gl_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);;
							$upd["description"] = "Purchase";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = $resultItem['vendor_terms'];
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_level_1"] = "";
							$upd["custom_text_level_2"] = "";
							$upd["custom_text_level_3"] = "";
							$upd["custom_text_level_4"] = "";
							$upd["custom_text_level_5"] = "";
							$upd["custom_text_level_6"] = "";
							$upd["custom_text_level_7"] = "";
							$upd["custom_text_level_8"] = "";
							$upd["custom_text_level_9"] = "";
							$upd["rcustom_text_level_1"] = "";
							$upd["rcustom_text_level_2"] = "";
							$upd["rcustom_text_level_3"] = "";
							$upd["rcustom_text_level_4"] = "";
							$upd["rcustom_text_level_5"] = "";
							$upd["rcustom_text_level_6"] = "";
							$upd["rcustom_text_level_7"] = "";
							$upd["rcustom_text_level_8"] = "";
							$upd["rcustom_text_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = "";
							if($resultItem['taxCode']!="NR")
							{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['taxCode'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							else{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}
							my_fputcsv($fp,$upd);
							unset($upd);
							$itemNo++;
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'monthly summary':
					$ret = $tmpSalesDb->sql_query("select vendor_code, ym, gl_code,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												group by ym, vendor_code
												order by ym");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{						
						$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($result['ym']))));
						if(strtotime($date) > strtotime($dateTo)){
							$date = $dateTo;
						}
						$batchNo = $this->create_batch_no(date("Ym",strtotime($result['ym'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select taxCode,
												vendor_terms,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where vendor_code = ".ms($result['vendor_code'])."
												and ym = ".ms($result['ym'])."
												group by taxCode");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = $result['vendor_code'];
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$date);
							$upd["bank_code"] = "";
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $result['gl_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);;
							$upd["description"] = "Purchase";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = $resultItem['vendor_terms'];
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_level_1"] = "";
							$upd["custom_text_level_2"] = "";
							$upd["custom_text_level_3"] = "";
							$upd["custom_text_level_4"] = "";
							$upd["custom_text_level_5"] = "";
							$upd["custom_text_level_6"] = "";
							$upd["custom_text_level_7"] = "";
							$upd["custom_text_level_8"] = "";
							$upd["custom_text_level_9"] = "";
							$upd["rcustom_text_level_1"] = "";
							$upd["rcustom_text_level_2"] = "";
							$upd["rcustom_text_level_3"] = "";
							$upd["rcustom_text_level_4"] = "";
							$upd["rcustom_text_level_5"] = "";
							$upd["rcustom_text_level_6"] = "";
							$upd["rcustom_text_level_7"] = "";
							$upd["rcustom_text_level_8"] = "";
							$upd["rcustom_text_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = "";
							if($resultItem['taxCode']!="NR")
							{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['taxCode'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							else{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}
							my_fputcsv($fp,$upd);
							unset($upd);
							$itemNo++;
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'itemize':					
					$ret = $tmpSalesDb->sql_query("select vendor_code, inv_date, gl_code, inv_no,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												group by inv_date, vendor_code, inv_no
												order by inv_date, inv_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{						
						$batchNo = $result['inv_no'];
						$retItem = $tmpSalesDb->sql_query("select taxCode,
												vendor_terms,
												sku_desc,
												round(ItemAmount,2) as ItemAmount,
												round(TaxAmount,2) as TaxAmount,
												round(TotalAmount,2) as TotalAmount
												from ".$this->tmpTable."
												where vendor_code = ".ms($result['vendor_code'])."
												and inv_date = ".ms($result['inv_date'])."
												and inv_no = ".ms($result['inv_no']));
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = $result['vendor_code'];
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['inv_date']);
							$upd["bank_code"] = "";
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $result['gl_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);;
							$upd["description"] = $resultItem['sku_desc'];
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = $resultItem['vendor_terms'];
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_level_1"] = "";
							$upd["custom_text_level_2"] = "";
							$upd["custom_text_level_3"] = "";
							$upd["custom_text_level_4"] = "";
							$upd["custom_text_level_5"] = "";
							$upd["custom_text_level_6"] = "";
							$upd["custom_text_level_7"] = "";
							$upd["custom_text_level_8"] = "";
							$upd["custom_text_level_9"] = "";
							$upd["rcustom_text_level_1"] = "";
							$upd["rcustom_text_level_2"] = "";
							$upd["rcustom_text_level_3"] = "";
							$upd["rcustom_text_level_4"] = "";
							$upd["rcustom_text_level_5"] = "";
							$upd["rcustom_text_level_6"] = "";
							$upd["rcustom_text_level_7"] = "";
							$upd["rcustom_text_level_8"] = "";
							$upd["rcustom_text_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = "";
							if($resultItem['taxCode']!="NR")
							{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['taxCode'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							else{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}
							my_fputcsv($fp,$upd);
							unset($upd);
							$itemNo++;
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'receipt':			
					$ret = $tmpSalesDb->sql_query("select vendor_code, inv_date, gl_code, inv_no,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												group by inv_date, vendor_code, inv_no
												order by inv_date, inv_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$batchNo = $result['inv_no'];
						$retItem = $tmpSalesDb->sql_query("select taxCode,
												vendor_terms,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where vendor_code = ".ms($result['vendor_code'])."
												and inv_date = ".ms($result['inv_date'])."
												and inv_no = ".ms($result['inv_no'])."
												group by taxCode");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = $result['vendor_code'];
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['inv_date']);
							$upd["bank_code"] = "";
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $result['gl_code'];
							$upd["currency_code"] = "";
							$upd["transaction_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);;
							$upd["description"] = "Purchase";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = $resultItem['vendor_terms'];
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_level_1"] = "";
							$upd["custom_text_level_2"] = "";
							$upd["custom_text_level_3"] = "";
							$upd["custom_text_level_4"] = "";
							$upd["custom_text_level_5"] = "";
							$upd["custom_text_level_6"] = "";
							$upd["custom_text_level_7"] = "";
							$upd["custom_text_level_8"] = "";
							$upd["custom_text_level_9"] = "";
							$upd["rcustom_text_level_1"] = "";
							$upd["rcustom_text_level_2"] = "";
							$upd["rcustom_text_level_3"] = "";
							$upd["rcustom_text_level_4"] = "";
							$upd["rcustom_text_level_5"] = "";
							$upd["rcustom_text_level_6"] = "";
							$upd["rcustom_text_level_7"] = "";
							$upd["rcustom_text_level_8"] = "";
							$upd["rcustom_text_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = "";
							if($resultItem['taxCode']!="NR")
							{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['taxCode'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							else{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}
							my_fputcsv($fp,$upd);
							unset($upd);
							$itemNo++;
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$tmpSalesDb->sql_query("select count(*) as total from ".$this->tmpTable);
		$total = $tmpSalesDb->sql_fetchrow();
		$tmpSalesDb->sql_freeresult();
		if($total['total']>0)
		{
			if(file_exists($this->tmpReceiverFile)) unlink($this->tmpReceiverFile);
			$fp = fopen($this->tmpReceiverFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeaderAR);

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $tmpSalesDb->sql_query("select do_date,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  group by do_date
												  order by do_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $this->create_batch_no(date("ymd",strtotime($result['do_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, customer_code, foreign_currency_code,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TotalAmount),2) as TotalAmount,
												  round(sum(foreign_currency_amount),2) as ItemFAmount,
												  round(sum(foreign_currency_gst_amount),2) as TotalFAmount
												  from ".$this->tmpTable."
												  where do_date = ".ms($result['do_date'])."
												  group by customer_code, tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "SI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $resultItem['customer_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales Invoice";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "DI";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							my_fputcsv($fp, $upd);
							unset($upd);

						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'monthly summary':
										$ret = $tmpSalesDb->sql_query("select ym
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  group by ym
												  order by ym");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($result['ym']))));
						if(strtotime($date) > strtotime($dateTo)){
							$date = $dateTo;
						}
						$batchNo = $this->create_batch_no(date("Ym",strtotime($result['ym'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, customer_code, foreign_currency_code,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TotalAmount),2) as TotalAmount,
												  round(sum(foreign_currency_amount),2) as ItemFAmount,
												  round(sum(foreign_currency_gst_amount),2) as TotalFAmount
												  from ".$this->tmpTable."
												  where ym = ".ms($result['ym'])."
												  group by customer_code, tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "SI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$date);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $resultItem['customer_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales Invoice";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "DI";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							my_fputcsv($fp, $upd);
							unset($upd);

						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'itemize':
					$ret = $tmpSalesDb->sql_query("select inv_no, do_date,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												group by do_date, inv_no
												order by do_date, inv_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$retItem = $tmpSalesDb->sql_query("select  customer_code, tax_code, sku_description, currencyrate,
														  foreign_currency_code, ItemAmount, TotalAmount,
														foreign_currency_amount as ItemFAmount,
														foreign_currency_gst_amount as TotalFAmount
														from ".$this->tmpTable."
														where do_date=".ms($result['do_date'])."
														and inv_no = ".ms($result['inv_no']));
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$bankAcc = $this->accSettings['bank_account_code']['account'];
							$upd["reference number_3"] = "SI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $result['inv_no'];
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $resultItem['customer_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = $resultItem['sku_description'];
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "LE";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}
							my_fputcsv($fp, $upd);
							unset($upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						unset($upd);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'receipt':
					$ret = $tmpSalesDb->sql_query("select inv_no, do_date,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  group by do_date, inv_no
												  order by do_date, inv_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$retItem = $tmpSalesDb->sql_query("select customer_code, tax_code, currencyrate, foreign_currency_code,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TotalAmount),2) as TotalAmount,
												round(sum(foreign_currency_amount),2) as ItemFAmount,
												round(sum(foreign_currency_gst_amount),2) as TotalFAmount
												from ".$this->tmpTable."
												where do_date= ".ms($result['do_date'])."
												and inv_no = ".ms($result['inv_no'])."
												group by customer_code, tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$bankAcc = $this->accSettings['bank_account_code']['account'];
							$upd["reference number_3"] = "SI";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $result['inv_no'];
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $resultItem['customer_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']);
							$upd["base_amount"] = $this->selling_price_currency_format($resultItem['ItemAmount']);
							$upd["description"] = "Sales Invoice";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = 0;
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = "";
							$upd["reference_number_4"] = "";
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = 0;
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							if(trim($resultItem['tax_code'])== "")
							{
								$upd["tax_calculation_method"] = "";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = "";
								$upd["transaction_amount_(tax_incl)"] = "";
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = "";
							}else{
								$upd["tax_calculation_method"] = "DI";
								$upd["header_tax_schedule_code"] = "";
								$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
								$upd["transaction_amount_(tax_incl)"] = $this->selling_price_currency_format(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']);
								$upd["transaction_amount_in_base_currency_(tax_incl)"] = $this->selling_price_currency_format($resultItem['TotalAmount']);
							}
							my_fputcsv($fp, $upd);
							unset($upd);
							$itemNo++;
						}

						$tmpSalesDb->sql_freeresult($retItem);
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

	function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$tmpSalesDb->sql_query("select count(*) as total from ".$this->tmpTable);
		$total = $tmpSalesDb->sql_fetchrow();
		$tmpSalesDb->sql_freeresult();
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeader);

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $tmpSalesDb->sql_query("select pos_date as cn_date, currencyrate, foreign_currency_code,
												    goods_return_reason as remark, return_date, cash_refund,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by cn_date, return_date, cash_refund, remark
													order by cn_date, return_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['credit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $this->create_batch_no(date("ymd",strtotime($result['cn_date'])),$bn);
						$returnBatchNo = $this->create_batch_no(date("ymd",strtotime($result['return_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(foreign_currency_amount) as ItemFAmount,
														sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
														sum(foreign_currency_gst_amount) as TotalFAmount
														from ".$this->tmpTable."
														where cash_refund = ".ms($result['cash_refund'])."
														and goods_return_reason = ".ms($result['remark'])."
														and pos_date = ".ms($result['cn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "CP";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['cn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Credit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $result['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];;
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							$upd["bank_amount"] =  $this->selling_price_currency_format(abs($result['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'monthly summary':
					$ret = $tmpSalesDb->sql_query("select ym as cn_date, currencyrate, foreign_currency_code,
												    goods_return_reason as remark, return_date, cash_refund,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by cn_date, return_date, cash_refund, remark
													order by cn_date, return_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['credit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($result['cn_date']))));
						if(strtotime($date) > strtotime($dateTo)){
							$date = $dateTo;
						}
						$batchNo = $this->create_batch_no(date("Ym",strtotime($result['cn_date'])),$bn);
						$returnBatchNo = $this->create_batch_no(date("ym",strtotime($result['return_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(foreign_currency_amount) as ItemFAmount,
														sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
														sum(foreign_currency_gst_amount) as TotalFAmount
														from ".$this->tmpTable."
														where cash_refund = ".ms($result['cash_refund'])."
														and goods_return_reason = ".ms($result['remark'])."
														and ym = ".ms($result['cn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "CP";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$date);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Credit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $result['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];;
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							$upd["bank_amount"] =  $this->selling_price_currency_format(abs($result['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'itemize':
					$ret = $tmpSalesDb->sql_query("select pos_date as cn_date, currencyrate, foreign_currency_code,
													credit_note_no,	receipt_no as return_receipt_no, return_date,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by cn_date, return_date, credit_note_no, return_receipt_no
													order by cn_date, return_date, credit_note_no, return_receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['credit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $result['credit_note_no'];
						$returnBatchNo = $result['return_receipt_no'];
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
															sku_description, goods_return_reason as remark,
															ItemAmount as ItemAmount,
															TaxAmount as TaxAmount,
															TotalAmount as TotalAmount,
															foreign_currency_amount as ItemFAmount,
															foreign_currency_gst_amount-foreign_currency_amount as TaxFAmount,
															foreign_currency_gst_amount as TotalFAmount
															from ".$this->tmpTable."
															where credit_note_no = ".ms($result['credit_note_no'])."
															and receipt_no = ".ms($result['return_receipt_no'])."
															and pos_date = ".ms($result['cn_date'])."
															and return_date = ".ms($result['return_date']));
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "CP";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['cn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = $resultItem['sku_description'];
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $resultItem['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];;
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							$upd["bank_amount"] =  $this->selling_price_currency_format(abs($result['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'receipt':
					$ret = $tmpSalesDb->sql_query("select pos_date as cn_date, currencyrate, foreign_currency_code,
													credit_note_no, return_date,
													receipt_no as return_receipt_no,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by cn_date, return_date, credit_note_no, return_receipt_no
													order by cn_date, return_date, credit_note_no, return_receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['credit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $result['credit_note_no'];
						$returnBatchNo = $result['return_receipt_no'];
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
														ItemAmount as ItemAmount,
														TaxAmount as TaxAmount,
														TotalAmount as TotalAmount,
														foreign_currency_amount as ItemFAmount,
														foreign_currency_gst_amount-foreign_currency_amount as TaxFAmount,
														foreign_currency_gst_amount as TotalFAmount,
														goods_return_reason as remark
														from ".$this->tmpTable."
														where credit_note_no = ".ms($result['credit_note_no'])."
														and receipt_no = ".ms($result['return_receipt_no'])."
														and pos_date = ".ms($result['cn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate, remark");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "CP";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['cn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Credit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $resultItem['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];;
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							$upd["bank_amount"] =  $this->selling_price_currency_format(abs($result['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$tmpSalesDb->sql_query("select count(*) as total from ".$this->tmpTable);
		$total = $tmpSalesDb->sql_fetchrow();
		$tmpSalesDb->sql_freeresult();
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeaderAP);

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $tmpSalesDb->sql_query("select pos_date as dn_date, currencyrate, foreign_currency_code,
													goods_return_reason as remark, return_date, cash_refund,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by dn_date, return_date, cash_refund, remark
													order by dn_date, return_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['debit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $this->create_batch_no(date("ymd",strtotime($result['dn_date'])),$bn);
						$returnBatchNo = $this->create_batch_no(date("ymd",strtotime($result['return_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(foreign_currency_amount) as ItemFAmount,
														sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
														sum(foreign_currency_gst_amount) as TotalFAmount
														from ".$this->tmpTable."
														where cash_refund = ".ms($result['cash_refund'])."
														and goods_return_reason = ".ms($result['remark'])."
														and pos_date = ".ms($result['dn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VD";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['dn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Debit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $result['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'monthly summary':
					$ret = $tmpSalesDb->sql_query("select ym as dn_date, currencyrate, foreign_currency_code,
													goods_return_reason as remark, return_date, cash_refund,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by dn_date, return_date, cash_refund, remark
													order by dn_date, return_date");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['debit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($result['dn_date']))));
						if(strtotime($date) > strtotime($dateTo)){
							$date = $dateTo;
						}
						$batchNo = $this->create_batch_no(date("Ym",strtotime($result['dn_date'])),$bn);
						$returnBatchNo = $this->create_batch_no(date("ym",strtotime($result['return_date'])),$bn);
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(foreign_currency_amount) as ItemFAmount,
														sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
														sum(foreign_currency_gst_amount) as TotalFAmount
														from ".$this->tmpTable."
														where cash_refund = ".ms($result['cash_refund'])."
														and goods_return_reason = ".ms($result['remark'])."
														and ym = ".ms($result['dn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VD";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$date);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Debit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $result['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'itemize':
					$ret = $tmpSalesDb->sql_query("select pos_date as dn_date, currencyrate, foreign_currency_code,
													debit_note_no, receipt_no as return_receipt_no, return_date,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by dn_date, return_date, debit_note_no, return_receipt_no
													order by dn_date, return_date, debit_note_no, return_receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['debit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $result['debit_note_no'];
						$returnBatchNo = $result['return_receipt_no'];
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code,
															sku_description, goods_return_reason as remark,
															ItemAmount as ItemAmount,
															TaxAmount as TaxAmount,
															TotalAmount as TotalAmount,
															foreign_currency_amount as ItemFAmount,
															foreign_currency_gst_amount-foreign_currency_amount as TaxFAmount,
															foreign_currency_gst_amount as TotalFAmount,
															from ".$this->tmpTable."
															where debit_note_no = ".ms($result['debit_note_no'])."
															and receipt_no = ".ms($result['return_receipt_no'])."
															and pos_date = ".ms($result['dn_date'])."
															and return_date = ".ms($result['return_date']));
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VD";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['dn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = $resultItem['sku_description'];
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $resultItem['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case 'receipt':
					$ret = $tmpSalesDb->sql_query("select pos_date as dn_date, currencyrate, foreign_currency_code,
													debit_note_no, return_date, receipt_no as return_receipt_no,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(foreign_currency_amount) as ItemFAmount,
													sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
													sum(foreign_currency_gst_amount) as TotalFAmount
													from ".$this->tmpTable."
													group by dn_date, return_date, debit_note_no, return_receipt_no
													order by dn_date, return_date, debit_note_no, return_receipt_no");
					$bn = 1;
					while($result = $tmpSalesDb->sql_fetchrow($ret))
					{
						$salesAcc = $this->accSettings['debit_note']['account'];
						$bankAcc = $this->accSettings['bank_account_code']['account'];
						$batchNo = $result['debit_note_no'];
						$returnBatchNo = $result['return_receipt_no'];
						$retItem = $tmpSalesDb->sql_query("select tax_code, currencyrate, foreign_currency_code, goods_return_reason as remark,
														ItemAmount as ItemAmount,
														TaxAmount as TaxAmount,
														TotalAmount as TotalAmount,
														foreign_currency_amount as ItemFAmount,
														foreign_currency_gst_amount-foreign_currency_amount as TaxFAmount,
														foreign_currency_gst_amount as TotalFAmount
														from ".$this->tmpTable."
														where debit_note_no = ".ms($result['debit_note_no'])."
														and receipt_no = ".ms($result['return_receipt_no'])."
														and pos_date = ".ms($result['dn_date'])."
														and return_date = ".ms($result['return_date'])."
														group by tax_code, foreign_currency_code, currencyrate, remark");
						$itemNo = 1;
						while($resultItem = $tmpSalesDb->sql_fetchrow($retItem))
						{
							$upd["reference number_3"] = "VD";
							$upd["serial_number"] = $bn;
							$upd["party_code"] = "";
							$upd["cheque_number"] = "";
							$upd["remarks"] = "";
							$upd["reference_number_1"] = $batchNo;
							$upd["transaction_date"] = $this->set_date($this->ExportDateFormat,$result['dn_date']);
							$upd["bank_code"] = $bankAcc['account_code'];
							$upd["item_number"] = $itemNo;
							$upd["gl_account_code"] = $salesAcc['account_code'];
							$upd["currency_code"] = $resultItem['foreign_currency_code'];
							$upd["transaction_amount"] = $this->selling_price_currency_format(abs(($resultItem['ItemFAmount']>0)?$resultItem['ItemFAmount']:$resultItem['ItemAmount']));
							$upd["base_amount"] =  $this->selling_price_currency_format(abs($resultItem['ItemAmount']));
							$upd["description"] = "Debit Note";
							$upd["salesman_code"] = "";
							$upd["freight_amount"] = "";
							$upd["due_date"] = "";
							$upd["credit_term"] = "";
							$upd["reference_number_2"] = $returnBatchNo;
							$upd["reference_number_4"] = $resultItem['remark'];
							$upd["note"] = "";
							$upd["received_date"] = "";
							$upd["bank_charges"] = "";
							$upd["custom_text_(header)_level_1"] = "";
							$upd["custom_text_(header)_level_2"] = "";
							$upd["custom_text_(header)_level_3"] = "";
							$upd["custom_text_(header)_level_4"] = "";
							$upd["custom_text_(header)_level_5"] = "";
							$upd["custom_text_(header)_level_6"] = "";
							$upd["custom_text_(header)_level_7"] = "";
							$upd["custom_text_(header)_level_8"] = "";
							$upd["custom_text_(header)_level_9"] = "";
							$upd["custom_text_(details)_level_1"] = "";
							$upd["custom_text_(details)_level_2"] = "";
							$upd["custom_text_(details)_level_3"] = "";
							$upd["custom_text_(details)_level_4"] = "";
							$upd["custom_text_(details)_level_5"] = "";
							$upd["custom_text_(details)_level_6"] = "";
							$upd["custom_text_(details)_level_7"] = "";
							$upd["custom_text_(details)_level_8"] = "";
							$upd["custom_text_(details)_level_9"] = "";
							$upd["analysis_code_level_1"] = "";
							$upd["analysis_code_level_2"] = "";
							$upd["analysis_code_level_3"] = "";
							$upd["analysis_code_level_4"] = "";
							$upd["analysis_code_level_5"] = "";
							$upd["analysis_code_level_6"] = "";
							$upd["analysis_code_level_7"] = "";
							$upd["analysis_code_level_8"] = "";
							$upd["analysis_code_level_9"] = "";
							$upd["cheque_status"] = "";
							$upd["presented_date"] = "";
							$upd["party_address_1"] = "";
							$upd["party_address_2"] = "";
							$upd["party_address_3"] = "";
							$upd["party_address_4"] = "";
							$upd["party_address_5"] = "";
							$upd["party_postcode"] = "";
							$upd["default_party_address"] = "";
							$upd["currency_rate"] = $resultItem['currencyrate'];
							$upd["tax_calculation_method"] = "LE";
							$upd["header_tax_schedule_code"] = "";
							$upd["detail_tax_schedule_code"] = $resultItem['tax_code'];
							$upd["transaction_amount_(tax_incl)"] =  $this->selling_price_currency_format(abs(($resultItem['TotalFAmount']>0)?$resultItem['TotalFAmount']:$resultItem['TotalAmount']));
							$upd["transaction_amount_in_base_currency_(tax_incl)"] =  $this->selling_price_currency_format(abs($resultItem['TotalAmount']));
							my_fputcsv($fp,$upd);
							$itemNo++;
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
			}
			fclose($fp);
		}
		return $total['total'];
	}

	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		/*already inside cash sales*/
	}

	function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		//todo
	}
}
?>
