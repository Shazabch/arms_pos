<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
include("do.include.php");

class DO_MATRIX_IBT_PROCESS extends Module{
	var $branches = array();
	var $data = array();
	
	function __construct($title)
	{
		global $con, $smarty;
		
		$con->sql_query("select * from branch where active=1 order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();

		$smarty->assign("branches", $this->branches);

		parent::__construct($title);
	}
	
	function _default(){
		if(isset($_REQUEST['show_report'])){
			$this->generate_data();
		}
		$this->display();
	}
	
	private function generate_data(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$to_branch_id = mi($form['to_branch_id']);
		$sid = mi($form['sku_item_id']);
		$include_nrr = mi($form['include_nrr']);
		
		$err = array();
		$this->data = array();
		
		if(!$to_branch_id){	// no branch
			$err[] = "Please select Deliver Branch.";
		}
		if(!$sid){	// no sku
			$err[] = "Please search and select SKU.";
		}else{
			if(!$err){
				// select sku
				$con->sql_query("select * from sku_items where id=$sid");
				$this->data['si'] = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$this->data['si']){	// invalid sku
					$err[] = "Invalid SKU ID#$sid";
				}
			}
		}
		
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		$this->data['to_branch_id'] = $to_branch_id;
		$this->data['size_list'] = get_matrix_size();
		
		if($this->data['size_list']){
			$this->data['size_list'] = array_values($this->data['size_list']);
			$size_count = count($this->data['size_list']);
			foreach($this->data['size_list'] as $size_num => $size){
				// stock balance
				$this->data['main']['stock_balance'][$size_num] = rand(0, 5);
				// 30 days pos
				$this->data['main']['30d_pos'][$size_num] = rand(0, 5);
				// stock min qty
				if($include_nrr || ($size_num > 1 && $size_num < $size_count-1)){
					$this->data['main']['stock_min_qty'][$size_num] = rand(1, 5);
				}
				// reorder qty
				$this->data['main']['reorder_qty'][$size_num] = $this->data['main']['30d_pos'][$size_num] - $this->data['main']['stock_balance'][$size_num];
				if($this->data['main']['reorder_qty'][$size_num] < 0)	unset($this->data['main']['reorder_qty'][$size_num]);
			}
			
			if($this->data['main']['reorder_qty']){
				$this->data['available_bid_list'] = array();
				foreach($this->branches as $bid => $b){
					if($bid == $to_branch_id)	continue;
					
					$this->data['available_bid_list'][$bid] = $bid;
					if(count($this->data['available_bid_list'])>=2)	break;	// put 2 branches only
				}
				
				foreach($this->data['size_list'] as $size_num => $size){
					foreach($this->data['available_bid_list'] as $bid){	// loop available branch
						// stock balance
						$this->data['available_branch'][$bid]['stock_balance'][$size_num] = rand(0, 5);
						// 30 days pos
						$this->data['available_branch'][$bid]['30d_pos'][$size_num] = rand(0, 5);
						
						// Available Qty
						$this->data['available_branch'][$bid]['available_qty'][$size_num] = $this->data['available_branch'][$bid]['stock_balance'][$size_num] - $this->data['main']['stock_min_qty'][$size_num];
						if($this->data['available_branch'][$bid]['available_qty'][$size_num] < 0)	$this->data['available_branch'][$bid]['available_qty'][$size_num] = 0;
					}
				}
			}
		}
		
		$smarty->assign('data', $this->data);
	}
	
	function ajax_generate_do(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		$sku_id = mi($form['sku_id']);
		$bid = mi($sessioninfo['branch_id']);
		$to_branch_id = mi($form['to_branch_id']);
		
		if(!$sku_id)	die("Invalid SKU ID.");
		if(!$to_branch_id)	die("Invalid Deliver Branch.");
		
		$item_num = 0;
		$transfer_qty_list = array();
		if($form['transfer_qty']){
			foreach($form['transfer_qty'] as $size_num => $transfer_qty){
				if($transfer_qty <= 0)	continue;
				$transfer_qty_list[] = $transfer_qty;
				$item_num++;
			}
		}
		if($item_num <= 0){
			die("No Item to Generate DO.");
		}
		
		$do = array();
		$do_items = array();
		$price_type = $config['do_default_price_from'] ? $config['do_default_price_from']:'selling';
		
		$do['branch_id'] = $bid;
		$do['do_branch_id'] = $to_branch_id;
		$do['user_id'] = $sessioninfo['id'];
		$do['last_update'] = 'CURRENT_TIMESTAMP';
		$do['added'] = 'CURRENT_TIMESTAMP';
		$do['do_date'] = date("Y-m-d");
		$do['do_type'] = 'transfer';
		
		if($config['do_default_price_from']=='cost')    $do['price_indicate'] = 1;
		elseif($config['do_default_price_from']=='last_do')    $do['price_indicate'] = 3;
		else    $do['price_indicate'] = 2;
		
		if($config['enable_gst']){
			$do['is_under_gst'] = $is_under_gst = check_do_gst_status($do);
		}
	
		// UOM FOR EACH
		$con->sql_query("select * from uom where fraction = 1 and active = 1 order by id limit 1");
		$uom = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sql = "select si.id as sku_item_id, si.packing_uom_id,ifnull(sip.price,si.selling_price) as sp, ifnull(sic.grn_cost,si.cost_price) as cost,si.artno,si.mcode
		,ifnull(sip.trade_discount_code,sku.default_trade_discount_code) as price_type, sic.qty as sb1, sic2.qty as sb2
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join sku_items_price sip on sip.sku_item_id=si.id and sip.branch_id=$bid
		left join sku_items_cost sic on sic.sku_item_id=si.id and sic.branch_id=$bid
		left join sku_items_cost sic2 on sic2.sku_item_id=si.id and sic2.branch_id=$to_branch_id
		where si.sku_id=$sku_id
		limit $item_num";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$items = array();
			$items['sku_item_id'] = $r['sku_item_id'];
			$items['uom_id'] = $uom['id'];
			$items['selling_price'] = $r['sp'];
			$items['cost'] = $r['cost'];
			$items['artno_mcode'] = $r['artno']?$r['artno']:$r['mcode'];
			$items['price_type'] = array(mi($bid)=>$r['price_type']);
			$items['pcs'] = array_shift($transfer_qty_list);
			$items['stock_balance1'] = $r['sb1'];
			$items['stock_balance2'] = $r['sb2'];
			
			// get cost price	
			if($price_type=='last_do'){
				// get last DO price
				$q_p = $con->sql_query("select (di.cost_price/uom.fraction) as cost_price, (di.display_cost_price/uom.fraction) as display_cost_price, di.display_cost_price_is_inclusive
				from do_items di
				left join do on di.do_id = do.id and di.branch_id = do.branch_id 
				left join uom on uom.id=di.uom_id
				where do.active=1 and di.sku_item_id=".mi($r['sku_item_id'])." and di.branch_id=$bid order by di.id desc limit 1");
			}
			elseif($price_type=='selling'){
				// get selling price
				$q_p = $con->sql_query("select price as cost_price from sku_items_price where sku_item_id=".mi($r['sku_item_id'])." and branch_id=$bid");
			}
			elseif($price_type=='cost'){
				// cost
				$q_p = $con->sql_query("select grn_cost as cost_price from sku_items_cost where sku_item_id=".mi($r['sku_item_id'])." and branch_id=$bid");
			}
			$temp_p = $con->sql_fetchrow($q_p);
			if(!$temp_p){
				if ($price_type=='last_do' or $price_type=='cost'){ // DO or GRN selected
					$q_m = $con->sql_query("select if(grn_cost is null, cost_price, grn_cost) as cost_price from  sku_items left join sku_items_cost on sku_item_id=sku_items.id and branch_id=$bid where id=".mi($r['sku_item_id']));
				}
				else
				{
					$q_m = $con->sql_query("select if(price is null, selling_price, price) as cost_price from sku_items left join sku_items_price on sku_item_id=sku_items.id and branch_id=$bid where id=".mi($r['sku_item_id']));
				}
				$temp_p = $con->sql_fetchrow($q_m);
			}
			$cost_price = $temp_p['cost_price'];
			$display_cost_price = isset($temp_p['display_cost_price']) ? $temp_p['display_cost_price'] : $cost_price;
			$display_cost_price_is_inclusive = isset($temp_p['display_cost_price_is_inclusive']) ? $temp_p['display_cost_price_is_inclusive'] : 0;
			
			if($config['enable_gst']){
				// get sku inclusive tax
				$is_sku_inclusive = get_sku_gst("inclusive_tax", $r['sku_item_id']);
				
				// get sku original output gst
				$output_gst = get_sku_gst("output_tax", $r['sku_item_id']);
				if($output_gst && $is_under_gst){
					if($price_type == 'selling'){
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							$price_included_gst = $cost_price;
							$display_cost_price_is_inclusive = 1;
							$display_cost_price = $price_included_gst;
							
							// find the selling price before tax
							$gst_amt = $price_included_gst / ($output_gst['rate']+100) * $output_gst['rate'];
							$before_tax_price = $price_included_gst - $gst_amt;
						}else{
							// is exclusive tax
							$before_tax_price = $cost_price;
							$gst_amt = $before_tax_price * $output_gst['rate'] / 100;
							$price_included_gst = $before_tax_price + $gst_amt;
						}
						
						// cost price need to use before gst for selling price
						$cost_price = $before_tax_price;
					}
					
					
					$items['gst_id'] = $output_gst['id'];
					$items['gst_code'] = $output_gst['code'];
					$items['gst_rate'] = $output_gst['rate'];
					$items['display_cost_price_is_inclusive'] = $display_cost_price_is_inclusive;
					$items['display_cost_price'] = $display_cost_price;
					
				}
			}
			
			$items['cost_price'] = $cost_price;
			
			$do_items []= $items;
		}
		$con->sql_freeresult($q1);
	
		$con->sql_query("insert into do ".mysql_insert_by_field($do));
		$do_id = $con->sql_nextid();
		
		foreach($do_items as $upd){
			$upd['do_id'] = $do_id;
			$upd['branch_id'] = $bid;
			if($upd['price_type']) $upd['price_type'] = serialize($upd['price_type']);
			$con->sql_query("insert into do_items ".mysql_insert_by_field($upd));
		}
		
		// recalculate all amt
		auto_update_do_all_amt($bid, $do_id);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['do_id'] = $do_id;
		$ret['bid'] = $bid;
		$ret['html'] = "DO ID#<a href='do.php?a=view&branch_id=$bid&id=$do_id' target='_blank'>$do_id</a>";
		print json_encode($ret);
		exit;
	}
}

$DO_MATRIX_IBT_PROCESS = new DO_MATRIX_IBT_PROCESS('Matrix IBT Process');
?>