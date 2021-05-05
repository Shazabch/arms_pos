<?php
/*
10/29/2014 9:46 AM Justin
- Enhanced to move the retrieve gst info function to functions.php

1/9/2015 12:26 PM Justin
- Enhanced to have privilege checking.

Justin
- Fix price calculation bug.

3/4/2015 4:29 PM Andy
- Change to limit user can only choose the level 3 category.

3/5/2015 3:41 PM Andy
- Fix wrong inclusive tax checking.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

4:53 PM 3/17/2015 Andy
- Change to only show active dept and cat.

3/20/2015 2:36 PM Justin
- Enhanced to have Download items into CSV format feature.

3/21/2015 1:51 PM Justin
- Enhanced to always set batch price change as approved once it is saved.
- Enhanced to limit the query for picking up maximum 5,000 items.

3/24/2015 9:48 AM Justin
- Bug fixed on showing mysql errors when counting items.
- Enhanced to set no time limit while saving items into Batch Price Change.
- Enhanced to have mprice selection.

3/25/2015 10:17 AM Justin
- Bug fixed on MPrice filtering will cause system to pickup all MPrice while downloading to CSV if untick all.

3/27/2015 2:48 PM Justin
- Enhanced to have normal price choice on mprice.

3/30/2015 1:13 PM Justin
- Bug fixed on insertion of price and mprice history that some times will cause mysql error.

7/27/2015 4:35 PM Justin
- skip those already have batch price change

1/22/2016 4:28 PM Justin
- Bug fixed on selling price filter have gone wrongly.

5/17/2018 11:03 AM Andy
- Enhanced to have calculation method add or deduct.

5/18/2018 1:37 PM Andy
- Fixed arms user cant see the add / deduct option.
- Change to export as zip file, each category a csv.

2/14/2020 3:25 PM William
- Enhanced to Change log type "GST PRICE WIZARD" to "GST_PRICE_WIZARD".
*/
include("include/common.php");
$maintenance->check(241);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_SKU_UPDATE_PRICE') && !privilege('MST_SKU_UPDATE_FUTURE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE_PRICE or MST_SKU_UPDATE_FUTURE_PRICE', BRANCH_CODE), "/index.php");
//ini_set("display_errors",1);
ini_set("memory_limit", "1024M");
$MAXIMUM_ITEMS = 5000;
$smarty->assign("MAXIMUM_ITEMS", $MAXIMUM_ITEMS);

class GST_PRICE_WIZARD extends Module{
	var $file_folder = "tmp/GST_PRICE_WIZARD";
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
		
		if(!is_dir($this->file_folder))	check_and_create_dir($this->file_folder);
		
		$files = scandir($this->file_folder);
		for($i=2; $i<count($files); $i++) {
			if(strtotime("-1 week") > filemtime($this->file_folder."/".$files[$i])) {
				unlink($this->file_folder."/".$files[$i]);
			}
		}
		
		if($sessioninfo['id'] != 1){
			$con->sql_query("select is_arms_user from user where id=".mi($sessioninfo['id']));
			$tmp = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		
		$smarty->assign('is_arms_user', ($tmp['is_arms_user'] ==1 || $sessioninfo['id'] == 1));

 		parent::__construct($title);
	}

	function _default(){
		global $con, $smarty, $sessioninfo;
		
		$this->init_selection();
		$vendor_list = $brand_list = $brand_groups = $st_list = array();
		// load vendor info
		$q1 = $con->sql_query("select *
							   from vendor 
							   where active=1
							   order by code, description");
		while($r = $con->sql_fetchassoc($q1)){
			$vendor_list[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("vendor_list", $vendor_list);
		
		// load brand info
		$q1 = $con->sql_query("select *
							   from brand 
							   where active=1
							   order by code, description");
		while($r = $con->sql_fetchassoc($q1)){
			$brand_list[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("brand_list", $brand_list);
		
		// load brand group info
		$brand_groups = get_brand_group();
		$smarty->assign("brand_groups", $brand_groups);

		$q1 = $con->sql_query("select * from sku_type order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$st_list[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("st_list", $st_list);
		
		// dept / category list
		/*$q1 = $con->sql_query("select line.id as line_id, line.description as line_name, dept.id as dept_id, dept.description as dept_name, c.id as cat_id, c.description as cat_name
from category c
left join category dept on dept.id=c.department_id
left join category_cache cc on cc.category_id=c.id
left join category line on line.id=cc.p1
where c.level=3 and c.active=1 and dept.active=1
order by line_name, dept_name, cat_name");
		$dept_cat_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$dept_cat_list[$r['dept_id']]['dept_name'] = $r['dept_name'];
			$dept_cat_list[$r['dept_id']]['line_name'] = $r['line_name'];
			$dept_cat_list[$r['dept_id']]['cat_list'][$r['cat_id']]['name'] = $r['cat_name'];
		}
		$con->sql_freeresult($q1);
		//print_r($dept_cat_list);
		$smarty->assign("dept_cat_list", $dept_cat_list);*/
		$q1 = $con->sql_query("select c.id,  c.root_id, c.level, c.code, c.description, c.department_id
			from category c
			where c.level<=3
			order by c.level, c.description");
		$cat_data = array();
		while($r = $con->sql_fetchassoc($q1)){
			// get total sku count
			/*if($r['level'] == 3){
				$con->sql_query("select count(*) as c
					from sku_items si 
					join sku on sku.id=si.sku_id
					join category_cache cc on cc.category_id=sku.category_id
					where cc.p3=");
			};*/
			
			// store cat details
			$cat_data['list'][$r['id']] = $r;
			
			// store cat tree
			$cat_data['tree'][$r['root_id']][$r['id']] = $r['id'];
		}
		$con->sql_freeresult($q1);
		
		if($cat_data){
			// Get SKU Count
			foreach($cat_data['tree'][0] as $line_id){	// Loop LINE
				$line_sku_count = 0;
				if($cat_data['tree'][$line_id]){
				foreach($cat_data['tree'][$line_id] as $dept_id){	// Loop DEPARTMENT
					$dept_sku_count = 0;
					if($cat_data['tree'][$dept_id]){	// This Department got 3rd level Category
						foreach($cat_data['tree'][$dept_id] as $cat_id){	// Loop 3rd Level Category
							// get sku count from 3rd level
							$con->sql_query("select count(*) as c
								from sku_items si 
								join sku on sku.id=si.sku_id
								join category_cache cc on cc.category_id=sku.category_id
								left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
								join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) and output_gst.rate>0
								where cc.p3=".mi($cat_id)." and si.active=1 and if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes' and ifnull(sip.price, si.selling_price) > 0");
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							$cat_data['list'][$cat_id]['sku_count'] = $tmp['c'];
							$dept_sku_count+=$tmp['c'];
							
							// Delete from category tree if this cat if empty sku
							if($cat_data['list'][$cat_id]['sku_count'] == 0){
								unset($cat_data['list'][$cat_id], $cat_data['tree'][$dept_id][$cat_id]);
							}
						}						
					}
					
					// 
					// get sku count directly at 2nd level
					$con->sql_query("select count(*) as c
							from sku_items si 
							join sku on sku.id=si.sku_id
							join category_cache cc on cc.category_id=sku.category_id
							left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
							join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) and output_gst.rate>0
							where sku.category_id=".mi($dept_id)." and si.active=1 and if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes' and ifnull(sip.price, si.selling_price) > 0");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$cat_data['list'][$dept_id]['sku_count'] += $tmp['c'];
					
					$dept_sku_count+=$tmp['c'];
					
					
					// Total SKU Count by Department
					$cat_data['list'][$dept_id]['sku_count'] = $dept_sku_count;
					
					$line_sku_count += $dept_sku_count;
					
					// Delete from category tree if this cat if empty sku
					if($cat_data['list'][$dept_id]['sku_count'] == 0){
						unset($cat_data['list'][$dept_id], $cat_data['tree'][$line_id][$dept_id]);
					}
				}
				}
				// Total SKU Count by Line
				$cat_data['list'][$line_id]['sku_count'] = $line_sku_count;
				
				// Delete from category tree if this cat if empty sku
				if($cat_data['list'][$line_id]['sku_count'] == 0){
					unset($cat_data['list'][$line_id], $cat_data['tree'][0][$line_id]);
				}
			}
		}
		//print_r($cat_data);
		$smarty->assign("cat_data", $cat_data);
		
		//$smarty->assign("form", $form);
	    $this->display();
	}
	
	function init_selection(){
		global $con, $smarty;
		
		// load gst list
		/*$q1 = $con->sql_query("select * from gst where active=1 and type='supply' order by code");
		
		while($r = $con->sql_fetchassoc($q1)){
			$gst_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("gst_list", $gst_list);*/
		$q1 = $con->sql_query("select * from branch where active=1 order by sequence, code");
		
		while($r = $con->sql_fetchassoc($q1)){
			$branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branch_list", $branch_list);
	}
	
	function ajax_search_items($is_export=false){
		global $con, $smarty, $sessioninfo, $config, $LANG, $MAXIMUM_ITEMS;
		set_time_limit(0);

		$form = $_REQUEST;
		/*if($form['category_id']){
			$cat_info = get_category_info($form['category_id']);
		}*/
		//print_r($form);exit;
		if(!$form['cat_id_list']){
			if($is_export){
				js_redirect($LANG['GST_PRICE_WIZARD_INVALID_CAT'], "/masterfile_gst.price_wizard.php");
			}else{
				$ret = array();
				$ret['failed_reason'] = $LANG['GST_PRICE_WIZARD_INVALID_CAT'];
				print json_encode($ret);
			}
		}else{
			$cat_filter = array();
			foreach($form['cat_id_list'] as $cat_id){
				$cat_lv = $form['cat_level_info'][$cat_id];
				$cat_filter[] = "cc.p".$cat_lv."=".$cat_id;
			}
			//$filters[] = "(".join(' or ', $cat_filter).")";
			
		}
		/*if(!$form['category_id'] || !$cat_info){
			if($is_export){
				js_redirect($LANG['GST_PRICE_WIZARD_INVALID_CAT'], "/masterfile_gst.price_wizard.php");
			}else{
				$ret = array();
				$ret['failed_reason'] = $LANG['GST_PRICE_WIZARD_INVALID_CAT'];
				print json_encode($ret);
			}
			exit;
		}*/
		
		if(!$form['mprice']){
			if($is_export){
				js_redirect($LANG['GST_PRICE_WIZARD_INVALID_CAT'], "/masterfile_gst.price_wizard.php");
			}else{
				$ret = array();
				$ret['failed_reason'] = $LANG['GST_PRICE_WIZARD_INVALID_MPRICE'];
				print json_encode($ret);
			}
			exit;
		}
		
		$calculate_method = 'add';
		if($form['calculate_method']){
			$calculate_method = trim($form['calculate_method']);
		}
		
		// load gst rate list (type=supply)
		$q1 = $con->sql_query("select * from gst where active=1 and type = 'supply'");

		$output_tax_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$output_tax_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		// load rounding condition from gst settings
		$q1 = $con->sql_query("select * from gst_settings where setting_name = 'sp_rounding_condition'");
		$gst_setting_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("gst_setting_info", $gst_setting_info);
		
		/*if($form['category_id'] > 0){
			//$filters[] = " (cat.id = ".mi($form['category_id']). " or cat.tree_str like ".ms('%('.$form['category_id'].')%') . ")";
			$filters[] = "cc.p".mi($cat_info['level'])."=".mi($form['category_id']);
		}*/
		
		if($form['brand_id']!=''){
			$filters[] = "sku.brand_id in (".join(',',process_brand_id($form['brand_id'])).")";
		}

		if($form['vendor_id']) $filters[] = "sku.vendor_id = ".mi($form['vendor_id']);

		if($form['sku_type']) $filters[] = "sku.sku_type=".ms($form['sku_type']);

		// skip those already have batch price change
		// $filters[] = " si.id not in (select sku_item_id from sku_items_future_price_items where sku_item_id=si.id)";
		$filters[] = "if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit', cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))='yes'";
		$filters[] = "si.active=1 and ifnull(sip.price, si.selling_price) > 0";
		if($filters) $filter = " where ".join(" and ", $filters);
		
		// Create File Prefix Name
		$con->sql_query("select * from branch where code='HQ'");
		$HQ = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($is_export){
			$times = time();
			$file_name_prefix = BRANCH_CODE." - ".$HQ['description'];
		}
		
		$sql_format = "select si.*, ifnull(sip.price, si.selling_price) as selling_price, sku.category_id, sku.mst_output_tax, sku.mst_inclusive_tax, output_gst.rate as gst_rate
							   from sku 
							   left join sku_items si on si.sku_id = sku.id
							   left join category cat on cat.id = sku.category_id
							   left join category_cache cc on cc.category_id=cat.id
							   left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($sessioninfo['branch_id'])."
							   join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax)) and output_gst.rate>0
							   ".$filter." %s
							   order by sku.category_id";
							   
		$total_row_count = 0;
		foreach($form['cat_id_list'] as $cat_id){
			$cat_info = get_category_info($cat_id);
			
			$cat_filter = "cc.p".$cat_info['level']."=".$cat_id;
			
			$sql = sprintf($sql_format, " and ".$cat_filter);
			
			$q1 = $con->sql_query($sql);
			$curr_count = $con->sql_numrows($q1);
			$items_count = $total_row_count+$curr_count;
			if(!$is_export && $items_count > $MAXIMUM_ITEMS){	// reach max items, stop
				$reached_maximum_items = 1;
				break;
			}
			
			if($is_export){
				$file_name = $file_name_prefix." - ".$cat_info['description']."_".$times.".csv";
				$fp = fopen($this->file_folder. "/".$file_name, 'w');
			}
			
			while($r = $con->sql_fetchassoc($q1)){			
				// sum up the gst amount
				if($calculate_method == 'deduct'){
					$proposed_selling_price = round($r['selling_price'] / ($r['gst_rate'] + 100) * 100, 2);
				}else{
					$proposed_selling_price = $r['selling_price'] + (round($r['selling_price'] * $r['gst_rate'] / 100, 2));		
				}
				
				$r['proposed_selling_price'] = process_gst_sp_rounding_condition($proposed_selling_price);
				if($r['proposed_selling_price'] == 0) $r['proposed_selling_price'] = $r['selling_price'];
		
				if($is_export && $form['mprice']['normal'] && $r['proposed_selling_price'] > 0){
					$row = array();
					$row['si_code'] = $r['sku_item_code'];
					$row['type'] = "normal";
					$row['selling_price'] = $r['proposed_selling_price'];
					
					$tmp_row = array($row['si_code'], $row['type'], $row['selling_price']);
					fputcsv($fp, $tmp_row);
				}
				
				// stock balance
				if(!$is_export){
					$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($sessioninfo['branch_id'])." and sku_item_id=".mi($r['id']));
					$r['stock_bal'] = $con->sql_fetchfield('qty');
					$con->sql_freeresult($sql);
				}
								
				foreach($config['sku_multiple_selling_price'] as $s){
					if(!$form['mprice'][$s]) continue;
					
					// get multiple selling price
					$q2 = $con->sql_query("select * from sku_items_mprice where branch_id = ".mi($sessioninfo['branch_id'])." and sku_item_id = ".mi($r['id'])." and type = ".ms($s));
					
					if($con->sql_numrows($q2)){
						while($r1 = $con->sql_fetchassoc($q2)){
							$r['mprice'][$s] = $r1['price'];
							
							// sum up the gst amount
							$tmp_proposed_mprice = $r['mprice'][$s] + round($r['mprice'][$s] * $r['gst_rate'] / 100, 2);
							
							if($calculate_method == 'deduct'){
								$tmp_proposed_mprice = round($r['mprice'][$s] / ($r['gst_rate'] + 100) * 100, 2);
							}else{
								$tmp_proposed_mprice = $r['mprice'][$s] + round($r['mprice'][$s] * $r['gst_rate'] / 100, 2);	
							}
				
							$proposed_mprice = process_gst_sp_rounding_condition($tmp_proposed_mprice);
							if($proposed_mprice == 0) $proposed_mprice = $r1['price'];
							$r['proposed_mprice'][$s] = $proposed_mprice;
						}
					}else{
						$r['mprice'][$s] = $r['selling_price'];
						$r['proposed_mprice'][$s] = $r['proposed_selling_price'];
					}
					$con->sql_freeresult($q2);
					
					if($is_export && $r['proposed_mprice'][$s] > 0){
						$row = array();
						$row['si_code'] = $r['sku_item_code'];
						$row['type'] = $s;
						$row['selling_price'] = $r['proposed_mprice'][$s];
						
						$tmp_row = array($row['si_code'], $row['type'], $row['selling_price']);
						fputcsv($fp, $tmp_row);
					}
				}
				
				if(!$is_export) $items[] = $r;
			}
			$con->sql_freeresult($q1);
			
			if($is_export){
				if($fp){
					fclose($fp);
					unset($fp);
				}
			}
		}
		
		if(!$is_export){	// Display
			$ret = array();
			$ret['ok'] = 1;
			
			if($reached_maximum_items){
				$ret['reached_maximum_items'] = 1;
			}else{
				$smarty->assign("form", $form);
			$smarty->assign("items", $items);
				$ret['html'] = $smarty->fetch("masterfile_gst.price_wizard.items.tpl");
			}
			
			print json_encode($ret);
			exit;
		}else{	// Export
			$parent_zip = $file_name_prefix."_".$times;
			exec("cd " . $this->file_folder."; zip -9 \"$parent_zip.zip\" *$times.csv");
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=\"$parent_zip.zip\"");
			readfile($this->file_folder."/$parent_zip.zip");
			log_br($sessioninfo['id'], 'GST_PRICE_WIZARD', 0, "Export SKU to ZIP File Format");
		}
	}
	
	function save(){
		global $con, $smarty, $sessioninfo, $config;
		set_time_limit(0);
		
		$form=$_REQUEST;
		$ret = array();
		$is_updated = false;
		unset($form['a'], $form['save']);
		
		$err = $this->validate();
		if(count($err) > 0){
			/*$smarty->assign("form", $form);
			$smarty->assign("err", $err);
			$smarty->display();*/
			//$ret['failed_reason'] = "* ".join($err, "\n* ");
			$smarty->assign("err", $err);
			$smarty->assign("form", $form);
			$this->init_selection();
			$this->display();
			exit;
		}
		

		if($form['type'] == "change_price"){ // user do regular price change
			// set branches list
			$branches = array();
			if(BRANCH_CODE == "HQ"){
				$branches = $form['effective_branches'];
			}else{
				$branches[] = $sessioninfo['branch_id'];
			}
			
			foreach($form['price'] as $sid=>$price_type_list){
				$sku_changed = 0;
				foreach($price_type_list as $price_type=>$new_sp){
					foreach($branches as $bid){
						if($price_type == "normal"){ // normal price
							// check and update normal selling price
							$q1 = $con->sql_query("select if(sip.price is null,si.selling_price,sip.price) as price, 
												   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code, 
												   sku.default_trade_discount_code 
												   from sku_items si
												   left join sku on sku_id = sku.id 
												   left join sku_items_price sip on sip.sku_item_id = si.id and branch_id = ".mi($bid)." 
												   where si.id = ".mi($sid));
							$price_info = $con->sql_fetchassoc($q1);
							$con->sql_freeresult($q1);
							
							if($new_sp == $price_info['price']) continue;
							$sku_changed = 1;
							
							// get last cost from GRN
							$prm = array();
							$prm['branch_id'] = $bid;
							$prm['sku_item_id'] = $sid;
							$temp = $this->get_last_cost($prm);
							
							$upd = array();
							$upd['branch_id'] = $bid;
							$upd['sku_item_id'] = $sid;
							$upd['price'] = $new_sp;
							$upd['cost'] = $temp['cost'];
							$upd['trade_discount_code'] = $price_info['trade_discount_code'];
							$upd['source'] = $temp['source'];
							$upd['user_id'] = $sessioninfo['id'];
							$upd['last_update'] = $upd['added'] = "CURRENT_TIMESTAMP";
							
							// insert price history
							$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id", "added")));
							
							// replace into normal selling price
							$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "last_update")));
							
							// capture log
							log_br($sessioninfo['id'], "GST_PRICE_WIZARD", 0, "Price Change for ".$form['sku_item_code'][$sid]." to ".mf($new_sp)." (Discount: ".$price_info['trade_discount_code'].", Branch ".get_branch_code($bid).")");
						}else{ // mprice
						
							// check if same price
							$q1 = $con->sql_query("select if(sim.price is null,si.selling_price,sim.price) as price, 
												   if(sim.price is null, sku.default_trade_discount_code, sim.trade_discount_code) as trade_discount_code
												   from sku_items si
												   left join sku on sku_id = sku.id 
												   left join sku_items_mprice sim on sim.sku_item_id = si.id and sim.branch_id = ".mi($bid)." and sim.type = ".ms($price_type)."
												   where si.id = ".mi($sid));
							$price_info = $con->sql_fetchassoc($q1);
							$con->sql_freeresult($q1);

							if ($new_sp == $price_info['price']) continue;
							$sku_changed = 1;
							
							$upd = array();
							$upd['branch_id'] = $bid;
							$upd['sku_item_id'] = $sid;
							$upd['type'] = $price_type;
							$upd['price'] = $new_sp;
							$upd['trade_discount_code'] = $price_info['trade_discount_code'];
							$upd['source'] = $temp['source'];
							$upd['user_id'] = $sessioninfo['id'];
							$upd['last_update'] = $upd['added'] = "CURRENT_TIMESTAMP";
							
							// insert mprice history
							$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "type", "price", "trade_discount_code", "user_id", "added")));
							
							// replace into multiple selling price
							$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "price", "trade_discount_code", "type", "last_update")));
							
							// capture log
							log_br($sessioninfo['id'], "GST_PRICE_WIZARD", 0, "Price Change for ".$form['sku_item_code'][$sid]." to ".mf($new_sp)." (Discount: ".$price_info['trade_discount_code'].", Type: ".$price_type.", Branch ".get_branch_code($bid).")");
						}
					}
				}
				// update sku items for counters to get the new price
				if($sku_changed) $con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = ".mi($sid));
			}
		}else{ // user do batch price change
			// setup the branch
			$effective_branches = array();
			if($form['effective_branches']){
				foreach($form['effective_branches'] as $bid=>$val){
					if($form['date_by_branch']){
						$effective_branches[$bid]['date'] = $form['branch_date'][$bid];
						$effective_branches[$bid]['hour'] = $form['branch_hour'][$bid];
						$effective_branches[$bid]['minute'] = $form['branch_minute'][$bid];
					}else $effective_branches[$bid] = $bid;
				}
			}
		
		
			// insert master information for future price
			$ins = array();
			$ins['branch_id'] = $sessioninfo['branch_id'];
			$ins['date'] = $form['date'];
			$ins['hour'] = $form['hour'];
			$ins['minute'] = $form['minute'];
			$ins['date_by_branch'] = $form['date_by_branch'];
			if($effective_branches) $ins['effective_branches'] = serialize($effective_branches);
			$ins['approved'] = $ins['status'] = $ins['active'] = 1;
			$ins['user_id'] = $sessioninfo['id'];
			$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
			
			$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
			$fp_id = $con->sql_nextid();
			
			$bid = $sessioninfo['branch_id'];
			
			$is_saved = false;
			foreach($form['price'] as $sid=>$price_type_list){
				foreach($price_type_list as $price_type=>$new_sp){
					if($price_type == "normal"){
						// check and update normal selling price
						$q1 = $con->sql_query("select if(sip.price is null,si.selling_price,sip.price) as price, 
											   if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
											   from sku_items si
											   left join sku on sku_id = sku.id 
											   left join sku_items_price sip on sip.sku_item_id = si.id and branch_id = ".mi($bid)." 
											   where si.id = ".mi($sid));
						$price_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
					}else{
						// check if same multiple selling price
						$q1 = $con->sql_query("select if(sim.price is null,si.selling_price,sim.price) as price, 
											   if(sim.price is null, sku.default_trade_discount_code, sim.trade_discount_code) as trade_discount_code
											   from sku_items si
											   left join sku on sku_id = sku.id 
											   left join sku_items_mprice sim on sim.sku_item_id = si.id and sim.branch_id = ".mi($bid)." and sim.type = ".ms($price_type)."
											   where si.id = ".mi($sid));
						$price_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
					}

					if ($new_sp == $price_info['price']) continue;
					$is_saved = true;
					// get last cost from GRN
					$prm = array();
					$prm['branch_id'] = $bid;
					$prm['sku_item_id'] = $sid;
					$temp = $this->get_last_cost($prm);
				
					$ins = array();
					$ins['branch_id'] = $bid;
					$ins['fp_id'] = $fp_id;
					$ins['sku_item_id'] = $sid;
					$ins['cost'] = $temp['cost'];
					$ins['selling_price'] = $price_info['price'];
					$ins['type'] = $price_type;
					$ins['trade_discount_code'] = $price_info['trade_discount_code'];
					//$ins['min_qty'] = $form['min_qty'][$id]; TO-DO list
					$ins['future_selling_price'] = $new_sp;
					
					$con->sql_query("select max(id) from sku_items_future_price_items where branch_id = ".mi($bid));
					$ins['id'] = $con->sql_fetchfield(0);
					$ins['id'] += 1;
					$con->sql_freeresult();
					
					$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
				}
			}
			
			// delete the batch price change since nothing to insert
			if(!$is_saved){
				$con->sql_query("delete from sku_items_future_price where id = ".mi($fp_id)." and branch_id = ".mi($bid));
			}else{
				$url .= "&fp_id=".mi($fp_id)."&bid=".mi($bid);
				log_br($sessioninfo['id'], 'GST_PRICE_WIZARD', 0, "Batch Price Change Saved: (ID#".$fp_id.", BRANCH_ID#".$bid.")");
			}
		}
		
		/*$ret['ok'] = 1;
		$ret['id'] = $form['id'];
		
		print json_encode($ret);*/
		
		header("Location: /masterfile_gst.price_wizard.php?save=1&type=".$form['type'].$url);
	}
	
	function validate(){
		global $LANG;
		$form=$_REQUEST;
		$err = array();

		if(count($form['effective_branches']) == 0){
			$err[] = $LANG['MST_FP_BRANCH_EMPTY'];
		}
		
		if($form['type'] == "future_price"){
			$curr_times = strtotime(date("Y-m-d H:i:s"));
			if($form['date_by_branch']){
				$invalid_branches = array();
				if($form['effective_branches']){
					foreach($form['effective_branches'] as $bid=>$val){
						$tmp['effective_branches'][$bid]['date'] = $form['branch_date'][$bid];
						$tmp['effective_branches'][$bid]['hour'] = $form['branch_hour'][$bid];
						$tmp['effective_branches'][$bid]['minute'] = $form['branch_minute'][$bid];
						$tmp_times = strtotime($form['branch_date'][$bid]." ".$form['branch_hour'][$bid].":".$form['branch_minute'][$bid].":00");
						if(!$form['branch_date'][$bid] || $tmp_times <= $curr_times) $invalid_branches[] = get_branch_code($bid);
					}
				}
					
				if($invalid_branches) $err[] = sprintf($LANG['MST_FP_DATE_INVALID'], "for ".join(", ", $invalid_branches));
				
				$form['effective_branches'] = $tmp['effective_branches'];
			}elseif(!$form['date'] || strtotime($form['date']." ".$form['hour'].":".$form['minute'].":00") <= $curr_times){
				$err[] = sprintf($LANG['MST_FP_DATE_INVALID'], "");
			}
		}
		
		return $err;
	}
	
	function get_last_cost($prm){
		global $con;
		
		// todo: if cost 0, find last cost from GRN/PO
		$form = array();
		
		$q1 = $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
		from grn_items
		left join uom on uom_id = uom.id
		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
		where grn_items.branch_id = ".mi($prm['branch_id'])." and grn.approved and sku_item_id=".mi($prm['sku_item_id'])." 
		having cost > 0
		order by grr.rcv_date desc limit 1");
		$c = $con->sql_fetchrow($q1);
		$con->sql_freeresult($q1);
		//print "using GRN $c[0]";
		if ($c){
			$form['cost'] = $c[0];
			$form['source'] = 'GRN';
		}
		
		if ($form['cost']==0){
			$q1 = $con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
			from po_items 
			left join po on po_id = po.id and po.branch_id = po.branch_id 
			where po.active and po.approved and po_items.branch_id = ".mi($prm['branch_id'])." and sku_item_id=".mi($prm['sku_item_id'])." 
			having cost > 0
			order by po.po_date desc limit 1");
			$c = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			//print "using PO $c[0]";
			if ($c)
			{
				$form['cost'] = $c[0];
				$form['source'] = 'PO';
			}
		}
		
		if ($form['cost']==0){
			$q1 = $con->sql_query("select cost_price from sku_items where id=".mi($prm['sku_item_id']));
			$c = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			//print "using MASTER $c[0]";
			if ($c)
			{
				$form['cost'] = $c[0];
				$form['source'] = 'MASTER SKU';
			}
		}
		
		return $form;
	}
	
	function export_csv(){
		$this->ajax_search_items(true);
	}
}

$GST_PRICE_WIZARD=new GST_PRICE_WIZARD("GST Price Wizard");

?>
