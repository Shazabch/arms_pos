<?php
/*
8/17/2012 3:45 PM Andy
- Fix purchase agreement sync replica problem.

9/3/2012 3:23 PM Andy
- Fix pagination problem.

9/13/2012 5:29 PM Andy
- Add new purchase agreement type, Seasonal.
- Add purchase agreement to support multiple add item.

9/24/2012 11:08 AM Andy
- Change "Normal" purchasee agreement to editable in approved status, instead of seasonal.

10/9/2012 5:09 PM Andy
- Fix foc item cannot be delete if delete until no item.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/31/2013 3:07 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

9/8/2016 4:23 PM Andy
- Enhanced to have remark for purchase agreement.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

9/26/2018 4:35 PM Andy
- Enhanced to have "Upload CSV" for Purchase Agreement.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(!$config['enable_po_agreement'])	js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('PO_SETUP_AGREEMENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_SETUP_AGREEMENT', BRANCH_CODE), "/index.php");
if(BRANCH_CODE != 'HQ')	js_redirect($LANG['HQ_ONLY'], "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

include("po.include.php");

class PURCHASE_AGREEMENT extends Module{
	var $allow_edit = 0;
	var $bid = 0;
	
	var $sample_csv = array(
		'header' => array('Type', 'Item Code','Rule No', 'Qty Type', "Qty1", "Qty2", "Discount", "Suggested Selling Price", "Purchase Price", "Allowed Branches"),
		'items' => array(
				array("P", "282787230000", "1", "Fixed", "3", "", "10%", "7", "5.5", "HQ,DEV"),
				array("P", "282732080000", "", "Multiply", "2", "", "", "18.2", "14", "DEV"),
				array("P", "281546200000", "2", "Range", "5", "10", "0.5", "19.9", "14.5", "DEV,HQ"),
				array("F", "281599490000", "1,2", "", "2", "", "0.5", "", "10", "DEV,HQ"),
			)
        );
	var $tbl_csv = 'tmp_pa_upload_csv';
	
	function __construct($title){
		global $con, $config, $smarty, $sessioninfo;
	
		$this->bid = mi($sessioninfo['branch_id']);
		if(isset($_REQUEST['branch_id']) && $_REQUEST['branch_id'] != $this->bid)	die("Invalid branch action.");	
		else{
		    $this->allow_edit = 1;
            $smarty->assign('allow_edit', $this->allow_edit);
		}
		parent::__construct($title);
	}
	
	function _default(){
		global $con, $config, $smarty, $sessioninfo;
		
		$this->display();
	}
	
	function load_list($sqlonly = false){
		global $con, $config, $smarty, $sessioninfo;
		
		//print_r($_REQUEST);
		
		$t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = 50;
		$start = $p*$size;	
		$search_str = trim($_REQUEST['search_str']);
		
		$filter = array();
		
		if (BRANCH_CODE!='HQ'){
			$filter[] = "pa.branch_id=".mi($sessioninfo['branch_id']);
		}
		
		switch($t){
			case 1:	// saved
		    	$filter[] = "pa.active=1 and pa.status=0 and pa.approved=0";
		    	break;
			case 2:	// show waiting for approval
				$filter[] = "pa.active=1 and pa.status=1 and pa.approved=0";
				break;
			case 3:	// rejected
				$filter[] ="pa.active=1 and pa.status=2 and pa.approved=0";
				break;
			case 4: // cancelled/terminated
			   	$filter[] ="pa.active=1 and pa.status=4 and pa.approved=0";
				break;
			case 5:	// approved
				$filter[] ="pa.active=1 and pa.status=1 and pa.approved=1";
				break;
		    default:	// search
			    $filter[] = '(pa.id='.ms($search_str)." or pa.title like ".ms('%'.replace_special_char($search_str).'%').')';
			    break;
		}
		
		$filter = "where ".join(' and ', $filter);
		
		// count total fist
		$con->sql_query("select count(*) from purchase_agreement pa $filter");
		$total_rows = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if($start >= $total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		
		$total_page = ceil($total_rows/$size);
		
		$q1 = $con->sql_query("select pa.* , branch.report_prefix, vendor.code as vcode, vendor.description as vdesc, if(bv.account_id = '' or bv.account_id is null, vendor.account_id, bv.account_id) as account_id, c.description as dept_name, bah.approvals, bah.approval_order_id
		from purchase_agreement pa
		left join branch on branch.id=pa.branch_id
		left join vendor on vendor.id=pa.vendor_id
		left join branch_vendor bv on bv.vendor_id = vendor.id and bv.branch_id = pa.branch_id
		left join category c on c.id=pa.dept_id
		left join branch_approval_history  bah on pa.approval_history_id = bah.id and pa.branch_id = bah.branch_id
		$filter
		order by pa.last_update desc $limit");
		$pa_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$pa_list[] = $r;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		
		$smarty->assign('total_rows', $total_rows);
		$smarty->assign('pa_list', $pa_list);
		$smarty->assign('total_page',$total_page);
		
		if(!$sqlonly){
			$ret['html'] = $smarty->fetch('po.po_agreement.setup.list.tpl');
			$ret['ok'] = 1;
	
			print json_encode($ret);
		}		
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
        $con->sql_query("delete from tmp_purchase_agreement_items where (purchase_agreement_id>1000000000 and purchase_agreement_id<".strtotime('-1 day').") and user_id=$sessioninfo[id]");
        
        $form = $_REQUEST;
        if(!$id){
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
		}
		
        if($_REQUEST['a']=='view'){
			$this->allow_edit = 0;
			$smarty->assign('allow_edit', 0);
		}	
        
        if(!is_new_id($id) && $branch_id){    // existing pa
            if($branch_id!=$sessioninfo['branch_id'] && $_REQUEST['a']!='view'){
				header("Location: $_SERVER[PHP_SELF]?a=view&branch_id=$branch_id&id=$id");
			}
            if($load_header){
                $form = load_purchase_agreement_header($branch_id, $id, true);
                if(!$form){  // promotion not found
                    display_redir("/".$this->redirect_php, "Purchase Agreement", sprintf($LANG['PURCHASE_AGREEMENT_NOT_FOUND'], $id));
				}
                if($_REQUEST['a']!='view')	$this->copy_to_tmp($branch_id, $id);
	
				$can_edit = true;
                if($_REQUEST['a']!='view'){
                	if(!$form['active']){
                		$can_edit = false;	// in-active, sure cant edit
                	}elseif($form['status'] && $form['approved']){	// alrdy approved
                		if($form['pa_type'] != 'normal'){	// only can edit if type = normal
                			$can_edit = false;
                		}
                	}else{
                		// not under reject & saved
                		if($form['status'] != 2 && $form['status'] !=0)	$can_edit = false;	// not reject, other all cant edit
                	}
                	
                	if(!$can_edit){
                		header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
						exit;
                	}	            
				}
			}else{
				load_pa_required_data();
			}
		}else{  // new
			if(!$id){
                $id=time();
				$form['id']=$id;
				$branch_id = $form['branch_id'] = $sessioninfo['branch_id'];
				$form['first_time'] = 1;
				$this->allow_edit = 1;
				$smarty->assign('allow_edit', $this->allow_edit);
				
				// Create from Upload CSV, Convert CSV Items into Temp Items
				if($this->is_create_from_upload_csv){
					$this->copy_csv_items_to_tmp($form['branch_id'], $form['id']);
				}
			}
			// load data such as sku type, price type, etc…
			load_pa_required_data();
		}
		
		//print_r($form);
		$items = $foc_items = array();
		$item_list = load_purhcase_agreement_items_list($branch_id, $id, ($_REQUEST['a']!='view'));
		//print_r($item_list);
		$smarty->assign('PAGE_TITLE', $this->title.' - '.(is_new_id($id)?'New ':'ID#'.$form['id']));
		$smarty->assign('form', $form);
		$smarty->assign('item_list', $item_list);
		
		$this->display('po.po_agreement.setup.open.tpl');
	}
	
	function refresh(){
		global $con;
		
	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
        $this->open($branch_id, $id);
	}
	
	function ajax_add_item(){
		global $con, $sessioninfo, $smarty, $LANG, $config;
		
		if(!$this->allow_edit)  die('You cannot edit this purchase agreement');
		
		$bid = $this->bid;
		$pa_id = mi($_REQUEST['pa_id']);
		$sid_list = $_REQUEST['sid_list'];
		
		if(!$sid_list || !is_array($sid_list))	die("Please search and select SKU first.");
		if(!$bid || !$pa_id)	die("Invalid Purchase Agreement.");
		
		// get max rule_num
		$con->sql_query("select max(rule_num) from tmp_purchase_agreement_items where branch_id=$bid and purchase_agreement_id=$pa_id");
		$rule_num = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
			
		$sql = "select si.id as sid, ifnull(sip.price, si.selling_price) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		where si.id in (".join(',', $sid_list).")";
		
		$q1 = $con->sql_query($sql);
		$pai_id_list = array();
		while($item = $con->sql_fetchassoc($q1)){
			$rule_num++;
			
			$upd = array();
			$upd['branch_id'] = $bid;
			$upd['purchase_agreement_id'] = $pa_id;
			$upd['rule_num'] = $rule_num;
			$upd['sku_item_id'] = $item['sid'];
			$upd['suggest_selling_price'] = $item['selling_price'];
			$upd['cost'] = $item['cost'];
			$upd['user_id'] = $sessioninfo['id'];
			
			$con->sql_query("insert into tmp_purchase_agreement_items ".mysql_insert_by_field($upd));
			$id = $con->sql_nextid();
			
			// store id list
			$pai_id_list[] = $id;
		}
		$con->sql_freeresult($q1);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = '';
		foreach($pai_id_list as $pai_id){
			$ret['html'] .= load_purchase_agreement_item($bid, $pai_id, true, true);
		}
		
		print json_encode($ret);
	}
	
	private function save_tmp_items($form){
    	global $con, $smarty, $sessioninfo;
    	
    	$branch_id = mi($form['branch_id']);
    	$pa_id = mi($form['id']);
    	   	
    	// purchase_agreement_items
    	
    	// truncate old items first
    	$con->sql_query("delete from tmp_purchase_agreement_items where branch_id=$branch_id and purchase_agreement_id=$pa_id and user_id=".mi($sessioninfo['id']));
    	
    	if($form['sku_item_id']['item']){
			foreach($form['sku_item_id']['item'] as $pai_id => $sid){
		        if(!$pai_id)   continue;
		        
                $upd = array();
				$upd['branch_id'] = $branch_id;
				$upd['id'] = $pai_id;
				$upd['purchase_agreement_id'] = $pa_id;
				$upd['rule_num'] = $form['rule_num']['item'][$pai_id];
				$upd['sku_item_id'] = $sid;		
				$upd['qty_type'] = $form['qty_type']['item'][$pai_id];
				$upd['qty1'] = $form['qty1']['item'][$pai_id];
				$upd['qty2'] = $form['qty2']['item'][$pai_id];
				$upd['discount'] = $form['discount']['item'][$pai_id];
				$upd['suggest_selling_price'] = $form['suggest_selling_price']['item'][$pai_id];
				$upd['purchase_price'] = $form['purchase_price']['item'][$pai_id];
				$upd['cost'] = $form['cost']['item'][$pai_id];
				$upd['allowed_branches'] = serialize($form['allowed_branches']['item'][$pai_id]);
				$upd['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("replace into tmp_purchase_agreement_items ".mysql_insert_by_field($upd));
			}
		}
		
		// purchase_agreement_foc_items
		
		// truncate old items first
    	$con->sql_query("delete from tmp_purchase_agreement_foc_items where branch_id=$branch_id and purchase_agreement_id=$pa_id and user_id=".mi($sessioninfo['id']));
    	if($form['sku_item_id']['foc_item']){
			foreach($form['sku_item_id']['foc_item'] as $pafi_id => $sid){
		        if(!$pai_id)   continue;
		        
                $upd = array();
				$upd['branch_id'] = $branch_id;
				$upd['id'] = $pafi_id;
				$upd['purchase_agreement_id'] = $pa_id;
				$upd['ref_rule_num'] = serialize($form['ref_rule_num']['foc_item'][$pafi_id]);
				$upd['sku_item_id'] = $sid;		
				$upd['qty'] = $form['qty']['foc_item'][$pafi_id];
				$upd['suggest_selling_price'] = $form['suggest_selling_price']['foc_item'][$pafi_id];
				$upd['purchase_price'] = $form['purchase_price']['foc_item'][$pafi_id];
				$upd['cost'] = $form['cost']['foc_item'][$pafi_id];
				$upd['allowed_branches'] = serialize($form['allowed_branches']['foc_item'][$pafi_id]);
				$upd['user_id'] = $sessioninfo['id'];
				
				$con->sql_query("replace into tmp_purchase_agreement_foc_items ".mysql_insert_by_field($upd));
			}
		}
	}
	
	function confirm(){
		$this->save(true);
	}
	
	function save($is_confirm = false){
		global $con, $smarty, $sessioninfo, $LANG;

        if(!$this->allow_edit)  die('You cannot edit this purchase agreement');
		
		$form = $_REQUEST;

		$branch_id = mi($form['branch_id']);
		$id = mi($form['id']);
		//print_r($form);exit;
		$this->save_tmp_items($form);
		
		//check status.
		$last_approval = false;
		if($is_confirm && !is_new_id($id)){
		    $con->sql_query("select active, status, approved from purchase_agreement where id=$id and branch_id=$branch_id");
		    if($r=$con->sql_fetchrow()){
		       if(($r['status']>0 && $r['status'] !=2) || $r['approved']){
		            // already confirm
		            display_redir("/".$this->redirect_php, "Purchase Agreement", sprintf($LANG['PROMO_ALREADY_CONFIRM_OR_APPROVED'], $promo_id));
				}
			}
			else{
			    // not found
			    display_redir("/".$this->redirect_php, "Purchase Agreement", sprintf($LANG['PURCHASE_AGREEMENT_NOT_FOUND'], $promo_id));
			}
		}
		
		if($form['date_from']=='' || strtotime($form['date_from'])<=0)	$err[]=$LANG['PURCHASE_AGREEMENT_INVALID_DATE_FROM'];
		if($form['date_to']=='' || strtotime($form['date_to'])<=0)	$err[]=$LANG['PURCHASE_AGREEMENT_INVALID_DATE_TO'];

		if (strtotime($form['date_from']) > strtotime($form['date_to'])){
			$err[]="Date From cannot greater than Date To";
		}
		if(!trim($form['title']))	$err[] = "Please key in title.";
		if(!$form['dept_id'])	$err[] = "Please select department.";
		if(!$form['vendor_id'])	$err[] = "Please select vendor.";

		// check if item list is empty
		$qc = $con->sql_query("select id from tmp_purchase_agreement_items
		where purchase_agreement_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
		if ($con->sql_numrows()<=0){
			$err[]="There is no items in the list.";
		}
		$con->sql_freeresult();
		
		if(!is_new_id($id))	$ori_form = load_purchase_agreement_header($branch_id, $id);
		
		// check approval flow
		if(!$ori_form['approved']){
			$form['status'] = 0;
			if(!$err && $is_confirm){
			   	$params = array();
		      	$params['type'] = 'PURCHASE_AGREEMENT';
		      	$params['branch_id'] = $branch_id;
		      	$params['user_id'] = $sessioninfo['id'];
		      	$params['dept_id'] = $form['dept_id'];
		      	$params['reftable'] = 'purchase_agreement';
		      	if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id'];
		      	$astat = check_and_create_approval2($params, $con);
	
				if(!$astat){
					$err[]= $LANG['PURCHASE_AGREEMENT_NO_APPROVAL_FLOW'];
				}
				else{
					$form['approval_history_id']=$astat[0];
		   			if($astat[1]=='|') $last_approval=true;
				}
	
				$form['status']=1;
			}
		}else{
			$form['status'] = 1;
			if($form['approval_history_id']){
				$bah = array();
				$bah['approval_history_id'] = $form['approval_history_id'];
				$bah['branch_id'] = $branch_id;
				$bah['user_id'] = $sessioninfo['id'];
				$bah['status'] = 1;
				$bah['log'] = 'Edit on Approved';
				
				$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($bah));
			}
			
		}	
		
		
		if($err){
		    $smarty->assign('err', $err);
			$this->open($branch_id, $id, false);
			exit;
		}
		
		// store data to save for mysql
		$upd1 = array();
		$upd1['title'] = $form['title'];
		$upd1['date_from'] = $form['date_from'];
		$upd1['date_to'] = $form['date_to'];
		$upd1['dept_id'] = $form['dept_id'];
		$upd1['vendor_id'] = $form['vendor_id'];
		$upd1['last_update'] = 'CURRENT_TIMESTAMP';
		$upd1['approval_history_id'] = mi($form['approval_history_id']);
		$upd1['status'] = mi($form['status']);
		$upd1['pa_type'] = $form['pa_type'];
		$upd1['remark'] = $form['remark'];
		
		// store old timestamp id
		$tmp_pa_id = $id;
		
		if(is_new_id($id)){
			$upd1['branch_id'] = $branch_id;
			$upd1['added'] = 'CURRENT_TIMESTAMP';
			$upd1['user_id'] = $sessioninfo['id'];
           
           	$id = $this->create_new_purchase_agreement($branch_id, $upd1);
            //$con->sql_query("insert into purchase_agreement ".mysql_insert_by_field($upd1));
			//$id = $con->sql_nextid();
		}else{
            $con->sql_query("update purchase_agreement set ".mysql_update_by_field($upd1)." where branch_id=$branch_id and id=$id");
		}
		
		// create items
		
		// purchase_agreement_items
		// get from tmp first
		$qpi = $con->sql_query("select * from tmp_purchase_agreement_items where branch_id=$branch_id and purchase_agreement_id=$tmp_pa_id and user_id=".mi($sessioninfo['id'])." order by id");
		$first_item_id = 0;
		while($r = $con->sql_fetchassoc($qpi)){ // loop tmp items
			$upd2 = $r;
			$upd2['purchase_agreement_id'] = $id;	// update to new pa id
			unset($upd2['id']);
			
			// insert into real item table
			$con->sql_query("replace into purchase_agreement_items ".mysql_insert_by_field($upd2));
			if(!$first_item_id) $first_item_id = $con->sql_nextid();
		}
		$con->sql_freeresult();
		
		//if($first_item_id){
			$con->sql_query("delete from purchase_agreement_items where branch_id=$branch_id and purchase_agreement_id=$id ".($first_item_id ? " and id<$first_item_id":""));
		//}
		// delete from temp
		$con->sql_query("delete from tmp_purchase_agreement_items where branch_id=$branch_id and purchase_agreement_id=$tmp_pa_id and user_id=".mi($sessioninfo['id']));
		
		// purchase_agreement_foc_items
		// get from tmp first
		$qpi = $con->sql_query("select * from tmp_purchase_agreement_foc_items where branch_id=$branch_id and purchase_agreement_id=$tmp_pa_id and user_id=".mi($sessioninfo['id'])." order by id");
		$first_item_id = 0;
		while($r = $con->sql_fetchassoc($qpi)){ // loop tmp items
			$upd2 = $r;
			$upd2['purchase_agreement_id'] = $id;	// update to new pa id
			unset($upd2['id']);
			
			// insert into real item table
			$con->sql_query("replace into purchase_agreement_foc_items ".mysql_insert_by_field($upd2));
			if(!$first_item_id) $first_item_id = $con->sql_nextid();
		}
		$con->sql_freeresult();
		
		//if($first_item_id){
			$con->sql_query("delete from purchase_agreement_foc_items where branch_id=$branch_id and purchase_agreement_id=$id ".($first_item_id ? " and id<$first_item_id":""));
		//}
		// delete from temp
		$con->sql_query("delete from tmp_purchase_agreement_foc_items where branch_id=$branch_id and purchase_agreement_id=$tmp_pa_id and user_id=".mi($sessioninfo['id']));
		
		if($is_confirm){
		    log_br($sessioninfo['id'], 'PURCHASE AGREEMENT', $id, "Purchase Agreement Confirmed (ID#$id)");
		    
			$to = get_pm_recipient_list2($id,$form['approval_history_id'],0,'confirmation',$branch_id,'purchase_agreement');
			send_pm2($to, "Purchase Agreement Confirmed (ID#$id)", "po.po_agreement.setup.php?a=view&id=$id&branch_id=$branch_id", array('module_name'=>'purchase_agreement'));
			
			$con->sql_query("update branch_approval_history set ref_id=$id where id=$form[approval_history_id] and branch_id=$branch_id");
			
			if ($last_approval){
				//send_pm($to, "Purchase Agreement Confirmed (ID#$id)", "po.po_agreement.setup.php?a=view&id=$id&branch_id=$branch_id");
				$con->sql_query("update purchase_agreement set active=1, status=1, approved=1 where branch_id=$branch_id and id=$id");
				$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ('$astat[0]', $branch_id, $sessioninfo[id], 1, 'Approved')");
				header("Location: ".$_SERVER['PHP_SELF']."?type=approved&id=$id");
			}
			else{
				//send_pm($to, "Purchase Agreement Approval (ID#$id)", "po.po_agreement.setup.php?a=view&id=$id&branch_id=$branch_id");
				header("Location: ".$_SERVER['PHP_SELF']."?type=confirm&id=$id");
			}
		}
		else{
			log_br($sessioninfo['id'], 'Purchase Agreement', $id, "Purchase Agreement Saved (ID#$id)");
			header("Location: ".$_SERVER['PHP_SELF']."?type=save&id=$id");
		}
	}
	
	private function copy_to_tmp($branch_id, $pa_id){
		global $con, $sessioninfo;

		// escape integer
        $branch_id = mi($branch_id);
        $pa_id = mi($pa_id);
        
        // purchase_agreement_items
		//delete ownself items in tmp table
		$tmp_tbl = 'tmp_purchase_agreement_items';
		$tbl = 'purchase_agreement_items';
		$con->sql_query("delete from $tmp_tbl where purchase_agreement_id=$pa_id and branch_id=$branch_id and user_id=$sessioninfo[id]");

		//update items
		$qpi = $con->sql_query("select * from $tbl where purchase_agreement_id=$pa_id and branch_id = $branch_id order by id");
		while($r = $con->sql_fetchassoc($qpi)){
			$r['user_id'] = $sessioninfo['id'];
			unset($r['id']);

			$con->sql_query("insert into $tmp_tbl ".mysql_insert_by_field($r));
		}
		$con->sql_freeresult($qpi);
		
		// purchase_agreement_foc_items
		//delete ownself items in tmp table
		$tmp_tbl = 'tmp_purchase_agreement_foc_items';
		$tbl = 'purchase_agreement_foc_items';
		$con->sql_query("delete from $tmp_tbl where purchase_agreement_id=$pa_id and branch_id=$branch_id and user_id=$sessioninfo[id]");

		//update items
		$qpi = $con->sql_query("select * from $tbl where purchase_agreement_id=$pa_id and branch_id = $branch_id order by id");
		while($r = $con->sql_fetchassoc($qpi)){
			$r['user_id'] = $sessioninfo['id'];
			unset($r['id']);

			$con->sql_query("insert into $tmp_tbl ".mysql_insert_by_field($r));
		}
		$con->sql_freeresult($qpi);
	}
	
	function ajax_add_foc_item(){
		global $con, $sessioninfo, $smarty, $LANG, $config;
		
		if(!$this->allow_edit)  die('You cannot edit this purchase agreement');
		
		$bid = $this->bid;
		$pa_id = mi($_REQUEST['pa_id']);
		$sid = mi($_REQUEST['sid']);
		$ref_rule_num = $_REQUEST['select_rule'];
		
		if(!$sid)	die("Please search and select SKU first.");
		if(!$bid || !$pa_id)	die("Invalid Purchase Agreement.");
		
		$sql = "select si.id as sid, ifnull(sip.price, si.selling_price) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost
		from sku_items si
		left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
		left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
		where si.id=$sid";
		
		$con->sql_query($sql);
		$item = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$item)	die("Invalid SKU.");
		
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['purchase_agreement_id'] = $pa_id;
		$upd['ref_rule_num'] = serialize($ref_rule_num);
		$upd['sku_item_id'] = $sid;
		$upd['suggest_selling_price'] = $item['selling_price'];
		$upd['cost'] = $item['cost'];
		$upd['user_id'] = $sessioninfo['id'];
		
		$con->sql_query("insert into tmp_purchase_agreement_foc_items ".mysql_insert_by_field($upd));
		$id = $con->sql_nextid();
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = load_purchase_agreement_foc_item($bid, $id, true, true);
		print json_encode($ret);
	}
	
	function delete(){
		global $con, $smarty, $sessioninfo, $LANG;

        if(!$this->allow_edit)  die('You cannot edit this purchase agreement');

		$form = $_REQUEST;

   		$branch_id = mi($form['branch_id']);
		$id = mi($form['id']);

        if ($sessioninfo['level']<9999)	$usrcheck = " and user_id=$sessioninfo[id]";

		// delete tmp items
        $con->sql_query("delete from tmp_purchase_agreement_items where purchase_agreement_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
        $con->sql_query("delete from tmp_purchase_agreement_foc_items where purchase_agreement_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
        
		if (!is_new_id($promo_id)){
	        $con->sql_query("update purchase_agreement
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), status=5, active=0, approved=0
	where id=$id and branch_id=$branch_id $usrcheck");
		}
		
		if ($con->sql_affectedrows()>0){
			log_br($sessioninfo['id'], 'PURCHASE AGREEMENT', $id, "Purchase Agreement Deleted (ID#$id)");
			header("Location: ".$_SERVER['PHP_SELF']."?msg=".urlencode(sprintf($LANG['PURCHASE_AGREEMENT_DELETED'], $id)));
		}
		else
			header("Location: ".$_SERVER['PHP_SELF']."?msg=".urlencode(sprintf($LANG['PURCHASE_AGREEMENT_NOT_DELETED'], $id)));
		exit;
	}
	
	function pa_reset(){
		global $con,$sessioninfo,$config;

		$branch_id = $this->bid;
		$pa_id = mi($_REQUEST['id']);
		
		/*$required_level = isset($config['doc_reset_level']) ? $config['doc_reset_level'] : 9999;
	
		if($sessioninfo['level']<$required_level){
	        js_redirect(sprintf('Forbidden', 'PO', BRANCH_CODE), "/po.php");
		}*/
	
		$form=load_purchase_agreement_header($branch_id, $pa_id, true);
	
		$aid = $form['approval_history_id'];		
		$approvals=$form['approvals'];
		$status = 0;
	
		$upd = array();
		$upd['approval_history_id'] = $aid;
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['status'] = $status;
		$upd['log'] = $_REQUEST['reason'];
	
		$con->sql_query("insert into branch_approval_history_items ".mysql_insert_by_field($upd)) or die(mysql_error());
		$con->sql_query("update branch_approval_history set status=$status where id = $aid and branch_id = $branch_id") or die(mysql_error());
	
		$upd = array();
		$upd['status'] = $status;
		$upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['approved'] = 0;
		$upd['active'] = 1;
		
		$con->sql_query("update purchase_agreement set ".mysql_update_by_field($upd)." where id=$pa_id and branch_id=$branch_id") or die(mysql_error());
	    log_br($sessioninfo['id'], 'PURCHASE AGREEMENT', $pa_id, "Purchase Agreement Reset, Branch ID:$branch_id, ID:$pa_id");
	    
		header("Location: ".$_SERVER['PHP_SELF']."?type=reset&id=$pa_id");
	}
	
	private function create_new_purchase_agreement($branch_id, $form = array(), $field = array()){
		global $con;
		
		$max_failed_attemp = 5;
		$failed_attemp = -1;
		if(!$branch_id) die('Invalid Branch ID');
	    
	    $form['branch_id'] = $branch_id;
	    
		do {
	        $failed_attemp++;
	    
	        // get new promotion ID, to avoid replica bugs
	        $con->sql_query("select max(id) from purchase_agreement where branch_id=$branch_id");
	    	$form['id'] = mi($con->sql_fetchfield(0))+1;
	    	$con->sql_freeresult();
	
	    	$sql = "insert into purchase_agreement " . mysql_insert_by_field($form, ($field?$field:false));
	    	if($failed_attemp<$max_failed_attemp){
	            $q_success = $con->sql_query($sql, false,false);
			}else{
	            $q_success = $con->sql_query($sql); // attemp insert more than 5 time, maybe is other error, stop unlimited loop
			}
	
	    } while (!$q_success);   // insert until success
	    return $form['id'];
	}
	
	function download_csv_sample(){
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_purchase_agreement.csv");
		
		$fp = tmpfile();
		$stream = stream_get_meta_data($fp);
		$path = $stream['uri'];
		fputcsv_eol($fp, $this->sample_csv['header']);
		
		foreach($this->sample_csv['items'] as $r) {
			fputcsv_eol($fp, $r);
		}
		print file_get_contents($path);
		fclose($fp);
	}
	
	function create_by_upload_csv(){
		global $con, $config, $appCore;
		
		$file_name = $_FILES['csv_file']['tmp_name'];
		$err = array();
		$used_rule_num = array();
		$valid_branch_code = array();
		
		//$this->tbl_csv = $this->tbl_csv."_".time();
		$con->sql_query("drop table if exists ".$this->tbl_csv);
		$con->sql_query("create table if not exists ".$this->tbl_csv."(
			id int primary key auto_increment,
			type char(5),
			item_code char(20),
			rule_num char(50),
			qty_type char(10),
			qty1 double,
			qty2 double,
			discount char(50),
			suggest_selling_price double,
			purchase_price double,
			str_allowed_branches text,
			error_msg char(200) not null,
			sku_item_id int not null default 0,
			allowed_branches text,
			index type (type),
			index rule_num (rule_num),
			index error_msg (error_msg)
		)");
		$fp = fopen($file_name, "r");
		fgetcsv($fp); //skip header
		$line_num = 1;
		$have_foc = false;
		
		while ($r = fgetcsv($fp)){
			$line_num++;
			//print_r($r);
			$upd = array();
			$upd['type'] = trim($r[0]);
			$upd['item_code'] = trim($r[1]);
			$upd['rule_num'] = trim($r[2]);
			$upd['qty_type'] = strtolower(trim($r[3]));
			$upd['qty1'] = mf($r[4]);
			$upd['qty2'] = mf($r[5]);
			$upd['discount'] = trim($r[6]);
			$upd['suggest_selling_price'] = round(mf($r[7]), 2);
			$upd['purchase_price'] = round(mf($r[8]), $config['global_cost_decimal_points']);
			$upd['str_allowed_branches'] = strtoupper(trim($r[9]));
			
			// Type
			if($upd['type'] != 'P' && $upd['type'] != 'F'){
				$upd['error_msg'] = "Invalid 'Type', Accept 'P' or 'F' only.";
			}
			
			// Item Code
			if(!$upd['error_msg']){
				if(!$upd['item_code']){
					$upd['error_msg'] = "Item Code is Empty";
				}else{
					$con->sql_query("select si.id
						from sku_items si
						where si.sku_item_code=".ms($upd['item_code'])." or si.mcode=".ms($upd['item_code'])." or si.artno=".ms($upd['item_code'])." or si.link_code=".ms($upd['item_code'])." order by id limit 1");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$sid = mi($tmp['id']);
					if($sid>0){
						$upd['sku_item_id'] = $sid;
					}else{
						$upd['error_msg'] = "Item Code '".$upd['item_code']."' Not Found.";
					}
				}
			}
			
			// Rule Num
			if(!$upd['error_msg']&& $upd['rule_num']>0){
				if($upd['type'] == 'P' ){
					$upd['rule_num'] = mi($upd['rule_num']);
					// This Rule already used
					if(isset($used_rule_num[$upd['rule_num']])){
						$upd['error_msg'] = "Rule Num '".$upd['rule_num']."' already used by line ".$used_rule_num[$upd['rule_num']];
					}
				}
			} 
			if(!$upd['error_msg'] && $upd['type'] == 'F'){
				if(!$upd['rule_num'])	$upd['error_msg'] = "FOC Item must have Rule No.";
			}
			
			
			// Qty Type
			if(!$upd['error_msg'] && $upd['type'] == 'P'){
				if($upd['qty_type'] != 'fixed' && $upd['qty_type'] != 'multiply' && $upd['qty_type'] != 'range'){
					$upd['error_msg'] = "Qty Type Error: Value must be either (fixed / multiply / range)";
				}
			}
			
			// Qty1
			if(!$upd['error_msg'] && $upd['qty1'] <= 0){
				$upd['error_msg'] = "Qty1 must be numeric number more than zero.";
			}
			
			// Qty2
			if(!$upd['error_msg']){
				if($upd['type'] == 'P' && $upd['qty_type'] == 'range'){
					if($upd['qty2'] <= 0){
						$upd['error_msg'] = "Qty2 must be numeric number more than zero.";
					}
				}else{
					$upd['qty2'] = 0;
				}
			}
			
			// Discount
			if(!$upd['error_msg'] && $upd['discount'] && $upd['type']=='P'){
				$discount_pattern = validate_discount_format($upd['discount']);
				if($discount_pattern != $upd['discount']){
					$upd['error_msg'] = "Invalid Discount.";
				}
			}
			
			// Allowed Branch
			if(!$upd['error_msg']){
				if(!$upd['str_allowed_branches']){
					$upd['error_msg'] = "Allowed Branch is Empty.";
				}else{
					$bcode_list = explode(",", $upd['str_allowed_branches']);
					$allowed_branches = array();
					foreach($bcode_list as $bcode){
						if(!isset($valid_branch_code[$bcode])){
							// Check Branch code exists in Database or not
							$valid_branch_code[$bcode] = $appCore->branchManager->getBranchInfo(0, $bcode);
						}
						
						if(!$valid_branch_code[$bcode]){	// Invalid Branch Code
							$upd['error_msg'] = "Branch Code '$bcode' is invalid.";
							break;
						}else{
							$allowed_branches[$valid_branch_code[$bcode]['id']] = 1;
						}
					}
					if($allowed_branches && !$upd['error_msg'])	$upd['allowed_branches'] = serialize($allowed_branches);
				}
			}
			
			// Mark which rule already used
			if($upd['type'] == 'P'){
				if($upd['rule_num']>0){
					if(!isset($used_rule_num[$upd['rule_num']])){
						$used_rule_num[$upd['rule_num']] = $line_num;
					}
				}
			}
			
			// Mark have FOC
			if(!$upd['error_msg'] && $upd['type'] == 'F')	$have_foc = true;
			
			// Add into table
			$con->sql_query("insert into ".$this->tbl_csv." ".mysql_insert_by_field($upd));
		}
		fclose($fp);
		
		// validate FOC Rule
		if($have_foc){
			$q1 = $con->sql_query("select tbl.id, tbl.rule_num
				from ".$this->tbl_csv." tbl
				where error_msg='' and type='F'");
			while($r = $con->sql_fetchassoc($q1)){
				$upd = array();
				$rule_num_list = explode(",", $r['rule_num']);
				$invalid_rule_num_list = array();
				foreach($rule_num_list as $rule_num){
					if(!$used_rule_num[$rule_num]){
						$invalid_rule_num_list[] = $rule_num;
					}
				}
				
				if($invalid_rule_num_list){
					$upd['error_msg'] = "Rule Num '".join(',', $invalid_rule_num_list)."' cannot be found in purchase item.";
					$con->sql_query("update ".$this->tbl_csv." set ".mysql_update_by_field($upd)." where id=".mi($r['id']));
				}
			}
			$con->sql_freeresult($q1);
		}
		
		$this->is_create_from_upload_csv = true;
		$this->open();
	}
	
	private function copy_csv_items_to_tmp($bid, $pa_id){
		global $con, $smarty, $sessioninfo;
		
		$bid = mi($bid);
		$pa_id = mi($pa_id);
		
		if(!$bid || !$pa_id)	return;
		$err = array();
		
		// Get Used Rule Num
		$used_rule_data = array();
		$con->sql_query("select distinct rule_num 
			from ".$this->tbl_csv."
			where error_msg='' and type='P' and rule_num>0
			order by id");
		while($r = $con->sql_fetchassoc()){
			$rule_num = mi($r['rule_num']);
			if($rule_num < $used_rule_data['min'])	$used_rule_data['min'] = $rule_num;
			if($rule_num > $used_rule_data['max'])	$used_rule_data['max'] = $rule_num;
			$used_rule_data['list'][] = $rule_num;
		}
		$con->sql_freeresult();
		
		// Get All Purchase Items		
		$q1 = $con->sql_query("select tbl.*, ifnull(sip.price, si.selling_price) as selling_price, ifnull(sic.grn_cost, si.cost_price) as cost, if(error_msg<>'',1, if(type='P',2,3)) as order_rank
			from ".$this->tbl_csv." tbl
			left join sku_items si on si.id=tbl.sku_item_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
			order by order_rank, id");
		$rule_num = 0;
		while($r = $con->sql_fetchassoc($q1)){
			if($r['error_msg']){
				$err[] = "line ".($r['id']+1).": ".$r['error_msg'];
			}else{
				$upd = array();
				$upd['branch_id'] = $bid;
				$upd['purchase_agreement_id'] = $pa_id;
				$upd['sku_item_id'] = $r['sku_item_id'];
				$upd['suggest_selling_price'] = $r['suggest_selling_price'] ? $r['suggest_selling_price'] : $r['selling_price'];
				$upd['purchase_price'] = $r['purchase_price'];
				$upd['cost'] = $r['cost'];
				$upd['allowed_branches'] = $r['allowed_branches'];
				$upd['user_id'] = $sessioninfo['id'];
				
				if($r['type'] == 'P'){
					// Purchase
					$tbl = 'tmp_purchase_agreement_items';
					
					$upd['qty_type'] = $r['qty_type'];
					$upd['discount'] = $r['discount'];
					$upd['qty1'] = $r['qty1'];
					$upd['qty2'] = $r['qty2'];
					
					if($r['rule_num']){
						$upd['rule_num'] = $r['rule_num'];
					}else{
						$rule_num++;
						if($used_rule_data){
							while($rule_num >= $used_rule_data['min'] && $rule_num <= $used_rule_data['max'] && in_array($rule_num, $used_rule_data['list'])){
								$rule_num++;
							}
						}
						$upd['rule_num'] = $rule_num;
					}
					
					
				}else{
					// FOC
					$tbl = 'tmp_purchase_agreement_foc_items';
					
					if($r['rule_num']){
						$ref_rule_list = explode(",", $r['rule_num']);
						foreach($ref_rule_list as $rule_num){
							$upd['ref_rule_num'][$rule_num] = $rule_num;
						}
					}
					$upd['qty'] = $r['qty1'];
					
					if($upd['ref_rule_num'])	$upd['ref_rule_num'] = serialize($upd['ref_rule_num']);
				}
				
				$con->sql_query("insert into $tbl ".mysql_insert_by_field($upd));
			}
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign('err', $err);
		$smarty->assign('need_refresh', 1);
	}
}

$PURCHASE_AGREEMENT = new PURCHASE_AGREEMENT('Purchase Agreement');
?>
