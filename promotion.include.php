<?php
/*
3/4/2011 10:01:54 AM Andy
- Change when create new promotion by cancel item, revoke, copy also use max ID+1.
- Fix when revoke or cancel item, some data is missing from copy promotion.

3/18/2011 4:17:46 PM Andy
- Enhance "Active Promotion" to show mix and match promotion. (move find_overlap_promo() to promotion.include.php)

4/5/2011 4:58:25 PM Andy
- Move load_promo_header() and load_promo_items() to promotion.include, so both promotion.php and promotion.approval.php will call the same functions instead of own coding.
- Move assign_consignment() to promotion.include.php to fix the bugs consignment data din't load in promotion approval.
- Add checking for $config['promotion_turn_off_overlap_info'] to see whether to load overlap promotion info.
- Add "Bundled Price", discount qty must more than 1.
- Add "Special FOC".

5/4/2011 5:09:06 PM Andy
- Add discount target filter. (sku type, price type, price range)
- Add Control Type for mix and match promotion.
- Promotion overlap info show mprice, qprice and category discount.

6/8/2011 11:55:03 AM Alex
- add checking of consignment bearing profit get from commission

6/23/2011 12:47:56 PM Alex
- fix promotion unable to discount in %

6/24/2011 5:18:03 PM Andy
- Make all branch default sort by sequence, code.

7/8/2011 12:21:47 PM Andy
- Make overlap promotion info close at default.
- Touch up overlap promotion info mprice layout.

7/11/2011 5:58:11 PM Justin
- Fixed the promotion that cannot show out while the promotion is assigned from HQ.

7/12/2011 1:00:07 PM Alex
- get consignment bearing data by filter the branch which creator the promotion id

7/13/2011 2:21:36 PM Andy
- Add branch can view promotion created by HQ (from promotion history/active promotion), only if the promotion got related to the branch.
- Add checking to prevent branch to edit promotion created by HQ.

7/14/2011 11:44:39 AM Andy
- Add flag to find_overlap_promo() to indicate it is old, current or future promotion.

7/18/2011 9:59:42 AM Andy
- Fix HQ can view all branch promotion at active promotion.

7/21/2011 3:53:38 PM Andy
- Fix mix and match promotion every loop limit does not appear if create by wizard.

10/14/2011 5:10:09 PM Andy
- Add promotion history sorting by date from, date to.

11/18/2011 5:00:18 PM Andy
- Fix mix and match printing cannot direct open the print page but redirect user to view.

2/17/2012 3:35:22 PM Andy
- Add can set different category reward point.

7/26/2012 10:21:34 AM Justin
- Enhanced to use different keys while found config membership_type contains type=>description.

2/22/2013 11:57 AM Fithri
- add checkbox (include all parent/child) under add item.
- if got tick checkbox, will automatically add all the selected parent/child items
- if the same parent/child item put together, the 2nd item row color will change, until a new group sku

8/12/2013 11:41 AM Andy
- Enhance to check maintenance version 208.

11/7/2013 3:45 PM Andy
- Enhance to get sku group when load mix and match required data.
- Enhance to check sku group when check overlap promotion.

1/7/2013 2:33 PM Andy
- Add can tick "Prompt when available" for each group. (need config).

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.
- Enhanced the item_category_point_inherit_options to include deduct by global and deduct by ratio.

4/11/2014 10:53 AM Fithri
- add data collector import function at promotion module

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

7/15/2015 3:07 PM Andy
- Enhanced to show category/brand ID tooltips for category/brand description.

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price

2/17/2017 3:12PM Justin
- Enhanced to show error message on a standard view.

7/6/2018 12:19 PM Andy
- Enhanced import promotion to have column member discount, member price, non-member discount and non-member price.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.

6/28/2019 4:35 PM Andy
- Enhanced to can show Discount Promotion in Membership Mobile App.

9/3/2020 2:02 PM Andy
- Enhance to check maintenance version 470.
*/

$maintenance->check(470);
$maintenance->check(128,true);

define('DISCOUNT_VALUE_PATTERM', "/^[0-9]+(\.?[0-9]{1,2})?%?$/");
define('PRICE_VALUE_PATTERM', "/^[0-9]+(\.?[0-9]{1,2})?$/");

$promo_type_info = array('discount'=>'Discount', 'mix_and_match'=> 'Mix & Match');
$discount_by_type = array('amt'=>'Amount', 'per'=>'Percent', 'fixed_price'=>'Fixed Price', 'foc'=>'FOC', 'bundled_price'=>'Bundled Price');

$discount_by_inclusive_tax_arr = array('yes'=>'Yes', 'no'=>'No');

$all_items_val = -1;
$group_total_val = -2;
$disc_by_qty_type = array($group_total_val => 'Group Total', $all_items_val => 'All Items');

$promo_control_type = array('No Control','Limit by day','Limit by period');
$smarty->assign("control_type", $promo_control_type);

$smarty->assign('promo_type_info', $promo_type_info);
$smarty->assign('discount_by_type', $discount_by_type);
$smarty->assign('disc_by_qty_type', $disc_by_qty_type);
$smarty->assign('all_items_val', $all_items_val);
$smarty->assign('group_total_val', $group_total_val);
$smarty->assign('discount_by_inclusive_tax_arr', $discount_by_inclusive_tax_arr);

$category_point_inherit_options = array('inherit'=>'Inherit (Follow Category)', 'none'=>'No Point', 'set'=>'Override Reward Point');
$smarty->assign('category_point_inherit_options', $category_point_inherit_options);

$item_category_point_inherit_options = array('inherit'=>'Inherit (Follow Promotion Header)', 'none'=>'No Point', 'set'=>'Override Reward Point', 'deduct_by_global'=>'Deduct by Global Points', 'deduct_by_ratio'=>'Deduct by Ratio');
$smarty->assign('item_category_point_inherit_options', $item_category_point_inherit_options);


function load_mix_n_match_header($branch_id, $promo_id, $redirect_if_not_found = false){
    global $con, $sessioninfo, $smarty, $LANG;

	$con->sql_query("select promotion.*, bah.approvals from promotion
					left join branch_approval_history bah on bah.id=promotion.approval_history_id and bah.branch_id=promotion.branch_id
					where promotion.id = ".mi($promo_id)." and promotion.branch_id = ".mi($branch_id));

	$form = $con->sql_fetchrow();
	
	if(!$form){
		if($redirect_if_not_found){
            display_redir("/promotion.php", "Promotion", sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
		}
		else return false;
	}

	$form['promo_branch_id'] = unserialize($form['promo_branch_id']);
	$form['category_point_inherit_data'] = unserialize($form['category_point_inherit_data']);
	
	if ($sessioninfo['level']>=9999)	// superuser approve and final
	{
		$form['is_approval'] = 1;
		$form['last_approver'] = 1;
	}
	else
	{
		if (preg_match("/\|$sessioninfo[id]\|/", $form['approvals']))
			$form['is_approval'] = 1;
		if (preg_match("/\|\d+\|$/", $form['approvals']))
			$form['last_approver'] = 1;
	}

	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table='promotion' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");

		$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
	}
	
	$form['label'] = 'draft';
	if($form['status']==1 && !$form['approved'])   $form['label'] = 'waiting_approve';
	elseif($form['status']==1 && $form['approved'])    $form['label'] = 'approved';
	elseif($form['status']==2)  $form['label'] = 'rejected';
	elseif($form['status']==4)  $form['label'] = 'terminated';
	elseif($form['status']==5)  $form['label'] = 'cancelled';

	// load data such as sku type, price type, etc...
	load_mnm_required_data();
		
	return $form;
}

function load_mix_n_match_items_list($branch_id, $id, $load_from_tmp = false){
    global $con,$smarty, $LANG, $sessioninfo, $config;

    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_promotion_mix_n_match_items';
		$filter[] = "pi.user_id=$sessioninfo[id]";
	}else   $tbl = 'promotion_mix_n_match_items';

	$filter[] = "pi.branch_id=".mi($branch_id)." and pi.promo_id=".mi($id);
	
	$filter = "where ".join(' and ', $filter);

	if(is_new_id($id)){
        $promo['date_from'] = $_REQUEST['date_from'];
        $promo['date_to'] = $_REQUEST['date_to'];
	}else{
        $promo = load_mix_n_match_header($branch_id, $id);
	}
	$q_pi = $con->sql_query("select * from $tbl pi $filter order by pi.sequence_num, pi.id");
	while($r = $con->sql_fetchassoc($q_pi)){
		$group_id = mi($r['group_id']);
		$disc_target_value = mi($r['disc_target_value']);
		$sql = '';
		// header
		$items['group_list'][$group_id]['header']['receipt_limit'] = mi($r['receipt_limit']);
		$items['group_list'][$group_id]['header']['disc_prefer_type'] = mi($r['disc_prefer_type']);
		$items['group_list'][$group_id]['header']['for_member'] = mi($r['for_member']);
		$items['group_list'][$group_id]['header']['for_non_member'] = mi($r['for_non_member']);
		$items['group_list'][$group_id]['header']['follow_sequence'] = mi($r['follow_sequence']);
		$items['group_list'][$group_id]['header']['control_type'] = mi($r['control_type']);
		$items['group_list'][$group_id]['header']['item_category_point_inherit_data'] = unserialize($r['item_category_point_inherit_data']);
		$items['group_list'][$group_id]['header']['for_member_type'] = unserialize($r['for_member_type']);
		$items['group_list'][$group_id]['header']['prompt_available'] = mi($r['prompt_available']);
		$items['group_list'][$group_id]['header']['extra_info'] = unserialize($r['extra_info']);
		
		// item list
		$r['disc_condition'] = unserialize($r['disc_condition']);
		$r['disc_target_info'] =unserialize($r['disc_target_info']);
		get_complete_disc_condition_info($r['disc_condition']);
		if($r['disc_condition']){
			// check got every or not
			foreach($r['disc_condition'] as $dc){
				if($dc['rule']=='every'){
					$r['got_every'] = true;
					break;
				}
			}
		}
		$r['item_info'] = get_disc_condition_item_info($r['disc_target_type'], $disc_target_value);
		
		// find overlap
		if(!$config['promotion_turn_off_overlap_info']){
            $r['overlap_pi'] = get_mix_n_match_overlap($promo, $r);
		}
			
		$items['group_list'][$group_id]['item_list'][$r['id']] = $r;
	}
	$con->sql_freeresult($q_pi);
	//print_r($items);

	$smarty->assign('is_under_gst', check_mix_n_match_is_under_gst($branch_id));
	
	return $items;
}

function load_mix_n_match_item($branch_id, $id, $load_from_tmp = false, $show_tpl = false){
    global $con,$smarty, $LANG, $sessioninfo;
    
    // escape integer
    $branch_id = mi($branch_id);
    $id = mi($id);
    
    $filter = array();
    if($load_from_tmp){
		$tbl = 'tmp_promotion_mix_n_match_items';
		$filter[] = "pi.user_id=$sessioninfo[id]";
	}else   $tbl = 'promotion_mix_n_match_items';
	
	$filter[] = "pi.branch_id=".mi($branch_id)." and pi.id=".mi($id);
	$filter = "where ".join(' and ', $filter);
	
	// load item
	$con->sql_query("select * from $tbl pi $filter");
	$promo_item = $con->sql_fetchassoc();
    $con->sql_freeresult();
    
	if($promo_item){
	    if(is_new_id($promo_item['promo_id'])){
	        $promo['date_from'] = $_REQUEST['date_from'];
	        $promo['date_to'] = $_REQUEST['date_to'];
		}else{
	        $promo = load_mix_n_match_header($branch_id, $promo_item['promo_id']);
		}
	
	    $promo_item['disc_condition'] = unserialize($promo_item['disc_condition']);
	    $promo_item['disc_target_info'] =unserialize($promo_item['disc_target_info']);
	    get_complete_disc_condition_info($promo_item['disc_condition']);
	    if($promo_item['disc_condition']){
			// check got every or not
			foreach($promo_item['disc_condition'] as $dc){
				if($dc['rule']=='every'){
					$promo_item['got_every'] = true;
					break;
				}
			}
		}
		$promo_item['item_info'] = get_disc_condition_item_info($promo_item['disc_target_type'], $promo_item['disc_target_value']);
		// find overlap
		$promo_item['overlap_pi'] = get_mix_n_match_overlap($promo, $promo_item);
		//print_r($promo_item['overlap_pi']);
	}
	
	$smarty->assign('is_under_gst', check_mix_n_match_is_under_gst($branch_id));
	
	if($show_tpl){
		$smarty->assign('promo_item', $promo_item);
		return $smarty->fetch('promotion.mix_n_match.open.promo_item_row.tpl');
	}
	return $promo_item;
}

function get_disc_condition_item_info($type, $value){
    global $con,$smarty, $LANG, $sessioninfo;
    
    $ret = array();
    $value = mi($value);
    $sql == '';
    if($type=='sku'){
	    $sql = "select si.sku_item_code,si.artno,si.description
		from sku_items si
		where si.id=$value";
	}elseif($type=='brand'){
        $sql = "select description from brand where id=$value";
	}elseif($type=='category'){
        $sql = "select description from category where id=$value";
	}elseif($type=='category_brand'){
		$cat_id = mi(substr($value, 0, -5));
		$brand_id = mi(substr($value, -5));
		// get category description
		$con->sql_query("select description from category where id=$cat_id");
		$ret['cat_desc'] = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		// get brand description
		
		$con->sql_query("select description from brand where id=$brand_id");
		$ret['brand_desc'] = $con->sql_fetchfield(0);
		
		$ret['cat_id'] = $cat_id;
		$ret['brand_id'] = $brand_id;
		
		$con->sql_freeresult();
	}elseif($type=='sku_group'){
		$sku_group_id = mi(substr($value, 0, -3));
		$branch_id = mi(substr($value, -3));
		
		$sql = "select code,description from sku_group where branch_id=$branch_id and sku_group_id=$sku_group_id";
	}
	
	// get items details
	if($sql){
        $con->sql_query($sql);
		$ret = $con->sql_fetchassoc();
		$con->sql_freeresult();
	}
	return $ret;
}

function get_complete_disc_condition_info(&$disc_condition){
	if($disc_condition){
		foreach($disc_condition as $condition_row_num=>$r){
            $disc_condition[$condition_row_num]['item_info'] = get_disc_condition_item_info($r['item_type'], $r['item_value']);
		}
	}
}

function create_new_promo($branch_id, $form = array(), $field = array()){
	global $con;
	$max_failed_attemp = 5;
	$failed_attemp = -1;
	if(!$branch_id) display_redir("promotion.php", "Promotion", "Invalid Branch ID");
    $form['branch_id'] = $branch_id;
    
	do {
        $failed_attemp++;
        // get new promotion ID, to avoid replica bugs
        $con->sql_query("select max(id) from promotion where branch_id=$branch_id");
    	$form['id'] = mi($con->sql_fetchfield(0))+1;
    	$con->sql_freeresult();

    	$sql = "insert into promotion " . mysql_insert_by_field($form, ($field?$field:false));
    	if($failed_attemp<$max_failed_attemp){
            $q_success = $con->sql_query($sql, false,false);
		}else{
            $q_success = $con->sql_query($sql); // attemp insert more than 5 time, maybe is other error, stop unlimited loop
		}

    } while (!$q_success);   // insert until success
    return $form['id'];
}

function find_overlap_promo($promo, $match_info, &$bid_list){
	global $con, $sessioninfo,$config;
	if(!$match_info)    return;
	
	// check date format
	if(strpos($promo['date_from'], "/")!==false){
		$promo['date_from'] = dmy_to_sqldate($promo['date_from']); // reformat using dmy
	}
	if(strpos($promo['date_to'], "/")!==false){
		$promo['date_to'] = dmy_to_sqldate($promo['date_to']); // reformat using dmy
	}
	$today_time = strtotime(date("Y-m-d"));
	
	if($match_info['sku_item_id']){
		$si_info_list = array();
		if($match_info['include_parent_child']){
			$q1 = $con->sql_query("select * from sku_items where sku_item_id = ".mi($match_info['sku_item_id']));
			$si_info = $con->sql_fetchassoc($q1);
			$match_info['sku_id'] = $si_info['sku_id'];

			$q1 = $con->sql_query("select * from sku_items where sku_id = ".mi($si_info['sku_id']));

			while($r = $con->sql_fetchassoc($q1)){
				$si_info_list[$r['id']] = get_mix_n_match_sku_info($r['id']);
			}
		}else{
			$si_info_list[$match_info['sku_item_id']] = get_mix_n_match_sku_info($match_info['sku_item_id']);
		}
	}
	
	// sku info list
	//$si_info_list = $match_info['si_info_list'];
	
	/*
	// sku item id
    $sku_item_id = mi($match_info['sku_item_id']);
	
	// sku id
	$sku_id = mi($match_info['sku_id']);*/
    
    // category id
    $cat_id = mi($match_info['cat_id']);
    
    // brand id
    if(isset($match_info['brand_id']))	$brand_id = mi($match_info['brand_id']);
    
    // category + brand
	$category_brand_id = mi($match_info['category_brand_id']);
    if($category_brand_id){
        $cat_id = mi(substr($category_brand_id, 0, -5));
		$brand_id = mi(substr($category_brand_id, -5));
	}
	
	// sku group id + branch_id
	if(isset($match_info['sku_group_id2'])){
		$sku_group_id = mi(substr($match_info['sku_group_id2'], 0, -3));
		$sku_group_bid = mi(substr($match_info['sku_group_id2'], -3));
	}
	
	if($cat_id || isset($brand_id)){
		if(isset($match_info['sku_type']))	$sku_type = $match_info['sku_type'];
		if(isset($match_info['price_type']))	$price_type = $match_info['price_type'];
		if($match_info['price_range_from']>0)	$price_range_from = mf($match_info['price_range_from']);
		if($match_info['price_range_to']>0)	$price_range_to = mf($match_info['price_range_to']);	
	}
	//print_r($match_info);
	// search by sku
	$filter_dis = array();
	$filter_mnm_or = array();
	$extra_join = '';
	/*if($sku_id){ // found it is including parent & child
		$extra_join .= " left join sku on sku.id=si.sku_id ";
		$filter_dis[] = "sku.id = ".mi($si_info['sku_id']);
	}if($sku_item_id){
	    // discount promo filter
		$filter_dis[] = "pi.sku_item_id=$sku_item_id";
	    
	    // mix and match filter
	    // get sku info
		$sku_info = get_mix_n_match_sku_info($sku_item_id);

		$brand_id = mi($sku_info['brand_id']);
		$cat_id = mi($sku_info['category_id']);
		$max_cat_lv = mi($sku_info['level']);
		$cat_info = get_category_info($cat_id);
		
		$sku_type = $sku_info['sku_type'];
		$price_type = $sku_info['price_type'];
		$price_range_from = $price_range_to = $sku_info['selling_price'];
	}*/
	if($si_info_list){
		$filter_dis[] = "pi.sku_item_id in (".join(",", array_keys($si_info_list)).")";
	}elseif($sku_group_bid && $sku_group_id){	// sku group
		//print "$sku_group_bid , $sku_group_id<br>";
		$filter_dis[] = "si.sku_item_code in (select distinct sgi.sku_item_code from sku_group_item sgi where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id)";
		
		// get sku item id list in this sku group
		$sku_group_sid_list = get_sku_group_sku_item_id_list($sku_group_bid, $sku_group_id);
	}else{	// cat or brand
		// other type filter
		$extra_join .= " left join sku on sku.id=si.sku_id ";
		if($cat_id>0){
			$cat_info = get_category_info($cat_id);

			$filter_dis[] = "cc.p".mi($cat_info['level'])."=$cat_id";
			$extra_join .= " left join category c on c.id=sku.category_id
							left join category_cache cc on cc.category_id=c.id ";
		}
		
		if(isset($brand_id)){
            $filter_dis[] = "sku.brand_id=$brand_id";
		}
		
		if(isset($sku_type))	$filter_dis[] = "sku.sku_type=".ms($sku_type);
		if(isset($price_type))	$filter_dis[] = "pi.item_price_type=".ms($price_type);
		
		if($price_range_from>0 && $price_range_to>0){
			$filter_dis[] = "ifnull(sp.price, si.selling_price) between ".mf($price_range_from)." and ".mf($price_range_to);
		}elseif($price_range_from>0){
			$filter_dis[] = "ifnull(sp.price, si.selling_price) >= ".mf($price_range_from);
		}elseif($price_range_to>0){
			$filter_dis[] = "ifnull(sp.price, si.selling_price) <= ".mf($price_range_to);
		}
	}
	
	//print_r($match_info);
	if(!$filter_dis)	return;
	$filter_p = array();
	/*if (BRANCH_CODE != 'HQ'){
		$filter_p[] = " p.promo_branch_id like ".ms('%"'.BRANCH_CODE.'%";');
	}*/

	//print_r($bid_list);
	if (!$bid_list){
        if (BRANCH_CODE != 'HQ'){   // not HQ, see promotion created by own branch and HQ
            $bid_list = array($sessioninfo['branch_id'], 1);
            $check_branch_id = $sessioninfo['branch_id'];	// check promotion related to own branch
		}else{  // HQ only check the promotion created by own
			//$check_branch_id = 1;	// this line maybe will be ignore, since HQ will see all branch related
			//$bid_list = array(1);
			
			
			$con->sql_query("select id from branch where active=1 order by sequence,code");
			$bid_list = array();
			while ($r = $con->sql_fetchrow()){
				$bid_list[] = mi($r['id']);
			}
			$con->sql_freeresult();
		}
	}else{
		// got pass branch id
		if(!in_array(1, $bid_list))	$bid_list[] = 1;	// if not include promotion created by HQ, add in branch id 1
		if(count($bid_list)==2){	// not get all branch
			$check_branch_id = $bid_list[0];	// the first bid always the branch to check for, dont break this rule !!!
		}
	}
	//print "<br />";
	//print_r($bid_list);
	//print "check_branch_id = $check_branch_id<br />";	
	
	$filter_p[] = "(".ms($promo['date_from'])." between p.date_from and p.date_to or ".ms($promo['date_to'])." between date_from and date_to or date_from between ".ms($promo['date_from'])." and ".ms($promo['date_to']).")";
	$filter_p[] = "p.id<>".mi($promo['id']);	// exclude own promotion
	$filter_p[] = "p.active=1 and p.status in (0,1,2,3)";
	$pol_items = array();
	
	// search category discount
	if($cat_id>0 && $cat_info && $match_info['include_category_disc']){
		
		// check self
		if(is_category_got_discount($cat_info, $bid_list)){
			$pol_items['category_disc'][] = $cat_info;	
		}
		// check root
		$curr_root_cat_id = $cat_info['root_id'];
		while($curr_root_cat_id>0){
			$tmp_cat_info = get_category_info($curr_root_cat_id);
			if(is_category_got_discount($tmp_cat_info, $bid_list)){
				$pol_items['category_disc'][] = $tmp_cat_info;	
			}
			$curr_root_cat_id = mi($tmp_cat_info['root_id']);
		}
		
		// check category belows
		if($cat_info['level']<3){
			$q_c = $con->sql_query("select c.*
from category_cache cc
left join category c on c.id=cc.category_id
where cc.p".$cat_info['level']."=$cat_id and c.level<=3 and c.id<>$cat_id and (c.category_disc>0 or c.member_category_disc>0)");
			while($r = $con->sql_fetchassoc($q_c)){
				$r['category_disc_by_branch'] = unserialize($r['category_disc_by_branch']);
				if(is_category_got_discount($r, $bid_list)){
					$pol_items['category_disc'][] = $r;
				}
			}
			$con->sql_freeresult($q_c);
		}
	}
	
	// search sku own category discount
	if(($si_info_list || $cat_id>0 || isset($brand_id) || ($sku_group_bid && $sku_group_id)) && $match_info['include_category_disc']){
		$filter_sku_disc = array();
		
		if($si_info_list){
			$filter_sku_disc[] = "si.id in (".join(",", array_keys($si_info_list)).")";
		}
		if($cat_id>0 && $cat_info)	$filter_sku_disc[] = "cc.p".$cat_info['level']."=$cat_id";
		if(isset($brand_id))	$filter_sku_disc[] = "sku.brand_id=$brand_id";
		if($sku_group_bid && $sku_group_id){	// sku group
			$filter_sku_disc[] = "si.sku_item_code in (select distinct sgi.sku_item_code from sku_group_item sgi where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id)";
		}
		$filter_sku_disc[] = "si.category_point_inherit='set'";
		
		$filter_sku_disc = "where ".join(' and ', $filter_sku_disc);
		
		$q_sku_disc = $con->sql_query("select si.id,si.sku_item_code, si.description,si.category_disc_by_branch_inherit
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		$filter_sku_disc order by si.sku_item_code");

		while($r_sku = $con->sql_fetchassoc($q_sku_disc)){
			$got_related = false;
			$r_sku['category_disc_by_branch_inherit'] = unserialize($r_sku['category_disc_by_branch_inherit']);
			if(!is_array($r_sku['category_disc_by_branch_inherit']) || !$r_sku['category_disc_by_branch_inherit'])	continue;
			
			// unset unrelated branch
			if(BRANCH_CODE == 'HQ'){
				if($bid_list){
					foreach($r_sku['category_disc_by_branch_inherit'] as $tmp_bid=>$r){
						if($tmp_bid && !in_array($tmp_bid, $bid_list))	unset($r_sku['category_disc_by_branch_inherit'][$tmp_bid]);
					}
				}
			}else{
				foreach($r_sku['category_disc_by_branch_inherit'] as $tmp_bid=>$r){
					if($tmp_bid && $tmp_bid != $sessioninfo['branch_id'])	unset($r_sku['category_disc_by_branch_inherit'][$tmp_bid]);
				}
			}
			
			if(!$r_sku['category_disc_by_branch_inherit'])	continue;
			
			// check got data key in or not
			foreach($r_sku['category_disc_by_branch_inherit'] as $tmp_bid=>$r){
				if(!isset($r['set_override'])){
					unset($r_sku['category_disc_by_branch_inherit'][$tmp_bid]);
					continue;
				}
				
				if($r['member']['global']>0 || $r['nonmember']['global']>0)	$got_related = true;
				if(!$got_related && $config['membership_type']){
					foreach($config['membership_type'] as $mtype=>$mtype_desc){
						if(is_numeric($mtype)) $mt = $mtype_desc;
						else $mt = $mtype;
						if($r['member'][$mt]>0){
							$got_related = true;
							break;
						}
					}
				}
			}
			
			if($got_related && $r_sku){
				$pol_items['category_disc_by_sku'][] = $r_sku;
			}
		}
		$con->sql_freeresult($q_sku_disc);
	}
	
	// loop for each branch to get their promotion
	// discount promotion
	foreach($bid_list as $created_branch_id){
		if(BRANCH_CODE != 'HQ' || $check_branch_id){
			$bid = $check_branch_id;
		}else{
			$bid = mi($created_branch_id);
		}

	    // search by SKU
		if($si_info_list && $match_info['include_mprice_qprice']){
			foreach($si_info_list as $sid => $tmp_sku_info){
				$mqp_filters = array();
				$mqp_filters[] = "tbl.branch_id=$bid and tbl.last_update<".ms($promo['date_to'])." and tbl.price>0";
				$mqp_filters[] = "tbl.sku_item_id=".mi($sid);
				
				$mqp_filter = join(" and ", $mqp_filters);
				// mprice
				$q_mp = $con->sql_query("select branch.code as bcode, tbl.branch_id, tbl.sku_item_id, tbl.type, tbl.price
										from sku_items_mprice tbl 
										left join branch on branch.id=tbl.branch_id
										left join sku_items si on si.id = tbl.sku_item_id
										where ".$mqp_filter);
				while($r = $con->sql_fetchassoc($q_mp)){
					if($r['price'] == $tmp_sku_info['selling_price'])	continue;
					$pol_items['mprice'][$r['branch_id']][$r['type']] = $r;
				}
				$con->sql_freeresult($q_mp);
				
				// qprice
				$q_qp = $con->sql_query("select branch.code as bcode, tbl.branch_id, tbl.sku_item_id, tbl.min_qty, tbl.price
										from sku_items_qprice tbl 
										left join branch on branch.id=tbl.branch_id
										left join sku_items si on si.id = tbl.sku_item_id
										where ".$mqp_filter." order by tbl.min_qty");
				while($r = $con->sql_fetchassoc($q_qp)){
					if($r['price'] == $tmp_sku_info['selling_price'])	continue;
					$pol_items['qprice'][] = $r;
				}
				$con->sql_freeresult($q_qp);
			}
		}
	    
		$own_filter_p = $filter_p;
		$own_filter_p[] = "p.branch_id=$created_branch_id";
		
		// if not HQ, check only promotion related to own branch
		if(BRANCH_CODE != 'HQ' || $check_branch_id){
			$own_filter_p[] = " p.promo_branch_id like ".ms('%"'.get_branch_code($check_branch_id).'";%');
		}
		
		$filter_p_str = "where ".join(' and ', $own_filter_p);
		
		$promotion_id_list = array();
		// get promotion id first
		$sql = "select p.branch_id, p.id
		from promotion p
		$filter_p_str";
		//print $sql;
		$qp = $con->sql_query($sql);

		while($r = $con->sql_fetchassoc($qp)){
            $promotion_id_list[] = mi($r['id']);	// store promotion id and group by created branch
		}
		$con->sql_freeresult($qp);

		//print_r($promotion_id_list);print "<br />";
		if(!$promotion_id_list) continue;   // no promotion overlap for this branch
		// discount promotion
		$sql = "select pi.*, p.status, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, p.promo_type, if(sp.price is null, si.selling_price, sp.price) as selling_price, sc.grn_cost, sc.qty, p.consignment_bearing
				from promotion_items pi
				left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id
				left join sku_items si on pi.sku_item_id = si.id
				left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = pi.branch_id
				left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = pi.branch_id
				$extra_join
				where p.branch_id=".mi($created_branch_id)." and p.id in (".join(',', $promotion_id_list).") and ".join(' and ', $filter_dis)." and p.promo_type='discount'
				order by p.date_from, p.date_to";
		//print $sql."<br />";
		$pol_sql = $con->sql_query($sql);
		while($r2 = $con->sql_fetchassoc($pol_sql)){
			$r2['allowed_member_type'] = unserialize($r2['allowed_member_type']);
			
			if(strtotime($r2['date_from'])<=$today_time && $today_time<=strtotime($r2['date_to'])){
				$r2['promo_status'] = 'is_current';
			}elseif($today_time>strtotime($r2['date_to'])){
				$r2['promo_status'] = 'is_old';
			}else	$r2['promo_status'] = 'is_future';
			
			$pol_items['discount'][] = $r2;
		}
		$con->sql_freeresult($pol_sql);
		
		// mix and match promotion
		// get all mix and match
		$sql = "select pi.*, p.status, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, p.promo_type
			from promotion_mix_n_match_items pi
			left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id
            where p.branch_id=".mi($created_branch_id)." and p.id in (".join(',', $promotion_id_list).") and p.promo_type='mix_and_match' and pi.disc_target_type<>'receipt'
			order by p.date_from";
		$q1 = $con->sql_query($sql);
		while($pi = $con->sql_fetchassoc($q1)){
		    $tmp_cat_id = 0;
		    $tmp_brand_id = 0;
			$pi['disc_condition'] = unserialize($pi['disc_condition']);
		    $pi['disc_target_info'] =unserialize($pi['disc_target_info']);
		    
		    // find by sku
			if($si_info_list){
				$is_overlap_si = false;
				foreach($si_info_list as $sid => $tmp_sku_info){
					$brand_id = $tmp_sku_info['brand_id'];
					$cat_info = get_category_info($tmp_sku_info['category_id']);
					switch($pi['disc_target_type']){
						case 'sku': // this is sku discount
							if($pi['disc_target_value']!=$sid) continue; // must break 2 loop
							else $is_overlap_si = true;
							break;
						case 'category':    // category discount
							$tmp_cat_info = get_category_info($pi['disc_target_value']);
							if($cat_info['p'.$tmp_cat_info['level']] != $pi['disc_target_value'])   continue;
							else $is_overlap_si = true;
							break;
						case 'brand':   // brand discount
							if($pi['disc_target_value']!=$brand_id) continue;
							else $is_overlap_si = true;
							break;
						case 'category_brand':  // cat + brand
							if(!$cat_info && !isset($brand_id))	continue;
							
							$tmp_cat_id = mi(substr($pi['disc_target_value'], 0, -5));
							$tmp_brand_id = mi(substr($pi['disc_target_value'], -5));
				
							// check brand
							if(isset($brand_id)){
								if($tmp_brand_id!=$brand_id)    continue;
								else $is_overlap_si = true;
							}
							// check category
							if($cat_info){
								$tmp_cat_info = get_category_info($tmp_cat_id);
								if($cat_info['p'.$tmp_cat_info['level']] != $tmp_cat_id)   continue;
								else $is_overlap_si = true;
							}
							break;
						case 'sku_group':
							$tmp_sku_group_id = mi(substr($pi['disc_target_value'], 0, -3));
							$tmp_sku_group_bid = mi(substr($pi['disc_target_value'], -3));
							if(!$tmp_sku_group_bid || !$tmp_sku_group_id)	continue;
							
							// check whether this sku in this sku group
							$con->sql_query("select sgi.*
							from sku_group_item sgi
							join sku_items si on si.sku_item_code=sgi.sku_item_code
							where sgi.branch_id=$tmp_sku_group_bid and sgi.sku_group_id=$tmp_sku_group_id and si.id=$sid limit 1");
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if(!$tmp) continue;
							else $is_overlap_si = true;
							break;
						default:
							continue;
					}

					if($is_overlap_si) break;
				}
				
				if(!$is_overlap_si) continue;
			}else{
			    // other, maybe brand, category or both
				switch($pi['disc_target_type']){
					case 'sku': // sku discount
					    // get sku info
						
						$sid_list = array();
						if($pi['disc_target_info']['include_parent_child']){
							$tmp_sql = $con->sql_query("select * from sku_items where id = ".mi($pi['disc_target_value']));
							$tmp_si_info = $con->sql_fetchassoc($tmp_sql);
							$con->sql_freeresult($tmp_sql);
							
							$tmp_sql = $con->sql_query("select * from sku_items where sku_id = ".mi($tmp_si_info['sku_id']));
							
							while($r3 = $con->sql_fetchassoc($tmp_sql)){
								$sid_list[] = $r3['id'];
							}
							$con->sql_freeresult($tmp_sql);
						}else{
							$sid_list[] = $pi['disc_target_value'];
						}
						
						$is_overlap_si = false;
						foreach($sid_list as $sid){
							$tmp_sku_info = array();
							$tmp_sku_info = get_mix_n_match_sku_info($sid);
							
							// check brand
							if(isset($brand_id)){
								if($tmp_sku_info['brand_id'] != $brand_id)  continue;
								else $is_overlap_si = true;
							}
							// check category
							if($cat_id){
								$tmp_cat_info = get_category_info($tmp_sku_info['category_id']);
								if($tmp_cat_info['p'.$cat_info['level']] != $cat_id)  continue;
								else $is_overlap_si = true;
							}
							// check sku type
							if(isset($sku_type)){
								if($tmp_sku_info['sku_type']!=$sku_type) continue;
								else $is_overlap_si = true;
							}
							// check price type
							if(isset($price_type)){
								if($tmp_sku_info['price_type']!=$price_type) continue;
								else $is_overlap_si = true;
							}
							// check price from
							if($price_range_from>0){
								if($tmp_sku_info['selling_price'] < $price_range_from) continue;
								else $is_overlap_si = true;
							}
							// check price to
							if($price_range_to>0){
								if($tmp_sku_info['selling_price'] > $price_range_to) continue;
								else $is_overlap_si = true;
							}
							
							if($sku_group_sid_list && is_array($sku_group_sid_list)){
								if(!in_array($sid, $sku_group_sid_list)) continue;	// item not in sku group
								else $is_overlap_si = true;
							}
							
							if($is_overlap_si) break;
						}
						
						if(!$is_overlap_si) continue 2;

					    break;
					case 'category':    // category discount
						// check category
					    if(!$cat_id)  continue 2;
                        $tmp_cat_info = get_category_info($pi['disc_target_value']);
                    	if($tmp_cat_info['p'.$cat_info['level']] != $cat_id && $cat_info['p'.$tmp_cat_info['level']] != $pi['disc_target_value'])  continue 2;
					    break;
					case 'brand':   // brand discount
						// check brand
					    if(!isset($brand_id))  continue 2;
                        if($pi['disc_target_value']!=$brand_id) continue 2;
					    break;
                    case 'category_brand':  // cat + brand
					    if(!$cat_id && !isset($brand_id))  continue 2;
					    $tmp_cat_id = mi(substr($pi['disc_target_value'], 0, -5));
						$tmp_brand_id = mi(substr($pi['disc_target_value'], -5));
						
						// check brand
						if(isset($brand_id)){
							if($tmp_brand_id!=$brand_id) continue 2;
						}
						
						// check category
						if($cat_id){
							$tmp_cat_info = get_category_info($tmp_cat_id);
                        	if($tmp_cat_info['p'.$cat_info['level']] != $cat_id)  continue 2;
						}
						break;
					case 'sku_group':
						//print "sku_group<br>";
						$tmp_sku_group_id = mi(substr($pi['disc_target_value'], 0, -3));
						$tmp_sku_group_bid = mi(substr($pi['disc_target_value'], -3));
						if(!$tmp_sku_group_bid || !$tmp_sku_group_id)	continue 2;
						
						$overlap = false;
						
						// sku group match
						if($sku_group_bid == $tmp_sku_group_bid && $sku_group_id == $tmp_sku_group_id)	$overlap = true;
						
						if(!$overlap && $sku_group_bid && $sku_group_id){
							// check whether got sku overlap
							$con->sql_query("select * from sku_group_item sgi
							where sgi.branch_id=$tmp_sku_group_bid and sgi.sku_group_id=$tmp_sku_group_id and sgi.sku_item_code in (select distinct sgi2.sku_item_code 
							from sku_group_item sgi2
							where sgi2.branch_id=$sku_group_bid and sgi2.sku_group_id=$sku_group_id) limit 1");
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if($tmp)	$overlap = true;
						}
						
						if(!$overlap)	continue 2;

						break;
					default:
					    continue 2;
				}
			}
			
			// not match for particular sku or is match for sku but mix n match promotion is not sku
			if($pi['disc_target_type'] != 'sku'){
				// check sku type
				if(isset($sku_type) && $pi['disc_target_sku_type']){
					if($pi['disc_target_sku_type'] != $sku_type)	continue;
				}
				
				// check price type
				if(isset($price_type) && $pi['disc_target_price_type']){
					if($pi['disc_target_price_type'] != $price_type)	continue;
				}

				// check price from
				if($price_range_from > 0 && $pi['disc_target_price_range_to'] > 0){
					if($pi['disc_target_price_range_to'] < $price_range_from)	continue;
				}
				
				// check price to
				if($price_range_to > 0 && $pi['disc_target_price_range_from'] > 0){
					if($pi['disc_target_price_range_from'] > $price_range_to)	continue;
				}
			}

		    get_complete_disc_condition_info($pi['disc_condition']);
			$pi['item_info'] = get_disc_condition_item_info($pi['disc_target_type'], $pi['disc_target_value']);
			
			if(strtotime($pi['date_from'])<=$today_time && $today_time<=strtotime($pi['date_to'])){
				$pi['promo_status'] = 'is_current';
			}elseif($today_time>strtotime($pi['date_to'])){
				$pi['promo_status'] = 'is_old';
			}else	$pi['promo_status'] = 'is_future';
			
			$pol_items['mix_n_match'][] = $pi;
		}
		$con->sql_freeresult();
	}
	
	//print_r($pol_items);
	return $pol_items;
}

function get_mix_n_match_overlap($promo, $pi){
    $bid_list = array();
	switch($pi['disc_target_type']){
		case 'sku':
			$match_info['include_mprice_qprice'] = true;
			$match_info['sku_item_id'] = mi($pi['disc_target_value']);
			//$si_info_list = array();
			if($pi['include_parent_child']){
				$match_info['include_parent_child'] = mi($pi['include_parent_child']);
			}
		    break;
		case 'category':
		    $match_info['cat_id'] = mi($pi['disc_target_value']);
		    break;
        case 'brand':
		    $match_info['brand_id'] = mi($pi['disc_target_value']);
		    break;
        case 'category_brand':
		    $match_info['category_brand_id'] = mi($pi['disc_target_value']);
		    break;
		case 'sku_group':
			$match_info['sku_group_id2'] = mi($pi['disc_target_value']);	// included sku_group_id + branch_id
		    break;
		default:
		    return;
	}
	$match_info['include_category_disc'] = 1;
	if($pi['disc_target_sku_type'])	$match_info['sku_type'] = $pi['disc_target_sku_type'];
	if($pi['disc_target_price_type'])	$match_info['price_type'] = $pi['disc_target_price_type'];
	if($pi['disc_target_price_range_from']>0)	$match_info['price_range_from'] = $pi['disc_target_price_range_from'];
	if($pi['disc_target_price_range_to']>0)	$match_info['price_range_to'] = $pi['disc_target_price_range_to'];
	
    /*$samepromos = find_overlap_promo($promo, $match_info, $bid_list);
	if ($samepromos['discount']){
		foreach($samepromos['discount'] as $r2){
			$ditems['discount'][] = $r2;
		}
	}

	if ($samepromos['mix_n_match']){
        foreach($samepromos['mix_n_match'] as $r2){
			$ditems['mix_n_match'][] = $r2;
		}
	}
	
	if($samepromos['mprice']){
		$ditems['mprice'] = $samepromos['mprice'];
	}*/
	$ditems = find_overlap_promo($promo, $match_info, $bid_list);
	return $ditems;
}

function get_mix_n_match_sku_info($sku_item_id, $bid = 0){
	global $con, $sessioninfo;
	$sku_item_id = mi($sku_item_id);
	if(!$bid)	$bid = $sessioninfo['branch_id'];
	
	if(!$sku_item_id)   return;

	$con->sql_query("select si.id, sku.category_id,sku.brand_id, c.level,sku.sku_type, ifnull(sip.price, si.selling_price) as selling_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category c on c.id=sku.category_id
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		where si.id=$sku_item_id");
	$sku_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $sku_info;
}

function load_discount_promo_header($branch_id, $promo_id){
	global $con, $sessioninfo, $smarty, $config;
	
	$promo_id = mi($promo_id);
	$branch_id = mi($branch_id);

	$con->sql_query("select promotion.*, bah.approvals from promotion
					left join branch_approval_history bah on bah.id=promotion.approval_history_id and bah.branch_id=promotion.branch_id
					where promotion.id = ".mi($promo_id)." and promotion.branch_id = ".mi($branch_id));

	$form = $con->sql_fetchrow();
	$form['promo_branch_id'] = unserialize($form['promo_branch_id']);
	$form['str_promo_branch_id_list'] = implode(",", array_keys($form['promo_branch_id']));
	$form['category_point_inherit_data'] = unserialize($form['category_point_inherit_data']);
	$form['special_for_you_info'] = unserialize($form['special_for_you_info']);
	
	// this is mix and match promotion
    if($form['promo_type']=='mix_and_match'){
    	if($_REQUEST['a']=='do_print')	$open_type = 'print_promotion';
		else{
			$open_type = ($_REQUEST['a']=='open' ? 'open' : 'view');
		}	
        
        $redir_url =  "promotion.mix_n_match.php?a=$open_type&branch_id=".mi($form['branch_id'])."&id=".mi($form['id']);
        if(isset($_REQUEST['highlight_promo_item_id'])){
            $redir_url .= '&highlight_promo_item_id='.$_REQUEST['highlight_promo_item_id'];
		}
	    header("Location: $redir_url");
		exit;
	}

	if ($sessioninfo['level']>=9999)	// superuser approve and final
	{
		$form['is_approval'] = 1;
		$form['last_approver'] = 1;
	}
	else
	{
		if (preg_match("/\|$sessioninfo[id]\|/", $form['approvals']))
			$form['is_approval'] = 1;
		if (preg_match("/\|\d+\|$/", $form['approvals']))
			$form['last_approver'] = 1;
	}

	if ($form['approval_history_id']>0){
		$q2=$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table='promotion' and i.branch_id=$branch_id and i.approval_history_id=$form[approval_history_id]
order by i.timestamp");

		$smarty->assign("approval_history", $con->sql_fetchrowset($q2));
	}
	
	// Promotion Banner
	if($config['membership_mobile_settings'] && $form['show_in_member_mobile']){
		$folder = "attch/promo_banner/$branch_id/$promo_id";
		if(file_exists($folder)){
			$file_list = glob("$folder/banner_vertical_1.*");
			if($file_list[0]){
				$form['banner_vertical_1'] = trim($file_list[0]);
			}			
		}
	}

	return $form;
}

function load_discount_promo_items($branch_id, $promo_id, $use_tmp=false, $form='', $from_data_collector = false){
	global $con, $sessioninfo, $smarty, $config, $appCore;

	$owner_filter='';
    $branch_id = mi($branch_id);
    $promo_id = mi($promo_id);

	if($use_tmp){
		$table="tmp_promotion_items";
		$owner_filter=" and tpi.user_id=$sessioninfo[id] ";
	}
	else{
		$table="promotion_items";
	}

	//if($this->pg_bid) $sic = "and sc.branch_id in (".$this->pg_bid.")";

	$q1=$con->sql_query("select tpi.*, si.mcode, si.sku_item_code,si.artno, mcode, if(sp.price is null, si.selling_price, sp.price) as selling_price, si.description, sc.grn_cost, sc.qty,p.consignment_bearing, si.sku_id, si.link_code
	from $table tpi
	left join promotion p on tpi.branch_id=p.branch_id and tpi.promo_id=p.id
	left join sku_items si on sku_item_id = si.id
	left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = tpi.branch_id
	left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = tpi.branch_id
	where tpi.promo_id=".$promo_id." and tpi.branch_id=".$branch_id." $owner_filter group by tpi.id
	order by tpi.id") or die(mysql_error());
	$promo_items = array();
	while($r = $con->sql_fetchassoc($q1)){
		$r['allowed_member_type'] = unserialize($r['allowed_member_type']);
		$r['extra_info'] = unserialize($r['extra_info']);
		
		if($config['membership_mobile_settings'] && $form['show_in_member_mobile']){
			$promo_photo_list = $appCore->skuManager->getSKUItemPromoPhotos($r['sku_item_id']);
			if($promo_photo_list){
				$r['promo_photo_url'] = $promo_photo_list[0];
			}
		}
		$promo_items[] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("promo_item_count", count($promo_items));

	if(($form['consignment_bearing']=='yes' || $promo_items[0]['consignment_bearing']=='yes') && $promo_items){
		if ($form['status'] === 0){
			if ($form['s_vendor_id'])		$form['vendor_id']=$form['s_vendor_id'];
			if ($form['s_brand_id'])		$form['brand_id']=$form['s_brand_id'];
	
			//compare consignment bearing discount and tmp promotion items discount
			$filter=" cb.dept_id=".intval($form['dept_id'])." and (cb.vendor_id=".intval($form['vendor_id'])." and cb.brand_id=".intval($form['brand_id']).") and cb.active=1 and cbi.profit>0";
			
			$sql="select cbi.trade_discount_type_code as code, cbi.profit, cbi.discount, if(cbi.use_net = 'amount','yes', cbi.use_net) as use_net, cbi.net_bearing  
					from consignment_bearing_items cbi
					left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
					where $filter";
	
			$cb_rid=$con->sql_query($sql);
			if ($con->sql_numrows($cb_rid)>0) {
	
				//direct get profit if not 'none' type
				if ($form['vendor_id'])	$sql_commission="select * from vendor_commission where department_id=".intval($form['dept_id'])." and vendor_id=".intval($form['vendor_id'])." and branch_id=".$branch_id;
				elseif ($form['brand_id'])	$sql_commission="select * from brand_commission where department_id=".intval($form['dept_id'])." and brand_id=".intval($form['brand_id'])." and branch_id=".$branch_id;
	
				if ($sql_commission){
					//brand or vendor mode
					$comm_rid=$con->sql_query($sql_commission);
					while ($comm=$con->sql_fetchassoc($comm_rid)){
						$cb_profit[$comm['skutype_code']]=$comm['rate'];
					}
					
					$con->sql_freeresult($comm_rid);
				}
	
				while ($cb=$con->sql_fetchassoc($cb_rid)){
					$cb['profit']=$cb_profit ? $cb_profit[$cb['code']]:$cb['profit']; 
				
					$check_cb_arr[$cb['code']][$cb['profit']][$cb['discount']][$cb['use_net']][$cb['net_bearing']]=1;
				}
			
				$con->sql_freeresult($cb_rid);
			}
			
			foreach ($promo_items as $no => $att){
				//member side
				$m_code=$att['member_trade_code'];
				$m_prof=mf($att['member_prof_p']);
				$m_disc=str_replace("%","",$att['member_disc_p']);
				$m_use_net=$att['member_use_net'];
				$m_bear=mf($att['member_net_bear_p']);
				
				//print $m_code." ".$m_prof." ".$m_disc." ".$m_use_net." ".$m_bear;
				if ($m_code !='' and $m_prof > 0 and $m_disc>0 and $m_bear > 0){
					if (!$check_cb_arr[$m_code][$m_prof][$m_disc][$m_use_net][$m_bear]){
						$promo_items[$no]['figure']['m']='unmatch';
					}
				}
	
				//nonmember side
				$nm_code=$att['non_member_trade_code'];
				$nm_prof=mf($att['non_member_prof_p']);
				$nm_disc=str_replace("%","",$att['non_member_disc_p']);
				$nm_use_net=$att['non_member_use_net'];
				$nm_bear=mf($att['non_member_net_bear_p']);
				if ($nm_code !="" and $nm_prof > 0 and $nm_disc > 0 and $nm_bear > 0){
	
					if (!$check_cb_arr[$nm_code][$nm_prof][$nm_disc][$nm_use_net][$nm_bear]){
						$promo_items[$no]['figure']['nm']='unmatch';
					}
				}
			}
		}
		
		//for cheking to avoid confuse of consignment bearing and normal mode
		foreach ($promo_items as $no => $att){
			if (strpos($att['member_disc_p'],"%")){
				$promo_items[$no]['cb_member_disc_p']=$att['member_disc_p'];
				unset($promo_items[$no]['member_disc_p']);
			}				

			if (strpos($att['non_member_disc_p'],"%")){
				$promo_items[$no]['cb_non_member_disc_p']=$att['non_member_disc_p'];
				unset($promo_items[$no]['non_member_disc_p']);
			}	
		}	
	}

	$smarty->assign("promotion_items",$promo_items);

	$con->sql_query("select * from promotion where branch_id = ".mi($branch_id)." and id = ".mi($promo_id));
	$promo = $con->sql_fetchrow();
	
	if ($from_data_collector) {
		//if using data collector, need this info to get overlap promo info
		$promo = $from_data_collector;
	}

	//$this->assign_consignment(false,$promo);
    assign_consignment(false,$promo);
    if(!$config['promotion_turn_off_overlap_info']){
        if ($promo_items){
		    $bid_list = array();
			foreach($promo_items as $pi)
			{
				$samepromos = find_overlap_promo($promo, array('sku_item_id'=>$pi['sku_item_id'], 'include_mprice_qprice'=>true, 'include_category_disc'=>true), $bid_list);
				if ($samepromos['discount'])
				{
					foreach($samepromos['discount'] as $r2)
					{
						$ditems['discount'][$pi['id']][] = $r2;
					}
				}

				if ($samepromos['mix_n_match']){
	                foreach($samepromos['mix_n_match'] as $r2)
					{
						$ditems['mix_n_match'][$pi['id']][] = $r2;
					}
				}
				
				if ($samepromos['mprice']){
	                foreach($samepromos['mprice'] as $bid=>$r2)
					{
						$ditems['mprice'][$pi['id']][$bid] = $r2;
					}
				}
				
				if ($samepromos['qprice']){
	                foreach($samepromos['qprice'] as $r2)
					{
						$ditems['qprice'][$pi['id']][] = $r2;
					}
				}
				
				if ($samepromos['category_disc']){
	                foreach($samepromos['category_disc'] as $r2)
					{
						$ditems['category_disc'][$pi['id']][] = $r2;
					}
				}
				
				if ($samepromos['category_disc_by_sku']){
	                foreach($samepromos['category_disc_by_sku'] as $r2)
					{
						$ditems['category_disc_by_sku'][$pi['id']][] = $r2;
					}
				}
			}
		}
	}
	
	//print_r($ditems);
	$smarty->assign("ditems",$ditems);

	return $promo_items;
}

function assign_consignment($get_from_request=true, $promo=''){
	global $con,$smarty,$sessioninfo;

	$filter='and cb.active=1';
	
	if ($get_from_request){
		$form=$_REQUEST;
		$form['vendor_id']=$form['s_vendor_id'];
		$form['brand_id']=$form['s_brand_id'];
	}else{
		$form=$promo;
	}

	if ($form['consignment_bearing']!='yes')   return;

	if (!$form['dept_id']) return;

	$filter.=" and r_type=".ms($form['r_type']);
	if ($form['r_type']=='vendor'){
		if (!$form['vendor_id'])    return;
		else	$filter.=" and cb.vendor_id=$form[vendor_id] ";
	}
	elseif ($form['r_type']=='brand'){
          if (!$form['brand_id'])    return;
		else	$filter.=" and cb.brand_id=$form[brand_id] ";
	}
	
	if ($form['branch_id'])
		$branch_id=$form['branch_id'];
	else
		$branch_id=$sessioninfo['branch_id'];

	$branch_filter=" and cb.branch_id=$branch_id";
	$branch_comm_filter=" and branch_id=$branch_id";

	$sql="select cbi.trade_discount_type_code as code , cbi.profit, if(cbi.use_net = 'amount','',concat(cbi.discount,'%')) as discount ,if(cbi.use_net = 'amount','yes',cbi.use_net) as use_net, cbi.net_bearing
		from consignment_bearing_items cbi
		left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
		where cb.dept_id=".mi($form['dept_id'])." $branch_filter $filter
		order by r_type";
		
	$cb_rid=$con->sql_query($sql);

	if ($con->sql_numrows($cb_rid)>0){

		//direct get profit if not 'none' type
		if ($form['vendor_id'])	$sql_commission="select * from vendor_commission where department_id=".intval($form['dept_id'])." and vendor_id=".intval($form['vendor_id']).$branch_comm_filter;
		elseif ($form['brand_id'])	$sql_commission="select * from brand_commission where department_id=".intval($form['dept_id'])." and brand_id=".intval($form['brand_id']).$branch_comm_filter;

		if ($sql_commission){
			//brand or vendor mode
			$comm_rid=$con->sql_query($sql_commission);
			while ($comm=$con->sql_fetchassoc($comm_rid)){
				$cb_profit[$comm['skutype_code']]=$comm['rate'];
			}
			
			$con->sql_freeresult($comm_rid);
		}

		while ($cb=$con->sql_fetchassoc($cb_rid)){
			$cb['profit']=$cb_profit ? $cb_profit[$cb['code']]:$cb['profit'];

			$cb_options[]=$cb;
		}

        $smarty->assign('consignment',$cb_options);
	}
	$con->sql_freeresult($cb_rid);
		
}

function load_consignment_bearning_dept($branch_id, $open_mode = true){
	global $con, $smarty, $sessioninfo;
	//check confirm
	if (!$branch_id)	$branch_id=$sessioninfo['branch_id'];
	
	if ($open_mode)	$dept_filter=" and c.id in ($sessioninfo[department_ids])";

    $con->sql_query("select distinct c.id,c.description from consignment_bearing_items cbi
		                left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
		                left join category c on cb.dept_id=c.id
						where cb.active=1 and cb.branch_id=$branch_id $dept_filter
						order by c.description");

	$smarty->assign("departments", $con->sql_fetchrowset());
	$con->sql_freeresult();
}

function load_mnm_required_data(){
	global $con, $smarty, $config, $sessioninfo;
	
	$master_sku_type = $smarty->get_template_vars('master_sku_type');
	$master_price_type = $smarty->get_template_vars('master_price_type');
	
	if(!$master_sku_type){
		$con->sql_query("select * from sku_type where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			$master_sku_type[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('master_sku_type', $master_sku_type);
	}
	
	if(!$master_price_type){
		$con->sql_query("select * from trade_discount_type order by code");
		while($r = $con->sql_fetchassoc()){
			$master_price_type[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('master_price_type', $master_price_type);
	}
	
	// sku group
	$master_sku_group_list = $smarty->get_template_vars('master_sku_group_list');
	if(!$master_sku_group_list){
		$master_sku_group_list = array();
		if($config['sku_group_searching_need_filter_user']){
			if($sessioninfo['level']>=900){
				$sku_group_filter = '';
			}elseif($sessioninfo['level']>=500){
		        $sku_group_filter = "where s1.branch_id=".mi($sessioninfo['branch_id']);
			}else{
		        $sku_group_filter = "where s1.user_id=".mi($sessioninfo['id']);
			}
		}
		
		
		$sql = "select s1.*,count(s2.sku_item_code) as item_count from sku_group s1
		left join sku_group_item s2 using(sku_group_id,branch_id)
		$sku_group_filter group by sku_group_id,branch_id order by description";
		
		$con->sql_query($sql);
		while($r = $con->sql_fetchassoc()){
			$r['id2'] = ($r['sku_group_id']*1000)+$r['branch_id'];
			$master_sku_group_list[] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('master_sku_group_list', $master_sku_group_list);
	}
	
}

function check_can_change_promotion_status($branch_id, $promo_id){
	global $con, $sessioninfo, $config, $LANG;
	
	$passed = false;
	if($config['single_server_mode'] && BRANCH_CODE == 'HQ'){
		$passed = true;
	}else{
		if($branch_id == $sessioninfo['branch_id'])	$passed = true;
		else	$passed = false;
	}
	
	if(!$passed){
		js_redirect(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], 'PROMOTION'), "/promotion.php");
	}
}

function is_category_got_discount(&$cat_info, $bid_list = array()){
	global $config, $sessioninfo;
	//print_r($bid_list);
	if(!is_array($cat_info['category_disc_by_branch']) || !$cat_info['category_disc_by_branch'])	return false;
	
	if(BRANCH_CODE == 'HQ'){
		if($bid_list){	// got branch id filter
			foreach($cat_info['category_disc_by_branch'] as $tmp_bid=>$r){
				if($tmp_bid && !in_array($tmp_bid, $bid_list)){
					unset($cat_info['category_disc_by_branch'][$tmp_bid]);
				}
			}
		}
	}else{
		foreach($cat_info['category_disc_by_branch'] as $tmp_bid=>$r){
			if($tmp_bid && $tmp_bid != $sessioninfo['branch_id'])	unset($cat_info['category_disc_by_branch'][$tmp_bid]);
		}
	}
	
	
	foreach($cat_info['category_disc_by_branch'] as $tmp_bid=>$r){
		if(isset($r['set_override'])){
			if($r['member']['global']>0 || $r['nonmember']['global']>0)	return true;
			if($config['membership_type']){
				foreach($config['membership_type'] as $mtype=>$mtype_desc){
					if(is_numeric($mtype)) $mt = $mtype_desc;
					else $mt = $mtype;
					if($r['member'][$mt]>0)	return true;
				}
			}	
		}
	}
	return false;
}

function get_sku_group_sku_item_id_list($sku_group_bid, $sku_group_id){
	global $con;
	
	// get sku item id list in sku group
	$con->sql_query("select si.id as sid 
	from sku_group_item sgi
	join sku_items si on si.sku_item_code=sgi.sku_item_code
	where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id");
	$sku_group_sid_list = array();
	while($r = $con->sql_fetchassoc()){
		$sku_group_sid_list[] = mi($r['sid']);
	}
	$con->sql_freeresult();
	
	return $sku_group_sid_list;
}

function check_mix_n_match_is_under_gst($branch_id){
	$params = array();
	$params['branch_id'] = $branch_id;
	$params['date'] = date('Y-m-d');
	return check_gst_status($params);
}

?>
