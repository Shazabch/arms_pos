<?php
/*
6/26/2009 3:00 PM Andy
- filter branches active=1

4/1/2010 5:37:54 PM Andy
- Monthly report change to only show active branch

5/31/2010 2:54:17 PM Andy
- Disable Cosignment Lost/Over Invoice
- Add 2 New Module: Credit Note and Debit Note.(Need Privileges to Access)
- Add 2 New Approval Flow : Credit Note Approval and Debit Note Approval.(Need Privileges to Access)
- Add New Consignment Discount Format: Masterfile Branch Trade Discount and Consignment Lost/Over Discount can now accept secondary discount percent using "+". e.g(50+10)(Need Config)
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.
- Stock balance and inventory calculation include CN(-)/DN(+), can see under SKU Masterfile->Inventory.
- Monthly Report lost qty will change to generate CN, over qty will change to generate DN.
- Fix monthly report generate invoice/adjustment cannot show stock balance bugs.
- Fix javascript and smarty rounding bugs.
- Add checking for allowed future/passed DO Date limit.

6/3/2010 6:06:14 PM Andy
- Add Multiple print.
- Add Export UBS for CN/DN.

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

6/9/2010 4:55:46 PM Andy
- Fix print multiple invoice middle line cannot be hide even config already set.
- Fix stock balance calculation to ignore inactive grr.
- Remove pos_transaction import modules.
- All report which use pos_transaction will change to use pos and pos_items.
- SKU items, category, pwp and member sales cache will directly generate once counter collection is finalized.
- All Sales cache will be delete once counter collection is un-finalized.
- cron to calculate pwp and member sales cache is retired.
- Counter collection finalize status will change to store in a new table.

7/21/2010 11:26:43 AM Andy
- Add Consignment Monthly Report Main Page List.
- Add Checking if monthly report already confirm, it cannot be print again.
- Fix a bugs Monthly Report last row data cannot save problems.

7/28/2010 1:46:18 PM Andy
- Add delete function to consignment monthly report (prompt to enter reason before delete) version 22.

1/26/2011 2:57:17 PM Andy
- Change to check maintenance version 49.

5/23/2011 11:16:32 AM Alex
- add check con_split_artno request to activate $config['ci_use_split_artno'] 

6/6/2011 6:14:21 PM Justin
- Added the include of currency code and exchange rate while in branches looping.

6/17/2011 11:44:40 AM Justin
- Fixed the bugs while loading branches, system unable to verify when need to use multiple currency.

6/24/2011 3:46:11 PM Andy
- Make all branch default sort by sequence, code.

4/2/2012 2:55:27 PM Andy
- Add checking maintenance TMP version 119.

2/4/2013 5:34 PM Justin
- Enhanced to filter branches from regions or branch group base on user's regions.

1/21/2015 5:55 PM Justin
- Enhanced to have GST calculation.

4/1/2015 5:44 PM Andy
- Add new consignment function "get_export_type_gst"

4/17/2015 3:54 PM Justin
- Bug fixed on sheet discount didn't round up to 2 decimal points.

6/5/2015 3:40 PM Andy
- Enhanced CN/DN recalculation.
- Enhanced to have display cost price feature for CN/DN.
*/

$smarty->assign('time_value', 1000000000);
$maintenance->check(244);
$maintenance->check(244, true);

if ($_REQUEST['con_split_artno']){
	$config['ci_use_split_artno']=1;
	$smarty->assign('config',$config);
}

$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
$smarty->assign('months',$months);

function load_branch_group($id=0){
	global $con,$smarty,$config;

	$branch_group = array();

	// check whether select all or specified group
	if($id>0){
		$where = "where id=".mi($id);
		$where2 = "and bgi.branch_group_id=".mi($id);
	}

	// load items
	$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
	while($r = $con->sql_fetchassoc($q1)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$con->sql_freeresult($q1);
	
	// load header
	$q1 = $con->sql_query("select * from branch_group $where",false,false);
	if($con->sql_numrows()<=0) return;
	while($r = $con->sql_fetchassoc($q1)){
		if(!$branch_group['items'][$r['id']]) continue;
        $branch_group['header'][$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	$smarty->assign('branch_group',$branch_group);
	return $branch_group;
}

function load_branch(){
	global $con,$smarty,$config;

	$q_b = $con->sql_query("select * from branch where active=1 order by sequence, code") or die(mysql_error());
	while($r = $con->sql_fetchrow($q_b)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
		if($config['masterfile_branch_region'][$r['region']]['currency'] && is_array($config['consignment_multiple_currency'])){
			$currency_code = $config['masterfile_branch_region'][$r['region']]['currency'];
			
			if(trim($currency_code)){
				$r['currency_code'] = strtoupper(trim($currency_code));
				$sql1 = $con->sql_query("select exchange_rate from consignment_forex where currency_code = ".ms($r['currency_code']));
				$cf = $con->sql_fetchrow($sql1);

				if($cf) $r['exchange_rate'] = $cf['exchange_rate'];
			}
		}
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($q_b);
	$smarty->assign('branches',$branches);
	return $branches;
}

function recalculate_cn_dn_amount($id, $bid, $tbl=""){
	global $con;
	
	if($tbl){
		$mst_tbl = $tbl;
		$dtl_tbl = $tbl.'_items';
	}else{
		$mst_tbl = NOTE_TBL;
		$dtl_tbl = NOTE_TBL_ITEMS;
	}
	
	$q1 = $con->sql_query("select * from ".$mst_tbl." where id = ".mi($id)." and branch_id = ".mi($bid));
	
	if($con->sql_numrows($q1) == 0) return;
	$doc_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if(!$doc_info['is_under_gst']) return;
	
	// load items
	$q1 = $con->sql_query("select tbli.*, u.fraction
						   from ".$dtl_tbl." tbli
						   left join uom u on u.id = tbli.uom_id
						   where tbli.".$mst_tbl."_id = ".mi($id)." and tbli.branch_id = ".mi($bid)." order by tbli.id");
	
	$sub_total_gross_amt = $sub_total_foreign_gross_amt = 0;
	$sub_total_gst_amt = $sub_total_foreign_gst_amt = 0;
	$sub_total_amt = $sub_total_foreign_amt = 0;
		
	$gross_discount_amount = $gross_foreign_discount_amount = 0;
	$sheet_gst_discount = $sheet_foreign_gst_discount = 0;
	$discount_amount = $foreign_discount_amount = 0;
	
	$total_gross_amt = $total_foreign_gross_amt = 0;
	$total_gst_amt = $total_foreign_gst_amt = 0;
	$total_amount = $total_foreign_amount = 0;
	
		
	while($r = $con->sql_fetchassoc($q1)){
		$item_amt = ($r['cost_price'] / $r['fraction']) * ($r['ctn'] * $r['fraction'] + $r['pcs']);
		$item_foreign_amt = ($r['foreign_cost_price'] / $r['fraction']) * ($r['ctn'] * $r['fraction'] + $r['pcs']);
		
		if($r['display_cost_price_is_inclusive'] && $r['gst_rate']){
			$item_foreign_amt = $item_foreign_amt / (100+$r['gst_rate']) * 100;
		}
		$item_disc_amt = 0;
		$foreign_discount_amt = 0;
		
		if($r['discount_per']){    // deduct item discount first
			$discount_arr = explode("+", $r['discount_per']);
			if($discount_arr[0]){
				$disc_amt = $item_amt * ($discount_arr[0]/100);
				$item_disc_amt += $disc_amt;
				$item_amt -= $disc_amt;
				
				$disc_amt = $item_foreign_amt * ($discount_arr[0]/100);
				$foreign_discount_amt += $disc_amt;
				$item_foreign_amt -= $disc_amt;
			}
			if($discount_arr[1]){
				$disc_amt = $item_amt * ($discount_arr[1]/100);
				$item_disc_amt += $disc_amt;
				$item_amt -= $disc_amt;
				
				$disc_amt = $item_foreign_amt * ($discount_arr[1]/100);
				$foreign_discount_amt += $disc_amt;
				$item_foreign_amt -= $disc_amt;
			}
		}

		// normal
		$r['discount_amt'] = round($item_disc_amt, 2);
		$r['item_amt'] = $item_amt;
		$r['item_gst_amt'] = round($item_amt * (100 + $r['gst_rate']) / 100, 2);
		$r['item_amt'] = round($r['item_amt'], 2);
		$r['item_gst'] = round($r['item_gst_amt']-$r['item_amt'], 2);
		
		// amt 2
		$r['item_disc_amt2'] = 0;
		$r['item_amt2'] = $r['item_amt'];
		$r['item_gst2'] = $r['item_gst'];
		$r['item_gst_amt2'] = $r['item_gst_amt'];
		
		// foreign
		$r['foreign_discount_amt'] = round($foreign_discount_amt, 2);
		$r['item_foreign_amt'] = $item_foreign_amt;
		$r['item_foreign_gst_amt'] = round($item_foreign_amt * (100 + $r['gst_rate']) / 100, 2);
		$r['item_foreign_amt']  = round($r['item_foreign_amt'] , 2);
		$r['item_foreign_gst'] = round($r['item_foreign_gst_amt'] - $r['item_foreign_amt'], 2);
		
		// amt 2
		$r['item_foreign_disc_amt2'] = 0;
		$r['item_foreign_amt2'] = $r['item_foreign_amt'];
		$r['item_foreign_gst2'] = $r['item_foreign_gst'];
		$r['item_foreign_gst_amt2'] = $r['item_foreign_gst_amt'];
		
		// normal
		$sub_total_gross_amt += $r['item_amt'];
		$sub_total_gst_amt += $r['item_gst'];
		$sub_total_amt += $r['item_gst_amt'];
		
		// foreign 
		$sub_total_foreign_gross_amt += $r['item_foreign_amt'];
		$sub_total_foreign_gst_amt += $r['item_foreign_gst'];
		$sub_total_foreign_amt += $r['item_foreign_gst_amt'];
		
		$items[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	$total_gross_amt = $sub_total_gross_amt = round($sub_total_gross_amt, 2);
	$total_gst_amt = $sub_total_gst_amt = round($sub_total_gst_amt, 2);
	$total_amount = $sub_total_amt = round($sub_total_amt, 2);
	
	$total_foreign_gross_amt = $sub_total_foreign_gross_amt = round($sub_total_foreign_gross_amt, 2);
	$total_foreign_gst_amt = $sub_total_foreign_gst_amt = round($sub_total_foreign_gst_amt, 2);
	$total_foreign_amount = $sub_total_foreign_amt = round($sub_total_foreign_amt, 2);
	
	// sheet discount
	$gross_discount_amount = $gross_foreign_discount_amount = 0;
	$sheet_gst_discount = $sheet_foreign_gst_discount = 0;
	$discount_amount = $foreign_discount_amount = 0;
	
	if($doc_info['discount']){
		$tmp_total_amount = $total_amount;
		$tmp_total_foreign_amount = $total_foreign_amount;
		
		$sheet_discount_arr = explode("+", $doc_info['discount']);
		if($sheet_discount_arr[0]){
			$disc_amt = round($tmp_total_amount * $sheet_discount_arr[0]/100, 2);
			$discount_amount += $disc_amt;
			$tmp_total_amount -= $disc_amt;
			
			$disc_amt = round($tmp_total_foreign_amount * $sheet_discount_arr[0]/100, 2);
			$foreign_discount_amount += $disc_amt;
			$tmp_total_foreign_amount -= $disc_amt;
		}
		
		if($sheet_discount_arr[1]){
			$disc_amt = round($tmp_total_amount * $sheet_discount_arr[1]/100, 2);
			$discount_amount += $disc_amt;
			$tmp_total_amount -= $disc_amt;
			
			$disc_amt = round($tmp_total_foreign_amount * $sheet_discount_arr[1]/100, 2);
			$foreign_discount_amount += $disc_amt;
			$tmp_total_foreign_amount -= $disc_amt;
		}
	}
	
	$p = ($discount_amount * 100) / $total_amount;
	
	$discount_amount = round($discount_amount, 2);
	if($discount_amount){
		// normal
		$total_amount -= $discount_amount;
		
		$gross_discount_amount = round($total_gross_amt * $p /100, 2);
		$total_gross_amt -= $gross_discount_amount;

		$sheet_gst_discount = round($discount_amount-$gross_discount_amount, 2);
		$total_gst_amt = round($total_gst_amt-$sheet_gst_discount,2);
		
		// foreign
		$total_foreign_amount -= $foreign_discount_amount;
		
		$gross_foreign_discount_amount = round($total_foreign_gross_amt * $p /100, 2);
		$total_foreign_gross_amt -= $gross_foreign_discount_amount;

		$sheet_foreign_gst_discount = round($foreign_discount_amount-$gross_foreign_discount_amount, 2);
		$total_foreign_gst_amt = round($total_foreign_gst_amt-$sheet_foreign_gst_discount,2);
	}
	
	$remaining_sheet_discount_gross_amt = $gross_discount_amount;
	$remaining_sheet_gst_discount = $sheet_gst_discount;
	$remaining_sheet_discount_amt = $discount_amount;
	
	$remaining_gross_foreign_discount_amount = $gross_foreign_discount_amount;
	$remaining_sheet_foreign_gst_discount = $sheet_foreign_gst_discount;
	$remaining_foreign_discount_amount = $foreign_discount_amount;
	
	// calculate amt2 and update amt by item
	$item_len = count($items);
	$i = 0;
	foreach($items as $tmp=>$r){
		$i++;
		
		// normal
		$line_gross_amt2 = $line_gross_amt = $r['item_amt2'] = $r['item_amt'];
		$line_gst_amt2 = $line_gst_amt = $r['item_gst2'] = $r['item_gst'];
		$line_amt2 = $line_amt = $r['item_gst_amt2'] = $r['item_gst_amt'];
		$item_discount_amount2 = $r['item_disc_amt2'] = 0;

		if($p){
			$line_amt2 = $line_amt * ((100 - $p) / 100);
			$line_gross_amt2 = $line_amt2 / ((100 + $r['gst_rate']) / 100);

			$line_amt2_rounded = round($line_amt2 ,2);
			$line_gross_amt2_rounded = round($line_gross_amt2 ,2);
			$line_gst_amt2 = round($line_amt2_rounded-$line_gross_amt2_rounded,2);
			
			$item_discount_amount2 = $line_amt - $line_amt2_rounded;
			
			$line_gross_amt2 = round($line_gross_amt2, 2);
			$line_gst_amt2 = round($line_gst_amt2, 2);
			$line_amt2 = round($line_amt2, 2);
			$item_discount_amount2 = round($item_discount_amount2, 2);
			
			$remaining_sheet_discount_gross_amt = round($remaining_sheet_discount_gross_amt - ($line_gross_amt - $line_gross_amt2), 2);
			$remaining_sheet_gst_discount = round($remaining_sheet_gst_discount - ($line_gst_amt - $line_gst_amt2), 2);
			$remaining_sheet_discount_amt = round($remaining_sheet_discount_amt - ($item_discount_amount2), 2);

			if($i == $item_len){	// last item
				if($remaining_sheet_discount_gross_amt != 0){
					$line_gross_amt2 -= $remaining_sheet_discount_gross_amt;
					$remaining_sheet_discount_gross_amt = 0;
				}
				if($remaining_sheet_gst_discount != 0){
					$line_gst_amt2 -= $remaining_sheet_gst_discount;
					$remaining_sheet_gst_discount = 0;
				}
				if($remaining_sheet_discount_amt != 0){
					$line_amt2 -= $remaining_sheet_discount_amt;
					$item_discount_amount2 += $remaining_sheet_discount_amt;
					$remaining_sheet_discount_amt = 0;
				}
			}
		}
		$r['item_amt2'] = $line_gross_amt2;
		$r['item_gst2'] = $line_gst_amt2;
		$r['item_gst_amt2'] = $line_amt2;
		$r['item_disc_amt2'] = $item_discount_amount2;
		
		// foreign
		$line_foreign_gross_amt2 = $line_foreign_gross_amt = $r['item_foreign_amt2'] = $r['item_foreign_amt'];
		$line_foreign_gst_amt2 = $line_foreign_gst_amt = $r['item_foreign_gst2'] = $r['item_foreign_gst'];
		$line_foreign_amt2 = $line_foreign_amt = $r['item_foreign_gst_amt2'] = $r['item_foreign_gst_amt'];
		$item_foreign_discount_amount2 = $r['item_foreign_disc_amt2'] = 0;
		
		if($p){
			$line_foreign_amt2 = $line_foreign_amt * ((100 - $p) / 100);
			$line_foreign_gross_amt2 = $line_foreign_amt2 / ((100 + $r['gst_rate']) / 100);

			$line_foreign_amt2_rounded = round($line_foreign_amt2 ,2);
			$line_foreign_gross_amt2_rounded = round($line_foreign_gross_amt2 ,2);
			$line_foreign_gst_amt2 = round($line_foreign_amt2_rounded-$line_foreign_gross_amt2_rounded,2);
			
			$item_foreign_discount_amount2 = $line_foreign_amt - $line_foreign_amt2_rounded;
			
			$line_foreign_gross_amt2 = round($line_foreign_gross_amt2, 2);
			$line_foreign_gst_amt2 = round($line_foreign_gst_amt2, 2);
			$line_foreign_amt2 = round($line_foreign_amt2, 2);
			$item_foreign_discount_amount2 = round($item_foreign_discount_amount2, 2);
			
			$remaining_gross_foreign_discount_amount = round($remaining_gross_foreign_discount_amount - ($line_foreign_gross_amt - $line_foreign_gross_amt2), 2);
			$remaining_sheet_foreign_gst_discount = round($remaining_sheet_foreign_gst_discount - ($line_foreign_gst_amt - $line_foreign_gst_amt2), 2);
			$remaining_foreign_discount_amount = round($remaining_foreign_discount_amount - ($item_foreign_discount_amount2), 2);
			
			if($i == $item_len){
				if($remaining_gross_foreign_discount_amount != 0){
					$line_foreign_gross_amt2 -= $remaining_gross_foreign_discount_amount;
					$remaining_gross_foreign_discount_amount = 0;
				}
				if($remaining_sheet_foreign_gst_discount != 0){
					$line_foreign_gst_amt2 -= $remaining_sheet_foreign_gst_discount;
					$remaining_sheet_foreign_gst_discount = 0;
				}
				if($remaining_foreign_discount_amount != 0){
					$line_foreign_amt2 -= $remaining_foreign_discount_amount;
					$item_foreign_discount_amount2 += $remaining_foreign_discount_amount;
					$remaining_foreign_discount_amount = 0;
				}
			}
		}
		
		$r['item_foreign_amt2'] = $line_foreign_gross_amt2;
		$r['item_foreign_gst2'] = $line_foreign_gst_amt2;
		$r['item_foreign_gst_amt2'] = $line_foreign_amt2;
		$r['item_foreign_disc_amt2'] = $item_foreign_discount_amount2;
	
	
		// ready to update items
		// local amount
		$upd = array();
		$upd['item_amt'] = $r['item_amt'];
		$upd['item_gst'] = $r['item_gst'];
		$upd['item_gst_amt'] = $r['item_gst_amt'];
		$upd['discount_amt'] = $r['discount_amt'];
		$upd['item_amt2'] = $r['item_amt2'];
		$upd['item_gst2'] = $r['item_gst2'];
		$upd['item_gst_amt2'] = round($r['item_gst_amt2'], 2);
		$upd['item_disc_amt2'] = $r['item_disc_amt2'];
		
		// foreign amount
		$upd['item_foreign_amt'] = $r['item_foreign_amt'];
		$upd['item_foreign_gst'] = $r['item_foreign_gst'];
		$upd['item_foreign_gst_amt'] = $r['item_foreign_gst_amt'];
		$upd['foreign_discount_amt'] = $r['item_foreign_disc_amt'];
		$upd['item_foreign_amt2'] = $r['item_foreign_amt2'];
		$upd['item_foreign_gst2'] = $r['item_foreign_gst2'];
		$upd['item_foreign_gst_amt2'] = round($r['item_foreign_gst_amt2'], 2);
		$upd['item_foreign_disc_amt2'] = $r['item_foreign_disc_amt2'];
		
		$con->sql_query("update ".$dtl_tbl." set ".mysql_update_by_field($upd)." where id = ".mi($r['id'])." and branch_id = ".mi($r['branch_id']));
	}
	
	// update main table
	$upd = array();
	$upd['total_gross_amt'] = round($total_gross_amt, 2);
	$upd['total_gst_amt'] = round($total_gst_amt, 2);
	$upd['sheet_gst_discount'] = round($sheet_gst_discount, 2);
	$upd['sub_total_amt'] = round($sub_total_amt, 2);
	$upd['total_amount'] = round($total_amount, 2);
	$upd['discount_amount'] = round($discount_amount, 2);
	$upd['gross_discount_amount'] = round($gross_discount_amount, 2);
	$upd['sub_total_gross_amt'] = round($sub_total_gross_amt, 2);
	
	$upd['total_foreign_gross_amt'] = round($total_foreign_gross_amt, 2);
	$upd['total_foreign_gst_amt'] = round($total_foreign_gst_amt, 2);
	$upd['sheet_foreign_gst_discount'] = round($sheet_foreign_gst_discount, 2);
	$upd['sub_total_foreign_amt'] = round($sub_total_foreign_amt, 2);
	$upd['total_foreign_amount'] = round($total_foreign_amount, 2);
	$upd['foreign_discount_amount'] = round($foreign_discount_amount, 2);
	$upd['gross_foreign_discount_amount'] = round($gross_foreign_discount_amount, 2);
	$upd['sub_total_foreign_gross_amt'] = round($sub_total_foreign_gross_amt, 2);
	
	$con->sql_query("update ".$mst_tbl." set ".mysql_update_by_field($upd)." where id = ".mi($id)." and branch_id = ".mi($bid));
}

function get_export_type_gst($is_export){
	global $con;
	
	if(!$is_export)	return;
	
	switch($is_export){
		case 1:	// foreign
			$setting_name = 'export_gst_type';
			break;
		case 2: // designated area
			$setting_name = 'designated_gst_type';
			break;
	}
	
	if($setting_name){
		// get gst id
		$q2 = $con->sql_query("select * from gst_settings where setting_name = ".ms($setting_name));
		$gst_settings = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		
		if($gst_settings){
			// get gst
			$q2 = $con->sql_query("select * from gst where id = ".mi($gst_settings['setting_value']));
			$export_gst = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			return $export_gst;
		}
	}
}

?>
