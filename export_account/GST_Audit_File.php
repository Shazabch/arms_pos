<?php
/*
2016-03-07 2:29PM Kee Kee
- Change "ARMS Point of Sales" to "ARMS Software"
- Added new column "GAFV1.0.0" into GAF file
2016-10-21 11:52AM Kee Kee
- Change GAF Format to compatible with Custom new GAF format(GAFV2.0.0)
2016-10-25 15:29 AM Kee Kee
- Change Cutomer code use BRN number instead of use Customer Code
2016-10-25 16:29 PM Kee Kee
- Change currency_code to blank instead "XXX" 
2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php
2017-01-17 15:18 PM Kee Kee
- Fixed Credit Note No no show in GAF file 

2017-01-25 14:58 Qiu Ying
- Bug fixed on Cash Sales should show all transaction include receipt item with 0 amount

2017-02-17 10:00 AM Qiu Ying
- Bug fixed on full tax invoice printed but the GAF does not capture the detail 

2017-02-27 11:27 Qiu Ying
- Bug fixed on GAF generate duplicate transactions

2017-03-16 11:51 AM Qiu Ying
- Bug fixed on GRR Invoice with non GST vendor does not show up in GAF
*/
include_once("ExportModule.php");
class GST_Audit_File extends ExportModule
{
	const NAME="GST Audit File (GAF)";
	static $export_with_sdk = false;
	static $show_all_column = false;
	static $groupby = array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array();
	var $tmpGAFFile = null;
	var $purchase_count = 0;
	var $purchase_amount = 0;
	var $purchase_gst_amount = 0;
	var $supply_count = 0;
	var $supply_amount = 0;
	var $supply_gst_amount = 0;
	var $ExportFileName = array(
        "gaf" => "%s/gaf_%s.txt",
    );
	var $ExportDateFormat = "d/m/Y";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>false,'cn'=>false);
		else
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>true);

		if($config['consignment_modules']){
			$dataType['cs']=false;
		}

        return array(
			"module_name" => __CLASS__,
			"export_with_sdk"=>self::$export_with_sdk,
			"show_all_column"=>self::$show_all_column,
			"settings"=>array(
				"customer_code"=>array(
					"name"=>"Customer Code",
					"account"=>array(
						"account_code"=>"CASH",
						"account_name"=>"CASH"
					),
					"help"=>"For POS Cash Sales only"
				),
			),
			"dataType"=> $dataType,
			"groupby"=>self::$groupby
		);
    }

	protected function replace_separator_in_string($str)
	{
		return $this->replace_separator($str,"|","txt");
	}

	function preset_account_column($dataType)
	{
		/*GAF FILE use text view show data*/
		$this->tvExportCol = array();
	}

	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			$fp = fopen($this->tmpFile, 'a');
			$upd['record_identifier'] = "S";
			$upd['customer_name'] = "S2_CustomerName";
			$upd['customer_id'] = "S3_CustomerBRN";
			$upd['customer_gst'] = "S4_CustomerGSTNo";
			$upd['pos_date'] = "S5_InvoiceDate";
			$upd['inv_no'] = "S6_InvoiceNo";
			$upd['exportK2No'] = "S7_ExportK2No";
			$upd['line_no'] = "S8_LineNo";
			$upd['product_desc'] = "S9_ProductDescription";
			$upd['sales'] = "S10_SValueMYR";
			$upd['gst_amount'] = "S11_SGSTValueMYR";
			$upd['tax_code'] = "S12_TaxCode";
			$upd['country'] = "S13_Country";
			$upd['currency_code'] = "S14_FCYCode";
			$upd['currency_amount'] = "S15_SValueFCY";
			$upd['currency_gst_amount'] = "S16_SGSTValueFCY";
			$upd['dummy'] = "";
			my_fputcsv($fp,$upd,"|","");
			unset($upd);	
			$ret = $this->sql_query($tmpSalesDb, "select doc_no, ref_no, customer_code,
										  customer_name, pos_date, tax_code,customer_remark,
										  sum(ItemAmount) as ItemAmount,
										  sum(TaxAmount) as TaxAmount,
										  description as sku_description
										  from ".$this->tmpTable."
										  where tax_code != ''
										  group by pos_date, ref_no, arms_code
										  order by pos_date, tablename, ref_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				if(!isset($oldRefNo) || $oldRefNo!=$result['ref_no'])
				{
					$oldRefNo = $result['ref_no'];
					$bn = 1;
				}
				
				$batchNo = sprintf($LANG['SET_RECEIPT_NO_PREFIX'],date("ymd",strtotime($result['pos_date'])),sprintf("%04d",$bn));
				$upd['record_identifier'] = "S";
				if(trim($result['customer_remark'])!="")
				{
					$customer_remark = unserialize($result['customer_remark']);
					//Set BRN(Business Register Number) as customer_id
					$upd['customer_name'] = $this->replace_separator_in_string((isset($customer_remark['Name'])?$customer_remark['Name']:""));
					$upd['customer_id'] = $this->replace_separator_in_string((isset($customer_remark['BRN'])?$customer_remark['BRN']:""));
					$upd['customer_gst'] = $this->replace_separator_in_string((isset($customer_remark['GST Reg No'])?$customer_remark['GST Reg No']:""));

				}else{
					$upd['customer_name'] = $this->replace_separator_in_string($result['customer_name']);
					$upd['customer_id'] = $this->replace_separator_in_string($result['customer_code']);
					$upd['customer_gst'] = "";//$this->replace_separator_in_string($result['customer_code']);
				}
				$upd['pos_date'] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$upd['inv_no'] = ((isset($result['credit_note_ref_no']) && ($result['credit_note_ref_no'])!="")?$result['credit_note_ref_no']:$result['ref_no']);
				$upd['exportK2No'] = "";
				$upd['line_no'] = $bn;
				$upd['product_desc'] = $result['sku_description'];//'SALES';
				$upd['sales'] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd['gst_amount'] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd['tax_code'] = $this->replace_separator_in_string($result['tax_code']);
				$upd['country'] = "";
				$upd['currency_code'] = "";
				$upd['currency_amount'] = $this->selling_price_currency_format(0);
				$upd['currency_gst_amount'] = $this->selling_price_currency_format(0);
				$upd['dummy'] = "";
				$this->supply_count++;
				$this->supply_amount += $upd['sales'];
				$this->supply_gst_amount += $upd['gst_amount'];
				//file_put_contents($this->tmpFile,implode("|",$upd)."\n",FILE_APPEND);
				my_fputcsv($fp,$upd,"|","");
				unset($upd);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);
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
			$fp = fopen($this->tmpFile, 'a');
			$upd['record_identity'] = "P";
			$upd['vendor_name'] = "P2_SupplierName";
			$upd['vendor_id'] = "P3_SupplierBRN";
			$upd['vendor_gstno'] = "P4_SupplierGSTNo";
			$upd['invoice_date'] = "P5_InvoiceDate";
			$upd['posting_date'] = "P6_PostingDate";
			$upd['invoice_no'] = "P7_InvoiceNo";
			$upd['import_declaration'] = "P8_ImportK1No";//Kastam Form
			$upd['line_number'] = "P9_LineNo";
			$upd['product_desc'] = "P10_ProductDescription";
			$upd['purchase'] = "P11_PValueMYR";
			$upd['purchase_gst_amount'] = "P12_PGSTValueMYR";
			$upd['tax_code'] = "P13_TaxCode";
			$upd['currency_code'] = "P14_FCYCode";
			$upd['currency_amount'] =  "P15_PValueFCY";
			$upd['currency_gst_amount'] =  "P16_PGSTValueFCY";
			$upd['dummy'] = "";
			my_fputcsv($fp,$upd,"|","");
			unset($upd);
			$this->purchase_count++;
			$this->purchase_amount += $this->selling_price_currency_format($result['ItemAmount']);
			$this->purchase_gst_amount += $this->selling_price_currency_format($result['TaxAmount']);
			//file_put_contents($this->tmpFile,implode("|",$upd)."\n",FILE_APPEND);
			
			
			$ret = $this->sql_query($tmpSalesDb, "select inv_no, vendor_name, vendor_brn, 
													vendor_gstno, vendor_id, inv_date, posting_date, taxCode, currency_code,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(currency_amount) as currency_amount,
													sum(currency_gst_amount) as currency_gst_amount										  
													from ".$this->tmpTable."
													group by ym, inv_no, inv_date, vendor_name, taxCode
													order by ym, inv_no");
		
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				if(!isset($oldRefNo) || $oldRefNo!=$result['inv_no'])
				{
					$oldRefNo = $result['inv_no'];
					$bn = 1;
				}
				
				$batchNo = sprintf($LANG['SET_RECEIPT_NO_PREFIX'],date("ymd",strtotime($result['inv_date'])),sprintf("%04d",$bn));
				$upd['record_identity'] = "P";
				$upd['vendor_name'] = $this->replace_separator_in_string($result['vendor_name']);
				$upd['vendor_id'] = $this->replace_separator_in_string($result['vendor_brn']);
				$upd['vendor_gstno'] = $this->replace_separator_in_string($result['vendor_gstno']);
				$upd['posting_date'] = $this->set_date($this->ExportDateFormat,$result['posting_date']);
				$upd['invoice_date'] = $this->set_date($this->ExportDateFormat,$result['inv_date']);
				$upd['invoice_no'] = $result['inv_no'];
				$upd['import_declaration'] = "";//Kastam Form
				$upd['line_number'] = $bn;
				$upd['product_desc'] = "Purchase";
				$upd['purchase'] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd['purchase_gst_amount'] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd['tax_code'] = $this->replace_separator_in_string(strtoupper($result['taxCode']));
				$upd['currency_code'] = "";//strtoupper($result['currency_code']);
				$upd['currency_amount'] =  $this->selling_price_currency_format($result['currency_amount']);
				$upd['currency_gst_amount'] =  $this->selling_price_currency_format($result['currency_gst_amount']);
				$upd['dummy'] = "";
				$this->purchase_count++;
				$this->purchase_amount += $this->selling_price_currency_format($result['ItemAmount']);
				$this->purchase_gst_amount += $this->selling_price_currency_format($result['TaxAmount']);
				//file_put_contents($this->tmpFile,implode("|",$upd)."\n",FILE_APPEND);
				my_fputcsv($fp,$upd,"|","");
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
			$fp = fopen($this->tmpFile, 'a');

			$ret = $this->sql_query($tmpSalesDb, "select do_date, inv_no, customer_code, customer_name, customer_brn, customer_gst_no, account_code, account_name,
                                tax_code, tax_account_code, tax_account_name, currencyrate, foreign_currency_code, terms,
                                round(sum(ItemAmount),2) as ItemAmount,
                                round(sum(TaxAmount),2) as TaxAmount,
                                round(sum(TotalAmount),2) as TotalAmount,
                                round(sum(ItemFAmount),2) as ItemFAmount,
                                round(sum(TaxFAmount),2) as TaxFAmount,
                                round(sum(TotalFAmount),2) as TotalFAmount,
								sku_description
                                from ".$this->tmpTable."
								where tax_code != ''
                                group by do_date, inv_no, arms_code
                                order by do_date, inv_no");
	
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				if(!isset($oldRefNo) || $oldRefNo!=$result['inv_no'])
				{
					$oldRefNo = $result['inv_no'];
					$bn = 1;
				}
				$batchNo = $result['inv_no'];
				$upd['record_identifier'] = "S";
				$upd['customer_name'] = $this->replace_separator_in_string($result['customer_name']);
				$upd['customer_brn'] = $this->replace_separator_in_string($result['customer_brn']);
				$upd['customer_gst_no'] = $this->replace_separator_in_string($result['customer_gst_no']);
				$upd['do_date'] = $this->set_date($this->ExportDateFormat,$result['do_date']);
				$upd['inv_no'] = $batchNo;
				$upd['ExportK2'] = "";
				$upd['line_no'] = $bn;
				$upd['product_desc'] = $result['sku_description'];
				$upd['sales'] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd['gst_amount'] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd['tax_code'] = $this->replace_separator_in_string($result['tax_code']);
				$upd['country'] = "";
				$upd['currency_code'] = ($result['foreign_currency_code']=="XXX"?"":$result['foreign_currency_code']);
				$upd['currency_amount'] = $this->selling_price_currency_format($result['ItemFAmount']);
				$upd['currency_gst_amount'] = $this->selling_price_currency_format($result['TaxFAmount']);
				$upd['dummy'] = "";
				$this->supply_count++;
				$this->supply_amount += $upd['sales'];
				$this->supply_gst_amount += $upd['gst_amount'];
				//file_put_contents($this->tmpReceiverFile,implode("|",$upd)."\n",FILE_APPEND);
				my_fputcsv($fp,$upd,"|","");
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
		//Comming Soon :D
	}

	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode){

	}
	
	function get_account_cash_sales_n_credit_note()
	{
		
	}

	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		/*already inside cash sales*/
	}

	function create_gaf_file($current_version=0, $branch_id)
	{
		global $config;
		if(file_exists($this->tmpGAFFile)) unlink($this->tmpGAFFile);

		$company_info=$this->get_company_info($branch_id);
	
		$fp = fopen($this->tmpGAFFile, 'w');
		$upd = array();
		$upd['record_indetity'] = "C";
		$upd['company_name'] = "C2_CompanyName";
		$upd['business_register_number'] = "C3_CompanyBRN";
		$upd['gst_register_number'] = "C4_CompanyGSTNo";
		$upd['period_start'] = "C5_PeriodStart";
		$upd['period_end'] = "C6_PeriodEnd";
		$upd['file_creation_date'] = "C7_GAFCreationDate";
		$upd['product_version'] = "C8_SoftwareVersion";
		$upd['gaf_version'] = "C9_GAFVersion";
		$upd['dummy'] = "";
		my_fputcsv($fp,$upd,"|","");
		unset($upd);
		$upd = array();
		$upd['record_indetity'] = "C";
		$upd['company_name'] = $this->replace_separator_in_string($company_info['gst_company_name']);
		$upd['business_register_number'] = $this->replace_separator_in_string($company_info['gst_company_business_register_number']);
		$upd['gst_register_number'] = $this->replace_separator_in_string($company_info['gst_register_no']);
		$upd['period_start'] = $this->set_date($this->ExportDateFormat,$this->dateFrom);
		$upd['period_end'] = $this->set_date($this->ExportDateFormat,$this->dateTo);
		$upd['file_creation_date'] = date("d/m/Y");
		$upd['product_version'] = sprintf("%s%sL","ARMS Software",$current_version);
		$upd['gaf_version'] = "GAFv2.0";
		$upd['dummy'] = "";
		my_fputcsv($fp,$upd,"|","");
		unset($upd);

		if(file_exists($this->tmpFile)){
          $data=trim(file_get_contents($this->tmpFile));
		  if(!empty($data)) fputs($fp,$data."\r\n");
		}

		$upd['record_identifier'] = "F";
		$upd['purchase_count'] = "F2_CountPRecord";
		$upd['purchase_amount'] = "F3_SumPValueMYR";
		$upd['purchase_gst_amount'] = "F4_SumPGSTValueMYR";
		$upd['supply_count'] = "F5_CountSRecord";
		$upd['supply_amount'] = "F6_SumSValueMYR";
		$upd['supply_gst_amount'] = "F7_SumSGSTValueMYR";
		$upd['ledger_count'] = "F8_CountLRecord";
		$upd['debit_sum'] = "F9_SumLDebit";
		$upd['credit_sum'] = "F10_SumLCredit";
		$upd['balance_sum'] = "F11_SumLCloseBalance";
		$upd['dummy'] = "";
		my_fputcsv($fp,$upd,"|","");
		unset($upd);
		$upd['record_identifier'] = "F";
		$upd['purchase_count'] = intval($this->purchase_count);
		$upd['purchase_amount'] = $this->selling_price_currency_format($this->purchase_amount);
		$upd['purchase_gst_amount'] = $this->selling_price_currency_format($this->purchase_gst_amount);
		$upd['supply_count'] = intval($this->supply_count);
		$upd['supply_amount'] = $this->selling_price_currency_format($this->supply_amount);
		$upd['supply_gst_amount'] = $this->selling_price_currency_format($this->supply_gst_amount);
		$upd['ledger_count'] = 0;
		$upd['debit_sum'] = 0;
		$upd['credit_sum'] = 0;
		$upd['balance_sum'] = 0;
		$upd['dummy'] = "";
		my_fputcsv($fp,$upd,"|","");
		unset($upd);
		fclose($fp);

		/*file_put_contents($this->tmpGAFFile,trim(file_get_contents("tmpHeader"))."\n",FILE_APPEND);
		if(file_exists($this->tmpFile)) file_put_contents($this->tmpGAFFile,trim(file_get_contents($this->tmpFile))."\n",FILE_APPEND);
		if(file_exists($this->tmpFile)) file_put_contents($this->tmpGAFFile,trim(file_get_contents($this->tmpFile))."\n",FILE_APPEND);
		if(file_exists($this->tmpReceiverFile)) file_put_contents($this->tmpGAFFile,trim(file_get_contents($this->tmpReceiverFile))."\n",FILE_APPEND);
		file_put_contents($this->tmpGAFFile,trim(file_get_contents("tmpFooter")),FILE_APPEND);*/
	}

	function export_account_data(){

	}
}
?>
