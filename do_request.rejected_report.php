<?php
/*
08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module
 */
include("include/common.php");
$maintenance->check(172);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO_REQUEST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO_REQUEST', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

class DO_REQUEST_REJECTED_REPORT extends Module{
	var $branches_list = array();
	var $data = array();
	var $sort_by_list = array(
		'dri.last_update' => 'Last Update',
		'dri.added' => 'Request Date',
		'si.sku_item_code' => 'ARMS Code',
		'si.artno' => 'Art No.',
		'si.mcode' => 'MCode',
		'category' => 'Category'
	);
	
	function __construct($title, $template=''){
		global $con, $smarty, $sessioninfo, $config;
		
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-90 day", strtotime($_REQUEST['date_to']))); 
		}
		
		$con->sql_query("select id,code,description from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
				
		$smarty->assign('branches_list', $this->branches_list);
		$smarty->assign('sort_by_list', $this->sort_by_list);
		
		parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $smarty;
		
		if($_REQUEST['load_report']){
			if($_REQUEST['export_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			
			$this->load_report();
		}
		
		$this->display();
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $config, $con_multi;
		
		$bid_list = array();
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$sort_by = $_REQUEST['sort_by'] ? $_REQUEST['sort_by'] : 'dri.last_update';
		$sort_order = $_REQUEST['sort_order'] == 'desc' ? 'desc' : 'asc';
		
		if(BRANCH_CODE == 'HQ'){
			if($_REQUEST['branch_id']){
				$bid_list[] = $_REQUEST['branch_id'];
			}else{
				$bid_list = array_keys($this->branches_list);
			}
		}else{
			$bid_list[] = $sessioninfo['branch_id'];
		}
		
		if(!$bid_list)	$err[] = "Please select branch.";
		if(!$date_from || !strtotime($date_from))	$err[] = "Invalid date from.";
		if(!$date_to || !strtotime($date_to))	$err[] = "Invalid date to.";
		if(!$err && strtotime($date_from) > strtotime($date_from))	$err[] = "Date to cannot early then date from.";
		if(!$err && date("Y", strtotime($date_from))<2007)	$err[] = "Report cannot show data early then year 2007.";
		
		$time1 = strtotime($date_from);
		$time2 = strtotime($date_to);
		$time_diff = $time2 - $time1;
		$date_diff = mi($time_diff/86400);
		if(!$err && $date_diff>90)	$err[] = "Report maximum show 90 days of data.";

		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		//print_r($bid_list);
		
		$con_multi= new mysql_multi();	// use report server
		$this->data = array();
		
		$order_by = '';
		switch($sort_by){
			case 'category':
				$order_by = "order by sku.category_id $sort_order, cc.p3 $sort_order, cc.p2 $sort_order";
				break;
			default: 
				$order_by = "order by $sort_by $sort_order";
				break;
		}
		
		$filter = array();
		$filter[] = "dri.branch_id in (".join(',', $bid_list).")";
		$filter[] = "dri.status=3 and dri.active=1";
		$filter[] = "dri.added between ".ms($date_from)." and ".ms($date_to.' 23:59:59');
		
		$filter = "where ".join(' and ', $filter);
		$sql = "select dri.*,reject_u.u as reject_by_u,request_u.u as request_by_u,request_from_b.code as request_from_bcode, request_to_b.code as request_to_bcode,si.sku_item_code,si.mcode,si.artno,si.description
from do_request_items dri
left join sku_items si on si.id=dri.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
left join user reject_u on reject_u.id=dri.reject_by
left join user request_u on request_u.id=dri.user_id
left join branch request_from_b on request_from_b.id=dri.branch_id 
left join branch request_to_b on request_to_b.id=dri.request_branch_id
$filter $order_by";
		//print $sql;

		$q1 = $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc($q1)){
			$r['do_list'] = unserialize($r['do_list']);
			$this->data['item_list'][] = $r;
		}
		$con_multi->sql_freeresult($q1);
		
		//print_r($this->data);
		
		$smarty->assign('data', $this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".(count($bid_list) > 1 ? 'All' : get_branch_code($bid_list[0]));
		$report_title[] = "Request Date: $date_from to $date_to";
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
}

$DO_REQUEST_REJECTED_REPORT = new DO_REQUEST_REJECTED_REPORT('DO Request Rejected Report');
?>
