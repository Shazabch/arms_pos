<?php
/*
10/4/2011 5:56:32 PM Justin
- Changed the error message for empty "debtor" become "vendor".
- Added new function "search_po".

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.

2/25/2014 4:41 PM Justin
- Enhanced the search document able to search by PO or DO.

4/22/2015 5:11 PM Justin
- Enhanced to have GST information.

9/25/2018 1:56 PM Justin
- Enhanced to have "Document Date" and it is compulsory.
- Enhanced to check user input which do not allow to save more than 1 invoice in a GRR.

1/15/2020 10:35 AM Justin
- Enhanced to insert ID manually for some tables that uses auto increment.
- Bug fixed on search GRR No couldn't find the actual GRR while user provides document no.

9/21/2020 9：56 AM William
- Enhanced to block closed month document create and save when config "monthly_closing" and "monthly_closing_block_document_action" is active.
*/
include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('GRR')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRR', BRANCH_CODE), "/pda");

class GRR_Module extends Scan_Product{
    function __construct($title){
        global $sessioninfo;
        
        $_SESSION['scan_product']['type'] = 'GRR';
		$_SESSION['scan_product']['name'] = isset($_SESSION['grr']['id']) ? 'GRR#'.$_SESSION['grr']['id'] : '';
		
	    if(isset($_REQUEST['branch_id'])){
			if($_REQUEST['branch_id'] != $sessioninfo['branch_id']){    // prevent edit other branch
				header("Location: $_SERVER[PHP_SELF]");
				exit;
			}
		}
		parent::__construct($title);
	}
	
    function init_module(){
	    global $con, $smarty;

		$smarty->assign('PAGE_TITLE', $this->title);
		$smarty->assign('module_name', $this->title);
		$smarty->assign('top_include','goods_receiving_record.top_include.tpl');
		$smarty->assign('btm_include','goods_receiving_record.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if($id&&$branch_id){
			$this->reset_session_grr($id,$branch_id);
		}else{
            $id = mi($_SESSION['grr']['id']);
            $branch_id = mi($_SESSION['grr']['branch_id']);
		}

		$this->show_setting();
	}
	
	function show_scan_product(){
		$this->search_product();
	}

	function new_grr(){
		unset($_SESSION['grr']);
		header("Location: $_SERVER[PHP_SELF]");
	}
	
	function show_setting(){
		global $con, $smarty;

		$this->default_load();

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
            $id = mi($_SESSION['grr']['id']);
            $branch_id = mi($_SESSION['grr']['branch_id']);
		}

		if($id>0 && $branch_id>0){
			if($_SESSION['grr']['find_grr']) $find_grr = $_SESSION['grr']['find_grr'];
			elseif($_REQUEST['find_grr']) $find_grr = $_REQUEST['find_grr'];
		    $this->reset_module_session($id,$branch_id);
			$con->sql_query("select * from grr where grr.id = ".$id." and grr.branch_id = ".$branch_id);
			$form = $con->sql_fetchrow();
			$form['find_grr'] = $find_grr;
			$smarty->assign('form', $form);
		}

		$smarty->assign('grr_tab', 'setting');
		$smarty->display('goods_receiving_record.index.tpl');
	}
	
	private function default_load(){
		global $con,$smarty,$sessioninfo;

		// all branches
		$con->sql_query("select * from branch where active=1 order by sequence,code") or die(mysql_error());
		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches',$branches);

		// branches group
		//$this->load_branch_group();
		
		$con->sql_query("select * from vendor where active=1 order by code",false,false);
		if($con->sql_numrows()>0){
			while($r = $con->sql_fetchrow()){
				$vendor[$r['id']] = $r;
			}
			$smarty->assign('vendor',$vendor);
		}

		if ($sessioninfo['level'] < 9999){
			if (!$sessioninfo['departments']) $depts = "id in (0)";
			else $depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}else{
			$depts = 1;
		}
		// show department option
		$con->sql_query("select id, description from category where active = 1 and level = 2 and $depts order by description");
		$smarty->assign("dept", $con->sql_fetchrowset());

		$con->sql_query("select id, u from user left join user_privilege on user.id = user_id where privilege_code = 'GRR' and branch_id = $sessioninfo[branch_id] order by u");

		$smarty->assign("rcv", $con->sql_fetchrowset());
	}
	
	function save_setting(){
		global $con, $smarty, $sessioninfo, $config, $LANG, $appCore;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		$upd = array();
		$upd['rcv_date'] = $_REQUEST['rcv_date'];
        $upd['vendor_id'] = mi($_REQUEST['vendor_id']);
        $upd['department_id'] = mi($_REQUEST['department_id']);
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['user_id'] = $sessioninfo['id'];
		$upd['transport'] = $_REQUEST['transport'];
		$upd['rcv_by'] = mi($_REQUEST['rcv_by']);
		
		// validating
        if(!$upd['vendor_id'])   $err[] = "* Vendor is required";
        if(!$upd['rcv_date'])   $err[] = "* Received Date is required";
		else{
			$rcv_date = strtotime($upd['rcv_date']);
			if ($rcv_date > time()){
				$err[] = sprintf($LANG['GRR_INVALID_RECEIVE_DATE'], $form['rcv_date']);
			}
			
			if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
				$lower_limit = $config['lower_date_limit'];
				$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

				if ($rcv_date<$lower_date){
					$err[] = sprintf($LANG['GRR_DATE_OVER_LIMIT']);
				}
			}
		}
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($_REQUEST['rcv_date']);
			if($is_month_closed)  $err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}

		if(!$upd['transport'])  $err[] = "* Lorry No is required";
		if(!$upd['rcv_by'])  $err[] = "* Received by is required";

		// special checking for all PO
		$sql1 = $con->sql_query("select gi.* from grr_items gi where gi.grr_id = ".mi($id)." and gi.branch_id = ".mi($branch_id)." and gi.type = 'PO'");

		while($r1 = $con->sql_fetchrow($sql1)){
			$sql2 = $con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,
									 delivered,department_id,cancel_date,po_no 
									 from po 
									 where approved=1 and po_no = ".ms($r1['doc_no']));
			
			if($con->sql_numrows($sql2) > 0){
				$p = $con->sql_fetchrow($sql2);

				if ($upd['vendor_id'] != $p['vendor_id'])
					$err[] = $LANG['GRR_VENDOR_DIFFERENT_FROM_PO'];

				if (strtotime($upd['rcv_date']) >= dmy_to_time($p['cancel_date']))
					$err[] = sprintf($LANG['GRR_PO_CANNOT_RECEIVE_UPON_CANCEL_DATE'], $p['cancel_date']);

				if ($upd['department_id'] != $p['department_id'])
					$err[] = $LANG['GRR_PO_FROM_DIFFERENT_DEPARTMENT'];
			}
		}
		
		if($err){
			$this->default_load();
			if($id>0){
				$upd['id'] = $id;
				$upd['branch_id'] = $branch_id;
			}
			$smarty->assign('form',$upd);
			$smarty->assign('err',$err);
			$smarty->display('goods_receiving_record.index.tpl');
			exit;
		}

		// old GRR
		if($id>0){
            $con->sql_query("update grr set ".mysql_update_by_field($upd)." where id = ".mi($id)." and branch_id = ".mi($branch_id));
			$loc = "?t=update&id=".mi($id);
			$msg = "Updated";
		}else{  // new GRR
			// Get Max ID
			unset($new_id);
			$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($branch_id));
							
			if(!$new_id) die("Unable to generate new ID from appCore!");
			
			$upd['id'] = $new_id;
			$upd['branch_id'] = $branch_id;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			// check gst status
			$prms['date'] = $upd['rcv_date'];
			$prms['vendor_id'] = $form['id'];
			$upd['is_under_gst'] = check_gst_status($prms);
			
			$con->sql_query("insert into grr ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
			$loc = "?t=insert&id=".mi($id);
			$msg = "Inserted";
		}
		
		log_br($sessioninfo['id'], 'GRR', $id, $msg.": GRR".sprintf("%05d",$id));
		$smarty->assign('loc', $loc);
		$this->reset_module_session($id,$branch_id);
		header("Location: $_SERVER[PHP_SELF]".$loc);
	}
	
	private function reset_module_session($id,$branch_id){
	    global $con;

	    $con->sql_query("select id,branch_id,vendor_id from grr where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$form = $con->sql_fetchrow();

		if(!$form)	js_redirect('Invalid GRR', "index.php");

        $_SESSION['grr'] = $form;
	}
	
	function view_items(){
		global $con, $smarty;
		$id = mi($_SESSION['grr']['id']);
        $branch_id = mi($_SESSION['grr']['branch_id']);

        if(!$id||!$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		// load grr information
		$q1 = $con->sql_query("select * from grr where id = ".mi($id)." and branch_id = ".mi($branch_id));
		$grr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		// load item list
        $q1 = $con->sql_query("select gi.*
							 from grr_items gi
							 where gi.grr_id = ".mi($id)." and gi.branch_id = ".mi($branch_id)."
							 order by gi.id");
		$items = $con->sql_fetchrowset($q1);
		$con->sql_freeresult($q1);

        $smarty->assign('items',$items);
        $smarty->assign('form',$grr_info);
		$smarty->assign('grr_tab', 'view_items');
		$smarty->assign('find_grr', $_REQUEST['find_grr']);
		$smarty->display('goods_receiving_record.view_items.tpl');
	}
	
	function add_items(){}
	
	function save_items(){
        global $con, $smarty, $appCore, $config, $LANG;
		$grr_id = $_REQUEST['grr_id'] = mi($_SESSION['grr']['id']);
        $branch_id = $_REQUEST['branch_id'] = mi($_SESSION['grr']['branch_id']);
		$form = $_REQUEST;

        if(!$grr_id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		$err = array();
		$err = $this->validate_data($form);
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$q1=$con->sql_query("select rcv_date from grr where id=$grr_id and branch_id=$branch_id");
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$is_month_closed = $appCore->is_month_closed($r['rcv_date']);
			if($is_month_closed)  $err['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
		//print_r($err);
		if($err){
			foreach($_REQUEST['doc_no'] as $gi_id=>$doc_no){
				$item = array();
				$item['id'] = $gi_id;
				$item['doc_no'] = $_REQUEST['doc_no'][$gi_id];
				$item['doc_date'] = $_REQUEST['doc_date'][$gi_id];
				$item['prev_doc_no'] = $_REQUEST['prev_doc_no'][$gi_id];
				$item['type'] = $_REQUEST['type'][$gi_id];
				$item['prev_type'] = $_REQUEST['prev_type'][$gi_id];
				$item['ctn'] = $_REQUEST['ctn'][$gi_id];
				$item['pcs'] = $_REQUEST['pcs'][$gi_id];
				$item['amount'] = $_REQUEST['amount'][$gi_id];
				$item['gst_amount'] = $_REQUEST['gst_amount'][$gi_id];
				$item['remark'] = $_REQUEST['remark'][$gi_id];
				$items[] = $item;
			}

			$smarty->assign('items',$items);
			$smarty->assign('err',$err);
			$smarty->assign('grr_tab', 'view_items');
			$smarty->display('goods_receiving_record.view_items.tpl');
			exit;
		}

        if($form['doc_no']){
			foreach($form['doc_no'] as $gi_id=>$doc_no){
				$upd = array();
				$upd['po_id'] = $form['po_id'][$gi_id];
				$upd['doc_no'] = $doc_no;
				$upd['doc_date'] = $form['doc_date'][$gi_id];
				$upd['type'] = $form['type'][$gi_id];
				$upd['ctn'] = $form['ctn'][$gi_id];
				$upd['pcs'] = $form['pcs'][$gi_id];
				$upd['amount'] = $form['amount'][$gi_id];
				$upd['gst_amount'] = $form['gst_amount'][$gi_id];
				$upd['remark'] = $form['remark'][$gi_id];

			    if($doc_no){
					if(is_new_id($gi_id)){ // is insert
						// Get Max ID
						unset($new_id);
						$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($branch_id));
										
						if(!$new_id) die("Unable to generate new ID from appCore!");
						
						$upd['id'] = $new_id;
						$upd['grr_id'] = $grr_id;
						$upd['branch_id'] = $branch_id;
						$con->sql_query("insert into grr_items ".mysql_insert_by_field($upd));
					}else{ // is update
						$con->sql_query("update grr_items set ".mysql_update_by_field($upd)." where branch_id = ".mi($branch_id)." and grr_id = ".mi($grr_id)." and id=".mi($gi_id));
					}
				}

				if (($form['prev_type'][$gi_id] == "PO" && $form['prev_type'][$gi_id] != $form['type'][$gi_id]) || ($form['prev_type'][$gi_id] == $form['type'][$gi_id] && $form['prev_doc_no'][$gi_id] != $form['doc_no'][$gi_id])){
					if ($form['prev_doc_no'][$gi_id] != "") $con->sql_query("update po set delivered = 0 where po_no = ".ms($form['prev_doc_no'][$gi_id]));
				}

				if ($form['po_id'][$gi_id]>0){
					//$con->sql_query("update po set delivered = 1 where id = ".mi($form['po_id'][$gi_id])." and branch_id = ".mi($form['po_branch_id'][$gi_id]));
					$con->sql_query("update po set delivered = 1 where po_no = ".ms($form['doc_no'][$gi_id]));

					// PM to PO owner and FYI if
					if ($con->sql_affectedrows()>0){
						//$con->sql_query("select po.user_id from po where po.id = ".mi($form['po_id'][$gi_id])." and po.branch_id = ".mi($form['po_branch_id'][$gi_id]));
						$con->sql_query("update po set delivered = 1 where po_no = ".ms($form['doc_no'][$gi_id]));
						$t = $con->sql_fetchrow();
						$to[] = $t['user_id'];

						send_pm($to, "PO Received (".$form['doc_no'][$gi_id].") in GRR (Branch: ".BRANCH_CODE.", GRR".sprintf("%05d",$grr_id).")", "/po.php?a=view&id=".$form['po_id'][$gi_id]."&branch_id=".$form['po_branch_id'][$gi_id]);
					}
				}
			}
			log_br($sessioninfo['id'], 'GRR', $grr_id, "Saved: GRR".sprintf("%05d",$grr_id));
			$this->update_grr_total($branch_id, $grr_id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	private function update_grr_total($branch_id, $id){
	    global $con;
	    
        $con->sql_query("select sum(ctn) as total_ctn, sum(pcs) as total_pcs, sum(amount) as total_amt, sum(gst_amount) as total_gst_amt
						 from grr_items
						 where branch_id=$branch_id and grr_id=$id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$upd['grr_ctn'] = $form['total_ctn'];
		$upd['grr_pcs'] = $form['total_pcs'];
		$upd['grr_amount'] = $form['total_amt'];
		$upd['grr_gst_amount'] = $form['total_gst_amt'];
		
		$con->sql_query("update grr set ".mysql_update_by_field($upd)." where branch_id = ".mi($branch_id)." and id = ".mi($id));
	}
	
	function delete_items(){
		global $con, $smarty, $config, $appCore, $LANG;

		$id = mi($_SESSION['grr']['id']);
        $branch_id = mi($_SESSION['grr']['branch_id']);

        if(!$id || !$branch_id){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		//check monthly closed
		$err = array();
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$q1=$con->sql_query("select rcv_date from grr where id=$id and branch_id=$branch_id");
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$is_month_closed = $appCore->is_month_closed($r['rcv_date']);
			if($is_month_closed)  $err['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
		if($err){
			foreach($_REQUEST['doc_no'] as $gi_id=>$doc_no){
				$item = array();
				$item['id'] = $gi_id;
				$item['doc_no'] = $_REQUEST['doc_no'][$gi_id];
				$item['doc_date'] = $_REQUEST['doc_date'][$gi_id];
				$item['prev_doc_no'] = $_REQUEST['prev_doc_no'][$gi_id];
				$item['type'] = $_REQUEST['type'][$gi_id];
				$item['prev_type'] = $_REQUEST['prev_type'][$gi_id];
				$item['ctn'] = $_REQUEST['ctn'][$gi_id];
				$item['pcs'] = $_REQUEST['pcs'][$gi_id];
				$item['amount'] = $_REQUEST['amount'][$gi_id];
				$item['gst_amount'] = $_REQUEST['gst_amount'][$gi_id];
				$item['remark'] = $_REQUEST['remark'][$gi_id];
				$items[] = $item;
			}

			$smarty->assign('items',$items);
			$smarty->assign('err',$err);
			$smarty->assign('grr_tab', 'view_items');
			$smarty->display('goods_receiving_record.view_items.tpl');
			exit;
		}
		
		
		if($_REQUEST['item_chx']){
			// update all PO's delivered become 0 if any
            $sql = $con->sql_query("select * from grr_items where grr_id = ".mi($id)." and branch_id = ".mi($branch_id)." and id in (".join(',',array_keys($_REQUEST['item_chx'])).") and type = 'PO'");

			while($r = $con->sql_fetchrow($sql)){
				if($r['doc_no'] != "") $con->sql_query("update po set delivered = 0 where po_no = ".ms($form['doc_no']));
			}

			$con->sql_query("delete from grr_items
			where grr_id = ".mi($id)." and branch_id = ".mi($branch_id)." and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");
			
			log_br($sessioninfo['id'], 'GRR', $id, "Deleted (items): GRR".sprintf("%05d",$id));
			$this->update_grr_total($branch_id, $id);
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function open(){
		global $con, $smarty, $sessioninfo;

		if(isset($_REQUEST['find_grr'])){
			$branch_id = mi($sessioninfo['branch_id']);
			$find_grr = $_REQUEST['find_grr'];
			//$con->sql_query("select report_prefix from branch where id=$branch_id") or die(mysql_error());
			//$report_prefix = $con->sql_fetchfield(0);

			if (preg_match("/^grr/i", $_REQUEST['find_grr'])){
				$grrid = intval(substr($_REQUEST['find_grr'],3));
				$findstr = "and grr.id = $grrid";
			}
			else{
				// search documents
				$con->sql_query("select distinct(grr_id) from grr_items where branch_id=".mi($sessioninfo['branch_id'])." and doc_no like ".ms("%".$find_grr."%")." or grr_id = ".mi($find_grr));

				$idlist = array();
				// return if no match
				if (!$con->sql_numrows()) $idlist[] = 0;
				while($r=$con->sql_fetchrow()){
					$idlist[] = $r[0];
				}
				$findstr = "and grr.id in (".join(",",$idlist).")";
			}
			
			$sql = "select grr.*, vendor.code as vendor_code, vendor.description as vendor_desc
					from grr
					left join vendor on vendor.id = grr.vendor_id
					where grr.branch_id = ".mi($branch_id)." $findstr and grr.active=1 and grr.status=0";

			$con->sql_query($sql);
			if($con->sql_numrows()<=0){
				$err[] = "No GRR Found with $find_grr.";
			}else{
				while($r = $con->sql_fetchrow()){
					$grr_list[] = $r;
				}

				$smarty->assign('grr_list',$grr_list);
			}
		}

		$smarty->assign('err',$err);
		$smarty->display('goods_receiving_record.search.tpl');
	}
	
	function change_grr(){
	    global $con, $smarty;

        $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(!$id || !$branch_id){
           js_redirect('Invalid GRR', "index.php");
           exit;
		}else{
			$this->reset_module_session($id,$branch_id);
			if($_REQUEST['find_grr']) $_SESSION['grr']['find_grr'] = $_REQUEST['find_grr'];
		}

		header("Location: $_SERVER[PHP_SELF]");
	}

	
	function search_pp_pono($original_docno, &$ret){
		global $con, $smarty, $sessioninfo, $reset_doc_no ;

		if (preg_match("/^([A-Z]+)(\d+)\(PP\)$/", $original_docno, $matches)){
			$pp_repor_prefix=$matches[1];
			$pp_po_id=$matches[2];
			
			if($pp_repor_prefix=='HQ'){
				$q1=$con->sql_query("select po_no from po where hq_po_id = ".mi($pp_po_id)." and po_branch_id = ".mi($sessioninfo['branch_id']));
				$r1 = $con->sql_fetchrow($q1);		
			}
			else{
				$q0=$con->sql_query("select id from branch where report_prefix = ".ms($pp_repor_prefix));
				$r0 = $con->sql_fetchrow($q0);
				$pp_branch_id=$r0['id'];
				
				$q1=$con->sql_query("select po_no from po where branch_id = ".mi($pp_branch_id)." and id = ".mi($pp_po_id));
				$r1 = $con->sql_fetchrow($q1);		
			}		

			if($r1){
				$reset_doc_no=$r1['po_no'];
			}			
			
		}	
		$con->sql_query("select id, active, vendor_id, branch_id, po_branch_id, partial_delivery, delivered, department_id, cancel_date, po_no from po where approved = 1 and po_no = ".ms($reset_doc_no));		
		$ret = $con->sql_fetchrow();
		
		return $reset_doc_no;
	}

	function ibt_validation(){
		global $con, $LANG;

		$form = $_REQUEST;
		$is_ibt = 0;
		$non_ibt = 0;

		foreach ($form['doc_no'] as $n=>$doc_no){
			if($doc_no!=''){
				if($form['type'][$n] != "DO" && $form['type'][$n] != "PO") continue;
				/*
				search grr item doc no either is below statement:
				GRR doc_type = "DO" + GRR doc_no = DO do_no, update grn column is_ibt = 1
				GRR doc_type = "PO" + GRR doc_no = po po_no, update grn column is_ibt = 1
				*/

				if($form['type'][$n] == "DO"){
					$sql = $con->sql_query("select * from do where do_no = ".ms($doc_no)." and do_branch_id = ".mi($form['branch_id']));
				}elseif($form['type'][$n] == "PO"){
					$sql = $con->sql_query("select * from po where po_no = ".ms($doc_no)." and po_branch_id = ".mi($form['branch_id'])." and is_ibt = 1");
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
	
	function validate_data(&$form){
		global $con, $sessioninfo, $LANG, $config;

		$err = array();
		$doc_used = array();

		$invalid_ibt = $this->ibt_validation();
		if($invalid_ibt) $err['top'][] = $invalid_ibt;

		$department_id = 0;

		$con->sql_query("select rcv_date, department_id, vendor_id from grr where id = ".mi($form['grr_id'])." and branch_id = ".mi($form['branch_id']));
		$tmp = $con->sql_fetchrow();
		
		$form = array_merge($tmp, $form);
		
		foreach ($form['gi_id'] as $n=>$gi_id){
			if (trim($form['doc_no'][$n]) != ""){
				// make sure documents are not duplicated
				if (!isset($doc_used[$form['type'][$n]][$form['doc_no'][$n]])) $doc_used[$form['type'][$n]][$form['doc_no'][$n]] = 1;
				else $err[$n][]=sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], $form['grr_id']);
				
				if ($form['type'][$n] != 'PO'){
					$con->sql_query("select grr_id, gi.id
									 from grr_items gi
									 left join grr on grr_id = grr.id and grr.branch_id=gi.branch_id  
									 where gi.id <> ".mi($gi_id)." and grr.branch_id = ".mi($form['branch_id'])."
									 and grr.vendor_id = ".mi($form['vendor_id'])."
									 and gi.doc_no = ".ms($form['doc_no'][$n])." 
									 and gi.type = ".ms($form['type'][$n])."
									 and grr.active=1");

					if ($con->sql_numrows()>0){
						$r = $con->sql_fetchrow();
						$err[$n][] = sprintf($LANG['GRR_DOC_NO_DUPLICATE'], $form['type'][$n], $form['doc_no'][$n], $r['grr_id']);
					}
					
					if((!$form['ctn'][$n] && !$form['pcs'][$n]) || !$form['amount'][$n]) $err[$n][] = sprintf($LANG['GRR_ITEM_INCOMPLETE'], "Ctn or Pcs and Amount");
				}else{ 			// make sure the PO exist
					$con->sql_query("select id,active,vendor_id,branch_id,po_branch_id,partial_delivery,
									 delivered,department_id,cancel_date,po_no 
									 from po 
									 where approved=1 and po_no = ".ms($form['doc_no'][$n]));

					$p = $con->sql_fetchrow();

					if (!$p){
						$reset_doc_no = $this->search_pp_pono($form['doc_no'][$n], $p);
						if($reset_doc_no) $form['doc_no'][$n]=$reset_doc_no;						
					}

					if(!$p){
						$err[$n][] = sprintf($LANG['GRR_PO_NOT_FOUND'],$form['doc_no'][$n]);
					}elseif(!$p['active']){ // PO is inactive. prompt PO was Cancelled
						$err[$n][] = sprintf($LANG['GRR_PO_INACTIVE'],$form['doc_no'][$n]);
					}else{
						$form['po_id'][$n] = $p['id'];
						$form['po_branch_id'][$n] = $p['branch_id'];
						
						if ($p['vendor_id'] != $form['vendor_id'])
							$err[$n][] = $LANG['GRR_VENDOR_DIFFERENT_FROM_PO'];

						if(($p['po_branch_id']>0 && $p['po_branch_id'] != $form['branch_id']) ||($p['po_branch_id']==0 && $p['branch_id'] != $form['branch_id']))
							$err[$n][] = $LANG['GRR_INVALID_RECEIVING_BRANCH'];

						if ($p['delivered'] && !$p['partial_delivery'])
						{
							if ($form['grr_id']==0) // error if new grr gets re-deliver
								$err[$n][] = $LANG['GRR_PO_DELIVERED'];
							elseif ($form['prev_doc_no'][$n]!=$p['po_no']) // error if doc number is different
								$err[$n][] = $LANG['GRR_PO_DELIVERED'];
						}
						if (strtotime($form['rcv_date']) >= dmy_to_time($p['cancel_date']))
							$err[$n][] = sprintf($LANG['GRR_PO_CANNOT_RECEIVE_UPON_CANCEL_DATE'], $p['cancel_date']);

						if ($form['department_id'] != $p['department_id'])
							$err[$n][] = $LANG['GRR_PO_FROM_DIFFERENT_DEPARTMENT'];
					}
				}
				
				// check document date
				if(!trim($form['doc_date'][$n])) $err[$n][] = sprintf($LANG['GRR_ITEM_INCOMPLETE'], "Document Date");
			}elseif(!trim($form['doc_no'][$n]) && $form['gi_id'][$n]) $err[$n][] = sprintf($LANG['GRR_ITEM_INCOMPLETE'], "Document No");
		}

		// newly enhance, to make sure user key in at least one document for inv, do or other when found is grn future
		if($config['use_grn_future']){
			$type = "|".join("|", $form['type']);
			$have_do = 0;
			$have_inv = 0;
			$have_other = 0;

			if(preg_match("{DO}", $type)){
				$have_do = 1;
			}
			if(preg_match("{INVOICE}", $type)){
				$have_inv = 1;
			}
			if(preg_match("{OTHER}", $type)){
				$have_other = 1;
			}
			
			if(!$have_do && !$have_inv && !$have_other) $err['top'][] = $LANG['GRR_INVALID_DOCUMENT'];
		}
		
		// if found GRR having more than one INVOICE, prompt error
		if(count($doc_used['INVOICE']) >= 2) $err['top'][] = $LANG['GRR_MORE_THAN_ONE_INV'];
		
		return $err;
	}
	
	function search_document(){
		global $con;
		
		$doc_no = $_REQUEST['doc_no'];
		$doc_type = $_REQUEST['doc_type'];
		
		if($doc_type == "PO"){
			$q1 = $con->sql_query("select * from po where po_no = ".ms($doc_no)." and active=1");
			
			if($con->sql_numrows($q1) > 0){
				$info = $con->sql_fetchassoc($q1);
				print json_encode(array("department_id"=>$info['department_id'],"vendor_id"=>$info['vendor_id']));
			}else{
				print json_encode(array("err_msg"=>"No record found"));
			}
			$con->sql_freeresult($q1);
		}else{
			$q1 = $con->sql_query("select * from do where do_no = ".ms($doc_no)." and active=1 and do_type = 'transfer'");
			
			if($con->sql_numrows($q1) > 0){
				$info = $con->sql_fetchassoc($q1);
				print json_encode(array("department_id"=>$info['dept_id']));
			}else{
				print json_encode(array("err_msg"=>"No record found"));
			}
			$con->sql_freeresult($q1);
		}
	}
}

$GRR_Module = new GRR_Module('GRR');
?>
