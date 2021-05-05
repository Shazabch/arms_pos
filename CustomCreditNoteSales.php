<?php
/*
4/5/2017 10:50 AM Qiu Ying
- Bug fixed on Batch No is not shown and Tax Rate becomes 0

4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

5/8/2017 9:10 AM Khausalya
- Enhanced changes from MYR to use config setting. 

5/25/2017 16:31 Qiu Ying
- Enhanced to export credit note with multiple invoice

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

8/15/2017 09:34 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type

12/6/2018 4:19 PM Andy
- Fixed Account Setting cannot get branch settings.
*/
include_once("CustomExportModule.php");
class CustomCreditNoteSales extends CustomExportModule
{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_credit_note($tmpSalesDb,$form)
	{
		global $LANG, $config;
		$ret=$this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
		$total = $this->sql_fetchrow($tmpSalesDb, $ret);
		$tmpSalesDb->sql_freeresult($ret);
		if($total['total']>0)
		{
			if(file_exists($this->tmpFile)) unlink($this->tmpFile);
			$fp = fopen($this->tmpFile, 'w');
			
			//Print Header
			if($form["header_column"]){
				foreach (unserialize($form["header_column"]) as $item){
					$upd[] = $item;
				}
				my_fputcsv($fp, $upd, $form["delimiter"]);
				unset($upd);
			}
			$data_column = unserialize($form["data_column"]);
			switch($form['row_format']){
				case "single_line":
					$ret = $this->sql_query($tmpSalesDb, "select date, return_receipt_no as ref_no, credit_note_no as doc_no, 
												currency_rate, currency_code, tax_code,uom,second_tax_code,
												customer_code, customer_name,batchno,account_code,account_name,terms,
												sum(ItemAmount) as SubTotal,
												sum(TaxAmount) as TotalTaxAmount,
												sum(TotalAmount) as GrandTotal,
												reason as remark,tax_rate,
												reason_code
												from ".$this->tmpTable."
												group by date, return_receipt_no, credit_note_no 
												order by date, return_receipt_no, credit_note_no");
					$bn = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$retItem = $this->sql_query($tmpSalesDb, "select date, return_receipt_no as ref_no, credit_note_no as doc_no, 
														currency_rate, currency_code, tax_code,second_tax_code,
														customer_code, customer_name,batchno,account_code,account_name,terms,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														reason as remark,tax_rate,reason,
														reason_code, uom
														from ".$this->tmpTable." where 
														date = ".ms($result['date'])." 
														and return_receipt_no = ".ms($result['ref_no'])." 
														and credit_note_no = ".ms($result['doc_no'])." 
														group by tax_code,reason 
														order by tax_code, reason");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $retItem))
						{
							if(trim($result['reason_code'])!="")
							{
								$result2['reason_code'] = $result['reason_code'];
							}
							elseif(trim($result2['remark'])!="")
							{
								$result2['reason_code'] = substr(trim($result2['remark']),0,8);
							}
							else{
								$result2['reason_code'] = "FGRT";
							}
							$result2['currency_code'] = ($result['currency_code']!="XXX")?$result['currency_code']:$config["arms_currency"]["code"];
							$result2["terms"] = ($result["terms"]==""?$this->accSettings['credit_term']['account_code']:$result['terms']);
							$result2["ItemAmount"] = abs($result2["ItemAmount"]);
							$result2["TaxAmount"] = abs($result2["TaxAmount"]);
							$result2["TotalAmount"] = abs($result2["TotalAmount"]);
							$result2["SubTotal"] = abs($result["SubTotal"]);
							$result2["TotalTaxAmount"] = abs($result["TotalTaxAmount"]);
							$result2["GrandTotal"] = abs($result["GrandTotal"]);
							$upd = $this->set_report_fields($result2, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $result2);
						}
						$tmpSalesDb->sql_freeresult($retItem);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "two_row":
					$ret = $this->sql_query($tmpSalesDb, "select credit_note_no as doc_no, date, currency_rate,
												customer_code, customer_name,uom,currency_code,batchno,
												return_receipt_no as ref_no, account_code, account_name, 
												tax_account_code, tax_account_name,reason,terms,reason_code,
												sum(ItemAmount) as ItemAmount,
												sum(TaxAmount) as TaxAmount,
												sum(TotalAmount) as TotalAmount,
												sum(ItemAmount) as SubTotal,
												sum(TaxAmount) as TotalTaxAmount,
												sum(TotalAmount) as GrandTotal,
												sum(ItemFAmount) as ItemFAmount,
												sum(TaxFAmount) as TaxFAmount,
												sum(TotalFAmount) as TotalFAmount
												from ".$this->tmpTable."
												where TotalAmount <> 0
												group by date, credit_note_no
												order by date, credit_note_no");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$result["reason"] = str_replace(array("\r","\n","\r\n"),"",$result['reason']);
						$result["ItemAmount"] = abs($result["ItemAmount"]);
						$result["TaxAmount"] = abs($result["TaxAmount"]);
						$result["TotalAmount"] = abs($result["TotalAmount"]);
						$result["SubTotal"] = abs($result["SubTotal"]);
						$result["TotalTaxAmount"] = abs($result["TotalTaxAmount"]);
						$result["GrandTotal"] = abs($result["GrandTotal"]);
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);

						$cond = "where date=".ms($result['date'])." and credit_note_no = ".ms($result['doc_no']);
						$ret2 = $this->sql_query($tmpSalesDb, "select credit_note_no as doc_no, date, currency_rate,
														customer_code, customer_name,uom,currency_code,batchno,
														return_receipt_no as ref_no,tax_code, account_code, account_name, 
														tax_account_code, tax_account_name,reason,terms,tax_rate,second_tax_code,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(ItemFAmount) as ItemFAmount,
														sum(TaxFAmount) as TaxFAmount,
														sum(TotalFAmount) as TotalFAmount
														from ".$this->tmpTable." $cond
														group by return_receipt_no,tax_code");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret2))
						{
							$result2["reason"] = str_replace(array("\r","\n","\r\n"),"",$result2['reason']);
							$result2["ItemAmount"] = abs($result2["ItemAmount"]);
							$result2["TaxAmount"] = abs($result2["TaxAmount"]);
							$result2["TotalAmount"] = abs($result2["TotalAmount"]);
							$result2["SubTotal"] = abs($result["SubTotal"]);
							$result2["TotalTaxAmount"] = abs($result["TotalTaxAmount"]);
							$result2["GrandTotal"] = abs($result["GrandTotal"]);
							$upd = $this->set_report_fields($result2, $data_column['detail'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret2);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "ledger_format":
					$ret = $this->sql_query($tmpSalesDb, "select batchno, return_receipt_no, credit_note_no, date, currency_rate, currency_code,
											account_code, account_name,
											sum(TotalAmount) as TotalAmount,
											sum(TotalFAmount) as TotalFAmount
											from ".$this->tmpTable."
											group by date, return_receipt_no, credit_note_no
											order by date, return_receipt_no, credit_note_no");
			
					$bn = 1;
					$this->seq_num = 1;
					while($cn = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$this->inv_seq_num = 1;
						$refno = $cn['credit_note_no'];
						$refno1 = $cn['batchno'];
						$docNo = sprintf("%07d",$bn);
						$amount = $cn['TotalAmount'];
						$amountF = ($cn['TotalFAmount']>0)?$cn['TotalFAmount']:$cn['TotalAmount'];				
						
						$result["accno"] = $cn['account_code'];
						$result["doc_no"] = $docNo;
						$result["date"] = $cn['date'];
						$result["ref_no"] = $refno;
						$result["batchno"] = $refno1;
						$result["desp2"] = "Credit Note";
						$result["desp"] = $cn['account_name'];
						$result["amount"] = $amount;
						$result["credit"] = abs($amount);
						$result["fx_amount"] = $amountF;
						$result["fx_credit"] = abs($amountF);
						$result["fx_rate"] = $cn['currency_rate'];
						$result["currency_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
						$result["bill_type"] = "C";
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $result);
						$this->inv_seq_num++;
						$this->seq_num++;
						
						$cond = "where date=".ms($cn['date'])." and return_receipt_no = ".ms($cn['return_receipt_no'])." and credit_note_no = ".ms($cn['credit_note_no']);
						$ret2 = $this->sql_query($tmpSalesDb, "select tax_code, customer_code, customer_name, tax_account_code, tax_account_name,
														sum(ItemAmount) as ItemAmount, tax_rate,
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
							
							$result["accno"] = $cnTax['customer_code'];
							$result["doc_no"] = $docNo;
							$result["date"] = $cn['date'];
							$result["ref_no"] = $refno;
							$result["batchno"] = $refno1;
							$result["desp2"] = "Credit Note";
							$result["desp"] = $cnTax['customer_name'];
							$result["desp3"] = $cnTax['tax_code'];
							$result["amount"] = $amount;
							$result["debit"] = $amount;
							$result["fx_amount"] = $amountF;
							$result["fx_debit"] = $amountF;
							$result["fx_rate"] = $cn['currency_rate'];
							$result["currency_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
							$result["bill_type"] = "C";
							$result["tax_rate"] = $cnTax['tax_rate'];;
							$upd = $this->set_report_fields($result, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $result);
							$this->inv_seq_num++;	
							$this->seq_num++;							
							
							$result["accno"] = $cnTax['tax_account_code'];
							$result["doc_no"] = $docNo;
							$result["date"] = $cn['date'];
							$result["ref_no"] = $refno;
							$result["batchno"] = $refno1;
							$result["desp2"] = "Credit Note";
							$result["desp"] = $cnTax['tax_account_name'];
							$result["amount"] = $tax;
							$result["debit"] = $tax;
							$result["fx_amount"] = $taxF;
							$result["fx_debit"] = $taxF;
							$result["fx_rate"] = $cn['currency_rate'];
							$result["currency_code"] = ($cn['currency_code']!="XXX")?$cn['currency_code']:$config["arms_currency"]["code"];
							$result["tax_code"] = $cnTax['tax_code'];
							$result["taxable"] = ($cnTax['tax_code']!="")?$amount:0;
							$result["fx_taxable"] = ($cnTax['tax_code']!="")?$amountF:0;
							$result["bill_type"] = "C";
							$result["tax_rate"] = $cnTax['tax_rate'];
							$upd = $this->set_report_fields($result, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $result);
							$this->inv_seq_num++;	
							$this->seq_num++;
						}
						$tmpSalesDb->sql_freeresult($ret2);
						$bn++;
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function create_credit_note($tmpSalesDb){
		global $config;
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
								`branch_id` integer default 0,
								`counter_id` integer default 0,
								`id` integer default 0,
								`batchno` varchar(20),
								`date` date,
								`ym` date,
								`return_receipt_no` char(30),								  
								`credit_note_no` char(30),								  
								`sku_description` char(150),
								`sku_cat_desc` char(150),
								`arms_code` char(20),
								`uom` char(20) default 'UNIT',
								`qty` double default 0,
								`currency_code` char(10) default '" . $config["arms_currency"]["code"] . "',
								`currency_rate` double default 1,
								`tax_code` char(30),
								`tax_rate` double default 0,
								`ItemAmount` double default 0,
								`TaxAmount` double default 0,
								`TotalAmount` double default 0,
								`ItemFAmount` double default 0,
								`TaxFAmount` double default 0,
								`TotalFAmount` double default 0,
								`reason` text,
								`terms` char(20),
								`customer_code` char(150),
								`customer_name` char(150),
								`account_code` char(100),
								`account_name` char(100),
								`tax_account_code` char(100),
								`tax_account_name` char(100),
								`reason_code` char(100),
								`reason_description` text,
								`second_tax_code` char(30),
								 primary key(`branch_id`,`counter_id`,`id`,`date`,`return_receipt_no`,`credit_note_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array()){
	  global $LANG, $config;
	  
		$i=1;
		$ret = $this->get_credit_note($pos_db, $where,false,false,false,true);
		$second_tax_code_list = $this->get_second_tax_code_list();
		if($pos_db->sql_numrows($ret) > 0)
		{
			while($rCN = $this->sql_fetchrow($pos_db, $ret))
			{	
				$retItem = $this->get_credit_note_pos_items($pos_db,$where,$rCN);
				if($pos_db->sql_numrows($retItem)>0)
				{
					$accountings=$this->accSettings;
					//$accountings = load_account_file($rCN['branch_id']);
					$this->accSettings=$accountings;
					unset($accountings);
					$rCN['pos_time'] = strtotime($rCN['pos_time']);
					$credit_note_no = (trim($rCN['credit_note_ref_no'])!=""?$rCN['credit_note_ref_no']:$rCN['credit_note_no']);
					if(isset($rCN['branch_id'])) $upd['branch_id'] = $rCN['branch_id'];
					if(isset($rCN['counter_id'])) $upd['counter_id'] = $rCN['counter_id'];
					while($rCNItem = $this->sql_fetchrow($pos_db, $retItem))
					{
						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rCNItem['sku_item_id']);
						if(isset($rCNItem['return_receipt_ref_no']) && trim($rCNItem['return_receipt_ref_no'])!="") {
							$return_receipt_no = $rCNItem['return_receipt_ref_no'];
						}
						else{
							$return_receipt_no = sprintf("%s%s",date("ymd",strtotime($rCNItem['return_date'])),$rCNItem['return_receipt_no']);
						}
							
						if(isset($rCN['goods_return_reason']) && trim($rCN['goods_return_reason'])!="")
						{
							list($grrCode,$grrDesc) = explode("-",$rCN['goods_return_reason']);
						}
						$upd['id'] = $i;
						$upd["batchno"] = $this->get_batchno(date("Y-m-d",$rCN['pos_time']));
						$upd['date'] = date("Y-m-d",$rCN['pos_time']);
						$upd['ym'] = date("Y-m-01",$rCN['pos_time']);
						$upd['return_receipt_no'] = $return_receipt_no;
						$upd['credit_note_no'] = $credit_note_no;
						$upd['sku_description'] = $sku['sku_desc'];
						$upd['sku_cat_desc'] = $sku['category_desc'];
						$upd['arms_code'] = $sku['arms_code'];
						$upd['uom'] = "Unit";
						$upd['qty'] = $rCNItem['qty'];
						$upd['reason_code'] = ((isset($grrCode))?$grrCode:"FGRT");
						$upd['reason_description'] = ((isset($grrDesc))?$grrDesc:"Faulty Item");
						$upd['reason'] = ((isset($rCNItem['goods_return_reason']) && trim($rCNItem['goods_return_reason'])!="")?$rCNItem['goods_return_reason']:"Faulty Item");				
						$upd['currency_rate'] = 1;
						if(isset($this->accSettings['terms']))
						{
							$account = $this->accSettings['terms'];
							$upd['terms'] = $account["account_code"];
						}
						
						if($rCNItem['tax_code'] && $rCNItem['tax_rate']!="")
						{
							$tax_amount=round($rCNItem['tax_amount'],2);
							
							$upd["tax_code"] = $rCNItem['tax_code'];
							$upd["tax_rate"] = $rCNItem['tax_rate'];
							$upd["ItemAmount"] = $rCNItem['before_tax_price'];
							$upd["TaxAmount"] = $tax_amount;
							$upd["TotalAmount"] = ($upd["ItemAmount"] + $upd["TaxAmount"]);
							
							unset($tax_amount);
						}
						else
						{
							$upd["tax_code"] = "NR";
							$upd["tax_rate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $rCNItem['before_tax_price'];
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings["sales_return"]))
							$account = $this->accSettings["sales_return"];
						else
							$account = $this->accSettings["sales"];
						$upd["account_code"] = $account['account_code'];
						$upd["account_name"] = $account['account_name'];
						$account = $this->accSettings['customer_code'];
						$upd['customer_code'] = $account['account_code'];
						$upd['customer_name'] = $account['account_name'];
						if(isset($this->accSettings[$upd["tax_code"]])){
							$account = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$account['account_code'];
							$upd["tax_account_name"]=$account['account_name'];
						}
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$i++;
					}
					
					$cond[] = "credit_note_no = ".ms($rCN['credit_note_no']);
					$cond[] = "branch_id = ".ms($rCN['branch_id']);
					$cond[] = "counter_id = ".ms($rCN['counter_id']);
					$cond[] = "date = ".ms($rCN['date']);
					$cond[] = "pos_id = ".ms($rCN['pos_id']);
					$where1 = "where ".implode(" and ",$cond);
					$this->sql_query($pos_db,"update pos_credit_note set acc_is_exported = 1 ".$where1);
					unset($upd, $return_receipt_no, $credit_note_no, $account, $rCN,$cond,$where1);
				}
				$pos_db->sql_freeresult($retItem);
			}
		}
		$pos_db->sql_freeresult($ret);
  
		if($config['consignment_modules']){
		  $ret = $this->get_credit_note($pos_db, $where, true);
			if($pos_db->sql_numrows($ret) > 0){
				while($rCN = $this->sql_fetchrow($pos_db, $ret)){
					$accountings=$this->accSettings;
					//$accountings = load_account_file($rPos['branch_id']);
					$this->accSettings=$accountings;
					unset($accountings);
					
					$branch=$this->get_branch($pos_db,$rCN['ci_branch_id']);
					$rCN['pos_time']=strtotime($rCN['ci_date']);
					$return_receipt_no = $rCN['inv_no'];
					$credit_note_no = $return_receipt_no;
					
					$ret1 = $this->get_credit_note_items($pos_db, $rCN);
		
					if($pos_db->sql_numrows($ret1) > 0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
							
							$branch = $this->get_branch($pos_db, $rCN['to_branch_id']);

							$debtor = array(
									'customer_code'=>$branch['account_code'],
									'customer_name'=>$branch['description'],
									'terms'=>$branch['con_terms'],
									'currency_code'=>$branch['currency_code']);
				
							$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$rItem['tax_code'] = $rItem['gst_code'];
							$rItem['tax_rate'] = $rItem['gst_rate'];
				
							if($rItem['tax_code'] != "" && $rItem['tax_rate'] != ""){
								$rItem['tax_amount'] = $rItem['item_gst2'];
								$rItem['before_tax_price'] = $rItem['item_amt2'];
				
								if($rCN['exchange_rate']>1){
									$rItem['exchange_rate'] = $rCN['exchange_rate'];
									$rItem['tax_amount_f'] = $rItem['item_foreign_gst2'];
									$rItem['before_tax_price_f'] = $rItem['item_foreign_amt2'];
								}
							}
							else{
								if($rCN['discount'] != ""){
									$total_discount = $rCN['sub_total_amt'];
									$this->cal_discount($total_discount, $rCN['discount']);
									$total_discount_per = $total_discount / $rCN['sub_total_amt'];
								}
								else{
									$total_discount = 0;
									$total_discount_per = 1;
								}

								$rItem['tax_amount'] = 0;
								$rItem['before_tax_price'] = ($rItem['cost_price'] * $rItem['qty']) - $rItem['discount_amt'];
				
								$rItem['before_tax_price'] = $rItem['before_tax_price'] * $total_discount_per;
				
								if($rCN['exchange_rate']>1){
									$rItem['exchange_rate'] = $rCN['exchange_rate'];
									$rItem['tax_amount_f'] = 0;
									$rItem['before_tax_price_f'] = ($rItem['foreign_cost_price'] * $rItem['qty']) - $rItem['foreign_discount_amt'];;
				
									$rItem['before_tax_price_f'] = $rItem['before_tax_price_f'] * $total_discount_per;
								}
				
								unset($total_discount, $total_discount_per);
							}
				
							unset($branch);
							
							$upd['branch_id'] = $rCN['branch_id'];
							$upd['id'] = $i;
							$upd["batchno"] = $this->get_batchno($rCN['date']);
							$upd['date'] = $rCN['date'];
							$upd['ym'] = date("Y-m-01",strtotime($rCN['date']));
							$upd['return_receipt_no'] = $return_receipt_no;
							$upd['credit_note_no'] = $credit_note_no;
							$upd['sku_description'] = $sku['sku_desc'];
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd['arms_code'] = $sku['arms_code'];
							$upd['uom'] = "Unit";
							$upd['qty'] = $rItem['row_qty'];
							
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["tax_rate"] = $rItem['tax_rate'];
							}else{
								$upd["tax_code"] = "NR";
								$upd["tax_rate"] = 0;
							}
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["ItemAmount"] = $rItem['before_tax_price'];
							$upd["TaxAmount"] = $rItem['tax_amount'];
							$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
												
							if($rItem['exchange_rate']>1){
								$upd["currency_rate"] = $rItem['exchange_rate'];
								$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
								$upd["TaxFAmount"] = $rItem['tax_amount_f'];
								$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
							}
					
							$upd['reason'] = $rCN['remark'];
							$upd['terms'] = $debtor['terms'];
							$upd['currency_code'] = $debtor['currency_code'];
							$upd['customer_code'] = $debtor['customer_code'];
							$upd['customer_name'] = $debtor['customer_name'];
							
							if(isset($this->accSettings['sales'])){
								$upd["account_code"] = $this->accSettings['sales']['account_code'];
								$upd["account_name"] = $this->accSettings['sales']['account_name'];
							}
					
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]];
								$upd["tax_account_code"] = $acc_tax['account_code'];
								$upd["tax_account_name"] = $acc_tax['account_name'];
							}
								
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd);
							$i++;
						}
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "inv_no = ".ms($rCN['inv_no']);
						$cond[] = "date = ".ms($rCN['date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cn set acc_is_exported=1 ".$where1);
						unset($cond,$where);
					}
				}
			}
		}
		else		
		{
			$ret = $this->get_credit_note($pos_db, $where, false, true);
			if($pos_db->sql_numrows($ret) > 0)
			{
				while($rCN = $this->sql_fetchrow($pos_db, $ret))
				{					
					$accountings=$this->accSettings;
					//$accountings = load_account_file($rPos['branch_id']);
					$this->accSettings=$accountings;
					unset($accountings);
										
					$rCN['pos_time']=strtotime($rCN['cn_date']);
					$credit_note_no = $rCN['cn_no'];
					$ret1 = $this->get_credit_note_items($pos_db, $rCN,true);
					$customer = $this->accSettings['customer_code'];
					$terms = $this->accSettings['credit_term'];
					if($pos_db->sql_numrows($ret1) > 0)
					{
						while($rItem = $this->sql_fetchrow($pos_db, $ret1))
						{
							$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
							
							//To get invoice_no
							$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
							if($pos_db->sql_numrows($retDo)>0)						
							{
								$rInv = $pos_db->sql_fetchrow($retDo);
								if($rInv['do_type']=='transfer'){
									$branch=$this->get_branch($pos_db,$rInv['do_branch_id']);
									$debtor=array('code'=>$branch['account_code_debtor'],
												'description'=>$branch['description'],
												'address'=>$branch['address'],
												'terms'=>$branch['con_terms'],
												'account_receivable_code'=>$branch['account_receivable_code'],
												'account_receivable_name'=>$branch['account_receivable_name']);
								}
								else{
									$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);
									
									$customer_remark = $debtor['description'];
								}								
							}
							else{
								$debtor = array(
									'code'=>$customer['account_code'],
									'description'=>$customer['account_name'],
									'terms'=>$terms['account_code'],
									'currency_code'=>$config["arms_currency"]["code"]);
							}
							
							$return_receipt_no = $rItem['inv_no'];
							$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$rItem['tax_code'] = $rItem['gst_code'];
							$rItem['tax_rate'] = $rItem['gst_rate'];
							$rItem['tax_amount'] = $rItem['line_gst_amt2'];
							$rItem['before_tax_price'] = $rItem['line_gross_amt2'];				
							
							$upd['branch_id'] = $rCN['branch_id'];
							$upd['id'] = $i;
							$upd["batchno"] = $this->get_batchno($rCN['cn_date']);
							$upd['date'] = $rCN['cn_date'];
							$upd['ym'] = date("Y-m-01",strtotime($rCN['cn_date']));
							$upd['return_receipt_no'] = $return_receipt_no;
							$upd['credit_note_no'] = $credit_note_no;
							$upd['sku_description'] = $sku['sku_desc'];
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd['arms_code'] = $sku['arms_code'];
							$upd['uom'] = "Unit";
							$upd['qty'] = $rItem['row_qty'];
							
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["tax_rate"] = $rItem['tax_rate'];
							}else{
								$upd["tax_code"] = "NR";
								$upd["tax_rate"] = 0;
							}
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							$upd["ItemAmount"] = $rItem['before_tax_price'];
							$upd["TaxAmount"] = $rItem['tax_amount'];
							$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
												
							if($rItem['exchange_rate']>1){
								$upd["currency_rate"] = $rItem['exchange_rate'];
								$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
								$upd["TaxFAmount"] = $rItem['tax_amount_f'];
								$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
							}
					
							$upd['reason'] = $rCN['remark'];
							$upd['terms'] = $debtor['terms'];
							$upd['currency_code'] = ((isset($debtor['currency_code']) && trim($debtor['currency_code'])!="")?$debtor['currency_code']:$config["arms_currency"]["code"]);
							$upd['customer_code'] = $debtor['code'];
							$upd['customer_name'] = $debtor['description'];
							
							if(isset($this->accSettings['sales_return'])){
								$upd["account_code"] = $this->accSettings['sales_return']['account_code'];
								$upd["account_name"] = $this->accSettings['sales_return']['account_name'];
							}
							elseif(isset($debtor['account_receivable_code']))
							{
								$upd["account_code"] = $debtor['account_receivable_code'];
								$upd["account_name"] = $debotr['account_receivable_name'];
							}
						
							elseif(isset($this->accSettings['sales'])){
								$upd["account_code"] = $this->accSettings['sales']['account_code'];
								$upd["account_name"] = $this->accSettings['sales']['account_name'];
							}
					
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]];
								$upd["tax_account_code"] = $acc_tax['account_code'];
								$upd["tax_account_name"] = $acc_tax['account_name'];
							}
								
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd,$debtor);
							$i++;
						}
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "cn_no = ".ms($rCN['cn_no']);
						$cond[] = "cn_date = ".ms($rCN['cn_date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cnote set acc_is_exported=1 ".$where1);
						unset($cond,$where1);
					}				
				}
			}
		}
	}
}
?>