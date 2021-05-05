<?php
/*
8/23/2019 2:42 PM Justin
- Bug fixed on department checking always activated even though the config do_approval_by_department is off.
- Bug fixed on driver info always can skip regardless the config do_checkout_no_need_lorry_info is turn off.

8/26/2019 5:32 PM Justin
- Bug fixed on system always prompt error message even got fill in the lorry no and driver name.

10/2/2019 10:28 AM Justin
- Bug fixed on system will prompt mysql error while DO contains only open items.
- Bug fixed on system will prompt error message saying the DO does not contain any item while there actually have open items.

10/8/2019 10:23 AM William
- Enhance to add first confirm time when do first update.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
include("do.include.php");

class DO_MULTI_CONFIRM_CHECKOUT extends Module{
	var $branches;
	var $do_type_list = array(
		'open' => 'Cash Sales DO',
		'credit_sales' => 'Credit Sales DO',
		'transfer' => 'Transfer DO'
	);
	var $limit_per_page = 20;
	
	function __construct($title){
		global $con, $smarty;
		
		$this->init_load();
		
		if($_REQUEST['process_type'] == "checkout") $title .= " Checkout";
		else $title .= " Confirm";
		
		parent::__construct($title);
	}
	
	function _default(){
	    $this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $appCore, $config;
		
		// assign do type list
		asort($this->do_type_list);
		$smarty->assign("do_type_list", $this->do_type_list);
		
		// load branch list for deliver to filter
		$this->branches = $appCore->branchManager->getBranchesList(array('active'=>1));
		$smarty->assign('branches', $this->branches);
		
		// load debtor for debtor filter
		$q1 = $con->sql_query("select * from debtor where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			$this->debtor_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("debtor_list", $this->debtor_list);
		
	}
	
	private function load_do_list(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		$filter = array();
		
		if($form['process_type'] == "checkout"){ // checkout do filter
			$filter[] = "do.do_no is not null and do.approved=1 and do.active=1 and do.status=1 and do.checkout=0";
		}else{ // saved do filter
			$filter[] = "do.status = 0 and do.approved=0 and do.active=1";
		}
		
		// filter do type if got provide
		if($form['do_type']){
			$filter[] = "do.do_type=".ms($form['do_type']);
		}
		
		// filter deliver branch if user select do type as "Transfer"
		if($form['deliver_to']){
			$filter[] = "(do.do_branch_id = ".mi($form['deliver_to'])." or do.deliver_branch like '%\"".mi($form['deliver_to'])."\"%')";
		}
		
		// found user was filtering debtor
		if($form['debtor_id']){
			$filter[] = "do.debtor_id=".ms($form['debtor_id']);
		}
		
		// filter user who created the do if he/she is not admin
		// applied for do multi confirm only
		if($sessioninfo['level'] < 9999 && !$form['process_type']){
			$filter[] = "do.user_id = ".mi($sessioninfo['id']);
		}
		
		// need to always filter branch if it is not consignment mode
		if(!$config['consignment_modules']){
			$filter[] = "do.branch_id = ".mi($sessioninfo['branch_id']);
		}
		
		$str_filter = join(' and ', $filter);
		$is_under_gst = 0;

		// get sku item id in this page
		$q1 = $con->sql_query("select do.*, b.report_prefix, sum(di.cost*((di.ctn * u.fraction) + di.pcs)) as cost, 
							  us.u as user_name, db.code as db_name, b.report_prefix as branch_prefix
							  from do
							  left join do_items di on di.do_id = do.id and di.branch_id = do.branch_id
							  left join uom u on u.id = di.uom_id
							  left join branch b on b.id = do.branch_id
							  left join user us on us.id = do.user_id
							  left join branch db on db.id = do.do_branch_id
							  where $str_filter
							  group by do.branch_id, do.id
							  order by do.last_update desc, do.do_type");
		while($r = $con->sql_fetchassoc($q1)){
			$r['open_info'] = unserialize($r['open_info']);
			$r['deliver_branch'] = unserialize($r['deliver_branch']);
			$r['checkout_info'] = unserialize($r['checkout_info']);
			$r['display_do_date'] = $r['do_date'];
			
			if($r['deliver_branch']){
				foreach($r['deliver_branch'] as $k=>$v){
					$r['d_branch']['id'][$k]=$v;
					$r['d_branch']['name'][$k]=$this->branches[$v]['code'];
				}
			}
			
			// found system returned error msg to user, therefore need to use form data
			if($this->use_form_data && $form['process_type'] == "checkout" && !$form['use_same_do_date']){
				$r['do_date'] = $form['do_date'][$r['branch_id']][$r['id']];
			}
			
			$form['do_list'][] = $r;
			if($r['is_under_gst']) $is_under_gst = 1;
		}
		$form['do_matched'] = $con->sql_numrows($q1);
		$con->sql_freeresult($q1);
		unset($str_filter);
		
		//print_r($form);
		$smarty->assign('form', $form);
		$smarty->assign('is_under_gst', $is_under_gst);
		return $form;
	}
	
	function ajax_reload_do_list(){
		global $con, $smarty, $sessioninfo;
		
		$this->init_load();
		$form = $this->load_do_list();
		//$smarty->assign('form', $form);
		
		$ret = array();
		$ret['ok'] = 1;
		if($this->do_err_list){
			$ret['is_error'] = true; // found got errors attached
			$smarty->assign("do_err_list", $this->do_err_list);
		}
		
		$ret['html'] = $smarty->fetch('do.multi_confirm_checkout.do_list.tpl');
		
		die(json_encode($ret));
	}
	
	function ajax_confirm_checkout_do(){
		global $con, $smarty, $sessioninfo, $appCore, $config;
		
		$form = $_REQUEST;
		$this->errm = array();
		
		// no DO were selected
		if(!$form['chk_do_list']){
			die("Please select a DO before confirm");
		}

		$this->do_err_list = array();
		$this->validate_data();
		
		// found having errors
		if($this->do_err_list){
			$this->use_form_data = true; // replace and use the data from form
			$this->ajax_reload_do_list();
			unset($this->use_form_data);
			return;
		}
		unset($this->do_err_list);
		
		foreach($form['chk_do_list'] as $bid=>$do_id_list){
			foreach($do_id_list as $do_id=>$is_checked){
				if(!$is_checked) continue;
				
				// get do information
				$q1 = $con->sql_query("select * from do where branch_id = ".mi($bid)." and id = ".mi($do_id));
				$do_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$upd = array();
				if($form['process_type'] == "checkout"){ // it is checkout DO
					if (!$do_info['first_checkout_date']){ // first time doing checkout for this DO
						$upd['first_checkout_date'] = "CURRENT_TIMESTAMP";
						
					}
					$upd['checkout_info'] = serialize($form['checkout_info']);
					$upd['checkout_by'] = $sessioninfo['user_id'];
					$upd['checkout'] = 1;
					$upd['checkout_remark'] = $form['checkout_remark'];
					if($form['use_same_do_date']) $upd['do_date'] = $form['same_do_date'];
					else $upd['do_date'] = $form['do_date'][$bid][$do_id];
					$upd['shipment_method'] = $form['shipment_method'];
					$upd['tracking_code'] = $form['tracking_code'];
					
					$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id = ".mi($bid)." and id = ".mi($do_id));
					
					// auto generate GRR & GRN
					$prms = array();
					$prms['id'] = $do_id;
					$prms['branch_id'] = $bid;
					$appCore->doManager->doGRNAutoGenerator($prms);
					
					// serial no handler
					unset($_REQUEST['do_id']);
					unset($_REQUEST['branch_id']);
					$_REQUEST['do_id'] = $do_id;
					$_REQUEST['branch_id'] = $bid;
					if($config['single_server_mode'] && $config['enable_sn_bn']) serial_no_handler("confirm");
					
					// select the sku item list and to be used for cost recalculation
					$sid_list = array();
					$q1 = $con->sql_query("select di.sku_item_id 
										   from do_items di 
										   left join do on do.id=di.do_id and do.branch_id=di.branch_id 
										   where di.branch_id = ".mi($bid)." and di.do_id=".mi($do_id)." and do.checkout=1 and do.approved=1 and do.status<2");
					while($r = $con->sql_fetchassoc($q1)){
						$sid_list[$r['sku_item_id']] = mi($r['sku_item_id']);
					}
					$con->sql_freeresult($q1);
					
					// mark sku_items as changed for cost recalculation
					// cannot do this outside of the loop due to the do_branch_id might different for each DO
					if($sid_list){
						$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id in (".mi($do_info['branch_id']).", ".mi($do_info['do_branch_id']).") and sku_item_id in (".join(',',$sid_list).")");
					}
					unset($sid_list);
					
					//send notification to user list if it was transfer DO
					if($do_info['do_type'] == "transfer"){
						$allowed_user = unserialize($do_info['allowed_user']);
						if ($allowed_user && is_array($allowed_user)){
							$user_list = array();
							foreach($allowed_user as $un){
								foreach($un as $uid => $dummy){
									$uid = mi($uid);
									if(!$uid)	continue;
									
									$user_list[$uid]=$uid;
								}
							}
							
							if($user_list){
								// send pm
								send_pm($user_list, "Delivery Order Checkout (DO No#".$do_info['do_no'].") had complete checkout", "do_checkout.php?a=view&id=".mi($do_id)."&branch_id=".mi($bid)."&do_type=transfer",-1,true);
							}
							unset($user_list);
						}
						unset($allowed_user);
					}
					
				}else{ // it is confirming DO
					// check approval via doManager.php
					$prms = array();
					$prms['dept_id'] = $do_info['dept_id'];
					$prms['branch_id'] = $bid;
					$prms['doc_amt'] = $do_info['total_amount'];
					if(isset($do_info['total_inv_amt'])) $prms['doc_amt'] = $do_info['total_inv_amt'];
					$prms['approval_history_id'] = $do_info['approval_history_id'];
					$ret = $appCore->doManager->doApprovalHandler($prms, $do_info);
					$upd['approval_history_id'] = $ret['approval_history_id'];
					$last_approval = $ret['last_approval'];
					$direct_approve_due_to_less_then_min_doc_amt = $ret['direct_approve_due_to_less_then_min_doc_amt'];
					
					// split by price type
					if($config['do_auto_split_by_price_type']){
						$prms = array();
						$prms['do_id'] = $do_id;
						$prms['branch_id'] = $bid;
						$prms['do_info'] = $do_info;
						$prms['use_tmp_tbl'] = false;
						$rs = $appCore->doManager->priceTypeHandler($prms, $ret_rs);
						
						// means the following DO is splitted by price type successfully
						if(!$rs['split_failed'] && $rs['do_id_arr']){
							continue; // no longer need to update current do as it being cancelled
						}
						
						if(isset($rs['remark'])) $upd['remark'] = $rs['remark'];
						if(isset($rs['do_markup'])) $upd['do_markup'] = $rs['do_markup'];
						if(isset($rs['markup_type'])) $upd['markup_type'] = $rs['markup_type'];
						unset($prms, $rs);
					}
					
					$do_info['approval_history_id'] = $upd['approval_history_id'] = $ret['approval_history_id'];
					$do_info['status'] = $upd['status'] = 1;
					if ($last_approval) $upd['approved'] = 1;
					
					// Check if first time confirm not exist, then update confirm_timestamp
					if($do_info['confirm_timestamp'] == 0){
						$upd['confirm_timestamp'] = 'CURRENT_TIMESTAMP';
					}
					$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id = ".mi($bid)." and id = ".mi($do_id));
					unset($upd);
					
					// auto approve the DO if it was last approval or send pm
					$formatted=sprintf("%05d",$do_id);
					//select report prefix from branch
					$q1 = $con->sql_query("select report_prefix from branch where id = ".mi($bid));
					$r=$con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					log_br($sessioninfo['id'], 'DELIVERY ORDER', $do_id, "Confirmed: (ID#".$r['report_prefix'].$formatted.", Pcs:".mf($do_info['total_pcs']).", Ctn:".mf($do_info['total_ctn']).", Amt:".sprintf("%.2f",$do_info['total_amount']).")");
					if ($last_approval){ // straight approve the DO if it was last approval
						if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
						do_approval($do_id, $bid, $do_info['status'], true, false);
					}else{ // update approval cycle and send pm to the next approval
						$con->sql_query("update branch_approval_history set ref_id=".mi($do_id)." where id=".mi($do_info['approval_history_id'])." and branch_id = ".mi($bid));
						$to = get_pm_recipient_list2($do_id,$do_info['approval_history_id'],0,'confirmation',$bid,'do');
						send_pm2($to, "Delivery Order Approval (ID#$do_id)", "do.php?page=$do_info[do_type]&a=view&id=$do_id&branch_id=$bid", array('module_name'=>'do'));
					}
				}
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		
		die(json_encode($ret));
	}
	
	function validate_data(){
		global $con, $config, $LANG, $appCore;
		
		$form = $_REQUEST;
		
		// get branch list
		$this->branches = $appCore->branchManager->getBranchesList();

		$approval_flow_list = array();
		foreach($form['chk_do_list'] as $bid=>$do_id_list){
			foreach($do_id_list as $do_id=>$is_checked){
				if(!$is_checked) continue;
				
				
				// get do information
				$q1 = $con->sql_query("select * from do where branch_id = ".mi($bid)." and id = ".mi($do_id));
				$do_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				// the amount is incorrect, need user to save the do again
				if($do_info['amt_need_update']){
					$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = "Required open and save to correct the amount.";
				}
				
				// unserialise the data
				if($do_info['deliver_branch']) $do_info['deliver_branch'] = unserialize($do_info['deliver_branch']);
				if($do_info['open_info']) $do_info['open_info'] = unserialize($do_info['open_info']);
				
				if($form['process_type'] == "checkout"){ // user is doing DO checkout
					// do date will base on user selection
					if($form['use_same_do_date']) $do_info['do_date'] = $form['same_do_date'];
					else $do_info['do_date'] = $form['do_date'][$bid][$do_id];
					$check_date = strtotime($do_info['do_date']);
				
					$global_gst_start_date = $config["global_gst_start_date"];
					$branch_gst_start_date = $sessioninfo["gst_start_date"];
					
					if ($do_info['is_under_gst']){
						if($check_date < strtotime($global_gst_start_date) && $check_date < strtotime($branch_gst_start_date)){
							$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['DO_DATE_OVER_LIMIT']);
						}
					}
					
					// means driver info is required, cannot left empty
					if(!$config['do_checkout_no_need_lorry_info'] && (!$form['checkout_info']['lorry_no'] || !$form['checkout_info']['name'])){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_CHECKOUT_DRIVER_INFO_REQUIRED'];
					}
				}else{ // user is doing confirm DO
					$check_date = strtotime($do_info['do_date']);

					// check if this do have department
					if($config['do_approval_by_department'] && !$do_info['dept_id']){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['DO_NO_DATA'], "department data");
					}
					
					// check if got do items or not
					$q1 = $con->sql_query("select * from do_items where branch_id = ".mi($bid)." and do_id = ".mi($do_id));
					
					if($con->sql_numrows($q1) == 0){ // no item found from do_items
						// check against do_open_items to see got items or not
						$q2 = $con->sql_query("select * from do_open_items where branch_id = ".mi($bid)." and do_id = ".mi($do_id));
						if($con->sql_numrows($q2) == 0) $this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['DO_EMPTY']);
						$con->sql_freeresult($q2);
					}else{ // found got items, check whether has qty or not
						while($di = $con->sql_fetchassoc($q1)){
							$curr_ctn = $curr_pcs = 0;
							if($do_info['deliver_branch']){ // it is deliver to multiple branches
								$di['ctn_allocation'] = unserialize($di['ctn_allocation']);
								$di['pcs_allocation'] = unserialize($di['pcs_allocation']);
								foreach($do_info['deliver_branch'] as $d_bid){
									$curr_ctn+=mf($di['ctn_allocation'][$d_bid]);
									$curr_pcs+=mf($di['pcs_allocation'][$d_bid]);
								}
							}else{
								$curr_ctn=mf($di['ctn']);
								$curr_pcs=mf($di['pcs']);
							}
							
							// if found one of the item contains zero qty, show errors
							if($curr_ctn<=0 && $curr_pcs<=0){ // got items but doesn't contains any qty
								$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_ITEM_ZERO_QTY'];
								break;
							}
						}
					}
					$con->sql_freeresult($q1);
					unset($di_info);
					
					// checking not to allow user to confirm this do if they are consignment customer
					// and the monthly report has been printed and confirmed
					if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($do_info['do_date'],$bid)) {
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
					}
					
					// check transaction end date
					if($config['consignment_modules']){
						// check deliver from
						if($this->branches[$bid]['trans_end_date'] > 0){
							$trans_end_times = strtotime($this->branches[$bid]['trans_end_date']);
							if($check_date > $trans_end_times){
								$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['MSTBRANCH_OVER_TRANS_END_DATE'], $this->branches[$bid]['code'],"for Deliver From");
							}
							unset($trans_end_times);
						}
						
						// check deliver to
						if($do_info['do_branch_id']){
							if($this->branches[$do_info['do_branch_id']]['trans_end_date'] > 0){
								$trans_end_times = strtotime($this->branches[$do_info['do_branch_id']]['trans_end_date']);
								if($check_date > $trans_end_times){
									$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['MSTBRANCH_OVER_TRANS_END_DATE'], $this->branches[$do_info['do_branch_id']]['code'], "for Deliver To");
								}
								unset($trans_end_times);
							}
						}
					}
					unset($check_date);

					if($do_info['create_type']==2){
						if(isset($do_info['open_info']['name']) && $do_info['open_info']['name']=='')
							$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_OPEN_INFO_NAME_EMPTY'];
						if(isset($do_info['open_info']['address']) && $do_info['open_info']['address']=='')
							$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_OPEN_INFO_ADDRESS_EMPTY'];
					}elseif($do_info['create_type']==4){
						$q1 = $con->sql_query("select * from sales_order where order_no=".ms($do_info['ref_no']));
						$sales_order = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						if(!$sales_order)   $this->do_err_list[$do_info['branch_id']][$do_info['id']][] = sprintf($LANG['SO_ORDER_NO_NOT_FOUND'], $do_info['ref_no']);
						unset($sales_order);
					}
					
					// check whether this dept from this do has approval flow					
					// always take branch id as key
					$af_key = $do_info['branch_id'];
					
					// add extra key as if do approval are base on department
					if($config['do_approval_by_department']) $af_key .= "-".$do_info['dept_id'];
					
					if(!isset($approval_flow_list[$af_key])){
						$filters = array();
						$filters[] = "branch_id=".mi($do_info['branch_id']);
						$filters[] = "type='DO'";
						if($config['do_approval_by_department']){
							$filters[] = "sku_category_id=".mi($do_info['dept_id']);
						}
						$filters[] = "active=1";
						$filter = join(" and ", $filters);
						$q1 = $con->sql_query("select * from approval_flow where ".$filter);
						$has_approval_flow = $con->sql_numrows($q1);
						$con->sql_freeresult($q1);
						
						// here we record down approval flow list base on branch + dept ID
						// so that we doesn't need to always run the query to re-check existed approval flow
						if($has_approval_flow > 0) $approval_flow_list[$af_key] = true;
						else $approval_flow_list[$af_key] = false;
						
						unset($has_approval_flow,$filters, $filter);
					}
					
					// doesn't have approval flow, keep this 
					//$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = "No Approval Flow to confirm this DO";
					if(!$approval_flow_list[$af_key]){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = "No Approval Flow to confirm this DO";
						continue;
					}
					unset($af_key);
				}
				
				// check do date
				if($do_info['do_date']){
					$arr= explode("-",$do_info['do_date']);
					$yy=$arr[0];
					$mm=$arr[1];
					$dd=$arr[2];
					if(!checkdate($mm,$dd,$yy)){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_INVALID_DATE'];
					}
				}else $this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_INVALID_DATE'];

				// check do date whether exceeded maximum date 
				if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
					$upper_limit = $config['upper_date_limit'];
					$upper_date = strtotime("+$upper_limit day" , strtotime("now"));

					if ($check_date>$upper_date){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_DATE_OVER_LIMIT'];
					}
					unset($upper_limit, $upper_date);
				}

				// check do date whether exceeded minimum date 
				if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
					$lower_limit = $config['lower_date_limit'];
					$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

					if ($check_date<$lower_date){
						$this->do_err_list[$do_info['branch_id']][$do_info['id']][] = $LANG['DO_DATE_OVER_LIMIT'];
					}
					unset($lower_limit, $lower_date);
				}
				
				unset($do_info);
			}
		}
	}
	
	// load last driver info for checkout DO
	function ajax_load_driver_info(){
		global $appCore;
		
		$ret = array();
		$ret = $appCore->doManager->loadDriverInfo();
		
		die(json_encode($ret));
	}
}

$DO_MULTI_CONFIRM_CHECKOUT = new DO_MULTI_CONFIRM_CHECKOUT('DO Multi');
?>