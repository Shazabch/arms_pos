<?php
/*
7/7/2010 4:51:46 PM Andy
- Fix currency table bugs.

7/15/2010 10:26:57 AM Andy
- Counter Collection 3 add currency float.

7/20/2010 10:59:54 AM Andy
- Add privilege to check whether user can do un-finalize counter collection or not. (System admin always can)

7/30/2010 11:49:06 AM Andy
- Fix counter collection sometime may occur sql error when doing finalize.

8/2/2010 12:37:57 PM Andy
- Fix a bugs when counter collection 3 have sales after the last close counter.

8/4/2010 1:40:50 PM Alex
- Fix showing receipt details by adding branch_id

11/10/2010 4:02:54 PM Justin
- Fixed the bugs where getting empty sales detail from membership history.

12/17/2010 5:33:31 PM Andy
- Add can change payment type 'others'.

12/20/2010 3:16:26 PM Andy
- Add round2 for sales variance to avoid negative zero variance.

12/21/2010 12:01:10 PM Alex
- check $config['csa_generate_report'] to create CSA report flag file

1/6/2011 10:48:28 AM Justin
- Fixed the bugs for calculating payment amount called from membership history.

1/7/2011 5:04:31 PM Andy
- Fix change cash domination cannot store odata bugs.

1/12/2011 5:18:55 PM Andy
- Fix sometime counter collection show no sales details if have pos cash domination.
- Add log when user create new cash domination.

1/13/2011 1:31:51 PM Andy
- Fix if after pos cash domination have transaction again, the to time show "N/A" bugs.

2/10/2011 3:29:47 PM Andy
- Fix unfinalize will not trigger system to recalculate stock balance.

3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.

4/11/2011 11:58:54 AM Andy
- Check if counter version is later than v109 then no need to +8 hour to cash domination timestamp.

4/7/2011 6:10:43 PM Alex
- Add transaction details to group payment by pos receipt.

4/13/2011 5:29:38 PM Andy
- add round2 for over amount.
- Add popup details for over amount transaction.

4/18/2011 5:57:15 PM Andy
- Fix if create new cash domination will also check counter revision to decide whether timestamp need to -8 hours.

4/29/2011 5:20:09 PM Andy
- Add checking if counter revision is later than v109, then it will check if last cash domination early than last pos, it will +8 hours. 

4/29/2011 5:39:05 PM Alex
- add get return item, item discount and open discount user from item_details() 

5/3/2011 3:37:02 PM Andy
- Fix a bug which cause if there are more than 1 cash domination it will only show the first cash domination.

5/4/2011 10:32:43 AM Andy
- Fix wrong timestamp checking.

6/10/2011 10:59:18 AM Andy
- Add print transaction details.

6/21/2011 5:17:59 PM Andy
- Fix "over" popup cannot show those receipt only got goods return but without pos payment.

6/21/2011 6:03:55 PM Alex
- add payment type approve by which user

6/24/2011 3:58:55 PM Andy
- Make all branch default sort by sequence, code.

7/4/2011 4:19:52 PM Andy
- Add config to counter collection to check whether need to +8 hours to latest cash denomination.

7/6/2011 11:12:26 AM Andy
- Change split() to use explode()

7/7/2011 3:13:43 PM Andy
- Fix a bugs cause counter collection change payment type may update wrong POS.

7/11/2011 11:06:50 AM Andy
- Move pp_is_currency() and pch_is_currency() to counter_collection.include.php
- Fix counter collection adjustment add coupon not working.

8/19/2011 10:20:49 AM Andy
- Fix change payment type bugs.

9/5/2011 9:58:04 AM Andy
- Fix cancel receipt bugs, the log store empty receipt number and din't no indicate it is activate or cancel receipt. 

9/6/2011 6:25:02 PM Alex
- add show multiple type of credit cards

9/20/2011 2:15:49 PM Andy
- Fix when change payment type will make rounding amount missing.

10/12/2011 5:29:36 PM Andy
- Add mix and match promotion at counter collection related module.

11/10/2011 6:14:25 PM Andy
- Fix counter collection to also show those mix and match discount which does not have discount amount. (eg: Free Voucher)

11/23/2011 5:46:41 PM Alex
- store counter collection data into database

12/20/2011 9:56:44 AM Andy
- Add show "Receipt Remark" at Receipt Item Details.

1/10/2012 3:33:43 PM Justin
- Fixed bugs that system unable to locate Advance Cash for different types of currencies.

2/13/2012 6:22:53 PM Justin
- Added to pick up Deposit info by counter.
- Modified to capture deposit info while view in item details.

2/29/2012 4:50:43 PM Justin
- Fixed the bugs when loading sales detail for member point history, it did not filter by branch.

3/9/2012 1:00:17 PM Andy
- Change finalize counter collection to submit by ajax, also add popup to show progress percentage.

3/30/2012 11:42:30 AM Andy
- Fix after adjust payment got wrong amount change.

4/12/2012 3:33:12 PM Justin
- Added to store deposit data into pos_counter_finalize.

5/3/2012 4:47:47 PM Andy
- Add to store trade in and top up data when finalize.
- Add "Top Up" information.
- Add "Trade In" information.

5/29/2012 4:06:00 PM Andy
- Fix when add new cash advance it wont record original amount.

6/27/2012 4:37:32 PM Justin
- Fixed the sales detail that shouldn't sum up rounding amount.

8/6/2012 11:12 AM Andy
- Add show total by counter.

8/10/2012 5:01 PM Andy
- Change counter collection to list POS by using end_time instead of pos_time.

8/13/2012 3:02 PM Andy
- Fix Transaction Details popup bug (sometime it group different receipt into 1 row).

8/15/2012 5:28 PM Andy
- Fix wrong sales and adjust amount if user adjust payment amount related to cash.

8/17/2012 4:48 PM Andy
- Show cash denomination user as cashier if found there is no sales cashier to show.

9/3/2012 11:47 AM Fithri
- Item details - show barcode

9/14/2012 4:34:00 PM Fithri
- add config to check must hv cash denom before finalize.
- add config to able to show multiple cashier name instead of only last cashier name

10/8/2012 10:30 AM Andy
- Add checking user privilege for cancel receipt (need config counter_collection_need_privilege_cancel_bill).

10/15/2012 2:10:00 PM Fithri
- Enhance log for Counter Collection to include counter_id and date (and other details)

10/26/2012 4:04 PM Andy
- Add "Membership Counter Info".
- Add cash advance into variance calculation.

11/2/2012 11:20 AM Justin
- Enhanced to use payment type from POS Settings as if found it is being set.

11/21/2012 9:50 AM Andy
- Fix Membership Counter Info collection not show if got multiple counter.

12/11/2012 11:39 AM Andy
- Add extra payment type feature.
- Add checking to payment type to show cash domination list.
- Add checking to payment type to show adjust payment list.

12/18/2012 3:01 PM Justin
- Bug fixed on system does not calculate the membership counter's variance while cannot find the cash domination.

12/27/2012 10:04 AM Justin
- Enhanced to capture missing log for counter collection data.

1/3/2013 3:33 PM Andy
- Fix wrong counter summary nett sales and adjustment.

1/17/2013 5:!1 PM Andy
- Add show how many quota used by the receipt in sales details.

2/5/2013 3:18 PM Fithri
- add adjusted payment receipt can revert to old payment type

2/21/2013 11:43 AM Andy
- Enhance to show those receipt have negative quota used in transaction details.
- Change to when create new cash domination, the user_id should be the login user, not the cashier.
- Change the log to can differential the edit cash domination or add new cash domination.

2/25/2013 3:58 PM Fithri
- add button close.
- add checking if is add new cash denom, no key in any amount then cannot save
- can delete the cash denom create from backend

3/6/2013 5:51 PM Andy
- Fix un-finalize cannot trigger item to recalculate cost and stock balance.

7/3/2013 4:44 PM Andy
- Enhance to show invalid member info.
- Show cancelled status in transaction details.

7/4/2013 1:45 PM Andy
- Enhance to record down if the transaction is cancel at backend.
- Enhance to show cancel at backend in popup transaction list.
- Enhance to add pos_receipt_cancel entry when cancel at backend.
- Fix print multiple cashier name bug.

07/23/2013 03:39 PM Justin
- Enhanced to check and insert/delete S/N history while doing finalize/unfinalize.

07/31/2013 11:49 AM Justin
- Enhanced to generate/delete pos_cashier_finalize while doing finalize/unfrinalize.

8/20/2013 10:15 AM Andy
- Fix counter collection deposit info.
- Enhance to show deposit refund and count the amount into cash.

10/10/2013 3:29 PM Andy
- Add ignore_user_abort() to prevent data loss.

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

11/26/2013 5:08 PM Andy
- Fix cash denomination negative zero bug.

12/30/2013 11:09 AM Andy
- Change to do not delete pos_finalized when unfinalize counter collection.
- Add auto check and pregen pos_finalized row if not found.

1/6/2014 4:38 PM Justin
- Enhanced to show counter by cashiers.

1/6/2014 5:13 PM Fithri
- make cash-advance value always negative
- make top-up value always positive
- show positive (absolute) value on screen

1/14/2014 3:44 PM Andy
- Remove clear_drawer=1 checking for pos_cash_domination.

1/14/2014 4:50 PM Justin
- Enhanced to use back the cashier ID for new cash advance insertion and record down the user who added the data as if found got config.
- Bug fixed on counters that no longer sort by network name.

1/17/2014 10:59 AM Justin
- Bug fixed on system shows error for pos_cash_history doesn't have more info.

1/20/2014 3:41 PM Justin
- Bug fixed on system will split the cash advance wrongly while having cash advance.

1/22/2014 2:11 PM Justin
- Enhanced to split cashier while view sales details, cash advance and adjustment.
- Enhanced to show name of cashier and collected by instead of ID.

1/29/2014 11:43 AM Justin
- Enhanced to align payment type of discount to show first then follow by other payment types.

2/6/2014 10:29 AM Fithri
- modify checking for invalid member card

2/13/2014 10:17 AM Fithri
- when cancel receipt at counter collection, it will also deletes records on membership promotion items

3/7/2014 5:38 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

3/19/2014 2:49 PM Justin
- Enhanced skip those cancelled receipt while searching invalid member.

3/25/2014 1:45 PM Justin
- Modified the wording from "Finalize" to "Finalise".

3/31/2014 1:48 PM Andy
- Add "Debit" into change payment type together with "Cash" and "Credit".

7/23/2014 5:49 PM Justin
- Enhanced to pickup payment type base on POS settings by branch when do adjustment.

8/6/2014 10:12 AM Justin
- Enhanced to skip "Coupon" and "Voucher" while picking up payment type from POS settings.

11/27/2014 4:59 PM Andy
- Enhance to show Service Charges, GST and Nett Sales 2.
- Change the calculation of Gross Sales.
- Enhance to display a better layout for receipt item details.
- Add to show the deleted item in receipt item details.

4/21/2015 5:42 PM Andy
- Enhanced to have view receipt mode.
- Enhanced to have goods return receipt link.

5/5/2015 3:33 PM Andy
- Enhanced to able to show transaction details using receipt ref no.
- Fix GST summary does not show zero rate amount.

5/6/2015 3:42 PM Andy
- Fix transaction details cannot show payment details if show by receipt ref no.

6/19/2015 2:49 PM Eric
- Enhanced to show by items and sales agent, use new function get_pos_by_receipt_ref_no()

12/3/2015 5:34 PM Andy
- Enhanced to allow user to view transaction details even total amount is zero.
- Enhanced to show service charge and gst once amount is not zero.

03/24/2016 17:45 Edwin
- Enchanced on showing Receipt Reference Number in tables and details pop out

4/15/2016 2:23 PM Andy
- Fix generate pos_cashier_finalize bug, date is always empty.

6/8/2016 3:12 PM Andy
- Fixed edit cash denom to show custom payment type.

06/09/2016 14:30 Edwin
- Reconstruct on 'ajax_add_receipt_row', 'ajax_add_payment_type', 'save_change_payment' and 'change_payment_type'.

8/16/2016 10:00 AM Andy
- Enhanced transaction details list payment amount to deduct receipt discount and mix & match discount, when there is no special type is given.

10/13/2016 10:47 AM Qiu Ying
- Fix nett sales amount in counter collection report incorrect

10/27/2016 11:13 AM Qiu Ying
- Bug fixed on receipt reference number does not match with actual receipt print 

11/22/2016 11:18 AM Andy
- Fixed foreign currency will missing if pos settings removed.
- Fixed column "RM" should only appear when got foreign currency.
- Add Special Cash Refund / Change.

11/24/2016 6:04 PM Andy
- Fixed denomination din't show currency data when pos settings removed.

12/22/2016 2:49 PM Andy
- Fixed deposit received by check cannot display.

1/9/2017 13:30 Qiu Ying
- Bug fixed on print empty item details from GST Credit Note Report

2/16/2017 2:46 PM Andy
- Fixed deposit received showing wrong before tax amount.

3/2/2017 3:01 PM Justin
- Enhanced to pickup deposit information while viewing item details.

3/3/2017 4:32 PM Andy
- Fix if pos_payment type is "Credit Cards", the sales details will unable to show the list.
- Enhanced to delete pos_finalised_error when finalise/unfinalise counter collection.

4/5/2017 4:26 PM Justin
- Enhanced to check against unsynced/missing data to disallow user to finalise if found any.

4/13/2017 16:21 Qiu Ying
- Bug fixed on Counter Collection Payment Type Missing

4/20/2017 8:46 AM Khausalya
- Enhanced changes from RM to use config setting. 

4/27/2017 3:38 PM Justin
- Enhanced to use cash_domination_notes from config instead of pos_config.

5/4/2017 10:34 AM Qiu Ying
- Bug fixed on when key in receipt no and keep entering multiple times, the receipt no will show multiple times.
- Bug fixed on when key in receipt no and end with a dot, the system will not prompt error message 

6/6/2017 10:09 AM Qiu Ying
- Bug fixed on deposit with no items will be shown in the receipt details in Counter Collection

2017-08-23 16:13 PM Qiu Ying 
- Bug fixed on showing wrong change amount

10/5/2017 9:53 AM Andy
- Change to get counter error from posManager.

10/17/2017 11:06 AM Andy
- Fixed custom payment type unable to show, due to case sensitive issue. need to convert to like "Member Point".

11/10/2017 5:03 PM Justin
- Enhanced to capture Reason for Cash Advance.
- Enhanced to always set "approved_by" field as current logged on user ID while add new cash advance.

11/29/2017 11:35 AM Andy
- Enhanced to show Sales Agent Code and Name when view transaction details.

3/30/2018 3:38 PM Justin
- Enhanced to remove sales cache from sales agent when unfinalise.

8/8/2018 10:55 AM Justin
- Bug fixed on cancelling or unset cancel status for receipt does not update membership_promotion_mix_n_match_items.

6/20/2018 3:48 PM Justin
- Enhanced to load foreign currency list base on sales and config.

10/9/2018 9:50 AM Justin
- Bug fixed on system will not display the foreign currency list if cash denomination has foreign currency but not for sales.

10/15/2018 11:11 AM Justin
- Bug fixed on GST summary will always show out when viewing Transaction Item Details.

1/2/2019 2:27 PM Andy
- Fixed if create denomination first then only do sales, the start time will become empty.

3/15/2019 11:32 AM Andy
- Enhanced to add ewallet payment into normal_payment_type array.
- Enhanced counter collection variances calculation to include ewallet sales and ewallet collection.
- Changed generate_pos_cashier_finalize() to posManaer->generatePosCashierFinalize()
- Enhanced Cash Denomination to have ewallet payment.

4/16/2019 5:17 PM Justin
- Enhanced to have void payment for eWallet while do cancel receipt.
- Disabled the "Undo cancelled receipt" while the transaction contain eWallet payment.
- Disabled the cancel receipt function while the transaction contain eWallet payment that are currently not on the present date.
- Enhanced Adjustment for receipt do not include eWallet payment type for user to choose.

10/18/2019 5:35 PM Justin
- Enhanced the looping of sales agent list to be compatible with newer POS counter version (v202).

12/10/2019 3:50 PM Andy
- Enchanced Add Cash Denomination to log denom time.
- Added record log for Delete Cash Denomination.

2/13/2020 1:28 PM Andy
- Fixed to only replace "_" to " " for credit card payment.

4/17/2020 10:29 AM William
- Enhanced to block finalise and un-finalize when got config "monthly_closing" and document date has closed.

6/11/2020 3:22 PM Andy
- Enhanced to delete data from sku_items_finalised_cache when unfinalise sales.

7/10/2020 4:30 PM Andy
- Added begin and commit for finalise and unfinalise.

4/22/2021 2:36 PM Andy
- Enhanced Cancel Receipt to can search by receipt ref no.
- Enhanced to not allow to change receipt status if the receipt contain deposit.
*/
class CounterCollection extends Module{
	var $branch_id;
	var $is_approval;
	var $err;
	var $normal_payment_type = array();
	var $got_mm_discount = false;
	var $got_top_up = false;
	var $got_service_charge = false;
	var $got_gst = false;
	
    function __construct($title, $template=''){
		global $con, $smarty, $config, $pos_config, $sessioninfo, $v109_time;
		$this->branch_id = $_REQUEST['branch_id'] ? mi($_REQUEST['branch_id']) : mi($sessioninfo['branch_id']);
		$this->v109_time = $v109_time;
        
		// get currency type and assign it into pos_config
		/*$con->sql_query("select * from pos_settings where branch_id=$this->branch_id and setting_name='currency'");
		$r = $con->sql_fetchrow();
		$con->sql_freeresult();
		$currencies = $r['setting_value'];
		if ($currencies) $currencies = unserialize($currencies);

		if (is_array($currencies))
		{
			//$pos_config['payment_type'] = array_merge($pos_config['payment_type'], array_keys($currencies));
            $pos_config['currency'] = array_keys($currencies);
            $pos_config['curr_rate'] = $currencies;
            //print_r($pos_config);
            foreach($pos_config['curr_rate'] as $currency_type=>$currency_rate){
                $currency_rate = sprintf("%01.3f", $currency_rate);
                if(!$currency_rate) continue;

				$this->currency_data[$currency_type]['currency_rate'][$currency_rate]= array();
			}
		}*/
		
		$this->foreign_currency_list = array();

		// Cash is default
		$this->normal_payment_type[] = 'Cash';
		
		// select other payment type from pos settings
		$q1 = $con->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
		$ps_info = $con->sql_fetchrow($q1);
		$ps_payment_type = unserialize($ps_info['setting_value']);
		$con->sql_freeresult($q1);

		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				$ori_ptype = $ptype;
				
				if(strpos(strtolower($ptype), "credit_card")===0){
					$ptype = str_replace("_", " ",$ptype);	// only replace "_" to " " if it is credit card
				}
				$ptype = ucwords($ptype);
				//$ptype = ucwords(str_replace("_", " ",$ptype));
				if($ptype == "Credit Card") $ptype = "Credit Cards";
				
				if(!$val) continue;	// in-active
				
				$this->normal_payment_type[] = $ptype;
			}
		}
		
		/*if(!$this->normal_payment_type){
			foreach($pos_config['payment_type'] as $ptype){
				if($ptype=='Discount')  continue;
				$this->normal_payment_type[] = $ptype;
			}
		}*/
		
		// extend by extra payment type
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ori_ptype = $ptype;
				$ptype = ucwords(strtolower($ptype));
				
				// store for available payment type, but not always in use
				if(!in_array($ptype, $pos_config['payment_type']))	$pos_config['payment_type'][] = $ptype;
				$pos_config["payment_type_label"][$ptype] = $ori_ptype;
			}
		}
		
		//$pos_config['normal_payment_type'] = $this->normal_payment_type;
		//print_r($pos_config['payment_type']);
		//print_r($this->normal_payment_type);
		$smarty->assign("pos_config",$pos_config);

		// check whether the user can do finalize or not
		$this->is_approval = privilege('CC_FINALIZE');

		// get all counters in this branch
		$con->sql_query("select * from counter_settings where branch_id=$this->branch_id order by network_name");
		while($r = $con->sql_fetchrow()){
			$counters[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign("counters", $counters);

		$con->sql_query("select id,u from user");
		while($r = $con->sql_fetchrow()){
			$username[$r['id']] = $r['u'];
		}
		$con->sql_freeresult();
		$smarty->assign("username", $username);
		$this->username = $username;

		// get counter errors
		/*$con->sql_query("select pcct.*, cs.network_name, branch.code
		from pos_counter_collection_tracking pcct
		left join counter_settings cs on cs.id = pcct.counter_id and cs.branch_id = pcct.branch_id
		left join branch on branch.id = pcct.branch_id
		where error <> '' and pcct.branch_id=$this->branch_id
		order by branch.sequence, branch.code");
		$smarty->assign("collection_error", $con->sql_fetchrowset());
		$con->sql_freeresult();*/
		
		
		if (!isset($_REQUEST['no_display']))	parent::__construct($title, $template);
	}

    function _default(){
		global $smarty,$con;

		if (!isset($_REQUEST['date_select']) || $_REQUEST['date_select'] == '')
		{
			$_REQUEST['date_select'] = date('Y-m-d');
		}
		// show counter collection data
		$this->show($_REQUEST['date_select'], 'open');
	}

	function view_by_date(){
		$this->_default();
	}

	private function show($date, $mode = 'view'){
	    global $con, $smarty, $pos_config, $sessioninfo,$mm_discount_col_value, $config, $appCore;

		$this->finalize=$_REQUEST['finalize'];

		if($mode = 'open'){
            $smarty->assign("allow_edit", $this->is_approval);
		}

		// load data
		check_invalid_code($this->branch_id,$date);
		$this->list_data($date);
		$prms = array();
		$prms['date'] = $date;
		$prms['branch_id'] = $this->branch_id;
		check_sales_sync_status($prms);

		// got errors
		if($this->err)  $smarty->assign('err', $this->err);

		$this->calculate_total_data();
		
		if($config['counter_collection_show_membership_receipt']){
			$this->load_membership_receipt_info();
		}
		
		if($config['membership_valid_cardno']){
			$this->load_invalid_member_info();
		}
		//print_r($this->data);
		if($sessioninfo['u'] == 'admin'){
			//print_r($this->total);
		}
		
		if($this->total){
			check_and_pregen_pos_finalized($this->branch_id, $date);
		}
		
		// get error
		$counters_error = $appCore->posManager->getCounterError($this->branch_id);
		$smarty->assign("counters_error",$counters_error);
		$ss_error = $appCore->posManager->getSyncServerError($this->branch_id);
		$smarty->assign("ss_error",$ss_error);
		
		//print_r($this->data);
		//print_r($this->total);
		//print_r($pos_config['currency']);
		//print_r($this->currency_data);
		
		$smarty->assign('got_mm_discount', $this->got_mm_discount);
		$smarty->assign('got_top_up', $this->got_top_up);
		$smarty->assign('got_service_charge', $this->got_service_charge);
		$smarty->assign('got_gst', $this->got_gst);
		$smarty->assign('total', $this->total);
		$smarty->assign('data', $this->data);
		//$smarty->assign('currency_data', $this->currency_data);
		$smarty->assign('pos_cash_domination', $this->pos_cash_domination);
		asort($this->normal_payment_type);
		//print_r($this->normal_payment_type);
		$smarty->assign('normal_payment_type', $this->normal_payment_type);
		$smarty->assign("pos_config",$pos_config);
		if($this->got_foreign_currency){
			$smarty->assign("foreign_currency_list", $this->foreign_currency_list);
			$smarty->assign("got_foreign_currency", $this->got_foreign_currency);
		}
		
		//save 
		unset($this->got_mm_discount,$this->total,$this->data,$this->pos_cash_domination);
		if ($this->store_data && $this->finalize){
			store_counter_collection_data($this->branch_id,$date,$this->store_data);
		}

		unset($this->store_data,$this->finalize);

		if (!isset($_REQUEST['no_display']))	$this->display();
	}
	
	private function list_data($date){
        global $con, $smarty, $config, $pos_config, $sessioninfo;

        if(!$date)  $this->err[] = "Invalid Date";
        $date_time = strtotime($date);

        if($this->err)  return;
        //  check finalize status
        if ($this->check_finalized($date))	$smarty->assign("is_finalized",1);

        $filter = array();
        $filter[] = "p.branch_id=$this->branch_id and p.date=".ms($date);
        $filter = join(" and ", $filter);

	
        // get those counter which have pos
        $con->sql_query("select distinct counter_id, cs.network_name, p.cashier_id as user_id
		from pos p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter and p.cancel_status=0 order by cs.network_name");
		
		while($r = $con->sql_fetchrow())
		{
			$pos_counters[$r['network_name']][$r['user_id']] = $r;
		}
		$con->sql_freeresult();

		// get those counter got counter collection (cash domination)
		$con->sql_query("select distinct counter_id, cs.network_name, p.user_id
		from pos_cash_domination p
		left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
		where $filter order by cs.network_name");
		while($r = $con->sql_fetchrow())
		{
			$pos_counters[$r['network_name']][$r['user_id']] = $r;
		}
		$con->sql_freeresult();
		
		if($config['counter_collection_split_counter_by_cashier']){
			// get those counter got cash advance
			$con->sql_query("select distinct counter_id, cs.network_name, p.user_id
			from pos_cash_history p
			left join counter_settings cs on p.counter_id=cs.id and p.branch_id=cs.branch_id
			where $filter order by cs.network_name");
			while($r = $con->sql_fetchrow())
			{
				$pos_counters[$r['network_name']][$r['user_id']] = $r;
			}
			$con->sql_freeresult();
		}
		
		// check got service charge or gst 
		$con->sql_query("select sum(service_charges) as total_service_charges, sum(total_gst_amt) as total_gst_amt 
			from pos p
			where $filter");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp['total_service_charges']!=0)	$this->got_service_charge = true;
		if($tmp['total_gst_amt']!=0)	$this->got_gst = true;
		
		if ($pos_counters){
			$filter_cashier = false;
            ksort($pos_counters);   // sort array base on counter name

			//if($sessioninfo['id'] == 1) print_r($pos_counters);
			
			// check got mix and match discount
            foreach($pos_counters as $dummy=>$cashiers){
				foreach($cashiers as $cashier_id=>$r1){
					$counter_id = $r1['counter_id'];
					if($config['counter_collection_split_counter_by_cashier']){
						$split_type = $cashier_id;
						if(!$filter_cashier){
							$pos_filter = " and p.cashier_id = ".mi($cashier_id);
							$oth_filter = " and p.user_id = ".mi($cashier_id);
						}
						else $filter_cashier = true;
					}else $split_type = "by_dom";
				
					$counter_version = get_counter_version($this->branch_id, $counter_id);

					// get min & max pos time
					$con->sql_query("select min(p.end_time) as s , max(p.end_time) as e
					from pos p
					where $filter and p.counter_id=".mi($counter_id).$pos_filter);

					$r = $con->sql_fetchassoc();
					$counter_whole_day_start = $r['s'];
					$counter_whole_day_end = $r['e'];
					$con->sql_freeresult();

					// get min & max pos cash domination time
					$con->sql_query("select min(p.timestamp) as s , max(p.timestamp) as e
					from pos_cash_domination p
					where $filter and p.counter_id=".mi($counter_id).$oth_filter);

					$r = $con->sql_fetchrow();
					if($r['e']&&$r['s']){
						if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time || ($counter_version>=109 && !is_latest_than_last_pos($this->branch_id, $counter_id, $date)))){
							$r['e'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['e'])));
							$r['s'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['s'])));
						}

						if((strtotime($r['s'])<strtotime($counter_whole_day_start)||!$counter_whole_day_start)&&$r['s'])	$counter_whole_day_start = $r['s'];
						if((strtotime($r['e'])>strtotime($counter_whole_day_end)||!$counter_whole_day_end)&&$r['e'])	$counter_whole_day_end = $r['e'];
					}
					$con->sql_freeresult();

					// change whole day end to last pos
					$con->sql_query("select max(end_time) from pos p where $filter and p.counter_id=".mi($counter_id)." and p.cashier_id = ".mi($cashier_id));
					$max_end_time = $con->sql_fetchfield(0);
					$con->sql_freeresult();

					if(strtotime($max_end_time)>strtotime($counter_whole_day_end))  $counter_whole_day_end = $max_end_time;


					// get min & max pos cash advance time
					$con->sql_query("select min(p.timestamp) as s , max(p.timestamp) as e
					from pos_cash_history p
					where $filter and p.counter_id=".mi($counter_id).$oth_filter);

					$r = $con->sql_fetchrow();
					if($r['e']&&$r['s']){
						if((strtotime($r['s'])<strtotime($counter_whole_day_start)||!$counter_whole_day_start)&&$r['s'])	$counter_whole_day_start = $r['s'];
						if((strtotime($r['e'])>strtotime($counter_whole_day_end)||!$counter_whole_day_end)&&$r['e'])	$counter_whole_day_end = $r['e'];
					}

					$con->sql_freeresult();

					// get pos cash domination list for "counter collection"
					$sql = "select * from pos_cash_domination p where $filter and p.counter_id=".mi($counter_id).$oth_filter." order by timestamp";
					//print $sql;
					$q_dom = $con->sql_query($sql);

					$start_time = $counter_whole_day_start;
					while($r = $con->sql_fetchrow($q_dom)){			
						// add 8 hours due to frontend time bugs
						if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time || ($counter_version>=109 && !is_latest_than_last_pos($this->branch_id, $counter_id, $date)))){
							$r['timestamp'] = date("Y-m-d H:i:s", strtotime("+8 hour", strtotime($r['timestamp'])));
						}

						$r['start_time'] = $start_time;
						$r['end_time'] = $r['timestamp'];
						$r['data'] = unserialize($r['data']);
						$r['odata'] = unserialize($r['odata']);
						$r['curr_rate'] = unserialize($r['curr_rate']);
						$r['ocurr_rate'] = unserialize($r['ocurr_rate']);
						$r['split_type'] = $split_type;

						$this->pos_cash_domination[$counter_id][$split_type][$r['id']] = $r;

						$start_time = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['end_time'])));
						
						// check if cash denomination do have foreign currency
						if($r['curr_rate']){
							//$this->got_foreign_currency = true;
							foreach($r['curr_rate'] as $tmp_curr_type=>$tmp_curr_rate){
								$this->check_and_create_foreign_currency_list($tmp_curr_type, $tmp_curr_rate);
							}
							
							if($this->foreign_currency_list) $this->got_foreign_currency = true;
						}
					}
					$con->sql_freeresult($q_dom);

					if(!$config['counter_collection_split_counter_by_cashier']){
						
					}
					
					
					
					if(!$this->pos_cash_domination[$counter_id][$split_type]){   // no pos cash domination found
						$this->pos_cash_domination[$counter_id][$split_type][0]['start_time'] = $counter_whole_day_start;
						$this->pos_cash_domination[$counter_id][$split_type][0]['end_time'] = $counter_whole_day_end;
					}
				}
				
				
				// get sales data
				$this->generate_data($counter_id, $date);
				// if got counter session which have no pos cash domination
				if(isset($this->data[$counter_id][$split_type][''])){
					$this->pos_cash_domination[$counter_id][$split_type]['']['end_time'] = $counter_whole_day_end;
				}
			}
		}
		//print_r($this->pos_cash_domination);
	}

	private function check_finalized($date){
		global $con;
		//$con->sql_query("select count(*) as count from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($date))." and finalized = 1 group by date");
		$con->sql_query("select * from pos_finalized where branch_id=".mi($this->branch_id)." and date=".ms($date)." and finalized=1");
		if ($con->sql_numrows()>0)
			return true;
		else
			return false;
	}

	private function get_pos_cash_domination_id($counter_id, $timestamp, $split_type){
		if(!$this->pos_cash_domination[$counter_id][$split_type])    return 0;
		foreach($this->pos_cash_domination[$counter_id][$split_type] as $dom_id=>$r){
			if(strtotime($timestamp)>=strtotime($r['start_time'])&&strtotime($timestamp)<=strtotime($r['end_time'])){
			    return $dom_id; // return the domination ID which the timestamp belongs to
			}
		}
	}

	private function generate_data($counter_id, $date){
        global $con, $pos_config, $mm_discount_col_value, $config;

        $counter_id = mi($counter_id);
		//$cashier_list = array();
		
		$filter = "p.branch_id=$this->branch_id and p.date=".ms($date)." and p.counter_id=$counter_id";

		// get from pos first
		$sql = "select p.* from pos p where $filter and p.cancel_status=0 order by end_time";
		//print $sql."<br />";
		//print_r($this->pos_cash_domination);
		$q_pos = $con->sql_query($sql);
		while($pos = $con->sql_fetchrow($q_pos)){
		    // assign pos_id
		    $pos_id = mi($pos['id']);
			
			if($config['counter_collection_split_counter_by_cashier']){
				$split_type = $pos['cashier_id'];
			}else $split_type = "by_dom";
			
            // get counter domination id
			$dom_id = $this->get_pos_cash_domination_id($counter_id, $pos['end_time'], $split_type);

		    // get from pos payment
		    $q_pp = $con->sql_query("select type, remark, changed, adjust, amount from pos_payment p where $filter and pos_id=$pos_id");
		    
		    // check got mix and match
		    if(!$this->data[$counter_id][$split_type][$dom_id]['others']['got_mm_discount']){
				$q_pmm = $con->sql_query("select id from pos_mix_match_usage p where $filter and p.pos_id=$pos_id limit 1");
				if($con->sql_numrows($q_pmm)){
					$this->data[$counter_id][$split_type][$dom_id]['others']['got_mm_discount']=1;
					$this->got_mm_discount = true;
				}
				$con->sql_freeresult($q_pmm);
			}
			
			$amount_change_dealed = false;
			
		    while($pp = $con->sql_fetchrow($q_pp)){
		        $is_changed = mi($pp['changed']);
		        $is_adjust = mi($pp['adjust']);
		        $row_type = 'cashier_sales';
		        $add_to_nett_sales = true;
		        if($is_changed){
                    $row_type = 'adj';
                    $add_to_nett_sales = false;
				}
				$adj_amt = 0;

				// is one of the credit cards
	            if (in_array($pp['type'], $pos_config['credit_card'])) $payment_type = 'Credit Cards';
				elseif ($pp['type'] == 'Cheque') $payment_type = 'Check';
	            else	$payment_type = $pp['type'];
				
				if(preg_match('/^ewallet_/i', $payment_type)){	// eWallet don uppercase
					$payment_type = strtolower($payment_type);
				}else{
					$payment_type = ucwords(strtolower($payment_type));
				}
				

                $is_foreign_currency = false;
                
                if($is_adjust || $is_changed)  $adj_amt = $pp['amount'];
                
                // check payment type
                switch ($payment_type){
                	case $mm_discount_col_value:
                		//$payment_type = 'Discount';	// store together with receipt discount
                		$this->got_mm_discount = true;
					case 'Discount':    // it is discount
					    $rm_amt = $pp['amount']*-1;  // discount show as negative
					    $add_to_nett_sales = false;
					    break;
					case 'Cash':    // it is cash payment
						$rm_amt = $pp['amount'] - $pos['amount_change'];    // amount decrease changed
						//print "Cash amt = $rm_amt<br>";
						$amount_change_dealed = true;
					    break;
					/*case 'Mix & Match Total Disc':
						$rm_amt = $pp['amount']*-1;
						$add_to_nett_sales = false;
					*/
					case 'Deposit':	// pay by deposit
						if($pos['amount_change']){	// got amt changed
							// check whether this deposit got pay by cash
							$con->sql_query("select count(*) from pos_payment p where $filter and p.pos_id=$pos_id and type='Cash' and p.adjust=0");
							$deposit_got_cash = mi($con->sql_fetchfield(0));
							$con->sql_freeresult();
						
							if(!$deposit_got_cash){	// this deposit dun hv cash but got change
								$payment_type = 'Cash';	// change payment type to cash
								$rm_amt = $pos['amount_change']*-1;	// make refund count into cash sales
								$amount_change_dealed = true;
								//$this->data[$counter_id][$dom_id]['deposit_refund_amt']+=$pos['amount_change'];
							}
							//print "payment_type = $payment_type<br>";
						}
						break;
					default:
					    // check is foreign currency
			            $currency_arr = pp_is_currency($pp['remark'], $pp['amount']);
			            if($currency_arr['is_currency']){   // it is foreign currency
							$currency_amt = $currency_arr['currency_amt'];
							$currency_rate = $currency_arr['currency_rate'];
							$this->got_foreign_currency = $is_foreign_currency = true;
							$payment_type = strtoupper($payment_type);
						}
						$rm_amt = round($currency_arr['rm_amt'], 2);
					    break;
				}
				
				if($is_foreign_currency){
					$this->check_and_create_foreign_currency_list($payment_type, $currency_rate);
				}
				
				if(strpos($payment_type, '_Float')){   // currency float
                    $payment_type = str_replace('_Float', '', $payment_type);
				}
				
				if($payment_type && $payment_type != $mm_discount_col_value && $payment_type != "Discount" && !in_array($payment_type, $this->normal_payment_type) && (in_array($payment_type, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $payment_type))){
					$this->normal_payment_type[] = $payment_type;
				}

				// row type is either 'cashier_sales' or 'adj'
				if($is_foreign_currency){   // is foreign currency	
					$use_amt = $is_changed ? $adj_amt : $rm_amt;
									
					$this->data[$counter_id][$split_type][$dom_id][$row_type]['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt;
	                $this->data[$counter_id][$split_type][$dom_id][$row_type]['foreign_currency'][$payment_type]['rm_amt'] += $use_amt;

					/*$this->currency_data[$payment_type]['currency_rate'][$currency_rate][$row_type]['foreign_amt'] += $currency_amt;
	                $this->currency_data[$payment_type]['currency_rate'][$currency_rate][$row_type]['rm_amt'] += $use_amt;*/
				}else{
					// if getting adj out, cashier sales will still use $rm_amt, only use $adj_amt if adj in
					$use_amt = $is_changed ? $adj_amt : $rm_amt;
					$this->data[$counter_id][$split_type][$dom_id][$row_type][$payment_type]['amt'] += $use_amt;
					$this->data[$counter_id][$split_type][$dom_id][$row_type][$payment_type]['got_data'] = 1;
				}

				// adjustment
				if($is_adjust){
                    if($is_foreign_currency){   // is foreign currency
		                $this->data[$counter_id][$split_type][$dom_id]['adj']['foreign_currency'][$payment_type]['foreign_amt'] += $currency_amt*-1;
		                $this->data[$counter_id][$split_type][$dom_id]['adj']['foreign_currency'][$payment_type]['rm_amt'] += $adj_amt*-1;
						
						/*$this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cashier_sales']['foreign_amt'] += $currency_amt*-1;
		                $this->currency_data[$payment_type]['currency_rate'][$currency_rate]['cashier_sales']['rm_amt'] += $adj_amt*-1;*/
					}else{
						$this->data[$counter_id][$split_type][$dom_id]['adj'][$payment_type]['amt'] += $adj_amt*-1;
						//$this->total[$counter_id]['adj'][$payment_type]['amt'] += $rm_amt*-1;
					}
					//$this->data[$counter_id][$dom_id]['adj']['nett_sales']['amt'] += $rm_amt*-1;
				}

				if($add_to_nett_sales){
                    //$this->data[$counter_id][$dom_id][$row_type]['nett_sales']['amt'] += $rm_amt;
					//$this->total[$counter_id]['cashier_sales']['nett_sales']['amt'] += $rm_amt;
					//$this->total['total']['nett_sales']['amt'] += $rm_amt;
				}
			}
			$con->sql_freeresult($q_pp);
			
			// sepcial cash refund / change
			if($pos['amount_change'] > 0 && !$amount_change_dealed){
				//print "extra change = ".$pos['amount_change']."<br>";
				$this->data[$counter_id][$split_type][$dom_id]['cash_change']['amt'] += $pos['amount_change'];
				$this->data[$counter_id][$split_type][$dom_id]['cash_change']['got_data'] = 1;
				
				$this->data[$counter_id][$split_type][$dom_id]['cashier_sales']['Cash']['amt'] -= $pos['amount_change'];
				$this->data[$counter_id][$split_type][$dom_id]['cashier_sales']['Cash']['got_data'] = 1;
			}

			// check if having deposit amt
			/*$q_pd = $con->sql_query("select *
									from pos_deposit_status_history pdsh
									where 
									(pdsh.pos_id = ".mi($pos_id)." and 
									pdsh.pos_date = ".ms($date)." and 
									pdsh.branch_id = ".mi($this->branch_id)." and 
									pdsh.counter_id = ".mi($counter_id).") or
									(pdsh.deposit_pos_id = ".mi($pos_id)." and 
									pdsh.deposit_pos_date = ".ms($date)." and 
									pdsh.deposit_branch_id = ".mi($this->branch_id)." and 
									pdsh.deposit_counter_id = ".mi($counter_id).")");

			if($con->sql_numrows($q_pd) > 0){
				while($pos_deposit = $con->sql_fetchrow($q_pd)){
					if($pos_deposit['type'] == "RECEIVED"){
						$filter = "p.id = ".mi($pos_deposit[''])." and p.date = ".ms($date)." and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($counter_id);
					}else{
						$filter = "p.id = ".mi($pos_id)." and p.date = ".ms($date)." and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($counter_id);
					}
					
					$con->sql_query("select p.amount, p.cancel_status, p.prune_status
									 from pos p
									 left join pos_payment pp on pp.pos_id = p.id and pp.branch_id = p.branch_id and pp.counter_id = p.counter_id and pp.date = p.date
									 where $filter");
						
					if($pos_deposit['type'] == "RECEIVED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'])
						$this->data[$counter_id][$dom_id]['deposit_received_amt']+=$pos_deposit['amount'];
						$left_join = 
					elseif($pos_deposit['type'] == "USED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'])
						$this->data[$counter_id][$dom_id]['deposit_used_amt']+=$pos_deposit['amount'];
					elseif($pos_deposit['type'] == "CANCEL_RCV")
						$this->data[$counter_id][$dom_id]['deposit_cancel_rcv_amt']+=$pos_deposit['amount'];
					elseif($pos_deposit['type'] == "CANCEL_USED")
						$this->data[$counter_id][$dom_id]['deposit_cancel_used_amt']+=$pos_deposit['amount'];
				}
				$con->sql_freeresult($q_pd);
			}*/
						
			// check if having deposit amt
			
			//////// OLD DEPOSIT METHOD /////////
			/*$q_pd = $con->sql_query("select p.amount, pp.amount as payment_amount, pdsh.type, p.cancel_status, p.prune_status, p.amount_change,
									pp.type as payment_type
									from pos_deposit_status_history pdsh
									left join pos_payment pp on pp.pos_id = pdsh.pos_id and pp.branch_id = pdsh.branch_id and pp.counter_id = pdsh.counter_id and pp.date = pdsh.pos_date
									left join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
									where
									pdsh.pos_id = ".mi($pos_id)." and 
									pdsh.pos_date = ".ms($date)." and 
									pdsh.branch_id = ".mi($this->branch_id)." and 
									pdsh.counter_id = ".mi($counter_id));

			if($con->sql_numrows($q_pd) > 0){
				while($pos_deposit = $con->sql_fetchrow($q_pd)){
					
					if($pos_deposit['type'] == "RECEIVED" && !$pos_deposit['cancel_status'] && $pos_deposit['payment_type'] != "Deposit"){
						// got receive deposit
						$this->data[$counter_id][$dom_id]['deposit_received_amt']+=$pos_deposit['amount'];
					}elseif($pos_deposit['type'] == "USED" && !$pos_deposit['cancel_status'] && !$pos_deposit['prune_status'] && $pos_deposit['payment_type'] == "Deposit"){
						// got used deposit
						$this->data[$counter_id][$dom_id]['deposit_used_amt']+=$pos_deposit['payment_amount'];
						
						if($pos_deposit['amount'] < $pos_deposit['payment_amount']){	// the purchase amt less then deposit amt
							if($pos_deposit['amount_change']){
								$this->data[$counter_id][$dom_id]['deposit_used_amt'] -= $pos_deposit['amount_change'];
								$this->data[$counter_id][$dom_id]['deposit_refund_amt']+=$pos_deposit['amount_change'];
							}
							
						}
					}elseif($pos_deposit['type'] == "CANCEL_RCV" && $pos_deposit['payment_type'] == "Cash"){
						// got cancel deposit
						$this->data[$counter_id][$dom_id]['deposit_cancel_rcv_amt']+=$pos_deposit['amount'];
					}elseif($pos_deposit['type'] == "CANCEL_USED" && $pos_deposit['payment_type'] == "Cash"){
						// got cancel used deposit
						$this->data[$counter_id][$dom_id]['deposit_cancel_used_amt']+=$pos_deposit['amount'];
					}
						
				}
				$con->sql_freeresult($q_pd);
			}*/
			
			// last cashier
			$this->data[$counter_id][$split_type][$dom_id]['last_cashier_id'] = $pos['cashier_id'];
			if(!$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'])	$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'] = array();
			if(!in_array($pos['cashier_id'], $this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'])){
				$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'][] = $pos['cashier_id'];
			}
			//cashier list
			//if (!in_array($pos['cashier_id'],$cashier_list))	$cashier_list[] = $pos['cashier_id'];
			
			
		    // over
		    $pos_amt = $pos['amount'];
			$real_receipt_amt = $pos['amount_tender'] - $pos['amount_change'] - $pos['service_charges'];
			$over_amt = mf($real_receipt_amt-$pos_amt);
			if($over_amt){
                $this->data[$counter_id][$split_type][$dom_id]['cashier_sales']['Over']['amt'] += round($over_amt,2);
                //$this->total[$counter_id]['cashier_sales']['over']['amt'] += $over_amt;
			}
			
			// trade in
			$q_ti = $con->sql_query("select pi.*
			from pos_items pi
			where pi.branch_id=$this->branch_id and pi.date=".ms($date)." and pi.counter_id=$counter_id and pi.pos_id=$pos_id and pi.trade_in_by>0");
			while($pi_ti = $con->sql_fetchassoc($q_ti)){
				$this->data[$counter_id][$split_type][$dom_id]['trade_in']['qty'] += $pi_ti['qty'];
				$this->data[$counter_id][$split_type][$dom_id]['trade_in']['amt'] += $pi_ti['price'];
				
				if($pi_ti['writeoff_by']){
					$this->data[$counter_id][$split_type][$dom_id]['trade_in']['writeoff_qty']+=$pi_ti['qty'];
					$this->data[$counter_id][$split_type][$dom_id]['trade_in']['writeoff_amt']+=$pi_ti['price'];
				}
			}
			$con->sql_freeresult($q_ti);
			
			// service charges
			if($this->got_service_charge){
				$this->data[$counter_id][$split_type][$dom_id]['cashier_sales']['service_charges']['amt'] += $pos['service_charges']-$pos['service_charges_gst_amt'];
			}
			
			// gst
			if($this->got_gst){
				$this->data[$counter_id][$split_type][$dom_id]['cashier_sales']['total_gst_amt']['amt'] += $pos['total_gst_amt'];
			}
		}
		$con->sql_freeresult($q_pos);
		
		//////// NEW DEPOSIT METHOD /////////
		// receive
		//$sql = "select p.* from pos p where $filter and p.cancel_status=0 order by end_time";
		// receive
		$sql = "select pd.*, p.amount, p.amount_change, p.end_time
from pos_deposit pd
left join pos p on p.branch_id=pd.branch_id and p.date=pd.date and p.counter_id=pd.counter_id and p.id=pd.pos_id
where $filter and p.cancel_status=0";
 		$q1 = $con->sql_query($sql);
 		while($r = $con->sql_fetchassoc($q1)){
			if($config['counter_collection_split_counter_by_cashier']){
				$split_type = $r['cashier_id'];
			}else $split_type = "by_dom";
		
 			// get counter domination id
			$dom_id = $this->get_pos_cash_domination_id($counter_id, $r['end_time'], $split_type);
 			$this->data[$counter_id][$split_type][$dom_id]['deposit_received_amt'] += round($r['deposit_amount'], 2);	// rcv amt
 		}
 		$con->sql_freeresult($q1);
 		
 		// used
 		$sql = "select sum(pd.deposit_amount) as deposit_amount, p.amount, p.amount_change, p.cancel_status, p.end_time, p.cashier_id
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
where $filter and p.cancel_status=0 and pdsh.type='USED'
group by p.branch_id,p.date,p.counter_id,p.id";
		$q2 = $con->sql_query($sql);
 		while($r = $con->sql_fetchassoc($q2)){
			if($config['counter_collection_split_counter_by_cashier']){
				$split_type = $r['cashier_id'];
			}else $split_type = "by_dom";
		
 			$dom_id = $this->get_pos_cash_domination_id($counter_id, $r['end_time'], $split_type);
 			
 			$used_amt = $r['deposit_amount'];
 			$refund = 0;
 			if($r['amount'] < $r['deposit_amount'] && $r['amount_change'] > 0){
 				$refund = $r['amount_change'];
 				$used_amt -= $refund;
 			}
 			
 			if($used_amt)	$this->data[$counter_id][$split_type][$dom_id]['deposit_used_amt'] += round($used_amt, 2);	// used amt
 			if($refund)	$this->data[$counter_id][$split_type][$dom_id]['deposit_refund_amt'] += round($refund, 2);	// refund amt
 		}
 		$con->sql_freeresult($q2);
 		
 		// cancel previous
 		$sql = "select p.amount, p.amount_change, p.cancel_status, p.end_time, p.cashier_id
from pos_deposit pd
left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
left join pos p on p.branch_id=pdsh.branch_id and p.date=pdsh.pos_date and p.counter_id=pdsh.counter_id and p.id=pdsh.pos_id
where $filter and p.cancel_status=0 and pdsh.type='CANCEL_RCV'";
 		$q3 = $con->sql_query($sql);
 		while($r = $con->sql_fetchassoc($q3)){
			if($config['counter_collection_split_counter_by_cashier']){
				$split_type = $r['cashier_id'];
			}else $split_type = "by_dom";
		
 			// get counter domination id
			$dom_id = $this->get_pos_cash_domination_id($counter_id, $r['end_time'], $split_type);
			
 			$this->data[$counter_id][$split_type][$dom_id]['deposit_cancel_rcv_amt'] += round($r['amount'], 2);	// cancel amt
 		}
 		$con->sql_freeresult($q3);
 		
 		/////// END DEPOSIT /////////
 		
		// cash advance
	    $q_pch = $con->sql_query("select p.* from pos_cash_history p where $filter");
	    while($pch = $con->sql_fetchrow($q_pch)){
			if($config['counter_collection_split_counter_by_cashier']){
				if($pch['ref_no'] || $pch['type'] == "ADVANCE") $split_type = $pch['user_id'];
			}else $split_type = "by_dom";
	
	        $dom_id = $this->get_pos_cash_domination_id($counter_id, $pch['timestamp'], $split_type);
			
			if($config['counter_collection_split_counter_by_cashier']){
				if(!$this->data[$counter_id][$split_type][$dom_id]['last_cashier_id']) $this->data[$counter_id][$split_type][$dom_id]['last_cashier_id'] = $pch['user_id'];
			}

	        $currency_arr = pch_is_currency($pch['remark'], $pch['amount']);
	        $rm_amt = $currency_arr['rm_amt'];

			switch($pch['type']){
				case 'ADVANCE':
					$type = 'cash_advance';
					break;
				case 'TOP_UP':
					$type = 'top_up';
					$this->got_top_up = true;
					break;
				default:
					continue 2;		
			}
			
			if($currency_arr['is_currency']){
			    $currency_type = $currency_arr['currency_type'];
			    $currency_rate = $currency_arr['currency_rate'];
			    $currency_amt = $currency_arr['currency_amt'];

                $this->data[$counter_id][$split_type][$dom_id][$type]['foreign_currency'][$currency_type]['foreign_amt'] += $currency_amt;
                $this->data[$counter_id][$split_type][$dom_id][$type]['foreign_currency'][$currency_type]['rm_amt'] += $rm_amt;

                //$this->total[$counter_id]['cash_advance']['foreign_currency'][$currency_type]['foreign_amt'] += $currency_amt;
                //$this->total[$counter_id]['cash_advance']['foreign_currency'][$currency_type]['rm_amt'] += $rm_amt;

                // jz use it to construct currency array
				$type = strtoupper($pch['type']);
				/*$this->currency_data[$currency_type][$type][$currency_rate]['cash_advance']['foreign_amt'] += $currency_amt;
                $this->currency_data[$currency_type][$type][$currency_rate]['cash_advance']['rm_amt'] += $rm_amt;*/
			}else{
                $this->data[$counter_id][$split_type][$dom_id][$type]['Cash']['amt'] += $rm_amt;
                //$this->total[$counter_id]['cash_advance']['cash']['amt'] += $rm_amt;
				$this->data[$counter_id][$split_type][$dom_id][$type]['nett_sales']['npt_amt'] += $rm_amt;
			}
			$this->data[$counter_id][$split_type][$dom_id][$type]['nett_sales']['amt'] += $rm_amt;
			
			/*if(!$this->pos_cash_domination[$counter_id][$split_type]){
				$tmp = array();
				$tmp['start_time'] = $pch['timestamp'];
				$tmp['end_time'] = $pch['timestamp'];
				$tmp['cashier_name'] = $pch['cashier_name'];
				$tmp['split_type'] = $split_type;
				$this->counters[$counter_id]['network_name'] = $pch['cashier_name'];
			}
			
			$this->pos_cash_domination[$counter_id][$split_type][$dom_id] = $tmp;*/
				
			//$this->total['total']['cash_advance']['amt'] += $rm_amt;
		}

		$pos_cash_domination_notes = $config['cash_domination_notes'];
		// pos cash domination
		//print_r($this->data[$counter_id]['pos_cash_domination']);
        if($this->pos_cash_domination[$counter_id]){
			foreach($this->pos_cash_domination[$counter_id] as $split_type=>$dl){	
				foreach($dl as $dom_id=>$r){
					if($r['user_id']){
						// if no sales, put cash denom user as cashier
						if(!$this->data[$counter_id][$split_type][$dom_id]['last_cashier_id']){
							$this->data[$counter_id][$split_type][$dom_id]['last_cashier_id'] = $r['user_id'];
							//if (!in_array($r['user_id'],$cashier_list))	$cashier_list[] = $r['user_id'];
						}
						if(!$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'])	$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'] = array();
						if(!in_array($r['user_id'], $this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'])){
							$this->data[$counter_id][$split_type][$dom_id]['arr_cashier_id_list'][] = $r['user_id'];
						}
					}
					
					if(!$r['data']&&!$r['odata']) continue;

					// latest data
					if($r['data']){
						if($r['curr_rate']){
							$curr_rate = $r['curr_rate'];  // use cash domination currency rate
						}
						else{
							$curr_rate = $this->foreign_currency_list;  // use default currency rate
						}

						foreach($r['data'] as $type=>$d2){
							$type_key = $type;
							$is_foreign_currency = false;
							if ($type_key == 'Cheque') $type_key = 'Check';

							if (in_array($type, $pos_config['credit_card']))
								$type_key = 'Credit Cards';
							elseif (in_array($type, array_keys($pos_cash_domination_notes)))
							{
								$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
								$type_key = 'Cash';
							}
							elseif ($type == 'Float')
							{
								$type_key = 'Cash';
								$d2 *= -1;
							}elseif(strpos($type, '_Float')){   // currency float
								$type_key = str_replace('_Float', '', $type);
								$d2 *= -1;
							}
							
							//print_r($pos_config['payment_type']);
							if($type_key && $type_key != $mm_discount_col_value && $type_key != "Discount" && !in_array($type_key, $this->normal_payment_type) && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
								$this->normal_payment_type[] = $type_key;
							}else{
								// is currency
								if($curr_rate[$type_key]){
									$is_foreign_currency = true;
									$this->check_and_create_foreign_currency_list($type_key, $curr_rate[$type_key]);
								}
							}

							if($is_foreign_currency){  // is currency collection
								$rm_amt = $curr_rate[$type_key] ? round(mf($d2/$curr_rate[$type_key]), 2) : 0;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['foreign_currency'][$type_key]['foreign_amt'] += $d2;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['foreign_currency'][$type_key]['rm_amt'] += $rm_amt;

								// jz use it to construct currency array
								$type_key = strtoupper($type_key);
								/*$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['foreign_amt'] += $d2;
								$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['rm_amt'] += $rm_amt;*/

								if(strpos($type, '_Float')){    // is currency float
									$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['foreign_currency'][$type_key]['Float']['foreign_amt'] += abs($d2);
								}
							}else{
								$rm_amt = $d2;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination'][$type_key]['amt'] += $rm_amt;

								if($type=='Float'){ // is cash float
									$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['Float']['amt'] += abs($rm_amt);
								}
								
								// sum up for normal payment type only, does not include foreign currency amount
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['nett_sales']['npt_amt'] += $rm_amt;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['nett_sales']['amt'] += $rm_amt;
							}
						}
					}

					// original data
					if($r['odata']){
						if($r['ocurr_rate']){
							$curr_rate = $r['ocurr_rate'];  // use cash domination currency rate
						}
						else{
							$curr_rate = $this->foreign_currency_list;  // use default currency rate
						}

						foreach($r['odata'] as $type=>$d2){
							$type_key = $type;
							$is_foreign_currency = false;
							if ($type_key == 'Cheque') $type_key = 'Check';

							if (in_array($type, $pos_config['credit_card']))
								$type_key = 'Credit Cards';
							elseif (in_array($type, array_keys($pos_cash_domination_notes)))
							{
								$d2 = $d2 * $config['cash_domination_notes'][$type]['value'];
								$type_key = 'Cash';
							}
							elseif ($type == 'Float')
							{
								//$type_key = 'Cash';
								$d2 *= -1;
							}
							
							if($type_key && $type_key != $mm_discount_col_value && $type_key != "Discount" && !in_array($type_key, $this->normal_payment_type) && (in_array($type_key, $pos_config['payment_type']) || preg_match('/^ewallet_/i', $type_key))){
								$this->normal_payment_type[] = $type_key;
							}else{
								// is currency
								if($curr_rate[$type_key]){
									$this->got_foreign_currency = $is_foreign_currency = true;
									$this->check_and_create_foreign_currency_list($type_key, $curr_rate[$type_key]);
								}
							}

							if($is_foreign_currency){  // is currency collection
								if($d2){
									if($curr_rate[$type_key])	$rm_amt = round(mf($d2/$curr_rate[$type_key]), 2);
									else $rm_amt = 0;	// no convert rate, then RM = zero
		
									$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['foreign_currency'][$type_key]['o_foreign_amt'] += $d2;
									$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['foreign_currency'][$type_key]['o_rm_amt'] += $rm_amt;
		
									// jz use it to construct currency array
									/*$type_key = strtoupper($type_key);
									$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_foreign_amt'] += $d2;
									$this->currency_data[$type_key]['currency_rate'][$curr_rate[$type_key]]['cash_domination']['o_rm_amt'] += $rm_amt; */                   	
								}	
							}else{
								$rm_amt = $d2;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination'][$type_key]['o_amt'] += $rm_amt;
								
								// sum up for normal payment type only, does not include foreign currency amount
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['nett_sales']['npt_o_amt'] += $rm_amt;
								$this->data[$counter_id][$split_type][$dom_id]['cash_domination']['nett_sales']['o_amt'] += $rm_amt;
							}
						}
					}
				}
			}
		}
		
		// Sort Denomination by ID
		if($split_type == "by_dom"){
			if($this->data[$counter_id][$split_type]){
				uksort($this->data[$counter_id][$split_type], array($this, "sort_denom_data"));
			}			
		}
		
		/*if ($cashier_list) {
			foreach($cashier_list as $clkey=>$clvalue) {
				$cashier_list[$clkey] = $this->username[$clvalue];
			}
			$this->data[$counter_id][$dom_id]['cashier_list'] = join(',',$cashier_list);
		}
		else {
			$this->data[$counter_id][$dom_id]['cashier_list'] = '';
		}*/
	}

	private function calculate_total_data(){
	    global $pos_config, $mm_discount_col_value, $config;


		if(!$this->data)    return;
		foreach($this->data as $counter_id=>&$st){
			foreach($st as $split_type=>&$c){
				foreach($c as $dom_id=>&$r){
					foreach($this->normal_payment_type as $payment_type){
						// cashier sales => nett sales
						$r['cashier_sales']['nett_sales']['amt'] += $r['cashier_sales'][$payment_type]['amt'];
						$r['cashier_sales']['nett_sales']['npt_amt'] += $r['cashier_sales'][$payment_type]['amt'];
												
						// total by counter by payment type
						$this->total['total_by_counter'][$counter_id]['cashier_sales'][$payment_type]['amt'] += $r['cashier_sales'][$payment_type]['amt'];
						$this->total['total_by_counter'][$counter_id]['cashier_sales'][$payment_type]['npt_amt'] += $r['cashier_sales'][$payment_type]['amt'];
						// total by counter
						$this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] += $r['cashier_sales'][$payment_type]['amt'];
						$this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['npt_amt'] += $r['cashier_sales'][$payment_type]['amt'];
					}
					//$r['cashier_sales']['nett_sales']['amt'] = $r['cashier_sales']['Cash']['amt']+$r['cashier_sales']['Credit Cards']['amt']+$r['cashier_sales']['Coupon']['amt']+$r['cashier_sales']['Voucher']['amt']+$r['cashier_sales']['Check']['amt'];
					$r['cashier_sales']['nett_sales2']['amt'] = $r['cashier_sales']['nett_sales']['amt'] - $r['cashier_sales']['service_charges']['amt'] - $r['cashier_sales']['total_gst_amt']['amt'] - $r['cashier_sales']['Rounding']['amt'];
					
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['Cash']['amt'] += $r['cashier_sales']['Cash']['amt'];
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['Credit Cards']['amt'] += $r['cashier_sales']['Credit Cards']['amt'];
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['Coupon']['amt'] += $r['cashier_sales']['Coupon']['amt'];
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['Voucher']['amt'] += $r['cashier_sales']['Voucher']['amt'];
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['Check']['amt'] += $r['cashier_sales']['Check']['amt'];
					//$this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] = $this->total['total_by_counter'][$counter_id]['cashier_sales']['Cash']['amt'] + $this->total['total_by_counter'][$counter_id]['cashier_sales']['Credit Cards']['amt'] + $this->total['total_by_counter'][$counter_id]['cashier_sales']['Coupon']['amt'] + $this->total['total_by_counter'][$counter_id]['cashier_sales']['Voucher']['amt'] + $this->total['total_by_counter'][$counter_id]['cashier_sales']['Check']['amt'];
					 
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$r['cashier_sales']['nett_sales']['amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'];
							
							// total by counter
							$this->total['total_by_counter'][$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'];
							$this->total['total_by_counter'][$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							
							$this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'];
						}
						$r['cashier_sales']['nett_sales2']['amt'] = $r['cashier_sales']['nett_sales']['amt'] - $r['cashier_sales']['service_charges']['amt'] - $r['cashier_sales']['total_gst_amt']['amt'] - $r['cashier_sales']['Rounding']['amt'] + $r['cashier_sales']['Currency_adjust']['amt'];
					}
					
					// cashier sales => gross sales
					$r['cashier_sales']['gross_sales']['amt'] = $r['cashier_sales']['nett_sales']['amt']-$r['cashier_sales']['Discount']['amt']-$r['cashier_sales']['Rounding']['amt']-$r['cashier_sales']['Over']['amt']-$r['cashier_sales'][$mm_discount_col_value]['amt']+$r['cashier_sales']['Currency_adjust']['amt'];
					$r['cashier_sales']['gross_sales']['amt'] -= $r['cashier_sales']['service_charges']['amt'];
					$r['cashier_sales']['gross_sales']['amt'] -= $r['cashier_sales']['total_gst_amt']['amt'];
					
					// total by counter
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['Discount']['amt'] += $r['cashier_sales']['Discount']['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['Rounding']['amt'] += $r['cashier_sales']['Rounding']['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['Over']['amt'] += $r['cashier_sales']['Over']['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales'][$mm_discount_col_value]['amt'] += $r['cashier_sales'][$mm_discount_col_value]['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['Currency_adjust']['amt'] += $r['cashier_sales']['Currency_adjust']['amt'];
					if($this->got_service_charge){
						$this->total['total_by_counter'][$counter_id]['cashier_sales']['service_charges']['amt'] += $r['cashier_sales']['service_charges']['amt'];
					}
					if($this->got_gst){
						$this->total['total_by_counter'][$counter_id]['cashier_sales']['total_gst_amt']['amt'] += $r['cashier_sales']['total_gst_amt']['amt'];
					}
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['gross_sales']['amt'] = $this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['Discount']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['Rounding']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['Over']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales'][$mm_discount_col_value]['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['Currency_adjust']['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['gross_sales']['amt'] -= $this->total['total_by_counter'][$counter_id]['cashier_sales']['service_charges']['amt'];
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['gross_sales']['amt'] -= $this->total['total_by_counter'][$counter_id]['cashier_sales']['total_gst_amt']['amt'];
					
					$this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales2']['amt'] = $this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['service_charges']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['total_gst_amt']['amt'] - $this->total['total_by_counter'][$counter_id]['cashier_sales']['Rounding']['amt'] + $this->total['total_by_counter'][$counter_id]['cashier_sales']['Currency_adjust']['amt'];
					
					//====>> Juz store data
					if ($this->finalize){
						$this->store_data[$counter_id]['cashier_sales']['Discount']['amt']+=$r['cashier_sales']['Discount']['amt'];
						$this->store_data[$counter_id]['cashier_sales']['Rounding']['amt']+=$r['cashier_sales']['Rounding']['amt'];
						$this->store_data[$counter_id]['cashier_sales']['Over']['amt']+=$r['cashier_sales']['Over']['amt'];
						$this->store_data[$counter_id]['cashier_sales']['Currency_adjust']['amt']+=$r['cashier_sales']['Currency_adjust']['amt'];
						
						$this->store_data[$counter_id]['cashier_sales'][$mm_discount_col_value]['amt']+=$r['cashier_sales'][$mm_discount_col_value]['amt'];
						
						if($this->got_service_charge){
							$this->store_data[$counter_id]['cashier_sales']['service_charges']['amt']+=$r['cashier_sales']['service_charges']['amt'];
						}
						if($this->got_gst){
							$this->store_data[$counter_id]['cashier_sales']['total_gst_amt']['amt']+=$r['cashier_sales']['total_gst_amt']['amt'];
						}
						
						$this->store_data[$counter_id]['cashier_sales']['Over']['amt']+=$r['cashier_sales']['Over']['amt'];
						
						$this->store_data[$counter_id]['cashier_sales']['gross_sales']['amt']+=$r['cashier_sales']['gross_sales']['amt'];
						
						$this->store_data[$counter_id]['cashier_sales']['nett_sales2']['amt']+=$r['cashier_sales']['nett_sales2']['amt'];
					}

					// adjustment => nett sales
					foreach($this->normal_payment_type as $payment_type){
						$r['adj']['nett_sales']['amt'] += $r['adj'][$payment_type]['amt'];
						$r['adj']['nett_sales']['npt_amt'] += $r['adj'][$payment_type]['amt'];
						
						// total by counter by payment type
						$this->total['total_by_counter'][$counter_id]['adj'][$payment_type]['amt'] += $r['adj'][$payment_type]['amt'];
						$this->total['total_by_counter'][$counter_id]['adj'][$payment_type]['npt_amt'] += $r['adj'][$payment_type]['amt'];
						
						// total by counter
						$this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['amt'] += $r['adj'][$payment_type]['amt'];
						$this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['npt_amt'] += $r['adj'][$payment_type]['amt'];
					}
					//$r['adj']['nett_sales']['amt'] = $r['adj']['Cash']['amt']+$r['adj']['Credit Cards']['amt']+$r['adj']['Coupon']['amt']+$r['adj']['Voucher']['amt']+$r['adj']['Check']['amt'];

					//$this->total['total_by_counter'][$counter_id]['adj']['Cash']['amt'] += $r['adj']['Cash']['amt'];
					//$this->total['total_by_counter'][$counter_id]['adj']['Credit Cards']['amt'] += $r['adj']['Credit Cards']['amt'];
					//$this->total['total_by_counter'][$counter_id]['adj']['Coupon']['amt'] += $r['adj']['Coupon']['amt'];
					//$this->total['total_by_counter'][$counter_id]['adj']['Voucher']['amt'] += $r['adj']['Voucher']['amt'];
					//$this->total['total_by_counter'][$counter_id]['adj']['Check']['amt'] += $r['adj']['Check']['amt'];
					
					//$this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['amt'] = $this->total['total_by_counter'][$counter_id]['adj']['Cash']['amt'] + $this->total['total_by_counter'][$counter_id]['adj']['Credit Cards']['amt'] + $this->total['total_by_counter'][$counter_id]['adj']['Coupon']['amt'] + $this->total['total_by_counter'][$counter_id]['adj']['Voucher']['amt'] + $this->total['total_by_counter'][$counter_id]['adj']['Check']['amt'];
					
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$r['adj']['nett_sales']['amt'] += $r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
							
							// total by counter
							$this->total['total_by_counter'][$counter_id]['adj']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
							$this->total['total_by_counter'][$counter_id]['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							
							$this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['amt'] += $r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
						}
					}
			
					// variance
					//foreach($pos_config['normal_payment_type'] as $payment_type){
					foreach($this->normal_payment_type as $payment_type){
						$r['variance'][$payment_type]['amt'] = round($r['cash_domination'][$payment_type]['amt'] - ($r['cashier_sales'][$payment_type]['amt']+$r['cash_advance'][$payment_type]['amt']+$r['adj'][$payment_type]['amt']+$r['top_up'][$payment_type]['amt']),2);
						
						// total by counter
						$this->total['total_by_counter'][$counter_id]['cash_domination'][$payment_type]['amt'] += $r['cash_domination'][$payment_type]['amt'];
						$this->total['total_by_counter'][$counter_id]['cash_domination'][$payment_type]['o_amt'] += $r['cash_domination'][$payment_type]['o_amt'];
						$this->total['total_by_counter'][$counter_id]['cash_advance'][$payment_type]['amt'] += $r['cash_advance'][$payment_type]['amt'];
						if(isset($r['top_up'])){
							$this->total['total_by_counter'][$counter_id]['top_up'][$payment_type]['amt'] += $r['top_up'][$payment_type]['amt'];
						}
						
						$this->total['total_by_counter'][$counter_id]['variance'][$payment_type]['amt'] = round($this->total['total_by_counter'][$counter_id]['cash_domination'][$payment_type]['amt'] - ($this->total['total_by_counter'][$counter_id]['cashier_sales'][$payment_type]['amt'] + $this->total['total_by_counter'][$counter_id]['cash_advance'][$payment_type]['amt'] + $this->total['total_by_counter'][$counter_id]['adj'][$payment_type]['amt'] + $this->total['total_by_counter'][$counter_id]['top_up'][$payment_type]['amt']),2);
						
						if ($this->finalize){
							$this->store_data[$counter_id]['cash_domination'][$payment_type]['amt']+=$r['cash_domination'][$payment_type]['amt'];
							$this->store_data[$counter_id]['cashier_sales'][$payment_type]['amt']+=$r['cashier_sales'][$payment_type]['amt'];
							$this->store_data[$counter_id]['top_up'][$payment_type]['amt']+=$r['top_up'][$payment_type]['amt'];
							$this->store_data[$counter_id]['cash_advance'][$payment_type]['amt']+=$r['cash_advance'][$payment_type]['amt'];
							$this->store_data[$counter_id]['adjustment'][$payment_type]['amt']+=$r['adj'][$payment_type]['amt'];
		  
							//====>> Juz store data                  
							$this->store_data[$counter_id]['variance'][$payment_type]['amt']+=$r['variance'][$payment_type]['amt'];
						}
						
						$this->total['cash_domination'][$payment_type]['amt'] += $r['cash_domination'][$payment_type]['amt'];
					}
					
					// foreign currency variance
					$ttl_fc_amt = 0;
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] = round($r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'] - ($r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt']),2);
							
							if($r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt']){
								$r['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] = round($r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] / $this->foreign_currency_list[$curr_payment_type], 2);
							
								// sum up total by foreign currency for variance comparison purpose
								$ttl_fc_amt += (round($r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] / $this->foreign_currency_list[$curr_payment_type], 2));
							}

							// total by counter
							$this->total['total_by_counter'][$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							$this->total['total_by_counter'][$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							if(isset($r['top_up'])){
								$this->total['total_by_counter'][$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							}
							
							// check if have currency Float
							if($r['cash_domination']['foreign_currency'][$curr_payment_type]['Float']['foreign_amt']){
								$this->total['total_by_counter'][$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['Float']['foreign_amt'] += abs($r['cash_domination']['foreign_currency'][$curr_payment_type]['Float']['foreign_amt']);
							}
							
							$this->total['total_by_counter'][$counter_id]['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] = round($this->total['total_by_counter'][$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'] - ($this->total['total_by_counter'][$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt'] + $this->total['total_by_counter'][$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt'] + $this->total['total_by_counter'][$counter_id]['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'] + $this->total['total_by_counter'][$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt']),2);
						
							$this->total['total_by_counter'][$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'];
							$this->total['total_by_counter'][$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt'];
							if(isset($r['top_up'])){
								$this->total['total_by_counter'][$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['top_up']['foreign_currency'][$curr_payment_type]['rm_amt'];
							}
													
							//$this->total['total_by_counter'][$counter_id]['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] = round($this->total['total_by_counter'][$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'] - ($this->total['total_by_counter'][$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'] + $this->total['total_by_counter'][$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt'] + $this->total['total_by_counter'][$counter_id]['adj']['foreign_currency'][$curr_payment_type]['rm_amt'] + $this->total['total_by_counter'][$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['rm_amt']),2);
							
							if($this->total['total_by_counter'][$counter_id]['variance']['foreign_currency'][$curr_payment_type]['foreign_amt']) $this->total['total_by_counter'][$counter_id]['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] = round($this->total['total_by_counter'][$counter_id]['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] / $this->foreign_currency_list[$curr_payment_type], 2);
							
							//====>> Juz store data
							if ($this->finalize){
								$this->store_data[$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['top_up']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['cash_advance']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['adjustment']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'];
								
								$this->store_data[$counter_id]['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt'];
								$this->store_data[$counter_id]['top_up']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['top_up']['foreign_currency'][$curr_payment_type]['rm_amt'];
								$this->store_data[$counter_id]['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['cash_advance']['foreign_currency'][$curr_payment_type]['rm_amt'];
								$this->store_data[$counter_id]['adjustment']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];						
								
								$this->store_data[$counter_id]['variance']['foreign_currency'][$curr_payment_type]['foreign_amt']+=$r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'];
								$this->store_data[$counter_id]['variance']['foreign_currency'][$curr_payment_type]['rm_amt']+=$r['variance']['foreign_currency'][$curr_payment_type]['rm_amt'];
							}
							
							$this->total['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							$this->total['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cash_domination']['foreign_currency'][$curr_payment_type]['rm_amt'];
						}

						$r['variance']['nett_sales']['npt_amt'] = round($r['cash_domination']['nett_sales']['npt_amt']-($r['cashier_sales']['nett_sales']['npt_amt']+$r['cash_advance']['nett_sales']['npt_amt']+$r['adj']['nett_sales']['npt_amt']+$r['top_up']['nett_sales']['npt_amt']), 2);
						$r['variance']['nett_sales']['amt'] = round($r['variance']['nett_sales']['npt_amt']+$ttl_fc_amt, 2);
					}else{
						$r['variance']['nett_sales']['amt'] = round($r['cash_domination']['nett_sales']['amt']-($r['cashier_sales']['nett_sales']['amt']+$r['cash_advance']['nett_sales']['amt']+$r['adj']['nett_sales']['amt']+$r['top_up']['nett_sales']['amt']),2);
					}

					// total by counter
					$this->total['total_by_counter'][$counter_id]['cash_domination']['nett_sales']['amt'] += $r['cash_domination']['nett_sales']['amt'];
					$this->total['total_by_counter'][$counter_id]['cash_domination']['nett_sales']['npt_amt'] += $r['cash_domination']['nett_sales']['npt_amt'];
					$this->total['total_by_counter'][$counter_id]['cash_advance']['nett_sales']['amt'] += $r['cash_advance']['nett_sales']['amt'];
					$this->total['total_by_counter'][$counter_id]['cash_advance']['nett_sales']['npt_amt'] += $r['cash_advance']['nett_sales']['npt_amt'];
					if(isset($r['top_up'])){
						$this->total['total_by_counter'][$counter_id]['top_up']['nett_sales']['amt'] += $r['top_up']['nett_sales']['amt'];				
						$this->total['total_by_counter'][$counter_id]['top_up']['nett_sales']['npt_amt'] += $r['top_up']['nett_sales']['npt_amt'];				
					}
					
					if($config['foreign_currency']){
						$this->total['total_by_counter'][$counter_id]['variance']['nett_sales']['npt_amt'] = round($this->total['total_by_counter'][$counter_id]['cash_domination']['nett_sales']['npt_amt'] - ($this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['npt_amt'] + $this->total['total_by_counter'][$counter_id]['cash_advance']['nett_sales']['npt_amt'] + $this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['npt_amt'] + $this->total['total_by_counter'][$counter_id]['top_up']['nett_sales']['npt_amt']),2);
						$this->total['total_by_counter'][$counter_id]['variance']['nett_sales']['amt'] = round($this->total['total_by_counter'][$counter_id]['variance']['nett_sales']['npt_amt']+$ttl_fc_amt, 2);
					}else{
						$this->total['total_by_counter'][$counter_id]['variance']['nett_sales']['amt'] = round($this->total['total_by_counter'][$counter_id]['cash_domination']['nett_sales']['amt'] - ($this->total['total_by_counter'][$counter_id]['cashier_sales']['nett_sales']['amt'] + $this->total['total_by_counter'][$counter_id]['cash_advance']['nett_sales']['amt'] + $this->total['total_by_counter'][$counter_id]['adj']['nett_sales']['amt'] + $this->total['total_by_counter'][$counter_id]['top_up']['nett_sales']['amt']),2);
					}


					if ($this->finalize){
						$this->store_data[$counter_id]['cash_domination']['nett_sales']['amt']+=$r['cash_domination']['nett_sales']['amt'];
						$this->store_data[$counter_id]['cashier_sales']['nett_sales']['amt']+=$r['cashier_sales']['nett_sales']['amt'];
						$this->store_data[$counter_id]['top_up']['nett_sales']['amt']+=$r['top_up']['nett_sales']['amt'];
						$this->store_data[$counter_id]['cash_advance']['nett_sales']['amt']+=$r['cash_advance']['nett_sales']['amt'];
						$this->store_data[$counter_id]['adjustment']['nett_sales']['amt']+=$r['adj']['nett_sales']['amt'];
						$this->store_data[$counter_id]['variance']['nett_sales']['amt']+=$r['variance']['nett_sales']['amt'];
					}
					
					// total by payment type
					//foreach($pos_config['normal_payment_type'] as $payment_type){
					foreach($this->normal_payment_type as $payment_type){
						$this->total['payment_type'][$payment_type]['amt'] += $r['cashier_sales'][$payment_type]['amt']+$r['adj'][$payment_type]['amt'];
					}
					// total by payment type - foreign currency
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$this->total['payment_type']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['foreign_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							$this->total['payment_type']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['cashier_sales']['foreign_currency'][$curr_payment_type]['rm_amt']+$r['adj']['foreign_currency'][$curr_payment_type]['rm_amt'];
						}
					}

					$this->total['payment_type']['nett_sales']['amt'] += $r['cashier_sales']['nett_sales']['amt']+$r['adj']['nett_sales']['amt'];
					$this->total['payment_type']['Discount']['amt'] += $r['cashier_sales']['Discount']['amt']+$r['adj']['Discount']['amt'];
					// mix and match
					$this->total['payment_type'][$mm_discount_col_value]['amt'] += $r['cashier_sales'][$mm_discount_col_value]['amt']+$r['adj'][$mm_discount_col_value]['amt'];
					
					$this->total['payment_type']['Rounding']['amt'] += $r['cashier_sales']['Rounding']['amt'];
					$this->total['payment_type']['Over']['amt'] += $r['cashier_sales']['Over']['amt'];
					$this->total['payment_type']['gross_sales']['amt'] += $r['cashier_sales']['gross_sales']['amt'];
					$this->total['payment_type']['Currency_adjust']['amt'] += $r['cashier_sales']['Currency_adjust']['amt'];
					
					if($this->got_service_charge){
						$this->total['payment_type']['service_charges']['amt'] += $r['cashier_sales']['service_charges']['amt'];
					}
					if($this->got_gst){
						$this->total['payment_type']['total_gst_amt']['amt'] += $r['cashier_sales']['total_gst_amt']['amt'];
					}

					// total by variance
					//foreach($pos_config['normal_payment_type'] as $payment_type){
					foreach($this->normal_payment_type as $payment_type){
						$this->total['variance'][$payment_type]['amt'] += $r['variance'][$payment_type]['amt'];
					}
					// total by variance - foreign currency
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$this->total['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'] += $r['variance']['foreign_currency'][$curr_payment_type]['foreign_amt'];
							$this->total['variance']['foreign_currency'][$curr_payment_type]['rm_amt'] += $r['variance']['foreign_currency'][$curr_payment_type]['rm_amt'];
						}
					}
					
					// deposit
					if($r['deposit_received_amt'] || $r['deposit_used_amt'] || $r['deposit_cancel_rcv_amt'] || $r['deposit_cancel_used_amt']){
						$this->total['total']['deposit']['rcv'] += $r['deposit_received_amt'];
						$this->total['total']['deposit']['used'] += $r['deposit_used_amt'];
						$this->total['total']['deposit']['refund'] += $r['deposit_refund_amt'];
						$this->total['total']['deposit']['cancel_rcv'] += $r['deposit_cancel_rcv_amt'];
						$this->total['total']['deposit']['cancel_used'] += $r['deposit_cancel_used_amt'];
						
						// total by counter
						$this->total['total_by_counter'][$counter_id]['deposit']['rcv'] += $r['deposit_received_amt'];
						$this->total['total_by_counter'][$counter_id]['deposit']['used'] += $r['deposit_used_amt'];
						$this->total['total_by_counter'][$counter_id]['deposit']['refund'] += $r['deposit_refund_amt'];
						$this->total['total_by_counter'][$counter_id]['deposit']['cancel_rcv'] += $r['deposit_cancel_rcv_amt'];
						$this->total['total_by_counter'][$counter_id]['deposit']['cancel_used'] += $r['deposit_cancel_used_amt'];
						
						if ($this->finalize){			
							$this->store_data[$counter_id]['deposit']['rcv'] += $r['deposit_received_amt'];
							$this->store_data[$counter_id]['deposit']['used'] += $r['deposit_used_amt'];
							$this->store_data[$counter_id]['deposit']['refund'] += $r['deposit_refund_amt'];
							$this->store_data[$counter_id]['deposit']['cancel_rcv'] += $r['deposit_cancel_rcv_amt'];
							$this->store_data[$counter_id]['deposit']['cancel_used'] += $r['deposit_cancel_used_amt'];
						}
					}
					
					if($r['trade_in']['qty'] || $r['trade_in']['amt']){
						// trade in
						$this->total['total']['trade_in']['qty'] += $r['trade_in']['qty'];
						$this->total['total']['trade_in']['amt'] += $r['trade_in']['amt'];
						$this->total['total']['trade_in']['writeoff_qty'] += $r['trade_in']['writeoff_qty'];
						$this->total['total']['trade_in']['writeoff_amt'] += $r['trade_in']['writeoff_amt'];
							
						// total by counter
						$this->total['total_by_counter'][$counter_id]['trade_in']['qty'] += $r['trade_in']['qty'];
						$this->total['total_by_counter'][$counter_id]['trade_in']['amt'] += $r['trade_in']['amt'];
						$this->total['total_by_counter'][$counter_id]['trade_in']['writeoff_qty'] += $r['trade_in']['writeoff_qty'];
						$this->total['total_by_counter'][$counter_id]['writeoff_amt'] += $r['trade_in']['writeoff_amt'];
						
						if($this->finalize){
							$this->store_data[$counter_id]['trade_in']['qty'] += $r['trade_in']['qty'];
							$this->store_data[$counter_id]['trade_in']['amt'] += $r['trade_in']['amt'];
							$this->store_data[$counter_id]['trade_in']['writeoff_qty'] += $r['trade_in']['writeoff_qty'];
							$this->store_data[$counter_id]['trade_in']['writeoff_amt'] += $r['trade_in']['writeoff_amt'];
						}
					}
					
					if($r['cash_change']){
						// cash refund / change
						$this->total['total']['cash_change']['amt'] += $r['cash_change']['amt'];
						
						// total by counter
						$this->total['total_by_counter'][$counter_id]['cash_change']['amt'] += $r['cash_change']['amt'];
						
						if($this->finalize){
							$this->store_data[$counter_id]['cash_change']['amt'] += $r['cash_change']['amt'];
						}
					}
					
					// grand total nett sales
					$this->total['total']['nett_sales']['amt'] += ($r['cashier_sales']['nett_sales']['amt']+$r['adj']['nett_sales']['amt']);
					// grand total top up
					$this->total['total']['top_up']['amt'] += $r['top_up']['nett_sales']['amt'];
					$this->total['total']['top_up']['npt_amt'] += $r['top_up']['nett_sales']['npt_amt'];
					// grand total advance
					$this->total['total']['cash_advance']['amt'] += $r['cash_advance']['nett_sales']['amt'];
					// grand total collection
					$this->total['total']['cash_domination']['amt'] += $r['cash_domination']['nett_sales']['amt'];
					
					// grand total collection by foreign currency
					if($config['foreign_currency']){
						foreach($config['foreign_currency'] as $currency_type=>$currency_info){
							$curr_payment_type = $currency_type;
							$this->total['total']['cash_domination'][$curr_payment_type]['foreign_amt'] += $r['cash_domination']['foreign_currency'][$curr_payment_type]['foreign_amt'];
						}
					}
					
					// grand total gross sales
					$this->total['total']['gross_sales']['amt'] += $r['cashier_sales']['gross_sales']['amt'];
					// grand total for service charges
					if($this->got_service_charge){
						$this->total['total']['service_charges']['amt'] += $r['cashier_sales']['service_charges']['amt'];
					}
					// grand total for gst
					if($this->got_gst){
						$this->total['total']['total_gst_amt']['amt'] += $r['cashier_sales']['total_gst_amt']['amt'];
					}
					$this->total['total']['nett_sales2']['amt'] += $r['cashier_sales']['nett_sales2']['amt'];
					
					if($config['foreign_currency']){
						$this->total['total']['variance']['amt'] += $r['variance']['nett_sales']['amt'];
					}else{
						$this->total['total']['variance']['amt'] = round($this->total['total']['cash_domination']['amt'] - $this->total['total']['cash_advance']['amt'] - $this->total['total']['nett_sales']['amt'] - $this->total['total']['top_up']['amt'], 2);
					}
				}
			}
		}

		/*if($this->currency_data){
		    // construct different currency rate array
			foreach($this->currency_data as $currency_type=>$curr_list){
				if($curr_list['currency_rate']){
					foreach($curr_list['currency_rate'] as $curr_rate=>$curr_item){
						$curr_item['rate'] = mf($curr_rate);
						$this->currency_data[$currency_type]['list'][] = $curr_item;
					}
				}
				$this->currency_data[$currency_type]['currency_rate_count'] = count($curr_list['currency_rate']);
			}


			// loop again to sum by different rate
			foreach($this->currency_data as $currency_type=>$curr_list){
				if($curr_list['list']){
					foreach($curr_list['list'] as $k=>$curr_item){
						// nett sales
						$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt'] += $curr_item['cashier_sales']['foreign_amt'] + $curr_item['adj']['foreign_amt'];
						$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt'] += $curr_item['cashier_sales']['rm_amt']+$curr_item['adj']['rm_amt'];
						
						// top up
						$this->currency_data[$currency_type]['list'][$k]['total']['top_up']['foreign_amt'] += $curr_item['top_up']['foreign_amt'];
						$this->currency_data[$currency_type]['list'][$k]['total']['top_up']['rm_amt'] += $curr_item['top_up']['rm_amt'];

						// cash advance
						$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'] += $curr_item['cash_advance']['foreign_amt'];
						$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'] += $curr_item['cash_advance']['rm_amt'];
						// pos domination
						$this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt'] += $curr_item['cash_domination']['foreign_amt'];
						$this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt'] += $curr_item['cash_domination']['rm_amt'];
						// variance
						$this->currency_data[$currency_type]['list'][$k]['total']['variance']['foreign_amt'] = $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'];
						//$this->currency_data[$currency_type]['list'][$k]['total']['variance']['rm_amt'] = $this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt']-$this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'];
						$this->currency_data[$currency_type]['list'][$k]['total']['variance']['rm_amt'] = round($this->currency_data[$currency_type]['list'][$k]['total']['variance']['foreign_amt'] / $curr_item['rate'], 2);

						// total foreign variance
						$this->currency_data['total']['variance']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['variance']['foreign_amt'],2);
						$this->currency_data['total']['variance']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['variance']['rm_amt'],2);

						// total foreign nett sales
						$this->currency_data['total']['nett_sales']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['foreign_amt'],2);
						$this->currency_data['total']['nett_sales']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['nett_sales']['rm_amt'],2);

						// total foreign adj
						$this->currency_data['total']['adj']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['adj']['foreign_amt'],2);
						$this->currency_data['total']['adj']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['adj']['rm_amt'],2);
						
						// total foreign top up
						$this->currency_data['total']['top_up']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['top_up']['foreign_amt'],2);
						$this->currency_data['total']['top_up']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['top_up']['rm_amt'],2);
						
						// total foreign cash_advance
						 $this->currency_data['total']['cash_advance']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['foreign_amt'],2);
						$this->currency_data['total']['cash_advance']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_advance']['rm_amt'],2);

						// total foreign cash_domination
						 $this->currency_data['total']['cash_domination']['foreign_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['foreign_amt'],2);
						$this->currency_data['total']['cash_domination']['rm_amt'] += round($this->currency_data[$currency_type]['list'][$k]['total']['cash_domination']['rm_amt'],2);
					}
				}
			}
		}*/
	}

	function finalize(){
        global $con, $sessioninfo, $smarty, $LANG, $config, $appCore;

		/*if($config['monthly_closing']){
			$is_month_closed = $appCore->is_month_closed($_REQUEST['date_select']);
			if($is_month_closed)  js_redirect($LANG['MONTH_DOCUMENT_IS_CLOSED']);
		}*/

		// check approval
		if(!$this->is_approval){
			if($this->is_ajax_finalize){
				die(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE));
			}else{
				js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");	
			}
		} 
		
		if ($config['counter_collection_check_cash_dominator_before_finalize']) {
			$dct = $con->sql_query("select distinct counter_id from pos where branch_id=".mi($this->branch_id)." and date=".ms($_REQUEST['date_select'])." and cancel_status=0");
			while($ct= $con->sql_fetchassoc($dct)) {
				$ctid = $con->sql_query("select counter_id from pos_cash_domination where counter_id=".mi($ct['counter_id'])." and branch_id=".mi($this->branch_id)." and date=".ms($_REQUEST['date_select'])." limit 1");
				if (!$con->sql_fetchassoc($ctid)) {
					$con->sql_query("select network_name from counter_settings where id=".mi($ct['counter_id'])." and branch_id=".mi($this->branch_id)." limit 1");
					$nn = $con->sql_fetchassoc();
					
					if($this->is_ajax_finalize){
						die(sprintf($LANG["COUNTER_NEED_DOMINATION_TO_FINALIZE"],$nn['network_name']));
					}else{
						js_redirect(sprintf($LANG["COUNTER_NEED_DOMINATION_TO_FINALIZE"],$nn['network_name']));
					}
					
					exit;
				}
			}
		}
		
		$prms = array();
		$prms['branch_id'] = $this->branch_id;
		$prms['date'] = $_REQUEST['date_select'];
		$prms['is_finalise'] = true;
		$got_sync_error = check_sales_sync_status($prms);
		
		// if found got missing/unsync record while doing finalise, prompt error msg
		if($got_sync_error > 0){
			if($this->is_ajax_finalize){
				print "Data Sync Error";
				exit;
			}else{
				js_redirect($LANG["COUNTER_DATA_SYNC_ERROR"]);
			}
		}

		$form = $_REQUEST;

    	//get counter list those got pos
   		$con->sql_query("select counter_id from pos where branch_id=$this->branch_id and cancel_status=0 and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}
		$con->sql_freeresult();

		// get counter list those got cash advance
   		$con->sql_query("select counter_id from pos_cash_history where branch_id=$this->branch_id and date=".ms($form['date_select'])." group by counter_id");
		while($r1= $con->sql_fetchrow()){
		    $counter[$r1['counter_id']] = 1;
		}
		$con->sql_freeresult();

		// check all counters got pos domination or not
		$no_clear_drawer=0;
		foreach ($counter as $cid => $dummy)
		{
			$rs = $con->sql_query("select * from pos_cash_domination where branch_id=$this->branch_id and date=".ms($form['date_select'])." and counter_id=".mi($cid)." and clear_drawer=1");
			if ($con->sql_numrows($rs)==0)
			{
    			$con->sql_query("select network_name from counter_settings where id=$cid and branch_id=$this->branch_id");
    			$r = $con->sql_fetchrow();
    			$arr[]=$r['network_name'];
				//$no_clear_drawer++;
			}
		}

		if($no_clear_drawer>0){ // got counter no pos domination
			if($this->is_ajax_finalize){
				die("Counter: ".implode(" , ", $arr)." ".$LANG['COUNTER_COLLECTION_NO_CASH_DOMINATION']);
			}else{
				header("Location: /counter_collection.php?date_select=".$form['date_select']."&msg=Counter: ".implode(" , ", $arr)." ".urlencode($LANG['COUNTER_COLLECTION_NO_CASH_DOMINATION']));
			}
			exit;
		}

		if(is_cc_finalized($this->branch_id, $form['date_select'])){
			die("Counter Collection already finalised, cannot finalise again.");
		}
		
		// prevent client abort
		ignore_user_abort(true);
		set_time_limit(0);

		// Begin Transaction
		$con->sql_begin_transaction();
		
		// update as finalized
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id=$this->branch_id and date=".ms($form['date_select']));
		$rs = $con->sql_query("select branch_id, date, counter_id from pos where branch_id=$this->branch_id and date = ".ms($form['date_select'])." group by counter_id");
		while ($r = $con->sql_fetchrow($rs)){
			$r['finalized'] = 1;
			$con->sql_query("insert into pos_counter_collection_tracking ".mysql_insert_by_field($r,array('branch_id','counter_id','date','finalized'))) or die(mysql_error());
		}
		$con->sql_query("replace into pos_finalized ".mysql_insert_by_field(array('branch_id'=>$this->branch_id, 'date'=>$form['date_select'],'finalized'=>1,'finalize_timestamp'=>'CURRENT_TIMESTAMP')));

        // generate pos_cashier_finalize
        foreach($counter as $cid=>$dummy){
            $appCore->posManager->generatePosCashierFinalize($form['date_select'], $cid, $this->branch_id);
        }
        
		// generate sales cache
		$params = array();
		if($this->is_ajax_finalize)$params['write_process_status'] = 1;
		update_sales_cache($this->branch_id, $form['date_select'],'','', $params);

		// send notification
		$this->send_notification($form['date_select']);

		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			@chmod($check_file,0777);
		}

		// insert into log
		log_br($sessioninfo['id'], 'Counter Collection', "", "Finalise Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");

		// Commit
		$con->sql_commit();
		
		if(!$this->is_ajax_finalize){
			header("Location: /counter_collection.php?date_select=".$form['date_select']."&finalize=1&msg=".urlencode($LANG['COUNTER_COLLECTION_FINALIZED']));
			exit;
		}
	}

	function unfinalize(){
		global $con, $sessioninfo, $smarty, $LANG, $config, $appCore;

		// check level and approval
		if (!privilege('CC_UNFINALIZE')&&$sessioninfo['level']<9999) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_UNFINALIZE', BRANCH_CODE), "/index.php");

		$form = $_REQUEST;
		
		//
		/*if($config['monthly_closing']){
			$is_month_closed = $appCore->is_month_closed($form['date_select']);
			if($is_month_closed){
				header("Location: /counter_collection.php?date_select=".$form['date_select']."&unfinalize=1&msg=".urlencode($LANG['MONTH_DOCUMENT_IS_CLOSED']));
				exit;
			}
		}*/
		
		// prevent client abort
		ignore_user_abort(true);
		set_time_limit(0);
		
		// Begin Transaction
		$con->sql_begin_transaction();
		
		// delete finalized status
		$con->sql_query("delete from pos_counter_collection_tracking where branch_id=$this->branch_id and date=".ms($form['date_select']));
		//$con->sql_query("delete from pos_finalized where branch_id=$this->branch_id and date=".ms($form['date_select']));
		$upd = array();
		$upd['branch_id'] = $this->branch_id;
		$upd['date'] = $form['date_select'];
		$upd['finalized'] = 0;
		$upd['finalize_timestamp'] = 0;
		$con->sql_query("replace into pos_finalized ".mysql_insert_by_field($upd));
		
		// insert log
		log_br($sessioninfo['id'], 'Counter Collection', "", "Un-Finalise Collection (Branch : ".BRANCH_CODE.", Date: $form[date_select])");

		// delete from sales cache
		$date = $form['date_select'];
		// update sku items cost changed
		$q1 = $con->sql_query("select distinct(sku_item_id) from sku_items_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
		$sid_list = array();
		while($r = $con->sql_fetchrow($q1)){
			$sid_list[] = mi($r[0]);
			if(count($sid_list)>1000){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
                $sid_list = array();
			}
		}
		$con->sql_freeresult($q1);
		
		if($sid_list){
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($this->branch_id)." and sku_item_id in (".join(',',$sid_list).")");
            $sid_list = array();
		}

		$con->sql_query("delete from sku_items_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from category_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from member_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	    $con->sql_query("delete from pwp_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
	  	$con->sql_query("delete from dept_trans_cache_b".mi($this->branch_id)." where date=".ms($date));
		$con->sql_query("delete from pos_counter_finalize where branch_id=".mi($this->branch_id)." and date=".ms($date));
		$con->sql_query("delete from daily_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
		$con->sql_query("delete from pos_finalised_error where branch_id=".mi($this->branch_id)." and date=".ms($date));
		$con->sql_query("delete from sku_items_finalised_cache where branch_id=".mi($this->branch_id)." and date=".ms($date));
		if($config['masterfile_enable_sa']) $con->sql_query("delete from sa_sales_cache_b".mi($this->branch_id)." where date=".ms($date));
		
        if($config['enable_sn_bn']){
            $q1 = $con->sql_query("select * from pos_items_sn_history where branch_id = ".mi($this->branch_id)." and date=".ms($date));
            
            while($r = $con->sql_fetchassoc($q1)){
                // delete it from history
                $con->sql_query("delete from pos_items_sn_history where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
                
                $params = array();
                $params['serial_no'] = $r['serial_no'];
                $params['sid'] = $r['sku_item_id'];

                relocate_sn_pos_info($params);
            }
        }
        $con->sql_query("delete from pos_cashier_finalize where branch_id=".mi($this->branch_id)." and date=".ms($date));

		// create flag file for cron csa report
		if ($config['csa_generate_report']){
			if (!is_dir("csa_report_cache")) mkdir("csa_report_cache",0777); //check folder
			list($year,$month,$day)=explode("-", $form['date_select']);

			$check_file="csa_report_cache/".BRANCH_CODE.".$year.".str_pad($month, 2, "0", STR_PAD_LEFT).".csa_cache";
			file_put_contents($check_file,"r");
			chmod($check_file,0777);
		}

		// Commit
		$con->sql_commit();
		
		header("Location: /counter_collection.php?date_select=".$form['date_select']."&unfinalize=1&msg=".urlencode($LANG['COUNTER_COLLECTION_UNFINALIZED']));
		exit;
	}

	function sales_details()
	{
		global $con, $smarty, $pos_config, $sessioninfo, $mm_discount_col_value, $config;

		if (isset($_REQUEST['type']))
		{
		    if(strpos($_REQUEST['type'], 'special-')!==false){  // is special type
				if($_REQUEST['type']=='special-over')	$special_over_only = true;
				if($_REQUEST['type']=='special-'.$mm_discount_col_value)	$got_mm_discount = true;
				if($_REQUEST['type']=='special-deposit_r')	$only_deposit_rcv = true;
				if($_REQUEST['type']=='special-deposit_u')	$only_deposit_used = true;
				if($_REQUEST['type']=='special-deposit_cr')	$only_deposit_cancel_rcv = true;
				if($_REQUEST['type']=='special-deposit_cu')	$only_deposit_cancel_used = true;
				if($_REQUEST['type']=='special-trade_in')	$got_trade_in = true;
				if($_REQUEST['type']=='special-trade_in_writeoff')	$got_trade_in_writeoff = true;
				if($_REQUEST['type']=='special-used_quota')	$got_used_quota = true;
				if($_REQUEST['type']=='special-deposit_rf'){
					$only_deposit_refund = true;
				}
				if($_REQUEST['type']=='special-service_charges')	$got_service_charge = true;
				if($_REQUEST['type']=='special-total_gst_amt')	$got_gst = true;
				if($_REQUEST['type']=='special-cash_change')	$only_special_cash_change = true;
				

			}else{
                if (strtolower($_REQUEST['type']) == 'credit cards')
				{
					$types[] = ms('Credit Cards');
					foreach($pos_config['credit_card'] as $k)
					{
						$types[] = ms($k);
					}
					
				}elseif(strtolower($_REQUEST['type']) == 'check'){
					$types[] = ms('Check');
					$types[] = ms('Cheque');
				}
				else{
					$types[] = ms($_REQUEST['type']);
					
					if($_REQUEST['type']=='Cash'){
						//$types[] = "'Deposit'";
						//$show_cash_refund = true;
					}	
				}
				$types = join(",", $types);	
				$where[] = "pp.type in ($types)";
			}
		}else{
			// no type is provided
			$no_type = true;
		}

		if (isset($_REQUEST['counter_id'])){
		    $counter_id = mi($_REQUEST['counter_id']);
            $where[] = " p.counter_id = ".$counter_id;
		}

		if (isset($_REQUEST['card_no']))
		{
			//$where[] = "p.member_no = (select m.card_no from membership_history m where m.card_no = ".ms($_REQUEST['card_no'])." group by m.card_no) and p.branch_id = ".mi($_REQUEST['branch_id']);
			$where[] = "p.member_no = ".ms($_REQUEST['card_no'])." and p.branch_id = ".mi($_REQUEST['branch_id']);

		}
		else
		{
        	if (isset($_REQUEST['branch_id']))  $bid = mi($_REQUEST['branch_id']);
        	else    $bid = mi($this->branch_id);
			$where[] = "p.branch_id = ".$bid;
        }
		
		// filter cashier if found having config set
		if($_REQUEST['cashier_id'] && $config['counter_collection_split_counter_by_cashier']){
			$where[] = "p.cashier_id = ".mi($_REQUEST['cashier_id']);
		}

		$select = "round(sum(if(pp.type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount";
		$groupby = 'group by p.branch_id,p.date,p.counter_id, p.id';
		$having = "";
		
		if($only_special_cash_change){
			$select .= ", round(sum(if(pp.type='Cash',pp.amount,0)),2) as cash_payment_amount, sum(if(pp.type='Deposit',1,0)) as paid_by_deposit_count";
			$having .= "having paid_by_deposit_count=0 and amount_change>cash_payment_amount";
		}
        if (isset($_REQUEST['e'])){
		    $e = $_REQUEST['e'];
		    if(!$e){    // get the max pos time
		        if($bid)	$e_filter[] = "branch_id=".mi($bid);
		        if($counter_id) $e_filter[] = "counter_id=".mi($counter_id);
		        $e_filter[] = "date=".ms($_REQUEST['date']);
		        if($e_filter)   $e_filter = "where ".join(' and ', $e_filter);
		        else    $e_filter = '';
				$con->sql_query("select max(end_time) from pos $e_filter");
				$e = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}
            $where[] = " p.end_time <= ".ms($e);
		}
		if (isset($_REQUEST['s'])) $where[] = "p.end_time >= ".ms($_REQUEST['s']);

		if($got_used_quota){
			$where[] = "p.quota_used<>0";
		}
		if($got_service_charge){
			$where[] = "p.service_charges<>0";
		}
		if($got_gst){
			$where[] = "p.total_gst_amt<>0";
		}
		$where[] = "(pp.adjust <> 1 or pp.id is null)";
		$where = implode(" and ", $where);

		$sql = "select p.*, pp.remark, pp.type, pp.adjust, pp.changed, user.u , round(p.service_charges-p.service_charges_gst_amt,2) as real_service_charges,
			$select 
			from pos p
			left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
			left join user on p.cashier_id = user.id
			where p.date = ".ms($_REQUEST['date'])." and pp.type != 'Rounding' and $where $groupby $having order by p.end_time";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q1)){
			$r['pos_more_info'] = unserialize($r['pos_more_info']);
			if($r['cancel_status'] && $r['pos_more_info']['cancel_at_backend']){
				$r['cancel_at_backend'] = 1;
			}
			
		    $currency_arr = pp_is_currency($r['remark'], $r['payment_amount']);
            if($currency_arr['is_currency']){   // it is foreign currency
				$r['payment_amount'] = round($currency_arr['rm_amt'], 2);
			}
			
			$r['over_amt'] = round($r['amount_tender'] - $r['amount'] - $r['amount_change'] - $r['service_charges'], 2);
			
			// only show over receipt
			if($special_over_only && !$r['over_amt'])   continue;
			
			// check only show got mix and match
			if($got_mm_discount){
				$q_pmm = $con->sql_query("select id from pos_mix_match_usage where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] limit 1");
				$passed = $con->sql_numrows($q_pmm)>0 ? 1 : 0;
				$con->sql_freeresult($q_pmm);
				if(!$passed)	continue;
			}elseif($only_deposit_rcv || $only_deposit_used || $only_deposit_cancel_rcv || $only_deposit_cancel_used){
				if($only_deposit_rcv) $filter_type = "RECEIVED";
				elseif($only_deposit_used) $filter_type = "USED";
				elseif($only_deposit_cancel_rcv) $filter_type = "CANCEL_RCV";
				else $filter_type = "CANCEL_USED";
				$q_deposit = $con->sql_query("select * from pos_deposit_status_history where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and pos_date=".ms($r['date'])." and pos_id=".mi($r['id'])." and type = ".ms($filter_type));
				
				$passed = $con->sql_numrows($q_deposit)>0 ? 1 : 0;
				if(!$passed)	continue;
			}elseif($got_trade_in || $got_trade_in_writeoff){
				$q_ti =$con->sql_query("select id from pos_items pi where branch_id=".mi($r['branch_id'])." and counter_id=".mi($r['counter_id'])." and date=".ms($r['date'])." and pos_id=".mi($r['id'])." and trade_in_by>0".( $got_trade_in_writeoff ? " and writeoff_by>0" : '')." limit 1");
				$passed = $con->sql_numrows($q_ti)>0 ? 1 : 0;
				$con->sql_freeresult($q_ti);
				if(!$passed)	continue;
			}elseif($only_deposit_refund){
				$q_dp = $con->sql_query("select sum(amount) from pos_payment where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] and type='Deposit'");
				$deposit_amt = $con->sql_fetchfield(0);
				$con->sql_freeresult();
				
				if($r['amount'] > $deposit_amt)	continue;
			}
			
			$deposit_amt = 0;
			if($show_cash_refund || $only_deposit_used || $only_deposit_refund){	// need to get deposit amt
				// get deposit amt
				$q_dp = $con->sql_query("select sum(amount) from pos_payment where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] and type='Deposit'");
				$deposit_amt = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}
			
			if($show_cash_refund){				
				if($r['amount'] < $deposit_amt){
					$r['payment_amount'] = $r['amount_change']*-1;
				}else{
					if($only_deposit_refund)	continue;	// only show deposit refund
					elseif($show_cash_refund){	// show by cash + cash refund
						// since no cash refund, check whether got cash payment for this receipt, if no then also skip
						$q_dp = $con->sql_query("select count(*) from pos_payment where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] and type='Cash'");
						$tmp_got_cash = $con->sql_fetchfield(0);
						$con->sql_freeresult();
						
						if(!$tmp_got_cash)	continue;
					}
				}
			}else{
				if($only_deposit_used || $only_deposit_refund){
					$used_amt = $deposit_amt;
		 			$refund = 0;
		 			if($r['amount'] < $deposit_amt && $r['amount_change'] > 0){
		 				$refund = $r['amount_change'];
		 				$used_amt -= $refund;
		 			}
		 			$r['deposit_used_amt'] = $used_amt;
		 			$r['deposit_refund_amt'] = $refund;
				}
			}
			
			if($no_type){
				$q_dp = $con->sql_query("select sum(amount) from pos_payment where branch_id=$r[branch_id] and counter_id=$r[counter_id] and date=".ms($r['date'])." and pos_id=$r[id] and type in ('Discount', ".ms($mm_discount_col_value).")");
				$total_discount_amt = mf($con->sql_fetchfield(0));
				$con->sql_freeresult();
				
				$r['payment_amount'] -= $total_discount_amt;
			}
			
			$r['real_gst_amt'] = $r['total_gst_amt'];
					
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('got_used_quota', $got_used_quota);
		$smarty->assign('got_service_charge', $got_service_charge);
		$smarty->assign('got_gst', $got_gst);
		$smarty->assign('only_special_cash_change', $only_special_cash_change);
		
		//print_r($items);
		$smarty->assign('items', $items);

		if (isset($_REQUEST['type']) && $_REQUEST['type']!="Cash" && strpos($_REQUEST['type'], 'special-')===false){
			//payement summary except Cash
			$p_sql = "select p.*, pp.remark, pp.type, sum(pp.amount) as payment_amount from
				pos_payment pp
				left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
				where p.date = ".ms($_REQUEST['date'])." and p.cancel_status=0 and $where group by pp.remark,p.receipt_no order by pp.remark";
//print $p_sql."<br />";
			$con->sql_query($p_sql);
			while($p = $con->sql_fetchassoc()){
				$payment_type[$p['type']][$p['remark']]['payment_amount'] += $p['payment_amount'];
				$receipt_no[$p['remark']][$p['receipt_no']]['pos_id']=$p['id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['date']=$p['date'];
				$receipt_no[$p['remark']][$p['receipt_no']]['cashier_id']=$p['cashier_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['counter_id']=$p['counter_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['branch_id']=$p['branch_id'];
				$receipt_no[$p['remark']][$p['receipt_no']]['payment_amount']+=$p['payment_amount'];
			}
			$con->sql_freeresult();

			if ($payment_type) {
				foreach ($payment_type as $ptype => $other){
					foreach ($other as $remark => $dummy){ 
						$payment_type[$ptype][$remark]['rowspan'] = count($receipt_no[$remark]);
					}
				}
			}

	        $smarty->assign('payment_type', $payment_type);
	        $smarty->assign('receipt_no', $receipt_no);
		}
		
		if($only_deposit_used || $only_deposit_refund){
			$smarty->assign('show_deposit_used_refund', 1);
		}

		$smarty->display('counter_collection.sales_details.tpl');
	}

	function cancel_receipt(){
		global $con, $smarty, $config, $LANG;

		if($config['counter_collection_need_privilege_cancel_bill'] && !privilege('CC_CANCEL_BILL')){
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CC_CANCEL_BILL', BRANCH_CODE), "/counter_collection.php");
		}
		
		if ($_REQUEST['fsubmit'])
		{
			$receipt_no = trim($_REQUEST['receipt_no']);
			
			$filter = array();
			$filter[] = "branch_id=$this->branch_id and date = ".ms($_REQUEST['date'])." and counter_id=".mi($_REQUEST['counter_id']);
			if(strlen($receipt_no)>=17){
				// Receipt Ref No
				$filter[] = "receipt_ref_no=".ms($receipt_no);
			}else{
				// Receipt No
				$filter[] = "receipt_no=".ms($receipt_no);
			}
			$str_filter = join(' and ', $filter);
			$q1 = $con->sql_query("select * from pos where $str_filter");
			while ($r = $con->sql_fetchassoc($q1))
			{
				if($r['deposit']){
					// Deposit Create
					print "<script>alert('Receipt (".$r['receipt_ref_no'].") linked with a Deposit, please go to Deposit Listing to do cancellation.');</script>";
				}else{
					// check if this receipt contains ewallet
					$q2 = $con->sql_query("select * from pos_payment where (type like '%ewallet%' or group_type = 'ewallet') and adjust=0 and branch_id = ".mi($r['branch_id'])." and date = ".ms($r['date'])." and counter_id = ".mi($r['counter_id'])." and pos_id = ".mi($r['id']));

					if($con->sql_numrows($q2) > 0){
						$pp_info = $con->sql_fetchassoc($q2);
						$ewallet_type = str_replace("ewallet_", "", $pp_info['type']);
						$r['ewallet_type'] = $ewallet_type;
						
						if($r['cancel_status']){
							$r['ewallet_error'] = 1;
							$r['ewallet_error_msg'] = $LANG['COUNTER_COLLECTION_EWALLET_CB_UNDO_DISABLED'];
						}elseif($r['date'] != date("Y-m-d")){
							$r['ewallet_error'] = 1;
							$r['ewallet_error_msg'] = $LANG['COUNTER_COLLECTION_EWALLET_CB_NOT_ACTUAL_DATE'];
						}
						
					}else $r['ewallet_type'] = "";
					$con->sql_freeresult($q2);
					
					// Check if contain Deposit Claim
					$q3 = $con->sql_query("select * from pos_payment where type='Deposit' and adjust=0 and branch_id = ".mi($r['branch_id'])." and date = ".ms($r['date'])." and counter_id = ".mi($r['counter_id'])." and pos_id = ".mi($r['id']));
					if($con->sql_numrows($q3) > 0){
						// Deposit Claim
						print "<script>alert('Receipt (".$r['receipt_ref_no'].") contain deposit claimed, modification on the receipt status is not allowed');</script>";
						continue;
					}
					$con->sql_freeresult($q3);
					
					$items[] = $r;
				}
			}
			$con->sql_freeresult($q1);
			$smarty->assign("items",$items);
		}
		$this->display('counter_collection.cancel_receipt.tpl');
	}	

	function ajax_cancel_receipt()
	{
		global $con, $smarty, $LANG, $sessioninfo;

		/*$con->sql_query("select * from pos_counter_collection_tracking where branch_id = ".mi($this->branch_id)." and date = ".ms(dmy_to_sqldate($_REQUEST['date']))." and finalized = 1 ");
		if ($con->sql_numrows()>0)
		{
			print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;
		}*/
		if($this->check_finalized($_REQUEST['date'])){
            print "Error: ".$LANG['COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE'];
			exit;
		}
		
		
		$bid = mi($this->branch_id);
		$date = $_REQUEST['date'];
		$counter_id = mi($_REQUEST['counter_id']);
		$pos_id = mi($_REQUEST['pos_id']);
		$v = mi($_REQUEST['v']);
		$ewallet_type = trim($_REQUEST['ewallet_type']);
		$receipt_ref_no = trim($_REQUEST['receipt_ref_no']);
		
		// do ewallet void payment
		if($ewallet_type){
			$prms = array();
			$prms['branch_id'] = $bid;
			$prms['counter_id'] = $counter_id;
			$prms['date'] = $date;
			$prms['ewallet_type'] = $ewallet_type;
			$prms['receipt_ref_no'] = $receipt_ref_no;
			
			$ret = $this->void_ewallet_payment($prms);
			
			if($ret && json_decode($ret, true)){
				$result = json_decode($ret, true);
				
				// need to do check date from tpl for the same date only can do void
				if($result['success'] != 1){
					print "Error: ".sprintf($LANG['COUNTER_COLLECTION_EWALLET_CB_ERROR'], $result['error']);
					exit;
				}
			}
			
		}
		
		$con->sql_query("select * from pos where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and id=$pos_id");
		$pos = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$pos){
			print "Error: ".$LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
			exit;
		}
		
		$pos['pos_more_info'] = unserialize($pos['pos_more_info']);
		if(!$pos['pos_more_info'])	$pos['pos_more_info'] = array();
		
		$upd = array();
		$upd['cancel_status'] = $v;
		$upd['pos_more_info'] = $pos['pos_more_info'];
		
		if($pos['cancel_status'] && !$v){
			// change from cancel to active
			if(!$pos['pos_more_info']['cancel_at_backend']){	// not cancel from backend
				// make a record to let future know this receipt was firstly cancel at counter
				$upd['pos_more_info']['origin_cancel_at_counter'] = 1;
			}
		}
		
		if($v){	// cancel
			$upd['pos_more_info']['cancel_at_backend']['user_id'] = $sessioninfo['id'];
			$mp_sqls = "update membership_promotion_items set cancelled = 1 where branch_id = $bid and counter_id = $counter_id and date = ".ms($date)." and pos_id = $pos_id";
			$mm_sqls = "update membership_promotion_mix_n_match_items set used = -1 where branch_id = $bid and counter_id = $counter_id and date = ".ms($date)." and pos_id = $pos_id";
		}
		else {
			$mp_sqls = "update membership_promotion_items set cancelled = 0 where branch_id = $bid and counter_id = $counter_id and date = ".ms($date)." and pos_id = $pos_id";
			$mm_sqls = "update membership_promotion_mix_n_match_items set used = 1 where branch_id = $bid and counter_id = $counter_id and date = ".ms($date)." and pos_id = $pos_id";
		}
		
		$upd['pos_more_info'] = serialize($upd['pos_more_info']);
		
		$con->sql_query("update pos set ".mysql_update_by_field($upd)." where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and id=$pos_id");

		if ($con->sql_affectedrows()>0)
		{
			//$receipt_no = get_pos_receipt_no($this->branch_id, $_REQUEST['date'], $_REQUEST['counter_id'], $_REQUEST['pos_id']);
			$receipt_no = $pos['receipt_no'];
			
			if($v){	// is cancel
				// get max pos_receipt_cancel id
				$con->sql_query("select max(id) from pos_receipt_cancel where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and receipt_no=".ms($receipt_no));
				$max_cancel_id = mi($con->sql_fetchfield(0));
				$con->sql_freeresult();
				
				if($max_cancel_id<1000)	$max_cancel_id = 1000;
				$max_cancel_id++;
				
				// create pos_receipt_cancel
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['counter_id'] = $counter_id;
				$upd['date'] = $date;
				$upd['receipt_no'] = $receipt_no;
				$upd['cancelled_by'] = $upd['verified_by'] = $sessioninfo['id'];
				$upd['cancelled_time'] = 'CURRENT_TIMESTAMP';
				$upd['id'] = $max_cancel_id;
				$con->sql_query("insert into pos_receipt_cancel ".mysql_insert_by_field($upd));
			}
			
			//update membership_promotion_items
			$con->sql_query($mp_sqls);
			
			//update membership_promotion_mix_n_match_items
			$con->sql_query($mm_sqls);
			
			log_br($sessioninfo['id'], 'Counter Collection '.($v ? 'Cancel' : 'Active').' Receipt', $receipt_no, "$msg (Date: $date, Counter ID: $counter_id, Receipt No: $receipt_no)");
			if ($v)
			{
				print $LANG['COUNTER_COLLECTION_RECEIPT_CANCELLED'];
			}
			else
			{
				print $LANG['COUNTER_COLLECTION_RECEIPT_UNCANCELLED'];
			}

		}
		else
		{
			print $LANG['COUNTER_COLLECTION_RECEIPT_NO_CHANGES'];
		}
	}

	function item_details(){
		global $con,$smarty,$pos_config, $mm_discount_col_value, $sessioninfo, $appCore;

		// get by receipt ref no
		if($_REQUEST['receipt_ref_no']){
			//$receipt_info = split_receipt_ref_no($_REQUEST['receipt_ref_no']);
			//Replace with new function
			$receipt_info = get_pos_by_receipt_ref_no($_REQUEST['receipt_ref_no']);
			if(!$receipt_info)	die("Invalid Params");
			if($receipt_info['error'])	die($receipt_info['error']);
			
			$branch_id = $receipt_info['branch_id'];
			$date = $receipt_info['date'];
			$counter_id = $receipt_info['counter_id'];
			$pos_id = $receipt_info['pos_id'];
		}
		
		if(!isset($branch_id)){
			if (isset($_REQUEST['branch_id']))
				$branch_id = $_REQUEST['branch_id'];
			else
				$branch_id = $this->branch_id;
		}
					
		if(!isset($date))	$date = $_REQUEST['date'];
		if(!isset($counter_id))	$counter_id = mi($_REQUEST['counter_id']);
		if(!isset($pos_id))	$pos_id = mi($_REQUEST['pos_id']);
		$filter = "where p.branch_id = ".mi($branch_id)." and p.date = ".ms($date)." and p.counter_id = ".mi($counter_id)." and p.id = ".mi($pos_id);
		
		$con->sql_query("select * from pos p $filter");
		$pos = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		
		$pos['receipt_remark'] = unserialize($pos['receipt_remark']);
		$pos['pos_more_info'] = unserialize($pos['pos_more_info']);
		$pos['branch_code'] = $branch_id == $sessioninfo['branch_id'] ? BRANCH_CODE : get_branch_code($branch_id);
		
		// Receipt Sales Agent
		if($pos['receipt_sa']){
			$pos['receipt_sa'] = unserialize($pos['receipt_sa']);
			if($pos['receipt_sa']){
				foreach($pos['receipt_sa'] as $sa_id=>$sa_info){
					// check if the sales agent ID was in ID list or older method of storing
					if(is_array($sa_info) && isset($sa_info['id'])) $tmp_sa_id = $sa_info['id'];
					else $tmp_sa_id = $sa_info;
					
					if($pos['sa_data'][$tmp_sa_id])	continue;
					
					// get sales agent
					$pos['sa_data'][$tmp_sa_id] = $appCore->salesAgentManager->getSA($tmp_sa_id);
					unset($tmp_sa_id);
				}
			}
		}
		$gst_summary = array();
		
		/*$con->sql_query("select p.counter_id, u.u as cashier_name, op_u.u as open_price_user, id_u.u as item_discount_user, r_u.u as return_user,p.receipt_no, p.end_time, p.member_no, pi.pos_id,
						 amount_change, pi.qty, pi.price, pi.discount, si.mcode, si.sku_item_code, si.description,pi.remark
						 from pos p
						 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
						 left join sku_items si on pi.sku_item_id = si.id
						 left join user u on u.id = p.cashier_id
						 left join user op_u on pi.open_price_by=op_u.id
						 left join user id_u on pi.item_discount_by=id_u.id
						 left join user r_u on pi.return_by=r_u.id
						 $filter");
		$items = $con->sql_fetchrowset();
		$con->sql_freeresult();*/

		
		$items = $deposit_items = array();
		
		if (!$receipt_info){
			$receipt_info = array();
		}
		$receipt_info['sub_total_amt'] = 0;
		$q1 = $con->sql_query("select cs.network_name, p.counter_id, u.u as cashier_name, op_u.u as open_price_user, id_u.u as 
							   item_discount_user, r_u.u as return_user,p.receipt_no, p.pos_time,p.end_time, p.member_no, pi.pos_id, 
							   p.deposit, p.receipt_ref_no, amount_change, pi.qty, pi.price, pi.discount, pi.more_info, si.mcode, si.sku_item_code, 
							   si.description,pi.remark,pi.trade_in_by,pi.writeoff_by, u_ti.u as trade_in_by_u, u_tiw.u as writeoff_by_u,pi.verify_code_by,pi.barcode,u_verify.u as verify_code_by_u, p.branch_id, p.cancel_status,
							   pi.tax_indicator, pi.tax_rate, pi.before_tax_price, pi.tax_amount, pi.item_id, pi.item_sa
							   from pos p
							   left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
							   left join sku_items si on pi.sku_item_id = si.id
							   left join user u on u.id = p.cashier_id
							   left join user op_u on pi.open_price_by=op_u.id
							   left join user id_u on pi.item_discount_by=id_u.id
							   left join user r_u on pi.return_by=r_u.id
							   left join user u_ti on u_ti.id=pi.trade_in_by
							   left join user u_tiw on u_tiw.id=pi.writeoff_by
							   left join user u_verify on u_verify.id=pi.verify_code_by
							   left join counter_settings cs on cs.id = p.counter_id and cs.branch_id = p.branch_id
							   $filter");
						 
		while($r = $con->sql_fetchassoc($q1)){
			if($r['deposit']){
				// is deposit
				$q2 = $con->sql_query($sql = "select pdsh.*, p.amount, pp.amount as payment_amount, p.cancel_status, 
									   p.prune_status, pp.type as payment_type
									   from pos_deposit_status_history pdsh
									   left join pos_payment pp on pp.pos_id = pdsh.pos_id and pp.branch_id = pdsh.branch_id and pp.counter_id = pdsh.counter_id and pp.date = pdsh.pos_date
									   left join pos p on p.id = pp.pos_id and p.branch_id = pp.branch_id and p.date = pp.date and p.counter_id = pp.counter_id
									   where pdsh.branch_id = ".mi($branch_id)." and pdsh.pos_date = ".ms($date)." and pdsh.counter_id = ".mi($counter_id)." and pdsh.pos_id = ".mi($pos_id));
				//print "$sql<br>";
				$items_info = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				$q3 = $con->sql_query($sql="select pd.item_list, pd.receipt_no, pd.pos_time, cs.network_name, u.u as cashier_name, pd.counter_id, pd.branch_id,
										pd.deposit_amount, pd.gst_info, pd.gst_amount, pd.pos_id
									   from pos_deposit pd
									   left join counter_settings cs on cs.id = pd.counter_id and cs.branch_id = pd.branch_id
									   left join user u on u.id = pd.cashier_id
									   where pd.pos_id = ".mi($items_info['deposit_pos_id'])." and pd.date = ".ms($items_info['deposit_pos_date'])." and pd.branch_id = ".mi($items_info['deposit_branch_id'])." and pd.counter_id = ".mi($items_info['deposit_counter_id']));
				//print "$sql<br>";
				if($con->sql_numrows($q3) > 0){
					$tmp_deposit_info = $con->sql_fetchassoc($q3);
					$tmp_deposit_info['pos_time'] = date("Y-m-d H:i:s", $tmp_deposit_info['pos_time']);
					
					$q4 = $con->sql_query($qq="select receipt_ref_no from pos where id = " . mi($tmp_deposit_info['pos_id']) . " and branch_id = " . mi($tmp_deposit_info['branch_id']) . " and counter_id = " . mi($tmp_deposit_info['counter_id']) . " and pos_time = " . ms($tmp_deposit_info['pos_time']));
					if($con->sql_numrows($q4) > 0){
						$ref_no = $con->sql_fetchassoc($q4);
						$con->sql_freeresult($q4);
						$tmp_deposit_info['cancel_receipt_ref_no'] = $ref_no['receipt_ref_no'];
					}
					
					//print_r($tmp_deposit_info);
					$items_info['item_list'] = $tmp_deposit_info['item_list'];

					if($items_info['type'] == "CANCEL_RCV" || $items_info['type'] == "CANCEL_USED"){
						$cancel_deposit_info = $tmp_deposit_info;
					}
					
					// is redeem deposit
					if($tmp_deposit_info['gst_amount']){
						$gst_info = unserialize($tmp_deposit_info['gst_info']);
						$gst_key = $gst_info['indicator_receipt']."-".mi($gst_info['rate']);
						$gst_summary[$gst_key]['tax_indicator'] = $gst_info['indicator_receipt'];
						$gst_summary[$gst_key]['tax_rate'] = mi($gst_info['rate']);
						$gst_summary[$gst_key]['before_tax_price'] += round($tmp_deposit_info['deposit_amount']-$tmp_deposit_info['gst_amount'],2);
						$gst_summary[$gst_key]['tax_amount'] += $tmp_deposit_info['gst_amount'];						
					}
				}
				$con->sql_freeresult($q3);
				
				if($items_info['item_list']){
					$item_list = unserialize($items_info['item_list']);
					//print_r($item_list);
					foreach($item_list as $row=>$f){
						$di['barcode'] = $f['barcode'];
						$di['qty'] = $f['quantity'];
						$di['sku_item_code'] = $f['item']['sku_item_code'];
						$di['description'] = $f['item']['receipt_description'];
						$di['uom_code'] = $f['item']['uom_code'];
						$di['selling_price'] = $f['fixed_price'];
						$deposit_items[] = $di;
					}
				}

				if($item_info['type'] == "USED") $deposit_amt = $items_info['payment_amount'];
				else  $deposit_amt = abs($items_info['amount']);

				$r['deposit_amount'] = $deposit_amt;
				$deposit_info = $r;
				
				$smarty->assign("cancel_deposit_info", $cancel_deposit_info);
				$smarty->assign("deposit_info", $deposit_info);
				$smarty->assign("is_deposit", true);
			}else {
				// normal non-deposit
				$more = unserialize($r['more_info']);
				if(isset($more['discount_str']))
					$r['discount_str'] = $more['discount_str'];
				
				
				// got gst
				if($r['tax_indicator']){
					$gst_key = $r['tax_indicator']."-".mf($r['tax_rate']);
					$gst_summary[$gst_key]['tax_indicator'] = $r['tax_indicator'];
					$gst_summary[$gst_key]['tax_rate'] = $r['tax_rate'];
					$gst_summary[$gst_key]['before_tax_price'] += $r['before_tax_price'];
					$gst_summary[$gst_key]['tax_amount'] += $r['tax_amount'];
				}
				
				$receipt_info['sub_total_amt'] += $r['price'] - $r['discount'];
				
				if($r['qty']<0){
					// goods return
					$q_gr = $con->sql_query("select * from pos_goods_return where branch_id=$branch_id and date=".ms($date)." and counter_id=$counter_id and pos_id=$pos_id and item_id=".mi($r['item_id']));
					$r['pos_goods_return'] = $con->sql_fetchassoc($q_gr);
					$con->sql_freeresult($q_gr);
				}
				
				// items sales agent
				if($r['item_sa']){
					$r['item_sa'] = unserialize($r['item_sa']);
					if($r['item_sa']){
						foreach($r['item_sa'] as $sa_id=>$sa_info){
							// check if the sales agent ID was in ID list or older method of storing
							if(is_array($sa_info) && isset($sa_info['id'])) $tmp_sa_id = $sa_info['id'];
							else $tmp_sa_id = $sa_info;
							
							// unset the current $sa_id as it could be older counter version that using index key instead of sales agent ID
							unset($r['item_sa'][$sa_id]);
							$r['item_sa'][$tmp_sa_id] = $tmp_sa_id;
							
							if($pos['sa_data'][$tmp_sa_id])	continue;
							
							// get sales agent
							$pos['sa_data'][$tmp_sa_id] = $appCore->salesAgentManager->getSA($tmp_sa_id);
							 // this is to set for the compatible of old and new version of sales agent ID stored in item_sa
						}
					}
				}
				
				$items[] = $r;
			}
		}
		
		//print_r($items);
		$receipt_info['total_amt'] = $receipt_info['sub_total_amt'];
		
		$con->sql_freeresult($q1);
		$smarty->assign('deposit_items',$deposit_items);
		$smarty->assign('items',$items);
		
		// get deleted items
		if(!$pos['deposit']){
			$receipt_info['deleted_item_list'] = array();
			$q_pdi = $con->sql_query("select pdi.*, si.mcode,si.artno, si.sku_item_code,si.description, user.u as delete_by_u
				from pos_delete_items pdi
				left join sku_items si on si.id=pdi.sku_item_id
				left join user on user.id=pdi.delete_by
				where pdi.branch_id=".mi($pos['branch_id'])." and pdi.counter_id=".mi($pos['counter_id'])." and pdi.date=".ms($pos['date'])." and pdi.pos_id=".mi($pos['id']));
			while($r = $con->sql_fetchassoc($q_pdi)){
				$receipt_info['deleted_item_list'][] = $r;
			}
			$con->sql_freeresult($q_pdi);
		}
		
		// service charge
		if($pos['service_charges']){
			$receipt_info['total_amt'] += $pos['service_charges'];
		}
		
		if($pos['pos_more_info']['service_charges']['sc_gst_detail'] && $pos['service_charges_gst_amt']>0){
			$gst_key = $pos['pos_more_info']['service_charges']['sc_gst_detail']['indicator_receipt']."-".mi($pos['pos_more_info']['service_charges']['sc_gst_detail']['rate']);
			$gst_summary[$gst_key]['tax_indicator'] = $pos['pos_more_info']['service_charges']['sc_gst_detail']['indicator_receipt'];
			$gst_summary[$gst_key]['tax_rate'] = mi($pos['pos_more_info']['service_charges']['sc_gst_detail']['rate']);
			$gst_summary[$gst_key]['before_tax_price'] += round($pos['service_charges']-$pos['service_charges_gst_amt'],2);
			$gst_summary[$gst_key]['tax_amount'] += $pos['service_charges_gst_amt'];
		}
		
		// is redeem deposit
		if($pos['pos_more_info']['deposit']){
			foreach($pos['pos_more_info']['deposit'] as $r){
				if(!$r['pos_more_info']['is_security_deposit_type'] && unserialize($r['gst_info'])){
					$gst_info = unserialize($r['gst_info']);
					$gst_key = $gst_info['indicator_receipt']."-".mi($gst_info['rate'])."N";
					$gst_summary[$gst_key]['tax_indicator'] = $gst_info['indicator_receipt'];
					$gst_summary[$gst_key]['tax_rate'] = mi($gst_info['rate']);
					$gst_summary[$gst_key]['before_tax_price'] += round(($r['amount']-$r['gst_amount'])*-1,2);
					$gst_summary[$gst_key]['tax_amount'] += $r['gst_amount']*-1;
				}
			}
			
			// select the deposit information
			$dsql = $con->sql_query("select p.receipt_no, p.receipt_ref_no, p.id as pos_id, p.branch_id, p.counter_id, p.date
									from pos_deposit pd
									left join pos_deposit_status_history pdsh on pdsh.deposit_branch_id=pd.branch_id and pdsh.deposit_pos_date=pd.date and pdsh.deposit_counter_id=pd.counter_id and pdsh.deposit_pos_id=pd.pos_id
									left join pos p on p.branch_id=pdsh.deposit_branch_id and p.date=pdsh.deposit_pos_date and p.counter_id=pdsh.deposit_counter_id and p.id=pdsh.deposit_pos_id
									where pdsh.branch_id=".mi($pos['branch_id'])." and pdsh.counter_id=".mi($pos['counter_id'])." and pdsh.pos_date=".ms($pos['date'])." and pdsh.pos_id=".mi($pos['id'])." and type = 'USED'");
			
			while($dc = $con->sql_fetchrow($dsql)){
				$dc_list[] = $dc;
			}
		}
		
		//print_r($gst_summary);
		$smarty->assign('gst_summary',$gst_summary);

		if(!$deposit_items){
			$smarty->assign("amount_change", $items[0]['amount_change']);

			$con->sql_query("select pp.*, ap_u.u as approved_by,
							case when pp.type = 'Mix & Match Total Disc' then 1
							when pp.type = 'Discount' then 2
							else 3 end as sequence
							from pos_payment pp
							left join user ap_u on pp.approved_by=ap_u.id
							where pp.branch_id = ".mi($branch_id)." and pp.date = ".ms($date)." and pp.counter_id = ".mi($counter_id)." and pp.pos_id=".mi($pos_id)." and pp.adjust <> 1 
							order by sequence, pp.id");

			while($r = $con->sql_fetchassoc()){
				$currency_arr = pp_is_currency($r['remark'], $r['amount']);
				if($currency_arr['is_currency']){   // it is foreign currency
					$r['amount'] = $currency_arr['rm_amt'];
				}
				
				if($r['type'] == 'Discount' || $r['type'] == $mm_discount_col_value){
					// discount
					$receipt_info['discount_list'][] = $r;
					$receipt_info['total_amt'] -= $r['amount'];
				}elseif($r['type']=='Rounding'){
					$receipt_info['rounding'] = $r;
					$receipt_info['total_amt'] += $r['amount'];
				}else{
					// payment
					if($r['type'] == "Currency_Adjust") $r['type'] = "Currency Adjust";
					
					$payment[] = $r;
				}
				
			}
			$con->sql_freeresult();
			
			// check mix and match info
			$con->sql_query("select * from pos_mix_match_usage where branch_id=".mi($branch_id)." and date=".ms($date)." and counter_id=".mi($counter_id)." and pos_id=".mi($pos_id)." order by id");
			while($r = $con->sql_fetchassoc()){
				$r['more_info'] = unserialize($r['more_info']);
				$pos_mix_match_usage_list[] = $r;
			}
			$con->sql_freeresult();

			$smarty->assign('payment',$payment);
		}
        
        //print_r($pos);
        $smarty->assign('pos', $pos);
        $smarty->assign('dc_list', $dc_list);
        $smarty->assign('pos_mix_match_usage_list',$pos_mix_match_usage_list);
        $smarty->assign('receipt_info',$receipt_info);
		$smarty->display('counter_collection.item_details.tpl');
	}

	function change_advance(){
		global $con, $smarty, $config, $LANG, $pos_config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");
		$type = trim($_REQUEST['type']);
		if(!$type)	$type = 'ADVANCE';

		$filter = array();
		$filter[] = "p.branch_id=$this->branch_id";
		$filter[] = "p.counter_id=".mi($_REQUEST['counter_id']);
		$filter[] = "p.date=".ms($_REQUEST['date']);
		$filter[] = "p.timestamp between ".ms($_REQUEST['s'])." and ".ms($_REQUEST['e']);
		$filter[] = "p.type=".ms($type);
		
		if($config['counter_collection_split_counter_by_cashier']){
			$filter[] = "user_id=".mi($_REQUEST['cashier_id']);
		}
		
		$filter = "where ".join(' and ', $filter);
		
		$sql = "select p.*, u1.id as cashier_id, u1.u as cashier, u2.id as approved_by_id, u2.u as approved_by 
				from pos_cash_history p 
				left join user u1 on u1.id = p.user_id
				left join user u2 on u2.id = p.collected_by
				$filter";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$items = array();
		while($r = $con->sql_fetchassoc($q1)){
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		
		// select Cash Advance Default Reason from POS settings
		$q1 = $con->sql_query("select * from pos_settings ps where ps.setting_name = 'ca_default_reason' and branch_id = ".mi($this->branch_id));
		$ca_reason_settings = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$smarty->assign('history_type', $type);
		$smarty->assign("items", $items);
		$smarty->assign("ca_reason_settings", $ca_reason_settings);
		$smarty->display('counter_collection.change_advance.tpl');
	}

	function save_change_advance(){
		global $sessioninfo,$con,$LANG,$config;

		$update = 0;
		$form = $_REQUEST;
		$form['branch_id'] = $this->branch_id;
		if($config['counter_collection_split_counter_by_cashier']){
			$form['user_id'] = $form['cashier_id'];
		}else{
			$form['user_id'] = $sessioninfo['id'];
		}
		$form['collected_by'] = $sessioninfo['id'];
		$form['type'] = $_REQUEST['history_type'];
		if(!$form['type'])	$form['type'] = 'ADVANCE';
		$form['timestamp'] = $_REQUEST['e'];
		
		foreach($_REQUEST['amount'] as $n => $amt){
			$id = intval($_REQUEST['id'][$n]);
			$reason = $_REQUEST['reason'][$n];
			
			if ($form['type'] == 'ADVANCE') {
				$amt = mf($amt) * -1; //cash advance must always negative (form data is positive)
			}
			
			$form['amount'] = $amt;
			
			if ($id>0)	// update current
			{
				$con->sql_query("update pos_cash_history set amount = ".mf($amt).", reason=".ms($reason)." where id = ".mi($_REQUEST['id'][$n])." and date = ".ms($_REQUEST['date'])." and branch_id = ".$this->branch_id." and counter_id = ".$_REQUEST['counter_id']) or die(mysql_error());
				$q1 = $con->sql_query("select * from pos_cash_history where date = ".ms($_REQUEST['date'])." and branch_id = ".$this->branch_id." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".$id);
				$r = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$form['oamount'] = $r['oamount'];
			}
			else	// add new
			{
				$form['oamount'] = $amt;
				$form['remark'] = $_REQUEST['remark'][$n];
				$form['reason'] = $_REQUEST['reason'][$n];
				
				if($config['counter_collection_split_counter_by_cashier'] && $form['user_id'] != $sessioninfo['id']){
					//$tmp['original_owner'] = $sessioninfo['id'];
					//$form['more_info'] = serialize($tmp);
				}
				$con->sql_query("insert into pos_cash_history ".mysql_insert_by_field($form,array('branch_id','counter_id','date','user_id','collected_by','type','amount','oamount','timestamp','remark','reason'))) or die(mysql_error());
				$form['oamount'] = 0;
			}
			if ($con->sql_affectedrows()>0)
			{
				if ($id <= 0) $id = $con->sql_nextid();
				$msg[] = $config["arms_currency"]["symbol"]. " " .$form['oamount']." to " . $config["arms_currency"]["symbol"]. " " . $amt;
				$update++;
			}
		}

		$msg = join(",",$msg);
		$msg .= " (ID: ".$id.", Branch : ".$this->branch_id.", Date: ".$_REQUEST['date'].", Counter ID: ".$_REQUEST['counter_id'].")";

		if ($update > 0)
		{
			log_br($sessioninfo['id'], 'Counter Collection', "", "Change Advance $msg");
			header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		}
		else
		header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
	}

	function change_payment_type(){
		global $con, $smarty, $config, $LANG, $pos_config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");
		
		if($config['counter_collection_split_counter_by_cashier']){
			$xtra_filter = " and p.cashier_id=".mi($_REQUEST['cashier_id']);
		}
		
		// get the pos list which adjustment has been changed
		$rs = $con->sql_query("select p.id, p.receipt_no
                              from pos p
                              left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
                              where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and pt.adjust = 1  and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e']).$xtra_filter."
                              group by p.id");
		while ($r = $con->sql_fetchrow($rs)) {
			$pos_id[] = $r['id'];
            $receipt_no[] = $r['receipt_no'];
		}
		$con->sql_freeresult($rs);
        
        if($receipt_no) $receipt_no = join(', ', $receipt_no);
        
        $payment_type = $foreign_currency_list = array();
        foreach($this->normal_payment_type as $pt) {
            $payment_type[$pt] = $pt;
        }
        
        foreach($pos_config['credit_card'] as $cc) {
            $credit_card[] = $cc;
        }
		
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $curr_code=>$curr_info){
				if(!$curr_info['active']) continue;
				if(!in_array($curr_code, $foreign_currency_list))  $foreign_currency_list[$curr_code] = $curr_code;
			}
		}
        
        if($receipt_no) {
            $con->sql_query("select p.id, pt.type from pos p
                            left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
                            where p.cancel_status = 0 and pt.type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount') and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.receipt_no in ($receipt_no) and pt.adjust = 0 and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e'])."
                            order by p.end_time");
            
            if ($con->sql_numrows() > 0) {
                while($r = $con->sql_fetchrow()) {
					if($config['foreign_currency'][$r['type']]){
						if(!isset($foreign_currency_list[$r['type']])) $foreign_currency_list[$r['type']] = $r['type'];
					}elseif(preg_match("/^ewallet_/i", $r['type'])){
						// do nothing cause doesn't allow user to select ewallet as payment type
					}elseif(!in_array($r['type'], $payment_type) && !in_array($r['type'], $credit_card)){
						$payment_type[$r['type']] = $r['type'];
					}
                }
                $con->sql_freeresult();
            }
        }
        asort($payment_type);
        if($foreign_currency_list) asort($foreign_currency_list);
        $pos_config['payment_type'] = $payment_type;
        
		if ($pos_id) {
			// loop the pos list
            $receipt_no = array();
			foreach($pos_id as $pid) {
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust
                                from pos p
                                left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
                                where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.id = ".mi($pid)." and changed = 1
                                order by pt.id");
				while($r = $con->sql_fetchassoc()) {
					$items[$r['id']][] = $r;
                    $receipt_no[$r['id']] = $r['receipt_no'];
				}
				$con->sql_freeresult();
				
				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust
                                from pos p
                                left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
                                where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.id = ".mi($pid)." and adjust = 1
                                order by pt.id");
				while($r = $con->sql_fetchassoc()) {
					$oitems[$r['id']][] = $r;
				}
				$con->sql_freeresult();
			}
		}

		$smarty->assign('PAGE_TITLE', 'Change Payment Type');
		$smarty->assign("all_items", $items);
        $smarty->assign("receipt_array", $receipt_no);
		$smarty->assign("oitems", $oitems);
        $smarty->assign("pos_config", $pos_config);
        $smarty->assign("foreign_currency_list", $foreign_currency_list);
        
		$smarty->display('counter_collection.change_payment.tpl');
        
//        foreach($pos_config['issuer_identifier'] as $ii)
//		{
//			$cc[$ii[0]] = 1;
//		}
//
//		$cash_credit = array_keys($cc);
//		
//		// load payment type from pos settings
//		$q1 = $con->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
//		
//		$ps_info = $con->sql_fetchrow($q1);
//		$ps_payment_type = unserialize($ps_info['setting_value']);
//		$con->sql_freeresult($q1);
//
//		$cash_credit[] = 'Cash';
//		if($ps_payment_type){
//			foreach($ps_payment_type as $ptype=>$val){
//				if(!$val) continue;
//				$ptype = ucwords(str_replace("_", " ",$ptype));
//				
//				if(in_array($ptype, $cash_credit) || $ptype == "Credit Card" || $ptype == "Coupon" || $ptype == "Voucher")  continue;
//				$cash_credit[] = $ptype;
//			}
//		}else{
//			$cash_credit[] = 'Check';
//		}
//        $cash_credit[] = 'Others';
//
//		$coupon_voucher[] = 'Coupon';
//		$coupon_voucher[] = 'Voucher';
//
//		$extra_payment_type = array();
//		if($config['counter_collection_extra_payment_type']){
//			foreach($config['counter_collection_extra_payment_type'] as $ptype){
//				$ori_ptype = $ptype;
//				$ptype = ucwords(strtolower($ptype));
//				
//				// store for available payment type, but not always in use
//				if(in_array($ptype, $this->normal_payment_type))	$extra_payment_type[] = $ptype;
//			}
//		}
//		
//		if($config['counter_collection_split_counter_by_cashier']){
//			$xtra_filter = " and p.cashier_id=".mi($_REQUEST['cashier_id']);
//		}
//		
//		// get the receipt list which got change payment type
//		$rs = $con->sql_query("select p.receipt_no from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and pt.adjust = 1  and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e']).$xtra_filter." group by receipt_no");
//		while ($r = $con->sql_fetchrow($rs))
//		{
//			$receipt_no[] = $r['receipt_no'];
//		}
//		$con->sql_freeresult($rs);
//
//		if ($receipt_no)
//		{
//			// loop the receipt list
//			foreach($receipt_no as $rno)
//			{
//				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and changed = 1");
//
//				while($r = $con->sql_fetchrow())
//				{
//					$items[$r['receipt_no']][] = $r;
//					
//					if($r['type'] != 'Rounding' && $r['type'] != $mm_discount_col_value && $r['type'] != 'Discount' && in_array($r['type'], $pos_config['payment_type']) && !in_array($ptype, $this->normal_payment_type) && !in_array($r['type'], $extra_payment_type)){
//						$extra_payment_type[] = $r['type'];
//					}
//				}
//				$con->sql_freeresult();
//				
//				$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($rno)." and adjust = 1");
//
//				while($r = $con->sql_fetchrow())
//				{
//					$oitems[$r['receipt_no']][] = $r;
//					
//					if($r['type'] != 'Rounding' && $r['type'] != $mm_discount_col_value && $r['type'] != 'Discount' && in_array($r['type'], $pos_config['payment_type']) && !in_array($ptype, $this->normal_payment_type) && !in_array($r['type'], $extra_payment_type)){
//						$extra_payment_type[] = $r['type'];
//					}
//				}
//				$con->sql_freeresult();
//			}
//		}
//
//		$smarty->assign('PAGE_TITLE','Change Payment Type');
//		$smarty->assign("all_items",$items);
//		$smarty->assign("oitems",$oitems);
//
//		foreach ($pos_config['payment_type'] as $pt)
//		{
//			if ($pt != 'Credit Cards')
//			$payment_type[] = $pt;
//		}
//
//		$ptcheck = array_keys($cc);
//		$ptcheck[] = 'Others';
//		$ptcheck[] = 'Coupon';
//		$ptcheck[] = 'Voucher';
//
//		$payment_type = array_merge($payment_type,array_keys($cc));
//		$payment_type[] = 'Others';
//
//		//print_r($extra_payment_type);
//		$smarty->assign("credit_cards", $ptcheck);
//		$smarty->assign("coupon_voucher",$coupon_voucher);
//		$smarty->assign("cc", $cash_credit);
//		$smarty->assign("payment_type",$payment_type);
//		$smarty->assign("extra_payment_type",$extra_payment_type);
//
//		$smarty->display('counter_collection.change_payment.tpl');
	}

	function save_change_payment(){
		global $con, $smarty, $LANG, $sessioninfo, $config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$form = $_REQUEST;
		
        $fitem = $ditem = $pos_array = $fpay = $dpay = $deleted_pospayment = array();
        if($form['type']) {
            //rearrange from data structure
            foreach($form['type'] as $pos_id => $pt) {
                foreach($pt as $pospay_id => $pay_type) {
                    $fitem[$pos_id][$pospay_id]['type'] = $form['type'][$pos_id][$pospay_id];
                    $fitem[$pos_id][$pospay_id]['remark'] = $form['remark'][$pos_id][$pospay_id];
                    $fitem[$pos_id][$pospay_id]['amount'] = $form['amount'][$pos_id][$pospay_id];
                    $fitem[$pos_id][$pospay_id]['receipt_no'] = $form['receipt_no'][$pos_id];
                    if(!in_array($pos_id, $pos_array))   $pos_array[] = $pos_id;
                    if(!in_array($pospay_id, $fpay) && (int)$pospay_id < 1000)   $fpay[$pos_id][] = $pospay_id;
                }
            }
            
            //get data from database
            foreach($pos_array as $pos_id) {
                $con->sql_query("select * from pos_payment
                                where changed = 1 and pos_id = $pos_id and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($form['date'])." and type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount') order by id");
                $changed = $con->sql_numrows();
                if($changed > 0) {
                    while($r = $con->sql_fetchassoc()) {
                        $ditem[$r['pos_id']][$r['id']]['type'] = $r['type'];
                        $ditem[$r['pos_id']][$r['id']]['remark'] = $r['remark'];
                        $ditem[$r['pos_id']][$r['id']]['amount'] = $r['amount'];
                        if(!in_array($r['id'], $dpay))   $dpay[$pos_id][] = $r['id'];
                        $new = 0;
                    }
                    $con->sql_freeresult();
                }else{
                    $con->sql_query("select * from pos_payment
                                    where pos_id = $pos_id and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($form['date'])." and type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount') order by id");
                    while($r = $con->sql_fetchassoc()) {
                        $ditem[$r['pos_id']][$r['id']]['type'] = $r['type'];
                        $ditem[$r['pos_id']][$r['id']]['remark'] = $r['remark'];
                        $ditem[$r['pos_id']][$r['id']]['amount'] = $r['amount'];
                        if(!in_array($r['id'], $dpay))   $dpay[$pos_id][] = $r['id'];
                        $new = 1;
                    }
                    $con->sql_freeresult();    
                }
            }
            
            //find deleted pos payment
            foreach($dpay as $key => $val) {
                $deleted_pospayment[$key] = array_diff($dpay[$key], $fpay[$key]);    
            }
            
            $update = 0;
            $common_log = "Branch ID: ".$this->branch_id.", Date: ".$form['date'].", Counter ID: ".$form['counter_id'];
            foreach($fitem as $pos_id => $i) {
                $diff = 0;
                $edit_log = array();
                $amount_tender = $changeable = 0;
                //compare form and database
                foreach($i as $pospay_id => $item) {
                    $child_log = array();
                    //add new payment type when pos payment id is more than 999
                    if((int)$pospay_id > 999) {
                        $child_log[] = ": ".$item['type']."(".number_format($item['amount'], 2).") ".($item['remark']?", Remark: ".ms($item['remark']):"");
                        $diff = 1;
                    }else {
                        if($item['type'] != $ditem[$pos_id][$pospay_id]['type'] || $item['amount'] != $ditem[$pos_id][$pospay_id]['amount']) {
                            $child_log[] = "from ".$ditem[$pos_id][$pospay_id]['type']." (".number_format($ditem[$pos_id][$pospay_id]['amount'], 2).") to ".$item['type']." (".number_format($item['amount'], 2).")";
                            $diff = 1;
                        }
                    
                        if($item['remark'] != $ditem[$pos_id][$pospay_id]['remark']) {
                            $child_log[] = "Remark from ".(!$ditem[$pos_id][$pospay_id]['remark']?"' '":ms($ditem[$pos_id][$pospay_id]['remark']))." to ".ms($item['remark']);
                            $diff = 1;
                        }
                    }
                    
                    if($deleted_pospayment[$pos_id]) {
                        $diff = 1;
                    }
                    
                    if($child_log) {
                        $create_log[$pospay_id] = join(', ', $child_log);
                        $edit_log[$pospay_id] =  "(POS Payment ID: $pospay_id - ".$create_log[$pospay_id].")";
                    }
                    if($item['type'] == 'Cash') $changeable = 1;
					
					// check if found it is foreign currency payment
					if(isset($config['foreign_currency'][$item['type']])){
						$currency_arr = pp_is_currency($item['remark'], $item['amount']);
						
						if($currency_arr['is_currency']){   // it is foreign currency
							$currency_amt = $currency_arr['currency_amt'];
							$currency_rate = $currency_arr['currency_rate'];
						}
						$item['amount'] = round($currency_arr['rm_amt'], 2);
					}
					
                    $amount_tender += $item['amount'];
                    
                }
                
                //update pos payment
                if($diff && $new) {
                    $con->sql_query("update pos_payment set adjust = 1 where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($form['date'])." and type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount')");
                    $log = array();
                    foreach($i as $pospay_id => $item) {
                        $item['branch_id'] = $this->branch_id;
                        $item['counter_id'] = $form['counter_id'];
                        $item['pos_id'] = $pos_id;
                        $item['date'] = $form['date'];
                        $item['changed'] = 1;
                        
                        if((int)$pospay_id > 999 || !in_array($pospay_id, $deleted_pospayment[$pos_id])) {
                            $con->sql_query("select max(id) as id from pos_payment where branch_id = ".mi($item['branch_id'])." and counter_id = ".mi($item['counter_id'])." and date = ".ms($item['date'])." group by date");
                            $r = $con->sql_fetchrow();
                            $con->sql_freeresult();
                            $item['id'] = $r['id']+1;
                            $con->sql_query("insert into pos_payment ".mysql_insert_by_field($item, array('branch_id','counter_id','id','pos_id','date','type','remark','amount','changed')));
                            
                            if($create_log[$pospay_id]) {
                                if((int)$pospay_id > 999)   $log[] = "(POS Payment ID ".$item['id']." added".$create_log[$pospay_id].")";
                                else                        $log[] = "(POS Payment ID changed from $pospay_id to ".$item['id'].", ".$create_log[$pospay_id].")";
                            }else {
                                $log[] = "(POS Payment ID changed from $pospay_id to ".$item['id'].")";
                            }
                        }
                    }
                    if($deleted_pospayment[$pos_id]) {
                        foreach($deleted_pospayment[$pos_id] as $dppid)
                        $log[] = "(POS Payment ID $dppid removed)";
                    }                    
                    log_br($sessioninfo['id'], 'Counter Collection', $pos_id, "$common_log, POS ID: $pos_id, Changes: #".$item['receipt_no']." - ".join(', ', $log));
                    $update = 1;
                }elseif($diff && !$new) {
                    foreach($i as $pospay_id => $item) {
                        if((int)$pospay_id > 999) {
                            $item['branch_id'] = $this->branch_id;
                            $item['counter_id'] = $form['counter_id'];
                            $item['pos_id'] = $pos_id;
                            $item['date'] = $form['date'];
                            $item['changed'] = 1;
                    
                            $con->sql_query("select max(id) as id from pos_payment where branch_id = ".mi($item['branch_id'])." and counter_id = ".mi($item['counter_id'])." and date = ".ms($item['date'])." group by date");
                            $r = $con->sql_fetchrow();
                            $con->sql_freeresult();
                            $item['id'] = $r['id']+1;
                            $con->sql_query("insert into pos_payment ".mysql_insert_by_field($item, array('branch_id','counter_id','id','pos_id','date','type','remark','amount','changed')));
                            $edit_log[$pospay_id] = "(POS Payment ID ".$item['id']." added".$create_log[$pospay_id].")";
                        }else {
                            $con->sql_query("update pos_payment set".mysql_update_by_field($item, array('type','remark','amount'))." where changed = 1 and pos_id = ".mi($pos_id)." and id = ".mi($pospay_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and date = ".ms($form['date'])." and type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount')");
                        }
                    }
                    
                    if($deleted_pospayment[$pos_id]) {
                        foreach($deleted_pospayment[$pos_id] as $dpid)
                            $con->sql_query("delete from pos_payment where changed = 1 and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and pos_id = ".mi($pos_id)." and date = ".ms($form['date'])." and id = $dpid");
                            $edit_log[$pospay_id] = "(POS Payment ID $dpid removed)";
                    }
                        
                    if($edit_log)    $log = join(', ', $edit_log);
                    log_br($sessioninfo['id'], 'Counter Collection', $pos_id, "$common_log, POS ID: $pos_id, Changes: #".$item['receipt_no']." - $log");
                    $update = 1;
                }
                
                //find miss and match discount and discount amount
                $additional_amount = $pos_amount = $cash = 0;
                
                $con->sql_query("select amount from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and pos_id = ".mi($pos_id)." and date = ".ms($form['date'])."and type = 'Cash' and adjust=0");
                $r = $con->sql_fetchassoc();
                $cash = $r['amount'];
                $con->sql_freeresult();
                
                $con->sql_query("select amount, remark, type from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and pos_id = ".mi($pos_id)." and date = ".ms($form['date'])." and type in ('Mix & Match Total Disc', 'Discount')");
                while($r = $con->sql_fetchassoc())	$additional_amount += $r['amount'];
                $con->sql_freeresult();
                $amount_tender += $additional_amount;
                
                $con->sql_query("select amount, service_charges from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and id = ".mi($pos_id)." and date = ".ms($form['date']));
                while($r = $con->sql_fetchassoc())  $pos_amount += ($r['amount'] + $r['service_charges']);
                $con->sql_freeresult();
                
                if($changeable) {
                    $amount_change = $amount_tender - $pos_amount;
                    if($amount_change < 0)  $amount_change = 0;
                    elseif($amount_change >= $cash)  $amount_change = $cash;
                }else   $amount_change = 0;
                
                //update amount tender
                $con->sql_query("update pos set amount_tender = ".mf($amount_tender).", amount_change = ".mf($amount_change)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($form['counter_id'])." and id = ".mi($pos_id)." and date = ".ms($form['date']));
            }
            
            if($update) header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
            else        header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
        }else{
            header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
        }
        
		///*
		//echo '<pre>';
		//print_r($form);
		//echo '</pre>';
		//exit;
		//*/
		//
		//$update = 0;
		//if ($form['type'])
		//{
		//	foreach($form['type'] as $receipt_no => $t)
		//	{
		//
		//		$items = array();
		//		$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust 
		//		from pos p 
		//		left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id 
		//		where p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cancel_status <> 1 and p.receipt_no = ".mi($receipt_no)." and changed <> 1 and pt.type <> 'Rounding'");
		//
		//		while ($r = $con->sql_fetchrow())
		//		{
		//			$pos_id = mi($r['id']);
		//			$oitems += $r['payment_amount'];
		//		}
		//		
		//		$all_trans[$pos_id] = 1;
		//
		//		foreach($t as $idx => $ty)
		//		{
		//			$item['type'] = $ty;
		//			$item['remark'] = $_REQUEST['remark'][$receipt_no][$idx];
		//			$item['amount'] = $_REQUEST['amount'][$receipt_no][$idx];
		//			$ind_ty[] = "$ty(".$item['amount'].")";
		//
		//			//$con->sql_query("update pos_payment set amount = ".mf($item['amount'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($idx)." and pos_id=$pos_id");
		//
		//			$nitem += $item['amount'];
		//			$items[] = $item;
		//		}
		//		$ty_all[] = "#$receipt_no - ".join(', ',$ind_ty);
		//		$ind_ty = array();
		//		
		//		//check is amount more than original amount
		//		if (floatval($nitem) > floatval($oitems))
		//		{
		//			header("Location: /counter_collection.php?a=change_payment_type&cashier_id=".$form['cashier_id']."&counter_id=".$form['counter_id']."&date=".$form['date']."&s=".$form['s']."&e=".$form['e']."&msg=".urlencode("Save Error: Receipt $receipt_no Total amount (".number_format($nitem,2).") cannot more than original amount (".number_format($oitems,2).")"));
		//			exit;
		//		}
		//		
		//		$con->sql_query("delete from pos_payment where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and changed = 1 and type <> 'Cancel'");
		//		$con->sql_query("update pos_payment set adjust = 1 where pos_id = ".mi($pos_id)." and branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and type <> 'Cancel' and type <> 'Rounding'");
		//		foreach ($items as $item)
		//		{
		//			$item['branch_id'] = $this->branch_id;
		//			$item['counter_id'] = $_REQUEST['counter_id'];
		//			$item['pos_id'] = $pos_id;
		//			$item['date'] = $_REQUEST['date'];
		//			$item['changed'] = 1;
		//
		//
		//			$con->sql_query("select max(id) as id from pos_payment where branch_id = ".mi($item['branch_id'])." and counter_id = ".mi($item['counter_id'])." and date = ".ms($item['date'])." group by date");
		//			$r = $con->sql_fetchrow();
		//			$item['id'] = $r['id']+1;
		//			$con->sql_query("insert into pos_payment ".mysql_insert_by_field($item,array('branch_id','counter_id','id','pos_id','date','type','remark','amount','changed')));
		//			$update++;
		//		}
		//
		//		$amt = 0;
		//
		//		$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and pos_id = ".mi($pos_id)." and adjust = 1 and type <> 'Rounding'");
		//
		//		while($r = $con->sql_fetchrow())
		//		{
		//			$amt += $r['amount'];
		//		}
		//		$con->sql_query("select * from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
		//		$pos_header = $con->sql_fetchrow();
		//
		//		$con->sql_query("update pos set amount_tender = ".mf($amt).", amount_change = ".floatval($amt-$pos_header['amount'])." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($pos_id));
		//	}
		//
		//}
		//if  (is_array($all_trans)) {
		//	$all_trans  = join(",", array_keys($all_trans));
		//	$all_trans .= ', Branch ID: '.$this->branch_id.', Date: '.$_REQUEST['date'].', Counter ID: '.$_REQUEST['counter_id'].', Change To: '.join(', ',$ty_all);
		//}
		//log_br($sessioninfo['id'], 'Counter Collection', "", "Change Payment Type (Pos ID: $all_trans)");
		//$ty_all = array();
		//
		//if ($update > 0)
		//header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_UPDATED']));
		//else
		//header("Location: /counter_collection.php?date_select=".$form['date']."&msg=".urlencode($LANG['COUNTER_COLLECTION_NOT_UPDATED']));
 	}

 	function ajax_add_receipt_row(){
		global $con, $smarty, $pos_config, $LANG, $config;
        
        $payment_type = $credit_card = $foreign_currency_list = array();
        foreach($this->normal_payment_type as $pt) {
            $payment_type[$pt] = $pt;
        }
         foreach($pos_config['credit_card'] as $cc) {
            $credit_card[] = $cc;
        }
		
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $curr_code=>$curr_info){
				if(!$curr_info['active']) continue;
				if(!in_array($curr_code, $foreign_currency_list))  $foreign_currency_list[$curr_code] = $curr_code;
			}
		}
		
		if (preg_match("/^[1-9][0-9]*$/", $_REQUEST['receipt_no'])){
			$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust
							from pos p
							left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
							where p.cancel_status = 0 and pt.type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount')
							and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.receipt_no = ". mi($_REQUEST['receipt_no'])." and pt.adjust = 0 and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e'])."
							order by p.end_time");
							
			if ($con->sql_numrows() > 0) {
				while($r = $con->sql_fetchrow()) {
					$pos_id = $r['id'];
					$items[] = $r;
					
					if($config['foreign_currency'][$r['type']]){
						if(!isset($foreign_currency_list[$r['type']])) $foreign_currency_list[$r['type']] = $r['type'];
					}elseif(preg_match("/^ewallet_/i", $r['type'])){
						// do nothing cause doesn't allow user to select ewallet as payment type
					}elseif(!in_array($r['type'], $payment_type) && !in_array($r['type'], $credit_card)){
						$payment_type[$r['type']] = $r['type'];
					}
				}
				$con->sql_freeresult();
				
				asort($payment_type);
				if($foreign_currency_list) asort($foreign_currency_list);
				$pos_config['payment_type'] = $payment_type;
				
				$smarty->assign("pos_id", $pos_id);
				$smarty->assign("receipt_no", $_REQUEST['receipt_no']);
				$smarty->assign("items", $items);
				$smarty->assign("pos_config", $pos_config);
				$smarty->assign("foreign_currency_list", $foreign_currency_list);
				print $smarty->fetch('counter_collection.change_payment.row.tpl');
			}else{
				print $LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
			}
		}else{
			print $LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
		}
        
		//$extra_payment_type = $cash_credit = $coupon_voucher = array();
		//if($config['counter_collection_extra_payment_type']) {
		//	foreach($config['counter_collection_extra_payment_type'] as $ptype) {
		//		$ori_ptype = $ptype;
		//		$ptype = ucwords(strtolower($ptype));
		//		
		//		// store for available payment type, but not always in use
		//		if(in_array($ptype, $this->normal_payment_type))	$extra_payment_type[] = $ptype;
		//	}
		//}
        //
		//foreach ($pos_config['payment_type'] as $pt) {
		//	if ($pt != 'Credit Cards')
		//	$payment_type[] = $pt;
		//}
        //
		//foreach($pos_config['issuer_identifier'] as $ii) {
		//	$cc[$ii[0]] = 1;
		//}
		//$ptcheck = array_keys($cc);
		//$ptcheck[] = 'Others';
		//$ptcheck[] = 'Coupon';
		//$ptcheck[] = 'Voucher';
		//$payment_type = array_merge($payment_type,array_keys($cc));
		//
		//$payment_type[] = 'Others';
		//$cash_credit = array_keys($cc);
        //
		//// load payment type from pos settings
		//$q1 = $con->sql_query("select * from pos_settings where setting_name = 'payment_type' and branch_id = ".mi($this->branch_id));
		//
		//$ps_info = $con->sql_fetchrow($q1);
		//$ps_payment_type = unserialize($ps_info['setting_value']);
		//$con->sql_freeresult($q1);
        //
		//$cash_credit[] = 'Cash';
		//if($ps_payment_type){
		//	foreach($ps_payment_type as $ptype=>$val){
		//		if(!$val) continue;
		//		$ptype = ucwords(str_replace("_", " ",$ptype));
		//		
		//		if(in_array($ptype, $cash_credit) || $ptype == "Credit Card" || $ptype == "Coupon" || $ptype == "Voucher")  continue;
		//		$cash_credit[] = $ptype;
		//	}
		//}else{
		//	$cash_credit[] = 'Check';
		//	$cash_credit[] = "Debit";
		//}
		//$cash_credit[] = 'Others';
		//
		//$coupon_voucher[] = 'Coupon';
		//$coupon_voucher[] = 'Voucher';
        //
		//$smarty->assign("coupon_voucher",$coupon_voucher);
		//$smarty->assign("cc", $cash_credit);
		//$smarty->assign("payment_type",$payment_type);
		//$smarty->assign("credit_cards", $ptcheck);
        //
        //$con->sql_query("select p.*, pt.type, pt.amount as payment_amount, pt.remark, pt.id as payment_id, pt.changed, pt.adjust
        //                from pos p
        //                left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
        //                where p.cancel_status = 0 and pt.type <> 'Cancel' and pt.type <> 'Rounding' and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pt.adjust = 0 and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e'])."
        //                order by p.end_time");
        //if ($con->sql_numrows()>0) {
        //    while($r = $con->sql_fetchrow()) {
        //        $tmp_r[] = $r;
        //        if($r['type'] != 'Rounding' && $r['type'] != $mm_discount_col_value && $r['type'] != 'Discount' && in_array($r['type'], $pos_config['payment_type']) && !in_array($ptype, $this->normal_payment_type) && !in_array($r['type'], $extra_payment_type)){
        //            $extra_payment_type[] = $r['type'];
        //            }
        //    }
        //    $con->sql_freeresult();
        //    
        //    //insert discount and mix&match first
        //    foreach($tmp_r as $key=>$val) {
        //        if($val['type'] == 'Discount'){
        //            $items[] = $val;
        //            $oitems[$item['receipt_no']][]=$val;
        //        }
        //    }
        //    
        //    foreach($tmp_r as $key=>$val) {
        //        if($val['type'] == 'Mix & Match Total Disc'){
        //            $items[] = $val;
        //            $oitems[$item['receipt_no']][]=$val;
        //        }
        //    }
        //    
        //    //insert others payment type
        //    foreach($tmp_r as $key=>$val) {
        //        if($val['type'] != 'Mix & Match Total Disc' && $val['type'] != 'Discount'){
        //            $items[] = $val;
        //            $oitems[$item['receipt_no']][]=$val;
        //        }
        //    }
        //    
        //    $smarty->assign("receipt_no",$_REQUEST['receipt_no']);
        //    $smarty->assign("items",$items);
        //    $smarty->assign("oitems",$oitems);
        //    $smarty->assign("extra_payment_type",$extra_payment_type);
        //    print $smarty->fetch('counter_collection.change_payment.row.tpl');
        //} else
        //    print $LANG['COUNTER_COLLECTION_INVAILD_RECEIPT'];
	}

	function change_x(){
		global $con,$smarty, $LANG, $pos_config, $config, $sessioninfo;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$con->sql_query("select * from pos_cash_domination where date = ".ms($_REQUEST['date'])." and branch_id =".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();
		$item['data'] = unserialize($item['data']);
		$item['odata'] = unserialize($item['odata']);
		$item['curr_rate'] = unserialize($item['curr_rate']);
		$item['ocurr_rate'] = unserialize($item['ocurr_rate']);
		$extra_cash_denom_type = array();
		
		if (isset($item['data']['Cheque']))
		{
			$item['data']['Check'] = $item['data']['Cheque'];
			unset($item['data']['Cheque']);
		}

		if (isset($item['odata']['Cheque']))
		{
			$item['odata']['Check'] = $item['odata']['Cheque'];
			unset($item['odata']['Cheque']);
		}
		
		// check if got these cash denom
		$arr_check_payment_type = array('Check', 'Voucher', 'Coupon');
		foreach($arr_check_payment_type as $ptype){
			if($item['data'][$ptype] || $item['odata'][$ptype]){
				if(!in_array($ptype, $this->normal_payment_type)){
					$this->normal_payment_type[] = $ptype;	
				}
			}			
		}
		
		// Credit Cards
		foreach($pos_config['credit_card'] as $ctype){
			if($item['data'][$ctype] || $item['odata'][$ctype]){
				if(!in_array('Credit Cards', $this->normal_payment_type)){
					$this->normal_payment_type[] = 'Credit Cards';
					break;	
				}
			}
		}
		
		if($config['counter_collection_extra_payment_type']){
			foreach($config['counter_collection_extra_payment_type'] as $ptype){
				$ori_ptype = $ptype;
				$ptype = ucwords(strtolower($ptype));
				
				if($item['data'][$ptype] || $item['odata'][$ptype]){
					if(!in_array($ptype, $this->normal_payment_type)){
						$this->normal_payment_type[] = $ptype;	
					}
				}
				
				if(in_array($ptype, $this->normal_payment_type)){
					$extra_cash_denom_type[] = $ptype;
				}
			}
		}
		
		// load foreign currency list
		$foreign_currency_list = array();
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $fc_code=>$fc_info){
				// skip if not active or already set in the list, and must not appear in original / amended data 
				if(!$fc_info['active'] && !isset($item['data'][$fc_code]) && !isset($item['odata'][$fc_code])) continue;
				
				$foreign_currency_list[$fc_code] = $fc_code;
			}
		}
				
		/*if($sessioninfo['id'] == 1){
			print_r($config['counter_collection_extra_payment_type']);
			print_r($this->normal_payment_type);
			print_r($item);
			print_r($extra_cash_denom_type);
		}*/	
		
		// eWallet
		$ewallet_list = array();
		$con->sql_query("select * from pos_settings where setting_name = 'ewallet_type' and branch_id = ".mi($this->branch_id));
		$ps = $con->sql_fetchrow();
		$ps_payment_type = unserialize($ps['setting_value']);
		$con->sql_freeresult();
		
		if($ps_payment_type){
			foreach($ps_payment_type as $ptype=>$val){
				if(!$val) continue;
				
				$ptype = 'ewallet_'.$ptype;
				if($ewallet_list[$ptype])  continue;
				$ewallet_list[$ptype] = $ptype;
			}
		}
		
		if($item['data']){
			foreach($item['data'] as $ptype => $v){
				if(preg_match('/^ewallet_/', $ptype) && !$ewallet_list[$ptype]){
					$ewallet_list[$ptype] = $ptype;
				}
			}
		}
		
		if($item['odata']){
			foreach($item['odata'] as $ptype => $v){
				if(preg_match('/^ewallet_/', $ptype) && !$ewallet_list[$ptype]){
					$ewallet_list[$ptype] = $ptype;
				}
			}
		}
		//print_r($ewallet_list);
		
		$smarty->assign('PAGE_TITLE','Counter Collection Change X-Figure');
		$smarty->assign('item',$item);
		$smarty->assign('normal_payment_type', $this->normal_payment_type);
		$smarty->assign('extra_cash_denom_type', $extra_cash_denom_type);
		$smarty->assign('foreign_currency_list', $foreign_currency_list);
		$smarty->assign('ewallet_list', $ewallet_list);
		$smarty->display('counter_collection.change_x.tpl');
	}

	function save_cash_domination(){
	
        global $con, $sessioninfo, $LANG, $config;

		if (!$this->is_approval) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'COUNTER COLLECTION Approval', BRANCH_CODE), "/index.php");

		$changes = $_REQUEST['data'];
		$rates = $_REQUEST['curr_rate'];
		$_REQUEST['data'] = serialize($_REQUEST['data']);
		$_REQUEST['curr_rate'] = serialize($_REQUEST['curr_rate']);
		$date_time = strtotime($_REQUEST['date']);
		
		$str_changes = array();
		foreach ($changes as $key => $value) {
			if (!empty($value)) $str_changes[] = "$key($value)";
		}
		
		$str_rates = array();
		foreach ($rates as $key => $value) {
			if (!empty($value)) $str_rates[] = "$key($value)";
		}

	if (intval($_REQUEST['id'])>0)
		{
			$q1 = $con->sql_query("select * from pos_cash_domination where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));

			if ($con->sql_numrows($q1)>0){
				$r = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				$upd = array();
				if ($r['odata'] == ''){
					$upd['odata'] = $r['data'];
					$upd['ocurr_rate'] = $r['curr_rate'];
				}
				if ($r['ocurr_rate'] == '') $upd['ocurr_rate'] = $r['curr_rate'];
				$upd['data'] = $_REQUEST['data'];
				$upd['curr_rate'] = $_REQUEST['curr_rate'];
				$upd['clear_drawer'] = $_REQUEST['clear_drawer'];

				$con->sql_query("update pos_cash_domination set ".mysql_update_by_field($upd)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." and id = ".mi($_REQUEST['id']));

				log_br($sessioninfo['id'], 'Counter Collection', mi($_REQUEST['id']), "Change Cash Denomination (ID: ".mi($_REQUEST['id']).", Branch ID: ".$this->branch_id.", Counter ID: ".$_REQUEST['counter_id'].", Date: ".$_REQUEST['date'].", Data: ".join(', ',$str_changes).", Rates: ".join(', ',$str_rates).", Total: ".number_format($_REQUEST['total_amount'],2).")");
				header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
			}
			else header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("Invalid X"));
		}else{
		    $counter_version = get_counter_version($this->branch_id, $_REQUEST['counter_id']);
		    
			$q1 = $con->sql_query("select  max(id) as id from pos_cash_domination where branch_id = ".$this->branch_id." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id']));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
   			$id = $r['id']+1;

   			$q1 = $con->sql_query("select  max(end_time) as e from pos where branch_id=".$this->branch_id." and date=".ms($_REQUEST['date'])." and counter_id=".mi($_REQUEST['counter_id'])." and cancel_status=0");
			$max_pos = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if($config['counter_collection_check_old_cash_domination_time'] && ($counter_version<109 || $date_time < $this->v109_time)){
			    $timestamp = date("Y-m-d H:i:s",1+strtotime($max_pos['e'])-(8*60*60));
			}else{
                $timestamp = date("Y-m-d H:i:s",1+strtotime($max_pos['e']));
			}
			
			$con->sql_query("insert into pos_cash_domination (branch_id,id,counter_id,user_id,data,timestamp,date,clear_drawer,curr_rate,is_from_backend) values (".$this->branch_id.",".mi($id).",".mi($_REQUEST['counter_id']).",".mi($sessioninfo['id']).",".ms($_REQUEST['data']).",".ms($timestamp).",".ms($_REQUEST['date']).",".mi($_REQUEST['clear_drawer']).",".ms($_REQUEST['curr_rate']).",1)");
			log_br($sessioninfo['id'], 'Counter Collection', mi($id), "Add Cash Denomination (ID: ".mi($id).", Branch ID: ".$this->branch_id.", Counter ID: ".$_REQUEST['counter_id'].", Date: ".$_REQUEST['date'].", Denom Time: $timestamp, Data: ".join(', ',$str_changes).", Rates: ".join(', ',$str_rates).", Total: ".number_format($_REQUEST['total_amount'],2).")");
			header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("X Updated"));
		}
	}
	
	function delete_cash_domination(){
        global $con, $sessioninfo;
		$con->sql_query("delete from pos_cash_domination where is_from_backend = 1 and branch_id = ".$this->branch_id." and id = ".mi($_REQUEST['id'])." and counter_id = ".mi($_REQUEST['counter_id'])." and date = ".ms($_REQUEST['date'])." limit 1");
		log_br($sessioninfo['id'], 'Counter Collection', mi($id), "Delete Cash Denomination (ID: ".mi($_REQUEST['id']).", Branch ID: ".$this->branch_id.", Counter ID: ".$_REQUEST['counter_id'].", Date: ".$_REQUEST['date'].")");
		header("Location: /counter_collection.php?date_select=".$_REQUEST['date']."&msg=".urlencode("Record deleted"));
		
	}

	function send_notification($date){
		global $con;

		$notify_users = array();
		foreach (preg_split("/\|/", $this->notify_users) as $kk)
		{
		    if ($kk) $notify_users[] = $kk;
		}

		foreach ($notify_users as $user)
		{
			send_pm($user, "Counter Collection Finalised for Branch:".BRANCH_CODE." Date: $date", "/counter_collection.php?a=view&date_select=$date&branch_id=".$this->branch_id);
		}

	}

	function ajax_change_cash_credit(){
		global $con,$pos_config;

		$is_cc = 0;
		$con->sql_query("select p.amount_change, pp.*
                        from pos p
                        left join pos_payment pp on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date = pp.date and p.id = pp.pos_id
                        where p.branch_id = ".mi($this->branch_id)." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.cashier_id = ".mi($_REQUEST['cashier_id'])." and p.receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));

		$item = $con->sql_fetchrow();

		if (in_array($_REQUEST['type'], $pos_config['credit_card']) || $_REQUEST['type'] == 'Credit Cards') $is_cc = 1;
//		$con->sql_query("select * from pos_payment where branch_id = ".mi($this->branch_id)." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and cashier_id = ".mi($_REQUEST['cashier_id'])." and receipt_no = ".mi($_REQUEST['receipt_no'])." and pp.id = ".mi($_REQUEST['id']));

		if ($item['type'] == 'Cash' && $is_cc && !$item['changed'])
			print round($item['amount'] - $item['amount_change'], 2);
		else
			print round($item['amount'], 2);
	}
    
	function view_tran_details(){
		global $con, $smarty;
		$smarty->assign('is_view_only', 1);
		$this->print_tran_details();
	}
	
	function print_tran_details(){
		global $con, $smarty;
		$smarty->assign('is_print', 1);
		$this->item_details();
	}
	
    function ajax_add_payment_type() {
        global $smarty, $con, $pos_config, $add_payment_count, $config;
        
        $payment_type = $foreign_currency_list = array();
        foreach($this->normal_payment_type as $pt) {
            $payment_type[$pt] = $pt;
        }
		
		if($config['foreign_currency']){
			foreach($config['foreign_currency'] as $curr_code=>$curr_info){
				if(!$curr_info['active']) continue;
				if(!in_array($curr_code, $foreign_currency_list))  $foreign_currency_list[$curr_code] = $curr_code;
			}
		}
       
        $con->sql_query("select p.id, pt.type, p.receipt_no from pos p
                        left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id
                        where p.cancel_status = 0 and pt.type not in ('Cancel', 'Rounding', 'Mix & Match Total Disc', 'Discount') and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.id = ".mi($_REQUEST['pos_id'])." and pt.adjust = 0 and p.end_time >= ".ms($_REQUEST['s'])." and p.end_time <= ".ms($_REQUEST['e'])."
                        order by p.end_time");
        
        if ($con->sql_numrows() > 0) {
			while($r = $con->sql_fetchrow()) {
                $pos_id = $r['id'];
                $receipt_no = $r['receipt_no'];
				
				if($config['foreign_currency'][$r['type']]){
					if(!isset($foreign_currency_list[$r['type']])) $foreign_currency_list[$r['type']] = $r['type'];
				}elseif(preg_match("/^ewallet_/i", $r['type'])){
					// do nothing cause doesn't allow user to select ewallet as payment type
				}elseif(!in_array($r['type'], $payment_type)){
					$payment_type[$r['type']] = $r['type'];
				}
			}
			$con->sql_freeresult();
        }  
        asort($payment_type);
        if($foreign_currency_list) asort($foreign_currency_list);
        $pos_config['payment_type'] = $payment_type;
        
        $item['id'] = $pos_id;
        $item['type'] = 'Cash';
        $item['receipt_no'] = $receipt_no;
        $item['payment_id'] = 1000 + mi($_REQUEST['pcount']) + 1;
        $smarty->assign('i', mi($_REQUEST['pindex']) + 1);
        $smarty->assign('item', $item);
        $smarty->assign('pos_config', $pos_config);
        $smarty->assign('foreign_currency_list', $foreign_currency_list);
        print $smarty->fetch('counter_collection.change_payment.row2.tpl');
    }
    
	//function ajax_add_coupon()
	//{
	//	global $con, $smarty, $pos_config,$LANG;
	//
	//	foreach ($pos_config['payment_type'] as $pt)
	//	{
	//		if ($pt != 'Credit Cards')
	//		$payment_type[] = $pt;
	//	}
	//	
	//	foreach($pos_config['issuer_identifier'] as $ii)
	//	{
	//		$cc[$ii[0]] = 1;
	//	}
	//	$ptcheck = array_keys($cc);
	//	$ptcheck[] = 'Others';
	//	$ptcheck[] = 'Coupon';
	//	$ptcheck[] = 'Voucher';
	//	$payment_type = array_merge($payment_type,array_keys($cc));
	//	
	//	$payment_type[] = 'Others';
	//	$cash_credit = array_keys($cc);
	//	$cash_credit[] = 'Cash';
	//	
	//	$coupon_voucher[] = 'Coupon';
	//	$coupon_voucher[] = 'Voucher';
	//	
	//	$con->sql_query("select count(pt.adjust) as count from pos p left join pos_payment pt on p.branch_id = pt.branch_id and p.counter_id = pt.counter_id and p.date = pt.date and p.id = pt.pos_id where pt.type <> 'Cancel' and p.branch_id = ".mi($this->branch_id)." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.date = ".ms($_REQUEST['date'])." and p.cashier_id = ".mi($_REQUEST['cashier_id'])." and pt.adjust = 1 group by adjust");
	//	$count = $con->sql_fetchrow();
	//	
	//	$smarty->assign('i',$count['count']+1);
	//	$item['receipt_no'] = $_REQUEST['receipt_no'];
	//	$item['type'] = 'Coupon';
	//	$smarty->assign("item", $item);
	//	$smarty->assign("coupon_voucher",$coupon_voucher);
	//	$smarty->assign("cc", $cash_credit);
	//	$smarty->assign("payment_type",$payment_type);
	//	$smarty->assign("credit_cards", $ptcheck);
	//	
	//	print $smarty->fetch('counter_collection.change_payment.row2.tpl');
	//}
	
	function ajax_finalize(){
		global $con;
		
		$this->is_ajax_finalize = true;
		$this->finalize();

		write_process_status('counter_collection','','', false, true);
		print "OK";
	}
	
	function ajax_revert_to_original() {
		global $con, $sessioninfo, $config;
        $payment_amt = $pos_amt = $changeable = 0;
        
		$con->sql_query("delete from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date'])." and changed = 1");
        
        $con->sql_query("select amount, remark, type from pos_payment where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date'])." and (adjust = 1 or type in ('Mix & Match Total Disc', 'Discount'))");
        while($r = $con->sql_fetchassoc()){
			// check if found it is foreign currency payment
			if(isset($config['foreign_currency'][$r['type']])){
				$currency_arr = pp_is_currency($r['remark'], $r['amount']);
				
				if($currency_arr['is_currency']){   // it is foreign currency
					$currency_amt = $currency_arr['currency_amt'];
					$currency_rate = $currency_arr['currency_rate'];
				}
				$amount = round($currency_arr['rm_amt'], 2);
			}else $amount = $r['amount'];
			
            $payment_amt += $amount;
            if($r['type'] == 'Cash') $changeable = 1;
        }
        $con->sql_freeresult();
        
        $con->sql_query("select amount, service_charges from pos where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date']));
        while($r = $con->sql_fetchassoc())   $pos_amt += ($r['amount'] + $r['service_charges']);
        $con->sql_freeresult();
        
        $con->sql_query("update pos set amount_tender = ".mf($payment_amt).", amount_change = ".mf($changeable?$payment_amt-$pos_amt:0)." where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date']));
		$con->sql_query("update pos_payment set adjust = 0 where branch_id = ".mi($this->branch_id)." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id = ".mi($_REQUEST['revert_pos_id'])." and date = ".ms($_REQUEST['date'])." and adjust = 1");
        
        log_br($sessioninfo['id'], 'Counter Collection', $_REQUEST['revert_pos_id'], "Branch ID: ".$this->branch_id.", Date: ".$_REQUEST['date'].", Counter ID: ".$_REQUEST['counter_id'].", POS ID: ".$_REQUEST['revert_pos_id']." reverted.");
        
		print 'OK'; 
		exit;
	}
    
	private function load_membership_receipt_info(){
		global $con, $smarty, $config, $sessioninfo;
		
		$date = $_REQUEST['date_select'];
		
		$this->mem_data = array();
		$bid = mi($sessioninfo['branch_id']);
		
		
		// get system amt
		$q_mr = $con->sql_query("select mr.* from membership_receipt mr where mr.branch_id=$bid and mr.timestamp between ".ms($date)." and ".ms($date.' 23:59:59')." order by mr.timestamp");

		while($r = $con->sql_fetchassoc($q_mr)){
			$this->mem_data['by_counter'][$r['counter_id']]['cash']['amt'] += round($r['amount'], 2);
			
			$this->mem_data['all_counter']['cash']['amt'] += round($r['amount'], 2);
		}
		$con->sql_freeresult($q_mr);
		
		// get user domination
		$q_mih = $con->sql_query("select * from membership_inventory_history where branch_id=$bid and timestamp between ".ms($date)." and ".ms($date.' 23:59:59')." order by counter_id, timestamp");
		$has_open = false;
		$open_amt = 0;
		$amt_get = 0;
		$last_counter_id = 0;
		
		while($r = $con->sql_fetchassoc($q_mih)){
			if($last_counter_id != $r['counter_id']){
				$has_open = false;
				$open_amt = 0;
				$amt_get = 0;
			}
			
			$r['inventory'] = unserialize($r['inventory']);
			
			if($r['type'] == 'OPEN'){
				$has_open = true;
				$open_amt = round($r['inventory']['COH'], 2);
			}else{
				if($r['type'] == 'CLOSE' && $has_open){
					$amt_get = round($r['inventory']['COH'] - $open_amt, 2);
					
					$this->mem_data['by_counter'][$r['counter_id']]['dom']['cash']['amt'] += $amt_get;
			
					$this->mem_data['all_counter']['dom']['cash']['amt'] += $amt_get;
			
					$has_open = false;
					$open_amt = 0;
					$amt_get = 0;
					
				}
			}
			// variance
			$this->mem_data['by_counter'][$r['counter_id']]['variance']['cash']['amt'] = $this->mem_data['by_counter'][$r['counter_id']]['dom']['cash']['amt'] - $this->mem_data['by_counter'][$r['counter_id']]['cash']['amt'];
			
			$last_counter_id = $r['counter_id'];
		}
		$con->sql_freeresult($q_mih);
		
		if($this->mem_data){
			foreach($this->mem_data['by_counter'] as $cid => $mem_data_r){
				$this->mem_data['all_counter']['variance']['cash']['amt'] += $mem_data_r['variance']['cash']['amt'];
			}
			
			// total added to pos cash
			
			// cash
			$this->mem_data['all_counter']['added_pos']['cash']['amt'] = $this->mem_data['all_counter']['cash']['amt'] + $this->total['payment_type']['Cash']['amt'];
			
			// collection
			$this->mem_data['all_counter']['added_pos']['dom']['cash']['amt'] = $this->mem_data['all_counter']['dom']['cash']['amt'] + ($this->total['total']['cash_advance']['amt']*-1) + $this->total['cash_domination']['Cash']['amt'];
			
			// variance
			$this->mem_data['all_counter']['added_pos']['variance']['cash']['amt'] = $this->mem_data['all_counter']['added_pos']['dom']['cash']['amt'] - $this->mem_data['all_counter']['added_pos']['cash']['amt'];
		}
		
		
		
		
		//print_r($this->mem_data);
		//print_r($this->total);
		$smarty->assign('mem_data', $this->mem_data);
	}
	
	private function load_invalid_member_info(){
		global $con, $smarty, $config, $sessioninfo;
		
		$date = $_REQUEST['date_select'];
		
		$this->invalid_mem_data = array();
		$bid = mi($sessioninfo['branch_id']);
		
		$q1 = $con->sql_query($sql = "select pos.* 
		from pos
		where pos.branch_id=$bid and pos.date=".ms($date)." and pos.member_no<>'' and pos.pos_more_info like '%is_invalid_member%' and pos.cancel_status = 0
		order by member_no");
		//print $sql;
		while($r = $con->sql_fetchassoc($q1)){
			$q4 = $con->sql_query("select id from membership_history where card_no = ".ms($r['member_no'])." limit 1");
			if (!$con->sql_numrows($q4)) {
				$this->invalid_mem_data[$r['member_no']]['receipt_list'][] = $r;	
			}
			$con->sql_freeresult($q4);
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('invalid_mem_data', $this->invalid_mem_data);
	}
	
	private function check_and_create_foreign_currency_list($payment_type, $currency_rate){
		global $config, $appCore;
		
		if(!is_array($this->foreign_currency_list) || !($this->foreign_currency_list[$payment_type])){
			/*if(!isset($this->currency_data[$payment_type]['currency_rate'][$currency_rate])){
				$this->currency_data[$payment_type]['currency_rate'][$currency_rate]= array();
			}*/
			
			if(!isset($this->foreign_currency_list[$payment_type])){
				$date = $_REQUEST['date_select'];
				if(!$date) $date = date("Y-m-d");
				
				// load the currency currently available from system
				if(!$currency_rate){
					$prms = array();
					$prms['branch_id'] = $this->branch_id;
					$prms['date'] = $date;
					$prms['code'] = $payment_type;
					$global_currency_rate = $appCore->posManager->loadForeignCurrencyRate($prms);
				}else $global_currency_rate = $currency_rate;
				
				$this->foreign_currency_list[$payment_type] = $global_currency_rate;
			}
		}
	}
	
	private function sort_denom_data($denom_id1, $denom_id2){
		if($denom_id1 == '')	return 1;
		if($denom_id2 == '')	return -1;
		
		return ($denom_id1 > $denom_id2) ? 1 : -1;
	}
	
	private function void_ewallet_payment($prms=array()){
		global $config, $appCore;
		
		if(!$prms) return;
	
		$url = $_SERVER['HTTP_HOST']."/api.ewallet.php";
	
		$s = curl_init();
		/*$headers = array(
			 'X_BRANCH_ID: '.$config['branch_id'],
			 'X_COUNTER_ID: '.$config['counter_id']
		 );*/
		 
		// config + branch_id + counter_id + date (YYYYMMDD) + receipt_ref_no
		$arms_sign = $appCore->posManager->generate_arms_sign($prms);
		 
		curl_setopt($s, CURLOPT_URL, $url);
		//curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		$data = array(
			'a' => 'void_payment',
			'branch_id' => $prms['branch_id'],
			'counter_id' => $prms['counter_id'],
			'ewallet_type' => $prms['ewallet_type'],
			'receipt_ref_no' => $prms['receipt_ref_no'],
			'transaction_date' => $prms['date'],
			'arms_sign' => $arms_sign
		);
		curl_setopt($s, CURLOPT_POSTFIELDS, $data);
		$ret = curl_exec($s);
		curl_close($s);
		
		return $ret;
	}
}

$CounterCollection = new CounterCollection ('Counter Collection','counter_collection3.tpl');
?>
