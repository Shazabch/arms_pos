<?php
/*
2016-01-12 5:52 PM Kee Kee
- Fixed assign wrong term code
- Fixed assign wrong payment type

2016-03-07 11:49am Kee Kee
- Fixed assign wrong cash sales "Term Code"
- Fixed assign wrong receipt no in cash sales with group by receipt format
- Cash Sales cannot contain goods return amount

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2016-09-27 11:53 AM Kee Kee
- Fixed CN No duplicate error for different return receipt no issue

2016-12-2316:52 PM Kee Kee
- Added Purchase Retun and Sales Return into Export Accounting Settings

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php

2017-01-25 14:58 Qiu Ying
- Bug fixed on Cash Sales should show all transaction include receipt item with 0 amount

3/9/2018 3:44 PM Andy
- Enhanced to put branch code in column "Project".

3/12/2018 3:13 PM Andy
- Enhanced to put branch code in column "Project" at Details row.

3/19/2018 5:38 PM Andy
- Enhance SQL Accounting to have Other Settings > Project.

6/20/2018 1:34 PM Andy
- Enhanced AP format master row, column "Docref2" to show GRR No (ref_no).

4/19/2018 10:58 AM Justin
- Bug fixed DO Cash Sales wrongly group into 2 MASTER rows while contains rounding and sales.
*/
include_once("ExportModule.php");
class SQLAccounting extends ExportModule
{
	const NAME="SQL Accounting (Exclude Payment)";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $tvExportCol2 = array();
	var $accSettings = array();
	var $masteColCS = array("DocType"=>0,"Docno"=>1,"Docnoex"=>2,"Docdate"=>3,"Postdate"=>4,
							"Code"=>5,"Companyname"=>6,"Address1"=>7,"Address2"=>8,
							"Address3"=>9,"Address4"=>10,"Phone1"=>11,"Fax1"=>12,
							"Attention"=>13,"Area"=>14,"Agent"=>15,"Project"=>16,
							"Terms"=>17,"Currencyrate"=>18,"Description"=>19,"Cancelled"=>20,
							"Docamt"=>21,"Validity"=>22,"Deliveryterm"=>23,"Cc"=>24,
							"Docref1"=>25,"Docref2"=>26,"Docref3"=>27,"Docref4"=>28,
							"Branchname"=>29,"Daddress1"=>30,"Daddress2"=>31,"Daddress3"=>32,
							"Daddress4"=>33,"Dattention"=>34,"Dphone1"=>35,"Dfax1"=>36,
							"Transferable"=>37,"D_Amount"=>38,"P_Paymentmethod"=>39,"P_Chequenumber"=>40,
							"P_Bankcharge"=>41,"P_Amount"=>42,"P_Paymentproject"=>43);
	var $detailColCS = array("DocType"=>0,"Docno"=>1,"Number"=>2,"Itemcode"=>3,"Location"=>4,
							 "Project"=>5,"Description"=>6,"Description2"=>7,"Description3"=>8,
							 "Qty"=>9,"Uom"=>10,"Suomqty"=>11,"Unitprice"=>12,
							 "Deliverydate"=>13,"Disc"=>14,"Tax"=>15,"Taxamt"=>16,
							 "Amount"=>17,"Printable"=>18,"Account"=>19,"Transferable"=>20,
							 "Remark1"=>21,"Remark2"=>22);
	var $PreMasteColCS = array("DocType"=>0,
							   "Docno"=>0,
							   "Docnoex"=>0,
							   "Docdate"=>0,
							   "Postdate"=>0,
							   "Code"=>0,
							   "Companyname"=>0,
							   "Terms"=>0,
							   "Currencyrate"=>1,
							   "Description"=>0,
							   "Cancelled"=>0,
							   "Docamt"=>1,
							   "Branchname"=>0,
							   "Transferable"=>0,
							   "D_Amount"=>1);
	var $PreDetailColCS = array("DocType"=>0,
								"Docno"=>0,
								"Number"=>0,
								"Itemcode"=>0,
								"Description"=>0,
								"Qty"=>1,
								"Uom"=>0,
								"Suomqty"=>1,
								"Unitprice"=>1,
								"Deliverydate"=>0,
								"Disc"=>1,
								"Tax"=>0,
								"Taxamt"=>1,
								"Amount"=>1,
								"Account"=>0);
	var $ExportFileName = array(
								"cs" => "%s/sl_cs_%s.txt",
								"ap" => "%s/sl_ap_%s.txt",
								"ar" => "%s/sl_ar_%s.txt",
								"cn" => "%s/sl_cn_%s.txt",
								"dn" => "%s/sl_dn_%s.txt",
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
			"settings"=>array(
				"purchase" => array(
					"name"=>"Purchase",
					"account"=> array(
						"account_code"=> "500-000",
						"account_name" => "Purchase",
					),
				),
				'purchase_return' => array (
					"name"=>"Purchase Return",
					"account"=> array (
						'account_code' => '612-000',
						'account_name' => 'Purchase',
					)
				),
				"sales" => array(
					"name"=>"Sales",
					"account"=> array(
						"account_code"=> "500-000",
						"account_name" => "CASH SALES",
					),
				),
				'sales_return' => array (
					"name"=>"Sales Return",
					"account"=> array (
						'account_code' => '510-000',
						'account_name' => 'Sales Return',
					)
				),
				"customer_code" => array(
					"name"=>"Customer Code",
					"account"=> array(
						"account_code"=> "300-C0001",
						"account_name" => "CASH SALES - POS",
					),
					"help"=>"For POS Cash Sales only"
				),
				"rounding"=>array(
					"name"=>"Rounding",
					"account"=>array(
						"account_code"=>"500-000",
						"account_name"=>"CASH SALES",
					),
				),
				"short"=>array(
					"name"=>"Short",
					"account"=>array(
						"account_code"=>"500-000",
						"account_name"=>"SHORT",
					),
				),
				"over"=>array(
					"name"=>"Over",
					"account"=>array(
						"account_code"=>"500-000",
						"account_name"=>"OVER",
					),
				),
				"terms"=>array(
					"name"=>"TERMS",
					"account"=>array(
						"account_code" => "C.O.D",
						"account_name" => "CASH ON DELIVERY",
					),
				),
				"cash" => array(
					"name"=>"Cash",
					"account"=>array(
						"account_code"=>"320-000",
						"account_name"=>"CASH",
					),
				),
				// OTHERS
				'project' => array (
					"name"=>"Project",
					"data"=>'',
					"remark"=>"Default Value: Leave empty system will automatically insert BRANCH CODE. 
					Put - to disable default BRANCH CODE, system will put ---- at Master Row and empty at Details Row."
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
				if($this->sys=='lite'){
					$this->tvExportCol = array_merge($this->PreMasteColCS,array("dummy"=>0));
					$this->tvExportCol2 = array_merge($this->PreDetailColCS,array("dummy"=>0));
				}
				else{
					$this->tvExportCol = $this->PreMasteColCS;
					$this->tvExportCol2 = $this->PreDetailColCS;
				}
				break;
			default:
					$this->tvExportCol = array();
					$this->tvExportCol1 = array();
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
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, customer_name, pos_date, acc_type, tax_code, batchno,
													account_name, account_code,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where (tablename='pos' or tablename='membership')
													and `type` = 'credit' and has_credit_notes = 0 
													group by pos_date, batchno, acc_type, account_code, account_name, tax_code
													order by pos_date");

					$masterInfo = array();
					$DetailInfo = array();
					$bn = 1;
					$terms = $this->accSettings['terms']['account']['account_code'];
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$posDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$docDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						if(!isset($masterInfo[$posDate]))
						{
							$batchNo = sprintf("%s%05s",$result['batchno'],$bn);
							$masterInfo[$posDate][$batchNo]["doctype"] = "MASTER";
							$masterInfo[$posDate][$batchNo]["docno"] = $batchNo;
							$masterInfo[$posDate][$batchNo]["docnoex"] = "";
							$masterInfo[$posDate][$batchNo]["docdate"] = $docDate;
							$masterInfo[$posDate][$batchNo]["postdate"] = $posDate;
							$masterInfo[$posDate][$batchNo]["code"] = $result['customer_code'];
							$masterInfo[$posDate][$batchNo]["companyname"] = $result['customer_name'];
							$masterInfo[$posDate][$batchNo]["address1"] = "";
							$masterInfo[$posDate][$batchNo]["address2"] = "";
							$masterInfo[$posDate][$batchNo]["address3"] = "";
							$masterInfo[$posDate][$batchNo]["address4"] = "";
							$masterInfo[$posDate][$batchNo]["phone1"] = "";
							$masterInfo[$posDate][$batchNo]["fax1"] = "";
							$masterInfo[$posDate][$batchNo]["attention"] = "";
							$masterInfo[$posDate][$batchNo]["area"] = "----";
							$masterInfo[$posDate][$batchNo]["agent"] = "----";
							$masterInfo[$posDate][$batchNo]["project"] = $this->getProjectData('master', $branchCode);
							$masterInfo[$posDate][$batchNo]["terms"] = $terms;//"CASH";
							$masterInfo[$posDate][$batchNo]["currencyrate"] = $this->selling_price_currency_format(1);
							$masterInfo[$posDate][$batchNo]["description"] = "SALES";
							$masterInfo[$posDate][$batchNo]["cancelled"] = $result['cancelled'];
							$masterInfo[$posDate][$batchNo]["docamt"] = 0;
							$masterInfo[$posDate][$batchNo]["validity"] = "";
							$masterInfo[$posDate][$batchNo]["deliveryterm"] = "";
							$masterInfo[$posDate][$batchNo]["cc"] = "";
							$masterInfo[$posDate][$batchNo]["docref1"] = "";
							$masterInfo[$posDate][$batchNo]["docref2"] = "";
							$masterInfo[$posDate][$batchNo]["docref3"] = "";
							$masterInfo[$posDate][$batchNo]["docref4"] = "";
							$masterInfo[$posDate][$batchNo]["branchname"] = "BILLING";
							$masterInfo[$posDate][$batchNo]["daddress1"] = "";
							$masterInfo[$posDate][$batchNo]["daddress2"] = "";
							$masterInfo[$posDate][$batchNo]["daddress3"] = "";
							$masterInfo[$posDate][$batchNo]["daddress4"] = "";
							$masterInfo[$posDate][$batchNo]["dattention"] = "";
							$masterInfo[$posDate][$batchNo]["dphone1"] = "";
							$masterInfo[$posDate][$batchNo]["dfax1"] = "";
							$masterInfo[$posDate][$batchNo]["transferable"] = $result['transferable'];
							$masterInfo[$posDate][$batchNo]["d_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_paymentmethod"] = "";
							$masterInfo[$posDate][$batchNo]["p_chequenumber"] = "";
							$masterInfo[$posDate][$batchNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_paymentproject"] = "";
							if($this->sys=="lite") $masterInfo[$posDate][$batchNo]["dummy"] = "";
							$DetailInfo[$posDate][$batchNo] = array();
							$bn++;
						}

						$masterInfo[$posDate][$batchNo]["docamt"] += $result['TotalAmount'];
						$upd["doctype"] = "DETAIL";
						$upd["docno"] = $batchNo;
						$upd["number"] = "";
						$upd["itemcode"] = "";
						$upd["location"] = "----";
						$upd["project"] = $this->getProjectData('detail', $branchCode);
						$upd["description"] = $result['account_name'];
						$upd["description2"] = "";
						$upd["description3"] = "";
						$upd["qty"] = sprintf("%0.2f",1);
						$upd["uom"] = $result['uom'];
						$upd["suomqty"] = sprintf("%0.2f",1);
						$upd["unitprice"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["deliverydate"] = $posDate;
						$upd["disc"] = $this->selling_price_currency_format(0);
						$upd["tax"] = $result['tax_code'];
						$upd["taxamt"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["amount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["printable"] = "T";
						$upd["account"] = $result['account_code'];
						$upd["transferable"] = $result['transferable'];
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						if($this->sys=="lite") $upd["dummy"] = "";
						$DetailInfo[$posDate][$batchNo][] = $upd;
						unset($upd);
					}

					if($this->sys=='pos'){
						$batchNo = "";
						$ret = $this->sql_query($tmpSalesDb, "select customer_code, customer_name, pos_date, acc_type, tax_code, doc_no, ref_no, batchno,
													account_name, account_code,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where tablename='do'
													and `type` = 'credit'
													group by pos_date, ref_no, acc_type, account_code, account_name, tax_code
													order by pos_date, ref_no");

						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$posDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$docDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							
							$batchNo = $result['ref_no'].sprintf("%05s",$bn);
							if(!isset($masterInfo[$posDate][$batchNo]))
							{
								$masterInfo[$posDate][$batchNo]["doctype"] = "MASTER";
								$masterInfo[$posDate][$batchNo]["docno"] = $batchNo;
								$masterInfo[$posDate][$batchNo]["docnoex"] = $result['doc_no'];
								$masterInfo[$posDate][$batchNo]["docdate"] = $docDate;
								$masterInfo[$posDate][$batchNo]["postdate"] = $posDate;
								$masterInfo[$posDate][$batchNo]["code"] = $result['customer_code'];
								$masterInfo[$posDate][$batchNo]["companyname"] = $result['customer_name'];
								$masterInfo[$posDate][$batchNo]["address1"] = "";
								$masterInfo[$posDate][$batchNo]["address2"] = "";
								$masterInfo[$posDate][$batchNo]["address3"] = "";
								$masterInfo[$posDate][$batchNo]["address4"] = "";
								$masterInfo[$posDate][$batchNo]["phone1"] = "";
								$masterInfo[$posDate][$batchNo]["fax1"] = "";
								$masterInfo[$posDate][$batchNo]["attention"] = "";
								$masterInfo[$posDate][$batchNo]["area"] = "----";
								$masterInfo[$posDate][$batchNo]["agent"] = "----";
								$masterInfo[$posDate][$batchNo]["project"] = $this->getProjectData('master', $branchCode);
								$masterInfo[$posDate][$batchNo]["terms"] = $terms;//"CASH";
								$masterInfo[$posDate][$batchNo]["currencyrate"] = $this->selling_price_currency_format(1);
								$masterInfo[$posDate][$batchNo]["description"] = "SALES";
								$masterInfo[$posDate][$batchNo]["cancelled"] = $result['cancelled'];
								$masterInfo[$posDate][$batchNo]["docamt"] = 0;
								$masterInfo[$posDate][$batchNo]["validity"] = "";
								$masterInfo[$posDate][$batchNo]["deliveryterm"] = "";
								$masterInfo[$posDate][$batchNo]["cc"] = "";
								$masterInfo[$posDate][$batchNo]["docref1"] = "";
								$masterInfo[$posDate][$batchNo]["docref2"] = "";
								$masterInfo[$posDate][$batchNo]["docref3"] = "";
								$masterInfo[$posDate][$batchNo]["docref4"] = "";
								$masterInfo[$posDate][$batchNo]["branchname"] = "BILLING";
								$masterInfo[$posDate][$batchNo]["daddress1"] = "";
								$masterInfo[$posDate][$batchNo]["daddress2"] = "";
								$masterInfo[$posDate][$batchNo]["daddress3"] = "";
								$masterInfo[$posDate][$batchNo]["daddress4"] = "";
								$masterInfo[$posDate][$batchNo]["dattention"] = "";
								$masterInfo[$posDate][$batchNo]["dphone1"] = "";
								$masterInfo[$posDate][$batchNo]["dfax1"] = "";
								$masterInfo[$posDate][$batchNo]["transferable"] = $result['transferable'];
								$masterInfo[$posDate][$batchNo]["d_amount"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_paymentmethod"] = "";
								$masterInfo[$posDate][$batchNo]["p_chequenumber"] = "";
								$masterInfo[$posDate][$batchNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_amount"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_paymentproject"] = "";
								if($this->sys=="lite") $masterInfo[$posDate][$batchNo]["dummy"] = "";
								$DetailInfo[$posDate][$batchNo] = array();
							}else $bn++;

							$masterInfo[$posDate][$batchNo]["docamt"] += $result['TotalAmount'];
							$upd["doctype"] = "DETAIL";
							$upd["docno"] = $batchNo;
							$upd["number"] = "";
							$upd["itemcode"] = "";
							$upd["location"] = "----";
							$upd["project"] = $this->getProjectData('detail', $branchCode);
							$upd["description"] = $result['account_name'];
							$upd["description2"] = "";
							$upd["description3"] = "";
							$upd["qty"] = sprintf("%0.2f",1);
							$upd["uom"] = $result['uom'];
							$upd["suomqty"] = sprintf("%0.2f",1);
							$upd["unitprice"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["deliverydate"] = $posDate;
							$upd["disc"] = $this->selling_price_currency_format(0);
							$upd["tax"] = $result['tax_code'];
							$upd["taxamt"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["amount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["printable"] = "T";
							$upd["account"] = $result['account_code'];
							$upd["transferable"] = $result['transferable'];
							$upd["remark1"] = "";
							$upd["remark2"] = "";
							if($this->sys=="lite") $upd["dummy"] = "";
							$DetailInfo[$posDate][$batchNo][] = $upd;
							unset($upd);
						}
					}

					foreach($masterInfo as $posDate=>$doc)
					{
						foreach($doc as $docNo=>$docInfo)
						{
							my_fputcsv($fp, $docInfo,";");
							if(isset($DetailInfo[$posDate][$docNo]))
							{
								foreach($DetailInfo[$posDate][$docNo] as $docDetailInfo)
								{
									my_fputcsv($fp, $docDetailInfo,";");
								}
							}
						}
					}
					break;
				case 'monthly summary':
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, customer_name, ym as pos_date, acc_type, tax_code, batchno,
													account_name, account_code,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where (tablename='pos' or tablename='membership')
													and `type` = 'credit' and has_credit_notes = 0 
													group by ym, acc_type, account_code, account_name, tax_code
													order by ym");

					$masterInfo = array();
					$DetailInfo = array();
					$bn = 1;
					$terms = $this->accSettings['terms']['account']['account_code'];
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$posDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$docDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						if(!isset($masterInfo[$posDate]))
						{
							$batchNo = $result['batchno'].sprintf("%05s",$bn);
							$masterInfo[$posDate][$batchNo]["doctype"] = "MASTER";
							$masterInfo[$posDate][$batchNo]["docno"] = $batchNo;
							$masterInfo[$posDate][$batchNo]["docnoex"] = "";
							$masterInfo[$posDate][$batchNo]["docdate"] = $docDate;
							$masterInfo[$posDate][$batchNo]["postdate"] = $posDate;
							$masterInfo[$posDate][$batchNo]["code"] = $result['customer_code'];
							$masterInfo[$posDate][$batchNo]["companyname"] = $result['customer_name'];
							$masterInfo[$posDate][$batchNo]["address1"] = "";
							$masterInfo[$posDate][$batchNo]["address2"] = "";
							$masterInfo[$posDate][$batchNo]["address3"] = "";
							$masterInfo[$posDate][$batchNo]["address4"] = "";
							$masterInfo[$posDate][$batchNo]["phone1"] = "";
							$masterInfo[$posDate][$batchNo]["fax1"] = "";
							$masterInfo[$posDate][$batchNo]["attention"] = "";
							$masterInfo[$posDate][$batchNo]["area"] = "----";
							$masterInfo[$posDate][$batchNo]["agent"] = "----";
							$masterInfo[$posDate][$batchNo]["project"] = $this->getProjectData('master', $branchCode);
							$masterInfo[$posDate][$batchNo]["terms"] = $terms;//"CASH";
							$masterInfo[$posDate][$batchNo]["currencyrate"] = $this->selling_price_currency_format(1);
							$masterInfo[$posDate][$batchNo]["description"] = "SALES";
							$masterInfo[$posDate][$batchNo]["cancelled"] = $result['cancelled'];
							$masterInfo[$posDate][$batchNo]["docamt"] = 0;
							$masterInfo[$posDate][$batchNo]["validity"] = "";
							$masterInfo[$posDate][$batchNo]["deliveryterm"] = "";
							$masterInfo[$posDate][$batchNo]["cc"] = "";
							$masterInfo[$posDate][$batchNo]["docref1"] = "";
							$masterInfo[$posDate][$batchNo]["docref2"] = "";
							$masterInfo[$posDate][$batchNo]["docref3"] = "";
							$masterInfo[$posDate][$batchNo]["docref4"] = "";
							$masterInfo[$posDate][$batchNo]["branchname"] = "BILLING";
							$masterInfo[$posDate][$batchNo]["daddress1"] = "";
							$masterInfo[$posDate][$batchNo]["daddress2"] = "";
							$masterInfo[$posDate][$batchNo]["daddress3"] = "";
							$masterInfo[$posDate][$batchNo]["daddress4"] = "";
							$masterInfo[$posDate][$batchNo]["dattention"] = "";
							$masterInfo[$posDate][$batchNo]["dphone1"] = "";
							$masterInfo[$posDate][$batchNo]["dfax1"] = "";
							$masterInfo[$posDate][$batchNo]["transferable"] = $result['transferable'];
							$masterInfo[$posDate][$batchNo]["d_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_paymentmethod"] = "";
							$masterInfo[$posDate][$batchNo]["p_chequenumber"] = "";
							$masterInfo[$posDate][$batchNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$posDate][$batchNo]["p_paymentproject"] = "";
							if($this->sys=="lite") $masterInfo[$posDate][$batchNo]["dummy"] = "";
							$DetailInfo[$posDate][$batchNo] = array();
							$bn++;
						}

						$masterInfo[$posDate][$batchNo]["docamt"] += $result['TotalAmount'];
						$upd["doctype"] = "DETAIL";
						$upd["docno"] = $batchNo;
						$upd["number"] = "";
						$upd["itemcode"] = "";
						$upd["location"] = "----";
						$upd["project"] = $this->getProjectData('detail', $branchCode);
						$upd["description"] = $result['account_name'];
						$upd["description2"] = "";
						$upd["description3"] = "";
						$upd["qty"] = sprintf("%0.2f",1);
						$upd["uom"] = $result['uom'];
						$upd["suomqty"] = sprintf("%0.2f",1);
						$upd["unitprice"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["deliverydate"] = $posDate;
						$upd["disc"] = $this->selling_price_currency_format(0);
						$upd["tax"] = $result['tax_code'];
						$upd["taxamt"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["amount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["printable"] = "T";
						$upd["account"] = $result['account_code'];
						$upd["transferable"] = $result['transferable'];
						$upd["remark1"] = "";
						$upd["remark2"] = "";
						if($this->sys=="lite") $upd["dummy"] = "";
						$DetailInfo[$posDate][$batchNo][] = $upd;
						unset($upd);
					}

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select customer_code, customer_name, pos_date, acc_type, tax_code, doc_no, ref_no, batchno,
													account_name, account_code,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where tablename='do'
													and `type` = 'credit'
													group by pos_date, doc_no, acc_type, account_code, account_name, tax_code
													order by pos_date, doc_no");

						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$posDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$docDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$batchNo = $result['ref_no'].sprintf("%05s",$bn);
							if(!isset($masterInfo[$posDate][$batchNo]))
							{
								$masterInfo[$posDate][$batchNo]["doctype"] = "MASTER";
								$masterInfo[$posDate][$batchNo]["docno"] = $batchNo;
								$masterInfo[$posDate][$batchNo]["docnoex"] = $result['doc_no'];
								$masterInfo[$posDate][$batchNo]["docdate"] = $docDate;
								$masterInfo[$posDate][$batchNo]["postdate"] = $posDate;
								$masterInfo[$posDate][$batchNo]["code"] = $result['customer_code'];
								$masterInfo[$posDate][$batchNo]["companyname"] = $result['customer_name'];
								$masterInfo[$posDate][$batchNo]["address1"] = "";
								$masterInfo[$posDate][$batchNo]["address2"] = "";
								$masterInfo[$posDate][$batchNo]["address3"] = "";
								$masterInfo[$posDate][$batchNo]["address4"] = "";
								$masterInfo[$posDate][$batchNo]["phone1"] = "";
								$masterInfo[$posDate][$batchNo]["fax1"] = "";
								$masterInfo[$posDate][$batchNo]["attention"] = "";
								$masterInfo[$posDate][$batchNo]["area"] = "----";
								$masterInfo[$posDate][$batchNo]["agent"] = "----";
								$masterInfo[$posDate][$batchNo]["project"] = $this->getProjectData('master', $branchCode);
								$masterInfo[$posDate][$batchNo]["terms"] = $terms;//"CASH";
								$masterInfo[$posDate][$batchNo]["currencyrate"] = $this->selling_price_currency_format(1);
								$masterInfo[$posDate][$batchNo]["description"] = "SALES";
								$masterInfo[$posDate][$batchNo]["cancelled"] = $result['cancelled'];
								$masterInfo[$posDate][$batchNo]["docamt"] = 0;
								$masterInfo[$posDate][$batchNo]["validity"] = "";
								$masterInfo[$posDate][$batchNo]["deliveryterm"] = "";
								$masterInfo[$posDate][$batchNo]["cc"] = "";
								$masterInfo[$posDate][$batchNo]["docref1"] = "";
								$masterInfo[$posDate][$batchNo]["docref2"] = "";
								$masterInfo[$posDate][$batchNo]["docref3"] = "";
								$masterInfo[$posDate][$batchNo]["docref4"] = "";
								$masterInfo[$posDate][$batchNo]["branchname"] = "BILLING";
								$masterInfo[$posDate][$batchNo]["daddress1"] = "";
								$masterInfo[$posDate][$batchNo]["daddress2"] = "";
								$masterInfo[$posDate][$batchNo]["daddress3"] = "";
								$masterInfo[$posDate][$batchNo]["daddress4"] = "";
								$masterInfo[$posDate][$batchNo]["dattention"] = "";
								$masterInfo[$posDate][$batchNo]["dphone1"] = "";
								$masterInfo[$posDate][$batchNo]["dfax1"] = "";
								$masterInfo[$posDate][$batchNo]["transferable"] = $result['transferable'];
								$masterInfo[$posDate][$batchNo]["d_amount"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_paymentmethod"] = "";
								$masterInfo[$posDate][$batchNo]["p_chequenumber"] = "";
								$masterInfo[$posDate][$batchNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_amount"] = $this->selling_price_currency_format(0);
								$masterInfo[$posDate][$batchNo]["p_paymentproject"] = "";
								if($this->sys=="lite") $masterInfo[$posDate][$batchNo]["dummy"] = "";
								$DetailInfo[$posDate][$batchNo] = array();
								$bn++;
							}

							$masterInfo[$posDate][$batchNo]["docamt"] += $result['TotalAmount'];
							$upd["doctype"] = "DETAIL";
							$upd["docno"] = $batchNo;
							$upd["number"] = "";
							$upd["itemcode"] = "";
							$upd["location"] = "----";
							$upd["project"] = $this->getProjectData('detail', $branchCode);
							$upd["description"] = $result['account_name'];
							$upd["description2"] = "";
							$upd["description3"] = "";
							$upd["qty"] = sprintf("%0.2f",1);
							$upd["uom"] = $result['uom'];
							$upd["suomqty"] = sprintf("%0.2f",1);
							$upd["unitprice"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["deliverydate"] = $posDate;
							$upd["disc"] = $this->selling_price_currency_format(0);
							$upd["tax"] = $result['tax_code'];
							$upd["taxamt"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["amount"] = $this->selling_price_currency_format($result['ItemAmount']);
							$upd["printable"] = "T";
							$upd["account"] = $result['account_code'];
							$upd["transferable"] = $result['transferable'];
							$upd["remark1"] = "";
							$upd["remark2"] = "";
							if($this->sys=="lite") $upd["dummy"] = "";
							$DetailInfo[$posDate][$batchNo][] = $upd;
							unset($upd);
						}
					}

					foreach($masterInfo as $posDate=>$doc)
					{
						foreach($doc as $docNo=>$docInfo)
						{
							my_fputcsv($fp,$docInfo,";");
							if(isset($DetailInfo[$posDate][$docNo]))
							{
								foreach($DetailInfo[$posDate][$docNo] as $docDetailInfo)
								{
									my_fputcsv($fp,$docDetailInfo,";");
								}
							}
						}
					}
					break;
				case 'receipt':

					$ret = $this->sql_query($tmpSalesDb, "select customer_code, customer_name, pos_date, acc_type, tax_code, doc_no, ref_no,
													account_name, account_code, description,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where qty > 0
													and `type` = 'credit' and has_credit_notes = 0 
													group by pos_date, ref_no, acc_type, account_code, account_name, tax_code
													order by pos_date");

					$masterInfo = array();
					$DetailInfo = array();
					$bn = 1;
					$terms = $this->accSettings['terms']['account']['account_code'];
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$posDate = $this->set_date($this->ExportDateFormat,$result['pos_date']);
						$batchNo = $result['ref_no'];//$result['doc_no'].sprintf("%05s",$bn);

						if(!isset($masterInfo[$result['ref_no']][$batchNo]))
						{
							$masterInfo[$result['ref_no']][$batchNo]["doctype"] = "MASTER";
							$masterInfo[$result['ref_no']][$batchNo]["docno"] = $result['ref_no'];
							$masterInfo[$result['ref_no']][$batchNo]["docnoex"] = $result['docnoex'];
							$masterInfo[$result['ref_no']][$batchNo]["docdate"] = $posDate;
							$masterInfo[$result['ref_no']][$batchNo]["postdate"] = $posDate;
							$masterInfo[$result['ref_no']][$batchNo]["code"] = $result['customer_code'];
							$masterInfo[$result['ref_no']][$batchNo]["companyname"] = $result['customer_name'];
							$masterInfo[$result['ref_no']][$batchNo]["address1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["address2"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["address3"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["address4"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["phone1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["fax1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["attention"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["area"] = "----";
							$masterInfo[$result['ref_no']][$batchNo]["agent"] = "----";
							$masterInfo[$result['ref_no']][$batchNo]["project"] = $this->getProjectData('master', $branchCode);
							$masterInfo[$result['ref_no']][$batchNo]["terms"] = $terms;//"CASH";
							$masterInfo[$result['ref_no']][$batchNo]["currencyrate"] = $this->selling_price_currency_format(1);
							$masterInfo[$result['ref_no']][$batchNo]["description"] = "SALES";
							$masterInfo[$result['ref_no']][$batchNo]["cancelled"] = $result['cancelled'];
							$masterInfo[$result['ref_no']][$batchNo]["docamt"] = 0;
							$masterInfo[$result['ref_no']][$batchNo]["validity"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["deliveryterm"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["cc"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["docref1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["docref2"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["docref3"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["docref4"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["branchname"] = "BILLING";
							$masterInfo[$result['ref_no']][$batchNo]["daddress1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["daddress2"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["daddress3"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["daddress4"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["dattention"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["dphone1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["dfax1"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["transferable"] = $result['transferable'];
							$masterInfo[$result['ref_no']][$batchNo]["d_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$result['ref_no']][$batchNo]["p_paymentmethod"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["p_chequenumber"] = "";
							$masterInfo[$result['ref_no']][$batchNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
							$masterInfo[$result['ref_no']][$batchNo]["p_amount"] = $this->selling_price_currency_format(0);
							$masterInfo[$result['ref_no']][$batchNo]["p_paymentproject"] = "";
							if($this->sys=="lite") $masterInfo[$result['ref_no']][$batchNo]["dummy"] = "";
							$DetailInfo[$result['ref_no']][$batchNo] = array();
							$bn++;
						}

						$masterInfo[$result['ref_no']][$batchNo]["docamt"] += $result['TotalAmount'];
						$upd["doctype"] = "DETAIL";
						$upd["docno"] = $result['ref_no'];
						$upd["number"] = "";
						$upd["itemcode"] = "";
						$upd["location"] = $result['location'];
						$upd["project"] = $this->getProjectData('detail', $branchCode);
						$upd["description"] = $result['account_name'];
						$upd["description2"] = "";
						$upd["description3"] = "";
						$upd["qty"] = sprintf("%0.2f",1);
						$upd["uom"] = $result['uom'];
						$upd["suomqty"] = sprintf("%0.2f",1);
						$upd["unitprice"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["deliverydate"] = $posDate;
						$upd["disc"] = $this->selling_price_currency_format(0);
						$upd["tax"] = $result['tax_code'];
						$upd["taxamt"] = $this->selling_price_currency_format($result['TaxAmount']);
						$upd["amount"] = $this->selling_price_currency_format($result['ItemAmount']);
						$upd["printable"] = "T";
						$upd["account"] = $result['account_code'];
						$upd["transferable"] = $result['transferable'];
						$upd["remark1"] = $result['remark1'];
						$upd["remark2"] = $result['remark2'];
						if($this->sys=="lite") $upd["dummy"] = "";
						$DetailInfo[$result['ref_no']][$batchNo][] = $upd;
						unset($upd);
					}
					$tmpSalesDb->sql_freeresult($ret);
					foreach($masterInfo as $receiptNo=>$doc)
					{
						foreach($doc as $docNo=>$docInfo)
						{
							my_fputcsv($fp,$docInfo,";");
							if(isset($DetailInfo[$receiptNo][$docNo]))
							{
								foreach($DetailInfo[$receiptNo][$docNo] as $docDetailInfo)
								{
									my_fputcsv($fp,$docDetailInfo,";");
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


			$ret = $this->sql_query($tmpSalesDb, "select vendor_terms, vendor_code, vendor_name, inv_date, batchno, inv_no, ref_no,
										  round(sum(ItemAmount),2) as ItemAmount,
										  round(sum(TaxAmount),2) as TaxAmount,
										  round(sum(TotalAmount),2) as TotalAmount
										  from ".$this->tmpTable."
										  group by inv_no, inv_date, vendor_code
										  order by inv_date");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['batchno'];
				$InvDate = $this->set_date($this->ExportDateFormat,$result['inv_date']);
				$upd["doctype"] = "MASTER";
				$upd["docno"] = $result['inv_no'];;
				$upd["docnoex"] = "";
				$upd["docdate"] = $InvDate;
				$upd["postdate"] = $InvDate;
				$upd["code"] = $result['vendor_code'];
				$upd["companyname"] = $result['vendor_name'];
				$upd["address1"] = "";
				$upd["address2"] = "";
				$upd["address3"] = "";
				$upd["address4"] = "";
				$upd["phone1"] = "";
				$upd["fax1"] = "";
				$upd["attention"] = "";
				$upd["area"] = "----";
				$upd["agent"] = "----";
				$upd["project"] = $this->getProjectData('master', $this->branchCode);
				$upd["terms"] = $result['vendor_terms'];
				$upd["currencyrate"] = $this->selling_price_currency_format(1);
				$upd["description"] = "PURCHASE";
				$upd["cancelled"] = "F";
				$upd["docamt"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["validity"] = "";
				$upd["deliveryterm"] = "";
				$upd["cc"] = "";
				$upd["docref1"] = $batchNo;
				$upd["docref2"] = $result['ref_no'];
				$upd["docref3"] = "";
				$upd["docref4"] = "";
				$upd["branchname"] = "BILLING";
				$upd["daddress1"] = "";
				$upd["daddress2"] = "";
				$upd["daddress3"] = "";
				$upd["daddress4"] = "";
				$upd["dattention"] = "";
				$upd["dphone1"] = "";
				$upd["dfax1"] = "";
				$upd["transferable"] = "T";
				$upd["d_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentmethod"] = "";
				$upd["p_chequenumber"] = "";
				$upd["p_bankcharge"] = $this->selling_price_currency_format(0);
				$upd["p_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentproject"] = "----";
				if($this->sys=="lite") $upd["dummy"] = "";
				my_fputcsv($fp,$upd,";");
				unset($upd);
				$ret1 = $this->sql_query($tmpSalesDb, "select taxCode, gl_code,
											   round(sum(ItemAmount),2) as ItemAmount,
											   round(sum(TaxAmount),2) as TaxAmount,
											   round(sum(TotalAmount),2) as TotalAmount
											   from ".$this->tmpTable."
											   where inv_date=".ms($result['inv_date'])."
											   and inv_no=".ms($result['inv_no'])."
											   and vendor_code=".ms($result['vendor_code'])."
											   group by taxCode");
				while($result1 = $this->sql_fetchrow($tmpSalesDb, $ret1))
				{
					$upd["doctype"] = "DETAIL";
					$upd["docno"] = $result['inv_no'];;
					$upd["number"] = "";
					$upd["itemcode"] = "";
					$upd["location"] = "----";
					$upd["project"] = $this->getProjectData('detail', $this->branchCode);
					$upd["description"] = "PURCHASE";
					$upd["description2"] = "";
					$upd["description3"] = "";
					$upd["qty"] = sprintf("%0.2f",1);
					$upd["uom"] = "UNIT";
					$upd["suomqty"] = sprintf("%0.2f",0);
					$upd["unitprice"] = $this->selling_price_currency_format($result1['ItemAmount']);
					$upd["deliverydate"] = $InvDate;
					$upd["disc"] = $this->selling_price_currency_format(0);
					$upd["tax"] = $result1['taxCode'];
					$upd["taxamt"] = $this->selling_price_currency_format($result1['TaxAmount']);
					$upd["amount"] = $this->selling_price_currency_format($result1['ItemAmount']);
					$upd["printable"] = "T";
					$upd["account"] = $result1['gl_code'];
					$upd["transferable"] = "T";
					$upd["remark1"] = "";
					$upd["remark2"] = "";
					if($this->sys=="lite") $upd["dummy"] = "";
					my_fputcsv($fp,$upd,";");
					unset($upd);
				}
				$tmpSalesDb->sql_freeresult($ret1);
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
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);

			$fp = fopen($this->tmpFile, 'w');

			$ret = $this->sql_query($tmpSalesDb, "select do_date, inv_no, customer_code, customer_name, account_code, account_name,
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
			$masterInfo = array();
			$DetailInfo = array();
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$doDate = $this->set_date($this->ExportDateFormat,$result['do_date']);
				$invNo=$result['inv_no'];
				if(!isset($masterInfo[$invNo]))
				{
					$batchNo = $this->create_batch_no(date("ymd",strtotime($result['do_date'])),$bn);
					$masterInfo[$invNo]["doctype"] = "MASTER";
					$masterInfo[$invNo]["docno"] = $invNo;
					$masterInfo[$invNo]["docnoex"] = "";
					$masterInfo[$invNo]["docdate"] = $doDate;
					$masterInfo[$invNo]["postdate"] = $doDate;
					$masterInfo[$invNo]["code"] = $result['customer_code'];
					$masterInfo[$invNo]["companyname"] = $result['customer_name'];
					$masterInfo[$invNo]["address1"] = "";
					$masterInfo[$invNo]["address2"] = "";
					$masterInfo[$invNo]["address3"] = "";
					$masterInfo[$invNo]["address4"] = "";
					$masterInfo[$invNo]["phone1"] = "";
					$masterInfo[$invNo]["fax1"] = "";
					$masterInfo[$invNo]["attention"] = "";
					$masterInfo[$invNo]["area"] = "----";
					$masterInfo[$invNo]["agent"] = "----";
					$masterInfo[$invNo]["project"] = $this->getProjectData('master', $branchCode);
					$masterInfo[$invNo]["terms"] = $result['terms'];
					$masterInfo[$invNo]["currencyrate"] = $this->selling_price_currency_format($result['currencyrate']);
					$masterInfo[$invNo]["description"] = $result['account_name'];
					$masterInfo[$invNo]["cancelled"] = "F";
					$masterInfo[$invNo]["docamt"] = 0;
					$masterInfo[$invNo]["validity"] = "";
					$masterInfo[$invNo]["deliveryterm"] = "";
					$masterInfo[$invNo]["cc"] = "";
					$masterInfo[$invNo]["docref1"] = "";
					$masterInfo[$invNo]["docref2"] = "";
					$masterInfo[$invNo]["docref3"] = "";
					$masterInfo[$invNo]["docref4"] = "";
					$masterInfo[$invNo]["branchname"] = "BILLING";
					$masterInfo[$invNo]["daddress1"] = "";
					$masterInfo[$invNo]["daddress2"] = "";
					$masterInfo[$invNo]["daddress3"] = "";
					$masterInfo[$invNo]["daddress4"] = "";
					$masterInfo[$invNo]["dattention"] = "";
					$masterInfo[$invNo]["dphone1"] = "";
					$masterInfo[$invNo]["dfax1"] = "";
					$masterInfo[$invNo]["transferable"] = "T";
					$masterInfo[$invNo]["d_amount"] = $this->selling_price_currency_format(0);
					$masterInfo[$invNo]["p_paymentmethod"] = "";
					$masterInfo[$invNo]["p_chequenumber"] = "";
					$masterInfo[$invNo]["p_bankcharge"] = $this->selling_price_currency_format(0);
					$masterInfo[$invNo]["p_amount"] = $this->selling_price_currency_format(0);
					$masterInfo[$invNo]["p_paymentproject"] = "----";
					if($this->sys=="lite") $masterInfo[$invNo]["dummy"] = "";
					$DetailInfo[$invNo] = array();
					$bn++;
				}

				$masterInfo[$invNo]["docamt"] += $result['TotalAmount'];
				$upd["doctype"] = "DETAIL";
				$upd["docno"] = $invNo;
				$upd["number"] = "";
				$upd["itemcode"] = "";
				$upd["location"] = "";
				$upd["project"] = $this->getProjectData('detail', $branchCode);
				$upd["description"] = 'SALES';
				$upd["description2"] = $result['sku_description'];
				$upd["description3"] = "";
				$upd["qty"] = sprintf("%0.2f",1);
				$upd["uom"] = $result['uom'];
				$upd["suomqty"] = sprintf("%0.2f",$result['suomqty']);
				$upd["unitprice"] = $this->selling_price_currency_format(($result['currencyrate']>1)?$result['ItemFAmount']:$result['ItemAmount']);
				$upd["deliverydate"] = $doDate;
				$upd["disc"] = $this->selling_price_currency_format(0);
				$upd["tax"] = $result['tax_code'];
				$upd["taxamt"] = $this->selling_price_currency_format(($result['currencyrate']>1)?$result['TaxFAmount']:$result['TaxAmount']);
				$upd["amount"] = $this->selling_price_currency_format(($result['currencyrate']>1)?$result['ItemFAmount']:$result['ItemAmount']);
				$upd["printable"] = "T";
				$upd["account"] = $result['account_code'];
				$upd["transferable"] = "T";
				$upd["remark1"] = $result['account_name'];
				$upd["remark2"] = "";
				if($this->sys=="lite") $upd["dummy"] = "";
				$DetailInfo[$invNo][] = $upd;
				unset($upd, $invNo);
			}
			$tmpSalesDb->sql_freeresult($ret);
			foreach($masterInfo as $invNo=>$docInfo)
			{
				my_fputcsv($fp,$docInfo,";");

				if(isset($DetailInfo[$invNo]))
				{
					foreach($DetailInfo[$invNo] as $docDetailInfo)
					{
						my_fputcsv($fp,$docDetailInfo,";");
					}
				}
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
			$ret = $this->sql_query($tmpSalesDb, "select credit_note_no, date, currencyrate,
												customer_code, customer_name, terms, reason,
												sum(TotalAmount) as TotalAmount,
												sum(TotalFAmount) as TotalFAmount
												from ".$this->tmpTable."
												where TotalAmount <> 0
												group by date, credit_note_no
												order by date, credit_note_no");

			$bn = 1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				$cn['TotalAmount'] = abs($cn['TotalAmount']);
				$cnDate = $this->set_date($this->ExportDateFormat,$cn['date']);
				$upd["doctype"] = "MASTER";
				$upd["docno"] = $cn['credit_note_no'];
				$upd["docnoex"] = "";
				$upd["docdate"] = $cnDate;
				$upd["postdate"] = $cnDate;
				$upd["code"] = $cn['customer_code'];
				$upd["companyname"] = $cn['customer_name'];
				$upd["address1"] = "";
				$upd["address2"] = "";
				$upd["address3"] = "";
				$upd["address4"] = "";
				$upd["phone1"] = "";
				$upd["fax1"] = "";
				$upd["attention"] = "";
				$upd["area"] = "----";
				$upd["agent"] = "----";
				$upd["project"] = $this->getProjectData('master', $branchCode);
				$upd["terms"] = $cn['terms'];
				$upd["currencyrate"] = $this->selling_price_currency_format(1);
				$upd["description"] = str_replace(array("\r","\n","\r\n"),"",$cn['reason']);
				$upd["cancelled"] = "F";
				$upd["docamt"] = $this->selling_price_currency_format($cn['TotalAmount']);
				$upd["validity"] = "";
				$upd["deliveryterm"] = "";
				$upd["cc"] = "";
				$upd["docref1"] = "";
				$upd["docref2"] = "";
				$upd["docref3"] = "";
				$upd["docref4"] = "";
				$upd["branchname"] = "BILLING";
				$upd["daddress1"] = "";
				$upd["daddress2"] = "";
				$upd["daddress3"] = "";
				$upd["daddress4"] = "";
				$upd["dattention"] = "";
				$upd["dphone1"] = "";
				$upd["dfax1"] = "";
				$upd["transferable"] = "T";
				$upd["d_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentmethod"] = "";
				$upd["p_chequenumber"] = "";
				$upd["p_bankcharge"] = $this->selling_price_currency_format(0);
				$upd["p_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentproject"] = "----";
				if($this->sys=="lite") $upd["dummy"] = "";
				my_fputcsv($fp,$upd,";");
				unset($upd);

				$cond = "where date=".ms($cn['date'])." and credit_note_no = ".ms($cn['credit_note_no']);
				$ret2 = $this->sql_query($tmpSalesDb, "select return_receipt_no,tax_code, account_code, account_name, tax_account_code, tax_account_name,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												sum(ItemFAmount) as ItemFAmount,
												sum(TaxFAmount) as TaxFAmount,
												sum(TotalFAmount) as TotalFAmount
												from ".$this->tmpTable." $cond
												group by return_receipt_no,tax_code");
				while($cnTax = $this->sql_fetchrow($tmpSalesDb, $ret2))
				{
					$cnTax['ItemAmount'] = abs($cnTax['ItemAmount']);
					$cnTax['TaxAmount'] = abs($cnTax['TaxAmount']);
					$upd["doctype"] = "DETAIL";
					$upd["docno"] = $cn['credit_note_no'];
					$upd["number"] = "";
					$upd["itemcode"] = "";
					$upd["location"] = "----";
					$upd["project"] = $this->getProjectData('detail', $branchCode);
					$upd["description"] = $cnTax['return_receipt_no'];
					$upd["description2"] = "";
					$upd["description3"] = "";
					$upd["qty"] = sprintf("%0.2f",1);
					$upd["uom"] = "UNIT";
					$upd["suomqty"] = sprintf("%0.2f",1);
					$upd["unitprice"] = $this->selling_price_currency_format($cnTax['ItemAmount']);
					$upd["deliverydate"] = $cnDate;
					$upd["disc"] = $this->selling_price_currency_format(0);
					$upd["tax"] = $cnTax['tax_code'];
					$upd["taxamt"] = $this->selling_price_currency_format($cnTax['TaxAmount']);
					$upd["amount"] = $this->selling_price_currency_format($cnTax['ItemAmount']);
					$upd["printable"] = "T";
					$upd["account"] = $cnTax['account_code'];
					$upd["transferable"] = "T";
					$upd["remark1"] = $cnTax['account_name'];
					$upd["remark2"] = "";
					if($this->sys=="lite") $upd["dummy"] = "";
					my_fputcsv($fp,$upd,";");
					unset($upd);
				}
				$tmpSalesDb->sql_freeresult($ret2);
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
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');

			$ret = $this->sql_query($tmpSalesDb, "select date, invoice_no, customer_code, customer_name, currencyrate, currency_code,
													reason, terms,
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

			while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$upd["doctype"] = "MASTER";
				$upd["docno"] = $dn['invoice_no'];
				$upd["docnoex"] = "";
				$upd["docdate"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["postdate"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["code"] = $dn['customer_code'];
				$upd["companyname"] = $dn['customer_name'];
				$upd["address1"] = "";
				$upd["address2"] = "";
				$upd["address3"] = "";
				$upd["address4"] = "";
				$upd["phone1"] = "";
				$upd["fax1"] = "";
				$upd["attention"] = "";
				$upd["area"] = "----";
				$upd["agent"] = "----";
				$upd["project"] = $this->getProjectData('master', $branchCode);
				$upd["terms"] = $dn['terms'];
				$upd["currencyrate"] = $this->selling_price_currency_format(1);
				$upd["description"] = str_replace(array("\r","\n","\r\n"),"",$dn['reason']);
				$upd["cancelled"] = "F";
				$upd["docamt"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["validity"] = "";
				$upd["deliveryterm"] = "";
				$upd["cc"] = "";
				$upd["docref1"] = "";
				$upd["docref2"] = "";
				$upd["docref3"] = "";
				$upd["docref4"] = "";
				$upd["branchname"] = "BILLING";
				$upd["daddress1"] = "";
				$upd["daddress2"] = "";
				$upd["daddress3"] = "";
				$upd["daddress4"] = "";
				$upd["dattention"] = "";
				$upd["dphone1"] = "";
				$upd["dfax1"] = "";
				$upd["transferable"] = "T";
				$upd["d_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentmethod"] = "";
				$upd["p_chequenumber"] = "";
				$upd["p_bankcharge"] = $this->selling_price_currency_format(0);
				$upd["p_amount"] = $this->selling_price_currency_format(0);
				$upd["p_paymentproject"] = "----";
				if($this->sys=="lite") $upd["dummy"] = "";
				my_fputcsv($fp,$upd,";");
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
					$upd["doctype"] = "DETAIL";
					$upd["docno"] = $dn['invoice_no'];
					$upd["number"] = "";
					$upd["itemcode"] = "";
					$upd["location"] = "----";
					$upd["project"] = $this->getProjectData('detail', $branchCode);
					$upd["description"] = "";
					$upd["description2"] = "";
					$upd["description3"] = "";
					$upd["qty"] = sprintf("%0.2f",1);
					$upd["uom"] = "UNIT";
					$upd["suomqty"] = sprintf("%0.2f",0);
					$upd["unitprice"] = $this->selling_price_currency_format($dnTax['ItemAmount']);
					$upd["deliverydate"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["disc"] = $this->selling_price_currency_format(0);
					$upd["tax"] = $dnTax['tax_code'];
					$upd["taxamt"] = $this->selling_price_currency_format($dnTax['TaxAmount']);
					$upd["amount"] = $this->selling_price_currency_format($dnTax['ItemAmount']);
					$upd["printable"] = "T";
					$upd["account"] = $dnTax['account_code'];
					$upd["transferable"] = "T";
					$upd["remark1"] = $dnTax['account_name'];
					$upd["remark2"] = "";
					if($this->sys=="lite") $upd["dummy"] = "";
					my_fputcsv($fp,$upd,";");
					unset($upd);
				}
				$tmpSalesDb->sql_freeresult($ret2);
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_payment($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		//Comming Soon :D
	}
	
	private function getProjectData($rowType, $branchCode){
		$v = trim($this->accSettings['project']['data']);
		if($v == '')	return $branchCode;
		if($v == '-')	return $rowType == 'master' ? '----' : '';
		return $v;
	}
	/*function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		//todo
	}*/
}
?>
