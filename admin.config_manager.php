<?php
/*
4/4/2011 5:58:15 PM Andy
- Change format due to multi dimension array failed to parse in some PHP version.

6/30/2011 6:01:16 PM Andy
- Fix array type config validation bugs.

9/9/2013 3:42 PM Andy
- Change config manager update method.
- Add log to capture what changes on config manager when user update.

10/21/2013 5:46 PM Andy
- Enhance to save counter_config.txt when update config manager.

1/7/2013 2:33 PM Andy
- Add config "mix_and_match_show_prompt_available" and "tpproperty_setting" into counter_config.txt

03/05/2014 9:39 AM Kee Kee
- Add config "receipt_running_no" into counter_config.txt

04/10/2014
- Add 'use_grn_future','grn_group_same_item' and 'do_skip_generate_grn'N config into counter_config.txt

4/15/2014 11:30 AM Justin
- Enhanced to add sku_weight_code_length into config txt.

02/16/2016 2:50 PM Kee Kee
- Add config "open_disc_entered_disc_amt" into counter_config.txt

11/2/2016 11:09 AM Qiu Ying
- Add membership_type in counter_config.txt
- Add membership_staff_type in counter_config.txt

11/8/2016 10:58 AM Andy
- Enhanced to able to run from terminal to generate config txt for counter.

04/25/2017 13:15 PM Kee Kee
- Added "cash_domination_notes" into counter_config_file.txt

4/25/2017 4:03 PM Andy
- Added "arms_currency" into counter_config_file.txt, not sure whether will use in future or not.

10/31/2017 4:11 PM Justin
- Enhanced to have generate default value for config "se_relief_claus_remark".

11/2/2017 5:40 PM Andy
- Enhanced to put config "pos_cash_advance_reason_list" into config text file.

6/22/2018 9:20 AM Justin
- Enhanced to put config "foreign_currency" and "foreign_currency_decimal_points" into config text file.
*/
include("include/common.php");

if(is_using_terminal()){
	ob_end_flush();
	
	if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
		@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps x\n";
	}else{
		@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps ax\n";
	}

	if (count($exec)>1)
	{
		print date("[H:i:s m.d.y]")." Another process is already running\n";
		print_r($exec);
		exit;
	}

	$arg = $_SERVER['argv'];
	$a = trim($arg[1]);	// get argument
	if(!$a)	die("Invalid Action\n");	// terminal must have an action call
	
	$_REQUEST['a'] = $a;
	
	set_time_limit(0);
}else{
	if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
	if($sessioninfo['id']!=1 || BRANCH_CODE != 'HQ')   js_redirect('This module is only accessible by top user.', "/index.php");
}

$maintenance->check(60);

class CONFIG_MANAGER extends Module{
	var $branch_id = 0;
	var $config_list = array();
	var $counter_config_file = 'counter_config.txt';
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		if(!$_REQUEST['skip_init'])	$this->init_selection();
		$this->branch_id = mi($sessioninfo['branch_id']);

		parent::__construct($title);
	}
	
	private function init_selection(){
	    global $con, $sessioninfo, $smarty;
	    
	    // try to parse INI file
        if($pure_config_list = parse_ini_file("admin.config_manager.ini", true)){
            //print_r($pure_config_list);
            
            // re-construct config list, due to parse ini bugs cannot use section for some php version
            $group_name = '';
            foreach($pure_config_list as $key => $data){
                list($config_name, $type) = explode("-", $key);
                if($config_name=='GROUP_CHANGE'){   // group change
                    $group_name = $data;
                    continue;
				}
                $temp_config_list[$group_name][$config_name][$type] = $data;
            }
			
			//print_r($temp_config_list);
			foreach($temp_config_list as $section_key => $section){
			    foreach($section as $config_name => $data){
			        $r = array();
			        $v = trim($data['value']);
			        $t = trim($data['type']);
			        
			        $r['default_info'] = $data;
			        
			        // check config type
			        list($r['type'], $r['description']) = explode(',', $t, 2);
					
					switch($r['type']){
					    case 'select':
						case 'radio':
						    if(!$v) $v = "Yes:1||No:0"; // default yes, no
						    
						    if($temp_arr = explode('||', $v)){
								foreach($temp_arr as $temp){
								    list($value_name, $value_key) = explode(':', $temp);
								    if($value_key=='') $value_key = $value_name;    // if no provide key, use label as key
									$r['value'][$value_key] = $value_name;
								}
							}
						    
						    break;
					}
                    $this->config_list[$section_key][$config_name] = $r;
				}
			}
		}
        
        $smarty->assign('config_list', $this->config_list);
	}
	
	function _default(){
	    global $con, $sessioninfo, $smarty;

		//if(file_exists("admin.config_manager.ini")) print "file exists";
		//print_r($this->config_list);
	    $this->display();
	}
	
	function save_config(){
        global $con, $sessioninfo, $smarty;
        
        //print_r($_REQUEST);
        $data = $_REQUEST['config_master'];
        //print_r($data);exit;
        // check the submited data can save or not
        $upd = array();
        $this->validate_data($data, $err, $upd);
        
        if($err){   // got error
            $ret['error'] = $err;
			print json_encode($ret);
			exit;
		}
		
		// get current config_master
		$cm = array();
		$con->sql_query("select * from config_master");
		while($r = $con->sql_fetchassoc()){
			$cm[$r['config_name']] = $r;
		}
		$con->sql_freeresult();
		
		// no error found
		// set all to in-active first
		//$con->sql_query("update config_master set active=0");
		$changes_log = array();
		if($upd){
			foreach($upd as $cf){
				if(!$cf['active']){	// in-acitve config
					if($cm[$cf['config_name']]['active']){
						$changes_log[] = "Inactive config: ".print_r($cf, true);
					}
					$con->sql_query("update config_master set active=0 where config_name=".ms($cf['config_name']));	// inactive config
					continue;
				}else{
					if(!isset($cm[$cf['config_name']])){
						$changes_log[] = "Add new config: ".print_r($cf, true);	// new config
					}else{
						if($cm[$cf['config_name']]['active'] != $cf['active'] || $cm[$cf['config_name']]['type'] != $cf['type'] || $cm[$cf['config_name']]['value'] != $cf['value']){
							$changes_log[] = "Update config: ".print_r($cf, true);	// update config
						}
					}
				}
				
				$con->sql_query("replace into config_master ".mysql_insert_by_field($cf));
			}
		}
		
		log_br($sessioninfo['id'],'CONFIG_MANAGER', 0, "Update Config Manager: ".print_r($changes_log, true));
		
		$this->generate_config_text();
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	private function validate_data($data, &$err, &$upd){
        global $con, $sessioninfo, $smarty;
        
        if(!$data)  return; // no config to active
        
        foreach($data as $config_name=>$r){
			//if(!$r['active'])   continue;   // inactive config
			$tmp = array();
			$tmp['config_name'] = $config_name;
			
			$err_msg = '';
			
			$v = trim($r['value']);
			if($r['type']=='array'){    // is array type
			    if($v==''){ // empty data
                    $err_msg = 'Empty data is not allowed.';
				}else{
				    $eval_success = eval($v);
				    if ($eval_success === false && ( $error = error_get_last() ) ) {
					    //myErrorHandler( $error['type'], $error['message'], $error['file'], $error['line'], null );
					    $err_msg = $error['message'];
					}
				}
			    
			}
			
			if($err_msg){   // found error for this config value
				$err[] = array('config_name'=>$config_name, 'error_msg'=>$err_msg);
				continue;
			}
			
			$tmp['active'] = $r['active'];
			$tmp['type'] = $r['type'];
			$tmp['value'] = $v;
			$upd[] = $tmp;
		}
	}
	
	function generate_config_text(){
		global $con, $config, $default_config;
		
		$config = $default_config;
		config_master_override();
		
		$cf_list = array(
			'sku_multiple_selling_price',
			'sku_multiple_quantity_price',
			'coupon_use_percentage',
			'coupon_amount_0_5_cent',
			'membership_use_card_prefix',
			'enable_sn_bn',
			'masterfile_enable_sa',
			'masterfile_sa_code_prefix',
			'membership_enable_staff_card',
			'currency_settings',
			'sku_enable_additional_description',
			'sku_multiple_quantity_mprice',
			'user_profile_show_item_discount_only_allow_percent',
			'mix_and_match_show_prompt_available',
			'tpproperty_setting',
			'receipt_running_no',
			'use_grn_future',
			'grn_group_same_item',
			'do_skip_generate_grn',
			'sku_weight_code_length',
			'no_banner_in_counter',
			'open_disc_entered_disc_amt',
			'membership_type',
			'membership_staff_type',
			'cash_domination_notes',
			'arms_currency',
			'se_relief_claus_remark',
			'pos_cash_advance_reason_list',
			'foreign_currency',
			'foreign_currency_decimal_points'
		);
		
		$data = array();
		foreach($cf_list as $v){
			if(isset($config[$v]))	$data[$v] = $config[$v];
		}
		file_put_contents($this->counter_config_file, serialize($data));
		chmod($this->counter_config_file, 0777);
	}
}

$CONFIG_MANAGER = new CONFIG_MANAGER('Config Manager');
?>
