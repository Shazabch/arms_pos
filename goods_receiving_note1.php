<?php
/*
5/25/2008 3:40:25 PM - yinsee
- fix grn cost - last cost of HQ should take from HQ's GRN only

10/22/2008 4:54:54 PM yinsee
- no branch filter when search

11/6/2008 2:28:52 PM yinsee
- when add item, selling_price only take from masterfile (remove PO and GRN check)

6/23/2009 4:05 PM Andy
add cheking on $config['grn_alt_print_template'] to allow custom print

6/30/2009 4:04 PM Andy
- add GRN Tax
	- alter table grn add grn_tax double
	- alter table grn_items add original_cost double after uom_id
	- alter table tmp_grn_items add original_cost double after uom_id
	
8/3/2009 3:29:15 PM Andy
- Add reset function

8/7/2009 3:13:25 PM Andy
- collect sku items packing_uom_id as master_uom_id when add or load items

12/9/2009 4:39:05 PM Andy
- add don't filter branch if consignment module

1/4/2010 6:11:14 PM Andy
- change GRN owner filter to include PO owner

4/2/2010 4:05:24 PM Andy
- Make user as account peronal if the grn creator is last approval
- Add "Multiple add item" feature for GRN

4/7/2010 11:30:52 AM Andy
- Change to even if user is last approval, also don't direct verifid the GRN, but put it for amount check first

4/21/2010 11:41:22 AM Andy
- Fix GRN cannot delete item bugs

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

10/13/2010 3:34:39 PM Justin
- Added a foc variance sum up and updates for grn
- Added a bridge to extract selling price from SKU item history

11/26/2010 12:02:56 PM Justin
- Fixed the duplication of PO items created whenever user click confirmed and no approval flow found.
- Changed the selling price to get from history first.

1/6/2011 4:00:37 PM Justin
- Added the feature to straight become verified GRN and skip the account check section when found amount, qty and foc variances is tally.

2/18/2011 5:52:15 PM Alex
- fix checking on search at function load_grn_list

3/14/2011 3:14:21 PM Justin
- Created a new config (grn_use_auto_verify) require for activate auto verify while do confirm for GRN. 

3/31/2011 4:20:54 PM Justin
- Added checking for whether the GRN is from IBT and update is_ibt and from_branch_id. 

4/15/2011 5:29:50 PM Justin
- Added the update for authorize field from GRN since this version not using following field.

6/16/2011 10:14:49 AM Justin
- Modified the script to pick up price history to add 1 more day for rcv date.

6/24/2011 12:30:31 PM Justin
- Modified the ctn and pcs can accept decimal number.

7/13/2011 10:47:12 AM Justin
- Moved the copy po items feature to include file for the share with handheld.

7/26/2011 5:37:57 PM Justin
- enhance speed of searching => get_item_details

8/5/2011 10:40:21 AM Justin
- Modified the cost to pick up base on current GRR's received date.

8/10/2011 1:39:32 PM Justin
- Fixed the po cost to round up by 3.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.

10/7/2011 11:05:10 AM Andy
- Add GRN Scan Barcode can accept new format (ARMS Code, MCode, Art.No, Link Code)

2/25/2012 02:05:11 PM Justin
- Added new feature to copy DO items as if it is IBT DO.

3/1/2012 12:05:12 PM Alex
- add get_grn_barcode_info() to get info from grn barcode => grn_ajax_add_item()

3/9/2012 5:57:27 PM Andy
- Add checking for config when add grn items. (will ignore IBT GRN if found related config is not set).

4/20/2012 5:40:42 PM Alex
- add packing uom code

7/12/2012 11:32:23 AM Justin
- Bug fixed packing uom code that place before sku item.

7/13/2012 4:56:23 PM Justin
- Enhanced to pick up packing UOM fraction.

7/24/2012 11:30 AM Justin
- Added to pickup Account ID and Code.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search on GRN list.
- Enhanced to pickup branch code.

8/7/2012 5:57 PM Justin
- Enhanced to accept new grn barcode scanning format.

8/23/2012 6:11 PM Justin
- Added to pickup related invoice.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/30/2013 3:17 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

2/12/2014 5:42 PM Justin
- Bug fixed on IBT have wrongly updated into GRN table.

2/25/2014 2:00 PM Justin
- Enhanced to have checking of item whether same department with GRN, if not then print error (need config).
- Enhanced the module to use json instead of old method to insert new item.

3/31/2014 4:38 PM Justin
- Enhanced to check blocked and inactive SKU when scan barcode.

4/21/2014 10:01 AM Justin
- Enhanced to have checking on block items in GRN.

4/28/2014 11:04 AM Justin
- Enhanced to check block item in PO instead of GRN while config "check_block_grn_as_po" is turned on.

8/29/2014 11:24 AM Justin
- Enhanced to update batch no to add "C-xxx" wordings in order for user to reuse the batch no.
- Enhanced to auto cancel adjustment that created from Batch No Setup.

11/25/2015 11:00 AM Qiu Ying
- GRN can search GRR
*/

init_selection();

$branch_id = mi($_REQUEST['branch_id']);
if ($branch_id == 0){
	$branch_id = $sessioninfo['branch_id'];
}
$id = intval($_REQUEST['id']);

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){

		case 'open_grr':
			open_grr();
			 exit;

		case 'open':
			$con->sql_query("delete from tmp_grn_items where (grn_id>1000000000 and grn_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
		case 'refresh':
			grn_open($id, $branch_id);
			exit;
		
		case 'view':
			grn_view($id, $branch_id);
			exit;
			
		case 'print':
		    if($config['grn_alt_print_template'])   $print_tpl = $config['grn_alt_print_template'];
		    else    $print_tpl = 'goods_receiving_note.print.tpl';
			grn_print($id, $branch_id, $print_tpl);
			exit;		
			
		case 'cancel':
			grn_cancel($id, $branch_id);			
		    exit;
		    
		case 'confirm':
		case 'save':
			grn_save($id, $branch_id, ($_REQUEST['a']=='confirm'));
			exit;
	    		
		case 'ajax_add_item':
			grn_ajax_add_item($id, $branch_id);
			exit;
			
		case 'ajax_delete_row':	
	        $con->sql_query("delete from tmp_grn_items where id=$id and branch_id=$branch_id");		
			exit;
			
		case 'ajax_add_vendor_sku':
			grn_ajax_add_vendor_sku($id, $branch_id);
			exit;
						
		case 'ajax_show_vendor_sku':
			$smarty->assign("show_varieties",1);
		    get_vendor_sku('po.new.show_sku.tpl');
		    exit;
		    
		case 'ajax_expand_sku':
			if (!isset($_REQUEST['showheader'])) $smarty->assign("hideheader",1);
			$smarty->assign("show_varieties",0);
		    expand_sku(intval($_REQUEST['sku_id']));
		    exit;
						
  		case 'fix_acc_ctnpcs':	        		
        	fix_acc_ctnpcs();
			exit;
			
      	case 'update_total_selling':
			ini_set('display_errors',1);
           	update_total_selling($id,$branch_id);
            exit;
			
        case 'update_all_total_selling':
			ini_set('display_errors',1);
			update_all_total_selling('');
			exit;
	
        case 'update_big_selling':
			ini_set('display_errors',1);
			update_all_total_selling(' where total_selling>final_amount*2 ');
            exit;
			
        case 'update_zero_selling':
			ini_set('display_errors',1);
 			update_all_total_selling(' where total_selling=0 ');           
            exit;	
			        
		case 'ajax_load_grn_list':
			/*
			print '<pre>';
			print_r($_SESSION);
			print '</pre>';
			*/
			load_grn_list();
			exit;
        case 'do_reset':
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['curr_date'],$branch_id)) {
				$err['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
				grn_view($id, $branch_id);
				exit;
			}
		    do_reset($id,$branch_id);
		    exit;
		case 'ajax_add_item_row':
		    ajax_add_item_row($id, $branch_id);
		    exit;
		case 'check_tmp_item_exists':
		    check_tmp_item_exists();
		    exit;
		case 'ajax_check_item_dept':
			ajax_check_item_dept();
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
      		print_r($_REQUEST);
		    exit;
	}
}

list_grr();
$smarty->display("goods_receiving_note.home.tpl");
exit;

function grn_save($grn_id, $branch_id, $is_confirm){
	global $con, $smarty, $LANG, $sessioninfo, $config;
	$form=$_REQUEST;
	
	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['rcv_date'],$branch_id)) {
		$err['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
	}
	
	/*
	print '<pre>';
	print_r($form);
	print '</pre>';
	//die;
	*/
	
	save_grn_items();
    
	$grr_item_id = intval($form['grr_item_id']);	
	$q=$con->sql_query("select id from grn where active and grr_item_id=$grr_item_id and branch_id=$branch_id and id <> $grn_id");			
	if($r = $con->sql_fetchrow($q)){
		$smarty->assign("url", "/goods_receiving_note.php");
	    $smarty->assign("title", "Goods Receiving Note");
	    $smarty->assign("subject", sprintf($LANG['GRN_GRR_ITEM_USED'], $grr_item_id, $grn_id));
	    $smarty->display("redir.tpl");
		exit;
	}
	
	$qty=0;
	$tt=0;
	$form['have_variance']=0;
	$form['total_selling']=0;
	
	if($form['cost']){
		foreach($form['cost'] as $k=>$dummy){
		    $rowqty=$form['ctn'][$k]*$form['uomf'][$k]+$form['pcs'][$k];
		    $qty+=$rowqty;
			if($form['type']=='PO' && $rowqty!=$form['po_qty'][$k]){
			    $form['have_variance']+=abs($rowqty-$form['po_qty'][$k]);
			 
				// calculate the FOC variance...
				$con->sql_query("select
								sum(po_items.qty * order_uom_fraction + po_items.qty_loose) as po_qty,
								sum(po_items.foc * order_uom_fraction + po_items.foc_loose) as foc_qty
								from po_items 
								where po_items.id = ".ms($form['po_item_id'][$k])." and po_items.branch_id = ".ms($branch_id)."
								group by po_items.id");
				$foc_var = $con->sql_fetchrow();
				$con->sql_freeresult();
				
				$ext_qty = $rowqty - $foc_var['po_qty'];

				if($ext_qty>=0 && $ext_qty<$foc_var['foc_qty']){ // it is equal or grater than po qty and less than foc qty
					$form['foc_variance']+=$foc_var['foc_qty']-$ext_qty;
				}elseif($ext_qty<0){ // it is less than the po qty, straight add up the foc qty from po
					$form['foc_variance']+=$foc_var['foc_qty'];
				}
			 }  
			    
			if($form['selling_uomf']) $form['total_selling']+=round($rowqty*$form['selling_price'][$k]/$form['selling_uomf'][$k], 2);
		}
	}
	else
	{
		$err['top'][] = $LANG['GRN_NO_ITEM']; 
	}

	// qty can zero 
	// if ($qty==0) { $err['top'][] = $LANG['GRN_QTY_ZERO']; }
		
	  $last_approval = false;
    if(!$err && $is_confirm){
      /*if ($form['approval_history_id']){
	        $con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");	        
	        $con->sql_query("select id,approvals from branch_approval_history where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");
			    $astat = $con->sql_fetchrow();
			    if($astat){
            $params = array();
  			    $params['user_id'] = $sessioninfo['id'];
            $params['id'] = $astat['id'];
            $params['branch_id'] = $branch_id;
  			    $last_approval = check_is_last_approval_by_id($params, $con);
          }    
		  }*/
		  $params = array(); 
		  $params['type'] = 'GOODS_RECEIVING_NOTE';
		  $params['user_id'] = $sessioninfo['id'];
		  $params['reftable'] = 'grn';
		  $params['dept_id'] = $form['department_id']; 
		  $params['skip_approve'] = true;
      if($config['consignment_modules'] && $config['single_server_mode']){
        //$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE',1, 'grn','',false,$branch_id);
        $params['branch_id'] = 1;
        $params['save_as_branch_id'] = $branch_id; 
  		}else{
        //$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE', $branch_id, 'grn', " sku_category_id=$form[department_id] ", false);
        $params['branch_id'] = $branch_id;        
		  }
      if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
      $astat = check_and_create_approval2($params, $con);
       
      if(!$astat)
  			$err['top'][]=$LANG['GRN_NO_APPROVAL_FLOW'];
  		else{
  			$form['approval_history_id']=$astat[0];
     		//if($astat[1]=='|') $last_approval=true;
  		}  		
	}

	if(!$err){
		if(!is_new_id($grn_id)){
		    //if($config['grn_have_tax']) $save_grn_tax = ",grn_tax=".mf($form['grn_tax']);
		    $con->sql_query("update grn set have_variance=".mi($form['have_variance']).", foc_variance=".mi($form['foc_variance']).", total_selling=".mf($form['total_selling']).", amount=".mf($form['amount']).", final_amount=".mf($form['amount']).", department_id=".mi($form['department_id']).",grn_tax=".mf($form['grn_tax']).", authorized=1 where branch_id=$branch_id and id=$grn_id");
		}
		else{
			/*
			search grr item doc no either is below statement:
			GRR doc_type = "DO" + GRR doc_no = DO do_no, update grn column is_ibt = 1
			GRR doc_type = "PO" + GRR doc_no = po po_no, update grn column is_ibt = 1
			*/
			$con->sql_query("select * from grr_items where branch_id=$branch_id and id=".mi($form['grr_item_id']));
			$gi = $con->sql_fetchrow();

			if($gi['type'] == "DO"){
				$ibt = $con->sql_query("select * from do where do_no = ".ms($gi['doc_no'])." and do_branch_id = ".mi($branch_id));
			}elseif($gi['type'] == "PO"){
				$ibt = $con->sql_query("select * from po where po_no = ".ms($gi['doc_no'])." and po_branch_id = ".mi($branch_id));
			}
			$ibt_dtl = $con->sql_fetchrow($ibt);

			if($ibt_dtl){
				$is_ibt = 1;
				$from_branch_id = $ibt_dtl['branch_id'];
			}

		    /*if($config['grn_have_tax']){
                //$save_grn_tax_col = ',grn_tax';
                //$save_grn_tax_val = ','.mf($form['grn_tax']);
			}*/
		    $con->sql_query("insert into grn 
(branch_id, user_id, vendor_id, department_id, grr_id, grr_item_id, authorized, added, total_selling, amount, final_amount, have_variance ,grn_tax, is_ibt, from_branch_id) values
($branch_id,$sessioninfo[id],".mi($form['vendor_id']).",".mi($form['department_id']).",".mi($form['grr_id']).",".mi($form['grr_item_id']).", 1, CURRENT_TIMESTAMP,".mf($form['total_selling']).",".mf($form['amount']).",".mf($form['amount']).",".mi($form['have_variance'])." ,".mf($form['grn_tax'])." ,".mi($is_ibt)." ,".mi($from_branch_id).")");
		    $form['id']=$con->sql_nextid();
		    
			$con->sql_query("update grr set status=1 where branch_id=$branch_id and id=".mi($form['grr_id']));
			$con->sql_query("update grr_items gi left join po on po.po_no = gi.doc_no and gi.type = 'PO' set gi.grn_used=1, po.delivered=1, po.last_update=po.last_update where gi.branch_id=$branch_id and gi.id=".mi($form['grr_item_id']));
		}
			
		$q1=$con->sql_query("select * from tmp_grn_items 
where grn_id=$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id") or die(mysql_error());
		$first_id=0;
		while($r=$con->sql_fetchrow($q1)){
		    $r['branch_id']=$branch_id;
		    $r['grn_id']=$form['id'];
		    
		    $con->sql_query("insert into grn_items ".mysql_insert_by_field($r, array("branch_id", "grn_id", "sku_item_id", "uom_id", "artno_mcode", "po_cost", "selling_price", "cost", "ctn", "pcs", "po_qty", "po_item_id", "weight","selling_uom_id","original_cost")));
		    
		    if($first_id==0) $first_id=$con->sql_nextid();
		}

		if($first_id>0){
			if(!is_new_id($grn_id)){
				$con->sql_query("delete from grn_items where branch_id=$branch_id and grn_id=$grn_id and id<$first_id") or die(mysql_error());			
			}
			$con->sql_query("delete from tmp_grn_items where grn_id=$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id]") or die(mysql_error());	
		}
		else{
			die("System error: Insert grn_items failed. Please contact ARMS technical support.");
		}
	
		if ($is_confirm){

			if($config['grn_use_auto_verify']){
				if($form['amount']==$form['ttl_grr_amt'] && ($config['grn_verification_allow_qty_variance'] || $form['have_variance']==0) && $form['foc_variance']==0) {
					// no amount variance, and no qty variance or qty variance is allowed
					$by_account = $sessioninfo['id'];
					$account_amount = $form['amount'];
					$doc_no = $form['doc_no'];
			    	$acc_update = "CURRENT_TIMESTAMP";
			    	$approved=1;
				}
			
				$con->sql_query("update grn set status=1, approved=".mi($approved).", approval_history_id=".mi($form['approval_history_id']).", by_account=".mi($by_account).", account_amount=".mf($account_amount).", acc_adjustment=account_amount, account_doc_no=".ms($doc_no).", account_update=".ms($acc_update)." where branch_id=$branch_id and id=$form[id]");
			}else{
				$con->sql_query("update grn set status=1, approved=".mi($approved).", approval_history_id=".mi($form['approval_history_id'])." where branch_id=$branch_id and id=$form[id]");
			}

			if ($last_approval){
	        $con->sql_query("update grn set approved=1,by_account=$sessioninfo[id] where branch_id=$branch_id and id=$form[id]");
	        update_sku_item_cost($form['id'], $branch_id);
			}
			else{
				$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id=$branch_id");
			}
			
			// send pm
			//$recipients=$astat[2];
			//$recipients=str_replace("|$sessioninfo[id]|", "|", $recipients);
	       	//$to=preg_split("/\|/", $recipients);
	       	
			$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'grn');
			send_pm2($to, sprintf("GRN Confirmed (GRN%05d)",$form['id']), "/goods_receiving_note.php?a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'grn'));		
		}
		header("Location: /goods_receiving_note.php?t=$form[a]&id=$form[id]&la=$last_approval");
	}
	else{
		$smarty->assign("errm", $err);
		$_REQUEST['err'] = $err['top'];
		grn_open($grn_id, $branch_id);
	}
	exit;
}

function grn_cancel($grn_id, $branch_id){
	global $con, $smarty, $LANG, $config, $sessioninfo;
	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['rcv_date'],$branch_id)) {
		$err['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
		$smarty->assign("errm", $err);
		$_REQUEST['err'] = $err['top'];
		grn_open($grn_id, $branch_id);
		exit;
	}

    $con->sql_query("update grn 
	left join grr_items on grn.branch_id=grr_items.branch_id and grn.grr_item_id=grr_items.id
	set grn.active=0, grr_items.grn_used=0
	where grn.id=$grn_id and grn.branch_id=$branch_id") or die(mysql_error());
	
	// check if it is having Batch No Setup
	$q1 = $con->sql_query("select * from grn where id = ".mi($grn_id)." and branch_id = ".mi($branch_id)." and batch_status = 1");
	$grn_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// found it is having batch no setup
	if($grn_info){
		// update all batch no become "C-xxx"
		$con->sql_query("update sku_batch_items set batch_no = concat('C-', batch_no) where grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
		
		// update adjustment become inactive
		$upd = array();
		$upd['status'] = 5;
		$upd['approved'] = $upd['active'] = 0;
		$upd['cancelled'] = "CURRENT_TIMESTAMP";
		$upd['cancelled_by'] = $sessioninfo['id'];
		$upd['reason'] = "Canceled by GRN";
		$con->sql_query("update adjustment set ".mysql_update_by_field($upd)." where grn_id = ".mi($grn_id)." and branch_id = ".mi($branch_id));
	}

	log_br($sessioninfo['id'], 'GRN', $grn_id, "Goods Receiving Note canceled by $sessioninfo[u] for (ID#$grn_id)");
	
    header("Location: /goods_receiving_note.php?t=cancel&id=$grn_id");
}

function grn_ajax_add_item($grn_id, $branch_id){
	global $smarty, $LANG;

	$form=$_REQUEST;
	$grr['type']=$form['type'];
	$smarty->assign("grr", $grr);			
	save_grn_items();

    $grn_barcode = trim($_REQUEST['grn_barcode']);
/*
	if($grn_barcode){    // add item by using scan barcode
	    if (preg_match("/^00/", $grn_barcode))	// form ARMS' GRN barcoder
		{
			$sku_item_id=mi(substr($grn_barcode,0,8));
			$pcs=mi(substr($grn_barcode,8,4));
		}
		else	// from ATP GRN Barcode, try to search the link-code
		{
	        $linkcode=substr($grn_barcode,0,7);
			$pcs=mi(substr($grn_barcode,7,5));
			$con->sql_query("select id from sku_items where link_code = ".ms($linkcode));
			$sku_item_id=$con->sql_fetchfield(0);
			if (!$sku_item_id) fail(sprintf($LANG['DO_INVALID_ITEM'],$linkcode));
		}
	}else{
	    $sku_item_id = intval($form['sku_item_id']);
	}
*/
	$sku_info = get_grn_barcode_info($grn_barcode,true);

	$sku_item_id = $sku_info['sku_item_id'];
	$pcs = $sku_info['qty_pcs'];

	$r = get_items_details($sku_item_id);
	$r['pcs'] = $pcs;
	add_temp_item($r, $grn_id);			  			
    $smarty->assign("item", $r);
	$arr = array();
	$rowdata = $smarty->fetch("goods_receiving_note.new.list.tpl");		

	$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);
	$smarty->assign("form", $form);
		    	
	header('Content-Type: text/xml');
    print array_to_xml($arr);
	exit;
}

function grn_ajax_add_vendor_sku($grn_id, $branch_id){
	global $smarty;
	
	$form=$_REQUEST;
	/*
    $vid=intval($form['vendor_id']);
	$bid=$branch_id;
	*/
	save_grn_items();
	
	if(!$form['sel']){
		print "<script>cancel_vendor_sku();</script>";
		exit;
	}
	foreach($form['sel'] as $sku_id=>$dummy){
		$r=get_items_details(intval($sku_id));			
		add_temp_item($r, $grn_id);			  			
	    $smarty->assign("item", $r);
	}
	print "<script>refresh_tables();</script>";
}	


function add_temp_item(&$r, $grn_id){
	global $con, $branch_id, $sessioninfo;	

	$r['grn_id']=$grn_id;
	$r['branch_id']=$branch_id;
	$r['user_id']=mi($sessioninfo['id']);
	$r['sku_item_id']=mi($r['id']);

    if ($r['artno'] != '') $r['artno_mcode'] = $r['artno'];	
	else $r['artno_mcode'] = $r['mcode'];
	
	$r['selling_uom_id'] = '1';	
    if(!$r['uom_id']){
		$r['uom_id'] = '1';
		$r['uom_fraction'] = 1;	
	}
	else{
		$con->sql_query("select fraction from uom where id = ".mi($r['uom_id']));
		$x = $con->sql_fetchrow();
		$r['uom_fraction'] = $x[0] ? $x[0] : 1;
	}

	$con->sql_query("insert into tmp_grn_items ".mysql_insert_by_field($r, array("grn_id", "branch_id", "user_id", "sku_item_id", "artno_mcode", "uom_id", "cost", "selling_uom_id", "selling_price","pcs")));
 	$r['id'] = $con->sql_nextid();
 	return $r['id'];
}

function save_grn_items(){
	global $con, $branch_id;
	
	$form=$_REQUEST;
	if($form['uom_id']){
		foreach($form['uom_id'] as $k=>$v){
			$update = array();
			$update['selling_price'] = doubleval($form['selling_price'][$k]);
		    $update['cost'] = doubleval($form['cost'][$k]);
		    $update['uom_id'] = mi($form['uom_id'][$k]);
		    $update['ctn'] = doubleval($form['ctn'][$k]);
		    $update['pcs'] = doubleval($form['pcs'][$k]); 
		    $update['weight'] = doubleval($form['weight'][$k]); 
		    $update['original_cost'] = doubleval($form['original_cost'][$k]);
		    
			$con->sql_query("update tmp_grn_items set " . mysql_update_by_field($update) . " where id=$k and branch_id=$branch_id");
		}	
	}
}

function get_items_details($id){                //Get item details base on sku_item id
	global $con, $sessioninfo, $config;

	$form = $_REQUEST;
    // $branch_chk = "";

	//if (BRANCH_CODE!='HQ')
	//	$branch_chk = " grn_items.branch_id=$sessioninfo[branch_id] and ";
		
	// get last grn cost
	$filter_grn = array();
	if(BRANCH_CODE == 'HQ'){
		if(!$config['grn_do_branch2hq_update_cost']){
			$filter_grn[] = "gri.type<>'DO'";	// exclude DO
		}
	}else{
		if(!$config['grn_do_hq2branch_update_cost ']){
			$filter_grn[] = "not (gri.type='DO' and do.branch_id=1)";
		}
		if(!$config['grn_do_branch2branch_update_cost ']){
			$filter_grn[] = "not (gri.type='DO' and do.branch_id>1)";
		}
	}
	if($filter_grn)	$filter_grn = "and ".join(' and ', $filter_grn);
	
	$sql = "select sku_items.*, if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost) as cost, grn_items.uom_id as uom_id, uom.fraction as uom_fraction, grn_items.selling_uom_id as selling_uom_id,sku_items.packing_uom_id as master_uom_id, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, c.department_id
from grn_items
left join uom on uom_id=uom.id
left join sku_items on sku_item_id=sku_items.id
left join uom puom on puom.id=sku_items.packing_uom_id
left join grn on grn_items.grn_id=grn.id and grn_items.branch_id=grn.branch_id
left join grr_items gri on gri.branch_id=grn.branch_id and gri.id=grn.grr_item_id
left join grr on grn.grr_id=grr.id and grn.branch_id=grr.branch_id
left join do on do.do_no=gri.doc_no and gri.type='DO' and do.do_type='transfer' and do.do_branch_id=gri.branch_id
left join sku on sku.id = sku_items.sku_id
left join category c on c.id = sku.category_id
where grn.branch_id = ".mi($sessioninfo['branch_id'])." and grn.approved=1 and sku_item_id = ".mi($id)." and grr.rcv_date <= ".ms($form['rcv_date'])." $filter_grn
having cost >0 
order by grr.rcv_date desc limit 1";
	//print $sql;
	$items=$con->sql_query($sql);
	
	if($con->sql_numrows()==0){
	    //get from po

	  //  if (BRANCH_CODE!='HQ')
	//		$branch_chk = "po_items.branch_id =$sessioninfo[branch_id] and ";
			
		$items=$con->sql_query("select sku_items.*, round(po_items.order_price, ".mi($config['global_cost_decimal_points']).") as cost, po_items.order_uom_id as uom_id, uom.fraction as uom_fraction,po_items.selling_uom_id as selling_uom_id,sku_items.packing_uom_id as master_uom_id, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, c.department_id
from po_items
left join uom on order_uom_id = uom.id
left join sku_items on sku_item_id = sku_items.id
left join uom puom on puom.id=sku_items.packing_uom_id
left join po on po_items.po_id = po.id and po_items.branch_id=po.branch_id 
left join sku on sku.id = sku_items.sku_id
left join category c on c.id = sku.category_id
where po.branch_id = ".mi($sessioninfo['branch_id'])." and po.active and po.approved and sku_item_id = ".mi($id)." and po.po_date <= ".ms($form['rcv_date'])."
having cost > 0 
order by po.po_date desc limit 1");
	}

	if($con->sql_numrows()==0){
	    //get from master
		$items=$con->sql_query("select sku_items.*, sku_items.id as sku_item_id, sku_items.cost_price as cost,sku_items.packing_uom_id as master_uom_id, puom.code as packing_uom_code, puom.fraction as packing_uom_fraction, puom.fraction as uom_fraction, c.department_id
		from sku_items
		left join sku on sku_id = sku.id
		left join uom puom on puom.id=sku_items.packing_uom_id
		left join category c on c.id = sku.category_id
		where sku_items.id = ".mi($id));
	}
	$get = $con->sql_fetchassoc($items);

	// selling price

	// get selling price from history
	$r1 = $con->sql_query("select * from sku_items_price_history where added < date_add(".ms($form['rcv_date']).", interval 1 day) and sku_item_id = ".mi($id)." and branch_id = ".mi($sessioninfo['branch_id'])." order by added desc limit 1");
	$siph = $con->sql_fetchassoc($r1);

	if($siph['price']){
		$get['selling_price']=$siph['price'];
	}else{
		$q0=$con->sql_query("select price as selling_price from sku_items_price where sku_item_id = ".mi($id)." and branch_id = ".mi($sessioninfo['branch_id']));
		$r0 = $con->sql_fetchassoc($q0);

		if($r0['selling_price']){
			$get['selling_price']=$r0['selling_price'];
		}
	}
	return $get;
}


function open_grr(){
	global $con, $smarty, $LANG, $branch_id;
	
	$form=$_REQUEST;
	$grn_id=intval($form['id']);
	$grr_item_id = intval($form['grr_item_id']);
	// make sure this GRR is not used
	$con->sql_query("select id from grn where active and grr_item_id=$grr_item_id and branch_id=$branch_id");
	
	if (!$_REQUEST['test'] && $r = $con->sql_fetchrow()){
		$smarty->assign("url", "/goods_receiving_note.php");
	    $smarty->assign("title", "Goods Receiving Note");
	    $smarty->assign("subject", sprintf($LANG['GRN_GRR_ITEM_USED'], $grr_item_id, $r['id']));
	    $smarty->display("redir.tpl");
		exit;
	}
	grn_open($grn_id, $branch_id);
}

function grn_open($grn_id, $branch_id){
	global $con, $smarty, $LANG;
	$form=$_REQUEST;
	
	if ($grn_id==0){
		$grn_id=time();
		if($grn_id <= $_SESSION['grn_last_create_time']) {$grn_id = $_SESSION['grn_last_create_time']+1;}
		$_SESSION['grn_last_create_time'] = $grn_id;
		$form['id']=$grn_id;
	}

	//if the action is open and is not a NEW GRN
	if ($form['a']=='open' && !is_new_id($grn_id)){
		$form=load_grn_header($grn_id, $branch_id);	
		if(!$form){
		    $smarty->assign("url", "/goods_receiving_note.php");
		    $smarty->assign("title", "GRN (Goods Receiving Note)");
		    $smarty->assign("subject", sprintf($LANG['GRN_NOT_FOUND'], $grn_id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		// locked, not allow to edit
		if(($form['status']>0 && $form['status']!=2) || $form['active']!=1){
		    grn_view($grn_id, $branch_id);
		    exit;
		}
		if(!$form['err']) copy_to_tmp($grn_id, $branch_id);	
	}
	//if the grn is NEW
	else{
	    $grr_item_id=intval($form['grr_item_id']);
		$grr=load_grr_item_header($grr_item_id, $branch_id);
		$smarty->assign("grr", $grr);
		
		$form['id']=$grn_id;
		$form['vendor_id']=$grr['vendor_id'];
		$form['grr_id']=$grr['grr_id'];
		$form['grr_item_id']=$grr['grr_item_id'];
		$form['department_id']=$grr['department_id'];
		// have po, load from po
		if($form['a']!='refresh' && !$form['err']){
			if($grr['do_id'] > 0){
				copy_do_items($grr['doc_no'], $grn_id, $branch_id);
			}elseif ($grr['po_id'] > 0){
				copy_po_items($grr['doc_no'], $grn_id, $branch_id);
			}
		}
	}	
	$form['items']=load_grn_items($grn_id, $branch_id, false, true);
	$smarty->assign("form", $form);
	$smarty->display("goods_receiving_note.new.tpl");
}


function copy_to_tmp($grn_id, $branch_id){
	global $con, $sessioninfo;
	//delete ownself GRN items in tmp table
	$con->sql_query("delete from tmp_grn_items where grn_id=$grn_id and branch_id=$branch_id and user_id=$sessioninfo[id]");

	//copy grn_items to tmp table
	$q1=$con->sql_query("insert into tmp_grn_items 
(grn_id, branch_id, user_id, sku_item_id, artno_mcode, uom_id, cost, 
selling_uom_id, selling_price, po_qty, po_cost, po_item_id, weight, ctn, pcs,original_cost)
select 
$grn_id, branch_id, $sessioninfo[id], sku_item_id, artno_mcode, uom_id, cost, selling_uom_id, selling_price, po_qty, po_cost, po_item_id, weight, ctn, pcs, original_cost
from grn_items where grn_id=$grn_id and branch_id=$branch_id order by id");
}	

function grn_view($grn_id, $branch_id){
	global $con, $smarty, $LANG, $err;
	$form=load_grn_header($grn_id, $branch_id);		
	if(!$form){
	    $smarty->assign("url", "/goods_receiving_note.php");
	    $smarty->assign("title", "GRN (Goods Receiving Note)");
	    $smarty->assign("subject", sprintf($LANG['GRN_NOT_FOUND'], $grn_id));
	    $smarty->display("redir.tpl");
	    exit;
	}
	$form['items']=load_grn_items($grn_id, $branch_id, $form['po_items']);
	$smarty->assign("form", $form);
	$smarty->assign("errm", $err);
	$smarty->display("goods_receiving_note.view.tpl");
}

function update_all_total_selling($where){
	global $con;
    $grn=$con->sql_query("select id,branch_id from grn $where");
	echo "select id,branch_id from grn $where<br>";	
    print $con->sql_numrows() . " rows found<br />";
    while($r=$con->sql_fetchrow($grn)){
    	update_total_selling($r['id'],$r['branch_id']);
    }
    print "<li> Done.";
    exit;	
}

function fix_acc_ctnpcs(){
	global $con;
    $con->sql_query("update grn_items set acc_ctn=0 where acc_ctn is null and acc_pcs is not null") or die(mysql_error());
	print $con->sql_affectedrows()." acc_ctn updated<br />";		
    $con->sql_query("update grn_items set acc_pcs=0 where acc_ctn is not null and acc_pcs is null") or die(mysql_error());
    print $con->sql_affectedrows()." acc_pcs updated<br />";
}

function load_grn_list($t=1){
	global $con, $sessioninfo, $smarty, $config;

	if (isset($_REQUEST['t'])){
		$t = intval($_REQUEST['t']);
		// if t used and s unused, this is for prompt (cancel, confirm etc)
		// we will show "saved GRN"
		if ($t==0 && !isset($_REQUEST['search'])) $t=1;
	}

	if ($sessioninfo['level']>=9999) 
		$owner_check = "";	
	else
		$owner_check = "(flow_approvals like '%|$sessioninfo[id]|%' or notify_users like '%|$sessioninfo[id]|%' or grn.user_id = $sessioninfo[id] or po.user_id=$sessioninfo[id]) and ";

	switch ($t){
	    case 0:
			$str = $_REQUEST['search'];
			$vendor_id = $_REQUEST['vendor_id'];
			if(!$str && !$vendor_id) die('Cannot search empty string');

			$where = array();
			if($str){
				$where[] = '(grn.id = ' . mi($str) .' or grr_items.doc_no like '.ms("%$str%").' or  grn.grr_id = ' . mi($str) .')';
			}
			
			if($vendor_id){
				$where[] = "grn.vendor_id = ".mi($vendor_id);
			}
			
			$where = join(" and ", $where);
		
	        break;

		case 1: // show saved
        	$where = "grn.active = 1 and grn.status = 0";
        	break;

		case 2: // show cancelled
		    $where = "grn.active = 0";
		    break;

		case 3: // show confirmed
        	$where = "grn.active = 1 and grn.status = 1 and grn.approved = 0";
        	break;

		case 4: // show approved
        	$where = "grn.active = 1 and grn.status = 1 and grn.approved = 1";
        	break;

	}
	
	
	
	if(!$config['consignment_modules']){
		if ($t!=0) $where .= " and grn.branch_id = $sessioninfo[branch_id]";
	}

	if (isset($t)){
		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}
		$limit =  "limit $start, $sz";
	
		$con->sql_query("select count(*) from grn
left join branch_approval_history on grn.approval_history_id = branch_approval_history.id and grn.branch_id = branch_approval_history.branch_id
left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
left join po on po.id=grr_items.po_id and po.branch_id=grr_items.branch_id and grr_items.type='PO'
where $owner_check $where");

		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
		    if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start) $pg .= " selected";
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
	}

	$sql = "select grn.*, grr_items.doc_no, grr_items.type, branch_approval_history.approvals, branch_approval_history.flow_approvals,
	if (vendor.id, vendor.description, (select branch.description from do left join branch on do.branch_id = branch.id where do_no = grr_items.doc_no)) as vendor, branch.report_prefix,grr_items.type,po.user_id as po_user_id,branch_approval_history.approval_order_id, vendor.code as vendor_code, branch.code as branch_code,
	if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id,
	if(grr_items.type='PO',(select group_concat(gi.doc_no order by 1 separator ', ') from grr_items gi where gi.type='INVOICE' and gi.grr_id=grn.grr_id and gi.branch_id=grn.branch_id),'') as related_invoice
	from grn
	left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id = grr_items.branch_id
	left join vendor on grn.vendor_id = vendor.id
	left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grn.branch_id
	left join branch on grn.branch_id = branch.id
	left join branch_approval_history on (grn.approval_history_id = branch_approval_history.id and grn.branch_id = branch_approval_history.branch_id)
	left join po on po.id=grr_items.po_id and po.branch_id=grr_items.branch_id and grr_items.type='PO'
	where $owner_check $where order by grn.last_update desc $limit";
	//print $sql;
	$con->sql_query($sql);

	$smarty->assign("grn_list", $con->sql_fetchrowset());
	$smarty->display("goods_receiving_note.list.tpl");
}

function list_grr(){
	global $con, $smarty, $sessioninfo, $branch_id;

	if ($_REQUEST['find_grr'] != ''){
	    // strip "grr#####" prefix
	    if (preg_match("/^grr/i", $_REQUEST['find_grr'])){
	    	$grrid = intval(substr($_REQUEST['find_grr'],3));
	    	$findstr = "and grr.id = $grrid";
		}
		else{
		    // search documents
			$con->sql_query("select distinct(grr_id) from grr_items where branch_id=$sessioninfo[branch_id] and doc_no like " . ms($_REQUEST['find_grr']));

			// return if no match
			if (!$con->sql_numrows()) return;
			$idlist = array();
			while($r=$con->sql_fetchrow()){
			    $idlist[] = $r[0];
			}
		    $findstr = "and grr.id in (".join(",",$idlist).")";
		}
	}
	else
		$findstr = "and grr_items.grn_used=0"; 

	// show current grr
	$con->sql_query("select grr.*, grr_items.*, grr.id as grr_id, grr.rcv_date, vendor.description as vendor, user.u, 
					 user2.u as rcv_u, category.description as department, vendor.code as vendor_code,
					 if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id
					 from grr_items
					 left join grr on (grr_id = grr.id and grr_items.branch_id = grr.branch_id)
					 left join user on grr.user_id = user.id
					 left join user user2 on grr.rcv_by = user2.id
					 left join vendor on grr.vendor_id = vendor.id
					 left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = grr_items.branch_id
					 left join category on grr.department_id = category.id
					 where grr.branch_id=$sessioninfo[branch_id] and grr.active $findstr
					 order by grr.rcv_date desc, grr_items.id
					 limit 100") or die(mysql_error());

	$smarty->assign("grr", $con->sql_fetchrowset());
}

function ajax_add_item_row($grn_id, $branch_id){
    global $con, $smarty, $sessioninfo, $LANG, $config;
    
    $grn_barcode = trim($_REQUEST['grn_barcode']);
    $sku_info_arr = array();

	if($grn_barcode){    // add item by using scan barcode
		$sku_info=get_grn_barcode_info($grn_barcode);
	
		if ($sku_info['sku_item_id']){
			//check for block item
			$q1 = $con->sql_query("select doc_block_list, block_list, active from sku_items where id = ".mi($sku_info['sku_item_id'])." limit 1");
			$tmp_si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if($config['check_block_grn_as_po']) $block_list_array = unserialize($tmp_si_info['block_list']);
			else $block_list_array = unserialize($tmp_si_info['doc_block_list']);
			$active = $tmp_si_info['active'];

			$si_err = "";
			if ($block_list_array) {
				if($config['check_block_grn_as_po']) $in_block_list = isset($block_list_array[$sessioninfo['branch_id']]);
				else $in_block_list = isset($block_list_array['grn'][$sessioninfo['branch_id']]);
				
				if($in_block_list) $si_err = sprintf($LANG['DOC_ITEM_IS_BLOCKED'], "GRN");
			}elseif(!$active){ // is inactive item
				$si_err = $LANG['PO_ITEM_IS_INACTIVE'];
			}
			
			if($si_err){
				$tmp = array();
				$tmp['error'] = $si_err;
				$ret[] = $tmp;
				print json_encode($ret);
				exit;
			}
		
			$sku_item_id = $sku_info['sku_item_id'];
			$pcs = mf($sku_info['qty_pcs']);
			$selling_price = mf($sku_info['selling_price']);
			if(isset($sku_info['new_cost_price'])) $cost_price = $sku_info['new_cost_price'];
		}else{
			$tmp = array();
			$tmp['error'] = $sku_info['err'];
			$ret[] = $tmp;
			print json_encode($ret);
			exit;
		}
		/*
		$grn_barcode_type = mi($_REQUEST['grn_barcode_type']);
		$pcs = 0;
		
		if(!$grn_barcode_type){	// default
			if (preg_match("/^00/", $grn_barcode))	// form ARMS' GRN barcoder
			{
				$sku_item_id=mi(substr($grn_barcode,0,8));
				$pcs=mi(substr($grn_barcode,8,4));
			}
			else	// from ATP GRN Barcode, try to search the link-code
			{
		        $linkcode=substr($grn_barcode,0,7);
				$pcs=mi(substr($grn_barcode,7,5));
				$con->sql_query("select id from sku_items where link_code = ".ms($linkcode));
				$sku_item_id=$con->sql_fetchfield(0);
				if (!$sku_item_id) fail(sprintf($LANG['DO_INVALID_ITEM'],$linkcode));
			}
		}else{
			switch($grn_barcode_type){
				case 1:	// amrs code, mcode, link code
					$con->sql_query("select id from sku_items where sku_item_code=".ms($grn_barcode)." or mcode=".ms($grn_barcode)." or artno=".ms($grn_barcode)." or link_code=".ms($grn_barcode)." limit 1");
					$sku_item_id=$con->sql_fetchfield(0);
					$con->sql_freeresult();
					break;
				default:
					fail("Invalid GRN Barcode Type");
					break;
			}
			
			if (!$sku_item_id) fail(sprintf($LANG['DO_INVALID_ITEM'],$grn_barcode));
		}
	    */
		$sku_item_id_arr[] = $sku_item_id;
		$sku_info_arr[$sku_item_id]['pcs'] = $pcs;
		$sku_info_arr[$sku_item_id]['selling_price'] = $selling_price;
		if(isset($cost_price)) $sku_info_arr[$sku_item_id]['cost_price'] = $cost_price;
	}else{
	   $sku_item_id_arr = $_REQUEST['sku_code_list'];
	}
	
	if($_REQUEST['item_dept_filter']){
		$new_si_list = ajax_check_item_dept(1);
		unset($sku_item_id_arr);
		
		$sku_item_id_arr = $new_si_list;
	}
	
    if($sku_item_id_arr){
        $form=$_REQUEST;
		$smarty->assign("grr", $form);

		save_grn_items();

		$ret = $tmp = array();
		foreach($sku_item_id_arr as $sid){
			$err_msg = array();
            $r = get_items_details(intval($sid));

          	if($sku_info_arr[$sid]['pcs'] > 0){
				if(ceil($sku_info_arr[$sid]['pcs']) != $sku_info_arr[$sid]['pcs'] && !$r['doc_allow_decimal']){ // is decimal points qty
					$err_msg[] = "* SKU Item [".$r['sku_item_code']."] is not decimal points item, whereas qty auto set to empty.";
					$sku_info_arr[$sid]['pcs'] = "";
				}

				if ($r['uom_fraction'] == 1){
					$r['pcs'] = $sku_info_arr[$sid]['pcs'];
				}elseif ($r['uom_fraction'] > 1){
					$r['pcs'] = $sku_info_arr[$sid]['pcs'] % $r['uom_fraction'];
					$r['ctn'] = ($sku_info_arr[$sid]['pcs'] - $r['pcs']) / $r['uom_fraction'];
				}
			}
			
			// selling price from grn barcoder
			if($sku_info_arr[$sid]['selling_price']) $r['selling_price'] = $sku_info_arr[$sid]['selling_price'];
			
			// cost price from grn barcoder
			if(isset($sku_info_arr[$sid]['cost_price'])){
				if(!$sku_info_arr[$sid]['cost_price']){
					$err_msg[] = "* Unable to find Trade Discount for SKU Item [".$r['sku_item_code']."], whereas cost auto set to empty.";
				}
				$r['cost'] = $sku_info_arr[$sid]['cost_price'];
			}
			
            add_temp_item($r, $grn_id);
            $smarty->assign("item", $r);
			
			$tmp['ok'] = 1;
			$tmp['html'] = $smarty->fetch("goods_receiving_note.new.list.tpl");
			$tmp['item_id'] = $r['id'];
			$tmp['department_id'] = $r['department_id'];
			$tmp['si_desc'] = " * ".$r['description'];
			
			if($err_msg){
				$tmp['error'] = join("\n", $err_msg);
			}
			
			$ret[] = $tmp;
		}
	}else{
		$tmp = array();
		$tmp['error'] = "No item Found.";
		$ret[] = $tmp;
	}
	print json_encode($ret);
}

function check_tmp_item_exists() {
	
	global $con, $sessioninfo,$branch_id;
	
	if ($_REQUEST['uom_id']) {
		$sql = "select count(*) as c from tmp_grn_items where id in (".join(',',array_keys($_REQUEST['uom_id'])).") and branch_id = $branch_id limit 1";
		$con->sql_query($sql);
		if ($con->sql_fetchfield('c') == count($_REQUEST['uom_id'])) print 'OK';
		else print "Error saving document : Probably it is opened & saved before in other window/tab";
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}


function ajax_check_item_dept($item_need_filter=false){
	global $con, $sessioninfo, $branch_id;
	
	$si_list = $_REQUEST['sku_code_list'];
	$dept_id = $_REQUEST['department_id'];
	$item_diff_dept = 0;
	$tmp = $ret = $si_desc_list = $new_si_list = array();
	
	$q1 = $con->sql_query("select si.id as sku_item_id, c.department_id as dept_id, si.description
						 from sku_items si
						 left join sku on sku.id = si.sku_id
						 left join category c on c.id = sku.category_id
						 where si.id in (".join(",", $si_list).")");
	
	while($r = $con->sql_fetchassoc($q1)){
		if($r['dept_id'] != $dept_id){
			$item_diff_dept = 1;
			$si_desc_list[] = " * ".$r['description'];
		}elseif($item_need_filter) $new_si_list[] = $r['sku_item_id'];
	}
	
	if(!$item_need_filter){
		$ret = $tmp = array();
		$tmp['item_diff_dept'] = $item_diff_dept;
		$tmp['si_desc'] = join("\n", $si_desc_list);
		$ret[] = $tmp;
		
		print json_encode($ret);
	}else return $new_si_list;
}
?>
