<?
/*
11/29/2010 11:44:56 AM Alex
- created by me

4/12/2011 4:29:10 PM Alex
- move allow interbranch from register to activate
- display more clear error message 

4/20/2011 6:00:42 PM Alex
- fix date valid_to bugs

5/27/2011 4:06:17 PM Alex
- add checking voucher value

8/22/2011 3:06:32 PM Alex
- change voucher can check multiple branch

10/21/2011 5:07:46 PM Alex
- change log to save allow interbranch

11/8/2011 5:41:36 PM Alex
- -1 day for date duration

3/14/2012 4:12:49 PM Alex
- change voucher code to uppercase when checking
 
3/20/2012 12:05:01 PM Alex
- add batch list to be choose for activation 

5/14/2012 4:04:23 PM Justin
- Added to update new field "disallow_disc_promo" and "disallow_other_voucher" into voucher_mst.

6/15/2012 04:01:00 PM Andy
- Change activate voucher script to include file.

11/26/2012 10:13:00 AM Fithri
- pre-select the choice of Valid Date to ""Duration".
- pre-check all interbranches instead just the logged on branch.
- Added new options "disallow_disc_promo" and "disallow_other_voucher" for user to maintain.

1/11/2013 5:17 PM Justin
- Enhanced to capture and show voucher code from membership redemption.
- Enhanced to auto display voucher codes that validate from Membership Redemption.

1/18/2013 1:44 PM Justin
- Bug fixed on system picking up duplicate voucher codes from redemption.

2/28/2013 5:55 PM Justin
- Enhanced to allow user can activate batch/code across branch base on config "voucher_allow_cross_branch_activate".

4/24/2013 4:08 PM Justin
- Enhanced to allow user to activate vouchers while found user was redirected from membership redemption and has new privilege "MEMBERSHIP_ALLOW_ACTIVATE_VOUCHER".

4/25/2013 5:33 PM Justin
- Enhanced to close this module while user does not have main privilege after activated the vouchers from membership.
- Bug fixed on voucher "valid date to" comparison.

8/21/2013 11:44 AM Justin
- Bug fixed on system always pick active remark as "Redemption" while activate vouchers from redemption.

6/26/2019 5:38 PM Andy
- Enhanced to can link voucher to member.

7/17/2019 4:07 PM Andy
- Enhanced to auto load member card_no when redirect from membership redemption.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if($_REQUEST['mr_id'] && $_REQUEST['mr_branch_id']){
	if(!privilege('MEMBERSHIP_ALLOW_ACTIVATE_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ALLOW_ACTIVATE_VOUCHER', BRANCH_CODE), "/index.php");
}else{
	if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
	if (!privilege('MST_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER', BRANCH_CODE), "/index.php");
	if (!privilege('MST_VOUCHER_ACTIVATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_VOUCHER_ACTIVATE', BRANCH_CODE), "/index.php");
}
$maintenance->check(175);

//ONLY OWN BRANCH CAN ACTIVATE CODES that assign by HQ
include("masterfile_voucher.include.php");
class Voucher extends Module{

	var $org_code=array();
	var $date_valid_to="";
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		$duration_valid = $config['voucher_active_month_duration'] ? $config['voucher_active_month_duration'] : 6;    //by month
	
		if (!$_REQUEST['valid_from'])
			$_REQUEST['valid_from']=date('Y-m-d');

		if (!$_REQUEST['valid_to'])
	        $_REQUEST['valid_to']=date('Y-m-d',strtotime("+$duration_valid month -1 day", strtotime($_REQUEST['valid_from'])));

		$con->sql_query("select * from branch where active=1 order by sequence, code");
		while ($r=$con->sql_fetchassoc()){
			$this->branches[$r['id']]=$r['code'];
		}
	    $con->sql_freeresult();
		
		if($_REQUEST['mr_id'] && $_REQUEST['mr_branch_id']){
			$q1 = $con->sql_query("select mri.voucher_code, mr.card_no
								   from membership_redemption_items mri
								   join membership_redemption mr on mr.branch_id=mri.branch_id and mr.id=mri.membership_redemption_id
								   where mri.membership_redemption_id = ".mi($_REQUEST['mr_id'])." and mri.branch_id = ".mi($_REQUEST['mr_branch_id']));
			while($r = $con->sql_fetchassoc($q1)){
				if($r['card_no'])	$_REQUEST['member_link'] = $r['card_no'];
				
				$r['voucher_code'] = unserialize($r['voucher_code']);
				foreach($r['voucher_code'] as $row=>$vc){
					$codes[$vc] = $vc;
				}
			}
			$_REQUEST['codes'] = join("\n", $codes);
			if($_REQUEST['codes'] && !$_REQUEST['active_remark']) $_REQUEST['active_remark'] = "Redemption";
		}

	    $smarty->assign('form',$_REQUEST);
		$smarty->assign('branches',$this->branches);
			    
 		parent::__construct($title);
	}

	function _default(){
		global $con;
		$con->sql_query("select * from branch where active=1 order by sequence, code");
		while ($r=$con->sql_fetchassoc()){
			$_REQUEST['interbranch'][$r['id']]=1;
		}
	    $con->sql_freeresult();
	    $this->load_batch_no();
	    $this->display();
	    exit;
	}
	
	function load_batch_no(){
		global $con,$smarty, $sessioninfo, $config;
		//default load batch no, min and max code
		
		if(!$config['voucher_allow_cross_branch_activate']) $filter = " and mv.branch_id=".mi($sessioninfo['branch_id']);
		
		$con->sql_query("select mv.batch_no, min(mv.code) as min_code, max(mv.code) as max_code, b.code as branch_code
						 from mst_voucher mv
						 left join branch b on b.id = mv.branch_id
						 where mv.cancel_status=0 and mv.active=0 $filter 
						 group by mv.batch_no, mv.branch_id
						 order by b.sequence asc, b.code asc, mv.batch_no desc");
		while ($r=$con->sql_fetchassoc()){
			$batch_nos[$r['batch_no']]['min_code']=$r['min_code'];
			$batch_nos[$r['batch_no']]['max_code']=$r['max_code'];
			$batch_nos[$r['batch_no']]['branch_code']=$r['branch_code'];
		}
	    $con->sql_freeresult();
		
		$smarty->assign('batch_nos',$batch_nos);	
	}
	
	function activate_form(){
		$form=$_REQUEST;
		//============get end date==========
		$date_type=$form['rdo_end'];

		if ($date_type == 'valid_to'){

		    if ($form['valid_from'] > $form[$date_type]){
				$err[] = $LANG['VOU_OVER_DATE'];
			}
		    $this->date_valid_to=$form[$date_type];
		}else{
            $this->date_valid_to=date("Y-m-d",strtotime("+$form[$date_type] month -1 day", strtotime($form['valid_from'])));
		}
	
		//==========end get===========
		
		if ($form['activate_by'] == 'batch_no'){
			$this->activate_by_batch();
		}else{
			$this->activate_by_list();
		}
	}
	
	function activate_by_list(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $appCore;

	    $form=$_REQUEST;
		$prefix_list = array();
		$filter = "";

		if(!$config['voucher_allow_cross_branch_activate']){
			$filter = " and ps.branch_id=".mi($sessioninfo['branch_id']);
		}
	
		$ps_res=$con->sql_query("select ps.setting_value, ps.branch_id
								from pos_settings ps
								where ps.setting_name='barcode_voucher_prefix'".$filter);

		while($r = $con->sql_fetchassoc($ps_res)){
			$prefix_list[$r['branch_id']] = $r['setting_value'];
		}
		$con->sql_freeresult($ps_res);
		if(!$prefix_list || !in_array("VC", $prefix_list)) $prefix_list[$sessioninfo['branch_id']]="VC";
        
        $codes_arr = explode("\n",trim($form['codes']));
		foreach ($codes_arr as $data){
		    $data=trim($data);
		    if ($data){
				$is_valid_voucher = false;
				foreach($prefix_list as $bid => $prefix){
					$input_barcode=substr($data,strlen($prefix),12);
					$voucher_code=substr($input_barcode,0,7);
					$voucher_amount = substr($input_barcode,-5 , 5)/100;                

					$this->org_code[$voucher_code]=$data;
					$check_code[$voucher_code]=$data;
					$check_amount[$voucher_code]=$voucher_amount;
					
					// check last 2 digit encryption
					$barcode_verify_code = substr($data,-2);
					$verify_code=substr(encrypt_for_verification($input_barcode),0,2);

					if (strtoupper($barcode_verify_code) == strtoupper($verify_code) && strtoupper($prefix) == strtoupper(substr($data,0,strlen($prefix)))){
						$codes_r[$voucher_code]=ms($voucher_code);
						if($config['voucher_allow_cross_branch_activate'] && $config['single_server_mode']){
							$q1 = $con->sql_query("select * from mst_voucher where code = ".ms($voucher_code)." and branch_id = ".mi($bid));
							
							if($con->sql_numrows($q1) > 0){
								$branches_used[$bid][$voucher_code] = $data;
								$is_valid_voucher = true;
							}
						}
					}else{
						if(!$config['voucher_allow_cross_branch_activate'] && $config['single_server_mode']) $invalid_code[$data]=$data;
					}
				}
				
				if($config['voucher_allow_cross_branch_activate'] && $config['single_server_mode']){
					if(!$is_valid_voucher) $invalid_code[$data]=$data;
					if($branches_used){
						foreach($branches_used as $bid=>$voucher){
							if(!$form['interbranch'][$bid]) $branch_require[$bid] = join(", ", $voucher);
						}
					}
				}
			}
		}

		//invalid code show not exist
		if ($invalid_code)	$err[]=sprintf($LANG['VOU_CODE_NO_EXIST'],join(", ",$invalid_code));
		elseif (!$invalid_code && !$codes_r) $err[]="Please enter a code.";
		
		// found user trying to approve voucher across branches but did not tick the 
		if($branch_require){
			foreach($branch_require as $bid=>$voucher_list){
				$err[] = sprintf($LANG['VOU_BRANCH_REQUIRED'], get_branch_code($bid), "Voucher", $voucher_list);
			}
		}
		
		if(trim($form['member_link'])){
			$member = $appCore->memberManager->getMember($form['member_link']);
			
			if(!$member){
				$err[] = "Member ($member_link) Not Found.";
			}else{
				$member_card_no = trim($member['card_no']);
			}
		}

		if ($err){
			$smarty->assign('err',$err);
			$this->_default();
		}

        $total_codes=count($codes_r);
		$codes = join(",",$codes_r);

		//print_r($this->org_code);
		
		$con->sql_query("select mv.*
						from mst_voucher mv
						where mv.code in ($codes)");
		while ($r=$con->sql_fetchassoc()){

			unset($check_code[$r['code']]);

			if ($r['branch_id'] != $sessioninfo['branch_id'] && !$config['voucher_allow_cross_branch_activate'])   $not_branch_code[$r['code']]=$r['code'];
			elseif ($r['voucher_value'] != $check_amount[$r['code']])	$value_code[$r['code']]=$r['code'];
			elseif ($r['active'] == '1')    	$active_code[$r['code']]=$r['code'];
			elseif ($r['cancel_status'] == '1')	$cancel_code[$r['code']]=$r['code'];
			elseif ($r['is_print'] == '0')		$not_print_code[$r['code']]=$r['code'];
			elseif ($r['valid_to'] > 0 && strtotime($r['valid_to'])<time()) $expired_code[$r['code']] = $r['code'];

			$voucher_batch_arr[$r['batch_no']] = $r['batch_no'];
		}
		$con->sql_freeresult();
		
		//check invalid code return error message
		if ($check_code){
 			$err=sprintf($LANG['VOU_CODE_NO_EXIST'],join(", ",$check_code));
		}
		
		if ($value_code){
            $err=$this->filter_error_code($value_code,$LANG['VOU_CODE_VALUE_NOT_MATCH']);		
		}

		if ($not_branch_code){
            $err=$this->filter_error_code($not_branch_code,$LANG['VOU_CODE_NOT_BRANCH']);
		}
		
		if ($active_code){
			$err=$this->filter_error_code($active_code,$LANG['VOU_CODE_HAD_ACTIVATED']);
		}

		if ($cancel_code){
			$err=$this->filter_error_code($cancel_code,$LANG['VOU_CODE_HAD_CANCELLED']);
	  	}

		if ($not_print_code){
			$err=$this->filter_error_code($not_print_code,$LANG['VOU_CODE_NOT_PRINTED']);
		}
		
		if ($expired_code){
			$err=$this->filter_error_code($expired_code,$LANG['VOU_CODE_EXPIRED']);
		}

		if ($err){
			$smarty->assign('err',$err);
		}else{
			$time_stamp=date("Y-m-d H:i:s");

			$params = array();
			$params['timestamp'] = $time_stamp;
			$params['active_remark'] = $form['active_remark'];
			$params['valid_from'] = $form['valid_from'];
			$params['valid_to'] = $this->date_valid_to." 23:59:59";
			$params['disallow_disc_promo'] = $form['disallow_disc_promo'];
			$params['disallow_other_voucher'] = $form['disallow_other_voucher'];
			
			//interbranch
			$interbranch = array();
			foreach($form['interbranch'] as $bid => $dummy){
				$interbranch[$bid]=$this->branches[$bid];
			}
			$params['interbranch'] = $interbranch;
			if($member_card_no)	$params['member_card_no'] = $member_card_no;
			
			$code_to_activate = array_keys($this->org_code);
			activate_voucher($voucher_batch_arr, $code_to_activate, $params);

			$smarty->assign('suc',sprintf($LANG["VOU_CODE_ACTIVATE"],$total_codes));
			
			if($_REQUEST['mr_id'] && $_REQUEST['mr_branch_id']){
				unset($_REQUEST['mr_id']);
				unset($_REQUEST['mr_branch_id']);
				unset($_REQUEST['codes']);
				unset($_REQUEST['active_remark']);
				$smarty->assign('form',$_REQUEST);
				
				if(!privilege('MST_VOUCHER_ACTIVATE')){
					print "<script>";
					print "alert(\"Voucher Activated Sucessfully.\");";
					print "window.close();";
					print "</script>";
					exit;
				}
			}
		}
	    $this->load_batch_no();

		$this->display();
	}
	
	function activate_by_batch(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $appCore;

	    $form=$_REQUEST;

		if (count($form['batch_nos'])>0){
			ksort($form['batch_nos']);
			$batch_nos=implode(",",$form['batch_nos']);
				
			$sql = "select mv.*
							from mst_voucher mv
							where mv.cancel_status=0 and mv.active=0 and mv.batch_no in ($batch_nos)";
			$con->sql_query($sql);
			
			if ($con->sql_numrows()>0){
				while ($r=$con->sql_fetchassoc()){
					if ($r['branch_id'] != $sessioninfo['branch_id'] && !$config['voucher_allow_cross_branch_activate'])   $not_branch_code[$r['code']]=$r['code'];
					elseif ($r['is_print'] == '0')		$not_print_code[$r['code']]=$r['code'];
					
					if($config['voucher_allow_cross_branch_activate'] && !$form['interbranch'][$r['branch_id']]) $branch_require[$r['branch_id']][$r['batch_no']] = $r['batch_no'];
				}
				
				if ($not_print_code){
					$err=$this->filter_error_code($not_print_code,$LANG['VOU_CODE_NOT_PRINTED']);
				}
				
				if($branch_require){
					foreach($branch_require as $bid=>$bn_list){
						$err[] = sprintf($LANG['VOU_BRANCH_REQUIRED'], get_branch_code($bid), "Batch No", join(", ", $bn_list));
					}
				}
			}else{
				$err=$LANG["VOU_NO_CODE_ACTIVATE"];
			}
		}else{
			$err=$LANG["VOU_NO_BATCH_SELECT"];
		}
		
		if(trim($form['member_link'])){			
			$member = $appCore->memberManager->getMember($form['member_link']);
			
			if(!$member){
				$err[] = "Member ($member_link) Not Found.";
			}else{
				$member_card_no = trim($member['card_no']);
			}
		}
		
		if ($err){
			$smarty->assign('err',$err);
		}else{

			$time_stamp=date("Y-m-d H:i:s");
			
			$params = array();
			$params['timestamp'] = $time_stamp;
			$params['active_remark'] = $form['active_remark'];
			$params['valid_from'] = $form['valid_from'];
			$params['valid_to'] = $this->date_valid_to." 23:59:59";
			$params['disallow_disc_promo'] = $form['disallow_disc_promo'];
			$params['disallow_other_voucher'] = $form['disallow_other_voucher'];
			$params['all_voucher_in_batch'] = true;	// all voucher in batch
			$voucher_batch_arr = $form['batch_nos'];
			
			//interbranch
			$interbranch = array();
			foreach($form['interbranch'] as $bid => $dummy){
				$interbranch[$bid]=$this->branches[$bid];
			}
			$params['interbranch'] = $interbranch;
			if($member_card_no)	$params['member_card_no'] = $member_card_no;
			
			$code_to_activate = array_keys($this->org_code);
			activate_voucher($voucher_batch_arr, $code_to_activate, $params);
			
			log_br($sessioninfo['id'], 'VOUCHER',$sessioninfo['branch_id'], "Activate voucher batch ($batch_nos) , From Branch:".get_branch_code($sessioninfo['branch_id']).", Allow branches: ".join(", ", $interbranch));
			
			$smarty->assign('suc',sprintf($LANG["VOU_BATCH_ACTIVATE"],$batch_nos));
		}
	    $this->load_batch_no();

		$this->display();
	}

	function filter_error_code($err_code, $language){
	    foreach ($err_code as $err_c){
   			if ($_REQUEST['activate_by'] == 'batch_no')
            	$msg_code[]=$err_c;
            else
            	$msg_code[]=$this->org_code[$err_c];
		}
	    
		$err[]=sprintf($language,join(", ",$msg_code));
		return $err;
	}
}

$Voucher=new Voucher("Voucher Activation");

?>
