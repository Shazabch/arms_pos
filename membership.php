<?php
/*
12/6/2007 5:08:42 PM yinsee
- change icfiles path to use $config[scanned_ic_path]

10/31/2008 07:03:03 PM yinsee
- set block_date = 0 when unblock (front-end check blocking by date)


02/06/2008 05:14:03 PM jeff
- clear terminated_date where renew member card 

7/13/2009 10:22:17 PM yinsee
- use $sessioninfo[branch_id] when insert from last history (to preven
 primary key crashing) 

11/6/2009 3:21:10 PM edward
- add cancel history
11/11/2009 4:56:28 PM edward
- fix view history using card_no to search nric

12/10/2009 4:20:02 PM Andy
- add get redemption history feature

12/29/2009 12:54:10 PM yinsee
- remove file_tunnel usage

1/14/2010 9:52:26 AM edward
- add privilege MEMBERSHIP_EDIT,MEMBERSHIP_ADD checking

1/14/2010 10:57:08 AM Andy
- update card no to null if card no = ''

6/22/2010 2:40:20 PM Alex
- add view only on member information

8/4/2010 10:37:20 AM Alex
- remove dmy_to_sqldate for $_REQUEST['date']

8/11/2010 12:18:31 PM Justin
- Added "user" field into membership_points table.

8/16/2010 10:47:47 AM Andy
- Change "Check Points & History" if no card_no match, check again nric.

10/29/2010 11:26:05 AM Justin
- Added the checking of redemption either is REDEEM or CANCELED.

3/7/2011 3:04:35 PM Justin
- Added the new field "radio_station" onto membership.
- Updated insert/update queries to trigger this field.

3/25/2011 1:55:57 PM Andy
- Add scan card no will prepand prefix card no to check. (need config)

3/30/2011 3:08:15 PM Justin
- Added age limit (controlled by config) for membership application.
- Fixed error message display wrongly while membership application's NRIC already existed in DB.

5/9/2011 12:33:18 PM yinsee
- add Favourite Product tab (request by tommy)

5/10/2011 6:14:02 PM Andy
- Add if no apply_branch_id, update membership info will update current branch as apply_branch_id. If login as HQ, will show a dropdown to let user choose apply branch.

5/11/2011 10:56:12 AM Justin
- Added the validation and update features for apply branch id.
- Fixed the bugs when found invalid email and redirected from PHP, system reset all fields with old data instead of showing current entries entered by user.
- Removed some of the repeated feature for checking of privileges.

6/24/2011 4:58:08 PM Andy
- Make all branch default sort by sequence, code.

6/29/2011 4:58:08 PM Justin
- Added new feature to call out the list of function to view full size of photo or upload photo.
- Added the function to hide/show the remark of "view full size" and enable/disable the view full size whenever system able/unable to find the IC image.
- Added a new function to update photo upload for IC image.
- Added a new function rename the IC image file if found member's NRIC has been changed.
- Fixed the bugs whenever upload new IC photo but still show the existing image instead of the new uploaded one.

8/19/2011 5:32:32 PM Justin
- Fixed the bugs while update IC image photo.

10/18/2011 12:59:32 PM Justin
- Modified to capture block date and reason to insert into membership history.

10/18/2011 5:08:32 PM Justin
- Added new function "user_verify".
- Added the update for "recruit_by".

12/5/2011 2:22:43 PM Justin
- Fixed bug of when renew card, system always pick the last renew branch instead of current branch.

2/27/2012 12:11:43 PM Justin
- Fixed the bugs where login from "Update Information" and "Check Points History" did not check for Cancel Bill.
- Modified "cancel bill" function to use AJAX call.

6/15/2012 6:11:23 PM Justin
- Added new validation "Principal NRIC".
- Do remove to provide member points recalculation every time the members being updated with having principal + sub card.
- Added to show sub card's points history when accessing principal card.

6/22/2012 2:07:00 PM Andy
- Add to show "Auto Redemption" History at membership points history.

7/30/2012 9:30:12 AM Justin
- Enhanced to insert/update new data base on config set into new table membership_extra_info.
- Added new function to run membership extra info structure.

8/2/2012 4:23:23 PM Justin
- Enhanced the form validation to have config checking as if found it is set.

8/28/2012 4:50:11 PM Justin
- Enhanced to recalculate member points while dob is changed.

8/28/2012 6:08 PM Justin
- Enhanced to insert "added" field for membership_history.

9/3/2012 3:38 PM Justin
- Enhanced to validate the renewal date before save it.

9/13/2012 4:26 PM Justin
- Enhanced to redirect member_points_changed into functions.php

10/8/2012 3:48 PM Justin
- Added new function to pickup member_type base on card no and update/insert into database.

10/24/2012 6:17 PM Justin
- Enhanced to have upgrade card feature.

10/25/2012 4:31:00 PM Fithri
- Cancelled transaction items should not included in "Favourite Product" tab.

10/31/2012 10:54 AM Justin
- Fixed bug of when found principal card having sub card, it shows empty point history while combine empty history from sub card.

11/23/2012 2:39 PM Justin
- Bug fixed on membership type check to use pattern check.
- Bug fixed on new card creation store wrong remark.

1/16/2013 11:31 AM Andy
- Add when change staff type will also trigger to recalculate point.
- Show member used and remaining quota information.
- Enhanced sub card point history layout.

2/6/2013 11:18 AM Justin
- Bug fixed on member does not mark to recalculate points while having principal card.

2/21/2013 3:17 PM Andy
- Add checking to privilege "MEMBERSHIP_UPDATE_STAFF_TYPE", if got only can update membership staff type.

2/21/2013 3:17 PM Andy
- Enhanced to set new as member as verified while found config "membership_auto_verify_member" is set.

3/13/2013 1:49 PM Justin
- Bug fixed on picking up wrong info for issue branch while editing member.

3/27/2013 4:23 PM Justin
- Bug fixed on system cannot search by Card No.

7/2/2013 1:46 PM Justin
- Bug fixed on point adjustment that unable to accept "," on keyed in on points.

8/15/2013 3:52 PM Justin
- Bug fixed on favourite products pickup those non member sales while current card no is empty.

8/19/2013 2:28 PM Justin
- Enhanced to update points_changed=1 after user has changed the current principal card into another.
- Enhanced to have new feature to unlink principal relationship.
- Enhanced to improves principal card validation.
- Enhanced to group principal and supplementary card points history by date.

9/10/2013 6:02 PM Justin
- Bug fixed on system allow user to set principal as others sub card.

9/13/2013 10:54 AM Fithri
- allow key-in issue date & expiry date when add member (if config is on)
- HQ can change issue branch when add & edit member

3:01 PM 9/26/2013 Justin
- Bug fixed on system will always update card no, issue and expiry date become null whenever editing membership.

4/18/2014 10:57 AM Justin
- Bug fixed on field "added" does not specify from which table.

5/21/2014 10:57 AM Justin
- Bug fixed on member verification that shows different result as notification.

8/22/2014 2:41 PM Justin
- Enhanced to load GST list by picking up GST type equal to "supply" only.
- Enhanced to have config checking on GST list.

7/28/2015 11:26 AM Joo Chia
- Fix to check against config(if set) for mandatory required fields in add new member, update, and member verification.

8/3/2015 2:26 PM Joo Chia
- Fix config required fields checking condition.

8/11/2015 4:38 PM Andy
- Fix validation error.

8/11/2015 6:11 PM Andy
- Fix membership type bug.

8/12/2015 12:46 PM Andy
- Fix verify bug.

11/19/2015 10:30 AM Qiu Ying
- auto resize image for membership ic.

5/4/2016 3:13 PM Andy
- Fix search member should filter nric first, then only card no.

1/23/2017 3:19 PM Andy
- Enhance to return error when found renew/issue/expiry date more than 2037-12-31.

2/7/2017 10:58 AM Andy
- Add checking to not allow issue date to same or over expiry date.

8/23/2018 2:55 PM Justin
- Fixed if change nric to start with "0" will cause the renewal and points history missing.

12/10/2018 11:03 AM Andy
- Hide "Always Print Full Tax Invoice" and "GST Type" when no config.enable_gst

7/2/2019 10:29 AM Andy
- Fixed member Favourite Product cannot get the data if member changed card_no.
- Enhanced member favourite product to try load from pregen cache data.

10/25/2019 4:26 PM William
- Enhanced to add "Remark" to membership.

11/12/2019 5:35 PM William
- Fixed bug issue branch selection will only show active branch.

11/21/2019 9:21 AM William
- Fix bug membership parent nric not update when nric change.
- Added checking to not allow duplicate email.

11/26/2019 11:09 AM William
- Change the date format of dob to display on "Member Points & History" screen.

1/2/2020 5:02 PM William
- Enhanced to insert "membership_guid" field for membership and membership_history table.

1/18/2021 1:20 PM William
- Enhanced to add column "pmr" to membership.

1/30/2020 5:17 Andy
- Increased maintenance checking to v438.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "Membership");
$maintenance->check(438);

$form = array();
$form['nric'] = preg_replace("/[^A-Z0-9]/", "", strtoupper(strval($_REQUEST['nric'])));

define('VALID_CARD_REMARK',"('N','L','R','LR','UC','C','ER')");

//print_r($_REQUEST);
if (isset($_REQUEST['a']))
{
	$errmsg = array();

	$a = strval($_REQUEST['a']);
	$t = strval($_REQUEST['t']);

	switch ($a)
	{	

		case 'ajax_validate_card':
		 	$card_no = $_REQUEST['card_no'];
			if (!validate_card($card_no, $errmsg))
				print $errmsg;
			else
				print "OK";
			exit;
			
		case 'renew_card':
			$card_no = $_REQUEST['nric'];
			$newexpiry = ms(dmy_to_sqldate($_REQUEST['renew_expiry_date']));
			$date=date("Y-m-d", strtotime($newexpiry));
			
			$err = date_validate($_REQUEST['renew_expiry_date'], "Expiry", "", true);
			
			if($err){
				$smarty->assign("tab_desc", "update");
				$smarty->assign("tab", 3);
				$smarty->assign("err", $err);
				show_info();
				exit;
			}
			
			$con->sql_query("select card_no from membership where nric=".ms($form['nric']));
			$mem = $con->sql_fetchrow();
			if ($mem)
			{
				$con->sql_query("update membership set next_expiry_date=$newexpiry, terminated_date = '' where nric=".ms($form['nric'])) or die(mysql_error());
				if ($con->sql_affectedrows()>0)
				{
					$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, added, m_type) select membership_guid, nric, ".ms($mem['card_no']).", ".mi($sessioninfo['branch_id']).", card_type, issue_date, $newexpiry, 'R', $sessioninfo[id], CURRENT_TIMESTAMP, m_type from membership_history where nric=".ms($form['nric'])." and remark in ('N','L','R','LR', 'C') order by issue_date desc, expiry_date desc limit 1");
					log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Renew Card ' . $form['nric'] ." => $mem[card_no]");
					$smarty->assign("msg","Updated");
				}
			}
			show_info();
			exit;
		case 'adjust_point':
			$rs = $con->sql_query("select * from membership where nric = ".ms($form['nric'])) or die(mysql_error());
			if ($con->sql_numrows($rs)>0)
			{
				$r = $con->sql_fetchrow($rs);
				$form['membership_guid'] = $r['membership_guid'];
				$form['card_no'] = $r['card_no'];
				$form['branch_id'] = $sessioninfo['branch_id'];;
				$form['date'] = "CURRENT_TIMESTAMP";
				$form['points'] = mf($_REQUEST['points']);
				$form['remark'] = $_REQUEST['remark'];
				$form['type'] = 'ADJUST';
				$form['user_id'] = $sessioninfo['id'];
				$con->sql_query("insert into membership_points ".mysql_insert_by_field($form)) or die(mysql_error());
				if ($con->sql_affectedrows()>0)
				{
					$con->sql_query("update membership set points = points + ".mf($form['points']).", points_update = ".ms($form['date'])." where nric = ".ms($form['nric']));
				}
				log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Adjust point for ' . $form['nric'] ." => $form[card_no]");
				$smarty->assign("msg","Updated");
			}
			else
			print "<script>alert(\"".$LANG['MEMBERSHIP_NRIC_NOT_IN_DATABASE']."\");</script>";
			
			show_info();
		exit;	
		case 'new_card':
			$card_no = $_REQUEST['card_no'];
			if (!validate_card($card_no, $err, $card_type))
			{
				print "<script>alert(\"".$err."\");</script>";
			}
      		else
      		{
      			$d1 = dmy_to_sqldate($_REQUEST['issue_date']);
      			$d2 = dmy_to_sqldate($_REQUEST['expiry_date']);
				
				$err = date_validate($_REQUEST['issue_date'], "Issue", "", true);
				$err1 = date_validate($_REQUEST['expiry_date'], "Expiry", "", true);
				
				if(!$err){
					if(strtotime($d2) < strtotime($d1)) $err[] = sprintf($LANG['MEMBERSHIP_DATE_RANGE_INVALID'], $_REQUEST['issue_date'], $_REQUEST['expiry_date']);
				}
				
				if($err1) $err = array_merge($err, $err1);
				
				if($err){
					$smarty->assign("tab_desc", "update");
					$smarty->assign("tab", 3);
					$smarty->assign("err", $err);
					show_info();
					exit;
				}
				
      			$con->sql_query("update membership set card_no = ".ms($card_no,true).", issue_date=".ms($d1).", next_expiry_date=".ms($d2).", terminated_date = '' where nric=".ms($form['nric'])) or die(mysql_error());
				if ($con->sql_affectedrows()>0)
				{
					$upd['nric'] = $_REQUEST['nric'];
					$upd['membership_guid'] = $appCore->newGUID();
					$upd['card_no'] = $_REQUEST['card_no'];
					$upd['branch_id'] = $sessioninfo['branch_id'];
					$upd['user_id'] = $sessioninfo['id'];
					$upd['card_type'] = $card_type;
					$upd['issue_date'] = $d1;
					$upd['expiry_date'] = $d2;
					$upd['remark'] = 'N';
					$upd['added'] = 'CURRENT_TIMESTAMP';
					$m_type = get_member_type($_REQUEST['card_no'], "pattern");
					if(!$m_type) $m_type = "member1";
					$upd['m_type'] = $m_type;
	        		$con->sql_query("insert into membership_history ".mysql_insert_by_field($upd)) or die(mysql_error());
					//(nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id) values (nric, ".ms($card_no).", branch_id, '$card_type', issue_date, expiry_date, 'C', $sessioninfo[id] from membership_history where nric=".ms($form['nric'])." and remark in ('N','L','R','LR') order by issue_date desc, expiry_date desc limit 1");
					log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'New Card ' . $form['nric'] ." => $card_no");
				}
			}
	      	show_info();
			exit;

		case 'upgrade_card':
	        if (!privilege('MEMBERSHIP_TOPEDIT'))
			{
				print "<script>alert(\"".(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_TOPEDIT', BRANCH_CODE))."\");</script>";
				break;
			}
			
			$card_no = $_REQUEST['card_no'];
			if (!validate_card($card_no, $err, $card_type))
			{
				print "<script>alert(\"".$err."\");</script>";
			}
      		elseif(!validate_card_upgrade($card_no, $err)){ // check whether can be upgraded or not
				print "<script>alert(\"".$err."\");</script>";
			}else
      		{
				// check if we have history to copy
				$con->sql_query("select * from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
				if ($con->sql_numrows()<=0)
				{
				    print "<script>alert(\"No valid history for Change Card (Please use New Member Card).\");</script>";
				}
				else
				{
					$con->sql_query("update membership set card_no = ".ms($card_no,true)." where nric=".ms($form['nric']));
					if ($con->sql_affectedrows()>0)
					{
						$m_type = get_member_type($card_no, "pattern");
						if(!$m_type) $m_type = "member1";
			        	$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, added, m_type) select membership_guid, nric, ".ms($card_no).", $sessioninfo[branch_id], ".ms($card_type).", issue_date, expiry_date, 'UC', $sessioninfo[id], CURRENT_TIMESTAMP, ".ms($m_type)." from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
						log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Upgrade Card ' . $form['nric'] ." => $card_no");
					}
				}
			}
	      	show_info();
			exit;
			
		case 'change_card':
	        if (!privilege('MEMBERSHIP_TOPEDIT'))
			{
				print "<script>alert(\"".(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_TOPEDIT', BRANCH_CODE))."\");</script>";
				break;
			}
			
			$card_no = $_REQUEST['card_no'];
			if (!validate_card($card_no, $err, $card_type))
			{
				print "<script>alert(\"".$err."\");</script>";
			}
      		else
      		{
				// check if we have history to copy
				$con->sql_query("select * from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
				if ($con->sql_numrows()<=0)
				{
				    print "<script>alert(\"No valid history for Change Card (Please use New Member Card).\");</script>";
				}
				else
				{
					$con->sql_query("update membership set card_no = ".ms($card_no,true)." where nric=".ms($form['nric']));
					if ($con->sql_affectedrows()>0)
					{
						$m_type = get_member_type($card_no, "pattern");
						if(!$m_type) $m_type = "member1";
			        	$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, added, m_type) select membership_guid, nric, ".ms($card_no).", $sessioninfo[branch_id], ".ms($card_type).", issue_date, expiry_date, 'C', $sessioninfo[id], CURRENT_TIMESTAMP, ".ms($m_type)." from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
						log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Change Card ' . $form['nric'] ." => $card_no");
					}
				}
			}
	      	show_info();
			exit;
			
	    case 'ajax_block':
	        if (!privilege('MEMBERSHIP'))
				fail(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP', BRANCH_CODE));

			if ($_REQUEST['reason'] == 'others')
			    $reason = $_REQUEST['reason_other'];
			else
			    $reason = $_REQUEST['reason'];
			
			$block_date = date("Y-m-d H:m:i");
			$con->sql_query("update membership set blocked_by = $sessioninfo[id], blocked_reason = ".ms($reason).", blocked_date = ".ms($block_date)." where nric=".ms($form['nric']));
			
			// add to membership history
			$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, action_date, action_reason, added, m_type) select membership_guid, nric, card_no, $sessioninfo[branch_id], card_type, issue_date, expiry_date, 'I', $sessioninfo[id], ".ms($block_date).", ".ms($reason).", CURRENT_TIMESTAMP, m_type from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
			
			log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Blocked Membership ' . $form['nric']);
			print sprintf($LANG['MEMBERSHIP_BLOCKED'], $form['nric']);
	        exit;
	        
	    case 'ajax_unblock':
	        if (!privilege('MEMBERSHIP_UNBLOCK'))
				fail(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_UNBLOCK', BRANCH_CODE));

			$con->sql_query("update membership set blocked_by = 0, blocked_date=0 where nric=".ms($form['nric']));
			
			// add to membership history
			$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, added, m_type) select membership_guid, nric, card_no, $sessioninfo[branch_id], card_type, issue_date, expiry_date, 'A', $sessioninfo[id], CURRENT_TIMESTAMP, m_type from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
			
			log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Un-blocked Membership ' . $form['nric']);
			//2/29/2008 6:01:04 PM gary -change block msg to un-block.
			print sprintf($LANG['MEMBERSHIP_UNBLOCKED'], $form['nric']);
			
	        exit;
	        
		case 'i':
			show_info();
			exit;
			
		case 'add':	// add new member
			if (!$config['membership_allow_add_at_backend'])
			{
				header("Location: $_SERVER[PHP_SELF]?t=verify");
			}
			if(!privilege('MEMBERSHIP_ADD'))
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ADD', BRANCH_CODE), "/index.php");
				
			$con->sql_query("select id, code from branch where active=1 order by sequence,code");
			$smarty->assign("branch_list", $con->sql_fetchrowset());
			$con->sql_freeresult();
			
			// load gst list
			if($config['membership_pre_gst_list']){
				$gst_filter = " and code in ('".join("','", $config['membership_pre_gst_list'])."')";
			}

			$con->sql_query("select * from gst where active=1 and type='supply'".$gst_filter);
			$gst_list = $con->sql_fetchrowset();
			$con->sql_freeresult();
			$smarty->assign("gst_list", $gst_list);
			
			$smarty->assign("add_mode", true);
			$smarty->display("membership_data.tpl");
			exit;
			
			
		case 'u':	// update customer info
			//print_r($_REQUEST);
			$add_mode = isset($_REQUEST['add_mode']);			
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
			    //if ($t == 'verify')
			    //{
			        // if saved from verification page, return errors as popup
			        $s = "";
					foreach ($errmsg as $m)
					{
					    $s .= "- $m\\n";
					}
				    print "<script>alert('$s');</script>";
				//}
				//else
				//{
				//	print "<script>alert('$s');</script>";
				if ($t != 'verify' && !$add_mode)
				{
					$smarty->assign("errmsg", $errmsg);
					$form['nric'] = $_REQUEST['old_nric'];
					show_info();
					/*print_r($_REQUEST);
					$smarty->assign("update", 1);
					
					$smarty->assign("form", $form);
					$smarty->display("membership_data.tpl");*/
				}
				else
				{
					$con->sql_query("select id, code from branch where active=1 order by sequence,code");
					$smarty->assign("branch_list", $con->sql_fetchrowset());
					$con->sql_freeresult();
					
					$smarty->assign("add_mode", $add_mode);
					$smarty->assign("form", $form);
					$smarty->display("membership_data.tpl");
				}
				exit;
			}
			
			$upd = array();
			$upd['nric'] = $form['nric'];
			$upd['parent_nric'] = $form['principal_nric'];
			$upd['name'] = $form['name'];
			$upd['member_type'] = $form['member_type'];
			$upd['designation'] = $form['designation'];
			$upd['gender'] = $form['gender'];
			$upd['dob'] = $form['dob'];
			$upd['marital_status'] = $form['marital_status'];
			$upd['national'] = $form['national'];
			$upd['race'] = $form['race'];
			$upd['education_level'] = $form['education_level'];
			$upd['preferred_lang'] = $form['preferred_lang'];
			$upd['address'] = $form['address'];
			$upd['postcode'] = $form['postcode'];
			$upd['city'] = $form['city'];
			$upd['state'] = $form['state'];
			$upd['phone_1'] = $form['phone_1'];
			$upd['phone_2'] = $form['phone_2'];
			$upd['phone_3'] = $form['phone_3'];
			$upd['email'] = $form['email'];
			$upd['occupation'] = $form['occupation'];
			$upd['income'] = $form['income'];
			$upd['newspaper'] = serialize($form['newspaper']);
			$upd['other_vip_card'] = serialize($form['other_vip_card']);
			$upd['radio_station'] = serialize($form['radio_station']);
			$upd['credit_card'] = serialize($form['credit_card']);
			$upd['recruit_by'] = mi($form['recruit_by']);
			$upd['remark'] = $form['remark'];
			
			if($config['enable_gst']){
				$upd['print_full_tax_invoice'] = mi($form['print_full_tax_invoice']);
				$upd['gst_type'] = mi($form['gst_type']);
			}			
			
			if ($add_mode && $config['membership_add_member_can_issue_card'] && $config['membership_auto_verify_member']) {
				$upd['card_no'] = $form['add_card_no'];
				$upd['issue_date'] = dmy_to_sqldate($form['issue_date']);
				$upd['next_expiry_date'] = dmy_to_sqldate($form['expiry_date']);
			}
			
			
			if($config['membership_enable_staff_card'] && privilege('MEMBERSHIP_UPDATE_STAFF_TYPE')){	// need privilege to update staff type
				$upd['staff_type'] = $form['staff_type'];
			}
			
			if($config['membership_pmr']){
				$upd['pmr'] = $form['pmr'];
			}
			
			if ($add_mode)
			{
				$new_guid = $appCore->newGUID();
				$upd['membership_guid'] = $new_guid;
				$upd['apply_branch_id'] = ($form['apply_branch_id']) ? $form['apply_branch_id'] : $sessioninfo['branch_id'];
				
				// do auto verify while found config
				if($config['membership_auto_verify_member']){
					$upd['verified_by'] = $sessioninfo['id'];
					$upd['verified_date'] = "CURRENT_TIMESTAMP";
				}
				
				/*
				print '<pre>';
				print_r($upd);
				print '</pre>';
				print("insert into membership ".mysql_insert_by_field($upd));
				die;
				*/
				
				$con->sql_query("insert into membership ".mysql_insert_by_field($upd));
				
				if($config['membership_extra_info']){
					$extra_info = array();
					foreach($config['membership_extra_info'] as $col=>$info){
						$extra_info[$col] = $form[$col];
					}
					$extra_info['nric'] = $form['nric'];
					$extra_info['added'] = "CURRENT_TIMESTAMP";
						
					$con->sql_query("insert into membership_extra_info ".mysql_insert_by_field($extra_info));
				}
				
				
				if ($config['membership_add_member_can_issue_card'] && $config['membership_auto_verify_member']) {
					$upd = array();
					$upd['membership_guid'] = $new_guid;
					$upd['nric'] = $form['nric'];
					$upd['card_no'] = $form['add_card_no'];
					$upd['branch_id'] = ($form['apply_branch_id']) ? $form['apply_branch_id'] : $sessioninfo['branch_id'];
					$upd['user_id'] = $sessioninfo['id'];
					
					$card_type = '';
					foreach($config['membership_cardtype'] as $type=>$ct)
					{
						if (preg_match($ct['pattern'], $upd['card_no']))
						{
							$card_type = $type;
							break;
						}
					}
					
					$upd['card_type'] = $card_type;
					$upd['issue_date'] = dmy_to_sqldate($form['issue_date']);
					$upd['expiry_date'] = dmy_to_sqldate($form['expiry_date']);
					$upd['remark'] = 'N';
					$upd['added'] = 'CURRENT_TIMESTAMP';
					$m_type = get_member_type($upd['card_no'], "pattern");
					if(!$m_type) $m_type = "member1";
					$upd['m_type'] = $m_type;
	        		$con->sql_query("insert into membership_history ".mysql_insert_by_field($upd)) or die(mysql_error());
					log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'New Card ' . $form['nric'] ." => ".$upd['card_no']);
				}
				
				log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Add Membership ' . $form['nric']);
				// saved. back to front page
				print "<script>alert('$LANG[MEMBERSHIP_DATA_UPDATED]');</script>";
				print "<script>parent.window.document.location='$_SERVER[PHP_SELF]?t=update'</script>";
			} 
			else
			{
				$upd['apply_branch_id'] = ($form['apply_branch_id']) ? $form['apply_branch_id'] : $sessioninfo['branch_id'];
				
				// store basic info
				$con->sql_query("update membership set ".mysql_update_by_field($upd)." where nric = " . ms($form['old_nric']));

				if($config['membership_extra_info']){
					$extra_info = array();
					if($form['old_nric'] !== $form['nric']) $extra_info['nric'] = $form['nric'];
					foreach($config['membership_extra_info'] as $col=>$info){
						$extra_info[$col] = $form[$col];
					}
					
					$q1 = $con->sql_query("select * from membership_extra_info where nric = ".ms($form['old_nric']));
					
					if($con->sql_numrows($q1) > 0){
						$extra_info['last_update'] = "CURRENT_TIMESTAMP";
						$con->sql_query("update membership_extra_info set ".mysql_update_by_field($extra_info)." where nric = ".ms($form['old_nric']));
					}else{
						$extra_info['nric'] = $form['nric'];
						$extra_info['added'] = "CURRENT_TIMESTAMP";
						$con->sql_query("insert into membership_extra_info ".mysql_insert_by_field($extra_info));
					}
					$con->sql_freeresult($q1);
				}
				
				// IC changed
				$changes = "";
				if ($form['old_nric'] !== $form['nric'])
				{
					$con->sql_query("update membership_history set nric = " . ms($form['nric']) . " where nric = " . ms($form['old_nric']));
					$con->sql_query("update membership_points set nric = " . ms($form['nric']) . " where nric = " . ms($form['old_nric']));
					$con->sql_query("update membership set parent_nric = ". ms($form['nric']) .  " where parent_nric = " . ms($form['old_nric']));
					$changes = "\nNRIC $form[old_nric]->$form[nric]";
					
					// update name for photo to use new nric
					if($form['nric']){
						$hurl = get_branch_file_url($r['apply_branch_code'], $r['icfile_ip']);
						if($hurl) $hurl."/";
						if(is_dir($_SERVER['DOCUMENT_ROOT']."/".$hurl.$config['scanned_ic_path'])){
							$old_file_path = $_SERVER['DOCUMENT_ROOT']."/".$hurl.$config['scanned_ic_path']."/$form[old_nric].JPG";
							if(file_exists($old_file_path)){
								$new_file_path = $_SERVER['DOCUMENT_ROOT']."/".$hurl.$config['scanned_ic_path']."/$form[nric].JPG";
								rename($old_file_path, $new_file_path);
								chmod($new_file_path, 0777);
							}
						}
					}
				}
				
				// delete to recalculate point or quota
				if($form['dob'] != $form['old_dob'] || ($config['membership_enable_staff_card'] && privilege('MEMBERSHIP_UPDATE_STAFF_TYPE') && ($form['staff_type'] != $form['old_staff_type']))){
					$con->sql_query("delete from tmp_membership_points_trigger where card_no in (select card_no from membership_history where nric=".ms($form['nric']).")");
					$con->sql_query("update membership set points_changed=1 where nric = ".ms($form['nric']));
				}
				
				if ($form['old_nric']!==$form['nric'] || $form['old_name']!=$form['name'])
				{
					// IC or Name changed, copy the last info and generate a history 
					$con->sql_query("insert into membership_history (membership_guid, nric, card_no, branch_id, card_type, issue_date, expiry_date, remark, user_id, added, m_type) select membership_guid, nric, card_no, $sessioninfo[branch_id], card_type, issue_date, expiry_date, 'U', $sessioninfo[id], CURRENT_TIMESTAMP, m_type from membership_history where nric=".ms($form['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
				}

				if($config['membership_data_use_custom_field']['principal_card']){
					$card_no_list = array();
					
					if($form['old_principal_nric']!=$form['principal_nric']){
						if($form['old_principal_nric']){
							$con->sql_query("select card_no from membership where nric=".ms($form['old_principal_nric']));
							$card_no_list[] = $con->sql_fetchfield(0);
							$con->sql_freeresult();
						}
						if($form['principal_nric']){
							$con->sql_query("select card_no from membership where nric=".ms($form['principal_nric']));
							$card_no_list[] = $con->sql_fetchfield(0);
							$con->sql_freeresult();
						}
						$con->sql_query("select card_no from membership where nric=".ms($form['old_nric']));
						$card_no_list[] = $con->sql_fetchfield(0);
						$con->sql_query("select card_no from membership where nric=".ms($form['nric']));
						$card_no_list[] = $con->sql_fetchfield(0);
					}
					
					if(count($card_no_list)>0){
						$con->sql_query("delete from tmp_membership_points_trigger where card_no in (\"".join("\",\"", $card_no_list)."\")");
						$con->sql_query("update membership set points_changed=1 where nric in (".ms($form['old_nric']).",".ms($form['nric']).")");
					}
				}
				
				foreach (preg_split("/\|/", $form["changed_fields"]) as $ff)
				{
					// strip array
					$ff = preg_replace("/\[.*\]/", '', $ff);
					if ($ff != "") $uqf[$ff] = 1;
				}
				if ($uqf) $changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

				log_br($sessioninfo['id'], 'MEMBERSHIP', 0, 'Membership update information (with Approval) ' . $form['nric'] . $changes);

				// if saved from verification page, do not redirect
				if ($t != 'verify')
				{
					// saved. back to front page
					print "<script>alert('$LANG[MEMBERSHIP_DATA_UPDATED]');</script>";
					print "<script>parent.window.document.location='$_SERVER[PHP_SELF]?t=update'</script>";
    			}
				else
				{
				    // callback approval action
					print "<script>alert('$LANG[MEMBERSHIP_DATA_UPDATED_AND_APPROVED]');</script>";
					print "<script>parent.window.opener.do_approve('$form[nric]'); parent.window.close();</script>";
    			}
			}
			exit;
		case 'v':
		    // let it flow thru, approval process is done below
		    break;
		case 'ajax_get_redemption_history':
		    ajax_get_redemption_history();
		    exit;
		case 'add_ic_photo':
			$nric = $_REQUEST['nric'];
			if(!$nric) die("No NRIC was found");
			$hurl = get_branch_file_url($r['apply_branch_code'], $r['icfile_ip']);
			if($hurl) $hurl."/";
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/".$config['scanned_ic_path'])){
				mkdir($_SERVER['DOCUMENT_ROOT']."/".$config['scanned_ic_path'], 0777, true);
			}

			$filepath = $_SERVER['DOCUMENT_ROOT']."/".$config['scanned_ic_path']."/$nric.JPG";
			
			move_uploaded_file($_FILES['ic_photo']['tmp_name'], $filepath);
			resize_photo($filepath, $filepath);
			$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $filepath);
			$urlenc = urlencode($imagep);

			print "<div id=\"ic_photo_ret\">
<img width=200 align=absmiddle src=\"$imagep?time=".time()."\" style=\"cursor:pointer; border:1px solid #999; padding:8px; background-color:#fff;cursor:pointer;z-index:100\" onclick=\"show_context_menu(this);\" title=\"Click to view full size or update photo\"><br />Click to view full size / Update photo
</div><script>parent.window.ic_upload_callback(document.getElementById('ic_photo_ret'), '<img src=\"$imagep?time=".time()."\">');</script>";

			log_br($sessioninfo['id'], 'MEMBERSHIP', 0, "Updated IC Image Photo (NRIC:".$nric.")");
			exit;
		case 'user_verify':
			$con->sql_query("select * from user where u=".ms($_REQUEST['username']));
			$verify = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			if($verify['id']) print $verify['id'];
			else print "User Not Found.";
		    exit;
		case 'principal_nric_verify':
			principal_nric_verify();
			exit;
		case 'principal_nric_unlink':
			principal_nric_unlink();
			exit;
		case 'ajax_get_auto_redemption_history':
			get_auto_redemption_history(true);
			exit;
		case 'update_membership_extra_info_structure':
			update_membership_extra_info_structure();
			exit;
		case 'member_points_changed':
			member_points_changed();
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
   			print_r($_REQUEST);
			exit;
	}
}

$con->sql_query("select id, code, ip from branch order by sequence,code");
$branches = $con->sql_fetchrowset();
$smarty->assign("branches", $branches);

switch ($_REQUEST['t'])
{
	case 'verify' :
	    // approve button clicked
	    if(!privilege('MEMBERSHIP_VERIFY'))
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_VERIFY', BRANCH_CODE), "/index.php");
		if ($_REQUEST['a'] == 'v')
		{
			$con->sql_query("update membership set verified_by = $sessioninfo[id], verified_date = CURDATE() where nric=" . ms($form['nric']) ." and verified_by = 0");
		}
		
		$filter = array();
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['name'])
			$filter[] = " name = '' ";
		
		if ($config['membership_type'] || $config['membership_required_fields']['member_type'])
			$filter[] = " member_type = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['designation'])
			$filter[] = " designation = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['gender'])
			$filter[] = " gender = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['dob'])
			$filter[] = " dob = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['marital_status'])
			$filter[] = " marital_status not in (0,1) ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['national'])
			$filter[] = " national = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['race'])
			$filter[] = " race = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['education_level'])
			$filter[] = " education_level = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['preferred_lang'])
			$filter[] = " preferred_lang = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['address'])
			$filter[] = " address = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['postcode'])
			$filter[] = " postcode = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['city'])
			$filter[] = " city = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['state'])
			$filter[] = " state = '' ";
		
		if ($config['membership_required_fields']['phone_1'])
			$filter[] = " phone_1 = '' ";
		
		if ($config['membership_required_fields']['phone_2'])
			$filter[] = " phone_2 = '' ";
		
		if ($config['membership_required_fields']['phone_3'])
			$filter[] = " phone_3 = '' ";
		
		if ($config['membership_required_fields']['email'])
			$filter[] = " email = '' ";
		
		if ($config['membership_required_fields']['recruit_by'])
			$filter[] = " recruit_by = '' ";
		
		if (!$config['membership_required_fields'] || $config['membership_required_fields']['occupation'])
			$filter[] = " occupation = '' ";
		
		if ($config['membership_required_fields']['income'])
			$filter[] = " income = '' ";		
		
		if ($filter) {
			$mbr_filter = "and not (".join(' or ', $filter).")";
			$bad_filter = "or (".join(' or ', $filter).")";
		} else {
			$mbr_filter = "";
			$bad_filter = "";
		}
			
		//print $mbr_filter;
		//print $bad_filter;
		
	    // search by name or nric
	    if (isset($_REQUEST['name']) || isset($_REQUEST['nric']))
	    {
	        if (isset($_REQUEST['name']))
				$ff = 'name';
			else
				$ff = 'nric';
			
			if (BRANCH_CODE == 'HQ')
				$branch_check = '';
			else
				$branch_check = "apply_branch_id = $sessioninfo[branch_id] and ";
				 
            $nn = strtoupper(preg_replace("/[^a-z0-9]/i", "", ($_REQUEST[$ff])));
			// sort by name
			//$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where $branch_check $ff like '$nn%' and terminated_date = 0 and verified_by = 0 and blocked_by = 0 and  not (dob = '' or address = '') order by $ff limit 100");
			$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where $branch_check $ff like '$nn%' and terminated_date = 0 and verified_by = 0 and blocked_by = 0 $mbr_filter order by $ff limit 100");
			$smarty->assign("members", $con->sql_fetchrowset());
			$smarty->assign("total", $con->sql_numrows());
			$total = $con->sql_numrows();

			//$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where $branch_check $ff like '$nn%' and terminated_date = 0 and verified_by = 0 and (blocked_by > 0 or (dob = '' or address = '')) order by $ff limit 100");
			$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where $branch_check $ff like '$nn%' and terminated_date = 0 and verified_by = 0 and (blocked_by > 0 $bad_filter) order by $ff limit 100");
			$smarty->assign("members_bad", $con->sql_fetchrowset());
			$smarty->assign("total_bad", $con->sql_numrows());
			$total_bad = $con->sql_numrows();
			
			if ($total>=100)
		    {
		        print "<br>* There are too many records in the result, only the first 100 are displayed *";
			}
		}
		else
		{
			$bid = intval($_REQUEST['branch_id']);
			foreach ($branches as $b)
			{
				if ($bid == 0 && $b['code'] == BRANCH_CODE)
				{
					$smarty->assign("ic_branch_ip", $b['ip']);
					$bid = $b['id'];
				    $_REQUEST['branch_id'] = $bid;
					break;
				}
				elseif ($bid == $b['id'])
				{
					$smarty->assign("ic_branch_ip", $b['ip']);
					break;
				}
			}

			if (isset($_REQUEST['dt']))
				$dt_filter = "and issue_date = ".ms($_REQUEST['dt']);
			/*else
				$dt = 'CURDATE()';*/
		
			//$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where terminated_date = 0 and blocked_by = 0 and verified_by = 0 and apply_branch_id = $bid and not (dob = '' or address = '') $dt_filter order by card_no");
			$con->sql_query($sql1 = "select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where terminated_date = 0 and blocked_by = 0 and verified_by = 0 and apply_branch_id = $bid $mbr_filter $dt_filter order by card_no");
			//print $sql1;
			$smarty->assign("members", $con->sql_fetchrowset());
			$smarty->assign("total", $con->sql_numrows());
			$total = $con->sql_numrows();

			//$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where verified_by = 0 and terminated_date = 0 and apply_branch_id = $bid and (blocked_by > 0 or (dob = '' or address = '')) $dt_filter order by card_no");
			$con->sql_query("select nric, name, dob, gender, address, card_no, issue_date, blocked_by, blocked_reason, blocked_date from membership where verified_by = 0 and terminated_date = 0 and apply_branch_id = $bid and (blocked_by > 0 $bad_filter) $dt_filter order by card_no");
			$smarty->assign("members_bad", $con->sql_fetchrowset());
			$smarty->assign("total_bad", $con->sql_numrows());
			$total_bad = $con->sql_numrows();
		}
	    
		// if flow from verify, just return the remaining records
		if ($a == 'v')
		{
		    print "<font color=blue>Total unverified record(s): ".($total+$total_bad)."</font>";
		    if ($total_bad > 0) print "<br><font color=red>Incomplete record(s): $total_bad [<a href=\"#incomplete\">click here view</a>]</font>";
		}
		else
		{
			if ($total + $total_bad <= 0)
			{
			    if (!isset($_REQUEST['name']) && !isset($_REQUEST['nric']))
			    {
					// no records, show timeline
					$con->sql_query("select count(nric) as total, DATE_FORMAT(issue_date, '%Y-%m-%d') as issue_date, DATE_FORMAT(issue_date, '%Y%m%d') as issue_date2 from membership where issue_date and terminated_date = 0 and verified_by = 0 and apply_branch_id = $bid group by issue_date order by issue_date");
					//
					if ($con->sql_numrows()>0)
					{
						$output = "<div id=unv>No record on selected date. Other unverified records are found at: <ul>\n";
						while ($r = $con->sql_fetchrow())
						{
							$output.="<li> <a href=\"javascript:void(reload_list('$r[issue_date2]'))\">$r[issue_date]</a> ($r[total])</li>";
						}
						$output.="</ul></div>";
					}
					else
					{
						$output.="<div id=unv>No unverified record for selected branch.</div>\n";
					}
				}
				else
				{
					$output = 'NO RECORD';
				}
				$smarty->assign("no_record", $output);
			}
			
			if (isset($_REQUEST['dt']) || isset($_REQUEST['name']) || isset($_REQUEST['nric']))
			{
				$smarty->display("membership_verify_table.tpl");
			}
			else
			{
				$smarty->display("membership_verify.tpl");
			}
		}
		break;

	default:
	        //checking privilege for cancel_history,history and update.
			if(!privilege('MEMBERSHIP_EDIT') && !privilege('MEMBERSHIP_ADD'))
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_EDIT', BRANCH_CODE), "/index.php");
			$smarty->display("membership_home.tpl");
}

function show_info()
{
	global $smarty, $con, $LANG, $form, $config, $sessioninfo, $pos_config, $appCore;
	//print_r($pos_config);
	
	$con->sql_query("select nric, card_no from membership where nric=".ms($form['nric']));
	$found_mem = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$found_mem){
		// get data by IC or CARD number
		$check_card_no = $form['nric'];
		if($config['membership_use_card_prefix']){
			if(!preg_match($config['membership_valid_cardno'], $check_card_no)){
				$check_card_no = get_membership_card_prefix($sessioninfo['branch_id']).$check_card_no;
			}
		}
		
		if (preg_match($config['membership_valid_cardno'], $check_card_no)) //substr($form['nric'],0,2) == 'AK')
		{
			$con->sql_query("select nric from membership_history where card_no = ".ms($check_card_no)." and remark != 'CB'");
			$r = $con->sql_fetchrow();
			if (!$r)
			{
				// no such number
				//$smarty->display("membership_home.tpl");
				//print "<script>alert('$LANG[MEMBERSHIP_CARD_NOT_IN_DATABASE]');</script>";
				//exit;
			}
			else
			{
				// use the nric and continue
				$form['nric'] = $r['nric'];
			}
		}
	}	
	
	$con->sql_query("select membership.*, branch.code as apply_branch_code, branch.ip as icfile_ip, DATE_FORMAT(dob,'%Y') as dob_y, DATE_FORMAT(dob,'%m') as dob_m, DATE_FORMAT(dob,'%d') as dob_d, DATE_FORMAT(dob, '%Y-%m-%d') as dob2, u.u as recruit_name 
	from membership 
	left join branch on membership.apply_branch_id = branch.id 
	left join user u on u.id = membership.recruit_by where nric = " . ms($form['nric']));
	$r = $con->sql_fetchrow();
	if(!$r){
        $smarty->display("membership_home.tpl");
		print "<script>alert('$LANG[MEMBERSHIP_CARD_OR_NRIC_NOT_IN_DATABASE]');</script>";
		exit;
	}
	
	// extra info
	if($config['membership_extra_info']){
		$q1 = $con->sql_query("select * from membership_extra_info where nric = ".ms($form['nric']));

		if($con->sql_numrows($q1) > 0){
			$extra_info = $con->sql_fetchassoc($q1);
			$r = array_merge($r, $extra_info);
		}
		$con->sql_freeresult($q1);
	}

	// get quota info	
	if($config['membership_enable_staff_card'] && $r['staff_type']){	// is staff card
		// get latest max quota
		$con->sql_query("select * from mst_staff_quota where staff_type=".ms($r['staff_type']));
		$r['staff_quota_info'] = $con->sql_fetchassoc();
		$con->sql_freeresult();
	}
	
	// check t-option
	switch ($_REQUEST['t'])
	{
		case 'cancel_history':
   			//$con->sql_query("select mh.id, mh.nric, mh.card_no, mh.branch_id,branch.code as branch_code, mh.card_type, mh.issue_date, mh.expiry_date, mh.remark, user.u from membership_history mh left join branch on branch_id = branch.id left join user on user_id = user.id where nric=".ms($form['nric'])." order by issue_date, expiry_date");
			//if($con->sql_numrows()>1){
				
			// check points from current membership points
			$q1 = $con->sql_query("select sum(points) as points from membership_points where card_no = ".ms($_REQUEST['card_no'])." and nric = ".ms($_REQUEST['nric'])." group by card_no");
			$mp_points = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);

			if($mp_points['points'] > 0){
				print(sprintf($LANG['MEMBERSHIP_CB_ERROR'], $_REQUEST['card_no']));
				exit;
			}
			
			// check points from pos
			$curr_date = date("Y-m-d");
			$q2 = $con->sql_query("select sum(point) as points from pos where date = ".ms($curr_date)." and member_no = ".ms($_REQUEST['card_no'])." group by member_no");
			$pos_points = $con->sql_fetchrow($q2);
			$con->sql_freeresult($q2);
			
			if($pos_points['points'] > 0){
				print(sprintf($LANG['MEMBERSHIP_CB_ERROR'], $_REQUEST['card_no']));
				exit;
			}

			// change status to 'CB'
			$con->sql_query("update membership_history set remark = 'CB', user_id=".mi($sessioninfo['id'])." where nric=".ms($_REQUEST['nric'])." and branch_id=".mi($_REQUEST['branch_id'])." and id=".mi($_REQUEST['id'])."");
			// restore membership info to last valid card
			$con->sql_query("select * from membership_history where nric=".ms($_REQUEST['nric'])." and remark in ".VALID_CARD_REMARK." order by issue_date desc, id desc limit 1");
			$update = $con->sql_fetchrow();
			$con->sql_query("Update membership set card_no=".ms($update['card_no'],true).", issue_date=".ms($update['issue_date']).", next_expiry_date=".ms($update['expiry_date'])." where nric=".ms($_REQUEST['nric'])."");

			print "ok";

			/*}
			else
			{
				$msg = "Cannot delete last history.";
			}*/
			//header("Location: /membership.php?t=history&a=i&nric=".$_REQUEST['nric']."&msg=".$msg);
			exit;
		case 'history': // view history
			$con->sql_query("select * from membership where nric=".ms($form['nric'])." or card_no = ".ms($form['nric']));
			$data = $con->sql_fetchrow();

		    if(!$data['verified_date'] == '' && !privilege('MEMBERSHIP_EDIT'))
		    {
         		$smarty->display("membership_home.tpl");
				print "<script>alert('$LANG[MEMBERSHIP_UPDATE]');</script>";
				exit;
			}
             
			// get history info
			$rs = $con->sql_query("select mh.id, mh.nric, mh.card_no, mh.branch_id,branch.code as branch_code, mh.card_type, mh.issue_date, mh.expiry_date, mh.remark, user.u, mh.action_date, mh.action_reason, mh.added, mh.offline_id from membership_history mh left join branch on branch_id = branch.id left join user on user_id = user.id where nric=".ms($form['nric'])."  order by mh.issue_date, mh.expiry_date");
			$history = $con->sql_fetchrowset($rs);
			$con->sql_freeresult($rs);
			
			// get last renewal
			$q1 = $con->sql_query("select b.code as branch_code, mh.card_type, b.ip from membership_history mh left join branch b on mh.branch_id = b.id where mh.nric=".ms($form['nric'])." order by mh.expiry_date desc, mh.issue_date desc, mh.added desc limit 1");
			$tmp_history = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$r['card_type'] = $tmp_history['card_type'];
			$r['branch_code'] = $tmp_history['branch_code'];

			// get point history			
			$rs = $con->sql_query("select p.*, p.date as ord_date, date(p.date) as date, b.code, user.u as user
								   from membership_points p 
								   left join user on user.id = p.user_id
								   left join branch b on b.id = p.branch_id where p.nric=".ms($form['nric'])." order by ord_date desc");
			$tmp_point_history = $tmp_sub_point_history = $point_history = array();
			while($ph = $con->sql_fetchassoc($rs)){
				$tmp_point_history[] = $ph;
			}
			$con->sql_freeresult($rs);
			
			// get the sub card point history
			if($config['membership_data_use_custom_field']['principal_card']){
				$sub_point_history = array();
				$sub_nric_list = array();
				
				// loop all the sub card nric
				$q1 = $con->sql_query("select m.nric from membership m where m.parent_nric = ".ms($form['nric']));
				while($m = $con->sql_fetchassoc($q1)){	
					$sub_nric_list[] = $m['nric'];
				}
				$con->sql_freeresult($q1);
				
				if($sub_nric_list){	// got sub card
					$tmp_nric_list = array();
					foreach($sub_nric_list as $tmp_nric){
						$tmp_nric_list[] = ms($tmp_nric);
					}
					
					$rs = $con->sql_query("select p.*, p.date as ord_date, date(p.date) as date, b.code, user.u as user
										   from membership_points p 
										   left join user on user.id = p.user_id
										   left join branch b on b.id = p.branch_id where p.nric in (".join(',', $tmp_nric_list).") order by ord_date desc");
					while($ph = $con->sql_fetchassoc($rs)){
						$ph['is_subcard'] = 1;
						$tmp_sub_point_history[] = $ph;
					}
					$con->sql_freeresult($rs);
					unset($sub_nric_list);
				}

				if($tmp_sub_point_history){
					$tmp_point_history = array_merge($tmp_point_history, $tmp_sub_point_history);
					unset($tmp_sub_point_history);

					usort($tmp_point_history, "sort_date");
				}
			}

			$point_history = $tmp_point_history;
			unset($tmp_point_history);
			
			// quota history
			if($config['membership_enable_staff_card'] && $r['staff_type']){
				$quota_history_list = array();
				$q1 = $con->sql_query("select qh.* ,user.u as user, b.code
					from staff_quota_used_history qh 
					left join user on user.id = qh.user_id
					left join branch b on b.id = qh.branch_id
					where qh.nric=".ms($form['nric'])." order by qh.quota_date desc");
				while($qh = $con->sql_fetchassoc($q1)){
					$quota_history_list[] = $qh;
				}
				$con->sql_freeresult($q1);
				//print_r($quota_history_list);
				$smarty->assign('quota_history_list', $quota_history_list);
			}
			
			// product history
			if($history){
				// Get all card no this member used before
				$card_no_list = $appCore->memberManager->getMemberCardNoList($form['nric']);
				$str_card_no = join(',', array_map('ms', $card_no_list));
				
				if($card_no_list){
					// Get Data from pregen table
					$q_fav = $con->sql_query( "select tbl.card_no, si.sku_item_code, si.artno, si.mcode, si.receipt_description,  sum(tbl.qty) as qty, sum(tbl.amount + tbl.tax_amt) as price, max(tbl.date) as dt 
					from membership_fav_items tbl
					join sku_items si on tbl.sku_item_id = si.id
					where tbl.card_no in ($str_card_no)
					group by tbl.card_no, tbl.sku_item_id
					order by qty desc, price desc
					limit 100");
					if(!$con->sql_numrows($q_fav)){	// No Data from pregen table
						$con->sql_freeresult($q_fav);
						
						// Direct select from pos_items - Remove in future
						$q_fav = $con->sql_query("select member_no as card_no, sku_item_code, artno, mcode, barcode, receipt_description,  sum(qty) as qty, sum(price-discount-discount2) as price, max(pi.date) as dt 
						from pos_items pi
						left join pos on pos.branch_id=pi.branch_id and pos.counter_id = pi.counter_id and pos.date = pi.date and pos.id = pi.pos_id
						left join sku_items on sku_item_id = sku_items.id
						where pos.member_no in ($str_card_no) and pos.cancel_status=0 and pos.member_no is not null and pos.member_no != ''
						group by card_no, sku_item_id
						order by qty desc, price desc
						limit 100");
					}
					
					$product_history = array();
					while($ph = $con->sql_fetchassoc($q_fav)){
						$product_history[] = $ph;
					}
					$con->sql_freeresult($q_fav);

					$smarty->assign("product_history", $product_history);
				}
			}

			$smarty->assign("point_history", $point_history);
			$smarty->assign("history", $history);
			$smarty->assign("form", $r);
			//print "<pre>";
			//print_r($r);
			$smarty->display("membership_history.tpl");
			
			break;

		case 'update': // update information
		
			// branch list
			$con->sql_query("select id, code from branch  where active=1 order by sequence,code");
			$smarty->assign("branch_list", $con->sql_fetchrowset());
			
			// load gst list
			if($config['membership_pre_gst_list']){
				$gst_filter = " and code in ('".join("','", $config['membership_pre_gst_list'])."')";
			}

			$con->sql_query("select * from gst where active=1 and type='supply'".$gst_filter);
			$gst_list = $con->sql_fetchrowset();
			$con->sql_freeresult();
			$smarty->assign("gst_list", $gst_list);
			
			//cannot edit verified data if no membership edit privilege
			$con->sql_query("select *, u.u as recruit_name from membership left join user u on u.id = membership.recruit_by where nric=".ms($form['nric']));
			$data = $con->sql_fetchrow();

		    if(!$data['verified_date'] == '' && !privilege('MEMBERSHIP_EDIT'))
		    {
         		$smarty->display("membership_home.tpl");
				print "<script>alert('$LANG[MEMBERSHIP_UPDATE]');</script>";
				exit;
			}
			
			$r['recruit_name'] = $data['recruit_name'];
			$r['recruit_by'] = $data['recruit_by'];
			$r['newspaper'] = unserialize($r['newspaper']);
			$r['other_vip_card'] = unserialize($r['other_vip_card']);
			$r['radio_station'] = unserialize($r['radio_station']);
			$r['credit_card'] = unserialize($r['credit_card']);

			$hurl = get_branch_file_url($r['apply_branch_code'], $r['icfile_ip']);
			//$r['ic_path'] = "/file_tunnel.php?f=$hurl/$config[scanned_ic_path]/$r[nric].JPG";
			$r['ic_path'] = "$hurl/$config[scanned_ic_path]/$r[nric].JPG";

			if($_REQUEST['old_nric']){
				//$_REQUEST['old_nric'] = $r['nric'];
				$r = array_merge($r, $_REQUEST);
			}
			
			// get last renewal
			$con->sql_query("select b.code as branch_code, card_type, b.ip from membership_history mh left join branch b on mh.branch_id = b.id where mh.nric=".ms($form['nric'])." order by mh.expiry_date desc, mh.issue_date desc, mh.added desc limit 1");
			$history = $con->sql_fetchrow();
			$r['card_type'] = $history['card_type'];
			$r['branch_code'] = $history['branch_code'];

			if($config['membership_enable_extra_info']){
				$q1 = $con->sql_query("select * from membership_extra_info where nric = ".ms($form['nric']));
				
				if($con->sql_numrows($q1) > 0){
					$extra_info = $con->sql_fetchassoc($q1);
					$r = array_merge($r, $extra_info);
				}
				$con->sql_freeresult($q1);
			}
			
			$smarty->assign("update", 1);
			if($_REQUEST['from_list']) $r['from_list']=1;
			$smarty->assign("form", $r);
			$smarty->display("membership_data.tpl");
			break;

		case 'view': // view information only


			//cannot edit verified data if no membership edit privilege
			$con->sql_query("select * from membership where nric=".ms($form['nric']));
			$data = $con->sql_fetchrow();

			if (!$r)
			{
				// no such number
				$smarty->display("membership_home.tpl");
				print "<script>alert('$LANG[MEMBERSHIP_CARD_OR_NRIC_NOT_IN_DATABASE]');</script>";
				exit;
			}
			$r['newspaper'] = unserialize($r['newspaper']);
			$r['other_vip_card'] = unserialize($r['other_vip_card']);
			$r['credit_card'] = unserialize($r['credit_card']);

			$hurl = get_branch_file_url($r['apply_branch_code'], $r['icfile_ip']);
			//$r['ic_path'] = "/file_tunnel.php?f=$hurl/$config[scanned_ic_path]/$r[nric].JPG";
			$r['ic_path'] = "$hurl/$config[scanned_ic_path]/$r[nric].JPG";

			// get last renewal
			$con->sql_query("select b.code as branch_code, mh.card_type, b.ip from membership_history mh left join branch b on mh.branch_id = b.id where mh.nric=".ms($form['nric'])." order by mh.expiry_date desc, mh.issue_date desc, mh.added desc limit 1");
			$history = $con->sql_fetchrow();
			$r['card_type'] = $history['card_type'];
			$r['branch_code'] = $history['branch_code'];

			$smarty->assign("read_only", 1);
			if($_REQUEST['from_list'])$r['from_list']=1;
			$smarty->assign("form", $r);
			$smarty->display("membership_data.tpl");
			break;

		case 'verify':
			// branch list
			$con->sql_query("select id, code from branch where active=1 order by sequence,code");
			$smarty->assign("branch_list", $con->sql_fetchrowset());
		
			$hurl = get_branch_file_url($r['apply_branch_code'], $r['icfile_ip']);
			//$r['ic_path'] = "/file_tunnel.php?f=$hurl/$config[scanned_ic_path]/$r[nric].JPG";
            $r['ic_path'] = "$hurl/$config[scanned_ic_path]/$r[nric].JPG";
            
			$r['newspaper'] = unserialize($r['newspaper']);
			$r['other_vip_card'] = unserialize($r['other_vip_card']);
			$r['radio_station'] = unserialize($r['radio_station']);
			$r['credit_card'] = unserialize($r['credit_card']);

			$smarty->assign("form", $r);
			$smarty->display("membership_data_verify.tpl");
			exit;

		default:
			print "<h3>Unhandled Request</h3>";
			//print_r($_REQUEST);
			exit;
	}
}

function date_validate($date, $type, $check_older_date=false, $check_max_date=false){
	global $LANG;
	
	$curr_date = strtotime(date("Y-m-d"));
	$check_time = strtotime(dmy_to_sqldate($date));
	$err = array();
	
	if($check_older_date){
		$check_date = strtotime(date("Y-m-d", $check_time));

		if($curr_date > $check_date) $err[] = sprintf($LANG['MEMBERSHIP_DATE_INVALID'], $type, $date);
	}else{
		$check_date = date("d/m/Y", $check_time);
		
		if($check_date != $date) $err[] = sprintf($LANG['MEMBERSHIP_DATE_INVALID'], $type, $date);
	}
	
	if(!$err && $check_max_date){
		if($ret = is_exceed_max_mysql_timestamp($check_time))	$err[] = sprintf($LANG['DATE_CANNOT_OVER'], "$type Date",$ret['max_date']);
	}
	
	return $err;
}

/*function validate_newcard(&$form)
{
	global $LANG, $config;

	$errm = array();

	$form['charges'] = 0;

	if ($form['atype'] == '')
	{
		$errm[] = $LANG['MEMBERSHIP_ATYPE_EMPTY'];
	}
	if ($form['ctype'] == '')
	{
		$errm[] = $LANG['MEMBERSHIP_CTYPE_EMPTY'];
	}
	if ($form['nric'] == '')
	{
		// no NRIC, back straight to front page
		header("Location: $_SERVER[PHP_SELF]");
		exit;
	}
	if (!preg_match($config['membership_valid_cardno'], $form['card_no']))
	{
		$errm[] = $LANG['MEMBERSHIP_INVALID_CARD_NO'];
	}

	if ($errm) return $errm;

	// get application type
	$mm = split(",", $form['atype']);
	$form['remark'] = $mm[0];
	$form['duration'] = $mm[1];
	$form['charges'] += $mm[2];

	// get card type
	$mm = split(",", $form['ctype']);
	$form['card_type'] = $mm[0];
	$form['charges'] += $mm[1];

	// calculate next expiry date
	if ($form['expiry'] < time()) $form['expiry'] = time();
	$form['expiry_date'] = date("Ymd", strtotime($form['duration'], $form['expiry']));

	return $errm;

}
*/
function validate_data(&$form)
{
	global $LANG, $config, $con;

	$errm = array();

	$form['nric'] = preg_replace("/[^A-Z0-9]/", "", strtoupper(strval($form['nric'])));

	if ($form['apply_branch_id'] == '')
		$errm[] = $LANG['MEMBERSHIP_ABID_EMPTY'];
	if ((!$config['membership_required_fields'] && $form['name'] == '') || ($config['membership_required_fields']['name'] && $form['name'] == ''))
		$errm[] = $LANG['MEMBERSHIP_NAME_EMPTY'];
	if($config['membership_type']){
		if ((!$config['membership_required_fields'] && $form['member_type'] == '') || ($config['membership_required_fields']['member_type'] && $form['member_type'] == ''))
			$errm[] = $LANG['MEMBERSHIP_MTYPE_EMPTY'];
	}
	
	if ((!$config['membership_required_fields'] && $form['designation'] == '') || ($config['membership_required_fields']['designation'] && $form['designation'] == ''))
		$errm[] = $LANG['MEMBERSHIP_DEISGNATION_EMPTY'];
	if ((!$config['membership_required_fields'] && $form['gender'] == '') || ($config['membership_required_fields']['gender'] && $form['gender'] == ''))
		$errm[] = $LANG['MEMBERSHIP_GENDER_EMPTY'];
		
	if ($form['old_nric']!==$form['nric'] || $form['add_mode'])
	{
		// make sure the new ic is unique
		$con->sql_query("select card_no, nric from membership where nric = ".ms($form['nric']));
		if ($r=$con->sql_fetchrow())
		{
			$errm[] = $LANG['MEMBERSHIP_NRIC_IN_DATABASE'].(($r['card_no']) ? " ($r[card_no])" : "");
		}
	}
	
	if($form['add_mode']){
		if ($config['membership_add_member_can_issue_card'] && $config['membership_auto_verify_member']) {
			$issue_date = dmy_to_sqldate($form['issue_date']);
			$next_expiry_date = dmy_to_sqldate($form['expiry_date']);
			
			if($ret = is_exceed_max_mysql_timestamp(strtotime($issue_date)))	$errm[] = sprintf($LANG['DATE_CANNOT_OVER'], "Issue Date", $ret['max_date']);
			if($ret = is_exceed_max_mysql_timestamp(strtotime($next_expiry_date)))	$errm[] = sprintf($LANG['DATE_CANNOT_OVER'], "Expiry Date", $ret['max_date']);
			
			if(!$errm){
				if(strtotime($next_expiry_date) < strtotime($issue_date)){
					$errm[] = sprintf($LANG['MEMBERSHIP_DATE_RANGE_INVALID'], $issue_date, $next_expiry_date);
				}
			}
		}
	}
	
	if($form['nric'] == '')
		$errm[] = $LANG['MEMBERSHIP_NRIC_EMPTY'];


	if((!$config['membership_required_fields'] && (!$form['dob_d'] || !$form['dob_m'] || !$form['dob_y'])) || ($config['membership_required_fields']['dob'] && (!$form['dob_d'] || !$form['dob_m'] || !$form['dob_y'])))
		$errm[] = $LANG['MEMBERSHIP_DOB_EMPTY'];
	
	if(($form['dob_d'] && (!$form['dob_m'] || !$form['dob_y'])) || 
		($form['dob_m'] && (!$form['dob_d'] || !$form['dob_y'])) || 
		($form['dob_y'] && (!$form['dob_d'] || !$form['dob_m'])))
		$errm[] = $LANG['MEMBERSHIP_DOB_EMPTY'];

	if($config['membership_age_limit']) $age_limit = $config['membership_age_limit'];
	else $age_limit = 12;

	if($form['dob_d'] && $form['dob_m'] && $form['dob_y']){
		
		if ($form['dob_d'] > 31)
		{
			$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
		}
		elseif ($form['dob_m'] > 12)
		{
			$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
		}
		elseif ($form['dob_m'] == 2)
		{
			if ($form['dob_d'] > 29)
				$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
			elseif ($form['dob_d'] > 28 && $form['dob_y']%4 > 0)
				$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
		}
		
		if (($form['dob_d'] > 30 && ($form['dob_m'] == 4 || $form['dob_m'] == 6 || $form['dob_m'] == 9 || $form['dob_m'] == 11)) || strlen($form['dob_y']) < 4)
		{
			$errm[] = sprintf($LANG['MEMBERSHIP_DOB_INVALID'], "$form[dob_d]-$form[dob_m]-$form[dob_y]");
		}
		elseif (date("Y")-$form['dob_y']<$age_limit)
		{
			$errm[] = sprintf($LANG['MEMBERSHIP_AGE_INVALID'], $age_limit);
		}
		
	}
	
	$form['dob'] = sprintf("%04d%02d%02d", $form['dob_y'], $form['dob_m'], $form['dob_d']);

	if((!$config['membership_required_fields'] && $form['marital_status'] == '') || ($config['membership_required_fields']['marital_status'] && $form['marital_status'] == ''))
		$errm[] = $LANG['MEMBERSHIP_MARITAL_STATUS_EMPTY'];
	if((!$config['membership_required_fields'] && $form['national'] == '') || ($config['membership_required_fields']['national'] && $form['national'] == ''))
		$errm[] = $LANG['MEMBERSHIP_NATIONAL_EMPTY'];
	if((!$config['membership_required_fields'] && $form['race'] == '') || ($config['membership_required_fields']['race'] && $form['race'] == ''))
		$errm[] = $LANG['MEMBERSHIP_RACE_EMPTY'];
	if((!$config['membership_required_fields'] && $form['education_level'] == '') || ($config['membership_required_fields']['education_level'] && $form['education_level'] == ''))
		$errm[] = $LANG['MEMBERSHIP_EDUCATION_LEVEL_EMPTY'];
	if((!$config['membership_required_fields'] && $form['address'] == '') || ($config['membership_required_fields']['address'] && $form['address'] == ''))
		$errm[] = $LANG['MEMBERSHIP_ADDRESS_EMPTY'];
	if((!$config['membership_required_fields'] && $form['postcode'] == '') || ($config['membership_required_fields']['postcode'] && $form['postcode'] == ''))
		$errm[] = $LANG['MEMBERSHIP_POSTCODE_EMPTY'];
	if((!$config['membership_required_fields'] && $form['city'] == '') || ($config['membership_required_fields']['city'] && $form['city'] == ''))
		$errm[] = $LANG['MEMBERSHIP_CITY_EMPTY'];
	if((!$config['membership_required_fields'] && $form['state'] == '') || ($config['membership_required_fields']['state'] && $form['state'] == ''))
		$errm[] = $LANG['MEMBERSHIP_STATE_EMPTY'];
	if(($config['membership_required_fields']['phone_1'] && $form['phone_1'] == ''))
		$errm[] = $LANG['MEMBERSHIP_PHONE_HOME_EMPTY'];
	if(($config['membership_required_fields']['phone_2'] && $form['phone_2'] == ''))
		$errm[] = $LANG['MEMBERSHIP_PHONE_OFFICE_EMPTY'];
	if(($config['membership_required_fields']['phone_3'] && $form['phone_3'] == ''))
		$errm[] = $LANG['MEMBERSHIP_PHONE_MOBILE_EMPTY'];
	if($config['membership_required_fields']['email'] && $form['email'] == '')
		$errm[] = $LANG['MEMBERSHIP_EMAIL_EMPTY'];
	if($form['email'] != '' && !preg_match(EMAIL_REGEX, $form['email']))
		$errm[] = $LANG['MEMBERSHIP_EMAIL_PATTERN_INVALID'];
	if($form['email']){
		$q1 = $con->sql_query("select nric from membership where email =".ms($form['email']));
		while($r1 = $con->sql_fetchassoc($q1)){
			$exist_email_nric[] = $r1['nric'];
		}
		if($con->sql_numrows($q1) > 0 && !in_array($form['old_nric'],$exist_email_nric)){
			$errm[] = $LANG['MEMBERSHIP_EMAIL_IN_DATABASE'];
		}
		$con->sql_freeresult($q1);
	}
	if((!$config['membership_required_fields'] && $form['occupation'] == '') || ($config['membership_required_fields']['occupation'] && $form['occupation'] == ''))
		$errm[] = $LANG['MEMBERSHIP_OCCUPATION_EMPTY'];

	return $errm;
}

function validate_card($card_no, &$errmsg, &$card_type = '')
{
	global $config, $con, $LANG;
	if (!preg_match($config['membership_valid_cardno'], $card_no))
	{
	  $errmsg = $LANG['MEMBERSHIP_INVALID_CARD_NO'];
	  return false;
	}

	// search if card in database
    $con->sql_query("select nric from membership_history where card_no = ".ms($card_no) . " and remark <> 'CB' limit 1");
    if ($con->sql_numrows()>0)  // if found
    {
		$errmsg = $LANG['MEMBERSHIP_CARD_IN_DATABASE'];
		return false;
    }

    // perform change card
    $card_type = '';
	foreach($config['membership_cardtype'] as $type=>$ct)
	{
		if (preg_match($ct['pattern'], $card_no))
		{
        	$card_type = $type;
        	break;
    	}
    }

	if (!$card_type) 
	{
		$errmsg = $LANG['MEMBERSHIP_INVALID_CARD_NO'];
		return false;
	}
	
	return true;
	
}

function validate_card_upgrade($new_card_no, &$errmsg){
	global $con,$form,$LANG,$config;

	$con->sql_query("select card_no from membership where nric = ".ms($form['nric']));
	$card_no = $con->sql_fetchfield(0);
	$con->sql_freeresult();
	
	   // perform change card
    $card_level = $new_card_level = 1;
	foreach($config['membership_cardtype'] as $type=>$ct){
		if (preg_match($ct['pattern'], $card_no)){
        	$card_level = $ct['level'];
    	}
		
		if(preg_match($ct['pattern'], $new_card_no)){
        	$new_card_level = $ct['level'];
		}
    }
	
	// found the user doing card level downgrade
	if($card_level >= $new_card_level){
		$errmsg = sprintf($LANG['MEMBERSHIP_INVALID_CARD_UPGRADE'], $new_card_no);
		return;
	}
	
	return true;
}

function ajax_get_redemption_history(){
	global $con,$smarty;
	
	$card_no = $_REQUEST['card_no'];
	$date = $_REQUEST['date'];
	$bid = mi($_REQUEST['bid']);
	$type = $_REQUEST['type'];
	
	$filter = array();
	$filter[] = "mr.branch_id=$bid";
	$filter[] = "mr.card_no=".ms($card_no);
	$filter[] = "mr.date=".ms($date);
	//$filter[] = "mr.status=0";
	
	if($type == "CANCELED") $filter[] = "mr.status=1 and mr.active=1";
	
	$filter = "where ".join(' and ', $filter);
	$sql = "select mr.*,branch.code as branch_code from membership_redemption mr
	left join  branch on branch.id=mr.branch_id
	$filter";

	$con->sql_query($sql) or die(mysql_error());
	$smarty->assign('mr_list',$con->sql_fetchrowset());
	$smarty->assign('type', $_REQUEST['type']);
	$smarty->display('membership.redemption_history_by_date.tpl');
}

function member_verify($nric)
{
	global $con;

    $con->sql_query("select * from membership where nric=".$nric);
	$verify = $con->sql_fetchrow();
	return $verify;
}

function get_auto_redemption_history($show_tpl){
	global $con, $smarty, $sessioninfo, $config;
	
	$bid = mi($_REQUEST['bid']);
	$card_no = trim($_REQUEST['card_no']);
	$ord_date = trim($_REQUEST['ord_date']);
	
	// get nric
	$con->sql_query("select nric from membership where card_no=".ms($card_no));
	$nric = trim($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	if(!$nric)	die("Invalid membership card");
	
	// get voucher
	$voucher_info = array();
	$q1 = $con->sql_query("select code, voucher_value from mst_voucher where branch_id=$bid and member_nric=".ms($nric)." and added=".ms($ord_date));
	while($r = $con->sql_fetchassoc($q1)){
		$voucher_info['by_value'][$r['voucher_value']]++;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign('voucher_info', $voucher_info);
	$smarty->display('membership_history.auto_redemption_history.tpl');
}

function update_membership_extra_info_structure(){
	global $con, $config;
	
	if(!$config['membership_extra_info'])	die("Config Not Found");
	
	$con->sql_query("explain membership_extra_info");
	$c_info = array();
	while($r = $con->sql_fetchassoc()){
		$c_info[$r['Field']] = $r;
	}
	$con->sql_freeresult();
	
	$alter_query = array();
	foreach($config['membership_extra_info'] as $c => $r){
		$data_type = trim($r['data_type']);
		if(isset($r['default_value']))	$default_value = $r['default_value'];
		
		if(!$data_type)	die("Invalid Datatype for $c");
		
		if(!isset($c_info[$c]))	$alter_query[] = "add $c $data_type ".(isset($default_value) ? "Default ".ms($default_value) : "");	// need add column
		else{
			if($c_info[$c]['Type'] != $r['data_type'] || ($c_info[$c]['Default'] != $default_value || isset($c_info[$c]['Default']) != isset($default_value))){	// need modify
				$alter_query[] = "modify $c $data_type ".(isset($default_value) ? "Default ".ms($default_value) : "");
			}
		}
		unset($default_value);
	}
	if($alter_query){
		$str = "alter table membership_extra_info ".join(',', $alter_query);
		print "$str<br>";
		$con->sql_query($str);
	}
	print "Done.";
}

function principal_nric_verify(){
	global $con, $LANG;

	$ret = array();
	$con->sql_query("select m.*
					 from membership m
					 where (m.nric = ".ms($_REQUEST['principal_info'])." or m.card_no = ".ms($_REQUEST['principal_info']).")");
	$pnric_info = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	if($pnric_info['nric']){
		// check is this card principal to others
		$q1 = $con->sql_query("select count(*) as is_principal from membership m where m.parent_nric = ".ms($pnric_info['nric']));
		$is_principal = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		// check is this the sub card is already principal to others
		$q1 = $con->sql_query("select count(*) as is_principal from membership m where m.parent_nric = ".ms($_REQUEST['nric']));
		$is_principal2 = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if($is_principal['is_principal']){ // found it is principal card for other card
			$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_SUB_USED'], $pnric_info['nric'], "Principal");
		}elseif($is_principal2['is_principal']){ // found this sub card is already principal to others
			$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_SUB_USED'], $_REQUEST['nric'], "Principal");
		}elseif($pnric_info['parent_nric']){ // found is supplementary card
			$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_SUB_USED'], $pnric_info['nric'], "Supplementary");
		}else{
			$q1 = $con->sql_query("select * from membership_history where nric = ".ms($pnric_info['nric'])." order by expiry_date desc, issue_date desc, added desc limit 1");
			$pnric_history_info = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);

			if($pnric_info['terminated_date'] > 0){ // terminated
				$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_CARD_CONFIRM'], "Principal [".$pnric_info['nric']."] has been terminated.\n");
			}elseif(strtotime($pnric_history_info['expiry_date']) < time()){ // expired
				$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_CARD_CONFIRM'], "Principal [".$pnric_info['nric']."] has been expired.\n");
			}else{
				$ret['result'] = sprintf($LANG['MEMBERSHIP_PRINCIPAL_CARD_CONFIRM'], "");
			}
			
			$ret['ok'] = 1; // no problem
			$ret['val'] = $pnric_info['nric'];
		}
	}else $ret['result'] = "Principal not found.";
	
	print json_encode($ret);
}

function principal_nric_unlink(){
	global $con, $LANG;
	
	$ret = $nric_negative_points = array();
	$principal_nric = $_REQUEST['principal_nric'];
	$nric = $_REQUEST['nric'];
	if(!$principal_nric || !$nric) return;
	
	$q1 = $con->sql_query("select sum(points) as total_pts, nric from membership_points where nric in (".ms($principal_nric).", ".ms($nric).") group by nric");
	
	while($r = $con->sql_fetchassoc($q1)){
		if($r['total_pts'] < 0) $nric_negative_points[$r['nric']] = $r['nric'];
	}
	$con->sql_freeresult($q1);
	
	if($nric_negative_points){
		$ret['result'] = sprintf($LANG['MEMBERSHIP_INVALID_PRINCIPAL_UNLINK'], join(",", $nric_negative_points));
	}else $ret['ok'] = 1;
	
	print json_encode($ret);
}

function sort_date($a,$b){
	if (($a['ord_date']==$b['ord_date'])) return 0;
	else return ($a['ord_date']>$b['ord_date']) ? -1:1;
}
?>
