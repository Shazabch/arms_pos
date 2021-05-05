<?php
/*

REVISION HISTORY
================

5/31/2010 4:14:05 PM Alex
- add config['upper_date_limit'] and config['lower_date_limit']

7/6/2011 2:47:44 PM Andy
- Change split() to use explode()

7/11/2011 4:08:15 PM Andy
- Replace htmlentities() to htmlspecialchars()

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

3/2/2012 10:24:39 AM Alex
- add scan barcode => ajax_add_item_row()

4/3/2012 3:37:53 PM Andy
- Change function init_selection to init_so_selection().
- Add can select approved Sales Order to generate to PO.

4/20/2012 6:07:19 PM Alex
- add packing uom code => ajax_add_item_row()

3/4/2013 2:03 PM Andy
- Add get receipt list when load the sales order which has been exported to POS.

3/6/2013 4:57 PM Fithri
- when scan bom package item, split item into itemize
- if one of the item delete, all related bom items also delete
- if change one item qty, all other also change

4/12/2013 4:33 PM Andy
- Enhance when user save Sales Order, it will remove the SO Amount need update flag.

5/14/2013 11:14 AM Andy
- Add selling type for sales order.
- Enhance to when un-tick use promo price, it will load back the normal selling price.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/29/2013 5:35 PM Andy
- Enhance to check adjustment total cost for approval when confirm.

3/31/2014 4:38 PM Justin
- Enhanced to inactive SKU when scan barcode.

4/22/2014 5:06 PM Justin
- Enhanced to have filter on mprice type by user.

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

2/9/2015 3:37 PM Andy
- GST Enhancements.

4/6/2015 4:41 PM Andy
- Remove to store the GST Indicator.

10/15/2015 2:53 PM Justin
- Enhanced to skip compulsory checking for batch code if config not set.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

11/7/2016 10:29 AM Andy
- Fixed sales order generate to po no gst.

3/23/2017 5:49 PM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when generate or update po.
- Fixed wrong po nsp and ssp.
- Fixed wrong po cost gst id.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().

4/20/2017 10:25 AM Justin
- Enhanced to have privilege checking.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/2/2017 10:13 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.

11/17/2017 5:52 PM Justin
- Enhanced it to become able to search by debtor code or description.

6/19/2018 1:30 PM Andy
- Fixed add item by parent child only search for active sku.

3/30/2018 4:13PM HockLee
- Create new functions for uploading Sales Order by csv.
- multi_save(), upload_csv(), show_result(), add_sales_item(), download_sample_so()

8/24/2018 4:12 PM Andy
- Enhanced Sales Order to have Debtor Price feature.
- Increase maintenance version checking to 360.

10/10/2018 1:00 PM HockLee
- Remove UOM ID column in download_sample_so().

10/17/2018 5:25 PM Andy
- Fixed if no mprice system should get normal price.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

12/28/2018 2:48 PM HockLee
- Bugs Fixed: remove integration code in save() function to avoid missing integration code.

1/25/2019 3:59 PM Andy
- Fixed $sku_code_list index issue.

6/19/2019 3:00 PM William
- Pick up "vertical_logo" and "vertical_logo_no_company_name" from Branch for logo and hide company name setting.
- Pick up "setting_value" from system_settings for logo setting.

11/28/2019 9:08 AM William
- Enhanced to display sku item photo to sales order module.
- Fixed bug when sales order module change the url "view" to "open" and the item is not able to edit, system will show error message and redirect to home page.

12/6/2019 2:34 PM William
- Change error message "This Sales Order belongs to other branch" to "You cannot edit other branch Sales Order"

2/14/2020 3:25 PM William
- Enhanced to Change log type "SALES ORDER" to "SALES_ORDER".

2/1/2021 4:09 PM William
- Enhanced to add remark to sales order items. 
- Enhanced to get reserve_qty when open sales order.

3/4/2021 15:00 PM Sin Rou
- Enhance to config and display out RSP and RSP Discount.
- Modify the sql by adding selection to RSP and RSP Discount.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
if (!privilege('SO_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SO_EDIT', BRANCH_CODE), "/index.php");
$maintenance->check(360);

ini_set('memory_limit', '1024M');

include("sales_order.include.php");
include('po.include.php');

class Sales_Order extends Module{
	var $order_list_size = 10;

	function __construct($title){
		global $con, $smarty;
		
		$this->init();

		if(!$_REQUEST['skip_init_load'])    init_so_selection();
		
		$smarty->assign('time_value', 1000000000);
		parent::__construct($title);
	}
	
	function _default(){
	    $this->init_table();
		$this->display();
	}
	
	private function init_table(){ /* moved to maintenance.php */ }

	function init(){
		//global $con, $config, $smarty;
		
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/stock_reorder_import"))	check_and_create_dir("attachments/stock_reorder_import");
	}
	
	function ajax_list_sel(){
        global $con, $sessioninfo, $smarty, $LANG;
        
        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $this->order_list_size;
		$start = $p*$size;
		
		$filter = array();
		$get_receipt_no = false;
		switch($t){
			case 1:	// saved order
				$filter[] = "so.status=0 and so.active=1";
				break;
			case 2: // waiting for approve
				$filter[] = "so.status=1 and so.approved=0 and so.active=1";
				break;
			case 3: // cancelled / terminted
				$filter[] = "(so.status=4 or so.status=5) and so.active=1";
				break;
			case 4: // approved
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=0";
			    break;
			case 5: // rejected
			    $filter[] = "so.status=2 and so.approved=0 and so.active=1";
			    break;
			case 7: // delivered
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=1 and so.exported_to_pos=0";
			    break;
			case 8: // exported to PO
			    $filter[] = "so.status=1 and so.approved=1 and so.active=1 and so.delivered=0 and so.exported_to_pos=1"; //change this later
				$get_receipt_no = true;
			    break;
			case 6: // search items
				$str = $_REQUEST['search_str'];
				if(!$str)	die('Cannot search empty string');
				$filter_or[] = "so.batch_code=".ms($str);
				$filter_or[] = "so.order_no=".ms($str);
				$filter_or[] = "so.cust_po=".ms($str);
				$filter_or[] = "so.id=".ms($str);
				$filter_or[] = 'debtor.code like '.ms('%'.replace_special_char($_REQUEST['search_str']).'%').' or debtor.description like '.ms('%'.replace_special_char($_REQUEST['search_str']).'%');
				$filter[] = "(".join(' or ',$filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		if(BRANCH_CODE!='HQ')	$filter[] = "so.branch_id=$sessioninfo[branch_id]";
		$filter = "where ".join(' and ',$filter);
		
		$con->sql_query("select count(*) from sales_order so
						left join branch on branch.id=so.branch_id
						left join debtor on debtor.id=so.debtor_id
						left join user on user.id=so.user_id
						left join branch_approval_history bah on bah.id = so.approval_history_id and bah.branch_id = so.branch_id
						$filter") or die(mysql_error());
		$total_rows = $con->sql_fetchfield(0);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by so.last_update desc";

		$total_page = ceil($total_rows/$size);

		$sql = "select so.*,user.u as username, branch.report_prefix as branch_prefix, branch.code as branch_code,bah.approvals, bah.approval_order_id,debtor.code as debtor_code,debtor.description as debtor_description, (select sum(soi.do_qty) from sales_order_items soi where soi.branch_id=so.branch_id and soi.sales_order_id=so.id) as delivered_qty
				from sales_order so
				left join branch on branch.id=so.branch_id
				left join debtor on debtor.id=so.debtor_id
				left join user on user.id=so.user_id
				left join branch_approval_history bah on bah.id = so.approval_history_id and bah.branch_id = so.branch_id
				$filter $order $limit";
		//print $sql;
		$q1 = $con->sql_query($sql);
		$order_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($r['po_used'] && $r['po_ref']){
				$tmp_arr = explode("|", $r['po_ref']);
				if($tmp_arr){
					foreach($tmp_arr as $po_ref){
						if(!$po_ref)	continue;
						
						list($tmp_bid, $tmp_po_id) = explode("-", $po_ref);
						if($tmp_bid && $tmp_po_id){
							$tmp_bcode = get_branch_code($tmp_bid);
							
							$r['po_list'][] = array(
								'bid' => $tmp_bid,
								'po_id' => $tmp_po_id,
								'code' => $tmp_bcode.sprintf("%05d", $tmp_po_id)
							);
						}
					}
				}
			}
			if ($get_receipt_no) {
				// get sales order exported to pos
				$r['receipt_details'] = get_sales_order_receipt_list($r['branch_id'], $r['id']);
			}
			$order_list[] = $r;
		}
		$con->sql_freeresult($q1);
		//print_r($order_list);
		$smarty->assign('order_list', $order_list);
		$smarty->assign('total_page',$total_page);
		$smarty->display("sales_order.list.tpl");
		
	}
	
	function open($id = 0, $branch_id = 0, $load_header = true){
        global $con, $sessioninfo, $smarty, $LANG, $config, $mprice_type_list;
		// delete old tmp items
        $con->sql_query("delete from tmp_sales_order_items where (sales_order_id>1000000000 and sales_order_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
        $form = $_REQUEST;

        if(!$id){
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
		}

		if(!$config['single_server_mode']&&$branch_id>0&&$id>0&&$branch_id!=$sessioninfo['branch_id']&&$_REQUEST['a']!='view'){
			header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
			exit;
		}

        if(!is_new_id($id)&&$branch_id){    // exists order
            if($load_header){
                $form = load_order_header($branch_id, $id, true);
                if($_REQUEST['a']=='open'){
					$this->copy_to_tmp($branch_id, $id);
					//check saved sales order only can save by branch from.
					if($form['status'] == 0 && $form['branch_id']!= $sessioninfo['branch_id']){
						$err_msg = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "Sales Order");
						js_redirect($err_msg, "/index.php");
					}
					
					//check rejected sales order only can save by owner
					if($form['status'] == 2 && $form['user_id']!= $sessioninfo['id']){
						$err_msg = "This Sales Order is only can edit by onwner.";
						js_redirect($err_msg, "/index.php");
					}
					//check terminted sales order only can view
					if($form['status'] == 4 && $form['user_id']!= $sessioninfo['id']){
						header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
						exit;
					}
				}

                if(($form['approved']||!$form['active'])&& $_REQUEST['a']!='view'){

		            header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
					exit;
				}
			}
			if($form['selling_type'] && !in_array($form['selling_type'], $mprice_type_list)){
				$mprice_type_list[] = $form['selling_type'];
				$smarty->assign('mprice_type_list', $mprice_type_list);
			}
		}else{  // new order
			if(!$id){
                $id=time();
				$form['id']=$id;
				$form['order_date'] = date("Y-m-d");
			}
			sku_multiple_selling_price_handler($form);
		}
		
		if($branch_id==0) $branch_id=$sessioninfo['branch_id'];

		if($_REQUEST['a'] != 'view'){
			// check gst status
			if($config['enable_gst'] && $form['order_date'])	$form['is_under_gst'] = check_gst_status(array('date'=>$form['order_date'], 'branch_id'=>$branch_id));
		}
		
		// load special exemption relief claus remark if it is new DO or existing DO but does not have the remark (edit mode)
		if($config['enable_gst'] && $form['is_under_gst'] && !$form['special_exemption_rcr']){
			$q1 = $con->sql_query("select * from gst_settings where setting_name = 'special_exemption_relief_claus_remark'");
			$sercr_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			$form['special_exemption_rcr'] = $sercr_info['setting_value'];
			if(!$form['special_exemption_rcr']) $form['special_exemption_rcr'] = $config['se_relief_claus_remark'];
		}

		$q2 = $con->sql_query("select integration_code from debtor where id = '".$form['debtor_id']."'");
		$data = $con->sql_fetchassoc($q2);
		$con->sql_freeresult($q2);
		$form['integration_code'] = $data['integration_code'];
		
		$items = load_order_items($branch_id, $id, ($_REQUEST['a']!='view'));
		//print_r($items);

		$smarty->assign('PAGE_TITLE', $this->title.' - '.(is_new_id($id)?'New Order':$form['order_no']));
		$smarty->assign('form', $form);
	
		$smarty->assign('items', $items);
		$smarty->display('sales_order.open.tpl');
	}
	
	function view(){
	    global $smarty;

	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		if(is_new_id($id)){
            $this->open();
            exit;
		}

		$smarty->assign('readonly', 1);
		$this->open($id, $branch_id, true);
				
	}	
	
	function refresh(){

	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		$this->save_tmp_items($branch_id, $id);
		
        $this->open($id, $branch_id, false);
	}
	
	private function copy_to_tmp($branch_id, $id){
		global $con, $sessioninfo;
		//delete ownself items in tmp table
		$con->sql_query("delete from tmp_sales_order_items where sales_order_id=$id and branch_id = $branch_id and user_id = $sessioninfo[id]");

		//copy items to tmp table
		$q1=$con->sql_query("insert into tmp_sales_order_items
							(sales_order_id, branch_id, user_id, sku_item_id, cost_price, selling_price, uom_id, ctn, pcs, stock_balance, parent_stock_balance, do_qty, item_discount, item_discount_amount, bom_ref_num, bom_qty_ratio,
							gst_id, gst_code, gst_rate, item_discount_amount2, line_gross_amt, line_gst_amt, line_amt, line_gross_amt2, line_gst_amt2, line_amt2, remark)
							select
							$id, branch_id, $sessioninfo[id], sku_item_id, cost_price, selling_price, uom_id, ctn, pcs, stock_balance, parent_stock_balance, do_qty, item_discount, item_discount_amount, bom_ref_num, bom_qty_ratio,
							gst_id, gst_code, gst_rate, item_discount_amount2, line_gross_amt, line_gst_amt, line_amt, line_gross_amt2, line_gst_amt2, line_amt2, remark
							from sales_order_items where sales_order_id=$id and branch_id=$branch_id order by id");
	}
	
	function ajax_add_item_row(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $gst_list, $appCore;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		if($_REQUEST['sid']) $sku_item_id_arr = $_REQUEST['sid'];
		else $sku_item_id_arr = $_REQUEST['sku_code_list'];
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		$order_date = $_REQUEST['order_date'];
		$use_promo_price = mi($_REQUEST['use_promo_price']);
		$use_debtor_price = mi($_REQUEST['use_debtor_price']);
		$selling_type = trim($_REQUEST['selling_type']);
		$is_special_exemption = mi($_REQUEST['is_special_exemption']);
		$form = $_REQUEST;
		$debtor_id = mi($form['debtor_id']);
				
		if($config['enable_gst'])	construct_gst_list();
		
		$item_pcs = array();
		$sku_info = get_grn_barcode_info($grn_barcode,true);
		 
		if($sku_info['sku_item_id']){
			// is inactive item
			$q1 = $con->sql_query("select active from sku_items where id = ".mi($sku_info['sku_item_id'])." limit 1");
			$tmp_si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if(!$tmp_si_info['active']){
				fail($LANG['PO_ITEM_IS_INACTIVE']);
			}
		
			$item_pcs[$sku_info['sku_item_id']] = $sku_info['qty_pcs'];
			$sku_item_id_arr[] = $sku_info['sku_item_id'];
		}
		
		if(!$sku_item_id_arr){
			die($LANG['NO_ITEM_FOUND']);
		}
		if(!$config['sales_order_item_allow_duplicate']){  // check duplicate
			$con->sql_query("select count(*) from tmp_sales_order_items where user_id=$sessioninfo[id] and branch_id=$branch_id and sales_order_id=$id and sku_item_id in (".join(',',$sku_item_id_arr).")");
			if($con->sql_fetchfield(0)>0)   die($LANG['SO_ITEM_FOUND_DUPLICATE']);
		}
		
		$bom_ref_num = time();
		if($config['sku_bom_additional_type']){
		
			foreach ($sku_item_id_arr as $bom_key => $tmp_sku_item_id) {
				$q2 = $con->sql_query($abc="select si.id, sku.is_bom, si.bom_type from sku_items si join sku on sku.id=si.sku_id where si.id = ".mi($tmp_sku_item_id));//print "$abc\n";
				$item = $con->sql_fetchassoc($q2);
				if ($item['is_bom'] && $item['bom_type'] == 'package') {
					$q3 = $con->sql_query($def="select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($item['id'])." order by bi.sku_item_id");//print "$def\n";
					while($bom_item = $con->sql_fetchassoc($q3)){
						if (!in_array($bom_item['sid'],$sku_item_id_arr)) {
							$sku_item_id_arr[] = $bom_item['sid'];
							$bom_qty[$bom_item['sid']] = $bom_item['qty'];
						}
					}
					unset($sku_item_id_arr[$bom_key]);
				}
			}
			
		}

		if($selling_type){	// got choose mprice selling
			$extra_col = ", simp.price as mprice";
			$left_join = "left join sku_items_mprice simp on simp.branch_id=$branch_id and simp.sku_item_id=si.id and simp.type=".ms($selling_type);
		}
		
		$q1 = $con->sql_query("select si.id as sku_item_id, si.sku_id, si.sku_item_code, si.description as sku_description, ifnull(si.artno,si.mcode) as artno_mcode, 1 as uom_id, 1 as uom_fraction,si.packing_uom_id as master_uom_id,sic.qty as stock_balance,ifnull(sic.grn_cost,si.cost_price) as cost_price, ifnull(sip.price,si.selling_price) as selling_price,
							  si.doc_allow_decimal,uom.code as packing_uom_code, si.artno, si.mcode $extra_col, si.use_rsp, if(sip.price is null, si.rsp_discount, sip.rsp_discount) as rsp_discount,si.rsp_price
							  from sku_items si
							  left join sku on sku_id = sku.id
							  left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
							  left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
							  left join uom on uom.id=si.packing_uom_id
							  $left_join
							  where si.id in (".join(',',$sku_item_id_arr).")");
		if($con->sql_numrows($q1)<=0){
            print "<script>alert('".jsstring($LANG['DO_INVALID_ITEM'])."')</script>";
			exit;
		}
		$smarty->assign('form', $form);

		$this->save_tmp_items($branch_id, $id);
		while($item = $con->sql_fetchassoc($q1)){
		    $sid = $item['sku_item_id'];
		    $pcs = $item_pcs[$sid];
		    if($pcs)    $item['pcs'] = $pcs;
		    $item['branch_id'] = $branch_id;
		    $item['sales_order_id'] = $id;
		    $item['user_id'] = $sessioninfo['id'];
			
			$selling_price = 0;
			
			// Use Debtor Price
			if($use_debtor_price){
				$selling_price = $appCore->skuManager->getSKUItemDebtorPrice($branch_id, $sid, $debtor_id);
			}
			
			if(!$selling_price){
				if($use_promo_price){	// Use Promo Price
					$selling_price = mf(get_lowest_price($branch_id, $sid, $order_date));
				}elseif($selling_type){	// Use MPrice
					if($item['mprice'])	$selling_price = $item['mprice'];
				}

				// Normal Price
				if(!$selling_price)	$selling_price = $item['selling_price'];
				
			}
		    $item['selling_price'] = $selling_price;
			
			if (isset($bom_qty[$sid])) {
				$item['bom_qty_ratio'] = $bom_qty[$sid];
				$item['bom_ref_num'] = $bom_ref_num;
			}
			else {
				$item['bom_qty_ratio'] = 0;
				$item['bom_ref_num'] = '';
			}
			
			// GST
			if($config['enable_gst']){
				if($form['is_under_gst']){
					// get sku is inclusive
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $sid);
					// get sku original output gst
					$sku_original_output_gst = get_sku_gst("output_tax", $sid);
					
					$use_gst = array();
					if($is_special_exemption){
						// is special exemption
						$use_gst = get_special_exemption_gst();
					}else{
						// normal debtor
						if($sku_original_output_gst){
							$use_gst = $sku_original_output_gst;
						}
					}
					if($use_gst){
						$item['gst_id'] = $use_gst['id'];
						$item['gst_code'] = $use_gst['code'];
						$item['gst_rate'] = $use_gst['rate'];
					}else{
						$item['gst_id'] = $gst_list[0]['id'];
						$item['gst_code'] = $gst_list[0]['code'];
						$item['gst_rate'] = $gst_list[0]['rate'];
					}
					
				
					if($is_sku_inclusive == 'yes'){
						// is inclusive tax
						// find the price before tax
						$gst_tax_price = round($item['selling_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
						$item['selling_price'] -= $gst_tax_price;
					}
				}
			}
			
			if($config['sales_order_show_photo']){
				$sku_item_photo = $appCore->skuManager->getSKUItemPhotos($item['sku_item_id']);
				if(count($sku_item_photo['photo_list'])> 0){
					$item['photo'] = $sku_item_photo['photo_list'][0];
				}
			}
			
			
			//get parent stock balance
			if($config['show_parent_stock_balance']){
				$item['parent_stock_balance'] = 0;
				$q2= $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
									 from sku_items si
									 left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
									 left join uom on uom.id=si.packing_uom_id
									 where si.sku_id=".mi($item['sku_id']));
				
				while($parent_stock_balance = $con->sql_fetchassoc($q2)) {
					$item['parent_stock_balance'] += $parent_stock_balance['parent_stock_balance'];
				}
				$con->sql_freeresult($q2);
			}		
			
			$item['reserve_qty'] = get_reserve_qty($branch_id, id, $item['sku_item_id']);
			
			$con->sql_query("insert into tmp_sales_order_items ".mysql_insert_by_field($item, array('branch_id','sales_order_id','user_id','sku_item_id','cost_price','selling_price','uom_id','stock_balance','parent_stock_balance','pcs','bom_ref_num','bom_qty_ratio'
			,'gst_id','gst_code','gst_rate')));		
			$item['id'] = $con->sql_nextid();
			
			$smarty->assign('item', $item);
			$smarty->display('sales_order.open.sheet.item_row.tpl');
		}
		$con->sql_freeresult($q1);
	}	
	
	function ajax_delete_item(){
        global $con, $smarty, $sessioninfo, $LANG, $config;

		$sales_order_id = mi($_REQUEST['order_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$id_list = json_decode($_REQUEST['id_list'],true);
		$con->sql_query("delete from tmp_sales_order_items where branch_id=$branch_id and sales_order_id=$sales_order_id and id in (".join(',',$id_list).")");
		print "OK";
	}
	
	private function save_tmp_items($branch_id, $id){
		global $con, $sessioninfo;

		$form=$_REQUEST;

		if($form['uom_id']){
            foreach($form['uom_id'] as $item_id=>$uom_id){
				$upd = array();
				$upd['ctn'] = doubleval($form['ctn'][$item_id]);
				$upd['pcs'] = doubleval($form['pcs'][$item_id]);
				$upd['bom_ref_num'] = $form['bom_ref_num'][$item_id];
				$upd['bom_qty_ratio'] = $form['bom_qty_ratio'][$item_id];
				
				$upd['cost_price'] = doubleval($form['cost_price'][$item_id]);
				$upd['selling_price'] = mf($form['selling_price'][$item_id]);
				$upd['uom_id'] = $uom_id;
				$upd['stock_balance'] = doubleval($form['stock_balance'][$item_id]);
				$upd['parent_stock_balance'] = doubleval($form['parent_stock_balance'][$item_id]);
				
				//$con->sql_query("update tmp_sales_order_items set ".mysql_update_by_field($upd)." where id=".mi($item_id)." and branch_id=$branch_id and user_id=$sessioninfo[id]");
				
				$upd['id'] = $item_id;
			    $upd['sales_order_id'] = mi($id);
			    $upd['branch_id'] = $branch_id;
			    $upd['user_id'] = $sessioninfo['id'];
			    $upd['sku_item_id'] = mi($form['item_sku_item_id'][$item_id]);
				$upd['do_qty'] = $form['do_qty'][$item_id];
				$upd['item_discount'] = $form['item_discount'][$item_id];
				$upd['item_discount_amount'] = $form['item_discount_amount'][$item_id];
				// gst
				$upd['gst_id'] = $form['gst_id'][$item_id];
				$upd['gst_code'] = $form['gst_code'][$item_id];
				$upd['gst_rate'] = $form['gst_rate'][$item_id];
				
				// amt2
				$upd['item_discount_amount2'] = $form['item_discount_amount2'][$item_id];
				$upd['line_gross_amt'] = $form['line_gross_amt'][$item_id];
				$upd['line_gst_amt'] = $form['line_gst_amt'][$item_id];
				$upd['line_amt'] = $form['line_amt'][$item_id];
				$upd['line_gross_amt2'] = $form['line_gross_amt2'][$item_id];
				$upd['line_gst_amt2'] = $form['line_gst_amt2'][$item_id];
				$upd['line_amt2'] = $form['line_amt2'][$item_id];
				
				$upd['remark'] = $form['item_remark'][$item_id];
				
				$con->sql_query("replace into tmp_sales_order_items ".mysql_insert_by_field($upd));
			}
		}
	}	
	
	function confirm(){
		$this->save(true);
	}
	
	function save($is_confirm = false){
		global $con, $smarty, $sessioninfo, $LANG, $config;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$form=$_REQUEST;
		
		$this->save_tmp_items($branch_id, $id);
		
		// validation
		$errm = array();
		if(!$form['uom_id']) $errm['top'] = sprintf($LANG['SO_EMPTY']);
		
		$arr= explode("-",$form['order_date']);
		$yy=$arr[0];
		$mm=$arr[1];
		$dd=$arr[2];
		if(!checkdate($mm,$dd,$yy)){
		   	$errm['top'][] = $LANG['SO_INVALID_DATE'];
			$form['order_date']='';
		}

		//Check Config date limit
		$check_date = strtotime($form['order_date']);
		
		if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
			$upper_limit = $config['upper_date_limit'];
			$upper_date = strtotime("+$upper_limit day" , strtotime("now"));

			if ($check_date>$upper_date){
				$errm['top'][] = $LANG['SO_DATE_OVER_LIMIT'];
				$form['order_date']='';
			}
		}

		if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
			$lower_limit = $config['lower_date_limit'];
			$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

			if ($check_date<$lower_date){
				$errm['top'][] = $LANG['SO_DATE_OVER_LIMIT'];
				$form['order_date']='';
			}
		}

		if($config['sales_order_require_batch_code'] && !trim($form['batch_code'])) $errm['top'][] = $LANG['SO_NO_BATCH_CODE'];
		if(!$form['debtor_id']) $errm['top'][] = $LANG['SO_NO_DEBTOR'];
		
		//print_r($form);exit;
		if(!$errm && $is_confirm){
            $params = array();
		    $params['type'] = 'SALES_ORDER';
		    $params['reftable'] = 'sales_order';
		    $params['user_id'] = $sessioninfo['id'];
		    $params['branch_id'] = $branch_id;
		    $params['doc_amt'] = $form['total_amount'];
		    
			if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
			$astat = check_and_create_approval2($params, $con);

	  	  	if(!$astat) $errm['top'][] = $LANG['SO_NO_APPROVAL_FLOW'];
	  		else{
	  			 $form['approval_history_id'] = $astat[0];
	     		 if ($astat[1] == '|'){
	     		 	$last_approval = true;
	     		 	if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
	     		 }
	  		}
		}
		
		if($errm){
			$smarty->assign("errm", $errm);
			$this->open($id, $branch_id, false);
			exit;
		}
		
		if ($is_confirm) $form['status'] = 1;
	    if ($last_approval) $form['approved'] = 1;
	    $form['last_update'] = 'CURRENT_TIMESTAMP';

		$update_fiels = array('batch_code', 'debtor_id', 'order_date', 'cust_po', 'status','approved','total_ctn', 'total_pcs', 'total_amount', 'total_qty', 'remark','last_update','approval_history_id','use_promo_price','sheet_discount','sheet_discount_amount','selling_type'
		,'is_under_gst', 'total_gross_amt', 'total_gst_amt', 'sheet_gst_discount','is_special_exemption','special_exemption_rcr', 'use_debtor_price');
		
		if (is_new_id($id)){
			$form['added'] = 'CURRENT_TIMESTAMP';
            $form['user_id'] = $sessioninfo['id'];

			$update_fiels[] = "branch_id";
			$update_fiels[] = "user_id";
			$update_fiels[] = "added";
			
			$con->sql_query("insert into sales_order ".mysql_insert_by_field($form, $update_fiels));
			$form['id'] = $con->sql_nextid();
		}
		else{
			$update_fiels[] = "amt_need_update";
			$form['amt_need_update'] = 0;
			
		    $con->sql_query("update sales_order set ".mysql_update_by_field($form, $update_fiels)." where branch_id=$branch_id and id=$id");
		}
		if(!$form['order_no']){
            $formatted = sprintf("%05d",$form['id']);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$b = $con->sql_fetchrow();
			$form['order_no'] = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($form['order_no'])." where branch_id=$branch_id and id=".mi($form['id']));
		}

        //copy tmp table to real items table
		$q1=$con->sql_query("select * from tmp_sales_order_items where sales_order_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id] order by id") or die(mysql_error());
		$first_id = 0;
		while($r=$con->sql_fetchrow($q1)){
			$upd['sales_order_id']=$form['id'];
			$upd['branch_id']=$r['branch_id'];
			$upd['sku_item_id']=$r['sku_item_id'];
			$upd['cost_price']=$r['cost_price'];
			$upd['selling_price']=$r['selling_price'];
			$upd['uom_id']=$r['uom_id'];
			$upd['ctn']=$r['ctn'];
			$upd['pcs']=$r['pcs'];
			$upd['stock_balance'] = $r['stock_balance'];
			$upd['parent_stock_balance'] = $r['parent_stock_balance'];
			$upd['do_qty'] = $r['do_qty'];
			$upd['item_discount'] = $r['item_discount'];
			$upd['item_discount_amount'] = $r['item_discount_amount'];
			$upd['bom_ref_num'] = $r['bom_ref_num'];
			$upd['bom_qty_ratio'] = $r['bom_qty_ratio'];
			$upd['gst_id'] = $r['gst_id'];
			$upd['gst_code'] = $r['gst_code'];
			$upd['gst_rate'] = $r['gst_rate'];
			$upd['item_discount_amount2'] = $r['item_discount_amount2'];
			$upd['line_gross_amt'] = $r['line_gross_amt'];
			$upd['line_gst_amt'] = $r['line_gst_amt'];
			$upd['line_amt'] = $r['line_amt'];
			$upd['line_gross_amt2'] = $r['line_gross_amt2'];
			$upd['line_gst_amt2'] = $r['line_gst_amt2'];
			$upd['line_amt2'] = $r['line_amt2'];
			$upd['remark'] = $r['remark'];
			
			$con->sql_query("insert into sales_order_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			if ($first_id==0) $first_id = $con->sql_nextid();
		}
		
		if ($first_id>0) {
			if(!is_new_id($id)){
				$con->sql_query("delete from sales_order_items where branch_id=$branch_id and sales_order_id=$id and id<$first_id");
			}
			$con->sql_query("delete from tmp_sales_order_items where sales_order_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		}
		else{
			die("System error: Insert items failed. Please do not open multiple DO page, close all other opened DO page and try again. If problem still exists please contact ARMS technical support.");
		}
		
		$t = '';
		$formatted=sprintf("%05d",$form['id']);
	    //select report prefix from branch
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
		$b = $con->sql_fetchrow();
		
		if ($is_confirm){
			$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
			
	        log_br($sessioninfo['id'], 'SALES_ORDER', $form['id'], "Confirmed: (ID#".$b['report_prefix'].$formatted.", Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		    if ($last_approval){
		    	if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;	// pass is direct approve to next function
                sales_order_approval($form['id'], $branch_id, $form['status'], true);
                $t = 'approved';
			}
			else{    
                $t = 'confirmed';
				$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'sales_order');
				send_pm2($to, "Sales Order Approval (ID#$form[id])", "sales_order.php?a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'sales_order'));
			}
				
		}
		else{
	        log_br($sessioninfo['id'], 'SALES_ORDER', $form['id'], "Saved: (ID#".$b['report_prefix'].$formatted." ,Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
	        $t = 'saved';
		}

		header("Location: $_SERVER[PHP_SELF]?t=$form[a]&save_id=$form[id]");
	}
	
	function do_reset(){
		global $con, $smarty;
		
		$form = $_REQUEST;
		$fail = reset_order($form['id'], $form['branch_id']);

		if($fail) {
			$_REQUEST['a']="view";
			$this->view();

		}
		
	}
	
	function delete(){
        global $con, $sessioninfo;
		$form = $_REQUEST;
        $id = $form['id'];
        $branch_id = $form['branch_id'];
        
	    if(!$type) $type='delete';
	    if(!$status) $status=4;
		$reason=ms($form['reason']);

	    $con->sql_query("update sales_order set cancelled_by=$sessioninfo[id], reason=$reason, status=$status where id=$id and branch_id=$branch_id");

	    $con->sql_query("delete from tmp_sales_order_items where sales_order_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");

	    header("Location: /sales_order.php?t=$type&save_id=$id");
	}
	
	function ajax_search_batch_code(){
	    global $con, $smarty;
        $v = trim($_REQUEST['value']);
        $LIMIT = 50;
        // call with limit
		$result1 = $con->sql_query("select distinct(batch_code) as batch_code from sales_order where batch_code like ".ms('%'.replace_special_char($v).'%')." and delivered = 0 order by batch_code limit ".($LIMIT+1));
        print "<ul>";
		if ($con->sql_numrows($result1) > 0)
		{

		    if ($con->sql_numrows($result1) > $LIMIT)
		    {
				print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
			}

			// generate list.
			while ($r = $con->sql_fetchrow($result1))
			{
				$out .= "<li title=".htmlspecialchars($r['batch_code'])."><span>".htmlspecialchars($r['batch_code']);
				$out .= "</span>";
				$out .= "</li>";
			}
        }
        else
        {
           print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
		}
		print $out;
        print "</ul>";
		exit;
	}
	
	function print_order(){
		global $con,$smarty;
        $id = $_REQUEST['id'];
        $branch_id = $_REQUEST['branch_id'];
		
		//get admin logo system_settings and branch logo setting 
		$system_settings = array();
		$setting_list = array('logo_vertical', 'verticle_logo_no_company_name');
		foreach($setting_list as $setting_name){
			$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
			$r = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$system_settings[$setting_name] = $r['setting_value'];
		}
		$qry1 = $con->sql_query("select is_vertical_logo,vertical_logo_no_company_name from branch where id=$branch_id");
		$r1 = $con->sql_fetchassoc($qry1);
		$con->sql_freeresult($qry1);
		if($r1['is_vertical_logo'] == 1){
			$system_settings['verticle_logo_no_company_name'] = $r1['vertical_logo_no_company_name'];
			$system_settings['logo_vertical'] = $r1['is_vertical_logo'];
		}		
		$smarty->assign("system_settings",$system_settings);
		
        print_order($branch_id, $id);
	}
	
	/*function ajax_use_promo_price(){
		global $con, $smarty, $sessioninfo;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$this->save_tmp_items($branch_id, $id);
		$form = $_REQUEST;

		$date = $form['order_date'];
		$ret = array();
		if($form['uom_id']){
            foreach($form['uom_id'] as $item_id=>$uom_id){
				//$con->sql_query("select sku_item_id from tmp_sales_order_items where id=".mi($item_id)." and branch_id=$branch_id and user_id=$sessioninfo[id]");
				//$sid = mi($con->sql_fetchfield(0));
				//$con->sql_freeresult();
				$sid = mi($form['item_sku_item_id'][$item_id]);
				
				if($form['use_promo_price']){	// use promotion price
					$ret[$item_id]['selling_price'] = mf(get_lowest_price($branch_id, $sid, $date));
				}else{	// use normal selling price
					$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price 
					from sku_items si 
					left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
					where si.id=$sid");
					$ret[$item_id]['selling_price'] = $con->sql_fetchfield(0);
					$con->sql_freeresult();
				}
				
			}
		}
		print json_encode($ret);
	}*/
	
	function ajax_search_order_no(){
		global $con, $smarty, $sessioninfo;
        $v = trim($_REQUEST['value']);
        $LIMIT = 50;
        
        $filter = array();
        $filter[] = "active=1 and status=1 and approved=1 and can_generate_po=1";
        $filter[] = "order_no like ".ms('%'.replace_special_char($v).'%');
        if(BRANCH_CODE != 'HQ'){
			$filter[] = "branch_id=".$sessioninfo['branch_id'];
		}
        $filter = "where ".join(' and ', $filter);
		$result1 = $con->sql_query("select distinct(order_no) as order_no from sales_order $filter order by order_no limit ".($LIMIT+1));
        print "<ul>";
		if ($con->sql_numrows($result1) > 0)
		{

		    if ($con->sql_numrows($result1) > $LIMIT)
		    {
				print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
			}

			// generate list.
			while ($r = $con->sql_fetchrow($result1))
			{
				$out .= "<li title=".htmlspecialchars($r['order_no'])."><span>".htmlspecialchars($r['order_no']);
				$out .= "</span>";
				$out .= "</li>";
			}
        }
        else
        {
           print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
		}
		print $out;
        print "</ul>";
		exit;
	}
	
	function ajax_add_order_no_to_generate_po(){
		global $con, $smarty, $sessioninfo;
		
		$order_no = trim($_REQUEST['order_no']);
		if(!$order_no)	die("Invalid Order No ($order_no).");
		
		$filter = array();
        $filter[] = "active=1 and status=1 and approved=1 and can_generate_po=1";
        $filter[] = "order_no=".ms($order_no);
        if(BRANCH_CODE != 'HQ'){
			$filter[] = "branch_id=".$sessioninfo['branch_id'];
		}
        $filter = "where ".join(' and ', $filter);
        
		$con->sql_query("select * from sales_order $filter");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$form)	die("Invalid Order No ($order_no).");
		
		$ret = array();
		$ret['ok'] = 1;
		$smarty->assign('so', $form);
		$ret['html'] = $smarty->fetch('sales_order.generate_po.so_row.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_generate_po(){
		global $con, $smarty, $sessioninfo, $config, $appCore;
		
		$so_list = $_REQUEST['so_list'];
		$vendor_type = trim($_REQUEST['vendor_type']);
		$po_branch_id = BRANCH_CODE == 'HQ' ? mi($_REQUEST['po_branch_id']) : $sessioninfo['branch_id'];
		if(!$po_branch_id)	$po_branch_id = $sessioninfo['branch_id'];
		$date = date("Y-m-d");
		//print_r($so_list);
		
		if(!is_array($so_list) || !$so_list){
			die("Invalid Sales Order to be generate.");
		}
		
		$vendor_col = 'master_vendor_id';
		if($vendor_type=='last_vendor'){
			$extra_col = ',vsh.vendor_id as vsh_vendor_id';
			$extra_join = "left join vendor_sku_history_b".$sessioninfo['branch_id']." vsh on vsh.sku_item_id=soi.sku_item_id and ".ms($date)." between vsh.from_date and vsh.to_date";
			$vendor_col = 'vsh_vendor_id';
		}
		$so_data = array();
		$po_info = array();
		$generated_po_key = array();
		$order_no_list = array();
		$ret = array();
		
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $sessioninfo['branch_id'];
			$prms['date'] = date("Y-m-d");
			$branch_is_under_gst = check_gst_status($prms);
			
			if($branch_is_under_gst) {
				$output_gst_list = construct_gst_list('supply');
			}
		}
		//die("branch_is_under_gst = $branch_is_under_gst");
		
		$ret['po_html'] = "<h2>Generated PO</h2>";
		foreach($so_list as $tmp_so_id){
			list($bid, $so_id) = explode("-", $tmp_so_id);
			
			$bid = mi($bid);
			$so_id = mi($so_id);
			
			// get sales order
			$con->sql_query("select * from sales_order where branch_id=$bid and id=$so_id and active=1 and status=1 and approved=1 and can_generate_po=1");
			$so = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$so){
				die("Sales Order Branch ID#$bid, ID#$so_id not found.");
			}
			
			$so_data[$tmp_so_id]['sales_order'] = $so;
			$order_no_list[] = $so['order_no'];
			
			// get sales order items
			$sql = "select soi.*,c.department_id, sku.vendor_id as master_vendor_id,uom.fraction as uom_fraction,si.artno,si.mcode, ifnull(sip.price, si.selling_price) as latest_selling_price
			$extra_col
			from sales_order_items soi
			left join sku_items si on si.id=soi.sku_item_id
			left join sku on sku.id=si.sku_id
			left join category c on c.id=sku.category_id
			left join uom on uom.id = soi.uom_id
			left join sku_items_price sip on sip.branch_id=".mi($sessioninfo['branch_id'])." and sip.sku_item_id=si.id
			$extra_join
			where soi.branch_id=$bid and soi.sales_order_id=$so_id";
			//print $sql;
			$q1 = $con->sql_query($sql);
			while($r = $con->sql_fetchassoc($q1)){
				// check whether this items already generate to PO
				$con->sql_query("select poi.id
				from po_items poi
				left join po on po.branch_id=poi.branch_id and po.id=poi.po_id
				where po.active=1 and poi.so_branch_id=".mi($r['branch_id'])." and poi.so_item_id=".mi($r['id'])." limit 1");
				$tmp_po_info = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($tmp_po_info){
					unset($tmp_po_info);
					continue;
				}
				$so_data[$tmp_so_id]['sales_order_items'][] = $r;
				
				$dept_id = mi($r['department_id']);
				$vendor_id = mi($r[$vendor_col]);
				if(!$vendor_id)	$vendor_id = $r['master_vendor_id'];
				
				$po_key = $dept_id.'-'.$vendor_id.'-';
				if(!isset($po_info[$po_key])){
					$po_info[$po_key] = array();
				}
				
				$po_info[$po_key]['items'][] = $r;
			}
			$con->sql_freeresult($q1);
		}
		
		
		foreach($po_info as $po_key => $tmp_info){
			list($dept_id, $vid) = explode('-', $po_key);
			$is_under_gst = 0;			
		
			$po = array();
		    $po['branch_id'] = $sessioninfo['branch_id'];
		    $po['user_id'] = $sessioninfo['id'];
		    $po['po_branch_id'] = $po_branch_id;
		    $po['po_date'] = date("Y-m-d");
		    $po['vendor_id'] = $vid;
		    $po['department_id'] = $dept_id;
		    $po['added'] = $po['last_update'] = 'CURRENT_TIMESTAMP';
		    $po['remark2'] = serialize('GENERRATED BY SALES ORDER');
		    $po['po_create_type'] = 1; // 1 = create by sales order
			
			// check whether this vendor got gst
			if($config['enable_gst']){
				$prms = array();
				$prms['vendor_id'] = $po['vendor_id'];
				$prms['date'] = $po['po_date'];
				$is_under_gst = check_gst_status($prms);
				
				// if found got set special vendor gst code, then all items must default choose it
				if($is_under_gst){
					if(!$input_gst_list) $input_gst_list = construct_gst_list('purchase');
				}				
			}
			
		    //if($config['po_enable_ibt'])	$po['is_ibt'] = $is_ibt;
			$po['is_under_gst'] = $is_under_gst;
		    
		    $con->sql_query("insert into po ".mysql_insert_by_field($po));
		    $po_id = $con->sql_nextid();
		    
		    $generated_po_key[] = $po['branch_id'].'-'.$po_id;
		    $po_amount = 0;
		    
		    foreach($tmp_info['items'] as $item){
				$po_items = array();
				$po_items['branch_id'] = $po['branch_id'];
				$po_items['po_id'] = $po_id;
				$po_items['user_id'] = $po['user_id'];
				$po_items['sku_item_id'] = $item['sku_item_id'];
				$po_items['selling_price'] = $item['latest_selling_price'];
				$po_items['selling_uom_id'] = $item['uom_id'];
				$po_items['order_uom_id'] = $item['uom_id'];
				$po_items['order_uom_fraction'] = $item['uom_fraction'];
				$po_items['order_price'] = $item['cost_price'];
				$po_items['selling_uom_fraction'] = $item['uom_fraction'];;
				$po_items['artno_mcode'] = $si['artno'] ? $si['artno'] : $si['mcode'];
				$po_items['stock_balance'] = $item['stock_balance'];

				
				if($branch_is_under_gst) {				
					// get inclusive tax info for selling price
					$inclusive_tax = get_sku_gst("inclusive_tax", $item['sku_item_id']);
					$output_gst = get_sku_gst("output_tax", $item['sku_item_id']);
					if($output_gst){
						$po_items['selling_gst_id'] = $output_gst['id'];
						$po_items['selling_gst_code'] = $output_gst['code'];
						$po_items['selling_gst_rate'] = $output_gst['rate'];
					}else{
						$po_items['selling_gst_id'] = $output_gst_list[0]['id'];
						$po_items['selling_gst_code'] = $output_gst_list[0]['code'];
						$po_items['selling_gst_rate'] = $output_gst_list[0]['rate'];
					}
					
					
					$prms = array();
					$prms['selling_price'] = $po_items['selling_price'];
					$prms['inclusive_tax'] = $inclusive_tax;
					$prms['gst_rate'] = $po_items['selling_gst_rate'];
					$gst_sp_info = calculate_gst_sp($prms);
					$po_items['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					
					if($inclusive_tax == "yes"){
						$po_items['gst_selling_price'] = $po_items['selling_price'];
						$po_items['selling_price'] = $gst_sp_info['gst_selling_price'];
					}else{
						$po_items['gst_selling_price'] = $gst_sp_info['gst_selling_price'];
					}
				}
				
				if($is_under_gst){
					// if found got set special vendor gst code, then all items must default choose it
					$vendor_special_gst_id = $appCore->vendorManager->getVendorSpecialGSTID($po['vendor_id']);
					if($vendor_special_gst_id > 0){
						foreach($input_gst_list as $tmp_gst_info){
							if($tmp_gst_info['id'] == $vendor_special_gst_id){
								$po_items['cost_gst_id'] = $tmp_gst_info['id'];
								$po_items['cost_gst_code'] = $tmp_gst_info['code'];
								$po_items['cost_gst_rate'] = $tmp_gst_info['rate'];
								break;
							}
						}
					}
					
					if(!$po_items['cost_gst_id']){ // check to get cost GST info
						$input_gst = get_sku_gst("input_tax", $item['sku_item_id']);
						if($input_gst){
							$po_items['cost_gst_id'] = $input_gst['id'];
							$po_items['cost_gst_code'] = $input_gst['code'];
							$po_items['cost_gst_rate'] = $input_gst['rate'];
						}else{
							$po_items['cost_gst_id'] = $input_gst_list[0]['id'];
							$po_items['cost_gst_code'] = $input_gst_list[0]['code'];
							$po_items['cost_gst_rate'] = $input_gst_list[0]['rate'];
						}
					}
				}
				
				// get sales trend
				$sales_trend = get_sales_trend($po_items['sku_item_id']);
				$po_items['sales_trend'] =  $sales_trend['sales_trend'];

				$po_items['qty'] = $item['ctn'];
				$po_items['qty_loose'] = $item['pcs'];
				$po_amount += ($po_items['qty'] * $po_items['order_price']) + ($po_items['qty_loose']/$po_items['order_uom_fraction'] * $po_items['order_price']);
				
				$po_items['sales_trend'] = serialize($po_items['sales_trend']);
				
				// sales order reference
				$po_items['so_branch_id'] = $item['branch_id'];
				$po_items['so_item_id'] = $item['id'];
				
				$con->sql_query("insert into po_items ".mysql_insert_by_field($po_items));
				
				// mark this po use for which sales order
				$tmp_so_id = $item['branch_id'].'-'.$item['sales_order_id'];
				$po_ref_key = $po['branch_id'].'-'.$po_id;
				$so_data[$tmp_so_id]['sales_order']['po_ref_list'][$po_ref_key] = 1;
			}
			
			//$con->sql_query("update po set po_amount=".ms($po_amount)." where branch_id=".mi($po['branch_id'])." and id=".mi($po_id));
			//$appCore->poManager->reCalcatePOUsingOldMethod($po['branch_id'], $po_id);
			$appCore->poManager->reCalcatePOAmt($po['branch_id'], $po_id);
			$ret['po_html'] .= "<a href='po.php?a=open&branch_id=".$po['branch_id']."&id=$po_id' target='_blank'>PO#$po_id</a><br />";
		}
		
		// update sales order usage
		foreach($so_data as $tmp_so_id=>$tmp_so){
			$so = $tmp_so['sales_order'];
			
			$upd = array();
			$upd['po_ref'] = ($so['po_ref'] ? $so['po_ref'] : "|").join("|", array_keys($so['po_ref_list']))."|";
			$upd['po_used'] = 1;	// mark alrdy used in PO
			$upd['can_generate_po'] = 0; // mark cannot create any PO for this SO
			
			$con->sql_query("update sales_order set ".mysql_update_by_field($upd)." where branch_id=".$so['branch_id']." and id=".$so['id']);
		}
		
		log_br($sessioninfo['id'], 'SALES_ORDER', 1, "Generate Sales Order to PO: Sales Order No. (".join(',', $order_no_list).")");
		
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function reload_item_price(){
		global $con, $smarty, $sessioninfo, $config, $appCore;
		
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$this->save_tmp_items($branch_id, $id);
		$form = $_REQUEST;

		$date = $form['order_date'];
		$ret = array();
		if($form['uom_id']){
            foreach($form['uom_id'] as $item_id=>$uom_id){
				/*$con->sql_query("select sku_item_id from tmp_sales_order_items where id=".mi($item_id)." and branch_id=$branch_id and user_id=$sessioninfo[id]");
				$sid = mi($con->sql_fetchfield(0));
				$con->sql_freeresult();*/
				$sid = mi($form['item_sku_item_id'][$item_id]);
				$selling_price = 0;
				if($form['use_debtor_price']){
					$selling_price = $appCore->skuManager->getSKUItemDebtorPrice($branch_id, $sid, $form['debtor_id']);
				}
				
				if(!$selling_price){
					if($form['use_promo_price']){	// use promotion price
						$selling_price = mf(get_lowest_price($branch_id, $sid, $date));
					}else{	// use normal selling price or mprice
						if($form['selling_type']){
							$extra_col = ", simp.price as mprice";
							$left_join = "left join sku_items_mprice simp on simp.branch_id=$branch_id and simp.sku_item_id=si.id and simp.type=".ms($form['selling_type']);
						}
						$con->sql_query("select ifnull(sip.price, si.selling_price) as selling_price $extra_col
						from sku_items si 
						left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
						$left_join
						where si.id=$sid");
						$tmp = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($form['selling_type'] && $tmp['mprice']>0){
							$selling_price = $tmp['mprice'];
						}else{
							$selling_price = $tmp['selling_price'];
						}
					}
				}
				$ret[$item_id]['selling_price'] = $selling_price;

				// GST
				if($config['enable_gst']){
					if($form['is_under_gst']){
						// get sku is inclusive
						$is_sku_inclusive = get_sku_gst("inclusive_tax", $sid);
						// get sku original output gst
						$sku_original_output_gst = get_sku_gst("output_tax", $sid);
						
						if($is_sku_inclusive == 'yes'){
							// is inclusive tax
							// find the price before tax
							$gst_tax_price = round($ret[$item_id]['selling_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
							$ret[$item_id]['selling_price'] -= $gst_tax_price;
						}
					}
				}
			}
		}
		print json_encode($ret);
	}
	
	function ajax_load_parent_child(){
		global $con, $smarty, $sessioninfo, $config;
		
		$sku_code_list = join(",", array_map("ms", explode(',', $_REQUEST['sku_code_list'])));
		$bid = $sessioninfo['branch_id'];
		
		if(!$sku_code_list) die("No item selected");
		
		$sku_id_list = array();
		$q1 = $con->sql_query("select tmp_si.sku_id from sku_items tmp_si where tmp_si.sku_item_code in (".$sku_code_list.")");
		while($r = $con->sql_fetchassoc($q1)){
			$sku_id_list[] = $r['sku_id'];
		}
		$con->sql_freeresult($q1);

		$sql = "select si.id,si.sku_item_code,si.mcode,si.link_code,si.description,si.artno,if(sip.price is null,si.selling_price,sip.price) as price, 
				si.doc_allow_decimal, sic.qty,u.code as master_uom_code, if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as discount_code
				from sku_items si
				left join sku on sku.id=si.sku_id
				left join uom u on u.id = si.packing_uom_id
				left join sku_items_price sip on si.id=sip.sku_item_id and sip.branch_id=".mi($bid)."
				left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=".mi($bid)."
				where si.sku_id in (".join(",", $sku_id_list).") and si.active=1
				order by si.sku_item_code, si.description";

		$q1 = $con->sql_query($sql) or die(mysql_error());

		while($r = $con->sql_fetchassoc($q1)){
			$items[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('items',$items);
		$smarty->display('parent_child_add.tpl');
	}
	
	function ajax_parent_child_add(){
		global $con, $smarty, $sessioninfo;
		
		$this->ajax_add_item_row();
	}

	function multi_save(){
		global $con, $smarty, $sessioninfo, $LANG, $config;
		
		$branch_id = $sessioninfo['branch_id'];
		$form = $_REQUEST;
		$data = array();
		
		foreach($form['so_id_n_debtor_n_integration'] as $value){
			$split = explode(',', $value);
			$s_id[] = $split[0];
			$debtor_id[] = $split[1];
			$inte_code[] = $split[2];
		}

		$k=0;
		foreach($s_id as $value){
			$data[$k]['so_id'] = $value;
			$k++;
		}
		$k=0;
		foreach($debtor_id as $value){
			$data[$k]['debtor_id'] = $value;
			$k++;
		}
		$k=0;
		foreach($inte_code as $value){
			$data[$k]['integration_code'] = $value;
			$k++;
		}

		foreach($form['select_sku'] as $value){
			$split = explode(',', $value);
			$integration_code[] = $split[0];
			$mcode[] = $split[1];
			$artno[] = $split[2];
			$uom_id[] = $split[3];
			$carton[] = $split[4];
			$sku_item_code[] = $split[5];
		}

		$k=0;
		foreach($integration_code as $value){
			$sales_item[$k]['integrate_code'] = $value;
			$k++;
		}
		$k=0;
		foreach($sku_item_code as $value){
			$sales_item[$k]['sku_code'] = $value;
			$k++;
		}
		$k=0;
		foreach($mcode as $value){
			$sales_item[$k]['mcode'] = $value;
			$k++;
		}
		$k=0;
		foreach($artno as $value){
			$sales_item[$k]['artno'] = $value;
			$k++;
		}
		$k=0;
		foreach($uom_id as $value){
			$sales_item[$k]['uom_id'] = $value;
			$k++;
		}
		$k=0;
		foreach($carton as $value){
			$sales_item[$k]['carton'] = $value;
			$k++;
		}
		
		foreach($data as $key => $value){
			$id = $value['so_id'];
			$form['debtor_id'] = $value['debtor_id'];
			$form['integration_code'] = $value['integration_code'];

			// validation
			$errm = array();
			if(!$form['uom_id']) $errm['top'] = sprintf($LANG['SO_EMPTY']);
			
			$arr = explode("-",$form['order_date']);
			$yy = $arr[0];
			$mm = $arr[1];
			$dd = $arr[2];
			if(!checkdate($mm,$dd,$yy)){
			   	$errm['top'][] = $LANG['SO_INVALID_DATE'];
				$form['order_date'] = '';
			}

			//Check Config date limit
			$check_date = strtotime($form['order_date']);
			
			if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
				$upper_limit = $config['upper_date_limit'];
				$upper_date = strtotime("+$upper_limit day" , strtotime("now"));

				if ($check_date>$upper_date){
					$errm['top'][] = $LANG['SO_DATE_OVER_LIMIT'];
					$form['order_date']='';
				}
			}

			if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
				$lower_limit = $config['lower_date_limit'];
				$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));

				if ($check_date<$lower_date){
					$errm['top'][] = $LANG['SO_DATE_OVER_LIMIT'];
					$form['order_date']='';
				}
			}

			if($config['sales_order_require_batch_code'] && !trim($form['batch_code'])) $errm['top'][] = $LANG['SO_NO_BATCH_CODE'];
			
			//print_r($form);exit;
			if(!$errm && $is_confirm){
	            $params = array();
			    $params['type'] = 'SALES_ORDER';
			    $params['reftable'] = 'sales_order';
			    $params['user_id'] = $sessioninfo['id'];
			    $params['branch_id'] = $branch_id;
			    $params['doc_amt'] = $form['total_amount'];
			    
				if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
				$astat = check_and_create_approval2($params, $con);

		  	  	if(!$astat) $errm['top'][] = $LANG['SO_NO_APPROVAL_FLOW'];
		  		else{
		  			 $form['approval_history_id'] = $astat[0];
		     		 if ($astat[1] == '|'){
		     		 	$last_approval = true;
		     		 	if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
		     		 }
		  		}
			}

			$con->sql_query("select debtor_mprice_type,special_exemption from debtor where integration_code = '".$form['integration_code']."' and active = 1");
			$debtor = $con->sql_fetchassoc();
			$form['selling_type'] = $debtor['debtor_mprice_type'];
			$form['is_special_exemption'] = $debtor['special_exemption'];
			$con->sql_freeresult();
			
			if ($is_confirm) $form['status'] = 1;
		    if ($last_approval) $form['approved'] = 1;
		    $form['last_update'] = 'CURRENT_TIMESTAMP';

			$update_fiels = array('batch_code', 'debtor_id', 'order_date', 'cust_po', 'status','approved','total_ctn', 'total_pcs', 'total_amount', 'total_qty', 'remark','last_update','approval_history_id','use_promo_price','sheet_discount','sheet_discount_amount','selling_type'
			,'is_under_gst', 'total_gross_amt', 'total_gst_amt', 'sheet_gst_discount','is_special_exemption','special_exemption_rcr','integration_code');
			
			if (is_new_id($id)){
				$form['added'] = 'CURRENT_TIMESTAMP';
	            $form['user_id'] = $sessioninfo['id'];

				$update_fiels[] = "branch_id";
				$update_fiels[] = "user_id";
				$update_fiels[] = "added";

				$con->sql_query("insert into sales_order ".mysql_insert_by_field($form, $update_fiels));
				$form['id'] = $con->sql_nextid();
			}
			else{
				$update_fiels[] = "amt_need_update";
				$form['amt_need_update'] = 0;
				
			    $con->sql_query("update sales_order set ".mysql_update_by_field($form, $update_fiels)." where branch_id=$branch_id and id=$id");
			}

            $formatted = sprintf("%05d",$form['id']);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$b = $con->sql_fetchrow();
			$form['order_no'] = $b['report_prefix'].$formatted;
			$con->sql_query("update sales_order set order_no=".ms($form['order_no'])." where branch_id=$branch_id and id=".mi($form['id']));

			foreach($sales_item as $value){
				if($value['integrate_code'] == $form['integration_code']){
					$sales_order_item_id = $this->add_sales_item($form['id'],$value['sku_code'], $value['mcode'], $value['artno'], $branch_id, $value['carton'], $value['integrate_code'], $value['uom_id']);
				}
			}
			// update total amt and qty to sales_order table, function from sales_order.include.php
			$so_data = recalculate_sales_order($branch_id, $form['id']);

			log_br($sessioninfo['id'], 'SALES ORDER CSV', $form['id'], "Saved: (ID#".$b['report_prefix'].$formatted." , Pcs:$so_data[pcs], Ctn:$so_data[ctn], Amt:".sprintf("%.2f",$so_data[amt]).", Total Qty:$so_data[qty])");
		}
		header("Location: $_SERVER[PHP_SELF]?t=$form[a]&save_id=$form[id]");
	}

	function upload_csv($id = 0, $branch_id = 0, $load_header = true){
        global $con, $sessioninfo, $smarty, $LANG, $config, $mprice_type_list;

		// delete old tmp items
        $con->sql_query("delete from tmp_sales_order_items where (sales_order_id>1000000000 and sales_order_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
        $form = $_REQUEST;
 
        if(!$id){
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
		}

		if(!$config['single_server_mode']&&$branch_id>0&&$id>0&&$branch_id!=$sessioninfo['branch_id']&&$_REQUEST['a']!='view'){
			header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
			exit;
		}

        if(!is_new_id($id)&&$branch_id){    // exists order
            if($load_header){
                $form = load_order_header($branch_id, $id, true);
                if($_REQUEST['a']=='open')	$this->copy_to_tmp($branch_id, $id);

                if(($form['approved']||!$form['active'])&& $_REQUEST['a']!='view'){

		            header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
					exit;
				}
			}
			if($form['selling_type'] && !in_array($form['selling_type'], $mprice_type_list)){
				$mprice_type_list[] = $form['selling_type'];
				$smarty->assign('mprice_type_list', $mprice_type_list);
			}
		}else{  // new order
			if(!$id){
                $id=time();
				$form['id']=$id;
				$form['order_date'] = date("Y-m-d");
			}
			sku_multiple_selling_price_handler($form);
		}
		
		if($branch_id==0) $branch_id=$sessioninfo['branch_id'];

		if($_REQUEST['a'] != 'view'){
			// check gst status
			if($config['enable_gst'] && $form['order_date'])	$form['is_under_gst'] = check_gst_status(array('date'=>$form['order_date'], 'branch_id'=>$branch_id));
		}
		
		// load special exemption relief claus remark if it is new DO or existing DO but does not have the remark (edit mode)
		if($config['enable_gst'] && $form['is_under_gst'] && !$form['special_exemption_rcr']){
			$q1 = $con->sql_query("select * from gst_settings where setting_name = 'special_exemption_relief_claus_remark'");
			$sercr_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			$form['special_exemption_rcr'] = $sercr_info['setting_value'];
			if(!$form['special_exemption_rcr']) $form['special_exemption_rcr'] = $config['se_relief_claus_remark'];
		}
		
		$items = load_order_items($branch_id, $id, ($_REQUEST['a']!='view'));

		$smarty->assign('PAGE_TITLE', $this->title.' - '.(is_new_id($id)?'New Order':$form['order_no']));
		$smarty->assign('form', $form);
				
		$smarty->assign('items', $items);
		$smarty->display('sales_order.upload_csv.tpl');
	}

	function show_result(){
		global $con, $smarty, $sessioninfo, $LANG, $config, $gst_list;

		$form = $_REQUEST;
		$branch_id = $sessioninfo['branch_id'];
		$file = $_FILES['import_csv'];
		$sample_file = substr($file['name'], 0, 6);
		$batch_code = substr($file['name'], 0, -4);
		if($file['error'] > 0){
			print "<script>alert('Please upload csv file again.')</script>";
		}else{
			$f = fopen($file['tmp_name'], "rt");
		}
		$item_lists = array();		
		$flag = true;

		if(isset($f)){
			while($r = fgetcsv($f)){
				$ins = array();
				if($flag) { $flag = false; continue; }
				switch($form['method']) {
					case '1':						
						
						$ins['disabled'] = 0;
						$ins['integration_code'] = trim($r[1]);
						$ins['artno'] = trim($r[3]);
						$ins['mcode'] = trim($r[4]);
						$err_msg = '';
						
						$filter = array();
						
						if($ins['mcode']){	// mcode got value
							$filter[] = "mcode=".ms($ins['mcode']);
						}
						
						if($ins['artno']){	// artno got value
							$filter[] = "artno=".ms($ins['artno']);
						}
						
						if(!$filter){	// No MCode and Art No
							$err_msg = 'SKU must has Art No. or Mcode.';
						}
						
						$ins['link_code'] = trim($r[5]);
						$ins['description'] = trim($r[6]);
						$uom = trim($r[7]);

						// Check UOM Exists or not
						$q_uom = $con->sql_query("select id, code from uom where code = ".ms($uom)." and active = 1");
						$d_uom = $con->sql_fetchassoc($q_uom);
						$con->sql_freeresult($q_uom);
						
						$ins['uom_id'] = $d_uom['id'];
						$ins['uom'] = $d_uom['code'];

						if(!$ins['uom_id']){	// UOM Not Exists
							$ins['uom'] = $uom;
							if(!$err_msg)	$err_msg = 'UOM code not found in the system.';
						}

						$ins['suggest_po_ctn_by_branch'] = trim($r[8]);					

						if(!$err_msg){	// No Error, Try to search sku
							$str_filter = "where ".join(" and ", $filter);
							$q_sku_item_code = $con->sql_query("select sku_item_code from sku_items $str_filter and active = 1");
							$d_sku_item_code = $con->sql_fetchassoc($q_sku_item_code);
							$con->sql_freeresult($q_sku_item_code);
							
							$ins['sku_item_code'] = $d_sku_item_code['sku_item_code'];
							if(!$ins['sku_item_code'])	$err_msg = 'Item Not Found.';	// SKU Not Found
						}
						
						if($err_msg){	// Got Error
							$ins['remark'] = $err_msg;
							$ins['disabled'] = 1;
							$ins['uncheck'] = 1;
						}

						break;
					default:
						break;
				}
				$item_lists['csv'][] = $ins;
			}
		

			foreach($item_lists['csv'] as $key => $value){
				$a = $value['integration_code'];
				if($a == $b){
					continue;
				}
				$int_arr[$a] = '';
				$b = $value['integration_code'];
			}		

			$i = 0;
			foreach($int_arr as $key => $value){
				$q1 = $con->sql_query("select id, code, description from debtor where integration_code = '".$key."'");
				$debtor_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);			

				$debtor['debtor_id'] = $debtor_info['id'];
				$debtor['code'] = $debtor_info['code'];
				$debtor['description'] = $debtor_info['description'];
				$debtor['integration_code'] = $key;

				if($debtor['debtor_id'] == ''){
					$debtor['debtor_remark'] = "Please assign Integration Code ($key) to the Debtor.";
					$debtor['debtor_disabled'] = 1;
				}else{
					$debtor['debtor_remark'] = "";
					$debtor['debtor_disabled'] = 0;
				}

				$int_arr[$key] = $debtor;

				$so_id = $_REQUEST['id'] + $i;
				$int_arr[$key]['so_id'] = $so_id;
				$i++;
			}

			foreach($item_lists['csv'] as $key => $r){
				$tmp_code = $r['integration_code'];

				if(isset($int_arr[$tmp_code])){
					$int_arr[$tmp_code]['sku_item_info_list'][] = $r;		
				}
			}

			// disable the debtor check box if there are no item be selected
			foreach($int_arr as $integration_code => $data){
				$total = 0;
				// check duplicate art number
				foreach($data['sku_item_info_list'] as $key => $value){
					if($data['debtor_disabled'] == 1){
						$int_arr[$integration_code]['sku_item_info_list'][$key]['uncheck'] = 1;
					}

					if($value['disabled'] == 0){
						if($value['artno'] && !$value['mcode']){
							$where = "where si.artno = ".ms($value['artno']);
						}

						if($value['mcode'] && !$value['artno']){
							$where = "where si.mcode = ".ms($value['mcode']);
						}

						if($value['artno'] && $value['mcode']){
							$where = "where si.artno = ".ms($value['artno'])." and si.mcode = ".ms($value['mcode']);
						}

						$q1 = $con->sql_query("select si.sku_item_code, si.mcode, si.link_code, si.description, si.artno, v.description as vendor 
							from sku_items si 
							left join sku s on s.id = si.sku_id 
							left join vendor v on v.id = s.vendor_id 
							$where");
						if($con->sql_numrows($q1) > 1){
							unset($int_arr[$integration_code]['sku_item_info_list'][$key]);
							$smarty->assign('duplicate', 1);
							while($sku = $con->sql_fetchassoc($q1)){

								$str = nl2br("System has detected duplicate SKU or Art Number.\r\n Please make a appropriate option.");
								$info_arr = array("integration_code" => $value['integration_code'], 
									"sku_item_code" => $sku['sku_item_code'], 
									"artno" => $sku['artno'], 
									"mcode" => $sku['mcode'], 
									"link_code" => $sku['link_code'], 
									"description" => $sku['description'], 
									"vendor" => $sku['vendor'], 
									"uom_id" => $value['uom_id'],
									"uom" => $value['uom'], 
									"suggest_po_ctn_by_branch" => $value['suggest_po_ctn_by_branch'], 
									"uncheck" => 1, 
									"duplicate" => 1, 
									"remark" => $str);
								
								array_push($int_arr[$integration_code]['sku_item_info_list'], $info_arr);
							}
						}
						$con->sql_freeresult($q1);
					}				

					if($value['disabled'] == 0){
						$total += 1; 
					}
					$int_arr[$integration_code]['data'] = $total;							
				}
			}
		}
		
		if($item_lists){
			$smarty->assign("item_lists", $item_lists);			
		}

		$form['file_name'] = $file['name'];
		$form['batch_code'] = $batch_code;

		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$smarty->assign('tmp_file', $tmp_file);
		$smarty->assign('sample', $sample_file);
		$smarty->assign("debtors", $int_arr);
		$smarty->display('sales_order.upload_csv.tpl');
	}	

	function add_sales_item($so_id, $sku_item_code, $mcode, $artno, $bid, $carton, $itgt_code, $uom_id){
		global $con, $smarty, $sessioninfo, $LANG, $config, $gst_list;

		$id = $so_id;
		$branch_id = $bid;
		$ctn = $carton;
		$integration_code = $itgt_code;
		if($_REQUEST['sid']) $sku_item_id_arr = $_REQUEST['sid'];
		else $sku_item_id_arr = $_REQUEST['sku_code_list'];
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		$order_date = $_REQUEST['order_date'];
		$use_promo_price = mi($_REQUEST['use_promo_price']);
		$form = $_REQUEST;
		
		if($config['enable_gst'])	construct_gst_list();
		
		if($mcode != ''){
			$q1 = $con->sql_query("select sku_item_code from sku_items where mcode = ".ms($mcode)."");
			$sku = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		}elseif($artno != ''){
			$q2 = $con->sql_query("select sku_item_code from sku_items where artno = ".ms($artno)."");
			$sku = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
		}else{
			die("Please ensure the item has Mcode and/or Art Number.");
		}

		$item_pcs = array();
		$sku_info = get_grn_barcode_info($grn_barcode,true);
		$sku_info['sku_item_id'] = $sku['sku_item_code'];
		
		if($sku_info['sku_item_id']){
			// is inactive item
			$q1 = $con->sql_query("select active from sku_items where sku_item_code = ".mi($sku_info['sku_item_id'])." limit 1");
			$tmp_si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if(!$tmp_si_info['active']){
				fail($LANG['PO_ITEM_IS_INACTIVE']);
			}
		
			$item_pcs[$sku_info['sku_item_id']] = $sku_info['qty_pcs'];
			$sku_item_id_arr[] = $sku_info['sku_item_id'];
		}
		
		if(!$sku_item_id_arr){
			die($LANG['NO_ITEM_FOUND']);
		}
		
		$bom_ref_num = time();
		if($config['sku_bom_additional_type']){
		
			foreach ($sku_item_id_arr as $bom_key => $tmp_sku_item_id) {
				$q2 = $con->sql_query($abc="select si.id, sku.is_bom, si.bom_type from sku_items si join sku on sku.id=si.sku_id where si.id = ".mi($tmp_sku_item_id));//print "$abc\n";
				$item = $con->sql_fetchassoc($q2);
				if ($item['is_bom'] && $item['bom_type'] == 'package') {
					$q3 = $con->sql_query($def="select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($item['id'])." order by bi.sku_item_id");//print "$def\n";
					while($bom_item = $con->sql_fetchassoc($q3)){
						if (!in_array($bom_item['sid'],$sku_item_id_arr)) {
							$sku_item_id_arr[] = $bom_item['sid'];
							$bom_qty[$bom_item['sid']] = $bom_item['qty'];
						}
					}
					unset($sku_item_id_arr[$bom_key]);
				}
			}
			
		}
		
		$con->sql_query("select debtor_mprice_type,special_exemption from debtor where integration_code = '$integration_code' and active = 1");
		$debtor = $con->sql_fetchassoc();
		$selling_type = $debtor['debtor_mprice_type'];
		$is_special_exemption = $debtor['special_exemption'];
		$con->sql_freeresult();

		if($selling_type){	// got choose mprice selling
			$extra_col = ", simp.price as mprice";
			$left_join = "left join sku_items_mprice simp on simp.branch_id=$branch_id and simp.sku_item_id=si.id and simp.type=".ms($selling_type);
		}
		
		$q1 = $con->sql_query("select si.id as sku_item_id, si.sku_id, si.sku_item_code, si.description as sku_description, ifnull(si.artno,si.mcode) as artno_mcode, 1 as uom_id, 1 as uom_fraction,si.packing_uom_id as master_uom_id,sic.qty as stock_balance,ifnull(sic.grn_cost,si.cost_price) as cost_price, ifnull(sip.price,si.selling_price) as selling_price,
							  si.doc_allow_decimal,uom.code as packing_uom_code $extra_col
							  from sku_items si
							  left join sku on sku_id = sku.id
							  left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
							  left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
							  left join uom on uom.id=si.packing_uom_id
							  $left_join
							  where si.sku_item_code in (".join(',',$sku_item_id_arr).")");
		if($con->sql_numrows($q1)<=0){
            print "<script>alert('".jsstring($LANG['DO_INVALID_ITEM'])."')</script>";
			exit;
		}
		$smarty->assign('form', $form);

		$i=0;
		while($item = $con->sql_fetchassoc($q1)){
		    $sid = $item['sku_item_id'];
		    $pcs = $item_pcs[$sid];
		    if($pcs)    $item['pcs'] = $pcs;
		    $item['branch_id'] = $branch_id;
		    $item['sales_order_id'] = $id;
		    $item['user_id'] = $sessioninfo['id'];
		    $item['integration_code'] = $integration_code;
		    $item['uom_id'] = $uom_id;
		    if($use_promo_price){
				$item['selling_price'] = mf(get_lowest_price($branch_id, $sid, $order_date));
			}elseif($selling_type){
				if($item['mprice'])	$item['selling_price'] = $item['mprice'];
			}
			
			if (isset($bom_qty[$sid])) {
				$item['bom_qty_ratio'] = $bom_qty[$sid];
				$item['bom_ref_num'] = $bom_ref_num;
			}
			else {
				$item['bom_qty_ratio'] = 0;
				$item['bom_ref_num'] = '';
			}
			
			// GST
			if($config['enable_gst']){
				if($form['is_under_gst']){
					// get sku is inclusive
					$is_sku_inclusive = get_sku_gst("inclusive_tax", $sid);
					// get sku original output gst
					$sku_original_output_gst = get_sku_gst("output_tax", $sid);
					
					$use_gst = array();
					if($is_special_exemption){
						// is special exemption
						$use_gst = get_special_exemption_gst();
					}else{
						// normal debtor
						if($sku_original_output_gst){
							$use_gst = $sku_original_output_gst;
						}
					}
					if($use_gst){
						$item['gst_id'] = $use_gst['id'];
						$item['gst_code'] = $use_gst['code'];
						$item['gst_rate'] = $use_gst['rate'];
					}else{
						$item['gst_id'] = $gst_list[0]['id'];
						$item['gst_code'] = $gst_list[0]['code'];
						$item['gst_rate'] = $gst_list[0]['rate'];
					}
					
				
					if($is_sku_inclusive == 'yes'){
						// is inclusive tax
						// find the price before tax
						$gst_tax_price = round($item['selling_price'] / ($sku_original_output_gst['rate']+100) * $sku_original_output_gst['rate'], 2);
						$item['selling_price'] -= $gst_tax_price;
					}
				}
			}
			
			//get parent stock balance
			if($config['show_parent_stock_balance']){
				$item['parent_stock_balance'] = 0;
				$q2= $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
									 from sku_items si
									 left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
									 left join uom on uom.id=si.packing_uom_id
									 where si.sku_id=".mi($item['sku_id']));
				
				while($parent_stock_balance = $con->sql_fetchassoc($q2)) {
					$item['parent_stock_balance'] += $parent_stock_balance['parent_stock_balance'];
				}
				$con->sql_freeresult($q2);
			}		
			
			$upd['branch_id'] = $item['branch_id'];
			$upd['sales_order_id'] = $item['sales_order_id'];
			$upd['user_id'] = $item['user_id'];
			$upd['sku_item_id'] = $item['sku_item_id'];
			$upd['cost_price'] = $item['cost_price'];
			$upd['selling_price'] = $item['selling_price'];
			$upd['uom_id'] = $item['uom_id'];
			$upd['ctn'] = $ctn;
			$upd['pcs'] = 0;
			$upd['stock_balance'] = $item['stock_balance'];			
			$upd['do_qty'] = 0;
			$upd['item_discount'] = '';
			$upd['item_discount_amount'] = 0;
			$upd['bom_ref_num'] = $item['bom_ref_num'];
			$upd['bom_qty_ratio'] = $item['bom_qty_ratio'];
			$upd['gst_id'] = $item['gst_id'];
			$upd['gst_code'] = $item['gst_code'];
			$upd['gst_rate'] = $item['gst_rate'];
			$upd['item_discount_amount2'] = 0;
			$upd['line_gross_amt'] = 0;
			$upd['line_gst_amt'] = 0;
			$upd['line_amt'] = 0;
			$upd['line_gross_amt2'] = 0;
			$upd['line_gst_amt2'] = 0;
			$upd['line_amt2'] = 0;
			$upd['parent_stock_balance'] = $item['parent_stock_balance'];

			$con->sql_query("insert into sales_order_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		}

		$next_id = $con->sql_nextid();
		$con->sql_freeresult($q1);
		return $next_id;
	}	

	// stock reorder sample
	var $headers = array(
		'1' => array("branchcode" => "BRANCH CODE",
					 "integration code" => "INTEGRATION CODE",
					 "arms_code" => "ARMS CODE",
					 "art_no" => "ART NO",
					 "mcode" => "MCODE",
					 "atp_code" => "ATP CODE",
					 "description" => "DESCRIPTION",
					 "uom" => "UOM",
					 "ctn" => "CTN"
				)
	);
	
	// stock reorder sample
	var $sample = array(
		'1' => array(
			'sample_1' => array("HQ", "INTC346", "280000050000", "77923421", "100030", "10030", "GREEN APPLE (AUST)", "WEIGHT", "3"),
			'sample_2' => array("HQ", "INTC346", "280000090000", "", "10034", "10034", "MANDARIN ORANGE (CHINA)", "WEIGHT", "6")
		)
	);

	// stock reorder sample
	function download_sample_so(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_so.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']])) . "\n";
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print join(",", $data) . "\n";
		}
	}
}

$Sales_Order = new Sales_Order('Sales Order');
?>