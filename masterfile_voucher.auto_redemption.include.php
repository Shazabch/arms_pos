<?php
/*
10/17/2019 10:47 AM William
- Enhanced voucher value use the "Voucher Setup" value.
*/
include("masterfile_voucher.include.php");

function load_voucher_value_list($bid){
    global $con, $smarty, $sessioninfo, $config;
    
    $voucher_value_list = array();
    // extend list
    $con->sql_query("select * from voucher_auto_redemp_master where branch_id=".mi($bid)." order by voucher_value");
    while($r = $con->sql_fetchassoc()){
        $tmp['voucher_value'] = round($r['voucher_value'], 2);
        $tmp['info'] = $r;
		$voucher_value_list[] = $tmp;
	}
    $con->sql_freeresult();
	
	// get voucher value from mst_voucher_setup
	$q1 = $con->sql_query("select * from mst_voucher_setup");
	$num_row = $con->sql_numrows($q1);
	if($num_row > 0){
		while($r1 = $con->sql_fetchassoc($q1)){
			$voucher_setup_value['voucher_value'] = $r1['voucher_value'];
			foreach($voucher_value_list as $key=>$val){
				if($val['voucher_value'] == $r1['voucher_value']){
					$voucher_setup_value['info'] = $val['info'];
				}
			}
			$voucher_value_info[] = $voucher_setup_value;
			unset($voucher_setup_value);
		}
	}else{  // get from config when no any voucher value
		if($config['voucher_value_prefix']){
			foreach($config['voucher_value_prefix'] as $v){
				$v = round($v ,2);
				$tmp = array();
				$tmp['voucher_value'] = $v;
				$config_voucher_value_list[] = $tmp;
			}
		}
		if($config_voucher_value_list){
			foreach($config_voucher_value_list as $key => $v_list){
				$config_voucher_setup_value['voucher_value'] = $v_list['voucher_value'];
				foreach($voucher_value_list as $key=>$val){
					if($val['voucher_value'] == $v_list['voucher_value']){
						$config_voucher_setup_value['info'] = $val['info'];
					}
				}
				$voucher_value_info[] = $config_voucher_setup_value;
				unset($config_voucher_setup_value);
			}
		}
	}
	$con->sql_freeresult($q1);
	
    usort($voucher_value_list, "sort_voucher_value_list");
    //print_r($voucher_value_list);
    $smarty->assign('voucher_value_list', $voucher_value_info);
    return $voucher_value_info;
}

function sort_voucher_value_list($a, $b){
    if($a['voucher_value'] == $b['voucher_value'])  return 0;
    return ($a['voucher_value'] > $b['voucher_value'])  ? 1 : 0;
}

function load_generate_history($bid, $his_id){
	global $con, $smarty, $sessioninfo, $config;
	
	$con->sql_query("select * from voucher_auto_redemp_history where branch_id=$bid and id=$his_id and active=1");
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	if($form){
		$form['batch_list'] = unserialize($form['batch_list']);
		$form['form_settings'] = unserialize($form['form_settings']);
		
		$batch_list = array();
		
		if($form['batch_list']){
			// get code from to
			foreach($form['batch_list'] as $batch_no){
				$batch_list[] = $batch_no;
				
				// get batch info
				$con->sql_query("select * from mst_voucher_batch where branch_id=$bid and batch_no=".mi($batch_no));
				$form['batch_info'][$batch_no]['data'] = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				// get from/to code
				$con->sql_query("select min(code) as from_code, max(code) as to_code from mst_voucher where branch_id=$bid and batch_no=".mi($batch_no));
				while($r = $con->sql_fetchassoc()){
					$form['batch_info'][$batch_no]['from_code'] = $r['from_code'];
					$form['batch_info'][$batch_no]['to_code'] = $r['to_code'];
				}
				$con->sql_freeresult();
			}
		}
		
		// get membership points info
	
		$con->sql_query("select nric,card_no,points from membership_points where type='AUTO_REDEEM' and date=".ms($form['added'])." and branch_id=".mi($form['branch_id'])." order by card_no");
		while($r = $con->sql_fetchassoc()){
			$form['member_info'][$r['nric']] = $r;
		}
		$con->sql_freeresult();
		
		if($form['member_info']){
			foreach($form['member_info'] as $nric=>$mem_info){
				$con->sql_query("select points from membership where nric=".ms($nric));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($tmp){
					$form['member_info'][$nric]['points_left'] = $tmp['points'];
					$form['member_info'][$nric]['points_before'] = $tmp['points'] + ($mem_info['points']*-1);
				}
				
				// get voucher info
				if($batch_list){
					$con->sql_query("select voucher_value from mst_voucher where branch_id=$bid and batch_no in (".join(',', $batch_list).")  and  member_nric=".ms($nric));
					while($r = $con->sql_fetchassoc()){
						$form['member_info'][$nric]['voucher_data'][$r['voucher_value']]++;
						$form['member_info'][$nric]['total_voucher_get']++;
						
						$form['total_data']['voucher_data'][$r['voucher_value']]++;
						$form['total_data']['total_voucher_get']++;
					}
					$con->sql_freeresult();
				}
			}
		}
	}	
	
	return $form;
}
?>
