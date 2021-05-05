<?php
/*
6/8/2018 3:09 PM Justin
- Enhanced to have "base currency rate" (to be used for POS counter).
- Bug fixed on the date filter issue while viewing currency rate history.

7/9/2018 2:53 PM Andy
- Enhanced base currency.

1/21/2019 11:53 AM Andy
- Fixed currency history date_to error.
*/

class currencyManager{
	// public var
	
	// private var
	var $codeList = array();	// default testing value
	
	function __construct(){
		global $smarty, $con, $appCore;

	}
	
	// function to get foreign currency code list
	// return array $codeList
	public function getCurrencyCodes(){
		global $con, $config;
		
		// load from db or config ?
		if(!$this->codeList && isset($config['foreign_currency']) && is_array($config['foreign_currency']) && $config['foreign_currency']){
			$this->codeList = array();
			foreach($config['foreign_currency'] as $code => $currencyDetails){
				if($currencyDetails['active']){
					$this->codeList[$code] = $code;
				}
			}
		}	
		
		return $this->codeList;
	}
	
	// function to check whether this currency code is valid to use
	// return boolean
	public function isValidCurrencyCode($currencyCode){
		$currencyCode = trim($currencyCode);
		if(!$currencyCode)	return false;
		
		$codeList = $this->getCurrencyCodes();
		return isset($codeList[$currencyCode]) ? true : false;
	}
	
	// function to all currency latest rate table
	// return array
	public function loadLatestCurrencyRate($currencyCode = ''){
		global $con;
		
		$filter = array();
		
		// filter only one currency code
		if($currencyCode != ''){
			$filter[] = "fcr.code=".ms($currencyCode);
		}
		if($filter)	$filter = "where ".join(' and ', $filter);
		else	$filter = '';
		
		// Load current data
		$currency_list = array();
		$q1 = $con->sql_query("select fcr.*, u.u as username
							   from foreign_currency_rate fcr
							   left join user u on u.id = fcr.user_id
							   $filter");
		
		while($r = $con->sql_fetchassoc($q1)){
			$currency_list[$r['code']] = $r;
		}
		$con->sql_freeresult($q1);
				
		if(!$currencyCode){
			// fill in the new code
			$all_currency_list = $this->getCurrencyCodes();
			
			foreach($all_currency_list as $code=>$dummy){
				$currency_list[$code]['code'] = $code;
			}
		}		
		
		return $currency_list;
	}
	
	// function to update currency code
	// return array
	//public function updateCurrency($userID, $date, $currencyCode, $rate, $base_rate){
	public function updateCurrency($userID, $params = array()){
		global $con, $appCore, $LANG;
		
		$userID = mi($userID);
		$currencyCode = trim($params['currencyCode']);
		$rate = mf($params['rate']);
		$base_rate = mf($params['base_rate']);
		$date = date("Y-m-d");	// Always today
		
		// check currency code
		if(!$currencyCode){
			return array('err'=> $LANG['FOREIGN_CURRENCY_CODE_EMPTY']);
		}
		
		// check date
		//if(!$appCore->isValidDateFormat($date)){
		//	return array('err' => $LANG['INVALID_DATE_FORMAT']);
		//}
		
		// Only can update Today date
		//if(date("Y-m-d", strtotime($date)) != date("Y-m-d")){
		//	return array('err' => sprintf($LANG['FOREIGN_CURRENCY_INVALID_DATE'], $date));
		//}
		
		// Either Rate must more than zero
		if($rate <= 0 && $base_rate <= 0){
			return array('err' => sprintf($LANG['FOREIGN_CURRENCY_INVALID_RATE'], $rate));
		}
		
		// currency code active or not
		if(!$this->isValidCurrencyCode($currencyCode)){
			return array('err' => sprintf($LANG['FOREIGN_CURRENCY_INVALID_CODE'], $currencyCode));
		}
		
		$sameRate = true;
		$sameBaseRate = true;
		
		// Get the old rate
		$oldResult = $this->loadCurrencyRateByDate($date, $currencyCode);
		if($oldResult){
			if($oldResult['err'])	return array('err' => $oldResult['err']);
			
			// same rate
			//if($oldResult['rate'] == $rate){
			//	return array('err' => sprintf($LANG['FOREIGN_CURRENCY_SAME_RATE'], $oldResult['rate']));
			//}
			if($rate > 0 && $oldResult['rate'] != $rate)	$sameRate = false;
			if($base_rate > 0 && $oldResult['base_rate'] != $base_rate)	$sameBaseRate = false;
			
			if($sameRate && $sameBaseRate)	return array('err' => $LANG['FOREIGN_CURRENCY_SAME_RATE']);
		}else{	// First Time, must provide rate & base_rate
			// Both Rate must more than zero
			if($rate <= 0){
				return array('err' => sprintf($LANG['FOREIGN_CURRENCY_INVALID_RATE'], $rate));
			}
			if($base_rate <= 0){
				return array('err' => sprintf($LANG['FOREIGN_CURRENCY_INVALID_RATE'], $base_rate));
			}
			$sameRate = $sameBaseRate = false;
		}
		//print_r($oldResult);
		$old_rate = mf($oldResult['rate']);
		$old_base_rate = mf($oldResult['base_rate']);
		
		$finalUpdateRate = $rate > 0 ? $rate : $oldResult['rate'];
		$finalUpdateBaseRate = $base_rate > 0 ? $base_rate : $oldResult['base_rate'];
		
		// Construct array to insert
		$ins = array();
		$ins['code'] = $currencyCode;
		$ins['rate'] = $finalUpdateRate;
		$ins['base_rate'] = $finalUpdateBaseRate;
		$ins['date'] = $date;
		$ins['user_id'] = $userID;
		$ins['last_update'] = "CURRENT_TIMESTAMP";
		//print_r($ins);exit;
		// insert data
		$con->sql_query("replace into foreign_currency_rate ".mysql_insert_by_field($ins));
		if($con->sql_affectedrows()>0){
			// insert history record
			$ins2 = array();
			$ins2['code'] = $currencyCode;
			$ins2['rate'] = $finalUpdateRate;
			$ins2['base_rate'] = $finalUpdateBaseRate;
			$ins2['date'] = $date;
			$ins2['user_id'] = $userID;
			$ins2['timestamp'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("replace into foreign_currency_rate_history_record ".mysql_insert_by_field($ins2));
			
			// log
			if(!$sameRate)	log_br($userID, 'CURRENCY_TABLE', 0, sprintf($LANG['FOREIGN_CURRENCY_UPDATED'], $currencyCode, $old_rate, $finalUpdateRate));
			if(!$sameBaseRate)	log_br($userID, 'CURRENCY_TABLE', 0, sprintf($LANG['FOREIGN_CURRENCY_UPDATED_RATE2'], $currencyCode, $old_base_rate, $finalUpdateBaseRate));
			
			// rebuild currency rate history
			$this->rebuildCurrencyRateHistory($currencyCode);
		}
		
		
		// Return successfull result
		$ret = array();
		$ret['ok'] = 1;
		$ret['code'] = $currencyCode;
		$ret['date'] = $date;
		$ret['new_rate'] = $finalUpdateRate;
		$ret['new_base_rate'] = $finalUpdateBaseRate;
		$ret['old_rate'] = $old_rate;
		$ret['old_base_rate'] = $old_base_rate;
		
		return $ret;
	}
	
	// function to load currency rate by date
	// return array or false on error
	public function loadCurrencyRateByDate($date, $currencyCode){
		global $con, $LANG;
		
		$currencyCode = trim($currencyCode);
		$date = trim($date);
		
		// check currency code
		if(!$currencyCode){
			return array('err'=> $LANG['FOREIGN_CURRENCY_CODE_EMPTY']);
		}
		
		// directly get latest rate if provided date is today
		/*if(strtotime($date) >= strtotime(date("Y-m-d"))){
			$currency_list = $this->loadLatestCurrencyRate($currencyCode);
			if(isset($currency_list[$currencyCode])){
				return array('rate'=>$currency_list[$currencyCode]['rate']);
			}else{
				return false;
			}
		}*/
		
		// select old rate from history table
		$filter = array();
		$filter[] = "fcr.code=".ms($currencyCode)." and ".ms($date)." between fcr.date_from and fcr.date_to";
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select * from foreign_currency_rate_history fcr $filter";
		$con->sql_query($sql);
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			return $tmp;
		}else{
			return false;
		}
	}
	
	// function to rebuild foreign_currency_rate_history
	// return array
	public function rebuildCurrencyRateHistory($currencyCode){
		global $con, $LANG;
		
		$currencyCode = trim($currencyCode);
		// check currency code
		if(!$currencyCode){
			return array('err'=> $LANG['FOREIGN_CURRENCY_CODE_EMPTY']);
		}
		
		// Delete history
		$con->sql_query("delete from foreign_currency_rate_history where code=".ms($currencyCode));
		
		// select from history record
		$upd = array();
		$q1 = $con->sql_query("select * from foreign_currency_rate_history_record where code=".ms($currencyCode)." order by date");
		while($r = $con->sql_fetchassoc($q1)){
			if($upd){
				$upd['date_to'] = date("Y-m-d", strtotime("-1 day ", strtotime($r['date'])));
				$con->sql_query("replace into foreign_currency_rate_history ".mysql_insert_by_field($upd));
				$upd = array();
			}
			
			// array not set construct new array
			if(!$upd){
				$upd['code'] = $r['code'];
				$upd['rate'] = $r['rate'];
				$upd['base_rate'] = $r['base_rate'];
				$upd['user_id'] = $r['user_id'];
				$upd['date_from'] = $r['date'];
			}
		}
		
		// still got latest data
		if($upd){
			$upd['date_to'] = "9999-12-31";
			$con->sql_query("replace into foreign_currency_rate_history ".mysql_insert_by_field($upd));
			$upd = array();
		}
		$con->sql_freeresult($q1);
		
		return array('ok'=>1);
	}
	
	// function to load currency history
	// return array
	public function loadCurrencyHistory($currencyCode, $params = array()){
		global $con, $LANG, $appCore;
		
		$currencyCode = trim($currencyCode);
		$date = trim($params['date']);
		$limit = mi($params['limit']);
		$page = mi($params['page']);
		
		// check currency code
		if(!$currencyCode){
			return array('err'=> $LANG['FOREIGN_CURRENCY_CODE_EMPTY']);
		}
		
		$str_order = "order by fr.date desc";
		$str_limit = '';
		
		$filter = array();
		$filter[] = "fr.code=".ms($currencyCode);
		
		if($date){
			// check date
			if(!$appCore->isValidDateFormat($date)){
				return array('err' => $LANG['INVALID_DATE_FORMAT']);
			}
			
			$filter[] = "fr.date<=".ms($date);
			$str_order = "order by fr.date desc";
			$str_limit = "limit 1";
		}else{
			if($limit>0){
				if($page>0){
					$str_limit = "limit ".mi($page*$limit)." $limit";
				}else{
					$str_limit = "limit $limit";
				}
			}
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$ret = array();
		
		// count total record
		if($limit > 0 && !$date){
			$con->sql_query("select count(*) as c
			from foreign_currency_rate_history_record fr
			$filter");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$totalRecord = mi($tmp['c']);
			if($totalRecord > 0){
				$ret['totalPage'] = ceil($totalRecord / $limit);
			}			
		}
		$ret['currencyHistoryRecordList'] = array();
		
		// select record
		$sql = "select fr.*, user.u
			from foreign_currency_rate_history_record fr
			left join user on user.id=fr.user_id
			$filter
			$str_order
			$str_limit";
		//print $sql;
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$ret['currencyHistoryRecordList'][] = $r;
		}
		$con->sql_freeresult();
		
		$ret['ok'] = 1;
		return $ret;
	}
}
?>