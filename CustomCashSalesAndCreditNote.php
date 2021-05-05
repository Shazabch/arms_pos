<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

5/8/2017 8:25 AM Khausalya
- Enhanced changes from MYR to use config setting.

5/25/2017 16:31 Qiu Ying
- Enhanced to export credit note with multiple invoice 
- Bug fixed on tax code not showing

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

8/2/2017 09:12 AM Qiu Ying
- Enhanced to add second tax code

8/16/2017 18:06 PM Qiu Ying
- Bug fixed on Second Tax code is empty in certain rows
*/
include_once("CustomExportModule.php");
class CustomCashSalesAndCreditNote extends CustomExportModule{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_cash_sales_n_credit_note($tmpSalesDb,$form)
	{
		global $config;
		$ret = $this->sql_query($tmpSalesDb, "select count(*) as total from ".$this->tmpTable);
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
				case "ledger_format":
				//Export Cash Sales From POS Counter
				$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, ref_no
														from ".$this->tmpTable." 
														where tablename = ".ms("pos")."
														group by pos_date, doc_no order by pos_date,doc_no");									
				$this->seq_num = 1;
				while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
				{
					//Return Sales Transaction
					$this->inv_seq_num = 1;
					$wcond[] = "`pos_date`=".ms($result['pos_date']);
					$wcond[] = "`doc_no`=".ms($result['doc_no']);
					$wcond[] = "`type`!=".ms('deposit');
					$wcond[] = "`is_credit_notes`=".ms(1);
				
					$retCN = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, doc_no, credit_note_no, is_refund
										from ".$this->tmpTable."
										where ".implode(" and ",$wcond)."
										group by pos_date, doc_no,credit_note_no");
					unset($wcond);
					if($tmpSalesDb->sql_numrows($retCN)>0)
					{
						while($resultCN = $this->sql_fetchrow($tmpSalesDb, $retCN))
						{
							//Get Credit Note Debit Information
							$this->inv_seq_num = 1;
							$wcond[] = "`type`=".ms("debit");
							$wcond[] = "`pos_date`=".ms($resultCN['pos_date']);
							$wcond[] = "`doc_no`=".ms($resultCN['doc_no']);
							$wcond[] = "`credit_note_no`=".ms($resultCN['credit_note_no']);
							$wcond[] = "`type`!=".ms('deposit');
							$wcond[] = "`acc_type`!=".ms('rounding');
							$wcond[] = "`is_credit_notes`=".ms(1);
							
							$retCNDebit = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id, tax_rate,
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
									$amount = abs($resultCNDebit['ItemAmount']);
									$tmp["accno"] =  $resultCNDebit['account_code'];
									$tmp["doc_no"] = $refno;
									$tmp["date"] = $result['pos_date'];
									$tmp["batchno"] = $refno2;
									$tmp["desp"] = $resultCNDebit['account_name'];
									$tmp["amount"] = $amount;
									$tmp["debit"] = $amount;
									$tmp["fx_amount"] = $tmp["amount"];
									$tmp["fx_debit"] = $tmp["debit"];
									$tmp["tax_code"] = $resultCNDebit['tax_code'];
									$tmp["second_tax_code"] = $resultCNDebit['second_tax_code'];
									$tmp["tax_rate"] = $resultCNDebit['tax_rate'];
									$tmp["bill_type"] = "C";
									$tmp["fx_rate"] = 1;
									$tmp["currency_code"] = $config["arms_currency"]["code"];
									$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
									my_fputcsv($fp, $upd, $form["delimiter"]);
									unset($upd, $tmp);
									$this->inv_seq_num++;
									$this->seq_num++;
									
									//Input Tax
									$taxAmt = abs($resultCNDebit['TaxAmount']);
									$tmp["accno"] =  $resultCNDebit['tax_account_code'];
									$tmp["doc_no"] = $refno;
									$tmp["date"] = $result['pos_date'];
									$tmp["batchno"] = $refno2;
									$tmp["desp"] = $resultCNDebit['tax_account_name'];
									$tmp["amount"] = $taxAmt;
									$tmp["debit"] = $taxAmt;
									$tmp["fx_amount"] = $tmp["amount"];
									$tmp["fx_debit"] = $tmp["debit"];
									$tmp["tax_code"] = $resultCNDebit['tax_code'];
									$tmp["second_tax_code"] = $resultCNDebit['second_tax_code'];
									$tmp["tax_rate"] = $resultCNDebit['tax_rate'];
									$tmp["taxable"] = $amount;
									$tmp["fx_taxable"] = $amount;
									$tmp["bill_type"] = "H";
									$tmp["fx_rate"] = 1;
									$tmp["currency_code"] = $config["arms_currency"]["code"];
									$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
									my_fputcsv($fp, $upd, $form["delimiter"]);
									unset($upd, $tmp);
									$this->inv_seq_num++;
									$this->seq_num++;
								
									//Get Credit Goods Exchange Contra
									if(!$resultCN["is_refund"]){
										$amount = abs($resultCNDebit['TotalAmount']);
										$tmp["accno"] = $this->accSettings["goods_exchange_contra"]["account_code"];
										$tmp["doc_no"] = $refno;
										$tmp["date"] = $result['pos_date'];
										$tmp["batchno"] = $refno2;
										$tmp["desp"] = $this->accSettings["goods_exchange_contra"]["account_name"];
										$tmp["amount"] = 0-$amount;
										$tmp["credit"] = $amount;
										$tmp["fx_amount"] =$tmp["amount"];
										$tmp["fx_credit"] = $tmp["credit"];
										$tmp["fx_rate"] = 1;
										$tmp["currency_code"] = $config["arms_currency"]["code"];
										$tmp["bill_type"] = "H";
										$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd, $tmp);
									}
								}
							}
							$tmpSalesDb->sql_freeresult($retCNDebit);
							
							$this->inv_seq_num = 1;
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
									$refno = $resultCNDebit['doc_no'];
									$refno2 = $resultCNDebit['batchno'];
									
									$amount = abs($resultCNDebit['ItemAmount']);
									$tmp["accno"] =  $resultCNDebit['account_code'];
									$tmp["doc_no"] = $refno;
									$tmp["date"] = $result['pos_date'];
									$tmp["batchno"] = $refno2;
									$tmp["desp"] = $resultCNDebit['account_name'];
									$tmp["amount"] = 0-$amount;
									$tmp["debit"] = 0-$amount;
									$tmp["fx_amount"] = $tmp["amount"];
									$tmp["fx_debit"] = $tmp["debit"];
									$tmp["bill_type"] = "C";
									$tmp["fx_rate"] = 1;
									$tmp["currency_code"] = $config["arms_currency"]["code"];
									$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
									my_fputcsv($fp, $upd, $form["delimiter"]);
									unset($upd, $tmp);
									$this->inv_seq_num++;
									$this->seq_num++;
								}
							}
							$tmpSalesDb->sql_freeresult($retCNDebit);
							
							//Get Credit Note Credit Information
							//$this->inv_seq_num = 1;
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
									$tmp["accno"] =  $resultCNCredit['account_code'];
									$tmp["doc_no"] = $refno;
									$tmp["date"] = $result['pos_date'];
									$tmp["batchno"] = $refno2;
									$tmp["desp"] = $resultCNCredit['account_name'];
									if($resultCNCredit["acc_type"] == "rounding"){
										$tmp["amount"] = 0-$amount;
										$tmp["credit"] = $amount;
									}else{
										$tmp["amount"] = $amount;
										$tmp["credit"] = abs($amount);
									}
									$tmp["fx_amount"] = $tmp["amount"];
									$tmp["fx_credit"] = $tmp["credit"];
									$tmp["bill_type"] = "C";
									$tmp["fx_rate"] = 1;
									$tmp["currency_code"] = $config["arms_currency"]["code"];
									$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
									my_fputcsv($fp, $upd, $form["delimiter"]);
									unset($upd, $tmp);
									$this->inv_seq_num++;
									$this->seq_num++;
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
										$acc_code = ($resultCNCredit["acc_type"] == "cash")?$resultCNCredit["account_code"]:$this->accSettings["goods_exchange_contra"]["account_code"];
										$acc_name = ($resultCNCredit["acc_type"] == "cash")?$resultCNCredit["account_name"]:$this->accSettings["goods_exchange_contra"]["account_name"];
										$refno = $resultCNCredit['doc_no'];
										$refno2 = $resultCNCredit['batchno'];
										
										$amount = $resultCNCredit['TotalAmount'];
										$tmp["accno"] = $acc_code;
										$tmp["doc_no"] = $refno;
										$tmp["date"] = $result['pos_date'];
										$tmp["batchno"] = $refno2;
										$tmp["desp"] = $acc_name;
										$tmp["amount"] = abs($amount);
										$tmp["debit"] = abs($amount);
										$tmp["fx_amount"] = $tmp["amount"];
										$tmp["fx_debit"] = $tmp["debit"];
										$tmp["fx_rate"] = 1;
										$tmp["currency_code"] = $config["arms_currency"]["code"];
										$tmp["bill_type"] = "H";
										$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd, $tmp);
										$this->inv_seq_num++;
										$this->seq_num++;
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
							if($resultDebitAccType['tablename']=='do'){
								$refno = $resultDebitAccType['doc_no'];
								$refno2 = $resultDebitAccType['doc_no'];
							}
							else{
								$refno = $resultDebitAccType['doc_no'];
								$refno2 = $resultDebitAccType['batchno'];
							}
							
							$amount = $resultDebitAccType['TotalAmount'];
							$tmp["accno"] =  $resultDebitAccType['account_code'];
							$tmp["doc_no"] = $refno;
							$tmp["date"] = $result['pos_date'];
							$tmp["batchno"] = $refno2;
							$tmp["desp"] = $resultDebitAccType['account_name'];
							$tmp["amount"] = $amount;
							$tmp["credit"] = abs($amount);
							$tmp["fx_amount"] = $tmp["amount"];
							$tmp["fx_credit"] = $tmp["credit"];
							$tmp["bill_type"] = "H";
							$tmp["fx_rate"] = 1;
							$tmp["currency_code"] = $config["arms_currency"]["code"];
							$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $tmp);
							$this->inv_seq_num++;
							$this->seq_num++;
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
										round(sum(ItemAmount),2) as ItemAmount,
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
								$tmp["accno"] =  $resultDebitAccType['account_code'];
								$tmp["doc_no"] = $refno;
								$tmp["date"] = $result['pos_date'];
								$tmp["batchno"] = $refno2;
								$tmp["desp"] = $resultDebitAccType['account_name'];
								$tmp["amount"] = $amount;
								$tmp["debit"] = $amount;
								$tmp["fx_amount"] = $tmp["amount"];
								$tmp["fx_debit"] = $tmp["debit"];
								$tmp["bill_type"] = "H";
								$tmp["fx_rate"] = 1;
								$tmp["currency_code"] = $config["arms_currency"]["code"];
								$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
								my_fputcsv($fp, $upd, $form["delimiter"]);
								unset($upd, $tmp);
								$this->inv_seq_num++;
								$this->seq_num++;
							}
							else{
								$amount = $resultDebitAccType['ItemAmount'];
								$tmp["accno"] =  $resultDebitAccType['account_code'];
								$tmp["doc_no"] = $refno;
								$tmp["date"] = $result['pos_date'];
								$tmp["batchno"] = $refno2;
								$tmp["desp"] = $resultDebitAccType['account_name'];
								$tmp["amount"] = $amount;
								$tmp["debit"] = $amount;
								$tmp["fx_amount"] = $tmp["amount"];
								$tmp["fx_debit"] = $tmp["debit"];
								$tmp["bill_type"] = "H";
								$tmp["fx_rate"] = 1;
								$tmp["currency_code"] = $config["arms_currency"]["code"];
								$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
								my_fputcsv($fp, $upd, $form["delimiter"]);
								unset($upd, $tmp);
								$this->inv_seq_num++;
								$this->seq_num++;
								
								$amount = $resultDebitAccType['TaxAmount'];
								$tmp["accno"] =  $resultDebitAccType['tax_account_code'];
								$tmp["doc_no"] = $refno;
								$tmp["date"] = $result['pos_date'];
								$tmp["batchno"] = $refno2;
								$tmp["desp"] = $resultDebitAccType['tax_account_name'];
								$tmp["amount"] = $amount;
								$tmp["debit"] = $amount;
								$tmp["fx_amount"] = $tmp["amount"];
								$tmp["fx_debit"] = $tmp["debit"];
								$tmp["bill_type"] = "H";
								$tmp["fx_rate"] = 1;
								$tmp["currency_code"] = $config["arms_currency"]["code"];
								$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
								my_fputcsv($fp, $upd, $form["delimiter"]);
								unset($upd, $tmp);
								$this->inv_seq_num++;
								$this->seq_num++;
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
										tax_code, tax_account_code, tax_account_name, tax_rate,second_tax_code,
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
							//Sales
							$amount = $resultDetail['ItemAmount'];
							$tmp["accno"] =  $resultDetail['account_code'];
							$tmp["doc_no"] = $refno;
							$tmp["date"] = $result['pos_date'];
							$tmp["batchno"] = $refno2;
							$tmp["desp"] = $resultDetail['account_name'];
							$tmp["amount"] = 0-$amount;
							$tmp["credit"] = $amount;
							$tmp["fx_amount"] = $tmp["amount"];
							$tmp["fx_credit"] = $tmp["credit"];
							$tmp["tax_code"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
							$tmp["second_tax_code"] = trim($resultDetail['second_tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
							$tmp["tax_rate"] = $resultDetail['tax_rate'];
							$tmp["bill_type"] = "H";
							$tmp["fx_rate"] = 1;
							$tmp["currency_code"] = $config["arms_currency"]["code"];
							$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $tmp);
							$this->inv_seq_num++;
							$this->seq_num++;
							if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
							
							//Input Tax
							$taxAmt = $resultDetail['TaxAmount'];
							$tmp["accno"] =  $resultDetail['tax_account_code'];
							$tmp["doc_no"] = $refno;
							$tmp["date"] = $result['pos_date'];
							$tmp["batchno"] = $refno2;
							$tmp["desp"] = $resultDetail['tax_account_name'];
							$tmp["amount"] = 0-$taxAmt;
							$tmp["credit"] = $taxAmt;
							$tmp["fx_amount"] = $tmp["amount"];
							$tmp["fx_credit"] = $tmp["credit"];
							$tmp["tax_code"] = $resultDetail['tax_code'];
							$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
							$tmp["tax_rate"] = $resultDetail['tax_rate'];
							$tmp["taxable"] = 0-$amount;
							$tmp["fx_taxable"] = 0-$amount;
							$tmp["bill_type"] = "H";
							$tmp["fx_rate"] = 1;
							$tmp["currency_code"] = $config["arms_currency"]["code"];
							$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $tmp);
							$this->inv_seq_num++;
							$this->seq_num++;
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
					$this->inv_seq_num = 1;				
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
						$tmp["accno"] =  $resultDebitAccType['account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["batchno"] = $refno2;
						$tmp["desp"] = $resultDebitAccType['account_name'];
						$tmp["amount"] = $amount;
						$tmp["debit"] = $amount;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_debit"] = $tmp["debit"];
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
					}		
					$tmpSalesDb->sql_freeresult($retDebitAccType);	
											
					$wcond[] = "`type`=".ms("credit");
					$wcond[] = "`pos_date`=".ms($result['pos_date']);
					$wcond[] = "`doc_no`=".ms($result['doc_no']);
					$wcond[] = "`acc_type`!=".ms("sales return");
					$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
										tax_code, tax_account_code, tax_account_name, tax_rate,second_tax_code,
										round(sum(ItemAmount),2) as ItemAmount,
										round(sum(TaxAmount),2) as TaxAmount,
										round(sum(TotalAmount),2) as TotalAmount
										from ".$this->tmpTable."
										where ".implode(" and ",$wcond)."
										group by pos_date, doc_no, account_code, account_name, tax_code");
					unset($wcond);
					while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
					{
						//Sales
						$amount = $resultDetail['ItemAmount'];
						$tmp["accno"] =  $resultDetail['account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["batchno"] = $refno2;
						$tmp["desp"] = $resultDetail['account_name'];
						$tmp["amount"] = 0-$amount;
						$tmp["credit"] = $amount;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_credit"] = $tmp["credit"];
						$tmp["tax_code"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
						$tmp["second_tax_code"] = trim($resultDetail['second_tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
						if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
						
						//Input Tax
						$taxAmt = $resultDetail['TaxAmount'];
						$tmp["accno"] =  $resultDetail['tax_account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["batchno"] = $refno2;
						$tmp["desp"] = $resultDetail['tax_account_name'];
						$tmp["amount"] = 0-$taxAmt;
						$tmp["credit"] = $taxAmt;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_credit"] = $tmp["credit"];
						$tmp["tax_code"] = $resultDetail['tax_code'];
						$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["taxable"] = 0-$amount;
						$tmp["fx_taxable"] = 0-$amount;
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
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
					$this->inv_seq_num = 1;				
					
					$refno = $result['doc_no'];
					$refno2 = $result['return_receipt_no'];
					
					if(isset($this->accSettings['cash_refund']))
					{
						$account = $this->accSettings['cash_refund'];
					}
					else
					{
						$account = $this->accSettings['cash'];
					}
					$amount = $result['TotalAmount'];
					$tmp["accno"] = $account['account_code'];
					$tmp["doc_no"] = $refno;
					$tmp["date"] = $result['pos_date'];
					$tmp["batchno"] = $refno2;
					$tmp["desp"] = $account['account_name'];
					$tmp["amount"] = 0-$amount;
					$tmp["credit"] = $amount;
					$tmp["fx_amount"] =$tmp["amount"];
					$tmp["fx_credit"] =$tmp["credit"];
					$tmp["bill_type"] = "H";
					$tmp["fx_rate"] = 1;
					$tmp["currency_code"] = $config["arms_currency"]["code"];
					$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
					my_fputcsv($fp, $upd, $form["delimiter"]);
					unset($upd, $tmp);
					$this->inv_seq_num++;
					$this->seq_num++;
		
											
					$wcond[] = "`pos_date`=".ms($result['pos_date']);
					$wcond[] = "`doc_no`=".ms($result['doc_no']);
					$wcond[] = "`return_receipt_no`=".ms($result['return_receipt_no']);

					$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
										tax_code, tax_account_code, tax_account_name, tax_Rate,second_tax_code,
										round(sum(ItemAmount),2) as ItemAmount,
										round(sum(TaxAmount),2) as TaxAmount,
										round(sum(TotalAmount),2) as TotalAmount
										from ".$this->tmpTable." 
										where ".implode(" and ",$wcond)."
										group by pos_date, doc_no, return_receipt_no, account_code, account_name, tax_code");
					unset($wcond);
					while($resultDetail = $this->sql_fetchrow($tmpSalesDb, $retDetail))
					{
						//Sales
						$amount = $resultDetail['ItemAmount'];
						$tmp["accno"] =  $resultDetail['account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["batchno"] = $refno2;
						$tmp["desp"] = $resultDetail['account_name'];
						$tmp["amount"] = $amount;
						$tmp["debit"] = $amount;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_debit"] =$tmp["debit"];
						$tmp["tax_code"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
						$tmp["second_tax_code"] = trim($resultDetail['second_tax_code'])=="NR"?"":$resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
						if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
						
						//Input Tax
						$taxAmt = $resultDetail['TaxAmount'];
						$tmp["accno"] =  $resultDetail['tax_account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["batchno"] = $refno2;
						$tmp["desp"] = $resultDetail['tax_account_name'];
						$tmp["amount"] = $taxAmt;
						$tmp["debit"] = $taxAmt;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_debit"] = $tmp["debit"];
						$tmp["tax_code"] = $resultDetail['tax_code'];
						$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["taxable"] = 0-$amount;
						$tmp["fx_taxable"] = 0-$amount;
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
					}
					unset($wcond);
				}
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function create_cash_sales_credit_note($tmpSalesDb)
	{
		global $config;
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
										`tablename` varchar(20),
										`batchno` varchar(20),
										`branch_id` integer default 0,
										`counter_id` integer default 0,
										`id` integer default 0,
										`pos_date` date,
										`doc_no` char(20),
										`ref_no` char(30),
										`ym` date,
										`type` char(20),
										`acc_type` char(20),
										`description` char(150),
										`return_receipt_no` char(30),	
										`credit_note_no` char(30),
										`sku_cat_desc` char(150),
										`arms_code` char(20),
										`uom` char(20) default 'UNIT',
										`qty` double default 0,
										`unit_price` double default 0,
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
										`cancelled` char(1) default 'F',
										`transferable` char(1) default 'T',
										`customer_remark` text,
										`cn_remark` text,
										`is_credit_notes` char(1) default 0,
										`is_refund`	tinyint(1) default 0,
										`second_tax_code`	char(30),
										primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_cash_sales_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config;
		$credit_card = $this->credit_cards_type();
		$second_tax_code_list = $this->get_second_tax_code_list();
		// Cash Sales from POS Counter
		$ret=$this->get_pos($pos_db, $where);
		if($pos_db->sql_numrows($ret) > 0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
			while($rPos = $this->sql_fetchrow($pos_db, $ret))
			{
				$accountings=$this->accSettings;
				$accountings = load_account_file($rPos['branch_id']);
				$this->accSettings=$accountings;
				unset($accountings);

				$rPos['pos_date']=strtotime($rPos['date']);
				$receipt_no = $rPos['receipt_no'];
				if(trim($rPos['print_full_tax_invoice_remark'])!="" && trim($rPos['print_full_tax_invoice_remark'])!="N;"){
					$customer_remark = $rPos['print_full_tax_invoice_remark'];
				}
				elseif((trim($rPos['print_full_tax_invoice_remark'])=="" || trim($rPos['print_full_tax_invoice_remark'])=="N;") && trim($rPos['special_exempt_remark'])!=""){
					$customer_remark = $rPos['special_exempt_remark'];
				}
				else{
					$customer_remark = "";
				}
				
				if(isset($rPos['pos_more_info'])) $rPos['pos_more_info'] = unserialize($rPos['pos_more_info']);
				
				$receipt_ref_no = $rPos['receipt_ref_no'];
				$posDate = date("Y-m-d",$rPos['pos_date']);
				$posYM = date("Y-m-01",$rPos['pos_date']);
				$is_cn = 0;
				$is_refund = 0;
				$retCN = $this->get_credit_note($pos_db,$where,false,false,array(),false,$rPos);
				if($pos_db->sql_numrows($retCN)>0)
				{
					while($rCN = $pos_db->sql_fetchassoc($retCN))
					{
						$is_cn = 1;
						$rCN['pos_time'] = strtotime($rCN['pos_time']);
						$credit_note_no = (trim($rCN['credit_note_ref_no'])!=""?$rCN['credit_note_ref_no']:$rCN['credit_note_no']);
						if(isset($rCN['branch_id'])) $upd['branch_id'] = $rCN['branch_id'];
						if(isset($rCN['counter_id'])) $upd['counter_id'] = $rCN['counter_id'];
						//$itemInfor = unserialize($rCN['item_infor']);
						$totalItemAmount = 0;
						$totalAmount = 0;
						$totalGSTAmount = 0;
						$retItem = $this->get_credit_note_pos_items($pos_db,$where,$rCN);
						if($pos_db->sql_numrows($retItem)>0)
						{
							while($rItem = $this->sql_fetchrow($pos_db, $retItem))
							{
								if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
								if(isset($rItem['return_receipt_ref_no']) && trim($rItem['return_receipt_ref_no'])!="") {
									$return_receipt_no = $rItem['return_receipt_ref_no'];
								}
								else{
									$return_receipt_no = sprintf("%s%s",date("ymd",strtotime($rItem['return_date'])),$rItem['return_receipt_no']);
								}
									
								if(isset($rItem['goods_return_reason']) && trim($rItem['goods_return_reason'])!="")
								{
									list($grrCode,$grrDesc) = explode("-",$rItem['goods_return_reason']);
								}
								
								if(isset($this->accSettings['sales_return']))
									$acc=$this->accSettings['sales_return'];
								else
									$acc=$this->accSettings['sales'];
								
								$upd = array();
								$upd['is_credit_notes'] = 1;
								$upd["tablename"] = "pos";
								$upd["batchno"] = $this->get_batchno($posDate);
								if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
								$upd["counter_id"] = $rPos['counter_id'];
								$upd["id"] = $rIdx;
								$upd["pos_date"] = $posDate;
								$upd["ym"] = $posYM;
								$upd["ref_no"] = $receipt_no;
								$upd["doc_no"] = $receipt_ref_no;
								$upd['credit_note_no'] = $credit_note_no;
								$upd['return_receipt_no'] = ((isset($rItem['return_receipt_ref_no']) && trim($rItem['return_receipt_ref_no'])!="")?$rItem['return_receipt_ref_no']:$rItem['return_receipt_no']);
								$upd["type"] = "debit";
								$upd["acc_type"] = "sales return";
								$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
								$upd['sku_cat_desc'] = $sku['category_desc'];
								$upd["arms_code"] = $sku['arms_code'];
								$upd["qty"] = $rItem['qty'];
								if($rItem['tax_code'] && $rItem['tax_rate']!=""){
									$tax_amount=round($rItem['tax_amount'],2);
									$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
									$upd["tax_code"] = $rItem['tax_code'];
									$upd["tax_rate"] = $rItem['tax_rate'];
									$upd["ItemAmount"] = $rItem['before_tax_price'];
									$upd["TaxAmount"] = $tax_amount;
									$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
								}else{
									$upd["unit_price"] = $rItem['price']/$rItem['qty'];
									$upd["tax_code"] = "NR";
									$upd["TaxAmount"] = 0;
									$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
								}
								
								$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
								
								if(isset($this->accSettings[$upd["tax_code"]])){
									$acc_tax = $this->accSettings[$upd["tax_code"]];
									$upd["tax_account_code"]=$acc_tax['account_code'];
									$upd["tax_account_name"]=$acc_tax['account_name'];
								}
								$upd["customer_code"] = $cus_acc['account_code'];
								$upd["customer_name"] = $cus_acc['account_name'];
								$upd["account_code"] = $acc['account_code'];
								$upd["account_name"] = $acc['account_name'];
								$upd["customer_remark"] = $customer_remark;
								
								$tmp["branch_id"] = $rItem["branch_id"];
								$tmp["counter_id"] = $rItem["counter_id"];
								$tmp["id"] = $rItem["pos_id"];
								$tmp["date"] = $rItem["date"];
								$tmp_ret=$this->get_pos_items($pos_db, $tmp);
								if($pos_db->sql_numrows($tmp_ret)== 1)
								{
									$upd["is_refund"] = 1;
									$is_refund = 1;
								}
								unset($tmp);
								$pos_db->sql_freeresult($tmp_ret);
				
								$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
								$rIdx++;
								$totalItemAmount += $upd["ItemAmount"];
								$totalAmount += $upd["TotalAmount"];
								$totalGSTAmount += $upd["TaxAmount"];
								unset($upd);
							}
							
			
							$cond[] = "credit_note_no = ".ms($rCN['credit_note_no']);
							$cond[] = "branch_id = ".ms($rCN['branch_id']);
							$cond[] = "counter_id = ".ms($rCN['counter_id']);
							$cond[] = "date = ".ms($rCN['date']);
							$cond[] = "pos_id = ".ms($rCN['pos_id']);
							$where1 = "where ".implode(" and ",$cond);
							$this->sql_query($pos_db,"update pos_credit_note set acc_is_exported = 1 ".$where1);
							unset($upd, $return_receipt_no, $account, $rCN,$cond,$where1);
						}
						$pos_db->sql_freeresult($retItem);
						unset($retItem);
					}
				}
				$pos_db->sql_freeresult($retCN);
				unset($retCN);

				$ret1 = $this->get_pos_payment($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1)>0)
				{
					while($rPayment = $this->sql_fetchrow($pos_db, $ret1))
					{	
						if(strtolower($rPayment['type'])!="deposit") 
						{
							if(in_array($rPayment['type'],array_keys($credit_card))){
								$description = 'credit_card';
							}
							else{
								$description = strtolower($rPayment['type']);
							}
		
							if($description=='mix & match total disc' || $description=='discount') continue;
							$acc=$this->accSettings[$description];
							$cus_acc=$this->accSettings['customer_code'];
							
							if($description=='cash') 
							{
								$rPayment['amount'] -= $rPos['amount_change'];
							}
							
							$upd = array();
							if($is_cn)
							{
								if($description=='cash' && $rPayment['amount'] ==0) continue;
								$type = ($description=='cash')?"debit":"credit";
								$upd['is_credit_notes'] = 1;
								$upd['credit_note_no'] = $credit_note_no;
								if($is_refund){
									$upd['is_refund'] = 1;
									$type = ($description=='cash')?"credit":"debit";
								}
							}
							else
							{
								$type = ($description=='rounding')?"credit":"debit";
							}

							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPayment["branch_id"])) $upd["branch_id"] = $rPayment['branch_id'];
							$upd["counter_id"] = $rPayment['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = $type;
							$upd["acc_type"] = $description;
							$upd["description"] = $rPayment['type'];
							$upd["ItemAmount"] = $rPayment['amount'];
							$upd["TotalAmount"] = $rPayment['amount'];
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd, $sku, $acc, $description, $type);
							$rIdx++;
						}
					}
		
					unset($rPayment);
				}
				$pos_db->sql_freeresult($ret1);
				
				if(isset($rPos['pos_more_info']['deposit']))
				{
					//print_r($rPos['pos_more_info']['deposit']);
					foreach($rPos['pos_more_info']['deposit'] as $deposit)
					{
						if(isset($deposit['gst_info'])) $deposit['gst_info'] = unserialize($deposit['gst_info']);
		
						$acc = $this->accSettings['deposit'];
						$cus_acc = $this->accSettings['customer_code'];

						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["ref_no"] = $receipt_no;
						$upd["doc_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "debit";
						$upd["acc_type"] = "deposit";
						$upd["description"] = "Deposit";
						$upd["qty"] = 1;
						$upd["unit_price"] = round($deposit['amount'], 2);
						if(isset($deposit['gst_info']['code']) && $deposit['gst_info']['rate']!=""){
							$upd["tax_code"] = $deposit['gst_info']['code'];
							$upd["tax_rate"] = $deposit['gst_info']['rate'];
							$upd["ItemAmount"] = ($deposit['amount']-$deposit['gst_amount']);
							$upd["TaxAmount"] = $deposit['gst_amount'];
							$upd["TotalAmount"] = $upd["unit_price"];
						}else{
							$upd["tax_code"] = "NR";
							$upd["TaxAmount"] = 0;
							$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
		
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd, $acc_tax);
						$rIdx++;
					}
				}
				
				$short_over=round($rPos['amount_tender']-$rPos['amount']-$rPos['amount_change']-$rPos['service_charges'],2);
				if($short_over!=0){				
					$cus_acc=$this->accSettings['customer_code'];
					$acc_short=isset($this->accSettings['short'])?$this->accSettings['short']:array("account_code"=>"","account_name"=>"");
					$acc_over=isset($this->accSettings['over'])?$this->accSettings['over']:array("account_code"=>"","account_name"=>"");
		
					$acc=($short_over>0)?$acc_over:$acc_short;
		
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					if(isset($rPos["counter_id"])) $upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["ref_no"] = $receipt_no;
					$upd["doc_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'credit';
					$upd["acc_type"] = ($short_over>0)?"over":"short";
					$upd["description"] = ($short_over>0)?"Over":"Short";
					$upd["ItemAmount"] = $short_over;
					$upd["TotalAmount"] = $short_over;
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
	
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $acc, $description, $type);
					$rIdx++;
				}
				
				if(isset($rPos['deposit']) && $rPos['deposit']){
					$ret1 = $this->get_pos_deposit($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							$acc=$this->accSettings['deposit'];
							$cus_acc=$this->accSettings['customer_code'];
							$rItem['gst_info']=unserialize($rItem['gst_info']);
							
							$upd = array();
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							$upd["type"] = "credit";
							$upd["acc_type"] = "deposit";
							$upd["description"] = "Deposit";
							$upd["qty"] = 1;
							$upd["unit_price"] = round($rItem['deposit_amount'], 2);
							if(isset($rItem['gst_info']['code']) && $rItem['gst_info']['rate']!=""){
								$upd["tax_code"] = $rItem['gst_info']['code'];
								$upd["tax_rate"] = $rItem['gst_info']['rate'];
								$upd["ItemAmount"] = $rItem['deposit_amount']-$rItem['gst_amount'];
								$upd["TaxAmount"] = $rItem['gst_amount'];
								$upd["TotalAmount"] = $upd["unit_price"];
							}else{
								$upd["tax_code"] = "NR";
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $upd['unit_price'];
							}
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
			
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
						
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd, $acc_tax);
							$rIdx++;
						}
					}
					$pos_db->sql_freeresult($ret1);
				}
				else{
					$ret1=$this->get_pos_items($pos_db, $rPos);
					if($pos_db->sql_numrows($ret1)>0)
					{
						while($rItem = $this->sql_fetchrow($pos_db, $ret1))
						{
							$has_credit_note = ($rItem['qty']<0 && $rPos['is_gst'])?1:0;	
							if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);
							$discount=$rItem['discount']+$rItem['discount2'];
							
							if($has_credit_note)
							{
								continue;
								if(isset($this->accSettings['sales_return']))
									$acc=$this->accSettings['sales_return'];
								else
									$acc=$this->accSettings['sales'];
							}
							else
								$acc=$this->accSettings['sales'];
							$cus_acc=$this->accSettings['customer_code'];
			
							$upd = array();
							$upd['is_credit_notes'] = $has_credit_note;
							$upd["tablename"] = "pos";
							$upd["batchno"] = $this->get_batchno($posDate);
							if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
							$upd["counter_id"] = $rPos['counter_id'];
							$upd["id"] = $rIdx;
							$upd["pos_date"] = $posDate;
							$upd["ref_no"] = $receipt_no;
							$upd["doc_no"] = $receipt_ref_no;
							$upd["ym"] = $posYM;
							if($has_credit_note)
							{
								$retGoodsReturn = $this->get_goods_return($pos_db,$where,$rItem);
								/*$retCN = $this->get_credit_note($pos_db,$where,false,false,$rItem);
								if($pos_db->sql_numrows($retCN)>0){
									$rCNItem = $this->sql_fetchrow($pos_db, $retCN);
									$upd['credit_note_no'] = $rCNItem['credit_note_ref_no'];
									$upd['return_receipt_no'] = $rCNItem['return_receipt_no'];
									$upd["type"] = "debit";
									$upd["acc_type"] = "sales return";
								}
								$pos_db->sql_freeresult($retCN);*/
							}
							else{
								$upd["type"] = "credit";
								$upd["acc_type"] = "sales";
							}
							$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
							$upd['sku_cat_desc'] = $sku['category_desc'];
							$upd["arms_code"] = $sku['arms_code'];
							$upd["qty"] = $rItem['qty'];
							if($rItem['tax_code'] && $rItem['tax_rate']!=""){
								$tax_amount=round($rItem['tax_amount'],2);
								$upd["unit_price"] = round($rItem['before_tax_price']/$rItem['qty'],2);
								$upd["tax_code"] = $rItem['tax_code'];
								$upd["tax_rate"] = $rItem['tax_rate'];
								$upd["ItemAmount"] = $rItem['before_tax_price'];
								$upd["TaxAmount"] = $tax_amount;
								$upd["TotalAmount"] = ($rItem['before_tax_price'] + $tax_amount);
							}else{
								$upd["unit_price"] = $rItem['price']/$rItem['qty'];
								$upd["tax_code"] = "NR";
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $rItem['price']-$discount;
							}
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}
							$upd["customer_code"] = $cus_acc['account_code'];
							$upd["customer_name"] = $cus_acc['account_name'];
							$upd["account_code"] = $acc['account_code'];
							$upd["account_name"] = $acc['account_name'];
							$upd["customer_remark"] = $customer_remark;
			
							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							$rIdx++;
							
						}
						unset($rItem, $upd, $sku, $discount, $tax_amount, $acc_tax);
					}
					$pos_db->sql_freeresult($ret1);
				}

				if(isset($rPos['service_charges']) && $rPos['service_charges']>0){
					$acc=$this->accSettings['service_charge'];
					$cus_acc=$this->accSettings['customer_code'];
					$upd = array();
					$upd["tablename"] = "pos";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["counter_id"] = $rPos['counter_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["ref_no"] = $receipt_no;
					$upd["doc_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "service_charge";
					$upd["description"] = "Service Charge";
					if(isset($rPos['pos_more_info']['service_charges']['sc_gst_detail'])){
						$upd["unit_price"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["tax_code"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['code'];
						$upd["tax_rate"] = $rPos['pos_more_info']['service_charges']['sc_gst_detail']['rate'];
						$upd["ItemAmount"] = $rPos['service_charges']-$rPos['service_charges_gst_amt'];
						$upd["TaxAmount"] = $rPos['service_charges_gst_amt'];
						$upd["TotalAmount"] = $rPos['service_charges'];
					}
					else{
						$upd["tax_code"] = "NR";
						$upd["unit_price"] = $rPos['service_charges'];
						$upd["ItemAmount"] = $upd["TotalAmount"] = $rPos['service_charges'];
					}
					$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
					if(isset($this->accSettings[$upd["tax_code"]])){
						$acc_tax = $this->accSettings[$upd["tax_code"]];
						$upd["tax_account_code"]=$acc_tax['account_code'];
						$upd["tax_account_name"]=$acc_tax['account_name'];
					}
		
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
		
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					unset($upd, $sku, $discount, $tax_amount, $acc_tax, $acc);
					$rIdx++;
				}
					
				$cond[] = "branch_id = ".ms($rPos['branch_id']);
				$cond[] = "counter_id = ".ms($rPos['counter_id']);
				$cond[] = "id = ".ms($rPos['id']);
				$cond[] = "date = ".ms($rPos['date']);				
				$where1 = implode(" and ",$cond);
				$this->sql_query($pos_db,"update pos set acc_is_exported = 1 where ".$where1);				
				unset($cond);
				//unset($where);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
		
		//DO Cash Sales
		$ret = $this->get_do($pos_db,$where);
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret))
			{
				$accountings=$this->accSettings;
				$accountings = load_account_file($rPos['branch_id']);
				$this->accSettings=$accountings;
				unset($accountings);
				$orgDoDate = $rPos['do_date'];
				$rPos['do_date'] = strtotime($rPos['do_date']);
				$rPos['open_info'] = unserialize($rPos['open_info']);

				$receipt_no = $rPos['inv_no'];
				$receipt_ref_no = $rPos['do_no'];
				$posDate = date("Y-m-d",$rPos['do_date']);
				$posYM = date("Y-m-01",$rPos['do_date']);
				$customer_remark=$rPos['open_info']['name'];

				$ret1=$this->get_do_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						$acc=$this->accSettings['sales'];
						$cus_acc=$this->accSettings['customer_code'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = "NR";
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				
				$pos_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						$acc=$this->accSettings['sales'];
						$cus_acc=$this->accSettings['customer_code'];
						if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];

						$upd = array();
						$upd["tablename"] = "do";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "sales";
						$upd["description"] = $sku['sku_desc'];
						$upd["arms_code"] = $sku['arms_code'];
						$upd["qty"] = $rItem['row_qty'];
						if($rItem['gst_id']){
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["tax_code"] = $rItem['gst_code'];
							$upd["tax_rate"] = $rItem['gst_rate'];
							$upd["ItemAmount"] = round($rItem['inv_line_gross_amt2'],2);
							$upd["TaxAmount"] = round($rItem["inv_line_gst_amt2"],2);
							$upd["TotalAmount"] = round(($upd["ItemAmount"]+$upd["TaxAmount"]),2);
						}
						else{
							$upd["tax_code"] = "NR";
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
						$upd["customer_remark"] = $customer_remark;

						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						$rIdx++;
					}
					
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}
				$pos_db->sql_freeresult($ret1);
				
				if($rPos['total_inv_amt'] > 0){
					$acc=$this->accSettings['cash'];
					$cus_acc=$this->accSettings['customer_code'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = 'debit';
					$upd["acc_type"] = 'cash';
					$upd["description"] = 'CASH';
					$upd["ItemAmount"] = $rPos['total_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];

					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $acc,$cond,$where1);
					$rIdx++;
				}

				if($rPos['total_round_inv_amt'] != 0)
				{
					$acc = $this->accSettings["rounding"];
					$cus_acc = $this->accSettings['customer_code'];

					$upd = array();
					$upd["tablename"] = "do";
					$upd["batchno"] = $this->get_batchno($posDate);
					if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
					$upd["id"] = $rIdx;
					$upd["pos_date"] = $posDate;
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
					$upd["ym"] = $posYM;
					$upd["type"] = "credit";
					$upd["acc_type"] = "rounding";
					$upd["description"] = "";
					$upd["ItemAmount"] = $rPos['total_round_inv_amt'];
					$upd["TotalAmount"] = $rPos['total_round_inv_amt'];
					$upd["customer_code"] = $cus_acc['account_code'];
					$upd["customer_name"] = $cus_acc['account_name'];
					$upd["account_code"] = $acc['account_code'];
					$upd["account_name"] = $acc['account_name'];
				
					$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
					$cond[] = "id = ".ms($rPos['id']);
					$cond[] = "branch_id = ".ms($rPos['branch_id']);
					$cond[] = "do_no = ".ms($rPos['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($pos_db,"update do set acc_is_exported = 1 ".$where1);
					unset($upd, $sku, $acc, $type,$cond,$where1);
					$rIdx++;
				}
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);	
		
		//Credit Notes 
		$org_where = $where;
		//$where[] = "do.do_type = ".ms("open");
		$ret = $this->get_credit_note($pos_db, $where, false,true);
		if($pos_db->sql_numrows($ret)>0)
		{
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rCN = $this->sql_fetchrow($pos_db, $ret))
			{					
				$accountings=$this->accSettings;
				$accountings = load_account_file($rCN['branch_id']);
				$this->accSettings=$accountings;
				unset($accountings);
				
				$credit_note_no = $rCN['cn_no'];
				
				$customer = $this->accSettings['customer_code'];
				$terms = $this->accSettings['credit_term'];
				$ret1 = $this->get_credit_note_items($pos_db, $rCN ,true);
				if($pos_db->sql_numrows($ret1) > 0)
				{
					$is_exported = false;
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($rItem["do_type"] != 'open'){
							continue;
						}
						$is_exported = true;						
						$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
						if($pos_db->sql_numrows($retDo)>0)						
						{
							$rInv = $pos_db->sql_fetchrow($retDo);
							$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);	
							$customer_remark = $debtor['description'];
						}
						else{
							$debtor = array(
								'code'=>$customer['account_code'],
								'description'=>$customer['account_name'],
								'terms'=>$terms['account_code'],
								'currency_code'=>$config["arms_currency"]["code"]);
						}
						
						$sku = $this->get_sku($sku_db,$rItem['sku_item_id']);
						$return_receipt_no = $rItem['inv_no'];
						$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$rItem['tax_code'] = $rItem['gst_code'];
						$rItem['tax_rate'] = $rItem['gst_rate'];
						$rItem['tax_amount'] = $rItem['line_gst_amt2'];
						$rItem['before_tax_price'] = $rItem['line_gross_amt2'];				
						
						$upd['tablename'] = "do_cn";
						$upd['branch_id'] = $rCN['branch_id'];
						$upd['id'] = $i;
						$upd["batchno"] = $this->get_batchno($rCN['cn_date']);
						$upd['pos_date'] = $rCN['cn_date'];
						$upd['ym'] = date("Y-m-01",strtotime($rCN['cn_date']));
						$upd["type"] = "debit";
						$upd["acc_type"] = "sales return";
						$upd['return_receipt_no'] = $return_receipt_no;
						$upd['doc_no'] = $upd['credit_note_no'] = $credit_note_no;
						$upd['description'] = $sku['sku_desc'];
						$upd['sku_cat_desc'] = $sku['category_desc'];
						$upd['arms_code'] = $sku['arms_code'];
						$upd['uom'] = "Unit";
						$upd['qty'] = $rItem['row_qty'];
				
						if(trim($rItem['gst_id'])!=""){
							$upd["tax_code"] = $rItem['tax_code'];
							$upd["tax_rate"] = $rItem['tax_rate'];
						}
						else
						{
							$upd["tax_code"] = "NR";
							$upd["tax_rate"] = 0;
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						$upd["ItemAmount"] = $rItem['before_tax_price'];
						$upd["TaxAmount"] = $rItem['tax_amount'];
						$upd["TotalAmount"] = ($rItem['before_tax_price'] + $rItem['tax_amount']);
						
						if(isset($this->accSettings['sales_return']))
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account_code'];
							$upd["account_name"] = $this->accSettings['sales_return']['account_name'];
						}
						elseif(isset($debtor['account_receivable_code']))
						{
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debotr['account_receivable_name'];
						}
						else
						{
							$upd["account_code"] = $this->accSettings['sales_return']['account_code'];
							$upd["account_name"] = $this->accSettings['sales']['account_name'];
						}						
						
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

						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"] = $acc_tax['account_code'];
							$upd["tax_account_name"] = $acc_tax['account_name'];
						}
									
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd,$debtor);
						$i++;
					}
					
					if($is_exported){
						$cond[] = "id = ".ms($rCN['id']);
						$cond[] = "branch_id = ".ms($rCN['branch_id']);
						$cond[] = "cn_no = ".ms($rCN['cn_no']);
						$cond[] = "cn_date = ".ms($rCN['cn_date']);
						$where1 = "where ".implode(" and ",$cond);
						$this->sql_query($pos_db, "update cnote set acc_is_exported=1 ".$where1);
						unset($cond,$where1);
					}
				}
				$pos_db->sql_freeresult($ret1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);
	}
}
?>