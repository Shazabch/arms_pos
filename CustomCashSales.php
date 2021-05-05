<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

4/7/2017 13:00 Qiu Ying
- Bug fixed on sku description should not be shown because we are using group by receipt

7/31/2017 08:15 Qiu Ying
- Bug fixed on description cannot set to 'SALES' directly
- Bug fixed on showing wrong amount in grand total

8/15/2017 09:25 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type

8/16/2017 18:06 PM Qiu Ying
- Bug fixed on Second Tax code is empty in certain rows

2017-08-23 11:00 AM Qiu Ying
- Enhanced to add auto count as prelist template

11/2/2017 5:24 PM Andy
- Revert to hide Row Format "Master No Repeat".
- Inactive Auto Count Preset Cash Sales Format.
*/

include_once("CustomExportModule.php");
class CustomCashSales extends CustomExportModule
{
	function get_cash_sales($tmpSalesDb,$form){
		global $LANG;
		$dateTo = $form["date_to"];
		$branchCode = $form["branch_code"];
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
					$orderBy = ", ref_no";
					$groupBy = "pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name, tax_code";
					foreach ($data_column["master"] as $key => $item){
						if($item["field_type"] == "line_total" || $item["field_type"] == "rounding_adj" || $item["field_type"] == "grand_total"){
							$filter = " and acc_type <> 'rounding'";
							$groupBy = "tax_code";
							unset($orderBy);
						}
					}
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,account_name,customer_name,
												round(sum(ItemAmount),2) as SubTotal,second_tax_code,
												round(sum(TaxAmount),2) as TotalTaxAmount,
												round(sum(TotalAmount),2) as GrandTotal
												from ".$this->tmpTable ."
												where `type` = 'credit' and has_credit_notes = 0 
												group by pos_date, ref_no
												order by pos_date, ref_no");
												

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$ret2 = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where ref_no = ".ms($result['ref_no'])."
												and acc_type = ".ms("rounding"));
						if($tmpSalesDb->sql_numrows($ret2)>0)
						{
							$result1 = $this->sql_fetchrow($tmpSalesDb, $ret2);
							$rounding_adjustment = $result1['TotalAmount'];
							$result["GrandTotal"] = $result["GrandTotal"] - $rounding_adjustment;
						}
						$tmpSalesDb->sql_freeresult($ret2);
						unset($result1);
						
						$ret1 = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,account_name,customer_name,
												round(sum(ItemAmount),2) as ItemAmount,second_tax_code,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type` = 'credit' and has_credit_notes = 0 $filter
												and ref_no = ".ms($result['ref_no']). " 
												group by $groupBy
												order by pos_date $orderBy");
												
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$result2["tax_code"] = trim($result2['tax_code'])!=""?$result2['tax_code']:"NR";
							$result2["second_tax_code"] = trim($result2['second_tax_code'])!=""?$result2['second_tax_code']:"NR";
							$result2["SubTotal"] = $result["GrandTotal"];
							$result2["TotalTaxAmount"] = $result["TotalTaxAmount"];
							$result2["GrandTotal"] = $result["GrandTotal"] + $rounding_adjustment;
							$result2["rounding_adj"] = $rounding_adjustment;
							$paymentType=array();
							$retPayment = $this->sql_query($tmpSalesDb, "select round(sum(TotalAmount),2) as TotalAmount, description
														   from ".$this->tmpTable."
														   where `type` = 'debit'
														   and pos_date=".ms($result2['date'])."
														   and ref_no = ".ms($result2['ref_no'])."
														   and description not in ('Mix & Match Total Disc')
														   group by description");
							while($resultPayment = $this->sql_fetchrow($tmpSalesDb, $retPayment))
							{
								$paymentType[strtolower($resultPayment['description'])] = $resultPayment['TotalAmount'];
							}
							$tmpSalesDb->sql_freeresult($retPayment);
							if(count(array_keys($paymentType)) > 3)
							{
								$payment = 0;

								foreach($paymentType as $type=>$amount)
								{
									if($type!=="cash" && $type!=="credit_card")
									{
										$payment += $amount;
										unset($paymentType[$type]);
									}
								}
								$paymentType['Other'] = $payment;
							}
							$pt = array_keys($paymentType);
							for($p = 0; $p<3; $p++)
							{
								$tmp = $p+1;
								if(isset($pt[$p]))
								{
									$type = ($pt[$p]=="credit_card"?"Credit Card":$pt[$p]);
									if($p==0)
									{
										$result2["payment_type".$tmp] = $type;
										$result2["payment_amt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
									else
									{
										$result2["payment_type".$tmp] = $type;
										$result2["payment_amt".$tmp] = $this->selling_price_currency_format($paymentType[$pt[$p]]);
									}
								}
								else
								{
									if($p==0)
									{
										$result2["payment_type".$tmp] = $this->accSettings['payment']['type'];
										$result2["payment_amt".$tmp] = "";
									}
									else
									{
										$result2["payment_type".$tmp] = "";
										$result2["payment_amt".$tmp] = "";
									}
								}
							}
							$upd = $this->set_report_fields($result2, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret1);
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case "two_row":
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,second_tax_code,
												transferable,cancelled,customer_name,account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount,
												round(sum(ItemAmount),2) as SubTotal,
												round(sum(TaxAmount),2) as TotalTaxAmount,
												round(sum(TotalAmount),2) as GrandTotal
												from ".$this->tmpTable ."
												where `type` = 'credit' and has_credit_notes = 0 
												and acc_type != " . ms("rounding") . " and qty > 0
												group by pos_date, ref_no
												order by pos_date, ref_no");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$result["terms"] = $this->accSettings['terms']['account_code'];
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);
						
						$ret1 = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,second_tax_code,
												transferable,cancelled,account_name,customer_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type` = 'credit' and has_credit_notes = 0 
												and ref_no = ".ms($result['ref_no'])." and qty > 0
												group by pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name, tax_code
												order by pos_date");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$result2["terms"] = $this->accSettings['terms']['account_code'];
							$result2["SubTotal"] = $result["SubTotal"];
							$result2["TotalTaxAmount"] = $result["TotalTaxAmount"];
							$result2["GrandTotal"] = $result["GrandTotal"];
							$upd = $this->set_report_fields($result2, $data_column['detail'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret1);
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
				case "no_repeat_master":
					$master_column = array_merge($data_column['master'], $data_column["detail"]);
					foreach($data_column['master'] as $key => $item){
						unset($data_column['master'][$key]);
						$tmp_detail[$key]["field_type"] = "";
					}
					$detail_column = array_merge($tmp_detail, $data_column["detail"]);
					
					$ret = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,second_tax_code,
												transferable,cancelled,customer_name,account_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount,
												round(sum(ItemAmount),2) as SubTotal,
												round(sum(TaxAmount),2) as TotalTaxAmount,
												round(sum(TotalAmount),2) as GrandTotal
												from ".$this->tmpTable ."
												where `type` = 'debit' and has_credit_notes = 0
												group by pos_date, ref_no
												order by pos_date, ref_no");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$result["credit"] = "";
						$result["debit"] = $result["TotalAmount"];
						$result["taxable_cr"] = "";
						$result["taxable_dr"] = $result["ItemAmount"];
						$result["tax_cr"] = "";
						$result["tax_dr"] = "";
						$result["terms"] = $this->accSettings['terms']['account_code'];
						$upd = $this->set_report_fields($result, $master_column, $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);
						
						$ret1 = $this->sql_query($tmpSalesDb, "select customer_code, pos_date as date, doc_no, ref_no,
												tax_code, tax_rate, batchno, description, account_code,second_tax_code,
												transferable,cancelled,account_name,customer_name,
												round(sum(ItemAmount),2) as ItemAmount,
												round(sum(TaxAmount),2) as TaxAmount,
												round(sum(TotalAmount),2) as TotalAmount
												from ".$this->tmpTable."
												where `type` = 'credit' and has_credit_notes = 0 
												and ref_no = ".ms($result['ref_no'])."
												group by pos_date, batchno, doc_no, ref_no, acc_type, account_code, account_name, tax_code
												order by pos_date");
						while($result2 = $this->sql_fetchrow($tmpSalesDb, $ret1))
						{
							$result2["credit"] = $result2["TotalAmount"];
							$result2["debit"] = "";
							$result2["taxable_cr"] = $result2["ItemAmount"];
							$result2["taxable_dr"] = "";
							$result2["tax_cr"] = $result2["TaxAmount"];
							$result2["tax_dr"] = "";
							$result2["terms"] = $this->accSettings['terms']['account_code'];
							$result2["SubTotal"] = $result["SubTotal"];
							$result2["TotalTaxAmount"] = $result["TotalTaxAmount"];
							$result2["GrandTotal"] = $result["GrandTotal"];
							$upd = $this->set_report_fields($result2, $detail_column, $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd);
						}
						$tmpSalesDb->sql_freeresult($ret1);
					}
					$tmpSalesDb->sql_freeresult($ret);
					break;
			}
			fclose($fp);
		}
		return $total['total'];
	}

	function get_payment($tmpSalesDb,$form){
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
					$ret = $this->sql_query($tmpSalesDb, "select pos_date as date, branch_id, doc_no, ref_no, acc_type,
														customer_code, account_code, account_name, tax_code, description as 'payment_description',
														round(sum(ItemAmount),2) as ItemAmount,customer_name,
														round(sum(TaxAmount),2) as TaxAmount,
														round(sum(TotalAmount),2) as TotalAmount
														from ".$this->tmpTable."
														where `type`='debit'
														group by pos_date, ref_no, acc_type, account_code, account_name
														order by pos_date");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$upd = $this->set_report_fields($result, $data_column['master'], $form);
						my_fputcsv($fp, $upd, $form["delimiter"]);
						unset($upd);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
	function create_cash_sales($tmpSalesDb){
		$this->sql_query($tmpSalesDb,"drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb,"create table if not exists ".$this->tmpTable."(
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
						`arms_code` char(50),
						`qty` double default 0,
						`unit_price` double default 0,
						`ItemAmount` double default 0,
						`TaxAmount` double default 0,
						`TotalAmount` double default 0,
						`tax_code` char(30),
						`tax_rate` double default 0,
						`customer_code` char(50),
						`customer_name` char(50),
						`account_code` char(50),
						`account_name` char(50),
						`tax_account_code` char(50),
						`tax_account_name` char(50),
						`cancelled` char(1) default 'F',
						`transferable` char(1) default 'T',
						`customer_remark` text,
						`has_credit_notes` char(1) default 0,
						`second_tax_code` char(30),
						primary key(`tablename`,`branch_id`,`counter_id`,`id`,`pos_date`,`doc_no`,`ref_no`))");
		$tmpSalesDb->sql_freeresult();
	}

	function update_cash_sales($tmpSalesDb,$pos_db,$sku_db=null,$where=array())
	{
		global $LANG, $config;

		$credit_card = $this->credit_cards_type();
		$second_tax_code_list = $this->get_second_tax_code_list();
		$ret=$this->get_pos($pos_db, $where);
		
		if($pos_db->sql_numrows($ret) > 0){
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			$rIdx = $this->get_max_id($tmpSalesDb, $this->tmpTable);
			while($rPos = $this->sql_fetchrow($pos_db, $ret)){
				$accountings=$this->accSettings;
				$accountings = load_account_file($rPos['branch_id']);
				$this->accSettings=$accountings;
				unset($accountings);
				
				$rPos['pos_date'] = strtotime($rPos['date']);
				$receipt_no = $rPos['receipt_no'];

				if(trim($rPos['print_full_tax_invoice_remark'])!=""){
					$customer_remark = $rPos['print_full_tax_invoice_remark'];
				}
				elseif(trim($rPos['print_full_tax_invoice_remark'])=="" && trim($rPos['special_exempt_remark'])!=""){
					$customer_remark = $rPos['special_exempt_remark'];
				}
				else{
					$customer_remark = "";
				}
				if(isset($rPos['pos_more_info'])) $rPos['pos_more_info']=unserialize($rPos['pos_more_info']);
	
				$receipt_ref_no = $rPos['receipt_ref_no'];
				$posDate = date("Y-m-d",$rPos['pos_date']);
				$posYM = date("Y-m-01",$rPos['pos_date']);
	
				$ret1 = $this->get_pos_payment($pos_db, $rPos);
				if($pos_db->sql_numrows($ret1)>0){
					while($rPayment = $this->sql_fetchrow($pos_db, $ret1)){
						if(in_array($rPayment['type'],array_keys($credit_card))){
							$description = 'credit_card';
						}
						else{
							$description = strtolower($rPayment['type']);
						}
		
						if($description=='mix & match total disc' || $description=='discount') continue;
		
						$acc=$this->accSettings[$description];
						$cus_acc=$this->accSettings['customer_code'];
		
						$type=($description=='rounding')?"credit":"debit";
		
						if($description=='cash') $rPayment['amount']-=$rPos['amount_change'];
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPayment["branch_id"])) $upd["branch_id"] = $rPayment['branch_id'];
						$upd["counter_id"] = $rPayment['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
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
		
					unset($rPayment);
				}
				$pos_db->sql_freeresult($ret1);
				
				$short_over = round($rPos['amount_tender']-$rPos['amount']-$rPos['amount_change']-$rPos['service_charges'],2);
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
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
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
							$upd["doc_no"] = $receipt_no;
							$upd["ref_no"] = $receipt_ref_no;
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
					if($pos_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
							
							$has_credit_note = 0;
							if($rItem['qty']<0) {
								$retCN = $this->get_credit_note($pos_db,$where,false,false,$rItem);
								if($pos_db->sql_numrows($retCN)>0)
								{
									$has_credit_note = 1;
									
								}
								$pos_db->sql_freeresult($retCN);
							}
							if($sku_db!=null) $sku = $this->get_sku($sku_db, $rItem['sku_item_id']);
							$discount = $rItem['discount']+$rItem['discount2'];
		
							$acc = $this->accSettings['sales'];
							$cus_acc=$this->accSettings['customer_code'];
							
							$upd = array();
							$upd['has_credit_notes'] = $has_credit_note;
							$upd["tablename"] = "pos";
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
							$upd["description"] = ($sku?$sku['sku_desc']:$rItem['sku_description']);
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
	
				if(isset($rPos['pos_more_info']['deposit'])){
					foreach($rPos['pos_more_info']['deposit'] as $deposit){
						if(isset($deposit['gst_info'])) $deposit['gst_info']=unserialize($deposit['gst_info']);
		
						$acc=$this->accSettings['deposit'];
						$cus_acc=$this->accSettings['customer_code'];
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($deposit["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = 'debit';
						$upd["acc_type"] = 'deposit';
						$upd["description"] = "Deposit";
						$upd["ItemAmount"] = 0-$deposit['amount'];
						$upd["TotalAmount"] = 0-$deposit['amount'];
						$upd["customer_code"] = $cus_acc['account_code'];
						$upd["customer_name"] = $cus_acc['account_name'];
						$upd["account_code"] = $acc['account_code'];
						$upd["account_name"] = $acc['account_name'];
		
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
		
						$upd = array();
						$upd["tablename"] = "pos";
						$upd["batchno"] = $this->get_batchno($posDate);
						if(isset($rPos["branch_id"])) $upd["branch_id"] = $rPos['branch_id'];
						$upd["counter_id"] = $rPos['counter_id'];
						$upd["id"] = $rIdx;
						$upd["pos_date"] = $posDate;
						$upd["doc_no"] = $receipt_no;
						$upd["ref_no"] = $receipt_ref_no;
						$upd["ym"] = $posYM;
						$upd["type"] = "credit";
						$upd["acc_type"] = "deposit";
						$upd["description"] = "Deposit";
						$upd["qty"] = 1;
						$upd["unit_price"] = 0-round($deposit['amount'], 2);
						if(isset($deposit['gst_info']['code']) && $deposit['gst_info']['rate']!=""){
							$upd["tax_code"] = $deposit['gst_info']['code'];
							$upd["tax_rate"] = $deposit['gst_info']['rate'];
							$upd["ItemAmount"] = 0-($deposit['amount']-$deposit['gst_amount']);
							$upd["TaxAmount"] = 0-$deposit['gst_amount'];
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
					unset($deposit);
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
					$upd["doc_no"] = $receipt_no;
					$upd["ref_no"] = $receipt_ref_no;
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
	
		$ret=$this->get_do($pos_db,$where);
		if($pos_db->sql_numrows($ret)>0){
			if(method_exists($tmpSalesDb,'sql_begin_transaction')) $tmpSalesDb->sql_begin_transaction();
			while($rPos = $this->sql_fetchrow($pos_db, $ret)){
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
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
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
					while($rItem = $this->sql_fetchrow($pos_db, $ret1)){
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
					$upd["counter_id"] = $rPos['counter_id'];
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

				if($rPos['total_round_inv_amt'] != 0){
					$acc=$this->accSettings["rounding"];
					$cus_acc=$this->accSettings['customer_code'];

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
	}
}

?>
