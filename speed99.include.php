<?php
/*
1/21/2021 12:10 PM Andy
- Added "getWarehouseByNumber".

3/12/2021 6:04 PM Shane
- Modified "get_invoice_no_prefix" function to get prefix from POS Settings.
- Modified "invoice_date_conversion" function to get config from general config.

4/14/2021 1:39 PM Andy
- Added function "is_same_arms_code_old_code".

4/23/2021 6:33 PM Shane
- Modified "get_outlet_code" to return arms branch_code if outlet_code is empty
- Added function "trim_outlet_code".
- Added function "get_point_after_digit".

4/28/2021 1:37 AM Shane
- Added "denso_server_ftp_info".
*/
class Speed99 {
	var $server_ftp_info = array();
	var $arev_sales_server_ftp_info = array();
	var $navision_sales_server_ftp_info = array();
	var $vbal_server_ftp_info = array();
	var $staff_server_ftp_info = array();
	var $denso_server_ftp_info = array();
	var $outlet_code_cache = array();
	var $invoice_no_prefix_cache = array();
	
	function __construct()
	{
		
	}
	
	function check_config(){
		global $config;
		
		$ret = array();
		if(!$config['speed99_settings']){
			$ret['error'] = "Config 'speed99_settings' Not Set";
			return $ret;
		}
		
		if(!$config['speed99_settings']['server_ftp_info']){
			$ret['error'] = "Config 'speed99_settings'.'server_ftp_info' Not Set";
			return $ret;
		}
		
		if(!$config['speed99_settings']['arev_sales_server_ftp_info']){
			$ret['error'] = "Config 'speed99_settings'.'arev_sales_server_ftp_info' Not Set";
			return $ret;
		}
		
		if(!$config['speed99_settings']['navision_sales_server_ftp_info']){
			$ret['error'] = "Config 'speed99_settings'.'navision_sales_server_ftp_info' Not Set";
			return $ret;
		}

		if(!$config['speed99_settings']['vbal_server_ftp_info']){
			$ret['error'] = "Config 'speed99_settings'.'vbal_server_ftp_info' Not Set";
			return $ret;
		}
		
		$this->server_ftp_info = $config['speed99_settings']['server_ftp_info'];
		$this->arev_sales_server_ftp_info = $config['speed99_settings']['arev_sales_server_ftp_info'];
		$this->navision_sales_server_ftp_info = $config['speed99_settings']['navision_sales_server_ftp_info'];
		$this->vbal_server_ftp_info = $config['speed99_settings']['vbal_server_ftp_info'];
		$this->staff_server_ftp_info = $config['speed99_settings']['staff_server_ftp_info'];
		$this->denso_server_ftp_info = $config['speed99_settings']['denso_server_ftp_info'];
		
		$ret['ok'] = 1;
		return $ret;
	}
	
	function invoice_date_conversion($date=false){
		global $config;

		if(!isset($config['invoice_date_conversion_anchor']) || !isset($config['invoice_date_conversion_anchor']['date']) || !$config['invoice_date_conversion_anchor']['date'] || !isset($config['invoice_date_conversion_anchor']['value']) || $config['invoice_date_conversion_anchor']['value'] == ''){
			return false;
		}

		if(!$date){
			$date = date('Y-m-d');
		}

		$anchor_date = $config['invoice_date_conversion_anchor']['date'];
		$datefrom = strtotime($anchor_date) ? strtotime($anchor_date) : $anchor_date;
		$dateto = strtotime($date) ? strtotime($date) : $date;

		$datediff = $dateto - $datefrom;

		$days = round($datediff / (60 * 60 * 24));

		$conversion = intval($config['invoice_date_conversion_anchor']['value']) + $days;

		return $conversion;
	}
	
	function get_outlet_code($b){
		global $con;
		
		$bid = mi($b['id']);
		if($bid <= 0)	return false;
		
		if(!isset($this->outlet_code_cache[$bid])){
			$con->sql_query("select * from speed99_branch_mapping where branch_id=$bid");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(isset($tmp['outlet_code']) && $tmp['outlet_code']){
				$this->outlet_code_cache[$bid] = trim($tmp['outlet_code']);
			}else{
				$this->outlet_code_cache[$bid] = $b['code'];
			}
		}

		return $this->outlet_code_cache[$bid];
	}
	
	function get_invoice_no_prefix($bid){
		global $con;
		$bid = mi($bid);
		if($bid <= 0)	return '';
		
		if(!isset($this->invoice_no_prefix_cache[$bid])){
			$con->sql_query("select * from pos_settings where branch_id=$bid and setting_name='receipt_no_prefix'");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$this->invoice_no_prefix_cache[$bid]= trim($tmp['setting_value']);
			
		}
		return $this->invoice_no_prefix_cache[$bid];
	}
	
	function get_sp9_payment_type($ptype){
		global $config, $pos_config;
		
		// Credit Card Checking
		if(strpos(strtolower($ptype), "credit_card")===0){
			$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
		}
		$ptype = ucwords($ptype);
		if($ptype == "Credit Card") $ptype = "Credit Cards";
				
		// is one of the credit cards
		if (in_array($ptype, $pos_config['credit_card'])) $payment_type = 'Credit Cards';
		elseif ($ptype == 'Cheque') $payment_type = 'Check';
		else	$payment_type = $ptype;
		
		if(preg_match('/^ewallet_/i', $payment_type)){	// eWallet don uppercase
			$payment_type = strtolower($payment_type);
		}else{
			$payment_type = ucwords(strtolower($payment_type));
		}
		
		// Use Uppercase to match with config
		$payment_type = strtoupper($payment_type);
		
		return trim($config['speed99_settings']['payment_type_mapping'][$payment_type]['type']);
	}
	
	function getWarehouseByNumber($warehouse_number){
		global $con;
		
		$warehouse_number = trim($warehouse_number);
		if(!$warehouse_number)	return false;
		
		$con->sql_query("select * from speed99_warehouse where warehouse_number=".ms($warehouse_number));
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}
	
	function is_same_arms_code_old_code(){
		global $config;
		
		if(isset($config['speed99_settings']['same_arms_code_old_code']) && $config['speed99_settings']['same_arms_code_old_code']){
			return true;
		}
		return false;
	}

	function trim_outlet_code($branch_code){
		$bcode = $branch_code;
		$first_char = substr($bcode,0,1);
		$remaining_char = substr($bcode,1);
		if(preg_match("/^[0-9]+$/", $remaining_char)){
			if(!preg_match("/^[0-9]$/", $first_char)){
				$bcode = $remaining_char;
			}
		}

		return $bcode;
	}

	function get_point_after_digit(){
		return 8;
	}
}


?>
