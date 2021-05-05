<?php
/*
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CN', BRANCH_CODE), "/index.php");	
include("include/excelwriter.php");
class CNOTE_SUMMARY extends Module{
	function __construct($title){
		global $con, $smarty;
		if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		$this->init_data();
		parent::__construct($title);
	}

	function init_data(){
		global $con, $smarty, $sessioninfo;
		
		//branch
		$branches = array();
		$con->sql_query("select * from branch where active=1 order by sequence,code");
		while ($r = $con->sql_fetchassoc()) $branches[$r['id']] = $r;
		$con->sql_freeresult();
		$smarty->assign('branches',$branches);
		
		//user
		if($sessioninfo['id'] != 1){
			$user_filter = "where (user.is_arms_user=0 or cnote.user_id=".mi($sessioninfo['id']).")";
		}
		$con->sql_query("select distinct(user.id) as id, user.u from cnote left join user on cnote.user_id = user.id $user_filter group by user.id");
		$smarty->assign("user", $con->sql_fetchrowset());
		$con->sql_freeresult();
	}
	
	function _default(){
		$this->display();
	}
	
	function generate_report(){
		global $smarty, $sessioninfo, $con;
		$form = $_REQUEST;
		$bid = get_request_branch(true);
		$from = $form['from'];
		$to = $form['to'];
		$user_id = $form['user_id'];
		$status = $form['status'];
		
		$where = array();
		if ($bid) $where[] = 'cn.branch_id = '.$bid;
		switch ($status){
			case 0: 
				$where[] = "cn.active=1 and cn.status in (0,1,2)";
				break;
			case 1: 
				$where[] = "cn.active=1 and cn.approved=0 and cn.status in (0,1,2)";
				break;
			case 2: 
				$where[] = "cn.active=1 and cn.approved=1";
				break;
		}
		$where[] = "(cn.cn_date between ".ms($from)." and ".ms($to).")";
		if ($user_id > 0 ) $where[] = 'cn.user_id = '.mi($user_id);
		if ($where){
			$where = "where " . join(" and ", $where);
		}
		$order = "order by cn.last_update desc";
		
		$sql = $con->sql_query("select cn.*, user.u as created_u from cnote cn
		left join user on user.id=cn.user_id 
		left join branch on branch.id = cn.branch_id
		$where and branch.active=1 $order");
		while($r=$con->sql_fetchassoc($sql)){
			$r['adj_id_list'] = unserialize($r['adj_id_list']);
			$key = $r['branch_id'].'_'.$r['id'];
			$total['total_amount'] += $r['total_amount'];
			$total['total_qty'] += $r['total_qty'];
			$table[$key] = $r;
		}
		$con->sql_freeresult($sql);
		
		//get invoice no and date from cnote item
		$q1 = $con->sql_query("select ci.return_inv_no,  ci.return_inv_date, ci.cnote_id
			from cnote cn
			left join cnote_items ci on cn.branch_id = ci.branch_id and cn.id = ci.cnote_id
			$where and cn.return_type = 'multiple_inv'
			group by ci.cnote_id, ci.return_inv_no, ci.return_inv_date
			order by ci.cnote_id, ci.return_inv_no, ci.return_inv_date");
		
		while($r = $con->sql_fetchassoc($q1)){
			$data['cnItemList'][$r["cnote_id"]][] = $r["return_inv_no"] . " (" . $r["return_inv_date"] . ")";
		}
		$con->sql_freeresult($q1);
		$smarty->assign('total',$total);
		$smarty->assign('cnItemList', $data['cnItemList']);
		$smarty->assign("table", $table);
		$this->display();
	}
	
	function output_excel(){
		global $smarty, $sessioninfo;
		$smarty->assign('no_header_footer', true);
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename=cnote_summary'.time().'.xls');
		print ExcelWriter::GetHeader();
		$this->generate_report();
		print ExcelWriter::GetFooter();
		exit;
	}
}
$CNOTE_SUMMARY = new CNOTE_SUMMARY('CN Summary');
?>