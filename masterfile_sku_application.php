<?
/*
Revision History
================
4 Apr 2007 - yinsee
- 	check for $config['sku_application_require_multics'] to enable/disable multics code checking

5/7/2007 1:12:03 PM - yinsee
- allow additional mcode format via $config['sku_application_valid_mcode']


5/16/2007 12:10:02 PM  yinsee
- moved create_sku_items() function to masterfile_sku_application.include.php
- fix a bug when no mcode/artno in table, table row/column got "eaten" during validate fail

6/27/2007 1:42:26 PM yinsee
- add "sku_application_allow_no_artno_mcode" check

7/6/2007 10:57:54 AM yinsee
- fix bug when "add item of similar vendor", cat_tree is not generated

7/16/2007 5:19:43 PM yinsee
- check only item with is_new=1 when comparing unique artno   

8/20/2007 5:53:51 PM gary
- add branch filter to allow branches view own branch sku status.

9/19/2007 1:33:42 PM gary
- if last approval checking the multics details have been fully completed.

9/26/2007 11:10:05 AM yinsee/gary
- added department check (and brand_id if softline) in bulk check_mcode

9/26/2007 6:54:36 PM gary
- fix the checking artno and mcode bug which exclude from checking.

9/27/2007 11:33:26 AM gary
- branches can view all SKU status (request by ah lee)

10/1/2007 4:25:37 PM gary
- trade discount value must greater than zero.

10/2/2007 3:16:04 PM yinsee
- use $form[trade_discount_table] to check discount value
- only PWP can have zero discount %
- update used-artno check to check on HQ for both approved and non-approved items

11/16/2007 1:56:54 PM gary
- add UOM IN SKU_APPLY_ITEMS AND SKU_ITEMS.

11/20/2007 10:38:44 AM gary
- separate out the confusing error msgs.

3/10/2008 2:30:36 PM yinsee
- allow A-Z in mcode
- 12/13/8 digit mcode must all be numeric

09.05.2008 18:16:30 Saw
- allow user to end package listing and continue new sku.

11/22/2008 6:18:12 PM yinsee
- skip softline brand check $config['sku_application_softline_require_brand']

12/26/2008 4:58:56 PM yinsee
- add $config['sku_artno_allow_specialchars']

2/2/2009 5:30:00 PM Andy
- add $config['sku_always_show_trade_discount'] checking in function validate_data

7/28/2009 1:41:37 PM Andy
- Add ctn 1 and ctn 2
* 
2009/10/15 14:23:17 PM Andy     
- Check first item must use uom_id = 1

11/17/2009 4:06:58 PM yinsee
- temporary disable matrix require at least 1 photo

11/17/2009 4:50:42 PM yinsee
- fix bug where matrix mcode will not be saved in sku_apply_items

12/14/2009 3:01:09 PM edward
- save decimal point & open price


12/29/2009 6:02:25 PM yinsee
- allow duplicate artno for matrix table if $config[sku_application_artno_allow_duplicate]
- check_and_create_approval only use department id

1/7/2010 4:48:23 PM Andy
- Fix SKU Application cause overwrite all items to only save last row mcode

1/8/2010 3:22:01 PM Andy
- Fix javascript error cause matrix cost cannot clone to next row if have $config['sku_always_show_trade_discount']

1/18/2010 4:57:09 PM Andy
- add approval order changes 

2/10/2010 3:33:56 PM Andy
- Allow special character for mcode at SKU Application if got $config['sku_artno_allow_specialchars']

3/19/2010 5:25:25 PM Andy
- Automatically show receipt description if user is the last approval
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

6/16/2010 6:12:04 PM Andy
- Fix HQ cost missing after system prompt user to key in receipt description.

8/3/2010 11:05:40 AM Alex
- Add Sku Type checking

8/13/2010 10:02:02 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

8/20/2010 11:23:34 AM - yinsee
- remove \s do not split by space when checking mcode/artno  for do_mcode_check
- not allow system-wide duplicate artno if consignment mode

8/20/2010 12:21:02 PM Justin
- add trim to remove space of artno and mcode

11/4/2010 12:15:40 PM Justin
- Modified trade discount code and trade discount type become null when the sku type is "OUTRIGHT".

12/7/2010 11:10:27 AM Andy
- Fix if sku directly approve by the creator, it will not send pm to the notifier.

12/9/2010 3:12:53 PM Justin
- Modified the HQ Cost to auto copy into the next row.
- Modified the checking for recalculate the cost price to calculate only when it is outright sku type.

12/13/2010 2:22:40 PM Justin
- Fixed the bugs where user can directly update the sku without the selecting the Trade Discount type when it is consignment type.
- Fixed the wrong checking for trade discount type while it is Outright sku type.

1/10/2011 3:32:54 PM Justin
- Added updates for S/N.

1/3/2011 5:21:50 PM Andy
- Add checking for sku application photo size. (need config)

3/10/2011 4:45:50 PM Andy
- Add PO reorder qty min & max at edit/apply SKU.

4/26/2011 1:36:20 PM Justin
- Added update for scale_type from SKU table.

5/17/2011 10:56:41 AM Andy
- Add checking for sku photo path and change path to show/add the image.

5/18/2011 4:33:49 PM Alex
- split article no to artno and size and join back when save
- use $config['ci_auto_gen_artno'] check the article no for duplicate

6/13/2011 3:15:24 PM Andy
- Add "Allow decimal qty in GRN" at SKU.

6/24/2011 4:49:41 PM Andy
- Make all branch default sort by sequence, code.

7/5/2011 5:44:32 PM Justin
- Modified the mcode to allow 5 and 6 digits.

7/6/2011 12:03:15 PM Andy
- Change split() to use explode()

7/22/2011 2:45:47 PM Alex
- add sku show other sku id if got duplicate artno or mcode

10/20/2011 10:18:31 AM Alex
- Modified the round up for cost to base on config.

10/25/2011 11:46:23 AM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/7/2011 5:41:22 PM Andy
- Add checking to skip checking for photo if not found photo element. (fix iphone,ipad browser bugs)

11/17/2011 4:35:33 PM Andy
- Add show/allow user to key in "link code" at SKU Application if got config.sku_application_show_linkcode

11/25/2011 5:28:27 PM Alex
- use mi for dept_id and vendor id to avoid sql error =>is_artmcode_used()

4/4/2012 5:06:22 PM Alex
- add checking sku terminate status when check auto gen artno

4/9/2012 6:01:04 PM Alex
- change mod to 0777 while save and upload photo => resize_image()

4/23/2012 4:44:12 PM Justin
- Added to maintain/update PO reorder qty by branch.

5/16/2012 5:16:34 PM Justin
- Fixed bugs of did not unserialize some of the components.

5/17/2012 3:16:41 PM Justin
- Fixed bugs of did not unserial po reorder qty by branch.

5/21/2012 9:58:23 AM Justin
- Fixed bugs of system could not show in proper while in packing list.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

7/2/2012 5:09:23 PM Justin
- Added new field "Scale Type" for user to maintain by item.
- Fixed bugs of auto tick "Overwrite PO Reorder qty by Branch" while return from error.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

9/4/2012 5:04 PM Drkoay
- call save_sku_items_price() during approve sku if $config['masterfile_update_sku_items_price_on_approve']=1

9/6/2012 9:21 AM Drkoay
- config masterfile_update_sku_items_price_on_approve change to consignment_new_sku_use_currency_table

2/14/2013 11:24 AM Fithri
- allow cost higher than selling

4/25/2013 4:33 PM Andy
- Add new module "SKU Application Revise List".
- Add checking to only allow owner to revise sku application, and also must login to apply branch.

5/17/2013 11:11 AM Justin
- Enhanced to manage additional description while config is turned on.

5/23/2013 2:48 PM Andy
- Fix matrix table mcode missing when revise.
- Fix warning message if the sku does not have additional description.

5/31/2013 2:50 PM Andy
- Fix when open revise, the extra info and reorder qty by branch cannot be show.
- Furhter fix warning message if the sku does not have additional description.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/30/2013 1:44 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/29/2013 3:55 PM Fithri
- fix bug where artno/mcode does not allow duplicate even when config sku_application_artno_allow_duplicate is turned on

9/13/2013 5:42 PM Justin
- Enhanced to allow user terminate Package Listing permanently by privilege.

10/17/2013 11:57 AM Andy
- Fix check artno/mcode cannot properly return the correct duplicated sku_id.

4/3/2014 2:28 PM Justin
- Enhanced to allow user maintain "PO Reorder Qty Min & Max" by SKU items.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/3/2014 11:32 AM Justin
- Enhanced to have new ability that can upload images by PDF file.

6/18/2014 4:00 PM Fithri
- fix bug where sku application doesn't allow duplicate artno even when config sku_application_artno_allow_duplicate turned on

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

7/9/2014 3:47 PM Justin
- Bug fixed on MCode format checking no longer working.

8/20/2014 5:55 PM DingRen/Justin
- add Input Tax, Output Tax, Inclusive Tax (ajax_grow_table)
- Enhanced to have calculation on GST (%) and selling price after/before GST while viewing application.

9/15/2014 5:57 PM Justin
- Enhanced to have show/hide gst settings for matrix table.

9/22/2014 3:48 PM Justin
- Bug fixed on inclusive tax get wrong info.

10/20/2014 3:20 PM Justin
- Enhanced to skip zero selling price and selling below cost price errors while Open Price is checked.

11/8/2014 11:37 AM Justin
- Enhanced to add config checking while loading gst list.

11/10/2014 4:18 PM Fithri
- allow SKU child items to have same color / size (config sku_allow_same_colour_size)

1/2/2015 4:50 PM Justin
- Enhanced to pickup GST info in details from category.

1/23/2015 2:36 PM Andy
- Change the selling price must always >0 except is open price.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/19/2015 5:58 PM Andy
- Fix wrong gp calculation.

3/24/2015 3:40 PM Andy
- Change to allow zero selling price if "allow selling foc" is ticked.

3/26/2015 4:54 PM Andy
- Fix sku item edit cost by percentage bug.

3/26/2015 6:12 PM Justin
- Bug fixed gst info capture wrongly while config is not turned on.

4/10/2015 10:37 AM Andy
- Fix sku application no update input tax/output tax/inclusive tax when revise sku.
- Fix sku application matrix no update input tax/output tax/inclusive tax.

10:39 AM 4/17/2015 Andy
- Enhance to have maxlength checking for matrix sku, mcode = 15, artno = 30.
- Increase the artno maxlength from 20 to 30.

5/29/2015 5:09 PM Justin
- Bug fixed on GST information for selling price inclusive tax show wrongly while on view mode.

7/20/2015 4:06 PM Andy
- Enhanced to load category/sku real input tax/output tax/inclusive tax on view.

10/21/2015 3:35 PM DingRen
- sku update to hqcon

11/20/2015 4:00 PM Qiu Ying
- auto resize image for sku photo

12/17/2015 1:01 PM DingRen
- add Allow Parent and Child duplicate MCode
- check check duplicate MCode

12/17/2015 5:43 PM Qiu Ying
- Add config to allow upload photo without resize

6/7/2016 2:51 PM Andy
- Fix when check duplicate mcode skip empty.

6/17/2016 10:10 AM Andy
- Enhanced to check whether images folder is uploaded, and show error image if got problem.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

3/6/2017 3:13 PM Justin
- Bug fixed on system always return (1) when checking duplicate mcode or artno.

4/21/2017 1:39 PM Justin
- Enhanced to capture "Not Allow Discount".

4/21/2017 2:15 PM Khausalya
- Enhanced changes from RM to use config setting. 

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

5/17/2017 10:31 AM Justin
- Bug fixed on "Not Allow Discount" become unticked after SKU has been approved.

9/11/2017 1:28 PM Justin
- Enhanced to have new feature "Use Matrix".
- Enhanced to pre-load size and color for Matrix while found config "enable_one_color_matrix_ibt" is turned on.

2017-09-12 14:10 PM Qiu Ying
- Bug fixed on treating special characters as wildcard character

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

5/7/2018 10:00 AM Kuan Yeh
- Added receipt description to update table in items and matrix
- Remove requirement to check last aprpoval for receipt description.
- Modified checking for receipt description

10/23/2018 10:59 AM Justin
- Enhanced the module to compatible with new SKU Type.

5/29/2019 4:38 PM William
- Added new moq and pickup.
- Enhanced to disable Moq value larger than Max value.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.

11/13/2019 4:38 PM William
- Enhanced to add new promotion photo to sku application.

1/31/2020 3:55 PM Andy
- Enhanced to show only active sku for sku application revise list.

2/25/2020 11:45 AM Andy
- Fixed to manually get new sku_id from skuManager->getMaxSKUID().

2/28/2020 1:45 PM William
- Enhanced to added new column "Marketplace Description".

7/13/2020 3:55 PM William
- Enhanced to have checkbox "Prompt when scan at POS Counter".

11/9/2020 5:35 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.
- Enhanced to have sql_begin_transaction() and sql_commit() when add sku.

11/12/2020 2:27 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
- Fixed Revise List cannot show the rejected sku applicant.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

//die("SKU Application is currently under maintenance");

include("masterfile_sku_application.include.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");
// if not HQ, connect to HQ
$hqcon = connect_hq();

// prepare path to store uploaded photo
if (!is_dir("tmp/sku"))
{
    if (!is_dir("tmp"))
	{
		mkdir("tmp");
		chmod("tmp",0777);
	}	
	
	mkdir("tmp/sku");
	chmod("tmp/sku",0777);
}

if (!is_dir("sku_photos"))
{
	mkdir("sku_photos");
	chmod("sku_photos",0777);
}
	
if(!is_ajax() && !$_REQUEST['a']){
	if(!is_writable("tmp") || !is_writable("tmp/sku") || !is_writable("sku_photos")){
		//display_redir("index.php", "SKU Application", "Image Folder Permission Error, Please contact Administrator.");
		print "<script>alert('Image Folder got permission error, upload photo may encounter some problem, please contact Administrator.');</script>";
	}
}

$con->sql_query("select id, code, fraction from uom where active order by code");
$smarty->assign("uom", $con->sql_fetchrowset());

$con->sql_query("select u.* 
				 from user u
				 left join user_privilege up on u.id=up.user_id 
				 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

while($r = $con->sql_fetchassoc()){
	$po_reorder_users[$r['id']] = $r;
}
$con->sql_freeresult();

$smarty->assign("po_reorder_users", $po_reorder_users);

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'fuse':
	        save_sku_items(intval($_REQUEST['id']));
	        exit;

	    case 'ajax_get_trade_discount_table':
	        if (isset($_REQUEST['brand_id']))
	        {
	            $did = get_department_id($_REQUEST['category_id']);
				$con->sql_query("select skutype_code, rate from brand_commission where branch_id = $sessioninfo[branch_id] and brand_id = " . mi($_REQUEST['brand_id']) . " and department_id = " . $did);
            }
            elseif (isset($_REQUEST['vendor_id']))
            {
            	$did = get_department_id($_REQUEST['category_id']);
                $con->sql_query("select skutype_code, rate from vendor_commission where branch_id = $sessioninfo[branch_id] and vendor_id = " . mi($_REQUEST['vendor_id']) . " and department_id = " . $did);
			}
			else
			{
			    print "alert('Invalid AJAX parameters');\n";
			}
            while ($r = $con->sql_fetchrow())
            {
				print "formobj.elements['trade_discount_table[$r[0]]'].value = '$r[1]';\n";
			}
			exit;
	        
	   	case 'ajax_delete_variety':
	   		$sku_item_id = intval($_REQUEST['sku_item_id']);
	   		$con->sql_query("delete from sku_apply_items where id = $sku_item_id");
	   		exit;

	    case 'ajax_check_artmcode':
	    	if ($_REQUEST['category_id'] && $_REQUEST['vendor_id']) {
				$line = get_line_detail();				
				if($line=='SOFTLINE' && isset($config['sku_application_softline_require_brand']) && !$_REQUEST['brand_id']){
					echo $LANG["SKU_SELECT_BRAND_FOR_SOFTLINE"];
					exit;
				}
				
		        if (isset($_REQUEST['artno']))
		        {
		        	$artno_artsize= strtoupper(trim($_REQUEST['artno']." ".$_REQUEST['artsize']));
		        
		        	if(!$config['sku_application_artno_allow_duplicate']){
						$i=is_artmcode_used(intval($_REQUEST['id']), strval($artno_artsize), intval($_REQUEST['vendor_id']), 'artno');
		        		if($i){
							printf ($LANG['SKU_ARTNO_REPEATED']." ($i)", $artno_artsize);					
							exit;
						}
		        	}		        	
				}
				elseif (isset($_REQUEST['mcode']))
				{
					if(!$config['sku_application_artno_allow_duplicate'] && !$_REQUEST['parent_child_duplicate_mcode']){
						if($i=is_artmcode_used(intval($_REQUEST['id']), strval($_REQUEST['mcode']), intval($_REQUEST['vendor_id']), 'mcode')){
							printf ($LANG['SKU_MCODE_REPEATED']." ($i)", $_REQUEST['mcode']);
							exit;
						}
					}
				    
				}
				print "OK";			
			}
			else{
				echo $LANG["SKU_SELECT_CATEGORY_AND_VENDOR"];
			}
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
	        $tbhqprice = $_REQUEST['tbhqprice'][$tableid];
	        $tbcost = $_REQUEST['tbcost'][$tableid];
	        $tbhqcost = $_REQUEST['tbhqcost'][$tableid];
	        $tdtype = intval($_REQUEST['trade_discount_type']);
	        $tbh = count($tb);
	        $tbw = count($tb[0]);
	        /*print "$tbh,$tbw,$add_row,$add_col<br />";
			print "<pre>";print_r($tb);print "</pre>";*/
			

			if($config['enable_one_color_matrix_ibt']){
				//$size_list = get_matrix_size();
				//$clr_list = get_matrix_color();
				// currently limit user to key in 1 color only
				$clr_list = array(0=>"RED");
				$row_count = count($clr_list)+1;
				$col_count = count($size_list)+1;
			}else{
				$row_count = $tbh+$add_row;
				$col_count = $tbw+$add_col;
			}
			
			if(!$config['enable_one_color_matrix_ibt']){
				print "<table class=input_matrix cellspacing=0 cellpadding=2 border=0>\n";
				print "<tr><td colspan=2></td>";
				for ($c=1,$real_c=-1;$c<$col_count;$c++)
				{
					if ($del_col > 0 && $c == $del_col) continue;
					$real_c++;
					print "<td align=center><font color=#999999>".chr($real_c+65)."</font></td>";
				}
				print "<td rowspan=2 align=center><font color=#999999>Selling<br />Price</font></td>";
				print '<td rowspan=2 align=center class="gst_settings"><font color=#999999>GST (<span id="span_gst_rate_'.$tableid.'">0</span>%)</font></td>';
				print '<td rowspan=2 align=center class="gst_settings"><font color=#999999>Selling Price<br /><span id="span_gst_indicator_'.$tableid.'">Before</span> GST</font></td>';
				if($config['do_enable_hq_selling'] && BRANCH_CODE =='HQ'){
					print "<td rowspan=2 align=center><font color=#999999>HQ Selling<br />Price</font></td>";
				}
				print "<td rowspan=2 align=center><font color=#999999>Cost<br />Price<br />(" . $config["arms_currency"]["symbol"] . " or %)</font></td>";
				if($config['sku_listing_show_hq_cost'] && BRANCH_CODE =='HQ'){
					print "<td rowspan=2 align=center><font color=#999999>HQ<br />Cost</font></td>";
				}
				print "</tr>\n";

				for ($r=0,$real_r=-1;$r<$row_count;$r++)
				{
					if ($del_row > 0 && $r == $del_row) continue;
					$real_r++;
					print "<tr>";
					if ($r>0)
						print "<td><font color=#999999>$real_r</font></td>";
					else
						print "<td></td>";
					for ($c=0,$real_c=-1;$c<$col_count;$c++)
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

						if ($r==0)	$td_title="title='Colors'";	//1st row Colors
						else	$td_title="title='Sizes'";	//1st column Sizes
						
						$cls = ($c==0 || $r==0) ? "onchange=uc(this) onkeydown=autocomplete_color_size(\"$type\",$tableid,$real_r,$real_c,this) alt='header' autocomplete='off' $td_title"  : "onchange=check_artmcode(this,'artno') title='Art No'";

						print "<td><input $cls name=tb[$tableid][$real_r][$real_c] id=\"".$div."_".$tableid."_".$real_r."_".$real_c."\" value=\"$v\" maxlength='30'>";
						print "<div id=\"div_".$div."_choices_".$tableid."_".$real_r."_".$real_c."\" class=\"autocomplete\" style=\"display:none;\"></div>";
						print "<br /><img src=ui/pixel.gif height=2 width=10><br />";
						$v = htmlspecialchars(strval($tbm[$r][$c]));
						$cls = ($c==0 || $r==0) ? "type=hidden" : "onchange=check_artmcode(this,'mcode')";
						print "<input $cls title='Mcode' name=tbm[$tableid][$real_r][$real_c] value=\"$v\" maxlength='15'></td>";
					}
					if ($r>0)
					{
						$v = sprintf("%.2f", $tbprice[$r]);
						print "<td><input class=ntp onblur=\"this.value=round2(this.value);";
						//print "if (document.f_a.sku_type.value == 'CONSIGN')recalculate_all_cost($tableid);";

						if ($r<$row_count-1)
						{
							print "if (this.value>0)document.f_a.elements['tbprice[$tableid][".($real_r+1)."]'].value=this.value;calc_matrix_gst($tableid,$real_r);";
						}

						print "\" name=tbprice[$tableid][$real_r] value=\"$v\"></td>";

						print '<td class="gst_settings"><input class=ntp type="text" name="tbgst['.$tableid.']['.$real_r.']" value="" readonly/></td>';
						print '<td class="gst_settings"><input class=ntp type="text" name="tbprice_gst['.$tableid.']['.$real_r.']" onblur="this.value=round2(this.value); calc_matrix_gst('.$tableid.','.$real_r.',\'gst_price\');" value="0.00"/></td>';

						if($config['do_enable_hq_selling'] && BRANCH_CODE =='HQ'){
							$v = sprintf("%.2f", $tbhqprice[$r]);
							print "<td><input class=ntp onblur=\"this.value=round(this.value, 2);";
							
							if ($r<$row_count-1)
							{
								print "if (this.value>0)document.f_a.elements['tbhqprice[$tableid][".($real_r+1)."]'].value=this.value;";
							}
							print "\" name=tbhqprice[$tableid][$real_r] value='".number_format($v, 2)."' /></td>";
						}
		
						$v = sprintf("%.3f", $tbcost[$r]);
						print "<td><input class=ntp onblur=\"matrix_cost_changed(this, $tableid, $real_r);";
						
						
						
						/*if ($r<$tbh+$add_row-1)
							print "if (this.value>0)document.f_a.elements['tbcost[$tableid][".($real_r+1)."]'].value=this.value;";*/
						$ro = ($tdtype>0) ? "readonly" : "";
						print "\" name=tbcost[$tableid][$real_r] $ro value=\"".number_format($v, $config['global_cost_decimal_points'])."\"></td>";
						if($config['sku_listing_show_hq_cost'] && BRANCH_CODE =='HQ'){
							$v = sprintf("%.3f", $tbhqcost[$r]);
							print "<td><input class=ntp onblur=\"this.value=round(this.value, ".$config['global_cost_decimal_points'].");";
							
							if ($r<$row_count-1)
							{
								print "if (this.value>0)document.f_a.elements['tbhqcost[$tableid][".($real_r+1)."]'].value=this.value;";
							}
							print "\" name=tbhqcost[$tableid][$real_r] value='".number_format($v, $config['global_cost_decimal_points'])."' /></td>";
						}
					}

					if ($r==0 && $c<=15){
						print '<td><img src="ui/tb_addcol.png" onclick="tb_expand('.$tableid.',0,1)" title="Add One Column"></td>';
					}else{
						print "<td><img src=ui/del.png title=\"Delete Row $real_r\" onclick=\"if (confirm('Are you sure?')) del_row($tableid,$real_r)\"></td>";
					}

					print "</tr>\n";
				}


				// maximum allowed is 10x10
				print "<tr><td colspan=2>";
				if ($r <= 15) print '<img src="ui/tb_addrow.png" onclick="tb_expand('.$tableid.',1,0)" title="Add One Row">';
				print "</td>";
				
				for ($c=1,$real_c=0;$c<$col_count;$c++)
				{
					if ($del_col > 0 && $c == $del_col) continue;
					$real_c++;
					print "<td><img src=ui/del.png title=\"Delete Column ".chr($real_c+64)."\" onclick=\"if (confirm('Are you sure?')) del_col($tableid,$real_c)\"></td>";
				}

				if ($r <= 15 && $c <= 15)
				{
					print "<td>&nbsp;</td>";
					if($config['do_enable_hq_selling'] && BRANCH_CODE=='HQ'){
						print "<td>&nbsp;</td>";
					}
					print "<td>&nbsp;</td>";
					print "<td>&nbsp;</td>";
					print "<td>&nbsp;</td>";
					if($config['sku_listing_show_hq_cost'] && BRANCH_CODE=='HQ'){
						print "<td>&nbsp;</td>";
					}

					print '<td colspan=2><img src="ui/tb_addrowcol.png" onclick="tb_expand('.$tableid.',1,1)" title="Grow Table"></td>';
				}
				print "</tr></table>\n";
			}else{
				for($c=1,$real_c=-1;$c<$col_count;$c++){
					if ($del_col > 0 && $c == $del_col) continue;
					$real_c++;
					$alp_list[] = chr($real_c+65);
				}
				
				$smarty->assign("form", $_REQUEST);
				$smarty->assign("alp_list", $alp_list);
				$smarty->assign("clr_list", $clr_list);
				$smarty->assign("size_list", $size_list);
				$smarty->display("masterfile_sku_application.matrix.tpl");
			}
			exit;

	    case 'ajax_add_form':
	        $category_id = mi($_REQUEST['cat_id']);
			$sku_type = trim($_REQUEST['sku_type']);

			if(!$sku_type||!$category_id)   $last_approval = false;
			else{
                $last_approval = check_is_last_approval_of_sku_application($category_id, $sku_type, $sessioninfo['id'], $sessioninfo['branch_id']);
			}

			$smarty->assign("last_approval", $last_approval);
			$smarty->assign("item_n", intval($_REQUEST['n']));
	        $smarty->assign("item_type", 'variety');
	        $smarty->display("masterfile_sku_application.items.tpl");
			exit;

	    case 'ajax_add_matrix':
	        $category_id = mi($_REQUEST['cat_id']);
			$sku_type = trim($_REQUEST['sku_type']);

			if(!$sku_type||!$category_id)   $last_approval = false;
			else{
                $last_approval = check_is_last_approval_of_sku_application($category_id, $sku_type, $sessioninfo['id'], $sessioninfo['branch_id']);
			}
			$smarty->assign("last_approval", $last_approval);
			$smarty->assign("item_n", intval($_REQUEST['n']));
	        $smarty->assign("item_type", 'matrix');
	        $smarty->display("masterfile_sku_application.items.tpl");
			exit;

	    case 'ajax_show_tree':
	        $id = intval($_REQUEST['root']);
	        $a = $con->sql_query("select id, description, tree_str from category c where tree_str like '%($id)' order by tree_str");
	        $n = 0;
	        while ($r=$con->sql_fetchrow($a))
		    {
		        $pv = str_replace('+', '\+', $v);
		        $pv = str_replace('\\', '\\\\', $v);
				$tt = get_category_tree($r['id'], $r['tree_str'], $have_child) . " > ";
				$lbl = $tt . preg_replace("/^($pv)/i", "<span class=sh>\\1</span>", $r['description']);
				$des = str_replace("'", "\'", $r['description']);
				$tt = str_replace("'", "\'", $tt);
				if ($have_child)
				{
		    		print "<a href=\"javascript:void(show_child($r[id]))\"> $lbl <img src=ui/expand.gif align=absmiddle border=0></a>\n";
		    	}
       			//else
		    	{
		    	    print "<a href=\"javascript:void(select_category($r[id], '$tt', '$des'))\"> $lbl</a>\n";
    			}
		    	$n++;
		    }
		    exit;

		case 'mcode_check':
		    do_mcode_check();
		    exit;

		case 'save':
			$no_prompt = 1;

			// form submitted
			$form = array();
			$items = array();

			$errm = validate_data($form, $items);
			//print_r($items);exit;
			
			
			
			if (!$errm)
			{
				$hqcon->sql_begin_transaction();
				
				// check if we need approval, if yes, create one and store the ID
				$hqcon->sql_query("select department_id from category where id = $form[category_id]");
				$tstr = $hqcon->sql_fetchrow();

		        $params = array();
		        $params['branch_id'] = $sessioninfo['branch_id'];
		        $params['type'] = 'SKU_APPLICATION';
		        $params['sku_type'] = $form['sku_type'];
		        $params['dept_id'] = $tstr[0];
		        $params['user_id'] = $sessioninfo['id'];
		        $params['reftable'] = 'sku';
		        $params['extra_sql'] = $extra_sql;
		        $astat = check_and_create_approval2($params, $hqcon);
       			if (!$astat)
				{
					$errm['top'][] = $LANG['SKU_NO_APPROVAL_FLOW'];
				}
				else
				{
					$form['approval_history_id'] = $astat[0];
				}
			}

			if (!$errm)
			{
       			$form['apply_branch_id'] = $sessioninfo['branch_id'];
       			//$form['trade_discount_table'] = serialize($form['trade_discount_table']);

       			// if approval list is empty (applicant is the last person in the list)
				// then set status as approved
       			if ($astat[1] == '|')
			    	$form['status'] = 1;
				else
				    $form['status'] = 0;
			    $form['added'] = date("Y-m-d H:i:s");
				
				if($form['sku_type'] != "CONSIGN"){
					$form['default_trade_discount_code'] = '';
	  				$form['trade_discount_type'] = 0;
				}
				
				// save or Update sku
				if ($form['id'] > 0)
				{
					// we save update to local instead of server to get rid of
					// notification still remain at home page after revise,
					// due to replication delay
					
					$insert_field = array("id", "apply_by","apply_branch_id", "category_id","sku_type","vendor_id","brand_id","listing_fee_type","listing_fee_remark","approval_history_id", "trade_discount_type", "default_trade_discount_code", "multics_dept", "multics_section", "multics_category", "multics_brand", "multics_pricetype", "remark", "note", "status", "added","no_inventory","is_fresh_market", "po_reorder_qty_min", "po_reorder_qty_max", "po_reorder_moq" , "scale_type", "po_reorder_notify_user_id", "po_reorder_by_child");

					// check if the sku type is outright, reset the default trade discount code and trade discount type
					if($form['sku_type'] != "CONSIGN"){	
						if($form['po_reorder_qty_setup'] && $form['po_reorder_qty_by_branch']){
							$form['po_reorder_qty_by_branch'] = serialize($form['po_reorder_qty_by_branch']);
						}else $form['po_reorder_qty_by_branch'] = "";
						
						$insert_field[] = "po_reorder_qty_by_branch";
					}
					
					if($config['enable_sn_bn']) $insert_field[] = "have_sn";
					if($config['sku_non_returnable'])	$insert_field[] = "group_non_returnable";
					
					if($config['enable_gst']){
						$insert_field[] = "mst_input_tax";
						$insert_field[] = "mst_output_tax";
						$insert_field[] = "mst_inclusive_tax";
					}

                    $insert_field[]="parent_child_duplicate_mcode";
					
					$hqcon->sql_query("replace into sku " . mysql_insert_by_field($form, $insert_field));
					$skuid = $form['id'];
				}
				else
				{
					$insert_field = array("apply_by","apply_branch_id", "category_id","sku_type","vendor_id","brand_id","listing_fee_type","listing_fee_remark","approval_history_id", "trade_discount_type", "default_trade_discount_code", "multics_dept", "multics_section", "multics_category", "multics_brand", "multics_pricetype", "remark", "note", "status","added","no_inventory","is_fresh_market", "po_reorder_qty_min", "po_reorder_qty_max", "po_reorder_moq" , "scale_type", "po_reorder_notify_user_id", "po_reorder_by_child");

					
					// check if the sku type is outright, reset the default trade discount code and trade discount type
					if($form['sku_type'] != "CONSIGN"){	
						if($form['po_reorder_qty_setup'] && $form['po_reorder_qty_by_branch']){
							$form['po_reorder_qty_by_branch'] = serialize($form['po_reorder_qty_by_branch']);
						}else $form['po_reorder_qty_by_branch'] = "";
						
						$insert_field[] = "po_reorder_qty_by_branch";
					}
					
					if($config['enable_sn_bn']) $insert_field[] = "have_sn";
					if($config['sku_non_returnable'])	$insert_field[] = "group_non_returnable";
					
					if($config['enable_gst']){
						$insert_field[] = "mst_input_tax";
						$insert_field[] = "mst_output_tax";
						$insert_field[] = "mst_inclusive_tax";
					}

                    $insert_field[]="parent_child_duplicate_mcode";
					
					$form['id'] = $appCore->skuManager->getMaxSKUID()+1;
					$insert_field[]="id";
				   	$hqcon->sql_query("insert into sku " . mysql_insert_by_field($form, $insert_field));
					$skuid = $hqcon->sql_nextid();
				}

				// save or update items
				foreach ($items as $item)
				{				
				    $item['sku_id'] = $skuid;
					if ($item['item_type']=='variety')
					{
						$item['artno']=strtoupper(trim($item['artno']." ".$item['artsize']));
						$item['category_disc_by_branch_inherit'] = serialize($item['category_disc_by_branch_inherit']);
						$item['category_point_by_branch_inherit'] = serialize($item['category_point_by_branch_inherit']);
						if($item['extra_info'])	$item['extra_info'] = serialize($item['extra_info']);
						
						$si_field = array('sku_id','artno','mcode','description','receipt_description','selling_price','cost_price','photo_count', 'description_table', 'packing_uom_id','ctn_1_uom_id','ctn_2_uom_id','open_price','decimal_qty','hq_cost','ri_id', 'doc_allow_decimal','allow_selling_foc','selling_foc','link_code','cat_disc_inherit','category_disc_by_branch_inherit','category_point_inherit','category_point_by_branch_inherit', 'scale_type', 'po_reorder_qty_min', 'po_reorder_qty_max','po_reorder_moq', 'po_reorder_notify_user_id', 'hq_selling', 'not_allow_disc', 'weight_kg', 'model', 'width', 'height', 'length');
						if($config['sku_non_returnable'])	$si_field[] = 'non_returnable';
						if($config['sku_extra_info']) $si_field[] = "extra_info";
						
						
						
						if($config['sku_enable_additional_description']){
							$additional_description_list = $additional_description = array();
							$si_field[] = "additional_description";
							$si_field[] = "additional_description_print_at_counter";
							$si_field[] = "additional_description_prompt_at_counter";
							$additional_description_list = explode("\n", trim($item['additional_description']));
							foreach($additional_description_list as $tmp_r=>$add_desc){
								if(!trim($add_desc)) continue;
								$additional_description[] = trim($add_desc);
							}
							if($additional_description) $item['additional_description'] = serialize($additional_description);
							else $item['additional_description'] = "";
							$item['additional_description_print_at_counter'] = $item['additional_description_print_at_counter'];
							$item['additional_description_prompt_at_counter'] = $item['additional_description_prompt_at_counter'];
						}
									
							
						
						if($config['enable_sn_bn'] && $item['sn_we']){
							$si_field[] = "sn_we";
							$si_field[] = "sn_we_type";
						}
						
						if(privilege('MST_INTERNAL_DESCRIPTION')){
							$si_field[] = "internal_description";
						}
						
						if($config['arms_marketplace_settings']){
							$si_field[] ="marketplace_description";
						}
						
						if($config['enable_gst']){
							$si_field[] = "input_tax";
							$si_field[] = "output_tax";
							$si_field[] = "inclusive_tax";
						}
						
						$si_field[] = "use_rsp";
						$si_field[] = "rsp_price";
						$si_field[] = "rsp_discount";
						
        				if ($item['id'] > 0){
        					$si_field[] = "id";
        					
        					$hqcon->sql_query("replace into sku_apply_items " . mysql_insert_by_field($item, $si_field));
        				}   
        				else{
        					$hqcon->sql_query("insert into sku_apply_items " . mysql_insert_by_field($item, $si_field));
        				}
        			}
        			else
        			{
        			    // save matrix as a serialized string
        			    if ($item['own_article'])
						{
							$item['artno'] = '';
							$item['mcode'] = '';
						}
        			    // "compress" table
        			    $tb = array();
        			    $tbp = array();
						$tbc = array();
        			    for ($r=0;$r<$item["table_height"];$r++)
        			    {
        			        for ($c=0;$c<$item["table_width"];$c++)
        			        {
								$tb[$r][$c] = $item['tb'][$r][$c];
								$tbm[$r][$c] = $item['tbm'][$r][$c];
							}
							$tbp[$r] = $item['tbprice'][$r];
							$tbhp[$r] = $item['tbhqprice'][$r];
							$tbc[$r] = $item['tbcost'][$r];
							$tbh[$r] = $item['tbhqcost'][$r];
						}

        			    $item['product_matrix'] = serialize(array('cols' => $item['table_width'], 'rows' => $item['table_height'], 'tb' => $tb, 'tbm' => $tbm, 'tbprice' => $tbp, 'tbcost' => $tbc, 'tbhqcost'=>$tbh, 'tbhqprice'=>$tbhp));
												
						$matrix_fields = array('sku_id','artno','mcode','description','receipt_description','photo_count','product_matrix');
						if($config['enable_gst']){
							$matrix_fields[] = "input_tax";
							$matrix_fields[] = "output_tax";
							$matrix_fields[] = "inclusive_tax";
						}
						
					    if ($item['id'] > 0){
							$matrix_fields[] = "id";
							$hqcon->sql_query("replace into sku_apply_items " . mysql_insert_by_field($item, $matrix_fields));
						}        			    	
        			    else{
							$hqcon->sql_query("insert into sku_apply_items " . mysql_insert_by_field($item, $matrix_fields));
						}
					}
       			    if ($item['id'] > 0)
       			        $subid = $item['id'];
					else
   			    		$subid = $hqcon->sql_nextid();
				
					// move pictures
					$counter = 0;
					$group_num = ceil($subid/10000);
					if ($item['saved_photo'])
					{
						check_and_create_dir("sku_photos/apply_photo");
						if(function_exists('use_new_sku_photo_path') && use_new_sku_photo_path()){
							check_and_create_dir("sku_photos/apply_photo/".$group_num);
							$sku_apply_photo_path = "sku_photos/apply_photo/".$group_num."/".$subid;
						}else{
							$sku_apply_photo_path = "sku_photos/$subid";
						}
						check_and_create_dir($sku_apply_photo_path);
						
					    /*if (!is_dir($sku_apply_photo_path))
						{
							//print "<li> Create sbudir sku_photos/$subid";
							mkdir($sku_apply_photo_path);
							chmod($sku_apply_photo_path,0777);
						}*/
						foreach ($item['saved_photo'] as $image)
						{
						    if (file_exists("$image.tmp"))
						    	@unlink("$image.tmp");
						    @rename("$image", "$image.tmp");
						}
						foreach ($item['saved_photo'] as $row=>$image)
						{
						    $counter++;
						    // remove existing images
						    if (file_exists("$sku_apply_photo_path/$counter.jpg")) 
						    	@unlink("$sku_apply_photo_path/$counter.jpg");

							// remove existing pdf
							if (file_exists("$sku_apply_photo_path/$counter.pdf")) 
						    	@unlink("$sku_apply_photo_path/$counter.pdf");
								
							// found it is PDF file, need to convert the first page become image
							if($item['saved_pdf'][$row]){
								/*$params = array();
								$params['path'] = $sku_apply_photo_path;
								$params['image_path'] = $image;
								$params['pdf_path'] = $item['saved_pdf'][$row];
								$params['counter'] = $counter;
								pdf_handler($params);*/
							}
							copy("$image.tmp", "$sku_apply_photo_path/$counter.jpg");
							@unlink("$image.tmp");
						}
					}
					$sku_promo_photo_path = "sku_photos/apply_promo_photo/".$group_num."/".$subid;
					if ($item['promotion_photo']){
						
						check_and_create_dir("sku_photos/apply_promo_photo");
						check_and_create_dir("sku_photos/apply_promo_photo/".$group_num);
						check_and_create_dir($sku_promo_photo_path);
						if($item['promotion_photo']){
							$promo_photo = $item['promotion_photo'];
						}
						if (file_exists("$promo_photo.tmp")) {
							@unlink("$promo_photo.tmp");
						}
						
						@rename("$promo_photo", "$promo_photo.tmp");
						
						if (file_exists("$sku_promo_photo_path/1.jpg")) {
							@unlink("$sku_promo_photo_path/1.jpg");
						}
						copy("$promo_photo.tmp", "$sku_promo_photo_path/1.jpg");
						@unlink("$promo_photo.tmp");
					}else{
						if(file_exists("$sku_promo_photo_path/1.jpg")){
							@unlink("$sku_promo_photo_path/1.jpg");
						}
					}
    			}

				// if approved (application is by last person in approval flow)
                if ($form['status']==1){
                    save_sku_items($skuid);
                    if($config['consignment_new_sku_use_currency_table']){
                      save_sku_items_price($skuid);
					}  
                    $aid = mi($form['approval_history_id']);
					
					/*
                    // get the PM list
					$hqcon->sql_query("select flow_approvals, approvals, sku.apply_by, notify_users from approval_history left join sku on approval_history.ref_id = sku.id where approval_history.id = $aid");
					$r = $hqcon->sql_fetchrow();

					$recipients = $r[3];
         			$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
         			$to = preg_split("/\|/", $recipients);
                    // send pm
					send_pm($to, "New SKU Application (ID#$skuid) ".$approval_status['1'], "masterfile_sku_application.php?a=view&id=$skuid");
					*/
                                      
				} 
				
				$to = get_pm_recipient_list2($skuid,$form['approval_history_id'],0,'confirmation',0,'sku');
				send_pm2($to, "New SKU Application (ID#$skuid) ".$approval_status[$form['status']], "masterfile_sku_application.php?a=view&id=$skuid", array('module_name'=>'sku'));

				// send PM
				if ($form['approval_history_id'])
				{
					$hqcon->sql_query("update approval_history set ref_id = $skuid where id = $form[approval_history_id]");

					// get the PM list
					/*$hqcon->sql_query("select notify_users from approval_history where id = $form[approval_history_id]");
					$r = $hqcon->sql_fetchrow();
					//print "ID = $form[approval_history_id]";

	               	$recipients = str_replace("|$sessioninfo[id]|", "|", $r[0]);
	               	$to = preg_split("/\|/", $recipients);

					// send pm
					send_pm($to, "New SKU Application (ID#$skuid)", "masterfile_sku_application.php?a=view&id=$skuid");
					*/
				}

				//add to log
				if ($form['id'] > 0)
					{
					log_br($sessioninfo['id'], 'MASTERFILE', $skuid, "New SKU Application Revise: (ID#$skuid)");
					}
				else
					{
					log_br($sessioninfo['id'], 'MASTERFILE', $skuid, "New SKU Application: (ID#$skuid)");
					}
					
				$hqcon->sql_commit();
				
				// perform forwarding to prevent reload
				// print "<a href=\"$_SERVER[PHP_SELF]?a=complete&id=$skuid&listing_fee_type=$form[listing_fee_type]\">Continue</a>";
				header("Location: $_SERVER[PHP_SELF]?a=complete&id=$skuid&listing_fee_type=$form[listing_fee_type]");
				exit;
			}
			else
			{
			    $hqcon->sql_query("select tree_str, description from category where id = $form[category_id]");
			    $r = $hqcon->sql_fetchrow();
			    $form['cat_desc'] = strtoupper($r['description']);
			    $form['cat_tree'] = htmlentities(get_category_tree($form['category_id'], $r['tree_str'], $dummy)  . " > " . $r['description']);
			    $smarty->assign("form", $form);

				//split artno to artno and size
				foreach ($items as $k => $item){
					split_artno_size($item);		
					$items[$k]['artno']=$item['artno'];
					$items[$k]['artsize']=$item['artsize'];
				}
				
			    $smarty->assign("items", $items);
			    $smarty->assign("errm", $errm);
			}
			
			
			
			break;

		case 'complete':

		    if ($_REQUEST['listing_fee_type'] == 'Package' && check_listing_package($LANG['SKU_CONTINUE_TO_NEXT_IN_PACKAGE']))
		    {
				$no_prompt = 1;
			}
			else
			{
				$smarty->assign("id", intval($_REQUEST['id']));
				$smarty->assign("PAGE_TITLE", "SKU Application Completed");
				$smarty->display("masterfile_sku_application.complete.tpl");
	            exit;
            }
            break;

		case 'view':
			/*if (!privilege('MST_SKU_UPDATE') && !privilege('MST_SKU_APPROVAL') && !privilege('MST_SKU_APPLY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE or MST_SKU_APPROVAL or MST_SKU_APPLY', BRANCH_CODE), "/index.php");*/
		    view_application();
			exit;			

		case 'revise':

			// get sku
			$no_prompt = 1;
			$smarty->assign("revise", 1);
		    $skuid = intval($_REQUEST['id']);
		    $hqcon->sql_query("select sku.*, vendor.description as vendor, brand.description as brand from sku left join vendor on sku.vendor_id = vendor.id left join brand on sku.brand_id = brand.id 
		    where sku.id = $skuid");
			$form = $hqcon->sql_fetchrow();
			if (!$form)
			{
			    $smarty->assign("url", "/home.php");
			    $smarty->assign("title", "SKU Application");
			    $smarty->assign("subject", sprintf($LANG['SKU_APPLICATION_NOT_EXIST'], $skuid));
			    $smarty->display("redir.tpl");
			    exit;
			}

			// check status
			if (($form['status']!=0 && $form['status']!=2) || $form['apply_by'] != $sessioninfo['id'])
			{
			    $smarty->assign("url", "/home.php");
			    $smarty->assign("title", "SKU Application");
			    $smarty->assign("subject", sprintf($LANG['SKU_REVISE_NOT_ALLOWED'],$skuid));
			    $smarty->display("redir.tpl");
			    exit;
			}
			
			if($form['apply_branch_id'] != $sessioninfo['branch_id']){
				$smarty->assign("url", "/home.php");
			    $smarty->assign("title", "SKU Application");
			    $smarty->assign("subject", sprintf($LANG['SKU_CANT_REVISE_OTHER_BRANCH'],$skuid));
			    $smarty->display("redir.tpl");
			    exit;
			}

			//$form['trade_discount_table'] = unserialize($form['trade_discount_table']);
		    $hqcon->sql_query("select tree_str, description from category where id = $form[category_id]");

		    // get category tree
		    $r = $hqcon->sql_fetchrow();
		    $form['category'] = strtoupper($r['description']);
		    $form['cat_tree'] = htmlentities(get_category_tree($form['category_id'], $r['tree_str'], $dummy)  . " > " . $r['description']);
			$form['po_reorder_qty_by_branch'] = unserialize($form['po_reorder_qty_by_branch']);
			
			if($form['po_reorder_qty_by_branch']){
				foreach($form['po_reorder_qty_by_branch']['min'] as $bid=>$min_qty){
					$max_qty = $form['po_reorder_qty_by_branch']['max'][$bid];
					$moq_qty = $form['po_reorder_qty_by_branch']['moq'][$bid];
					if(($min_qty || $max_qty) && $max_qty<=$min_qty) $invalid_prqb_branches[] = get_branch_code($bid);
					if($min_qty || $max_qty) $have_po_reorder_qty_by_branch = true;
					
					if(($moq_qty && $max_qty) && $max_qty < $moq_qty) $invalid_prqb_branches2[] = get_branch_code($bid);
				}
				if($have_po_reorder_qty_by_branch)	$form['po_reorder_qty_setup'] = 1;	
			}
				
			// get approval/reject comment
			$hqcon->sql_query("select approval_history_items.status, approval_history_items.timestamp, approval_history_items.log, user.u from ((approval_history_items left join approval_history on approval_history_id = approval_history.id) left join user on approval_history_items.user_id = user.id) where approval_history_id = $form[approval_history_id] or (approval_history.ref_table = 'sku' and approval_history.ref_id = $skuid) order by timestamp");
			$approval = array();
			while ($r = $hqcon->sql_fetchrow())
			{
			    $r['log'] = unserialize($r['log']);
			    array_push($approval, $r);
			}
			$form['approval_history_items'] = $approval;
		    $lrmk = unserialize($form['listing_fee_remark']);
		    switch ($form['listing_fee_type'])
			{
				case 'Listing Fee' :
				    $form['listing_fee_amount'] = $lrmk['amount'];
					$form['listing_fee_when'] = $lrmk['when'];
					$form['listing_fee_dn'] = $lrmk['dn'];
					break; // straight fwd amount

				case 'In Kind' :
				    $form['listing_fee_inkind'] = unserialize($form['listing_fee_remark']);
					break;

			    case 'Package' :
			        $form['listing_fee_package_amount'] = $lrmk['amount'];
			        $form['listing_fee_package_count'] = $lrmk['count'];
			        $form['listing_fee_when'] = $lrmk['when'];
					$form['listing_fee_dn'] = $lrmk['dn'];
			        $form['listing_fee_package_first_sku_id'] = $lrmk['first_sku_id'];
					break;

			    case 'Package2' :
			        $form['listing_fee_package_amount'] = $lrmk['amount'];
			        $form['listing_fee_package_count'] = $lrmk['count'];
			        $form['listing_fee_when'] = $lrmk['when'];
					$form['listing_fee_dn'] = $lrmk['dn'];
					break;

			}
		    // get items
			$hqcon->sql_query("select * from sku_apply_items where sku_id = $skuid order by id");
			$items = array();
			while ($item = $hqcon->sql_fetchrow())
			{
			    if ($item['product_matrix'] != '')
			    {
					$arr = unserialize($item['product_matrix']);
				    $item['tb'] = $arr['tb'];
				    $item['tbm'] = $arr['tbm'];
				    $item['tbprice'] = $arr['tbprice'];
				    $item['tbhqprice'] = $arr['tbhqprice'];
				    $item['tbcost'] = $arr['tbcost'];
				    $item['tbhqcost'] = $arr['tbhqcost'];
				    $item['item_type'] = 'matrix';
			    }
			    else
			    {
			        $item['item_type'] = 'variety';
				}
				$item['own_article'] = ($item['artno']=='' && $item['mcode']=='') ? 1 : 0;
			    $item['description_table'] = unserialize($item['description_table']);
				$item['category_disc_by_branch_inherit'] = unserialize($item['category_disc_by_branch_inherit']);
				$item['category_point_by_branch_inherit'] = unserialize($item['category_point_by_branch_inherit']);
				if($item['extra_info'])	$item['extra_info'] = unserialize($item['extra_info']);
				
			    $item['description0'] = $item['description_table'][0];
			    $item['description1'] = $item['description_table'][1];
			    $item['description2'] = $item['description_table'][2];
			    $item['description3'] = $item['description_table'][3];
			    $item['description4'] = $item['description_table'][4];
			    $item['description5'] = $item['description_table'][5];
				if($config['sku_enable_additional_description'] && $item['additional_description']) $item['additional_description'] = join("\n", unserialize($item['additional_description']));

				$group_num = ceil($item['id']/10000);
				if(function_exists('use_new_sku_photo_path') && use_new_sku_photo_path()){
					$sku_apply_photo_path = "sku_photos/apply_photo/".$group_num."/".$item['id'];
				}else{
					$sku_apply_photo_path = "sku_photos/$item[id]";
				}
						
			    for ($i=1; $i<=$item['photo_count']; $i++)
			    {
			        $item['saved_photo'][] = "$sku_apply_photo_path/$i.jpg";
				}
				
				$sku_apply_promotion_photo_path = "sku_photos/apply_promo_photo/".$group_num."/".$item['id'];
				if(file_exists("$sku_apply_promotion_photo_path/1.jpg")){
					$item['promotion_photo'] = "$sku_apply_promotion_photo_path/1.jpg";
				}
				split_artno_size($item);
			    array_push($items, $item);
			}
		    $smarty->assign("form", $form);
		    $smarty->assign("items", $items);
		    break;
		    //$smarty->assign("errm", $errm);

		case 'list':
		    $status_filter = '';
			$join_items = "sku";
			$_REQUEST['search_code'] = trim($_REQUEST['search_code']);
			
			$where = "(sku.apply_by = $sessioninfo[id] or flow_approvals like '%|$sessioninfo[id]|%' or notify_users like '%|$sessioninfo[id]|%')";
			
			// if superuser, view all
			if ($sessioninfo['level'] >= 800) $where = "1";

		    if (isset($_REQUEST['sku_id']))
			{
				//$where = "1";
				$status_filter .= " and sku.id = " . mi($_REQUEST['sku_id']);
			}
			elseif (isset($_REQUEST['search_code']) && $_REQUEST['search_code']!='')
			{
				//$where = "1";
				$ll = preg_split("/\s+/", $_REQUEST['search_code']);
				$desc_match = array();
				foreach ($ll as $l)
				{
				    if ($l) $desc_match[] = "sku_apply_items.description like " . ms('%'.replace_special_char($l).'%');
				}
				$desc_match = join(" and ", $desc_match);

				$status_filter .= " and (($desc_match) or sku_apply_items.artno like ".ms(replace_special_char($_REQUEST['search_code']).'%')." or sku_apply_items.mcode like ".ms(replace_special_char($_REQUEST['search_code']).'%').") or sku_apply_items.product_matrix like ".ms('%:"'.replace_special_char($_REQUEST['search_code']).'%";%');
				$join_items = "sku_apply_items left join sku on sku_apply_items.sku_id = sku.id";
			}
			else
			{
				if ($_REQUEST['status'] >= 0)
				{
					$status_filter = "and sku.status = " . mi($_REQUEST['status']);
					if (intval($_REQUEST['status']) == 1 || intval($_REQUEST['status']) == 4)
					{
						if (strstr($_REQUEST['status'], 'a'))
						{
							$status_filter .= " and ";
						}
						else
						{
							$status_filter .= " and not ";
						}
						$status_filter .= "(approval_history.approvals = '' or approval_history.approvals is null or approval_history.approvals = '|')";
					}
				}

				if (isset($_REQUEST['branch_id']) || isset($_REQUEST['department_id']))
				{
					if ($_REQUEST['branch_id'] > 0)
						$status_filter .= " and sku.apply_branch_id = " . mi($_REQUEST['branch_id']);

					if ($_REQUEST['department_id'] > 0)
						$status_filter .= " and category.department_id = " . mi($_REQUEST['department_id']);

				}
			}

			if (isset($_REQUEST['sz']))
				$sz = intval($_REQUEST['sz']);
			else
				$sz = 50;
			if (isset($_REQUEST['s']))
				$s = intval($_REQUEST['s']);
			else
				$s = 0;

			$where .= " $status_filter";
			
			/*
			Request by Ah lee (Branches can view all SKU status.)
			if(BRANCH_CODE != 'HQ'){
				$where .=" and sku.apply_branch_id =".mi($sessioninfo['branch_id'])."";
			}
			*/
			
   			$r1 = $hqcon->sql_query("select distinct sku.*, approvals, flow_approvals as org_approvals, notify_users, brand.description as brand, category.description as category, category.tree_str, branch.code as branch,approval_order_id,approved_by from $join_items left join approval_history on sku.approval_history_id = approval_history.id left join brand on sku.brand_id = brand.id left join category on sku.category_id = category.id left join branch on sku.apply_branch_id = branch.id where $where order by sku.timestamp limit $s, $sz");
   	
			$sku = array();
			while ($r = $hqcon->sql_fetchrow($r1))
			{
		
			  $r['approved_by'] = get_user_list($r['approved_by']);
			  if($r['approvals']==''||$r['approvals']=='|'){
			    $r['app_status'] = 1;  // done approval
        }
        if(!$r['app_status']){
          $r['org_approvals'] = str_replace($r['approvals'],"|",$r['org_approvals']);
  				$r['approvals'] = get_user_list($r['approvals']," > ", $r['approval_order_id']);
  				$r['org_approvals'] = get_user_list($r['org_approvals']," > ", $r['approval_order_id']);
        }
				
				$r['cat_tree'] = get_category_tree($r['category_id'], $r['tree_str'], $dummy) . " > <font color=red>" . $r['category'] . "</font>";
				$r['u'] = get_user_list($r['apply_by']);
				array_push($sku, $r);
			}
			$smarty->assign("sku", $sku);
                      
			// page selection if record is more than page size
			$hqcon->sql_query("select count(distinct sku.id) from $join_items left join approval_history on sku.approval_history_id = approval_history.id left join category on sku.category_id = category.id where $where");
			$t = $hqcon->sql_fetchrow();
			$total = $t[0];
			if ($total > $sz)
			{
			    // create pagination
			    $pg = "&nbsp;&nbsp; <b>Page</b> <select name=s onchange=\"form.submit()\">";
			    for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
			    {
			        $pg .= "<option value=$i";
			        if ($i == $s) $pg .= " selected";
					$pg .= ">$p</option>";
				}
				$pg .= "</select>";
				$smarty->assign("pagination", $pg);
			}
			
			// branch
			$con->sql_query("select id, code from branch where active=1 order by sequence,code");
			$smarty->assign("branch", $con->sql_fetchrowset());
			
			// dept
	        if ($sessioninfo['level'] <= 9999)
	        {
	        	if ($sessioninfo['departments'])
					$depts = join(",", array_keys($sessioninfo['departments']));
				else
					$depts = 0;
				$con->sql_query("select id, description from category where active and level = 2 and id in ($depts) order by description");
			}
			else
			{
				$con->sql_query("select id, description from category where active and level = 2 order by description");
			}
			$smarty->assign("dept", $con->sql_fetchrowset());

			$smarty->assign("PAGE_TITLE", "SKU Application Status");
			$smarty->display("masterfile_sku_application.status.tpl");
			exit;
		case 'skip_listing':
			$no_prompt = 1;
			break;
		case 'ajax_check_is_last_approval':
		    ajax_check_is_last_approval();
		    exit;
		case 'ajax_check_fm_type':
			$con->sql_query("select cc.is_fresh_market from category_cache cc where cc.category_id = ".mi($_REQUEST['category_id']));
			$type = $con->sql_fetchrow();

			print $type['is_fresh_market'];
			exit;
		case 'ajax_get_new_artno':
			ajax_get_new_artno();
			exit;
		case 'revise_list':
			revise_list();
			exit;
		case 'terminate_listing':
			terminate_package();
			break;
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
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

if (!privilege('MST_SKU_APPLY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_APPLY', BRANCH_CODE), "/index.php");

// check if previously added as package, if yes then we fix the package
if (!$no_prompt ) check_listing_package($LANG['SKU_CONTINUE_FROM_PREVIOUS_PACKAGE']);

if (isset($_REQUEST['copy_id']))
{
	// copy header from previous application
	$id = intval($_REQUEST['copy_id']);
	$hqcon->sql_query("select sku.*, user.u as username, branch.code as apply_branch_code, branch.ip as apply_branch_ip, category.description as category, category.tree_str as tree_str,  vendor.description as vendor, brand.code as brand_code, brand.active as brand_active, brand.description as brand from ((((sku left join category on sku.category_id = category.id) left join vendor on sku.vendor_id = vendor.id) left join brand on sku.brand_id = brand.id) left join user on sku.apply_by = user.id) left join branch on sku.apply_branch_id = branch.id where sku.id = $id");
	$sku = $hqcon->sql_fetchrow();
	$sku['id'] = 0;
	if ($sku['brand_id'] == 0) $sku['brand'] = 'UN-BRANDED';
	$sku['cat_tree'] = htmlentities(get_category_tree($sku['category_id'], $sku['tree_str'], $dummy)  . " > " . $sku['category']);
	$sku['po_reorder_qty_by_branch'] = unserialize($sku['po_reorder_qty_by_branch']);
	$smarty->assign("form", $sku);
}

$con->sql_query("select id,code from trade_discount_type order by code");
$smarty->assign("trade_discount_table", $con->sql_fetchrowset());
$con->sql_freeresult();

$con->sql_query("select u.* 
				 from user u
				 left join user_privilege up on u.id=up.user_id 
				 where up.privilege_code = 'NT_STOCK_REORDER' and u.active = 1 and u.is_arms_user=0");

while($r = $con->sql_fetchassoc()){
	$po_reorder_users[$r['id']] = $r;
}
$con->sql_freeresult();

$smarty->assign("po_reorder_users", $po_reorder_users);
// set default discount code to N1
//if (!$smarty->get_template_vars("form")) $smarty->assign("form", array("default_trade_discount_code"=>"N1"));

// load dummy data for matrix
if($config['enable_one_color_matrix_ibt']){
	$matrix['use_matrix'] = "yes";
	$clr_list = get_matrix_color();
	$size_list = get_matrix_size();
	
	if($clr_list && $size_list){
		foreach($clr_list as $arr1=>$clr){
			foreach($size_list as $arr2=>$size){
				$min_qty = rand(1,5);
				$max_qty = rand(5,10);
				$is_nnr = rand(0,1);
				$matrix['matrix']['is_nnr'][$size] = $is_nnr;
				$matrix['matrix']['min_qty'][$clr][$size] = $min_qty;
				$matrix['matrix']['max_qty'][$clr][$size] = $max_qty;
			}
		}
	}
	
	$smarty->assign("matrix", $matrix);
}

$smarty->assign("PAGE_TITLE", "SKU Application");
$smarty->display("masterfile_sku_application.tpl");
exit;

// check mcode in bulk
function do_mcode_check()
{
	global $con, $sessioninfo, $smarty;

    $outs = '';
	if ($_REQUEST['list'])
	{
	    // 8/20/2010 11:23:34 AM - yinsee (remove \s do not split by space)
	    foreach (preg_split("/[\n\r]+/", $_REQUEST['list']) as $code)
	    {
	        if (trim($code) == '') continue;
	        $chk = is_artmcode_used(0, $code, intval($_REQUEST['vendor_id']), 'artno');
	        if (!$chk)
	            $chk = is_artmcode_used(0, $code, intval($_REQUEST['vendor_id']), 'mcode');

	        if ($chk == "Invalid Code")
				$outs .= "<li> $code - <font color=red>$chk</font>";
			elseif ($chk)
			    $outs .= "<li> $code - <font color=red>CODE REPEAT ($chk)</font>";
			else
			    $outs .= "<li> $code - NEW CODE";
		}
	}
	$con->sql_query("select description from vendor where id = ".mi($_REQUEST['vendor_id']));
	$r = $con->sql_fetchrow();
	$smarty->assign("vendor", $r[0]);
	$smarty->assign("msg", $outs);
	$smarty->display("masterfile_sku_checkmcode.tpl");
}

// return true if article number exist
function is_artmcode_used($exclude_id, $code, $vendor_id, $type){
	global $con, $hqcon, $config;

	$line = get_line_detail($dept_id);	
	$vendor_id=mi($vendor_id);
	$dept_id=mi($dept_id);
	/*
	if($line=='SOFTLINE' && $_REQUEST['brand_id']==''){
		return "Please Enter Brand for SOFTLINE category.";
	}*/
	$code = strtoupper(trim($code));
	
	/*if ($type == 'artno')
		$code = preg_replace("/[^A-Z0-9]/","",$code);
	else*/
	if (!$config['sku_artno_allow_specialchars']) $code = preg_replace("/[^A-Z0-9]/","",$code);
	if ($code == '') return "Invalid Code";

	// for approved sku, check the code in sku items
	if ($type == 'artno'){
	    if ($config['consignment_modules'])
	    {
	        $hqcon->sql_query("select concat('SKU ',sku_id) as id, $type
from sku_items
left join sku on sku_id = sku.id
where sku_id <> $exclude_id and $type = ".ms($code));
		}
		else if($line=='SOFTLINE'){
			$hqcon->sql_query("select concat('SKU ',sku_id) as id, $type, c1.department_id
from sku_items 
left join sku on sku_id = sku.id
left join category c1 on c1.id=sku.category_id
left join brand on brand.id=sku.brand_id
where sku_id <> $exclude_id and vendor_id = $vendor_id and $type = ".ms($code)." and c1.department_id=".ms($dept_id)." and sku.brand_id=".ms($_REQUEST['brand_id']));
		}
		else{
			$hqcon->sql_query("select concat('SKU ',sku_id) as id, $type, c1.department_id
from sku_items 
left join sku on sku_id = sku.id
left join category c1 on c1.id=sku.category_id
where sku_id <> $exclude_id and vendor_id = $vendor_id and $type = ".ms($code)." and c1.department_id=$dept_id");		
		}
	}
	else{
		$hqcon->sql_query("select concat('SKU ',sku_id) as id, $type from sku_items where sku_id <> $exclude_id and $type = ".ms($code));
	}
	$r = $hqcon->sql_fetchrow();
 	
	if (!$r) 
 	{
		// for unapproved sku, check the code in sku apply items
		if ($type == 'artno'){
			if($line=='SOFTLINE'){
				$hqcon->sql_query("select concat('APPLICATION ',sku.id) as id, $type, product_matrix, c1.department_id
from sku_apply_items 
left join sku on sku_apply_items.sku_id = sku.id
left join category c1 on c1.id=sku.category_id 
left join brand on brand.id=sku.brand_id
where is_new and (sku.status <> 4 and sku.active=0) and vendor_id = $vendor_id and sku_id <> $exclude_id and ($type = " . ms($code) . " or product_matrix like ".ms('%:"'.replace_special_char($code).'";%').") and c1.department_id=$dept_id and sku.brand_id=".ms($_REQUEST['brand_id'])." group by sku_apply_items.description");			
			}
			else{
				$hqcon->sql_query("select concat('APPLICATION ',sku.id) as id, $type, product_matrix, c1.department_id 
from sku_apply_items 
left join sku on sku_apply_items.sku_id = sku.id
left join category c1 on c1.id=sku.category_id 
where is_new and (sku.status <> 4 and sku.active=0) and vendor_id = $vendor_id and sku_id <> $exclude_id and ($type = " . ms($code) . " or product_matrix like ".ms('%:"'.replace_special_char($code).'";%').") and c1.department_id=$dept_id group by sku_apply_items.description");			
			}		
		}
		else{
			$hqcon->sql_query("select concat('APPLICATION ',sku.id) as id, $type, product_matrix from sku_apply_items left join sku on sku_apply_items.sku_id = sku.id where is_new and (sku.status <> 4 and sku.active=0) and sku_id <> $exclude_id and ($type = " . ms($code) . " or product_matrix like ".ms('%:"'.replace_special_char($code).'";%').") group by description");	
		}
		$r = $hqcon->sql_fetchrow();
		if (!$r) return false;
	}
	
	// if mcode matches art_mcode
	if ($r[$type] == $code) return $r['id'];

	// check if mcode matches product matrix
	$tmp = unserialize($r['product_matrix']);
	if ($type == 'artno')
		$tb = $tmp['tb'];
	else
		$tb = $tmp['tbm'];

	if (!is_array($tb)) return false;

	array_shift($tb);   // remove header row
	foreach ($tb as $row)
	{
		array_shift($row); // remove header column
  		foreach ($row as $item)
  		{
			if ($code == $item) return $r['id'];
		}
	}
	return false;

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

// approval of new sku item
function view_application()
{
    global $smarty, $sessioninfo, $con, $LANG, $config, $output_tax_list, $input_tax_list;
	$id = intval($_REQUEST['id']);

	$q1 = $con->sql_query("select id,code from trade_discount_type order by code");
	$smarty->assign("trade_discount_table", $con->sql_fetchrowset());
	$con->sql_freeresult($q1);

	$q1 = $con->sql_query("select sku.*, user.u as username, branch.code as apply_branch_code, branch.ip as apply_branch_ip, category.description as category, category.tree_str as tree_str,  vendor.description as vendor, brand.code as brand_code, brand.active as brand_active, brand.description as brand from ((((sku left join category on sku.category_id = category.id) left join vendor on sku.vendor_id = vendor.id) left join brand on sku.brand_id = brand.id) left join user on sku.apply_by = user.id) left join branch on sku.apply_branch_id = branch.id where sku.id = $id");
	$sku = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if (!$sku){
	    $smarty->assign("url", "/home.php");
	    $smarty->assign("title", "SKU Application");
	    $smarty->assign("subject", sprintf($LANG['SKU_APPLICATION_NOT_EXIST'], $id));
	    $smarty->display("redir.tpl");
	    exit;
	}
/*
	// check approval flow if user is allowed to approve/view
	if ($sku['apply_by'] != $sessioninfo['id'])
	{
		$hqcon->sql_query("select flow_approvals, approvals, notify_users from approval_history where id = $sku[approval_history_id]");
		if (!$sku['approval_history_id'] || $app = $hqcon->sql_fetchrow()) // no flow
		{
		    // not allowed
		    if (!strstr($app[0], "|$sessioninfo[id]|") && !strstr($app[1], "|$sessioninfo[id]|") && !strstr($app[2], "|$sessioninfo[id]|") && !privilege('MST_SKU_UPDATE') && $sessioninfo['level']<9999)
		    {
		        $smarty->assign("url", "/home.php");
			    $smarty->assign("title", "SKU Application");
			    $smarty->assign("subject", sprintf($LANG['SKU_NO_ACCESS'], $id));
			    $smarty->display("redir.tpl");
			    exit;
		    }
		    elseif (!strstr($app[1], "|$sessioninfo[id]|")) // not in approval list
		    {
				if ($_REQUEST['a'] == 'approval') $_REQUEST['a'] = 'view';
			}
			else
			{
			    // check if last approval
			    $smarty->assign("last_approval", ($app[1] == "|$sessioninfo[id]|"));
			}
		}
		elseif ($sessioninfo['level']<9999) // if not admin
		{
		    // no such flow
		    $smarty->assign("url", "/home.php");
		    $smarty->assign("title", "SKU Application");
		    $smarty->assign("subject", sprintf($LANG['SKU_NO_FLOW'], $id));
			//"The SKU Application ID#$id does not have approval flow.");
		    $smarty->display("redir.tpl");
		    exit;
		}
		else    // admin is readonly
		{
	    	$_REQUEST['a'] = 'view';
	    }

	}
	elseif ($_REQUEST['a'] == 'approval') // view by owner
	    $_REQUEST['a'] = 'view';
*/
	$sku['listing_fee_remark'] = unserialize($sku['listing_fee_remark']);
	$sku['po_reorder_qty_by_branch'] = unserialize($sku['po_reorder_qty_by_branch']);
	$sku['category'] = get_category_tree($sku['category_id'], $sku['tree_str'], $dummy)  . " > " . $sku['category'];

	$tb = array();
	if ($sku['trade_discount_type'] == 1){
		$did = get_department_id($sku['category_id']); //get dept
		$q1 = $con->sql_query("select skutype_code, rate from brand_commission where branch_id = $sku[apply_branch_id] and brand_id = $sku[brand_id] and department_id = " . $did);

        while ($r = $con->sql_fetchassoc($q1)){
            $tb[$r[0]] = $r[1];
		}
		$con->sql_freeresult($q1);
	}elseif ($sku['trade_discount_type'] == 2){
    	$did = get_department_id($sku['category_id']); //get dept
        $q1 = $con->sql_query("select skutype_code, rate from vendor_commission where branch_id = $sku[apply_branch_id] and vendor_id = $sku[vendor_id] and department_id = ".$did);
        while ($r = $con->sql_fetchassoc($q1)){
            $tb[$r[0]] = $r[1];
		}
		$con->sql_freeresult($q1);
    }
	$sku['trade_discount_table'] = $tb;

	// get approval/reject comment
	$q1 = $con->sql_query("select approval_history_items.status, approval_history_items.timestamp, approval_history_items.log, user.u 
						   from approval_history_items 
						   left join user on approval_history_items.user_id = user.id 
						   where approval_history_id = $sku[approval_history_id]
						   order by timestamp");
	$approval = array();
	while ($r = $con->sql_fetchassoc($q1)){
	    $r['log'] = unserialize($r['log']);
	    array_push($approval, $r);
	}
	$con->sql_freeresult($q1);
	$sku['approval_history_items'] = $approval;
	
	if($config['enable_gst']){
		// get category tax info
		$sku['cat_input_tax'] = $cat_input_tax = get_category_gst("input_tax", $sku['category_id'], array('no_check_use_zero_rate'=>1));
		$sku['cat_output_tax'] = $cat_output_tax = get_category_gst("output_tax", $sku['category_id'], array('no_check_use_zero_rate'=>1));
		$sku['cat_inclusive_tax'] = $cat_inclusive_tax = get_category_gst("inclusive_tax", $sku['category_id']);
		
		// real tax info
		$sku['real_input_tax'] = $sku['mst_input_tax'] > 0 ? $input_tax_list[$sku['mst_input_tax']] : $sku['cat_input_tax'];
		$sku['real_output_tax'] = $sku['mst_output_tax'] > 0 ? $output_tax_list[$sku['mst_output_tax']] : $sku['cat_output_tax'];
		$sku['real_inclusive_tax'] = (!$sku['mst_inclusive_tax'] || $sku['mst_inclusive_tax'] == 'inherit')	? $sku['cat_inclusive_tax'] : $sku['mst_inclusive_tax'];
	}
	
	$smarty->assign("form", $sku);
	
	$q1 = $con->sql_query("select sku_apply_items.*, uom.code as uom,ri.group_name as ri_group_name
						   from sku_apply_items 
						   left join uom on uom.id=packing_uom_id
						   left join ri on ri.id=sku_apply_items.ri_id
						   where sku_id = $id");
	$items = array();
	while ($item = $con->sql_fetchassoc($q1))
	{		
		if($config['enable_gst']){
			// input tax
			if($item['input_tax'] > 0){
				// use own
				$item['input_tax_code'] = $input_tax_list[$item['input_tax']]['code'];
				$item['input_tax_rate'] = $input_tax_list[$item['input_tax']]['rate'];
			}else{
				// use real
				$item['input_tax_code'] = $sku['real_input_tax']['code'];
				$item['input_tax_rate'] = $sku['real_input_tax']['rate'];
			}
			
			// output tax
			if($item['output_tax'] > 0){
				$item['output_tax_code'] = $output_tax_list[$item['output_tax']]['code'];
				$item['output_tax_rate'] = $output_tax_list[$item['output_tax']]['rate'];
			}else{
				$item['output_tax_code'] = $sku['real_output_tax']['code'];
				$item['output_tax_rate'] = $sku['real_output_tax']['rate'];
			}
			
			// inclusive tax
			$item['real_inclusive_tax'] = $item['inclusive_tax'] == "inherit" ? $sku['real_inclusive_tax'] : $item['inclusive_tax'];
			
			if($item['inclusive_tax'] == "inherit") $inclusive_tax = $cat_inclusive_tax;
			$item['real_inclusive_tax'] = $inclusive_tax;
		
			// get output tax follow by item > sku > category
			if($item['output_tax'] == -1) $output_tax = $sku['mst_output_tax'];
			else $output_tax = $item['output_tax'];
			
			if($output_tax == -1) $item['gst_rate'] = $cat_output_tax['rate'];
			else $item['gst_rate'] = $output_tax_list[$output_tax]['rate'];
			
		}
		
		

	    $arr = unserialize($item['product_matrix']);
	    $item['tb'] = $arr['tb'];
	    $item['tbm'] = $arr['tbm'];
	    $item['tbprice'] = $arr['tbprice'];
	    $item['tbhqprice'] = $arr['tbhqprice'];
	    $item['tbcost'] = $arr['tbcost'];
	    $item['tbhqcost'] = $arr['tbhqcost'];
	    $item['description_table'] = unserialize($item['description_table']);
	    $item['category_disc_by_branch_inherit'] = unserialize($item['category_disc_by_branch_inherit']);
	    $item['category_point_by_branch_inherit'] = unserialize($item['category_point_by_branch_inherit']);
		$item['extra_info'] = unserialize($item['extra_info']);
		if($config['sku_enable_additional_description'] && $item['additional_description']) $item['additional_description'] = join("\n", unserialize($item['additional_description']));
		
		$group_num = ceil($item['id']/10000);
		$sku_apply_photo_path = "sku_photos/apply_promo_photo/".$group_num."/".$item['id'];
		if(file_exists("$sku_apply_photo_path/1.jpg")){
			$item['promotion_photo'] = "$sku_apply_photo_path/1.jpg";
		}
	    split_artno_size($item);
	    array_push($items, $item);
	}
	$con->sql_freeresult($q1);

	//print_r($items);
	$smarty->assign("items", $items);
	
	$hurl = get_branch_file_url($sku['apply_branch_code'], $sku['apply_branch_ip']);
	$smarty->assign("image_path", $hurl);
	$smarty->assign("thumbnail_path", $hurl);

	$smarty->assign("PAGE_TITLE", "New SKU Approval");
	$smarty->display("masterfile_sku_approval.tpl");
}

/*
	if last item is package fee
	    if not first item
	        if total_sku_added >= package_count
	        	return // done
			else
			    continue nx item in package (ref_id = lastitem.ref_id)
		else
		    continue nx item in package (ref_id = this.id)
	endif
*/
function check_listing_package($msg)
{
	global $hqcon, $sessioninfo, $smarty;
	$hqcon->sql_query("select * from sku where apply_by = $sessioninfo[id] order by id desc limit 1");
	$r = $hqcon->sql_fetchrow();

	if ($r['listing_fee_type'] != 'Package') return false; // last sku is not package

	$t[0] = 1;
	$rm = unserialize($r['listing_fee_remark']);
	if ($rm['first_sku_id'] > 0)
	{
		$hqcon->sql_query("select count(id) from sku where apply_by = $sessioninfo[id] and listing_fee_type = 'Package' && listing_fee_remark like '%\"first_sku_id\";i:$rm[first_sku_id];}'");
		$t = $hqcon->sql_fetchrow();
		$t[0]++;
		if ($t[0]>=$rm['count'])
		{
		    //print "<script>alert('Completed')</script>";
			return false;
		}
	    $form['listing_fee_package_first_sku_id'] = $rm['first_sku_id'];
	}
	else
	{
	    $form['listing_fee_package_first_sku_id'] = $r['id'];
	}
    $msg = sprintf($msg, $t[0]+1, $rm['count']);
    print "<script>alert('$msg')</script>";

	$form['category_id'] = $r['category_id'];
	$form['sku_type'] = $r['sku_type'];
//    $form['trade_discount_table'] = unserialize($r['trade_discount_table']);
	$form['vendor_id'] = $r['vendor_id'];
	$form['brand_id'] = $r['brand_id'];
	$form['listing_fee_package_current'] = $t[0]+1;
    $form['listing_fee_type'] = $r['listing_fee_type'];
    $form['listing_fee_package_amount'] = $rm['amount'];
    $form['listing_fee_when'] = $rm['when'];
	$form['listing_fee_dn'] = $rm['dn'];
    $form['listing_fee_package_count'] = $rm['count'];
    $form['po_reorder_qty_by_branch'] = unserialize($r['po_reorder_qty_by_branch']);

    $hqcon->sql_query("select tree_str, description from category where id = $form[category_id]");
    $r = $hqcon->sql_fetchrow();
    $form['cat_desc'] = strtoupper($r['description']);
    $form['cat_tree'] = htmlentities(get_category_tree($form['category_id'], $r['tree_str'], $dummy)  . " > " . $r['description']);

	$smarty->assign("form", $form);

	return true;
}

// return the required photo from min_sku_photo field of category
// if -1, check the root (and keep going up)
function get_required_photo_count($cat_id, $skutype)
{
	global $con;

	$min[$skutype] = -1;
	while ($min[$skutype] == -1 and $cat_id > 0)
	{
		$con->sql_query("select root_id, min_sku_photo from category where id = $cat_id");
		$r = $con->sql_fetchrow();
		$cat_id = $r[0];
		$min = unserialize($r[1]);
		if (!isset($min[$skutype])) $min[$skutype] = -1;
	}
	return $min[$skutype];
}


/*function check_last_approval($f)
{
	global $con, $sessioninfo;
	
	$did = get_department_id($f['category_id']);
	if ($f['apply_branch_id'] == 0) $f['apply_branch_id'] = $sessioninfo['branch_id'];
	
	$con->sql_query("select approvals from approval_flow where branch_id = " . mi($f['apply_branch_id']) . " and type = 'SKU_APPLICATION' and sku_category_id = $did and sku_type = " . ms($f['sku_type']) . " and active");

	$r = $con->sql_fetchrow();
	if (!$r) return false;
	if (preg_match("/\|$sessioninfo[id]\|$/", $r[0]))
	{
		return true;
	}
	return false;
}*/

function validate_data(&$form, &$items)
{
	global $hqcon, $LANG, $sessioninfo, $last_approval, $smarty, $config, $con;

	//print "<pre>"; print_r($_REQUEST);print"</pre>";
	$err = array();
		
    $form = $_REQUEST;

	$form['id'] = intval($_REQUEST['id']);
	$form['apply_by'] = $sessioninfo['id'];

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
		/*if (!$form['receipt_description']) 
		{
			$err['top'][] = $LANG['SKU_LAST_APPROVAL_ENTER_RECEIPT_DESCRIPTION'];
			print "<script>alert('".$LANG['SKU_LAST_APPROVAL_ENTER_RECEIPT_DESCRIPTION']."')</script>\n";
		}*/
	}
	//die();
	// check fields
	$form['category_id'] = intval($_REQUEST['category_id']);
	if ($form['category_id'] == 0)
		$err['top'][] = $LANG['SKU_INVALID_CATEGORY'];

	$SKU_MIN_PHOTO_REQUIRED = get_required_photo_count($form['category_id'], $form['sku_type']);

	$form['vendor_id'] = intval($_REQUEST['vendor_id']);
	if ($form['vendor_id'] == 0)
		$err['top'][] = $LANG['SKU_INVALID_VENDOR'];

	if (!$form['sku_type'])
		$err['top'][] = $LANG['SKU_INVALID_TYPE'];

	$form['brand_id'] = intval($_REQUEST['brand_id']);
	
	if(!$form['po_reorder_qty_min'] && !$form['po_reorder_qty_max'] && $form['po_reorder_moq']){
		$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "");
	}elseif($form['po_reorder_qty_min'] && $form['po_reorder_qty_max'] || $form['po_reorder_moq']){
		if($form['po_reorder_qty_max']<=$form['po_reorder_qty_min']){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
		}
		if($form['po_reorder_qty_max'] < $form['po_reorder_moq']){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "");
		}
	}elseif(!$form['po_reorder_qty_min'] && !$form['po_reorder_qty_max'] && $form['po_reorder_notify_user_id']){ // found no set min & max but have notify person
		$err['top'][] = sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "");
	}
	if($form['po_reorder_qty_min'] || $form['po_reorder_qty_max']){
		if(count($err['top']) > 0 && in_array(sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], ""),$err['top'])){
			
		}else{
			if($form['po_reorder_qty_max']<=$form['po_reorder_qty_min']){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
			}
		}
	}

	if($form['po_reorder_qty_setup']){
		$invalid_prqbnuid_branches = $invalid_prqb_branches = array();
		$invalid_prqbnuid_branches2 = $invalid_prqb_branches2 = array();
		foreach($form['po_reorder_qty_by_branch']['min'] as $bid=>$min_qty){
			$max_qty = $form['po_reorder_qty_by_branch']['max'][$bid];
			$moq_qty = $form['po_reorder_qty_by_branch']['moq'][$bid];
			$notify_user_id = $form['po_reorder_qty_by_branch']['notify_user_id'][$bid];
			if(!$min_qty && !$max_qty && $moq_qty){
				$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "");
			}elseif($min_qty && $max_qty || $moq_qty){
				if(($min_qty || $max_qty) && $max_qty<=$min_qty) $invalid_prqb_branches[] = get_branch_code($bid);
				if($min_qty || $max_qty || $moq_qty) $have_po_reorder_qty_by_branch = true;
				if(!$min_qty && !$max_qty && $notify_user_id) $invalid_prqbnuid_branches[] = get_branch_code($bid);
				if(!$moq_qty && !$max_qty && $notify_user_id) $invalid_prqbnuid_branches2[] = get_branch_code($bid);
				if(($moq_qty && $max_qty) && $max_qty < $moq_qty) $invalid_prqb_branches2[] = get_branch_code($bid);
			}
			if($min_qty || $max_qty){
				if(($min_qty || $max_qty) && $max_qty<=$min_qty) $invalid_prqb_branches[] = get_branch_code($bid);
			}
		}
		if(count($invalid_prqb_branches) > 0){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "for branch ".join(", ", $invalid_prqb_branches));
		}
		
		if(count($invalid_prqb_branches2) > 0){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ'], "for branch ".join(", ", $invalid_prqb_branches2));
		}
		
		if(count($invalid_prqbnuid_branches) > 0){
			$err['top'][] = sprintf($LANG['SKU_PO_REORDER_NOTIFY_PERSON_ERROR'], "for branch ".join(", ", $invalid_prqbnuid_branches));
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
		
	$form['listing_fee_type'] = strval($_REQUEST['listing_fee_type']);
	$form['listing_fee_amount'] = doubleval($_REQUEST['listing_fee_amount']);
	$form['listing_fee_inkind'] = $_REQUEST['listing_fee_inkind'];
	$form['mst_input_tax'] = $_REQUEST['mst_input_tax'];
	$form['mst_output_tax'] = $_REQUEST['mst_output_tax'];
	$form['mst_inclusive_tax'] = $_REQUEST['mst_inclusive_tax'];

	switch ($form['listing_fee_type'])
	{
	    case 'No Listing Fee' :
			$form['listing_fee_remark'] = '';
			break; // no fee

		case 'Listing Fee' :
			$form['listing_fee_when'] = strval($_REQUEST['listing_fee_when']);
			$form['listing_fee_dn'] = strval($_REQUEST['listing_fee_dn']);
			if ($form['listing_fee_amount'] <= 0)
				$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE'];
			$form['listing_fee_remark'] = serialize(array(
				"amount" => $form['listing_fee_amount'],
				"when" => $form['listing_fee_when'],
				"dn" => $form['listing_fee_dn']));
			break; // straight fwd amount

		case 'In Kind' :
			for ($i=0; $i<count($form['listing_fee_inkind']['item'])-1;$i++)
			{
			    if ($form['listing_fee_inkind']['item'][$i] == '' || $form['listing_fee_inkind']['qty'][$i] <= 0 || $form['listing_fee_inkind']['cost'][$i] <= 0 || $form['listing_fee_inkind']['total_cost'][$i] <= 0)
			    {
					$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE'];
					break;
				}
			}
			$form['listing_fee_remark'] = serialize($form['listing_fee_inkind']);
			break;

	    case 'Package' :
			$form['listing_fee_when'] = strval($_REQUEST['listing_fee_when2']);
			$form['listing_fee_dn'] = strval($_REQUEST['listing_fee_dn2']);
			$form['listing_fee_package_amount'] = doubleval($_REQUEST['listing_fee_package_amount']);
			$form['listing_fee_package_count'] = intval($_REQUEST['listing_fee_package_count']);
			$form['listing_fee_package_first_sku_id'] = intval($_REQUEST['listing_fee_package_first_sku_id']);
			if ($form['listing_fee_package_amount'] <= 0)
				$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE'];
			if ($form['listing_fee_package_count'] < 2)
				$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE_PACKAGE_COUNT'];
			$form['listing_fee_remark'] =
				serialize(array("amount"=>$form['listing_fee_package_amount'],
				"count"=>$form['listing_fee_package_count'],
				"when" => $form['listing_fee_when'],
				"dn" => $form['listing_fee_dn'],
				"first_sku_id" => $form['listing_fee_package_first_sku_id'] ));
			break;

	    case 'Package2' :
			$form['listing_fee_when'] = strval($_REQUEST['listing_fee_when3']);
			$form['listing_fee_dn'] = strval($_REQUEST['listing_fee_dn3']);
			$form['listing_fee_package_amount'] = doubleval($_REQUEST['listing_fee_package_amount2']);
			$form['listing_fee_package_count'] = intval($_REQUEST['listing_fee_package_count2']);
			if ($form['listing_fee_package_amount'] <= 0)
				$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE'];
			if ($form['listing_fee_package_count'] < 2)
				$err['top'][] = $LANG['SKU_INVALID_LISTING_FEE_PACKAGE_COUNT'];
			$form['listing_fee_remark'] =
				serialize(array("amount"=>$form['listing_fee_package_amount'],
				"count"=>$form['listing_fee_package_count'],
				"when" => $form['listing_fee_when'],
				"dn" => $form['listing_fee_dn'] ));
			break;

		default:
		    print "<pre>";
			print_r($_REQUEST);
			print "</pre>";
		    die("Error: Unhandled Listing Fee Type $form[listing_fee_type]");

	}

	//checking the multics details if only the applicant is last approval too.
	if ($config['sku_application_require_multics'] && $last_approval)
	{
		if ($form['multics_dept'] == '' || $form['multics_section'] == '' || $form['multics_category'] == '' || $form['multics_brand'] == '' || $form['multics_pricetype'] == '')
		{
		    	$err['top'][] = $LANG['SKU_EMPTY_LINK_CODE'];
		}

		if (!find_multics_code(file("MDEPT.dat"), $form['multics_dept']))
		    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Department");
		if (!find_multics_code(file("MSECT.dat"), $form['multics_section']))
		    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Section");
		if (!find_multics_code(file("MCAT.dat"), $form['multics_category']))
		    $err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Category");
		if (!find_multics_code(file("MBRAND.dat"), $form['multics_brand']))
	    	$err['top'][] = sprintf($LANG['SKU_INVALID_LINK_CODE'], "Brand");
	}
	
	//check auto gen artno
	if ($config['ci_auto_gen_artno']){
		foreach ($form['artno'] as $key => $value){
			$artno_array[$value]=$value;
		}

		//check duplicate artno	
		$cartno_rid=$con->sql_query("select si.* from sku_items si left join sku on sku.id=si.sku_id 
									where si.artno between ".ms(min($artno_array))." and ".ms(max($artno_array)."Z")." and sku.status <> 4 limit 1");

		$cartno_rid2=$con->sql_query("select sai.* from sku_apply_items sai left join sku on sku.id=sai.sku_id
										where sai.artno between ".ms(min($artno_array))." and ".ms(max($artno_array)."Z")." and sku.status <> 4 limit 1");

		if ($con->sql_numrows($cartno_rid)>0 || $con->sql_numrows($cartno_rid2)>0){
			$err['top'][]=$LANG['SKU_CI_ARTNO_USED'];

			$max_rid=$con->sql_query("select SUBSTRING_INDEX(max(artno),' ',1) as m_artno from sku_items where artno between ".ms($form['max_artno'])." and ".ms($form['max_artno']."Z"));
			$max_artno=$con->sql_fetchassoc($max_rid);
	
			$max_rid2=$con->sql_query("select SUBSTRING_INDEX(max(artno),' ',1) as m_artno from sku_apply_items where artno between ".ms($form['max_artno'])." and ".ms($form['max_artno']."Z"));
			$max_artno2=$con->sql_fetchassoc($max_rid2);
	
			$m_artno= $max_artno2['m_artno'] > $max_artno['m_artno'] ? $max_artno2['m_artno'] : $max_artno['m_artno']; 
	
			if ($m_artno){
				$form['max_num'] = mi(preg_replace("/^$form[max_artno]/", "", $m_artno));
			}
	
			$con->sql_freeresult($max_rid);
			$con->sql_freeresult($max_rid2);
		}

		$con->sql_freeresult($cartno_rid);
		$con->sql_freeresult($cartno_rid2);
	}

	// check items
	$n = 0;
	$mcode_used = array();
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
            $item['id'] = intval($_REQUEST['subid'][$n]);
		    $item['own_article'] = intval($_REQUEST['own_article'][$n]);
		    $item['ctn_1_uom_id'] = mi($_REQUEST['ctn_1_uom_id'][$n]);
		    $item['ctn_2_uom_id'] = mi($_REQUEST['ctn_2_uom_id'][$n]);
      		$item['open_price'] = intval($_REQUEST['open_price'][$n]);
      		$item['decimal_qty'] = intval($_REQUEST['decimal_qty'][$n]);
      		$item['doc_allow_decimal'] = intval($_REQUEST['doc_allow_decimal'][$n]);
      		$item['ri_id'] = mi($_REQUEST['ri_id'][$n]);
      		$item['ri_group_name'] = trim($_REQUEST['ri_group_name'][$n]);
      		$item['scale_type'] = trim($_REQUEST['dtl_scale_type'][$n]);
		    $item['extra_info'] = $_REQUEST['extra_info'][$n];
		    $item['input_tax'] = $_REQUEST['dtl_input_tax'][$n];
		    $item['output_tax'] = $_REQUEST['dtl_output_tax'][$n];
		    $item['inclusive_tax'] = $_REQUEST['dtl_inclusive_tax'][$n];
		    
		    if($config['sku_non_returnable']){
				$item['non_returnable'] = mi($_REQUEST['non_returnable'][$n]);
			}
			
			//join artno and size while save   
			$item['artno'] = strtoupper(trim($_REQUEST['artno'][$n]." ".$_REQUEST['artsize'][$n]));

			if (!$config['sku_artno_allow_specialchars']) $item['artno'] = preg_replace("/[^A-Z0-9]/", "", $item['artno']);
			if (!$config['sku_application_artno_allow_duplicate'] && $item['artno'] != '')
			{
				if (isset($artno_used[$item['artno']]))
					$this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $item['artno'], "in variety ".$artno_used[$item['artno']]);
            	$artno_used[$item['artno']] = $n;
			}
			
			$item['mcode'] = trim($_REQUEST['mcode'][$n]);
            if($item['mcode'])	$all_mcode[]=$item['mcode'];
			if (!$config['sku_artno_allow_specialchars']) $item['mcode'] = preg_replace("/[^A-Z0-9]/", "", $item['mcode']);
			if (!$config['sku_application_artno_allow_duplicate'] && $item['mcode'] != '')
			{
				if (isset($mcode_used[$item['mcode']]))
				    $this_err[] = sprintf($LANG['SKU_MCODE_USED'], $item['mcode'], "in variety ".$mcode_used[$item['mcode']]);
            	$mcode_used[$item['mcode']] = $n;
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
			
			/*if(isset($_REQUEST['receipt_description'][$n])){
			$item['receipt_description'] = strval($_REQUEST['receipt_description'][$n]);
			}else{
			$this_err[] = "Receipt Description Invalid";	
			} */
			
			
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
				$item['description2'] = strval($_REQUEST['description2'][$n]);
				$item['description3'] = strval($_REQUEST['description3'][$n]);
				$item['description4'] = strval($_REQUEST['description4'][$n]);
				$item['description5'] = strval($_REQUEST['description5'][$n]);
				$item['description_table'] = serialize(array($item['description0'], $item['description1'], $item['description2'], $item['description3'], $item['description4'], $item['description5']));
				$item['selling_price'] = doubleval($_REQUEST['selling_price'][$n]);
				$item['cost_price'] = doubleval($_REQUEST['cost_price'][$n]);
				if($config['sku_listing_show_hq_cost']&&BRANCH_CODE=='HQ'){
                    $item['hq_cost'] = doubleval($_REQUEST['hq_cost'][$n]);
				}
				$item['allow_selling_foc'] = mi($_REQUEST['allow_selling_foc'][$n]);
				$item['selling_foc'] = $item['allow_selling_foc'] ? mi($_REQUEST['selling_foc'][$n]) : 0;
				if($item['selling_price'] <= 0 && $item['allow_selling_foc']) $item['selling_foc'] = 1;
				// category discount
				$item['cat_disc_inherit'] = trim($_REQUEST['cat_disc_inherit'][$n]);
		        
		        if(!$item['cat_disc_inherit'])	$item['cat_disc_inherit'] = 'inherit';
		        
				$item['category_disc_by_branch_inherit'] = $_REQUEST['category_disc_by_branch_inherit'][$n];
				if($item['cat_disc_inherit']!='set'){
					$item['category_disc_by_branch_inherit'] = array();
				}
				
				// category reward point
				$item['category_point_inherit'] = trim($_REQUEST['category_point_inherit'][$n]);
		        		        
		        $item['category_point_by_branch_inherit'] = $_REQUEST['category_point_by_branch_inherit'][$n];
		        $item['additional_description'] = $_REQUEST['additional_description'][$n];
		        $item['additional_description_print_at_counter'] = $_REQUEST['additional_description_print_at_counter'][$n];
				$item['additional_description_prompt_at_counter'] = $_REQUEST['additional_description_prompt_at_counter'][$n];
		        
		        if(!$item['category_point_inherit'])	$item['category_point_inherit'] = 'inherit';
		        if($item['category_point_inherit']!='set')	$item['category_point_by_branch_inherit'] = array();
				
				if($config['do_enable_hq_selling']&&BRANCH_CODE=='HQ'){
                    $item['hq_selling'] = doubleval($_REQUEST['hq_selling'][$n]);
				}
				
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
					
				// consignment type sku cannot have zero cost
				if ($form['sku_type'] != 'CONSIGN' && $item['cost_price'] == 0){
					$this_err[] = $LANG['SKU_INVALID_COST_PRICE'];
				}
				
				$item['po_reorder_qty_min'] = $form['si_po_reorder_qty_min'][$n];
				$item['po_reorder_qty_max'] = $form['si_po_reorder_qty_max'][$n];
				$item['po_reorder_moq']  = $form['si_po_reorder_moq'][$n];
				$item['po_reorder_notify_user_id'] = $form['si_po_reorder_notify_user_id'][$n];
				if(!$item['po_reorder_qty_min'] && !$item['po_reorder_qty_max'] && $item['po_reorder_moq']){
					$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX'], "");
				}elseif($item['po_reorder_qty_min'] && $item['po_reorder_qty_max'] || $item['po_reorder_moq']){
					if($item['po_reorder_qty_max']<=$item['po_reorder_qty_min']){
						$this_err[] = sprintf($LANG['SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN'], "");
					}
					if($item['po_reorder_qty_max'] < $item['po_reorder_moq']){
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
				
				$item['sn_we'] = $form['sn_we'][$n];
				$item['sn_we_type'] = $form['sn_we_type'][$n];
				$item['internal_description'] = $form['internal_description'][$n];
				$item['marketplace_description'] = $form['marketplace_description'][$n];
				$item['not_allow_disc'] = $form['not_allow_disc'][$n];
				$item['weight_kg'] = mf($form['weight_kg'][$n]);
				$item['model'] = $form['model'][$n];
				$item['width'] = $form['width'][$n];
				$item['height'] = $form['height'][$n];
				$item['length'] = $form['length'][$n];
				
				$item['use_rsp'] = mi($form['use_rsp'][$n]);
				$item['rsp_price'] = mf($form['rsp_price'][$n]);
				$item['rsp_discount'] = trim($form['rsp_discount'][$n]);
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

			if ($item['artno'] != '' && !$config['sku_application_artno_allow_duplicate'])
			{
			    if ($i=is_artmcode_used($form['id'], $item['artno'], $form['vendor_id'], 'artno')) $this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $item['artno'], "by the same vendor in existing SKU. ($i)");
			}
   			if ($item['mcode'] != '')
			{
				// at least 1 photo if mcode used
				// temporary disable
				//if ($SKU_MIN_PHOTO_REQUIRED < 1) $SKU_MIN_PHOTO_REQUIRED = 1;
				    
			    if ($m=is_artmcode_used($form['id'], $item['mcode'], $form['vendor_id'], 'mcode')){
					if (!$config['sku_application_artno_allow_duplicate']) $this_err[] = sprintf($LANG['SKU_MCODE_USED'], $item['mcode'], "in existing SKU. ($m)");
				}

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

			if (!$config['sku_application_allow_no_artno_mcode'] && ($item['artno'] == '' && $item['mcode'] == '' && ($item['item_type'] == 'variety' || !$item['own_article'])))
				$this_err[] = $LANG['SKU_INVALID_ART_MCODE'];

			if ($item['description'] == '')
				$this_err[] = $LANG['SKU_INVALID_DESCRIPTION'];
			
            //remove last approval check
			if ($item['receipt_description'] == '')
			{
				$this_err[] = $LANG['SKU_INVALID_RECEIPT_DESCRIPTION'];
				
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
									if ($tbcol != '' && $c > 0 && is_artmcode_used($form['id'], $tbcol, $form['vendor_id'], 'artno'))
									{
									    if (!$config['sku_application_artno_allow_duplicate']) $this_err[] = sprintf($LANG['SKU_ARTNO_USED'], $tbcol, "(Row $r, Column ".chr($c+64).")");
									}
									if ($tbm[$r][$c] != '' && $c > 0 && is_artmcode_used($form['id'], $tbm[$r][$c], $form['vendor_id'], 'mcode'))
									{
									    if (!$config['sku_application_artno_allow_duplicate']) $this_err[] = sprintf($LANG['SKU_MCODE_USED'], $tbm[$r][$c], "(Row $r, Column ".chr($c+64).")");
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
						    $this_err[] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
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
						    $this_err[] = sprintf($LANG['SKU_MATRIX_INCOMPLETE'], "(Row $r, Column ".chr($c+64).")");
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


			// check images
			$item['photo_count'] = 0;
	        $item['saved_photo'] = array();

			// check previously added images
			$upload_running = $sessioninfo['id'] . "000";
			if ($_REQUEST["photo_$n"])
			{
			    foreach ($_REQUEST["photo_$n"] as $spid)
			    {
				    if (file_exists($spid))
				    {
				    	$item['saved_photo'][] = $spid;
				    	$item['photo_count']++;
				   		$upload_running = intval(substr(strrchr($spid, "/"),1)) + 1;
				    }
				}
			}

			// check new uplaoded images
			$size_width = mi($config['sku_application_photo_size']['width']);
			$size_height = mi($config['sku_application_photo_size']['height']);
			
			
			$photo = $_FILES["photo_$n"];
			for ($i=0; $i<5; $i++)
			{
				if (isset($photo['error'][$i]) && $photo['error'][$i] === 0)
				{
				    $chk_file_ok = true;
				    
				    // check accept type
		            if ($photo['type'][$i] != 'image/jpeg' && $photo['type'][$i] != 'application/pdf'){
		                $this_err[] = sprintf($LANG['SKU_INVALID_PHOTO'], $photo['name'][$i]);
		                $chk_file_ok = false;
					}
					
					// check width and height
					if($chk_file_ok && $photo['type'][$i] == 'image/jpeg' && $config['sku_application_photo_size'] && $size_width>0 && $size_height>0){
					    $tmp_fp = getimagesize($photo['tmp_name'][$i]);

						if($tmp_fp[0]!=$size_width || $tmp_fp[1]!=$size_height){
                            $this_err[] = sprintf($LANG['SKU_INVALID_PHOTO_SIZE'], $photo['name'][$i]);
                            $chk_file_ok = false;
						}
					}
					
					$spid = "tmp/sku/".$upload_running."_".$n.".jpg";
					if($photo['type'][$i] == 'application/pdf'){ // found if it is PDF file, convert the first page become image
						$params = array();
						$params['image_path'] = $upload_running."_".$n.".jpg";
						$params['sku_path'] = "tmp/sku";
						$params['pdf_path'] = $photo['tmp_name'][$i];
						$spid = pdf_handler($params);
						
						if(!file_exists($spid)){
							$this_err[] = sprintf($LANG['SKU_PDF_CONVERT_FAILED'], $photo['name'][$i]);
							$chk_file_ok = false;
						}
					}
					
					// can upload
					if($chk_file_ok){
						if($photo['type'][$i] != 'application/pdf') {
							copy($photo['tmp_name'][$i], $spid);
							if (!$config["sku_no_resize_photo"]) resize_photo($spid, $spid);
						}
						$item['saved_photo'][] = $spid;
					    $item['photo_count']++;
					    $upload_running++;
					}
				}
		 	}
		 	if ($item['photo_count'] < $SKU_MIN_PHOTO_REQUIRED)
		 	{
		 	    $this_err[] = sprintf($LANG['SKU_MIN_PHOTO_REQUIRED'], $SKU_MIN_PHOTO_REQUIRED);
			}

			//check new upload promotion 
			$item['promotion_photo'] = array();
			$upload_uid = $sessioninfo['id'] . "000";
			if($_REQUEST["saved_promotion_photo_$n"]){
				$item['promotion_photo'] = $_REQUEST["saved_promotion_photo_$n"];
			}
			$promotion_photo = $_FILES["promotion_photo_$n"];
			if (isset($promotion_photo['error']) && $promotion_photo['error'] === 0){
				$chk_promo_img = true;
				
				// check accept type
				if ($promotion_photo['type'] != 'image/jpeg' && $promotion_photo['type'] != 'application/pdf'){
					$this_err[] = sprintf($LANG['SKU_INVALID_PHOTO'], $promotion_photo['name']);
					$chk_promo_img = false;
				}
				
				// check width and height
				if($chk_promo_img && $promotion_photo['type'] == 'image/jpeg' && $config['sku_application_photo_size'] && $size_width>0 && $size_height>0){
					$tmp_fp = getimagesize($promotion_photo['tmp_name']);

					if($tmp_fp[0]!=$size_width || $tmp_fp[1]!=$size_height){
						$this_err[] = sprintf($LANG['SKU_INVALID_PHOTO_SIZE'], $promotion_photo['name']);
						$chk_promo_img = false;
					}
				}
				// can upload
				$sku_promotion = "tmp/sku_promotion";
				if (!is_dir($sku_promotion)){
					mkdir($sku_promotion);
					chmod($sku_promotion,0777);
				}
				
				$promotion_id = "tmp/sku_promotion/".$upload_uid."_".$n.".jpg";
				if($chk_promo_img){
					if($promotion_photo['type'] != 'application/pdf') {
						copy($promotion_photo['tmp_name'], $promotion_id);
						if (!$config["sku_no_resize_photo"]) resize_photo($promotion_id, $promotion_id);
					}
					$item['promotion_photo'] = $promotion_id;
				}
			}
			// add item to list
			array_push($items, $item);
			if ($this_err) $err['items'][count($items)-1] = $this_err;
			$n++;
			// infinite loop prevention
			if ($n>100) break;
	    }

        if(!$_REQUEST['parent_child_duplicate_mcode']){
            if($all_mcode!=array_unique($all_mcode)){
                $dmcode=array_unique( array_diff_assoc( $all_mcode, array_unique( $all_mcode ) ) );
                $err['top'][] = sprintf($LANG["SKU_MCODE_DUPLICATE"],implode(", ",$dmcode));//"Duplicate Manufacturer's Code - ".implode(", ",$dmcode);
            }
        }
	}

	if ($form['listing_fee_type'] == 'Package2' && $n != $form['listing_fee_package_count'])
	{
	    $err['top'][] = sprintf($LANG['SKU_PACKAGE_VARIETY_COUNT_LESS'], $form['listing_fee_package_count']);
	}

    if ($n==0)
    {
        $err['top'][] = $LANG['SKU_NO_ITEM'];
	}
	
	return $err;
}

function find_multics_code($lines, $str)
{
	foreach ($lines as $line)
	{
	    $m = explode(",", $line);
	    if (trim($m[0]) == trim($str)) return true;
	}
	return false;
}
//check till here last approval
function ajax_check_is_last_approval(){
	global $con, $sessioninfo;
	
	$category_id = mi($_REQUEST['cat_id']);
	$sku_type = trim($_REQUEST['sku_type']);
	
	if(!$sku_type||!$category_id)   die('NO');
	$last_approval = check_is_last_approval_of_sku_application($category_id, $sku_type, $sessioninfo['id'], $sessioninfo['branch_id']);

	if($last_approval)  print "OK";
	else print "NO";
}

function revise_list(){
	global $con, $sessioninfo, $config, $smarty, $LANG;
	
	if (!privilege('MST_SKU_APPLY')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_APPLY', BRANCH_CODE), "/index.php");
	
	$filter = array();
	if($sessioninfo['level']<9999){
		$filter[] = "sku.apply_by=$sessioninfo[id]";
	}
	
	$filter[] = "sku.status in (0,2)";
	$filter[] = "sku.active=0";
	if(BRANCH_CODE != 'HQ'){
		$filter[] = "sku.apply_branch_id=$sessioninfo[branch_id]";
	}
	
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select sku.*, dept.description as dept_desc, c.description as cat_desc, v.description as vendor_desc, br.description as brand_desc, user.u as user_u, b.code as apply_branch_code
	from sku
	left join category c on c.id=sku.category_id
	left join category dept on dept.id=c.department_id
	left join vendor v on v.id=sku.vendor_id
	left join brand br on br.id=sku.brand_id
	left join user on user.id=sku.apply_by
	left join branch b on b.id=sku.apply_branch_id
	$filter
	order by sku.timestamp";
	//print $sql;
	
	$sku_list = array();
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		$sku_list[] = $r;
	}
	$con->sql_freeresult();
	
	$smarty->assign("PAGE_TITLE", "SKU Application Revise List");
	$smarty->assign('sku_list', $sku_list);
	$smarty->display('masterfile_sku_application.revise_main.tpl');
}

function terminate_package(){
	global $hqcon, $smarty, $sessioninfo;
	
	$form = $_REQUEST;
	
	$hqcon->sql_query("select * from sku where id = ".mi($form['id']));
	$r = $hqcon->sql_fetchrow();

	if ($r['listing_fee_type'] != 'Package') return; // last sku is not package
	
	$package = unserialize($r['listing_fee_remark']);
	
	$package['count'] = mi($form['count']);
	$package['first_sku_id'] = mi($form['id']);
	
	$upd = array();
	$upd['listing_fee_remark'] = serialize($package);
	$upd['timestamp'] = "CURRENT_TIMESTAMP";
	
	$hqcon->sql_query("update sku set ".mysql_update_by_field($upd)." where id = ".mi($form['id']));
	
	log_br($sessioninfo['id'], 'MASTERFILE', $form['id'], "Terminated SKU Application Package Listing Fee: (ID#$form[id])");
	
	header("Location: $_SERVER[PHP_SELF]");
}
?>
