<?php
/*

9/3/2012 5:20:10PM Drkoay
- add Currency Table
- add function save_currency(), save_currency_history()

9/6/2012 9:21 AM Drkoay
- config masterfile_update_sku_items_price_on_approve change to consignment_new_sku_use_currency_table

4/27/2015 5:07 PM Justin
- Enhanced to have Currency Description.

4/21/2017 11.11 AM Khausalya
- Enhanced changes from RM to use config setting. 

2/17/2020 4:39 PM Andy
- Fixed php time "h:i" should be "H:i".
*/
include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class Forex extends Module{
	var $page_size = 30;
	
    function _default(){
		global $con, $smarty;
		$this->load();
	    $this->display();
	}
	
	function load(){
        global $con, $smarty, $config, $LANG;

		// show errors if found no config set
		if(!is_array($config['consignment_multiple_currency'])) $err['top'][] = $LANG['CONSIGNMENT_FOREX_NO_CONFIG'];
		else{
			// loop the config to take the different currency types
			foreach($config['consignment_multiple_currency'] as $cc){
				if(!$cc || $cc == $config["arms_currency"]["symbol"]) continue; // if found it's RM or empty, skip
				$currency_code = strtoupper($cc); // string all to uppercase in case user set the currency code in lowercase
				$currency_list[$currency_code] = $currency_code; 
			}
			if(!$currency_list) $err['top'][] = $LANG['CONSIGNMENT_FOREX_CONFIG_NO_CURRENCY'];
			else ksort($currency_list);
		}

		if(!$err){
			$sql = $con->sql_query("select cf.*
									 from consignment_forex cf
									 order by cf.currency_code");

			while($r = $con->sql_fetchassoc($sql)){
				$forex_info[$r['currency_code']] = $r;
			}
			$con->sql_freeresult($sql);
		}

		$smarty->assign('errm',$err);
		$smarty->assign('currency_list',$currency_list);
		$smarty->assign('forex_info',$forex_info);
		
		if(isset($config['consignment_new_sku_use_currency_table']) && $config['consignment_new_sku_use_currency_table']){
			$sql=$con->sql_query("select * from consignment_currency_table");
			while($r = $con->sql_fetchassoc($sql)){
				$r['currency']=unserialize($r['currency']);
				$consignment_currency_table[] = $r;
			}
			
			
			$smarty->assign('consignment_currency_table',$consignment_currency_table);		
		}
	}

	function save(){
		global $con, $smarty, $sessioninfo, $LANG;
		$form=$_REQUEST;

		foreach($form['currency_code'] as $code=>$r){
			$have_history = false;
			$sql = $con->sql_query("select * from consignment_forex where currency_code = ".ms($code));
			$cf = $con->sql_fetchassoc($sql);

			if($con->sql_numrows($sql) == 0){ // insert as new
				$ins = array();
				$ins['currency_code'] = $code;
				$ins['currency_description'] = $form['currency_description'][$code];
				$ins['exchange_rate'] = $form['exchange_rate'][$code];
				$con->sql_query("insert into consignment_forex ".mysql_insert_by_field($ins));
				
				$have_history = true;
				
				$log[] = sprintf($LANG['CONSIGNMENT_FOREX_ADDED'], $code, $ins['exchange_rate']);
			}else{ // update
				$upd = array();
				$upd['currency_description'] = $form['currency_description'][$code];
				$upd['exchange_rate'] = $form['exchange_rate'][$code];
				$con->sql_query("update consignment_forex set ".mysql_update_by_field($upd)." where currency_code = ".ms($code));
				
				if($con->sql_affectedrows() > 0){
					$log[] = sprintf($LANG['CONSIGNMENT_FOREX_UPDATED'], $code, $cf['exchange_rate'], $upd['exchange_rate'], $cf['currency_description'], $upd['currency_description']);
					if($cf['exchange_rate'] != $upd['exchange_rate']) $have_history = true;
				}
			}
			$con->sql_freeresult($sql);

			if($have_history == true){
				$his_ins = array();
				$his_ins['currency_code'] = $code;
				$his_ins['exchange_rate'] = $form['exchange_rate'][$code];
				$his_ins['added'] = "CURRENT_TIMESTAMP";
				$his_ins['user_id'] = $sessioninfo['id'];
				$con->sql_query("insert into consignment_forex_history ".mysql_insert_by_field($his_ins));
			}
		}
		if(!$log) $log[] = $LANG['NO_CHANGES_MADE']; // no changes was made to database
		$smarty->assign("log", $log);
		$this->_default();
	}

	function history(){ // trigger forex history past updated by user
		global $con, $smarty;

		$form = $_REQUEST;
		$curr_code = $form['curr_code'];

		if($curr_code){
			$con->sql_query("select cfh.*, user.u as user 
							 from consignment_forex_history cfh
							 left join user on cfh.user_id = user.id
							 where cfh.currency_code = ".ms($curr_code)."
							 order by cfh.added desc");
			
			$history=$con->sql_fetchrowset();
			$con->sql_freeresult();
		}
		$smarty->assign("history", $history);
		$smarty->display("consignment.forex.history.tpl");
	}
	
	function save_currency(){
		global $con, $smarty, $sessioninfo, $LANG,$config;
		$data=$_REQUEST['data'];
		
		$this->save_currency_history();
				
		$con->sql_query("TRUNCATE TABLE `consignment_currency_table`");
		
		$currenttime=date('Y-m-d H:i:s');
		
		foreach($data['from'] as $k=>$v){
			$upd=array();
			$upd['from']=$data['from'][$k];
			$upd['to']=$data['to'][$k];
			$upd['currency']=array();
			$upd['added']=$currenttime;			
			// loop the config to take the different currency types
			foreach($config['consignment_multiple_currency'] as $cc){
				if(!$cc || $cc == $config["arms_currency"]["symbol"]) continue; // if found it's RM or empty, skip
				$currency_code = strtoupper($cc); // string all to uppercase in case user set the currency code in lowercase
				$upd['currency'][$currency_code]=$data['currency'][$currency_code][$k];				
			}
			$upd['currency']=serialize($upd['currency']);
			$upd['user_id']=$sessioninfo['id'];
			
			$con->sql_query("insert into consignment_currency_table ".mysql_insert_by_field($upd));			
		}
		
		log_br($sessioninfo['id'], 'Consignment Forex', 0, "Update currecny table");
		
		$this->_default();
	}
	
	function save_currency_history(){
		global $con, $sessioninfo;
		
		$sql=$con->sql_query("select * from consignment_currency_table");
		while($r = $con->sql_fetchassoc($sql)){
			$upd=array();
			$upd['from']=$r['from'];
			$upd['to']=$r['to'];
			$upd['currency']=$r['currency'];
			$upd['added']=$r['added'];
			$upd['user_id']=$r['user_id'];
			
			$con->sql_query("insert into consignment_currency_table_history ".mysql_insert_by_field($upd));			
		}
	}
}

$Forex = new Forex('Forex');
?>
