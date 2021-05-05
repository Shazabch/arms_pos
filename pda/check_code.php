<?php
/*
3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

2/27/2013 3:20 PM Fithri
- show stock balance - deduct unfinalized qty (stock with un-finalize sales)
*/
include("common.php");
if (!intranet_or_login()) js_redirect($LANG['ACCESS_DENIED_NEED_LOGIN_OR_INTRANET'], "/index.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
session_start();

if (isset($_REQUEST['a'])){
	if($_REQUEST['a'] == "find"){
		$sdesc = trim($_REQUEST['code']);
		$show_child = intval($_REQUEST['show_child']);
		$bid = get_request_branch();
			
		if ($sdesc != ''){
			$ll = preg_split("/\s+/", $sdesc);
			
			$linkcode = $sdesc;
			if (strlen($sdesc)==8) $linkcode = substr($sdesc,0,7);
			else if (preg_match("/^2/",$sdesc)) $linkcode = substr($sdesc,0,12);

				
			if(BRANCH_CODE != 'HQ') $bfilter = " and branch_id = ".mi($sessioninfo['branch_id']);
			// search for serial no
			$con->sql_query("select sku_item_id from pos_items_sn where serial_no like '%".$sdesc."%' and active = 1".$bfilter);
			
			while($r = $con->sql_fetchrow()){
				$sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
			}

			// search for batch no
			$con->sql_query("select sku_item_id from sku_batch_items where batch_no like '%".$sdesc."%' and sku_item_id != 0".$bfilter);

			while($r = $con->sql_fetchrow()){
				$sku_item_id_list[$r['sku_item_id']] = $r['sku_item_id'];
			}
			
			if($sku_item_id_list) $extra_filter = " or sku_items.id in (".join(",", $sku_item_id_list).")";

			// searching sql
			$where = "sku_items.active=1 and (sku_items.link_code = ".ms($linkcode)." or sku_items.sku_item_code = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." or sku_items.sku_item_code like ".ms("%$sdesc%")." or sku_items.artno like ".ms($sdesc.'%')." or sku_items.mcode = ".ms(preg_match("/^2/",$sdesc)?substr($sdesc,0,12):$sdesc)." $extra_filter)";

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
			
			$res1 = $con->sql_query("select sku_items.*, sku_apply_items.photo_count, if(sku_items_price.price is null, sku_items.selling_price, price) as selling_price, sku.sku_type, vendor.description as vendor, brand.description as brand, branch.code as branch_code, branch.ip as branch_ip, sc.*,rii.ri_id, ri.group_name as ri_group_name
				from sku_items left join sku on sku_items.sku_id = sku.id
				left join sku_items_price on sku_item_id = sku_items.id and branch_id = ".mi($bid)."
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
				if ($con->sql_numrows($res1)<=50)
					$msg = "<p>Search of <b>$_REQUEST[code]</b> return ".$con->sql_numrows($res1)." result(s).</p>";
				else
					$msg = "<p>Search of <b>$_REQUEST[code]</b> return more than 50 result(s). Showing the first 50.</p>";
				$n=0;
				while($r=$con->sql_fetchrow($res1)){
					$n++;
					if ($n>50) break;
					$bn_filter = array();

					/*$hurl = get_branch_file_url($r['branch_code'], $r['branch_ip']);
					$r['image_path'] = $hurl;
					$r['photos'] = get_sku_item_photos($r['id'],$r);*/

					
					if (BRANCH_CODE=='HQ'){
						// get latest price
						$con->sql_query("select sku_items_price.*, branch.code, branch.active from sku_items_price left join branch on branch_id = branch.id where sku_item_id = $r[id] and branch.active = 1 order by branch.sequence, branch.code");
	
						while($p = $con->sql_fetchrow()){
							$r['price'][$p['code']] = $p;
						}
						
						//$r1 = $con->sql_query("select sku_items_cost.*, branch.code from sku_items_cost left join branch on branch_id = branch.id where sku_item_id = $r[id] order by branch_id");
						//while($r1 = $con->sql_fetchrow())
						//{
						//	$r['stock_balance'][$r1['code']] = $r1;
						//}
					}else $bn_filter[] = "branch_id = ".mi($sessioninfo['branch_id']);

					$bn_filter[] = "sku_item_id = ".mi($r['id']);
					$bn = $con->sql_query("select sbi.batch_no, sbi.expired_date, b.code from sku_batch_items sbi left join branch b on b.id = sbi.branch_id where ".join(" and ", $bn_filter)." order by b.sequence, b.code");

					while($b = $con->sql_fetchrow($bn)){
						$r['batch_no'] = $b['batch_no'];
						$r['expired_date'] = $b['expired_date'];
						$r['batch_items'][$b['code']] = $b['code'];
					}
					
					//unfinalized stock balance
					$res2 = $con->sql_query("select ifnull(sum(pi.qty),0) as qty from pos p left join pos_items pi on p.id=pi.pos_id and p.branch_id = pi.branch_id and p.counter_id = pi.counter_id and p.date = pi.date where p.branch_id = ".mi($bid)." and p.date > '$last_finalized_date' and p.cancel_status = 0 and pi.sku_item_id = ".$r['id']);
					//print $abc.'<br /><br />';
					$r2 = $con->sql_fetchassoc($res2);
					$r['unfinalize_qty'] = $r['qty'] - $r2['qty'];

					$items[] = $r;
				}
				$con->sql_freeresult();
				$smarty->assign("items", $items);
				$smarty->assign("show_unfinalize_sb", (BRANCH_CODE != 'HQ'));
			}else $msg = "<p>Search of <b>$_REQUEST[code]</b> return 0 result.</p>";
		}else $msg = "Warning: Search string is empty";
		$smarty->assign("is_find", 1);
		$smarty->assign("msg", $msg);
	}
}

$smarty->assign("PAGE_TITLE", "Check Code");
$smarty->display("check_code.tpl");
?>
