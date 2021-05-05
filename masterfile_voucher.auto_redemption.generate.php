<?php
/*
11/22/2016 5:36 PM Andy
- Fixed code should append zero infront.

10/14/2019 3:17 PM William
- Fixed bug when generate multiple batch, skip deplicate voucher code.

10/18/2019 3:10 PM William
- Added new checking to avoid voucher code save negative code.

1/3/2020 9:55 AM William
- Enhanced to insert "membership_guid" field for membership_points table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.
*/
include("include/common.php");
include("masterfile_voucher.auto_redemption.include.php");
if(!$login && is_ajax())	die($LANG['YOU_HAVE_LOGGED_OUT']);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_voucher_auto_redemption']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MST_VOUCHER_AUTO_REDEMP_GENERATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER_AUTO_REDEMP_GENERATE', BRANCH_CODE), "/index.php");
if(!$config['single_server_mode'] && BRANCH_CODE != 'HQ')	js_redirect("Multiple Server mode must generate voucher at HQ", "/index.php");
$maintenance->check(438);

class VOUCHER_AUTO_REDEMPTION_GENERATE extends Module{
	var $branches = array();

    function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
		$con->sql_query("select * from branch where active=1 order by sequence, code");
		while ($r=$con->sql_fetchassoc()){
			$this->branches[$r['id']]=$r;
		}
	    $con->sql_freeresult();
	    $smarty->assign('branches', $this->branches);
	    
	    // load voucher prefix list
        $this->voucher_value_list = load_voucher_value_list($sessioninfo['branch_id']);	// HQ list
	    
		parent::__construct($title);
    }
    
    function _default(){
        global $sessioninfo, $smarty;
        
        // firs time
        if(!isset($_REQUEST['batch_date_from'])){
        	$form['batch_date_from'] = array('');
        	$smarty->assign('form', $form);
        }
        
        $this->display();
    }
	
    private function return_submit_with_error($err, $form){
    	global $smarty;
    	
    	$smarty->assign('form', $form);
		$smarty->assign('err', $err);
		$this->display();
		exit;
    }
    
    function generate_voucher(){
    	global $con, $smarty, $config, $sessioninfo, $LANG;
    	
    	//print_r($_REQUEST);
    	
    	$form = $_REQUEST;
    	
    	$err = array();
    	$no_need_point_check = false;
    	$batch_list = array();
    	$voucher_list = array();
    	
		if(!$form['voucher_use'])	$err[] = "Please at least select one voucher type.";
		else{
			foreach($form['voucher_use'] as $vcid => $dummy){
				$vc_info = $this->voucher_value_list[$vcid]['info'];
				if(!$vc_info){
					$err[] = "Selected voucher type no longer exists.";
				}elseif(!$vc_info['allowed']){
					$err[] = "The voucher value $vc_info[voucher_value] is not allow to redeem.";
				}else{
					if($vc_info['points_use']<=0){
						$no_need_point_check = true;
					}
					
					if($vc_info['points_use']<=0 && $vc_info['max_qty']<=0)	$err[] = "The voucher value $vc_info[voucher_value] have wrong setup settings.";
					$voucher_list[$vc_info['id']] = $vc_info;
				}
			}
		}
		
		if(!$form['print_format'])	$err[] = "Please select printing format.";
		else{
			if(!$config['voucher_member_redeem_print_template'][$form['print_format']])	$err[] = "Printing format is invalid.";
		}
		
		$min_batch_date_from = '';
		$min_batch_date_to = '';
    	
    	if(!$form['interbranch'])	$err[] = "Please select interbranch.";
 		if(!$form['batch_date_from'])	$err[] = "Please add at least 1 batch.";
 		else{
 			$row = 1;
 			
 			foreach($form['batch_date_from'] as $k => $batch_date_from){
 				$batch_date_to = $form['batch_date_to'][$k];
 				
 				$time_from = strtotime($batch_date_from);
 				$time_to = strtotime($batch_date_to);
 				$batch_list[] = array('from'=>$batch_date_from, 'to'=>$batch_date_to);
 				
 				if(!$time_from)	$err[] = "Batch $row date from is empty.";
 				if(!$time_to)	$err[] = "Batch $row date to is empty.";
 				if(!$err && $time_from > $time_to)	$err[] = "Batch $row date to cannot earlier then date from.";
 				
 				if(!$err && (!$min_batch_date_from || strtotime($min_batch_date_from)>$time_from))	$min_batch_date_from = $batch_date_from;
 				if(!$err && (!$min_batch_date_to || strtotime($min_batch_date_to)>$time_to))	$min_batch_date_to = $batch_date_to;
 				
 				$row++;
 			}
 		}
		// check length of code start
		if(strlen($form['code_start']) > 7) $err[]= "Voucher code max number digit is 7";
		
		// check code start
		$exist_code = $this->check_exist_vouchercode($form['code_start']);
		if($exist_code) $err[] =  sprintf($LANG['REDEMPTION_VOUCHER_DUPLICATE'], $form['code_start']); 
		if($form['code_start'] < 0)   $err[]= "Not allow to key in nagative voucher code.";
    	//$err[] = "min_batch_date_from = $min_batch_date_from, min_batch_date_to = $min_batch_date_to";
    	
    	if($err){
    		$this->return_submit_with_error($err, $form);
    	}
    	
    	// calculate voucher for member
    	//print_r($batch_list);
    	
    	// sort batch list from last to first
    	usort($batch_list, array($this, 'sort_batch_list'));
    	//print_r($batch_list);
    	
    	// sort voucher list by highest value to lowest
    	uksort($voucher_list, array($this, 'sort_voucher_list'));
    	//print_r($voucher_list);
    	
    	$batch_list_count = count($batch_list);
    	$voucher_list_count = count($voucher_list);
    	
    	$filter = array();
    	$filter[] = "(parent_nric is null or parent_nric='')";
    	$filter[] = "(card_no is not null and card_no<>'')";
    	$filter[] = "next_expiry_date>".ms($min_batch_date_to);
    	$filter[] = "blocked_date<=0 and terminated_date<=0";
    	if(!$no_need_point_check)	$filter[] = "points>0";
    	
    	$filter = "where ".join(' and ', $filter);
    	
    	$sql = "select membership_guid,nric,name,address,postcode,city,state,card_no,points,next_expiry_date
    	from membership $filter order by card_no";
    	$q_m = $con->sql_query($sql);
    	
    	//print $sql;
    	
    	$max_points_per_batch = mi($form['max_points_use']);
    	
    	$all_redeem_info = array();
    	$total_voucher_out = 0;
    	
    	while($member = $con->sql_fetchassoc($q_m)){
    		$redeem_info = array();
    		$redeem_info['total_available_points'] = $member['points'];
    		$redeem_info['member_info'] = $member;
    		$redeem_info['total_voucher_gain'] = 0;
    		
    		$member_expiry_time = strtotime($member['next_expiry_date']);
    		
    		// divide member point to each batch
    		$point_per_batch = floor($member['points']/$batch_list_count);
    		
    		// check how many points for each bath, if got limit then cannot use more then limit point
    		if($max_points_per_batch>0 && $point_per_batch>$max_points_per_batch)	$point_per_batch = $max_points_per_batch;
    		
    		// loop for each batch
    		$left_point_from_prev_batch = 0;
    		foreach($batch_list as $batch_key=>$batch){
    			$batch_time_from = strtotime($batch['from']);
    			$batch_time_to = strtotime($batch['to']);
    			
    			// every batch start with same available point
    			$this_batch_available_point = $point_per_batch;
    			
    			// check whether got left point from prev batch
    			if($left_point_from_prev_batch>0){
    				// add prev left point to this batch
    				$this_batch_available_point += $left_point_from_prev_batch;
    				
    				// reset left point
    				$left_point_from_prev_batch = 0;
    			}
    			
    			// this batch available point over the limit
    			if($max_points_per_batch>0 && $this_batch_available_point>$max_points_per_batch){
    				// store the over left point
    				$left_point_from_prev_batch = $this_batch_available_point - $max_points_per_batch;
    				
    				// use the max limit point for this batch
    				$this_batch_available_point = $max_points_per_batch;
    			}
    			
    			// only eligible if member does not expiry within this batch time frame
    			if($member_expiry_time>$batch_time_to){
    				// loop for each available voucher
    				foreach($voucher_list as $voucher){
    					$vc_id = mi($voucher['id']);
    					
    					// loop if this voucher still can use
    					while(($this_batch_available_point>0 && $this_batch_available_point >= $voucher['points_use']) || !$voucher['points_use']){
    						
    						
    						// this voucher got limit qty per batch
    						if($voucher['max_qty']>0 && $redeem_info['voucher_usage'][$batch_key][$vc_id]['qty']>=$voucher['max_qty']){
    							// this voucher alrdy reach redeem limit for this batch
    							break;
    						}
    						
    						// redeem this voucher
    						$redeem_info['voucher_usage'][$batch_key][$vc_id]['qty']+=1;
    						$total_voucher_out++;
    						$redeem_info['total_voucher_gain']++;
    						
    						// add points use
    						$redeem_info['voucher_usage'][$batch_key][$vc_id]['points_use'] += $voucher['points_use'];
    						
    						// minus available point
    						$this_batch_available_point -= $voucher['points_use'];
    						$redeem_info['total_points_used'] += $voucher['points_use'];
    						
    						// redeem till so many? impossible !!
    						if($redeem_info['voucher_usage'][$batch_key][$vc_id]['qty']>=99)	break;
    					}
    				}
    			}
    			
    			// store the left point for next batch use
    			$left_point_from_prev_batch += $this_batch_available_point;
    		}
    		
    		if($redeem_info['total_voucher_gain']>0){
    			$all_redeem_info[$member['nric']] = $redeem_info;
    		}
    	}
    	
    	$con->sql_freeresult($q_m);
    	//print_r($all_redeem_info);
		
    	if(!$all_redeem_info || $total_voucher_out<=0){
    		$err[] = "No member eligible for this redemption.";
    		$this->return_submit_with_error($err, $form);
    	}
    	
    	
    	$allow_interbranch = array();
    	foreach($form['interbranch'] as $bid){
    		$allow_interbranch[$bid] = get_branch_code($bid);
    	}
    	$str_allow_interbranch = serialize($allow_interbranch);
    	
    	$curr_voucher_code = $form['code_start'];
    	
    	$batch_no_list = array();
    	
    	$redemp_time = $time_stamp=date("Y-m-d H:i:s");
    	
    	// loop from first batch to last batch
    	for($i = $batch_list_count-1; $i>=0; $i--){
    		$batch_key = $i;
    		$batch = $batch_list[$batch_key];
    		
    		// create batch
    		$new_batch = array();
    		$new_batch['branch_id'] = $sessioninfo['branch_id'];
	        $new_batch['added'] = $redemp_time;
			$new_batch['last_update'] = $redemp_time;
			$new_batch['create_user_id'] = $sessioninfo['id'];
	
			// get new batch no
		    $con->sql_query("select max(batch_no) as max_batch from mst_voucher_batch");
	        $tmp = $con->sql_fetchassoc();
	        $con->sql_freeresult();
	        $batch_no = $tmp['max_batch']+1;
	        $new_batch['batch_no'] = $batch_no;
	        
	        // store batch no
	        $batch_no_list[] = $batch_no;
	        
	        // special type for this batch
	        $new_batch['batch_type'] = 'member_redeem';
	        
	        // insert new batch
	        $con->sql_query("insert into mst_voucher_batch ".mysql_insert_by_field($new_batch));
	        
	        // loop for each member
			foreach($all_redeem_info as $nric => $redeem_info){
				// check this member can redeem anything from this batch or not
				if(!$redeem_info['voucher_usage'][$batch_key])	continue;
				
				// loop for each voucher avalaible to redeem for this member
				foreach($redeem_info['voucher_usage'][$batch_key] as $vc_id => $vc_info){
					for($j=0; $j<$vc_info['qty']; $j++){
						$new_voucher = array();
						$new_voucher['branch_id'] = $new_batch['branch_id'];
						$new_voucher['batch_no'] = $batch_no;
						$used_code = $this->check_exist_vouchercode($curr_voucher_code);
						if($used_code){
							$curr_voucher_code = $this->ajax_get_new_voucher_code(false);
						}
						$new_voucher['code'] = str_pad($curr_voucher_code,7,"0",STR_PAD_LEFT);
						$curr_voucher_code++;
						
						$new_voucher['voucher_value'] = $voucher_list[$vc_id]['voucher_value'];
						$new_voucher['allow_interbranch'] = $str_allow_interbranch;
						$new_voucher['valid_from'] = $batch['from'];
						$new_voucher['valid_to'] = $batch['to'];
						//$new_voucher['active'] = 1;
						//$new_voucher['last_update'] = $new_voucher['added'] = $new_voucher['activated'] = $redemp_time;
						$new_voucher['added'] = $redemp_time;
						$new_voucher['create_user_id'] = $sessioninfo['id'];
						$new_voucher['disallow_disc_promo'] = $form['disallow_disc_promo'];
						$new_voucher['disallow_other_voucher'] = $form['disallow_other_voucher'];
						$new_voucher['member_nric'] = $nric;
						
						$con->sql_query("insert into mst_voucher ".mysql_insert_by_field($new_voucher));
					}					
				}
			}
			
			// activate
	    	$params = array();
			$params['timestamp'] = $redemp_time;
			$params['active_remark'] = 'AUTO ACTIVATE BY SYSTEM';
			$params['valid_from'] = $batch['from'];
			$params['valid_to'] = $batch['to']." 23:59:59";
			$params['disallow_disc_promo'] = $form['disallow_disc_promo'];
			$params['disallow_other_voucher'] = $form['disallow_other_voucher'];
			$params['interbranch'] = $allow_interbranch;
			$params['all_voucher_in_batch'] = 1;
			$params['is_print_only'] = 0;
			activate_voucher(array($batch_no), array(), $params);
    	}
    	
    	// total points use for all members
    	$grand_total_points_used = 0;
    	foreach($all_redeem_info as $nric => $redeem_info){	// loop for each member
    		$upd = array();
			$upd['membership_guid'] = $redeem_info['member_info']['membership_guid'];
    		$upd['nric'] = $nric;
    		$upd['card_no'] = $redeem_info['member_info']['card_no'];
    		$upd['branch_id'] = $sessioninfo['branch_id'];
    		$upd['date'] = $redemp_time;
    		$upd['points'] = $redeem_info['total_points_used']*-1;
    		$upd['remark'] = 'AUTO REDEEM BY SYSTEM TO VOUCHER';
    		$upd['type'] = 'AUTO_REDEEM';
    		$upd['user_id'] = $sessioninfo['id'];
    		
    		// add use points
    		$con->sql_query("insert into membership_points ".mysql_insert_by_field($upd));
    		
    		// deduct points from user
    		$con->sql_query("update membership set points=points+".mi($upd['points'])." where nric=".ms($nric));
    		
    		$grand_total_points_used += $redeem_info['total_points_used'];
    	}
    	
    	
    	
    	// record history - voucher_auto_redemp_history
		$upd = array();
		$upd['branch_id'] = $sessioninfo['branch_id'];
		$upd['added'] = $redemp_time;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['batch_list'] = serialize($batch_no_list);
		$upd['print_format'] = $form['print_format'];
		$upd['form_settings'] = serialize($form);
		$upd['total_points_used'] = $grand_total_points_used;
		$con->sql_query("insert into voucher_auto_redemp_history ".mysql_insert_by_field($upd));
		$his_id = $con->sql_nextid();
		
		log_br($sessioninfo['id'], 'AUTO_REDEMPTION', $his_id, "Auto Redemption was created by $sessioninfo[u] (Branch ID#$sessioninfo[branch_id] ID#$his_id)");
		
		header("Location: $_SERVER[PHP_SELF]?a=open_his&branch_id=$upd[branch_id]&his_id=$his_id&just_done=1");
		exit;
    }
    
    private function sort_batch_list($a, $b){
    	// sort by last batch to first batch
    	$time1 = strtotime($a['to']);
    	$time2 = strtotime($b['to']);
    	
    	if($time1 == $time2)	return 0;
    	return $time1 > $time2 ? -1 : 1;
    }
    
    private function sort_voucher_list($a, $b){
    	// sort by last batch to first batch
    	$v1 = $a['voucher_value'];
    	$v2 = $b['voucher_value'];
    	
    	if($v1 == $v2)	return 0;
    	return $v1 > $v2 ? -1 : 1;
    }
    
    function his_list(){
    	global $con, $smarty, $config, $sessioninfo;
    	
    	$his_list = array();
    	$con->sql_query("select * from voucher_auto_redemp_history where branch_id=".mi($sessioninfo['branch_id'])." and active=1 order by id desc");
    	while($r = $con->sql_fetchassoc()){
    		$r['batch_list'] = unserialize($r['batch_list']);
    		$r['form_settings'] = unserialize($r['form_settings']);
    		
    		$his_list[] = $r;
    	}
    	$con->sql_freeresult();
    	
    	//print_r($his_list);
    	
    	$smarty->assign('his_list', $his_list);
    	$this->display('masterfile_voucher.auto_redemption.generate.his_list.tpl');
    }
    
    function open_his($bid=0, $his_id=0){
    	global $con, $smarty, $config, $sessioninfo;
    	
    	if(!$bid && !$his_id){
    		$bid = mi($_REQUEST['branch_id']);
    		$his_id = mi($_REQUEST['his_id']);
    	}
    	
    	if(!$bid || !$his_id)	die('Invalid Branch ID or History ID');
    	    	
    	$form = load_generate_history($bid, $his_id);
    	if(!$form){
    		js_redirect("History ID#$his_id Not Found.", $_SERVER['PHP_SELF']."?a=his_list");
    	}
    	//print_r($form);
    	
    	$smarty->assign('form', $form);
    	$this->display('masterfile_voucher.auto_redemption.generate.open_his.tpl');
    }
 
 	function cancel_his_from_list(){
 		$bid = mi($_REQUEST['branch_id']);
    	$his_id = mi($_REQUEST['his_id']);
    	
    	$err = $this->perform_cancel_his($bid, $his_id);
    	
    	if($err){
    		$str = '';
    		foreach($err as $e){
    			$str .= "$e\n";
    		}
    		js_redirect($str, $_SERVER['PHP_SELF']."?a=his_list");
    		exit;
    	}
 	}
 	   
    function cancel_his(){
    	global $smarty;
    	
    	
    	$bid = mi($_REQUEST['branch_id']);
    	$his_id = mi($_REQUEST['his_id']);
    	
    	$err = $this->perform_cancel_his($bid, $his_id);
    	
    	if($err){
    		$smarty->assign('err', $err);	
	    	$this->open_his($bid, $his_id);
	    	exit;
    	}
    }
    
    private function perform_cancel_his($bid, $his_id){
    	global $con, $smarty, $config, $sessioninfo;
    	
    	$bid = mi($bid);
    	$his_id = mi($his_id);
    	
    	if($bid<=0 || $his_id<=0)	$err[] = "Invalid Branch ID or History ID";

    	$form = load_generate_history($bid, $his_id);
    	foreach($form['batch_info'] as $batch_no=>$batch_info){
    		if(isset($batch_info['data']['cancel_status']) && !$batch_info['data']['cancel_status'])	$err[] = "Please manually cancel Batch No ($batch_no) first.";
    	}
    	
    	if($err)	return $err;
    	
    	$batch_time = $form['added'];
    	
		// return points to member
		if($form['member_info']){
			foreach($form['member_info'] as $nric=>$mem_info){
				$p = $mem_info['points']*-1;
				if($p>0){
					$con->sql_query("update membership set points=points+$p where nric=".ms($nric));
				}
			}
			// remove points history
			$con->sql_query("delete from membership_points where branch_id=$bid and date=".ms($batch_time)." and type='AUTO_REDEEM'");
		}
		
		$upd = array();
		$upd['active'] = 0;
		$upd['cancel_timestamp'] = 'CURRENT_TIMESTAMP';
		$upd['cancel_by'] = $sessioninfo['id'];
		$con->sql_query("update voucher_auto_redemp_history set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$his_id");
		
		log_br($sessioninfo['id'], 'AUTO_REDEMPTION', $his_id, "Auto Redemption was cancelled by $sessioninfo[u] (Branch ID#$bid ID#$his_id)");
			
		$str = "Auto Redemption ID#$his_id cancelled.";
    	js_redirect($str, $_SERVER['PHP_SELF']."?a=his_list&msg=".urlencode($str));
    	exit;
    }
	
	function ajax_get_new_voucher_code($is_start_code = true){
		global $con;
		$new_code = 1;
		
		$q1 = $con->sql_query("select distinct(code) as code from mst_voucher order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$code = mi($r['code']);
			if($code > $new_code){
				break;
			}
			$new_code = $code+1;
			
		}
		$con->sql_freeresult($q1);
		
		if(!$is_start_code){
			return $new_code;
		}else{
			$ret = array();
			$ret['ok'] = 1;
			$ret['new_code'] = $new_code;
			print json_encode($ret);
		}
	}
	
	function check_exist_vouchercode($voucher_code){
		global $con;
		$exist_code = 0;
		$code = str_pad($voucher_code,7,"0",STR_PAD_LEFT);
		$q1 = $con->sql_query("select distinct(code) as code from mst_voucher where code=$code");
		$num_row = $con->sql_numrows($q1);
		if($num_row > 0){
			$exist_code = 1;
		}
		$con->sql_freeresult($q1);
		return $exist_code;
	}
}

$VOUCHER_AUTO_REDEMPTION_GENERATE = new VOUCHER_AUTO_REDEMPTION_GENERATE('Voucher Auto Redemption Generate');
?>
