<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PAYMENT_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PAYMENT_VOUCHER', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Cheque Issue Log Sheet');

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){

		case 'print':
			$form=$_REQUEST;
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			$where="log_sheet_no =".ms($form['ls_no'])." and log_sheet_page=".ms($form['p'])."and log_sheet_status=3";

			$con->sql_query("select * from branch where id=".ms($form['b']));
			$branch=$con->sql_fetchrow();			
			$smarty->assign("branch", $branch);
			
			$q1=$con->sql_query("select voucher.*, b1.description as c_branch_name, b1.code as c_branch_code, b1.report_prefix as c_branch_prefix, vendor.description as vendor, vbd.description as bank 
	from voucher 
	left join vendor on voucher.vendor_id=vendor.id
	left join branch b1 on voucher.cheque_branch_id=b1.id
	left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
	where (voucher.status=3 or voucher.status=0) and $where order by cheque_no,payment_date");
			while ($r1 = $con->sql_fetchrow($q1)){
				$list[]=$r1;			
			}
			$smarty->assign("list", $list);
			$smarty->assign("form", $form);
			$page_row=20;
			$totalpage = ceil(count($list)/$page_row);
			for ($i=0,$page=1;$i<count($list);$i+=$page_row,$page++){
		        $smarty->assign("page", "$page of $totalpage");
		        $smarty->assign("start_counter", $i);
		        $smarty->assign("current_page", $page);
		        $smarty->assign("total_page", $totalpage);
        		$smarty->assign("list", array_slice($list,$i,$page_row));
		    	$smarty->display("payment_voucher.print.log_sheet.to_branch.tpl");
				$smarty->assign("skip_header",1);
			}	
			exit;
	
		case 'ajax_load_log_sheet_list':
		    load_log_sheet_list();
		    $smarty->display("payment_voucher.log_sheet.home.list.tpl");
			exit;
			
		case 'save':
			$form=$_REQUEST;
			foreach ($_REQUEST['cheque_no'] as $k=>$v){
				if($v!='Cancelled'){
					$q1=$con->sql_query("select voucher_branch_id, cheque_no, vvc_code from voucher where voucher_no=".ms($k));
					$r1=$con->sql_fetchrow($q1);
					if(!checking_cheque_no($k,$r1['vvc_code'],$r1['voucher_branch_id'],$v) && $v!=''){
				   		$errm['top'][] = sprintf($LANG['PAYMENT_VOUCHER_EXISTING_CHEQUE_NO'], $v, $k);				
					}
				}
				$con->sql_query("update voucher set cheque_no='$v' where  voucher_no=".ms($k));					

							
			}
			if($errm){
				$where="log_sheet_no=".ms($form['ls_no'])." and log_sheet_status=2";			
				load_log_sheet_items($where);
				if($r1){	
					$smarty->assign("items", $r1);
					$smarty->assign("errm", $errm);
					$smarty->assign("form", $form);
					$smarty->display("payment_voucher.log_sheet.edit.tpl");
				}						
			}
			else{
				header("Location: /payment_voucher.log_sheet.php?t=save&ls_no=$form[ls_no]");			
			}
			exit;
			
		case 'confirm':
			$form=$_REQUEST;
			$q1=$con->sql_query("select max(log_sheet_page) as page from voucher where log_sheet_no=".ms($form['ls_no'])." and log_sheet_status=3");
			$r1 = $con->sql_fetchrow($q1);
			$latest_page=$r1['page']+1;
				
			foreach ($form['cheque_no'] as $k=>$v){
				if($v!='Cancelled'){
					$q2=$con->sql_query("select voucher_branch_id, cheque_no, vvc_code from voucher where voucher_no=".ms($k));
					$r2=$con->sql_fetchrow($q2);
					if(!checking_cheque_no($k,$r2['vvc_code'],$r2['voucher_branch_id'],$v) && $v!=''){
				   		$errm['top'][] = sprintf($LANG['PAYMENT_VOUCHER_EXISTING_CHEQUE_NO'], $v, $k);				
					}
				}
				if($v && $_REQUEST['submit'][$k]){					
					$con->sql_query("update voucher set cheque_no='$v', log_sheet_status=3 ,  log_sheet_page='$latest_page' where voucher_no=".ms($k));				
				}
				else{
					$con->sql_query("update voucher set cheque_no='$v' where  voucher_no=".ms($k));					
				}						
			}
			if($errm){
				$where="log_sheet_no=".ms($form['ls_no'])." and log_sheet_status=2";		
				load_log_sheet_items($where);	
				if($r1){	
					$smarty->assign("items", $r1);
					$smarty->assign("errm", $errm);
					$smarty->assign("form", $form);
					$smarty->display("payment_voucher.log_sheet.edit.tpl");
				}						
			}
			else{
				header("Location: /payment_voucher.log_sheet.php?t=confirm&ls_no=$form[ls_no]/$latest_page");			
			}			
			exit;
			
		case 'view':			
		case 'open':
			$form=$_REQUEST;
			if($form['p'] && $form['a']=='view'){
				$form['log_sheet_status']=3;
				$where="log_sheet_no =".ms($form['ls_no'])." and log_sheet_page=".ms($form['p'])."and log_sheet_status=3";			
			}
			else{
				$where="log_sheet_no=".ms($form['ls_no'])." and log_sheet_status=2";			
			}
			load_log_sheet_items($where);		
			if($r1){
				$smarty->assign("items", $r1);
				$smarty->assign("form", $form);
				$smarty->display("payment_voucher.log_sheet.edit.tpl");
			}
			else{
				show_redir($_SERVER['PHP_SELF'], 'Cheque Issue Log Sheet', sprintf($LANG['PAYMENT_VOUCHER_INVALID_ID']));
				header("Location: /payment_voucher.log_sheet.php");	
			}	
			exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display("payment_voucher.log_sheet.home.tpl");
exit;

function load_log_sheet_items($where){
	global $con, $sessioninfo, $smarty, $r1, $form;
	
	$q1=$con->sql_query("select voucher.*, b1.description as c_branch_name, b1.code as c_branch_code, b1.report_prefix as c_branch_prefix, vendor.description as vendor, vbd.description as bank, b2.code as voucher_branch 
	from voucher 
	left join vendor on voucher.vendor_id=vendor.id
	left join branch b1 on voucher.cheque_branch_id=b1.id
	left join branch b2 on voucher.voucher_branch_id=b2.id
	left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
	where (voucher.status=3 or voucher.status=0) and $where order by payment_date, cheque_no");
	while ($d1 = $con->sql_fetchrow($q1)){
		$form['voucher_branch']=$d1['voucher_branch'];		
		$r1[]=$d1;	
	}
	//$r1 = $con->sql_fetchrowset($q1);
}

function checking_cheque_no($voucher_no,$bank,$vb,$cheque_no){
	global $con, $sessioninfo, $smarty;
	
	$con->sql_query("select id from voucher where voucher_branch_id='$vb' and cheque_no='$cheque_no' and vvc_code='$bank' and voucher_no<>'$voucher_no'");

	$r=$con->sql_fetchrow();
	if($r){
		return false;
	}
	else{
		return true;
	}		
}


function load_log_sheet_list(){
	global $con, $sessioninfo, $smarty;
	
	if (!$t) $t = intval($_REQUEST['t']);
	if(BRANCH_CODE != 'HQ'){
    	$where = "voucher.voucher_branch_id=$sessioninfo[branch_id] and ";	
	}
	else{
		$where='';
	}
	switch ($t){
	    case 0:
	        $where .= 'voucher.log_sheet_no like '. ms($_REQUEST['s'].'%').' or voucher.log_sheet_page like '. ms($_REQUEST['s'].'%').' or voucher.voucher_no like '. ms($_REQUEST['s'].'%').'';
	        $_REQUEST['s'] = '';
	        break;

		case 1: //saved log sheet
        	$where .= "voucher.active and voucher.log_sheet_status=2 and voucher.log_sheet_page=0";
        	break;
		
		case 2:
        	$where .= "voucher.active and voucher.log_sheet_status=3";
        	break;
    }
    
	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select id from voucher where
$where and (voucher.status=3 or voucher.status=0) and voucher.voucher_type<>5 group by log_sheet_no, log_sheet_page order by last_update desc");
	$total=0;
	while ($r = $con->sql_fetchrow()){
		$total++;
	}
	
	if ($total > $sz){
	    if ($start > $total) $start = 0;
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
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
    
	$con->sql_query("select voucher.*, branch.code as branch, count(voucher_no) as total_voucher
from voucher
left join branch on voucher_branch_id = branch.id
where $where and (voucher.status=3 or voucher.status=0) and voucher.voucher_type<>5 group by log_sheet_no, log_sheet_page order by last_update desc limit $start, $sz");
	while($r=$con->sql_fetchrow()){
		$list[]=$r;
	}
	$smarty->assign("list", $list);
	//echo"<pre>";print_r($list);echo"</pre>";
}

?>
