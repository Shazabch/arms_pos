<?php
/*
6/8/2018 3:09 PM Justin
- Enhanced to have "base currency rate" (to be used for POS counter).

7/9/2018 2:53 PM Andy
- Enhanced base currency.
*/

include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ADMIN_FOREIGN_CURRENCY_RATE_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ADMIN_FOREIGN_CURRENCY_RATE_UPDATE', BRANCH_CODE), "/index.php");	

class FOREIGN_CURRENCY_RATE_TABLE extends Module{
	
	function __construct($title){
		global $con, $smarty;
		
		parent::__construct($title);
	}
	
	function _default(){
		// Load Foreign Currency Table
		$this->loadFCTable();
		
	    $this->display();
	}
	
	private function loadFCTable(){
		global $con, $appCore, $smarty;
		
		// Get All Active Currency Code
		$codeList = $appCore->currencyManager->getCurrencyCodes();
		
		// Get All Currency Latest Data
		$currencyData = $appCore->currencyManager->loadLatestCurrencyRate();
		
		//print_r($currencyData);
		$smarty->assign('codeList', $codeList);
		$smarty->assign('currencyData', $currencyData);
	}
	
	function update_currency(){
		global $smarty, $sessioninfo, $appCore, $LANG;
		
		$form = $_REQUEST;
		$curr_date = date("Y-m-d");
		
		$params = array();
		$params['currencyCode'] = trim($form['curr_code']);
		$params['rate'] = trim($form['new_rate']);
		$params['base_rate'] = trim($form['new_base_rate']);
		
		// Perform update
		//$result = $appCore->currencyManager->updateCurrency($sessioninfo['id'], $curr_date, $form['curr_code'], $form['new_rate'], $form['new_base_rate']);
		$result = $appCore->currencyManager->updateCurrency($sessioninfo['id'], $params);
		if($result['err']){
			display_redir($_SERVER['PHP_SELF'], $this->title, $result['err']);
		}
		
		// Redirect once success
		header("Location: ".$_SERVER['PHP_SELF']."?t=updated&code=".$result['code']."&old_rate=".$result['old_rate']."&new_rate=".$result['new_rate']."&old_base_rate=".$result['old_base_rate']."&new_base_rate=".$result['new_base_rate']);
		exit;
	}
	
	function ajax_search_history(){
		global $smarty, $sessioninfo, $appCore, $LANG;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$curr_code = trim($form['curr_code']);
		$history_date = trim($form['history_date']);
		$page = mi($form['page']);
		
		$params = array();
		if($history_date){
			$params['date'] = $history_date;
		}else{
			$params['limit'] = 100;
			$params['page'] = $page;
		}
		
		// load history
		$result = $appCore->currencyManager->loadCurrencyHistory($curr_code, $params);
		if($result['err'])	die($result['err']);
		elseif(!$result)	die('No respond from server.');
		
		
		
		$ret = array();
				
		$smarty->assign('currency_his_list', $result['currencyHistoryRecordList']);
		$ret['html'] = $smarty->fetch('admin.foreign_currency.rate.history_table.tpl');
		$ret['ok'] = 1;
		die(json_encode($ret));
	}
	
	function ajax_open_edit_rate(){
		global $LANG, $smarty, $appCore;
		
		$curr_code = trim($_REQUEST['curr_code']);
		if(!$curr_code)	die($LANG['FOREIGN_CURRENCY_CODE_EMPTY']);
		
		$currencyData = $appCore->currencyManager->loadLatestCurrencyRate($curr_code);
		$form = $currencyData[$curr_code];
		
		if(!$form){
			//die(sprintf($LANG['FOREIGN_CURRENCY_INVALID_CODE'], $curr_code));
			$form['code'] = $curr_code;	// First time edit
		}	
		
		$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('admin.foreign_currency.rate.edit.tpl');
		die(json_encode($ret));
	}
}

$FOREIGN_CURRENCY_RATE_TABLE = new FOREIGN_CURRENCY_RATE_TABLE('Currency Rate Table');
?>