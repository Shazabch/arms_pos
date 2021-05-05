<?php
/*
3/24/2014 5:56 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*/

include("include/common.php");
$maintenance->check(130);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_TRADE_IN_WRITEOFF')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_TRADE_IN_WRITEOFF', BRANCH_CODE), "/index.php");
require_once('counter_collection.include.php');

class TRADE_IN_WRITEOFF extends Module{
	var $branch_id = 0;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;
		
		if(!$_REQUEST['skip_init_load'])    $this->init_load();
		
		if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
		
		$this->branch_id = mi($sessioninfo['branch_id']);
		
		parent::__construct($title);
	}
	
	function _default(){
     	if($_REQUEST['show_data']){
			$this->generate_data(); 
		}
        $this->display();
	}
	
	private function init_load(){
		global $con, $smarty;
		
	}
	
	private function generate_data(){
		global $con, $smarty, $sessioninfo, $config;
		
		$date = $_REQUEST['date'];
		$bid = $this->branch_id;
		
		// validate
		if(!strtotime($date))	$err[] = "Invalid Date.";
		if(!$bid || (BRANCH_CODE != 'HQ' && $sessioninfo['branch_id'] != $bid))	$err[] = "Invalid Branch.";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$allow_edit = 1;
		if(is_cc_finalized($bid, $date))	$allow_edit = 0;
		
		$report_title[] = "Branch: ".BRANCH_CODE;
		$report_title[] = "Date: ".$date;
		
		$filter = array();
		$filter[] = "pos.cancel_status=0";
		$filter[] = "pos.date=".ms($date);
		$filter[] = "pos.branch_id=".mi($bid);
		//$filter[] = "pi.sku_item_id=0";
		$filter[] = "pi.trade_in_by>0";
		
		$where = " where ".join(" and ",$filter);

	$sql="select cs.network_name, pos.branch_id, pos.date,pos.counter_id, pos.id as pos_id, pos.receipt_no, pi.id as pos_items_id, pi.barcode,pi.sku_item_id, pi.sku_description, 
		pi.qty,pi.price , tu.u as trade_in_by_u, si.sku_item_code, si.mcode, si.link_code, si.description,pi.writeoff_by, u_writeoff.u as writeoff_by_u, pi.writeoff_timestamp,pi.verify_code_by, u_verify.u as verify_code_by_u, pi.verify_timestamp,pi.trade_in_by,pi.more_info
		from pos_items pi 
		left join pos on pi.pos_id=pos.id and pi.counter_id=pos.counter_id and pi.branch_id=pos.branch_id and pi.date=pos.date
		left join sku_items si on si.id=pi.sku_item_id
		left join user tu on tu.id = pi.trade_in_by 
		left join user u_writeoff on u_writeoff.id = pi.writeoff_by
		left join user u_verify on u_verify.id = pi.verify_code_by
		left join counter_settings cs on cs.id = pos.counter_id and cs.branch_id=pos.branch_id
		$where
		order by cs.network_name, pos.id, pi.barcode";
		
		//print $sql;
		$q1 = $con->sql_query($sql);
		$this->data = array();
		while($r = $con->sql_fetchassoc($q1)){
			$r['more_info'] = unserialize($r['more_info']);
			
			//$this->data['by_items'][] = $r;
			if(!isset($this->data['counter_info'][$r['counter_id']])){
				$this->data['counter_info'][$r['counter_id']]['network_name'] = $r['network_name'];
				$this->data['counter_info'][$r['counter_id']]['branch_id'] = $r['branch_id'];
				$this->data['counter_info'][$r['counter_id']]['counter_id'] = $r['counter_id'];
			}
			$this->data['by_counter'][$r['counter_id']][] = $r;
		}
		$con->sql_freeresult($q1);
		
		//print_r($this->data);
		$smarty->assign('allow_edit', $allow_edit);
		$smarty->assign('data', $this->data);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	private function load_pi_row($date, $counter_id, $pos_id, $pos_item_id){
		global $con, $smarty, $sessioninfo, $config;
		
		$bid = $this->branch_id;
		if(!$bid || !$date || !$counter_id || !$pos_id || !$pos_item_id)	return array();
		
		$con->sql_query("select cs.network_name, pos.branch_id, pos.date,pos.counter_id, pos.id as pos_id, pos.receipt_no, pi.id as pos_items_id, pi.barcode,pi.sku_item_id, pi.sku_description, 
		pi.qty,pi.price , tu.u as trade_in_by_u, si.sku_item_code, si.mcode, si.link_code, si.description,pi.writeoff_by, u_writeoff.u as writeoff_by_u, pi.writeoff_timestamp,pi.verify_code_by, u_verify.u as verify_code_by_u, pi.verify_timestamp,pi.trade_in_by,pi.more_info
			from pos_items pi
			left join pos on pos.branch_id=pi.branch_id and pos.date=pi.date and pos.counter_id=pi.counter_id and pos.id=pi.pos_id
			left join sku_items si on si.id=pi.sku_item_id
			left join user tu on tu.id = pi.trade_in_by 
			left join user u_writeoff on u_writeoff.id = pi.writeoff_by
			left join user u_verify on u_verify.id = pi.verify_code_by
			left join counter_settings cs on cs.id = pos.counter_id and cs.branch_id=pos.branch_id
			where pi.branch_id=$bid and pi.date=".ms($date)." and pi.counter_id=$counter_id and pi.pos_id=$pos_id and pi.id=$pos_item_id");
		$pi = $con->sql_fetchassoc();
		$pi['more_info'] = unserialize($pi['more_info']); 
		$con->sql_freeresult();
		
		return $pi;
	}
	
	function ajax_set_writeoff(){
		global $con, $smarty, $sessioninfo, $config;
		
		//print_r($_REQUEST);	
		
		$date = $_REQUEST['date'];
		$bid = $this->branch_id;
		$counter_id = mi($_REQUEST['counter_id']);
		$pos_id = mi($_REQUEST['pos_id']);
		$pos_item_id = mi($_REQUEST['pos_item_id']);
		$set_writeoff = mi($_REQUEST['writeoff']);
		
		// validate
		$err = array();
		if(!strtotime($date))	$err[] = "Invalid Date.";
		if(!$bid || (BRANCH_CODE != 'HQ' && $sessioninfo['branch_id'] != $bid))	$err[] = "Invalid Branch.";
		if(!$counter_id)	$err[] = "Invalid Counter.";
		if(!$pos_id)	$err[] = "Invalid POS.";
		if(!$pos_item_id)	$err[] = "Invalid POS Items.";
		if(!$err && is_cc_finalized($bid, $date))	$err[] = "Counter Collection already finalised.";
		
		if(!$err){
			$pi = $this->load_pi_row($date, $counter_id, $pos_id, $pos_item_id);
			
			if(!$pi)	$err[] = "POS Items not found.";
			if($pi && !$pi['trade_in_by'])	$err[] = "The items in not Trade In item.";
			
			if(!$err){
				if($set_writeoff && $pi['writeoff_by'])	$err[] = "The items already write-off.";
				elseif(!$set_writeoff && !$pi['writeoff_by'])	$err[] = "The items is not yet write-off.";
			}
			
			if($pi['cancel_status'])	$err[] = "The POS already cancel.";
		}
		
		if($err){
			foreach($err as $e){
				print $e."\n";
			}
			return;
		}
		
		$upd = array();
		if($set_writeoff){
			$upd['writeoff_by'] = $sessioninfo['id'];
			$upd['writeoff_timestamp'] = 'CURRENT_TIMESTAMP';
			$upd['verify_code_by'] = '';
			$upd['verify_timestamp'] = 0;
			$upd['sku_item_id'] = 0;
		}else{
			$upd['writeoff_by'] = 0;
			$upd['writeoff_timestamp'] = 0;
		}
		
		$con->sql_query("update pos_items set ".mysql_update_by_field($upd)." where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id and pos_id=$pos_id and id=$pos_item_id limit 1");
		
		if($set_writeoff){
			$act = "Write-off SKU";
		}else{
			$act = "Undo Write-off SKU";
		}
		
		log_br($sessioninfo['id'], 'Trade In', "", "$act : Branch ID#$pi[branch_id], Date#$pi[date] Counter ID#$pi[counter_id], POS ID#$pi[pos_id], POS Item ID#$pi[pos_items_id]");
		
		$new_pi = $this->load_pi_row($date, $counter_id, $pos_id, $pos_item_id);
		$smarty->assign('allow_edit', 1);
		$smarty->assign('pi', $new_pi);
		$ret['html'] = $smarty->fetch('pos.trade_in.write_off.row.tpl');
		$ret['ok'] = 1;
		
		print json_encode($ret);
	}
}

$TRADE_IN_WRITEOFF = new TRADE_IN_WRITEOFF('Manage Trade In Write-Off');
?>
