<?php
/*
4/6/2017 13:18 Qiu Ying
- Bug fixed on the file cannot view properly after download

5/8/2017 9:06 AM Khausalya
-Enhanced changes from MYR to use config setting. 

5/25/2017 16:31 Qiu Ying
- Enhanced to export credit note with multiple invoice

6/1/2017 16:24 Qiu Ying
- Bug fixed on branch code not showing after export

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

6/20/2017 11:14 AM Qiu Ying
- Bug fixed on amount showing wrong when export credit note

8/1/2017 11:10 AM Qiu Ying
- Enhanced to add second tax code
*/

require_once("language.php");
abstract class CustomExportModule
{
	var $sys=null;
	var $db;
	var $tmpTable = null;
	var $tmpFile = null;
	var $tmpPaymentFile2 = null;
	var $tmpExportFileName = null;
	var $tmpExportFileNamePT = null;
	var $folder = null;
	var $accSettings = array();
	var $dataFrom = null;
	var $dataTo = null;
	var $debug=0;
	var $debug_file=0;
	var $batchno="";

	function __construct($sys='lite'){
		$this->sys=$sys;
	}

	function clear_db($tmpSalesDb){
		if($this->tmpTable) $this->sql_query($tmpSalesDb,"drop table if exists ".$this->tmpTable);
	}
	
	function get_second_tax_code_list(){
		global $con;
		$sql = "select code, second_tax_code from gst";
		$ret = $this->sql_query($con, $sql);
		$list = array();
		while($r = $this->sql_fetchrow($con,$ret)){
			$list[$r["code"]] = $r["second_tax_code"];
		}
		$con->sql_freeresult($ret);
		
		return $list;
	}
	
	function set_report_fields($result2, $data_column, $form){
		global $LANG;
		$type = array("total_amount", "fx_taxable", "taxable", "fx_rate", "fx_credit", "fx_debit", "fx_amount", "credit", "debit", "amount",
						"rounding_adj", "payment_amt1", "payment_amt2", "payment_amt3");
		foreach ($data_column as $key => $item){
			if($item["field_type"] == "open_field"){
				$upd[$key] = $item["field_value"];
			}
			elseif($item["field_type"] == "inv_seq_num"){
				$upd[$key] = sprintf("%0" . $item["field_value"] . "d",$this->inv_seq_num);
			}
			elseif($item["field_type"] == "seq_num"){
				$upd[$key] = sprintf("%0" . $item["field_value"] . "d",$this->seq_num);
			}
			elseif($item["field_type"] == "date"){
				if(isset($form["time_format"]) && $form["time_format"]){
					$upd[$key] = $this->set_date($form["date_format"] . " " . $form["time_format"],$result2["date"]);
				}else{
					$upd[$key] = $this->set_date($form["date_format"],$result2["date"]);
				}
			}
			elseif($item["field_type"] == "account_code"){
				if ($form["row_format"] == "ledger_format"){
					$upd[$key] = $result2['accno'];
				}else{
					$upd[$key] = $result2['account_code'];
				}
			}
			elseif($item["field_type"] == "account_name"){
				if ($form["row_format"] == "ledger_format"){
					$upd[$key] = $result2['desp'];
				}else{
					$upd[$key] = $result2['account_name'];
				}
			}
			elseif($item["field_type"] == "tax_rate"){
				if($result2['tax_code']){
					$upd[$key] = sprintf($LANG['GST_RATE'],$result2['tax_rate']);
				}else{
					$upd[$key] = "";
				}
			}
			elseif($item["field_type"] == "credit_term"){
				$upd[$key] = $this->accSettings['credit_term']['account_code'];
			}
			elseif($item["field_type"] == "tax_gl_code"){
				$upd[$key] = $this->accSettings['tax_gl_account']['account_code'];
			}
			elseif($item["field_type"] == "tax_gl_name"){
				$upd[$key] = $this->accSettings['tax_gl_account']['account_name'];
			}
			elseif($item["field_type"] == "purchase_tax_code"){
				$upd[$key] = $this->accSettings['purchase_tax']['account_code'];
			}
			elseif($item["field_type"] == "purchase_tax_name"){
				$upd[$key] = $this->accSettings['purchase_tax']['account_name'];
			}
			elseif($item["field_type"] == "currency_rate"){
				$upd[$key] = ($result2["currency_rate"] == "")?1:$result2["currency_rate"];
			}
			elseif($item["field_type"] == "selling_price_before_gst"){
				$upd[$key] = $this->selling_price_currency_format($result2["ItemAmount"]);
			}
			elseif($item["field_type"] == "tax_amount"){
				$upd[$key] = $this->selling_price_currency_format($result2["TaxAmount"]);
			}
			elseif($item["field_type"] == "selling_price_after_gst"){
				$upd[$key] = $this->selling_price_currency_format($result2["TotalAmount"]);
			}
			elseif($item["field_type"] == "sub_total"){
				$upd[$key] = $this->selling_price_currency_format($result2['SubTotal']);
			}
			elseif($item["field_type"] == "total_tax_amount"){
				$upd[$key] = $this->selling_price_currency_format($result2['TotalTaxAmount']);
			}
			elseif($item["field_type"] == "grand_total"){
				$upd[$key] = $this->selling_price_currency_format($result2['GrandTotal']);
			}		
			elseif(in_array($item["field_type"], $type)){
				$upd[$key] = $this->selling_price_currency_format($result2[$item["field_type"]]);
			}
			elseif($item["field_type"] == "qty"){
				$upd[$key] = (isset($result2[$item["field_type"]])?$result2[$item["field_type"]]:1);
			}
			elseif($item["field_type"] == "uom"){
				$upd[$key] = (isset($result2[$item["field_type"]])?$result2[$item["field_type"]]:"UNIT");
			}
			elseif($item["field_type"] == "branch_code"){
				$upd[$key] = $form["branch_code"];
			}
			elseif(isset($item["field_type"]) == "second_tax_code"){
				$upd[$key] = $result2[$item["field_type"]];
			}
			elseif(isset($item["field_type"])){
				$upd[$key] = $result2[$item["field_type"]];
			}
			else{
				$upd[$key] = "";
			}
		}
		return $upd;
	}
	
	protected function insert_debit_notes($tmpSalesDb, $pos_db, $sku_db, $rDN, $rItem)
	{
		global $LANG, $config;

		$i = $this->get_max_id($tmpSalesDb, $this->tmpTable);

		$accountings=$this->accSettings;
		$accountings = load_account_file($rPos['branch_id']);
		$this->accSettings=$accountings;
		unset($accountings);

		$sku = $this->get_sku($sku_db, $rItem['sku_item_id']);
		$second_tax_code_list = $this->get_second_tax_code_list();
		if($config['consignment_modules']){
			$rDN['reason'] = $rDN['remark'];
			$branch = $this->get_branch($pos_db, $rDN['to_branch_id']);

			$debtor = array(
				'customer_code'=>$branch['account_code'],
				'customer_name'=>$branch['description'],
				'terms'=>$branch['con_terms'],
				'currency_code'=>$branch['currency_code']);

			$return_receipt_no = $rDN['inv_no'];
			$rItem['uom'] = $rItem['uom_description'];
			$rItem['qty'] = ($rItem['ctn'] * $rItem['fraction']) + $rItem['pcs'];
			$rItem['tax_code'] = $rItem['gst_code'];
			$rItem['tax_rate'] = $rItem['gst_rate'];

			if($rItem['tax_code'] != "" && $rItem['tax_rate'] != ""){
				$rItem['tax_amount'] = $rItem['item_gst2'];
				$rItem['before_tax_price'] = $rItem['item_amt2'];

				if($rDN['exchange_rate']>1){
					$rItem['exchange_rate'] = $rDN['exchange_rate'];
					$rItem['tax_amount_f'] = $rItem['item_foreign_gst2'];
					$rItem['before_tax_price_f'] = $rItem['item_foreign_amt2'];
				}
			}
			else{
				if($rDN['discount'] != ""){
					$total_discount = $rDN['sub_total_amt'];
					$this->cal_discount($total_discount, $rDN['discount']);
					$total_discount_per = $total_discount / $rDN['sub_total_amt'];
				}
				else{
					$total_discount = 0;
					$total_discount_per = 1;
				}

				$rItem['tax_amount'] = 0;
				$rItem['before_tax_price'] = ($rItem['cost_price'] * $rItem['qty']) - $rItem['discount_amt'];

				$rItem['before_tax_price'] = $rItem['before_tax_price'] * $total_discount_per;

				if($rDN['exchange_rate']>1){
					$rItem['exchange_rate'] = $rDN['exchange_rate'];
					$rItem['tax_amount_f'] = 0;
					$rItem['before_tax_price_f'] = ($rItem['foreign_cost_price'] * $rItem['qty']) - $rItem['foreign_discount_amt'];

					$rItem['before_tax_price_f'] = $rItem['before_tax_price_f'] * $total_discount_per;
				}

				unset($total_discount, $total_discount_per);
			}

			unset($branch);
		}
		else{
			$vendor = $this->get_vendor($pos_db,$rDN);

			$debtor = array(
					'customer_code' => $vendor['code'],
					'customer_name' => $vendor['description'],
					'terms' => $vendor['term'],
					'currency_code'=>$config["arms_currency"]["code"]);

			$rDN['date'] = $rDN['dn_date'];
			$rDN['reason'] = $rDN['remark'];

			$return_receipt_no = $rDN['dn_no'];
			$rItem['uom'] = "Unit";
			$rItem['tax_code'] = $rItem['gst_code'];
			$rItem['tax_rate'] = $rItem['gst_rate'];

			$rItem['tax_amount'] = $rItem['item_gst_amount'];
			$rItem['before_tax_price'] = $rItem['item_gross_amount'];

			unset($vendor);
		}

		if(isset($rDN['branch_id'])) $rDN['branch_id'] = $rDN['branch_id'];
		if(isset($rDN['counter_id'])) $rDN['counter_id'] = $rDN['counter_id'];
		$upd['id'] = $i;
		$upd["batchno"] = $this->get_batchno($rDN['date']);
		$upd['date'] = $rDN['date'];
		$upd['ym'] = date("Y-m-01",strtotime($rDN['date']));
		$upd['inv_no'] = $return_receipt_no;
		$upd['sku_description'] = $sku['sku_desc'];
		$upd['sku_cat_desc'] = $sku['category_desc'];
		$upd['arms_code'] = $sku['arms_code'];
		$upd['uom'] = $rItem['uom'];
		$upd['qty'] = $rItem['qty'];

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
		$upd["reason"] = $rDN['reason'];
		$upd["reason_code"] = "WIDV"; //Wrong Item Delivered

		if($rItem['exchange_rate']>1){
			$upd["currency_rate"] = $rItem['exchange_rate'];
			$upd["ItemFAmount"] = $rItem['before_tax_price_f'];
			$upd["TaxFAmount"] = $rItem['tax_amount_f'];
			$upd["TotalFAmount"] = ($rItem['before_tax_price_f'] + $rItem['tax_amount_f']);
		}

		$upd['terms'] = $debtor['terms'];
		$upd['currency_code'] = $debtor['currency_code'];
		$upd['customer_code'] = $debtor['customer_code'];
		$upd['customer_name'] = $debtor['customer_name'];

		if(isset($this->accSettings['purchase_return'])){
			$upd["account_code"] = $this->accSettings['purchase_return']['account_code'];
			$upd["account_name"] = $this->accSettings['purchase_return']['account_name'];
		}
		elseif(isset($this->accSettings['purchase'])){
			$upd["account_code"] = $this->accSettings['purchase']['account_code'];
			$upd["account_name"] = $this->accSettings['purchase']['account_name'];
		}


		if(isset($this->accSettings[$upd["tax_code"]])){
			$acc_tax = $this->accSettings[$upd["tax_code"]];
			$upd["tax_account_code"] = $acc_tax['account_code'];
			$upd["tax_account_name"] = $acc_tax['account_name'];
		}

		$this->sql_query($tmpSalesDb, "insert into ".$this->tmpTable." ".mysql_insert_by_field($upd));
		unset($upd, $branch, $sku, $return_receipt_no, $debit_note_no, $account, $acc_tax, $rDN,$rItem);
	}

	protected function sql_query($con,$sql)
	{
		$this->show_debug($sql);

		$ret=$con->sql_query($sql,false,false);

		$err=$con->sql_error($ret);

		if($err['code']!=0){
			echo '<script>parent.show_error("'.str_replace(array("\t","\n","\r"),"",$err['message']).'")</script>';die();
		}

		return $ret;
	}

	protected function sql_fetchrow($con,$ret){
	  	return $con->sql_fetchassoc($ret);
	}

	protected function selling_price_currency_format($str){
		return number_format($str,2,".","");
	}

	protected function round_curreny($str){
		if(function_exists("round_curreny")){
			return round_curreny($str);
		}
		return round($str,2);
	}

	protected function credit_cards_type(){
		if(function_exists("credit_cards_type")){
			return credit_cards_type();
		}

		global $config,$pos_config;

		$cc=$pos_config['issuer_identifier'];

		$credit_card = array();
		foreach($cc as $c){
			$credit_card[$c[0]] = 1;
		}
		$credit_card['Others'] = 1;
		return $credit_card;
	}

	protected function replace_separator($str,$separator,$file_format){
		$regular_expression = "/[".$separator."]/";
		if(preg_match($regular_expression,$str)){
			$replace_with = ($file_format=="csv"?"\\".$separator:" ");
			return preg_replace($regular_expression,$replace_with,$str);
		}
		return $str;
	}

	protected function create_batch_no($date,$batch_no){
		global $LANG,$config;

		$receipt = sprintf("%04s",$batch_no);

		return sprintf($LANG['SET_RECEIPT_NO_PREFIX'],$date,$receipt);
	}

	protected function monthly_summary_date_checking($date,$dateTo){
		$date =  date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($date))));
		if(strtotime($date) > strtotime($dateTo)){
			$date = $dateTo;
		}
		return $date;
	}

	protected function set_date($date_format,$date){
		return date($date_format,strtotime($date));
	}

	protected function get_company_info($branch_id)
	{
        global $con,$sessioninfo;

        $q1 = $this->sql_query($con, "select * from branch where id=".mi($branch_id));
        $ret = $this->sql_fetchrow($con, $q1);
        $con->sql_freeresult($q1);
        $gst = array();
        $gst['gst_company_name'] = $ret['description'];
		$gst['gst_company_business_register_number'] = $ret['company_no'];
		$gst['gst_register_no'] = $ret['gst_register_no'];

        return $gst;
	}
	
	protected function calculate_financial_year_period($start_date,$current_date)
	{		
		$ts1 = strtotime($start_date);
		$ts2 = strtotime($current_date);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);


		$diff_period = intval(((($year2 - $year1) * 12) + ($month2 - $month1)) + 1);		
		return $diff_period;
	}

	protected function cal_discount(&$new_amt,$discount="")
	{
		$discount_arr = explode("+", $discount);

		if($discount_arr[0]){
			$new_amt = $new_amt - round(($new_amt*($discount_arr[0]/100)),2);
		}

		if($discount_arr[1])
		{
			$new_amt = $new_amt - round(($new_amt*($discount_arr[1]/100)),2);
		}
		$new_amt=round($new_amt,2);
	}

	function show_debug($msg){
		if($this->debug){
		  if(is_string($msg)) $msg=trim(preg_replace('/\t+/', '', $msg));
			file_put_contents($this->debug_file,"<pre>".$msg."</pre>",FILE_APPEND);
		}
	}

	protected function get_batchno($date){

		$batchno="";

		foreach($this->batchno as $batch){
			if(strtotime($date)>=strtotime($batch['date_from']) && strtotime($date)<=strtotime($batch['date_to'])){

				$batchno=$batch['batchno'];
				continue;
			}
		}

		return $batchno;
	}

	protected function get_max_id($db, $table){
		$ret = $this->sql_query($db, "select max(id) as max from ".$table);
		$max = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		return ($max['max']+1);
	}

	protected function get_sku($db=null, $sku_item_id){
		$sql = "select si.sku_item_code as arms_code, si.receipt_description as sku_desc, c.description as category_desc
						from sku_items si
						join sku s on si.sku_id = s.id
						join category c on s.category_id = c.id
						where si.id = ".mi($sku_item_id);

		$ret = $this->sql_query($db, $sql);
		$sku = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		return $sku;
	}

	protected function get_pos($pos_db,$where=array()){
		global $config;

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms(date("Y-m-d",strtotime($this->dateFrom)));
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms(date("Y-m-d",strtotime($this->dateTo)));

		$where[] = "cancel_status = 0";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from pos p $cond order by pos_time, branch_id, receipt_no";
	
		return $this->sql_query($pos_db, $sql);
	}

    protected function get_pos_items($pos_db,$rPos){
        $sql="select * from pos_items
			  where branch_id = ".mi($rPos['branch_id'])."
			  and counter_id = ".mi($rPos['counter_id'])."
			  and pos_id = ".mi($rPos['id'])."
			  and date = ".ms($rPos['date']);

		return $this->sql_query($pos_db, $sql);
    }

	protected function get_pos_deposit($pos_db,$rPos){
		$sql = "select * from pos_deposit
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date']);

		return $this->sql_query($pos_db, $sql);
	}

	protected function get_pos_deposit_status($pos_db,$rPos){
		$sql="select pd.* from pos_deposit_status pds
			  left join pos_deposit pd on pds.deposit_branch_id = pd.branch_id and pds.deposit_counter_id = pd.counter_id and pds.deposit_pos_id = pd.pos_id and pds.deposit_date = pd.date
			  where pds.branch_id = ".mi($rPos['branch_id'])."
			  and pds.counter_id = ".mi($rPos['counter_id'])."
			  and pds.pos_id = ".mi($rPos['id'])."
			  and pds.date = ".ms($rPos['date'])."
			  and pds.receipt_no = ".ms($rPos['receipt_no']);

		return $this->sql_query($pos_db, $sql);
	}

    protected function get_pos_payment($pos_db,$rPos){
	  	$sql = "select * from pos_payment
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date']);

	  return $this->sql_query($pos_db, $sql);
    }

	protected function get_pos_credit_note($pos_db,$rPos){
	  	$sql = "select * from pos_credit_note
				where branch_id = ".mi($rPos['branch_id'])."
				and counter_id = ".mi($rPos['counter_id'])."
				and pos_id = ".mi($rPos['id'])."
				and date = ".ms($rPos['date']);


		return $this->sql_query($pos_db, $sql);
	}

	protected function get_membership_redemption($pos_db,$where=array()){
		global $config;

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);

		$where[] = "active = 1";
		$where[] = "verified = 1";
		$where[] = "status = 0";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from membership_redemption p $cond order by date";		
		
		return $this->sql_query($pos_db, $sql);
	}

	protected function get_membership_redemption_items($pos_db,$rMem){
		$sql = "select * from membership_redemption_items
				where membership_redemption_id = ".mi($rMem['id'])."
				and branch_id = ".mi($rMem['branch_id']);

		return $this->sql_query($pos_db, $sql);
	}

	protected function get_membership($pos_db,$rMem){
		$sql = "select *, branch.code as apply_branch_code, branch.ip as icfile_ip
						   from membership
						   left join branch on membership.apply_branch_id = branch.id
						   where membership.nric=".ms($rMem['nric']);
		$ret=$this->sql_query($pos_db, $sql);
		$membership = $this->sql_fetchrow($pos_db, $ret);
		$pos_db->sql_freeresult($ret);

		return $membership;
	}

	protected function get_vendor($vendor_db,$result){
	  	$sql = "select *,description as company_name, term as vendor_terms_code
							  from vendor where id = ".ms($result['vendor_id']);		
		$ret=$this->sql_query($vendor_db, $sql);
		$vendor = $this->sql_fetchrow($vendor_db, $ret);
		$vendor_db->sql_freeresult($ret);

		return $vendor;
	}

	protected function get_vendor_by_do($con,$doc_no){
		$do=$this->get_do_by_doc_no($con,$doc_no);

		$branch=$this->get_branch($con,$do['branch_id']);

		return $debtor=array('code'=>$branch['account_code'],
							'description'=>$branch['description'],
							'address'=>$branch['address'],
							'terms'=>$branch['con_terms'],
							'account_payable_code'=>$vendor['account_payable_code'],
							'account_payable_name'=>$vendor['account_payable_name'],
							);
	}

	protected function get_grn($grn_db,$where=array()){
		global $config;
		$cond = "";
		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "gi.doc_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "gi.doc_date <= ".ms($this->dateTo);

		$where[] = "p.active = 1";
		$where[] = "p.status = 1";
		$where[]=  "gi.type = ".ms("INVOICE");
		$where[]=  "gn.active = 1";
		$where[]=  "gn.status = 1";
		$where[]=  "gn.approved = 1";
		
		if($where) $cond = "where ".implode(" and ",$where);
		
		$sql = "select *, gi.amount as amount from grr p join grr_items gi on
				p.id = gi.grr_id and 
				p.branch_id = gi.branch_id
				join grn gn on
				gn.grr_id = gi.grr_id and 
				gn.branch_id = gi.branch_id
				$cond order by gi.doc_date";
		return $this->sql_query($grn_db, $sql);
	}

	
	protected function get_grn_items($grn_db,$result){
	    $sql = "select * from grr_items
				where branch_id = ".mi($result['branch_id'])."
				and grr_id = ".ms($result['id'])."
				and type in ('INVOICE','DO','OTHER')";
		
		return $this->sql_query($grn_db, $sql);
	}

    protected function get_invoice($db,$result){
		$sql = "select grn.* from grr left join grn on grr.id=grn.grr_id and grr.branch_id=grn.branch_id where grr.id = ".mi($result['id'])." and grr.branch_id=".ms($result['branch_id'])." limit 1";

		$ret=$this->sql_query($db, $sql);
		$invoice = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);


		if($invoice)
			return $invoice;
		else
			return 0;
	}

	protected function get_do_by_doc_no($con,$doc_no){
		$sql="select * from do where do_no=".ms($doc_no);

		return $this->sql_query($con, $sql);
	}

	protected function get_do($db,$where=array(),$type='open'){

		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.do_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "p.do_date <= ".ms($this->dateTo);
		
		if(is_array($type)){
			 $where[]="do_type in ('".implode("','",$type)."')";
		}
		else $where[]="do_type = ".ms($type);
		$where[]="status = 1";
		$where[]="active = 1";
		$where[]="approved = 1";
		$where[]="checkout = 1";
		$where[]="(inv_no is not null and inv_no != \"\")";

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from do p $cond order by do_date";		
		return $this->sql_query($db, $sql);
    }

	protected function get_do_items($db,$rDo){
		$sql = "select doi.*, rcv_uom.fraction as fraction
							  from do_items doi
							  left join uom rcv_uom on doi.uom_id = rcv_uom.id
							  where doi.branch_id = ".mi($rDo['branch_id'])."
							  and do_id = ".mi($rDo['id']);
		return $this->sql_query($db, $sql);
	}
	
	protected function get_do_open_items($db,$rDo){
		$sql = "select doi.*
							  from do_open_items doi
							  where doi.branch_id = ".mi($rDo['branch_id'])."
							  and do_id = ".mi($rDo['id']);
		return $this->sql_query($db, $sql);
	}

	protected function get_ci($db,$where=array(),$type='sales'){
		if(isset($this->dateFrom) && $this->dateFrom) $where[] = "ci_date >= ".ms($this->dateFrom);
		if(isset($this->dateTo) && $this->dateTo) $where[] = "ci_date <= ".ms($this->dateTo);
		$where[]="type in ('".$type."')";
		$where[]="status = 1";
		$where[]="active = 1";
		$where[]="approved = 1";
		$where[]='ci_no <> ""';

		if($where) $cond = "where ".implode(" and ",$where);

		$sql = "select * from ci $cond order by last_update";

		return $this->sql_query($db, $sql);
	}

	protected function get_ci_items($db,$rDo){

		$sql="select coi.*, rcv_uom.fraction as fraction
							  from ci_items coi
							  left join uom rcv_uom on coi.uom_id = rcv_uom.id
							  where coi.branch_id = ".mi($rDo['branch_id'])."
							  and ci_id = ".mi($rDo['id']);

		return $this->sql_query($db, $sql);
	}

	protected function get_debtor($db,$debtor_id){
		$ret=$this->sql_query($db,"select * from debtor where id=".mi($debtor_id));
		$debtor = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		return $debtor;
	}

	protected function get_branch($db,$branch_id=0){
		global $config;

		$ret=$this->sql_query($db, "select * from branch where id=".mi($branch_id));
		$branch = $this->sql_fetchrow($db, $ret);
		$db->sql_freeresult($ret);

		if(isset($config['masterfile_branch_region']) && isset($config['masterfile_branch_region'][$branch['region']])){
			$branch['currency_code']=$this->fix_currency($config['masterfile_branch_region'][$branch['region']]['currency']);
		}

		return $branch;
	}

	protected function fix_currency($currency_code){
		global $config;
		if(strtolower($currency_code)=="rm"){
			$currency_code=$config["arms_currency"]["code"];
		}

		return $currency_code;
	}

	protected function get_goods_return($pos_db,$where=array(),$posItem = array())
	{
		global $config;

		$cond = "";
		$where = array();
		$where[] = "pgr.pos_id = ".$posItem['pos_id'];
		$where[] = "pgr.branch_id = ".$posItem['branch_id'];
		$where[] = "pgr.counter_id = ".$posItem['counter_id'];
		$where[] = "pgr.item_id = ".$posItem['item_id'];
		$cond = "where ".implode(" and ",$where);		
		$sql = "select * from pos_goods_return pgr $cond";		
	}

	//protected function get_credit_note($pos_db,$where=array(),$consignment=false,$is_backCN = false,$posItem = array())
	protected function get_credit_note($pos_db,$where=array(),$consignment=false,$is_backCN = false,$posItem = array(),$from_update_cn = false,$posInfo = array())
	{
		global $config;

		$cond = "";

		if($consignment){
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);
			$where[] = "p.status = 1";
			$where[] = "p.active = 1";
			$where[] = "p.approved = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from cn p $cond order by p.date";
		}
		elseif($is_backCN)
		{
			$where[] = "p.status = 1";
			$where[] = "p.active = 1";
			$where[] = "p.approved = 1";
			
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.cn_date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.cn_date <= ".ms($this->dateTo);
			$cond = "where ".implode(" and ",$where);
			$sql = "select p.* from cnote p " . $cond;
		}
		else{	
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);
			$where[]="cancel_status = 0";
			if(!$from_update_cn && !$posInfo)
			{
				if(!$skip_filter_qty) $where[]="pi.qty < 0";
				if($posItem)
				{
					$where[] = "pi.pos_id = ".$posItem['pos_id'];
					$where[] = "pi.branch_id = ".$posItem['branch_id'];
					$where[] = "pi.counter_id = ".$posItem['counter_id'];
					$where[] = "pi.item_id = ".$posItem['item_id'];
				}
	
			}
			else{
				if($posInfo)
				{
					$where[] = "pcn.pos_id = ".$posInfo['id'];
					$where[] = "pcn.branch_id = ".$posInfo['branch_id'];
					$where[] = "pcn.counter_id = ".$posInfo['counter_id'];
					$where[] = "pcn.date = ".ms($posInfo['date']);
				}
			}
						
			$cond = "where ".implode(" and ",$where);
			if(!$from_update_cn && !$posInfo)
			{
				$sql = "select * from pos_credit_note pcn
					join pos_goods_return pgr on
					pcn.pos_id = pgr.pos_id and 
					pcn.date=pgr.date and 
					pcn.counter_id = pgr.counter_id and 
					pcn.branch_id = pgr.branch_id
					join pos_items pi on 
					pgr.pos_id = pi.pos_id and 
					pi.item_id = pgr.item_id and 
					pi.counter_id = pgr.counter_id and 
					pi.branch_id = pgr.branch_id and 
					pi.date = pgr.date
					join pos p on 
					p.id = pi.pos_id and 
					p.counter_id = pi.counter_id and 
					p.branch_id = pi.branch_id and 
					p.date = pi.date
					$cond
					order by pcn.date, pcn.credit_note_no";
			//print $sql."\n";
			}
			else{
				$sql = "select pcn.credit_note_no, 
							   pcn.credit_note_ref_no, 
							   pcn.branch_id,
							   pcn.counter_id,
							   pcn.pos_id,
							   pcn.date,
							   pcn.company_name,
							   pcn.address,
							   pcn.gst_register_number,
							   pcn.customer_infor,
							   pcn.item_infor,
							   p.start_time,
							   p.end_time,
							   p.pos_time 
							   from pos_credit_note pcn
				join pos p on 
				p.id = pcn.pos_id and 
				p.counter_id = pcn.counter_id and 
				p.branch_id = pcn.branch_id and 
				p.date = pcn.date
				$cond
				group by pcn.branch_id,pcn.counter_id,pcn.pos_id,pcn.date, pcn.credit_note_no
				order by pcn.date, pcn.credit_note_no";
				
			}
		}
	
		return $this->sql_query($pos_db, $sql);
	}
	
	protected function get_credit_note_pos_items($pos_db,$where,$posItem,$itemInfo = array())
	{
		$where = array();
		$where[] = "pgr.pos_id = ".ms($posItem['pos_id']);
		$where[] = "pgr.branch_id = ".ms($posItem['branch_id']);
		$where[] = "pgr.counter_id = ".ms($posItem['counter_id']);
		$where[] = "pgr.date = ".ms($posItem['date']);
		if($itemInfo)
		{
			$where[] = "pgr.return_pos_id = ".ms($itemInfo['pos_id']);
			if(isset($itemInfo['branch_id']))
				$where[] = "pgr.return_branch_id = ".ms($itemInfo['branch_id']);
			else
				$where[] = "pgr.return_branch_id = ".ms($posItem['branch_id']);
			$where[] = "pgr.return_counter_id = ".ms($itemInfo['counter_id']);
			$where[] = "pgr.return_date = ".ms($itemInfo['date']);
			$where[] = "pgr.return_item_id = ".ms($itemInfo['item_id']);
		}
		
		$cond = "where ".implode(" and ",$where);			
		$sql = "select * from pos_goods_return pgr 
					join pos_items pi on pgr.pos_id = pi.pos_id and pi.item_id = pgr.item_id and pi.counter_id = pgr.counter_id and pi.branch_id = pgr.branch_id and pi.date = pgr.date
					$cond";	
					
		return $this->sql_query($pos_db, $sql);
	}

	protected function get_credit_note_items($db,$rCn=array(),$is_backCN = false)
	{
		if($is_backCN)
		{
			if($rCn['return_type'] == "multiple_inv"){
				$str_col = " cni.return_inv_no as 'inv_no'";
				$str_join = "left join do on do.id = cni.return_do_id and do.branch_id = c.branch_id";
			}else{
				$str_col = " c.inv_no";
				$str_join = "left join do on do.id = c.do_id and do.branch_id = c.branch_id";
			}
			
			$sql = "select cni.*,rcv_uom.fraction as fraction, c.return_type, c.do_id, c.inv_date, do.do_type, $str_col from cnote_items cni
					left join uom rcv_uom on cni.uom_id = rcv_uom.id
					left join cnote c on c.branch_id = cni.branch_id and c.id = cni.cnote_id
					$str_join
					where cni.branch_id = ".ms($rCn['branch_id'])." and cni.cnote_id = ".ms($rCn['id']);
		}
		else{
			$sql="select cni.*,rcv_uom.fraction as fraction
					from cn_items cni
					left join uom rcv_uom on cni.uom_id = rcv_uom.id		
					where branch_id=".ms($rCn['branch_id'])." and cn_id=".mi($rCn['id']);
		}
		
			
		return $this->sql_query($db, $sql);
	}
	
	protected function get_debit_note($pos_db,$where=array(),$consignment=false){
		global $config;

		$cond = "";

		if($consignment){
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.date <= ".ms($this->dateTo);

			$where[]="p.status = 1";
			$where[]="p.active = 1";
			$where[]="p.approved = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from dn p $cond order by p.date";
		}
		else{
			if(isset($this->dateFrom) && $this->dateFrom) $where[] = "p.dn_date >= ".ms($this->dateFrom);
			if(isset($this->dateTo) && $this->dateTo) $where[] = "p.dn_date <= ".ms($this->dateTo);

			$where[]="p.ref_table in('grn','gra')";
			$where[]="p.active = 1";

			$cond = "where ".implode(" and ",$where);

			$sql = "select * from dnote p $cond order by p.dn_date";		
		}

		return $this->sql_query($pos_db, $sql);
	}

	protected function get_debit_note_items($db,$rDn=array(),$consignment=false)
	{
		if($consignment){
			$sql = "select *, rcv_uom.fraction as fraction, rcv_uom.description as uom
					from dn_items di
					left join uom rcv_uom on di.uom_id = rcv_uom.id
					where di.dn_id=".mi($rDn['id'])."
					and di.branch_id=".mi($rDn['branch_id']);
		}
		else{
			$sql = "select *
					from dnote_items
					where branch_id = ".mi($rDn['branch_id'])."
					and dnote_id = ".mi($rDn['id']);			
		}

		return $this->sql_query($db, $sql);
	}
	
	protected function get_cn_inv_infor($db,$rCN)
	{
		if($rCN["return_type"] == "multiple_inv"){
			$filter[] = "id = " . ms($rCN['return_do_id']);
			$filter[] = "do_date = " . ms($rCN['return_inv_date']);
		}else{
			$filter[] = "id = ". ms($rCN['do_id']);
			$filter[] = "do_date = ". ms($rCN['inv_date']);
		}
		
		$str_filter = implode(" and ",$filter);
		$sql = "select * from do
				where branch_id = " . ms($rCN['branch_id']) . "
				and inv_no = " . ms($rCN['inv_no']) . " and " . $str_filter;
		return $this->sql_query($db, $sql);		
	}
}
?>
