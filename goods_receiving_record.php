<?
/*
Revision History
================
4/20/07 3:31:00 PM   yinsee
- remove sku_vendor_history for HQ using GURUN's data

4/28/2007 5:10:45 PM    yinsee
- added PO cancel date checking
 >> PO_CANNOT_RECEIVE_UPON_CANCEL_DATE
 
8/2/2007 1:45:18 PM  yinsee + gary
- change the curr_po_id to curr_po_no (take the doc_no as key)
- line 109
- line 165

8/3/2007 2:03:09 PM  -gary
- added send_pm (po link)
- line 132

13/08/07 gary
- fix from $form[curr_po_no][$n] (syntax error) to $form['doc_no'][$n]

9/18/2007 12:39:59 PM yinsee
- remove notify user from pm when PO received

9/21/2007 12:14:19 PM gary
- add view grr case.

9/21/2007 3:13:25 PM gary
- add validate for proformal po_no in ajax and when save (validate_data).

10/16/2007 2:45:33 PM gary
- GRN worksheet show PO Cost and latest selling price. mark "*" if below cost. 
- if FOC, show last GRN cost (or master if no GRN).

10/22/2007 15:50:17 AM gary
- order the priting worksheet by po_items.id

11/2/2007 3:50:30 PM gary
- set time limit 0 for printing

1/23/2008 6:37:27 PM gary
- validate the PO_no is zero.

1/24/2008 1:53:18 PM yinsee
- replace SQL for calling last-GRN cost

2/4/2008 11:02:52 AM yinsee
- GRR rcv_date cannot be greater than today

2/11/2008 1:18:10 PM gary
- fix the left join (branch_id) bug when checking the existing doc_no (non-po).

3/5/2008 10:53:19 AM gary
- change the old purchase_order.php link to new po (po.php).

4/8/2008 1:51:23 PM gary
- sum all qty by sku_item_id in po when print worksheet.

5/5/2008 2:47:35 PM gary
- get po selling and current selling for worksheet printing.

5/9/2008 12:49:02 PM gary
- add min(po_items.id) to resort the foc items sequence.

3/20/2009 5:05:51 PM yinsee
- fix Partial Delivery checking (remove doc_no comparison)

4/3/2009 4:44:37 PM yinsee
- fix partial delivery checking again (bug: edit grr cannot save) 

6/23/2009 4:30 PM Andy
- add checking on $config['grr_alt_print_template'] and $config['grr_worksheet_alt_print_template'] to allow custom print

8/3/2009 3:29:45 PM Andy
- add Reset function

11/19/2009 6:12:22 PM Andy
- add change grn_used=0 in reset

2/1/2010 6:57:27 PM Andy
- Add feature to allow process DO (click to generate grr and grn)

2/5/2010 1:56:05 PM Andy
- Add config['grr_process_do'] to control show/hide Process DO

5/28/2010 11:52:39 AM Alex
- add config['upper_date_limit'] and config['lower_date_limit'] and config['reset_date_limit']

6/15/2010 1:28:42 PM Andy
- GRR process DO change to dont auto approved.

7/27/2010 5:21:45 PM Alex
- Add delete(set active=0) on grr and can only be displayed by searching it.

8/5/2010 5:58:20 PM Andy
- GRR process DO change to dont auto approved.

8/30/2010 4:16:01 PM Andy
- Fix grr reset bugs, if grr reset second or more time, it always cancel the first time created GRN.

10/11/2010 12:54:00 PM Alex
- Fix bugs on create same PO in different grr after delete a grr that contain current PO by set delivered to 0 after after delete

12/2/2010 3:27:27 PM Justin
- Added item printing on per page based on config set.
- Applied to both GRR report and Worksheet reports.

3/28/2011 3:41:05 PM Justin
- Added IBT validation for GRR.

4/13/2011 12:42:30 PM Justin
- Fixed the IBT validation to skip checking for those invoice and others type.

4/14/2011 3:39:56 PM Justin
- When checking PO, added to check is IBT/non-IBT as well.

4/18/2011 10:26:44 AM Alex
- fix delete grr bugs to check po_branch_id and po_no

5/6/2011 5:33:11 PM Justin
- Added the checking for GRR must contain at least one Inv, DO or Other when using GRN future.

7/6/2011 11:44:26 AM Andy
- Change split() to use explode()

8/4/2011 3:48:11 PM Justin
- Fixed the PO's delivered cannot update once GRR being created.

8/8/2011 11:05:11 AM Justin
- Fixed the bugs once search GRR, system is unable to show all GRR anymore even not searching anything.

4:35 PM 10/27/2011 Justin
- Fixed the bugs when change PO become other type, system cannot did not update back the lorry icon become undeliver.
- Fixed while delete GRR that contains PO item, system will only update the first PO item become undelivered.

3/5/2012 3:33:31 PM Justin
- Added to pickup GRN list.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/25/2012 12:25 PM Justin
- Added to pick up packing UOM fraction while printing report.

1/29/2013 10:32 AM Justin
- Bug fixed on sometimes when print worksheet, the PO item's remark cannot show as if having normal + foc item.

3/8/2013 6:00 PM Justin
- Bug fixed on loading available PO that always load empty list.
- Bug Fixed on loading available PO that cannot loads own branch PO.

3/21/2013 10:22 AM Justin
- Modified the way of updating sku_items_cost while reset GRR.

4/3/2013 3:03 PM Justin
- Bug fixed on calculating cost and qty.

4/4/2013 1:38 PM Justin
- Bug fixed on sql error while reset grr.

4/23/2013 1:40 PM Justin
- Bug fixed on cannot re-use PO even cancel the GRR.
- Added new checking to validate GRR must have PO control by vendor.

4/25/2013 6:01 PM Justin
- Bug fixed on user did not key in Document No but ticked type as PO, system will still allow user to save GRR.

5/14/2013 10:16 AM Justin
- Bug fixed on po cost does not average by uom fraction.

5/20/2013 3:34 PM Andy
- Change when process DO it will get sku_item_id first, then update sku_items_cost by sku_item_id.

07/15/2013 06:05 PM Justin
- Added "View available DO" feature.

7/30/2013 4:09 PM Andy
- Enhance GRR to get PO owner notification settings when send pm.

9/9/2013 11:40 AM Fithri
- in 'view available PO', show HQ PO no if available

10/8/2013 5:57 PM Justin
- Enhanced to apply checking for cancellation date of multiple branches from PO.

1/23/2014 3:53 PM Andy
- Fix cancel_date sometime show as "Array" bug.
- Enhance to load PO branch report prefix when load available po.

3/13/2014 3:53 PM Justin
- enhance to have new feature that can insert department & vendor automatically when user provides document no by PO/DO.

12/16/2014 3:47 PM Justin
- Bug fixed on GRR without PO checking by branch since it is missing.

4/11/2015 11:12 AM Justin
- Enhanced to have GST calculation.

4/20/2015 10:14 AM Justin
- Enhanced to have more specific error message while reset GRR.

4/22/2015 10:58 AM Justin
- Bug fixed on GST checking get wrong vendor ID.

5/6/2015 5:09 PM Justin
- Enhanced to have document date and GST code selection.
- Enhanced to allow user key in same invoice no if having different tax codes.

5/13/2015 11:42 AM Justin
- Enhanced to allow user can edit back previous GRR if found it is being created into GRN.

5/18/2015 2:28 PM Justin
- Enhanced to have check vendor.

6/4/2015 3:09 PM Justin
- Bug fixed on system did not set new grr items as used if edit existing GRR.

6/12/2015 2:36 PM Justin
- Enhanced to have validation on GST amount must have figures when GST rate is not zero percent.

6/15/2015 5:39 PM Justin
- Bug fixed on system will do GST validation for those non-GST GRR.

7/15/2015 5:28 PM Andy
- Change to only list latest 3 month GRR.

7/28/2015 2:43 PM Justin
- Bug fixed on system will show clone GRR items if GRN have been canceled and re-do.

01/05/2015 3:06 PM DingRen
- load more info on ajax_search_doc_info

3/1/2016 10:50 AM Qiu Ying
- Modify grr_reset to use the reset_grr function in "goods_receiving_record.include.php"

04-Mar-2016 17:20 Edwin
- Bug fixed on GRN checking amount overwrite to new GRN amount when it fail to save.
- Bug fixed on GRN invoice counter checker failure after prompt error due to more than one invoice detected

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

5/3/2016 5:00 PM Andy
- Enhanced to not allow edit on inactive GRR.

05/06/2016 10:30 Edwin
- Bug fixed on save rejected when documents sequence changed.

5/26/2016 5:45 PM Andy
- Fix print grr worksheet to use grr branch selling price.

5/30/2016 6:01 PM Andy
- Added checking others grn before set the po delivered to 0 when deleting grr.
- Fixed check po delivered qty to compatible to grn future.

5/31/2016 5:01 PM Andy
- Fix query error on update_po_receiving_count().

06/21/2016 10:00 Edwin
- Bug fixed on missing date in wroksheet
- Bug fixed on unable to retrieve data from PO which have multiple branch
- Enhanced on show exclusive tax tag if SKU item is exclude tax in worksheet

7/25/2016 4:25 PM Andy
- Fixed print grn worksheet to follow po items sequence.

9/5/2016 1:54 PM Andy
- Fixed print grn worksheet po cost & qty empty bug.

12/1/2016 12:00 PM Andy
- Enhanced to check vendor internal code when search IBT DO.

12/6/2016 9:39 AM Andy
- Hide ARMS user from user list.

1/13/2017 12:01 PM Andy
- Move update_po_receiving_count() to goods_receiving_record.include.php
- Enhanced to merge grn_cancel and reset_grr to process_reset_grr_grn()

4/18/2017 09:20 AM Qiu Ying
- Enhanced to prompt error message when PO cancellation date overdue & upload image

7/18/2017 14:31  Qiu Ying
- Enhanced to download the saved attachment
- Enhanced to save pdf file and display pdf file as image in GRR

7/25/2017 17:46 Qiu Ying
- Bug fixed on cannot download image and pdf file with name contain space

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/15/2017 9:20 AM Justin
- Enhanced to have "Allow Multiple Department" checkbox.

11/16/2017 3:47 PM Justin
- Bug fixed on column name changing.

11/29/2017 1:57 PM Justin
- Enhanced to display error message while found GRR does not contain any invoice document (happens when the GRN is created that is under Account Verification or Approved only).

4/19/2018 3:14 PM Justin
- Enhanced to have foreign currency feature.

7/26/2018 6:09 PM Justin
- Bug fixed on currency rate will insert as 0 if not using foreign currency.

8/9/2018 5:38 PM Justin
- Enhanced to load GRR attachments from grnManager.

8/28/2018 10:39 AM Andy
- Add SST feature.

10/9/2018 2:00 PM Justin
- Enhanced to allow receive PO with cancellation date on the same day as goods receive date.
- Enhanced allow user to receive overdue PO (need to provide username and password for privilege checking).

5/16/2019 3:44 PM William
- Pickup report_prefix for enhance "GRR" and "GRN".

10/21/2019 1:21 PM Andy
- Fixed GRR unable to delete photo.

12/20/2019 10:03 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRR', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
//include("purchase_order.include.php");
include("po.include.php");
include("goods_receiving_record.include.php");
include_once("masterfile_sku_application.include.php");
init_selection();

$smarty->assign("PAGE_TITLE", "GRR (Goods Receiving Record)");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'download_photo':
			$file = urldecode($_REQUEST['f']);
			$path = pathinfo($file);
			
			if(!file_exists($file)){
				print "<script type='text/javascript'>
				alert('File is missing, cannot be downloaded.');
				</script>";
				exit;
			}
			ob_start();
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			switch(strtolower($path["extension"])){
				case 'pdf':
					header('Content-type: application/pdf');
					break;
				case 'jpg':
				case 'jpeg':
				case 'png':
					header('Content-type: image');
					break;
			}

			header("Content-Disposition: attachment; filename=\"". basename($file) ."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($file));
			header('Accept-Ranges: bytes');
			ob_clean();
			flush();
			echo file_get_contents($file);
			exit;
		case 'add_photo':
			$tmp_time = $_REQUEST["tmp_time"];
				
			$tmp_dir_path = $_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] . "/" . $tmp_time;
			
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr");
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp");
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp/" . $sessioninfo["branch_id"]);
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"]);
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] . "/". $tmp_time);
			check_and_create_dir($_SERVER['DOCUMENT_ROOT'] . "/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] . "/". $tmp_time ."/pdf");
			
			$path = $_FILES['fnew']['name'];
			$ext = pathinfo($path, PATHINFO_EXTENSION);
			list($name,$dummy) = explode(".$ext", $path);
			
			// found it is uploaded by PDF file, convert the first page into image
			if($_FILES['fnew']['type'] == "application/pdf"){
				$name = preg_replace("/[^A-Za-z0-9_-]/", "", $name) . "." . strtolower($ext);
				$filepath1 = "$tmp_dir_path/pdf/".$name;
				move_uploaded_file($_FILES['fnew']['tmp_name'], $filepath1);
				$img_path = str_replace(".pdf", ".jpg", $name);
				$params = array();
				$params['image_path'] = $img_path;
				$params['sku_path'] = $tmp_dir_path;
				$params['pdf_path'] = $filepath1;
				
				$filepath = pdf_handler($params);
				move_uploaded_file($filepath1, $filepath);
				$file_type = "pdf";
			}else{
				$name = $name . "." . strtolower($ext);
				$filepath = "$tmp_dir_path/".$name;
				move_uploaded_file($_FILES['fnew']['tmp_name'], $filepath);
				//if (!$config["sku_no_resize_photo"])
				resize_photo($filepath,$filepath);
			}
			
			$imagep = str_replace($_SERVER['DOCUMENT_ROOT']. "/", "", $filepath);
			chmod($imagep,0777);
			$urlenc = urlencode($imagep);
			
			print "<div id=ret><div class=imgrollover>
<img width=110 height=100 align=absmiddle vspace=4 hspace=4 src=\"/thumb.php?w=110&h=100&img=$urlenc\" border=0 style=\"cursor:pointer\" onclick=\"popup_div('img_full', '<img width=640 src=\'$imagep\'>')\" title=\"View\"><br>
<img src=\"/ui/del.png\" align=absmiddle onclick=\"if (confirm('Are you sure?'))del_image(this.parentNode,'$urlenc')\"> Delete
</div></div><script>parent.window.upload_callback(document.getElementById('ret'));</script>";
			exit;
		case 'ajax_load_available_po':
			load_available_po();
			exit;
	
		case 'process_po_no':
			process_po_no();
			exit;
		
		case 'print':
			set_time_limit(0);
			do_print();
			exit;
		
		//for view purpose.
		case 'view':
			$form=$_REQUEST;
		    $id = intval($form['id']);
		    $branch_id = intval($form['branch_id']);
			view_grr($id,$branch_id);

			exit;
		
		case 'open':
		    $id = intval($_REQUEST['id']);
		    $branch_id = intval($_REQUEST['branch_id']);
		    $q1 = $con->sql_query("select grr.*, grr.id as grr_id, DATE_FORMAT(grr.rcv_date,'%Y-%m-%d') as rcv_date, vendor.description as vendor from grr left join vendor on grr.vendor_id = vendor.id where grr.id=$id and grr.branch_id = $branch_id");
			$form = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		    $form['grn_used'] = 0;
			if($form['active'] && $form['status']) $form['grn_used'] = 1;
			if(!$form['active']){
				header("Location: $_SERVER[PHP_SELF]?a=view&branch_id=$branch_id&id=$id");exit;
			}
			/*if ($con->sql_numrows()>0)
		    {
				js_redirect(sprintf($LANG['GRR_CANNOT_EDIT'],$id), $_SERVER['PHP_SELF']);
				exit;
			}*/
			unset($form['id']);

			// get GRR items
            $q1 = $con->sql_query("select grr_items.*, doc_no as curr_po_no from grr_items where grr_id = $id and branch_id = $branch_id order by id");
			while($r=$con->sql_fetchassoc($q1))
			{
				foreach (explode(",", "id,curr_po_no,doc_no,doc_date,type,ctn,pcs,amount,remark,gst_amount,gst_id,gst_code,gst_rate,tax,po_override_by_user_id") as $field)
				{
					$form[$field][] = $r[$field];
				}
				$n++;
			}
			$con->sql_freeresult($q1);
           
			$form['edit_on']=1;
            $form['old_grr_amount'] = $form['grr_amount'];
            $form['old_grr_gst_amount'] = $form['grr_gst_amount'];
			$smarty->assign("form", $form);
			break;

		case 'save':
		    $form = $_REQUEST;
		    $err = validate_data($form);
			
		    if (!$err)
		    {
				//$form['rcv_date'] = dmy_to_sqldate($form['rcv_date']);
				$upd = array();
				$upd['vendor_id'] = $form['vendor_id'];
				$upd['department_id'] = $form['department_id'];
				$upd['rcv_by'] = $form['rcv_by'];
				$upd['rcv_date'] = $form['rcv_date'];
				$upd['transport'] = $form['transport'];
				$upd['grr_ctn'] = $form['grr_ctn'];
				$upd['grr_pcs'] = $form['grr_pcs'];
				$upd['grr_amount'] = $form['grr_amount'];
				$upd['is_under_gst'] = $form['is_under_gst'];
				$upd['grr_gst_amount'] = $form['grr_gst_amount'];
				$upd['allow_multi_dept'] = $form['allow_multi_dept'];
				$upd['tax_percent'] = $form['tax_percent'];
				$upd['tax_register'] = $form['tax_register'];
				$upd['grr_tax'] = $form['grr_tax'];
				//$upd_field = array("vendor_id", "department_id", "rcv_by", "rcv_date", "transport", "grr_ctn", "grr_pcs", "grr_amount", "is_under_gst", "grr_gst_amount", "allow_multi_dept", "tax_percent", "tax_register", "grr_tax");
				
				if($config['foreign_currency']){
					$upd['currency_code'] = $form['currency_code'];
					$upd['currency_rate'] = $form['currency_rate'];
					$upd['use_po_currency'] = $form['use_po_currency'];
					$upd['currency_rate_override_by_user_id'] = $form['currency_rate_override_by_user_id'];
					//$upd_field = array_merge($upd_field, array("currency_code", "currency_rate", "use_po_currency", "currency_rate_override_by_user_id"));
				}
				
		        // save header
		        if ($form['grr_id']==0)
				{
					// call appCore to generate new ID
					$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($form['branch_id']));
					
					if(!$new_id) die("Unable to generate new ID from appCore!");
					
					$upd['id'] = $new_id;
					$upd['branch_id'] = $form['branch_id'];
					$upd['user_id'] = $form['user_id'];
					$upd['added'] = "CURRENT_TIMESTAMP";
					
					/*$upd_field = array_merge($upd_field, array("id", "branch_id", "user_id", "added"));
				    $form['added'] = 'CURRENT_TIMESTAMP';*/
					$con->sql_query("insert into grr ".mysql_insert_by_field($upd));
					$grr_id = $con->sql_nextid();
				}
				else
				{
					$con->sql_query("update grr set ".mysql_update_by_field($upd) . " where id = $form[grr_id] and branch_id = $form[branch_id]");
					$grr_id = $form['grr_id'];
				}

				// save items
		    	foreach ($form['id'] as $n=>$dummy)
				{
					$gi_id = $form['id'][$n];

					$aa = array();
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
					$aa['remark'] = $form['remark'][$n];
					$aa['tax'] = $form['tax'][$n];
					
					if($aa['po_id'] && $aa['type'] == "PO"){
						$aa['po_override_by_user_id'] = $form['po_override_by_user_id'][$n];
					}else $aa['po_override_by_user_id'] = "";
					
					if ($form['doc_no'][$n] == ''){
					    if ($gi_id>0) $con->sql_query("delete from grr_items where id = ".mi($gi_id)." and branch_id = ".mi($form['branch_id']));
					}else{
    					if($gi_id==0){
							// call appCore to generate new ID
							$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($form['branch_id']));
							
							if(!$new_id) die("Unable to generate new ID from appCore!");
							
							$aa['id'] = $new_id;
							$aa['grr_id'] = $grr_id;
							$aa['branch_id'] = $form['branch_id'];
							$aa['grn_used'] = $form['grn_used'];
							$con->sql_query("insert into grr_items ".mysql_insert_by_field($aa));
						}else{
							$con->sql_query("update grr_items set ".mysql_update_by_field($aa)." where id = ".mi($gi_id)." and branch_id = ".mi($form['branch_id']));
						}
					}

					// update po delivered status
					/*if ($form['po_id'][$n] != $form['curr_po_id'][$n])
					{
						if ($form['curr_po_id'][$n]>0) $con->sql_query("update po set delivered = 0 where id = " . mi($form['curr_po_id'][$n]) . " and branch_id = " . mi($form['po_branch_id'][$n]));
					}*/

					if (($form['doc_no'][$n] != $form['curr_po_no'][$n] || $form['type'][$n] != "PO") && $form['curr_po_no'][$n] != "")
					{
						$con->sql_query("update po set delivered = 0 where po_no = ".ms($form['curr_po_no'][$n]));
					}

					if ($form['po_id'][$n]>0){
						$con->sql_query($sql="update po set delivered = 1 where po_no = ".ms($form['doc_no'][$n]));

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

							send_pm2($to, "PO Received (".$form['doc_no'][$n].") in GRR (Branch: ".BRANCH_CODE.", GRR".sprintf("%05d",$grr_id).")", "/po.php?a=view&id={$form[po_id][$n]}&branch_id={$form[po_branch_id][$n]}");
						}
					}
		    	}
				
				//move tmp folder images to permanent folder location
				$source_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] ."/" . $_REQUEST["tmp"];				
				
				if(file_exists($source_path)){
					$destination_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/". $form["branch_id"] ."/$grr_id";
					remove_tmp_files($destination_path);
					$have_files = glob($source_path."/*.{jpg,JPG,jpeg,JPEG,png,PNG,pdf,PDF}", GLOB_BRACE);
					if(!empty($have_files)){
						
						$files = scandir($source_path);
						
						check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr");
						check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/" . $form["branch_id"]);
						check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/" . $form["branch_id"] . "/" . $grr_id);
						check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/attch/grr/" . $form["branch_id"] . "/" . $grr_id . "/pdf");
						
						foreach($files as $file) {
							if($file != "." && $file != ".."){
								if(!is_dir($source_path . "/" . $file)){
									$pdf_have_files = glob($source_path."/pdf/" . str_replace(".jpg", "", $file) . ".{pdf,PDF}", GLOB_BRACE);
									if($pdf_have_files){
										$tmp_files = scandir($source_path."/pdf");
										foreach($tmp_files as $tmp_file) {
											if($tmp_file != "." && $tmp_file != ".."){
												if (copy($source_path. "/pdf/" .$tmp_file, $destination_path. "/pdf/" .$tmp_file)) {
													log_br($sessioninfo['id'], 'GRR', $id, "GRR ID#$grr_id Add PDF ($tmp_file)");
												}
												copy($source_path. "/" .$file, $destination_path. "/" .$file);
											}
										}
									}else{
										if (copy($source_path. "/" .$file, $destination_path. "/" .$file)) {
											log_br($sessioninfo['id'], 'GRR', $id, "GRR ID#$grr_id Add Photo ($file)");
										}
									}
								}
							}
						}
					}
					remove_tmp_files($source_path);
				}
				$q3= $con->sql_query("select report_prefix from branch where id=". $form["branch_id"]);
				while($r =$con->sql_fetchassoc($q3)){
					$report_prefix = $r['report_prefix'];
				}
				$con->sql_freeresult($q3);
				log_br($sessioninfo['id'], 'GRR', $grr_id, "Saved: ".$report_prefix.sprintf("%05d",$grr_id));
		    	header("Location: /goods_receiving_record.php?t=$_REQUEST[a]&id=$grr_id&report_prefix=$report_prefix");
		    }
		    else
		    {
				if (!$form['vendor'])	$form['vendor']=$form['vendor_descrip'];
		        $smarty->assign("errm", $err);
		        $smarty->assign("form", $form);
			}
			break;
   		case 'delete':
			$form = $_REQUEST;
			$bid=$form['branch_id'];
			$grr_id=$form['grr_id'];
			$q1 = $con->sql_query("select gi.type, gi.doc_no, gi.po_id,branch.report_prefix from grr_items gi
							left join branch on gi.branch_id=branch.id
							left join grr on gi.grr_id=grr.id and gi.branch_id=grr.branch_id
							where grr.id=$grr_id and grr.branch_id=$bid");

		    if ($con->sql_numrows($q1)>0){
				$con->sql_query("update grr set active=0 where id=$grr_id and branch_id=$bid");
				
		        while($g=$con->sql_fetchrow($q1)){
					if ($g['type'] == 'PO'){
						// check whether still got grr using this po
						if(!$config['use_grn_future']) $extra_filter = "and grn.grr_item_id = gri.id";
	
						$sql = "select grn.branch_id,grn.id as grn_id
					from grr_items gri
					join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
					join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id $extra_filter
					where gri.type='PO' and gri.doc_no=".ms($g['doc_no'])." and grn.active=1 and grr.active=1
					order by grn.branch_id,grn.id limit 1";
						$con->sql_query($sql);
						$got_grn = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if(!$got_grn){
							$q2 = $con->sql_query("select * from po where po_no = ".ms($g['doc_no']));
							$po_info = $con->sql_fetchassoc($q2);
							$con->sql_freeresult($q2);
						
							// update delivered = 0 for PO
							$con->sql_query("update po set delivered = 0 where id = ".mi($po_info['id'])." and branch_id = ".mi($po_info['branch_id']));
							
							// update delivered = 0 for PO items
							$con->sql_query("update po_items set delivered = 0 where po_id = ".mi($po_info['id'])." and branch_id = ".mi($po_info['branch_id']));
						}else{
							update_po_receiving_count($g['doc_no']);
						}
					}
					$report_prefix = $g['report_prefix'];
				}
					
				$source_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"] ."/" . $_REQUEST["tmp"];				
				
				if(file_exists($source_path)){
					remove_tmp_files($source_path);
				}
		        
				log_br($sessioninfo['id'], 'GRR',$grr_id, "Deleted: ".$report_prefix.sprintf("%05d",$grr_id));
		    	header("Location: /goods_receiving_record.php?t=$_REQUEST[a]&id=$grr_id&report_prefix=$report_prefix");
			}else{
		        $err['top'][]=$LANG['GRR_NOT_FOUND'];
		        $smarty->assign('errm',$err);
   		        $smarty->assign("form", $form);
			}
			break;
		case 'do_reset':
		    $fail = grr_reset($_REQUEST['id'],$_REQUEST['branch_id']);

			if ($fail){

				$form=$_REQUEST;
			    $id = intval($form['id']);
			    $branch_id = intval($form['branch_id']);
				view_grr($id,$branch_id);

			}
		    exit;
		case 'process_do_no':
		    process_do_no();
		    exit;
		case 'ajax_load_available_do':
		    load_available_do();
		    exit;
		case 'ajax_search_doc_info':
		    ajax_search_doc_info();
		    exit;
		case 'ajax_check_gst_status':
		    ajax_check_gst_status();
		    exit;
		case 'loadCurrencyRate':
			loadCurrencyRate();
			exit;
		case 'ajax_remove_photo':			
			$f = urldecode($_REQUEST['f']);
			if(!$f)	die("Invalid Image File Path");
			
			if (@unlink($f)){				
				print "OK";
			}
			else{
				print "Delete failed";
			}
				
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

//empty tmp folder 
$tmp_dir_path = $_SERVER['DOCUMENT_ROOT']."/attch/grr/tmp/" . $sessioninfo["branch_id"] . "/" . $sessioninfo["id"];
if(file_exists($tmp_dir_path) && !isset($_REQUEST["grr_id"])){
	remove_tmp_files($tmp_dir_path, true);
}

$appCore->grnManager->load_grr_images();

list_grr();
// select valid receivers
$con->sql_query("select id, u from user left join user_privilege on user.id = user_id where privilege_code = 'GRR' and branch_id = $sessioninfo[branch_id] and user.is_arms_user=0 order by u");
$smarty->assign("rcv", $con->sql_fetchrowset());

// got turn on currency
if($config['foreign_currency']){
	// load Currency Code List
	loadGRRCurrencyCodeList($form);
}

$smarty->display("goods_receiving_record.new.tpl");
exit;

function remove_tmp_files($dir, $delete_all = false){
	if(is_dir($dir)){
		$objects = scandir($dir);
		$today = date("Y-m-d");
		
		foreach($objects as $object){
			if($object != "." && $object != ".."){
				if($delete_all){
					if(basename($object) < strtotime($today)){
						if (is_dir($dir."/".$object)){
							remove_tmp_files($dir."/".$object);
						}else{
							unlink($dir."/".$object);
						}
					}
				}else{
					if (is_dir($dir."/".$object)){
						remove_tmp_files($dir."/".$object);
					}else{
						unlink($dir."/".$object);
					}
				}
			}
		}
		
		//check empty directory then remove
		if($delete_all){
			if(count(glob($dir . "*")) === 0 ){
				rmdir($dir);
			}
		}else{
			rmdir($dir);
		}
	}
}

function load_available_po(){
	global $con, $sessioninfo, $smarty;
	$form=$_REQUEST;
	$branch_id = intval($form['branch_id']);
	if ($branch_id ==''){
		$branch_id = $sessioninfo['branch_id'];
	}
	
	$branch_filter = " and case when po.po_branch_id > 0 and po.po_branch_id is not null then po.po_branch_id = $branch_id else po.branch_id = $branch_id end";
	
	$con->sql_query($abc="select po.*, user.u as user, date_format(now(),'%d/%m/%Y') as today, branch.report_prefix, b2.report_prefix as own_report_prefix
from po 
left join user on user.id=user_id
left join branch on po.po_branch_id = branch.id
left join branch b2 on b2.id=po.branch_id
where po.approved=1 and po.active=1 and po.vendor_id=".mi($form['vendor_id'])." and delivered<>1 and department_id=".mi($form['department_id']).$branch_filter);

	while($r=$con->sql_fetchrow()){
		$cancel_date = '';
		
		if(is_array(unserialize($r['cancel_date']))){
			$r['cancel_date'] = unserialize($r['cancel_date']);
			foreach($r['cancel_date'] as $bid=>$cd){
				if($bid == $sessioninfo['branch_id'] && $cd){
					$cancel_date = $cd;
					break;
				}
				if($cd){
					if(dmy_to_time($cd) > dmy_to_time($cancel_date)){
						$cancel_date = $cd;
					}
				}
			}
		}else $cancel_date = $r['cancel_date'];

		//echo "$cancel_date===$r[today]<br>";
		//echo dmy_to_time($r[cancel_date])."===".dmy_to_time($r[today])."<br>";
		if(dmy_to_time($cancel_date)>=dmy_to_time($r['today'])){
			$r['cancel_date2'] = $cancel_date;
			$po[]=$r;
		}
	}	

	$smarty->assign("po", $po);
	//echo"<pre>";print_r($po);echo"</pre>";
	$smarty->display("goods_receiving_record.show_po.tpl");
}

function validate_data(&$form) {
	global $con, $sessioninfo, $LANG, $config;

	$err = $doc_used = $doc_date_list = array();
	//if ($form['branch_id']==0) $form['branch_id'] = $sessioninfo['branch_id'];
	//if ($form['user_id']==0) $form['user_id'] = $sessioninfo['id'];
			
	$rdate = strtotime($form['rcv_date']);
	if ($rdate === false || $rdate > time()) {
		$err['top'][] = sprintf($LANG['GRR_INVALID_RECEIVE_DATE'], $form['rcv_date']);
	}
	
	if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0) {
		$lower_limit = $config['lower_date_limit'];
		$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

		if ($rdate<$lower_date) {
			$err['top'][] = sprintf($LANG['GRR_DATE_OVER_LIMIT']);
		}
	}
	
	// validate vendor ID
	if(!$form['grn_used'] && !$form['vendor_id']) {
		$err['top'][] = sprintf($LANG['GRR_INVALID_VENDOR']);
	}

	$invalid_ibt = ibt_validation();
	if($invalid_ibt) $err['top'][] = $invalid_ibt;
	$grr_id = intval($form['grr_id']);

	$department_id = $po_count = 0;
	$doc_type_list = array();
	foreach ($form['id'] as $n=>$dummy){
		if ($form['doc_no'][$n]!=''){
			$doc_type_list[$form['type'][$n]] = 1;
			// make sure documents are not duplicated
			if (!isset($doc_used[$form['doc_no'][$n]][$form['type'][$n]][$form['gst_id'][$n]])) $doc_used[$form['doc_no'][$n]][$form['type'][$n]][$form['gst_id'][$n]] = 1;
			else $err[$n][]=sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], "this GRR");

			// question: want to separate by branch??
			$id = intval($form['id'][$n]);
			if ($form['type'][$n] != 'PO') {
				if($form['is_under_gst']) $extra_filter = "and gi.gst_id = ".mi($form['gst_id'][$n]);
				$q1 = $con->sql_query("select gi.grr_id, gi.id 
									   from grr_items gi
									   left join grr on gi.grr_id = grr.id and grr.branch_id=gi.branch_id
									   where gi.id <> ".mi($id)." and gi.grr_id <> ".mi($grr_id)." and grr.branch_id = ".mi($form['branch_id'])." and grr.vendor_id = ".mi($form['vendor_id'])." and gi.doc_no = ".ms($form['doc_no'][$n])." and gi.type = ".ms($form['type'][$n])." and grr.active=1 $extra_filter");

				if ($con->sql_numrows($q1)>0) {
				    $r = $con->sql_fetchassoc($q1);
					$err[$n][] = sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], $r['grr_id']);
				}
				$con->sql_freeresult($q1);
				
				// check if having different document date for same document no
				if(!$form['doc_date'][$n]){
					$err[$n][] = sprintf($LANG['GRR_EMPTY_DOC_DATE'], $form['type'][$n], $form['doc_no'][$n]);
				}elseif($doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] && $doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] != $form['doc_date'][$n]){
					$err[$n][] = sprintf($LANG['GRR_INVALID_DOC_DATE'], $form['type'][$n], $form['doc_no'][$n]);
				}else $doc_date_list[$form['doc_no'][$n]][$form['type'][$n]]['date'] = $form['doc_date'][$n];
				
				// check if having row amount and gst not zero percent, need to prompt user key in gst amount
				if($form['is_under_gst'] && $form['amount'][$n] > 0 && $form['gst_rate'][$n] > 0 && !$form['gst_amount'][$n]){
					$err[$n][] = sprintf($LANG['GRR_INVALID_GST_AMT'], $form['gst_code'][$n], $form['gst_rate'][$n]);
				}
			}else{ 			// make sure the PO exist
				$con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,delivered,department_id,cancel_date,po_no
                                from po
                                where approved=1 and po_no = ".ms($form['doc_no'][$n]));
				$p = $con->sql_fetchrow();
				
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
					//$form['department_list'][$p['department_id']] = 1;

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
					
					if ($form['po_override_by_user_id'][$n] == 0 && strtotime($form['rcv_date']) > dmy_to_time($cancel_date))
					    $err[$n][] = sprintf($LANG['GRR_PO_CANNOT_RECEIVE_UPON_CANCEL_DATE'], $cancel_date);

					if (!$form['allow_multi_dept'] && $form['department_id'] != $p['department_id'])
					    $err[$n][] = $LANG['GRR_PO_FROM_DIFFERENT_DEPARTMENT'];
				}
				
				if($p) $po_count++;
			}
		}
	}

	// newly enhance, to make sure user key in at least one document for inv, do or other when found is grn future
	if($config['use_grn_future']){
		if(!$doc_type_list['DO'] && !$doc_type_list['INVOICE'] && !$doc_type_list['OTHER']) $err['top'][] = $LANG['GRR_INVALID_DOCUMENT'];
		
		// if found user trying to insert 2 po and having currency differences
		if($po_count > 1 && $form['use_po_currency']) $err['top'][] = $LANG['GRR_MULTIPLE_CURRENCY_RATE'];
	}
	
	// check vendor by branch either required to have at least one po
	
	$q1 = $con->sql_query("select allow_grr_without_po from branch_vendor where vendor_id = ".mi($form['vendor_id'])." and branch_id = ".mi($sessioninfo['branch_id']));
	
	if($con->sql_numrows($q1) > 0){
		$branch_vd_info = $con->sql_fetchassoc($q1);
		
		if(!$branch_vd_info['allow_grr_without_po']){
			$has_po = false;
			foreach ($form['id'] as $n=>$dummy){
				if ($form['doc_no'][$n]!=''){
					if($form['type'][$n] == "PO"){
						$has_po = true;
						break;
					}
				}
			}
			
			if(!$has_po){
				$err['top'][] = $LANG['GRR_PO_REQUIRED'];
			}
		}
	}else{
		// check masterfile vendor either required to have at least one po
		$con->sql_query("select allow_grr_without_po from vendor where id = ".mi($form['vendor_id']));
		$allow_grr_without_po = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if(!$allow_grr_without_po){
			$has_po = false;
			foreach ($form['id'] as $n=>$dummy){
				if ($form['doc_no'][$n]!=''){
					if($form['type'][$n] == "PO"){
						$has_po = true;
						break;
					}
				}
			}
			
			if(!$has_po){
				$err['top'][] = $LANG['GRR_PO_REQUIRED'];
			}
		}
	}
	$con->sql_freeresult($q1);
	
	// it is used GRR, need to check the total amount must equal to old amount
	if($form['grn_used']){
		if($form['old_grr_amount'] != $form['grr_amount']){
			$err['top'][] = sprintf($LANG['GRR_AMOUNT_VARIANCE'], "", "", number_format($form['old_grr_amount'], 2));
		}
		
		if($form['old_grr_gst_amount'] != $form['grr_gst_amount']){
			$err['top'][] = sprintf($LANG['GRR_AMOUNT_VARIANCE'], "GST", "GST", number_format($form['old_grr_gst_amount'], 2));
		}
		
		// check if the grn has been send over to Account Verification or approved then prompt another error for user to key in invoice
		if($doc_type_list['PO'] && !$doc_type_list['INVOICE']){
			$q1 = $con->sql_query("select * from grn where grn.grr_id = ".mi($grr_id)." and grn.branch_id = ".mi($sessioninfo['branch_id'])." and grn.active = 1 and grn.authorized = 1");
			
			if($con->sql_numrows($q1) > 0){
				$grn_info = $con->sql_fetchassoc($q1);
				if($grn_info['status'] == 1){ // means the GRN has been sent for approval or approved but still doesn't have invoice
					$err['top'][] = $LANG['GRR_REQUIRES_INVOICE'];
				}elseif($grn_info['div1_approved_by'] && $grn_info['div2_approved_by'] && !$grn_info['div4_approved_by'] && $form['old_grr_amount'] != $form['grr_amount']){
					// GRN is under Account Verification, need to prompt user error since it already passed by document pending
					$err['top'][] = $LANG['GRR_REQUIRES_INVOICE'];
				}
			}
			$con->sql_freeresult($q1);
		}
	}
	
	return $err;
}


function list_grr()
{
	global $con, $smarty, $sessioninfo;

	$filter_active='and grr.active=1';

	if ($_REQUEST['find_grr']){
	    // strip "grr#####" prefix
	    if (preg_match("/^grr/i", $_REQUEST['find_grr']))
	    	$_REQUEST['find_grr'] = intval(substr($_REQUEST['find_grr'],3));

		// if search grr by id
		if (is_numeric($_REQUEST['find_grr']))
		    $findstr = "and grr.id = ".mi($_REQUEST['find_grr']);
		else
		{
		    // search documents
			$con->sql_query("select distinct(grr_id) from grr_items where branch_id=$sessioninfo[branch_id] and doc_no like " . ms(replace_special_char($_REQUEST['find_grr'])));

			// return if no match
			if (!$con->sql_numrows()) return;
			$idlist = array();
			while($r=$con->sql_fetchrow())
			{
			    $idlist[] = $r[0];
			}
		    $findstr = "and grr.id in (".join(",",$idlist).")";
		}
		
		$filter_active='';
	}else{
		$filter_date = " and grr.rcv_date>=".ms(date("Y-m-d", strtotime("-3 month")));
	}


	// show current grr
	// set GRR status =1 after GRN is created
	$con->sql_query("select grr.*, grr_items.*, grr.id as grr_id, grr.rcv_date, vendor.description as vendor, user.u,
					user2.u as rcv_u, category.description as department, vendor.code as vendor_code,
					if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id, grn.id as grn_id,branch.report_prefix 
					from grr_items
					left join branch on grr_items.branch_id = branch.id
					left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id)
					left join user on grr.user_id = user.id
					left join user user2 on grr.rcv_by = user2.id
					left join vendor on grr.vendor_id = vendor.id
					left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
					left join category on grr.department_id = category.id
					left join grn on grn.grr_id = grr.id and grn.branch_id = grr.branch_id and grn.active=1
					where grr.branch_id=$sessioninfo[branch_id] $filter_active $findstr $filter_date
					order by grr.last_update desc, grr_items.id
					limit 100") or die(mysql_error());

	$smarty->assign("grr", $con->sql_fetchrowset());
	$con->sql_freeresult();
}

// print GRR 
function do_print()
{
	global $con, $smarty, $sessioninfo, $config;

	$id = intval($_REQUEST['id']);
	$branch_id = intval($_REQUEST['branch_id']);


	// print GRR header
	if ($_REQUEST['print_worksheet'] != '')
		$wsc = "worksheet_print_counter = worksheet_print_counter + 1,";
	else
		$wsc = "";
	$con->sql_query("update grr set $wsc print_counter = print_counter + 1, last_update = last_update where id = $id and branch_id = $branch_id");
	$con->sql_query("select * from branch where id = $branch_id");
	$smarty->assign("branch", $con->sql_fetchrow());

	$res1 = $con->sql_query("select grr.*, grr_items.*, grr.id as grr_id, grr.rcv_date, vendor.description as vendor, branch.report_prefix ,
							user.fullname as keyin_fullname, user2.fullname as rcv_fullname, 
							category.description as department, vendor.code as vendor_code,
							if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
							from grr_items
							left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id)
							left join branch on grr_items.branch_id = branch.id
							left join user on grr.user_id = user.id
							left join user user2 on grr.rcv_by = user2.id
							left join vendor on grr.vendor_id = vendor.id
							left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
							left join category on grr.department_id = category.id
							where grr.id = $id and grr.branch_id=$branch_id and grr.active=1
							order by grr.added desc, grr_items.id") or die(mysql_error());
	while($r = $con->sql_fetchassoc($res1)) $grr_items[] = $r;
    $con->sql_freeresult($res1);
    //$grr_items = $con->sql_fetchrowset();
 
	// load GST summary info
	if($grr_items[0]['is_under_gst']){
		load_gst_summary($grr_items);
	}
	
	if ($_REQUEST['print_grr'] != '')
	{
		$item_per_page= $config['grr_print_per_page'] ? $config['grr_print_per_page']:10;
		$item_per_lastpage = $config['grr_print_per_last_page']>0 ? $config['grr_print_per_last_page']:10;

		$totalpage = 1 + ceil((count($grr_items)-$item_per_lastpage)/$item_per_page);

	    for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
	    	if($page == $totalpage) $smarty->assign("is_last_page", 1);
	        $smarty->assign("page", "Page $page of $totalpage");
	        $smarty->assign("start_counter", $i);
	        $smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
	        $items = array_slice($grr_items,$i,$item_per_page);
	        $smarty->assign("items", $items);
	        if($config['grr_alt_print_template'])   $smarty->display($config['grr_alt_print_template']);
			else $smarty->display("goods_receiving_record.print.tpl");
			$smarty->assign("skip_header",1);
		}
	}

	if ($_REQUEST['print_worksheet'] != '')
	{
        $po_found = 0;
        foreach($grr_items as $r) {
            if ($r['type']=='PO')
			{
				print_worksheet($r);
                $po_found = 1;
			}
        }
//		$con->sql_rowseek(0, $res1);
//		while ($r=$con->sql_fetchassoc($res1))
//		{
//			if ($r['type']=='PO')
//			{
//				print_worksheet($r);
//                $po_found = 1;
//			}
//		}
        if(!$po_found) {
            print "<script>alert('No item to print.')</script>";
            die("<script>window.close()</script>");
        }
		$smarty->assign("skip_header",1);
	}
		
}

// print each worksheet from PO
function print_worksheet($grr){

	global $con, $smarty, $sessioninfo, $config;

	$q1=$con->sql_query("select po.*, category.grn_po_qty, category.description as dept from po left join category on po.department_id = category.id where po_no = ".ms($grr['doc_no']));
	$po = $con->sql_fetchassoc($q1);
    
    $dd = @unserialize($po['delivery_date']);
    $cd = @unserialize($po['cancel_date']);
    
    if($dd !== false && $cd !== false) {
        $delivery_date = unserialize($po['delivery_date']);
        $cancel_date = unserialize($po['cancel_date']);
        
        $po['delivery_date'] = $delivery_date[$grr['branch_id']];
        $po['cancel_date'] = $cancel_date[$grr['branch_id']];    
    }
    
//	$q2=$con->sql_query("select po_items.*, sum(po_items.qty) as qty, sum(po_items.qty_loose) as qty_loose, sum(po_items.foc) as foc, sum(po_items.foc_loose) as foc_loose, uom.code as order_uom, sku_items.sku_item_code, sku_items.description, if(sku_items_price.price>0, sku_items_price.price, sku_items.selling_price) as master_selling_price, sku_items.link_code, if(sku_items_price.price>0, sku_items_price.price, sku_items.selling_price) as curr_sell, po_items.selling_price as po_sell, uom.fraction as order_uom_fraction,
//                            (select grn_items.cost/u2.fraction
//                            from grn_items left join uom u2 on grn_items.uom_id=u2.id
//                            left join grn on grn_id = grn.id and grn.branch_id = grn_items.branch_id
//                            left join grr on grr_id = grr.id and grr.branch_id = grn.branch_id
//                            where grn_items.sku_item_id =po_items.sku_item_id and po_items.branch_id = grn_items.branch_id
//                            order by rcv_date desc limit 1 ) as grn_cost,
//                        sku_items.cost_price as sku_cost, sku_items.artno, sku_items.mcode
//                        from po_items
//                        left join sku_items on po_items.sku_item_id = sku_items.id
//                        left join sku_items_price on po_items.sku_item_id = sku_items_price.sku_item_id and sku_items_price.branch_id=".mi($grr['branch_id'])."
//                        left join uom on order_uom_id = uom.id
//                        where po_items.po_id=$po[id] and po_items.branch_id=$po[branch_id] and (po_items.qty>0 or po_items.qty_loose>0 or po_items.foc>0 or po_items.foc_loose>0)
//                        group by po_items.id, po_items.branch_id
//                        order by min(po_items.id)");

    $q2=$con->sql_query("select po_items.*, po.po_branch_id, sku_items.sku_item_code, sku_items.cost_price as sku_cost, sku_items.artno, sku_items.mcode, sku_items.description, if(sku_items_price.price>0, sku_items_price.price, sku_items.selling_price) as master_selling_price, sku_items.link_code, if(sku_items_price.price>0, sku_items_price.price, sku_items.selling_price) as curr_sell, po_items.selling_price as po_sell,
                            (select grn_items.cost/u2.fraction
                            from grn_items left join uom u2 on grn_items.uom_id=u2.id
                            left join grn on grn_id = grn.id and grn.branch_id = grn_items.branch_id
                            left join grr on grr_id = grr.id and grr.branch_id = grn.branch_id
                            where grn_items.sku_item_id =po_items.sku_item_id and po_items.branch_id = grn_items.branch_id
                            order by rcv_date desc limit 1 ) as grn_cost,
                        uom.code as order_uom, uom.fraction as order_uom_fraction
                        from po_items
                        left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
                        left join sku_items on po_items.sku_item_id = sku_items.id
                        left join sku_items_price on po_items.sku_item_id = sku_items_price.sku_item_id and sku_items_price.branch_id=".mi($grr['branch_id'])."
                        left join uom on order_uom_id = uom.id
                        where po_items.po_id=".mi($po['id'])." and po_items.branch_id=".mi($po['branch_id'])."
						order by po_items.branch_id, po_items.po_id, po_items.id");
    
	while ($r2=$con->sql_fetchassoc($q2)){
		// sum up po qty by items
        if(mi($grr['branch_id']) == 1 && mi($r2['po_branch_id']) == 0) {
            $qty_alloc = unserialize($r2['qty_allocation']);
            $qty_loose_alloc = unserialize($r2['qty_loose_allocation']);
            $r2['qty'] = ($qty_alloc[$grr['branch_id']] > 0 ? $qty_alloc[$grr['branch_id']] : 0);
            $r2['qty_loose'] = ($qty_loose_alloc[$grr['branch_id']] > 0 ? $qty_loose_alloc[$grr['branch_id']] : 0);
            
            $foc_alloc = unserialize($r2['foc_allocation']);
            $foc_loose_alloc = unserialize($r2['foc_loose_allocation']);
            $r2['foc'] = ($foc_alloc[$grr['branch_id']] >0?$foc_alloc[$grr['branch_id']]:0);
            $r2['foc_loose'] = ($foc_loose_alloc[$grr['branch_id']] >0?$foc_loose_alloc[$grr['branch_id']]:0);
            
            $selling_price_alloc = unserialize($r2['selling_price_allocation']);
            $gst_selling_price_alloc = unserialize($r2['gst_selling_price_allocation']);
            $r2['selling_price'] = $r2['po_sell'] = $selling_price_alloc[$grr['branch_id']];
            $r2['gst_selling_price'] = $gst_selling_price_alloc[$grr['branch_id']];
        }
        
        $curr_qty = ($r2['qty'] * $r2['order_uom_fraction'])+$r2['qty_loose']+($r2['foc'] * $r2['order_uom_fraction'])+$r2['foc_loose'];
		if($po_items[$r2['sku_item_id']]){
			$r2['qty'] = $po_items[$r2['sku_item_id']]['qty'] + ($r2['qty'] * $r2['order_uom_fraction']);
			$r2['qty_loose'] = $po_items[$r2['sku_item_id']]['qty_loose'] + $r2['qty_loose'];
			$r2['foc'] = $po_items[$r2['sku_item_id']]['foc'] + ($r2['foc'] * $r2['order_uom_fraction']);
			$r2['foc_loose'] = $po_items[$r2['sku_item_id']]['foc_loose'] + $r2['foc_loose'];
		}

		if($r2['remark']){
			if($po_items[$r2['sku_item_id']]['remark']) $r2['remark'] = $po_items[$r2['sku_item_id']]['remark'].",".$r2['remark'];
		}else $r2['remark'] = $po_items[$r2['sku_item_id']]['remark'];

		if($r2['remark2']){
			if($po_items[$r2['sku_item_id']]['remark2']) $r2['remark2'] = $po_items[$r2['sku_item_id']]['remark2'].",".$r2['remark2'];
		}else $r2['remark2'] = $po_items[$r2['sku_item_id']]['remark2'];
		
		if($r2['is_foc']){
			$po['is_foc']=1;	
		}
	
		if((mf($r2['foc'])||mf($r2['foc_loose']))&&$config['grr_worksheet_show_additional_foc_row']){
			$po['show_foc'] = 1;
			$r2['is_foc_row'] = 1;
		}
        
        $prms = array();
		$prms['branch_id'] = $grr['branch_id'];
		$prms['date'] = $grr['rcv_date'];
		$branch_is_under_gst = check_gst_status($prms);
        
        $incl_tax = get_sku_gst("inclusive_tax", $r2['sku_item_id']);
        if($incl_tax == 'yes' && $branch_is_under_gst) {
            $r2['po_sell'] = $r2['gst_selling_price'];
            $r2['include_tax'] = 1;
        }else   $r2['include_tax'] = 0;
        
		$r2['ttl_qty'] = $po_items[$r2['sku_item_id']]['ttl_qty']+$curr_qty;
		$r2['ttl_cost'] = $po_items[$r2['sku_item_id']]['ttl_cost']+($curr_qty*$r2['order_price']/$r2['order_uom_fraction']);	// here look like got problem???
		$po_items[$r2['sku_item_id']]=$r2;
	}
	if($sessioninfo['u']=='admin'){
		//print_r($po_items);
	}
	
	$smarty->assign("grr", $grr);
	$smarty->assign("po", $po);
 
	$item_per_page= $config['grr_worksheet_per_page']? $config['grr_worksheet_per_page']:10;
	$item_per_lastpage = $config['grr_worksheet_per_last_page']>0 ? $config['grr_worksheet_per_last_page']:10;

	$totalpage = 1 + ceil((count($po_items)-$item_per_lastpage)/$item_per_page);

    for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
    	if($page == $totalpage) $smarty->assign("is_lastpage", 1);
    	else $smarty->assign("is_lastpage", 0);
        $smarty->assign("page", "Page $page of $totalpage");
        $smarty->assign("start_counter", $i);
        $smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
        $items = array_slice($po_items,$i,$item_per_page);
        $smarty->assign("po_items", $items);
        if($config['grr_worksheet_alt_print_template'])   $smarty->display($config['grr_worksheet_alt_print_template']);
		else $smarty->display("goods_receiving_record.print.worksheet.tpl");
		$smarty->assign("skip_header",1);
	}
}

function search_pp_pono($original_docno, &$ret){
	global $con, $smarty, $sessioninfo, $reset_doc_no ;

	if (preg_match("/^([A-Z]+)(\d+)\(PP\)$/", $original_docno, $matches)){
		$pp_repor_prefix=$matches[1];
		$pp_po_id=$matches[2];
		
		if($pp_repor_prefix=='HQ'){
			$q1=$con->sql_query("select po_no from po where hq_po_id=".mi($pp_po_id)."  and po_branch_id=".mi($sessioninfo['branch_id']));
			$r1 = $con->sql_fetchrow($q1);		
		}
		else{
			$q0=$con->sql_query("select id from branch where report_prefix=".ms($pp_repor_prefix));
			$r0 = $con->sql_fetchrow($q0);
			$pp_branch_id=$r0['id'];
			
			$q1=$con->sql_query("select po_no from po where branch_id=".mi($pp_branch_id)." and id=".mi($pp_po_id));
			$r1 = $con->sql_fetchrow($q1);		
		}		

		if($r1){
			$reset_doc_no=$r1['po_no'];
		}			
		
	}	
	$con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,delivered,department_id,cancel_date,po_no,currency_code,currency_rate from po where approved=1 and po_no =".ms($reset_doc_no));		
	$ret = $con->sql_fetchrow();
	
	return $reset_doc_no;
}

// update PO delivered status
/*function update_po_receiving_count($po_no){
	global $con, $config;

	// reset all number to zero first...
	$con->sql_query("update po_items left join po
	on po_items.po_id = po.id and po_items.branch_id = po.branch_id
	set po_items.delivered = 0 where po_no=".ms($po_no));

	if(!$config['use_grn_future']) $extra_filter = "and grn.grr_item_id = gri.id";
	
	$sql = "select grn_items.*, uom.fraction 
		from grr_items gri
		join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
		join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id $extra_filter
		join grn_items on grn_items.branch_id=grn.branch_id and grn_items.grn_id=grn.id
		left join uom on grn_items.uom_id = uom.id
		where gri.type='PO' and gri.doc_no=".ms($po_no)." and grn.active=1 and grr.active=1 and grn.status=1
		order by grn.branch_id,grn.id limit 1";
					
	$q1 = $con->sql_query($sql);
	$rcvq = array();
	while($r=$con->sql_fetchrow($q1)){
		if ($r['po_item_id']=='')
			continue;

		if ($bid==0)
			$bid = $r['branch_id'];

		if ($r['acc_pcs']>0 || $r['acc_ctn']>0)
			$rcvq[$r['po_item_id']] += $r['acc_pcs'] + $r['fraction']*$r['acc_ctn'];
		else
			$rcvq[$r['po_item_id']] += $r['pcs'] + $r['fraction']*$r['ctn'];

		//print "<li> $r[po_item_id] = ".$rcvq[$r['po_item_id']];
		//print_r($r);
	}
	$con->sql_freeresult($q1);

	if (!$rcvq) return;
	foreach ($rcvq as $k=>$v){
		$con->sql_query("update po_items set delivered = $v where id=$k and branch_id=$bid");
	}
}*/


function view_grr($id,$branch_id){
	global $con,$config,$smarty,$LANG,$appCore;
	
	$items = array();
	$rs1 = $con->sql_query("select grr.*, grr_items.*, grr.id as grr_id, grr.rcv_date, vendor.description as vendor, user.u as keyin_u, user2.u as rcv_u, category.description as department,branch.report_prefix
	from grr_items left join branch on grr_items.branch_id=branch.id
	left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id) left join user on grr.user_id = user.id left join user user2 on grr.rcv_by = user2.id left join vendor on grr.vendor_id = vendor.id left join category on grr.department_id = category.id where grr.id = $id and grr.branch_id=$branch_id order by grr_items.id");
	while($r = $con->sql_fetchrow($rs1))
	{
		if ($r['type'] == 'PO')
		{
			// get_po();
			//$po_detail[$r['doc_no']] = get_po($r['doc_no']);
			$con->sql_query("select id, branch_id from po where po_no = ".ms($r['doc_no']));
			$po=$con->sql_fetchrow();
			$r['po_id'] = $po['id'];
			$r['po_branch_id'] = $po['branch_id'];
		}
		$items[] = $r;
	}
	
	$q2 = $con->sql_query("select grn.id, grn.branch_id from grn where grn.grr_id = ".mi($id)." and grn.branch_id = ".mi($branch_id)." and grn.active = 1");
	
	while($r1 = $con->sql_fetchrow($q2)){
		$grn_list[] = $r1;
	}

	$items[0]['grn_list'] = $grn_list;
	$appCore->grnManager->load_grr_images();
	
	$smarty->assign("PAGE_TITLE", "GRR Detail");
	$smarty->assign("items", $items);
	$smarty->display("goods_receiving_record.view.tpl");

}

function grr_reset($grr_id,$branch_id){
	global $con;
    //$invalid = reset_grr($grr_id,$branch_id,'GRR',$_REQUEST['rcv_date']);
    $invalid = process_reset_grr_grn($branch_id, $grr_id, 0, "GRR");
     
    if ($invalid) return true;
    else{
		$q1 = $con->sql_query("Select report_prefix from branch where id=".$branch_id);
		$r=$con->sql_fetchassoc($q1);
		$report_prefix = $r['report_prefix'];
		$con->sql_freeresult($q1);
        header("Location: /goods_receiving_record.php?t=reset&id=$grr_id&report_prefix=$report_prefix");
		exit;
    }
}

function process_do_no(){
  global $con, $sessioninfo, $config, $appCore;
  
  if(!$config['grr_process_do']){
    header("Location: /goods_receiving_record.php");
	 exit;
  }
  
  $do_no = trim($_REQUEST['do_no']);
  
  // check do exists or not
  $con->sql_query("select * from do where do_no=".ms($do_no)." and active=1 and checkout=1 and approved=1 and do_branch_id=".mi($sessioninfo['branch_id']));
  
  $form = $con->sql_fetchrow();
  if(!$form){
      header("Location: $_SERVER[PHP_SELF]?err_msg=Invalid Do No '$do_no'");
      exit;
  }
  
  // check in GRR see already checkout or not
  $con->sql_query("select gi.* from grr_items gi left join grr on grr.id=gi.grr_id and grr.branch_id=gi.branch_id where type='DO' and doc_no=".ms($do_no)." and active=1");
  
  $grr_items = $con->sql_fetchrow();
  if($grr_items){
      header("Location: $_SERVER[PHP_SELF]?err_msg='$do_no' already created GRR");
      exit;
  }
  
  $grr = array();
  $grr_items = array();
  $form['checkout_by']=$sessioninfo['id'];
	  $form['checkout']=1;
	  
    $r1 = $form;
    
		$r1['checkout_info'] = unserialize($r1['checkout_info']);
		
		if($r1['do_branch_id']>0 && !$r1['open_info'] && $r1['do_type']!='credit_sales'){
			$q2=$con->sql_query("select do.do_date, sku_items.artno, sku_items.mcode, sku.sku_code, sku_items.id as id, do_items.user_id,do_items.ctn, do_items.pcs, do_items.id as do_item_id , sku_items.description as description, uom.id as uom_id, uom.fraction as uom_fraction, do_items.cost_price as cost_price, sku_items.sku_item_code, uom.code as uom_code, c2.description as dept_name, c2.id as dept_id, do_items.sku_item_id as sku_item_id, do_items.artno_mcode as artno_mcode,do.do_type,do.total_rcv,do_items.rcv_pcs
from do_items
left join do on do_id = do.id and do_items.branch_id = do.branch_id
left join sku_items	on do_items.sku_item_id = sku_items.id
left join sku on sku_items.sku_id = sku.id
left join uom on do_items.uom_id = uom.id
left join category c1 on sku.category_id=c1.id
left join category c2 on c1.department_id = c2.id
where do_items.do_id=$r1[id] and do_items.branch_id=$r1[branch_id] group by dept_name, do_items.id order by dept_name");
    
			while ($r2 = $con->sql_fetchrow($q2)){
				$temp[$r2['dept_id']][]=$r2;
			}		

			foreach($temp as $k=>$v){			
				foreach($temp[$k] as $k1=>$v1){
					//echo "$v1[sku_code]<br>";
					if($v1['do_type']=='transfer'&&$config['do_use_rcv_pcs']){
                        $total_pcs[$k]+=$v1['rcv_pcs'];
					}else{
                        $total_ctn[$k]+=$v1['ctn'];
						$total_pcs[$k]+=$v1['pcs'];
					}
					
					$total_amt[$k]+=(($v1['ctn']*$v1['cost_price'])+($v1['cost_price']/$v1['uom_fraction']*$v1['pcs']));
				}
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($r1['do_branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grr['id'] = $new_id;
				$grr['branch_id']=$r1['do_branch_id'];
				$grr['user_id']=$r1['user_id'];
				$grr['rcv_by']=$r1['checkout_by'];
				$grr['rcv_date']=$r1['do_date'];
				$grr['grr_ctn']=$total_ctn[$k];	
				$grr['grr_amount']=$total_amt[$k];
				$grr['added']='CURRENT_TIMESTAMP()';
				$grr['grr_pcs']=$total_pcs[$k];
				$grr['department_id']=$k;
				$grr['status']=1;
				$grr['transport']=$r1['checkout_info']['lorry_no'];
				
				$con->sql_query("insert into grr " . mysql_insert_by_field($grr, array('id', 'branch_id', 'user_id', 'rcv_by', 'rcv_date', 'grr_ctn','grr_amount', 'added', 'grr_pcs', 'status','department_id') ));
				$grr_id = $con->sql_nextid();
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($r1['do_branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grr_items['id']=$new_id;
				$grr_items['grr_id']=$grr_id;
				$grr_items['branch_id']=$r1['do_branch_id'];
				$grr_items['doc_no']=$r1['do_no'];
				$grr_items['type']='DO';
				$grr_items['ctn']=$total_ctn[$k];
				$grr_items['amount']=$total_amt[$k];
				$grr_items['remark']=$r1['remark'];
				$grr_items['pcs']=$total_pcs[$k];				
				$grr_items['grn_used']=1;
				
				//auto insert into grr_items							
				$con->sql_query("insert into grr_items " . mysql_insert_by_field($grr_items, array('id', 'branch_id', 'grr_id', 'doc_no', 'type', 'ctn','amount', 'remark', 'pcs', 'grn_used') ));				
				$grr_items_id = $con->sql_nextid();
				
				// call appCore to generate new ID
				$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($r1['do_branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grn['id'] = $new_id;
				$grn['branch_id']=$r1['do_branch_id'];
				$grn['user_id']=$r1['checkout_by'];
				$grn['grr_id']=$grr_id;
				$grn['grr_item_id']=$grr_items_id;								
				$grn['amount']=$total_amt[$k];
				//$grn['status']=1;
				//$grn['approved']=1;
				$grn['added']='CURRENT_TIMESTAMP()';
				$grn['final_amount']=$total_amt[$k];
				$grn['department_id']=$k;
	
				//auto insert into grn							
				$con->sql_query("insert into grn " . mysql_insert_by_field($grn, array('id', 'branch_id', 'user_id', 'grr_id', 'grr_item_id', 'amount','status', 'approved', 'added', 'final_amount','department_id') ));				
				$grn_id = $con->sql_nextid();
				
				$grn_items['branch_id']=$r1['do_branch_id'];
				$grn_items['grn_id']=$grn_id;
																			
				foreach($temp[$k] as $k1=>$v1){
					// call appCore to generate new ID
					$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($r1['do_branch_id']));
					
					if(!$new_id) die("Unable to generate new ID from appCore!");
					
					$grn_items['id']=$new_id;
					$grn_items['sku_item_id']=$v1['sku_item_id'];
					$grn_items['artno_mcode']=$v1['artno_mcode'];
					$grn_items['uom_id']=$v1['uom_id'];
					$grn_items['cost']=$v1['cost_price'];
					
					if($v1['do_type']=='transfer'&&$config['do_use_rcv_pcs']){
						$grn_items['pcs']=$v1['rcv_pcs'];
					}else{
                        $grn_items['ctn']=$v1['ctn'];
						$grn_items['pcs']=$v1['pcs'];
					}
					
					$q3=$con->sql_query("select if(sp.price is null, selling_price, sp.price) as selling from sku_items left join sku on sku.id=sku_items.sku_id left join sku_items_price sp on sku_items.id = sp.sku_item_id and sp.branch_id=$grn_items[branch_id] where sku_items.id=$v1[sku_item_id]");
					$r3 = $con->sql_fetchrow($q3);
					
					$grn_items['selling_uom_id']=1;
					$grn_items['selling_price']=$r3['selling'];
	
					//auto insert into grn							
					$con->sql_query("insert into grn_items " . mysql_insert_by_field($grn_items, array('id', 'branch_id', 'grn_id', 'sku_item_id', 'artno_mcode', 'uom_id','cost', 'ctn', 'pcs','selling_uom_id','selling_price')));												
	
				}
				// update total_selling
			    $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as sell
from grn_items
left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
where grn_id=$grn_id and branch_id=$grn[branch_id]") or die(mysql_error());
			    $t = $con->sql_fetchrow();
			    $t[0] = doubleval($t[0]); 
			
			    $con->sql_query("update grn set last_update=last_update,total_selling=$t[0] where id=$grn_id and branch_id=$grn[branch_id]");				
	
			}				
		
		}else{
      header("Location: $_SERVER[PHP_SELF]?err_msg=Do No '$do_no' cannot be process.");
      exit;
    }
		
	//update sku_items_cost for do_items
	//$con->sql_query("update sku_items_cost set changed=1 where branch_id in ($form[branch_id], $form[do_branch_id]) and sku_item_id in (select sku_item_id from do_items left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id where do_items.do_id=$form[id] and do_items.branch_id=$form[branch_id] and do.checkout and do.approved and do.status<2)");
	
	// update sku items cost
	$sid_list = array();
	$con->sql_query("select distinct sku_item_id 
	from do_items
	where branch_id=$form[branch_id] and do_id=$form[id]");
	while($r = $con->sql_fetchassoc()){
		$sid_list[] = mi($r['sku_item_id']);
	}
	$con->sql_freeresult();
	
	if($sid_list){
		$con->sql_query("update sku_items_cost set changed=1 where branch_id in ($form[branch_id], $form[do_branch_id]) and sku_item_id in (".join(',', $sid_list).")");
	}
		
	log_br($sessioninfo['id'], 'GRR', $do_no, "Process DO : DO No $do_no");
				
	header("Location: $_SERVER[PHP_SELF]?msg='$do_no' successfully created.");
}

function ibt_validation(){
	global $con, $LANG;

	$form = $_REQUEST;
	$is_ibt = 0;
	$non_ibt = 0;

	foreach ($form['id'] as $n=>$dummy){
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

			if($con->sql_numrows($sql) > 0) $is_ibt = 1;
			else $non_ibt = 1;
		}
		if($is_ibt && $non_ibt) break; // stop the loop and rdy to display error msg
	}

	// found if having both IBT and non IBT in one GRR then display error msg
	if($is_ibt && $non_ibt) $err = $LANG['GRR_IBT_ERROR'];
	return $err;
}

function load_available_do(){
	global $con, $sessioninfo, $smarty;
	$form=$_REQUEST;
	$branch_id = intval($form['branch_id']);
	if ($branch_id ==''){
		$branch_id = $sessioninfo['branch_id'];
	}
	
	$branch_filter = " and case when do.do_branch_id > 0 and do.do_branch_id is not null then do.do_branch_id = $branch_id else do.branch_id = $branch_id end";
	
	$q1 = $con->sql_query("select do.*, user.u as user, date_format(now(),'%d/%m/%Y') as today
                           from do 
                           left join user on user.id=do.user_id
                           where do.approved=1 and do.active=1 and do.checkout=1 and do.do_type = 'transfer'".$branch_filter);

	while($r = $con->sql_fetchassoc($q1)){
        $q2 = $con->sql_query("select *
                               from grr
                               left join grr_items gi on gi.grr_id = grr.id and gi.branch_id = grr.branch_id
                               where grr.active = 1 and gi.doc_no = ".ms($r['do_no'])."
                               and gi.type = 'DO' and gi.branch_id = ".mi($branch_id));
        
        if($con->sql_numrows($q2) > 0) continue; // means this DO has been received before and no longer available
        $con->sql_freeresult($q2);
         
        $do[]=$r;
    }
    $con->sql_freeresult($q1);

	$smarty->assign("do", $do);
	$smarty->display("goods_receiving_record.show_do.tpl");
}

function ajax_search_doc_info(){
	global $con, $sessioninfo, $LANG, $appCore;
	
	$doc_no = $_REQUEST['doc_no'];
	$doc_type = $_REQUEST['doc_type'];
	
	if($doc_type == "PO"){
		$q1 = $con->sql_query("select po.department_id, po.vendor_id, concat(v.code, ' - ', v.description) as vd_desc,
                              po_date,	po_amount, po_no, po.cancel_date, po.currency_code, po.currency_rate
							   from po 
							   left join vendor v on v.id = po.vendor_id
							   where po.po_no = ".ms($doc_no));
	}else{
		$q1 = $con->sql_query("select do.id, do.branch_id,do.dept_id as department_id,ifnull(v2.id,v.id) as vendor_id, if(v2.id,concat(v2.code, ' - ', v2.description),concat(v.code, ' - ', v.description)) as vd_desc,
                              do_no,do_date from do
                              left join branch b on b.id=do.branch_id
                              left join vendor v on v.code = b.code
                              left join vendor v2 on v2.internal_code = b.code
                              where do_no = ".ms($doc_no)." and do_type='transfer'");
	}
	
	$tmp = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if(!$tmp){
		$tmp['no_data'] = 1;
	}

    if(!$tmp['vendor_id']){
        $tmp['no_vendor'] = 1;
    }else{
		// Get Tax Register
		$result = $appCore->vendorManager->getTaxRegister($tmp['vendor_id']);
		if($result['tax_register']){
			$tmp['tax_register'] = $result['tax_register'];
			$tmp['tax_percent'] = $result['tax_percent'];
		}
	}

    if($tmp && $doc_type=="DO"){
        $q1 = $con->sql_query("select * from do_items
                              where do_id=".mi($tmp['id'])." and branch_id=".mi($tmp['branch_id']));

        $tmp['items']=array();
        while($r = $con->sql_fetchassoc($q1)){
            $gst_info = get_sku_gst("input_tax", $r['sku_item_id']);

            if(!isset($tmp['items'][$gst_info['id']])){
                $tmp['items'][$gst_info['id']]=array("gst_id"=>$gst_info['id'],"do_no"=>$tmp['do_no'], "do_date"=>$tmp['do_date'], "ctn"=>0, "pcs"=>0, "amount"=>0, "gst"=>0);
            }

            $tmp['items'][$gst_info['id']]['ctn']+=$r['ctn'];
            $tmp['items'][$gst_info['id']]['pcs']+=$r['pcs'];
            $tmp['items'][$gst_info['id']]['amount']+=$r['inv_line_amt2'];
            $tmp['items'][$gst_info['id']]['gst']+=$r['inv_line_gst_amt2'];
        }

        $con->sql_freeresult($q1);
    }
	if($tmp && $doc_type=="PO"){
		if(is_array(unserialize($tmp['cancel_date']))){
			$tmp['cancel_date'] = unserialize($tmp['cancel_date']);
			foreach($tmp['cancel_date'] as $bid=>$cd){
				if(!$cd) continue;
				$cancel_date = $cd;
				break;
			}
		}else $cancel_date = $tmp['cancel_date'];
		
		if (strtotime($_REQUEST['rcv_date']) > dmy_to_time($cancel_date)){
			$tmp["po_cancelled"] = 1;
			$tmp["po_cancelled_msg"] = sprintf($LANG['GRR_OVERRIDE_PO_CANCEL_DATE'], $cancel_date);
		}
	}
	
	$ret[] = $tmp;
	
	print json_encode($ret);
}

function ajax_check_gst_status(){
	global $con, $smarty;
	
	$form = $_REQUEST;
	$is_under_gst = 0;

	$prms = array();
	if($_REQUEST['date']) $prms['date'] = $form['date'];
	else $prms['date'] = date("Y-m-d");
	$prms['vendor_id'] = $form['id'];
	$is_under_gst = check_gst_status($prms);
	
	$ret = array();
	$ret['is_under_gst'] = mi($is_under_gst);
	
	print json_encode($ret);
}

function load_gst_summary($items=array()){
	global $con, $smarty;
	
	if(!$items) return;
	
	// load gst information
	$q1 = $con->sql_query("select * from gst");
	
	$gst_list = array();
	while($r = $con->sql_fetchassoc($q1)){
		$gst_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	foreach($items as $dummy=>$r){
		if(!$r['gst_id']) continue;
		
		$item_amt = round($r['amount']-$r['gst_amount'], 2);
		$item_gst_amt = round($r['gst_amount'], 2);
		
		$gst_summary_list[$r['gst_code']]['total_amount'] += $item_amt;
		$gst_summary_list[$r['gst_code']]['total_gst_amount'] += $item_gst_amt;
		$gst_summary_list[$r['gst_code']]['gst_rate'] = $r['gst_rate'];
	}
	$smarty->assign("gst_summary_list", $gst_summary_list);
}

function process_po_no(){
	global $LANG, $config, $con;
	
	$form = $_REQUEST;
	
	$new_doc_no = search_pp_pono($form['doc_no'], $po_info);
	
	// if found matching with xxxxx(PP)
	$ret = array();
	if(preg_match("/^([A-Z]+)(\d+)\(PP\)$/", $form['doc_no'])){
		if(!$new_doc_no){ // couldn't find any PO, show error msg
			$msg=sprintf($LANG['GRR_PO_NOT_FOUND'], $form['doc_no']);
			$ret['err'] = $msg;
		}else{ // return the actual doc no if found it
			$ret['new_doc_no'] = $new_doc_no;
		}
	}
	
	// if po info not found and system is under foreign currency, need to select currency info from po
	if(!$ret['err']){
		if(!$po_info){
			$q1 = $con->sql_query("select * from po where po_no = ".ms($form['doc_no']));
			$po_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		}
		
		// cancel date selection
		if(is_array(unserialize($po_info['cancel_date']))){
			$po_info['cancel_date'] = unserialize($po_info['cancel_date']);
			foreach($po_info['cancel_date'] as $bid=>$cd){
				if(!$cd) continue;
				$cancel_date = $cd;
				break;
			}
		}else $cancel_date = $po_info['cancel_date'];
		
		if (strtotime($_REQUEST['rcv_date']) > dmy_to_time($cancel_date)){
			$ret['po_cancelled'] = 1;
			$ret['po_cancelled_msg'] = sprintf($LANG['GRR_OVERRIDE_PO_CANCEL_DATE'], $cancel_date);
		}
		
		if($config['foreign_currency'] && $po_info['currency_code']){
			$ret['currency_code'] = $po_info['currency_code'];
			$ret['currency_rate'] = $po_info['currency_rate'];
		}elseif(!$po_info){
			$msg=sprintf($LANG['GRR_PO_NOT_FOUND'], $form['doc_no']);
			$ret['err'] = $msg;
		}
	}
	
	// if called this function from pending document under GRN, need to check GRR vs document currency
	if($config['foreign_currency'] && $po_info && $form['from_grn'] && $form['grr_currency_code'] != $po_info['currency_code']){
		if(!$form['grr_currency_code']) $currency_code = $config['arms_currency']['symbol'];
		else $currency_code = $form['grr_currency_code'];
		$msg=sprintf($LANG['GRR_DIFF_CURRENCY_WITH_PO'], $currency_code);
		$ret['err'] = $msg;
	}
	
	print json_encode($ret);
}
?>
