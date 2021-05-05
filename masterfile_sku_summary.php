<?php
/* 
4/12/2018 9:45 AM Kuan Yeh
- Enhanced to added total purchase by YTD and MTD 
- Enhanced to added total sales by YTD and MTD 

4/20/2018 10:26 AM Andy
- Fixed Purchase Chart not showing.

11/26/2018 3:06 PM Justin
- Enhanced to have Quotation Cost information.
*/


include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//ini_set('memory_limit', '2048M');
set_time_limit(0);

class MASTERFILE_SKU_SUMMARY extends Module{
	var $data = array();
	var $branch_got_gst = 0;
	var $bid = 0;
	var $branches = array();
	var $period_list = array("last_3m"=>"Last 3 Months", "last_6m"=>"Last 6 Months");
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

		// current selected branch
		$this->bid = BRANCH_CODE == 'HQ' ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		if(!$this->bid)	$this->bid = $sessioninfo['branch_id'];
		
		// check current branch got gst or not
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $this->bid;
			$prms['date'] = date("Y-m-d");
			if(check_gst_status($prms)){
				$this->branch_got_gst = 1;
			}
		}
		$smarty->assign('branch_got_gst', $this->branch_got_gst);
		
		// branches
		$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
	    while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
		
		// Period List
		$smarty->assign('period_list', $this->period_list);
		
		parent::__construct($title);
	}
	
	function _default(){
	    global $con, $smarty;

	    $this->load_sku_data();
		$this->display();
	}
	
	private function load_sku_data(){
		global $con, $smarty, $sessioninfo, $config;
		
		$sid = mi($_REQUEST['sid']);
		if(!$sid)	return;
		
		$this->data = array();
		
		if($config['enable_gst']){
			$extra_col = ",input_gst.code as input_gst_code, input_gst.rate as input_gst_rate, output_gst.code as output_gst_code, output_gst.rate as output_gst_rate";
			$extra_left_join ="left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
				left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))";
			
			if($this->branch_got_gst){
				$extra_col .= ", round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(sip.price,si.selling_price),ifnull(sip.price,si.selling_price)*(100+output_gst.rate)/100),2)
		as selling_price_after_gst, round(if(if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes',ifnull(sip.price, si.selling_price)/(100+output_gst.rate)*100,ifnull(sip.price, si.selling_price)),2) as selling_price_before_tax";
			}
		}
		
		$con->sql_query($q = "select si.id, si.sku_item_code, si.mcode,si.link_code, si.description as sku_desc, si.artno, c.description as cat_desc, brand.description as brand_desc, vendor.description as vendor_desc, ifnull(sic.grn_cost, si.cost_price) as cost, ifnull(sip.price, si.selling_price) as selling_price, si.selling_price as mst_selling_price, 
		si.cost_price as mst_cost, (sic.grn_cost * sic.qty) as closing_bal_val, si.sku_apply_items_id, sip.last_update as sp_last_update, sic.last_update as cost_last_update, sku.vendor_id
		$extra_col
		from sku_items si 
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join brand on brand.id=sku.brand_id
		left join vendor on vendor.id=sku.vendor_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join sku_items_cost sic on sic.branch_id=$this->bid and sic.sku_item_id=si.id
		left join sku_items_price sip on sip.branch_id=$this->bid and sip.sku_item_id=si.id
		$extra_left_join
		where si.id=$sid");
		//print $q;
		$this->data['sku_info'] = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		// load sku photos
		$apply_photo_list = get_sku_apply_item_photos($this->data['sku_info']['sku_apply_items_id']);
		$photo_list = get_sku_item_photos($this->data['sku_info']['id'],$this->data['sku_info']);
		
		if(!$apply_photo_list)	$apply_photo_list = array();
		if(!$photo_list)	$photo_list = array();
		$all_photo_list = array_merge($apply_photo_list, $photo_list);
		if($all_photo_list){
			foreach($all_photo_list as $photo_path){
				$photo_details = array();
				$photo_details['abs_path'] = $photo_path;
				if(file_exists($photo_path)){
					$photo_details['last_update'] = date("Y-m-d H:i:s", filemtime($photo_path));
					//$photo_details['name'] = basename($photo_path);			
					$this->data['sku_info']['photo_list'][] = $photo_details;
				}
			}
		}
		
		if($this->branch_got_gst){
			$this->data['sku_info']['gst_amt'] = round($this->data['sku_info']['selling_price_after_gst']-$this->data['sku_info']['selling_price_before_tax'], 2);
			$this->data['sku_info']['gp'] = round($this->data['sku_info']['selling_price_before_tax']-$this->data['sku_info']['cost'], 2);
			if($this->data['sku_info']['gp']) $this->data['sku_info']['gp_per'] = round($this->data['sku_info']['gp'] / $this->data['sku_info']['selling_price_before_tax']*100, 2);
		}else{
			$this->data['sku_info']['gp'] = round($this->data['sku_info']['selling_price']-$this->data['sku_info']['cost'], 2);
			if($this->data['sku_info']['gp']) $this->data['sku_info']['gp_per'] = round($this->data['sku_info']['gp'] / $this->data['sku_info']['selling_price']*100, 2);
		}
		
		// load turnover
		$curr_date = date("Y-m-d");
		$last_mth_date = date("Y-m-d", strtotime("-1 month", strtotime($curr_date)));
		$last_yr_date = date("Y-m-d", strtotime("-1 year", strtotime($curr_date)));
		$no_of_days = mi((strtotime($curr_date)-strtotime($last_mth_date))/86400)+1;
		$q1 = $con->sql_query("select sum(pos.cost) as pos_val
							   from sku_items_sales_cache_b".mi($this->bid)." pos 
							   where pos.date between ".ms($last_mth_date)." and ".ms($curr_date)." and pos.sku_item_id = ".mi($sid)."
							   group by pos.sku_item_id");
		$pos_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($pos_info['pos_val']) $this->data['sku_info']['inventory_turnover'] = ($this->data['sku_info']['closing_bal_val'] / $pos_info['pos_val']) * $no_of_days;
		
		// first select to see whether got change price on the actual day from last year 
		$sp_last_yr_date = date("Y-m-d", strtotime("-1 year", strtotime($curr_date)));
		$q1 = $con->sql_query("select his.*
							   from sku_items_price_history his 
							   where his.branch_id = ".mi($this->bid)." and 
							   his.sku_item_id = ".mi($sid)." and 
							   his.added between ".ms($sp_last_yr_date." 00:00:00")." and ".ms($sp_last_yr_date." 23:59:59")."
							   order by his.added desc
							   limit 1");
							   
		// couldn't find any change price on the actual day from last year, search earlier than that
		if($con->sql_numrows($q1) == 0){
			$q1 = $con->sql_query("select his.*
								   from sku_items_price_history his 
								   where his.branch_id = ".mi($this->bid)." and 
								   his.sku_item_id = ".mi($sid)." and 
								   his.added < ".ms($sp_last_yr_date." 00:00:00")."
								   order by his.added desc
								   limit 1");
		}
		$min_sp_his_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($min_sp_his_info['added']){ // found have price change history, replace the date from
			$sp_last_yr_date = $min_sp_his_info['added'];
		}else $sp_last_yr_date = $sp_last_yr_date." 00:00:00";
		
		// load min and max selling price
		$q1 = $con->sql_query("select min(his.price) as min_sp, max(his.price) as max_sp
							   from sku_items_price_history his 
							   where his.branch_id = ".mi($this->bid)." and 
							   his.sku_item_id = ".mi($sid)." and 
							   his.added between ".ms($sp_last_yr_date)." and ".ms($curr_date." 23:59:59"));
		$sp_his_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$this->data['sku_info']['min_sp'] = $sp_his_info['min_sp'];
		$this->data['sku_info']['max_sp'] = $sp_his_info['max_sp'];
		
		if(!$sp_his_info['min_sp'] && !$sp_his_info['max_sp']){ // found system never have change price before, proceed to check further on latest sp
			if(strtotime($this->data['sku_info']['sp_last_update']) >= strtotime($last_yr_date)){ // found got change price from last 12 months
				if($this->data['sku_info']['mst_selling_price'] < $this->data['sku_info']['selling_price']){
					$this->data['sku_info']['min_sp'] = $this->data['sku_info']['mst_selling_price'];
					$this->data['sku_info']['max_sp'] = $this->data['sku_info']['selling_price'];
				}else{
					$this->data['sku_info']['min_sp'] = $this->data['sku_info']['selling_price'];
					$this->data['sku_info']['max_sp'] = $this->data['sku_info']['mst_selling_price'];
				}
			}else{
				if($this->data['sku_info']['selling_price'] > 0) $this->data['sku_info']['min_sp'] = $this->data['sku_info']['max_sp'] = $this->data['sku_info']['selling_price'];
				else $this->data['sku_info']['min_sp'] = $this->data['sku_info']['max_sp'] = $this->data['sku_info']['mst_selling_price'];
			}
		}
		unset($min_sp_his_info, $sp_his_info);
		
		// first select to see whether got change cost on the actual day from last year 
		$cp_last_yr_date = date("Y-m-d", strtotime("-1 year", strtotime($curr_date)));
		$q1 = $con->sql_query("select his.*
							   from sku_items_cost_history his 
							   where his.branch_id = ".mi($this->bid)." and 
							   his.sku_item_id = ".mi($sid)." and 
							   his.date = ".ms($cp_last_yr_date)."
							   limit 1");
							   
		// couldn't find any change cost on the actual day from last year, search earlier than that
		if($con->sql_numrows($q1) == 0){
			$q1 = $con->sql_query("select his.*
								   from sku_items_cost_history his 
								   where his.branch_id = ".mi($this->bid)." and 
								   his.sku_item_id = ".mi($sid)." and 
								   his.date < ".ms($cp_last_yr_date)."
								   order by his.date desc
								   limit 1");
		}
		$min_cp_his_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($min_cp_his_info['date']){ // found have change cost history, replace the date from
			$cp_last_yr_date = $min_cp_his_info['date'];
		}
		
		// load min and max cost price
		$q1 = $con->sql_query("select min(his.grn_cost) as min_cost, max(his.grn_cost) as max_cost
							   from sku_items_cost_history his 
							   where his.branch_id = ".mi($this->bid)." and 
							   his.sku_item_id = ".mi($sid)." and 
							   his.date between ".ms($cp_last_yr_date)." and ".ms($curr_date));
		$cp_his_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$this->data['sku_info']['min_cost'] = $cp_his_info['min_cost'];
		$this->data['sku_info']['max_cost'] = $cp_his_info['max_cost'];
	
		if(!$cp_his_info['min_cost'] && !$cp_his_info['max_cost']){ // found system never have change cost before, proceed to check further on latest cost
			if(strtotime($this->data['sku_info']['cost_last_update']) >= strtotime($last_yr_date)){ // found got change cost from last 12 months
				if($this->data['sku_info']['mst_cost'] < $this->data['sku_info']['cost']){
					$this->data['sku_info']['min_cost'] = $this->data['sku_info']['mst_cost'];
					$this->data['sku_info']['max_cost'] = $this->data['sku_info']['cost'];
				}else{
					$this->data['sku_info']['min_cost'] = $this->data['sku_info']['cost'];
					$this->data['sku_info']['max_cost'] = $this->data['sku_info']['mst_cost'];
				}
			}else{
				if($this->data['sku_info']['cost'] > 0) $this->data['sku_info']['min_cost'] = $this->data['sku_info']['max_cost'] = $this->data['sku_info']['cost'];
				else $this->data['sku_info']['min_cost'] = $this->data['sku_info']['max_cost'] = $this->data['sku_info']['mst_cost'];
			}
		}
		unset($min_cp_his_info, $cp_his_info);
		
		// load total purchase QTY for MTD 
		$year_first =date('Y-01-01');
		$ths_mth_first=date("Y-m-01");
		$tdy_date=date("Y-m-d");
		
		//load total purchase QTY for MTD 
		//month to today
		$qaa_m= $con->sql_query("SELECT gi.branch_id, gi.grn_id,gi.sku_item_id,
							SUM(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*uom.fraction + gi.pcs,gi.acc_ctn*uom.fraction+gi.acc_pcs)) as total_pur_m
							FROM grn_items gi
							JOIN grn on grn.branch_id=gi.branch_id AND grn.id=gi.grn_id
							JOIN grr on grr.branch_id=gi.branch_id AND grr.id=grn.grr_id
							LEFT JOIN uom on gi.uom_id=uom.id
							WHERE grr.active=1 
							AND grn.approved=1 
							AND grn.status=1 
							AND grn.active=1 
							AND grn.branch_id=".mi($this->bid)." 
							AND gi.sku_item_id=".mi($sid)."
							AND grr.rcv_date BETWEEN ".ms($ths_mth_first)." AND ".ms($tdy_date)." 							
							");  
							//print $sql;
		$tp_info_m=$con->sql_fetchassoc($qaa_m);
		$con->sql_freeresult($qaa_m);					
		
		//year to today
		$qaa_y= $con->sql_query("SELECT gi.branch_id, gi.grn_id,gi.sku_item_id,
							SUM(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*uom.fraction + gi.pcs,gi.acc_ctn*uom.fraction+gi.acc_pcs)) as total_pur_y
							FROM grn_items gi
							JOIN grn on grn.branch_id=gi.branch_id AND grn.id=gi.grn_id
							JOIN grr on grr.branch_id=gi.branch_id AND grr.id=grn.grr_id
							LEFT JOIN uom on gi.uom_id=uom.id
							WHERE grr.active=1 
							AND grn.approved=1 
							AND grn.status=1 
							AND grn.active=1 
							AND grn.branch_id=".mi($this->bid)." 
							AND gi.sku_item_id=".mi($sid)."
							AND grr.rcv_date BETWEEN ".ms($year_first)." AND ".ms($tdy_date)." 							
							"); 
							//print " ";
							//print $sql2;
		$tp_info_y=$con->sql_fetchassoc($qaa_y);
		$con->sql_freeresult($qaa_y);	
		
		$this->data['sku_info']['total_pur_m']=$tp_info_m['total_pur_m'];
		$this->data['sku_info']['total_pur_y']=$tp_info_y['total_pur_y'];
		
		unset($tp_info_m,$tp_info_y); 
		
		
		//load total sales QTY for MTD
		//month to today
		$qbb_m= $con->sql_query("select SUM(qty)as total_s_m from sku_items_sales_cache_b".mi($this->bid)." where sku_item_id=".mi($sid)." 
		and date between ".ms($ths_mth_first)." 
		and ".ms($tdy_date)." "); 
		$ts_info_m=$con->sql_fetchassoc($qbb_m);
		$con->sql_freeresult($qbb_m);
		
		//year to today		
		$qbb_y= $con->sql_query("select SUM(qty)as total_s_y from sku_items_sales_cache_b".mi($this->bid)." where sku_item_id=".mi($sid)." 
		and date between ".ms($year_first)." 
		and ".ms($tdy_date)." "); 
		$ts_info_y=$con->sql_fetchassoc($qbb_y);
		$con->sql_freeresult($qbb_y);
		
		$this->data['sku_info']['total_s_m']=$ts_info_m['total_s_m'];
		$this->data['sku_info']['total_s_y']=$ts_info_y['total_s_y'];
				
		unset($ts_info_m,$ts_info_y);
		
		// load Quotation Cost from Master Vendor
		$q1 = $con->sql_query("select sivqc.*
							   from sku_items_vendor_quotation_cost sivqc
							   where sivqc.branch_id = ".mi($this->bid)." and sivqc.sku_item_id = ".mi($sid)." and sivqc.vendor_id = ".mi($this->data['sku_info']['vendor_id']));
		$quo_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$this->data['sku_info']['quotation_cost'] = $quo_info['cost'];
		
		$smarty->assign('data', $this->data);
	}
	
	function reload_sales_chart(){
		global $con, $smarty, $sessioninfo, $config;
		
		$sid = mi($_REQUEST['sid']);
		$compare_sales = mi($_REQUEST['compare_sales']);
		if(!$sid)	return;
		
		$period = trim($_REQUEST['period']);
		$y = date("Y");
		$m = mi(date("m"));
		switch($period){
			case 'last_6m':
				$m_to_deduct = 5;
				break;
			case 'last_3m':
			default:
				$m_to_deduct = 2;
				$date_from = date("Y-m-d", strtotime("-3 month", strtotime($date_to)));
				break;
		}
		$date_to = $y.'-'.$m.'-'.days_of_month($m, $y);
		for($i = 0; $i < $m_to_deduct; $i++){
			$m--;
			if($m <=0){
				$y--;
				$m = 12;
			}
		}
		$date_from = $y.'-'.$m.'-1';
		
		$ret = array();

		// sku item id list
		if($compare_sales){
			// select random count of SKU items for comparison
			// todo: need to select child for comparison
			$ret['sid_list'][$sid] = array();
			
			// try to select if this sku items has other child
			$q1 = $con->sql_query("select * from sku_items where sku_id = (select tmp.sku_id from sku_items tmp where tmp.id = ".mi($sid).") and id != ".mi($sid));
			
			while($r = $con->sql_fetchassoc($q1)){
				$ret['sid_list'][$r['id']] = array();
			}
			$con->sql_freeresult($q1);
		}else{
			$ret['sid_list'] = array($sid=>array());
		}

		// get details for sku item list
		foreach($ret['sid_list'] as $tmp_sid => $r){
			$q1 = $con->sql_query("select si.sku_item_code, si.description as sku_desc
			from sku_items si
			where si.id=$tmp_sid");
			$tmp = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			$tmp['color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));	// random color
			
			$ret['sid_list'][$tmp_sid] = $tmp;
		}
		
		// generate sales
		$sdate_from = strtotime($date_from);
		$sdate_to = strtotime($date_to);
		while($sdate_from < $sdate_to){
			$db_yr = date("Y", $sdate_from);
			$db_mth = date("m", $sdate_from);
			$key = $db_yr.$db_mth;
			foreach($ret['sid_list'] as $tmp_sid=>$s){
				$q1 = $con->sql_query($sql="select sum(pos.amount) as ttl_gross_amt, sum(pos.cost) ttl_cost_amt
									   from sku_items_sales_cache_b".mi($this->bid)." pos
									   where pos.sku_item_id = ".mi($tmp_sid)." and pos.year = ".mi($db_yr)." and pos.month = ".mi($db_mth));

				while($r = $con->sql_fetchassoc($q1)){				
					$ret['item_sales'][$tmp_sid][$key]['gross_amount'] += $r['ttl_gross_amt'];
					$ret['item_sales'][$tmp_sid][$key]['gp'] += $r['ttl_gross_amt'] - $r['ttl_cost_amt'];
					$ret['item_sales'][$tmp_sid][$key]['label'] = date("M Y", $sdate_from);
				}
				$con->sql_freeresult($q1);
			}
			
			$sdate_from = strtotime("+1 month", $sdate_from);
		}
		
		// generate GRN
		foreach($ret['sid_list'] as $tmp_sid => $s){
			$q1 = $con->sql_query($sql = "select distinct(grr.vendor_id) as vid, vendor.description as vendor_desc,
								   sum(if (gi.acc_cost is null, gi.cost, gi.acc_cost) * if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn *rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as item_amt
								   from grn
								   join grn_items gi on gi.branch_id=grn.branch_id and gi.grn_id=grn.id
								   join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
								   join vendor on vendor.id=grr.vendor_id
								   left join uom rcv_uom on gi.uom_id=rcv_uom.id
								   where grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grn.branch_id=".mi($this->bid)." and gi.sku_item_id=".mi($tmp_sid)." and 
								   grr.rcv_date between ".ms($date_from)." and ".ms($date_to)."
								   group by grn.vendor_id");
			$ret['grn_sql'][$tmp_sid] = $sql;

			while($r = $con->sql_fetchassoc($q1)){
				$ret['grn']['vendor_id_list'][$r['vid']]['vendor_desc'] = $r['vendor_desc'];
				$ret['grn']['vendor_id_list'][$r['vid']]['amt'] += $r['item_amt'];
			}
			$con->sql_freeresult($q1);
		}
		
	
	
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	function ajax_load_quotation_cost(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		$sid = $form['sid'];
		if(!$sid) die("Invalid SKU Item");
		if(BRANCH_CODE == "HQ") $bid = $form['bid'];
		else $bid = $sessioninfo['branch_id'];
		
		// load Quotation Cost from the rest of the vendor got set it
		$vd_qc_list = array();
		$q1 = $con->sql_query("select sivqc.*, v.code as vd_code, v.description as vd_desc 
							   from sku_items_vendor_quotation_cost sivqc
							   left join vendor v on v.id = sivqc.vendor_id
							   where branch_id = ".mi($bid)." and sku_item_id = ".mi($sid));

		while($r = $con->sql_fetchassoc($q1)){
			$vd_qc_list[$r['vendor_id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("data", $vd_qc_list);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_sku_summary.quotation_cost_list.tpl');
		
		print json_encode($ret);
	}
}

$MASTERFILE_SKU_SUMMARY = new MASTERFILE_SKU_SUMMARY('SKU Summary');
?>