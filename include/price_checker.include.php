<?php
/*
6/2/2010 4:57:24 PM Andy
- Fix bugs. (promotion din't check time)

6/25/2010 3:58:33 PM Andy
- Add show promotion period.

1/27/2011 3:26:53 PM Andy
- Fix shuttle SG15 price checker get the wrong price from other branch.

3/22/2011 11:09:06 AM Justin
- Added show photo in price checker
- Added filter of photo that doesn't exists.

3/23/2011 9:54:50 AM Andy
- Check if got define terminal then no need to get sku photos.

5/16/2011 5:46:39 PM Andy
- Add checking for sku photo path and change path to show the image. use_new_sku_photo_path() and get_image_path()

2/21/2012 3:45:34 PM Andy
- Enhance price checker to compatible with new category discount checking.

4/8/2013 12:31 PM Andy
- Fix member price cannot be show if no discount set for individual branch.

5/16/2013 5:10 PM Justin
- Enhanced to have new function that can check member.

1:15 PM 3/6/2015 Andy
- Enhanced to have gst calculation.
- Enhanced to always get the price inclusive gst as the final price shown.

11:56 AM 3/26/2015 Andy
- Enhance to manually call "get_global_gst_settings" when found $global_gst_settings is not set.

8/11/2016 9:50 AM Andy
- Fixed category non-member and member discount checking, should use double to check.

12/12/2016 5:47 PM Andy
- Fixed sku photo always return empty.

12/20/2016 10:13 AM Andy
- Enhanced to able to get remote photo.

1/20/2017 2:47 PM Andy
- Add show stock balance if using main server.

12/4/2017 5:46 PM Andy
- Fixed function "get_member_type_list" use the wrong mysql connection object.

12/5/2017 2:14 PM Andy
- Replace object $con with $mycon when found got passed $mycon mysql object.

7/9/2018 4:22 PM Andy
- Enhanced to check if code is 13 digits, if 13 digits not found any sku, cut the last digit and match first 12 digit.

7/23/2018 2:07 PM Andy
- Enhanced to search artno if POS Settings got turn on "artno_as_barcode".

7/31/2018 4:33 PM Andy
- Enhanced to check "Not Allow Discount".

1/11/2019 11:34 AM Andy
- Fixed Price Checker cannot show item last category.

9/13/2019 9:54 AM Justin
- Enhanced to pickup promotion time from and to for non member.

4/10/2020 3:58 PM Andy
- Enhanced to can check price by sku_item_id.

11/3/2020 1:10 PM Andy
- Fixed Promotion price branch code checking error.

1/5/2021 1:13 PM William
- Enhanced to return member_point and member_branch_point to when pass params "get_point_settings" to check_price function.

2/2/2021 5:08 PM Andy
- Change the checking code to use loop instead of mysql "or".

2/8/2021 1:42 PM Andy
- Added "sku_type" and "trade_discount_code".
*/
include_once("functions.php");
if(!function_exists('ms')){
    function ms($str,$null_if_empty=0)
	{
		if (trim($str) === '' && $null_if_empty) return "null";
		settype($str, 'string');
		$str = str_replace("'", "''", $str);
		$str = str_replace("\\", "\\\\", $str);
		return "'" . (trim($str)) . "'";
	}
}
function check_price($params){
	global $con, $global_gst_settings, $config, $terminal_use_branch_id, $sessioninfo;
	
	// Scanned Code
	$code = $params['code'];
	
	// MySQL Connection
	if($params['mysql_con']){
		$con = $mycon = $params['mysql_con'];
	}		
	else{
		$mycon = $con;
	}
	
	// Branch ID
	$branch_id = intval($params['branch_id']);
	$terminal_use_branch_id = $branch_id;	// a global variable for other functions to know what branch id currently is
	
	// Branch Code
	$mycon->sql_query("select code from branch where id=$branch_id");
	$branch_code = $mycon->sql_fetchfield(0);
	$mycon->sql_freeresult();
	
	$filter = array();
	$filter[] = "si.active=1";
	
	// SKU ITEM ID
	$sku_item_id = mi($params['sku_item_id']);
	
	if($sku_item_id>0){
		// Direct select sku using sku_item_id
		//$filter[] = "si.id=$sku_item_id";
		
		$code_checking_list = array("si.id=$sku_item_id");
	}else{
		// Search sku using barcode
		// Get POS Settings whether need to search art no
		$artno_as_barcode = mi(get_pos_settings_value($branch_id, 'artno_as_barcode'));
		
		// Check 13 Digits
		/*$filter_12_digits = array();
		$str_code2 = '';
		if(strlen($code)==13){
			$code2 = substr($code,0,12);
			
			$filter_12_digits[] = "si.mcode = ".ms($code2);
			$filter_12_digits[] = "si.link_code = ".ms($code2);
			$filter_12_digits[] = "si.sku_item_code = ".ms($code2);
			if($artno_as_barcode)	$filter_12_digits[] = "si.artno = ".ms($code2);
			
			//$str_code2 = " or si.mcode = ".ms($code2)." or si.link_code = ".ms($code2)." or si.sku_item_code = ".ms($code2);
			$str_code2 = join(' or ', $filter_12_digits);
		}
		
		$filter_or = array();
		$filter_or[] = "si.mcode = ".ms($code);
		$filter_or[] = "si.link_code = ".ms($code);
		$filter_or[] = "si.sku_item_code = ".ms($code);
		if($artno_as_barcode)	$filter_or[] = "si.artno = ".ms($code);
		
		if($str_code2)	$filter_or[] = $str_code2;
		$filter[] = "(".join(' or ', $filter_or).")";*/
		
		$code_checking_list = array();
		$code_checking_list[] = "si.sku_item_code = ".ms($code);
		$code_checking_list[] = "si.mcode = ".ms($code);
		$code_checking_list[] = "si.link_code = ".ms($code);
		if($artno_as_barcode)	$code_checking_list[] = "si.artno = ".ms($code);
		
		if(strlen($code)==13){
			$code2 = substr($code,0,12);
			
			$code_checking_list[] = "si.sku_item_code = ".ms($code2);
			$code_checking_list[] = "si.mcode = ".ms($code2);
			$code_checking_list[] = "si.link_code = ".ms($code2);
			if($artno_as_barcode)	$code_checking_list[] = "si.artno = ".ms($code2);
		}
	}
	
	// gst
	if($config['enable_gst']){
		if(!$global_gst_settings)	get_global_gst_settings();
		
		$prms = array();
		$prms['branch_id'] = $branch_id;
		$prms['date'] = date("Y-m-d");
		
		// check is under gst or not
		$is_under_gst = check_gst_status($prms);
	}
	
	
	$sort_rank = ",if(si.sku_item_code=".ms($code).",0, if(si.mcode=".ms($code).",1,if(si.link_code=".ms($code).",2,3))) as sort_rank";
	
	$current_time = date("H:i:s",time());
	
	foreach($code_checking_list as $code_check){
		$filter2 = $filter;
		$filter2[] = $code_check;
		$str_filter = join(' and ', $filter2);
		
		$sql = "select 
			si.*, 
			si.selling_price as master_price, 
			si.sku_apply_items_id, 
			sai.photo_count, 
			sip.price, 
			brand.description as brand, 
			branch.code as branch_code, 
			branch.ip as branch_ip,
			sku.category_id,
			c.category_disc,
			c.root_id as root_cat_id,
			c.tree_str,
			if(si.inclusive_tax='inherit', sku.mst_inclusive_tax, si.inclusive_tax) as sku_inclusive_tax,
			if(sip.price is null,si.selling_foc,sip.selling_price_foc) as selling_price_foc,
			uom1.code as ctn_1_uom_code,
			uom2.code as ctn_2_uom_code,
			si.got_pos_photo,
			sku.sku_type,
			if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code
			$sort_rank
			from sku_items si
				left join sku on sku_id = sku.id
				left join brand on sku.brand_id = brand.id
				left join branch on sku.apply_branch_id = branch.id
				left join sku_apply_items sai on si.sku_apply_items_id = sai.id
				left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = $branch_id
				left join category c on c.id=sku.category_id
				left join uom uom1 on uom1.id=si.ctn_1_uom_id
				left join uom uom2 on uom2.id=si.ctn_2_uom_id
			where $str_filter order by sort_rank, si.sku_item_code limit 1";
		$mycon->sql_query($sql);
		$sku = $mycon->sql_fetchrow();
		$mycon->sql_freeresult();
		
		// Found sku, break
		if($sku){
			$sku['matched_condition'] = $code_check;
			break;
		}
	}
	/*$str_filter = join(' and ', $filter);
	
	$mycon->sql_query("select 
	si.*, 
	si.selling_price as master_price, 
	si.sku_apply_items_id, 
	sai.photo_count, 
	sip.price, 
	brand.description as brand, 
	branch.code as branch_code, 
	branch.ip as branch_ip,
	sku.category_id,
	c.category_disc,
	c.root_id as root_cat_id,
	c.tree_str,
	if(si.inclusive_tax='inherit', sku.mst_inclusive_tax, si.inclusive_tax) as sku_inclusive_tax,
	if(sip.price is null,si.selling_foc,sip.selling_price_foc) as selling_price_foc,
	uom1.code as ctn_1_uom_code,
	uom2.code as ctn_2_uom_code,
	si.got_pos_photo
	$sort_rank
	from sku_items si
		left join sku on sku_id = sku.id
		left join brand on sku.brand_id = brand.id
		left join branch on sku.apply_branch_id = branch.id
		left join sku_apply_items sai on si.sku_apply_items_id = sai.id
		left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = $branch_id
		left join category c on c.id=sku.category_id
		left join uom uom1 on uom1.id=si.ctn_1_uom_id
		left join uom uom2 on uom2.id=si.ctn_2_uom_id
	where $str_filter order by sort_rank, si.sku_item_code limit 1");
	$sku = $mycon->sql_fetchrow();*/
	if(!$sku)   return array('error'=>"item_not_found");
	
	// get category info
	if(isset($params['get_cat_info']) && $params['get_cat_info'] && $sku['category_id']){
		$sku['cat_tree_info'] = get_cat_tree_info($sku['category_id'], $sku['tree_str'], true);
	}
	$member_type_list = get_member_type_list($params);
	
	$sku['category_disc_by_branch_inherit'] = unserialize($sku['category_disc_by_branch_inherit']);
	
	if(!$sku['sku_inclusive_tax'] || $sku['sku_inclusive_tax']=='inherit'){
		// get category inclusive / gst setting inclusive
		$sku['sku_inclusive_tax'] = get_category_gst('inclusive_tax', $sku['category_id']);
	}
	
	//if($config['enable_gst']){
	if($is_under_gst){
		// get output tax gst
		$sku['output_gst'] = get_sku_gst("output_tax", $sku['id']);
	}
	
	$photo_num = 0;
	if(!defined('TERMINAL')){
        // show photo in price check from SKU application photo
		if ($sku['photo_count']>0){
			$sku['image_path'] = get_branch_file_url($sku['branch_code'], $sku['branch_ip']);

			// do checking to skip those photo that doesn't existed
			/*for($i=1; $i<=$sku['photo_count']; $i++){
				$photo = "../".$sku['image_path']."sku_photos/".$sku['sku_apply_items_id']."/".$i.".jpg";

				if(file_exists($photo)){
					$photo_num = $i;
					break;
				}
			}
			$sku['photo_count'] = $photo_num;*/
			$sku['photos'] = get_sku_apply_item_photos($sku['sku_apply_items_id']);
		}

		// if found no photo in SKU application photo, try get photo attachment from individual sku item...
		if(!$sku['photos']){
			$sku['photos'] = get_sku_item_photos($sku['id'],$sku);
			// do checking to skip those photo that doesn't existed
			/*for($i=0; $i<count($sku['photos']); $i++){
				//$photo = "../".$sku['photos'][$i];
				$photo = $sku['photos'][$i];
				if(file_exists($photo)){
					$photo_num = $photo;
					break;
				}
			}
			$sku['photos'] = $photo_num;*/
		}
		
		// get the photo from remote server
		if(!$sku['photos'] && $params['hq_server_url'] && $params['get_remote_photo']){
			$url_to_get_photo = $params['hq_server_url']."/http_con.php?a=get_sku_item_photo_list&sku_item_id=".mi($sku['id'])."&sku_apply_items_id=".mi($sku['sku_apply_items_id'])."&SKIP_CONNECT_MYSQL=1";
			//print $url_to_get_photo;
			
			$tmp_photo_list = @file_get_contents($url_to_get_photo);
			$sku['photos'] = @unserialize($tmp_photo_list);				
		}
		
		if($sku['photos']){
			$sku['photo'] = $sku['photos'][0]; // only take the first photo
		}
	}
	//print_r($sku);

	// use master price if local price is not defined
	if ($sku['price']==0) $sku['price'] = $sku['master_price'];
	
	// mark current price before any discount
	$sku['default_price'] = $sku['price'];
	
	// got foc setting
	if($sku['selling_price_foc'])	$sku['price'] = 0;
	
	//////////////////////////////// check for category discount //////////////////////////
	
	// assign category price as current price before do any category discount
	$sku['mem_category_price'] = $sku['category_price'] = $sku['price'];
	
	// start perform category discount for non-member
	$mem_category_discount = $category_discount = '';
	$cat_id = $sku['category_id'];
	$member_type_check_done = false;
	
	// initial member type discount array
	$member_type_cat_discount = array();
	
	// no member type array, mark no need check
	if(!$member_type_list){
		$member_type_check_done = true;
	}
	
	if(!$sku['cat_disc_inherit'])	$sku['cat_disc_inherit'] = 'inherit';
	
	if(!$sku['not_allow_disc']){	// Allow Discount
		// sku mark override
		if($sku['cat_disc_inherit']=='set'){
			// check sku items category discount if array exists
			if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']) || isset($sku['category_disc_by_branch_inherit'][0]['member'])){
				// check sku items member type discount if member type array exists
				if($member_type_list){
					// loop for each member type
					foreach($member_type_list as $mtype){
						$tmp_disc = '';
						
						// this member type already get discount
						if(isset($member_type_cat_discount[$mtype]['discount']))	continue;
				
						// - check member type discount first	
						if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['member'])){
							// check member type discount - by branch
							if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['member'][$mtype])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][$branch_id]['member'][$mtype]);
							}
							
							// check member type discount - all branch
							if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member'][$mtype])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member'][$mtype]);
							}
							
							// check global member discount - by branch
							/*if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global'])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global']);
							}*/
						}
						
						// still cant get discount, get member discount
						if($tmp_disc===''){
							// check global member discount - by branch
							if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global'])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global']);
							}
							
							// check global member discount - all branch
							if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member']['global'])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member']['global']);
							}
						}
						
						// all branch
						/*if(isset($sku['category_disc_by_branch_inherit'][0]['member'])){
							// check member type discount - all branch
							if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member'][$mtype])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member'][$mtype]);
							}
							
							// check global member discount - all branch
							if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member']['global'])){
								$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member']['global']);
							}
						}*/
						
						
						// not empty, mean got set, use it
						if($tmp_disc!==''){
							$member_type_cat_discount[$mtype]['discount'] = $tmp_disc;
						}
					}
					
					// check if all member type already get discount, mark no need to check member type again
					if(count($member_type_cat_discount)==count($member_type_list))	$member_type_check_done = true;
				}
				
				// need to check member discount (not by member type)
				if($mem_category_discount === ''){
					$tmp_disc = '';
					
					// check global member discount - by branch
					if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global'])){
						$tmp_disc = trim($sku['category_disc_by_branch_inherit'][$branch_id]['member']['global']);
					}
					
					// check global member discount - all branch
					if($tmp_disc==='' && isset($sku['category_disc_by_branch_inherit'][0]['member']['global'])){
						$tmp_disc = trim($sku['category_disc_by_branch_inherit'][0]['member']['global']);
					}
					
					$mem_category_discount = $tmp_disc;
					
				}
				
			}
			
			// check sku items non-member category discount
			if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']) || isset($sku['category_disc_by_branch_inherit'][0]['nonmember'])){
				// try to get own branch discount first
				if(isset($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']['global'])){
					$category_discount = trim($sku['category_disc_by_branch_inherit'][$branch_id]['nonmember']['global']);
				}
				
				// get from all branch
				if($category_discount==='' && isset($sku['category_disc_by_branch_inherit'][0]['nonmember']['global'])){
					$category_discount = trim($sku['category_disc_by_branch_inherit'][0]['nonmember']['global']);
				}
			}
			
			// member discount less than non-member?
			if(mf($mem_category_discount)<mf($category_discount))	$mem_category_discount = $category_discount;
		}
		
		// check category discount
		while($sku['cat_disc_inherit']!='none' && (!$member_type_check_done || $mem_category_discount==='' || $category_discount==='') && $cat_id>0){
			// get category info
			$mycon->sql_query("select * from category where id=".ms($cat_id));
			$cat_info = $mycon->sql_fetchassoc();
			$mycon->sql_freeresult();
			
			// unserialize discount info
			$cat_info['category_disc_by_branch'] = unserialize($cat_info['category_disc_by_branch']);
			
			// non-member
			if($category_discount===''){
				// try to get own branch discount first
				if(isset($cat_info['category_disc_by_branch'][$branch_id]['nonmember']['global'])){
					$category_discount = trim($cat_info['category_disc_by_branch'][$branch_id]['nonmember']['global']);
				}
				
				// own branch not set on this level, check all branch
				if($category_discount===''){
					// if it is a new version, check for array
					if(isset($cat_info['category_disc_by_branch'][0]['nonmember']['global'])){
						$category_discount = trim($cat_info['category_disc_by_branch'][0]['nonmember']['global']);
					}else{
						// for old version, direct check column
						$category_discount = trim($cat_info['category_disc']);
					}
				}
			}
			
			
			// member
			if($mem_category_discount==='' || !$member_type_check_done){
				// try to get own branch discount first
				if(isset($cat_info['category_disc_by_branch'][$branch_id]['member']) || isset($cat_info['category_disc_by_branch'][0]['member'])){
					// need to check member type discount
					if(!$member_type_check_done){
						// check member type first
						if($member_type_list){
							foreach($member_type_list as $mtype){
								$tmp_disc = '';
								
								// this member type already get discount
								if(isset($member_type_cat_discount[$mtype]['discount']))	continue;
								
								// check member type discount - by branch
								if(isset($cat_info['category_disc_by_branch'][$branch_id]['member'][$mtype])){
									$tmp_disc = trim($cat_info['category_disc_by_branch'][$branch_id]['member'][$mtype]);
								}
								
								// check member type discount - all branch
								if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][0]['member'][$mtype])){
									$tmp_disc = trim($cat_info['category_disc_by_branch'][0]['member'][$mtype]);
								}
								
								// check global member discount - by branch
								if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][$branch_id]['member']['global'])){
									$tmp_disc = trim($cat_info['category_disc_by_branch'][$branch_id]['member']['global']);
								}
								
								// check global member discount - all branch
								if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][0]['member']['global'])){
									$tmp_disc = trim($cat_info['category_disc_by_branch'][0]['member']['global']);
								}
								
								// try use old method column
								if($tmp_disc===''){
									$tmp_disc = trim($cat_info['member_category_disc']);
								}
								
								// not empty, mean got set, use it
								if($tmp_disc!==''){
									$member_type_cat_discount[$mtype]['discount'] = $tmp_disc;
								}
							}
						}
						
						// check if all member type already get discount, mark no need to check member type again
						if(count($member_type_cat_discount)==count($member_type_list))	$member_type_check_done = true;
					}
					
					// need to check member discount (not by member type)
					if($mem_category_discount === ''){
						$tmp_disc = '';
						
						// check global member discount - by branch
						if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][$branch_id]['member']['global'])){
							$tmp_disc = trim($cat_info['category_disc_by_branch'][$branch_id]['member']['global']);
						}

						// check global member discount - all branch
						if($tmp_disc==='' && isset($cat_info['category_disc_by_branch'][0]['member']['global'])){
							$tmp_disc = trim($cat_info['category_disc_by_branch'][0]['member']['global']);
						}
						
						$mem_category_discount = $tmp_disc;
					}
				}
			}

			// get parent category id
			$cat_id = $cat_info['root_id'];
			
			// member discount less than non-member?
			if(mf($mem_category_discount)<mf($category_discount))	$mem_category_discount = $category_discount;
		}
	}
	
	
	
	
	
	// got gst
	//if($config['enable_gst']){	// this is the correct one, but due to need to match to counter, hv to use check is_under_gst
	if($is_under_gst){
		if($sku['sku_inclusive_tax']=='yes'){
			// is inclusive tax
			$sku['price_include_gst'] = $sku['price'];
			$sku['gst_amt'] = round($sku['price_include_gst'] / (100 + $sku['output_gst']['rate']) * $sku['output_gst']['rate'], 2);
			$sku['price_before_gst'] = round($sku['price_include_gst'] - $sku['gst_amt'], 4);
			
			// default price
			$sku['default_price_include_gst'] = $sku['default_price'];
			$sku['default_gst_amt'] = round($sku['default_price_include_gst'] / (100 + $sku['output_gst']['rate']) * $sku['output_gst']['rate'], 2);
			$sku['default_price_before_gst'] = round($sku['default_price_include_gst'] - $sku['gst_amt'], 4);
		}else{
			// is exclusive tax
			$sku['price_before_gst'] = $sku['price'];
			$sku['gst_amt'] = round($sku['price_before_gst'] * $sku['output_gst']['rate'] / 100, 2);
			$sku['price_include_gst'] = round($sku['price_before_gst'] + $sku['gst_amt'], 4);
			
			// default price
			$sku['default_price_before_gst'] = $sku['default_price'];
			$sku['default_gst_amt'] = round($sku['default_price_before_gst'] * $sku['output_gst']['rate'] / 100, 2);
			$sku['default_price_include_gst'] = round($sku['default_price_before_gst'] + $sku['default_gst_amt'], 4);
		}
		
		if(!$is_under_gst){
			// gst not yet started
			$sku['gst_amt'] = 0;	// remove all gst amt
			$sku['price_include_gst'] = $sku['price_before_gst'];
		}
		
		// price shown is always gst included
		$sku['mem_category_price'] = $sku['category_price'] = $sku['price'] = $sku['price_include_gst'];
		
		$sku['default_price'] = $sku['default_price_include_gst'];
	}
	
	if(!$sku['not_allow_disc']){	// Allow Discount
		// non-member category discount
		//$category_discount = 10;	// testing purpose
		$sku['category_disc'] = $category_discount;
		if($sku['category_disc']){
			if($is_under_gst){
				$sku['category_price'] = $sku['price_include_gst'] * (1 - doubleval($sku['category_disc'])/100);
			}else{
				// no gst
				$sku['category_price'] = $sku['price'] * (1 - doubleval($sku['category_disc'])/100);
			}
		}
						
		// member category discount
		//$mem_category_discount = 10;	// testing purpose
		$sku['mem_category_disc'] = $mem_category_discount;
		if($sku['mem_category_disc']){
			if($is_under_gst){
				$sku['mem_category_price'] = $sku['price_include_gst'] * (1 - doubleval($sku['mem_category_disc'])/100);
			}else{
				$sku['mem_category_price'] = $sku['price'] * (1 - doubleval($sku['mem_category_disc'])/100);
			}
		}
		
		/*if($sku['category_disc']!=''){  // this category got disc
			$sku['category_price'] = $sku['price'] * (1 - doubleval($sku['category_disc'])/100);
		}else{  // find until top category discount
			if($sku['root_cat_id']){
				$mycon->sql_query("select * from category where id=".$sku['root_cat_id']);
				while($root_cat = $mycon->sql_fetchrow()){    // continue to loop until no parent category
					if($root_cat['category_disc']!=''){
						$sku['category_disc'] = $root_cat['category_disc'];
						$sku['category_price'] = $sku['price'] * (1 - doubleval($sku['category_disc'])/100);
						break;
					}else{
						$mycon->sql_query("select * from category where id=".$root_cat['root_id']);
					}
				}
			}
		}*/
		
		// get promotion discount
		$mycon->sql_query("select p.title, p.date_from, p.date_to, p.time_from, p.time_to, pi.*, p.category_point_inherit, p.category_point_inherit_data
			from promotion p left join promotion_items pi on p.branch_id = pi.branch_id and p.id = pi.promo_id
			where
			promo_branch_id like '%\"".$branch_code."\"%' and
			pi.sku_item_id = $sku[id] and
			CURDATE() between date_from and date_to
			and CURTIME() between time_from and time_to
			and p.approved = 1 and p.active = 1
			");

		$member = array();
		$non_member = array();
		$memner_type_data = array();
		
		while($p=$mycon->sql_fetchrow())
		{
			$p['allowed_member_type'] = unserialize($p['allowed_member_type']);
			
			// testing purpose
			//$p['member_disc_a'] = 0; 
			//$p['non_member_disc_a'] = 0;	
			//$p['member_disc_p'] = '';
			//$p['non_member_disc_p'] = '1';
			// end testing purpose
			
			// member_disc_p	member_disc_a	non_member_disc_p	non_member_disc_a
			if ($p['member_disc_a']>0)
			{
				// selling at specified price
				$member_price = $p['member_disc_a'];
				if($is_under_gst && $sku['sku_inclusive_tax'] == 'no'){
					// need to add gst amt
					$gst_amt = round($member_price * $sku['output_gst']['rate'] / 100, 2);
					$member_price = round($member_price + $gst_amt, 2);
				}
			}
			elseif (strstr($p['member_disc_p'],'%'))
			{
				// discount by %
				$member_price = $sku['price'] * (1 - doubleval($p['member_disc_p'])/100);
			}
			else
			{
				// discount by certain value
				//if($config['enable_gst']){
				if($is_under_gst){
					// need to check discount after tax or not
					if($global_gst_settings['disc_after_sp']){
						// yes - just discount from price included tax
						$member_price = $sku['price']  - doubleval($p['member_disc_p']);
					}else{
						// no - need to discount from price before tax
						$member_price = $sku['price_before_gst']  - doubleval($p['member_disc_p']);
						if($is_under_gst){
							// got gst, need to add back the gst into final price
							$gst_amt = round($member_price * $sku['output_gst']['rate'] / 100, 2);
							$member_price = round($member_price + $gst_amt, 2);
						}
					}
				}else{
					$member_price = $sku['price']  - doubleval($p['member_disc_p']);
				}
			}
			$member_price = round($member_price,2);
			
			// non member
			if ($p['non_member_disc_a']>0)
			{
				// selling at specified price
				$non_member_price = $p['non_member_disc_a'];
				if($is_under_gst && $sku['sku_inclusive_tax'] == 'no'){
					// need to add gst amt
					$gst_amt = round($non_member_price * $sku['output_gst']['rate'] / 100, 2);
					$non_member_price = round($non_member_price + $gst_amt, 2);
				}
			}
			elseif (strstr($p['non_member_disc_p'],'%'))
			{
				// discount by %
				$non_member_price = $sku['price'] * (1 - doubleval($p['non_member_disc_p'])/100);
			}
			else
			{
				// discount by certain value
				//if($config['enable_gst']){
				if($is_under_gst){
					// need to check discount after tax or not
					if($global_gst_settings['disc_after_sp']){
						// yes - just discount from price included tax
						$non_member_price = $sku['price']  - doubleval($p['non_member_disc_p']);
					}else{
						// no - need to discount from price before tax
						$non_member_price = $sku['price_before_gst']  - doubleval($p['non_member_disc_p']);
						if($is_under_gst){
							// got gst, need to add back the gst into final price
							$gst_amt = round($non_member_price * $sku['output_gst']['rate'] / 100, 2);
							$non_member_price = round($non_member_price + $gst_amt, 2);
						}
					}
				}else{
					$non_member_price = $sku['price']  - doubleval($p['non_member_disc_p']);
				}
			}
			$non_member_price = round($non_member_price,2);
			
			if(isset($p['allowed_member_type']['member_type']) && $p['allowed_member_type']['member_type']){	// only for selected member type
				// only member type use this member price
				$member_type_price = $member_price;
				if ($member_type_price>$non_member_price) $member_type_price = $non_member_price;
				
				// other member use back non-member price
				$member_price = $non_member_price;
				
				if($member_type_price != $member_price){
					foreach($p['allowed_member_type']['member_type'] as $mtype=>$mt_r){
						$memner_type_data[$mtype][] = array('price'=>$member_type_price, 'limit'=>$p['member_limit'],'date_from'=>$p['date_from'], 'date_to'=>$p['date_to']);
					}
				}	
			}else{	// for all member
				if ($member_price>$non_member_price) $member_price = $non_member_price;
			}

			$member[] = $member_price;
			$non_member[] = $non_member_price;
			
			$member_data[] = array('price'=>$member_price, 'limit'=>$p['member_limit'],'date_from'=>$p['date_from'], 'date_to'=>$p['date_to'], 'category_point_inherit'=>$p['category_point_inherit'], 'category_point_inherit_data'=>$p['category_point_inherit_data']);
			$non_member_data[] = array('price'=>$non_member_price, 'date_from'=>$p['date_from'], 'date_to'=>$p['date_to'], 'time_from'=>$p['time_from'], 'time_to'=>$p['time_to'], 'category_point_inherit'=>$p['category_point_inherit'], 'category_point_inherit_data'=>$p['category_point_inherit_data']);
		}
	}
	
	$member_point_info = array();
	$non_member_point_info = array();
    
	// find lowest promotion member price
	if($member_data){   
		foreach($member_data as $r){
			if(!isset($sku['member_price'])||$sku['member_price']>$r['price']){
                $sku['member_price'] = $r['price'];
                $sku['member_limit'] = $r['limit'];
                if($r['date_from']&&$r['date_to']){
                    $sku['member_date_from'] = $r['date_from'];
                    $sku['member_date_to'] = $r['date_to'];
				}
				
				//get member point
				if($params['get_point_settings']){
					if($r['category_point_inherit'] == 'inherit'){
						$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
						if($category_point_data){
							unset($member_point);
							$member_point = $category_point_data['category_point'];
							if($category_point_data['category_member_point']){
								$member_point_info = $category_point_data['category_member_point'];
							}
						}
					}elseif($r['category_point_inherit'] == 'set'){  //use the member point
						unset($member_point);
						if(@unserialize($r['category_point_inherit_data']) != false){
							$category_point_inherit_data= unserialize($r['category_point_inherit_data']);
							if($member_type_list){
								foreach($member_type_list as $mtype){
									if($category_point_inherit_data[$mtype] == '' && $category_point_inherit_data['global']!= ''){
										$member_point_info[$mtype]['point']= $category_point_inherit_data['global'];
										$member_point_info[$mtype]['type']= 'promotion';
									}else{
										if($category_point_inherit_data[$mtype]!= ''){
											$member_point_info[$mtype]['point']= $category_point_inherit_data[$mtype];
											$member_point_info[$mtype]['type']= 'promotion';
										}
									}
								}
							}
						}
					}else{
						unset($member_point_info);
						$member_point = 0;
					}
				}
			}
		}
	}else  $sku['member_price'] = @min($member);

	// find lowest promotion non-member price
	if($non_member_data){
		foreach($non_member_data as $r){
			if(!isset($sku['non_member_price'])||$sku['non_member_price']>$r['price']){
                $sku['non_member_price'] = $r['price'];
                if($r['date_from']&&$r['date_to']){
                    $sku['non_member_date_from'] = $r['date_from'];
                    $sku['non_member_date_to'] = $r['date_to'];
                    $sku['non_member_time_from'] = $r['time_from'];
                    $sku['non_member_time_to'] = $r['time_to'];
				}
				
				//get non member point
				if($params['get_point_settings']){
					if($r['category_point_inherit'] == 'inherit'){
						$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
						if($category_point_data){
							$non_member_point = $category_point_data['category_point'];
							if($category_point_data['category_member_point']){
								$non_member_point_info = $category_point_data['category_member_point'];
							}
						}
					}elseif($r['category_point_inherit'] == 'set'){  //use the member point
						if(@unserialize($r['category_point_inherit_data']) != false){
							$category_point_inherit_data= unserialize($r['category_point_inherit_data']);
							if($member_type_list){
								foreach($member_type_list as $mtype){
									if($category_point_inherit_data[$mtype] == '' && $category_point_inherit_data['global']!= ''){
										$non_member_point_info[$mtype]['point']= $category_point_inherit_data['global'];
										$non_member_point_info[$mtype]['type']= 'promotion';
									}else{
										if($category_point_inherit_data[$mtype]!= ''){
											$non_member_point_info[$mtype]['point']= $category_point_inherit_data[$mtype];
											$non_member_point_info[$mtype]['type']= 'promotion';
										}
									}
								}
							}
						}
					}else{
						unset($non_member_point_info);
						$non_member_point = 0;
					}
				}
			}
		}
	}else	$sku['non_member_price'] = @min($non_member);
	
	// price by member type
	if($memner_type_data){
		foreach($memner_type_data as $mtype=>$m_data){
			foreach($m_data as $r){
				if(!isset($sku['member_type_price'][$mtype]) || $sku['member_type_price'][$mtype]>$r['price'] ){
	                $sku['member_type_price'][$mtype]['price'] = $r['price'];
	                if($r['date_from']&&$r['date_to']){
	                    $sku['member_type_price'][$mtype]['date_from'] = $r['date_from'];
	                    $sku['member_type_price'][$mtype]['date_to'] = $r['date_to'];
					}
				}
			}
			
			// member type price more then global member price? use global member price
			if($sku['member_type_price'][$mtype]['price'] > $sku['member_price']){
				unset($sku['member_type_price'][$mtype]);
			}
		}
	}
	
	if(!$sku['member_price']){
		$sku['member_price'] = $sku['price'];
		
		//get member point
		if($params['get_point_settings']){
			unset($member_point_info, $member_point);
			if($sku['category_point_inherit'] == 'inherit'){
				$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
				if($category_point_data){
					$member_point = $category_point_data['category_point'];
					if($category_point_data['category_member_point']){
						$member_point_info = $category_point_data['category_member_point'];
					}
				}
			}elseif($sku['category_point_inherit'] == 'set'){
				if(@unserialize($sku['category_point_by_branch_inherit']) != false){
					$sku_category_point_by_branch = array();
					$sku_category_point_by_branch_inherit = unserialize($sku['category_point_by_branch_inherit']);
					
					//get member point by branch
					if($sku_category_point_by_branch_inherit[$branch_id]){
						$sku_category_point_by_branch = $sku_category_point_by_branch_inherit[$branch_id];
					}else{
						if($sku_category_point_by_branch_inherit[0])  $sku_category_point_by_branch = $sku_category_point_by_branch_inherit[0];
					}
					
					if($sku_category_point_by_branch){
						foreach($member_type_list as $mtype){
							//if(isset($member_point_info[$mtype]['point']))	continue;
							if($sku_category_point_by_branch[$mtype] != ''){
								$member_point_info[$mtype]['point'] = $sku_category_point_by_branch[$mtype];
								$member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
							}else{
								if($sku_category_point_by_branch['global']!= ''){
									$member_point_info[$mtype]['point'] = $sku_category_point_by_branch['global'];
									$member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
								}
							}
						}
					}
				}
			}else{
				unset($member_point_info);
				$member_point = 0;
			}
		}
	}
	if(!$sku['non_member_price']){
		$sku['non_member_price'] = $sku['price'];
		
		//get non member point
		if($params['get_point_settings']){
			unset($non_member_point_info, $non_member_point);
			if($sku['category_point_inherit'] == 'inherit'){
				$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
				if($category_point_data){
					$non_member_point = $category_point_data['category_point'];
					if($category_point_data['category_member_point']){
						$non_member_point_info = $category_point_data['category_member_point'];
					}
				}
			}elseif($sku['category_point_inherit'] == 'set'){
				if(@unserialize($sku['category_point_by_branch_inherit']) != false){
					$sku_category_point_by_branch = array();
					$sku_category_point_by_branch_inherit = unserialize($sku['category_point_by_branch_inherit']);
					
					//get member point by branch
					if($sku_category_point_by_branch_inherit[$branch_id]){
						$sku_category_point_by_branch = $sku_category_point_by_branch_inherit[$branch_id];
					}else{
						if($sku_category_point_by_branch_inherit[0])  $sku_category_point_by_branch = $sku_category_point_by_branch_inherit[0];
					}
					
					if($sku_category_point_by_branch){
						foreach($member_type_list as $mtype){
							//if(isset($non_member_point_info[$mtype]['point']))	continue;
							if($sku_category_point_by_branch[$mtype] != ''){
								$non_member_point_info[$mtype]['point'] = $sku_category_point_by_branch[$mtype];
								$non_member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
							}else{
								if($sku_category_point_by_branch['global']!= ''){
									$non_member_point_info[$mtype]['point'] = $sku_category_point_by_branch['global'];
									$non_member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
								}
							}
						}
					}
				}
			}else{
				unset($non_member_point_info);
				$non_member_point = 0;
			}
		}
	}
	
	// compare category with promotion - non-member
	if($sku['category_price']<$sku['non_member_price']){
		// category price lower then promotion
		$sku['non_member_price'] = $sku['category_price'];
		// no more promo period sine it no longer use promo price
        unset($sku['non_member_date_from']);
        unset($sku['non_member_date_to']);
        unset($sku['non_member_time_from']);
        unset($sku['non_member_time_to']);
		
		//get member point
		if($params['get_point_settings']){
			unset($non_member_point_info, $non_member_point);
			$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
			if($category_point_data){
				$non_member_point = $category_point_data['category_point'];
				if($category_point_data['category_member_point']){
					$non_member_point_info = $category_point_data['category_member_point'];
				}
			}
		}
	}
	
	// compare category with promotion - member
	if($sku['mem_category_price']<$sku['member_price']){
		// category price lower then promotion
		$sku['member_price'] = $sku['mem_category_price'];
		
		//get member point
		if($params['get_point_settings']){
			unset($member_point_info, $member_point);
			$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
			if($category_point_data){
				$member_point = $category_point_data['category_point'];
				if($category_point_data['category_member_point']){
					$member_point_info = $category_point_data['category_member_point'];
				}
			}
		}
		// no more promo period sine it no longer use promo price
        unset($sku['member_date_from']);
        unset($sku['member_date_to']);
	}
	
	// calculate category discount - non-member
    /*if($sku['category_price']<$sku['price']){   
        $sku['price'] = $sku['category_price'];
        $sku['disc'] = intval(100-round($sku['price']/$sku['default_price'],2)*100);    
	}*/
	if($sku['price']<$sku['default_price']){
		$sku['disc'] = intval(100-round($sku['price']/$sku['default_price'],2)*100);    
	}
	
	if($sku['member_price']>$sku['non_member_price']){
		$sku['member_price'] = $sku['non_member_price'];
		
		if($params['get_point_settings']){
			unset($member_point_info, $member_point);
			$member_point_info = $non_member_point_info;
			if(isset($non_member_point)){
				$member_point = $non_member_point;
			}
		}
	}
		
	// calculate member price
	if($sku['price']<=$sku['member_price']){
        $sku['member_price'] = $sku['price'];
        // no more promo period sine it no longer use promo price
        unset($sku['member_date_from']);
        unset($sku['member_date_to']);
		
		//get member point
		if($params['get_point_settings']){
			unset($member_point_info, $member_point);
			if($sku['category_point_inherit'] == 'inherit'){
				$category_point_data= get_category_point($params, $member_type_list, $sku['category_id'], $sku['tree_str']);
				if($category_point_data){
					$member_point = $category_point_data['category_point'];
					if($category_point_data['category_member_point']){
						$member_point_info = $category_point_data['category_member_point'];
					}
				}
			}elseif($sku['category_point_inherit'] == 'set'){
				if(@unserialize($sku['category_point_by_branch_inherit']) != false){
					$sku_category_point_by_branch = array();
					$sku_category_point_by_branch_inherit = unserialize($sku['category_point_by_branch_inherit']);
					
					//get member point by branch
					if($sku_category_point_by_branch_inherit[$branch_id]){
						$sku_category_point_by_branch = $sku_category_point_by_branch_inherit[$branch_id];
					}else{
						if($sku_category_point_by_branch_inherit[0])  $sku_category_point_by_branch = $sku_category_point_by_branch_inherit[0];
					}
					
					if($sku_category_point_by_branch){
						foreach($member_type_list as $mtype){
							//if(isset($member_point_info[$mtype]['point']))	continue;
							if($sku_category_point_by_branch[$mtype] != ''){
								$member_point_info[$mtype]['point'] = $sku_category_point_by_branch[$mtype];
								$member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
							}else{
								if($sku_category_point_by_branch['global']!= ''){
									$member_point_info[$mtype]['point'] = $sku_category_point_by_branch['global'];
									$member_point_info[$mtype]['type']= 'SKU Items ('.$sku['id'].")";
								}
							}
						}
					}
				}
			}else{
				unset($member_point_info);
				$member_point = 0;
			}
		}
	}
	
	//use pos setting member point
	if($params['get_point_settings']){
		$mycon->sql_query("select * from pos_settings where setting_name = 'unit_point' and branch_id=$branch_id");
		$r2 = $mycon->sql_fetchassoc();
		$mycon->sql_freeresult();
		
		if(!isset($member_point) && $r2['setting_value']){
			$member_point = mf($r2['setting_value']);
		}
		$sku['member_point'] = $member_point;
		$sku['member_type_point'] = $member_point_info;
	}
	unset($member_point_info, $non_member_point_info);
	
	if($sku['member_price']>0){
		$sku['member_discount'] = intval(100-round($sku['member_price']/$sku['default_price'],2)*100);
	}else{
		$sku['member_discount'] = 100;
	}
	

	// calculate non-member price
	if($sku['price']<=$sku['non_member_price']){
        $sku['non_member_price'] = $sku['price'];
        // no more promo period sine it no longer use promo price
        unset($sku['non_member_date_from']);
        unset($sku['non_member_date_to']);
        unset($sku['non_member_time_from']);
        unset($sku['non_member_time_to']);
	}
	if($sku['non_member_price']>0){
		$sku['non_member_discount'] = intval(100-round($sku['non_member_price']/$sku['default_price'],2)*100);
	}else{
		$sku['non_member_discount'] = 100;
	}
	

	// additional for member type discount
	if($member_type_cat_discount){
		//print_r($member_type_cat_discount);
		foreach($member_type_cat_discount as $mtype=>$r){
			// cannot less than non-member discount
			if($r['discount']<$sku['non_member_discount'])	$r['discount'] = $sku['non_member_discount'];
			
			// calculate member type price
			$r['price'] = $sku['price'] * (1 - doubleval($r['discount'])/100);
			
			// assign to array if different with normal member price
			if($r['price']!=$sku['member_price']){
				$sku['member_type_cat_discount'][$mtype] = $r;
			}
		}
	}
	
	// promotion discount by member type
	if($sku['member_type_price']){
		foreach($sku['member_type_price'] as $mtype => $r){
			if(!isset($sku['member_type_cat_discount'][$mtype]) || $r['price'] < $sku['member_type_cat_discount'][$mtype]['price']){
				$r['discount'] = intval(100-round($r['price']/$sku['default_price'],2)*100);
				$sku['member_type_cat_discount'][$mtype] = $r;
			}
		}
	}
	
	if($is_under_gst){
		$sku['is_under_gst'] = 1;
		// recalculate price before gst and gst amt
		
		// non member price
		if($sku['price']>0){
			$sku['price_include_gst'] = $sku['price'];
			$sku['gst_amt'] = round($sku['price_include_gst'] / (100 + $sku['output_gst']['rate']) * $sku['output_gst']['rate'], 2);
			$sku['price_before_gst'] = round($sku['price_include_gst'] - $sku['gst_amt'], 4);
		}
		
		if($sku['non_member_price']>0){
			$sku['non_member_price_include_gst'] = $sku['non_member_price'];
			$sku['non_member_gst_amt'] = round($sku['non_member_price_include_gst'] / (100 + $sku['output_gst']['rate']) * $sku['output_gst']['rate'], 2);
			$sku['non_member_price_before_gst'] = round($sku['non_member_price_include_gst'] - $sku['non_member_gst_amt'], 4);
		}
		
		// member price
		if($sku['member_price']>0){
			$sku['member_price_include_gst'] = $sku['member_price'];
			$sku['member_gst_amt'] = round($sku['member_price_include_gst'] / (100 + $sku['output_gst']['rate']) * $sku['output_gst']['rate'], 2);
			$sku['member_price_before_gst'] = round($sku['member_price_include_gst'] - $sku['member_gst_amt'], 4);
		}
	}
	
	// need to get stock balance
	if($params['get_stock']){
		if(!defined('SYNC_SERVER')){
			$sic_exists = $mycon->sql_query_false("explain sku_items_cost");
			$mycon->sql_freeresult();
			
			if($sic_exists){
				$mycon->sql_query("select qty from sku_items_cost where branch_id=$branch_id and sku_item_id=".mi($sku['id']));
				$tmp = $mycon->sql_fetchassoc();
				$mycon->sql_freeresult();
				
				$sku['stock_balance'] = $tmp['qty'];
			}
		}
	}
	
	if($sessioninfo['id'] == 1){
		//print_r($sku);
	}
    return $sku;
}

function get_member_type_list($params){
	global $con;
	
	if($params['mysql_con'])
		$mycon = $params['mysql_con'];
	else
		$mycon = $con;
	
	$member_type_list = array();
	$mycon->sql_query("select distinct type from sku_items_mprice  where type like 'member%' order by type");
	while($r = $mycon->sql_fetchassoc()){
		$member_type_list[] = $r['type'];
	}
	$mycon->sql_freeresult();
	return $member_type_list;
}

function check_member($params){
	global $con;

	if($params['mysql_con'])
		$mycon = $params['mysql_con'];
	else
		$mycon = $con;
	
	$code = $params['code'];
	$bid = $params['branch_id'];
	
	$q1 = $mycon->sql_query("select *, date_format(issue_date, '%Y-%m-%d') as issue_date, date_format(next_expiry_date, '%Y-%m-%d') as next_expiry_date from membership where (card_no = ".ms($code)." or nric = ".ms($code).")");
	$member_info = $mycon->sql_fetchassoc($q1);
	$mycon->sql_freeresult($q1);
	
	return $member_info;
}

function get_category_point($params, $member_type_list, $category_id, $tree_str){
	global $con;
	
	if($params['mysql_con'])
		$mycon = $params['mysql_con'];
	else
		$mycon = $con;
	
	$branch_id = mi($params['branch_id']);
	$category_id = mi($category_id);
	if($category_id && $tree_str){
		$category_list = get_cat_tree_info($category_id, $tree_str, true);
		$category_level_count = count($category_list);
	}
	$data_list = array();
	
	if($category_level_count > 0){
		//category height to low
		for($n=$category_level_count;$n>=1;$n--){
			$category_id = mi($category_list[$n-1]['id']);
			
			$mycon->sql_query("select category_point, category_point_by_branch from category where id =$category_id");
			$category_info = $mycon->sql_fetchassoc();
			$mycon->sql_freeresult();
			
			if($category_info){
				$data_list['category_point'] = mf($category_info['category_point']);
				if(@unserialize($category_info['category_point_by_branch']) != false){
					$category_point_by_branch = array();
					$category_point_by_branch_list = unserialize($category_info['category_point_by_branch']);
					if($category_point_by_branch_list[$branch_id]){
						$category_point_by_branch = $category_point_by_branch_list[$branch_id];
					}else{
						if($category_point_by_branch_list[0]){
							$category_point_by_branch = $category_point_by_branch_list[0];
						}
					}
					
					if($category_point_by_branch && $member_type_list){
						foreach($member_type_list as $mtype){
							if(isset($data_list['category_member_point'][$mtype]['point']))	continue;
							if($category_point_by_branch[$mtype] != ''){
								$data_list['category_member_point'][$mtype]['point'] = $category_point_by_branch[$mtype];
								$data_list['category_member_point'][$mtype]['type'] = "category";
							}else{
								if($category_point_by_branch['global']!= ''){
									$data_list['category_member_point'][$mtype]['point'] = $category_point_by_branch['global'];
									$data_list['category_member_point'][$mtype]['type'] = "category";
								}
							}
						}
					}
					
					
				}
			}
		}
	}
	
	return $data_list;
}
?>
