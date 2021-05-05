<?
/*
Revision History
================
6/28/2007 4:11:56 PM yinsee
- cancelled GRA during checkout

7/23/2007 10:00:57 AM yinsee
- fix bug where print wont work if no ARMS item in GRA

9/21/2007 10:41:59 AM yinsee
- only show total in last page

11/26/2007 12:07:01 PM gary
- add update sku_items_cost query.

21/19/2008 3:55:22 PM gary
- separate out the NONSKU and SKU items with pagination.

4/17/2008 6:20:14 PM yinsee
- set returned=0,status=1 when cancel! (cannot set returned=2)

6/23/2009 4:36 PM Andy
- add checking on $config['gra_alt_print_template'] to allow custom print


1/7/2009 12:00:09 PM yinsee
- add $config['gra_print_item_per_page']

2/7/2009 12:06 PM jeff
- fix grand total when print gra checkout
- fix gra printing bugs (when print gra with item not in sku all content wrong.)

22/7/2009 5:22:43 PM yinsee
exclude items that cannot check out when viewing in gra checkout

8/21/2009 4:59:46 PM Andy
- edit printing setting, default item per page change from 9 to 15

8/28/2009 10:16:17 AM Andy
default item per page change to 14
- fix printing bug

1/4/2010 4:52:22 PM Andy
- reset all smarty variable when print another copy

1/13/2010 5:32:39 PM Andy
- Fix the rows num bugs when printing 

3/29/2010 12:05:00 PM Andy
- Fix GRA can multiple checkout bugs

11/15/2010 11:48:00 AM Justin
- Added GRA's info to take branch code.

1/27/2011 5:06:43 PM Andy
- Fix GRA printing show wrong price type.

7/6/2011 11:45:11 AM Andy
- Change split() to use explode()

8/2/2011 3:08:22 PM Justin
- Added to pick up sku item's doc decimal point.
- Fixed the windows.location that does not update the qty (cache problem) once the system has updated the GRA item.
- Modified the rounding up as 2 decimal points for amount updates.

2/16/2012 4:11:58 PM Andy
- Show branch code of GRA in notification title and viewing page.

4/23/2012 3:00:35 PM Alex
- add packing uom code => case "view"

7/18/2012 5:16:12 PM Justin
- Added ajax search GRA function.
- Enhanced to have GRA dispose function.

7/25/2012 12:25 PM Justin
- Added to pick up packing UOM fraction while printing report.

8/7/2012 4:40 PM Justin
- Enhanced to capture disposal date.

2/18/2013 10:11 AM Justin
- Bug fixed on system did not shows out item not in ARMS list during view GRA from checkout.

2/20/2013 5:21 PM Justin
- Modified the next disposal date will add 29 days but not 1 month.

7/9/2013 5:55 PM Justin
- Enhanced to query and show approval history if have any.

07/19/2013 11:26 AM Justin
- Enhanced to always update approved=1 while confirm to checkout.

7/31/2013 10:25 AM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/27/2013 4:50 PM Justin
- Bug fixed for send PM no longer working while not using approval flow.
- Bug fixed on owner / notify user did not get the correct approval settings.

4/4/2014 9:55 AM Justin
- Bug fixed on if user did not tick the item for return during checkout, items will stuck and this item will calculated as n/A qty for GRA.

5/12/2014 2:27 PM Fithri
- allow user to select Returned Date

11/6/2014 10:03 AM Justin
- Bug fixed on item that do not tick for checkout will no longer can be found under GRA item list.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/9/2015 3:35 PM Justin
- Enhanced to set D/N to cancel if found GRA has been reset or cancelled.

4/18/2015 4:19 PM Justin
- Enhanced to pickup more info from item not in ARMS.

5/8/2015 10:51 AM Justin
- Enhanced to capture and printout document date.
- Enhanced to unset GST amount while printing report.

5/14/2015 4:04 PM Justin
- Bug fixed on selling price always round up wrongly.

11/30/2015 9:43 PM DingRen
- when save  recalculate amount_gst, gst and amount and change to decimal 2

12/24/2015 9:55 AM Qiu Ying
- SKU Additional Description should show in document printing

1/27/2016 9:55 AM Qiu Ying
- Fix wrong calculation in "ajax_edit_item"

3/3/2016 3:07 PM Qiu Ying
- Fix print not show empty row

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

10/3/2018 5:46 PM Justin
- Enhanced to hide Inv No and Date as if the GRA doesn't have any information of it.

12/6/2018 10:43 AM Justin
- Enhanced to have Rounding Adjust.

05/06/2019 3:20 PM Liew
- Old code not appear on GRA Checkout screen (refer screenshot GRA - checkout)

5/17/2019 10:41 AM William
- Pickup report_prefix for enhance "GRA".
*/
include("include/common.php");
require("goods_return_advice.include.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA_CHECKOUT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA_CHECKOUT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

if (isset($_REQUEST['branch_id']))
	$branch_id = intval($_REQUEST['branch_id']);
else
	$branch_id = $sessioninfo['branch_id'];

$smarty->assign("PAGE_TITLE", "GRA Checkout");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    // --------- adding SKU to GRA ------------
		case 'ajax_load_gra_list':
			load_gra_list("goods_return_advice.checkout.list.tpl",true);
			exit;
			
		case 'cancel':
			$comment = ms($_REQUEST['comment']);
			$gra_id = intval($_REQUEST['id']);
			// TODO: use 'status' column for return/checkout status instead of 'returned' column
		    // save GRA info as CANCEL (set returned=2 in table gra)
			$con->sql_query("update gra set returned=0,status=1,remark=$comment where returned=0 and id = $gra_id and branch_id = $branch_id");

			//Pickup report_prefix
			$q1 = $con->sql_query($qry ="Select branch.report_prefix from branch where branch.id=$branch_id");
			while($r1 = $con->sql_fetchassoc($q1)){
				$report_prefix = $r1['report_prefix'];
			}
			$con->sql_freeresult($q1);
			if ($con->sql_affectedrows()>0)
			{
				$con->sql_query("insert into gra_items (branch_id, user_id, sku_item_id, vendor_id, qty, cost, return_type, reason,selling_price,doc_no,doc_date,gst_id,gst_code,gst_rate,gst_selling_price,amount,gst,amount_gst) select branch_id, user_id, sku_item_id, vendor_id, qty, cost, return_type,$comment,selling_price,doc_no,doc_date,gst_id,gst_code,gst_rate,gst_selling_price,amount,gst,amount_gst from gra_items where gra_id = $gra_id and branch_id = $branch_id");
				
				$con->sql_query("update dnote set active=0 where ref_table = 'gra' and ref_id = ".mi($gra_id)." and branch_id = ".mi($branch_id));
			}
			header("Location: /goods_return_advice.checkout.php?t=cancel&id=$gra_id&report_prefix=$report_prefix");
			exit;
		
		case 'ajax_edit_item':
			$gra_id = intval($_REQUEST['id']);
			$id = intval($_REQUEST['gra_item_id']);
			$gra_id = intval($_REQUEST['id']);
			$qty = intval($_REQUEST['qty']);
			$cost = $_REQUEST['cost'];

			$con->sql_query("select * from user where active AND NOT template AND id= ".mi($_REQUEST['user_id'])." and p = md5(".ms($_REQUEST['password']).")");
			if ($r = $con->sql_fetchrow()){
                $con->sql_query("select gi.*,g.sku_type,g.is_under_gst from gra_items gi
                                left join gra g on g.id=gi.gra_id and g.branch_id=gi.branch_id
                                where gi.id=$id and gi.gra_id = $gra_id and gi.branch_id = $branch_id");
                $gra_item=$con->sql_fetchrow();
                $gst=$gra_item['gst'];
                $amount_gst=$gra_item['amount_gst'];

                if($gra_item['sku_type']=='CONSIGN')
                    $amount = $qty * $gra_item['selling_price'];
                else{
                    $amount = $qty * $cost;
				}
                if($gra_item['is_under_gst']){
                    $amount_gst=round($amount * ((100+$gra_item['gst_rate'])/100),2);
                    $gst=$amount_gst-round($amount, 2);
                }
                $amount=round($amount,2);
				$con->sql_query("update gra_items set qty=$qty,cost=$cost,amount=$amount,gst=$gst,amount_gst=$amount_gst where id=$id and gra_id = $gra_id and branch_id = $branch_id");
				
				$con->sql_query("select gra.extra_amount, sum(gi.amount) as amount from gra left join gra_items gi on gra.id = gi.gra_id and gra.branch_id = gi.branch_id where gra.branch_id = $branch_id and gra.id = $gra_id");
				$gra=$con->sql_fetchrow();
				$extra_amount = $gra["extra_amount"];
				$total_amount = $gra["amount"] + $extra_amount;
				
				$con->sql_query("update gra set amount = $total_amount where id = $gra_id and branch_id = $branch_id");

				header("Location: $_SERVER[PHP_SELF]?a=open&id=$gra_id");
			}
			else{
				echo "<script>alert('Invalid Password!!!\\nYou are not allowed make any changes');</script>";
				echo "<script>window.location = 'goods_return_advice.checkout.php?a=open&id=$gra_id'</script>";
			}
			exit;

        // --------- Create/Modify GRA ------------
		
		case 'open':
			$id = intval($_REQUEST['id']);
			$gra=generate_gra_list($id, $branch_id);
			if ($gra['return_timestamp'] == '0000-00-00 00:00:00') $gra['return_timestamp'] = date('Y-m-d H:i:s');
			$smarty->assign("form", $gra);

			/*
			print '<pre>';
			print_r($gra);
			print '</pre>';
			*/
			if ($gra['returned']>0)
				header("Location: $_SERVER[PHP_SELF]?a=view&id=$id"); // switch to readonly mode
			else
				$smarty->display("goods_return_advice.checkout.open.tpl");
			exit;

		case 'view':
		case 'print':
			
			
			$id = intval($_REQUEST['id']);
			
			// 11:16 AM 12/6/2018 disbled by Justin since the calculation has been moved to "confirm" function
			// update the GRA amount
			/*$con->sql_query("select sum(qty*cost) from gra_items where gra_id=$id and branch_id = $branch_id");
			$amt = $con->sql_fetchrow();
			$con->sql_query("update gra set last_update=last_update,amount=".mf(round($amt[0], 2))."+extra_amount+rounding_adjust where id=$id and branch_id = $branch_id");*/

			$q1 = $con->sql_query("select gra.*, branch.code as branch_code,branch.report_prefix, user.u, vendor.description as vendor, 
								   category.description as dept_code, vendor.code as vendor_code,
								   if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
								   from gra
								   left join user on gra.user_id = user.id
								   left join vendor on gra.vendor_id = vendor.id
								   left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = gra.branch_id
								   left join category on gra.dept_id = category.id
								   left join branch on branch.id = gra.branch_id
								   where gra.id = $id and gra.branch_id = $branch_id");

			$gra = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
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
						
						if(trim($gra['extra']['doc_no'][$idx]) || $gra['extra']['doc_date'][$idx] != 0) $igi_have_doc_info = true;
					  }
				}
				
				if($gra['approval_history_id']>0){
					$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u 
										from branch_approval_history_items i 
										left join branch_approval_history h on i.approval_history_id=h.id and i.branch_id=h.branch_id 
										left join user on i.user_id = user.id 
										where h.ref_table = 'gra' and i.branch_id=".mi($branch_id)." and (h.ref_id=".mi($id)." or h.id = ".mi($gra['approval_history_id']).")
										order by i.timestamp");

					$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
					$con->sql_freeresult($q2);
				}
				
				/*if($gra['return_timestamp'] == 0 || !$gra['returned_by']) $gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
				else $gra['disposal_date'] = $gra['return_timestamp'];*/
				
				$gra['disposal_date'] = date("Y-m-d H:i:s", strtotime("+29 day", strtotime($gra['added'])));
				
				$q1 = $con->sql_query("select gra_items.*, sku_item_code, artno, mcode,sku_items.link_code, sku_items.additional_description, sku_items.description as sku, if(sip.price, sip.trade_discount_code, sku.default_trade_discount_code) as price_type,if(sip.price, sip.price, sku_items.selling_price) as selling_price, sku_items.doc_allow_decimal, puom.code as packing_uom_code,
				if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax
from gra_items
left join sku_items on gra_items.sku_item_id = sku_items.id
left join sku on sku_items.sku_id=sku.id
left join category_cache cc on cc.category_id=sku.category_id
left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id =gra_items.branch_id
left join uom puom on puom.id=sku_items.packing_uom_id
where gra_id = $id and gra_items.branch_id = $branch_id");
				$tmp_gra_items = array();
				$gi_have_doc_info = false;
				while($r = $con->sql_fetchassoc($q1)){
					if(trim($r['doc_no']) || $r['doc_date'] != 0) $gi_have_doc_info = true;
					
					// calculate the net selling price as if under gst mode
					if($gra['is_under_gst']){
						$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
						$gst_info = get_sku_gst("output_tax", $r['sku_item_id']);
						
						if($is_inclusive_tax == "yes"){
							$gst_tax_price = round($r['selling_price'] / ($gst_info['rate']+100) * $gst_info['rate'], $config['global_cost_decimal_points']);
							$r['selling_price'] -= $gst_tax_price;
						}
					}
					$tmp_gra_items[] = $r;
				}
				$con->sql_freeresult($q1);
				$gra['items'] = $tmp_gra_items;
				//echo"<pre>";print_r($gra);echo"</pre>";
				$smarty->assign("form", $gra);
			    $smarty->assign("new", $new);
			    $smarty->assign("igi_have_doc_info", $igi_have_doc_info);
			    $smarty->assign("gi_have_doc_info", $gi_have_doc_info);

			
				// update printing counter
				if ($_REQUEST['a']=='print'){
					if($_REQUEST['own_copy'])    $copy[] = 'own';
					if($_REQUEST['vendor_copy'])    $copy[] = 'vendor_copy';
					$con->sql_query("update gra set last_update=last_update, print_counter=print_counter+1 where gra.id = $id and branch_id = $branch_id");
					
					$con->sql_query("select * from branch where id = $branch_id");
					$smarty->assign("branch", $con->sql_fetchrow());
					
					$GRA_ITEMS_PER_PAGE = ($config['gra_print_item_per_page']>0)?$config['gra_print_item_per_page']:14;
					$GRA_ITEMS_PER_LAST_PAGE = $GRA_ITEMS_PER_PAGE;
					
					$totalpage_sku = 1 + ceil((count($gra['items'])-$GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
					$totalpage_nonsku =  1 + ceil((count($new)-$GRA_ITEMS_PER_LAST_PAGE)/$GRA_ITEMS_PER_PAGE);
					$totalpage=$totalpage_sku+$totalpage_nonsku;
                    
					/*print "<pre>";
					print_r($gra);
					print "</pre>";*/
					
					
					// start print GRA
					$item_index = -1;
					$item_no = -1;
					$page = 1;
					
					$page_item_list = array();
					$page_item_info = array();
				
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
				
					// fix last page
					if(count($page_item_list[$page]) > $GRA_ITEMS_PER_LAST_PAGE){	
						$page++;
						$page_item_list[$page] = array();
					}
					
					$totalpage = count($page_item_list) + $totalpage_nonsku;
					
					foreach($copy as $cp_type){
					    $smarty->clear_assign(array("ttl_qty", "ttl_amt", "total_qty","total_amt", "total_gst","total_gst_amt","ttl1_qty","ttl1_amt","total1_qty","total1_amt","total1_gst","total1_gst_amt"));
					    $smarty->assign('copy_type',$cp_type);
                        if ($gra['items']){
							foreach($page_item_list as $page => $item_list){
								$this_page_num = ($page < $totalpage) ? $GRA_ITEMS_PER_PAGE : $GRA_ITEMS_PER_LAST_PAGE;
                                $smarty->assign('show_total',1);
								$smarty->assign("is_lastpage", ($page >= $totalpage));
								$smarty->assign("page", "Page $page of $totalpage");
								$smarty->assign("PAGE_SIZE", $this_page_num);
								$smarty->assign("start_counter",$item_list[0]['item_no']);
								$smarty->assign("new", "");
								$smarty->assign("items", $item_list);
								$smarty->assign("page_item_info", $page_item_info[$page]);
								//if($sessioninfo['u']=='wsatp')	print('print4');
								if($config['gra_alt_print_template'])   $smarty->display($config['gra_alt_print_template']);
								else	$smarty->display('goods_return_advice.print.tpl');
								//if($sessioninfo['u']=='wsatp')	print('print5');
								$smarty->assign("skip_header",1);
								//if($sessioninfo['u']=='wsatp')	print('print6');
								$total_p=$page;
							}
                        	/*
							for($i=0,$p=0;$p<$totalpage_sku;$i+=$GRA_ITEMS_PER_PAGE,$p++){
								$page = $p+1;
								/*for($k=0;$k<2;$k++){
									if($k==0)$smarty->assign("vendor_copy",1);
									else $smarty->assign("vendor_copy",0);
									$smarty->assign("is_lastpage", ($page >= $totalpage_sku));
							        $smarty->assign("page", "Page $page of $totalpage");
							        $smarty->assign("start_counter", $i);
							        $smarty->assign("items", array_slice($gra['items'],$i,$GRA_ITEMS_PER_PAGE));
									$smarty->display('goods_return_advice.print.tpl');
									$smarty->assign("skip_header",1);
								}

								$smarty->assign('show_total',1);
								$smarty->assign("is_lastpage", ($page >= $totalpage));
						        $smarty->assign("page", "Page $page of $totalpage");
						        if($page >= $totalpage){
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
    							}else{
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);
    							}
						        $gra_items = array_slice($gra['items'],$i,$GRA_ITEMS_PER_PAGE);
						        $smarty->assign("start_counter", $i);
						        $smarty->assign("new", "");
						        $smarty->assign("items", $gra_items);
						        
						        //if($sessioninfo['u']=='wsatp')	print('print4');
						        if($config['gra_alt_print_template'])   $smarty->display($config['gra_alt_print_template']);
								else	$smarty->display('goods_return_advice.print.tpl');
								//if($sessioninfo['u']=='wsatp')	print('print5');
								$smarty->assign("skip_header",1);
								//if($sessioninfo['u']=='wsatp')	print('print6');
								$total_p=$page;
							}*/
							
						}
						if ($new){
							for($j=0,$p=0;$p<$totalpage_nonsku;$j+=$GRA_ITEMS_PER_PAGE,$p++){
								$page = $p+1;
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
						        $smarty->assign("start_counter", $j);
						        if($no_page >= $totalpage){
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_LAST_PAGE);
								}else{
									$smarty->assign("PAGE_SIZE", $GRA_ITEMS_PER_PAGE);
    							}
								$smarty->assign("new", array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
/*						        print "<pre>";
						        print_r(array_slice($new,$j,$GRA_ITEMS_PER_PAGE));
						        print "</pre>";
*/						        $smarty->assign("items", "");
								
						        if($config['gra_alt_print_template'])   $smarty->display($config['gra_alt_print_template']);
								else	$smarty->display('goods_return_advice.print.tpl');
								$smarty->assign("skip_header",1);
							}
						}
					}
				}
				else{
					$smarty->display("goods_return_advice.view.tpl");
				}
			}
			else{
			    $smarty->assign("url", "/goods_return_advice.checkout.php");
			    $smarty->assign("title", "GRA Checkout");
			    $smarty->assign("subject", sprintf($LANG['GRA_INVALID_GRA_NO'], $id));
			    $smarty->display("redir.tpl");
			}
			
			exit;

		case 'confirm':
			$form = $_REQUEST;
			/*
			print '<pre>';
			print_r($form);
			print '</pre>';
			die;
			*/
			$gra_id = intval($_REQUEST['id']);
			$err = validate_data($form);
			if (!$form['return_timestamp']) $err['sheet'][] = 'Returned Date cannot be empty';
			if ($err)
			{
				$gra=generate_gra_list($gra_id, $branch_id);
				$gra = array_merge($gra, $form);
				$smarty->assign("form",$gra);
				$smarty->assign("errm",$err);
				$smarty->display("goods_return_advice.checkout.open.tpl");
				exit;
			}

			$form['misc_info'] = serialize($form['misc_info']);
			//$form['return_timestamp'] = 'CURRENT_TIMESTAMP';
            $form['approved'] = 1;
			
			if(!$form['currency_code']) $form['currency_rate'] = 1; // always insert as 1 if not choosing foreign currency
			
			// save GRA info
			$con->sql_query("update gra set ".mysql_update_by_field($form, array("transport","returned","misc_info","returned_by", "return_timestamp", "remark", "approved", "currency_rate", "amount", "rounding_adjust"))." where id = $gra_id and branch_id = $branch_id");

			// update GRA item checkout bit
			foreach ($form['reason'] as $k=>$v)
			{
				$k = intval($k);
				if($form[checkout][$k]){
					$reason="''";
				}
				else{
					$temp_reason=ms($form[reason][$k]);
					if($temp_reason=="'other'")
				    	$reason=ms($form[untick_reason][$k]);
					else
				    	$reason=$temp_reason;
				}
				if($form[checkout][$k]){
					$con->sql_query("update gra_items set checkout=1,reason=$reason where id=$k and gra_id = $gra_id and branch_id = $branch_id");
				}
				else{
					$con->sql_query("update gra_items set gra_id=0,reason=$reason,batchno=0,status=0,temp_gra_uid=0 where id=$k and gra_id = $gra_id and branch_id = $branch_id");
				}

			}
			// duplicate those not checkout
			$con->sql_query("insert into gra_items (branch_id,user_id,sku_item_id,vendor_id,qty,cost,return_type,selling_price,doc_no,doc_date,gst_id,gst_code,gst_rate,gst_selling_price,amount,gst,amount_gst) select branch_id,user_id,sku_item_id,vendor_id,qty,cost,return_type,selling_price,doc_no,doc_date,gst_id,gst_code,gst_rate,gst_selling_price,amount,gst,amount_gst from gra_items where checkout=0 and gra_id = $gra_id and branch_id = $branch_id");
			if ($con->sql_affectedrows()>0){
				$rr = " / " . $con->sql_affectedrows() . " item(s) copied to unused SKU.";
			}
			else{
				$rr = '';
			}
			
            set_sku_items_cost_changed($gra_id, $branch_id);
            
			//update sku_item_cost
			//$con->sql_query("update sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (select sku_item_id from gra_items left join gra on gra_id = gra.id and gra_items.branch_id = gra.branch_id where gra_items.gra_id=$gra_id and gra_items.branch_id=$branch_id and gra_items.checkout=1 and gra.status=0 and gra.returned)");
			//$abc=$con->sql_affectedrows();
			//echo $abc;


			// which departments these item are from?
			/*$con->sql_query("select distinct(department_id) from gra_items left join sku_items on sku_item_id = sku_items.id left join sku on sku_id = sku.id left join category on category_id = category.id where gra_id = $gra_id and branch_id = $branch_id");
			$depts = array();
			while($r=$con->sql_fetchrow())
			{
			    $depts[] = $r[0];
			}
			if ($depts)
				$depts = " and sku_category_id in (".join(",",$depts).")";
			else
			    $depts = "";*/
			    			
		    // PM the GRA owner and notify users
		    $con->sql_query("select gra.user_id,gra.approval_history_id,bah.approval_settings, gra.dept_id,branch.report_prefix
		    from gra 
			left join branch on gra.branch_id=branch.id
		    left join branch_approval_history bah on bah.branch_id=gra.branch_id and bah.id=gra.approval_history_id
		    where gra.id = $gra_id and gra.branch_id = $branch_id");
		    $gra = $con->sql_fetchrow();
		    $gra['approval_settings'] = unserialize($gra['approval_settings']);
			$report_prefix = $gra['report_prefix'];
		    $con->sql_freeresult();
			
			// add to log
		    log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("GRA Checkout ".$report_prefix."%05d - ".get_branch_code($branch_id)." $rr",$gra_id));
			
		    /*$notify = array();
		    $notify[] = $gra['user_id'];
		    
		    $con->sql_query("select notify_users from approval_flow where branch_id=$branch_id and type='GOODS_RETURN_ADVICE' and active=1 $depts");
		    $r=$con->sql_fetchrow();
	        $notify += explode("|",$r[0]);
			send_pm($notify, sprintf("GRA Checkout (GRA%05d) - ".get_branch_code($branch_id)." $rr",$gra_id), "/goods_return_advice.php?a=view&id=$gra_id&branch_id=$branch_id");*/

			$to = array();
			if(!$config['gra_no_approval_flow']){
				$to = get_pm_recipient_list2($gra_id, $gra['approval_history_id'], 0, 'approval', $branch_id, 'gra');
				
				// add owner if not in list
				if(!$to[$gra['user_id']]){
					$tmp = array();
					$tmp['user_id'] = $gra['user_id'];
					$tmp['approval_settings'] = $gra['approval_settings']['owner'];
					$tmp['type'] = 'owner';
					$to[$gra['user_id']] = $tmp;
				}
			}else{
				$q1 = $con->sql_query("select notify_users, approval_settings from approval_flow where branch_id=$branch_id and type='GOODS_RETURN_ADVICE' and active=1 and sku_category_id = ".mi($gra['dept_id']));
				$r = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$as['approval_settings'] = unserialize($r['approval_settings']);
				
				// owner settings
			    $to[$gra['user_id']]['user_id'] = $gra['user_id'];
				$to[$gra['user_id']]['approval_settings'] = $as['approval_settings']['owner'];
				$to[$gra['user_id']]['type'] = "owner";

				// pickup users from approval flow
				$notify_users = explode("|", $r['notify_users']);
				foreach($notify_users as $dummy=>$uid){
					if(!$uid) continue;
					$to[$uid]['user_id'] = $uid;
					$to[$uid]['approval_settings'] = $as['approval_settings']['notify'][$uid];
					$to[$uid]['type'] = "notify";
				}
			}

			send_pm2($to, sprintf("GRA Checkout (GRA%05d) - ".get_branch_code($branch_id)." $rr",$gra_id), "/goods_return_advice.php?a=view&id=$gra_id&branch_id=$branch_id", array('module_name'=>'gra'));
				
			// after save , return to front page
			header("Location: $_SERVER[PHP_SELF]?t=confirm&id=$gra_id&report_prefix=$report_prefix");
			exit;
		case 'ajax_search_gra':
			ajax_search_gra();
			exit;
		case 'gra_disposal':
			gra_disposal();
			exit;
		case 'print_arms_dn':
			print_arms_dn($_REQUEST['branch_id'], $_REQUEST['id']);
			exit;
		case 'loadCurrencyRate':
			loadCurrencyRate();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

get_gra_items();
$con->sql_query("select id,description from vendor order by description");
$smarty->assign("vendors",$con->sql_fetchrowset());
$smarty->assign("vendor_id",$_REQUEST['vendor_id']);
$smarty->display("goods_return_advice.checkout.tpl");

function validate_data(&$form)
{
	global $LANG, $branch_id, $con, $sessioninfo;

    $gra_id = intval($form['id']);
	$branch_id = intval($form['branch_id']);
	
	$err = array();

	$form['returned'] = 1;
	$form['branch_id'] = $branch_id;
	$form['returned_by'] = $sessioninfo['id'];

	// make sure have items
	if (!$form['checkout'])
	{
		$err['sheet'][] = $LANG['GRA_NO_ITEMS'];
	}
	
	
	$con->sql_query("select returned from gra where id=$gra_id and branch_id=$branch_id");
	if($con->sql_fetchfield(0)==1)  js_redirect(sprintf($LANG['GRA_ALREADY_CHECKOUT'],"'#".$gra_id."'"), "/goods_return_advice.checkout.php");
	
	return $err;
}

function generate_gra_list($id, $branch_id){
	global $con, $smarty, $config, $appCore;

	$q1 = $con->sql_query("select gra.*, user.u, vendor.description as vendor,category.description as dept_code,
						   branch.code as branch_code,branch.report_prefix 
						   from gra
						   left join user on gra.user_id = user.id
						   left join vendor on gra.vendor_id = vendor.id
						   left join category on gra.dept_id = category.id
						   left join branch on branch.id=gra.branch_id
						   where gra.id = $id and branch_id = $branch_id");
	$gra = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);

	if ($gra){
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
				$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
				$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
				$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
			}
		}
		$q1 = $con->sql_query("select gra_items.*, sku_item_code, artno, mcode, link_code, sku_items.description as sku, 
							   if(sip.price, sip.price, sku_items.selling_price) as selling_price, sku_items.doc_allow_decimal,puom.code as packing_uom_code
							   from gra_items 
							   left join sku_items on gra_items.sku_item_id = sku_items.id 
							   left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id = gra_items.branch_id
							   left join uom puom on puom.id=sku_items.packing_uom_id
							   where gra_id = $id and gra_items.branch_id = $branch_id");
		
		while($r = $con->sql_fetchassoc($q1)){
			// calculate the net selling price as if under gst mode
			if($gra['is_under_gst']){
				$is_inclusive_tax = get_sku_gst("inclusive_tax", $r['sku_item_id']);
				$gst_info = get_sku_gst("output_tax", $r['sku_item_id']);
				
				if($is_inclusive_tax == "yes"){
					$gst_tax_price = round($r['selling_price'] / ($gst_info['rate']+100) * $gst_info['rate'], $config['global_cost_decimal_points']);
					$r['selling_price'] -= $gst_tax_price;
					$r['inclusive_tax'] = 'yes';
				}else{
					$r['inclusive_tax'] = 'no';
				}
			}
			$gra_items[] = $r;
		}
		$con->sql_freeresult($q1);
		$gra['items'] = $gra_items;
		//echo"<pre>";print_r($gra);echo"</pre>";
		
		if($gra['approval_history_id']>0){
			$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u 
								from branch_approval_history_items i 
								left join branch_approval_history h on i.approval_history_id=h.id and i.branch_id=h.branch_id 
								left join user on i.user_id = user.id 
								where h.ref_table = 'gra' and i.branch_id=".mi($branch_id)." and (h.ref_id=".mi($id)." or h.id = ".mi($gra['approval_history_id']).")
								order by i.timestamp");

			$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
			$con->sql_freeresult($q2);
		}
		
		// need to load latest currency rate while doing checkout (except gra already have returned_timestamp)
		if($gra['currency_code'] && !$gra['returned_timestamp']){
			$date = date("Y-m-d");
			$ret = $appCore->currencyManager->loadCurrencyRateByDate($date, $gra['currency_code']);
			
			// if found new rate is not null and it's different with the one captured from draft gra
			// then need to use it as new currency rate
			if($ret['rate'] > 0 && $ret['rate'] != $gra['currency_rate']){
				$gra['old_currency_rate'] = $gra['currency_rate'];
				$gra['currency_rate'] = $ret['rate'];
			}
		}
		
		$smarty->assign("new", $new);
		return $gra;
	}
	else
	{
	    $smarty->assign("url", "/goods_return_advice.checkout.php");
	    $smarty->assign("title", "Goods Return Advice");
	    $smarty->assign("subject", sprintf($LANG['GRA_INVALID_GRA_NO'], $id));
	    $smarty->display("redir.tpl");
	}
}

function ajax_search_gra(){
	global $con, $smarty, $sessioninfo;

	$form = $_REQUEST;
	if(!$form['due_date']) return;
	$filter = " and gra.branch_id = ".mi($sessioninfo['branch_id']);
	
	$curr_date = date("Y-m-d");
	$q1 = $con->sql_query("select gra.*, v.code as vd_code, c.description as dept_code, b.code as bcode,b.report_prefix 
						   from gra 
						   left join branch b on b.id = gra.branch_id
						   left join vendor v on v.id = gra.vendor_id
						   left join category c on c.id = gra.dept_id and c.level = 2
						   where gra.status = 0 and gra.returned = 0 and
						   date_format(gra.added, '%Y-%m-%d') between ".ms($form['due_date'])." and ".ms($curr_date).$filter."
						   order by gra.last_update desc");

	while($r = $con->sql_fetchassoc($q1)){
		$items[] = $r;
	}
	
	if($items){
		$smarty->assign("items", $items);
		$smarty->display("goods_return_advice.checkout.dispose_list.tpl");
	}
}

function gra_disposal(){
	global $con, $sessioninfo;
	
	$form = $_REQUEST;

	if($form['dispose_item']){
		foreach($form['dispose_item'] as $gra_id=>$bid){
			//Pickup report_prefix
			$q1 = $con->sql_query("Select branch.report_prefix from branch where branch.id=".mi($bid));
			while($r1 = $con->sql_fetchassoc($q1)){
				$report_prefix = $r1['report_prefix'];
			}
			$con->sql_freeresult($q1);
			// update gra to mark it as disposal
			$con->sql_query("update gra set returned=1, type='Disposal', last_update=CURRENT_TIMESTAMP, returned_by=".mi($sessioninfo['id']).", return_timestamp=CURRENT_TIMESTAMP where id = ".mi($gra_id)." and branch_id = ".mi($bid));

			// update gra items to set batchno > 0
			$con->sql_query("update gra_items set batchno=batchno+1 where gra_id = ".mi($gra_id)." and branch_id = ".mi($bid));
			
			// add to log
		    log_br($sessioninfo['id'], 'GRA', $gra_id, sprintf("GRA Checkout ".$report_prefix."%05d - Disposal ".get_branch_code($bid),$gra_id));
			
			$gra_info[] = sprintf($report_prefix."%05d - ".get_branch_code($bid),$gra_id);
		}
		$url = "t=dispose&gra_list=".urlencode(join(", ", $gra_info));
	}
	
	header("Location: /goods_return_advice.checkout.php?".$url);
}
