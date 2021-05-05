<?php
/*
REVISION HISTORY
================
11/7/2007 10:56:56 AM gary
- branches just can read itself voucher before completed.

11/28/2007 12:47:39 PM gary
- add validate for issue_name.

3/21/2008 11:09:38 AM gary
- fix when cancel pv stil print in PV Log_sheet but remark as cancelled.
- PV log_sheet without cheque list on the log_sheet status.

4/7/2008 11:44:21 AM gary
- amount words amend.

3/23/2010 2:40:40 PM Andy
- move function convert_number to common.php

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

4/5/2011 4:00:41 PM Justin
- Added new config['payment_voucher_print_cheque_choice'] and new field to validate whether want to print cheque or not.

6/24/2011 5:05:47 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:11:45 PM Andy
- Change split() to use explode()

9/12/2012 5:25 PM Andy
- improve loading speed and fix memory overflow problem.

10/1/2012 3:46 PM Justin
- Bug fixed on take too long to generate log sheet no.

10/18/2012 2:07:00 PM Fithri
- payment voucher "Print & Re-Print Cheque By Log Sheet" add search log sheet

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

2/20/2017 4:50 PM Justin
- Bug fixed on showing PHP error message while access this module.

remarks
========
STATUS :
status=0 => cancelled the voucher
status=1 => Saved Voucher
status=2 => voucher printed
status=3 => completed / cheque printed
status=4 => Damage cheque

log_sheet_status=1 => voucher printed or fast payment
log_sheet_status=2 => PV Log_sheet (summary) printed
log_sheet_status=3 => log_sheet send bck to branches after cheque printed
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PAYMENT_VOUCHER')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PAYMENT_VOUCHER', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

ini_set('memory_limit', '512M');
set_time_limit(0);

$smarty->assign("PAGE_TITLE", "Payment Voucher");

// selection for doc type
$doc_type = array (
	"VI" => "VI",
	"VD" => "VD",
	"C" => "C",
	"O" => "O",
	"VC" => "VC",
	"D" => "D"
);

$smarty->assign("doc_type", $doc_type);
			
if(BRANCH_CODE == 'HQ'){
	$con->sql_query("select id, code from branch order by sequence,code");
	$smarty->assign("branches", $con->sql_fetchrowset());
}
else{
	$con->sql_query("select id, code from branch where id=1 or id=$sessioninfo[branch_id] order by id desc");
	$smarty->assign("branches", $con->sql_fetchrowset());	
}

$file_name="issue_list.dat";
if(file_exists($file_name)){
	$handle = fopen($file_name, "r");
		
	while ($line = fgetcsv($handle,4096)){
		$tmp_name['name'] = trim($line[0]);
		$issue_list[]=$tmp_name;
	}
	$smarty->assign("issue_list", $issue_list);
}

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
			
		case 'open_ls':
			$form=$_REQUEST;
			$where="log_sheet_no=".ms($form['ls_no'])." and log_sheet_status>1";
/*			
			$q1=$con->sql_query("select voucher.*, b1.description as c_branch_name, b1.code as c_branch_code, b1.report_prefix as c_branch_prefix, vendor.description as vendor, vbd.description as bank, b2.code as voucher_branch 
from voucher 
left join vendor on voucher.vendor_id=vendor.id
left join branch b1 on voucher.cheque_branch_id=b1.id
left join branch b2 on voucher.voucher_branch_id=b2.id
left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
where (voucher.status=3 or voucher.status=0) and $where group by log_sheet_page, voucher_no order by log_sheet_page");
*/
			$q1=$con->sql_query("select voucher.*, b1.description as c_branch_name, b1.code as c_branch_code, b1.report_prefix as c_branch_prefix, vendor.description as vendor, vbd.description as bank, b2.code as voucher_branch 
from voucher 
left join vendor on voucher.vendor_id=vendor.id
left join branch b1 on voucher.cheque_branch_id=b1.id
left join branch b2 on voucher.voucher_branch_id=b2.id
left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
where (voucher.status>1 or voucher.status=0) and $where group by log_sheet_page, voucher_no order by log_sheet_page");
			while ($r1 = $con->sql_fetchrow($q1)){
				//echo"<pre>";print_r($r1);echo"</pre>";
				$form['voucher_branch']=$r1['voucher_branch'];
				$items[$r1['log_sheet_page']][]=$r1;
			}
			//echo"<pre>";print_r($items);echo"</pre>";
			//$r1 = $con->sql_fetchrowset($q1);
			//exit;
			if($items){
				$smarty->assign("items", $items);
				$smarty->assign("form", $form);
				$smarty->display("payment_voucher.ls.view.tpl");
			}
			else{
				show_redir($_SERVER['PHP_SELF'], 'Cheque Issue Log Sheet', sprintf($LANG['PAYMENT_VOUCHER_INVALID_ID']));
				header("Location: /payment_voucher.php");	
			}
			exit;
	
		case 'ajax_load_ls_items':
			$form=$_REQUEST;
			
			if($form['printed']){
				$where="voucher.status>1 and log_sheet_no=".ms($form['ls_no'])." and vvc_code=".ms($form['bank'])." and log_sheet_status>1";			
			}
			else{
				$where="voucher.status=2 and log_sheet_no=".ms($form['ls_no'])." and vvc_code=".ms($form['bank'])." and log_sheet_status=2";			
			}
									
			$q1=$con->sql_query("select voucher.*, b1.description as c_branch_name, b1.code as c_branch_code, b1.report_prefix as c_branch_prefix, vendor.description as vendor 
	from voucher 
	left join vendor on voucher.vendor_id=vendor.id
	left join branch b1 on voucher.cheque_branch_id=b1.id
	where $where order by payment_date, cheque_no");
			
		    $keyin = $con->sql_fetchrowset($q1);
			$smarty->assign("keyin", $keyin);			
			$smarty->assign("form", $_REQUEST);
		    $smarty->display("payment_voucher.home.print.items.tpl");
			exit;

		case 'ajax_load_log_sheet':
			$limit = 100;
			if($_REQUEST['from']=='cheque'){
				$smarty->assign("autocomplete", 'cheque_autocomplete');
        		$where = "voucher.active and voucher.log_sheet_status=2 and voucher.log_sheet_page=0 and voucher.status=2";		
			}
			else if($_REQUEST['from']=='reprint_ls'){
        		//$where = "voucher.active and voucher.log_sheet_status=2 and voucher.log_sheet_page=0 and voucher.status=2";
        		//all printed voucher can be reprint
				$where = "voucher.active and voucher.log_sheet_status>1";		
				if(BRANCH_CODE!='HQ'){
					$where.=" and voucher_branch_id=$sessioninfo[branch_id]";
				}			
			}
			else if($_REQUEST['from']=='reprint_cheque'){
				$smarty->assign("autocomplete", 'reprint_cheque_autocomplete');
				$where = "voucher.active and voucher.status=3 and log_sheet_no<>''";				
			}
			else if($_REQUEST['from']=='cheque_autocomplete'){
				$where = "voucher.active and voucher.log_sheet_status=2 and voucher.log_sheet_page=0 and voucher.status=2 and log_sheet_no like '$_REQUEST[cheque_autocomplete_log_sheet_no]%'";
				$limit = 50;
			}
			else if($_REQUEST['from']=='reprint_cheque_autocomplete'){
				$where = "voucher.active and voucher.status=3 and log_sheet_no<>'' and log_sheet_no like '$_REQUEST[reprint_cheque_autocomplete_log_sheet_no]%'";
				$limit = 50;
			}
        	
			$con->sql_query("select voucher.*, branch.code as branch, count(voucher_no) as total_voucher
from voucher
left join branch on voucher_branch_id = branch.id
where $where and voucher_type<>5 group by log_sheet_no order by last_update desc limit $limit");
			while($r=$con->sql_fetchrow()){
				$ls_list[]=$r;
			}
			
			if ($_REQUEST['from']=='reprint_cheque_autocomplete' || $_REQUEST['from']=='cheque_autocomplete') {
				if ($ls_list) {
					print "<ul>";
					foreach ($ls_list as $vcno) {
						print "<li title=\"$vcno[log_sheet_no]\">$vcno[log_sheet_no]</li>";
					}
					print "</ul>";
					exit;
				}
			}
			
		    if(count($ls_list)>0){
				$smarty->assign("ls_list", $ls_list);
			    $smarty->display("payment_voucher.home.log_sheet.refresh.tpl");			
			}
			else{
				echo "<script>alert('No New Payment Voucher Log Sheets Found');curtain_clicked();</script>";		
			}				
			exit;
	
		case 'keyin_damage_cheque'://using voucher_branch_id
			$form=$_REQUEST;
			$form['branch_id']=$form['damage_branch_id'];
			$form['banker']=$form['bank'];
			$form['remarks']=$form['damage_remarks'];
			$form['user_id']=$sessioninfo['id'];
						
			if($form['cheque_no'] && $form['damage_remarks']){
				$con->sql_query("insert into voucher_damage_cheque ".mysql_insert_by_field($form,array('cheque_no','branch_id','banker','remarks','user_id')));			
			}
			header("Location: /payment_voucher.php");
			exit;

		case 'print_butt'://using voucher_branch_id
			$form=$_REQUEST;			
			$f_no=$form['from_c_no'];
			$t_no=$form['to_c_no'];			
			
			$smarty->assign("form", $form);
			
			$q=$con->sql_query("select description from voucher_banker_details where id=".mi($form['bank']));
			$r=$con->sql_fetchrow($q);
			$bank=$r[0];
			$smarty->assign("bank", $bank);
					
			$q0=$con->sql_query("select * from branch where id=".mi($form['butt_branch_id']));
			$branch=$con->sql_fetchrow($q0);
			$smarty->assign("branch", $branch);
		
			for($i=$f_no;$i<=$t_no;$i++){
				$have_row=0;
				$q2=$con->sql_query("select voucher.*, vendor.description as vendor 
from voucher 
left join vendor on vendor.id=vendor_id
where vvc_code=".ms($form['bank'])." and voucher_branch_id=".mi($form['butt_branch_id'])." and cheque_no=".mi($i)." and (voucher.status=3 or voucher.status=0) and voucher.active ");
				$r2 = $con->sql_fetchrow($q2);
				if($r2){
					$have_row=1;
					if($r2['status']=='0'){
						$r2['issue_name']='CANCELLED';
						$r2['vendor']='CANCELLED';
					}
					$row_item=$r2;
				}
				if(!$have_row){
					$q3=$con->sql_query("select * 
from voucher_damage_cheque
where banker=".ms($form['bank'])." and branch_id=".mi($form['butt_branch_id'])." and cheque_no=".mi($i));
					$r3 = $con->sql_fetchrow($q3);
					if($r3){
						$have_row=1;
						$temp['status']=0;
						$temp['payment_date']=$r3['added'];
						$temp['cheque_no']=$i;
						$temp['vendor']='DAMAGED';
						$temp['issue_name']='DAMAGED';
						$temp['cancelled_reason']=$r3['remarks'];
						$row_item=$temp;											
					}
				}
				if(!$have_row){
					//js_redirect($LANG['PAYMENT_VOUCHER_LOSING_CHEQUE'], "/payment_voucher.php");
					$temp['status']=-1;
					$temp['payment_date']='';
					$temp['cheque_no']=$i;
					$temp['vendor']='NOT FOUND';
					$temp['issue_name']='NOT FOUND';
					$temp['cancelled_reason']='';
					$row_item=$temp;				
				}
				//else{
				$items[]=$row_item;
				//}		
			}
			$page_items=50;
			$totalpage = ceil(count($items)/$page_items);
			for ($i=0,$page=1;$i<count($items);$i+=$page_items,$page++){
		        $smarty->assign("page", "Page $page of $totalpage");
		        $smarty->assign("start_counter", $i);
        		$smarty->assign("current_page", $page);
        		$smarty->assign("total_page", $totalpage);
		        $smarty->assign("items", array_slice($items,$i,$page_items));
				$smarty->display("payment_voucher.print.cheque_butt.tpl");	
				$smarty->assign("skip_header",1);
			}	
		  	exit;		
		
		case 'ajax_load_banker_selection':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$bid=$_REQUEST['bid'];
			$ls_no=$_REQUEST['ls_no'];
			
			if(!$bid && $ls_no){
				$q2=$con->sql_query("select voucher_branch_id from voucher where log_sheet_no=".ms($ls_no)." limit 1");
			    $r2 = $con->sql_fetchrow($q2);
				$bid=$r2[0];		
			}
			
			$q1=$con->sql_query("select vvc_details from branch where id = $bid");
		    $r1 = $con->sql_fetchrow($q1);
		    $banker = unserialize($r1['vvc_details']);
		    foreach($banker['bank_id'] as $k=>$v){
		    	if($v){
					$q2=$con->sql_query("select description from voucher_banker_details where id=$v");
				    $r2 = $con->sql_fetchrow($q2);		    		
					$bank['bank_name'][$v]=$r2['description'];
				}
		    }
		    if($bank){
				$smarty->assign("bank", $bank);	
			    $smarty->display("payment_voucher.home.banker_selection.refresh.tpl");
			}
			else{
				echo "<script>alert('No Banker details found in selected branch, please refer to HQ Finance Department.');curtain_clicked();bid='';</script>";			
			}				
			exit;

		case 'keyin_cheque':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			if($_REQUEST['print_type']=='keyin_cheque' || $_REQUEST['print_type']=='keyin_cheque_date' || $_REQUEST['print_type']=='keyin_cheque_no'){
				if($_REQUEST['print_type']=='keyin_cheque_no'){
					if($_REQUEST['p'] && $_REQUEST['u'] && !$sessioninfo['privilege']['PAYMENT_VOUCHER_EDIT']){
						$user_q=$con->sql_query("select id from user where active AND NOT template AND l = " . ms($_REQUEST['u']) . " and p = md5(" . ms($_REQUEST['p']) . ")");
						$user_r = $con->sql_fetchrow($user_q);
						
						if($user_r){
							$privilege_q=$con->sql_query("select allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = ".mi($user_r['id'])." and privilege_code = 'PAYMENT_VOUCHER_EDIT' and branch.code = ". ms(BRANCH_CODE));	
							$privilege_r = $con->sql_fetchrow($privilege_q);
							if(!$privilege_r['allowed']){
								js_redirect($LANG['PAYMENT_VOUCHER_NOT_ALLOW_REPRINT'], "/payment_voucher.php");
							}
						}
						else{
							js_redirect($LANG['PAYMENT_VOUCHER_INVALID_USERNAME_OR_PASSWORD'], "/payment_voucher.php");				
						}				
					
					}
				}
				$count=0;
				$temp_no=0;
				foreach($_REQUEST['print_items'] as $k=>$v){
					if($_REQUEST['cheque_no'][$k]){
						if($count==0){
							$cheque_no=ms($_REQUEST['cheque_no'][$k]);
							$temp_no=$_REQUEST['cheque_no'][$k];
							$count++;
						}
						else{
							$cheque_no=ms($_REQUEST['cheque_no'][$k]);
						}
					}
					else{
						$cheque_no=$temp_no+$count;
						$cheque_no=ms($cheque_no);
						$count++;
					}
					$con->sql_query("update voucher set cheque_no='$cheque_no', status=3 where voucher_no='$v'");	
				}
				if($_REQUEST['print_type']=='keyin_cheque_no'){
					header("Location: /payment_voucher.php");
				}
			}
			exit;
			
		case 'reprint_ls':
			$reprint=1;
			$ls_no=$_REQUEST['log_sheet_no'];
			$con->sql_query("select voucher_no from voucher where log_sheet_no=".ms($ls_no));
			while ($r=$con->sql_fetchrow()){
				$_REQUEST['print_items'][]=$r['voucher_no'];
			}		
			$_REQUEST['print_type']='summary';
	
		case 'print_list':
			//if($_REQUEST['print_type']=='cheque' || $_REQUEST['print_type']=='cheque_date'){
			if($_REQUEST['print_type']=='cheques_by_ls'){
				if($_REQUEST['p'] && $_REQUEST['u'] && !$sessioninfo['privilege']['PAYMENT_VOUCHER_EDIT']){
					$user_q=$con->sql_query("select id from user where active AND NOT template AND l = " . ms($_REQUEST['u']) . " and p = md5(" . ms($_REQUEST['p']) . ")");
					$user_r = $con->sql_fetchrow($user_q);
					
					if($user_r){
						$privilege_q=$con->sql_query("select allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = ".mi($user_r['id'])." and privilege_code = 'PAYMENT_VOUCHER_EDIT' and branch.code = ". ms(BRANCH_CODE));	
						$privilege_r = $con->sql_fetchrow($privilege_q);
						if(!$privilege_r['allowed']){
							js_redirect($LANG['PAYMENT_VOUCHER_NOT_ALLOW_REPRINT'], "/payment_voucher.php");
						}
					}
					else{
						js_redirect($LANG['PAYMENT_VOUCHER_INVALID_USERNAME_OR_PASSWORD'], "/payment_voucher.php");				
					}				
				
				}
				foreach($_REQUEST['print_items'] as $k=>$v){
					$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where voucher_no='$v'");
			    	$r1 = $con->sql_fetchrow();
					$total_amount=$r1['total_credit']-$r1['total_debit'];
					$form['total']=$total_amount;
					$form['issue_name']=$r1['issue_name'];
					$form['vendor']=$r1['vendor'];
					$form['payment_date']=$r1['payment_date'];
					if(strlen($form['vendor'])>51){
						$form['vendor']='';
					}
					if($total_amount>0){
						$total_amount=number_format($total_amount, 2, '.', '');						
						list($front, $back) = explode('.', $total_amount);
					    $str = convert_number($front);
					    
					    if($back>0){
					    	
					    	$str = "$str and cents " . convert_number($back);				
						}
					    $str .= " only";
					    $form['total_in_words']=$str;
					    //$date = date("dmy", mktime($r1['payment_date']));		    
					    //$form['date']=$date;

						$smarty->assign("form", $form);
						
						$tpl=strtolower($r1['vvc_code']);
						$file="templates/cheque_formats/$tpl.tpl";
						if (file_exists($file)) {
							$smarty->display("cheque_formats/$tpl.tpl");
							$con->sql_query("update voucher set status=3 where voucher_no='$v'");
						} 
						else {
							js_redirect(sprintf($LANG['PAYMENT_VOUCHER_INVALID_TPL'], 'PAYMENT VOUCHER', BRANCH_CODE), "/payment_voucher.php");
						}
				    }
				}
			}
			
			elseif($_REQUEST['print_type']=='voucher' || $_REQUEST['print_type']=='voucher_date'){
				$item=array();
				foreach($_REQUEST['print_items'] as $k=>$v){			
						$con->sql_query("select voucher.*, vendor.description as vendor, branch.description as branch_name, branch.address as branch_address, branch.phone_1 as branch_phone_1, branch.phone_2 as branch_phone_2, branch.phone_3 as branch_phone_3, user.fullname as user_name, vbd.code as banker_code
from voucher 
left join user on user.id=voucher.user_id
left join vendor on voucher.vendor_id=vendor.id
left join branch on voucher.voucher_branch_id=branch.id
left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
where voucher_no='$v'");
			    	$r1 = $con->sql_fetchrow();
			    	$form=$r1;
					get_vvc($r1['vendor_id'],$r1['voucher_branch_id'],$r1['vvc_code']);
					$form['total_acct_code']=count($vvc['acct_code']);
					$total_amount=$r1['total_credit']-$r1['total_debit'];
					$form['total']=$total_amount;
					$form['vendor']=$r1['vendor'];
					if($total_amount>0){
						$total_amount=number_format($total_amount, 2, '.', '');
						list($front, $back) = explode('.', $total_amount);
			
					    $str = convert_number($front);
					    if($back>0){
					    	
					    	$str = "$str and cents " . convert_number($back);				
						}
					    $str .= " only";
					    $form['total_in_words']=$str;
					    $date = date("dmy", mktime($r1['payment_date']));		    
					    $form['date']=$date;

						$smarty->assign("form", $form);					
						
						$form['doc_type'] = unserialize($r1['doc_type']);
						$form['doc_date'] = unserialize($r1['doc_date']);		
						$form['doc_no'] = unserialize($r1['doc_no']);
						$form['credit'] = unserialize($r1['credit']);
						$form['debit'] = unserialize($r1['debit']);
						
						foreach ($form['doc_no'] as $k1=>$v1){
							$list[$k1]['doc_type']=$form['doc_type'][$k1];
							$list[$k1]['doc_date']=$form['doc_date'][$k1];
							$list[$k1]['doc_no']=$v1;
							$list[$k1]['credit']=$form['credit'][$k1];
							$list[$k1]['debit']=$form['debit'][$k1];
						}
						get_print_items($list);
					}
					$item='';
					$list='';
					if($r1['voucher_type']=='5'){
						$con->sql_query("update voucher set status=3, log_sheet_status=3 where voucher_no='$v'");						
					}
					elseif ((!$r1['cheque_no'] || $r1['status']<3) && ($_REQUEST['print_vendor_copy'] || $_REQUEST['print_branch_copy'])){
						//printed voucher log_sheet_status=1;
						$con->sql_query("update voucher set status=2, log_sheet_status=1 where voucher_no='$v'");
					}
				}					
			}
			//PRINT PV LOG_SHEET AND GENERATE LOG SHEET NO
			elseif($_REQUEST['print_type']=='summary' || $_REQUEST['print_type']=='summary_date'){
				$count=0;			
				//echo"<pre>";print_r($_REQUEST);echo"</pre>";
								
				foreach($_REQUEST['print_items'] as $k=>$v){			
						$con->sql_query("select voucher.*, vendor.description as vendor, branch.description as branch_name, branch.address as branch_address, branch.phone_1 as branch_phone_1, branch.phone_2 as branch_phone_2, branch.phone_3 as branch_phone_3, user.fullname as user_name, branch.code as branch_code
from voucher 
left join user on user.id=voucher.user_id
left join vendor on voucher.vendor_id=vendor.id
left join branch on voucher.voucher_branch_id=branch.id
where voucher_no='$v'");

			    	$r1 = $con->sql_fetchrow();
					$con->sql_freeresult();
			    	$form=$r1;
					$form['from_date']=$_REQUEST['from_date'];
					$form['to_date']=$_REQUEST['to_date'];
					$form['branch_code']=$r1['branch_code'];
										
					if($r1['branch_id']=='1' || $r1['voucher_type']==2){			
						$hq_list[$count]['voucher_no']=$v;
						$hq_list[$count]['payment_date']=$r1['payment_date'];
						$hq_list[$count]['status']=$r1['status'];
						$hq_list[$count]['vendor']=$r1['vendor'];
						$hq_list[$count]['issue_name']=$r1['issue_name'];
						$hq_list[$count]['voucher_type']=$r1['voucher_type'];
						$hq_list[$count]['urgent']=$r1['urgent'];
						$hq_list[$count]['total_credit']=$r1['total_credit'];
						$hq_list[$count]['total_debit']=$r1['total_debit'];
						$hq_list[$count]['log_sheet_status']=$r1['log_sheet_status'];
						$hq_list[$count]['voucher_branch_id']=$r1['voucher_branch_id'];	
																																										
						$r1['doc_no'] = unserialize($r1['doc_no']);
						$r1['credit'] = unserialize($r1['credit']);
						$r1['debit'] = unserialize($r1['debit']);
						
						foreach ($r1['doc_no'] as $k1=>$v1){
							$hq_list[$count]['credit']=$r1['credit'][$k1];
							$hq_list[$count]['debit']=$r1['debit'][$k1];
						}		
					
					}
					else{	
						$list[$count]['voucher_no']=$v;
						$list[$count]['payment_date']=$r1['payment_date'];
						$list[$count]['status']=$r1['status'];
						$list[$count]['vendor']=$r1['vendor'];
						$list[$count]['issue_name']=$r1['issue_name'];
						$list[$count]['voucher_type']=$r1['voucher_type'];
						$list[$count]['urgent']=$r1['urgent'];
						$list[$count]['total_credit']=$r1['total_credit'];
						$list[$count]['total_debit']=$r1['total_debit'];
						$list[$count]['log_sheet_status']=$r1['log_sheet_status'];
																																				
						$r1['doc_no'] = unserialize($r1['doc_no']);
						$r1['credit'] = unserialize($r1['credit']);
						$r1['debit'] = unserialize($r1['debit']);
						
						foreach ($r1['doc_no'] as $k1=>$v1){
							$list[$count]['credit']=$r1['credit'][$k1];
							$list[$count]['debit']=$r1['debit'][$k1];
						}
					}
					$count++;					
					if($r1['status']<3 && $r1['status']){
						$con->sql_query("update voucher set status=2, log_sheet_status=2 where voucher_no='$v'");			
					}
					elseif(($r1['status']==3 || !$r1['status']) && !$reprint){
						$con->sql_query("update voucher set log_sheet_status=2 where voucher_no='$v'");
					}
				}
				if($list){
					$branch_id=mi($_REQUEST['branch_id']);
					if(!$ls_no){
						$con->sql_query("select report_prefix, ip from branch where id = $branch_id");		
						$report_prefix = $con->sql_fetchrow();
						$con->sql_freeresult();

						$con->sql_query("select max(length(log_sheet_no)) as mx_lgth from voucher where branch_id = $branch_id and log_sheet_no like '$report_prefix[0]%'");
						$max_length = $con->sql_fetchfield(0);
						$con->sql_freeresult();
						
						if($max_length > 0) $filter = " and length(log_sheet_no) >= ".mi($max_length);
						$con->sql_query("select max(log_sheet_no) as mx from voucher where branch_id = $branch_id and log_sheet_no like '$report_prefix[0]%'".$filter);
						$r = $con->sql_fetchrow();
						$con->sql_freeresult();
					
						if (!$r){
							$n = 1;
						}
						else{
							$n = preg_replace("/^".$report_prefix[0]."/", "", substr($r[0],0,-2))+1;
						}				
						$ls_no = $report_prefix[0].sprintf("%05d", $n).'LS';					
					}					
					foreach ($list as $k=>$v){
						if($v['log_sheet_status']<'2'){
							$con->sql_query("update voucher set log_sheet_no='$ls_no' where voucher_no='$v[voucher_no]'");						
						}
					}				
				}			
				if($hq_list){				
					$con->sql_query("select report_prefix, ip, id from branch where code = 'HQ'");
					$report_prefix = $con->sql_fetchrow();
					$con->sql_freeresult();
					$branch_id=$report_prefix['id'];
					
					foreach($hq_list as $k=>$v){

						if (!isset($b_used[$v['voucher_branch_id']])){
							$b_used[$v['voucher_branch_id']] = 1;
							if(!$ls_no){
								$con->sql_query("select max(length(log_sheet_no)) as mx_lgth from voucher where log_sheet_no like '$report_prefix[0]%'");
								$max_length = $con->sql_fetchfield(0);
								$con->sql_freeresult();
								
								if($max_length > 0) $filter = " and length(log_sheet_no) >= ".mi($max_length);

								$con->sql_query("select max(log_sheet_no) as mx from voucher where log_sheet_no like '$report_prefix[0]%'".$filter);

								$r = $con->sql_fetchrow();							
								$con->sql_freeresult();

								if (!$r){
									$n = 1;
								}
								else{
									$n = preg_replace("/^".$report_prefix[0]."/", "", substr($r[0],0,-2))+1;
								}			
								$hq_ls_no = $report_prefix[0]. sprintf("%05d", $n).'LS';
							}
							else{
								$hq_ls_no=$ls_no;
							}
							
							$ls_used[$v['voucher_branch_id']]=$hq_ls_no;

							if($v['log_sheet_status']<'2'){
								$con->sql_query("update voucher set log_sheet_no='$hq_ls_no' where voucher_no='$v[voucher_no]'");						
							}
							$v['ls_no']=$hq_ls_no;
							$hq_ls_list[$v['voucher_branch_id']][]=$v;
						}
						else{
							$b=$v['voucher_branch_id'];
							$v['ls_no']=$ls_used[$b];
							$hq_ls_list[$b][]=$v;
							if($v['log_sheet_status']<'2'){
								$con->sql_query("update voucher set log_sheet_no='$hq_ls_no' where voucher_no='$v[voucher_no]'");						
							}
						}
					}
					
				}
				$form['total_voucher']=$count;
				$page_row=30;
							
				if($list){
					$form['is_hq']=0;
					$form['ls_no']=$ls_no;
					$smarty->assign("form", $form);
					$totalpage = ceil(count($list)/$page_row);
					for ($i=0,$page=1;$i<count($list);$i+=$page_row,$page++){
				        $smarty->assign("page", "$page of $totalpage");
				        $smarty->assign("start_counter", $i);
				        $smarty->assign("list", array_slice($list,$i,$page_row));
						$smarty->display("payment_voucher.print.log_sheet.tpl");
						$smarty->assign("skip_header",1);
					}				
				}
				if($hq_ls_list){
					foreach($hq_ls_list as $k=>$v){
						$hq_list=$v;
						$con->sql_query("select branch.code as branch_code, branch.description as branch_name, branch.address as branch_address, branch.phone_1 as branch_phone_1, branch.phone_2 as branch_phone_2, branch.phone_3 as branch_phone_3 from  branch where id=$k");

						$r_branch = $con->sql_fetchrow();
						$form['branch_name']=$r_branch['branch_name'];
						$form['branch_address']=$r_branch['branch_address'];						
						$form['branch_phone_1']=$r_branch['branch_phone_1'];
						$form['branch_phone_2']=$r_branch['branch_phone_2'];
						$form['branch_phone_3']=$r_branch['branch_phone_3'];
						$form['branch_code']=$r_branch['branch_code'];
											
						$form['is_hq']=1;
						$form['ls_no']=$ls_used[$k];

						$smarty->assign("form", $form);
						$totalpage = ceil(count($hq_list)/$page_row);
						for ($i=0,$page=1;$i<count($hq_list);$i+=$page_row,$page++){
					        $smarty->assign("page", "$page of $totalpage");
					        $smarty->assign("start_counter", $i);
					        $smarty->assign("hq_list", array_slice($hq_list,$i,$page_row));
							$smarty->display("payment_voucher.print.log_sheet.tpl");
							$smarty->assign("skip_header",1);
						}						
					}			
				}
			}
			exit;
		
		case 'ajax_load_keyin_list':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$form=$_REQUEST;
			$branch_id=mi($form['branch_id']);
			$f_date=ms($form['from_date']);
			$t_date=ms($form['to_date']);
			$f_no=ms($form['from_no']);
			$t_no=ms($form['to_no']);
			$bank=ms($form['bank']);
			$branch_field=" branch_id ";
			
			if($_REQUEST['type']=='cheque' || $_REQUEST['type']=='keyin_cheque' || $_REQUEST['type']=='keyin_cheque_no'){
				if($_REQUEST['type']=='keyin_cheque_no' || $_REQUEST['type']=='keyin_cheque')  {
					if($_REQUEST['include_printed']=='1'){
						$where=" and voucher.status=3 and voucher.active";
					}
					else{
						$where=" and voucher.status=3 and cheque_no is null and voucher.active";
					}					
				}
				else{
					if($_REQUEST['include_printed']=='1'){
						$where=" and voucher.status>1 and voucher.active";
					}
					else{
						$where=" and voucher.status=2 and voucher.active";
					}				
				}
				if($_REQUEST['bank']){
					$where.=" and voucher.vvc_code=$bank";
				}				
				$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $branch_field=$branch_id and payment_date>=$f_date and payment_date<=$t_date and voucher.id>=$f_no and voucher.id<=$t_no $where");

				$smarty->assign("keyin", $con->sql_fetchrowset());
			}
			elseif($_REQUEST['type']=='summary'){
				if($_REQUEST['include_printed']=='1'){
					$where=" and voucher.log_sheet_status>0 and voucher.active";
				}
				//@@ need to check voucher.log_sheet_status==1 as voucher printer
				else{
					$where=" and voucher.log_sheet_status=1 and voucher.active ";
				}
				if($_REQUEST['bank']){
					$where.=" and voucher.vvc_code=$bank";
				}
				if(BRANCH_CODE!='HQ'){
					$where.=" and voucher.voucher_type<>2 ";
				}				
				/*
				if(BRANCH_CODE!='HQ'){
					$where.=" and voucher.voucher_type<>2 ";
				}
				*/				
				$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $branch_field=$branch_id and payment_date>=$f_date and payment_date<=$t_date and voucher.id>=$f_no and voucher.id<=$t_no $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());
			}
			elseif($_REQUEST['type']=='cheque_date'){
				if($_REQUEST['include_printed']=='1'){
					$where=" and voucher.status>1 and voucher.active";
				}
				else{
					$where=" and voucher.status=2 and voucher.active";
				}	
				if($_REQUEST['bank']){
					$where.=" and voucher.vvc_code=$bank";
				}
				$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $branch_field=$branch_id and payment_date>=$f_date and payment_date<=$t_date $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());
			}
			
			elseif($_REQUEST['type']=='keyin_cheque_date' || $_REQUEST['type']=='continue_voucher_date' || $_REQUEST['type']=='summary_date'){
				$where = "voucher_no in ('" . join("','", array_keys($_REQUEST['print_items']))."')";
				$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());			
			}
			/*elseif($_REQUEST['type']=='continue_voucher_date'){
				$where = "voucher_no in ('" . join("','", array_keys($_REQUEST['print_items']))."')";
				$con->sql_query("select voucher.*, vendor.description as vendor  
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());			
			}*/
			elseif($_REQUEST['type']=='voucher'){
				if($_REQUEST['include_printed']=='1'){
					$where=" and voucher.status and  voucher.active";
				}
				else{
					$where=" and voucher.status=1 and voucher.active";
				}
				if($_REQUEST['bank']){
					$where.=" and voucher.vvc_code=$bank and cheque_no is not null";
				}
				if(BRANCH_CODE!='HQ'){
					$where.=" and voucher.voucher_type<>2 ";
				}
				
				$con->sql_query("select voucher.*, vendor.description as vendor 
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $branch_field=$branch_id and payment_date>=$f_date and payment_date<=$t_date and voucher.id>=$f_no and voucher.id<=$t_no $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());
			}
			elseif($_REQUEST['type']=='voucher_date'){
				if($_REQUEST['include_printed']=='1'){
					$where=" and voucher.status and  voucher.active";
				}
				else{
					$where=" and voucher.status=1 and voucher.active";
				}
				if(BRANCH_CODE!='HQ'){
					$where.=" and voucher.voucher_type<>2 ";
				}
					
				$con->sql_query("select voucher.*, vendor.description as vendor 
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where $branch_field=$branch_id and payment_date>=$f_date and payment_date<=$t_date $where");
				$smarty->assign("keyin", $con->sql_fetchrowset());
			}
			elseif($_REQUEST['type']=='single_cheque'){
				$vb=mi($form['vb']);
				$b=mi($form['b']);
				$id=mi($form['id']);
				$bank=ms($form['bank']);
				$con->sql_query("select voucher.*, vendor.description as vendor 
from voucher 
left join vendor on voucher.vendor_id=vendor.id
where voucher_branch_id=$vb and voucher.branch_id=$b and voucher.id=$id and vvc_code=$bank");
				$keyin=$con->sql_fetchrow();
				$smarty->assign("keyin", $keyin);
			}			
			$smarty->assign("form", $_REQUEST);
		    $smarty->display("payment_voucher.home.print.items.tpl");
			exit;
	
		case 'cancel':
			if(!$_REQUEST['branch_id']){
				$branch_id=$sessioninfo['branch_id'];
			}
			else{
				$branch_id=mi($_REQUEST['branch_id']);			
			}
			$id=mi($_REQUEST['id']);			
			$form['cancelled_by']=$sessioninfo['id'];
			$form['cancelled_reason']=$_REQUEST['reason'];
			$form['status']=0;
			
			//$form['log_sheet_status']=0;
			//$form['log_sheet_no']='';
			//$form['log_sheet_page']=0;
			
			
			if(BRANCH_CODE=='HQ'){
				$update_where=" id = $id and branch_id=$branch_id";
			}
			else{
				$update_where=" id = $id and voucher_branch_id=$sessioninfo[branch_id] and branch_id=$branch_id";	
			}
			$con->sql_query("update voucher set ".mysql_update_by_field($form, array("status", "cancelled_by", "cancelled_reason")) . " where $update_where");			
			header("Location: /payment_voucher.php?t=cancelled&id=$id");	
			exit;
			
	
		case 'ajax_load_vendor_detail':
			$alert=1;
		case 'refresh_bank':		
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$form=$_REQUEST;
			if($alert){
				$form['alert']=1;
			}
			$vendor_id=$form['vendor_id'];
			$branch_id=$form['voucher_branch_id'];
			$bank=$_REQUEST['bank'];
			
				
			get_vvc($vendor_id,$branch_id,$bank);
			$form['total_acct_code']=count($vvc['acct_code']);
			//echo"<pre>";print_r($form);echo"</pre>";
			$smarty->assign("form", $form);
			
			if(!$vvc['bank']){
				echo "<script>alert('No Banker details found in selected branch, please refer to HQ Finance Department.');</script>";
			}
			elseif(!$vvc['acct_code'] && $_REQUEST['voucher_type'] != '4'){
				echo "<script>
					    alert('No Accout Code details found for this vendor, please refer to HQ Finance Department.');
						$('vendor_id').value='';	
						$('autocomplete_vendor').value='';
						$('autocomplete_vendor_choices').value='';
					 </script>";
			}
			else{
		    	$smarty->display("payment_voucher.edit.vendor_detail.tpl");
			}		
			exit;		
	
		case 'ajax_load_voucher_list':
		    load_voucher_list();
		    $smarty->display("payment_voucher.home.list.tpl");
			exit;
		
		case 'ajax_load_ls_list':
		    load_ls_list();
		    $smarty->display("payment_voucher.home.ls_list.tpl");
			exit;		
			
		case 'new':
			$smarty->display("payment_voucher.edit.tpl");		
			exit;
		
		case 'print':		
		case 'print_cheque':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			//exit;
			$cheque_q=$con->sql_query("select damage_cheque, cheque_no from voucher where voucher_no =".ms($_REQUEST['print_items']));
			$cheque_r=$con->sql_fetchrow($cheque_q);
			
			if($_REQUEST['u'] && $cheque_r['cheque_no'] && !$sessioninfo['privilege']['PAYMENT_VOUCHER_EDIT']){
				$user_q=$con->sql_query("select id from user where active AND NOT template AND l = " . ms($_REQUEST['u']) . " and p = md5(" . ms($_REQUEST['p']) . ")");
				$user_r = $con->sql_fetchrow($user_q);
				
				if($user_r){
					$privilege_q=$con->sql_query("select allowed from user_privilege left join branch on user_privilege.branch_id = branch.id where user_id = ".mi($user_r['id'])." and privilege_code = 'PAYMENT_VOUCHER_EDIT' and branch.code = ". ms(BRANCH_CODE));	
					$privilege_r = $con->sql_fetchrow($privilege_q);
					if(!$privilege_r['allowed']){
						js_redirect($LANG['PAYMENT_VOUCHER_NOT_ALLOW_REPRINT'], "/payment_voucher.php");
					}
				}
				else{
					js_redirect($LANG['PAYMENT_VOUCHER_INVALID_USERNAME_OR_PASSWORD'], "/payment_voucher.php");				
				}				
			}
			$cheque_no=$_REQUEST['cheque_no'];
			if(!checking_cheque_no() && $cheque_no!=''){
				echo "<script>alert('Error occured during printing cheque, Existing Cheque No.')</script>";
				exit;
			}
						
			$con->sql_query("select * from branch where id =".mi($_REQUEST['branch_id'])."");
			$smarty->assign("branch", $con->sql_fetchrow());
							
		case 'view':
		case 'open':
			$form=$_REQUEST;
			get_voucher_details($form);
			if($_REQUEST['a']=='print_cheque' || $_REQUEST['a']=='print'){
				if($total_amount>0){
					if($_REQUEST['a']=='print_cheque' && strlen($form['vendor'])>51){
						$form['vendor']='';
					}
					$total_amount=number_format($total_amount, 2, '.', '');
					list($front, $back) = explode('.', $total_amount);
				    $str = convert_number($front);
				    if($back>0){				    	
				    	$str = "$str and cents " . convert_number($back);				
					}
				    $str .= " only";
				    $form['total_in_words']=$str;
			    }
			    else{
					js_redirect(sprintf($LANG['PAYMENT_VOUCHER_INVALID_AMOUNT'], 'PAYMENT VOUCHER', BRANCH_CODE), "/payment_voucher.php");				
				}
		    }
			//echo"<pre>";print_r($form);echo"</pre>";			    	    
			$smarty->assign("form", $form);
			
			if($_REQUEST['a']=='print'){
				get_print_items($list);	
				if($form['voucher_type']=='5'){
					$con->sql_query("update voucher set status=3, log_sheet_status=3 where id =".mi($_REQUEST['id'])." and branch_id=".mi($_REQUEST['branch_id'])." and voucher_branch_id=".mi($_REQUEST['voucher_branch_id'])."");						
				}			
				elseif (!$form['cheque_no'] && $form['status']<3){
					$con->sql_query("update voucher set status=2, log_sheet_status=1  where id =".mi($_REQUEST['id'])." and branch_id=".mi($_REQUEST['branch_id'])." and voucher_branch_id=".mi($_REQUEST['voucher_branch_id'])."");			
				}	
			}
			elseif($_REQUEST['a']=='print_cheque'){
				//$cheque_no=ms($_REQUEST['cheque_no']);
				
				if($_REQUEST['action']=='print'){
					$tpl=strtolower($_REQUEST['tpl']);
					$margin['top']=$_REQUEST['top'];
					$margin['left']=$_REQUEST['left'];
					$smarty->assign("margin", $margin);
					$file="templates/cheque_formats/$tpl.tpl";
	
					$smarty->assign("vc_no", $_REQUEST['print_items']);
					if(!$config['payment_voucher_print_cheque_choice'] || ($config['payment_voucher_print_cheque_choice'] && $_REQUEST['print_cheque'])) $smarty->display("cheque_formats/$tpl.tpl");
					else{
						$_REQUEST['t'] = 4;
						load_voucher_list();
					}
	
					if (file_exists($file) || ($config['payment_voucher_print_cheque_choice'] && !$_REQUEST['print_cheque'])){
						$con->sql_query("update voucher set cheque_no='$cheque_no', status=3 where voucher_no=".ms($_REQUEST['print_items'])."");						
					}else{
						js_redirect(sprintf($LANG['PAYMENT_VOUCHER_INVALID_TPL'], 'PAYMENT VOUCHER', BRANCH_CODE), "/payment_voucher.php");
					}
				}
				elseif($_REQUEST['action']=='edit'){
					$con->sql_query("update voucher set cheque_no='$cheque_no', status=3 where voucher_no=".ms($_REQUEST['print_items'])."");				
				}
			}	
			else{
				$smarty->display("payment_voucher.edit.tpl");
			}
			exit;
		
		
		case 'save':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			if(!$_REQUEST['branch_id']){
				$branch_id=$sessioninfo['branch_id'];
			}
			else{
				$branch_id=mi($_REQUEST['branch_id']);			
			}
			$form=$_REQUEST;
			$id=mi($form['id']);
			$form['user_id']=$sessioninfo['id'];
			$form['branch_id']=$branch_id;
			$form['vvc_code']=$form['bank'];
			$form['added']='CURRENT_TIMESTAMP()';
			if($form['urgent']){
				$form['urgent']=1;
			}
			$form['payment_date']=dmy_to_sqldate($form['selected_date']);
			//check the date either valid or not
			$arr= explode("-",$form['payment_date']);
			$yy=$arr[0];
			$mm=$arr[1];
			$dd=$arr[2];
			if(!checkdate($mm,$dd,$yy)){
				$form['payment_date']='';
			   	$errm['top'][] = $LANG['PAYMENT_VOUCHER_INVALID_PAYMENT_DATE'];							
			}
			$doc_type = array();
			$doc_date = array();
			$doc_no = array();
			$credit = array();
			$debit = array();
			$doc_type = array();

			$vendor_id=$form['vendor_id'];			
			
			//add-on requested by ah lee to allow same doc_type + doc_no when voucher_type='O'
			//$add_on=" && $form[doc_type][$k]!='O'";						
			//validation for existing doc_no with doc_type and vendor filter
			if($sessioninfo['u']=='wsatp'){
				//print_r($_REQUEST);
			}
			foreach($form['doc_no'] as $k=>$v){
				if($sessioninfo['u']=='wsatp'){
					//print "$k =>".memory_get_usage()."<br />";
					//if($k>20)	exit;
				}
				if($v!=''){
					$chk=array();
					if($id && $vendor_id){
						if($sessioninfo['u']=='wsatp'){
							//die("1 - select voucher_no, doc_no, doc_type from voucher where vendor_id = $vendor_id and (voucher_no<>'$form[voucher_no]') and status>0");
						}
						$q0=$con->sql_query("select voucher_no, doc_no, doc_type from voucher where vendor_id = $vendor_id and (voucher_no<>'$form[voucher_no]') and status>0 and doc_no like ".ms('%:"'.$v.'";%'));
						while($r0 = $con->sql_fetchrow($q0)){
							$chk['doc_no'] = unserialize($r0['doc_no']);
							$chk['doc_type'] = unserialize($r0['doc_type']);
							foreach($chk['doc_no'] as $k1=>$v1){
								if($v1==$v && $form['doc_type'][$k]==$chk['doc_type'][$k1] && $form['doc_type'][$k]!='O'){
									$check_used=1;
									$voucher_no=$r0['voucher_no'];
									break;
								}
							}
						}
						$con->sql_freeresult($q0);
					}
					elseif(!$id && $vendor_id){
						if($sessioninfo['u']=='wsatp'){
							//die("2 - select voucher_no, doc_no, doc_type from voucher where vendor_id = $vendor_id and status>0");
						}			
						$q0=$con->sql_query("select voucher_no, doc_no, doc_type from voucher where vendor_id = $vendor_id and status>0 and doc_no like ".ms('%:"'.$v.'";%'));
						while($r0 = $con->sql_fetchrow($q0)){
							$chk['doc_no'] = unserialize($r0['doc_no']);
							$chk['doc_type'] = unserialize($r0['doc_type']);
							foreach($chk['doc_no'] as $k1=>$v1){
								//echo "$v1==$v && $form[doc_type][$k]==$chk[doc_type][$k1]<br>";
								if($v1==$v && $form['doc_type'][$k]==$chk['doc_type'][$k1] && $form['doc_type'][$k]!='O'){
									$check_used=1;
									$voucher_no=$r0['voucher_no'];
									break;
								}
							}
						}
						$con->sql_freeresult($q0);	
					}
					elseif($id && !$vendor_id){
						if($sessioninfo['u']=='wsatp'){
							//die("3 - select voucher_no, doc_no, doc_type from voucher where (voucher_no<>'$form[voucher_no]') and status>0 and doc_no like ".ms('%:"'.$v.'";%'));
						}
						$q0=$con->sql_query("select voucher_no, doc_no, doc_type from voucher where (voucher_no<>'$form[voucher_no]') and status>0 and doc_no like ".ms('%:"'.$v.'";%'));
						while($r0 = $con->sql_fetchrow($q0)){
							$chk['doc_no'] = unserialize($r0['doc_no']);
							$chk['doc_type'] = unserialize($r0['doc_type']);
							foreach($chk['doc_no'] as $k1=>$v1){
								//echo "doc_no=$v1 / $v<br>";
								//echo"<pre>";print_r($form['doc_type'][$k]);echo"</pre>";
								//echo"<pre>";print_r($chk['doc_type'][$k1]);echo"</pre>";
								if($v1==$v && $form['doc_type'][$k]==$chk['doc_type'][$k1] && $form['doc_type'][$k]!='O'){
									$check_used=1;
									$voucher_no=$r0['voucher_no'];
									break;
								}
							}
						}
						$con->sql_freeresult($q0);			
					}
					elseif(!$id && !$vendor_id){
						if($sessioninfo['u']=='wsatp'){
							//die("4 -select voucher_no, doc_no, doc_type from voucher where status>0");
						}
						$q0=$con->sql_query("select voucher_no, doc_no, doc_type from voucher where status>0 and doc_no like ".ms('%:"'.$v.'";%'));
						while($r0 = $con->sql_fetchrow($q0)){
							$chk['doc_no'] = unserialize($r0['doc_no']);
							$chk['doc_type'] = unserialize($r0['doc_type']);
							foreach($chk['doc_no'] as $k1=>$v1){
								if($v1==$v && $form['doc_type'][$k]==$chk['doc_type'][$k1] && $form['doc_type'][$k]!='O'){
									$check_used=1;
									$voucher_no=$r0['voucher_no'];
									break;
								}
							}
						}
						$con->sql_freeresult($q0);		
					}
				    if ($check_used!='1'){
				    	$temp_credit = preg_replace("(,)", '', $form['credit'][$k]);
				    	$temp_debit = preg_replace("(,)", '', $form['debit'][$k]);
						$doc_type[] = $form['doc_type'][$k];
						
						$form['doc_date'][$k]=dmy_to_sqldate($form['doc_date'][$k]);
						//check the date either valid or not
						$arr= explode("-",$form['doc_date'][$k]);
						$yy=$arr[0];
						$mm=$arr[1];
						$dd=$arr[2];
						if(!checkdate($mm,$dd,$yy)){
							$form['doc_date'][$k]='';
			   				$errm['voucher'][] = sprintf($LANG['PAYMENT_VOUCHER_INVALID_DOC_DATE'], $form['doc_no'][$k]);							
						}
						$doc_date[] = $form['doc_date'][$k];						
						$doc_no[] = $form['doc_no'][$k];
						$credit[] = $temp_credit;	
						$debit[] = $temp_debit;					
					}
					else{
			   			$errm['voucher'][] = sprintf($LANG['PAYMENT_VOUCHER_USED_ITEMS'], $v,$voucher_no);			
					}
					$check_used=0;
				}
			}
			if($sessioninfo['u']=='wsatp'){
				//die("after loop item");
			}
			//echo"<pre>";print_r($doc_date);echo"</pre>";
			//exit;
			if(count($doc_no)>0){
				foreach($doc_no as $k=>$v){
					$total_credit+=$credit[$k];
					$total_debit+=$debit[$k];
				}			
			}
			else{
				$errm['top'][]=$LANG['PAYMENT_VOUCHER_NO_ITEM'];
				$smarty->assign("errm", $errm);
				$smarty->assign("form", $form);
				$smarty->display("payment_voucher.edit.tpl");
				exit;			
			}

			$form['total_credit']=$total_credit;
			$form['total_debit']=$total_debit;
			
			$form['doc_type'] = serialize($doc_type);
			$form['doc_date'] = serialize($doc_date);
			$form['doc_no'] = serialize($doc_no);
			$form['credit'] = serialize($credit);
			$form['debit'] = serialize($debit);

			if(BRANCH_CODE=='HQ'){
				$update_where=" id = $id and branch_id=$branch_id";
				$form['voucher_branch_id']=mi($form['voucher_branch_id']);
			}
			else{
				$update_where=" id = $id and voucher_branch_id=$sessioninfo[branch_id] and branch_id=$branch_id";
				$form['voucher_branch_id']=$branch_id;		
			}
			if($form['voucher_type']=='2' && !$form['log_sheet_status']){
				$form['log_sheet_status']=1;
			}
			// 11/28/2007 12:46:52 PM add by gary to avoid have issue name.
			if($form['voucher_type']=='1' || $form['voucher_type']=='2' || $form['voucher_type']=='5'){
				$form['issue_name']='';
			}
			if(!$form['status']){
				$form['status']=1;			
			}

			if($id){
				$con->sql_query("update voucher set ".mysql_update_by_field($form, array('vendor_id', "user_id", "vvc_code", "payment_date","doc_type", "doc_date", "doc_no", "credit", "debit", "total_credit", "total_debit", "voucher_remark", "issue_name", "acct_code",'urgent','cheque_branch_id','log_sheet_status','status')) . " where $update_where");
			}
			else{
				$is_new=1;
				$con->sql_query("insert into voucher ".mysql_insert_by_field($form,array('vendor_id', 'voucher_no', 'vvc_code', 'branch_id', 'user_id', 'payment_date', 'doc_type', 'doc_date', 'doc_no', 'credit', 'debit', 'total_credit','total_debit','added', 'voucher_branch_id', 'voucher_remark', 'voucher_type', 'issue_name', 'acct_code','urgent','cheque_branch_id','log_sheet_status','status')));
				
				$id=$con->sql_nextid();
			    $con->sql_query("select report_prefix from branch where id=$branch_id");
			    $r2 = $con->sql_fetchrow();			    
				$prefix=$r2[0];				
				$voucher_no= sprintf("%s%05d",$prefix,$id);			
				$con->sql_query("update voucher set voucher_no='$voucher_no' where id = $id and branch_id=$branch_id");
			}
			if($errm){				
				$smarty->assign("errm", $errm);
				$form['id']=$id;
				get_voucher_details($form);
				//echo"<pre>";print_r($form);echo"</pre>";
				$smarty->assign("form", $form);
				$smarty->display("payment_voucher.edit.tpl");
				exit;			
			}
			else{
				if(!$is_new){
					header("Location: /payment_voucher.php");				
				}
				else{
					header("Location: /payment_voucher.php?a=new&t=save&v_no=$voucher_no&d=$form[payment_date]&cbid=$form[cheque_branch_id]&vbid=$form[voucher_branch_id]&v_type=$form[voucher_type]");
				}
			}	
			exit;

							
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;	
	}
}

$smarty->display("payment_voucher.home.tpl");
exit;

function checking_cheque_no(){
	global $con, $sessioninfo, $smarty;

	$voucher_no=ms($_REQUEST['print_items']);	
	$bank=ms($_REQUEST['tpl']);
	$vb=ms($_REQUEST['voucher_branch_id']);
	$cheque_no=ms($_REQUEST['cheque_no']);	
	
	$con->sql_query("select id from voucher where voucher_branch_id=$vb and cheque_no=$cheque_no and vvc_code=$bank and voucher_no<>$voucher_no");
	$r=$con->sql_fetchrow();
	if($r){
		return false;
	}
	else{
		return true;
	}		
}

function load_ls_list(){
	global $con, $sessioninfo, $smarty, $config;
	
	if (!$t) $t = intval($_REQUEST['t']);
	
	if(BRANCH_CODE != 'HQ'){
    	$where = " and voucher.voucher_branch_id=$sessioninfo[branch_id] ";	
	}
	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}
	$con->sql_query("select id from voucher
where voucher.status=3 and log_sheet_no<>'' $where
group by log_sheet_no");

	$total=0;
	while ($r = $con->sql_fetchrow()){
		$total++;
	}
	//echo "$total<br>";
	if ($total > $sz)
	{
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
	

/*	
	$con->sql_query("select voucher.log_sheet_no, voucher.branch_id, branch.code as branch, sum(voucher.log_sheet_page<>'0') as total_complete, sum(voucher.log_sheet_page='0') as total_incomplete, count(log_sheet_page) as total_all
from voucher
left join branch on voucher_branch_id = branch.id
where (voucher.status=3 or voucher.status=0) and log_sheet_no<>'' $where
group by log_sheet_no order by total_incomplete desc limit $start, $sz");
*/
	$con->sql_query("select voucher.log_sheet_no, voucher.branch_id, branch.code as branch, sum(voucher.log_sheet_page<>'0') as total_complete, sum(voucher.log_sheet_page='0') as total_incomplete, count(log_sheet_page) as total_all
from voucher
left join branch on voucher_branch_id = branch.id
where (voucher.status>1 or voucher.status=0) and log_sheet_status>1 and log_sheet_no<>'' $where
group by log_sheet_no order by total_incomplete desc limit $start, $sz");
	while($r=$con->sql_fetchrow()){
		$list[]=$r;
	}
	//echo"<pre>";print_r($list);echo"</pre>";
	$smarty->assign("list", $list);	
}


function load_voucher_list(){
	global $con, $sessioninfo, $smarty, $config;
	
	if (!$t) $t = intval($_REQUEST['t']);
	if(BRANCH_CODE != 'HQ'){
		if($t=='4'){
    		$where = "voucher.voucher_branch_id=$sessioninfo[branch_id] and ";		
		}
		else{
    		$where = "voucher.branch_id=$sessioninfo[branch_id] and ";		
		}	
	}
	switch ($t)
	{
	    case 0:
	        $where .= '(voucher.id = ' . mi($_REQUEST['search']) . ' or voucher.voucher_no like ' . ms($_REQUEST['search'].'%') . ' or voucher.doc_no like '. ms('%:"'.$_REQUEST['search'].'%') . ' or vendor.description like '. ms($_REQUEST['search'].'%') . ' or voucher.cheque_no like '. ms($_REQUEST['search'].'%') .')';
			break;

		case 1: // show saved
        	$where .= "voucher.active and voucher.status=1";
        	break;
        
		case 2: // show voucher printed
        	$where .= "voucher.active and voucher.status>1 and voucher.status<>3";
        	$status['voucher_printed']=1;
			$smarty->assign("status", $status);
        	break;
        
		case 3: // show cancelled/inactive
        	$where .= "voucher.active and voucher.status<1";
        	$status['cancel']=1;
			$smarty->assign("status", $status);
        	break;
        	
		case 4: // show completed voucher
        	$where .= "voucher.active and voucher.status=3";
        	$status['completed']=1;
        	$status['voucher_printed']=1;
			$smarty->assign("status", $status);
        	break;
	}

	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}
	$con->sql_query("select count(*) from voucher
left join vendor on vendor.id=voucher.vendor_id
where $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
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

	$con->sql_query("select voucher.*, vendor.description as vendor, branch.code as branch, branch.report_prefix as prefix , vbd.description as banker
from voucher  
left join vendor on vendor.id=voucher.vendor_id
left join branch on branch.id=voucher.voucher_branch_id
left join voucher_banker_details vbd on vbd.id=voucher.vvc_code
where $where 
order by last_update desc limit $start, $sz");
	while($r=$con->sql_fetchrow()){
		$list[]=$r;
	}
	$smarty->assign("list", $list);
	//echo"<pre>";print_r($list);echo"</pre>";
}

function get_vvc($vendor_id,$branch_id,$bank){

	global $con, $sessioninfo, $smarty, $vvc, $form;

	$vvc=array();
	if($vendor_id){

		$con->sql_query("select * from vendor where id = $vendor_id");
	    $r = $con->sql_fetchrow();
	    $temp['acct_code'] = unserialize($r['acct_code']);
	    $form['vendor']=$r['description'];
	    if($temp['acct_code']){
		    foreach($temp['acct_code'][$branch_id] as $k=>$v){
				if($v!=''){
					$vvc['acct_code'][]=$v;
				}
			}		
		}	
	}
	else{
		$vvc['acct_code']=$_REQUEST['acct_code'];
	}
	
	//get the last selected bank from the bank 
	$q0=$con->sql_query("select vvc_code from voucher where voucher_branch_id=$branch_id order by added desc limit 1");
    $r0 = $con->sql_fetchrow($q0);		
    $last_vvc_code=$r0['vvc_code'];

	$q1=$con->sql_query("select vvc_details from branch where id = $branch_id");
    $r1 = $con->sql_fetchrow($q1);
    $temp['details'] = unserialize($r1['vvc_details']);	

    if($temp['details']['bank_id']){
    	$i=1;
	    foreach($temp['details']['bank_id'] as $k=>$v){
			if($v){
				$vvc['bank']['bank_id'][$v]=$v;
				
				$q2=$con->sql_query("select description from voucher_banker_details where id=$v");
			    $r2 = $con->sql_fetchrow($q2);
			    
				$vvc['bank']['bank_name'][$v]=$r2['description'];	
				$vvc['bank']['banker_code'][$v]=$temp['details']['banker_code'][$k];
				
				if(!$bank && $i==1){
					if($v!=$last_vvc_code && $last_vvc_code){
							$vvc['selected_bank']=$last_vvc_code;
							$vvc['selected_bank_name']=$vvc['bank']['bank_name'][$last_vvc_code];
							$vvc['selected_bank_code']=$vvc['bank']['banker_code'][$last_vvc_code];						
					}
					else{
						$vvc['selected_bank']=$v;
						$vvc['selected_bank_name']=$vvc['bank']['bank_name'][$v];
						$vvc['selected_bank_code']=$vvc['bank']['banker_code'][$v];				
					
					}
					$i++;				
				}
				elseif($bank){
					$vvc['selected_bank']=$bank;					
					$vvc['selected_bank_name']=$vvc['bank']['bank_name'][$bank];
					$vvc['selected_bank_code']=$vvc['bank']['banker_code'][$bank];
				}				
			
			}
		}
	}
	$smarty->assign("vvc", $vvc);	
}

function get_voucher_details($form){
	global $con, $sessioninfo, $smarty, $form, $list, $total_amount,$vvc;
	
	if(BRANCH_CODE == 'HQ'){
		$where ="voucher.id =".mi($form['id'])." and voucher.branch_id =".mi($form['branch_id'])."";
	}
	else{
		$where ="voucher.id =".mi($form['id'])." and voucher.voucher_branch_id =$sessioninfo[branch_id] and voucher.branch_id =".mi($form['branch_id'])."";			
	}
	
	$con->sql_query("select voucher.*, vendor.description as vendor, branch.description as branch_name, branch.address as branch_address, branch.phone_1 as branch_phone_1, branch.phone_2 as branch_phone_2, branch.phone_3 as branch_phone_3, user.fullname as user_name, branch.report_prefix as voucher_branch_code, vbd.code as banker_code, u1.u as cancel_user 
from voucher 
left join user on user.id=voucher.user_id
left join vendor on voucher.vendor_id=vendor.id
left join branch on voucher.voucher_branch_id=branch.id
left join voucher_banker_details vbd on vbd.id=voucher.vvc_code 
left join user u1 on u1.id=voucher.cancelled_by
where $where");
	$r=$con->sql_fetchrow();
	if($r){
		$form=$r;
		if($_REQUEST['a']=='print'){
			$form['banker_code']=$r['banker_code'];			
		}
		get_vvc($r['vendor_id'],$r['voucher_branch_id'],$r['vvc_code']);		
		$form['total_acct_code']=count($vvc['acct_code']);
		$total_amount=$r['total_credit']-$r['total_debit'];
		$form['total']=$total_amount;		
	}
	else{
		show_redir($_SERVER['PHP_SELF'], 'Payment Voucher', sprintf($LANG['PAYMENT_VOUCHER_INVALID_ID']));
		header("Location: /payment_voucher.php");	
	}

	$form['doc_type'] = unserialize($form['doc_type']);
	$form['doc_date'] = unserialize($form['doc_date']);		
	$form['doc_no'] = unserialize($form['doc_no']);
	$form['credit'] = unserialize($form['credit']);
	$form['debit'] = unserialize($form['debit']);
	
	foreach ($form['doc_no'] as $k=>$v){
		$list[$k]['doc_type']=$form['doc_type'][$k];
		$list[$k]['doc_date']=$form['doc_date'][$k];
		$list[$k]['doc_no']=$v;
		$list[$k]['credit']=$form['credit'][$k];
		$list[$k]['debit']=$form['debit'][$k];
	}
	$smarty->assign("list", $list);	
	//echo"<pre>";print_r($list);echo"</pre>";
}


function get_print_items($list){
	global $con, $sessioninfo, $smarty, $list, $item;
	
	$page_row=12;
	$item=array();
	foreach($list as $k2=>$v2){
		$abc=$list[$k2]['doc_type'];
		if(($list[$k2]['doc_type']=='VI' || $list[$k2]['doc_type']=='VD' || $list[$k2]['doc_type']=='C' || $list[$k2]['doc_type']=='O') && $list[$k2]['credit']>0){
			if($list[$k2]['doc_type']=='VI'){
				$list[$k2]['doc_type_display']='INVOICE';
			}
			elseif($list[$k2]['doc_type']=='VD'){
				$list[$k2]['doc_type_display']='VENDOR DEBIT NOTE';
			}
			elseif($list[$k2]['doc_type']=='C'){
				$list[$k2]['doc_type_display']='CREDIT NOTE';			
			}
			elseif($list[$k2]['doc_type']=='O'){
				$list[$k2]['doc_type_display']='OTHER';			
			}
			$list[$k2]['type']='positive';
			$item['positive'][]=$list[$k2];
		}

		if(($list[$k2]['doc_type']=='D' || $list[$k2]['doc_type']=='VC' || $list[$k2]['doc_type']=='O') && $list[$k2]['debit']>0){
			if($list[$k2]['doc_type']=='D'){
				$list[$k2]['doc_type_display']='DEBIT NOTE';
			}
			elseif($list[$k2]['doc_type']=='VC'){
				$list[$k2]['doc_type_display']='VENDOR CREDIT NOTE';
			}
			elseif($list[$k2]['doc_type']=='O'){
				$list[$k2]['doc_type_display']='OTHER';			
			}
			$list[$k2]['type']='negative';
			$item['negative'][]=$list[$k2];					
		}

	}
	$item['positive'][]='total_positive';
	$item['negative'][]='total_negative';	

	$total_items=array();
	$total_items = array_merge($item['positive'],$item['negative']);
	$total_items[]='total_amount';
	$smarty->assign("total", count($total_items));
	
	$totalpage = ceil(count($total_items)/$page_row);
	for ($i=0,$page=1;$i<count($total_items);$i+=$page_row,$page++){
        $smarty->assign("page", "$page of $totalpage");
        $smarty->assign("start_counter", $i);
        $smarty->assign("current_page", $page);
        $smarty->assign("total_page", $totalpage);
        $smarty->assign("list", array_slice($total_items,$i,$page_row));
        if($_REQUEST['print_vendor_copy']){
			$smarty->display("payment_voucher.print.no_signature.tpl");		
		}
		if($_REQUEST['print_branch_copy']){
			$smarty->display("payment_voucher.print.tpl");		
		}
		$smarty->assign("skip_header",1);
	}
	$con->sql_query("update voucher set status=3 where voucher_no='$v'");
}

?>
