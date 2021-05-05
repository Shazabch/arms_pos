<?php
/*
2016-03-08 05:29pm Kee Kee
- Cash Sales cannot contain goods return amount

2016-04-06 09:46am Kee Kee
- Cash Sales format missing header
- Cash Sales miising Payment information

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2016-09-21 3:43 PM Kee Kee
- Million accounting format for purchase incorrect (assign wrong value/amount)

2016-09-22 9:52 AM Kee Kee
- Swaped Purchase ref no and ref no2 value

2016-09-22 11:12 AM Kee Kee
- Fixed Million accounting format for purchase debit note incorrect & refno set with wrong document no(Mantis Ticket No:0000156)

2016-09-22 02:03 PM Kee Kee
- Added purchase_return account code/name for Purchase Debit Note

2016-09-23 10:32 AM Kee Kee
- Use gl_name in Purchase "descp" column instead set with "PURCHASE"

2016-09-30 9:30 AM Kee Kee
- Combine Cash Sales & Credit Notes in 1 export file

2016-10-05 16:12 PM Kee Kee
- Debit Note Taxable/FxTaxable amount must be -ve

2016-10-05 16:26 PM Kee Kee
- Credit Sales export curr_code should be set with MYR instead XXX

2016-10-24 15:24 PM Kee Kee
- Export Cash Sales and Credit Notes (POS Counter) together

2016-11-02 13:35 PM Kee Kee
- Change "Account Receiver" to "Cash Sales"/"Credit Sales"

2016-11-02 17:35 PM Kee Kee
- Export Cash Sales DO and Credit Notes

2016-11-07 13:06 PM Kee Kee
- Export Credit Sales / IBT Sales and Credit Note

2016-11-10 9:48 AM Kee Kee
- Fixed extra a row when export cash Sales & Credit Note

2016-11-11 9:22 AM Kee Kee
- Fixed GST output tax for return inward amount placed in the wrong column, it should be in the debit column and the amount needs to be positive.

2016-11-16 12:50PM Kee Kee
- Fixed Account export for deposit claims not balance

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php

2017-01-09 15:00PM Kee Kee
- Fixed Problem with the double entry for goods return export to Million Accounting

2017-01-10 17:57 PM Kee Kee
- Fixed Sales Taxable should be -amount

2017-02-17 17:09 PM Qiu Ying
- Bug fixed on unable to load debtor account code when generate Sales & Credit Note export

2017-02-21 09:57 AM Qiu Ying
- Bug fixed on Million Account export for purchase export duplicate data

2017-02-27 11:27 Qiu Ying
- Remove testing message in get_account_cash_sales_n_credit_note

2017-03-01 17:53 Qiu Ying
- Bug fixed on goods exchange not fulfilled to MFRS and GST requirement

2017-03-08 11:18 Qiu Ying
- Bug fixed on the position and the document type for cash received in tax invoice is wrong

5/8/2017 8:50 AM Khausalya
- Enhanced changes from MYR to use config setting. 

5/29/2017 16:50 Qiu Ying
- Enhanced to export credit note with multiple invoice

11/17/2017 4:42 PM Andy
- Fixed Million cscn "over" should not have 2nd tax row.
- Fixed Million rounding error on cscn credit note.
*/
include_once("ExportModule.php");
class Million extends ExportModule
{
	const NAME="Million";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array("accno"=>0,"doc_type"=>0,"doc_no"=>0,"seq"=>0,"doc_date"=>0,
								  "refno"=>0,"refno2"=>0,"refno3"=>0,"desp"=>0,"desp2"=>0,"desp3"=>0,"desp4"=>0,
								  "amount"=>1,"debit"=>1,"credit"=>1,"fx_amount"=>1,"fx_debit"=>1,"fx_credit"=>1,"fx_rate"=>1,
								  "curr_code"=>0,"taxcode"=>0,"taxable"=>1,"fx_taxable"=>1,"link_seq"=>0,
								  "billtype"=>0,"remark1"=>0,"remark2"=>0,"batchno"=>0,"projcode"=>0,"deptcode"=>0,"accmgr_id"=>0,"cheque_no"=>0);
	var $ExportFileName = array(
		"ar" => "%s/million_ar_%s.csv",
		"cscn" => "%s/million_cs_%s.csv",
		"arcn" => "%s/million_ar_%s.csv",
		"ap" => "%s/million_ap_%s.csv",
		"cn" => "%s/million_cn_%s.csv",
		"dn" => "%s/million_dn_%s.csv",
    );
	var $ExportDateFormat = "d/m/y 00:00";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config;
		
		//$dataType=array('cs'=>true,'ap'=>true,'ar'=>true,'cn'=>true,'dn'=>true);
		$dataType=array('cscn'=>true,'arcn'=>true,'ap'=>true,'dn'=>true);

		if($config['consignment_modules']){
			$dataType['cscn']=false;
			$dataType['arcn']=false;
			$dataType['ar']=true;
			$dataType['cn']=true;
			$dataType['ap']=false;
		}

        return array(
			"module_name" => __CLASS__,
			"export_with_sdk"=>self::$export_with_sdk,
			"show_all_column"=>self::$show_all_column,
			"settings" => array (
				'purchase' => array (
					"name"=>"Purchase",
					"account"=> array (
						'account_code' => '5005/0000',
						'account_name' => 'Purchase',
					)
				),
				'purchase_return' => array (
					"name"=>"Purchase Return",
					"account"=> array (
						'account_code' => '5005/0000',
						'account_name' => 'Purchase',
					)
				),
				'sales' => array (
					"name"=>"Cash Sales",
					"account"=> array (
						'account_code' => '5005/0000',
						'account_name' => 'Cash Sales',
					)
				),
				'sales_return' => array (
					"name"=>"Sales Return",
					"account"=> array (
						'account_code' => '5006/0000',
						'account_name' => 'Sales Return',
					)
				),
				'credit_sales' => array (
					"name"=>"Credit Sales",
					"account"=> array (
						'account_code' => '5005/0000',
						'account_name' => 'Credit Sales',
					)
				),
				'customer_code' => array(//Default Debtor Code
					"name"=>"Customer Code",
					"account"=>array(
						'account_code' => '3000/0001',
						'account_name' => 'Cash Sales',
					),
					"help"=>"For POS Cash Sales only"
				),
				'cash' => array (
					"name"=>"Cash",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Cash In Hand',
					)
				),
				'credit_card' => array (
					"name"=>"Credit Card",
					"account"=>array(
						'account_code' => '3020/0001',
						'account_name' => 'Credit Cards',
					)
				),
				'coupon' => array (
					"name"=>"Coupon",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Cash In Hand',
					)
				),
				'voucher' => array(
					"name"=>"Voucher",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Cash In Hand',
					)
				),
				'check' => array (
					"name"=>"Check",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Cash In Hand',
					)
				),
				'deposit' => array (
					"name"=>"Deposit",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Deposit',
					)
				),
				'rounding' => array (
					"name"=>"Rounding",
					"account"=>array(
						'account_code' => '3010/0001',
						'account_name' => 'Rounding',
					),
				),
				'service_charge' => array (
					"name"=>"Service Charge",
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'Cash Sales',
					)
				),
				'cash_refund' => array (
					"name"=>"Cash Refund",
					"account"=>array(
						'account_code' => '3010/0003',
						'account_name' => 'Cash Refund',
					)
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '3010/0002',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '3010/0003',
						'account_name' => 'Over',
					)
				),
				'SR' => array (
					"name"=>"SR @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4050/0001',
						'account_name' => 'GST Output Tax',
					)
				),
				'goods_exchange_contra' => array (
					"name"=>"Goods Exchange Contra A/C",
					"account"=> array (
						'account_code' => '9999/9999',
						'account_name' => 'Goods Exchange Contra A/C',
					)
				),
				'ZRL' => array (
					"name"=>"ZRL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'ZRL @0%',
					)
				),
				'ZRE' => array (
					"name"=>"ZRE @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'ZRE @0%',
					)
				),
				'ES43' => array (
					"name"=> "ES43 @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'ES43 @0%',
					)
				),
				'DS' => array (
					"name"=>"DS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'DS @6%',
					)
				),
				'OS' => array (
					"name"=>"OS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'OS @0%',
					)
				),
				'ES' => array (
					"name"=>"ES @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '5000/0000',
						'account_name' => 'ES @0%',
					)
				),
				'RS' => array (
					"name"=>"RS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'RS @0%',
					),
				),
				'GS' =>  array (
					"name"=>"GS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'GS @0%',
					)
				),
				'AJS' => array (
					"name"=>"AJS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'AJS @6%',
					)
				),
				'AJP' => array (
					"name"=>"AJP @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'BL' => array (
					"name"=>"BL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'EP' => array (
					"name"=>"EP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'GP' => array (
					"name"=> "GP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'IM' => array (
					"name"=>"IM @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'IS' => array (
					"name"=>"IS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'NR' => array (
					"name"=>"NR @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'OP' => array (
					"name"=>"OP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0000',
						'account_name' => 'Output Tax',
					),
				),
				'TX' =>  array (
					"name"=>"TX @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-E43' => array (
					"name"=>"TX-E43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-N43' => array (
					"name"=>"TX-N43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'TX-RE' => array (
					"name"=>"TX-RE @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
					)
				),
				'ZP' => array (
					"name"=>"ZP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '3050/0001',
						'account_name' => 'GST Input Tax',
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
			case 'cs':
			case 'ar':
			case 'ap':
			case 'cn':
			case 'dn':
				  $this->tvExportCol = $this->ExportFileHeader;
			break;
			default:
					$this->tvExportCol = array();
				break;
		}
	}

	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
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
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, acc_type, account_code, account_name");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{						
						$posDate=$result['pos_date'];
						$branch_id=$result['batchno'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] = 0;
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']])){
							$tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
						}

						$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] += $result['TotalAmount'];
						$tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];
					}
					unset($result, $posDate, $branch_id);
					$tmpSalesDb->sql_freeresult($ret);

					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, account_code, account_name,
											tax_code, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit'
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, acc_type, account_code, account_name, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['pos_date'];
						$branch_id=$result['batchno'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] = 0;
						}
						
						$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] += $result['TotalAmount'];

						if($result['tax_code']){
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] += $result['ItemAmount'];

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] += $result['ItemAmount'];
							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] += $result['TaxAmount'];
						}
						else{
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']]))
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']] = 0;

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
						}
					}
					unset($result, $posDate);
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, account_code, account_name,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='debit'
												and tablename='do'
												group by pos_date, batchno, doc_no, acc_type");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
							$posDate=$result['pos_date'];
							$branch_id=$result['batchno'];
							$receipt_no=$result['doc_no'];

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'])) $tmpSalesDetail[$posDate][$branch_id]['do']=array();

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no] = array();
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] += $result['TotalAmount'];

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];
						}
						unset($result, $posDate, $branch_id, $receipt_no);
						$tmpSalesDb->sql_freeresult($ret);

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, acc_type, account_code, account_name,
												tax_code, tax_account_code, tax_account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='credit'
												and tablename='do'
												group by pos_date, batchno, doc_no, account_code, account_name, tax_code");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
							$posDate=$result['pos_date'];
							$branch_id=$result['batchno'];
							$receipt_no=$result['doc_no'];

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'])) $tmpSalesDetail[$posDate][$branch_id]['do']=array();

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no] = array();
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] = 0;
							}
							
							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] += $result['TotalAmount'];

							if($result['tax_code']){
								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] += $result['ItemAmount'];

								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] = 0;
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] += $result['ItemAmount'];
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] += $result['TaxAmount'];
							}
							else{
								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']]))
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']] = 0;

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
							}
						}
						unset($result, $posDate, $branch_id, $receipt_no);
						$tmpSalesDb->sql_freeresult($ret);
					}

					$bn = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail){
							$sequence = 1;
							//$refno = $this->create_batch_no(date("ymd",strtotime($posDate)),sprintf("%04d",$bn));
							$refno = $branch_id;
							$docNo="POS-".sprintf("%04s",$bn);
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$amount = $val['debitamt'];

										$upd["accno"] = $acc_code;
										$upd["doc_type"] = "GL";
										$upd["doc_no"] = $docNo;
										$upd["seq"] = sprintf("%d",$sequence);
										$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["refno"] = $refno;
										$upd["refno2"] = "";
										$upd["refno3"] = "";
										$upd["desp"] = $acc_name;
										$upd["desp2"] = "";
										$upd["desp3"] = "";
										$upd["desp4"] = "";
										$upd["amount"] = $this->selling_price_currency_format($amount);
										$upd["debit"] = $this->selling_price_currency_format($amount);
										$upd["credit"] = $this->selling_price_currency_format(0);
										$upd["fx_amount"] = $this->selling_price_currency_format($amount);
										$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
										$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
										$upd["fx_rate"] = 1;
										$upd["curr_code"] = $config["arms_currency"]["code"];
										$upd["taxcode"] = "";
										$upd["taxable"] = $this->selling_price_currency_format(0);
										$upd["fx_taxable"] = $this->selling_price_currency_format(0);
										$upd["link_seq"] = 0;
										$upd["billtype"] = "H";
										$upd["remark1"] = "";
										$upd["remark2"] = "";
										$upd["batchno"] = "";
										$upd["projcode"] = "";
										$upd["deptcode"] = "";
										$upd["accmgr_id"] = "";
										$upd["cheque_no"] = "";
										my_fputcsv($fp, $upd);
										unset($upd);
										$sequence++;
									}
								}
							}

							foreach($salesDetail['sales'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$value2)
									{
										if(is_array($value2)){
											foreach($value2 as $tax_name=>$val){
												if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

												$amount = (isset($val['amt']))?$val['amt']:$val;
												$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

												$upd["accno"] = $acc_code;
												$upd["doc_type"] = "GL";
												$upd["doc_no"] = $docNo;
												$upd["seq"] = sprintf("%d",$sequence);
												$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["refno"] = $refno;
												$upd["refno2"] = "";
												$upd["refno3"] = "";
												$upd["desp"] = $acc_name;
												$upd["desp2"] = $tax_name;
												$upd["desp3"] = "";
												$upd["desp4"] = "";
												$upd["amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["debit"] = $this->selling_price_currency_format(0);
												$upd["credit"] = $this->selling_price_currency_format($amount);
												$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
												$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
												$upd["fx_rate"] = 1;
												$upd["curr_code"] = $config["arms_currency"]["code"];
												$upd["taxcode"] = ($tax_code!="")?$val['tax_code']:"";
												$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["link_seq"] = 0;
												$upd["billtype"] = "H";
												$upd["remark1"] = "";
												$upd["remark2"] = "";
												$upd["batchno"] = "";
												$upd["projcode"] = "";
												$upd["deptcode"] = "";
												$upd["accmgr_id"] = "";
												$upd["cheque_no"] = "";
												my_fputcsv($fp, $upd);
												unset($upd, $tax_name);
												$sequence++;
											}
										}
										else{
											$amount = $value2;

											$upd["accno"] = $acc_code;
											$upd["doc_type"] = "GL";
											$upd["doc_no"] = $docNo;
											$upd["seq"] = sprintf("%d",$sequence);
											$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["refno"] = $refno;
											$upd["refno2"] = "";
											$upd["refno3"] = "";
											$upd["desp"] = $acc_name;
											$upd["desp2"] = "";
											$upd["desp3"] = "";
											$upd["desp4"] = "";
											$upd["amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["debit"] = $this->selling_price_currency_format(0);
											$upd["credit"] = $this->selling_price_currency_format($amount);
											$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
											$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
											$upd["fx_rate"] = 1;
											$upd["curr_code"] = $config["arms_currency"]["code"];
											$upd["taxcode"] = "";
											$upd["taxable"] = $this->selling_price_currency_format(0);
											$upd["fx_taxable"] = $this->selling_price_currency_format(0);
											$upd["link_seq"] = 0;
											$upd["billtype"] = "H";
											$upd["remark1"] = "";
											$upd["remark2"] = "";
											$upd["batchno"] = "";
											$upd["projcode"] = "";
											$upd["deptcode"] = "";
											$upd["accmgr_id"] = "";
											$upd["cheque_no"] = "";
											my_fputcsv($fp, $upd);
											unset($upd);
											$sequence++;
										}
									}
								}
							}

							if(isset($salesDetail['do'])){
								foreach($salesDetail['do'] as $receipt_no=>$salesDetail_do)
								{
									foreach($salesDetail_do['payment'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$val)
											{
												$amount = $val['debitamt'];

												$upd["accno"] = $acc_code;
												$upd["doc_type"] = "GL";
												$upd["doc_no"] = $receipt_no;
												$upd["seq"] = sprintf("%d",$sequence);
												$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["refno"] = $refno;
												$upd["refno2"] = "";
												$upd["refno3"] = "";
												$upd["desp"] = $acc_name;
												$upd["desp2"] = "";
												$upd["desp3"] = "";
												$upd["desp4"] = "";
												$upd["amount"] = $this->selling_price_currency_format($amount);
												$upd["debit"] = $this->selling_price_currency_format($amount);
												$upd["credit"] = $this->selling_price_currency_format(0);
												$upd["fx_amount"] = $this->selling_price_currency_format($amount);
												$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
												$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
												$upd["fx_rate"] = 1;
												$upd["curr_code"] = $config["arms_currency"]["code"];
												$upd["taxcode"] = "";
												$upd["taxable"] = $this->selling_price_currency_format(0);
												$upd["fx_taxable"] = $this->selling_price_currency_format(0);
												$upd["link_seq"] = 0;
												$upd["billtype"] = "H";
												$upd["remark1"] = "";
												$upd["remark2"] = "";
												$upd["batchno"] = "";
												$upd["projcode"] = "";
												$upd["deptcode"] = "";
												$upd["accmgr_id"] = "";
												$upd["cheque_no"] = "";
												my_fputcsv($fp, $upd);
												unset($upd);
												$sequence++;
											}
										}
									}

									foreach($salesDetail_do['sales'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$value2)
											{
												if(is_array($value2)){
													foreach($value2 as $tax_name=>$val){
														if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

														$amount = (isset($val['amt']))?$val['amt']:$val;
														$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

														$upd["accno"] = $acc_code;
														$upd["doc_type"] = "GL";
														$upd["doc_no"] = $receipt_no;
														$upd["seq"] = sprintf("%d",$sequence);
														$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["refno"] = $refno;
														$upd["refno2"] = "";
														$upd["refno3"] = "";
														$upd["desp"] = $acc_name;
														$upd["desp2"] = $tax_name;
														$upd["desp3"] = "";
														$upd["desp4"] = "";
														$upd["amount"] = $this->selling_price_currency_format(0-$amount);
														$upd["debit"] = $this->selling_price_currency_format(0);
														$upd["credit"] = $this->selling_price_currency_format($amount);
														$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
														$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
														$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
														$upd["fx_rate"] = 1;
														$upd["curr_code"] = $config["arms_currency"]["code"];
														$upd["taxcode"] = ($tax_code!="")?$val['tax_code']:"";
														$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
														$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
														$upd["link_seq"] = 0;
														$upd["billtype"] = "H";
														$upd["remark1"] = "";
														$upd["remark2"] = "";
														$upd["batchno"] = "";
														$upd["projcode"] = "";
														$upd["deptcode"] = "";
														$upd["accmgr_id"] = "";
														$upd["cheque_no"] = "";
														my_fputcsv($fp, $upd);
														unset($upd, $tax_name);
														$sequence++;
													}
												}
												else{
													$amount = $value2;

													$upd["accno"] = $acc_code;
													$upd["doc_type"] = "GL";
													$upd["doc_no"] = $docNo;
													$upd["seq"] = sprintf("%d",$sequence);
													$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["refno"] = $refno;
													$upd["refno2"] = $receipt_no;
													$upd["refno3"] = "";
													$upd["desp"] = $acc_name;
													$upd["desp2"] = "";
													$upd["desp3"] = "";
													$upd["desp4"] = "";
													$upd["amount"] = $this->selling_price_currency_format(0-$amount);
													$upd["debit"] = $this->selling_price_currency_format(0);
													$upd["credit"] = $this->selling_price_currency_format($amount);
													$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
													$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
													$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
													$upd["fx_rate"] = 1;
													$upd["curr_code"] = $config["arms_currency"]["code"];
													$upd["taxcode"] = "";
													$upd["taxable"] = $this->selling_price_currency_format(0);
													$upd["fx_taxable"] = $this->selling_price_currency_format(0);
													$upd["link_seq"] = 0;
													$upd["billtype"] = "H";
													$upd["remark1"] = "";
													$upd["remark2"] = "";
													$upd["batchno"] = "";
													$upd["projcode"] = "";
													$upd["deptcode"] = "";
													$upd["accmgr_id"] = "";
													$upd["cheque_no"] = "";
													my_fputcsv($fp, $upd);
													unset($upd);
													$sequence++;
												}
											}
										}
									}
								}
							}
						}
						$bn++;
					}

					unset($tmpSalesDetail, $bn ,$sequence);
					break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, acc_type, account_code, account_name");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['ym'];
						$branch_id=$result['batchno'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] = 0;
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']])){
							$tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
						}

						$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] += $result['TotalAmount'];
						$tmpSalesDetail[$posDate][$branch_id]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];
					}
					unset($result, $posDate, $branch_id);
					$tmpSalesDb->sql_freeresult($ret);

					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno, acc_type, account_code, account_name,
											tax_code, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0 
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, account_code, account_name, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['ym'];
						$branch_id=$result['batchno'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] = 0;
						}
						
						$tmpSalesDetail[$posDate][$branch_id]['sales']['total_amount'] += $result['TotalAmount'];

						if($result['tax_code']){
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] += $result['ItemAmount'];

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] += $result['ItemAmount'];
							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] += $result['TaxAmount'];
						}
						else{
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']]))
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']] = 0;

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
						}
					}
					unset($result, $posDate, $branch_id);
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, account_code, account_name,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='debit'
												and tablename='do'
												group by pos_date, batchno, doc_no, acc_type");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
							$posDate=$result['pos_date'];
							$branch_id=$result['batchno'];
							$receipt_no=$result['doc_no'];

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'])) $tmpSalesDetail[$posDate][$branch_id]['do']=array();

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no] = array();
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] += $result['TotalAmount'];

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];
						}
						unset($result, $posDate, $branch_id, $receipt_no);
						$tmpSalesDb->sql_freeresult($ret);

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, acc_type, account_code, account_name,
												tax_code, tax_account_code, tax_account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='credit'
												and tablename='do'
												group by pos_date, batchno, doc_no, account_code, account_name, tax_code");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
							$posDate=$result['pos_date'];
							$branch_id=$result['batchno'];
							$receipt_no=$result['doc_no'];

							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'])) $tmpSalesDetail[$posDate][$branch_id]['do']=array();

							if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no])){
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no] = array();
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['payment']['total_amount'] = 0;
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] = 0;
							}
							
							$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales']['total_amount'] += $result['TotalAmount'];

							if($result['tax_code']){
								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] += $result['ItemAmount'];

								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] = 0;
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] += $result['ItemAmount'];
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] += $result['TaxAmount'];
							}
							else{
								if(!isset($tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']]))
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']] = 0;

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
							}
						}
						unset($result, $posDate, $branch_id, $receipt_no);
						$tmpSalesDb->sql_freeresult($ret);

					}

					if(is_array($tmpSalesDetail)) ksort($tmpSalesDetail);

					$bn = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail){
							$sequence = 1;
							//$refno = $this->create_batch_no(date("ymd",strtotime($posDate)),sprintf("%04d",$bn));
							$refno = $branch_id;
							$docNo="POS-".sprintf("%04s",$bn);
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$amount = $val['debitamt'];

										$upd["accno"] = $acc_code;
										$upd["doc_type"] = "GL";
										$upd["doc_no"] = $docNo;
										$upd["seq"] = sprintf("%d",$sequence);
										$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["refno"] = $refno;
										$upd["refno2"] = "";
										$upd["refno3"] = "";
										$upd["desp"] = $acc_name;
										$upd["desp2"] = "";
										$upd["desp3"] = "";
										$upd["desp4"] = "";
										$upd["amount"] = $this->selling_price_currency_format($amount);
										$upd["debit"] = $this->selling_price_currency_format($amount);
										$upd["credit"] = $this->selling_price_currency_format(0);
										$upd["fx_amount"] = $this->selling_price_currency_format($amount);
										$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
										$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
										$upd["fx_rate"] = 1;
										$upd["curr_code"] = $config["arms_currency"]["code"];
										$upd["taxcode"] = "";
										$upd["taxable"] = $this->selling_price_currency_format(0);
										$upd["fx_taxable"] = $this->selling_price_currency_format(0);
										$upd["link_seq"] = 0;
										$upd["billtype"] = "H";
										$upd["remark1"] = "";
										$upd["remark2"] = "";
										$upd["batchno"] = "";
										$upd["projcode"] = "";
										$upd["deptcode"] = "";
										$upd["accmgr_id"] = "";
										$upd["cheque_no"] = "";
										my_fputcsv($fp, $upd);
										unset($upd);
										$sequence++;
									}
								}
							}

							foreach($salesDetail['sales'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$value2)
									{
										if(is_array($value2)){
											foreach($value2 as $tax_name=>$val){
												if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

												$amount = (isset($val['amt']))?$val['amt']:$val;
												$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

												$upd["accno"] = $acc_code;
												$upd["doc_type"] = "GL";
												$upd["doc_no"] = $docNo;
												$upd["seq"] = sprintf("%d",$sequence);
												$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["refno"] = $refno;
												$upd["refno2"] = "";
												$upd["refno3"] = "";
												$upd["desp"] = $acc_name;
												$upd["desp2"] = $tax_name;
												$upd["desp3"] = "";
												$upd["desp4"] = "";
												$upd["amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["debit"] = $this->selling_price_currency_format(0);
												$upd["credit"] = $this->selling_price_currency_format($amount);
												$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
												$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
												$upd["fx_rate"] = 1;
												$upd["curr_code"] = $config["arms_currency"]["code"];
												$upd["taxcode"] = ($tax_code!="")?$val['tax_code']:"";
												$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["link_seq"] = 0;
												$upd["billtype"] = "H";
												$upd["remark1"] = "";
												$upd["remark2"] = "";
												$upd["batchno"] = "";
												$upd["projcode"] = "";
												$upd["deptcode"] = "";
												$upd["accmgr_id"] = "";
												$upd["cheque_no"] = "";
												my_fputcsv($fp, $upd);
												unset($upd, $tax_name);
												$sequence++;
											}
										}
										else{
											$amount = $value2;

											$upd["accno"] = $acc_code;
											$upd["doc_type"] = "GL";
											$upd["doc_no"] = $docNo;
											$upd["seq"] = sprintf("%d",$sequence);
											$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["refno"] = $refno;
											$upd["refno2"] = "";
											$upd["refno3"] = "";
											$upd["desp"] = $acc_name;
											$upd["desp2"] = "";
											$upd["desp3"] = "";
											$upd["desp4"] = "";
											$upd["amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["debit"] = $this->selling_price_currency_format(0);
											$upd["credit"] = $this->selling_price_currency_format($amount);
											$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
											$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
											$upd["fx_rate"] = 1;
											$upd["curr_code"] = $config["arms_currency"]["code"];
											$upd["taxcode"] = "";
											$upd["taxable"] = $this->selling_price_currency_format(0);
											$upd["fx_taxable"] = $this->selling_price_currency_format(0);
											$upd["link_seq"] = 0;
											$upd["billtype"] = "H";
											$upd["remark1"] = "";
											$upd["remark2"] = "";
											$upd["batchno"] = "";
											$upd["projcode"] = "";
											$upd["deptcode"] = "";
											$upd["accmgr_id"] = "";
											$upd["cheque_no"] = "";
											my_fputcsv($fp, $upd);
											unset($upd);
											$sequence++;
										}
									}
								}
							}

							if(isset($salesDetail['do'])){
								foreach($salesDetail['do'] as $receipt_no=>$salesDetail_do)
								{
									foreach($salesDetail_do['payment'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$val)
											{
												$amount = $val['debitamt'];

												$upd["accno"] = $acc_code;
												$upd["doc_type"] = "GL";
												$upd["doc_no"] = $receipt_no;
												$upd["seq"] = sprintf("%d",$sequence);
												$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["refno"] = $refno;
												$upd["refno2"] = "";
												$upd["refno3"] = "";
												$upd["desp"] = $acc_name;
												$upd["desp2"] = "";
												$upd["desp3"] = "";
												$upd["desp4"] = "";
												$upd["amount"] = $this->selling_price_currency_format($amount);
												$upd["debit"] = $this->selling_price_currency_format($amount);
												$upd["credit"] = $this->selling_price_currency_format(0);
												$upd["fx_amount"] = $this->selling_price_currency_format($amount);
												$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
												$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
												$upd["fx_rate"] = 1;
												$upd["curr_code"] = $config["arms_currency"]["code"];
												$upd["taxcode"] = "";
												$upd["taxable"] = $this->selling_price_currency_format(0);
												$upd["fx_taxable"] = $this->selling_price_currency_format(0);
												$upd["link_seq"] = 0;
												$upd["billtype"] = "H";
												$upd["remark1"] = "";
												$upd["remark2"] = "";
												$upd["batchno"] = "";
												$upd["projcode"] = "";
												$upd["deptcode"] = "";
												$upd["accmgr_id"] = "";
												$upd["cheque_no"] = "";
												my_fputcsv($fp, $upd);
												unset($upd);
												$sequence++;
											}
										}
									}

									foreach($salesDetail_do['sales'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$value2)
											{
												if(is_array($value2)){
													foreach($value2 as $tax_name=>$val){
														if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

														$amount = (isset($val['amt']))?$val['amt']:$val;
														$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

														$upd["accno"] = $acc_code;
														$upd["doc_type"] = "GL";
														$upd["doc_no"] = $receipt_no;
														$upd["seq"] = sprintf("%d",$sequence);
														$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["refno"] = $refno;
														$upd["refno2"] = "";
														$upd["refno3"] = "";
														$upd["desp"] = $acc_name;
														$upd["desp2"] = $tax_name;
														$upd["desp3"] = "";
														$upd["desp4"] = "";
														$upd["amount"] = $this->selling_price_currency_format(0-$amount);
														$upd["debit"] = $this->selling_price_currency_format(0);
														$upd["credit"] = $this->selling_price_currency_format($amount);
														$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
														$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
														$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
														$upd["fx_rate"] = 1;
														$upd["curr_code"] = $config["arms_currency"]["code"];
														$upd["taxcode"] = ($tax_code!="")?$val['tax_code']:"";
														$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
														$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
														$upd["link_seq"] = 0;
														$upd["billtype"] = "H";
														$upd["remark1"] = "";
														$upd["remark2"] = "";
														$upd["batchno"] = "";
														$upd["projcode"] = "";
														$upd["deptcode"] = "";
														$upd["accmgr_id"] = "";
														$upd["cheque_no"] = "";
														my_fputcsv($fp, $upd);
														unset($upd, $tax_name);
														$sequence++;
													}
												}
												else{
													$amount = $value2;

													$upd["accno"] = $acc_code;
													$upd["doc_type"] = "GL";
													$upd["doc_no"] = $docNo;
													$upd["seq"] = sprintf("%d",$sequence);
													$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["refno"] = $refno;
													$upd["refno2"] = $receipt_no;
													$upd["refno3"] = "";
													$upd["desp"] = $acc_name;
													$upd["desp2"] = "";
													$upd["desp3"] = "";
													$upd["desp4"] = "";
													$upd["amount"] = $this->selling_price_currency_format(0-$amount);
													$upd["debit"] = $this->selling_price_currency_format(0);
													$upd["credit"] = $this->selling_price_currency_format($amount);
													$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
													$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
													$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
													$upd["fx_rate"] = 1;
													$upd["curr_code"] = $config["arms_currency"]["code"];
													$upd["taxcode"] = "";
													$upd["taxable"] = $this->selling_price_currency_format(0);
													$upd["fx_taxable"] = $this->selling_price_currency_format(0);
													$upd["link_seq"] = 0;
													$upd["billtype"] = "H";
													$upd["remark1"] = "";
													$upd["remark2"] = "";
													$upd["batchno"] = "";
													$upd["projcode"] = "";
													$upd["deptcode"] = "";
													$upd["accmgr_id"] = "";
													$upd["cheque_no"] = "";
													my_fputcsv($fp, $upd);
													unset($upd);
													$sequence++;
												}
											}
										}
									}
								}
							}
						}
						$bn++;
					}

					unset($tmpSalesDetail, $posDate, $salesDetails, $receipt_no, $salesDetail, $acc_code, $value, $acc_name, $value2, $tax_name, $val);
					break;
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
											doc_no, ref_no, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											group by pos_date, ref_no, acc_type");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$posDate=$result['pos_date'];
						$receipt_no = $result['ref_no'];

						if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();

						if(!isset($tmpSalesDetail[$posDate][$receipt_no])){
							$tmpSalesDetail[$posDate][$receipt_no] = array();
							$tmpSalesDetail[$posDate][$receipt_no]['tablename'] = $result['tablename'];
							$tmpSalesDetail[$posDate][$receipt_no]['batchno'] = $result['batchno'];
							$tmpSalesDetail[$posDate][$receipt_no]['counter_id'] = $result['counter_id'];
							$tmpSalesDetail[$posDate][$receipt_no]['doc_no'] = $result['doc_no'];
							$tmpSalesDetail[$posDate][$receipt_no]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$receipt_no]['sales']['total_amount'] = 0;
						}

						$tmpSalesDetail[$posDate][$receipt_no]['payment']['total_amount'] += $result['TotalAmount'];

						if(!isset($tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']])){
							$tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
						}

						$tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];											
					}

					$tmpSalesDb->sql_freeresult($ret);

					$ret = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
											tax_code, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit'
											group by pos_date, ref_no, account_code, account_name, tax_code");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){						
						$posDate=$result['pos_date'];
						$receipt_no=$result['ref_no'];

						if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();

						if(!isset($tmpSalesDetail[$posDate][$receipt_no])){
							$tmpSalesDetail[$posDate][$receipt_no] = array();
							$tmpSalesDetail[$posDate][$receipt_no]['tablename'] = $result['tablename'];
							$tmpSalesDetail[$posDate][$receipt_no]['batchno'] = $result['batchno'];
							$tmpSalesDetail[$posDate][$receipt_no]['counter_id'] = $result['counter_id'];
							$tmpSalesDetail[$posDate][$receipt_no]['doc_no'] = $result['doc_no'];
							$tmpSalesDetail[$posDate][$receipt_no]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$receipt_no]['sales']['total_amount'] = 0;
						}

						$tmpSalesDetail[$posDate][$receipt_no]['sales']['total_amount'] += $result['TotalAmount'];

						if($result['tax_code']){
							if(!isset($tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']] += $result['ItemAmount'];

							if(!isset($tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] = 0;
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] = 0;
							}

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_amt'] += $result['ItemAmount'];
							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['amt'] += $result['TaxAmount'];
						}
						else{
							if(!isset($tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']]))
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']] = 0;

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
						}
					}
					$tmpSalesDb->sql_freeresult($ret);
					unset($result, $posDate);					
					foreach($tmpSalesDetail as $posDate=>$salesDetails)
					{
						$sequence=1;
						foreach($salesDetails as $receipt_no=>$salesDetail)
						{
							if($salesDetail['tablename']=='do'){
								$refno = $salesDetail['doc_no'];//$batchNo;
								$refno2 = $salesDetail['doc_no'];//$batchNo;
							}
							else{
								//$refno = sprintf("%03s%03s",$salesDetail['branch_id'],$salesDetail['counter_id']);
								$refno = $salesDetail['batchno'];
								$refno2 = $salesDetail['doc_no'];//$batchNo;
							}

							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$amount = $val['debitamt'];

										$upd["accno"] = $acc_code;
										$upd["doc_type"] = "GL";
										$upd["doc_no"] = $receipt_no;
										$upd["seq"] = sprintf("%d",$sequence);
										$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["refno"] = $refno;
										$upd["refno2"] = $refno2;
										$upd["refno3"] = "";
										$upd["desp"] = $acc_name;
										$upd["desp2"] = "";
										$upd["desp3"] = "";
										$upd["desp4"] = "";
										$upd["amount"] = $this->selling_price_currency_format($amount);
										$upd["debit"] = $this->selling_price_currency_format($amount);
										$upd["credit"] = $this->selling_price_currency_format(0);
										$upd["fx_amount"] = $this->selling_price_currency_format($amount);
										$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
										$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
										$upd["fx_rate"] = 1;
										$upd["curr_code"] = $config["arms_currency"]["code"];
										$upd["taxcode"] = "";
										$upd["taxable"] = $this->selling_price_currency_format(0);
										$upd["fx_taxable"] = $this->selling_price_currency_format(0);
										$upd["link_seq"] = 0;
										$upd["billtype"] = "H";
										$upd["remark1"] = "";
										$upd["remark2"] = "";
										$upd["batchno"] = "";
										$upd["projcode"] = "";
										$upd["deptcode"] = "";
										$upd["accmgr_id"] = "";
										$upd["cheque_no"] = "";
										my_fputcsv($fp, $upd);
										unset($upd);
										$sequence++;
									}
								}
							}

							foreach($salesDetail['sales'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$value2)
									{
										if(is_array($value2)){
											foreach($value2 as $tax_name=>$val){
												if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

												$amount = (isset($val['amt']))?$val['amt']:$val;
												$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

												$upd["accno"] = $acc_code;
												$upd["doc_type"] = "GL";
												$upd["doc_no"] = $receipt_no;
												$upd["seq"] = sprintf("%d",$sequence);
												$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["refno"] = $refno;
												$upd["refno2"] = $refno2;
												$upd["refno3"] = "";
												$upd["desp"] = $acc_name;
												$upd["desp2"] = $tax_name;
												$upd["desp3"] = "";
												$upd["desp4"] = "";
												$upd["amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["debit"] = $this->selling_price_currency_format(0);
												$upd["credit"] = $this->selling_price_currency_format($amount);
												$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
												$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
												$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
												$upd["fx_rate"] = 1;
												$upd["curr_code"] = $config["arms_currency"]["code"];
												$upd["taxcode"] = ($tax_code!="")?$val['tax_code']:"";
												$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(0-$val['total_amt']):$this->selling_price_currency_format(0);
												$upd["link_seq"] = 0;
												$upd["billtype"] = "H";
												$upd["remark1"] = "";
												$upd["remark2"] = "";
												$upd["batchno"] = "";
												$upd["projcode"] = "";
												$upd["deptcode"] = "";
												$upd["accmgr_id"] = "";
												$upd["cheque_no"] = "";
												my_fputcsv($fp, $upd);
												unset($upd, $tax_name);
												$sequence++;
											}
										}
										else{
											$amount = $value2;

											$upd["accno"] = $acc_code;
											$upd["doc_type"] = "GL";
											$upd["doc_no"] = $receipt_no;
											$upd["seq"] = sprintf("%d",$sequence);
											$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["refno"] = $refno;
											$upd["refno2"] = $refno2;
											$upd["refno3"] = "";
											$upd["desp"] = $acc_name;
											$upd["desp2"] = "";
											$upd["desp3"] = "";
											$upd["desp4"] = "";
											$upd["amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["debit"] = $this->selling_price_currency_format(0);
											$upd["credit"] = $this->selling_price_currency_format($amount);
											$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
											$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
											$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
											$upd["fx_rate"] = 1;
											$upd["curr_code"] = $config["arms_currency"]["code"];
											$upd["taxcode"] = "";
											$upd["taxable"] = $this->selling_price_currency_format(0);
											$upd["fx_taxable"] = $this->selling_price_currency_format(0);
											$upd["link_seq"] = 0;
											$upd["billtype"] = "H";
											$upd["remark1"] = "";
											$upd["remark2"] = "";
											$upd["batchno"] = "";
											$upd["projcode"] = "";
											$upd["deptcode"] = "";
											$upd["accmgr_id"] = "";
											$upd["cheque_no"] = "";
											my_fputcsv($fp, $upd);
											unset($upd);
											$sequence++;
										}
									}
								}
							}
						}
					}
					unset($tmpSalesDetail, $posDate, $salesDetails, $receipt_no, $salesDetail, $acc_code, $value, $acc_name, $value2, $tax_name, $val);
				break;
			}
			fclose($fp);
		}
		return $total['total'];
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
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
	
			$ret = $this->sql_query($tmpSalesDb, "select batchno, gl_code, gl_name, vendor_terms, vendor_code, vendor_name, inv_date, inv_no,
										  round(sum(ItemAmount),2) as ItemAmount,
										  round(sum(TaxAmount),2) as TaxAmount,
										  round(sum(TotalAmount),2) as TotalAmount									  
										  from ".$this->tmpTable."
										  group by vendor_code, vendor_id, inv_date, inv_no
										  order by inv_date,inv_no");

			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				
				//$batchNo = $this->create_batch_no(date("ymd",strtotime($result['inv_date'])),$bn);
				$batchNo = $result['batchno'];
				$InvDate = $this->set_date($this->ExportDateFormat,$result['inv_date']);
				$docNo = $result['inv_no']; //sprintf("%07d",$bn);
				$sequence = 1;								
			
				$ret2 = $this->sql_query($tmpSalesDb, "select round(sum(ItemAmount),2) as ItemAmount,
														round(sum(TaxAmount),2) as TaxAmount, 
														taxCode, tax_account_code, tax_account_name
														from ".$this->tmpTable."
														where inv_date = ".ms($result['inv_date'])."
														and inv_no=".ms($result['inv_no'])."
														and vendor_code = ".ms($result['vendor_code'])."
														group by taxCode");



				if($tmpSalesDb->sql_numrows($ret2)>0)
				{
					while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret2))
					{
						if(!isset($purchasebyTax[$result2['taxCode']]))
						{
							$purchasebyTax[$result2['taxCode']]['gst_amount'] = 0;
							$purchasebyTax[$result2['taxCode']]['ItemAmount'] = 0;
						}
						
						if(!isset($inputTaxValue[$result2['taxCode']][$result2['tax_account_code']][$result2['tax_account_name']]))
						{
							$inputTaxValue[$result2['taxCode']][$result2['tax_account_code']][$result2['tax_account_name']]['gst_amount'] = 0;
							$inputTaxValue[$result2['taxCode']][$result2['tax_account_code']][$result2['tax_account_name']]['ItemAmount'] = 0;
						}
						$purchasebyTax[$result2['taxCode']]['gst_amount'] += $result2['TaxAmount'];
						$purchasebyTax[$result2['taxCode']]['ItemAmount'] += $result2['ItemAmount'];
						$inputTaxValue[$result2['taxCode']][$result2['tax_account_code']][$result2['tax_account_name']]['gst_amount']+= $result2['TaxAmount'];
						$inputTaxValue[$result2['taxCode']][$result2['tax_account_code']][$result2['tax_account_name']]['ItemAmount']+= $result2['ItemAmount'];
					}					
				}

				$tmpSalesDb->sql_freeresult($ret2);
				if(isset($purchasebyTax))
				{
					foreach($purchasebyTax as $taxCode=>$value)	
					{
						$upd["accno"] = $result['gl_code'];
						$upd["doc_type"] = "GL";
						$upd["doc_no"] = $docNo;
						$upd["seq"] = sprintf("%d",$sequence);
						$upd["doc_date"] = $InvDate;
						$upd["refno"] = $docNo;
						$upd["refno2"] = $batchNo;
						$upd["refno3"] = "";
						$upd["desp"] = $result['gl_name'];
						$upd["desp2"] = "";
						$upd["desp3"] = "";
						$upd["desp4"] = "";
						$upd["amount"] = $this->selling_price_currency_format($value['ItemAmount']);
						$upd["debit"] = $this->selling_price_currency_format($value['ItemAmount']);
						$upd["credit"] = $this->selling_price_currency_format(0);
						$upd["fx_amount"] = $this->selling_price_currency_format($value['ItemAmount']);
						$upd["fx_debit"] = $this->selling_price_currency_format($value['ItemAmount']);
						$upd["fx_credit"] = $this->selling_price_currency_format(0);
						$upd["fx_rate"] = 1;
						$upd["curr_code"] = $config["arms_currency"]["code"];
						$upd["taxcode"] = $taxCode;
						$upd["taxable"] = "";
						$upd["fx_taxable"] = "";
						$upd["link_seq"] = 0;
						$upd["billtype"] = "I";
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						$upd["batchno"] = "";
						$upd["projcode"] = "";
						$upd["deptcode"] = "";
						$upd["accmgr_id"] = "";
						$upd["cheque_no"] = "";
						my_fputcsv($fp, $upd);
						unset($upd);
						$sequence++;					
					}
					unset($purchasebyTax);
				}
				
				if(isset($inputTaxValue))
				{
					foreach($inputTaxValue as $taxCode=>$value)
					{										
						foreach($value as $taxAccCode=>$val)
						{
							foreach($val as $taxAccName=>$val2)
							{															
								$upd["accno"] = $taxAccCode;
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $docNo;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $InvDate;
								$upd["refno"] = $docNo;
								$upd["refno2"] = $batchNo;
								$upd["refno3"] = "";
								$upd["desp"] = $taxAccName;
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format($val2['gst_amount']);
								$upd["debit"] = $this->selling_price_currency_format($val2['gst_amount']);
								$upd["credit"] = $this->selling_price_currency_format(0);
								$upd["fx_amount"] = $this->selling_price_currency_format($val2['gst_amount']);
								$upd["fx_debit"] = $this->selling_price_currency_format($val2['gst_amount']);
								$upd["fx_credit"] = $this->selling_price_currency_format(0);
								$upd["fx_rate"] = 1;
								$upd["curr_code"] = $config["arms_currency"]["code"];
								$upd["taxcode"] = $taxCode;
								$upd["taxable"] = $this->selling_price_currency_format($val2['ItemAmount']);
								$upd["fx_taxable"] = $this->selling_price_currency_format(0);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "I";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;								
							}
						}
					}
					unset($inputTaxValue);
				}
				
				$upd["accno"] = $result['vendor_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $docNo;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $InvDate;
				$upd["refno"] = $docNo;
				$upd["refno2"] = $batchNo;
				$upd["refno3"] = "";
				$upd["desp"] = $result['vendor_name'];
				$upd["desp2"] = "";
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format((0-$result['TotalAmount']));
				$upd["debit"] = $this->selling_price_currency_format(0);
				$upd["credit"] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd["fx_amount"] = $this->selling_price_currency_format((0-$result['TotalAmount']));
				$upd["fx_debit"] = $this->selling_price_currency_format(0);
				$upd["fx_credit"] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd["fx_rate"] = 1;
				$upd["curr_code"] = $config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = "";
				$upd["fx_taxable"] = "";
				$upd["link_seq"] = 0;
				$upd["billtype"] = "I";
				$upd["remark1"] = "";
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				unset($upd);
				$sequence++;
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);			
			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_receiver($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));

            $ret = $this->sql_query($tmpSalesDb, "select batchno, do_date, inv_no, customer_code, customer_name, account_code, account_name,
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

            while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$invNo=$result['inv_no'];
				if(!isset($tmpSalesDetail[$invNo]))
				{
					$tmpSalesDetail[$invNo]=array();
					$tmpSalesDetail[$invNo]['batchno']=$result['batchno'];
					$tmpSalesDetail[$invNo]['do_date']=$result['do_date'];
					$tmpSalesDetail[$invNo]['currencyrate']=$result['currencyrate'];
					$tmpSalesDetail[$invNo]['currency_code']=$result['foreign_currency_code'];
					$tmpSalesDetail[$invNo]['payment']['account_code'] = $result['customer_code'];
					$tmpSalesDetail[$invNo]['payment']['account_name'] = $result['customer_name'];
					$tmpSalesDetail[$invNo]['payment']['debitamt']=0;
					$tmpSalesDetail[$invNo]['payment']['debitfamt']=0;
					$tmpSalesDetail[$invNo]['sales']['creditamt'] = 0;
					$tmpSalesDetail[$invNo]['sales']['creditfamt'] = 0;
				}

				$tmpSalesDetail[$invNo]['payment']['debitamt'] += $result["TotalAmount"];
				$tmpSalesDetail[$invNo]['payment']['debitfamt'] += round($result["TotalFAmount"],2);
				$tmpSalesDetail[$invNo]['sales']['creditamt'] += $result["TotalAmount"];
				$tmpSalesDetail[$invNo]['sales']['creditfamt'] += round($result["TotalFAmount"],2);

				if($result['tax_code'])
				{
					if(!isset($tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
						$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] = 0;
						$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] = 0;
					}

					$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] += $result['ItemAmount'];
					$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] += $result['ItemFAmount'];

					if(!isset($tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
						$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
						$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] = 0;
						$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditamt'] = 0;
						$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] = 0;
						$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditfamt'] = 0;
					}

					$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] += $result['TaxAmount'];
					$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditamt'] += $result['ItemAmount'];
					$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] += $result['TaxFAmount'];
					$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditfamt'] += $result['ItemFAmount'];
				}
				else
				{
					if(!isset($tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']])){
						$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']]['creditamt'] = 0;
						$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']]['creditfamt'] = 0;
					}

					$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']]['creditamt'] += $result['ItemAmount'];
					$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']]['creditfamt'] += $result['ItemFAmount'];
				}
			}
			$tmpSalesDb->sql_freeresult($ret);

			$bn = 1;
			foreach($tmpSalesDetail as $invNo=>$salesDetail)
			{
				$sequence = 1;
				$doDate=$salesDetail['do_date'];
				$docNo = sprintf("%07d",$bn);
				$refno = $salesDetail['batchno'];

				if(isset($salesDetail['payment']))
				{
					$upd["accno"] = $salesDetail['payment']['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $docNo;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$doDate);
					$upd["refno"] = $refno;
					$upd["refno2"] = $invNo;
					$upd["refno3"] = "";
					$upd["desp"] = $salesDetail['payment']['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($salesDetail['payment']['debitamt']);
					$upd["debit"] = $this->selling_price_currency_format($salesDetail['payment']['debitamt']);
					$upd["credit"] = 0;
					$upd["fx_amount"] = $this->selling_price_currency_format(($salesDetail['payment']['debitfamt']>0)?$salesDetail['payment']['debitfamt']:$salesDetail['payment']['debitamt']);
					$upd["fx_debit"] = $this->selling_price_currency_format(($salesDetail['payment']['debitfamt']>0)?$salesDetail['payment']['debitfamt']:$salesDetail['payment']['debitamt']);
					$upd["fx_credit"] = 0;
					$upd["fx_rate"] = $salesDetail['currencyrate'];
					$upd["curr_code"] = ($salesDetail['currency_code']!="XXX"?$salesDetail['currency_code']:$config["arms_currency"]["code"]);
					$upd["taxcode"] = "";
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "I";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
				}

				foreach($salesDetail['sales'] as $acc_code=>$value){
					if(is_array($value))
					{
						foreach($value as $acc_name=>$value2)
						{
							foreach($value2 as $tax_name=>$val)
							{
								if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

								$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

								$upd["accno"] = $acc_code;
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $docNo;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$doDate);
								$upd["refno"] = $refno;
								$upd["refno2"] = $invNo;
								$upd["refno3"] = "";
								$upd["desp"] = $acc_name;
								$upd["desp2"] = $tax_name;
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format(0-$val['creditamt']);
								$upd["debit"] = 0;
								$upd["credit"] = $this->selling_price_currency_format($val['creditamt']);
								$upd["fx_amount"] = $this->selling_price_currency_format(0-(($val['creditfamt']>0)?$val['creditfamt']:$val['creditamt']));
								$upd["fx_debit"] = 0;
								$upd["fx_credit"] = $this->selling_price_currency_format(($val['creditfamt']>0)?$val['creditfamt']:$val['creditamt']);
								$upd["fx_rate"] = $salesDetail['currencyrate'];;
								$upd["curr_code"] = ($salesDetail['currency_code']!="XXX"?$salesDetail['currency_code']:$config["arms_currency"]["code"]);
								$upd["taxcode"] = $tax_code;
								$upd["taxable"] = ($tax_code!="")?$this->selling_price_currency_format($val['total_creditamt']):$this->selling_price_currency_format(0);
								$upd["fx_taxable"] = ($tax_code!="")?$this->selling_price_currency_format(($val['total_creditfamt']>0)?$val['total_creditfamt']:$val['total_creditamt']):$this->selling_price_currency_format(0);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "I";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;
							}
						}
					}
				}
				$bn++;
			}
			unset($tmpSalesDetail);
			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
			/*$ret = $this->sql_query($tmpSalesDb, "select receipt_no, currencyrate, foreign_currency_code,
											credit_note_no, pos_date as cn_date, return_date, account_code, account_name,
											goods_return_reason as remark, cash_refund,
											sum(ItemAmount) as ItemAmount,
											sum(TaxAmount) as TaxAmount,
											sum(TotalAmount) as TotalAmount,
											sum(foreign_currency_amount) as ItemFAmount,
											sum(foreign_currency_gst_amount-foreign_currency_amount) as TaxFAmount,
											sum(foreign_currency_gst_amount) as TotalFAmount
											from ".$this->tmpTable."
											group by credit_note_no, cn_date, receipt_no, return_date
											order by cn_date, return_date");*/

			$ret = $this->sql_query($tmpSalesDb, "select batchno, return_receipt_no, credit_note_no, date, currencyrate, currency_code,
															account_code, account_name,
											sum(TotalAmount) as TotalAmount,
											sum(TotalFAmount) as TotalFAmount
											from ".$this->tmpTable."
											group by date, return_receipt_no, credit_note_no
											order by date, return_receipt_no, credit_note_no");
			
			$bn = 1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$sequence = 1;
				$refno = $cn['credit_note_no'];
				$refno1 = $cn['batchno'];
				$docNo = sprintf("%07d",$bn);
				$amount = $cn['TotalAmount'];
				$amountF = ($cn['TotalFAmount']>0)?$cn['TotalFAmount']:$cn['TotalAmount'];				
				
				$upd["accno"] = $cn['account_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $docNo;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
				$upd["refno"] = $refno;
				$upd["refno2"] = $refno1;
				$upd["refno3"] = "";
				$upd["desp"] = "Credit Note";
				$upd["desp2"] = $cn['account_name'];
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format($amount);
				$upd["debit"] = $this->selling_price_currency_format(0);
				$upd["credit"] = $this->selling_price_currency_format(abs($amount));
				$upd["fx_amount"] = $this->selling_price_currency_format($amountF);
				$upd["fx_debit"] = $this->selling_price_currency_format(0);
				$upd["fx_credit"] = $this->selling_price_currency_format(abs($amountF));
				$upd["fx_rate"] = $cn['currencyrate'];
				$upd["curr_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = $this->selling_price_currency_format(0);
				$upd["fx_taxable"] = $this->selling_price_currency_format(0);
				$upd["link_seq"] = 0;
				$upd["billtype"] = "C";
				$upd["remark1"] = $cn['remark'];
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				$sequence++;
				unset($upd);
				
				$cond = "where date=".ms($cn['date'])." and return_receipt_no = ".ms($cn['return_receipt_no'])." and credit_note_no = ".ms($cn['credit_note_no']);
				$ret2 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, customer_name, tax_account_code, tax_account_name,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												sum(ItemFAmount) as ItemFAmount,
												sum(TaxFAmount) as TaxFAmount,
												sum(TotalFAmount) as TotalFAmount
												from ".$this->tmpTable." $cond
												group by date, return_receipt_no, credit_note_no, date, tax_code
												order by date");
				while($cnTax = $this->sql_fetchrow($tmpSalesDb, $ret2))
				{
					$amount = $cnTax['ItemAmount'];
					$amountF = ($cnTax['ItemFAmount']>0)?$cnTax['ItemFAmount']:$cnTax['ItemAmount'];
					$tax = $cnTax['TaxAmount'];
					$taxF = ($cnTax['TaxFAmount']>0)?$cnTax['TaxFAmount']:$cnTax['TaxAmount'];
					
					$upd["accno"] = $cnTax['customer_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $docNo;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno1;
					$upd["refno3"] = "";
					$upd["desp"] = "Credit Note";
					$upd["desp2"] = $cnTax['customer_name'];
					$upd["desp3"] = $cnTax['tax_code'];
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($amount);
					$upd["debit"] = $this->selling_price_currency_format($amount);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($amountF);
					$upd["fx_debit"] = $this->selling_price_currency_format($amountF);
					$upd["fx_credit"] = $this->selling_price_currency_format(0);
					$upd["fx_rate"] = $cn['currencyrate'];
					$upd["curr_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
					$upd["taxcode"] = "";
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "C";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					$sequence++;
					unset($upd);					
					
					$upd["accno"] = $cnTax['tax_account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $docNo;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno1;
					$upd["refno3"] = "";
					$upd["desp"] = "Credit Note";
					$upd["desp2"] = $cnTax['tax_account_name'];
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($tax);
					$upd["debit"] = $this->selling_price_currency_format($tax);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($taxF);
					$upd["fx_debit"] = $this->selling_price_currency_format($taxF);
					$upd["fx_credit"] = $this->selling_price_currency_format(0);
					$upd["fx_rate"] = $cn['currencyrate'];
					$upd["curr_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
					$upd["taxcode"] = $cnTax['tax_code'];
					$upd["taxable"] = ($cnTax['tax_code']!="")?$this->selling_price_currency_format($amount):$this->selling_price_currency_format(0);
					$upd["fx_taxable"] = ($cnTax['tax_code']!="")?$this->selling_price_currency_format($amountF):$this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "C";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					$sequence++;
					unset($upd);					
				}
				$tmpSalesDb->sql_freeresult($ret2);
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
			$ret = $this->sql_query($tmpSalesDb, "select batchno, date, 
													invoice_no, customer_code, 
													customer_name, 
													currencyrate, currency_code,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount
													from ".$this->tmpTable."
													group by date, invoice_no, customer_code
													order by date, invoice_no");

			$sequence=1;
			while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$docNo = $dn['invoice_no'];


				$cond = "where date = ".ms($dn['date'])."
						 and invoice_no = ".ms($dn['invoice_no'])."
						 and customer_code = ".ms($dn['customer_code']);
				$ret2 = $this->sql_query($tmpSalesDb, "select tax_code, account_code, account_name, tax_account_name, tax_account_code,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												sum(ItemFAmount) as ItemFAmount,
												sum(TaxFAmount) as TaxFAmount,
												sum(TotalFAmount) as TotalFAmount
												from ".$this->tmpTable." $cond
												group by tax_code");
				while($dnTax = $this->sql_fetchrow($tmpSalesDb, $ret2))
				{
					$amount = $dnTax['ItemAmount'];
					$amountF = ($dnTax['ItemFAmount']>0)?$dnTax['ItemFAmount']:$dnTax['ItemAmount'];
					$tax = $dnTax['TaxAmount'];
					$taxF = ($dnTax['TaxFAmount']>0)?$dnTax['TaxFAmount']:$dnTax['TaxAmount'];
					if(!isset($dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]))
					{
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemAmount'] = 0;
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxAmount'] = 0;
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalAmount'] = 0;
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemFAmount'] = 0;
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxFAmount'] = 0;
						$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalFAmount'] = 0;
					}
					
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemAmount'] += $dnTax['ItemAmount'];
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxAmount'] += $dnTax['TaxAmount'];
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalAmount'] += $dnTax['TotalAmount'];
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemFAmount'] += $dnTax['ItemFAmount'];
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxFAmount'] += $dnTax['TaxFAmount'];
					$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalFAmount'] += $dnTax['TotalFAmount'];
					
					if(!isset($dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]))
					{
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemAmount'] = 0;
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxAmount'] = 0;
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalAmount'] = 0;
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemFAmount'] = 0;
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxFAmount'] = 0;
						$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalFAmount'] = 0;
					}
					
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemAmount'] += $dnTax['ItemAmount'];
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxAmount'] += $dnTax['TaxAmount'];
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalAmount'] += $dnTax['TotalAmount'];
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemFAmount'] += $dnTax['ItemFAmount'];
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxFAmount'] += $dnTax['TaxFAmount'];
					$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalFAmount'] += $dnTax['TotalFAmount'];
					
				}
				$tmpSalesDb->sql_freeresult($ret2);
				
				$upd["accno"] = $dn['customer_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $docNo;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["refno"] = $docNo;
				$upd["refno2"] = $dn['batchno'];
				$upd["refno3"] = "";
				$upd["desp"] = $dn['customer_name'];
				$upd["desp2"] = "";
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["debit"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["credit"] = $this->selling_price_currency_format(0);
				$upd["fx_amount"] = $this->selling_price_currency_format(($dn['TotalFAmount']>0)?$dn['TotalFAmount']:$dn['TotalAmount']);
				$upd["fx_debit"] = $this->selling_price_currency_format(($dn['TotalFAmount']>0)?$dn['TotalFAmount']:$dn['TotalAmount']);
				$upd["fx_credit"] = $this->selling_price_currency_format(0);
				$upd["fx_rate"] = $dn['currencyrate'];
				$upd["curr_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = "";
				$upd["fx_taxable"] = "";
				$upd["link_seq"] = 0;
				$upd["billtype"] = "C";
				$upd["remark1"] = "";
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				$sequence++;
				unset($upd);
				if(isset($dnInfo) && $dnInfo)
				{
					foreach($dnInfo as $txCode=>$val)
					{
						foreach($val as $accCode=>$val2)
						{
							foreach($val2 as $accName=>$val3)
							{
								$amount = $val3['ItemAmount'];
								$amountF = ($val3['ItemFAmount']>0)?$val3['ItemFAmount']:$val3['ItemAmount'];
								$tax = $val3['TaxAmount'];
								$taxF = ($val3['TaxFAmount']>0)?$val3['TaxFAmount']:0;
								$upd["accno"] = $accCode;
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $docNo;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
								$upd["refno"] = $docNo;
								$upd["refno2"] = $dn['batchno'];
								$upd["refno3"] = "";
								$upd["desp"] = $accName;
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format(0-$amount);
								$upd["debit"] = $this->selling_price_currency_format(0);
								$upd["credit"] = $this->selling_price_currency_format($amount);
								$upd["fx_amount"] = $this->selling_price_currency_format(0-$amountF);
								$upd["fx_debit"] = $this->selling_price_currency_format(0);
								$upd["fx_credit"] = $this->selling_price_currency_format($amountF);
								$upd["fx_rate"] = $dn['currencyrate'];
								$upd["curr_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
								$upd["taxcode"] = $txCode;
								$upd["taxable"] = "";
								$upd["fx_taxable"] = "";
								$upd["link_seq"] = 0;
								$upd["billtype"] = "C";
								$upd["remark1"] = $dn['reason'];
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								$sequence++;
								unset($upd);

							}
						}
					}
				}
				unset($dnInfo);
				
				if(isset($dnInputTax) && $dnInputTax)
				{
					foreach($dnInputTax as $txCode=>$val)
					{
						foreach($val as $txAccCode=>$val2)
						{
							foreach($val2 as $txAccName=>$val3)
							{
								$amountF = ($val3['ItemFAmount']>0)?$val3['ItemFAmount']:$val3['ItemAmount'];
								$tax = $val3['TaxAmount'];
								$taxF = ($val3['TaxFAmount']>0)?$val3['TaxFAmount']:$val3['TaxAmount'];
								$upd["accno"] = $txAccCode;
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $docNo;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
								$upd["refno"] = $docNo;
								$upd["refno2"] = $dn['batchno'];
								$upd["refno3"] = "";
								$upd["desp"] = $txAccName;
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format(0-$tax);
								$upd["debit"] = $this->selling_price_currency_format(0);
								$upd["credit"] = $this->selling_price_currency_format($tax);
								$upd["fx_amount"] = $this->selling_price_currency_format(0-$taxF);
								$upd["fx_debit"] = $this->selling_price_currency_format(0);
								$upd["fx_credit"] = $this->selling_price_currency_format($taxF);
								$upd["fx_rate"] = $dn['currencyrate'];
								$upd["curr_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
								$upd["taxcode"] = $txCode;
								$upd["taxable"] = $this->selling_price_currency_format(0-$val3['ItemAmount']);
								$upd["fx_taxable"] = $this->selling_price_currency_format(0-$amountF);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "C";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								$sequence++;
								unset($upd);
							}
						}
					}
				}
				
				unset($dnInputTax);
				
			}
			$tmpSalesDb->sql_freeresult($ret);

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
		global $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
			//Export Cash Sales From POS Counter

			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, ref_no
													from ".$this->tmpTable." 
													where tablename = ".ms("pos")."
													group by pos_date, doc_no order by pos_date,doc_no");									

			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				//Return Sales Transaction
				$sequence = 1;
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
						$sequence = 1;
						$wcond[] = "`type`=".ms("debit");
						$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
						$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
						$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
						$wcond[] = "`type`!=".ms('deposit');
						$wcond[] = "`acc_type`!=".ms('rounding');
						$wcond[] = "`is_credit_notes`=".ms(1);
						
						$retCNDebit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
											credit_note_no, return_receipt_no, account_code, account_name, tax_code,acc_type,
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
								
								$amount = abs($resultCNDebit['ItemAmount']);
								$upd["accno"] = $resultCNDebit['account_code'];
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $refno;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
								$upd["refno"] = $refno;
								$upd["refno2"] = $refno2;
								$upd["refno3"] = "";
								$upd["desp"] = $resultCNDebit['account_name'];
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format($amount);
								$upd["debit"] = $this->selling_price_currency_format($amount);
								$upd["credit"] = $this->selling_price_currency_format(0);
								$upd["fx_amount"] = $this->selling_price_currency_format($amount);
								$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
								$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
								$upd["fx_rate"] = 1;
								$upd["curr_code"] = $config["arms_currency"]["code"];
								$upd["taxcode"] = $resultCNDebit['tax_code'];
								$upd["taxable"] = $this->selling_price_currency_format(0);
								$upd["fx_taxable"] = $this->selling_price_currency_format(0);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "C";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;
								
								$taxAmt = abs($resultCNDebit['TaxAmount']);
								//Input Tax
								$upd["accno"] = $resultCNDebit['tax_account_code'];
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $refno;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
								$upd["refno"] = $refno;
								$upd["refno2"] = $refno2;
								$upd["refno3"] = "";
								$upd["desp"] = $resultCNDebit['tax_account_name'];
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format($taxAmt);
								$upd["debit"] = $this->selling_price_currency_format($taxAmt);
								$upd["credit"] = $this->selling_price_currency_format(0);
								$upd["fx_amount"] = $this->selling_price_currency_format($taxAmt);
								$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
								$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
								$upd["fx_rate"] = 1;
								$upd["curr_code"] = $config["arms_currency"]["code"];
								$upd["taxcode"] = $resultCNDebit['tax_code'];
								$upd["taxable"] = $this->selling_price_currency_format($amount);
								$upd["fx_taxable"] = $this->selling_price_currency_format($amount);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "H";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;
								
								//Get Credit Goods Exchange Contra
								if(!$resultCN["is_refund"]){
									$amount = abs($resultCNDebit['TotalAmount']);
									$upd["accno"] = $this->accSettings["goods_exchange_contra"]["account"]["account_code"];
									$upd["doc_type"] = "GL";
									$upd["doc_no"] = $refno;
									$upd["seq"] = sprintf("%d",$sequence);
									$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
									$upd["refno"] = $refno;
									$upd["refno2"] = $refno2;
									$upd["refno3"] = "";
									$upd["desp"] = $this->accSettings["goods_exchange_contra"]["account"]["account_name"];
									$upd["desp2"] = "";
									$upd["desp3"] = "";
									$upd["desp4"] = "";
									$upd["amount"] = $this->selling_price_currency_format(0-$amount);
									$upd["debit"] = $this->selling_price_currency_format(0);
									$upd["credit"] = $this->selling_price_currency_format($amount);
									$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
									$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
									$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
									$upd["fx_rate"] = 1;
									$upd["curr_code"] = $config["arms_currency"]["code"];
									$upd["taxcode"] = "";
									$upd["taxable"] = $this->selling_price_currency_format(0);
									$upd["fx_taxable"] = $this->selling_price_currency_format(0);
									$upd["link_seq"] = 0;
									$upd["billtype"] = "H";
									$upd["remark1"] = "";
									$upd["remark2"] = "";
									$upd["batchno"] = "";
									$upd["projcode"] = "";
									$upd["deptcode"] = "";
									$upd["accmgr_id"] = "";
									$upd["cheque_no"] = "";
									my_fputcsv($fp, $upd);
									unset($upd);
								}
							}
						}
						$tmpSalesDb->sql_freeresult($retCNDebit);
						
						$sequence = 1;
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
								
								$amount = ($resultCNDebit['ItemAmount']);
								$upd["accno"] = $resultCNDebit['account_code'];
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $refno;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
								$upd["refno"] = $refno;
								$upd["refno2"] = $refno2;
								$upd["refno3"] = "";
								$upd["desp"] = $resultCNDebit['account_name'];
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								$upd["amount"] = $this->selling_price_currency_format(0-$amount);
								$upd["debit"] = $this->selling_price_currency_format(0-$amount);
								$upd["credit"] = $this->selling_price_currency_format(0);
								$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
								$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
								$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
								$upd["fx_rate"] = 1;
								$upd["curr_code"] = $config["arms_currency"]["code"];
								$upd["taxcode"] = "";
								$upd["taxable"] = $this->selling_price_currency_format(0);
								$upd["fx_taxable"] = $this->selling_price_currency_format(0);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "C";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;
							}
						}
						$tmpSalesDb->sql_freeresult($retCNDebit);
						
						//Get Credit Note Credit Information
						//$sequence = 1;
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
								
								$amount = $resultCNCredit['ItemAmount'];
								$upd["accno"] = $resultCNCredit['account_code'];
								$upd["doc_type"] = "GL";
								$upd["doc_no"] = $refno;
								$upd["seq"] = sprintf("%d",$sequence);
								$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
								$upd["refno"] = $refno;
								$upd["refno2"] = $refno2;
								$upd["refno3"] = "";
								$upd["desp"] = $resultCNCredit['account_name'];
								$upd["desp2"] = "";
								$upd["desp3"] = "";
								$upd["desp4"] = "";
								if($resultCNCredit["acc_type"] == "rounding"){
									$upd["amount"] = $this->selling_price_currency_format(0-$amount);
									$upd["debit"] = $this->selling_price_currency_format(0);
									$upd["credit"] = $this->selling_price_currency_format($amount);
									$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
								}else{
									$upd["amount"] = $this->selling_price_currency_format($amount);
									$upd["debit"] = $this->selling_price_currency_format(0);
									$upd["credit"] = $this->selling_price_currency_format(abs($amount));
									$upd["fx_amount"] = $this->selling_price_currency_format($amount);
								}
								$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
								$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
								$upd["fx_rate"] = 1;
								$upd["curr_code"] = $config["arms_currency"]["code"];
								$upd["taxcode"] = "";
								$upd["taxable"] = $this->selling_price_currency_format(0);
								$upd["fx_taxable"] = $this->selling_price_currency_format(0);
								$upd["link_seq"] = 0;
								$upd["billtype"] = "C";
								$upd["remark1"] = "";
								$upd["remark2"] = "";
								$upd["batchno"] = "";
								$upd["projcode"] = "";
								$upd["deptcode"] = "";
								$upd["accmgr_id"] = "";
								$upd["cheque_no"] = "";
								my_fputcsv($fp, $upd);
								unset($upd);
								$sequence++;
							}
							
						}
						$tmpSalesDb->sql_freeresult($resultCNCredit);
						
						//Get Debit Goods Exchange Contra
						if(!$resultCN["is_refund"]){
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
									
									$amount = $resultCNCredit['TotalAmount'];
									$upd["accno"] = $acc_code;
									$upd["doc_type"] = "GL";
									$upd["doc_no"] = $refno;
									$upd["seq"] = sprintf("%d",$sequence);
									$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
									$upd["refno"] = $refno;
									$upd["refno2"] = $refno2;
									$upd["refno3"] = "";
									$upd["desp"] = $acc_name;
									$upd["desp2"] = "";
									$upd["desp3"] = "";
									$upd["desp4"] = "";
									$upd["amount"] = $this->selling_price_currency_format(abs($amount));
									$upd["debit"] = $this->selling_price_currency_format(abs($amount));
									$upd["credit"] = $this->selling_price_currency_format(0);
									$upd["fx_amount"] = $this->selling_price_currency_format(abs($amount));
									$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
									$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
									$upd["fx_rate"] = 1;
									$upd["curr_code"] = $config["arms_currency"]["code"];
									$upd["taxcode"] = "";
									$upd["taxable"] = $this->selling_price_currency_format(0);
									$upd["fx_taxable"] = $this->selling_price_currency_format(0);
									$upd["link_seq"] = 0;
									$upd["billtype"] = "H";
									$upd["remark1"] = "";
									$upd["remark2"] = "";
									$upd["batchno"] = "";
									$upd["projcode"] = "";
									$upd["deptcode"] = "";
									$upd["accmgr_id"] = "";
									$upd["cheque_no"] = "";
									my_fputcsv($fp, $upd);
									unset($upd);
									$sequence++;
								}
								
							}
							$tmpSalesDb->sql_freeresult($resultCNCredit);
						}
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
						
						$amount = $resultDebitAccType['TotalAmount'];
						$upd["accno"] = $resultDebitAccType['account_code'];
						$upd["doc_type"] = "GL";
						$upd["doc_no"] = $refno;
						$upd["seq"] = sprintf("%d",$sequence);
						$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["refno"] = $refno;
						$upd["refno2"] = $refno2;
						$upd["refno3"] = "";
						$upd["desp"] = $resultDebitAccType['account_name'];
						$upd["desp2"] = "";
						$upd["desp3"] = "";
						$upd["desp4"] = "";
						$upd["amount"] = $this->selling_price_currency_format($amount);
						$upd["debit"] = $this->selling_price_currency_format(0);
						$upd["credit"] = $this->selling_price_currency_format(abs($amount));
						$upd["fx_amount"] = $this->selling_price_currency_format($amount);
						$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
						$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
						$upd["fx_rate"] = 1;
						$upd["curr_code"] = $config["arms_currency"]["code"];
						$upd["taxcode"] = "";
						$upd["taxable"] = $this->selling_price_currency_format(0);
						$upd["fx_taxable"] = $this->selling_price_currency_format(0);
						$upd["link_seq"] = 0;
						$upd["billtype"] = "H";
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						$upd["batchno"] = "";
						$upd["projcode"] = "";
						$upd["deptcode"] = "";
						$upd["accmgr_id"] = "";
						$upd["cheque_no"] = "";
						my_fputcsv($fp, $upd);
						unset($upd);
						$sequence++;
					}		
				
				}
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
				unset($wcond);
				$wcond[] = "`type`=".ms("debit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`is_credit_notes`=".ms(0);
				$wcond[] = "`TotalAmount` > ".ms(0);

				$retDebitAccType = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
									tax_code, acc_type,tax_account_code, tax_account_name,
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
						
						if(strtolower($resultDebitAccType['acc_type'])!="deposit")
						{
							$amount = $resultDebitAccType['TotalAmount'];
							$upd["accno"] = $resultDebitAccType['account_code'];
							$upd["doc_type"] = "GL";
							$upd["doc_no"] = $refno;
							$upd["seq"] = sprintf("%d",$sequence);
							$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["refno"] = $refno;
							$upd["refno2"] = $refno2;
							$upd["refno3"] = "";
							$upd["desp"] = $resultDebitAccType['account_name'];
							$upd["desp2"] = "";
							$upd["desp3"] = "";
							$upd["desp4"] = "";
							$upd["amount"] = $this->selling_price_currency_format($amount);
							$upd["debit"] = $this->selling_price_currency_format($amount);
							$upd["credit"] = $this->selling_price_currency_format(0);
							$upd["fx_amount"] = $this->selling_price_currency_format($amount);
							$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
							$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
							$upd["fx_rate"] = 1;
							$upd["curr_code"] = $config["arms_currency"]["code"];
							$upd["taxcode"] = "";
							$upd["taxable"] = $this->selling_price_currency_format(0);
							$upd["fx_taxable"] = $this->selling_price_currency_format(0);
							$upd["link_seq"] = 0;
							$upd["billtype"] = "H";
							$upd["remark1"] = "";
							$upd["remark2"] = "";
							$upd["batchno"] = "";
							$upd["projcode"] = "";
							$upd["deptcode"] = "";
							$upd["accmgr_id"] = "";
							$upd["cheque_no"] = "";
							my_fputcsv($fp, $upd);
							unset($upd);
							$sequence++;
						}
						else{
							$amount = $resultDebitAccType['itemAmount'];
							$upd["accno"] = $resultDebitAccType['account_code'];
							$upd["doc_type"] = "GL";
							$upd["doc_no"] = $refno;
							$upd["seq"] = sprintf("%d",$sequence);
							$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["refno"] = $refno;
							$upd["refno2"] = $refno2;
							$upd["refno3"] = "";
							$upd["desp"] = $resultDebitAccType['account_name'];
							$upd["desp2"] = "";
							$upd["desp3"] = "";
							$upd["desp4"] = "";
							$upd["amount"] = $this->selling_price_currency_format($amount);
							$upd["debit"] = $this->selling_price_currency_format($amount);
							$upd["credit"] = $this->selling_price_currency_format(0);
							$upd["fx_amount"] = $this->selling_price_currency_format($amount);
							$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
							$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
							$upd["fx_rate"] = 1;
							$upd["curr_code"] = $config["arms_currency"]["code"];
							$upd["taxcode"] = "";
							$upd["taxable"] = $this->selling_price_currency_format(0);
							$upd["fx_taxable"] = $this->selling_price_currency_format(0);
							$upd["link_seq"] = 0;
							$upd["billtype"] = "H";
							$upd["remark1"] = "";
							$upd["remark2"] = "";
							$upd["batchno"] = "";
							$upd["projcode"] = "";
							$upd["deptcode"] = "";
							$upd["accmgr_id"] = "";
							$upd["cheque_no"] = "";
							my_fputcsv($fp, $upd);
							unset($upd);
							$sequence++;
							$amount = $resultDebitAccType['TaxAmount'];
							$upd["accno"] = $resultDebitAccType['tax_account_code'];
							$upd["doc_type"] = "GL";
							$upd["doc_no"] = $refno;
							$upd["seq"] = sprintf("%d",$sequence);
							$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["refno"] = $refno;
							$upd["refno2"] = $refno2;
							$upd["refno3"] = "";
							$upd["desp"] = $resultDebitAccType['tax_account_name'];
							$upd["desp2"] = "";
							$upd["desp3"] = "";
							$upd["desp4"] = "";
							$upd["amount"] = $this->selling_price_currency_format($amount);
							$upd["debit"] = $this->selling_price_currency_format($amount);
							$upd["credit"] = $this->selling_price_currency_format(0);
							$upd["fx_amount"] = $this->selling_price_currency_format($amount);
							$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
							$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
							$upd["fx_rate"] = 1;
							$upd["curr_code"] = $config["arms_currency"]["code"];
							$upd["taxcode"] = "";
							$upd["taxable"] = $this->selling_price_currency_format(0);
							$upd["fx_taxable"] = $this->selling_price_currency_format(0);
							$upd["link_seq"] = 0;
							$upd["billtype"] = "H";
							$upd["remark1"] = "";
							$upd["remark2"] = "";
							$upd["batchno"] = "";
							$upd["projcode"] = "";
							$upd["deptcode"] = "";
							$upd["accmgr_id"] = "";
							$upd["cheque_no"] = "";
							my_fputcsv($fp, $upd);
							unset($upd);
							$sequence++;
						}
					}		
				}
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
				unset($wcond);
				
				$wcond[] = "`type`=".ms("credit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`is_credit_notes`=".ms(0);
				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name,
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
						$amount = $resultDetail['ItemAmount'];
						$taxAmt = $resultDetail['TaxAmount'];
						//Sales
						$upd["accno"] = $resultDetail['account_code'];
						$upd["doc_type"] = "GL";
						$upd["doc_no"] = $refno;
						$upd["seq"] = sprintf("%d",$sequence);
						$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["refno"] = $refno;
						$upd["refno2"] = $refno2;
						$upd["refno3"] = "";
						$upd["desp"] = $resultDetail['account_name'];
						$upd["desp2"] = "";
						$upd["desp3"] = "";
						$upd["desp4"] = "";
						$upd["amount"] = $this->selling_price_currency_format(0-$amount);
						$upd["debit"] = $this->selling_price_currency_format(0);
						$upd["credit"] = $this->selling_price_currency_format($amount);
						$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
						$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
						$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
						$upd["fx_rate"] = 1;
						$upd["curr_code"] = $config["arms_currency"]["code"];
						$upd["taxcode"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
						$upd["taxable"] = $this->selling_price_currency_format(0);
						$upd["fx_taxable"] = $this->selling_price_currency_format(0);
						$upd["link_seq"] = 0;
						$upd["billtype"] = "H";
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						$upd["batchno"] = "";
						$upd["projcode"] = "";
						$upd["deptcode"] = "";
						$upd["accmgr_id"] = "";
						$upd["cheque_no"] = "";
						my_fputcsv($fp, $upd);
						unset($upd);
						$sequence++;
						if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding' || strtolower($resultDetail['acc_type'])=='over')continue;
						//Input Tax
						$upd["accno"] = $resultDetail['tax_account_code'];
						$upd["doc_type"] = "GL";
						$upd["doc_no"] = $refno;
						$upd["seq"] = sprintf("%d",$sequence);
						$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$upd["refno"] = $refno;
						$upd["refno2"] = $refno2;
						$upd["refno3"] = "";
						$upd["desp"] = $resultDetail['tax_account_name'];
						$upd["desp2"] = "";
						$upd["desp3"] = "";
						$upd["desp4"] = "";
						$upd["amount"] = $this->selling_price_currency_format(0-$taxAmt);
						$upd["debit"] = $this->selling_price_currency_format(0);
						$upd["credit"] = $this->selling_price_currency_format($taxAmt);
						$upd["fx_amount"] = $this->selling_price_currency_format(0-$taxAmt);
						$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
						$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
						$upd["fx_rate"] = 1;
						$upd["curr_code"] = $config["arms_currency"]["code"];
						$upd["taxcode"] = $resultDetail['tax_code'];
						$upd["taxable"] = $this->selling_price_currency_format(0-$amount);
						$upd["fx_taxable"] = $this->selling_price_currency_format(0-$amount);
						$upd["link_seq"] = 0;
						$upd["billtype"] = "H";
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						$upd["batchno"] = "";
						$upd["projcode"] = "";
						$upd["deptcode"] = "";
						$upd["accmgr_id"] = "";
						$upd["cheque_no"] = "";
						my_fputcsv($fp, $upd);
						unset($upd);
						$sequence++;
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
				$sequence = 1;				
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
					$upd["accno"] = $resultDebitAccType['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDebitAccType['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($amount);
					$upd["debit"] = $this->selling_price_currency_format($amount);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($amount);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = "";
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
				}		
				$tmpSalesDb->sql_freeresult($retDebitAccType);	
										
				$wcond[] = "`type`=".ms("credit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`acc_type`!=".ms("sales return");
				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['ItemAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					//Sales
					$upd["accno"] = $resultDetail['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format(0-$amount);
					$upd["debit"] = $this->selling_price_currency_format(0);
					$upd["credit"] = $this->selling_price_currency_format($amount);
					$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
					if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
					//Input Tax
					$upd["accno"] = $resultDetail['tax_account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['tax_account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format(0-$taxAmt);
					$upd["debit"] = $this->selling_price_currency_format(0);
					$upd["credit"] = $this->selling_price_currency_format($taxAmt);
					$upd["fx_amount"] = $this->selling_price_currency_format(0-$taxAmt);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = $resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
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
				$sequence = 1;				
				
				$refno = $result['doc_no'];
				$refno2 = $result['return_receipt_no'];
				
				if(isset($this->accSettings['cash_refund']))
				{
					$account = $this->accSettings['cash_refund']['account'];
				}
				else
				{
					$account = $this->accSettings['cash']['account'];
				}
				$amount = $result['TotalAmount'];
				$upd["accno"] = $account['account_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $refno;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$upd["refno"] = $refno;
				$upd["refno2"] = $refno2;
				$upd["refno3"] = "";
				$upd["desp"] = $account['account_name'];
				$upd["desp2"] = "";
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format(0-$amount);
				$upd["debit"] = $this->selling_price_currency_format(0);
				$upd["credit"] = $this->selling_price_currency_format($amount);
				$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
				$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
				$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
				$upd["fx_rate"] = 1;
				$upd["curr_code"] = $config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = $this->selling_price_currency_format(0);
				$upd["fx_taxable"] = $this->selling_price_currency_format(0);
				$upd["link_seq"] = 0;
				$upd["billtype"] = "H";
				$upd["remark1"] = "";
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				unset($upd);
				$sequence++;
	
										
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`return_receipt_no`=".ms($result['return_receipt_no']);

				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable." 
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, return_receipt_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['ItemAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					//Sales
					$upd["accno"] = $resultDetail['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($amount);
					$upd["debit"] = $this->selling_price_currency_format($amount);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($amount);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
					if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
					//Input Tax
					$upd["accno"] = $resultDetail['tax_account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['tax_account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($taxAmt);
					$upd["debit"] = $this->selling_price_currency_format($taxAmt);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($taxAmt);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = $resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
				}
				unset($wcond);
			}
			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		global $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, array_keys($this->ExportFileHeader));
			
			//Export Credit Sales From DO
			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
													doc_no, ref_no, is_credit_notes,customer_code, customer_name,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable." where acc_type	!= ".ms('sales return')."
													group by pos_date, doc_no, is_credit_notes,customer_code, customer_name order by pos_date,doc_no");		
			
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$sequence = 1;		
				$refno = $result['doc_no'];
				$refno2 = $result['doc_no'];
				
				$amount = $result['TotalAmount'];
				$upd["accno"] = $result['customer_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $refno;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$upd["refno"] = $refno;
				$upd["refno2"] = $refno2;
				$upd["refno3"] = "";
				$upd["desp"] = $result['customer_name'];
				$upd["desp2"] = "";
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format($amount);
				$upd["debit"] = $this->selling_price_currency_format($amount);
				$upd["credit"] = $this->selling_price_currency_format(0);
				$upd["fx_amount"] = $this->selling_price_currency_format($amount);
				$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
				$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
				$upd["fx_rate"] = 1;
				$upd["curr_code"] = $config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = $this->selling_price_currency_format(0);
				$upd["fx_taxable"] = $this->selling_price_currency_format(0);
				$upd["link_seq"] = 0;
				$upd["billtype"] = "H";
				$upd["remark1"] = "";
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				unset($upd);
				$sequence++;
										
				$wcond[] = "`type`=".ms("credit");
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`acc_type`!=".ms("sales return");
				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['ItemAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					//Sales
					$upd["accno"] = $resultDetail['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format(0-$amount);
					$upd["debit"] = $this->selling_price_currency_format(0);
					$upd["credit"] = $this->selling_price_currency_format($amount);
					$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
					if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
					//Input Tax
					$upd["accno"] = $resultDetail['tax_account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['tax_account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format(0-$taxAmt);
					$upd["debit"] = $this->selling_price_currency_format(0);
					$upd["credit"] = $this->selling_price_currency_format($taxAmt);
					$upd["fx_amount"] = $this->selling_price_currency_format(0-$taxAmt);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = $resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0-$amount);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
				}
				unset($wcond);
			}
			
			//Export Sales DO's Credit Note
			$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
													doc_no, ref_no, return_receipt_no,customer_code,customer_name,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type`='debit'
													and tablename = ".ms("do_cn")."
													group by pos_date, doc_no, return_receipt_no order by pos_date,doc_no");
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$sequence = 1;				
				
				$refno = $result['doc_no'];
				$refno2 = $result['return_receipt_no'];
				
				if(isset($this->accSettings['cash_refund']))
				{
					$account = $this->accSettings['cash_refund']['account'];
				}
				else
				{
					$account = $this->accSettings['cash']['account'];
				}
				$amount = $result['TotalAmount'];
				$upd["accno"] = $result['customer_code'];
				$upd["doc_type"] = "GL";
				$upd["doc_no"] = $refno;
				$upd["seq"] = sprintf("%d",$sequence);
				$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
				$upd["refno"] = $refno;
				$upd["refno2"] = $refno2;
				$upd["refno3"] = "";
				$upd["desp"] = $result['customer_name'];
				$upd["desp2"] = "";
				$upd["desp3"] = "";
				$upd["desp4"] = "";
				$upd["amount"] = $this->selling_price_currency_format(0-$amount);
				$upd["debit"] = $this->selling_price_currency_format(0);
				$upd["credit"] = $this->selling_price_currency_format($amount);
				$upd["fx_amount"] = $this->selling_price_currency_format(0-$amount);
				$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
				$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
				$upd["fx_rate"] = 1;
				$upd["curr_code"] = $config["arms_currency"]["code"];
				$upd["taxcode"] = "";
				$upd["taxable"] = $this->selling_price_currency_format(0);
				$upd["fx_taxable"] = $this->selling_price_currency_format(0);
				$upd["link_seq"] = 0;
				$upd["billtype"] = "H";
				$upd["remark1"] = "";
				$upd["remark2"] = "";
				$upd["batchno"] = "";
				$upd["projcode"] = "";
				$upd["deptcode"] = "";
				$upd["accmgr_id"] = "";
				$upd["cheque_no"] = "";
				my_fputcsv($fp, $upd);
				unset($upd);
				$sequence++;
	
				$wcond[] = "`pos_date`=".ms($result['pos_date']);
				$wcond[] = "`doc_no`=".ms($result['doc_no']);
				$wcond[] = "`return_receipt_no`=".ms($result['return_receipt_no']);

				$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
									tax_code, tax_account_code, tax_account_name,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount
									from ".$this->tmpTable."
									where ".implode(" and ",$wcond)."
									group by pos_date, doc_no, return_receipt_no, account_code, account_name, tax_code");
				unset($wcond);
				while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
				{
					$amount = $resultDetail['ItemAmount'];
					$taxAmt = $resultDetail['TaxAmount'];
					//Sales
					$upd["accno"] = $resultDetail['account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($amount);
					$upd["debit"] = $this->selling_price_currency_format($amount);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($amount);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format(0);
					$upd["fx_taxable"] = $this->selling_price_currency_format(0);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
					if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
					//Input Tax
					$upd["accno"] = $resultDetail['tax_account_code'];
					$upd["doc_type"] = "GL";
					$upd["doc_no"] = $refno;
					$upd["seq"] = sprintf("%d",$sequence);
					$upd["doc_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
					$upd["refno"] = $refno;
					$upd["refno2"] = $refno2;
					$upd["refno3"] = "";
					$upd["desp"] = $resultDetail['tax_account_name'];
					$upd["desp2"] = "";
					$upd["desp3"] = "";
					$upd["desp4"] = "";
					$upd["amount"] = $this->selling_price_currency_format($taxAmt);
					$upd["debit"] = $this->selling_price_currency_format($taxAmt);
					$upd["credit"] = $this->selling_price_currency_format(0);
					$upd["fx_amount"] = $this->selling_price_currency_format($taxAmt);
					$upd["fx_debit"] = $this->selling_price_currency_format($upd["debit"]);
					$upd["fx_credit"] = $this->selling_price_currency_format($upd["credit"]);
					$upd["fx_rate"] = 1;
					$upd["curr_code"] = $config["arms_currency"]["code"];
					$upd["taxcode"] = $resultDetail['tax_code'];
					$upd["taxable"] = $this->selling_price_currency_format($amount);
					$upd["fx_taxable"] = $this->selling_price_currency_format($amount);
					$upd["link_seq"] = 0;
					$upd["billtype"] = "H";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					$upd["batchno"] = "";
					$upd["projcode"] = "";
					$upd["deptcode"] = "";
					$upd["accmgr_id"] = "";
					$upd["cheque_no"] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
					$sequence++;
				}
				unset($wcond);
			}
			fclose($fp);
		}
		return $total['total'];
	}

	
}
?>
