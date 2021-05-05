<?php
/*
3/26/2019 5:27 PM Justin
- Enhanced to have new function check_commission_conditions and check_range_commission.

10/14/2019 5:23 PM Andy
- enhanced salesAgentManager.getSA() to can get sales agent by code.
- Added salesAgentManager.getSAList()

10/22/2019 10:45 AM Justin
- Enhanced to have new function "posSAHandler".

10/24/2019 6:07 PM Justin
- Bug fixed on range commission checking couldn't compatible with new POS version (v202).

11/19/2019 1:19 PM Justin
- Enhanced to use new method to calculate commission by sales / qty range.

11/22/2019 5:25 PM Justin
- Enhanced to return total ratio.

12/2/2019 11:00 AM Justin
- Enhanced to have new function "getKPIItems" and "getMonthList".

1/2/2020 3:16 PM Justin
- Bug fixed on the function "getMonthList" requires customer's server to install certain package in order to make it work.
*/

class salesAgentManager{
	// common variable
	var $cacheSAList = array();
	
	// private
	

	function __construct(){
		
	}
	
	public function getSAList($params = array()){
		global $con;
		
		$filter = array();
		if(isset($params['active']))	$filter[] = "active=".mi($params['active']);
		
		$str_filter = '';
		if($filter)	$str_filter = "where ".join(' and ', $filter);
		
		$sa_list = array();
		$q1 = $con->sql_query("select *
			from sa
			$str_filter
			order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$sa_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		return $sa_list;
	}
	
	// function to get Sales Agent by Sales Agent ID
	// return array $sa
	public function getSA($saID, $saCode=''){
		global $con;
		
		$saID = mi($saID);
		$saCode = trim($saCode);
		if(!$saID && !$saCode){
			return;	// no id and no code
		}			
		
		$filter = array();
		
		// return cache data
		if($saID){
			if(isset($this->cacheSAList[$saID]))	return $this->cacheSAList[$saID];
			$filter[] = "id=$saID";
		}else{
			$filter[] = "code=".ms($saCode);
		}

		$str_filter = "where ".join(' and ', $filter);
		$q1 = $con->sql_query("select * from sa $str_filter");
		$sa = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($sa){
			// Store cache by Sales Agent ID
			$this->cacheSAList[$sa['id']] = $sa;
		}else{
			if($saID)	$this->cacheSAList[$saID] = false;			
		}		
		
		return $sa;
	}
	
	function check_commission_conditions($conditions, $arr){
		if(!$conditions || !$arr) return;
		
		if($conditions['sku_item_id'] > 0){ // set commission by sku item
			if($conditions['sku_item_id'] && $conditions['sku_item_id'] != $arr['sku_item_id']) return;
		}else{ // set commission by category/brand/sku type/sku type/price type/vendor
			if($conditions['category_id'] && $conditions['category_id'] != $arr['category_id']){
				
				// get category info using category ID from commission settings
				$condition_cat_info = array();
				$condition_cat_info = get_category_info($conditions['category_id']);
				
				// get SKU item category info
				$sales_cat_info = array();
				$sales_cat_info = get_category_info($arr['category_id']);
				
				if($sales_cat_info['p'.$condition_cat_info['level']] != $conditions['category_id']){
					return;
				}
			}
			if(isset($conditions['brand_id']) && $conditions['brand_id'] != $arr['brand_id']) return;
			if($conditions['sku_type'] && $conditions['sku_type'] != $arr['sku_type']) return;
			if($conditions['price_type'] && !preg_match("/".$arr['trade_discount_code']."/", $conditions['price_type'])) return;
			if($conditions['vendor_id'] && $conditions['vendor_id'] != $arr['vendor_id']) return;
		}
		
		return true;
	}
	
	function check_range_commission($sa_id, $bid, $prms=array()){
		global $con, $config;
	
		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}
	
		$conditions = $do_filters = $po_filters = $ext_filter = $havings = array();
		$conditions = $prms['conditions'];
		if($conditions['sku_item_id'] > 0){ // set commission by sku item
			$ext_filter[] = "si.id = ".mi($conditions['sku_item_id']);
		}else{ // set commission by category/brand/sku type/sku type/price type/vendor
			//if($conditions['category_id']) $ext_filter[] = "sku.category_id = ".mi($conditions['category_id']);
			if(isset($conditions['brand_id'])) $ext_filter[] = "sku.brand_id = ".mi($conditions['brand_id']);
			if($conditions['sku_type']) $ext_filter[] = "sku.sku_type = ".ms($conditions['sku_type']);
			if($conditions['price_type'] && !preg_match("/".$r1['trade_discount_code']."/", $conditions['price_type'])){
				$price_type_list = str_replace(",", "','", str_replace(" ", "", $conditions['price_type']));
				$havings[] = "having trade_discount_code in (".ms($price_type_list).")";
			}
			if($conditions['vendor_id']) $ext_filter[] = "sku.vendor_id =".mi($conditions['sku_type']);
		}

		// construct new date from and to base on the commission starting date
		if($prms['sac_date']){
			$sac_date_from = date("Y-m", strtotime($prms['sac_date']))."-01";
			$sac_date_to = date("Y-m-d", strtotime("-1 day", strtotime("+1 month", strtotime($sac_date_from))));
		}
		
		if($prms['filters']) $filter = " and ".join(" and ", $prms['filters']);
		if($ext_filter) $filter .= " and ".join(" and ", $ext_filter);
		if($havings) $having = join(", ", $havings);

		if(($prms['sales_type'] && $prms['sales_type'] != 'pos') || !$prms['sales_type']){
			if($prms['sales_type']) $do_filters[] = "do.do_type =".ms($prms['sales_type']);
			$do_filters[] = "do.do_date between ".ms($sac_date_from)." and ".ms($sac_date_to);
			if($do_filters) $do_filter = " and ".join(" and ", $do_filters);
			$sql[] = "select 'DO' as type, do_type, do.mst_sa, di.dtl_sa, do.do_date as date, di.inv_line_gross_amt2 as cost_price, di.cost,
					  do.do_markup, do.markup_type, 0 as pi_amount, uom.fraction, di.do_id as mst_id, di.do_id as receipt_ref_no,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, ((di.ctn*uom.fraction)+di.pcs) as qty, 
					  di.item_discount, sku.category_id, c.level as cat_level, 0 as sales_cache_qty
					  from `do`
					  left join `do_items` di on di.do_id = do.id and di.branch_id = do.branch_id
					  left join `uom` on uom.id = di.uom_id
					  left join `sku_items` si on si.id = di.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  where do.branch_id = ".mi($bid)." and do.active=1 and do.approved=1 and do.checkout=1 and ((do.mst_sa != '' and do.mst_sa is not null and do.mst_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%') or (di.dtl_sa != '' and di.dtl_sa is not null and di.dtl_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%')) and do.do_type != 'transfer'
					  $filter $do_filter";
		}

		if($prms['sales_type'] == 'pos' || !$prms['sales_type']){
			$po_filters[] = "pos.date between ".ms($sac_date_from)." and ".ms($sac_date_to);
			if($po_filters) $po_filter = " and ".join(" and ", $po_filters);
			$sql[] = "select 'POS' as type, '' as do_type, pos.receipt_sa as mst_sa, pi.item_sa as dtl_sa, pos.date,0 as cost_price, sisc.cost, 
					  0 as do_markup, '' as markup_type, (pi.price-pi.discount-pi.discount2-pi.tax_amount) as pi_amount, 1 as fraction, pos.id as mst_id, pos.receipt_ref_no,
					  if(sip.trade_discount_code is not null and sip.trade_discount_code != '', sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code, pi.qty, 0 as item_discount, sku.category_id, c.level as cat_level, sisc.qty as sales_cache_qty
					  from `pos`
					  left join `pos_items` pi on pi.pos_id = pos.id and pi.branch_id = pos.branch_id and pi.date = pos.date and pi.counter_id = pos.counter_id
					  left join `sku_items` si on si.id = pi.sku_item_id
					  left join `sku` on sku.id = si.sku_id
					  left join `category` c on c.id = sku.category_id
					  left join `sku_items_price` sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($bid)."
					  left join `sku_items_sales_cache_b".mi($bid)."` sisc on sisc.sku_item_id = si.id and sisc.date = pos.date
					  join pos_finalized pf on pf.branch_id=pos.branch_id and pf.date=pos.date and pf.finalized=1
					  where pos.branch_id = ".mi($bid)." and pos.cancel_status=0 and ((pos.receipt_sa != '' and pos.receipt_sa is not null and pos.receipt_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%') or (pi.item_sa != '' and pi.item_sa is not null and pi.item_sa like '%s:".strlen(mi($sa_id)).":\"".mi($sa_id)."\";%'))
					  $filter $po_filter";
		}

		$all_sql = join(" UNION ALL ", $sql)." $having order by date";
		$q1 = $con_multi->sql_query($all_sql);

		$ttl_amt = $ttl_cost = $ttl_qty = $ttl_trans_count = 0;
		while($r1 = $con_multi->sql_fetchassoc($q1)){
			$sa_list = $org_sa_list = array();
			$row_amt_ctn = $row_amt_pcs = $row_amt = 0;
			
			if($conditions['category_id'] && $conditions['category_id'] != $r1['category_id']){
				// get category info using category ID from commission settings	
				$condition_cat_info = array();
				$condition_cat_info = get_category_info($conditions['category_id']);
				
				// get SKU item category info
				$sales_cat_info = array();
				$sales_cat_info = get_category_info($r1['category_id']);
				
				if($sales_cat_info['p'.$condition_cat_info['level']] != $conditions['category_id']){
					continue;
				}
			}
			if($r1['mst_sa']) $sa_list = unserialize($r1['mst_sa']);
			else $sa_list = unserialize($r1['dtl_sa']);
			
			if(count($sa_list) == 0) continue;
			$org_sa_list = $sa_list;
			
			if($r1['type'] == "DO"){
				$curr_trans_key = $r1['mst_id'];
				$row_amt = round($r1['cost_price'],2);
				$row_cost = $r1['cost'] * $r1['qty'] / $r1['fraction'];
			}else{
				$curr_trans_key = $r1['receipt_ref_no'];
				$row_amt = round($r1['pi_amount'],2);
				$row_cost = $r1['cost'] / $r1['sales_cache_qty'] * $r1['qty'];
			}
			
			// check if the receipt contains ratio
			$tmp_prms = array();
			$tmp_prms['sa_list'] = $sa_list;
			$tmp_prms['sales_amount'] = $row_amt;
			$sa_ratio_result = array();
			$sa_ratio_result = $this->posSAHandler($tmp_prms);
			unset($tmp_prms);
			
			// do not proceed if no such sales agent for the transaction
			if($sa_ratio_result['id_list_existed']){
				if(!$sa_list[$sa_id]) continue;
			}elseif(!in_array($sa_id, $sa_list)) continue;
			
			// check if the sales agent got set with ratio then use the ratio to calculate the sales amount for each sales agent
			if($r1['mst_sa']){
				if($sa_ratio_result['use_ratio']){
					$row_amt = $sa_ratio_result['sa_ratio_sales_list'][$sa_id]['sales_amt'];
				}elseif(count($org_sa_list) > 1 && $config['sa_calc_average_sales']){ // otherwise check if turn on config to calculate average sales for all sales agent
					$row_cost = round($row_cost / count($org_sa_list), $config['global_cost_decimal_points']);
					$row_amt = round($row_amt / count($org_sa_list), 2);
				}
			}
			
			$row_qty = $r1['qty'];
			
			$ttl_amt += $row_amt;
			$ttl_cost += $row_cost;
			$ttl_qty += $row_qty;
			if($curr_trans_key != $last_trans_key){
				$ttl_trans_count++;
			}
			
			unset($sa_ratio_result, $org_sa_list, $row_amt, $row_qty);
			
			$last_trans_key = $curr_trans_key;
		}
		$con_multi->sql_freeresult($q1);

		if($prms['commission_method'] == "Sales Range"){ // is set by sales
			$amt = $ttl_amt;
		}else{ // is set by qty
			$amt = $ttl_qty;
		}

		$ret = array();
		if(count($prms['commission_value_list']) > 0){
			foreach($prms['commission_value_list'] as $r=>$range_list){
				// skip when it is less/more than between range from and to
				if(($range_list['range_from'] && $range_list['range_from'] > $amt) || ($range_list['range_to'] && $range_list['range_to'] < $amt)) continue;
				//return $range_list['value'];
				
				// skip when it is less/more than between range from and to
				if((!$range_list['range_from'] || $range_list['range_from'] <= $amt) && (!$range_list['range_no'] || $range_list['range_to'] >= $amt)){ // it is fulfiled commission
					if($range_list['value'] > $ret['commission_value']){ // check and place the highest commission
						$ret['commission_value'] = $range_list['value'];
					}
				}
			}
		}
		
		if($ret['commission_value']){ //if found having commission, send result as passed
			$ret['result'] = "passed";
			$ret['ttl_sales_amt'] = $ttl_amt;
			$ret['ttl_cost'] = $ttl_cost;
			$ret['ttl_sales_qty'] = $ttl_qty;
			$ret['ttl_trans_count'] = $ttl_trans_count;
		}else{ // otherwise the result will be failed
			$ret['result'] = "failed";
		}
		
		return $ret;
	}
	
	function posSAHandler($prms=array()){
		global $config;
		
		if(!$prms['sa_list'] || !$prms['sales_amount']) return;
		
		$ret = array();
		$ttl_ratio = 0;
		
		// sum up the total ratio first
		foreach($prms['sa_list'] as $sa_id=>$sa_info){
			if(is_array($sa_info)){
				if(isset($sa_info['id']) && array_key_exists("id", $sa_info)) $ret['id_list_existed'] = true;
			
				if(isset($sa_info['ratio']) && $sa_info['ratio'] > 0){
					$ret['use_ratio'] = true;
					$ttl_ratio += $sa_info['ratio'];
				}
			}
		}
		
		// if found got capture ratio for sales agent, do below
		if($ret['use_ratio']){
			// calculate the actual sales base on ratio set for sales agent
			foreach($prms['sa_list'] as $sa_id=>$sa_info){
				if(!$sa_info['ratio']) $ret['sa_ratio_sales_list'][$sa_id]['sales_amt'] = 0; // set sales amount as zero as if found the ratio is zero
				else{
					// calculation method: ratio calculation method: sales / total ratio * individual ratio
					$ret['sa_ratio_sales_list'][$sa_id]['sales_amt'] += round($prms['sales_amount'] /  $ttl_ratio * $sa_info['ratio'], 2);
				}
			}
		}
		$ret['ttl_ratio'] = $ttl_ratio;
		unset($ttl_ratio);
		
		return $ret;
	}
	
	function getKPIItems($prms=array()){
		global $con;
		
		// must have sales agent ID
		if(!$prms['sa_id']) return;
		
		$sa_info = $this->getSA($prms['sa_id']);
		
		// must have position
		if(!$sa_info['position_id']) return;
		
		$kpi_items_list = array();
		$q1 = $con->sql_query("select * from sa_kpi_items where position_id = ".mi($sa_info['position_id'])." order by id");
		while($r = $con->sql_fetchassoc($q1)){
			if(!$r['scores']) continue; // skip the KPI items which doesn't have scores
			$kpi_items_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		return $kpi_items_list;
	}
	
	function getMonthList($prms=array()){		
		// set to use current year if doesn't found from param
		if(!$prms['year']) $prms['year'] = date("Y");
		
		// set to use current month if doesn't found from param
		if(!$prms['month']){
			if($prms['year'] != date("Y")) $prms['month'] = 12; // set to get all months if the year is older
			else $prms['month'] = date("m");
		}
		
		$first_mth_date = $prms['year']."-01-01"; // always starts with first month of the year
		$end_mth_date = date("Y-m-t", strtotime($prms['year']."-".$prms['month']."-01")); // always ends with last month of the year
		$start_time = strtotime($first_mth_date);
		$end_time = strtotime($end_mth_date);
		
		while ($start_time <= $end_time) {
			$curr_yr = date("Y", $start_time);
			$curr_mth = mi(date("m", $start_time));
			$curr_mth_desc = date("F", $start_time);
			$mth_list[$curr_mth] = $curr_mth_desc;
			unset($curr_yr, $curr_mth, $curr_mth_desc);
			
			$start_time = strtotime("+1 month", $start_time);
		}
		
		return $mth_list;
	}
}
?>