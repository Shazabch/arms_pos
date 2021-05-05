<?php
/*
4/7/2021 9:40 AM Andy
- Added New Integration "KOMAISO".
*/
class KOMAISO {
	var $server_ftp_info = array();
	
	function __construct()
	{
		
	}
	
	function check_config(){
		global $config;
		
		$ret = array();
		if(!$config['komaiso_settings']){
			$ret['error'] = "Config 'komaiso_settings' Not Set";
			return $ret;
		}
		
		if(!$config['komaiso_settings']['server_ftp_info']){
			$ret['error'] = "Config 'komaiso_settings'.'server_ftp_info' Not Set";
			return $ret;
		}
		
		
		$this->server_ftp_info = $config['komaiso_settings']['server_ftp_info'];
		
		$ret['ok'] = 1;
		return $ret;
	}
	
	function get_komaiso_payment_type($ptype){
		global $config;
		
		$ptype = strtoupper(trim($ptype));
		if(!$ptype)	return '';
		
		$komaiso_ptype = '';
		
		// Check Payment Type Mapping
		if(isset($config['komaiso_settings']['payment_type_mapping']) && $config['komaiso_settings']['payment_type_mapping']){
			if(isset($config['komaiso_settings']['payment_type_mapping'][$ptype])){
				$komaiso_ptype = trim($config['komaiso_settings']['payment_type_mapping'][$ptype]['type']);
			}
		}
		
		if($komaiso_ptype)	return $komaiso_ptype;
		
		//if(preg_match('/^EWALLET_/i', $ptype)){
			// ewallet
		//}
		return substr($ptype, 0, 3);	// default get first 3 char
		
		/*switch($ptype){
			case 'ROUNDING':
				return 'RDG';
			case 'VISA':
				return 'VSA';
			case 'AMEX':
				return 'AMX';
			default:
				return substr($ptype, 0, 3);	// default get first 3 char
				break;
		}*/
	}
}
?>
