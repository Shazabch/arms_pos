<?php
/*
7/16/2012 4:39:34 PM Justin
- Fixed bug that system wrongly save the SKU Type + Price Type while add by SKU item.

5/30/2013 11:37 AM Justin
- Enhanced to check new privilege "MST_SALES_AGENT".

2/17/2017 4:10 PM Justin
- Bug fixed on commission activate/deactivate feature.

4/21/2017 9:36 AM Khausalya
- Enhanced changes from RM to use config setting. 

6/12/2017 16:52 Qiu Ying
- Bug fixed on qty values are listed with currency symbols

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SALES_AGENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SALES_AGENT', BRANCH_CODE), "/index.php");
include("masterfile_sa_commission.include.php");

class MASTERFILE_SA_COMMISSION extends Module{
	function __construct($title){
		global $con, $smarty;

		$con->sql_query("select * from sku_type where active=1 order by code");
		$master_sku_type = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('master_sku_type', $master_sku_type);
		
		$con->sql_query("select * from trade_discount_type order by code");
		while($r = $con->sql_fetchassoc()){
			$master_price_type[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('master_price_type', $master_price_type);
		
		$commission_type = array("Flat", "Sales Range", "Qty Range");

		$smarty->assign('commission_type', $commission_type);

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		$sac_list = array();
		
		$_REQUEST['t'] = 1;
		$this->ajax_load_commission_list();
		
	    $this->display("masterfile_sa_commission.tpl");
	}

	function open_commission(){
		global $con, $smarty, $LANG, $sessioninfo;

		if(!$_REQUEST['id']){
			$sac_id=time();
			$form['id']=$sac_id;
			$form['branch_id']=$sessioninfo['branch_id'];
		}else{
			if(!$this->is_error){
				$q1 = $con->sql_query("select * from sa_commission where id = ".mi($_REQUEST['id'])." and branch_id = ".mi($_REQUEST['branch_id']));
				$form = $con->sql_fetchrow($q1);
				$con->sql_freeresult($q1);
				$items = load_commission_items($form['id'], $form['branch_id'], $date_list);
				$smarty->assign("date_list", $date_list);
				$smarty->assign("items", $items);
			}else{
				$form = $this->form;
			}
		}

		if(BRANCH_CODE == "HQ"){
			$con->sql_query("select id, code from branch where active=1 and id = ".mi($form['branch_id'])." order by sequence,code");
			$form['branch_code'] = $con->sql_fetchfield(1);
			$con->sql_freeresult();
		}
		
		$smarty->assign("load_temp_table", 1);
		$smarty->assign("form", $form);
		$this->display("masterfile_sa_commission.open.tpl");
	}

	function ajax_load_commission_list(){
		global $con, $smarty, $config, $sessioninfo;

		if (!$t) $t = intval($_REQUEST['t']);

		$where = array();

		switch($t)
		{
			case 0:
				$str = trim($_REQUEST['search']);
				if(!$str)	die('Cannot search empty string');
				
				$where[] = "(sac.id = ".mi($str)." or sac.id like ".ms("%".replace_special_char($str)."%").")";
				break;

			case 1: // show active Commission
				$where[] = "sac.active = 1";
				break;

			case 2: // show inactive
				$where[] = "sac.active = 0";
				break;
		}

		if(BRANCH_CODE != "HQ" || !$config['single_server_mode']) $where[] = "sac.branch_id = ".mi($sessioninfo['branch_id']);
		
		$where = "where ".join(" and ", $where);

		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}
		$limit =  "limit $start, $sz";
	
		$con->sql_query("select count(*)
						 from sa_commission sac
						 $where");

		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
		    if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"commission_list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start) $pg .= " selected";
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}

		$q1 = $con->sql_query("select sac.*, u.u as username
							   from sa_commission sac
							   left join user u on u.id = sac.user_id
							   $where
							   order by sac.last_update desc
							   $limit");
		//$sa = $con->sql_fetchrowset($q1);
		
		//$current_time = strtotime(date("Y-m-d H:i:s"));
		while($r1 = $con->sql_fetchrow($q1)){
			$sac_list[] = $r1;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("t", $_REQUEST['t']);
		$smarty->assign("sac_list", $sac_list);
	    if($_REQUEST['ajax'] == 1) $this->display("masterfile_sa_commission.list.tpl");
	}

	function ajax_toggle_commission_status(){
		global $con, $smarty, $sessioninfo;
		
		$con->sql_query("update sa_commission set active=".mi($_REQUEST['status'])." where id = ".mi($_REQUEST['id'])." and branch_id = ".mi($_REQUEST['branch_id']));

		if($con->sql_affectedrows() > 0){
			print "OK";
		}else die("Failed update commission status.");
	}

	function ajax_add_commission(){
		global $smarty;
		
		$form = $_REQUEST;
		
		if($form['commission_method']){
			foreach($form['commission_method'] as $saci_id=>$commission_type){
				if($prv_date_group_id > 0 && $prv_date_group_id != $form['date_group_id'][$saci_id]) break;
				if($form['is_deleted'][$saci_id]) continue;
				$item = array();
				if(!$id) $id = time();
				else $id = $id+1;
				$item['id'] = $id;
				$date_from =date("Y-m-d",strtotime("+1 day",strtotime($form['selected_date_from'][$form['date_group_id'][$saci_id]])));
				$item['date_from'] = $date_from;
				$item['sku_item_id'] = $form['sku_item_id'][$saci_id];
				$item['brand_id'] = $form['brand_id'][$saci_id];
				$item['category_id'] = $form['category_id'][$saci_id];
				$item['sku_type'] = $form['sku_type'][$saci_id];
				$item['price_type'] = $form['price_type'][$saci_id];
				$item['vendor_id'] = $form['vendor_id'][$saci_id];
				$item['commission_method'] = $form['commission_method'][$saci_id];
				$item['is_deleted'] = $form['is_deleted'][$saci_id];
				$item['active'] = $form['active'][$saci_id];

				if(!$date_list[$item['date_from']]){ // add into date list if found not exists
					$date_list[$item['date_from']] = $form['date_item_id'];
				}
				
				$cm_value = array();
				if($commission_type != "Flat"){
					if(count($form['sac_item_cm_range_value'][$saci_id]) > 0){
						$row_count = 0;
						foreach($form['sac_item_cm_range_value'][$saci_id] as $row_id=>$cm_range_value){
							$range_from = $form['sac_item_cm_range_from'][$saci_id][$row_id];
							$range_to = $form['sac_item_cm_range_to'][$saci_id][$row_id];
							$item['commission_value'][$row_count]['range_from'] = $range_from;
							$item['commission_value'][$row_count]['range_to'] = $range_to;
							$item['commission_value'][$row_count]['value'] = $cm_range_value;
							$row_count++;
						}
					}
				}else{
					//$cm_range_value = $form['cm_value'][$saci_id];
					$item['commission_value'] = $form['cm_value'][$saci_id];
				}

				$conditions = array();
				if($form['sku_item_id'][$saci_id] > 0){ // commission item by sku item
					$conditions['sku_item_id'] = $form['sku_item_id'][$saci_id];
				}else{ // commission item by combination (category + brand)
					$conditions['category_id'] = $form['category_id'][$saci_id];
					$conditions['brand_id'] = $form['brand_id'][$saci_id];
					$conditions['sku_type'] = $form['sku_type'][$saci_id];
					$conditions['price_type'] = $form['cpt'][$saci_id];
					$conditions['vendor_id'] = $form['vendor_id'][$saci_id];
				}

				// load commission item's info
				$tmp_item = array();
				$tmp_item = get_commission_condition_item_info($conditions);
				$item = array_merge($item, $tmp_item);
				
				$items[$form['date_item_id']][] = $item;
				$prv_date_group_id = $form['date_group_id'][$saci_id];
			}
		}
		
		if($date_list && $items){
			$smarty->assign("date_list", $date_list);
			$smarty->assign("items", $items);
			$smarty->assign("header_is_hidden", 1);
			$ret['ok'] = 1;
			$ret['html'] = $smarty->fetch("masterfile_sa_commission.open.item.tpl");
		}else{
			$ret['err_msg'] = "Nothing to add!";
		}
		print json_encode($ret);
	}
	
	function ajax_add_commission_item(){
		global $con, $smarty, $sessioninfo;

		$form = $_REQUEST;
		
		$item = $item_info = $conditions = array();
		$sac_item_id = strtotime(date("Y-m-d H:i:s"));
		if($form['condition_type'] == "si"){ // add item by sku item
			$conditions['sku_item_id'] = $form['sku_item_id'];
			unset($form['cst']);
			unset($form['cpt']);
		}else{ // add item by category + brand
			$conditions['brand_id'] = $form['brand_id'];
			$conditions['category_id'] = $form['category_id'];
			$conditions['vendor_id'] = $form['vendor_id'];
		}

		$item_info = get_commission_condition_item_info($conditions);
		$item_info['id'] = $sac_item_id;
		$item_info['sku_type'] = $form['cst'];
		$item_info['price_type'] = $form['cpt'];
		$item_info['active'] = 1;
		$item[$form['date_item_id']][] = $item_info;

		if(is_array($item)){
			//$smarty->assign("sac_id", $form['id']);
			$smarty->assign("date_item_id", $form['date_item_id']);
			$smarty->assign("items", $item);
			$smarty->assign("is_hidden", 1);
			$ret['ok'] = 1;
			//$ret['sac_id'] = $sac_item['sac_id'];
			$ret['sac_item_id'] = $sac_item_id;
			$ret['html'] = $smarty->fetch("masterfile_sa_commission.open.item_row.tpl");
		}else{
			$ret['err_msg'] = sprintf($LANG['SAC_ITEM_NOT_FOUND'], $sac_item_id);
		}
		print json_encode($ret);
	}
	
	function save_commission(){
		global $con, $smarty, $sessioninfo;

		$form=$_REQUEST;
		$err = $this->commission_validate($items);
		$is_deleted_items = array();
		
		if($err){
			$smarty->assign("err", $err);
			$this->is_error = true;
			$this->open_commission();
			//print_r($err);
			exit;
		}

		// if it is not a new commission
		if(is_new_id($form['id'])){ // do insert
			$mst_ins = array();
			$mst_ins['branch_id'] = $form['branch_id'];
			$mst_ins['title'] = $form['title'];
			$mst_ins['user_id'] = $sessioninfo['id'];
			$mst_ins['added'] = "CURRENT_TIMESTAMP";
			$mst_ins['last_update'] = "CURRENT_TIMESTAMP";

			$con->sql_query("insert into sa_commission ".mysql_insert_by_field($mst_ins));
			$form['id'] = $con->sql_nextid();
			$status_msg = "Added New";
		}else{ // do update
			$mst_upd = array();
			$mst_upd['title'] = $form['title'];
			$mst_upd['last_update'] = "CURRENT_TIMESTAMP";

			$con->sql_query("update sa_commission set ".mysql_update_by_field($mst_upd)." where id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
			$status_msg = "Updated";
		}
		log_br($sessioninfo['id'], 'SA_COMMISSION', $form['id'], $status_msg." Commission: ID#".$form['id']." - ".get_branch_code($form['branch_id']));

		foreach($form['commission_method'] as $saci_id=>$commission_type){
			if($form['is_deleted'][$saci_id]){ // is not newly added items and it's being deleted
				if(is_new_id($saci_id)) continue;
				$is_deleted_items[] = $saci_id;
				continue;
			}
			$cm_value = array();
			if($commission_type == "Flat"){
				$cm_range_value = $form['cm_value'][$saci_id];
			}else{
				if(count($form['sac_item_cm_range_value'][$saci_id]) > 0){
					$row_count = 0;
					foreach($form['sac_item_cm_range_value'][$saci_id] as $row_id=>$cm_range_value){
						$cm_value[$row_count]['range_from'] = $form['sac_item_cm_range_from'][$saci_id][$row_id];
						$cm_value[$row_count]['range_to'] = $form['sac_item_cm_range_to'][$saci_id][$row_id];
						$cm_value[$row_count]['value'] = $cm_range_value;
						$row_count++;
					}
					if($cm_value) $cm_range_value = serialize($cm_value);
				}
			}

			$conditions = array();
			if($form['sku_item_id'][$saci_id] > 0){ // commission item by sku item
				$conditions['sku_item_id'] = $form['sku_item_id'][$saci_id];
			}else{ // commission item by combination (category + brand)
				if($form['category_id'][$saci_id]) $conditions['category_id'] = $form['category_id'][$saci_id];
				if($form['brand_id'][$saci_id] !== '') $conditions['brand_id'] = $form['brand_id'][$saci_id];
				if($form['sku_type'][$saci_id]) $conditions['sku_type'] = $form['sku_type'][$saci_id];
				if($form['price_type'][$saci_id]) $conditions['price_type'] = join(",", $form['price_type'][$saci_id]);
				if($form['vendor_id'][$saci_id]) $conditions['vendor_id'] = $form['vendor_id'][$saci_id];
			}

			$dtl = array();
			$dtl['date_from'] = $form['selected_date_from'][$form['date_group_id'][$saci_id]];
			if($conditions) $dtl['conditions'] = serialize($conditions);
			$dtl['commission_method'] = $commission_type;
			$dtl['commission_value'] = $cm_range_value;

			if(is_new_id($saci_id)){ // insert commission item
				$dtl['branch_id'] = $form['branch_id'];
				$dtl['sac_id'] = $form['id'];
				$dtl['added'] = "CURRENT_TIMESTAMP";

				$con->sql_query("insert into sa_commission_items ".mysql_insert_by_field($dtl));
				log_br($sessioninfo['id'], 'SA_COMMISSION', $form['id'], "Added S/A Commission Item: MST_ID#".mi($form['id'])." ITEM_ID#".mi($saci_id)." - ".get_branch_code($form['branch_id']));
			}else{ // update commission item
				$dtl['active'] = $form['active'][$saci_id];
				$con->sql_query("update sa_commission_items set ".mysql_update_by_field($dtl)." where sac_id = ".mi($form['id'])." and id = ".mi($saci_id)." and branch_id = ".mi($form['branch_id']));
				if($con->sql_affectedrows()>0){
					log_br($sessioninfo['id'], 'SA_COMMISSION', $form['id'], "Updated S/A Commission Item: MST_ID#".mi($form['id'])." ITEM_ID#".mi($saci_id)." - ".get_branch_code($form['branch_id']));
				}
			}
		}
		
		if(count($is_deleted_items) > 0){
			$con->sql_query("delete from sa_commission_items where id in (".join(",", $is_deleted_items).") and sac_id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
		}

		// update all commission's date to
		$q1 = $con->sql_query("select * from sa_commission_items where sac_id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id'])." order by id");
		
		while($r1 = $con->sql_fetchrow($q1)){
			$q2 = $con->sql_query("select date_sub(date_from, interval 1 day) as date_to from sa_commission_items where date_from > ".ms($r1['date_from'])." and id != ".mi($r1['id'])." and sac_id = ".mi($r1['sac_id'])." and branch_id = ".mi($r1['branch_id'])." order by date_from desc limit 1");
			
			if($con->sql_numrows($q2) > 0){
				$r2 = $con->sql_fetchrow($q2);
				$con->sql_freeresult($q2);
				$dtl_upd = array();
				$dtl_upd['date_to'] = $r2['date_to'];
				$con->sql_query("update sa_commission_items set ".mysql_update_by_field($dtl_upd)." where id=".mi($r1['id'])." and sac_id = ".mi($r1['sac_id'])." and branch_id = ".mi($r1['branch_id']));
			}else{
				$con->sql_query("update sa_commission_items set date_to = '' where id=".mi($r1['id'])." and sac_id = ".mi($r1['sac_id'])." and branch_id = ".mi($r1['branch_id']));
			}
		}
		$con->sql_freeresult($q1);
		$smarty->assign("status", "save");
		$smarty->assign("id", $form['id']);

		$this->_default();
	}
	
	function commission_validate(&$items){
		global $con, $LANG, $smarty, $config;
		$form=$_REQUEST;
		$err = $items = array();
		$date_count = 0;
		
		if(!trim($form['title'])) $err['mst'][] = $LANG['SAC_INVALID_TITLE'];
		if(count($form['commission_method']) == 0)  $err['mst'][] = $LANG['SAC_NO_ITEM'];
		else{
			foreach($form['commission_method'] as $saci_id=>$commission_type){
				if($form['is_deleted'][$saci_id]) continue;
				$item = array();
				$item['id'] = $saci_id;
				$item['date_from'] = $form['selected_date_from'][$form['date_group_id'][$saci_id]];
				$item['sku_item_id'] = $form['sku_item_id'][$saci_id];
				$item['brand_id'] = $form['brand_id'][$saci_id];
				$item['category_id'] = $form['category_id'][$saci_id];
				$item['sku_type'] = $form['sku_type'][$saci_id];
				$item['price_type'] = $form['price_type'][$saci_id];
				$item['vendor_id'] = $form['vendor_id'][$saci_id];
				$item['commission_method'] = $form['commission_method'][$saci_id];
				$item['is_deleted'] = $form['is_deleted'][$saci_id];
				$item['active'] = $form['active'][$saci_id];

				if(!$date_list[$item['date_from']]){ // add into date list if found not exists
					$date_count++;
					$date_list[$item['date_from']] = $date_count;
				}
				
				$cm_value = array();
				if($commission_type != "Flat"){
					if(count($form['sac_item_cm_range_value'][$saci_id]) > 0){
						$row_count = 0;

						foreach($form['sac_item_cm_range_value'][$saci_id] as $row_id=>$cm_range_value){
							$range_from = $form['sac_item_cm_range_from'][$saci_id][$row_id];
							$ori_range_to = $range_to = $form['sac_item_cm_range_to'][$saci_id][$row_id];
							if ($ori_range_to == 0) $ori_range_to = 99999999999999;
							$item['commission_value'][$row_count]['range_from'] = $range_from;
							$item['commission_value'][$row_count]['range_to'] = $range_to;
							$item['commission_value'][$row_count]['value'] = $cm_range_value;

							$is_error = false;
							// capture the lowest and highest of commission + check error
							foreach($form['sac_item_cm_range_value'][$saci_id] as $tmp_row_id=>$tmp_cm_range_value){
								if($row_id == $tmp_row_id) continue; // skip own row

								$tmp_range_from = $form['sac_item_cm_range_from'][$saci_id][$tmp_row_id];
								if($tmp_range_from == 0) $tmp_range_from = 0;
								$tmp_range_to = $form['sac_item_cm_range_to'][$saci_id][$tmp_row_id];
								if($tmp_range_to == 0) $tmp_range_to = 99999999999999;
								if(($tmp_range_from <= $range_from && $tmp_range_to >= $range_from) || ($tmp_range_from <= $ori_range_to && $tmp_range_to >= $ori_range_to)){
									$is_error = true;
									break;
								}
							}
							
							if($is_error){
								$currency_symbol = "";
								if($commission_type == "Sales Range"){
									$currency_symbol = $config["arms_currency"]["symbol"];
								}
								
								if($range_from>0 && $range_to>0) $range_err_msg = "between " . $currency_symbol.$range_from." - " . $currency_symbol .$range_to;
								elseif($range_from>0 && $range_to==0) $range_err_msg = "start from " . $currency_symbol.$range_from;
								elseif($range_from==0 && $range_to>0) $range_err_msg = "at most " . $currency_symbol.$range_to;

								$err['dtl'][$date_list[$item['date_from']]][] = "Having duplicated Commission Range for Row [".mi($row_id+1)."] ".$range_err_msg;
							}
							
							$row_count++;
							unset($lowest_range_from[$saci_id]);
							unset($highest_range_to[$saci_id]);
						}
					}
				}else{
					//$cm_range_value = $form['cm_value'][$saci_id];
					$item['commission_value'] = $form['cm_value'][$saci_id];
				}

				$conditions = array();
				if($form['sku_item_id'][$saci_id] > 0){ // commission item by sku item
					$conditions['sku_item_id'] = $form['sku_item_id'][$saci_id];
				}else{ // commission item by combination (category + brand)
					$conditions['category_id'] = $form['category_id'][$saci_id];
					$conditions['brand_id'] = $form['brand_id'][$saci_id];
					$conditions['sku_type'] = $form['sku_type'][$saci_id];
					$conditions['price_type'] = $form['cpt'][$saci_id];
					$conditions['vendor_id'] = $form['vendor_id'][$saci_id];
				}

				// load commission item's info
				$tmp_item = array();
				$tmp_item = get_commission_condition_item_info($conditions);
				$item = array_merge($item, $tmp_item);

				// check whether having repeated conditions
				if(count($items[$date_list[$item['date_from']]]) > 0){
					$condition_is_duplicated = false;
					$msg = "";
					foreach($items[$date_list[$item['date_from']]] as $tmp_row=>$tmp_arr_item){
						if($item['sku_item_id'] > 0){ // current item condition is by sku item
							if($tmp_arr_item['sku_item_id'] == $item['sku_item_id']){
								$msg = $item['description'];
								$condition_is_duplicated = true;
							}
						}else{ // current item condition is by category + brand
							if($tmp_arr_item['category_id'] == $item['category_id'] && $tmp_arr_item['brand_id'] == $item['brand_id']){
								if($item['cat_desc']) $msg = "Category: ".$item['cat_desc'];
								if($item['brand_desc']){
									if($msg) $msg .= ", ";
									$msg .= "Brand: ".$item['brand_desc'];
								}
								$condition_is_duplicated = true;
							}
						}
						if($condition_is_duplicated) break;
					}
								
					if($condition_is_duplicated) $err['dtl'][$date_list[$item['date_from']]][] = "Condition Duplicated for ".$msg;
				}
				
				$items[$date_list[$item['date_from']]][] = $item;
			}
		}

		$smarty->assign("date_list", $date_list);
		$smarty->assign("items", $items);
		$this->form = $form;

		return $err;
	}
}

$MASTERFILE_SA_COMMISSION=new MASTERFILE_SA_COMMISSION("Commission Master File");

?>
