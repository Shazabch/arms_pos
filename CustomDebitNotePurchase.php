<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

5/8/2017 9:04 AM Khausalya
- Enhanced changes from MYR to use config setting. 

6/12/2017 15:45 Qiu Ying
- Bug fixed on add tax rate in all data type

8/2/2017 09:12 AM Qiu Ying
- Enhanced to add second tax code

8/15/2017 09:35 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type
*/
include_once("CustomExportModule.php");
class CustomDebitNotePurchase extends CustomExportModule{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_debit_note($tmpSalesDb,$form)
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
					$ret = $this->sql_query($tmpSalesDb, "select date, inv_no, customer_code, 
													customer_name,uom,
													account_code, account_name,batchno,tax_code, tax_rate,
													currency_rate, currency_code, reason,second_tax_code,
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
													group by date, inv_no, tax_code, reason
													order by date, inv_no, tax_code");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$result["reason"] = (trim($result['reason'])==""?"WIDV":substr($result['reason'],0,8));
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "two_row":
					$ret = $this->sql_query($tmpSalesDb, "select date, inv_no, customer_code, 
														customer_name,uom,
														account_code, account_name,batchno,
														currency_rate, currency_code, reason,terms,
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
														group by date, inv_no
														order by date, inv_no");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$result["reason"] = str_replace(array("\r","\n","\r\n"),"",$result['reason']);
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);

						$cond = "where date = ".ms($result['date'])."
								 and inv_no = ".ms($result['inv_no']);
						$ret2 = $this->sql_query($tmpSalesDb, "select date, inv_no, customer_code, 
														customer_name,uom, tax_rate,
														account_code, account_name,batchno,tax_code,second_tax_code,
														currency_rate, currency_code, reason,terms,
														sum(ItemAmount) as ItemAmount,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(ItemFAmount) as ItemFAmount,
														sum(TaxFAmount) as TaxFAmount,
														sum(TotalFAmount) as TotalFAmount
														from ".$this->tmpTable." $cond
														group by tax_code");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret2))
						{
							$result2["reason"] = str_replace(array("\r","\n","\r\n"),"",$result2['reason']);
							$result2["SubTotal"] = $result["SubTotal"];
							$result2["TotalTaxAmount"] = $result["TotalTaxAmount"];
							$result2["GrandTotal"] = $result["GrandTotal"];
							$upd = $this->set_report_fields($result2, $data_column['detail'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $result2);
						}
						$tmpSalesDb->sql_freeresult($ret2);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "ledger_format":
					$ret = $this->sql_query($tmpSalesDb, "select batchno, date, 
													inv_no as 'doc_no', customer_code, 
													customer_name, 
													currency_rate, currency_code,
													sum(ItemAmount) as ItemAmount,
													sum(TaxAmount) as TaxAmount,
													sum(TotalAmount) as TotalAmount,
													sum(ItemFAmount) as ItemFAmount,
													sum(TaxFAmount) as TaxFAmount,
													sum(TotalFAmount) as TotalFAmount
													from ".$this->tmpTable."
													group by date, inv_no, customer_code
													order by date, inv_no");

					$this->seq_num=1;
					$this->inv_seq_num=1;
					while($dn = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$cond = "where date = ".ms($dn['date'])."
								 and inv_no = ".ms($dn['doc_no'])."
								 and customer_code = ".ms($dn['customer_code']);
						$ret2 = $this->sql_query($tmpSalesDb, "select tax_code, account_code, account_name, tax_account_name, tax_account_code,
														sum(ItemAmount) as ItemAmount, tax_rate,second_tax_code,
														sum(TaxAmount) as TaxAmount,
														sum(TotalAmount) as TotalAmount,
														sum(ItemFAmount) as ItemFAmount,
														sum(TaxFAmount) as TaxFAmount,
														sum(TotalFAmount) as TotalFAmount
														from ".$this->tmpTable." $cond
														group by tax_code");
						while($dnTax = $this->sql_fetchrow($tmpSalesDb, $ret2))
						{
							
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemAmount'] += $dnTax['ItemAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxAmount'] += $dnTax['TaxAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalAmount'] += $dnTax['TotalAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['ItemFAmount'] += $dnTax['ItemFAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TaxFAmount'] += $dnTax['TaxFAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['TotalFAmount'] += $dnTax['TotalFAmount'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['tax_rate'] = $dnTax['tax_rate'];
							$dnInfo[$dnTax['tax_code']][$dnTax['account_code']][$dnTax['account_name']]['second_tax_code'] = $dnTax['second_tax_code'];

							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemAmount'] += $dnTax['ItemAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxAmount'] += $dnTax['TaxAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalAmount'] += $dnTax['TotalAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['ItemFAmount'] += $dnTax['ItemFAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TaxFAmount'] += $dnTax['TaxFAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['TotalFAmount'] += $dnTax['TotalFAmount'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['tax_rate'] = $dnTax['tax_rate'];
							$dnInputTax[$dnTax['tax_code']][$dnTax['tax_account_code']][$dnTax['tax_account_name']]['second_tax_code'] = $dnTax['second_tax_code'];
							
						}
						$tmpSalesDb->sql_freeresult($ret2);
						
						$tmp["accno"] = $dn['customer_code'];
						$tmp["desp"] = $dn['customer_name'];
						$tmp["doc_no"] = $dn['doc_no'];
						$tmp["date"] = $dn['date'];
						$tmp["batchno"] = $dn['batchno'];
						$tmp["amount"] = $dn['TotalAmount'];
						$tmp["debit"] = $dn['TotalAmount'];
						$tmp["fx_amount"] = ($dn['TotalFAmount']>0)?$dn['TotalFAmount']:$dn['TotalAmount'];
						$tmp["fx_debit"] = ($dn['TotalFAmount']>0)?$dn['TotalFAmount']:$dn['TotalAmount'];
						$tmp["fx_rate"] = $dn['currency_rate'];
						$tmp["currency_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
						$tmp["total_amount"] = $dn['TotalAmount'];
						$tmp["bill_type"] = "C";
						
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->seq_num++;
						$this->inv_seq_num++;
						
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
										$val3["accno"] = $accCode;
										$val3["desp"] = $accName;
										$val3["doc_no"] = $dn['doc_no'];
										$val3["date"] = $dn['date'];
										$val3["batchno"] = $dn['batchno'];
										$val3["amount"] = 0-$amount;
										$val3["credit"] = $amount;
										$val3["fx_amount"] = 0-$amountF;
										$val3["fx_credit"] = $amountF;
										$val3["fx_rate"] = $dn['currency_rate'];
										$val3["currency_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
										$val3["tax_code"] = $txCode;
										$val3["tax_rate"] = $val3["tax_rate"];
										$val3["bill_type"] = "C";
										$val3["total_amount"] = $dn['TotalAmount'];
										$upd = $this->set_report_fields($val3, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd, $tmp);
										$this->seq_num++;
										$this->inv_seq_num++;

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
										$val3["accno"] = $txAccCode;
										$val3["desp"] = $txAccName;
										$val3["doc_no"] = $dn['doc_no'];
										$val3["date"] = $dn['date'];
										$val3["batchno"] = $dn['batchno'];
										$val3["amount"] = 0-$tax;
										$val3["credit"] = $tax;
										$val3["fx_amount"] = 0-$taxF;
										$val3["fx_credit"] = $taxF;
										$val3["fx_rate"] = $dn['currency_rate'];
										$val3["currency_code"] = ($dn['currency_code']!="XXX")?$dn['currency_code']:$config["arms_currency"]["code"];
										$val3["tax_code"] = $txCode;
										$val3["tax_rate"] = $val3["tax_rate"];
										$val3["taxable"] = 0-$val3['ItemAmount'];
										$val3["fx_taxable"] = 0-$amountF;
										$val3["bill_type"] = "C";
										$val3["total_amount"] = $dn['TotalAmount'];
										$upd = $this->set_report_fields($val3, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd, $tmp);
										$this->seq_num++;
										$this->inv_seq_num++;
									}
								}
							}
						}
						unset($dnInputTax);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function create_debit_note($tmpSalesDb){
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
									`branch_id` integer default 0,
									`counter_id` integer default 0,
									`id` integer default 0,
									`batchno` varchar(20),
									`date` date,
									`ym` date,
									`inv_no` char(50),
									`sku_description` char(50),
									`sku_cat_desc` char(50),
									`arms_code` char(30),
									`uom` char(150) default 'UNIT',
									`qty` double default 0,
									`tax_code` char(30),
									`tax_rate` double default 0,
									`ItemAmount` double default 0,
									`TaxAmount` double default 0,
									`TotalAmount` double default 0,
									`reason` text,
									`currency_code` char(10),
									`currency_rate` double default 1,									
									`ItemFAmount` double default 0,
									`TaxFAmount` double default 0,
									`TotalFAmount` double default 0,
									`terms` char(30),								  
									`customer_code` char(150),
									`customer_name` char(150),
									`account_code` char(100),
									`account_name` char(100),
									`tax_account_code` char(100),
									`tax_account_name` char(100),
									`reason_code` char(100),
									`second_tax_code` char(30),
									 primary key(`branch_id`,`counter_id`,`id`,`date`,`inv_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_debit_note($tmpSalesDb, $pos_db, $sku_db=null, $where=array()){
		$ret = $this->get_debit_note($pos_db, $where);
		if($pos_db->sql_numrows($ret) > 0){
			while($rDN = $this->sql_fetchrow($pos_db, $ret)){
				$ret1 = $this->get_debit_note_items($pos_db, $rDN);

				if($pos_db->sql_numrows($ret1) > 0){
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
						$this->insert_debit_notes($tmpSalesDb, $pos_db, $sku_db, $rDN, $rItem);
					}
					$cond[] = "branch_id = ".ms($rDN['branch_id']);
					$cond[] = "id = ".ms($rDN['id']);
					$cond[] = "dn_no = ".ms($rDN['dn_no']);
					$cond[] = "dn_date = ".ms($rDN['dn_date']);
					$where1 = "where ".implode(" and ",$cond);
					$this->sql_query($pos_db,"update dnote set acc_is_exported = 1 ".$where1);
					unset($where1,$cond);
				}
			}
		}
	}
}
?>