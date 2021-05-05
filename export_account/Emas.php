<?php
/*
2016-03-07 03:09pm Kee Kee
- Cash Sales cannot contain goods return amount

2016-06-24 3:30PM Kee Kee
- Fixed cannot export over/short figure into accounting software format

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php

2017-02-03 9:34 AM Qiu Ying
- Remove testing message in account payable
*/
include_once("ExportModule.php");
class Emas extends ExportModule
{
	const NAME="Emas";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array("Batchno"=>0,"Tranno"=>0,"Accno"=>0,"Ref1"=>0,"Ref2"=>0,
									"Date"=>0,"Desc1"=>0,"Desc2"=>0,"Debitamt"=>1,"Creditamt"=>1,
									"Fcamt"=>0,"Age"=>0,"Source"=>0,"Job"=>0,"Agent"=>0,
									"Paid"=>0,"Billtype"=>0,"For1"=>0,"For2"=>0,"Chequeno"=>0,
									"Knockoff"=>0,"Mark"=>0,"Fail"=>0,"User"=>0,"Drfcamt"=>1,
									"Crfcamt"=>1,"Fcrate"=>1,"Markpdc"=>0,"Dtpdc"=>0,"Taxdesc"=>0,
									"Taxamt"=>1);
	var $ExportFileName = array(
        "cs" => "%s/emas_cs_%s.txt",
		"ap" => "%s/emas_ap_%s.txt",
		"ar" => "%s/emas_ar_%s.txt",
		"cn" => "%s/emas_cn_%s.txt",
		"dn" => "%s/emas_dn_%s.txt",
    );
	var $ExportDateFormat = "d/m/Y";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite') {
		global $config;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>false,'cn'=>true);
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
			"settings" => array (
				'branch_code'=> array(
					"editable"=>false,
					"name"=>"Branch Code",
					"code"=>"MY"
				),
				'purchase' => array (
					"name"=>"Purchase",
					"account"=> array (
						'account_code' => '50010000',
						'account_name' => 'Purchase',
					)
				),
				'sales' => array (
					"name"=>"Sales",
					"account"=> array (
						'account_code' => '50010000',
						'account_name' => 'Cash Sales',
					)
				),
				'credit_sales' => array (
					"name"=>"Credit Sales",
					"account"=> array (
						'account_code' => '50020000',
						'account_name' => 'Sales',
					)
				),
				'customer_code'=>array(
					"name"=>"Customer Code",
					"account"=>array(
						'account_code'=> '3000001',
						'account_name'=> 'Cash',
					),
					"help"=>"For POS Cash Sales only"
				),
				'cash' => array (
					"name"=>"Cash",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Cash In Hand',
					)
				),
				'credit_card' => array (
					"name"=>"Credit Card",
					"account"=>array(
						'account_code' => '30100000',
						'account_name' => 'Credit Card',
					)
				),
				'coupon' => array (
					"name"=>"Coupon",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Cash In Hand',
					)
				),
				'voucher' => array(
					"name"=>"Voucher",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Cash In Hand',
					)
				),
				'check' => array (
					"name"=>"Check",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Cash In Hand',
					)
				),
				'deposit' => array (
					"name"=>"Deposit",
					"account"=>array(
						'account_code' => '30100000',
						'account_name' => 'Cash In Hand',
					)
				),
				'rounding' => array (
					"name"=>"Rounding",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Rounding',
					),
				),
				'service_charge' => array (
					"name"=>"Service Charge",
					"account"=>array(
						'account_code' => '50010000',
						'account_name' => 'Cash Sales',
					)
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '30200000',
						'account_name' => 'Over',
					)
				),
				'SR' => array (
					"name"=>"SR @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '95005002',
						'account_name' => 'SR @6%',
					)
				),
				'ZRL' => array (
					"name"=>"ZRL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'ZRL @0%',
					)
				),
				'ZRE' => array (
					"name"=>"ZRE @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'ZRE @0%',
					)
				),
				'ES43' => array (
					"name"=> "ES43 @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'ES43 @0%',
					)
				),
				'DS' => array (
					"name"=>"DS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'DS @6%',
					)
				),
				'OS' => array (
					"name"=>"OS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'OS @0%',
					)
				),
				'ES' => array (
					"name"=>"ES @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '95005002',
						'account_name' => 'ES @0%',
					)
				),
				'RS' => array (
					"name"=>"RS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'RS @0%',
					),
				),
				'GS' =>  array (
					"name"=>"GS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'GS @0%',
					)
				),
				'AJS' => array (
					"name"=>"AJS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005002',
						'account_name' => 'AJS @6%',
					)
				),
				'AJP' => array (
					"name"=>"AJP @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '95005002',
						'account_name' => 'AJP @6%',
					)
				),
				'BL' => array (
					"name"=>"BL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'BL @0%',
					)
				),
				'EP' => array (
					"name"=>"EP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'EP @0%',
					)
				),
				'GP' => array (
					"name"=> "GP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'GP @0%',
					)
				),
				'IM' => array (
					"name"=>"IM @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'IM @6%',
					)
				),
				'IS' => array (
					"name"=>"IS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'IS @0%',
					)
				),
				'NR' => array (
					"name"=>"NR @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '95005001',
						'account_name' => 'NR @0%',
					)
				),
				'OP' => array (
					"name"=>"OP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'OP @0%',
					),
				),
				'TX' =>  array (
					"name"=>"TX @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'TX @6%',
					)
				),
				'TX-E43' => array (
					"name"=>"TX-E43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'TX-E43 @6%',
					)
				),
				'TX-N43' => array (
					"name"=>"TX-N43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'TX-N43 @6%',
					)
				),
				'TX-RE' => array (
					"name"=>"TX-RE @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'TX-RE @6%',
					)
				),
				'ZP' => array (
					"name"=>"ZP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '95005001',
						'account_name' => 'ZP @0%',
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
				if($this->sys=='lite')
					$this->tvExportCol = array_merge($this->ExportFileHeader,array("dummy"=>0));
				else
					$this->tvExportCol = $this->ExportFileHeader;
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

			switch(strtolower($groupBy))
			{
				case 'daily summary':
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, acc_type");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['pos_date'];
						$branch_id=$result['batchno'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();

						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id] = array();
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
											where `type`='credit' and has_credit_notes = 0 
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, account_code, account_name, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['pos_date'];
						$branch_id=$result['batchno'];
						
						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$branch_id])){
							$tmpSalesDetail[$posDate][$branch_id] = array();
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
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']] += $result['TaxAmount'];
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
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, account_code, account_name,
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
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['ref_no'] = $result['ref_no'];
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

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name,
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
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['ref_no'] = $result['ref_no'];
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
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] += $result['TaxAmount'];
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
					$knockoff = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail){
							//$batchNo = $this->create_batch_no(date("ymd",strtotime($posDate)),$bn);
							//$batchNo = sprintf("%03s%03s",$branch_id,$bn);
							$batchNo=$branch_id;
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$upd["batchno"] = "";
										$upd["tranno"] = "";
										$upd["accno"] = $acc_code;
										$upd["ref1"] = $batchNo;//$batchNo;
										$upd["ref2"] = "POS-".sprintf("%04s",$bn);
										$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["desc1"] =  $acc_name;
										$upd["desc2"] = "";
										$upd["debitamt"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["creditamt"] = "";
										$upd["fcamt"] = "";
										$upd["age"] = "";
										$upd["source"] = "";
										$upd["job"] = "";
										$upd["agent"] = "";
										$upd["paid"] = "";
										$upd["billtype"] = "IN";
										$upd["for1"] = "";
										$upd["for2"] = "";
										$upd["chequeno"] = "";
										$upd["knockoff"] = sprintf("%03d",$knockoff);
										$upd["mark"] = "";
										$upd["fail"] = "";
										$upd["user"] = "";
										$upd["drfcamt"] = "";
										$upd["crfcamt"] = "";
										$upd["fcrate "] = "";
										$upd["markpdc"] = "";
										$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["taxdesc"] = "STAX";
										$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['payment']['total_amount']);
										my_fputcsv($fp, $upd);
										unset($upd);
										$knockoff++;
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
												if($tax_name=='other') $tax_name="";

												$upd["batchno"] = "";
												$upd["tranno"] = "";
												$upd["accno"] = $acc_code;
												$upd["ref1"] = $batchNo;//$batchNo;
												$upd["ref2"] = "POS-".sprintf("%04s",$bn);
												$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["desc1"] =  $acc_name;
												$upd["desc2"] = $tax_name;
												$upd["debitamt"] = "";
												$upd["creditamt"] = $this->selling_price_currency_format($val);
												$upd["fcamt"] = "";
												$upd["age"] = "";
												$upd["source"] = "";
												$upd["job"] = "";
												$upd["agent"] = "";
												$upd["paid"] = "";
												$upd["billtype"] = "IN";
												$upd["for1"] = "";
												$upd["for2"] = "";
												$upd["chequeno"] = "";
												$upd["knockoff"] = sprintf("%03d",$knockoff);
												$upd["mark"] = "";
												$upd["fail"] = "";
												$upd["user"] = "";
												$upd["drfcamt"] = "";
												$upd["crfcamt"] = "";
												$upd["fcrate "] = "";
												$upd["markpdc"] = "";
												$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["taxdesc"] ="STAX";
												$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
												my_fputcsv($fp, $upd);
												unset($upd);
												$knockoff++;
											}
										}
										else{
											$upd["batchno"] = "";
											$upd["tranno"] = "";
											$upd["accno"] = $acc_code;
											$upd["ref1"] = $batchNo;//$batchNo;
											$upd["ref2"] = "POS-".sprintf("%04s",$bn);
											$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["desc1"] =  $acc_name;
											$upd["desc2"] = "";
											$upd["debitamt"] = "";
											$upd["creditamt"] = $this->selling_price_currency_format($value2);
											$upd["fcamt"] = "";
											$upd["age"] = "";
											$upd["source"] = "";
											$upd["job"] = "";
											$upd["agent"] = "";
											$upd["paid"] = "";
											$upd["billtype"] = "IN";
											$upd["for1"] = "";
											$upd["for2"] = "";
											$upd["chequeno"] = "";
											$upd["knockoff"] = sprintf("%03d",$knockoff);
											$upd["mark"] = "";
											$upd["fail"] = "";
											$upd["user"] = "";
											$upd["drfcamt"] = "";
											$upd["crfcamt"] = "";
											$upd["fcrate "] = "";
											$upd["markpdc"] = "";
											$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["taxdesc"] ="STAX";
											$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
											my_fputcsv($fp, $upd);
											unset($upd);
											$knockoff++;
										}
									}
								}
							}

							if(isset($salesDetail['do'])){
								foreach($salesDetail['do'] as $receipt_no=>$salesDetail_do)
								{
									$batchNo=$salesDetail_do['ref_no'];
									foreach($salesDetail_do['payment'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$val)
											{
												$upd["batchno"] = "";
												$upd["tranno"] = "";
												$upd["accno"] = $acc_code;
												$upd["ref1"] = $batchNo;//$batchNo;
												$upd["ref2"] = $receipt_no;
												$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["desc1"] =  $acc_name;
												$upd["desc2"] = "";
												$upd["debitamt"] = $this->selling_price_currency_format($val['debitamt']);
												$upd["creditamt"] = "";
												$upd["fcamt"] = "";
												$upd["age"] = "";
												$upd["source"] = "";
												$upd["job"] = "";
												$upd["agent"] = "";
												$upd["paid"] = "";
												$upd["billtype"] = "IN";
												$upd["for1"] = "";
												$upd["for2"] = "";
												$upd["chequeno"] = "";
												$upd["knockoff"] = sprintf("%03d",$knockoff);
												$upd["mark"] = "";
												$upd["fail"] = "";
												$upd["user"] = "";
												$upd["drfcamt"] = "";
												$upd["crfcamt"] = "";
												$upd["fcrate "] = "";
												$upd["markpdc"] = "";
												$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["taxdesc"] = "STAX";
												$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['payment']['total_amount']);
												my_fputcsv($fp, $upd);
												unset($upd);
												$knockoff++;
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
														if($tax_name=='other') $tax_name="";
														$upd["batchno"] = "";
														$upd["tranno"] = "";
														$upd["accno"] = $acc_code;
														$upd["ref1"] = $batchNo;//$batchNo;
														$upd["ref2"] = $receipt_no;
														$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["desc1"] =  $acc_name;
														$upd["desc2"] = $tax_name;
														$upd["debitamt"] = "";
														$upd["creditamt"] = $this->selling_price_currency_format($val);
														$upd["fcamt"] = "";
														$upd["age"] = "";
														$upd["source"] = "";
														$upd["job"] = "";
														$upd["agent"] = "";
														$upd["paid"] = "";
														$upd["billtype"] = "IN";
														$upd["for1"] = "";
														$upd["for2"] = "";
														$upd["chequeno"] = "";
														$upd["knockoff"] = sprintf("%03d",$knockoff);
														$upd["mark"] = "";
														$upd["fail"] = "";
														$upd["user"] = "";
														$upd["drfcamt"] = "";
														$upd["crfcamt"] = "";
														$upd["fcrate "] = "";
														$upd["markpdc"] = "";
														$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["taxdesc"] ="STAX";
														$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['sales']['total_amount']);
														my_fputcsv($fp, $upd);
														unset($upd);
														$knockoff++;
													}
												}
												else{
													$upd["batchno"] = "";
													$upd["tranno"] = "";
													$upd["accno"] = $acc_code;
													$upd["ref1"] = $batchNo;//$batchNo;
													$upd["ref2"] = $receipt_no;
													$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["desc1"] =  $acc_name;
													$upd["desc2"] = "";
													$upd["debitamt"] = "";
													$upd["creditamt"] = $this->selling_price_currency_format($value2);
													$upd["fcamt"] = "";
													$upd["age"] = "";
													$upd["source"] = "";
													$upd["job"] = "";
													$upd["agent"] = "";
													$upd["paid"] = "";
													$upd["billtype"] = "IN";
													$upd["for1"] = "";
													$upd["for2"] = "";
													$upd["chequeno"] = "";
													$upd["knockoff"] = sprintf("%03d",$knockoff);
													$upd["mark"] = "";
													$upd["fail"] = "";
													$upd["user"] = "";
													$upd["drfcamt"] = "";
													$upd["crfcamt"] = "";
													$upd["fcrate "] = "";
													$upd["markpdc"] = "";
													$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["taxdesc"] ="STAX";
													$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['sales']['total_amount']);
													my_fputcsv($fp, $upd);
													unset($upd);
													$knockoff++;
												}
											}
										}
									}
								}
							}
							$bn++;
						}
					}

					unset($tmpSalesDetail, $bn ,$knockoff);
				break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, acc_type");

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
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']] += $result['TaxAmount'];
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
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, account_code, account_name,
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
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['ref_no'] = $result['ref_no'];
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

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name,
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
								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['ref_no'] = $result['ref_no'];
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
									$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] = 0;
								}

								$tmpSalesDetail[$posDate][$branch_id]['do'][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] += $result['TaxAmount'];
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
					$knockoff = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail){
							$date =  $this->monthly_summary_date_checking($posDate,$dateTo);
							//$batchNo = $this->create_batch_no(date("ym",strtotime($posDate)),$bn);
							//$batchNo = sprintf("%03s%03s",$branch_id,$bn);
							$batchNo = $branch_id;
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$upd["batchno"] = "";
										$upd["tranno"] = "";
										$upd["accno"] = $acc_code;
										$upd["ref1"] = $batchNo;//$batchNo;
										$upd["ref2"] = "POS-".sprintf("%04s",$bn);
										$upd["date"] = $this->set_date($this->ExportDateFormat,$date);
										$upd["desc1"] =  $acc_name;
										$upd["desc2"] = "";
										$upd["debitamt"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["creditamt"] = "";
										$upd["fcamt"] = "";
										$upd["age"] = "";
										$upd["source"] = "";
										$upd["job"] = "";
										$upd["agent"] = "";
										$upd["paid"] = "";
										$upd["billtype"] = "IN";
										$upd["for1"] = "";
										$upd["for2"] = "";
										$upd["chequeno"] = "";
										$upd["knockoff"] = sprintf("%03d",$knockoff);
										$upd["mark"] = "";
										$upd["fail"] = "";
										$upd["user"] = "";
										$upd["drfcamt"] = "";
										$upd["crfcamt"] = "";
										$upd["fcrate "] = "";
										$upd["markpdc"] = "";
										$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$date);
										$upd["taxdesc"] = "STAX";
										$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['payment']['total_amount']);
										my_fputcsv($fp, $upd);
										unset($upd);
										$knockoff++;
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
												if($tax_name=='other') $tax_name="";

												$upd["batchno"] = "";
												$upd["tranno"] = "";
												$upd["accno"] = $acc_code;
												$upd["ref1"] = $batchNo;//$batchNo;
												$upd["ref2"] = "POS-".sprintf("%04s",$bn);
												$upd["date"] = $this->set_date($this->ExportDateFormat,$date);
												$upd["desc1"] =  $acc_name;
												$upd["desc2"] = $tax_name;
												$upd["debitamt"] = "";
												$upd["creditamt"] = $this->selling_price_currency_format($val);
												$upd["fcamt"] = "";
												$upd["age"] = "";
												$upd["source"] = "";
												$upd["job"] = "";
												$upd["agent"] = "";
												$upd["paid"] = "";
												$upd["billtype"] = "IN";
												$upd["for1"] = "";
												$upd["for2"] = "";
												$upd["chequeno"] = "";
												$upd["knockoff"] = sprintf("%03d",$knockoff);
												$upd["mark"] = "";
												$upd["fail"] = "";
												$upd["user"] = "";
												$upd["drfcamt"] = "";
												$upd["crfcamt"] = "";
												$upd["fcrate "] = "";
												$upd["markpdc"] = "";
												$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$date);
												$upd["taxdesc"] ="STAX";
												$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
												my_fputcsv($fp, $upd);
												unset($upd);
												$knockoff++;
											}
										}
										else{
											$upd["batchno"] = "";
											$upd["tranno"] = "";
											$upd["accno"] = $acc_code;
											$upd["ref1"] = $batchNo;//$batchNo;
											$upd["ref2"] = "POS-".sprintf("%04s",$bn);
											$upd["date"] = $this->set_date($this->ExportDateFormat,$date);
											$upd["desc1"] =  $acc_name;
											$upd["desc2"] = "";
											$upd["debitamt"] = "";
											$upd["creditamt"] = $this->selling_price_currency_format($value2);
											$upd["fcamt"] = "";
											$upd["age"] = "";
											$upd["source"] = "";
											$upd["job"] = "";
											$upd["agent"] = "";
											$upd["paid"] = "";
											$upd["billtype"] = "IN";
											$upd["for1"] = "";
											$upd["for2"] = "";
											$upd["chequeno"] = "";
											$upd["knockoff"] = sprintf("%03d",$knockoff);
											$upd["mark"] = "";
											$upd["fail"] = "";
											$upd["user"] = "";
											$upd["drfcamt"] = "";
											$upd["crfcamt"] = "";
											$upd["fcrate "] = "";
											$upd["markpdc"] = "";
											$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$date);
											$upd["taxdesc"] ="STAX";
											$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
											my_fputcsv($fp, $upd);
											unset($upd);
											$knockoff++;
										}
									}
								}
							}

							if(isset($salesDetail['do'])){
								foreach($salesDetail['do'] as $receipt_no=>$salesDetail_do)
								{
									$batchNo=$salesDetail_do['ref_no'];
									foreach($salesDetail_do['payment'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$val)
											{
												$upd["batchno"] = "";
												$upd["tranno"] = "";
												$upd["accno"] = $acc_code;
												$upd["ref1"] = $batchNo;//$batchNo;
												$upd["ref2"] = $receipt_no;
												$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["desc1"] =  $acc_name;
												$upd["desc2"] = "";
												$upd["debitamt"] = $this->selling_price_currency_format($val['debitamt']);
												$upd["creditamt"] = "";
												$upd["fcamt"] = "";
												$upd["age"] = "";
												$upd["source"] = "";
												$upd["job"] = "";
												$upd["agent"] = "";
												$upd["paid"] = "";
												$upd["billtype"] = "IN";
												$upd["for1"] = "";
												$upd["for2"] = "";
												$upd["chequeno"] = "";
												$upd["knockoff"] = sprintf("%03d",$knockoff);
												$upd["mark"] = "";
												$upd["fail"] = "";
												$upd["user"] = "";
												$upd["drfcamt"] = "";
												$upd["crfcamt"] = "";
												$upd["fcrate "] = "";
												$upd["markpdc"] = "";
												$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["taxdesc"] = "STAX";
												$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['payment']['total_amount']);
												my_fputcsv($fp, $upd);
												unset($upd);
												$knockoff++;
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
														if($tax_name=='other') $tax_name="";
														$upd["batchno"] = "";
														$upd["tranno"] = "";
														$upd["accno"] = $acc_code;
														$upd["ref1"] = $batchNo;//$batchNo;
														$upd["ref2"] = $receipt_no;
														$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["desc1"] =  $acc_name;
														$upd["desc2"] = $tax_name;
														$upd["debitamt"] = "";
														$upd["creditamt"] = $this->selling_price_currency_format($val);
														$upd["fcamt"] = "";
														$upd["age"] = "";
														$upd["source"] = "";
														$upd["job"] = "";
														$upd["agent"] = "";
														$upd["paid"] = "";
														$upd["billtype"] = "IN";
														$upd["for1"] = "";
														$upd["for2"] = "";
														$upd["chequeno"] = "";
														$upd["knockoff"] = sprintf("%03d",$knockoff);
														$upd["mark"] = "";
														$upd["fail"] = "";
														$upd["user"] = "";
														$upd["drfcamt"] = "";
														$upd["crfcamt"] = "";
														$upd["fcrate "] = "";
														$upd["markpdc"] = "";
														$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["taxdesc"] ="STAX";
														$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['sales']['total_amount']);
														my_fputcsv($fp, $upd);
														unset($upd);
														$knockoff++;
													}
												}
												else{
													$upd["batchno"] = "";
													$upd["tranno"] = "";
													$upd["accno"] = $acc_code;
													$upd["ref1"] = $batchNo;//$batchNo;
													$upd["ref2"] = $receipt_no;
													$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["desc1"] =  $acc_name;
													$upd["desc2"] = "";
													$upd["debitamt"] = "";
													$upd["creditamt"] = $this->selling_price_currency_format($value2);
													$upd["fcamt"] = "";
													$upd["age"] = "";
													$upd["source"] = "";
													$upd["job"] = "";
													$upd["agent"] = "";
													$upd["paid"] = "";
													$upd["billtype"] = "IN";
													$upd["for1"] = "";
													$upd["for2"] = "";
													$upd["chequeno"] = "";
													$upd["knockoff"] = sprintf("%03d",$knockoff);
													$upd["mark"] = "";
													$upd["fail"] = "";
													$upd["user"] = "";
													$upd["drfcamt"] = "";
													$upd["crfcamt"] = "";
													$upd["fcrate "] = "";
													$upd["markpdc"] = "";
													$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["taxdesc"] ="STAX";
													$upd["taxamt"] = $this->selling_price_currency_format($salesDetail_do['sales']['total_amount']);
													my_fputcsv($fp, $upd);
													unset($upd);
													$knockoff++;
												}
											}
										}
									}
								}
							}
						}
						$bn++;
					}

					unset($tmpSalesDetail, $bn, $knockoff);
				break;
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id, doc_no, ref_no, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit'
											group by tablename, ref_no, acc_type
											order by pos_date, ref_no");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						if($result['TotalAmount']==0) continue;
						$posDate=$result['pos_date'];
						$receipt_no=$result['ref_no'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$receipt_no])){
							$tmpSalesDetail[$posDate][$receipt_no] = array();
							$tmpSalesDetail[$posDate][$receipt_no]['tablename'] = $result['tablename'];
							$tmpSalesDetail[$posDate][$receipt_no]['batchno'] = $result['batchno'];
							$tmpSalesDetail[$posDate][$receipt_no]['counter_id'] = $result['counter_id'];
							$tmpSalesDetail[$posDate][$receipt_no]['doc_no'] = $result['doc_no'];
							$tmpSalesDetail[$posDate][$receipt_no]['ref_no'] = $result['ref_no'];
							$tmpSalesDetail[$posDate][$receipt_no]['payment']['total_amount'] = 0;
							$tmpSalesDetail[$posDate][$receipt_no]['sales']['total_amount'] = 0;
						}

						if(!isset($tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']])){
							$tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] = 0;
						}

						$tmpSalesDetail[$posDate][$receipt_no]['payment']['total_amount'] += $result['TotalAmount'];
						$tmpSalesDetail[$posDate][$receipt_no]['payment'][$result['account_code']][$result['account_name']]['debitamt'] += $result['TotalAmount'];
					}
					unset($result, $posDate);
					$tmpSalesDb->sql_freeresult($ret);

					$ret = $this->sql_query($tmpSalesDb, "select batchno, counter_id, pos_date, doc_no, ref_no, acc_type, account_code, account_name,
											tax_code, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and has_credit_notes = 0 
											group by tablename, ref_no, account_code, account_name, tax_code
											order by pos_date");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
						$posDate=$result['pos_date'];
						$receipt_no=$result['ref_no'];

						if(!isset($tmpSalesDetail[$posDate])){
							$tmpSalesDetail[$posDate] = array();
						}

						if(!isset($tmpSalesDetail[$posDate][$receipt_no])){
							$tmpSalesDetail[$posDate][$receipt_no] = array();
							$tmpSalesDetail[$posDate][$receipt_no]['tablename'] = $result['tablename'];
							$tmpSalesDetail[$posDate][$receipt_no]['batchno'] = $result['batchno'];
							$tmpSalesDetail[$posDate][$receipt_no]['counter_id'] = $result['counter_id'];
							$tmpSalesDetail[$posDate][$receipt_no]['doc_no'] = $result['doc_no'];
							$tmpSalesDetail[$posDate][$receipt_no]['ref_no'] = $result['ref_no'];
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
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] = 0;
							}

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']] += $result['TaxAmount'];
						}
						else{
							if(!isset($tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']]))
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']] = 0;

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['account_code']][$result['account_name']] += $result['ItemAmount'];
						}
					}
					unset($result, $posDate);
					$tmpSalesDb->sql_freeresult($ret);

					$knockoff = 1;
					foreach($tmpSalesDetail as $posDate=>$salesDetails)
					{
						foreach($salesDetails as $receipt_no=>$salesDetail)
						{
							if($salesDetail['tablename']=='do'){
								$ref1 = $salesDetail['ref_no'];//$batchNo;
								$ref2 = $salesDetail['doc_no'];//$batchNo;
							}
							else{
								//$ref1 = ($this->sys=='pos')?sprintf("%03s%03s",$salesDetail['branch_id'],$salesDetail['counter_id']):$salesDetail['doc_no'];
								$ref1 = $salesDetail['batchno'];
								$ref2 = $salesDetail['doc_no'];//$batchNo;
							}

							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$upd["batchno"] = "";
										$upd["tranno"] = "";
										$upd["accno"] = $acc_code;
										$upd["ref1"] = $ref1;
										$upd["ref2"] = $ref2;
										$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["desc1"] =  $acc_name;
										$upd["desc2"] = "";
										$upd["debitamt"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["creditamt"] = "";
										$upd["fcamt"] = "";
										$upd["age"] = "";
										$upd["source"] = "";
										$upd["job"] = "";
										$upd["agent"] = "";
										$upd["paid"] = "";
										$upd["billtype"] = "IN";
										$upd["for1"] = "";
										$upd["for2"] = "";
										$upd["chequeno"] = "";
										$upd["knockoff"] = sprintf("%03d",$knockoff);
										$upd["mark"] = "";
										$upd["fail"] = "";
										$upd["user"] = "";
										$upd["drfcamt"] = "";
										$upd["crfcamt"] = "";
										$upd["fcrate "] = "";
										$upd["markpdc"] = "";
										$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["taxdesc"] = "STAX";
										$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['payment']['total_amount']);
										my_fputcsv($fp, $upd);
										unset($upd);
										$knockoff++;
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
												if($tax_name=='other') $tax_name="";
												$upd["batchno"] = "";
												$upd["tranno"] = "";
												$upd["accno"] = $acc_code;
												$upd["ref1"] = $ref1;
												$upd["ref2"] = $ref2;
												$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["desc1"] =  $acc_name;
												$upd["desc2"] = $tax_name;
												$upd["debitamt"] = "";
												$upd["creditamt"] = $this->selling_price_currency_format($val);
												$upd["fcamt"] = "";
												$upd["age"] = "";
												$upd["source"] = "";
												$upd["job"] = "";
												$upd["agent"] = "";
												$upd["paid"] = "";
												$upd["billtype"] = "IN";
												$upd["for1"] = "";
												$upd["for2"] = "";
												$upd["chequeno"] = "";
												$upd["knockoff"] = sprintf("%03d",$knockoff);
												$upd["mark"] = "";
												$upd["fail"] = "";
												$upd["user"] = "";
												$upd["drfcamt"] = "";
												$upd["crfcamt"] = "";
												$upd["fcrate "] = "";
												$upd["markpdc"] = "";
												$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["taxdesc"] ="STAX";
												$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
												my_fputcsv($fp, $upd);
												unset($upd);
												$knockoff++;
											}
										}
										else{
											$upd["batchno"] = "";
											$upd["tranno"] = "";
											$upd["accno"] = $acc_code;
											$upd["ref1"] = $ref1;
											$upd["ref2"] = $ref2;
											$upd["date"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["desc1"] =  $acc_name;
											$upd["desc2"] = "";
											$upd["debitamt"] = "";
											$upd["creditamt"] = $this->selling_price_currency_format($value2);
											$upd["fcamt"] = "";
											$upd["age"] = "";
											$upd["source"] = "";
											$upd["job"] = "";
											$upd["agent"] = "";
											$upd["paid"] = "";
											$upd["billtype"] = "IN";
											$upd["for1"] = "";
											$upd["for2"] = "";
											$upd["chequeno"] = "";
											$upd["knockoff"] = sprintf("%03d",$knockoff);
											$upd["mark"] = "";
											$upd["fail"] = "";
											$upd["user"] = "";
											$upd["drfcamt"] = "";
											$upd["crfcamt"] = "";
											$upd["fcrate "] = "";
											$upd["markpdc"] = "";
											$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["taxdesc"] ="STAX";
											$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['total_amount']);
											my_fputcsv($fp, $upd);
											unset($upd);
											$knockoff++;
										}
									}
								}
							}
						}
					}
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

			$sql="select batchno, gl_code, vendor_terms, vendor_code, vendor_name, inv_no, inv_date,
			round(sum(ItemAmount),2) as ItemAmount,
			round(sum(TaxAmount),2) as TaxAmount,
			round(sum(TotalAmount),2) as TotalAmount
			from ".$this->tmpTable."
			group by inv_date, inv_no
			order by inv_date";

			$ret = $this->sql_query($tmpSalesDb, $sql);

			$knockoff = 1;
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['batchno'];
				$InvDate = $this->set_date($this->ExportDateFormat,$result['inv_date']);

				$sql="select round(sum(TaxAmount),2) as TaxAmount, taxCode, tax_account_code, tax_account_name
				from ".$this->tmpTable."
				where inv_date = ".ms($result['inv_date'])."
				and inv_no = ".ms($result['inv_no'])."
				and vendor_code = ".ms($result['vendor_code'])."
				group by tax_account_code, tax_account_name";

				$ret2 = $this->sql_query($tmpSalesDb, $sql);
				if($tmpSalesDb->sql_numrows($ret2)>0){
					$taxdesc = "PTAX";
					while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret2)){
						if(!isset($inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']])){
							$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['TaxAmount'] = 0;
						}
						$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['TaxAmount']+=$result2['TaxAmount'];
					}
				}else{
					$taxdesc = "";
				}
				$tmpSalesDb->sql_freeresult($ret2);
				$upd["batchno"] = "";
				$upd["tranno"] = "";
				$upd["accno"] = $result['gl_code'];
				$upd["ref1"] = $batchNo;
				$upd["ref2"] = $result['inv_no'];
				$upd["date"] = $InvDate;
				$upd["desc1"] = "PURCHASE";
				$upd["desc2"] = "";
				$upd["debitamt"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["creditamt"] = "";
				$upd["fcamt"] = "";
				$upd["age"] = "";
				$upd["source"] = "";
				$upd["job"] = "";
				$upd["agent"] = "";
				$upd["paid"] = "";
				$upd["billtype"] = "RV";
				$upd["for1"] = "";
				$upd["for2"] = "";
				$upd["chequeno"] = "";
				$upd["knockoff"] = $knockoff;
				$upd["mark"] = "";
				$upd["fail"] = "";
				$upd["user"] = "";
				$upd["drfcamt"] = "";
				$upd["crfcamt"] = "";
				$upd["fcrate"] = "";
				$upd["markpdc"] = "";
				$upd["dtpdc"] = $InvDate;
				$upd["taxdesc"] = $taxdesc;
				$upd["taxamt"] = $this->selling_price_currency_format($result['TotalAmount']);
				my_fputcsv($fp, $upd);
				$knockoff++;
				if(isset($inputTaxValue))
				{
					foreach($inputTaxValue as $taxAccCode=>$val)
					{
						foreach($val as $taxAccName=>$val2)
						{
							$upd["batchno"] = "";
							$upd["tranno"] = "";
							$upd["accno"] = $taxAccCode;
							$upd["ref1"] = $batchNo;
							$upd["ref2"] = $result['inv_no'];
							$upd["date"] = $InvDate;
							$upd["desc1"] = $taxAccName;
							$upd["desc2"] = "";
							$upd["debitamt"] = $this->selling_price_currency_format($val2['TaxAmount']);
							$upd["creditamt"] = "";
							$upd["fcamt"] = "";
							$upd["age"] = "";
							$upd["source"] = "";
							$upd["job"] = "";
							$upd["agent"] = "";
							$upd["paid"] = "";
							$upd["billtype"] = "IN";
							$upd["for1"] = "";
							$upd["for2"] = "";
							$upd["chequeno"] = "";
							$upd["knockoff"] = $knockoff;
							$upd["mark"] = "";
							$upd["fail"] = "";
							$upd["user"] = "";
							$upd["drfcamt"] = "";
							$upd["crfcamt"] = "";
							$upd["fcrate"] = "";
							$upd["markpdc"] = "";
							$upd["dtpdc"] = $InvDate;
							$upd["taxdesc"] = $taxdesc;
							$upd["taxamt"] = $this->selling_price_currency_format($result['TotalAmount']);
							my_fputcsv($fp, $upd);
							$knockoff++;
						}
					}
					unset($inputTaxValue);
				}
				$upd["batchno"] = "";
				$upd["tranno"] = "";
				$upd["accno"] = $result['vendor_code'];
				$upd["ref1"] = $batchNo;
				$upd["ref2"] = $result['inv_no'];
				$upd["date"] = $InvDate;
				$upd["desc1"] = $result['vendor_name'];
				$upd["desc2"] = "";
				$upd["debitamt"] = "";
				$upd["creditamt"] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd["fcamt"] = "";
				$upd["age"] = "";
				$upd["source"] = "";
				$upd["job"] = "";
				$upd["agent"] = "";
				$upd["paid"] = "";
				$upd["billtype"] = "IN";
				$upd["for1"] = "";
				$upd["for2"] = "";
				$upd["chequeno"] = "";
				$upd["knockoff"] = $knockoff;
				$upd["mark"] = "";
				$upd["fail"] = "";
				$upd["user"] = "";
				$upd["drfcamt"] = "";
				$upd["crfcamt"] = "";
				$upd["fcrate"] = "";
				$upd["markpdc"] = "";
				$upd["dtpdc"] = $InvDate;
				$upd["taxdesc"] = $taxdesc;
				$upd["taxamt"] = $this->selling_price_currency_format($result['TotalAmount']);
				my_fputcsv($fp, $upd);
				$knockoff++;
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
			if(file_exists($this->tmpReceiverFile)) unlink($this->tmpReceiverFile);
			$fp = fopen($this->tmpReceiverFile, 'w');

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

			while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
              $invNo=$result['inv_no'];
              if(!isset($tmpSalesDetail[$invNo])){
                  $tmpSalesDetail[$invNo]=array();
				  $tmpSalesDetail[$invNo]['batchno']=$result['batchno'];
                  $tmpSalesDetail[$invNo]['do_date']=$result['do_date'];
                  $tmpSalesDetail[$invNo]['currencyrate']=$result['currencyrate'];
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

              if($result['tax_code']){
                  if(!isset($tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
                      $tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] = 0;
                      $tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] = 0;
                  }

                  $tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] += $result['ItemAmount'];
                  $tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] += $result['ItemFAmount'];

                  if(!isset($tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
                      $tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
                      $tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] = 0;
                      $tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] = 0;
                  }

                  $tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] += $result['TaxAmount'];
                  $tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] += $result['TaxFAmount'];
              }
              else{
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
			$knockoff = 1;
			foreach($tmpSalesDetail as $invNo=>$salesDetail)
			{
				$doDate=$salesDetail['do_date'];
				//$batchNo = $this->create_batch_no(date("ymd",strtotime($doDate)),$bn);
				$batchNo = $salesDetail['batchno'];

				if(isset($salesDetail['payment'])){
					$upd["batchno"] = "";
					$upd["tranno"] = "";
					$upd["accno"] = $salesDetail['payment']['account_code'];;
					$upd["ref1"] = $invNo;//$batchNo;
					$upd["ref2"] = $batchNo;
					$upd["date"] = $this->set_date($this->ExportDateFormat,$doDate);
					$upd["desc1"] =  $salesDetail['payment']['account_name'];
					$upd["desc2"] = "";
					$upd["debitamt"] = $this->selling_price_currency_format($salesDetail['payment']['debitamt']);
					$upd["creditamt"] = "";
					$upd["fcamt"] = "";
					$upd["age"] = "";
					$upd["source"] = "";
					$upd["job"] = "";
					$upd["agent"] = "";
					$upd["paid"] = "";
					$upd["billtype"] = "IN";
					$upd["for1"] = "";
					$upd["for2"] = "";
					$upd["chequeno"] = "";
					$upd["knockoff"] = sprintf("%03d",$knockoff);
					$upd["mark"] = "";
					$upd["fail"] = "";
					$upd["user"] = "";
					$upd["drfcamt"] = $this->selling_price_currency_format(($salesDetail['payment']['debitfamt']>0)?$salesDetail['payment']['debitfamt']:$salesDetail['payment']['debitamt']);
					$upd["crfcamt"] = "";
					$upd["fcrate "] = $salesDetail['currencyrate'];
					$upd["markpdc"] = "";
					$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$doDate);
					$upd["taxdesc"] = "STAX";
					$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['payment']['debitamt']);
					my_fputcsv($fp, $upd);
					unset($upd);
					$knockoff++;
				}

				foreach($salesDetail['sales'] as $acc_code=>$value)
				{
					if(is_array($value))
					{
						foreach($value as $acc_name=>$value2)
						{
                          foreach($value2 as $tax_name=>$val){
                            if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

							$upd["batchno"] = "";
							$upd["tranno"] = "";
							$upd["accno"] = $acc_code;
							$upd["ref1"] = $invNo;//$batchNo;
							$upd["ref2"] = $batchNo;
							$upd["date"] = $this->set_date($this->ExportDateFormat,$doDate);
							$upd["desc1"] = $acc_name;
							$upd["desc2"] = $tax_name;
							$upd["debitamt"] = "";
							$upd["creditamt"] = $this->selling_price_currency_format($val['creditamt']);
							$upd["fcamt"] = "";
							$upd["age"] = "";
							$upd["source"] = "";
							$upd["job"] = "";
							$upd["agent"] = "";
							$upd["paid"] = "";
							$upd["billtype"] = "IN";
							$upd["for1"] = "";
							$upd["for2"] = "";
							$upd["chequeno"] = "";
							$upd["knockoff"] = sprintf("%03d",$knockoff);
							$upd["mark"] = "";
							$upd["fail"] = "";
							$upd["user"] = "";
							$upd["drfcamt"] = "";
							$upd["crfcamt"] = $this->selling_price_currency_format(($val['creditfamt']>0)?$val['creditfamt']:$val['creditamt']);
							$upd["fcrate "] = $salesDetail['currencyrate'];;
							$upd["markpdc"] = "";
							$upd["dtpdc"] = $this->set_date($this->ExportDateFormat,$doDate);
							$upd["taxdesc"] ="STAX";
							$upd["taxamt"] = $this->selling_price_currency_format($salesDetail['sales']['creditamt']);
							my_fputcsv($fp, $upd);
							unset($upd);
							$knockoff++;
                          }
						}
					}
				}

				$bn++;
			}

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

			$ret = $this->sql_query($tmpSalesDb, "select batchno, return_receipt_no, credit_note_no, date, currencyrate,
															account_code, account_name,
											sum(TotalAmount) as TotalAmount,
											sum(TotalFAmount) as TotalFAmount
											from ".$this->tmpTable."
											group by date, return_receipt_no, credit_note_no
											order by date, return_receipt_no, credit_note_no");
			$bn=1;
			$knockoff = 1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$refno = $cn['credit_note_no'];
				$refno1 = $cn['batchno'];

				$upd["Batchno"] = "";
				$upd["Tranno"] = "";
				$upd["Accno"] = $cn['account_code'];
				$upd["Ref1"] = $refno;
				$upd["Ref2"] = $refno1;
				$upd["Date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
				$upd["Desc1"] = $cn['account_name'];
				$upd["Desc2"] = "";
				$upd["Debitamt"] = "";
				$upd["Creditamt"] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd["Fcamt"] = "";
				$upd["Age"] = "";
				$upd["Source"] = "";
				$upd["Job"] = "";
				$upd["Agent"] = "";
				$upd["Paid"] = "";
				$upd["Billtype"] = "CN";
				$upd["For1"] = "";
				$upd["For2"] = "";
				$upd["Chequeno"] = "";
				$upd["Knockoff"] = sprintf("%03d",$knockoff);
				$upd["Mark"] = "";
				$upd["Fail"] = "";
				$upd["User"] = "";
				$upd["Drfcamt"] = "";
				$upd["Crfcamt"] = "";
				$upd["Fcrate"] = $cn['currencyrate'];
				$upd["Markpdc"] = "";
				$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$cn['date']);
				$upd["Taxdesc"] = "STAX";
				$upd["Taxamt"] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				my_fputcsv($fp, $upd);
				unset($upd);
				$knockoff++;
				
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
					$upd["Batchno"] = "";
					$upd["Tranno"] = "";
					$upd["Accno"] = $cnTax['customer_code'];
					$upd["Ref1"] = $refno;
					$upd["Ref2"] = $refno1;
					$upd["Date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["Desc1"] = $cnTax['customer_name'];
					$upd["Desc2"] = $cnTax['tax_code'];
					$upd["Debitamt"] = $this->selling_price_currency_format(abs($cnTax['ItemAmount']));
					$upd["Creditamt"] = "";
					$upd["Fcamt"] = "";
					$upd["Age"] = "";
					$upd["Source"] = "";
					$upd["Job"] = "";
					$upd["Agent"] = "";
					$upd["Paid"] = "";
					$upd["Billtype"] = "CN";
					$upd["For1"] = "";
					$upd["For2"] = "";
					$upd["Chequeno"] = "";
					$upd["Knockoff"] = sprintf("%03d",$knockoff);
					$upd["Mark"] = "";
					$upd["Fail"] = "";
					$upd["User"] = "";
					$upd["Drfcamt"] = "";
					$upd["Crfcamt"] = "";
					$upd["Fcrate"] = $cn['currencyrate'];
					$upd["Markpdc"] = "";
					$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["Taxdesc"] = "STAX";
					$upd["Taxamt"] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
					my_fputcsv($fp, $upd);
					$knockoff++;
					unset($upd);
					
					$upd["Batchno"] = "";
					$upd["Tranno"] = "";
					$upd["Accno"] = $cnTax['tax_account_code'];
					$upd["Ref1"] = $refno;
					$upd["Ref2"] = $refno1;
					$upd["Date"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["Desc1"] = $cnTax['tax_account_name'];
					$upd["Desc2"] = "";
					$upd["Debitamt"] = $this->selling_price_currency_format(abs($cnTax['TaxAmount']));
					$upd["Creditamt"] = "";
					$upd["Fcamt"] = "";
					$upd["Age"] = "";
					$upd["Source"] = "";
					$upd["Job"] = "";
					$upd["Agent"] = "";
					$upd["Paid"] = "";
					$upd["Billtype"] = "CN";
					$upd["For1"] = "";
					$upd["For2"] = "";
					$upd["Chequeno"] = "";
					$upd["Knockoff"] = sprintf("%03d",$knockoff);
					$upd["Mark"] = "";
					$upd["Fail"] = "";
					$upd["User"] = "";
					$upd["Drfcamt"] = "";
					$upd["Crfcamt"] = "";
					$upd["Fcrate"] = $cn['currencyrate'];
					$upd["Markpdc"] = "";
					$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["Taxdesc"] = "STAX";
					$upd["Taxamt"] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
					my_fputcsv($fp, $upd);
					$knockoff++;
					unset($upd);
				}
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_debit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode){
		global $LANG;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0){
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');

			$ret = $this->sql_query($tmpSalesDb, "select batchno, date, invoice_no, customer_code, customer_name,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount
													from ".$this->tmpTable."
													where TotalAmount <> 0
													group by date, invoice_no
													order by date, invoice_no");

			$knockoff = 1;
			while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$refno = $dn['invoice_no'];
				$batchno = $dn['batchno'];

				$upd["Batchno"] = "";
				$upd["Tranno"] = "";
				$upd["Accno"] = $dn['customer_code'];
				$upd["Ref1"] = $refno;
				$upd["Ref2"] = $batchno;
				$upd["Date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["Desc1"] = $dn['customer_name'];
				$upd["Desc2"] = "";
				$upd["Debitamt"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["Creditamt"] = "";
				$upd["Fcamt"] = "";
				$upd["Age"] = "";
				$upd["Source"] = "";
				$upd["Job"] = "";
				$upd["Agent"] = "";
				$upd["Paid"] = "";
				$upd["Billtype"] = "DN";
				$upd["For1"] = "";
				$upd["For2"] = "";
				$upd["Chequeno"] = "";
				$upd["Knockoff"] = sprintf("%03d",$knockoff);
				$upd["Mark"] = "";
				$upd["Fail"] = "";
				$upd["User"] = "";
				$upd["Drfcamt"] = "";
				$upd["Crfcamt"] = "";
				$upd["Fcrate"] = "";
				$upd["Markpdc"] = "";
				$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["Taxdesc"] = "STAX";
				$upd["Taxamt"] = $this->selling_price_currency_format($dn['TotalAmount']);
				my_fputcsv($fp, $upd);
				$knockoff++;
				unset($upd);

				$cond = "where date = ".ms($dn['date'])."
						 and invoice_no = ".ms($dn['invoice_no']);
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
					$upd["Batchno"] = "";
					$upd["Tranno"] = "";
					$upd["Accno"] = $dnTax['account_code'];
					$upd["Ref1"] = $refno;
					$upd["Ref2"] = $batchno;
					$upd["Date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["Desc1"] = $dnTax['account_name'];
					$upd["Desc2"] = "";
					$upd["Debitamt"] = "";
					$upd["Creditamt"] = $this->selling_price_currency_format($dnTax['ItemAmount']);
					$upd["Fcamt"] = "";
					$upd["Age"] = "";
					$upd["Source"] = "";
					$upd["Job"] = "";
					$upd["Agent"] = "";
					$upd["Paid"] = "";
					$upd["Billtype"] = "DN";
					$upd["For1"] = "";
					$upd["For2"] = "";
					$upd["Chequeno"] = "";
					$upd["Knockoff"] = sprintf("%03d",$knockoff);
					$upd["Mark"] = "";
					$upd["Fail"] = "";
					$upd["User"] = "";
					$upd["Drfcamt"] = "";
					$upd["Crfcamt"] = "";
					$upd["Fcrate"] = "";
					$upd["Markpdc"] = "";
					$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["Taxdesc"] = "STAX";
					$upd["Taxamt"] = $this->selling_price_currency_format($dn['TotalAmount']);
					my_fputcsv($fp, $upd);
					$knockoff++;
					unset($upd);

					$upd["Batchno"] = "";
					$upd["Tranno"] = "";
					$upd["Accno"] = $dnTax['tax_account_code'];;
					$upd["Ref1"] = $refno;
					$upd["Ref2"] = $batchno;
					$upd["Date"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["Desc1"] = $dnTax['tax_account_name'];
					$upd["Desc2"] = "";
					$upd["Debitamt"] = "";
					$upd["Creditamt"] = $this->selling_price_currency_format($dnTax['TaxAmount']);
					$upd["Fcamt"] = "";
					$upd["Age"] = "";
					$upd["Source"] = "";
					$upd["Job"] = "";
					$upd["Agent"] = "";
					$upd["Paid"] = "";
					$upd["Billtype"] = "DN";
					$upd["For1"] = "";
					$upd["For2"] = "";
					$upd["Chequeno"] = "";
					$upd["Knockoff"] = sprintf("%03d",$knockoff);
					$upd["Mark"] = "";
					$upd["Fail"] = "";
					$upd["User"] = "";
					$upd["Drfcamt"] = "";
					$upd["Crfcamt"] = "";
					$upd["Fcrate"] = "";
					$upd["Markpdc"] = "";
					$upd["Dtpdc"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["Taxdesc"] = "STAX";
					$upd["Taxamt"] = $this->selling_price_currency_format($dn['TotalAmount']);
					my_fputcsv($fp, $upd);
					$knockoff++;
					unset($upd);
				}
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
		//todo
	}
}
?>
