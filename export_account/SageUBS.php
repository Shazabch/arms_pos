<?php 
/*
2016-03-08 05:30pm Kee Kee
- Cash Sales cannot contain goods return amount

2017-01-04 14:07PM Kee Kee
- Added "$branchCode" into function which assign in ExportModule.php
*/
include_once("ExportModule.php");
class SageUBS extends ExportModule
{
	const NAME="SageUBS";
	static $export_with_sdk=false;
	static $show_all_column=false;
	static $groupby=array("Daily Summary","Monthly Summary","Receipt");
	
	var $tvExportCol = array();
	var $accSettings = array();
	var $ExportFileHeader = array();
	var $ScreenCol = array("ENTRY" => 0,						
							"ACC_CODE" => 0,
							"ACCNO" => 0,
							"FPERIOD" => 0,
							"DATE" => 0,
							"BATCHNO" => 0,
							"TRANNO" => 0,
							"VOUC_SEQ" => 0,							
							"TTYPE" => 0,
							"REFERENCE" => 0,
							"REFNO" => 0,					
							"DESP" => 0,
							"DESPA"=> 0,
							"TAXPEC" => 1,
							"DEBITAMT" => 1,
							"CREDITAMT" => 1,							
							"FCAMT"=>1,
							"EXC_RATE"=>1,
							"REM1" => 0,
							"REM4" => 0,
							"STRAN" => 0,
							"TAXPUR" => 1,
							"TRDATETIME" => 1,
							"TAXINCL" => 0,
							"ITEMNO" => 0,
							"ITAXTYPE" => 0,
							"TYPE"=>0);	
							
	var $ExportFileDBHeader = array(
		array("ENTRY","C",8),	
		array("ACC_CODE","C",15),	
		array("ACCNO","C",12),	
		array("FPERIOD","N",2,0),	
		array("DATE","D",8,0),	
		array("BATCHNO","N",4,0),	
		array("TRANNO","N",4,0),	
		array("VOUC_SEQ","N",4,0),
		array("VOUC_SEQ_2","N",4,0),
		array("TTYPE","C",2),	
		array("REFERENCE","C",10),	
		array("REFNO","C",10),	
		array("DESP","C",40),		
		array("DESPA","C",40),	
		array("DESPB","C",40),	
		array("DESPC","C",40),	
		array("DESPD","C",40),	
		array("DESPE","C",40),	
		array("DESPF","C",40),	
		array("TAXPEC","N",5,2),	
		array("DEBITAMT","N",17,2),	
		array("CREDITAMT","N",17,2),	
		array("FCAMT","N",17,2),	
		array("DEBIT_FC","N",17,2),	
		array("CREDIT_FC","N",17,2),	
		array("EXC_RATE","N",18,10),
		array("ARAPTYPE","C",1),	
		array("AGE","N",2,0),	
		array("SOURCE","C",4),	
		array("JOB","C",4),	
		array("JOB2","C",4),
		array("SUBJOB","C",4),
		array("JOB_VALUE","N",19,2),	
		array("JOB2_VALUE","N",19,2),	
		array("POSTED","C",1),	
		array("EXPORTED","C",1),
		array("EXPORTED1","C",1),	
		array("EXPORTED2","C",1),	
		array("EXPORTED3","C",1),	
		array("REM1","C",4),	
		array("REM2","C",100),	
		array("REM3","C",100),
		array("REM4","C",8),	
		array("REM5","N",4,0),	
		array("RPT_ROW","N",4,0),	
		array("AGENT","C",12),	
		array("SITE","C",12),	
		array("STRAN","C",8),
		array("TAXPUR","N",17,2),	
		array("PAYMODE","C",4),	
		array("TRDATETIME","C",30),
		array("CORR_ACC","C",12),	
		array("ACCNO2","C",12),	
		array("ACCNO3","C",12),	
		array("DATE2","D"),	
		array("USERID","C",8),
		array("TCURRCODE","C",4),	
		array("TCURRAMT","N",19,2),	
		array("ISSUDATE","D"),	
		array("BPERIOD","N",2,0),	
		array("BDATE","D"),	
		array("VPERIOD","N",2,0),	
		array("ORIGIN","C",12),	
		array("MPERIOD","N",2,0),
		array("TMBATCHNO","C",10),		
		array("TMDATE","C",8),
		array("TMSTATUS","C",100),	
		array("TMCHK","N","1",0),	
		array("PERMITNO","C",20),
		array("CREATED_BY","C",8),	
		array("UPDATED_BY","C",8),	
		array("CREATED_ON","C",8),
		array("UPDATED_ON","C",8),
		array("COMPNAME","C",40),	
		array("CMPUEN","C",25),	
		array("CMPINNO","C",25),
		array("CMPINDTE","D"),	
		array("CMPTXAT","N",17,2),
		array("CMPUNBL","N",17,2),
		array("CMPPERN","C",20),	
		array("BVCODE","C",8),	
		array("TCODE","C",8),	
		array("NOTINUSE","L"),	
		array("RECPTTYPE","N",1,0),	
		array("SALESTYPE","N",1,0),
		array("INVTOISS","C",12),	
		array("TAXINCL","L"),	
		array("ITEMNO","C",24),	
		array("MAJORIND","C",10),	
		array("ITAXTYPE","C",10),	
		array("TYPE","C",4),
		array("REVERSAL","C",1),	
		array("GSTREGNO","C",40),	
		array("SUBMITTED","L"),	
		array("FINAL","L")
	);

	var $ExportFileName = array(
        "cs" => "%s/sageUBS_glpost9_cs_%s.dbf",
        "ap" => "%s/sageUBS_glpost9_ap_%s.dbf",
				"ar" => "%s/sageUBS_glpost9_ar_%s.dbf",
				"cn" => "%s/sageUBS_glpost9_cn_%s.dbf",
				"dn" => "%s/sageUBS_glpost9_dn_%s.dbf",

    );
	var $ExportDateFormat = "Ymd";
	var $ExportDateTimeFormat = "d/m/y H:i:s";

	static function get_name() {
        return self::NAME;
  }

	static function get_property($sys='lite'){
		global $config;

		if($sys=='lite')
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>false,'cn'=>true);
		else
			$dataType=array('cs'=>true,'ap'=>true,'ar'=>true,'cn'=>true);

		if($config['consignment_modules']){
			$dataType['cs']=false;
			//$dataType['dn']=true;
		}

    return array(
			"module_name" => __CLASS__,
			"export_with_sdk"=>self::$export_with_sdk,
			"show_all_column"=>self::$show_all_column,
			"settings" => array (				
				'sales' => array (
					"name"=>"Sales",
					"account"=> array (
						'account_code' => '5000/001',
						'account_name' => 'Cash Sales',
					)
				),
				'sales_return' => array (
					"name"=>"Sales Return",
					"account"=> array (
						'account_code' => '5005/000',
						'account_name' => 'Sales Return',
					)
				),
				'credit_sales' => array (
					"name"=>"Credit Sales",
					"account"=> array (
						'account_code' => '5000/000',
						'account_name' => 'Sales',
					)
				),
				'customer_code'=>array(
					"name"=>"Customer Code",
					"account"=>array(
						'account_code'=> '3000/C01',
						'account_name'=> 'Cash',
					),
					"help"=>"For POS Cash Sales only"
				),
				'cash' => array (
					"name"=>"Cash",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Cash In Hand',
					)
				),
				'credit_card' => array (
					"name"=>"Credit Card",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Credit Card',
					)
				),
				'coupon' => array (
					"name"=>"Coupon",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Cash In Hand',
					)
				),
				'voucher' => array(
					"name"=>"Voucher",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Cash In Hand',
					)
				),
				'check' => array (
					"name"=>"Check",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Cash In Hand',
					)
				),
				'deposit' => array (
					"name"=>"Deposit",
					"account"=>array(
						'account_code' => '3040/000',
						'account_name' => 'Cash In Hand',
					)
				),
				'rounding' => array (
					"name"=>"Rounding",
					"account"=>array(
						'account_code' => '3040/001',
						'account_name' => 'Rounding',
					),
				),
				'service_charge' => array (
					"name"=>"Service Charge",
					"account"=>array(
						'account_code' => '5000/001',
						'account_name' => 'Cash Sales',
					)
				),
				'short' => array (
					"name"=>"Short",
					"account"=>array(
						'account_code' => '3040/002',
						'account_name' => 'Short',
					)
				),
				'over' => array (
					"name"=>"Over",
					"account"=>array(
						'account_code' => '3040/003',
						'account_name' => 'Over',
					)
				),
				'SR' => array (
					"name"=>"SR @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4800/020',
						'account_name' => 'SR @6%',
					)
				),
				'ZRL' => array (
					"name"=>"ZRL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'ZRL @0%',
					)
				),
				'ZRE' => array (
					"name"=>"ZRE @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'ZRE @0%',
					)
				),
				'ES43' => array (
					"name"=> "ES43 @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'ES43 @0%',
					)
				),
				'DS' => array (
					"name"=>"DS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'DS @6%',
					)
				),
				'OS' => array (
					"name"=>"OS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'OS @0%',
					)
				),
				'ES' => array (
					"name"=>"ES @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4800/020',
						'account_name' => 'ES @0%',
					)
				),
				'RS' => array (
					"name"=>"RS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'RS @0%',
					),
				),
				'GS' =>  array (
					"name"=>"GS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'GS @0%',
					)
				),
				'AJS' => array (
					"name"=>"AJS @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/020',
						'account_name' => 'AJS @6%',
					)
				),
				'AJP' => array (
					"name"=>"AJP @6%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4800/010',
						'account_name' => 'AJP @6%',
					)
				),
				'BL' => array (
					"name"=>"BL @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'BL @0%',
					)
				),
				'EP' => array (
					"name"=>"EP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'EP @0%',
					)
				),
				'GP' => array (
					"name"=> "GP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'GP @0%',
					)
				),
				'IM' => array (
					"name"=>"IM @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'IM @6%',
					)
				),
				'IS' => array (
					"name"=>"IS @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'IS @0%',
					)
				),
				'NR' => array (
					"name"=>"NR @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4800/010',
						'account_name' => 'NR @0%',
					)
				),
				'OP' => array (
					"name"=>"OP @0%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'OP @0%',
					),
				),
				'TX' =>  array (
					"name"=>"TX @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'TX @6%',
					)
				),
				'TX-E43' => array (
					"name"=>"TX-E43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'TX-E43 @6%',
					)
				),
				'TX-N43' => array (
					"name"=>"TX-N43 @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'TX-N43 @6%',
					)
				),
				'TX-RE' => array (
					"name"=>"TX-RE @6%",
					"gst"=>true,
					"account"=>array(
						'account_code' => '4800/010',
						'account_name' => 'TX-RE @6%',
					)
				),
				'financial_date'=>array(
					"name"=>"Financial Date",
					"date"=>"2015-01-01"
				),
				'NR' => array (
					"name"=>"NR @0%",
					"gst"=>true,
					"account"=> array(
						'account_code' => '4800/010',
						'account_name' => 'NR @0%',
					)
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
			case 'ap':					
			case 'dn':
			case 'cn':
					if($this->sys=='lits')					
						$this->tvExportCol = array_merge($this->ScreenCol,array("dummy"=>0));
					else
						$this->tvExportCol = $this->ScreenCol;
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
											where `type`='debit' and ((TotalAmount > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
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
					unset($result, $posDate);
					$tmpSalesDb->sql_freeresult($ret);					
					$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, acc_type, account_code, account_name,
											tax_code,taxRate, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and ((qty > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").") 													
											and (tablename='pos' or tablename='membership')
											group by pos_date, batchno, account_code, account_name, tax_code,taxRate");

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
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] += $result['TaxAmount'];
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
												group by pos_date, doc_no, acc_type");
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
						unset($result, $posDate);
						$tmpSalesDb->sql_freeresult($ret);

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, acc_type, account_code, account_name,
												tax_code, tax_account_code, tax_account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='credit'
												and tablename='do'
												group by pos_date, doc_no, account_code, account_name, tax_code");
						while($result = $this->sql_fetchrow($tmpSalesDb, $ret)){
							$posDate=$result['pos_date'];
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
						$tmpSalesDb->sql_freeresult($ret);
					}

					$bn = 1;
					$knockoff = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail)
						{
							$batchNo = $branch_id;
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{		
										$upd["ACCNO"] = $acc_code;
										$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
										$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
										$upd["VOUC_SEQ"] = sprintf("%d",$bn);										
										$upd["REFERENCE"] = $batchNo;
										$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
										$upd["DESP"] = $acc_name;										
										$upd["DEBITAMT"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["CREDITAMT"] = "0";									
										$upd["AGE"] = "0";
										$upd["TAXPUR"] = "0";
										$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
										$upd["TAXINCL"] = "FALSE";
										$upd["ITEMNO"] = "SALES";
										$upd["TYPE"] = "CS";										
										$this->insert_cash_sales($fp,$upd);
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
										if(is_array($value2))
										{
											foreach($value2 as $tax_name=>$val)
											{											
												$upd["ACCNO"] = $acc_code;
												$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
												$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["VOUC_SEQ"] = sprintf("%d",$bn);
												$upd["REFERENCE"] = $batchNo;
												$upd["REFNO"] = "POS-".sprintf("%04s",$bn);
												$upd["DESP"] = $acc_name;
												$upd["TAXPEC"] = intval($taxRate);
												$upd["DEBITAMT"] = "0";
												$upd["CREDITAMT"] = $this->selling_price_currency_format($val);
												$upd["AGE"] = "0";
												$upd["REM4"] = $tax_name;
												$upd["TAXPUR"] = $this->selling_price_currency_format(0-$val);
												$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
												$upd["TAXINCL"] = "FALSE";
												$upd["ITEMNO"] = "SALES";
												$upd["TYPE"] = "CS";
												$this->insert_cash_sales($fp,$upd);
												unset($upd,$tax_name);
												$knockoff++;
											}
										}
										else{
											if($tax_name=='other') $tax_name="";
											$upd["ACCNO"] = $acc_code;
											$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
											$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["VOUC_SEQ"] = sprintf("%d",$bn);
											$upd["REFERENCE"] = $batchNo;
											$upd["REFNO"] = "POS-".sprintf("%04s",$bn);
											$upd["DESP"] = $acc_name;
											$upd["DESPA"] = $tax_name;
											$upd["DEBITAMT"] = "0";
											$upd["CREDITAMT"] = $this->selling_price_currency_format($value2);
											$upd["AGE"] = "0";
											$upd["TAXPUR"] = "0";
											$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
											$upd["TAXINCL"] = "FALSE";
											$upd["ITEMNO"] = "SALES";
											$upd["TYPE"] = "CS";
											$this->insert_cash_sales($fp,$upd);
											unset($upd);
											$knockoff++;
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
												$upd["ACCNO"] = $acc_code;
												$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
												$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["VOUC_SEQ"] = sprintf("%d",$bn);
												$upd["REFERENCE"] = $batchNo;
												$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
												$upd["DESP"] = $acc_name;
												$upd["DEBITAMT"] = $this->selling_price_currency_format($val['debitamt']);
												$upd["CREDITAMT"] = "0";									
												$upd["AGE"] = "0";
												$upd["TAXPUR"] = "0";
												$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
												$upd["TAXINCL"] = "FALSE";
												$upd["ITEMNO"] = "SALES";												
												$upd["TYPE"] = "CS";
												$this->insert_cash_sales($fp,$upd);
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
														
														$upd["ACCNO"] = $acc_code;
														$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
														$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["VOUC_SEQ"] = sprintf("%d",$bn);
														$upd["REFERENCE"] = $batchNo;
														$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
														$upd["DESP"] = $acc_name;
														$upd["DEBITAMT"] = "0";
														$upd["CREDITAMT"] = $this->selling_price_currency_format($val);
														$upd["AGE"] = "0";
														$upd["TAXINCL"] = "FALSE";									
														$upd["ITEMNO"] = "SALES";													
														$upd["TYPE"] = "CS";																				
														$this->insert_cash_sales($fp,$upd);
														unset($upd,$tax_name);
														$knockoff++;
													}
												}
												else{
													$upd["ACCNO"] = $acc_code;
													$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
													$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["VOUC_SEQ"] = sprintf("%d",$bn);
													$upd["REFERENCE"] = $batchNo;
													$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
													$upd["DESP"] = $acc_name;
													$upd["DEBITAMT"] = "0";
													$upd["CREDITAMT"] = $this->selling_price_currency_format($value2);									
													$upd["AGE"] = "0";
													$upd["TAXINCL"] = "FALSE";									
													$upd["ITEMNO"] = "SALES";													
													$upd["TYPE"] = "CS";													
													$this->insert_cash_sales($fp,$upd);
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
											where `type`='debit' and ((TotalAmount > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
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
					unset($result, $posDate);
					$tmpSalesDb->sql_freeresult($ret);

					$ret = $this->sql_query($tmpSalesDb, "select ym, batchno, acc_type, account_code, account_name,
											tax_code,taxRate, tax_account_code, tax_account_name,
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='credit' and ((qty > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").") 
											and (tablename='pos' or tablename='membership')
											group by ym, batchno, account_code, account_name, tax_code,taxRate");

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
								$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$branch_id]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] += $result['TaxAmount'];
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
												group by pos_date, doc_no, acc_type");
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
						unset($result, $posDate);
						$tmpSalesDb->sql_freeresult($ret);

						$ret = $this->sql_query($tmpSalesDb, "select pos_date, batchno, doc_no, acc_type, account_code, account_name,
												tax_code, tax_account_code, tax_account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type`='credit'
												and tablename='do'
												group by pos_date, doc_no, account_code, account_name, tax_code");
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
					}

					$bn = 1;
					$knockoff = 1;
					foreach($tmpSalesDetail as $posDate=>$branches)
					{
						foreach($branches as $branch_id=>$salesDetail){
							$date =  $this->monthly_summary_date_checking($posDate,$dateTo);
							$batchNo = $branch_id;
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{		
										$upd["ACCNO"] = $acc_code;
										$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$date);
										$upd["DATE"] = $this->set_date($this->ExportDateFormat,$date);									
										$upd["VOUC_SEQ"] = sprintf("%d",$bn);
										$upd["REFERENCE"] = $batchNo;
										$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
										$upd["DESP"] = $acc_name;
										$upd["DEBITAMT"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["CREDITAMT"] = "0";									
										$upd["AGE"] = "0";
										$upd["TAXPUR"] = "0";										
										$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);										
										$upd["TAXINCL"] = "FALSE";									
										$upd["ITEMNO"] = "SALES";										
										$upd["TYPE"] = "CS";
										$this->insert_cash_sales($fp,$upd);
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
										if(is_array($value2))
										{										
											foreach($value2 as $tax_name=>$val)
											{
												$upd["ACCNO"] = $acc_code;
												$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$date);
												$upd["DATE"] = $this->set_date($this->ExportDateFormat,$date);
												$upd["VOUC_SEQ"] = sprintf("%d",$bn);
												$upd["REFERENCE"] = $batchNo;
												$upd["REFNO"] = "POS-".sprintf("%04s",$bn);
												$upd["DESP"] = $acc_name;
												$upd["TAXPEC"] = intval($taxRate);
												$upd["DEBITAMT"] = "0";
												$upd["CREDITAMT"] = $this->selling_price_currency_format($val);
												$upd["AGE"] = "0";
												$upd["REM4"] = $tax_name;
												$upd["TAXPUR"] = $this->selling_price_currency_format(0-$val);
												$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
												$upd["TAXINCL"] = "FALSE";
												$upd["ITEMNO"] = "SALES";
												$upd["TYPE"] = "CS";
												$this->insert_cash_sales($fp,$upd);
												unset($upd,$tax_name);
												$knockoff++;
											}
										}
										else{
											if($tax_name=='other') $tax_name="";
											$upd["ACCNO"] = $acc_code;
											$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$date);
											$upd["DATE"] = $this->set_date($this->ExportDateFormat,$date);
											$upd["VOUC_SEQ"] = sprintf("%d",$bn);
											$upd["REFERENCE"] = $batchNo;
											$upd["REFNO"] = "POS-".sprintf("%04s",$bn);
											$upd["DESP"] = $acc_name;
											$upd["DESPA"] = $tax_name;
											$upd["DEBITAMT"] = "0";
											$upd["CREDITAMT"] = $this->selling_price_currency_format($value2);
											$upd["AGE"] = "0";
											$upd["TAXPUR"] = "0";
											$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$date);
											$upd["TAXINCL"] = "FALSE";
											$upd["ITEMNO"] = "SALES";
											$upd["TYPE"] = "CS";
											$this->insert_cash_sales($fp,$upd);
											unset($upd);
											$knockoff++;
										}
									}
								}							
							}
							
							if(isset($salesDetail['do']))
							{
								foreach($salesDetail['do'] as $receipt_no=>$salesDetail_do)
								{
									foreach($salesDetail_do['payment'] as $acc_code=>$value)
									{
										if(is_array($value))
										{
											foreach($value as $acc_name=>$val)
											{	
												$upd["ACCNO"] = $acc_code;
												$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
												$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["VOUC_SEQ"] = sprintf("%d",$bn);
												$upd["REFERENCE"] = $batchNo;
												$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
												$upd["DESP"] = $acc_name;
												$upd["DEBITAMT"] = $this->selling_price_currency_format($val['debitamt']);
												$upd["CREDITAMT"] = "0";									
												$upd["AGE"] = "0";
												$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
												$upd["TAXINCL"] = "FALSE";
												$upd["ITEMNO"] = "SALES";												
												$upd["TYPE"] = "CS";																											
												$this->insert_cash_sales($fp,$upd);
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
														$upd["ACCNO"] = $acc_code;
														$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
														$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
														$upd["VOUC_SEQ"] = sprintf("%d",$bn);
														$upd["REFERENCE"] = $batchNo;
														$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
														$upd["DESP"] = $acc_name;
														$upd["DESPA"] = $tax_name;
														$upd["DEBITAMT"] = "0";
														$upd["CREDITAMT"] = $this->selling_price_currency_format($val);									
														$upd["AGE"] = "0";
														$upd["TAXINCL"] = "FALSE";									
														$upd["ITEMNO"] = "SALES";														
														$upd["TYPE"] = "CS";														
														$this->insert_cash_sales($fp,$upd);
														unset($upd,$tax_name);
														$knockoff++;
													}
												}
												else{												
													$upd["ACCNO"] = $acc_code;
													$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
													$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
													$upd["VOUC_SEQ"] = sprintf("%d",$bn);
													$upd["REFERENCE"] = $batchNo;
													$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
													$upd["DESP"] = $acc_name;
													$upd["DEBITAMT"] = "0";
													$upd["CREDITAMT"] = $this->selling_price_currency_format($value2);									
													$upd["AGE"] = "0";
													$upd["TAXINCL"] = "FALSE";
													$upd["ITEMNO"] = "SALES";													
													$upd["TYPE"] = "CS";													
													$this->insert_cash_sales($fp,$upd);
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
					unset($tmpSalesDetail, $bn, $knockoff);
				break;
				case 'receipt':
					$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id, doc_no, ref_no, acc_type, account_code, account_name,
											round(sum(TotalAmount),2) as TotalAmount
											from ".$this->tmpTable."
											where `type`='debit' and ((TotalAmount > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
											group by tablename, ref_no, acc_type
											order by pos_date, ref_no");

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
											where `type`='credit' and ((qty > 0 and acc_type!=".ms("Rounding").") or acc_type=".ms("Rounding").")
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
								$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] = 0;
							}

							$tmpSalesDetail[$posDate][$receipt_no]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']] += $result['TaxAmount'];
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
							foreach($salesDetail['payment'] as $acc_code=>$value)
							{
								if(is_array($value))
								{
									foreach($value as $acc_name=>$val)
									{
										$upd["ACCNO"] = $acc_code;
										$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
										$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);									
										$upd["VOUC_SEQ"] = sprintf("%d",$bn);
										$upd["REFERENCE"] = $salesDetail['doc_no'];
										$upd["REFNO"] = $receipt_no;				
										$upd["DESP"] = $acc_name;										
										$upd["DEBITAMT"] = $this->selling_price_currency_format($val['debitamt']);
										$upd["CREDITAMT"] = "0";
										$upd["AGE"] = "0";
										$upd["TAXPUR"] = "0";										
										$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
										$upd["TAXINCL"] = "FALSE";
										$upd["ITEMNO"] = "SALES";										
										$upd["TYPE"] = "CS";																		
										$this->insert_cash_sales($fp,$upd);
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
										if(is_array($value2))
										{										
											foreach($value2 as $tax_name=>$val)
											{											

												$upd["ACCNO"] = $acc_code;
												$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
												$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
												$upd["VOUC_SEQ"] = sprintf("%d",$bn);
												$upd["REFERENCE"] = $salesDetail['doc_no'];
												$upd["REFNO"] = $receipt_no;
												$upd["DESP"] = $acc_name;
												$upd["TAXPEC"] = intval($taxRate);
												$upd["DEBITAMT"] = "0";
												$upd["CREDITAMT"] = $this->selling_price_currency_format($val);
												$upd["AGE"] = "0";
												$upd["REM4"] = $tax_name;
												$upd["TAXPUR"] = $this->selling_price_currency_format(0-$val);
												$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
												$upd["TAXINCL"] = "FALSE";
												$upd["ITEMNO"] = "SALES";
												$upd["TYPE"] = "CS";
												$this->insert_cash_sales($fp,$upd);
												unset($upd,$tax_name);
												$knockoff++;

											}
										}
										else{
											if($tax_name=='other') $tax_name="";
											$upd["ACCNO"] = $acc_code;
											$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$posDate);
											$upd["DATE"] = $this->set_date($this->ExportDateFormat,$posDate);
											$upd["VOUC_SEQ"] = sprintf("%d",$bn);
											$upd["REFERENCE"] = $salesDetail['doc_no'];
											$upd["REFNO"] = $receipt_no;
											$upd["DESP"] = $acc_name;
											$upd["DESPA"] = $tax_name;
											$upd["DEBITAMT"] = "0";
											$upd["CREDITAMT"] = $this->selling_price_currency_format($value2);
											$upd["AGE"] = "0";
											$upd["TAXPUR"] = "0";
											$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$posDate);
											$upd["TAXINCL"] = "FALSE";
											$upd["ITEMNO"] = "SALES";
											$upd["TYPE"] = "CS";
											$this->insert_cash_sales($fp,$upd);
											unset($upd,$tax_name);
											$knockoff++;
										}
									}
								}	
							}
							$bn++;
						}
					}
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	private function insert_cash_sales($fp,$data){
		$upd=array();
		foreach($this->ExportFileDBHeader as $header){
			$upd[$header[0]]=(isset($data[$header[0]]))?$data[$header[0]]:"";
		}
		my_fputcsv($fp, $upd);
		unset($upd);
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
				$batchNo = $result['inv_no'];
				$InvDate = $this->set_date($this->ExportDateFormat,$result['inv_date']);

				$sql="select round(sum(TaxAmount),2) as TaxAmount, taxCode, taxRate,tax_account_code, tax_account_name
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
							$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['tax_code'] = $result2['taxCode'];
							$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['tax_rate'] = $result2['taxRate'];
							$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['TaxAmount'] = 0;
						}
						$inputTaxValue[$result2['tax_account_code']][$result2['tax_account_name']]['TaxAmount']+=$result2['TaxAmount'];
					}
				}else{
					$taxdesc = "";
				}
				$tmpSalesDb->sql_freeresult($ret2);

				$upd["ACCNO"] = $result['vendor_code'];
				$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$result['inv_date']);
				$upd["DATE"] = $InvDate;									
				$upd["VOUC_SEQ"] = sprintf("%d",$bn);
				$upd["REFERENCE"] = $result['batchno'];
				$upd["REFNO"] = $batchNo;				
				$upd["DESP"] = $result['vendor_name'];;										
				$upd["TAXPEC"] = "0";
				$upd["DEBITAMT"] = $this->selling_price_currency_format($result['TotalAmount']);
				$upd["CREDITAMT"] = "0";
				$upd["AGE"] = "0";
				$upd["TAXPUR"] = "0";										
				$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$result['inv_date']);
				$upd["TAXINCL"] = "FALSE";
				$upd["ITEMNO"] = "PURCHASE";										
				$upd["TYPE"] = "RV";
				$this->insert_cash_sales($fp,$upd);
				unset($upd);
				$knockoff++;					
			
				if(isset($inputTaxValue))
				{
					foreach($inputTaxValue as $taxAccCode=>$val)
					{
						foreach($val as $taxAccName=>$val2)
						{
							$upd["ACCNO"] = $taxAccCode;
							$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$result['inv_date']);
							$upd["DATE"] = $InvDate;
							$upd["VOUC_SEQ"] = sprintf("%d",$bn);
							$upd["REFERENCE"] = $result['batchno'];;
							$upd["REFNO"] = $batchNo;										
							$upd["DESP"] = $taxAccName;														
							$upd["TAXPEC"] = intval($val2['tax_rate']);
							$upd["DEBITAMT"] = "0";
							$upd["CREDITAMT"] = $this->selling_price_currency_format($val2['TaxAmount']);									
							$upd["AGE"] = "0";
							$upd["REM4"] = $val2['tax_code'];														
							$upd["TAXPUR"] = $this->selling_price_currency_format(0-$val2['TaxAmount']);
							$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$result['inv_date']);														
							$upd["TAXINCL"] = "FALSE";									
							$upd["ITEMNO"] = "PURCHASE";														
							$upd["TYPE"] = "RV";																							
							$this->insert_cash_sales($fp,$upd);
							unset($upd);
							$knockoff++;														
						}
					}
					unset($inputTaxValue);
				}
				
				$upd["ACCNO"] = $result['vendor_code'];
				$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$result['inv_date']);
				$upd["DATE"] = $InvDate;									
				$upd["VOUC_SEQ"] = sprintf("%d",$bn);
				$upd["REFERENCE"] = $result['batchno'];;
				$upd["REFNO"] = $batchNo;				
				$upd["DESP"] = $result['vendor_name'];
				$upd["TAXPEC"] = "0";
				$upd["DEBITAMT"] = "0";
				$upd["CREDITAMT"] = $this->selling_price_currency_format($result['ItemAmount']);
				$upd["AGE"] = "0";
				$upd["TAXPUR"] = "0";										
				$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$result['inv_date']);
				$upd["TAXINCL"] = "FALSE";									
				$upd["ITEMNO"] = "PURCHASE";										
				$upd["TYPE"] = "RV";																		
				$this->insert_cash_sales($fp,$upd);
				unset($upd);
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
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');

			$ret = $this->sql_query($tmpSalesDb, "select do_date,batchno, inv_no, customer_code, customer_name, account_code, account_name,
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
					$tmpSalesDetail[$invNo]['do_date']=$result['do_date'];
					$tmpSalesDetail[$invNo]['batchno']=$result['batchno'];
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
				$batchNo = $salesDetail['batchno'];

				if(isset($salesDetail['payment'])){					
					$upd["ACCNO"] = $salesDetail['payment']['account_code'];
					$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$doDate);
					$upd["DATE"] = $this->set_date($this->ExportDateFormat,$doDate);
					$upd["VOUC_SEQ"] = sprintf("%d",$bn);										
					$upd["REFERENCE"] = $batchNo;
					$upd["REFNO"] = "POS-".sprintf("%04s",$bn);									
					$upd["DESP"] = $salesDetail['payment']['account_name'];
					$upd["DEBITAMT"] = $this->selling_price_currency_format($salesDetail['payment']['debitamt']);
					$upd["CREDITAMT"] = "0";									
					$upd["AGE"] = "0";
					$upd["TAXPUR"] = "0";
					$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$doDate);
					$upd["TAXINCL"] = "FALSE";
					$upd["ITEMNO"] = "SALES";
					$upd["TYPE"] = "AR";
					$this->insert_cash_sales($fp,$upd);
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

								$upd["ACCNO"] = $acc_code;
								$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$doDate);
								$upd["DATE"] = $this->set_date($this->ExportDateFormat,$doDate);
								$upd["VOUC_SEQ"] = sprintf("%d",$bn);
								$upd["REFERENCE"] = $batchNo;
								$upd["REFNO"] = $invNo;									
								$upd["DESP"] = $acc_name;
								$upd["DESPA"] = $tax_name;													
								$upd["DEBITAMT"] = "0";
								$upd["CREDITAMT"] = $this->selling_price_currency_format($val['creditamt']);									
								$upd["AGE"] = "0";
								$upd["TAXPUR"] = "0";													
								$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$doDate);
								$upd["TAXINCL"] = "FALSE";									
								$upd["ITEMNO"] = "SALES";													
								$upd["TYPE"] = "AR";																						
								$this->insert_cash_sales($fp,$upd);
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

			$ret = $this->sql_query($tmpSalesDb, "select return_receipt_no, credit_note_no, date, currencyrate,
											account_code, account_name, reason,
											sum(TotalAmount) as TotalAmount,
											sum(TotalFAmount) as TotalFAmount
											from ".$this->tmpTable."
											group by date, return_receipt_no, credit_note_no
											order by date, return_receipt_no, credit_note_no");
			
			$bn=1;
			while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{				
				$refno = $cn['credit_note_no'];
				$refno1 = $cn['return_receipt_no'];
				$upd["ACCNO"] = $cn['account_code'];
				$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$cn['date']);
				$upd["DATE"] = $this->set_date($this->ExportDateFormat,$cn['date']);
				$upd["VOUC_SEQ"] = $bn;
				$upd["REFERENCE"] = $refno;
				$upd["REFNO"] = $refno1;
				$upd["DESP"] = $cn['account_name'];
				$upd["DEBITAMT"] = $this->selling_price_currency_format(abs($cn['TotalAmount']));
				$upd["CREDITAMT"] = "0";
				$upd["REM1"] = $cn["reason"];
				$upd["REM5"] = "STAX";
				$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$cn['date']);
				$upd["TYPE"] = "CN";				
				$this->insert_cash_sales($fp,$upd);
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
					$upd["ACCNO"] = $cn['customer_code'];
					$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$cn['date']);
					$upd["DATE"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["VOUC_SEQ"] = $bn;
					$upd["REFERENCE"] = $refno;
					$upd["REFNO"] = $refno1;
					$upd["DESP"] = $cn['customer_name'];
					$upd["DEBITAMT"] = "0";
					$upd["CREDITAMT"] = $this->selling_price_currency_format(abs($cn['ItemAmount']));
					$upd["REM1"] = $cn["reason"];
					$upd["REM5"] = "STAX";
					$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$cn['date']);
					$upd["TAXINCL"] = "FALSE";
					$upd["TYPE"] = "CN";
					$this->insert_cash_sales($fp,$upd);
					unset($upd);
										
					$upd["ACCNO"] = $cnTax['tax_account_code'];
					$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$cn['date']);
					$upd["DATE"] = $this->set_date($this->ExportDateFormat,$cn['date']);
					$upd["VOUC_SEQ"] = $bn;
					$upd["REFERENCE"] = $refno;
					$upd["REFNO"] = $refno1;
					$upd["DESP"] = $cnTax['tax_account_name'];
					$upd["TAXPEC"] = intval($cnTax['taxRate']);
					$upd["DEBITAMT"] = $this->selling_price_currency_format(abs($cnTax['TaxAmount']));
					$upd["CREDITAMT"] = "0";
					$upd["REM1"] = $cn["reason"];
					$upd["REM4"] = $cnTax['tax_code'];
					$upd["REM5"] = "STAX";					
					$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$cn['date']);
					$upd["TAXINCL"] = "FALSE";
					$upd["TYPE"] = "CN";
					$this->insert_cash_sales($fp,$upd);
					unset($upd);
				}
				
				$bn++;
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

			$ret = $this->sql_query($tmpSalesDb, "select date, invoice_no, customer_code, customer_name,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount
													from ".$this->tmpTable."
													group by date, invoice_no
													order by date, invoice_no");

			$knockoff = 1;
			while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
			{
				$upd["ACCNO"] = $dn['customer_code'];
				$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$dn['date']);
				$upd["DATE"] = $this->set_date($this->ExportDateFormat,$dn['date']);
				$upd["VOUC_SEQ"] = sprintf("%d",$bn);
				$upd["REFERENCE"] = $dn['invoice_no'];
				$upd["REFNO"] = $dn['invoice_no'];
				$upd["DESP"] = $dn['customer_name'];
				$upd["DEBITAMT"] = $this->selling_price_currency_format($val['TotalAmount']);
				$upd["CREDITAMT"] = "0";									
				$upd["AGE"] = "0";
				$upd["TAXPUR"] = "0";
				$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$dn['date']);
				$upd["TAXINCL"] = "FALSE";
				$upd["ITEMNO"] = "SALES";												
				$upd["TYPE"] = "DN";
				$this->insert_cash_sales($fp,$upd);
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
					$upd["ACCNO"] = $dnTax['account_code'];
					$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$dn['date']);
					$upd["DATE"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["VOUC_SEQ"] = sprintf("%d",$bn);
					$upd["REFERENCE"] = $dn['invoice_no'];
					$upd["REFNO"] = $dn['invoice_no'];			
					$upd["DESP"] = $dnTax['account_name'];
					$upd["DEBITAMT"] = "0";
					$upd["CREDITAMT"] = $this->selling_price_currency_format($dnTax['ItemAmount']);									
					$upd["AGE"] = "0";
					$upd["TAXPUR"] = "0";
					$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$dn['date']);
					$upd["TAXINCL"] = "FALSE";
					$upd["ITEMNO"] = "SALES";														
					$upd["TYPE"] = "DN";																							
					$this->insert_cash_sales($fp,$upd);
					unset($upd);
							
					$upd["ACCNO"] = $dnTax['tax_account_code'];
					$upd["FPERIOD"] = $this->calculate_financial_year_period($this->accSettings['financial_date']["date"],$dn['date']);
					$upd["DATE"] = $this->set_date($this->ExportDateFormat,$dn['date']);
					$upd["VOUC_SEQ"] = sprintf("%d",$bn);
					$upd["REFERENCE"] = $dn['invoice_no'];
					$upd["REFNO"] = $dn['invoice_no'];					
					$upd["DESP"] = $dnTax['tax_account_name'];
					$upd["TAXPEC"] = intval($taxRate);
					$upd["DEBITAMT"] = "0";
					$upd["CREDITAMT"] = $this->selling_price_currency_format($dnTax['TaxAmount']);									
					$upd["AGE"] = "0";
					$upd["REM4"] = $dnTax['tax_code'];
					$upd["TAXPUR"] = $this->selling_price_currency_format(0-$dnTax['TaxAmount']);
					$upd["TRDATETIME"] = $this->set_date($this->ExportDateTimeFormat,$dn['date']);
					$upd["TAXINCL"] = "FALSE";
					$upd["ITEMNO"] = "SALES";														
					$upd["TYPE"] = "DN";																							
					$this->insert_cash_sales($fp,$upd);
					unset($upd);
				}
				$bn++;
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
		
	function export_account_data()
	{
		file_put_contents($this->debug_file,"<pre>Generate DBF</pre>",FILE_APPEND);
		$ret = false;
		if(!OS_LINUX) dl("php_dbase.dll");
		
		if(file_exists($this->tmpFile)) $file=$this->tmpFile;

		if($file!=""){		
			if(!dbase_create($this->tmpExportFileName, $this->ExportFileDBHeader)){
				$ret = "Failed to create %s";
			}
			$db = dbase_open($this->tmpExportFileName, 2);
			$f = fopen($file,"r");
			while($r = fgetcsv($f,","))
			{								
				dbase_add_record($db,$r);						
			}
			dbase_close($db);
			fclose($f);
			unset($db);
		}
		
		return $ret;
	}

	/*function get_account_cash_sales_n_credit_note($tmpSalesDb,$groupBy,$dateTo)
	{
		//todo
	}*/
}
?>
