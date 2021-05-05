<?php
/*
4/6/2017 10:15 AM Qiu Ying
- Bug fixed on missing batch no in ledger format

4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

5/2/2017 8:52 AM Khausalya
- Enhanced changes from MYR to use config setting. 

5/25/2017 16:31 Qiu Ying
- Enhanced to export credit note with multiple invoice

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

8/2/2017 09:12 AM Qiu Ying
- Enhanced to add second tax code
*/

include_once("CustomExportModule.php");
class CustomSalesAndCreditNote extends CustomExportModule{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_sales_n_credit_note($tmpSalesDb,$form)
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
				//Export Credit Sales From DO
				$ret = $this->sql_query($tmpSalesDb, "select tablename, pos_date, batchno, counter_id,
														doc_no, ref_no, is_credit_notes,customer_code, customer_name,
														round(sum(TotalAmount),2) as TotalAmount
														from ".$this->tmpTable." where acc_type	!= ".ms('sales return')."
														group by pos_date, doc_no, is_credit_notes,customer_code, customer_name order by pos_date,doc_no");		
				$this->seq_num = 1;
				while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
				{
					$this->inv_seq_num = 1;		
					$refno = $result['doc_no'];
					$refno2 = $result['doc_no'];
					
					$amount = $result['TotalAmount'];
					$tmp["accno"] =  $result['customer_code'];
					$tmp["doc_no"] = $refno;
					$tmp["date"] = $result['pos_date'];
					$tmp["ref_no"] = $refno2;
					$tmp["desp"] = $result['customer_name'];
					$tmp["amount"] = $amount;
					$tmp["debit"] = $amount;
					$tmp["fx_amount"] = $tmp["amount"];
					$tmp["fx_debit"] = $tmp["debit"];
					$tmp["bill_type"] = "H";
					$tmp["fx_rate"] = 1;
					$tmp["currency_code"] = $config["arms_currency"]["code"];
					$tmp["batchno"] = $result["batchno"];
					$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
					my_fputcsv($fp, $upd, $form["delimiter"]);
					unset($upd, $tmp);
					$this->inv_seq_num++;
					$this->seq_num++;
											
					$wcond[] = "`type`=".ms("credit");
					$wcond[] = "`pos_date`=".ms($result['pos_date']);
					$wcond[] = "`doc_no`=".ms($result['doc_no']);
					$wcond[] = "`acc_type`!=".ms("sales return");
					$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
										tax_code, tax_account_code, tax_account_name,batchno, tax_rate,second_tax_code,
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
						$tmp["accno"] =  $resultDetail['account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["ref_no"] = $refno2;
						$tmp["desp"] = $resultDetail['account_name'];
						$tmp["amount"] = 0-$amount;
						$tmp["credit"] = $amount;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_credit"] = $tmp["credit"];
						$tmp["tax_code"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
						$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$tmp["batchno"] = $resultDetail["batchno"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
						if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
						
						//Input Tax
						$tmp["accno"] =  $resultDetail['tax_account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["ref_no"] = $refno2;
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
						$tmp["batchno"] = $resultDetail["batchno"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
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
					$this->inv_seq_num = 1;				
					
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
					$tmp["accno"] =  $result['customer_code'];
					$tmp["doc_no"] = $refno;
					$tmp["date"] = $result['pos_date'];
					$tmp["ref_no"] = $refno2;
					$tmp["desp"] = $result['customer_name'];
					$tmp["amount"] = 0-$amount;
					$tmp["credit"] = $amount;
					$tmp["fx_amount"] = $tmp["amount"];
					$tmp["fx_credit"] = $tmp["credit"];
					$tmp["bill_type"] = "H";
					$tmp["fx_rate"] = 1;
					$tmp["currency_code"] = $config["arms_currency"]["code"];
					$tmp["batchno"] = $result["batchno"];
					$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
					my_fputcsv($fp, $upd, $form["delimiter"]);
					unset($upd, $tmp);
					$this->inv_seq_num++;
					$this->seq_num++;
		
					$wcond[] = "`pos_date`=".ms($result['pos_date']);
					$wcond[] = "`doc_no`=".ms($result['doc_no']);
					$wcond[] = "`return_receipt_no`=".ms($result['return_receipt_no']);

					$retDetail = $this->sql_query($tmpSalesDb, "select pos_date, doc_no, ref_no, acc_type, account_code, account_name,
										tax_code, tax_account_code, tax_account_name,batchno, tax_rate,second_tax_code,
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
						$tmp["accno"] =  $resultDetail['account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["ref_no"] = $refno2;
						$tmp["desp"] = $resultDetail['account_name'];
						$tmp["amount"] = $amount;
						$tmp["debit"] = $amount;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_debit"] = $tmp["debit"];
						$tmp["tax_code"] = trim($resultDetail['tax_code'])=="NR"?"":$resultDetail['tax_code'];
						$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$tmp["batchno"] = $resultDetail["batchno"];
						$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd, $tmp);
						$this->inv_seq_num++;
						$this->seq_num++;
						if($resultDetail['tax_code']=="NR" || strtolower($resultDetail['acc_type'])=='rounding')continue;
						
						//Input Tax
						$tmp["accno"] =  $resultDetail['tax_account_code'];
						$tmp["doc_no"] = $refno;
						$tmp["date"] = $result['pos_date'];
						$tmp["ref_no"] = $refno2;
						$tmp["desp"] = $resultDetail['tax_account_name'];
						$tmp["amount"] = $taxAmt;
						$tmp["debit"] = $taxAmt;
						$tmp["fx_amount"] = $tmp["amount"];
						$tmp["fx_debit"] = $tmp["debit"];
						$tmp["tax_code"] = $resultDetail['tax_code'];
						$tmp["second_tax_code"] = $resultDetail['second_tax_code'];
						$tmp["tax_rate"] = $resultDetail['tax_rate'];
						$tmp["taxable"] = $amount;
						$tmp["fx_taxable"] = $amount;
						$tmp["bill_type"] = "H";
						$tmp["fx_rate"] = 1;
						$tmp["currency_code"] = $config["arms_currency"]["code"];
						$tmp["batchno"] = $resultDetail["batchno"];
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
	
	function create_sales_credit_note($tmpSalesDb)
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
										`currencyrate` double default 1,
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
										`customer_brn` char(150),
										`customer_gst_no` char(150),
										`address` text,
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
										`second_tax_code` char(30),
										primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
		$tmpSalesDb->sql_freeresult();
	}
	
	function update_sales_credit_note($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config;
		$credit_card = $this->credit_cards_type();
		$second_tax_code_list = $this->get_second_tax_code_list();
		$ret = $this->get_do($pos_db,$where,array('transfer','credit_sales'));
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
				if($rPos['do_type']=='transfer'){
					$branch=$this->get_branch($pos_db,$rPos['do_branch_id']);
					$debtor=array('code'=>$branch['account_code_debtor'],
								'description'=>$branch['description'],
								'address'=>$branch['address'],
								'terms'=>$branch['con_terms'],
								'account_receivable_code'=>$branch['account_receivable_code'],
								'account_receivable_name'=>$branch['account_receivable_name'],
								'brn'=>$branch['company_no'],
								'gst_register_no'=>$branch['gst_register_no']);
				}
				else{
					$debtor = $this->get_debtor($pos_db,$rPos['debtor_id']);
					$customer_remark = $debtor['description'];
					$customer_brn = $debtor['company_no'];
				}
				
				$ret1=$this->get_do_items($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1) > 0){
					$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
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
					
						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
					
								$upd["account_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account_name'];
							}
						}

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
					
						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["account_code"] = $debtor['account_receivable_code'];
							$upd["account_name"] = $debtor['account_receivable_name'];

							if(isset($this->accSettings['credit_sales'])){
								if($upd["account_code"]=="") $upd["account_code"]=$this->accSettings['credit_sales']['account_code'];
								if($upd["account_name"]=="") $upd["account_name"]=$this->accSettings['credit_sales']['account_name'];
							}
						}
						else{
							$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_code']:"";
							$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_name']:"";
							$upd["address"] = $rDo['open_info']['address'];
							if(isset($this->accSettings['terms'])) $upd["terms"] = $this->accSettings['terms']['account_code'];
							if(isset($this->accSettings['credit_sales'])){
					
								$upd["account_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account_name'];
							}
						}
						
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
			}
			if(method_exists($tmpSalesDb,'sql_commit')) $tmpSalesDb->sql_commit();
		}
		$pos_db->sql_freeresult($ret);	
		
		//Credit Notes 
		$org_where = $where;
		//$where[] = "do.do_type in ('".implode("','",array('transfer','credit_sales'))."')";
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
				
				$ret1 = $this->get_credit_note_items($pos_db, $rCN ,true);
				if($pos_db->sql_numrows($ret1) > 0)
				{
					$is_exported = false;
					while($rItem = $this->sql_fetchrow($pos_db, $ret1))
					{
						if($rItem["do_type"] == 'open'){
							continue;
						}
						$is_exported = true;
						$retDo = $this->get_cn_inv_infor($pos_db,$rItem);
						if($pos_db->sql_numrows($retDo)>0)						
						{
							$rInv = $pos_db->sql_fetchrow($retDo);
							if($rInv['do_type']=='transfer')
							{
								$branch = $this->get_branch($pos_db,$rInv['do_branch_id']);
								$debtor=array('code'=>$branch['account_code_debtor'],
											'description'=>$branch['description'],
											'address'=>$branch['address'],
											'terms'=>$branch['con_terms'],
											'account_receivable_code'=>$branch['account_receivable_code'],
											'account_receivable_name'=>$branch['account_receivable_name'],
											'brn'=>$branch['company_no'],
											'gst_register_no'=>$branch['gst_register_no']);
							}
							else
							{
								$debtor = $this->get_debtor($pos_db,$rInv['debtor_id']);		
							}
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
							$upd["currencyrate"] = $rItem['exchange_rate'];
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