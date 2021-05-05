<?php
/*
6/22/2011 10:42:12 AM Justin
- Fixed the bugs whenever user confirmed the GRN that without any divisions found.
- Fixed the bugs while confirmed GRN, system shows it is existed.

6/24/2011 3:46:21 PM Justin
- Added a new field to capture GRN is created from GRN future.

7/13/2011 10:47:12 AM Justin
- Moved the copy po items and check grn item's type features to include file for the share with handheld.
- Modified while copy po items into grn items, use grr's document no instead po ID.

7/14/2011 12:11:21 PM Justin
- Fix the bugs where user cannot view the list of grn while he/she has privileges to access.

7/26/2011 6:06:21 PM Justin
- Added Ctn field to compatible with "allow decimal" feature.
- Enhanced to have allow decimal points key in for ctn and pcs.
- Enhanced the system can have return by Ctn >> added new field as "return_ctn".

8/1/2011 2:28:32 PM Justin
- Fixed the document no and type wrongly assign when it is not created from GRN Future.

8/4/2011 10:30:32 PM Justin
- Fixed the GRN that is not editable even user is owner.

8/8/2011 2:12:21 PM Justin
- Fixed the GRN cannot show to those user having require privileges.

8/12/2011 3:42:54 PM Justin
- Added the missing function of "repopulate_vendor_sku_history" and "grn_repopulate_po_receiving_count" for future use.
- Fixed the cost from SKU not in ARMS that rounded up become integer value.

9/8/2011 3:43:54 PM Justin
- Added to update account amount from Account Verification.

9/9/2011 10:48:32 AM Justin
- Fixed the bugs where selling UOM ID save as zero.
- Added auto approve if found the division approver is the last approval of the GRN.
- Added new print option "GRN Summary" from Account Verification (old version of GRN).
- Fixed the bugs where the system cannot load GRN items while return error message.

9/14/2011 3:34:43 PM Justin
- Fixed the bugs where after confirmed, system still show it is not.

9/20/2011 4:30:43 PM Justin
- Added the missing function when the user also is the last approval, update po count & vendor sku history.

4:39 PM 9/22/2011 Justin
- Added the new function that able to follow sort by user inserted sequence.

12:08 PM 9/30/2011 Justin
- Modified to remove the send PM to privilegers but only to PO owner as if GRN contains PO items.

10/5/2011 6:13:21 PM Justin
- Fixed the function  do not capture po item id, cost and po qty while item is not from PO.

10/7/2011 12:47:12 PM Justin
- Fixed the bugs where system cannot capture and insert properly while found having different po child items.

10/20/2011 6:20:53 PM Justin
- Fixed the item that cannot add while in condition of having undelivered PO and adding child item.

10/24/2011 3:25:32 PM Justin
- Fixed system bugs when having undelivered PO item + matched with PO item vs Delivered PO item + matched with PO item remove item bugs.

5:32 PM 11/17/2011 Justin
- Fixed the bugs where system is unable to save further child item if found having more than 2 different child items but sharing same parent sku.
- Fixed the bugs where system is unable to split child follow by po item.

12/29/2011 3:19:43 PM Justin
- Fixed the bugs that last child items that is exactly equal to qty from po items but system doesn't save it.

1/3/2012 10:46:34 AM Justin
- Fixed the double qty bugs.

1/19/2012 6:05:43 PM Justin
- Fixed the double qty bugs while adding from different master SKU items.
- Fixed the bugs that the child split base on po qty is no longer working.

2/24/2012 10:10:32 AM Justin
- Added new feature to capture IBT DO items and becomes GRN items.
- Added new ability to retrieve do items info.
- Added new ability to auto create approval history and capture reject reason when user reject GRN.

3/1/2012 12:05:12 PM Alex
- add get_grn_barcode_info() to get info from grn barcode => grn_ajax_add_item(), ajax_add_item_row()

6/13/2012 2:58:23 PM Justin
- Fixed bugs that using parent SKU item unable to search child SKU item information which received from PO.

6/28/2012 4:28:53 PM Justin
- Modified printing parameter to match properly.

7/2/2012 1:22:45 PM Justin
- Bug Fixed of the GRR list appear 2 times on the list for one GRR.

7/3/2012 3:34:44 PM Justin
- Added new feature that allows user to change GRN owner when GRN document is save as draft.

7/5/2012 12:03:23 PM Justin
- Bug Fixed of can't create GRN from GRR while it is matched the valid condition.

7/13/2012 11:05:23 AM Justin
- Added to do checking whether document can/cannot print D/N Report.
- Enhanced to pick up packing UOM fraction.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/27/2012 9:44:12 AM Justin
- Enhanced child to use parent packing uom fraction while found parent packing uom fraction is not 1.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search on GRN list.
- Enhanced to pickup branch code.

8/7/2012 5:57 PM Justin
- Enhanced to accept new grn barcode scanning format.

8/15/2012 11:28 AM Justin
- Bug fixed on the item not in ARMS is not working after implemented GRN barcoder.
- Enhanced to set the item back to undelivered PO item when delete item.

8/16/2012 11:50 AM Justin
- Enhanced to use parent cost instead of PO cost.

8/22/2012 2:29 PM Justin
- Bug fixed on after update matched with PO items become undelivered item from delete and add this item again, system will show blank row after add the following item.
- Bug fixed on system did not highlight the invalid sku item as new item while it is being rechecked and become valid sku item.

8/23/2012 6:11 PM Justin
- Added to pickup related invoice info.
- Enhanced to only show own branch documents while it is subbranch.
- Bug fixed on PO Cost and Selling Price were calculated wrongly for parent and child.

9/5/2012 10:52 AM Justin
- Allow user to view all saved GRN which created by other user while found privilge "GRN_CHANGE_OWNER".

9/6/2012 1:11 PM Justin
- Bug fixed on system that cannot store the reason while in verify stage.
- Bug fixed on system can't detect suggested selling price after few enhancements has been made.
- Bug fixed on system shows all approved GRN on Account Verification tab.

9/18/2012 9:55 AM Justin
- Enhanced recalculate total selling function to include more filter options.
- Added new function "update_all_total_amount".

9/19/2012 5:05 PM Justin
- Enhanced to show account verification info while in view mode.

10/9/2012 2:37 PM Justin
- Bug fixed on capture wrong selling price, cost and PO cost while receive in carton.

10/12/2012 11:16 AM Justin
- Bug fixed on selling price do not divide correctly.

10/18/2012 4:27 PM Justin
- Enhanced to do checking when user add BOM Package SKU, it will add the item in bom sku list instead of the bom sku.

10/29/2012 12:03 PM Justin
- Bug fixed on system unable to get valid sku item id.

11/7/2012 5:35 PM Justin
- Bug fixed on selling price calculated wrongly.

11/20/2012 2:51 PM Justin
- Bug fixed on system unable to identify new sku items either is child or not in PO/DO.

12/27/2012 11:17 AM Justin
- Bug fixed on capture empty selling price.

1/9/2013 4:24 PM Justin
- Bug fixed on system doesn't update GRR item info as if the GRN is not newly added.

1/10/2013 10:41 AM Justin
- Bug fixed on during user delete items that is imported from PO, system do not reset return ctn or pcs.

1/23/2013 2:00 PM Justin
- Bug fixed on system rarely capture empty cost price during save.

1/25/2013 10:11 AM Justin
- Bug fixed on system does not insert approval history item while the account verifier is the last approval.

2/22/2013 2:25 PM Justin
- Enhanced to have bypass Account Verification section while found config "grn_future_skip_acc_verify" is set.

2/25/2013 3:38 PM Justin
- Enhanced to must go through Account Verification even the GRN is equal to GRR amt base on config "grn_future_require_acc_verify" set.

3/12/2013 11:03 AM Justin
- Bug fixed system will createaccount verification bugs when GRN has been rejected.

4/10/2013 3:48 PM Justin
- Enhanced search engine to search those GRR without GRN and show user the result.

4/12/2013 2:10 PM Justin
- Added new feature to monitor grn items qty must equal or less than po qty if found vendor got turn on this setting.
- Enhanced to multiply available PO qty with packing uom fraction.

5/8/2013 10:40 AM Justin
- Bug fixed on wrongly sum up rcv qty for all users instead of current user.

5/9/2013 4:57 PM Justin
- Enhanced to use sku parent group instead of po item id to sum up available po qty.

5/14/2013 3:33 PM Justin
- Bug fixed on user can double refreshed and get 2 set of po items.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/30/2013 3:17 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

8/22/2013 4:53 PM Justin
- Bug fixed on system wrongly counted the PO items that appears in 2 tables, hidden and visible tables.

8/23/2013 11:27 AM Andy
- Fix sql error when try to send approval pm.

10/7/2013 6:11 PM Justin
- Bug fixed on items from BOM will cause unable to add item.

10/16/2013 4:24 PM Fithri
- if search value is 13 chars (armscode/mcode/linkcode/artno) if not found then use only the first 12 character - this is because barcoder generate 1 extra character

12/6/2013 10:33 AM Justin
- Enhanced to capture log whenever user save GRN.

2/12/2014 5:14 PM Justin
- Enhanced to capture IBT info under GRN table.

3/31/2014 4:38 PM Justin
- Enhanced to check blocked and inactive SKU when scan barcode.

4/21/2014 10:01 AM Justin
- Enhanced to have checking on block items in GRN.

4/28/2014 11:04 AM Justin
- Enhanced to check block item in PO instead of GRN while config "check_block_grn_as_po" is turned on.

8/29/2014 11:24 AM Justin
- Enhanced to update batch no to add "C-xxx" wordings in order for user to reuse the batch no.
- Enhanced to auto cancel adjustment that created from Batch No Setup.

11/11/2014 3:53 PM Justin
- Enhanced to have GST calculation.

3/14/2015 9:42 AM Justin
- Enhanced to have generate and print DN feature.

3/25/2015 1:43 PM Justin
- Bug fixed on current selling price does not calculate exclusive/inclusive gst.

4/3/2015 6:14 PM Justin
- Enhanced to have checking not to insert Account GST info while it is chosen the same as current one.

4/7/2015 2:34 PM Justin
- Bug fixed on print D/N report does not filter off active=0.

4/8/2015 5:50 PM Justin
- Bug fixed on GRR still show out those GRR that being used by GRN.

4/9/2015 11:40 AM Justin
- Bug fixed on GRR list some times could not allow user to create GRN.

4/11/2015 2:48 PM Justin
- Enhanced to have invoice qty under account correction.
- Enhanced to have comparison between GRR and GRN GST amount.

4/14/2015 10:49 AM Justin
- Enhanced GRR to capture GST info.

4/21/2015 4:41 PM Justin
- Enhanced GRR to check GST amount and config while doing save.

4/22/2015 11:34 AM Justin
- Bug fixed on final amount does not update when auto approve.

4/28/2015 6:02 PM Justin
- Enhanced to have generate D/N feature while under Account Verification step.

5/7/2015 9:30 AM Justin
- Enhanced to have invoice price.

5/28/2015 2:44 PM Justin
- Enhanced to always revert to saved GRN when do reject.

5/29/2015 10:12 AM Justin
- Enhanced to bypass document pending if found both GRR and GRN amount is zero.

6/4/2015 3:09 PM Justin
- Bug fixed on system did not set new grr items as used if edit existing GRR.

6/22/2015 11:50 AM Justin
- Bug fixed on last approval of the GRN will cause amount calculate wrongly.

7/20/2015 10:40 AM Joo Chia
- When view GRN, only assign manager_col =1 if form approved to hide account correction column when view before approve.

7/28/2015 2:30 PM Justin
- Bug fixed on cost price did not update accordingly while perform account verification to update UOM.

9/23/2015 9.56 AM DingRen
- always show Account Verification when view waiting approval grn

9/28/2015 5:27 PM DingRen
- add checking to show/hide "Load all PO Items" button

10/13/2015 9:36 AM DingRen
- check gra status when load_grn_list if use_grn_future_allow_generate_gra

11/25/2015 11:00 AM Qiu Ying
- GRN can search GRR

12/14/2015 3:20 PM Qiu Ying
- GRN add misc cost and discount etc, similar to PO

12/15/2015 9:18 PM Qiu Ying
- Add GST rounding adjustment

12/22/2015 5:50 PM DingRen
- fix query error

12/29/2015 3:45 PM Qiu Ying
- Fix GRN cannot show PO amount

02/15/2016 11:58 Edwin
- Bug fixed on remark checking (short/over) on PO items when open first time.

2/29/2016 2:30 PM Qiu Ying
- Auto reset GRR when GRN is canceled by calling the reset_grr function from "goods_receiving_record.include.php"

03/15/2016 11:30 Edwin
- Enhanced on show selling price in add sku items window

03/24/2016 17:15 Edwin
- Added privilege to reset GRN although user level is lower than reset level required

04/01/2016 10:30 Edwin
- Bug fixed on approved or rejected grn without checking whether these actions were executed more than once

5/30/2016 5:58 PM Andy
- Added function fix_po_delivered to fixed po items delivered qty.

6/7/2016 10:42 AM Andy
- Enhanced to allow owner to reset approved grn.

10/17/2016 09:34 AM Qiu Ying
- Bug fixed on when grr is deleted but still active in grn, so when go to cancel grn, error will prompt

10/18/2016 15:26 Qiu Ying
- Bug fixed on "Load all po items" button should only exist when doc type = PO, grn = saved / edit mode, category allow grn to load po = yes

10/28/2016 11:36 AM Andy
- Change "sent to PO Variance" to "Sent to SKU Manage".

10/31/2016 2:23 PM Andy
- Fixed wrong document type and doc_no in grn listing.

1/12/2017 4:14 PM Andy
- Enhanced to use branch_is_under_gst to check gst selling price.

1/13/2017 12:01 PM Andy
- Enhanced to merge grn_cancel and reset_grr to process_reset_grr_grn()

2/7/2017 5:46 PM Andy
- Fixed to only allow HQ user to view other branch GRN when using search function.
- Change the popup error message when found the user try to save the GRN but already login to other branch.
- Change the popup error message when found the user try to save the GRN while the GRN already opened and saved in other tab.

2/9/2017 3:54 PM Andy
- Fixed create grn from grr cannot add item.

2/13/2017 2:27 PM Andy
- Modify the error message to use in language file.

2/22/2017 11:59 AM Justin
- Bug fixed on branch code is missing while creating GRN from GRR.

3/8/2017 5:03 PM Justin
- Enhanced to have new function "grn_sn_handler".
- Enhanced to auto revert all S/N from DO Transfer back to the existing branch while GRN has been reset.
- Enhanced to stop user from confirming the IBT GRN while the receiving qty is lesser than the total of S/N from DO Transfer.

5/10/2017 11:05 AM Justin
- Optimised some of the scripts.

5/15/2017 1:19 PM Justin
- Enhanced to have FOC qty and discount calculation feature for Account Verification.

5/22/2017 2:04 PM Justin
- Enhanced to pickup gst information while owner saved the GRN.

6/2/2017 11:19 AM Justin
- Bug fixed on gst information for SKU not in ARMS will be missing after GRN is fully approved.

6/5/2017 15:15 Qiu Ying
- Enhanced to add GRR Date

6/9/2017 10:27 AM Justin
- Bug fixed on BOM items will also main bom sku which is wrong.

6/30/2017 14:30 Qiu Ying
- Bug fixed on the first approver create and confirm the GRN in account verification, system will still requried the approval from the first approver

7/13/2017 10:37 AM Justin
- Bug fixed on selling price will become zero while GST for branch is not turned on and vendor is under GST registered.

7/19/2017 4:20 PM Justin
- Bug fixed on BOM items will not functioning while user is adding more than 1 BOM at same time.

7/25/2017 1:31 PM Justin
- Bug fixed on duplicated bom items from same bom family  will allow user to add while using multi-add.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

3/13/2018 5:54 PM Justin
- Bug fixed on the pending document somehow did not unserialise the PO cancellation date while do checking.

4/19/2018 3:08 PM Justin
- Enhanced GRN becomes non GST when found GRR is using foreign currency.

7/5/2018 2:51 PM Justin
- Bug fixed on Suggested Selling Price will become zero if GRR contains foreign currency.

8/16/2018 12:08 PM Andy
- Enhanced to check and show error if users perform certain action from different branch.

8/27/2018 2:35 PM Justin
- Enhanced to bring back the GRN Tax.
- Add SST feature.

3:37 PM 9/21/2018 Justin
- Bug fixed there a field did not update correctly while owner was the last approval of the GRN.

10/12/2018 3:08 PM Justin
- Bug fixed on Inv. qty and price does not update when the GRN is not under GST.

10/22/2018 11:11 AM Justin
- Bug fixed on system will generate dummy GRN approval history when user confirmed and reject the GRN.

11/1/2018 10:18 AM Justin
- Bug fixed on turning on skip account verification will bypass the pending document stage.

11/13/2018 2:13 PM Justin
- Enhanced to bypass Account Verification when save from pending document and has config "grn_future_skip_acc_verify " turned on.

11/13/2018 4:08 PM Justin
- Bug fixed on print D/N is not available whenever system is no longer running GST.

5/17/2019 11:47 AM William
- Pickup report_prefix for enhance "GRA","GRR","GRN".

12/20/2019 10:03 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.

5/5/2020 3:41 PM Justin
- Enhanced to remove the owner from able to reset the approved GRN.

5/8/2020 1:00 PM William
- Enhanced to block confirm when the month has closed.

6/9/2020 11:29 AM William
- Bug fixed to exclude inactive sku when GRN use scan barcode to add item.

6/11/2020 3:31 PM William
- Bug fixed, add checking for blocked grn when show multi sku result.

6/26/2020 11:16 AM William
- Bug fixed, when return error message the modified previously will replace.

11/9/2020 9:29 AM William
- Enhance grn module to have "Add item by CSV" and "Export grn Item" feature.

11/13/2020 3:24 PM William
- Change grn item export file name to grn_export_(grn no).
*/
init_selection();

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id == 0){
	$branch_id = $sessioninfo['branch_id'];
}
$id = intval($_REQUEST['id']);

// editing grn from other branch
if(isset($_REQUEST['grn_bid']) && $_REQUEST['grn_bid'] != $sessioninfo['branch_id']){
	$ori_bcode = get_branch_code($_REQUEST['grn_bid']);
	if(is_ajax()){		
		die(sprintf($LANG['GRN_BRANCH_CHANGED'], $ori_bcode, BRANCH_CODE));
	}else{
		if (isset($_REQUEST['a'])){
			if(in_array($_REQUEST['a'], array('cancel', 'open', 'approve', 'confirm', 'save', 'grr_save', 'reject', 'reset', 'do_reset'))){
				$smarty->assign("url", "/goods_receiving_note.php");
				$smarty->assign("title", "Goods Receiving Note");
				$smarty->assign("subject", sprintf($LANG['GRN_BRANCH_CHANGED'], $ori_bcode, BRANCH_CODE));
				$smarty->display("redir.tpl");
				exit;
			}
		}
	}
}

$headers = array(
	'1' => array("item_code" => "Item Code", "cost"=> "Cost", "qty" => "Qty")
);

$sample = array(
	'1' => array(
		array("285020940000", "", "8"),
		array("284357220000", "9.50", "3")
	)
);
//$smarty->assign("div", $_REQUEST['div']);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){

		case 'open_grr':
			open_grr();
			 exit;

	case 'open':
			$con->sql_query("delete from tmp_grn_items where (grn_id>1000000000 and grn_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
		case 'refresh':
			grn_open($id, $branch_id);
			exit;
		
		case 'view':
			grn_view($id, $branch_id);
			exit;
			
		case 'print':
			$print_tpl = array();
			if($_REQUEST['print_grn_summary']){
				if($config['grn_complete_alt_print_template']) $print_tpl[] = $config['grn_complete_alt_print_template'];
				else $print_tpl[] = 'goods_receiving_note_approval.report.print.tpl';
			}
			if($_REQUEST['print_grn_var_report']){
				if($config['grn_alt_print_template']) $print_tpl[] = $config['grn_alt_print_template'];
				else $print_tpl[] = 'goods_receiving_note.print.tpl';
			}
			grn_print($id, $branch_id, $print_tpl);
			exit;
			
		case 'print_grn':
			if($config['grn_future_print_template'])   $print_tpl = $config['grn_future_print_template'];
			else $print_tpl = 'goods_receiving_note.print_grn.tpl';
			grn_print($id, $branch_id, $print_tpl);
			exit;
					
		case 'cancel':
			grn_cancel($id, $branch_id);			
		    exit;
		    
		case 'approve':
		case 'confirm':
		case 'save':
			grn_save($id, $branch_id, $_REQUEST['a']);
			exit;
		case 'ajax_add_item':
			grn_ajax_add_item($id, $branch_id);
			exit;
			
		case 'ajax_delete_row':
			if($_REQUEST['item_id_list']){
				foreach($_REQUEST['item_id_list'] as $item_id){
					$q1 = $con->sql_query("select * from tmp_grn_items where grn_id = ".mi($_REQUEST['grn_id'])." and branch_id = ".mi($branch_id)." and id = ".mi($item_id)." and item_group = 1 and po_qty > 0");
					
					if($con->sql_numrows($q1) > 0){
						$_REQUEST['new_doc_type'] = 0;
					}else{
						unset($_REQUEST['new_doc_type']);
					}
					
					if(!isset($_REQUEST['new_doc_type'])){
						$con->sql_query("delete from tmp_grn_items where id=".mi($item_id)." and branch_id=".mi($branch_id));
					}else{
						$upd = array();
						if(!$_REQUEST['new_doc_type']){
							$upd['ctn'] = 0;
							$upd['pcs'] = 0;
							$upd['return_ctn'] = 0;
							$upd['return_pcs'] = 0;
							$upd['acc_ctn'] = "";
							$upd['acc_pcs'] = "";
							$upd['bom_ref_num'] = 0;
							$upd['bom_qty_ratio'] = 0;
							$upd['inv_qty'] = "";
							$upd['inv_cost'] = "";
							$upd['acc_foc_ctn'] = 0;
							$upd['acc_foc_pcs'] = 0;
							$upd['acc_foc_amt'] = 0;
							$upd['acc_disc'] = 0;
							$upd['acc_disc_amt'] = 0;
						}
						$upd['item_group'] = $_REQUEST['new_doc_type'];
						$con->sql_query("update tmp_grn_items set ".mysql_update_by_field($upd, false, 1)." where id=".mi($item_id)." and branch_id=".mi($branch_id));
					}
				}
			}
			exit;
			
		case 'ajax_add_vendor_sku':
			grn_ajax_add_vendor_sku($id, $branch_id);
			exit;
						
		case 'ajax_show_vendor_sku':
			$smarty->assign("show_varieties",1);
		    get_vendor_sku('po.new.show_sku.tpl');
		    exit;
		    
		case 'ajax_expand_sku':
			if (!isset($_REQUEST['showheader'])) $smarty->assign("hideheader",1);
			$smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']));
		    exit;
						
  		case 'fix_acc_ctnpcs':	        		
        	fix_acc_ctnpcs();
			exit;
			
      	case 'update_total_selling':
			ini_set('display_errors',1);
           	update_total_selling($id,$branch_id);
            exit;

      	case 'update_total_amount':
			ini_set('display_errors',1);
           	update_total_amount($id,$branch_id);
            exit;
			
        case 'update_all_total_selling':
			ini_set('display_errors',1);
			if($_REQUEST['branch_id']) $where = "branch_id = ".mi($_REQUEST['branch_id']);
			update_all_total_selling($where);
			exit;

        case 'update_all_total_amount':
			ini_set('display_errors',1);
			if($_REQUEST['branch_id']) $where = "where branch_id = ".mi($_REQUEST['branch_id']);
			update_all_total_amount($where);
			exit;
	
			
        case 'update_big_selling':
			ini_set('display_errors',1);
			$where .= "total_selling>final_amount*2";
			if($_REQUEST['branch_id']) $where = " and branch_id = ".mi($_REQUEST['branch_id']);
			update_all_total_selling($where);
            exit;
			
        case 'update_zero_selling':
			ini_set('display_errors',1);
			$where = "(total_selling=0 or amount > total_selling)";
			if($_REQUEST['branch_id']) $where .= " and branch_id = ".mi($_REQUEST['branch_id']);
 			update_all_total_selling($where);           
            exit;	
			        
		case 'ajax_load_grn_list':
			load_grn_list();
			exit;
        case 'reset':
		    $con->sql_query("update grn set div".$_REQUEST['div']."_approved_by = 0 where id = ".mi($id)." and branch_id = ".mi($branch_id));
			log_br($sessioninfo['id'], 'GRN', $id, "Goods Receiving Note reset by $sessioninfo[u] for division $_REQUEST[div] (ID#$id)");
		    header("Location: /goods_receiving_note.php?id=".$id."&t=".$_REQUEST['a']);
		    exit;
		case 'ajax_add_item_row':
		    ajax_add_item_row($id, $branch_id);
		    exit;
		case 'ajax_add_variance_item':
		    ajax_add_variance_item($id, $branch_id);
		    exit;
		case 'ajax_add_return_item':
		    ajax_add_return_item($id, $branch_id);
		    exit;
		case 'ajax_recheck_nsi':
		    ajax_recheck_nsi($id, $branch_id);
		    exit;
        case 'do_reset':
		    do_reset($id,$branch_id);
		    exit;
        case 'reject':
			do_reject($id, $branch_id);
		    exit;
		case 'repopulate_vendor_sku_history':
		    grn_repopulate_vendor_sku_history();
			exit;
		case 'repopulate_po_receiving_count':
		    grn_repopulate_po_receiving_count();
			exit;
		case 'grr_save':
			$form = $_REQUEST;
			grr_save($form['grr_id'], $form['branch_id']);
			exit;
		case 'grn_change_owner':
			$form = $_REQUEST;
			grn_change_owner($form['id'], $form['branch_id']);
			exit;
			case 'ajax_validate_dn_report':
			ajax_validate_dn_report();
			exit;
		case 'fix_grn_selling_price':
			grn_fix_selling_price($_REQUEST['id'], $_REQUEST['bid']);
			exit;
		case 'fix_grn_cost':
			fix_grn_cost($_REQUEST['id'], $_REQUEST['bid']);
			exit;
		case 'check_tmp_item_exists':
			check_tmp_item_exists();
			exit;
		case 'print_arms_dn':
			print_arms_dn($_REQUEST['branch_id'], $_REQUEST['id']);
			exit;
		case 'ajax_generate_dn':
			ajax_generate_dn();
			exit;
		case 'fix_po_delivered':
			fix_po_delivered();
			exit;
		case 'download_sample_grn':
			download_sample_grn();
			exit;
		case 'ajax_open_csv_popup':
			ajax_open_csv_popup();
			exit;
		case 'show_result':
			show_result();
			exit;
		case 'ajax_get_uploaded_csv_result':
			ajax_get_uploaded_csv_result();
			exit;
		case 'ajax_import_grn':
			ajax_import_grn();
			exit;
		case 'export_grn_item':
			export_grn_item();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
      		print_r($_REQUEST);
		    exit;
	}
}

list_grr();
$smarty->display("goods_receiving_note.home.tpl");
exit;

function grn_save($grn_id, $branch_id, $confirm_type, $is_generate_dn=0){
	global $con, $smarty, $LANG, $sessioninfo, $config, $appCore;

	/*
		$form['action']
		- edit = Owner Editing
		- modify = authorized user (have privileges) confirming GRN
		- !$form['action'] = Account Verification
	*/
	
	$form=$_REQUEST;
	//print_r($form);exit;
	$tt=0;
	$err=array();
	$upd = array();
	$form['have_variance']=0;
	$form['total_selling']=0;
	$nsi=array();
	$status=0;
	$approved=0;
	$mwpo_rcv_qty=$mwpo_info=$mwpoip_rcv_qty=$mwpoip_siid=$mwpoip_piid=$del_prev_mwpip=$item_seq=$child_reserved=array();
	
	$grr_id = intval($form['grr_id']);	
	$con->sql_query("select id from grr where active=1 and id=$grr_id and branch_id=$branch_id");
	$grr = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if (!$grr){
		$smarty->assign("url", "/goods_receiving_note.php");
		$smarty->assign("title", "Goods Receiving Note");
		$smarty->assign("subject", sprintf($LANG['GRR_DELETED'], $grr_id));
		$smarty->display("redir.tpl");
		exit;
	}
	
	
	$q=$con->sql_query("select id from grn where active and grr_id=$grr_id and branch_id=$branch_id and id <> $grn_id");			
	if($r = $con->sql_fetchassoc($q)){
		$smarty->assign("url", "/goods_receiving_note.php");
	    $smarty->assign("title", "Goods Receiving Note");
	    $smarty->assign("subject", sprintf($LANG['GRN_GRR_ITEM_USED'], $grr_id, $grn_id));
	    $smarty->display("redir.tpl");
		exit;
	}
	$con->sql_freeresult($q);

	/*if($config['monthly_closing']){
		$con->sql_query("select rcv_date from grr where id=$grr_id and branch_id=$branch_id");
		$date = $con->sql_fetchrow();
		$con->sql_freeresult();
		$is_month_closed = $appCore->is_month_closed($date['rcv_date']);
		if($is_month_closed && ($config['monthly_closing_block_document_action'] || $confirm_type == "confirm")){
			$err['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
	}*/
	
	

	$is_ibt = $non_ibt = $from_bid = 0;

	$q1 = $con->sql_query("select * from grr_items where grr_id = ".mi($grr_id)." and branch_id = ".mi($branch_id));

	/*
	search grr item doc no either is below statement:
	GRR doc_type = "DO" + GRR doc_no = DO do_no, update grn column is_ibt = 1
	GRR doc_type = "PO" + GRR doc_no = po po_no, update grn column is_ibt = 1
	*/
	while($r = $con->sql_fetchassoc($q1)){
		if($r['type'] != "DO" && $r['type'] != "PO"){
			continue; // skip to check for error since not DO and PO
		}elseif($r['type'] == "DO"){
			$sql = $con->sql_query("select * from do where do_no = ".ms($r['doc_no'])." and do_branch_id = ".mi($r['branch_id']));
		}elseif($r['type'] == "PO"){
			$sql = $con->sql_query("select * from po where po_no = ".ms($r['doc_no'])." and po_branch_id = ".mi($r['branch_id'])."  and is_ibt = 1");
		}

		if($con->sql_numrows($sql) > 0){
			$tmp = $con->sql_fetchassoc($sql);
			$is_ibt = 1;
			$from_bid = $tmp['branch_id'];
		}else $non_ibt = 1;

		if($is_ibt && $non_ibt) break; // stop the loop and rdy to display error msg
	}
	$con->sql_freeresult($q1);

	// found if having both IBT and non IBT in one GRR then display error msg
	if($is_ibt && $non_ibt) $err['top'][] = $LANG['GRR_IBT_ERROR'];
	
	// search item that matched with PO/item parent to do adjustments of the rcv qty...
	if($form['0_sku_item_code'] && !$err && $form['action'] == 'edit'){
		foreach($form['0_sku_item_code'] as $k=>$dummy){
			// item that matched with PO
			$si_id = mi($form['0_sku_item_id'][$k]);
			if($form['1_sku_item_code']){
				foreach($form['1_sku_item_code'] as $l=>$dummy1){
					$curr_rcv_qty = $form['1_uom_fraction'][$l] * $form['1_ctn'][$l] + $form['1_pcs'][$l];
					if($form['1_item_group'][$l] == 1 && $form['1_po_item_id'][$l] == $form['0_po_item_id'][$k] && $form['1_sku_item_id'][$l] == $si_id){
						$mwpo_rcv_qty[$si_id] += $curr_rcv_qty;
						$mwpo_info[$si_id]['bom_ref_num'] = $form['1_bom_ref_num'][$l];
						$mwpo_info[$si_id]['bom_qty_ratio'] = $form['1_bom_qty_ratio'][$l];
						unset($form['1_sku_item_code'][$l]);
						unset($form['1_uom_id'][$l]);
					}elseif($form['1_item_group'][$l] == 2 && $form['1_po_item_id'][$l] == $form['0_po_item_id'][$k] && $form['1_sku_item_id'][$l] != $si_id && $form['1_bom_ref_num'][$l] > 0){
						$mwpoip_rcv_qty[$si_id][$l] = $curr_rcv_qty;
						$mwpoip_siid[$si_id][$l] = $form['1_sku_item_id'][$l];
						$mwpoip_piid[$si_id][$l] = $form['1_po_item_id'][$l];
					}
					
					if(!$item_seq[$form['1_sku_item_id'][$l]]) $item_seq[$form['1_sku_item_id'][$l]] = $form['1_item_seq'][$l];
				}
			}

			 // found got rcv qty from matched with PO
			if(isset($mwpo_rcv_qty[$si_id])){
				$form['0_item_group'][$k] = 1;
				$form['0_item_seq'][$k] = $item_seq[$si_id];
				$form['0_bom_ref_num'][$k] = $mwpo_info[$si_id]['bom_ref_num'];
				$form['0_bom_qty_ratio'][$k] = $mwpo_info[$si_id]['bom_qty_ratio'];
				if($form['0_po_qty'][$k] >= $mwpo_rcv_qty[$si_id]){ // if PO qty is larger than rcv qty
					$form['0_pcs'][$k] = $mwpo_rcv_qty[$si_id];
					unset($mwpo_rcv_qty[$si_id]);
				}elseif($form['0_po_qty'][$k] < $mwpo_rcv_qty[$si_id]){ // found if PO qty is less than rcv qty
					$mwpo_rcv_qty[$si_id] -= $form['0_po_qty'][$k];
					$form['0_pcs'][$k] = $form['0_po_qty'][$k];
				}

				// delete the temp row for matched with PO
				$con->sql_query("delete from tmp_grn_items where item_group = 1 and po_qty = 0 and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and sku_item_id = ".mi($si_id));
			}

			$bal_po_qty = $form['0_po_qty'][$k]-$form['0_pcs'][$k];

			// found got rcv qty from match with PO item parent
			// do this when matched with PO rcv qty is empty
			if(!$mwpo_rcv_qty[$si_id] && $bal_po_qty > 0 && count($mwpoip_rcv_qty[$si_id]) > 0){
				// delete the temp row for matched with PO item parent
				foreach($mwpoip_rcv_qty as $msiid=>$gi_list){
					if($msiid != $si_id) continue;
					foreach($gi_list as $gi_id=>$qty){
						//if($form['0_po_item_id'][$k] != $mwpoip_piid[$msiid][$gi_id]) continue;
						$siid = $mwpoip_siid[$msiid][$gi_id];
						$qty_bal = $mwpoip_rcv_qty[$msiid][$gi_id] - $bal_po_qty;
						//print $qty_bal."<br />";
						if($qty_bal < 0){ // the keyed in qty of matched with po item parent is short
							$rcv_pcs = $mwpoip_rcv_qty[$msiid][$gi_id];
							$bal_po_qty -= $rcv_pcs;
							unset($mwpoip_rcv_qty[$msiid][$gi_id]);
						}else{
							$rcv_pcs = $mwpoip_rcv_qty[$msiid][$gi_id];
							$mwpoip_rcv_qty[$msiid][$gi_id] -= $bal_po_qty;
							if($prv_msiid == $msiid && $prv_siid != $siid){
								if(!$mwpoip_rcv_qty[$msiid][$gi_id]) unset($mwpoip_rcv_qty[$msiid][$gi_id]);
								unset($mwpoip_rcv_qty[$msiid][$gi_id]);
							}
							else $rcv_pcs = $bal_po_qty;
							$bal_po_qty = 0;
						}
						$prv_msiid = $msiid;
						$prv_siid = $siid;

						//print $rcv_pcs."<br />";
						if($rcv_pcs == 0){
							if($mwpoip_rcv_qty[$msiid][$gi_id] > 0) $tmp_gi_id_list[$msiid][$gi_id] = $last_tmp_gi_id;
							continue;
						}
						// get child uom id and fraction
						$con->sql_query("select ".mi($form['1_uom_id'][$gi_id])." as uom_id, uom.fraction as sku_fraction, si.sku_item_code, si.packing_uom_id as master_uom_id, ifnull(sip.price, si.selling_price) as selling_price
										 from sku_items si
										 left join uom on uom.id = si.packing_uom_id 
										 left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($branch_id)."
										 where si.id = ".mi($siid));

						$uom_id = $con->sql_fetchfield(0);
						$sku_uom = $con->sql_fetchfield(1);
						$sku_item_code = $con->sql_fetchfield(2);
						$master_uom_id = $con->sql_fetchfield(3);
						$curr_selling_price = $con->sql_fetchfield(4);
						$org_sku_uom = $form['1_uom_fraction'][$gi_id];
						$org_selling_uom = $form['1_selling_uomf'][$gi_id];
						$con->sql_freeresult();
						
						// get master uom fraction
						$con->sql_query("select uom.fraction
										 from sku_items si
										 left join uom on uom.id = si.packing_uom_id
										 where si.id = ".mi($msiid));
						$selling_uom_fraction = $mst_uom_fraction = $con->sql_fetchfield(0);
						$con->sql_freeresult();

						if($mst_uom_fraction == 1){
							$mst_uom_fraction = $form['0_uom_fraction'][$k];
							if($form['0_cost'][$k] > $form['0_selling_price'][$k]){
								$selling_uom_fraction = $mst_uom_fraction;
							}
						}
						
						$curr_cost = $form['0_cost'][$k] * ($sku_uom / $mst_uom_fraction) * $org_sku_uom;
						$curr_po_cost = $form['0_po_cost'][$k] * ($sku_uom / $mst_uom_fraction) * $org_sku_uom;
						$curr_sp = $form['0_selling_price'][$k] * ($sku_uom / $selling_uom_fraction) * $org_sku_uom;
						$bom_ref_num = $form['1_bom_ref_num'][$gi_id];
						$bom_qty_ratio = $form['1_bom_qty_ratio'][$gi_id];

						if($rcv_pcs){
							$q1 = $con->sql_query("select $branch_id as branch_id, $sessioninfo[id] as user_id, $grn_id as grn_id, 
												 $siid as sku_item_id, tgi.artno_mcode, $uom_id as uom_id, 
												 $curr_cost as cost, $curr_sp as selling_price, $rcv_pcs as pcs, 0 as po_qty, $curr_po_cost as po_cost, tgi.po_item_id, tgi.weight, tgi.original_cost, 
												 2 as item_group, $bom_ref_num as bom_ref_num, $bom_qty_ratio as bom_qty_ratio
												 from tmp_grn_items tgi
												 left join uom on uom.id = tgi.uom_id
												 where tgi.branch_id = ".mi($branch_id)."
												 and tgi.grn_id =".mi($grn_id)."
												 and (tgi.item_group = 0 or tgi.item_group = 1)
												 and tgi.po_item_id != 0
												 and tgi.id = ".mi($k));
							$mst_info = $con->sql_fetchassoc($q1);
							$con->sql_freeresult($q1);
							
							// call appCore to generate new ID
							unset($new_id);
							$new_id = $appCore->generateNewID("tmp_grn_items", "branch_id = ".mi($branch_id));
							
							if(!$new_id) die("Unable to generate new ID from appCore!");
							
							$mst_info['id'] = $new_id;
							$mst_info['item_seq'] = $item_seq[$siid];
							$con->sql_query("insert into tmp_grn_items ".mysql_insert_by_field($mst_info));

							$last_tmp_gi_id = $con->sql_nextid();
							
							if($last_tmp_gi_id && ($form['type'] == "PO" || $form['is_ibt_do'])){
								// check those items that having different selling price
								/*$con->sql_query("select tgi.*, uom.fraction as uom_fraction, ifnull(sip.price, si.selling_price) as selling_price, 
												 ifnull(tgi.selling_price, 0) as sug_selling_price
												 from tmp_grn_items tgi
												 left join sku_items si on tgi.sku_item_id = si.id
												 left join sku_items_price sip on sip.sku_item_id = tgi.sku_item_id and sip.branch_id = tgi.branch_id
												 left join uom on uom.id = tgi.uom_id
												 where tgi.grn_id = ".mi($grn_id)."
												 and tgi.branch_id = ".mi($branch_id)."
												 and tgi.id = ".mi($last_tmp_gi_id));

								if($con->sql_numrows() > 0){*/
								$grn_item = array();
								$form['1_sku_item_code'][$last_tmp_gi_id] = $sku_item_code;
								$form['1_item_return'][$last_tmp_gi_id] = 0;
								$form['1_cost'][$last_tmp_gi_id] = $mst_info['cost'];
								$form['1_ctn'][$last_tmp_gi_id] = $mst_info['ctn'];
								$form['1_pcs'][$last_tmp_gi_id] = $mst_info['pcs'];
								$form['1_po_cost'][$last_tmp_gi_id] = $mst_info['po_cost'];
								$form['1_po_item_id'][$last_tmp_gi_id] = $mst_info['po_item_id'];
								$form['1_uom_id'][$last_tmp_gi_id] = $uom_id;
								$form['1_uom_fraction'][$last_tmp_gi_id] = $org_sku_uom;
								$form['1_sku_item_id'][$last_tmp_gi_id] = $mst_info['sku_item_id'];
								$form['1_master_uom_id'][$last_tmp_gi_id] = $master_uom_id;
								$form['1_selling_price'][$last_tmp_gi_id] = $mst_info['selling_price'];
								$form['1_curr_selling_price'][$last_tmp_gi_id] = $curr_selling_price;
								$form['1_item_group'][$last_tmp_gi_id] = $mst_info['item_group'];
								$tmp_b = $mst_info['ctn'] + $mst_info['pcs'] / $org_sku_uom;
								$form['1_amt'][$last_tmp_gi_id] = round($tmp_b * $mst_info['cost'], 2);
								$form['1_item_seq'][$last_tmp_gi_id] = $mst_info['item_seq'];
								$form['1_bom_ref_num'][$last_tmp_gi_id] = $mst_info['bom_ref_num'];
								$form['1_bom_qty_ratio'][$last_tmp_gi_id] = $mst_info['bom_qty_ratio'];
								$tmp_gi_id_list[$msiid][$gi_id] = $last_tmp_gi_id;
								//}
							}

							if(!$del_prev_mwpip[$mst_info['po_item_id']]){
								$con->sql_query("delete from tmp_grn_items where item_group = 2 and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and id < ".mi($last_tmp_gi_id)." and po_item_id = ".mi($mst_info['po_item_id'])." and bom_ref_num != 0");
								$del_prev_mwpip[$mst_info['po_item_id']] = true;
							}
							
							$item_seq[$siid] = $item_seq[$siid]+0.01; // add 0.01 incase it explode into many childs
						}
					}
				}
			}
			$last_grn_si_id[$si_id] = $k;
		}

		// check and found if still got rcv qty left for matched with PO
		if(count($mwpo_rcv_qty)>0){
			foreach($mwpo_rcv_qty as $siid=>$rcv_qty){
				// loop again 
				foreach($last_grn_si_id as $siid1=>$tmp_gi_id){
					if($siid1 == $siid){
						$form['0_pcs'][$tmp_gi_id] += $rcv_qty;
						unset($mwpo_rcv_qty[$siid]);
					}
				}
			}
		}

		// check and found if still got rcv qty left for matched with PO item parent
		if(count($mwpoip_rcv_qty)>0){
			foreach($mwpoip_rcv_qty as $msiid=>$gi_list){
				foreach($gi_list as $gi_id=>$qty){
					$last_tmp_gi_id = $tmp_gi_id_list[$msiid][$gi_id];
					$form['1_pcs'][$last_tmp_gi_id] += $qty;
					unset($mwpoip_rcv_qty[$msiid][$gi_id]);
				}
			}
		}
	}

	//exit;
	if(($form['type'] == "PO" || $form['is_ibt_do']) && $form['1_sku_item_id'] && $form['action'] == "edit" && $confirm_type == "confirm"){
		foreach($form['1_sku_item_id'] as $k=>$dummy){
			if($form['branch_is_under_gst']){
				$is_inclusive_tax = get_sku_gst("inclusive_tax", $form['1_sku_item_id'][$k]);
				if($is_inclusive_tax == "no") $selling_price = $form['1_selling_price'][$k];
				else $selling_price = $form['1_gst_selling_price'][$k];
			}else $selling_price = $form['1_selling_price'][$k];

			switch($config['grn_check_selling_price']){
				case "LOWER":
					if($selling_price < $form['1_curr_selling_price'][$k]) $have_diff_sp = true;
					break;
				case "HIGHER":
					if($selling_price > $form['1_curr_selling_price'][$k]) $have_diff_sp = true;
					break;
				default:
					if($selling_price != $form['1_curr_selling_price'][$k]) $have_diff_sp = true;
					break;
			}
			if($have_diff_sp) break;
		}
	}

	$_REQUEST = $form; // set all the current changes to form and replace into REQUEST

	save_grn_items();
	
	// check if the user trying to input qty which is lesser than the sold qty from S/N
	if($form['action'] == "edit" && $config['enable_sn_bn']){
		$prms = array();
		$prms['grn_id'] = $grn_id;
		$prms['branch_id'] = $branch_id;
		$prms['grr_id'] = $grr_id;
		$prms['use_tmp'] = true;
		$prms['process_type'] = "validate";
		$sn_err = array();
		$sn_err = grn_sn_handler($prms);

		if($sn_err){
			if($err['top']) $err['top'] = array_merge($err['top'], $sn_err);
			else $err['top'] = $sn_err;
		}
	}
	
	// check if vendor got tick want to monitor grn items qty not over po qty
	$q1 = $con->sql_query("select v.grn_qty_no_over_po_qty 
						   from grr
						   left join vendor v on v.id = grr.vendor_id						   
						   where grr.id = ".mi($grr_id)." and grr.branch_id = ".mi($branch_id));
	$grr_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	$po_info = $grn_qty = $sku_list = array();
	if($grr_info['grn_qty_no_over_po_qty']){
		$q1 = $con->sql_query("select tgi.*, si.sku_item_code, si.description, si.sku_id, u.fraction as uom_fraction,
							   pkuom.fraction as packing_uom_fraction
							   from tmp_grn_items tgi 
							   left join sku_items si on si.id = tgi.sku_item_id
							   left join uom u on u.id = tgi.uom_id
							   left join uom pkuom on pkuom.id = si.packing_uom_id
							   where tgi.grn_id = ".mi($grn_id)." and tgi.branch_id = ".mi($branch_id)." and tgi.user_id = ".mi($sessioninfo['id'])." and tgi.sku_item_id != 0");
		
		while($r = $con->sql_fetchassoc($q1)){
			if($r['po_item_id']){
				if($r['item_group']) $info[$r['sku_id']]['si_list'][$r['sku_item_code']] = $r['sku_item_code'];
				if($r['available_po_qty']) $info[$r['sku_id']]['available_po_qty'] += $r['available_po_qty'];
				$info[$r['sku_id']]['rcv_qty'] += (($r['uom_fraction'] * $r['ctn']) + $r['pcs']) * $r['packing_uom_fraction'];
			}
		}

		foreach($info as $sku_id=>$f){
			if($f['rcv_qty'] > $f['available_po_qty']) $err['top'][] = sprintf($LANG['GRN_ITEM_OVER_PO_QTY'], join(", ", $f['si_list']), mi($f['available_po_qty']));
		}
		
		if($form['type'] == "PO"){
			foreach($form['3_sku_item_id'] as $k=>$sid){
				$err['top'][] = sprintf($LANG['GRN_ITEM_OVER_PO_QTY_ITEM_REJECTED'], $form['3_sku_item_code'][$k]);
			}
		}
	}

	if($form['action'] == "edit"){
		$tbl_start = 0;
		$tbl_end = 4;
	}elseif($form['action'] == "verify"){
		$tbl_start = 1;
		$tbl_end = 5;
	}

	for($doc_type=$tbl_start; $doc_type<=$tbl_end; $doc_type++){
		if($form[$doc_type.'_sku_item_code']){
			foreach($form[$doc_type.'_sku_item_code'] as $k=>$dummy){
				$foc_var = array();
				/*if($doc_type == 0){
					$form[$doc_type.'_amt'][$k] = round($form[$doc_type."_cost"][$k]*($form[$doc_type."_ctn"][$k]+(($form[$doc_type."_pcs"][$k]-($form[$doc_type."_return_pcs"][$k]*$form[$doc_type."_uom_fraction"][$k])+$form[$doc_type."_return_pcs"][$k])/$form[$doc_type."_uom_fraction"][$k])), 2);
				}*/
			    if(!$form[$doc_type.'_item_return'][$k] || $form[$doc_type.'_item_return'][$k] == 0){
				    $rowqty = doubleval(($form[$doc_type.'_ctn'][$k] * $form[$doc_type.'_uom_fraction'][$k]) + $form[$doc_type.'_pcs'][$k]) - doubleval(($form[$doc_type.'_return_ctn'][$k] * $form[$doc_type.'_uom_fraction'][$k]) - $form[$doc_type.'_return_pcs'][$k]);
	
					if($form[$doc_type.'_po_item_id'][$k] && $rowqty!=$form[$doc_type.'_po_qty'][$k]){
					    //print "ID = ".$k." Rcv Qty = ".$rowqty." and PO Qty = ".$form[$doc_type.'_po_qty'][$k]."<br />";
						$form['have_variance']+=$rowqty-$form[$doc_type.'_po_qty'][$k];
						//print $k." is item group of ".$form[$doc_type.'_item_group'][$k]."<br />";
						if($form[$doc_type.'_item_group'][$k] < 2){
							// calculate the FOC variance...
							$con->sql_query("select
											 sum(po_items.qty * order_uom_fraction + po_items.qty_loose) as po_qty,
											 sum(po_items.foc * order_uom_fraction + po_items.foc_loose) as foc_qty
											 from po_items 
											 where po_items.id = ".ms($form[$doc_type.'_po_item_id'][$k])." and po_items.branch_id = ".ms($branch_id)."
											 group by po_items.id");
							$foc_var = $con->sql_fetchrow();
							$con->sql_freeresult();
							
							$ext_qty = $rowqty - $foc_var['po_qty'];
	
							if($ext_qty>=0 && $ext_qty<$foc_var['foc_qty']){ // it is equal or grater than po qty and less than foc qty
								$form['foc_variance']+=$foc_var['foc_qty']-$ext_qty;
							}elseif($ext_qty<0){ // it is less than the po qty, straight add up the foc qty from po
								$form['foc_variance']+=$foc_var['foc_qty'];
							}
						}
						
					//print "current foc_var = ".$form['foc_variance']." and foc = ".$foc_var['foc_qty']."<br />";
					}/*if($form[$doc_type.'_selling_uomf'][$k]){
						$form['total_selling'] += $rowqty*round($form[$doc_type.'_selling_price'][$k]/$form[$doc_type.'_uom_fraction'][$k], 2);
					}*/
				}
			}
			$have_items = true;
		}

		if($doc_type == 4 && $form[$doc_type.'_sku_item_code']){
			// need to select one invoice information for gra usage
			if($form['is_under_gst']){
				$q1 = $con->sql_query("select *, case when type = 'INVOICE' then 1 when type = 'DO' then 2 else 3 end as type_asc from grr_items where grr_id = ".mi($grr_id)." and branch_id = ".mi($branch_id)." and type != 'PO' and doc_date != '' and doc_date is not null order by type_asc limit 1");
				$nsi_gst_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				// need to select default gst code OP if gst from GST couldn't be found 
				if(!$nsi_gst_info){
					$q1 = $con->sql_query("select id as gst_id, code as gst_code, rate as gst_rate from gst where code = 'OP' and type = 'purchase'");
					$nsi_gst_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
				}
			}
			
			foreach($form[$doc_type.'_sku_item_code'] as $k=>$dummy){
				$nsi['code'][] = $dummy;
				$nsi['description'][] = $form[$doc_type.'_description'][$k];
				$nsi['qty'][] = doubleval($form[$doc_type.'_pcs'][$k]);
				$nsi['cost'][] = doubleval($form[$doc_type.'_cost'][$k]);
				$nsi['i_c'][] = mi($form[$doc_type.'_item_return'][$k]);
				
				if($form['is_under_gst']){
					if(!$form[$doc_type.'_gst_id'][$k]){
						$nsi['gst_id'][] = $nsi_gst_info['gst_id'];
						$nsi['gst_code'][] = $nsi_gst_info['gst_code'];
						$nsi['gst_rate'][] = $nsi_gst_info['gst_rate'];
						if($nsi_gst_info['doc_no']) $nsi['doc_no'][] = $nsi_gst_info['doc_no'];
						if($nsi_gst_info['doc_date']) $nsi['doc_date'][] = $nsi_gst_info['doc_date'];
					}else{
						$nsi['gst_id'][] = $form[$doc_type.'_gst_id'][$k];
						$nsi['gst_code'][] = $form[$doc_type.'_gst_code'][$k];
						$nsi['gst_rate'][] = $form[$doc_type.'_gst_rate'][$k];
						if($form[$doc_type.'_doc_no'][$k]) $nsi['doc_no'][] = $form[$doc_type.'_doc_no'][$k];
						if($form[$doc_type.'_doc_date'][$k]) $nsi['doc_date'][] = $form[$doc_type.'_doc_date'][$k];
					}
				}
			}
			$have_items = true;
		}
	}
	
	if((!$have_items && $form['action']) || (!$form['action'] && !$form['pcs'])) $err['top'][] = $LANG['GRN_NO_ITEM']; 

	// qty can zero 
	// if ($qty==0) { $err['top'][] = $LANG['GRN_QTY_ZERO']; }

	if(count($nsi) > 0){
		$_REQUEST['non_sku_items'] = $nsi;
		$nsi = serialize($nsi);
	}else{ // if found no items that not belongs to ARMS
		if($form['action'] == "edit" && $confirm_type == "confirm"){
			$div2_approved_by = mi($sessioninfo['id']); // do not to go thru SKU apply confirmation
		}
		unset($nsi);
	}

	if($form['action'] == "edit" && $confirm_type == "confirm"){
		
		if(!$nsi) $div2_approved_by = mi($sessioninfo['id']); // do not need to go thru SKU apply confirmation

		// if found no selling price differences, straight mark price change as "confirmed"
		if(!$have_diff_sp) $div3_approved_by = mi($sessioninfo['id']);
		
		if($form['type'] != "PO"){
			$div1_approved_by = mi($sessioninfo['id']); // do not need to go thru PO Variance confirmation
			$div3_approved_by = mi($sessioninfo['id']); // do not need to go thru Price Change confirmation
		}
	}

	if(!$err){
		if(!is_new_id($grn_id)){
		    if($form['action']){ // sku manage
		    	$grn_upd = array();
				if($form['action'] == "edit"){
					$grn_upd['div1_approved_by'] = $div1_approved_by;
					$grn_upd['div2_approved_by'] = $div2_approved_by;
					$grn_upd['div3_approved_by'] = $div3_approved_by;
				}
				$grn_upd['have_variance'] = doubleval(abs($form['have_variance']));
				$grn_upd['foc_variance'] = doubleval(abs($form['foc_variance']));
				$grn_upd['total_selling'] = mf($form['total_selling']);
				$grn_upd['amount'] = mf($form['amount']);
				$grn_upd['final_amount'] = mf($form['amount']);
				$grn_upd['department_id'] = mi($form['department_id']);
				$grn_upd['grn_tax'] = mf($form['grn_tax']);
				$grn_upd['non_sku_items'] = $nsi;
				$grn_upd['generate_gra'] = mi($form['generate_gra']);
				if($is_ibt){
					$grn_upd['is_ibt'] = $is_ibt;
					$grn_upd['from_branch_id'] = mi($from_bid);
				}

				$con->sql_query("update grn set ".mysql_update_by_field($grn_upd)." where branch_id=$branch_id and id=$grn_id");
		    }else{ // account verification
				$acc_upd = array();
				$acc_upd['rounding_amt'] = $form['rounding_amt'];
				$acc_upd['account_amount'] = $form['account_amount'];
				$acc_upd['account_update'] = 'CURRENT_TIMESTAMP';
				$acc_upd['acc_adjustment'] = $form['acc_adjustment'];
				$acc_upd['action_adjustment'] = $form['action_adjustment'];
				$acc_upd['acc_action'] = $form['acc_action'];
				$acc_upd['buyer_adjustment'] = $form['buyer_adjustment'];
				$acc_upd['final_amount'] = $form['ga'];
				$acc_upd['dn_issued'] = $form['dn_issued'];
				$acc_upd['dn_number'] = $form['dn_number'];
				$acc_upd['dn_amount'] = $form['dn_amount'];
				$acc_upd['dn_reason'] = $form['dn_reason'];
				$acc_upd['last_update'] = "CURRENT_TIMESTAMP";
				if($is_ibt){
					$acc_upd['is_ibt'] = $is_ibt;
					$acc_upd['from_branch_id'] = mi($from_bid);
				}

				if($confirm_type == 'approve'){
					//$acc_upd['approved'] = 1;
					$acc_upd['require_store_confirm'] = 0;
					$acc_upd['store_confirmed'] = 'CURRENT_TIMESTAMP';
					$acc_upd['store_confirmed_by'] = $sessioninfo['id'];
				}
				
				if($form['is_under_gst']){
					$acc_upd['dn_number'] = $form['dn_number'];
					$acc_upd['dn_amount'] = $form['dn_amount'];
					
					if($acc_upd['dn_number'] && $acc_upd['dn_amount']){
						$acc_upd['dn_issued'] = 1;
					}else{
						$acc_upd['dn_issued'] = 0;
					}
					
					$acc_upd['rounding_gst_amt'] = doubleval($form['gst_rounding_adj']);
					$acc_upd['final_gst_amt'] = doubleval($form['ttl_acc_gst']);
				}
				$acc_upd['tax'] = $form['tax'];

				$con->sql_query("update grn set ".mysql_update_by_field($acc_upd)." where id=".mi($grn_id)." and branch_id=".mi($branch_id)) or die(mysql_error());
			}
		}else{ //save new grn 
			$grn_ins = array();
			
			// Get Max ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($branch_id));
							
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$grn_ins['id'] = $new_id;
			$grn_ins['branch_id'] = mi($branch_id);
			$grn_ins['user_id'] = mi($sessioninfo['id']);
			$grn_ins['vendor_id'] = mi($form['vendor_id']);
			$grn_ins['department_id'] = mi($form['department_id']);
			$grn_ins['grr_id'] = mi($form['grr_id']);
			$grn_ins['grr_item_id'] = mi($form['grr_item_id']);
			$grn_ins['added'] = "CURRENT_TIMESTAMP";
			$grn_ins['total_selling'] = mf($form['total_selling']);
			$grn_ins['amount'] = mf($form['amount']);
			$grn_ins['div1_approved_by'] = $div1_approved_by;
			$grn_ins['div2_approved_by'] = $div2_approved_by;
			$grn_ins['div3_approved_by'] = $div3_approved_by;
			$grn_ins['final_amount'] = mf($form['amount']);
			$grn_ins['have_variance'] = mi($form['have_variance']);
			$grn_ins['foc_variance'] = mi($form['foc_variance']);
			$grn_ins['grn_tax'] = mf($form['grn_tax']);
			$grn_ins['non_sku_items'] = $nsi;
			$grn_ins['generate_gra'] = mf($form['generate_gra']);
			if($is_ibt){
				$grn_ins['is_ibt'] = $is_ibt;
				$grn_ins['from_branch_id'] = mi($from_bid);
			}
			$grn_ins['is_future'] = 1;
			$grn_ins['is_under_gst'] = mi($form['is_under_gst']);
			$grn_ins['grn_tax'] = mf($form['grn_tax']);
			
		    $con->sql_query("insert into grn ".mysql_insert_by_field($grn_ins));
		    $form['id']=$con->sql_nextid();
		    $url .= "&newly_added=1";
			//$con->sql_query("update grr set status=1 where branch_id=$branch_id and id=".mi($form['grr_id']));
		}
		
		// update grr items info
		$con->sql_query("update grr_items gi
						 left join grr on grr.id = gi.grr_id and grr.branch_id = gi.branch_id 
						 left join po on po.po_no = gi.doc_no and gi.type = 'PO'
						 set gi.grn_used=1, grr.status=1, po.delivered=1
						 where grr.branch_id=$branch_id and grr.id=".mi($form['grr_id']));

		if($form['action'] == "edit") $order_seq = "item_seq,"; // when is owner editing, only order by sequence
		
		$q1=$con->sql_query("select * from tmp_grn_items 
							 where grn_id=$grn_id 
							 and branch_id=$branch_id 
							 and user_id=$sessioninfo[id] 
							 and sku_item_id != 0
							 order by $order_seq id") or die(mysql_error());

		$first_id=0;
		while($r=$con->sql_fetchassoc($q1)){
			$ins = array();
			
			// Get Max ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($branch_id));
							
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$ins['id'] = $new_id;
			$ins['branch_id'] = $branch_id;
			$ins['grn_id'] = $form['id'];
			$ins['sku_item_id'] = $r['sku_item_id'];
			$ins['uom_id'] = $r['uom_id'];
			$ins['artno_mcode'] = $r['artno_mcode'];
			$ins['po_cost'] = $r['po_cost'];
			$ins['selling_price'] = $r['selling_price'];
			$ins['cost'] = $r['cost'];
			$ins['ctn'] = $r['ctn'];
			$ins['pcs'] = $r['pcs'];
			$ins['return_ctn'] = $r['return_ctn'];
			$ins['return_pcs'] = $r['return_pcs'];
			if(!$form['action']){
				if($r['acc_ctn'] != '' || $r['acc_pcs'] != ''){
					$ins['acc_ctn'] = doubleval($r['acc_ctn']);
					$ins['acc_pcs'] = doubleval($r['acc_pcs']);
				}
				
				if($r['acc_cost'] != '') $ins['acc_cost'] = $r['acc_cost'];
				
				$ins['rcc_status'] = $form['rcc_status'][$r['id']];
				
				if($form['is_under_gst']){
					if($r['acc_gst_id']) $ins['acc_gst_id'] = $r['acc_gst_id'];
					if($r['acc_gst_code']) $ins['acc_gst_code'] = $r['acc_gst_code'];
					if($r['acc_gst_rate']) $ins['acc_gst_rate'] = $r['acc_gst_rate'];
					
				}
				
				if($r['inv_qty'] != '') $ins['inv_qty'] = doubleval($r['inv_qty']);
				if($r['inv_cost'] != '') $ins['inv_cost'] = doubleval($r['inv_cost']);
			}
			$ins['po_qty'] = $r['po_qty'];
			$ins['po_item_id'] = $r['po_item_id'];
			$ins['weight'] = $r['weight'];
			$ins['selling_uom_id'] = $r['selling_uom_id'];
			$ins['original_cost'] = $r['original_cost'];
			$ins['item_group'] = $r['item_group'];
			$ins['item_check'] = $r['item_check'];
			$ins['from_isi'] = $r['from_isi'];
			$ins['reason'] = $r['reason'];
			$ins['bom_ref_num'] = $r['bom_ref_num'];
			$ins['bom_qty_ratio'] = $r['bom_qty_ratio'];
			$ins['available_po_qty'] = $r['available_po_qty'];
			
			if($form['is_under_gst']){
				$ins['gst_id'] = $r['gst_id'];
				$ins['gst_code'] = $r['gst_code'];
				$ins['gst_rate'] = $r['gst_rate'];
			}
			
			if($form['branch_is_under_gst']){
				$ins['selling_gst_id'] = $r['selling_gst_id'];
				$ins['selling_gst_code'] = $r['selling_gst_code'];
				$ins['selling_gst_rate'] = $r['selling_gst_rate'];
				$ins['gst_selling_price'] = $r['gst_selling_price'];
			}
			
			$ins['acc_foc_ctn'] = $r['acc_foc_ctn'];
			$ins['acc_foc_pcs'] = $r['acc_foc_pcs'];
			$ins['acc_foc_amt'] = $r['acc_foc_amt'];
			$ins['acc_disc'] = $r['acc_disc'];
			$ins['acc_disc_amt'] = $r['acc_disc_amt'];

		   	$con->sql_query("insert into grn_items ".mysql_insert_by_field($ins));
		    if($first_id==0) $first_id=$con->sql_nextid();
		}
		$con->sql_freeresult();

		if($first_id>0){
			if(!is_new_id($grn_id)){
				$con->sql_query("delete from grn_items where branch_id=$branch_id and grn_id=$grn_id and id<$first_id") or die(mysql_error());
			}
			$con->sql_query("delete from tmp_grn_items where grn_id=$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id]") or die(mysql_error());	
		}else{
			$con->sql_query("delete from grn_items where branch_id=$branch_id and grn_id=$grn_id") or die(mysql_error());
			$con->sql_query("delete from tmp_grn_items where grn_id=$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id]") or die(mysql_error());
		}

		update_total_selling($form['id'], $branch_id);
		$amount_info = update_total_amount($form['id'], $branch_id);
		$ttl_gross_amount = $amount_info['ttl_gross_amt']; // nett total
		if($form['is_under_gst']){
			$ttl_gst_amount = $amount_info['ttl_gst_amt']; // gst amount only
			$ttl_amount = $amount_info['ttl_amt']; // total amount incl. gst
		}else{
			$ttl_gst_amount = 0;
			$ttl_amount = 0;
		}
		
		// assign total amount for comparison purpose
		if(!$ttl_amount) $ttl_amount = $ttl_gross_amount;

		$last_approval = false;
		
		// load grn information 
		$con->sql_query("select * from grn where branch_id=".mi($branch_id)." and id=".mi($form['id']));
		$grn = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$err && ($confirm_type == "approve" || $confirm_type == "confirm")){
			if($form['action'] == "verify"){
				if(privilege('GRN_VAR_DIV')){
					$grn['div1_approved_by'] = $upd['div1_approved_by'] = $sessioninfo['id'];
				}
				if(privilege('GRN_SIV_DIV')){
					$grn['div2_approved_by'] = $upd['div2_approved_by'] = $sessioninfo['id'];
				}
				if(privilege('GRN_PC_DIV')){
					$grn['div3_approved_by'] = $upd['div3_approved_by'] = $sessioninfo['id'];
				}
			}elseif($form['action'] == "edit"){
				$upd['authorized'] = 1; // update authotized for owner confirmed the GRN
				
				// if found this GRN have been confirmed by divisions, send them a pm for the re-confirmation
				if(($grn['div1_approved_by'] || $grn['div2_approved_by'] || $grn['div3_approved_by'] || $grn['div4_approved_by']) && ($ttl_amount != $form['ttl_grr_amt'] || $ttl_gst_amount != $form['ttl_grr_gst_amt'])){
					// unset all the division id for user to confirm again
					if($form['type'] == "PO" || $form['is_ibt_do']){
						$grn['div1_approved_by'] = $upd['div1_approved_by'] = 0;
						if($have_diff_sp) $grn['div3_approved_by'] = $upd['div3_approved_by'] = 0;
					}
					if($nsi) $grn['div2_approved_by'] = $upd['div2_approved_by'] = 0;
					$grn['div4_approved_by'] = $upd['div4_approved_by'] = 0;
				}elseif($ttl_amount == $form['ttl_grr_amt'] && $ttl_gst_amount == $form['ttl_grr_gst_amt'] && $form['foc_variance'] == 0){
					if($form['type'] == "PO" || $form['is_ibt_do']){
						if(privilege('GRN_VAR_DIV')){
							$grn['div1_approved_by'] = $upd['div1_approved_by'] = $sessioninfo['id'];
						}
						if(privilege('GRN_PC_DIV')){
							$grn['div3_approved_by'] = $upd['div3_approved_by'] = $sessioninfo['id'];
						}
					}
					if(privilege('GRN_SIV_DIV')){
						$grn['div2_approved_by'] = $upd['div2_approved_by'] = $sessioninfo['id'];
					}
				}
			}elseif(!$form['action']){
				if(privilege('GRN_ACCV_DIV')){
					$grn['div4_approved_by'] = $upd['div4_approved_by'] = $sessioninfo['id'];
				}
			}
		}

		// auto confirm for account verification while found have no amount difference
		if($form['action'] && $confirm_type == "confirm" && $grn['div1_approved_by'] && $grn['div2_approved_by']){
			if(($ttl_amount == $form['ttl_grr_amt'] && $ttl_gst_amount == $form['ttl_grr_gst_amt'] && !$config['grn_future_require_acc_verify']) || ($form['ttl_grr_amt'] > 0 && $config['grn_future_skip_acc_verify'])){
				if($config['grn_future_skip_acc_verify']) $upd['buyer_adjustment'] = round($ttl_amount - $form['ttl_grr_amt'], 2);
				$grn['div4_approved_by'] = $upd['div4_approved_by'] = $sessioninfo['id'];
				$upd['by_account'] = $sessioninfo['id'];
				$upd['account_amount'] = $form['ttl_grr_amt'];
				$upd['account_update'] = 'CURRENT_TIMESTAMP';
			}
		}
		
		// if found all being approved, update status and ready send to approval flow
		if($grn['div1_approved_by'] && $grn['div2_approved_by'] && $grn['div3_approved_by'] && $grn['div4_approved_by']){
			$upd['status'] = 1;

			$params = array();
			if($confirm_type == "approve"){
				if($upd['status']) $upd['approved'] = 1;
			}
			$params['type'] = 'GOODS_RECEIVING_NOTE';
			$params['user_id'] = $sessioninfo['id'];
			$params['reftable'] = 'grn';
			$params['dept_id'] = $form['department_id']; 
			//$params['skip_approve'] = true;
		
			if($config['consignment_modules'] && $config['single_server_mode']){ // Cutemaree using it
				//$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE',1, 'grn','',false,$branch_id);
				$params['branch_id'] = 1;
				$params['save_as_branch_id'] = $branch_id;
			}else{
				//$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE', $branch_id, 'grn', " sku_category_id=$form[department_id] ", false);
				$params['branch_id'] = $branch_id;        
			}
			if($grn['approval_history_id']) $params['curr_flow_id'] = $grn['approval_history_id']; // use back the same id if already have
			//if($form['approval2_history_id']) $params['curr_flow_id'] = $form['approval2_history_id']; // use back the same id if already have

			$astat = check_and_create_approval2($params, $con);
			
			if(!$astat)
				$err['top'][]=$LANG['GRN_NO_APPROVAL_FLOW'];
			else{
				//if($confirm_type == "approve") $upd['approval_history_id']=$astat[0];
				if($confirm_type == "confirm"){
					$form['approval_history_id'] = $upd['approval_history_id']=$astat[0];
				} 
				if($astat[1]=='|') $last_approval=true;
			}
		}
	}

	if(!$err){
		/*if ($confirm_type == 'approve'){ // temporary not using this function...
			$con->sql_query("update grn set ".mysql_update_by_field($upd)." where branch_id=".mi($branch_id)." and id=".mi($form['id']));
		    log_br($sessioninfo['id'], 'GRN', $form['id'], "Goods Receiving Note confirmed by $sessioninfo[u] for division $form[div] (ID#$form[id])");

			if ($last_approval){
		        $con->sql_query("update grn set approved=1 where branch_id=".mi($branch_id)." and id=".mi($form['id']));
		        update_sku_item_cost($form['id'], $branch_id);
			}
			else{
				if($upd['status']) $con->sql_query("update branch_approval_history set ref_id=".mi($form['id'])." where id=".mi($form['approval_history_id'])." and branch_id=".mi($branch_id));
			}

			if($upd['status']){
				// send pm
				$recipients=$astat[2];
				$recipients=str_replace("|$sessioninfo[id]|", "|", $recipients);
		       	$to=preg_split("/\|/", $recipients);
		       	
				send_pm($to, sprintf("GRN Send to Approval (GRN%05d)",$form['id']), "/goods_receiving_note.php?a=view&id=$form[id]&branch_id=$branch_id");
			}
		}else*/
		if($confirm_type == 'confirm'){ // do this approval flow when found no issue for the GRN
			/*if($upd['approval_history_id'] && !$last_approval){
				$con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($upd['approval_history_id'])." and branch_id = ".mi($branch_id));
				$con->sql_query("select id,approvals from branch_approval_history where id = ".mi($upd['approval_history_id'])." and branch_id = ".mi($branch_id));
				$astat = $con->sql_fetchrow();
				$con->sql_freeresult();

				if($astat){
					$params = array();
					$params['user_id'] = $sessioninfo['id'];
					$params['id'] = $astat['id'];
					$params['branch_id'] = $branch_id;
					$last_approval = check_is_last_approval_by_id($params, $con);
				}
				
				if($form['action'] && $last_approval){
					$upd['by_account'] = $sessioninfo['id'];
					$upd['account_amount'] = $form['ttl_grr_amt'];
				}
			}*/
			
			if($grn['div1_approved_by'] && $grn['div2_approved_by'] && $grn['div3_approved_by'] && !$last_approval) $upd['account_amount'] = $form['ttl_grr_amt'];
			if($upd['div4_approved_by']) $upd['final_amount'] = $ttl_amount;

			$con->sql_query("update grn set ".mysql_update_by_field($upd)." where branch_id=".mi($branch_id)." and id=".mi($form['id']));			
			log_br($sessioninfo['id'], 'GRN', $form['id'], "Goods Receiving Note confirmed by $sessioninfo[u] for (ID#$form[id])");
			
			if ($last_approval){
		        $con->sql_query("update grn set approved=1,by_account=".mi($sessioninfo['id']).",acc_adjustment=".mf($ttl_amount)." where branch_id=".mi($branch_id)." and id=".mi($form['id']));
				$con->sql_query("update branch_approval_history set ref_id=".mi($form['id']).", approvals='' where id=".mi($upd['approval_history_id'])." and branch_id=".mi($branch_id));

				$form['approval_history_id']=$astat[0];

				// insert an item into approval history
				$bahi_ins = array();
				$bahi_ins['approval_history_id'] = $upd['approval_history_id'];
				$bahi_ins['branch_id'] = $branch_id;
				$bahi_ins['user_id'] = $sessioninfo['id'];
				$bahi_ins['status'] = 1;
				$bahi_ins['log'] = 'Approve';
				
				$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($bahi_ins));
				
				update_sku_item_cost($form['id'], $branch_id);
				update_sku_vendor_history($form['id'], $branch_id);
				
				// search and handle items that currently having to return qty
				items_return_handler($form['id'], $branch_id);
				update_total_amount($form['id'], $branch_id);
		
				//update PO receiving count
				if ($form['type']=='PO' && $form['doc_no']){
					// update sku monitoring group items
					$exp_po_no = explode(",", $form['doc_no']);
					foreach($exp_po_no as $r=>$po_no){
						$p_no = trim($po_no);
						update_po_receiving_count($p_no);
						if($config['po_enable_ibt']) update_sku_monitoring_group_items_changed($p_no, $branch_id, $form['id']);
					}
				}
				
				// if found got turn on skip auto generate grn, need to check do transfer whether got s/n and transfer it to current branch
				/*if($config['single_server_mode'] && $config['enable_sn_bn']){
					$prms = array();
					$prms['grn_id'] = $form['id'];
					$prms['branch_id'] = $branch_id;
					$prms['process_type'] = "transfer";
					grn_sn_handler($prms);
				}*/
			}else{
				if($upd['status']){ // is send to approval flow
					$con->sql_query("update branch_approval_history set ref_id=".mi($form['id'])." where id=".mi($form['approval_history_id'])." and branch_id=".mi($branch_id));
				
					// send pm
					$to = array();
					$recipients=$astat[2];
					$recipients=str_replace("|$sessioninfo[id]|", "|", $recipients);
					//$to=preg_split("/\|/", $recipients);
				}
			}

			if($form['approval_history_id']){
				$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'grn');
			}

			if($form['action'] == "edit" && ($form['type'] == "PO" || $form['is_ibt_do'])){
				$grp_doc_no = str_replace(", ", "', '", $form['doc_no']);
				$con->sql_query("select po.user_id, bah.approval_settings
				from po 
				left join branch_approval_history bah on bah.branch_id=po.branch_id and bah.id=po.approval_history_id
				where po_no in ('".$grp_doc_no."')");
			
				while($r = $con->sql_fetchassoc()){					
					$r['approval_settings'] = unserialize($r['approval_settings']);
								
					$tmp = array();
					$tmp['user_id'] = $r['user_id'];
					$tmp['approval_settings'] = $r['approval_settings']['owner'];
					$tmp['type'] = 'owner';
					$to[$t['user_id']] = $tmp;
				}
				$con->sql_freeresult();
			}
		}else{
			log_br($sessioninfo['id'], 'GRN', $form['id'], "GRN saved by $sessioninfo[u] for (ID#$form[id])");
		}

		if($form['grn_rpt_print'] && $form['action']){
			$url .= "&grn_rpt_print=".$form['grn_rpt_print'];
		}
		
		// status for JS to alert
		$msg_type = 0;
		$group_msg_type = array();
		if($confirm_type){
			$grn = array();
			$con->sql_query("select grn.*,branch.report_prefix  from grn left join branch on grn.branch_id=branch.id where grn.branch_id=".mi($branch_id)." and grn.id=".mi($form['id']));
			$grn = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$report_prefix = $grn['report_prefix'];
			
			if($grn['div1_approved_by'] && $grn['div2_approved_by'] && $grn['div3_approved_by'] && $grn['div4_approved_by']){
				if($last_approval) $msg_type = "fully approved"; // fully approved
				else $msg_type = "approval"; // sent to approval
			}elseif($grn['div1_approved_by'] && $grn['div2_approved_by'] && $grn['div3_approved_by']){
				if($form['ttl_grr_amt'] != 0 || ($ttl_amount == 0 && $form['ttl_grr_amt'] == 0)) $msg_type = "account verification"; // sent to Account Verification
				else $msg_type = "document pending"; // sent to document pending
			}else{
				if(!$grn['div1_approved_by']) $group_msg_type[] = "SKU Manage"; // sent to SKU Manage
				if(!$grn['div2_approved_by']) $group_msg_type[] = "sku apply"; // sent to SKU Apply
				if(!$grn['div3_approved_by']) $group_msg_type[] = "change price"; // sent to Change Price
				if(count($group_msg_type) > 0) $msg_type = join(", ", $group_msg_type);
			}
			if($msg_type) $url .= "&msg_type=".urlencode($msg_type);
		}
		
		if($to){
			send_pm2($to, sprintf("GRN Confirmed ".$report_prefix."%05d)",$form['id']), "/goods_receiving_note.php?a=view&id=".mi($form['id'])."&branch_id=".mi($branch_id), array('module_name'=>'grn'));
		}
		
		if(!$is_generate_dn){
			header("Location: /goods_receiving_note.php?t=$form[a]".$url."&id=".mi($form['id'])."&branch_id=".mi($branch_id)."&la=".mi($last_approval)."&action=".$form['action']."&report_prefix=".$report_prefix);
		}else return true;
	}else{
		$smarty->assign("errm", $err);
		$_REQUEST['err'] = $err['top'];
		$_REQUEST['a'] = "open";
		grn_open($form['id'], $branch_id);
	}
}

function grr_save($grr_id, $branch_id){
	global $smarty, $con, $LANG, $sessioninfo, $config, $appCore;
	$form = $_REQUEST;
	$grn_id = $form['id'];
	//$grn_amount = $form['grn_amount'];
	$err = grr_validate_data($form);
	
	// recalculate grn amount to get the correct figures
	$amount_info = update_total_amount($grn_id, $branch_id);
	$grn_gross_amount = $amount_info['ttl_gross_amt']; // nett total
	if($form['is_under_gst']){
		$grn_gst_amount = $amount_info['ttl_gst_amt']; // gst amount only
		$grn_amount = $amount_info['ttl_amt']; // total amount incl. gst
	}else{
		$grn_gst_amount = 0;
		$grn_amount = 0;
	}
	
	// assign total amount for comparison purpose
	if(!$grn_amount) $grn_amount = $grn_gross_amount;

	if($grn_amount == $form['grr_amount'] && $grn_gst_amount == $form['grr_gst_amount'] && !$config['grn_future_require_acc_verify'] || ($form['grr_amount'] > 0 && $config['grn_future_skip_acc_verify'])){
		$last_approval = false;
		$params = array();
		$params['type'] = 'GOODS_RECEIVING_NOTE';
		$params['user_id'] = $sessioninfo['id'];
		$params['reftable'] = 'grn';
		$params['ref_id'] = $grn_id;
		$params['dept_id'] = $form['department_id']; 
		//$params['skip_approve'] = true;

		if($config['consignment_modules'] && $config['single_server_mode']){ // Cutemaree using it
			$params['branch_id'] = 1;
			$params['save_as_branch_id'] = $branch_id;
		}else{
			$params['branch_id'] = $branch_id;        
		}
		
		// select from GRN again in case customer open multiple tab to confirm
		$q1 = $con->sql_query("select * from grn where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
		$grn = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($grn['approval_history_id']) $params['curr_flow_id'] = $grn['approval_history_id']; // use back the same id if already have

		$astat = check_and_create_approval2($params, $con);

		if(!$astat)
			$err['top'][]=$LANG['GRN_NO_APPROVAL_FLOW'];
		else{
			$form['approval_history_id']=$astat[0];
			if($astat[1]=='|') $last_approval=true;
			/*else{
				$con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($form['approval_history_id'])." and branch_id = ".mi($branch_id));
				$con->sql_query("select id,approvals from branch_approval_history where id = ".mi($form['approval_history_id'])." and branch_id = ".mi($branch_id));
				$astat = $con->sql_fetchrow();

				if($astat){
					$params = array();
					$params['user_id'] = $sessioninfo['id'];
					$params['id'] = $astat['id'];
					$params['branch_id'] = $branch_id;
					$last_approval = check_is_last_approval_by_id($params, $con);
				}
			}*/
		}
	}
	
	if (!$err){
		// update grr
		$con->sql_query("update grr set ".mysql_update_by_field($form, array("grr_ctn", "grr_pcs", "grr_amount", "grr_gst_amount", "grr_tax"))." where id = ".mi($grr_id)." and branch_id = ".mi($branch_id));
		
		// update grn
		$grn_upd = array();
		if($config['grn_future_skip_acc_verify']){
			$grn_upd['buyer_adjustment'] = round($grn_amount - $form['grr_amount'], 2);
			$grn_upd['div4_approved_by'] = $sessioninfo['id'];
			$grn_upd['by_account'] = $sessioninfo['id'];
			$grn_upd['account_update'] = 'CURRENT_TIMESTAMP';
			$grn_upd['approval_history_id'] = $form['approval_history_id'];
			$grn_upd['status'] = 1;
		}

		if ($last_approval){
			$grn_upd['status'] = 1;
			$grn_upd['approved'] = 1;
			$grn_upd['approval_history_id'] = $form['approval_history_id'];
			$grn_upd['by_account'] = $sessioninfo['id'];
			$grn_upd['div4_approved_by'] = $sessioninfo['id'];
			$con->sql_query("update branch_approval_history set ref_id=".mi($grn_id).", approvals='' where id=".mi($form['approval_history_id'])." and branch_id=".mi($branch_id));
			update_sku_item_cost($grn_id, $branch_id);
			
			$grn_upd['final_amount'] = $grn_amount;
		}
		$grn_upd['account_amount'] = $form['grr_amount'];
		$con->sql_query("update grn set ".mysql_update_by_field($grn_upd)." where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
		
		$q3=$con->sql_query("Select report_prefix from branch where id=".mi($branch_id));
		$r = $con->sql_fetchassoc($q3);
		$report_prefix = $r['report_prefix'];
		$con->sql_freeresult($q3);
		// save items
		foreach ($form['grr_item_id'] as $n=>$dummy){
			$aa = array();
			$gi_id = intval($form['grr_item_id'][$n]);
			$aa['po_id'] = $form['po_id'][$n];
			$aa['doc_no'] = $form['doc_no'][$n];
			$aa['doc_date'] = $form['doc_date'][$n];
			$aa['type'] = $form['type'][$n];
			$aa['ctn'] = $form['ctn'][$n];
			$aa['pcs'] = $form['pcs'][$n];
			$aa['amount'] = $form['amount'][$n];
			$aa['gst_amount'] = $form['gst_amount'][$n];
			if($form['is_under_gst'] && $aa['type'] != "PO"){
				$aa['gst_id'] = $form['gst_id'][$n];
				$aa['gst_code'] = $form['gst_code'][$n];
				$aa['gst_rate'] = $form['gst_rate'][$n];
			}else{
				$aa['gst_id'] = "";
				$aa['gst_code'] = "";
				$aa['gst_rate'] = "";
			}
			$aa['tax'] = $form['tax'][$n];
			$aa['remark'] = $form['remark'][$n];
			if ($form['doc_no'][$n] == ''){
				if ($gi_id>0) $con->sql_query("delete from grr_items where id = ".mi($gi_id)." and branch_id = ".mi($form['branch_id']));
			}else{
				if($gi_id==0){
					// Get Max ID
					unset($new_id);
					$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($form['branch_id']));
									
					if(!$new_id) die("Unable to generate new ID from appCore!");
					
					$aa['id'] = $new_id;
					$aa['grr_id'] = $grr_id;
					$aa['branch_id'] = $form['branch_id'];
					$aa['grn_used'] = 1;
					$con->sql_query("insert into grr_items ".mysql_insert_by_field($aa));
				}else{
					$con->sql_query("update grr_items set ".mysql_update_by_field($aa)." where id = ".mi($gi_id)." and branch_id = ".mi($form['branch_id']));
				}
			}

			if ($form['doc_no'][$n] != $form['curr_po_no'][$n] && $form['curr_po_no'][$n] != "")
			{
				$con->sql_query("update po set delivered = 0 where po_no = ".ms($form['curr_po_no'][$n]));
			}

			if ($form['po_id'][$n]>0){
				$con->sql_query("update po set delivered = 1 where po_no = ".ms($form['doc_no'][$n]));

				// PM to PO owner and FYI if
				if ($con->sql_affectedrows()>0)
				{
					$to = array();
							
					$con->sql_query("select po.user_id, bah.approval_settings
					from po 
					left join branch_approval_history bah on bah.branch_id=po.branch_id and bah.id=po.approval_history_id
					where po.po_no = ".ms($form['doc_no'][$n]));
					$t = $con->sql_fetchrow();
					$con->sql_freeresult();
					
					if($t){
						$t['approval_settings'] = unserialize($t['approval_settings']);
						
						$tmp = array();
						$tmp['user_id'] = $t['user_id'];
						$tmp['approval_settings'] = $t['approval_settings']['owner'];
						$tmp['type'] = 'owner';
						$to[$t['user_id']] = $tmp;
					}
					send_pm2($to, "PO Received (".$form['doc_no'][$n].") in GRR (Branch: ".BRANCH_CODE.", ".$report_prefix.sprintf("%05d",$grr_id).")", "/po.php?a=view&id={$form[po_id][$n]}&branch_id={$form[po_branch_id][$n]}");
				}
			}
		}
		
		
		$url = "";
		$msg_type = "";
		if($form['grr_amount']>0){
			if($last_approval) $msg_type = $report_prefix.sprintf("%05d",$grn_id)." fully approved";
			elseif($grn_upd['div4_approved_by'] && $config['grn_future_skip_acc_verify']) $msg_type = "sent to approval";
			else $msg_type = "sent to account verification";
		}else $msg_type = "remain as document pending";
		$url = "&msg_type=".urlencode($msg_type);
		log_br($sessioninfo['id'], 'GRR', $grr_id, "Saved: ".$report_prefix.sprintf("%05d",$grr_id));
		header("Location: /goods_receiving_note.php?t=$_REQUEST[a]&id=".$grr_id.$url."&report_prefix=".$report_prefix);
	}else{
		$header_form=load_grn_header($grn_id, $branch_id);
		if($header_form) $form = array_merge($form, $header_form);
		$smarty->assign("errm", $err);
		$smarty->assign("form", $form);
		$smarty->display("goods_receiving_note2.new.tpl");
	}
}

function grn_cancel($grn_id, $branch_id){
	global $con, $sessioninfo;
    
    //validate grr
    $q2 = $con->sql_query("select grr.id, grr.rcv_date ,branch.report_prefix as report_prefix
    from grr
	left join branch on grr.branch_id = branch.id
    left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id  
    where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($branch_id));
	$grr_info = $con->sql_fetchassoc($q2);
    $con->sql_freeresult($q2);
    
	process_reset_grr_grn($branch_id, $grr_info['id'], $grn_id, "GRN");
	header("Location: /goods_receiving_note.php?t=cancel&id=$grn_id&grr_id=".$grr_info['id']."&report_prefix=".$grr_info['report_prefix']);
	exit;
    /*$invalid = reset_grr($grr_info['id'], $branch_id,'GRN', $grr_info['rcv_date'], $grn_id);
    
    //update grn if grr is valid
    if (!$invalid){
        $con->sql_query("update grn 
        left join grr_items on grn.branch_id=grr_items.branch_id and grn.grr_item_id=grr_items.id
        set grn.active=0, grr_items.grn_used=0
        where grn.id = ".mi($grn_id)." and grn.branch_id = ".mi($branch_id)) or die(mysql_error());
        
        // get grn
        $q1 = $con->sql_query("select * from grn where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
        $grn_info = $con->sql_fetchassoc($q1);
        $con->sql_freeresult($q1);
        
        // check if it is having Batch No Setup
        if($grn_info['batch_status']){
            // update all batch no become "C-xxx"
            $con->sql_query("update sku_batch_items set batch_no = concat('C-', batch_no) where grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
            
            // update adjustment become inactive
            $upd = array();
            $upd['status'] = 5;
            $upd['approved'] = $upd['active'] = 0;
            $upd['cancelled'] = "CURRENT_TIMESTAMP";
            $upd['cancelled_by'] = $sessioninfo['id'];
            $upd['reason'] = "Canceled by GRN";
            if($grn_info['is_under_gst']){
                $upd['dn_number'] = "";
                $upd['dn_amount'] = 0;
                $upd['dn_issued'] = 0;
            }
            $con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
        }
		 // set dnote to inactive
		if($grn_info['is_under_gst']){
			$con->sql_query("update dnote set active=0 where ref_table = 'grn' and ref_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
		}
        
        log_br($sessioninfo['id'], 'GRN', $grn_id, "Goods Receiving Note canceled by $sessioninfo[u] for (ID#$grn_id)");
        
        header("Location: /goods_receiving_note.php?t=cancel&id=$grn_id&grr_id=".$grr_info['id']);
    }*/
}

function grn_change_owner($grn_id, $branch_id){
	global $con, $sessioninfo, $LANG;
	$form=$_REQUEST;
	
	$filter = array();
	$filter[] = "up.branch_id=".mi($sessioninfo['branch_id'])." and up.privilege_code in ('GRN')";
	$filter[] = "user.u=".ms($form['new_owner'])." and user.active=1";
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select user.id from user 
			left join user_privilege up on user.id=up.user_id 
			$filter
			limit 1";
	//die($sql);
	$q1=$con->sql_query($sql);
	$r1=$con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// user not found or no privilege
	if(!$r1)	die(printf($LANG['GRN_CHOWN_FAILED'], $form['new_owner']));
	
	$con->sql_query("update grn set user_id=".mi($r1['id'])." where id=".mi($grn_id)." and branch_id=".mi($branch_id));
		
    if($con->sql_affectedrows()>0){
		printf($LANG['GRN_CHOWN_SUCCESS'], $form['new_owner']);
	}else{
		die("Update Failed. The user already is the owner or GRN not exists.");
	}
}

function do_reject($grn_id, $branch_id){
	global $smarty, $con, $sessioninfo, $config, $appCore, $LANG;
	$form = $_REQUEST;
	$upd = $bahi_ins = array();

	$params = array();
	$params['type'] = 'GOODS_RECEIVING_NOTE';
	$params['user_id'] = $sessioninfo['id'];
	$params['reftable'] = 'grn';
	$params['ref_id'] = $grn_id;
	$params['dept_id'] = $form['department_id']; 
	$params['skip_approve'] = true;
	$params['skip_send_email'] = true;

	if($config['consignment_modules'] && $config['single_server_mode']){ // Cutemaree using it
		//$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE',1, 'grn','',false,$branch_id);
		$params['branch_id'] = 1;
		$params['save_as_branch_id'] = $branch_id;
	}else{
		//$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE', $branch_id, 'grn', " sku_category_id=$form[department_id] ", false);
		$params['branch_id'] = $branch_id;        
	}
	

	// select from GRN again in case customer open multiple tab to confirm
	$q1 = $con->sql_query("select *,
						   if(user_id != div1_approved_by, div1_approved_by, 0) as div1_approved_by,
						   if(user_id != div2_approved_by, div2_approved_by, 0) as div2_approved_by,
						   if(user_id != div3_approved_by, div3_approved_by, 0) as div3_approved_by,
						   if(user_id != div4_approved_by, div4_approved_by, 0) as div4_approved_by
						   from grn where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
	$grn = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	$q2 = $con->sql_query("select report_prefix from branch where id=".mi($branch_id));
	$branch = $con->sql_fetchassoc($q2);
	$report_prefix = $branch['report_prefix'];
	$con->sql_freeresult($q2);
	
	if($grn['approval_history_id']) $params['curr_flow_id'] = $grn['approval_history_id']; // use back the same id if already have
	//if($form['approval2_history_id']) $params['curr_flow_id'] = $form['approval2_history_id']; // use back the same id if already have
	
	$astat = check_and_create_approval2($params, $con);
	
	$form['approval_history_id']=$astat[0];

	$bahi_ins['approval_history_id'] = $form['approval_history_id'];
	$bahi_ins['branch_id'] = $branch_id;
	$bahi_ins['user_id'] = $sessioninfo['id'];
	$bahi_ins['status'] = 2;
	$bahi_ins['log'] = $form['reject_reason'];
	
	$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($bahi_ins));

	$msg_type = "";
	/*if($form['action'] == "verify"){ // reject from division 1, only reject from owner that confirm this GRN
		$upd['authorized'] = 0;
		$msg_type = "saved GRN";
	}elseif(!$form['action']){ // reject from account verification, reset division 1 and 2.
		if($form['type'] == "PO" || $form['is_ibt_do']){
			$upd['div1_approved_by'] = 0;
			$msg_type = "po variance";
		}
		$upd['div2_approved_by'] = 0;
		if($msg_type) $msg_type .= ", ";
		$msg_type .= "sku apply";
		//$upd['div4_approved_by'] = 0;
	}*/
	
	$upd['div1_approved_by'] = 0;
	$upd['div2_approved_by'] = 0;
	$upd['div3_approved_by'] = 0;
	$upd['authorized'] = 0;
	$msg_type = "saved GRN";
	$upd['approval_history_id'] = $form['approval_history_id'];
	$upd['last_update'] = "CURRENT_TIMESTAMP";

	$con->sql_query("update grn set ".mysql_update_by_field($upd)." where id = ".mi($grn_id)." and branch_id = ".mi($branch_id));

	if($grn){
		$to = array();
		$to[$grn['user_id']] = $grn['user_id'];

		if($form['type'] == "PO" || $form['is_ibt_do']){
			$to[$grn['div1_approved_by']] = $grn['div1_approved_by'];
			if($form['action']) $to[$grn['div3_approved_by']] = $grn['div3_approved_by'];
		}

		$to[$grn['div2_approved_by']] = $grn['div2_approved_by'];
		if($form['action']) $to[$grn['div4_approved_by']] = $grn['div4_approved_by'];
	}

	// here need to send the notification to all privilege users
	if($to) send_pm($to, sprintf("GRN Rejected ".$report_prefix."%05d",$grn_id), "/goods_receiving_note.php?a=view&id=$grn_id&branch_id=$branch_id");

	// capture the log where this GRN being rejected
	log_br($sessioninfo['id'], 'GRN', $grn_id, "Goods Receiving Note rejected by $sessioninfo[u] for (ID#$grn_id)");

	$url = "";
	if($msg_type) $url = "&msg_type=".urlencode($msg_type);
	
	header("Location: /goods_receiving_note.php?id=".$grn_id."&t=".$form['a'].$url."&report_prefix=".$report_prefix);
}

function grn_ajax_add_item($grn_id, $branch_id){
	global $smarty, $LANG;

	$form=$_REQUEST;
	$grr['type']=$form['type'];
	$smarty->assign("grr", $grr);			
	save_grn_items();

    $grn_barcode = trim($_REQUEST['grn_barcode']);
/*
	if($grn_barcode){    // add item by using scan barcode
	    if (preg_match("/^00/", $grn_barcode))	// form ARMS' GRN barcoder
		{
			$sku_item_id=mi(substr($grn_barcode,0,8));
			$pcs=mi(substr($grn_barcode,8,4));
		}
		else	// from ATP GRN Barcode, try to search the link-code
		{
	        $linkcode=substr($grn_barcode,0,7);
			$pcs=mi(substr($grn_barcode,7,5));
			$con->sql_query("select id from sku_items where link_code = ".ms($linkcode));
			$sku_item_id=$con->sql_fetchfield(0);
			if (!$sku_item_id) fail(sprintf($LANG['DO_INVALID_ITEM'],$linkcode));
		}
	}else{
	    $sku_item_id = intval($form['sku_item_id']);
	}
*/
	$sku_info = get_grn_barcode_info($grn_barcode,true);
	$sku_item_id = $sku_info['sku_item_id'];
	$pcs = $sku_info['qty_pcs'];
	
	$r = get_items_details($sku_item_id, '');
	$r['pcs'] = $pcs;
	add_temp_item($r, $grn_id);			  			
    $smarty->assign("item", $r);
	$arr = array();
	$rowdata = $smarty->fetch("goods_receiving_note2.new.list.tpl");		

	$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);
	$smarty->assign("form", $form);
		    	
	header('Content-Type: text/xml');
    print array_to_xml($arr);
	exit;
}

function grn_ajax_add_vendor_sku($grn_id, $branch_id){
	global $smarty, $con;
	
	$form=$_REQUEST;
	/*
    $vid=intval($form['vendor_id']);
	$bid=$branch_id;
	*/
	save_grn_items();
	
	if(!$form['sel']){
		print "<script>cancel_vendor_sku();</script>";
		exit;
	}
	foreach($form['sel'] as $sku_id=>$dummy){
		$r=get_items_details(intval($sku_id), '');
		$r['item_group'] = check_sku_item_type($grn_id, $sku_id, $branch_id, $form['type']);
		
		// if it is matched with PO items, will not do insertion.
		$con->sql_query("select * from tmp_grn_items tgi 
						 left join sku_items si on si.id = tgi.sku_item_id
						 where tgi.grn_id = ".ms($grn_id)." and tgi.branch_id = ".ms($branch_id)." and tgi.sku_item_id = ".ms($sku_id));
		$grn_items = $con->sql_fetchrow();

		if(!$grn_items){ // if no record, just add to temp table...
			add_temp_item($r, $grn_id);
		}else{ // if were found records, check item group
			if($grn_items['item_group'] == 0){ // if found it is from undelivered PO, update it become matched with PO
				$con->sql_query("update tmp_grn_items tgi set tgi.item_group = 1 where tgi.grn_id = ".ms($grn_id)." and tgi.branch_id = ".ms($branch_id)." and tgi.sku_item_id = ".ms($sku_id));  
			}else{ // others just leave it as existed item.
				$count++;
				$err_sku_item_code[] = "$count. ".$grn_items['sku_item_code'];
			}
		}
	    $smarty->assign("item", $r);
	}
	
	if($err_sku_item_code){
		$err_sic = join("\\n", $err_sku_item_code);
		print "<script>alert(\"The following SKU Item Code(s) were not successfully added to list due to the duplication: \\n\\n$err_sic\")</script>";
	}
	
	print "<script>refresh_tables();</script>";
}

function save_grn_items(){
	global $con, $branch_id, $sessioninfo, $appCore;
	
	$form=$_REQUEST;

	if($form['action'] == "edit"){
		$tbl_start = 0;
		$tbl_end = 4;
	}elseif($form['action'] == "verify"){
		$tbl_start = 1;
		$tbl_end = 4;
	}

	// update for division 1~3
	if($form['action']){
		for($doc_type=$tbl_start; $doc_type<=$tbl_end; $doc_type++){
			if($form[$doc_type.'_uom_id']){
				foreach($form[$doc_type.'_uom_id'] as $k=>$v){
					$update = array();
					//$update['selling_price'] = doubleval($form[$doc_type.'_selling_price'][$k]);
					$update['user_id'] = $sessioninfo['id'];
					if(!$form[$doc_type.'_cost'][$k]) $form[$doc_type.'_cost'][$k] = $form[$doc_type.'_po_cost'][$k];
					$update['cost'] = doubleval($form[$doc_type.'_cost'][$k]);
					$update['uom_id'] = mi($form[$doc_type.'_uom_id'][$k]);
					if($form[$doc_type.'_uom_fraction'][$k] > 1 && floor($form[$doc_type.'_pcs'][$k] / $form[$doc_type.'_uom_fraction'][$k]) > 0){
						$update['ctn'] = floor($form[$doc_type.'_pcs'][$k] / $form[$doc_type.'_uom_fraction'][$k]);
						$update['pcs'] = doubleval($form[$doc_type.'_pcs'][$k] - ($update['ctn'] * $form[$doc_type.'_uom_fraction'][$k]));
					}else{
						$update['ctn'] = doubleval($form[$doc_type.'_ctn'][$k]);
						$update['pcs'] = doubleval($form[$doc_type.'_pcs'][$k]);
					}
					$update['return_ctn'] = doubleval($form[$doc_type.'_return_ctn'][$k]);
					$update['return_pcs'] = doubleval($form[$doc_type.'_return_pcs'][$k]);
					$update['weight'] = doubleval($form[$doc_type.'_weight'][$k]);
					$update['po_item_id'] = mi($form[$doc_type.'_po_item_id'][$k]);
					$update['original_cost'] = doubleval($form[$doc_type.'_original_cost'][$k]);
					$update['item_group'] = mi($form[$doc_type.'_item_group'][$k]);
					$update['item_check'] = mi($form[$doc_type.'_item_return'][$k]);
					$update['from_isi'] = mi($form[$doc_type.'_from_isi'][$k]);
					if(!$form[$doc_type.'_selling_uom_id'][$k]) $update['selling_uom_id'] = 1;
					else $update['selling_uom_id'] = $form[$doc_type.'_selling_uom_id'][$k];
				    $update['reason'] = $form['5_reason'][$k];
					$update['item_seq'] = doubleval($form[$doc_type.'_item_seq'][$k]);
					$update['bom_ref_num'] = doubleval($form[$doc_type.'_bom_ref_num'][$k]);
					$update['bom_qty_ratio'] = doubleval($form[$doc_type.'_bom_qty_ratio'][$k]);
					$update['available_po_qty'] = doubleval($form[$doc_type.'_available_po_qty'][$k]);
					if($form['is_under_gst']){
						$update['gst_id'] = mi($form[$doc_type.'_gst_id'][$k]);
						$update['gst_code'] = $form[$doc_type.'_gst_code'][$k];
						$update['gst_rate'] = mf($form[$doc_type.'_gst_rate'][$k]);
					}
					
					if($form['branch_is_under_gst']){
						$update['selling_gst_id'] = mi($form[$doc_type.'_selling_gst_id'][$k]);
						$update['selling_gst_code'] = $form[$doc_type.'_selling_gst_code'][$k];
						$update['selling_gst_rate'] = mf($form[$doc_type.'_selling_gst_rate'][$k]);
						$update['gst_selling_price'] = mf($form[$doc_type.'_gst_selling_price'][$k]);
					}

					$con->sql_query("update tmp_grn_items set ".mysql_update_by_field($update)." where id=".mi($k)." and branch_id=".mi($branch_id));
					
					if($update['from_isi'] && $con->sql_numrows() == 0){
						// Get Max ID
						unset($new_id);
						$new_id = $appCore->generateNewID("tmp_grn_items", "branch_id = ".mi($branch_id));
										
						if(!$new_id) die("Unable to generate new ID from appCore!");
						
						$update['id'] = $new_id;
						$update['branch_id'] = $branch_id;
						$update['grn_id'] = $_REQUEST['id'];
						$con->sql_query("insert into tmp_grn_items ".mysql_insert_by_field($update));
						
					}
				}
			}
		}
	}else{ // update for account verification
	    foreach($form['pcs'] as $k=>$dummy){
			$upd = array();
	    	$upd['uom_id'] = mi($form['uom_id'][$k]);
	        $upd['acc_pcs'] = trim($form['acc_pcs'][$k]);
	        $upd['acc_ctn'] = trim($form['acc_ctn'][$k]);
			$upd['acc_cost'] = $form['acc_cost'][$k];
			$upd['cost'] = $form['cost'][$k];
			$upd['inv_qty'] = $form['inv_qty'][$k];
			$upd['inv_cost'] = $form['inv_cost'][$k];
	        
	        // if have account correction on qty, set '0' to the unfilled box 
			if ($upd['acc_ctn']!=='' || $upd['acc_pcs']!==''){
				$upd['acc_ctn'] = doubleval($upd['acc_ctn']);
				$upd['acc_pcs'] = doubleval($upd['acc_pcs']);
			}
			
			if($form['is_under_gst']){
				if($form['gst_id'][$k] != $form['acc_gst_id'][$k]){
					$upd['acc_gst_id'] = mi($form['acc_gst_id'][$k]);
					$upd['acc_gst_code'] = $form['acc_gst_code'][$k];
					$upd['acc_gst_rate'] = $form['acc_gst_rate'][$k];
				}
			}

			// if have account correction on inv qty
			if ($upd['inv_qty']!=='') $upd['inv_qty'] = doubleval($upd['inv_qty']);
			if ($upd['inv_cost']!=='') $upd['inv_cost'] = doubleval($upd['inv_cost']);

			$upd['po_item_id'] = $form['po_item_id'][$k];
	        $upd['acc_foc_ctn'] = $form['acc_foc_ctn'][$k];
	        $upd['acc_foc_pcs'] = $form['acc_foc_pcs'][$k];
	        $upd['acc_foc_amt'] = $form['acc_foc_amt'][$k];
	        $upd['acc_disc'] = $form['acc_disc'][$k];
	        $upd['acc_disc_amt'] = $form['acc_disc_amt'][$k];

	  		$con->sql_query($abc="update tmp_grn_items set ".mysql_update_by_field($upd,false,1)." where id=".mi($k)." and grn_id=".mi($form['id'])." and branch_id=".mi($branch_id));
		}
	}
}

function open_grr(){
	global $con, $smarty, $LANG, $branch_id;
	
	$form=$_REQUEST;
	$grn_id=intval($form['id']);
	$grr_id = intval($form['grr_id']);
	
	//make sure grr is not deleted
	$con->sql_query("select id from grr where active=1 and id=$grr_id and branch_id=$branch_id");
	$grr = $con->sql_fetchassoc();
	$con->sql_freeresult();

	if (!$grr){
		$smarty->assign("url", "/goods_receiving_note.php");
		$smarty->assign("title", "Goods Receiving Note");
		$smarty->assign("subject", sprintf($LANG['GRR_DELETED'], $grr_id));
		$smarty->display("redir.tpl");
		exit;
	}
	
	// make sure this GRR is not used
	$con->sql_query("select id from grn where active=1 and grr_id=$grr_id and branch_id=$branch_id");
	$smarty->assign("new", "1");

	if (!$_REQUEST['test'] && $r = $con->sql_fetchrow()){
		$smarty->assign("url", "/goods_receiving_note.php");
		$smarty->assign("title", "Goods Receiving Note");
		$smarty->assign("subject", sprintf($LANG['GRN_GRR_ITEM_USED'], $grr_id, $r['id']));
		$smarty->display("redir.tpl");
		exit;
	}

	grn_open($grn_id, $branch_id);
}

function grn_open($grn_id, $branch_id){
	global $con, $smarty, $LANG, $sessioninfo, $config;
	$form=$_REQUEST;
	$got_error = false;
	if($form['err']) $got_error = true;
	
	if ($grn_id==0){
		$grn_id=time();
		if($grn_id <= $_SESSION['grn_last_create_time']) {$grn_id = $_SESSION['grn_last_create_time']+1;}
		$_SESSION['grn_last_create_time'] = $grn_id;
		$form['id']=$grn_id;
	}
	
	//if the action is open and is not a NEW GRN
	if ($form['a']=='open' && !is_new_id($grn_id)){
		$form=load_grn_header($grn_id, $branch_id);	
		if(!$form || ($form && BRANCH_CODE != 'HQ' && $form['branch_id'] != $sessioninfo['branch_id'])){
		    $smarty->assign("url", "/goods_receiving_note.php");
		    $smarty->assign("title", "GRN (Goods Receiving Note)");
		    $smarty->assign("subject", sprintf($LANG['GRN_NOT_FOUND'], $grn_id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		// locked, not allow to edit
		if(($form['status']>0 && $form['status']!=2) || $form['active']!=1 || $form['branch_id'] != $sessioninfo['branch_id']){
		    grn_view($grn_id, $branch_id);
		    exit;
		}
		if(!$got_error) copy_to_tmp($grn_id, $branch_id);
	}
	//if the grn is NEW
	else{
	    $grr_id=intval($form['grr_id']);
		$grr=load_grr_item_header($grr_id, $branch_id, 0, 1);

		$smarty->assign("grr", $grr);
		
		$form['branch_id'] = $sessioninfo['branch_id'];
		$form['id']=$grn_id;
		$form['vendor_id']=$grr['vendor_id'];
		$form['grr_id']=$grr['grr_id'];
		$form['department_id']=$grr['department_id'];
		if(!$form['branch_code']) $form['branch_code'] = $grr['branch_code'];
		if($grr['tax_register']) $form['grn_tax'] = $grr['tax_percent'];
		if($grr['report_prefix']) $form['report_prefix'] = $grr['report_prefix'];
		
		// have po, load from po
		if ($form['a']!='refresh' && !$got_error){
			$con->sql_query("delete from tmp_grn_items where grn_id=".mi($grn_id)." and branch_id=".mi($branch_id)." and user_id=".mi($sessioninfo['id']));
			if($grr['grp_do_no']){
				copy_do_items($grr['grp_do_no'], $grn_id, $branch_id);
				$form['po_items'] = load_do_items($grr['grp_do_no']);
			}elseif($grr['grp_po_no']){
				copy_po_items($grr['grp_po_no'], $grn_id, $branch_id);
				$form['po_items'] = load_po_items($grr['grp_po_no']);
			}
		}
		
		// GRN needs check again for GST status
		if($config['enable_gst']){
			// if found grr contains PO which having foreign currency from PO, always mark GRN as non gst
			if($grr['currency_code'] || !$grr['is_under_gst']){
				$form['is_under_gst'] = 0;
				//$form['branch_is_under_gst'] = 0;
			}else{
				/*$prms = array();
				$prms['vendor_id'] = $form['vendor_id'];
				$prms['date'] = $grr['rcv_date'];
				$form['is_under_gst'] = check_gst_status($prms);*/
				$form['is_under_gst'] = $grr['is_under_gst'];
			}

			// need to load if branch is under gst
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = date("Y-m-d");
			$form['branch_is_under_gst'] = check_gst_status($prms);
		}
	}

    if((($grr['type']=='PO' && $grr['grn_auto_load_po_items']) || ($form['grn_auto_load_po_items'] && $form['type'] == 'PO')) && $_REQUEST['action'] == 'edit' && privilege('GRN_CAN_LOAD_ALL_PO_ITEMS')){
		$smarty->assign("allow_auto_load_po_items",true);
	}

	// it is not from account verification
	//if($_REQUEST['action'] && !is_new_id($grn_id)){
		//$form['po_items'] = false;
	//}
	if($_REQUEST['action'] != "grr_edit"){
		$form['items']=load_grn_items($grn_id, $branch_id, $form['po_items'], true);
	}else{
		$q1 = $con->sql_query("select grr_items.*, doc_no as curr_po_no from grr_items where grr_id = ".mi($form['grr_id'])." and branch_id = ".mi($branch_id));
		
		while($r = $con->sql_fetchassoc($q1)){
			$items[] = $r;
		}
		$con->sql_freeresult($q1);

		$form['grr_items']=$items;
	}
	
    $q1 = $con->sql_query("select po.*
						   from grn
						   left join grr_items gi on grn.grr_id = gi.grr_id and grn.branch_id = gi.branch_id
						   left join po on case when po.po_branch_id > 0 and po.po_branch_id is not null then po.po_branch_id = gi.branch_id else po.branch_id = gi.branch_id end and po.id=gi.po_id
						   where grn.id =". mi($grn_id) ." and grn.branch_id = ". mi($branch_id) ." and gi.type = 'PO'
						   limit 1");
	
    $po = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	if ($po['id']){
		$po['deliver_to']=unserialize($po['deliver_to']);	
		
		$po['sdiscount']=unserialize($po['sdiscount']);
		if(is_array($po['sdiscount'])) 
			$po['sdiscount']=$po['sdiscount'][0];
		
		$po['rdiscount']=unserialize($po['rdiscount']);
		if(is_array($po['rdiscount'])) 
			$po['rdiscount']=$po['rdiscount'][0];
		
		$po['ddiscount']=unserialize($po['ddiscount']);
		if(is_array($po['ddiscount'])) 
			$po['ddiscount']=$po['ddiscount'][0];
		
		$po['misc_cost']=unserialize($po['misc_cost']);
		if(is_array($po['misc_cost'])) 
			$po['misc_cost']=$po['misc_cost'][0];

		$po['transport_cost']=unserialize($po['transport_cost']);
		if(is_array($po['transport_cost']))
			$po['transport_cost']=$po['transport_cost'][0];
		
		$po['remark']=unserialize($po['remark']);
		if(is_array($po['remark']))
			$po['remark']=$po['remark'][0];
			
		$po['remark2']=unserialize($po['remark2']);
		if(is_array($po['remark2']))
			$po['remark2']=$po['remark2'][0];

		$smarty->assign("form_po", $po);
		$con->sql_freeresult();

		$q1=$con->sql_query($abc="select tpi.*, si.sku_item_code, si.description, si.artno, si.mcode,pkuom.code as packing_uom, if(pkuom.fraction > 1, uom.fraction, pkuom.fraction) as selling_uom_fraction, pkuom.fraction as master_uom_fraction, si.link_code, uom.code as order_uom, u2.code as selling_uom,si.packing_uom_id as master_uom_id, si.sku_id, sai.photo_count, si.sku_apply_items_id, si.doc_allow_decimal, si.sku_id, si.size, si.color,tpi.so_branch_id,tpi.so_item_id, si.hq_cost, sku.category_id
		from po_items tpi  
		left join sku_items si on tpi.sku_item_id = si.id
		left join sku on sku.id = si.sku_id
		left join sku_apply_items sai on sai.id=si.sku_apply_items_id
		left join uom on uom.id = tpi.order_uom_id
		left join uom u2 on u2.id=tpi.selling_uom_id
		left join uom pkuom on pkuom.id = si.packing_uom_id
		where tpi.po_id=".mi($po['id'])." and tpi.branch_id=".mi($po['branch_id'])."
		order by tpi.id");//print $abc;
		$total=array();
		$foc_annotations=array();
		$foc_id=0;
		
		while($r1=$con->sql_fetchassoc($q1)){
			$sid = mi($r1['sku_item_id']);
			$sku_apply_items_id = mi($r1['sku_apply_items_id']);
			
			$r1['deliver_to']=unserialize($r1['deliver_to']);
			
			if(is_array($r1['deliver_to']))
				$r1['balance']=unserialize($r1['balance']);
				
			if($r1['is_foc']){
				$foc_id++;
				$r1['foc_id']=$foc_id;
			}
			
			$r1['selling_price_allocation']=unserialize($r1['selling_price_allocation']);
			$r1['gst_selling_price_allocation']=unserialize($r1['gst_selling_price_allocation']);
			$r1['qty_allocation']=unserialize($r1['qty_allocation']);
			$r1['qty_loose_allocation']=unserialize($r1['qty_loose_allocation']);
			$r1['foc_allocation']=unserialize($r1['foc_allocation']);
			$r1['foc_loose_allocation']=unserialize($r1['foc_loose_allocation']);
			$r1['sales_trend']=unserialize($r1['sales_trend']);
			$r1['stock_balance']=unserialize($r1['stock_balance']);
			$r1['parent_stock_balance']=unserialize($r1['parent_stock_balance']);

			$r1['total_selling'] = 0;
			if($r1['selling_uom_fraction']==0)
				$r1['selling_uom_fraction']=1;
			if($r1['order_uom_fraction']==0)
				$r1['order_uom_fraction']=1;
			
			if(is_array($po['deliver_to'])){
				$r1['balance'] = unserialize($r1['balance']);
				$r1['qty']=0;
				foreach($po['deliver_to'] as $v=>$k){
					$q = $r1['qty_allocation'][$k]*$r1['order_uom_fraction'] + $r1['qty_loose_allocation'][$k];				
					$r1['qty'] += $q;
					$total['qty_allocation'][$k] += $q;
					$total['qty'] += $q;
					$r1['row_qty'] += $q;
				
					$q2 = $r1['foc_allocation'][$k]*$r1['order_uom_fraction'] + $r1['foc_loose_allocation'][$k];
					$r1['foc'] += $q2;
					$total['foc_allocation'][$k] += $q2;
					$total['foc'] += $q2;
					$r1['row_foc'] += $q2;
					
					$r1['branch_qty'][$k] += $q + $q2;
					$r1['branch_qty']['total'] += $q + $q2;

					$r1['ctn'] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
					$total['ctn'] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
					$r1['total_selling'] += ($q+$q2)/$r1['selling_uom_fraction']*$r1['selling_price_allocation'][$k];
					$r1["br_sp"][$k] = ($q+$q2)/$r1['selling_uom_fraction']*$r1['selling_price_allocation'][$k];
					$r1["br_cp"][$k] = $q/$r1['order_uom_fraction']*$r1['order_price'];

					$total['br_sp'][$k] += $r1["br_sp"][$k];
					$total['br_cp'][$k] += $r1["br_cp"][$k];
					
					$total['total_ctn'][$k] += $r1['qty_allocation'][$k] + $r1['foc_allocation'][$k];
					$total['total_pcs'][$k] += $r1['qty_loose_allocation'][$k] + $r1['foc_loose_allocation'][$k];
					
					if($form['branch_is_under_gst']){
						$r1['total_gst_selling'] += ($q+$q2)/$r1['selling_uom_fraction']*$r1['gst_selling_price_allocation'][$k];
					}
				}
				$r1['gamount'] = $r1['qty']/$r1['order_uom_fraction']*$r1['order_price'];
			}
			else{
				$r1['row_qty'] = $r1['qty']*$r1['order_uom_fraction']+$r1['qty_loose'];
				$r1['row_foc'] = $r1['foc']*$r1['order_uom_fraction']+$r1['foc_loose'];

				$total['qty']+=$r1['row_qty'];
				
				$total['foc']+=$r1['row_foc'];

				$total['ctn']+=$r1['qty']+$r1['foc'];

				$r1['total_selling'] = ($r1['row_qty']+$r1['row_foc'])/$r1['selling_uom_fraction']*$r1['selling_price'];

				$r1["br_sp"] = $r1['total_selling'];
				$r1["br_cp"] = $r1['row_qty']/$r1['order_uom_fraction']*$r1['order_price'];

				$total['br_sp'] += $r1["br_sp"];
				$total['br_cp'] += $r1["br_cp"];

				$r1['gamount'] = ($r1['qty']+($r1['qty_loose']/$r1['order_uom_fraction']))*$r1['order_price'];
				
				if($form['branch_is_under_gst']){
					$r1['total_gst_selling'] = ($r1['row_qty']+$r1['row_foc'])/$r1['selling_uom_fraction']*$r1['gst_selling_price'];
				}
			}

			$total['sell'] += $r1['total_selling'];
			$total['gst_sell'] += $r1['total_gst_selling'];

			if (!$r1['is_foc'])
				$total['gamount'] += $r1['gamount'];
			$r1['amount'] = $r1['gamount'];

			if ($r1['tax']>0)
				$r1['amount'] *= ($r1['tax']+100)/100;
					
			if ($r1['discount']){
				$camt = $r1['amount'];
				$r1['amount'] = parse_formula($r1['amount'],$r1['discount']);
				$r1['disc_amount'] = $camt - $r1['amount'];
			}
			
			if(!$r1['artno_mcode']){
				if($r1['artno'])
					$r1['artno_mcode']=$r1['artno'];
				else
					$r1['artno_mcode']=$r1['mcode'];				
			}
		
			if (!$r1['is_foc'])
				$total['amount'] += round($r1['amount'], 2);

			if(is_array($po['deliver_to'])){
				$branch_disc = 0; $branch_gp = 0;
				foreach($po['deliver_to'] as $v=>$k){
					if ($r1['br_sp'][$k]>0) 
					{
						$branch_disc = $r1['disc_amount'] * $r1['branch_qty'][$k] / $r1['branch_qty']['total'];
						$branch_gp = ($r1['br_sp'][$k]-$r1['br_cp'][$k]-$branch_disc)/$r1['br_sp'][$k];
						
						$r1['branch_gp'][$k] = round($branch_gp*100,2);
					}
				}
			}
			
			if($po['active'] && $config['allow_sales_order'] && $r1['so_branch_id'] && $r1['so_item_id']){
				// get sales order item relationship
				$q_so = $con->sql_query("select soi.*,so.order_no
				from sales_order_items soi
				left join sales_order so on so.branch_id=soi.branch_id and so.id=soi.sales_order_id
				where soi.branch_id=".mi($r1['so_branch_id'])." and soi.id=".mi($r1['so_item_id']));
				$tmp_so_info = $con->sql_fetchassoc($q_so);
				$con->sql_freeresult($q_so);
				
				if($tmp_so_info){
					$r1['sales_order_items'] = $tmp_so_info;
				}
			}

			if($po['is_under_gst'] && !$r1['is_foc']){
				// if found it is not being used previous and it is refresh, then load new gst info
				//if($_REQUEST['a'] == "refresh"){
				if(!$r1['cost_gst_id']){
					$input_gst = get_sku_gst("input_tax", $sid);
					if($input_gst){
						$r1['cost_gst_id'] = $input_gst['id'];
						$r1['cost_gst_code'] = $input_gst['code'];
						$r1['cost_gst_rate'] = $input_gst['rate'];
					}else{
						$r1['cost_gst_id'] = $input_gst_list[0]['id'];
						$r1['cost_gst_code'] = $input_gst_list[0]['code'];
						$r1['cost_gst_rate'] = $input_gst_list[0]['rate'];
					}
				}
				//}

				if($r1['row_qty']){
					// calculate GST cost
					$order_price = round($r1['amount'] / $r1['row_qty'], $config['global_cost_decimal_points']);
					$r1['cost_gst_amt'] = round($order_price*$r1['cost_gst_rate']/100, $config['global_cost_decimal_points']);
					$r1['row_cost_gst'] = round($r1['cost_gst_amt'] * $r1['row_qty'], 2);
					$r1['row_cost_gst_amt'] = $r1['amount'] + $r1['row_cost_gst'];
					$total['gst_rate_amount'] += round($r1['row_cost_gst'], 2);
					$total['gst_amount'] += round($r1['row_cost_gst_amt'], 2);
				}
				
				// gst
				if($po['is_under_gst']){
					if($r1['cost_gst_id']){
						$prms = array();
						$prms['gst_id'] = $r1['cost_gst_id'];
						$prms['gst_code'] = $r1['cost_gst_code'];
						$prms['gst_rate'] = $r1['cost_gst_rate'];
						$prms['gst_list'] = $input_gst_list;
						$input_gst_list = check_and_extend_gst_list($prms); // cost GST
					}
				}
			}
			
			if($form['branch_is_under_gst'] && !$r1['is_foc']){
				$is_inclusive_tax = get_sku_gst("inclusive_tax", $sid);
				
				if(!$r1['selling_gst_id']){
					$output_gst = get_sku_gst("output_tax", $sid);
					if($output_gst){
						$r1['selling_gst_id'] = $output_gst['id'];
						$r1['selling_gst_code'] = $output_gst['code'];
						$r1['selling_gst_rate'] = $output_gst['rate'];
					}else{
						$r1['selling_gst_id'] = $output_gst_list[0]['id'];
						$r1['selling_gst_code'] = $output_gst_list[0]['code'];
						$r1['selling_gst_rate'] = $output_gst_list[0]['rate'];
					}
				}
				
				if(is_array($po['deliver_to'])){
					foreach($po['deliver_to'] as $v=>$k){
						if(!$r1['gst_selling_price_allocation'][$k]){
							$prms = array();
							$prms['selling_price'] = $r1['selling_price_allocation'][$k];
							$prms['inclusive_tax'] = $is_inclusive_tax;
							$prms['gst_rate'] = $r1['selling_gst_rate'];
							$gst_sp_info = calculate_gst_sp($prms);
							
							if($is_inclusive_tax == "yes"){
								$r1['gst_selling_price_allocation'][$k] = $r1['selling_price_allocation'][$k];
								$r1['selling_price_allocation'][$k] = $gst_sp_info['gst_selling_price'];
							}else{
								$r1['gst_selling_price_allocation'][$k] = $gst_sp_info['gst_selling_price'];
							}
						}
					}
				}elseif(!$r1['gst_selling_price']){
					$prms = array();
					$prms['selling_price'] = $r1['selling_price'];
					$prms['inclusive_tax'] = $is_inclusive_tax;
					$prms['gst_rate'] = $r1['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					
					if($is_inclusive_tax == "no") $r1['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					else $r1['gst_selling_price'] = $r1['selling_price'];
					
					if($is_inclusive_tax == "yes"){
						$r1['gst_selling_price'] = $r1['selling_price'];
						$r1['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$r1['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				// get inclusive tax follow by sku > category
				$r1['inclusive_tax'] = get_sku_gst("inclusive_tax", $r1['sku_item_id']);
				
				if($r1['inclusive_tax'] == "yes") $sp_inclusive_tax = 1;
				
				if($r1['selling_gst_id']){
					$prms = array();
					$prms['gst_id'] = $r1['selling_gst_id'];
					$prms['gst_code'] = $r1['selling_gst_code'];
					$prms['gst_rate'] = $r1['selling_gst_rate'];
					$prms['gst_list'] = $output_gst_list;
					$output_gst_list = check_and_extend_gst_list($prms); // selling GST
				}
			}
		}
		$con->sql_freeresult($q1);
	
		foreach ($total as $k=>$dummy){
			$a = $total['amount'];
			$a = parse_formula($a,$po['misc_cost'],true);
			$tmpa = $a;
			$a = parse_formula($a,$po['sdiscount'],false);
			$total['sdiscount_amount'] = $tmpa - $a;
			$b = $a;
			$a = parse_formula($a,$po['rdiscount'],false); // hidden discount
			$a = parse_formula($a,$po['ddiscount'],false); // hidden discount (deduct cost)
			$a += $po['transport_cost'];
			$b += $po['transport_cost'];
			$total['final_amount2'] = $b;
			$total['final_amount'] = $a;
			
			$a = $total['gst_amount'];
			$a = parse_formula($a,$po['misc_cost'],true);
			$tmpa = $a;
			$a = parse_formula($a,$po['sdiscount'],false);
			$total['sdiscount_gst_amount'] = $tmpa - $a;
			$b = $a;
			$a = parse_formula($a,$po['rdiscount'],false); // hidden discount
			$a = parse_formula($a,$po['ddiscount'],false); // hidden discount (deduct cost)
			$a += $po['transport_cost'];
			$b += $po['transport_cost'];
			$total['final_gst_amount2'] = $b;
			$total['final_gst_amount'] = $a;
		}
		$smarty->assign("total_po", $total);
	}
	$smarty->assign("form", $form);
	$smarty->display("goods_receiving_note2.new.tpl");
}

function grn_view($grn_id, $branch_id){
	global $con, $smarty, $LANG, $sessioninfo;
	$form=load_grn_header($grn_id, $branch_id);
	
	if(!$form){
	    $smarty->assign("url", "/goods_receiving_note.php");
	    $smarty->assign("title", "GRN (Goods Receiving Note)");
	    $smarty->assign("subject", sprintf($LANG['GRN_NOT_FOUND'], $grn_id));
	    $smarty->display("redir.tpl");
	    exit;
	}
	
	$form['items']=load_grn_items($grn_id, $branch_id, $form['po_items']);
	
	// check whether got edited from Account Verification, if yes then need to show Account Verification columns
	/*foreach($form['items'] as $gid=>$r){
		if((trim($r['acc_ctn']) != "" && $r['acc_ctn'] >= 0) || (trim($r['acc_pcs']) != "" && $r['acc_pcs'] >= 0) || (trim($r['inv_qty']) != "" && $r['inv_qty'] >= 0) || (trim($r['acc_cost']) != "" && $r['acc_cost'] >= 0)|| (trim($r['inv_cost']) != "" && $r['inv_cost'] >= 0)){
			if($form['authorized'] && $form['status'] == 1) $smarty->assign("manager_col", 1);
			$smarty->assign("cu_id", 1);
			break;
		}
	}*/
    
    $required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
    if(privilege('GRN_ALLOW_USER_RESET') || $sessioninfo['level']>=$required_level) {
        $smarty->assign("allow_reset", 1);
    }else {
        $smarty->assign("allow_reset", 0);
    }
    $smarty->assign("manager_col", 1);
    $smarty->assign("cu_id", 1);
	$smarty->assign("form", $form);
	$smarty->display("goods_receiving_note.view.tpl");
}

function update_all_total_selling($where){
	global $con;
	if($where) $where = " where ".$where;
    $grn=$con->sql_query("select id,branch_id from grn $where");
	echo "select id,branch_id from grn $where<br>";	
    print $con->sql_numrows() . " rows found<br />";
    while($r=$con->sql_fetchrow($grn)){
    	update_total_selling($r['id'],$r['branch_id']);
    }
    print "<li> Done.";
    exit;	
}

function update_all_total_amount($where){
	global $con;
    $grn=$con->sql_query("select id,branch_id from grn $where");
	echo "select id,branch_id from grn $where<br>";	
    print $con->sql_numrows() . " rows found<br />";
    while($r=$con->sql_fetchrow($grn)){
    	update_total_amount($r['id'],$r['branch_id']);
    }
    print "<li> Done.";
    exit;	
}

function fix_acc_ctnpcs(){
	global $con;
    $con->sql_query("update grn_items set acc_ctn=0 where acc_ctn is null and acc_pcs is not null") or die(mysql_error());
	print $con->sql_affectedrows()." acc_ctn updated<br />";		
    $con->sql_query("update grn_items set acc_pcs=0 where acc_ctn is not null and acc_pcs is null") or die(mysql_error());
    print $con->sql_affectedrows()." acc_pcs updated<br />";
}

function load_grn_list($t=1){
	global $con, $sessioninfo, $smarty, $config;

	if (isset($_REQUEST['t'])){
		$t = intval($_REQUEST['t']);
		// if t used and s unused, this is for prompt (cancel, confirm etc)
		// we will show "saved GRN"
		if ($t==0 && !isset($_REQUEST['search'])) $t=1;
	}
	$no_owner_check = false;
	
	switch ($t){
	    case 0: // search
	        $str = trim($_REQUEST['search']);
	        $vendor_id = $_REQUEST['vendor_id'];
			if(!$str && !$vendor_id) die('Cannot search empty string');

			$where = array();
			if($str){
				$where[] = "(grn.id=".ms(preg_replace("/[^0-9]/","", $str))." or (select gri.id from grr_items gri where gri.doc_no like ".ms("%".replace_special_char($str)."%")." and gri.grr_id = grn.grr_id and gri.branch_id = grn.branch_id group by gri.grr_id) or grn.grr_id=".ms(preg_replace("/[^0-9]/","", $str)).")";
			}
			
			if($vendor_id){
				$where[] = "grn.vendor_id = ".mi($vendor_id);
			}
			
			$where = join(" and ", $where);

			if(privilege('GRN_VAR_DIV') || privilege('GRN_SIV_DIV') || privilege('GRN_PC_DIV') || privilege('GRN_ACCV_DIV')){
				$owner_check_filter = "or case 
											when grn.active = 1 and grn.status = 0 and (".mi(privilege('GRN_VAR_DIV'))." or ".mi(privilege('GRN_SIV_DIV'))." or ".mi(privilege('GRN_PC_DIV')).")
											then 1
											when grn.active = 1 and grn.authorized = 1 and grn.div1_approved_by != 0 and grn.div2_approved_by != 0 and (grn.div4_approved_by = 0 or grn.div4_approved_by is null)  and ".mi(privilege('GRN_ACCV_DIV'))."
											then 1
											else 0
									   end";
			}
	        break;

		case 1: // show saved
        	$where = "grn.active = 1 and grn.status = 0";

			if(privilege('GRN_VAR_DIV') || privilege('GRN_SIV_DIV') || privilege('GRN_PC_DIV')) $no_owner_check = true; // ignore owner checking while the user has the privilege
			$smarty->assign("action", "verify");
			
			if(privilege('GRN_CHANGE_OWNER')) $no_owner_check = true;
        	break;
		case 2: // show pending documents
        	$where = "grn.active = 1 and grn.authorized = 1 and grn.div1_approved_by != 0 and grn.div2_approved_by != 0 and grn.div3_approved_by != 0 and (grn.div4_approved_by = 0 or grn.div4_approved_by is null) and grr.grr_amount = 0 and grr.grr_amount != grn.amount and grn.approved = 0";
			
			if(privilege('GRN_ACCV_DIV')) $no_owner_check = true; // ignore owner checking while the user has the privilege
        	$smarty->assign("action", "grr_edit");
			
			break;
		case 3: // show account verification
        	$where = "grn.active = 1 and grn.authorized = 1 and grn.div1_approved_by != 0 and grn.div2_approved_by != 0 and (grn.div4_approved_by = 0 or grn.div4_approved_by is null) and (grr.grr_amount != 0 or grr.grr_amount = grn.amount) and grn.approved = 0";
			
			if(privilege('GRN_ACCV_DIV')) $no_owner_check = true; // ignore owner checking while the user has the privilege
        	break;
		case 4: // show cancelled
		    $where = "(grn.status = 5 and grn.approved = 0) or grn.active = 0";
		    break;
		case 5: // show Waiting for Approval
        	$where = "grn.active = 1 and grn.status = 1 and grn.approved = 0";
        	break;
		case 6: // show approved
        	$where = "grn.active = 1 and grn.status = 1 and grn.approved = 1";
        	break;
	}

	if ($sessioninfo['level']>=9999 || $no_owner_check) 
		$owner_check = "";	
	else
		$owner_check = "(flow_approvals like '%|$sessioninfo[id]|%' or notify_users like '%|$sessioninfo[id]|%' or grn.user_id = $sessioninfo[id] or po.user_id=$sessioninfo[id] $owner_check_filter) and ";
	
	if(!$config['consignment_modules']){
		if ($t!=0 || BRANCH_CODE != "HQ") $where .= " and grn.branch_id = $sessioninfo[branch_id]";
	}

	if (isset($t)){
		if ($sessioninfo['level']<9999 && !$no_owner_check && $t == 1){
			$where .= " and case when grn.user_id != ".mi($sessioninfo['id'])." and (".mi(privilege('GRN_VAR_DIV'))." = 0 and ".mi(privilege('GRN_SIV_DIV'))." = 0 and ".mi(privilege('GRN_PC_DIV'))." = 0) then grn.id = 0 else 1=1 end";
		}

		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}
		$limit =  "limit $start, $sz";

		$con->sql_query("select count(*) from grn
left join branch_approval_history on grn.approval_history_id = branch_approval_history.id and grn.branch_id = branch_approval_history.branch_id
left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
left join po on po.id=grr_items.po_id and po.branch_id=grr_items.branch_id and grr_items.type='PO'
where $owner_check $where");

		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
		    if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start) $pg .= " selected";
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
	}

	$sql = "select grn.*, branch_approval_history.approvals, branch_approval_history.flow_approvals,grr.rcv_date,
	if (vendor.id, vendor.description, (select branch.description from do left join branch on do.branch_id = branch.id where do_no = grr_items.doc_no)) as vendor,branch.report_prefix,grr_items.doc_no,grr_items.type,po.user_id as po_user_id,branch_approval_history.approval_order_id,vendor.code as vendor_code, branch.code as branch_code,
	if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id, grr.currency_code, grr.currency_rate
	from grn
	left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id
	left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
	left join vendor on grn.vendor_id = vendor.id
	left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grn.branch_id
	left join branch on grn.branch_id = branch.id
	left join branch_approval_history on (grn.approval_history_id = branch_approval_history.id and grn.branch_id = branch_approval_history.branch_id)
	left join po on po.id=grr_items.po_id and po.branch_id=grr_items.branch_id and grr_items.type='PO'
	where $owner_check $where 
	order by grn.last_update desc $limit";
	//print $sql;
	$ql = $con->sql_query($sql);

	$grn_list = $existed_grr = array();
	while($r1=$con->sql_fetchassoc($ql)){
		if($r1['is_future']){
			$grp_doc = array();
			$is_from_do = false;
		
			/*$sql2 = $con->sql_query("select group_concat(distinct doc_no separator ', ') as doc_no, type, case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc from grr_items where grr_id = ".ms($r1['grr_id'])." and branch_id = ".ms($r1['branch_id'])." group by type_asc order by type_asc ASC limit 1");

			while($r2=$con->sql_fetchrow($sql2)){
				$r1['doc_no'] = $r2['doc_no'];
				$r1['type'] = $r2['type'];
			}*/
			$sql2 = $con->sql_query("select gi.*
			from grr_items gi
			where gi.grr_id = ".ms($r1['grr_id'])." and gi.branch_id = ".ms($r1['branch_id']));
			
			while($r2=$con->sql_fetchrow($sql2)){
				$grp_doc[$r2['type']][$r2['doc_no']] = $r2['doc_no'];
				
				if($r2['type'] == "DO" && ($config['do_skip_generate_grn'] || $sessioninfo['branch_type'] == "franchise")){
					if($sessioninfo['branch_type'] == "franchise") $filter = "debtor_id = ".mi($sessioninfo['debtor_id'])." and do_type = 'credit_sales'";
					else $filter = "do_branch_id = ".mi($r2['branch_id'])." and do_type = 'transfer'";
					$q3 = $con->sql_query("select *, id as do_id from do where do_no = ".ms($r2['doc_no'])." and ".$filter);
					if($con->sql_numrows($q3) > 0){  // means it is IBT DO
						while($grr_do = $con->sql_fetchassoc($q3)){
							$is_from_do = true;
						}
					}
					$con->sql_freeresult($q3);
				}
			}
			$con->sql_freeresult($sql2);
			
			if($is_from_do){
				$r1['type'] = "DO";
				$r1['doc_no'] = join(", ", $grp_doc['DO']);
			}elseif(isset($grp_doc['PO'])){
				$r1['type'] = "PO";
				$r1['doc_no'] = join(", ", $grp_doc['PO']);
			}elseif(isset($grp_doc['INVOICE'])){
				$r1['type'] = "INVOICE";
				$r1['doc_no'] = join(", ", $grp_doc['INVOICE']);
			}elseif(isset($grp_doc['DO'])){
				$r1['type'] = "DO";
				$r1['doc_no'] = join(", ", $grp_doc['DO']);
			}else{
				$r1['type'] = "OTHER";
				$r1['doc_no'] = join(", ", $grp_doc['OTHER']);
			}
			
		}
		
		if($config['grn_summary_show_related_invoice'] && $r1['type'] == "PO"){
			$q1 = $con->sql_query("select group_concat(gi.doc_no order by 1 separator ', ') as related_invoice from grr_items gi where gi.type='INVOICE' and gi.grr_id=".mi($r1['grr_id'])." and gi.branch_id=".mi($r1['branch_id']));
			
			$tmp = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$r1['related_invoice'] = $tmp['related_invoice'];
		}
		
		// verify if this approved GRN is using ARMS generated DN
		if($r1['approved']){
			$q2 = $con->sql_query("select * from dnote where ref_table = 'grn' and ref_id = ".mi($r1['id'])." and branch_id = ".mi($r1['branch_id'])." and active=1");
			$dnote_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($dnote_info){ // found this GRN already have DN generated
				$r1['print_arms_dn'] = 1;
			}elseif(!$r1['dn_issued'] && !$r1['dn_number'] && !$r1['dn_amount']){ // found this GRN already have DN generated
				//$r1['generate_arms_dn'] = 1; no longer allow user to generate from list
			}else $r1['generate_arms_dn'] = 0;
		}

        if($config['use_grn_future_allow_generate_gra'] && $r1['approved']) check_gra($r1,$r1['branch_id']);

		$grn_list[] = $r1;
		$existed_grr[$r1['grr_id']] = 1;
	}
	$con->sql_freeresult($q1);
	
	if($_REQUEST['search']){
		list_grr($_REQUEST['search']);
	}

	$smarty->assign("t", $t);
	$smarty->assign("search", $_REQUEST['search']);
	$smarty->assign("grn_list", $grn_list);
	$smarty->display("goods_receiving_note.list.tpl");
}

function list_grr($search_str=""){
	global $con, $smarty, $sessioninfo, $branch_id;

	$filters = array();
	if($search_str){
		$find_grr = $search_str;
		$filters[] = "grr.status=0";
	}elseif($_REQUEST['find_grr']) $find_grr = $_REQUEST['find_grr'];
	
	if ($find_grr){
	    // strip "grr#####" prefix
	    if (preg_match("/^grr/i", $find_grr)){
	    	$grrid = intval(substr($find_grr,3));
	    	$findstr = "and grr.id = $grrid";
		}
		else{
		    // search documents
			$con->sql_query("select distinct(grr_id) from grr_items where branch_id=".mi($sessioninfo['branch_id'])." and doc_no like ".ms("%".replace_special_char($find_grr)."%")." or grr_id = ".mi($find_grr));

			// return if no match
			if (!$con->sql_numrows()) return;
			$idlist = array();
			while($r=$con->sql_fetchrow()){
			    $idlist[] = $r[0];
			}
		    $findstr = "and grr.id in (".join(",",$idlist).")";
		}
	}else $having = "having grn_used_count = 0";

	if($filters) $filter = " and ".join(" and ", $filters);
	
	// show current grr
	$q1 = $con->sql_query("select grr.*, gi.*, grr.id as id, gi.id as grr_item_id, grr.id as grr_id, grr.rcv_date, 
						 vendor.description as vendor, user.u, user2.u as rcv_u, category.description as department,
						 vendor.code as vendor_code, if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
						 (select sum(ggi.grn_used) from grr_items ggi where ggi.grr_id = grr.id and ggi.branch_id = grr.branch_id) as grn_used_count,branch.report_prefix
						 from grr
						 left join branch on grr.branch_id=branch.id 
						 left join grr_items gi on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
						 left join user on grr.user_id = user.id
						 left join user user2 on grr.rcv_by = user2.id
						 left join vendor on grr.vendor_id = vendor.id
						 left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr.branch_id
						 left join category on grr.department_id = category.id
						 where grr.active=1 and grr.branch_id=".mi($sessioninfo['branch_id'])." $findstr $filter
						 $having
						 order by grr.rcv_date desc, grr.id
						 limit 100");

	while($r = $con->sql_fetchassoc($q1)){
		$grr[] = $r;
	}
	$con->sql_freeresult($q1);

	$smarty->assign("grr", $grr);
}

function get_parent_info($grn_id, $sid, $branch_id){
	global $con, $sessioninfo;
	$ttl_mwpo = 0;
	$ttl_mwpip = 0;

	$con->sql_query("select sku_items.sku_id, uom.id, uom.fraction as sku_fraction 
					 from sku_items 
					 left join uom on uom.id = sku_items.packing_uom_id 
					 where sku_items.id = ".mi($sid));
	$sku_id = $con->sql_fetchfield(0);
	$uom_id = $con->sql_fetchfield(1);
	$sku_uom = $con->sql_fetchfield(2);
	$con->sql_freeresult();

	// get the last po item id...
	$con->sql_query("select tgi.po_item_id
					 from tmp_grn_items tgi 
					 left join sku_items si on si.id = tgi.sku_item_id
					 left join sku on sku.id = si.sku_id
					 where tgi.item_group = 2 
					 and tgi.branch_id = ".mi($branch_id)." 
					 and tgi.grn_id =".mi($grn_id)."
					 and tgi.po_item_id != 0
					 and sku.id = ".mi($sku_id)."
					 order by tgi.id desc 
					 limit 1");
	$po_item_id = $con->sql_fetchfield(0);
	$con->sql_freeresult(); 
	
	if($po_item_id > 0) $filter = "and tgi.po_item_id = ".mi($po_item_id);

	$q1 = $con->sql_query("select $branch_id as branch_id, $sessioninfo[id] as user_id, $grn_id as grn_id, $sid as sku_item_id, tgi.artno_mcode, $uom_id as uom_id, tgi.cost*($sku_uom/if(pkuom.fraction > 1, pkuom.fraction, uom.fraction)) as cost, 
					 tgi.selling_price*ifnull($sku_uom/if(pkuom.fraction > 1 or tgi.cost > tgi.selling_price, pkuom.fraction, uom.fraction),1) as selling_price, 0 as po_qty, tgi.po_cost*($sku_uom/if(pkuom.fraction > 1, pkuom.fraction, uom.fraction)) as po_cost, tgi.po_item_id, tgi.weight, tgi.original_cost, 2 as item_group, sku.is_bom, si.mcode, tgi.gst_id, tgi.gst_code, tgi.gst_rate, tgi.selling_gst_id, tgi.selling_gst_code, 
					 tgi.selling_gst_rate, tgi.gst_selling_price
					 from tmp_grn_items tgi
					 left join sku_items si on si.id = tgi.sku_item_id
					 left join sku on sku.id = si.sku_id
					 left join uom on uom.id = tgi.uom_id
					 left join uom pkuom on pkuom.id = si.packing_uom_id
					 where tgi.branch_id = ".mi($branch_id)."
					 and tgi.grn_id =".mi($grn_id)."
					 and (tgi.item_group = 0 or tgi.item_group = 1 or tgi.item_group = 2)
					 and tgi.po_item_id != 0
					 and sku.id = ".mi($sku_id)."
					 $filter
					 and tgi.user_id = ".mi($sessioninfo['id'])."
					 order by tgi.id
					 limit 1");
	
	return $con->sql_fetchassoc($q1);
}

function ajax_add_item_row($grn_id, $branch_id){
    global $con, $smarty, $sessioninfo, $LANG, $config;

	$form=$_REQUEST;
    $grn_barcode = trim($form['grn_barcode']);
    $sku_info_arr = array();

	if($grn_barcode){    // add item by using scan barcode
		$grn_barcode_type = $form['grn_barcode_type'];
		if(!$grn_barcode_type){
			$sku_info=get_grn_barcode_info($grn_barcode,false);
		
			if ($sku_info['sku_item_id']){
				$sid = $sku_info['sku_item_id'];
				$sku_item_id_arr[] = $sid;
				$sku_info_arr[$sid]['pcs'] = mf($sku_info['qty_pcs']);
				$sku_info_arr[$sid]['selling_price'] = mf($sku_info['selling_price']);
				if(isset($sku_info['new_cost_price'])) $sku_info_arr[$sid]['cost_price'] = $sku_info['new_cost_price'];
				
				$existed_gi_info = is_item_existed($sid, $sku_info_arr[$sid], true);

				if($existed_gi_info){
					$temp['data']['existed_gi_id'] = $existed_gi_info['id'];
					$temp['data']['existed_si_code'] = $existed_gi_info['sku_item_code'];
					$temp['data']['existed_item_group'] = $existed_gi_info['item_group'];
					$temp['data']['existed_pcs'] = mf($sku_info['qty_pcs']);
					$ret[] = $temp;
					print json_encode($ret);
					return;
				}
			}
		}else{
			switch($grn_barcode_type){
				case 1:	// arms code, mcode, link code
					if (strlen($grn_barcode) == 13) {
						$grn_barcode2 = substr($grn_barcode,0,12);
					}
					if ($grn_barcode2) {
						$in_str = ms($grn_barcode).','.ms($grn_barcode2);
						$q1 = $con->sql_query("select * from sku_items where sku_item_code in ($in_str) or mcode in ($in_str) or artno in ($in_str) or link_code in ($in_str)");
					}
					else {
						$q1 = $con->sql_query("select * from sku_items where sku_item_code=".ms($grn_barcode)." or mcode=".ms($grn_barcode)." or artno=".ms($grn_barcode)." or link_code=".ms($grn_barcode));
					}

					if ($con->sql_numrows($q1) == 0){
						$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$grn_barcode);	
						if ($print_error) fail($si_info['err']);
					}elseif($con->sql_numrows($q1) > 1){ // contains more than 1 item
						// print the list of SKU items menu
						$items = array();
						while($r = $con->sql_fetchassoc($q1)){
							if($config['check_block_grn_as_po']){
								$block_list = unserialize($r['block_list']);
								$doc_is_blocked = isset($block_list[$sessioninfo['branch_id']]);
							}else{
								$block_list = unserialize($r['doc_block_list']);
								$doc_is_blocked = isset($block_list['grn'][$sessioninfo['branch_id']]);
							}
							if($doc_is_blocked)  $r['doc_is_blocked'] =1;
							$items[] = $r;
						}
						
						$smarty->assign("items", $items);
						$temp['data']['sku_list'] = 1;
						$temp['html'] = $smarty->fetch("goods_receiving_note.nsi_list.tpl");
						$ret[] = $temp;
						
						print json_encode($ret);
						return;
					}else{
						while($tmp = $con->sql_fetchassoc($q1)){
							if($tmp['active'] == 0)  fail(sprintf($LANG['GRN_INACTIVE_SKU'], $grn_barcode));
							$sku_item_id_arr[] = $tmp['id'];
						}
					}
					$con->sql_freeresult();
					break;
				default:
					$si_info['err'] = "Invalid GRN Barcode Type";	
					if ($print_error)	fail("Invalid GRN Barcode Type");
					break;
			}
		}
		
		if($sku_item_id_arr){
			//check for block item
			$q1 = $con->sql_query("select doc_block_list, block_list, active from sku_items where id in (".join(",", $sku_item_id_arr).") limit 1");
			$tmp_si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if($config['check_block_grn_as_po']) $block_list_array = unserialize($tmp_si_info['block_list']);
			else $block_list_array = unserialize($tmp_si_info['doc_block_list']);
			$active = $tmp_si_info['active'];

			if ($block_list_array) {
				if($config['check_block_grn_as_po']) $in_block_list = isset($block_list_array[$sessioninfo['branch_id']]);
				else $in_block_list = isset($block_list_array['grn'][$sessioninfo['branch_id']]);
				
				if ($in_block_list) fail(sprintf($LANG['DOC_ITEM_IS_BLOCKED'], "GRN"));
			}elseif(!$active){ // is inactive item
				fail($LANG['PO_ITEM_IS_INACTIVE']);
			}
		}
	}else{
	   $sku_item_id_arr = $_REQUEST['sku_code_list'];
	}
	
	if($sku_item_id_arr && $config['sku_bom_additional_type']){
		$sql = $con->sql_query("select *, si.sku_item_code
								from bom_items bi
								left join sku_items si on si.id = bi.bom_id
								left join sku on sku.id = si.sku_id 
								where bi.bom_id in (".join(",", $sku_item_id_arr).") and sku.is_bom = 1 and si.bom_type = 'package'");
		
		while($r = $con->sql_fetchassoc($sql)){
			$sid = $r['sku_item_id'];

			if($grn_barcode && !$grn_barcode_type && $sku_info_arr[$r['bom_id']]['pcs']){
				$qty_ratio = $sku_info_arr[$r['bom_id']]['pcs'];
			}else $qty_ratio = 1;
			$sku_info_arr[$sid]['pcs'] += $r['qty'] * $qty_ratio;

			if(!$sku_info_arr[$r['bom_id']]['bom_ref_num']) $sku_info_arr[$r['bom_id']]['bom_ref_num'] = $r['bom_id'];
			$sku_info_arr[$sid]['bom_ref_num'] = $r['bom_id'];
			$sku_info_arr[$sid]['bom_qty_ratio'] = $r['qty'];
			$sku_info_arr[$sid]['is_bom_item'] = true;
			if($sku_item_id_arr && !array_search($sid, $sku_item_id_arr)) $sku_item_id_arr[] = $sid;
			else{ // found user trying to add duplicate bom items at same time, show errors
				$temp['data']['error'] = sprintf($LANG['GRN_BOM_PACKAGE_EXISTED'], $r['sku_item_code']);
				$ret[] = $temp;
				print json_encode($ret);
				exit;
			}
		}
		$con->sql_freeresult($q1);
	}
	
	//asort($sku_item_id_arr);
	
	// check if vendor got tick want to monitor grn items qty not over po qty
	$q1 = $con->sql_query("select v.grn_qty_no_over_po_qty 
						   from grr
						   left join vendor v on v.id = grr.vendor_id						   
						   where grr.id = ".mi($form['grr_id'])." and grr.branch_id = ".mi($branch_id));
	$grr_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	if($sku_item_id_arr){
		$smarty->assign("grr", $form);
		//save_grn_items();
		$si_count = count($sku_item_id_arr);
		foreach($sku_item_id_arr as $sid){
			$err_msg = $temp = array();

			$doc_type = check_sku_item_type($grn_id, $sid, $branch_id, $form['type'], $form['gid']);
			
			if($doc_type == 2){
				$r = get_parent_info($grn_id, $sid, $branch_id);

				$doc_type = 1;  // combine document type 2 become 1
				$is_cf_d2 = 1;
			}else{
				if($doc_type == 1){ // straight take the undelivered item info from database
				
					if(!$form['gid']){
						$con->sql_query("select id from tmp_grn_items where sku_item_id = ".mi($sid)." and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and user_id = ".mi($sessioninfo['id'])." and item_group in (0, 1)");
						$form['gid'] = $con->sql_fetchfield(0);
						$con->sql_freeresult();
					}
					$r = get_items_details('', $form['gid']);
					//$r['selling_price'] = $r['sug_selling_price'];
					$r['po_qty'] = 0;
				}else $r = get_items_details(intval($sid), '');
				
				if($doc_type == 3){
					$r['po_item_id'] = 0;
					$r['po_qty'] = 0;
					$r['po_cost'] = 0;
				}
				$r['item_group'] = $doc_type;
				// do not insert the invalid SKU item into temporary table
			}
			
			if($form['type'] == "PO" && $doc_type > 2 && $grr_info['grn_qty_no_over_po_qty']){
				$temp['data']['error'] = sprintf($LANG['GRN_ITEM_OVER_PO_QTY_ITEM_REJECTED'], $r['sku_item_code']);
				$ret[] = $temp;
				continue;
			}
	
			if($r['is_bom'] && $sku_info_arr[$sid]['bom_ref_num']){
				$bom_id = $sku_info_arr[$sid]['bom_ref_num'];
				$q1 = $con->sql_query("select * from tmp_grn_items where bom_ref_num = ".mi($bom_id)." and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and user_id = ".mi($sessioninfo['id']));

				if($con->sql_numrows($q1) > 0){ // found already got add the following bom package
					$temp['data']['error'] = sprintf($LANG['GRN_BOM_PACKAGE_EXISTED'], $r['sku_item_code']);
					$ret[] = $temp;
					print json_encode($ret);
					exit;
				}else{
					$q2 = $con->sql_query("select * from tmp_grn_items where sku_item_id in (".join(",", $sku_item_id_arr).") and sku_item_id != ".mi($sid)." and grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and user_id = ".mi($sessioninfo['id']));
					
					if($con->sql_numrows($q2) > 0){
						$temp['data']['error'] = sprintf($LANG['GRN_BOM_PACKAGE_EXISTED'], $r['sku_item_code']);
						$ret[] = $temp;
						print json_encode($ret);
						exit;
					}
					$con->sql_freeresult($q2);
				}
				$con->sql_freeresult($q1);
				continue; // need to skip the bom original sku item
			}else{
				$r['is_bom'] = 0;
				$r['is_bom_item'] = 0;
			}

			if($sku_info_arr[$sid]['pcs']){ // from grn barcode or bom items
				if(ceil($sku_info_arr[$sid]['pcs']) != $sku_info_arr[$sid]['pcs'] && !$r['doc_allow_decimal']){ // is decimal points qty
					$err_msg[] = sprintf($LANG['GRN_SCALE_ITEM_INVALID_DP'], $r['sku_item_code']);
					$sku_info_arr[$sid]['pcs'] = "";
				}
				$r['pcs'] = $sku_info_arr[$sid]['pcs'];
			}else{
				$r['pcs'] = mi($form['new_qty']); // took from user input
			}
            
			if($sku_info_arr[$sid]['selling_price']){ // from grn barcode
				$r['selling_price'] = $sku_info_arr[$sid]['selling_price'];
			}
            
            if($config['enable_gst']){
                $prms = array();
                $prms['branch_id'] = $branch_id;
                $prms['date'] = $form['rcv_date'];
                $branch_under_gst = check_gst_status($prms);
                
                $prms = array();
                $prms['vendor_id'] = $form['vendor_id'];
                $prms['date'] = $form['rcv_date'];
                $vendor_under_gst = check_gst_status($prms);
            }
            
            if($branch_under_gst) { 
                $temp['data']['branch_under_gst'] = $branch_under_gst;
            }else {
                if($vendor_under_gst) {
                    $inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
                    if($inclusive_tax == "yes" && $r['gst_selling_price'] != 0) $r['selling_price'] = $r['gst_selling_price'];
                }
            }

			// cost price from grn barcoder
			if(isset($sku_info_arr[$sid]['cost_price'])){
				if(!$sku_info_arr[$sid]['cost_price']){
					$err_msg[] = sprintf($LANG['GRN_SCALE_ITEM_INVALID_TD'], $r['sku_item_code']);
				}
				$r['cost'] = $sku_info_arr[$sid]['cost_price'];
			}
			
			if($sku_info_arr[$sid]['is_bom_item']){
				$r['bom_ref_num'] = $sku_info_arr[$sid]['bom_ref_num'];
				$r['bom_qty_ratio'] = $sku_info_arr[$sid]['bom_qty_ratio'];
			}
			
			$item_id = add_temp_item($r, $grn_id);

			if($r['item_group'] == 2){
				$r = get_items_details('', $item_id);
				if($r['grn_item_id']) $r['id'] = $r['grn_item_id'];
			}

			if($form['is_recheck']){
				$title = "title=\"".$r['sku_item_code']." is new SKU item\"";
				$bg_color = "#AFC7C7";
				$mo_bg_color = "#CFECEC";
				$temp['data']['sid'] = $sid;
				//$temp['data']['pcs'] = $form['new_qty'];
				//$r['pcs'] = $form['new_qty'];
				$r['from_isi'] = $form['is_recheck'];
			}else{
				$mo_bg_color = "#ffffcc";
			}

			$smarty->assign("form", $form);
			$r['ctn'] = 0;
			$smarty->assign("item", $r);
            
			$temp['data']['id'] = $r['id'];
			$temp['data']['doc_type'] = $doc_type;
			$temp['data']['sku_item_code'] = $r['sku_item_code'];
			$temp['data']['fraction'] = $r['uom_fraction'];
			$temp['data']['packing_uom_fraction'] = $r['packing_uom_fraction'];
			$temp['data']['is_bom'] = $r['is_bom'];
			if($sku_info_arr[$sid]['is_bom_item']) $temp['data']['is_bom_item'] = $sku_info_arr[$sid]['is_bom_item'];
			else $temp['data']['is_bom_item'] = 0;
			$temp['data']['grn_barcode_type'] = $grn_barcode_type;

			if($sku_info_arr[$sid]['pcs'] > 0){
				if ($r['uom_fraction'] == 1){
					$temp['data']['pcs'] = $sku_info_arr[$sid]['pcs'];
				}elseif ($r['uom_fraction'] > 1){
					$temp['data']['pcs'] = $sku_info_arr[$sid]['pcs'] % $r['uom_fraction'];
					$temp['data']['ctn'] = ($sku_info_arr[$sid]['pcs'] - $temp['data']['pcs']) / $r['uom_fraction'];
				}
			}
            
            $temp['data']['sell'] = $r['selling_price'];
			$temp['data']['cost'] = $r['cost'];
			$temp['data']['dad'] = $r['doc_allow_decimal'];
			$temp['data']['si_count'] = $si_count;
			if($err_msg) $temp['data']['error'] = join("\n", $err_msg);
			//$temp['data']['is_double_sku'] = 1;
			$smarty->assign("doc_type", $doc_type);
			$smarty->assign("is_pi", 1);
			$temp['html'] = "<tr id=\"".$doc_type."_titem".$r['id']."\" bgcolor=\"$bg_color\" onmouseout=\"this.bgColor='$bg_color';\" onmouseover=\"this.bgColor='$mo_bg_color';\" $title>".$smarty->fetch("goods_receiving_note2.new.list.tpl")."</tr>";
			$ret[] = $temp;
		}
		
		print json_encode($ret);
	}else{ // item not in ARMS
		$temp['data']['doc_type'] = 4;
		if($grn_barcode){  // add invalid sku item from barcode
			$r['sku_item_code'] = $grn_barcode;
			$r['id'] = $grn_barcode;
		}else{ // add invalid sku item from add sku
			$r['sku_item_code'] = $form['sku'];
			$r['id'] = $form['sku'];
		}

		$doc_type = 4;
		$smarty->assign("item", $r);
		$smarty->assign("doc_type", 4);
		$temp['html'] = "<tr id=\"".$doc_type."_titem".$r['id']."\" bgcolor=\"$bg_color\" onmouseout=\"this.bgColor='$bg_color';\" onmouseover=\"this.bgColor='$mo_bg_color';\" $title>".$smarty->fetch("goods_receiving_note2.new.list.tpl")."</tr>";
		
		$ret[] = $temp;
		print json_encode($ret);
	}
}

function ajax_add_variance_item($grn_id, $branch_id){
    global $con, $smarty, $sessioninfo, $appCore;
    
    $form = $_REQUEST;
    $sid = $form['sid'];
    $doc_type = $form['doc_type'];
	$r = get_items_details('', $form['parent_id']);
	$r['item_check'] = 1;
	$r['item_group'] = $doc_type;
	$r['is_return'] = 1;
	$r['po_qty'] = 0;
	$r['po_cost'] = 0;
	$r['po_item_id'] = 0;
    $grr['type'] = 'PO';
	$r['pcs'] = $form['variance'];
    
	add_temp_item($r, $grn_id);

    $smarty->assign("item", $r);
    $temp['data']['id'] = $r['id'];
    $temp['data']['doc_type'] = $doc_type;

    $smarty->assign("doc_type", $doc_type);
    $smarty->assign("grr", $grr);

  	$temp['html'] = "<tr id=\"".$doc_type."_titem".$r['id']."\" onmouseout=\"this.bgColor='';\" onmouseover=\"this.bgColor='#ffffcc';\">".$smarty->fetch("goods_receiving_note2.new.list.tpl")."</tr>";
  	$ret[] = $temp;
  	print json_encode($ret);
}

function ajax_add_return_item($grn_id, $branch_id){
    global $con, $smarty, $sessioninfo;
    
    $form = $_REQUEST;
    $smarty->assign("grr", $form);
    $sid = $form['id'];
	$r = get_items_details('', $sid);
	$r['item_check'] = 1;
	$r['item_group'] = 3;
	$r['is_return'] = 1;
	$r['po_qty'] = 0;
	$r['po_cost'] = 0;
	$r['po_item_id'] = 0;
	$r['pcs'] = $form['qty_var'];
    
	add_temp_item($r, $grn_id);

    $smarty->assign("item", $r);
    $temp['data']['id'] = $r['id'];
    $temp['data']['doc_type'] = 3;

    $smarty->assign("doc_type", 3);

  	$temp['html'] = "<tr id=\"3_titem".$r['id']."\" onmouseout=\"this.bgColor='';\" onmouseover=\"this.bgColor='#ffffcc';\">".$smarty->fetch("goods_receiving_note2.new.list.tpl")."</tr>";
  	$ret[] = $temp;
  	print json_encode($ret);
}

function ajax_recheck_nsi($grn_id, $branch_id){
    global $con, $smarty, $sessioninfo;
    
    $form = $_REQUEST;
    $smarty->assign("grr", $form);
	$code_error = array();
	$count = 0;

	if($form['4_sku_item_code']){
		foreach($form['4_sku_item_code'] as $k=>$dummy){
			if($form['item_code']){ // if found user recheck for single code and cannot found it thru looping, skip it
				if($k != $form['item_code']) continue;
			}
			$temp = array();

			$con->sql_query("select si.* 
							 from sku_items si
							 left join sku on sku.id = si.sku_id
							 where si.mcode = ".ms($k)."
							 or (si.artno = ".ms($k)." and sku.vendor_id = ".mi($form['vendor_id']).")");
			
			if($con->sql_numrows() == 1){
				$t = $con->sql_fetchrow();
			}elseif($con->sql_numrows() > 1){
				while($t = $con->sql_fetchrow()){
					$t['code'] = $k;
					$t['cost'] = $form['4_cost'][$k];
					$t['pcs'] = $form['4_pcs'][$k];
					$sku_list[] = $t;
				}
				continue;
			}else{
				$code_error[] = $k;
				continue;
			}

			$doc_type = check_sku_item_type($grn_id, $t['id'], $branch_id, $form['type']);

			if($doc_type == 2){
				$r = get_parent_info($grn_id, $t['id'], $branch_id);

				$doc_type = 1;  // combine document type 2 become 1
				$is_cf_d2 = 1;
			}else{
				$r = get_items_details(intval($t['id']), '');
				$r['item_group'] = $doc_type;
				if($doc_type == 3){
					$r['po_item_id'] = 0;
					$r['po_qty'] = 0;
					$r['po_cost'] = 0;
				}
			}

			$r['cost'] = mf($form['4_cost'][$k]); // took from user input
			$r['pcs'] = mi($form['4_pcs'][$k]); // took from user input
			$r['from_isi'] = 1;

			$item_id = add_temp_item($r, $grn_id);

			if($r['item_group'] == 2) $r = get_items_details('', $item_id);

			$smarty->assign("form", $form);
			$smarty->assign("item", $r);

			$temp['data']['id'] = $r['id'];
			$temp['data']['doc_type'] = $doc_type;
			$temp['data']['code'] = $k;

			$smarty->assign("doc_type", $doc_type);
			$smarty->assign("is_pi", 1);

			$temp['html'] = "<tr id=\"".$doc_type."_titem".$r['id']."\" bgcolor=\"#AFC7C7\" onmouseout=\"this.bgColor='#AFC7C7';\" onmouseover=\"this.bgColor='#CFECEC';\" title=\"".$r['sku_item_code']." is new SKU item\">".$smarty->fetch("goods_receiving_note2.new.list.tpl")."</tr>";
			$ret[] = $temp;
			
			if($form['item_code']){ // if found user recheck for single code and found it, terminate the foreach
				if($k == $form['item_code']) break;
			}
		}
	}else{
		$temp['error'] = "No items to recheck";
		$ret[] = $temp;
	}
	
	// print error for the list of codes
	if(count($code_error) > 0){
		if($form['item_code'])
			$temp['code_err'] = "Cannot find Code [".$form['item_code']."] from database.";
		else
			$temp['code_err'] = "Cannot find below Code(s) from database: \n\n".join("\n", $code_error);
		$ret[] = $temp;
	}
	
	// print the list of SKU items menu
	if(count($sku_list) > 0){
		$smarty->assign("items", $sku_list);
		$temp['sku_list'] = 1;
		$smarty->assign("is_recheck", $form['is_recheck']);
		$smarty->assign("grn_barcode", $grn_barcode);
		$temp['html'] = $smarty->fetch("goods_receiving_note.nsi_list.tpl");
		$ret[] = $temp;
	}
	
	print json_encode($ret);
}

function grr_validate_data(&$form){

	global $con, $sessioninfo, $LANG, $config;

	$err = $doc_used = $doc_date_list = array();

	$invalid_ibt = grr_ibt_validation();
	if($invalid_ibt) $err['top'][] = $invalid_ibt;

	$department_id = 0;
	foreach ($form['grr_item_id'] as $n=>$dummy){
		if ($form['doc_no'][$n]!=''){
			$item['id'] = $form['grr_item_id'][$n];
			$item['curr_po_no'] = $form['curr_po_no'][$n];
			$item['doc_no'] = $form['doc_no'][$n];
			$item['doc_date'] = $form['doc_date'][$n];
			$item['type'] = $form['type'][$n];
			$item['ctn'] = $form['ctn'][$n];
			$item['pcs'] = $form['pcs'][$n];
			$item['amount'] = $form['amount'][$n];
			$item['gst_amount'] = $form['gst_amount'][$n];
			$item['gst_id'] = $form['gst_id'][$n];
			$item['gst_code'] = $form['gst_code'][$n];
			$item['gst_rate'] = $form['gst_rate'][$n];
			$item['tax'] = $form['tax'][$n];
			$item['remark'] = $form['remark'][$n];
			// make sure documents are not duplicated
			if (!isset($doc_used[$form['doc_no'][$n]][$form['type'][$n]][$form['gst_id'][$n]])) $doc_used[$form['doc_no'][$n]][$form['type'][$n]][$form['gst_id'][$n]] = 1;
			else $err[$n][]=sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], "this GRR");

			// question: want to separate by branch??
			$id = intval($form['grr_item_id'][$n]);
			
			if ($form['type'][$n] != 'PO'){
				if($form['is_under_gst']) $extra_filter = "and gi.gst_id = ".mi($form['gst_id'][$n]);
				$q1 = $con->sql_query("select grr_id, gi.id 
									   from grr_items gi
									   left join grr on gi.grr_id = grr.id and grr.branch_id=gi.branch_id
									   where gi.id <> ".mi($id)." and grr.branch_id = ".mi($form['branch_id'])." and grr.vendor_id = ".mi($form['vendor_id'])." and gi.doc_no = ".ms($form['doc_no'][$n])." and gi.type = ".ms($form['type'][$n])." and grr.active=1 $extra_filter");

				if ($con->sql_numrows($q1)>0){
				    $r = $con->sql_fetchassoc($q1);
					$err[$n][] = sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], $r['grr_id']);
				}
				$con->sql_freeresult($q1);
				
				// check if having different document date for same document no
				if(!$form['doc_date'][$n]){
					$err[$n][] = sprintf($LANG['GRR_EMPTY_DOC_DATE'], $form['type'][$n], $form['doc_no'][$n]);
				}if($doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] && $doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] != $form['doc_date'][$n]){
					$err[$n][] = sprintf($LANG['GRR_INVALID_DOC_DATE'], $form['type'][$n], $form['doc_no'][$n]);
				}else $doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] = $form['doc_date'][$n];
			}else{ 			// make sure the PO exist
				$q1 = $con->sql_query("select id, active, vendor_id, branch_id, po_branch_id, partial_delivery, delivered, department_id, cancel_date, po_no 
									   from po 
									   where approved=1 and po_no = ".ms($form['doc_no'][$n]));
				$p = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				if(is_array(unserialize($p['cancel_date']))){
					$p['cancel_date'] = unserialize($p['cancel_date']);
					foreach($p['cancel_date'] as $bid=>$cd){
						if(!$cd) continue;
						$cancel_date = $cd;
						break;
					}
				}else $cancel_date = $p['cancel_date'];
				
				if (!$p){
					$reset_doc_no=search_pp_pono($form['doc_no'][$n], $p);
					if($reset_doc_no) $form['doc_no'][$n]=$reset_doc_no;						
				}

				if(!$p){
				    $err[$n][] = sprintf($LANG['GRR_PO_NOT_FOUND'],$form['doc_no'][$n]);
				}elseif(!$p['active']){ // PO is inactive. prompt PO was Cancelled
					$err[$n][] = sprintf($LANG['GRR_PO_INACTIVE'],$form['doc_no'][$n]);
				}else{
					$form['po_id'][$n] = $p['id'];
					$form['po_branch_id'][$n] = $p['branch_id'];

					if ($p['vendor_id'] != $form['vendor_id'])
					    $err[$n][] = $LANG['GRR_VENDOR_DIFFERENT_FROM_PO'];

					if(($p['po_branch_id']>0 && $p['po_branch_id'] != $form['branch_id']) ||($p['po_branch_id']==0 && $p['branch_id'] != $form['branch_id']))
						$err[$n][] = $LANG['GRR_INVALID_RECEIVING_BRANCH'];

					if ($p['delivered'] && !$p['partial_delivery'])
					{
						if ($form['grr_id']==0) // error if new grr gets re-deliver
					    	$err[$n][] = $LANG['GRR_PO_DELIVERED'];
					    elseif ($form['curr_po_no'][$n]!=$p['po_no']) // error if doc number is different
					    	$err[$n][] = $LANG['GRR_PO_DELIVERED'];
					}
					if (strtotime($form['rcv_date']) >= dmy_to_time($cancel_date))
					    $err[$n][] = sprintf($LANG['GRR_PO_CANNOT_RECEIVE_UPON_CANCEL_DATE'], $cancel_date);

					if ($form['department_id'] != $p['department_id'])
					    $err[$n][] = $LANG['GRR_PO_FROM_DIFFERENT_DEPARTMENT'];
				}
			}
			$form['grr_items'][] = $item;
		}
	}

	// newly enhance, to make sure user key in at least one document for inv, do or other when found is grn future
	if($config['use_grn_future']){
		$type = "|".join("|", $form['type']);
		$have_do = 0;
		$have_inv = 0;
		$have_other = 0;

		if(preg_match("{DO}", $type)){
			$have_do = 1;
		}
		if(preg_match("{INVOICE}", $type)){
			$have_inv = 1;
		}
		if(preg_match("{OTHER}", $type)){
			$have_other = 1;
		}
		
		if(!$have_do && !$have_inv && !$have_other) $err['top'][] = $LANG['GRR_INVALID_DOCUMENT'];
	}
	
	return $err;
}

function grr_ibt_validation(){
	global $con, $LANG;

	$form = $_REQUEST;
	$is_ibt = $non_ibt = 0;

	foreach ($form['grr_item_id'] as $n=>$dummy){
		if($form['doc_no'][$n]!=''){
			if($form['type'][$n] != "DO" && $form['type'][$n] != "PO") continue;
			/*
			search grr item doc no either is below statement:
			GRR doc_type = "DO" + GRR doc_no = DO do_no, update grn column is_ibt = 1
			GRR doc_type = "PO" + GRR doc_no = po po_no, update grn column is_ibt = 1
			*/

			if($form['type'][$n] == "DO"){
				$sql = $con->sql_query("select * from do where do_no = ".ms($form['doc_no'][$n])." and do_branch_id = ".mi($form['branch_id']));
			}elseif($form['type'][$n] == "PO"){
				$sql = $con->sql_query("select * from po where po_no = ".ms($form['doc_no'][$n])." and po_branch_id = ".mi($form['branch_id'])." and is_ibt = 1");
			};

			if($con->sql_numrows($sql) > 0){
				$is_ibt = 1;
			}else $non_ibt = 1;

			$con->sql_freeresult($sql);
		}
		if($is_ibt && $non_ibt) break; // stop the loop and rdy to display error msg
	}

	// found if having both IBT and non IBT in one GRR then display error msg
	if($is_ibt && $non_ibt) $err = $LANG['GRR_IBT_ERROR'];
	return $err;
}

function search_pp_pono($original_docno, &$ret){
	global $con, $smarty, $sessioninfo, $reset_doc_no ;

	if (preg_match("/^([A-Z]+)(\d+)\(PP\)$/", $original_docno, $matches)){
		$pp_repor_prefix=$matches[1];
		$pp_po_id=$matches[2];
		
		if($pp_repor_prefix=='HQ'){
			$q1=$con->sql_query("select po_no from po where hq_po_id=$pp_po_id  and po_branch_id=$sessioninfo[branch_id]");
			$r1 = $con->sql_fetchrow($q1);		
		}
		else{
			$q0=$con->sql_query("select id from branch where report_prefix=".ms($pp_repor_prefix));
			$r0 = $con->sql_fetchrow($q0);
			$pp_branch_id=$r0['id'];
			
			$q1=$con->sql_query("select po_no from po where branch_id=$pp_branch_id  and id=$pp_po_id");
			$r1 = $con->sql_fetchrow($q1);		
		}		

		if($r1){
			$reset_doc_no=$r1['po_no'];
		}			
		
	}	
	$con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,delivered,department_id,cancel_date,po_no from po where approved=1 and po_no =".ms($reset_doc_no));		
	$ret = $con->sql_fetchrow();
	
	return $reset_doc_no;
}

function ajax_validate_dn_report(){
	global $con;

	$form = $_REQUEST;
	
	$q1=$con->sql_query("select gi.*,
						 gi.selling_price, si.sku_id, si.mcode, si.link_code,
						 si.sku_item_code, si.description, u1.code as rcv_uom, 
						 u1.fraction as uom_fraction, si.artno, si.packing_uom_id as master_uom_id,
						 u1.code as uom_code, u1.code as order_uom
						 from grn
						 left join grn_items gi on gi.grn_id = grn.id and gi.branch_id = grn.branch_id
						 left join sku_items si on gi.sku_item_id = si.id
						 left join sku on sku.id = si.sku_id 
						 left join uom u1 on gi.uom_id = u1.id
						 where gi.grn_id=".mi($form['id'])."
						 and gi.branch_id=".mi($form['branch_id'])." 
						 and (gi.acc_cost is not null or grn.buyer_adjustment != 0)
						 order by gi.id");

	if($con->sql_numrows($q1) == 0){
		print "no";
	}
	$con->sql_freeresult($q1);
}

function is_item_existed($sid, $si_info, $use_tmp=false){
	global $con, $id, $branch_id;
	
	$form = $_REQUEST;
	if($use_tmp) $tbl = "tmp_grn_items";
	else $tbl = "grn_items";
	
	$filter = array();
	if(isset($si_info['selling_price'])) $filter[] = "tgi.selling_price = ".mf($si_info['selling_price']);
	if(isset($si_info['cost_price'])) $filter[] = "tgi.cost = ".mf($si_info['cost_price']);
	
	if($filter) $filters = " and ".join(" and ", $filter);
	
	$q1 = $con->sql_query("select tgi.id, si.sku_item_code, tgi.item_group
						   from $tbl tgi 
						   left join sku_items si on si.id = tgi.sku_item_id
						   where tgi.sku_item_id = ".mi($sid)."
						   and tgi.grn_id = ".mi($id)." and tgi.branch_id = ".mi($branch_id)." and tgi.item_group >= 3".$filters);
						   
	if($con->sql_numrows($q1) > 0){
		$gi = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		return $gi;
	}
}

function check_tmp_item_exists(){
	global $con, $sessioninfo,$branch_id, $LANG;
	
	$tmp_item_id = array();
    
    //for confirm GRR and saved GRN
	for ($i=0; $i<=3; $i++) { //doc type from 1-3
		if ($_REQUEST[$i.'_sku_item_code']) {
			$item_id_list = array_keys($_REQUEST[$i.'_sku_item_code']);

			foreach($item_id_list as $doc_type=>$item_id){
				$tmp_item_id[$item_id] = $item_id;
			}
		}
	}
	
    //for Confirm account verification
    if($_REQUEST['item_id']) {
        for($j=1; $j<count($_REQUEST['item_id']); $j++) {
            $tmp_item_id[] = $_REQUEST['item_id'][$j];
        }
    }
    
	if ($tmp_item_id) {
		$sql = "select count(*) as c from tmp_grn_items where id in (".join(',',$tmp_item_id).") and branch_id = $branch_id limit 1";//die($sql);
		$con->sql_query($sql);

		if ($con->sql_fetchfield('c') == count($tmp_item_id)) print 'OK';
		else print $LANG['DOCUMENT_SAVED_IN_OTHER_TAB'];
		$con->sql_freeresult();
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}

function ajax_generate_dn(){
	global $con, $smarty, $sessioninfo, $branch_id;
	
	$form = $_REQUEST;
	if(!$form['branch_id']) $form['branch_id'] = $branch_id;
	$is_saved = grn_save($form['id'], $form['branch_id'], "save", 1);
	
	$ret = array();
	if($is_saved){ // if found grn have been saved, proceed to generate
		$prms = array();
		$prms['grn_id'] = $form['id'];
		$prms['branch_id'] = $form['branch_id'];
		$prms['need_print'] = 0;
		$prms['is_generate'] = 1;
		generate_dn_from_grn($prms);

		$ret['ok'] = 1;
		//grn_open($id, $branch_id);
	}
	
	if(!$ret['ok']) $ret['failed_reason'] = "unable to save GRN!";
	
	print json_encode($ret);
}

function fix_po_delivered(){
	$po_no = trim($_REQUEST['po_no']);
	if(!$po_no)	die("No PO No.");
	update_po_receiving_count($po_no);
	print "Fixed $po_no";
}

//download csv sample
function download_sample_grn(){
	global $headers, $sample;
	
	header("Content-type: application/msexcel");
	header("Content-Disposition: attachment; filename=sample_import_grn_item.csv");
	
	print join(",", array_values($headers[1]));
	foreach($sample[1] as $sample) {
		$data = array();
		foreach($sample as $d) {
			$data[] = $d;
		}
		print "\n\r".join(",", $data);
	}
}

function ajax_open_csv_popup(){
	global $smarty, $headers, $sample;
	
	//create file if not exist
	if (!is_dir("attachments"))	check_and_create_dir("attachments");
	if (!is_dir("attachments/import_grn_items"))	check_and_create_dir("attachments/import_grn_items");
	
	$form=$_REQUEST;
	$smarty->assign("form", $form);
	$smarty->assign("sample_headers", $headers);
	$smarty->assign("sample", $sample);
	
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('goods_receiving_note2.upload_csv.tpl');
	print json_encode($ret);
}


function show_result(){
	global $con, $smarty, $config, $headers, $sample, $sessioninfo, $LANG;
	
	$form = $_REQUEST;
	$id= mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$file = $_FILES['import_csv'];
	
	$f = fopen($file['tmp_name'], "rt");
	$line = fgetcsv($f);
	
	$item_lists = $code_list = array();
	if(count($line) == count($headers[$form['method']])) { 
		$item_list = array();
		while($r = fgetcsv($f)){
			$error = array();
			$result['ttl_row']++;
			
			foreach($r as $tmp_row => $val){
				$r[$tmp_row] = utf8_encode(trim($val));
			}
			
			$ins = array();
			switch($form['method']) {
				case '1':
					$ins['item_code'] = trim($r[0]);
					$ins['cost'] = mf($r[1]);
					$ins['qty'] = mf($r[2]);
					break;
			}
			
			if($ins['item_code']) {
				$con->sql_query("select id, doc_allow_decimal from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
				$item_info = $con->sql_fetchassoc();
				$count = $con->sql_numrows();
				$con->sql_freeresult();
				
				if($count <= 0 && strlen($ins['item_code']) == 13){
					$ins['item_code'] = substr($ins['item_code'], 0, 12);
					$con->sql_query("select id from sku_items where active=1 and (sku_item_code = ".ms($ins['item_code'])." or mcode = ".ms($ins['item_code'])." or link_code = ".ms($ins['item_code'])." or artno = ".ms($ins['item_code']).")");
					$item_info = $con->sql_fetchassoc();
					$count = $con->sql_numrows();
					$con->sql_freeresult();
				}
				
				$sku_item_id = mi($item_info['id']);
				$doc_allow_decimal = $item_info['doc_allow_decimal'];
				if(!$sku_item_id) $error[] = 'Item Code('.$ins['item_code'].') not found';
				if($count > 1)  $error[] = 'Item Code('.$ins['item_code'].') match result more than 1';
				
				
				if($sku_item_id && $count == 1){
					//get order cost
					if($ins['cost']){
						$cost = $ins['cost'];
					}else{
						$item_details = get_items_details($sku_item_id, '');
						$cost = $item_details['cost_price'];
					}
					
					$con->sql_query("select * from tmp_grn_items where sku_item_id=$sku_item_id and grn_id=$id and branch_id=$branch_id and user_id=".mi($sessioninfo['id']));
					$duplicate_row2 = $con->sql_numrows();
					$tmp_grn_info = $con->sql_fetchassoc();
					$con->sql_freeresult();
					if($form['allow_duplicate']){
						if($duplicate_row2 > 0 && $tmp_grn_info['cost'] != $cost){
							$error[] = "Cannot duplicate difference Cost with same item";
						}
					}else{
						if($duplicate_row2 > 0)  $error[] = "Item Code(".$ins['item_code'].") is duplicated";
					}
					
					//check duplicate
					if(in_array($sku_item_id, array_keys($item_list))){
						if($form['allow_duplicate'] && $item_list[$sku_item_id]!= $cost)  $error[] = "Cannot duplicate difference Cost with same item";
						if(!$form['allow_duplicate'])  $error[] = "Item Code(".$ins['item_code'].") is duplicated";
					}else{
						$item_list[$sku_item_id] = $cost;
					}
					
					//check for block item
					$q1 = $con->sql_query("select doc_block_list, block_list, active from sku_items where id =$sku_item_id");
					$tmp_si_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);

					if($config['check_block_grn_as_po']) $block_list_array = unserialize($tmp_si_info['block_list']);
					else $block_list_array = unserialize($tmp_si_info['doc_block_list']);
					$active = $tmp_si_info['active'];

					if ($block_list_array) {
						if($config['check_block_grn_as_po']) $in_block_list = isset($block_list_array[$branch_id]);
						else $in_block_list = isset($block_list_array['grn'][$branch_id]);
						
						if ($in_block_list) $error[] = sprintf($LANG['DOC_ITEM_IS_BLOCKED'], "GRN");
					}elseif(!$active){ // is inactive item
						$error[] =$LANG['PO_ITEM_IS_INACTIVE'];
					}
					
					//check sku item doc_allow_decimal
					if($doc_allow_decimal){
						$ins['qty'] = round($ins['qty'], $config['global_qty_decimal_points']);
					}else{
						$ins['qty'] = mi($ins['qty']);
					}
				}
			}else   $error[] = "Empty Item Code";
			
			if($ins['qty'] == 0){
				$error[] = "Invalid Qty";
			}
			
			$error = array_unique($error);
			if($error)	$ins['error'] = join(', ', $error);
			
			$item_lists[] = $ins;
			
			if($ins['error'])	$result['error_row']++;
			else				$result['import_row']++;
		}
		
		if($item_lists){
			$header = $headers[$form['method']];
			if($result['error_row'] > 0)	$header[] = 'Error';
			
			$file_name = "grn_".time().".csv";
			
			$fp = fopen("attachments/import_grn_items/".$file_name, 'w');
			fputcsv($fp, array_values($header));
			foreach($item_lists as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			chmod("attachments/import_grn_items/".$file_name, 0777);
			
			print "<script>parent.window.GRN_UPLOAD_CSV.ajax_show_result('$file_name', '');</script>";
		}else{
			print "<script>parent.window.GRN_UPLOAD_CSV.ajax_show_result('', 'No data found on the file.');</script>";
		}
	}else {
		print "<script>parent.window.GRN_UPLOAD_CSV.ajax_show_result('', 'Column not match. Please re-check import file.');</script>";
	}
}

function ajax_get_uploaded_csv_result(){
	global $smarty, $headers, $sample;
	
	$form = $_REQUEST;
	if(!$form['file_name'] || !file_exists("attachments/import_grn_items/".$form['file_name'])){
		die("File no found.");
		exit;
	}
	
	$f = fopen("attachments/import_grn_items/".$form['file_name'], "rt");
	$line = fgetcsv($f);
	
	if(in_array('Error', $line))  $error_index = array_search("Error", $line);
	else  $error_index = count($line);
	
	$item_lists = $result = array();
	$num_row = 0;
	while($r = fgetcsv($f)){
		$result['ttl_row']++;
		foreach($r as $tmp_row => $val){
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		
		$data_list = array();
		switch ($form['method']) {
			case '1':
				$data_list['item_code'] = $r[0];
				$data_list['cost'] = $r[1];
				$data_list['qty'] = $r[2];
				if(!$r[$error_index]) $result['import_row']++;
				else{
					$data_list['error'] = $r[3];
					$result['error_row']++;
				}
				break;
		}
		$item_lists[] = $data_list;
	}
	
	$ret = array();
	if($item_lists){
		$header = $headers[1];
		if($result['error_row'] > 0)	$header[] = 'Error';
		
		$smarty->assign("result", $result);
		$smarty->assign("file_name", $form['file_name']);
		$smarty->assign("item_header", array_values($header));
		$smarty->assign("item_lists", $item_lists);
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('goods_receiving_note2.upload_csv.result.tpl');
	}else{
		die("Result not found.");
	}	
	
	print json_encode($ret);
}

function ajax_import_grn(){
	global $con, $smarty, $headers, $sessioninfo, $appCore, $config;
	
	$form = $_REQUEST;
	$grn_id= mi($form['id']);
	$branch_id = mi($form['branch_id']);
	$item_checked = $form['grn_tmp_item'];
	
	if(!$form['file_name'] || !file_exists("attachments/import_grn_items/".$form['file_name'])){
		die('File not found.');
	}
	
	$f = fopen("attachments/import_grn_items/".$form['file_name'], "rt");
	$line = fgetcsv($f);
	if(in_array('Error', $line)) {
		$error_index = array_search("Error", $line);
	}else{
		$error_index = count($line);
	}
    
	$error_list = $row_item = array();
	$num_row = $i = 0;
	while($r = fgetcsv($f)){
		$i++;
		foreach($r as $tmp_row => $val){
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		
		switch ($form['method']) {
			case '1':
				if(!$r[$error_index] && in_array($i, $item_checked)) {
					if($r[0]) {
						$con->sql_query("select * from sku_items where active=1 and (sku_item_code=".ms($r[0])." or mcode=".ms($r[0])." or link_code=".ms($r[0])." or artno=".ms($r[0]).") limit 1");
						
						$item_info = $con->sql_fetchassoc();
						$con->sql_freeresult();
					}
					$sku_item_id = mi($item_info['id']);
					$con->sql_query("select * from tmp_grn_items where sku_item_id =$sku_item_id and grn_id =$grn_id and branch_id=$branch_id and user_id = $sessioninfo[id]");
					$count = $con->sql_numrows();
					$exist_grn_info = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$num =0;
					if($count > 0){
						$con->sql_query("update tmp_grn_items set pcs=pcs+".mf($r[2])." where sku_item_id =$sku_item_id and grn_id =$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
						$num = $con->sql_affectedrows();
						
						$temp = array();
						$temp['duplicate'] = true;
						$temp['existed_item_id'] = $exist_grn_info['id'];
						$temp['item_group'] = $exist_grn_info['item_group'];
						$temp['pcs'] = $r[2];
						$row_item[] = $temp;
					}else{
						$ins = array();
						$doc_type = 3;
						
						$item_details = get_items_details($sku_item_id, '');
						$new_id = $appCore->generateNewID("tmp_grn_items", "branch_id = ".mi($branch_id));
						
						$ins['id'] = $new_id;
						$ins['branch_id'] = $branch_id;
						$ins['grn_id'] = $grn_id;
						$ins['user_id'] = $sessioninfo['id'];
						$ins['sku_item_id'] = $sku_item_id;
						$ins['artno_mcode'] = $item_info['artno'] ? $item_info['artno'] : $item_info['mcode'];
						$ins['uom_id'] = $item_details['uom_id'];
						$ins['cost'] = ($r[1] > 0) ? $r[1] : $item_details['cost_price'];
						$ins['selling_uom_id'] = 1;
						$ins['selling_price'] = $item_details['selling_price'];
						$ins['pcs'] = $r[2];
						$ins['item_group'] = $doc_type;
						$ins['selling_gst_id'] = $item_details['selling_gst_id'];
						$ins['selling_gst_code'] = $item_details['selling_gst_code'];
						$ins['selling_gst_rate'] = $item_details['selling_gst_rate'];
						$ins['gst_selling_price'] = $item_details['gst_selling_price'];
						
						if($config['sku_bom_additional_type'] && $item_details['is_bom']){
							$sql = $con->sql_query("select *, si.sku_item_code
													from bom_items bi
													left join sku_items si on si.id = bi.bom_id
													left join sku on sku.id = si.sku_id 
													where bi.bom_id=$sku_item_id and sku.is_bom = 1 and si.bom_type = 'package'");
							
							while($r2 = $con->sql_fetchassoc($sql)){
								$ins['bom_ref_num'] = $r2['bom_id'];
								$ins['bom_qty_ratio'] = $r2['qty'];
							}
							$con->sql_freeresult($q1);
						}
						
						$con->sql_query("insert into tmp_grn_items ".mysql_insert_by_field($ins));
						$num = $con->sql_affectedrows();

						//pass data to tpl file
						$item = array_merge($item_details, $ins);
						$smarty->assign("grr", $form);
						$smarty->assign("form", $form);
						$smarty->assign("item", $item);
						$smarty->assign("doc_type", $doc_type);
						$smarty->assign("is_pi", 1);
						$_REQUEST['action'] = 'edit';
						
						if($form['is_recheck']){
							$title = "title=\"".$r['sku_item_code']." is new SKU item\"";
							$bg_color = "#AFC7C7";
							$mo_bg_color = "#CFECEC";
							$temp['data']['sid'] = $sid;
							$r['from_isi'] = $form['is_recheck'];
						}else{
							$mo_bg_color = "#ffffcc";
						}
						
						$tpl = $smarty->fetch("goods_receiving_note2.new.list.tpl");
						$tmp['doc_type'] = $doc_type;
						$tmp['rowdata'] = "<tr id=\"".$doc_type."_titem".$new_id."\" bgcolor=\"$bg_color\" onmouseout=\"this.bgColor='$bg_color';\" onmouseover=\"this.bgColor='$mo_bg_color';\" $title>".$tpl."</tr>";
						$row_item[] = $tmp;
					}
					
					if ($num > 0)	$num_row++;
				}else{
					if($r[$error_index]) $error_list[] = $r;
				}
				break;
		}
	}
        
	if($error_list) {
		$fp = fopen("attachments/import_grn_items/invalid_".$form['file_name'], 'w');
		fputcsv($fp, array_values($line));
		
		foreach($error_list as $r){
			fputcsv($fp, $r);
		}
		fclose($fp);
		
		chmod("attachments/import_grn_items/invalid_".$form['file_name'], 0777);
	}
	
	$ret = array();
	$ret['ok'] = 1;
	if ($num_row > 0) {
		$ret['html'] = $row_item;
		$ret['file'] = '';
		if($error_list){
			$ret['file'] = "invalid_".$form['file_name'];
		}
		$ret['msg'] = "$num_row item(s) added.";
	}else{
		$ret['msg'] = "Failed to add GRN items.";
	}
	
	print json_encode($ret);
}

//export grn item
function export_grn_item(){
	global $con, $smarty, $sessioninfo, $appCore, $config;
	
	$got_item = false;
	$form = $_REQUEST;
	$branch_id= mi($form['branch_id']);
	$id= mi($form['id']);
	
	//header
	$link_code_name = $config['link_code_name'] ? $config['link_code_name'] : 'Link Code';
	$header_array = array('ARMS Code', 'Mcode', 'Art-no', $link_code_name, 'UOM', 'ctn', 'pcs', 'cost');
	
	//select report prefix from branch
	$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
	$prefix=$con->sql_fetchrow();
	$con->sql_freeresult();
	$report_prefix = $prefix['report_prefix'];
	
	$formatted=sprintf("%05d",$id);
	$document_no = $report_prefix.$formatted;
	
	$filename = 'grn_export_'.$document_no.'.csv';
	$fp = fopen($filename, 'w');
	
	if($branch_id && $id){
		$sql = "select si.sku_item_code, si.mcode, si.artno, si.link_code, uom.code as code, gi.ctn, gi.pcs, gi.cost 
		from grn_items gi 
		left join sku_items si on si.id = gi.sku_item_id
		left join uom on uom.id = gi.uom_id
		where gi.grn_id=$id and gi.branch_id=$branch_id
		order by gi.id";
		$q1 = $con->sql_query($sql);
		if ($con->sql_numrows($q1)>0) {
			fputcsv($fp, $header_array);
			$got_item = true;
			while($r = $con->sql_fetchassoc($q1)){
				$arr = array();
				$arr[] = $r['sku_item_code'];
				$arr[] = $r['mcode'];
				$arr[] = $r['artno'];
				$arr[] = $r['link_code'];
				$arr[] = $r['code'];
				$arr[] = $r['ctn'];
				$arr[] = $r['pcs'];
				$arr[] = $r['cost'];
				fputcsv($fp, $arr);
			}
		}
		$con->sql_freeresult($q1);
		fclose($fp);
	}
	
	if ($got_item) {
		log_br($sessioninfo['id'], 'GRN', $id, "Export GRN Items to CSV File");
		header('Content-Type: application/msexcel');
		header('Content-Disposition: attachment;filename='.$filename);
		print file_get_contents($filename);
	}
	unlink($filename);
	
	if (!$got_item){
		js_redirect("No GRN items data.", $_SERVER['PHP_SELF']);
	}
	exit;
}
?>
