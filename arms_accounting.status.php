<?php
/*
5/20/2019 1:40 PM Andy
- Added Account Receivable Integration.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('ARMS_ACCOUNTING_STATUS')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ARMS_ACCOUNTING_STATUS', BRANCH_CODE), "/index.php");
$maintenance->check(386);

class ARMS_ACCOUNTING_STATUS extends Module {
	var $list_size = 10;
	var $status_desc = array(
		-1 => 'Cancelled',
		0 => 'New',
		1 => 'Processing',
		2 => 'Done',
		3 => 'Error',
	);
	
	function __construct($title)
	{	
		global $smarty;
		
		$smarty->assign('status_desc', $this->status_desc);
		
		parent::__construct($title);
	}
	
	function _default()
	{
		$this->display();
	}
	
	function ajax_list_sel(){
		global $con, $sessioninfo, $config, $smarty;
		
		//print_r($_REQUEST);
		
		$acc_type = trim($_REQUEST['acc_type']);
		$tab_num = mi($_REQUEST['tab_num']);
		$page_num = mi($_REQUEST['page_num']);
		$size = $this->list_size;
		$start = $page_num*$size;
		
		if(!$acc_type)	die('Invalid Type');
		
		$batch_list = array();
		$xtra_join = '';
		
		$filter = array();
		$filter[] = "abo.type=".ms($acc_type);
		if(BRANCH_CODE != 'HQ')	$filter[] = "abo.branch_id=".mi($sessioninfo['branch_id']);
		
		switch($tab_num){
			case 1:	// New / Error
				$filter[] = "abo.status in (0,3)";
				break;
			case 2: // Processing
				$filter[] = "abo.status=1";
				break;
			case 3: // Done
				$filter[] = "abo.status=2";
				break;
			case 0: // search items
				$str = trim($_REQUEST['search_str']);
				if(!$str)	die('Cannot search empty string');
				$str2 = replace_special_char($_REQUEST['search_str']);
				
				$filter_or = array();
				$filter_or[] = "abo.batch_id=".ms($str);
				
				if($acc_type == 'ap'){
					$filter_or[] = 'abo.batch_id like '.ms('%'.$str2.'%');
					$filter_or[] = 'acif.grr_id like '.ms('%'.$str2.'%');
					$filter_or[] = "acif.acc_doc_no like ".ms('%'.$str2.'%');
					$filter_or[] = "gi.doc_no like ".ms('%'.$str2.'%');
					$xtra_join = "left join ap_arms_acc_info acif on acif.branch_id=abo.branch_id and acif.batch_id=abo.batch_id
								left join grr on grr.branch_id=acif.branch_id and grr.id=acif.grr_id
								left join grr_items gi on gi.branch_id=grr.branch_id and gi.grr_id=grr.id and gi.type='INVOICE'";
				}elseif($acc_type == 'cs'){
					$filter_or[] = 'abo.batch_id like '.ms('%'.$str2.'%').' or acif.inv_no like '.ms('%'.$str2.'%')." or acif.acc_doc_no like ".ms('%'.$str2.'%');
					$xtra_join = "left join cs_arms_acc_info acif on acif.branch_id=abo.branch_id and acif.batch_id=abo.batch_id";
				}elseif($acc_type == 'ar'){
					$filter_or[] = 'abo.batch_id like '.ms('%'.$str2.'%');
					$filter_or[] = 'acif.do_id like '.ms('%'.$str2.'%');
					$filter_or[] = "acif.acc_doc_no like ".ms('%'.$str2.'%');
					$filter_or[] = "do.inv_no like ".ms('%'.$str2.'%');
					$xtra_join = "left join ar_arms_acc_info acif on acif.branch_id=abo.branch_id and acif.batch_id=abo.batch_id
								left join do on do.branch_id=acif.branch_id and do.id=acif.do_id";
				}
				//print_r($filter_or);exit;
				$filter[] = "(".join(' or ', $filter_or).")";
				break;
			default:
				die('Invalid Page');
		}
		//print_r($filter);exit;
		$str_filter = "where ".join(' and ',$filter);
		
		// Count Total
		$con->sql_query("create temporary table tmp_acc_batch_no(
			batch_id char(20) not null primary key,
			branch_id int not null default 0
		)");
		$con->sql_query("insert IGNORE into tmp_acc_batch_no (batch_id, branch_id) (select abo.batch_id, abo.branch_id 
			from arms_acc_batch_no abo
			$xtra_join		
			$str_filter)");
		$con->sql_query("select count(*) as c from tmp_acc_batch_no");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		$total_rows = mi($tmp['c']);
	
		if($start>=$total_rows){
			$start = 0;
			$_REQUEST['p'] = 0;
		}
		$limit = "limit $start, $size";
		$order = "order by abo.last_update desc";
		
		$total_page = ceil($total_rows / $size);
		
		$sql = "select abo.*, b.code as bcode
			from arms_acc_batch_no abo
			join tmp_acc_batch_no tmp_acc on tmp_acc.branch_id=abo.branch_id and tmp_acc.batch_id=abo.batch_id and abo.type=".ms($acc_type)."
			left join branch b on b.id=abo.branch_id
			where abo.type=".ms($acc_type)."
			order by abo.last_update desc";
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$batch_list[] = $r;
			
			if($acc_type == 'ap'){
				$tbl = 'ap_arms_acc_info';
			}elseif($acc_type == 'cs'){
				$tbl = 'cs_arms_acc_info';
			}else{
				continue;
			}
		}
		$con->sql_freeresult($q1);
		
		//print_r($batch_list);exit;
		
		$smarty->assign('batch_list', $batch_list);
		$smarty->assign('acc_type', $acc_type);
		$smarty->assign('total_page', $total_page);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("arms_accounting.status.list.tpl");
		
		print json_encode($ret);
		
	}
	
	function view_details(){
		global $con, $sessioninfo, $smarty;
		
		$bid = mi($_REQUEST['branch_id']);
		$id = mi($_REQUEST['id']);
		$acc_type = trim($_REQUEST['acc_type']);
		
		if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id']){
			js_redirect("You cant access other branch info.", $_SERVER['PHP_SELF']);
		}
		
		$con->sql_query($sql = "select * from arms_acc_batch_no where branch_id=$bid and id=$id and type=".ms($acc_type));
		//print $sql;exit;
		$batch_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$batch_info){
			js_redirect("The Batch you requested is not found.", $_SERVER['PHP_SELF']);
		}
		
		$batch_id = trim($batch_info['batch_id']);
		
		if($acc_type == 'ap'){
			$tbl = 'ap_arms_acc_info';
		}elseif($acc_type == 'cs'){
			$tbl = 'cs_arms_acc_info';
		}elseif($acc_type == 'ar'){
			$tbl = 'ar_arms_acc_info';
		}else{
			js_redirect("Invalid Account Type", $_SERVER['PHP_SELF']);
		}
		
		$q1 = $con->sql_query("select acif.*
			from $tbl acif
			where acif.branch_id=$bid and acif.batch_id=".ms($batch_id));
		$inv_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($acc_type == 'cs' && $r['type'] == 'do' && $r['inv_no']){
				$con->sql_query("select id from do where branch_id=$bid and inv_no=".ms($r['inv_no']));
				$do = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$r['do_id'] = $do['id'];
			}
			$inv_list[] = $r;
			
		}
		$con->sql_freeresult($q1);
		
		//print_r($batch_info);
		//print_r($inv_list);
		
		$smarty->assign('batch_info', $batch_info);
		$smarty->assign('inv_list', $inv_list);
		$smarty->assign('acc_type', $acc_type);
		$smarty->display('arms_accounting.status.view_details.tpl');
	}
}

$ARMS_ACCOUNTING_STATUS = new ARMS_ACCOUNTING_STATUS('ARMS Accounting Integration Status');
?>