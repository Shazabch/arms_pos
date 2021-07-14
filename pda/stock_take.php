<?php
/*
2/1/2011 10:58:48 AM Alex
- fix date checking bugs

6/1/2011 4:26:17 PM Andy
- Fix selling price bugs. (should take latest price, not master price)

10/16/2011 10:32:53 AM Justin
- Added to pick up doc_allow_decimal from sku items table.

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

4/5/2012 10:57:12 AM Justin
- Enhanced to search sku item info by sku item id instead of code while save scanning.
- Enhanced to return user to scan menu as if found sku item id is empty and show error message.
- Modified the validate_code to return tpl instead of using json.

6/14/2012 4:31:34 PM Justin
- Added new function to auto add item when got check "Add item when match one result" from module.

8/29/2012 1:36 PM Andy
- Add privilege checking for DO, GRR, GRN, Adj, Stock Take and Voucher.
- Fix stock take module title wrongly show as DO.

9/6/2012 5:47 PM Justin
- Added new features that allow user to search and delete/edit.

12/7/2012 10:58:00 AM Fithri
- "block fresh market stock take to accept child sku

2/24/2014 4:24 PM Andy
- Fix the stock take module assign wrong session bug.
- Fix the variable bug. (sometime "loc" sometime "location").

9/9/2020 2:48 PM William
- Bug Fixed to add insert id manually for stock_take_pre table that use auto increment.

9/21/2020 9:18 AM William
- Enhanced to block create and save when config "monthly_closing_block_document_action" is active and document date has closed.
*/

include("common.php");
include("class.scan_product.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/pda/index.php");
if (!privilege('STOCK_TAKE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE', BRANCH_CODE), "/pda");
$maintenance->check(1);

class ST_module extends Scan_Product{

	function init_module(){
	    global $con, $smarty;
		// alter any default value, such as $this->scan_templates and $this->result_templates
		//$this->scan_templates = 'abc.tpl';
		//$this->result_templates = 'abc.tpl';

		$smarty->assign('module_name','Stock Take');
		$smarty->assign('PAGE_TITLE','Stock Take');
		$smarty->assign('top_include','do.top_include.tpl');
		$smarty->assign('btm_include','do.btm_include.tpl');
	}
	
	function default_(){
		global $con, $smarty;
		
		/*$id = mi($_REQUEST['id']);
		$branch_id = mi($_REQUEST['branch_id']);
		
		if($id&&$branch_id){
			$this->reset_session_do($id,$branch_id);
		}else{
            $id = mi($_SESSION['do']['id']);
            $branch_id = mi($_SESSION['do']['branch_id']);
		}
		
		if($id>0){
			print "111";
			$this->show_scan_product();
		}else{
			print "222";
			$this->show_setting();
		}*/
	}
	
	/*function open(){
		global $con,$smarty;

		unset($_SESSION['st']);
		
		$q1 = $con->sql_query("select * from branch order by sequence, code");
		$branches = $con->sql_fetchrowset($q1);
		$con->sql_freeresult($q1);
		
		$smarty->assign("branches", $branches);
		$smarty->display('stock_take.search.tpl');
	}*/
	
	function view_items(){
		global $con,$smarty;
		$form = $_REQUEST;
		
		if($_SESSION['st']){
			$form['branch_id'] = $_SESSION['st']['branch_id'];
			$form['date'] = $_SESSION['st']['date'];
			$form['location'] = $_SESSION['st']['location'];
			$form['shelf'] = $_SESSION['st']['shelf'];
		}
		
		$q1 = $con->sql_query("select *, stp.id as item_id, si.sku_item_code, si.mcode, si.artno, si.description
							   from stock_take_pre stp
							   left join sku_items si on si.id = stp.sku_item_id
							   where stp.branch_id = ".mi($form['branch_id'])." and stp.date = ".ms($form['date'])." and stp.location = ".ms($form['location'])." and stp.shelf = ".ms($form['shelf'])."
							   order by stp.id");
		
		$item_list = array();
		while($r = $con->sql_fetchassoc()){
			$item_list[] = $r;
		}
		$con->sql_freeresult();
		//if($con->sql_numrows($q1) > 0){
			/*$_SESSION['st']['branch_id'] = $form['branch_id'];
			$_SESSION['st']['date'] = $form['date'];
			$_SESSION['st']['location'] = $form['location'];
			$_SESSION['st']['shelf'] = $form['shelf'];
			
			$_SESSION['st']['title'] = get_branch_code($form['branch_id'])." / ".$form['date']." / ".$form['location']." / ".$form['shelf'];*/

			$smarty->assign('items', $item_list);
			$con->sql_freeresult($q1);
			$smarty->display('stock_take.view_items.tpl');
		/*}else{
			$err[] = "No data";
			$smarty->assign("err", $err);
			$smarty->assign("form", $form);
			$this->open();
		}*/
	}

	function save_items(){
        global $con, $smarty, $config, $appCore, $LANG;
		$branch_id = $_SESSION['st']['branch_id'];
		$date = $_SESSION['st']['date'];
		$location = $_SESSION['st']['location'];
		$shelf = $_SESSION['st']['shelf'];

       if(!$branch_id || !$date || !$location || !$shelf){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		//check monthly closed
		$err = array();
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($date);
			if($is_month_closed){
				$err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];

				$q1 = $con->sql_query("select *, stp.id as item_id, si.sku_item_code, si.mcode, si.artno, si.description
									   from stock_take_pre stp
									   left join sku_items si on si.id = stp.sku_item_id
									   where stp.branch_id = ".mi($branch_id)." and stp.date = ".ms($date)." and stp.location = ".ms($location)." and stp.shelf = ".ms($shelf)."
									   order by stp.id");
			
				$item_list = array();
				while($r = $con->sql_fetchassoc($q1)){
					$item_list[] = $r;
				}
				$con->sql_freeresult($q1);
				
				$smarty->assign('items', $item_list);
				$smarty->assign('err',$err);
				$smarty->display('stock_take.view_items.tpl');
				exit;
			}
		}

        if($_REQUEST['qty']){
			foreach($_REQUEST['qty'] as $st_id=>$qty){
				$con->sql_query("update stock_take_pre set qty=".mf($qty)." where branch_id = ".mi($branch_id)." and date = ".ms($date)." and location = ".ms($location)." and shelf = ".ms($shelf)." and id=".mi($st_id));
			}
		}
		header("Location: $_SERVER[PHP_SELF]?a=view_items");
	}
	
	function delete_items(){
		global $con, $smarty, $config, $appCore, $LANG;
		$branch_id = $_SESSION['st']['branch_id'];
		$date = $_SESSION['st']['date'];
		$location = $_SESSION['st']['location'];
		$shelf = $_SESSION['st']['shelf'];

        if(!$branch_id || !$date || !$location || !$shelf){
			header("Location: $_SERVER[PHP_SELF]");
			exit;
		}
		
		//check monthly closed
		$err = array();
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($date);
			if($is_month_closed){
				$err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];

				$q1 = $con->sql_query("select *, stp.id as item_id, si.sku_item_code, si.mcode, si.artno, si.description
									   from stock_take_pre stp
									   left join sku_items si on si.id = stp.sku_item_id
									   where stp.branch_id = ".mi($branch_id)." and stp.date = ".ms($date)." and stp.location = ".ms($location)." and stp.shelf = ".ms($shelf)."
									   order by stp.id");
			
				$item_list = array();
				while($r = $con->sql_fetchassoc($q1)){
					$item_list[] = $r;
				}
				$con->sql_freeresult($q1);
				
				$smarty->assign('items', $item_list);
				$smarty->assign('err',$err);
				$smarty->display('stock_take.view_items.tpl');
				exit;
			}
		}
		
		if($_REQUEST['item_chx']){
		
            $con->sql_query("delete from stock_take_pre
							 where branch_id = ".mi($branch_id)." and date = ".ms($date)." and location = ".ms($location)." and shelf = ".ms($shelf)." and id in (".join(',',array_keys($_REQUEST['item_chx'])).")");

			header("Location: $_SERVER[PHP_SELF]?a=view_items");
		}
	}
	
	function stock_take(){
		global $con,$smarty,$sessioninfo;
		
		//unset($_SESSION['date']);
		//unset($_SESSION['loc']);
		//unset($_SESSION['shelf']);
		unset($_SESSION['st']);
		
		//print $sessioninfo['branch_id'];
		//$smarty->assign("brand_id",$sessioninfo['branch_id']);
		$smarty->display('stock_take.tpl');
	}
	
	function show_scan_product(){
		$this->search_product();
	}
  
	function add_items(){

	}
  
	function save_setting($err=""){
		global $con,$smarty,$sessioninfo,$config,$appCore,$LANG;
		
		//check monthly closed
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($_REQUEST['date_t']);
			if($is_month_closed)  $err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
		if(!$err){
			$this->is_date();
			$_SESSION['st']['branch_id'] = $sessioninfo['branch_id'];
			$_SESSION['st']['date'] = $_REQUEST['date_t'];
			$_SESSION['st']['location'] = $_REQUEST['location'];
			$_SESSION['st']['shelf'] = $_REQUEST['shelf'];
			$_SESSION['st']['title'] = get_branch_code($_SESSION['st']['branch_id'])." / ".$_SESSION['st']['date']." / ".$_SESSION['st']['location']." / ".$_SESSION['st']['shelf'];
			
			log_br($sessioninfo['id'], 'PDA Stock Take', 0, "save setting (Branch#".$sessioninfo['branch_id']);
		}else{
			$shelf = $_REQUEST['shelf'] ? $_REQUEST['shelf'] : $_SESSION['st']['shelf'];
			$location = $_REQUEST['location'] ? $_REQUEST['location'] : $_SESSION['st']['location'];
			$_REQUEST['date_t'] = $_REQUEST['date_t'] ? $_REQUEST['date_t'] : $_SESSION['st']['date'];
			
			$smarty->assign("form",$_REQUEST);
			$smarty->assign("shelf", $shelf);
			$smarty->assign("location", $location);
			$smarty->assign("errm", $err);
			$smarty->display("stock_take.tpl");
			exit;
		}
		//$smarty->display('stock_take.scan.tpl');
		
		header("Location: $_SERVER[PHP_SELF]?a=show_scan");
	}
	
	function show_scan(){
		global $con,$smarty,$sessioninfo;
		
		$smarty->display('stock_take.scan.tpl');
	}
  
	function save_scanning(){
		global $con,$smarty,$sessioninfo,$appCore,$config,$LANG;
		
		if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($_SESSION['st']['date']);
			if($is_month_closed){
				$err = array();
				$err[] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
				$this->save_setting($err);
				exit;
			}
		}

		if(!$_REQUEST['sku_item_id']){
			$err = array();
			$err[] = "Item not found.";
			$this->save_setting($err);
			exit;
		}

		$upd = array();
		$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($sessioninfo['branch_id']));
		$upd['branch_id'] = $sessioninfo['branch_id'];
		$upd['date'] = $_SESSION['st']['date'];
		$upd['location'] = $_SESSION['st']['location'];
		$upd['shelf'] = $_SESSION['st']['shelf'];
		$upd['user_id'] = $sessioninfo['id'];
		$upd['imported'] = "0";

		//get sku item id
		$sql = "select si.id,si.sku_item_code,si.artno,si.description,si.mcode,sku.is_fresh_market,cc.is_fresh_market as cc_is_fresh_market from sku_items si
		left join sku on si.sku_id=sku.id
		left join category_cache cc on sku.category_id=cc.category_id
		where si.id = ".mi($_REQUEST['sku_item_id']);
		$con->sql_query($sql) or die(mysql_error());

		$id = $con->sql_fetchfield(0);
		$sku_code = $con->sql_fetchfield(1);
		$artno = $con->sql_fetchfield(2);
		$desc = $con->sql_fetchfield(3);
		$mcode = $con->sql_fetchfield(4);
		
		if ($con->sql_fetchfield(5) == 'yes' || ($con->sql_fetchfield(5) == 'inherit' && $con->sql_fetchfield(6) == 'yes')) $upd['is_fresh_market'] = 1;
		else $upd['is_fresh_market'] = 0;
		 
		$upd['sku_item_id'] = $id;
		//$upd['arms_code'] = $sku_code;
		//$upd['artno'] = $artno;
		//$upd['description'] = $desc;
		//$upd['mcode'] = $mcode;
		//$upd['user'] = $sessioninfo['u'];
		$upd['qty'] = $_REQUEST['qty'];
		//$upd['description'] = $_REQUEST['description'];
		//$upd['selling_price'] = $_REQUEST['sell_price'];

		$result = $con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd)) or die(mysql_error());
		$stp_id = $con->sql_nextid($result);
		
		log_br($sessioninfo['id'], 'PDA Stock Take', 0, "Save Scanning (Branch#".$sessioninfo['branch_id']);
		
		if($result)
		{
			//$smarty->display('stock_take.scan.tpl');
			//header("location: stock_take.php?a=save_setting&date_t=".$_SESSION['st']['date']."&location=".$_SESSION['st']['location']."&shelf=".$_SESSION['st']['shelf']."&auto_add=".mi($_REQUEST['auto_add']));
			header("Location: $_SERVER[PHP_SELF]?a=show_scan&auto_add=".mi($_REQUEST['auto_add']));
		}

		
	}
  
	function validate_code(){
		global $con,$smarty,$sessioninfo;
		$bid = mi($sessioninfo['branch_id']);
      
		$code = $_REQUEST['code'];
		$sql = "select si.id as sku_item_id,si.description,ifnull(sip.price, si.selling_price) as selling_price, 
				si.doc_allow_decimal,si.is_parent,sku.is_fresh_market,cc.is_fresh_market as cc_is_fresh_market
				from sku_items si 
				left join sku on si.sku_id=sku.id
				left join category_cache cc on cc.category_id=sku.category_id
				left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
				where si.sku_item_code = ".ms(substr($code,0,12))." or si.mcode = ".ms($code)." or si.mcode = ".ms(substr($code,0,12))." or si.artno = ".ms($code)." or si.link_code =".ms($code)." or si.link_code = ".ms(substr($code,0,12));
		//print $sql;
		$q1 = $con->sql_query($sql) or die(mysql_error());
		
		if($con->sql_numrows($q1) > 0){
			$si_info = $con->sql_fetchassoc($q1);
			
			/*
			echo '<pre>';
			print_r($si_info);
			echo '</pre>';
			*/
			$allowed = true;
			if ($si_info['is_fresh_market'] == 'yes' && !$si_info['is_parent']) $allowed = false;
			if ($si_info['is_fresh_market'] == 'inherit' && $si_info['cc_is_fresh_market'] == 'yes' && !$si_info['is_parent']) $allowed = false;
			
			if ($allowed) {
				if($_REQUEST['auto_add_item']){
					$_REQUEST['sku_item_id'] = $si_info['sku_item_id'];
					$_REQUEST['qty'] = 1;
					$_REQUEST['auto_add'] = 1;
					$this->save_scanning();
					exit;
				}else{
					$si_info['code'] = $code;
					$smarty->assign("si_info", $si_info);
				}
			}
			else {
				$err[] = "Fresh market SKU, only parent is allowed";
				$smarty->assign("errm", $err);
			}
		}else{
			$err[] = "Item not found.";
			$smarty->assign("errm", $err);
		}

		$smarty->display('stock_take.scan.tpl');
	}

	function is_date(){
		//check date format
		list($year,$month,$day)=explode('-',$_REQUEST['date_t']);
		if (!$year || strlen(intval($year))!=4 || !$month || !$day)
		{
		header("Location:stock_take.php?a=stock_take");
		exit;
		}

		//check date available
		if (checkdate($month, $day, $year)){
		 //return TRUE;
		}else{
			header("Location:stock_take.php?a=stock_take");
			exit;
		}
	}
}

//print_r($_SESSION);
$ST_module = new ST_module('Stock Take');

?>

