<?php
/*
Update History
==============
10/31/2007 10:27:40 AM yinsee
- shorten the SQL to calculate total GRN items qty

10/31/2007 10:51:03 AM gary
- remove select from sku uom_id , set selling uom to 1.
- add branch_id filter in sku_items_price. 

11/22/2007 4:16:55 PM gary
- do items sequence follow do_items.id

11/26/2007 12:41:32 PM gary
- add update sku_items_cost query when checkout.

12/13/2007 5:13:16 PM gary
- add set changed=1 for delivery branch.;

1/23/2008 3:52:46 PM gary
- fix the update cost bug (when checkout).

2/1/2008 6:06:33 PM gary
- separate out the do list templates and fix the searching bugs.

3/28/2008 4:52:35 PM gary
- when delivery to other company (open_info) and not the outlets just exclude from generate grn and grr.

4/8/2008 2:09:18 PM gary
- fix group by sku_item_id when select from do_items. 

5/8/2008 2:36:20 PM gary
- reset the tab to checkout type (tab no.6) if call from do.php

4/9/2009 6:40:29 PM yinsee
- export GRR rcv_date follow do_date

5/11/2009 4:42:00 PM Andy
- use rcv_qty if it is transfer DO and config['do_use_rcv_pcs']=1

8/6/2009 5:55:42 PM Andy
- add assign do_type to templates

8/24/2009 4:16:07 PM Andy
- check grr active if do_type is transfer or do_type not set

8/25/2009 2:20:19 PM Andy
- trim REQUEST[s] before search and check grr active on search

22/10/2009 9:47:40 AM yinsee
 - show and do.approved=1 and do.active=1 and do.status=1 when search
 
11/20/2009 12:39:37 PM Andy
- pass one more parameter ($form) to function load_do_items

12/28/2009 10:45:43 AM Andy
- Add HTTP_REFERER to check if checkout page is come from DO

3/29/2010 11:36:19 AM Andy
- Fix bugs when multiple user checkout same DO at same.

3/31/2010 5:37:49 PM Andy
- Fix DO checkout show inactive GRN bugs

5/10/2010 3:12:51 PM Andy
- Add DO Markup.

5/13/2010 3:51:42 PM Andy
- Fix checkout items use last item cost bugs

5/31/2010 2:54:17 PM Andy
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.
- Fix javascript and smarty rounding bugs.
- Add checking for allowed future/passed DO Date limit.

7/7/2010 6:02:19 PM Andy
- Change system search variable.
- Fix DO after checkout cannot directly print when using multiple server mode.

3/31/2011 4:20:54 PM Justin
- Added the update of "is IBT" and from branch ID while inserting GRN.

6/6/2011 5:20:54 PM Justin
- Added to validate with serial no only when config found.

8/22/2011 1:45:32 PM Justin
- Added to insert authorized=1 when creating GRN.

9/13/2011 4:39:47 PM Andy
- Fix after DO checkout and reset again, it cant be search under checkout screen.

2/24/2012 2:01:32 PM Justin
- Fixed the wrong config set.

2/28/2012 2:58:32 PM Justin
- Added new config "do_skip_generate_grn" to decide whether need to generate GRR and GRN.

3/9/2012 10:07:16 AM Andy
- Fix DO Checkout pagination bugs.

4/6/2012 5:07:21 PM Alex
- add send_pm to user for DO Transfer only =>do_confirm();

4/13/2012 9:36:33 AM Alex
- send_pm move to bottom of confirm function
- fix the unserialize problem that would return array value by add checking

8/14/2012 3:54:21 PM Fithri
- bypass privilege check when user coming from home page (pm.php)

10/2/2012 10:20 AM Andy
- Fix DO Checkout pagination not showing under "Saved".

1/18/2013 5:30 PM Justin
- Enhanced to capture inactive debtor info.

3/5/2013 4:59 PM Justin
- Enhanced the search engine can search by DO receipt no.

9/23/2013 10:41 AM Justin
- Enhanced to set GRN as using version 2 while doing checkout for Transfer DO.

4:25 PM 9/25/2013
- Enhanced to improve while doing reset do, change to select sku_item_id to update changed=1 instead of using sub query.

11/27/2013 5:06 PM Andy
- Fix load DO list to filter out those inactive GRR.

11/28/2013 4:14 PM Andy
- Fix MySQL slow on getting GRR info.

5/13/2014 1:57 PM Fithri
- allow searching po number in checkout search box

2/9/2015 3:37 PM Andy
- GST Enhancements.

4/15/2015 11:48 AM Andy
- Enhanced DO checkout generate GRR/GRN to compatible with new GST GRR/GRN.

4/29/2015 10:11 AM Andy
- Change the calculation when generate DO to GRN.

2:40 PM 5/8/2015 Andy
- Fix should not put gst_selling_price for grn_items if not under gst.

5/18/2015 2:41 PM Justin
- Enhanced to insert GRR items base on GRN tax code

5/29/2015 11:16 AM Justin
- Bug fixed on checkout will not do anything.

12/17/2015 05:11 PM Qiu Ying
- Add config to put default value for checkout_remark

01/05/2015 3:06 PM DingRen
- add save vendor id for transfer DO checkout

2/22/2016 11:15 AM Qiu Ying
- Search DO should filter status, active and approved

2/29/2016 1:38 PM Andy
- Fix DO Checkout cannot properly link to GRR and GRN when is is grn future.

3/8/2016 2:23 PM Qiu Ying
- DO Date can change and save in checkout page.

12/1/2016 4:40 PM Andy
- Enhanced to check vendor internal code when checkout Transfer DO.

3/23/2017 4:45 PM Justin
- Enhanced to add config checking "single_server_mode" when do confirm/reset DO for S/N.

4/4/2017 10:01 AM Justin
- Enhanced to have new button "Load Last Driver Info" which able to loads last driver information from a checkout DO.

4/14/2017 8:55 AM Qiu Ying
- Bug fixed on load last driver still enable in view mode

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

2/7/2018 1:54 PM Justin
- Bug fixed on GRR follow DO invoice amount instead GRN.
- Enhanced to add into "Rounding Adjustment" while have GST variance in between GRN and DO.

3/14/2018 10:00 AM Justin
- Enhanced DO checkout always create one GRR only base on DO department (config) or the department from first item.

4/20/2018 10:23 AM Justin
- Bug fixed on vendor ID inserted as empty from GRN while GRR has it.

5/30/2018 4:13PM HockLee
- Create DO Checkout by Batch. do_checkout_by_batch().

8/27/2018 4:00PM HockLee
- Bugs fixed: show_do_for_checkout().

10/11/2018 3:37 PM Andy
- Fixed DO Checkout auto popup for print cannot click on print invoice.

10/5/2018 3:06 PM Justin
- Enhanced to have Shipment Method and Tracking Code during checkout.

5/30/2019 2:14 PM William
- Pickup report_prefix for enhance "GRR","GRN".

7/2/2019 2:36 PM William
- Added new "Disable Auto Parent & Child Distribution".

8/7/2019 11:53 AM Justin
- Enhanced the load last driver info to call from appCore DO Manager.
- Enhanced to move auto generate GRR and GRN to appCore DO Manager.

10/8/2019 9:49 AM William
- Fixed bug system unable to detect barcode when barcode has 13 digit.
- Fixed bug when total scan quantity negative then the quantity will become none.

10/15/2019 1:27 PM William
- Enhanced to show variances when scan zero or no scan.
- Enhanced to added negative qty and number format of qty checking.

3/25/2020 10:49 AM William
- Enhanced to added upload_img function.
- Enhanced to check same tracking code cannot use same Shipment Method when config "do_checkout_allow_duplicate_shipment_method_tracking_code" is active.

4/16/2020 11:25 AM William
- Enhanced to check month closed when checkout do.

3/29/2021 6:00 PM Ian
-Added Function "export_completed_do" for DO item export to csv
*/
$HTTP_REFERER = basename($_SERVER['HTTP_REFERER']);

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO_CHECKOUT')&&$HTTP_REFERER!='do.php'&&!preg_match('/^pm.php/',$HTTP_REFERER)) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO_CHECKOUT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

$smarty->assign("PAGE_TITLE", "Delivery Order Checkout");
init_selection();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
		case 'confirm':
			do_confirm();	
			exit;

		case 'ajax_load_do_list':
		    load_do_list();
		    exit;
		
		case 'view':
		case 'edit':
		case 'open':
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			$form=load_do_header($id, $branch_id);
			
			$checklist_disable_parent_child = $form['checklist_disable_parent_child'];
			$smarty->assign("checklist_disable_parent_child", $checklist_disable_parent_child);
			
			if (!$form["checkout_remark"] && $_REQUEST['a'] == "open"){
				if ($config["do_default_checkout_remark"])
					$form["checkout_remark"] = $config["do_default_checkout_remark"];
			}
			
			$image_list = $appCore->doManager->load_do_checkout_img($id, $branch_id);
			if($image_list) $form['image_list'] = $image_list;
			
			$smarty->assign("form", $form);

			if($config['do_checkout_scan_item_variance']){
				$q1 = $con->sql_query("select dbi.*, si.sku_id 
									   from do_barcode_items dbi
									   left join sku_items si on si.id = dbi.sku_item_id
									   where dbi.do_id = ".mi($id)." and dbi.branch_id = ".mi($branch_id)."
									   order by dbi.id");
				
				$num_rows = $con->sql_numrows($q1);
				if($num_rows > 0){
					while($r = $con->sql_fetchassoc($q1)){
						$row++;
						$var_items[$row] = $r;
					}
					$con->sql_freeresult($q1);
				}else{  //Check the do variance is save or skip
					$q2 = $con->sql_query("Select check_variance from do where id =$id and branch_id=$branch_id");
					$r2 = $con->sql_fetchrow($q2);
					$con->sql_freeresult($q2);
					if($r2['check_variance'] == 1){  // when no scan any barcode and click save
						$q3 = $con->sql_query("select di.do_id, di.branch_id, di.sku_item_id, di.artno_mcode, si.sku_id from do_items di
											   left join sku_items si on si.id = di.sku_item_id
												where do_id= $id and branch_id=$branch_id");
						while($r3 = $con->sql_fetchassoc($q3)){
							$row++;
							$r3['qty'] = 0;
							$r3['barcode'] = $r3['artno_mcode'];
							$var_items[$row] = $r3;
						}
						$con->sql_freeresult($q3);
					}
				}
			}
			
			if($_REQUEST['a'] == "open" && !$_REQUEST['skip_barcode_scan'] && $config['do_checkout_scan_item_variance']){
                check_must_can_edit($branch_id, $id,0,1);
				if(!$r2['check_variance'] == 1){ // when has scan barcode and click save, it will show the items saved
					$smarty->assign("items", $var_items);
				}
                $smarty->display("do_checkout.checklist.open.tpl");
			}else{
				$do_items = load_do_items($id, $branch_id, $form);
				$last_do_si = $tmp_items = array();
				if($var_items){
					if($_REQUEST['checklist_disable_parent_child']){
						$checklist_disable_parent_child = 1;
					}
					// sum up the variance base on SKU parent
					if($checklist_disable_parent_child == 1){
						foreach($var_items as $row=>$r){
							$tmp_items[$r['sku_item_id']]['qty'] += $r['qty'];
						}
						foreach($do_items as $row=>$r){
							$do_items[$row]['qty'] = $r['ctn'] * $r['uom_fraction'] + $r['pcs'];
							$tmp_items[$r['sku_item_id']]['totalqty'] += $do_items[$row]['qty'];
						}
					}else{
						foreach($var_items as $row=>$r){
							$tmp_items[$r['sku_id']]['qty'] += $r['qty'];
						}
					}
					
					// calculate the variance
					foreach($do_items as $row=>$r){
						if($checklist_disable_parent_child == 1){
							$do_items[$row]['qty'] = $r['ctn'] * $r['uom_fraction'] + $r['pcs'];
							//$sku_item_id[$r['sku_item_id']] = $r['sku_item_id'];
							if($tmp_items[$r['sku_item_id']]['qty'] >= $do_items[$row]['qty']){
								if($tmp_items[$r['sku_item_id']]['qty'] - $do_items[$row]['qty'] > $tmp_items[$r['sku_item_id']]['totalqty'] - $do_items[$row]['qty']){
									$do_items[$row]['variance'] = 0;
									if($tmp_items[$r['sku_item_id']]['totalqty'] > $do_items[$row]['qty']){
										$do_items[$row]['scan_qty'] = $do_items[$row]['qty'];
									}else{
										$do_items[$row]['variance'] = $tmp_items[$r['sku_item_id']]['qty'] - $do_items[$row]['qty'];
										$do_items[$row]['scan_qty'] = $tmp_items[$r['sku_item_id']]['qty'];
									}
								}
								else{
									$do_items[$row]['variance'] = 0;
									$do_items[$row]['scan_qty'] = $do_items[$row]['qty'];
								}
								$tmp_items[$r['sku_item_id']]['totalqty'] -= $do_items[$row]['qty'];
								$tmp_items[$r['sku_item_id']]['qty'] -= $do_items[$row]['qty'];
							}
							elseif($tmp_items[$r['sku_item_id']]['qty'] > 0){
								$tmp_items[$r['sku_item_id']]['totalqty'] -= $do_items[$row]['qty'];
								$do_items[$row]['variance'] = $tmp_items[$r['sku_item_id']]['qty'] - $do_items[$row]['qty'];
								$do_items[$row]['scan_qty'] = $tmp_items[$r['sku_item_id']]['qty'];
								$tmp_items[$r['sku_item_id']]['qty'] -= $do_items[$row]['qty'];
							}else{  //for nagative value item and no scan item
								if($tmp_items[$r['sku_item_id']]['qty'] == '') $tmp_items[$r['sku_item_id']]['qty'] = 0;
								$do_items[$row]['variance'] = $tmp_items[$r['sku_item_id']]['qty'] - $do_items[$row]['qty'];
								$do_items[$row]['scan_qty'] = $tmp_items[$r['sku_item_id']]['qty'];
							}
							$last_do_si[$r['sku_item_id']] = $row;
						}else{
							$qty = $r['ctn'] * $r['uom_fraction'] + $r['pcs'];
							if($tmp_items[$r['sku_id']]['qty'] >= $qty){ // found the scanned qty is greater than current DO qty
								$do_items[$row]['variance'] = 0;
								$do_items[$row]['scan_qty'] = $qty;
								$tmp_items[$r['sku_id']]['qty'] -= $qty;
							}elseif($tmp_items[$r['sku_id']]['qty'] > 0){
								$do_items[$row]['variance'] = $tmp_items[$r['sku_id']]['qty'] - $qty;
								$do_items[$row]['scan_qty'] = $tmp_items[$r['sku_id']]['qty'];
								unset($tmp_items[$r['sku_id']]);
							}else{  //for nagative value item and no scan item
								if($tmp_items[$r['sku_id']]['qty'] == '') $tmp_items[$r['sku_id']]['qty'] = 0;
								$do_items[$row]['variance'] = $tmp_items[$r['sku_id']]['qty'] - $qty;
								$do_items[$row]['scan_qty'] = $tmp_items[$r['sku_id']]['qty'];
								unset($tmp_items[$r['sku_id']]);
							}
							$last_do_si[$r['sku_id']] = $row;
						}
					}
					// found if there still have balance, need to add into the last item
					if($checklist_disable_parent_child != 1){
						if($tmp_items){
							foreach($tmp_items as $sku_id => $r){
								if($r['qty'] > 0){
									$row = $last_do_si[$sku_id];
									$do_items[$row]['scan_qty'] += $r['qty'];
									$do_items[$row]['variance'] += $r['qty'];
								}
							}
						}
					}
					
					$smarty->assign("have_scan_items", 1);
					//print_r($tmp_items);
				}
				
				if($_REQUEST['a'] == "view" || $form["checkout"]){
					$smarty->assign("view_only", 1);
				}
				
				if($_REQUEST['a'] == "edit" && $sessioninfo['privilege']['DO_CHECKOUT_MODIFY']){
					$smarty->assign("enable_edit", 1);
				}
				
				$smarty->assign("do_items", $do_items);
				$smarty->assign("readonly", 1);
				$smarty->assign('do_type',$form['do_type']);
				$smarty->display("do_checkout.open.tpl");
			}
			exit;

		case 'save_barcode':
			save_barcode();
			exit;
		case 'skip_barcode':
			// update custom_col when click skip
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
			$con->sql_query("update do set check_variance=0 where id=$id and branch_id=$branch_id");
			$redirect_url = "Location: $_SERVER[PHP_SELF]?a=open&skip_barcode_scan=1&id=".$id."&branch_id=".$branch_id;
			header($redirect_url);
			exit;
		case 'ajax_load_driver_info':
			ajax_load_driver_info();
			exit;
		case 'ajax_search_batch_code':
		    ajax_search_batch_code();
		    exit;		
		case 'do_checkout_by_batch':
			// get transporter
			$transporter = array();
			$q_transporter = $con->sql_query("select id as 'transporter_id', name from transporter_type where active = 1 order by name");
			while($d_transporter = $con->sql_fetchassoc($q_transporter)){
				$transporter[$d_transporter['transporter_id']] = $d_transporter['name'];
			}
			$con->sql_freeresult($q_transporter);

			$smarty->assign('no_data_msg', ' ');
			$smarty->assign('transporter', $transporter);
		    $smarty->display("do_checkout.by_batch.tpl");
		    exit;
		case 'show_do_for_checkout':		    
		    show_do_for_checkout();
		    exit;
		case 'open_route':		    
		    open_route();
		    exit;
		case 'print_assignment_note_by_batch':		    
		    print_assignment_note_by_batch();
		    exit;
		case 'do_checkout_save':		    
		    do_checkout_save();
		    exit;
		case 'upload_img':
			upload_img();
			exit;
		case 'ajax_remove_image':
			ajax_remove_image();
			exit;
		case 'update_do_checkout':
			update_do_checkout();
			exit;
		case 'export_completed_do':
			$id=mi($_REQUEST['id']);
			$branch_id=mi($_REQUEST['branch_id']);
			export_completed_do($id, $branch_id);
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	    
	}
}

if(isset($_REQUEST['t'])){
	switch($_REQUEST['t']){
		case 'confirm':
			check_confirmed_do();
			break;
		default:
		    print "<h1>Unhandled Request T</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
$smarty->display("do_checkout.home.tpl");
exit;

function do_confirm(){
	global $con, $sessioninfo, $config, $LANG, $appCore;

	$form=$_REQUEST;
	$form['checkout_info'] = serialize($form['checkout_info']);
	$form['checkout_by']=$sessioninfo['id'];
	$form['checkout']=1;
	$do_no=ms($form['do_no']);

	$con->sql_query("select id,branch_id,checkout,allowed_user,do_type from do where do_no=$do_no");
	$r = $con->sql_fetchassoc();
	
	if($r['checkout']==1){
        js_redirect(sprintf($LANG['DO_ALREADY_CHECKOUT'],"'".$do_no."'"), "/do_checkout.php");
	}
    
    //Validate DO Date
    $arr= explode("-",$form['do_date']);
    $yy=$arr[0];
    $mm=$arr[1];
    $dd=$arr[2];

    if(!checkdate($mm,$dd,$yy)){
        js_redirect(sprintf($LANG['DO_INVALID_DATE']), "/do_checkout.php");
    }
    
    $check_date = strtotime($form['do_date']);
	
	if($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['do_date']);
		if($is_month_closed){
			js_redirect($LANG['MONTH_DOCUMENT_IS_CLOSED'], "/do_checkout.php");
		}
	}
	
    if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
        $upper_limit = $config['upper_date_limit'];
        $upper_date = strtotime("+$upper_limit day" , strtotime("now"));

        if ($check_date>$upper_date){
            js_redirect(sprintf($LANG['DO_DATE_OVER_LIMIT']), "/do_checkout.php");
        }

    }

    if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
        $lower_limit = $config['lower_date_limit'];
        $lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));


        if ($check_date<$lower_date){
            js_redirect(sprintf($LANG['DO_DATE_OVER_LIMIT']), "/do_checkout.php");
        }
    }
    
    $global_gst_start_date = $config["global_gst_start_date"];
    $branch_gst_start_date = $sessioninfo["gst_start_date"];
    
    if ($form['is_under_gst']){
        if($check_date < strtotime($global_gst_start_date) && $check_date < strtotime($branch_gst_start_date)){
            js_redirect(sprintf($LANG['DO_DATE_OVER_LIMIT']), "/do_checkout.php");
        }
    }
	
	if($config['do_checkout_allow_duplicate_shipment_method_tracking_code'] && $form['shipment_method'] && $form['tracking_code']){
		$con->sql_query("select id from do where shipment_method =".ms($form['shipment_method'])." and tracking_code=".ms($form['tracking_code'])." and branch_id = ".mi($r['branch_id'])." and id <> ".mi($r['id']));
		$row = $con->sql_numrows();
		if($row > 0)  js_redirect("This Tracking Code already used on same Shipment Method.", "/do_checkout.php");
		$con->sql_freeresult();
	}
    
    if (!$form['first_checkout_date']){
        $con->sql_query("update do set first_checkout_date = CURRENT_TIMESTAMP() where do_no=$do_no");
    }
	
   	$con->sql_query("update do set " . mysql_update_by_field($form, array('checkout_info', 'checkout_by', 'checkout', 'checkout_remark', 'do_date', 'shipment_method', 'tracking_code'))."where do_no=$do_no");
    
    //print_r($form);
    
    //AUTO GENERATE GRR -> GRR ITEMS -> GRN 
	$prms = array();
	$prms['id'] = $r['id'];
	$prms['branch_id'] = $r['branch_id'];
	$appCore->doManager->doGRNAutoGenerator($prms);
	
	if($config['single_server_mode'] && $config['enable_sn_bn']) serial_no_handler("confirm");
	
	$sid_list = array();
	$q1 = $con->sql_query("select sku_item_id from do_items left join do on do.id=do_items.do_id and do.branch_id=do_items.branch_id where do_items.do_id=$form[id] and do_items.branch_id=$form[branch_id] and do.checkout and do.approved and do.status<2");
	while($r = $con->sql_fetchassoc($q1)){
		$sid_list[] = mi($r['sku_item_id']);
	}
	$con->sql_freeresult($q1);
	
	if($sid_list){
		//update sku_items_cost for do_items
		$con->sql_query("update sku_items_cost set changed=1 where branch_id in (".mi($form['branch_id']).", ".mi($form['do_branch_id']).") and sku_item_id in (".join(',', $sid_list).")");
		$sid_list = array();
	}

	//move to bottom
    if ($r['do_type'] == 'transfer'){
        //send notification to user list    
	    $allowed_user = unserialize($r['allowed_user']);
	    if ($allowed_user){
		    foreach($allowed_user as $un){
				foreach($un as $uid => $dummy){
					$uid = mi($uid);
					if(!$uid)	continue;
					
					$user_list[$uid]=$uid;
				}
			}
			
			if($user_list){
				// send pm
				send_pm($user_list, "Delivery Order Checkout (DO No#$do_no) had complete checkout", "do_checkout.php?a=view&id=$r[id]&branch_id=$form[branch_id]&do_type=transfer",-1,true);
			}
		}
	}
	header("Location: $_SERVER[PHP_SELF]?t=confirm&id=$form[id]&bid=$form[branch_id]");
}

function load_do_list($t = 0){
	global $con, $sessioninfo, $smarty,$config;
	if (!$t) {
		$t = intval($_REQUEST['t']);
	}	
    $_REQUEST['s'] = trim($_REQUEST['s']);
    
	if(!$config['consignment_modules']){
    	$where = "(do.branch_id=$sessioninfo[branch_id] ) and ";	
	}
	//do.do_branch_id=$sessioninfo[branch_id] or 
	switch ($t){
	    case 0:
	        $_REQUEST['search'] = trim($_REQUEST['search']);
			if ($_REQUEST['search']==''){
				print "<p align=center>I won't search empty string</p>";
				exit;
			}   
		    $where .= 'do.active=1 and do.approved = 1 and do.status = 1 and (do.do_no is not null and (do.id = ' . mi($_REQUEST['search']) . ' or do.id like ' . ms('%'.replace_special_char($_REQUEST['search']).'%').' or do.do_no like '.ms('%'.replace_special_char($_REQUEST['search']).'%').' or b2.code='.ms($_REQUEST['search']).' or do.po_no like '.ms(replace_special_char($_REQUEST['search'])).'))';
			
			if($config['do_generate_receipt_no']){
				$where .= 'or (do.do_receipt_no is not null and do.do_receipt_no != "" and do.do_receipt_no = '.mi($_REQUEST['search']).')';
			}
			
		   	//$where2 = " and (grr.active=1 or grr.active is null) and (grn.active=1 or grn.active is null) and do.approved=1 and do.active=1 and do.status=1";
		    $_REQUEST['s'] = '';
		    $search_do=1;
	        break;

		case 1:
			$where .= "do.do_no is not null and do.approved=1 and do.active=1 and do.status=1 and do.checkout=0";
			//$group_by .= 'group by do.do_no';
			$saved_list=1;
        	break;

		case 2:
			$where .= "do.do_no is not null and do.approved=1 and do.active=1 and do.status=1 and do.checkout=1";
            //$where2 = " and (grr.active=1 or grr.active is null) and (grn.active=1 or grn.active is null)";
		    break;
	}

	if(isset($_REQUEST['do_type'])){
		$where .= " and do_type=".ms($_REQUEST['do_type']);
	}
	
	if(!$search_do){
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}
		$con->sql_query($q = "select count(*) 
		from do 
		where $where $where2 $group_by");
		$r = $con->sql_fetchrow();
		$total = $r[0];
		//print "$q";
		if ($total > $sz){
		    if ($start > $total) $start = 0;
		    //if call from do.php then set back to checkout tab.
		    if($_REQUEST['from']=='do') $t=6;
			$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start){
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
		$limit_query='limit '.$start.', '.$sz;  	
	} 

	$sql = "select do.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2, user.u as user_name 
	from do 
	left join category on do.dept_id = category.id 
	left join branch on do.branch_id = branch.id 
	left join branch b2 on do.do_branch_id = b2.id 
	left join user on user.id = do.user_id  
	where $where $where2 $group_by 
	order by do.last_update desc $limit_query";

/*
	grr.id as grr_id, grn.id as grn_id, grr_items.id as grr_items_id
	
	left join grr on grr.active=1 and grr.branch_id=do.do_branch_id and grr.id in (select gri.grr_id from grr_items gri where gri.doc_no = do.do_no and gri.branch_id=do.do_branch_id and gri.type='DO')
	left join grr_items on grr_items.doc_no = do.do_no and grr_items.branch_id=grr.branch_id and grr_items.type='DO' and grr_items.grr_id=grr.id
	left join grn on grn.grr_id=grr.id and grn.branch_id=grr.branch_id and grn.active=1
*/
	//print $sql;
	$q2=$con->sql_query($sql);

	$debtors = $smarty->get_template_vars('debtor');
	while ($r2= $con->sql_fetchrow($q2)){
		$additional_grr_grn_info = array();

		$r2['checkout_info'] = unserialize($r2['checkout_info']);
 		$r2['open_info'] = unserialize($r2['open_info']);	
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);

		if($r2['debtor_id'] && !$debtors[$r2['debtor_id']]){
			$sql = $con->sql_query("select * from debtor where id = ".mi($r2['debtor_id']));
			$debtor_info = $con->sql_fetchassoc($sql);
			
			if($debtor_info){
				$debtors[$debtor_info['id']] = $debtor_info;
			}
		}
		
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];			
			}
		}
		
		if($r2['do_type'] == 'transfer' && $r2['do_branch_id'] && $r2['checkout']){	// got checkout, need get grr/grr items, grn
			
			// get grr_items
			$q_gi = $con->sql_query("select branch_id,id,grr_id from grr_items where doc_no=".ms($r2['do_no'])." and type='DO' and branch_id=".mi($r2['do_branch_id']));
			while($gi = $con->sql_fetchassoc($q_gi)){
				// get grr
				$q_grr = $con->sql_query("select branch_id, id from grr where branch_id=".mi($gi['branch_id'])." and id=".mi($gi['grr_id'])." and active=1");
				while($grr = $con->sql_fetchassoc($q_grr)){
					// get grn
					$q_grn = $con->sql_query("select grn.branch_id, grn.id,branch.report_prefix from grn left join branch on grn.branch_id = branch.id where grn.branch_id=".mi($grr['branch_id'])." and grn.grr_id=".mi($grr['id'])." and grn.active=1 and if(grn.is_future=1,1,grn.grr_item_id=".mi($gi['id']).")");
					while($grn = $con->sql_fetchassoc($q_grn)){
						$arr = array();
						$arr['grr_id'] = $grr['id'];
						$arr['grr_items_id'] = $gi['id'];
						$arr['grn_id'] = $grn['id'];
						$arr['report_prefix'] = $grn['report_prefix'];
						$additional_grr_grn_info[] = $arr;	// store grr/grn info
						
					}
					$con->sql_freeresult($q_grn);
				}
				$con->sql_freeresult($q_grr);
			}
			$con->sql_freeresult($q_gi);
		
		}
		
		$i = 0;
		do{
			if(is_array($additional_grr_grn_info[$i])){
				$r2 = array_merge($r2, $additional_grr_grn_info[$i]);	
			}
			
			if($search_do){
				if($r2['checkout'])	$completed[]=$r2;		
				else $saved[]=$r2;
			}
			else{
				$temp2[]=$r2;
			}
		
			$i++;
		}while($i < count($additional_grr_grn_info));
		
		
	}

	if($debtors) $smarty->assign("debtor", $debtors);
	
	if($search_do){
		if(!$saved && !$completed){
			print "<p align=center>- No Record-</p>";		
		}
		
		if($saved){
			$smarty->assign("do_list", $saved);
			print "<h3>&nbsp; Saved DO</h3>";
			$smarty->display("do_checkout.saved_list.tpl");	
		}
		if($completed){
			$smarty->assign("do_list", $completed);
			print "<h3>&nbsp; Completed DO</h3>";
			$smarty->display("do_checkout.completed_list.tpl");	
		}	
	}
	else{
	    //print_r($temp2);
		$smarty->assign("do_list", $temp2);
		if($saved_list)$smarty->display("do_checkout.saved_list.tpl");		
		else $smarty->display("do_checkout.completed_list.tpl");	
	}
}

function save_barcode(){
	global $con, $sessioninfo, $smarty, $LANG;
	
	$form = $_REQUEST;
	$invalid_barcode = $si_list = $invalid_format_qty = array();
	// validate barcode
	foreach($form['barcode'] as $row=>$barcode){
		$barcode = trim($barcode);
		$tmp = array();
		if(!$barcode) continue;
		if($form['qty'][$row] < 0){
			$err[] = "Not Allow to enter negative Qty.";
			$tmp['is_error'] = true;
		}
		$filter = "(sku_item_code = ".ms($barcode)." or artno = ".ms($barcode)." or link_code = ".ms($barcode)." or mcode = ".ms($barcode).")";
		$q1 = $con->sql_query("select * from sku_items where ".$filter." limit 1");
		$si_info = $con->sql_fetchassoc($q1);
		
		//Check if barcode digit is 13 and cannot get any data, auto remove last digit of barcode and search again. 
		$barcode_numrows = 0;
		if($con->sql_numrows($q1) == 0){
			if(strlen($barcode) == 13){
				$newbarcode =  substr($barcode, 0, -1);
				$filter2 = "(sku_item_code = ".ms($newbarcode)." or artno = ".ms($newbarcode)." or link_code = ".ms($newbarcode)." or mcode = ".ms($newbarcode).")";
				$q_barcode = $con->sql_query("select * from sku_items where ".$filter2." limit 1");
				$si_info = $con->sql_fetchassoc($q_barcode);
				$barcode_numrows = $con->sql_numrows($q_barcode);
				if($barcode_numrows == 1){
					if($form['barcode'][$row] == $barcode){
						$form['barcode'][$row] = $newbarcode;
					}
					$barcode = $newbarcode;
				}
				$con->sql_freeresult($q_barcode);
			}
		}
		// found it is not a valid SKU item
		if($con->sql_numrows($q1) == 0 && $barcode_numrows == 0){
			$invalid_barcode[] = $barcode;
			$tmp['is_error'] = true;
		}else{
			// check if it is existed in DO items
			$q2 = $con->sql_query("select * from do_items where do_id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($si_info['id']));
			
			if($con->sql_numrows($q2) == 0){
				$invalid_barcode[] = $barcode;
				$tmp['is_error'] = true;
			}else $si_list[$barcode] = $si_info;
			
			$con->sql_freeresult($q2);
		}
		// check sku item qty allow decimal or not
		if(strpos($form['qty'][$row], ".") !== false && $si_info['doc_allow_decimal']!=1){
			$invalid_format_qty[] = $barcode;
			$tmp['is_error'] = true;
		}
		$con->sql_freeresult($q1);
		$tmp['barcode'] = $barcode;
		$tmp['qty'] = $form['qty'][$row];
		$items[$row] = $tmp;
	}
	
	// found having invalid barcodes, present error message
	if($invalid_barcode){
		$err[] = sprintf($LANG["DO_CHECKOUT_INVALID_BARCODE"], join(",", $invalid_barcode));
	}
	if($invalid_format_qty){
		$err[] = "Decimal Qty not allow on this DO [".join(",", $invalid_format_qty)."].";
	}
	if($err){
		$err = array_unique($err);
		$checklist_disable_parent_child = $form['checklist_disable_parent_child'];
		$data=load_do_header($form['id'], $form['branch_id']);
		$smarty->assign("checklist_disable_parent_child", $checklist_disable_parent_child);
		$smarty->assign("form", $data);
		$smarty->assign("items", $items);
		$smarty->assign("err", $err);
		$smarty->display("do_checkout.checklist.open.tpl");
		exit;
	}
	
	$con->sql_query("delete from do_barcode_items where do_id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
	
	foreach($form['barcode'] as $row=>$barcode){
		$barcode = trim($barcode);
		if(!$barcode) continue;

		$ins = array();
		$ins['do_id'] = $form['id'];
		$ins['branch_id'] = $form['branch_id'];
		$ins['sku_item_id'] = $si_list[$barcode]['id'];
		$ins['user_id'] = $sessioninfo['id'];
		$ins['barcode'] = $barcode;
		$ins['qty'] = $form['qty'][$row];
		$ins['last_update'] = $ins['added'] = "CURRENT_TIMETSAMP";
		
		$con->sql_query("replace into do_barcode_items ".mysql_insert_by_field($ins));
	}
	
	// Update DO Checklist
	$upd = array();
	$upd['checklist_disable_parent_child'] = mi($form['checklist_disable_parent_child']);
	$upd['check_variance'] = 1;
	$con->sql_query("update do set ".mysql_update_by_field($upd)." where id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
	
	// Redirect
	$redirect_url = "Location: $_SERVER[PHP_SELF]?a=open&id=".mi($form['id'])."&branch_id=".mi($form['branch_id'])."&skip_barcode_scan=1";
	if($upd['checklist_disable_parent_child']){
		$redirect_url .= "&checklist_disable_parent_child=".$upd['checklist_disable_parent_child'];
	}
	header($redirect_url);
}

function ajax_load_driver_info(){
	global $appCore;
		
	$ret = array();
	$ret = $appCore->doManager->loadDriverInfo();
	
	print json_encode($ret);
}

function ajax_search_batch_code(){
    global $con, $smarty, $sessioninfo;
    $v = trim($_REQUEST['value']);
    $LIMIT = 50;
    // call with limit
	$result1 = $con->sql_query("select distinct(batch_code) as batch_code from do where do.branch_id = ".mi($sessioninfo['branch_id'])." and batch_code like ".ms('%'.replace_special_char($v).'%')." and active = 1 and approved = 1 order by batch_code limit ".($LIMIT+1));
    print "<ul>";
	if ($con->sql_numrows($result1) > 0)
	{

	    if ($con->sql_numrows($result1) > $LIMIT)
	    {
			print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
		}

		// generate list.
		while ($r = $con->sql_fetchrow($result1))
		{
			$out .= "<li title=".htmlspecialchars($r['batch_code'])."><span>".htmlspecialchars($r['batch_code']);
			$out .= "</span>";
			$out .= "</li>";
		}
    }
    else
    {
       print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
	}
	print $out;
    print "</ul>";
	exit;
}

function show_do_for_checkout(){
	global $con, $smarty, $sessioninfo, $config;

	$form = $_REQUEST;
	$transporter_id = $form['transporter_id'];
	$batch_code = $form['batch_code'];
	$date_from = $form['date_from'];
	$date_to = $form['date_to'];
	$branch_id = $sessioninfo['branch_id'];

	$q_do = $con->sql_query("select checkout 
		from do 
		where branch_id = ".mi($branch_id)." and batch_code = ".ms($batch_code)." and active = 1 and status = 1 and approved = 1 and checkout = 0");
	$checkout_row = $con->sql_numrows();
	$con->sql_freeresult($q_do);

	if(!$checkout_row){
		$smarty->assign('no_data_msg', 'The batch has been checkout or invalid Batch Code.');

		// get transporter
		$transporter = array();
		$q_transporter = $con->sql_query("select id as 'transporter_id', name from transporter_type where active = 1 order by name");
		while($d_transporter = $con->sql_fetchassoc($q_transporter)){
			$transporter[$d_transporter['transporter_id']] = $d_transporter['name'];
		}
		$con->sql_freeresult($q_transporter);
		$smarty->assign('transporter', $transporter);
	}	

	// if all or partial DO have not checkout 
	if($checkout_row){
		$q_transporter = $con->sql_query("select t.id, t.code, t.company_name, tv.id as 'vehicle_id', tv.plate_no, tv.max_load, tv.route_id, tv.occupied, tr.name as 'route_name', vs.name as 'status' 
			from transporter t 
			left join transporter_vehicle tv on tv.transporter_id = t.id 
			left join transporter_route tr on tr.id = tv.route_id 
			left join transporter_vehicle_status vs on vs.id = tv.status_id 
			where t.type_id = $transporter_id and t.active = 1 and tv.active = 1 and tr.active = 1 and vs.active = 1 
			order by vs.name, tv.plate_no");

		$row = $con->sql_numrows();
		if($row == 0){
			$smarty->assign('no_data_msg', 'No data. You may check the Transporter Master File, Batch Code or Date.');
		}

		$transport_info = array();
		while($d_transporter = $con->sql_fetchassoc($q_transporter)){
			$transport_info[$d_transporter['company_name']][] = $d_transporter;
			$transport_id = $d_transporter['id'];
			$route_id[] = $d_transporter['route_id'];
		}
		$con->sql_freeresult($q_transporter);

		// get transporter
		$transporter = array();
		$q_transporter = $con->sql_query("select id as 'transporter_id', name from transporter_type where active = 1 order by name");
		while($d_transporter = $con->sql_fetchassoc($q_transporter)){
			$transporter[$d_transporter['transporter_id']] = $d_transporter['name'];
		}
		$con->sql_freeresult($q_transporter);

		$smarty->assign('transporter', $transporter);

		// get route area
		$route_area = array();
		foreach($route_id as $key => $id){
			$q_route_area = $con->sql_query("select tr.id as 'route_id', tr.name as 'route_name', a.name as 'area', tra.sequence 
				from transporter_route tr 
				left join transporter_route_area tra on tra.route_id = tr.id 
				left join transporter_area a on a.id = tra.area_id 
				where tr.id = $id and tr.active = 1 and tra.active = 1 and a.active = 1 
				order by tra.sequence");
			while($d_route_area = $con->sql_fetchassoc($q_route_area)){
				$route_area[$d_route_area['route_name']][] = $d_route_area;
			}
			$con->sql_freeresult($q_route_area);
		}

		// get debtor area
		$debtor_area = array();		
		$q_debtor_area = $con->sql_query("select do.id, do.branch_id, do.do_no, do.do_date, dt.area as 'debtor_area' 
			from do 
			left join sales_order so on so.order_no = do.ref_no 
			left join debtor dt on dt.integration_code = so.integration_code 
			where do.branch_id = ".mi($branch_id)." and do.approved = 1 and do.active = 1 and do.status = 1 and do.checkout = 0 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)."");
		
		$row = $con->sql_numrows();
		if(!$row){
			$smarty->assign('no_data_msg', 'DO not found. Please make sure the Batch Code or the Date range is correct.');
		}

		while($d_debtor_area = $con->sql_fetchassoc($q_debtor_area)){
			$debtor_area[$d_debtor_area['do_no']][] = $d_debtor_area;
			$id = $d_debtor_area['id'];
			$branch_id = $d_debtor_area['branch_id'];
		}
		$con->sql_freeresult($q_debtor_area);

		$destination = array();
		foreach($debtor_area as $do_no => $d_area){
			foreach($d_area as $d_key => $d_value){
				foreach($route_area as $route => $area){
					foreach($area as $key => $data){
						if($d_value['debtor_area'] == $data['area']){
							$destination[$data['route_id']][] = $d_value;
						}
					}
				}
			}				
		}

		if($config["do_default_checkout_remark"]){
			$form["checkout_remark"] = $config["do_default_checkout_remark"];
		}

	}

	$smarty->assign('debtor_area', $debtor_area);
	$smarty->assign('destination', $destination);
	$smarty->assign('transport_type_id', $transporter_id);
	$smarty->assign('transport_id', $transport_id);
	$smarty->assign('batch_code', $batch_code);
	$smarty->assign('date_from', $date_from);
	$smarty->assign('date_to', $date_to);
	$smarty->assign('transport_info', $transport_info);
	$smarty->assign('form', $form);
	$smarty->display('do_checkout.by_batch.tpl');
}

function open_route(){
	global $con, $smarty, $sessioninfo;

	$form = $_REQUEST;
	$batch_code = $form['batch_code'];
	$date_from = $form['date_from'];
	$date_to = $form['date_to'];
	$branch_id = $sessioninfo['branch_id'];

	$route_id = mi($_REQUEST['id']);
	if($id){
		$con->sql_query("select * from transporter_vehicle where id = $id");
		$smarty->assign('form', $con->sql_fetchrow());
	}

	// get route area
	$route_area = array();
	$q_route_area = $con->sql_query("select tr.id as 'route_id', tr.name as 'route_name', ta.name as 'area', tra.sequence 
		from transporter_route tr 
		left join transporter_route_area tra on tra.route_id = tr.id 
		left join transporter_area ta on ta.id = tra.area_id 
		where tr.id = $route_id and tr.active = 1 and tra.active = 1 and ta.active = 1 
		order by tra.sequence");
	while($d_route_area = $con->sql_fetchassoc($q_route_area)){
		$route_area[$d_route_area['route_name']][] = $d_route_area;
	}
	$con->sql_freeresult($q_route_area);

	// get debtor area
	$debtor_area = array();
	$q_debtor_area = $con->sql_query("select do.id, do.do_no, do.do_date, dt.description as 'debtor', dt.area as 'debtor_area' 
	from do 
	left join sales_order so on so.order_no = do.ref_no 
	left join debtor dt on dt.integration_code = so.integration_code 
	where do.branch_id = ".mi($branch_id)." and  do.approved = 1 and do.active = 1 and do.status = 1 and do.checkout = 0 and do.batch_code = ".ms($batch_code)." and do.cancelled_by is null and do.do_date between ".ms($date_from)." and ".ms($date_to)."");
	while($d_debtor_area = $con->sql_fetchassoc($q_debtor_area)){
		$debtor_area[$d_debtor_area['do_no']][] = $d_debtor_area;
	}
	$con->sql_freeresult($q_debtor_area);

	$destination = array();
	$i = 0;
	foreach($debtor_area as $do_no => $d_area){
		foreach($d_area as $d_key => $d_value){
			foreach($route_area as $route => $r_area){
				foreach($r_area as $r_key => $r_value){
					if($d_value['debtor_area'] == $r_value['area']){
						$destination[$r_value['route_id']][$i][] = $d_value;
						$destination[$r_value['route_id']][$i]['error'] = 0;
					}else{						
						$destination[$r_value['route_id']][$i]['id'] = $d_value['id'];
						$destination[$r_value['route_id']][$i]['branch_id'] = $d_value['branch_id'];
						$destination[$r_value['route_id']][$i]['do_no'] = $d_value['do_no'];
						$destination[$r_value['route_id']][$i]['do_date'] = $d_value['do_date'];
						$destination[$r_value['route_id']][$i]['debtor_area'] = $d_value['debtor_area'];
						$destination[$r_value['route_id']][$i]['debtor'] = $d_value['debtor'];
					}
				}
			}
			$i++;
		}				
	}

	$smarty->assign('route_area', $route_area);
	$smarty->assign('destination', $destination);

	$smarty->display('do_checkout.by_batch.route.tpl');
}

function print_assignment_note_by_batch(){
	global $con, $smarty, $sessioninfo;

	$batch_code = $_REQUEST['batch_code'];
	$branch_id = $sessioninfo['branch_id'];

	$do_assign = array();
	$q_do_assign = $con->sql_query("select da.transporter_id,t.company_name, t.address, t.phone_1, t.phone_2, t.fax, tv.id as 'vehicle_id', 
		tv.plate_no, td.name, td.ic_no, td.phone_1, td.phone_2, do.id as 'do_id', do.do_date, do.do_no, d.code, d.description, d.area 
		from do_assignment da 
		left join do on do.id = da.do_id and do.branch_id = da.branch_id  
		left join sales_order so on so.order_no = do.ref_no 
		left join debtor d on d.integration_code = so.integration_code
		left join transporter_route tr on tr.id = da.route_id
		left join transporter t on t.id = da.transporter_id 
		left join transporter_vehicle tv on tv.id = da.vehicle_id 
		left join transporter_driver td on td.id = da.driver_id 
		where do.branch_id = ".mi($branch_id)." and do.batch_code = ".ms($batch_code)." and do.checkout = 1");

	$row = $con->sql_numrows();
	if($row == 0){
		echo '<script language="javascript">alert("No data. The DO may not be checked out.");window.close();</script>';
		exit;
	}

	$sum_ttl_carton = 0;
	$sum_ttl_weight = 0;
	$same_vehicle_id = 0;
	while($d_do_assign = $con->sql_fetchassoc($q_do_assign)){
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['company_name'] = $d_do_assign['company_name'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['address'] = $d_do_assign['address'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['phone_1'] = $d_do_assign['phone_1'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['phone_2'] = $d_do_assign['phone_2'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['fax'] = $d_do_assign['fax'];

		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['driver_name'] = $d_do_assign['name'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['driver_ic_no'] = $d_do_assign['ic_no'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['driver_phone_1'] = $d_do_assign['phone_1'];
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['driver_phone_2'] = $d_do_assign['phone_2'];

		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['do'][$d_do_assign['do_id']] = $d_do_assign;

		$do_id = $d_do_assign['do_id'];
		$vehicle_id = $d_do_assign['vehicle_id'];
		$transporter_id = $d_do_assign['transporter_id'];
		$vehicle_id = $d_do_assign['vehicle_id'];

		$q_sql = $con->sql_query("select sum(p.carton) as ttl_carton, sum(p.weight_kg) as ttl_weight 
			from do_items di 
			left join packing p on p.do_items_id = di.id and p.branch_id = di.branch_id 
			left join do on do.id = di.do_id 
			where do.branch_id = ".mi($branch_id)." and do.id = $do_id");
		$d_sql = $con->sql_fetchassoc($q_sql);

		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['do'][$d_do_assign['do_id']] = array_merge($do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['do'][$d_do_assign['do_id']], $d_sql);

		if($vehicle_id != $same_vehicle_id){
			$sum_ttl_carton = $d_sql['ttl_carton'];
			$sum_ttl_weight = $d_sql['ttl_weight'];
		}else{
			$sum_ttl_carton += $d_sql['ttl_carton'];
			$sum_ttl_weight += $d_sql['ttl_weight'];
		}
		$same_vehicle_id = $vehicle_id;

		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['sum_ttl_carton'] = $sum_ttl_carton;
		$do_assign[$d_do_assign['plate_no']][$d_do_assign['company_name']]['sum_ttl_weight'] = $sum_ttl_weight;	
	}
	$con->sql_freeresult($q_do_assign);

	$smarty->assign('batch_code', $batch_code);
	$smarty->assign('do_assign', $do_assign);
	$smarty->display('do.print_assignment_note.tpl');
}

function do_checkout_save(){
	global $con, $sessioninfo, $config, $LANG;

	$form = $_REQUEST;
	$form['checkout_info'] = serialize($form['checkout_info']);
	$form['checkout_by'] = $sessioninfo['id'];
	$form['checkout'] = 1;
	$transporter_id = $form['transporter_id'];
	$do_no_arr = $form['do_no'];
	$branch_id = $sessioninfo['branch_id'];
	$user_id = $sessioninfo['id'];

	foreach($do_no_arr as $key => $value){
		$do_no = explode("_", $value);
		$do_id = $do_no[2];

		$con->sql_query("select id, checkout, allowed_user, do_type from do where branch_id = ".mi($branch_id)." and id = $do_id");
		$r = $con->sql_fetchassoc();
		
		if($r['checkout'] == 1){
	        js_redirect(sprintf($LANG['DO_ALREADY_CHECKOUT'], "'".$do_no."'"), "/do_checkout.php");
		}
	}

    //Validate DO Date
    $arr= explode("-", $form['do_date']);
    $yy = $arr[0];
    $mm = $arr[1];
    $dd = $arr[2];

    if(!checkdate($mm, $dd, $yy)){
        js_redirect(sprintf($LANG['DO_INVALID_DATE']), "/do_checkout.php");
    }
    
    $check_date = strtotime($form['do_date']);

    if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
        $upper_limit = $config['upper_date_limit'];
        $upper_date = strtotime("+$upper_limit day", strtotime("now"));

        if ($check_date > $upper_date){
            js_redirect(sprintf($LANG['DO_DATE_OVER_LIMIT']), "/do_checkout.php");
        }
    }

    if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
        $lower_limit = $config['lower_date_limit'];
        $lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

        if ($check_date < $lower_date){
            js_redirect(sprintf($LANG['DO_DATE_OVER_LIMIT']), "/do_checkout.php");
        }
    }

    $upd['branch_id'] = $branch_id;
    $upd['user_id'] = $user_id;
    $upd['transporter_id'] = $transporter_id;
	
   	$vehicle_arr = array();
   	foreach($do_no_arr as $key => $value){
   		$split = explode("_", $value);
   		$do_id = $split[2];
   		$route_id = $split[1];
   		$vehicle_id = $split[0];

   		$q_vehicle = $con->sql_query("select plate_no from transporter_vehicle where id = $vehicle_id and active = 1");
		$d_vehicle = $con->sql_fetchassoc($q_vehicle);
   			$vehicle_arr['lorry_no'] = $d_vehicle['plate_no'];
   		$con->sql_freeresult($q_vehicle);

   		$q_driver = $con->sql_query("select id as 'driver_id', name, ic_no from transporter_driver where vehicle_id = $vehicle_id and active = 1");
		$d_driver = $con->sql_fetchassoc($q_driver);
		$driver_id = $d_driver['driver_id'];
		$vehicle_arr['name'] = $d_driver['name'];
		$vehicle_arr['nric'] = $d_driver['ic_no'];
		$con->sql_freeresult($q_driver); 		

   		$form['checkout_info'] = serialize($vehicle_arr);

   		$upd['route_id'] = $route_id;
   		$upd['vehicle_id'] = $vehicle_id;
   		$upd['driver_id'] = $driver_id;
   		$upd['do_id'] = $do_id;
   		$upd['active'] = 1;
   		$upd['added'] = 'CURRENT_TIMESTAMP';
		$upd['last_update'] = 'CURRENT_TIMESTAMP';

		$con->sql_query("insert into do_assignment " . mysql_insert_by_field($upd));

   		// update DO checkout info
   		$con->sql_query("update do set " . mysql_update_by_field($form, array('checkout_info', 'checkout_by', 'checkout', 'checkout_remark', 'do_date', 'shipment_method', 'tracking_code'))."where id = $do_id and branch_id = $branch_id");

   		// occupied=1 (in use)
   		$con->sql_query("update transporter_vehicle set occupied = 1, last_update = CURRENT_TIMESTAMP where id = $vehicle_id");
   	}

   	header("Location: $_SERVER[PHP_SELF]");
}

function check_confirmed_do(){
	global $con, $smarty;
	
	$bid = mi($_REQUEST['bid']);
	$do_id = ($_REQUEST['id']);
	
	if(!$bid || !$do_id)	return;
	
	$confirmed_do = load_do_header($do_id, $bid);
	
	$smarty->assign('confirmed_do', $confirmed_do);
}

function upload_img(){
	global $appCore;
	
	$form = $_REQUEST;
	$branch_id = mi($form['branch_id']);
	$id = mi($form['id']);
	
	$attch_folder = 'attch';
	$do_checkout_folder = 'do_checkout_img';
	$file_name = $_FILES['fnew']['name'];
	
	if(isset($_FILES["fnew"]) && $_FILES["fnew"]["error"] == 0){
		//error checking
		$err = array();
		$filepath = $attch_folder."/".$do_checkout_folder."/".$branch_id."/".$id."/".$file_name;
		$image_list = $appCore->doManager->load_do_checkout_img($id, $branch_id);
		
		if(in_array($filepath, $image_list)){    //check file name duplicate
			$err[] = "Upload file name cannot duplicate.";
		}
		
		if($_FILES['fnew']['size'] > 1048576){   //check file size
			$err[] = "Upload file size cannot more than 1 MB.";
		}
		$errm = implode('\n', $err);
		
		if($errm){
			$str = "<script>";
			$str .= "parent.window.upload_img_error_msg('".$errm."')";
			$str .= "</script>";
			print $str;
			exit;
		}
		
		// make folder
		if(!is_dir($attch_folder)){
			mkdir($attch_folder);
			chmod($attch_folder,0777);
		}
		
		if(!is_dir($attch_folder."/".$do_checkout_folder)){
			mkdir($attch_folder."/".$do_checkout_folder);
			chmod($attch_folder."/".$do_checkout_folder,0777);
		}
	
		if(!is_dir($attch_folder."/".$do_checkout_folder."/".$branch_id)){
			mkdir($attch_folder."/".$do_checkout_folder."/".$branch_id);
			chmod($attch_folder."/".$do_checkout_folder."/".$branch_id, 0777);
		}
		
		if(!is_dir($attch_folder."/".$do_checkout_folder."/".$branch_id."/".$id)){
			mkdir($attch_folder."/".$do_checkout_folder."/".$branch_id."/".$id);
			chmod($attch_folder."/".$do_checkout_folder."/".$branch_id."/".$id, 0777);
		}
		
		move_uploaded_file($_FILES['fnew']['tmp_name'], $filepath);
		chmod($imagep,0777);
	
		$str = "<script>";
		$str .= "parent.window.upload_img_callback('".$filepath."')";
		$str .= "</script>";
		print $str;
	
	}
	exit;
}

function ajax_remove_image(){
	$form = $_REQUEST;
	$branch_id = mi($form['branch_id']);
	$id = mi($form['id']);
	$f = $form['f'];
	
	if(unlink($f)){
		print "OK";
	}else{
		print "Delete failed";
	}
	
	exit;
}

function update_do_checkout(){
	global $con, $sessioninfo, $smarty, $config;
	
	$form = $_REQUEST;
	$id = $form['id'];
	$branch_id = $form['branch_id'];
	
	if($config['do_checkout_allow_duplicate_shipment_method_tracking_code'] && $form['shipment_method'] && $form['tracking_code']){
		$con->sql_query("select id from do where shipment_method =".ms($form['shipment_method'])." and tracking_code=".ms($form['tracking_code'])." and branch_id = ".mi($branch_id)." and id <> ".mi($id));
		$row = $con->sql_numrows();
		if($row > 0)  js_redirect("This Tracking Code already used on same Shipment Method.", "/do_checkout.php");
		$con->sql_freeresult();
	}

	$upd = array();
	$upd['shipment_method'] = $form['shipment_method'];
	$upd['tracking_code'] = $form['tracking_code'];
	
	// update DO checkout info
	$con->sql_query("update do set " . mysql_update_by_field($upd)."where id =$id and branch_id =$branch_id");
	
	header("Location: $_SERVER[PHP_SELF]?r=save&save_id=$id");
}

//export completed DO items checkout
function export_completed_do($id, $branch_id){
	global $appCore;
	
	$appCore->doManager->export_do($id, $branch_id);
	exit;
}
?>