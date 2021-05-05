<?php
/*
2/19/2020 11:28 AM Andy
- Enhanced api "member_login" to return different error when member account is not mobile_registered, not verified or blocked.

11/20/2020 1:55 PM William
- Enhanced to add new api "check_member".

2/4/2021 12:38 PM William
- Enhanced check_member api to return error message when member card expired.

2/1/2021 2:35 PM Andy
- Enhanced to support ARMS Internal API in Sync Server.	

3/12/2021 2:09 PM Rayleen
- Enhanced to add new api "opencart_add_member"

3/15/2021 4:05 PM Rayleen
- remove check_settings() from constrcut
- rename validate_date() function to validate_opencart_eform_data
*/

class API_ARMS_INTERNAL_MEMBER {
	var $main_api = false;
	
	var $err_list = array(
		"member_mobile_not_registered" => "This member account is not register for mobile.",
		"member_not_verified" => "This member account is not verify, please verify it first.",
		"member_blocked" => "This member account already blocked.",
		"member_not_found" => "This member account not found.",
		"member_expired" => "This member account has expired.",
		"member_type_not_set" => "Default member type is not configured."
	);
	
	var $sync_server_compatible_api = array('check_member'); 
	function __construct($main_api){
		$this->main_api = $main_api;

		$this->get_post_json_data();
	}

	private function get_post_json_data(){
		$this->rcv_data = json_decode(file_get_contents('php://input'), true);
	}

	function is_api_support_sync_server($api_name){
		if(in_array($api_name, $this->sync_server_compatible_api)){
			return true;
		}
		return false;
	}

	function get_my_member_info(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		if(!$this->main_api->member){
			$this->main_api->error_die($this->main_api->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['member_data'] = array();
		$ret['member_data']['nric'] = trim($this->main_api->member['nric']);
		$ret['member_data']['card_no'] = trim($this->main_api->member['card_no']);
		$ret['member_data']['name'] = trim($this->main_api->member['name']);
		$ret['member_data']['gender'] = trim($this->main_api->member['gender']);
		$dob = substr($this->main_api->member['dob'], 0, 4).'-'.substr($this->main_api->member['dob'], 4, 2).'-'.substr($this->main_api->member['dob'], 6, 2);
		$dob = $appCore->isValidDateFormat($dob) ? $dob : '';
		$ret['member_data']['dob'] = $dob;
		$ret['member_data']['postcode'] = trim($this->main_api->member['postcode']);
		$ret['member_data']['address'] = trim($this->main_api->member['address']);
		$ret['member_data']['city'] = trim($this->main_api->member['city']);
		$ret['member_data']['state'] = trim($this->main_api->member['state']);
		$ret['member_data']['phone_3'] = trim($this->main_api->member['phone_3']);
		$ret['member_data']['email'] = trim($this->main_api->member['email']);
		$ret['member_data']['points'] = mi($this->main_api->member['points']);
		$ret['member_data']['points_update'] = trim($this->main_api->member['points_update']);
		$ret['member_data']['issue_date'] = date("Y-m-d", strtotime($this->main_api->member['issue_date']));
		$ret['member_data']['next_expiry_date'] = date("Y-m-d", strtotime($this->main_api->member['next_expiry_date']));
		$ret['member_data']['member_type'] = trim($this->main_api->member['member_type']);
		$ret['member_data']['member_type_desc'] = trim($config['membership_type'][$ret['member_data']['member_type']]);
		$ret['member_data']['profile_image_url'] = trim($this->main_api->member['profile_image_url']);
		$ret['member_data']['mobile_registered_time'] = trim($this->main_api->member['mobile_registered_time']);
		
		if($this->main_api->member['referral_code']){
			$ret['member_data']['referral_code'] = $this->main_api->member['referral_code'];
		}else{
			// Begin
			$con->sql_begin_transaction();
		
			$ret['member_data']['referral_code'] = $appCore->memberManager->generateMemberReferralCode(trim($this->main_api->member['nric']));
			
			// Commit
			$con->sql_commit();
		}
		$ret['member_data']['refer_by_referral_code'] = trim($this->main_api->member['refer_by_referral_code']);
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function get_my_member_coupon_list(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		if(!$this->main_api->member){
			$this->main_api->error_die($this->main_api->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		// Whether to show limited coupon
		$show_limited_coupon = $this->main_api->member_app_version_compare($this->main_api->APP_VERSION, "2.1.0") >= 0 ? true : false;
		//print "show_limited_coupon = $show_limited_coupon";exit;
		
		$ret = array();
		$ret['result'] = 1;
		$ret['coupon_list'] = array();
		
		$nric = trim($this->main_api->member['nric']);
		
		// Get Card No List
		$card_no_list = $appCore->memberManager->getMemberCardNoList($nric);
		
		if($card_no_list){
			$filter = array();
			$filter[] = "cp.active=1 and ".ms(date("Y-m-d"))."<=cp.valid_to";
			$str_filter = "where ".join(' and ', $filter);
			
			$sql = "select cp.*, ci.coupon_code, ci.full_coupon_code, ci.print_value, dept.description as dept_desc, brand.description as brand_desc, v.description as vendor_desc, ci.remark as ci_remark
				from coupon_items ci
				join coupon cp on cp.branch_id=ci.branch_id and cp.id=ci.coupon_id
				left join category dept on dept.id=cp.dept_id
				left join brand on brand.id=cp.brand_id
				left join vendor v on v.id=cp.vendor_id
				$str_filter
				order by cp.valid_to";
			//print $sql;exit;
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				$allowed = false;
				$need_check_coupon_items_member = false;
				$need_check_referral = false;
				$total_used_count = 0;
				$referrer_max_use = 0;
				$referee_max_use = 0;
				$referrer_count = 0;
				$referral_program_max_use = 0;
				
				$r['si_list'] = unserialize($r['si_list']);
				$r['member_limit_profile_info'] = unserialize($r['member_limit_profile_info']);
				
				if($r['member_limit_count']>0){	// Got limit usage
					$need_check_coupon_items_member = true;
				}
				
				// Check allow for this member or not
				switch($r['member_limit_type']){
					case 'selected_member':
						$need_check_coupon_items_member = true;
						break;
					case 'member_type':	// allow for selected member type
						$r['member_limit_info'] = unserialize($r['member_limit_info']);
						if(isset($r['member_limit_info']['member_type'][$this->main_api->member['member_type']])){
							$allowed = true;
						}
						break;
					case 'referral_program':
						$need_check_coupon_items_member = true;
						$need_check_referral = true;
						break;
					case 'all_member':	// Allowed for all member
					default:	// Allow for anyone (member and non-member)
						$allowed = true;
						break;
				}
				
				if($need_check_coupon_items_member){
					// Get coupon items member data
					$result = $appCore->couponManager->getCouponItemsMember($r['coupon_code'], $card_no_list, array('active'=>1));
					//print_r($result);exit;
					if($result['ok']){
						$total_used_count = mi($result['total_used_count']);
						if($r['member_limit_count']>0 && $total_used_count >= $r['member_limit_count']){	// Reach maximum used
							$allowed = false;
						}else{
							if($r['member_limit_type'] == 'selected_member' || $r['member_limit_type'] == 'referral_program'){	// allow for selected member
								if($result['data']){
									foreach($result['data'] as $coupon_items_member){	// This member allow for this coupon
										if($coupon_items_member['active']){
											$allowed = true;
											break;
										}
									}
								}
							}
						}
						
						// Need further check on referral limit
						if($need_check_referral && $allowed){
							$referrer_count = mi($result['referrer_count']);
							$referrer_max_use = mi($result['referrer_max_use']);
							$referee_max_use = mi($result['referee_max_use']);
							$referral_program_max_use = mi($referrer_max_use + $referee_max_use);
							
							// coupon got maximum usage
							if($r['member_limit_count'] > 0 && $referral_program_max_use > $r['member_limit_count']){
								$referral_program_max_use = $r['member_limit_count'];
							}
							
							if($total_used_count >= $referral_program_max_use){
								$allowed = false;
							}
						}
					}else{
						// Got error on retrieve member data
						$allowed = false;
					}
				}
				
				
				
				// Not allow to show limited coupon
				if(!$show_limited_coupon){
					if($r['member_limit_type'] && $allowed){	// this coupon is for member and all above checking passed
						// Further checking on member limit
						
						// Got limit for register day from / to
						if($r['member_limit_mobile_day_start']>0 && $r['member_limit_mobile_day_end']>0){
							//$day_count = (strtotime(date("Y-m-d")) - strtotime(date("Y-m-d", strtotime($this->member['mobile_registered_time']))))/60/60/24;
							$day_count = ceil(((strtotime("now") - strtotime($this->member['mobile_registered_time'])))/60/60/24);
							if(!($day_count >= $r['member_limit_mobile_day_start'] && $day_count <= $r['member_limit_mobile_day_end'])){
								$allowed = false;	// day not meet the requirement
							}
						}
						
						// Required some specified member profile field
						if($allowed && $r['member_limit_profile_info']){
							foreach($r['member_limit_profile_info'] as $member_profile_field => $needed){
								if($needed){
									if(!$this->main_api->member[$member_profile_field]){
										$allowed = false;	// this field din't fill
									}
								}
							}
						}
					}
				}
				
				
				if($allowed){
					$tmp = array();
					$tmp['full_coupon_code'] = $r['full_coupon_code'];
					$tmp['value'] = $r['print_value'];
					$tmp['discount_by'] = $r['discount_by'];
					
					if($need_check_referral){
						$tmp['member_limit_count'] = $referral_program_max_use;
					}else{
						$tmp['member_limit_count'] = $r['member_limit_count'];
					}
					$tmp['total_used_count'] = $total_used_count;
					$tmp['valid_from'] = $r['valid_from'];
					$tmp['valid_to'] = $r['valid_to'];
					$tmp['time_from'] = $r['time_from'];
					$tmp['time_to'] = $r['time_to'];
					$tmp['min_qty'] = $r['min_qty'];
					$tmp['min_amt'] = $r['min_amt'];
					$tmp['min_receipt_amt'] = $r['min_receipt_amt'];
					$tmp['remark'] = $r['ci_remark'];
					
					$tmp['limit_sid_list'] = array();
					if($r['si_list']){
						$tmp['limit_sid_list'] = $r['si_list'];
					}
					$tmp['dept_id'] = $r['dept_id'];
					$tmp['dept_desc'] = trim($r['dept_desc']);
					$tmp['brand_id'] = $r['brand_id'];
					$tmp['brand_desc'] = trim($r['brand_desc']);
					$tmp['vendor_id'] = $r['vendor_id'];
					$tmp['vendor_desc'] = trim($r['vendor_desc']);
					$tmp['member_limit_mobile_day_start'] = mi($r['member_limit_mobile_day_start']);
					$tmp['member_limit_mobile_day_end'] = mi($r['member_limit_mobile_day_end']);
					$tmp['member_limit_profile_info'] = array();
					if($r['member_limit_profile_info']){
						$tmp['member_limit_profile_info'] = $r['member_limit_profile_info'];
					}
					
					if($need_check_referral){
						// Referral Program
						$tmp['referrer_count'] = $referrer_count;
						$tmp['referrer_max_use'] = $referrer_max_use;
						$tmp['referee_max_use'] = $referee_max_use;
					}
					$ret['coupon_list'][] = $tmp;
				}
			}
			$con->sql_freeresult($q1);
		}
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function enter_member_referral_code(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
				
		// Must login as member
		if(!$this->main_api->member){
			$this->main_api->error_die($this->main_api->err_list["member_data_not_found"], "member_data_not_found");
		}
		
		// Referral Code
		$referral_code = trim($_REQUEST['referral_code']);
		if(!$referral_code)	$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], "referral_code"), "invalid_data");
		
		// Begin
		$con->sql_begin_transaction();
			
		// Update
		$result = $appCore->memberManager->setReferByReferralCode($this->main_api->member['nric'], $referral_code);
		if(!$result['ok']){
			// Update Failed
			if($result['error_code'] && $result['error']){
				$this->main_api->error_die($result['error'], $result['error_code']);
			}else{
				$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
			}
		}
		
		// Commit
		$con->sql_commit();
			
		// Construct Respond Data
		$ret = array();
		$ret['result'] = 1;
				
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function member_login(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();
		
		$login_id = trim($_REQUEST['login_id']);
		$p = trim($_REQUEST['p']);
		
		// No Login ID or Password
		if(!$login_id || !$p){
			$this->main_api->error_die($this->main_api->err_list["member_login_failed"], "member_login_failed");
		}
		
		$filter = array();
		$filter[] = "(m.nric=".ms($login_id)." or m.card_no=".ms($login_id)." or (m.email<>'' and m.email=".ms($login_id).")) and m.memb_password=".ms($p);
		//$filter[] = "m.mobile_registered=1 and m.verified_date>0";
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select m.*
			from membership m
			$str_filter";
		//die($sql);
		
		// Check Database
		$con->sql_query($sql);
		$member = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Member Not Found
		if(!$member){
			$this->main_api->error_die($this->main_api->err_list["member_login_failed"], "member_login_failed");
		}
		
		// Not Registered for mobile
		if(!$member['mobile_registered']){
			$this->main_api->error_die($this->err_list["member_mobile_not_registered"], "member_mobile_not_registered");
		}
		
		// Not Verified
		if(!$member['verified_by']){
			$this->main_api->error_die($this->err_list["member_not_verified"], "member_not_verified");
		}
		
		// Member Blocked
		if($member['blocked_by']){
			$this->main_api->error_die($this->err_list["member_blocked"], "member_blocked");
		}
		
		$session_token = $appCore->newGUID();
		
		$upd = array();
		$upd['nric'] = $member['nric'];
		$upd['device_id'] = $this->main_api->DEVICE_ID;
		$upd['session_token'] = $session_token;
		$upd['ip'] = $this->main_api->device_ip;
		$upd['app_type'] = $this->main_api->APP_TYPE;
		$upd['app_version'] = $this->main_api->APP_VERSION;
		$upd['last_access'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("replace into member_app_session ".mysql_insert_by_field($upd));
		
		$ret = array();
		$ret['result'] = 1;
		$ret['nric'] = $member['nric'];
		$ret['session_token'] = $session_token;
		
		// Return Data
		$this->main_api->respond_data($ret);
	}
	
	function check_member(){
		global $con, $config, $appCore;
		
		// Validate Device
		$this->main_api->is_valid_device();		
		
		$card_no = $_REQUEST['card_no'];
		$member_info = array();
		
		$filter = array();
		$filter[] = "m.card_no=".ms($card_no);
		
		$str_filter = "where ".join(' and ', $filter);
		$sql = "select m.*
			from membership m
			$str_filter limit 1";
		
		// Check Database
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$current_date = strtotime(date("Y-m-d"));
			$issue_date_str = strtotime($r['issue_date']);
			$next_expiry_date_str = strtotime($r['next_expiry_date']);
			
			if (($current_date <= $issue_date_str) || ($current_date >= $next_expiry_date_str)){
				$this->main_api->error_die($this->err_list["member_expired"], "member_expired");
			}
			$member_info['membership_guid'] = $r['membership_guid'];
			$member_info['nric'] = $r['nric'];
			$member_info['card_no'] = $r['card_no'];
			$member_info['name'] = $r['name'];
			$member_info['gender'] = $r['gender'];
			$dob = substr($r['dob'], 0, 4).'-'.substr($r['dob'], 4, 2).'-'.substr($r['dob'], 6, 2);
			$dob = $appCore->isValidDateFormat($dob) ? $dob : '';
			$member_info['dob'] = $dob;
			$member_info['verified_by'] = $r['verified_by'];
			$member_info['blocked_by'] = $r['blocked_by'];
			$member_info['postcode'] = trim($r['postcode']);
			$member_info['address'] = trim($r['address']);
			$member_info['city'] = trim($r['city']);
			$member_info['state'] = trim($r['state']);
			$member_info['phone_3'] = trim($r['phone_3']);
			$member_info['email'] = trim($r['email']);
			$member_info['points'] = mi($r['points']);
			$member_info['points_update'] = trim($r['points_update']);
			$member_info['issue_date'] = date("Y-m-d", strtotime($r['issue_date']));
			$member_info['next_expiry_date'] = date("Y-m-d", strtotime($r['next_expiry_date']));
			$member_info['member_type'] = $r['member_type'];
			$member_info['member_type_desc'] = trim($config['membership_type'][$r['member_type']]);
			$member_info['profile_image_url'] = $r['profile_image_url'];
		}
		$con->sql_freeresult();
		
		// Member Not Found
		if(!$member_info){
			$this->main_api->error_die($this->err_list["member_not_found"], "member_not_found");
		}
		
		// Not Verified
		if(!$member_info['verified_by']){
			$this->main_api->error_die($this->err_list["member_not_verified"], "member_not_verified");
		}
		
		// Member Blocked
		if($member_info['blocked_by']){
			$this->main_api->error_die($this->err_list["member_blocked"], "member_blocked");
		}
		
		$ret = array();
		$ret['result'] = 1;
		$ret['member_info'] = $member_info;
		
		// Return Data
		$this->main_api->respond_data($ret);
	}

	private function check_eform_settings(){
		global $config;
		
		// Member Type
		if($config['membership_eform_settings']['default_member_type']){
			$this->default_member_type = trim($config['membership_eform_settings']['default_member_type']);
			if(!$config['membership_type'][$this->default_member_type]){
				$this->main_api->error_die($this->err_list["member_type_not_set"], "member_type_not_set");
			}
		}
		
		// Default 1 Year
		$this->card_expiry_duration_year = trim($config['membership_eform_settings']['card_expiry_duration_year']);
		if($this->card_expiry_duration_year <= 0 && $this->card_expiry_duration_year != 'life')	$this->card_expiry_duration_year = 1;
		
	}

	function opencart_add_member(){
		global $con, $appCore, $config;
		// Validate Device
		$this->main_api->is_valid_device();	

		// get receive data
		$data = $this->rcv_data;

		// check eform settings
		$this->check_eform_settings();

		// validate data
		$this->validate_opencart_eform_data($data);

		// prepare member data
		$member = $this->get_opencart_member_data($data);

		// insert data to membership table
		$con->sql_begin_transaction();
			$con->sql_query("insert into membership ".mysql_insert_by_field($member));
			log_br(1, 'MEMBERSHIP', 0, 'Add Membership ' . $member['nric']);
		$con->sql_commit();

		// send email to member
		$this->member_send_email($member);

		// return result
		$ret = array();
		$ret['result'] = 1;
		$this->main_api->respond_data($ret);
		
	}

	private function get_opencart_member_data($data)
	{
		global $con, $appCore, $config;

		$member = array();
		$member['gender'] = '';
		$member['dob'] = '';
		if($config['arms_marketplace_settings']['branch_code']){
			$member['apply_branch_id'] = get_branch_id($config['arms_marketplace_settings']['branch_code']);
		}else{
			$member['apply_branch_id'] = 1; //HQ
		}
		$member['name'] = $data['firstname'].' '.$data['lastname'];
		$member['postcode'] = $data['postcode'];
		$member['address'] = $data['address'];
		$member['city'] =  $data['city'];
		$member['state'] = $data['state'];
		$member['phone_3'] = $data['telephone'];
		$member['email'] = $data['email'];
		$member['member_type'] = $this->default_member_type;
		$member['membership_guid'] = $appCore->newGUID();

		// Get New Card No
		$new_card = $appCore->memberManager->getEFormNewCardNo();
		if(!$new_card['ok']){
			$this->main_api->error_die($this->main_api->err_list["unknown_error"], "unknown_error");
		}
		$member['card_no'] = $new_card['new_card_no'];
		$member['nric'] = $new_card['new_card_no'];
		$member['issue_date'] = date("Y-m-d");

		if($this->card_expiry_duration_year)
		{
			if($this-card_expiry_duration_year == 'life'){
				$member['next_expiry_date'] = '2037-12-31';
			}else{
				$member['next_expiry_date'] =  date("Y-m-d", strtotime("+".$this->card_expiry_duration_year." year"));
			}
		}else{
			$member['next_expiry_date'] = date("Y-m-d", strtotime("+1 year"));
		}
		$member['eform_registered'] = 1;
		$member['eform_registered_time'] = 'CURRENT_TIMESTAMP';
		$member['eform_registered_verify_code'] = $appCore->generateRandomCode(64);

		return $member;
	}

	private function validate_opencart_eform_data($data){
		global $con;

		if(!$data['firstname'] || !$data['lastname'])
		{
			$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'name'), "invalid_data");
		}

		if(!$data['telephone'])
		{
			$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'phone'), "invalid_data");
		}

		if(!$data['email'])
		{
			$this->main_api->error_die(sprintf($this->main_api->err_list["invalid_data"], 'email'), "invalid_data");
		}

		// duplicate email
		$con->sql_query("select email from membership where email=".ms($data['email']));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp){
			$this->main_api->error_die($this->main_api->err_list["member_email_alrdy_used"], "member_email_alrdy_used");
		}

		return true;
	}

	private function member_send_email($data)
	{
		global $appCore;
		// Generate QR Code
		$qr_img_name = tempnam("/tmp", "mqr");
		$appCore->generateQRCodeImage($qr_img_name, $data['card_no']);

		// Send Email
		include_once("include/class.phpmailer.php");
	
		$mailer = new PHPMailer(true);
		//$mailer->From = "noreply@arms.com.my";
		$mailer->FromName = "ARMS Notification";
		$mailer->Subject = "New Member Registered";
		$mailer->IsHTML(true);
		$mailer->IsMail();
	
		$mailer->AddAddress($data['email']);
		
		// Add QRCode as Attachment
		$mailer->AddAttachment($qr_img_name, $data['card_no'].".png");
		
		$email_body = "<h2><u>New Member Registered</u></h2>\r\n";
		$email_body .= "Congratulation, you have successfully registered as our membership, below is your membership details.<br />\r\n";
		//$email_body .= "<b>NRIC / Passport</b>: ".$form['nric']."<br />\r\n";
		$email_body .= "<b>Card No</b>: ".$data['card_no']."<br />\r\n";
		$email_body .= "<b>Full Name</b>: ".$data['name']."<br /><br />\r\n";
		//$email_body .= "<b>Password</b>: ".$password."<br />\r\n";
		
	
		$email_body .= "Please click on below link to verify your membership.<br />\r\n";
		$url = $this->server_host()."/membership.eform.php?a=verify&code=".$data['eform_registered_verify_code'];
		$email_body .= "<a href=\"".$url."\" target='_blank'>Verify Membership</a><br />\r\n";
		$email_body .= "(Copy the below link if the above link is not working)<br />\r\n";
		$email_body .= $url."<br /><br />\r\n";
		$email_body .= "Please take note your membership is not active until you verify it.<br /><br />\r\n";
	
		$mailer->Body = $email_body;
		
		// send the mail
		//print_r($mailer);
		if($send_success = phpmailer_send($mailer, $mailer_info)){
			//print ": OK";
		}else{
			//print ": Failed";
			//print "> ".$mailer_info['err'];
		}

		$mailer->ClearAddresses();
	}

	private function server_host()
	{	
		$url = '';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
			$url .= "https://";   
		}else{
			$url .= "http://";   
		}
		$url .= $_SERVER['HTTP_HOST'];
		list($_SERVER['HTTP_HOST'], $port) = explode(":", $_SERVER['HTTP_HOST']);
		if(!$port && $_SERVER['SERVER_PORT']){
			$url .= ":".$_SERVER['SERVER_PORT'];
		}
		return $url;
	}
}
?>