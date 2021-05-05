<?php
/*
4/22/2009 12:47:41 PM yinsee
Tracker #20 : promotion approval flow control multiple approver

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

26/8/2009 12:46:27 PM yinsee
- revoke and copy to use the current user id as owner

1/10/2009 3:27:52 PM yinsee
- fix bug when no promo item, error msg appear as
 "System error: Insert promotion_items failed. Please contact ARMS technical support."

12/10/2009 9:30:00 AM jeff
- fix search category price type filter "Unknown column 'trade_discount_code'"

12/24/2009 5:22:19 PM Andy
- Fix if item no cost use master cost

3/16/2010 4:05:49 PM Andy
- Make promotion able to change branch after save

3/25/2010 4:11:21 PM Andy
- Add 3 types of promotion items control. 'No Control','Limit by day' and 'Limit by period'

4/19/2010 11:13:18 AM Andy
- add promotion id at pos_items

4/19/2010 5:45:00 PM Andy
- Fix copy promotion din't copy control type and limit bugs

7/5/2010 10:54:46 AM edward
- save print_title_in_receipt to table promotion

7/6/2010 10:06:38 AM edward
- save member_receipt_amt and non_member_receipt_amt to table promotion_items

7/7/2010 12:28:07 PM Justin
- To take Stock balance based on the selected Branches Promotion instead of using current branch accessed.

7/12/2010 4:17:09 PM edward
- remove member_receipt_amt and non_member_receipt_amt

7/15/2010 9:57:36 AM alex
- remove ',' at last string in promo branch

7/19/2010 12:30:55 PM Alex
- add consignment bearing Checking

12/13/2010 2:24:54 PM Alex
- add trade discount type checking based on vendor or brand in consignment bearing mode

12/16/2010 2:35:46 PM Andy
- Add block customer to buy more if already hit promotion items limit.
- Add new promotion type (mix_and_match), default promotion type will be 'discount'.

1/3/2011 12:31:13 PM Andy
- Fix promotion show duplicate entry in "SKU Items Change Price".

1/10/2011 10:55:38 AM Andy
- Show sku price type in promotion setup.

1/27/2011 2:50:32 PM Justin
- Fixed the bugs where show blank page while viewing promotion history.

1/27/2011 7:10:55 PM Alex
- fix get item details bugs

1/31/2011 3:43:26 PM Justin
- Fixed the bugs where it cannot show when edit.
- Changed all the stock balance calculation to only sum up own branch id instead of taking branches promotion id.

3/2/2011 1:50:32 PM Andy
- Change insert promotion to use max(id)+1 to avoid mysql replica wrong ID to slave.

3/3/2011 5:57:47 PM Andy
- Change when create new promotion by cancel item, revoke, copy also use max ID+1.
- Fix when revoke or cancel item, some data is missing from copy promotion.

3/9/2011 2:03:24 PM Andy
- Fix cancel selected item will not trigger promotion last update.

3/15/2011 4:24:12 PM Alex
- add checking active on consignment bearing

3/18/2011 4:17:46 PM Andy
- Enhance "Active Promotion" to show mix and match promotion. (move find_overlap_promo() to promotion.include.php)

3/22/2011 2:38:56 PM Alex
- add saving consignment bearing data
- only show consignment bearing discount that match with item price type => assign_consignment

4/5/2011 4:58:44 PM Andy
- Move load_promo_header() and load_promo_items() to promotion.include, so both promotion.php and promotion.approval.php will call the same functions instead of own coding.
- Move assign_consignment() to promotion.include.php to fix the bugs consignment data din't load in promotion approval.
- Add checking for $config['promotion_turn_off_overlap_info'] to see whether to load overlap promotion info.
- Change discount promotion date format to YYYY-MM-DD.

5/9/2011 4:58:45 PM Alex
- add checking config consignment bearing at ajax_get_sku

5/9/2011 Andy
- Promotion overlap info show mprice, qprice and category discount. 
- Fix discount promotion after delete item cannot add back item if other user also openning this promotion.

6/24/2011 5:19:36 PM Andy
- Make all branch default sort by sequence, code.

7/1/2011 5:58:14 PM Alex
- fix check no discount bugs at consignment bearing mode 

7/6/2011 12:14:24 PM Andy
- Change split() to use explode()

7/8/2011 12:22:09 PM Andy
- Make overlap promotion info close at default.
- Touch up overlap promotion info mprice layout.

7/12/2011 1:00:07 PM Alex
- get consignment bearing data by filter the branch which creator the promotion id

7/13/2011 2:20:59 PM Andy
- Add branch can view promotion created by HQ (from promotion history/active promotion), only if the promotion got related to the branch.
- Add checking to prevent branch to edit promotion created by HQ. 

9/6/2011 11:58:42 AM Alex
- fix data missing while confirm in consignment bearing mode

9/23/2011 3:59:50 PM Andy
- Add automatically cancel whole promotion if found all items has been cancelled.

11/18/2011 5:00:01 PM Andy
- Add can use config to control print item per page for discount promotion.

2/16/2012 6:02:27 PM Andy
- Remove unused "is_pwp" variable.
- Add can set different category reward point.
- Add one promotion maximum can key in 500 items.

7/5/2012 11:14 AM Andy
- Fix promotion add item error.

9/19/2012 3:11 PM Andy
- Fix member reward point blank problem.

1/29/2013 1:14 PM Justin
- Enhanced to validate and show user error message if found qty from is higher than qty to.

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items
- if the same parent/child item put together, the 2nd item row color will change, until a new group sku

4/3/2013 5:50 PM Fithri
- fix bug where promotion can save and confirm without branch

6/7/2013 11:06 AM Andy
- Enhance to filter the promotion list, if got privilege "PROMOTION_MIX", only show mix and match promotion.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/25/2013 5:04 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

9/6/2013 3:40 PM Fithri
- add search item by vendor
- change brand search to autocomplete

1/23/2014 11:17 AM Fithri
- add new search filters 'starting in x days', 'ending in x days' & 'currently active'

4/11/2014 10:53 AM Fithri
- add data collector import function at promotion module

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

5/29/2014 5:56 PM Justin
- Enhanced import from CSV to have few options of choosing import format and delimiter.

5/9/2016 2:25 PM Andy
- Fix "Start In", "End In" and "Currently Active" to filter active promotion only.

2/17/2017 3:12PM Justin
- Enhanced to show error message on a standard view.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

3/6/2018 5:44 PM HockLee
- Add new function export_to_csv() to export Promotion to CSV file.

7/6/2018 12:19 PM Andy
- Enhanced import promotion to have column member discount, member price, non-member discount and non-member price.

9/14/2018 2:41 PM Andy
- Enhance promotion list sku to maximum list 1000 items.

10/23/2018 4:30 PM Justin
- Enhanced to load SKU Type list from its own table instead of SKU table.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.

6/28/2019 4:35 PM Andy
- Enhanced to can show Discount Promotion in Membership Mobile App.

7/23/2019 10:37 AM William
- Added can use config "promo_add_sku_items_max_limit" to control add and import promotion.

8/8/2019 10:35 AM William
- Fixed php error display when there is no items in promotion list.

8/17/2020 9:00 AM William
- Enhanced to added new module "Promotion Pop Card".

12/18/2020 4:26 PM William
- Bug fixed promotion price not using non-member-price when config "membership_module" inactive.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PROMOTION')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION', BRANCH_CODE), "/index.php");
//ini_set("display_errors", 0);

include('promotion.include.php');

//die("Promotion is currently under maintenance, please come back later.");

class Promotion extends Module
{
	var $branch_id, $promo_id, $branch, $control_type;

	function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty, $config, $promo_control_type;
		$this->branch_id = intval($_REQUEST['branch_id']);
		$this->promo_id = intval($_REQUEST['id']);
		$this->control_type = $promo_control_type;
		
		if ($this->branch_id =='')
		{
			$this->branch_id = $sessioninfo['branch_id'];
		}
		$con->sql_query("select id, code from branch where active=1 order by sequence,code");

		while($r=$con->sql_fetchrow())
		{
			$this->branch[$r['id']] = $r;
		}

  		load_consignment_bearning_dept($this->branch_id, ($_REQUEST['a']=='open'));
		$smarty->assign("branch", array_values($this->branch));
		$smarty->assign("branches", $this->branch);
		$smarty->assign("sessioninfo", $sessioninfo);

		parent::__construct($title, $template='');
	}

	function delete()
	{
		global $sessioninfo, $con, $LANG, $smarty;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		check_can_change_promotion_status($branch_id, $promo_id);
		
		if ($sessioninfo['level']<9999)
			$usrcheck=" and user_id=$sessioninfo[id]";

		if ($promo_id==0){
			$con->sql_query("delete from tmp_promotion_items
	where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		}
		else{
			$con->sql_query("update promotion
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0
	where id=$promo_id and branch_id=$branch_id $usrcheck");
		}
		if ($con->sql_affectedrows()>0){
			$smarty->assign("id", $promo_id);
			$smarty->assign("type", "delete");
			log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion Deleted (ID#$promo_id)");
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_DELETED'], $promo_id)));
		}
		else
		header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_NOT_DELETED'], $promo_id)));

	}

	function cancel()
	{
		global $sessioninfo, $con, $LANG, $smarty;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		check_can_change_promotion_status($branch_id, $promo_id);
		
/*		if ($sessioninfo['level']<9999)
			$usrcheck=" and user_id=$sessioninfo[id]";
*/
		$con->sql_query("update promotion
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0
	where id=$promo_id and branch_id=$branch_id $usrcheck");
		if ($con->sql_affectedrows()>0){
			$smarty->assign("id", $promo_id);
			$smarty->assign("type", "cancel");
			log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion Cancelled (ID#$promo_id)");
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_CANCELLED'], $promo_id)));
		}
		else
		header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_NOT_CANCELLED'], $promo_id)));
	}

	function confirm()
	{
		$this->save(($_REQUEST['a']=='confirm'));
	}

	function save($is_confirm = 0)
	{
		global $con, $smarty, $sessioninfo, $LANG;

		$form = $_REQUEST;
		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		//check Promo status.
		if($is_confirm && !is_new_id($promo_id))
		{
		    $con->sql_query("select status, approved from promotion where id=$promo_id and branch_id=$branch_id");
		    if($r=$con->sql_fetchrow())
		    {
		       if(($r['status']>0 && $r['status'] !=2) || $r['approved'])
		       {
					$smarty->assign("url", "/promotion.php");
					$smarty->assign("title", "Promotion");
					$smarty->assign("subject", sprintf($LANG['PROMO_ALREADY_CONFIRM_OR_APPROVED'], $promo_id));
					$smarty->display("redir.tpl");
			        exit;
				}
			}
			else{
				$smarty->assign("url", "/promotion.php");
				$smarty->assign("title", "Promotion");
				$smarty->assign("subject", sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
				$smarty->display("redir.tpl");
				exit;
			}
		}

		$last_approval=false;
		$err=array();
	   if($form['member_min_item']){
		foreach($form['member_min_item'] as $k => $v)
		{
		    $upd = array();

		    if ($form['consignment_bearing'] == 'yes'){
				if (isset($form['member_consignment'][$k])){
					list($member_profit,$member_discount,$member_use_net,$member_bearing,$member_trade_code)=explode(',',$form['member_consignment'][$k]); //member

					if (strpos($form['member_disc_p'][$k],"%")){
						$err['top']['a']=$LANG['PROMO_CB_CANNOT_PERCENTAGE'];
					}

					$member_discount=$member_discount?$member_discount:$form['member_disc_p'][$k];					
					$tmp_member_discount=str_replace("%",'',$member_discount);
					if ($is_confirm && !$err['top']['b'] && ($member_profit && !$tmp_member_discount)){
						$err['top']['b']=$LANG['PROMO_CB_NO_DISCOUNT'];
					}
					
		            $upd['member_trade_code']=$member_trade_code;    //discount code
		            $upd['member_prof_p']=$member_profit;    //profit
		            $upd['member_disc_p']=$member_discount;  //discount
					$upd['member_use_net']=$member_use_net=='no' ? $member_use_net: 'yes';   //use net or bear, no saving amount
					$upd['member_net_bear_p']=$member_bearing;   //bearing
				}
				else{
		            $upd['member_trade_code']='';    //discount code
		            $upd['member_prof_p']=0;    //profit
		            $upd['member_disc_p']=0;    //discount
					$upd['member_use_net']='no';   //use net or bear
					$upd['member_net_bear_p']=0;    //bearing
				}

		        if (isset($form['non_consignment'][$k])){
					list($non_profit,$non_discount,$non_use_net,$non_bearing, $non_trade_code)=explode(',',$form['non_consignment'][$k]); //non member

					if (strpos($form['non_member_disc_p'][$k],"%")){
						$err['top']['a']=$LANG['PROMO_CB_CANNOT_PERCENTAGE'];
					}

					$non_discount=$non_discount?$non_discount:$form['non_member_disc_p'][$k];
					$tmp_non_discount=str_replace("%",'',$non_discount);
					if ($is_confirm && !$err['top']['b'] && ($non_profit && !$tmp_non_discount)){
						$err['top']['b']=$LANG['PROMO_CB_NO_DISCOUNT'];
					}
					$upd['non_member_trade_code']=$non_trade_code;    //discount code
		            $upd['non_member_prof_p']=$non_profit;    //profit
		            $upd['non_member_disc_p']=$non_discount;  //discount
					$upd['non_member_use_net']=$non_use_net=='no' ? $non_use_net: 'yes';   //use net or bear only, no saving amount
					$upd['non_member_net_bear_p']=$non_bearing;   //bearing
				}
				else{
					$upd['non_member_trade_code']='';    //discount code
		            $upd['non_member_prof_p']=0;    //profit
		            $upd['non_member_disc_p']=0;    //discount
					$upd['non_member_use_net']='no';   //use net or bear
					$upd['non_member_net_bear_p']=0;    //bearing
				}
			}else{
			    $upd['non_member_disc_p'] = $form['non_member_disc_p'][$k];
		    	$upd['member_disc_p'] = $form['member_disc_p'][$k];
			}
			
			if(!$member_qty_error && $form['member_qty_to'][$k] != 0 && $form['member_qty_from'][$k] > $form['member_qty_to'][$k]){
				$member_qty_error = true;
				$err['top'][] = sprintf($LANG['PROMO_INVALID_QTY'], "Member");
			}

			if(!$non_member_qty_error && $form['non_member_qty_to'][$k] != 0 && $form['non_member_qty_from'][$k] > $form['non_member_qty_to'][$k]){
				$non_member_qty_error = true;
				$err['top'][] = sprintf($LANG['PROMO_INVALID_QTY'], "Non Member");
			}
			
		    $upd['member_disc_a'] = $form['member_disc_a'][$k];
		    $upd['non_member_disc_a'] = $form['non_member_disc_a'][$k];
		    $upd['member_qty_from'] = mi($form['member_qty_from'][$k]);
		    $upd['member_qty_to'] = mi($form['member_qty_to'][$k]);
		    $upd['non_member_qty_from'] = mi($form['non_member_qty_from'][$k]);
		    $upd['non_member_qty_to'] = mi($form['non_member_qty_to'][$k]);
		    $upd['member_min_item'] = mi($form['member_min_item'][$k]);
		    $upd['non_member_min_item'] = mi($form['non_member_min_item'][$k]);
		    $upd['control_type'] = mi($form['control_type'][$k]);
		    $upd['member_limit'] = mi($form['member_limit'][$k]);
            $upd['non_member_limit'] = mi($form['non_member_limit'][$k]);
			$upd['member_receipt_amt'] = $form['member_receipt_amt'][$k];
		    $upd['non_member_receipt_amt'] = $form['non_member_receipt_amt'][$k];
		    $upd['block_normal'] = $form['block_normal'][$k];
		    $upd['member_block_normal'] = $form['member_block_normal'][$k];
		    $upd['item_price_type'] = $form['item_price_type'][$k];
			$upd['allowed_member_type'] = serialize($form['allowed_member_type'][$k]);
			
			/*$con->sql_query("update tmp_promotion_items set member_disc_p = ".ms($form['member_disc_p'][$k]).", member_disc_a = ".mf($form['member_disc_a'][$k]).", non_member_disc_p = ".ms($form['non_member_disc_p'][$k]).", non_member_disc_a = ".mf($form['non_member_disc_a'][$k]).", member_qty_from = ".mi($form['member_qty_from'][$k]).", member_qty_to = ".mi($form['member_qty_to'][$k]).", non_member_qty_from = ".mi($form['non_member_qty_from'][$k]).", non_member_qty_to = ".mi($form['non_member_qty_to'][$k]).", member_min_item = ".mi($form['member_min_item'][$k]).", non_member_min_item = ".mi($form['non_member_min_item'][$k])." where id = ".mi($k)) or die(mysql_error());*/
			$con->sql_query("update tmp_promotion_items set ".mysql_update_by_field($upd)." where id = ".mi($k));
		}
	  }
		//validate data

		$form['id']=$promo_id;
		$form['branch_id']=$branch_id;

		if($form['date_from']=='' || strtotime($form['date_from'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_from']))
		$err['top'][]=$LANG['PROMO_INVALID_DATE_FROM'];

		if($form['date_to']=='' || strtotime($form['date_to'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_to']))
		$err['top'][]=$LANG['PROMO_INVALID_DATE_TO'];

		if (strtotime($form['date_from']) > strtotime($form['date_to']))
		{
			$err['top'][]="Date From cannot greater than Date To";
		}
		
		if (!$form['promo_branch_id']) {
			$err['top'][]="Please select at least one branch";
		}

		// check if promo item list is empty
		$q2=$con->sql_query("select count(*) from tmp_promotion_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id");
		$total = $con->sql_fetchfield(0);
		if ($total <= 0)
		{
			$err['top'][]="There are no items in your promotion.";
		}

		//if Promotion confirm, check approval flow
		if(!$err && $is_confirm)
		{
	   	$params = array();
      	$params['type'] = 'PROMOTION';
      	$params['branch_id'] = $branch_id;
      	$params['user_id'] = $sessioninfo['id'];
      	$params['reftable'] = 'promotion';
      	if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id'];
      	$astat = check_and_create_approval2($params, $con);

			if(!$astat)
			{
				$err['top'][]= $LANG['PROMO_NO_APPROVAL_FLOW'];
			}
			else
			{
				$form['approval_history_id']=$astat[0];
	   		if($astat[1]=='|') $last_approval=true;
			}
		}

		//make promotion actual
		if(!$err)
		{
			if($is_confirm) $form['status']=1;

			if(!$form['user_id']) $form['user_id']=$sessioninfo['id'];
			$form['date_from']=$form['date_from'];
			$form['date_to']=$form['date_to'];
			$form['added']='CURRENT_TIMESTAMP';
			$form['promo_branch_id'] = serialize($form['promo_branch_id']);
			$form['print_title_in_receipt'] = mi($form['print_title_in_receipt']);
			$form['dept_id']=mi($form['dept_id']);
			$form['vendor_id']=mi($form['s_vendor_id']);
			$form['brand_id']=mi($form['s_brand_id']);
			if ($form['consignment_bearing']=='yes')	$form['consignment_bearing']='yes';
			else	$form['consignment_bearing']='no';
			
			if(!$form['category_point_inherit'])	$form['category_point_inherit'] = 'inherit';
			if($form['category_point_inherit']!='set')	$form['category_point_inherit_data'] = array();
			
			$form['category_point_inherit_data'] = serialize($form['category_point_inherit_data']);
			
			//insert NEW promotion
			if(is_new_id($promo_id)){
			    $form['id'] = create_new_promo($branch_id, $form, array('branch_id','id','approval_history_id','title', 'date_from', 'date_to','time_from','time_to','added', 'user_id','promo_branch_id','status','approved','print_title_in_receipt','consignment_bearing','dept_id','r_type','vendor_id', 'brand_id','category_point_inherit','category_point_inherit_data'));
			}
			//update EXIST PO
			else{
			   $form['last_update'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("update promotion set " . mysql_update_by_field($form, array('approval_history_id','title', 'date_from', 'date_to', 'time_from', 'time_to', 'status', 'approved','last_update','promo_branch_id','print_title_in_receipt','consignment_bearing','category_point_inherit','category_point_inherit_data')) . " where id=$form[id] and branch_id=$form[branch_id]");
			}

			//get temporary item_id
			$q1=$con->sql_query("select id, sku_item_id from tmp_promotion_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id");
			while($r1=$con->sql_fetchrow($q1)){
				$tmp_id[$r1['id']]=$r1['sku_item_id'];
			}

			//update items
			$q2=$con->sql_query("select * from tmp_promotion_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id");
			$first_id=0;
			while($r2=$con->sql_fetchrow($q2))
			{
				$r2['branch_id']=$branch_id;
				$r2['promo_id']=$form['id'];

				$con->sql_query("insert into promotion_items " . mysql_insert_by_field($r2, array('promo_id', 'branch_id', 'user_id', 'sku_item_id','brand_id',
				'member_trade_code','member_prof_p','member_disc_p','member_use_net','member_net_bear_p','member_disc_a',
				'non_member_trade_code','non_member_prof_p','non_member_disc_p','non_member_use_net','non_member_net_bear_p','non_member_disc_a','member_qty_from',
				'non_member_qty_to','member_qty_to','non_member_qty_from','member_min_item','non_member_min_item','category_id','control_type','member_limit','non_member_limit','member_receipt_amt','non_member_receipt_amt','block_normal','member_block_normal','item_price_type','allowed_member_type','extra_info')));


				$promo_item_id=$con->sql_nextid();

				// comment out by Andy - why need to everytime update promotion when insert promotion_items?
				//$con->sql_query("update promotion set last_update = CURRENT_TIMESTAMP where id = $promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
				if($first_id==0) $first_id=$promo_item_id;
			}

			if($first_id>0)
			{
				if(!is_new_id($promo_id))
				{
					$con->sql_query("delete from promotion_items where branch_id=$branch_id and promo_id=$promo_id and id<$first_id") or die(mysql_error());
				}

				$con->sql_query("delete from tmp_promotion_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]") or die(mysql_error());
			}
			else
			{
				display_redir($_SERVER['PHP_SELF'], "Promotion", "System error: Insert promotion_items failed. Please contact ARMS technical support.");
			}

			if($is_confirm)
			{
			    log_br($sessioninfo['id'], 'PROMOTION', $form['id'], "Promotion Confirmed (ID#$form[id])");
				$to = get_pm_recipient_list2($promo_id,$form['approval_history_id'],0, 'confirmation',$branch_id,'promotion');
				
				if ($last_approval){
					$promo_no=$this->post_process_promo($form['id'], $branch_id);
					$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ('$astat[0]', $branch_id, $sessioninfo[id], 1, 'Approved')");
					send_pm2($to, "Promotion Confirmed (ID#$promo_id) $approval_status[$approve]", "promotion.php?a=view&id=$promo_id&branch_id=$branch_id");
					header("Location: /promotion.php?type=approved&id=$form[id]&promono=$promo_no");
				}
				else{
					$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id=$branch_id");
	 				$promo_no=$form['id'];
					send_pm2($to, "Promotion Approval (ID#$promo_id)", "promotion.php?a=view&id=$promo_id&branch_id=$branch_id", array('module_name'=>'promotion'));
					header("Location: /promotion.php?type=confirm&id=$form[id]&promono=$promo_no");
				}
			}
			else{
				log_br($sessioninfo['id'], 'Promotion', $form['id'], "Promotion Saved (ID#$form[id])");
				header("Location: /promotion.php?type=save&id=$form[id]");
			}
		}
		else
		{
			$smarty->assign("errm", $err);
			$this->open();
		}
	}

	// this is now real PO
	function post_process_promo($promo_id, $branch_id)
	{
		global $con,$sessioninfo;

//		$promo_id = $this->promo_id;
//		$branch_id = $this->branch_id;

//		$con->sql_query("select * from promotion where id=$promo_id and branch_id=$branch_id");
//		$promo_head = $con->sql_fetchrow();


		$con->sql_query("update promotion set approved=1, active=1 where id=$promo_id and branch_id = $branch_id");
	}

	/*function send_notification($po_id, $branch_id, $vendor_id, $dept_id, $allowed_user){
		global $con;

		$q1 = $con->sql_query("select description, contact_email from vendor where id=$vendor_id");
		$r1 = $con->sql_fetchrow($q1);
		$vendor=$r1['description'];
		$vendor_email=$r1['contact_email'];

		$q2 = $con->sql_query("select description from category where id=$dept_id");
		$r2 = $con->sql_fetchrow($q2);
		$dept=$r2['description'];

		if ($allowed_user)
		{
			foreach ($allowed_user as $k=>$v){
				send_pm($k, sprintf("PO (on behalf from HQ Approved) (HQ%05d, Dept:$dept, Vendor:$vendor)",$po_id), "/po.php?a=view&id=$po_id&branch_id=$branch_id");
			}
		}
	}*/

	/*function load_promo_header()
	{
		global $con, $sessioninfo, $smarty;
		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		$con->sql_query("select promotion.*, bah.approvals from promotion
						left join branch_approval_history bah on bah.id=promotion.approval_history_id and bah.branch_id=promotion.branch_id
						where promotion.id = ".mi($promo_id)." and promotion.branch_id = ".mi($branch_id));

		$form = $con->sql_fetchrow();
		$form['promo_branch_id'] = unserialize($form['promo_branch_id']);

		// this is mix and match promotion
        if($form['promo_type']=='mix_and_match'){
            $open_type = ($_REQUEST['a']=='open' ? 'open' : 'view');
            $redir_url =  "promotion.mix_n_match.php?a=$open_type&branch_id=".mi($form['branch_id'])."&id=".mi($form['id']);
            if(isset($_REQUEST['highlight_promo_item_id'])){
                $redir_url .= '&highlight_promo_item_id='.$_REQUEST['highlight_promo_item_id'];
			}
		    header("Location: $redir_url");
			exit;
		}
			
		if ($sessioninfo['level']>=9999)	// superuser approve and final
		{
			$form['is_approval'] = 1;
			$form['last_approver'] = 1;
		}
		else
		{
			if (preg_match("/\|$sessioninfo[id]\|/", $form['approvals']))
				$form['is_approval'] = 1;
			if (preg_match("/\|\d+\|$/", $form['approvals']))
				$form['last_approver'] = 1;
		}

		if ($form['approval_history_id']>0){
			$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u
	from branch_approval_history_items i
	left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
	left join user on i.user_id = user.id
	where h.ref_table='promotion' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
	order by i.timestamp");

			$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
		}

		return $form;

	}*/


	function ajax_add_promo_row()
	{
		global $con, $smarty, $sessioninfo, $LANG;
		
		//check sku not empty
		if (!$_REQUEST['sku_code_list'])
		{
			print $LANG['PROMO_SELECT_SKU'];
			exit;
		}

		$con->sql_query("select count(*) as count from tmp_promotion_items where promo_id=".$this->promo_id." and branch_id =".$this->branch_id." ".(($this->promo_id==0)? "and user_id=$sessioninfo[id]" : "")." group by promo_id");
		$item_count=$con->sql_fetchrow();

		//print $item_count['count'];
		foreach($_REQUEST['sku_code_list'] as $sku_item_id)
		{
			$sku_item_id=intval($sku_item_id);

			//check duplicate sku in list
			$con->sql_query("select * from tmp_promotion_items where sku_item_id = $sku_item_id and promo_id = ".$this->promo_id." and branch_id = ".$this->branch_id." and ".(($this->promo_id==0)?" user_id = $sessioninfo[id]":1));

			if ($con->sql_numrows() > 0)
			{
				print $LANG['PROMO_DUPLICATE_ITEM'];
				exit;
			}

			foreach($_REQUEST['promo_branch_id'] as $pbid => $pbc)
			{
				$gbid[] = $pbid;
			}

			if(count($gbid) > 0){
				$g_bid = join($gbid);
				$sic = "and sc.branch_id in (".join($gbid).")";
			}

			$r=$this->get_items_detail($sku_item_id,$this->branch_id, $g_bid);

			$this->add_temp_row($r);
		
		    if ($_REQUEST['consignment_bearing'] == 'yes'){
				//for cheking to avoid confuse of consignment bearing and normal mode
				if (strpos($r['member_disc_p'],"%")){
					$r['cb_member_disc_p']=$r['member_disc_p'];
					unset($r['member_disc_p']);
				}
	
				if (strpos($r['non_member_disc_p'],"%")){
					$r['cb_non_member_disc_p']=$r['non_member_disc_p'];
					unset($r['non_member_disc_p']);
				}
			}

			$items[] = $r;

			foreach($_REQUEST['promo_branch_id'] as $pbid => $pbc)
			{
				$con->sql_query("select pi.*, p.status, p.active, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, if(sp.price is null, si.selling_price, sp.price) as selling_price, sc.grn_cost, sc.qty from promotion_items pi
						left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id
						left join sku_items si on pi.sku_item_id = si.id
						left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = pi.branch_id
						left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = pi.branch_id
						where p.promo_branch_id like '%\"".$pbc."\"%' and (".ms($_REQUEST['date_from'])." between date_from and date_to or	".ms($_REQUEST['date_to'])."
						between date_from and date_to or date_from between ".ms($_REQUEST['date_from'])." and ".ms($_REQUEST['date_to']).") and
						pi.sku_item_id = ".mi($sku_item_id)." and p.status <> 5 and p.status <> 4");

				if ($con->sql_numrows()>0)
				{
					while($r2 = $con->sql_fetchrow())
					{
						$ditems[$r['id']][] = $r2;
					}
				}
			}
		}

		//$this->assign_consignment();
//		print_r($items);
		assign_consignment();
//		print_r($_REQUEST);
		$smarty->assign("form", $_REQUEST);
		$smarty->assign("itemcount", ($item_count['count']));
		$smarty->assign("promotion_items", $items);
		$smarty->assign("promo_item_count",count($items));
		$smarty->assign("allow_edit",1);
		$smarty->assign("ditems",$ditems);

		print trim($smarty->fetch('promotion.new.promotion_row.tpl'));
	}

	function ajax_delete_promo_row()
	{
		global $con;
		$id = mi($_REQUEST['promo_items_id']);
		$branch_id = $this->branch_id;
		$con->sql_query("delete from tmp_promotion_items where id=$id and branch_id=$branch_id");
	}

	function open()
	{
		global $smarty, $con, $sessioninfo, $LANG;
		$con->sql_query("delete from tmp_promotion_items where (promo_id>1000000000 and promo_id<".strtotime('-1 day').") and user_id=$sessioninfo[id]");

		$form=$_REQUEST;
		$form['branch_id'] = $this->branch_id;
		if ($this->promo_id==0){
			$this->promo_id=time();
			if($this->promo_id <= $_SESSION['promo_last_create_time']) {$this->promo_id = $_SESSION['promo_last_create_time']+1;}
			$_SESSION['promo_last_create_time'] = $this->promo_id;
			$form['id']=$this->promo_id;
			$form['category_point_inherit'] = 'inherit';
		}

		if ($form['a']=='open' && !is_new_id($this->promo_id)){

			//get Existing PO header
			//$form = $this->load_promo_header();
			$form = load_discount_promo_header($this->branch_id, $this->promo_id);
   			/*if($form['promo_type']=='mix_and_match'){
			    header("Location: promotion.mix_n_match.php?a=open&branch_id=".mi($form['branch_id'])."&id=".mi($form['id']));
				exit;
			}*/
			$this->copy_to_tmp();
		}
		else
		{
			if ($form['a'] == 'refresh')
			{
				if($form['date_from']=='' || strtotime($form['date_from'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_from']))
				$err['top'][]=$LANG['PROMO_INVALID_DATE_FROM'];

				if($form['date_to']=='' || strtotime($form['date_to'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_to']))
				$err['top'][]=$LANG['PROMO_INVALID_DATE_TO'];

				if (strtotime($form['date_from']) > strtotime($form['date_to']))
				{
					$err['top'][]="Date From cannot greater than Date To";
				}
			}
			if ($err) $smarty->assign("errm", $err);

			if ($form['a'] == 'refresh' && $this->branch_id == 1)
			{
			  if(!empty($form['promo_branch_id'])){
				foreach ($form['promo_branch_id'] as $v)
				{
					$promo_branch_id[$v]= get_branch_code($v);
				}

				$form['promo_branch_id'] = $promo_branch_id;
			  }
			}
			else
			{
				$form['promo_branch_id'][$this->branch_id] = get_branch_code($this->branch_id);
			}

			$form['date_from'] = $this->datetosql($form['date_from']);
			$form['date_to'] = $this->datetosql($form['date_to']);
		}

		/*foreach ($form['promo_branch_id'] as $t=>$k)
		{
			$pg_bid[] = $t;
		}
		if(count($pg_bid) > 0) $this->pg_bid = join(",", $pg_bid);*/

		//$this->load_promo_items(true,$form);
		load_discount_promo_items($this->branch_id, $this->promo_id, true, $form, $this->from_data_collector);
		$con->sql_query("select code as price_type from trade_discount_type order by price_type");
		$smarty->assign("price_type", $con->sql_fetchrowset());
		$con->sql_query("select code as sku_type from sku_type where active=1");
		$smarty->assign("sku_type", $con->sql_fetchrowset());
		$con->sql_query("select id, description from vendor where active=1 order by description");
		$smarty->assign("vendors", $con->sql_fetchrowset());
       	assign_consignment();
		$smarty->assign("form", $form);
		$this->display('promotion.new.tpl');
	}

	function copy_to_tmp()
	{
		global $con, $sessioninfo;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		//delete ownself PO items in tmp table
		$con->sql_query("delete from tmp_promotion_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]");

		//update items
		$q2 = $con->sql_query("select * from promotion_items where promo_id=$promo_id and branch_id = $branch_id order by id");
		while($r2=$con->sql_fetchrow($q2))
		{
			$r2['branch_id']=$branch_id;
			$r2['promo_id']=$promo_id;
			$r2['user_id']=$sessioninfo['id'];
			$sid = mi($r2['sku_item_id']);
			// get price type
			//if(!$r2['item_price_type']){
			    // always use latest price type if editable
				$r2['item_price_type'] = get_sku_latest_price_type($branch_id, $sid);
			//}
			$con->sql_query("insert into tmp_promotion_items " . mysql_insert_by_field($r2, array('promo_id', 'branch_id', 'user_id', 'sku_item_id', 'brand_id',
			'member_trade_code', 'member_prof_p','member_disc_p', 'member_use_net', 'member_net_bear_p','member_disc_a',
			'non_member_trade_code','non_member_prof_p','non_member_disc_p','non_member_use_net','non_member_net_bear_p','non_member_disc_a',
			'member_qty_from','non_member_qty_from','member_qty_to','non_member_qty_to','member_min_item','non_member_min_item','category_id','control_type','member_limit','non_member_limit','member_receipt_amt','non_member_receipt_amt','block_normal','member_block_normal','item_price_type','allowed_member_type','extra_info')));

		}

	}
	
	function create_from_upload_file() { //xx
	
		global $con, $smarty, $config;
		
		$file_size = $_FILES['files']['size'];
		if($file_size <= 0){
			print("<script>alert('Please select a file to import');</script>");
			$this->display();
			exit;
		}
		
		$this->promo_id = time();
		$file_name = $_FILES['files']['tmp_name'];
		$handle = fopen($file_name, "r");
		$import_format = $_REQUEST['import_format'] ? $_REQUEST['import_format'] : 1;
		$delimiter = $_REQUEST['delimiter'];
		
		$items = array();
		$inv_items = array();
		$line_no = 0;
		$err_data = 0;
		while ($line = fgetcsv($handle,4096,$delimiter)) {
			$line_no++;
			$code = trim($line[0]);
			if(!$code)	continue;	// skip empty row
			$err_msg = '';
			
			unset($r1);
			if($import_format==1){	// default
				if(preg_match('/^2[0-9]*$/', $code) && strlen($code) > 12){
					$tmp_si_code = substr($code, 0, 12);
				}else{
					$tmp_si_code = $code;
				}
				
				$q1=$con->sql_query("select id from sku_items where (sku_item_code=".ms($tmp_si_code)." or mcode=".ms($code)." or link_code=".ms($code)." or artno=".ms($code).")");
				$r1 = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}elseif($import_format==2){	// use grn barcode
				if (preg_match("/^00/", $code))	// form ARMS' GRN barcoder
				{
					$sku_item_id=mi(substr($code,0,8));
					//$qty = mi(substr($code,8,4));
					$sql = "select id from sku_items where id = ".mi($sku_item_id);
				}
				else	// from ATP GRN Barcode, try to search the link-code 
				{
					$linkcode=substr($code,0,7);
					//$qty = mi(substr($code,7,5));
					$sql = "select id from sku_items where link_code = ".ms($linkcode);
				}
				$q1 = $con->sql_query($sql);
				$r1 = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}
			
			if(!$r1){	// Item Code Not Found
				$err_msg = 'Code Not Found';
				$err_data += 1;
			}
			
			$sku_item_id = mi($r1['id']);
			if(!$err_msg && $sku_item_id){	// Item Found
				if(in_array($sku_item_id, $items)){	// Duplicated sku
					$err_msg = "Duplicate Item";
					$err_data += 1;
				}
			}
			
			if(!$err_msg){	// item matched
				$member_disc_p = trim($line[1]);	// Member Discount
				$member_disc_a = trim($line[2]);	// Member Price
				$non_member_disc_p = trim($line[3]);	// Non-Member Discount
				$non_member_disc_a = trim($line[4]);	// Non-Member Price
				
				if($member_disc_p && $member_disc_a){	// Not allow to have both member discount & price
					$err_msg = 'Cannot have both [Member Discount] and [Member Price] at the same time';
					$err_data += 1;
				}
				
				if(!$err_msg && $non_member_disc_p && $non_member_disc_a){	// Not allow to have both non-member discount & price
					$err_msg = 'Cannot have both [Non-Member Discount] and [Non-Member Price] at the same time';
					$err_data += 1;
				}
				
				if(!$err_msg && $member_disc_p){
					if(!preg_match(DISCOUNT_VALUE_PATTERM, $member_disc_p)){
						$err_msg = "Invalid [Member Discount]: '$member_disc_p'";
						$err_data += 1;
					}
				}
				
				if(!$err_msg && $member_disc_a){
					if(!preg_match(PRICE_VALUE_PATTERM, $member_disc_a)){
						$err_msg = "Invalid [Member Price]: '$member_disc_a'";
						$err_data += 1;
					}
				}
				
				if(!$err_msg && $non_member_disc_p){
					if(!preg_match(DISCOUNT_VALUE_PATTERM, $non_member_disc_p)){
						$err_msg = "Invalid [Non-Member Discount]: '$non_member_disc_p'";
						$err_data += 1;
					}
				}
				
				if(!$err_msg && $non_member_disc_a){
					if(!preg_match(DISCOUNT_VALUE_PATTERM, $non_member_disc_a)){
						$err_msg = "Invalid [Non-Member Price]: '$non_member_disc_a'";
						$err_data += 1;
					}
				}
			}
			
			$valid_data = $line_no - $err_data;
			$max_limit_promotion = mi($config['promo_add_sku_items_max_limit']) ? mi($config['promo_add_sku_items_max_limit']) : 500;
			if($valid_data > $max_limit_promotion){
				$err_msg = "This promotion is limit to import ".$max_limit_promotion." item.";
			}
			
			if($err_msg){	// Got Error
				$inv_items[] = array('code'=>$code, 'msg'=>$err_msg, 'line_no'=>$line_no);	// Store Error
				continue;
			}
						
			// Valid Item
			$r = $this->get_items_detail($sku_item_id,$this->branch_id,0);
			$r['member_disc_p'] = $member_disc_p;
			$r['member_disc_a'] = $member_disc_a;
			$r['non_member_disc_p'] = $non_member_disc_p;
			$r['non_member_disc_a'] = $non_member_disc_a;
			
			$this->add_temp_row($r);
			$items[] = $sku_item_id;
			
		}
		
		$_REQUEST['id'] = $this->promo_id;
		$_REQUEST['branch_id'] = $this->branch_id;
		$_REQUEST['a'] = 'refresh';
		$_REQUEST['date_to'] = $_REQUEST['date_from'] = date('Y-m-d');
		
		$this->from_data_collector = array();
		$this->from_data_collector['id'] = $this->promo_id;
		$this->from_data_collector['date_from'] = $_REQUEST['date_from'];
		$this->from_data_collector['date_to'] = $_REQUEST['date_to'];
		$smarty->assign("data_collector_invalid_items", $inv_items);
		$this->refresh();
	}

	function datetosql($date)
	{
		list($dd,$mm,$yy) = preg_split("/(-|\/)/",$date);
		return strtotime("$yy-$mm-$dd");
	}

	function refresh()
	{
       	//$this->assign_consignment();
		$this->open();
	}

	function _default()
	{
		$this->display();
	}

	function ajax_check_consignment(){
        global $con, $sessioninfo, $smarty;

        $filter='';
        if (isset($_REQUEST['vendor_id']))	$filter[]= "cb.vendor_id=$_REQUEST[vendor_id] ";
        if (isset($_REQUEST['brand_id']))	$filter[]= "cb.brand_id=$_REQUEST[brand_id] ";

		$filter[]= "cb.active=1";

		$filt=join(' and ',$filter);

		$con->sql_query("select cbi.id from consignment_bearing_items cbi
						left join consignment_bearing cb on cbi.consignment_bearing_id = cb.id and cbi.branch_id=cb.branch_id
						where cb.branch_id=$sessioninfo[branch_id] and cb.dept_id=$_REQUEST[dept_id] and cb.r_type=".ms($_REQUEST['r_type'])." $filt ");
		if ($con->sql_numrows()<1)  print "No";
	}

	function ajax_change_brand_vendor(){

        global $con, $sessioninfo, $smarty;

        if (!$_REQUEST['dept_id']){
			print 'NO';
			return;
		}

		$arr_select=array();
		
		$branch_id=$_REQUEST['branch_id'];
		if (!$branch_id)	$branch_id=$sessioninfo['branch_id'];

		$filter_branch="cb.branch_id=$branch_id and";

		//vendor
        if ($sessioninfo['vendors']) $vd = "and cb.vendor_id in (".$sessioninfo['vendor_ids'].")";
        else    $vd='';

  		$con->sql_query("select distinct cb.vendor_id, vendor.description
						 from consignment_bearing_items cbi
						 left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
						 left join vendor on cb.vendor_id=vendor.id
						 where $filter_branch cb.dept_id=$_REQUEST[dept_id] and cb.r_type='vendor' and cb.active=1 $vd
						 order by vendor.description");

		if ($con->sql_numrows()>0){
			$arr_select['vendor'].="<option value='' selected>-- Please Select A Vendor--</option>";

	        while($v=$con->sql_fetchassoc()){

	        	if ($_REQUEST['vendor_id']==$v['vendor_id'])    $select="selected";
				else	$select='';

				$arr_select['vendor'].="<option value=$v[vendor_id] $select>".$v['description']."</option>";
			}
		}else{
			$arr_select['vendor'].="<option value=''>No Data</option>";
		}
		$con->sql_freeresult();

		//brand
        if ($sessioninfo['brands']) $bd = "and cb.brand_id in (".$sessioninfo['brand_ids'].")";
        else    $bd='';

		$con->sql_query("select distinct cb.brand_id, brand.description
						 from consignment_bearing_items cbi
						 left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
						 left join brand on cb.brand_id=brand.id
						 where $filter_branch cb.dept_id=$_REQUEST[dept_id] and r_type='brand' and cb.active=1 $bd
						 order by brand.description");
		if ($con->sql_numrows()>0){
			$arr_select['brand'].="<option value='' selected>-- Please Select A Brand--</option>";

	        while($b=$con->sql_fetchassoc()){
				if ($_REQUEST['brand_id']==$b['brand_id'])    $select="selected";
				else	$select='';

				$arr_select['brand'].="<option value=$b[brand_id] $select>".$b['description']."</option>";
			}
		}else{
			$arr_select['brand'].="<option value=''>No Data</option>";
		}
		$con->sql_freeresult();

		print json_encode($arr_select);

	}
/*
	function ajax_show_consignment_table(){
		global $con, $sessioninfo, $smarty;
		$filter='';
		if (isset($_REQUEST['vendor_id'])){
			$filter=" and cb.vendor_id=$_REQUEST[vendor_id] ";
		}

		if (isset($_REQUEST['brand_id'])){
			$filter=" and cb.brand_id=$_REQUEST[brand_id] ";
		}

		$sql="select cbi.trade_discount_type_code as r_type , cbi.profit, cbi.discount, cbi.net_bearing from consignment_bearing_items cbi
			left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
			where cb.branch_id=$sessioninfo[branch_id] and cb.dept_id=$_REQUEST[dept_id] $filter";

		$con->sql_query($sql);

		$smarty->assign('consignment',$con->sql_fetchrowset());
		$smarty->assign('item_id',$_REQUEST['item_id']);
		$smarty->assign('t_mem',$_REQUEST['t_mem']);
		$smarty->display('promotion.new.consignment_table.tpl');


	}
*/
	function ajax_load_promotion_list($t=0)
	{
		global $con, $sessioninfo, $smarty;

		if(!$t) $t=intval($_REQUEST['t']);
		/*
		if($sessioninfo['level']>=9999)
			$owner_check="";
		else
			$owner_check="user_id=$sessioninfo[id] and";
		*/

		if (BRANCH_CODE!='HQ')
		{
			$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
			$r = $con->sql_fetchrow();
			$branch_sql = "promotion.branch_id = ".$r['id']." and ";
		}
		switch($t){
			//search PO
			case 0:
				
				if (!$_REQUEST['search'] && !$_REQUEST['search_filter']) die('<br />&nbsp;&nbsp;Cannot search empty string<br /><br />');
				
				$search_filter = array();
				
				if ($_REQUEST['search']) {
					$search_filter[] = "(promotion.id=".mi($_REQUEST['search'])." or promotion.title like ". ms("%".replace_special_char($_REQUEST['search'])."%") . ")";
				}
				
				if ($_REQUEST['search_filter']) {
				
					$d = mi($_REQUEST['day_count']);
					$today = ms(date('Y-m-d'));
					
					switch ($_REQUEST['search_filter']) {
					
						case 'starting_in':
							$search_filter[] = " DATEDIFF(date_from,$today) = $d and promotion.active=1 and promotion.status=1 and promotion.approved = 1 ";
						break;
						
						case 'ending_in':
							$search_filter[] = " DATEDIFF(date_to,$today) = $d and promotion.active=1 and promotion.status=1 and promotion.approved = 1 ";
						break;
						
						case 'currently_active':
							$search_filter[] = " date_from <= $today and date_to >= $today and promotion.active=1 and promotion.status=1 and promotion.approved = 1 ";
						break;
					}
					
				}
				
			    $where = $search_filter ? join(' and ',$search_filter) : ' 1 ';
				
				/*
				print '<pre>';
				print_r($_REQUEST);
				print '</pre>';
			    $_REQUEST['s']='';
				die($where);
				*/
			    break;

			// show saved Promotion
			case 1:
				//$owner_check="user_id=$sessioninfo[id] and";
		    	$where="promotion.status=0 and not promotion.approved ";
		    	break;

			// show waiting for approval (and KIV)
			case 2:
				$where="(promotion.status=1 or promotion.status=3) and not promotion.approved";
				break;

			// show inactive
			case 3:
			   	$where="(promotion.status=4 or promotion.status=5)";
				break;

			// show approved
			case 4:
				$where="promotion.approved=1 and promotion.status = 1";
				break;

			// show rejected
			case 5:
				$where="promotion.status=2";
				break;

			// show approved HQ PO
			case 6:
		    	$where="promotion.branch_id=1 and promotion.approved and promotion.status=1";
		    	break;
		}

		$allowed_promo_type = array(ms('discount'));
		if(privilege('PROMOTION_MIX'))	$allowed_promo_type[] = ms('mix_and_match');
		$where .= " and promotion.promo_type in (".join(',', $allowed_promo_type).")";
		
		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else
			$sz = 25;
		$con->sql_query("select count(*) from promotion where $owner_check $branch_sql $where");
		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
			if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start){
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
		$q1=$con->sql_query("select user.u, promotion.*, branch_approval_history.approvals,branch_approval_history.approval_order_id
    from promotion
		left join user on promotion.user_id = user.id
		left join branch_approval_history on (promotion.approval_history_id = branch_approval_history.id and promotion.branch_id = branch_approval_history.branch_id)
		where $owner_check $branch_sql $where order by last_update desc limit $start, $sz");

		while ($r1=$con->sql_fetchrow($q1)){
			$r1['promo_branch_id'] = unserialize($r1['promo_branch_id']);
			if ($r1['promo_branch_id']){
				$r1['str_promo_branch_id_list'] = implode(",", array_keys($r1['promo_branch_id']));
				$r1['promo_branch_id'] = implode(",",$r1['promo_branch_id']);
			}
			$promo_list[]=$r1;
		}
		
		//print_r($promo_list);
		$smarty->assign("promo_list", $promo_list);
		$smarty->display("promotion.list.tpl");

	}

	/*function load_promo_items($use_tmp=false,$form='')
	{
		global $con, $sessioninfo, $smarty;

		$owner_filter='';

		if($use_tmp){
			$table="tmp_promotion_items";
			$owner_filter=" and user_id=$sessioninfo[id] ";
		}
		else{
			$table="promotion_items";
		}

		//if($this->pg_bid) $sic = "and sc.branch_id in (".$this->pg_bid.")";

		$q1=$con->sql_query("select tpi.*, si.mcode, si.sku_item_code,si.artno, mcode, if(sp.price is null, si.selling_price, sp.price) as selling_price, si.description, sc.grn_cost, sc.qty from $table tpi
		left join sku_items si on sku_item_id = si.id
		left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = ".mi($this->branch_id)."
		left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = ".mi($this->branch_id)."
		where tpi.promo_id=".$this->promo_id." and tpi.branch_id=".$this->branch_id." $owner_filter group by tpi.id
		order by tpi.id") or die(mysql_error());
		$promo_items = $con->sql_fetchrowset();
		$smarty->assign("promo_item_count", count($promo_items));

		if($form['consignment_bearing']=='yes' and $form['status'] == 0 and $promo_items){

			//compare consignment bearing discount and tmp promotion items discount
			$filter=" cb.dept_id=".intval($form['dept_id'])." and (cb.vendor_id=".intval($form['vendor_id'])." or cb.brand_id=".intval($form['brand_id']).") and cb.active=1";

			foreach ($promo_items as $no => $att){

				if ($att['non_member_trade_code'] ==  $att['member_trade_code'] and
				$att['non_member_prof_p'] ==  $att['member_prof_p'] and
				$att['non_member_disc_p'] == $att['member_disc_p'] and
				$att['non_member_use_net'] ==  $att['member_use_net'] and
				$att['non_member_net_bear_p'] ==  $att['member_net_bear_p'])
				{
					$nm_code=ms($att['non_member_trade_code']);
					$nm_prof=mf($att['non_member_prof_p']);
					$nm_disc=mf(str_replace("%","",$att['non_member_disc_p']));
					$nm_use_net=ms($att['non_member_use_net']);
					$nm_bear=mf($att['non_member_net_bear_p']);

					if ($nm_code =='' and $nm_prof == 0 and $nm_disc == 0 and $nm_disc == 0) continue;
					$check_non=" cbi.trade_discount_type_code=$nm_code and cbi.profit=$nm_prof and cbi.discount=$nm_disc and cbi.use_net=$nm_use_net and cbi.net_bearing=$nm_bear ";


					$sql="select * from consignment_bearing_items cbi
							left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
							where $filter and $check_non ";
					$con->sql_query($sql);
					if ($con->sql_numrows()<=0) {
						$promo_items[$no]['figure']['nm']='unmatch';
						$promo_items[$no]['figure']['m']='unmatch';

					}

				}else{

					$nm_code=ms($att['non_member_trade_code']);
					$nm_prof=mf($att['non_member_prof_p']);
					$nm_disc=mf(str_replace("%","",$att['non_member_disc_p']));
					$nm_use_net=ms($att['non_member_use_net']);
					$nm_bear=mf($att['non_member_net_bear_p']);

					if ($nm_code !='' and $nm_prof != 0 and $nm_disc != 0 and $nm_bear != 0){
						$check_non=" cbi.trade_discount_type_code=$nm_code and cbi.profit=$nm_prof and cbi.discount=$nm_disc and cbi.use_net=$nm_use_net and cbi.net_bearing=$nm_bear ";
						$sql1="select * from consignment_bearing_items cbi
							left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
							where $filter and $check_non ";
						$con->sql_query($sql1);
						if ($con->sql_numrows()<=0) $promo_items[$no]['figure']['nm']='unmatch';

//print "non-member ".$sql1."<br />";
					}


					$m_code=ms($att['member_trade_code']);
					$m_prof=mf($att['member_prof_p']);
					$m_disc=mf(str_replace("%","",$att['member_disc_p']));
					$m_use_net=ms($att['member_use_net']);
					$m_bear=mf($att['member_net_bear_p']);

					if ($m_code !='' and $m_prof != 0 and $m_disc != 0 and $nm_bear != 0){
						$check_member=" cbi.trade_discount_type_code=$m_code and cbi.profit=$m_prof and cbi.discount=$m_disc and cbi.use_net=$m_use_net and cbi.net_bearing=$m_bear ";

						$sql2="select * from consignment_bearing_items cbi
							left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
							where $filter and $check_member ";
//print "member ".$sql2."<br />";
						$con->sql_query($sql2);
						if ($con->sql_numrows()<=0) $promo_items[$no]['figure']['m']='unmatch';
					}
				}

			}
		}

		$smarty->assign("promotion_items",$promo_items);

		$con->sql_query("select * from promotion where branch_id = ".mi($this->branch_id)." and id = ".mi($this->promo_id));
		$promo = $con->sql_fetchrow();

		$this->assign_consignment(false,$promo);

		if ($promo_items)
		{
		    $bid_list = array();
			foreach($promo_items as $pi)
			{
				$samepromos = find_overlap_promo($promo, array('sku_item_id'=>$pi['sku_item_id']), $bid_list);
				if ($samepromos['discount'])
				{
					foreach($samepromos['discount'] as $r2)
					{
						$ditems['discount'][$pi['id']][] = $r2;
					}
				}
				
				if ($samepromos['mix_n_match']){
                    foreach($samepromos['mix_n_match'] as $r2)
					{
						$ditems['mix_n_match'][$pi['id']][] = $r2;
					}
				}
			}
		}
		//print_r($ditems);
		$smarty->assign("ditems",$ditems);

		return $promo_items;
	}*/

	function ajax_get_promotions()
	{
		global $con, $smarty;
        $promo['date_from'] = $_REQUEST['date_from'] ? $_REQUEST['date_from'] : date("Y-m-d");
		$promo['date_to'] = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date("Y-m-d");

		$bid_list = array();
		if($_REQUEST['promo_bid']){
			$bid_list = array(mi($_REQUEST['promo_bid']));
		}
		foreach (explode(",",$_REQUEST['id']) as $k)
		{
			$id = intval($k); if ($id<=0) continue;
			$ret = find_overlap_promo($promo, array('sku_item_id'=>$id), $bid_list);
			if ($ret['discount'])
			{
				$con->sql_query("select * from sku_items where id=$id");
				$data['discount'][$k] = array('item'=>$con->sql_fetchrow(),'promos'=>$ret['discount']);
			}
			if ($ret['mix_n_match'])
			{
				$data['mix_n_match'][$k] = $ret['mix_n_match'];
			}
		}
		
		//print_r($data);
		$smarty->assign("data", $data);
		
		// masterfile_sku_items.promotions.tpl
		$tpl = $_REQUEST['template'] ? $_REQUEST['template'] : 'masterfile_sku_items.promotions.tpl';
		$smarty->display($tpl);
		exit;
	}

	function cancel_selected_item()
	{
		global $con,$smarty,$sessioninfo,$LANG;
		$promo_id = $_REQUEST['id'];
		$branch_id = $_REQUEST['branch_id'];

		check_can_change_promotion_status($branch_id, $promo_id);
		
		if (!privilege('PROMOTION_CANCEL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION CANCEL SELECTED PROMOTION ITEMS', BRANCH_CODE), "/index.php");

//		print_r(join(",",array_keys($_REQUEST['cancel_item'])));
		$new_promo_bid = $sessioninfo['branch_id'];
        $new_promo_id = create_new_promo($new_promo_bid); // create new promotion

        // select old promotion
        $con->sql_query("select title, date_from, date_to, time_from, time_to, promo_branch_id, consignment_bearing, dept_id,r_type, vendor_id, brand_id
		from promotion where id=$promo_id and branch_id=$branch_id");
		$temp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$temp) display_redir($_SERVER['PHP_SELF'], "Promotion", "Promotion ID#$promo_id cannot found.");

		$temp['user_id'] = $sessioninfo['id'];
		$temp['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update promotion set ".mysql_update_by_field($temp)." where branch_id=$new_promo_bid and id=$new_promo_id");

		/*$con->sql_query("insert into promotion
		(branch_id, user_id, title, date_from, date_to, time_from, time_to, promo_branch_id)
		select ".intval($sessioninfo['branch_id'])." as branch_id, ".intval($sessioninfo['id'])." as user_id, title, date_from, date_to, time_from, time_to, promo_branch_id
		from promotion where id=$promo_id and branch_id=$branch_id");
		$new_promo_id = $con->sql_nextid();
		$con->sql_query("update promotion set added=CURRENT_TIMESTAMP where id=$promo_id and branch_id=$branch_id");*/

		$q2 = $con->sql_query("select * from promotion_items where promo_id=$promo_id and branch_id=$branch_id order by id");
		while($r2=$con->sql_fetchrow($q2)){
			$r2['branch_id']=$new_promo_bid;
			$r2['promo_id']=$new_promo_id;
			$r2['user_id']=$sessioninfo['id'];

			if (in_array($r2['id'],array_keys($_REQUEST['cancel_item'])))
			{
				// insert item into new promotion
				$con->sql_query("insert into promotion_items " . mysql_insert_by_field($r2, array('promo_id', 'branch_id', 'user_id', 'sku_item_id', 'brand_id', 'member_disc_p', 'member_disc_a', 'member_min_item', 'member_qty_from', 'member_qty_to', 'non_member_disc_p', 'non_member_disc_a', 'non_member_min_item', 'non_member_qty_from', 'non_member_qty_to')));
				
				// delete items from current promotion
				$con->sql_query("delete from promotion_items where promo_id = ".mi($promo_id)." and branch_id = ".mi($branch_id)." and id = ".mi($r2['id']));
			}
		}

		// make update to activate trigger
		$con->sql_query("update promotion set last_update=CURRENT_TIMESTAMP where branch_id=$branch_id and id=$promo_id");

		log_br($sessioninfo['id'], 'PROMOTION', $new_promo_id, "Promotion Revoked (ID#$promo_id -> ID#$new_promo_id)");
		header("Location: $_SERVER[PHP_SELF]?a=open&id=$new_promo_id&branch_id=$branch_id&msg=".urlencode(sprintf($LANG['PROMO_CANCEL_PROMO_ITEM'], $promo_id, $new_promo_id)));

		// check if current promotion have no items left
		$con->sql_query("select id from promotion_items where promo_id=".mi($promo_id)." and branch_id=".mi($branch_id)." limit 1");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$tmp){	// alrdy no items
			$con->sql_query("update promotion
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0
	where id=$promo_id and branch_id=$branch_id");
			log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion Cancelled (ID#$promo_id) due to all items has been cancelled.");
		}
	}

	function view()
	{
		global $smarty, $LANG;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		//$form=$this->load_promo_header($promo_id, $branch_id);
		$form = load_discount_promo_header($branch_id, $promo_id);
		
		if (!$form){
			if ($_REQUEST['ajax']) die(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
			$smarty->assign("url", "/promotion.php");
			$smarty->assign("title", "Promotion");
			$smarty->assign("subject", sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
			$smarty->display("redir.tpl");
			exit;
		}
		$smarty->assign("readonly", 1);
		/*foreach ($form['promo_branch_id'] as $t=>$k)
		{
			$pg_bid[] = $t;
		}
		if(count($pg_bid) > 0) $this->pg_bid = join($pg_bid);*/

		//$this->load_promo_items();
		load_discount_promo_items($branch_id, $promo_id);
		$smarty->assign("form", $form);
		if ($_REQUEST['ajax'])
			$smarty->display("promotion.ajax_view.tpl");
		else
			$smarty->display("promotion.new.tpl");
	}

	function ajax_add_item_row()
	{
		global $con, $smarty, $sessioninfo, $LANG, $config;
//check sku not empty
		if (!$_REQUEST['sku_code_list'])
		{
			print $LANG['PROMO_SELECT_SKU'];
			exit;
		}

		$con->sql_query("select count(*) as count from tmp_promotion_items where promo_id=".$this->promo_id." and branch_id =".$this->branch_id." and user_id=$sessioninfo[id] group by promo_id");
		$item_count=$con->sql_fetchrow();


//		print $item_count['count'];
/*
		foreach($_REQUEST['sku_code_list'] as $sku_item_id)
		{
			$sku_item_id=intval($sku_item_id);

			//check duplicate sku in list

			$con->sql_query("select * from tmp_promotion_items where sku_item_id = $sku_item_id and promo_id = ".$this->promo_id." and branch_id = ".$this->branch_id." and ".(($this->promo_id==0)?" user_id = $sessioninfo[id]":1));
			if ($con->sql_numrows() > 0)
			{
				print $LANG['PROMO_DUPLICATE_ITEM'];
				exit;
			}
		}
*/
		$max_limit_promotion = mi($config['promo_add_sku_items_max_limit']) ? mi($config['promo_add_sku_items_max_limit']) : 500;
		$new_item_count = count($_REQUEST['sku_code_list']);
		if($item_count['count'] + $new_item_count > $max_limit_promotion){
			print sprintf($LANG['PROMO_LIMIT_ITEMS'], $max_limit_promotion);
			exit;
		}
		
		if ($_REQUEST['include_all_sku_item']) {
			$sku_item_id_list = array();
			foreach ($_REQUEST['sku_code_list'] as $sku_item_id) {
				$sql_id_list = $con->sql_query("select id from sku_items where sku_id = (select sku_id from sku_items where id = $sku_item_id) and active = 1 order by is_parent desc, sku_item_code asc");
				while ($res_id_list = $con->sql_fetchassoc($sql_id_list)) {
					if (!in_array($res_id_list['id'],$sku_item_id_list)) $sku_item_id_list[] = $res_id_list['id'];
				}
			}
			$_REQUEST['sku_code_list'] = $sku_item_id_list;
		}
		
		foreach($_REQUEST['sku_code_list'] as $sku_item_id)
		{
			$sku_item_id=intval($sku_item_id);
			//check duplicate sku in list

			//$con->sql_query("select * from tmp_promotion_items where sku_item_id = $sku_item_id and promo_id = ".$this->promo_id." and branch_id = ".$this->branch_id." and ".(($this->promo_id==0)?" user_id = $sessioninfo[id]":1));
			
			/*$con->sql_query("select * from tmp_promotion_items where sku_item_id = $sku_item_id and promo_id = ".$this->promo_id." and branch_id = ".$this->branch_id." and user_id = $sessioninfo[id]");
			if ($con->sql_numrows() > 0)
			{
				print $LANG['PROMO_DUPLICATE_ITEM'];
				exit;
			}*/

			$r=$this->get_items_detail($sku_item_id,$this->branch_id, $g_bid);

			if ($_REQUEST['consignment_bearing']=="yes" && !$r['item_price_type']){
				print $LANG['MCB_NO_PRICE_TYPE'];
				exit; 				
			}

 			$items[] = $r;

			/*foreach($_REQUEST['promo_branch_id'] as $pbid => $pbc)
			{
	  			$con->sql_query("select pi.*, p.status, p.active, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, if(sp.price is null, si.selling_price, sp.price) as selling_price, sc.grn_cost, sc.qty from promotion_items pi
	  					left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id
	  					left join sku_items si on pi.sku_item_id = si.id
	  					left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = pi.branch_id
	  					left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = pi.branch_id
	  					where p.promo_branch_id like '%".$pbc."%' and (".ms(dmy_to_sqldate($_REQUEST['date_from']))." between date_from and date_to or	".ms(dmy_to_sqldate($_REQUEST['date_to']))."
	  					between date_from and date_to or date_from between ".ms(dmy_to_sqldate($_REQUEST['date_from']))." and ".ms(dmy_to_sqldate($_REQUEST['date_to'])).") and
	  					pi.sku_item_id = ".mi($sku_item_id)." and p.status <> 5 and p.status <> 4");
	  			if ($con->sql_numrows()>0)
	  			{
	  				while($r2 = $con->sql_fetchrow())
	  				{
	  					$ditems[$r['id']][] = $r2;
	  				}
	  			}
			}*/
		}
		
		// find overlap
		if ($items){
			foreach($items as $k=>$pi)		$this->add_temp_row($items[$k]);
		
		    if(!$config['promotion_turn_off_overlap_info']){
                $bid_list = array();
				foreach($items as $pi)
				{
					$samepromos = find_overlap_promo($_REQUEST, array('sku_item_id'=>$pi['sku_item_id'], 'include_mprice_qprice'=>true, 'include_category_disc'=>true), $bid_list);
					if ($samepromos['discount'])
					{
						foreach($samepromos['discount'] as $r2)
						{
							$ditems['discount'][$pi['id']][] = $r2;
						}
					}

					if ($samepromos['mix_n_match']){
		                foreach($samepromos['mix_n_match'] as $r2)
						{
							$ditems['mix_n_match'][$pi['id']][] = $r2;
						}
					}
					
					if ($samepromos['mprice']){
		                foreach($samepromos['mprice'] as $bid=>$r2)
						{
							$ditems['mprice'][$pi['id']][$bid] = $r2;
						}
					}
					
					if ($samepromos['qprice']){
		                foreach($samepromos['qprice'] as $r2)
						{
							$ditems['qprice'][$pi['id']][] = $r2;
						}
					}
					
					if ($samepromos['category_disc']){
		                foreach($samepromos['category_disc'] as $r2)
						{
							$ditems['category_disc'][$pi['id']][] = $r2;
						}
					}
					
					if ($samepromos['category_disc_by_sku']){
		                foreach($samepromos['category_disc_by_sku'] as $r2)
						{
							$ditems['category_disc_by_sku'][$pi['id']][] = $r2;
						}
					}
				}
			}
		    
		}

		$predisc = explode(",",$_REQUEST['d']);
		$non_consignment=!empty($_REQUEST['non_consignment']) ? end($_REQUEST['non_consignment']) : $_REQUEST['non_consignment'];
		$_REQUEST['non_selected_consignment']=$non_consignment;

		$member_consignment=!empty($_REQUEST['member_consignment']) ? end($_REQUEST['member_consignment']) : $_REQUEST['member_consignment'];
		$_REQUEST['member_selected_consignment']=$member_consignment;

       	$smarty->assign("form", $_REQUEST);

       	//$this->assign_consignment();
       	assign_consignment();
		//print_r($items);
		$smarty->assign("predisc", $predisc);
		$smarty->assign("itemcount", ($item_count['count']));
		$smarty->assign("promotion_items", $items);
		$smarty->assign("promo_item_count",count($items));
		$smarty->assign("allow_edit",1);
		$smarty->assign("ditems",$ditems);
		print trim($smarty->fetch('promotion.new.promotion_row.tpl'));
	}

	//add new item in temporary table
	function add_temp_row(&$r, $foc_sz=''){
		global $con, $sessioninfo, $LANG, $config;
		$r['promo_id']=$this->promo_id;
		$r['branch_id']=$this->branch_id;
		$r['user_id']=$sessioninfo['id'];
	    if ($_REQUEST['consignment_bearing'] == 'yes'){
	        if (isset($_REQUEST['m_consignment'])){
				list($member_profit,$member_discount,$member_use_net,$member_bearing,$member_trade_code)=explode(',',$_REQUEST['m_consignment']);	
				$member_discount=$_REQUEST['m_disc']?$_REQUEST['m_disc']:$member_discount;

				$r['member_trade_code']=$member_trade_code;
				$r['member_prof_p']=$member_profit;
				$r['member_disc_p']=$member_discount;
				$r['member_use_net']=$member_use_net;
				$r['member_net_bear_p']=$member_bearing;
			}else{
				$r['member_trade_code']='';
				$r['member_prof_p']=0;
				$r['member_disc_p']=0;
				$r['member_use_net']=0;
				$r['member_net_bear_p']=0;
			}

			if (isset($_REQUEST['nm_consignment'])){
				list($non_profit,$non_discount,$non_use_net,$non_bearing, $non_trade_code)=explode(',',$_REQUEST['nm_consignment']);
				$non_discount=$_REQUEST['nm_disc']?$_REQUEST['nm_disc']:$non_discount;

				$r['non_member_trade_code']=$non_trade_code;
				$r['non_member_prof_p']=$non_profit;
				$r['non_member_disc_p']=$non_discount;
				$r['non_member_use_net']=$non_use_net;
				$r['non_member_net_bear_p']=$non_bearing;
			}else{
				$r['non_member_trade_code']='';
				$r['non_member_prof_p']=0;
				$r['non_member_disc_p']=0;
				$r['non_member_use_net']='no';
				$r['non_member_net_bear_p']=0;
			}

		}else{
			if(!isset($r['member_disc_p']))	$r['member_disc_p']=$_REQUEST['m_disc'];
			if(!isset($r['non_member_disc_p']))	$r['non_member_disc_p']=$_REQUEST['nm_disc'];
		}

		if(!isset($r['member_disc_a']))	$r['member_disc_a']=$_REQUEST['m_price'];
		if(!isset($r['non_member_disc_a']))	$r['non_member_disc_a']=$_REQUEST['nm_price'];
		$r['member_min_item']=$_REQUEST['m_min'];
		$r['member_qty_from']=$_REQUEST['m_from'];
		$r['member_qty_to']=$_REQUEST['m_to'];
		$r['non_member_min_item']=$_REQUEST['nm_min'];
		$r['non_member_qty_from']=$_REQUEST['nm_from'];
		$r['non_member_qty_to']=$_REQUEST['nm_to'];

		$con->sql_query("insert into tmp_promotion_items ".mysql_insert_by_field($r, array('promo_id', 'branch_id', 'user_id', 'sku_item_id', 'brand_id',
		'member_trade_code','member_prof_p','member_disc_p','member_use_net','member_net_bear_p', 'member_disc_a', 'member_min_item', 'member_qty_from', 'member_qty_to',
		'non_member_trade_code','non_member_prof_p','non_member_disc_p','non_member_use_net','non_member_net_bear_p', 'non_member_disc_a', 'non_member_min_item', 'non_member_qty_from','non_member_qty_to','item_price_type')));
	 	$r['id']=$con->sql_nextid();

	 	return $r['id'];
	}

	function get_items_detail($sku_item_id, $branch_id, $g_bid)
	{
		global $con;

		// escape integer
        $branch_id = mi($branch_id);
        $sku_item_id = mi($sku_item_id);

		//if($g_bid) $sic = "and sc.branch_id in (".$g_bid.")";

		$con->sql_query("select if(sp.price is null, si.selling_price, sp.price) as selling_price, si.mcode, si.artno, si.sku_item_code,
						 ifnull(sc.grn_cost,si.cost_price) as grn_cost, sc.qty, si.id as sku_item_id, si.description,
						 brand.description as brand, sku.brand_id,
						 if(sp.price is null, sku.default_trade_discount_code, sp.trade_discount_code) as item_price_type, sku.id as sku_id
						 from sku_items si
						 left join sku on si.sku_id = sku.id
						 left join brand on sku.brand_id = brand.id
						 left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = $branch_id
						 left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = $branch_id
						 where si.id = ".$sku_item_id);
		$r = $con->sql_fetchrow();

		return $r;
	}

	function ajax_get_sku_type()
	{
		global $con;

		$sql = "select * from sku_type where active=1 order by code";

		$q1 = $con->sql_query($sql) or die(mysql_error());


		print "	<b>Sku Type</b> ";
		print "<select name=sku_type>";

		if (isset($_REQUEST['show_all'])){
		    if($selected=='All'){

		        print "<option value='All' selected>-- All --</option>";
		    }else{

                print "<option value='All'>-- All --</option>";
			}

		}
		while($r=$con->sql_fetchassoc($q1))
		{
		    if($selected==$r['code']){
		        print "<option value=\"$r[code]\" selected> $r[description]</option>";
			}else{
	            print "<option value=\"$r[code]\"> $r[description]</option>";
			}
		}
		$con->sql_freeresult($q1);
		print "</select>";

		exit;

	}

	function ajax_get_price_type()
	{
		global $con;
		//$sql = "select distinct sku_items_price.trade_discount_code as price_type from sku_items_price having price_type <> '' order by price_type";
		$sql = "select code as price_type from trade_discount_type order by price_type";

		$con->sql_query($sql) or die(mysql_error());

		print "	<b>Price Type</b> ";
		print "<select name=price_type>";

		if (isset($_REQUEST['show_all'])){
		    if($selected=='All'){

		        print "<option value='All' selected>-- All --</option>";
		    }else{

                print "<option value='All'>-- All --</option>";
			}

		}

		while($r=$con->sql_fetchrow())
		{
		    if($selected==$r['price_type']){
		        print "<option value=\"$r[price_type]\" selected> $r[price_type]</option>";
			}else{
	            print "<option value=\"$r[price_type]\"> $r[price_type]</option>";
			}
		}
		print "</select>";

		exit;
	}

	function ajax_get_sku()
	{
		global $con, $smarty,$config;

		$limit = 1000;
		$category_id = mi($_REQUEST['category_id']);
		$filter[] = $_REQUEST['brand_id'] ? 'sku.brand_id = '.mi($_REQUEST['brand_id']) : 1;
		$filter[] = $_REQUEST['sku_type']!='All'?'sku_type = '.ms($_REQUEST['sku_type']):1;
		$price_type = $_REQUEST['price_type']!='All'?'((price_type = '.ms($_REQUEST['price_type']).') or ((price_type = "" or price_type IS NULL) and default_trade_discount_code = '.ms($_REQUEST['price_type']).' ))':1;

		if (!$category_id && !$_REQUEST['brand_id'] && $_REQUEST['sku_type']=='All' && $_REQUEST['price_type']=='All' && !$_REQUEST['vendor_id']) {
			print "<p align=center>Please select at least one search criteria</p>";
			exit;
		}
		
		if($_REQUEST['sku_id'])
		{
			$filter[] = "sku_items.id not in ('".join("','",$_REQUEST['sku_id'])."')";
		}

		//consignment bearing
		if ($config['use_consignment_bearing']){
			if ($_REQUEST['consignment_bearing'] == 'yes'){
	            $filter[] = "sku.sku_type='CONSIGN'";
				if ($_REQUEST['s_vendor_id'])   	$filter[] = "sku.trade_discount_type=2 and sku.vendor_id=".intval($_REQUEST['s_vendor_id']);
		   		elseif ($_REQUEST['s_brand_id'])  	$filter[] = "sku.trade_discount_type=1";
			}else{
	            $filter[] = "sku.sku_type='OUTRIGHT'";
			}
		}
		
		if ($_REQUEST['vendor_id']) {
			$filter[] = "sku.vendor_id = ".mi($_REQUEST['vendor_id']);
		}

		if($category_id!='')
		{
	        $con->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
			$temp = $con->sql_fetchrow();
			$level = $temp['level'];
			
			$filter[] = "p$level = $category_id";
		}
		
		if ($filter)	$where_filter = join(' and  ',$filter);
		
		$item_limit = 1000;
		$items = array();
		
		foreach($_REQUEST['promo_branch_id'] as $bid =>$bcode)
		{
			$con->sql_query("select sku.default_trade_discount_code, sku_items.id, sku_items.sku_item_code, sku_items.description, sku_items.artno, sku_items.mcode, sku_items.sku_id, sku.varieties, sku_items.block_list, sku.sku_type, (select trade_discount_code from sku_items_price where sku_item_id = sku_items.id and branch_id = $bid) as price_type,sku.brand_id,sku.vendor_id from sku_items
				left join sku on sku_items.sku_id = sku.id
				left join category_cache on sku.category_id = category_cache.category_id
				where $where_filter
				group by sku.id, sku_item_code
				having $price_type
				limit $item_limit");
			$curr_count = count($items);
			while($r = $con->sql_fetchrow())
			{
				if(!isset($items[$r['id']])){
					$items[$r['id']] = $r;
					$curr_count++;
				}				
				if($curr_count>=$item_limit)	break;
			}
		}
		
//		$pp = ",$r[cost_price],$r[selling_price]";
		if ($con->sql_numrows()>0)
		{
		    //$this->assign_consignment();
		    assign_consignment();
		    $smarty->assign("form",$_REQUEST);
			$smarty->assign("items",$items);
			
			if($curr_count>=$item_limit){
				$smarty->assign("reach_item_limit",$item_limit);
			}
			

			print $smarty->fetch('promotion.new.add_promo.tpl');
			/*
			print "<textarea rows=10 cols=200>$abc
			
(".count($items)." rows)</textarea>";
			*/
		}
		else {
			print "<p align=center>No item found.</p>";
			/*
			print "<textarea rows=10 cols=200>$abc
			
(".count($items)." rows)</textarea>";
			*/
		}
	}

	function ajax_get_brand()
	{
		global $con;

		$category_id = $_REQUEST['category_id'];
		$selected = $_REQUEST['selected'];
		$view_all = $_REQUEST['view_all'];
		$r_type = $_REQUEST['r_type'];
		$bearing_filter='';
		if ($_REQUEST['consignment_bearing']=='yes' && $r_type!='none'){
			if ($r_type == 'vendor')
			    $bearing_filter = ' and sku.vendor_id='.intval($_REQUEST['s_vendor_id']);
			elseif ($r_type == 'brand')
			    $bearing_filter = ' and sku.brand_id='.intval($_REQUEST['s_brand_id']);
		}

		if(isset($_REQUEST['view_all'])&&$_REQUEST['view_all']=='all_brand'){
		    $con->sql_query("select id as brand_id,description from brand order by description") or die(mysql_error());
		}else{
            if($category_id!=''){
	            $con->sql_query("select level from category where id=".mi($category_id)) or die(mysql_error());
				$temp = $con->sql_fetchrow();
				$level = $temp['level'];

				if($level!=0){
	                $sql = "select distinct(brand_id),description
					from sku left join category_cache using (category_id)
					left join brand on brand_id = brand.id
					where p$level=".mi($category_id)." and description <> '' $bearing_filter order by description";

					$con->sql_query($sql) or die(mysql_error());
				}
			}
		}
		print "	<b>Brand</b> ";
		print "<select name=brand_id \">";

		if ($_REQUEST['consignment_bearing']!='yes'){
			if (isset($_REQUEST['show_all'])){
			    if($selected=='All'){

			        print "<option value='All' selected>-- All --</option>";
			    }else{

	                print "<option value='All'>-- All --</option>";
				}

			}

			if($selected==0&&$selected!='All'){
	            print "<option value=0 selected>UNBRANDED</option>";
			}else{
	            print "<option value=0>UNBRANDED</option>";
			}
		}

		while($r=$con->sql_fetchrow())
		{
		    if($selected==$r['brand_id']){
		        print "<option value=\"$r[brand_id]\" selected> $r[description]</option>";
			}else{
	            print "<option value=\"$r[brand_id]\"> $r[description]</option>";
			}
		}
		print "</select>";

		exit;
	}

	function do_print()
	{
		global $con, $smarty, $LANG, $config, $appCore;

		if (isset($_REQUEST['load'])){
			$_REQUEST['load'];
			//$form=$this->load_promo_header();
			$form = load_discount_promo_header($this->branch_id, $this->promo_id);
			/*foreach ($form['promo_branch_id'] as $t=>$k)
			{
				$pg_bid[] = $t;
			}
			if(count($pg_bid) > 0) $this->pg_bid = join(",", $pg_bid);*/
			//$promo_items=$this->load_promo_items();
			$promo_items = load_discount_promo_items($this->branch_id, $this->promo_id);
			
			//for consignemnt bearing getting the data 
			if ($form['consignment_bearing']=='yes'){
				foreach ($promo_items as $no => $att){
					if (strpos($att['cb_member_disc_p'],"%")){
						$promo_items[$no]['member_disc_p']=$att['cb_member_disc_p'];
						unset($promo_items[$no]['cb_member_disc_p']);
					}
		
					if (strpos($att['cb_non_member_disc_p'],"%")){
						$promo_items[$no]['non_member_disc_p']=$att['cb_non_member_disc_p'];
						unset($promo_items[$no]['cb_non_member_disc_p']);
					}	
				}	
			}
		}
		else{
			$form=$_REQUEST;

			if($form['readonly']){
				//$promo_items=$this->load_promo_items();
				$promo_items = load_discount_promo_items($this->branch_id, $this->promo_id);
			}
			else{
				//update items discount to tmp table
				foreach($form['member_disc_p'] as $k => $v)
				{
					$con->sql_query("update tmp_promotion_items set
									member_trade_code=".ms($form['member_trade_code'][$k]).",
									member_prof_p = ".ms($form['member_prof_p'][$k]).",
									member_disc_p = ".ms($form['member_disc_p'][$k]).",
									member_use_net = ".ms($form['member_use_net'][$k]).",
									member_net_bear_p = ".ms($form['member_net_bear_p'][$k]).",
									member_disc_a = ".mf($form['member_disc_a'][$k]).",
									non_member_trade_code=".ms($form['non_member_trade_code'][$k]).",
									non_member_prof_p = ".ms($form['non_member_prof_p'][$k]).",
									non_member_disc_p = ".ms($form['non_member_disc_p'][$k]).",
									non_member_use_net = ".ms($form['non_member_use_net'][$k]).",
									non_member_net_bear_p = ".ms($form['non_member_net_bear_p'][$k]).",
									non_member_disc_a = ".mf($form['non_member_disc_a'][$k]).",
									member_qty_from = ".mi($form['member_qty_from'][$k]).",
									member_qty_to = ".mi($form['member_qty_to'][$k]).",
									non_member_qty_from = ".mi($form['non_member_qty_from'][$k]).",
									non_member_qty_to = ".mi($form['non_member_qty_to'][$k]).",
									member_min_item = ".mi($form['member_min_item'][$k]).",
									non_member_min_item = ".mi($form['non_member_min_item'][$k])."
									where id = ".mi($k))
									or die(mysql_error());
				}

				//$promo_items=$this->load_promo_items(true);
				$promo_items = load_discount_promo_items($this->branch_id, $this->promo_id, true);
			}
		}

		// this is mix and match promotion
		if($form['promo_type']=='mix_and_match'){
			header("Location: promotion.mix_n_match.php?a=print_promotion&branch_id=".$this->branch_id."&id=".$this->promo_id);
			exit;
		}

		$con->sql_query("select * from branch where id = ".$this->branch_id);
		$branch_info = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$con->sql_query("select fullname from user where id = " . mi($form['user_id']));
		$r = $con->sql_fetchrow();
		$form['fullname'] = $r[0];

		if($_REQUEST['print_type']=='mot'){	// Ministry of Trade 
			$_REQUEST['mot_fmt'] = 1;
		}
		
		$print_by_branch = 0;
		if($this->branch_id == 1){
			$print_by_branch = mi($_REQUEST['print_by_branch']);
			if($print_by_branch){
				// Print by branch
				$print_promo_bid = $_REQUEST['print_promo_bid'];
				if(!$print_promo_bid || !is_array($print_promo_bid)){
					display_redir($_SERVER['PHP_SELF'], "Failed to Print Promotion ID#$this->promo_id", "No branch is selected");
				}
			}			
		}		
		
		$show_stock = true;	// Default need show stock balance
		if($_REQUEST['mot_fmt'] || $config['promo_print_hide_column_stock'])	$show_stock = false;
		
		// Need to get promo branch stock balance
		if($print_by_branch && ($show_stock || $config['promo_print_hide_zero_stock'])){
			//print_r($form);			
			// Loop Promo Branch ID
			foreach($print_promo_bid as $promo_bid){
				// Loop item
				foreach($promo_items as $key => $r){
					if($promo_bid == $this->branch_id){
						// own branch - copy from column 'qty'
						$promo_items[$key]['branch_stock_data'][$promo_bid] = $r['qty'];
					}else{
						// other branch - Get Branch Stock Balance
						$result = $appCore->skuManager->getSKULatestStockAndCost($r['sku_item_id'], $promo_bid);
						$promo_items[$key]['branch_stock_data'][$promo_bid] = $result['qty'];
					}
					
				}
			}
		}
				
		//print_r($promo_items);
		
		$smarty->assign("branch_info", $branch_info);
		$smarty->assign("form", $form);
		
		$printed_count = 0;
		
		if($print_by_branch){	// Print by branch			
			// Loop branch
			foreach($print_promo_bid as $promo_bid){
				$params = array();
				$params['show_stock'] = $show_stock;
				$params['print_by_branch'] = 1;
				$params['print_promo_bid'] = $promo_bid;
				$printed_count += $this->promo_sheet_print($form, $promo_items, $params);
			}
		}else{	// print single copy
			$params = array();
			$params['show_stock'] = $show_stock;
			$printed_count += $this->promo_sheet_print($form, $promo_items, $params);
		}
		
		if(!$printed_count){
			display_redir($_SERVER['PHP_SELF'], "Failed to Print Promotion ID#$this->promo_id", "No item is printed");
		}

/*		$con->sql_query("update po set print_counter=print_counter+1, last_update=last_update where id=$form[id] and branch_id=$form[branch_id]");
		log_br($sessioninfo['id'], 'PURCHASE ORDER', $form['id'], "Print PO (ID#$form[po_no])");
*/	}

	function promo_sheet_print($form, $promo_items, $params = array())
	{
		global $con, $smarty, $config;

		$page_size = $config['promotion_print_item_per_page']>0 ? $config['promotion_print_item_per_page'] : 45;		
		$tpl = "promotion.print.tpl";
		
		// Need show stock balance or not
		$show_stock = mi($params['show_stock']);
		
		// Got Print by Branch
		if(isset($params['print_by_branch'])){
			$print_by_branch = $params['print_by_branch'];
			$print_promo_bid = mi($params['print_promo_bid']);
			
			if($print_by_branch && $print_promo_bid){
				$con->sql_query("select * from branch where id = $print_promo_bid");
				$branch_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(($show_stock || $config['promo_print_hide_zero_stock']) && !$_REQUEST['mot_fmt']){
					foreach($promo_items as $key => $r){
						$promo_items[$key]['qty'] = $r['branch_stock_data'][$print_promo_bid];	// replace stock balance column
					}
				}
				
				$smarty->assign("branch_info", $branch_info);
			}
		}
		
		// Need to hide zero stock balance
		if(($config['promo_print_hide_zero_stock']) && !$_REQUEST['mot_fmt']){
			foreach($promo_items as $key => $r){
				if($r['qty']<=0){
					unset($promo_items[$key]);
				}
			}
		}
		
		//print_r($promo_items);
		if(!$promo_items){
			return 0;
		}
		
		$totalpage=ceil(count($promo_items)/$page_size);
		$smarty->assign('page_size', $page_size);
		$smarty->assign('print_promo_bid', $print_promo_bid);
		$smarty->assign('show_stock', $show_stock);
		
		for ($j=0,$page=1;$j<count($promo_items);$j+=$page_size,$page++){
		    $smarty->assign("page", " $page of $totalpage");
		    $smarty->assign("start_counter", $j);
		    $smarty->assign("promo_items", array_slice($promo_items,$j,$page_size));

			$smarty->display("$tpl");
			$smarty->assign("skip_header",1);
		}
		return 1;
	}
	
	function export_to_csv()
	{
		global $con, $smarty, $LANG;
		
		// check id must exists
		if(!$this->branch_id || !$this->promo_id){
			js_redirect(sprintf($LANG['PROMO_PROVIDE_ID'], $this->promo_id), $_SERVER['PHP_SELF']);			
		}
		
		// get promotion and promotion items
		$form = load_discount_promo_header($this->branch_id, $this->promo_id);
		$promo_items = load_discount_promo_items($this->branch_id, $this->promo_id);

		// check promotion exists
		if(!$form){
			js_redirect(sprintf($LANG['PROMO_NOT_FOUND'], $this->promo_id), $_SERVER['PHP_SELF']);
		}
		
		//for consignemnt bearing getting the data 
		/*if ($form['consignment_bearing']=='yes'){
			foreach ($promo_items as $no => $att){
				if (strpos($att['cb_member_disc_p'],"%")){
					$promo_items[$no]['member_disc_p']=$att['cb_member_disc_p'];
					unset($promo_items[$no]['cb_member_disc_p']);
				}
	
				if (strpos($att['cb_non_member_disc_p'],"%")){
					$promo_items[$no]['non_member_disc_p']=$att['cb_non_member_disc_p'];
					unset($promo_items[$no]['cb_non_member_disc_p']);
				}	
			}	
		}*/

		// this is mix and match promotion
		/*if($form['promo_type']=='mix_and_match'){
			header("Location: promotion.mix_n_match.php?a=print_promotion&branch_id=".$this->branch_id."&id=".$this->promo_id);
			exit;
		}*/
		
		// export to CSV
		$filename = 'ARMS_Promotion_'.$this->promo_id.'.csv';
		$fp = fopen($filename, 'w');

		// CSV header
		$header_array = array('NO','ARMS CODE','MCODE','ART NO','SKU DESCRIPTION','SELLING PRICE','MEMBER DISCOUNT','MEMBER PRICE','MEMBER NET','MEMBER TYPE','MEMBER LIMIT','NON MEMBER DISCOUNT','NON MEMBER PRICE','NON MEMBER NET');
		fputcsv($fp, $header_array);
		
		foreach($promo_items as $promo_item)
		{
			$counter++;
			$member_net_sp = '';
			$non_member_net_sp = '';
			$items = array();
			$items[] = $counter;
			$items[] = $promo_item['sku_item_code'];
			$items[] = $promo_item['mcode'];
			$items[] = $promo_item['artno'];
			$items[] = $promo_item['description'];
			$items[] = $promo_item['selling_price'];
				if(!$promo_item['member_disc_p']){
					$member_disc_p = '';
				}else{
					$member_disc_p = $promo_item['member_disc_p'];
				}
			$items[] = $member_disc_p;
				if(!$promo_item['member_disc_a']){
					$member_disc_a = '';
				}else{
					$member_disc_a = $promo_item['member_disc_a'];
				}
			$items[] = $member_disc_a;
				if($promo_item['member_disc_p']){
					if(strpos($promo_item['member_disc_p'],'%')){
						$member_net_per = $promo_item['selling_price']*$promo_item['member_disc_p']/100;
						$member_net_sp = $promo_item['selling_price']-$member_net_per;
						$member_net_sp = number_format($member_net_sp, 2);
					}else{
						$member_net_sp = $promo_item['selling_price']-$promo_item['member_disc_p'];
						$member_net_sp = number_format($member_net_sp, 2);
					}
				}
			$items[] = $member_net_sp;
				if($promo_item['control_type']=='1'){
					$control_type = 'D';
				}elseif($promo_item['control_type']=='2'){
					$control_type = 'P';
				}
			$items[] = $control_type;
				if(!$promo_item['member_limit']){
					$member_limit = '';
				}else{
					$member_limit = $promo_item['member_limit'];
				}
			$items[] = $member_limit;
				if(!$promo_item['non_member_disc_p']){
					$non_member_disc_p = '';
				}else{
					$non_member_disc_p = $promo_item['non_member_disc_p'];
				}
			$items[] = $non_member_disc_p;
				if(!$promo_item['non_member_disc_a']){
					$non_member_disc_a = '';
				}else{
					$non_member_disc_a = $promo_item['non_member_disc_a'];
				}
			$items[] = $non_member_disc_a;
				if($promo_item['non_member_disc_p']){
					if(strpos($promo_item['non_member_disc_p'],'%')){
						$non_member_net_per = $promo_item['selling_price']*$promo_item['non_member_disc_p']/100;
						$non_member_net_sp = $promo_item['selling_price']-$non_member_net_per;
						$non_member_net_sp = number_format($non_member_net_sp, 2);
					}else{
						$non_member_net_sp = $promo_item['selling_price']-$promo_item['non_member_disc_p'];
						$non_member_net_sp = number_format($non_member_net_sp, 2);
					}
				}
			$items[] = $non_member_net_sp;
			fputcsv($fp, $items);
		}
		
		fclose($fp);
		
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=ARMS_Promotion_'.$this->promo_id.'.csv');
		print file_get_contents($filename);
			
		unlink($filename);
		exit;
	}

	function revoke()
	{
		global $con, $smarty, $sessioninfo,$LANG;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

        $new_promo_bid = $sessioninfo['branch_id'];
        $new_promo_id = create_new_promo($new_promo_bid); // create new promotion

        // select old promotion
        $con->sql_query("select title, date_from, date_to, time_from, time_to, promo_branch_id, consignment_bearing, dept_id,r_type, vendor_id, brand_id
		from promotion where id=$promo_id and branch_id=$branch_id");
		$temp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$temp)  display_redir($_SERVER['PHP_SELF'], "Promotion", "Promotion ID#$promo_id cannot found.");

		$temp['user_id'] = $sessioninfo['id'];
		$temp['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update promotion set ".mysql_update_by_field($temp)." where branch_id=$new_promo_bid and id=$new_promo_id");

		/*$con->sql_query("insert into promotion
	(branch_id, user_id, title, date_from, date_to, time_from, time_to, promo_branch_id)
	select
	branch_id, $sessioninfo[id], title, date_from, date_to, time_from, time_to, promo_branch_id
	from promotion where id=$promo_id and branch_id=$branch_id");
		$new_promo_id = $con->sql_nextid();
		$con->sql_query("update promotion set added=CURRENT_TIMESTAMP, revoke_id=$new_promo_id where id=$promo_id and branch_id=$branch_id");*/

		//insert new po items to the revoked po
		$q2=$con->sql_query("select * from promotion_items where promo_id=$promo_id and branch_id=$branch_id order by id");
		while($r2=$con->sql_fetchrow($q2)){
			$r2['branch_id']=$new_promo_bid;
			$r2['promo_id']=$new_promo_id;
			$r2['user_id']=$sessioninfo['id'];

			$con->sql_query("insert into promotion_items " . mysql_insert_by_field($r2, array('promo_id', 'branch_id', 'user_id', 'sku_item_id', 'brand_id', 'member_disc_p', 'member_disc_a', 'member_min_item', 'member_qty_from', 'member_qty_to', 'non_member_disc_p', 'non_member_disc_a', 'non_member_min_item', 'non_member_qty_from', 'non_member_qty_to','allowed_member_type')));
			$promo_item_id= $con->sql_nextid();
		}

		$smarty->assign("id", $new_promo_id);
		$smarty->assign("type", "revoke");
		log_br($sessioninfo['id'], 'PROMOTION', $new_promo_id, "Promotion Revoked (ID#$promo_id -> ID#$new_promo_id)");
		header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_REVOKED'], $promo_id, $new_promo_id)));
	}

	function copy()
	{
		global $con, $smarty, $sessioninfo,$LANG;

		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;

		$con->sql_query("select * from promotion where id=$promo_id and branch_id=$branch_id ");

		if ($sessioninfo['branch_id'] != $branch_id)
		{
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_INVALID_COPY'])));
			exit;
		}

        $new_promo_bid = $sessioninfo['branch_id'];
        $new_promo_id = create_new_promo($new_promo_bid); // create new promotion

        // select old promotion
        $con->sql_query("select title, date_from, date_to, time_from, time_to, promo_branch_id, consignment_bearing, dept_id,r_type, vendor_id, brand_id
		from promotion where id=$promo_id and branch_id=$branch_id");
		$temp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if(!$temp) display_redir($_SERVER['PHP_SELF'], "Promotion", "Promotion ID#$promo_id cannot found.");

		$temp['user_id'] = $sessioninfo['id'];
		$temp['added'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update promotion set ".mysql_update_by_field($temp)." where branch_id=$new_promo_bid and id=$new_promo_id");

		/*$con->sql_query("insert into promotion
		(branch_id, user_id, title, date_from, date_to, time_from, time_to, promo_branch_id, added, consignment_bearing, dept_id,r_type, vendor_id, brand_id)
		select
		branch_id, $sessioninfo[id], title, date_from, date_to, time_from, time_to, promo_branch_id, CURRENT_TIMESTAMP, consignment_bearing, dept_id,r_type, vendor_id, brand_id
		from promotion where id=$promo_id and branch_id=$branch_id ");
		$new_promo_id = $con->sql_nextid();*/

		if ($new_promo_id)
		{
			//insert new po items to the revoked po
			$q2=$con->sql_query("select * from promotion_items where promo_id=$promo_id and branch_id=$branch_id order by id");
			while($r2=$con->sql_fetchrow($q2)){
				$r2['branch_id']=$new_promo_bid;
				$r2['promo_id']=$new_promo_id;
				$r2['user_id']=$sessioninfo['id'];

				$con->sql_query("insert into promotion_items " . mysql_insert_by_field($r2, array('promo_id', 'branch_id', 'user_id', 'sku_item_id', 'brand_id', 'member_trade_code', 'member_prof_p',
				'member_disc_p', 'member_use_net','member_net_bear_p', 'member_disc_a', 'member_min_item', 'member_qty_from', 'member_qty_to','non_member_trade_code', 'non_member_prof_p', 'non_member_disc_p', 'non_member_use_net', 'non_member_net_bear_p', 'non_member_disc_a', 'non_member_min_item', 'non_member_qty_from', 'non_member_qty_to','control_type','member_limit','non_member_limit','member_receipt_amt','non_member_receipt_amt','allowed_member_type')));
				$promo_item_id= $con->sql_nextid();
			}

			log_br($sessioninfo['id'], 'PROMOTION', $new_promo_id, "Promotion Copy (ID#$promo_id -> ID#$new_promo_id)");
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_COPY'], $promo_id, $new_promo_id)));
		}
		else
		{
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['PROMO_NOT_COPY'], $promo_id, $new_promo_id)));
		}
	}
	
	function check_tmp_item_exists() {
		global $con, $sessioninfo;
		
		if ($_REQUEST['selling_price']) {
			$sql = "select count(*) as c from tmp_promotion_items where id in (".join(',',array_keys($_REQUEST['selling_price'])).") and branch_id = ".mi($_REQUEST['branch_id'])." limit 1";
			$con->sql_query($sql);
			if ($con->sql_fetchfield('c') == count($_REQUEST['selling_price'])) print 'OK';
			else print "Error saving document : Probably it is opened & saved before in other window/tab";
			exit;
		}
		else {
			print 'OK';
			exit;
		}
	}
	
	function edit_member_mobile(){
		global $con, $smarty, $LANG, $sessioninfo;
		
		// Check Privilege
		if(!privilege('PROMOTION_MEMBER_MOBILE_CONFIGURE')){
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION', BRANCH_CODE), "/index.php");
		}
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		
		// Load Promotion
		$form = load_discount_promo_header($bid, $promo_id);
		if(!$form){
			js_redirect(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id), "/index.php");
		}
		
		$promo_items = load_discount_promo_items($bid, $promo_id, false, $form);
		
		//print_r($promo_items);
		
		if($sessioninfo['branch_id'] == $bid && $form['active'] && $form['status'] == 1 && $form['approved'] == 1){
			$can_edit = true;
		}
		
		$smarty->assign('form', $form);
		$smarty->assign('promo_items', $promo_items);
		$smarty->assign('can_edit', $can_edit);
		$smarty->display('promotion.edit_member_mobile.tpl');
	}
	
	function ajax_change_promo_show_in_member_mobile(){
		global $con, $smarty, $LANG, $sessioninfo; 
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		$show_in_member_mobile = mi($_REQUEST['show_in_member_mobile']);
		
		//print_r($_REQUEST);exit;
		
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['show_in_member_mobile'] = $show_in_member_mobile;
		$con->sql_query("update promotion set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$promo_id");
		
		log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion (ID#$promo_id) ".($show_in_member_mobile?'Enabled':'Disabled')." show in membership mobile");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function upload_promo_banner(){
		global $con, $smarty, $LANG, $sessioninfo; 
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		
		// Create Folder
		$folder = "attch/promo_banner";
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Banner Folder');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";
			}
		}
		
		// Check Branch Folder
		$folder = $folder."/".$bid;
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Banner Branch Folder');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";
			}
		}
		
		// Check Promo Folder
		$folder = $folder."/".$promo_id;
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Banner Branch Sub Folder');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";
			}
		}
		
		//print_r($_FILES);
		
		$fname = 'banner_vertical_1';
		
		// Check File Error
		if ($_FILES[$fname]['error'] == 0 && preg_match("/\.(jpg|jpeg|png|gif)$/i",$_FILES[$fname]['name'], $ext)){
			$filename = "banner_vertical_1".$ext[0];
			$final_path = $folder."/".$filename;
			
			// Move File to Actual Folder
			if(move_uploaded_file($_FILES[$fname]['tmp_name'], $final_path)){
				$file_uploaded = true;
			}
			else{
				$file_uploaded = false;
			}
			
			// Call Back
			if($file_uploaded){
				log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion (ID#$promo_id) Vertical Banner #1 Uploaded");
			
				// Delete other file with same name but different extension
				foreach(glob("$folder/banner_vertical_1.*") as $f){
					if(basename($f) != $filename){
						unlink($f);
					}
				}
				
				print "<script>parent.PROMO_MEM_MOBILE.banner_vertical_1_uploaded('$final_path?".time()."');</script>";
			}else{
				print "<script>parent.alert('".$LANG['POS_SETTINGS_CANT_MOVE_FILE']."');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";
			}
		}elseif (!preg_match("/\.(jpg|jpeg|png|gif)$/i",$_FILES[$fname]['name'])){
			print "<script>parent.alert('".$LANG['POS_SETTINGS_INVALID_FORMAT']."');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";
		}	
		else{
			print "<script>parent.alert('".$LANG['POS_SETTINGS_UPLOAD_ERROR']."');parent.PROMO_MEM_MOBILE.banner_uploaded_failed();</script>";	
		}
	}
	
	function ajax_update_promo_item_in_membership_mobile(){
		global $con, $smarty, $LANG, $sessioninfo; 
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		$item_id = mi($_REQUEST['item_id']);
		$is_show = mi($_REQUEST['is_show']);
		
		$upd = array();
		$upd['show_in_member_mobile'] = $is_show;
		$con->sql_query($sql = "update promotion_items set ".mysql_update_by_field($upd)." where branch_id=$bid and promo_id=$promo_id and id=$item_id");
		//die($sql);
		$updated = $con->sql_affectedrows();
		if(!$updated)	die("Update Failed.");
		
		log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion (ID#$promo_id) (Item ID#$item_id) ".($is_show?'Show in':'Removed from')." Membership Mobile.");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_update_special_for_you_settings(){
		global $con, $smarty, $LANG, $sessioninfo; 
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		$enable_special_for_you = mi($_REQUEST['enable_special_for_you']);
		
		$upd = array();
		$upd['enable_special_for_you'] = $enable_special_for_you;
		$upd['special_for_you_info'] = serialize($_REQUEST['special_for_you_info']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query($sql = "update promotion set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$promo_id");
		//die($sql);
		$updated = $con->sql_affectedrows();
		if(!$updated)	die("Update Failed.");
		
		$str = "Promotion (ID#$promo_id) Enable Special For you: ".($enable_special_for_you ? 'Yes': 'No');
		if($enable_special_for_you){
			$str .= ", Target: ".$_REQUEST['special_for_you_info']['target'];
			$str .= ", Qty: ".$_REQUEST['special_for_you_info']['qty'];
			$str .= ", Month: ".$_REQUEST['special_for_you_info']['month'];
		}
		
		log_br($sessioninfo['id'], 'PROMOTION', $promo_id, $str);
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function promotion_pop_card_setting(){
		global $con, $smarty, $LANG, $sessioninfo;
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		
		// Check Privilege
		if(!privilege('PROMOTION_POP_CARD')){
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION_POP_CARD', BRANCH_CODE), "/index.php");
		}
		
		// Load Promotion
		$form = load_discount_promo_header($bid, $promo_id);
		if(!$form){
			js_redirect(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id), "/index.php");
		}
		$file = "attch/promo_pop_card/$bid/$promo_id/1.jpg";
		if(file_exists($file)){
			$file_time = filemtime($file);
			$form['promo_pop_photo'] = trim($file)."?t=".$file_time;
		}
		
		$promo_pop_cards_bg = array();
		$bg_folder = "ui/promo_pop_cards_bg";
		if(file_exists($bg_folder)){
			foreach  (array_merge(glob("$bg_folder/*.[jJ][pP][gG]"),glob("$bg_folder/*.[jJ][pP][eE][gG]")) as $f){
				$f = str_replace("$bg_folder/", "", $f);
				$f_name = substr($f, 0 , (strrpos($f, ".")));
				$promo_pop_cards_bg[$f_name] = $bg_folder."/".$f;
			}
		}
		$form['promo_pop_cards_bg'] = $promo_pop_cards_bg;
		
		$last_pop_card_print_settings = unserialize($form['last_pop_card_print_settings']);
		if($last_pop_card_print_settings){
			$form['promo_items'] = $last_pop_card_print_settings['promotion_item_list'];
			$form['printing_format'] = $last_pop_card_print_settings['settings']['printing_format'];
			$form['member_discount'] = $last_pop_card_print_settings['settings']['member_discount'];
			$form['card_per_page'] = mi($last_pop_card_print_settings['settings']['card_per_page']);
			$form['selling_price_branch_id'] = mi($last_pop_card_print_settings['settings']['selling_price_branch_id']);
			$form['background_image_setting'] = $last_pop_card_print_settings['settings']['background_image_setting'];
			$form['promo_pop_cards_bg_path'] = $last_pop_card_print_settings['settings']['promo_pop_cards_bg_path'];
		}
		$promo_items = load_discount_promo_items($bid, $promo_id, false, $form);
		
		$smarty->assign('form', $form);
		$smarty->assign('promo_items', $promo_items);
		$smarty->display('promotion.pop_card.tpl');
	}
	
	
	function upload_promo_pop_card_photo(){
		global $smarty, $LANG, $sessioninfo;
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		
		//create folder if not exists
		$folder = "attch/promo_pop_card";
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Pop Card Folder');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";
			}
		}
		
		// check branch folder
		$folder = $folder."/".$bid;
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Pop Card Folder');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";
			}
		}
		
		// check promo card folder
		$folder = $folder."/".$promo_id;
		if(!file_exists($folder)){
			$success = check_and_create_dir($folder);
			if(!$success){
				print "<script>parent.alert('Unable to Create Promotion Pop Card Folder');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";
			}
		}
		
		$fname = 'promo_pop_photo';
		// Check File Error
		if ($_FILES[$fname]['error'] == 0 && preg_match("/\.(jpg|jpeg)$/i",$_FILES[$fname]['name'], $ext)){
			$filename = "1.jpg";
			$final_path = $folder."/".$filename;
			
			// Move File to Actual Folder
			if(move_uploaded_file($_FILES[$fname]['tmp_name'], $final_path)){
				log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion (ID#$promo_id) Promotion Pop Card Uploaded");
				print "<script>parent.PROMO_POP_CARD.pop_promo_card_uploaded('$final_path?".time()."');</script>";
			}
			else{
				print "<script>parent.alert('".$LANG['POS_SETTINGS_CANT_MOVE_FILE']."');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";
			}
		}elseif (!preg_match("/\.(jpg|jpeg)$/i",$_FILES[$fname]['name'])){
			print "<script>parent.alert('Only JPG/JPEG allowed.');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";
		}	
		else{
			print "<script>parent.alert('".$LANG['POS_SETTINGS_UPLOAD_ERROR']."');parent.PROMO_POP_CARD.pop_promo_card_uploaded_failed();</script>";	
		}
	}
	
	//print and save settings
	function print_promo_pop_card(){
		global $con, $smarty, $LANG, $sessioninfo, $config;
		
		$bid = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['id']);
		$last_pop_card_print_settings = $promo_items = array();
		
		// Load Promotion
		$promo_data = load_discount_promo_header($bid, $promo_id);
		if(!$promo_data)  js_redirect(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id), "/index.php");
		
		$form = $_REQUEST;
		$form['title'] = $promo_data['title'];
		$form['date_from'] = date("d M Y", strtotime($promo_data['date_from']));
		$form['date_to'] = date("d M Y", strtotime($promo_data['date_to']));
		$selling_price_branch_id = mi($form['selling_price_branch_id']);
		
		if($promo_data['time_from'] = "00:00:00" && $promo_data['time_to'] != "23:59:00"){
			$form['time_from'] = date("g:i A", strtotime($promo_data['time_from']));
			$form['time_to'] = date("g:i A", strtotime($promo_data['time_to']));
		}
		
		if($form['background_image_setting'] == 'use_system_img'){
			$form['promo_pop_photo'] = $form['promo_pop_cards_bg'];
		}elseif($form['background_image_setting'] == 'use_upload_img'){
			//get pop card image
			$file = "attch/promo_pop_card/$bid/$promo_id/1.jpg";
			if(file_exists($file)){
				$file_time = filemtime($file);
				$form['promo_pop_photo'] = trim($file)."?t=".$file_time;
			}
		}elseif($form['background_image_setting'] == 'no_backgroud_img'){
			$form['promo_pop_photo'] = '';
		}
		
		//update promotion pop card settings
		if($form['promo_items']){
			$promtion_item_list = array_keys($form['promo_items']);
			$last_pop_card_print_settings['promotion_item_list'] = $promtion_item_list;
			
			$promotion_items = load_discount_promo_items($bid, $promo_id, false, $form);
			if($promotion_items){
				foreach($promotion_items as $key=>$val){
					$id= $val['id'];
					if(in_array($id, $promtion_item_list)){
						$con->sql_query("select * from sku_items_price where sku_item_id =".mi($val['sku_item_id'])." and branch_id=$selling_price_branch_id");
						$r=$con->sql_fetchrow();
						$con->sql_freeresult();
						if($r['price']){
							$val['selling_price'] = $r['price'];
						}
						//if($config['membership_module']){
							if(($form['member_discount'] =='' || $form['member_discount'] == 'member') && (!$val['member_disc_p'] && !$val['member_disc_a'])){
								if(!$val['member_disc_a'] && $val['non_member_disc_a'])  $val['member_disc_a'] = $val['non_member_disc_a'];
								if(!$val['member_disc_p'] && $val['non_member_disc_p'])  $val['member_disc_p'] = $val['non_member_disc_p'];
								$val['use_non_member_disc'] = true;
							}
							
							$member_disc_p = $val['member_disc_p'];
							$non_member_disc_p = $val['non_member_disc_p'];
							$val['member_price'] = $val['member_disc_a'];
							$val['non_member_price'] = $val['non_member_disc_a'];
							if(!(strpos($member_disc_p, "%") &&  $form['member_discount'] == 'member') && !(strpos($non_member_disc_p, "%") &&  $form['member_discount'] == 'non_member')){
								if((!strpos($member_disc_p, "%") || !strpos($non_member_disc_p, "%")) && ($val['member_price'] <= 0 || $val['non_member_price'] <=0)){
									if($val['member_price'] <= 0 && $member_disc_p){
										if(strpos($member_disc_p, "%"))  $val['member_price'] = $val['selling_price'] -($val['selling_price']*mf($member_disc_p)) / 100;
										else  $val['member_price'] = $val['selling_price'] - $member_disc_p;
									}
									
									if($val['non_member_price'] <= 0 && $non_member_disc_p){
										if(strpos($non_member_disc_p, "%"))  $val['non_member_price'] = $val['selling_price'] - ($val['selling_price']*mf($non_member_disc_p)) / 100;
										else  $val['non_member_price'] = $val['selling_price'] - $non_member_disc_p;
									}
								}
							}
						//}
						$promo_items[] = $val;
					}
				}
			}
		}		
		$last_pop_card_print_settings['settings']['printing_format'] = $form['printing_format'];
		if($config['membership_module'])  $last_pop_card_print_settings['settings']['member_discount'] = $form['member_discount'];
		$last_pop_card_print_settings['settings']['card_per_page'] = $form['card_per_page'];
		$last_pop_card_print_settings['settings']['selling_price_branch_id'] = mi($form['selling_price_branch_id']);
		$last_pop_card_print_settings['settings']['background_image_setting'] = $form['background_image_setting'];
		$last_pop_card_print_settings['settings']['promo_pop_cards_bg_path'] = $form['promo_pop_cards_bg'];
		
		$upd = array();
		$upd['last_pop_card_print_settings'] = serialize($last_pop_card_print_settings);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update promotion set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$promo_id");
		log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Update promtion (ID#$promo_id) settings and print promotion pop card");

		$smarty->assign('promo_items', $promo_items);
		$smarty->assign('form', $form);
		$smarty->display('promotion.print_pop_card.tpl');
	}

	/*function assign_consignment($get_from_request=true, $promo=''){
		global $con,$smarty,$sessioninfo;

		$filter='and cb.active=1';

		if ($get_from_request){
			if ($_REQUEST['consignment_bearing']!='yes')   return;

			if (!$_REQUEST['dept_id']) return;

			$filter.=" and r_type=".ms($_REQUEST['r_type']);
			if ($_REQUEST['r_type']=='vendor'){
				if (!$_REQUEST['s_vendor_id'])    return;
				else	$filter.=" and cb.vendor_id=$_REQUEST[s_vendor_id] ";
			}
			elseif ($_REQUEST['r_type']=='brand'){
	            if (!$_REQUEST['s_brand_id'])    return;
				else	$filter.=" and cb.brand_id=$_REQUEST[s_brand_id] ";
			}
		}else{
			$filter.=" and r_type=".ms($promo['r_type']);
			if ($promo['consignment_bearing']=='yes'){
                $_REQUEST['dept_id']=$promo['dept_id'];
				if ($promo['r_type']=='vendor' )
				    $filter.=" and cb.vendor_id=$promo[vendor_id] ";

				if ($promo['r_type']=='brand' )
				    $filter.=" and cb.brand_id=$promo[brand_id] ";
			}else{
				return;
			}
		}
		
		$sql="select cbi.trade_discount_type_code as code , cbi.profit, concat(cbi.discount,'%') as discount ,cbi.use_net, cbi.net_bearing
			from consignment_bearing_items cbi
			left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
			where cb.branch_id=$sessioninfo[branch_id] and cb.dept_id=$_REQUEST[dept_id] $filter
			order by r_type";

		$con->sql_query($sql);

		if ($con->sql_numrows()>0)
			$smarty->assign('consignment',$con->sql_fetchrowset());
	}*/
	
	/*function find_overlap_promo($promo, $sku_item_id, $bid_list){
		global $con, $sessioninfo;

		if (BRANCH_CODE != 'HQ'){
			$sql = " p.promo_branch_id like '%\"".BRANCH_CODE."\"%' ";
		}else{
			$sql = 1;
		}

		if ($this->pg_bid == ''){
	        if (BRANCH_CODE != 'HQ'){
	            $this->pg_bid=$sessioninfo['branch_id'];
			}else{
				$con->sql_query("select id from branch");
				while ($bid=$con->sql_fetchrow()){
					$arr_bid[]=$bid['id'];
				}
				$this->pg_bid=join(',',$arr_bid);
			}
		}
		$pol_sql = $con->sql_query("select pi.*, p.status, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, if(sp.price is null, si.selling_price, sp.price) as selling_price, sc.grn_cost, sc.qty from promotion_items pi
					left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id
					left join sku_items si on pi.sku_item_id = si.id
					left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = pi.branch_id
					left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = pi.branch_id
					where $sql and p.branch_id in ($this->pg_bid) and (".ms($promo['date_from'])." between date_from and date_to or
					".ms($promo['date_to'])." between date_from and date_to or date_from between ".ms($promo['date_from'])." and ".ms($promo['date_to']).") and
					pi.sku_item_id = ".mi($sku_item_id)." and p.id <> ".mi($promo['id'])." and p.status <> 5 and p.status <> 4");

		while($r2=$con->sql_fetchassoc($pol_sql)){
			$pol_items['discount'][] = $r2;
		}
		$con->sql_freeresult($pol_sql);

		return $pol_items;
	}*/
}

$promotion = new Promotion ('Promotion');

?>
