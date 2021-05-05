<?php
/*
9/25/2007 10:57:08 AM - yinsee
- if find code start with 2, only take first 12-characters 

11/25/2007 - yinsee
- if find code is 8 digit, search the first 7 digit in link_code (ATP Code)

5/14/2008 11:29:59 AM yinsee
- add login or intranet access checking

4/22/2009 12:04:45 PM yinsee
- Tracker #23: Front End module not allow scan for sku status is in-active.
 
12/28/2009 9:32:03 AM edward
- add location and qty balance.

8/13/2010 10:07:46 AM Andy
- Add Replacement Item Group popup in front end check code.

3/15/2011 2:57:29 PM Justin
- Added the filter for selling price of inactive branch.

5/4/2011 5:40:22 PM Justin
- Added to look up for Batch and Serial No.

5/31/2011 1:03:30 PM Justin
- Rename the "grn_batch_items" into "sku_batch_items".

6/24/2011 4:07:49 PM Andy
- Make all branch default sort by sequence, code.

2/29/2012 11:54:43 AM Justin
- Added to pickup Price Type

9/7/2012 4:37:PM Fithri
- add offer price after the selling price

11/15/2012 2:26 PM Andy
- Add checking to first query filter, if first query filter return no result then change the filter not to substr mode.

11/23/2012 6:11 PM Justin
- Enhanced to recognize weighing scale code set from POS Settings. 

2/27/2013 3:20 PM Fithri
- show stock balance - deduct unfinalized qty (stock with un-finalize sales)

3/27/2015 3:01 PM Andy
- Enhanced to check GST price.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

1/29/2019 4:48 PM Andy
- Fixed mprice show as 'Array' bug.
*/
include("include/common.php");
if (!intranet_or_login()) js_redirect($LANG['ACCESS_DENIED_NEED_LOGIN_OR_INTRANET'], "/index.php");

if (isset($_REQUEST['a']))
{
switch($_REQUEST['a'])
{

	case 'find' :

		
		$sdesc = trim($_REQUEST['code']);
		$show_child = intval($_REQUEST['show_child']);
		$bid = get_request_branch();
			
		if ($sdesc != '')
		{
			$ll = preg_split("/\s+/", $sdesc);
			/*$desc_match = array();
			foreach ($ll as $l)
			{
			    if ($l) $desc_match[] = "sku_items.description like " . ms('%'.$l.'%');
			}
			$desc_match = join(" and ", $desc_match);*/
			
			$linkcode = $sdesc;
			if (strlen($sdesc)==8)
				$linkcode = substr($sdesc,0,7);
			else if (preg_match("/^2/",$sdesc))
				$linkcode = substr($sdesc,0,12);

			if(!$sessioninfo['branch_id']){
				$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
				$sessioninfo['branch_id'] = $con->sql_fetchfield(0);
				$con->sql_freeresult();
			}
			if($config['enable_sn_bn']){
				
				if(BRANCH_CODE != 'HQ' && $sessioninfo['branch_id']) $bfilter = " and branch_id = ".mi($sessioninfo['branch_id']);
				// search for serial no
				$con->sql_query("select sku_item_id from pos_items_sn where serial_no like ". ms("%".replace_special_char($sdesc)."%") . " and active = 1".$bfilter);
				
				while($r = $con->sql_fetchrow()){
					$sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
				}

				// search for batch no
				$con->sql_query("select sku_item_id from sku_batch_items where batch_no like ". ms("%".replace_special_char($sdesc)."%") . " and sku_item_id != 0".$bfilter);

				while($r = $con->sql_fetchrow()){
					$sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
				}
				
				if($sku_item_id_list) $extra_filter = " or sku_items.id in (".join(",", $sku_item_id_list).")";
			}
			
			if(!$extra_filter){
				$sku_info=get_grn_barcode_info($sdesc,false);
			}

			if($sku_info['sku_item_id']) $where = "sku_items.id = ".mi($sku_info['sku_item_id']);
			else{
				// searching sql
				//$where = "(($desc_match) or sku_items.link_code = ".ms($linkcode)." or sku_items.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or sku_items.sku_item_code like ".ms("%$sdesc%")." or sku_items.artno = ".ms($sdesc)." or sku_items.mcode = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc).")";
				$total_mcode_item=0;
				if (strlen($sdesc) == 13){
					$where = "sku_items.active=1 and sku_items.mcode = ".ms($sdesc);

					// check whether this filter got item
					$con->sql_query("select count(*)
										 from sku_items
										 where $where
										 limit 1");
					$t = $con->sql_fetchrow();
					$con->sql_freeresult();
					$total_mcode_item = $t[0];
				}

				if($total_mcode_item==0){
					$where = "sku_items.active=1 and (sku_items.link_code = ".ms($linkcode)." or sku_items.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or sku_items.sku_item_code like ". ms("%".replace_special_char($sdesc)."%") . " or sku_items.artno like ". ms(replace_special_char($sdesc)."%") . " or sku_items.mcode = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." $extra_filter)";
				}
			}
			
			// check whether this filter got item
			$con->sql_query("select sku_items.id, sku_items.sku_id
								 from sku_items
								 where $where
								 limit 1");

			// this filter return no item
			if($con->sql_numrows()<=0){
				// remove the mcode substr 
				$where = "sku_items.active=1 and (sku_items.link_code = ".ms($linkcode)." or sku_items.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or sku_items.sku_item_code like ". ms("%".replace_special_char($sdesc)."%") . " or sku_items.artno like ". ms(replace_special_char($sdesc)."%") . " or sku_items.mcode = ".ms($sdesc)." $extra_filter)";
			}
			$con->sql_freeresult();
			
			if($show_child){ // found need to show child and replacement items
				$con->sql_query("select sku_items.id, sku_items.sku_id
								 from sku_items
								 where $where
								 order by sku_items.description limit 51");
				
				$ttl_rows = $con->sql_numrows();

				while($r = $con->sql_fetchrow()){
					$sku_id_list[$r['sku_id']] = $r['sku_id'];
				}
				$con->sql_freeresult();

				if($sku_id_list) $where = "sku_items.sku_id in (".join(",", $sku_id_list).")";
			}
			
			//get latest finalized date, use to get unfinalized stock balance
			$res3 = $con->sql_query("select max(date) as maxdate from pos_finalized where finalized = 1 and branch_id = ".mi($bid));
			$r3 = $con->sql_fetchassoc($res3);
			$last_finalized_date = $r3['maxdate'];
			//var_dump($last_finalized_date);
			
			$res1 = $con->sql_query("select sku_items.*, sku_apply_items.photo_count, if(sip.price is null, 
									 sku_items.selling_price, price) as selling_price, sku.sku_type, vendor.description as vendor, brand.description as brand, branch.code as branch_code, branch.ip as branch_ip, sc.*,rii.ri_id, ri.group_name as ri_group_name, sku.default_trade_discount_code,
									 if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code
									 from sku_items
									 left join sku on sku_items.sku_id = sku.id
									 left join sku_items_price sip on sku_item_id = sku_items.id and branch_id = ".mi($bid)."
									 left join sku_items_cost sc on sc.sku_item_id = sku_items.id and sc.branch_id = ".mi($bid)."
									 left join brand on brand_id = brand.id 
									 left join vendor on vendor_id = vendor.id 
									 left join branch on apply_branch_id = branch.id
									 left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
									 left join ri_items rii on rii.sku_item_id=sku_items.id
									 left join ri on ri.id=rii.ri_id
									 where $where order by description limit 51");

			if ($con->sql_numrows($res1))
			{
				if($config['enable_gst']){
					$prms = array();
					$prms['branch_id'] = $sessioninfo['branch_id'];
					$is_under_gst = check_gst_status($prms);
				}
	
				if ($con->sql_numrows($res1)<=50)
					print "<p>Search of <b>$_REQUEST[code]</b> return ".$con->sql_numrows($res1)." result(s).</p>";
				else
				    print "<p>Search of <b>$_REQUEST[code]</b> return more than 50 result(s). Showing the first 50.</p>";
				$n=0;
				while($r=$con->sql_fetchrow($res1))
				{
				    $n++;
				    if ($n>50) break;
					$bn_filter = array();

					$hurl = get_branch_file_url($r['branch_code'], $r['branch_ip']);
					$r['image_path'] = $hurl;
				 	$r['photos'] = get_sku_item_photos($r['id'],$r);   

					$sku_item_id = mi($r['id']);
					
					if($is_under_gst){
						// get sku inclusive tax
						$r['is_sku_inclusive'] = get_sku_gst("inclusive_tax", $sku_item_id);
						
						// get sku original output gst
						$r['output_gst'] = get_sku_gst("output_tax", $sku_item_id);
						
						if($r['output_gst']){
							if($r['is_sku_inclusive'] == 'yes'){
								// is inclusive tax
								// find the selling price before tax
								//$gst_amt = round($r['selling_price'] / ($output_gst['rate']+100) * $output_gst['rate'], 2);
								//$price_included_gst = $r['selling_price'];
								//$before_tax_price = $price_included_gst - $gst_amt;
							}else{
								// is exclusive tax
								$before_tax_price = $r['selling_price'];
								$gst_amt = round($before_tax_price * $r['output_gst']['rate'] / 100, 2);
								$price_included_gst = $before_tax_price + $gst_amt;
								$r['selling_price'] = $price_included_gst;
							}
						}						
					}
					
					if (BRANCH_CODE=='HQ')
					{
						// get latest price
						$con->sql_query($def="select sku_items_price.*, branch.code, branch.active from sku_items_price left join branch on branch_id = branch.id where sku_item_id = $r[id] and branch.active = 1 order by branch.sequence, branch.code");
						//print $def.'<br />';
						while($p = $con->sql_fetchrow())
						{
							if(!$p['trade_discount_code']) $p['trade_discount_code'] = $r['default_trade_discount_code'];
							
							// gst
							if($is_under_gst){
								if($r['is_sku_inclusive'] == 'no'){
									// is exclusive tax
									$before_tax_price = $p['price'];
									$gst_amt = round($before_tax_price * $r['output_gst']['rate'] / 100, 2);
									$price_included_gst = $before_tax_price + $gst_amt;
									$p['price'] = $price_included_gst;
								}
							}
						    $r['price'][$p['code']] = $p;
						}
						
						//$r1 = $con->sql_query("select sku_items_cost.*, branch.code from sku_items_cost left join branch on branch_id = branch.id where sku_item_id = $r[id] order by branch_id");
						//while($r1 = $con->sql_fetchrow())
						//{
						//	$r['stock_balance'][$r1['code']] = $r1;
						//}
					}else{
						$bn_filter[] = "branch_id = ".mi($sessioninfo['branch_id']);
					}
					$bn_filter[] = "sku_item_id = ".mi($r['id']);
					$bn = $con->sql_query("select sbi.batch_no, sbi.expired_date, b.code from sku_batch_items sbi left join branch b on b.id = sbi.branch_id where ".join(" and ", $bn_filter)." order by b.sequence, b.code");

					while($b = $con->sql_fetchrow($bn)){
						$r['batch_no'] = $b['batch_no'];
						$r['expired_date'] = $b['expired_date'];
						$r['batch_items'][$b['code']] = $b['code'];
					}
					
					if ($config['sku_mprice_in_check_code']) {
					
						if (BRANCH_CODE=='HQ') {
						
							$mprice = $con->sql_query($qq="select branch_id, code, sku_items_mprice.type, price from sku_items_mprice left join branch on branch_id = branch.id where sku_item_id=$r[id] and branch.active = 1 order by code");
							
							if ($con->sql_numrows()) {
							
								while($row = $con->sql_fetchrow($mprice)) {
									// gst
									if($is_under_gst){
										if($r['is_sku_inclusive'] == 'no'){
											// is exclusive tax
											$before_tax_price = $row['price'];
											$gst_amt = round($before_tax_price * $r['output_gst']['rate'] / 100, 2);
											$price_included_gst = $before_tax_price + $gst_amt;
											$row['price'] = $price_included_gst;
										}
									}
									if (in_array($row['type'],$config['sku_mprice_in_check_code'])) {
										$r['mprice'][$row['type']][$row['code']] = $row['price'];
									}
								}
								$r['show_all_branches'] = true;
							
							}
							
							else {
								foreach ($config['sku_mprice_in_check_code'] as $type) {
									if (!isset($r['mprice'][$type])) $r['mprice'][$type] = $r['selling_price'];
								}
								$r['show_all_branches'] = false;
							}
							
							/*
							if (true) {
							
								$brq = $con->sql_query("select code from branch where branch.active = 1 order by code");
								while($br = $con->sql_fetchrow($brq)) {
									foreach ($config['sku_mprice_in_check_code'] as $type) {
										if (!isset($r['mprice'][$type][$br['code']])) $r['mprice'][$type][$br['code']] = $r['selling_price'];
									}
								}
								
							}
							*/
							//$smarty->assign("show_all_branches", $show_all_branches);
							
						}
						
						else {
							//$r[id] = 127522;
							$mprice = $con->sql_query($qq="select type, price from sku_items_mprice where branch_id = $bid and sku_item_id=$r[id]");
							//print $qq.'<br /><br />';
							
							while($row = $con->sql_fetchrow($mprice)) {
								// gst
								if($is_under_gst){
									if($r['is_sku_inclusive'] == 'no'){
										// is exclusive tax
										$before_tax_price = $row['price'];
										$gst_amt = round($before_tax_price * $r['output_gst']['rate'] / 100, 2);
										$price_included_gst = $before_tax_price + $gst_amt;
										$row['price'] = $price_included_gst;
									}
								}
									
								if (in_array($row['type'],$config['sku_mprice_in_check_code'])) {
									$r['mprice'][$row['type']] = $row['price'];
								}
							}
							
							foreach ($config['sku_mprice_in_check_code'] as $type) {
								if (!isset($r['mprice'][$type])) $r['mprice'][$type] = $r['selling_price'];
							}
						}
						
						if ($r['mprice']) ksort($r['mprice']);
						
					}
					
					//unfinalized stock balance
					$res2 = $con->sql_query("select ifnull(sum(pi.qty),0) as qty from pos p left join pos_items pi on p.id=pi.pos_id and p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date where p.branch_id = ".mi($bid)." and p.date > '$last_finalized_date' and p.cancel_status = 0 and pi.sku_item_id = ".$r['id']);
					//print $abc.'<br /><br />';
					$r2 = $con->sql_fetchassoc($res2);
					$r['unfinalize_qty'] = $r['qty'] - $r2['qty'];
					

					$items[] = $r;
				}

				$con->sql_freeresult();
				
				/*
				echo '<pre>';
				print_r($items);
				//print_r($config['sku_multiple_selling_price']);
				//print_r($config['sku_mprice_in_check_code']);
				//print BRANCH_CODE.'348637487';
				echo '</pre>';
				*/
				
				$smarty->assign("items", $items);
				$smarty->assign("is_under_gst", $is_under_gst);
				$smarty->assign("show_unfinalize_sb", (BRANCH_CODE != 'HQ'));
			    $smarty->display("front_end.check_code.items.tpl");
			}
			else
			{
			    print "<p>Search of <b>$_REQUEST[code]</b> return 0 result.</p>";
			}
		}
		else
		{
			print "Warning: Search string is empty";
		}
		exit;

 	default:
		print "Unknown Options";
		print_r($_REQUEST);
		exit;
}
}
$smarty->assign("PAGE_TITLE", "Check Code");
$smarty->display("front_end.check_code.tpl");
?>
