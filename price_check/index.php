<?php
/*
12/20/2016 10:13 AM Andy
- Moved to load remote photo script to price_checker.include.php
- Change to load default config from root config.php

1/20/2017 2:47 PM Andy
- Add show stock balance if using main server.
*/
define('PRICE_CHECKER',1);
@include('config.php');
include("../include/common.php");
include("../include/price_checker.include.php");
include("../language.php");
if (!intranet_or_login()) js_redirect($LANG['ACCESS_DENIED_NEED_LOGIN_OR_INTRANET'], "/index.php");

$smarty->template_dir = './templates';
$smarty->compile_dir = './templates_c';

if (!is_dir($smarty->compile_dir)) @mkdir($smarty->compile_dir,0777);

//setcookie('arms_login_branch', BRANCH_CODE,1);

if (isset($_REQUEST['branch'])){
	setcookie('arms_login_branch',$_REQUEST['branch'],strtotime('+1 year'));
	print "<script>parent.window.location = '.';</script>";exit;
}

if(isset($_REQUEST['code'])){
	$_COOKIE['scan_counter']++;
	setcookie('scan_counter',$_COOKIE['scan_counter'],strtotime('+1 year'));
}
//print_r($_COOKIE);
//print_r($_SERVER);

class PRICE_CHECK extends Module{
	var $info_dir = 'info';
	var $branch_info;
	var $branch_id;
	var $client_info;
	var $show_stock = false;
	
	function __construct($title){
		if (!is_dir($this->info_dir)) @mkdir($this->info_dir,0777);
		
		parent::__construct($title);
	}
	
	function _default(){
		global $config, $smarty;
		
		$this->check_branch();
		$this->get_client_info();
		
		if(!defined('SYNC_SERVER')){
			$this->show_stock = true;
			$smarty->assign('show_stock', $this->show_stock);
		}
		
		
		if(trim($_REQUEST['code'])){
			$this->code = trim($_REQUEST['code']);
			$this->check_code();
		}
		
		if($config['price_checker_format']){
			$smarty->display("format_".$config['price_checker_format'].".tpl");
		}else{
			$smarty->display("default_format.tpl");
		}
	}
	
	private function check_branch(){
		global $config, $con, $smarty;
		
		if(isset($_COOKIE['arms_login_branch'])){
			$bcode = strtoupper(trim($_COOKIE['arms_login_branch']));
			//if(in_array($bcode, $config['allowed_branch'])){
				$con->sql_query("select id,code from branch where code = " . ms($bcode));
				$this->branch_info = $con->sql_fetchrow();
				$this->branch_id = $this->branch_info[0];
			//}
			if(!$this->branch_id)	die("Invalid Branch ".$bcode);
		}else{
			$con->sql_query("select id,code from branch where code = " . ms(BRANCH_CODE));
			$this->branch_info=$con->sql_fetchrow();
			$this->branch_id=$this->branch_info[0];
			
			if(!$this->branch_id)	die("Invalid Branch ".BRANCH_CODE);
		}
		
		$smarty->assign("branch_info", $this->branch_info);
	}
	
	private function check_code(){
		global $con, $smarty, $config;
		
		$code = trim($this->code);
		if (preg_match('/^28/',$code)) $code = substr($code,0,12);

		$params = array();
		$params['branch_id'] = $this->branch_id;
		$params['code'] = $code;
		$params['get_cat_info'] = 1;
		$params['get_remote_photo'] = 1;
		$params['hq_server_url'] = $config['hq_server_url'];
		if($this->show_stock)	$params['get_stock'] = 1;
		$sku = check_price($params);

		/*if($sku['error']){
			$member = check_member($params);
			if(!$member){
				$smarty->display("check.not_found.tpl");
				exit;
			}else{
				$smarty->assign("member", $member);
			}
		}*/
		
		/*if($sku['id'] && !$sku['error']){
			if(!$sku['photos'] && $config['hq_server_url']){
				$url_to_get_photo = $config['hq_server_url']."/http_con.php?a=get_sku_item_photo_list&sku_item_id=".mi($sku['id'])."&sku_apply_items_id=".mi($sku['sku_apply_items_id'])."&SKIP_CONNECT_MYSQL=1";
				//print $url_to_get_photo;
				
				$tmp_photo_list = @file_get_contents($url_to_get_photo);
				$sku['photos'] = @unserialize($tmp_photo_list);				
			}
			if($sku['photos']){
				//$sku['photo'] = ($config['hq_server_url']?$config['hq_server_url'].'/':'').$sku['photos'][0]; // only take the first photo
				$sku['photo'] = $sku['photos'][0]; // only take the first photo
			}
		}*/

		$smarty->assign("sku", $sku);
		//print_r($sku);
		
		// add one scan count
		$this->client_info['count']++;
		
		$this->update_client_info();
	}
	
	private function get_client_info(){
		global $client_ip, $smarty;
		$filepath = $this->info_dir."/".$client_ip.".txt";
		//print "filepath = $filepath";
		//if(!file_exists($filepath))	return;
		
		$str = file_get_contents($filepath);
		$this->client_info = unserialize($str);
		if(!$this->client_info)	$this->client_info = array();
		$smarty->assign('client_info', $this->client_info);
		$smarty->assign('scan_counter', $_COOKIE['scan_counter']);
	}
	
	private function set_client_info(){
		global $client_ip;
		$filepath = $this->info_dir."/".$client_ip.".txt";
		file_put_contents($filepath, serialize($this->client_info));
	}
	
	private function update_client_info(){
		global $smarty;
		
		//print_r($client_info);
		$this->set_client_info();
		$smarty->assign('client_info', $this->client_info);
	}
}

$PRICE_CHECK = new PRICE_CHECK('Price Checker');
//print '<meta http-equiv="refresh" content="30;URL=.">';
?>

