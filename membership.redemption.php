<?php
/*
3/2/2010 3:24:52 PM Andy
- Add receipt date period checking

3/12/2010 11:40:10 AM Andy
- Delete button change and multiple delete function.
- Toggle active button change and multiple active/deactive function.
- Fix item cannot approve due to have amount but no end date bugs.
- Add use current date feature for receipt control.

8/11/2010 12:03:11 PM Justin
- Added new feature to redeem item add by Search SKU engine. 
- Added new config['membership_redemption_allow_use_search'] to enable/disable this new feature.
  -> If disabled, system will show as previous version.
  -> Else if enabled, the list of items will cleared and required user to search by SKU item to add item.
- Created a checking feature to check if found HQ is offline, shows error message indicate the Redemption is out of service and terminate the program.
- Changed most of the sql query to use hqcon.
- Fixed the list of redemption items disappeared if user redeemed one or more of the items keyed in with invalid information (this happened when using config).

8/18/2010 11:28:42 AM Justin
- Added the display of scanned IC image link configuration.
- Added Approval Flow check and updates.
- Added PM send for Notifier.

8/27/2010 3:28:30 PM Justin
- Removed function that set Cash to zero.
- Changed the message printing while HQ offline.

9/29/2010 3:40:13 PM Justin
- Added new process to show Scanned IC image on the screen during success login the membership Card No and NRIC
- Allowed user to proceed to next page or remain on the current page.
- Removed the prompt out window for showing scanned IC image.
- Added total cash paid by member and update to database.
- Fixed the bug where cannot do redemption.
- Updated the both format A4 and receipt report printing to include the following cash paid and change.
- Changed some of the wrrong error message labels.

10/28/2010 12:05:47 PM Justin
- Added the info to capture user id to insert into database.
- Modified the insertion for membership points to capture membership redemption id and place under remark field.
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.
- Fixed the bugs when user make redemption and item cannot be added to the list.

11/1/2010 2:57:15 PM Justin
- Solved the bugs where approval cannot view the Redemption Verification list.

11/8/2010 3:45:22 PM Justin
- Disabled the checking of Expired Member Card instead of block it to make redemption.

11/9/2010 4:14:33 PM Justin
- Added the updates for member when a redemption that is being redeemed.

12/16/2010 11:28:35 AM Justin
- Added the checking for add sku items to check against the sku item code.

12/27/2010 10:46:44 AM Justin
- Added barcode scan feature.

1/26/2011 11:03:11 AM Justin
- Added the showing SKU item existed error message whenever user scan item that already existed in the list. 

3/14/2011 6:20:48 PM Justin
- Fixed the bugs where cannot load redemption items. 

3/25/2011 2:03:37 PM Andy
- Add scan card no will prepand prefix card no to check. (need config)

4/8/2011 5:03:18 PM Justin
- Changed all the connection back to local connection instead of using hq connection to reduce the time consumes.
- Only connect to HQ while retrieving membership latest points, insert points history and points update.

7/6/2011 12:06:55 PM Andy
- Change split() to use explode()

7/11/2011 4:07:13 PM Andy
- Replace htmlentities() to htmlspecialchars()

8/16/2011 12:23:21 PM Justin
- Added missing "status" while inserting adjustment.

10/12/2011 4:59:47 PM Alex
- add doc_allow_decimal for quantity decimal checking

6/22/2012 5:48:23 PM Justin
- Added new validation to show error message as when accessed from sub card and found principal card have been blocked.
- Added to use points from principal card while found config.

7/19/2012 9:37:23 AM Justin
- Bug fixed system unable to get latest points from membership.

7/26/2012 2:20 PM Justin
- Bug fixed on triggering wrong points while redeem.

1/11/2013 10:40 AM Justin
- Enhanced to accept voucher redemption and store voucher code.

2/5/2013 9:48 AM Justin
- Enhanced to check and show errors while in same redemption that contains repeated voucher.

2/6/2013 12:12 PM Justin
- Bug fixed on system does not sum up points form sub card.

4/24/2013 6:14 PM Justin
- Enhanced to have more validations for vouchers similar as Voucher Activation.
- Enhanced to show error msg while found user claims voucher without privilege.

4/25/2013 5:34 PM Justin
- Bug fixed on voucher "valid date to" comparison.

4/29/2013 5:31 PM Justin
- Added new config to check voucher activation status.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/31/2013 5:12 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.
- Fix Membership Redemption Verify show wrong Approval sequence in Waiting for Approval list.

8/19/2013 4:30 PM Justin
- Bug fixed on system wrong calculating total points for principal and sub cards.

9/17/2014 2:36 PM Fithri
- when do redemption, check if voucher code has been used

2/17/2015 1:50 PM Andy
- Enhance to get gst data when made redemption.

7/30/2015 5:13 PM Justin
- Bug fixed on voucher verification bugs.

1/19/2017 4:44 PM Andy
- Fixed redeem voucher got bug at branch.

3/16/2017 5:33 PM Andy
- Fixed voucher not found if redeem cross branch.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/2/2018 5:13 PM Justin
- Enhanced to check against expired member base on config and prompt error.

1/3/2020 4:05 PM William
- Enhanced to insert "membership_guid" field for "membership_points" table.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.

1/8/2020 4:10 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.
*/
include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_REDEEM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_REDEEM', BRANCH_CODE), "/index.php");
$maintenance->check(438);

// shows user the redemption module is unavailable if found no connection to HQ
$hqcon = connect_hq();

if(!$hqcon->db_connect_id){
	$smarty->display('header.tpl');
	print "<h1>Membership Redemption".$LANG['HQ_OFFLINE']."</h1>";
	$smarty->display('footer.tpl');
	exit;
}

class MembershipRedemption extends Module{
 	function _default(){
		global $con, $smarty;
		//$this->init_table();
	    $this->display();
	}
	
	
	// disabled since already moved to maintenance.php
	/*private function init_table(){
		global $con, $hqcon;
		
		$con->sql_query("create table if not exists membership_redemption(
branch_id int,
id int auto_increment,
user_id int,
card_no varchar(20),
nric varchar(20),
redemption_no char(20) unique,
active tinyint default 1,
status tinyint default 0,
added timestamp default 0,
last_update timestamp default 0,
date date,
points_left int,
total_pt_need int,
total_cash_need double,
total_qty int,
adjustment_id int,
print_count int not null default 0,
cancel_by int,
primary key(branch_id,id),
index(user_id),index(card_no),index(nric),index(active),index(status),index(adjustment_id),index(date)
)") or die(mysql_error());
		$con->sql_query("alter table membership_redemption convert to charset latin1 collate latin1_general_ci",false,false);

		$con->sql_query("create table if not exists membership_redemption_items(
id int auto_increment,
branch_id int,
membership_redemption_id int,
sku_item_id int,
redemption_item_id int,
cost double,
selling_price double,
qty double,
pt_need int,
cash_need double,
receipt_amt_need double,
receipt_no varchar(20),
receipt_date date,
counter_no varchar(20),
primary key(branch_id,id),
index(membership_redemption_id),index(sku_item_id),
index(redemption_item_id),index(receipt_no,receipt_date,counter_no)
)") or die(mysql_error());
        $con->sql_query("alter table membership_redemption_items convert to charset latin1 collate latin1_general_ci",false,false);
	}*/
	
	function check_and_show_items(){
		global $con, $hqcon, $smarty, $sessioninfo, $LANG, $config;
		
		//print_r($_REQUEST);
		$card_no = $_REQUEST['card_no'];
	    $nric = $_REQUEST['nric'];
	    
	    //$check_card_no = $card_no;
		if($config['membership_use_card_prefix']){
			if(!preg_match($config['membership_valid_cardno'], $card_no)){
	            $card_no = get_membership_card_prefix($sessioninfo['branch_id']).$card_no;
			}
		}
	
		// check card no and nric
		$hqcon->sql_query("select *, branch.code as apply_branch_code, branch.ip as icfile_ip from membership left join branch on membership.apply_branch_id = branch.id where nric=".ms($nric)." and card_no=".ms($card_no)) or die(mysql_error());
		$membership = $hqcon->sql_fetchrow();
		
		// check card info
		if(!$membership)    $err[] = "Invalid Card No or NRIC";
		else{
			//if(time()>strtotime($membership['next_expiry_date']))   $err[] = "This Card have been expired";
			if($membership['blocked_by']>0) $err[] = "This Card have been blocked";
			elseif($config['membership_data_use_custom_field']['principal_card'] && $membership['parent_nric']){
				$hqcon->sql_query("select blocked_by from membership where nric=".ms($membership['parent_nric'])) or die(mysql_error());
				if($hqcon->sql_fetchfield(0) > 0) $err[] = "This Card have been blocked";
			}elseif($config['membership_redemption_disallow_expired_member'] && time()>strtotime($membership['next_expiry_date'])) $err[] = "This Card have been expired";
		}
		
		if(!$err && !$_REQUEST['proceed'] && $config['membership_redemption_use_enhanced']){
			// Scanned IC image
			$hurl = get_branch_file_url($membership['apply_branch_code'], $membership['icfile_ip']);
			$ic_path = "$hurl/$config[scanned_ic_path]/$membership[nric].JPG";
		}else{
			if(!$err){
				$_REQUEST['proceed'] = 1;
			}
		}

		if(!$err && $_REQUEST['proceed']){
		    // get latest card type and issue by branch
		    $sql = "select mh.id, mh.nric, mh.card_no, mh.branch_id,branch.code as branch_code, mh.card_type, 
					mh.issue_date, mh.expiry_date, mh.remark, user.u 
					from membership_history mh
					left join branch on branch_id = branch.id left join user on user_id = user.id 
					where nric=".ms($nric)."
					order by issue_date desc, expiry_date desc
					limit 1";

		    $con->sql_query($sql) or die(mysql_error());
			$membership['history'] = $con->sql_fetchrow();
			
			$nric_list[] = ms(-1);
			
			if(!$membership['parent_nric']) $nric_list[] = ms($nric);	// normal member redemption
			else{			
				if($config['membership_allow_use_points_from_principal_card']){ // got principal card
					$nric_list[] = ms($membership['parent_nric']);
					$nric_list[] = ms($nric);
				}
			}
			
			// get points from sub card
			$q1 = $con->sql_query("select * from membership where parent_nric = ".ms($nric));
			
			while($r = $con->sql_fetchassoc($q1)){
				$nric_list[] = ms($r['nric']);
			}
			$con->sql_freeresult($q1);
			
			// get latest points
			$sql = "select sum(points) as latest_point from membership_points p left join branch b on b.id = p.branch_id where p.nric in (".join(",", $nric_list).") order by p.date desc ";
			
			$hqcon->sql_query($sql) or die(mysql_error());
			$membership['points'] = mi($hqcon->sql_fetchfield(0));
			
			if($_REQUEST['save']){  // the action is save
				$this->validate_data($membership, $upd, $receipt_info, $items, $err);
				
				if($config['membership_redemption_use_enhanced'] && !$err){
					$params = array();
					$params['type'] = 'MEMBERSHIP_REDEMPTION';
					$params['user_id'] = $sessioninfo['id'];
					$params['reftable'] = 'membership_redemption';
					//$params['skip_approve'] = true;
			       	$params['branch_id'] = $sessioninfo['branch_id'];
			       	if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id'];
			       	$astat = check_and_create_approval2($params, $con);
		
			       	if(!$astat){
						$err[]=$LANG['REDEMPTION_SKU_NO_APPROVAL_FLOW'];
					}else{
						$upd['approval_history_id']=$astat[0];
						$membership['recipients']=$astat[2];
						if($astat[1]=='|') $membership['is_last_approval']=true;
					}
				}

				if(!$err){  // no error, perform to save and generate
					if(!$config['membership_redemption_use_enhanced']) $upd['verified'] = 1;
					$this->save_redemption($membership, $upd, $receipt_info, $items, $err);
					if(!$err)   exit;
				}
			}
		
			if(!$config['membership_redemption_use_enhanced'] || ($config['membership_redemption_use_enhanced'] && $this->item_id_list)){
				// load available items
				$max_points = mi($membership['points']);
				$filter = array();
				$filter[] = "mrs.active=1 and mrs.confirm=1";
				$filter[] = "mrs.point<=$max_points";
				if(BRANCH_CODE=='HQ')	$filter[] = "mrs.branch_id=".mi($sessioninfo['branch_id']);
				else    $filter[] = "(mrs.branch_id=".mi($sessioninfo['branch_id'])." or mrs.branch_id=1)";
				$filter = "where ".join(' and ',$filter);

				if($this->item_id_list) $filter .= " and mrs.id in (".join(',', $this->item_id_list).")";

				$curr_date = date('Y-m-d');

				$sql = "select mrs.*,si.sku_item_code,si.description,ifnull(sip.price, si.selling_price) as selling_price, 
						si.doc_allow_decimal
						from membership_redemption_sku mrs
						left join sku_items si on si.id=mrs.sku_item_id
						left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
						$filter and
						case
							when mrs.valid_date_from is not null and mrs.valid_date_from != '' and mrs.valid_date_from != '0000-00-00' and mrs.valid_date_to is not null and mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00'
							then mrs.valid_date_from <= ".ms($curr_date)." and mrs.valid_date_to >= ".ms($curr_date)."
							when mrs.valid_date_from is not null and mrs.valid_date_from != '' and mrs.valid_date_from != '0000-00-00' and (mrs.valid_date_to is null or mrs.valid_date_to = '' or mrs.valid_date_to = '0000-00-00')
							then mrs.valid_date_from <= ".ms($curr_date)."
							when (mrs.valid_date_from is null or mrs.valid_date_from = '' or mrs.valid_date_from = '0000-00-00') and mrs.valid_date_to is not null and mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00'
							then mrs.valid_date_to >= ".ms($curr_date)."
							else 1=1
						end
						order by point";

				$con->sql_query($sql) or die(mysql_error());
				while($r = $con->sql_fetchrow()){
				    if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
						$r['available_branches'] = unserialize($r['available_branches']);
						if(!$r['available_branches'][$sessioninfo['branch_id']])    continue;   // not available for current branch
					}
					
					$condition_list = array();
					if($r['point'])	$condition_list[] = number_format($r['point'])." pt";
					//$r['cash'] = 0;
					if($r['valid_date_from'] == '0000-00-00') $r['valid_date_from'] = '';
					if($r['valid_date_to'] == '0000-00-00') $r['valid_date_to'] = '';
					$item_list[] = $r;
				}
			}

			// Scanned IC image
			$hurl = get_branch_file_url($membership['apply_branch_code'], $membership['icfile_ip']);
			//$r['ic_path'] = "/file_tunnel.php?f=$hurl/$config[scanned_ic_path]/$r[nric].JPG";
			$membership['ic_path'] = "$hurl/$config[scanned_ic_path]/$membership[nric].JPG";

			$smarty->assign('err',$err);
            $smarty->assign('item_list',$item_list);
            $smarty->assign('items',$items);
			$smarty->assign('membership_info',$membership);
			$this->display('membership.redemption.redemption_item_list.tpl');
			exit;
		}
		
		if($err || $ic_path){
            $smarty->assign('err',$err);
            if($ic_path){
            	$smarty->assign('ic_path', $ic_path);
            	$smarty->assign('card_no', $card_no);
            	$smarty->assign('nric', $nric);
            }
            $this->display();
		}
	}
	
	private function validate_data($membership, &$upd, &$receipt_info, &$items, &$err){
        global $con, $smarty, $sessioninfo, $LANG, $config;
        
        $upd['points_left'] = mi($membership['points']);
        $upd['total_pt_need'] = 0;
        $upd['total_cash_need'] = 0;
        $upd['total_cash_paid'] = $_REQUEST['ttl_cash_paid'];
        $upd['card_no'] = $membership['card_no'];
        $upd['nric'] = $membership['nric'];
        $upd['gross_total_amt'] = 0;
        $upd['total_gst_amt'] = 0;
        $upd['total_amount'] = 0;
		
        $bid = mi($sessioninfo['branch_id']);
        //print_r($membership);
        
        if(!$_REQUEST['qty']){
			$err[] = $LANG['REDEMPTION_EMPTY'];
			return;
		}
		
		if($config['enable_gst']){
			$params = array();
			$params['date'] = date("Y-m-d");
			$params['branch_id'] = $sessioninfo['branch_id'];
			$upd['is_under_gst'] = check_gst_status($params);
		}
		
		$item_id_list = array();
		foreach($_REQUEST['qty'] as $item_branch_id=>$qty_made){
		    list($item_id, $ibranch_id) = explode("_",$item_branch_id);
			if(mi($qty_made)<=0)  continue;
			$item_ids[] = $item_id;
			$items[$item_branch_id]['qty'] = $qty_made;
			$items[$item_branch_id]['receipt_no'] = $_REQUEST['receipt_no'][$item_branch_id];
			$items[$item_branch_id]['receipt_date'] = $_REQUEST['receipt_date'][$item_branch_id];
			$items[$item_branch_id]['counter_no'] = $_REQUEST['counter_no'][$item_branch_id];
			$items[$item_branch_id]['redemption_item_id'] = $item_branch_id;
			$items[$item_branch_id]['voucher_code'] = $_REQUEST['voucher_code'][$item_branch_id];
			$items[$item_branch_id]['voucher_value'] = $_REQUEST['voucher_value'][$item_branch_id];
			$item_id_list[$ibranch_id][] = $item_id;
		}
		
		$this->item_id_list = $item_ids;
		
		//print_r($item_id_list);
		
		if($item_id_list){
            $filter = array();
			$filter[] = "mrs.active=1";
			//$filter[] = "mrs.point<=$max_points";
			if(BRANCH_CODE=='HQ')	$filter[] = "mrs.branch_id=$bid";
			else    $filter[] = "(mrs.branch_id=$bid or mrs.branch_id=1)";

			//$filter[] = "mrs.id in (".join(',',$item_id_list).")";
			foreach($item_id_list as $ibranch_id => $all_item_list){
				$filter_or[] = "(mrs.branch_id=".mi($ibranch_id)." and mrs.id in (".join(',',$all_item_list)."))";
			}
			$filter[] = "(".join(' or ', $filter_or).")";
			
			$filter = "where ".join(' and ',$filter);

			$sql = "select mrs.*,si.sku_item_code,si.description,ifnull(sip.price, si.selling_price) as selling_price,sic.grn_cost, si.doc_allow_decimal
					from membership_redemption_sku mrs
					left join sku_items si on si.id=mrs.sku_item_id
					left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id =$bid
					left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = $bid
					$filter
					order by point";

			//die($sql);
			$q1 = $con->sql_query($sql) or die(mysql_error());
			while($r = $con->sql_fetchrow($q1)){
			    if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
					$r['available_branches'] = unserialize($r['available_branches']);
					if(!$r['available_branches'][$sessioninfo['branch_id']])    {
						$err[] = sprintf($LANG['REDEMPTION_SKU_UNAVAILABLE'], $r['sku_item_code']);
					}
				}
				
			    $row_key = $r['id']."_".$r['branch_id'];
			    //$r['cash'] = 0;
				//$redemption_sku[$row_key] = $r;
				
				// GST
				$sku_item_id = mi($r['sku_item_id']);
				$selling_price = round($r['selling_price'], 2);
				if($config['enable_gst']){
					//if($form['is_under_gst']){
						// get sku is inclusive
						$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
						// get sku original output gst
						$sku_original_output_gst = get_sku_gst("output_tax", $sku_item_id);
						
						if($sku_original_output_gst){
							if($is_sku_inclusive == 'yes'){
								// is inclusive tax
								// find the price before tax
								$gst_tax_price = round($selling_price / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
								$price_included_gst = $selling_price;
								$selling_price = round($selling_price - $gst_tax_price, 2);
							}
							
							// got gst for this branch
							if($upd['is_under_gst']){
								$items[$row_key]['gst_id'] = $sku_original_output_gst['id'];
								$items[$row_key]['gst_code'] = $sku_original_output_gst['code'];
								$items[$row_key]['gst_rate'] = $sku_original_output_gst['rate'];
								$items[$row_key]['gst_indicator'] = $sku_original_output_gst['indicator_receipt'];
							}
						}
					//}
				}
	
				$items[$row_key]['sku_item_id'] = $sku_item_id;
				$items[$row_key]['selling_price'] = $selling_price;
				$items[$row_key]['cost'] = mf($r['grn_cost']);
				$items[$row_key]['pt_need'] = mi($r['point']);
				$items[$row_key]['cash_need'] = mf($r['cash']);
				$items[$row_key]['receipt_amt_need'] = mf($r['receipt_amount']);
				if($r['receipt_amount']){
			        $items[$row_key]['receipt_date_from'] = $r['receipt_date_from'];
			        $items[$row_key]['receipt_date_to'] = $r['receipt_date_to'];
			       	if($r['use_curr_date'])	$items[$row_key]['use_curr_date'] = true;
        		}
				
				// gst amt per qty
				$pcs_gst_amt = 0;
				if($upd['is_under_gst']){
					$pcs_gst_amt = round($selling_price * $items[$row_key]['gst_rate'] / 100, 2);
				}
				
				// calculate gross amt
				$items[$row_key]['line_gross_amt'] = $items[$row_key]['qty'] * $selling_price;
				
				// line gst amt
				$line_gst_amt = 0;
				if($items[$row_key]['gst_rate']>0){
					$line_gst_amt = round($pcs_gst_amt * $items[$row_key]['qty'], 2);
				}
				$items[$row_key]['line_gst_amt'] = $line_gst_amt;
				$items[$row_key]['line_amt'] = round($items[$row_key]['line_gross_amt'] + $line_gst_amt, 2);
				
				$upd['gross_total_amt'] += $items[$row_key]['line_gross_amt'];
				$upd['total_gst_amt'] += $items[$row_key]['line_gst_amt'];
				$upd['total_amount'] += $items[$row_key]['line_amt'];
			}
			$con->sql_freeresult($q1);
			
			foreach($items as $item_id=>$r){
				$qty = mi($r['qty']);
				$pt_need = mi($r['pt_need']);
				$cash_need = mf($r['cash_need']);
				$receipt_amt_need = mf($r['receipt_amt_need']);
				
				$upd['total_qty'] += $qty;
				if($pt_need>0){ // need points
                    $upd['total_pt_need'] += ($pt_need*$qty);
				}
				
				if($cash_need){ // need cash
                    $upd['total_cash_need'] += ($cash_need*$qty);
				}
				
				if($receipt_amt_need>0){ // need receipt
					$receipt_no = trim($r['receipt_no']);
					$receipt_date = trim($r['receipt_date']);
					$counter_no = trim($r['counter_no']);

					if(!$receipt_no||!$receipt_date||!$counter_no){
						$err[] = $LANG['REDEMPTION_RECEIPT_DETAIL_REQUIRED'];
						continue;
					}
					$receipt_key = $receipt_no."/".$receipt_date."/".$counter_no;
					
					if(!$receipt_info[$receipt_key]){   // no receipt info, retrieve from database
                        $rcp_filter = array();
						$rcp_filter[] = "pos.date=".ms($receipt_date);
						$rcp_filter[] = "pos.branch_id=$bid";
						$rcp_filter[] = "cs.network_name=".ms($counter_no);
						$rcp_filter[] = "pos.receipt_no=".ms($receipt_no);
						$rcp_filter[] = "cs.active=1 and cancel_status=0";

						$rcp_filter = "where ".join(' and ', $rcp_filter);
						$sql = "select pos.* ,cs.network_name,
						(select count(*) from membership_redemption_items mri
left join membership_redemption mr on mr.id=mri.membership_redemption_id and mr.branch_id=mri.branch_id
where mri.receipt_no=pos.receipt_no and mri.receipt_date=pos.date and mri.counter_no=cs.network_name and mr.active=1 and mr.status=0) as receipt_used
	from pos
	left join counter_settings cs on cs.id=pos.counter_id and cs.branch_id=pos.branch_id
	$rcp_filter limit 1";
	    //print $sql;	

	                    $con->sql_query($sql) or die(mysql_error());
	                    $receipt_info[$receipt_key] = $con->sql_fetchrow();
	                    $receipt_info[$receipt_key]['sql_selected'] = 1;
	                    $receipt_info[$receipt_key]['amt_left'] = mf($receipt_info[$receipt_key]['amount']);
					}
					
					if(!$receipt_info[$receipt_key]['receipt_no']){ // no receipt num
						$err[] = sprintf($LANG['REDEMPTION_INVALID_RECEIPT'], $receipt_no, $receipt_date, $counter_no);
						continue;
					}
					
					if($receipt_info[$receipt_key]['receipt_used']){ // receipt used in ohther redemption
						$err[] = sprintf($LANG['REDEMPTION_RECEIPT_USED'], $receipt_no);
						continue;
					}
					
					$receipt_amt_need2 = mf($receipt_amt_need*$qty);
					if($receipt_amt_need2>$receipt_info[$receipt_key]['amt_left']){
			            $err[] = sprintf($LANG['REDEMPTION_RECEIPT_AMT_UNMATCHED'], $receipt_no);
			            continue;
					}
					
					if($r['use_curr_date']){
						if(strtotime($receipt_info[$receipt_key]['date'])!=strtotime(date('Y-m-d'))){
							$err[] = sprintf($LANG['REDEMPTION_INVALID_RECEIPT_DATE'], $receipt_no);
				            continue;
						}
					}else{
                        if(strtotime($r['receipt_date_from'])&&$receipt_info[$receipt_key]['date']<$r['receipt_date_from']){
							$err[] = sprintf($LANG['REDEMPTION_RECEIPT_BEFORE_REDEEM_DATE'], $receipt_no, $r['receipt_date_from']);
				            continue;
				        }
						if(strtotime($r['receipt_date_to'])&&$receipt_info[$receipt_key]['date']>$r['receipt_date_to']){
							$err[] = sprintf($LANG['REDEMPTION_RECEIPT_AFTER_REDEEM_DATE'], $receipt_no, $r['receipt_date_to']);
				            continue;
				        }
					}
					
					$receipt_info[$receipt_key]['amt_left'] -= $receipt_amt_need2;
				}

				if($r['voucher_code']){
					$existing_vouchers = array();
					list($tmp_iid, $tmp_bid) = explode("_",$item_id);
					foreach($r['voucher_code'] as $row=>$val){
						$voucher_verify_code_unmatched = false;
						if(!$val){ // found it is empty
							$err[] = $LANG['REDEMPTION_VOUCHER_EMPTY'];
							break;
						}
						
						if($existing_vouchers[$val]){ // found it is duplicated in same redemption
							$err[] = sprintf($LANG['REDEMPTION_VOUCHER_DUPLICATE'], $val);
							break;
						}
						
						$q1 = $con->sql_query("select * from pos_settings where setting_name = 'barcode_voucher_prefix' and branch_id = ".mi($sessioninfo['branch_id']));
						$bvp_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						// if found branch no set voucher prefix code, use "VC" as default
						if(!$bvp_info) $bvp_info['setting_value']="VC";
						$setting_prefix_length = strlen($bvp_info['setting_value']);
						
						// check voucher length
						$scanned_voucher_prefix = substr($val, 0, $setting_prefix_length);
						
						// check prefix 
						if($scanned_voucher_prefix != $bvp_info['setting_value']) $voucher_verify_code_unmatched = true;
						
						$prefix_length = $setting_prefix_length+14;
						if(strlen($val) != $prefix_length) $voucher_verify_code_unmatched = true;
						
						//check last 2 digit to validate the voucher
						$org_voucher_code = substr($val, $setting_prefix_length, 12);
						
						$scanned_verify_code = substr($val,-2);
						$verify_code= substr(encrypt_for_verification($org_voucher_code),0,2);

						if (strtoupper($scanned_verify_code) != strtoupper($verify_code)) $voucher_verify_code_unmatched = true;

						if($voucher_verify_code_unmatched){
							$err[] = sprintf($LANG['REDEMPTION_VOUCHER_LENGTH_UNMATCHED'], $val);
							continue;
						}

						$this->voucher_code[$val] = $voucher_code = substr($org_voucher_code, 0, 7);
						$voucher_amt = substr($org_voucher_code,-5 , 5)/100;
						
						// check voucher amount
						$q1 = $con->sql_query("select * from mst_voucher where code = ".ms($voucher_code)." and voucher_value = ".mf($voucher_amt));
						$r1 = $con->sql_fetchassoc($q1);
						//check if voucher code has been used
						$used_voucher = false;
						$q2 = $con->sql_query("select id from membership_redemption_items where voucher_code like '%\"$val\"%' limit 1");
						if ($con->sql_numrows($q2) > 0) $used_voucher = true;
						
						if($con->sql_numrows($q1) == 0){
							$err[] = sprintf($LANG['REDEMPTION_VOUCHER_NOT_EXISTS'], $val);
							continue;
						}elseif($voucher_amt != $r['voucher_value']){
							$err[] = sprintf($LANG['REDEMPTION_VOUCHER_INVALID_AMT'], $val);
							continue;
						}elseif($used_voucher){
							$err[] = sprintf($LANG['REDEMPTION_VOUCHER_USED'], $val);
							continue;
						}else{
							if ($r1['branch_id'] != $sessioninfo['branch_id'] && !$config['voucher_allow_cross_branch_activate']){
								$err[] = sprintf($LANG['REDEMPTION_VOUCHER_INVALID_BRANCH'], $val);
								continue;
							}elseif($r1['cancel_status'] == '1'){
								$err[] = sprintf($LANG['REDEMPTION_VOUCHER_CANCELED'], $val);
								continue;
							}elseif($r1['is_print'] == '0'){
								$err[] = sprintf($LANG['REDEMPTION_VOUCHER_NOT_PRINTED'], $val);
								continue;
							}elseif($r1['active'] == '0' && !privilege('MEMBERSHIP_ALLOW_ACTIVATE_VOUCHER') && !privilege('MST_VOUCHER_ACTIVATE')){
								$err[] = $LANG['REDEMPTION_VOUCHER_NO_PRIVILEGE'];
								continue;
							}elseif($r1['active'] == '1' && $config['membership_redemption_check_voucher_activation']){
								$err[] = sprintf($LANG['REDEMPTION_VOUCHER_ACTIVATED'], $val);
								continue;
							}elseif($r1['valid_to'] > 0 && strtotime($r1['valid_to'])<time()){
								$err[] = sprintf($LANG['REDEMPTION_VOUCHER_EXPIRED'], $val, $r1['valid_to']);
								continue;
							}
						}
						$con->sql_freeresult($q1);
						
						$existing_vouchers[$val] = true;
					}
				}
			}
		}
		
		if($upd['total_pt_need']>$upd['points_left']) $err[] = sprintf($LANG['REDEMPTION_INSUFFICIENT_POINTS'], number_format($upd['total_pt_need']), number_format($upd['points_left']));
		//print_r($upd);
		//print_r($items);
		//$err[] = "testing error";
		
	}
	
	private function save_redemption($membership, $upd, $receipt_info, $items, &$err){
        global $con, $smarty, $sessioninfo, $hqcon, $config, $LANG, $appCore;
        
        $sku_item_id_list = array();
		$have_vouchers = false;
        
        if(!$items){
			$err[] = $LANG['REDEMPTION_EMPTY'];
			return;
		}
		
		// generate membership_redemption
		// from_branch
		$con->sql_query("select * from branch where id=".mi($sessioninfo['branch_id'])) or die(mysql_error());
		$from_branch = $con->sql_fetchrow();
		
		// insert header
        $upd['branch_id'] = $sessioninfo['branch_id'];
        $upd['user_id'] = $sessioninfo['id'];
        $upd['added'] = 'CURRENT_TIMESTAMP';
        $upd['last_update'] = 'CURRENT_TIMESTAMP';
        $upd['date'] = date('Y-m-d');
        
        $con->sql_query("insert into membership_redemption ".mysql_insert_by_field($upd)) or die(mysql_error());
        $membership_redemption_id = $con->sql_nextid();

		// update redemption no
        $upd_later = array();
        $upd_later['redemption_no'] = $from_branch['report_prefix'].sprintf('%05d',$membership_redemption_id);
        

        // insert items
        foreach($items as $item_id=>$item){
            $sku_item_id_list[] = $item['sku_item_id'];
            
			$item['branch_id'] = $sessioninfo['branch_id'];
			$item['membership_redemption_id'] = $membership_redemption_id;
			unset($item['receipt_date_from']);
			unset($item['receipt_date_to']);
			if($item['voucher_code']){
				list($tmp_iid, $tmp_bid) = explode("_",$item_id);
				foreach($item['voucher_code'] as $row=>$val){
					$q1 = $con->sql_query("update mst_voucher set mr_id = ".mi($membership_redemption_id).", mr_branch_id = ".mi($sessioninfo['branch_id']).", last_update = CURRENT_TIMESTAMP where code = ".ms($this->voucher_code[$val])." and branch_id = ".mi($tmp_bid)." and voucher_value = ".mf($item['voucher_value']));
				}
				$item['is_voucher'] = true;
				$item['voucher_value'] = $item['voucher_value'];
				$item['voucher_code'] = serialize($item['voucher_code']);
				$have_vouchers = true;
			}else{
				$item['is_voucher'] = false;
				$item['voucher_value'] = 0;
				$item['voucher_code'] = "";
			}
			$con->sql_query("insert into membership_redemption_items ".mysql_insert_by_field($item)) or die(mysql_error());
		}
		
		// get the items latest stock balance
		$con->sql_query("select * from sku_items_cost where branch_id=$sessioninfo[branch_id] and sku_item_id in (".join(',',$sku_item_id_list).")") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$stock_balance[$r['sku_item_id']] = $r;
		}
		
		log_br($sessioninfo['id'], 'Redemption', $membership_redemption_id, "Make Redemption (Branch#".$sessioninfo['branch_id'].", ID#$membership_redemption_id, Card No:$upd[card_no], NRIC: $upd[nric])");
		
		
		// generate adjustment
		// insert header
		$adj = array();
		$adj['id'] = $appCore->generateNewID("adjustment","branch_id=".mi($sessioninfo['branch_id']));
		$adj['branch_id'] = $sessioninfo['branch_id'];
		$adj['user_id'] = $sessioninfo['id'];
        $adj['added'] = 'CURRENT_TIMESTAMP';
        $adj['last_update'] = 'CURRENT_TIMESTAMP';
        $adj['adjustment_date'] = date('Y-m-d');
        $adj['adjustment_type'] = 'Membership Redemption';
        $adj['remark'] = "Redemption ID: #$membership_redemption_id";
        $adj['status'] = 1;
        $adj['approved'] = 1;
        $con->sql_query("insert into adjustment ".mysql_insert_by_field($adj)) or die(mysql_error());
        //$adj_id = $con->sql_nextid();
		$adj_id = $adj['id'];
        
        // insert items
        foreach($items as $item_id=>$item){
            $adj_item = array();
			$adj_item['id'] = $appCore->generateNewID("adjustment_items","branch_id=".mi($sessioninfo['branch_id']));
            $adj_item['branch_id'] = $sessioninfo['branch_id'];
            $adj_item['adjustment_id'] = $adj_id;
            $adj_item['user_id'] = $sessioninfo['id'];
            $adj_item['sku_item_id'] = $item['sku_item_id'];
            $adj_item['qty'] = $item['qty']*-1;
            $adj_item['cost'] = $item['cost'];
            $adj_item['stock_balance'] = $stock_balance[$item['sku_item_id']]['qty'];
            $con->sql_query("insert into adjustment_items ".mysql_insert_by_field($adj_item)) or die(mysql_error());
            
        }
        log_br($sessioninfo['id'], 'ADJUSTMENT', $adj_id, "Auto generate and approve by Redemption Module (Branch#".$sessioninfo['branch_id'].", Redemption ID#$membership_redemption_id, Card No:$upd[card_no], NRIC: $upd[nric])");
        
        // update header again
        $upd_later['adjustment_id'] = $adj_id;
        $con->sql_query("update membership_redemption set ".mysql_update_by_field($upd_later)." where id=$membership_redemption_id and branch_id=".mi($sessioninfo['branch_id'])) or die(mysql_error());

        // update sku item cost
        $con->sql_query("update sku_items_cost set changed=1 where branch_id=".$sessioninfo['branch_id']." and sku_item_id in (".join(',',$sku_item_id_list).")");
        
        // check membership point
        $hqcon->sql_query("select * from membership_points where card_no=".ms($upd['card_no'])." and branch_id=".$sessioninfo['branch_id']." and date(date)=".ms(date('Y-m-d'))." and type='REDEEM'") or die(mysql_error());
        $temp = $hqcon->sql_fetchrow();
        $mp = array();
        if($temp){  // already have this entry, do update
			$mp['points'] = $temp['points']+mf($upd['total_pt_need']*-1);
			//$mp['remark'] = $temp['remark'].", \nRedemption #$membership_redemption_id";
			$hqcon->sql_query("update membership_points set ".mysql_update_by_field($mp)." where card_no=".ms($upd['card_no'])." and branch_id=".$sessioninfo['branch_id']." and date(date)=".ms(date('Y-m-d'))." and type='REDEEM'") or die(mysql_error());
		}else{
            // insert into membership_points
			$mp['membership_guid'] = $membership['membership_guid'];
	        $mp['nric'] = $upd['nric'];
	        $mp['card_no'] = $upd['card_no'];
	        $mp['branch_id'] = $sessioninfo['branch_id'];
			$mp['date'] = 'CURRENT_TIMESTAMP';
			$mp['points'] = mf($upd['total_pt_need']*-1);
			$mp['remark'] = "Make Redemption";
			$mp['type'] = 'REDEEM';
			$mp['user_id'] = $sessioninfo['id'];
			$hqcon->sql_query("insert into membership_points ".mysql_insert_by_field($mp)) or die(mysql_error());
		}

		// update into membership to get the latest points....
		$hqcon->sql_query("update membership set points = points - ".mi($upd['total_pt_need']).", points_update = ".ms($upd['date'])." where nric = ".ms($upd['nric']));

		log_br($sessioninfo['id'], 'Redemption', $membership_redemption_id, "Reduce Membership Point (Branch#".$sessioninfo['branch_id'].", Redemption ID#$membership_redemption_id, Card No:$upd[card_no], NRIC: $upd[nric])");

		if($config['membership_redemption_use_enhanced']){
			if($membership['is_last_approval']){	// is last approval
				$con->sql_query("update membership_redemption set status=0, verified=1, last_update=CURRENT_TIMESTAMP where id=$membership_redemption_id and branch_id=$sessioninfo[branch_id]");
				
				$tmp = array();
				$tmp['branch_id'] = $sessioninfo['branch_id'];
				$tmp['approval_history_id'] = $upd['approval_history_id'];
				$tmp['user_id'] = $sessioninfo['id'];
				$tmp['status'] = 1;
				$tmp['log'] = 'Verified';
				
				$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($tmp));
			}
			$con->sql_query("update branch_approval_history set ref_id=$membership_redemption_id where id=$upd[approval_history_id] and branch_id=$sessioninfo[branch_id]");
			/*
			// send pm
			$recipients=$membership['recipients'];
			$recipients=str_replace("|$sessioninfo[id]|", "|", $recipients);
	       	$to=preg_split("/\|/", $recipients);
			*/
			$to = get_pm_recipient_list2($membership_redemption_id,$upd['approval_history_id'],0,'confirmation',$sessioninfo['branch_id'],'membership_redemption');
			send_pm2($to, "Membership Redemption Send to Verify ($upd_later[redemption_no])", "/membership.redemption_history.php?a=view&id=$membership_redemption_id&branch_id=$sessioninfo[branch_id]", array('module_name'=>'membership_redemption'));
			
		}

        //die("OK");
		if(!$err){
			header("Location: $_SERVER[PHP_SELF]?a=redempt_success&id=".$membership_redemption_id."&branch_id=".$sessioninfo['branch_id']."&have_vouchers=".$have_vouchers);
			exit;
		}
	}
	
	function redempt_success(){
        global $con, $smarty, $sessioninfo,$LANG;
        
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		$con->sql_query("select * from membership_redemption where id=$id and branch_id=$branch_id and active=1") or die(mysql_error());
		$form = $con->sql_fetchrow();
		
		if(!$form){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		$smarty->assign('form',$form);
        $smarty->display('membership.redemption.success.tpl');
	}
	
	function ajax_add_item_row(){
        global $con, $smarty, $sessioninfo, $LANG, $hqcon;
        $nric = $_REQUEST['nric'];
		$str = '';
		
		if (!$_REQUEST['sku_code_list'])
		{
			print $LANG['PROMO_SELECT_SKU'];
			exit;
		}

		foreach($_REQUEST['sku_code_list'] as $idx=>$mrs_id)
		{
		    /*$hqcon->sql_query("select * from sku_items where id=".mi($sku_item_id)) or die(mysql_error());
		    $r = $hqcon->sql_fetchrow();
			if (!$r)    continue;   // no this sku item id*/
			$sku_item_code = explode(",", $_REQUEST['grp_sku_code']);

			// get latest points
			$sql = "select sum(points) as latest_point from membership_points p left join branch b on b.id = p.branch_id where p.nric=".ms($nric)." order by p.date desc ";
			$hqcon->sql_query($sql) or die(mysql_error());
			$max_points = $hqcon->sql_fetchfield(0);
			
			// load items
			$sql = "select mrs.*,si.sku_item_code,si.description,ifnull(sip.price, si.selling_price) as selling_price, si.doc_allow_decimal
					from membership_redemption_sku mrs
					left join sku_items si on si.id=mrs.sku_item_id
					left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
					where mrs.active=1 and mrs.point<=".mi($max_points)." and mrs.id=".mi($mrs_id)." and si.sku_item_code = ".ms($sku_item_code[$idx])."
order by point";

			$con->sql_query($sql) or die(mysql_error());
			$total_rows = $con->sql_numrows();
			
			while($r = $con->sql_fetchrow()){
			    if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
					$r['available_branches'] = unserialize($r['available_branches']);
					if(!$r['available_branches'][$sessioninfo['branch_id']]) continue; // not available for current branch
				}
				if($r['valid_date_from'] == '0000-00-00') $r['valid_date_from'] = '';
				if($r['valid_date_to'] == '0000-00-00') $r['valid_date_to'] = '';
				$item = $r;
			}
			$con->sql_freeresult();
			$smarty->assign('err',$err);
            $smarty->assign('item',$item);
			if($total_rows) $str .= $smarty->fetch('membership.redemption.redemption_item_list_row.tpl');
		}
		
		print trim($str);
	}
	
	function barcode_scan(){ // this function available while config['membership_redemption_use_enhanced'] added
		global $con, $smarty, $sessioninfo, $LANG, $hqcon;

		$form = $_REQUEST;
		if(!$form['mr_barcode']) return;

		$curr_date = date('Y-m-d');
		$sdesc = trim($form['mr_barcode']);
		$linkcode = $sdesc;
		if(strlen($sdesc)==8) $linkcode = substr($sdesc,0,7);
		elseif(preg_match("/^2/",$sdesc)) $linkcode = substr($sdesc,0,12);

		$sql = "select sum(points) as latest_point from membership_points p left join branch b on b.id = p.branch_id where p.nric=".ms($form['nric'])." order by p.date desc";
		$hqcon->sql_query($sql) or die(mysql_error());
		$max_points = $hqcon->sql_fetchfield(0);
	
		//if($form['item_list']) $where[] = "mrs.id not in (".$form['item_list'].")";

		//$where = "(($desc_match) or sku_items.link_code = ".ms($linkcode)." or sku_items.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or sku_items.sku_item_code like ".ms("%$sdesc%")." or sku_items.artno = ".ms($sdesc)." or sku_items.mcode = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc).")";
		$where[] = "si.active=1 and (si.link_code = ".ms($linkcode)." or si.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or si.sku_item_code like ".ms("%".replace_special_char($sdesc)."%")." or si.artno like ".ms(replace_special_char($sdesc).'%')." or si.mcode = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc).")";

		$result = $con->sql_query("select mrs.*, mrs.id, si.sku_item_code, si.description,
								  ifnull(sip.price, si.selling_price) as selling_price,
	 							  si.sku_id, sku.sku_type, si.is_parent,
								  if(mrs.valid_date_from = '0000-00-00', '', mrs.valid_date_from) as valid_date_from,
								  if(mrs.valid_date_to = '0000-00-00', '', mrs.valid_date_to) as valid_date_to,
								  si.doc_allow_decimal
								  from membership_redemption_sku mrs
								  left join sku_items si on si.id = mrs.sku_item_id
								  left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
								  left join sku on si.sku_id = sku.id
								  left join category on sku.category_id = category.id
								  where mrs.active=1 and mrs.confirm=1 and si.active=1 and ".join(" and ", $where)."
								  order by si.sku_item_code, si.description");

		// found 1 record only, straight display row

		if($con->sql_numrows($result) == 0){ // no record found - show error message
			$temp['err'] = "No record found.";
			$ret[] = $temp;
		}elseif($con->sql_numrows($result) == 1){ // straight display the row
			$r = $con->sql_fetchrow($result);

		    if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
				$r['available_branches'] = unserialize($r['available_branches']);
				if(!$r['available_branches'][$sessioninfo['branch_id']]){
					$temp['err'] = "No record found."; // not available for current branch
					$ret[] = $temp;
				}
			}

			if(!$temp['err'] && $form['item_list'] && preg_match("{^".$r['id']."$}", $form['item_list'])){
				$temp['err'] = "Following SKU item already existed:\n * ".$r['sku_item_code'];
				$ret[] = $temp;
			}

			if(!$temp['err']){
				if($r['valid_date_from'] == '0000-00-00') $r['valid_date_from'] = '';
				if($r['valid_date_to'] == '0000-00-00') $r['valid_date_to'] = '';
	
	            $smarty->assign('item',$r);
				$temp['mri_row'] = $smarty->fetch('membership.redemption.redemption_item_list_row.tpl');
				$ret[] = $temp;
			}
		}else{
			$out = "<br />";
			// generate list.
			//$temp['data']['mri_list'] = 1;

			while ($r = $con->sql_fetchrow($result)){
				if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
					$r['available_branches'] = unserialize($r['available_branches']);
					if(!$r['available_branches'][$sessioninfo['branch_id']]) continue; // not available for current branch
				}
				$highlight = '';

				if($form['item_list'] && preg_match("{^".$r['id']."$}", $form['item_list'])) continue;

				if($r['valid_date_from']){
					// is invalid date of sku item
					if($r['valid_date_from'] > $curr_date){
						$highlight = "style='background-color:#FFF8C6;'";
						$title = "title=\"Valid Date haven't Reached\"";
					}
				}
				if($r['valid_date_to']){
					// is invalid date of sku item
					if($r['valid_date_to'] < $curr_date){
						$highlight = "style='background-color:#FFF8C6;'";
						$title = "title=\"Valid Date Overdue\"";
					}
				}
				
				if(!$highlight) $title = "title=\"$r[id],$r[sku_item_code]\"";
				
				
			    $out .= "<li $highlight $title><input id=cb_ajax_sku_$r[id] value=\"$r[id],$r[sku_item_code]$pp\" title=\"".htmlentities($r['description'])."\" ".($max_points < $r['point'] || $highlight != ''?"disabled":"")." type=checkbox><label class=clickable for=cb_ajax_sku_$r[id]>".htmlspecialchars($r['description'])."</label>";
			    if($show_multiple)   $items_list[] = $r;

				//Selling: $%.2f  Cost: $%.3f  Margin: %.2f%%
				//$r[selling_price], $r[cost_price], ($r[selling_price]-$r[cost_price])/$r[cost_price]*100,
				if(!$r['valid_date_from']) $r['valid_date_from'] = "-";
				if(!$r['valid_date_to']) $r['valid_date_to'] = "-";
				$out .= sprintf("<span class=informal> (Point: %s, Cash: %s, Rec. Amt: %s, Date Start: %s, Date End: %s)",  $r['point'], $r['cash'], number_format($r['receipt_amount'], 2), $r['valid_date_from'], $r['valid_date_to']);

				$out .= "</span>";
				$out .= "<input type=hidden id='inp_is_parent,$r[id]' value='$r[is_parent]'>";
				$out .= "</li>";
				if($max_points < $r['point']){
					$out .= sprintf("<li $highlight><span class=informal>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Insufficient Points)</span></li>");
				}
	        }
	        $temp['mri_list'] = "<ul style=\"list-style-type:none;margin:0;padding:0;\">".$out."</ul>";
	        $ret[] = $temp;
		}

		print json_encode($ret);
	}
}

$m = new MembershipRedemption('Membership Redemption');
?>
