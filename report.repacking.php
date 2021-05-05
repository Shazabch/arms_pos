<?php
include('include/common.php');
$maintenance->check(177);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_REPACKING')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_REPACKING', BRANCH_CODE), "/index.php");

class REPACKING_RPEORT extends Module {
	var $vendor_list = array();
	var $branch_list = array();
	var $status_list = array(
		'draft'=> 'Draft',
		'completed'=> 'Completed'
	);
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;
	
		// load branch list
		$con->sql_query("select id,code from branch order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);
		
		// get vendor list
		$con->sql_query("select v.id,v.code,v.description
from vendor v
join vendor_portal_info vpi on vpi.vendor_id=v.id
order by v.code");
		while($r = $con->sql_fetchassoc()){
			$this->vendor_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendor_list', $this->vendor_list);
				
		if(!isset($_REQUEST['date_from']) && !isset($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d");
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-7 day", strtotime($_REQUEST['date_to'])));
		}
		
		$smarty->assign('status_list', $this->status_list);
		
		parent::__construct($title);
	}
	
	function _default(){
	
		if($_REQUEST['show_report']){
			$this->generate_report();
		}
		$this->display();
	}
	
	private function generate_report(){
		global $con, $smarty, $sessioninfo, $config;
		
		$err = array();
		
		$bid = mi($_REQUEST['branch_id']);
		if(BRANCH_CODE != 'HQ'){
			$bid = $sessioninfo['branch_id'];
		}
		$vendor_id = mi($_REQUEST['vendor_id']);
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$status = trim($_REQUEST['status']);
		
		if(!$date_from)	$err[] = "Invalid Date From.";
		if(!$date_to)	$err[] = "Invalid Date To";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		$this->data = array();
		
		$filter = array();
		if($bid)	$filter[] = "rp.branch_id=$bid";
		if($vendor_id)	$filter[] = "rp.vendor_id=$vendor_id";
		$filter[] = "rp.repacking_date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "rp.active=1";
		
		switch($status){
			case 'draft':
				$filter[] = "rp.status=0 and rp.approved=0";
				break;
			case 'completed':
				$filter[] = "rp.status=1 and rp.approved=1";
				break;
		}
		
		$filter = 'where '.join(' and ', $filter);
		
		// get repacking
		$sql = "select rp.*, b.code as bcode, v.code as vcode,v.description as v_desc
from vp_repacking rp
join branch b on b.id=rp.branch_id
join vendor v on v.id=rp.vendor_id
$filter
order by rp.repacking_date desc";
		
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
			$rp_key = $r['branch_id'].'_'.$r['id'];
			
			$this->data['data']['rp_list'][$rp_key] = $r;
		}
		$con->sql_freeresult($q1);
		
		// get repacking lose item
		$sql = "select i.*, si.sku_item_code,si.mcode,si.description
		from vp_repacking_lose_items i
		join vp_repacking rp on rp.branch_id=i.branch_id and rp.id=i.repacking_id
		left join sku_items si on si.id=i.sku_item_id
		$filter";
		
		$q2 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q2)){
			$rp_key = $r['branch_id'].'_'.$r['repacking_id'];
			
			$this->data['data']['rp_list'][$rp_key]['group_list'][$r['group_id']]['lose_item_list'][] = $r;
		}
		$con->sql_freeresult($q2);
		
		// get repacking pack item
		$sql = "select i.*, si.sku_item_code,si.mcode,si.description
		from vp_repacking_pack_items i
		join vp_repacking rp on rp.branch_id=i.branch_id and rp.id=i.repacking_id
		left join sku_items si on si.id=i.sku_item_id
		$filter";
		
		//print $sql;
		
		$q2 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q2)){
			$rp_key = $r['branch_id'].'_'.$r['repacking_id'];
			
			$this->data['data']['rp_list'][$rp_key]['group_list'][$r['group_id']]['pack_item'] = $r;
		}
		$con->sql_freeresult($q2);
		
		if($this->data['data']['rp_list']){
			foreach($this->data['data']['rp_list'] as $rp_key => $rp){
				foreach($rp['group_list'] as $group_id => $rpg){
					// calculate total lose item cost
					$total_lose_item_cost = 0;
					
					if($rpg['lose_item_list']){
						foreach($rpg['lose_item_list'] as $r){
							$total_lose_item_cost += round($r['cost'] * $r['qty'], $config['global_cost_decimal_points']);
						}
					}
					$this->data['data']['rp_list'][$rp_key]['group_list'][$group_id]['total_lose_item_cost'] = $total_lose_item_cost;
					
					// total pack item cost
					$this->data['data']['rp_list'][$rp_key]['group_list'][$group_id]['total_pack_item_cost'] = $rpg['pack_item']['qty'] * $rpg['pack_item']['calc_cost'];
				}				
			}
		}
		//print_r($this->data);
		
		$report_title = array();
		$report_title[] = "Branch: ".($bid ? $this->branch_list[$bid]['code'] : 'All');
		$report_title[] = "Date From ".$date_from." to ".$date_to;
		$report_title[] = "Vendor: ".($vendor_id ? $this->vendor_list[$vendor_id]['code'] : 'All');
		$report_title[] = "Status: ".($status ? $this->status_list[$status] : 'All');
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		$smarty->assign('data', $this->data);
	}
}

$REPACKING_RPEORT = new REPACKING_RPEORT('Repacking Report');
?>
