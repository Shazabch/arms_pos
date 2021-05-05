<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

8/2/2017 09:12 AM Qiu Ying
- Enhanced to add second tax code
- Bug fixed on bill type should be set I for million

8/15/2017 09:35 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type

11/2/2017 5:45 PM Andy
- Added Data "Purchase Account Code" and "Purchase Account Name" for Purhcase Single Line and Two Row format.

12/6/2018 4:19 PM Andy
- Fixed Account Setting cannot get branch settings.
*/
include_once("CustomExportModule.php");
class CustomPurchase extends CustomExportModule
{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_payable($tmpSalesDb,$form)
	{
		global $LANG;
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
				case "two_row":
					$ret = $this->sql_query($tmpSalesDb, "select vendor_code as customer_code,
												vendor_name as customer_name,
												vendor_terms,inv_date as date,inv_no,gl_code,gl_name,batchno,
												tax_code,ref_no,department, tax_rate,second_tax_code,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount,
												round(sum(ItemAmount),2) as SubTotal,
												round(sum(TaxAmount),2) as TotalTaxAmount,
												round(sum(TotalAmount),2) as GrandTotal,
												purchase_account_code, purchase_account_name
												from ".$this->tmpTable."
												group by inv_no, inv_date, vendor_code
												order by inv_date");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						if($form["row_format"] == "two_row"){
							$data_column_type = $data_column['detail'];
							$upd = $this->set_report_fields($result, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd);
						}else{
							$data_column_type = $data_column['master'];
						}
						
						$ret1 = $this->sql_query($tmpSalesDb, "select vendor_code as customer_code,vendor_name as customer_name,
													vendor_terms,inv_date as date,inv_no,gl_code,gl_name,batchno,
													tax_code,ref_no,department, tax_rate,second_tax_code,
													round(sum(ItemAmount),2) as ItemAmount,
													round(sum(TaxAmount),2) as TaxAmount,
													round(sum(TotalAmount),2) as TotalAmount,
													purchase_account_code, purchase_account_name
													from ".$this->tmpTable."
													where inv_date=".ms($result['date'])."
													and inv_no=".ms($result['inv_no'])."
													and vendor_code=".ms($result['customer_code'])."
													group by tax_code");
						while($result1 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$result1["SubTotal"] = $result["SubTotal"];
							$result1["TotalTaxAmount"] = $result["TotalTaxAmount"];
							$result1["GrandTotal"] = $result["GrandTotal"];
							$upd = $this->set_report_fields($result1, $data_column_type, $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd,$result1);
						}
						$tmpSalesDb->sql_freeresult($ret1);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "ledger_format":
					$ret = $this->sql_query($tmpSalesDb, "select batchno, gl_code, gl_name, tax_rate,
											vendor_terms, vendor_code, vendor_name, inv_date as date, inv_no as 'doc_no',
											round(sum(ItemAmount),2) as ItemAmount,
											round(sum(TaxAmount),2) as TaxAmount,
											round(sum(TotalAmount),2) as TotalAmount									  
											from ".$this->tmpTable."
											group by vendor_code, vendor_id, inv_date, inv_no
											order by inv_date,inv_no");

					$this->seq_num = 1;
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$this->inv_seq_num = 1;								
					
						$ret2 = $this->sql_query($tmpSalesDb, "select round(sum(ItemAmount),2) as ItemAmount, tax_rate,
																round(sum(TaxAmount),2) as TaxAmount, second_tax_code,
																tax_code, tax_account_code, tax_account_name
																from ".$this->tmpTable."
																where inv_date = ".ms($result['date'])."
																and inv_no=".ms($result['doc_no'])."
																and vendor_code = ".ms($result['vendor_code'])."
																group by tax_code");



						if($tmpSalesDb->sql_numrows($ret2)>0)
						{
							while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret2))
							{
								$purchasebyTax[$result2['tax_code']]['accno'] = $result['gl_code'];
								$purchasebyTax[$result2['tax_code']]['desp'] = $result['gl_name'];
								$purchasebyTax[$result2['tax_code']]['date'] = $result['date'];
								$purchasebyTax[$result2['tax_code']]['doc_no'] = $result['doc_no'];
								$purchasebyTax[$result2['tax_code']]['batchno'] = $result['batchno'];
								$purchasebyTax[$result2['tax_code']]['tax_code'] = $result2['tax_code'];
								$purchasebyTax[$result2['tax_code']]['second_tax_code'] = $result2['second_tax_code'];
								
								$purchasebyTax[$result2['tax_code']]['amount'] += $result2['ItemAmount'];
								$purchasebyTax[$result2['tax_code']]['debit'] += $result2['ItemAmount'];
								$purchasebyTax[$result2['tax_code']]['fx_amount'] += $result2['ItemAmount'];
								$purchasebyTax[$result2['tax_code']]['fx_debit'] += $result2['ItemAmount'];
								$purchasebyTax[$result2['tax_code']]['total_amount'] += $result['TotalAmount'];
								$purchasebyTax[$result2['tax_code']]['tax_rate'] = $result2['tax_rate'];
								
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['accno'] = $result2['tax_account_code'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['desp'] = $result2['tax_account_name'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['date'] = $result['date'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['doc_no'] = $result['doc_no'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['batchno'] = $result['batchno'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['tax_code'] = $result2['tax_code'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['second_tax_code'] = $result2['second_tax_code'];
								
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['amount']+= $result2['TaxAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['debit']+= $result2['TaxAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['fx_amount']+= $result2['TaxAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['fx_debit']+= $result2['TaxAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['taxable']+= $result2['ItemAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['total_amount']+= $result['TotalAmount'];
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['fx_taxable'] = 0;
								$inputTaxValue[$result2['tax_code']][$result2['tax_account_code']][$result2['tax_account_name']]['tax_rate'] = $result2['tax_rate'];
								
							}					
						}

						$tmpSalesDb->sql_freeresult($ret2);
						if(isset($purchasebyTax))
						{
							foreach($purchasebyTax as $tax_code=>$value)	
							{
								$value["bill_type"] = "I";
								$upd = $this->set_report_fields($value, $data_column['master'], $form);
								my_fputcsv($fp, $upd, $form["delimiter"]);
								unset($upd);
								$this->inv_seq_num++;	
								$this->seq_num++;
							}
							unset($purchasebyTax);
						}
						
						if(isset($inputTaxValue))
						{
							foreach($inputTaxValue as $tax_code=>$value)
							{										
								foreach($value as $taxAccCode=>$val)
								{
									foreach($val as $taxAccName=>$val2)
									{					
										$val2["bill_type"] = "I";
										$upd = $this->set_report_fields($val2, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd);
										$this->inv_seq_num++;
										$this->seq_num++;
									}
								}
							}
							unset($inputTaxValue);
						}
						
						$tmp["accno"] = $result["vendor_code"];
						$tmp["desp"] = $result["vendor_name"];
						$tmp["doc_no"] = $result["doc_no"];
						$tmp["date"] = $result["date"];
						$tmp["batchno"] = $result["batchno"];
						$tmp["bill_type"] = "I";
						
						$tmp["amount"] = 0-$result["TotalAmount"];
						$tmp["credit"] = $result["TotalAmount"];
						$tmp["fx_amount"] = 0-$result["TotalAmount"];
						$tmp["fx_credit"] = $result["TotalAmount"];
						$tmp["total_amount"] = $result["TotalAmount"];
						$tmp["tax_rate"] = $result["tax_rate"];
						
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd,$tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
					}
					$tmpSalesDb->sql_freeresult($ret);	
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function create_account_payable($tmpSalesDb){
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
								`branch_id` integer default 0,
								`id` integer default 0,
								`batchno` varchar(20),
								`inv_no` char(150),
								`ref_no` char(150),
								`vendor_code` char(150),
								`vendor_id` char(150),
								`vendor_name` char(150),
								`vendor_brn` char(150),
								`vendor_gstno` char(150),
								`inv_date` date,
								`posting_date` date,
								`sku_desc` char(150),
								`gl_code` char(150),
								`gl_name` text,
								`department` char(150) default 0,
								`tax_code` char(30),
								`tax_rate` double default 0,
								`ItemAmount` double default 0,
								`TaxAmount` double default 0,
								`TotalAmount` double default 0,
								`currency_code` char(150) default 'XXX',
								`currency_amount` double default 0,
								`currency_gst_amount` double default 0,
								`receive_qty` double default 0,
								`vendor_terms` char(100),
								`item_cost` double default 0,
								`ym` date,
								`type` char(10),
								`arms_code` char(50),
								`account_code` char(100),
								`account_name` char(100),
								`purchase_account_code` char(100),
								`purchase_account_name` char(100),
								`tax_account_code` char(100),
								`tax_account_name` char(100),
								`second_tax_code` char(30),
								primary key(`branch_id`,`id`))"); //`job_code` char(150) default 0,
		$tmpSalesDb->sql_freeresult();
	}

	function update_account_payable($tmpSalesDb,$grn_db=null,$vendor_db=null,$sku_db=null,$where=array(),$branchCode){		
		global $config;
		$ret = $this->get_grn($grn_db,$where);
		$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);
		$second_tax_code_list = $this->get_second_tax_code_list();
		if($grn_db->sql_numrows($ret)>0)
		{
			
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($result = $this->sql_fetchrow($grn_db, $ret))
			{
				if($result['vendor_id']>0) $vendor = $this->get_vendor($vendor_db,$result);

				$accountings=$this->accSettings;
				//$accountings = load_account_file($rPos['branch_id']);
				$this->accSettings=$accountings;
				unset($accountings);

				$ref_no="GRR".sprintf("%05d",$result['grr_id']);
				$ym=date("Y-m-d",strtotime($result['doc_date']));
				
				$result['total_purchase_price_inc_gst'] = $result['amount'];
				$result['total_purchase_price_excl_gst'] = $result['amount']-$result['gst_amount'];
				$result['total_gst_amount'] = $result['gst_amount'];
				
				$invoice_date = $result['doc_date'];
				$posting_date = $result['rcv_date'];
				
				if(isset($result['branch_id'])) $upd["branch_id"] = $result['branch_id'];
				$upd["id"] = $rIdx;
				$upd["batchno"] = $this->get_batchno($invoice_date);
				$upd["inv_no"] = $result['doc_no'];
				$upd["ref_no"] = $ref_no;
				$upd["vendor_code"] = strval($vendor['code']);
				$upd["vendor_id"] = $vendor['id'];
				$upd["vendor_name"] = (isset($vendor['company_name']))?$vendor['company_name']:"";
				$upd["vendor_brn"] = (isset($vendor['company_no']))?$vendor['company_no']:"";
				$upd["vendor_gstno"] = (isset($vendor['gst_register_no']))?$vendor['gst_register_no']:"";
				$upd["vendor_terms"] = (isset($vendor['vendor_terms_code']))?$vendor['vendor_terms_code']:"";
				$upd["inv_date"] = $invoice_date;
				$upd["posting_date"] = $posting_date;
				$upd["arms_code"] = $sku['arms_code'];
				$upd["sku_desc"] = $sku['sku_desc'];
				$upd["gl_code"] = trim($vendor['account_payable_code']);
				$upd["gl_name"] = trim($vendor['account_payable_name']);
				$upd["purchase_account_code"] = $this->accSettings['purchase']['account_code'];
				$upd["purchase_account_name"] = $this->accSettings['purchase']['account_name'];
				$upd["receive_qty"] = $result['receive_qty'];
				$upd["item_cost"] = $result['item_cost'];
				if($result['gst_id'] == 0){
					$upd["tax_code"] = "NR";
					$upd["ItemAmount"] = $result['total_purchase_price_excl_gst'];
					$upd["TaxAmount"] = $this->selling_price_currency_format(0);
					$upd["TotalAmount"] =  $result['total_purchase_price_excl_gst'];
				}
				else{
					$upd["tax_code"] = $result['gst_code'];
					$upd["tax_rate"] = $result['gst_rate'];
					$upd["ItemAmount"] = $result['total_purchase_price_excl_gst'];
					$upd["TaxAmount"] = $result['total_gst_amount'];
					$upd["TotalAmount"] = $result['total_purchase_price_inc_gst'];
				}
				$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
				if(isset($this->accSettings[$upd["tax_code"]])){
					$acc_tax = $this->accSettings[$upd["tax_code"]];
					$upd["tax_account_code"] =  $acc_tax['account_code'];
					$upd["tax_account_name"] =  $acc_tax['account_name'];
				}
				$upd["ym"] = $ym;
				$upd["type"] = $result['type'];
				//$upd["job_code"] = $job;
				$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable.mysql_insert_by_field($upd));
				$rIdx++;
				
				unset($upd,$acc_tax);
				
				$grn_db->sql_freeresult($ret1);
				$cond[] = "branch_id = ".ms($result['branch_id']);
				$cond[] = "id = ".ms($result['grr_id']);
				$cond[] = "rcv_date = ".ms($result['rcv_date']);
				$where1 = "where ".implode(" and ",$cond);
				$this->sql_query($grn_db,"update grr set acc_is_exported = 1 ".$where1);
				unset($vendor,$invoice_date,$cond,$where1);
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
	}
}

?>
