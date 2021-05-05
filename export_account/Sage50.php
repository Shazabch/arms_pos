<?php
/*
2016-03-07 11:38am Kee Kee
- Fixed all document format, failed to import into Sage50 accounting software

2016-06-24 2:22pm Kee Kee
- Fixed failed export Rounding records into export module

2016-06-27 3:37 PM Kee Kee
- Remove filter -ve QTY items for export cash sales

2016-06-28 2:36PM Kee Kee
- Filter -ve QTY items for export cash sales with check has_credit_notes

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php

2017-01-18 16:27 Qiu Ying
- Bug fixed on showing wrong Sub Total and Grand Total when generate Cash Sales

2017-01-25 14:58 Qiu Ying
- Bug fixed on Cash Sales should show all transaction include receipt item with 0 amount

2017-02-03 14:06 Qiu Ying
- Remove testing message in Sales Credit Note

5/8/2017 8:37 AM Khausalya
- Enhanced changes from MYR to use config setting
*/
include_once("ExportModule.php");
class Sage50 extends ExportModule
{
	const NAME="Sage 50";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");

	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array("Invoice Type", "Customer Code", "Document Number", "Document Date",
									"Currency Code", "Exchange Rate", "Reference Number", "Delivery Mode Code",
									"Agent Code", "Credit Term Code", "Project Code", "Attention",
									"Address 1", "Delivery Address 1", "SI Total Line Discount", "SI Total Line Tax",
									"SI Sub Total", "SI Footer Discount %", "SI Footer Discount % 2", "SI Footer Discount % 3",
									"SI Footer Discount Total", "SI Footer Tax Code", "SI Total Footer Tax", "SI Rounding Adjustment",
									"SI Grand Total", "Item Code", "Item Description", "Item Location Code",
									"Quantity", "UOM Code", "Price","Amount",
									"Line Discount Amount", "Line Tax Code", "Line Tax Amount", "Line Tax GL Account Code",
									"Line Total", "Line Sales GL Account Code","SI Tax Type");
	var $ScreenCol = array("Invoice Type" => 0,
							"Customer Code" => 0,
							"Document Number" => 0,
							"Document Date" => 0,
							"Currency Code" => 0,
							"Credit Term Code" => 0,
							"SI Total Line Discount" => 1,
							"SI Total Line Tax" => 1,
							"SI Sub Total" => 1,
							"SI Rounding Adjustment" => 1,
							"SI Grand Total" => 1,
							"Item Code" => 0,
							"Item Description" => 0,
							"Quantity" => 1,
							"Price" => 1,
							"Amount" => 1,
							"Line Discount Amount" => 1,
							"Line Tax Code" => 1,
							"Line Tax Amount" => 1,
							"Line Total" => 1,
							"Line Sales GL Account Code" => 0,
							"dummy"=>0);
	var $ExportFileHeaderAP = array("Invoice Type", "Is Imported Service (Y/N)", "Supplier Code", "Document Number",
									"Document Date", "Currency Code", "Exchange Rate", "Permit No",
									"Reference Number", "Delivery Mode Code", "Purchaser Code",	"Credit Term Code",
									"Project Code",	"Attention", "Address 1", "PI Sub Total",
									"PI Total Line Discount", "PI Total Line Tax", "PI Footer Discount %", "PI Footer Discount % 2",
									"PI Footer Discount % 3", "PI Total Footer Discount", "PI Footer Tax Code", "PI Total Footer Tax",
									"PI Rounding Adjustment", "PI Grand Total", "Item Code", "Item Description",
									"Item Location Code", "Quantity", "UOM Code", "Price",
									"Amount", "Line Discount Amount", "Line Tax Code", "Line Tax Amount",
									"Line Tax GL Account Code", "Line Total", "Line Purchase GL Account Code", "Supplier Name",
									"Line Purchase GL Account Name", "Line Tax GL Account Name");
	var $ScreenColAP = array("Invoice Type" =>0,
							"Supplier Code" =>0,
							"Document Number" =>0,
							"Document Date" =>0,
							"PI Sub Total" =>1,
							"PI Total Line Discount" =>1,
							"PI Total Line Tax" =>1,
							"PI Rounding Adjustment" =>1,
							"PI Grand Total" =>1,
							"Item Code" =>0,
							"Item Description" =>0,
							"Quantity" =>1,
							"UOM Code" =>0,
							"Price" =>1,
							"Amount" =>1,
							"Line Discount Amount" =>1,
							"Line Tax Code" =>0,
							"Line Tax Amount" =>1,
							"Line Total" =>1,
							"Line Purchase GL Account Code" =>0,
							"Supplier Name" =>0,
							"Line Purchase GL Account Name" =>0,
							"Line Tax GL Account Name" =>0,
							"dummy"=>0);
	var $ExportFileHeaderCN = array("Customer Code", "Customer Name", "Document Number", "Document Date",
									"Currency Code", "Exchange Rate", "Reference Number", "Delivery Mode Code",
									"Agent Code", "Credit Term Code", "Project Code", "Attention",
									"Address 1", "Address 2", "Address 3", "Address 4",
									"City", "Postcode", "State Code", "Country Code",
									"Delivery Address - Attention", "Delivery Address 1", 
									"SCN Total Line Discount","SCN Total Line Tax","SCN Sub Total","SCN Rounding Adjustment",
									"SCN Grand Total", "Item Code", "Item Description", "Item Location Code", "Quantity",
									"UOM Code", "Price", "Amount", "Line Discount %", "Line Discount % 2", "Line Discount % 3",
									"Line Discount Amount", "Line Tax Code", "Line Tax Amount", "Line Tax GL Account Code",
									"Line Tax GL Account Name", "Line Total", "Line Sales GL Account Code", "Line Sales GL Account Name",
									"Line Reason Code", "Sales Invoice Number",
									"Line Discount GL Account Code", "Line Discount GL Account Name");	
	var $ExportFileHeaderDN = array("Supplier Code","Supplier Name","Document Number","Document Date",
									"Currency Code","Exchange Rate","Reference Number","Credit Term Code",
									"Address 1","Delivery Address 1","PDN Sub Total","PDN Total Line Discount","PDN Total Line Tax",
									"PDN Rounding Adjustment","PDN Grand Total","Item Code","Item Description",
									"Item Location Code","Quantity","UOM Code","Price","Amount","Line Discount %",
									"Line Discount Amount","Line Tax Code","Line Tax Amount","Line Tax GL Account Code",
									"Line Tax GL Account Name","Line Total","Line Purchase GL Account Code","Line Purchase GL Account Name","Line Reason Code");

	var $ExportFileName = array(
        "cs" => "%s/sage_cs_%s.csv",
		"ar" => "%s/sage_ar_%s.csv",
		"ap" => "%s/sage_ap_%s.csv",
		"cn" => "%s/sage_cn_%s.csv",
		"dn" => "%s/sage_dn_%s.csv",
    );
	var $ExportDateFormat = "d/m/Y";

	static function get_name() {
        return self::NAME;
    }

	static function get_property($sys='lite'){
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
			"settings"=>  array (
				"purchase"=>array(
					"name"=>"Purchase GL Account Code",
					"account"=>array(
						'account_code'=>"5000/0000",
						'account_name'=>"PURCHASE",
					)
				),
				"sales"=>array(
					"name"=>"Sales GL Account Code",
					"account"=>array(
						'account_code'=>"5000/0000",
						'account_name'=>"SALES",
					)
				),
				'customer_code' => array(
					"name"=>"Customer Code",
					"account"=> array (
						'account_code' => 'C0000012',
						'account_name' => 'CASH',
					),
					"help"=>"For POS Cash Sales only"
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '5000/0000',
						'account_name' => 'Over',
					)
				),
				'credit_term' => array(
					"name"=>"Credit Term Code",
					"account"=>array(
						'account_code'=>"COD",
						'account_name'=>"Cash on Delivery",
					)
				),				
				'purchase_tax' => array(
					"name"=>"Purchase Tax",
					"account"=>array(
						'account_code'=>"3050/000",
						"account_name"=>"Purchase Tax"
					)
				),
				'tax_gl_account' => array(
					"name"=>"Tax GL Account Code",
					"account"=>array(
						'account_code'=>"9500/000",
						"account_name"=>"Taxation"
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
					$this->tvExportCol = $this->ScreenCol;
				break;
			case 'ap':
					$this->tvExportCol = $this->ScreenColAP;
				break;
			case 'dn':
			case 'cn':
					$this->tvExportCol = $this->ScreenColCN;
				break;
			default:
					$this->tvExportCol = array();
				break;
		}
	}	
	
	function get_cash_sales($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);

			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeader);

			switch(strtolower($groupBy))
			{				
				case 'daily summary':					
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, batchno,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TaxAmount),2) as TaxAmount,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  where `type` = 'credit' and has_credit_notes = 0			
												  and (tablename='pos' or tablename='membership')
												  group by pos_date, batchno");
					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{	
						$posDate=$result['pos_date'];
						$branch_id=$result['branch_id'];
						$batchNo = $result['batchno'];

						$ret2 = $this->sql_query($tmpSalesDb, "select sum(TotalAmount) as TotalAmount
																from ".$this->tmpTable."
																where pos_date = ".ms($posDate)."
																and (tablename='pos' or tablename='membership')
																and acc_type=".ms("rounding")."
																and branch_id=".mi($branch_id));
						$rounding_adjustment = 0;
						if($tmpSalesDb->sql_numrows($ret2)>0){
							$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
							$rounding_adjustment = $result1['TotalAmount'];
							unset($result1);
						}
						$tmpSalesDb->sql_freeresult($ret2);
						$batchNo = $result['batchno'].sprintf("%05s",$bn);
						$ret1 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, account_code,
													   round(sum(ItemAmount),2) as ItemAmount,
													   round(sum(TaxAmount),2) as TaxAmount,
													   round(sum(TotalAmount),2) as TotalAmount
													   from ".$this->tmpTable."
													   where `type` = 'credit' and has_credit_notes = 0 
													   and pos_date = ".ms($posDate)."
													   and branch_id = ".mi($branch_id)."
													   and (tablename='pos' or tablename='membership')
													   and acc_type <> ".ms("rounding")."													   
													   group by acc_type, tax_code");
						$result['TotalAmount']=$result['TotalAmount']-$rounding_adjustment;
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$upd["invoice_type"] = 2;
							$upd["customer_code"] = $result2['customer_code'];
							$upd["document_number"] = $batchNo;
							$upd["document_date"] = $this->set_date($this->ExportDateFormat,$posDate);
							$upd["currency_code"] = $config["arms_currency"]["code"];
							$upd["exchange_rate"] = 1;
							$upd["reference_number"] = $batchNo;
							$upd["delivery_mode_code"] = "";
							$upd["agent_code"] = "";
							$upd["credit_term_code"] = $this->accSettings['credit_term']['account']['account_code'];
							$upd["project_code"] = "";
							$upd["attention"] = "";
							$upd["address_1"] = "Text";		
							$upd["delivery_address_1"] = "Text";			
							$upd["si_total_line_discount"] = 0;
							$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["si_sub_total"] = $this->selling_price_currency_format($result['TotalAmount']);
							$upd["si_footer_discount_%"] = 0;
							$upd["si_footer_discount_%_2"] = 0;
							$upd["si_footer_discount_%_3"] = 0;
							$upd["si_footer_discount_total"] = 0;
							$upd["si_footer_tax_code"] = "";
							$upd["si_total_footer_tax"] = "";
							$upd["si_rounding_adjustment"] = $this->selling_price_currency_format($rounding_adjustment);
							$upd["si_grand_total"] = $this->selling_price_currency_format(($result['TotalAmount']+$rounding_adjustment));
							$upd["item_code"] = "Text";
							$upd["item_description"] = "SALES";
							$upd["item_location_code"] = "DEFAULT";
							$upd["quantity"] = 1;
							$upd["uom_code"] = "Unit";
							$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["line_discount_amount"] = 0;
							$upd["line_tax_code"] = ($result2['tax_code']==""?"OS":$result2['tax_code']);
							$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
							$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
							$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
							$upd["line_sales_gl_account_code"] = $result2['account_code'];
							$upd["si_tax_type"] = 1;														
							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id][]=$upd;
							unset($upd);
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, doc_no, ref_no, batchno,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type` = 'credit'
													and tablename='do'
													group by ref_no");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$posDate=$result['pos_date'];
							$branch_id=$result['branch_id'];
							$batchNo = $result['batchno'];
							$ret2 = $this->sql_query($tmpSalesDb, "select sum(TotalAmount) as TotalAmount
														  from ".$this->tmpTable."
														  where ref_no = ".ms($result['ref_no'])."
														  and tablename='do'
														  and branch_id=".mi($branch_id)."
														  and acc_type=".ms("rounding"));
							$rounding_adjustment = 0;
							if($tmpSalesDb->sql_numrows($ret2)>0){
								$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
								$rounding_adjustment = $result1['TotalAmount'];
								unset($result1);
							}
							$tmpSalesDb->sql_freeresult($ret2);

							$ret1 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, account_code,
														 round(sum(ItemAmount),2) as ItemAmount,
														 round(sum(TaxAmount),2) as TaxAmount,
														 round(sum(TotalAmount),2) as TotalAmount
														 from ".$this->tmpTable."
														 where `type` = 'credit'
														 and ref_no = ".ms($result['ref_no'])."
														 and tablename='do'
														 and acc_type <> ".ms("rounding")."
														 and branch_id=".mi($branch_id)."
														 group by acc_type, tax_code");
							$result['TotalAmount']=$result['TotalAmount']-$rounding_adjustment;
							while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
							{
								$upd["invoice_type"] = 2;
								$upd["customer_code"] = $result2['customer_code'];
								$upd["document_number"] = $result['ref_no'];
								$upd["document_date"] = $this->set_date($this->ExportDateFormat,$posDate);
								$upd["currency_code"] = $config["arms_currency"]["code"];
								$upd["exchange_rate"] = 1;
								$upd["reference_number"] = $batchNo;
								$upd["delivery_mode_code"] = "";
								$upd["agent_code"] = "";
								$upd["credit_term_code"] = $this->accSettings['credit_term']['account']['account_code'];
								$upd["project_code"] = "";
								$upd["attention"] = "";
								$upd["address_1"] = "Text";
								$upd["delivery_address_1"] = "Text";
								$upd["si_total_line_discount"] = 0;
								$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
								$upd["si_sub_total"] = $this->selling_price_currency_format($result['TotalAmount']);
								$upd["si_footer_discount_%"] = 0;
								$upd["si_footer_discount_%_2"] = 0;
								$upd["si_footer_discount_%_3"] = 0;
								$upd["si_footer_discount_total"] = 0;
								$upd["si_footer_tax_code"] = "";
								$upd["si_total_footer_tax"] = "";
								$upd["si_rounding_adjustment"] = $this->selling_price_currency_format($rounding_adjustment);
								$upd["si_grand_total"] = $this->selling_price_currency_format(($result['TotalAmount']+$rounding_adjustment));
								$upd["item_code"] = "Text";
								$upd["item_description"] = "SALES";
								$upd["item_location_code"] = "DEFAULT";
								$upd["quantity"] = 1;
								$upd["uom_code"] = "Unit";
								$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
								$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
								$upd["line_discount_amount"] = 0;
								$upd["line_tax_code"] = ($result2['tax_code']==""?"OS":$result2['tax_code']);
								$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
								$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
								$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
								$upd["line_sales_gl_account_code"] = $result2['account_code'];
								$upd["si_tax_type"] = 1;								
								if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
								if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
								$tmpSalesDetail[$posDate][$branch_id][]=$upd;
								unset($upd);
							}
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
					$ret = $this->sql_query($tmpSalesDb, "select ym, pos_date, branch_id,batchno,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type` = 'credit' and has_credit_notes = 0 
													and (tablename='pos' or tablename='membership')
													group by ym, branch_id");
					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{	
						$posDate=$result['ym'];
						$branch_id=$result['branch_id'];
						$batchNo = $result['batchno'];

						$ret2 = $this->sql_query($tmpSalesDb, "select sum(TotalAmount) as TotalAmount
																from ".$this->tmpTable."
																where ym = ".ms($posDate)."
																and (tablename='pos' or tablename='membership')
																and branch_id=".mi($branch_id)."
																and acc_type=".ms("rounding"));
						$rounding_adjustment = 0;
						if($tmpSalesDb->sql_numrows($ret2)>0){
							$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
							$rounding_adjustment = $result1['TotalAmount'];
							unset($result1);
						}
						$result['TotalAmount']=$result['TotalAmount']-$rounding_adjustment;
						$tmpSalesDb->sql_freeresult($ret2);

						$ret1 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, account_code,
													   round(sum(ItemAmount),2) as ItemAmount,
													   round(sum(TaxAmount),2) as TaxAmount,
													   round(sum(TotalAmount),2) as TotalAmount
													   from ".$this->tmpTable."
													   where `type` = 'credit' and has_credit_notes = 0 
													   and ym = ".ms($posDate)."
													   and acc_type <> ".ms("rounding")."												
													   and (tablename='pos' or tablename='membership')
													   and branch_id=".mi($branch_id)."
													   group by acc_type, tax_code");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$upd["invoice_type"] = 2;
							$upd["customer_code"] = $result2['customer_code'];
							$upd["document_number"] = $batchNo;
							$upd["document_date"] = $this->set_date($this->ExportDateFormat,$posDate);
							$upd["currency_code"] = $config["arms_currency"]["code"];
							$upd["exchange_rate"] = 1;
							$upd["reference_number"] = "";
							$upd["delivery_mode_code"] = "";
							$upd["agent_code"] = "";
							$upd["credit_term_code"] = $this->accSettings['credit_term']['account']['account_code'];
							$upd["project_code"] = "";
							$upd["attention"] = "";
							$upd["address_1"] = "Text";
							$upd["delivery_address_1"] = "Text";
							$upd["si_total_line_discount"] = 0;
							$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["si_sub_total"] = $this->selling_price_currency_format($result['TotalAmount']);
							$upd["si_footer_discount_%"] = 0;
							$upd["si_footer_discount_%_2"] = 0;
							$upd["si_footer_discount_%_3"] = 0;
							$upd["si_footer_discount_total"] = 0;
							$upd["si_footer_tax_code"] = "";
							$upd["si_total_footer_tax"] = "";
							$upd["si_rounding_adjustment"] = $this->selling_price_currency_format($rounding_adjustment);
							$upd["si_grand_total"] = $this->selling_price_currency_format(($result['TotalAmount']+$rounding_adjustment));
							$upd["item_code"] = "Text";
							$upd["item_description"] = "SALES";
							$upd["item_location_code"] = "DEFAULT";
							$upd["quantity"] = 1;
							$upd["uom_code"] = "Unit";
							$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["line_discount_amount"] = 0;
							$upd["line_tax_code"] = ($result2['tax_code']==""?"OS":$result2['tax_code']);
							$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
							$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
							$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
							$upd["line_sales_gl_account_code"] = $result2['account_code'];
							$upd["si_tax_type"] = 1;							
							if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
							if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
							$tmpSalesDetail[$posDate][$branch_id][]=$upd;
							unset($upd);
						}
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);

					if($this->sys=='pos'){
						$ret = $this->sql_query($tmpSalesDb, "select pos_date, branch_id, doc_no, ref_no,batchno,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount
													from ".$this->tmpTable."
													where `type` = 'credit'
													and tablename='do'
													group by ref_no");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
						{
							$posDate=$result['pos_date'];
							$branch_id=$result['branch_id'];
							$batchNo = $result['batchno'];
							$ret2 = $this->sql_query($tmpSalesDb, "select sum(TotalAmount) as TotalAmount
														  from ".$this->tmpTable."
														  where ref_no = ".ms($result['ref_no'])."
														  and tablename='do'
														  and branch_id=".mi($branch_id)."
														  and acc_type=".ms("rounding"));
							$rounding_adjustment = 0;
							if($tmpSalesDb->sql_numrows($ret2)>0){
								$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
								$rounding_adjustment = $result1['TotalAmount'];
								unset($result1);
							}
							$result['TotalAmount']=$result['TotalAmount']-$rounding_adjustment;
							$tmpSalesDb->sql_freeresult($ret2);

							$ret1 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, account_code,
														 round(sum(ItemAmount),2) as ItemAmount,
														 round(sum(TaxAmount),2) as TaxAmount,
														 round(sum(TotalAmount),2) as TotalAmount
														 from ".$this->tmpTable."
														 where `type` = 'credit'
														 and acc_type <> ".ms("rounding")."
														 and ref_no = ".ms($result['ref_no'])."
														 and tablename='do'
														 and branch_id=".mi($branch_id)."
														 group by acc_type, tax_code");
							while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
							{
								$upd["invoice_type"] = 2;
								$upd["customer_code"] = $result2['customer_code'];
								$upd["document_number"] = $result['ref_no'];
								$upd["document_date"] = $this->set_date($this->ExportDateFormat,$posDate);
								$upd["currency_code"] = $config["arms_currency"]["code"];
								$upd["exchange_rate"] = 1;
								$upd["reference_number"] = $batchNo;
								$upd["delivery_mode_code"] = "";
								$upd["agent_code"] = "";
								$upd["credit_term_code"] = $this->accSettings['credit_term']['account']['account_code'];
								$upd["project_code"] = "";
								$upd["attention"] = "";
								$upd["address_1"] = "Text";
								$upd["delivery_address_1"] = "Text";
								$upd["si_total_line_discount"] = 0;
								$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
								$upd["si_sub_total"] = $this->selling_price_currency_format($result['TotalAmount']);
								$upd["si_footer_discount_%"] = 0;
								$upd["si_footer_discount_%_2"] = 0;
								$upd["si_footer_discount_%_3"] = 0;
								$upd["si_footer_discount_total"] = 0;
								$upd["si_footer_tax_code"] = "";
								$upd["si_total_footer_tax"] = "";
								$upd["si_rounding_adjustment"] = $this->selling_price_currency_format($rounding_adjustment);
								$upd["si_grand_total"] = $this->selling_price_currency_format(($result['TotalAmount']+$rounding_adjustment));
								$upd["item_code"] = "Text";
								$upd["item_description"] = "SALES";
								$upd["item_location_code"] = "DEFAULT";
								$upd["quantity"] = 1;
								$upd["uom_code"] = "Unit";
								$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
								$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
								$upd["line_discount_amount"] = 0;
								$upd["line_tax_code"] = ($result2['tax_code']==""?"OS":$result2['tax_code']);
								$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
								$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
								$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
								$upd["line_sales_gl_account_code"] = $result2['account_code'];
								$upd["si_tax_type"] = 1;								
								if(!isset($tmpSalesDetail[$posDate])) $tmpSalesDetail[$posDate]=array();
								if(!isset($tmpSalesDetail[$posDate][$branch_id])) $tmpSalesDetail[$posDate][$branch_id]=array();
								$tmpSalesDetail[$posDate][$branch_id][]=$upd;
								unset($upd);
							}
						}
						$tmpSalesDb->sql_freeresult($ret);
					}

					ksort($tmpSalesDetail);
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
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, branch_id,batchno,
												  round(sum(ItemAmount),2) as ItemAmount,
												  round(sum(TaxAmount),2) as TaxAmount,
												  round(sum(TotalAmount),2) as TotalAmount
												  from ".$this->tmpTable."
												  where `type` = 'credit' and has_credit_notes = 0
												  group by pos_date, ref_no
												  order by pos_date, branch_id, ref_no");
					$bn = 1;
					$rounding_adjustment = 0;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$ret2 = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount
													   from ".$this->tmpTable."
													   where ref_no = ".ms($result['ref_no'])."
													   and acc_type = ".ms("Rounding"));
						if($tmpSalesDb->sql_numrows($ret2)>0)
						{
							$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
							$rounding_adjustment = $result1['TotalAmount'];
						}
						$tmpSalesDb->sql_freeresult($ret2);
						unset($result1);
						$batchNo = sprintf($LANG['SET_RECEIPT_NO_PREFIX'],date("ymd",strtotime($result['pos_date'])),sprintf("%04d",$bn));
						$ret1 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, account_code,
													   round(sum(ItemAmount),2) as ItemAmount,
													   round(sum(TaxAmount),2) as TaxAmount,
													   round(sum(TotalAmount),2) as TotalAmount
													   from ".$this->tmpTable."
													   where `type` = 'credit' and has_credit_notes = 0 
													   and ref_no = ".ms($result['ref_no'])."
													   and acc_type <> ".ms("rounding")."
													   group by tax_code");
						$result['TotalAmount']=$result['TotalAmount']-$rounding_adjustment;
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$upd["invoice_type"] = 2;
							$upd["customer_code"] = $result2['customer_code'];
							$upd["document_number"] = $result['ref_no'];
							$upd["document_date"] = $this->set_date($this->ExportDateFormat,$result['pos_date']);
							$upd["currency_code"] = $config["arms_currency"]["code"];
							$upd["exchange_rate"] = 1;
							$upd["reference_number"] = $result['batchno'];
							$upd["delivery_mode_code"] = "";
							$upd["agent_code"] = "";
							$upd["credit_term_code"] = $this->accSettings['credit_term']['account']['account_code'];
							$upd["project_code"] = "";
							$upd["attention"] = "";
							$upd["address_1"] = "Text";
							$upd["delivery_address_1"] = "Text";
							$upd["si_total_line_discount"] = 0;
							$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
							$upd["si_sub_total"] = $this->selling_price_currency_format($result['TotalAmount']);
							$upd["si_footer_discount_%"] = 0;
							$upd["si_footer_discount_%_2"] = 0;
							$upd["si_footer_discount_%_3"] = 0;
							$upd["si_footer_discount_total"] = 0;
							$upd["si_footer_tax_code"] = "";
							$upd["si_total_footer_tax"] = "";
							$upd["si_rounding_adjustment"] = $this->selling_price_currency_format($rounding_adjustment);
							$upd["si_grand_total"] = $this->selling_price_currency_format(($result['TotalAmount']+$rounding_adjustment));
							$upd["item_code"] = "Text";
							$upd["item_description"] = "SALES";
							$upd["item_location_code"] = "DEFAULT";
							$upd["quantity"] = 1;
							$upd["uom_code"] = "Unit";
							$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
							$upd["line_discount_amount"] = 0;
							$upd["line_tax_code"] = ($result2['tax_code']==""?"OS":$result2['tax_code']);
							$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
							$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
							$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
							$upd["line_sales_gl_account_code"] = $result2['account_code'];				
							$upd["si_tax_type"] = 1;								
							my_fputcsv($fp, $upd);
							unset($upd);
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
	
	function get_account_payable($tmpSalesDb,$groupBy,$dateTo)
	{
		global $LANG, $config;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile,'w');
			fputcsv($fp, $this->ExportFileHeaderAP);

			$ret = $this->sql_query($tmpSalesDb, "select
						vendor_code,
						vendor_name,
						vendor_terms,
						inv_date,
						inv_no,
						gl_code,
						gl_name,
						batchno,
						sum(ItemAmount) as ItemAmount,
						sum(TaxAmount) as TaxAmount,
						sum(TotalAmount) as TotalAmount,
						sum(receive_qty) as rqty
						from ".$this->tmpTable."
						group by vendor_code, inv_no,
						inv_date");
			$bn = 1;
			while($retAp = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$inv_no = $retAp['inv_no'];
				$InvDate = $this->set_date($this->ExportDateFormat,$retAp['inv_date']);
				$ret2 = $this->sql_query($tmpSalesDb, "select
							taxCode,
							sum(ItemAmount) as ItemAmount,
							sum(TaxAmount) as TaxAmount,
							sum(TotalAmount) as TotalAmount
							from ".$this->tmpTable.
							" where vendor_code = ".ms($retAp['vendor_code']).
							" and inv_date = ".ms($retAp['inv_date']).
							" and inv_no = ".ms($retAp['inv_no']).
							" group by taxCode");
				while($retAPItem = $this->sql_fetchrow($tmpSalesDb, $ret2))
				{
					$upd["invoice type"] = 0;
					$upd["is_imported_service"] = 0;
					$upd["supplier_code"] = $retAp['vendor_code'];
					$upd["document_number"] = $inv_no;
					$upd["document_date"] = $InvDate;
					$upd["currency_code"] = $config["arms_currency"]["code"];
					$upd["exchange_rate"] = 1;
					$upd["permit_no"] = "";
					$upd["reference_number"] = $retAp['batchno'];
					$upd["delivery_mode_code"] = "";
					$upd["purchaser_code"] = "";
					$upd["credit_term_code"] = $retAp['vendor_terms'];
					$upd["project_code"] = "";
					$upd["attention"] = "";
					$upd["address_1"] = "Text";
					$upd["pi_sub_total"] = $this->selling_price_currency_format($retAp['ItemAmount']);
					$upd["pi_total_line_discount"] = $this->selling_price_currency_format(0);
					$upd["pi_total_line_tax"] = $this->selling_price_currency_format($retAp['TaxAmount']);
					$upd["pi_footer_discount_%"] = $this->selling_price_currency_format(0);
					$upd["pi_footer_discount_%_2"] = $this->selling_price_currency_format(0);
					$upd["pi_footer_discount_%_3"] = $this->selling_price_currency_format(0);
					$upd["pi_total_footer_discount"] = $this->selling_price_currency_format(0);
					$upd["pi_footer_tax_code"] = $this->selling_price_currency_format(0);
					$upd["pi_total_footer_tax"] = $this->selling_price_currency_format(0);
					$upd["pi_rounding_adjustment"] = $this->selling_price_currency_format(0);
					$upd["pi_grand_total"] = $this->selling_price_currency_format($retAp['TotalAmount']);
					$upd["item_code"] = "Text";
					$upd["item_description"] = "Purchase";
					$upd["item_location_code"] = "Text";
					$upd["quantity"] = 1;
					$upd["uom_code"] = "Unit";
					$upd["price"] = $this->selling_price_currency_format($retAPItem['ItemAmount']);
					$upd["amount"] = $this->selling_price_currency_format($retAPItem['ItemAmount']);
					$upd["line_discount_amount"] = $this->selling_price_currency_format(0);
					$upd["line_tax_code"] = $retAPItem['taxCode'];
					$upd["line_tax_amount"] = $this->selling_price_currency_format($retAPItem['TaxAmount']);
					$upd["line_tax_gl_account_code"] = (!isset($this->accSettings['purchase_tax'])?"3050/000":$this->accSettings['purchase_tax']['account']['account_code']);//"Text";"Text";
					$upd["line_total"] = $this->selling_price_currency_format($retAPItem['TotalAmount']);
					$upd["line_purchase_gl_account_code"] = $retAp['gl_code'];
					$upd["supplier_name"] = $retAp['vendor_name'];
					$upd["line_purchase_gl_account_name"] = $retAp['gl_name'];
					$upd["line_tax_gl_account_name"] = (!isset($this->accSettings['purchase_tax'])?"Purchase Tax":$this->accSettings['purchase_tax']['account']['account_name']);
					fputcsv($fp,$upd);
					unset($upd);
				}
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
			my_fputcsv($fp, $this->ExportFileHeader);

			$ret = $this->sql_query($tmpSalesDb, "select inv_no, do_date,batchno,
										  round(sum(ItemAmount),2) as ItemAmount,
										  round(sum(TaxAmount),2) as TaxAmount,
										  round(sum(TotalAmount),2) as TotalAmount
										  from ".$this->tmpTable."
										  group by do_date, inv_no
                                          order by do_date, inv_no");
			$bn = 1;
			while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$batchNo = $result['batchno'];
				$ret1 = $this->sql_query($tmpSalesDb, "select do_date, inv_no, customer_code, customer_name, account_code, account_name,
                                tax_code, tax_account_code, tax_account_name, currencyrate, foreign_currency_code, terms,
                                round(sum(ItemAmount),2) as ItemAmount,
                                round(sum(TaxAmount),2) as TaxAmount,
                                round(sum(TotalAmount),2) as TotalAmount,
                                round(sum(ItemFAmount),2) as ItemFAmount,
                                round(sum(TaxFAmount),2) as TaxFAmount,
                                round(sum(TotalFAmount),2) as TotalFAmount
                                from ".$this->tmpTable."
                                where do_date = '".$result['do_date']."'
                                and inv_no = '".$result['inv_no']."'
                                group by customer_code, account_code, account_name, tax_code");
				while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
				{
					$upd["invoice_type"] = 0;
					$upd["customer_code"] = $result2['customer_code'];
					$upd["document_number"] = $result['inv_no'];
					$upd["document_date"] = $this->set_date($this->ExportDateFormat,$result['do_date']);
					$upd["currency_code"] = $result2['foreign_currency_code'];
					$upd["exchange_rate"] = $result2['currencyrate'];
					$upd["reference_number"] = $result['inv_no'];
					$upd["delivery_mode_code"] = "";
					$upd["agent_code"] = "";
					$upd["credit_term_code"] = $result2['terms'];
					$upd["project_code"] = "";
					$upd["attention"] = "";
					$upd["address_1"] = "Text";
					$upd["delivery_address_1"] = "Text";
					$upd["si_total_line_discount"] = 0;
					$upd["si_total_line_tax"] = $this->selling_price_currency_format($result['TaxAmount']);
					$upd["si_sub_total"] = $this->selling_price_currency_format($result['ItemAmount']);
					$upd["si_footer_discount_%"] = 0;
					$upd["si_footer_discount_%_2"] = 0;
					$upd["si_footer_discount_%_3"] = 0;
					$upd["si_footer_discount_total"] = 0;
					$upd["si_footer_tax_code"] = "";
					$upd["si_total_footer_tax"] = "";
					$upd["si_rounding_adjustment"] = 0;
					$upd["si_grand_total"] = $this->selling_price_currency_format($result['TotalAmount']);
					$upd["item_code"] = "Text";
					$upd["item_description"] = "SALES";
					$upd["item_location_code"] = "DEFAULT";
					$upd["quantity"] = 1;
					$upd["uom_code"] = "Unit";
					$upd["price"] = $this->selling_price_currency_format($result2["TotalAmount"]);
					$upd["amount"] = $this->selling_price_currency_format($result2["TotalAmount"]);
					$upd["line_discount_amount"] = 0;
					$upd["line_tax_code"] = $result2['tax_code'];
					$upd["line_tax_amount"] = $this->selling_price_currency_format($result2['TaxAmount']);
					$upd["line_tax_gl_account_code"] = $this->accSettings['tax_gl_account']['account']['account_code'];
					$upd["line_total"] = $this->selling_price_currency_format($result2['TotalAmount']);
					$upd["line_sales_gl_account_code"] = $result2['account_code'];
					$upd["si_tax_type"] = 1;
					my_fputcsv($fp, $upd);
					unset($upd);
				}
				$bn++;
			}
			$tmpSalesDb->sql_freeresult($ret);

			fclose($fp);
		}
		return $total['total'];
	}

	function get_account_credit_note($tmpSalesDb,$groupBy,$dateTo,$branchCode)
	{
		global $LANG, $config;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeaderCN);
	
			$ret = $this->sql_query($tmpSalesDb, "select date, return_receipt_no, credit_note_no, currencyrate, currency_code, tax_code,
												customer_code, customer_name,batchno,account_code,account_name,terms,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												reason as remark,
												reason_code
												from ".$this->tmpTable."
												group by date, return_receipt_no, credit_note_no 
												order by date, return_receipt_no, credit_note_no");
			$bn = 1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				
				$accTerms = $this->accSettings['credit_term']['account'];
				$docNo = $cn['credit_note_no'];
				$SalesInvNo = $cn['return_receipt_no'];
				$cnDate = $this->set_date($this->ExportDateFormat,$cn['date']);
				$retItem = $this->sql_query($tmpSalesDb, "select date, return_receipt_no, credit_note_no, currencyrate, currency_code, tax_code,
												customer_code, customer_name,batchno,account_code,account_name,terms,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												reason as remark,
												reason_code
												from ".$this->tmpTable." where 
												date = ".ms($cn['date'])." 
												and return_receipt_no = ".ms($cn['return_receipt_no'])." 
												and credit_note_no = ".ms($cn['credit_note_no'])." 
												group by tax_code,reason 
												order by tax_code, reason");
				while($cnItem = $this->sql_fetchrow($tmpSalesDb, $retItem))
				{
					$upd['customer_code'] = $cn['customer_code'];
					$upd['customer_name'] = $cn['customer_name'];
					$upd['document_no'] = $docNo;
					$upd['document_date'] = $cnDate;
					$upd['currency_code'] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
					$upd['exchange_rate'] = $cn['currencyrate'];
					$upd['reference_number'] = $cn['batchno'];
					$upd['delivery_mode_code'] = "";
					$upd['agent_code'] = "";
					$upd['credit_term_code'] = ($cn["terms"]==""?$accTerms['account_code']:$cn['terms']);
					$upd['project_code'] = "";
					$upd['attention'] = "";
					$upd['address_1'] = "TEXT";
					$upd['address_2'] = "";
					$upd['address_3'] = "";
					$upd['address_4'] = "";
					$upd['city'] = "";
					$upd['postcode'] = "";
					$upd['state_code'] = "";
					$upd['country_code'] = "";
					$upd['delivery_address_-_attention'] = "";
					$upd['delivery_address_1'] = "TEXT";				
					$upd['scn_total_line_discount'] = $this->selling_price_currency_format(0);
					$upd['scn_total_line_tax'] = $this->selling_price_currency_format(abs($cn['TaxAmount']));
					$upd['scn_sub_total'] = $this->selling_price_currency_format(abs($cn['ItemAmount']));								
					$upd['scn_rounding_adjustment'] = "";
					$upd['scn_grand_total'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
					$upd['item_code'] = "TEXT";
					$upd['item_description'] = "TEXT";
					$upd['item_location_code'] = "DEFAULT";
					$upd['quantity'] = "1";
					$upd['uom_code'] = "UNIT";
					$upd['price'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
					$upd['amount'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
					$upd['line_discount_%'] = $this->selling_price_currency_format(0);
					$upd['line_discount_%_2'] = "";
					$upd['line_discount_%_3'] = "";
					$upd['line_discount_amount'] = $this->selling_price_currency_format(0);
					$upd['line_tax_code'] = $cnItem['tax_code'];
					$upd['line_tax_amount'] = $this->selling_price_currency_format(abs($cnItem['TaxAmount']));
					$upd['line_tax_gl_account_code'] = $this->accSettings['tax_gl_account']['account']['account_code'];
					$upd['line_tax_gl_account_name'] = $this->accSettings['tax_gl_account']['account']['account_code'];
					$upd['line_total'] = $this->selling_price_currency_format(abs($cnItem['TotalAmount']));
					$upd['line_sales_gl_account_code'] = $cn['account_code'];
					$upd['line_sales_gl_account_name'] = $cn['account_name'];	
					if(trim($cn['reason_code'])!="")
					{
						$upd['line_reason_code'] = $cn['reason_code'];
					}
					elseif(trim($cnItem['remark'])!="")
					{
						$upd['line_reason_code'] = substr(trim($cnItem['remark']),0,8);
					}
					else{
						$upd['line_reason_code'] = "FGRT";
					}
					
					$upd['sales_invoice_number'] = $SalesInvNo;
					$upd['line_discount_gl_account_code'] = "";
					$upd['line_discount_gl_account_name'] = "";
					my_fputcsv($fp, $upd);
					unset($upd);
				}
				$tmpSalesDb->sql_freeresult($cnItem);
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
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			my_fputcsv($fp, $this->ExportFileHeaderDN);

			$ret = $this->sql_query($tmpSalesDb, "select date, invoice_no, customer_code as vendor_code, customer_name as vendor_name,
													account_code, account_name,batchno,tax_code,
													currencyrate, currency_code, reason,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount											
													from ".$this->tmpTable."
													group by date, invoice_no, tax_code, reason
													order by date, invoice_no, tax_code");

			while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				$accTerms = $this->accSettings['credit_term']['account'];
				$docNo = $dn['invoice_no'];
				$dnDate = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["supplier_code"] = $dn["vendor_code"];		
				$upd["supplier_name"] = $dn["vendor_name"];		
				$upd["document_number"] = $docNo;
				$upd["document_date"] = $dnDate;
				$upd["currency_code"] = $dn['currency_code'];
				$upd["exchange_rate"] = (trim($db['currencyrate'])!=""?$dn['currencyrate']:1);
				$upd["reference_number"] = $dn['batchno'];		
				$upd['credit_term_code'] = $accTerms['account_code'];
				$upd["address_1"] = "TEXT";
				$upd["delivery_address_1"] = "TEXT";
				$upd["pdn_sub_total"] = $this->selling_price_currency_format($dn['ItemAmount']);
				$upd["pdn_total_line_discount"] = $this->selling_price_currency_format(0);
				$upd["pdn_total_line_tax"] = $this->selling_price_currency_format($dn['TaxAmount']);
				$upd["pdn_rounding_adjustment"] = "0";
				$upd["pdn_grand _total"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["item_code"] = "TEXT";
				$upd["item_description"] = "TEXT";
				$upd["item_location_code"] = "DEFAULT";
				$upd["quantity"] = "1";
				$upd["uom_code"] = "UNIT";
				$upd["price"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["amount"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["line_discount_%"] = "0";
				$upd["line_discount_amount"] = $this->selling_price_currency_format(0);
				$upd["line_tax_code"] = $dn['tax_code'];
				$upd["line_tax_amount"] = $this->selling_price_currency_format($dn['TaxAmount']);
				$upd["line_tax_gl_account_code"] = (!isset($this->accSettings['purchase_tax'])?"3050/000":$this->accSettings['purchase_tax']['account']['account_code']);
				$upd["line_tax_gl_account_name"] = (!isset($this->accSettings['purchase_tax'])?"Purchase Tax":$this->accSettings['purchase_tax']['account']['account_name']);
				$upd["line_total"] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd["line_purchase_gl_account_code"] = $dn['account_code'];
				$upd["line_purchase_gl_account_name"] = $dn['account_name'];
				$upd["line_reason_code"] = (trim($dn['reason'])==""?"WIDV":substr($dn['reason'],0,8));
				/*
				$upd['price'] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd['amount'] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd['line_discount_%'] = "";
				$upd['line_discount_%_2'] = "";
				$upd['line_discount_%_3'] = "";
				$upd['line_discount_amount'] = "";
				$upd['line_tax_code'] = $dn['tax_code'];
				$upd['line_tax_amount'] = $this->selling_price_currency_format($dn['TaxAmount']);
				$upd['line_tax_gl_account_code'] = "";
				$upd['line_tax_gl_account_name'] = "";
				$upd['line_total'] = $this->selling_price_currency_format($dn['TotalAmount']);
				$upd['line_sales_gl_account_code'] = $dn['account_code'];
				$upd['line_sales_gl_account_name'] = $dn['account_name'];
				$upd['line_project_code'] = "";
				$upd['line_analytical_code_1'] = "";
				$upd['line_analytical_code_2'] = "";
				$upd['line_analytical_code_3'] = "";
				$upd['line_analytical_code_4'] = "";
				$upd['line_analytical_code_5'] = "";
				$upd['line_user_defined_field_1'] = "";
				$upd['line_user_defined_field_2'] = "";
				$upd['line_reason_code'] = $dn['reason'];
				$upd['sales_invoice_number'] = "";
				$upd['line_discount_gl_account_code'] = "";
				$upd['line_discount_gl_account_name'] = "";*/
				my_fputcsv($fp, $upd);
				unset($upd);
			}
			$tmpSalesDb->sql_freeresult($ret);
			/*$ret = $this->sql_query($tmpSalesDb, "select receipt_no, currencyrate, foreign_currency_code,
											debit_note_no, pos_date as dn_date, cash_refund,
											return_date, goods_return_reason as remark,
											sum(ItemAmount) as ItemAmount,
											sum(TaxAmount) as TaxAmount,
											sum(TotalAmount) as TotalAmount
											from ".$this->tmpTable."
											group by remark, debit_note_no, dn_date, receipt_no, return_date
											order by dn_date,return_date");
			$bn = 1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$accTerms = $this->accSettings['credit_term']['account'];
				$docNo = $cn['debit_note_no'];
				$SalesInvNo = $cn['receipt_no'];
				$cnDate = $this->set_date($this->ExportDateFormat,$cn['dn_date']);
				$upd['customer_code'] = $cn['customer_code'];
				$upd['customer_name'] = $cn['customer_name'];
				$upd['document_no'] = $docNo;
				$upd['document_date'] = $cnDate;
				$upd['currency_code'] = ($cn['foreign_currency_code']!="XXX")?$cn['foreign_currency_code']:$config["arms_currency"]["code"];
				$upd['exchange_rate'] = $result2['currencyrate'];
				$upd['reference_number'] = "";
				$upd['delivery_mode_code'] = "";
				$upd['agent_code'] = "";
				$upd['credit_term_code'] = $accTerms['account_code'];
				$upd['project_code'] = "";
				$upd['attention'] = "";
				$upd['address_1'] = "";
				$upd['address_2'] = "";
				$upd['address_3'] = "";
				$upd['address_4'] = "";
				$upd['city'] = "";
				$upd['postcode'] = "";
				$upd['state_code'] = "";
				$upd['country_code'] = "";
				$upd['delivery_address_-_attention'] = "";
				$upd['delivery_address_1'] = "";
				$upd['delivery_address_2'] = "";
				$upd['delivery_address_3'] = "";
				$upd['delivery_address_4'] = "";
				$upd['delivery_address_-_city'] = "";
				$upd['delivery_address_-_postcode'] = "";
				$upd['delivery_address_-_state_code'] = "";
				$upd['delivery_address_-_country_code'] = "";
				$upd['user_defined_field_1'] = "";
				$upd['user_defined_field_2'] = "";
				$upd['user_defined_field_3'] = "";
				$upd['user_defined_field_4'] = "";
				$upd['user_defined_field_5'] = "";
				$upd['user_defined_field_6'] = "";
				$upd['user_defined_field_7'] = "";
				$upd['user_defined_field_8'] = "";
				$upd['analytical_code_1'] = "";
				$upd['analytical_code_2'] = "";
				$upd['analytical_code_3'] = "";
				$upd['analytical_code_4'] = "";
				$upd['analytical_code_5'] = "";
				$upd['scn_total_line_discount'] = $this->selling_price_currency_format(0);
				$upd['scn_total_line_tax'] = $this->selling_price_currency_format(abs($cn['TaxAmount']));
				$upd['scn_sub_total'] = $this->selling_price_currency_format(abs($cn['ItemAmount']));
				$upd['scn_footer_discount_%'] = "";
				$upd['scn_footer_discount_%_2'] = "";
				$upd['scn_footer_discount_%_3'] = "";
				$upd['scn_total_footer_discount'] = "";
				$upd['scn_footer_tax_code'] = "";
				$upd['scn_total_footer_tax'] = "";
				$upd['scn_rounding_adjustment'] = "";
				$upd['scn_grand_total'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd['item_code'] = "TEXT";
				$upd['item_description'] = "TEXT";
				$upd['item_location_code'] = "";
				$upd['quantity'] = "1";
				$upd['uom_code'] = "UNIT";
				$upd['price'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd['amount'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd['line_discount_%'] = "";
				$upd['line_discount_%_2'] = "";
				$upd['line_discount_%_3'] = "";
				$upd['line_discount_amount'] = "";
				$upd['line_tax_code'] = $cn['tax_code'];
				$upd['line_tax_amount'] = $this->selling_price_currency_format(abs($cn['TaxAmount']));
				$upd['line_tax_gl_account_code'] = "";
				$upd['line_tax_gl_account_name'] = "";
				$upd['line_total'] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd['line_sales_gl_account_code'] = $cn['account_code'];
				$upd['line_sales_gl_account_name'] = $cn['account_name'];
				$upd['line_project_code'] = "";
				$upd['line_analytical_code_1'] = "";
				$upd['line_analytical_code_2'] = "";
				$upd['line_analytical_code_3'] = "";
				$upd['line_analytical_code_4'] = "";
				$upd['line_analytical_code_5'] = "";
				$upd['line_user_defined_field_1'] = "";
				$upd['line_user_defined_field_2'] = "";
				$upd['line_reason_code'] = $cn['remark'];
				$upd['sales_invoice_number'] = $SalesInvNo;
				$upd['line_discount_gl_account_code'] = "";
				$upd['line_discount_gl_account_name'] = "";
				my_fputcsv($fp, $upd);
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
		/*already inside cash sales*/
	}

	/*function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		//todo
	}*/
}
?>
