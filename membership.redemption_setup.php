<?php/*29/9/2009 10:00:00 PM jeff- change sku autocomplete to multiple add12/1/2009 2:52:19 PM Andy- rename from ' membership.redemption.php' to 'membership.redemption_setup.php"- change some tpl file name12/9/2009 3:27:08 PM Andy- add return 404 to ajax if fail to update (error when point and receipt amt both zero)- add item available branch feature- branch only can setup and edit the item create by themself3/2/2010 3:25:14 PM Andy- Add receipt date period checking3/12/2010 11:40:10 AM Andy- Delete button change and multiple delete function.- Toggle active button change and multiple active/deactive function.- Fix item cannot approve due to have amount but no end date bugs.- Add use current date feature for receipt control.8/11/2010 12:17:07 PM Justin- Added 2 new fields which is valid date start and end into membership redemption item setup table (membership_redemption_sku).- Enhanced the save and update sql queries to include both of these date fields.- Enhanced the checking for valid and receipt date start/end while found both fields is not empty.  -> When found the valid and receipt date end is earlier than date start, show error message to indicate the date is invalid.8/18/2010 11:33:02 AM Justin- Created a update feature to update all the active redemption item become inactive if found they already expired based on current date.- Fixed the ajax add item return non human readable message when adding empty sku item.8/25/2010 6:01:30 PM Justin- Enhanced the redemption item setup module to include search by SKU. (completed)  -> This filter available when user tick on "Filter Redemption with SKU" checkbox, a form of SKU will be presented.  -> User can add SKU items in multiple and these SKU items will listed under the a text box.  -> Added SKU items can be delete by require user to select and press on "remove" button.- Added "Cash" field to maintain the required cash to redeem items.- Added validation for cash must not key in less than 0 amount.- Added validation for Receipt and Valid Date End cannot be set early than current date.9/17/2010 5:24:43 PM Justin- Added 2 new status "Pending" and "Expired" filters.- Changed the filter method for "status" field to consider status of "confirm".9/22/2010 12:26:08 PM Justin- Re-aligned the status filter.9/27/2010 3:44:47 PM Justin- Added a cancel date updates for those item that updated by system.- Added item status to be updated altogether under save function.- Added validation for item status unable to update if found point, cash and receipt amount is null.- Activated those previous codes that use to delete the hidden deleted items.- Disabled instant item status update and item delete codes since no longer using it.10/25/2010 6:29:45 PM Justin- Changed the branch filter to filter available branch.10/28/2010 4:27:43 PM Justin- Fixed the bugs that unable to compare points and block user if found existed item is same sku item and points.- Created a unset function to unset those items is use to do checking purpose only.- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.11/8/2010 10:48:36 AM Justin- Fixed the bugs where it keep displays invalid error message saying that the user cannot update the items which is not belongs to the branch.1/20/2011 1:38:11 PM Justin- Added to capture for SKU created by and approved by.- Added to retrieve users that created and approved items.2/15/2011 3:44:30 PM Justin- Modified the checking function from PHP to allow user store same SKU item with same points or cash when found previous sku item is already expired.2/25/2011 3:17:02 PM Justin- Fixed the bugs where user cannot extend the sku items valid date by adding new same sku item.4/22/2011 1:17:02 PM Justin- Fixed the bugs that item does not show all in template during refresh the filters.6/24/2011 5:01:39 PM Andy- Make all branch default sort by sequence, code.7/6/2011 12:08:49 PM Andy- Change split() to use explode()10/15/2012 12:07 PM Justin- Bug fixed on receipt amount cannot be insert as decimal points figure.1/10/2013 5:05 PM Justin- Bug fixed on cash require cannot insert as decimal figure.1/14/2013 2:04 PM Justin- Enhanced to capture voucher value.3/9/2017 5:44 PM Justin- Enhanced to not check the valid due date when the item is inactive.7/4/2017 2:47 PM Justin- Enhanced to check against the "Receipt Date To" when user have set the receipt amount.*/include("include/common.php");//ini_set("display_errors",1);if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");if (!privilege('MEMBERSHIP_SETREDEEM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MEMBERSHIP_SETREDEEM', BRANCH_CODE), "/index.php");$maintenance->check(175);class MembershipRedemption extends Module{	var $branch_id;	function __construct($title, $template='')	{		global $sessioninfo,$con,$smarty;		$branch_id = get_request_branch(true);		$_REQUEST['branch_id'] = $branch_id;				/*$this->branch_id = intval($_REQUEST['branch_id']);		if ($this->branch_id =='')		{			$this->branch_id = $sessioninfo['branch_id'];		}		$smarty->assign('replace_table', 1);*/				if(!$_REQUEST['no_init_load'])	$this->init_load();		$smarty->assign('allow_edit',1);		parent::__construct($title, $template);			}		function _default()	{		//$this->list_data();		$this->init_table();		$this->display();		}		private function init_table(){ /* moved to maintenance.php */ }		private function init_load(){		global $con, $smarty;				// branch		$con->sql_query("select * from branch order by sequence,code") or die(mysql_error());		while($r = $con->sql_fetchrow()){			$branches[$r['id']] = $r;		}		$smarty->assign('branches',$branches);				// branch group		// load header		$con->sql_query("select * from branch_group");		while($r = $con->sql_fetchrow()){		    $branches_group['header'][$r['id']] =$r;		}		// load items		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id		order by branch.sequence, branch.code");		while($r = $con->sql_fetchrow()){	        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;	        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];		}		$smarty->assign('branches_group',$branches_group);	}		function ajax_load_redemption_available_branches(){        global $con, $smarty, $sessioninfo, $LANG;                $id = mi($_REQUEST['id']);        $branch_id = mi($_REQUEST['branch_id']);                if(BRANCH_CODE!='HQ'&&$branch_id!=$sessioninfo['branch_id'])    die($LANG['REDEMPTION_INVALID_ID']);                $con->sql_query("select * from membership_redemption_sku where id=$id and branch_id=$branch_id") or die(mysql_error());        $form = $con->sql_fetchrow();                if(!$form)  die($LANG['REDEMPTION_INVALID_ID']);        $form['available_branches'] = unserialize($form['available_branches']);                $smarty->assign('form',$form);        $smarty->display('membership.redemption_setup.available_branches.tpl');	}		function ajax_save_available_branches(){        global $con, $smarty, $sessioninfo, $LANG;                $id = mi($_REQUEST['id']);        $branch_id = mi($_REQUEST['branch_id']);		$available_branches = $_REQUEST['available_branches'];				if(!$available_branches)   die($LANG['REDEMPTION_INVALID_DATA_PROVIDED']);        if(BRANCH_CODE!='HQ'&&$branch_id!=$sessioninfo['branch_id'])    die($LANG['REDEMPTION_INVALID_ID']);                $con->sql_query("select * from membership_redemption_sku where id=$id and branch_id=$branch_id") or die(mysql_error());        $form = $con->sql_fetchrow();        if(!$form)  die($LANG['REDEMPTION_INVALID_ID']);        		$upd['available_branches'] = serialize($available_branches);		$con->sql_query("update membership_redemption_sku set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id") or die(mysql_error());		print "OK";	}		function refresh_item_list(){		global $con, $smarty, $sessioninfo;		$branch_id = get_request_branch(true);		$active = $_REQUEST['active'];		$point_range = $_REQUEST['point_range'];		$point_range_min = mi($_REQUEST['point_range_min']);		$point_range_max = mi($_REQUEST['point_range_max']);		$branches_group = $smarty->get_template_vars('branches_group');		$limit = mi($_REQUEST['limit']);		$sort_by = $_REQUEST['sort_by'];		$order_by = $_REQUEST['order_by'];				if($_REQUEST['filter_sku']){			$sku_item_code_arr = $_REQUEST['sku_code_list1'];			if(!$sku_item_code_arr){				print("Please add one or more SKU item to search");				exit;			}			$temp = array();			foreach($sku_item_code_arr as $sku_item_code){				$temp[] = ms($sku_item_code);			}						$filter[] = "si.sku_item_code in (".join(',', $temp).")";		}				// do update for all the expired redemption items	    $con->sql_query("update membership_redemption_sku mrs						 set mrs.active=0, cancel_date = CURRENT_TIMESTAMP						 where mrs.active=1 and mrs.valid_date_to != '' 						 and mrs.valid_date_to != '0000-00-00' and mrs.valid_date_to<curdate()");		//$filter[] = array();		// filter		if(BRANCH_CODE!='HQ'){			$filter[] = "mrs.branch_id in ($sessioninfo[branch_id],1)";			$filter[] = "mrs.available_branches like '%i:$sessioninfo[branch_id];i:1;%'";		}else{			if($branch_id) $filter[] = "mrs.available_branches like '%i:$branch_id;i:1;%'";		}		if($active!='All'){			if($active == '1'){ // draft, means like fresh added				$filter[] = "mrs.active=0 and mrs.confirm=0";				echo 'here';			}elseif($active == '2'){ // pending, those already activated but not confirm				$filter[] = "mrs.active=1 and mrs.confirm=0";			}elseif($active == '3'){ // active				$filter[] = "mrs.active=1 and mrs.confirm=1";			}elseif($active == '4'){ // being confirmed and already expired				$filter[] = "mrs.active=0 and mrs.confirm=1";			}		}		if($point_range=='between'){			if($point_range_max>0&&$point_range_max<$point_range_min)   $err[] = "Point Range Max cannot more than Min.";			else{			    if(!$point_range_max&&!$point_range_min){					$err[] = "Both Point Range Min & Max are empty";				}else{					if($point_range_min)    $filter[] = "mrs.point>=$point_range_min";					if($point_range_max)    $filter[] = "mrs.point<=$point_range_max";				}			}		}				if($sort_by){			$order_str = "order by $sort_by $order_by";		}else{			$order_str = "order by mrs.added";		}		if($limit){			if($limit<0)    $err[] = "Limit cannot be negative";			elseif(BRANCH_CODE=='HQ')    $limit_str = "limit $limit";		}				if($sku_code_list) $filter = "si.sku_item_code in ($sku_code_list)";				if($filter)	$filter = "where ".join(' and ', $filter);		if(!$err){  // no error		    $sql = "select mrs.*, if(sp.price is null, si.selling_price, sp.price) as selling_price, si.sku_item_code,sc.grn_cost, sc.qty, si.id as sku_item_id,					si.description, branch.code as bcode, ucase(ut.u) as cancel_user, ucase(uc.u) as create_user, ucase(um.u) as approve_user,				 	if(mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00', datediff(mrs.valid_date_to, date_format(CURDATE(), '%Y-%m-%d')) + 1, '') as days_left					from membership_redemption_sku mrs					left join sku_items si on mrs.sku_item_id = si.id					left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = ".$sessioninfo['branch_id']."					left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = ".$sessioninfo['branch_id']."					left join branch on branch.id=mrs.branch_id					left join user ut on ut.id = mrs.cancel_by					left join user uc on uc.id = mrs.created_by					left join user um on um.id = mrs.approved_by					$filter $order_str $limit_str";			//print $sql;            $r_1 = $con->sql_query($sql);            $row_count = 0;			while($r = $con->sql_fetchrow($r_1)){			    $r['available_branches'] = unserialize($r['available_branches']);			    if($r['available_branches']){					$r['available_branches2'] = join(',',array_keys($r['available_branches']));				}								/*if(BRANCH_CODE!='HQ'&&$r['branch_id']!=$sessioninfo['branch_id']){					if(!$r['available_branches'][$sessioninfo['branch_id']])    continue;				}else{		            if($branch_id){						if($branch_id>0){   // single branch							if(!$r['available_branches'][$branch_id])    continue;						}else{  // branch group							$bgid = abs($branch_id);						    $bid_list = array();							foreach($branches_group['items'][$bgid] as $bid=>$b){								if($r['available_branches'][$bid]) $have_b = true;							}							if(!$have_b) continue;						}					}				}*/	            $redemption_items[] = $r;	            $row_count++;	            if(BRANCH_CODE!='HQ'&&$row_count>=$limit&&$limit)   break;			}			$con->sql_freeresult();						$smarty->assign('redemption_items',$redemption_items);		}				$smarty->assign('err',$err);		$this->display('membership.redemption_setup.list.tpl');	}		function ajax_save_item(){        global $con, $smarty, $sessioninfo, $LANG, $config;                $item_array = $_REQUEST['item_array'];        $item_status = $_REQUEST['item_status'];        $item_confirm = $_REQUEST['item_confirm'];        $available_branches = $_REQUEST['available_branches2'];        $point = $_REQUEST['point'];        $cash = $_REQUEST['cash'];        $receipt_amount = $_REQUEST['receipt_amount'];		$item_sku_item_code = $_REQUEST['item_sku_item_code'];		$item_sku_item_id = $_REQUEST['item_sku_item_id'];		$valid_date_from = $_REQUEST['valid_date_from'];		$valid_date_to = $_REQUEST['valid_date_to'];		$item_is_delete = $_REQUEST['item_is_delete'];		$receipt_date_from = $_REQUEST['receipt_date_from'];		$receipt_date_to = $_REQUEST['receipt_date_to'];		$use_curr_date = $_REQUEST['use_curr_date'];		$voucher_value = $_REQUEST['voucher_value'];		$deleted_items = array();				if(!$item_array)    die($LANG['REDEMPTION_NO_ITEM_UPDATE']);		// check deleted_item		if($item_is_delete){			foreach($item_is_delete as $key=>$is_delete){				if($is_delete){					unset($item_array[$key]);					$deleted_items[] = $key;				}			}		}				// check point and receipt		foreach($item_array as $key=>$v){			list($bid,$item_id) = explode("_",$key);			// if found it is using config and the item is confirm or expired, do reset since not going to update or compare them			if(((($item_status[$key] && $item_confirm[$key]) || (!$item_status[$key] && $item_confirm[$key]))) && $config['membership_redemption_use_enhanced'] || ($bid != $sessioninfo['branch_id'] && BRANCH_CODE != 'HQ')){				unset($item_array[$key]);				continue;			}			$scode = $item_sku_item_code[$key];			$sid = $item_sku_item_id[$key];			$vc_date_from = strtotime($valid_date_from[$key]);			$vc_date_to = strtotime($valid_date_to[$key]);			$rc_date_from = strtotime($receipt_date_from[$key]);			$rc_date_to = strtotime($receipt_date_to[$key]);			// check valid date start and date end			if($valid_date_from[$key] && $valid_date_to[$key]){				if($vc_date_from>$vc_date_to){					$err[] = $scode.": Valid ".$LANG['REDEMPTION_INVALID_DATE_PROVIDED']." : ".$valid_date_from[$key]." ~ ".$valid_date_to[$key];				}			}						// check must be active item and overdue valid date end			if($item_status[$key] && $valid_date_to[$key]){				if($valid_date_to[$key] < date("Y-m-d")){					$err[] = $scode.": Valid Date End ".$LANG['REDEMPTION_DATE_OVERDUE']." : ".$valid_date_to[$key];				}			}			// check both empty			if(mi($point[$key])<=0&&mf($receipt_amount[$key])<=0){				$err[] = $scode.": ".$LANG['REDEMPTION_INVALID_POINT'];				continue;			}						// check if got receipt amount but without receipt date to			if(!$use_curr_date[$key] && $receipt_amount[$key] != 0 && !$receipt_date_to[$key]){				$err[] = $scode.": ".$LANG['REDEMPTION_EMPTY_RECEIPT_DATE_TO'];				continue;			}			// check both empty			if(mf($cash[$key])<0){				$err[] = $scode.": ".$LANG['REDEMPTION_INVALID_CASH'];				continue;			}			// check for item status			if($item_status[$key] && (mi($point[$key])==0 && mf($cash[$key])==0 && mf($receipt_amount[$key])==0)){				$err[] = $scode.": ".$LANG['REDEMPTION_INVALID_STATUS'];				continue;			}						// check receipt date start and date end			if($receipt_date_from[$key] && $receipt_date_to[$key]){				if($rc_date_from>$rc_date_to){					$err[] = $scode.": Receipt ".$LANG['REDEMPTION_INVALID_DATE_PROVIDED']." : ".$receipt_date_from[$key]." ~ ".$receipt_date_to[$key];				}			}			// check overdue valid date end			if($receipt_date_to[$key]){				if($receipt_date_to[$key] < date("Y-m-d")){					$err[] = $scode.": Receipt Date End ".$LANG['REDEMPTION_DATE_OVERDUE']." : ".$receipt_date_to[$key];				}			}			// check duplicate sku id and point			$con->sql_query("select count(*) from membership_redemption_sku where branch_id=$bid and sku_item_id=$sid and point=".mi($point[$key])." and id<>$item_id and valid_date_to	> CURDATE()") or die(mysql_error());			if($con->sql_fetchfield(0)>0)   $err[] = $scode.": ".$LANG['REDEMPTION_DUPLICATE_SKUID_AND_POINT'].": point ".mi($point[$key]);			//if($sessioninfo['u'] == 'wsatp') print "select count(*) from membership_redemption_sku where branch_id=$bid and sku_item_id=$sid and point=".mi($point[$key])." and id<>$item_id and valid_date_to	> CURDATE()"."<br />";			// if found it is using config and the item is confirm or expired, do reset since not going to update them		}        // check saving items        if(BRANCH_CODE!='HQ'){  // can only save own items			foreach($item_array as $key=>$v){				list($bid,$item_id) = explode("_",$key);				if($bid!=$sessioninfo['branch_id']){					$scode = $item_sku_item_code[$key];					$err[] = $scode.": ".$LANG['REDEMPTION_CANNOT_UPDATE_ITEM_IN_OTHER_BRANCH'];				}			}		}		if($err){		    print "<ul>";			foreach($err as $e){				print "<li>$e</li>";			}			print "</ul>";			exit;		}		// update items		foreach($item_array as $key=>$v){			list($bid,$item_id) = explode("_",$key);			$upd = array();			//$upd['active'] = mi($active[$key]);			$upd['active'] = mi($item_status[$key]);						// if found no config, means use as previous one			if($upd['active'] > 0 && !$config['membership_redemption_use_enhanced']){				$upd['confirm'] = 1;			}else{				$upd['confirm'] = 0;			}						$upd['point'] = mi($point[$key]);			$upd['cash'] = mf($cash[$key]);			$upd['receipt_amount'] = mf($receipt_amount[$key]);			$upd['valid_date_from'] = $valid_date_from[$key];			$upd['valid_date_to'] = $valid_date_to[$key];			$upd['receipt_date_from'] = $receipt_date_from[$key];			$upd['receipt_date_to'] = $receipt_date_to[$key];			$upd['use_curr_date'] = mi($use_curr_date[$key]);			$ab = explode(',', $available_branches[$key]);			$upd['available_branches'][$bid] = 1;			foreach($ab as $ab_bid){			    if($ab_bid)	$upd['available_branches'][$ab_bid] = 1;			}			$upd['available_branches'] = serialize($upd['available_branches']);			$upd['timestamp'] = 'CURRENT_TIMESTAMP';			if($voucher_value[$key]){				$upd['voucher_value'] = $voucher_value[$key];				$upd['is_voucher'] = true;			}else{				$upd['voucher_value'] = "";				$upd['is_voucher'] = false;			}			$con->sql_query("update membership_redemption_sku set ".mysql_update_by_field($upd)." where branch_id=$bid and id=$item_id") or die(mysql_error());		}		log_br($sessioninfo['id'], 'Redemption',0, "Update Setup Item");				// delete items		if($deleted_items){			foreach($deleted_items as $key){                list($bid,$item_id) = explode("_",$key);                $sid = $item_sku_item_id[$key];                $con->sql_query("delete from membership_redemption_sku where branch_id=$bid and id=$item_id");                log_br($sessioninfo['id'], 'Redemption',$sid, "Delete Setup Item (Branch#$bid, SKU Item ID#$sid");			}		}				print "OK";	}		function ajax_add_item_row(){        global $con, $smarty, $sessioninfo, $LANG;		$str = '';				if (!$_REQUEST['sku_code_list'])		{			print $LANG['PROMO_SELECT_SKU'];			exit;		}				foreach($_REQUEST['sku_code_list'] as $sku_item_id)		{		    $con->sql_query("select * from sku_items where id=".mi($sku_item_id)) or die(mysql_error());		    $r = $con->sql_fetchrow();			if (!$r)    continue;   // no this sku item id						$upd = array();			$upd['branch_id'] = $sessioninfo['branch_id'];			$upd['sku_item_id'] = $sku_item_id;			$upd['available_branches'] = serialize(array($sessioninfo['branch_id']=>1));			$upd['added'] = 'CURRENT_TIMESTAMP';			$upd['created_by'] = $sessioninfo['id'];			$con->sql_query("insert into membership_redemption_sku ".mysql_insert_by_field($upd)) or die(mysql_error());			$item_id = $con->sql_nextid();						$sql = "select if(sp.price is null, si.selling_price, sp.price) as selling_price, mrs.*, si.sku_item_code,sc.grn_cost,			 	sc.qty, si.id as sku_item_id, si.description, ucase(uc.u) as create_user				,branch.code as bcode				from membership_redemption_sku mrs				left join sku_items si on mrs.sku_item_id = si.id				left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = ".$sessioninfo['branch_id']."				left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = ".$sessioninfo['branch_id']."				left join branch on branch.id=mrs.branch_id				left join user uc on mrs.created_by = uc.id				where mrs.id=$item_id and mrs.branch_id=".$sessioninfo['branch_id'];			$con->sql_query($sql) or die(mysql_error());			$item = $con->sql_fetchrow();			$item['available_branches'] = unserialize($item['available_branches']);			if($item['available_branches']){				$item['available_branches2'] = join(',',array_keys($item['available_branches']));			}			$smarty->assign('item',$item);			$str .= $smarty->fetch('membership.redemption_setup.list.row.tpl');			log_br($sessioninfo['id'], 'Redemption',$sku_item_id, "Setup New Item (Branch#".$sessioninfo['branch_id'].", SKU Item ID#$sku_item_id)");		}		print trim($str);	}}$membership_redemption = new MembershipRedemption ('Redemption Item Setup');?>