<?
/*
// 10/1/2009 4:09 PM Andy
* - Hide those zero qty debtor
* 
6/24/2011 4:04:37 PM Andy
- Make all branch default sort by sequence, code.
*/
include("include/common.php");
//$con = new sql_db('gmark-hq.arms.com.my:4001','arms','4383659','armshq');

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
include("do.include.php");

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	    case 'ajax_get_transfer_details':
	        ajax_get_transfer_details();
	        exit;
	    default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

init_selection();
load_branches_group();

if(isset($_REQUEST['subm'])){
	load_table();
}

$smarty->assign('PAGE_TITLE','Transfer Report');

if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));

$smarty->display('do.report.tpl');
	
function load_table(){
	global $con,$smarty,$sessioninfo;
	$filter = array();
	$err = array();
	//print_r($_REQUEST);
	$from_date = $_REQUEST['from'];
	$to_date = $_REQUEST['to'];
	$do_type = $_REQUEST['do_type'];
	
	if(!$from_date||!$to_date)  $err[] = "Invalid Date Range.";
	
	if(BRANCH_CODE=='HQ'){
		if($_REQUEST['branch_id'])  $filter[] = "branch_id in (".join(',',$_REQUEST['branch_id']).")";
		else    $err[] = 'Please Select Delivery From Branch';
	}else   $filter[] = "branch_id=".mi($sessioninfo['branch_id']);
	
	if($do_type=='transfer'){
        if($_REQUEST['to_branch_id'])   $filter[] = "do_branch_id in (".join(',',$_REQUEST['to_branch_id']).")";
		else    $err[] = 'Please Select Delivery To Branch';
	}else{
        if($_REQUEST['to_branch_id'])   $filter[] = "(do_branch_id in (".join(',',$_REQUEST['to_branch_id']).") or do_type<>'transfer')";
	}
	

	if($do_type) $filter[] = 'do_type='.ms($do_type);
	$filter[] = "approved=1 and active=1";
	$filter[] = "do_date between ".ms($from_date)." and ".ms($to_date);
	$filter = join(' and ',$filter);
	
	if($err){
		$smarty->assign('err',$err);
		return;
	}
	
	$sql = "select do.*
from do
where $filter";
	//print $sql;
	$q_1 = $con->sql_query($sql) or die(mysql_error());
	while($r = $con->sql_fetchrow($q_1)){
	    if($r['do_type']=='transfer'){
            $table[$r['do_type']][$r['branch_id']][$r['do_branch_id']] ++;
            $total[$r['do_type']]['total'][$r['do_branch_id']] ++;
		}elseif($r['do_type']=='credit_sales'){
            $table[$r['do_type']][$r['branch_id']][$r['debtor_id']] ++;
            $total[$r['do_type']]['total'][$r['debtor_id']] ++;
		}elseif($r['do_type']=='open'){
            $table[$r['do_type']][$r['branch_id']]['open'] ++;
            $total[$r['do_type']]['total']['open'] ++;
		}
		$total[$r['do_type']][$r['branch_id']]['total'] ++;
        $total[$r['do_type']]['total']['total'] ++;
	}
	$con->sql_freeresult($q_1);
	//print_r($table);
	
	// generate branch list
	if(BRANCH_CODE=='HQ'){
        $con->sql_query("select * from branch where id in (".join(',',$_REQUEST['branch_id']).") order by sequence,code") or die(mysql_error());
		$branches_list = $con->sql_fetchrowset();
	}else{
		$con->sql_query("select * from branch where id=".mi($sessioninfo['branch_id'])) or die(mysql_error());
		$branches_list = $con->sql_fetchrowset();
	}
	
	$con->sql_query("select * from branch where id in (".join(',',$_REQUEST['to_branch_id']).") order by sequence,code") or die(mysql_error());
	$to_branches_list = $con->sql_fetchrowset();
	
	// credit sales - debtor list
	if(!$do_type||$do_type=='credit_sales'){
		$debtor = $smarty->get_template_vars('debtor');
		if($debtor){
			foreach($debtor as $d){
				if($total['credit_sales']['total'][$d['id']]) $new_debtor[$d['id']] = $d;
			}
		}
	}
	
	$smarty->assign('table',$table);
	$smarty->assign('total',$total);
	$smarty->assign('branches_list',$branches_list);
	$smarty->assign('to_branches_list',$to_branches_list);
	$smarty->assign('debtor',$new_debtor);
}

function load_branches_group($id=0){
	global $con,$smarty;
	// check whether select all or specified group
	if($id>0){
		$where = "where id=".mi($id);
		$where2 = "where bgi.branch_group_id=".mi($id);
	}
	// load header
	$con->sql_query("select * from branch_group $where");
	$branches_group['header'] = $con->sql_fetchrowset();

	// load items
	$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id $where2 order by branch.sequence, branch.code");
	while($r = $con->sql_fetchrow()){
        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$smarty->assign('branches_group',$branches_group);
	return $branches_group;
}

function ajax_get_transfer_details(){
	global $con,$smarty,$sessioninfo;
	
	if(BRANCH_CODE=='HQ'){
        $branch_id = mi($_REQUEST['branch_id']);
	}else   $branch_id = $sessioninfo['branch_id'];
	
	$con->sql_query("select * from branch where id=$branch_id") or die(mysql_error());
	$branch_info = $con->sql_fetchrow();
	
	$to_id = mi($_REQUEST['to_id']);
	$do_type = $_REQUEST['do_type'];
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	
	$filter = array();
	$filter[] = "do.branch_id=$branch_id and do.do_date between ".ms($from_date)." and ".ms($to_date)." and do.do_type=".ms($do_type)." and do.approved=1 and do.active=1";
	if($do_type=='transfer')    $filter[] = "do_branch_id=$to_id";
	elseif($do_type=='credit_sales')    $filter[] = "debtor_id=$to_id";
	
	$filter = join(' and ',$filter);
	$sql = "select do.*,user.u from do
left join user on user.id=do.user_id
where $filter order by do_date";
	$con->sql_query($sql) or die(mysql_error());

	$smarty->assign('do_list',$con->sql_fetchrowset());
	$smarty->assign('branch_info',$branch_info);
	$smarty->display('do.report.transfer_details.tpl');
}
?>
