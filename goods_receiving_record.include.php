<?php
/*
03/29/2016 11:00 Edwin
- Modified on skip reset privilege checking on GRR when GRN is cancelled

1/13/2017 12:01 PM Andy
- Enhanced to merge grn_cancel and reset_grr to process_reset_grr_grn()

1/17/2017 2:32 PM Andy
- Fixed update_po_receiving_count() will update wrong branch po.

1/23/2017 10:03 AM Andy
- Fixed GRN owner reset bug.

4/16/2018 4:21 PM Justin
- Added new functions used by Foreign Currency feature.

5/24/2019 9:36 AM William
- Pickup report_prefix for enhance "GRR".
*/
/*function reset_grr($grr_id,$branch_id,$type,$rcv_date, $grn_id=0){
    global $con,$sessioninfo,$config,$smarty,$LANG;

	$grr_id = mi($grr_id);
	$branch_id = mi($branch_id);
	
    if ($type == "GRN"){
		$grn_id = mi($grn_id);
        $php_page = "/goods_receiving_note.php";
		$query_string = "?a=open&id=".$grn_id."&branch_id=".$branch_id."&action=edit";
    }else{
        $php_page = "/goods_receiving_record.php";

		$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
		if($sessioninfo['level']<$required_level){
	        js_redirect(sprintf('Forbidden', $type, BRANCH_CODE), $php_page . $query_string);
		}
    }    

	$rdate = strtotime($rcv_date);

	if (isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_date = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));

		if ($rdate<$reset_date){
            if ($type == "GRN"){
                js_redirect(sprintf('Forbidden', 'GRN', BRANCH_CODE), "/goods_receiving_note.php?a=open&id=".$grn_id."&branch_id=".$branch_id."&action=edit");
            }
            else{
                $err['top'][] = sprintf($LANG['GRR_DATE_RESET_LIMIT']);
                $smarty->assign("errm", $err);
                return true;
            }
		}
	}

    $q1 = $con->sql_query("select * from grr where id=$grr_id and branch_id = $branch_id");
	$form=$con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if(!$form)  js_redirect($LANG['GRR_NOT_FOUND'], $php_page); // if GRR not found
	elseif(!$form['active'])  js_redirect(sprintf($LANG['GRR_INACTIVE'], $grr_id), $php_page); // if it is been deleted before
	elseif(!$form['status'])  js_redirect(sprintf($LANG['GRR_INVALID_RESET'], $grr_id), $php_page); // if this is been reset before

	//$aid=$form['approval_history_id'];
	//$approvals=$form['approvals'];
	$status = 0;

	// get items
	$q_i = $con->sql_query("select * from grr_items where grr_id=$grr_id and branch_id=$branch_id") or die(mysql_error());
	while($r = $con->sql_fetchrow($q_i)){
		$grr_item_id = $r['id'];
		
		// get grn data
		$q_grn = $con->sql_query("select * from grn where grr_item_id=$grr_item_id and branch_id=$branch_id and active=1") or die(mysql_error());
		while($grn = $con->sql_fetchrow($q_grn)){   // set to inactive and update cost
			$si_list = array();
            //update sku_item_cost
			$q1 = $con->sql_query("select sku_item_id from grn_items where grn_items.grn_id = ".mi($grn['id'])." and grn_items.branch_id=".mi($branch_id));
			while($tmp = $con->sql_fetchassoc($q1)){
				$si_list[] = $tmp['sku_item_id'];
			}
			if($si_list){
				$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($branch_id)." and sku_item_id in (".join(",", $si_list).")") or die(mysql_error());
			}
			// delete from vender sku history
			$con->sql_query("delete from vendor_sku_history where branch_id = ".mi($branch_id)." and source= 'GRN' and ref_id = ".mi($grn['id']));
			// set to inactive
			$con->sql_query("update grn set active=0 where id=".mi($grn['id'])." and branch_id=".mi($branch_id)) or die(mysql_error());
		}
		
		// update receiving count if it is PO
		if($r['type']=='PO'){
            update_po_receiving_count($r['doc_no']);
		}
	}
	
	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
//	$upd['approved'] = 0;

	$con->sql_query("update grr set ".mysql_update_by_field($upd)." where id=$grr_id and branch_id=$branch_id") or die(mysql_error());
	
	$upd = array();
	$upd['grn_used'] = 0;
	$con->sql_query("update grr_items set ".mysql_update_by_field($upd)." where grr_id=$grr_id and branch_id=$branch_id") or die(mysql_error());
	
    //log_br($sessioninfo['id'], 'GRR', $grr_id, sprintf("Reset: ($form[id])",$grr_id));
	log_br($sessioninfo['id'], 'GRR', $grr_id, "Reset : GRR".sprintf("%05d",$grr_id));
}*/

// update PO delivered status
function update_po_receiving_count($po_no){
	global $con, $config;

	if(strpos($po_no, ",") == true){ // is group doc no
		$po_no = str_replace(',', '","', $po_no);
	}

	// reset all number to zero first...
	$con->sql_query("update po_items left join po
	on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
	set po_items.delivered = 0 where po_no in (".ms($po_no).")");
					 
	if(!$config['use_grn_future']) $extra_filter = "and grn.grr_item_id = gri.id";
	
	$sql = "select grn_items.*, uom.fraction, gri.doc_no
		from grr_items gri
		join grr on grr.branch_id=gri.branch_id and grr.id=gri.grr_id
		join grn on grn.branch_id=grr.branch_id and grn.grr_id=grr.id $extra_filter
		join grn_items on grn_items.branch_id=grn.branch_id and grn_items.grn_id=grn.id
		left join uom on grn_items.uom_id = uom.id
		where gri.type='PO' and gri.doc_no in (".ms($po_no).") and grn.active=1 and grr.active=1 and grn.status=1
		order by grn.branch_id,grn.id";
	//print $sql;		
	$q1 = $con->sql_query($sql);

	$rcvq = array();
	$doc_list = array();
	while($r=$con->sql_fetchrow($q1)){
		if (!$r['po_item_id']) 
			continue;
			
		$bid = 0;
		if(!isset($doc_list[$r['doc_no']])){
			// get the po
			$con->sql_query("select branch_id,id from po where po_no=".ms($r['doc_no']));
			$doc_list[$r['doc_no']] = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
		}
		$bid = $doc_list[$r['doc_no']]['branch_id'];
		if(!$bid)	continue;

		$qty = 0;
		if ($r['acc_pcs']>0 || $r['acc_ctn']>0)
			$qty = $r['acc_pcs'] + $r['fraction'] * $r['acc_ctn'];
		else
			$qty = $r['pcs'] + $r['fraction'] * $r['ctn'];

		$rcvq[$bid][$r['po_item_id']] += $qty;
		//print "<li> $r[po_item_id] = ".$rcvq[$r['po_item_id']];
		//print_r($r);
	}
	$con->sql_freeresult($q1);

	if (!$rcvq) return;
	//print_r($rcvq);
	foreach($rcvq as $bid => $brcvq){
		foreach($brcvq as $k=>$v){
			$con->sql_query("update po_items set delivered = ".mi($v)." where id = ".mi($k)." and branch_id = ".mi($bid));
		}
	}
}

function process_reset_grr_grn($branch_id, $grr_id = 0, $grn_id = 0, $from_type = "GRN"){
	global $con,$sessioninfo,$config,$smarty,$LANG;
	
	$branch_id = mi($branch_id);
	$grr_id = mi($grr_id);
	$grn_id = mi($grn_id);
	
	if(!$branch_id){
		$errmsg = "No Branch ID";
	}
	
	if(!$errmsg && !$grr_id && !$grn_id){
		$errmsg = "No GRR and GRN ID";
	}
	
	if(!$errmsg){		
		if($from_type == 'GRR'){	// is from reset grr
			$php_page = "/goods_receiving_record.php";

			$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
			if($sessioninfo['level']<$required_level){
				js_redirect(sprintf('Forbidden', $from_type, BRANCH_CODE), $php_page);
			}
		}else{	// is from cancel grn
			$php_page = "/goods_receiving_note.php";
		}
	}
	
	if(!$errmsg){
		if(!$grr_id){
			$con->sql_query("select * from grn where branch_id=$branch_id and id=$grn_id");
			$grn = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$grr_id = mi($grn['grr_id']);
			if(!$grr_id){
				$errmsg = "Error: Cannot Found GRR ID";
			}
		}
	}
	
	if(!$errmsg){
		$con->sql_query("select * from grr where id=$grr_id and branch_id = $branch_id");
		$grr = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$grr)  js_redirect($LANG['GRR_NOT_FOUND'], $php_page); // if GRR not found
		elseif(!$grr['active'])  js_redirect(sprintf($LANG['GRR_INACTIVE'], $grr_id), $php_page); // if it is been deleted before
		elseif(!$grr['status'])  js_redirect(sprintf($LANG['GRR_INVALID_RESET'], $grr_id), $php_page); // if this is been reset before
	}
	
	if ($from_type == 'GRR' && isset($config['reset_date_limit']) && $config['reset_date_limit'] >= 0){
		$reset_limit = $config['reset_date_limit'];
		$reset_date = strtotime("-1 day",strtotime("-$reset_limit day" , strtotime("now")));

		if (strtotime($grr['rcv_date'])<$reset_date){
            if ($from_type == "GRN"){
                js_redirect(sprintf('Forbidden', 'GRN', BRANCH_CODE), "/goods_receiving_note.php?a=open&id=".$grn_id."&branch_id=".$branch_id."&action=edit");
            }
            else{
                $err['top'][] = sprintf($LANG['GRR_DATE_RESET_LIMIT']);
                $smarty->assign("errm", $err);
                return true;
            }
			$errmsg = $LANG['GRR_DATE_RESET_LIMIT'];
		}
	}
	
	if($errmsg){
		if ($from_type == 'GRR'){
			$err['top'][] = $errmsg;
			$smarty->assign("errm", $err);
			return true;
		}else{
			js_redirect($errmsg, "/goods_receiving_note.php?a=open&id=".$grn_id."&branch_id=".$branch_id."&action=edit");
		}
	}
	// cancel all grn
	$q_grn = $con->sql_query("select * from grn where branch_id=$branch_id and grr_id=$grr_id and active=1");
	while($grn_info = $con->sql_fetchassoc($q_grn)){
		$con->sql_query("update grn 
        set active=0
        where grn.id = ".mi($grn_info['id'])." and grn.branch_id = ".mi($branch_id));
        
		$si_list = array();
		//update sku_item_cost
		$q_gi = $con->sql_query("select sku_item_id from grn_items where grn_items.grn_id = ".mi($grn_info['id'])." and grn_items.branch_id=".mi($branch_id));
		while($tmp = $con->sql_fetchassoc($q_gi)){
			$si_list[] = $tmp['sku_item_id'];
		}
		$con->sql_freeresult($q_gi);
		if($si_list){
			$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($branch_id)." and sku_item_id in (".join(",", $si_list).")") or die(mysql_error());
		}
		// delete from vender sku history
		$con->sql_query("delete from vendor_sku_history where branch_id = ".mi($branch_id)." and source= 'GRN' and ref_id = ".mi($grn_info['id']));
				
        // check if it is having Batch No Setup
        if($grn_info['batch_status']){
            // update all batch no become "C-xxx"
            $con->sql_query("update sku_batch_items set batch_no = concat('C-', batch_no) where grn_id = ".mi($grn_info['id'])." and branch_id = ".mi($branch_id));
            
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
            $con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where grn_id = ".mi($grn_info['id'])." and branch_id = ".mi($branch_id));
        }
		 // set dnote to inactive
		if($grn_info['is_under_gst']){
			$con->sql_query("update dnote set active=0 where ref_table = 'grn' and ref_id = ".mi($grn_info['id'])." and branch_id = ".mi($branch_id));
		}
        
        log_br($sessioninfo['id'], 'GRN', $grn_info['id'], "Goods Receiving Note canceled by $sessioninfo[u] for (ID#".$grn_info['id'].")");
	}
	$con->sql_freeresult($q_grn);
	
	// get all grr_items
	$q_i = $con->sql_query("select grr_items.*,branch.report_prefix from grr_items left join branch on grr_items.branch_id= branch.id where grr_items.grr_id=$grr_id and grr_items.branch_id=$branch_id") or die(mysql_error());
	while($r = $con->sql_fetchrow($q_i)){
		/*if(!$config['use_grn_future']){
			$grr_item_id = $r['id'];
			// get grn by grr_item_id
			$q_grn = $con->sql_query("select * from grn where grr_item_id=$grr_item_id and branch_id=$branch_id and active=1") or die(mysql_error());
			while($grn = $con->sql_fetchrow($q_grn)){   // set to inactive and update cost
				$si_list = array();
				//update sku_item_cost
				$q1 = $con->sql_query("select sku_item_id from grn_items where grn_items.grn_id = ".mi($grn['id'])." and grn_items.branch_id=".mi($branch_id));
				while($tmp = $con->sql_fetchassoc($q1)){
					$si_list[] = $tmp['sku_item_id'];
				}
				if($si_list){
					$con->sql_query("update sku_items_cost set changed=1 where branch_id=".mi($branch_id)." and sku_item_id in (".join(",", $si_list).")") or die(mysql_error());
				}
				// delete from vender sku history
				$con->sql_query("delete from vendor_sku_history where branch_id = ".mi($branch_id)." and source= 'GRN' and ref_id = ".mi($grn['id']));
				// set to inactive
				$con->sql_query("update grn set active=0 where id=".mi($grn['id'])." and branch_id=".mi($branch_id)) or die(mysql_error());
			}
		}*/
		$report_prefix = $r['report_prefix'];
		
		// update receiving count if it is PO
		if($r['type']=='PO'){
            update_po_receiving_count($r['doc_no']);
		}
	}
	
	$upd = array();
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	//$upd['approved'] = 0;
	$con->sql_query("update grr set ".mysql_update_by_field($upd)." where id=$grr_id and branch_id=$branch_id") or die(mysql_error());
	
	$upd = array();
	$upd['grn_used'] = 0;
	$con->sql_query("update grr_items set ".mysql_update_by_field($upd)." where grr_id=$grr_id and branch_id=$branch_id") or die(mysql_error());
	
	log_br($sessioninfo['id'], 'GRR', $grr_id, "Reset : ".$report_prefix.sprintf("%05d",$grr_id));
}

function loadGRRCurrencyCodeList($grr){
	global $config, $smarty, $appCore;
	
	// got turn on currency
	if($config['foreign_currency']){
		// Get Foreign Currency Code Array
		$foreignCurrencyCodeList = $appCore->currencyManager->getCurrencyCodes();
		
		// If GRR using the Foreign Currency which now already inactive, need to append into array
		if($grr['currency_code'] && !isset($foreignCurrencyCodeList[$grr['currency_code']])){
			$foreignCurrencyCodeList[$grr['currency_code']] = $grr['currency_code'];
		}
	}
	
	if(isset($smarty) && $smarty){
		$smarty->assign('foreignCurrencyCodeList', $foreignCurrencyCodeList);
	}
	return $foreignCurrencyCodeList;
}

function loadCurrencyRate(){
	global $LANG, $appCore;
	
	$form = $_REQUEST;
	if(!$form['date']) $ret['err'] = sprintf($LANG['GRR_INVALID_RECEIVE_DATE'], "");
	
	if(!$ret['err']){
		$ret = $appCore->currencyManager->loadCurrencyRateByDate($form['date'], $form['currency_code']);
	}
	
	// if found got code but no rate, ned to prompt error
	if($form['currency_code'] && !$ret['rate']) $ret['err'] = $LANG['CURRENCY_RATE_ZERO'];
	
	print json_encode($ret);
}
?>