<?php
/*
REVISION HISTORY
================
11/16/2007 1:54:51 PM GARY
- add UOM in sku_items.

8/19/2009 6:03:03 PM Andy
- when add new item, first item become parent

3/19/2010 5:25:25 PM Andy
- Automatically show receipt description if user is the last approval
- Add HQ Cost at HQ SKU Application & SKU Approval if got config

6/1/2010 10:16:05 AM Alex
- Add weight,size,color,flavor,misc in sku_items

8/13/2010 10:02:21 AM Andy
- Add SKU without inventory. (control by category + sku and can be inherit)
- Add Fresh Market Sku. (control by category + sku and can be inherit)(Need Config)

6/13/2011 3:15:24 PM Andy
- Add "Allow decimal qty in GRN" at SKU.

6/20/2011 1:56:54 PM Andy
- Make scale type variable share in SKU application and SKU master file.

9/14/2011 11:31:06 AM Alex
- move function split_artno_size() to here

10/20/2011 10:14:49 AM Alex
- add filter uneeded space in function split_artno_size() 

10/25/2011 1:59:57 PM Andy
- Add "Allow FOC" and "FOC" checkbox for SKU Selling Price.

11/17/2011 4:59:30 PM Andy
- Add show/allow user to key in "link code" at SKU Application if got config.sku_application_show_linkcode

3/28/2012 5:54:12 PM Andy
- Add checking if sku group is create by matrix then parent will allow empty mcode/artno.

6/25/2012 2:48 PM Andy
- Add feature to allow customize SKU information.

7/2/2012 5:13:12 PM Justin
- Modified scale_type_list to new variables.

7/26/2012 3:23 PM Andy
- Add non-returnable feature.

9/4/2012 5:04 PM Drkoay
- add function save_sku_items_price(), run only $config['masterfile_update_sku_items_price_on_approve']=1
- add function get_last_cost(), copy from masterfile_sku_items_price.php

11/20/2012 6:10 PM Andy
- Fix after approve sku, matrix produce 1 empty sku with no mcode/artno. (due to no config sku_matrix_start_from_zero), use the first matrix item info for it.

12/6/2012 2:48 PM Andy
- Fix only skip matrix item if both artno and mcode are empty.

3/11/2013 6:04 PM Andy
- Change "Fix Price" to "Fixed Price".

5/17/2013 11:11 AM Justin
- Enhanced to manage additional description while config is turned on.

07/12/2013 04:34 PM Justin
- Enhanced to auto generate price change as if found having additional selling price.

8/12/2013 2:08 PM Andy
- Enhance to check maintenance version 208.

4/3/2014 2:28 PM Justin
- Enhanced to allow user maintain "PO Reorder Qty Min & Max" by SKU items.

5/26/2014 10:55 AM Justin
- Enhanced to have "HQ Selling".

6/3/2014 11:32 AM Justin
- Enhanced to have new ability that can upload images by PDF file.

6/20/2014 10:47 AM Justin
- Enhanced to have "Warranty Period" and "Internal Description (need privilege)" by item.

8/20/2014 5:55 PM DingRen
- add get_category_gst function
- add get_gst_settings

9/17/2014 2:12 PM Justin
- Enhanced to have show/hide gst settings for matrix table.

10/9/2014 3:52 PM Justin
- Enhanced to change the method of check gst settings table.

10/9/2014 4:19 PM Justin
- Enhanced to have GST calculation.

10/29/2014 9:46 AM Justin
- Enhanced to move the retrieve gst info function to functions.php
- Enhanced to add config checking while loading gst list.

3/5/2015 11:54 AM Andy
- Fix check_gst_status to use 'check_only_need_active'.

3/26/2015 6:12 PM Justin
- Bug fixed gst info capture wrongly while config is not turned on.

4/25/2017 4:47 PM Andy
- Raise maintenance version checking to 317.

5/12/2017 16:28 Qiu Ying
- Added function "check_receipt_desc_max_length"

5/17/2017 10:31 AM Justin
- Bug fixed on "Not Allow Discount" become unticked after SKU has been approved.

9/11/2017 1:41 PM Justin
- Enhanced to have new feature "Use Matrix".

2/1/2018 2:50 PM Justin
- Added new settings "Weight in KG".

10/22/2018 4:54 PM Justin
- Enhanced the module to compatible with new SKU Type.
- Modified to load SKU type from its own table instead from SKU.

5/29/2019 10:30 AM William
- Added new moq and pickup.

9/25/2019 9:46 AM Andy
- Change "save_sku_items" update is_parent method.

8/27/2019 11:35 AM Justin
- Enhanced to have model, width, height and length.

10/2/2019 9:24 AM Andy
- Raise maintenance version checking to 412.

11/13/2019 3:56 PM William
- Enhanced to move apply promotion photo to actual promotion file.

11/18/2019 3:10 PM William
- Fixed bug folder apply not auto generate when approved.
- Fixed bug wrong folder group num id.

2/17/2020 4:39 PM Andy
- Fixed php time "h:i" should be "H:i".

2/28/2020 2:25 PM William
- Enhanced to added new column "Marketplace Description".

3/2/2020 4:06 PM Andy
- Raise maintenance version checking to 447.

7/13/2020 5:32 PM William
- Enhanced to have checkbox "Prompt when scan at POS Counter".

11/9/2020 5:35 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.

11/13/2020 4:06 PM Andy
- Added "Recommended Selling Price" (RSP) feature.
*/
$maintenance->check(447);

$inherit_options = array('inherit'=>'Inherit (Follow Category)', 'yes'=>'Yes', 'no'=>'No');
$smarty->assign('inherit_options', $inherit_options);

$scale_type_list = array(-1 => 'Inherit (Follow SKU)', 0 => 'No', 1 => 'Fixed Price', 2 => 'Weighted');
$smarty->assign('scale_type_list', $scale_type_list);

$discount_inherit_options = array('inherit'=>'Inherit (Follow Category)', 'none'=>'No Discount', 'set'=>'Override Discount');
$smarty->assign('discount_inherit_options', $discount_inherit_options);

$category_point_inherit_options = array('inherit'=>'Inherit (Follow Category)', 'none'=>'No Point', 'set'=>'Override Reward Point');
$smarty->assign('category_point_inherit_options', $category_point_inherit_options);

load_sku_type_list();

if($config['enable_gst']){
	$gst_settings = check_gst_status(array('check_only_need_active'=>1));

	$q1 = $con->sql_query("select * from gst where active=1");

	while($r = $con->sql_fetchassoc($q1)){
		if($r['type'] == "purchase"){
			$input_tax_list[$r['id']] = $r;
		}else{
			$output_tax_list[$r['id']] = $r;
		}
	}
	$con->sql_freeresult($q1);
	$smarty->assign("gst_settings", $gst_settings);
	$smarty->assign("input_tax_list", $input_tax_list);
	$smarty->assign("output_tax_list", $output_tax_list);
}

if($config['enable_one_color_matrix_ibt']){
	//$clr_list = get_matrix_color();
	$clr_list = array(0=>"RED");
	$size_list = get_matrix_size();
	
	$smarty->assign("clr_list", $clr_list);
	$smarty->assign("size_list", $size_list);
}

/*$q1 = $con->sql_query("select * from return_policy order by title");

while($r = $con->sql_fetchassoc($q1)){
	$rp_list[$r['branch_id']][$r['id']] = $r;
}
$con->sql_freeresult($q1);
$smarty->assign("rp_list", $rp_list);*/

load_branch_list();
$sku_extra_info = construct_custom_sku_info();

function check_receipt_desc_max_length($tmp_desc){
	global $LANG;
	$ret = array();
	$have_unicode = false;
	$max_desc_length = 40;
	if(mb_strlen($tmp_desc,'UTF-8')!=strlen($tmp_desc)){
		$have_unicode = true;
		$max_desc_length = 13;
	}
	
	if(mb_strlen($tmp_desc,'UTF-8') > $max_desc_length){
		$ret["err"] = ($have_unicode?sprintf($LANG["SKU_EXCEED_MAX_LENGTH_NON_ALPHABET"], $max_desc_length):sprintf($LANG["SKU_EXCEED_MAX_LENGTH"], $max_desc_length));
	}
	return $ret;
}

// create real sku items from sku_id given
function save_sku_items($skuid)
{
	global $hqcon, $config;
	
	// get the current latest sku item code
    $hqcon->sql_query("select max(sku_item_code) from sku_items where sku_id = $skuid");
    if ($r = $hqcon->sql_fetchrow())
    {
        $itemcode = $r[0];
	}
	if (!$itemcode) $itemcode = sprintf(ARMS_SKU_CODE_PREFIX, $skuid)."0000";

	$iter = $hqcon->sql_query("select * from sku_apply_items where sku_id = $skuid and is_new = 1");
    $default_added = false;

    $item_count = 0;
    $got_use_matrix = false;
	while ($item = $hqcon->sql_fetchrow($iter))
	{
		$item['added'] = date("Y-m-d H:i:s");
	    $matrix = unserialize($item['product_matrix']);
	    $description_table = unserialize($item['description_table']);
	    $item['sku_apply_items_id'] = $item['id'];
	    $item['extra_info'] = unserialize($item['extra_info']);
	    
	    //  artno and mcode empty indicates individual code
		if ($item['artno'] == '' && $item['mcode'] == '')
			$share_artmcode = 0;
		else
		    $share_artmcode = 1;

	    if (is_array($matrix))
	    {
	    	$got_use_matrix = true;
	    	
			// insert default item 0000
			if (!$config['sku_matrix_start_from_zero'] && !$default_added)
			{
			    $default_added = true;
				$item['sku_item_code'] = $itemcode;
				$itemcode++;
				$item['selling_price'] = $matrix['tbprice'][1];
				$item['cost_price'] = $matrix['tbcost'][1];
				$item['hq_cost'] = $matrix['tbhqcost'][1];
				$item['hq_selling'] = $matrix['tbhqprice'][1];
				
				if (!$share_artmcode)
				{
					$item['artno'] = $matrix['tb'][1][1];
					$item['mcode'] = $matrix['tbm'][1][1];
			    }
				
				$si_field = array("sku_id","sku_apply_items_id", "sku_item_code", "artno", "mcode", "description", "receipt_description", "selling_price", "cost_price", "added", "hq_cost", "hq_selling");
				
				if($config['enable_gst']){
					$si_field[] = "input_tax";
					$si_field[] = "output_tax";
					$si_field[] = "inclusive_tax";
				}
				    
		   		$hqcon->sql_query("insert into sku_items " . mysql_insert_by_field($item, $si_field));
			    $item_count = 0;
			}

	        $description = $item['description'];
	        $rdescription = $item['receipt_description'];
			for ($r=1; $r<$matrix['rows']; $r++)
			{
				for ($c=1; $c<$matrix['cols']; $c++)
				{
					if ($matrix['tb'][$r][$c] == '' && $matrix['tbm'][$r][$c] == '' && !$share_artmcode) continue;
				    $item['sku_item_code'] = $itemcode;
				    $itemcode++;
				    $item['size'] = $matrix['tb'][$r][0];
				    $item['color'] = $matrix['tb'][0][$c];
				    $item['description'] = $description . " " . $matrix['tb'][$r][0] . " " . $matrix['tb'][0][$c];
				    $item['receipt_description'] = $rdescription . " " . $matrix['tb'][$r][0] . " " . $matrix['tb'][0][$c];
                    $item['selling_price'] = $matrix['tbprice'][$r];
                    $item['cost_price'] = $matrix['tbcost'][$r];
                    $item['hq_cost'] = $matrix['tbhqcost'][$r];
                    $item['hq_selling'] = $matrix['tbhqprice'][$r];
				    
				    if (!$share_artmcode)
					{
						$item['artno'] = $matrix['tb'][$r][$c];
						$item['mcode'] = $matrix['tbm'][$r][$c];
				    }
					
					$si_field = array("sku_id","sku_apply_items_id", "sku_item_code", "artno", "mcode", "description", "receipt_description", "selling_price", "cost_price", "added", "hq_cost", "size", "color", "hq_selling");
					
					if($config['enable_gst']){
						$si_field[] = "input_tax";
						$si_field[] = "output_tax";
						$si_field[] = "inclusive_tax";
					}
					
	   				$hqcon->sql_query("insert into sku_items " . mysql_insert_by_field($item, $si_field));
				    $item_count = 0;
				}
			}

		}
		else
		{
		    $item['weight'] = $description_table[1];
		    $item['size'] = $description_table[2];
		    $item['color'] = $description_table[3];
		    $item['flavor'] = $description_table[4];
		    $item['misc'] = $description_table[5];

			$si_field = array("sku_id","sku_apply_items_id", "sku_item_code", "artno", "mcode", "description", "receipt_description", "selling_price", "cost_price", "added",'ctn_1_uom_id','ctn_2_uom_id','open_price','decimal_qty','hq_cost','weight','size','color','flavor','misc', 'doc_allow_decimal','allow_selling_foc','selling_foc','link_code','cat_disc_inherit','category_point_inherit','category_disc_by_branch_inherit','category_point_by_branch_inherit', 'po_reorder_qty_min', 'po_reorder_qty_max','po_reorder_moq','scale_type', 'po_reorder_notify_user_id','hq_selling','internal_description','marketplace_description','not_allow_disc','weight_kg', 'model', 'width', 'height', 'length');
			if($config['sku_non_returnable'])	$si_field[] = 'non_returnable';
			
			if($config['sku_enable_additional_description']){
				$si_field[] = "additional_description";
				$si_field[] = "additional_description_print_at_counter";
				$si_field[] = "additional_description_prompt_at_counter";
			}
			
			if($config['enable_sn_bn'] && $item['sn_we']){
				$si_field[] = "sn_we";
				$si_field[] = "sn_we_type";
			}
			
			if($config['enable_gst']){
				$si_field[] = "input_tax";
				$si_field[] = "output_tax";
				$si_field[] = "inclusive_tax";
			}
			
			// RSP
			$si_field[] = "use_rsp";
			$si_field[] = "rsp_price";
			$si_field[] = "rsp_discount";
			
			// insert default item 0000
			if (!$config['sku_variety_start_from_zero'] && !$default_added)
			{
			    $default_added = true;
				$item['sku_item_code'] = $itemcode;
				$itemcode++;
		   		$hqcon->sql_query("insert into sku_items " . mysql_insert_by_field($item, $si_field));
		   		$new_sid = $hqcon->sql_nextid();
		   		if($config['enable_replacement_items']){
		   			if($item['ri_id'])  change_item_replacement_group($item['ri_id'], $new_sid);
		   		}
				$item_count = 0;

				// generate extra info table
				generate_sku_extra_info($new_sid, $item['extra_info']);
			}

			$si_field[] = "packing_uom_id";
			
		    // single variety
		    $item['sku_item_code'] = $itemcode;
		    $itemcode++;
	   		$hqcon->sql_query("insert into sku_items " . mysql_insert_by_field($item, $si_field));
	   		$new_sid = $hqcon->sql_nextid();
	   		if($config['enable_replacement_items']){
                if($item['ri_id'])  change_item_replacement_group($item['ri_id'], $new_sid);
			}
		   	
		    $item_count = 0;

    		// generate extra info table
			generate_sku_extra_info($new_sid, $item['extra_info']);
	   	}
		//check the sku item promotion photo
		$apply_sku_item_id = $item['id'];
		$group_num = ceil($apply_sku_item_id/10000);
		$promo_photo = "sku_photos/apply_promo_photo/".$group_num."/".$apply_sku_item_id."/1.jpg";
		//copy promotion photo to actual promotion file
		if(file_exists("$promo_photo")){
			$q1 = $hqcon->sql_query("select id from sku_items where sku_id=".mi($skuid)." and sku_apply_items_id=".mi($apply_sku_item_id));
			$r1 = $hqcon->sql_fetchrow($q1);
			$hqcon->sql_freeresult($q1);
			$sku_item_id = $r1['id'];
			$sku_group_num = ceil($sku_item_id/10000);
			check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo");
			check_and_create_dir($_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$sku_group_num);
			$sku_promo_photo_path = $_SERVER['DOCUMENT_ROOT']."/sku_photos/promo_photo/".$sku_group_num."/".$sku_item_id;
			check_and_create_dir($sku_promo_photo_path);
			copy("$promo_photo", "$sku_promo_photo_path/1.jpg");
			$hqcon->sql_query("update sku_items set got_pos_photo=1 where sku_id=".mi($skuid)." and sku_apply_items_id=".mi($apply_sku_item_id));
		}
   		$hqcon->sql_query("update sku_apply_items set is_new = 0 where id = $item[id]");
	}
	
	// check whether this sku already have parent
	$hqcon->sql_query("select id from sku_items where is_parent=1 and sku_id=$skuid") or die(mysql_error());
	$parent_si = $hqcon->sql_fetchassoc();
	$hqcon->sql_freeresult();
	
	if(!$parent_si){
        //$hqcon->sql_query("update sku_items set is_parent=1 where sku_id=$skuid and packing_uom_id=1 order by sku_item_code,id limit 1") or die(mysql_error());
		
		// Get which sku to set as parent
		$hqcon->sql_query("select si.id 
			from sku_items si 
			join uom on uom.id=si.packing_uom_id
			where si.sku_id=$skuid and uom.fraction=1 
			order by si.sku_item_code,si.id limit 1");
		$parent_si = $hqcon->sql_fetchassoc();
		$hqcon->sql_freeresult();
		
		$upd = array();
		$upd['is_parent'] = 1;
		
		// No sku can set as parent? mark the first as EACH and set as parent
		if(!$parent_si){
			$hqcon->sql_query("select si.id 
			from sku_items si 
			where si.sku_id=$skuid
			order by si.sku_item_code,si.id limit 1");
			$parent_si = $hqcon->sql_fetchassoc();
			$hqcon->sql_freeresult();
			
			// Force Mark as EACH
			$upd['packing_uom_id'] = 1;
		}
		
		$parent_sid = mi($parent_si['id']);
		
        $hqcon->sql_query("update sku_items set ".mysql_update_by_field($upd)."
		where sku_id=$skuid and id=$parent_sid") or die(mysql_error());
	}

	$upd = array();
	$upd['varieties'] = $item_count;
	$upd['active'] = 1;
	$upd['sku_code'] = sprintf(ARMS_SKU_CODE_PREFIX, $skuid);
	if($got_use_matrix)	$upd['allow_parent_empty_artno_mcode'] = 1;
	
	// update sku master record
	$hqcon->sql_query("update sku set ".mysql_update_by_field($upd)." where id = $skuid");
	
	if($config['masterfile_branch_enable_additional_sp']) generate_additional_sp($skuid);
}

function check_is_last_approval_of_sku_application($cat_id, $sku_type, $user_id, $branch_id){
    $params = array();
	$params['branch_id'] = $branch_id;
	$params['sku_type'] = $sku_type;
	$params['type'] = 'SKU_APPLICATION';
	$params['dept_id'] = get_department_id($cat_id);
	$params['user_id'] = $user_id;
	return is_last_approval($params);
}

function split_artno_size(&$item){
	$artno_code_arr=explode(" ",trim($item['artno']));
	
	$item['artno']=$artno_code_arr[0];
	unset($artno_code_arr[0]);

	//remove uneeded space
	foreach ($artno_code_arr as $key=> $art){	if (!$art)	unset($artno_code_arr[$key]);}
		
	$item['artsize']= join(" ", $artno_code_arr);
	unset($artno_code_arr);
}

function generate_sku_extra_info($sid, $extra_info){
	global $config, $con;
	
	if($config['sku_extra_info'] && $extra_info && $sid>0){
		$tmp = array();
		$tmp['sku_item_id'] = $sid;

		foreach($extra_info as $colname=>$colvalue){
			$tmp[$colname] = $colvalue;
		}
		$con->sql_query("replace into sku_extra_info ".mysql_insert_by_field($tmp));
		unset($tmp);
	}
}

function construct_custom_sku_info(){
	global $con, $config, $smarty;
	
	$sku_extra_info = array();
	
	if(!$config['sku_extra_info'])	return $sku_extra_info;
	
	foreach($config['sku_extra_info'] as $c => $r){
		$r['col'] = $c;
		
		$sku_extra_info[$c] = $r;
	}
	//print_r($sku_extra_info);
	
	if($smarty)	$smarty->assign('sku_extra_info', $sku_extra_info);
	return $sku_extra_info;
}

function save_sku_items_price($skuid=0){
	global $con, $config, $sessioninfo;
	
	$branches=array();
	$con->sql_query("select id, code, region from branch where (active=1 or (active=0 and inactive_change_price=1)) order by sequence,code");
	while($r = $con->sql_fetchassoc()){
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult();
		
	$con->sql_query("select si.*,s.default_trade_discount_code from sku_items si left join sku s on si.sku_id=s.id where si.sku_id = $skuid and si.active=1");
	while($r = $con->sql_fetchassoc()){
		$sku_items[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$currenttime=date('Y-m-d H:i:s');
	$currentdate=date('Y-m-d');
	
	foreach($sku_items as $sid=>$s){
		$con->sql_query("select * from `consignment_currency_table` where `from` <= ".mf($s['selling_price'])." and `to` >= ".mf($s['selling_price']));
		$c = $con->sql_fetchassoc();
		if($c) $c['currency']=unserialize($c['currency']);
		
		foreach($branches as $bid=>$b){			
			if($b['region']!="" && isset($config['masterfile_branch_region'][$b['region']])){
				
				$currency=$config['masterfile_branch_region'][$b['region']]['currency'];									
				if(isset($c['currency'][$currency]) && floatval($c['currency'][$currency]) > 0){
					
					$from=array();
					$form['user_id'] = $sessioninfo['id'];
					$form['branch_id'] = $bid;
					$form['source'] = 'SKU';
					$form['sku_item_id'] = $sid;
					$form['code']=$s['sku_item_code'];			
					$form['price'] = $c['currency'][$currency];
					$form['trade_discount_code'] = $s['default_trade_discount_code'];
					get_last_cost($form);
					
					$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id")));
					$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code")));
					$con->sql_query("update sku_items set lastupdate = ".ms($currenttime)." where id = $form[sku_item_id]");						
				}				
			}
		}
				
		if($config['sku_use_region_price'] && $config['masterfile_branch_region']){			
			foreach($config['masterfile_branch_region'] as $region_code=>$currency){
				if(isset($c['currency'][$currency['currency']]) && floatval($c['currency'][$currency['currency']]) > 0){
					$selling_price=mf($c['currency'][$currency['currency']]);
					$price_type=$s['default_trade_discount_code'];
					$upd = array();
					$upd['region_code'] = $region_code;
					$upd['sku_item_id'] = $sid;
					$upd['mprice_type'] = 'normal';
					$upd['price'] = $selling_price;
					$upd['trade_discount_code'] = $price_type;
					
					$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
									
					// update - sku_items_rprice_history
					$upd['date'] = $currentdate;
					$upd['user_id'] = $sessioninfo['id'];
					$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));					
				
					if (isset($config['sku_multiple_selling_price'])){
						foreach($config['sku_multiple_selling_price'] as $mprice_type){
							$upd = array();
							$upd['region_code'] = $region_code;
							$upd['sku_item_id'] = $sid;
							$upd['mprice_type'] = $mprice_type;
							$upd['price'] = $selling_price;
							$upd['trade_discount_code'] = $price_type;
							
							$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
							
							// update - sku_items_rprice_history
							$upd['date'] = $currentdate;
							$upd['user_id'] = $sessioninfo['id'];
							$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
						}
					}
				}
			}
		}		
	}		
}

function get_last_cost(&$form)
{
	global $con;
	
	// todo: if cost 0, find last cost from GRN/PO
	$form['cost'] = 0;
	
    $con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
from grn_items
left join uom on uom_id = uom.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where grn_items.branch_id = $form[branch_id] and grn.approved and sku_item_id=".ms($form['sku_item_id'])." 
having cost > 0
order by grr.rcv_date desc limit 1");
	$c = $con->sql_fetchrow();
    //print "using GRN $c[0]";
	if ($c)
	{
		$form['cost'] = $c[0];
		$form['source'] = 'GRN';
	}
	
	if ($form['cost']==0)
 	{
	 	$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
from po_items 
left join po on po_id = po.id and po.branch_id = po.branch_id 
where po.active and po.approved and po_items.branch_id = $form[branch_id] and sku_item_id=".ms($form['sku_item_id'])." 
having cost > 0
order by po.po_date desc limit 1");
 		$c = $con->sql_fetchrow();
 	    //print "using PO $c[0]";
 		if ($c)
		{
			$form['cost'] = $c[0];
			$form['source'] = 'PO';
		}
 	}
 	
	if ($form['cost']==0)
 	{
	 	$con->sql_query("select cost_price from sku_items where id=".ms($form['sku_item_id']));
 		$c = $con->sql_fetchrow();
 	    //print "using MASTER $c[0]";
 		if ($c)
		{
			$form['cost'] = $c[0];
			$form['source'] = 'MASTER SKU';
		}
 	}
}

function generate_additional_sp($sku_id){
	global $con, $sessioninfo;
	
	$branch_asp_list = $region_asp_list = $branch_list = array();

	// load branch additional selling price 
	$q1 = $con->sql_query("select * from branch_additional_sp");
	
	while($r = $con->sql_fetchassoc($q1)){
		$branch_asp_list[$r['branch_id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	// load region additional selling price
	$q1 = $con->sql_query("select * from branch_region_additional_sp");
	
	while($r = $con->sql_fetchassoc($q1)){
		$region_asp_list[$r['region_code']] = $r;
	}
	$con->sql_freeresult($q1);

	$q1 = $con->sql_query("select * from branch where active=1");
	
	while($r = $con->sql_fetchassoc($q1)){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	
	// get sku item list
	$q1 = $con->sql_query("select si.*, sku.default_trade_discount_code, sku.apply_by 
						   from sku_items si
						   left join sku on sku.id = si.sku_id
						   where si.sku_id = ".mi($sku_id));
	
	while($r = $con->sql_fetchassoc($q1)){
		if($r['apply_by']) $user_id = $r['apply_by'];
		else $user_id = $sessioninfo['id'];
		
		foreach($branch_list as $bid => $binfo){
			$region = $binfo['region'];
			if($branch_asp_list[$bid]){
				$extra_sp = $branch_asp_list[$bid];
			}else{
				$extra_sp = $region_asp_list[$region];
			}
			
			if(!$extra_sp['additional_sp']) continue; // found no additional selling price for this branch, skip
			
			$new_sp = mf($r['selling_price'])+mf($extra_sp['additional_sp']);
			
			// insert into price table
			$ins = array();
			$ins['branch_id'] = $bid;
			$ins['sku_item_id'] = $r['id'];
			$ins['last_update'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['added'])));
			$ins['price'] = $new_sp;
			$ins['cost'] = $r['cost_price'];
			$ins['trade_discount_code'] = $r['default_trade_discount_code'];
			
			$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
			
			// insert into price history table
			$ins = array();
			$ins['branch_id'] = $bid;
			$ins['sku_item_id'] = $r['id'];
			$ins['added'] = date("Y-m-d H:i:s", strtotime("+1 second", strtotime($r['added'])));
			$ins['price'] = $new_sp;
			$ins['cost'] = $r['cost_price'];
			$ins['source'] = "MASTER SKU";
			$ins['user_id'] = $user_id;
			$ins['trade_discount_code'] = $r['default_trade_discount_code'];

			$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
		}
	}
	$con->sql_freeresult($q1);
}

function pdf_handler($params){
	global $smarty, $config;
	
	if(!$params) return;

	$pdf_path = $params['pdf_path'];
	$image_path = $params['image_path'];
	// sku listing maybe?
	$sku_path = $params['sku_path'];
	//$counter = $params['counter'];
	
	//get the name of the file
	$image_path = basename($image_path, ".jpg");
	
	//remove all characters from the file name other than letters, numbers, hyphens and underscores
	$image_path = preg_replace("/[^A-Za-z0-9_-]/", "", $image_path);
		 
	//add the desired extension to the thumbnail
	$thumb = $image_path.".jpg";
	
	// check if got sku path assigned, need to combine it
	if($sku_path) $thumb = $sku_path."/".$thumb;
	
	// delete existing file
	if(file_exists($thumb))  @unlink($thumb);
	 
	//execute imageMagick's 'convert', setting the color space to RGB and size to 800px wide
	exec("convert \"{$pdf_path}[0]\" -flatten -colorspace RGB -geometry 800 $thumb");
	
	return $thumb;
}

?>
