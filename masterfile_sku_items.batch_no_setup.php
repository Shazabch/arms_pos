<?php
/*
5/31/2011 1:03:30 PM Justin
- Rename the "grn_batch_items" into "sku_batch_items".

6/10/2011 2:48:23 PM Justin
- Modified the report print to have more printing on per page/last page.
- Fixed the bugs while calculating the days expired.

6/20/2011 11:14:30 AM Justin
- Fixed the wrong calculation of batch item remain/expired days.

10/7/2011 4:04:32 PM Justin
- Fixed the item deletion bugs.

8/29/2014 10:16 AM Justin
- Enhanced the check duplicate Batch No base on GRN's status.
- Enhanced to capture GRN ID while storing adjustment.

9/22/2014 11:43 AM Justin
- Enhanced to have decimal points while adding batch no depending on SKU item settings.

5/30/2017 4:05 PM Andy
- Fixed generate item bugs.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

3/28/2018 11:20 AM Andy
- Enhanced Batch No Setup to copy weight_kg when create new sku_items.

1/8/2020 4:10 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.
*/
include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

// shows user the SKU batch no setup module is unavailable if found no connection to HQ
$hqcon = new sql_db(HQ_MYSQL, $db_default_connection[1], $db_default_connection[2], $db_default_connection[3], false);

if(!$hqcon->db_connect_id){
	$smarty->display('header.tpl');
	print "<h1>SKU Batch No Setup".$LANG['HQ_OFFLINE']."</h1>";
	$smarty->display('footer.tpl');
	exit;
}

if($config['use_grn_future']){
	include("goods_receiving_note2.include.php");
}else{
	include("goods_receiving_note1.include.php");
}

class SKU_Batch_No_Setup extends Module{
	var $page_size = 30;
	
    function _default(){
		global $con, $smarty;
	    $this->display();
	}
	
	function ajax_list_sel(){
        global $hqcon, $smarty, $sessioninfo;
        $page_size = $this->page_size;

		$t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);

		$size = $page_size;
		$start = $p*$size;

		switch($t){
			case 1:	// waiting for setup
				$filter[] = "grn.batch_status=0";;
				break;
			case 2: // confirmed
				$filter[] = "grn.batch_status=1";
				break;
			case 3: // search grn
				if(!$_REQUEST['search_str']) die('Cannot search empty string');
				
				// strip "grn#####" prefix
			    if(preg_match("/^grn/i", $_REQUEST['search_str']))
			    	$_REQUEST['search_str'] = intval(substr($_REQUEST['search_str'],3));

				$filter[]  = '(grn.id = ' . mi($_REQUEST['search_str']) .' or grr_items.doc_no like '.ms("%". replace_special_char($_REQUEST[search_str]). "%").')';
				$smarty->assign('search_str', $str);
				break;
			default:
				die('Invalid Page');
		}

		//if($branch_id) $filter[] = "mr.branch_id=".mi($branch_id);
		
		$filter = join(' and ',$filter);

		$sql = "select count(*) from grn left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id where grn.active = 1 and grn.status = 1 and grn.authorized = 1 and grn.approved = 1 and grn.branch_id = ".ms($sessioninfo['branch_id'])." and ".$filter;
		//print $sql;
		$hqcon->sql_query($sql) or die(mysql_error());
		$total_rows = $hqcon->sql_fetchfield(0);
		$hqcon->sql_freeresult();

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";

		$total_page = ceil($total_rows/$size);
		$sql = "select grn.*, grr_items.doc_no, grr_items.type,
				if (vendor.id, vendor.description, (select branch.description from do left join branch on do.branch_id = branch.id where do_no = grr_items.doc_no)) as vendor,
				branch.report_prefix,grr_items.type,po.user_id as po_user_id
				from grn
				left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
				left join vendor on grn.vendor_id = vendor.id
				left join branch on grn.branch_id = branch.id
				left join po on po.id=grr_items.po_id and po.branch_id=grr_items.branch_id and grr_items.type='PO'
				where grn.active = 1 and grn.status = 1 and grn.authorized = 1 and grn.approved = 1 
				and grn.branch_id = ".ms($sessioninfo['branch_id'])." and $filter
				order by grn.last_update desc $limit";
		//print $sql;
		$hqcon->sql_query($sql) or die(mysql_error());
		while($r = $hqcon->sql_fetchrow()){
			$items[] = $r;
		}
		$hqcon->sql_freeresult();
		$smarty->assign('items',$items);
		$smarty->assign('total_page',$total_page);
		$smarty->assign('item_counter',$start+1);
		$smarty->display('masterfile_sku_items.batch_no_setup.list.tpl');
	}
	
	function edit(){
		global $con, $hqcon, $smarty, $LANG;
		$form=$_REQUEST;

		$grn_id = $form['id'];
		$branch_id = $form['branch_id'];
		
		$form=load_grn_header($grn_id, $branch_id);	
		$form['items']=load_grn_items($grn_id, $branch_id, false, false);
		//print_r($form);
		// get existing grn batch items
		$hqcon->sql_query("select * from sku_batch_items where grn_id = ".ms($grn_id)." and branch_id = ".ms($branch_id)."order by id");
		$form['batch_items'] = $hqcon->sql_fetchrowset();
		$hqcon->sql_freeresult();

		// unset invalid sku items
		unset($form['items']['non_sku_items']);
		
		$smarty->assign("form", $form);
		
		$smarty->display("masterfile_sku_items.batch_no_setup.detail.tpl");
	}

	function save($skip_redirect=false){
		global $hqcon, $smarty, $sessioninfo;
		$form=$_REQUEST;

		$grn_id = $form['id'];
		$branch_id = $form['branch_id'];

		// delete existing deleted record
		if($form['deleted_batch_item_list']){
			$hqcon->sql_query("delete from sku_batch_items where grn_id = ".mi($grn_id)." and branch_id =".mi($branch_id)." and id in ($form[deleted_batch_item_list])");
		}
		
		$ins['branch_id'] = $branch_id;
		$ins['grn_id'] = $grn_id;
		
		if($form['batch_id']){
			foreach($form['batch_id'] as $k=>$dummy){
				if($form['batch_no'][$k]){
					$hqcon->sql_query("update sku_batch_items 
									   set qty = ".doubleval($form['batch_qty'][$k]).",
									   batch_no = ".ms($form['batch_no'][$k]).",
									   expired_date = ".ms($form['expired_date'][$k]).",
									   last_update = CURRENT_TIMESTAMP
									   where grn_id = ".mi($grn_id)." and ".mi($form['grn_item_id'][$k])." and branch_id =".mi($branch_id)." and id = ".mi($form['batch_id'][$k]));

					if($hqcon->sql_affectedrows() == 0){ // means the batch item is newly added
						$ins['grn_item_id'] = $form['grn_item_id'][$k];
						$ins['batch_no'] = $form['batch_no'][$k];
						$ins['qty'] = $form['batch_qty'][$k];
						$ins['expired_date'] = $form['expired_date'][$k];
						$ins['added'] = 'CURRENT_TIMESTAMP';
						$ins['created_by'] = $sessioninfo['id'];
						$hqcon->sql_query("insert into sku_batch_items ".mysql_insert_by_field($ins));
						$new_sbi_id = $hqcon->sql_nextid();
						$_REQUEST['batch_id'][$k] = $new_sbi_id;
					}
				}
			}
		}

		$_REQUEST['t'] = 1;

		if(!$skip_redirect) $this->display();
	}


	function confirm(){
		global $hqcon,$smarty,$sessioninfo,$config,$LANG,$appCore;

		$errmsg = $this->bn_validate();

		if($errmsg){
			print "<div class=errmsg><ul>";
			foreach ($errmsg as $s)
			{
				print "<li> $s";
			}
			print "</ul></div>";
			exit;
		}

		$this->save(true); // re-save

		
		
		$form = $_REQUEST;
		
		$adj = array();
    	
		if($form['batch_id']){
			$hqcon->sql_query("select *, grn.department_id as dept_id
							   from grn left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
							   where grn.id = ".ms($form['id'])." and grn.branch_id = ".ms($form['branch_id']));
			$grn = $hqcon->sql_fetchrow();
			$hqcon->sql_freeresult();
			$adj['id'] = $appCore->generateNewID("adjustment", "branch_id=".mi($form['branch_id']));
			$adj['branch_id'] = $form['branch_id'];
			$adj['user_id'] = $sessioninfo['id'];
			$adj['dept_id'] = $grn['dept_id'];
			$adj['adjustment_date'] = $grn['rcv_date'];
			$adj['adjustment_type'] = "Batch No Setup";
			$adj['remark'] = "GRN #".$form['id'];
			$adj['status'] = 1;
			$adj['approved'] = 1;
			$adj['added'] = "CURRENT_TIMESTAMP";
			$adj['grn_id'] = $form['id'];
			$hqcon->sql_query("insert into adjustment ".mysql_insert_by_field($adj));
			$new_adj_id = $adj['id'];

			foreach($form['batch_id'] as $k=>$dummy){
				if($form['batch_no'][$k]){
					$hqcon->sql_query("select *
									   from grn_items gi 
									   left join sku_items si on si.id = gi.sku_item_id 
									   where gi.id = ".ms($form['grn_item_id'][$k])." 
									   and gi.branch_id = ".ms($form['branch_id']));

					$r1 = $hqcon->sql_fetchrow();
					$hqcon->sql_freeresult();
					$sku_id = mi($r1['sku_id']);
					
					if($form['grn_item_id'][$k] != $prv_gi_id){ // insert deduction from the master SKU
						$ttl_qty = 0;
						foreach($form['batch_id'] as $tmp=>$dummy1){
							if($form['grn_item_id'][$tmp] == $form['grn_item_id'][$k])
								$ttl_qty += $form['batch_qty'][$tmp];
							else continue;
						}
						if(!$ttl_qty) continue;
		
						$ai = array();
						$ai['id'] = $appCore->generateNewID("adjustment_items", "branch_id=".mi($form['branch_id']));
						$ai['adjustment_id'] = $new_adj_id;
						$ai['branch_id'] = $form['branch_id'];
						$ai['user_id'] = $sessioninfo['id'];
						$ai['sku_item_id'] = $r1['sku_item_id'];
						$ai['qty'] = $ttl_qty*-1;
						$ai['cost'] = $r1['cost'];
						$ai['selling_price'] = $r1['selling_price'];
						
						$hqcon->sql_query("select qty from sku_items_cost where branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($r1['sku_item_id']));
						$ai['stock_balance'] = $hqcon->sql_fetchfield(0);
						$hqcon->sql_freeresult();
						
						$hqcon->sql_query("insert into adjustment_items ".mysql_insert_by_field($ai));
						$hqcon->sql_query("update sku_items_cost set changed=1 where branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($r1['sku_item_id']));
					}
					$r2 = $hqcon->sql_query("select sbi.sku_item_id from sku_batch_items sbi where sbi.sku_item_id != 0 and sbi.batch_no = ".ms($form['batch_no'][$k])." and sbi.expired_date = ".ms($form['expired_date'][$k])." and sbi.grn_id != ".mi($form['id'])." and sbi.grn_item_id != ".mi($form['grn_item_id'][$k]));
					$sbi = $hqcon->sql_fetchrow($r2);

					if($hqcon->sql_numrows($r2) == 0){
						$findcode = (28000000 + intval($r1['sku_id'])).'%';
						$hqcon->sql_query("select max(sku_item_code), id from sku_items where sku_id=$sku_id");
						$r3 = $hqcon->sql_fetchrow();
						$hqcon->sql_freeresult();

						$hqcon->sql_query("insert into sku_items
										   (sku_id, sku_apply_items_id, sku_item_code, packing_uom_id, mcode, link_code, description, receipt_description, selling_price, cost_price, shelf_no, shelf_facing, shelf_depth, shelf_height, bestbuy, status, active, lastupdate, added, artno, block_list, misc_cost, open_price, hq_cost, ctn_1_uom_id, ctn_2_uom_id, location, decimal_qty, weight, size, color, flavor, misc, weight_kg)
										   select sku_id, sku_apply_items_id, ".ms($r3[0]+1).", packing_uom_id, mcode, link_code, description, receipt_description, selling_price, cost_price, shelf_no, shelf_facing, shelf_depth, shelf_height, bestbuy, status, active, lastupdate, added, artno, block_list, misc_cost, open_price, hq_cost, ctn_1_uom_id, ctn_2_uom_id, location, decimal_qty, weight, size, color, flavor, misc, weight_kg from sku_items where is_parent = 1 and id = ".ms($r3[1]));
						$new_sku_item_id = $hqcon->sql_nextid();
					}else{
						$new_sku_item_id = $sbi['sku_item_id'];
					}
					$hqcon->sql_freeresult();

					if($new_sku_item_id){
						$ai = array();
						$ai['id'] = $appCore->generateNewID("adjustment_items", "branch_id=".mi($form['branch_id']));
						$ai['adjustment_id'] = $new_adj_id;
						$ai['branch_id'] = $form['branch_id'];
						$ai['user_id'] = $sessioninfo['id'];
						$ai['sku_item_id'] = $new_sku_item_id;
						$ai['qty'] = $form['batch_qty'][$k];
						$ai['cost'] = $r1['cost'];
						$ai['selling_price'] = $r1['selling_price'];
						$ai['stock_balance'] = 0;
						$hqcon->sql_query("insert into adjustment_items ".mysql_insert_by_field($ai));
						log_br($sessioninfo['id'], 'SKU_BATCH_NO_SETUP', $form['id'], "SKU Item ID#".($new_sku_item_id)." from GRN ID#$form[id])");
					}

					$upd_sbi = array();
					$upd_sbi['sku_item_id'] = $new_sku_item_id;
					$upd_sbi['batch_no'] = $form['batch_no'][$k];
					$upd_sbi['qty'] = $form['batch_qty'][$k];
					$upd_sbi['expired_date'] = $form['expired_date'][$k];
					$upd_sbi['last_update'] = "CURRENT_TIMESTAMP";
					
					$hqcon->sql_query("update sku_batch_items set ".mysql_update_by_field($upd_sbi)." where id = ".mi($form['batch_id'][$k])." and branch_id = ".ms($form['branch_id']));

					$prv_gi_id = $form['grn_item_id'][$k];
				}
			}
			$hqcon->sql_query("update grn set batch_status = 1 where id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));

			//$_REQUEST['t'] = 2;
			//$this->display();
		}
	}

	function do_print(){
		global $con,$hqcon,$smarty,$sessioninfo;
		$form = $_REQUEST;

		$sql = "select *, si.description as sku_description, v.description as vendor, c.description as department, sbi.qty as batch_qty
		from `sku_batch_items` sbi
		left join `grn` on grn.id = sbi.grn_id and sbi.branch_id = sbi.branch_id
		left join sku_items si on si.id = sbi.sku_item_id
		left join `sku` on sku.id = si.sku_id
		left join grr on grr.id = grn.grr_id and grr.branch_id = grn.branch_id 
		left join vendor v on v.id = grn.vendor_id
		left join category c on c.id = grn.department_id
		where sbi.grn_id = ".mi($form['id'])." and sbi.branch_id = ".mi($form['branch_id'])." and grn.batch_status = 1
		order by si.sku_item_code";

		$hqcon->sql_query($sql);

		$curr_time = strtotime(date("Y-m-d"));

		while($r = $hqcon->sql_fetchrow()){
			$form['grr_id'] = $r['grr_id'];
			$form['vendor'] = $r['vendor'];
			$form['transport'] = $r['transport'];
			$form['department'] = $r['department'];
			$form['grr_ctn'] = $r['grr_ctn'];
			$form['grr_pcs'] = $r['grr_pcs'];
			$form['grr_amount'] = $r['grr_amount'];
			$items[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			$items[$r['sku_item_id']]['sku_description'] = $r['sku_description'];
			$items[$r['sku_item_id']]['location'] = $r['location'];
			$items[$r['sku_item_id']]['batch_no'] = $r['batch_no'];
			$items[$r['sku_item_id']]['expired_date'] = $r['expired_date'];
			$items[$r['sku_item_id']]['batch_qty'] += $r['batch_qty'];

			if($r['expired_date']){
				$expired_time = strtotime($r['expired_date']);
				
				if($curr_time >= $expired_time){
					$days_remain = mi(($curr_time-$expired_time)/86400)*-1;
					if($days_remain == 0) $days_remain = "Today";
				}else{
					$days_remain = mi(($expired_time-$curr_time)/86400);
				}

				$items[$r['sku_item_id']]['days_remain'] = $days_remain;
			}
		}
		
		$item_per_page= $config['masterfile_bn_report_print_item_per_page']?$config['masterfile_bn_report_print_item_per_page']:37;
		$item_per_lastpage = $config['masterfile_bn_report_print_item_last_page']>0 ? $config['masterfile_bn_report_print_item_last_page'] : $item_per_page-5;

		$totalpage = 1 + ceil((count($items)-$item_per_lastpage)/$item_per_page);

		$con->sql_query("select * from branch where id = ".mi($form['branch_id']));
		$smarty->assign("branch", $con->sql_fetchrow());
		$smarty->assign("form", $form);
		
		if($config['masterfile_bn_alt_print_template']) $tpl = $config['masterfile_bn_alt_print_template'];
		else $tpl = "masterfile_sku_items.batch_no_setup.print.tpl";
		
		for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
			if($page == $totalpage) $smarty->assign("is_last_page", 1);
			$smarty->assign("page", "Page $page of $totalpage");
	        $smarty->assign("start_counter", $i);
	        $smarty->assign("PAGE_SIZE", ($page < $totalpage) ? $item_per_page : $item_per_lastpage);
	        $items = array_slice($items,$i,$item_per_page);
	        $smarty->assign("items", $items);
			$smarty->display($tpl);
			$smarty->assign("skip_header",1);
		}
	}
	
	function ajax_add_batch_item(){
	    global $smarty,$sessioninfo,$con;
	    
	    sleep(1); // to prevent the user click too fast

	    $form = $_REQUEST;
		
		$q1 = $con->sql_query("select * from sku_items where id = ".mi($form['sid']));
		$si_info = $con->sql_fetchassoc($q1);
		$con->sql_numrows($q1);
		
	    $r['grn_item_id'] = $form['id'];
	    $r['id'] = time();
		$r['doc_allow_decimal'] = $si_info['doc_allow_decimal']; 
	    $smarty->assign("item", $r);
	
	  	$temp['html'] = $smarty->fetch("masterfile_sku_items.batch_no_setup.detail_row.tpl");
	  	$ret[] = $temp;
	  	print json_encode($ret);
	}

	function bn_validate(){
		global $hqcon,$smarty,$LANG,$sessioninfo;
		
		$form = $_REQUEST;
		$total_count = 1;
		$ttl_batch_qty = 0;
		$err = array();
		$curr_time = strtotime(date("Y-m-d"));
		
		/*
		$ttl_batch_qty = array();
		$batchno_list = array();
		if($form['batch_id'])
		{
			foreach ($form['batch_id'] as $k => $dummy)
			{
				$batch_no = trim($form['batch_no'][$k]);
				$gi_id = $form['grn_item_id'][$k];
				$ttl_batch_qty[$gi_id] += $form['batch_qty'][$k];
				
				//empty batchno
				if (empty($batch_no)) $err1[$gi_id]['bn_empty'] = $LANG['SKU_BN_EMPTY'];
				else
				{
					if ($batchno_list[$gi_id] && in_array($batch_no, $batchno_list[$gi_id])) $err1[$gi_id]['bn_dup'] = sprintf($LANG['SKU_BN_DUPLICATE'],$batch_no);
					else $batchno_list[$gi_id][] = $batch_no;
				}
				
				//empty expiry
				if (empty($form['expired_date'][$k])) $err1[$gi_id]['ed_error'] = $LANG['SKU_BN_ED_EMPTY'];
				else
				{
					//expiry < today
					$expired_time = strtotime($form['expired_date'][$k]);
					if ($curr_time > $expired_time) $err1[$gi_id]['ed_error'] = $LANG['SKU_BN_INVALID_ED'];
				}
				
				//invalid qty
				if ($form['batch_qty'][$k] < 0) $err1[$gi_id]['qty_error'] = $LANG['SKU_BN_QTY_INVALID'];
				
			}
			
			//total qty mismatch
			foreach ($form['grn_item_qty'] as $gi_id => $gi_id_qty)
			{
				if ($form['grn_item_qty'][$gi_id] != $ttl_batch_qty[$gi_id]) $err1[$gi_id]['total_error'] = $LANG['SKU_BN_QTY_MISMATCH'];
			}
			
			if ($err1)
			{
				foreach ($err1 as $gi_id => $elist)
				{
					$errmsg = join("<br />", $elist);
					$err[$gi_id] = "<li>".$form['sku_item_code'][$gi_id]." encounterred below:<br />".$errmsg;
				}
			}
		}
		else $err[] = $LANG['SKU_BN_NO_ITEM'];*/
		
		
		if($form['batch_id']){
			foreach($form['batch_id'] as $k=>$dummy){
				
				$batch_no = $form['batch_no'][$k];
				$curr_gi_id = $form['grn_item_id'][$k];
				
				// if found got different
				if($curr_gi_id != $prev_gi_id){
					if(!$prev_gi_id) $prev_gi_id = $curr_gi_id;
					// found qty over between grn item and ttl batch qty
					if($ttl_batch_qty > $form['grn_item_qty'][$prev_gi_id]) $tmp_err['qty_over'] = $LANG['SKU_BN_QTY_OVER'];
					// if found the grn item having batch items
					if(count($dup_dn) > 0) $tmp_err['bn_dup'] = sprintf($LANG['SKU_BN_DUPLICATE'], join(",", $dup_dn)); 
					if(count($tmp_err) > 0){
						$errmsg = join("<br />", $tmp_err);
						$err[$prev_gi_id] = "<li>".$form['sku_item_code'][$prev_gi_id]." encounterred below:<br />".$errmsg;
					}
					$curr_grn_item_qty = $form['grn_item_qty'][$prev_gi_id];
					$ttl_batch_qty = 0;
					$tmp_err = array();
					$dup_dn = array();
				}
				
				if(!$batch_no){ // found batch no is empty
					$tmp_err['bn_empty'] = $LANG['SKU_BN_EMPTY'];
				}elseif($batch_no){
					// check for duplicate items
					$hqcon->sql_query("select sbi.batch_no from sku_batch_items sbi left join sku_items si on si.id = sbi.sku_item_id left join grn on grn.id = sbi.grn_id and grn.branch_id = sbi.branch_id where si.sku_id = ".mi($form['sku_id'][$curr_gi_id])." and sbi.batch_no = ".ms($batch_no)." and sbi.expired_date = ".ms($form['expired_date'][$k])." and sbi.grn_id != ".mi($form['id'])." and sbi.grn_item_id != ".mi($curr_gi_id)." and grn.active=1");

					if($hqcon->sql_numrows() > 0) $dup_dn[] = $batch_no;
				}
				
				if(!$form['expired_date'][$k]){ // found expired date is empty
					$tmp_err['ed_empty'] = $LANG['SKU_BN_ED_EMPTY'];
				}else{
					// check if the expired date set by user already passed current day
					$expired_time = strtotime($form['expired_date'][$k]);
					if($curr_time > $expired_time) $tmp_err['ed_empty'] = $LANG['SKU_BN_INVALID_ED'];
				}
				
				if($form['batch_qty'][$k] < 0){ // found batch qty is empty or less than 0
					$tmp_err['qty_empty'] = $LANG['SKU_BN_QTY_INVALID'];
				}
				
				$prev_gi_id = $curr_gi_id;
				$ttl_batch_qty += $form['batch_qty'][$k];
				
				$ttl_batch_count++;

				if(count($form['batch_id']) == $ttl_batch_count){ // this is special checking for last Batch No item inserted
					// found qty over between grn item and ttl batch qty
					if($ttl_batch_qty > $form['grn_item_qty'][$curr_gi_id]) $tmp_err['qty_over'] = $LANG['SKU_BN_QTY_OVER'];
					// if found the grn item having batch items
					if(count($dup_dn) > 0) $tmp_err['bn_dup'] = sprintf($LANG['SKU_BN_DUPLICATE'], join(",", $dup_dn)); 
					if(count($tmp_err) > 0){
						$errmsg = join("<br />", $tmp_err);
						$err[$curr_gi_id] = "<li>".$form['sku_item_code'][$curr_gi_id]." encounterred below:<br />".$errmsg;
					}
				}
			}
		}else $err[] = $LANG['SKU_BN_NO_ITEM'];
		

		return $err;
	}
}

$SKU_Batch_No_Setup = new SKU_Batch_No_Setup('SKU Batch No Setup');
?>
