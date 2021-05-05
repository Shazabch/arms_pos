<?
/*
Revision History
================
19 Apr 2007 - yinsee
- allow delete of sku for non owner
- add return_type column for gra items

7/2/2007 6:15:37 PM -yinsee
- add more condition to gra_id updating when save (attempt to solve the bug where items from other GRA become the items in newly saved GRA)

7/23/2007 10:00:57 AM yinsee
- fix bug where print wont work if no ARMS item in GRA

9/21/2007 10:41:59 AM yinsee
- only show total in last page

1/30/2008 11:14:21 AM yinsee
- recode the ajax_get_grn_vendor_list to show only one entry (last GRN) from each vendor

2/19/2008 3:55:22 PM gary
- separate out the NONSKU and SKU items with pagination.

6/22/2009 5:48 PM Andy
- reset total qty and total amt when print different copy

6/23/2009 4:43 PM Andy
- add checking on $config['gra_'.$_REQUEST['a'].'_alt_print_template'] to allow custom print
	- REQUEST['a'] maybe 'view' or 'print'
	
30/6/2009 6:07:39 PM yinsee
- add $config['gra_print_item_per_page'] to control page print-out size

2/7/2009 12:06 PM jeff
- fix grand total when print gra checkout
- fix gra printing bugs (when print gra with item not in sku all content wrong.)

8/28/2009 10:16:37 AM Andy
- edit printing setting, default item per page to 14
- fix some printing bug

11/17/2009 4:06:50 PM edward
- edit log sentence.

7/6/2010 4:35:04 PM Justin
- Set the query to capture current selling price and insert into GRA item table.
- Provide different selling prices as usual follow by:
  -> Get from SKU item price table, as if it is null then taken from SKU items table.
- Modified the query to take selling price directly from GRA items.

11/15/2010 11:48:00 AM Justin
- Added GRA's info to take branch code.

1/27/2011 5:06:43 PM Andy
- Fix GRA printing show wrong price type.

1/28/2011 10:41:05 AM Andy
-Fix GRA print checklist show wrong price type.

4/29/2011 4:43:50 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

6/24/2011 12:47:48 PM Andy
- Change when add gra item, the cost getting method if not found grn and po, will try to get the latest cost before using master cost. 

8/2/2011 3:08:22 PM Justin
- Modified the Ctn and Pcs round up to base on config.
- Amended the global cost config if not found, preset it as 3 decimal points.

8/17/2011 11:40:21 AM Justin
- Added pagination for GRA items.
- Fixed pagination bugs after added new GRA items.

8/18/2011 11:10:32 AM Justin
- Fixed the delete function not working.

10/28/2011 6:06:41 PM Andy
- Add can generate GRA by import CSV.

1/30/2012 9:43:29 AM Andy
- Try change insert gra_items query when cancel item, see whether can fix replica error.

2/29/2012 4:29:10 PM Alex
- add ajax_scan_grn_barcode() for scanning grn barcode

4/23/2012 3:00:35 PM Alex
- add packing uom code

7/25/2012 12:25 PM Justin
- Added to pick up packing UOM fraction while printing report.
- Enhanced to have custom report for checklist.

8/7/2012 4:40 PM Justin
- Enhanced to capture disposal date.

9/24/2012 3:37 PM Justin
- Added to insert/update "remark".

10/8/2012 1:22 PM Justin
- Bug fixed on system always require user to key in batch no.

10/8/2012 3:37 PM Justin
- Bug fixed on query error.

2/20/2013 5:21 PM Justin
- Modified the next disposal date will add 29 days but not 1 month.

6/20/2013 2:42 PM Justin
- Bug fixed on system permanently set all items become unassigned from saved GRA while header information has been changed even user did not hit the save button.

7/2/2013 11:38 AM Justin
- Enhanced to show the list of batch no while click on print checklist.
- Enhanced to process and generate approval flow for GRA.

7/31/2013 10:25 AM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.
- Enhance GRA to have approval history when confirm and directly get approve.
- Enhance GRA to show approval history when under save.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window

10/10/2013 11:29 AM Fithri
- fix wrong amount calculation when saving gra

12/13/2013 1:24 PM Justin
- Bug fixed on gra item missing while do remove all items function.

2/18/2014 11:57 AM Justin
- Bug fixed on system will remove all items which already assigned to other GRA if found same user doing new GRA.

2/20/2014 4:58 PM Justin
- Enhanced to process GRA items under tmp table before it is update into the actual table.

2/27/2014 11:41 AM Justin
- Bug fixed on GRA generate sock3 SQL error due to combine of tmp table.
- Bug fixed on showing query errors while click on consign/outright tab for un-assigned GRA items.
- Bug fixed on showing SQL query error while delete GRA item from SKU for return.

5/29/2014 4:24 PM Justin
- Enhanced import from CSV to have few options of choosing import format and delimiter.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

3/16/2015 1:05 PM Justin
- Bug fixed on packing list number is missing while in view/edit mode.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/9/2015 3:35 PM Justin
- Enhanced to set D/N to cancel if found GRA has been reset or cancelled.

4/18/2015 4:25 PM Justin
- Enhanced to allow user maintain Inv/DO No. and Return Type for item not in ARMS.
- Enhanced to maintain GST info while add new, cancel or edit items.

4/21/2015 5:41 PM Justin
- Enhanced to use input tax as tax code instead of output tax.

4/22/2015 5:43 PM Justin
- Enhanced to always allow user to key in Inv/DO No. when add new GRA item.

4/29/2015 4:48 PM Justin
- Enhanced to use current date and branch to check GST status.

5/8/2015 9:56 AM Justin
- Enhanced to allow user maintain invoice date while add new return item.

11/30/2015 9:43 PM DingRen
- when save  recalculate amount_gst, gst and amount and change to decimal 2

12/29/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

01/04/2016 5:22PM DingRen
- recalculate gra items on save and confirm

1/19/2016 10:00 AM Andy
- Fix wrong amount for consign items.

1/21/2016 4:16 PM Qiu Ying
- Fix wrong recalculate gra items on save.

2/17/2016 11:30 AM Qiu Ying
- Fix GRA always print 1 more page
- Fix GRA printing, DN amount become wrong when amt > 1000

2/18/2016 2:30 PM Qiu Ying
- Fix print and print_checklist's is_lastpage problems

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/22/2016 11:53 AM Andy
- Change get gra items po cost to get latest po only.

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

6/1/2017 3:41 PM Justin
- Bug fixed on system never capture logs when user do GRA cancellation.

6/2/2017 11:35 AM Justin
- Bug fixed on redirect from other module will still allow user to edit and confirm even though the GRR have been cancel, approved or checkout.

6/15/2017 10:18 AM Andy
- Enhanced to get sku_id when view gra.

7/20/2017 5:49 PM Justin
- Enhanced to use tpl to show vendor list instead of using PHP to print out HTML codes.
- Enhanced to have "Show All GRN Documents" checkbox that will display all GRN documents.
- Enhanced to choose "NR" as GST code while GRN document is not under GST.

12/18/2017 3:35 PM Justin
- Enhanced to load last GRN if doesn't find any GRN from past 6 months.
- Disabled to load PO list while searching SKU for return.

4/26/2018 4:22 PM Justin
- Enhanced to have foreign currency feature.

7/6/2018 4:10 PM Justin
- Bug fixed on foreign currency list doesn't load while get errors after confirm the GRA.

10/3/2018 5:46 PM Justin
- Enhanced to hide Inv No and Date as if the GRA doesn't have any information of it.

10/23/2018 1:33 PM Justin
- Enhanced to load SKU Type list from database instead of hardcoded it.

12/26/2018 11:46 AM Justin
- Bug fixed the wrong assign of page size while printing last page.

5/16/2019 5:07 PM William
- Pickup report_prefix for enhance "GRA","GRN".
========
GRA.sku_type
outright item, use cost * qty
consign item, use selling * qty


*/
include("include/common.php");
require("goods_return_advice.include.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if (isset($_REQUEST['branch_id']))
	$branch_id = intval($_REQUEST['branch_id']);
else
	$branch_id = $sessioninfo['branch_id'];

// manager and above can see all department
if ($sessioninfo['level'] < 9999)
{
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else
{
	$depts = 1;
}

// show department option
$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

$smarty->assign("PAGE_TITLE", "GRA (Goods Return Advice)");

// check gst status
$prms = array();
$prms['branch_id'] = $sessioninfo['branch_id'];
$prms['date'] = date("Y-m-d");
$is_under_gst = check_gst_status($prms);
$gst_list = array();
if($is_under_gst){
	$gst_list = construct_gst_list("purchase");
	$smarty->assign("is_under_gst", $is_under_gst);
	$smarty->assign("gst_list", $gst_list);
}

// load foreign currency list
if($config['foreign_currency']){
	$foreignCurrencyCodeList = $appCore->currencyManager->getCurrencyCodes();
	$smarty->assign('foreignCurrencyCodeList', $foreignCurrencyCodeList);
}

load_sku_type_list();

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_gra_list':
   			load_gra_list();
			exit;

		case 'cancel':
			$comment = $_REQUEST['comment'];
			$gra_id = intval($_REQUEST['id']);

		    // save GRA info as CANCEL (set status=1)
			$con->sql_query("update gra set returned=0,status=1,remark=".ms($comment)." where id = $gra_id and branch_id = $branch_id");
			if ($con->sql_affectedrows()>0)
			{
				$q1 = $con->sql_query("select gra_items.*,branch.report_prefix
									 from gra_items 
									 left join branch on gra_items.branch_id =branch.id
									 where gra_items.gra_id = $gra_id and gra_items.branch_id = $branch_id");
				while($r = $con->sql_fetchassoc($q1)){
					
					$upd = array();
					$upd['branch_id'] = $r['branch_id'];
					$upd['user_id'] = $r['user_id'];
					$upd['sku_item_id'] = $r['sku_item_id'];
					$upd['vendor_id'] = $r['vendor_id'];
					$upd['qty'] = $r['qty'];
					$upd['cost'] = $r['cost'];
					$upd['selling_price'] = $r['selling_price'];
					$upd['return_type'] = $r['return_type'];
					$upd['reason'] = $comment;
					$upd['gst_id'] = $r['gst_id'];
					$upd['gst_code'] = $r['gst_code'];
					$upd['gst_rate'] = $r['gst_rate'];
					$upd['gst_selling_price'] = $r['gst_selling_price'];
					$upd['doc_no'] = $r['doc_no'];
					$upd['doc_date'] = $r['doc_date'];
                    $upd['amount'] = $r['amount'];
                    $upd['gst'] = $r['gst'];
                    $upd['amount_gst'] = $r['amount_gst'];
                    $upd['currency_code'] = $r['currency_code'];
					$con->sql_query("insert into gra_items ".mysql_insert_by_field($upd));
					$report_prefix = $r['report_prefix'];
				}
				
				$con->sql_query("update dnote set active=0 where ref_table = 'gra' and ref_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));

                // delete tmp gra items
                $con->sql_query("delete from tmp_gra_items where gra_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
				
				// need to keep a log for user who cancel the GRA
				log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("Cancelled: ".$report_prefix."%05d",$gra_id));
				
			}
			header("Location: /goods_return_advice.php?t=cancel&id=$gra_id&report_prefix=$report_prefix");
			exit;

		case 'ajax_sort_list':
 		    $row = $_REQUEST;
		    $row['branch_id'] =$branch_id;
		    $row['user_id'] = $sessioninfo['id'];
		    if($row['curr_sku_type'] != '*') $filters = "sku.sku_type=".ms($row['curr_sku_type'])." and gri.gra_id=0 and gri.temp_gra_uid=0 and gri.branch_id = ".mi($branch_id);
		
			get_gra_items($filters, true);
			$smarty->assign("curr_sku_type", $row['curr_sku_type']);
		    $smarty->display("goods_return_advice.items.tpl");
			exit;

		case 'ajax_add_gra_item':
		    $row = $_REQUEST;
		    $row['branch_id'] = $branch_id;
		    $row['user_id'] = $sessioninfo['id'];
		    if ($row['vendor_id']==-1) $row['vendor_id'] = $row['other_vendor_id'];
			if ($row['return_type']=='other') $row['return_type'] = $row['return_type_other'];
			// get selling price from sku_items_price
			$con->sql_query("select sip.price
							 from sku_items_price sip 
							 where sip.sku_item_id = ".ms($row['sku_item_id'])."
							 and sip.branch_id = ".ms($row['branch_id'])."
							 limit 1");
			$price = $con->sql_fetchfield(0);
			
			// get selling price and decimal points from sku_items
			$con->sql_query("select sku_items.selling_price, sku_items.doc_allow_decimal
							 from sku_items 
							 where sku_items.id = ".ms($row['sku_item_id'])."
							 limit 1");
			$selling_price = $con->sql_fetchfield(0);
			$doc_allow_decimal = $con->sql_fetchfield(1);
			
			if($price) $row['selling_price'] = round($price, $dp);
			else $row['selling_price'] = round($selling_price, $dp);
			if($doc_allow_decimal) $qty_dp = $config['global_qty_decimal_points'];
			else $qty_dp = 0;
			$row['qty'] = round($row['qty'], $qty_dp);
			$row['cost'] = round($row['cost'], $dp);
			$row['doc_no'] = strtoupper($row['doc_no']);
			$row['doc_date'] = $row['doc_date'];

            if($row['sku_type']=='CONSIGN')
                $row['amount'] = $row['qty'] * $row['selling_price'];
            else
                $row['amount'] = $row['qty'] * $row['cost'];
			
			if($is_under_gst){
				$prms = array();
				$is_inclusive_tax = get_sku_gst("inclusive_tax", $row['sku_item_id']);

				$prms['selling_price'] = $row['selling_price'];
				$prms['inclusive_tax'] = $is_inclusive_tax;
				$prms['gst_rate'] = $row['gst_rate'];
				$gst_sp_info = calculate_gra_gst_sp($prms);
				$row['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
				//$r['gst_amt'] = $gst_sp_info['gst_amt'];
				
				if($is_inclusive_tax == "yes"){
					$row['gst_selling_price'] = $row['selling_price'];
					$row['selling_price'] = $gst_sp_info['gst_selling_price'];
				}

				if($row['sku_type']=='CONSIGN'){
					$row['amount_gst']=round($row['gst_selling_price'] * $row['qty'],2);
					$row['amount'] = $row['amount_gst'] / ((100+$row['gst_rate']) / 100);
				}else{
					$row['amount_gst']=round($row['amount'] * ((100+$row['gst_rate'])/100),2);
				}
                
                $row['gst']=$row['amount_gst']-round($row['amount'], 2);
			}
            $row['amount']=round($row['amount'],2);
			
			$con->sql_query("insert into gra_items ".mysql_insert_by_field($row, array("branch_id","sku_item_id","qty","vendor_id","user_id","cost", "selling_price", "return_type", "gst_id", "gst_code", "gst_rate", "gst_selling_price", "doc_no", "doc_date","amount","gst","amount_gst", "currency_code")));
		    
			get_gra_items("sku.sku_type = ".ms($row['sku_type'])." and gri.gra_id=0 and gri.temp_gra_uid=0 and gri.branch_id=".mi($branch_id), true);
			$smarty->assign("sku_type", $row['sku_type']);
		    $smarty->display("goods_return_advice.items.tpl");
		    exit;

		case 'ajax_del_gra_item':
		    $id = intval($_REQUEST['id']);
		    //die("delete from gra_items where id = $id and branch_id=$branch_id");
		    $con->sql_query("delete from gra_items where id = $id and branch_id=$branch_id");
			if($_REQUEST['sku_type'] != '*') $filters = "sku.sku_type=".ms($_REQUEST['sku_type'])." and gri.gra_id=0 and gri.temp_gra_uid=0 and gri.branch_id = ".mi($branch_id);
			get_gra_items($filters, true);
			$smarty->assign("sku_type", $_REQUEST['sku_type']);
		    $smarty->display("goods_return_advice.items.tpl");
		    exit;

		case 'ajax_get_grn_vendor_list':
		    // todo: determine Vendor to GRA base on inventory
		    $sku_item_id = mi($_REQUEST['sku_item_id']);
			$vendor_item = $grn_list = $vd_sku_grn_list = array();
			
			// load GST - NR 
			$q1 = $con->sql_query("select * from gst where type='purchase' and code = 'NR'");
			$nr_gst_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
		    // select last GRN of each vendor using vendor_sku_history
			$filters = array();
			$filters[] = "vsh.source = 'GRN'";
			$filters[] = "vsh.branch_id = ".mi($branch_id);
			$filters[] = "vsh.sku_item_id = ".mi($sku_item_id);
			$past_6_mths_date = date("Y-m-d", strtotime("-6 months"));
			if(!$_REQUEST['show_all_grn_docs']) $date_filter = "and grr.rcv_date >= ".ms($past_6_mths_date);
		    
			$q1 = $con->sql_query("select vsh.*, vendor.description as vendor, sku_item_code, sku_type 
								   from vendor_sku_history vsh
								   left join vendor on vendor_id = vendor.id 
								   left join sku_items on vsh.sku_item_id=sku_items.id
								   left join sku on sku_items.sku_id=sku.id
								   left join grn on grn.id = vsh.ref_id and grn.branch_id = vsh.branch_id
								   left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
								   where ".join(" and ", $filters)." $date_filter
								   order by vsh.added desc");

			// if found past 6 months doesn't have any GRN, try to get the last GRN
			if($con->sql_numrows($q1) == 0){
				$q1 = $con->sql_query("select vsh.*, vendor.description as vendor, sku_item_code, sku_type 
									   from vendor_sku_history vsh
									   left join vendor on vendor_id = vendor.id 
									   left join sku_items on vsh.sku_item_id=sku_items.id
									   left join sku on sku_items.sku_id=sku.id
									   left join grn on grn.id = vsh.ref_id and grn.branch_id = vsh.branch_id
									   left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
									   where ".join(" and ", $filters)."
									   order by vsh.added desc
									   limit 1");
			}
			
			while($r = $con->sql_fetchassoc($q1)){
				$q2 = $con->sql_query("select gri.*, grn.id as grn_id, grn.is_under_gst, grr.currency_code,branch.report_prefix
									   from grr_items gri
									   left join branch on gri.branch_id=branch.id 
									   left join grr on grr.id = gri.grr_id and grr.branch_id = gri.branch_id
									   left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
									   where grn.id = ".mi($r['ref_id'])." and grn.branch_id = ".mi($r['branch_id'])." and gri.type != 'PO'
									   order by grr.rcv_date desc");

				if($con->sql_numrows($q2) > 0){
					while($r1 = $con->sql_fetchassoc($q2)){
						$gi_info = array();
						$gst_id = $nr_gst_info['id']; // need to always select "NR" as gst
						if($r1['is_under_gst']){ // need to select the gst info from grn items
							$q3 = $con->sql_query("select if(acc_gst_id > 0 and acc_gst_id is not null, acc_gst_id, gst_id) as gst_id from grn_items where sku_item_id = ".mi($sku_item_id)." and grn_id = ".mi($r1['grn_id'])." and branch_id = ".mi($r1['branch_id']));
							$gi_info = $con->sql_fetchassoc($q3);
							$con->sql_freeresult($q3);
							
							if($gi_info['gst_id'] > 0) $gst_id = $gi_info['gst_id']; // get from grn items
							unset($gi_info);
						}
						
						$r1['doc_no'] = strtoupper($r1['doc_no']);
						if($r1['doc_date'] == 0) $r1['doc_date'] = "";
						
						$grn_list[$r['vendor_id']][$r1['grn_id']]['cost_price'] = $r['cost_price'];
						$grn_list[$r['vendor_id']][$r1['grn_id']]['gst_id'] = $gst_id;
						$grn_list[$r['vendor_id']][$r1['grn_id']]['doc_date'] = $r1['doc_date'];
						$grn_list[$r['vendor_id']][$r1['grn_id']]['doc_no'] = $r1['doc_no'];
						$grn_list[$r['vendor_id']][$r1['grn_id']]['type'] = $r1['type'];
						$grn_list[$r['vendor_id']][$r1['grn_id']]['currency_code'] = $r1['currency_code'];
						$grn_list[$r['vendor_id']][$r1['grn_id']]['report_prefix'] = $r1['report_prefix'];
					}
				}
				$con->sql_freeresult($q2);
				
				if ($vendor_item[$r['vendor_id']]) continue;
				//$r['source'] = sprintf("GRN%05d", $r['ref_id']);
				$r['cost_price'] = sprintf("%.".$dp."f",$r['cost_price']);
				$vendor_item[$r['vendor_id']] = $r;
			}
			$con->sql_freeresult($q1);
			
			// if no grn, get from PO
			// turn off as per requested by Tommy since 12/18/2017 3:30 PM
			/*if (!$vendor_item)
			{
				$q1 = $con->sql_query("select po.vendor_id, vendor.description as vendor, po.po_no, po_items.order_price / po_items.order_uom_fraction as cost_price,sku.sku_type,sku_items.sku_item_code,po_items.artno_mcode as artno, u.fraction
from po_items
left join sku_items on po_items.sku_item_id = sku_items.id
left join sku on sku_items.sku_id = sku.id
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
left join vendor on po.vendor_id = vendor.id
left join uom u on u.id = po_items.order_uom_id
where po_items.sku_item_id = $sku_item_id and po_items.branch_id=$branch_id
order by po.added desc");

				while($r = $con->sql_fetchassoc($q1))
			    {
					if ($vendor_item[$r['vendor_id']]) continue;
					if($r['po_no']) $r['source'] = $r['po_no'];
					else $r['source'] = "PO";
					$r['cost_price'] = sprintf("%.".$dp."f",$r['cost_price']);
					$vendor_item[$r['vendor_id']] = $r;
					
				}
				$con->sql_freeresult($q1);
			}*/
			
			// if no po, get latest cost
			if (!$vendor_item){
				$q1 = $con->sql_query("select vendor.id as vendor_id, vendor.description as vendor, sic.grn_cost as cost_price,sku.sku_type,sku_items.sku_item_code,if(sku_items.artno,sku_items.artno,sku_items.mcode) as artno
					from sku_items_cost sic
					left join sku_items on sku_items.id=sic.sku_item_id
					left join sku on sku_items.sku_id = sku.id
					left join vendor on sku.vendor_id = vendor.id
					where sic.sku_item_id=$sku_item_id and sic.branch_id=$branch_id");
				while($r = $con->sql_fetchassoc($q1))
			    {
					if ($vendor_item[$r['vendor_id']]) continue;
					$r['source'] = 'Masterfile';
					$r['cost_price'] = sprintf("%.".$dp."f",$r['cost_price']);
					$vendor_item[$r['vendor_id']] = $r;
					
				}
				$con->sql_freeresult($q1);
			}
			
			// if still no item, get from master
			if (!$vendor_item)
			{
				$q1 = $con->sql_query("select vendor.id as vendor_id, vendor.description as vendor, sku_items.cost_price,sku.sku_type,sku_items.sku_item_code,if(sku_items.artno,sku_items.artno,sku_items.mcode) as artno
					from sku_items
					left join sku on sku_items.sku_id = sku.id
					left join vendor on sku.vendor_id = vendor.id
					where sku_items.id = $sku_item_id");
				while($r = $con->sql_fetchassoc($q1))
			    {
					if ($vendor_item[$r['vendor_id']]) continue;
					$r['source'] = 'Masterfile';
					$r['cost_price'] = sprintf("%.".$dp."f",$r['cost_price']);
					$vendor_item[$r['vendor_id']] = $r;
					
				}
				$con->sql_freeresult($q1);
			}
			
			$gst_info = get_sku_gst("input_tax", $sku_item_id);
			$smarty->assign("vendor_item", $vendor_item);
			$smarty->assign("grn_list", $grn_list);
			$smarty->assign("gst_info", $gst_info);
			$smarty->display("goods_return_advice.home.vendor_list.tpl");
			exit;
        // --------- Create/Modify GRA ------------
        
        case 'ajax_copyall_gra_items':
        	$gra_id = intval($_REQUEST['gra_id']);
			$vendor_id = intval($_REQUEST['vendor_id']);
			$sku_type = ms($_REQUEST['sku_type']);
			$dept_id = intval($_REQUEST['dept_id']);
			
			$filters = array();
			if(!is_new_id($gra_id)) $filters[] = "(gi.gra_id = 0 or gi.gra_id = ".mi($gra_id).")";
			else $filters[] = "gi.gra_id = 0";
			
			$filters[] = "tgi.id is null and gi.vendor_id=$vendor_id and gi.branch_id = $branch_id and c.department_id = $dept_id and sku.sku_type = $sku_type";
			
			if($config['foreign_currency']){
				if($_REQUEST['currency_code']) $filters[] = "gi.currency_code = ".ms($_REQUEST['currency_code']);
				else $filters[] = "(gi.currency_code is null or gi.currency_code = '')";
			}
			
			//print_r($_REQUEST);
			$q1 = $con->sql_query("select gi.* 
								  from gra_items gi
								  left join sku_items si on gi.sku_item_id = si.id 
								  left join sku on si.sku_id = sku.id 
								  left join category c on sku.category_id = c.id 
								  left join tmp_gra_items tgi on tgi.item_id = gi.id and tgi.branch_id = gi.branch_id and tgi.gra_id = ".mi($gra_id)."
								  where ".join(" and ", $filters));
			
			while($r = $con->sql_fetchassoc($q1)){
				$ins = array();
				$ins['item_id'] = $r['id'];
				$ins['branch_id'] = $r['branch_id'];
				$ins['gra_id'] = $gra_id;
				$ins['user_id'] = $sessioninfo['id'];
				$ins['sku_item_id'] = $r['sku_item_id'];
				$ins['vendor_id'] = $r['vendor_id'];
				$ins['qty'] = $r['qty'];
				$ins['cost'] = $r['cost'];
				$ins['selling_price'] = $r['selling_price'];
				$ins['gst_id'] = $r['gst_id'];
				$ins['gst_code'] = $r['gst_code'];
				$ins['gst_rate'] = $r['gst_rate'];
				$ins['gst_selling_price'] = $r['gst_selling_price'];
				$ins['doc_no'] = $r['doc_no'];
				$ins['doc_date'] = $r['doc_date'];
				$ins['currency_code'] = $r['currency_code'];
				
				$con->sql_query("replace into tmp_gra_items ".mysql_insert_by_field($ins));
			}
			$con->sql_freeresult($q1);

			exit;
			
		case 'ajax_removeall_gra_items':
        	$gra_id = intval($_REQUEST['gra_id']);
			
			if($gra_id) $filter = "gra_id=".mi($gra_id);
			else $filter = "user_id=".mi($sessioninfo['id'])." and gra_id = 0";

        	$con->sql_query("delete from tmp_gra_items where $filter and branch_id = $branch_id");
        	exit;
        
        // copy 1 item
		case 'ajax_settemp_gra_item':
			$gra_id=intval($_REQUEST['gra_id']);
			$gra_item_id=intval($_REQUEST['gra_item_id']);
			
			$q1 = $con->sql_query("select * from gra_items where id = ".mi($gra_item_id)." and branch_id = ".mi($branch_id));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$ins = array();
			$ins['item_id'] = $r['id'];
			$ins['branch_id'] = $r['branch_id'];
			$ins['gra_id'] = $gra_id;
			$ins['user_id'] = $sessioninfo['id'];
			$ins['sku_item_id'] = $r['sku_item_id'];
			$ins['vendor_id'] = $r['vendor_id'];
			$ins['qty'] = $r['qty'];
			$ins['cost'] = $r['cost'];
			$ins['selling_price'] = $r['selling_price'];
			$ins['gst_id'] = $r['gst_id'];
			$ins['gst_code'] = $r['gst_code'];
			$ins['gst_rate'] = $r['gst_rate'];
			$ins['gst_selling_price'] = $r['gst_selling_price'];
			$ins['doc_no'] = $r['doc_no'];
			$ins['doc_date'] = $r['doc_date'];
			$ins['currency_code'] = $r['currency_code'];
			
			$con->sql_query("replace into tmp_gra_items ".mysql_insert_by_field($ins));
			// continue to show used GRA items
		case 'ajax_showtemp_gra_items':
			$gra_id = intval($_REQUEST['gra_id']);
			$get_valid_items = $_REQUEST['get_valid_items'];
			
			if($get_valid_items){
				$q1 =  $con->sql_query($sql="select tgi.* 
										from tmp_gra_items tgi
										where tgi.gra_id = ".mi($gra_id)." and tgi.branch_id = ".mi($branch_id));

				if($con->sql_numrows($q1) > 0){
					while($r = $con->sql_fetchassoc($q1)){
						$q2 = $con->sql_query("select * from gra_items where id = ".mi($r['item_id'])." and branch_id = ".mi($r['branch_id']));
						$item_info = $con->sql_fetchassoc($q2);
						
						if($con->sql_numrows($q2) > 0){ // item existed, check if the item still available
							if($item_info['gra_id'] && $item_info['gra_id'] != $gra_id){ // found it is being assigned to other GRA
								$con->sql_query("delete from tmp_gra_items where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
							}
						}
					}
				}
			}
			
			if($gra_id) $filter = "gri.gra_id=".mi($gra_id);
			else $filter = "gri.user_id=".mi($sessioninfo['id'])." and gri.gra_id = 0";
			
			get_gra_items("$filter and gri.branch_id=$branch_id", false, true);
			$smarty->assign("is_current_item",1);
		    $smarty->display("goods_return_advice.items_simple.tpl");
			exit;

		// remove 1 item
		case 'ajax_unsettemp_gra_item':
			$gra_item_id=intval($_REQUEST['gra_item_id']);
			$sku_type = ms($_REQUEST['sku_type']);
			// no GRA created yet
			$con->sql_query("delete from tmp_gra_items where id=$gra_item_id and branch_id = $branch_id");
			// continue to show unused GRA items
			
		case 'ajax_unused_gra_items':		
			$gra_id = intval($_REQUEST['gra_id']);
			$vendor_id = intval($_REQUEST['vendor_id']);
			$sku_type = ms($_REQUEST['sku_type']);
			$dept_id = intval($_REQUEST['dept_id']);

			if(!is_new_id($gra_id)) $filter = "(gri.gra_id = 0 or gri.gra_id = ".mi($gra_id).")";
			else $filter = "gri.gra_id = 0 and gri.temp_gra_uid=0";
			
			if($config['foreign_currency']){
				if($_REQUEST['currency_code']) $filter .= " and gri.currency_code = ".ms($_REQUEST['currency_code']);
				else $filter .= " and (gri.currency_code is null or gri.currency_code = '')";
			}
			
			get_gra_items("sku.sku_type = $sku_type and $filter and gri.branch_id=$branch_id and gri.vendor_id = $vendor_id and category.department_id=$dept_id", false);

		    $smarty->display("goods_return_advice.items_simple.tpl");
			exit;

		case 'open':
			$id = intval($_REQUEST['id']);
			if($id){
				$con->sql_query("select gra.*, vendor.description as vendor,category.description as dept_code from gra left join vendor on gra.vendor_id = vendor.id left join category on gra.dept_id = category.id where gra.id = $id and branch_id = $branch_id");
				$gra = $con->sql_fetchrow();
				$gra['misc_info'] = unserialize($gra['misc_info']);
				$gra['extra']= unserialize($gra['extra']);
				
				if($gra['extra']){
					foreach ($gra['extra']['code'] as $idx=>$fn){
						$new[$idx]['code'] = $fn;
						$new[$idx]['description'] = $gra['extra']['description'][$idx];
						$new[$idx]['cost'] = $gra['extra']['cost'][$idx];
						$new[$idx]['qty'] = $gra['extra']['qty'][$idx];
						$new[$idx]['gst_id'] = $gra['extra']['gst_id'][$idx];
						$new[$idx]['gst_code'] = $gra['extra']['gst_code'][$idx];
						$new[$idx]['gst_rate'] = $gra['extra']['gst_rate'][$idx];
						$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
						$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
						$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
					}
				}

				$smarty->assign("new", $new);
				
				if ($gra['approval_history_id']>0){
					$q0=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
		from branch_approval_history_items i
		left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
		left join user on i.user_id = user.id
		where h.ref_table = 'gra' and i.branch_id = $branch_id and i.approval_history_id = $gra[approval_history_id] 
		order by i.timestamp");
					$approval_history = array();
					while($r = $con->sql_fetchassoc($q0)){
						$r['more_info'] = unserialize($r['more_info']);
						$approval_history[] = $r;
					}
					$con->sql_freeresult($q0);
					$smarty->assign("approval_history", $approval_history);
				}

				// delete items from tmp table
				$con->sql_query("delete tgi.* 
								from tmp_gra_items tgi
								where tgi.gra_id = ".mi($id)." and tgi.branch_id = ".mi($branch_id));
								
				
				$q1 = $con->sql_query("select * from gra_items where gra_id = ".mi($id)." and branch_id = ".mi($branch_id));
				
				while($r = $con->sql_fetchassoc($q1)){
					$ins = array();
					$ins['item_id'] = $r['id'];
					$ins['branch_id'] = $r['branch_id'];
					$ins['gra_id'] = $r['gra_id'];
					$ins['user_id'] = $sessioninfo['id'];
					$ins['sku_item_id'] = $r['sku_item_id'];
					$ins['vendor_id'] = $r['vendor_id'];
					$ins['qty'] = $r['qty'];
					$ins['cost'] = $r['cost'];
					$ins['selling_price'] = $r['selling_price'];
					$ins['batchno'] = $r['batchno'];
					$ins['gst_id'] = $r['gst_id'];
					$ins['gst_code'] = $r['gst_code'];
					$ins['gst_rate'] = $r['gst_rate'];
					$ins['gst_selling_price'] = $r['gst_selling_price'];
					$ins['doc_no'] = $r['doc_no'];
					$ins['doc_date'] = $r['doc_date'];
					$ins['currency_code'] = $r['currency_code'];
					
					$con->sql_query("replace into tmp_gra_items ".mysql_insert_by_field($ins));
				}
			}else{
				// delete related gra items from tmp table
				$con->sql_query("delete from tmp_gra_items where user_id=".mi($sessioninfo['id'])." and branch_id = ".mi($branch_id)." and gra_id>1000000000");
			
				$id = $gra['id'] = time();
			}

			//echo"<pre>";print_r($new);echo"</pre>";
  			//echo"<pre>";print_r($gra);echo"</pre>";
			$smarty->assign("form", $gra);
			
			loadGRACurrencyCodeList($gra);

			if(!$gra['status'] && !$gra['returned'] && !$gra['approved']) {
				if(!is_new_id($id)){
					$con->sql_query("update gra_items set temp_gra_uid = $sessioninfo[id] where gra_id = $id and branch_id = $branch_id");
				}
				$smarty->display("goods_return_advice.new.tpl");
			}else{ // if it is checkout or approved or cancelled
				header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id"); // switch to readonly mode
			}
			exit;

		case 'view':
		case 'print':
			$id = intval($_REQUEST['id']);
			// update the GRA amount
		//	$con->sql_query("select sum(qty*cost) from gra_items where gra_id=$id and branch_id = $branch_id");
		//	$amt = $con->sql_fetchrow();
		//	$con->sql_query("update gra set last_update=last_update,amount=".mf($amt[0])." where id=$id and branch_id = $branch_id");

			$con->sql_query("select gra.*, branch.code as branch_code,branch.report_prefix, user.u, vendor.description as vendor,
							category.description as dept_code, vendor.code as vendor_code,
							if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
							from gra
							left join user on gra.user_id = user.id
							left join vendor on gra.vendor_id = vendor.id
							left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
							left join category on gra.dept_id = category.id
							left join branch on branch.id = gra.branch_id
							where gra.id = $id and gra.branch_id = $branch_id");
			$gra = $con->sql_fetchrow();
			if ($gra){
				$gra['misc_info'] = unserialize($gra['misc_info']);
  				$gra['extra']= unserialize($gra['extra']);
  				if($gra['extra']){
					$igi_have_doc_info = false;
    				foreach ($gra['extra']['code'] as $idx=>$fn){
						$new[$idx]['code'] = $fn;
						$new[$idx]['description'] = $gra['extra']['description'][$idx];
   						$new[$idx]['cost'] = $gra['extra']['cost'][$idx];
   						$new[$idx]['qty'] = $gra['extra']['qty'][$idx];
						$new[$idx]['gst_id'] = $gra['extra']['gst_id'][$idx];
						$new[$idx]['gst_code'] = $gra['extra']['gst_code'][$idx];
						$new[$idx]['gst_rate'] = $gra['extra']['gst_rate'][$idx];
						$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
						$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
						$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
						
						if(trim($gra['extra']['doc_no'][$idx]) || $gra['extra']['doc_date'][$idx] != 0) $igi_have_doc_info = true;
					}
				}
				
				if($gra['approval_history_id']>0){
					$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u, i.more_info
										from branch_approval_history_items i 
										left join branch_approval_history h on i.approval_history_id=h.id and i.branch_id=h.branch_id 
										left join user on i.user_id = user.id 
										where h.ref_table = 'gra' and i.branch_id=".mi($branch_id)." and (h.ref_id=".mi($id)." or h.id = ".mi($gra['approval_history_id']).")
										order by i.timestamp");

					$approval_history = array();
					while($r = $con->sql_fetchassoc($q2)){
						$r['more_info'] = unserialize($r['more_info']);
						$approval_history[] = $r;
					}
					$smarty->assign("approval_history", $approval_history);
					$con->sql_freeresult($q2);
				}
				
				/*if($gra['return_timestamp'] == 0 || !$gra['returned_by']) $gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
				else $gra['disposal_date'] = $gra['return_timestamp'];*/
				
				$gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
				
				$q1=$con->sql_query("select gra_items.*, sku_item_code, artno, mcode,sku_items.link_code, sku_items.additional_description,
									 sku_items.description as sku,if(sip.price, sip.trade_discount_code, sku.default_trade_discount_code) as price_type,
									 gra_items.selling_price, sku_items.doc_allow_decimal,puom.code as packing_uom_code,if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax, sku_items.sku_id
									 from gra_items
									 left join sku_items on gra_items.sku_item_id = sku_items.id
									 left join uom puom on puom.id=sku_items.packing_uom_id
									 left join sku on sku_items.sku_id=sku.id
									 left join category_cache cc on cc.category_id=sku.category_id
									 left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id=gra_items.branch_id
									 where gra_id = $id and gra_items.branch_id = $branch_id");
				
				$gra_items = array();
				$gi_have_doc_info = false;
				while($r = $con->sql_fetchassoc($q1)){
					if(trim($r['doc_no']) || $r['doc_date'] != 0) $gi_have_doc_info = true;
					$gra_items[] = $r;
				}
				$con->sql_freeresult($q1);
				
				$gra['items'] = $gra_items;
                $gra['misc_info']['dn_amount'] = doubleval(str_replace(",","", $gra['misc_info']['dn_amount']));
				$smarty->assign("form", $gra);
				$smarty->assign("igi_have_doc_info", $igi_have_doc_info);
				$smarty->assign("gi_have_doc_info", $gi_have_doc_info);
				$smarty->assign("new", $new);

				// update printing counter
				if ($_REQUEST['a']=='print'){
				    if($_REQUEST['own_copy'])    $copy[] = 'own';
					if($_REQUEST['vendor_copy'])    $copy[] = 'vendor_copy';
					
					$con->sql_query("update gra set last_update=last_update,print_counter=print_counter+1 where gra.id = $id and branch_id = $branch_id");
					$con->sql_query("select * from branch where id = $branch_id");
					$smarty->assign("branch", $con->sql_fetchrow());

					$GRA_ITEMS_PER_PAGE = ($config['gra_print_item_per_page']>0)?$config['gra_print_item_per_page']:14;
                    $GRA_ITEMS_PER_LAST_PAGE = $GRA_ITEMS_PER_PAGE;

					$totalpage_sku = 1 + ceil((count($gra['items'])-$GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
					$totalpage_nonsku =  1 + ceil((count($new)-$GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
					$totalpage=$totalpage_sku+$totalpage_nonsku;
					$smarty->assign('show_total',1);
					
					// start print GRA
					$item_index = -1;
					$item_no = -1;
					$page = 1;
					
					$page_item_list = array();
					$page_item_info = array();
				
                    if ($gra['items']){
                        foreach($gra['items'] as $r){	// loop for each item
                            if($item_index+1>=$GRA_ITEMS_PER_PAGE){
                                $page++;
                                $item_index = -1;
                            }
                            
                            $item_no++;
                            $item_index++;
                            $r['item_no'] = $item_no;
                            
                            $page_item_list[$page][$item_index] = $r;	// add item to this page
                            
                            if($config['sku_enable_additional_description'] && $r['additional_description']){
                                $r['additional_description'] = unserialize($r['additional_description']);
                                foreach($r['additional_description'] as $desc){
                                    if($item_index+1>=$GRA_ITEMS_PER_PAGE){
                                        $page++;
                                        $item_index = -1;
                                    }
                            
                                    $item_index++;
                                    $desc_row = array();
                                    $desc_row['sku'] = $desc;
                                    $page_item_list[$page][$item_index] = $desc_row;
                                    $page_item_info[$page][$item_index]['not_item'] = 1;
                                }
                            }
                        }
                    }
                    
                    // fix last page
                    if(count($page_item_list[$page]) > $GRA_ITEMS_PER_LAST_PAGE){	
                        $page++;
                        $page_item_list[$page] = array();
                    }
				
                    $totalpage = count($page_item_list) + $totalpage_nonsku;
                    
                    foreach($copy as $cp_type){
					    $smarty->assign('copy_type',$cp_type);
						// reset total
						$smarty->assign('total1_amt',0);
						$smarty->assign('total1_gst',0);
						$smarty->assign('total1_gst_amt',0);
					    $smarty->assign('total1_qty',0);
						$smarty->assign('ttl1_qty',0);
						$smarty->assign('ttl1_amt',0);
						$smarty->assign('ttl1_gst_amt',0);
						$smarty->assign('total_qty',0);
						$smarty->assign('total_amt',0);
						$smarty->assign('total_gst',0);
						$smarty->assign('total_gst_amt',0);
						$smarty->assign('ttl_qty',0);
						$smarty->assign('ttl_amt',0);
						$smarty->assign('ttl_gst',0);
						$smarty->assign('ttl_gst_amt',0);
						
						if($config['gra_alt_print_template'])    $print_tpl = $config['gra_alt_print_template'];
						else    $print_tpl = "goods_return_advice.$_REQUEST[a].tpl";
						
						if ($config['report_logo_by_branch']['gra'][get_branch_code($branch_id)]) $smarty->assign("alt_logo_img", $config['report_logo_by_branch']['gra'][get_branch_code($branch_id)]);

						if ($gra['items']){
							foreach($page_item_list as $page => $item_list){
								$this_page_num = ($page < $totalpage) ? $GRA_ITEMS_PER_PAGE : $GRA_ITEMS_PER_LAST_PAGE;
								$smarty->assign("is_lastpage", ($page >= $totalpage));
								$smarty->assign("page", "Page $page of $totalpage");
								$smarty->assign("PAGE_SIZE", $this_page_num);
								$smarty->assign("start_counter",$item_list[0]['item_no']);
								$smarty->assign("new", "");
								$smarty->assign("items", $item_list);
								$smarty->assign("page_item_info", $page_item_info[$page]);
								$smarty->display($print_tpl);
								$smarty->assign("skip_header",1);
                                $total_p=$page;
							}
							/*
							for ($i=0,$page=1;$page<=$totalpage_sku;$i+=$GRA_ITEMS_PER_PAGE,$page++){
								$smarty->assign("is_lastpage", ($page >= $totalpage));
						        $smarty->assign("page", "Page $page of $totalpage");
						        
						        if($page >= $totalpage){
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
								}else{
                                    $smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);
                                    
								}
								$smarty->assign("items", array_slice($gra['items'],$i,$GRA_ITEMS_PER_PAGE));
						        $smarty->assign("start_counter", $i);
						        $smarty->assign("new", "");
						        
								//$smarty->display("goods_return_advice.$_REQUEST[a].tpl");
								$smarty->display($print_tpl);
								$smarty->assign("skip_header",1);
								$total_p=$page;
							}*/
						}						
					    if ($new){
							for($j=0,$page=1;$page<=$totalpage_nonsku;$j+=$GRA_ITEMS_PER_PAGE,$page++){
								/*for($k=0;$k<2;$k++){
									if($k==0)$smarty->assign("vendor_copy",1);
									else $smarty->assign("vendor_copy",0);

									$no_page=$total_p+$page;
									$smarty->assign("is_lastpage", ($page >= $totalpage_nonsku));
							        $smarty->assign("page", "Page $no_page of $totalpage");
							        $smarty->assign("start_counter", $j);
							        $smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
									$smarty->display('goods_return_advice.print.tpl');
									$smarty->assign("skip_header",1);
								}*/
								
								$no_page=$total_p+$page;
								$smarty->assign('show_total',1);
								$smarty->assign("is_lastpage", ($no_page >= $totalpage));
						        $smarty->assign("page", "Page $no_page of $totalpage");
						        if($no_page >= $totalpage){
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
								}else{
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);
    							}
								$smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
						        $smarty->assign("start_counter", $j);
/*						        print "<pre>";
						        print_r(array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
						        print "</pre>";
*/						        $smarty->assign("items", "");
								
						        $smarty->display($print_tpl);
								$smarty->assign("skip_header",1);
							}
						}
					}
				}
				else{
				    if($config['gra_'.$_REQUEST['a'].'_alt_print_template'])    $print_tpl = $config['gra_'.$_REQUEST['a'].'_alt_print_template'];
					else    $print_tpl = "goods_return_advice.$_REQUEST[a].tpl";
					$smarty->display($print_tpl);
					//$smarty->display("goods_return_advice.$_REQUEST[a].tpl");
				}

			}
			else{
			    $smarty->assign("url", "/goods_return_advice.php");
			    $smarty->assign("title", "Goods Return Advice");
			    $smarty->assign("subject", sprintf($LANG['GRA_INVALID_GRA_NO'], $id));
			    $smarty->display("redir.tpl");
			}
			exit;

		case 'confirm':
			$is_confirm = true;
		case 'save':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
		    //exit;
			$form = $_REQUEST;
			
			$gra_id = intval($_REQUEST['id']);
			$err = $upd = $params = array();
			$err = validate_data($form);
			$params['type'] = 'GOODS_RETURN_ADVICE';
			$params['user_id'] = $sessioninfo['id'];
			$params['reftable'] = 'gra';
			$params['dept_id'] = $form['dept_id']; 
			$params['branch_id'] = $branch_id;        

			if(!$err){
				// get item total amount
				
				if ($form['sku_type']=='CONSIGN') $val = "sum(qty*selling_price)";
				else $val = "sum(qty*cost)";
				
				$con->sql_query("select $val from tmp_gra_items where gra_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
				$total_amt = mf($con->sql_fetchfield(0));
				$con->sql_freeresult();
				
				$total_amt += $form['extra_amount'];
				
				$params['doc_amt'] = $total_amt;
				
				if($is_confirm){
					$last_approval = false;
					if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
					$astat = check_and_create_approval2($params, $con);

					if(!$astat) $err['sheet'][]=$LANG['GRA_NO_APPROVAL_FLOW'];
					else{
						$form['approval_history_id']=$astat[0];
						if($astat[1]=='|'){
							$last_approval=true;
							
							if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
						} 
					}
					
					$upd['status'] = 2;
				}
			}
			
			if ($err){
				$smarty->assign("form",$form);
				$smarty->assign("errm",$err);
				$smarty->display("goods_return_advice.new.tpl");
				exit;
			}
			
			$upd['vendor_id'] = $form['vendor_id'];
			$upd['dept_id'] = $form['dept_id'];
			$upd['sku_type'] = $form['sku_type'];
			//$upd['amount'] = $form['amount'];
			$upd['amount'] = $total_amt;	// total amount is included extra amount
			$upd['misc_info'] = $form['misc_info'];
			$upd['extra'] = $form['extra'];
			$upd['extra_amount'] = $form['extra_amount'];
			$upd['remark2'] = $form['remark2'];
			$upd['approval_history_id'] = $form['approval_history_id'];
			$upd['is_under_gst'] = $is_under_gst;
			$upd['currency_code'] = $form['currency_code'];
			if($last_approval){
				$upd['status'] = 0;
				$upd['approved'] = 1;
			}
			
			if($config['foreign_currency']){
				$upd['currency_code'] = $form['currency_code'];
				
				// if doesn't found this GRA has return_timestamp (checkout date), then always reload currency rate
				if(!$form['return_timestamp']){
					if($form['currency_code']){
						$date = date("Y-m-d");
						$ret = $appCore->currencyManager->loadCurrencyRateByDate($date, $form['currency_code']);
						$upd['currency_rate'] = $ret['rate'];
					}else{
						$upd['currency_rate'] = 1; // always insert currency rate as 1 for base currency
					}
				}
				
				// if found have currency code, set this GRA become non gst
				if($form['currency_code']) $upd['is_under_gst'] = 0;
			}
			
			// save GRA
			if (!is_new_id($form['id'])){
				$con->sql_query("update gra set ".mysql_update_by_field($upd)." where id = $gra_id and branch_id = $branch_id") or die(mysql_error());
			}else{ // insert as new GRA
				$upd['branch_id'] = $form['branch_id'];
				$upd['user_id'] = $form['user_id'];
				$upd['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into gra ".mysql_insert_by_field($upd));
				$gra_id = $con->sql_nextid();
			}
	
			// debug purpose
			//$flog = fopen("gra_log.txt", "a+");
			//fwrite($flog, "GRA ID: $gra_id     BRANCH: $branch_id\n");
			//$con->sql_query("select * from gra_items where (gra_id=0 or gra_id = $gra_id) and temp_gra_uid = $sessioninfo[id] and branch_id = $branch_id");
			//while($r=$con->sql_fetchrow()) {fwrite($flog, join("|", $r)."\n");}
	
			/// change the GRA ID of the selected items
			//$con->sql_query("update gra_items set gra_id=$gra_id, batchno=0, status=0, temp_gra_uid=0 where (gra_id=0 or gra_id = $gra_id) and temp_gra_uid = $sessioninfo[id] and branch_id = $branch_id");
			//fwrite($flog, "\nAffected rows: ".$con->sql_affectedrows()."\n--------------------------------------------------------\n");
			//fclose($flog);

			// update the GRA amount
			//$con->sql_query("select sum(qty*cost) from gra_items where gra_id=$gra_id and branch_id = $branch_id");
			//amt = $con->sql_fetchrow();
			//$con->sql_query("update gra set amount=".mf(amt[0])."+extra_amount where id=$gra_id and branch_id = $branch_id");
			
			// get all tmp gra item for update
			$q1 = $con->sql_query("select tgi.* from tmp_gra_items tgi where tgi.branch_id = ".mi($branch_id)." and tgi.gra_id = ".mi($_REQUEST['id']));
			
			while($r = $con->sql_fetchassoc($q1)){
				// update gra items
				$upd=array();
                $upd['gra_id']=$gra_id;
                $upd['batchno']=0;
                $upd['status']=0;
                $upd['temp_gra_uid']=0;

                $con->sql_query("update gra_items set ".mysql_update_by_field($upd)."
                                where branch_id = ".mi($branch_id)." and id = ".mi($r['item_id'])." and gra_id = 0");
			}
			$con->sql_freeresult($q1);
			
			// update gra items that being un-assigned
			if(!is_new_id($_REQUEST['id'])){
				$q1 = $con->sql_query("select gi.* 
									   from gra_items gi
									   where gi.gra_id = ".mi($_REQUEST['id'])." and gi.branch_id = ".mi($branch_id));
				
				while($r = $con->sql_fetchassoc($q1)){
					$q2 = $con->sql_query("select * from tmp_gra_items where item_id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and gra_id = ".mi($r['gra_id']));
					
					// if found the item had been un-assigned then only update it 
					if($con->sql_numrows($q2) == 0){
						$upd=array();
                        $upd['gra_id']=0;
                        $upd['batchno']=0;
                        $upd['status']=0;
                        $upd['temp_gra_uid']=0;

                       $con->sql_query("update gra_items set ".mysql_update_by_field($upd)."
                               where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id'])." and gra_id = ".mi($r['gra_id']));
					}
					$con->sql_freeresult($q2);
				}
			}
			
			// delete tmp gra items
			$con->sql_query("delete from tmp_gra_items where gra_id = ".mi($_REQUEST['id'])." and branch_id = ".mi($branch_id));

            //recalculate gra_items amount
            $q1 = $con->sql_query("select gra_items.*,branch.report_prefix from gra_items left join branch on gra_items.branch_id=branch.id  where gra_items.branch_id = ".mi($branch_id)." and gra_id = ".mi($gra_id));
            while($gra_item = $con->sql_fetchassoc($q1)){
                if($is_under_gst){
                    if($gra_item['gst_id']==0){
                        $gst_info = get_sku_gst("input_tax", $gra_item['sku_item_id']);
                        $gra_item['gst_id'] = $gst_info['id'];
                        $gra_item['gst_code'] = $gst_info['code'];
                        $gra_item['gst_rate'] = $gst_info['rate'];
                    }

                    $prms = array();
                    $is_inclusive_tax = get_sku_gst("inclusive_tax", $gra_item['sku_item_id']);

                    $prms['selling_price'] = $gra_item['selling_price'];
                    $prms['inclusive_tax'] = $is_inclusive_tax;
                    $prms['gst_rate'] = $gra_item['gst_rate'];
                    
                    $gra_item['amount_gst'] = round($gra_item['amount'] * ((100+$gra_item['gst_rate'])/100),2);
                    $gra_item['gst'] = $gra_item['amount_gst']-round($gra_item['amount'], 2);
                }

                $gst=$gra_item['gst'];
                $amount_gst=$gra_item['amount_gst'];

                if($form['sku_type']=='CONSIGN')
                    $amount = $gra_item['qty'] * $gra_item['selling_price'];
                else
                    $amount = $gra_item['qty'] * $gra_item['cost'];

                if($is_under_gst){
                    $amount_gst=round($amount * ((100+$gra_item['gst_rate'])/100),2);
                    $gst=$amount_gst-round($amount, 2);
                }
                $amount=round($amount,2);

                $upd=array();
                $upd["gst_id"]=$gra_item['gst_id'];
                $upd["gst_code"]=$gra_item['gst_code'];
                $upd["gst_rate"]=$gra_item['gst_rate'];
                $upd["gst_selling_price"]=$gra_item['gst_selling_price'];
                $upd["amount"]=$amount;
                $upd["gst"]=$gst;
                $upd["amount_gst"]=$amount_gst;

                $con->sql_query("update gra_items set ".mysql_update_by_field($upd)."
                                where id=".$gra_item['id']." and gra_id = ".$gra_item['gra_id']." and branch_id = ".$gra_item['branch_id']);
            }
            $con->sql_freeresult($q1);
			$q2 =$con->sql_query("select branch.report_prefix from branch where branch.id = ".mi($branch_id));
			while($b_name =$con->sql_fetchassoc($q2)){
				$report_prefix = $b_name['report_prefix'];
			}
			$con->sql_freeresult($q2);
			if ($form['sku_type']=='CONSIGN') $val = "sum(qty*selling_price)";
				else $val = "sum(qty*cost)";

            $con->sql_query("select $val from gra_items where gra_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
			$total_amt = mf($con->sql_fetchfield(0));
			$con->sql_freeresult();

			$total_amt += $form['extra_amount'];

            $con->sql_query("update gra set amount=$total_amt where id = $gra_id and branch_id = $branch_id");

			// auto goto printing page
			if ($_REQUEST['a']=='confirm'){
				$con->sql_query("update branch_approval_history set ref_id = $gra_id where id = $form[approval_history_id] and branch_id = $branch_id");
				
				if($last_approval){	// direct approve - create a approval history row
					$upd = array();
					$upd['approval_history_id'] = $form['approval_history_id'];
					$upd['branch_id'] = $branch_id;
					$upd['user_id'] = $sessioninfo['id'];
					$upd['status'] = 1;
					$upd['log'] = 'Approved';
					
					if($direct_approve_due_to_less_then_min_doc_amt)	$upd['more_info']['direct_approve_due_to_less_then_min_doc_amt'] = 1;
					if($upd['more_info'])	$upd['more_info'] = serialize($upd['more_info']);
					
					$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd));
				}
				
				$to = get_pm_recipient_list2($gra_id,$form['approval_history_id'],0, 'confirmation',$branch_id,'gra');
				send_pm2($to, "Goods Return Advice Confirm (ID#$gra_id)", "goods_return_advice.php?a=view&id=$gra_id&branch_id=$branch_id", array('module_name'=>'gra'));
				
			    log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("Confirmed: ".$report_prefix."%05d",$gra_id));
				// after save , return to front page
				header("Location: $_SERVER[PHP_SELF]?t=confirm&id=$gra_id&report_prefix=$report_prefix");
			}else{
			    log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("Saved: ".$report_prefix."%05d",$gra_id));
			    // after save , return to front page
				header("Location: $_SERVER[PHP_SELF]?t=save&id=$gra_id&report_prefix=$report_prefix");
			}
			exit;

		case 'close':
			// restore the unassigned
			$con->sql_query("delete from tmp_gra_items where gra_id = ".mi($_REQUEST['id'])." and branch_id=".mi($branch_id));
			header("Location: $_SERVER[PHP_SELF]");
			exit;

		case 'print_checklist':
			$gra_id = intval($_REQUEST['id']);
			$branch_id = intval($_REQUEST['bid']);
			$bno = intval($_REQUEST['bno']);

			$con->sql_query("select gra.*, user.u, vendor.description as vendor,category.description as dept_code, 
							vendor.code as vendor_code, if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
							from gra
							left join user on gra.user_id = user.id
							left join vendor on gra.vendor_id = vendor.id
							left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
							left join category on gra.dept_id = category.id
							where gra.id = $gra_id and gra.branch_id = $branch_id");
			$gra = $con->sql_fetchrow();
			$con->sql_freeresult();
			$gra['misc_info'] = unserialize($gra['misc_info']);

			/*if($gra['return_timestamp'] == 0 || !$gra['returned_by']) $gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
			else $gra['disposal_date'] = $gra['return_timestamp'];*/
			
			$gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
			
  			$gra['extra']= unserialize($gra['extra']);
  			if($gra['extra']){
    			foreach ($gra['extra']['code'] as $idx=>$fn){
					$new[$idx]['code'] = $fn;
					$new[$idx]['description'] = $gra['extra']['description'][$idx];
   					$new[$idx]['cost'] = $gra['extra']['cost'][$idx];
   					$new[$idx]['qty'] = $gra['extra']['qty'][$idx];
					$new[$idx]['gst_id'] = $gra['extra']['gst_id'][$idx];
					$new[$idx]['gst_code'] = $gra['extra']['gst_code'][$idx];
					$new[$idx]['gst_rate'] = $gra['extra']['gst_rate'][$idx];
					$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
					$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
					$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
				}
			}

			// if batchno = 0, check if we have new items
			if ($bno == 0){
				$con->sql_query("select min(batchno),max(batchno) from gra_items where gra_id=$gra_id and branch_id = $branch_id");
				$r=$con->sql_fetchrow();
				// print max(batchno) if there is no new items (min=0)
				if ($r[0]>0) $bno = $r[1];
			}

			if ($bno == 0){
				// create new Checklist and print
				$r1=$con->sql_query("select gra_items.*, sku_item_code, artno, mcode, sku_items.description as sku,sku_items.additional_description,
									 if(sip.price, sip.trade_discount_code, sku.default_trade_discount_code) as price_type,
									 gra_items.selling_price, sku_items.link_code, sku_items.doc_allow_decimal,
									 if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax
									 from gra_items
									 left join sku_items on gra_items.sku_item_id = sku_items.id
									 left join sku on sku_items.sku_id=sku.id
									 left join category_cache cc on cc.category_id=sku.category_id
                                     left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id=gra_items.branch_id
									 where gra_items.status=0 and gra_id=$gra_id and gra_items.branch_id = $branch_id");
				$no_row=$con->sql_numrows($r1);
				if ($no_row<=0 && !$new){
					print "<script>alert('$LANG[GRA_CHECKLIST_EMPTY]');</script>";
					exit;
				}
				/*while($row1=$con->sql_fetchrow($r1)){
					call_cost_sell($row1);
				}*/
				$gra['items'] = $con->sql_fetchrowset($r1);
				$con->sql_freeresult($r1);
				
				//update status to locked
				$r=$con->sql_query("select max(batchno) from gra_items where gra_id=$gra_id and branch_id = $branch_id and status<>0");
				$maxb = $con->sql_fetchrow($r);
				$con->sql_freeresult($r);
				$bno=$maxb[0]+1;
				$con->sql_query("update gra_items set added=added,status=1,batchno=$bno where gra_id=$gra_id and branch_id = $branch_id and status=0");
			}
			else{
				$r=$con->sql_query("select gra_items.*, if(sip.price, sip.trade_discount_code, sku.default_trade_discount_code) as price_type, sku_type,sku_item_code,
									gra_items.selling_price, artno, mcode, sku_items.description as sku,
									sku_items.additional_description,
									sku_items.link_code, sku_items.doc_allow_decimal,
									if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax
									from gra_items
									left join sku_items on gra_items.sku_item_id = sku_items.id
									left join sku on sku_items.sku_id=sku.id
									left join category_cache cc on cc.category_id=sku.category_id
                                    left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id=gra_items.branch_id
									where batchno=$bno and gra_id=$gra_id and gra_items.branch_id = $branch_id");
				$no_row=$con->sql_numrows($r);
				if ($no_row<=0 && !$new){
					print "<script>alert('$LANG[GRA_CHECKLIST_EMPTY]');</script>";
					exit;
				}
				$gra['items'] = $con->sql_fetchrowset($r);
				$con->sql_freeresult($r);
			}
			
			$gra['batchno']=$bno;
			$smarty->assign("form", $gra);
			//$smarty->assign("new", $new);
			$smarty->assign("no_row", $no_row);

			$con->sql_query("select * from branch where id = $branch_id");
			$smarty->assign("branch", $con->sql_fetchrow());
			$con->sql_freeresult();
			
			$GRA_ITEMS_PER_PAGE =  ($config['gra_print_item_per_page']>0)?$config['gra_print_item_per_page']:9;
            $GRA_ITEMS_PER_LAST_PAGE = $GRA_ITEMS_PER_PAGE;
			$totalpage_sku = 1+ ceil((count($gra['items']) - $GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
			$totalpage_nonsku = 1 + ceil((count($new) - $GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
			$totalpage=$totalpage_sku+$totalpage_nonsku;
			if($config['gra_checklist_alt_print_template']) $tpl = $config['gra_checklist_alt_print_template'];
			else $tpl = "goods_return_advice.print.checklist.tpl";
			
			// start print GRA
			$item_index = -1;
			$item_no = -1;
			$page = 1;
			
			$page_item_list = array();
			$page_item_info = array();
		
            if ($gra['items']){
                foreach($gra['items'] as $r){	// loop for each item
                    if($item_index+1>=$GRA_ITEMS_PER_PAGE){
                        $page++;
                        $item_index = -1;
                    }
                    
                    $item_no++;
                    $item_index++;
                    $r['item_no'] = $item_no;
                    
                    $page_item_list[$page][$item_index] = $r;	// add item to this page
                    
                    if($config['sku_enable_additional_description'] && $r['additional_description']){
                        $r['additional_description'] = unserialize($r['additional_description']);
                        foreach($r['additional_description'] as $desc){
                            if($item_index+1>=$GRA_ITEMS_PER_PAGE){
                                $page++;
                                $item_index = -1;
                            }
                    
                            $item_index++;
                            $desc_row = array();
                            $desc_row['sku'] = $desc;
                            $page_item_list[$page][$item_index] = $desc_row;
                            $page_item_info[$page][$item_index]['not_item'] = 1;
                        }
                    }
                }
            }
            
			// fix last page
			if(count($page_item_list[$page]) > $GRA_ITEMS_PER_LAST_PAGE){	
				$page++;
				$page_item_list[$page] = array();
			}
			
			$totalpage = count($page_item_list) + $totalpage_nonsku;
			
			if ($gra['items']){	
				foreach($page_item_list as $page => $item_list){
					$this_page_num = ($page < $totalpage) ? $GRA_ITEMS_PER_PAGE : $GRA_ITEMS_PER_LAST_PAGE;
					$smarty->assign("is_lastpage", ($page >= $totalpage_sku));
					$smarty->assign("page", "Page $page of $totalpage");
					$smarty->assign("PAGE_SIZE", $this_page_num);
					$smarty->assign("start_counter",$item_list[0]['item_no']);
					$smarty->assign("items", $item_list);
					$smarty->assign("page_item_info", $page_item_info[$page]);
					$smarty->display($tpl);
					$smarty->assign("skip_header",1);
                    $total_p=$page;
				}/*
				for($i=0,$page=1;$i<count($gra['items']);$i+=$GRA_ITEMS_PER_PAGE,$page++){
					$smarty->assign("is_lastpage", ($page >= $totalpage_sku));
			        $smarty->assign("page", "Page $page of $totalpage");
			        $smarty->assign("start_counter", $i);
			        $smarty->assign("items", array_slice($gra['items'],$i,$GRA_ITEMS_PER_PAGE));
					$smarty->display($tpl);
					$smarty->assign("skip_header",1);
					$total_p=$page;
				}*/
			}
			if ($new){
				for($j=0,$page=1;$j<count($new);$j+=$GRA_ITEMS_PER_PAGE,$page++){
					$no_page=$total_p+$page;
					$this_page_num = ($no_page < $totalpage) ? $GRA_ITEMS_PER_PAGE : $GRA_ITEMS_PER_LAST_PAGE;
					$smarty->assign("PAGE_SIZE", $this_page_num);
					$smarty->assign("is_lastpage", ($no_page >= $totalpage_nonsku));
			        $smarty->assign("page", "Page $no_page of $totalpage");
			        $smarty->assign("start_counter", $j);
			        $smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
					$smarty->display($tpl);
					$smarty->assign("skip_header",1);
				}
			}
			exit;
        case 'do_reset':
		    do_reset($_REQUEST['id'],$branch_id);
		    exit;
		case 'import_by_csv':
			import_by_csv();
			exit;
		case 'ajax_scan_grn_barcode':
			//print_r($_REQUEST);
			$grn_barcode = $_REQUEST['grn_barcode'];
			$sku_info=get_grn_barcode_info($grn_barcode);
			print json_encode($sku_info);
			exit;
		case 'ajax_load_packing_list':
			ajax_load_packing_list();
			exit;
		case 'check_tmp_item_exists':
			check_tmp_item_exists();
			exit;
		case 'print_arms_dn':
			print_arms_dn($_REQUEST['branch_id'], $_REQUEST['id']);
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
      		print_r($_REQUEST);
		    exit;
	}
}

get_gra_items('', true);	
$con->sql_query("select id,description from vendor where active=1 order by description");
$smarty->assign("vendors",$con->sql_fetchrowset());

$prms = array();
$prms['branch_id'] = $sessioninfo['branch_id'];
$prms['date'] = date("Y-m-d");

$smarty->display("goods_return_advice.home.tpl");

function validate_data(&$form)
{
	global $LANG, $branch_id, $con, $sessioninfo, $smarty, $is_under_gst;

	$err = array();
	$form['branch_id'] = $branch_id;

	// item checking
	$q1 = $con->sql_query("select id from tmp_gra_items where gra_id = ".mi($form['id'])." and branch_id = ".mi($branch_id)." limit 1");
	if ($con->sql_numrows($q1)==0){
	    if(count($form['new']['code'])<1){
			$err['sheet'][] = $LANG['GRA_NO_ITEMS'];
		}
		else{
        	$fn1=$form['new']['code'][0];
	    	$fn2=$form['new']['description'][0];
			$fn3=$form['new']['qty'][0];

			if(!$fn1 || !$fn2 || !$fn3){
				$err['sheet'][] = $LANG['GRA_NO_ITEMS'];
				$err['sheet'][] = $LANG['GRA_ITEM_NOT_SKU_INCOMPLETE'];
			}
		}
	}
	$con->sql_freeresult($q1);
	
	// check if the items have been removed or assigned to other GRA
	$q1 =  $con->sql_query($sql="select tgi.* 
							from tmp_gra_items tgi
							where tgi.gra_id = ".mi($form['id'])." and tgi.branch_id = ".mi($branch_id));

	if($con->sql_numrows($q1) > 0){
		while($r = $con->sql_fetchassoc($q1)){
			$q2 = $con->sql_query("select * from gra_items where id = ".mi($r['item_id'])." and branch_id = ".mi($r['branch_id']));
			$item_info = $con->sql_fetchassoc($q2);
			
			if($con->sql_numrows($q2) > 0){ // item existed, check if the item still available
				if($item_info['gra_id'] && $item_info['gra_id'] != $form['id']){ // found it is being assigned to other GRA
					$err['sheet'][] = $LANG['GRA_INVALID_ITEMS'];
					break;
				}
			}else{ // item has been removed, show errors
				$err['sheet'][] = $LANG['GRA_INVALID_ITEMS'];
				break;
			}
			$con->sql_freeresult($q2);
		}
	}
	$con->sql_freeresult($q1);

	$form['misc_info'] = serialize($form['misc_info']);
	$form['extra_amount'] = 0;
	
	if($is_under_gst){
		// re-construct since the data from functions.php does not capture array key by GST id
		$q1 = $con->sql_query("select * from gst where type='purchase' order by id");
		$tmp_gst_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$tmp_gst_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}
	
	$new=$list_new=array();
	if($form['new']){
		foreach ($form['new']['code'] as $idx=>$fn){
			$fn2=$form['new']['description'][$idx];
			$fn3=$form['new']['qty'][$idx];
			$fn4=$form['new']['cost'][$idx];
		    if($fn && $fn2 && $fn3){//exclude the empty field
				$form['extra_amount']+=($form['new']['cost'][$idx]*$form['new']['qty'][$idx]);
				$list_new['code'][$idx] = $new[$idx]['code'] = $fn;
				$list_new['description'][$idx] = $new[$idx]['description'] = $fn2;
				$list_new['qty'][$idx] = $new[$idx]['qty'] = $fn3;
				$list_new['cost'][$idx] = $new[$idx]['cost'] = $fn4;

				// for display purpose
				if($is_under_gst){
					$gst_id = $list_new['gst_id'][$idx] = $new[$idx]['gst_id'] = $form['new']['gst_id'][$idx];
					$list_new['gst_code'][$idx] = $new[$idx]['gst_code'] = $tmp_gst_list[$gst_id]['code'];
					$list_new['gst_rate'][$idx] = $new[$idx]['gst_rate'] = $tmp_gst_list[$gst_id]['rate'];
				}
				
				$list_new['doc_no'][$idx] = $new[$idx]['doc_no'] = $form['new']['doc_no'][$idx];
				$list_new['doc_date'][$idx] = $new[$idx]['doc_date'] = $form['new']['doc_date'][$idx];
				$list_new['reason'][$idx] = $new[$idx]['reason'] = $form['new']['reason'][$idx];
			}
		}
	}
	
	$smarty->assign("new", $new);
	
	$form['extra'] = serialize($list_new);
	if($form['dept_id']){
		$q1 = $con->sql_query("select * from category where id = ".mi($form['dept_id']));
		$dept_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$form['dept_code'] = $dept_info['description'];
	}
	
	return $err;
}

/*function call_cost_sell($r1){

	global $LANG, $con, $sessioninfo,$gra;
	
	$temp=$r1;
	$q2=$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3),grn_items.selling_price
	from grn_items
	left join uom on uom_id = uom.id
	left join sku_items on sku_item_id = sku_items.id
	left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
	left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
	where
	grn_items.branch_id = $r1[branch_id] and grn.approved
	and sku_item_code=$r1[sku_item_code] order by grr.rcv_date desc limit 1");
	$r2 = $con->sql_fetchrow($q2);
	$temp['grn_cost'] = $r2[0];
	$temp['grn_sell'] = $r2[1];


	$q3=$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3),po_items.selling_price
	from po_items
	left join sku_items on sku_item_id = sku_items.id
	left join po on po_id = po.id and po.branch_id = po.branch_id
	where po.active and po.approved and po_items.branch_id = $r1[branch_id] and sku_item_code=".ms($r1['sku_item_code'])." order by po.po_date desc limit 1");
	$r3 = $con->sql_fetchrow($q3);
	$temp['po_cost'] = $r3[0];
	$temp['po_sell'] = $r3[1];

	$q4=$con->sql_query("select cost_price,selling_price from sku_items where sku_item_code=".ms($r1['sku_item_code']));
	$r4 = $con->sql_fetchrow($q4);
	$temp['master_cost'] = $r4[0];
	$temp['master_sell'] = $r4[1];

	$gra['items'][]=$temp;
}
*/

function import_by_csv(){
	global $con, $smarty, $sessioninfo, $dp;
	
	//print_r($_FILES);
	$branch_id = mi($sessioninfo['branch_id']);
	$vendor_id = mi($_REQUEST['vendor_id']);
	$return_type = trim($_REQUEST['return_type']);
	//print "vid = $vendor_id<br />";
	if(!$vendor_id)	show_redir($_SERVER['PHP_SELF'], 'GRA Import by CSV', 'No Vendor is selected.');
	if(!$return_type)	show_redir($_SERVER['PHP_SELF'], 'GRA Import by CSV', 'No Return Type is selected.');
	
	$f = $_FILES['f'];
	if (!$f || $f['error']){
		show_redir($_SERVER['PHP_SELF'], 'GRA Import by CSV', 'Please select file to upload.');
	}
	
	$err = check_upload_file('f', 'csv');
	if($err){
		show_redir($_SERVER['PHP_SELF'], 'GRA Import by CSV', join(', ',$err));
	}
	
	$import_format = $_REQUEST['import_format'] ? $_REQUEST['import_format'] : 1;
	$delimeter = trim($_REQUEST['delimiter']);
	$fp = fopen($f['tmp_name'], "r");
	$warning = array();
	$data = array();
	
	while($r = fgetcsv($fp,4096,$delimeter)){
		/*$filter = array();
		if($sku_item_code)	$filter[] = "si.sku_item_code=".ms($sku_item_code);
		if($mcode)	$filter[] = "si.mcode=".ms($mcode);
		
		if(!$filter)	continue;
		$str_filter = 'where '.join(' and ', $filter);*/
		
		unset($r1);
		if($import_format==1){	// default
			$code = $link_code = trim($r[0]);
			$sku_item_code = trim($r[1]);
			$qty = trim($r[2]);
			
			if ($sku_item_code=='' && $link_code){
				$q0=$con->sql_query("select id, sku_item_code from sku_items where link_code=".ms($link_code)." limit 1");
				$r0= $con->sql_fetchassoc($q0);
				$con->sql_freeresult($q0);
				if($r0){
					$sku_item_code = $r0['sku_item_code'];
				}
			}
			
			if($sku_item_code){
				if(preg_match('/^2[0-9]*$/', $sku_item_code) && strlen($sku_item_code) > 12){
					$sku_item_code_12 = substr($sku_item_code, 0, 12);
					$filter = " or sku_item_code = ".ms($sku_item_code_12);
				}
				
				$q1=$con->sql_query("select id from sku_items where (sku_item_code=".ms($sku_item_code)." or mcode=".ms($sku_item_code).$filter.")");
				$r1= $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
			}

			if(!$r1){
				$warning[] = "Item Not Found: Code#$code, ARMS Code#$sku_item_code";
				continue;
			}elseif(!mf($qty)){
				$warning[] = "Item contains zero qty: Code#$code, ARMS Code#$sku_item_code";
				continue;
			}
		}elseif($import_format==2){	// use grn barcode
			$code = trim($r[0]);
			if (preg_match("/^00/", $code))	// form ARMS' GRN barcoder
			{
				$sku_item_id=mi(substr($code,0,8));
				$qty = mi(substr($code,8,4));
				$sql = "select id from sku_items where id = ".mi($sku_item_id);
			}
			else	// from ATP GRN Barcode, try to search the link-code 
			{
				$linkcode=substr($code,0,7);
				$qty = mi(substr($code,7,5));
				$sql = "select id from sku_items where link_code = ".ms($linkcode);
			}
			$q1 = $con->sql_query($sql);
			$r1 = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if(!$r1){
				$warning[] = "Item Not Found: Code#$code";
				continue;
			}elseif(!mf($qty)){
				$warning[] = "Item contains zero qty: Code#$code";
				continue;
			}
		}elseif($import_format==3){ // standard import
			$code = trim($r[0]);
			$qty = trim($r[1]);
			
			if(preg_match('/^2[0-9]*$/', $code) && strlen($code) > 12){
				$code_12 = substr($code, 0, 12);
				$filter = " or sku_item_code = ".ms($code_12);
			}
			
			$q1=$con->sql_query("select id from sku_items where (sku_item_code=".ms($code)." or mcode=".ms($code)." or link_code=".ms($code).$filter.")");
			$r1 = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if(!$r1){
				$warning[] = "Item Not Found: ARMS Code#$sku_item_code";
				continue;
			}elseif(!mf($qty)){
				$warning[] = "Item contains zero qty: ARMS Code#$sku_item_code";
				continue;
			}
		}
		
		
		$sql = "select si.id,ifnull(sip.price,si.selling_price) as selling_price,si.cost_price,c.department_id,sku.sku_type, sic.grn_cost as lastest_cost
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
		left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
		where si.id = ".mi($r1['id']);
		
		
		//print $sql."<br />";
		$q_sku = $con->sql_query($sql);
		$item = $con->sql_fetchassoc($q_sku);
		$con->sql_freeresult($q_sku);
		
		
		$item['qty'] = $qty;
		$sid = $item['id'];
		
		// select last GRN of each vendor using vendor_sku_history
	    $q_vsh = $con->sql_query("select vsh.cost_price,vsh.source
		from vendor_sku_history vsh  
		where source = 'GRN' and branch_id=$branch_id and sku_item_id=$sid and vendor_id=$vendor_id
		order by added desc limit 1");
		$vsh = $con->sql_fetchassoc($q_vsh);
		$con->sql_freeresult($q_vsh);
		
		if($vsh){
			$item['source'] = sprintf("GRN%05d", $vsh['ref_id']);
			$item['use_cost_price'] = sprintf("%.".$dp."f",$vsh['cost_price']);
		}
		
		// no GRN use PO
		if(!isset($item['use_cost_price'])){
			$q_po = $con->sql_query("select po.po_no, po_items.order_price / po_items.order_uom_fraction as cost_price
from po_items
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
where po_items.sku_item_id=$sid and po_items.branch_id=$branch_id
order by po.po_date desc limit 1");
			$po = $con->sql_fetchassoc($q_po);
			if($po){
				$item['source'] = $po['po_no'];
				$item['use_cost_price'] = sprintf("%.".$dp."f",$po['cost_price']);
			}
		}
		
		// still no po, use lastest cost
		if(!isset($item['use_cost_price']) && $item['lastest_cost']){
			$item['source'] = 'GRN';
			$item['use_cost_price'] = sprintf("%.".$dp."f",$item['lastest_cost']);
		}
		
		// no latest cost, use master
		if(!isset($item['use_cost_price'])){
			$item['source'] = 'Masterfile';
			$item['use_cost_price'] = sprintf("%.".$dp."f",$item['cost_price']);
		}
		
		$data[$item['department_id']][$item['sku_type']][] = $item;
	}
	
	if(!$data && !$warning)	show_redir($_SERVER['PHP_SELF'], 'GRA Import by CSV', 'No data to generate.');
	
	//print_r($data);
	$gra_data = array();
	foreach($data as $dept_id => $sku_type_list){	// loop for each department
		foreach($sku_type_list as $sku_type => $items){	// loop for each sku type
			$gra = array();
			$gra['branch_id'] = $branch_id;
			$gra['user_id'] = $sessioninfo['id'];
			$gra['vendor_id'] = $vendor_id;
			$gra['dept_id'] = $dept_id;
			$gra['added'] = $gra['last_update'] = 'CURRENT_TIMESTAMP';
			$gra['sku_type'] = $sku_type;
			$gra['remark'] = 'Generate by CSV';
			
			$con->sql_query("insert into gra ".mysql_insert_by_field($gra));
			$gra_id = $con->sql_nextid();
			
			$total_amt = 0;
			
			foreach($items as $r){	// loop for each item
				$gi = array();
				$gi['branch_id'] = $branch_id;
				$gi['gra_id'] = $gra_id;
				$gi['user_id'] = $gra['user_id'];
				$gi['sku_item_id'] = $r['id'];
				$gi['vendor_id'] = $vendor_id;
				$gi['qty'] = $r['qty'];
				$gi['added'] = 'CURRENT_TIMESTAMP';
				$gi['cost'] = $r['use_cost_price'];
				$gi['selling_price'] = $r['selling_price'];
				$gi['return_type'] = $return_type;
				
                if($sku_type=='CONSIGN')
                    $gi['amount'] = $gi['qty'] * $gi['selling_price'];
                else
                    $gi['amount'] = $gi['qty'] * $gi['cost'];

				if($is_under_gst){
					$prms = array();
					$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['id']);
					$gst_info = get_sku_gst("input_tax", $r['id']);
					$gi['gst_id'] = $gst_info['id'];
					$gi['gst_code'] = $gst_info['code'];
					$gi['gst_rate'] = $gst_info['rate'];

					$prms['selling_price'] = $gi['selling_price'];
					$prms['inclusive_tax'] = $is_inclusive_tax;
					$prms['gst_rate'] = $gi['gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$gi['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($is_inclusive_tax == "yes"){
						$gi['gst_selling_price'] = $gi['selling_price'];
						$gi['selling_price'] = $gst_sp_info['gst_selling_price'];
					}

                    $gi['amount_gst']=round($gi['amount'] * ((100+$gi['gst_rate'])/100),2);
                    $gi['gst']=$gi['amount_gst']-round($gi['amount'], 2);
				}
                $gi['amount']=round($gi['amount'],2);

				$row_amt = $gi['amount'];
				$con->sql_query("insert into gra_items ".mysql_insert_by_field($gi));
				
				$total_amt+=$row_amt;
			}
			
			$con->sql_query("update gra set amount=".mf($total_amt)." where branch_id=$branch_id and id=$gra_id");
			$gra_data['id_list'][] = $gra_id;
			
			$qry3 = $con->sql_query("select branch.report_prefix from gra left join branch on gra.branch_id =branch.id where gra.id =$gra_id");
			$b_name = $con->sql_fetchassoc($qry3);
			$report_prefix = $b_name['report_prefix'];
			$con->sql_freeresult($qry3);
		}
	}

	
	$smarty->assign('report_prefix',$report_prefix);
	$smarty->assign('warning', $warning);
	$smarty->assign('gra_data', $gra_data);
	$smarty->display('goods_return_advice.generated_gra.tpl');
}

function ajax_load_packing_list(){
	global $con, $smarty, $sessioninfo;
	
	$form = $_REQUEST;

	$ret = $batch_no_count = $packing_list = array();
	$q1 = $con->sql_query("select * from gra_items where gra_id = ".mi($form['id'])." and branch_id = ".mi($form['bid'])." and batchno != 0");
	
	while($r = $con->sql_fetchassoc($q1)){
		$batch_no_count[$r['batchno']]++;
		$packing_list[$r['batchno']] = "<li onclick=\"gra_select_packing_no(this, '".$r['batchno']."');\" id=\"selected_type\" batch_no=\"".$r['batchno']."\">Batch#".$r['batchno']." - ".$batch_no_count[$r['batchno']]." Item(s)</li>";
	}
	$con->sql_freeresult($q1);
	
	if($packing_list){
		$ret['ok'] = 1;
		$ret['html'] = join("", $packing_list);
	}
	
	print json_encode($ret);
}

function check_tmp_item_exists() {
	
	global $con, $smarty, $sessioninfo;
	$gra_id = intval($_REQUEST['id']);
	
	if ($_REQUEST['current_gra_items']) {
		$sql = "select count(*) as c from tmp_gra_items where user_id=".mi($sessioninfo['id'])." and branch_id=".mi($_REQUEST['branch_id'])." and id in (".join(',',$_REQUEST['current_gra_items']).")";
		$con->sql_query($sql);
		if ($con->sql_fetchfield('c') == count($_REQUEST['current_gra_items'])) print 'OK';
		else print "Error saving document : Probably it is opened & saved before in other window/tab";
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}

?>