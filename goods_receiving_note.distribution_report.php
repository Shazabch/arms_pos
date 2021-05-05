<?php
/*
6/24/2011 4:09:49 PM Andy
- Make all branch default sort by sequence, code.

9/8/2011 4:40:49 PM Andy
- Change report title to show GRR and GRN no.

4/23/2015 3:38 PM Andy
- Enhanced to show Deliver %.
- Enhanced to can view full status.
- Fix wrong DO qty when available qty is negative.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

5/21/2019 3:38 PM William
- Pickup report_prefix for enhance "GRN","GRR".
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$maintenance->check(69);

class GRN_DISTRIBUTION_REPORT extends Module{
	var $branches = array();
	var $branches_group = array();
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
        
		parent::__construct($title);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    
	    if($_REQUEST['load_report']){
			$this->generate_report();
			if(isset($_REQUEST['output_excel'])){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title." To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}
		
		
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty;
	    	
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group header
		$con->sql_query("select * from branch_group",false,false);
		while($r = $con->sql_fetchassoc()){
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult();

		if($this->branches_group){
            // load branch group items
			$con->sql_query("select bgi.*,branch.code,branch.description
			from branch_group_items bgi
			left join branch on bgi.branch_id=branch.id
			where branch.active=1
			order by branch.sequence, branch.code");
			while($r = $con->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
	}
	
	private function generate_report(){
	    global $con, $smarty, $sessioninfo;
				
		$doc_no = trim($_REQUEST['doc_no']);
		$grn_bid_id = trim($_REQUEST['grn_bid_id']);
		$report_title = $data = $total = array();
		
		list($bid, $grn_id) = explode("_", $grn_bid_id);

		$bid = mi($bid);
		$grn_id = mi($grn_id);
		
		if(!$bid || (BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id']))	$err[] = "Invalid GRN Branch";
		if(!$grn_id)	$err[] = "Invalid GRN ID";
		
		if(!$err){
			// load GRN
			$q_grn = $con->sql_query("select grr.rcv_date, grn.*,grri.doc_no,branch.report_prefix 
			from grn
			left join branch on grn.branch_id = branch.id
			left join grr_items grri on grri.branch_id=grn.branch_id and grri.id=grn.grr_item_id
			left join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
			where grn.branch_id=$bid and grn.id=$grn_id");
			$grn = $con->sql_fetchassoc($q_grn);
			$con->sql_freeresult($q_grn);
			$report_prefix = $grn['report_prefix'];
			if(!$grn)	$err[] = "GRN Branch ID: $bid, ID: $grn_id cannot be found.";
		}
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "Document# ".$report_prefix.sprintf("%05d", $grn['grr_id']).", ".$report_prefix.sprintf("%05d", $grn_id).", Doc No: ".$grn['doc_no'];
		$report_title[] = "Received Date: ".$grn['rcv_date'];
		
		// load GRN ITEMS
		$q_grni = $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, grn_items.sku_item_id, si.artno, si.sku_item_code, si.description
			from grn_items
			left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
			left join sku_items si on grn_items.sku_item_id = si.id
			where grn_items.branch_id=$bid and grn_items.grn_id=$grn_id
			group by grn_items.sku_item_id
			order by si.sku_item_code");
			$grn_items = array();
		$rcv_date = $grn['rcv_date'];
		$sid_list = array();
		while($r = $con->sql_fetchassoc($q_grni)){
			$sid = mi($r['sku_item_id']);
			$sid_list[] = $sid;
			$grn_items[$sid] = $r;
		}
		$con->sql_freeresult($_grni);
		
		if(!$sid_list){
			return false;	// no item in this GRN
		}
			
		// get do qty
		$q_di = $con->sql_query("select di.id, di.sku_item_id, ((di.ctn*uom.fraction)+di.pcs) as do_qty
from do_items di
left join do on do.branch_id=di.branch_id and do.id=di.do_id
left join uom on uom.id=di.uom_id
where do.branch_id=$bid and do.active=1 and do.status=1 and do.checkout=1 and do.do_date>=".ms($rcv_date)." and di.sku_item_id in (".join(',', $sid_list).")");
		$do_left_qty = array();
		while($di = $con->sql_fetchassoc($q_di)){
			$sid = mi($di['sku_item_id']);
			$left_qty = $grn_items[$sid]['qty'] - $grn_items[$sid]['do_qty'];
			//print "$sid, left_qty = $left_qty<br>";
			// it is due to other grn have taken some do_qty
			if(isset($do_left_qty[$di['id']]))	$di['do_qty'] = $do_left_qty[$di['id']];
			if(!$di['do_qty'])	continue;	// already used all qty to DO
			
			$do_qty = 0;
			if($di['do_qty'] < $left_qty)	$do_qty = $di['do_qty'];
			else	$do_qty = $left_qty;
			
			//print "$sid, do_qty = $do_qty<br>";
			$grn_items[$sid]['do_qty'] += $do_qty;
			$do_left_qty[$di['id']] = $di['do_qty'] - $do_qty;
		}
		$con->sql_freeresult($q_di);
		//print_r($grn_items);
		// get opening and balance
		$opening_date = date("Y-m-d", strtotime("-1 day", strtotime($rcv_date)));
		$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($opening_date));
		
		// check table exists
		$got_sb_tbl = $con->sql_query("explain $sb_tbl", false,false);
		$con->sql_freeresult();
		
		if($got_sb_tbl){
			foreach($grn_items as $sid=>$r){
			
				// get stock balance at opening
				$q_sb = $con->sql_query("select qty from $sb_tbl sb
				where sb.sku_item_id=".mi($sid)." and ".ms($opening_date)." between sb.from_date and sb.to_date limit 1");
				$sb = $con->sql_fetchassoc($q_sb);
				$con->sql_freeresult($q_sb);
				
				$grn_items[$sid]['opening_qty'] = $opening_qty = mf($sb['qty']);
				
				$available_qty = $r['qty'] + $opening_qty;
				
				// calculate closing balance
				if($r['do_qty'] > $available_qty && $available_qty > 0)	$grn_items[$sid]['do_qty'] = $available_qty;
				$grn_items[$sid]['balance_qty'] = $available_qty - $grn_items[$sid]['do_qty'];
				
				$grn_items[$sid]['deliver_per'] = $grn_items[$sid]['do_qty'] / $r['qty'] * 100;
				$total['opening_qty'] += $grn_items[$sid]['opening_qty'];
				$total['qty'] += $grn_items[$sid]['qty'];
				$total['do_qty'] += $grn_items[$sid]['do_qty'];
				$total['balance_qty'] += $grn_items[$sid]['balance_qty'];
				$total['deliver_per'] = $total['do_qty'] / $total['qty'] * 100;
			}
		}

		
		$data['grn'] = $grn;
		$data['grn_items'] = $grn_items;
		$data['total'] = $total;
		//print_r($data);
		$smarty->assign('data', $data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function view_status(){
		global $con, $sessioninfo, $smarty, $config;
		
		// GRN Distribution Status
		if (BRANCH_CODE == "HQ" && privilege('NT_GRN_DISTRIBUTE')){
			$q1 = $con->sql_query("select count(*) as ttl_branch from branch");
			$branch_count = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			// check if branch not more than 1, then do not show distribution
			if($branch_count['ttl_branch'] > 1){
				$grn_deliver_monitor = array();
				$monitor_after_day = mi($config['grn_distribution_monitor_after_day']);
				if(!$monitor_after_day)	$monitor_after_day = 3;
				$grn_deliver_monitor['info']['monitor_after_day'] = $monitor_after_day;

				$date_filter = date("Y-m-d", strtotime("-$monitor_after_day day"));

				$min_do_qty_percent = mi($config['grn_distribution_monitor_min_do_qty_percent']);
				if(!$min_do_qty_percent)	$min_do_qty_percent = 50;
				$grn_deliver_monitor['info']['min_do_qty_percent'] = $min_do_qty_percent;

				// get all GRN need to monitor
				$q_grn = $con->sql_query("select grr.rcv_date,grn.branch_id,grn.id, branch.report_prefix 
					from grn
					left join branch on grn.branch_id = branch.id
					left join grr_items gi on gi.branch_id=grn.branch_id and gi.id=grn.grr_item_id
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					where grn.branch_id=1 and grn.need_monitor_deliver=1 and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date<".ms($date_filter)." order by rcv_date desc");
				// loop for each GRN
				$do_left_qty = array();				
				while($grn = $con->sql_fetchassoc($q_grn)){
					$q_grni = $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty, grn_items.sku_item_id
					from grn_items
					left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
					left join sku_items on grn_items.sku_item_id = sku_items.id
					where grn_items.branch_id = ".mi($grn['branch_id'])." and grn_items.grn_id=".mi($grn['id'])."
					group by grn_items.sku_item_id
					");
					// loop for grn items
					$total_grn_qty = 0;
					$sid_list = array();
					$grn_items = array();
					while($grni = $con->sql_fetchassoc($q_grni)){
						$sid = mi($grni['sku_item_id']);
						$total_grn_qty += $grni['qty'];
						$sid_list[] = $sid;
						$grn_items[$sid] = $grni;
					}
					$con->sql_freeresult($q_grni);

					if(!$sid_list || !$total_grn_qty){
						$con->sql_query("update grn set need_monitor_deliver=0 where branch_id=".mi($grn['branch_id'])." and id=".mi($grn['id']));
						continue;
					}

					$q_di = $con->sql_query("select di.id, di.sku_item_id, ((di.ctn*uom.fraction)+di.pcs) as do_qty
		from do_items di
		left join do on do.branch_id=di.branch_id and do.id=di.do_id
		left join uom on uom.id=di.uom_id
		where do.branch_id=".mi($grn['branch_id'])." and do.active=1 and do.status=1 and do.approved=1 and do.checkout=1 and do.do_date>=".ms($grn['rcv_date'])." and di.sku_item_id in (".join(',', $sid_list).")");
					$total_do_qty = 0;
					while($di = $con->sql_fetchassoc($q_di)){
						$sid = mi($di['sku_item_id']);
						$left_qty = $grn_items[$sid]['qty'] - $grn_items[$sid]['do_qty'];

						// it is due to other grn have taken some do_qty
						if(isset($do_left_qty[$di['id']]))	$di['do_qty'] = $do_left_qty[$di['id']];
						if(!$di['do_qty'])	continue;	// already used all qty to DO

						$do_qty = 0;
						if($di['do_qty'] < $left_qty)	$do_qty = $di['do_qty'];
						else	$do_qty = $left_qty;

						$grn_items[$sid]['do_qty'] += $do_qty;
						$do_left_qty[$di['id']] = $di['do_qty'] - $do_qty;
						$total_do_qty += $do_qty;
					}
					$con->sql_freeresult($q_di);
					$grn['items'] = $grn_items;
					$grn['do_per'] = mi(($total_do_qty / $total_grn_qty) * 100);

					if($grn['do_per'] >= $min_do_qty_percent){	// already qualified percent
						// mark as no need to monitor anymore
						$con->sql_query("update grn set need_monitor_deliver=0 where branch_id=".mi($grn['branch_id'])." and id=".mi($grn['id']));
					}else{
						$grn_deliver_monitor['grn'][] = $grn;
					}
				}
				$con->sql_freeresult($q_grn);
				//print_r($do_left_qty);
				//print_r($grn_deliver_monitor);
				$smarty->assign('grn_deliver_monitor', $grn_deliver_monitor);
			}
		}
		$smarty->display('goods_receiving_note.distribution_report.view_status.tpl');
	}
}

$GRN_DISTRIBUTION_REPORT = new GRN_DISTRIBUTION_REPORT('GRN Distribution Report');
?>
