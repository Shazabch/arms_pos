<?php
/*
10:45 AM 6/29/2012 Justin
- Added to update last update for SKU item to make changes for sync to frontend.

7/26/2012 4:46:34 PM Justin
- Enhanced to allow other users can access this module as long as they are not Guest.

7/27/2012 10:41:23 AM Justin
- Enhanced to capture max ID+1 for new future price master and detail records to resolve sock2 problem.

7/27/2012 3:17:34 PM Justin
- Fixed bug on getting wrong Master ID while adding sub qprice item.

8/16/2012 10:44 AM Andy
- Add when change price will also automatically copy price to item under same parent/child, same uom, same price typ. (need config sku_change_price_always_apply_to_same_uom)

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/29/2013 4:49 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.
- Fix Batch Price Change show wrong Approval sequence in Waiting for Approval list.

3/31/2014 4:38 PM Justin
- Enhanced to check inactive SKU when scan barcode.

7/17/2014 3:03 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.
- Bug fixed on qprice item list will not load out once the user change type from qprice > other type > qprice.

10/9/2014 4:19 PM Justin
- Enhanced to have GST calculation.

11/6/2014 2:17 PM Justin
- Enhanced to use global function to check GST status.

1/10/2015 11:07 AM Justin
- Bug fixed on system restore zero as selling price for price change when do cancel.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/13/2015 6:00 PM yinsee
- Add import from CSV (Mcode/Armscode, Price Type, Price) -- function import_csv()

3/19/2015 5:57 PM Justin
- Enhanced to take out the history lookup section.

3/21/2015 11:34 AM Justin
- Enhanced to have new feature "import from price wizard".
- Enhanced to allow user show full items while exceeded the maximum items.
- Enhanced to set time limit to unlimited when showing full items.

3/23/2015 5:45 PM Justin
- Enhanced to allow user to do cancel while in saved mode.

4/7/2016 10:46 AM Andy
- Enhanced to load item selling price before gst.

7/28/2016 4:18 PM Andy
- Fixed import csv to trim price type.

6/2/2017 13:28 Qiu Ying
- Enhanced to add a "Add All Price Type" button in order to add SKU with different price type

6/14/2017 13:41 Qiu Ying
- Bug fixed on price cannot be copied when item added by "Add All Price Type"

5/15/2018 5:13 PM Justin
- Bug fixed on user able to re-save, re-confirm and re-cancel the document if they change the URL from a=view to a=open.

11/9/2018 4:42 PM Justin
- Enhanced to have Remark data.

4/22/2021 5:40 PM Edward Au
- select region to tick on certain combo box
*/
include("include/common.php");
include("masterfile_sku_items.future_price.include.php");
//ini_set("display_errors",1);
ini_set("memory_limit", "1024M");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if($_REQUEST['a'] != "view" || !$sessioninfo['level']){
	if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
	elseif (!privilege('MST_SKU_UPDATE_FUTURE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE_FUTURE_PRICE', BRANCH_CODE), "/index.php");
}

class BATCH_PRICE_CHANGE_MODULE extends Module{
	var $page_size = 30;

	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);

		$con->sql_query("select code from trade_discount_type order by code");
		$default_tdt = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('discount_codes',$default_tdt);
		
		$gst_settings = check_gst_status();
		$smarty->assign("gst_settings", $gst_settings);
		
    	parent::__construct($title);
    }
	
    function _default(){
		global $con, $smarty;
	    $this->display();
	}
	
	function ajax_list_sel(){
        global $con, $smarty, $sessioninfo;
        $page_size = $this->page_size;

		$t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);

		$size = $page_size;
		$start = $p*$size;

		switch($t){
			case 1:	// save
				$filter[] = "sifp.status=0 and sifp.approved=0";
				break;
			case 2: // waiting for approval
				$filter[] = "sifp.status=1 and sifp.approved=0";
				break;
			case 3: // rejected
				$filter[] = "sifp.active=1 and sifp.status=2";
				break;
			case 4: // cancelled/terminated
				$filter[] = "(sifp.active=0 or sifp.status=5)";
				break;
			case 5: // approved
				$filter[] = "sifp.status=1 and sifp.approved=1";
				break;
			case 6: // searching
				if(!$_REQUEST['search_str']) die('Cannot search empty string');

				$filter[]  = "(sifp.id = ".mi($_REQUEST['search_str']).")";
				$smarty->assign('search_str', $str);
				break;
			default:
				die('Invalid Page');
		}

		if($sessioninfo['level'] < 9999) $filter[] = "sifp.user_id = ".mi($sessioninfo['id']);
		
		$filter = join(" and ", $filter);

		$sql = $con->sql_query("select count(*)
								from sku_items_future_price sifp
								where sifp.branch_id = ".mi($sessioninfo['branch_id'])." and ".$filter);

		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult($sql);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";

		$total_page = ceil($total_rows/$size);
		$sql = $con->sql_query("select sifp.*, u.u as username, bah.approvals, bah.approval_order_id
								from sku_items_future_price sifp
								left join user u on u.id = sifp.user_id
								left join branch_approval_history bah on bah.id = sifp.approval_history_id and bah.branch_id = sifp.branch_id
								where sifp.branch_id = ".mi($sessioninfo['branch_id'])." and $filter
								order by sifp.last_update desc $limit");

		while($r = $con->sql_fetchassoc($sql)){
			$r['effective_branches'] = unserialize($r['effective_branches']);
			$fp_list[] = $r;
		}
		$con->sql_freeresult($sql);
		$smarty->assign('fp_list',$fp_list);
		$smarty->assign('total_page',$total_page);
		$smarty->assign('item_counter',$start+1);
		$smarty->display('masterfile_sku_items.future_price.list.tpl');
	}

	function view(){
		global $con, $smarty, $LANG;
		$smarty->assign("readonly", 1);
		$this->open();
	}
	
	function open($err=false){
		global $con, $smarty, $sessioninfo;
		$fp_id = $_REQUEST['id'];
		$branch_id = $_REQUEST['branch_id'];

		//print_r($sessioninfo);
		// get existing grn batch items
		if(!$err){
			if($fp_id && $branch_id){
				// header
				$form = load_header($fp_id, $branch_id);
				
				// details
				$items = load_items($fp_id, $branch_id);
				$form = array_merge($form, $items);
				
				if($form['status'] && $form['status']!=2){
					$smarty->assign("readonly", 1);
				}
			}else{
				$form['id'] = time();
				$form['branch_id'] = $sessioninfo['branch_id'];
				$form['date'] = date("Y-m-d");

				if (isset($_FILES['csv']))
				{
					$items = $this->import_csv();
					$form = array_merge($form, $items);
				}
			}
			//print_r($form);
			$smarty->assign("form", $form);
		}

		$brn_grp_list = array();
		$q1 = $con->sql_query("select bg.*, group_concat(bgi.branch_id separator ',') as grp_items from branch_group bg 
							left join branch_group_items bgi on bg.id=bgi.branch_group_id group by bg.id");
	
		while($r = $con->sql_fetchassoc($q1)){
		$brn_grp_list[] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r ($brn_grp_list);
		$smarty->assign('brn_grp_list', $brn_grp_list);


		$smarty->display("masterfile_sku_items.future_price.open.tpl");
	}

	function do_print(){
		fp_print();
	}
	
	function confirm(){
		$this->save(true);
	}

	function save($is_confirm=false){
		global $con, $smarty, $LANG, $sessioninfo, $config;
		$form=$_REQUEST;
		$fp_id = $form['id'];
		$branch_id = $form['branch_id'];
			
		$err = validate($form);

		$last_approval = false;
		if(!$err && $is_confirm){
			$status = 1;
			$params = array(); 
			$params['type'] = 'MST_FUTURE_PRICE';
			$params['user_id'] = $sessioninfo['id'];
			$params['reftable'] = 'future_price';
			if($config['consignment_modules'] && $config['single_server_mode']){
				//$astat = check_and_create_branch_approval('GOODS_RECEIVING_NOTE',1, 'grn','',false,$branch_id);
				$params['branch_id'] = 1;
				$params['save_as_branch_id'] = $branch_id; 
			}else{
				$params['branch_id'] = $branch_id;        
			}
			// use back the same id if already have
			if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id'];
			$astat = check_and_create_approval2($params, $con);

			// is last approval
			if($astat[1]=='|') $last_approval = true;

			if(!$astat) $err['mst'][]=$LANG['GRN_NO_APPROVAL_FLOW'];
			else $_REQUEST['approval_history_id']=$form['approval_history_id']=$astat[0];
		}

		if($err){
			$smarty->assign("form", $form);
			$smarty->assign("errm", $err);
			$this->open($err=true);
			return;
		}

		$effective_branches = array();
		if($form['effective_branches']){
			foreach($form['effective_branches'] as $bid=>$val){
				if($form['date_by_branch']){
					$effective_branches[$bid]['hour'] = $form['branch_hour'][$bid];
					$effective_branches[$bid]['minute'] = $form['branch_minute'][$bid];
					$effective_branches[$bid]['date'] = $form['branch_date'][$bid];
				}else $effective_branches[$bid] = $bid;
			}
		}
		
		if(!is_new_id($fp_id)){
			$upd['status'] = $status;
			$upd['date_by_branch'] = $form['date_by_branch'];
			$upd['date'] = $form['date'];
			$upd['hour'] = $form['hour'];
			$upd['minute'] = $form['minute'];
			if($effective_branches) $upd['effective_branches'] = serialize($effective_branches);
			$upd['approval_history_id'] = $form['approval_history_id'];
			$upd['remark'] = $form['remark'];
			$upd['last_update'] = "CURRENT_TIMESTAMP";

			$con->sql_query("update sku_items_future_price set ".mysql_update_by_field($upd)." where branch_id=".mi($branch_id)." and id=".mi($fp_id));
		}
		else{
			$ins = array();
			$ins['branch_id'] = $branch_id;
			$ins['date_by_branch'] = $form['date_by_branch'];
			$ins['date'] = $form['date'];
			$ins['hour'] = $form['hour'];
			$ins['minute'] = $form['minute'];
			if($effective_branches) $ins['effective_branches'] = serialize($effective_branches);
			$ins['status'] = $status;
			$ins['remark'] = $form['remark'];
			$ins['approval_history_id'] = $form['approval_history_id'];
			$ins['user_id'] = $sessioninfo['id'];
			$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
			
			$con->sql_query("select max(id) from sku_items_future_price where branch_id = ".mi($branch_id));
			$ins['id'] = $con->sql_fetchfield(0);
			$ins['id'] += 1;
			$con->sql_freeresult();
			
			$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
			$form['id']=$fp_id=$con->sql_nextid();
		}
		
		// **** MOVED FOLLOWING FUNCTION TO cron.future_price.php ****
		//$curr_date = date("Y-m-d h:i:s");
		// search for old info from normal price, mprice and qprice
		/*foreach($form['si_id'] as $id=>$sid){
			if($form['type'][$id] == "normal"){
				foreach($form['effective_branches'] as $bid=>$val){
					$q1 = $con->sql_query("select price, cost, source, ref_id, user_id, trade_discount_code from sku_items_price_history where sku_item_id = ".mi($sid)." and branch_id = ".mi($bid)." and added <= ".ms($curr_date)." order by added desc limit 1");
					$price_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					if($price_info) $form['old_info'][$id][$bid] = $price_info;
					else{ // in case user never done any price update before, need to pickup info from master
						$q1 = $con->sql_query("select if(sip.price is null,si.selling_price, sip.price) as price, sku.default_trade_discount_code,
											   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
											   from sku_items si
											   left join sku on sku_id = sku.id
											   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)." 
											   where si.id = ".mi($sid));

						$t=$con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						$price_info = array();
						$price_info['price'] = $t['price'];
						$price_info['user_id'] = $sessioninfo['id'];
					
						// must hv price type
						if($config['sku_always_show_trade_discount']){
							$price_info['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
						}
						
						$prms = array();
						$prms['sku_item_id'] = $sid;
						$prms['branch_id'] = $bid;
						$tmp = $this->get_last_cost($prms);
						$price_info['cost'] = $tmp['cost'];
						$price_info['source'] = $tmp['source'];
						
						$form['old_info'][$id][$bid] = $price_info;
					}
				}
			}elseif($form['type'][$id] == "qprice"){
				if(isset($qprice_info_existed[$sid])) continue;
				else $qprice_info_existed[$sid] = 1;
				foreach($form['effective_branches'] as $bid=>$val){
					$q1 = $con->sql_query("select min_qty, price from sku_items_qprice where sku_item_id = ".mi($sid)." and branch_id = ".mi($bid));

					while($r1 = $con->sql_fetchassoc($q1)){
						$form['old_info'][$id][$bid][] = $r1;
					}
					$con->sql_freeresult($q1);
				}
			}else{
				foreach($form['effective_branches'] as $bid=>$val){
					$q1 = $con->sql_query("select type, price, user_id, trade_discount_code from sku_items_mprice_history where sku_item_id = ".mi($sid)." and branch_id = ".mi($bid)." and type = ".ms($form['type'][$id])." order by added desc limit 1");
					$mprice_info = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					if($mprice_info) $form['old_info'][$id][$bid] = $mprice_info;
					else{ // in case user never done any price update before, need to pickup info from master
						$q1 = $con->sql_query("select if(sip.price is null,si.selling_price, sip.price) as price, sku.default_trade_discount_code,
											   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
											   from sku_items si
											   left join sku on sku_id = sku.id
											   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)." 
											   where si.id = ".mi($sid));

						$t=$con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						$mprice_info = array();
						$mprice_info['price'] = $t['price'];
						$mprice_info['user_id'] = $sessioninfo['id'];

						// must hv price type
						if($config['sku_always_show_trade_discount']){
							$mprice_info['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
						}
						
						$form['old_info'][$id][$bid] = $mprice_info;
					}
				}
			}
		}*/
			
		foreach($form['si_id'] as $id=>$sid){
			if(!$form['is_deleted'][$id]){
				if(!is_new_id($id)){
					$upd = array();
					$upd['type'] = $form['type'][$id];
					$upd['trade_discount_code'] = $form['trade_discount_code'][$id];
					$upd['min_qty'] = $form['min_qty'][$id];
					$upd['future_selling_price'] = $form['future_selling_price'][$id];
					//$upd['old_info'] = serialize($form['old_info'][$id]);
					
					$con->sql_query("update sku_items_future_price_items set ".mysql_update_by_field($upd)." where id = ".mi($id)." and fp_id = ".mi($fp_id)." and branch_id = ".mi($branch_id));
				}else{
					$ins = array();
					$ins['branch_id'] = $branch_id;
					$ins['fp_id'] = $fp_id;
					$ins['sku_item_id'] = $sid;
					$ins['cost'] = $form['cost'][$id];
					$ins['selling_price'] = $form['selling_price'][$id];
					$ins['type'] = $form['type'][$id];
					$ins['trade_discount_code'] = $form['trade_discount_code'][$id];
					$ins['min_qty'] = $form['min_qty'][$id];
					$ins['future_selling_price'] = $form['future_selling_price'][$id];
					//$ins['old_info'] = serialize($form['old_info'][$id]);
					
					$con->sql_query("select max(id) from sku_items_future_price_items where branch_id = ".mi($branch_id));
					$ins['id'] = $con->sql_fetchfield(0);
					$ins['id'] += 1;
					$con->sql_freeresult();
					
					if($form['gst_settings']){
						$ins['gst_id'] = $form['gst_id'][$id];
						$ins['gst_rate'] = $form['gst_rate'][$id];
						$ins['gst_code'] = $form['gst_code'][$id];
					}
					
					$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
				}
			}elseif(!is_new_id($id)){
				$con->sql_query("delete from sku_items_future_price_items where fp_id = ".mi($fp_id)." and branch_id = ".mi($branch_id)." and id = ".mi($id));
			}
		}
	
		if ($is_confirm){
			$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
			
		    if ($last_approval){
                future_price_approval($form['id'], $branch_id, $status, true);
                $t = 'approved';
			}else{
                $t = 'confirmed';
				$to = get_pm_recipient_list2($fp_id,$form['approval_history_id'],0,'confirmation',$branch_id,'future_price');
				send_pm2($to, "Batch Price Change Approval (ID#$fp_id)", "masterfile_sku_items.future_price.php?a=view&id=$fp_id&branch_id=$branch_id", array('module_name'=>'future_price'));
			}
		}else{
	        log_br($sessioninfo['id'], 'FUTURE_PRICE', $form['id'], "Saved: (ID#".$form['id'].", BRANCH_ID#".$branch_id.")");
	        $t = 'saved';
		}
		
		header("Location: /masterfile_sku_items.future_price.php?act=$form[a]&id=$form[id]&la=$last_approval");
	}

	function cancel(){
		global $con, $sessioninfo, $config;
		$form=$_REQUEST;
		
		$q1 = $con->sql_query("select * from sku_items_future_price where id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
		$info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		// do restore for existing selling price while it is under approved status
		if($info['status'] && $info['approved']){
			$q1 = $con->sql_query("select * from sku_items_future_price_items sifpi where sifpi.fp_id = ".mi($form['id'])." and sifpi.branch_id = ".mi($form['branch_id'])." order by id");
			
			while($r = $con->sql_fetchassoc($q1)){
				$old_info = unserialize($r['old_info']);
				
				if(!$old_info) continue;

				foreach($old_info as $bid=>$r1){
					if($r['type'] != "qprice" && !$r1['price']){ // if found no data from old info, need to pickup info from masterfile
						$q1 = $con->sql_query("select if(sip.price is null,si.selling_price, sip.price) as price, sku.default_trade_discount_code,
											   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
											   from sku_items si
											   left join sku on sku_id = sku.id
											   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)." 
											   where si.id = ".mi($r['sku_item_id']));

						$t=$con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						$r1['price'] = $t['price'];
						$r1['user_id'] = $sessioninfo['id'];
					
						// must hv price type
						if($config['sku_always_show_trade_discount']){
							$r1['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
						}
						
						// pickup for normal selling price only
						if($r['type'] == "normal"){
							$prms = array();
							$prms['sku_item_id'] = $r['sku_item_id'];
							$prms['branch_id'] = $bid;
							$tmp = $this->get_last_cost($prms);
							$r1['cost'] = $tmp['cost'];
							$r1['source'] = $tmp['source'];
						}
					}
				
					if($r['type'] == "qprice"){
						// check if got user manual add qprice
						$q2 = $con->sql_query("select * from sku_items_qprice_history where sku_item_id = ".mi($r['sku_item_id'])." and branch_id = ".mi($bid)." order by added desc limit 1");
						$qprice_info = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);

						if($qprice_info['fp_id'] == $form['id'] && $qprice_info['fpi_id'] == $r['id'] && $qprice_info['fp_branch_id'] == $form['branch_id'] && !$qprice_si_existed[$r['sku_item_id']]){
							foreach($r1 as $row=>$r2){
								// insert into qprice table
								$ins = array();
								$ins['branch_id'] = $bid;
								$ins['sku_item_id'] = $r['sku_item_id'];
								$ins['min_qty'] = $r2['min_qty'];
								$ins['price'] = $r2['price'];
								$ins['last_update'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($qprice_info['added'])));
								
								$con->sql_query("replace into sku_items_qprice ".mysql_insert_by_field($ins));
								
								// insert into qprice history table
								$ins = array();
								$ins['branch_id'] = $bid;
								$ins['sku_item_id'] = $r['sku_item_id'];
								$ins['min_qty'] = $r2['min_qty'];
								$ins['price'] = $r2['price'];
								$ins['added'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($qprice_info['added'])));
								$ins['user_id'] = $sessioninfo['id'];

								$con->sql_query("replace into sku_items_qprice_history ".mysql_insert_by_field($ins));
							}
							$qprice_si_existed[$r['sku_item_id']] = 1;
						}
					}elseif($r['type'] == "normal"){
						// get last normal price
						$q2 = $con->sql_query("select * from sku_items_price_history where sku_item_id = ".mi($r['sku_item_id'])." and branch_id = ".mi($bid)." order by added desc limit 1");
						$price_info = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);

						if($price_info['fp_id'] == $form['id'] && $price_info['fpi_id'] == $r['id'] && $price_info['fp_branch_id'] == $form['branch_id']){
							// insert into price table
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $r['sku_item_id'];
							$ins['last_update'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($price_info['added'])));
							$ins['price'] = $r1['price'];
							$ins['cost'] = $r1['cost'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							
							$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
							
							// insert into price history table
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $r['sku_item_id'];
							$ins['added'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($price_info['added'])));
							$ins['price'] = $r1['price'];
							$ins['cost'] = $r1['cost'];
							$ins['source'] = $r1['source'];
							$ins['ref_id'] = $r1['ref_id'];
							$ins['user_id'] = $r1['user_id'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];

							$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
						}
					}else{
						// get last mprice
						$q2 = $con->sql_query("select * from sku_items_mprice_history where sku_item_id = ".mi($r['sku_item_id'])." and branch_id = ".mi($bid)." order by added desc limit 1");
						$mprice_info = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);

						if($mprice_info['fp_id'] == $form['id'] && $mprice_info['fpi_id'] == $r['id'] && $mprice_info['fp_branch_id'] == $form['branch_id']){
							// insert into mprice table
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $r['sku_item_id'];
							$ins['type'] = $r['type'];
							$ins['last_update'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($mprice_info['added'])));
							$ins['price'] = $r1['price'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];
							
							$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($ins));
							
							// insert into mprice history table
							$ins = array();
							$ins['branch_id'] = $bid;
							$ins['sku_item_id'] = $r['sku_item_id'];
							$ins['type'] = $r['type'];
							$ins['added'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($mprice_info['added'])));
							$ins['price'] = $r1['price'];
							$ins['user_id'] = $r1['user_id'];
							$ins['trade_discount_code'] = $r1['trade_discount_code'];

							$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($ins));
						}
					}
				}
				$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = ".mi($r['sku_item_id']));
			}
		}

		$con->sql_query("update sku_items_future_price
						 set active=0, status=5, last_update = CURRENT_TIMESTAMP
						 where id=".mi($form['id'])." and branch_id=".mi($form['branch_id']));

		log_br($sessioninfo['id'], 'FUTURE_PRICE', $form['id'], "Cancelled: (ID#".$form['id'].", BRANCH_ID#".$form['branch_id'].")");

		if($form['is_ajax']) $xtra_url = "&t=5";
		header("Location: /masterfile_sku_items.future_price.php?act=cancel&id=".mi($form['id']).$xtra_url);
	}

	/*function recall(){
		global $con, $sessioninfo;
		$form=$_REQUEST;

		$con->sql_query("update sku_items_future_price
						 set status = 0, last_update = CURRENT_TIMESTAMP
						 where id=".mi($form['id'])." and branch_id=".mi($form['branch_id']));

		log_br($sessioninfo['id'], 'FUTURE_PRICE', $form['id'], "Recalled: (ID#".$form['id'].", BRANCH_ID#".$form['branch_id'].")");
		header("Location: /masterfile_sku_items.future_price.php?act=recall&id=".mi($form['id']));
	}*/

	function get_item_info($sid){
		global $con, $sessioninfo, $config;
		
		$q1=$con->sql_query("select sku.sku_type, si.id as sku_item_id, si.sku_item_code, si.description, si.artno, 
							si.mcode, si.doc_allow_decimal, ifnull(sip.price, si.selling_price) as selling_price,
							ifnull(sic.grn_cost, si.cost_price) as cost, si.sku_id, puom.fraction as packing_uom_fraction, sic.qty as stock_bal,
							sku.category_id, sku.mst_output_tax, si.output_tax, sku.mst_inclusive_tax, si.inclusive_tax, sku.default_trade_discount_code,
							if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
							from sku_items si
							left join sku on sku.id = si.sku_id
							left join uom puom on puom.id=si.packing_uom_id
							left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
							left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($sessioninfo['branch_id'])."
							where si.id = ".mi($sid));
		$info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
	
		// load output tax list
		$output_tax_list = array();
		$q1 = $con->sql_query("select * from gst where active=1 and type = 'supply'");

		while($r = $con->sql_fetchassoc($q1)){
			$output_tax_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// must hv price type
		if($config['sku_always_show_trade_discount']){
			$info['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
		}
	
		if($_REQUEST['gst_settings']){
			// get category tax info
			$cat_output_tax = get_category_gst("output_tax", $info['category_id'], array('no_check_use_zero_rate'=>1));
			$cat_inclusive_tax = get_category_gst("inclusive_tax", $info['category_id']);
			
			// get output tax follow by item > sku > category
			if($info['output_tax'] == -1) $output_tax = $info['mst_output_tax'];
			else $output_tax = $info['output_tax'];
			
			if($output_tax == -1){
				$info['gst_id'] = $cat_output_tax['id'];
				$info['gst_rate'] = $cat_output_tax['rate'];
				$info['gst_code'] = $cat_output_tax['code'];
			}else{
				$info['gst_id'] = $output_tax;
				$info['gst_rate'] = $output_tax_list[$output_tax]['rate'];
				$info['gst_code'] = $output_tax_list[$output_tax]['code'];
			}

			// get inclusive tax follow by item > sku > category
			if($info['inclusive_tax'] == "inherit") $inclusive_tax = $info['mst_inclusive_tax'];
			else $inclusive_tax = $info['inclusive_tax'];
			
			if($inclusive_tax == "inherit") $inclusive_tax = $cat_inclusive_tax;
			$info['inclusive_tax'] = $inclusive_tax;
			
			if($inclusive_tax == 'yes'){
				$prm = array();
				$prm['selling_price'] = $info['selling_price'];
				$prm['inclusive_tax'] = $inclusive_tax;
				$prm['gst_rate'] = $info['gst_rate'];
				$ret = calculate_gst_sp($prm);
				$info['selling_price_before_gst'] = $ret['gst_selling_price'];
			}else{
				$info['selling_price_before_gst'] = $info['selling_price'];
			}
		}

		return $info;
	}
	
	function import_csv() {
	    global $con,$smarty,$sessioninfo,$LANG;

	    $ret = array();

	    $rowid = time();
	    
		$f = fopen($_FILES['csv']['tmp_name'], "r");
		while ($line = fgetcsv($f)) {
			// find mcode/armscode matching
			$q1 = $con->sql_query("select * from sku_items where (sku_item_code = ".ms($line[0])." or mcode = ".ms($line[0])." or link_code = ".ms($line[0]).") limit 1");
			$si = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$row = $this->get_item_info($si['id']);
			$row['id'] = $rowid++;
			$row['type'] = trim($line[1]);
			$row['future_selling_price'] = $line[2];

			$gst = get_sku_gst('output_tax', $si['id']);
			$row['inclusive_tax'] = get_sku_gst('inclusive_tax', $si['id']);
			$row['gst_rate'] = $gst['rate'];
			$row['gst_id'] = $gst['id'];
			$row['gst_code'] = $gst['code'];
			
			$ret[] = $row;
		}	
		fclose($f);	
		return array('items'=>$ret);
	}

	function ajax_add_item(){
	    global $con,$smarty,$sessioninfo,$LANG,$config;

		$form = $_REQUEST;

		$grn_barcode = trim($form['grn_barcode']);
		$sku_item_pcs_arr = $used_id = array();
		if($grn_barcode && $form['is_barcode']){    // add item by using scan barcode
			$sku_info=get_grn_barcode_info($grn_barcode,true);
			if ($sku_info['sku_item_id']){
				// is inactive item
				$q1 = $con->sql_query("select active from sku_items where id = ".mi($sku_info['sku_item_id'])." limit 1");
				$tmp_si_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				if(!$tmp_si_info['active']){
					fail($LANG['PO_ITEM_IS_INACTIVE']);
				}
			
				$sku_item_id = $sku_info['sku_item_id'];
				$pcs = $sku_info['qty_pcs'];
			}else{
				print $sku_info['err'];
				return;
			}
			$sku_item_id_arr[] = $sku_item_id;
			$sku_item_pcs_arr[$sku_item_id] = $pcs;
		}else{
			if($form["add_all_price_type"] && $config["sku_multiple_selling_price"]){
				$temp_price_type = array_values($config["sku_multiple_selling_price"]);
				array_unshift($temp_price_type , "normal");
				foreach($temp_price_type as $item){
					foreach($form['sku_items_list'] as $tmp){
						$sku_item_id_arr[] = $tmp;
					}
				}
			}else{
				$sku_item_id_arr = $form['sku_items_list'];
			}
		}
		
		if(count($sku_item_id_arr) > 0){
			foreach($sku_item_id_arr as $row=>$sid){
				$r = $this->get_item_info($sid);
				if($sku_item_pcs_arr[$sid] > 0) $r['min_qty'] = $sku_item_pcs_arr[$sid];
				
				if(isset($form["temp_id"])){
					if(!isset($id_count)){
						$id_count = count($form["temp_id"])+1;
					}
					
					if(isset($form["temp_id"][$new_id])){
						$new_id=$form["temp_id"][$new_id]+$id_count;
					}
					else{
						$new_id = time()+$id_count;
					}
				}else{
					$new_id = time()+$id_count;
				}
				
				if(isset($used_id[$new_id])){
					$new_id=$used_id[$new_id]+1;
					$id_count++;
				}
				
				if($form["add_all_price_type"]){
					$r["type"] = $temp_price_type[$row];
				}
				$r['id'] = $new_id;
				$used_id[$new_id] = $new_id;
				$smarty->assign("item", $r);
			
				$temp['html'] = $smarty->fetch("masterfile_sku_items.future_price.open.item.tpl");
				$temp['ok'] = 1;
				$ret[] = $temp;
			}

			print json_encode($ret);
		}else print "No item to add.";
	}
	
	function ajax_add_qprice_items(){
		global $con,$smarty,$sessioninfo;
		$form = $_REQUEST;
		
		if(!$form['is_sub_item']){
			$q1 = $con->sql_query("select * from sku_items_qprice where sku_item_id = ".mi($form['sku_item_id'])." and branch_id = ".mi($sessioninfo['branch_id']));
			
			while($r = $con->sql_fetchassoc($q1)){
				$key = $r['sku_item_id'];
				//print $r['sku_item_id'];
				$sku_item_id_arr[] = $key;
				$sku_item_proposed_price[] = $r['price'];
				$sku_item_min_qty[] = $r['min_qty'];
				
			}
			$con->sql_freeresult($q1);
		}else{
			sleep(1);
			$sku_item_id_arr[] = $form['sku_item_id'];
		}
		
		// try to lookup existing qprice items available from backend
		if(!$form['is_sub_item']){
			$q1 = $con->sql_query("select sifpi.*
						 from sku_items_future_price_items sifpi
						 where sifpi.fp_id=".mi($form['id'])." and sifpi.branch_id=".mi($form['branch_id'])." 
						 and sifpi.type = 'qprice' and sifpi.sku_item_id = ".mi($form['sku_item_id'])." and sifpi.id != ".mi($form['mid'])."
						 order by sifpi.id");
			
			while($r = $con->sql_fetchassoc($q1)){
				$r1 = $this->get_item_info($form['sku_item_id']);
				
				$r = array_merge($r, $r1);
				$form['qprice_items'][$form['sku_item_id']][] = $r;
			}
		}

		if($sku_item_id_arr){
			foreach($sku_item_id_arr as $row=>$sid){
				$r = $this->get_item_info($sid);
				$new_id = time()+$id_count;
				if(isset($used_id[$new_id])){
					$new_id=$used_id[$new_id]+1;
					$id_count++;
				}

				$r['min_qty'] = $sku_item_min_qty[$row];
				$r['future_selling_price'] = $sku_item_proposed_price[$row];
				$r['type'] = "qprice";
				
				$r['id'] = $new_id;
				$used_id[$new_id] = $new_id;
				$form['qprice_items'][$form['sku_item_id']][] = $r;
			}
		}


		$form['item']['id'] = $form['mid'];
		$smarty->assign("item", $form['item']);
		$smarty->assign("form", $form);
		$temp['ok'] = 1;
		$temp['html'] = $smarty->fetch("masterfile_sku_items.future_price.open.item.qprice.tpl");
		$ret[] = $temp;
		print json_encode($ret);
	}
	
	function get_last_cost($form){
		global $con;

		$ret['cost'] = 0;
		
		$q1 = $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
							   from grn_items
						 	   left join uom on uom_id = uom.id
							   left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
							   left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
							   where grn_items.branch_id = ".mi($form['branch_id'])." and grn.approved and sku_item_id=".mi($form['sku_item_id'])." 
							   having cost > 0
							   order by grr.rcv_date desc limit 1");
		$c = $con->sql_fetchassoc($q1);
		//print "using GRN $c[0]";
		if ($c){
			$ret['cost'] = $c['cost'];
			$ret['source'] = 'GRN';
		}
		
		if ($ret['cost']==0){
			$q1 = $con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
								   from po_items 
								   left join po on po_id = po.id and po.branch_id = po.branch_id 
								   where po.active and po.approved and po_items.branch_id = ".mi($form['branch_id'])." and sku_item_id=".mi($form['sku_item_id'])." 
								   having cost > 0
								   order by po.po_date desc limit 1");
			$c = $con->sql_fetchassoc($q1);
			//print "using PO $c[0]";
			if ($c){
				$ret['cost'] = $c['cost'];
				$ret['source'] = 'PO';
			}
		}
		
		if ($ret['cost']==0){
			$q1 = $con->sql_query("select cost_price from sku_items where id=".mi($form['sku_item_id']));
			$c = $con->sql_fetchassoc($q1);
			//print "using MASTER $c[0]";
	
			if($c){
				$ret['cost'] = $c['cost_price'];
				$ret['source'] = 'MASTER SKU';
			}
		}
		
		return $ret;
	}
	
	function open_csv($err=false){
		global $con, $smarty, $sessioninfo;

		if(!$err){
			$form['branch_id'] = $sessioninfo['branch_id'];
			$form['date'] = date("Y-m-d");

			$smarty->assign("form", $form);
		}

		$smarty->display("masterfile_sku_items.future_price.open.csv.tpl");
	}
	
	function save_csv(){
		global $con, $smarty, $sessioninfo;
		set_time_limit(0);

		$form = $_REQUEST;
		$err = validate($form);
		
		// do not check for items
		unset($err['dtl']);
		
		if($err){
			$smarty->assign("form", $form);
			$smarty->assign("errm", $err);
			$this->open_csv($err=true);
			exit;
		}
		
		
		$is_under_gst = check_gst_status();
		$branch_id = $sessioninfo['branch_id'];
		//$items = $this->import_csv();
		
		$effective_branches = array();
		if($form['effective_branches']){
			foreach($form['effective_branches'] as $bid=>$val){
				if($form['date_by_branch']){
					$effective_branches[$bid]['hour'] = $form['branch_hour'][$bid];
					$effective_branches[$bid]['minute'] = $form['branch_minute'][$bid];
					$effective_branches[$bid]['date'] = $form['branch_date'][$bid];
				}else $effective_branches[$bid] = $bid;
			}
		}

		// insert master info as approved
		$ins = array();
		$ins['branch_id'] = $branch_id;
		$ins['date_by_branch'] = $form['date_by_branch'];
		$ins['date'] = $form['date'];
		$ins['hour'] = $form['hour'];
		$ins['minute'] = $form['minute'];
		if($effective_branches) $ins['effective_branches'] = serialize($effective_branches);
		$ins['user_id'] = $sessioninfo['id'];
		$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
		$ins['active'] = $ins['approved'] = $ins['status'] = 1;
		
		$con->sql_query("select max(id) from sku_items_future_price where branch_id = ".mi($branch_id));
		$ins['id'] = $con->sql_fetchfield(0);
		$ins['id'] += 1;
		$con->sql_freeresult();
		
		$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
		$fp_id=$fp_id=$con->sql_nextid();
		
		$f = fopen($_FILES['csv']['tmp_name'], "r");
		while($line = fgetcsv($f, 1000, ",")){
			// find mcode/armscode matching
			$q1 = $con->sql_query("select * from sku_items where (sku_item_code = ".ms($line[0])." or mcode = ".ms($line[0])." or link_code = ".ms($line[0]).") limit 1");
			$si = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if(!$si) continue;
			$info = $this->get_item_info($si['id']);
			
			$ins = array();
			$ins['branch_id'] = $branch_id;
			$ins['fp_id'] = $fp_id;
			$ins['sku_item_id'] = $info['sku_item_id'];
			$ins['cost'] = $info['cost'];
			$ins['selling_price'] = $info['selling_price'];
			$ins['type'] = $line[1];
			$ins['trade_discount_code'] = $info['trade_discount_code'];
			//$ins['min_qty'] = $r['min_qty'];
			$ins['future_selling_price'] = mf($line[2]);

			if($is_under_gst){
				$gst = get_sku_gst('output_tax', $si['id']);
				$ins['gst_rate'] = $gst['rate'];
				$ins['gst_id'] = $gst['id'];
				$ins['gst_code'] = $gst['code'];
			}
			
			$con->sql_query("select max(id) from sku_items_future_price_items where branch_id = ".mi($branch_id));
			$ins['id'] = $con->sql_fetchfield(0);
			$ins['id'] += 1;
			$con->sql_freeresult();
			
			$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
		}
		fclose($f);	
		
		header("Location: /masterfile_sku_items.future_price.php?act=confirm&id=".mi($fp_id)."&la=".mi($sessioninfo['id']));
	}
	
	function show_full_items(){
		global $con, $smarty, $LANG, $sessioninfo;
		set_time_limit(0);
		
		$form = $_REQUEST;
		// load header
		$hinfo = load_header($form['id'], $form['branch_id']);
		
		if(!$hinfo){
			print "<b>".$LANG['MST_FP_NOT_FOUND']."</b>";
			exit;
		}
		
		print "<table width=\"100%\" style=\"border:1px solid #999; padding:5px; background-color:#fe9\" cellspacing=\"1\" cellpadding=\"4\">
				<thead>
					<tr height=\"32\" bgcolor=\"#ffffff\" class=\"small\">
						<th rowspan=\"2\">#</th>
						<th rowspan=\"2\">ARMS</th>
						<th rowspan=\"2\">Artno</th>
						<th rowspan=\"2\">Mcode</th>
						<th rowspan=\"2\">Description</th>
						<th rowspan=\"2\">Stock<br />Balance</th>";
		
		if($sessioninfo['privilege']['SHOW_COST']){
			print "<th rowspan=\"2\">Cost</th>";
		}
		
		print "<th rowspan=\"2\">Price</th>
				  <th rowspan=\"2\">Price Type</th>
				  <th rowspan=\"2\">Discount<br />Code</th>
				  <th rowspan=\"2\">Min Qty<br />(QPrice)</th>
				  <th rowspan=\"2\">Proposed<br />Price</th>";

		if($sessioninfo['privilege']['SHOW_COST']){
			print "<th colspan=\"2\">Current</th>
				   <th colspan=\"2\">New</th>
				   <th colspan=\"2\">Variance</th>";
		}
		print "</tr>";

		if($sessioninfo['privilege']['SHOW_COST']){
			print "<tr height=\"32\" bgcolor=\"#ffffff\" class=\"small\">
					  <th>GP</th>
					  <th>GP(%)</th>
					  <th>GP</th>
					  <th>GP(%)</th>
					  <th>GP</th>
					  <th>GP(%)</th>
				  </tr>";
		}
		print "</thead>";
		
		$smarty->assign("readonly", 1);
		$smarty->assign("is_show_full_items", 1);
		$smarty->assign("form", $hinfo);
		
		$q1 = $con->sql_query("select sifpi.*, sku.sku_type, si.sku_item_code, si.artno, si.mcode, si.description, si.doc_allow_decimal,si.sku_id, 
						 puom.fraction as packing_uom_fraction, sic.qty as stock_bal, sku.category_id, sku.category_id, sku.mst_output_tax, 
						 si.output_tax, sku.mst_inclusive_tax, si.inclusive_tax
						 from sku_items_future_price_items sifpi
						 left join sku_items si on si.id = sifpi.sku_item_id
						 left join sku on sku.id = si.sku_id
						 left join uom puom on puom.id=si.packing_uom_id
						 left join sku_items_cost sic on sic.sku_item_id = sifpi.sku_item_id and sic.branch_id = sifpi.branch_id
						 where sifpi.fp_id=".mi($form['id'])." and sifpi.branch_id=".mi($form['branch_id'])."
						 order by sifpi.id");

		$row_no = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$r['extra_info'] = unserialize($r['extra_info']);
			$row_no++;
			$smarty->assign("row_no", $row_no);
			$smarty->assign("item", $r);
			
			if($r['type'] == "qprice") $smarty->display("masterfile_sku_items.future_price.open.item.qprice.tpl");
			else $smarty->display("masterfile_sku_items.future_price.open.item.tpl");
		}
		$con->sql_freeresult($q1);
		print "</table>";
	}
}

$BATCH_PRICE_CHANGE_MODULE = new BATCH_PRICE_CHANGE_MODULE('Batch Selling Price Change');
?>

