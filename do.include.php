<?php
/*
REVISION HISTORY
================
3/31/2008 5:37:42 PM gary
- add all uom details when load do items.

4/1/2009 1:48:00 PM Andy
- add "b2.description as do_branch_description" in sql at function load_do_header

5/27/2009 4:30:00 PM Andy
- add function assign_inv_no()

7/9/2009 2:51 PM Andy
- add table alter
	- alter table do add inv_no char(10) unique after do_no

7/21/2009 4:46:06 PM Andy
- Add do_reset function and if do_no exists, use back the do_no

7/27/2009 3:55:52 PM Andy
- DO Approval branch id change from sessioninfo[branch_id] to form[branch_id]

8/6/2009 2:37:47 PM Andy
- add DO Credit sales
- function load_do_items, add collect master sku packing_uom_id as master_uom_id

8/11/2009 3:29:01 PM Andy
- assign do & inv no checking by using do_no and do_type

8/19/2009 10:12:19 AM Andy
- fix do reset bug

9/1/2009 10:59:54 AM Andy
- deliver to dropdown include self branch
 
2009/10/08 11:15:00 Andy
- When DO approve, those deliver to multiple branches will have split to have their own approval history id
- when DO reset , make DO become active=1

11/5/2009 4:49:57 PM Andy
- add invoice discount. per sheet and per item

11/16/2009 11:55:48 AM Andy
- fix price type bug if Cash Sales DO

11/19/2009 12:20:17 PM Andy
- edit do create from po feature

12/1/2009 11:52:54 AM Andy
- check if $config['sku_always_show_trade_discount'] and latest price type is null, then use master price type

12/7/2009 4:17:07 PM Andy
- Fix invoice no bug if $config.do_invoice_separate_number not set, use do_no instead of id

1/18/2010 3:59:32 PM Andy
- Add paid feature for cash sales DO

5/10/2010 3:04:00 PM
- Add DO Markup.

5/14/2010 11:17:30 AM Andy
- Add Sales Person Name in Credit/Cash Sales DO. (need config)

5/17/2010 1:50:22 PM Andy
- Add DO auto split by price type can automatically insert DO Discount base on branch trade discount. (need config)
- DO Markup can now be use as DO Discount as well.

5/19/2010 11:23:27 AM Andy
- cash sales DO Printing add sales person name
- credit sales DO Printing add sales person name & debtor term

5/27/2010 10:40:53 AM Andy
- Add debtor code and area on printing

5/31/2010 2:54:17 PM Andy
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.
- Fix javascript and smarty rounding bugs.
- Add checking for allowed future/passed DO Date limit.

5/31/2010 4:12:18 PM Alex
- Add $config['reset_date_limit']

6/17/2010 3:10:10 PM Justin
- Added a table called open_do_items
- Created a set of functions to indicate the different types between SKU Item and Open Item.
- Added a new button to allow user to execute to create a Open Item while editing/creating a particular DO.
- Solved the duplications error while creating Open Item.
- Added 1 new field (description) under tmp_open_item. 
- Added Department query.
- Added Department drop-down list selection and able to add/update into database based on the config if it is set.
- Added config for DO - $config['do_add_open_item']. This config will enable/disable the Open Item feature on DO.
- Added the checking function not to filter out Open Item from hidden under report printing if found config is set.

6/22/2010 12:25:06 PM Justin
- Removed the maintenance check and place under do.php

11/9/2010 11:43:19 AM Andy
- Add checking for canceled/deleted and prevent it to be edit.

11/10/2010 2:48:15 PM Alex
- fix bugs on missing insert department id and stock balance

1/25/2011 10:25:01 AM Andy
- Fix a bugs which cause multiple approval make document stuck.

1/26/2011 10:13:56 AM Andy
- Fix a bugs cause if direct approve will not get invoice no.

2/16/2011 5:43:19 PM Justin
- Added S/N enhancements.

4/8/2011 5:58:57 PM Justin
- Added to take cost as well while approval.

5/27/2011 2:51:20 PM  Justin
- Added branch list to attach "deliver to" info.
- Added branch list to attach "exchange rate" from forex.
- Added the the insert for do items to include "foreign_cost_price".

6/6/2011 5:20:54 PM Justin
- Added to validate with serial no only when config found.

6/21/2011 4:24:12 PM Justin
- Added the missing of Open Item split by branch when approved.

6/24/2011 4:01:16 PM Andy
- Make all branch default sort by sequence, code.

6/24/2011 6:35:18 PM Alex
- add get price_indicate for each items

7/6/2011 11:17:47 AM Andy
- Change split() to use explode()

7/18/2011 9:41:42 AM Justin
- Fixed the S/N bugs where it always created empty row and unable user to confirm again whenever the DO is transfer for multiple branches.

8/10/2011 4:04:25 PM Andy
- Change DO Invoice Discount format.

8/23/2011 10:44:12 AM Andy
- Move check maintenance version to do.include.php

10/3/2011 5:55:43 PM Justin
- Applied when get item list, pick up sku item's doc_allow_decimal.

10/21/2011 4:28:32 PM Justin
- Fixed the bugs where system calculate the foreign amount wrongly.

11/18/2011 2:35:04 PM Andy
- Add save/show DO price type. (only if all items in DO having same price type and is consignment mode).

12/6/2011 11:45:32 AM Justin
- Added to pickup sa list if config found.
- Enhanced to have add/update sa info to do and do items.

1/13/2012 6:02:11 PM Justin
- Added to pickup sku ID, size and color.

2/8/2012 4:54:34 PM Justin
- Added to pick up masterfile UOM Code.

2/14/2012 6:27:42 PM Justin
- Modified to capture whether DO items contains S/A.

4/6/2012 4:47:48 PM Alex
- add user notification list on change_user_list() and change_user_list_process()

4/23/2012 11:07:47 AM Alex
- add check user active => change_user_list_process()

4/25/2012 6:41:33 PM Alex
- change get price type and selling price based on do date

4/30/2012 6:24:35 PM Alex
- +1 day while comparing do date with sku items price history timestamp

7/30/2012 10:16 AM Andy
- Fix price indicator missing once DO split by price type or after approved split by multiple branch.

8/8/2012 3:53 PM Justin
- Enhanced to pickup selling price from grn barcode if scan by prefix barcode.

10/1/2012 3:46 PM Justin
- Bug fixed on take too long to generate DO/Invoice no.

10/10/2012 2:24 PM Justin
- Bug fixed on generating Invoice No which getting wrong count of numbers.

12/10/2012 2:31:00 PM Fithri
- Select debtor icon change to button
- Add another button for add branch

1/17/2013 3:06 PM Justin
- Enhanced to capture inactive debtor info.

3/1/2013 5:20 PM Justin
- Enhanced to retrieve master UOM fraction for enable/disable UOM field base on config set.

3/28/2013 12:02 PM Andy
- when reset do, change to select sku_item_id to update changed=1 instead of using sub query.

4/16/2013 3:35 PM Andy
- Change to check maintenance version 196.

5/16/2013 4:10 PM Andy
- Change to check maintenance version 198.
- Add to get sku items additional description when load do items.

5/22/2013 5:08 PM Justin
- Bug fixed on system does not reload stock balance for single deliver branch while loading items.
- Enhanced to only reload stock balance while editing DO.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/16/2013 03:53 PM Justin
- Modified to change the status for S/N to wording instead of numberic.

7/29/2013 2:28 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.
- Enhance to load more_info when select approval history.

8/13/2013 11:45 AM Justin
- Enhanced to pickup weight from sku items while loading do items.

10/8/2013 3:07 PM Justin
- Enhanced to pickup debtor info.

10/17/2013 3:04 PM Justin
- Bug fixed on reset DO that do not update item inventory change.

11/27/2013 4:36 PM Andy
- Fix reset bug, which causing the transfer DO unable to recalculate stock balance for delivered branch.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

3/5/2014 1:44 PM Justin
- Bug fixed on serial_no_handler does not being called up while reset DO.
- Bug fixed on system did not update primary information into serial no while DO has been checkout.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

3/31/2014 4:05 PM Justin
- Bug fixed on POS branch ID is not updated while S/N has been sold.

4/22/2014 5:06 PM Justin
- Enhanced to have filter on mprice type by user.

5/12/2014 5:58 PM Justin
- Bug fixed on serial no not functioning when comes to reset.

5/26/2014 10:55 AM Justin
- Enhanced to have new price indicator "Masterfile - HQ Selling".

7/9/2014 11:29 AM Justin
- Enhanced to pickup warranty type and periods by SKU items.

7/17/2014 10:46 AM Fithri
- when select debtor, automatically select mprice if the debtor's mprice is set & user cannot change it

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

11/13/2014 5:29 PM Andy
- Fix to update sales order delivered qty when DO get terminated in approval cycle.

1/27/2015 11:49 AM Justin
- Enhanced to pickup branch's email and contact no.

2/9/2015 3:37 PM Andy
- GST Enhancements.

3/23/2015 10:16 AM Andy
- Change to allow GST for consignment modules when not transfer DO.

4/18/2015 12:05 PM Andy
- Added DO function generate_do_gst_summary.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.
- Change the DO rounding calculation.

6/25/2015 12:16 PM Andy
- Fix a bug where DO and branch deliver to address is not working.

2/22/2016 1:26 PM Qiu Ying
- Modify "check_must_can_edit" to check is_checkout_screen

04/01/2016 17:30 Edwin
- Added debtor telephone number and terms at credit sales print preview.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.
- Bug fixed on stock balance calculate wrong when changed branch on "Deliver To".

10/6/2016 5:45 PM Andy
- Fixed to only reset grr/grn when it is single_server_mode and no config do_skip_generate_grn.
- Fixed reset grr/grn to use grr_id if is using grn future.

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

12/1/2016 4:39 PM Andy
- Increase maintenance version checking to 304.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

3/23/2017 4:36 PM Justin
- Enhanced S/N handler to update status according to the config "do_skip_generate_grn".
- Enhanced reset S/N in two ways based on config "do_skip_generate_grn".

3/29/2017 3:59 PM Justin
- Bug fixed on adding parent & child SKU will not filter added SKU items if config "do_item_allow_duplicate" is off.
- Enhanced to have new privilege checking for user to reset DO.

5/29/2017 3:44 PM Justin
- Enhanced to update DO items to have BOM information.

6/16/2017 12:35 PM Andy
- Increase maintenance version checking to 322.
- Added TMP maintenance checking version 322.

6/23/2017 2:08 PM Justin
- Enhanced to pick up cost from the same day as DO date.

6/30/2017 3:42 PM Justin
- Enhanced to disable SKU not allowed to add when it is BOM Package SKU when using Add Parent & Child.

2017-08-25 15:08 PM Qiu Ying
- Enhanced to add Debtor blocklist for Credit Sales DO at debtor masterfile

9/6/2017 5:14 PM Andy
- Increase maintenance version checking to 328.

10/3/2017 10:12 AM Justin
- Enhanced to call sales trend from skuManager.php. 

12/11/2017 9:26 AM Justin
- Enhanced to have "Address (Delivery)".
- Enhanced to have "Use different Deliver To" which will allow user to key in delivery company name and address.

12/12/2017 3:25 PM Andy
- Increase maintenance version checking to 341.

6/18/2018 11:49 AM Andy
- Fixed add item by parent child only search for active sku.

6/1/2018 10:00 AM HockLee
- join packing table in load_do_items()

8/15/2018 2:40 PM Andy
- Increase maintenance version checking to 356.

8/30/2018 11:19 AM Andy
- Enhanced Credit Sales DO to have Debtor Price feature.
- Increase maintenance version checking to 360.

10/9/2018 4:43 PM Andy
- Fixed item decimal points show incorrectly when in view DO.

10/12/2018 5:25 PM Andy
- Increase maintenance version checking to 368.

11/16/2018 3:34 PM Justin
- Enhanced to get brand info while loading DO items.

11/23/2018 2:50 PM Andy
- Add Create Multiple DO from CSV. (Transfer DO Only)
- Increase tmp maintenance version checking to 355.

12/13/2018 5:20 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.
- Enhanced to load artno_mcode from sku_items instead of DO items.

2/12/2019 5:56 PM Andy
- Change auto_update_do_all_amt() to call doManager->recalculateDOAmount()
- move create_do_from_tmp() to become doManager->createDOFromTMP().
- move check_do_gst_status() to become doManager->checkDOGstStatus().
- move get_item_selling() to become doManager->getItemSelling().

3/14/2019 5:23 PM Andy
- Increase maintenance version checking to 383.

5/30/2019 11:45 PM Andy
- Enhanced to show Related DO.

5/31/2019 10:18 AM Andy
- Enhanced to show branch code in Related DO link.

7/4/2019 11:20 AM Andy
- Increase maintenance version checking to 400.

8/23/2019 1:31 PM William
- Enhanced to added new column "custom_col" for new config "do_custom_column".

3/14/2019 5:23 PM Andy
- Increase maintenance version checking to 407.

10/3/2019 5:35 PM William
- Pickup last approval history info for printing.

10/7/2019 1:07 PM Andy
- Increase maintenance version checking to 413.

10/17/2019 5:18 PM Andy
- Increase maintenance version checking to 415.

11/27/2019 2:15 PM William
- Enhanced to show sku item photo when config "do_show_photo" is active.

2/14/2020 3:25 PM William
- Enhanced to Change log type "DO" to "DELIVERY ORDER".

4/7/2020 12:03 PM William
- move function assign_do_no() to doManager.

10/17/2019 5:18 PM Andy
- Increase maintenance version checking to 457.

4/15/2020 4:14 PM William
- Enhanced to check month closed when Reset.

8/3/2021 3:15 PM Ian
- Modified $sql to add selection for rsp & rsp price
*/
$maintenance->check(457);
$maintenance->check(407, true);
$approval_status = array(1 => "Approved", 2 => "Rejected", 5 => "Cancelled/Terminated");

$con->sql_query("select * from category where id in ($sessioninfo[department_ids]) order by description");
$smarty->assign("departments", $con->sql_fetchrowset());

$default_settings=array(1 => "Cost", 2=> "Selling (Normal)", 3 => "Last DO", 4 => "PO Cost");
if($config['sku_multiple_selling_price']){
	foreach ($config['sku_multiple_selling_price'] as $data){
		$default_settings[$data]="Selling - ".$data;
	}
}

if($_REQUEST['do_type'] == "transfer"){
	$default_settings['hqselling']="Masterfile - HQ Selling";
}

$config['sku_multiple_selling_price']=$default_settings;
unset($default_settings);
$smarty->assign("config", $config);

if($config['masterfile_enable_sa']){
	$q1 = $con->sql_query("select * from sa where active=1 order by code");
	while($r = $con->sql_fetchassoc($q1)){
		$sa_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("sa_list", $sa_list);
}

function load_do_header($id, $branch_id){
	global $con, $smarty, $sessioninfo, $config;
	
	$sql = "select do.*, bah.approvals, branch.report_prefix as prefix, category.description as dept_name, b2.code as do_branch_name, user.u as user, b2.description as do_branch_description,branch.code as from_branch_name,branch.description as from_branch_description, user.fullname as owner_fullname,do.address_deliver_to, b2.deliver_to as b2_deliver_to, do.packed as packing
            from do
            left join branch_approval_history bah on bah.id = do.approval_history_id and bah.branch_id = do.branch_id
            left join branch on branch.id=do.branch_id
            left join branch b2 on b2.id=do_branch_id
            left join category on category.id=do.dept_id
            left join user on user.id=do.user_id
            where do.id=$id and do.branch_id=$branch_id";
	$q1=$con->sql_query($sql);
	$form = $con->sql_fetchassoc($q1);
	
	if($form['do_type'] == "credit_sales"){
		// select debtor (bill) info
		if($form['debtor_id']){
			$q1 = $con->sql_query("select d.description as debtor_description, d.address as debtor_address, d.phone_1 as p1,d.phone_2 as p2,
								   if(d.delivery_address != '' and d.delivery_address is not null, d.delivery_address, d.address) as debtor_deliver_address, d.phone_3 as pfax,
								   d.term as debtor_term,d.phone_1 as debtor_phone,d.code as debtor_code,d.area as debtor_area, d.contact_person as debtor_cp
								   from debtor d
								   where d.id = ".mi($form['debtor_id']));
			if($con->sql_numrows($q1) > 0){
				$bill_dt_info = $con->sql_fetchassoc($q1);
				$form = array_merge($form, $bill_dt_info);
			}
			$con->sql_freeresult($q1);
		}
		
		// select debtor (deliver) info
		if($form['use_address_deliver_to']){
			if($form['delivery_debtor_id']){
				$q1 = $con->sql_query("select d.description as deliver_debtor_description, d.phone_1 as p1, d.phone_2 as deliver_p2,
									   if(d.delivery_address != '' and d.delivery_address is not null, d.delivery_address, d.address) as deliver_debtor_deliver_address, d.phone_3 as deliver_pfax, 
									   d.term as deliver_debtor_term, d.phone_1 as deliver_debtor_phone, d.code as deliver_debtor_code, 
									   d.area as deliver_debtor_area, d.contact_person as deliver_debtor_cp
									   from debtor d
									   where d.id = ".mi($form['delivery_debtor_id']));
				if($con->sql_numrows($q1) > 0){
					$deliver_dt_info = $con->sql_fetchassoc($q1);
					// found it got set custom address
					if(trim($form['address_deliver_to'])) $deliver_dt_info['debtor_deliver_address'] = $form['address_deliver_to'];
					elseif($deliver_dt_info['deliver_debtor_deliver_address']) $deliver_dt_info['debtor_deliver_address'] = $deliver_dt_info['deliver_debtor_deliver_address'];
					else $deliver_dt_info['debtor_deliver_address'] = $form['debtor_deliver_address'];
					$form = array_merge($form, $deliver_dt_info);
				}
			}elseif($form['address_deliver_to']){ // found user tick "Same as above" and got key in custom delivery address
				$form['debtor_deliver_address'] = $form['address_deliver_to'];
			}
			$con->sql_freeresult($q1);
		}
	}
	
	$con->sql_freeresult($q1);
	
	if($form['total_amount']>0 && !$form['sub_total_amt']){
		// recalculate and update all amt & amt 2
		auto_update_do_all_amt($branch_id, $id);

		// reload again
		$q1=$con->sql_query($sql);
		$form = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
	}
	
	if ($form['po_no'] != '') $smarty->assign("from_po",1);
	$form['deliver_branch']=unserialize($form['deliver_branch']);
	$form['checkout_info']=unserialize($form['checkout_info']);
	$form['open_info']=unserialize($form['open_info']);
	$form['mst_sa']=unserialize($form['mst_sa']);
	$form['allowed_user']=unserialize($form['allowed_user']);
	
	$debtors = $smarty->get_template_vars('debtor');
	
	if($form['debtor_id'] && !$debtors[$form['debtor_id']]){
		$sql = $con->sql_query("select * from debtor where id = ".mi($form['debtor_id']));
		$debtor_info = $con->sql_fetchassoc($sql);
		
		if($debtor_info){
			$debtors[$debtor_info['id']] = $debtor_info;
			$smarty->assign("debtor", $debtors);
		}
		$con->sql_freeresult($sql);
	}
	
	if($form['do_markup'])	$form['do_markup_arr'] = explode("+", $form['do_markup']);
	if($form['markup_type']=='down'){
        $form['do_markup_arr'][0] *= -1;
        $form['do_markup_arr'][1] *= -1;
	}
	
	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info, user.fullname
                            from branch_approval_history_items i
                            left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
                            left join user on i.user_id=user.id
                            where h.ref_table='do' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
                            order by i.timestamp");
		$approval_history = array();
		while($r = $con->sql_fetchassoc($q2)){
			$r['more_info'] = unserialize($r['more_info']);
			$approval_history[] = $r;
		}
		$con->sql_freeresult($q2);
		$smarty->assign("approval_history", $approval_history);
		$approval_history_info = end($approval_history);
		if($approval_history_info['log'] == 'Approved'){
			$form['approval_history_info'] = $approval_history_info;
		}
	}
	
	if($form['relationship_guid']){
		$con->sql_query("select do.branch_id, do.id, do.do_no, b.code as bcode
			from do
			left join branch b on b.id=do.branch_id
			where do.relationship_guid=".ms($form['relationship_guid']));
		while($r = $con->sql_fetchassoc()){
			if($r['branch_id'] == $form['branch_id'] && $r['id'] == $form['id'])	continue;
			
			$do_key = $r['branch_id'].'_'.$r['id'];
			$form['related_do_list'][$do_key] = $r;
		}
		$con->sql_freeresult();
	}
	
	$params = array();
	$params['user_id'] = $sessioninfo['id'];
	$params['id'] = $form['approval_history_id'];
	$params['branch_id'] = $branch_id;
	$params['check_is_approval'] = true;
	$is_approval = check_is_last_approval_by_id($params, $con);
	if($is_approval)  $form['is_approval'] = 1;
			
	change_user_list(false,$form);
	//print_r($form);
	sku_multiple_selling_price_handler($form);
		
	if($form['active'] == 1 && ($form['status']==0 || $form['status'] ==2) && !$form['approved'] && !$form['checkout']){
		$form['is_under_gst'] = check_do_gst_status($form);
	}
	
	//print_r($form);
	return $form;
}

function change_user_list($ajax=true,$form){
	global $smarty;
	$user_list = change_user_list_process($form);
	
	if ($ajax){	
		print json_encode($user_list);
	}else		$smarty->assign("user_list",$user_list);
}

function change_user_list_process($form){
    global $con;
	$department_id = mi($form['dept_id']);
	
	if ($department_id){
		$filter= "and user.departments like '%i:$department_id;%'";
	}

	if ($form['deliver_branch']){
		foreach($form['deliver_branch'] as $k=>$v){
			$q1=$con->sql_query("select up.user_id,user.u from user_privilege up
		left join user on up.user_id=user.id
		where privilege_code='DO' and branch_id=$v and user.active = 1 and user.is_arms_user=0 $filter 
		order by user.u");
			while($r_u = $con->sql_fetchassoc($q1)){
				$user_list[$v][$r_u['user_id']]=$r_u['u'];
			}
			$con->sql_freeresult($q1);
		}
	}else{
		$do_branch_id = mi($form['do_branch_id']);	
		//get user list
		$q1=$con->sql_query("select up.user_id,user.u from user_privilege up
	left join user on up.user_id=user.id
	where privilege_code='DO' and user.active = 1 and branch_id=$do_branch_id and user.is_arms_user=0 $filter 
	order by user.u");
		while($r_u = $con->sql_fetchassoc($q1)){
			$user_list[$do_branch_id][$r_u['user_id']]=$r_u['u'];
		}
		$con->sql_freeresult($q1);
	}
	return $user_list;
}

function get_item_selling($sid, $deliver_branch, $do_bid, $do_date, $selling_price=0){
	global $con, $branch_id, $appCore;
	
	return $appCore->doManager->getItemSelling($branch_id, $sid, $deliver_branch, $do_bid, $do_date, $selling_price);
}

/*function get_selling_price($sid,$bid,$key_bid,$do_date){
	global $con,$config;

    $key_bid = intval($key_bid);
    $sql="select round(if(sip.price is null, si.selling_price, sip.price),3) as selling_price,if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as trade_discount_code,
	sku.default_trade_discount_code
from sku_items si
left join sku_items_price_history sip on sip.sku_item_id=si.id and branch_id=$bid and sip.added < ".ms($do_date)."
left join sku on sku.id=si.sku_id
where si.id=$sid order by sip.added desc limit 1";
	$q1=$con->sql_query($sql);
//	print $sql;
	//print "key bid = $key_bid";
    $r1 = $con->sql_fetchassoc($q1);
    $con->sql_freeresult($q1);
    $ret['selling_price'] = $r1['selling_price'];
    //$ret['price_type'][$bid] = $r1['trade_discount_code'];
    $ret['price_type'][$key_bid] = $r1['trade_discount_code'];
    if($config['sku_always_show_trade_discount']&&!$r1['trade_discount_code'])  $ret['price_type'][$key_bid] = $r1['default_trade_discount_code'];
	
	//print_r($ret);
	return $ret;
}*/

function get_do_date($do_id,$bid){
	global $con;
	$q1=$con->sql_query("select do_date from do where id=$do_id and branch_id=$bid"); 
	$r1 = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	return $r1['do_date']; 
}

function load_do_items($do_id, $branch_id, $form, $use_tmp = false){
	global $con, $sessioninfo, $config, $smarty, $gst_list, $appCore;
	
	// generate gst list
	if(!$gst_list)	construct_gst_list();
	
 	//$form=$_REQUEST;
 	//print_r($form);
 	if($form['po_multi_deliver_to']&&$form['po_no'])    $form['deliver_branch'] = $form['po_multi_deliver_to'];
 	//print_r($form);
	$owner_filter='';
	if($use_tmp){
		$table="tmp_do_items";
		$owner_filter=" and tdi.user_id=$sessioninfo[id] ";
		$doi_description = "tdi.description as doi_description,";
	}
	else{
		$table="do_items";
		$get_open_item = 1;
	}
	
	// check $deliver_branch
	if($form['deliver_branch']){
		if(!is_array($form['deliver_branch'])) $deliver_branch = unserialize($form['deliver_branch']);
		else    $deliver_branch = ($form['deliver_branch']);
		$bid_exists = false;
		foreach($deliver_branch as $temp_bid){
			if($temp_bid>0) $bid_exists = true;
		}
		if(!$bid_exists)    $deliver_branch = '';

	}
	
	$bom_loaded = array();
	$sql = "select tdi.*, sku_items.id as sku_item_id, sku_items.sku_item_code, sku_items.description as description, sku_items.sku_id, 
			$doi_description sku_items.artno, sku_items.mcode, uom.id as uom_id, uom.code as uom_code, 
			uom.fraction as uom_fraction, tdi.selling_price as selling_price, do.deliver_branch, do.do_branch_id, do.status, do.do_date,
			do.active,do.checkout, sku_items.artno, stock_balance1, stock_balance2, stock_balance2_allocation, 
			sku_items.packing_uom_id as master_uom_id, item_discount,sku_items.link_code, tdi.serial_no, sku.have_sn, 
			ifnull(do.do_type, ".ms($form['do_type']).") as do_type, sku_items.doc_allow_decimal, sku_items.sku_id, 
			sku_items.size, sku_items.color, si_uom.code as master_uom_code, si_uom.id as master_uom_id, si_uom.fraction as master_uom_fraction,
			sku_items.additional_description, sku_items.weight, sku_items.sn_we, sku_items.sn_we_type, sku_items.location, p.carton as 'pack_carton', p.weight_kg as 'pack_weight', 
			do.do_no, b.id as brand_id, b.code as brand_code,
			sku_items.use_rsp, if(sip.price is null, sku_items.rsp_discount, sip.rsp_discount) as rsp_discount,sku_items.rsp_price
			from $table tdi
			left join do on do.id=do_id and do.branch_id=tdi.branch_id
			left join sku_items on tdi.sku_item_id=sku_items.id
			left join sku_items_price sip on sip.sku_item_id =tdi.sku_item_id and sip.branch_id=tdi.branch_id
			left join sku on sku_items.sku_id = sku.id
			left join uom on uom.id=tdi.uom_id
			left join uom si_uom on si_uom.id = sku_items.packing_uom_id
			left join packing p on p.do_items_id = tdi.id and p.branch_id = tdi.branch_id
			left join brand b on b.id = sku.brand_id
			where do_id=$do_id and tdi.branch_id = $branch_id $owner_filter
			order by tdi.id";
	//print_r($deliver_branch);

	$q1=$con->sql_query($sql);
	while($r1 = $con->sql_fetchassoc($q1)){
		$r1['ctn_allocation'] = unserialize($r1['ctn_allocation']);
		$r1['pcs_allocation'] = unserialize($r1['pcs_allocation']);
		$r1['price_type'] = unserialize($r1['price_type']);
		$r1['serial_no'] = unserialize($r1['serial_no']);
		$r1['dtl_sa'] = unserialize($r1['dtl_sa']);
		$r1['additional_description'] = unserialize($r1['additional_description']);
		$r1['stock_balance2_allocation'] = unserialize($r1['stock_balance2_allocation']);
		$r1['parent_stock_balance2_allocation'] = unserialize($r1['parent_stock_balance2_allocation']);
        
		if($r1['dtl_sa']) $smarty->assign("dtl_have_sa", 1);

		if($r1['serial_no'] && $r1['do_type'] == "transfer"){
			$temp_bid = array();
			$ttl_sn = 0;
			if($deliver_branch) $temp_bid = $deliver_branch;
			else $temp_bid[$r1['do_branch_id']] = $r1['do_branch_id'];

			foreach($temp_bid as $row=>$bid){
				$splt_sn = explode("\n", $r1['serial_no'][$bid]);
				$sn_count = 0;

				foreach($splt_sn as $i=>$sn){
					if(trim($sn)) $sn_count++;
				}

				$r1['serial_no_count'][$bid] = $sn_count;
				$ttl_sn = $ttl_sn + $sn_count;
			}
			$r1['ttl_sn'] = $ttl_sn;
		}

		// set the SKU Item Code and Description take from tmp_open_items if it is open item
		if(!$r1['sku_item_id']){
			$r1['oi'] = 1;
			$r1['sku_item_id'] = '';
			$r1['description'] = $r1['doi_description'];
		}
		
		if($use_tmp){
			$do_bid=mi($form['do_branch_id']);
					
			// refresh latest selling price if we are loading from tmp_do_items
			if($r1['sku_item_id']){
				$do_date = $form['do_date'] ? $form['do_date'] : $r1['do_date']; 
				$tmp_sell=get_item_selling($r1['sku_item_id'], $deliver_branch, $do_bid,$do_date);
				$r1 = array_merge($r1, $tmp_sell);
				//print "update $table set selling_price_allocation=".sz($tmp_sell['selling_price_allocation']).", selling_price=".mf($tmp_sell['selling_price']).",price_type=".sz($tmp_sell['price_type'])." where id=$r1[id] and branch_id=$branch_id";
				$con->sql_query("update $table set selling_price_allocation=".sz($tmp_sell['selling_price_allocation']).", selling_price=".mf($tmp_sell['selling_price']).",price_type=".sz($tmp_sell['price_type'])." where id=$r1[id] and branch_id=$branch_id");
			}
		}
		else{
			//echo "Old = saved<br>";
			$r1['selling_price_allocation'] = unserialize($r1['selling_price_allocation']);		
		}
		
		if($r1['sku_item_id'] && $use_tmp){
			// stock balance 1
			$sb = $con->sql_query("select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id=$r1[sku_item_id]") or die(mysql_error());
			$r1['stock_balance1']=$con->sql_fetchfield('qty');
			$con->sql_freeresult($sb);
			
            //parent stock balance 1
            if($config['show_parent_stock_balance']) {
                $psb = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
                                        from sku_items si
                                        left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
                                        left join uom on uom.id=si.packing_uom_id
                                        where si.sku_id=$r1[sku_id]");
                
                $parent_stock_balance1 = 0;
                while($data = $con->sql_fetchassoc($psb)) {
                	$parent_stock_balance1 += $data['parent_stock_balance'];
                }
                $r1['parent_stock_balance1'] = $parent_stock_balance1;
                $con->sql_freeresult($psb);
            }
            
			if ($deliver_branch){
	    		unset($r1['stock_balance2_allocation']);

				foreach ($deliver_branch as $bid){
                    // stock balance allocation 2
					$con->sql_query("select sku_item_id,qty from sku_items_cost where branch_id=$bid and sku_item_id=$r1[sku_item_id]") or die(mysql_error());
					while($get_stock = $con->sql_fetchassoc()){
                        $r1['stock_balance2_allocation'][$bid]=$get_stock['qty'];
					}
                    $con->sql_freeresult();
                    
                    // parent stock balance allocation 2
                    if($config['show_parent_stock_balance']) {
                        $psb2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
                                        from sku_items si
                                        left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
                                        left join uom on uom.id=si.packing_uom_id
                                        where si.sku_id=$r1[sku_id]");
                            
                        $parent_stock_balance2_allocation = 0;
                        while($data_allocation = $con->sql_fetchassoc($psb2)) {
                        	$parent_stock_balance2_allocation += $data_allocation['parent_stock_balance'];
                        }
                        $r1['parent_stock_balance2_allocation'][$bid] = $parent_stock_balance2_allocation;
                        $con->sql_freeresult($psb2);
                    }
				}
			}else{
                // stock balance 2
                $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".($form['branch_changed']?$form['do_branch_id']:mi($r1['do_branch_id']))." and sku_item_id=".mi($r1['sku_item_id'])) or die(mysql_error());
				$r1['stock_balance2'] = $con->sql_fetchfield('qty');
				$con->sql_freeresult();
                
                //parent stock balance 2
                if($config['show_parent_stock_balance']) {
                    $psb2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
                                          from sku_items si
                                          left join sku_items_cost sic on sic.branch_id=".($form['branch_changed']?$form['do_branch_id']:mi($r1['do_branch_id']))." and sic.sku_item_id=si.id
                                          left join uom on uom.id=si.packing_uom_id
                                          where si.sku_id=$r1[sku_id]");
                    
                    $parent_stock_balnce2 = 0;
                    while($data2 = $con->sql_fetchassoc($psb2)) {
                    	$parent_stock_balnce2 += $data2['parent_stock_balance'];
                    }
                    $r1['parent_stock_balance2'] = $parent_stock_balnce2;
                    $con->sql_freeresult($psb2);
                }
			}

			if (!$r1['cost'] && $r1['status'] == 0 && $r1['active'] == 1){
				$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
				$tmp_cost = get_sku_item_cost_selling($branch_id, $r1['sku_item_id'], $tmp_do_date, array("cost"));	
				$r1['cost'] = $tmp_cost['cost'];
				unset($tmp_cost);
			}			
		}
		
		if(isset($config['sku_multiple_selling_price'][$r1['price_indicate']])){
			$r1['price_indicator'] = $config['sku_multiple_selling_price'][$r1['price_indicate']];
		}else{
			$r1['price_indicator'] = $r1['price_indicate'];
		}

		if(preg_match("/kg/i", $r1['weight'])){
			$r1['weight'] = mf($r1['weight']);
		}else $r1['weight'] = mf($r1['weight']) / 1000;
		
		// gst
		if($form['is_under_gst'] && $r1['gst_id'] > 0){
			check_and_extend_gst_list($r1);
		}
		
		if(isset($config['do_custom_column'])){
			$r1['custom_col'] = unserialize($r1['custom_col']);
		}
		// need to get bom parent info
		if($r1['bom_id'] && !$bom_loaded[$r1['bom_ref_num']]){
			$q2 = $con->sql_query("select si.*, if(si.artno is null or si.artno='',si.mcode, si.artno) as artno_mcode, sku.is_bom
								   from sku_items si
								   join sku on sku.id=si.sku_id
								   where si.id=".mi($r1['bom_id']));
			$bom_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			$r1['is_first_bom'] = 1;
			$r1['bom_parent_si_code'] = $bom_info['sku_item_code'];
			$r1['bom_parent_si_desc'] = $bom_info['description'];
			$r1['bom_parent_si_artno_mcode'] = $bom_info['artno_mcode'];
			$r1['bom_parent_si_artno'] = $bom_info['artno'];
			$r1['bom_parent_si_mcode'] = $bom_info['mcode'];
			
			$bom_loaded[$r1['bom_ref_num']] = true;
		}
		
		if($config['do_show_photo']){
			$sku_item_photo = $appCore->skuManager->getSKUItemPhotos($r1['sku_item_id']);
			if(count($sku_item_photo['photo_list'])> 0){
				$r1['photo'] = $sku_item_photo['photo_list'][0];
			}
		}
		$do_items[]=$r1;
	}
	$con->sql_freeresult($q1);

	if($get_open_item){
		$sql = "select tdi.*, tdi.description as description, tdi.artno_mcode,
				uom.id as uom_id, uom.code as uom_code, uom.fraction as uom_fraction, 
				tdi.selling_price as selling_price, do.deliver_branch, do.do_branch_id, 
				item_discount
				from do_open_items tdi
				left join do on do.id=do_id and do.branch_id=tdi.branch_id
				left join uom on uom.id=tdi.uom_id
				where do_id=$do_id and tdi.branch_id = $branch_id $owner_filter
				order by tdi.id";

		$q2=$con->sql_query($sql);
		while($r2=$con->sql_fetchrow($q2)){
			$r2['oi'] = 1;
			$r2['ctn_allocation'] = unserialize($r2['ctn_allocation']);
			$r2['pcs_allocation'] = unserialize($r2['pcs_allocation']);
			$r2['price_type'] = unserialize($r2['price_type']);
			$r2['selling_price_allocation'] = unserialize($r2['selling_price_allocation']);		
			$r2['custom_col'] = unserialize($r2['custom_col']);
			// gst
			if($form['is_under_gst'] && $r2['gst_id'] > 0){
				check_and_extend_gst_list($r2);
			}
		
			$do_items[]=$r2;
		}
		$con->sql_freeresult($q2);
	}
	//print_r($gst_list);
	//print_r($do_items);
    return $do_items;
}

function do_approval($do_id, $branch_id, $status, $auto_approve=false, $redirect=true){
 	global $con, $sessioninfo, $smarty, $config, $approval_status, $appCore;

	if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
		$con->sql_query("select group_concat(u separator ', ') as u from user where id in (".str_replace('-',',',$_REQUEST['on_behalf_of']).")");
		$on_behalf_of_u = $con->sql_fetchfield(0);
		$con->sql_query("select u from user where id = ".mi($_REQUEST['on_behalf_by'])." limit 1");
		$on_behalf_by_u = $con->sql_fetchfield(0);
		$approval_on_behalf = array(
			'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
			'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
			'on_behalf_of_u' => $on_behalf_of_u,
			'on_behalf_by_u' => $on_behalf_by_u,
		);
	}
	else {
		$approval_on_behalf = false;
	}
	
 	$form=$_REQUEST;

 	$approved=0;
 	if($auto_approve){
		$form=load_do_header($do_id, $branch_id);
	}	
 	else{
		check_must_can_edit($branch_id, $do_id, true);  // check whether it is still need approval
	}
 	
	$aid=$form['approval_history_id'];
	$approvals=$form['approvals'];

	if($status==1){
		$comment="Approved";
		$params = array();
		$params['approve'] = 1;
		$params['user_id'] = $sessioninfo['id'];
		$params['id'] = $aid;
		$params['branch_id'] = $branch_id;
		$params['update_approval_flow'] = true;
		if($auto_approve) $params['auto_approve'] = true;

		$is_last = check_is_last_approval_by_id($params, $con);
    	if($is_last)  $approved = 1;
	}
	else{
	  $comment= trim($form['comment']);
    $con->sql_query("update branch_approval_history set status=$status, approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id") or die(mysql_error());
  } 
	
	$upd = array();
	$upd['branch_id'] =  $form['branch_id'];
	$upd['approval_history_id'] = $aid;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	if ($approval_on_behalf) $comment .= " (by ".$on_behalf_by_u." on behalf of ".$on_behalf_of_u.")";
	$upd['log'] = $comment;
	
	if($_REQUEST['direct_approve_due_to_less_then_min_doc_amt'])	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
	if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	//$con->sql_query("update branch_approval_history set status=$status, approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id") or die(mysql_error());
	
    $q1=$con->sql_query("select * from do where id=$do_id and branch_id=$branch_id");
    $r1=$con->sql_fetchrow($q1);
	$r1['deliver_branch']=unserialize($r1['deliver_branch']);				
	$tmp_form['allowed_user']=unserialize($r1['allowed_user']);	
	
	// if HQ multiple branches DO
	if($r1['do_type'] == 'transfer' && $r1['deliver_branch'] && $approved){
		// create approval flow
		$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
		$report_prefix = $con->sql_fetchfield(0);
		$id_no = $report_prefix.sprintf('%05d', $do_id);
		$history_log = "approved from DO <a href=\"do.php?a=view&id=".mi($do_id)."&branch_id=".mi($branch_id)."&do_type=transfer\">$id_no</a>";
		
		$con->sql_query("select * from branch_approval_history where id=$aid and branch_id=$branch_id") or die(mysql_error());
		$app_history = $con->sql_fetchrow();
		
		// check consignment currency mode
		$currency_discount_params = array();
		if($config['consignment_modules'] && $config['masterfile_branch_region'] && $config['consignment_multiple_currency'] && $r1['exchange_rate']>1){
			$is_currency_mode = true;
			
			if($r1['price_indicate']==1)	$use_cost_indicate = true;
			
			if($use_cost_indicate)	$currency_multiply = 1;
			else	$currency_multiply = $r1['exchange_rate'];
			
			$currency_discount_params = array('currency_multiply'=>$currency_multiply);
			$currency_multiply_rate = 1/$r1['exchange_rate'];
			
			if($use_cost_indicate)	$foreign_currency_discount_params['currency_multiply'] = $currency_multiply_rate;

		}
	
		foreach($r1['deliver_branch'] as $k=>$v){
			$r1['do_branch_id']=$v;
			unset($r1['allowed_user']);
			$r1['allowed_user'][$v]=$tmp_form['allowed_user'][$v];
			$r1['allowed_user'] = serialize($r1['allowed_user']);
			$r1['deliver_branch']='';
			$r1['approved'] = $approved;
			$r1['status']=$status;
			$r1['hq_do_id']=$do_id;
			
			$con->sql_query("insert into do " . mysql_insert_by_field($r1, array('branch_id', 'user_id', 'do_branch_id','dept_id', 'status', 'approved', 'do_date', 'added', 'deliver_branch', 'remark','approval_history_id', 'po_no', 'hq_do_id','discount','do_markup','exchange_rate','allowed_user','no_use_credit_sales_cost'
			,'is_under_gst')));
			$new_do_id = $con->sql_nextid();
			
			// own approval history
			// branch_approval_history
			$upd = array();
			$upd['approval_flow_id'] = $app_history['approval_flow_id'];
			$upd['ref_table'] = $app_history['ref_table'];
			$upd['ref_id'] = $app_history['ref_id'];
			$upd['active'] = $app_history['active'];
			$upd['flow_approvals'] = $app_history['flow_approvals'];
			$upd['approvals'] = $app_history['approvals'];
			$upd['notify_users'] = $app_history['notify_users'];
			$upd['status'] = $app_history['status'];
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($upd)) or die(mysql_error());
			$new_aid = $con->sql_nextid();
			
			// branch_approval_history_items
			$upd = array();
			$upd['approval_history_id'] = $new_aid;
			$upd['user_id'] = $sessioninfo['id'];
			$upd['log'] = $history_log;
			$upd['timestamp'] = 'CURRENT_TIMESTAMP';
			$upd['status'] = 1;
			$upd['branch_id'] = $branch_id;
			$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			
			$con->sql_query("update do set approval_history_id=$new_aid where id=$new_do_id and branch_id=$branch_id") or die(mysql_error());
			
			$do_no=$appCore->doManager->assign_do_no($new_do_id, $branch_id);
			
			$q2=$con->sql_query("select do_items.*, uom.fraction as fraction
                                from do_items
                                left join uom on uom.id=uom_id
                                where do_id=$do_id and branch_id=$branch_id order by do_items.id");
			while ($item=$con->sql_fetchrow($q2)){
				$item['pcs_allocation']=unserialize($item['pcs_allocation']);
				$item['ctn_allocation']=unserialize($item['ctn_allocation']);
				$item['selling_price_allocation']=unserialize($item['selling_price_allocation']);
				$item['stock_balance2_allocation']=unserialize($item['stock_balance2_allocation']);
                $item['parent_stock_balance2_allocation']=unserialize($item['parent_stock_balance2_allocation']);
				$item['serial_no']=unserialize($item['serial_no']);

				foreach($item['pcs_allocation'] as $k1=>$v1){
					if($k1==$v){
						unset($item['sn']);
						$item['ctn']=$item['ctn_allocation'][$k1];
						$item['selling_price']=$item['selling_price_allocation'][$k1];
						$item['stock_balance2']=$item['stock_balance2_allocation'][$k1];
                        $item['parent_stock_balance2']=$item['parent_stock_balance2_allocation'][$k1];
						$item['sn'][$v]=$item['serial_no'][$v];
						if($item['sn'][$v]) $item['serial_no']=serialize($item['sn']);

						$item['pcs']=$v1;
						$item['do_id']=$new_do_id;
						
						$total_ctn+=$item['ctn'];		    						
						$total_pcs+=$item['pcs'];
						$cost = $item['cost_price'];
						
						// currency
						if($is_currency_mode){
							$foreign_cost = round($cost*$currency_multiply_rate,3);
						}
			
						if($r1['do_markup']){
							$r1['do_markup_arr'] = explode("+", $r1['do_markup']);
					        if($r1['markup_type']=='down'){
				                $r1['do_markup_arr'][0] *= -1;
				                $r1['do_markup_arr'][1] *= -1;
							}
					        if($r1['do_markup_arr'][0]){
								$cost = $cost * (1+($r1['do_markup_arr'][0]/100));
							}
							if($r1['do_markup_arr'][1]){
								$cost = $cost * (1+($r1['do_markup_arr'][1]/100));
							}
							
							// currency markup
							if($is_currency_mode){
								if($r1['do_markup_arr'][0]){
									$foreign_cost = $foreign_cost * (1+($r1['do_markup_arr'][0]/100));
								}
								if($r1['do_markup_arr'][1]){
									$foreign_cost = $foreign_cost * (1+($r1['do_markup_arr'][1]/100));
								}
							}
						}
						$row_qty = ($item['ctn'] * $item['fraction']) + $item['pcs'];
						$amt_ctn = $cost*$item['ctn'];
						$amt_pcs = ($cost/$item['fraction'])*$item['pcs'];
						
						$total_qty += $row_qty;
						$con->sql_query("insert into do_items " . mysql_insert_by_field($item, array('do_id','branch_id', 'sku_item_id','artno_mcode','uom_id','ctn','pcs', 'cost', 'cost_price', 
						'foreign_cost_price', 'selling_price','stock_balance1','stock_balance2','parent_stock_balance1','parent_stock_balance2', 'item_discount', 'serial_no','price_indicate'
						,'gst_id','gst_code','gst_rate','display_cost_price_is_inclusive','display_cost_price','bom_id','bom_ref_num','bom_qty_ratio','custom_col')));
					}	
				}
			}
			$con->sql_freeresult($q2);

			// split for open items
			$q2=$con->sql_query("select do_open_items.*, uom.fraction as fraction
                                from do_open_items
                                left join uom on uom.id=uom_id
                                where do_id=$do_id and branch_id=$branch_id order by do_open_items.id");
			while ($open_item=$con->sql_fetchrow($q2)){
				$open_item['pcs_allocation']=unserialize($open_item['pcs_allocation']);
				$open_item['ctn_allocation']=unserialize($open_item['ctn_allocation']);
				$open_item['selling_price_allocation']=unserialize($open_item['selling_price_allocation']);

				foreach($open_item['pcs_allocation'] as $k2=>$v2){
					if($k2==$v){
						$open_item['ctn']=$open_item['ctn_allocation'][$k2];
						$open_item['selling_price']=$open_item['selling_price_allocation'][$k2];

						$open_item['pcs']=$v2;
						$open_item['do_id']=$new_do_id;
						
						$total_ctn+=$open_item['ctn'];		    						
						$total_pcs+=$open_item['pcs'];
						$cost = $open_item['cost_price'];
						
						// currency
						if($is_currency_mode){
							$foreign_cost = round($cost*$currency_multiply_rate,3);
						}
						
						if($r1['do_markup']){
							$r1['do_markup_arr'] = explode("+", $r1['do_markup']);
					        if($r1['markup_type']=='down'){
				                $r1['do_markup_arr'][0] *= -1;
				                $r1['do_markup_arr'][1] *= -1;
							}
					        if($r1['do_markup_arr'][0]){
								$cost = $cost * (1+($r1['do_markup_arr'][0]/100));
							}
							if($r1['do_markup_arr'][1]){
								$cost = $cost * (1+($r1['do_markup_arr'][1]/100));
							}
							
							// currency markup
							if($is_currency_mode){
								if($r1['do_markup_arr'][0]){
									$foreign_cost = $foreign_cost * (1+($r1['do_markup_arr'][0]/100));
								}
								if($r1['do_markup_arr'][1]){
									$foreign_cost = $foreign_cost * (1+($r1['do_markup_arr'][1]/100));
								}
							}
						}
						$row_qty = ($open_item['ctn']*$open_item['fraction'])+$open_item['pcs'];
						
						$amt_ctn = $cost*$open_item['ctn'];
						$amt_pcs = ($cost/$open_item['fraction'])*$open_item['pcs'];
						
						$total_qty += $row_qty;
						
						$con->sql_query("insert into do_open_items " . mysql_insert_by_field($open_item, array('do_id','branch_id', 'sku_item_id','artno_mcode','uom_id','ctn','pcs', 'cost_price', 
						'foreign_cost_price', 'selling_price','item_discount','gst_id','gst_code','gst_rate','display_cost_price_is_inclusive','display_cost_price')));
					}
				}
			}
			$con->sql_freeresult($q2);

			$upd2['total_qty'] = $total_qty;
			$upd2['total_ctn'] = $total_ctn;
			$upd2['total_pcs'] = $total_pcs;
			
			// currency
			if($is_currency_mode){
				$upd2['sub_total_foreign_inv_amt'] = $sub_total_foreign_inv_amt;
				$upd2['total_foreign_inv_amt'] = $total_foreign_inv_amt;
			}else{
				$upd2['sub_total_foreign_inv_amt'] = 0;
				$upd2['total_foreign_inv_amt'] = 0;
				$upd2['total_foreign_amount'] = 0;
			}
			
			$con->sql_query("update do set ".mysql_update_by_field($upd2)." where id=$new_do_id and branch_id=$branch_id");
						
			if($config['consignment_modules']){
				update_do_sheet_price_type($branch_id, $new_do_id);
			}
			
			// recalculate all amt
			auto_update_do_all_amt($branch_id, $new_do_id);
			
			$total_qty = 0;	
			$total_ctn=0;
			$total_pcs=0;	
			$total_amt=0;
			$total_foreign_amount = 0;
			$sub_total_inv_amt = 0;
			$total_inv_amt = 0;
			$sub_total_foreign_inv_amt = 0;
			$total_foreign_inv_amt = 0;
		}
		$update_command=" status=$status, approved=$approved, active=0 ";
	}	
	// branches DO or open_info DO
	elseif(($r1['do_branch_id'] || $r1['open_info']['name'] || $r1['do_type']=='credit_sales') && $approved){
		$do_no=$appCore->doManager->assign_do_no($do_id, $branch_id);
		$update_command=" do_no='".$do_no."', deliver_branch='', status=$status, approved=$approved ";
	}	
	else{
		$update_command=" status=$status, approved=$approved ";
	}
	
	$con->sql_query("update do set $update_command where id=$do_id and branch_id=$branch_id"); 

	if($r1['create_type']==4){
		// update the delivered qty
        $con->sql_query("select * from sales_order where order_no=".ms($r1['ref_no']));
		$sales_order = $con->sql_fetchrow();
		$con->sql_freeresult();
		if($sales_order){
            update_sales_order_do_qty($sales_order['id'], $sales_order['branch_id'], $sales_order);
		}
	}
	//send_pm_to_user($do_id, $branch_id, $aid, $status);
	$to = get_pm_recipient_list2($do_id,$aid,$status,'approval',$branch_id,'do');
	$status_str = ($is_last || $status != 1) ? $approval_status[$status] : '';
	send_pm2($to, "Delivery Order Approval (ID#$do_id) $status_str", "do.php?page=$form[do_type]&a=view&id=$do_id&branch_id=$branch_id", array('module_name'=>'do'));
			
	if ($approved)
		$status_msg="Fully Approved";
	elseif ($status==1)
		$status_msg="Approved";
	elseif ($status==2)
		$status_msg="Rejected";
	elseif ($status==5)
		$status_msg="Cancelled/Terminated";
	else
	    die("WTF?");
	
	log_br($sessioninfo['id'], 'DELIVERY ORDER', $do_id, "DO $status_msg by $sessioninfo[u] (ID#$do_id)");
	if($redirect){
        if($auto_approve)
			header("Location: /do.php?page=$form[do_type]&t=approve&save_id=$do_id");
		else{
			if ($approval_on_behalf) {
				header("Location: /stucked_document_approvals.php?m=do");
				exit;
			}
		    if($config['consignment_modules']){
                header("Location: /do_approval.php?t=$_REQUEST[a]&id=$id&branch_id=$branch_id");
			}else{
                header("Location: /do_approval.php?t=$form[a]&id=$do_id");
			}
		}	
		exit;
	}
}

function assign_inv_no($do_id, $branch_id){
	global $con, $config;
	$type_postfix_list = array('transfer'=>'','credit_sales'=>'/D','open'=>'/C');
	
	// do type
	$con->sql_query("select do_no,do_type from do where id=$do_id and branch_id=$branch_id") or die(mysql_error());
	$do_no = $con->sql_fetchfield('do_no');
	$do_type = $con->sql_fetchfield('do_type');
	$type_postfix = $type_postfix_list[$do_type];
	$con->sql_freeresult();
	
	// report prefix
	$con->sql_query("select report_prefix, ip from branch where id=$branch_id");
	$report_prefix = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	// lookup for max length of inv no
	$con->sql_query("select max(length(inv_no)) as mx_lgth from do where branch_id = ".mi($branch_id)." and do_type=".ms($do_type)." and inv_no like '$report_prefix[0]%'");
	$max_length = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	if($config['do_invoice_separate_number']){
		if($max_length > 0) $filter = " and length(inv_no) >= ".mi($max_length);
        $con->sql_query("select max(inv_no) from do where branch_id=".mi($branch_id)." and inv_no like '$report_prefix[0]%' and do_type=".ms($do_type).$filter) or die(mysql_error());

	    $r = $con->sql_fetchrow();
		$con->sql_freeresult();

	    if (!$r)
			$n = 1;
		else{
	        list($num,$dummy) = explode("/",$r[0]);
			$n = preg_replace("/^".$report_prefix[0]."/","", $num)+1;
		}

	    /*$inv_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
	    while(!$con->sql_query("update do set inv_no='$inv_no', last_update=CURRENT_TIMESTAMP where id=$do_id and branch_id = $branch_id",false,false)){
			$n++;
			$inv_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
		}*/
	}else{
	    //$n = $do_id;
	    list($real_do_no,$dummy) = explode("/",$do_no);
		$n = preg_replace("/^".$report_prefix[0]."/","", $real_do_no);
	    
	}
    $inv_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
    while(!$con->sql_query("update do set inv_no='$inv_no', last_update=CURRENT_TIMESTAMP where id=$do_id and branch_id = $branch_id",false,false)){
		$n++;
		$inv_no = $report_prefix[0] . sprintf("%05d", $n).$type_postfix;
	}
    
	return $inv_no;
}

/*
function send_pm_to_user($do_id, $branch_id, $aid, $status){
	global $con, $sessioninfo, $smarty, $approval_status;
	// get the PM list
	$con->sql_query("select notify_users 
from branch_approval_history where id=$aid and branch_id = $branch_id");
	$r = $con->sql_fetchrow();
	
	$recipients = $r[0];
	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
	$to = preg_split("/\|/", $recipients);
	
	// send pm
	send_pm($to, "Delivery Order Approval (ID#$do_id) $approval_status[$status]", "do.php?page=$form[do_type]&a=view&id=$do_id&branch_id=$branch_id");

}
*/

function init_selection(){
	global $con, $sessioninfo, $smarty, $config;
	
	$con->sql_query("select id, code, description, report_prefix, deliver_to, region, address, phone_1, phone_2, contact_email from branch where active=1 order by sequence, code");
	$smarty->assign("branch", $con->sql_fetchrowset());
	
	$sql = $con->sql_query("select id, code, description, report_prefix, deliver_to, region from branch where active=1 order by sequence,code");
	while($r = $con->sql_fetchrow($sql)){
		$currency_code = $config['masterfile_branch_region'][$r['region']]['currency'];
		
		if(trim($currency_code)){
			$r['currency_code'] = strtoupper(trim($currency_code));
			$sql1 = $con->sql_query("select exchange_rate from consignment_forex where currency_code = ".ms($r['currency_code']));
			$cf = $con->sql_fetchrow($sql1);
			
			if($cf) $r['exchange_rate'] = $cf['exchange_rate'];
		}
        $all_branch[] = $r;
	}
	$con->sql_freeresult();
	$smarty->assign("all_branch", $all_branch);
	//$smarty->assign("all_branch", $con->sql_fetchrowset());
	
	$con->sql_query("select id, code, fraction from uom where active order by code");
	$smarty->assign("uom", $con->sql_fetchrowset());
	
	if($config['do_allow_credit_sales']){
		$con->sql_query("select * from debtor where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			if($_REQUEST["do_type"] == "credit_sales"){
				$block=unserialize($r["credit_sales_do_block_list"]);
				if (!isset($block[$sessioninfo["branch_id"]])){
					$debtor[$r['id']] = $r;
				}
			}else{
				$debtor[$r['id']] = $r;
			}
		}
		$smarty->assign("debtor", $debtor);
	}	
}	

/*function load_do_items2($do_id, $branch_id, $use_tmp = false){
	global $con, $sessioninfo;

 	$form=$_REQUEST;
	$owner_filter='';
	$chk_branch = '';
	
	if($use_tmp){
		$table="tmp_do_items";
		$owner_filter=" and tdi.user_id=$sessioninfo[id] ";
		$owner_filter2 = " and user_id=$sessioninfo[id] ";
	}
	else{
		$table="do_items";
		$chk_branch = "and tdi.branch_id=$branch_id";
	}
	$sql = "select tdi.*, sku_items.sku_item_code, sku_items.description as description, sku_items.artno, sku_items.mcode, uom.id as uom_id, uom.code as uom_code, uom.fraction as uom_fraction, tdi.selling_price as selling_price, do.deliver_branch, do.do_branch_id ,sku_items.artno,price_type,rcv_pcs,stock_balance1,stock_balance2,stock_balance2_allocation
from $table tdi
left join do on do.id=do_id and do.branch_id=tdi.branch_id
left join sku_items on tdi.sku_item_id=sku_items.id
left join uom on uom.id=tdi.uom_id
where do_id=$do_id $chk_branch $owner_filter
order by tdi.id";
    //print $sql;
	$q1=$con->sql_query($sql);
	while($r1=$con->sql_fetchrow($q1)){
		$r1['ctn_allocation'] = unserialize($r1['ctn_allocation']);
		$r1['pcs_allocation'] = unserialize($r1['pcs_allocation']);
		$r1['price_type'] = unserialize($r1['price_type']);
		$r1['stock_balance2_allocation'] = unserialize($r1['stock_balance2_allocation']);
		
		if($use_tmp){
			$do_bid = intval($_REQUEST['do_bid']);
			
			// refresh latest selling price if we are loading from tmp_do_items
			$tmp_sell=get_item_selling($r1['sku_item_id'], $deliver_branch, $do_bid);
			$r1 = array_merge($r1, $tmp_sell);
			$con->sql_query("update $table set selling_price_allocation=".sz($tmp_sell['selling_price_allocation']).", selling_price=".mf($tmp_sell['selling_price']).",price_type=".sz($tmp_sell['price_type'])." where id=$r1[id] $chk_branch $owner_filter2");
		}
		else{
			//echo "Old = saved<br>";
			$r1['selling_price_allocation'] = unserialize($r1['selling_price_allocation']);
		}

		$do_items[]=$r1;
	}
    return $do_items;
}*/

function do_reset($do_id,$branch_id){
	global $con,$sessioninfo,$config,$smarty,$LANG,$appCore;

	$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
	
	if($sessioninfo['level']<$required_level && !privilege('DO_ALLOW_USER_RESET')){
        js_redirect(sprintf('Forbidden', 'DO', BRANCH_CODE), "/do.php");
	}
	
	$form=load_do_header($do_id, $branch_id);
	if(!$form['approved']){
		header("Location: /do.php?page=$form[do_type]&save_id=$do_id&msg=DO Already Reset");
		exit;
	}
	
	if($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['do_date']);
		if($is_month_closed){
			js_redirect($LANG['MONTH_DOCUMENT_IS_CLOSED'], "/do.php");
		}
	}

	//add reset config
	$check_date = strtotime($form['do_date']);

	if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_limit = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));

		if ($check_date<$reset_limit){
  	   		$errm['top'][] = $LANG['DO_DATE_RESET_LIMIT'];
		}

	}

	$_REQUEST['do_id'] = $do_id;
	$_REQUEST['branch_id'] = $branch_id;
	if($config['enable_sn_bn']) $sn_err = serial_no_handler("reset");
	
	if($sn_err) $errm['top'][] = $LANG['DO_SN_RESET_ERROR'];
	
	if($errm){
		$smarty->assign("errm", $errm);
		return true;
	}
	
	$aid=$form['approval_history_id'];

	// check duplicate approval history id
	$con->sql_query("select count(*) from do where approval_history_id=$aid") or die(mysql_error());
	if($con->sql_fetchfield(0)>1){
		$con->sql_query("select * from branch_approval_history where id=$aid and branch_id=$branch_id") or die(mysql_error());
		$app_history = $con->sql_fetchrow();
		// use new aid
		// own approval history
		// branch_approval_history
		$upd = array();
		$upd['approval_flow_id'] = $app_history['approval_flow_id'];
		$upd['ref_table'] = $app_history['ref_table'];
		$upd['ref_id'] = $app_history['ref_id'];
		$upd['active'] = $app_history['active'];
		$upd['flow_approvals'] = $app_history['flow_approvals'];
		$upd['approvals'] = $app_history['approvals'];
		$upd['notify_users'] = $app_history['notify_users'];
		$upd['status'] = $app_history['status'];
		$upd['branch_id'] = $branch_id;
		$upd['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into branch_approval_history ".mysql_insert_by_field($upd)) or die(mysql_error());
		$new_aid = $con->sql_nextid();
		
		// branch_approval_history_items
		$upd = array();
		$upd['approval_history_id'] = $new_aid;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['log'] = "Approval History clear by system";
		$upd['timestamp'] = 'CURRENT_TIMESTAMP';
		$upd['status'] = 1;
		$upd['branch_id'] = $branch_id;
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		
		$con->sql_query("update do set approval_history_id=$new_aid where id=$do_id and branch_id=$branch_id") or die(mysql_error());
		$aid = $new_aid;
	}
	$approvals=$form['approvals'];
	$status = 0;
	
	$upd = array();
	$upd['approval_history_id'] = $aid;
	$upd['branch_id'] = $branch_id;
	$upd['user_id'] = $sessioninfo['id'];
	$upd['status'] = $status;
	$upd['log'] = $_REQUEST['reason'];
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
	$con->sql_query("update branch_approval_history set status=$status where id = $aid and branch_id = $branch_id") or die(mysql_error());
	
	if($form['checkout']&&$form['do_no']&&$form['do_type']=='transfer'&&!$config['do_skip_generate_grn']&&$config['single_server_mode']){
	    $do_no = $form['do_no'];
	    
        // grr items
		$q_grr_items = $con->sql_query("select * from grr_items where doc_no=".ms($do_no)." and type='DO' and grn_used=1") or die(mysql_error());
		while($grr_items = $con->sql_fetchrow($q_grr_items)){
            $grr_item_id = mi($grr_items['id']);
			$grr_id = mi($grr_items['grr_id']);
			
			if($config['use_grn_future']){
				$filter_grn = "grn.grr_id=".$grr_id;
			}else{
				$filter_grn = "grn.grr_item_id=".$grr_item_id;
			}
			
			// get sku item id in grn_items
			$sid_list = array();
			$q_gi = $con->sql_query("select distinct gi.sku_item_id
			from grn_items gi
			left join grn on grn.branch_id=gi.branch_id and grn.id=gi.grn_id
			where grn.branch_id=".mi($grr_items['branch_id'])." and $filter_grn");
			while($gi = $con->sql_fetchassoc($q_gi)){
				$sid_list[] = mi($gi['sku_item_id']);
			}
			$con->sql_freeresult($q_gi);
			
			// update grn
			$con->sql_query("update grn set active=0,status=0 where $filter_grn and branch_id=".mi($grr_items['branch_id'])) or die(mysql_error());
			// update grr
			$con->sql_query("update grr set active=0,status=0 where id=".mi($grr_items['grr_id'])." and branch_id=".mi($grr_items['branch_id'])) or die(mysql_error());
			
			if($sid_list){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id = ".mi($grr_items['branch_id'])." and sku_item_id in (".join(',', $sid_list).")");
			}
		}
		$con->sql_freeresult($q_grr_items);
	}
		
	// update sku items cost
	$sid_list = array();
	$con->sql_query("select distinct sku_item_id 
	from do_items
	where branch_id=$branch_id and do_id=$do_id");
	while($r = $con->sql_fetchassoc()){
		$sid_list[] = mi($r['sku_item_id']);
	}
	$con->sql_freeresult();
	
	if($sid_list){
		$con->sql_query("update sku_items_cost set changed=1 where branch_id = $branch_id and sku_item_id in (".join(',', $sid_list).")");
	}
	
	
	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$upd['approved'] = 0;
	$upd['checkout'] = 0;
	$upd['active'] = 1;
	$upd['paid'] = 0;
	$con->sql_query("update do set ".mysql_update_by_field($upd)." where id=$do_id and branch_id=$branch_id") or die(mysql_error());
	
	log_br($sessioninfo['id'], 'DELIVERY ORDER', $do_id, sprintf("DO Reset ($form[do_no])",$do_id));
	
	header("Location: /do.php?page=$form[do_type]&t=reset&save_id=$do_id");
}

function get_sales_trend($branch_id,$sid){
	global $con, $sessioninfo, $appCore;
	
	$data = array();
	$data['sales_trend'] = $appCore->skuManager->getSKUSalesTrend($branch_id,$sid); // call it from skuManager.php
	
	if ($data['sales_trend']) ksort($data['sales_trend']['qty']);

	return $data;
}

function update_sales_order_do_qty($sales_order_id, $branch_id, $sales_order = array()){
	global $con;
	
	if(!$sales_order){
        $con->sql_query("select * from sales_order where id=$sales_order_id and branch_id=$branch_id");
		$sales_order = $con->sql_fetchrow();
	}

    // mark sales order as delivered
	$con->sql_query("update sales_order set delivered=1 where id=$sales_order_id and branch_id=$branch_id");  

	// update delivered item qty - unsaved, waiting approve, approved and rejected
	$con->sql_query("select di.sku_item_id,sum((di.ctn*uom.fraction)+di.pcs) as total_pcs
from do
left join do_items di on do.id=di.do_id and do.branch_id=di.branch_id
left join uom on uom.id=di.uom_id
where do.ref_tbl='sales_order' and do.ref_no=".ms($sales_order['order_no'])." and do.active=1 and do.status in (0,1,2)
group by di.sku_item_id");
	$so_item_do_qty_array = array();
	while($r = $con->sql_fetchrow()){
		$sid = mi($r['sku_item_id']);
		$do_qty = mf($r['total_pcs']);
		$so_item_do_qty_array[$sid] += $do_qty;
		//$con->sql_query("update sales_order_items set do_qty=$do_qty where branch_id=$branch_id and sales_order_id=$sales_order_id and sku_item_id=$sid");
	}
	$q_soi = $con->sql_query("select * from sales_order_items where branch_id=$branch_id and sales_order_id=$sales_order_id");
	while($r = $con->sql_fetchrow($q_soi)){
	    $item_id = mi($r['id']);
        $sid = mi($r['sku_item_id']);
        $do_qty = mf($so_item_do_qty_array[$sid]);
        $con->sql_query("update sales_order_items set do_qty=$do_qty where id=$item_id and branch_id=$branch_id and sales_order_id=$sales_order_id and sku_item_id=$sid");
	}
}

function check_must_can_edit($branch_id, $do_id, $is_approval_screen = false, $is_checkout_screen = false){
	global $con, $LANG;

    $con->sql_query("select active, status, approved,checkout from do where branch_id=".mi($branch_id)." and id=".mi($do_id));

	if($r = $con->sql_fetchrow()){  // invoice exists
		if(!$r['active']){  // inactive
            display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_INACTIVE'], $do_id));
		}elseif ($r['status']==4 || $r['status']==5){    // canceled or deleted
		    display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_ALREADY_CANCELED_OR_DELETED'], $do_id));
		}else{
			if($is_approval_screen){
                if($r['approved']){    // already approved
				    display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_ALREADY_CONFIRM_OR_APPROVED'], $do_id));
				}
			}elseif($is_checkout_screen){
                if ($r['approved'] == 1 && $r['status'] == 1 && $r['checkout'] == 1)
                    display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_ALREADY_CHECKOUT'], $do_id));
				elseif($r['approved'] != 1 || $r['status'] != 1 || $r['checkout'] != 0)
                    display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_CANNOT_CHECKOUT'], $do_id));
			}elseif(($r['status']>0 && $r['status'] !=2) || $r['approved']){    // confimred or approved
			    display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_ALREADY_CONFIRM_OR_APPROVED'], $do_id));
			}
		}
	}else{
        display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['DO_NOT_FOUND'], $do_id)); // invoice not found
	}
	$con->sql_freeresult();
}

function serial_no_handler($process_type){
	global $con, $sessioninfo, $config, $smarty;

	$form = $_REQUEST;

	if($form['do_id'] && $form['branch_id']) $filter = "do.id=".mi($form['do_id'])." and do.branch_id = ".mi($form['branch_id']);
	else $filter = "do_no=".ms($form['do_no']);

	$q1=$con->sql_query("select *
						 from do_items
						 left join do on do_id = do.id and do_items.branch_id = do.branch_id
						 where $filter and do_items.serial_no != '' and do_items.serial_no is not null
						 order by do_items.id");

	if($process_type == "confirm"){ // this process carry on when the process type is confirm
		while($r1 = $con->sql_fetchassoc($q1)){
			$serial_no = unserialize($r1['serial_no']);

			if(count($serial_no) == 0) continue; // skip all the do items that does not have S/N

			if($r1['do_branch_id']>0 && !$r1['open_info'] && $r1['do_type']!='credit_sales'){ // is from Transfer DO
				foreach($serial_no as $bid=>$sn_list){
					$curr_sn_list = explode("\n", $sn_list);
		
					foreach($curr_sn_list as $r=>$sn){
						$q2 = $con->sql_query("select * from pos_items_sn pis where pis.sku_item_id = ".mi($r1['sku_item_id'])." and pis.serial_no = ".ms($sn)." and pis.branch_id = ".mi($r1['branch_id']));
						$pis = $con->sql_fetchassoc($q2);
		
						if($con->sql_numrows($q2) > 0){
							$upd = array();
							$upd['last_update'] = "CURRENT_TIMESTAMP";
							if($config['do_skip_generate_grn']){ // the GRN hasn't being created, so need to update the status only 
								$upd['status'] = "Transition";
							}else{ // update the located branch since the GRN has been created automatically
								$upd['located_branch_id'] = $bid;
							}
							
							$q3 = $con->sql_query("update pos_items_sn set ".mysql_update_by_field($upd)." where id = ".mi($pis['id'])." and branch_id = ".mi($pis['branch_id']));
		
							// insert S/N history
							if($con->sql_affectedrows($q3)>0){
								$his_ins['pisn_id'] = $pis['id'];
								$his_ins['branch_id'] = $pis['branch_id'];
								$his_ins['sku_item_id'] = $pis['sku_item_id'];
								$his_ins['located_branch_id'] = $pis['located_branch_id'];
								$his_ins['serial_no'] = $pis['serial_no'];
								$his_ins['remark'] = "DO checkout - confirmed";
								$his_ins['status'] = $pis['status'];
								$his_ins['active'] = $pis['active'];
								$his_ins['added'] = "CURRENT_TIMESTAMP";
								$his_ins['user_id'] = $sessioninfo['id'];
		
								$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
							}
						}
						$con->sql_freeresult($q2);
					}
				}
			}else{ // is from Cash and Credit sales use
				$q2 = $con->sql_query("select max(pos_id) as pos_id from sn_info where counter_id = -2 and branch_id = ".mi($r1['branch_id'])." and date = ".ms($r1['do_date']));
				$sni = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
				$sni['pos_id'] = $sni['pos_id']+1;
	
				for($i = 0; $i < count($serial_no['sn']); $i++){
					$q3 = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($r1['sku_item_id'])." and serial_no = ".ms($serial_no['sn'][$i])." and located_branch_id = ".mi($r1['branch_id']));
					$pis = $con->sql_fetchassoc($q3);
	
					if($con->sql_numrows($q3) > 0){
						// update into primary table
						$pis_upd = array();
						$pis_upd['pos_id'] = $sni['pos_id'];
						$pis_upd['pos_item_id'] = $i+1;
						$pis_upd['pos_branch_id'] = $r1['branch_id'];
						$pis_upd['date'] = $r1['do_date'];
						$pis_upd['counter_id'] = -2;
						$pis_upd['status'] = "Sold";
						$pis_upd['active'] = 1;
						$pis_upd['last_update'] = "CURRENT_TIMESTAMP";
						$q4 = $con->sql_query("update pos_items_sn set ".mysql_update_by_field($pis_upd)." where id = ".mi($pis['id'])." and branch_id = ".mi($pis['branch_id']));
	
						// insert serial no info & history
						if($con->sql_affectedrows($q4)){
							$sn_info['pos_id'] = $sni['pos_id'];
							$sn_info['item_id'] = $i+1;
							$sn_info['branch_id'] = $r1['branch_id'];
							$sn_info['date'] = $r1['do_date'];
							$sn_info['counter_id'] = -2;
							$sn_info['sku_item_id'] = $r1['sku_item_id'];
							$sn_info['nric'] = $serial_no['nric'][$i];
							$sn_info['name'] = $serial_no['name'][$i];
							$sn_info['address'] = $serial_no['address'][$i];
							$sn_info['contact_no'] = $serial_no['cn'][$i];
							$sn_info['email'] = $serial_no['email'][$i];
							if($serial_no['we'][$i]) $sn_info['warranty_expired'] = date("Y-m-d", strtotime("+".$serial_no['we'][$i]." ".$serial_no['we_type'][$i], strtotime($r1['do_date'])));
							$sn_info['serial_no'] = $serial_no['sn'][$i];
							$sn_info['active'] = 1;
	
							$con->sql_query("insert into sn_info ".mysql_insert_by_field($sn_info));
	
							// insert S/N history
							$his_ins['pisn_id'] = $pis['id'];
							$his_ins['branch_id'] = $pis['branch_id'];
							$his_ins['sku_item_id'] = $pis['sku_item_id'];
							$his_ins['located_branch_id'] = $pis['located_branch_id'];
							$his_ins['serial_no'] = $serial_no['sn'][$i];
							$his_ins['remark'] = "DO checkout - confirmed";
							$his_ins['status'] = "Sold";
							$his_ins['active'] = 1;
							$his_ins['added'] = "CURRENT_TIMESTAMP";
							$his_ins['user_id'] = $sessioninfo['id'];
	
							$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
						}
					}
					$con->sql_freeresult($q3);
				}
			}
		}
	}elseif($process_type == "reset"){ // while doing reset from complete DO checkout
		$serial_no_terminate = false;

		while($r1 = $con->sql_fetchassoc($q1)){
			$serial_no = unserialize($r1['serial_no']);

			if(count($serial_no) == 0) continue; // skip all the do items that does not have S/N

			if($r1['do_branch_id']>0 && !$r1['open_info'] && $r1['do_type']!='credit_sales'){ // revert all S/N from Transfer DO
				foreach($serial_no as $bid=>$sn_list){
					$curr_sn_list = explode("\n", $sn_list);
	
					if($config['do_skip_generate_grn']) $filter = " and pis.status = 'Transition' and pis.located_branch_id = ".mi($r1['branch_id']);
					else $filter = " and pis.status = 'Available' and pis.located_branch_id = ".mi($bid);
					foreach($curr_sn_list as $r=>$sn){
						$q2 = $con->sql_query("select * from pos_items_sn pis where pis.sku_item_id = ".mi($r1['sku_item_id'])." and pis.serial_no = ".ms($sn).$filter);

						if($con->sql_numrows($q2) == 0){
							$serial_no_terminate = true;
							break;
						}
						$con->sql_freeresult($q2);
					}
					if($serial_no_terminate) return true;
				}
				
				foreach($serial_no as $bid=>$sn_list){
					$curr_sn_list = explode("\n", $sn_list);
	
					foreach($curr_sn_list as $r=>$sn){
						$q2 = $con->sql_query("select * from pos_items_sn pis where pis.sku_item_id = ".mi($r1['sku_item_id'])." and pis.serial_no = ".ms($sn)." and pis.branch_id = ".mi($r1['branch_id']));
						$pis = $con->sql_fetchassoc($q2);
		
						if($con->sql_numrows($q2) > 0){
							$upd = array();
							$upd['last_update'] = "CURRENT_TIMESTAMP";
							if($config['do_skip_generate_grn']){ // the GRN hasn't being created, so need to update the status only 
								$upd['status'] = "Available";
							}else{ // update the located branch since the GRN has been created automatically
								$upd['located_branch_id'] = $r1['branch_id'];
							}
							
							$q3 = $con->sql_query("update pos_items_sn set ".mysql_update_by_field($upd)." where id = ".mi($pis['id'])." and branch_id = ".mi($form['branch_id']));

							// insert S/N history
							if($con->sql_affectedrows($q3)>0){
								$his_ins['pisn_id'] = $pis['id'];
								$his_ins['branch_id'] = $pis['branch_id'];
								$his_ins['sku_item_id'] = $pis['sku_item_id'];
								$his_ins['located_branch_id'] = $pis['located_branch_id'];
								$his_ins['serial_no'] = $pis['serial_no'];
								$his_ins['remark'] = "DO checkout - cancelled";
								$his_ins['status'] = $pis['status'];
								$his_ins['active'] = $pis['active'];
								$his_ins['added'] = "CURRENT_TIMESTAMP";
								$his_ins['user_id'] = $sessioninfo['id'];
		
								$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
							}
						}
						$con->sql_freeresult($q2);
					}
				}
			}else{ // revert all S/N from Cash and Credit sales
				for($i = 0; $i < count($serial_no['sn']); $i++){
					$q2 = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($r1['sku_item_id'])." and serial_no = ".ms($serial_no['sn'][$i])." and located_branch_id = ".mi($r1['branch_id']));
					$pis = $con->sql_fetchassoc($q2);
					$con->sql_freeresult($q2);
	
					$q3 = $con->sql_query("update pos_items_sn set status='Available', last_update=CURRENT_TIMESTAMP where id = ".mi($pis['id'])." and branch_id = ".mi($pis['branch_id']));
	
					// revert serial no info's status become inactive and insert history					
					if($con->sql_affectedrows($q3) > 0){
						$con->sql_query("update sn_info set active = 0 where counter_id = -2 and branch_id = ".mi($r1['branch_id'])." and sku_item_id = ".mi($r1['sku_item_id'])." and date = ".ms($r1['do_date'])." and serial_no = ".ms($serial_no['sn'][$i]));
						
						// insert S/N history
						$his_ins['pisn_id'] = $pis['id'];
						$his_ins['branch_id'] = $pis['branch_id'];
						$his_ins['sku_item_id'] = $pis['sku_item_id'];
						$his_ins['located_branch_id'] = $pis['located_branch_id'];
						$his_ins['serial_no'] = $serial_no['sn'][$i];
						$his_ins['remark'] = "DO checkout - cancelled";
						$his_ins['status'] = "Available";
						$his_ins['active'] = 0;
						$his_ins['added'] = "CURRENT_TIMESTAMP";
						$his_ins['user_id'] = $sessioninfo['id'];
	
						$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
					}
				}
			}
		}
	}
}

function update_do_sheet_price_type($bid, $do_id){
	global $con, $config;
	
	if(!$config['consignment_modules'])	return false;
	
	$bid = mi($bid);
	$do_id = mi($do_id);
	
	if(!$bid || !$do_id)	return false;
	
	$q_di = $con->sql_query("select price_type from do_items where branch_id=$bid and do_id=$do_id");
	$price_type_list = array();
	while($r = $con->sql_fetchassoc($q_di)){
		$r['price_type'] = unserialize($r['price_type']);

		if(!$r['price_type']){
			if(!in_array('', $price_type_list))	$price_type_list[] = '';
			continue;
		}	
		
		foreach($r['price_type'] as $tmp_bid=>$pt){
			if(!in_array($pt, $price_type_list))	$price_type_list[] = $pt;	
		}
		
		if(count($price_type_list)>1){	// found more than 1 price type
			break;
		}
	}
	$con->sql_freeresult($q_di);
	//print_r($price_type_list);exit;
	// more than 1 price type, save empty
	$sheet_price_type = count($price_type_list)>1 ? '' : $price_type_list[0];
	
	$con->sql_query("update do set sheet_price_type=".ms($sheet_price_type)." where branch_id=$bid and id=$do_id");
}

function sku_multiple_selling_price_handler($form=array()){
	global $config, $con, $smarty, $sessioninfo;
	
	$q1 = $con->sql_query("select allow_mprice from user where id = ".mi($sessioninfo['id']));
	$user_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	$debtor_mprice_type = '';
	if ($form['do_type'] == 'credit_sales' && $form['id'] && $form['branch_id']) {
		$q1 = $con->sql_query("select debtor.debtor_mprice_type from do left join debtor on do.debtor_id = debtor.id where do.id = ".mi($form['id'])." and do.branch_id = ".mi($form['branch_id'])." limit 1");
		$r1 = $con->sql_fetchassoc($q1);
		if ($r1) $debtor_mprice_type = $r1['debtor_mprice_type'];
		$con->sql_freeresult($q1);
	}
	
	$allow_mprice = unserialize($user_info['allow_mprice']);
	
	foreach ($config['sku_multiple_selling_price'] as $row=>$data){
		if($data == "Cost" || $data == "Selling (Normal)" || $data == "Last DO" || $data == "PO Cost" || preg_match("/member/", $data) || $form['price_indicate'] == $row) continue;

		if($form['price_indicate'] == "hqselling" || ($config['do_enable_hq_selling'] && BRANCH_CODE == "HQ" && $row == "hqselling")) continue;
		
		if ($data == ('Selling - '.$debtor_mprice_type)) continue;
		
		$mprice_matched = 0;
		if($allow_mprice){
			foreach($allow_mprice as $user_mprice=>$dummy){
				if(preg_match("/$user_mprice/", $data)){
					$mprice_matched = 1;
					break;
				}
			}
		}
		
		if(!$mprice_matched){
			unset($config['sku_multiple_selling_price'][$row]);
		}
		if ($allow_mprice) {
			if (array_key_exists('not_allow',$allow_mprice)) {
				unset($config['sku_multiple_selling_price'][$row]);
			}
		}
	}
	
	$disallowed_mprice = '';
	if ($debtor_mprice_type && $allow_mprice) {
		if (array_key_exists('not_allow',$allow_mprice) || !array_key_exists($debtor_mprice_type,$allow_mprice)) {
			$disallowed_mprice = $debtor_mprice_type;
		}
	}
	if (!$allow_mprice) {
		$disallowed_mprice = $debtor_mprice_type;
	}
	if (preg_match("/member/", $disallowed_mprice)) $disallowed_mprice = '';
	
	$smarty->assign('disallowed_mprice', $disallowed_mprice);
	$smarty->assign("config", $config);
}

function ajax_load_parent_child(){
    global $con, $smarty, $sessioninfo, $config;
    
    $sid = $_REQUEST['sku_item_id'];
    $bid = $sessioninfo['branch_id'];
    $do_id = $_REQUEST['do_id'];
	
	if(!$sid) die("No item selected");
	
	$filter = array();
	$filter[] = "si.sku_id = (select tmp_si.sku_id from sku_items tmp_si where tmp_si.id = ".mi($sid).")";
	$filter[] = "si.active=1";
	
	if(!$config['do_item_allow_duplicate']){
		$filter[] = "si.id not in (select sku_item_id from tmp_do_items tdi where tdi.do_id=".mi($do_id)." and tdi.user_id=".mi($sessioninfo['id']).")";	
	}
	
	$filter = "where ".join(' and ', $filter);

	$sql = "select si.id,si.sku_item_code,si.mcode,si.link_code,si.description,si.artno,if(sip.price is null,si.selling_price,sip.price) as price, sku.is_bom, si.bom_type,
			si.doc_allow_decimal, sic.qty,u.code as master_uom_code, if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as discount_code
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join uom u on u.id = si.packing_uom_id
			left join sku_items_price sip on si.id=sip.sku_item_id and sip.branch_id=".mi($bid)."
			left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=".mi($bid)."
			$filter
			order by si.sku_item_code, si.description";

	$q1 = $con->sql_query($sql) or die(mysql_error());

	while($r = $con->sql_fetchassoc($q1)){
		$items[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	$smarty->assign('items',$items);
	$smarty->display('parent_child_add.tpl');
}

function auto_update_do_all_amt($branch_id, $do_id){
	global $appCore;
	
	$appCore->doManager->recalculateDOAmount($branch_id, $do_id);
}

function check_do_gst_status($form){
	global $appCore;
	
	return $appCore->doManager->checkDOGstStatus($form);
}

function generate_do_gst_summary($form, $do_items_list){
	$gst_summary = array();
	
	if($do_items_list){
		foreach($do_items_list as $di){
			
			$gst_code = trim($di['gst_code']);
			$gst_rate = mf($di['gst_rate']);
			
			if(!$gst_code && !$gst_rate)	continue;
			
			$gst_key = $gst_code.'-'.$gst_rate;
			// do
			$gst_summary['do']['gst'][$gst_key]['code'] = $gst_code;
			$gst_summary['do']['gst'][$gst_key]['rate'] = $gst_rate;
			$gst_summary['do']['gst'][$gst_key]['amount'] += $di['line_gross_amt'];
			$gst_summary['do']['gst'][$gst_key]['gst_amt'] += $di['line_gst_amt'];
			$gst_summary['do']['gst'][$gst_key]['amt_incl_gst'] += $di['line_amt'];
			
			// inv
			$gst_summary['inv']['gst'][$gst_key]['code'] = $gst_code;
			$gst_summary['inv']['gst'][$gst_key]['rate'] = $gst_rate;
			$gst_summary['inv']['gst'][$gst_key]['amount'] += $di['inv_line_gross_amt2'];
			$gst_summary['inv']['gst'][$gst_key]['gst_amt'] += $di['inv_line_gst_amt2'];
			$gst_summary['inv']['gst'][$gst_key]['amt_incl_gst'] += $di['inv_line_amt2'];
		}
	}
	
	return $gst_summary;
}
?>
