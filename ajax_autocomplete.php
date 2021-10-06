<?php
/*
Revision History
================
18 Apr 2007  - yinsee
- add PO branch column

8/1/2007 11:00:32 AM -gary
- add filter option for payment voucher, make it possible to select deactive vendor.

9/13/2007 11:38:21 AM - yinsee 
- enhance searching for vendor, dept, branch by code 

10/29/2007 2:58:48 PM gary
- add option to show the vendor code in autocomplete.

1/15/2008 4:36:08 PM yinsee
- add block_list parameter to control which sku autocomplete should use the block_list (coz we only want to block in PO!)

2008-9-24 1:00 PM Andy
- add CASE ajax_load_sku_group_list,ajax_add_sku_item_into_list

31/7/2009 5:27:09 PM yinsee
- remove limitation on search_category 

8/7/2009 2:05:41 PM Andy
- check config['sku_autocomplete_hide_variety'] to only show parent

4/20/2010 10:16:11 AM Andy
- add skip department control on sku items search

4/21/2010 5:38:57 PM Andy
- add skip printing control on sku items search
- add control to able to show select multiple sku template

4/30/2010 1:24:58 PM Andy
- Make category search can search by code, need turn on config.

6/16/2010 4:33:37 PM Alex
- add ajax_vendor_checkout for gra checking in PO

7/13/2010 11:26:31 AM Alex
- add message on ajax_vendor_checkout and link to GRA Summary

7/19/2010 11:13:37 AM Alex
- add bearing checking on ajax_search_category

7/27/2010 9:55:26 AM Alex
- change checking sku_item_code=concat(sku_code,'0000') to is_parent under ajax_search_sku

8/11/2010 10:18:06 AM Andy
- Add sku autocomplete to filter fresh market sku.

8/11/2010 11:56:01 AM Justin
- Added a new function to Search multiple SKU from make Redemption menu (ajax_search_redemption).
- Added the SKU search function to include different filtered SKU items as below for the following new function:
  -> filter off those SKU items when found it is existed on the list of redemption item.
  -> filter off those SKU items when found it is over the valid end date or earlier than the valid start date.

8/13/2010 11:44:01 AM Justin
- Taken away all the filters of SKU items that is not enough point to redeem or Overdue valid date.
- Modified the redemption item search engine to display as below:
  -> If found member's points is insufficient compared the list of redemption items, those items will be added extra remarks indicates that the following item is insufficient points to redeem.
  -> If found the item is already overdue its valid date, highlight and replace the title to mention the item is overdue valid date when mouseover.

8/17/2010 3:57:41 PM Andy
- Add new function ajax_search_sku_by_handheld() to search sku by using handheld.

8/23/2010 10:39:23 AM Justin
- Amended the search SKU for redemption when make redemption to base on available branch.
- Amended search SKU for redemption only available when it is confirmed.

8/25/2010 6:53:08 PM Alex
- add filter show inactive items for ajax_search_sku

9/1/2010 5:16:55 PM Andy
- Add search sku to filter parent sku.
- Add sku handheld to check if got filter parent sku and user entered child sku, it will auto change to return parent sku.

9/6/2010 4:52:49 PM Justin
- Fixed the "ajax_vendor_checkout" bugs.

9/29/2010 4:58:34 PM Justin
- Added a Cash Need on the sku list whenever user search sku from redemption module.


10/8/2010 12:45:26 PM Alex
- add departments privilege filter in ajax_search_category

10/8/2010 5:37:42 PM Justin
- Fixed the wrong displat of title for valid date from and to.

10/15/2010 11:37:53 AM Alex
- add skip dept filter in ajax_search_category

11/4/2010 4:05:07 PM Alex
- add show varieties condition at reset_sku

11/8/2010 12:31:28 PM Justin
- Skipped the Dept filter for search redemption.

11/17/2010 6:18:47 PM Justin
- Added the filter of serial no check for ajax sku call.

11/24/2010 2:57:34 PM Justin
- Simplified the redemption item search query to reduce the processing speed.

12/13/2010 12:14:09 PM Alex
- add trade discount type and sku_type filter on ajax_search_sku and ajax_search category

12/20/2010 4:11:17 PM Andy
- Make ajax search category can filter max level and dont show the find subcat icon.

12/27/2010 11:50:31 AM Alex
- check request['branch_id'] array at vendor and brand

1/12/2011 5:24:28 PM Justin
- Show Batch No and Expired Date when search SKU from all types of DO modules.

2/15/2011 4:55:16 PM Justin
- Fixed the wrong ascending bugs while search SKU base in description.

5/25/2011 3:12:33 PM Andy
- Add ajax search grn autocomplete.

5/31/2011 1:03:30 PM Justin
- Rename the "grn_batch_items" into "sku_batch_items".

6/24/2011 3:19:49 PM Andy
- Make all branch default sort by sequence, code.

7/11/2011 4:35:02 PM Andy
- Enable autocomplete to search/show utf8 charset.
- Replace htmlentities() to htmlspecialchars()

7/20/2011 9:42:21 AM Justin
- Added Art No and MCode as attributes for info retrieving purpose.

9/8/2011 4:30:53 PM Andy
- Make search grn document can search GRR and GRN no.
- Add can delete GRN distribution status from notification. (must level 9999)

9/23/2011 5:39:34 PM Justin
- Added doc_allow_decimal as hidden field.

12/14/2011 12:06:43 PM Justin
- Added to skip department filter and prefix session info branch ID when found is login by sales agent.

2/13/2012 3:11:48 PM Alex
- add mi on $sessioninfo[id] in case 'ajax_allowed_login_branches':

3/1/2012 11:34:27 AM Andy
- improve 'ajax_search_sku' sql searching time 

3/20/2012 11:41:32 AM Andy
- Change login_as only show active branch.

3/30/2012 3:59:24 PM Andy
- Add parameter to force check department when search SKU, admin default no need check, but other default need check.

4/3/2012 4:01:34 PM Justin
- Added new config "sku_autocomplete_limit" to use custom limit for sku list.

7/12/2012 4:07 PM Andy
- Add "Go to branch" feature for Vendor Portal.

7/18/2012 10:38 AM Justin
- Enhanced to have ajax call that retrieve batch price change.
- Moved function "ajax_get_bpc" into ajax_notification.php.

7/27/2012 3:32 PM Andy
- Add checking for non-returnable sku and block it to use.
- Add checking to maintenance version 140.

8/10/2012 11:11 AM Andy
- Add to check and block user to add sku from autocomplete if found the sku got purchase agreement.

9/13/2012 10:00:00 AM Fithri
- member update - can search by u,l,fullname

9/20/2012 1:22 PM Andy
- Add checking for "block_is_bom" for ajax search sku.

10/8/2012 1:58:00 PM Fithri
- SKU change price block bom type "package"

10/22/2012 3:33 PM Andy
- Add user level filter on searching sku group. (need config "sku_group_searching_need_filter_user").

1/7/2013 9:32 AM Andy
- Enhance sku description searching query.

2/6/2013 3:34 PM Andy
- Fix sku description searching query by escape the regex string with preg_quote().
- Fix mysql match against in boolean mode will return false if one of the words contain in fulltext stopwords.

4/2/2013 5:56 PM Andy
- Enhance popup login to branch able to show in debtor portal.

4/30/2013 10:42 AM Fithri
- bugfix : update profile autocomplete got error when login at branch

5/23/2013 4:04 PM Justin
- Bug fixed on system does not filter off those categories that parent has been set to inactive.

6/11/2013 9:51 AM Andy
- Fix user search to exclude branch filter when login as HQ.

7/22/2013 3:21 PM Andy
- Enhance search user to pass username.

10/16/2013 4:24 PM Fithri
- if search value is 13 chars (armscode/mcode/linkcode/artno) if not found then use only the first 12 character - this is because barcoder generate 1 extra character

4/15/2014 4:56 PM Justin
- Enhanced to have new filter "po_reorder_by_child" while searching SKU from PO Reorder Qty by Branch module.

4/21/2014 10:01 AM Justin
- Enhanced to have checking on block items in GRN.

12/22/2015 6:00 PM Andy
- Fix color/size autocomplete.

02/01/2016 15:00 Edwin
- Modified ajax_vendor_checkout where clause to include save approval

6/3/2016 12:00 PM Andy
- Enhanced to compatible with php7.

9/8/2016 11:24 AM Andy
- Fixed search user should exclude admin.

9/8/2016 11:46 PM Andy
- Enhanced to filter out arms user.

4/19/2017 2:56 PM Justin
- Enhanced load SKU group item to check against "have_sn" if found got this parameter exists.

6/9/2017 1:36 PM Justin
- Enhanced to choose status as "Un-checkout" when click on GRA summary link.

6/22/2017 2:01 PM Justin
- Enhanced to have new feature that can skip existed SKU items while calling out the multi add menu.

7/31/2017 11:44 AM Justin
- Bug fixed on MCode with 13 digits will causes PHP fatar error.

8/2/2017 10:56 AM Justin
- Bug fixed on sku type filter will cause SQL errors.

8/9/2017 13:10 PM Qiu Ying
- Enhanced to add category level filter in category autocomplete

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

12/20/2017 10:13 AM Andy
- Fixed to show session timeout when users session timeout, including manually logout and auto session timeout.

12/27/2017 5:42 PM Andy
- Enhanced ajax search sku can filter sku with weight_kg.

5/21/2018 4:42 PM Justin
- Enhanced to use 'like' to do matching for SKU description instead of using 'match'.

8/21/2018 12:23 PM Andy
- Added "ajax_search_debtor".

8/30/2018 10:12 AM Andy
- Enhanced to get Vendor Tax Register in vendor autocomplete.

1/10/2019 2:25 PM Justin
- Enhanced to block zero or negative stock balance when adding DO items (need config).

05/07/2019 3:12 PM Liew
- Add by Group not working"

5/23/2019 11:58 AM William
- Enhance "GRR","GRN" word to report_prefix.

8/15/2019 11:22 AM William
- Enhanced "Category" can select level 1 category.

11/22/2019 4:36 PM Justin
- Added new function "ajax_search_sa".
*/
/*
	Ajax autocomplete helper script
	a = ajax_search_vendor / brand / category
*/
	include("include/common.php");
    if (!$sessioninfo && !$_SESSION) die("<ul><li>Session Timeout. Please Login</li></ul>");
	elseif($_SESSION['sa_ticket']){
		$_REQUEST['skip_dept_filter'] = 1;
		$sessioninfo['branch_id'] = 1;
	}elseif(!$login){
		die("<ul><li>Session Timeout. Please Login</li></ul>");
	}
	
	if($err_msg = $maintenance->check(298, false, false))	die("<ul><li onclick=\"alert('$err_msg')\">$err_msg</li></ul>");

	$mysql_stopwords_list = get_mysql_stopwords_list();

	function color_filter($col){
	    $input_color = $_REQUEST['color'];
		return stripos($col,$input_color)!==false;
	}
	
	function size_filter($siz){
	    $input_size = $_REQUEST['size'];
		return stripos($siz,$input_size)!==false;
	}


	switch($_REQUEST['a'])
	{
	    case 'get_category_level':
			$min_lvl = $_REQUEST["min_level"];
			
			if($_REQUEST['allow_select_line']){
				$start_from = 1;
				$min_lvl = 1;
			}else{
				$start_from = $min_lvl+1;
			}
			
			$con->sql_query("select max(level) as max_lvl from category where level > " . mi($min_lvl));
			$max_lvl = $con->sql_fetchfield("max_lvl");
			
			if(isset($_REQUEST["category_level"]) && $_REQUEST["category_level"] == "all_$min_lvl") {
				$all_selected = "selected";
			}
			print "<option value='all_$min_lvl' $all_selected>Any Level (Min Level ". $start_from .")</option>";
			
			for($i = $start_from; $i <= $max_lvl; $i++){
				if(isset($_REQUEST["category_level"]) && $_REQUEST["category_level"] == $i) {
					$not_all_selected = "selected";
				}
				print "<option value='$i' $not_all_selected>Level $i</option>";
				if(isset($not_all_selected)) unset($not_all_selected);
			}
			exit;
		case 'ajax_search_vendor':
	        $v = strval($_REQUEST['vendor']);
	        $vint = intval($v);
			$out = '';
			$branch_id=$sessioninfo['branch_id'];
			//Add block by Alex
			if(isset($_REQUEST['block'])){
                if($_REQUEST['block']=='po' || $_REQUEST['block']=='grr')	$col_tbl=$_REQUEST['block']."_block_list,";
			}
			else $col_tbl='';



			if(isset($_REQUEST['bearing'])){
			    if (is_array($_REQUEST['branch_id']))   $br_ids= join(",",$_REQUEST['branch_id']);
			    else    $br_ids= $_REQUEST['branch_id'];

				$con->sql_query("select distinct vendor_id from vendor_commission where branch_id in ($br_ids) and department_id=$_REQUEST[dept_id] and rate>0");
				if ($con->sql_numrows()>0){
					while ($r=$con->sql_fetchrow()){
						$arr_vid[]=$r['vendor_id'];
					}
					$vid="and id in (".join(",",$arr_vid).")";
				}
				else
				    $vid="and id=0";
			}

	        /*original code from yinsee
			if ($sessioninfo['vendors']) $vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
	        $con->sql_query("select id, description from vendor where active $vd and (description like " . ms($v.'%') . " or description like " . ms('% '.$v.'%') . ") order by description");*/
	        if ($sessioninfo['vendors']) $vd = "id in (".join(",",array_keys($sessioninfo['vendors'])).") and ";
			if ($_REQUEST['type']!='All') $vd = "active and $vd"; 
			
			$con->sql_query("select $col_tbl id, code, description, tax_register, tax_percent
				from vendor where $vd (id = $vint or code = ".ms($v)." or description like " . ms(replace_special_char($v)."%") . " or description like " . ms("%". replace_special_char($v) ."%") . ")$vid order by description");
			
			print "<ul>";
			if ($con->sql_numrows() > 0)
			{
			    if ($con->sql_numrows() > 15)
			    {
					print "<li><span class=informal>Found ".$con->sql_numrows()." matches, showing first 15</span></li>";
				}
				$c = 0;
				while ($r = $con->sql_fetchrow())
				{

					$block=unserialize($r[0]);
					if (isset($block[$branch_id])){
					    $out .= "<li title=\"0\" class=strike onclick=\"alert('$LANG[MSTVENDOR_IS_BLOCK]')\">". htmlspecialchars($r['description']) ."</li>";
					}
					elseif($config['ajax_autocomplete_hide_vendor_code']){
						$out .= "<li title=\"$r[id]\" tax_register='$r[tax_register]' tax_percent='$r[tax_percent]'>". htmlspecialchars($r['description']) ."</li>";					
					}
					else{
						$out .= "<li title=\"$r[id]\" tax_register='$r[tax_register]' tax_percent='$r[tax_percent]'>".($r['code']?"$r[code] -":""). htmlspecialchars($r['description']) ."</li>";					
					}
					$c++;
					if ($c >= 15) break;
				}
	        }
	        else
	        {
	           print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
			}
			print $out;
	        print "</ul>";
			exit;

	    case 'ajax_search_brand':
	        $v = strval($_REQUEST['brand']);
	        $vint = intval($v);
	        $out = '';
	        if ($sessioninfo['brands']) $br = "id in (".join(",",array_keys($sessioninfo['brands'])).") and";
	        if($_REQUEST['type']!='All') $br = "active and $br";


			if(isset($_REQUEST['bearing'])){
  			    if (is_array($_REQUEST['branch_id']))   $br_ids= join(",",$_REQUEST['branch_id']);
			    else    $br_ids= $_REQUEST['branch_id'];

				$con->sql_query("select distinct brand_id from brand_commission where branch_id in ($br_ids) and department_id=$_REQUEST[dept_id] and rate>0");
				if ($con->sql_numrows()>0){
					while ($r=$con->sql_fetchrow()){
						$arr_bid[]=$r['brand_id'];
					}

				}
				if (!$arr_bid) 	$bid="and id=0";
					else	$bid="and id in (".join(',',$arr_bid).")";

			}

	        $con->sql_query("select id, description from brand where $br (id = $vint or code = ".ms($v)." or description like " . ms(replace_special_char($v).'%') . " or description like " . ms('%'.replace_special_char($v).'%') . ") $bid order by description");
	        
			print "<ul>";
			if ($con->sql_numrows() > 0)
			{
			    if ($con->sql_numrows() > 15)
			    {
					print "<li><span class=informal>Found ".$con->sql_numrows()." matches, showing first 15</span></li>";
				}
				$c = 0;
				while ($r = $con->sql_fetchrow())
				{
					$out .= "<li title=\"$r[id]\">". htmlspecialchars($r['description']) ."</li>";
					$c++;
					if ($c >= 15) break;
				}
	        }
	        else
	        {
	            print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
			}
			if (!isset($_REQUEST['no_unbranded'])) print "<li title=\"0\">UN-BRANDED</li>";
			print $out;
	        print "</ul>";
			exit;

	    case 'ajax_search_dept':
	        $v = strval($_REQUEST['department']);
	        $vint = intval($v);
	        $out = '';
	        $depts = join(",", array_keys($sessioninfo['departments']));
	        $con->sql_query("select id, description from category where active and id in ($depts) and (id = $vint or code = ".ms($v)." or description like " . ms(replace_special_char($v).'%') . " or description like " . ms('%'.replace_special_char($v).'%') . ") order by description");
			print "<ul>";
			if ($con->sql_numrows() > 0)
			{
			    /*if ($con->sql_numrows() > 15)
			    {
					print "<li><span class=informal>Found ".$con->sql_numrows()." matches, showing first 15</span></li>";
				}
				$c = 0;*/
				while ($r = $con->sql_fetchrow())
				{
					$out .= "<li title=\"$r[id]\">". htmlspecialchars($r['description']) ."</li>";
					/*$c++;
					if ($c >= 15) break;*/
				}
	        }
	        else
	        {
	           print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
			}
			print $out;
	        print "</ul>";
			exit;

		case 'ajax_search_redemption':
			$cid = intval($_REQUEST['dept_id']);
	        $vid = intval($_REQUEST['vendor_id']);
	        $brand_id= intval($_REQUEST['brand_id']);
	        /* due to this search engine does not need to look for dept filter
	        $skip_dept_filter = mi($_REQUEST['skip_dept_filter']);*/
	        $skip_dept_filter = 1;
	        $hide_print = mi($_REQUEST['hide_print']);
	        $show_multiple = mi($_REQUEST['show_multiple']);
	        $search_type = intval($_REQUEST['type']);
	        $nric = $_REQUEST['nric'];
	        $item_list = $_REQUEST['item_list'];

	        //Check if found NRIC for membership redemption item setup
	        if($nric){
				// get latest points
				$sql = "select sum(points) as latest_point from membership_points p left join branch b on b.id = p.branch_id where p.nric=".ms($nric)." order by p.date desc";
				$con->sql_query($sql) or die(mysql_error());
				$max_points = $con->sql_fetchfield(0);

				if($item_list) $mr_where = " and mrs.id not in (".$item_list.")";
				/* Due to still want to show all the overdue valid date and insufficient points
				// load available items
				$sql = "select mrs.id
						from membership_redemption_sku mrs
						left join sku_items si on si.id=mrs.sku_item_id
						left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
						where mrs.active=1 and mrs.point<=".mi($max_points)." $mr_filter and
						case
							when mrs.valid_date_from is not null and mrs.valid_date_from != '0000-00-00' and mrs.valid_date_to is not null and mrs.valid_date_to != '0000-00-00'
							then mrs.valid_date_from <= ".ms($curr_date)." and mrs.valid_date_to >= ".ms($curr_date)."
							when mrs.valid_date_from is not null and mrs.valid_date_from != '0000-00-00' and mrs.valid_date_to is null or mrs.valid_date_to = '0000-00-00'
							then mrs.valid_date_from <= ".ms($curr_date)."
							when mrs.valid_date_from is null or mrs.valid_date_from = '0000-00-00' and mrs.valid_date_to is not null and mrs.valid_date_to != '0000-00-00'
							then mrs.valid_date_to >= ".ms($curr_date)."
							else 1=1
						end
						group by mrs.id
						order by mrs.id";

				$con->sql_query($sql) or die(mysql_error());
				$total_rows = $con->sql_numrows();
				
				while($r = $con->sql_fetchrow()){
					$sku_filter[] = $r['id'];
				}
				if($sku_filter) $mr_where = "and mrs.id in (".join(',', $sku_filter).")";
				else $mr_where = "and mrs.id is null";*/
			}
	        
	        if (isset($_REQUEST['value'])) $v = trim($_REQUEST['value']);
	        else $v = trim($_REQUEST['sku']);

			$dept = "";
			if ($cid)
				$dept = "(category.department_id = $cid or sku.category_id = $cid) and";
			else
			{
			    // check if pasw correct
			    if (isset($_REQUEST['pw']))
			    {
				    $con->sql_query("select id from user where id = $sessioninfo[id] and p = " . ms(md5($_REQUEST['pw'])));
				    if ($con->sql_numrows()<=0)
				    {
						
						exit;
				    }
			    }
			    else
			    {
			        if(!$skip_dept_filter){
                        $dept = " (category.department_id in (".join(",",array_keys($sessioninfo['departments'])).") or sku.category_id in (".join(",",array_keys($sessioninfo['departments'])).")) and";
					}
			        
				}
			}

			// search type
			switch ($search_type)
	        {
	            case 1:     // search mcode and link_code
					$sql_where = "$dept (sku_items.mcode like " . ms(replace_special_char($v)."%"). " or sku_items.link_code like " . ms(replace_special_char($v)."%") . ")";
	                break;

	            case 2:	// search artno
					$sql_where = "$dept sku_items.artno like " . ms(replace_special_char($v)."%");
	                break;

	            case 3:     // search arms code
					$sql_where = "$dept (sku_id = ".mi($v). " or sku_item_code like " . ms(replace_special_char($v)."%") .")";
	                break;

				default:    // search description
					$ll = preg_split("/\s+/", $v);

					$desc_matching = array();
					foreach ($ll as $l) {
						if ($l) $desc_matching[] = "sku_items.description like " . ms('%'.replace_special_char($l).'%');
					}
					$desc_match = join(" and ", $desc_matching);
					
					if ($config['sku_autocomplete_hide_variety'])
						//$sql_where = "sku_item_code like " . ms('%0000') . " and $dept $desc_match";
						$sql_where = "sku_items.is_parent=1 and $dept $desc_match";
					else
						$sql_where = "$dept $desc_match";
					break;
			}

			//filter sku type
			if (isset($_REQUEST['sku_type']))
				$sql_where .= " and sku.sku_type = ".ms($_REQUEST['sku_type']);

			if($config['sku_autocomplete_limit']) $LIMIT = $config['sku_autocomplete_limit'];
			else $LIMIT = 50;

			// call with limit
			$result1 = $con->sql_query("select mrs.*, mrs.id, sku_items.sku_item_code, sku_items.description,
			 							sku_items.sku_id, sku.varieties,sku_items.block_list, sku.sku_type, 
										sku_items.is_parent,
										if(mrs.valid_date_from = '0000-00-00', '', mrs.valid_date_from) as valid_date_from,
										if(mrs.valid_date_to = '0000-00-00', '', mrs.valid_date_to) as valid_date_to
										from membership_redemption_sku mrs
										left join sku_items on sku_items.id = mrs.sku_item_id
										left join sku on sku_items.sku_id = sku.id
										left join category on sku.category_id = category.id
										where mrs.active=1 and mrs.confirm=1 and sku_items.active=1 and $sql_where $mr_where
										order by sku_items.description limit ".($LIMIT+1));

			$out = '';
			$curr_date = date('Y-m-d');
			$items_list = array();
			if(!$hide_print) print "<ul >";
			if ($con->sql_numrows($result1) > 0){
			
			    if ($con->sql_numrows($result1) > $LIMIT)
			    {
					if(!$hide_print)	print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
				}

				// generate list.
				while ($r = $con->sql_fetchrow($result1))
				{
					if($r['branch_id']!=$sessioninfo['branch_id']){ // check available branches
						$r['available_branches'] = unserialize($r['available_branches']);
						if(!$r['available_branches'][$sessioninfo['branch_id']]) continue; // not available for current branch
					}
					$highlight = '';
					if($r['valid_date_from']){
						// is invalid date of sku item
						if($r['valid_date_from'] > $curr_date){
							$highlight = "style='background-color:#FFF8C6;'";
							$title = "title=\"Valid Date haven't Reached\"";
						}
					}
					if($r['valid_date_to']){
						// is invalid date of sku item
						if($r['valid_date_to'] < $curr_date){
							$highlight = "style='background-color:#FFF8C6;'";
							$title = "title=\"Valid Date Overdue\"";
						}
					}
					
					if(!$highlight) $title = "title=\"$r[id],$r[sku_item_code]\"";
					
				    $out .= "<li $highlight $title>". ($_REQUEST['multiple']?"<input id=cb_ajax_sku_$r[id] value=\"$r[id],$r[sku_item_code]$pp\" title=\"".htmlspecialchars($r['description'])."\" ".($max_points < $r['point'] || $highlight != ''?"disabled":"")." type=checkbox> ":"")."<label class=clickable for=cb_ajax_sku_$r[id]>".htmlspecialchars($r['description'])."</label>";
				    if($show_multiple)   $items_list[] = $r;

					//Selling: $%.2f  Cost: $%.3f  Margin: %.2f%%
					//$r[selling_price], $r[cost_price], ($r[selling_price]-$r[cost_price])/$r[cost_price]*100,
					if(!$r['valid_date_from']) $r['valid_date_from'] = "-";
					if(!$r['valid_date_to']) $r['valid_date_to'] = "-";
					$out .= sprintf("<span class=informal> (Point: %s, Cash: %s, Rec. Amt: %s, Date Start: %s, Date End: %s)",  $r['point'], $r['cash'], number_format($r['receipt_amount'], 2), $r['valid_date_from'], $r['valid_date_to']);

					$out .= "</span>";
					$out .= "<input type=hidden id='inp_is_parent,$r[id]' value='$r[is_parent]'>";
					$out .= "</li>";
					if($_REQUEST['multiple'] && $max_points < $r['point']){
						$out .= sprintf("<li $highlight><span class=informal>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Insufficient Points)</span></li>");
					}
				}
	        }
	        else
	        {
	           if(!$hide_print)	print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
			}
			
			if($con->sql_numrows($result1) > 0 && !$out) print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
			
			if(!$hide_print){
                print $out;
	        	print "</ul>";
			}

			if($show_multiple){
			    $smarty->assign('search_str', $v);
			    $smarty->assign('search_type', $search_type);
				$smarty->assign('items_list', $items_list);
				//print_r($items_list);
				$smarty->display('ajax_autocomplete.sku_multiple_add.tpl');
			}
			exit;
			

	    case 'ajax_search_sku':
	        $cid = intval($_REQUEST['dept_id']);
	        $vid = intval($_REQUEST['vendor_id']);
	        $brand_id= intval($_REQUEST['brand_id']);
	        $skip_dept_filter = mi($_REQUEST['skip_dept_filter']);
	        $must_check_dept = mi($_REQUEST['must_check_dept']);
	        $hide_print = mi($_REQUEST['hide_print']);
	        $show_multiple = mi($_REQUEST['show_multiple']);
	        $search_type = intval($_REQUEST['type']);
	        $is_parent_only = mi($_REQUEST['is_parent_only']);
	        $show_varieties = mi($_REQUEST['show_varieties']);
			$block_non_returnable = mi($_REQUEST['block_non_returnable']);
			$block_is_bom = mi($_REQUEST['block_is_bom']);
			$block_bom_package = mi($_REQUEST['block_bom_package']);
			$check_po_reorder_by_child = mi($_REQUEST['check_po_reorder_by_child']);
			$doc_block_type = $_REQUEST['doc_block_type'];
			$skip_sku_item_id = $_REQUEST['skip_sku_item_id'];
			$need_weight_kg = mi($_REQUEST['need_weight_kg']);
			
			$filters = array();
			
			if($config['enable_po_agreement'])	$block_got_purchase_agreement = mi($_REQUEST['block_got_purchase_agreement']);
			
	        if(isset($_REQUEST['fresh_market_filter']))	$fresh_market_filter = trim($_REQUEST['fresh_market_filter']);
			
	        //Check on consignment bearing
	        $bearing = $_REQUEST['bearing'];
	        if ($bearing){
				if ($vid)   $filters[] = "sku.trade_discount_type=2 and sku.vendor_id = $vid ";
				elseif ($brand_id) $filters[] = "sku.trade_discount_type=1 and sku.brand_id = $brand_id ";
			}
	        
	        if (isset($_REQUEST['value']))
	        	$v = trim($_REQUEST['value']);
	        else
	        	$v = trim($_REQUEST['sku']);

			//
			//and sku.vendor_id = $vid
			if ($cid){
				$con->sql_query("select level from category where id=$cid");
				$cat_level=$con->sql_fetchfield(0);				 
				
				//$dept = "(category.department_id = $cid or sku.category_id = $cid) and";
				$filters[] = "cc.p$cat_level = $cid";
				
			}else
			{
			    // check if pasw correct
			    if (isset($_REQUEST['pw']))
			    {
				    $con->sql_query("select id from user where id = $sessioninfo[id] and p = " . ms(md5($_REQUEST['pw'])));
				    if ($con->sql_numrows()<=0)
				    {
						print "<ul><li  title=\"0\"><span class=informal>$LANG[SKU_SEARCH_ALL_WRONG_PASSWORD]</span></li></ul>";
						exit;
				    }
			    }
			    else
			    {
			        if((!$skip_dept_filter && $sessioninfo['level']<9999) || $must_check_dept){
                        $filters[] = " (category.department_id in (".join(",",array_keys($sessioninfo['departments'])).") or sku.category_id in (".join(",",array_keys($sessioninfo['departments']))."))";
					}
			        
				  }
			}

			// search type
			switch ($search_type)
	        {
	            case 1:     // search mcode and link_code
					$v1 = (strlen($v) == 13) ? substr($v,0,12) : $v;//only fixed the first 12 chars when searching
					$filters[] = "(sku_items.mcode like " . ms(replace_special_char($v1)."%"). " or sku_items.link_code like " . ms(replace_special_char($v1)."%") . ")";
	                break;

	            case 2:	// search artno
					$v1 = (strlen($v) == 13) ? substr($v,0,12) : $v;//only fixed the first 12 chars when searching
					$filters[] = "sku_items.artno like " . ms(replace_special_char($v1)."%");
	                break;

	            case 3:     // search arms code
					$v1 = (strlen($v) == 13) ? substr($v,0,12) : $v;//only fixed the first 12 chars when searching
					$filters[] = "(sku_id = ".mi($v1). " or sku_item_code like " . ms(replace_special_char($v1)."%") .")";
	                break;

				default:    // search description
					$ll = preg_split("/\s+/", $v);

					$desc_matching = array();
					foreach ($ll as $l) {
						if ($l) $desc_matching[] = "sku_items.description like " . ms('%'.replace_special_char($l).'%');
					}
					$desc_match = join(" and ", $desc_matching);

					if ($config['sku_autocomplete_hide_variety'])
						//$sql_where = "sku_item_code like " . ms('%0000') . " and $dept $desc_match";
						$filters[] =  "sku_items.is_parent=1 and ".$desc_match;
					else
						$filters[] = $desc_match;
					break;
			}

			//sku type: CONSIGN | OUTRIGHT
			if ($_REQUEST['sku_type'])
				$filters[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);

			if($fresh_market_filter){
				$filters[] = "(sku.is_fresh_market=".ms($fresh_market_filter)." or (sku.is_fresh_market='inherit' and cc.is_fresh_market=".ms($fresh_market_filter)."))";
			}
	
			if($_REQUEST['check_sn']){
				$filters[] = "sku.have_sn != 0";
			}
			
			if($is_parent_only) $filters[] = "sku_items.is_parent=1";
			
			if (!$_REQUEST['show_inactive']) $filters[] = "sku_items.active=1";

			if($_REQUEST['from_do']){
				$select .= ", sbi.batch_no, sbi.expired_date as batch_expired_date";
				$left_join .= "left join sku_batch_items sbi on sku_items.id = sbi.sku_item_id and sbi.qty >0 and sbi.branch_id = $sessioninfo[branch_id]";
				if($config['do_block_zero_stock_bal_items']){
					$select .= ", sic.qty as sb_qty";
					$left_join .= " left join sku_items_cost sic on sic.sku_item_id = sku_items.id and sic.branch_id = ".mi($sessioninfo['branch_id']);
				}
			}
			
			if($check_po_reorder_by_child){
				$filters[] = " sku.po_reorder_by_child = 1";
			}
			
			if($skip_sku_item_id){
				if(is_array($skip_sku_item_id)) $filters[] = "sku_items.id not in (".join(",", $skip_sku_item_id).")";
				else $filters[] = "sku_items.id != ".mi($skip_sku_item_id);
			}

			if($need_weight_kg){
				$filters[] = "sku_items.weight_kg>0";
			}
			if($config['sku_autocomplete_limit']) $LIMIT = $config['sku_autocomplete_limit'];
			else $LIMIT = 50;
			
			$tmp_bid = $sessioninfo['branch_id'];
			
            if($search_type==1 && strlen($v) == 13){
                $sql_where2="(sku_items.mcode like " . ms(replace_special_char($v)). " or sku_items.link_code like " . ms(replace_special_char($v)) . ") and ".join(" and ", $filters);

                $sql = "select count(*)
                from sku_items
                left join sku_items_price on sku_items.id = sku_items_price.sku_item_id and sku_items_price.branch_id = $tmp_bid
                left join sku on sku_items.sku_id = sku.id
                $left_join
                left join category on sku.category_id = category.id
                left join category_cache cc on cc.category_id=sku.category_id
                where $sql_where2";

                $con->sql_query($sql);
                $t = $con->sql_fetchrow();
                $con->sql_freeresult();
                $total_mcode_item = $t[0];

                if($total_mcode_item>0){
                    $filters[] = "(sku_items.mcode like " . ms(replace_special_char($v)). " or sku_items.link_code like " . ms(replace_special_char($v)) . ")";
                }
            }

			// call with limit
			$sql = "select sku_items.id, sku_items.sku_item_code, sku_items.description, sku_items.artno, sku_items.mcode, sku_items.sku_id, 
										if(sku_items_price.price>0,sku_items_price.price,sku_items.selling_price) as selling_price, 
										if(sku_items_price.cost>0,sku_items_price.cost,sku_items.cost_price) as cost_price, 
										sku.varieties,sku_items.block_list, sku.sku_type, sku_items.is_parent, sku_items.doc_allow_decimal,
										if(sku_items.non_returnable=-1,sku.group_non_returnable,sku_items.non_returnable) as non_returnable, sku.is_bom, sku_items.bom_type, sku_items.doc_block_list
										$select
										from sku_items
										left join sku_items_price on sku_items.id = sku_items_price.sku_item_id and sku_items_price.branch_id = $tmp_bid
										left join sku on sku_items.sku_id = sku.id
										$left_join
										left join category on sku.category_id = category.id
										left join category_cache cc on cc.category_id=sku.category_id
										where ".join(" and ", $filters)."
										order by sku_items.description limit ".($LIMIT+1);
			//if($sessioninfo['u']=='wsatp')	
			//print $sql;
			
			$result1 = $con->sql_query($sql);
						
			$out = '';
			$items_list = array();
			if(!$hide_print)	print "<ul class=\"list-group list-group-flush\">";
			if ($con->sql_numrows($result1) > 0)
			{

			    if ($con->sql_numrows($result1) > $LIMIT)
			    {
					if(!$hide_print)	print "<li class=\"list-group-item list-group-item-action\"><span  class=\"informal\">Showing first $LIMIT items...</span></li>";
				}

				// generate list.
				while ($r = $con->sql_fetchassoc($result1))
				{
					$not_allow_due_to_purchase_agreement = 0;
					if($block_got_purchase_agreement){	// do not allow user to add item which got purchase agreement
						$pa_sql = "select pai.id
						from purchase_agreement_items pai
						join purchase_agreement pa on pa.branch_id=pai.branch_id and pa.id=pai.purchase_agreement_id
						where pai.sku_item_id=".mi($r['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1";
						$con->sql_query($pa_sql);
						$pai = $con->sql_fetchassoc();
						$con->sql_freeresult();
						if($pai)	$not_allow_due_to_purchase_agreement = 1;
						else{
							// check foc item as well
							$pa_sql = "select pafi.id
							from purchase_agreement_foc_items pafi
							join purchase_agreement pa on pa.branch_id=pafi.branch_id and pa.id=pafi.purchase_agreement_id
							where pafi.sku_item_id=".mi($r['id'])." and pa.active=1 and pa.status=1 and pa.approved=1 limit 1";
							$con->sql_query($pa_sql);
							$pai = $con->sql_fetchassoc();
							$con->sql_freeresult();
							if($pai)	$not_allow_due_to_purchase_agreement = 1;
						}
					}
			
					$block_return = 0;
					if($block_non_returnable){
						if($r['non_returnable']==1)	$block_return = 1;
					}
					
					if ($_REQUEST['block_list'])
					{
						$r['block_list']=unserialize($r['block_list']);
						$block = isset($r['block_list'][$sessioninfo['branch_id']]);
					}
					else
						$block = 0;

					if ($doc_block_type)
					{
						$r['doc_block_list']=unserialize($r['doc_block_list']);
						$doc_block = isset($r['doc_block_list'][$doc_block_type][$sessioninfo['branch_id']]);
					}
					else
						$doc_block = 0;
					
				    $pp = '';
				    if($_REQUEST['get_price']){
						$pp .= ",$r[cost_price],$r[selling_price]";
					}
					
					$err_msg = '';
					//PO – Block consignment SKU to issued PO (request by sllee)
					if($_REQUEST['from_po'] && $r['sku_type']=='CONSIGN' && $config['po_block_consignment_sku']){
					    //$out .= "<li title=\"0\" class=strike onclick=\"alert('$LANG[PO_CONSIGN_ITEM_IS_BLOCKED]')\">". htmlspecialchars($r['description']);	
					    $err_msg = $LANG['PO_CONSIGN_ITEM_IS_BLOCKED'];
					}
					elseif($block_is_bom && $r['is_bom'] && $config['sku_bom_allow_add_normal_bom_sku'] && $r['bom_type'] != 'normal'){
						$err_msg = $LANG['BOM_SKU_NOT_ALLOWED_NORMAL_ONLY'];
					}
					elseif($block_is_bom && $r['is_bom'] && !$config['sku_bom_allow_add_normal_bom_sku']){
						$err_msg = $LANG['BOM_SKU_NOT_ALLOWED'];
					}
					elseif($block_bom_package && $r['is_bom'] && $r['bom_type'] == 'package'){
						$err_msg = $LANG['SKU_BLOCKED_BOM_PACKAGE'];
					}
					elseif($not_allow_due_to_purchase_agreement){
						$err_msg = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
					}
					elseif($block){
						$err_msg = $LANG['PO_ITEM_IS_BLOCKED'];
					}
					elseif($doc_block){
						$err_msg = sprintf($LANG['DOC_ITEM_IS_BLOCKED'], strtoupper($doc_block_type));
					}
					elseif($block_return){
						$err_msg = $LANG['SKU_IS_NON_RETURNABLE'];
					}
					elseif($_REQUEST['from_do'] && $config['do_block_zero_stock_bal_items'] && $r['sb_qty'] <= 0){
						$err_msg = sprintf($LANG['DO_ZERO_STOCK_BAL_ITEM'], $r['sku_item_code']);
					}else{	// no error
					    if ($block_bom_package) $out .= "<li class=\"list-group-item list-group-item-action\" title=\"$r[id],$r[sku_item_code],".htmlspecialchars($r['description'])."\" onclick=\"set_search_value(this)\">".htmlspecialchars($r['description']);
						else $out .= "<li class=\"list-group-item list-group-item-action\" title=\"$r[id],$r[sku_item_code]$pp\">". ($_REQUEST['multiple']?"<input id=cb_ajax_sku_$r[id] value=\"$r[id],$r[sku_item_code]$pp\" title=\"".htmlspecialchars($r['description'])."\" artno=\"$r[artno]\" mcode=\"$r[mcode]\" type=checkbox> ":"")."<label class=clickable for=cb_ajax_sku_$r[id]>".htmlspecialchars ($r['description'])."</label>";
					    if($show_multiple)   $items_list[] = $r;
					}	
					
					if($err_msg){
						//$err_msg = $LANG['PO_ITEM_IS_BLOCKED'];
						//if($block_return)	$err_msg = $LANG['SKU_IS_NON_RETURNABLE'];
						//if($not_allow_due_to_purchase_agreement)	$err_msg = $LANG['PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT'];
						//if ($block_bom_package) $out .= "<li title=\"bobobo\" class=strike onclick=\"set_search_value(this.title)\">". htmlspecialchars($r['description']);
					    $out .= "<li title=\"0\" class=\"list-group-item list-group-item-action strike\" onclick=\"alert('$err_msg')\">". htmlspecialchars($r['description']);
					}

					//Selling: $%.2f  Cost: $%.3f  Margin: %.2f%%
					//$r[selling_price], $r[cost_price], ($r[selling_price]-$r[cost_price])/$r[cost_price]*100,
					if ($r['artno'] == '') $r['artno'] = "-";
					if ($r['mcode'] == '') $r['mcode'] = "-";
			
					if ($r['batch_no']){
						$out .= sprintf("<span class=informal> (Art No:%s  MCode:%s  batch No:%s,  Expire Date:%s)",  $r['artno'], $r['mcode'], $r['batch_no'], $r['batch_expired_date']);
					}else{
						$out .= sprintf("<span class=informal> (Art No:%s  MCode:%s)",  $r['artno'], $r['mcode']);
					}
					
					if ($show_varieties){
						if ($config['sku_autocomplete_hide_variety'] && $r['varieties']>1)
							$out .= "[<a href=\"javascript:void(sku_show_varieties($r[sku_id]))\">variaties</a>]";
					}

					$out .= "</span>";
					$out .= "<input type=hidden id='inp_is_parent,$r[id]' value='$r[is_parent]'>";
					$out .= "<input type=hidden id='inp_dad,$r[id]' name='inp_dad[$r[id]]' value='$r[doc_allow_decimal]'>";
					$out .= "</li>";
				}
				$con->sql_freeresult($result1);
	        }
	        else
	        {
	           if(!$hide_print)	print "<li class=\"list-group-item list-group-item-action\" title=\"0\"><span class=informal>No Matches for $v</span></li>";
			}

			if(!$hide_print){
                print $out;
	        	print "</ul>";
			}
			if($show_multiple){
			    $smarty->assign('search_str', $v);
			    $smarty->assign('search_type', $search_type);
				$smarty->assign('items_list', $items_list);
				//print_r($items_list);
				$smarty->display('ajax_autocomplete.sku_multiple_add.tpl');
			}
			exit;

		case 'ajax_search_category':
		    $no_findcat_expand = mi($_REQUEST['no_findcat_expand']);
			print "<ul class=\"list-group list-group-flush\">";

			//check consignment bearing
			if (isset($_REQUEST['bearing'])){
				$filter[] = "c.department_id=".intval($_REQUEST['dept_id']);

			    if (isset($_REQUEST['vendor_id'])) $filter[] = "sku.trade_discount_type=2 and sku.vendor_id=".intval($_REQUEST['vendor_id']);
			    elseif (isset($_REQUEST['brand_id'])) $filter[] = "sku.trade_discount_type=1 and sku.brand_id=".intval($_REQUEST['brand_id']);
			}

			//sku type: CONSIGN | OUTRIGHT
			if ($_REQUEST['sku_type'])
				$filter[] = "sku.sku_type = ".ms($_REQUEST['sku_type']);

			
			//skip filter by department
	        $skip_dept_filter = mi($_REQUEST['skip_dept_filter']);
			if (!$skip_dept_filter)
				$filter[] = "c.department_id in (".$sessioninfo['department_ids'].")";
			
			$v = strval($_REQUEST['category']);
			$vint = intval($v);
			
		    // if minimum search level is given, use.
		    if (isset($_REQUEST['min_level']))
		    {
				$min_level = intval($_REQUEST['min_level']);
				if(isset($_REQUEST['category_level'])){
					if($_REQUEST['category_level'] == "all_$min_level"){
						if(!$_REQUEST['allow_select_line']){
							$filter[] = "c.level > $min_level";
						}else{
							$filter[] = "c.level >= $min_level";
						}
					}else{
						$filter[] = "c.level = " . intval($_REQUEST['category_level']);
					}
				}else{
					$filter[] = "c.level > $min_level";
				}
		    }
			else
		    {
		        // selection is at least > department
				$filter[] = "c.level > 1";
			}
			
			// max level filter
			if(isset($_REQUEST['max_level'])){
                $filter[] = "c.level <= ".mi($_REQUEST['max_level']);
			}

            $where_filter = join(' and ', $filter);

		    if (isset($_REQUEST['child']))
		    {
		        $rid = intval($_REQUEST['child']);
		        $a = $con->sql_query("select c.id, c.code, c.description, c.tree_str, concat(c.tree_str,'(',c.id,')') as ts2
									from category c where c.active=1 and c.root_id = $rid order by ts2");
			}
			else
			{
			    $filter_or = array();
			    $filter_or[] = "c.id = $vint or c.description like " . ms(replace_special_char($v).'%') . " or c.description like " . ms('%'.replace_special_char($v).'%');
			    if($config['category_search_include_code']) $filter_or[] = "(c.code<>'' and c.code like ".ms(replace_special_char($v).'%').")";
			    $filter_or = "(".join(' or ', $filter_or).")";

				//$a = $con->sql_query("select id, description, tree_str, concat(tree_str,'(',id,')') as ts2 from category where active=1 and $limit_dept and (id = $vint or description like " . ms($v.'%') . " or description like " . ms('% '.$v.'%') . ") order by ts2");
				$a = $con->sql_query($q = "select distinct (c.id) as id, c.code, c.description, c.tree_str, concat(c.tree_str,'(',c.id,')') as ts2, c.level, cc.*
									from category c 
									left join sku on sku.category_id=c.id
									left join category_cache cc on cc.category_id = c.id
									where c.active=1 and $where_filter and $filter_or order by ts2");
				//print $q;
			}
			if ($con->sql_numrows($a)<=0)
			{
			    print "<li class=\"list-group-item\" title=\"0\"><span class=informal>No matches for $v</span></li>";
			    exit;
			}
			/*if ($con->sql_numrows($a)>50)
			{
				print "<li><span class=informal>Found " . $con->sql_numrows($a) . " matches, showing first 50...</span></li>";
			}
			$n = 0;*/
		    while ($r=$con->sql_fetchrow($a))
		    {
				// check parent's status, if inactive then skip
				$is_inactive = false;
				for($i=1; $i<=$r['level']; $i++){
					if(!$r['p'.$i]) continue;

					$q1 = $con->sql_query("select * from category where id = ".mi($r['p'.$i])." and active = 1");
					
					if($con->sql_numrows($q1) == 0){
						$is_inactive = true;
						break;
					}
					$con->sql_freeresult($q1);
				}
				
				if($is_inactive) continue;
				
		        $pv = str_replace('+', '\+', $v);
		        $pv = str_replace('\\', '\\\\', $v);
		        $pv = str_replace('/', '\/', $v);
				$tt = get_category_tree($r['id'], $r['tree_str'], $have_child);
				if($r['level'] > 1){
					$tt .= " > ";
				}else{
					$tt .= " ";
				}
				
				if($config['category_search_include_code']&&strpos(strtolower($r['code']), strtolower($v))!==false) $tt .= "<span style='color:blue;'>[".$r['code']."]</span> ";
				$lbl = $tt . preg_replace("/($pv)/i", "<span class=sh>\\1</span>", $r['description']);
				
				$des = htmlspecialchars($r['description']);
				$tt = htmlspecialchars($tt);

	    	    //$min_photo = get_required_photo_count($r['id'],'CONSIGN');
	    	    $hc = $have_child ? "1" : "0";
				print "<li class=\"list-group-item list-group-item-action\" style=\"cusror:pointer;\" title=\"$r[id],$hc\">";
				print "$lbl";
				if ($have_child && !$no_findcat_expand) print "<img src=ui/findcat_expand.png align=absmiddle border=0 class=clickable title=\"expand child categories\" onclick=show_child($r[id]) hspace=5>";
				print "</li>";
				/*$n++;
                if ($n==50) break;*/
		    }
		    print "</ul>";
		    exit;
			
		case 'ajax_search_user':
			$uu = $_REQUEST['search_username'];
			
			$where = array();
			$where[] = "( u like ".ms("%".replace_special_char($uu)."%")." or l like ".ms("%".replace_special_char($uu)."%")." or fullname like ".ms("%".replace_special_char($uu)."%")." )";
			
			if ($_REQUEST['user_profile'] == 1 && BRANCH_CODE != 'HQ') {
				$bid = get_request_branch(true);
				$where[] = " user.default_branch_id = $bid ";
			}
			
			if($sessioninfo['id'] != 1){
				$where[] = "user.id != 1";
				$where[] = "(user.is_arms_user=0 or user.id=".mi($sessioninfo['id']).")";
			}
			$where = join(' and ', $where);
			
			print "<ul class=\"bg-white list-group list-group-flush rounded-40 shadow bd bd-t-0\">";
			$con->sql_query("select id,u,l,fullname from user where $where order by u,l,fullname");
			while ($user=$con->sql_fetchrow()) {
				print "<li class=\"list-group-item list-group-item-action\" style=\"cursor:pointer\" title=\"$user[id]\" u=\"".htmlspecialchars($user['u'])."\">$user[u] - $user[l] - $user[fullname]</li>";
			}
			print "</ul>";
			exit;

		case 'sku_cost_history':
		    $id = intval($_REQUEST['id']);
		    $con->sql_query("select qty,qty_loose,foc, foc_loose,order_price, po_date, branch.report_prefix, b2.report_prefix as report_prefix2, uom.code as uom, vendor.description as vendor, po_items.selling_price, po_items.tax, po_items.discount 
from po_items 
left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
left join uom on po_items.order_uom_id = uom.id 
left join vendor on po.vendor_id = vendor.id 
left join branch on po.branch_id = branch.id 
left join branch b2 on po.po_branch_id = b2.id 
where po.approved and po.active and po_items.sku_item_id = $id 
order by po.po_date desc, po.id desc") or die(mysql_error());
			$po_history=$con->sql_fetchrowset();
			//echo"<pre>";print_r($po_history);echo"</pre>";
		    $smarty->assign("po_history",$po_history);
			$smarty->display("popup.cost_history.tpl");
		    exit;
		case 'ajax_load_sku_group_list':
			$sg_filter = array();
			if($config['sku_group_searching_need_filter_user']){
				if($sessioninfo['level']>=900){
					// do nothing
				}elseif($sessioninfo['level']>=500){
		            $sg_filter[] = "s1.branch_id=".mi($sessioninfo['branch_id']);
				}else{
		            $sg_filter[] = "s1.user_id=".mi($sessioninfo['id']);
				}
			}
			
			if($_REQUEST['check_sn']){
				$sg_filter[] = "s.have_sn != 0";
			}
		    
			if($sg_filter) $filter = "where ".join(" and ", $sg_filter);
			
		    $sql = "select s1.*,count(s2.sku_item_code) as item_count 
					from sku_group s1
					left join sku_group_item s2 using(sku_group_id,branch_id)
					left join sku_items si on si.sku_item_code = s2.sku_item_code
					left join sku s on s.id = si.sku_id
					$filter 
					group by sku_group_id, branch_id 
					order by description";
		    
		    $q1 = $con->sql_query($sql) or die(mysql_error());

			if($con->sql_numrows($q1)>0){
			    print "<table width=100%>";
				$is_dbl_sku = $_REQUEST['is_dbl_sku'];
			    while($r = $con->sql_fetchassoc($q1)){
			        $sku_group_id = $r['sku_group_id'];
			        $branch_id = $r['branch_id'];
			        
					print "<tr onmouseover=\"this.bgColor='#f0f0f0'\" onmouseout=\"this.bgColor='';\"><td>";
					print "<a href=\"javascript:add_sku_item$is_dbl_sku('$sku_group_id','$branch_id')\">";
					print $r['description']." (".$r['item_count'].")</a>";
					print "</td></tr>";
				}
				print "</table>";
			}else{
				print "No Group Available";
			}
			$con->sql_freeresult($q1);
			
		    exit;
		case 'ajax_add_sku_item_into_list':
		    $sku_group_id = $_REQUEST['sku_group_id'];
			$branch_id = $_REQUEST['branch_id'];
			$check_sn = $_REQUEST['check_sn'];
			$is_dbl_sku = $_REQUEST['is_dbl_sku'];
			
			if($check_sn) $sn_filter = "and s.have_sn != 0";
		    
		    $con->sql_query("select sgi.*,si.description 
			from sku_group_item sgi
			left join sku_items si using (sku_item_code)
			left join sku s on s.id = si.sku_id
			where sku_group_id=$sku_group_id and branch_id=$branch_id $sn_filter");

			if($con->sql_numrows()>0){
				while($r = $con->sql_fetchrow()){
				    $code = jsstring($r['sku_item_code']);
				    $description = jsstring($r['description']);
				    
					print "<script>add_sku_to_list$is_dbl_sku('$code','$description');</script>";
				}
			}else{
                print "<script>alert('No item in this group');</script>";
			}
   
		    exit;
		    
		case 'ajax_allowed_login_branches':
			$tmp_branch_list = array();
			
			if($sessioninfo){
				$con->sql_query("select branch.code from user_privilege left join branch on branch_id = branch.id where privilege_code = 'LOGIN' and user_id = ".mi($sessioninfo['id'])." and branch.active=1 order by branch.sequence, branch.code");
				while($r=$con->sql_fetchassoc()){
					$tmp_branch_list[] = $r;
				}
				$con->sql_freeresult();	
			}elseif($vp_session){	// vendor portal
				$con->sql_query("select branch.code from branch where id in (".join(',', $vp_session['vp']['allowed_branches']).") and branch.active=1 order by branch.sequence, branch.code");
				while($r=$con->sql_fetchassoc()){
					$tmp_branch_list[] = $r;
				}
				$con->sql_freeresult();
			}elseif($dp_session){	// debtor portal
				$con->sql_query("select branch.code from branch where branch.active=1 order by branch.sequence, branch.code");
				while($r=$con->sql_fetchassoc()){
					$tmp_branch_list[] = $r;
				}
				$con->sql_freeresult();
			}
			
			print "<select id=\"goto_branch_select\" class=\"form-control select2\">";
			if($tmp_branch_list){
				foreach($tmp_branch_list as $r){
					print "<option value=\"$r[code]\" ".(BRANCH_CODE==$r['code']?'selected':'').">$r[code]</option>";
				}
			}
		    print "</select>";
		    exit;
		    
	    case 'ajax_search_color_size':

		    $out = '';

	        if ($_REQUEST['color']){

		        $color=file("color.txt");
		        $result = array_filter($color,'color_filter');

			}
			else{

		        $size=file("size.txt");
		        $result = array_filter($size,'size_filter');
			}

			print "<ul>";
			if (count($result) > 0)
			{
			    if (count($result) > 15)
			    {
					print "<li><span class=informal>Found ".count($result)." matches, showing first 15</span></li>";
				}
				$c = 0;
				foreach ($result as $found)
				{
					$out .= "<li title=\"$found\">". htmlspecialchars($found) ."</li>";
					$c++;
					if ($c >= 15) break;
				}
	        }
			print $out;
	        print "</ul>";

			exit;
			
		case 'ajax_vendor_checkout':
		    if (!$_REQUEST['vendor_id'])exit;
		    else{
				$where="gra.status in (0,2) and gra.returned = 0 and gra.vendor_id=".mi($_REQUEST['vendor_id'])." and gra.branch_id=".mi($sessioninfo['branch_id']);
				$sql="select * 
					  from gra 
					  left join gra_items gi on gi.gra_id = gra.id and gi.branch_id = gra.branch_id
					  where $where group by gra.id";

				$check_gra=$con->sql_query($sql);
				$num=$con->sql_numrows($check_gra);

				if ($num>0){
				    $link= "from=0000-00-00&to=".date("Y-m-d")."&branch_id=$sessioninfo[branch_id]&department_id=&returned=3&vendor_id=$_REQUEST[vendor_id]";
				    printf($LANG['GRA_UNCHEKOUT_ITEMS']," <a href='goods_return_advice.summary_by_dept.php?$link' target='_blank'>Click here</a> to view from GRA Summary.");
			    }
			}
			exit;

		case 'ajax_search_replacement_item_group':
		    ajax_search_replacement_item_group();
		    exit;
		case 'ajax_search_sku_by_handheld':
		    ajax_search_sku_by_handheld();
		    exit;
		case 'ajax_search_grn':
			ajax_search_grn();
			exit;
		case 'ajax_delete_grn_distribution':
			ajax_delete_grn_distribution();
			exit;
		case 'ajax_search_debtor':
			ajax_search_debtor();
			exit;
		case 'ajax_search_sa': // to search sales agent
			ajax_search_sa();
			exit;
		default:
			print "<ul>";
	        print "<li>Unhandled Request<span class=informal>";
			print_r($_REQUEST);
			print "</span></li>";
	        print "</ul>";
	        exit;

	}

function ajax_search_replacement_item_group(){
	global $con;
	
	$v = trim($_REQUEST['str']);
	if(!$v) return;
	print "<ul>";
	$con->sql_query("select * from ri where group_name like ".ms(replace_special_char($v).'%')." or group_name like ".ms('% '.replace_special_char($v).'%')." order by group_name limit 51");
    if($con->sql_numrows() > 0){
	    if($con->sql_numrows() > 50)
	    {
			print "<li><span class=informal>showing first 50</span></li>";
		}
		while($r = $con->sql_fetchrow()){
			print "<li title=\"$r[id]\">".htmlspecialchars($r['group_name'])."</li>";
		}
    }
	print "</ul>";
}

function ajax_search_sku_by_handheld(){
	global $con;
	
	$v = trim($_REQUEST['value']);
    $is_parent_only = mi($_REQUEST['is_parent_only']);
    
	if(isset($_REQUEST['fresh_market_filter']))	$fresh_market_filter = trim($_REQUEST['fresh_market_filter']);
	if($fresh_market_filter){
		$filter[]= "(sku.is_fresh_market=".ms($fresh_market_filter)." or (sku.is_fresh_market='inherit' and cc.is_fresh_market=".ms($fresh_market_filter)."))";
	}
	$filter[] = "(si.sku_item_code=".ms(substr($v,0,12))." or si.mcode = ".ms($v)." or si.mcode = ".ms(substr($v,0,12))." or si.artno = ".ms($v)." or si.link_code =".ms($v)." or si.link_code = ".ms(substr($v,0,12)).")";
	$filter[] = "si.active=1";
	
	$filter = "where ".join(' and ', $filter);
	$sql = "select si.id,si.description,si.is_parent,si.sku_id,si.doc_allow_decimal
	from sku_items si
	left join sku on sku.id=si.sku_id
	left join category_cache cc on cc.category_id=sku.category_id
	$filter";

    $con->sql_query($sql) or die(mysql_error());
    $sku = $con->sql_fetchassoc();

	if($sku && $is_parent_only && !$sku['is_parent']){    // must use parent
		// change to use parent
		$con->sql_query("select si.id,si.description,si.is_parent,si.sku_id
from sku_items si
where si.sku_id=".mi($sku['sku_id'])." and si.is_parent=1 limit 1");
        $sku = $con->sql_fetchassoc();
        if($sku)    $sku['change_from_child_to_parent'] = 1;
	}
		
    if($sku)
    {
        print json_encode($sku);
    }else{
		print "no";
	}
}

function ajax_search_grn(){
	global $con, $sessioninfo;
		
	if(BRANCH_CODE == 'HQ')	$branch_id = mi($_REQUEST['branch_id']);
	else	$branch_id = $sessioninfo['branch_id'];
	$v = trim(strtolower($_REQUEST['value']));
	
	$filter = array();
	if($branch_id)	$filter[] = "grn.branch_id=".mi($branch_id);
	
	if(strpos($v, 'grn')!==false || strpos($v, 'grr')!==false)	$v = mi(str_replace('grn', '', $v));
	$filter[] = "(grri.doc_no=".ms($v)." or grn.id=".mi($v)." or grr.id=".mi($v).")";
	$filter[] = "grr.active=1 and grn.active=1";
	if(isset($_REQUEST['status']))	$filter[] = "grn.status=".mi($_REQUEST['status']);
	if(isset($_REQUEST['approved']))	$filter[] = "grn.approved=".mi($_REQUEST['approved']);
	
	$filter = $filter ? 'where '.join(' and ', $filter) : '';
	
	$sql = "select grr.rcv_date, grri.doc_no, grn.branch_id, grn.id as grn_id, branch.code as bcode,branch.report_prefix,grr.id as grr_id
	from grn
	left join grr_items grri on grri.branch_id=grn.branch_id and grri.id=grn.grr_item_id
	left join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
	left join branch on branch.id=grn.branch_id
	$filter";
	
	$q1 = $con->sql_query($sql);
	$maximum_row_num = 20;
	$row_count = $con->sql_numrows($q1);
	
	$output = "<ul>";
	if ($row_count > $maximum_row_num){
		$output .= "<li><span class=informal>Found ".$row_count." matches, showing first $maximum_row_num</span></li>";
	}
	
	$c = 0;
	while($r = $con->sql_fetchassoc($q1)){
		$desc = 'Branch: '.$r['bcode'].', '.$r['report_prefix'].sprintf("%05d", $r['grr_id']).', '.$r['report_prefix'].sprintf("%05d", $r['grn_id']).', Doc no: '.$r['doc_no'].', Received date: '.$r['rcv_date'];		
		$output .= "<li title='$r[branch_id],$r[grn_id],$r[doc_no]'>". htmlspecialchars($desc) ."</li>";
		$c++;
		if ($c >= $maximum_row_num) break;
	}
	if($row_count<=0)	$output .= "<li><span class=informal>No item found.</span></li>";	
	$output .= "</ul>";
	$con->sql_freeresult($q1);
	
	print $output;
}

function ajax_delete_grn_distribution(){
	global $con, $sessioninfo, $LANG;
	
	$bid = mi($_REQUEST['bid']);
	$grn_id = mi($_REQUEST['grn_id']);
	if (BRANCH_CODE != "HQ")	die($LANG['HQ_ONLY']);
	if(!privilege('NT_GRN_DISTRIBUTE'))	die(sprintf($LANG['NO_PRIVILEGE'], 'NT_GRN_DISTRIBUTE', BRANCH_CODE));
	if($sessioninfo['level']<9999)	die($LANG['USER_LEVEL_NO_REACH']);
	
	$con->sql_query("update grn set need_monitor_deliver=0 where branch_id=$bid and id=$grn_id");
	
	print "OK";
}

function get_mysql_stopwords_list(){
	$array_stop_words = array('a\'s',  
	'able',  
	'about',  
	'above',  
	'according',  
	'accordingly',  
	'across',  
	'actually',  
	'after',  
	'afterwards',  
	'again',  
	'against',  
	'ain\'t',  
	'all',  
	'allow',  
	'allows',  
	'almost',  
	'alone',  
	'along',  
	'already',  
	'also',  
	'although',  
	'always',  
	'am',  
	'among',  
	'amongst',  
	'an',  
	'and',  
	'another',  
	'any',  
	'anybody',  
	'anyhow',  
	'anyone',  
	'anything',  
	'anyway',  
	'anyways',  
	'anywhere',  
	'apart',  
	'appear',  
	'appreciate',  
	'appropriate',  
	'are',  
	'aren\'t',  
	'around',  
	'as',  
	'aside',  
	'ask',  
	'asking',  
	'associated',  
	'at',  
	'available',  
	'away',  
	'awfully',  
	'be',  
	'became',  
	'because',  
	'become',  
	'becomes',  
	'becoming',  
	'been',  
	'before',  
	'beforehand',  
	'behind',  
	'being',  
	'believe',  
	'below',  
	'beside',  
	'besides',  
	'best',  
	'better',  
	'between',  
	'beyond',  
	'both',  
	'brief',  
	'but',  
	'by',  
	'c\'mon',  
	'c\'s',  
	'came',  
	'can',  
	'can\'t',  
	'cannot',  
	'cant',  
	'cause',  
	'causes',  
	'certain',  
	'certainly',  
	'changes',  
	'clearly',  
	'co',  
	'com',  
	'come',  
	'comes',  
	'concerning',  
	'consequently',  
	'consider',  
	'considering',  
	'contain',  
	'containing',  
	'contains',  
	'corresponding',  
	'could',  
	'couldn\'t',  
	'course',  
	'currently',  
	'definitely',  
	'described',  
	'despite',  
	'did',  
	'didn\'t',  
	'different',  
	'do',  
	'does',  
	'doesn\'t',  
	'doing',  
	'don\'t',  
	'done',  
	'down',  
	'downwards',  
	'during',  
	'each',  
	'edu',  
	'eg',  
	'eight',  
	'either',  
	'else',  
	'elsewhere',  
	'enough',  
	'entirely',  
	'especially',  
	'et',  
	'etc',  
	'even',  
	'ever',  
	'every',  
	'everybody',  
	'everyone',  
	'everything',  
	'everywhere',  
	'ex',  
	'exactly',  
	'example',  
	'except',  
	'far',  
	'few',  
	'fifth',  
	'first',  
	'five',  
	'followed',  
	'following',  
	'follows',  
	'for',  
	'former',  
	'formerly',  
	'forth',  
	'four',  
	'from',  
	'further',  
	'furthermore',  
	'get',  
	'gets',  
	'getting',  
	'given',  
	'gives',  
	'go',  
	'goes',  
	'going',  
	'gone',  
	'got',  
	'gotten',  
	'greetings',  
	'had',  
	'hadn\'t',  
	'happens',  
	'hardly',  
	'has',  
	'hasn\'t',  
	'have',  
	'haven\'t',  
	'having',  
	'he',  
	'he\'s',  
	'hello',  
	'help',  
	'hence',  
	'her',  
	'here',  
	'here\'s',  
	'hereafter',  
	'hereby',  
	'herein',  
	'hereupon',  
	'hers',  
	'herself',  
	'hi',  
	'him',  
	'himself',  
	'his',  
	'hither',  
	'hopefully',  
	'how',  
	'howbeit',  
	'however',  
	'i\'d',  
	'i\'ll',  
	'i\'m',  
	'i\'ve',  
	'ie',  
	'if',  
	'ignored',  
	'immediate',  
	'in',  
	'inasmuch',  
	'inc',  
	'indeed',  
	'indicate',  
	'indicated',  
	'indicates',  
	'inner',  
	'insofar',  
	'instead',  
	'into',  
	'inward',  
	'is',  
	'isn\'t',  
	'it',  
	'it\'d',  
	'it\'ll',  
	'it\'s',  
	'its',  
	'itself',  
	'just',  
	'keep',  
	'keeps',  
	'kept',  
	'know',  
	'knows',  
	'known',  
	'last',  
	'lately',  
	'later',  
	'latter',  
	'latterly',  
	'least',  
	'less',  
	'lest',  
	'let',  
	'let\'s',  
	'like',  
	'liked',  
	'likely',  
	'little',  
	'look',  
	'looking',  
	'looks',  
	'ltd',  
	'mainly',  
	'many',  
	'may',  
	'maybe',  
	'me',  
	'mean',  
	'meanwhile',  
	'merely',  
	'might',  
	'more',  
	'moreover',  
	'most',  
	'mostly',  
	'much',  
	'must',  
	'my',  
	'myself',  
	'name',  
	'namely',  
	'nd',  
	'near',  
	'nearly',  
	'necessary',  
	'need',  
	'needs',  
	'neither',  
	'never',  
	'nevertheless',  
	'new',  
	'next',  
	'nine',  
	'no',  
	'nobody',  
	'non',  
	'none',  
	'noone',  
	'nor',  
	'normally',  
	'not',  
	'nothing',  
	'novel',  
	'now',  
	'nowhere',  
	'obviously',  
	'of',  
	'off',  
	'often',  
	'oh',  
	'ok',  
	'okay',  
	'old',  
	'on',  
	'once',  
	'one',  
	'ones',  
	'only',  
	'onto',  
	'or',  
	'other',  
	'others',  
	'otherwise',  
	'ought',  
	'our',  
	'ours',  
	'ourselves',  
	'out',  
	'outside',  
	'over',  
	'overall',  
	'own',  
	'particular',  
	'particularly',  
	'per',  
	'perhaps',  
	'placed',  
	'please',  
	'plus',  
	'possible',  
	'presumably',  
	'probably',  
	'provides',  
	'que',  
	'quite',  
	'qv',  
	'rather',  
	'rd',  
	're',  
	'really',  
	'reasonably',  
	'regarding',  
	'regardless',  
	'regards',  
	'relatively',  
	'respectively',  
	'right',  
	'said',  
	'same',  
	'saw',  
	'say',  
	'saying',  
	'says',  
	'second',  
	'secondly',  
	'see',  
	'seeing',  
	'seem',  
	'seemed',  
	'seeming',  
	'seems',  
	'seen',  
	'self',  
	'selves',  
	'sensible',  
	'sent',  
	'serious',  
	'seriously',  
	'seven',  
	'several',  
	'shall',  
	'she',  
	'should',  
	'shouldn\'t',  
	'since',  
	'six',  
	'so',  
	'some',  
	'somebody',  
	'somehow',  
	'someone',  
	'something',  
	'sometime',  
	'sometimes',  
	'somewhat',  
	'somewhere',  
	'soon',  
	'sorry',  
	'specified',  
	'specify',  
	'specifying',  
	'still',  
	'sub',  
	'such',  
	'sup',  
	'sure',  
	't\'s',  
	'take',  
	'taken',  
	'tell',  
	'tends',  
	'th',  
	'than',  
	'thank',  
	'thanks',  
	'thanx',  
	'that',  
	'that\'s',  
	'thats',  
	'the',  
	'their',  
	'theirs',  
	'them',  
	'themselves',  
	'then',  
	'thence',  
	'there',  
	'there\'s',  
	'thereafter',  
	'thereby',  
	'therefore',  
	'therein',  
	'theres',  
	'thereupon',  
	'these',  
	'they',  
	'they\'d',  
	'they\'ll',  
	'they\'re',  
	'they\'ve',  
	'think',  
	'third',  
	'this',  
	'thorough',  
	'thoroughly',  
	'those',  
	'though',  
	'three',  
	'through',  
	'throughout',  
	'thru',  
	'thus',  
	'to',  
	'together',  
	'too',  
	'took',  
	'toward',  
	'towards',  
	'tried',  
	'tries',  
	'truly',  
	'try',  
	'trying',  
	'twice',  
	'two',  
	'un',  
	'under',  
	'unfortunately',  
	'unless',  
	'unlikely',  
	'until',  
	'unto',  
	'up',  
	'upon',  
	'us',  
	'use',  
	'used',  
	'useful',  
	'uses',  
	'using',  
	'usually',  
	'value',  
	'various',  
	'very',  
	'via',  
	'viz',  
	'vs',  
	'want',  
	'wants',  
	'was',  
	'wasn\'t',  
	'way',  
	'we',  
	'we\'d',  
	'we\'ll',  
	'we\'re',  
	'we\'ve',  
	'welcome',  
	'well',  
	'went',  
	'were',  
	'weren\'t',  
	'what',  
	'what\'s',  
	'whatever',  
	'when',  
	'whence',  
	'whenever',  
	'where',  
	'where\'s',  
	'whereafter',  
	'whereas',  
	'whereby',  
	'wherein',  
	'whereupon',  
	'wherever',  
	'whether',  
	'which',  
	'while',  
	'whither',  
	'who',  
	'who\'s',  
	'whoever',  
	'whole',  
	'whom',  
	'whose',  
	'why',  
	'will',  
	'willing',  
	'wish',  
	'with',  
	'within',  
	'without',  
	'won\'t',  
	'wonder',  
	'would',  
	'would',  
	'wouldn\'t',  
	'yes',  
	'yet',  
	'you',  
	'you\'d',  
	'you\'ll',  
	'you\'re',  
	'you\'ve',  
	'your',  
	'yours',  
	'yourself',  
	'yourselves',  
	'zero');
	
	return $array_stop_words;
}

function ajax_search_debtor(){
	global $con, $smarty, $sessioninfo, $config;
	
	print "<ul>";
	
	$v = strval($_REQUEST['debtor']);
	$vint = intval($v);
	$filter = array();
	$filter[] = "d.active=1";
	$filter[] = "(d.id = $vint or d.description like " . ms('%'.replace_special_char($v).'%')." or d.code=".ms($v).")";
	
	$where_filter = join(' and ', $filter);

	$q1 = $con->sql_query($q = "select distinct (d.id) as id, d.code, d.description
						from debtor d
						where $where_filter order by d.code, d.description
						limit 50");
	//print $q;
	
	if ($con->sql_numrows($q1)<=0)
	{
		print "<li title=\"0\"><span class=informal>No matches for $v</span></li>";
		exit;
	}
	if ($con->sql_numrows($q1)>50)
	{
		print "<li><span class=informal>Found " . $con->sql_numrows($q1) . " matches, showing first 50...</span></li>";
	}
	
	while ($r=$con->sql_fetchassoc($q1)){		
		$pv = str_replace('+', '\+', $v);
		$pv = str_replace('\\', '\\\\', $v);
		$pv = str_replace('/', '\/', $v);
		$tt = "";
		
		if(strpos(strtolower($r['code']), strtolower($v))!==false) $tt .= "<span style='color:blue;'>[".$r['code']."]</span> ";	// High Code if matched code
		
		$lbl = $tt . preg_replace("/($pv)/i", "<span class=sh>\\1</span>", $r['description']);
		
		//$des = htmlspecialchars($r['description']);
		//$tt = htmlspecialchars($tt);
		
		print "<li title=\"$r[id]\">";
		print "$lbl";
		print "</li>";
	}
	print "</ul>";
	$con->sql_freeresult($q1);
}

function ajax_search_sa(){
	global $con;
	
	$sa_name = $_REQUEST['search_sa_name'];
	
	$where = array();
	$where[] = "( sa.code like ".ms("%".replace_special_char($sa_name)."%")." or sa.name like ".ms("%".replace_special_char($sa_name)."%").")";
	
	$where = join(' and ', $where);
	
	print "<ul>";
	$q1 = $con->sql_query("select sa.id, sa.code, sa.name from sa where $where order by sa.code, sa.name");
	while ($sa=$con->sql_fetchassoc($q1)) {
		print "<li title=\"$sa[id]\" sa_name=\"".htmlspecialchars($sa['name'])."\">$sa[code] - $sa[name]</li>";
	}
	print "</ul>";
}
?>
