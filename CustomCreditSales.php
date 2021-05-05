<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

4/7/2017 8:48 AM Qiu Ying
- Bug fixed on sku description should not be shown because we are using group by receipt

5/2/2017 8:21 AM Khausalya
- Enhanced changs from MYR to use config setting.

6/12/2017 14:41 Qiu Ying
- Bug fixed on add tax rate in all data type

8/15/2017 09:35 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type
*/
include_once("CustomExportModule.php");
class CustomCreditSales extends CustomExportModule
{
	var $seq_num = 0;
	var $inv_seq_num = 0;
	
	function get_account_receiver($tmpSalesDb, $form){
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
				case "two_row":
					$ret = $this->sql_query($tmpSalesDb, "select do_date, inv_no, customer_code, customer_name, 
									account_code, account_name,foreign_currency_code,
									tax_code, currency_rate, terms,batchno,tax_rate,second_tax_code,
									uom, suomqty,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount,
									round(sum(ItemFAmount),2) as ItemFAmount,
									round(sum(TaxFAmount),2) as TaxFAmount,
									round(sum(TotalFAmount),2) as TotalFAmount
									from ".$this->tmpTable."
									group by do_date, inv_no, customer_code, account_code, account_name
									order by do_date, inv_no");
					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						if($form["row_format"] == "two_row"){
							$tmp["date"] = $result["do_date"];
							$tmp["inv_no"] = $result["inv_no"];
							$tmp["customer_code"] = $result["customer_code"];
							$tmp["customer_name"] = $result["customer_name"];
							$tmp["account_code"] = $result["account_code"];
							$tmp["account_name"] = $result["account_name"];
							$tmp["terms"] = $result["terms"];
							$tmp["currency_rate"] = $result["currency_rate"];
							$tmp["uom"] = $result["uom"];
							$tmp["suomqty"] = $result["suomqty"];
							//$tmp["tax_code"] = $result["tax_code"];
							$tmp["SubTotal"] = $tmp["ItemAmount"] = ($result['currency_rate']>1)?$result['ItemFAmount']:$result['ItemAmount'];
							$tmp["TotalTaxAmount"] = $tmp["TaxAmount"] = ($result['currency_rate']>1)?$result['TaxFAmount']:$result['TaxAmount'];
							$tmp["GrandTotal"] = $tmp["TotalAmount"] = $result['TotalAmount'];
							$upd = $this->set_report_fields($tmp, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd,$tmp);
						}
						
						$ret1 = $this->sql_query($tmpSalesDb, "select do_date, inv_no, customer_code, customer_name, 
									account_code, account_name,foreign_currency_code,
									tax_code, currency_rate, terms,batchno,tax_rate,second_tax_code,
									uom, suomqty,
									round(sum(ItemAmount),2) as ItemAmount,
									round(sum(TaxAmount),2) as TaxAmount,
									round(sum(TotalAmount),2) as TotalAmount,
									round(sum(ItemFAmount),2) as ItemFAmount,
									round(sum(TaxFAmount),2) as TaxFAmount,
									round(sum(TotalFAmount),2) as TotalFAmount
									from ".$this->tmpTable."
									where inv_no = " . ms($result["inv_no"]) . "
									group by do_date, inv_no, customer_code, account_code, account_name,tax_code
									order by do_date, inv_no");
						while($result1 = $this->sql_fetchrow($tmpSalesDb,   $ret1))
						{
							if($form["row_format"] == "single_line"){
								$data_column_type = $data_column['master'];
								$tmp["ItemAmount"] = $result1['ItemAmount'];
								$tmp["TaxAmount"] = $result1['TaxAmount'];
								$tmp["TotalAmount"] = $result1['TotalAmount'];
								$tmp["TotalTaxAmount"] = $result["TaxAmount"];
								$tmp["SubTotal"] = $result["ItemAmount"];
								$tmp["GrandTotal"] = $result["TotalAmount"];
								$tmp["foreign_currency_code"] = $result1["foreign_currency_code"];
								$tmp["batchno"] = $result["batchno"];
								$tmp["payment_type1"] = $this->accSettings['tax_gl_account']['account_code'];
								$tmp["payment_amt1"] = $result1["TotalAmount"];
							}else{
								$data_column_type = $data_column['detail'];
								$tmp["ItemAmount"] = ($result1['currency_rate']>1)?$result1['ItemFAmount']:$result1['ItemAmount'];
								$tmp["TaxAmount"] = ($result1['currency_rate']>1)?$result1['TaxFAmount']:$result1['TaxAmount'];
								$tmp["TotalAmount"] = ($result1['currency_rate']>1)?$result1['TotalFAmount']:$result1['TotalAmount'];
							}
							$tmp["date"] = $result1["do_date"];
							$tmp["inv_no"] = $result1["inv_no"];
							$tmp["customer_code"] = $result1["customer_code"];
							$tmp["customer_name"] = $result1["customer_name"];
							$tmp["account_code"] = $result1["account_code"];
							$tmp["account_name"] = $result1["account_name"];
							$tmp["terms"] = $result1["terms"];
							$tmp["currency_rate"] = $result1["currency_rate"];
							$tmp["uom"] = $result1["uom"];
							$tmp["suomqty"] = $result1["suomqty"];
							$tmp["tax_code"] = $result1["tax_code"];
							$tmp["tax_rate"] = $result1["tax_rate"];
							$tmp["second_tax_code"] = $result1["second_tax_code"];
							
							$upd = $this->set_report_fields($tmp, $data_column_type, $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $tmp);
						}
						$tmpSalesDb->sql_freeresult($ret1);
					}
					$tmpSalesDb->sql_freeresult($ret);
				break;
				case "ledger_format":
					$ret = $this->sql_query($tmpSalesDb, "select batchno, do_date, inv_no, customer_code, customer_name, account_code, account_name,
                                tax_code, tax_account_code, tax_account_name, currency_rate, foreign_currency_code, terms, tax_rate,
                                round(sum(ItemAmount),2) as ItemAmount,
                                round(sum(TaxAmount),2) as TaxAmount,
                                round(sum(TotalAmount),2) as TotalAmount,
                                round(sum(ItemFAmount),2) as ItemFAmount,
                                round(sum(TaxFAmount),2) as TaxFAmount,
                                round(sum(TotalFAmount),2) as TotalFAmount
                                from ".$this->tmpTable."
                                group by do_date, inv_no, customer_code, account_code, account_name, tax_code
                                order by do_date, inv_no");

					while($result = $this->sql_fetchrow($tmpSalesDb, $ret))
					{
						$invNo=$result['inv_no'];
						if(!isset($tmpSalesDetail[$invNo]))
						{
							$tmpSalesDetail[$invNo]=array();
							$tmpSalesDetail[$invNo]['batchno']=$result['batchno'];
							$tmpSalesDetail[$invNo]['do_date']=$result['do_date'];
							$tmpSalesDetail[$invNo]['currency_rate']=$result['currency_rate'];
							$tmpSalesDetail[$invNo]['currency_code']=$result['foreign_currency_code'];
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

						if($result['tax_code'])
						{
							if(!isset($tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']])){
								$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] = 0;
								$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] = 0;
							}

							$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditamt'] += $result['ItemAmount'];
							$tmpSalesDetail[$invNo]['sales'][$result['account_code']][$result['account_name']][$result['tax_code']]['creditfamt'] += $result['ItemFAmount'];

							if(!isset($tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']])){
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_code'] = $result['tax_code'];
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['tax_rate'] = $result['tax_rate'];
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] = 0;
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditamt'] = 0;
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] = 0;
								$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditfamt'] = 0;
							}

							$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditamt'] += $result['TaxAmount'];
							$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditamt'] += $result['ItemAmount'];
							$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['creditfamt'] += $result['TaxFAmount'];
							$tmpSalesDetail[$invNo]['sales'][$result['tax_account_code']][$result['tax_account_name']][$result['tax_code']]['total_creditfamt'] += $result['ItemFAmount'];
						}
						else
						{
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
					$this->seq_num = 1;
					foreach($tmpSalesDetail as $invNo=>$salesDetail)
					{
						$this->inv_seq_num = 1;
						$doDate=$salesDetail['do_date'];
						$docNo = sprintf("%07d",$bn);
						$refno = $salesDetail['batchno'];

						if(isset($salesDetail['payment']))
						{
							$result["accno"] = $salesDetail['payment']['account_code'];
							$result["doc_no"] = $docNo;
							$result["date"] = $doDate;
							$result["batchno"] = $refno;
							$result["ref_no"] = $invNo;
							$result["desp"] = $salesDetail['payment']['account_name'];
							$result["amount"] = $salesDetail['payment']['debitamt'];
							$result["debit"] = $salesDetail['payment']['debitamt'];
							$result["fx_amount"] = ($salesDetail['payment']['debitfamt']>0)?$salesDetail['payment']['debitfamt']:$salesDetail['payment']['debitamt'];
							$result["fx_debit"] = ($salesDetail['payment']['debitfamt']>0)?$salesDetail['payment']['debitfamt']:$salesDetail['payment']['debitamt'];
							$result["fx_rate"] = $salesDetail['currency_rate'];
							$result["currency_code"] = ($salesDetail['currency_code']!="XXX"?$salesDetail['currency_code']:$config["arms_currency"]["code"]);
							$result["bill_type"] = "I";
							$upd = $this->set_report_fields($result, $data_column['master'], $form);
							my_fputcsv($fp, $upd, $form["delimiter"]);
							unset($upd, $result);
							$this->inv_seq_num++;
							$this->seq_num++;
						}

						foreach($salesDetail['sales'] as $acc_code=>$value){
							if(is_array($value))
							{
								foreach($value as $acc_name=>$value2)
								{
									foreach($value2 as $tax_name=>$val)
									{
										if($tax_name=='other' || $val['tax_code']!="") $tax_name="";

										$tax_code = (isset($val['tax_code']) && $val['tax_code']!="")?$val['tax_code']:"";

										$result["accno"] = $acc_code;
										$result["doc_no"] = $docNo;
										$result["date"] = $doDate;
										$result["batchno"] = $refno;
										$result["ref_no"] = $invNo;
										$result["desp"] = $acc_name;
										$result["desp2"] = $tax_name;
										$result["amount"] = 0-$val['creditamt'];
										$result["credit"] = $val['creditamt'];
										$result["fx_amount"] = (0-(($val['creditfamt']>0)?$val['creditfamt']:$val['creditamt']));
										$result["fx_credit"] = (($val['creditfamt']>0)?$val['creditfamt']:$val['creditamt']);
										$result["fx_rate"] = $salesDetail['currency_rate'];
										$result["currency_code"] = ($salesDetail['currency_code']!="XXX"?$salesDetail['currency_code']:$config["arms_currency"]["code"]);
										$result["tax_code"] = $tax_code;
										$result["tax_rate"] = $val['tax_rate'];
										$result["taxable"] = ($tax_code!="")?$val['total_creditamt']:0;
										$result["fx_taxable"] = ($tax_code!="")?(($val['total_creditfamt']>0)?$val['total_creditfamt']:$val['total_creditamt']):0;
										$result["bill_type"] = "I";
										$upd = $this->set_report_fields($result, $data_column['master'], $form);
										my_fputcsv($fp, $upd, $form["delimiter"]);
										unset($upd, $result);
										$this->inv_seq_num++;
										$this->seq_num++;
									}
								}
							}
						}
						$bn++;
					}
					unset($tmpSalesDetail);
				break;
			}
			fclose($fp);
		}
		return $total['total'];
	}
	
    function create_account_receiver($tmpSalesDb){
		$this->sql_query($tmpSalesDb, "drop table if exists ".$this->tmpTable);
		$this->sql_query($tmpSalesDb, "create table if not exists ".$this->tmpTable."(
									`branch_id` integer default 0,
									`id` integer default 0,
									`batchno` varchar(20),
									`customer_code` char(150),
									`customer_name` char(150),
									`customer_brn` char(150),
									`customer_gst_no` char(150),
									`inv_no` char(150),
									`docnoex` char(150),
									`do_date` date,
									`terms` char(150),
									`currency_rate` double default 1,
									`acc_description` char(150),
									`sku_description` char(150),
									`sku_cat_desc` char(150),
									`qty` double default 0,
									`uom` char(150) default 'UNIT',
									`suomqty` double default 0,
									`unit_price` double default 0,
									`disc` char(150),
									`tax_code` char(30),
									`ItemAmount` double default 0,
									`TaxAmount` double default 0,
									`TotalAmount` double default 0,
									`acc_sales_code` char(150),
									`remark1` text,
									`remark2` text,
									`country` char(150),
									`address` text,
									`ItemFAmount` double default 0,
									`TaxFAmount` double default 0,
									`TotalFAmount` double default 0,
									`foreign_currency_code` char(150) default 'XXX',
									`foreign_currency_amount` double default 0,
									`foreign_currency_gst_amount` double default '0',
									`tax_rate` double default 0,
									`customer_remark` text,
									`arms_code` char(20),
									`selling_price_inc_gst` double default 0,
									`ym` date,
									`account_code` char(100),
									`account_name` char(100),
									`tax_account_code` char(100),
									`tax_account_name` char(100),
									`second_tax_code` char(30),
									primary key(`branch_id`,`id`,`do_date`,`inv_no`))");
		$tmpSalesDb->sql_freeresult();
	}

	function update_account_receiver($tmpSalesDb,$do_db,$sku_db=null,$where=array()){
		global $config;

		$ret=$this->get_do($do_db,$where,array('transfer','credit_sales'));
		$second_tax_code_list = $this->get_second_tax_code_list();
		if($do_db->sql_numrows($ret)>0)
		{
			$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);

			while($rDo = $this->sql_fetchrow($do_db, $ret))
			{
				$orgDoDate = $rDo['do_date'];
				$rDo['do_date']=strtotime($rDo['do_date']);
				$doDate = date("Y-m-d",$rDo['do_date']);

				if($rDo['do_type']=='transfer'){
					$branch=$this->get_branch($do_db,$rDo['do_branch_id']);
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
					$debtor=$this->get_debtor($do_db,$rDo['debtor_id']);
					$customer_remark = $debtor['description'];
					$customer_brn = $debtor['company_no'];
				}
				
				$rDo['total']=0;
				$ret1=$this->get_do_items($do_db,$rDo);

				if($do_db->sql_numrows($ret1)>0){
					while($rItem = $this->sql_fetchrow($do_db, $ret1)){
						$accountings=$this->accSettings;
						$accountings = load_account_file($rDo['branch_id']);
						$this->accSettings=$accountings;
						unset($accountings);

						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

						$upd["branch_id"] = $rDo['branch_id'];
						$upd["id"] = $rIdx;
						$upd["batchno"] = $this->get_batchno($doDate);

						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["acc_sales_code"] = $debtor['account_receivable_code'];
							$upd["acc_description"] = $debtor['account_receivable_name'];
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
								$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["acc_description"] = $this->accSettings['credit_sales']['account_name'];
			
								$upd["account_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account_name'];
							}
						}

						$upd["inv_no"] = $rDo['inv_no'];
						$upd["do_date"] = $doDate;
						$upd["sku_description"] = $sku['sku_desc'];
						$upd["sku_cat_desc"] = $sku['category_desc'];
						$upd["arms_code"] = $sku['arms_code'];

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$upd["qty"] = $rItem['row_qty'];

						//if($rItem['gst_code'] && $rItem['gst_rate']>0){
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
							$upd["tax_rate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}

						$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['row_qty']),2);

						$upd["customer_remark"] = $customer_remark;

						$upd["ym"] = date("Y-m-01",$rDo['do_date']);
					
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
					}
					$cond[] = "id = ".ms($rDo['id']);
					$cond[] = "branch_id = ".ms($rDo['branch_id']);
					$cond[] = "do_no = ".ms($rDo['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($do_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}	
				$do_db->sql_freeresult($ret1);
				
				$ret1=$this->get_do_open_items($do_db,$rDo);

				if($do_db->sql_numrows($ret1)>0){
					while($rItem = $this->sql_fetchrow($do_db, $ret1)){
						$accountings=$this->accSettings;
						$accountings = load_account_file($rDo['branch_id']);
						$this->accSettings=$accountings;
						unset($accountings);

						if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

						$upd["branch_id"] = $rDo['branch_id'];
						$upd["id"] = $rIdx;
						$upd["batchno"] = $this->get_batchno($doDate);

						if(isset($debtor)){
							$upd["customer_code"] = $debtor["code"];
							$upd["customer_name"] = $debtor["description"];
							$upd["customer_brn"] = $debtor["company_no"];
							$upd["customer_gst_no"] = (isset($debtor["gst_register_no"])?$debtor["gst_register_no"]:"");
							$upd["address"] = $debtor["address"];
							$upd["terms"] = $debtor['term'];
							$upd["acc_sales_code"] = $debtor['account_receivable_code'];
							$upd["acc_description"] = $debtor['account_receivable_name'];
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
								$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["acc_description"] = $this->accSettings['credit_sales']['account_name'];
			
								$upd["account_code"] = $this->accSettings['credit_sales']['account_code'];
								$upd["account_name"] = $this->accSettings['credit_sales']['account_name'];
							}
						}

						$upd["inv_no"] = $rDo['inv_no'];
						$upd["do_date"] = $doDate;
						$upd["sku_description"] = $sku['sku_desc'];
						$upd["sku_cat_desc"] = $sku['category_desc'];
						$upd["arms_code"] = $sku['arms_code'];

						$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
						$upd["qty"] = $rItem['row_qty'];

						//if($rItem['gst_code'] && $rItem['gst_rate']>0){
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
							$upd["tax_rate"] = 0;
							$upd["TaxAmount"] = 0;
							$upd["unit_price"] = round(($rItem['inv_line_gross_amt2']/$rItem['row_qty']),2);
							$upd["ItemAmount"] = $upd["TotalAmount"] = round($rItem['inv_line_gross_amt2'],2);
						}
						$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
						if(isset($this->accSettings[$upd["tax_code"]])){
							$acc_tax = $this->accSettings[$upd["tax_code"]];
							$upd["tax_account_code"]=$acc_tax['account_code'];
							$upd["tax_account_name"]=$acc_tax['account_name'];
						}

						$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['row_qty']),2);

						$upd["customer_remark"] = $customer_remark;

						$upd["ym"] = date("Y-m-01",$rDo['do_date']);
					
						$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
						unset($upd);
						$rIdx++;
					}
					$cond[] = "id = ".ms($rDo['id']);
					$cond[] = "branch_id = ".ms($rDo['branch_id']);
					$cond[] = "do_no = ".ms($rDo['do_no']);
					$cond[] = "do_date = ".ms($orgDoDate);
					$where1 = "where ".implode(" and ",$cond);					
					$this->sql_query($do_db,"update do set acc_is_exported = 1 ".$where1);
					unset($cond,$where1);
				}	
				$do_db->sql_freeresult($ret1);
			}
		}

		if($config['consignment_modules']){
			$ret=$this->get_ci($do_db);
			if($do_db->sql_numrows($ret)>0){
				$rIdx = $this->get_max_id($tmpSalesDb,$this->tmpTable);

				while($rDo = $this->sql_fetchrow($do_db, $ret)){
					$accountings=$this->accSettings;
					$accountings = load_account_file($rDo['branch_id']);
					$this->accSettings=$accountings;
					unset($accountings);

					if($rDo['discount_percent']>0){
						$total_discount=$rDo['sub_total_amt'];
						$this->cal_discount($total_discount,$rDo['discount_percent']);
						$total_discount_per=$total_discount/$rDo['sub_total_amt'];
					}
					else{
						$total_discount=0;
						$total_discount_per=0;
					}
					$orgDoDate = $rDo['ci_date'];
					$rDo['ci_date']=strtotime($rDo['ci_date']);
					$doDate = date("Y-m-d",$rDo['ci_date']);

					$debtor=$this->get_branch($do_db,$rDo['ci_branch_id']);

					$rDo['total']=0;
					$ret1=$this->get_ci_items($do_db,$rDo);
					
					if($do_db->sql_numrows($ret1)>0){
						while($rItem = $this->sql_fetchrow($do_db, $ret1)){
							if($sku_db!=null) $sku = $this->get_sku($sku_db,$rItem['sku_item_id']);

							$upd["branch_id"] = $rDo['branch_id'];
							$upd["id"] = $rIdx;
							$upd["batchno"] = $this->get_batchno($doDate);

							if(isset($debtor)){
								$upd["customer_code"] = $debtor["code"];
								$upd["customer_name"] = $debtor["description"];
								$upd["address"] = $debtor["address"];
								$upd["terms"] = $debtor["con_terms"];
								$upd["acc_sales_code"] = $debtor['account_receivable_code'];
								$upd["acc_description"] = $debtor['account_receivable_name'];
								$upd["account_code"] = $debtor['account_receivable_code'];
								$upd["account_name"] = $debtor['account_receivable_name'];
							}
							else{
								$upd["customer_code"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_code']:"";
								$upd["customer_name"] = isset($this->accSettings['customer_code'])?$this->accSettings['customer_code']['account_name']:"";
								$upd["address"] = "";

								if(isset($this->accSettings['terms']) && $upd["terms"]=="") $upd["terms"] = $this->accSettings['terms']['account_code'];
								if(isset($this->accSettings['credit_sales'])) {
									$upd["acc_sales_code"] = $this->accSettings['credit_sales']['account_code'];
									$upd["acc_description"] = $this->accSettings['credit_sales']['account_name'];
									$upd["account_code"] = $this->accSettings['credit_sales']['account_code'];
									$upd["account_name"] = $this->accSettings['credit_sales']['account_code'];
								}
							}

							$upd["inv_no"] = $rDo['ci_no'];
							$upd["do_date"] = $doDate;
							$upd["sku_description"] = $sku['sku_desc'];
							$upd["sku_cat_desc"] = $sku['category_desc'];
							$upd["arms_code"] = $sku['arms_code'];

							$rItem['row_qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
							$upd["qty"] = $rItem['row_qty'];

							$upd["unit_price"] = round($rItem['cost_price'],2);
							if($rItem['gst_code'] && $rItem['gst_rate']!=""){
								$upd["tax_code"] = $rItem['gst_code'];
								$upd["tax_rate"] = $rItem['gst_rate'];
								$upd["ItemAmount"] = round($rItem['item_amt2'],2);
								$upd["TaxAmount"] = round($rItem['item_gst2'],2);
								$upd["TotalAmount"] = round($rItem["item_gst_amt2"],2);

								$upd["ItemFAmount"] = round($rItem['item_foreign_amt2'],2);
								$upd["TaxFAmount"]=round($rItem['item_foreign_gst_amt2']-$rItem['item_foreign_amt2'],2);
								$upd["TotalFAmount"] = round($rItem['item_foreign_gst_amt2'],2);
				
								$upd["foreign_currency_gst_amount"]=round($rItem['item_foreign_gst_amt2'],2);
								$upd["foreign_currency_amount"]=round($rItem['item_foreign_amt2'],2);
				
								$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['qty']),2);
							}
							else{
								$ItemAmount=$rItem['cost_price']*$upd["qty"];
				
								if($rDo['discount_selling_price_percent']!=""){
									$this->cal_discount($ItemAmount,$rDo['discount_selling_price_percent']);
								}
				
								if($rDo['discount_item_row_percent']!=""){
									$this->cal_discount($ItemAmount,$rDo['discount_item_row_percent']);
								}
				
								if($total_discount_per>0){
									$ItemAmount=round($ItemAmount*$total_discount_per,4);
								}

								$upd["tax_code"] = "NR";
								$upd["tax_rate"] = 0;
								$upd["TaxAmount"] = 0;
								$upd["ItemAmount"] = $upd["TotalAmount"] = $ItemAmount;

								$ItemFAmount=$rItem['foreign_cost_price']*$upd["qty"];

								if($rDo['discount_selling_price_percent']!=""){
									$this->cal_discount($ItemFAmount,$rDo['discount_selling_price_percent']);
								}

								if($rDo['discount_item_row_percent']!=""){
									$this->cal_discount($ItemFAmount,$rDo['discount_item_row_percent']);
								}

								if($total_discount_per>0){
									$ItemFAmount=round($ItemFAmount*$total_discount_per,4);
								}

								$upd["TaxFAmount"] = 0;
								$upd["ItemFAmount"] = $upd["TotalFAmount"] = $ItemFAmount;
				
								$upd["foreign_currency_gst_amount"]=round($ItemFAmount,2);
								$upd["foreign_currency_amount"]=round($ItemFAmount,2);
								$upd['selling_price_inc_gst'] = round(($upd["TotalAmount"]/$rItem['qty']),2);
							}
							$upd["second_tax_code"] = ($second_tax_code_list[$upd["tax_code"]]?$second_tax_code_list[$upd["tax_code"]]:$upd["tax_code"]);
							if(isset($this->accSettings[$upd["tax_code"]])){
								$acc_tax = $this->accSettings[$upd["tax_code"]];
								$upd["tax_account_code"]=$acc_tax['account_code'];
								$upd["tax_account_name"]=$acc_tax['account_name'];
							}

							if($rDo['exchange_rate']>0) $upd["currency_rate"]=$rDo['exchange_rate'];

							if(isset($debtor['currency_code'])) $upd['foreign_currency_code']=$debtor['currency_code'];
							$upd["customer_remark"] = "";

							$upd["ym"] = date("Y-m-01",$rDo['ci_date']);

							$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
							unset($upd);
							$rIdx++;
						}
						$cond[] = "id = ".ms($rDo['id']);
						$cond[] = "branch_id = ".ms($rDo['branch_id']);
						$cond[] = "ci_no = ".ms($rDo['ci_no']);
						$cond[] = "ci_date = ".ms($orgDoDate);
						$where1 = "where ".implode(" and ",$cond);											
						$this->sql_query($do_db,"update ci set acc_is_exported = 1 ".$where1);
						unset($cond,$where1);
					}					
				}
			}
		}
	}

}

?>
