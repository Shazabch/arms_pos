<?php
/*
Revision History
================
4/20/07 3:31:00 PM   yinsee
- add branch_id column in PM table

5/21/2007 4:36:44 PM yinsee
- add branch autologin for PO approval and GRN verification

9/21/2007 12:11:39 PM gary
 - add grr incomplete notification for 3 days inactive from rcv_date.

9/27/2007 2:40:00 PM gary
- added inactive users notification.

11/14/2007 5:54:00 PM yinsee
- add price change notification

11/15/2007 12:41:22 PM gary
- change inactive users notification.

11/23/2007 10:40:27 AM gary
-remove inactive users notification.

11/23/2007 5:11:17 PM gary
- add adjustment notification.

12/3/2007 1:06:28 PM gary
- modify adjustment notification

12/3/2007 5:56:01 PM gary
- modify do notification.

1/12/2009 5:30:34 PM Andy
- add Consignment Invoice notification.

4/1/2009 2:35:00 PM Andy
- modify SKU_PRICE_CHANGE notification

9/7/2009 3:08:46 PM yinsee
- new sku notification

10/1/2009 10:02 AM Andy
- add DO Request notification

11/16/2009 10:29:52 AM Andy
- fix to only get active=1 DO

1/18/2010 5:45:21 PM Andy
- approval order changes

3/16/2010 4:03:54 PM Andy
- Add member summary at notification

4/14/2010 3:07:22 PM Andy
- Membership Summary Notification add branch filtering

5/31/2010 2:54:17 PM Andy
- Disable Cosignment Lost/Over Invoice
- Add 2 New Module: Credit Note and Debit Note.(Need Privileges to Access)
- Add 2 New Approval Flow : Credit Note Approval and Debit Note Approval.(Need Privileges to Access)
- Add New Consignment Discount Format: Masterfile Branch Trade Discount and Consignment Lost/Over Discount can now accept secondary discount percent using "+". e.g(50+10)(Need Config)
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.
- Monthly Report lost qty will change to generate CN, over qty will change to generate DN.
- Fix monthly report generate invoice/adjustment cannot show stock balance bugs.
- Fix javascript and smarty rounding bugs.
- Add checking for allowed future/passed DO Date limit.

7/13/2010 10:37:40 AM yinsee
- fix OVERDUE PO delivered date

7/19/2010 3:45:55 PM Andy
- Add counter collection un-finalized notice at main page. (Need privilege to see)

7/27/2010 10:49:45 AM Andy
- Exclude today date for unfinalized pos notification.

7/27/2010 2:18:23 PM Alex
- add checking active in grr while show notification

8/18/2010 11:38:28 AM Justin
- Created a new notification feature on home page to allow user view to be expired redemption items (need privilege "NT_RDM_ITEM").
- Added the redemption notification on home page (need config['membership_redemption_use_approval']).

8/24/2010 3:11:36 PM Justin
- Created Redemption Item Approval notification.
- Required MEMBERSHIP_ITEM_CFRM to view and approve membership redemption item.
- Amended the notification of Redemption Item Approval to filter based on available branch.

9/9/2010 5:47:42 PM Justin
- Removed the privilege REDEMPTION_CANCEL_RE from redemption notification

9/29/2010 3:48:33 PM Justin
- Fixed the wrong days left calculation for redemption item.

10/25/2010 4:43:32 PM Justin
- Fixed the sql error when no set config for redemption item expire days.

10/28/2010 4:48:45 PM Justin
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

11/1/2010 2:56:45 PM Justin
- Solved the bugs where approval cannot view the Redemption Verification list.

11/2/2010 11:13:25 AM Alex
- fix last_update ambiguous field

1/12/2011 6:07:18 PM Andy
- change column lastlogin to use lastlogin from table user_status.

3/18/2011 10:43:08 AM Andy
- Hide inactive branch from un-finalize POS notification.

5/24/2011 5:11:36 PM Andy
- Add show GRN Distribution Status at notification page.
- Add sql_freeresult to some notification query. (alex)

6/6/2011 3:00:21 PM Justin
- Added new Approval Flow notification for GRN Future.

7/6/2011 12:10:47 PM Andy
- Change split() to use explode()

9/6/2011 2:38:39 PM Andy
- Add show notification for Stock Reorder.

9/30/2011 12:11:43 PM Justin
- Modified to have GRN Confirmation query.

10/10/2011 6:28:11 PM Justin
- Fixed the GRR notification not to pick up those IBT items.

11/17/2011 2:57:33 PM Andy
- Fix PO Overdue to only show those po_option=0

12/12/2011 1:24:00 PM Kee Kee
- Show Temp Price Items

2/7/2012 3:42:59 PM Alex
- clear unused variable and add con->sql_freeresult()
- add invalid SKU data

2/9/2012 11:36:25 AM Andy
- Reduce SQL query workload when checking unfinalize pos.

4/17/2012 5:08:32 PM Justin
- Added new approval notification "Future Change Price".

6/14/2012 4:56:20 PM dingren
- Added new approval notification "e-Form".

7/24/2012 4:30 PM Andy
- Fix disk space checking missing.

8/10/2012 11:13 AM Andy
- Add purchase agreement approval notification.

1/22/2013 2:35 PM Justin
- Enhanced not to show GRN distribution while customer only have 1 branch only.

7/10/2013 11:06 AM Justin
- Added GRA approval notification.

7/29/2013 5:07 PM Andy
- Fix Batch Price Change notification to load user based on approval sequence.
- Fix Membership Redemption Verify show wrong Approval sequence in Waiting for Approval list.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

12/31/2013 2:39 PM Andy
- Change to check finalized=0 for those sales not yet finalized.

2/11/2014 2:45 PM Justin
- Enhanced to order PO overdue info to sort by latest PO date.

2/17/2014 2:57 PM Justin
- Enhanced GRR status to sort by latest receive date to older.

2/5/2015 5:37 PM Andy
- Optimise some query and put some privilege checking for some notification.

2/11/2015 11:59 AM Andy
- Change check unfinalize pos to have min date which is -year from current date.

2/26/2015 11:18 AM Andy
- Fix sku approval loading bug.

4/3/2015 3:20 PM Andy
- Optimise load unfinalise POS query.

4/6/2015 1:44 PM Andy
- Fix future price approval notification.

4/23/2015 3:52 PM Andy
- Enhanced GRN Distribution Status to load only limited data, user will need to click on link to view more data.

6/1/2015 1:26 PM Justin
- Enhanced to show total count by different status of GRN documents.

6/23/2015 5:29 PM Eric
- Login Notification Enhancement 
- Move most of the code to ajax_notification.php so the left and right sidebar will load with ajax

6/26/2015 6:26 PM Andy
- Fix SKU Approval cannot show.

11/25/2015 1:20 PM Qiu Ying
- pm check opened, bold if not yet opened

2017-08-24 14:32 PM Qiu Ying
- Enhanced to move pm coding to pm.php

9/27/2018 3:20 PM Andy
- Add Annoucement list can load from file.

9/20/2019 9:35 AM Andy
- Enhanced to ignore dev/loop in free space monitor.
*/
$maintenance->check(208);
	//GET USERS > 1 MONTH DID NOT LOGIN ARMS
	/*
	if($sessioninfo['level']>=9999){
		$q_user=$con->sql_query("select user.id, u, us.lastlogin, branch.code as b_code
from user
left join user_status us on us.user_id=user.id
left join branch on default_branch_id=branch.id
where user.active AND NOT template AND us.lastlogin< DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
group by b_code, user.id");
		while($row=$con->sql_fetchrow($q_user)){
			$users[$row['b_code']][]=$row;
		}
		//echo"<pre>";print_r($users);echo"</pre>";
		$smarty->assign("inactive_users",$users);
	}
	*/

	// Load Announcement List
	$appCore->announcementManager->checkAnnouncement(array('user_id'=>$sessioninfo['id']));

	$depts = "dept_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";

	if (privilege('GRR_NOTIFY')){
		$where = "grr.branch_id = $sessioninfo[branch_id] and grr.status <> 2 and grr.vendor_id != 0 group by grr.id having po_count>po_used_count or (po_count=0 and inv_count>inv_used_count) and grr.active=1";

		$result = $con->sql_query("select sum(if(type='PO',1,0)) as po_count,sum(if(type='PO' and grn_used=1,1,0)) as po_used_count, sum(if(type='INVOICE',1,0)) as inv_count,sum(if(type='INVOICE' and grn_used=1,1,0)) as inv_used_count, grr.*, category.id as dept_id, vendor.description as vendor
from grr_items
left join grr on grr_items.grr_id = grr.id and grr_items.branch_id = grr.branch_id
left join category on category.id=grr.department_id
left join vendor on vendor.id=grr.vendor_id
where grr.rcv_date< DATE_SUB(CURDATE(),INTERVAL $config[grr_incomplete_notification] DAY) and $where and $depts
order by grr.rcv_date desc");

		$grr_notify=$con->sql_fetchrowset($result);
		$smarty->assign("grr_notify",$grr_notify);
		unset($where,$grr_notify);
		$con->sql_freeresult($result);
	}

	if (privilege('MST_SKU_APPROVAL'))
	{

		// check notifications and run
		// aproval history status:
		// status = 0 - no body touch it yet
		// status = 1 - someone approved
		// status = 2 - someone rejected
		// status = 3 - pending
		// status = 4 - dead
		//$con->sql_query("select count(*) from sku left join approval_history on sku.approval_history_id = approval_history.id where approvals like '%|$sessioninfo[id]|%' and sku.status <> 2 order by sku.timestamp");

		$result = $con->sql_query("select approval_history.*, category.description as category_desc, brand.description as brand_desc, category.tree_str, sku.id, branch.code as apply_branch, user.u 
		from (
				(
					(
						approval_history left join approval_flow on approval_history.approval_flow_id = approval_flow.id
					) 
					left join sku on sku.approval_history_id = approval_history.id
				) 
				left join category on sku.category_id = category.id
			) 
			left join brand on sku.brand_id = brand.id 
			left join user on sku.apply_by = user.id 
			left join branch on sku.apply_branch_id = branch.id 
			where approval_history.status = 3 and approval_flow.type = 'SKU_APPLICATION' and 
			(
				(
					approval_flow.aorder = 1 and approval_history.approvals like '|$sessioninfo[id]|%'
				) 
				or 
				(
					approval_flow.aorder = 0 and approval_history.approvals like '%|$sessioninfo[id]|%'
				)
			)");
		$arr = array();
		while ($a = $con->sql_fetchassoc($result))
		{
		    $a['department'] = get_department($a['id'], $a['tree_str']);
			array_push($arr, $a);
		}
		$smarty->assign("sku_pending", $arr);
		$con->sql_freeresult($result);
		unset($r,$arr,$a);
	}

	if (privilege('MST_SKU_APPLY'))
	{
		// check rejected approval that invovles me
	    $result = $con->sql_query("select added, category.description as category_desc, brand.description as brand_desc, category.tree_str, sku.id from (sku left join category on sku.category_id = category.id) left join brand on sku.brand_id = brand.id where apply_by = $sessioninfo[id] and status = 2");
		$arr = array();
		while ($a = $con->sql_fetchassoc($result))
		{
		    $a['department'] = get_department($a['id'], $a['tree_str']);
			array_push($arr, $a);
		}
		$smarty->assign("sku_revision", $arr);
		$con->sql_freeresult($result);
		unset($arr);
	}

	if (privilege('DO'))
	{
		// check rejected approval that invovles me
	    $result = $con->sql_query("select do.*, branch.code as branch from do
left join branch on branch.id=do.branch_id
where do.approved=0 and user_id=$sessioninfo[id] and branch_id=$sessioninfo[branch_id] and status=2 and do.active=1
order by branch.sequence,branch.code");
		$smarty->assign("do_revision", $con->sql_fetchrowset($result));
		$con->sql_freeresult($result);
	}

	if (privilege('PO'))
	{
		// check rejected approval that invovles me
	    $result = $con->sql_query("select po.*, category.description as department
from (po left join category on po.department_id = category.id)
where po.approved=0 and user_id = $sessioninfo[id] and branch_id = $sessioninfo[branch_id] and status = 2 and po.active=1");

		$smarty->assign("po_revision", $con->sql_fetchrowset($result));
		$con->sql_freeresult($result);
	}

	if (privilege('PROMOTION'))
	{
		// check rejected promotion approval that invovles me
	    $result = $con->sql_query("select * from promotion
where approved=0 and user_id = $sessioninfo[id] and branch_id = $sessioninfo[branch_id] and status = 2 and promotion.active=1");
		$smarty->assign("promo_reject", $con->sql_fetchrowset($result));
		$con->sql_freeresult($result);
	}

	if (privilege('NT_RDM_ITEM') && $config['membership_redemption_expire_days'] && !isset($sessioninfo['departments'][0]))
	{
		// check notifications and run
		$result = $con->sql_query("select mrs.*, si.sku_item_code, if(mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00', datediff(mrs.valid_date_to, date_format(CURDATE(), '%Y-%m-%d')) + 1, '') as days_left from membership_redemption_sku mrs left join sku_items si on si.id = mrs.sku_item_id where mrs.active = 1 and mrs.valid_date_to != '' and mrs.valid_date_to != '0000-00-00' having days_left <= $config[membership_redemption_expire_days] and days_left > 0 order by mrs.id");

		while($r = $con->sql_fetchrow($result)){
			$r['available_branches'] = unserialize($r['available_branches']);
			if(BRANCH_CODE!='HQ'&&$r['branch_id']!=$sessioninfo['branch_id']){
				if(!$r['available_branches'][$sessioninfo['branch_id']])    continue;
			}

            $redemption_items[] = $r;
		}
		$smarty->assign("redemption_items", $redemption_items);
		$con->sql_freeresult($result);
	}

	// GRN Distribution Status
	if (BRANCH_CODE == "HQ" && privilege('NT_GRN_DISTRIBUTE')){
		$q1 = $con->sql_query("select count(*) as ttl_branch from branch");
		$branch_count = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		// check if branch not more than 1, then do not show distribution
		if($branch_count['ttl_branch'] > 1){
			$grn_deliver_monitor = array();
			$monitor_after_day = mi($config['grn_distribution_monitor_after_day']);
			if(!$monitor_after_day)	$monitor_after_day = 3;
			$grn_deliver_monitor['info']['monitor_after_day'] = $monitor_after_day;

			$date_filter = date("Y-m-d", strtotime("-$monitor_after_day day"));

			$min_do_qty_percent = mi($config['grn_distribution_monitor_min_do_qty_percent']);
			if(!$min_do_qty_percent)	$min_do_qty_percent = 50;
			$grn_deliver_monitor['info']['min_do_qty_percent'] = $min_do_qty_percent;

			// get all GRN need to monitor
			$q_grn = $con->sql_query("select grr.rcv_date,grn.branch_id,grn.id
				from grn
				left join grr_items gi on gi.branch_id=grn.branch_id and gi.id=grn.grr_item_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				where grn.branch_id=1 and grn.need_monitor_deliver=1 and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date<".ms($date_filter)." order by rcv_date desc");
			// loop for each GRN
			$do_left_qty = array();
			$limit_count = 10;
			
			while($grn = $con->sql_fetchassoc($q_grn)){
				$q_grni = $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, grn_items.sku_item_id
				from grn_items
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				left join sku_items on grn_items.sku_item_id = sku_items.id
				where grn_items.branch_id = ".mi($grn['branch_id'])." and grn_items.grn_id=".mi($grn['id'])."
				group by grn_items.sku_item_id
				");
				// loop for grn items
				$total_grn_qty = 0;
				$sid_list = array();
				$grn_items = array();
				while($grni = $con->sql_fetchassoc($q_grni)){
					$sid = mi($grni['sku_item_id']);
					$total_grn_qty += $grni['qty'];
					$sid_list[] = $sid;
					$grn_items[$sid] = $grni;
				}
				$con->sql_freeresult($q_grni);

				if(!$sid_list || !$total_grn_qty){
					$con->sql_query("update grn set need_monitor_deliver=0 where branch_id=".mi($grn['branch_id'])." and id=".mi($grn['id']));
					continue;
				}

				$q_di = $con->sql_query("select di.id, di.sku_item_id, ((di.ctn*uom.fraction)+di.pcs) as do_qty
	from do_items di
	left join do on do.branch_id=di.branch_id and do.id=di.do_id
	left join uom on uom.id=di.uom_id
	where do.branch_id=".mi($grn['branch_id'])." and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and do.do_date>=".ms($grn['rcv_date'])." and di.sku_item_id in (".join(',', $sid_list).")");
				$total_do_qty = 0;
				while($di = $con->sql_fetchassoc($q_di)){
					$sid = mi($di['sku_item_id']);
					$left_qty = $grn_items[$sid]['qty'] - $grn_items[$sid]['do_qty'];

					// it is due to other grn have taken some do_qty
					if(isset($do_left_qty[$di['id']]))	$di['do_qty'] = $do_left_qty[$di['id']];
					if(!$di['do_qty'])	continue;	// already used all qty to DO

					$do_qty = 0;
					if($di['do_qty'] < $left_qty)	$do_qty = $di['do_qty'];
					else	$do_qty = $left_qty;

					$grn_items[$sid]['do_qty'] += $do_qty;
					$do_left_qty[$di['id']] = $di['do_qty'] - $do_qty;
					$total_do_qty += $do_qty;
				}
				$con->sql_freeresult($q_di);
				$grn['items'] = $grn_items;
				$grn['do_per'] = mi(($total_do_qty / $total_grn_qty) * 100);				
				if($grn['do_per'] >= $min_do_qty_percent){	// already qualified percent
					// mark as no need to monitor anymore
					$con->sql_query("update grn set need_monitor_deliver=0 where branch_id=".mi($grn['branch_id'])." and id=".mi($grn['id']));
				}else{
					if(count($grn_deliver_monitor['grn'])>=$limit_count){
						$grn_deliver_monitor['have_more'] = 1;
						break;	// save loading not to load too many
					}else{
						$grn_deliver_monitor['grn'][] = $grn;
					}
				}
			}
			$con->sql_freeresult($q_grn);
			//print_r($do_left_qty);
			//print_r($grn_deliver_monitor);			
			$smarty->assign('grn_deliver_monitor', $grn_deliver_monitor);
			unset($di,$grn,$grn_items,$do_left_qty,$grn_deliver_monitor);
		}
	}

	if($sessioninfo['level']>=1000){
        // show disk space status
        foreach(preg_split('/\n/',`df | grep "^/dev"`) as $line)
        {
			if ($line[0]){
				if(preg_match('/^\/dev\/loop/', $line))	continue;
				
				$d[] = preg_split('/\s+/', $line);
			}
                
        }
        $smarty->assign("disk_space", $d);
        unset($d);
        }

	

  	//check user permission
  	//$result = $con->sql_query("select * from branch_approval_history where flow_approvals like '%|$sessioninfo[id]|%' and branch_id = ".ms($sessioninfo['branch_id']));

	
	// notification for stock reorder pregen sku
	if(privilege('NT_STOCK_REORDER')){
		$result = $con->sql_query("select vsr.*,v.code as vcode,v.description as v_desc,c.description as c_desc from
vendor_stock_reorder_sku vsr
left join vendor v on v.id=vsr.vendor_id
left join category c on c.id=vsr.category_id
where vsr.branch_id=".mi($sessioninfo['branch_id'])."
order by v_desc,c_desc");
		$stock_reorder_data = array();
		while($r = $con->sql_fetchassoc($result)){
			$r['sku_id_list'] = unserialize($r['sku_id_list']);
			$stock_reorder_data[$r['vendor_id']][$r['category_id']] = $r;
		}
		$con->sql_freeresult($result);
		$smarty->assign('stock_reorder_data', $stock_reorder_data);
		unset($r,$stock_reorder_data);
	}

	//offline docs
	/*if (true) { //any privilege???

		$uid = $sessioninfo[id];
		$off_docs = array();
		$sqls = array();
		
		$sqls['adj'] = "select id, branch_id, added from adjustment where status=0 and approved=0 and active=1 and user_id=$uid and offline_id>0";
		$sqls['sku'] = "select id, added from sku where status=0 and active=1 and apply_by=$uid and offline_id>0";
		$sqls['do'] = "select id, branch_id, do_type, added from do where status=0 and approved=0 and active=1 and user_id=$uid and offline_id>0";
		$sqls['po'] = "select id, branch_id, added from po where status=0 and approved=0 and active=1 and user_id=$uid and offline_id>0";
		$sqls['gra'] = "select id, branch_id, added from gra where status=0 and returned=0 and approved=0 and user_id=$uid and offline_id>0";
		$sqls['grn'] = "select id, branch_id, added from grn where status=0 and approved=0 and active=1 and user_id=$uid and offline_id>0";
		$sqls['grr'] = "select id, branch_id, added from grr where status=1 and active=1 and user_id=$uid and offline_id>0";
		
		foreach ($sqls as $k => $s) {
			$q = $con->sql_query($s);
			while ($r = $con->sql_fetchassoc($q)) {
				$off_docs[$k][] = $r;
			}
		}
		$smarty->assign("off_docs", $off_docs);
	}*/
	
?>
