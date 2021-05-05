<?php
/*
11/9/2017 4:56 PM Andy
- New Accouting Export Format "Auto Count". Only have Purchase and Cash Sales.
*/
include_once("ExportModule.php");
class AutoCount extends ExportModule
{
	const NAME="Auto Count";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Receipt");
	static $version = '1.8.028 rev184';

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array(
								'ap' => array("DocNo", "DocDate", "CreditorCode", "SupplierInvoiceNo", "JournalType", "DisplayTerm", "PurchaseAgent", "Description", "CurrencyRate", "RefNo2", "Note", "InclusiveTax", "AccNo", "ToAccountRate", "DetailDescription", "ProjNo", "DeptNo", "TaxType", "TaxableAmt", "Tax", "TaxAdjustment", "Amount"),
								'cscn' => array("DocNo", "DocDate", "JournalType", "DocNo2", "CurrencyCode", "CurrencyRate", "Note", "InclusiveTax", "AccNo", "ToAccountRate", "ProjNo", "DeptNo", "TaxType", "Description", "FurtherDescription", "RefNo2", "SalesAgent", "TaxBRNo", "TaxBName", "TaxRefNo", "TaxPermitNo", "TaxExportCountry", "DR", "CR", "TaxableDR", "TaxableCR", "TaxAdjustment", "TaxDR", "TaxCR", "SupplyPurchase", "ToTaxCurrencyRate")
							);
	var $ExportFileName = array(
		//"ar" => "%s/million_ar_%s.csv",
		"cscn" => "%s/autocount_cs_%s.csv",
		//"arcn" => "%s/million_ar_%s.csv",
		"ap" => "%s/autocount_ap_%s.csv",
		//"cn" => "%s/million_cn_%s.csv",
		//"dn" => "%s/million_dn_%s.csv",
    );
	var $ExportDateFormat = "d/m/y";
	
	static function get_name() {
        return self::NAME;
    }

	static function get_property() {
		global $config;
		
		//$dataType=array('cs'=>true,'ap'=>true,'ar'=>true,'cn'=>true,'dn'=>true);
		//$dataType=array('cscn'=>true,'arcn'=>true,'ap'=>true,'dn'=>true);
		$dataType=array('ap'=>true, 'cscn'=>true);

        return array(
			"module_name" => __CLASS__,
			"version" => self::$version,
			"settings" => array (
				'purchase' => array (
					"name"=>"Purchase",
					"account"=> array (
						'account_code' => '610-0000',
						'account_name' => 'Purchase',
					)
				),
				'sales' => array (
					"name"=>"Cash Sales",
					"account"=> array (
						'account_code' => '500-1000',
						'account_name' => 'Cash Sales',
					)
				),
				'sales_return' => array (
					"name"=>"Sales Return Inwards",
					"account"=> array (
						'account_code' => '510-0000',
						'account_name' => 'Sales Return Inwards',
					)
				),
				'credit_sales' => array (
					"name"=>"Credit Sales",
					"account"=> array (
						'account_code' => '500-0000',
						'account_name' => 'Credit Sales',
					)
				),
				'cash' => array (
					"name"=>"Cash",
					"account"=>array(
						'account_code' => '320-0000',
						'account_name' => 'Cash In Hand',
					)
				),
				'credit_card' => array (
					"name"=>"Credit Card",
					"account"=>array(
						'account_code' => '310-0000',
						'account_name' => 'Cash at Bank',
					)
				),
				'coupon' => array (
					"name"=>"Coupon",
					"account"=>array(
						'account_code' => '320-0000',
						'account_name' => 'Cash In Hand',
					)
				),
				'voucher' => array(
					"name"=>"Voucher",
					"account"=>array(
						'account_code' => '320-0000',
						'account_name' => 'Cash In Hand',
					)
				),
				'check' => array (
					"name"=>"Check",
					"account"=>array(
						'account_code' => '320-0000',
						'account_name' => 'Cash In Hand',
					)
				),
				'deposit' => array (
					"name"=>"Deposit",
					"account"=>array(
						'account_code' => '440-0000',
						'account_name' => 'Deposit Received',
					)
				),
				'rounding' => array (
					"name"=>"Rounding",
					"account"=>array(
						'account_code' => '500-1000',
						'account_name' => 'Rounding',
					),
				),
				'service_charge' => array (
					"name"=>"Service Charge",
					"account"=>array(
						'account_code' => '500-1000',
						'account_name' => 'Cash Sales',
					)
				),
				'cash_refund' => array (
					"name"=>"Cash Refund",
					"account"=>array(
						'account_code' => '320-0003',
						'account_name' => 'Cash Refund',
					)
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '320-0002',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '320-0003',
						'account_name' => 'Over',
					)
				),
				'goods_exchange_contra' => array (
					"name"=>"Goods Exchange Contra A/C",
					"account"=> array (
						'account_code' => '999-9999',
						'account_name' => 'Goods Exchange Contra A/C',
					)
				),
				'SR' => array (
					"name"=>"SR @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => 'GST-4010',
						'account_name' => 'GST Output Tax',
					)
				),				
				'ZRL' => array (
					"name"=>"ZRL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'ZRL @0%',
					)
				),
				'ZRE' => array (
					"name"=>"ZRE @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'ZRE @0%',
					)
				),
				'ES43' => array (
					"name"=> "ES43 @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'ES43 @0%',
					)
				),
				'DS' => array (
					"name"=>"DS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-4010',
						'account_name' => 'DS @6%',
					)
				),
				'OS' => array (
					"name"=>"OS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'OS @0%',
					)
				),
				'ES' => array (
					"name"=>"ES @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '',
						'account_name' => 'ES @0%',
					)
				),
				'RS' => array (
					"name"=>"RS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'RS @0%',
					),
				),
				'GS' =>  array (
					"name"=>"GS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'GS @0%',
					)
				),
				'AJS' => array (
					"name"=>"AJS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-4010',
						'account_name' => 'AJS @6%',
					)
				),
				'AJP' => array (
					"name"=>"AJP @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'BL' => array (
					"name"=>"BL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-9010',
						'account_name' => 'GST Input Tax',
					)
				),
				'EP' => array (
					"name"=>"EP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'GST Input Tax',
					)
				),
				'GP' => array (
					"name"=> "GP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'GST Input Tax',
					)
				),
				'IM' => array (
					"name"=>"IM @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'IS' => array (
					"name"=>"IS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'GST Input Tax',
					)
				),
				'NR' => array (
					"name"=>"NR @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '',
						'account_name' => 'GST Input Tax',
					)
				),
				'OP' => array (
					"name"=>"OP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'Output Tax',
					),
				),
				'TX' =>  array (
					"name"=>"TX @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-E43' => array (
					"name"=>"TX-E43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-N43' => array (
					"name"=>"TX-N43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-RE' => array (
					"name"=>"TX-RE @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => 'GST-3010',
						'account_name' => 'GST Input Tax',
					)
				),
				'ZP' => array (
					"name"=>"ZP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '',
						'account_name' => 'GST Input Tax',
					)
				),
				// OTHERS
				'purchase_journal_type' => array (
					"name"=>"Purchase Journal Type",
					"data"=>'Purchase'
				),
				'sales_journal_type' => array (
					"name"=>"Sales Journal Type",
					"data"=>'Sales'
				),
				'proj_no' => array (
					"name"=>"Project No.",
					"data"=>''
				),
			),
			"dataType"=> $dataType,
			"groupby"=>self::$groupby
		);
    }

	
	function get_account_payable($tmpSalesDb,$groupBy,$dateTo)
	{
		global $LANG, $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			$purchaseTmp = array();
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeader['ap']);
	
			$ret = $this->sql_query($tmpSalesDb, "select t.batchno, t.gl_code, t.gl_name, t.vendor_terms, t.vendor_code, t.vendor_name, t.inv_date, t.inv_no,
										  t.ItemAmount, t.TaxAmount, t.TotalAmount, t.taxCode, t.second_tax_code
										  from ".$this->tmpTable." t
										  order by t.inv_date, t.inv_no, t.taxCode");

			$last_inv_no = '';
			
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
				$batchNo = $result['batchno'];
				$InvDate = $this->set_date($this->ExportDateFormat, $result['inv_date']);
				$docNo = $result['inv_no'];
			
				$upd = array();
				
				if($last_inv_no != $result['inv_no']){
					// need master
					$upd['DocNo'] = $result['inv_no'];
					$upd['DocDate'] = $InvDate;
					$upd['CreditorCode'] = $result['vendor_code'];
					$upd['SupplierInvoiceNo'] = $result['inv_no'];
					$upd['JournalType'] = $this->accSettings['purchase_journal_type']['data'];
					$upd['DisplayTerm'] = $result['vendor_terms'];
					$upd['PurchaseAgent'] = '';
					$upd['Description'] = $this->accSettings['purchase']['account']['account_name'];
					$upd['CurrencyRate'] = 1;
					$upd['RefNo2'] = '';
					$upd['Note'] = '';
					$upd['InclusiveTax'] = 'T';
				}else{
					// no need repeat master
					$upd['DocNo'] = '';
					$upd['DocDate'] = '';
					$upd['CreditorCode'] = '';
					$upd['SupplierInvoiceNo'] = '';
					$upd['JournalType'] = '';
					$upd['DisplayTerm'] = '';
					$upd['PurchaseAgent'] = '';
					$upd['Description'] = '';
					$upd['CurrencyRate'] = '';
					$upd['RefNo2'] = '';
					$upd['Note'] = '';
					$upd['InclusiveTax'] = '';
				}
				$upd['AccNo'] = $this->accSettings['purchase']['account']['account_code'];
				$upd['ToAccountRate'] = 1;
				$upd['DetailDescription'] = $this->accSettings['purchase']['account']['account_name'];
				$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
				$upd['DeptNo'] = '';
				$upd['TaxType'] = $result['second_tax_code'];
				$upd['TaxableAmt'] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd['Tax'] = $this->selling_price_currency_format($result['TaxAmount']);
				$upd['TaxAdjustment'] = 0;
				$upd['Amount'] = $this->selling_price_currency_format($result['TotalAmount']);
				
				my_fputcsv($fp, $upd);
				
				$last_inv_no = $result['inv_no'];
			}
			$tmpSalesDb->sql_freeresult($ret);			
			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		global $config;
		
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeader['cscn']);
						
			$empty_master_row = array('','','','','','','','');
			
			//Export Cash Sales From POS Counter
			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, ref_no
													from ".$this->tmpTable." 
													where tablename = ".ms("pos")."
													group by pos_date, doc_no order by pos_date,doc_no");									

			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				// Master Details
				$master_row = array();
				$master_row['DocNo'] = $result['doc_no'];
				$master_row['DocDate'] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$master_row['JournalType'] = $this->accSettings['sales_journal_type']['data'];
				$master_row['DocNo2'] = '';
				$master_row['CurrencyCode'] = $config["arms_currency"]["code"];
				$master_row['CurrencyRate'] = 1;
				$master_row['Note'] = '';
				$master_row['InclusiveTax'] = 'T';
				$last_doc_no = '';
				
				//Return Sales Transaction
				$wcond = array();
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`type`!=".ms('deposit');
				$wcond[] = "`is_credit_notes`=".ms(1);
			
				$retCN = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, credit_note_no,is_refund
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no,credit_note_no");
				unset($wcond);
				if($tmpSalesDb->sql_numrows($retCN)>0)
				{
					while($resultCN = $this->sql_fetchrow($tmpSalesDb, $retCN))
					{
						//Get Credit Note Debit Information
						$wcond = array();
						$wcond[] = "`type`=".ms("debit");
						$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
						$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
						$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
						$wcond[] = "`type`!=".ms('deposit');
						$wcond[] = "`acc_type`!=".ms('rounding');
						$wcond[] = "`is_credit_notes`=".ms(1);
						
						$retCNDebit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
											credit_note_no, return_receipt_no, account_code, account_name, tax_code,acc_type,second_tax_code,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount,
											tax_account_code,tax_account_name
											from ".$this->tmpTable."
											where ".implode(" and ",$wcond)."
											group by pos_date, credit_note_no, account_code, account_name,tax_account_code,tax_account_name,tax_code");
						unset($wcond);
						while($resultCNDebit = $this->sql_fetchrow($tmpSalesDb,$retCNDebit))
						{
							if($resultCNDebit["acc_type"] != "cash"){
								$refno = $resultCNDebit['credit_note_no'];
								$refno2 = $resultCNDebit['batchno'];
								
								$amount = abs($resultCNDebit['TotalAmount']);
								$taxAmt = abs($resultCNDebit['TaxAmount']);
								
								$upd = $master_row;
								
								$upd["AccNo"] = $resultCNDebit['account_code'];
								$last_doc_no = $upd['DocNo'] = $refno;	// replace document no using credit note no
								
								$upd['ToAccountRate'] = 1;
								$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
								$upd['DeptNo'] = '';
								$upd['TaxType'] = $resultCNDebit['second_tax_code'];
								$upd["Description"] = $resultCNDebit['account_name'];
								$upd['FurtherDescription'] = '';
								$upd['RefNo2'] = '';
								$upd['SalesAgent'] = '';
								$upd['TaxBRNo'] = '';
								$upd['TaxBName'] = '';
								$upd['TaxRefNo'] = '';
								$upd['TaxPermitNo'] = '';
								$upd['TaxExportCountry'] = '';
								$upd["DR"] = $this->selling_price_currency_format($amount);
								$upd['CR'] = 0;
								$upd['TaxableDR'] = $this->selling_price_currency_format($amount);
								$upd['TaxableCR'] = 0;
								$upd['TaxAdjustment'] = 0;
								$upd["TaxDR"] = $this->selling_price_currency_format($taxAmt);
								$upd["TaxCR"] = 0;
								$upd["SupplyPurchase"] = 'S';
								$upd["ToTaxCurrencyRate"] = 1;
								
								my_fputcsv($fp, $upd);
								unset($upd);
																
								//Get Credit Goods Exchange Contra
								//if(!$resultCN["is_refund"]){
									$upd = $empty_master_row;
									
									$amount = abs($resultCNDebit['TotalAmount']);
									$upd["AccNo"] = $this->accSettings["goods_exchange_contra"]["account"]["account_code"];
									$upd['ToAccountRate'] = 1;
									$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
									$upd['DeptNo'] = '';
									$upd['TaxType'] = '';
									$upd["Description"] = $this->accSettings["goods_exchange_contra"]["account"]["account_name"];
									$upd['FurtherDescription'] = '';
									$upd['RefNo2'] = '';
									$upd['SalesAgent'] = '';
									$upd['TaxBRNo'] = '';
									$upd['TaxBName'] = '';
									$upd['TaxRefNo'] = '';
									$upd['TaxPermitNo'] = '';
									$upd['TaxExportCountry'] = '';
									$upd["DR"] = 0;
									$upd['CR'] = $this->selling_price_currency_format($amount);
									$upd['TaxableDR'] = 0;
									$upd['TaxableCR'] = 0;
									$upd['TaxAdjustment'] = 0;
									$upd["TaxDR"] = 0;
									$upd["TaxCR"] = 0;
									$upd["SupplyPurchase"] = 'S';
									$upd["ToTaxCurrencyRate"] = 1;
								
									my_fputcsv($fp, $upd);
									unset($upd);
								//}
							}
						}
						$tmpSalesDb->sql_freeresult($retCNDebit);
						
						$wcond = array();
						$wcond[] = "`type`=".ms("debit");
						$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
						$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
						$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
						$wcond[] = "`type`!=".ms('deposit');
						$wcond[] = "`acc_type`=".ms('rounding');
						$wcond[] = "`is_credit_notes`=".ms(1);

						$retCNDebit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
											credit_note_no, return_receipt_no, account_code, account_name,doc_no,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where ".implode(" and ",$wcond)."
											group by pos_date, credit_note_no, account_code, account_name");
						unset($wcond);
						if($tmpSalesDb->sql_numrows($retCNDebit)>0)
						{
							while($resultCNDebit = $this->sql_fetchrow($tmpSalesDb,$retCNDebit))
							{
								//$refno = $resultCNDebit['credit_note_no'];
								$refno = $resultCNDebit['doc_no'];
								$refno2 = $resultCNDebit['batchno'];
								
								if($last_doc_no != $refno){
									$upd = $master_row;
									$last_doc_no = $upd["DocNo"] = $refno;
								}else{
									$upd = $empty_master_row;
								}
												
								//$amount = abs($resultCNDebit['TotalAmount']);
								$amount = (0-$resultCNDebit['TotalAmount']);
								$upd["AccNo"] = $resultCNDebit['account_code'];								
								$upd['ToAccountRate'] = 1;
								$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
								$upd['DeptNo'] = '';
								$upd['TaxType'] = '';
								$upd["Description"] = $resultCNDebit['account_name'];
								$upd['FurtherDescription'] = '';
								$upd['RefNo2'] = '';
								$upd['SalesAgent'] = '';
								$upd['TaxBRNo'] = '';
								$upd['TaxBName'] = '';
								$upd['TaxRefNo'] = '';
								$upd['TaxPermitNo'] = '';
								$upd['TaxExportCountry'] = '';
								$upd["DR"] = $amount>0 ? $this->selling_price_currency_format($amount) : 0;
								$upd["CR"] = $amount<0 ? $this->selling_price_currency_format(abs($amount)) : 0;
								$upd['TaxableDR'] = 0;
								$upd['TaxableCR'] = 0;
								$upd['TaxAdjustment'] = 0;
								$upd["TaxDR"] = 0;
								$upd["TaxCR"] = 0;
								$upd["SupplyPurchase"] = 'S';
								$upd["ToTaxCurrencyRate"] = 1;
								
								my_fputcsv($fp, $upd);
								unset($upd);
							}
						}
						$tmpSalesDb->sql_freeresult($retCNDebit);
						
						//Get Credit Note Credit Information
						$wcond = array();
						$wcond[] = "`type`=".ms("credit");
						$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
						$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
						$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
						$wcond[] = "`type`!=".ms('deposit');
						$wcond[] = "`is_credit_notes`=".ms(1);
			
						$retCNCredit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, 									
											credit_note_no, return_receipt_no, account_code, account_name,acc_type,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where ".implode(" and ",$wcond)."
											group by pos_date, credit_note_no, account_code, account_name");
						unset($wcond);
						if($tmpSalesDb->sql_numrows($resultCNCredit)>0)
						{
							while($resultCNCredit = $this->sql_fetchrow($tmpSalesDb,$retCNCredit))
							{
								$refno = $resultCNCredit['doc_no'];
								$refno2 = $resultCNCredit['batchno'];
								
								if($last_doc_no != $refno){
									$upd = $master_row;
									$last_doc_no = $upd["DocNo"] = $refno;
								}else{
									$upd = $empty_master_row;
								}
								
								$amount = $resultCNCredit['TotalAmount'];
								
								$upd["AccNo"] = $resultCNCredit['account_code'];								
								$upd['ToAccountRate'] = 1;
								$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
								$upd['DeptNo'] = '';
								$upd['TaxType'] = '';
								$upd["Description"] = $resultCNCredit['account_name'];
								$upd['FurtherDescription'] = '';
								$upd['RefNo2'] = '';
								$upd['SalesAgent'] = '';
								$upd['TaxBRNo'] = '';
								$upd['TaxBName'] = '';
								$upd['TaxRefNo'] = '';
								$upd['TaxPermitNo'] = '';
								$upd['TaxExportCountry'] = '';
								
								if($resultCNCredit["acc_type"] == "rounding"){
									$upd["DR"] = $amount<0 ? $upd['CR'] = $this->selling_price_currency_format(abs($amount)) : 0;
									$upd['CR'] = $amount>0 ? $this->selling_price_currency_format($amount) : 0;
								}else{
									$upd["DR"] = $this->selling_price_currency_format(0);
									$upd['CR'] = $this->selling_price_currency_format(abs($amount));
								}
								
								$upd['TaxableDR'] = 0;
								$upd['TaxableCR'] = 0;
								$upd['TaxAdjustment'] = 0;
								$upd["TaxDR"] = 0;
								$upd["TaxCR"] = 0;
								$upd["SupplyPurchase"] = 'S';
								$upd["ToTaxCurrencyRate"] = 1;
								
								my_fputcsv($fp, $upd);
								unset($upd);
							}
							
						}
						$tmpSalesDb->sql_freeresult($resultCNCredit);
						
						
						//Get Debit Goods Exchange Contra
						//if(!$resultCN["is_refund"]){
							$wcond = array();
							$wcond[] = "`type`=".ms("debit");
							$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
							$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
							$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
							$wcond[] = "`type`!=".ms('deposit');
							$wcond[] = "`acc_type`!=".ms('rounding');
							$wcond[] = "`is_credit_notes`=".ms(1);
				
							$retCNCredit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no,								
												credit_note_no, return_receipt_no, account_code, account_name,acc_type,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where ".implode(" and ",$wcond)."
												group by pos_date, credit_note_no, account_code, account_name");
							unset($wcond);
							if($tmpSalesDb->sql_numrows($resultCNCredit)>0)
							{
								while($resultCNCredit = $this->sql_fetchrow($tmpSalesDb,$retCNCredit))
								{
									$acc_code = ($resultCNCredit["acc_type"] == "cash")?$resultCNCredit["account_code"]:$this->accSettings["goods_exchange_contra"]["account"]["account_code"];
									$acc_name = ($resultCNCredit["acc_type"] == "cash")?$resultCNCredit["account_name"]:$this->accSettings["goods_exchange_contra"]["account"]["account_name"];
									$refno = $resultCNCredit['doc_no'];
									$refno2 = $resultCNCredit['batchno'];
									
									if($last_doc_no != $refno){
										$upd = $master_row;
										$last_doc_no = $upd["DocNo"] = $refno;
									}else{
										$upd = $empty_master_row;
									}
									
									$amount = $resultCNCredit['TotalAmount'];
									
									$upd["AccNo"] = $acc_code;
									$upd['ToAccountRate'] = 1;
									$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
									$upd['DeptNo'] = '';
									$upd['TaxType'] = '';
									$upd["Description"] = $acc_name;
									$upd['FurtherDescription'] = '';
									$upd['RefNo2'] = '';
									$upd['SalesAgent'] = '';
									$upd['TaxBRNo'] = '';
									$upd['TaxBName'] = '';
									$upd['TaxRefNo'] = '';
									$upd['TaxPermitNo'] = '';
									$upd['TaxExportCountry'] = '';
									$upd["DR"] = $this->selling_price_currency_format(abs($amount));
									$upd["CR"] = $this->selling_price_currency_format(0);
									$upd['TaxableDR'] = 0;
									$upd['TaxableCR'] = 0;
									$upd['TaxAdjustment'] = 0;
									$upd["TaxDR"] = 0;
									$upd["TaxCR"] = 0;
									$upd["SupplyPurchase"] = 'S';
									$upd["ToTaxCurrencyRate"] = 1;
									
									my_fputcsv($fp, $upd);
									unset($upd);
								}
								
							}
							$tmpSalesDb->sql_freeresult($resultCNCredit);
						//}
					}
				}
				
				$tmpSalesDb->sql_freeresult($retCN);
				
				
				//Normal Sales Transaction
				$wcond = array();
				$wcond[] = "`type`=".ms("debit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`type`!=".ms('deposit');
				$wcond[] = "`is_credit_notes`=".ms(0);
				$wcond[] = "`TotalAmount` < ".ms(0);
				$retDebitAccType = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
									doc_no, ref_no, credit_note_no, account_code, account_name,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name");
				
				if($tmpSalesDb->sql_numrows($retDebitAccType)>0)
				{
					while($resultDebitAccType = $this->sql_fetchrow($tmpSalesDb, $retDebitAccType))
					{
						//print_r($resultDebitAccType);
						if($resultDebitAccType['tablename']=='do'){
							$refno = $resultDebitAccType['doc_no'];
							$refno2 = $resultDebitAccType['doc_no'];
						}
						else{
							$refno = $resultDebitAccType['doc_no'];
							$refno2 = $resultDebitAccType['batchno'];
						}
						
						if($last_doc_no != $refno){
							$upd = $master_row;
							$last_doc_no = $upd["DocNo"] = $refno;
						}else{
							$upd = $empty_master_row;
						}
						
						$amount = $resultDebitAccType['TotalAmount'];
									
						$upd["AccNo"] = $resultDebitAccType['account_code'];
						$upd['DocNo'] = $refno;
						$upd['ToAccountRate'] = 1;
						$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
						$upd['DeptNo'] = '';
						$upd['TaxType'] = '';
						$upd["Description"] = $resultDebitAccType['account_name'];
						$upd['FurtherDescription'] = '';
						$upd['RefNo2'] = '';
						$upd['SalesAgent'] = '';
						$upd['TaxBRNo'] = '';
						$upd['TaxBName'] = '';
						$upd['TaxRefNo'] = '';
						$upd['TaxPermitNo'] = '';
						$upd['TaxExportCountry'] = '';
						$upd["DR"] = $this->selling_price_currency_format(0);
						$upd["CR"] = $this->selling_price_currency_format(abs($amount));
						$upd['TaxableDR'] = 0;
						$upd['TaxableCR'] = 0;
						$upd['TaxAdjustment'] = 0;
						$upd["TaxDR"] = 0;
						$upd["TaxCR"] = 0;
						$upd["SupplyPurchase"] = 'S';
						$upd["ToTaxCurrencyRate"] = 1;
						my_fputcsv($fp, $upd);
						unset($upd);
					}		
				
				}
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
				unset($wcond);
				
				$wcond = array();
				$wcond[] = "`type`=".ms("debit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`is_credit_notes`=".ms(0);
				$wcond[] = "`TotalAmount` > ".ms(0);

				$retDebitAccType = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
									tax_code, acc_type,tax_account_code, tax_account_name, second_tax_code, 
									doc_no, ref_no, account_code, account_name,
									round(sum(ItemAmount),2) as itemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, acc_type,account_code, account_name, tax_account_code, tax_account_name");
				
				if($tmpSalesDb->sql_numrows($retDebitAccType)>0)
				{
					while($resultDebitAccType = $this->sql_fetchrow($tmpSalesDb, $retDebitAccType))
					{
						if($resultDebitAccType['tablename']=='do'){
							$refno = $resultDebitAccType['doc_no'];
							$refno2 = $resultDebitAccType['doc_no'];
						}
						else{
							$refno = $resultDebitAccType['doc_no'];
							$refno2 = $resultDebitAccType['batchno'];
						}
						
						if($last_doc_no != $refno){
							$upd = $master_row;
							$last_doc_no = $upd["DocNo"] = $refno;
						}else{
							$upd = $empty_master_row;
						}
						
						if(strtolower($resultDebitAccType['acc_type'])!="deposit")
						{
							$amount = $resultDebitAccType['TotalAmount'];
							$upd["AccNo"] = $resultDebitAccType['account_code'];
							$upd['ToAccountRate'] = 1;
							$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
							$upd['DeptNo'] = '';
							$upd['TaxType'] = '';
							$upd["Description"] = $resultDebitAccType['account_name'];
							$upd['FurtherDescription'] = '';
							$upd['RefNo2'] = '';
							$upd['SalesAgent'] = '';
							$upd['TaxBRNo'] = '';
							$upd['TaxBName'] = '';
							$upd['TaxRefNo'] = '';
							$upd['TaxPermitNo'] = '';
							$upd['TaxExportCountry'] = '';
							$upd["DR"] = $this->selling_price_currency_format($amount);
							$upd["CR"] = $this->selling_price_currency_format(0);
							$upd['TaxableDR'] = 0;
							$upd['TaxableCR'] = 0;
							$upd['TaxAdjustment'] = 0;
							$upd["TaxDR"] = 0;
							$upd["TaxCR"] = 0;
							$upd["SupplyPurchase"] = 'S';
							$upd["ToTaxCurrencyRate"] = 1;
							my_fputcsv($fp, $upd);
							unset($upd);
						}
						else{
							$amount = $resultDebitAccType['TotalAmount'];
							/*$upd["AccNo"] = $resultDebitAccType['account_code'];
							$upd['ToAccountRate'] = 1;
							$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
							$upd['DeptNo'] = '';
							$upd['TaxType'] = '';
							$upd["Description"] = $resultDebitAccType['account_name'];
							$upd['FurtherDescription'] = '';
							$upd['RefNo2'] = '';
							$upd['SalesAgent'] = '';
							$upd['TaxBRNo'] = '';
							$upd['TaxBName'] = '';
							$upd['TaxRefNo'] = '';
							$upd['TaxPermitNo'] = '';
							$upd['TaxExportCountry'] = '';
							$upd["DR"] = $this->selling_price_currency_format($amount);
							$upd["CR"] = $this->selling_price_currency_format(0);
							$upd['TaxableDR'] = 0;
							$upd['TaxableCR'] = 0;
							$upd['TaxAdjustment'] = 0;
							$upd["TaxDR"] = $this->selling_price_currency_format($upd["DR"]);
							$upd["TaxCR"] = $this->selling_price_currency_format($upd["CR"]);
							$upd["SupplyPurchase"] = 'S';
							$upd["ToTaxCurrencyRate"] = 1;
							my_fputcsv($fp, $upd);
							unset($upd);*/
							
							
							//$upd["AccNo"] = $resultDebitAccType['tax_account_code'];
							$upd["AccNo"] = $resultDebitAccType['account_code'];
							$upd['ToAccountRate'] = 1;
							$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
							$upd['DeptNo'] = '';
							$upd['TaxType'] = $resultDebitAccType['second_tax_code'];
							//$upd["Description"] = $resultDebitAccType['tax_account_name'];
							$upd["Description"] = $resultDebitAccType['account_name'];
							$upd['FurtherDescription'] = '';
							$upd['RefNo2'] = '';
							$upd['SalesAgent'] = '';
							$upd['TaxBRNo'] = '';
							$upd['TaxBName'] = '';
							$upd['TaxRefNo'] = '';
							$upd['TaxPermitNo'] = '';
							$upd['TaxExportCountry'] = '';
							$upd["DR"] = $this->selling_price_currency_format($amount);
							$upd["CR"] = 0;
							$upd['TaxableDR'] = $this->selling_price_currency_format($amount);
							$upd['TaxableCR'] = 0;
							$upd['TaxAdjustment'] = 0;
							$upd["TaxDR"] = $this->selling_price_currency_format($resultDebitAccType['TaxAmount']);
							$upd["TaxCR"] = 0;
							$upd["SupplyPurchase"] = 'S';
							$upd["ToTaxCurrencyRate"] = 1;
							my_fputcsv($fp, $upd);
							unset($upd);
							
						}
					}		
				}
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
				unset($wcond);
				
				$wcond = array();
				$wcond[] = "`type`=".ms("credit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`is_credit_notes`=".ms(0);
				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name, second_tax_code,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable." 
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name, tax_code");
				unset($wcond);
				if($tmpSalesDb->sql_numrows($retDetail)>0)
				{
					while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
					{
						$amount = $resultDetail['TotalAmount'];
						$taxAmt = $resultDetail['TaxAmount'];
						
						if($last_doc_no != $refno){
							$upd = $master_row;
							$last_doc_no = $upd["DocNo"] = $refno;
						}else{
							$upd = $empty_master_row;
						}
						
						//Sales
						$upd["AccNo"] = $resultDetail['account_code'];
						$upd['ToAccountRate'] = 1;
						$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
						$upd['DeptNo'] = '';
						$upd["TaxType"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
						$upd["Description"] = $resultDetail['account_name'];
						$upd['FurtherDescription'] = '';
						$upd['RefNo2'] = '';
						$upd['SalesAgent'] = '';
						$upd['TaxBRNo'] = '';
						$upd['TaxBName'] = '';
						$upd['TaxRefNo'] = '';
						$upd['TaxPermitNo'] = '';
						$upd['TaxExportCountry'] = '';
						
						if($resultDetail["acc_type"] == 'rounding'){
							$upd["DR"] = $amount<0 ? $this->selling_price_currency_format(abs($amount)) : 0;
							$upd["CR"] = $amount>0 ? $this->selling_price_currency_format($amount) : 0;
							$upd['TaxableDR'] = 0;
							$upd['TaxableCR'] = 0;
							$upd['TaxAdjustment'] = 0;
							$upd["TaxDR"] = 0;
							$upd["TaxCR"] = 0;
						}else{
							$upd["DR"] = 0;
							$upd["CR"] = $this->selling_price_currency_format($amount);
							$upd['TaxableDR'] = 0;
							$upd['TaxableCR'] = $this->selling_price_currency_format($amount);
							$upd['TaxAdjustment'] = 0;
							$upd["TaxDR"] = 0;
							$upd["TaxCR"] = $this->selling_price_currency_format($resultDetail['TaxAmount']);
						}
						
						$upd["SupplyPurchase"] = 'S';
						$upd["ToTaxCurrencyRate"] = 1;
						my_fputcsv($fp, $upd);
						unset($upd);
					}
				}
				$tmpSalesDb->sql_freeresult($retDetail);
				
				unset($wcond);
			}
			$tmpSalesDb->sql_freeresult($ret);
			
			
			//Export Cash Sales From DO
			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
													doc_no, ref_no, is_credit_notes,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type`='debit' and acc_type != ".ms("sales return")."
													and tablename = ".ms("do")."
													group by pos_date, doc_no, is_credit_notes order by pos_date,doc_no");									

			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				// Master Details
				$master_row = array();
				$master_row['DocNo'] = $result['doc_no'];
				$master_row['DocDate'] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$master_row['JournalType'] = $this->accSettings['sales_journal_type']['data'];
				$master_row['DocNo2'] = '';
				$master_row['CurrencyCode'] = $config["arms_currency"]["code"];
				$master_row['CurrencyRate'] = 1;
				$master_row['Note'] = '';
				$master_row['InclusiveTax'] = 'T';
				
				$wcond = array();
				$wcond[] = "`type`=".ms("debit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`is_credit_notes`=".ms(0);
				$wcond[] = "`TotalAmount` > ".ms(0);
				$retDebitAccType = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
									doc_no, ref_no, account_code, account_name,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name");
				unset($wcond);
				while($resultDebitAccType = $this->sql_fetchrow($tmpSalesDb, $retDebitAccType))
				{
					if($resultDebitAccType['tablename']=='do'){
						$refno = $resultDebitAccType['doc_no'];
						$refno2 = $resultDebitAccType['doc_no'];
					}
					else{
						$refno = $resultDebitAccType['doc_no'];
						$refno2 = $resultDebitAccType['batchno'];
					}
					$amount = $resultDebitAccType['TotalAmount'];
					
					$upd = $master_row;
					
					$upd["DocNo"] = $refno;
					$upd["AccNo"] = $resultDebitAccType['account_code'];
					$upd['ToAccountRate'] = 1;
					$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
					$upd['DeptNo'] = '';
					$upd['TaxType'] = '';
					$upd["Description"] = $resultDebitAccType['account_name'];
					$upd['FurtherDescription'] = '';
					$upd['RefNo2'] = '';
					$upd['SalesAgent'] = '';
					$upd['TaxBRNo'] = '';
					$upd['TaxBName'] = '';
					$upd['TaxRefNo'] = '';
					$upd['TaxPermitNo'] = '';
					$upd['TaxExportCountry'] = '';
					$upd["DR"] = $this->selling_price_currency_format($amount);
					$upd["CR"] = $this->selling_price_currency_format(0);
					$upd['TaxableDR'] = 0;
					$upd['TaxableCR'] = 0;
					$upd['TaxAdjustment'] = 0;
					$upd["TaxDR"] = 0;
					$upd["TaxCR"] = 0;
					$upd["SupplyPurchase"] = 'S';
					$upd["ToTaxCurrencyRate"] = 1;
					
					my_fputcsv($fp, $upd);
					unset($upd);
				}		
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
					
				$wcond = array();
				$wcond[] = "`type`=".ms("credit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`acc_type`!=".ms("sales return");
				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name, second_tax_code, 
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['TotalAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					
					//Sales
					$upd = $empty_master_row;
					
					$upd["AccNo"] = $resultDetail['account_code'];
					$upd['ToAccountRate'] = 1;
					$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
					$upd['DeptNo'] = '';
					$upd['TaxType'] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
					$upd["Description"] = $resultDetail['account_name'];
					$upd['FurtherDescription'] = '';
					$upd['RefNo2'] = '';
					$upd['SalesAgent'] = '';
					$upd['TaxBRNo'] = '';
					$upd['TaxBName'] = '';
					$upd['TaxRefNo'] = '';
					$upd['TaxPermitNo'] = '';
					$upd['TaxExportCountry'] = '';
					$upd["DR"] = 0;
					$upd["CR"] = $this->selling_price_currency_format($amount);
					$upd['TaxableDR'] = 0;
					$upd['TaxableCR'] = $this->selling_price_currency_format($amount);
					$upd['TaxAdjustment'] = 0;
					$upd["TaxDR"] = 0;
					$upd["TaxCR"] = $this->selling_price_currency_format($resultDetail['TaxAmount']);
					$upd["SupplyPurchase"] = 'S';
					$upd["ToTaxCurrencyRate"] = 1;
					
					my_fputcsv($fp, $upd);
					unset($upd);
				}
				unset($wcond);
			}
			
			
			//Export Cash Sales DO's Credit Note
			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
													doc_no, ref_no, return_receipt_no,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type`='debit'
													and tablename = ".ms("do_cn")."
													group by pos_date, doc_no, return_receipt_no order by pos_date,doc_no");
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$refno = $result['doc_no'];
				$refno2 = $result['return_receipt_no'];
				
				// Master Details
				$master_row = array();
				$master_row['DocNo'] = $result['doc_no'];
				$master_row['DocDate'] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$master_row['JournalType'] = $this->accSettings['sales_journal_type']['data'];
				$master_row['DocNo2'] = '';
				$master_row['CurrencyCode'] = $config["arms_currency"]["code"];
				$master_row['CurrencyRate'] = 1;
				$master_row['Note'] = '';
				$master_row['InclusiveTax'] = 'T';
				
				if(isset($this->accSettings['cash_refund']))
				{
					$account = $this->accSettings['cash_refund']['account'];
				}
				else
				{
					$account = $this->accSettings['cash']['account'];
				}
				$amount = $result['TotalAmount'];
				
				$upd = $master_row;
				
				$upd["AccNo"] = $account['account_code'];
				$upd['ToAccountRate'] = 1;
				$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
				$upd['DeptNo'] = '';
				$upd['TaxType'] = '';
				$upd["Description"] = $account['account_name'];
				$upd['FurtherDescription'] = '';
				$upd['RefNo2'] = '';
				$upd['SalesAgent'] = '';
				$upd['TaxBRNo'] = '';
				$upd['TaxBName'] = '';
				$upd['TaxRefNo'] = '';
				$upd['TaxPermitNo'] = '';
				$upd['TaxExportCountry'] = '';
				$upd["DR"] = $this->selling_price_currency_format(0);
				$upd["CR"] = $this->selling_price_currency_format($amount);
				$upd['TaxableDR'] = 0;
				$upd['TaxableCR'] = 0;
				$upd['TaxAdjustment'] = 0;
				$upd["TaxDR"] = 0;
				$upd["TaxCR"] = 0;
				$upd["SupplyPurchase"] = 'S';
				$upd["ToTaxCurrencyRate"] = 1;
				
				my_fputcsv($fp, $upd);
				unset($upd);
	
				$wcond = array();
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`return_receipt_no`=".ms($result['return_receipt_no']);

				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name, second_tax_code, 
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable." 
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, return_receipt_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['TotalAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					//Sales
					$upd = $empty_master_row;
					
					/*
					$upd["AccNo"] = $resultCNDebit['account_code'];						
					$upd['ToAccountRate'] = 1;
					$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
					$upd['DeptNo'] = '';
					$upd['TaxType'] = $resultCNDebit['second_tax_code'];
					$upd["Description"] = $resultCNDebit['account_name'];
					$upd['FurtherDescription'] = '';
					$upd['RefNo2'] = '';
					$upd['SalesAgent'] = '';
					$upd['TaxBRNo'] = '';
					$upd['TaxBName'] = '';
					$upd['TaxRefNo'] = '';
					$upd['TaxPermitNo'] = '';
					$upd['TaxExportCountry'] = '';
					$upd["DR"] = $this->selling_price_currency_format($amount);
					$upd['CR'] = $this->selling_price_currency_format(0);
					$upd['TaxableDR'] = 0;
					$upd['TaxableCR'] = 0;
					$upd['TaxAdjustment'] = 0;
					$upd["TaxDR"] = $this->selling_price_currency_format($upd["DR"]);
					$upd["TaxCR"] = $this->selling_price_currency_format($upd["CR"]);
					$upd["SupplyPurchase"] = 'S';
					$upd["ToTaxCurrencyRate"] = 1;
					*/
				
					$upd["AccNo"] = $resultDetail['account_code'];
					$upd['ToAccountRate'] = 1;
					$upd['ProjNo'] = $this->accSettings['proj_no']['data'];
					$upd['DeptNo'] = '';
					$upd['TaxType'] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
					$upd["Description"] = $resultDetail['account_name'];
					$upd['FurtherDescription'] = '';
					$upd['RefNo2'] = '';
					$upd['SalesAgent'] = '';
					$upd['TaxBRNo'] = '';
					$upd['TaxBName'] = '';
					$upd['TaxRefNo'] = '';
					$upd['TaxPermitNo'] = '';
					$upd['TaxExportCountry'] = '';
					$upd["DR"] = $this->selling_price_currency_format($amount);
					$upd["CR"] = 0;
					$upd['TaxableDR'] = $this->selling_price_currency_format($amount);
					$upd['TaxableCR'] = 0;
					$upd['TaxAdjustment'] = 0;
					$upd["TaxDR"] = $this->selling_price_currency_format($resultDetail["TaxAmount"]);
					$upd["TaxCR"] = 0;
					$upd["SupplyPurchase"] = 'S';
					$upd["ToTaxCurrencyRate"] = 1;
					
					my_fputcsv($fp, $upd);
					unset($upd);
				}
				unset($wcond);
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function preset_account_column($dataType){}
	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode){}
	function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode){}
	function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode){}
	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode){}
	function get_account_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo){}
	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode){}
}
?>
