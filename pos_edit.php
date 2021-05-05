<?php
/*
10/15/2012 9:26 AM Andy
- Add import pos items by csv.

3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['id']!=1) js_redirect("Admin only", "/index.php");

include_once('counter_collection.include.php');

class POS_EDIT extends Module{
	var $bid = 0;
	var $counter_list = array();
	var $branches_list = array();
	var $user_list = array();
	
	function __construct($title, $template=''){
		global $con, $smarty, $config, $sessioninfo;
		
		$this->bid = $sessioninfo['branch_id'];
		
		$this->load_counters();
		$this->load_branches_list();
		
		$payment_type = array("Cash", "Coupon", "Voucher", "Debit", "Credit", "Master", "VISA", "Diners", "AMEX", "Mix & Match Total Disc", "Rounding", "Others", "Deposit", "Check");
		$smarty->assign("payment_type", $payment_type);
		
		parent::__construct($title, $template);
	}
	 
	function _default(){
		global $smarty,$con;
		
		
		
		$this->display();
	}
	
	function load_counters(){
		global $con, $smarty;
		
		$this->counter_list = array();
		$con->sql_query("select id,network_name from counter_settings where branch_id=$this->bid order by network_name");
		while($r = $con->sql_fetchassoc()){
			$this->counter_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('counter_list', $this->counter_list);		
	}
	
	function load_branches_list(){
		global $con, $smarty;
		
		$this->branches_list = array();
		$con->sql_query("select id,code from branch order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches_list', $this->branches_list);
	}
	
	function load_user_list(){
		global $con, $smarty;
		
		$this->user_list = array();
		$con->sql_query("select id,u,fullname from user order by u");
		while($r = $con->sql_fetchassoc()){
			$this->user_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('user_list', $this->user_list);
	}
	
	function ajax_load_receipt(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);
		
		$bid = $this->bid;
		$cid = mi($_REQUEST['counter_id']);
		$date = trim($_REQUEST['date']);
		$receipt_no = trim($_REQUEST['receipt_no']);
		$is_new_receipt = mi($_REQUEST['is_new_receipt']);
		if(!$bid)	die("Invalid Branch ID");
		if(!$cid)	die("Invalid Counter ID");
		if(strtotime($date)<=strtotime('2010-01-01'))	die("Invalid Date");
		if(!$is_new_receipt && !$receipt_no)	die("Please key in receipt no.");
			
		$search_form = array();
		$search_form['bid'] = $bid;
		$search_form['cid'] = $cid;
		$search_form['date'] = $date;
		if(!$is_new_receipt)	$search_form['receipt_no'] = $receipt_no;
		
		$this->data['form'] = array();
		if(!$is_new_receipt){
			// pos
			$con->sql_query("select * from pos where branch_id=$bid and counter_id=$cid and date=".ms($date)." and receipt_no=".ms($receipt_no));
			$this->data['form'] = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$this->data['form'])	die("Receipt No ($receipt_no) Not Found");
			
			// pos_items
			$con->sql_query("select pi.*, si.sku_item_code 
			from pos_items pi
			left join sku_items si on si.id=pi.sku_item_id
			where pi.branch_id=$bid and pi.counter_id=$cid and pi.date=".ms($date)." and pi.pos_id=".mi($this->data['form']['id']));
			while($r = $con->sql_fetchassoc()){
				$this->data['pos_items'][] = $r;
			}
			$con->sql_freeresult();
			
			// pos_payment
			$con->sql_query("select * from pos_payment where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=".mi($this->data['form']['id']));
			while($r = $con->sql_fetchassoc()){
				$this->data['pos_payment'][] = $r;
			}
			$con->sql_freeresult();
			
			// mix and match info
			$con->sql_query("select * from pos_mix_match_usage where branch_id=".mi($bid)." and date=".ms($date)." and counter_id=".mi($cid)." and pos_id=".mi($this->data['form']['id'])." order by id");
			while($r = $con->sql_fetchassoc()){
				$r['more_info'] = unserialize($r['more_info']);
				$pos_mix_match_usage_list[] = $r;
			}
			$con->sql_freeresult();
			$this->data['mix_n_match'] = $pos_mix_match_usage_list;
			
			/*
			print '<pre>';
			print_r($this->data);
			print '</pre>';
			*/
		}
		
		$this->load_user_list();
		
		$smarty->assign('data', $this->data);
		$smarty->assign('search_form', $search_form);
		
		$ret = array();
		$ret['html'] = $smarty->fetch('pos_edit.content.tpl');
		$ret['ok'] = 1;
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function ajax_save_pos(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);exit;
		$form = $_REQUEST;
		
		$pos_id = mi($form['pos_id']);
		$bid = mi($form['branch_id']);
		$cid = mi($form['counter_id']);
		$date = $form['date'];
		
		if(!$pos_id)	$is_new_pos = true;
		
		if(is_cc_finalized($bid, $date))	die("Counter Collection Date $date already finalised.");
		
		// pos
		$pos = array();
		$pos['receipt_no'] = $form['receipt_no'];
		$pos['cashier_id'] = $form['cashier_id'];
		$pos['cancel_status'] = mi($form['cancel_status']);
		$pos['start_time'] = $form['start_time'];
		$pos['end_time'] = $form['end_time'];
		$pos['pos_time'] = $form['pos_time'];
		$pos['amount'] = round($form['amount'], 2);
		$pos['amount_tender'] = round($form['amount_tender'], 2);
		$pos['amount_change'] = round($form['amount_change'], 2);
		$pos['race'] = strtoupper(trim($form['race']));
		$pos['member_no'] = trim($form['member_no']);
		$pos['point'] = mi($form['point']);
		$pos['prune_status'] = mi($form['prune_status']);
		$pos['receipt_ref_no'] = $form['receipt_ref_no'];
		
		if(!$pos['receipt_no'])	die("Please key in receipt no.");
		if(!$pos['cashier_id'])	die("Please select cashier.");
		
		// pos_items
		$pi_list = array();
		$row_no = 0;
		
		$si_info_list = array();
		$item_id_list = array();
		
		if($form['pi_id']){
			foreach($form['pi_id'] as $pi_id){
				$row_no++;
				
				$pi = array();
				$pi['pi_id'] = $pi_id;
				
				if($form['pi_delete'][$pi_id]){
					$pi['is_delete'] = 1;
				}else{
					$sku_item_code = trim($form['pi_sku_item_code'][$pi_id]);
					$barcode = trim($form['pi_barcode'][$pi_id]);
					$item_id = mi($form['pi_item_id'][$pi_id]);
					$pi_qty = mf($form['pi_qty'][$pi_id]);
					$pi_price = round($form['pi_price'][$pi_id], 2);
					$pi_discount = round($form['pi_discount'][$pi_id], 2);
					
					if(!$sku_item_code)	die("POS Items row #$row_no no ARMS Code");
					if(!$barcode)	die("POS Items row #$row_no no Barcode");
					if(!$item_id)	die("POS Items row #$row_no no Item ID");
					if(!$pi_qty)	die("POS Items row #$row_no no Qty");
					
					if(in_array($item_id, $item_id_list))	die("POS Items row #$row_no Item ID Duplicated.");
					$item_id_list[] = $item_id;
					
					if(!isset($si_info_list[$sku_item_code])){
						$con->sql_query("select si.id, ifnull((select siph.trade_discount_code from sku_items_price_history siph where siph.branch_id=$bid and siph.sku_item_id=si.id and siph.added<=".ms($date.' 23:59:59')." order by siph.added desc limit 1), sku.default_trade_discount_code) as trade_discount_code
						from sku_items si 
						join sku on sku.id=si.sku_id
						where si.sku_item_code=".ms($sku_item_code));
						$si_info_list[$sku_item_code] = $con->sql_fetchassoc();
						$con->sql_freeresult();
					}
					
					if(!$si_info_list[$sku_item_code])	die("POS Items row #$row_no, ARMS Code $sku_item_code is invalid.");
					
					$pi['sku_item_id'] = $si_info_list[$sku_item_code]['id'];
					$pi['barcode'] = $barcode;
					$pi['qty'] = $pi_qty;
					$pi['item_id'] = $item_id;
					$pi['price'] = $pi_price;
					$pi['discount'] = $pi_discount;
					$pi['trade_discount_code'] = $si_info_list[$sku_item_code]['trade_discount_code'];
				}
				
				// add into pi_list
				$pi_list[] = $pi;
			}
		}
		
		// pos_payment
		$pp_list = array();
		$row_no = 0;
		
		if($form['pp_id']){
			foreach($form['pp_id'] as $pp_id){
				$row_no++;
				
				$pp = array();
				$pp['pp_id'] = $pp_id;
				
				if($form['pp_delete'][$pp_id]){
					$pp['is_delete'] = 1;
				}else{
					$pp_type = trim($form['pp_type'][$pp_id]);
					$pp_remark = trim($form['pp_remark'][$pp_id]);
					$pp_amount = round($form['pp_amount'][$pp_id], 2);
					
					$pp['type'] = $pp_type;
					$pp['remark'] = $pp_remark;
					$pp['amount'] = $pp_amount;
				}
				$pp_list[] = $pp;
			}
		}
		
		// mix and match
		$mm_list = array();
		if($form['mm_id']){

			foreach($form['mm_id'] as $mm_id){
				
				$mm = array();
				$mm['mm_id'] = $mm_id;
				
				if($form['mm_delete'][$mm_id]){
					$mm['is_delete'] = 1;
				}else{
					$mm_remark = trim($form['mm_remark'][$mm_id]);
					$mm_amount = round($form['mm_amount'][$mm_id], 2);
					
					$mm['remark'] = $mm_remark;
					$mm['amount'] = $mm_amount;
				}
				
				// add into mm_list
				$mm_list[] = $mm;
			}
		}
		//print_r($mm_list);exit;
		
		// backup first
		if(!$is_new_pos){
			$backup_time = time();
			$con->sql_query("create table tmp_pos_".$backup_time." (select * from pos where branch_id=$bid and counter_id=$cid and date=".ms($date)." and id=$pos_id)");
			$con->sql_query("create table tmp_pos_items_".$backup_time." (select * from pos_items where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id)");
			$con->sql_query("create table tmp_pos_payment_".$backup_time." (select * from pos_payment where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id)");
			$con->sql_query("create table pos_mix_match_usage_".$backup_time." (select * from pos_mix_match_usage where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id)");
		}
		
		// insert/update pos
		if($is_new_pos){	// insert
			$pos['branch_id'] = $bid;
			$pos['counter_id'] = $cid;
			$pos['date'] = $date;
			
			// get max id first
			$con->sql_query("select max(id) from pos where branch_id=$bid and counter_id=$cid and date=".ms($date));
			$max_pos_id = mi($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			$pos['id'] = $max_pos_id+1;
			$con->sql_query("insert into pos ".mysql_insert_by_field($pos));
			$pos_id = $pos['id'];
		}else{	// update
			$con->sql_query("update pos set ".mysql_update_by_field($pos)." where branch_id=$bid and counter_id=$cid and date=".ms($date)." and id=$pos_id");
		}
		
		// pos_items
		if($pi_list){
			foreach($pi_list as $pi){
				$pi_id = mi($pi['pi_id']);
				unset($pi['pi_id']);
				
				if(is_new_id($pi_id)){	// new row
					$pi['branch_id'] = $bid;
					$pi['counter_id'] = $cid;
					$pi['date'] = $date;
					$pi['pos_id'] = $pos_id;
					
					// get max id first
					$con->sql_query("select max(id) from pos_items where branch_id=$bid and counter_id=$cid and date=".ms($date));
					$max_pi_id = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
					$pi['id'] = $max_pi_id+1;
					
					$con->sql_query("insert into pos_items ".mysql_insert_by_field($pi));
				}else{	// existing row
					if($pi['is_delete']){
						$con->sql_query("delete from pos_items where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($pi_id));	
					}else{
						$con->sql_query("update pos_items set ".mysql_update_by_field($pi)." where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($pi_id));
					}
				}
			}
		}
		
		// pos_payment
		if($pp_list){
			foreach($pp_list as $pp){
				$pp_id = mi($pp['pp_id']);
				unset($pp['pp_id']);
				
				if(is_new_id($pp_id)){	// new row
					$pp['branch_id'] = $bid;
					$pp['counter_id'] = $cid;
					$pp['date'] = $date;
					$pp['pos_id'] = $pos_id;
					
					// get max id first
					$con->sql_query("select max(id) from pos_payment where branch_id=$bid and counter_id=$cid and date=".ms($date));
					$max_pp_id = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
					$pp['id'] = $max_pp_id+1;
					
					$con->sql_query("insert into pos_payment ".mysql_insert_by_field($pp));
				}else{	// existing row
					if($pp['is_delete']){
						$con->sql_query("delete from pos_payment where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($pp_id));	
					}else{
						$con->sql_query("update pos_payment set ".mysql_update_by_field($pp)." where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($pp_id));
					}
				}
			}
		}
		
		// mix and match
		if($mm_list){
			foreach($mm_list as $mm){
				$mm_id = mi($mm['mm_id']);
				unset($mm['mm_id']);
				
				if(is_new_id($mm_id)){	// new row
					$mm['branch_id'] = $bid;
					$mm['counter_id'] = $cid;
					$mm['date'] = $date;
					$mm['pos_id'] = $pos_id;
					
					//insert empty data
					$mm['group_id'] = 0;
					$mm['promo_id'] = 0;
					$mm['more_info'] = serialize(array());
					
					// get max id first
					$con->sql_query("select max(id) from pos_mix_match_usage where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id = $pos_id");
					$max_mm_id = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
					$mm['id'] = $max_mm_id+1;
					
					$con->sql_query("insert into pos_mix_match_usage ".mysql_insert_by_field($mm));
				}else{	// existing row
					if($mm['is_delete']){
						$con->sql_query("delete from pos_mix_match_usage where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($mm_id)." limit 1");
					}else{
						$con->sql_query("update pos_mix_match_usage set ".mysql_update_by_field($mm)." where branch_id=$bid and counter_id=$cid and date=".ms($date)." and pos_id=$pos_id and id=".mi($mm_id)." limit 1");
					}
				}
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['receipt_no'] = $pos['receipt_no'];
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print json_encode($ret);
	}
	
	function import_pi_by_csv(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);exit;
		$form = $_REQUEST;
		
		$pos_id = mi($form['pos_id']);
		$bid = mi($form['branch_id']);
		$cid = mi($form['counter_id']);
		$date = $form['date'];
		
		if(!$pos_id)	$is_new_pos = true;
		
		// check file
	    $err = check_upload_file('pi_csv', 'csv');
	    //$err[] = "error!";
	    
	    if($err){
	    	$str = "";
	    	foreach($err as $e){
	    		$str .= "$e\n";
	    	}
	    	print "<script>alert('$e');</script>";
	    	exit;
	    }
	    
	    // no problem found, safe to read
        $f = $_FILES['pi_csv'];
		$fp = fopen($f['tmp_name'], "r");
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = '';
		$pi_id = time();
		
		while($r = fgetcsv($fp)){
			//print_r($r);
			$pi = array();
			$pi['sku_item_code'] = trim($r[0]);
			$pi['barcode'] = trim($r[1]);
			$pi['qty'] = trim($r[2]);
			$pi['price'] = trim($r[3]);
			$pi['discount'] = trim($r[4]);
			
			if(!$pi['sku_item_code'] && !$pi['barcode'] && !$pi['qty'] && !$pi['price'] && !$pi['discount'])	continue;
			
			$pi_id++;
			
			$smarty->assign('pi', $pi);
			$smarty->assign('pi_id', $pi_id);
			
			$ret['html'] .= $smarty->fetch('pos_edit.content.pi.tpl');
		}
		
		$ret = array_map(utf8_encode, $ret);	// must put this before json encode
		print "<script>window.parent.import_pi_by_csv_callback('".jsstring(json_encode($ret))."');</script>";
	}
	
	function process_barcode() {
		$barcode = $_REQUEST['barcode'];//000186420009
		$barcode_info = $this->get_barcode_info($barcode);
		
		/*
		print_r($barcode_info);
		exit;
		print_r($barcode_info['pe']);
		*/
		
		$barcode_info = array_map(utf8_encode, $barcode_info);
		print json_encode($barcode_info);
		exit;
	}
	
	function get_barcode_info($barcode){
		global $con,$LANG,$config,$sessioninfo;
		
		$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description from sku_items where sku_item_code=".ms($barcode)." or mcode=".ms($barcode)." or artno=".ms($barcode)." or link_code=".ms($barcode)." limit 1");
		$si_info=$con->sql_fetchassoc();
		$con->sql_freeresult();
		if ($si_info) return $si_info;

		if (preg_match("/^00/", $barcode)){	// form ARMS' GRN barcoder
			$sku_item_id=mi(substr($barcode,0,8));
			$qty_pcs=mi(substr($barcode,8,4));
			$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description from sku_items where id = ".mi($sku_item_id));
			$si_info=$con->sql_fetchassoc();
			if (!$si_info['sku_item_id']){
				$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$sku_item_id);
			}
			$si_info['qty_pcs']=$qty_pcs;
			$con->sql_freeresult();
			$where = 'A';
		}
		elseif(strlen($barcode) == 12 || strlen($barcode) == 13){
			$config['barcode_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_unit_code_prefix");
			$config['barcode_unit_code_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_unit_code_mcode_length");
			if(!$config['barcode_unit_code_mcode_length']) $config['barcode_unit_code_mcode_length'] = 5;
			$config['barcode_price_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_code_prefix");
			$config['barcode_price_code_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_code_mcode_length");
			if(!$config['barcode_price_code_mcode_length']) $config['barcode_price_code_mcode_length'] = 5;
			
			if (isset($config['barcode_unit_code_prefix'])){
				if (preg_match("/^".$config['barcode_unit_code_prefix']."/", $barcode, $unit_matches)){
					$mcode = substr($barcode, strlen($unit_matches[0]), $config['barcode_unit_code_mcode_length']);
					$qty = substr($barcode, strlen($unit_matches[0]) + $config['barcode_unit_code_mcode_length'], 12-strlen($unit_matches[0])-$config['barcode_unit_code_mcode_length']);
				}
			}

			if (isset($config['barcode_price_code_prefix'])){
				if (preg_match("/^".$config['barcode_price_code_prefix']."/", $barcode, $price_matches)){
					$mcode = substr($barcode, strlen($price_matches[0]), $config['barcode_price_code_mcode_length']);
					$price = substr($barcode, strlen($price_matches[0]) + $config['barcode_price_code_mcode_length'], 12-strlen($price_matches[0])-$config['barcode_price_code_mcode_length']);
					$selling_price = $price/100;
				}
			}

			if ($mcode){
				$q1 = $con->sql_query("select si.id as sku_item_id, ifnull(sip.price, si.selling_price) as selling_price, si.scale_type, sku_id, si.sku_item_code
									   from sku_items si
									   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
									   where si.active=1 and si.mcode = ".ms($mcode));

				$si_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				if($si_info){
					if($si_info['scale_type']=="-1"){
						$q2 = $con->sql_query("select scale_type from sku where id=".mi($si_info['sku_id']));
						$r = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);
						$si_info['scale_type'] = $r['scale_type'];
						unset($r);
					}
					$con->sql_freeresult($q1);

					if ($qty && !$price){
						$si_info['qty_pcs'] = $qty/100;// the unit for $qty is in g, so $qty/100 to make the unit in 100g.
						unset($si_info['selling_price']);
					}elseif (isset($selling_price)){
						$si_info['selling_price'] = $selling_price;
						/*
						// todo: what if selling price is zero? what qty should i carry?
						if ($si_info['selling_price'] > 0)
							$si_info['qty_pcs'] = round($selling_price/$si_info['selling_price'], 2);
						else
							$si_info['qty_pcs'] = 1;
						*/
					}
				}else{
					$si_info = array();
					$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $barcode);
				}
			}else{
				$si_info = array();
				$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $barcode);
			}
			$where = 'B';
		}
		elseif(strlen($barcode) == 17 || strlen($barcode) == 18){
			$config['barcode_price_n_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_n_unit_code_prefix");
			$config['barcode_price_n_unit_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_price_n_unit_mcode_length");
			if(!$config['barcode_price_n_unit_mcode_length']) $config['barcode_price_n_unit_mcode_length'] = 5;
			$config['barcode_total_price_n_unit_code_prefix'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_total_price_n_unit_code_prefix");
			$config['barcode_total_price_n_unit_mcode_length'] = get_pos_settings_value($sessioninfo['branch_id'], "barcode_total_price_n_unit_mcode_length");
			if(!$config['barcode_total_price_n_unit_mcode_length']) $config['barcode_total_price_n_unit_mcode_length'] = 5;
			$config['weight_fraction'] = get_pos_settings_value($sessioninfo['branch_id'], "weight_fraction");

			// barcode_price_n_unit_code_prefix
			if(isset($config['barcode_price_n_unit_code_prefix']) && $config['barcode_price_n_unit_code_prefix'] == substr($barcode,0,2)){
				if (preg_match("/^".$config['barcode_price_n_unit_code_prefix']."/", $barcode, $price_unit_matches)){
					$mcode = substr($barcode, strlen($price_unit_matches[0]), $config['barcode_price_n_unit_mcode_length']);
					
					$q1 = $con->sql_query("select scale_type, id as sku_item_id, sku_id, sku_item_code from sku_items where active=1 and mcode = ".ms($mcode));
					$si_info = $con->sql_fetchassoc($q1);
					if($si_info['scale_type']=="-1"){
						$q2 = $con->sql_query("select scale_type from sku where id=".mi($si_info['sku_id']));
						$r = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);
						$si_info['scale_type'] = $r['scale_type'];
						unset($r);
					}
					$con->sql_freeresult($q1);

					if ($si_info){
						$mcode_length = strlen($config['barcode_price_n_unit_code_prefix'])+$config['barcode_price_n_unit_mcode_length'];
						$price_unit_code = substr($barcode,$mcode_length);	
						$price = substr($price_unit_code,0,5);
						$selling_price = $price/100;
						$qty = substr($price_unit_code,5,5);
						
						if($si_info['scale_type']==1) $unit_qty = intval($qty);
						else{
							if(intval($qty)<20){
								$si_info = array();
							}else{
								// the unit is base on scale type or weight fraction (if scale type is "weight" then use weight_fraction divide
								$unit_qty = round($qty/$config['weight_fraction'],2);
							}
						}
							
						if($si_info){
							if($si_info['scale_type']==0) $si_info = array();
							else{
								if($selling_price <= 0.01) $si_info = array();
								else{
									//$si_info['selling_price'] = $selling_price;
									$si_info['selling_price'] = round($selling_price*$unit_qty,2);
									$si_info['qty_pcs'] = $unit_qty;
								}
							}
						}
					}else{
						$si_info = array();
					}
				}
				$where = 'C1';
			}
			
			// barcode_total_price_n_unit_code_prefix
			elseif(isset($config['barcode_total_price_n_unit_code_prefix']) && $config['barcode_total_price_n_unit_code_prefix'] == substr($barcode,0,2)){
				if (preg_match("/^".$config['barcode_total_price_n_unit_code_prefix']."/", $barcode, $total_price_unit_matches)){
					$mcode = substr($barcode, strlen($total_price_unit_matches[0]), $config['barcode_total_price_n_unit_mcode_length']);
					$q1 = $con->sql_query("select scale_type, id as sku_item_id, sku_id, sku_item_code from sku_items where active=1 and mcode = ".ms($mcode));
					$si_info = $con->sql_fetchassoc($q1);
					if($si_info['scale_type']=="-1"){
						$q2 = $con->sql_query("select scale_type from sku where id=".mi($si_info['sku_id']));
						$r = $con->sql_fetchassoc($q2);
						$con->sql_freeresult($q2);
						$si_info['scale_type'] = $r['scale_type'];
						unset($r);
					}
					$con->sql_freeresult($q1);

					if (is_array($si_info)){
						$price = substr($barcode,(strlen($config['barcode_total_price_n_unit_code_prefix'])+$config['barcode_total_price_n_unit_mcode_length']),5);
						$selling_price = $price/100;
						$qty = substr($barcode,(strlen($config['barcode_total_price_n_unit_code_prefix'])+$config['barcode_total_price_n_unit_mcode_length']+5),5);
						
						if($si_info['scale_type']==1) $unit_qty = intval($qty);
						else{
							if(intval($qty)<20) $si_info = array();
							else $unit_qty = round($qty/$config['weight_fraction'],2);  // the unit is base on scale type or weight fraction (if scale type is "weight" then use weight_fraction divide
						}
						
						if($si_info){
							if($si_info['scale_type']==0) $si_info = array();
							else{
								if($selling_price<= 0.01) $si_info = array();
								else{
									//$si_info['selling_price'] = $selling_price/$unit_qty;
									$si_info['selling_price'] = $selling_price;
									$si_info['qty_pcs'] = $unit_qty;
								}
							}
						}
					}else{
						$si_info = array();
					}
				}
				$where = 'C2';
			}
			
			/*
			$pe = array();
			$pe['barcode'] = $barcode;
			$pe['prefix'] = substr($barcode,0,2);
			$pe['mcode'] = $mcode;
			//$pe['price'] = intval($price);
			$pe['selling_price'] = $selling_price;
			$pe['qty'] = intval($qty);
			$pe['scale_type'] = $si_info['scale_type'];
			$pe['weight_fraction'] = $config['weight_fraction'];
			$pe['unit_qty'] = round($pe['qty']/$config['weight_fraction'],2);
			if ($pe['prefix'] == $config['barcode_total_price_n_unit_code_prefix']) {
				$pe['total_price'] = $selling_price;
			}
			else {
				$pe['total_price'] = round($selling_price*$pe['unit_qty'],2);
			}
			*/

			if(!$si_info){
				$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $barcode);
			}
		}
		else{	// from ATP GRN Barcode, try to search the link-code 
			$linkcode=substr($barcode,0,7);
			$qty_pcs=mi(substr($barcode,7,5));
			$con->sql_query("select id as sku_item_id,sku_item_code,receipt_description from sku_items where link_code = ".ms($linkcode));
			$si_info=$con->sql_fetchassoc();
			$si_info['qty_pcs']=$qty_pcs;
			$con->sql_freeresult();
			if (!$si_info['sku_item_id']){
				$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'],$linkcode);	
			}
			$where = 'D';
		}
		
		$si_info['barcode'] = $barcode;
		$si_info['prefix'] = substr($barcode,0,2);
		$si_info['mcode'] = $mcode;
		$si_info['weight_fraction'] = $config['weight_fraction'];
		
		ksort($si_info);
		return $si_info;
	}
}

$POS_EDIT = new POS_EDIT('POS Edit');
?>
