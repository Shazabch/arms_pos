<?
/*
REVISION HISTORY
================
11/22/2007 11:17:00 AM gary
- remove vendor filter

10/25/2010 2:32:42 PM PM Justin
- Fixed the missing of sum up amount for extra items.

6/24/2011 4:17:08 PM Andy
- Make all branch default sort by sequence, code.

9/15/2011 3:45:43 PM Justin
- Fixed the bugs where GRA missing by branch if filter branch as "All".
- Modified the status as below:
  => Saved - for those saved in GRA.
  => Completed (Not Returned) - for those already printed out the checklsit and awaiting for return.
  => Returned - for those already confirmed to return.
  
4/19/2012 9:48:59 AM Alex
- fix returned bugs while filtering => show_report()

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/9/2013 5:53 PM Justin
- Enhanced to  have checking on approved = 1.

4/20/2015 4:41 PM Justin
- Enhanced to have GST information.

5/21/2015 10:17 AM Justin
- Bug fixed sql errors.

11/30/2015 9:43 PM DingRen
- when calculate amount_gst, gst and amount and change to decimal 2 for extra

02/29/2016 09:46 Edwin
- Bugs fixed on status filter in GRA summary

02/29/2016 16:16 Edwin
- Bug fixed on return date prompt out when GRA is not completed

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/12/2016 15:30 Edwin
- Bugs fixed on status filter in GRA summary
- Bugs fixed on gst amount does not round off to 2 decimal place.

6/1/2017 3:04 PM Justin
- Bug fixed on terminated GRA will still show out when filter status "Completed".

6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

10/24/2017 4:23 PM Justin
- Bug fixed on system will not trigger any GRA if selecting same date for both date from/to.

5/7/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/12/2018 11:38 AM Justin
- Enhanced to have Rounding Adjust.

5/16/2019 11:18AM William
- Pickup report_prefix for enhance "GRA".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$branch_id = $sessioninfo['branch_id'];

$smarty->assign("PAGE_TITLE", "GRA Listing");

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
	}
}

init_selection();

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
$smarty->display("goods_return_advice.listing_report.tpl");

function show_report(){
	global $con, $sessioninfo, $smarty;
	$filter = array();
	
	if (isset($_REQUEST['branch_id'])){
	    if ($_REQUEST['branch_id']>0) $filter[] = "gra.branch_id = " .mi($_REQUEST['branch_id']);
	}
	else{
	    $filter[] = "gra.branch_id = $sessioninfo[branch_id]";	
	}

	$from_date=$_REQUEST['from'];
	//$to_date=date("Y-m-d",strtotime("+1 day",strtotime($_REQUEST['to'])));
	$to_date = $_REQUEST['to']." 23:59:59";
	
	if ($_REQUEST['department_id']) $filter[] = "gra.dept_id = ".mi($_REQUEST['department_id']);
	//if ($_REQUEST['vendor_id']) $filter[] = "gra.vendor_id = ".mi($_REQUEST['vendor_id']);
	$filter[] = "category.id in (".join(",",array_keys($sessioninfo['departments'])).")";
	
	if ($_REQUEST['sku_type']) $filter[] = "gra.sku_type = ".ms($_REQUEST['sku_type']);

	if (strcmp($_REQUEST['returned'],'')){
		//if($_REQUEST['returned']) $having = "having not_allow_checkout = 0";
		
		if($_REQUEST['returned'] == 0 || $_REQUEST['returned'] == 1){ 
			if($_REQUEST['returned'] == 0) {
				$filter[] = "gra.status in (0,2)";  // is saved and waiting approval
				$filter[] = "gra.approved = 0";
			}
			else {
				$filter[] = "gra.status = 0";	// is approved
				$filter[] = "gra.approved = 1"; 
			}
			$returned = 0;
		 	$filter[] = "gra.added between ".ms($from_date)." and ".ms($to_date);
		}elseif($_REQUEST['returned'] == 2){ // is completed
			$returned = 1;
			$filter[] = "gra.status = 0 and gra.approved = 1"; 
			$filter[] = "gra.return_timestamp between ".ms($from_date)." and ".ms($to_date);
		}elseif($_REQUEST['returned'] == 3){ // un-checkout
			$returned = 0;
			$filter[] = "gra.status in (0,2) and gra.returned = 0";
			$filter[] = "gra.added between ".ms($from_date)." and ".ms($to_date);
		}
		
		$filter[] = "gra.returned = ".mi($returned);
	}else{
		$filter[] = "((gra.status in (0,2) and gra.returned = 0 and gra.approved in (0,1) and gra.added between ".ms($from_date)." and ".ms($to_date).") or (gra.status = 0 and gra.returned = 1 and gra.approved = 1 and gra.return_timestamp between ".ms($from_date)." and ".ms($to_date)."))";
	}
	$where = join(" and ", $filter);
	//print_r($where); exit;
	$r=$con->sql_query("select gra.*, gra.extra_amount, (gra.amount-gra.extra_amount) as amount,
						vendor.description as vendor, category.description as department, b1.code as branch, 
						vendor.code as vendor_code, 
						if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
						sum(round(gi.gst, 2)) as gst,branch.report_prefix
						from gra 
						left join gra_items gi on gra.id=gi.gra_id and gra.branch_id = gi.branch_id 
						left join branch on gra.branch_id = branch.id
						left join vendor on gra.vendor_id = vendor.id
						left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
						left join category on gra.dept_id = category.id
						left join branch b1 on b1.id=gra.branch_id  
						where $where
						group by gra.id, gra.branch_id
						order by b1.sequence, b1.code, gra.id");
	
	//$r=$con->sql_query("select gra.*, gra.extra_amount, sum(gi.amount) as amount, 
	//					vendor.description as vendor, category.description as department, b1.code as branch, 
	//					count(if(gi.batchno=0, gi.id, null)) as not_allow_checkout, vendor.code as vendor_code, 
	//					if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
	//					sum(round(gi.gst, 2)) as gst
	//					from gra 
	//					left join gra_items gi on gra.id=gi.gra_id and gra.branch_id = gi.branch_id
	//					left join vendor on gra.vendor_id = vendor.id
	//					left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
	//					left join category on gra.dept_id = category.id
	//					left join branch b1 on b1.id=gra.branch_id  
	//					where $where
	//					group by gra.id, gra.branch_id
	//					$having
	//					order by b1.sequence, b1.code, gra.id");

	if (!$con->sql_numrows($r)){
	    print "<p>-- No Record --</p>";
	    return;
	}
	
	$is_under_gst = $have_fc = $got_rounding_adj = 0;
	while ($gra = $con->sql_fetchassoc($r)){
		
		$gra['misc_info'] = unserialize($gra['misc_info']);
		$gra['extra']= unserialize($gra['extra']);
		$gra['amount'] = round($gra['amount'] + $gra['extra_amount'], 2);
		$form['vendor']=$gra['vendor'];
		$form['branch']=$gra['branch'];
		
		if($gra['is_under_gst']){
			$is_under_gst = 1;
			if($gra['extra']){
				foreach($gra['extra']['code'] as $idx=>$code){
					$qty = $gra['extra']['qty'][$idx];
					$cost = $gra['extra']['cost'][$idx];
					$gst_rate = $gra['extra']['gst_rate'][$idx];

					$row_amount=$qty*$cost;
					$row_gst_amt=round($row_amount * ((100+$gst_rate)/100), 2);
					$row_amount=round($row_amount, 2);
					$extra_gst=$row_gst_amt-$row_amount;

					//$extra_gst = round($qty*$cost*$gst_rate/100, 2);
					$gra['gst'] += $extra_gst;
				}
			}
		}else $gra['gst'] = 0;
		
		if($gra['returned'] == 0)
			$gra['return_timestamp'] = 0;
		
		if($gra['currency_code']) $have_fc = 1;
		
		if($gra['rounding_adjust']) $got_rounding_adj = 1;
		
		$gra_list[]=$gra;
	}
	$con->sql_freeresult($r);

	$smarty->assign("form", $form);
	$smarty->assign("gra", $gra_list);
	$smarty->assign("have_fc", $have_fc);
	$smarty->assign("is_under_gst", $is_under_gst);
	$smarty->assign("got_rounding_adj", $got_rounding_adj);
}

function init_selection(){
	global $con, $sessioninfo, $smarty;
	// manager and above can see all department
	if ($sessioninfo['level'] < 9999){
		if (!$sessioninfo['departments'])
			$depts = "id in (0)";
		else
			$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
	}
	else{
		$depts = 1;
	}
	// show department option
	$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
	$smarty->assign("dept", $con->sql_fetchrowset());
	
	$con->sql_query("select id, code from branch where active=1 order by sequence,code");
	$smarty->assign("branch", $con->sql_fetchrowset());
	// get vendor
	$con->sql_query("select distinct vendor.id, vendor.description from grr left join vendor on grr.vendor_id = vendor.id ".(BRANCH_CODE == 'HQ' ? "" : " where grr.branch_id = $sessioninfo[branch_id]")." order by description");
	$smarty->assign("vendor", $con->sql_fetchrowset());
	
	$con->sql_query("select UPPER(code) as code from sku_type where active=1 order by code");
	$smarty->assign("sku_type", $con->sql_fetchrowset());
}
?>
