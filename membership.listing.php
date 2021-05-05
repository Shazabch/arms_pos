<?php
/*
27/8/2009 10:10:48 AM yinsee
fix bottom's pagination bug 
1/14/2010 9:49:12 AM edward
change privilege checking from MEMBERHSIP to MEMBERSHIP_EDIT

3/16/2010 5:50:32 PM Andy
- Add Expiry & Date filter for membership listing

8/11/2010 11:54:44 AM Justin
- Added a new date filter called "Birth Date".
- Allows user to filter by date from/to based on the member's DOB.
- Re-format DOB from integer become date format during display.

8/25/2010 5:44:55 PM Justin
- Added the Last Renew Branch and Purchase Branch columns.
- Allowed user to filter by Branch across different branches.

11/30/2010 5:55PM yinsee
- Add iSMS feature
- use phone_3 column from member database
- valid number is /01\d+{8,9}/ (only 10 or 11 digits)
- system auto add "6" prefix

3/14/2011 11:21:43 AM Justin
- Fixed the date filter that show empty data.

3/23/2011 3:21:28 PM Justin
- Added points update filter.

4/14/2011 5:34:06 PM Justin
- Added the "=" and double quote onto Card NO while export to excel format to prevent the excel auto numeric convertion.

4/25/2011 2:34:06 PM Justin
- Solved the card no not to include the "=" and double quote while it is print but not export.

6/24/2011 4:56:50 PM Andy
- Make all branch default sort by sequence, code.

6/29/2011 12:30:41 PM Andy
- Fix pagination javascipt. 
- Add can choose field to export excel.

7/6/2011 12:06:11 PM Andy
- Change split() to use explode()

7/19/2011 11:52:21 AM Justin
- Added the live sorting for all columns from template for membership listing and list printing.

8/17/2011 2:14:11 PM Justin
- Added array fields for export excel.

11/9/2011 6:25:43 PM Justin
- Added new filter "Age".
- Added to show new filter "Age" to show at print report and excel format.

11/10/2011 11:43:28 AM Andy
- Add checking config and privilege at server side.

11/14/2011 12:06:43 PM Justin
- Added new filter "Gender".
- Added to show new filter "Gender" to show at print report and excel format.

12/5/2011 11:16:32 AM Justin
- Removed the "last purchase branch" from query for no longer use.
- Fixed bug of when exporting excel file with filter "Expiry - No", system does not do so.
- Fixed bug when export or print membership records which sort by last renew or purchase branch, system did not response anything.
- Fixed bug of cannot retrieve the exact last renew branch during membership listing.

12/27/2011 yinsee
- auto detect and calculate accurate SMS costing, no limit

2/10/2012 5:04:23 PM Justin
- Added to pick up "Last Purchase Branch".

3/22/2012 10:24:43 AM Justin
- Fixed the SQL bugs that when filter member address, system shows sql error.

6/26/2012 2:48:12 PM Justin
- Added all possible fields into the array for excel file export.
- Added unserializes for some informations that being serialized for excel export purpose.
- Added few arrays that to store prefix information.

7/30/2012 4:08:34 PM Justin
- Enhanced to show extra info from config "membership_extra_info" while do excel export.

9/13/2012 4:28 PM Justin
- Enhanced to mark points as changed while cannot found card no from tmp table.

1/21/2013 3:36 PM Andy
- Fix membership listing always first time filter empty gender.
- Add can filter member type and staff type in membership listing.

1/29/2013 4:43 PM Justin
- Enhanced to show better screen after SMS sent.

3/21/2013 5:01 PM Justin
- Bug fixed on send sms.

11/15/2013 4:26 PM Justin
- Bug fixed on the hit maximum memory limits while export into excel.

12/26/2013 11:20 AM Justin
- Bug fixed on send sms to take off the dash (-) that causes sms cannot send to member.
- Enhanced the sms sent result to have more info such as how many numbers has been sent.
- Enhanced to take away the success count get from sms website since it is returning success result for 100 numbers.
- Enhanced to show failed info and also capture it into log.

12/30/2013 12:06 PM Justin
- Bug fixed on filters of terminated,blocked, verified and expiry shows incorrectly during exporting or printing.

1/16/2014 5:55 PM Justin
- Optimized the send SMS process.

2/7/2014 10:46 AM Andy
- Fix to only use cron to send sms when there are more than 100 members.

11/18/2015 5:00 PM Qiu Ying
- remove the config of membership export and print

05/30/2016 10:25 Edwin
- Bug fixed on sms target filter does not check member type and staff type.
- Rearrange code structure.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

12/4/2019 3:52 PM Andy
- Enhanced to can filter member by mobile registered yes/no and mobile register date from/to.
- Enhanced to check form_filter error.
- Enhanced to allow users to select which fields to display / print / export.
- Increase maintenance version checking to 429.

1/13/2020 3:58 PM William
- Enhanced to add new phone filter.

2/12/2020 11:09 AM William
- Enhanced to add new birthday month and birthday day filter.

2/13/2020 10:57 AM Andy
- Enhanced to prevent call multiple query which doesn't use.

9/23/2020 1:06 PM William
- Enhanced "Membership Listing" module able to filter 0 value.

02/17/2021 5:51 PM Rayleen
- Enhance to display only active branch in Branch Select Dropdown
- Enhanced to Export Member List in CSV File
- Add 'date' array in $available_field to determine which field is a date

02/18/2021 5:09 PM Rayleen
- Use fputcsv_eol() in export csv

02/22/2021 10:34 AM Rayleen
- Fix bug column not displaying correctly - apply_branch, marital_status, last_pucrhase_branch and verified_by
*/

include("include/common.php");
include("include/class.isms.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MEMBERSHIP_EDIT') && !privilege('MEMBERSHIP_ADD')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_EDIT', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '512M');
set_time_limit(0);
$maintenance->check(429);

$available_field = array(
	"card_no" => array('label'=>"Card No", 'default_tick'=>1), 
	"nric" => array('label'=>"NRIC", 'default_tick'=>1), 
	"name" => array('label'=>"Name", 'default_tick'=>1),
	"member_type" => array('label'=>"Membership Type"),
	"designation" => array('label'=>"Designation"),
	"gender" => array('label'=>"Gender", 'default_tick'=>1),
	"dob" => array('label'=>"D.O.B", 'default_tick'=>1,'date'=>1),
	"marital_status" => array('label'=>"Marital Status"), 
	"race" => array('label'=>"Race", 'default_tick'=>1), 
	"national" => array('label'=>"National", 'default_tick'=>1),
	"education_level" => array('label'=>"Education Level"), 
	"preferred_lang" => array('label'=>"Preferred Language"), 
	"address" => array('label'=>"Address"), 
	"postcode" => array('label'=>"Post Code"), 
	"city" => array('label'=>"City"), 
	"state" => array('label'=>"State"), 
	"phone_1" => array('label'=>"Phone (Home)"),
	"phone_2" => array('label'=>"Phone (Office)"),
	"phone_3" => array('label'=>"Phone (Mobile)"),
	"email" => array('label'=>"Email"),
	"apply_branch" => array('label'=>"Apply Branch", 'default_tick'=>1),
	"last_renew_branch" => array('label'=>"Last Renew (Issue Branch)", 'default_tick'=>1),
	"last_purchase_branch" => array('label'=>"Last Purchase Branch", 'default_tick'=>1),
	"points" => array('label'=>"Points", 'default_tick'=>1),
	"points_update" => array('label'=>"Points Update", 'default_tick'=>1,'date'=>1),
	"issue_date" => array('label'=>"Issue Date", 'default_tick'=>1,'date'=>1),
	"next_expiry_date" => array('label'=>"Expiry Date", 'default_tick'=>1,'date'=>1),
	"terminated_date" => array('label'=>"Terminated Date", 'default_tick'=>1,'date'=>1),
	"blocked_date" => array('label'=>"Blocked Date", 'default_tick'=>1, 'date'=>1),
	"verified_by" => array('label'=>"Verified By", 'default_tick'=>1),
	"occupation" => array('label'=>"Occupation"),
	"income" => array('label'=>"Income"),
	"mobile_registered_time" => array('label'=>"Mobile Register Time",'date'=>1),
	//"credit_card" => array('label'=>"Credit Card"),
);

//if($config['membership_data_use_custom_field']['recruit_by']) $available_field['recruit_by'] = array('label'=>"Recruit By");
if($config['membership_data_use_custom_field']['principal_card']) $available_field['parent_nric'] = array('label'=>"Parent NRIC");
if($config['membership_data_use_customize_value']){
	//foreach($config['membership_data_use_customize_value'] as $r=>$f){
	//	$available_field[$f['input_name']] = array('label'=>$f['title']);
	//}
}else{
	if(!$config['membership_not_malaysian']){
		//$available_field['newspaper'] = array('label'=>"Newspaper");
		//$available_field['other_vip_card'] = array('label'=>"Other VIP Card");
	}
}	
if($config['membership_extra_info']){
	//foreach($config['membership_extra_info'] as $col=>$info){
	//	$available_field[$col] = array('label'=>$info['description']);
	//}
}

$smarty->assign("available_field", $available_field);

class MembershipListing extends Module
{
	function __construct($title, $template='') {
        $this->init();
		parent::__construct($title, $template='');
	}
    
	function init() {
        global $con, $smarty, $config, $available_field, $appCore;
        
        $con->sql_query("select id, code from branch  where active = 1 order by sequence,code");
		while($r = $con->sql_fetchrow()) {
			$branches[$r['code']] = $r['id'];
		}
		$smarty->assign("branches", $branches);
        
		$con->sql_query("select distinct race from membership where race <> ''");
		while($r = $con->sql_fetchrow()) {
			$races[] = $r['race'];
		}
		$smarty->assign("races", $races);
        
		$con->sql_query("select distinct national from membership where national <> ''");
		while($r = $con->sql_fetchrow()) {
			$nationals[] = $r['national'];
		}
		$smarty->assign("nationals", $nationals);
        
		$con->sql_query("select distinct city from membership where city <> ''");
		while($r = $con->sql_fetchrow()) {
			$cities[] = $r['city'];
		}
		$smarty->assign("cities", $cities);
        
		$con->sql_query("select distinct state from membership where state <> ''");
		while($r = $con->sql_fetchrow()) {
			$states[] = $r['state'];
		}
		$smarty->assign("states", $states);
		
		$con->sql_query("select distinct(postcode) from membership where postcode<>'' order by postcode");
		while($r = $con->sql_fetchrow()) {
			$postcode[] = $r[0];
		}
		$smarty->assign("postcode", $postcode);
		
        $date_filter = array(
                             "issue_date" => "Issue Date",
                             "verified_date" => "Verified Date",
                             "blocked_date" => "Blocked Date",
                             "terminated_date" => "Terminated Date",
                             "next_expiry_date" => "Expiry Date",
                             "dob" => "Birth Date",
                             "points_update" => "Points Update"
                             );
        $smarty->assign("date_filter", $date_filter);
        
        $search_type = array(
                             "card_no" => "Card No.",
                             "name" => "Name",
                             "nric" => "NRIC",
                             "address" => "Address",
							 "phone"=>"Phone"
                             );
        $smarty->assign('search_type', $search_type);
                
		$curr_date = date("Y-m-d H:i:s");
		$sms_info = array();
		$q1 = $con->sql_query("select * from membership_isms where approved = 1 and active = 1 and cron_status = 0 order by send_date, added");
		
		while($r = $con->sql_fetchassoc($q1)) {
			$send_date = $r['send_date']." ".$r['send_hour'].":".$r['send_min'].":00";
			// found it is future sms, skip for now
			if(strtotime($curr_date) < strtotime($send_date)) continue;

			$r['progress_perc'] = round($r['total_run'] / $r['total_recipient'] * 100, 0);
			$sms_info[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("sms_info", $sms_info);
		
		// Mark default selected fields
		if(!isset($_REQUEST['export_field'])){
			foreach($available_field as $field => $r){
				if(!$r['default_tick'])	continue;
				
				$_REQUEST['export_field'][$field] = 1;
			}
		}
		
		//load month name list
		$monthsList = $appCore->monthsList;
		$smarty->assign('months', $monthsList);
    }
    
	function _default() {
		$this->member_list();
	}
	
	function member_list() {
		global $con, $smarty, $config;
        
		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else
			$sz = 50;
		
        $this->form_filter($filter, $array_filters, $print_params, $err);
        if($err){
			$smarty->assign('err', $err);
		}else{
			$con->sql_query("select count(*) from membership m $filter");
		
			$r = $con->sql_fetchrow();
			$total = $r[0];
			$smarty->assign('total_row', $total);
			$con->sql_freeresult();
			
			if ($total > $sz){	
				if ($start > $total) $start = 0;
				// create pagination
				$pg="";
				for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
					$pg .= "<option value=$i";
					if ($i == $start){
						$pg .= " selected";
					}
					$pg .= ">$p</option>";
				}
				$pg .= "</select>&nbsp;&nbsp;";
				$pg2 = "<b>Page</b> <select name=s1 onchange=\"document.f_a.s.selectedIndex=this.selectedIndex;submit_form();\">".$pg;
				$smarty->assign("pagination2", "$pg2");
				
				$pg = "<b>Page</b> <select name=s onchange=\"submit_form();\">".$pg;
				$smarty->assign("pagination", "$pg");
			}
			
			if(!isset($_REQUEST['is_print'])&&!isset($_REQUEST['is_export'])){
				$limit = "limit $start, $sz";
			}
			
			$sort_column = isset($_COOKIE['_tbsort_membership_listing'])?$_COOKIE['_tbsort_membership_listing']:'';
			$sort_order = isset($_COOKIE['_tbsort_membership_listing_order'])?$_COOKIE['_tbsort_membership_listing_order']:'asc';
			
			if($sort_column) $sort_by = "order by $sort_column $sort_order";

			if($config['single_server_mode'] || (!$config['single_server_mode'] && BRANCH_CODE == "HQ")){
				$tmp_select = ", tmp_mpt.card_no as tmp_card_no";
				$tmp_left_join = "left join tmp_membership_points_trigger tmp_mpt on tmp_mpt.card_no = m.card_no";
			}
			
			$q1 = $con->sql_query($sql = "select  m.*, date_format(m.dob, '%d-%m-%Y') as dob, u.u, b.code as apply_branch_code, lp.code as lp_branch_code,
							(
								select lrbc.code 
								from membership_history mh
							    left join branch lrbc on lrbc.id = mh.branch_id
							    where mh.nric = m.nric
							    order by mh.issue_date desc ,mh.expiry_date desc limit 1
							) as last_renew_branch
								$tmp_select
								 from membership m 
								 left join user u on m.verified_by = u.id 
								 left join branch b on b.id = m.apply_branch_id 
								 left join branch lp on lp.id = m.lp_branch_id
								 $tmp_left_join
								 $filter $sort_by $limit");
			//print $sql;

			//$members = $con->sql_fetchrowset($q1);
			while($r = $con->sql_fetchrow($q1)){
				$r['newspaper'] = unserialize($r['newspaper']);
				
				if(!$r['points_changed'] && (isset($r['tmp_card_no']) && !$r['tmp_card_no'])){
					$q2 = $con->sql_query("select * from membership_history where nric = ".ms($r['nric'])." and card_no = ".ms($r['card_no']));
					
					if($con->sql_numrows($q2) > 0){
						if(!$r['tmp_card_no']) $r['points_changed'] = 1;
					}
					$con->sql_freeresult($q2);
				}

				/*$q2 = $con->sql_query("select lrbc.code from membership_history mh
									   left join branch lrbc on lrbc.id = mh.branch_id
									   where mh.nric = ".ms($r['nric'])."
									   order by mh.issue_date, mh.expiry_date");
				$history = $con->sql_fetchrowset($q2);
				$r['last_renew_branch'] = $history[count($history)-1]['code'];
				$con->sql_freeresult($q2);*/
				
				$members[] = $r;
			}
			$con->sql_freeresult($q1);
			$smarty->assign('members', $members);
		}
		
		$smarty->assign('start_counter', $start);
        
		
		if($_REQUEST['sort'])	$smarty->display("membership.listing.table.tpl");
		else $this->display();
	}
	
    function form_filter(&$filter, &$array_filters, &$print_params, &$err = array()) {
        global $config, $appCore, $LANG;
        
        $form = $_REQUEST;
        $filter = array();
        $print_params = array();
		
		if($config['membership_mobile_settings']){
			$form['mobile_registered_date_from'] = trim($form['mobile_registered_date_from']);
			$form['mobile_registered_date_to'] = trim($form['mobile_registered_date_to']);
			$form['mobile_registered'] = trim($form['mobile_registered']);
			
			// Mobile Registered
			if($form['mobile_registered'] != "") {
				switch ($form['mobile_registered']) {
					case 'y':
						$filter[] = "m.mobile_registered=1";
						$print_params['mobile_registered'] = "Yes";
						break;
					case 'n':
						$filter[] = "m.mobile_registered=0";
						$print_params['mobile_registered'] = "No";
						break;
				}
			}else   $print_params['mobile_registered'] = "All";
			
			if($form['mobile_registered'] == 'y'){
				// Register Date From
				if($form['mobile_registered_date_from']){
					if(!$appCore->isValidDateFormat($form['mobile_registered_date_from'])){
						$err[] = sprintf($LANG['INVALID_DATE_FORMAT2'], "Mobile Register Date From");
					}else{
						$filter[] = "m.mobile_registered_time >= ".ms($form['mobile_registered_date_from']);
						$print_params['mobile_registered_date_from'] = $form['mobile_registered_date_from'];
					}
				}
				
				// Register Date To
				if($form['mobile_registered_date_to']){
					if(!$appCore->isValidDateFormat($form['mobile_registered_date_to'])){
						$err[] = sprintf($LANG['INVALID_DATE_FORMAT2'], "Mobile Register Date To");
					}else{
						$filter[] = "m.mobile_registered_time <= ".ms($form['mobile_registered_date_to']." 23:59:59");
						$print_params['mobile_registered_date_to'] = $form['mobile_registered_date_to'];
					}
				}
			}			
		}
        
        if($form['branch_id']) {
            $filter[] = "m.apply_branch_id = ".mi($form['branch_id']);
            $print_params['branch'] = get_branch_code($form['branch_id']);
        }else   $print_params['branch'] = "All";
        
        if($form['race']) {
            $filter[] = "m.race = ".ms($form['race']);
            $print_params['race'] = $form['race'];
        }else   $print_params['race'] = "All";
        
        if($form['national']) {
            $filter[] = "m.national = ".ms($form['national']);
        }
        
        if($form['city']) {
            $filter[] = "m.city = ".ms($form['city']);
            $print_params['city'] = $form['city'];
        }else   $print_params['city'] = "All";
        
        if($form['state']) {
            $filter[] = "m.state = ".ms($form['state']);
            $print_params['state'] = $form['state'];
        }else   $print_params['state'] = "All";
        
        if($form['postcode']) {
            $filter[] = "m.postcode = ".ms($form['postcode']);
        }
        
        if($form['date_filter']) {
            $date_filter = $form['date_filter'];

            $date_from = $form['date_from'];
            $date_to = $form['date_to'];

            $print_params['date_from'] = $date_from;
            $print_params['date_to'] = $date_to;
            
            // replace the "-" for date from/to if date filter is dob
            if($date_filter == 'dob'){
                if($date_from)  $date_from = str_replace("-", "", $form['date_from']);
                if($date_to)    $date_to = str_replace("-", "", $form['date_to']);
            }
            
            if($date_from && $date_to)  $filter[] = "m.$date_filter between ".ms($date_from)." and ".ms($date_to);
            elseif($date_from)          $filter[] = "m.$date_filter >= ".ms($date_from);
            elseif($date_to)            $filter[] = "m.$date_filter <= ".ms($date_to);
            
            if($date_filter == 'dob')   $print_params['date_filter'] = strtoupper($date_filter);
            else                        $print_params['date_filter'] = ucwords(str_replace("_", " ", $date_filter));
		}
        
        if($form['search_type'] && $form['search_value']) {
			if($form['search_type'] == 'phone'){
				$filter_phone = array();
				for($i=1;$i<=3;$i++){
					$filter_phone[] = "m.".$form['search_type']."_$i like ".ms("%".replace_special_char($_REQUEST['search_value'])."%");
				}
				$filter[] = "(".implode(" or ",$filter_phone).")";
			}else{
				$filter[] = "m.".$form['search_type']." like ".ms("%".replace_special_char($_REQUEST['search_value'])."%");
			}
			$print_params['search_type'] = ucwords(str_replace("_", " ", $form['search_type']));
            $print_params['search_value'] = $form['search_value'];
		}
        
        if($form['terminated'] != "") {
            switch ($form['terminated']) {
                case 'y':
                    $filter[] = "m.terminated_date > 0";
                    $print_params['terminated'] = "Yes";
                    break;
                case 'n':
                    $filter[] = "m.terminated_date = 0";
                    $print_params['terminated'] = "No";
                    break;
            }
        }else   $print_params['terminated'] = "All";
        
        if($form['blocked'] != "") {
            switch ($form['blocked']) {
                case 'y':
                    $filter[] = "m.blocked_by > 0";
                    $print_params['blocked'] = "Yes";
                    break;
                case 'n':
                    $filter[] = "m.blocked_by = 0";
                    $print_params['blocked'] = "No";
                    break;
            }
        }else   $print_params['blocked'] = "All";
		
        if($form['verified'] != "") {
            switch ($form['verified']) {
                case 'y':
                    $filter[] = "m.verified_by > 0";
                    $print_params['verified'] = "Yes";
                    break;
                case 'n':
                    $filter[] = "m.verified_by = 0";
                    $print_params['verified'] = "No";
                    break;
            }
        }else   $print_params['verified'] = "All";
        
        if($form['expiry'] != "") {
            switch ($form['expiry']) {
                case 'y':
                    $filter[] = "m.next_expiry_date < ".ms(date('Y-m-d'));
                    $print_params['expiry'] = "Yes";
                    break;
                case 'n':
                    $filter[] = "m.next_expiry_date >= ".ms(date('Y-m-d'));
                    $print_params['expiry'] = "No";
                    break;
            }
        }else   $print_params['expiry'] = "All";
        
        if($form['point_from'] != '') {
            $filter[] = "m.points >= ".mi($form['point_from']);
            $print_params['point_from'] = $form['point_from'];
        }
        
		if($form['point_to'] != '') {
            $filter[] = "m.points <= ".mi($form['point_to']);
            $print_params['point_to'] = $form['point_to'];
        }
        
        if($form['gender']) {
            $filter[] = "m.gender = ".ms($form['gender']);
            $print_params['gender'] = $form['gender'];
        }
        
		$current_year = date("Y");
        if($form['age_from']) {
            $filter[] = "($current_year-date_format(m.dob,'%Y')) >= ".mi($form['age_from']);
            $print_params['age_from'] = $form['age_from'];
        }
        
		if($form['age_to']) {
            $filter[] = "($current_year-date_format(m.dob,'%Y')) <= ".mi($form['age_to']);
            $print_params['age_to'] = $form['age_to'];
        }

		// filter member type
		if($config['membership_type'] && $form['member_type']) {
			$filter[] = "m.member_type = ".ms($form['member_type']);
		}
		
		// filter staff type
		if($config['membership_enable_staff_card'] && $form['staff_type']) {
			$filter[] = "m.staff_type=".ms($form['staff_type']);
		}
		
		//filter by birthday month
		if($form['birthday_month_from'] && $form['birthday_month_to']){
			if(mi($form['birthday_month_from']) > mi($form['birthday_month_to'])){
				$err[] = "Birthday Month From cannot greater than Month To.";
			}else{
				$filter[] = "date_format(m.dob,'%m') >= ".mi($form['birthday_month_from'])." and date_format(m.dob,'%m') <= ".mi($form['birthday_month_to']);
			}
		}elseif(($form['birthday_month_from'] && !$form['birthday_month_to']) || ($form['birthday_month_to'] && !$form['birthday_month_from'])){
			$err[] = "Filter by Birthday Month must have Month From and Month To.";
		}
		
		//filter by birthday day
		if($form['birthday_day_from'] && $form['birthday_day_to']){
			if(mi($form['birthday_day_from']) > mi($form['birthday_day_to'])){
				$err[] = "Birthday Day From cannot greater than Day To.";
			}else{
				$filter[] = "date_format(m.dob,'%d') >= ".mi($form['birthday_day_from'])." and date_format(m.dob,'%d') <= ".mi($form['birthday_day_to']);
			}
		}elseif(($form['birthday_day_from'] && !$form['birthday_day_to']) || ($form['birthday_day_to'] && !$form['birthday_day_from'])){
			$err[] = "Filter by Birthday Day must have Day From and Day To.";
		}
		
        if($filter) {
            $array_filters = $filter;
            $filter = "where ".join(" and ", $filter);
        }else   $filter = "";
    }
	
	function print_member_list($type='default'){
		global $con, $smarty, $config, $available_field;
			
		$export_field = $_REQUEST['export_field'];
		if($type=='mailing_list')	$print_mailing_list = true;
		elseif($type=='export_excel'){
		    $export_excel = true;
            include_once("include/excelwriter.php");
		}elseif($type=='export_csv'){
			$export_csv = true;
		}

		$this->form_filter($filter, $array_filters, $print_params, $err);
		if($err){
			display_redir($_SERVER['PHP_SELF'], "Membership Listing", join('</li><li>', $err));
		}

		$smarty->assign('print_params', $print_params);
		$sql = "select count(*) from membership m $filter";

		$con->sql_query($sql);
		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		$sql_limit = 10000;

		$page = 0;
		if($print_mailing_list){
            $item_per_page = $config['membership_listing_print_mailing_item_per_page'] ? $config['membership_listing_print_mailing_item_per_page'] : 12;
            if($config['membership_listing_print_mailing_alt_templates'])   $tpl = $config['membership_listing_print_mailing_alt_templates'];
            else	$tpl = 'membership.listing.print_mailing_list.tpl'; 
		}	
		elseif($export_excel){
            $item_per_page = $config['membership_listing_export_excel_item_per_sheet'] ? $config['membership_listing_export_excel_item_per_sheet'] : 1000;
            if($config['membership_listing_export_excel_alt_templates'])   $tpl = $config['membership_listing_export_excel_alt_templates'];
            else	$tpl = 'membership.listing.export_excel.tpl';
		}else{
            $item_per_page = $config['membership_listing_printing_item_per_page'] >= 5 ? $config['membership_listing_printing_item_per_page'] : 20;
            if($config['membership_listing_print_alt_templates'])   $tpl = $config['membership_listing_print_alt_templates'];
            else	$tpl = 'membership.listing.print.tpl';
		}
		
		$totalpage = ceil($total_rows/$item_per_page);
		
		$start_counter = 1;
		$page = 1;
		$smarty->assign('totalpage', $totalpage);
		$smarty->assign('PAGE_SIZE',$item_per_page);
		$smarty->assign('export_field', $export_field);
		$tmpname = "Membership_Listing_".time();

		$sort_column = isset($_COOKIE['_tbsort_membership_listing'])?$_COOKIE['_tbsort_membership_listing']:'';
		$sort_order = isset($_COOKIE['_tbsort_membership_listing_order'])?$_COOKIE['_tbsort_membership_listing_order']:'asc';
		
		if($sort_column) $sort_by = $sort_column." ".$sort_order;
		else $sort_by = "m.nric";

		if($type=="export_excel") $prefix_card_no = "concat('=\"', m.card_no, '\"') as card_no,";

		if($config['membership_data_use_customize_value']){
			foreach($config['membership_data_use_customize_value'] as $row=>$f){
				if($f['input_name'] == "newspaper"){
					foreach($config['membership_data_use_customize_value'][$row]['value'] as $val_type=>$val_name){
						$newspaper_list[$val_type] = $val_name;
					}
				}
				if($f['input_name'] == "radio_station"){
					foreach($config['membership_data_use_customize_value'][$row]['value'] as $val_type=>$val_name){
						$radio_station_list[$val_type] = $val_name;
					}
				}
			}
		}else{
			// newspaper array
			$newspaper_list = array("nst" => "New Strait Time", "thestar" => "The Star", "kwongwah" => "Kwong Wah Yit Poh", "sinchew" => "Sin Chew Jit Poh", "nanyang" => "Nanyang Siang Poh", "guangming" => "Guang Ming", "utusan" => "Utusan Malaysia", "bharian" => "Berita Harian", "malaymail" => "Malay Mail", "thesun" => "The Sun", "tamil" => "Tamil Newspaper", "chinapress", "chinapress" => "China Press");
			$vip_card_list = array("sunshine" => "Sunshine", "thestore" => "The Store", "fajar" => "Fajar", "parkson" => "Parkson", "jusco" => "Jaya Jusco", "yawata" => "Yawata", "pacifc" => "pacific", "makro" => "Makro", "metrojaya" => "Metro Jaya");			
		}
		
		$credit_card_list = array("visa" => "Visa", "master" => "Master", "amex" => "Amex", "diners" => "Diners", "others" => "Others");
		
		for($i=0; $i<$total_rows; $i+=$sql_limit){
			$tmp_items = $temp = array();
			$sql = "select m.*, $prefix_card_no date_format(m.dob, '%d-%m-%Y') as dob, u.u, u2.u as recruit_by, b.code as apply_branch_code, lp.code as lp_branch_code,
					(
						select lrbc.code 
						from membership_history mh
						left join branch lrbc on lrbc.id = mh.branch_id
						where mh.nric = m.nric
						order by mh.issue_date desc ,mh.expiry_date desc limit 1
					) as last_renew_branch
					from membership m
					left join user u on m.verified_by = u.id 
					left join user u2 on u.id = m.recruit_by
					left join branch b on b.id = m.apply_branch_id 
					left join branch lp on lp.id = m.lp_branch_id
					$filter
					order by $sort_by
					limit $i, $sql_limit";
					
			$q1 = $con->sql_query($sql);
			//print $con->sql_numrows()." selected ".memory_get_usage()."<br>";
			while($r = $con->sql_fetchassoc($q1)){
				$newspaper = $radio_station = $other_vip_card = $credit_card = array();
				$r['newspaper'] = unserialize($r['newspaper']);
				$r['radio_station'] = unserialize($r['radio_station']);
				$r['other_vip_card'] = unserialize($r['other_vip_card']);
				$r['credit_card'] = unserialize($r['credit_card']);
				if ($r['newspaper']){
					foreach($r['newspaper'] as $val_type=>$dummy){
						$newspaper[] = $newspaper_list[$val_type];
					}
				}
				$r['newspaper'] = join(",", $newspaper);
				if ($r['radio_station']){
					foreach($r['radio_station'] as $val_type=>$dummy){
						$radio_station[] = $radio_station_list[$val_type];
					}
				}
				$r['radio_station'] = join(",", $radio_station);
				if ($r['other_vip_card']){
					foreach($r['other_vip_card'] as $val_type=>$dummy){
						$other_vip_card[] = $vip_card_list[$val_type];
					}
				}
				$r['other_vip_card'] = join(",", $other_vip_card);
				if ($r['credit_card']){
					foreach($r['credit_card'] as $val_type=>$dummy){
						$credit_card[] = $credit_card_list[$val_type];
					}
				}
				$r['credit_card'] = join(",", $credit_card);
				
				if($config['membership_extra_info']){
					// extra info
					$q2 = $con->sql_query("select * from membership_extra_info mei where mei.nric = ".ms($r['nric']));
					
					if($con->sql_numrows($q2) > 0){
						$extra_info = $con->sql_fetchassoc($q2);
						$r = array_merge($r, $extra_info);
					}
					$con->sql_freeresult($q2);
				}
				
				$tmp_items[] = $r;
			}
			$con->sql_freeresult($q1);
			
			$temp = array_merge($temp, $tmp_items);
			
			while($temp){
                for($j = 1; $j<=$item_per_page && $temp; $j++){
	                $items[] = array_shift($temp);
				}
				if(!$export_csv){
					if(count($items)>=$item_per_page){
					    $smarty->assign('start_counter', $start_counter);
					    $smarty->assign('page', $page);
					    $smarty->assign('items', $items);
					    	if($export_excel){
						        $output = "/tmp/".$tmpname."_".$page.".xls";
				                file_put_contents($output, ExcelWriter::GetHeader().$smarty->fetch($tpl).ExcelWriter::GetFooter());
							}else	$smarty->display($tpl);
	                    $smarty->assign("skip_header",1);
	                    $start_counter += $item_per_page;
	                    $page++;
	                    unset($items);
					}
				}
			}
			
			
			//print "after assign: ".memory_get_usage()."<br>";
			//$con->sql_freeresult();
			//unset($temp);
			//print "after free: ".memory_get_usage()."<br>";
		}
		if($items && !$export_csv){
		    $smarty->assign('start_counter', $start_counter);
		    $smarty->assign('page', $page);
		    $smarty->assign('items', $items);
            if($export_excel){
		        $output = "/tmp/".$tmpname."_".$page.".xls";
                file_put_contents($output, ExcelWriter::GetHeader().$smarty->fetch($tpl).ExcelWriter::GetFooter());
			} else{
				$smarty->display($tpl);
			}
            $smarty->assign("skip_header",1);
            $start_counter += $item_per_page;
            $page++;
            unset($items);
		}
		
		if($export_excel){
            exec("cd /tmp; zip -9 $tmpname.zip $tmpname*.xls");
			//ob_end_clean();
			//log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[title] To Excel($_REQUEST[report_title])");

			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$tmpname.zip");
			readfile("/tmp/$tmpname.zip");
		}
		
		if($export_csv){
			$contents = array();
			$headers = array();
			$th = array();
			foreach ($export_field as $field => $value) {
				$headers[] = $field;
				if($available_field[$field]['label']){
					$th[] = $available_field[$field]['label'];
				}else{
					$th[] = '-';
				}
			}

			$rows = array();
			$counter = 0;
			foreach($items as $key=>$item){
				$row = array();
				foreach ($headers as $column) {
					if($available_field[$column]['date']){
						$format = ($column=='mobile_registered_time')?'d/m/Y H:i:s':'d/m/Y';
						$row[] = (strtotime($item[$column])>0) ? "\t".date($format, strtotime($item[$column])) : '-';
					}else{
						$column = ($column=='apply_branch'?'apply_branch_code':$column);
						$column = ($column=='last_purchase_branch'?'lp_branch_code':$column);
						$column = ($column=='verified_by'?'u':$column);
						if($column=='marital_status'){
							$tdata = ($item[$column]==1)?'Married':'Single';
						}else{
							$tdata = $item[$column];
						}
						$row[] = "\t".$tdata;
						
					}
				}
				$rows[] = $row;
			}
			$path = "/tmp";
			$filename = 'member_list_csv_'.time().'.csv';
			$fp = fopen($path."/".$filename, 'w');

			$contents = fputcsv_eol($fp, $th);
			foreach ($rows as $r) {
				$contents =  fputcsv_eol($fp, $r);
			}
			fclose($fp);

			header("Content-type: text/msexcel");
			header('Content-Disposition: attachment;filename='.$filename);
			print file_get_contents($path."/".$filename);
		}
		exit;
	}
	
	function print_mailing_list(){
        $this->print_member_list('mailing_list');
	}
	
	function export_excel(){
        $this->print_member_list('export_excel');
	}

	function export_csv(){
        $this->print_member_list('export_csv');
	}
	
	function sms_get_credit() {
		$isms = new iSMS();
		print $isms->get_credit();
		exit;
	}
	
	function send_sms() {

		global $con, $sessioninfo, $config, $LANG, $smarty;
		
		if(!$config['isms_user']){
			js_redirect($LANG['NEED_CONFIG'], $_SERVER['PHP_SELF']);
		}
		if(!privilege('MEMBERSHIP_ALLOW_SMS')){
			js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ALLOW_SMS', BRANCH_CODE), "/index.php");
		}
		
		$this->form_filter($filter, $array_filters, $print_params, $err);
		if($err){
			display_redir($_SERVER['PHP_SELF'], "Membership Listing", join('</li><li>', $err));
		}
		
		$isms = new iSMS();

		$sql = "select name,phone_3,phone_2,phone_1 from membership m $filter";
		$q1 = $con->sql_query($sql);
		$numbers = array();
		while($r=$con->sql_fetchassoc($q1)){
			$mobile = trim($r['phone_3']);
			$mobile = str_replace("-", "", $mobile);
			
			// go to phone 2, then phone 1 if blank
			if (!preg_match('/^01\d{8,9}/',$mobile)) $mobile = trim($r['phone_2']); 
			$mobile = str_replace("-", "", $mobile);
			if (!preg_match('/^01\d{8,9}/',$mobile)) $mobile = trim($r['phone_1']); 
			$mobile = str_replace("-", "", $mobile);
			
			// check valid numbers
			if (preg_match('/^01\d{8,9}/',$mobile)) $numbers[] = '6'.$mobile;
			//else print "<li> rejected $r[0] ($r[1])\n";
		}
		$con->sql_freeresult($q1);

		$smarty->display("header.tpl");

		if(count($numbers) < 100){
			$success_count = $isms->send_sms($numbers,$_REQUEST['sms']);
			$cc = $isms->get_credit();
			
			$failed_count = 0;
			$failed_count = count($numbers) - $success_count;
			$failed_msg = "";
			if($failed_count != 0) $failed_msg = "and failed to send to $failed_count member(s).";
			
			log_br($sessioninfo['id'],'Membership','SMS',"Send SMS to $success_count member(s) $failed_msg (Remaining credit: $cc)");
			
			if($numbers){
				print "<h1>SMS has been sent to $success_count member(s) ";
				if($failed_msg){
					print $failed_msg;
				}
			}else{
				print "<h1>SMS cannot be send.";
			}
			
			print "<br /> Your Remaining Credit is $cc</h1>";
		}else{
			$ins = array();
			$ins['branch_id'] = $sessioninfo['branch_id'];
			$ins['user_id'] = $sessioninfo['id'];
			$ins['active'] = 1;
			$ins['status'] = 1;
			$ins['approved'] = 1;
			$ins['send_date'] = date("Y-m-d");
			$ins['send_hour'] = 0;
			$ins['send_min'] = 0;
			$ins['msg'] = $_REQUEST['sms'];
			$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
			if($array_filters) $ins['filters'] = serialize($array_filters);
			$ins['total_recipient'] = count($numbers);
			$ins['total_run'] = 0;
			
			$con->sql_query("insert into membership_isms ".mysql_insert_by_field($ins));
			
			print "<h1> Due to the SMS is send out more than 100 members, <br />
				   system have marked to send SMS for the following members after 5 minutes.<br /><br />
				   Please go back to previous page for progress of SMS Broadcast.</h1><br />";
		}
		print "<input type=\"button\" value=\"Back\" onclick=\"window.location = 'membership.listing.php';\" />";
		$smarty->display("footer.tpl");
	}
	
	function ajax_reload_sms_broadcast(){
		global $con, $config, $sessioninfo, $smarty;
		
		$ret = array();
		$q1 = $con->sql_query("select * from membership_isms order by send_date, added");
		
		while($r = $con->sql_fetchassoc($q1)){
			$r['progress_perc'] = round($r['total_run'] / $r['total_recipient'] * 100, 0);
			$ret[] = $r;
		}
		
		print json_encode($ret);
	}
	
	function ajax_show_pn(){
		global $con, $config, $sessioninfo, $smarty, $LANG;
		
		if (!privilege('MEMBERSHIP_ALLOW_PN')) die(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ALLOW_PN', BRANCH_CODE));
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('membership.listing.push_notification.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_send_pn(){
		global $con, $config, $sessioninfo, $smarty, $LANG, $appCore;
		
		if (!privilege('MEMBERSHIP_ALLOW_PN')) die(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_ALLOW_PN', BRANCH_CODE));
		
		//print_r($_REQUEST);
		
		$err = array();
		$pn_title = trim($_REQUEST['pn_title']);
		$pn_msg = trim($_REQUEST['pn_msg']);
		$screen_tag = trim($_REQUEST['screen_tag']);
		if(!$pn_title)	$err[] = "Push Title is Empty";
		if(!$pn_msg)	$err[] = "Push Text is Empty";
		
		$this->form_filter($filter, $array_filters, $print_params, $err);
		if($err){
			foreach($err as $e){
				print "$e\n";
			}
			exit;
		}
		//print_r($filter);
		
		// make sure only select mobile register
		if(!$filter)	$filter = "where m.mobile_registered=1";
		else $filter .= " and m.mobile_registered=1";
		
		// Begin Transaction
		$con->sql_begin_transaction();
			
		// Select Member
		$q1 = $con->sql_query("select m.nric
			 from membership m 
			 $filter
			 order by m.nric");
			 
		// Get Total Member Count
		$member_count = mi($con->sql_numrows($q1));
		
		// No Member Found
		if($member_count<=0)	die("No Member is Selected.");
		
		$upd = array();
		$pn_guid = $appCore->newGUID();
		$upd['guid'] = $pn_guid;
		$upd['branch_id'] = $sessioninfo['branch_id'];
		$upd['user_id'] = $sessioninfo['id'];
		$upd['pn_title'] = $pn_title;
		$upd['pn_msg'] = $pn_msg;
		$upd['screen_tag'] = $screen_tag;
		$upd['active'] = 1;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into memberships_pn ".mysql_insert_by_field($upd));
		
		while($member = $con->sql_fetchassoc($q1)){
			$nric = trim($member['nric']);
			
			$upd2 = array();
			$upd2['guid'] = $appCore->newGUID();
			$upd2['memberships_pn_guid'] = $pn_guid;
			$upd2['nric'] = $nric;
			$upd2['added'] = $upd2['last_update'] = 'CURRENT_TIMESTAMP';
			$con->sql_query("insert into memberships_pn_items ".mysql_insert_by_field($upd2));
		}
		$con->sql_freeresult($q1);
		
		log_br($sessioninfo['id'],'Membership', 0,"Send Push Notification to $member_count member(s)");
		
		// Commit Transaction
		$con->sql_commit();
		
		// Send push notification using cron in background
		$appCore->cronManager->runCronMemberPushNotification(true, '-pn_guid='.$pn_guid);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['pn_guid'] = $pn_guid;
		print json_encode($ret);
	}
	
	function ajax_monitor_pn(){
		global $con, $config, $sessioninfo, $smarty, $LANG, $appCore;
		
		$pn_guid = trim($_REQUEST['pn_guid']);
		if(!$pn_guid)	die("Invalid ID");
		
		$con->sql_query("select * from memberships_pn where guid=".ms($pn_guid));
		$pn = $con->sql_fetchassoc();
		$con->sql_freeresult();
		//$pn['err_msg'] = 'test error';
		
		if($pn['err_msg']){	// Got Error			
			$ret = array();
			$ret['ok'] = 1;
			$ret['err_msg'] = $pn['err_msg'];
			print json_encode($ret);
			exit;
		}
		$ret = array();
		$ret['ok'] = 1;
		
		if($pn['completed']){
			// Check Total
			$con->sql_query("select sum(completed) as total_count, sum(success) as success_count from memberships_pn_items where memberships_pn_guid=".ms($pn_guid));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$ret['completed'] = 1;
			$ret['total_count'] = $tmp['total_count'];
			$ret['success_count'] = $tmp['success_count'];
		}else{
			$monitor_fp_path = 'cron.check_member_push_notification.monitor';
			$str = @file_get_contents($monitor_fp_path);
			$monitor = unserialize($str);
			if($monitor){
				// Is Running this pn
				if($monitor['running_guid'] == $pn_guid){
					$ret['total_count'] = $monitor['total_count'];
					$ret['curr_count'] = $monitor['curr_count'];
				}
			}
		}	
		
		print json_encode($ret);
	}
}

$membership_listing = new MembershipListing ('Membership Listing');
?>
