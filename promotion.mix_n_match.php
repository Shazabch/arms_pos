<?php
/*
2/28/2011 11:18:35 AM Andy
- Add "from qty".
- Add Discount Preference: "All discount target"

3/2/2011 1:50:32 PM Andy
- Change insert promotion to use max(id)+1 to avoid mysql replica wrong ID to slave.

3/3/2011 5:57:47 PM Andy
- Change when create new promotion by cancel item, revoke, copy also use max ID+1.
- Fix when revoke or cancel item, some data is missing from copy promotion.
- Add receipt description.

4/29/2011 5:37:57 PM Andy
- Add "discount by qty" for each mix and match promotion row.
- Add "Bundled Price", discount qty must more than 1.
- Add "Special FOC".
- Add discount target filter. (sku type, price type, price range)
- Add Control Type for mix and match promotion.
- Add create promotion by wizard.

6/24/2011 5:19:03 PM Andy
- Make all branch default sort by sequence, code.

7/8/2011 12:22:01 PM Andy
- Make overlap promotion info close at default.
- Touch up overlap promotion info mprice layout.

7/26/2011 3:21:29 PM Andy
- Add generate promotion wizard will also set receipt limit.
- Fix promotion wizard (Start discount on Y Qty onwards), (Step Promotion Discount).

9/5/2011 9:31:50 AM Andy
- Add (PWP) remark for promotion wizard id#13

9/7/2011 1:02:56 PM Andy
- Fix Discount Filter for SKU Type and Price Type does not show when create new promotion. 

2/17/2012 3:35:35 PM Andy
- Add can set different category reward point.

5/18/2012 12:00:00 PM Andy
- Fix promotion create by branch cannot be use.

8/23/2012 2:50 PM Justin
- Bug fixed on Override Member Reward Point by Group not working properly.

6/7/2013 11:06 AM Andy
- Add checking privilege "PROMOTION_MIX" to allow user to create/edit/view mix and match.

7/25/2013 5:04 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

1/7/2013 2:33 PM Andy
- Add can tick "Prompt when available" for each group. (need config).

3/5/2014 5:17 PM Justin
- Enhanced to have include parent & child feature.

5/30/2016 1:59 PM Andy
- Enhanced to compatible with php7.

9/19/2016 09:05 Qiu Ying
- Enhanced to set selling inclusive or exclusive for bundled price

2/17/2017 3:12PM Justin
- Enhanced to show error message on a standard view.

4/19/2017 1:54 PM Khausalya 
- Enhanced changes from RM to use config setting. 

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PROMOTION')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION', BRANCH_CODE), "/index.php");
if (!privilege('PROMOTION_MIX')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION_MIX', BRANCH_CODE), "/index.php");

include('promotion.include.php');

class MIX_N_MATCH_PROMOTION extends Module{
	var $branch_id = 0;
	var $promo_id = 0;
	var $branches = array();
	var $discount_target_type_arr = array(
		'sku_item_id' => 'ARMS Code',
		'category_id' => 'Category',
		'brand_id' => 'Brand',
		'receipt' => 'Receipt'
	);
	var $allow_edit = 0;
	var $redirect_php = 'promotion.php';
	var $control_type = array();
	var $promotion_wizard_list = array();
	
    function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty, $config, $promo_control_type;
		
		$this->control_type = $promo_control_type;
		
		if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		
		if($_REQUEST['branch_id']==$sessioninfo['branch_id']){
		    $this->allow_edit = 1;
            $smarty->assign('allow_edit', $this->allow_edit);
		}
		
		$smarty->assign('discount_target_type_arr', $this->discount_target_type_arr);
		
		// promotion wizard list
		$this->promotion_wizard_list[1] = array(
			'title' => 'Buy 1 Free 1 Promotion. (Same Item)',
			'desc' => '<b><u>Description:</u></b><br />If customer buy 2 selected item. one of it will become FOC.<br /><br /> <b><u>Example</u></b>:<br />Purchase T-Shirt (code: ABC) and get another T-Shirt (code: ABC) Free.<br />If there is a limit of maximum 2 item, then there will be only 2 item can become FOC, the 3rd qualified item will need to pay.'
		);
		$this->promotion_wizard_list[15] = array(
			'title' => 'Buy 1 Free 1 Promotion. (Different Item)',
			'desc' => '<b><u>Description:</u></b><br />If customer buy 1 selected item. they will get one of the other item with FOC.<br /><br /> <b><u>Example</u></b>:<br />Purchase T-Shirt (code: ABC) and get another T-Shirt (code: EFG) Free.<br />If there is a limit of maximum 2 item, then there will be only 2 item can become FOC, the 3rd qualified item will need to pay.'
		);
		
		$this->promotion_wizard_list[2] = array(
			'title' => 'Discount on <span class="keyword">Receipt Total Amount</span>, based on <span class="keyword">Receipt Total Amount</span>. (Buy more than ' . $config["arms_currency"]["symbol"] . '100 discount '.$config["arms_currency"]["symbol"].'10)',
			'desc' => '<b><u>Description:</u></b><br />If customer buy amount X or more, they will get the discount on receipt total amount.<br /><br /> <b><u>Example</u></b>:<br />Discount ' . $config["arms_currency"]["symbol"] . '10 or 10% when customer buy ' . $config["arms_currency"]["symbol"] . '100 or more.'
		);
		$this->promotion_wizard_list[3] = array(
			'title' => 'Discount on <span class="keyword">Category Total Amount</span>, based on <span class="keyword">Total Amount of Category Qualifier</span>.',
			'desc' => '<b><u>Description:</u></b><br />If customer buy amount X or more for selected category, they will get the discount on selected category total amount.<br /><br /> <b><u>Example</u></b>:<br />Purchase ' . $config["arms_currency"]["symbol"] . '50 or more from Grocery will get ' . $config["arms_currency"]["symbol"] . '5 or 5% discount on grocery total amount.'
		);
		$this->promotion_wizard_list[4] = array(
			'title' => 'Discount on <span class="keyword">Brand Total Amount</span>, based on <span class="keyword">Total Amount of Brand Qualifier</span>.',
			'desc' => '<b><u>Description:</u></b><br />If customer buy amount X or more for selected brand, they will get the discount on selected brand total amount.<br /><br /> <b><u>Example</u></b>:<br />Purchase ' . $config["arms_currency"]["symbol"] . '50 or more from AYAMAS will get ' . $config["arms_currency"]["symbol"] . '5 or 5% discount on brand total amount.'
		);
		$this->promotion_wizard_list[5] = array(
			'title' => 'Discount on <span class="keyword">Selected Item Total Amount</span>, based on <span class="keyword">Total Qty of Item Qualifier</span>.',
			'desc' => '<b><u>Description:</u></b><br />If customer buy qty X or more for selected item, they will get the discount on selected item total amount.<br /><br /> <b><u>Example</u></b>:<br />Purchase 3 or more for the same T-shirt, will get ' . $config["arms_currency"]["symbol"] . '5 or 5% discount on the selected T-shirt total amount.'
		);
		$this->promotion_wizard_list[6] = array(
			'title' => 'Discount on <span class="keyword">Category+Brand Total Amount</span>, based on <span class="keyword">Total Amount of Category+Brand Qualifier</span>.',
			'desc' => '<b><u>Description:</u></b><br />If customer buy amount X or more for selected category and brand, they will get the discount on selected category and brand total amount.<br /><br /> <b><u>Example</u></b>:<br />Purchase ' . $config["arms_currency"]["symbol"] . '50 or more for Binbi T-shirt, will get ' . $config["arms_currency"]["symbol"] . '5 or 5% discount on Binbi T-shirt total amount.'
		);
		$this->promotion_wizard_list[7] = array(
			'title' => 'Discount on <span class="keyword">Selected Item</span>, based on <span class="keyword">Total Qty of Item Qualifier</span>. (Buy Y Qty and get some of it discount)',
			'desc' => '<b><u>Description:</u></b><br />If customer buy qty X for selected item. some of it will get discount.<br /><br /> <b><u>Example</u></b>:<br />Purchase 3 T-Shirt (code: ABC), the 3rd T-shirt will get 50% discount. (or 2nd and 3rd also get 50% discount)<br />If there is a limit of maximum 2 item, then there will be no discount for 3rd qualified item.'
		);
		$this->promotion_wizard_list[8] = array(
			'title' => 'Discount on <span class="keyword">Selected Item</span>, based on <span class="keyword">Total Qty of Item Qualifier</span>. (Start discount on Y Qty onwards)',
			'desc' => '<b><u>Description:</u></b><br />If customer buy X or more qty for selected item, they will get the discount on the X item onwards.<br /><br /> <b><u>Example</u></b>:<br />Purchase 3 or more T-Shirt (code: ABC), the 3rd T-Shirt and onwards all get 20% discount.<br />If there is a limit of maximum 2 item, then there will be no discount for 3rd qualified item.'
		);
		
		$this->promotion_wizard_list[9] = array(
			'title' => 'Discount on <span class="keyword">Selected Item</span>, based on <span class="keyword">Total Qty of Item Qualifier</span>, Discount with <span class="keyword">Bundled Price</span>.',
			'desc' => '<b><u>Description:</u></b><br />If customer buy qty Y for selected item, they will get a better offer price for this item with bundled price.<br /><br /> <b><u>Example</u></b>:<br />Purchase 1 T-Shirt (code: ABC) at normal price ' . $config["arms_currency"]["symbol"] . '13<br /> purchase 2 T-Shirt (code: ABC) with bundled price ' . $config["arms_currency"]["symbol"] . '24<br />purchase 3 T-Shirt (code: ABC) with bundled price ' . $config["arms_currency"]["symbol"] . '33.'
		);
		
		$this->promotion_wizard_list[10] = array(
			'title' => 'Package Promotion Discount.',
			'desc' => '<b><u>Example</u></b>:<br />Buy 3 with 20% discount<br />Buy 5 with 30% discount<br />Buy 8 with 40% discount.'
		);
		
		$this->promotion_wizard_list[11] = array(
			'title' => 'Step Promotion Discount.',
			'desc' => '<b><u>Example</u></b>:<br />10% discount after 3 items<br />15% discount after 6 items<br />20% discount after 10 items.'
		);
		
		$this->promotion_wizard_list[12] = array(
			'title' => 'Range Promotion Discount.',
			'desc' => '<b><u>Example</u></b>:<br />For the first 3 items, you pay the normal price<br />for the next 3 items (4-6), you get 10% discount<br />for the next 4 items (7-10), you get 15% discount<br />for those over 10, get 20% discount.'
		);
		
		$this->promotion_wizard_list[13] = array(
			'title' => 'Entitle <span class="keyword">Special Price for selected item</span>, based on <span class="keyword">Receipt Total Amount</span>. (<span class="keyword">PWP</span>: Fixed Price for SKU)',
			'desc' => '<b><u>Example</u></b>:<br />If receipt total amount more than ' . $config["arms_currency"]["symbol"] . '50, then entitle customer to buy milo 10kg at ' . $config["arms_currency"]["symbol"] . '4.'
		);
		
		$this->promotion_wizard_list[14] = array(
			'title' => 'Discount on <span class="keyword">User Selected Target</span>, based on <span class="keyword">User Selected Qualifier</span>. (Purchase X and Y then get discount for Z)',
			'desc' => '<b><u>Description:</u></b><br />Purchase X and Y then get discount for Z.<br /><br /><b><u>Example</u></b>:<br />Purchase 1 T-shirt and 1 Pants then get discount ' . $config["arms_currency"]["symbol"] . '10 or 10% for 1 Belt.'
		);
		$this->promotion_wizard_list[16] = array(
			'title' => '<span class="keyword">Free Special Item</span>, based on <span class="keyword">Receipt Total Amount</span>. (Free coupon, voucher, etc...)',
			'desc' => '<b><u>Description:</u></b><br />Purchase ' . $config["arms_currency"]["symbol"] . 'XXX or more and then get a voucher to use for next visit.<br /><br /><b><u>Example</u></b>:<br />Purchase ' . $config["arms_currency"]["symbol"] . '100 or more then get a ' . $config["arms_currency"]["symbol"] . '5 Voucher.'
		);
		$this->promotion_wizard_list[17] = array(
			'title' => '<span class="keyword">Free Special Item</span>, based on <span class="keyword">User Selected Qualifier</span>. (Free coupon, voucher, etc...)',
			'desc' => '<b><u>Description:</u></b><br />Purchase ' . $config["arms_currency"]["symbol"] . 'XXX or more from selected item and then get a voucher to use for next visit.<br /><br /><b><u>Example</u></b>:<br />Purchase ' . $config["arms_currency"]["symbol"] . '100 or more  from grocery then get a ' . $config["arms_currency"]["symbol"] . '5 Voucher.'
		);
		
		$smarty->assign('promotion_wizard_list', $this->promotion_wizard_list);
		parent::__construct($title, $template);
	}
	
	private function init_load(){
		global $con, $smarty;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select * from branch order by sequence,code", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
		$smarty->assign('branch', $this->branches);	// old promotion.php using this name in smarty, to make template can share in 2 promotion
	}
	
	function _default(){
	    global $con, $smarty;

		//$this->display();
	}
	
	function view(){
	    global $smarty;

	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		if(is_new_id($id)){
            $this->open();
            exit;
		}

		$this->open($branch_id, $id, true);
	}
	
	function open($branch_id = 0, $id = 0, $load_header = true){
        global $con, $sessioninfo, $smarty, $LANG, $config;

		// delete old tmp items
        $con->sql_query("delete from tmp_promotion_mix_n_match_items where (promo_id>1000000000 and promo_id<".strtotime('-1 day').") and user_id=$sessioninfo[id]");
        
        $form = $_REQUEST;
        if(!$id){
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
		}
		
        if($_REQUEST['a']=='view'){
			$this->allow_edit = 0;
			$smarty->assign('allow_edit', 0);
		}	
        
        if(!is_new_id($id) && $branch_id){    // existing promotion
            if($branch_id!=$sessioninfo['branch_id'] && $_REQUEST['a']!='view'){
				header("Location: $_SERVER[PHP_SELF]?a=view&branch_id=$branch_id&id=$id");
			}
            if($load_header){
                $form = load_mix_n_match_header($branch_id, $id, true);
                if(!$form){  // promotion not found
                    display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
				}
                if($_REQUEST['a']!='view')	$this->copy_to_tmp($branch_id, $id);

                if(($form['approved'] || !$form['active']) && $_REQUEST['a']!='view'){
		            header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
					exit;
				}
			}
		}else{  // new promotion
			if(!$id){
                $id=time();
				$form['id']=$id;
				$form['branch_id']=$sessioninfo['branch_id'];
				$form['first_time'] = 1;
				$this->allow_edit = 1;
				$smarty->assign('allow_edit', $this->allow_edit);
			}
			// load data such as sku type, price type, etc...
			load_mnm_required_data();
		}
		
		//print_r($form);

		$items = load_mix_n_match_items_list($branch_id, $id, ($_REQUEST['a']!='view'));
		//print_r($items);
		$smarty->assign('PAGE_TITLE', $this->title.' - '.(is_new_id($id)?'New ':'ID#'.$form['id']));
		$smarty->assign('form', $form);
		$smarty->assign('items', $items);
		
		$this->display('promotion.mix_n_match.open.tpl');
	}
	
	function refresh(){
		global $con;
		
	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
        $this->open($branch_id, $id);
	}
	
	function add_new_mix_n_match_group(){
		global $con, $smarty, $sessioninfo ,$LANG;
		
		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}

		$branch_id = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['promo_id']);
		
		// get max group id
		$con->sql_query("select max(group_id) from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and user_id=".mi($sessioninfo['id']));
		$new_group_id = mi($con->sql_fetchfield(0))+1;
		
		$promo_group['header']['for_member'] = 1;
		$promo_group['header']['for_non_member'] = 1;
		$promo_group['header']['disc_prefer_type'] = 1;
		
		$smarty->assign('group_id', $new_group_id);
		$smarty->assign('promo_group', $promo_group);
		$ret['html'] = $smarty->fetch('promotion.mix_n_match.open.group.tpl');
		$ret['ok'] = 1;
		$ret['new_group_id'] = $new_group_id;
		print json_encode($ret);
	}
	
	function delete_promo_group(){
		global $con, $smarty, $sessioninfo, $LANG;
		
		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
		
		$branch_id = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['promo_id']);
		$group_id = mi($_REQUEST['group_id']);
		
		$con->sql_query("delete from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and group_id=$group_id and user_id=".mi($sessioninfo['id']));
		print "OK";
	}
	
	function ajax_add_discount_item(){
		global $con, $smarty, $sessioninfo, $LANG;
		//print_r($_REQUEST);
		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
        
		$branch_id = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['promo_id']);
		$group_id = mi($_REQUEST['group_id']);
		
		if(!$branch_id) die("Invalid Promotion Branch ID");
		if(!$promo_id) die("Invalid Promotion ID");
		if(!$group_id) die("Invalid Promotion Group ID");
		
		$upd = array();
		$upd['branch_id'] = $branch_id;
		$upd['promo_id'] = $promo_id;
		$upd['group_id'] = $group_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['disc_target_type'] = trim($_REQUEST['disc_target_type']);
		$upd['disc_target_value'] = trim($_REQUEST['disc_target_value']);
		$upd['disc_target_sku_type'] = trim($_REQUEST['disc_target_sku_type']);
		$upd['disc_target_price_type'] = trim($_REQUEST['disc_target_price_type']);
		$upd['disc_target_price_range_from'] = mf($_REQUEST['disc_target_price_range_from']);
		$upd['disc_target_price_range_to'] = mf($_REQUEST['disc_target_price_range_to']);
		if ($_REQUEST['disc_by_inclusive_tax'])
			$upd['disc_by_inclusive_tax'] = trim($_REQUEST['disc_by_inclusive_tax']);
		
		// construct disc_target_info
		if(isset($_REQUEST['disc_target_info']) && is_array($_REQUEST['disc_target_info'])){
			$disc_target_info = $_REQUEST['disc_target_info'];
			while($v = array_shift($disc_target_info)){
				$upd['disc_target_info'][$v] = urldecode(array_shift($disc_target_info));
			}
		}
		//print_r($upd);exit;
		
		// get next sequence num
		$con->sql_query("select max(sequence_num) from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and group_id=$group_id");
		$upd['sequence_num'] = mi($con->sql_fetchfield(0))+1;
		$con->sql_freeresult();
		
		// get sku cost and selling
		if($upd['disc_target_type']=='sku'){
		    $sid = mi($upd['disc_target_value']);
		    
		    $upd['disc_target_info'] = $this->get_mix_n_match_discount_target_info($upd['disc_target_type'], $sid, $branch_id);
			
			if($_REQUEST['include_parent_child']){
				$upd['disc_target_info']['include_parent_child'] = "1";
			}
		    
			/*$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
			where si.id=$sid");
			
			$upd['disc_target_info'] = $con->sql_fetchassoc();*/
		}
		if(isset($upd['disc_target_info'])){
			$upd['disc_target_info'] = serialize($upd['disc_target_info']);
		}
		
		$con->sql_query("insert into tmp_promotion_mix_n_match_items ".mysql_insert_by_field($upd));
		$id = $con->sql_nextid();
		
		$ret['ok'] = 1;
		$ret['html'] = load_mix_n_match_item($branch_id, $id, true, true);
		print json_encode($ret);
	}
	
	function delete_discount_item(){
		global $con, $smarty, $sessioninfo, $LANG;

		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}

        $branch_id = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['promo_id']);
		$item_id = mi($_REQUEST['item_id']);

		if(!$branch_id) die("Invalid Promotion Branch ID");
		if(!$promo_id) die("Invalid Promotion ID");
		if(!$item_id) die("Invalid Promotion Item ID");
		
		$con->sql_query("delete from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and id=$item_id");
		print "OK";
	}
	
	function ajax_add_item_condition_row(){
		global $con, $smarty, $sessioninfo, $LANG;

		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}

        $branch_id = mi($_REQUEST['branch_id']);
		$promo_id = mi($_REQUEST['promo_id']);
		$group_id = mi($_REQUEST['group_id']);
		$item_id = mi($_REQUEST['item_id']);
		$disc_target_type = trim($_REQUEST['disc_target_type']);
		$disc_target_value = trim($_REQUEST['disc_target_value']);
		$condition_row_num = mi($_REQUEST['condition_row_num']);
		
        if(!$branch_id) die("Invalid Promotion Branch ID");
		if(!$promo_id) die("Invalid Promotion ID");
		if(!$item_id) die("Invalid Promotion Item ID");
		if(!$group_id) die("Invalid Promotion Group ID");
		if(!$disc_target_type) die("Invalid Search Type");
		if(!$disc_target_value) die("Invalid Target Value");
		
		$promo_disc_condition = array();
		$promo_disc_condition['item_type'] = $disc_target_type;
		$promo_disc_condition['item_value'] = $disc_target_value;
		$promo_disc_condition['item_info'] = get_disc_condition_item_info($disc_target_type, $disc_target_value);
		
		if($disc_target_type == "sku" && $_REQUEST['include_parent_child']){
			$promo_disc_condition['include_parent_child'] = "1";
		}
		//print_r($promo_disc_condition);
		$smarty->assign('group_id', $group_id);
		$smarty->assign('item_id', $item_id);
		$smarty->assign('condition_row_num', $condition_row_num);
		$smarty->assign('promo_disc_condition', $promo_disc_condition);
		
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('promotion.mix_n_match.open.promo_item_row.disc_condition.tpl');
		
		print json_encode($ret);
	}
	
	function confirm(){
		$this->save(true);
	}
	
	private function save_tmp_items($form){
    	global $con, $smarty, $sessioninfo, $config;
    	
    	$branch_id = mi($form['branch_id']);
    	$promo_id = mi($form['id']);
    	
    	// truncate old items first
    	$con->sql_query("delete from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and user_id=".mi($sessioninfo['id']));
    	
    	if($form['disc_target_type']){
			foreach($form['disc_target_type'] as $group_id => $item_list){
			    $sequence = 0;
			    foreach($item_list as $item_id=>$disc_target_type){
			        if(!$item_id)   continue;
			        $sequence++;
			        
                    $upd = array();
					$upd['branch_id'] = $branch_id;
					$upd['id'] = $item_id;
					$upd['promo_id'] = $promo_id;
					$upd['group_id'] = $group_id;
					$upd['user_id'] = $sessioninfo['id'];
					$upd['sequence_num'] = $sequence;
					$upd['disc_target_type'] = $disc_target_type;
					$upd['disc_target_value'] = $form['disc_target_value'][$group_id][$item_id];
					$upd['disc_target_info'] = serialize($form['disc_target_info'][$group_id][$item_id]);
					$upd['disc_target_sku_type'] = $form['disc_target_sku_type'][$group_id][$item_id];
					$upd['disc_target_price_type'] = $form['disc_target_price_type'][$group_id][$item_id];
					$upd['disc_target_price_range_from'] = $form['disc_target_price_range_from'][$group_id][$item_id];
					$upd['disc_target_price_range_to'] = $form['disc_target_price_range_to'][$group_id][$item_id];
					$upd['disc_condition'] = serialize($form['disc_condition'][$group_id][$item_id]);
					$upd['disc_by_type'] = $form['disc_by_type'][$group_id][$item_id];
					$upd['disc_by_value'] = $form['disc_by_value'][$group_id][$item_id];
					$upd['disc_by_qty'] = $form['disc_by_qty'][$group_id][$item_id];
					$upd['disc_by_inclusive_tax'] = $form['disc_by_inclusive_tax'][$group_id][$item_id];
					$upd['disc_limit'] = $form['disc_limit'][$group_id][$item_id];
					$upd['loop_limit'] = $form['loop_limit'][$group_id][$item_id];
					$upd['receipt_limit'] = $form['receipt_limit'][$group_id];
					$upd['disc_prefer_type'] = $form['disc_prefer_type'][$group_id];
					$upd['for_member'] = $form['for_member'][$group_id];
					$upd['for_non_member'] = $form['for_non_member'][$group_id];
					$upd['follow_sequence'] = $form['follow_sequence'][$group_id];
					$upd['prompt_available'] = mi($form['prompt_available'][$group_id]);
					$upd['control_type'] = $form['control_type'][$group_id];
					$upd['qty_from'] = $form['qty_from'][$group_id][$item_id];
					
					// override point
					$upd['item_category_point_inherit_data'] = $form['item_category_point_inherit_data'][$group_id];
					if($upd['item_category_point_inherit_data']['inherit']!='set'){
						$arr['inherit'] = $upd['item_category_point_inherit_data']['inherit'];
						unset($upd['item_category_point_inherit_data']);
						$upd['item_category_point_inherit_data'] = $arr;
						//$upd['item_category_point_inherit_data'] = array();
					}
					$upd['item_category_point_inherit_data'] = serialize($upd['item_category_point_inherit_data']);
					
					$upd['item_remark'] = $form['item_remark'][$group_id][$item_id];
					$upd['receipt_description'] = $form['receipt_description'][$group_id][$item_id];
					
					// member type
					$upd['for_member_type'] = serialize($form['for_member_type'][$group_id]);
					
					$con->sql_query("replace into tmp_promotion_mix_n_match_items ".mysql_insert_by_field($upd));
				}
			}
		}
	}
	
	function save($is_confirm = false){
		global $con, $smarty, $sessioninfo, $LANG;

        if(!$this->allow_edit){
			display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
		
		$form = $_REQUEST;

		$branch_id = mi($form['branch_id']);
		$id = mi($form['id']);
		//print_r($form);exit;
		$this->save_tmp_items($form);
		
		//check Promo status.
		$last_approval = false;
		if($is_confirm && !is_new_id($id)){
		    $con->sql_query("select status, approved from promotion where id=$id and branch_id=$branch_id");
		    if($r=$con->sql_fetchrow()){
		       if(($r['status']>0 && $r['status'] !=2) || $r['approved']){
		            // promotion already confirm
		            display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['PROMO_ALREADY_CONFIRM_OR_APPROVED'], $promo_id));
				}
			}
			else{
			    // promotion not found
			    display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['PROMO_NOT_FOUND'], $promo_id));
			}
		}
		
		if($form['date_from']=='' || strtotime($form['date_from'])<=0)	$err[]=$LANG['PROMO_INVALID_DATE_FROM'];
		if($form['date_to']=='' || strtotime($form['date_to'])<=0)	$err[]=$LANG['PROMO_INVALID_DATE_TO'];

		if (strtotime($form['date_from']) > strtotime($form['date_to'])){
			$err[]="Date From cannot greater than Date To";
		}

		// check if promo item list is empty
		$qc = $con->sql_query("select id from tmp_promotion_mix_n_match_items
		where promo_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		if ($con->sql_numrows()<=0){
			$err[]="There are no items in your promotion.";
		}
		$con->sql_freeresult();
		
		// check approval flow
		$form['status'] = 0;
		if(!$err && $is_confirm){
		   	$params = array();
	      	$params['type'] = 'PROMOTION';
	      	$params['branch_id'] = $branch_id;
	      	$params['user_id'] = $sessioninfo['id'];
	      	$params['reftable'] = 'promotion';
	      	if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id'];
	      	$astat = check_and_create_approval2($params, $con);

			if(!$astat){
				$err[]= $LANG['PROMO_NO_APPROVAL_FLOW'];
			}
			else{
				$form['approval_history_id']=$astat[0];
	   			if($astat[1]=='|') $last_approval=true;
			}

			$form['status']=1;
		}
		
		if($err){
		    $smarty->assign('err', $err);
			$this->open($branch_id, $id, false);
			exit;
		}
		if($form['branch_id']!=1)	$form['promo_branch_id'] = array($form['branch_id']=>get_branch_code($form['branch_id']));	// fix create by branch no promo branch
		// store data to save for mysql
		$upd1 = array();
		$upd1['title'] = $form['title'];
		$upd1['date_from'] = $form['date_from'];
		$upd1['date_to'] = $form['date_to'];
		$upd1['time_from'] = $form['time_from'];
		$upd1['time_to'] = $form['time_to'];
		$upd1['last_update'] = 'CURRENT_TIMESTAMP';
		$upd1['promo_branch_id'] = $form['promo_branch_id'];
		$upd1['promo_type'] = 'mix_and_match';
		$upd1['approval_history_id'] = mi($form['approval_history_id']);
		$upd1['status'] = mi($form['status']);
		$upd1['category_point_inherit_data'] = $form['category_point_inherit_data'];
		$upd1['category_point_inherit'] = trim($form['category_point_inherit']);
		
		// store old timestamp id
		$tmp_promo_id = $id;
		
		// create header
		$upd1['promo_branch_id'] = serialize($upd1['promo_branch_id']);
		
		if($upd1['category_point_inherit']!='set')	$upd1['category_point_inherit_data'] = array();
		$upd1['category_point_inherit_data'] = serialize($upd1['category_point_inherit_data']);
		
		if(is_new_id($id)){
			$upd1['branch_id'] = $branch_id;
			$upd1['added'] = 'CURRENT_TIMESTAMP';
			$upd1['user_id'] = $sessioninfo['id'];
            $upd1['id'] = create_new_promo($branch_id, $upd1);
            
			$id = $upd1['id'];
		}else{
            $con->sql_query("update promotion set ".mysql_update_by_field($upd1)." where branch_id=$branch_id and id=$id");
		}
		
		// create items
		// get from tmp first
		$qpi = $con->sql_query("select * from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$tmp_promo_id and user_id=".mi($sessioninfo['id'])." order by id");
		$first_item_id = 0;
		while($r = $con->sql_fetchassoc($qpi)){ // loop tmp items
			$upd2 = $r;
			$upd2['promo_id'] = $id;
			unset($upd2['id']);
			
			// insert into real item table
			$con->sql_query("replace into promotion_mix_n_match_items ".mysql_insert_by_field($upd2));
			if(!$first_item_id) $first_item_id = $con->sql_nextid();
		}
		//$con->sql_freeresult();
		
		if($first_item_id){
			$con->sql_query("delete from promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$id and id<$first_item_id");
		}
		// delete from temp
		$con->sql_query("delete from tmp_promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$tmp_promo_id and user_id=".mi($sessioninfo['id']));
		
		if($is_confirm){
		    log_br($sessioninfo['id'], 'PROMOTION', $id, "Promotion Confirmed (ID#$id)");
		    
		    $to = get_pm_recipient_list2($id,$form['approval_history_id'],0, 'confirmation',$branch_id,'promotion');
		    
			if ($last_approval){
				$con->sql_query("update promotion set active=1, approved=1 where branch_id=$branch_id and id=$id");
				$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ('$astat[0]', $branch_id, $sessioninfo[id], 1, 'Approved')");
				send_pm2($to, "Promotion Confirmed (ID#$id)", "promotion.php?a=view&id=$id&branch_id=$branch_id");
				header("Location: /".$this->redirect_php."?type=approved&id=$id");
			}
			else{
				$con->sql_query("update branch_approval_history set ref_id=$id where id=$form[approval_history_id] and branch_id=$branch_id");
				send_pm2($to, "Promotion Approval (ID#$id)", "promotion.php?a=view&id=$id&branch_id=$branch_id", array('module_name'=>'promotion'));
				header("Location: /".$this->redirect_php."?type=confirm&id=$id");
			}
		}
		else{
			log_br($sessioninfo['id'], 'Promotion', $id, "Promotion Saved (ID#$id)");
			header("Location: /".$this->redirect_php."?type=save&id=$id");
		}
	}
	
	private function copy_to_tmp($branch_id, $promo_id){
		global $con, $sessioninfo;

		// escape integer
        $branch_id = mi($branch_id);
        $promo_id = mi($promo_id);
        
		//delete ownself items in tmp table
		$tmp_tbl = 'tmp_promotion_mix_n_match_items';
		$tbl = 'promotion_mix_n_match_items';
		$con->sql_query("delete from $tmp_tbl where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]");

		//update items
		$qpi = $con->sql_query("select * from $tbl where promo_id=$promo_id and branch_id = $branch_id order by id");
		while($r = $con->sql_fetchassoc($qpi)){
			$r['user_id'] = $sessioninfo['id'];
			unset($r['id']);

			$con->sql_query("insert into $tmp_tbl ".mysql_insert_by_field($r));
		}
		//$con->sql_freeresult($qpi);
	}
	
	function delete(){
		global $con, $smarty, $sessioninfo, $LANG;

        if(!$this->allow_edit){
			display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}

		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$promo_id = mi($form['id']);

        if ($sessioninfo['level']<9999)	$usrcheck = " and user_id=$sessioninfo[id]";

		// delete tmp items
        $con->sql_query("delete from tmp_promotion_mix_n_match_items where promo_id=$promo_id and branch_id=$branch_id and user_id=$sessioninfo[id]");
        
		if (!is_new_id($promo_id)){
	        $con->sql_query("update promotion
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0, approved=0
	where id=$promo_id and branch_id=$branch_id $usrcheck");
		}
		
		if ($con->sql_affectedrows()>0){
			log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion Deleted (ID#$promo_id)");
			header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_DELETED'], $promo_id)));
		}
		else
			header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_NOT_DELETED'], $promo_id)));
		exit;
	}
	
	function cancel(){
		global $con, $smarty, $sessioninfo, $LANG;

        if(!$this->allow_edit){
			display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
        
		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$promo_id = mi($form['id']);
		
		$con->sql_query("update promotion
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=5, active=0, approved=0
	where id=$promo_id and branch_id=$branch_id");
	
	    if($form['approval_history_id']){
            $con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values (".ms($form['approval_history_id']).", $branch_id, $sessioninfo[id], 5, 'Cancelled')");
		}
		
		if ($con->sql_affectedrows()>0){
			log_br($sessioninfo['id'], 'PROMOTION', $promo_id, "Promotion Cancelled (ID#$promo_id)");
			header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_CANCELLED'], $promo_id)));
		}
		else
		header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_NOT_CANCELLED'], $promo_id)));
	}
	
	function copy_promotion(){
		global $con, $smarty, $sessioninfo, $LANG;
		
		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$promo_id = mi($form['id']);
		
		if ($sessioninfo['branch_id'] != $branch_id){
			header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_INVALID_COPY'])));
			exit;
		}
		
        if(!$this->allow_edit){
			display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}

		// select the required filed from promotion to copy
		$con->sql_query("select title, date_from, date_to, time_from, time_to, promo_branch_id, promo_type from promotion where branch_id=$branch_id and id=$promo_id");
		$original_promo = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$original_promo){
            header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id)));
			exit;
		}
		
		$new_promo = $original_promo;
		$new_promo['user_id'] = $sessioninfo['id'];
		$new_promo['branch_id'] = $sessioninfo['branch_id'];
		$new_promo['added'] = $new_promo['last_update'] = 'CURRENT_TIMESTAMP';
        $new_promo['active'] = 1;
        
        // insert header
        $new_promo_id = create_new_promo($sessioninfo['branch_id'], $new_promo);
		if(!$new_promo_id){ // failed to copy
            header("Location: /".$this->redirect_php."?msg=".urlencode($LANG['PROMO_NOT_COPY']));
            exit;
		}
		
		// copy items
		$qpi = $con->sql_query("select * from promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id order by id");
		while($r = $con->sql_fetchassoc($qpi)){
			unset($r['id']);
			$r['promo_id'] = $new_promo_id;
			$r['user_id'] = $sessioninfo['id'];
			$r['branch_id'] = $sessioninfo['branch_id'];
			
			$con->sql_query("insert into promotion_mix_n_match_items ".mysql_insert_by_field($r));
		}
		$con->sql_freeresult($qpi);
		
		log_br($sessioninfo['id'], 'PROMOTION', $new_promo_id, "Promotion Copy (ID#$promo_id -> ID#$new_promo_id)");
		header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_COPY'], $promo_id, $new_promo_id)));
	}
	
	function cancel_group(){
		global $con, $smarty, $sessioninfo, $LANG;

		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$promo_id = mi($form['id']);

		//print_r($_REQUEST);exit;
		// check privilege
		if (!privilege('PROMOTION_CANCEL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PROMOTION_CANCEL', BRANCH_CODE), "/index.php");
		
		if(!$this->allow_edit){
			display_redir("/".$this->redirect_php, "Promotion", sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
		
		if(!$form['group_selection']){
            header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_GROUP_NOT_CANCEL'], $promo_id)));
            exit;
		}
		
		// select the required filed from promotion to copy
		$con->sql_query("select title, date_from, date_to, time_from, time_to, promo_branch_id, promo_type from promotion where branch_id=$branch_id and id=$promo_id");
		$original_promo = $con->sql_fetchassoc();
		$con->sql_freeresult();

		if(!$original_promo){
            header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_NOT_FOUND'], $promo_id)));
			exit;
		}
		
		// construct group id
		$group_id_list = array();
		foreach($form['group_selection'] as $group_id){
		    $group_id = mi($group_id);
		    if(!$group_id)  continue;
            $group_id_list[] = $group_id;
		}

        // cannot found group id
		if(!$group_id_list){
            header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_GROUP_NOT_CANCEL'], $promo_id)));
            exit;
		}
		
		// construct new promo array
		$new_promo = $original_promo;
		$new_promo['user_id'] = $sessioninfo['id'];
		$new_promo['branch_id'] = $sessioninfo['branch_id'];
		$new_promo['added'] = $new_promo['last_update'] = 'CURRENT_TIMESTAMP';
        $new_promo['active'] = 1;

		// select items list from original promotion, which need to cancel
        $con->sql_query("select * from promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and group_id in (".join(',', $group_id_list).") order by group_id, id");
		$items = array();
		while($r = $con->sql_fetchassoc()){
			unset($r['id']);
			unset($r['user_id']);
			unset($r['promo_id']);
			$items[] = $r;
		}
		$con->sql_freeresult();

		// no item in promotion to be cancel
		if(!$items){
            header("Location: /".$this->redirect_php."?msg=".urlencode(sprintf($LANG['PROMO_GROUP_NOT_CANCEL'], $promo_id)));
            exit;
		}
		
        // create new promotion
        $new_promo_id = create_new_promo($sessioninfo['branch_id'], $new_promo);
		if(!$new_promo_id){ // failed to create new promo
            header("Location: /".$this->redirect_php."?msg=".urlencode($LANG['PROMO_NOT_COPY']));
            exit;
		}
		
		// insert cancelled items to new promotion
		foreach($items as $r){
			$r['promo_id'] = $new_promo_id;
			$r['user_id'] = $sessioninfo['id'];
			$r['branch_id'] = $sessioninfo['branch_id'];
			$con->sql_query("insert into promotion_mix_n_match_items ".mysql_insert_by_field($r));
		}
		unset($items);
		
		// delete cancelled item from original promotion
		$con->sql_query("delete from promotion_mix_n_match_items where branch_id=$branch_id and promo_id=$promo_id and group_id in (".join(',', $group_id_list).") order by group_id, id");
		
		// make update to activate trigger
		$con->sql_query("update promotion set last_update=CURRENT_TIMESTAMP where branch_id=$branch_id and id=$promo_id");
		
		log_br($sessioninfo['id'], 'PROMOTION', $new_promo_id, "Promotion Group Cancelled and move to new promotion (ID#$promo_id -> ID#$new_promo_id)");
		header("Location: /".$this->redirect_php."?&msg=".urlencode(sprintf($LANG['PROMO_GROUP_CANCELLED_AND_COPIED'], $promo_id, $new_promo_id)));
	}
	
	function do_print(){
		$this->print_promotion();
	}
	
	function print_promotion(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$promo_id = mi($form['id']);
		$print_by_branch = mi($_REQUEST['print_by_branch']);
		if($print_by_branch){
			$print_promo_bid = $_REQUEST['print_promo_bid'];
			
			if(!$print_promo_bid || !is_array($print_promo_bid)){
				display_redir($_SERVER['PHP_SELF'], "Failed to Print Promotion ID#$promo_id", "No branch is selected");
			}
		}
		
		//print_r($_REQUEST);exit;
		
		// load promotion header
		$form = load_mix_n_match_header($branch_id, $promo_id, true);
		
		// load promotion items
		$items = load_mix_n_match_items_list($branch_id, $promo_id);
		
		// select additional info for printing
		// branch info
		$con->sql_query("select * from branch where id=$branch_id");
		$branch_info = $con->sql_fetchassoc();
		$con->sql_freeresult();

		// owner user fullname
		$con->sql_query("select fullname from user where id=".mi($form['user_id']));
		$r = $con->sql_fetchrow();
		$form['fullname'] = $r[0];

		//print_r($items);
		$smarty->assign("branch_info", $branch_info);
		$smarty->assign('form', $form);
		$smarty->assign('items', $items);
		
		$print_tpl = "promotion.mix_n_match.print.tpl";
		if($config['promotion_mix_n_match_alt_print_tpl']) $print_tpl = $config['promotion_mix_n_match_alt_print_tpl'];
		
		if($print_promo_bid){
			foreach($print_promo_bid as $promo_bid){
				$con->sql_query("select * from branch where id = $promo_bid");
				$branch_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
				$smarty->assign("branch_info", $branch_info);
				
				$smarty->assign('print_promo_bid', $promo_bid);
				$this->display($print_tpl);
			}
		}else{
			$this->display($print_tpl);
		}
		
	}
	
	function load_wizard_screen(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$form = $_REQUEST;
		
		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
		
		switch($form['screen']){
			case 'main':
				$this->load_wizard_main_screen();
				break;
			case 'discount_target_screen':
				$this->load_discount_target_screen();
				break;
			//case 'disc_condition_screen':
				//$this->load_disc_condition_screen();
				//break;
			default:
				die("Invalid Navigation Screen!");
				break;
		}
	}
	
	private function load_wizard_main_screen(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$form = $_REQUEST;
		
		$smarty->assign('promo_data', $form);
		$this->display('promotion.mix_n_match.open.wizard_dialog.type_list.tpl');
	}
	
	private function load_discount_target_screen(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$form = $_REQUEST;
	
		$pwid = mi($form['pwid']);
		
		// preset promotion item data
		$this->get_promotion_wizard_preset_data($pwid, $form);
		
		if(!$form['invalid_pwid'] && $form['item_list']){
			foreach($form['item_list'] as $k=>$item){
				if($item['disc_target_type'] && $item['disc_target_value']){
					$item['item_info'] = get_disc_condition_item_info($item['disc_target_type'], $item['disc_target_value']);
					$item['disc_target_info'] = $this->get_mix_n_match_discount_target_info($item['disc_target_type'], $item['disc_target_value']);
				}
				
				$form['item_list'][$k] = $item;
			}	
		}	
		$smarty->assign('promo_data', $form);
		$smarty->assign('is_under_gst', check_mix_n_match_is_under_gst($form['branch_id']));
		
		$this->display('promotion.mix_n_match.open.wizard_dialog.discount_target_screen.tpl');
	}
	
	private function get_promotion_wizard_preset_data($pwid, &$form, $params = array()){
		global $con, $smarty, $sessioninfo, $LANG, $config;	
		
		switch($pwid){
			case 1:	// buy 1 free 1
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='foc';
					$item['disc_by_qty'] = 1;
								
					// disc condition		
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_limit'] = 1;
				break;
			case 2: // Discount on Receipt Total Amount, based on Receipt Total Amount. 
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'receipt';
					$item['disc_by_type'] ='amt';
					$item['disc_by_value'] = 10;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 3: // Discount on Category Total Amount, based on Total Amount of Category Qualifier. 
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'category';
					$item['disc_by_type'] ='amt';
					$item['disc_by_value'] = 5;
					$item['disc_by_qty'] = -2;	// group total
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 4: // Brand Total Amount, based on Total Amount of Brand Qualifier. 
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'brand';
					$item['disc_by_type'] ='amt';
					$item['disc_by_value'] = 5;
					$item['disc_by_qty'] = -2;	// group total
					
					// disc condition		
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 5: // Discount on Selected Item Total Amount, based on Total Qty of Item Qualifier. 
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='amt';
					$item['disc_by_value'] = 5;
					$item['disc_by_qty'] = -2;	// group total
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 6: // Discount on Category+Brand Total Amount, based on Total Amount of Category+Brand Qualifier. 
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'category_brand';
					$item['disc_by_type'] ='amt';
					$item['disc_by_value'] = 5;
					$item['disc_by_qty'] = -2;	// group total
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_edit_condition_value'] = true;
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 7: // Discount on Selected Item, based on Total Qty of Item Qualifier. (Buy Y Qty and get some of it discount)
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 50;
					$item['disc_by_qty'] = 2;
					//$item['disc_limit'] = 2;
					//$item['qty_from'] = 1;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				//$form['allowed_qty_from'] = 1;
				$form['allowed_disc_limit'] = 1;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 8: // Discount on Selected Item, based on Total Qty of Item Qualifier. (Start discount on Y Qty onwards)
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 20;
					$item['disc_by_qty'] = -1;
					//$item['disc_limit'] = 2;
					$item['qty_from'] = 3;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_by_type'] = array('amt', 'per');
				//$form['allowed_qty_from'] = 1;
				//$form['allowed_disc_limit'] = 1;
				//$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 9: // Discount on Selected Item, based on Total Qty of Item Qualifier, Discount with Bundled Price. 
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='bundled_price';
					$item['disc_by_value'] = 24;
					$item['disc_by_qty'] = 2;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
					
					// item 2
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='bundled_price';
					$item['disc_by_value'] = 33;
					$item['disc_by_qty'] = 3;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>3));
					
					$form['item_list'][2] = $item;
				}
				$form['allow_add_discount_target'] = true;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 10: // Package Promotion Discount.
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 20;
					$item['disc_by_qty'] = 3;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
					
					// item 2
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 30;
					$item['disc_by_qty'] = 5;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>5));
					
					$form['item_list'][2] = $item;
					
					// item 3
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 40;
					$item['disc_by_qty'] = 8;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>8));
					
					$form['item_list'][3] = $item;
				}

				$form['allow_delete_discount_target'] = true;
				$form['allow_add_discount_target'] = true;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 11: // Step Promotion Discount. 
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 10;
					//$item['qty_from'] = 3;
					$item['disc_by_qty'] = -2;
					//$item['disc_limit'] = 3;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
					
					// item 2
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 15;
					//$item['qty_from'] = 6;
					$item['disc_by_qty'] = -2;
					//$item['disc_limit'] = 4;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>6));
					
					$form['item_list'][2] = $item;
					
					// item 3
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 20;
					//$item['qty_from'] = 10;
					$item['disc_by_qty'] = -2;
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>10));
					
					$form['item_list'][3] = $item;
				}
			
				$form['receipt_limit'] = 1;
				//$form['allowed_qty_from'] = 1;
				//$form['allowed_disc_limit'] = 1;
				$form['allow_add_discount_target'] = true;
				$form['allow_delete_discount_target'] = true;
				//$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				//$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 12: // Range Promotion Discount. 
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 10;
					$item['qty_from'] = 4;
					$item['disc_by_qty'] = 3;
					$item['disc_limit'] = 3;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					$form['item_list'][1] = $item;
					
					// item 2
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 15;
					$item['qty_from'] = 7;
					$item['disc_by_qty'] = 4;
					$item['disc_limit'] = 4;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>7));
					
					$form['item_list'][2] = $item;
					
					// item 3
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 20;
					$item['qty_from'] = 11;
					$item['disc_by_qty'] = -1;
					
					// disc condition
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form, array('condition_value'=>11));
		
					$form['item_list'][3] = $item;
				}
				$form['allow_add_discount_target'] = true;
				$form['allow_delete_discount_target'] = true;
				$form['allowed_qty_from'] = 1;
				$form['allowed_disc_limit'] = 1;
				//$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 13: // Entitle Special Price for selected item, based on User Selected Qualifier.
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='fixed_price';
					$item['disc_by_value'] = 4;
					
					// disc condition
					$disc_condition = array();
					$disc_condition['item_type'] = 'receipt';
					$disc_condition['rule'] = 'over_equal';
					$disc_condition['condition_type'] = 'amt';
					$disc_condition['condition_value'] = 50;
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				//$form['allowed_disc_limit'] = 1;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				$form['allow_edit_condition_item_type'] = 1;
				$form['allowed_disc_condition_item_type'] = array('receipt');
				break;
			case 14: // Discount on Selected Item, based on User Selected Qualifier.
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_by_type'] ='per';
					$item['disc_by_value'] = 10;
					$item['disc_by_qty'] = 1;	// group total
					
					// disc condition
					$temp = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					$temp2 = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					$item['disc_condition'][1] = $temp[1];
					$item['disc_condition'][2] = $temp2[1];
					
					$form['item_list'][1] = $item;
				}
				
				$form['allowed_disc_limit'] = true;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				
				$form['allowed_disc_target_type'] = array('sku', 'category', 'brand', 'category_brand');
				$form['allowed_disc_by_type'] = array('amt', 'per');
				break;
			case 15:	// buy 1 free 1 (different item)
				if(!$form['item_list']){	// first time, still no item in the list
					$item = array();
					$item['disc_target_type'] = 'sku';
					$item['disc_by_type'] ='foc';
					$item['disc_by_qty'] = 1;
								
					// disc condition		
					$item['disc_condition'] = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				$form['allowed_disc_limit'] = 1;
				break;
			case 16: // Free Special Item, based on Receipt Total Amount. 
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] ='special_foc';
					$item['disc_target_info']['description'] = 'Voucher ' . $config["arms_currency"]["symbol"] . '5';
					$item['disc_by_type'] ='foc';
					$item['disc_by_qty'] = 1;
					
					// disc condition
					$temp2 = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					$item['disc_condition'] =$this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				
				//$form['allowed_disc_limit'] = true;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			case 17: // Free Special Item, based on Receipt Total Amount. 
				if(!$form['item_list']){	// first time, still no item in the list
					// item 1
					$item = array();
					$item['disc_target_type'] ='special_foc';
					$item['disc_target_info']['description'] = 'Voucher ' . $config["arms_currency"]["symbol"] . '5';
					$item['disc_by_type'] ='foc';
					$item['disc_by_qty'] = 1;
					
					// disc condition
					$temp2 = $this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					$item['disc_condition'] =$this->get_promotion_wizard_preset_disc_condition_data($pwid, $form);
					
					$form['item_list'][1] = $item;
				}
				
				$form['allowed_disc_limit'] = true;
				$form['allowed_disc_by_qty']['not_allowed'][-1] = 1; // not allow all items
				$form['allowed_disc_by_qty']['not_allowed'][-2] = 1; // not allow group total
				break;
			default:
				print "Unknown Promotion Type.";
				$form['invalid_pwid'] = 1;
				break;
		}
	}
	
	private function get_promotion_wizard_preset_disc_condition_data($pwid, &$form, $params = array()){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		switch($pwid){
			case 1:	// buy 1 free 1
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 2;
				$disc_row[1] = $disc_condition;
				
				return $disc_row;
				break;
			case 2: // Discount on Receipt Total Amount, based on Receipt Total Amount. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'receipt';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 100;
				$disc_row[1] = $disc_condition;
				
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 3: // Discount on Category Total Amount, based on Total Amount of Category Qualifier. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'category';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 50;
				$disc_row[1] = $disc_condition;
				
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 4: // Brand Total Amount, based on Total Amount of Brand Qualifier. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'brand';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 50;
				$disc_row[1] = $disc_condition;
				
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 5: // Discount on Selected Item Total Amount, based on Total Qty of Item Qualifier.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 3;
				$disc_row[1] = $disc_condition;
					 
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 6: // Discount on Category+Brand Total Amount, based on Total Amount of Category+Brand Qualifier.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'category_brand';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 50;
				$disc_row[1] = $disc_condition;
					 
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 7: // Discount on Selected Item, based on Total Qty of Item Qualifier. (Buy Y Qty and get some of it discount)
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 3;
				$disc_row[1] = $disc_condition;
					 
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 8: // Discount on Selected Item, based on Total Qty of Item Qualifier. (Start discount on Y Qty onwards)
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 3;
				$disc_row[1] = $disc_condition;
					 
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 9: // Discount on Selected Item, based on Total Qty of Item Qualifier, Discount with Bundled Price. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = isset($params['condition_value']) ? $params['condition_value'] : 2;
				$disc_row[1] = $disc_condition;
				
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 10: // Package Promotion Discount.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = isset($params['condition_value']) ? $params['condition_value'] : 3;
				$disc_row[1] = $disc_condition;
					
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 11: // Step Promotion Discount.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = isset($params['condition_value']) ? $params['condition_value'] : 3;
				$disc_row[1] = $disc_condition;
					
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 12: // Range Promotion Discount. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = isset($params['condition_value']) ? $params['condition_value'] : 4;
				$disc_row[1] = $disc_condition;
					
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 13: // Entitle Special Price for selected item, based on User Selected Qualifier.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'receipt';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 50;
				$disc_row[1] = $disc_condition;
					
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 14: // Discount on Selected Item, based on User Selected Qualifier.
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 1;
				$disc_row[1] = $disc_condition;
					
				$form['allow_edit_condition_item_type'] = true;
				$form['allowed_disc_condition_item_type'] = array('sku', 'category', 'brand', 'category_brand');
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 15:	// buy 1 free 1 (diff item)
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'sku';
				$disc_condition['rule'] = 'every';
				$disc_condition['condition_type'] = 'qty';
				$disc_condition['condition_value'] = 1;
				$disc_row[1] = $disc_condition;
				
				$form['allow_edit_condition_item_type'] = true;
				return $disc_row;
				break;
			case 16: // Free Special Item, based on Receipt Total Amount. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['item_type'] = 'receipt';
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 100;
				$disc_row[1] = $disc_condition;
				
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			case 17: // Free Special Item, based on User Selected Qualifier. 
				// disc condition
				$disc_row = array();
				$disc_condition = array();
				$disc_condition['rule'] = 'over_equal';
				$disc_condition['condition_type'] = 'amt';
				$disc_condition['condition_value'] = 100;
				$disc_row[1] = $disc_condition;
				
				$form['allow_edit_condition_item_type'] = true;
				$form['allowed_disc_condition_item_type'] = array('sku', 'category', 'brand', 'category_brand');
				$form['allowed_edit_condition_value'] = true;
				return $disc_row;
				break;
			default:
				return false;
				break;
		}
	}
	
	private function get_mix_n_match_discount_target_info($disc_type, $disc_value, $bid = 0){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		if(!$bid)	$bid = $sessioninfo['branch_id'];
		
		switch($disc_type){
			case 'sku':
				$sid = mi($disc_value);
				if(!$sid)	return;
				$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			where si.id=$sid");
				$ret = $con->sql_fetchassoc();
				break;
		}
		
			
		return $ret;	
	}
	
	function ajax_show_disc_target_item_info(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$disc_target_value = mi($_REQUEST['disc_target_value']);
		$disc_target_type = trim($_REQUEST['disc_target_type']);
		$include_parent_child = mi($_REQUEST['include_parent_child']);
		$display_tpl = $_REQUEST['display_tpl'] ? $_REQUEST['display_tpl'] : 'promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.disc_target_info.tpl';
		if($disc_target_type!='brand' && !$disc_target_value) die("Invalid Target Value");
		
		$promo_item['disc_target_type'] = $disc_target_type;
		$promo_item['item_info'] = get_disc_condition_item_info($disc_target_type, $disc_target_value);
		
		if($disc_target_type=='sku'){
			$promo_item['disc_target_info'] = $this->get_mix_n_match_discount_target_info('sku', $disc_target_value);
			$promo_item['disc_target_info']['include_parent_child'] = $include_parent_child;
		}else{
			$promo_item['disc_target_info'] = get_disc_condition_item_info($disc_target_type, $disc_target_value);
		}
		
		$smarty->assign('promo_item', $promo_item);
		
		$ret['promo_item'] = $promo_item;
		$ret['html'] = $smarty->fetch($display_tpl);
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_pw_add_disc_target_item(){
		global $con, $smarty, $sessioninfo, $LANG, $config;	
	
		$pwid = mi($_REQUEST['pwid']);
		$max_row_num = mi($_REQUEST['max_row_num']);
		
		if(!$max_row_num) die("Invalid Item Row Number");
		$row_num = $max_row_num;
		
		$promo_data = array();
		$this->get_promotion_wizard_preset_data($pwid, $promo_data);
		
		if(!$promo_data || $form['invalid_pwid']){
			die("Invalid Promotion Type");
		}	
		
		$ret = array();
		
		$smarty->assign('promo_data', $promo_data);
		foreach($promo_data['item_list'] as $promo_item){
			$row_num++;
			$smarty->assign('row_num', $row_num);
			$smarty->assign('promo_item', $promo_item);
			$ret['html'] .= $smarty->fetch('promotion.mix_n_match.open.wizard_dialog.discount_target_screen.item_row.tpl');
			break;	// only add 1 row
		}
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_generate_promo_by_promo_wizard(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		//print_r($_REQUEST);	
		
		if(!$this->allow_edit){
			die(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Mix & Match Promotion"));
		}
				
		$form = $_REQUEST;
		
		$branch_id = mi($form['branch_id']);
		$group_id = mi($form['group_id']);
		$promo_id = mi($form['promo_id']);
		
		$items = array();
		
		// no item found
		if(!$form['disc_target_type']){
			die("No item in the list");
		}
		
		// loop all item and perform check
		$row_count = 0;
		$got_disc_limit = false;
		$receipt_limit = 0;
		
		foreach($form['disc_target_type'] as $row_num => $disc_target_type){
			$row_count++;
			
			$item = array();
			$item['disc_target_type'] = trim($disc_target_type);
			$item['disc_target_value'] = $form['disc_target_value'][$row_num];
			$item['disc_target_info'] = $form['disc_target_info'][$row_num];
			$item['disc_by_type'] = $form['disc_by_type'][$row_num];
			$item['disc_by_value'] = $form['disc_by_value'][$row_num];
			$item['disc_by_qty'] = $form['disc_by_qty'][$row_num];
			$item['qty_from'] = $form['qty_from'][$row_num];
			$item['disc_limit'] = $form['disc_limit'][$row_num];
			$item['disc_condition'] = $form['disc_condition'][$row_num];
			$item['disc_by_inclusive_tax'] = $form['disc_by_inclusive_tax'][$row_num];
			
			if($item['disc_target_type']!='brand' && $item['disc_target_type']!='receipt' && $item['disc_target_type']!='special_foc'){
				if(!$item['disc_target_value'])	die("Invalid Discount Target for Condition ".$row_count);
			}
			
			if(!$item['disc_condition']){
				die("Discount Condition ".$row_count." has no discount qualifier.");
			}
			
			foreach($item['disc_condition'] as $condition_row_num=>$dc){
				if($dc['item_type']!='brand' && $dc['item_type']!='receipt'){
					if(!$dc['item_value']) die("Invalid discount qualifier ".$condition_row_num." for discount condition ".$row_count);
				}
			}
			
			if($item['disc_limit'])	$got_disc_limit = true;
			
			if(isset($form['receipt_limit'][$row_num])){
				$receipt_limit = $item['receipt_limit'] = mi($form['receipt_limit'][$row_num]);
			}
			$items[] = $item;
		}
		
		// order by most disc_by_value
		$this->got_disc_limit = $got_disc_limit;	// use for sorting to whether asc or desc
		usort($items, array($this, "sort_disc_by_value"));
		
		$ret = array();
		foreach($items as $upd){
			$upd['branch_id'] = $branch_id;
			$upd['group_id'] = $group_id;
			$upd['promo_id'] = $promo_id;
			$upd['user_id'] = $sessioninfo['id'];
			
			$upd['disc_target_info'] = serialize($upd['disc_target_info']);
			$upd['disc_condition'] = serialize($upd['disc_condition']);
			
			$con->sql_query("insert into tmp_promotion_mix_n_match_items ".mysql_insert_by_field($upd));
			$item_id = $con->sql_nextid();
			
			$ret['html'] .= load_mix_n_match_item($branch_id, $item_id, true, true);
		}
		
		if($receipt_limit){
			$ret['group_setting']['receipt_limit'] = $receipt_limit;
		}
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
	
	private function sort_disc_by_value($a,$b){
		if($a['disc_by_value'] == $b['disc_by_value']) return 0;
		else{
			if($this->got_disc_limit){	// got limit = small to big
				return ($a['disc_by_value'] > $b['disc_by_value'] ? 1 : -1);
			}else{	// no limit = big to small
				return ($a['disc_by_value'] > $b['disc_by_value'] ? -1 : 1);	
			}
		} 
	}
}

$MIX_N_MATCH_PROMOTION = new MIX_N_MATCH_PROMOTION('Mix and Match Promotion');
?>
