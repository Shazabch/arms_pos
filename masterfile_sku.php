<?php
/*Revision History
----------------------
6/13/2007 5:50:09 PM gary
-added block list (by branch) for each SKU.
- in save_sku() and load_sku().

8/21/2007 6:33:19 PM yinsee
- add sku image updating

10/31/2007 10:27:40 AM yinsee
- shorten the SQL to calculate total GRN items qty

11/19/2007 9:57:28 AM gary
- add do and adjustment stock.

11/19/2007 10:50:26 AM gary
- moving SKU function. (in tracker)

11/20/2007 1:34:51 PM gary
- add exclude cancel in sql condition for do, adj and grn.

11/23/2007 4:41:19 PM gary
- fix the bug when moving sku.

1/7/2008 10:15:35 AM yinsee
- admin can see all item (important to show uncategorize)

1/11/2008 3:25:53 PM yinsee
- remove block_list from sku table (only needed in sku_items)

1/16/2008 5:24:57 PM yinsee
- move get_inventory to ajax_sku_popups.php

2/5/2008 12:41:52 PM yinsee
- list sku by brand_id

2/20/2008 11:54:17 AM yinsee
- add urldecode for delete image

2/26/2008 11:17:31 AM gary
- insertion to sku_obsolete when move sku.

4/16/2008 4:24:54 PM yinsee
- fix packing uom not showing when a=view

11/18/2008 2:38:01 PM yinsee
- is_parent derive from (sku_code+'0000' == sku_item_code)

12/29/2008 6:49:32 PM yinsee
- add branch column
- vendor, branch filters

1/6/2009 7:00:14 PM yinsee
- search will show parents also if the match is child sku item

2/12/2009 11:33 AM Andy
- modify data shown by branch group

2/16/2009 12:05:18 PM yinsee
- multics report: print Note

3/11/2009 10:52:51 AM yinsee
- view sku show photo
- extra photos folder (cutemaree)

4/4/2009 9:39:25 AM yinsee
- hide inactive branch

5/8/2009 12:47:05 PM yinsee
- set sku_items_cost changed=1 when edit

05/19/2009 1:08 PM jeff
- add if search_description is empty dont search by sku.id

6/22/2009 3:04:00 PM Andy
- Add HQ Cost for show
	- alter table sku_items add hq_cost double

6/23/2009 4:12:00 PM jeff
- add in show sku child without sku

7/7/2009 10:34:00 AM jeff
- add sku_type filter

7/28/2009 11:14:23 AM Andy
- Add ctn 1 and ctn 2 for sku items

8/18/2009 5:56:20 PM ys
- use triggers to set is_parent

8/19/2009 2:43:10 PM Andy
- is_parent is now a column, no need use sku item code to compare
- check sku can move or not before moving

5/10/2009 3:46:40 PM yinsee
- add stock location
*
2009/10/15 17:29:35 PM Andy
- Modify reindex parent function

11/4/2009 6:19:07 PM yinsee
- change parent using parent ARMS Code

11/5/2009 4:54:42 PM yinsee
- add change parent button when edit SKU

11/11/2009 9:38:47 AM edward
- add sku_item_code for all log_br

11/17/2009 3:27:59 PM yinsee
- fix a bug when sku_id is not same as armscode, it will give error "System Error (line 901)"

12/11/2009 3:26:40 PM edward
- add save_sku decimal qty

12/21/2009 6:23:17 PM Andy
- Fix if no filter category will have sql error

2/23/2010 4:39:36 PM Andy
- Fix Master SKU searching show less item then actual

3/1/2010 5:40:35 PM Andy
- Add update category changed if item change category

3/30/2010 10:18:45 AM yinsee
- show thumbail

4/26/2010 4:34:00 PM Andy
- A little bit reduce memory usage

5/31/2010 2:42:40 PM yinsee
- fix bug when adding sku, the next item code could cause duplicate if item has been moved to other parent

6/3/2010 12:07:32 PM yinsee
- fix sku listing photo bug (point to branch to get the photo)

7/2/2010 6:37:19 PM Alex
- Add filtering on avoid duplicate artno or mcode compare to other sku_id

8/3/2010 4:08:41 PM yinsee
- reason log to 'MASTERFILE_SKU_ACT' instead of 'MASTERFILE' (bug)

8/13/2010 10:01:19 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/16/2010 11:37:19 AM Andy
- Remove the word "Reason Active/Inactive: " from saving active/inactive SKU.

8/19/2010 6:09:26 PM Alex
- Add sku_type checking while submit

8/20/2010 11:23:34 AM - yinsee
- not allow system-wide duplicate artno if consignment mode

10/29/2010 6:05:47 PM Justin
- Modified trade discount code and trade discount type become null when the sku type is "OUTRIGHT".

11/8/2010 6:00:05 PM Alex
- add sku items active/inactive filter

11/15/2010 11:00:10 AM Alex
- add block filter

11/16/2010 6:36:16 PM Justin
- Fixed the consigment mode that compare wrongly on artno.

11/17/2010 6:09:22 PM Justin
- Added checking and update for sku whether have serial no or not.

11/29/2010 2:20:21 PM Justin
- Fixed the sql query errors when brand is empty.
- Added mi and ms functions to all variables that without these function.

12/3/2010 5:08:55 PM PM Alex
- fix show unblock and block items not same as show all items

12/8/2010 9:56:51 AM Justin
- Added the checking for Trade Discount table cannot be empty when it is consignment mode.

12/9/2010 12:00:43 PM Andy
- Add allow delete sku apply photo at sku edit.
- Change get sku apply photo method.

12/9/2010 3:18:47 PM Justin
- Modified the HQ Cost to auto copy into the next row.
- Modified the checking for recalculate the cost price to calculate only when it is outright sku type.

12/13/2010 11:37:40 AM Justin
- Fixed the bugs where user can directly update the sku without the selecting the Trade Discount type when it is consignment type.
- Fixed the wrong checking for trade discount type while it is Outright sku type.

12/15/2010 2:49:53 PM Alex
- reset to previous sku parent

1/10/2011 3:32:54 PM Justin
- Added updates for S/N.

3/10/2011 3:24:34 PM Andy
- Add PO reorder qty min & max at edit/apply SKU.

3/28/2011 4:24:32 PM Justin
- Added to retrieve extra info when view receipt info in item details.

3/29/2011 10:51:59 AM Justin
- Added member no information for receipt detail.

4/13/2011 3:14:14 PM Andy
- Change resync sku at masterfile only can use at HQ.
- Change resync sku to use replace into, so the wrong info or missing sku in other branches or sync server will be correct too.

4/26/2011 1:36:20 PM Justin
- Added update for scale_type from SKU table.

5/16/2011 6:32:02 PM Andy
- Add checking for sku photo path and change path to show/add the image.

5/18/2011 12:44:24 PM Alex
- split artno to artno and size

6/1/2011 11:51:37 AM Andy
- Fix add SKU photo wrong photo path.

6/13/2011 3:02:59 PM Andy
- Add "Allow decimal qty in GRN" at SKU.

6/20/2011 1:49:19 PM Andy
- include "masterfile_sku_application.include.php" at masterfile_sku.php

6/24/2011 4:48:14 PM Andy
- Make all branch default sort by sequence, code.

6/30/2011 11:29:21 AM Justin
- Added the missing checking for valid format of MCode.
- Fixed the error message to always show on each error SKU item instead show on top of SKU header.

7/5/2011 5:44:32 PM Justin
- Modified the mcode to allow 5 and 6 digits.

7/22/2011 2:45:47 PM Alex
- add sku show other sku id if got duplicate artno or mcode

9/6/2011 5:29:32 PM Justin
- Added new feature to check SKU items/Matrix first before save SKU and SKU items info.
- Disabled the previous checking function "check_error" for SKU items.

9/7/2011 3:07:41 PM Justin
- Modified the errors return array for SKU items to capture reject reason.
- Modified to pick up existing reject reason for those existing SKU items.

9/12/2011 7:20:36 PM Alex
- fix error checking at mcode

9/14/2011 9:59:24 AM Alex
- add filter for searching code

9/15/2011 11:16:43 AM Andy
- Add filter "All" for searching code.
- Fix a bugs cause SKU child din't show and also fix memory problem.

9/19/2011 11:37:29 AM Alex
- remove the auto generate artno checking

9/20/2011 6:27:59 PM Alex
- remove checking on same sku artno 

9/22/2011 11:56:23 AM Andy
- Change masterfile SKU default will not load SKU list, will only load when user search.
- Significantly increase loading speed.
- Fix pagination bugs.

9/23/2011 11:15:24 AM Andy
- Fix brand and vendor dropdown does not load on start.

10/20/2011 9:56:13 AM Alex
- fix bugs of explode article no problem
- Modified the round up for cost to base on config.

10/24/2011 5:22:18 PM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

3/28/2012 6:07:55 PM Andy
- Add checking if sku group is create by matrix then parent will allow empty mcode/artno.

4/9/2012 5:51:11 PM Alex
- change mod to 0777 after upload photo
- change mod of image while uploaded - add_photo

4/23/2012 4:44:12 PM Justin
- Added to maintain/update PO reorder qty by branch.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

6/29/2012 3:29:11 PM Justin
- Added new validation for Reject Reason.

7/2/2012 5:09:23 PM Justin
- Added new field "Scale Type" for user to maintain by item.
- Fixed bugs of auto tick "Overwrite PO Reorder qty by Branch" while return from error.

7/5/2012 2:45:23 PM Justin
- Fixed bug of showing reject reason is empty when add by size/color.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

9/3/2012 11:47 AM Fithri
- Item details - show barcode

9/21/2012 10:17 AM Justin
- Added to redirect user to BOM editor page while found the SKU is belongs to BOM.

11/23/2012 12:13 PM Justin
- Enhanced to skip insert reason log while found it is the same as previous.

12/20/2012 9:59:00 AM Fithri
- Under Sku Listing request add filter for Scale type

2/14/2013 11:24 AM Fithri
- allow cost higher than selling

3/12/2013 11:03 PM Andy
- fix to trigger recalculate cost when change sku parent, only when cost calculation method got consider parent/child.

5/17/2013 11:11 AM Justin
- Enhanced to manage additional description while config is turned on.

5/23/2013 2:48 PM Andy
- Fix warning message if the sku does not have additional description.

10/10/2013 5:57 PM Justin
- Fixed bug on receipt description always pickup 40 characters after return from errors.

10/17/2013 3:34 PM Fithri
- if search value is 13 chars (armscode/mcode/linkcode/artno) if not found then use only the first 12 character - this is because barcoder generate 1 extra character

12/27/2013 11:51 AM Andy
- Fix delete sku application photo bug.

3/31/2014 3:04 PM Justin
- Enhanced to have duplicate validation for Art No & MCode from SKU Application.

4/3/2014 2:28 PM Justin
- Enhanced to allow user maintain "PO Reorder Qty Min & Max" by SKU items.

4/21/2014 10:38 AM Justin
- Enhanced to allow user maintain "Block item in GRN".

5/14/2014 5:02 PM Justin
- Bug fixed on artno checking for consignment customers.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/3/2014 11:32 AM Justin
- Enhanced to have new ability that can upload images by PDF file.

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

8/20/2014 5:55 PM DingRen
- add Input Tax, Output Tax, Inclusive Tax

10/20/2014 3:20 PM Justin
- Enhanced to skip zero selling price and selling below cost price errors while Open Price is checked.

11/10/2014 4:18 PM Fithri
- allow SKU child items to have same color / size (config sku_allow_same_colour_size)

1/2/2015 4:50 PM Justin
- Enhanced to pickup GST info in details from category.

1/23/2015 2:36 PM Andy
- Change the selling price must always >0 except is open price.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/24/2015 3:40 PM Andy
- Change to allow zero selling price if "allow selling foc" is ticked.

3/26/2015 6:12 PM Justin
- Bug fixed gst info capture wrongly while config is not turned on.

4/10/2015 10:42 AM Andy
- Enhance to load category gst when edit masterfile sku.
- Enhance to immediately calculate item gst_amt and gst_selling_price on load sku items.

7/16/2015 10:31 AM Andy
- Enhanced to load item real input tax/output tax.

7/28/2015 3:13 PM Justin
- Bug fixed on inclusive tax for category will load wrongly.

7/30/2015 5:47 PM Joo Chia
- Enhance to load input tax, output tax, and inclusive tax and show in table.
- Enhance to allow to filter by input tax, output tax, and inclusive tax.

11/23/2015 9:30 AM Qiu Ying
- auto resize image for sku photo

12/17/2015 1:01 PM DingRen
- add Allow Parent and Child duplicate MCode
- check check duplicate MCode

12/17/2015 5:43 PM Qiu Ying
- Add config to allow upload photo without resize

05/03/2016 15:00 Edwin
- Added new filter field: Goat Parent Child

5/20/2016 1:21 PM Andy
- Fix when update sku, the log save wrong sku_item_code.

6/7/2016 2:51 PM Andy
- Fix when check duplicate mcode skip empty.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

13/2/2017 10:07 AM Zhi Kai
- Change the page title of 'SKU Masterfile' to 'SKU Listing'

4/20/2017 2:42 PM Khausalya
- Enhanced changes from RM to use config setting. 

4/21/2017 1:39 PM Justin
- Enhanced to capture "Not Allow Discount".

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

6/15/2017 13:45 Qiu Ying
- Enhanced to show total page record
- Enhanced to show the latest cost

7/21/2017 16:42 Qiu Ying
- Bug fixed on Masterfile SKU Listing Parent Child Filter not working

8/1/2017 17:24 Qiu Ying
- Bug fixed on loading Sku Listing not responding

8/2/2017 4:19 PM Andy
- Add sql_freeresult for query in get_reason() and also use sql_fetchassoc().
- Increase memory limit to 1024M for search sku.

8/11/2017 1:04 PM Justin
- Bug fixed on take too long on querying sku items count.

9/11/2017 1:41 PM Justin
- Enhanced to have new feature "Use Matrix".

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

2017-09-21 09:45 AM Qiu Ying
- Enhanced to add column "Packing UOM"
- Enhanced to add column Stock Reorder Min & Max Qty, if set by branch, then display under branch column

11/28/2017 1:43 PM Justin
- Enhanced to have Group by SKU checkbox and able to sum up the stock balance while it is checked.
- Optimised to take out on screen call for smarty function.

11/30/2017 6:03 PM Justin
- Bug fixed on the Bal Qty column for all items always show 0.
- Bug fixed when the Branch Group is selected, item details in HQ are not shown.

12/20/2017 5:22 PM Andy
- Fixed to show "N/A" for no inventory sku.

12/27/2017 10:26 AM Andy
- Fixed "AVG Cost" cannot show when users login at branch.

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

4/24/2018 9:41 AM Andy
- Fixed load_branches_group to filter active branch only.

10/22/2018 4:54 PM Justin
- Enhanced the module to compatible with new SKU Type.
- Modified to load SKU type from its own table instead from SKU.

11/20/2018 3:52 PM Justin
- Enhanced to have "Sort by" and "Match word with" dropdown list options.

12/17/2018 10:38 AM Justin
- Bug fixed on newly added SKU items as if user press refresh button from the redirected page.

12/18/2018 11:50 AM Andy
- Fixed sku searching match anyplace.

12/19/2018 3:10 PM Andy
- Fixed 13 digits search not found.

1/15/2019 5:43 PM Justin
- Bug fixed on showing mysql error when view this module redirected from Masterfile Brand.

1/16/2019 11:02 AM Andy
- Enhanced to disable users to change sku_type if sku got used in documents or sales.
- Enhanced to disable users to change packing uom if sku got grn.

3/6/2019 2:48 PM Andy
- Enhanced to have Page Selection at the bottom of page.

5/7/2019 10:00 AM William
- Add search filter by packing uom.

5/29/2019 5:00 PM William
- Added new moq and pickup.
- Enhanced to disable Moq value larger than Max value.

6/27/2019 11:AM William
- Added new insert promotion image.

7/8/2019 10:44 PM William
- Enhanced View Logs record more details log, such as what fields and changed value.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.

9/27/2019 1:31 PM Andy
- Enhanced to capture log when remove any sku image.
- Enhanced to update sku_items.lastupdate when upload POS Image or remove any sku image.
- Change to always save POS Image name as 1.jpg

2/28/2020 11:56 AM William
- Enhanced to added new column "Marketplace Description".

7/13/2020 3:52 PM William
- Enhanced to have checkbox "Prompt when scan at POS Counter".

8/3/2020 1:55 PM William
- Enhanced to show promotion photo as 1st photo on sku listing table.

8/24/2020 1:58 PM William
- Enhanced to clear browser cache when diplay new upload image.

11/11/2020 12:03 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.

11/13/2020 4:06 PM Andy
- Added "Recommended Selling Price" (RSP) feature.


*/
include("include/common.php");
//$maintenance->check(26);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '1024M');
include_once("masterfile_sku_application.include.php");

//$con = new sql_db("hq.aneka.com.my", "arms_slave", "arms_slave", "armshq");

$smarty->assign("PAGE_TITLE", "SKU Listing");

$con->sql_query("select id, code, fraction from uom where active order by code");
$smarty->assign("uom", $con->sql_fetchrowset());

//$inherit_options = array('inherit'=>'Inherit (Follow Category)', 'yes'=>'Yes', 'no'=>'No');
//$smarty->assign('inherit_options', $inherit_options);

if (isset($_REQUEST['a']))
{
	switch ($_REQUEST['a'])
	{
		case 'update_move_sku':
			$form=$_REQUEST;
			$sku_item_id=$form['sku_item_id'];
			$to_sku_id=mi($form['to_sku_id']);
			$can_move = false;

			$q1=$con->sql_query("select sku_id from sku_items where id=$sku_item_id");
			$r1 = $con->sql_fetchrow($q1);
			$old_sku_id=mi($r1['sku_id']);

			// check whether can move or not
			$con->sql_query("select * from sku_items where sku_id=$old_sku_id and id<>$sku_item_id") or die(mysql_error());
			$other_items = $con->sql_fetchrowset();

			if(!$other_items)	$can_move = true;
			else{
                foreach($other_items as $r){
					if($r['is_parent']||$r['packing_uom_id']==1){
					    $can_move = true;
					    if($r['is_parent']){
							$already_have_parent = true;
							break;
						}
					}
				}
			}
			if(!$can_move){
				die("This item cannot be move, no item in the group can become parent.");
			}
			
			// load info from new parent SKU
			$q1 = $con->sql_query("select sku_item_code, weight_kg from sku_items where sku_id=".mi($to_sku_id)." and is_parent= 1");
			$rs=$con->sql_fetchassoc($q1);
			
			// load other SKU item to be moved
			$q1 = $con->sql_query("select si.sku_item_code, u.fraction as uom_fraction from sku_items si left join uom u on u.id = si.packing_uom_id where si.id=".mi($sku_item_id));
			$r=$con->sql_fetchassoc($q1);
			
			// recalculate the weight in KG by following the ratio from new parent SKU
			$child_weight_kg = 0;
			if($rs['weight_kg']) $child_weight_kg = round($r['uom_fraction'] * $rs['weight_kg'], $config['global_weight_decimal_points']);

			$con->sql_query("update sku_items set sku_id=".mi($to_sku_id).", is_parent=0, weight_kg = ".mf($child_weight_kg)." where id=".mi($sku_item_id));

			log_br($sessioninfo['id'], 'MASTERFILE', $sku_item_id, "Move SKU: ".$r['sku_item_code']." to ".$rs['sku_item_code']);

			if($config['sku_use_avg_cost_as_last_cost'] || $config['sku_update_cost_by_parent_child']){
				$con->sql_query("update sku_items_cost set changed=1 where sku_item_id in (select si.id from sku_items si where si.sku_id in (".$old_sku_id.",".$to_sku_id."))");
			}
			
			$q2=$con->sql_query("select id from sku_items where sku_id=".mi($old_sku_id));
			if ($con->sql_numrows($q2)==0){

				$con->sql_query("insert into sku_obsolete (id, sku_code, category_id, vendor_id, uom_id, brand_id, status, active, apply_by, approval_history_id, remark, sku_type, listing_fee_type, listing_fee_remark, apply_branch_id, multics_dept, multics_section, multics_brand, note, multics_category, multics_pricetype, default_trade_discount_code, trade_discount_type, timestamp, added, varieties, block_list, is_bom)
select id, sku_code, category_id, vendor_id, uom_id, brand_id, status, active, apply_by, approval_history_id, remark, sku_type, listing_fee_type, listing_fee_remark, apply_branch_id, multics_dept, multics_section, multics_brand, note, multics_category, multics_pricetype, default_trade_discount_code, trade_discount_type, timestamp, added, varieties, block_list, is_bom
from sku where sku.id=".mi($old_sku_id));
				$con->sql_query("delete from sku where id=".mi($old_sku_id));
			}else{
				// still left other items in old sku,
				if(!$already_have_parent){
					$con->sql_query("update sku_items set is_parent=1 where sku_id=".mi($old_sku_id)." and packing_uom_id=1 order by sku_item_code,id limit 1") or die(mysql_error());
				}
			}
			break;

		case 'reset_to_parent':

			$sid = mi($_REQUEST['sku_item_id']);

			$con->sql_query("select si.sku_id, sku.sku_code as prev_code, si.sku_item_code, substring(si.sku_item_code,1,8) as sku_code from sku_items si
			                left join sku on si.sku_id=sku.id
							where si.is_parent=0 and si.id = ".mi($sid)) or die(mysql_error());

			$item = $con->sql_fetchrow();

			$sku_code = $item['sku_code'];

			$obs=$con->sql_query("select * from sku_obsolete where sku_code = ".mi($sku_code)) or die(mysql_error());
			if ($con->sql_numrows($obs)>0){
				$sku_obs=$con->sql_fetchrow();
				$con->sql_query("insert into sku " . mysql_insert_by_field($sku_obs));

				$con->sql_query("update sku_items set sku_id=".mi($sku_obs['id']).", is_parent=1 where id=".mi($sid));

				$con->sql_query("delete from sku_obsolete where id=".mi($sku_obs['id']));

				log_br($sessioninfo['id'], 'MASTERFILE', $sid, "Reset SKU ".$item['sku_item_code']." : Sku id: ".$item['sku_id']." to ".$sku_obs['id']);
			}
			break;

		case 'ajax_load_move_sku':
			load_sku('masterfile_sku.sku_detail.tpl');
			exit;

		case 'ajax_remove_photo':
			$sku_item_id = mi($_REQUEST['sku_item_id']);
			$photo_type = $_REQUEST['photo_type'];
			if(!$sku_item_id)	die("Invalid SKU ITEM ID.");
			
			$f = urldecode($_REQUEST['f']);
			if(!$f)	die("Invalid Image File Path");
			
			if (@unlink($f)){
				$upd = array();
				$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
				if($photo_type == "promo_photo") $upd['got_pos_photo'] = 0;
				$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$sku_item_id");
				
				log_br($sessioninfo['id'], 'MASTERFILE', $sku_item_id, "SKU Removed Photo (ID#$sku_item_id), Path: $f");
				print "OK";
			}

			else
				print "Delete failed";
			exit;
	    case 'ajax_check_artmcode':

	    	if ($_REQUEST['artnos']!=''){

	            if (!$config['sku_application_artno_allow_duplicate']){

/*
                    $arr_artno=array();
		            $a=0;
					foreach($_REQUEST['artno'] as $artno){
						$arr_artno[]= $artno;
					}
					foreach($arr_artno as $artno){
						if ($_REQUEST['artnos'] == $artno)	++$a;
					}

					if ($a>=2){
						printf ("Error: ".$LANG['SKU_ARTNO_REPEATED']."\n", $_REQUEST['artnos']);
						return;
					}
					unset($arr_artno);
*/
                    if ($config['consignment_modules'])
				    {
				        $sku_rid=$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($_REQUEST['id'])." and artno = ".ms($_REQUEST['artnos']));
					}
					else
					{
						$get_did=$con->sql_query("select department_id from category where id=".mi($_REQUEST['category_id']));
						$r=$con->sql_fetchrow($get_did);
						$line=get_line_detail($r['department_id']);
	
						if($line=='SOFTLINE')	$filter=" and sku.brand_id = ".mi($_REQUEST['brand_id']);
						
						$con->sql_freeresult($get_did);
					    $sku_rid=$con->sql_query("select concat('SKU ',sku.id) as id from sku_items si
					   					left join sku on si.sku_id=sku.id
					   					left join category c1 on sku.category_id=c1.id and c1.department_id = ".mi($r['department_id'])."
										where sku.id <> ".mi($_REQUEST['id'])." $filter and sku.vendor_id= ".mi($_REQUEST['vendor_id'])."
										and si.artno = ".ms($_REQUEST['artnos']));
					}
					
				    if($con->sql_numrows($sku_rid) > 0 ){
						$sku=$con->sql_fetchrow($sku_rid);
				    	printf($LANG['SKU_ARTNO_REPEATED']." ($sku[id])", $_REQUEST['artnos']);
				    	$con->sql_freeresult($sku_rid);
				    	return;
				    }
				}
			}
			elseif ($_REQUEST['mcodes']!=''){
                $arr_mcode=array();
			    $m=0;

				$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($_REQUEST['id'])." and mcode = ".ms($_REQUEST['mcodes']));
			    if($con->sql_numrows() > 0 ){
					$sku=$con->sql_fetchrow($sku_rid);
			    	printf("Error: ".$LANG['SKU_MCODE_REPEATED']." ($sku[id])", $_REQUEST['mcodes']);
			    	return;
				}

				foreach($_REQUEST['mcode'] as $mcode){
					$arr_mcode[]= $mcode;
				}
				foreach($arr_mcode as $mcode){
					if ($_REQUEST['mcodes'] == $mcode)	++$m;
				}

				if ($m>=2 && !$_REQUEST['parent_child_duplicate_mcode']){
					printf ("Warning: ".$LANG['SKU_MCODE_REPEATED_WARNING']."\n", $_REQUEST['mcodes']);
					return;
				}

				unset($arr_mcode);
			}
			print "OK";
			exit;
	    case 'ajax_add_matrix':
	        $category_id = mi($_REQUEST['cat_id']);
			$sku_type = trim($_REQUEST['sku_type']);

			if(!$sku_type||!$category_id)   $last_approval = false;
			else{
                $last_approval = check_is_last_approval_of_sku_application($category_id, $sku_type, $sessioninfo['id'], $sessioninfo['branch_id']);
			}
			$smarty->assign("last_approval", $last_approval);
			//$smarty->assign("item_n", intval($_REQUEST['n']));
	        $smarty->assign("item_type", 'matrix');
	        $smarty->display("masterfile_sku.edit.matrix.tpl");
			exit;

		case 'ajax_grow_table':
	        // get the current table and add row and col
	        $tableid = intval($_REQUEST['table']);
	        $add_row = intval($_REQUEST['add_row']);
	        $add_col = intval($_REQUEST['add_col']);
	        $del_row = intval($_REQUEST['del_row']);
	        $del_col = intval($_REQUEST['del_col']);
	        $tb = $_REQUEST['tb'][$tableid];
	        $tbm = $_REQUEST['tbm'][$tableid];
	        $tbprice = $_REQUEST['tbprice'][$tableid];
	        $tbcost = $_REQUEST['tbcost'][$tableid];
	        $tdtype = intval($_REQUEST['trade_discount_type']);
	        $tbh = count($tb);
	        $tbw = count($tb[0]);
	        /*print "$tbh,$tbw,$add_row,$add_col<br />";
			print "<pre>";print_r($tb);print "</pre>";*/

			print "<table class=input_matrix cellspacing=0 cellpadding=2 border=0>\n";

			print "<tr><td colspan=2></td>";
			for ($c=1,$real_c=-1;$c<$tbw+$add_col;$c++)
			{
		    	if ($del_col > 0 && $c == $del_col) continue;
		    	$real_c++;
				print "<td align=center><font color=#999999>".chr($real_c+65)."</font></td>";
			}
			print "<td rowspan=2 align=center><font color=#999999>Selling<br />Price</font></td>";
			print "<td rowspan=2 align=center><font color=#999999>Cost<br />Price<br />(" . $config["arms_currency"]["symbol"] . " or %)</font></td>";
			if($config['sku_listing_show_hq_cost'] && BRANCH_CODE =='HQ'){
                print "<td rowspan=2 align=center><font color=#999999>HQ<br />Cost</font></td>";
			}
			print "</tr>\n";

			for ($r=0,$real_r=-1;$r<$tbh+$add_row;$r++)
			{
			    if ($del_row > 0 && $r == $del_row) continue;
		       	$real_r++;
			    print "<tr>";
				if ($r>0)
			    	print "<td><font color=#999999>$real_r</font></td>";
			    else
			   	 	print "<td></td>";
				for ($c=0,$real_c=-1;$c<$tbw+$add_col;$c++)
				{
				    if ($del_col > 0 && $c == $del_col) continue;
				    $real_c++;
				    if ($r==0 && $c==0)
				    {
				    	print '<td><input name=tb['.$tableid.'][0][0] type=hidden><input name=tbm['.$tableid.'][0][0] type=hidden>Product<br />Varieties</td>';
				        continue;
					}

				    if ($real_r == 0 ) {
						$div = "autocomplete_color";
						$type = "color";
					}
				    else{
						$type = "size";
						$div = "autocomplete_size";
					}

					$v = htmlspecialchars(strval($tb[$r][$c]));
				    $cls = ($c==0 || $r==0) ? "onchange=uc(this) onkeydown=autocomplete_color_size(\"$type\",$tableid,$real_r,$real_c,this) alt='header' autocomplete='off'"  : "onchange=check_artmcode(this,'artno') ";

				    print "<td><input $cls title='Art No' name=tb[$tableid][$real_r][$real_c] id=\"".$div."_".$tableid."_".$real_r."_".$real_c."\" value=\"$v\">";
     				print "<div id=\"div_".$div."_choices_".$tableid."_".$real_r."_".$real_c."\" class=\"autocomplete\" style=\"display:none;\"></div>";
					print "<br /><img src=ui/pixel.gif height=2 width=10><br />";
					$v = htmlspecialchars(strval($tbm[$r][$c]));
				    $cls = ($c==0 || $r==0) ? "type=hidden" : "onchange=check_artmcode(this,'mcode')";
					print "<input $cls title='Mcode' name=tbm[$tableid][$real_r][$real_c] value=\"$v\"></td>";


				}
				if ($r>0)
				{
					$v = sprintf("%.2f", $tbprice[$r]);
					print "<td><input class=ntp onblur=\"this.value=round2(this.value);";
					print "if (document.f_a.trade_discount_type && !document.f_a.trade_discount_type[0].checked)recalculate_all_cost($tableid);";

					if ($r<$tbh+$add_row-1)
					{
						print "if (this.value>0)document.f_a.elements['tbprice[$tableid][".($real_r+1)."]'].value=this.value;";
					}

					print "\" name=tbprice[$tableid][$real_r] value=\"$v\"></td>";
					$v = sprintf("%.3f", $tbcost[$r]);
					print "<td><input class=ntp onblur=\"percent_to_amount(document.f_a.elements['tbprice[$tableid][$real_r]'],this);";
					if ($r<$tbh+$add_row-1)
						print "if (this.value>0)document.f_a.elements['tbcost[$tableid][".($real_r+1)."]'].value=this.value;";
					$ro = ($tdtype>0) ? "readonly" : "";
					print "\" name=tbcost[$tableid][$real_r] $ro value=\"".number_format($v, $config['global_cost_decimal_points'])."\"></td>";
					if($config['sku_listing_show_hq_cost'] && BRANCH_CODE =='HQ'){
						$v = sprintf("%.3f", $tbhqcost[$r]);
					    print "<td><input class=ntp onblur=\"this.value=round(this.value, ".$config['global_cost_decimal_points'].");";
					    
					    if ($r<$tbh+$add_row-1)
						{
							print "if (this.value>0)document.f_a.elements['tbhqcost[$tableid][".($real_r+1)."]'].value=this.value;";
						}
						print "\" name=tbhqcost[$tableid][$real_r] value='".number_format($v, $config['global_cost_decimal_points'])."' /></td>";
					}
				}

				if ($r==0 && $c<=15)
				{
					print '<td><img src="ui/tb_addcol.png" onclick="tb_expand('.$tableid.',0,1)" title="Add One Column"></td>';
     			}
     			else
     			{
     			 print "<td><img src=ui/del.png title=\"Delete Row $real_r\" onclick=\"if (confirm('Are you sure?')) del_row($tableid,$real_r)\"></td>";
				}
				print "</tr>\n";
			}


			// maximum allowed is 10x10
			print "<tr><td colspan=2>";
			if ($r <= 15)
				print '<img src="ui/tb_addrow.png" onclick="tb_expand('.$tableid.',1,0)" title="Add One Row">';
			print "</td>";
			for ($c=1,$real_c=0;$c<$tbw+$add_col;$c++)
			{
			    if ($del_col > 0 && $c == $del_col) continue;
			    $real_c++;
				print "<td><img src=ui/del.png title=\"Delete Column ".chr($real_c+64)."\" onclick=\"if (confirm('Are you sure?')) del_col($tableid,$real_c)\"></td>";
			}

	        if ($r <= 15 && $c <= 15)
            {
                print "<td>&nbsp;</td>";
				print "<td>&nbsp;</td>";
				if($config['sku_listing_show_hq_cost'] && BRANCH_CODE=='HQ'){
					print "<td>&nbsp;</td>";
				}
				print '<td colspan=2><img src="ui/tb_addrowcol.png" onclick="tb_expand('.$tableid.',1,1)" title="Grow Table"></td>';
			}
			print "</tr></table>\n";
	        exit;

		case 'add_photo':
			$id = intval($_REQUEST['id']);

			// check where to store sku photo
			if(function_exists('use_new_sku_photo_path') && use_new_sku_photo_path()){
				$group_num = ceil($id/10000);
				if (!is_dir($_SERVER['DOCUMENT_ROOT']."/sku_photos/actual_photo/".$group_num)){
					mkdir($_SERVER['DOCUMENT_ROOT']."/sku_photos/actual_photo/".$group_num, 0777, true);
				}
				$sku_photo_abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/actual_photo/".$group_num."/".$id;
			}else{
				$sku_photo_abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/a/".$id;
			}
			if (!is_dir($sku_photo_abs_path)){
				mkdir($sku_photo_abs_path, 0777, true);
			}
		
			//print_r($_FILES['fnew']);
			
			// found it is uploaded by PDF file, convert the first page into image
			if($_FILES['fnew']['type'] == "application/pdf"){
				$img_path = str_replace(".pdf", ".jpg", $_FILES['fnew']['name']);
				//$tmp_filepath = "$sku_photo_abs_path/$img_path";
				$params = array();
				$params['image_path'] = $img_path;
				$params['sku_path'] = $sku_photo_abs_path;
				$params['pdf_path'] = $_FILES['fnew']['tmp_name'];
				
				$filepath = pdf_handler($params);
			}else{
				$filepath = "$sku_photo_abs_path/{$_FILES['fnew']['name']}";
				move_uploaded_file($_FILES['fnew']['tmp_name'], $filepath);
				if (!$config["sku_no_resize_photo"])
					resize_photo($filepath,$filepath);
			}
			
			$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $filepath);
			chmod($imagep,0777);
			$urlenc = urlencode($imagep);
			
			print "<div id=ret><div class=imgrollover>
<img width=110 height=100 align=absmiddle vspace=4 hspace=4 src=\"/thumb.php?w=110&h=100&img=$urlenc\" border=0 style=\"cursor:pointer\" onclick=\"popup_div('img_full', '<img width=640 src=\'$imagep\'>')\" title=\"View\"><br>
<img src=\"/ui/del.png\" align=absmiddle onclick=\"if (confirm('Are you sure?'))del_image(this.parentNode,'$urlenc', 'actual_photo')\"> Delete
</div></div><script>parent.window.upload_callback($id,document.getElementById('ret'));</script>";


   			$con->sql_query("select sku_item_code from sku_items where id =".mi($id));
			$r=$con->sql_fetchrow();
		//	print_r($r);
		//	exit;
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "SKU Add Photo ".$r['sku_item_code']." (ID#$id)");
			exit;
			
		//add new promotion image
		case 'add_promotion_photo' :
			$id = intval($_REQUEST['id']);
			// check where to store sku promotion photo
			$group_num = ceil($id/10000);
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num)){
				mkdir($_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num, 0777, true);
			}
			$sku_photo_abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num."/".$id;
			if (!is_dir($sku_photo_abs_path)){
				mkdir($sku_photo_abs_path, 0777, true);
			}

			$ext = strtolower(pathinfo($_FILES['fnew2']['name'], PATHINFO_EXTENSION));
			//rename image to 1
			$newfilename = "1.jpg";
			$filepath = "$sku_photo_abs_path/{$newfilename}";
			//$filepath = "$sku_photo_abs_path/{$_FILES['fnew']['name']}";
			move_uploaded_file($_FILES['fnew2']['tmp_name'], $filepath);
			if (!$config["sku_no_resize_photo"])
				resize_photo($filepath,$filepath);
			
			$imagep = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", $filepath);
			chmod($imagep,0777);
			$urlenc = urlencode($imagep);
			//replace image 
			$current_time = date("Y-m-d H:i:s");
			print "	
			<script>parent.window.remove_current_promotion_img($id);</script>
			<div id=ret2>
				<div id=current_promotion_img[$id] class=imgrollover>
				<img width=110 height=100 align=absmiddle vspace=4 hspace=4 src=\"/thumb.php?w=110&h=100&img=$urlenc\" border=0 style=\"cursor:pointer\" onclick=\"popup_div('img_full', '<img id=img_full_promo width=640 src=\'$imagep?t=$current_time\'>')\" title=\"View\"><br>
				<img src=\"/ui/del.png\" align=absmiddle onclick=\"if (confirm('Are you sure?'))del_image(this.parentNode,'$urlenc', 'promo_photo')\"> Delete
				</div>
			</div>
			<script>parent.window.upload_callback2($id,document.getElementById('ret2'));</script>";
			//$imagep = "";
			
			$upd = array();
			$upd['lastupdate'] = 'CURRENT_TIMESTAMP';
			$upd['got_pos_photo'] = 1;
			$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$id");
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "SKU Add Promotion Photo ".$r['sku_item_code']." (ID#$id)");
			exit;
		case 'ajax_save_linkcode' :
		    if (!privilege('MST_SKU_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/masterfile_sku.php");

		    $id = intval($_REQUEST['sku_id']);
		    $v = ms($_REQUEST['value']);
		    // check duplicate
		    if ($v != '-')
			{
				$con->sql_query("select id from sku_items where link_code = ".$v." and sku_id <> ".$id." limit 1");
			    if ($con->sql_numrows()>0)
			    {
			        print sprintf($LANG['SKU_LINK_CODE_USED'], $v);
			        exit;
				}
			}
			$con->sql_query("update sku_items set link_code = ".$v." where sku_id = ".$id);
			log_br($sessioninfo['id'], 'MASTERFILE', $id, "SKU Update link_code (ID#$id => $v)");
			print "OK";
		    exit;

/*		case 'ajax_delete_variety':
			if (!privilege('MST_SKU_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/masterfile_sku.php");
			$sku_item_id = intval($_REQUEST['sku_item_id']);
	   		$con->sql_query("delete from sku_items where id = $sku_item_id") or die(mysql_error());
	   		exit;
*/
		case 'ajax_get_trade_discount_table':
			$dsctable = load_discount_table($_REQUEST);
			foreach($dsctable as $k=>$v)
			{
				print "formobj.elements['trade_discount_table[$k]'].value = '$v';\n";
			}
			exit;

		case 'ajax_add_item':
			$con->sql_query("select id,code,description from branch order by sequence,code");
			$smarty->assign("branch", $con->sql_fetchrowset());
			
			$con->sql_query("select u.* 
							 from user u
							 left join user_privilege up on u.id=up.user_id 
							 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

			while($r = $con->sql_fetchassoc()){
				$po_reorder_users[$r['id']] = $r;
			}
			$con->sql_freeresult();
			$smarty->assign("po_reorder_users", $po_reorder_users);

			$smarty->display("masterfile_sku.edit.items.tpl");
			exit;

		case 'edit':
		    if (!privilege('MST_SKU_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/masterfile_sku.php");
		    load_sku();
			exit;

		case 'view':
		    load_sku('masterfile_sku.view.tpl');
			exit;


		case 'save':
		    if (!privilege('MST_SKU_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/masterfile_sku.php");
		    save_sku();
			exit;

		case 'find':
		    if($_REQUEST['ajax']==1){
                show_sku(false);
                exit;
			}
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			break;
		case 'get_branch_sku':
		    get_branch_sku();
		    exit;
		case 'reindex_is_parent':
		    reindex_is_parent();
		    exit;
		case 'ajax_check_sku_move':
		    ajax_check_sku_move();
		    exit;
		case 'ajax_check_sku_obsolete':
		    ajax_check_sku_obsolete();
		    exit;
		case 'change_parent':
		    change_parent();
		    exit;
		case 'sales_details':
		    sales_details();
    	    exit;
	    case 'item_details':
	        item_details();
	        exit;
		case 'update_sku_cost_changed':
		    update_sku_cost_changed();
		    exit;
		case 'resync_sku':
		    resync_sku();
		    exit;
        case 'ajax_remove_sku_apply_photo':
            ajax_remove_sku_apply_photo();
			exit;
		case 'ajax_check_fm_type':
			$con->sql_query("select cc.is_fresh_market from category_cache cc where cc.category_id = ".mi($_REQUEST['category_id']));
			$type = $con->sql_fetchrow();

			print $type['is_fresh_market'];
			exit;
		case 'update_sku_extra_info_structure':
			update_sku_extra_info_structure();
			exit;
        case 'ajax_load_category_GST':

            $id=$_REQUEST['id'];

            $input_tax=get_category_gst("input_tax",$id, array('no_check_use_zero_rate'=>1));
            $output_tax=get_category_gst("output_tax",$id, array('no_check_use_zero_rate'=>1));

            $settings["input_tax"]=$input_tax['id'];
            $settings["input_tax_code"]=$input_tax['code'];
            $settings["input_tax_rate"]=$input_tax['rate'];
            $settings["output_tax"]=$output_tax['id'];
            $settings["output_tax_code"]=$output_tax['code'];
            $settings["output_tax_rate"]=$output_tax['rate'];
            $settings["inclusive_tax"]=get_category_gst("inclusive_tax",$id);

            die(json_encode($settings));
            exit;
		case 'sku_updated_redirect':
			sku_updated_redirect();
			exit;
	  	default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

load_branches();
init_load();
if($_REQUEST['load'])	show_sku();
load_branches_group();

$smarty->display("masterfile_sku.tpl");
exit;

function init_load(){
	global $con, $smarty, $sessioninfo, $config;
	
	$con->sql_query("select id,description from brand where active=1 order by description");
	$r = $con->sql_fetchrowset();
	$con->sql_freeresult();
	if($r)	array_unshift($r, array("id"=>'0', "description"=>"UN-BRANDED"));
	$smarty->assign("brands", $r);
	unset($r);

	$con->sql_query("select id,description from vendor where active=1 order by description");
	$smarty->assign("vendors", $con->sql_fetchrowset());
	$con->sql_freeresult();
    
    $parent_child = array("" => "-- All --",
                          "yes" => "Yes",
                          "no" => "No");
    $smarty->assign("parent_child", $parent_child);
	
	$sorting_list = array("sku_item_code"=>"ARMS Code", "artno"=>"Art No", "mcode"=>"MCode", "brand_code"=>"Brand", "description"=>"Description");
	if($config['link_code_name']) $sorting_list['link_code'] = $config['link_code_name'];
	asort($sorting_list);
	$smarty->assign("sorting_list", $sorting_list);

	$matching_method_list = array("any"=>"Any Place", "start"=>"Starting", "exact"=>"Exact Match");
	asort($matching_method_list);
	$smarty->assign("matching_method_list", $matching_method_list);
}

function show_sku($sqlonly=true){
	global $con, $smarty, $sessioninfo, $LANG, $config;

    // view latest sku items added
	$where = array();
	$where_in = array();
	
	$incl_tax_string = " if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) ";
	
	$gst_enable_sel_string = " input_gst.id as input_tax_id, input_gst.code as input_tax_code, input_gst.rate as input_tax_rate, output_gst.id as output_tax_id, output_gst.code as output_tax_code,
								output_gst.rate as output_tax_rate, ".$incl_tax_string." as real_inclusive_tax ";
				
	$gst_enable_join_string = " left join gst input_gst on input_gst.id=if(if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax)<0,cc.input_tax,if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax))
						left join gst output_gst on output_gst.id=if(if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax)<0,cc.output_tax,if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax)) ";

	$common_join_str = "left join category_cache cc on cc.category_id=sku.category_id";
	
	if($config['enable_gst']){
		$select_gst_fields = ", ".$gst_enable_sel_string;
		$join_gst_files = $gst_enable_join_string;
	} else {					
		$select_gst_fields = "";
		$join_gst_files = "";
	}
	$common_join_str .= $join_gst_files;
	
	// admin can see all item (important to show uncategorize)
	if ($sessioninfo['level']<9999)
		$where[] = "(category.department_id in ($sessioninfo[department_ids]) or category.department_id is null)";

	// filter active and inactive
	if ($_REQUEST['active'] != '')
		$where[]= "sku_items.active=".mi($_REQUEST['active']);
	
	//filter uom
	if($_REQUEST['uom_id'] !=''){
		$where[] = "sku_items.packing_uom_id=".mi($_REQUEST['uom_id']);
	}
	

	// filter block or no block
    if ($_REQUEST['block'] != ''){
        $branches=load_branches(true);
		$bid=$_REQUEST['branch_id'];
		if ($branches[$bid]){
			if ($_REQUEST['block'] == '1'){
				//blocked
	        	$where[]= "sku_items.block_list like '%i:$bid;%'";
	        }
	        elseif ($_REQUEST['block'] == '0'){
				//no blocked
	            $where[]= "(sku_items.block_list not like '%i:$bid;%' or sku_items.block_list is null)";
			}
        }
	}

	// direct find by ID
	if ($_REQUEST['sku_id'] != '')
	{
		$where[] = 'sku.id = '.mi($_REQUEST['sku_id']);
	}
	elseif ($_REQUEST['sku_item_code'] != '')
	{
		$where[] = 'sku.id = (select sku_id from sku_items where sku_item_code = ' . ms($_REQUEST['sku_item_code']).')';
	}
	else
	{
		if (isset($_REQUEST['brand_id']) && $_REQUEST['brand_id']!=='') {	// -1 = ALL, 0 = unbranded
			$where[] = 'sku.brand_id = '.mi($_REQUEST['brand_id']);
		}

		if ($_REQUEST['vendor_id'] > 0) {
			$where[] = 'sku.vendor_id = '.mi($_REQUEST['vendor_id']);
		}

		if ($_REQUEST['s1'] && $_REQUEST['category_id'] > 0)
		{
			$where[] = " (category.id = ".mi($_REQUEST['category_id']). " or category.tree_str like ".ms('%('.$_REQUEST['category_id'].')%') . ")";
		}

		if (isset($_REQUEST['sku_type']) && $_REQUEST['sku_type']!=='') {	// -1 = ALL, 0 = unbranded
			$where[] = 'sku.sku_type = '.ms($_REQUEST['sku_type']);
		}
		
		if (isset($_REQUEST['scale_type']) && $_REQUEST['scale_type']!=='') {
			$where[] = '(sku.scale_type = '.mi($_REQUEST['scale_type']).' or sku_items.scale_type = '.mi($_REQUEST['scale_type']).')';
		}
		
		if (isset($_REQUEST['input_tax_filter']) && $_REQUEST['input_tax_filter']!=='' && $config['enable_gst']) {
			$where[] = 'input_gst.id = '.mi($_REQUEST['input_tax_filter']);
		}
		
		if (isset($_REQUEST['output_tax_filter']) && $_REQUEST['output_tax_filter']!=='' && $config['enable_gst']) {
			$where[] = 'output_gst.id = '.mi($_REQUEST['output_tax_filter']);
		}
		
		if (isset($_REQUEST['incl_tax_filter']) && $_REQUEST['incl_tax_filter']!=='' && $config['enable_gst']) {
			$where[] = $incl_tax_string.' = '.ms($_REQUEST['incl_tax_filter']);
		}
		
		$sdesc = trim($_REQUEST['search_description']);
		if ($sdesc == '') {
			// for multics, have to get rid of this one day ;)
			switch ($_REQUEST['status'])
			{
			    case '':
			        break;
				case '0':
					$where_in[] = "(sku_items.link_code = '' or sku_items.link_code is null)";
					break;
				case '1':
				    $where_in[] = "sku_items.link_code <> ''";
					break;
			}
		}
  		else {
			$_REQUEST['status'] = '';
            $total_mcode_item=0;
            if (strlen($sdesc) == 13){ //search for full matching mcode
                $page_where = array();
                if($where)	$page_where = $where;
                $page_where[] = "sku_items.mcode like ".ms(replace_special_char($sdesc));
                if($page_where)	$page_where = "where ".join(' and ', $page_where);
                else	$page_where = '';

                $con->sql_query("select count(*) from sku_items
						left join sku on sku_items.sku_id = sku.id
						left join category on sku.category_id = category.id
						left join vendor on sku.vendor_id = vendor.id
						$common_join_str
						$page_where");

                $t = $con->sql_fetchrow();
                $con->sql_freeresult();
                $total_mcode_item = $t[0];
            }

            if($total_mcode_item>0) {
                $where_in[] = "sku_items.mcode like ".ms(replace_special_char($sdesc));
            }
            else {
				// use 12 digits if it is 13 digit
				if (strlen($sdesc) == 13) $sdesc = substr($sdesc,0,12);
				
				// search description
				// match word with
				if($_REQUEST['match_method'] == "start") $match_method = "like ".ms(replace_special_char($sdesc)."%"); // match with starting
				elseif($_REQUEST['match_method'] == "exact") $match_method = "= ".ms(replace_special_char($sdesc)); // exact word
				else{ // any place
					$match_method = "like ".ms("%".replace_special_char($sdesc)."%");
				}
				
                switch ($_REQUEST['search_filter']) {
                    case 'linkcode':
                        $where_in[] = "sku_items.link_code ".$match_method;
                        break;
                    case 'armscode':
                        $where_in[] = "sku_items.sku_item_code ".$match_method;
                        break;
                    case 'artno':
                        $where_in[] = "sku_items.artno ".$match_method;
                        break;
                    case 'mcode':
                        $where_in[] = "sku_items.mcode ".$match_method;
                        break;
                    case 'description':
						$desc_match = array();
						if($_REQUEST['match_method'] == "any"){
							$ll = preg_split("/\s+/", $sdesc);
							foreach ($ll as $l) {
								if ($l) $desc_match[] = "sku_items.description like " . ms('%'.replace_special_char($l).'%');
							}
						} 
						else{
							$desc_match[] = "sku_items.description ".$match_method;
						}
						$where_in[] = join(" and ", $desc_match);
                        break;
                    default:
						$desc_match = array();
						if($_REQUEST['match_method'] == "any"){
							$ll = preg_split("/\s+/", $sdesc);
							foreach ($ll as $l) {
								if ($l) $desc_match[] = "sku_items.description like " . ms('%'.replace_special_char($l).'%');
							}
						} 
						else{
							$desc_match[] = "sku_items.description ".$match_method;
						}

                        $desc_match = join(" and ", $desc_match);
                        $where_in[] = "(($desc_match)
                        or sku_items.link_code ".$match_method."
                        or sku_items.sku_item_code ".$match_method."
                        or sku_items.artno ".$match_method."
                        or sku_items.mcode ".$match_method.")";
                        break;
                }
            }
		}
	}

	$where_in = join(" and ", $where_in);
	$default_filter = $where2 = $where;
	
	// no longer using clickable sorting since 11/20/2018 10:37 AM 
	//$sort_column = isset($_COOKIE['_tbsort_masterfile_sku'])?$_COOKIE['_tbsort_masterfile_sku']:'sku_items.sku_item_code';
	//$sort_order = isset($_COOKIE['_tbsort_masterfile_sku_order'])?$_COOKIE['_tbsort_masterfile_sku_order']:'desc';
	
	// sort by
	if($_REQUEST['sorting_type']){
		if($_REQUEST['sorting_type'] != "brand_code") $order_by = "sku_items.".$_REQUEST['sorting_type'];
		else $order_by = "brand.code";
		
		if($_REQUEST['sorting_sequence']) $order_by .= " ".$_REQUEST['sorting_sequence'];
	}
	
	// put a default order by since this module will be called somewhere else
	if(!$order_by) $order_by = "sku_items.sku_item_code desc";
	
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 50;
	if (isset($_REQUEST['s']))
		$pg_start = intval($_REQUEST['s']);
	else
		$pg_start = 0;
	
//	$page_where = array();
//	if($default_filter)	$page_where = $default_filter; 
//	if($where_in)	$page_where[] = $where_in; 
//	$page_where[] = "is_parent=1";
//	if($page_where)	$page_where = "where ".join(' and ', $page_where);
//	else	$page_where = '';
//    
//	$con->sql_query("select count(*) from sku_items
//                    left join sku on sku_items.sku_id = sku.id 
//					left join category on sku.category_id = category.id 
//					left join vendor on sku.vendor_id = vendor.id 
//					$join_gst_files
//					$page_where");
//   
//	$t = $con->sql_fetchrow();
//	$con->sql_freeresult();
//	$total = $t[0];
//    
    //check total got how many page
	$page_where = array();
    if($default_filter) $page_where = $default_filter; 
    if($where_in){
		$con->sql_query("create temporary table tmp_sku_filtered (index sku_id (sku_id)) (select distinct sku_id from sku_items where $where_in)");
		$join_sku = " join tmp_sku_filtered tsf on tsf.sku_id = sku.id";
	}
    if($page_where)	$page_where = "where ".join(' and ', $page_where);
    else	$page_where = '';
    
    switch($_REQUEST['parent_child_filter']){
        case "yes":
            $parent_child_filter = "group by sku_items.sku_id having parent_child_count > 1";
            break;
        case "no":
            $parent_child_filter = "group by sku_items.sku_id having parent_child_count = 1";
            break;
        default:
            $parent_child_filter = "group by sku_items.sku_id";
    }
    
    $con->sql_query("select count(*) as parent_child_count from sku_items
                    left join sku on sku_items.sku_id = sku.id 
					left join category on sku.category_id = category.id 
					left join vendor on sku.vendor_id = vendor.id 
					$common_join_str
					$join_sku
					$page_where $parent_child_filter");
    $total = $con->sql_numrows();
	$con->sql_freeresult();

	if ($pg_start > $total) {	// page start more than total item, change to start from page 1
		$pg_start = 0;
	} 
	
	// page selection if record is more than page size
	$smarty->assign("size_page", $sz);
	if ($total > $sz) {    
		// create pagination
		$pg = "&nbsp; <b>Page</b> <select name=s onchange=\"form.submit()\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++) {
			$pg .= "<option value=$i";
			if ($i == $pg_start) {
				$pg .= " selected";
				$sel_pg = $p;
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("page_of", sprintf("Page %d of %d", $sel_pg, $p-1));
		$smarty->assign("pagination", $pg);
		$smarty->assign("page_max", $p-1);
	}else{
		$sel_pg = 1;
		$sz = $total;
	}
	
	$item_from = ($sel_pg - 1) * $sz + 1;
	$item_to = min($total, $sel_pg * $sz);
	$smarty->assign("selected_page", $sel_pg);
	$smarty->assign("item_from", $item_from);
	$smarty->assign("item_to", $item_to);
	$smarty->assign("total_item", $total);
	$limit = "limit $pg_start, $sz";

//    if ($where_in && $_REQUEST['search_description'] != '') {
//		$filter = $where ? join(" and ", $where) : 1;
//		$sql = "select distinct sku_items.sku_id , brand.description as brand from sku_items
//                left join sku on sku_items.sku_id = sku.id
//                left join category on sku.category_id = category.id
//                left join vendor on vendor.id = vendor_id
//                left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
//                left join brand on brand_id = brand.id
//                left join branch on apply_branch_id = branch.id
//                $join_gst_files
//                where $filter and $where_in order by $sort_column $sort_order $limit";
//		//print $sql;exit;
//		$con->sql_query($sql);
//		$tmp_sku_id_list = array();
//		while($r=$con->sql_fetchassoc()) {
//			$tmp_sku_id_list[] = mi($r['sku_id']);
//		}
//		$con->sql_freeresult();
//		
//		if ($tmp_sku_id_list) {
//			$where_sku = "sku.id in (".join(',',$tmp_sku_id_list).")";
//		}
//		else {
//			print "<script>alert('".jsstring($LANG['SKU_SEARCH_NOT_FOUND'])."');</script>";
//			$where = 0;
//			$err_msg = $LANG['SKU_SEARCH_NOT_FOUND'];
//			$smarty->assign('err_msg', $err_msg);
//			return false;
//		}
//	}
    
	if ($where_in && $_REQUEST['search_description'] != '') {
		$filter = $where ? join(" and ", $where) : 1;
        
        switch($_REQUEST['parent_child_filter']){
        case "yes":
        case "no":
            $parent_child_select = " , (select count(*) from sku_items si2 where si2.sku_id=sku_items.sku_id) as parent_child_count";
            break;
        default:
            $parent_child_select = "";
        }
        
        $sql = "select sku_items.sku_id, brand.description as brand $parent_child_select
                from sku_items
                left join sku on sku_items.sku_id = sku.id
                left join category on sku.category_id = category.id
                left join vendor on vendor.id = vendor_id
                left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
                left join brand on brand_id = brand.id
                left join branch on apply_branch_id = branch.id
                $common_join_str
                where $filter and $where_in $parent_child_filter order by $order_by $limit";
                
		//print $sql;
		$con->sql_query($sql);
		$tmp_sku_id_list = array();
		while($r=$con->sql_fetchassoc()) {
			$tmp_sku_id_list[] = mi($r['sku_id']);
		}
		$con->sql_freeresult();
        
		if($tmp_sku_id_list) {
			$where_sku = "sku.id in (".join(',',$tmp_sku_id_list).") and is_parent = 1 ";
		}
		else {
			print "<script>alert('".jsstring($LANG['SKU_SEARCH_NOT_FOUND'])."');</script>";
			$where = 0;
			$err_msg = $LANG['SKU_SEARCH_NOT_FOUND'];
			$smarty->assign('err_msg', $err_msg);
			return false;
		}
	}else{
        if(!$where) $where = "where 1";
        else $where = "where ".join(' and ', $where);

        $sql = "select sku_items.sku_id, count(sku_items.sku_id) as parent_child_count, brand.description as brand
                from sku_items
                left join sku on sku_items.sku_id = sku.id
                left join category on sku.category_id = category.id
                left join vendor on vendor.id = vendor_id
                left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
                left join brand on brand_id = brand.id
                left join branch on apply_branch_id = branch.id
                $common_join_str
                $where $parent_child_filter order by $order_by $limit";
        $con->sql_query($sql);
        $tmp_sku_id_list = array();
		while($r=$con->sql_fetchassoc()) {
			$tmp_sku_id_list[] = mi($r['sku_id']);
		}
		$con->sql_freeresult();
        
        if($tmp_sku_id_list) {
			$where_sku = "sku.id in (".join(',',$tmp_sku_id_list).") and is_parent = 1 ";
		}
		else {
			print "<script>alert('".jsstring($LANG['SKU_SEARCH_NOT_FOUND'])."');</script>";
			$where = 0;
			$err_msg = $LANG['SKU_SEARCH_NOT_FOUND'];
			$smarty->assign('err_msg', $err_msg);
			return false;
		}
    }
	
	$branch_list = array();
	if (BRANCH_CODE == 'HQ') {
		$q1 =$con->sql_query("select id,code from branch where active=1 order by sequence,code");
		
		while($r = $con->sql_fetchassoc($q1)){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}else{
		$q1 = $con->sql_query("select id,code from branch where code = ".ms(BRANCH_CODE));
		$binfo = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$branch_list[$binfo['id']] = $binfo;
		unset($binfo);
	}
	
	$sku_id_list = $sku_item_id_list = $si_info_list = array(); // list store store sku_id, list to store all sku_item_id and sku item data list

	// get parent
	$sql = "select sku_items.*, sku_apply_items.photo_count, sku_items.selling_price as selling, sku_items.cost_price as cost, category.description as category, category.tree_str, category.id as cid, brand.description as brand, multics_category, multics_brand, multics_dept, multics_section, multics_pricetype, default_trade_discount_code, vendor.code as vendor_code, vendor.description as vendor, sku_items.active, sku.note, branch.code as branch_code, branch.ip as branch_ip, sku.is_bom, sku.po_reorder_by_child, if(sku.no_inventory='inherit',cc.no_inventory, sku.no_inventory) as no_inventory
            $select_gst_fields, uom.code as packing_uom, uom.fraction as packing_uom_fraction, sku.po_reorder_qty_min as sku_prd_qty_min, 
			sku.po_reorder_qty_max as sku_prd_qty_max,sku.po_reorder_qty_by_branch as sku_prd_qty_by_branch, sku_items.ctn_1_uom_id, sku_items.ctn_2_uom_id, uom2.fraction as ctn_1_fraction,uom3.fraction as ctn_2_fraction, uom2.code as ctn_1_code,uom3.code as ctn_2_code
            from sku_items
            left join sku on sku_items.sku_id = sku.id
            left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
            left join vendor on vendor.id = vendor_id
            left join category on sku.category_id = category.id
            left join brand on brand_id = brand.id
            left join branch on apply_branch_id = branch.id
			left join uom on sku_items.packing_uom_id = uom.id
			left join uom uom2 on uom2.id=sku_items.ctn_1_uom_id
			left join uom uom3 on uom3.id=sku_items.ctn_2_uom_id
            $common_join_str
            where $where_sku 
            order by $order_by, is_parent desc";
    //print $sql;exit;			
	$show_global_reorder_qty = false;
	$show_reorder_by_branch = false;
	$r1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($r1)){
		// get promotion photo
		if ($r['got_pos_photo'] == 1){
			$r['promotion_photos'] = get_sku_promotion_photos($r['id'],$r, true);	// skip if photo at remote server
			$r['promotion_photos_time'] = filemtime($r['promotion_photos'][0]);
		}
		
		// get image path and sku apply photo
		if ($r['photo_count']>0) {
			$r['image_path'] = get_branch_file_url($r['branch_code'], $r['branch_ip']);
			if($r['sku_apply_items_id']) $r['images_list'] = get_sku_apply_item_photos($r['sku_apply_items_id'], $r['image_path']);
		}
		if (!$r['active']) $r['reason'] = get_reason($r['id']);
		$r['photos'] = get_sku_item_photos($r['id'],$r, true);	// skip if photo at remote server
		
		if ($r["sku_prd_qty_min"] || $r["sku_prd_qty_max"]){
			$show_global_reorder_qty = true;
		}
		
		if($r["po_reorder_by_child"] || $r["sku_prd_qty_by_branch"]){
			$show_reorder_by_branch = true;
			if ($r["sku_prd_qty_by_branch"]){
				$r["prd_qty_by_branch"] = unserialize($r["sku_prd_qty_by_branch"]);
			}
		}
		
		$sku[] = $r;
		$sku_id_list[] = $r['sku_id'];
		$sku_item_id_list[$r['id']] = $r['id'];
		
		// load branch's data
		foreach($branch_list as $bid=>$tmp){
			$sic_filters = array();
			if($_REQUEST['group_by_sku']){ // filter by SKU, because need to get the total qty from parent child
				$sic_filters[] = "si.sku_id = ".mi($r['sku_id']);
			}else{ // only parent qty
				$sic_filters[] = "si.id = ".mi($r['id']);
			}
			
			// get current cost price
			$q2 = $con->sql_query("select sic.*, si.id as sku_item_id, u.fraction as uom_fraction
								   from sku_items si
								   left join sku on sku.id = si.sku_id
								   left join uom u on u.id = si.packing_uom_id
								   left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($bid)."
								   where ".join(" and ", $sic_filters));

			while($cinfo = $con->sql_fetchassoc($q2)){
				if($r['id'] == $cinfo['sku_item_id']){ // is parent info
					$cinfo['got_cost'] = 1;
					if(!$cinfo['grn_cost']){
						$cinfo['grn_cost'] = $r['cost'];
						$cinfo['got_cost'] = 0;
					}
					$parent_cost_price = $si_info_list[$bid][$r['id']]['cost_price'] = $cinfo['grn_cost'];
					$si_info_list[$bid][$r['id']]['avg_cost'] = $cinfo['avg_cost'];
					$si_info_list[$bid][$r['id']]['got_cost'] = $cinfo['got_cost'];
					$si_info_list[$bid][$r['id']]['changed'] = $cinfo['changed'];
					$si_info_list[$bid][$r['id']]['l30d_grn'] = $cinfo['l30d_grn'];
					$si_info_list[$bid][$r['id']]['l30d_pos'] = $cinfo['l30d_pos'];
					$si_info_list[$bid][$r['id']]['l90d_grn'] = $cinfo['l90d_grn'];
					$si_info_list[$bid][$r['id']]['l90d_pos'] = $cinfo['l90d_pos'];
					
					// parent SKU always not to multiply with UOM in case customer has fraction that is > 1
					$si_info_list[$bid][$r['id']]['stock_balance'] += $cinfo['qty'];
				}else{
					// child code will always multiply with fraction
					$si_info_list[$bid][$r['id']]['stock_balance'] += ($cinfo['qty'] * $cinfo['uom_fraction']);
				}
			}
			$con->sql_freeresult($q2);
			
			// get current selling price
			$q2 = $con->sql_query("select sip.* 
								   from sku_items_price sip
								   where sip.sku_item_id = ".mi($r['id'])." and sip.branch_id = ".mi($bid));
			$pinfo = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);

			if(!$pinfo['price']) $pinfo['price'] = $r['selling'];
			if(!$pinfo['trade_discount_code']) $pinfo['trade_discount_code'] = $r['default_trade_discount_code'];			
			$si_info_list[$bid][$r['id']]['selling_price'] = $pinfo['price'];
			$si_info_list[$bid][$r['id']]['trade_discount_code'] = $pinfo['trade_discount_code'];
			
			// calculate GP
			if(privilege('SHOW_COST') && $pinfo['price']){
				// found selling price is inclusive GST, need to exclude it
				
				if($config['enable_gst'] && $r['real_inclusive_tax'] == "yes"){
					$sp_before_gst = round($pinfo['price'] / ((100+$r['output_tax_rate']) / 100), 2);
				}else $sp_before_gst = $pinfo['price'];
				
				$si_info_list[$bid][$r['id']]['gp'] = $sp_before_gst - $parent_cost_price;
				$si_info_list[$bid][$r['id']]['gp_perc'] = round($si_info_list[$bid][$r['id']]['gp'] / $sp_before_gst * 100, 2);
			}
			
			unset($cinfo, $pinfo);
		}
	}
	$smarty->assign("show_global_reorder_qty", $show_global_reorder_qty);
	$smarty->assign("show_reorder_by_branch", $show_reorder_by_branch);
	$con->sql_freeresult($r1);

	// get childs
	if ($sku_id_list) {
		$where3 = "sku.id in (".join(',',$sku_id_list).") and is_parent<>1";
		if($where2)	$where2 = "where " . join(" and ", $where2);
		else    $where2 = "where 1";

		$sql = "select sku_items.*, sku_apply_items.photo_count, sku_items.selling_price as selling, sku_items.cost_price as cost, category.description as category, category.tree_str, category.id as cid, brand.description as brand, multics_category, multics_brand, multics_dept, multics_section, multics_pricetype, default_trade_discount_code, vendor.code as vendor_code, vendor.description as vendor, sku_items.active, sku.note, sku.is_bom, sku.po_reorder_by_child
				$select_gst_fields, uom.code as packing_uom, uom.fraction as packing_uom_fraction, sku.po_reorder_qty_min as sku_prd_qty_min, 
				sku.po_reorder_qty_max as sku_prd_qty_max,sku.po_reorder_qty_by_branch as sku_prd_qty_by_branch, sku_items.ctn_1_uom_id, sku_items.ctn_2_uom_id, uom2.fraction as ctn_1_fraction,uom3.fraction as ctn_2_fraction, uom2.code as ctn_1_code,uom3.code as ctn_2_code, b.code as branch_code, b.ip as branch_ip
				from sku_items
                left join sku on sku_items.sku_id = sku.id
                left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
                left join vendor on vendor.id = vendor_id
                left join category on sku.category_id = category.id
                left join brand on brand_id = brand.id
				left join uom on sku_items.packing_uom_id = uom.id
				left join uom uom2 on uom2.id=sku_items.ctn_1_uom_id
				left join uom uom3 on uom3.id=sku_items.ctn_2_uom_id
				left join branch b on sku.apply_branch_id = b.id
                $common_join_str
                $where2 and $where3";
		//print $sql;exit;
		$r2 = $con->sql_query($sql) or die(mysql_error());

	    $sku_items_child = array();
		while($r = $con->sql_fetchassoc($r2)) {
			// get promotion photo
			if ($r['got_pos_photo'] == 1){
				$r['promotion_photos'] = get_sku_promotion_photos($r['id'],$r, true);	// skip if photo at remote server
				$r['promotion_photos_time'] = filemtime($r['promotion_photos'][0]);
			}
			
			// get image path and sku apply photo
			if ($r['photo_count']>0) {
				$r['image_path'] = get_branch_file_url($r['branch_code'], $r['branch_ip']);
				if($r['sku_apply_items_id']) $r['images_list'] = get_sku_apply_item_photos($r['sku_apply_items_id'], $r['image_path']);
			}
			
			if (!$r['active'] && $r['is_parent'] == 1) $r['reason'] = get_reason($r['id']);
			$r['photos'] = get_sku_item_photos($r['id'],$r, true); 	// skip if photo at remote server
			
			if($r["po_reorder_by_child"] || $r["sku_prd_qty_by_branch"]){
				if ($r["sku_prd_qty_by_branch"]){
					$r["prd_qty_by_branch"] = unserialize($r["sku_prd_qty_by_branch"]);
				}
			}
			
		    $sku_items_child[$r['sku_id']][$r['id']] = $r;
		    $sku_item_id_list[$r['id']] = $r['id'];
			
			// load branch's data
			foreach($branch_list as $bid=>$tmp){
				// get current cost price
				$q2 = $con->sql_query("select sic.*
									   from sku_items_cost sic
									   where sic.sku_item_id = ".mi($r['id'])." and sic.branch_id = ".mi($bid));
				$cinfo = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
				
				$cinfo['got_cost'] = 1;
				if(!$cinfo['grn_cost']){
					$cinfo['grn_cost'] = $r['cost'];
					$cinfo['got_cost'] = 0;
				}
				$si_info_list[$bid][$r['id']]['cost_price'] = $cinfo['grn_cost'];
				$si_info_list[$bid][$r['id']]['avg_cost'] = $cinfo['avg_cost'];
				$si_info_list[$bid][$r['id']]['got_cost'] = $cinfo['got_cost'];
				$si_info_list[$bid][$r['id']]['changed'] = $cinfo['changed'];
				$si_info_list[$bid][$r['id']]['stock_balance'] = $cinfo['qty'];
				$si_info_list[$bid][$r['id']]['l30d_grn'] = $cinfo['l30d_grn'];
				$si_info_list[$bid][$r['id']]['l30d_pos'] = $cinfo['l30d_pos'];
				$si_info_list[$bid][$r['id']]['l90d_grn'] = $cinfo['l90d_grn'];
				$si_info_list[$bid][$r['id']]['l90d_pos'] = $cinfo['l90d_pos'];
				
				// get current selling price
				$q2 = $con->sql_query("select sip.* 
									   from sku_items_price sip
									   where sip.sku_item_id = ".mi($r['id'])." and sip.branch_id = ".mi($bid));
				$pinfo = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);

				if(!$pinfo['price']) $pinfo['price'] = $r['selling'];
				if(!$pinfo['trade_discount_code']) $pinfo['trade_discount_code'] = $r['default_trade_discount_code'];			
				$si_info_list[$bid][$r['id']]['selling_price'] = $pinfo['price'];
				$si_info_list[$bid][$r['id']]['trade_discount_code'] = $pinfo['trade_discount_code'];
				
				// calculate GP
				if(privilege('SHOW_COST') && $pinfo['price']){
					// found selling price is inclusive GST, need to exclude it
					
					if($config['enable_gst'] && $r['real_inclusive_tax'] == "yes"){
						$sp_before_gst = round($pinfo['price'] / ((100+$r['output_tax_rate']) / 100), 2);
					}else $sp_before_gst = $pinfo['price'];
					
					$si_info_list[$bid][$r['id']]['gp'] = $sp_before_gst - $cinfo['grn_cost'];
					$si_info_list[$bid][$r['id']]['gp_perc'] = round($si_info_list[$bid][$r['id']]['gp'] / $sp_before_gst * 100, 2);
				}
				
				unset($cinfo, $pinfo);
			}
		}
		$con->sql_freeresult($r2);
	}
	//print_r($sku);
	
	if(!$sku && !$sku_items_child) {
		print "<script>alert('".jsstring($LANG['SKU_SEARCH_NOT_FOUND'])."');</script>";
		$err_msg = $LANG['SKU_SEARCH_NOT_FOUND'];
		$smarty->assign('err_msg', $err_msg);
		return false;
	}
	$smarty->assign("sku", $sku);
	$smarty->assign("sku_items_child", $sku_items_child);
    
//	if ($where_sku) {
//		$where3 = "((".$where_sku." and is_parent=1))";
//		$where4 = "((".$where_sku." and is_parent<>1))";
//	}
//	else {
//		$where3 = "is_parent=1";
//		$where4 = "is_parent<>1";
//	}
//
//	$sku_id_list = array(); // list store store sku id
//	$sku_item_id_list = array();    // list to store all sku_item_id
//
//	if(!$where) $where = "where 1";
//	else    $where = "where ".join(' and ', $where);
//	if(!$where3)    $where3 = 1;
//	
//	$limit_parent = $where_sku ? '' : $limit;
//
//	// get parent
//	$sql = "select sku_items.*, sku_apply_items.photo_count, sku_items.selling_price as selling, sku_items.cost_price as cost, category.description as category, category.tree_str, category.id as cid, brand.description as brand, multics_category, multics_brand, multics_dept, multics_section, multics_pricetype, default_trade_discount_code, vendor.code as vendor_code, vendor.description as vendor, sku_items.active, sku.note, branch.code as branch_code, branch.ip as branch_ip, sku.is_bom, sku.po_reorder_by_child
//            $select_gst_fields
//            from sku_items
//            left join sku on sku_items.sku_id = sku.id
//            left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
//            left join vendor on vendor.id = vendor_id
//            left join category on sku.category_id = category.id
//            left join brand on brand_id = brand.id
//            left join branch on apply_branch_id = branch.id
//            $join_gst_files
//            $where and $where3 
//            order by $sort_column $sort_order, is_parent desc $limit_parent";
//            
//	if($sessioninfo['u']=='wsatp'){
//        //print $sql;exit;			
//	}
//
//	$r1 = $con->sql_query($sql);
//	while($r = $con->sql_fetchassoc($r1)){
//		// get image path
//		if ($r['photo_count']>0) { $r['image_path'] = get_branch_file_url($r['branch_code'], $r['branch_ip']); }
//		if (!$r['active']) $r['reason'] = get_reason($r['id']);
//		$r['photos'] = get_sku_item_photos($r['id'],$r, true);	// skip if photo at remote server
//				
//		$sku[] = $r;
//		$sku_id_list[] = $r['sku_id'];
//		$sku_item_id_list[$r['id']] = $r['id'];
//	}
//
//	$con->sql_freeresult($r1);
//		if($sessioninfo['u']=='wsatp'){
//			//print "done";exit;	
//			//print_r($_SERVER);
//			//print "http://$_SERVER[HTTP_HOST]:$_SERVER[HTTP_PORT]";		
//		}
//
//	// get childs
//	if ($sku_id_list) {
//		$where4 = "sku.id in (".join(',',$sku_id_list).") and is_parent<>1";
//		if($where2)	$where2 = "where " . join(" and ", $where2);
//		else    $where2 = "where 1";
//
//		$sql = "select sku_items.*, sku_apply_items.photo_count, sku_items.selling_price as selling, sku_items.cost_price as cost, category.description as category, category.tree_str, category.id as cid, brand.description as brand, multics_category, multics_brand, multics_dept, multics_section, multics_pricetype, default_trade_discount_code, vendor.code as vendor_code, vendor.description as vendor, sku_items.active, sku.note, sku.is_bom, sku.po_reorder_by_child
//				$select_gst_fields
//				from sku_items
//                left join sku on sku_items.sku_id = sku.id
//                left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
//                left join vendor on vendor.id = vendor_id
//                left join category on sku.category_id = category.id
//                left join brand on brand_id = brand.id
//                $join_gst_files
//                $where2 and $where4";
//		//print $sql;exit;
//		$r2 = $con->sql_query($sql) or die(mysql_error());
//
//	    $sku_items_child = array();
//		while($r = $con->sql_fetchassoc($r2)) {
//			if (!$r['active'] && $r['is_parent'] == 1) $r['reason'] = get_reason($r['id']);
//			$r['photos'] = get_sku_item_photos($r['id'],$r, true); 	// skip if photo at remote server
//						
//		    $sku_items_child[$r['sku_id']][] = $r;
//		    $sku_item_id_list[$r['id']] = $r['id'];
//		}
//		$con->sql_freeresult($r2);
//
//	}
//	//print_r($sku);
//	
//	if(!$sku && !$sku_items_child) {
//		print "<script>alert('".jsstring($LANG['SKU_SEARCH_NOT_FOUND'])."');</script>";
//		$err_msg = $LANG['SKU_SEARCH_NOT_FOUND'];
//		$smarty->assign('err_msg', $err_msg);
//		return false;
//	}
//	$smarty->assign("sku", $sku);
//	$smarty->assign("sku_items_child", $sku_items_child);

	/*
	if($_REQUEST['group_by_sku']){ // need to add child stock bal into parent
		$child_filters = array();
		$child_filters[] = "si.id != ".mi($r1['sku_item_id']);
		$child_filters[] = "si.sku_id = ".mi($r1['sku_id']);
		if($item_branch_str)   $child_filters[] = "sic.branch_id in ($item_branch_str)";
		$child_filter = "where ".join(' and ', $child_filters);
		$sql1 = $con->sql_query("select sic.*
								from sku_items_cost sic
								left join sku_items si on si.id = sic.sku_item_id
								left join sku on sku.id = si.sku_id ".
								$child_filter);

		while($r2 = $con->sql_fetchassoc($sql1)){
			$branches_group_data[$r['id']][$r1['sku_item_id']]['stock_balance'] += $r2['qty'];
		}
		$con->sql_freeresult($sql1);
	}*/

	//print_r($branch_got_group);

	$branch_group_id = intval($_REQUEST['branches_group']);   // get selected branch group id
	$branches_group_data = array();
	if(BRANCH_CODE == "HQ"){
		if($branch_group_id==0){
			$branches_group = load_branches_group();
			if($branches_group['header']){ // if branches group details exists
				foreach($branches_group['header'] as $r) {   // start loop and sum up the stock balance by branch group
					$item_branch = $branches_group['items'][$r['id']];
					foreach($item_branch as $tmp_bid=>$tmp){ // loop all the branches from group
						// get data from branch list
						$tmp_si_info = $si_info_list[$tmp_bid];
						foreach($tmp_si_info as $tmp_sid=>$r1){
							$branches_group_data[$r['id']][$tmp_sid]['stock_balance'] += $r1['stock_balance'];
						}
						if($tmp['code'] != "HQ") unset($si_info_list[$tmp_bid], $branch_list[$tmp_bid]);
					}
				}
			}
		}else{  // single branch group is selected
			$branches_group = load_branches_group($branch_group_id);    // load selected group only
			$item_branch = $branches_group['items'][$branch_group_id];
			$ids = array();
			foreach($item_branch as $key=>$temp) {
				$ids[] = $key;
			}
			$q1 = $con->sql_query("select * from branch where active=1 and id in (".join(',',$ids).") order by sequence,code");
			while($r = $con->sql_fetchassoc($q1)) {
				$branch2[$r['id']] = $r;
			}
			$con->sql_freeresult($q1);

			$smarty->assign("branch2", $branch2);
		}
	}
	
	$smarty->assign("branch_list", $branch_list);
	$smarty->assign('si_info_list', $si_info_list);
	$smarty->assign('branches_group_data', $branches_group_data);
	unset($branch_list, $si_info_list, $branches_group_data);

	if(!$sqlonly){
	    load_branches();
	    load_branches_group();
		$smarty->display('masterfile_sku.table.tpl');
	}

	if (isset($_REQUEST['print']))
	{
		$smarty->display("masterfile_sku.print.tpl");
		exit;
	}
/*
	print "<pre>";
	print_r($sku);
	print "</pre>";
*/

/*
	print "<pre>";
	print_r($sku_items_child);
	print "</pre>";
*/
}

function sales_details()
{
  	global $con, $smarty, $pos_config;

    /*
  	if (isset($_REQUEST['type']))
  	{
  		if (strtolower($_REQUEST['type']) == 'credit cards')
  		{
  			foreach($pos_config['credit_card'] as $k)
  			{
  				$types[] = ms($k);
  			}
  			$types = join(",", $types);
  		}
  		else
  			$types = ms($_REQUEST['type']);
  		$where[] = "pp.type in ($types)";
  	}

  	if (isset($_REQUEST['counter_id']))	$where[] = " p.counter_id = ".mi($_REQUEST['counter_id']);
  	if (isset($_REQUEST['e'])) $where[] = " p.pos_time <= ".ms($_REQUEST['e']);
  	if (isset($_REQUEST['s'])) $where[] = "p.pos_time >= ".ms($_REQUEST['s']);

  	if (isset($_REQUEST['card_no']))
  	{
  		$where[] = "m.card_no = ".ms($_REQUEST['card_no']);
  		$groupby = 'group by p.id';
  		$select = "round(sum(if(type='Cash',pp.amount-p.amount_change,pp.amount)),2) as payment_amount";
  		$left_join = "left join membership m on m.card_no = p.member_no";
  	}
  	else
  	{
  		$select = "round(if(type='Cash',pp.amount-p.amount_change,pp.amount),2) as payment_amount";
        	if (isset($_REQUEST['branch_id']))
  		$where[] = "pp.branch_id = ".mi($_REQUEST['branch_id']);
  		else
  		$where[] = "pp.branch_id = ".mi($this->branch_id);
        }
  	$where = implode(" and ", $where);
  	*/


  	/*
  	$con->sql_query("select p.*, pp.type, pp.adjust, pp.changed, user.u ,$select from
			pos_payment pp
			left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
			$left_join
			left join user on p.cashier_id = user.id
			where p.date = ".ms(dmy_to_sqldate($_REQUEST['date']))."  and $where $groupby order by p.pos_time");
  	*/

  	$select = "round(if(type='Cash',pp.amount-p.amount_change,pp.amount),2) as payment_amount";

  	$con->sql_query("select p.*, pp.type, pp.adjust, pp.changed, user.u,$select from
  		pos_payment pp
  		left join pos p on p.branch_id = pp.branch_id and p.counter_id = pp.counter_id and p.date= pp.date and p.id = pp.pos_id
  		left join user on p.cashier_id = user.id
      left join pos_items on pos_items.branch_id = p.branch_id and pos_items.counter_id = p.counter_id and pos_items.pos_id = p.id and pos_items.date = p.date
  		where p.date = ".ms($_REQUEST['date'])." and p.cancel_status = '0' and pos_items.sku_item_id = ".ms($_REQUEST['sku'])." and p.branch_id = ".ms($_REQUEST['brn_id'])." group by p.receipt_no");

    $smarty->assign('b',$_REQUEST['brn_id']);
    $smarty->assign('skc',$_REQUEST['sku']);
  	$smarty->assign('items',$con->sql_fetchrowset());
  	$smarty->display('counter_collection.sales_details.tpl');
}

function item_details()
{
    global $con,$smarty;

		$con->sql_query("select p.counter_id, u.u as cashier_name, p.receipt_no, p.pos_time, p.member_no, pi.pos_id, 
						 amount_change, pi.qty, pi.price, pi.discount, pi.barcode, si.mcode, si.sku_item_code, si.description, si.weight, si.color, si.size,
						 si.flavor, si.misc
						 from pos p
						 left join pos_items pi on p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date= pi.date and p.id = pi.pos_id
						 left join sku_items si on pi.sku_item_id = si.id
						 left join user u on u.id = p.cashier_id
						 where p.branch_id = ".mi($_REQUEST['br_id'])." and p.date = ".ms($_REQUEST['date'])." and p.counter_id = ".mi($_REQUEST['counter_id'])." and p.id = ".mi($_REQUEST['pos_id']));
		$items = $con->sql_fetchrowset();
		$smarty->assign('items',$items);

		$smarty->assign("amount_change", $items[0]['amount_change']);

		$con->sql_query("select * from pos_payment where branch_id = ".mi($_REQUEST['br_id'])." and date = ".ms($_REQUEST['date'])." and counter_id = ".mi($_REQUEST['counter_id'])." and pos_id=".mi($items[0]['pos_id']));

		//find sku_code
		$con->sql_query("select sku_item_code from sku_items where id = ".mi($_REQUEST['sku_id']));
		$r = $con->sql_fetchrow();
		$sku_code = $r['sku_item_code'];

		$smarty->assign('sku_code',$sku_code);
		$smarty->assign('payment',$con->sql_fetchrowset());
		$smarty->display('counter_collection.item_details.tpl');
}

function load_sku($tpl="masterfile_sku.edit.tpl")
{
	global $con, $smarty, $sessioninfo, $LANG, $config;

	if($tpl == 'masterfile_sku.view.tpl')	$is_view = 1;
	
	//seraching from po
	if($_REQUEST['from_po']){
		$q1=$con->sql_query("select sku_id from sku_items where id=".mi($_REQUEST['id']));
		$r1 = $con->sql_fetchrow($q1);
		$skuid=$r1['sku_id'];
	}
	elseif (isset($_REQUEST['parent_code']))
	{
	    $con->sql_query("select sku_id from sku_items where sku_item_code = ".ms($_REQUEST['parent_code'])) or die(mysql_error());
	    $skuid = intval($con->sql_fetchfield(0));
		$smarty->assign('to_sku_id', $skuid);
	}
	else
	{
		$skuid = intval($_REQUEST['id']);
	}

	$con->sql_query("select sku.*, vendor.description as vendor, brand.description as brand, branch.code as branch_code, branch.ip as branch_ip from sku left join vendor on sku.vendor_id = vendor.id left join brand on sku.brand_id = brand.id left join branch on apply_branch_id = branch.id where sku.id = ".mi($skuid));
	$form = $con->sql_fetchrow();
	if (!$form){
		if($_REQUEST['a']=='ajax_load_move_sku'){
			echo "<h1 align=center>The SKU does not exits.</h1><p align=center><br><br><input id=btn type=button value='Close' onclick='curtain_clicked();'></p><script>$('btn').focus();</script>";
			exit;
		}
		else{
			$smarty->assign("url", "/home.php");
			$smarty->assign("title", "SKU Application");
			$smarty->assign("subject", sprintf($LANG['SKU_NOT_EXIST'], $skuid));
			$smarty->display("redir.tpl");
		}
		exit;
	}
	
	if($form['is_bom']){
		$sid = $_REQUEST['sid'];
		header("Location: bom.php?a=load_bom_details&sku_bom=".mi($form['id'])."&bom_id=".mi($sid));
		exit;
	}
	
	$category_gst = array();
	if($config['enable_gst']){
		if($form['category_id']){
			$input_tax=get_category_gst("input_tax", $form['category_id'], array('no_check_use_zero_rate'=>1));
			$output_tax=get_category_gst("output_tax", $form['category_id'], array('no_check_use_zero_rate'=>1));

			$category_gst["input_tax"]=$input_tax['id'];
			$category_gst["input_tax_code"]=$input_tax['code'];
			$category_gst["input_tax_rate"]=$input_tax['rate'];
			$category_gst["output_tax"]=$output_tax['id'];
			$category_gst["output_tax_code"]=$output_tax['code'];
			$category_gst["output_tax_rate"]=$output_tax['rate'];
			$category_gst["inclusive_tax"]=get_category_gst("inclusive_tax",$form['category_id']);
		}				
	}
	
	// get image path
	$hurl = get_branch_file_url($form['branch_code'], $form['branch_ip']);
	$smarty->assign("image_path", $hurl);

	$rs2 = $con->sql_query("select si.*, sku_apply_items.photo_count, uom.code as uom,rii.ri_id,ri.group_name as ri_group_name,
		output_gst.code as output_gst_code, output_gst.rate as output_gst_rate,input_gst.code as input_gst_code, input_gst.rate as input_gst_rate,
		if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)) as real_inclusive_tax
	from sku_items si
	left join sku on si.sku_id = sku.id
	left join uom on packing_uom_id = uom.id
	left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
	left join ri_items rii on rii.sku_item_id=si.id
	left join ri on ri.id=rii.ri_id
	left join category_cache cc on cc.category_id=sku.category_id
	left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
	left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
	where si.sku_id = ".mi($skuid)." order by is_parent desc, sku_item_code");
	if ($con->sql_numrows() <= 0)
	{
		$smarty->assign("url", "/home.php");
		$smarty->assign("title", "SKU Application");
		$smarty->assign("subject", sprintf($LANG['SKU_NO_ITEMS'], $skuid));
		$smarty->display("redir.tpl");
		exit;
	}
	//added by gary , load block list from db.
	$sku_item_id_list = array();
	$i=0;
	while($r1=$con->sql_fetchrow($rs2)){
		if (!$r1['reason']) $r1['reason'] = get_reason($r1['id']);
		
		//split article no to artno and size
		split_artno_size($r1);
		
		if($config['enable_gst']){
			if($r1['real_inclusive_tax'] == 'yes'){
				$gst_amt = round($r1['selling_price'] / (100 + $r1['output_gst_rate']) * $r1['output_gst_rate'], 2);
				$gst_selling_price = $r1['selling_price'] - $gst_amt;
			}else{
				$gst_amt = round($r1['selling_price'] * $r1['output_gst_rate'], 2);
				$gst_selling_price = $r1['selling_price'] + $gst_amt;
			}
			$r1['gst_amt'] = $gst_amt;
			$r1['gst_selling_price'] = $gst_selling_price;
		}
		$items[$i]=$r1;
		$items[$i]['block_list']=unserialize($r1['block_list']);
		$items[$i]['doc_block_list']=unserialize($r1['doc_block_list']);
		/*print("sku_photos/a/$r1[id]/*.jpg");
		print_r(glob("sku_photos/a/$r1[id]/*.jpg"));*/
		$items[$i]['photos'] = get_sku_item_photos($r1['id'],$r1);
        $items[$i]['sku_apply_photos'] = get_sku_apply_item_photos($r1['sku_apply_items_id'], $hurl);
		//get promotion image
		$photo_promotion = get_sku_promotion_photos($r1['id'],$r1);
		if($photo_promotion)  $items[$i]['photos_promotion_time'] = filemtime($photo_promotion[0]);
		$items[$i]['photos_promotion'] = $photo_promotion;
		
        $items[$i]['category_disc_by_branch_inherit'] = unserialize($r1['category_disc_by_branch_inherit']);
        $items[$i]['category_point_by_branch_inherit'] = unserialize($r1['category_point_by_branch_inherit']);
		
		$branch_list = load_branch_list();
		foreach($branch_list as $bid => $data){
			$sql_str1 = "select grn_cost from sku_items_cost sic where sic.sku_item_id = " . mi($r1["id"]) . " and sic.branch_id = " . mi($bid);
			$sql1 = $con->sql_query($sql_str1);
			
			if ($con->sql_numrows() > 0){
				while($result1=$con->sql_fetchassoc($sql1)){
					$items[$i]["all_branch_cost"][$data["code"]]["code"] = $data["code"]; 
					$items[$i]["all_branch_cost"][$data["code"]]["latest_cost"] = $result1["grn_cost"]; 
				}
				$con->sql_freeresult($sql1);
			}else{
				
				$sql_str2 = "select cost_price from sku_items si where si.id = " . mi($r1["id"]);
				$sql2 = $con->sql_query($sql_str2);
				while($result2=$con->sql_fetchassoc($sql2)){
					$items[$i]["all_branch_cost"][$data["code"]]["code"] = $data["code"]; 
					$items[$i]["all_branch_cost"][$data["code"]]["latest_cost"] = $result2["cost_price"]; 
				}
				$con->sql_freeresult($sql2);
			}
		}
		
		if($config['sku_enable_additional_description'] && $r1['additional_description']) $items[$i]['additional_description'] = join("\n", unserialize($r1['additional_description']));
		
		if($config['sku_extra_info']){
			$con->sql_query("select * from sku_extra_info where sku_item_id=".mi($r1['id']));
			$items[$i]['extra_info'] = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		
		// Not Allow to change Packing UOM if sku got grn
		if(!$is_view && !$sessioninfo['is_arms_user']){
			$con->sql_query("select * from vendor_sku_history where sku_item_id=".mi($r1['id'])." limit 1");
			$got_grn = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($got_grn){
				$items[$i]['disable_edit_packing_uom'] = 1;
			}
		}
		
		// Check if branch got change selling price
		if(!$is_view){
			$con->sql_query("select sip.* 
				from sku_items_price sip
				join branch b on b.id=sip.branch_id
				where b.active=1 and sip.sku_item_id=".mi($r1['id'])."
				limit 1");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp){
				$items[$i]['got_change_price'] = 1;
			}
		}
		
		if(!in_array($r1['id'],$sku_item_id_list)){
            $sku_item_id_list[] = $r1['id'];
		}
		$i++;
	}

	//echo"<pre>";print_r($items);echo"</pre>";
	$smarty->assign("items", $items);

	$con->sql_query("select tree_str, description from category where id = ".mi($form['category_id']));
	// get category tree
	$r = $con->sql_fetchrow();
	$form['category'] = strtoupper($r['description']);
	$form['cat_tree'] = htmlentities(get_category_tree($form['category_id'], $r['tree_str'], $dummy) . " > " . $r['description']);

	// get discount table
	if ($form['trade_discount_type']>0)
	{
		$ff = array();
		$ff['branch_id'] = $form['apply_branch_id'];
		$ff['category_id'] = $form['category_id'];
		if ($form['trade_discount_type']==1)
			$ff['brand_id'] = $form['brand_id'];
		else
			$ff['vendor_id'] = $form['vendor_id'];
		$form['trade_discount_table'] = load_discount_table($ff);
	}
	//added by gary , load block list from db.
	// $form['block_list'] = unserialize($form['block_list']);
	if($_REQUEST['a']=='ajax_load_move_sku'){
		$smarty->assign("request", $_REQUEST);
	}
	
	$form['po_reorder_qty_by_branch'] = unserialize($form['po_reorder_qty_by_branch']);
	
	// load dummy data for matrix
	if($config['enable_one_color_matrix_ibt']){
		$form['use_matrix'] = "yes";
		$clr_list = get_matrix_color();
		$size_list = get_matrix_size();
		
		if($clr_list && $size_list){
			foreach($clr_list as $arr1=>$clr){
				foreach($size_list as $arr2=>$size){
					$min_qty = rand(1,5);
					$max_qty = rand(5,10);
					$is_nnr = rand(0,1);
					$form['matrix']['is_nnr'][$size] = $is_nnr;
					$form['matrix']['min_qty'][$clr][$size] = $min_qty;
					$form['matrix']['max_qty'][$clr][$size] = $max_qty;
				}
			}
		}
	}
	
	// Not Allow to edit SKU Type sku got used in documents or sales
	if(!$is_view && !$sessioninfo['is_arms_user']){
		if($sku_item_id_list){
			$con->sql_query("select * from sku_items_cost where sku_item_id in (".join(',', $sku_item_id_list).") and date>0 limit 1");
			$got_used = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($got_used){
				$form['disable_edit_sku_type'] = 1;
			}	
		}
	}
	
	$smarty->assign("form", $form);

	//echo"<pre>";print_r($items);echo"</pre>";
	$con->sql_query("select id,code from trade_discount_type order by code");
	$smarty->assign("trade_discount_table", $con->sql_fetchrowset());

	$con->sql_query("select id,code from branch where active=1 order by sequence,code");
	$smarty->assign("branch", $con->sql_fetchrowset());
	
	$con->sql_query("select u.* 
					 from user u
					 left join user_privilege up on u.id=up.user_id 
					 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

	while($r = $con->sql_fetchassoc()){
		$po_reorder_users[$r['id']] = $r;
	}
	$con->sql_freeresult();

	$smarty->assign("po_reorder_users", $po_reorder_users);

	$smarty->assign('category_gst', $category_gst);
	
	$smarty->display($tpl);
	exit;
}


function get_reason($id)
{
	global $con;

	$rs10 = $con->sql_query("select log.*, user.u from log left join user on user.id = log.user_id where rid = ".mi($id)." and type = 'MASTERFILE_SKU_ACT' order by timestamp desc limit 1");

	$r = $con->sql_fetchassoc($rs10);
	
	$con->sql_freeresult($rs10);

	return $r;
}

function load_discount_table($ff)
{
	global $con;
	$branch_id = $ff['branch_id'];
	if (isset($ff['brand_id']))
	{
		$did = get_department_id($ff['category_id']);
		$rs1 = $con->sql_query("select skutype_code, rate from brand_commission where branch_id = ".mi($branch_id)." and brand_id = ".mi($ff['brand_id'])." and department_id = ".mi($did));
	}
	elseif (isset($ff['vendor_id']))
	{
		$did = get_department_id($ff['category_id']);
		$rs1 = $con->sql_query("select skutype_code, rate from vendor_commission where branch_id = ".mi($branch_id)." and vendor_id = ".mi($ff['vendor_id'])." and department_id = ".mi($did));
	}

	$ret = array();
	while($r=$con->sql_fetchrow($rs1))
	{
		$ret[$r[0]] = $r[1];
	}
	return $ret;
}

function save_sku(){
	global $con, $smarty, $sessioninfo, $LANG, $config, $appCore;

	$form = $_REQUEST;

	$form['varieties'] = count($form['artno']);
	$skuid = mi($form['id']);
	
	/*
	//pick up original_form for check change log edit field
	$con->sql_query("select * from sku where id=".mi($form['id']));
	$original_form = $con->sql_fetchassoc();
	$con->sql_freeresult();			
		
	$edited_fields = array();
	foreach($original_form as $col=>$old_value){
		if(isset($form[$col]) && $form[$col] != $old_value && $col!='po_reorder_notify_user_id' && $col!='active' && $col!='po_reorder_qty_by_branch' && $col!='varieties'){
			$edited_fields[] = $col." = ".$form[$col];
		}
	}*/
	
	// Checking top sku form error here
	if (!$form['sku_type'])
		$errm['top'][] = $LANG['SKU_INVALID_TYPE'];

	// check if selected trade_discount_table is zero or no value
	if(!$config['sku_always_show_trade_discount']){
		if (($form['trade_discount_type']>0 || $form['trade_discount_table'][$form['default_trade_discount_code']] == 0) && $form['sku_type'] == "CONSIGN"){
			if ($form['trade_discount_table'][$form['default_trade_discount_code']] == '')
			{
				// checking if trade discount value=0 or empty
				$errm['top'][] = $LANG['SKU_INVALID_TRADE_DISCOUNT_TABLE'];
			}
			
			if ($form['trade_discount_table'][$form['default_trade_discount_code']] == 0 && $form['default_trade_discount_code'] != 'PWP')
			{
				// checking if trade discount value=0 or empty
				$errm['top'][] = $LANG['SKU_TRADE_DISCOUNT_VALUE_IS_ZERO'];
			}
		}
	}

	$items = array();
	$errm = validate_data($form, $items);

	if($errm){
		//print_r($items);
		$con->sql_query("select tree_str, description from category where id = $form[category_id]");
		$r = $con->sql_fetchrow();
		$form['cat_desc'] = strtoupper($r['description']);
		$form['cat_tree'] = htmlentities(get_category_tree($form['category_id'], $r['tree_str'], $dummy)  . " > " . $r['description']);

		$con->sql_query("select id,code from trade_discount_type order by code");
		$smarty->assign("trade_discount_table", $con->sql_fetchrowset());
		
		$con->sql_query("select id,code from branch where active=1 order by sequence,code");
		$smarty->assign("branch", $con->sql_fetchrowset());
		
		$con->sql_query("select u.* 
						 from user u
						 left join user_privilege up on u.id=up.user_id 
						 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

		while($r = $con->sql_fetchassoc()){
			$po_reorder_users[$r['id']] = $r;
		}
		$con->sql_freeresult();

		$smarty->assign("po_reorder_users", $po_reorder_users);

		$con->sql_query("select sku.*, branch.code as branch_code, branch.ip as branch_ip from sku left join vendor on sku.vendor_id = vendor.id left join brand on sku.brand_id = brand.id left join branch on apply_branch_id = branch.id where sku.id = ".mi($form['id']));
		$sku_info = $con->sql_fetchrow();
		$form['sku_code'] = $sku_info['sku_code'];
		$form['branch_code'] = $sku_info['branch_code'];

		// get image path
		$hurl = get_branch_file_url($form['branch_code'], $form['branch_ip']);
		$smarty->assign("image_path", $hurl);

		$rs2 = $con->sql_query("select sku_items.*, sku_apply_items.photo_count, uom.code as uom,rii.ri_id,ri.group_name as ri_group_name
		from sku_items
		left join uom on packing_uom_id = uom.id
		left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
		left join ri_items rii on rii.sku_item_id=sku_items.id
		left join ri on ri.id=rii.ri_id
		where sku_items.sku_id = ".mi($form['id'])." order by is_parent desc, sku_item_code");

		//added by gary , load block list from db.
		$sku_item_id_list = array();
		$i=0;
		while($r1=$con->sql_fetchrow($rs2)){
			if (!$r1['active'] && !$items[$i]['is_new']) $items[$i]['reason'] = get_reason($r1['id']);

			$items[$i]['photos'] = get_sku_item_photos($r1['id'],$r1);
			$items[$i]['sku_apply_photos'] = get_sku_apply_item_photos($r1['sku_apply_items_id'], $hurl);
			
			if(!in_array($r1['id'],$sku_item_id_list)){
				$sku_item_id_list[] = $r1['id'];
			}
			$i++;
		}

		// get discount table
		if ($form['trade_discount_type']>0)
		{
			$ff = array();
			$ff['branch_id'] = $form['apply_branch_id'];
			$ff['category_id'] = $form['category_id'];
			if ($form['trade_discount_type']==1)
				$ff['brand_id'] = $form['brand_id'];
			else
				$ff['vendor_id'] = $form['vendor_id'];
			$form['trade_discount_table'] = load_discount_table($ff);
		}
		
		$smarty->assign("form", $form);

		//split artno to artno and size
		foreach ($items as $k => $item){
			split_artno_size($item);		
			$items[$k]['artno']=$item['artno'];
			$items[$k]['artsize']=$item['artsize'];
		}

		$smarty->assign("items", $items);
		$smarty->assign("errm", $errm);
		$smarty->display('masterfile_sku.edit.tpl');
		exit;
	}
   	/*if ($err){
		$smarty->assign('errm',$err);
		load_sku();
		exit;
	}*/

	//echo"<pre>";print_r($form);echo"</pre>";
	//added by gary serialize the block branches
	//$form['block_list'] = serialize($form['block_list']['sku']);

	// check category changed
	$con->sql_query("select * from sku where id=".mi($form['id']));
	$old_sku = $con->sql_fetchrow();
	if($old_sku['no_inventory']!=$form['no_inventory']) $no_inventory_update = true;
	$old_cat_id = mi($old_sku['category_id']);
	if($old_cat_id!=$form['category_id']){ // got change category
	    update_category_changed($old_cat_id);
	    update_category_changed($form['category_id']);
  	}
  	
  	$update_field =  array("apply_branch_id", "category_id", "sku_type", "vendor_id", "brand_id", "trade_discount_type", "default_trade_discount_code", "multics_dept", "multics_section", "multics_category", "multics_brand", "multics_pricetype", "varieties", "note", 'no_inventory', 'is_fresh_market', 'po_reorder_qty_min', 'po_reorder_qty_max','po_reorder_moq','scale_type', 'po_reorder_notify_user_id', 'po_reorder_by_child');

  	// check if the sku type is outright, reset the default trade discount code and trade discount type
  	if($form['sku_type'] != "CONSIGN"){
	  	$form['default_trade_discount_code'] = '';
	  	$form['trade_discount_type'] = 0;
	}
	
	if(!$form['po_reorder_by_child']){
		if($form['po_reorder_qty_setup'] && $form['po_reorder_qty_by_branch']){
			$form['po_reorder_qty_by_branch'] = serialize($form['po_reorder_qty_by_branch']);
		}else $form['po_reorder_qty_by_branch'] = "";
	
		$update_field[] = "po_reorder_qty_by_branch";
	}
  	
  
	if($config['enable_sn_bn']) $update_field[] = "have_sn";
	if($config['sku_non_returnable'])	$update_field[] = "group_non_returnable";
	
	if($config['enable_gst']){
		$update_field[] = "mst_input_tax";
		$update_field[] = "mst_output_tax";
		$update_field[] = "mst_inclusive_tax";
	}
    $update_field[] = "parent_child_duplicate_mcode";

	$con->sql_query("update sku set " . mysql_update_by_field($form, $update_field) . " where id = " . mi($form['id']));

	if ($con->sql_affectedrows()){
	    //$con->sql_query("select sku_item_code from sku_items where is_parent=1 and sku_id =".mi($form['id']));
		//$r=$con->sql_fetchrow();
		log_br($sessioninfo['id'], 'MASTERFILE', $form['id'], "SKU General Information updated: (SKU ID#$form[id])");
	}
	
/*	if (isset($form['is_new']))
	{
		$findcode = (28000000 + intval($form['id'])) . '%';
		// yinsee - removed: sku_id = ".mi($form['id']) . " and
		$con->sql_query("select max(sku_item_code) from sku_items where sku_item_code like '$findcode'");
		$r  = $con->sql_fetchrow();
		if (!$r)
		{
			die("System Error: Failed to retrieve last sku_item_code from sku $form[id]");
			exit;
		}
		$last_item_code = $r[0];
		// when sku_id and arms code is not same...
		if ($last_item_code=='') $last_item_code = sprintf('28%06d0000',intval($form['id']));
	}
*/

	//check duplicate color and size
    $n=0;
    $check=array();
    $matrix_check=array();
	/*foreach ($form['artno'] as $i=>$dummy)
	{
		if ($_REQUEST['description2'][$i] != '' && $_REQUEST['description3'][$i] != ''){
		    foreach ($check as $vsc){
				if ($vsc['size']==$form['description2'][$i] && $vsc['color']==$form['description3'][$i]){
					$errs['top'][]=sprintf($LANG['SKU_COLOR_SIZE_DUPLICATE'],$form['description3'][$i],$form['description2'][$i]);

					break;
				}
			}
	 		$check[$n]['size'] = $form['description2'][$i];
	        $check[$n]['color'] = $form['description3'][$i];
	        ++$n;
		}

		for ($r=1; $r<count($form['tb'][$i]); $r++)
		{

			for ($c=1; $c<count($form['tb'][$i][0]); $c++)
			{

				if ($form['tb'][$i][0][$c] == '' || $form['tb'][$i][$r][0] == '') continue;

		   	    foreach ($check as $msc){
					if ($msc['size']==$form['tb'][$i][$r][0] && $msc['color']==$form['tb'][$i][0][$c]){
						$errs['top'][]=sprintf($LANG['SKU_COLOR_SIZE_DUPLICATE'],$form['tb'][$i][0][$c],$form['tb'][$i][$r][0]);
						break;
					}
				}

				$check[$n]['size'] = $form['tb'][$i][$r][0];
			    $check[$n]['color'] = $form['tb'][$i][0][$c];
	            ++$n;
			}
		}
	}*/

	/*
	//pick up original_form for check change log edit field
	$skuitems_is_parent = 0;
	$con->sql_query($qry="select * from sku_items where sku_id=".mi($form['id']));
	while($r2 = $con->sql_fetchassoc()){
		$original_form2[] = $r2;
	}
	foreach($original_form2 as $row=>$r){
		foreach($r as $column=>$value){
			$ori_value[$r['id']][$column] = $value;
		}
	}
	$con->sql_freeresult();

	$edited_fields2 =array();
	foreach($ori_value as $id=>$val){
		$val['additional_description'] = !empty($val['additional_description']) ? implode(unserialize($val['additional_description'])) : $val['additional_description'];
		$val['category_disc_by_branch_inherit'] = !empty($val['category_disc_by_branch_inherit']) ? implode(unserialize($val['category_disc_by_branch_inherit'])) : $val['category_disc_by_branch_inherit'];
		$val['category_point_by_branch_inherit'] = !empty($val['category_point_by_branch_inherit']) ? implode(unserialize($val['category_point_by_branch_inherit'])) : $val['category_point_by_branch_inherit'];
		$val['block_list'] = !empty($val['block_list']) ? unserialize($val['block_list']) : $val['block_list'];
		$val['doc_block_list'] = !empty($val['doc_block_list']) ? unserialize($val['doc_block_list']) : $val['doc_block_list'];
		if(is_array($form['block_list'][$id])){
			if(is_array($val['block_list'])){
				$result_block_list = array_diff_key(array_keys($form['block_list'][$id]),array_keys($val['block_list']));
				if(!empty($result_block_list)){
					$form['block_list'][$id] =implode(",",$result_block_list);
				}else{
					$result_block_list = array_diff_key(array_keys($val['block_list']),array_keys($form['block_list'][$id]));
					if(!empty($result_block_list)){
						$form['block_list'][$id] ='';
					}
				}
			}else{
				$form['block_list'][$id] =implode(",",array_keys($form['block_list'][$id]));
			}
		}else{
			if(is_array($val['block_list'])){
				$form['block_list'][$id] =implode(",",array_keys($val['block_list']));
			}
		}
		
		
		if(is_array($form['doc_block_list'][$id])){
			if(is_array($val['doc_block_list'])){
				$result_doc_block_list = array_diff_key(array_keys($form['doc_block_list'][$id]),array_keys($val['doc_block_list']));
				if(!empty($result_doc_block_list)){
					$form['doc_block_list'][$id] =implode(",",$result_doc_block_list);
				}else{
					$result_doc_block_list = array_diff_key(array_keys($val['doc_block_list']),array_keys($form['doc_block_list'][$id]));
					if(!empty($result_doc_block_list)){
						$form['doc_block_list'][$id] ='';
					}
				}
			}else{
				$form['doc_block_list'][$id] =implode(",",array_keys($form['doc_block_list'][$id]));
			}
		}else{
			if(is_array($val['doc_block_list'])){
				$form['doc_block_list'][$id] =implode(",",array_keys($val['doc_block_list']));
			}
		}

		foreach($val as $col =>$val2){
			if(isset($form[$col][$id]) && $form[$col][$id] != $val2 && $col != 'sn_we_type'){
				$edited_fields2[$id][] = $col." = ".$form[$col][$id];
			}
		}
	}*/

	foreach ($form['artno'] as $k=>$dummy)
	{
		$item = array();
		$item['description'] = $form['description'][$k];
	 	$item['active'] = $form['active'][$k];
		//$item['artno'] = $form['artno'][$k];

		//join artno and size while save   
	    $join_art_size[]=strtoupper(trim($form['artno'][$k]));
	    $join_art_size[]=strtoupper(trim($form['artsize'][$k]));
		$item['artno'] = join(" ",$join_art_size);
		// temporary dun put this checking since they no complaint
		//if (!$config['sku_artno_allow_specialchars']) $item['artno'] = preg_replace("/[^A-Z0-9]/", "", $item['artno']);
		unset($join_art_size);

		$item['packing_uom_id'] = $form['packing_uom_id'][$k];
		$item['mcode'] = $form['mcode'][$k];
		// temporary dun put this checking since they no complaint
		//if (!$config['sku_artno_allow_specialchars']) $item['mcode'] = preg_replace("/[^A-Z0-9]/", "", $item['mcode']);
		
		$item['link_code'] = $form['link_code'][$k];
		$item['receipt_description'] = $form['receipt_description'][$k];
 		$item['open_price'] = $form['open_price'][$k];
 		$item['location'] = $form['location'][$k];
 		$item['decimal_qty'] = $form['decimal_qty'][$k];
 		$item['doc_allow_decimal'] = mi($form['doc_allow_decimal'][$k]);
 		$item['block_list'] = serialize($_REQUEST['block_list'][$k]); //$form[block_list] is serliazed from blocklist[sku], canot use.
 		$item['doc_block_list'] = serialize($_REQUEST['doc_block_list'][$k]);
		$item['selling_price'] = $form['selling_price'][$k];
		if($config['enable_gst']){
			$item['input_tax'] = $form['dtl_input_tax'][$k];
			$item['output_tax'] = $form['dtl_output_tax'][$k];
			$item['inclusive_tax'] = $form['dtl_inclusive_tax'][$k];
		}
 		$item['cost_price'] = $form['cost_price'][$k];
 		$item['weight'] = $form['description1'][$k];
 		$item['size'] = $form['description2'][$k];
        $item['color'] = $form['description3'][$k];
        $item['flavor'] = $form['description4'][$k];
        $item['misc'] = $form['description5'][$k];
        $item['allow_selling_foc'] = mi($form['allow_selling_foc'][$k]);
        $item['selling_foc'] = mi($form['selling_foc'][$k]);
		if($item['selling_price'] <=0 && $item['allow_selling_foc'])	$item['selling_foc'] = 1;
        $item['not_allow_disc'] = mi($form['not_allow_disc'][$k]);
        $item['weight_kg'] = mf($form['weight_kg'][$k]);
        $item['model'] = $form['model'][$k];
        $item['width'] = $form['width'][$k];
        $item['height'] = $form['height'][$k];
        $item['length'] = $form['length'][$k];
		
        $item['scale_type'] = mi($form['dtl_scale_type'][$k]);
        $tmp_extra_info = $form['extra_info'][$k];
        if($config['sku_non_returnable'])	$item['non_returnable'] = mi($form['non_returnable'][$k]);
        
        // category discount
        // inherit option
        $item['cat_disc_inherit'] = trim($form['cat_disc_inherit'][$k]);
        
        // inherit value
        if($item['cat_disc_inherit']!='set'){
			$form['category_disc_by_branch_inherit'][$k] = array();
		}
        $item['category_disc_by_branch_inherit'] = serialize($form['category_disc_by_branch_inherit'][$k]);
                
        // category reward point
        $item['category_point_inherit'] = trim($form['category_point_inherit'][$k]);
        if($item['category_point_inherit']!='set'){
			$form['category_point_by_branch_inherit'][$k] = array();
		}
        
        $item['category_point_by_branch_inherit'] = serialize($form['category_point_by_branch_inherit'][$k]);
		/*if($config['masterfile_enable_return_policy']){
			$item['return_policy'] = serialize($form['dtl_return_policy'][$k]);
		}*/
        
		if($config['enable_replacement_items'])		$ri_id = $form['ri_id'][$k];

		
		/*$err = check_error($k,'','');

		if (isset($err) && isset($errs)){
			$err=array_merge_recursive($errs,$err);
		}
		elseif (!isset($err)){
			$err=$errs;
		}

		if ($err){
			$smarty->assign('errm',$err);
			load_sku();
			exit;
		}*/

		if($config['masterfile_sku_enable_ctn']){
			$item['ctn_1_uom_id'] = $form['ctn_1_uom_id'][$k];
			$item['ctn_2_uom_id'] = $form['ctn_2_uom_id'][$k];
		}

		if($config['sku_listing_show_hq_cost']&&BRANCH_CODE=='HQ'){
			$item['hq_cost'] = $form['hq_cost'][$k];
		}
		
		if($config['do_enable_hq_selling']&&BRANCH_CODE=='HQ'){
			$item['hq_selling'] = $form['hq_selling'][$k];
		}
		
		if($config['sku_enable_additional_description']){
			$additional_description_list = $additional_description = array();
			$additional_description_list = explode("\n", trim($form['additional_description'][$k]));
			foreach($additional_description_list as $tmp_r=>$add_desc){
				if(!trim($add_desc)) continue;
				$additional_description[] = trim($add_desc);
			}
			if($additional_description) $item['additional_description'] = serialize($additional_description);
			else $item['additional_description'] = "";
			$item['additional_description_print_at_counter'] = $form['additional_description_print_at_counter'][$k];
			$item['additional_description_prompt_at_counter'] = $form['additional_description_prompt_at_counter'][$k];
		}
		
		if($form['po_reorder_by_child']){
			$item['po_reorder_qty_min'] = $form['si_po_reorder_qty_min'][$k];
			$item['po_reorder_qty_max'] = $form['si_po_reorder_qty_max'][$k];
			$item['po_reorder_moq'] = $form['si_po_reorder_moq'][$k];
			$item['po_reorder_notify_user_id'] = $form['si_po_reorder_notify_user_id'][$k];
		}
		
		if($config['enable_sn_bn'] && $form['sn_we'][$k]){
			$item['sn_we'] = $form['sn_we'][$k];
			$item['sn_we_type'] = $form['sn_we_type'][$k];
		}
		
		if(privilege('MST_INTERNAL_DESCRIPTION')){
			$item['internal_description'] = $form['internal_description'][$k];
		}
		
		if($config['arms_marketplace_settings']){
			$item['marketplace_description'] = $form['marketplace_description'][$k];
		}
		
		// RSP
		$item['use_rsp'] = mi($form['use_rsp'][$k]);
		$item['rsp_price'] = mf($form['rsp_price'][$k]);
		$item['rsp_discount'] = trim($form['rsp_discount'][$k]);

		if (!is_numeric($k))
		{
		    die ("Error: Item ID#$k is invalid");
		}

		if (isset($form['is_new'][$k]))
		{

			$findcode = (28000000 + intval($form['id'])) . '%';
			// yinsee - removed: sku_id = ".mi($form['id']) . " and
			$con->sql_query("select max(sku_item_code) from sku_items where sku_item_code like '$findcode'");
			$r  = $con->sql_fetchrow();
			if (!$r)
			{
				die("System Error: Failed to retrieve last sku_item_code from sku $form[id]");
				exit;
			}
			$last_item_code = $r[0];
			// when sku_id and arms code is not same...
			if ($last_item_code=='') $last_item_code = sprintf('28%06d0000',intval($form['id']));

	    	if ($last_item_code == '')
			{
				die("System Error: This should not happen but just in case it did....");
				exit;
			}
			$item['sku_id'] = $form['id'];
			$item['sku_item_code'] = ++$last_item_code;
			$item['added'] = date("Y-m-d H:i:s");

			if ($form['item_type'][$k] == "variety"){

				$con->sql_query("insert into sku_items " . mysql_insert_by_field($item));
				$sid = $con->sql_nextid();
				if($config['enable_replacement_items'])	if($ri_id)  change_item_replacement_group($ri_id, $sid);
				if ($sid)
				{
					log_br($sessioninfo['id'], 'MASTERFILE', $sid, "SKU Item $item[sku_item_code] added (ID#$sid)");
				}
			}
			else{
				if ($form['own_article'][$k])
					$share_artmcode = 0;
				else
				    $share_artmcode = 1;

		        $description = $item['description'];
		        $rdescription = $item['receipt_description'];

				for ($r=1; $r<count($form['tb'][$k]); $r++)
				{
					for ($c=1; $c<count($form['tb'][$k][0]); $c++)
					{
						if ($form['tb'][$k][0][$c] == '' || $form['tb'][$k][$r][0] == '') continue;

						$item['size'] = $form['tb'][$k][$r][0];
					    $item['color'] = $form['tb'][$k][0][$c];
					    $item['description'] = $description . " " . $form['tb'][$k][$r][0] . " " . $form['tb'][$k][0][$c];
					    $item['receipt_description'] = $rdescription . " " . $form['tb'][$k][$r][0] . " " . $form['tb'][$k][0][$c];
	                    $item['selling_price'] = $form['tbprice'][$k][$r];
	                    $item['hq_selling'] = $form['tbhqprice'][$k][$r];
	                    $item['cost_price'] = $form['tbcost'][$k][$r];
	                    $item['hq_cost'] = $form['tbhqcost'][$k][$r];

						if (!$share_artmcode)
						{
							$item['artno'] = $form['tb'][$k][$r][$c];
							$item['mcode'] = $form['tbm'][$k][$r][$c];
					    }

					    //$err=check_error($k,$r,$c);

						if ($err){
							$smarty->assign('errm',$err);
							load_sku();
							exit;
						}

		   				$con->sql_query("insert into sku_items " . mysql_insert_by_field($item, array("sku_id", "sku_item_code", "artno", "mcode", "description", "receipt_description", "selling_price", "cost_price", "added",'hq_cost','size','color','hq_selling')));
		    			$item['sku_item_code'] = ++$last_item_code;
					}
				}
			}
		}
		else{
			$con->sql_query("update sku_items set ".mysql_update_by_field($item)." where id =  ".mi($k));
			$items_updated = $con->sql_affectedrows();
			
			if ($items_updated || $no_inventory_update)
			{
				if($items_updated){
					// change from non rsp to rsp
					if($item['use_rsp'] && !$item['ori_use_rsp']){
						$q_sip = $con->sql_query("select sip.branch_id, sip.price, sip.rsp_discount, ifnull(sic.grn_cost, si.cost_price) as cost, sip.trade_discount_code, sip.selling_price_foc
							from sku_items si
							join sku_items_price sip on sip.sku_item_id=si.id
							join branch b on b.id=sip.branch_id
							left join sku_items_cost sic on sic.branch_id=b.id and sic.sku_item_id=si.id
							where b.active=1 and si.id=".mi($k));
						while($sip = $con->sql_fetchassoc($q_sip)){
							// Check if price got rsp_discount
							if(round($sip['price'], 2) != round($item['selling_price'], 2) || trim($sip['rsp_discount']) != $item['rsp_discount']){
								// Got different, need to do price change
								$upd_siph = array();
								$upd_siph['branch_id'] = $sip['branch_id'];
								$upd_siph['sku_item_id'] = $k;
								$upd_siph['price'] = round($item['selling_price'], 2);
								$upd_siph['cost'] = $item['cost'];
								$upd_siph['trade_discount_code'] = $sip['trade_discount_code'];
								$upd_siph['source'] = 'SKU';
								$upd_siph['user_id'] = 1;
								$upd_siph['selling_price_foc'] = $sip['selling_price_foc'];
								$upd_siph['rsp_discount'] = $item['rsp_discount'];
								
								$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($upd_siph));
								
								$upd_sip = array();
								$upd_sip['branch_id'] = $sip['branch_id'];
								$upd_sip['sku_item_id'] = $k;
								$upd_sip['price'] = round($item['selling_price'], 2);
								$upd_sip['cost'] = $item['cost'];
								$upd_sip['trade_discount_code'] = $sip['trade_discount_code'];
								$upd_sip['selling_price_foc'] = $sip['selling_price_foc'];
								$upd_sip['rsp_discount'] = $item['rsp_discount'];
								
								$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd_sip));
							}
						}
						$con->sql_freeresult($q_sip);
					}
				}
				
				// trigger cost changed
				$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = ".mi($k));
				$con->sql_query("select sku_item_code from sku_items where id = ".mi($k));
				$si = $con->sql_fetchrow();
				$con->sql_freeresult();
				
				log_br($sessioninfo['id'], 'MASTERFILE', $k, "SKU Item $si[0] updated (ID#$k)");
			}
			if($config['enable_replacement_items'])	change_item_replacement_group($ri_id, $k);
			$sid = $k;
		}
		
		// generate extra info table
		generate_sku_extra_info($sid, $tmp_extra_info);
		

		if (!$form['active'][$k] && $form['reason'][$k] <> '' && $form['reason'][$k] <> get_reason($k))
		{
			log_br($sessioninfo['id'], 'MASTERFILE_SKU_ACT', $k, $form['reason'][$k]);
		}
	}
	
	// reupdate all sku_items weight
	$appCore->skuManager->reupdateSKUWeight($skuid);
	
	header("Location: $_SERVER[PHP_SELF]?a=sku_updated_redirect");

	exit;
}

function load_branches($sqlonly=false){
    global $smarty, $con, $sessioninfo;

	if(BRANCH_CODE == 'HQ'){
	    if ($_REQUEST['branches_group']){
	        $filter = " and bgi.branch_group_id = ".mi($_REQUEST['branches_group']);
	    }
		$con->sql_query("select branch.* from branch
						left join branch_group_items bgi on bgi.branch_id=branch.id
						where branch.active=1 $filter order by branch.sequence");
	}else{
	    $con->sql_query("select * from branch where id=".mi($sessioninfo['branch_id']));
	    $_REQUEST['branch_id']=$sessioninfo['branch_id'];
   }
	while($r = $con->sql_fetchrow()){
		if ($sqlonly) $branches[$r['id']] = $r['id'];
		else	$branches[$r['id']] = $r;
	}
	
	//check branch id request is in branch group
	if (!$branches[$_REQUEST['branch_id']])
		unset($_REQUEST['branch_id']);

	$con->sql_freeresult();

	if ($sqlonly)
		return $branches;
	else
	    $smarty->assign("branches", $branches);
}

function load_branches_group($id=0){
	global $con,$smarty;
	// check whether select all or specified group
	$where2 = "where branch.active=1";
	if($id>0){
		$where = "where id=".mi($id);
		$where2 .= " and bgi.branch_group_id=".mi($id);
	}
	// load header
	$con->sql_query("select * from branch_group $where");
	$branches_group['header'] = $con->sql_fetchrowset();

	// load items
	$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id $where2 order by branch.sequence, branch.code");
	while($r = $con->sql_fetchrow($q1)){
        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$con->sql_freeresult($q1);
	$smarty->assign('branches_group',$branches_group);
	return $branches_group;
}

function get_branch_sku(){
	global $con,$smarty;

	$group_id = intval($_REQUEST['group_id']);
	$sku_item_id = intval($_REQUEST['sku_item_id']);
	$group_by_sku = intval($_REQUEST['group_by_sku']);

	$branches_group = array();
	$branches_group = load_branches_group($group_id);

	$sql = "select * 
			from sku_items 
			where id=$sku_item_id";
	$q1 = $con->sql_query($sql) or die(mysql_error());
	$sku_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// loop every branch from this group for stock balance, selling and cost prices
	foreach($branches_group['items'][$group_id] as $bid=>$tmp){
		$sic_filters = array();
		$sic_filters[] = "sic.branch_id = ".mi($bid);
		if($group_by_sku && $sku_info['is_parent']){ // filter by SKU, because need to get the total qty from parent child
			$sic_filters[] = "si.sku_id = ".mi($sku_info['sku_id']);
		}else{ // only parent qty
			$sic_filters[] = "si.id = ".mi($sku_info['id']);
		}
		
		// get stock balance
		$q1 = $con->sql_query("select sic.*, u.fraction as uom_fraction
							   from sku_items_cost sic
							   left join sku_items si on si.id = sic.sku_item_id
							   left join sku on sku.id = si.sku_id
							   left join uom u on u.id = si.packing_uom_id
							   where ".join(" and ", $sic_filters));

		while($r = $con->sql_fetchassoc($q1)){
			if($sku_info['id'] == $r['sku_item_id']){ // is current sku item
				if(!$r['grn_cost']) $r['grn_cost'] = $sku_info['cost_price'];
				$branch_sku_info[$bid]['cost_price'] = $r['grn_cost'];
				$branch_sku_info[$bid]['changed'] = $r['changed'];
			}
			
			if($group_by_sku && $sku_info['is_parent']){
				// do nth
			}else $r['uom_fraction'] = 1; // need to do this while view on child item
			$branch_sku_info[$bid]['stock_balance'] += ($r['qty'] * $r['uom_fraction']);
		}
		$con->sql_freeresult($q1);
							   
		// get current selling price
		$q1 = $con->sql_query("select sip.* 
							   from sku_items_price sip
							   where sip.sku_item_id = ".mi($sku_info['id'])." and sip.branch_id = ".mi($bid));
		$pinfo = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if(!$pinfo['price']) $pinfo['price'] = $sku_info['selling_price'];
		$branch_sku_info[$bid]['selling_price'] = $pinfo['price'];
	}
	
	$smarty->assign('sku_info',$sku_info);
	$smarty->assign('branch_sku_info',$branch_sku_info);
	$smarty->display('masterfile_sku.branch_group.tpl');
}

function reindex_is_parent(){
    global $con;

	$updated_row = 0;
        $reset_row = 0;
	$cannot_update = 0;
	set_time_limit(0);

	$q_1 = $con->sql_query("select distinct(sku_id) from sku_items") or die(mysql_error());
	while($row = $con->sql_fetchrow($q_1)){
		$sku_id = mi($row[0]);
		$q_2 = $con->sql_query("select * from sku_items where sku_id = ".$sku_id." and is_parent=1") or die(mysql_error());
                $parent_row = $con->sql_numrows($q_2);
		if($parent_row!=1){
                        if($parent_row>1){ // have more than 1 parent
                            // set all items is_parent=0
                            $con->sql_query("update sku_items set is_parent=0 where sku_id = ".$sku_id) or die(mysql_error());
                            $reset_row++;
                        }

			$con->sql_query("update sku_items set is_parent=1 where sku_id = ".$sku_id." and packing_uom_id=1 order by sku_item_code,id limit 1") or die(mysql_error());
			if($con->sql_affectedrows())	$updated_row++;
			else{
				// failed to make parent due to no uom_id=1
				$q_3 = $con->sql_query("select count(*) from sku_items where sku_id = ".$sku_id) or die(mysql_error());
				$temp = $con->sql_fetchrow($q_3);
				if($temp[0]==1){	// only one item in this group, make it uom_id=1 and is_parent=1
					$con->sql_query("update sku_items set packing_uom_id=1,is_parent=1 where sku_id = ".$sku_id." order by sku_item_code,id limit 1") or die(mysql_error());
					print "SKU ID: $sku_id , This sku only have one item, set uom_id to 1 and become parent.<br>";
					$updated_row++;
				}else{
					print "SKU ID: $sku_id , This sku have $temp[0] items, cannot update.<br>";
					$cannot_update++;
				}
			}
		}
	}
        if($reset_row)  print "<p>$reset_row SKU Parent Reset.</p>";
	print "<p>$updated_row row(s) updated.</p>";
	if($cannot_update)  print "<p>$cannot_update row(s) cannot update.</p>";
}

function ajax_check_sku_move(){
	global $con;

	$sid = mi($_REQUEST['sku_item_id']);
	$con->sql_query("select * from sku_items where id = ".mi($sid)) or die(mysql_error());
	$item = $con->sql_fetchrow();
	if(!$item)  die("Invalid Item.");

	$sku_id = mi($item['sku_id']);

	$con->sql_query("select * from sku_items where sku_id = ".mi($sku_id)." and id <> ".mi($sid)) or die(mysql_error());
	$other_items = $con->sql_fetchrowset();

	if(!$other_items){
		print "OK";exit;
	}

	foreach($other_items as $r){
		if($r['is_parent']||$r['packing_uom_id']==1){
			print "OK";
			exit;
		}
	}

	die("This item cannot be move, no item in the group can become parent.");
}

function ajax_check_sku_obsolete(){
	global $con;
	$sid = mi($_REQUEST['sku_item_id']);
	
	$con->sql_query("select si.is_parent, substring(si.sku_item_code,9,4) as parent_code, substring(si.sku_item_code,1,8) as sku_code
					from sku_items si
					where si.id = ".mi($sid)) or die(mysql_error());
	
	$item = $con->sql_fetchrow();

	if(!$item)  die("Invalid Item.");
	
	if ($item['is_parent'] >0 ) die("Current sku item had been set as parent.");

	if ($item['parent_code'] != '0000')	die("Last 4 digits of ARMS code must be '0000'.");

	$sku_code = $item['sku_code'];

	$con->sql_query("select * from sku_obsolete where sku_code = ".mi($sku_code)) or die(mysql_error());

	if ($con->sql_numrows()>0)  print "OK";
	else    die("Cannot find previous sku record.");
}


function change_parent()
{
	global $LANG, $con;
    if (!privilege('MST_SKU_UPDATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE', BRANCH_CODE), "/masterfile_sku.php");

	
	$sku_item_id = mi($_REQUEST['sku_item_id']);
	$q1 = $con->sql_query("select * from sku_items where id = ".$sku_item_id);
	$r = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	if (!$r) die("Invalid SKU Item");

	// select parent information
	$q1 = $con->sql_query("select * from sku_items where is_parent=1 and sku_id = ".mi($r['sku_id']));
	$parent_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	$con->sql_query("update sku_items set is_parent=0 where is_parent=1 and sku_id = ".mi($r['sku_id'])) or die(mysql_error());
	$con->sql_query("update sku_items set is_parent=1, packing_uom_id=1, weight_kg=".mf($parent_info['weight_kg'])." where id = ".$sku_item_id) or die(mysql_error());

	header("Location: $_SERVER[PHP_SELF]?a=edit&id=$r[sku_id]");
}

function validate_data(&$form, &$items)
{
	global $hqcon, $LANG, $sessioninfo, $last_approval, $smarty, $config, $con;

	//print "<pre>"; print_r($_REQUEST);print"</pre>";
	$err = array();
		
    $form = $_REQUEST;
	//print_r($form);

	$form['id'] = intval($_REQUEST['id']);
	$form['apply_by'] = $sessioninfo['id'];

	$con->sql_query("select * from sku where id=".mi($form['id']));
	$ori_form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$params = array();
	$params['branch_id'] = $form['apply_branch_id']>0?$form['apply_branch_id']:$sessioninfo['branch_id'];
	$params['sku_type'] = $form['sku_type'];
	$params['type'] = 'SKU_APPLICATION';
	$params['dept_id'] = get_department_id($form['category_id']);
	$params['user_id'] = $sessioninfo['id'];
	if(!$params['sku_type']||!$form['category_id'])   $last_approval = false;
	else	$last_approval = is_last_approval($params);
  //$last_approval = check_last_approval($form);
  
	if ($last_approval)
	{
		$smarty->assign("last_approval", 1);
		if (!$form['receipt_description']) 
		{
			$err['top'][] = $LANG['SKU_LAST_APPROVAL_ENTER_RECEIPT_DESCRIPTION'];
			//print "<script>alert('".$LANG['SKU_LAST_APPROVAL_ENTER_RECEIPT_DESCRIPTION']."')</script>\n";
		}
	}
	//die();
	// check fields
	$form['category_id'] = intval($_REQUEST['category_id']);
	if ($form['category_id'] == 0)
		$err['top'][] = $LANG['SKU_INVALID_CATEGORY'];

	$form['vendor_id'] = intval($_REQUEST['vendor_id']);
	if ($form['vendor_id'] == 0)
		$err['top'][] = $LANG['SKU_INVALID_VENDOR'];

	if (!$form['sku_type'])
		$err['top'][] = $LANG['SKU_INVALID_TYPE'];

	$form['brand_id'] = intval($_REQUEST['brand_id']);
	
	// found got set po reorder qty
	if(!$form['po_reorder_by_child']){
		if(!$form['po_reorder_qty_min'] && !$form['po_reorder_qty_max'] && $form['po_reorder_moq']){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "");
		}elseif($form['po_reorder_qty_min'] && $form['po_reorder_qty_max'] || $form['po_reorder_moq']){
			if($form['po_reorder_qty_max']<=$form['po_reorder_qty_min']){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
			}
			if($form['po_reorder_qty_max']<$form['po_reorder_moq']){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "");
			}
		}elseif(!$form['po_reorder_qty_min'] && !$form['po_reorder_qty_max'] && $form['po_reorder_notify_user_id']){ // found no set min & max but have notify person
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "");
		}
		if($form['po_reorder_qty_min'] || $form['po_reorder_qty_max']){
			if(count($err['top'])>0 && in_array(sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], ""),$err['top'])){
				
			}else{
				if($form['po_reorder_qty_max']<=$form['po_reorder_qty_min']){
					$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
				}
			}
		}

		// found got set po reorder qty by branch
		if($form['po_reorder_qty_setup']){
			$invalid_prqbnuid_branches = $invalid_prqb_branches = array();
			$invalid_prqbnuid_branches2 = $invalid_prqb_branches2 = array();
			foreach($form['po_reorder_qty_by_branch']['min'] as $bid=>$min_qty){
				$max_qty = $form['po_reorder_qty_by_branch']['max'][$bid];
				$moq_qty = $form['po_reorder_qty_by_branch']['moq'][$bid];
				$notify_user_id = $form['po_reorder_qty_by_branch']['notify_user_id'][$bid];
				if(!$min_qty && !$max_qty && $moq_qty) {
					$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "for branch ".join(", ", $invalid_prqb_branches));
				}
					if(($min_qty || $max_qty) && $max_qty<=$min_qty) $invalid_prqb_branches[] = get_branch_code($bid);
					if($min_qty || $max_qty) $have_po_reorder_qty_by_branch = true;
					if(!$min_qty && !$max_qty && $notify_user_id) $invalid_prqbnuid_branches[] = get_branch_code($bid);
					if(!$moq_qty && !$max_qty && $notify_user_id) $invalid_prqbnuid_branches2[] = get_branch_code($bid);
					if(($moq_qty && $max_qty) && $max_qty<$moq_qty) $invalid_prqb_branches2[] = get_branch_code($bid);
				if($min_qty || $max_qty){
					if(($min_qty || $max_qty) && $max_qty<=$min_qty) $invalid_prqb_branches[] = get_branch_code($bid);
				}
			}
			if(count($invalid_prqb_branches) > 0){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "for branch ".join(", ", $invalid_prqb_branches));
			}
			
			if(count($invalid_prqbnuid_branches) > 0){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "for branch ".join(", ", $invalid_prqbnuid_branches));
			}
			
			if(count($invalid_prqb_branches2) > 0){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "for branch ".join(", ", $invalid_prqb_branches2));
			}
		}
	}

	if(!$have_po_reorder_qty_by_branch) unset($form['po_reorder_qty_by_branch']);
	
	// 0 - none
	// 1 - by brand
	// 2 - by vendor
    $form['trade_discount_type'] = intval($_REQUEST['trade_discount_type']);

	// unbranded cannot select by brand
	if ($form['brand_id'] == 0 && $form['trade_discount_type'] == 1)
	    $err['top'][] = $LANG['SKU_UNBRAND_USE_BRAND_DISCOUNT_TABLE'];

	// consignment require discount table
	if(!$config['sku_always_show_trade_discount']){
        if ($form['sku_type'] == 'CONSIGN' && $form['trade_discount_type'] == 0)
	    	$err['top'][] = $LANG['SKU_INVALID_DISCOUNT_TYPE_CONSIGN'];
	}

    // default type, Normal, BB, SBB etc
    if($config['sku_always_show_trade_discount'] || $form['trade_discount_type']>0){
    	$form['default_trade_discount_code'] = strval($_REQUEST['default_trade_discount_code']);
    }
    else
	{
		// cancel the default trade discount code
		$form['default_trade_discount_code'] = '';
    }
    
	$form['trade_discount_table'] = $_REQUEST['trade_discount_table'];
	$form['scale_type'] = $_REQUEST['mst_scale_type'];
	
	// check if selected trade_discount_table is zero or no value
	if(!$config['sku_always_show_trade_discount']){
		if (($form['trade_discount_type']>0 || $form['trade_discount_table'][$form['default_trade_discount_code']] == 0) && $form['sku_type'] == "CONSIGN"){
			if ($form['trade_discount_table'][$form['default_trade_discount_code']] == '')
			{
				// checking if trade discount value=0 or empty
				$err['top'][] = $LANG['SKU_INVALID_TRADE_DISCOUNT_TABLE'];
			}
			
			if ($form['trade_discount_table'][$form['default_trade_discount_code']] == 0 && $form['default_trade_discount_code'] != 'PWP')
			{
				// checking if trade discount value=0 or empty
				$err['top'][] = $LANG['SKU_TRADE_DISCOUNT_VALUE_IS_ZERO'];
			}
		}
	}
	
	// 0 = no, 1 = yes
	if($config['sku_non_returnable'])	$form['group_non_returnable'] = mi($_REQUEST['group_non_returnable']);

	// check items
	$n = 0;
 	$artno_used= array();
	$is_first_item = true;
	$l=0;
    $check=array();
    $matrix_check=array();

	if (isset($_REQUEST['description']))
	{
        $all_mcode=array();
		//check duplicate color and size
		foreach ($_REQUEST['description'] as $n=>$v)
		{
            $this_err = array();
            $item['id'] = $n;
            $item['sku_item_code'] = $_REQUEST['si_code'][$n];
		    $item['own_article'] = intval($_REQUEST['own_article'][$n]);
		    $item['ctn_1_uom_id'] = mi($_REQUEST['ctn_1_uom_id'][$n]);
		    $item['ctn_2_uom_id'] = mi($_REQUEST['ctn_2_uom_id'][$n]);
      		$item['open_price'] = intval($_REQUEST['open_price'][$n]);
      		$item['decimal_qty'] = intval($_REQUEST['decimal_qty'][$n]);
      		$item['doc_allow_decimal'] = intval($_REQUEST['doc_allow_decimal'][$n]);
      		$item['ri_id'] = mi($_REQUEST['ri_id'][$n]);
      		$item['ri_group_name'] = trim($_REQUEST['ri_group_name'][$n]);
      		$item['is_parent'] = $_REQUEST['is_parent'][$n];
      		$item['active'] = $_REQUEST['active'][$n];
      		$item['reject_reason'] = $_REQUEST['reason'][$n];
      		$item['location'] = $_REQUEST['location'][$n];
      		$item['is_new'] = $_REQUEST['is_new'][$n];
			$item['block_list'] = $_REQUEST['block_list'][$n];
			$item['scale_type'] = $_REQUEST['dtl_scale_type'][$n];
			$item['extra_info'] = $_REQUEST['extra_info'][$n];
			$item['doc_block_list'] = $_REQUEST['doc_block_list'][$n];
			$item['disable_edit_packing_uom'] = $_REQUEST['disable_edit_packing_uom'][$n];
			
			if($config['enable_gst']){
				$item['input_tax'] = $_REQUEST['dtl_input_tax'][$n];
				$item['output_tax'] = $_REQUEST['dtl_output_tax'][$n];
				$item['inclusive_tax'] = $_REQUEST['dtl_inclusive_tax'][$n];
			}
			
			if($config['sku_non_returnable']){
				$item['non_returnable'] = mi($_REQUEST['non_returnable'][$n]);
			}
			
			//join artno and size while save   
			$item['artno'] = strtoupper(trim($_REQUEST['artno'][$n]." ".$_REQUEST['artsize'][$n]));

			if (!$config['sku_artno_allow_specialchars']) $item['artno'] = preg_replace("/[^A-Z0-9]/", "", $item['artno']);
/*
			if (!$config['sku_application_artno_allow_duplicate'] && $item['artno'] != '')
			{
				if (isset($artno_used[$item['artno']]))
					$this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $item['artno'], "in variety ".$artno_used[$item['artno']]);
            	$artno_used[$item['artno']] = $n;
			}
*/			
			$item['mcode'] = trim($_REQUEST['mcode'][$n]);
            if($item['mcode'])	$all_mcode[]=$item['mcode'];
			
			if (!$config['sku_artno_allow_specialchars']) $item['mcode'] = preg_replace("/[^A-Z0-9]/", "", $item['mcode']);
			if (!$config['sku_application_artno_allow_duplicate'] && $item['mcode'] != '')
			{
				$sku_rid=$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($form['id'])." and mcode = ".ms($item['mcode']));

				if($con->sql_numrows($sku_rid) > 0 ){
					$sku=$con->sql_fetchassoc($sku_rid);
					$this_err[] = sprintf($LANG['SKU_MCODE_USED'],$item['mcode'],"in existing SKU. ($sku[id])");
				}else{ // check from sku_apply_items
					$sai_rid=$con->sql_query($abc="select concat('SKU ',sku_id) as id 
											  from sku_apply_items sai 
											  left join sku on sai.sku_id = sku.id
											  where sai.is_new and (sku.status <> 4 and sku.active=0) and sai.sku_id <> ".mi($form['id'])." and (sai.mcode = ".ms($item['mcode'])." or sai.product_matrix like ".ms('%:"'.replace_special_char($item['mcode']).'";%').")");
					
					if($con->sql_numrows($sai_rid) > 0 ){
						$sku=$con->sql_fetchassoc($sai_rid);
						$this_err[] = sprintf($LANG['SKU_MCODE_USED'],$item['mcode'],"in existing SKU. ($sku[id])");
					}
					$con->sql_freeresult($sai_rid);
				}
				$con->sql_freeresult($sku_rid);
			}

            $item['link_code'] = strval($_REQUEST['link_code'][$n]);
			$item['item_type'] = $_REQUEST['item_type'][$n];
			$item['packing_uom_id'] = $_REQUEST['packing_uom_id'][$n];
			if($is_first_item){
				if($item['packing_uom_id']!=1){
					// check if fraction is 1 or not
					$con->sql_query("select fraction from uom where id=".mi($item['packing_uom_id'])." and active=1");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();

					if($tmp['fraction'] != 1){
						$this_err[] = $LANG['SKU_FIRST_ITEM_UOM_EACH'];
					}
				}
				$is_first_item = false;
			}
			
			$item['description'] = strval($_REQUEST['description'][$n]);

			$ret_desc = check_receipt_desc_max_length($_REQUEST['receipt_description'][$n]);
			if($ret_desc["err"]){
				$this_err[] = $ret_desc["err"];
			}
			
			$item['receipt_description'] = strval($_REQUEST['receipt_description'][$n]);

			if ($item['item_type'] == 'variety')
			{
			    //Check variety color and size :: Skip if empty
				if ($_REQUEST['description2'][$n] != '' && $_REQUEST['description3'][$n] != ''){
					foreach ($check as $vsc){
						if ($vsc['size']==$_REQUEST['description2'][$n] && $vsc['color']==$_REQUEST['description3'][$n] && !$config['sku_allow_same_colour_size']){
							$this_err[]=sprintf($LANG['SKU_COLOR_SIZE_DUPLICATE'],$_REQUEST['description3'][$n],$_REQUEST['description2'][$n]);

							break;
						}
					}

			 		$check[$l]['size'] = $_REQUEST['description2'][$n];
			        $check[$l]['color'] = $_REQUEST['description3'][$n];
					++$l;
            	}
				$item['description0'] = strval($_REQUEST['description0'][$n]);
				$item['description1'] = strval($_REQUEST['description1'][$n]);
				$item['weight'] = strval($_REQUEST['description1'][$n]);
				$item['description2'] = strval($_REQUEST['description2'][$n]);
				$item['size'] = strval($_REQUEST['description2'][$n]);
				$item['description3'] = strval($_REQUEST['description3'][$n]);
				$item['color'] = strval($_REQUEST['description3'][$n]);
				$item['description4'] = strval($_REQUEST['description4'][$n]);
				$item['flavor'] = strval($_REQUEST['description4'][$n]);
				$item['description5'] = strval($_REQUEST['description5'][$n]);
				$item['misc'] = strval($_REQUEST['description5'][$n]);
				$item['description_table'] = serialize(array($item['description0'], $item['description1'], $item['description2'], $item['description3'], $item['description4'], $item['description5']));
				$item['selling_price'] = doubleval($_REQUEST['selling_price'][$n]);
        		$item['cost_price'] = doubleval($_REQUEST['cost_price'][$n]);
				if($config['sku_listing_show_hq_cost']&&BRANCH_CODE=='HQ'){
                    $item['hq_cost'] = doubleval($_REQUEST['hq_cost'][$n]);
				}
				if($config['do_enable_hq_selling']&&BRANCH_CODE=='HQ'){
					$item['hq_selling'] = $_REQUEST['hq_selling'][$n];
				}
				
				$item['allow_selling_foc'] = mi($_REQUEST['allow_selling_foc'][$n]);
				$item['selling_foc'] = $item['allow_selling_foc'] ? mi($_REQUEST['selling_foc'][$n]) : 0;
				if($item['selling_price'] <=0 && $item['allow_selling_foc'])	$item['selling_foc'] = 1;
				
				// category discount
				// inherit option
				$item['cat_disc_inherit'] = trim($_REQUEST['cat_disc_inherit'][$n]);
		        
		        if(!$item['cat_disc_inherit'])	$item['cat_disc_inherit'] = 'inherit';
		        
		        // inherit value
				$item['category_disc_by_branch_inherit'] = $_REQUEST['category_disc_by_branch_inherit'][$n];
		        if($item['cat_disc_inherit'] !='set'){
					$item['category_disc_by_branch_inherit'] = array();
				}
				
		        // category reward point
		        $item['category_point_inherit'] = trim($_REQUEST['category_point_inherit'][$n]);
		        $item['category_point_by_branch_inherit'] = $_REQUEST['category_point_by_branch_inherit'][$n];
		        //$item['dtl_return_policy'] = $_REQUEST['dtl_return_policy'][$n];
		        
		        if(!$item['category_point_inherit'])	$item['category_point_inherit'] = 'inherit';
		        if($item['category_point_inherit']!='set')	$item['category_point_by_branch_inherit'] = array();
				
				// check whether selling is zero
				if ($item['selling_price'] <= 0 && !$item['open_price'] && !$item['allow_selling_foc']){
					$this_err[] = $LANG['SKU_INVALID_SELLING_PRICE'];
				}	
					
				// check whether selling below cost
				if (!$config['sku_allow_cost_higher_than_selling']) {
					if ($item['selling_price'] < $item['cost_price'] && !$item['selling_foc'] && !$item['open_price']){
						$this_err[] = $LANG['SKU_SELLING_BELOW_COST'];
					}
				}
					
				// OUTRIGHT type sku cannot have zero cost
				if ($form['sku_type'] != 'CONSIGN' && $item['cost_price'] == 0){
					$this_err[] = $LANG['SKU_INVALID_COST_PRICE'];
				}
				
				if(!$item['active'] && !trim($item['reject_reason'])){
					$this_err[] = $LANG['SKU_EMPTY_REJECT_REASON'];
				}
				
				if($config['sku_enable_additional_description']){
					$item['additional_description'] = $_REQUEST['additional_description'][$n];
					$item['additional_description_print_at_counter'] = $_REQUEST['additional_description_print_at_counter'][$n];
					$item['additional_description_prompt_at_counter '] = $_REQUEST['additional_description_prompt_at_counter'][$n];
				}
				
				if($form['po_reorder_by_child']){
					$item['po_reorder_qty_min'] = $form['si_po_reorder_qty_min'][$n];
					$item['po_reorder_qty_max'] = $form['si_po_reorder_qty_max'][$n];
					$item['po_reorder_moq'] = $form['si_po_reorder_moq'][$n];
					$item['po_reorder_notify_user_id'] = $form['si_po_reorder_notify_user_id'][$n];
					if(!$item['po_reorder_qty_max'] && !$item['po_reorder_qty_min'] && $item['po_reorder_moq']){
						$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "");
					}elseif($item['po_reorder_qty_min'] && $item['po_reorder_qty_max'] || $item['po_reorder_moq']){
						if($item['po_reorder_qty_max']<=$item['po_reorder_qty_min']){
							$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
						}
						if($item['po_reorder_qty_max']< $item['po_reorder_moq']){
							$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "");
						}
					}elseif(!$item['po_reorder_qty_min'] && !$item['po_reorder_qty_max'] && $item['po_reorder_notify_user_id']){ // found no set min & max but have notify person
						$this_err[] = sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "");
					}
					if($item['po_reorder_qty_min'] || $item['po_reorder_qty_max']){
						if(count($this_err)>0 && in_array(sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], ""),$this_err)){
							
						}else{
							if($item['po_reorder_qty_max']<=$item['po_reorder_qty_min']){
								$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
							}
						}
					}
				}
				
				if($config['enable_sn_bn']){
					$item['sn_we'] = $form['sn_we'][$n];
					$item['sn_we_type'] = $form['sn_we_type'][$n];
				}
				
				if(privilege('MST_INTERNAL_DESCRIPTION')){
					$item['internal_description'] = $form['internal_description'][$n];
				}
				
				if($config['arms_marketplace_settings']){
					$item['marketplace_description'] = $form['marketplace_description'][$n];
				}
				
				$item['model'] = $_REQUEST['model'][$n];
				$item['width'] = $_REQUEST['width'][$n];
				$item['height'] = $_REQUEST['height'][$n];
				$item['length'] = $_REQUEST['length'][$n];
				
				// RSP
				$item['ori_use_rsp'] = $_REQUEST['ori_use_rsp'][$n];
				$item['use_rsp'] = $_REQUEST['use_rsp'][$n];
				$item['rsp_price'] = $_REQUEST['rsp_price'][$n];
				$item['rsp_discount'] = $_REQUEST['rsp_discount'][$n];
			}
			else
			{
			    $item['description_table'] = '';
				$item['tb'] = $_REQUEST['tb'][$n];
				$item['tbm'] = $_REQUEST['tbm'][$n];
				$item['tbprice'] = $_REQUEST['tbprice'][$n];
				$item['tbhqprice'] = $_REQUEST['tbhqprice'][$n];
				$item['tbcost'] = $_REQUEST['tbcost'][$n];
				$item['tbhqcost'] = $_REQUEST['tbhqcost'][$n];
			}

			if ($form['artno'][$n]!=''){
				if (!$config['sku_application_artno_allow_duplicate']){
					$artno=trim($form['artno'][$n]." ".$form['artsize'][$n]);

					if ($config['consignment_modules'])
					{
						$sku_sql="select * from sku_items where sku_id <> ".mi($_REQUEST['id'])." and artno = ".ms($artno);
					}
					else
					{
						$get_did=$con->sql_query("select department_id from category where id = ".mi($form['category_id']));
						$r=$con->sql_fetchrow($get_did);
						$line=get_line_detail($r['department_id']);

						if($line=='SOFTLINE')	$filter=" and sku.brand_id = ".mi($form['brand_id']);

						$sku_sql="select concat('SKU ',sku_id) as id from sku_items si
										left join sku on si.sku_id=sku.id
										left join category c1 on c1.id=sku.category_id and c1.department_id = ".mi($r['department_id'])."
										where sku.id <> ".mi($form['id'])." $filter and sku.vendor_id = ".mi($form['vendor_id'])."	and si.artno = ".ms($artno);

						$con->sql_freeresult($get_did);
					}

					$sku_rid=$con->sql_query($sku_sql);

					if ($con->sql_numrows($sku_rid) > 0){
						$sku=$con->sql_fetchassoc($sku_rid);
						$this_err[] = sprintf($LANG['SKU_ARTNO_USED'],$artno,"by the same vendor in existing SKU. ($sku[id])");
					}else{
						if ($config['consignment_modules']){
							$sai_sql="select * from sku_apply_items where is_new = 1 and sku_id <> ".mi($_REQUEST['id'])." and artno = ".ms($artno);
						}
						else
						{
							$get_did=$con->sql_query("select department_id from category where id = ".mi($form['category_id']));
							$r=$con->sql_fetchassoc($get_did);
							$con->sql_freeresult($get_did);
							$line=get_line_detail($r['department_id']);

							if($line=='SOFTLINE')	$filter=" and sku.brand_id = ".mi($form['brand_id']);

							$sai_sql="select concat('SKU ',sku_id) as id 
									from sku_apply_items sai
									left join sku on sai.sku_id=sku.id
									left join category c1 on c1.id=sku.category_id and c1.department_id = ".mi($r['department_id'])."
									where sai.is_new and (sku.status <> 4 and sku.active=0) and sku.id <> ".mi($form['id'])." $filter and sku.vendor_id = ".mi($form['vendor_id'])." and (sai.artno = ".ms($artno)." or sai.product_matrix like ".ms('%:"'.replace_special_char($artno).'";%').") and c1.department_id=".mi($r['department_id']);
						}

						$sai_rid=$con->sql_query($sai_sql);

						if ($con->sql_numrows($sai_rid) > 0 ){
							$sku=$con->sql_fetchassoc($sai_rid);
							$this_err[] = sprintf($LANG['SKU_ARTNO_USED'],$artno,"by the same vendor in existing SKU. ($sku[id])");
						}
						$con->sql_freeresult($sai_rid);
					}
					
					$con->sql_freeresult($sku_rid);
				}
			}
			
			/*if ($item['artno'] != '' && $i=is_artmcode_used($form['id'], $item['artno'], $form['vendor_id'], 'artno'))
			{
			    $this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $item['artno'], "by the same vendor in existing SKU. ($i)");
			}*/
   			if ($item['mcode'] != '')
			{
				// at least 1 photo if mcode used
				// temporary disable
				//if ($SKU_MIN_PHOTO_REQUIRED < 1) $SKU_MIN_PHOTO_REQUIRED = 1;
				    
			    //if ($m=is_artmcode_used($form['id'], $item['mcode'], $form['vendor_id'], 'mcode'))
					//$this_err[] = sprintf($LANG['SKU_MCODE_USED'], $item['mcode'], "in existing SKU. ($m)");

                if (preg_match('/^[0-9]+$/',$item['mcode']) && in_array(strlen($item['mcode']), array(5,6,8,12,13)))
				{
     				// ok no problem
				}
				elseif (isset($config['sku_application_valid_mcode']) && preg_match($config['sku_application_valid_mcode'],$item['mcode']))
				{
					// ok no problem too
				}
				elseif($config['sku_artno_allow_specialchars']&& in_array(strlen($item['mcode']), array(5,6,8,12,13))){
          // ok no problem too
				}
				else
				{
					$this_err[] = sprintf($LANG['SKU_MCODE_INVALID_FORMAT'], $item['mcode']);
				}
			}
	
			if($item['is_parent'] && $ori_form['allow_parent_empty_artno_mcode']){
				// no need check mcode
			}else{
				if (!$config['sku_application_allow_no_artno_mcode'] && ($item['artno'] == '' && $item['mcode'] == '' && ($item['item_type'] == 'variety' || !$item['own_article']))){
					$this_err[] = $LANG['SKU_INVALID_ART_MCODE'];
				}
			}
			
			

			if ($item['description'] == '')
				$this_err[] = $LANG['SKU_INVALID_DESCRIPTION'];

			if ($last_approval && $item['receipt_description'] == '')
			{
				$this_err[] = $LANG['SKU_INVALID_RECEIPT_DESCRIPTION'];
				$item['receipt_description'] = substr($item['description'], 0, 40);
			}
			
			// make sure table is not empty
			$tbm = $item['tbm'];
			if ($item['item_type'] != 'variety')
			{

				for ($r=1; $r<count($item['tb']); $r++)
				{
					for ($c=1; $c<count($item['tb'][0]); $c++)
					{

						if ($item['tb'][0][$c] == '' || $item['tb'][$r][0] == '') continue;

				   	    foreach ($check as $msc){
							if ($msc['size']==$item['tb'][$r][0] && $msc['color']==$item['tb'][0][$c] && !$config['sku_allow_same_colour_size']){
								$this_err[]=sprintf($LANG['SKU_COLOR_SIZE_DUPLICATE'],$item['tb'][0][$c],$item['tb'][$r][0]);
								break;
							}
						}

						$check[$l]['size'] = $item['tb'][$r][0];
					    $check[$l]['color'] = $item['tb'][0][$c];
					    ++$l;
					}
				}
		        
				if ($item['own_article'])
				{
				    $rmax=0;$cmax=0;$c=0;$r=0;$spc=false;$rspc=false;$invalid_table=false;$tbn=0;
					foreach ($item['tb'] as $tbrow)
					{
					    if ($r==0)
					    {
					        // check header
					        $c = 0;
							foreach ($tbrow as $tbcol)
							{
								if ($tbcol != '')
								{
									$cmax = $c;
									if ($spc)
									{
									    // if contain empty header (in between 2 filled column header)
									    $invalid_table = true;
										$c--;
									    break;
									}
								}
								elseif ($c != 0)
								{
								    // found spacing
								    $spc = 1;
								}
								$c++;
							}
						}
						else
						{
						    if ($tbrow[0] != '')
						    {
						        $rmax = $r;
						        if ($rspc)
						        {
						             // if contain empty header (in between 2 filled row header)
								    $invalid_table = true;
								    $c=0;
								    $r--;
								    break;
								}
							}
							else
							{
							    $rspc = true;
							}


						    $c=0;
							foreach ($tbrow as $tbcol)
							{
							    // 11/17/2009 4:04:57 PM yinsee
							    // at least 1 photo for matrix table - temporary disable
								// if ($tbm[$r][$c] != '' && $SKU_MIN_PHOTO_REQUIRED<1) $SKU_MIN_PHOTO_REQUIRED = 1;

							   	if ($tbcol != '' || $tbm[$r][$c] != '')
							    {

							        // check if mcode used
									/*if ($tbcol != '' && $c > 0 && is_artmcode_used($form['id'], $tbcol, $form['vendor_id'], 'artno'))
									{
									    $this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $tbcol, "(Row $r, Column ".chr($c+64).")");
									}
									if ($tbm[$r][$c] != '' && $c > 0 && is_artmcode_used($form['id'], $tbm[$r][$c], $form['vendor_id'], 'mcode'))
									{
									    $this_err[] = sprintf($LANG['SKU_MCODE_USED'], $tbm[$r][$c], "(Row $r, Column ".chr($c+64).")");
									}*/

							        // mark the max column
									if ($tbrow[0] == '') // no row header
									{
										if ($cmax < $c) $cmax = $c;
										$invalid_table = true;
										break;
									}
									elseif ($c > $cmax) // no column header
									{
									    if ($cmax < $c) $cmax = $c;
										$invalid_table = true;
										break;
									}
									if ($c>0) $tbn++;
								}
								$c++;
							}
						}
						if ($invalid_table)
						{
							if($c==0) $col = 0;
							else $col = chr($c+64);
						    $this_err[] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".$col.")");
						    break;
						}
						$r++;
					}
				} // own article
				else
				{
					$rmax=0;$cmax=0;$c=0;$r=0;$spc=false;$rspc=false;$invalid_table=false;$tbn=0;
					foreach ($item['tb'] as $tbrow)
					{
					    if ($r==0)
					    {
					        // check header
					        $c = 0;
							foreach ($tbrow as $tbcol)
							{
								if ($tbcol != '')
								{
									$cmax = $c;
									if ($spc)
									{
									    // if contain empty header (in between 2 filled column header)
									    $invalid_table = true;
									    $c--;
									    break;
									}
								}
								elseif ($c != 0)
								{
								    // found spacing
								    $spc = true;
								}
								$c++;
							}
						}
						else
						{
						    if ($tbrow[0] != '')
						    {
							    $rmax = $r;
						        if ($rspc)
						        {
						             // if contain empty header (in between 2 filled row header)
								    $invalid_table = true;
								    $c=0;
								    $r--;
								}
							}
							else
							{
							    $rspc = true;
							}
						}
						if ($invalid_table)
						{
							if($c==0) $col = 0;
							else $col = chr($c+64);
						    $this_err[] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".$col.")");
						    break;
						}
						$r++;
					}
				} // share article
				if (!$invalid_table)
				{
				    if (($rmax<1 || $cmax<1) || ($item['own_article'] && $tbn<2))
				    {
				    	$this_err[] = $LANG['SKU_MATRIX_EMPTY'];
				    }
				}
				// check price row
				for ($i=0;$i<$rmax;$i++)
				{
				    if (doubleval($item['tbprice'][$i+1]) <= 0)
				    {
						$this_err[] = $LANG['SKU_MATRIX_INVALID_PRICE'];
						break;
					}
					if ($form['sku_type'] != 'CONSIGN')
					{
						if (doubleval($item['tbcost'][$i+1]) <= 0)
						{
							$this_err[] = $LANG['SKU_MATRIX_INVALID_COST'];
							break;
						}
						if (!$config['sku_allow_cost_higher_than_selling']) {
							if (doubleval($item['tbprice'][$i+1]) < doubleval($item['tbcost'][$i+1]))
							{
								$this_err[] = $LANG['SKU_MATRIX_SELLING_BELOW_COST'];
								break;
							}
						}
					}
				}
				$item["table_width"] = $cmax+1;
				$item["table_height"] = $rmax+1;
			}   // matrix table check

			/*if($item['item_type'] == "matrix"){
				$item['id'] = $count;
				$n = $count;
			}else $item['id'] = $n;*/

			// add item to list
			array_push($items, $item);
			if ($this_err) $err['items'][$n] = $this_err;
			$count++;
	    }

        if(!$_REQUEST['parent_child_duplicate_mcode']){
            if($all_mcode!=array_unique($all_mcode)){
                $dmcode=array_unique( array_diff_assoc( $all_mcode, array_unique( $all_mcode ) ) );
                $err['top'][] = sprintf($LANG["SKU_MCODE_DUPLICATE"],implode(", ",$dmcode));//"Duplicate Manufacturer's Code - ".implode(", ",$dmcode);
            }
        }
	}

    if ($n==0)
    {
        $err['top'][] = $LANG['SKU_NO_ITEM'];
	}

	return $err;
}

/*function check_error($id,$row,$col)
{
    Global	$LANG,$config, $con;
    $form=$_REQUEST;

	$arr_artno=array();
	$arr_mcode=array();


    if ($form['item_type'][$id] == 'variety'){
		if (!$config['sku_application_allow_no_artno_mcode'] && ($form['artno'][$id] == '' && $form['mcode'][$id] == '' )){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_ART_MCODE'];
			else $err['items'][$id][] = $LANG['SKU_INVALID_ART_MCODE'];
		}

		if ($form['selling_price'][$id] == 0){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_SELLING_PRICE'];
			else $err['items'][$id][] = $LANG['SKU_INVALID_SELLING_PRICE'];
		}

		if ($form['selling_price'][$id] < $form['cost_price'][$id]){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_SELLING_BELOW_COST'];
			else $err['items'][$id][] = $LANG['SKU_SELLING_BELOW_COST'];
		}

		if ($form['sku_type'] != 'CONSIGN' && $form['cost_price'][$id] == 0){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_COST_PRICE'];
			else $err['items'][$id][] = $LANG['SKU_INVALID_COST_PRICE'];
		}

		if ($form['receipt_description'][$id] == ''){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_RECEIPT_DESCRIPTION'];
			else $err['items'][$id][] = $LANG['SKU_INVALID_RECEIPT_DESCRIPTION'];
		}

		if (!$config['sku_application_allow_no_artno_mcode'] && $form['artno'][$id]=='' && $form['mcode'][$id]==''){
		    if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_ART_MCODE'];
		    else $err['items'][$id][] = $LANG['SKU_INVALID_ART_MCODE'];
		}

		if ($form['artno'][$id]!=''){
			if (!$config['sku_application_artno_allow_duplicate']){
/*
				foreach ($form['artno'] as $artno){
					$arr_artno[]=$artno;
				}

				$a=0;
				foreach($arr_artno as $artno){
					if ($form['artno'][$id] == $artno)	++$a;
				}

				if ($a>=2){
					$err['items'][$id][]=sprintf ($LANG['SKU_ARTNO_REPEATED'], $form['artno'][$id]);
				}
				unset($arr_artno);
*//*
				$artno=trim($form['artno'][$id]." ".$form['artsize'][$id]);

				if ($config['consignment_modules'])
				{
					$sku_sql="select * from sku_items where sku_id <> ".mi($_REQUEST['id'])." and artno = ".ms($artno);
				}
				else
				{
					$get_did=$con->sql_query("select department_id from category where id = ".mi($form['category_id']));
					$r=$con->sql_fetchrow($get_did);
					$line=get_line_detail($r['department_id']);

					if($line=='SOFTLINE')	$filter=" and sku.brand_id = ".mi($form['brand_id']);

					$sku_sql="select concat('SKU ',sku_id) as id from sku_items si
				   					left join sku on si.sku_id=sku.id
				   					left join category c1 on c1.id=sku.category_id and c1.department_id = ".mi($r['department_id'])."
									where sku.id <> ".mi($form['id'])." $filter and sku.vendor_id = ".mi($form['vendor_id'])."	and si.artno = ".ms($artno);
					
					$con->sql_freeresult($get_did);
			    }

			    $sku_rid=$con->sql_query($sku_sql);

				if ($con->sql_numrows($sku_rid) > 0 ){
					$sku=$con->sql_fetchassoc($sku_rid);
			    	if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_ARTNO_USED'],$artno,"by the same vendor in existing SKU. ($sku[id])");
			    	else $err['items'][$id][] = sprintf($LANG['SKU_ARTNO_USED'],$artno,"by the same vendor in existing SKU. ($sku[id])");
			    }
			    $con->sql_freeresult($sku_rid);
			}
		}

		if ($form['mcode'][$id]!=''){
/*

					foreach($form['mcode'] as $mcode){
						$arr_mcode[]= $mcode;
					}

    			    $m=0;
					foreach($arr_mcode as $mcode){
						if ($form['mcode'][$id] == $mcode)	++$m;
					}

					if ($m>=2){
						$err['items'][$id][]=sprintf ($LANG['SKU_MCODE_REPEATED'], $form['mcode'][$id]);
					}

					unset($arr_mcode);
*//*

		    $sku_rid=$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($form['id'])." and mcode = ".ms($form['mcode'][$id]));
		    if($con->sql_numrows($sku_rid) > 0 ){
				$sku=$con->sql_fetchassoc($sku_rid);
		    	if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_MCODE_USED'],$form['mcode'][$id],"in existing SKU. ($sku[id])");
		    	else $err['items'][$id][] = sprintf($LANG['SKU_MCODE_USED'],$form['mcode'][$id],"in existing SKU. ($sku[id])");
			}
			$con->sql_freeresult($sku_rid);
			// at least 1 photo if mcode used
			// temporary disable
			//if ($SKU_MIN_PHOTO_REQUIRED < 1) $SKU_MIN_PHOTO_REQUIRED = 1;

			if (preg_match('/^[0-9]+$/',$form['mcode'][$id]) && in_array(strlen($form['mcode'][$id]), array(5,6,8,12,13)))
			{
				// ok no problem
			}
			elseif (isset($config['sku_application_valid_mcode']) && preg_match($config['sku_application_valid_mcode'],$form['mcode'][$id]))
			{
				// ok no problem too
			}
			elseif($config['sku_artno_allow_specialchars'] && in_array(strlen($form['mcode'][$id]), array(5,6,8,12,13)))
			{
			// ok no problem too
			}
			else
			{
				if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_MCODE_INVALID_FORMAT'], $form['mcode'][$id]);
				else $err['items'][$id][] = sprintf($LANG['SKU_MCODE_INVALID_FORMAT'], $form['mcode'][$id]);
			}
		}
	}
	else
	{
        if (!$config['sku_application_allow_no_artno_mcode'] && ($form['tb'][$id][$row][$col] == '' || $form['tbm'][$id][$row][$col] == '' ) && $form['own_article'][$id]){
			if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_INVALID_ART_MCODE'];
			else $err['items'][$id][] = $LANG['SKU_INVALID_ART_MCODE'];
		}

		// make sure table is not empty
		$tbm = $form['tbm'][$id];
		if ($form['item_type'][$id] != 'variety')
		{

			if ($form['own_article'][$id])
			{
				$get_did=$con->sql_query("select department_id from category where id = ".mi($form['category_id']));
				$r=$con->sql_fetchassoc($get_did);
				$line=get_line_detail($r['department_id']);
				$con->sql_freeresult($get_did);
			    $rmax=0;$cmax=0;$c=0;$r=0;$spc=false;$rspc=false;$invalid_table=false;$tbn=0;
				foreach ($form['tb'][$id] as $tbrow)
				{
				    if ($r==0)
				    {
				        // check header
				        $c = 0;
						foreach ($tbrow as $tbcol)
						{
							if ($tbcol != '')
							{
								$cmax = $c;
								if ($spc)
								{
								    // if contain empty header (in between 2 filled column header)
								    $invalid_table = true;
									$c--;
								    break;
								}
							}
							elseif ($c != 0)
							{
							    // found spacing
							    $spc = 1;
							}
							$c++;
						}
					}
					else
					{
					    if ($tbrow[0] != '')
					    {
					        $rmax = $r;
					        if ($rspc)
					        {
					             // if contain empty header (in between 2 filled row header)
							    $invalid_table = true;
							    $c=0;
							    $r--;
							    break;
							}
						}
						else
						{
						    $rspc = true;
						}


					    $c=0;
						foreach ($tbrow as $tbcol)
						{
						    // 11/17/2009 4:04:57 PM yinsee
						    // at least 1 photo for matrix table - temporary disable
							// if ($tbm[$r][$c] != '' && $SKU_MIN_PHOTO_REQUIRED<1) $SKU_MIN_PHOTO_REQUIRED = 1;


						   	if ($tbcol != '' || $tbm[$r][$c] != '')
						    {
/*
						        // check if mcode used
								if ($tbcol != '' && $c > 0 && is_artmcode_used($form['id'][$id], $tbcol, $form['vendor_id'][$id], 'artno'))
								{
								    $err['items'][$id][] = sprintf($LANG['SKU_ARTNO_USED'], $tbcol, "(Row $r, Column ".chr($c+64).")");
								}
								if ($tbm[$r][$c] != '' && $c > 0 && is_artmcode_used($form['id'][$id], $tbm[$r][$c], $form['vendor_id'][$id], 'mcode'))
								{
								    $err['items'][$id][] = sprintf($LANG['SKU_MCODE_USED'], $tbm[$r][$c], "(Row $r, Column ".chr($c+64).")");
								}
*//*
								if (!$config['sku_application_artno_allow_duplicate'] && $tbcol){
									if($line=='SOFTLINE')	$filter=" and sku.brand_id = ".mi($form['brand_id']);
								
									$sku_sql="select concat('SKU ',sku_id) as id from sku_items si
								   					left join sku on si.sku_id=sku.id
								   					left join category c1 on c1.id=sku.category_id and c1.department_id = ".mi($r['department_id'])."
													where sku.id <> ".mi($form['id'])." $filter and sku.vendor_id = ".mi($form['vendor_id'])."	and si.artno = ".ms($tbcol);

								    $sku_rid=$con->sql_query($sku_sql);
								    if($con->sql_numrows($sku_rid) > 0 ){
										$sku=$con->sql_fetchassoc($sku_rid);
								    	if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_ARTNO_USED'],$tbcol,"by the same vendor in existing SKU. ($sku[id])");
								    	else $err['items'][$id][] = sprintf($LANG['SKU_ARTNO_USED'],$tbcol,"by the same vendor in existing SKU. ($sku[id])");
									}
									$con->sql_freeresult($sku_rid);
								}

								if ($tbm[$r][$c]){
								    $sku_rid=$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($form['id'])." and mcode = ".ms($tbm[$r][$c]));
								    if($con->sql_numrows($sku_rid)>0){
										$sku=$con->sql_fetchassoc($sku_rid);
								    	if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_MCODE_USED'],$tbm[$r][$c],"in existing SKU. ($sku[id])");
								    	else $err['items'][$id][] = sprintf($LANG['SKU_MCODE_USED'],$tbm[$r][$c],"in existing SKU. ($sku[id])");
									}
									$con->sql_freeresult($sku_rid);
								}
						        // mark the max column
								if ($tbrow[0] == '') // no row header
								{
									if ($cmax < $c) $cmax = $c;
									$invalid_table = true;
									break;
								}
								elseif ($c > $cmax) // no column header
								{
								    if ($cmax < $c) $cmax = $c;
									$invalid_table = true;
									break;
								}
								if ($c>0) $tbn++;
							}
							$c++;
						}
					}
					if ($invalid_table)
					{
					    if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
					    else $err['items'][$id][] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
					    break;
					}
					$r++;
				}
			} // own article
			else
			{

				$rmax=0;$cmax=0;$c=0;$r=0;$spc=false;$rspc=false;$invalid_table=false;$tbn=0;
				foreach ($form['tb'][$id] as $tbrow)
				{
				    if ($r==0)
				    {
				        // check header
				        $c = 0;
						foreach ($tbrow as $tbcol)
						{
							if ($tbcol != '')
							{
								$cmax = $c;
								if ($spc)
								{
								    // if contain empty header (in between 2 filled column header)
								    $invalid_table = true;
								    $c--;
								    break;
								}
							}
							elseif ($c != 0)
							{
							    // found spacing
							    $spc = true;
							}
							$c++;
						}
					}
					else
					{
					    if ($tbrow[0] != '')
					    {
						    $rmax = $r;
					        if ($rspc)
					        {
					             // if contain empty header (in between 2 filled row header)
							    $invalid_table = true;
							    $c=0;
							    $r--;
							}
						}
						else
						{
						    $rspc = true;
						}
					}
					if ($invalid_table)
					{
					    if (isset($form['is_new'][$id])) $err['top'][] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
					    else $err['items'][$id][] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
					    break;
					}
					$r++;
				}
			} // share article
			if (!$invalid_table)
			{
			    if (($rmax<1 || $cmax<1) || ($form['own_article'][$id] && $tbn<2))
			    {
			    	if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_MATRIX_EMPTY'];
			    	else $err['items'][$id][] = $LANG['SKU_MATRIX_EMPTY'];
			    }
			}
			// check price row
			for ($i=0;$i<$rmax;$i++)
			{
			    if (doubleval($form['tbprice'][$id][$i+1]) <= 0)
			    {
					if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_MATRIX_INVALID_PRICE'];
					else $err['items'][$id][] = $LANG['SKU_MATRIX_INVALID_PRICE'];
					break;
				}
				if ($form['sku_type'] != 'CONSIGN')
				{
					if (doubleval($form['tbcost'][$id][$i+1]) <= 0)
					{
						if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_MATRIX_INVALID_COST'];
						else $err['items'][$id][] = $LANG['SKU_MATRIX_INVALID_COST'];
						break;
					}
					if (doubleval($form['tbprice'][$id][$i+1]) < doubleval($form['tbcost'][$id][$i+1]))
					{
						if (isset($form['is_new'][$id])) $err['top'][] = $LANG['SKU_MATRIX_SELLING_BELOW_COST'];
						else $err['items'][$id][] = $LANG['SKU_MATRIX_SELLING_BELOW_COST'];
						break;
					}
				}
			}
//				$item["table_width"] = $cmax+1;
//				$item["table_height"] = $rmax+1;
		}   // matrix table check
	}

	return $err;

}*/

function update_sku_cost_changed(){
	global $con;

	$sid = mi($_REQUEST['sid']);
	$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = ".mi($sid));
	print "OK";
}

function resync_sku(){
	global $con, $LANG;
	
	// check HQ only
	if(BRANCH_CODE != 'HQ') die($LANG['HQ_ONLY']);
	
	$sid = mi($_REQUEST['sid']);
	
	$con->sql_query("select * from sku_items where id=$sid");
	$si = $con->sql_fetchassoc();
	$con->sql_freeresult();
	if(!$si)    die("SKU not found! Please contact administrator to fix this problem.");
	
	$si_list[] = $si;
	if($si['is_parent']){   // this is parent, also resync all child
		// sku
		$con->sql_query("select * from sku where id=".mi($si['sku_id']));
		$sku = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$con->sql_query("replace into sku ".mysql_insert_by_field($sku));
		
		// child sku_items
		$con->sql_query("select * from sku_items where sku_id=".mi($si['sku_id'])." and id<>$sid");
		while($r = $con->sql_fetchassoc()){
            $si_list[] = $r;
		}
		$con->sql_freeresult();
	}
	if($si_list){
		foreach($si_list as $r){
            $con->sql_query("replace into sku_items ".mysql_insert_by_field($r));
		}
	}
	
	//$con->sql_query("update sku_items set lastupdate=now() where id = ".mi($sid));
	print "OK";
}

function ajax_remove_sku_apply_photo(){
	global $con, $smarty, $sessioninfo, $config;

	$checking = array("http:");
	$file_pattern = "/[0-9]*.jpg|[0-9]*.jpeg|[0-9]*.png/";
	$file_type_pattern = "/.jpg$|.jpeg$|.png$/D";

	$file_path = urldecode($_REQUEST['file_path']);
	$sku_apply_items_id = mi($_REQUEST['sku_apply_items_id']);

	if(!$file_path || !$sku_apply_items_id) die('Delete failed');

	//$file_path_split = preg_split("/\/\//",$file_path);
	$file_path_split = preg_split("/sku_photos/",$file_path);
	$remote_domain = $file_path_split[0];
	$remote_file_path = 'sku_photos'.$file_path_split[1];
	
	if (!$config['single_server_mode'] && $remote_domain && preg_match("/^http:/", $remote_domain)){
		//if (in_array($file_path_split[0], $checking)){
			$url = $remote_domain."/http_con.php?a=delete_sku_photo&sku_apply_items_id=$sku_apply_items_id&file_path=".urlencode($remote_file_path)."&uid=$sessioninfo[id]";
					
		   	//reconnect 2 times if failed
		   	
		  	$test_time=2;
		  	for ($i=1;$i<=$test_time;$i++){
		  		
		      	$str = trim(@file_get_contents($url));

		      	if ($str) break;
			}
			
			if($str == 'OK'){
				$con->sql_query("select photo_count from sku_apply_items where id=$sku_apply_items_id");
				$sai = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($sai){
					$upd = array();
					$upd['photo_count'] = $sai['photo_count']-1;
					if($upd['photo_count']<0)	$upd['photo_count'] = 0;
					
					$con->sql_query("update sku_apply_items set ".mysql_update_by_field($upd)." where id=$sku_apply_items_id");
				}
			}
			print $str;
		//}
	}else{
		$dir_path = preg_replace($file_pattern,"",$file_path);
		if (file_exists($file_path)){
			if (@unlink($file_path)){
			    $con->sql_query("update sku_apply_items set photo_count=photo_count-1 where id=$sku_apply_items_id");
	
				log_br($sessioninfo['id'], 'MASTERFILE', $sku_apply_items_id, "Delete a photo from sku apply items id#$sku_apply_items_id");
	
				//get default file name 
				if(preg_match($file_pattern,$file_path,$matches)){
					$default_file_no = preg_replace($file_type_pattern,"",$matches[0]);
				}
				//renaming other files
				if (is_dir($dir_path)) {
				    if ($dh = opendir($dir_path)) {
				        while (($file = readdir($dh)) !== false) {
				        	if (filetype($dir_path . $file) == "file"){
				        		if (preg_match($file_type_pattern,$file,$matches)){
									$file_key = mi(preg_replace($file_type_pattern,"",$file));
									$file_array[$file_key]['name'] = $file;
									$file_array[$file_key]['extension'] = $matches[0];
								}
							}
				        }
				        closedir($dh);
				    }
				}

				if ($file_array){
					@ksort($file_array);
					$no=$default_file_no;
					foreach ($file_array as $key => $other){
						//rename file name more than removed file
						if ($no<=$key){
							$before_change = $dir_path . $other['name']; 
							$after_change = $dir_path . ($key-1) . $other['extension'];
							@rename($before_change,$after_change);
							@touch($after_change);
							@chmod($after_change,0777);
						}
						$i++;
					}
				}
			    
		        print "OK";
			}else{
		        print "Delete failed";
			}
		}else{
			print "File not existed";
		}
	}
}

function get_line_detail(&$dept_id = false){
    global $con;
    
	$q0=$con->sql_query("select department_id from category where id=".ms($_REQUEST['category_id']));
	$r0 = $con->sql_fetchrow($q0);
	$dept_id=$r0['department_id'];	
	
	$q1=$con->sql_query("select root_id from category where id=".ms($dept_id));
	$r1 = $con->sql_fetchrow($q1);
	
	$q2=$con->sql_query("select description from category where id=".ms($r1['root_id']));
	$r2 = $con->sql_fetchrow($q2);
	$line=strtoupper($r2['description']);
	return $line;
}

function update_sku_extra_info_structure(){
	global $con, $config;
	
	if(!$config['sku_extra_info'])	die("Config Not Found");
	
	$con->sql_query("explain sku_extra_info");
	$c_info = array();
	while($r = $con->sql_fetchassoc()){
		$c_info[$r['Field']] = $r;
	}
	$con->sql_freeresult();
	
	$alter_query = array();
	foreach($config['sku_extra_info'] as $c => $r){
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
		$str = "alter table sku_extra_info ".join(',', $alter_query);
		print "$str<br>";
		$con->sql_query($str);
	}
	print "Done.";
}

function sku_updated_redirect(){
	global $LANG, $smarty;
	
	$smarty->assign("url", "/masterfile_sku.php");
	$smarty->assign("title", "SKU Update");
	$smarty->assign("subject", $LANG['SKU_UPDATED']);
	$smarty->display("redir.tpl");
}
?>
