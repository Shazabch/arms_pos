<?
/*
11/25/2010 5:20:43 PM Alex
- created by me
11/26/2010 11:48:15 AM Alex
- add pagination
1/13/2011 5:25:56 PM Alex
- add create_user_id and active_user_id
4/12/2011 4:56:25 PM Alex
- change use crypt and md5 encryption
- change print out exactly quantity as user input
4/20/2011 6:18:06 PM Alex
- fix date valid_to bugs
5/4/2011 5:03:00 PM Alex
- change default format and $config['coupon_print_template'] into array
5/12/2011 3:19:30 PM Alex
- add check coupon had printed at ajax_edit_item()
5/16/2011 3:07:21 PM Alex
- add linux and windows print format
3/14/2012 4:39:40 PM Alex
- change prefix to uppercase 
9/11/2013 5:00 PM Justin
- Enhanced to rework some of the components such as add/edit Coupon to use pop up dialog.
11/28/2013 2:41 PM Justin
- Bug fixed on SKU item still exists in SKU item list even it is deleted.

5/11/2015 2:34 PM Andy
- Remove the coupon by percentage.

4/21/2017 10:54 AM Khausalya 
- Enhanced changes from RM to use config setting. 

8/27/2019 1:54 PM Andy
- Added Discount by Percentage.
- Added Minimum Receipt Amount.
- Added Search Coupon feature.
- Enhanced to able to auto get new coupon code.

9/27/2019 11:06 AM Andy
- Fixed cannot remove brand and vendor in coupon condition.

11/28/2019 4:43 PM Andy
- Added feature to control coupon only show for mobile registered member since day X to day Y or need profile info.
- Increase maintenance version checking to 429.

2/11/2020 5:58 PM Andy
- Added new coupon feature "Referral Program".
- Increase maintenance version checking to 448.

2/26/2021 3:13 PM Andy
- Added new coupon default format "ARMS A4 paper v2: 4 x 2" and "ARMS A4 paper v2: 4 x 2 (Greyscale)".

*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
if (!privilege('MST_COUPON')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_COUPON', BRANCH_CODE), "/index.php");
$maintenance->check(448);

class Coupon extends Module{

	//pre-printed format
	var $format=array(
			'LARMS' => array (
			    'description' => 'Linux: ARMS A4 paper: 4 x 2',
				'row' => 4,
				'column' => 2,
				'address' => 'coupon_formats/default.tpl'
			),
			'WARMS' => array (
			    'description' => 'Windows: ARMS A4 paper: 4 x 2',
				'row' => 4,
				'column' => 2,
				'address' => 'coupon_formats/window_default.tpl'
			),
			'ARMS_v2_4x2' => array (
			    'description' => 'ARMS A4 paper v2: 4 x 2',
				'row' => 4,
				'column' => 2,
				'address' => 'coupon_formats/default_v2_4x2.tpl'
			),
			'ARMS_v2_4x2_greyscale' => array (
			    'description' => 'ARMS A4 paper v2: 4 x 2 (Greyscale)',
				'row' => 4,
				'column' => 2,
				'address' => 'coupon_formats/default_v2_4x2_greyscale.tpl'
			)
		);
	var $member_limit_profile_info_list = array(
		'address' => 'Address',
		'postcode' => 'Post Code',
		'state' => 'State',
		'phone_3' => 'Mobile Phone',
		'gender' => 'Gender',
		'dob' => 'Date of Birth'
	);

	function __construct($title){
		global $con, $smarty, $sessioninfo,$config;
	    
		$this->duration_valid = $config['coupon_active_month_duration'] ? $config['coupon_active_month_duration'] : 6;    //by month
 
	  	$dept_sql="select * from category where level=2 and id in (".$sessioninfo['department_ids'].")";
		$con->sql_query($dept_sql);
		$dept=$con->sql_fetchrowset();
                
        $con->sql_freeresult();
		$smarty->assign('dept',$dept);

		$smarty->assign('privilege',$sessioninfo['privilege']);
		$smarty->assign('member_limit_profile_info_list',$this->member_limit_profile_info_list);

		if ($config['coupon_print_template']){
			foreach ($config['coupon_print_template'] as $key => $dummy){
				if ($this->format[$key])	continue;
				else $this->format[$key]=$dummy;
			}
	
//			$this->format= $config['coupon_print_template']+$this->format;
		}
		$smarty->assign('print_format', $this->format);
 		parent::__construct($title);
	}

	function _default(){
	
		if (!$_REQUEST['valid_from'])
			$_REQUEST['valid_from']=date('Y-m-d');
	
		if (!$_REQUEST['valid_to'])
		    $_REQUEST['valid_to']=date('Y-m-d',strtotime("+$this->duration_valid month", strtotime($_REQUEST['valid_from'])));

        $_REQUEST['print_type']='amount';
        
		$this->display();
        exit;
	}

	function ajax_load_coupon_list(){
		global $con,$smarty,$config, $sessioninfo;

       	switch ($_REQUEST['t'])
		{
			case 1: // All coupons
				break;
			
			case 2: // Activated Coupons
				$filter[] = "coupon.active=1";
				break;

			case 3: // Inactivated Coupons
				$filter[] = "coupon.active=0";
				break;
			default: // search
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "coupon.id=".ms($str);
				$filter_or[] = "coupon.code like ".ms('%'.$str.'%');
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
		}

		$filter[]="(coupon.dept_id in (".$sessioninfo['department_ids'].") or coupon.dept_id=0)";

		if ($filter){
			$where = " where ".join(" and ", $filter);
		}
		
		// pagination-------------------------->
		
		$start = intval($_REQUEST['s']);

		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else{
			if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
				else	$sz = 25;
		}
		$con->sql_query("select count(*)
						from coupon	$where");
		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz)
		{
		    if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"COUPON_MAIN.list_sel($_REQUEST[t],this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
			{
				$pg .= "<option value=$i";
				if ($i == $start)
				{
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	  	}
		//--------------------------------------------------------->
//DATE(coupon.valid_from) as valid_from, DATE_FORMAT(coupon.valid_from,'%H:%i') as valid_time_from, DATE(coupon.valid_to) as valid_to, DATE_FORMAT(coupon.valid_to,'%H:%i') as valid_time_to,
		$sql="select coupon.*,
			ifnull(category.description , 'All') as dept_desc, brand.description as brand_desc, vendor.description as vendor_desc, u1.u as user_name
			from coupon
			left join category on coupon.dept_id=category.id
			left join brand on coupon.brand_id=brand.id
			left join vendor on coupon.vendor_id=vendor.id
			left join user u1 on coupon.create_user_id=u1.id
			$where
			order by coupon.id desc limit $start,$sz";

		$q1 = $con->sql_query($sql);
		
		$data = array();
		while($r = $con->sql_fetchassoc($q1)){
			$r['si_list'] = unserialize($r['si_list']);
			
			if($r['si_list']){
				$si_code_list = array();
				$q2 = $con->sql_query("select * from sku_items where id in (".join(",", $r['si_list']).")");
				
				while($r1 = $con->sql_fetchassoc($q2)){
					$si_code_list[] = $r1;
				}
				$con->sql_freeresult($q2);
				
				$r['si_list'] = $si_code_list;
			}
			
			if($r['is_print']){
				// Get printed items list
				$q_ci = $con->sql_query("select ci.*, user.u as printed_by
					from coupon_items ci
					left join user on user.id=ci.user_id
					where ci.branch_id=".mi($r['branch_id'])." and ci.coupon_id=".mi($r['id'])." order by ci.added desc");
				while($ci = $con->sql_fetchassoc($q_ci)){
					$r['coupon_items_list'][] = $ci;
				}
				$con->sql_freeresult($q_ci);
			}
			
			$data[] = $r;
		}
        $con->sql_freeresult($q1);

		$smarty->assign('details', $data);		
		$smarty->display("masterfile_coupon.list.tpl");
	}

	function ajax_delete_item(){
        global $con, $LANG, $sessioninfo;
   		return;

   		$form = $_REQUEST;

        $branch_id=mi($sessioninfo['branch_id']);
        $id=$form['id'];

		//check exist
		$sql="select coupon.*, ifnull(category.description , 'All') as dept_desc, brand.description as brand_desc, vendor.description as vendor_desc
			from coupon
			left join category on coupon.dept_id=category.id
			left join brand on coupon.brand_id=brand.id
			left join vendor on coupon.vendor_id=vendor.id
			where coupon.id=$id and coupon.branch_id=$branch_id" ;
		$con->sql_query($sql);

		if ($con->sql_numrows()>0){
		    $r = $con->sql_fetchrow();

		    $log_arr[] = "Department: ".$r['dept_desc'];

		    if ($r['brand_desc'])
		        $log_arr[] = "Brand:".$r['brand_desc'];
			else
			    $log_arr[] = "Vendor:".$r['vendor_desc'];
		    
		    $log = join(", ", $log_arr);
		    
			$con->sql_query("delete from coupon where id=$id and branch_id=$branch_id");
	        log_br($sessioninfo['id'], 'COUPON', $r['code'], "Delete Coupon id:$id, Branch:".get_branch_code($branch_id).", Code:".$r['code'].", ".$log);
			print "OK";
		}else{
			print $LANG['COUP_NO_DATA'];
		}
	}
	
	function ajax_activate_deactivate_item(){
        global $con, $LANG, $sessioninfo;
		$form = $_REQUEST;
        $branch_id=mi($sessioninfo['branch_id']);
        $id=$form['id'];

		//check exist
		$sql="select coupon.*, ifnull(category.description , 'All') as dept_desc, brand.description as brand_desc, vendor.description as vendor_desc
			from coupon
			left join category on coupon.dept_id=category.id
			left join brand on coupon.brand_id=brand.id
			left join vendor on coupon.vendor_id=vendor.id
			where coupon.id=$id and coupon.branch_id=$branch_id";

		$q1 = $con->sql_query($sql);
		$c_info = $con->sql_fetchassoc($q1);
		
		/*if ($form['active']){
			//check code exist
			if ($c_info['brand_id'])
				$filters[] = "brand_id=$c_info[brand_id]";
			else
				$filters[] = "vendor_id=$c_info[vendor_id]";

			//dept_id = 0 = All
			// if no select all
			if ($c_info['dept_id']>0) $filters[] = "(dept_id=0 or dept_id=$c_info[dept_id])";

	        $filters[] = "active=1";
	        $filter = join(" and ", $filters);

			$dept_bv_sql="select * from coupon where branch_id=$branch_id and $filter";
			$con->sql_query($dept_bv_sql);

			if ($con->sql_numrows()>0){
	            print $LANG['COUP_BRAND_VENDOR_ACTIVATED'];
	            return;
			}
			$con->sql_freeresult();
		}*/

		if ($con->sql_numrows($q1)>0){
		    $log_arr[] = "Department:".$c_info['dept_desc'];

		    if ($c_info['brand_desc'])
		        $log_arr[] = "Brand:".$c_info['brand_desc'];
			else
			    $log_arr[] = "Vendor:".$c_info['vendor_desc'];

		    $log = join(", ", $log_arr);
		    
            $upd['active'] = $form['active'];
            
            //check active
	        if ($upd['active']){
				$active = "Activate";
	 			$upd['activated']="CURRENT_TIMESTAMP";
	        }else{
				$active = "Deactivate";
			}
			
			$upd['active_user_id']=$sessioninfo['id'];
 			$upd['last_update']="CURRENT_TIMESTAMP";
	        $con->sql_query("update coupon set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");

	        log_br($sessioninfo['id'], 'COUPON', $r['code'], "$active Coupon id:$id, Branch:".get_branch_code($branch_id).", Code:".$r['code'].", ".$log);

	        print "OK";
		}else{
			print $LANG['COUP_NO_DATA'];
		}
	}
	
	function print_coupon(){
		global $con, $smarty, $config,$sessioninfo, $LANG, $appCore;
		
		$form=$_REQUEST;
		$form['print_type'] = "amount";	// always amount
		//print_r($form);exit;
		if($form['discount_by'] == 'per'){
			// Percentage
			if (!$form['print_value']){
				print sprintf($LANG['COUP_MISS_DATA'],"coupon percentage");
				exit;
			}

			if ($form['print_value'] >= 100){
				print sprintf($LANG['COUP_LIMIT'],"percentage","99.99%");
				exit;
			}
		}else{
			// Amount
			if (!$form['print_value']){
				print sprintf($LANG['COUP_MISS_DATA'],"coupon amount");
				exit;
			}

			if ($config['coupon_amount_0_5_cent']){
				$check_amount=substr($form['print_value']*100,-1);

				if ($check_amount != "5" && $check_amount != '0'){
					print $LANG['COUP_MUST_0_5_CENT'];
					exit;
				}
				
				if ($form['print_value'] >= 1000){
					print sprintf($LANG['COUP_LIMIT'],"amount", $config["arms_currency"]["symbol"] . " 999.95");
					exit;
				}

			}elseif ($form['print_value'] >= 1000){
				print sprintf($LANG['COUP_LIMIT'],"amount", $config["arms_currency"]["symbol"] . " 999.99");
				exit;
			}
		}
		
		if (!$form['print_qty']){
			print sprintf($LANG['COUP_MISS_DATA'],"coupon quantity");
			exit;
		}
		
		if ($form['print_qty'] > 500){
			print sprintf($LANG['COUP_LIMIT'],"quantity","500");
			exit;
		}

		//get prefix header
		$sql_pre = "select * from pos_settings ps
					where ps.branch_id = $form[branch_id] and ps.setting_name='barcode_coupon_prefix'";

        $sql_p=$con->sql_query($sql_pre);

	    $p=$con->sql_fetchassoc($sql_p);

		if (!$p['setting_value']){
			$coupon_prefix="CP";
		}else{
            $coupon_prefix=strtoupper($p['setting_value']);
		}
		$con->sql_freeresult();
		
		$con->sql_query("update coupon set remark='$form[remark]' where branch_id = $form[branch_id] and id=$form[id]");

		//get coupon data
		$sql = "select cp.*, branch.code as branch_code, SUBSTRING(category.description,1,22) as dept_description, SUBSTRING(brand.description,1,22) as brand_description, SUBSTRING(vendor.description,1,22) as vendor_description
				from coupon cp
		        left join branch on cp.branch_id=branch.id
		        left join category on cp.dept_id=category.id
		        left join vendor on cp.vendor_id=vendor.id
		        left join brand on cp.brand_id=brand.id
				where cp.branch_id = $form[branch_id] and cp.id=$form[id]";

        $sql_r=$con->sql_query($sql);

	    $r=$con->sql_fetchassoc($sql_r);
		$r['barcode_coupon_prefix']=$coupon_prefix;

		if ($form['discount_by'] == "per"){
			//exclude .00
			if (mi($form['print_value']) == $form['print_value']){
	            $form['print_value']=mi($form['print_value']);
			}
			$r['coupon_value']=$form['print_value'];
            $log_msg = $form['print_value']."%";
		}else{
			//exclude .00
/*			if (mi($form['print_value']) == $form['print_value']){
	            $form['print_value']=mi($form['print_value']);
			}
*/			$r['coupon_value']=$form['print_value'];
			$log_msg = $config["arms_currency"]["symbol"] . " ".$form['print_value'];
		}
		
		$r['discount_by']=$form['discount_by'];

		$pages=$this->get_no_of_sheet($form['print_format'], $form['print_qty']);

		$r['is_print']+=$form['print_qty'];

		if ($r['discount_by'] == "per"){
			$r['print_type'] = 'percentage';
			$barcode=str_pad($r['code'],7,"0",STR_PAD_LEFT)."O".str_pad(($r['coupon_value']*100),4,"0",STR_PAD_LEFT);
		}		    
		else{
			$r['print_type'] = 'amount';
			$barcode=str_pad($r['code'],7,"0",STR_PAD_LEFT).str_pad(($r['coupon_value']*100),5,"0",STR_PAD_LEFT);
		}

		$r['secur_barcode']=$barcode.substr(encrypt_for_verification($barcode),0,2);

		$time_stamp=ms(date("Y-m-d H:i:s"));

		$con->sql_query("update coupon set is_print=$r[is_print], last_update=$time_stamp where branch_id = $form[branch_id] and id=$form[id]");

		// Check coupon items
		$con->sql_begin_transaction();
		
		$coupon_code = $barcode;
		$full_coupon_code = $coupon_prefix.$r['secur_barcode'];
		$con->sql_query("select * from coupon_items where coupon_code=".ms($coupon_code)." for update");
		$cpi = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($cpi){
			// Printed before
			$upd = array();
			$upd['print_qty'] = $cpi['print_qty']+$form['print_qty'];
			$upd['last_update'] = 'CURRENT_TIMESTAMP';
			if($form['remark'])	$upd['remark'] = $form['remark'];
			
			$con->sql_query("update coupon_items set ".mysql_update_by_field($upd)." where coupon_code=".ms($coupon_code));
		}else{
			// New Print
			$upd = array();
			$upd['coupon_code'] = $coupon_code;
			$upd['full_coupon_code'] = $full_coupon_code;
			$upd['branch_id'] = $form['branch_id'];
			$upd['coupon_id'] = $form['id'];
			$upd['print_qty'] = $form['print_qty'];
			$upd['print_value'] = $form['print_value'];
			$upd['print_format'] = $form['print_format'];
			$upd['user_id'] = $sessioninfo['id'];
			$upd['remark'] = $form['remark'];
			$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into coupon_items ".mysql_insert_by_field($upd));
		}
		$con->sql_commit();
		
		$coupon[]=$r;
		//print_r($coupon);
		//print_r($pages);exit;
		log_br($sessioninfo['id'],'COUPON',$code,"Print coupon code: ".$r['secur_barcode'].", ".($r['discount_by']=='per'?'Percent':'Amount')." : $log_msg, Print Qty: $form[print_qty]");

		$smarty->assign('coupon',$coupon);
		$smarty->assign('pages',$pages);
		$smarty->assign('coupon_code',$r['code']);
		
		$address=$pages['address'];

		$smarty->display($address);
	}
	
	function validate_data(){
		global $LANG, $sessioninfo, $con;
		$form=$_REQUEST;

		$err = array();
		$code=$form['code'];

		if(!$form['is_print']){
			//check code
			if (!$code){
				$err[] = $LANG['COUP_MISS_CODE'];
			}elseif (strlen($code) >7 ){
				$err[] = $LANG['COUP_CODE_OVER_DIGIT'];
			}

			$date_type=$form['rdo_end'];
			if($date_type == 'valid_to' && $form['valid_from'] > $form[$date_type]){
				$err[] = $LANG['COUP_OVER_DATE'];
			}
			
			//check duplicate code
			$filter = "";
			if($form['id']) $filter = " and id != ".mi($form['id']);
			$q1=$con->sql_query("select * from coupon where code=".mi($form['code'])." and branch_id=".mi($form['branch_id']).$filter);
			
			if ($con->sql_numrows($q1)>0){
				$err[] = $LANG['COUP_CODE_EXIST'];
			}
			$con->sql_freeresult($q1);
			
			//check brand, vendor 
			/*if ($form['setting'] == "brand" && !$form['brand_id']){
				return sprintf($LANG['COUP_MISS_DATA'],"brand");
			}
			
			if ($form['setting'] == "vendor" && !$form['vendor_id']){
				return sprintf($LANG['COUP_MISS_DATA'],"vendor");
			}*/
			
			if ($form['setting'] == "dept_bran_vd"){
				/*$filters = $msg_type = array();
				$filter = "";

				//check brand
				if($form['brand_id']){
					$filters[] = "brand_id=$form[brand_id]";
					$msg_type[]="brand";
				}else $filters[] = "(brand_id is null or brand_id = 0)"; 
				
				// check vendor
				if($form['vendor_id']){
					$filters[] = "vendor_id=$form[vendor_id]";
					$msg_type[]="vendor";
				}else $filters[] = "(vendor_id is null or vendor_id = 0)";
				
				if($form['dept_id']){
					$filters[] = "department";
					$filters[] = "dept_id=".mi($form['dept_id']);
				}
				
				if($form['id']) $filters[] = "id != ".mi($form['id']);

				if($filters) $filter = join(" and ", $filters);

				$sql = "select * from coupon where branch_id=".mi($sessioninfo['branch_id'])." and $filter";
				$q1 = $con->sql_query($sql);

				if ($con->sql_numrows($q1)>0){
					$err[] = sprintf($LANG['COUP_BRAND_VENDOR_EXIST'], join(", ", $msg_type));
				}*/
			}else{
				$si_no_data = false;
				$si_list = array();
				if($form['sku_code_list']){
					$q1 = $con->sql_query("select * from sku_items where sku_item_code in (".join(",", $form['sku_code_list']).")");
					
					if($con->sql_numrows($q1) == 0){
						$si_no_data = true;
					}else{
						while($r = $con->sql_fetchassoc($q1)){
							$si_list[] = $r['id'];
						}
						$con->sql_freeresult($q1);
						$form['sku_code_list'] = serialize($si_list);
					}
				}
				
				if(!$form['sku_code_list'] || $si_no_data){
					$err[] = $LANG['COUP_EMPTY_SKU_ITEM'];
				}
			}
			
			// check sku items
			if ($form['setting'] == "sku_items" && !trim($form['sku_code_list'])){
				$err[] = sprintf($LANG['COUP_MISS_DATA'],"SKU items");
			}
			
			// Referral Program
			if($form['member_limit_type'] == 'referral_program'){
				$form['referrer_coupon_get'] = mi($form['referrer_coupon_get']);
				$form['referrer_count_need'] = mi($form['referrer_count_need']);
				$form['referee_coupon_get'] = mi($form['referee_coupon_get']);
				$form['referee_day_limit'] = mi($form['referee_day_limit']);
				
				// Not Allow Negative
				if($form['referrer_coupon_get'] < 0){
					$err[] = sprintf($LANG['DATA_NOT_ALLOW_NEGATIVE'], 'Referrer Coupon');
				}
				if($form['referrer_count_need'] < 0){
					$err[] = sprintf($LANG['DATA_NOT_ALLOW_NEGATIVE'], 'Referral Count');
				}
				if($form['referee_coupon_get'] < 0){
					$err[] = sprintf($LANG['DATA_NOT_ALLOW_NEGATIVE'], 'Referee Coupon');
				}
				if($form['referee_day_limit'] < 0){
					$err[] = sprintf($LANG['DATA_NOT_ALLOW_NEGATIVE'], 'Referee Day');
				}
				
				// Cannot all zero
				if(!$form['referrer_coupon_get'] && !$form['referrer_count_need'] && !$form['referee_coupon_get']){
					$err[] = $LANG['COUP_REF_PROG_ALL_ZERO'];
				}
				if($form['referrer_coupon_get']>0 && !$form['referrer_count_need']){
					$err[] = sprintf($LANG['COUP_MISS_DATA'],"Referral Count");
				}
			}
			
		}
		
		if (!$form['valid_time_from']){
            $err[] = sprintf($LANG['COUP_MISS_TIME'],"start");
		}
		
		if (!$form['valid_time_to']){
            $err[] = sprintf($LANG['COUP_MISS_TIME'],"end");
		}

		$time_from=explode(":",$form['valid_time_from']);

		if (count($time_from)!=2 || strlen($time_from[0]) != 2 || strlen($time_from[1]) != 2 || !is_numeric($time_from[0]) ||!is_numeric($time_from[1])){
			$err[] = sprintf($LANG['COUP_INVALID_TIME_FORMAT'],"start");
		}
		
		$time_to=explode(":",$form['valid_time_to']);

		if (count($time_to)!=2 || strlen($time_to[0]) != 2 || strlen($time_to[0]) != 2 || !is_numeric($time_to[0]) ||!is_numeric($time_to[1])){
			$err[] = sprintf($LANG['COUP_INVALID_TIME_FORMAT'],"end");
		}
		
		
		
		return $err;
	}
	
	function get_log(){
		global $con;
		
		$form=$_REQUEST;

		$q1 = $con->sql_query("select cp.*, ifnull(c.description , 'All') as dept_desc, b.description as brand_desc, 
							   v.description as vendor_desc, u.u as user_name
							   from coupon cp
							   left join category c on cp.dept_id=c.id
							   left join brand b on cp.brand_id=b.id
							   left join vendor v on cp.vendor_id=v.id
							   left join user u on cp.create_user_id=u.id
							   where cp.id = ".mi($form['id'])." and cp.branch_id = ".mi($form['branch_id']));
		
		$info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$info['si_list']){
			if($info['Department']) $log_arr[] = "Department: ".$info['department'];
			else $log_arr[] = "Department: All";
			if($info['brand']) $log_arr[] = "Brand: ".$info['brand'];
			else $log_arr[] = "Brand: All";
			if($info['vendor']) $log_arr[] = "Vendor: ".$info['vendor'];
			else $log_arr[] = "Vendor: All";
		}else{
			$info['si_list'] = unserialize($info['si_list']);
			$q1 = $con->sql_query("select * from sku_items where id in (".join(",", $info['si_list']).")");
			
			while($r = $con->sql_fetchassoc($q1)){
				$si_code_list[] = $r['sku_item_code'];
			}
			
			$log_arr[] = "SKU Item Code: ".$form['sku_code_list'];
		}

		$log = join(", ", $log_arr);
		    
		return $log;
	}
	
	function get_no_of_sheet($print_format, $print_qty){
	
	    $print_out['row']=$this->format[$print_format]['row'];
	    $print_out['column']=$this->format[$print_format]['column'];
	    $pcs=$print_out['row']*$print_out['column'];
	    $print_out['sheet']=ceil($print_qty/$pcs);
	    $print_out['pcs']=$print_qty;
	    $print_out['address']=$this->format[$print_format]['address'];	    
		return $print_out;
	}
	
	function ajax_load_coupon_dialog(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		
		if($form['id'] && $form['bid']){
			$action = "Edit";
			$q1 = $con->sql_query("select cp.*, ifnull(c.description , 'All') as dept_desc, b.description as brand_desc, 
								   v.description as vendor_desc, u1.u as user_name,
								   DATE_FORMAT(cp.time_from,'%H:%i') as valid_time_from, DATE_FORMAT(cp.time_to,'%H:%i') as valid_time_to
								   from coupon cp
								   left join category c on cp.dept_id=c.id
								   left join brand b on cp.brand_id=b.id
								   left join vendor v on cp.vendor_id=v.id
								   left join user u1 on cp.create_user_id=u1.id
								   where cp.id = ".mi($form['id'])." and cp.branch_id = ".mi($form['bid']));
			
			$data = $con->sql_fetchassoc($q1);
			$data['member_limit_info'] = unserialize($data['member_limit_info']);
			$data['member_limit_profile_info'] = unserialize($data['member_limit_profile_info']);
			if($data['si_list']){
				$si_list = unserialize($data['si_list']);
				$q1 = $con->sql_query("select * from sku_items where id in (".join(",", $si_list).")");
				
				while($r = $con->sql_fetchassoc($q1)){
					$category[] = $r;
				}
				$con->sql_freeresult($q1);
			}
		}else{
			$action = "New";
			$data['valid_from']=date('Y-m-d');
			$data['valid_to']=date('Y-m-d',strtotime("+$this->duration_valid month", strtotime($_REQUEST['valid_from'])));
		}
		
		$smarty->assign("form", $data);
		$smarty->assign("group_item", $category);
		$smarty->assign("action", $action);
		$ret['ok'] = 1;
		
		if($form['print_coupon']) $ret['html'] = $smarty->fetch("masterfile_coupon.print.tpl");
		else $ret['html'] = $smarty->fetch("masterfile_coupon.new.tpl");
		
		print json_encode($ret);
	}
	
	function ajax_save_coupon(){
		global $con, $smarty, $config, $sessioninfo;
		$form = $_REQUEST;
		//print_r($form);
		//exit;
		
		$err=$this->validate_data();

		if ($err){
			$err_msg = "You had encountered below errors:\n\n";
			$err_msg .= "- ".join("\n- ", $err);
			print $err_msg;
			return;
		}
		
		$date_type=$form['rdo_end'];
		if ($date_type == 'valid_to'){
		    $date_valid_to=$form[$date_type];
		}else{
            $date_valid_to=date("Y-m-d",strtotime("+$form[$date_type] month"));
		}
		
		if($form['setting'] == 'sku_items'){
			$si_id_list = array();
			$q1 = $con->sql_query("select * from sku_items where sku_item_code in (".join(",", $form['sku_code_list']).")");
			
			while($r = $con->sql_fetchassoc($q1)){
				$si_id_list[] = $r['id'];
			}
		}
		if(!trim($form['brand']))	$form['brand_id'] = 0;
		if(!trim($form['vendor']))	$form['vendor_id'] = 0;
		
		
		// is existing coupon
		if($form['id'] && $form['branch_id']){
			if(!$form['is_print']){
				$upd['code']=str_pad($form['code'],7,"0",STR_PAD_LEFT);
				
				if($form['setting'] == 'dept_bran_vd'){
					$upd['dept_id']=mi($form['dept_id']);
					$upd['brand_id']=$form['brand_id'];
					$upd['vendor_id']=$form['vendor_id'];
					$upd['si_list']="";
				}else{
					$upd['dept_id']=0;
					$upd['brand_id']="";
					$upd['vendor_id']="";
					$upd['si_list']=serialize($si_id_list);
				}
				$upd['min_qty'] = $form['min_qty'];
				$upd['min_amt'] = mf($form['min_amt']);
				$upd['min_receipt_amt'] = mf($form['min_receipt_amt']);
				$upd['discount_by'] = trim($form['discount_by']);
				$upd['member_limit_type'] = trim($form['member_limit_type']);
				$upd['member_limit_info'] = serialize($form['member_limit_info']);
				$upd['member_limit_count'] = mi($form['member_limit_count']);
				$upd['member_limit_mobile_day_start'] = mi($form['member_limit_mobile_day_start']);
				$upd['member_limit_mobile_day_end'] = mi($form['member_limit_mobile_day_end']);
				$upd['member_limit_profile_info'] = serialize($form['member_limit_profile_info']);
				$upd['referrer_coupon_get'] = mi($form['referrer_coupon_get']);
				$upd['referrer_count_need'] = mi($form['referrer_count_need']);
				$upd['referee_coupon_get'] = mi($form['referee_coupon_get']);
				$upd['referee_day_limit'] = mi($form['referee_day_limit']);
			}
			
            $upd['valid_from']=$form['valid_from'];
            $upd['valid_to']=$date_valid_to;
            $upd['time_from']=$form['valid_time_from'];
            $upd['time_to']=$form['valid_time_to'];
	   		$upd['last_update']="CURRENT_TIMESTAMP";
			
			$q1 = $con->sql_query("update coupon set ".mysql_update_by_field($upd)." where id = ".mi($form['id'])." and branch_id = ".mi($form['branch_id']));
			
			if($con->sql_affectedrows($q1) > 0){ // store log
				$log = $this->get_log();
				log_br($sessioninfo['id'], 'COUPON', $form['code'], "Updated Coupon id:".mi($form['id']).", Branch:".get_branch_code($form['branch_id']).", Code:".$form['code'].", ".$log);
			}
		}else{ // is new coupon
			$ins = array();
			$ins['branch_id']=$sessioninfo['branch_id'];
			$ins['code']=str_pad($form['code'],7,"0",STR_PAD_LEFT);
			$ins['valid_from']=$form['valid_from'];
			$ins['valid_to']=$date_valid_to;
			$ins['time_from']=$form['valid_time_from'];
			$ins['time_to']=$form['valid_time_to'];
			$ins['added']="CURRENT_TIMESTAMP";
			$ins['last_update']="CURRENT_TIMESTAMP";
			$ins['create_user_id']=$sessioninfo['id'];
			$ins['min_qty'] = $form['min_qty'];
			$ins['min_amt'] = mf($form['min_amt']);
			$ins['min_receipt_amt'] = mf($form['min_receipt_amt']);
			$ins['discount_by'] = trim($form['discount_by']);
			$ins['member_limit_type'] = trim($form['member_limit_type']);
			$ins['member_limit_info'] = serialize($form['member_limit_info']);
			$ins['member_limit_count'] = mi($form['member_limit_count']);
			$ins['member_limit_mobile_day_start'] = mi($form['member_limit_mobile_day_start']);
			$ins['member_limit_mobile_day_end'] = mi($form['member_limit_mobile_day_end']);
			$ins['member_limit_profile_info'] = serialize($form['member_limit_profile_info']);
			$ins['referrer_coupon_get'] = mi($form['referrer_coupon_get']);
			$ins['referrer_count_need'] = mi($form['referrer_count_need']);
			$ins['referee_coupon_get'] = mi($form['referee_coupon_get']);
			$ins['referee_day_limit'] = mi($form['referee_day_limit']);
				
			if($form['setting'] == 'dept_bran_vd'){
				$ins['dept_id']=mi($form['dept_id']);
				$ins['brand_id']=$form['brand_id'];
				$ins['vendor_id']=$form['vendor_id'];
				$ins['si_list']="";
			}else{
				$ins['dept_id']=0;
				$ins['brand_id']="";
				$ins['vendor_id']="";
				$ins['si_list']=serialize($si_id_list);
			}

			$con->sql_query("insert into coupon ". mysql_insert_by_field($ins));
			$_REQUEST['id'] = $con->sql_nextid();
			$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
		
			$log = $this->get_log();
			log_br($sessioninfo['id'], 'COUPON', $form['code'], "New Coupon id:".mi($form['id']).", Branch:".get_branch_code($form['branch_id']).", Code:".$form['code'].", ".$log);
		}
		
		print "OK";
	}
	
	function manage_member(){
		global $con, $smarty, $appCore;
		
		$coupon_code = trim($_REQUEST['coupon_code']);		
		$result = $appCore->couponManager->getCouponItems($coupon_code);
		if(!$result['ok']){
			$err_msg = $result['error'];
		}
		
		if(!$err_msg){
			$coupon_items = $result['data'];			
			if(!$coupon_items['member_limit_type']){
				$err_msg = "This feature only allow for Member";
			}			
		}
		
		// Got Error
		if($err_msg){
			$smarty->assign("url", $_SERVER['PHP_SELF']);
		    $smarty->assign("title", $this->title);
		    $smarty->assign("subject", $err_msg);
		    $smarty->display("redir.tpl");
			exit;
		}
		
		$smarty->assign('coupon_items', $coupon_items);
		$smarty->display("masterfile_coupon.manage_member.tpl");
	}
	
	function ajax_show_add_member(){
		global $con, $smarty, $appCore;
		
		if (!privilege('MST_COUPON_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_COUPON_EDIT', BRANCH_CODE), "/index.php");
		
		$coupon_code = trim($_REQUEST['coupon_code']);
		$result = $appCore->couponManager->getCouponItems($coupon_code);
		if(!$result['ok']){
			die($result['error']);
		}
		$coupon_items = $result['data'];
		
		$smarty->assign('coupon_items', $coupon_items);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("masterfile_coupon.manage_member.add_member.tpl");
		
		print json_encode($ret);
	}
	
	function ajax_add_member(){
		global $con, $smarty, $appCore;
		
		//print_r($_REQUEST);
		
		// Get Coupon Items
		$coupon_code = trim($_REQUEST['coupon_code']);
		$result = $appCore->couponManager->getCouponItems($coupon_code);
		if(!$result['ok']){
			die($result['error']);
		}
		$coupon_items = $result['data'];
		
		$card_no_list = array();
		$err = array();
		$temp = preg_split("/\s*[\n\r,]+\s*/", trim($_REQUEST['add_member_list']));
		foreach($temp as $str){
			if(trim($str)=='')    continue;
			
			// Search member
			$member = $appCore->memberManager->getMember($str);
			if(!$member){
				$err[] = "Member '$str' not found";
				continue;
			}
			
			$card_no = trim($member['card_no']);
			if(!in_array($card_no, $card_no_list)){
				$card_no_list[] = $card_no;
			}
		}
		
		$failed_reason = '';
		if($err){
			foreach($err as $e){
				$failed_reason .= "- $e<br />";
			}
		}
		
		if(!$err && !$card_no_list){
			$failed_reason .= "No member to add.";
		}
		
		// Got Error
		if($failed_reason){
			$failed_reason = 'Error:<br />'.$failed_reason;
			
			$ret = array();
			$ret['failed_reason'] = $failed_reason;
			print json_encode($ret);
			exit;
		}
		
		$result = $this->add_coupon_member_by_list($coupon_code, $card_no_list);
		$inserted_count = mi($result['inserted_count']);
		$duplicated_count = mi($result['duplicated_count']);
		$error_count = mi($result['error_count']);
		$err = $result['error_list'];
				
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = '';
		
		if($inserted_count > 0){
			$ret['html'] .= "Added: ".$inserted_count."<br />";
		}
		if($duplicated_count > 0){
			$ret['html'] .= "Skipped Duplicate: ".$duplicated_count."<br />";
		}
		if($error_count > 0){
			$ret['html'] .= "Error: ".$error_count."<br />";
			foreach($err as $e){
				$ret['html'] .= "- ".$e."<br />";
			}
		}
		
		print json_encode($ret);
	}
	
	function ajax_reload_coupon_member_list(){
		global $con, $smarty, $appCore;
		
		//print_r($_REQUEST);
		
		// Get Coupon Items
		$coupon_code = trim($_REQUEST['coupon_code']);
		$result = $appCore->couponManager->getCouponItems($coupon_code);
		if(!$result['ok']){
			die($result['error']);
		}
		$coupon_items = $result['data'];
		
		$member_list = array();
		$q1 = $con->sql_query("select cim.*, m.nric, m.name
			from coupon_items_member cim
			left join membership m on m.card_no=cim.card_no
			where cim.coupon_code=".ms($coupon_code)." order by cim.card_no");
		while($r = $con->sql_fetchassoc($q1)){
			$member_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('coupon_items', $coupon_items);
		$smarty->assign('member_list', $member_list);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_coupon.manage_member.list.tpl');
		
		print json_encode($ret);
	}
	
	function download_import_coupon_member(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_coupon_member.csv");
		
		$sample_line = array('Card_No_0001', 'Card_No_0002', 'NRIC_0001', 'NRIC_0002');
		foreach($sample_line as $sample) {
			print $sample . "\n\r";
		}
	}
	
	function csv_add_coupon_member(){
		global $con, $smarty, $appCore;
		
		$coupon_code = trim($_REQUEST['coupon_code']);
		$file = $_FILES['member_file'];
		$f = fopen($file['tmp_name'], "rt");
		$err = array();
		$card_no_list = array();
		while($r = fgetcsv($f)){
			$str = trim($r[0]);
			if(!$str)	continue;
			
			// Search member
			$member = $appCore->memberManager->getMember($str);
			if(!$member){
				$err[] = "Member '$str' not found";
				continue;
			}
			
			$card_no = trim($member['card_no']);
			if(!in_array($card_no, $card_no_list)){
				$card_no_list[] = $card_no;
			}
		}
		
		fclose($f);
		
		if(!$err){
			if(!$card_no_list){
				$err[] = "No member to add.";
			}
		}
		
		if($err){
			$failed_reason = 'Error: <br />';
			foreach($err as $e){
				$failed_reason .= "- $e<br />";
			}
			print "<script>parent.ADD_MEMBER_DIALOG.csv_import_error('".jsstring($failed_reason)."');</script>";
			exit;
		}
		
		$result = $this->add_coupon_member_by_list($coupon_code, $card_no_list);
		$inserted_count = mi($result['inserted_count']);
		$duplicated_count = mi($result['duplicated_count']);
		$error_count = mi($result['error_count']);
		$err = $result['error_list'];

		$success_html = '';
		
		if($inserted_count > 0){
			$success_html .= "Added: ".$inserted_count."<br />";
		}
		if($duplicated_count > 0){
			$success_html .= "Skipped Duplicate: ".$duplicated_count."<br />";
		}
		if($error_count > 0){
			$success_html .= "Error: ".$error_count."<br />";
			foreach($err as $e){
				$success_html .= "- ".$e."<br />";
			}
		}
		
		print "<script>parent.ADD_MEMBER_DIALOG.csv_import_success('".jsstring($success_html)."');</script>";
	}
	
	function add_coupon_member_by_list($coupon_code, $card_no_list){
		global $con, $smarty, $appCore;
		
		$con->sql_begin_transaction();
		
		$params = array();
		$inserted_count = $duplicated_count = $error_count = 0;
		$err = array();
		if(isset($_REQUEST['enable_push_notification_manual']))	$params['send_push_notification'] = mi($_REQUEST['enable_push_notification_manual']);
		foreach($card_no_list as $card_no){	// Loop card_no
			$result = $appCore->couponManager->addCouponItemsToMember($coupon_code, $card_no, $params);
			if($result['ok']){	// Insert success
				$inserted_count++;
			}elseif($result['duplicated']){	// already have this member
				$duplicated_count++;
			}else{	// insert failed
				$error_count++;
				$err[] = $result['error'];
			}
		}
		$con->sql_commit();
		
		$ret = array();
		$ret['inserted_count'] = $inserted_count;
		$ret['duplicated_count'] = $duplicated_count;
		$ret['error_count'] = $error_count;
		$ret['error_list'] = $err;
		
		return $ret;
	}
	
	function ajax_set_coupon_member_active(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$coupon_code = trim($_REQUEST['coupon_code']);
		$card_no = trim($_REQUEST['card_no']);
		$active = mi($_REQUEST['active']);
		
		$upd = array();
		$upd['active'] = $active;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("update coupon_items_member set ".mysql_update_by_field($upd)." where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no));
		$updated = $con->sql_affectedrows();
		
		if(!$updated){
			die("Update Failed.");
		}
		log_br($sessioninfo['id'], 'COUPON',0, ($active ? 'Activate':'Deactivate')." Coupon Member, Card No: ".$card_no);
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_delete_coupon_member(){
		global $con, $smarty, $appCore, $sessioninfo;
		
		$coupon_code = trim($_REQUEST['coupon_code']);
		$card_no = trim($_REQUEST['card_no']);
		
		if(!$coupon_code)	die("Invalid Coupon Code");
		if(!$card_no)	die("Invalid Card No");
		
		$con->sql_begin_transaction();
		
		$con->sql_query("select * from coupon_items_member where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no)." for update");
		$data = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$data){
			die("Data Not Found");
		}
		
		if($data['used_count']>0){
			die("You cannot delete this member due to this member already used the coupon, instead please deactivate it.");
		}
		
		$con->sql_query("delete from coupon_items_member where coupon_code=".ms($coupon_code)." and card_no=".ms($card_no));
		log_br($sessioninfo['id'], 'COUPON', 0, "Delete Coupon Member, Card No: ".$card_no);
		$con->sql_commit();
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function ajax_get_new_coupon_code(){
		global $con;
		
		$new_code = 1;
		
		$q1 = $con->sql_query("select distinct(code) as code from coupon coupon order by code");
		while($r = $con->sql_fetchassoc($q1)){
			$code = mi($r['code']);
			if($code > $new_code){
				break;
			}
			$new_code = $code+1;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['new_code'] = $new_code;
		print json_encode($ret);
	}
}

$coupon=new coupon("Coupon");

?>
