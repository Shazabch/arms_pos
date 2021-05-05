<?php
/*
9/3/2010 3:20:11 PM Andy
- add privilege checking "FM_WRITE_OFF".

7/5/2011 1:26:55 PM Andy
- Change split() to use explode()

7/27/2011 4:24:32 PM Justin
- Added to pick up sku item's doc decimal point.
- Added cost to round base on config set.

7/3/2013 11:32 AM Fithri
- pm notification standardization

1/8/2020 3:33 PM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FM_WRITE_OFF')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FM_WRITE_OFF', BRANCH_CODE), "/index.php");

include('adjustment.fresh_market_write_off.include.php');

class FRESH_MARKET_WRITE_OFF extends Module{

    function __construct($title){
		global $con, $smarty;

		if(!$_REQUEST['skip_init_load'])    init_selection();

		parent::__construct($title);
	}
	
	function _default(){
		$this->display();
	}
	
	function ajax_list_sel(){
		global $con, $smarty, $sessioninfo, $config;

        $t = mi($_REQUEST['t']);
		$p = mi($_REQUEST['p']);
		$size = $config['document_page_size']>0 ? $config['document_page_size'] : 25;
		$start = $p*$size;

		$filter = array();
		if (!$sessioninfo['departments'])	$depts = "(0)";
		else	$depts = "(" . join(",", array_keys($sessioninfo['departments'])) . ")";
		
		if($sessioninfo['level']<9999){ // not system admin
            if ($sessioninfo['level']>=800){
				$filter[] = "adj.dept_id in $depts";
			}elseif($sessioninfo['level']>=400){
				$filter[] = "adj.branch_id=$sessioninfo[branch_id] and adj.dept_id in $depts";
			}
			else{
				$filter[] = "adj.user_id=$sessioninfo[id]";
			}
		}
        if(BRANCH_CODE != 'HQ'){
	    	$filter[] = "adj.branch_id=$sessioninfo[branch_id]";
		}

		switch ($t){
		    case 1: // search
		        $str = trim($_REQUEST['search_str']);
		        $filter[] = "adj.id=".ms(preg_replace("/[^0-9]/","", $str));
		        break;
			case 2: // show saved Adj
	        	$filter[] = "adj.status=0 and adj.approved=0 and adj.active=1";
	        	break;
			case 3: // show waiting for approval (and Keep In View)
			    $filter[] = "(adj.status=1 or adj.status=3) and adj.approved=0 and adj.active=1";
			    break;
            case 4: // show rejected
			    $filter[] = "adj.status=2 and adj.approved=0 and adj.active=1";
			    break;
			case 5: // show inactive
			    $filter[] = "(adj.status=4 or adj.status=5) and adj.active=1 and adj.approved=0";
			    break;
			case 6: // show approved
			    $filter[] = "adj.approved=1 and adj.active=1 and adj.status=1";
			    break;
		}
		
		$filter[] = "adj.adjustment_type=".ms(ADJ_FRESH_MARKET_WRITEOFF_TYPE);
		$filter = 'where '.join(' and ', $filter);
		$con->sql_query("select count(*) from adjustment adj $filter");
		$total_rows = $con->sql_fetchfield(0);

		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by adj.last_update desc";
		$total_page = ceil($total_rows/$size);
		
		$q2 = $con->sql_query("select adj.*, b1.report_prefix as prefix, dept.description as department, user.u as u, b1.code as branch, bah.approvals,bah.approval_order_id as aorder_id
from adjustment adj
left join branch b1 on b1.id=adj.branch_id
left join branch_approval_history bah on bah.id = adj.approval_history_id and bah.branch_id = adj.branch_id
left join category dept on dept_id = dept.id
left join user on user_id = user.id
$filter $order $limit");
		while($r = $con->sql_fetchrow($q2)){
			$adj_list[] = $r;
		}
		$con->sql_freeresult($q2);
		$smarty->assign("adj_list", $adj_list);
		$smarty->assign('total_page',$total_page);
		$this->display('adjustment.fresh_market_write_off.list.tpl');
	}
	
	function view(){
	    global $smarty;

	    $id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$smarty->assign('readonly', 1);
		$this->open($id, $branch_id, true);
	}
	
	function open($id = 0, $branch_id = 0, $load_header = true){
        global $con, $sessioninfo, $smarty, $LANG, $config;

		
		// delete old tmp items
        $con->sql_query("delete from tmp_adjustment_items where branch_id=$sessioninfo[branch_id] and (adjustment_id>1000000000 and adjustment_id<".strtotime('-1 day').") and user_id=$sessioninfo[id]");
        $form = $_REQUEST;
        if(!$id){
			$id = mi($_REQUEST['id']);
			$branch_id = mi($_REQUEST['branch_id']);
		}
		
// yinsee: why HQ only ??
//		if(BRANCH_CODE != 'HQ' && $branch_id!=$sessioninfo['branch_id']){   // not allow to access other branch if not HQ
//            header("Location: $_SERVER[PHP_SELF]");
//			exit;
//		}

		if($branch_id!=$sessioninfo['branch_id']&&$_REQUEST['a']!='view'&&$id){  // not in same branch, can only view
			header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
			exit;
		}

        if(!is_new_id($id)&&$branch_id){    // exists
            if($load_header){
                $form = load_adj_header($branch_id, $id, true);
                if($_REQUEST['a']!='view')	$this->copy_to_tmp($branch_id, $id);

                if(($form['approved']||!$form['active'])&& $_REQUEST['a']!='view'){ // approved or inactive sheet can only view
		            header("Location: $_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id");
					exit;
				}
			}
		}else{  // new 
			if(!$id){
                $id=time();
				$form['id']=$id;
			}
		}

		$items = load_adj_items($branch_id, $id, ($_REQUEST['a']!='view'));
		$smarty->assign('form', $form);
		$smarty->assign('items', $items);
		$smarty->display('adjustment.fresh_market_write_off.open.tpl');
	}
	
	private function copy_to_tmp($branch_id, $id){
		global $con, $sessioninfo, $appCore;
		//delete ownself items in tmp table
		$con->sql_query("delete from tmp_adjustment_items where adjustment_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");

		//copy items to tmp table
		$q1 = $con->sql_query("select *
		from adjustment_items where adjustment_id=$id and branch_id=$branch_id order by id");
		while($r1 = $con->sql_fetchassoc($q1)){
			$r1['id'] = $appCore->generateNewID("tmp_adjustment_items", "branch_id=".mi($branch_id));
			$r1['user_id'] = $sessioninfo['id'];
			$con->sql_query("insert into tmp_adjustment_items " . mysql_insert_by_field($r1, array('id', 'adjustment_id','branch_id','user_id','sku_item_id','cost','qty','selling_price','stock_balance')));
		}
		$con->sql_freeresult($q1);
	}
	
	private function save_tmp_items($branch_id, $id){
		global $con, $sessioninfo, $config;

		$form = $_REQUEST;

		if($form['uom_id']){
            foreach($form['uom_id'] as $item_id=>$uom_id){
				$upd = array();
				$upd['qty'] = abs($form['qty'][$item_id])*-1;
				$upd['cost'] = round($form['cost'][$item_id], $config['global_cost_decimal_points']);
				$upd['selling_price'] = mf($form['selling_price'][$item_id]);
				$upd['stock_balance'] = mf($form['stock_balance'][$item_id]);

				$con->sql_query("update tmp_adjustment_items set ".mysql_update_by_field($upd)." where id=".mi($item_id)." and branch_id=$branch_id and user_id=$sessioninfo[id]");
			}
		}
	}
	
	function ajax_add_item_row(){
        global $con, $smarty, $sessioninfo, $LANG, $config, $appCore;

		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$sku_item_id_arr = $_REQUEST['sku_code_list'];
		$date = $_REQUEST['date'];

		if(!$sku_item_id_arr){
			die($LANG['NO_ITEM_FOUND']);
		}

		$q1 = $con->sql_query("select si.id as sku_item_id, si.sku_item_code, si.description as sku_description, ifnull(si.artno,si.mcode) as artno_mcode, si.packing_uom_id as uom_id, uom.fraction as uom_fraction, uom.code as uom_code, sic.qty as stock_balance,ifnull(sic.grn_cost,si.cost_price) as cost, ifnull(sip.price,si.selling_price) as selling_price, si.doc_allow_decimal
from sku_items si
left join sku on sku_id = sku.id
left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
left join sku_items_price sip on sip.branch_id=$branch_id and sip.sku_item_id=si.id
left join uom on uom.id=si.packing_uom_id
where si.id in (".join(',',$sku_item_id_arr).")");

		if($con->sql_numrows($q1)<=0){
            die($LANG['NO_ITEM_FOUND']);
		}
		$this->save_tmp_items($branch_id, $id);

		while($item = $con->sql_fetchrow($q1)){
		    $sid = $item['sku_item_id'];
			$item['id'] = $appCore->generateNewID("tmp_adjustment_items", "branch_id=".mi($branch_id));
		    $item['branch_id'] = $branch_id;
		    $item['adjustment_id'] = $id;
		    $item['user_id'] = $sessioninfo['id'];
		    
			$con->sql_query("insert into tmp_adjustment_items ".mysql_insert_by_field($item, array('id', 'branch_id','adjustment_id','user_id','sku_item_id','cost','selling_price','stock_balance')));
			$smarty->assign('item', $item);
			$smarty->display('adjustment.fresh_market_write_off.open.sheet.item_row.tpl');
		}
	}

   	function ajax_delete_item(){
        global $con, $smarty, $sessioninfo, $LANG, $config;

		$adj_id = mi($_REQUEST['adj_id']);
		$branch_id = mi($_REQUEST['branch_id']);
		$item_id = mi($_REQUEST['id']);
		$con->sql_query("delete from tmp_adjustment_items where branch_id=$branch_id and adjustment_id=$adj_id and id=$item_id and user_id=$sessioninfo[id]");
		print "OK";
	}

    function confirm(){
		$this->save(true);
	}
	
    function save($is_confirm = false){
		global $con, $smarty, $sessioninfo, $LANG, $config, $appCore;
		$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);

		$form=$_REQUEST;
		$this->save_tmp_items($branch_id, $id);
		$form['adjustment_type'] = ADJ_FRESH_MARKET_WRITEOFF_TYPE;
		// validation
		$errm = array();
		if(!$form['uom_id']) $errm['top'][] = $LANG['ADJUSTMENT_NO_ITEM'];

		$arr=explode("-",$form['adjustment_date']);
		$yy=$arr[0];
		$mm=$arr[1];
		$dd=$arr[2];
		if(!checkdate($mm,$dd,$yy)){
		   	$errm['top'][] = $LANG['ADJUSTMENT_INVALID_DATE'];
		}

		if(!$errm && $is_confirm){
            $params = array();
		    $params['reftable'] = 'ADJUSTMENT';
		    $params['type'] = 'ADJUSTMENT';
		    $params['user_id'] = $sessioninfo['id'];
		    $params['branch_id'] = $branch_id;

			if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
			$astat = check_and_create_approval2($params, $con);

	  	  	if(!$astat) $errm['top'][] = $LANG['ADJUSTMENT_NO_APPROVAL_FLOW'];
	  		else{
	  			 $form['approval_history_id'] = $astat[0];
	     		 if ($astat[1] == '|') $last_approval = true;
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

		if (is_new_id($id)){
			$form['id'] = $appCore->generateNewID("adjustment", "branch_id = ".mi($branch_id));
			$form['added'] = 'CURRENT_TIMESTAMP';
            $form['user_id'] = $sessioninfo['id'];

			$con->sql_query("insert into adjustment ".mysql_insert_by_field($form, array('id', 'branch_id', 'adjustment_date', 'status', 'approved', 'user_id', 'remark','added', 'last_update','approval_history_id','dept_id','adjustment_type')));
		}
		else{
		    $con->sql_query("update adjustment set ".mysql_update_by_field($form, array('adjustment_date', 'status','approved', 'remark','last_update','approval_history_id','dept_id','adjustment_type'))." where branch_id=$branch_id and id=$id");
		}

        //copy tmp table to real items table
		$q1=$con->sql_query("select adji.*
		from tmp_adjustment_items adji
		where adji.adjustment_id=$id and adji.branch_id=$branch_id and adji.user_id=$sessioninfo[id]
		order by adji.id");

		$first_id = 0;
		while($r=$con->sql_fetchrow($q1)){
			$upd['id'] = $appCore->generateNewID("adjustment_items", "branch_id = ".mi($r['branch_id']));
			$upd['adjustment_id']=$form['id'];
			$upd['branch_id']=$r['branch_id'];
			$upd['sku_item_id']=$r['sku_item_id'];
			$upd['cost']=$r['cost'];
			$upd['selling_price']=$r['selling_price'];
			$upd['stock_balance'] = $r['stock_balance'];
			$upd['qty'] = $r['qty'];
			
			$con->sql_query("insert into adjustment_items ".mysql_insert_by_field($upd));
			if ($first_id==0) $first_id = $upd['id'];
		}

		if ($first_id>0) {
			if(!is_new_id($id)){
				$con->sql_query("delete from adjustment_items where branch_id=$branch_id and adjustment_id=$id and id<$first_id");
			}
			$con->sql_query("delete from tmp_adjustment_items where branch_id=$branch_id and adjustment_id=$id and user_id=$sessioninfo[id]");
		}
		else{
			die("System error: Insert items failed. Please contact ARMS technical support.");
		}

		$t = '';
		$formatted=sprintf("%05d",$form['id']);
	    //select report prefix from branch
		$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
		$b = $con->sql_fetchrow();

		if ($is_confirm){
            log_br($sessioninfo['id'], 'ADJUSTMENT', $form['id'], "Confirmed Adj.No:".' ('.$b['report_prefix'].$formatted.') ');
		    if ($last_approval){
                adj_approval($branch_id, $form['id']);
                $t = 'approve';
			}
			else{
			    $con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
                $t = 'confirme';
				$to = get_pm_recipient_list($form['id'],$form['approval_history_id'],$status,'confirmation',$branch_id,'adjustment');
				send_pm($to, "Adjustment Approval (ID#$form[id])", "adjustment.php?a=view&id=$form[id]&branch_id=$branch_id");
			}
		}
		else{
	        log_br($sessioninfo['id'], 'ADJUSTMENT', $form['id'], "Saved Adj.No:".' ('.$b['report_prefix'].$formatted.') ');
	        $t = 'saved';
		}
		header("Location: $_SERVER[PHP_SELF]?t=$form[a]&save_id=$form[id]");
		exit;
	}
	
	function delete(){
        global $con, $sessioninfo;
		$form = $_REQUEST;
        $id = $form['id'];
        $branch_id = $form['branch_id'];

	    $type = 'delete';
	    $status = 5;
		$reason = ms($form['reason']);
		$cancelled = 'CURRENT_TIMESTAMP';

	    $con->sql_query("update adjustment set cancelled_by=$sessioninfo[id], cancelled=$cancelled, reason=$reason, status=$status where id=$id and branch_id=$branch_id");

	    $con->sql_query("delete from tmp_adjustment_items where adjustment_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");
	    log_br($sessioninfo['id'], 'ADJUSTMENT', $form['id'], "Adj.Delete: (ID#".$form['id'].")");
	    header("Location: $_SERVER[PHP_SELF]?t=$type&save_id=$id");
	}
	
	function do_reset(){
		global $con, $smarty;

		$form = $_REQUEST;
		adj_reset($form['branch_id'], $form['id']);
	}
}

$FRESH_MARKET_WRITE_OFF = new FRESH_MARKET_WRITE_OFF('Fresh Market SKU Write Off');

?>
