<?php
/*
8/30/2019 10:42 AM Andy
- Added couponManager.

10/16/2019 11:25 AM Andy
- Enhanced to pass branch_id when send push notification to member.

2/13/2020 5:35 PM Andy
- Added couponManager function "checkReferralProgramCouponByReferralHistory".
- Enhanced couponManager function "getCouponItems", "addCouponItemsToMember" and "getCouponItemsMember" to have Referral Program Info.
*/

class couponManager{
	// public var
	
	// private var
	
	
	function __construct(){
		global $smarty, $con, $appCore;

		
	}
	
	public function getCouponItems($coupon_code){
		global $con, $LANG;
		
		$coupon_code = trim($coupon_code);
		if(!$coupon_code){
			return array('error' => $LANG['COUP_MISS_CODE']);
		}
		
		// Get Coupon
		$con->sql_query("select ci.*, c.member_limit_type, c.discount_by, c.member_limit_count, c.referrer_coupon_get, c.referrer_count_need, c.referee_coupon_get
			from coupon_items ci
			join coupon c on c.branch_id=ci.branch_id and c.id=ci.coupon_id
			where ci.coupon_code=".ms($coupon_code));
		$coupon_items = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$coupon_items){
			return array('error' => $LANG['COUP_NO_DATA']);
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['data'] = $coupon_items;
		
		return $ret;
	}
	
	public function addCouponItemsToMember($coupon_code, $card_no, $params = array()){
		global $con, $LANG, $config, $appCore, $sessioninfo;
		
		$coupon_code = trim($coupon_code);
		if(!$coupon_code){
			return array('error' => $LANG['COUP_MISS_CODE']);
		}
		
		$card_no = trim($card_no);
		if(!$card_no){
			return array('error' => "Invalid Member Card No");
		}
		
		// Get Coupon Items
		$result = $this->getCouponItems($coupon_code);
		if(!$result['ok']){
			return array('error' => $result['error']);
		}
		$coupon_items = $result['data'];
		
		if(isset($params['send_push_notification']))	$send_push_notification = mi($params['send_push_notification']);
		if(isset($params['add_referee_max_use']))	$add_referee_max_use = mi($params['add_referee_max_use']);
		if(isset($params['add_referrer_coupon_get']))	$add_referrer_coupon_get = mi($params['add_referrer_coupon_get']);
		if(isset($params['referrer_count']))	$referrer_count = mi($params['referrer_count']);
		
		// Check Data Existed
		$con->sql_query("select * from coupon_items_member where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no)." for update");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			// Not Referral Program - Show Duplicate
			if($add_referee_max_use<=0 && $add_referrer_coupon_get<=0 && $referrer_count<=0){
				return array('error'=>'Member Card No '.$card_no.' already exists', 'duplicated'=>1);
			}
			
			// Update
			$upd = array();
			if($add_referee_max_use > 0){
				$upd['referee_max_use'] = mi($tmp['referee_max_use']+$add_referee_max_use);
			}
			if($add_referrer_coupon_get > 0){
				$upd['referrer_max_use'] = mi($tmp['referrer_max_use']+$add_referrer_coupon_get);
			}
			if(isset($referrer_count)){
				$upd['referrer_count'] = $referrer_count;
			}
			
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("update coupon_items_member set ".mysql_update_by_field($upd)." where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no));
		}else{
			// Insert new row
			$upd = array();
			$upd['coupon_code'] = $coupon_code;
			$upd['branch_id'] = mi($coupon_items['branch_id']);
			$upd['coupon_id'] = mi($coupon_items['coupon_id']);
			$upd['card_no'] = $card_no;
			$upd['used_count'] = 0;
			if($add_referee_max_use > 0){
				$upd['referee_max_use'] = $add_referee_max_use;
			}
			if($add_referrer_coupon_get > 0){
				$upd['referrer_max_use'] = $add_referrer_coupon_get;
			}
			if(isset($referrer_count)){
				$upd['referrer_count'] = $referrer_count;
			}
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			$sql = "insert into coupon_items_member ".mysql_insert_by_field($upd);
			//print $sql;exit;
			$con->sql_query($sql);
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		log_br($user_id, 'COUPON',0, "Add Coupon Member, Card No: ".$card_no.", Coupon Code: ".$coupon_code);
		
		if($send_push_notification && $config['membership_mobile_settings'] && $config['enable_push_notification']){			
			$title = "Coupon Received";
			$message = "You have received a Coupon.";
			$appCore->memberManager->sendPushNotificationToMember($card_no, $title, $message, array('branch_id'=>$coupon_items['branch_id']));			
		}
		
		return array('ok'=>1);
	}
	
	public function getCouponItemsMember($coupon_code, $card_no_list, $params = array()){
		global $con, $LANG, $config, $appCore, $sessioninfo;
		
		$coupon_code = trim($coupon_code);
		if(!$coupon_code){
			return array('error' => $LANG['COUP_MISS_CODE']);
		}
		
		if(!$card_no_list || !is_array($card_no_list)){
			return array('error' => "Invalid Card No List");
		}
		
		$filter = array();
		$filter[] = "coupon_code=".ms($coupon_code);
		$filter[] = "card_no in (".join(',', array_map('ms', $card_no_list)).")";
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		$str_filter = "where ".join(' and ', $filter);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['total_used_count'] = 0;
		$ret['data'] = array();
		
		$con->sql_query("select * from coupon_items_member 
			$str_filter");
		while($r = $con->sql_fetchassoc()){
			$ret['data'][] = $r;
			$ret['total_used_count'] += $r['used_count'];
			
			// Referral Program
			if($r['referrer_count']>0){
				$ret['referrer_count'] += $r['referrer_count'];
			}
			if($r['referrer_max_use']>0){
				$ret['referrer_max_use'] += $r['referrer_max_use'];
			}
			if($r['referee_max_use']>0){
				$ret['referee_max_use'] += $r['referee_max_use'];
			}
		}
		$con->sql_freeresult();
		
		return $ret;
	}
	
	public function checkReferralProgramCouponByReferralHistory($referral_history_guid){
		global $con, $appCore, $LANG, $sessioninfo;
		
		$referral_history_guid = trim($referral_history_guid);
		if(!$referral_history_guid)	return false;
		
		// Get Referral History
		$con->sql_query("select * from memberships_referral_history where guid=".ms($referral_history_guid));
		$referral_his = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Referral History Not Found
		if(!$referral_his)	return false;
		
		// Get Referee Member
		$referee = $appCore->memberManager->getMemberByGUID($referral_his['referee_membership_guid']);
		if(!$referee)	return false;
		
		// Get Referrer Member
		$referrer = $appCore->memberManager->getMemberByGUID($referral_his['referrer_membership_guid']);
		if(!$referrer)	return false;
		
		// Check the date when key in referral_code
		$referral_date = date("Y-m-d", strtotime($referral_his['added']));
		if(date("Y", strtotime($referral_date))<2000)	return false;
		
		// Referee Card No List
		$referee_card_no_list = $appCore->memberManager->getMemberCardNoList($referee['nric']);
		
		// Referrer Card No List
		$referrer_card_no_list = $appCore->memberManager->getMemberCardNoList($referrer['nric']);
		
		// Referee Registration Day Count when key in referral_code
		$referee_day_count = ceil(((strtotime($referral_date) - strtotime($referee['mobile_registered_time'])))/60/60/24);
		
		// Check Available Coupon within this period
		$filter = array();
		$filter[] = "cp.active=1 and cp.member_limit_type='referral_program'";
		$filter[] = ms($referral_date)." between cp.valid_from and cp.valid_to";
		$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select cp.*
			from coupon cp
			$str_filter
			order by cp.code");
		while($cp = $con->sql_fetchassoc($q1)){
			// Must key in X day after registration
			if($cp['referee_day_limit']>0){
				if($referee_day_count > $cp['referee_day_limit']){
					// Not eligible
					continue;
				}
			}
				
			// Get Coupon Items
			$q2 = $con->sql_query("select * from coupon_items where branch_id=".mi($cp['branch_id'])." and coupon_id=".mi($cp['id']));
			while($cpi = $con->sql_fetchassoc($q2)){
				// Check and Add Link to Coupon and History
				$con->sql_query("select * from coupon_items_member_referral_history where coupon_code=".ms($cpi['coupon_code'])." and memberships_referral_history_guid=".ms($referral_history_guid));
				$linked_existed = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$linked_existed){
					$upd = array();
					$upd['guid'] = $appCore->newGUID();
					$upd['memberships_referral_history_guid'] = $referral_history_guid;
					$upd['coupon_code'] = $cpi['coupon_code'];
					$upd['added'] = 'CURRENT_TIMESTAMP';
					$con->sql_query("insert into coupon_items_member_referral_history ".mysql_insert_by_field($upd));
				}
				
				///////// Referee Checking ///////////
				if($cp['referee_coupon_get']>0){
					$referee_coupon_get = mi($cp['referee_coupon_get']);
					
					// Referee Entitled to Get Coupon
					if($referee_coupon_get > 0){
						// Check if already have this coupon
						$cpim_data = $this->getCouponItemsMember($cpi['coupon_code'], $referee_card_no_list);
						if($cpim_data['referee_max_use'] < $referee_coupon_get){
							if($cpim_data['referee_max_use'] > 0){
								$referee_coupon_get -= $cpim_data['referee_max_use'];
							}
							// Need Send Coupon
							if($referee_coupon_get > 0){
								// Add Coupon for Referee
								$tmp_params = array();
								$tmp_params['add_referee_max_use'] = $referee_coupon_get;
								$tmp_params['send_push_notification'] = 1;
								$result = $this->addCouponItemsToMember($cpi['coupon_code'], $referee['card_no'], $tmp_params);
							}
						}
					}
				}
				
				///////// Referrer Checking ///////////
				if($cp['referrer_coupon_get']>0){
					// Check Total Referral Count
					$q_cimrh = $con->sql_query("select count(*) as c
						from coupon_items_member_referral_history  cimrh
						join memberships_referral_history mrh on mrh.guid=cimrh.memberships_referral_history_guid
						where cimrh.coupon_code=".ms($cpi['coupon_code'])." and mrh.referrer_membership_guid=".ms($referrer['membership_guid']));
					$tmp = $con->sql_fetchassoc($q_cimrh);
					$con->sql_freeresult($q_cimrh);
					$referrer_count = mi($tmp['c']);
					
					/*if($sessioninfo['id'] == 306){
						print_r($tmp);exit;
					}*/
					
					// Calculate How many coupon Get
					$referrer_coupon_get = 0;
					$referrer_count_need = mi($cp['referrer_count_need']);
					if($referrer_count_need > 0){
						$referrer_coupon_get = mi(floor($referrer_count / $referrer_count_need))*mi($cp['referrer_coupon_get']);
					}
					
					// Referrer Entitled to Get Coupon
					if($cp['referrer_coupon_get'] > 0){
						// Check if already have this coupon
						$cpim_data = $this->getCouponItemsMember($cpi['coupon_code'], $referrer_card_no_list);
						if($cpim_data['referrer_count'] != $referrer_count || $cpim_data['referrer_max_use'] < $referrer_max_use){
							if($cpim_data['referrer_max_use'] > 0){
								$referrer_coupon_get -= $cpim_data['referrer_max_use'];
							}
							
							// Got new coupon or referrer changed
							if($referrer_coupon_get > 0 || $cpim_data['referrer_count'] != $referrer_count){
								$tmp_params = array();
								if($referrer_coupon_get > 0){
									// Add Coupon for Referee
									$tmp_params['add_referrer_coupon_get'] = $referrer_coupon_get;
									// Need Send Coupon
									$tmp_params['send_push_notification'] = 1;
								}
								$tmp_params['referrer_count'] = $referrer_count;
								$result = $this->addCouponItemsToMember($cpi['coupon_code'], $referrer['card_no'], $tmp_params);
							}
						}
					}
				}
			}
			$con->sql_freeresult($q2);			
		}
		$con->sql_freeresult($q1);
		
		return true;
	}
}
?>
