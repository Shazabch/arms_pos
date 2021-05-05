<?php
/*
7/3/2017 3:07 PM Andy
- Add skuManager function getSKUSalesTrend()

10/2/2017 4:40 PM Justin
- Enhanced the sales trend function can accept multiple branch and SKU items.
- Enhanced the sales trend script to calculate base on date not year & month.

3/28/2018 11:04 AM Andy
- Add skuManager function reupdateSKUWeight()

8/23/2018 11:20 AM Andy
- Added skuManager function getSKUItemPrice(), updateDebtorPrice() and getSKUItemDebtorPrice().

11/20/2018 2:56 PM Andy
- Added skuManager function searchSKUbyCode().

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

2/25/2019 10:06 AM Andy
- Added skuManager function getSKULatestStockAndCost()

3/21/2019 11:27 AM Andy
- Added skuManager function getSKUItemPhotos().

6/3/2019 2:42 PM Andy
- Enhanced skuManager function getSKUGroupList() to can accept params.

6/28/2019 4:53 PM Andy
- Added skuManager function getSKUItemPromoPhotos().

2/25/2020 11:44 AM Andy
- Added skuManager function "getMaxSKUID".

2/20/2020 10:50 AM Andy
- Added skuManager function "saveSKUTag", "getSKUTagByID" and "updateSKUTagActive".

3/3/2020 11:50 AM William
- Enhanced function "getSKUItemPhotos" to view pos image.
*/
class skuManager{
	// common variable
	
	// private
	private $existsStockBalanceTableList;

	function __construct(){
		
	}

	// function to get sku group list
	// return array skuGroupList
	function getSKUGroupList($params = array()){
		global $con, $smarty, $sessioninfo, $config, $appCore;

        if($sessioninfo['level']>=900 || $params['get_all']){
			$sku_group_filter = '';
		}else{
			if($config['sku_group_searching_need_filter_user']){
				if($sessioninfo['level']>=500){
		            $sku_group_filter = "where sg.branch_id=".mi($sessioninfo['branch_id']);
				}else{
		            $sku_group_filter = "where sg.user_id=".mi($sessioninfo['id']);
				}
			}
		}
		

        $q1 = $con->sql_query("select sg.*,user.u
		from sku_group sg
		left join user on sg.user_id=user.id
		$sku_group_filter order by sg.description");
		$skuGroupList = array();
		while($r = $con->sql_fetchassoc($q1)){
			$key = $r['branch_id'].'_'.$r['sku_group_id'];
			$skuGroupList[$key] = $r;
		}
		$con->sql_freeresult($q1);

		return $skuGroupList;
	}

	// function to get sku type list
	// return array skuTypeList
	function getSKUTypeList($params = array()){
		global $con;

		$filter = array();
		if(isset($params['active']))	$filter[] = "st.active=".mi($params['active']);

		if($filter)	$str_filter = 'where '.join(' and ', $filter);

		$skuTypeList = array();
		$q1 = $con->sql_query("select st.* from sku_type st $str_filter order by code");
	
		while($r = $con->sql_fetchassoc($q1)){
			$skuTypeList[$r['code']] = $r;
		}
		$con->sql_freeresult($q1);

		return $skuTypeList;
	}

	// function to auto assign smarty variable based on submitted sku
	// return null
	function assignGroupItemForSKUAutocomplteMultipleAdd2($sku_code_list){
		global $con, $smarty, $appCore;
		if(!$appCore->haveSmarty)	return;
		if(!is_array($sku_code_list) || !$sku_code_list)	return;
		
		$str_sku_code_list = join(",", array_map("ms", $_REQUEST['sku_code_list']));
		
	    // select sku item id list
     	$con->sql_query("select * from sku_items where sku_item_code in (".$str_sku_code_list.")");
		while($r = $con->sql_fetchassoc()){
			$group_item[] = $r;
		}
		$con->sql_freeresult();

		$smarty->assign('group_item', $group_item);
	}

	// function to get sku_items stock balance in back dated
	// return array stockBalanceRow
	function getStockBalance($sku_item_id, $branch_id, $date){
		global $con;
		
		if(!is_array($this->existsStockBalanceTableList))	$this->existsStockBalanceTableList = array();

		$sku_item_id = mi($sku_item_id);
		$branch_id = mi($branch_id);
		$year = mi(date("Y", strtotime($date)));
		
		// validate
		if($sku_item_id <=0 || $branch_id <=0 || $year<2000)	return;
		
		// make the table name
		$stk_bal_table = "stock_balance_b".$branch_id."_".$year;

		// check if the table already validate before
		if(!isset($this->existsStockBalanceTableList[$stk_bal_table])){
			// validate whether the table is exists
			$q_sb = $con->sql_query_false("explain $stk_bal_table");
			if($q_sb){
				// mark the table is exists
				$this->existsStockBalanceTableList[$stk_bal_table] = 1;
			}
			$con->sql_freeresult($q_sb);
		}
		
		if(isset($this->existsStockBalanceTableList[$stk_bal_table])){
			// select the stock if the table exists
			$q1 = $con->sql_query("select qty from $stk_bal_table where ".ms($date)." between from_date and to_date and sku_item_id=".mi($sku_item_id));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			return $r;
		}
	}
	
	// function to get sku_items latest stock balance and cost
	// return array
	public function getSKULatestStockAndCost($sku_item_id, $branch_id){
		global $con;
		
		$sku_item_id = mi($sku_item_id);
		$branch_id = mi($branch_id);
		
		if($sku_item_id <=0 || $branch_id <=0 )	return;
		
		$con->sql_query("select qty, grn_cost from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $data;
	}

	// function to get sku items
	// return array si
	function getSKUItemsInfo($sid){
		global $con;

		$con->sql_query("select si.*
			from sku_items si
			where si.id=".mi($sid));
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();

		return $si;
	}
	
	// function to get sku items sales trend in 1 year
	// return array data
	function getSKUSalesTrend($bid, $sid, $prms=array()){
		global $con, $sessioninfo;

		if(!$bid || !$sid)	return;
	
		if($prms['qty_sum_method']) $qty_sum_method = $prms['qty_sum_method'];
		else $qty_sum_method = "sum(sc.qty)";
		
		$filters = array();
		$filters[] = "sc.date > ".ms($dt);
		
		if(is_array($bid)) $bid_list = $bid;
		else $bid_list[] = $bid;
		
		if(is_array($sid)) $filters[] = "sc.sku_item_id in (".join(",", $sid).")";
		else $filters[] = "sc.sku_item_id = ".mi($sid);
		
		$data=array();
		$dt = date('Y-m-d', strtotime('-1 year'));
		$curr_times = strtotime(date("Y-m-d"));
		foreach($bid_list as $bid){
			$sql="select sc.date, sc.year, sc.month, $qty_sum_method as qty 
				  from sku_items_sales_cache_b".mi($bid)." sc 
				  left join sku_items si on si.id=sc.sku_item_id
				  left join uom on uom.id=si.packing_uom_id
				  where ".join(" and ", $filters)."
				  group by sc.date";

			$q1 = $con->sql_query($sql);
			
			while($r=$con->sql_fetchassoc($q1)){
				$sales_times = strtotime($r['date']);
				$times_diff = $curr_times - $sales_times;
				foreach(array(1,3,6,12) as $mm){
					$st_times = $mm * 30 * strtotime("+1 day", 0); // convert sales trend month into seconds
					if ($times_diff <= $st_times){
						$data['sales_trend']['qty'][$mm]+=$r['qty'];
					}
				}
			}
		}
		
		if ($data)
		{
			ksort($data['sales_trend']['qty']);
		}

		return $data['sales_trend'];
	}
	
	// function to reupdate all sku childs weight_kg based on parent
	// return null
	function reupdateSKUWeight($skuID, $params = array()){
		global $con, $config;
		
		$skuID = mi($skuID);
		if(!$skuID)	return;
		
		// sometime for multi server mode they need to use hqcon
		$hqcon = (isset($params['hqcon']) && $params['hqcon']) ? $params['hqcon'] : $con;
		
		// Get Parent
		$hqcon->sql_query("select weight_kg from sku_items where is_parent=1 and sku_id=$skuID");
		$parent = $hqcon->sql_fetchassoc();
		$hqcon->sql_freeresult();
		
		$weight_kg = round($parent['weight_kg'], $config['global_weight_decimal_points']);
		
		// Get Children
		$q1 = $hqcon->sql_query("select si.id, uom.fraction as packing_uom_fraction
		from sku_items si
		left join uom on uom.id=si.packing_uom_id
		where is_parent=0 and sku_id=$skuID
		order by si.id");
		while($r = $hqcon->sql_fetchassoc($q1)){
			$upd = array();
			$upd['weight_kg'] = round($r['packing_uom_fraction']*$weight_kg, $config['global_weight_decimal_points']);
			$hqcon->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id=$r[id]");
		}
		$hqcon->sql_freeresult($q1);
	}
	
	// function to get latest sku item selling price by branch
	// return double
	function getSKUItemPrice($bid, $sid){
		global $con;
		
		$bid = mi($bid);
		$sid = mi($sid);
		
		if(!$bid || !$sid)	return 0;
		
		$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price
			from sku_items si
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			where si.id=$sid");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return mf($data['selling_price']);
	}
	
	// function to update debtor price
	// return array
	function updateDebtorPrice($params = array()){
		global $con;
		
		$user_id = mi($params['user_id']);
		$debtor_id = mi($params['debtor_id']);
		$bid = mi($params['bid']);
		$sid = mi($params['sid']);
		$price = round(mf($params['price']), 2);
		if($price < 0)	$price = 0;
		$bcode = get_branch_code($bid);
		
		// sku_items_debtor_price
		$sidp = array();
		$sidp['branch_id'] = $bid;
		$sidp['sku_item_id'] = $sid;
		$sidp['debtor_id'] = $debtor_id;
		$sidp['price'] = $price;
		$sidp['user_id'] = $user_id;
		$sidp['last_update'] = 'CURRENT_TIMESTAMP';
		
		// Select current row
		$con->sql_query("select * from sku_items_debtor_price where branch_id=$bid and sku_item_id=$sid and debtor_id=$debtor_id");
		$curr_data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$curr_data && !$price){	// Currently no data and price is zero, no need update
			return;
		}
		
		if($curr_data['price'] == $price){	// same price, no need update
			return;
		}
		
		// Update Record
		$con->sql_query("replace into sku_items_debtor_price ".mysql_insert_by_field($sidp));
		
		// Select Again the record
		$con->sql_query("select * from sku_items_debtor_price where branch_id=$bid and sku_item_id=$sid and debtor_id=$debtor_id");
		$sidph = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sidph['added'] = $sidph['last_update'];
		unset($sidph['last_update']);
		
		// Update Record History
		$con->sql_query("replace into sku_items_debtor_price_history ".mysql_insert_by_field($sidph));
		
		log_br($user_id, 'DEBTOR_PRICE', $sid, "Branch Debtor Price Updated: $bcode, Debtor ID#$debtor_id, SKU ITEM ID#$sid, Price: $price");
		
		$ret = array();
		$ret['ok'] = 1;
		
		return $ret;
	}
	
	// function to get debtor price
	// return double
	function getSKUItemDebtorPrice($bid, $sid, $debtor_id, $params = array()){
		global $con;
		
		$bid = mi($bid);
		$sid = mi($sid);
		$debtor_id = mi($debtor_id);
		if(isset($params['get_normal_price']))	$get_normal_price = mi($params['get_normal_price']);
		
		if(!$bid || !$sid || !$debtor_id)	return 0;
		
		$con->sql_query("select price from sku_items_debtor_price where branch_id=$bid and sku_item_id=$sid and debtor_id=$debtor_id");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$price = mf($data['price']);
		
		// Debtor Price not set, user normal selling price
		if($get_normal_price && $price<=0)	$price = $this->getSKUItemPrice($bid, $sid);
		
		return $price;
	}
	
	// function to search sku by code
	// return array $sid_list
	public function searchSKUbyCode($item_code, $params = array()){
		global $con;
		
		$filter = array();
		$limit = 0;
		if(isset($params['active']))	$filter[] = "si.active=".mi($params['active']);
		if(isset($params['limit']))	$limit = mi($params['limit']);
		
		// Check 13 Digits
		$filter_12_digits = array();
		$str_code2 = '';
		if(strlen($item_code)==13){
			$code2 = substr($item_code,0,12);
			
			$filter_12_digits[] = "si.mcode = ".ms($code2);
			$filter_12_digits[] = "si.link_code = ".ms($code2);
			$filter_12_digits[] = "si.sku_item_code = ".ms($code2);
			$filter_12_digits[] = "si.artno = ".ms($code2);
			
			$str_code2 = join(' or ', $filter_12_digits);
		}
		
		$sort_rank = ",if(si.sku_item_code=".ms($item_code).",0, if(si.mcode=".ms($item_code).",1,if(si.link_code=".ms($item_code).",2,3))) as sort_rank";
		
		$filter_or = array();
		$filter_or[] = "si.mcode = ".ms($item_code);
		$filter_or[] = "si.link_code = ".ms($item_code);
		$filter_or[] = "si.sku_item_code = ".ms($item_code);
		$filter_or[] = "si.artno = ".ms($item_code);
		
		if($str_code2)	$filter_or[] = $str_code2;
		
		$filter[] = "(".join(' or ', $filter_or).")";
		
		$str_filter = "where ".join(' and ', $filter);
		$str_limit = '';
		if($limit > 0)	$str_limit = "limit $limit";
		
		$str_order_by = "order by sort_rank, si.sku_item_code";
		
		$sid_list = array();
		$sql = "select si.id $sort_rank
			from sku_items si
			$str_filter
			$str_order_by
			$str_limit";
		//print "$sql<br>";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$sid_list[] = mi($r['id']);
		}
		$con->sql_freeresult($q1);
		
		return $sid_list;
	}
	
	// function get sku photo url list
	// return array $photo_info
	public function getSKUItemPhotos($sid, $params = array()){
		global $config, $con, $sessioninfo;
		
		$sid = mi($sid);
		if($sid<=0)	return;
		
		$photo_info = array();
		$photo_list = $apply_photo_list = $sku_promo_photo = array();
		
		$con->sql_query("select si.sku_apply_items_id, si.artno, si.got_pos_photo from sku_items si where si.id=$sid");
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		//get sku promotion photo
		if($si['got_pos_photo']){
			$sku_promo_photo = $this->getSKUItemPromoPhotos($sid);
			if($sku_promo_photo)  $photo_list = array_merge($photo_list, $sku_promo_photo);
		}
			
		// Get SKU Apply Item ID
		$sku_apply_items_id = mi($si['sku_apply_items_id']);
		//print "sku_apply_items_id = $sku_apply_items_id";
		// Get Apply Photo
		if($sku_apply_items_id > 0){
			$con->sql_query("select sai.id as sku_apply_items_id, sai.photo_count
				from sku_apply_items sai
				where sai.id=$sku_apply_items_id");
			$sai = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if ($sai['photo_count'] > 0) {
				$apply_photo_list = get_sku_apply_item_photos($sku_apply_items_id);
			}
		}
		//print_r($apply_photo_list);
		if($apply_photo_list)	$photo_list = array_merge($photo_list, $apply_photo_list);
		
		// Get sku_item photo
		$actual_photos_list = get_sku_item_photos($sid, $si, true);	// skip if photo at remote server
		if($actual_photos_list){
			// got actual photo
			$photo_list = array_merge($photo_list, $actual_photos_list);
		}else{
			// if actual_photo is at other server, just get the url first, later load when needed
			if($si['url_to_get_photo'])	$photo_info['url_to_get_photo'] = $si['url_to_get_photo'];
		}
		
		$photo_info['photo_list'] = $photo_list;
		return $photo_info;		
	}
	
	public function getSKUItemPromoPhotos($sid){
		global $config, $con, $sessioninfo;
		
		$sid = mi($sid);
		if($sid<=0)	return;
		
		$group_num = ceil($sid/10000);
		$abs_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$group_num."/".$sid."/";
		if(!file_exists($abs_path))	return false;
		
		$photo_list = array();
		foreach(array_merge(glob("$abs_path/*.jpg"),glob("$abs_path/*.JPG")) as $f){
			$f = str_replace("$_SERVER[DOCUMENT_ROOT]/", "", $f);
			$photo_list[] = $f;
		}
		
		return $photo_list;
	}
	
	public function getMaxSKUID(){
		global $con, $hqcon;
		
		if(!$hqcon){
			$hqcon = connect_hq();
		}
		
		// Check SKU
		$hqcon->sql_query("select max(id) as max_id from sku for update");
		$sku = $hqcon->sql_fetchassoc();
		$hqcon->sql_freeresult();
		
		$max_id = mi($sku['max_id']);
		
		// Check SKU Obsolete
		$hqcon->sql_query("select max(id) as max_id from sku_obsolete for update");
		$sku_obsolete = $hqcon->sql_fetchassoc();
		$hqcon->sql_freeresult();
		
		$max_id2 = mi($sku_obsolete['max_id']);
		
		if($max_id2 > $max_id)	$max_id = $max_id2;
		
		return $max_id;
	}
	
	/*public function saveSKUTag($sku_tag_id, $save_data, $params = array()){
		global $con, $appCore, $smarty, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		$user_id = mi($params['user_id']);
		if(!$user_id)	$user_id = $sessioninfo['id'];
		if(!$user_id)	$user_id = 1;
		
		// Validate Data
		if(!is_array($save_data) || !$save_data)	return array("error"=>$LANG['SKU_TAG_NO_DATA_TO_SAVE'], "error_code"=>"SKU_TAG_NO_DATA_TO_SAVE");
		$code = strtoupper(trim($save_data['code']));
		$description = trim($save_data['description']);
		
		if(!$code)	return array("error"=>$LANG['SKU_TAG_NO_CODE'], "error_code"=>"SKU_TAG_NO_CODE");
		if(!$description)	return array("error"=>$LANG['SKU_TAG_NO_DESC'], "error_code"=>"SKU_TAG_NO_DESC");
		
		// Check Duplicate Code
		$con->sql_query("select id from sku_tag where code=".ms($code)." and id<>$sku_tag_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		if($tmp)	return array("error"=>$LANG['SKU_TAG_CODE_DUPLICATE'], "error_code"=>"SKU_TAG_CODE_DUPLICATE");
		
		$upd = array();
		$upd['code'] = $code;
		$upd['description'] = $description;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		if($sku_tag_id > 0){
			// Update
			$con->sql_query("update sku_tag set ".mysql_update_by_field($upd)." where id=$sku_tag_id");
		}else{
			// Insert
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into sku_tag ".mysql_insert_by_field($upd));
			$sku_tag_id = $con->sql_nextid();
			$is_new = true;
		}
		
		log_br($user_id, 'SKU_TAG', $sku_tag_id, ($is_new ? "Add":"Update")." SKU Tag, Code: $code");
		
		return array("ok" => 1);
	}
	
	public function getSKUTagByID($sku_tag_id, $params = array()){
		global $con, $appCore, $smarty, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		if($sku_tag_id <= 0)	return false;
		
		$filter = array();
		$filter[] = "st.id=$sku_tag_id";
		$str_filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select st.*
			from sku_tag st
			$str_filter");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$data)	return false;
		
		return $data;
	}
	
	public function updateSKUTagActive($sku_tag_id, $is_active, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		$is_active = mi($is_active);
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		if(!$sku_tag_id)	return array('error' => $LANG['SKU_TAG_ID_INVALID'], "error_code"=>"SKU_TAG_ID_INVALID");
		
		// Get SKU Tag
		$sku_tag = $this->getSKUTagByID($sku_tag_id);
		if(!$sku_tag)		return array('error' => sprintf($LANG['SKU_TAG_ID_DATA_NOT_FOUND'], $sku_tag_id), "error_code"=>"SKU_TAG_ID_DATA_NOT_FOUND");
		
		$upd = array();
		$upd['active'] = $is_active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("update sku_tag set ".mysql_update_by_field($upd)." where id=$sku_tag_id");
		
		$str_log = ($is_active ? 'Activated':'Deactivated')." SKU Tag, Code: ".$sku_tag['code'];
		
		log_br($user_id, 'SKU_TAG', $sku_tag_id, $str_log);
		
		$ret = array();
		$ret['ok'] = 1;
		return $ret;
	}
	
	public function getSKUTagItemList($sku_tag_id, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		if($sku_tag_id <= 0)	return false;
		
		$start = mi($params['start']);
		$limit = mi($params['limit']);
		
		if(isset($params['order_by'])){
			$str_order_by = trim($params['order_by']);
		}else{
			$str_order_by = "order by sti.added desc";
		}
		
		$ret = array();
		$ret['ok'] = 1;
		// Count Total
		if($params['count_total']){
			$con->sql_query("select count(*) as c
				from sku_tag_items sti
				where sti.sku_tag_id=$sku_tag_id");
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$ret['total_rows'] = mi($tmp['c']);
			
			if($start >= $ret['total_rows']){
				$start = 0;
			}
		}
		
		$str_limit = '';
		if($limit > 0){
			$str_limit = "limit $start, $limit";
		}
		
		$ret['item_list'] = array();
		$q1 = $con->sql_query("select sti.*, si.sku_item_code, si.mcode, si.artno, si.link_code, si.description, uom.code as packing_uom_code
			from sku_tag_items sti
			join sku_items si on si.id=sti.sku_item_id
			left join uom on uom.id=si.packing_uom_id
			where sti.sku_tag_id=$sku_tag_id
			$str_order_by
			$str_limit");
		while($r = $con->sql_fetchassoc($q1)){
			$ret['item_list'][$r['sku_item_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $ret;
	}
	
	public function addSKUTagItem($sku_tag_id, $sid, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		if($sku_tag_id <= 0)	return array('error' => $LANG['SKU_TAG_ID_INVALID'], "error_code"=>"SKU_TAG_ID_INVALID");
		
		$sid = mi($sid);
		if($sid <= 0)	return array('error' => sprintf($LANG['INVALID_DATA'], "SKU ITEM ID", $sid), "error_code"=>"INVALID_DATA");
		
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		// Get SKU Tag
		$sku_tag = $this->getSKUTagByID($sku_tag_id);
		if(!$sku_tag)		return array('error' => sprintf($LANG['SKU_TAG_ID_DATA_NOT_FOUND'], $sku_tag_id), "error_code"=>"SKU_TAG_ID_DATA_NOT_FOUND");
		
		// Check Duplicate
		$con->sql_query("select * from sku_tag_items where sku_tag_id=$sku_tag_id and sku_item_id=$sid");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Already exists
		if($tmp){
			return array('error' => sprintf($LANG['SKU_TAG_ITEM_ALRDY_EXISTS'], $sid), "error_code"=>"SKU_TAG_ITEM_ALRDY_EXISTS");
		}
		
		// Insert
		$upd = array();
		$upd['sku_tag_id'] = $sku_tag_id;
		$upd['sku_item_id'] = $sid;
		if(isset($params['po_reorder_qty_min']))	$upd['po_reorder_qty_min'] = mi($params['po_reorder_qty_min']);
		if(isset($params['po_reorder_qty_max']))	$upd['po_reorder_qty_max'] = mi($params['po_reorder_qty_max']);
		if(isset($params['po_reorder_moq']))	$upd['po_reorder_moq'] = mi($params['po_reorder_moq']);
		if(isset($params['po_qty']))	$upd['po_qty'] = mi($params['po_qty']);
		if(isset($params['do_qty']))	$upd['do_qty'] = mi($params['do_qty']);
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into sku_tag_items ".mysql_insert_by_field($upd));
		
		log_br($user_id, 'SKU_TAG', $sku_tag_id, "Add SKU Item, Tag [".$sku_tag['code']."], SKU ITEM ID#$sid");
		
		return array('ok'=>1);
	}
	
	public function updateSKUTagItem($sku_tag_id, $sid, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		if($sku_tag_id <= 0)	return array('error' => $LANG['SKU_TAG_ID_INVALID'], "error_code"=>"SKU_TAG_ID_INVALID");
		
		$sid = mi($sid);
		if($sid <= 0)	return array('error' => sprintf($LANG['INVALID_DATA'], "SKU ITEM ID", $sid), "error_code"=>"INVALID_DATA");
		
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		// Get SKU Tag
		$sku_tag = $this->getSKUTagByID($sku_tag_id);
		if(!$sku_tag)		return array('error' => sprintf($LANG['SKU_TAG_ID_DATA_NOT_FOUND'], $sku_tag_id), "error_code"=>"SKU_TAG_ID_DATA_NOT_FOUND");
		
		// Select Item
		$con->sql_query("select * from sku_tag_items where sku_tag_id=$sku_tag_id and sku_item_id=$sid");
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Item Not Found
		if(!$item){
			return array('error' => sprintf($LANG['SKU_TAG_ITEM_NOT_FOUND'], "ID: $sid"), "error_code"=>"SKU_TAG_ITEM_NOT_FOUND");
		}
		
		// Insert
		$upd = array();
		if(isset($params['po_reorder_qty_min']))	$upd['po_reorder_qty_min'] = mi($params['po_reorder_qty_min']);
		if(isset($params['po_reorder_qty_max']))	$upd['po_reorder_qty_max'] = mi($params['po_reorder_qty_max']);
		if(isset($params['po_reorder_moq']))	$upd['po_reorder_moq'] = mi($params['po_reorder_moq']);
		if(isset($params['po_qty']))	$upd['po_qty'] = mi($params['po_qty']);
		if(isset($params['do_qty']))	$upd['do_qty'] = mi($params['do_qty']);
		
		if(!$upd){
			return array('error' => $LANG['SKU_TAG_ITEM_NOTHING_TO_UPDATE'], "error_code"=>"SKU_TAG_ITEM_NOTHING_TO_UPDATE");
		}
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update sku_tag_items set ".mysql_update_by_field($upd)." where sku_tag_id=$sku_tag_id and sku_item_id=$sid");
		
		log_br($user_id, 'SKU_TAG', $sku_tag_id, "Update SKU Item, Tag [".$sku_tag['code']."], SKU ITEM ID#$sid");
		
		return array('ok'=>1);
	}
	
	public function deleteSKUTagItem($sku_tag_id, $sid, $params = array()){
		global $con, $LANG, $sessioninfo;
		
		$sku_tag_id = mi($sku_tag_id);
		if($sku_tag_id <= 0)	return array('error' => $LANG['SKU_TAG_ID_INVALID'], "error_code"=>"SKU_TAG_ID_INVALID");
		
		$sid = mi($sid);
		if($sid <= 0)	return array('error' => sprintf($LANG['INVALID_DATA'], "SKU ITEM ID", $sid), "error_code"=>"INVALID_DATA");
		
		$user_id = mi($params['user_id']);
		if(!$user_id){
			$user_id = $sessioninfo ? $sessioninfo['id'] : 1;
		}
		
		// Get SKU Tag
		$sku_tag = $this->getSKUTagByID($sku_tag_id);
		if(!$sku_tag)		return array('error' => sprintf($LANG['SKU_TAG_ID_DATA_NOT_FOUND'], $sku_tag_id), "error_code"=>"SKU_TAG_ID_DATA_NOT_FOUND");
		
		// Select Item
		$con->sql_query("select * from sku_tag_items where sku_tag_id=$sku_tag_id and sku_item_id=$sid");
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// Item Not Found
		if(!$item){
			return array('error' => sprintf($LANG['SKU_TAG_ITEM_NOT_FOUND'], "ID: $sid"), "error_code"=>"SKU_TAG_ITEM_NOT_FOUND");
		}
	}*/
}
?>
