<?php

include("../../include/common.php");
$maintenance->check(212);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_CUSTOM1')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_CUSTOM1', BRANCH_CODE), "/index.php");

class TRADE_OFFER_REPORT extends Module{
	var $branch_list = array();
	
	function __construct($title, $template=''){
		global $con, $sessioninfo, $smarty;
		
		$this->branch_list = array();
		$con->sql_query("select * from branch order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branch_list', $this->branch_list);
		
		parent::__construct($title, $template);
	}
	
	 function _default(){
	 	global $con, $sessioninfo, $smarty;
	 	
	 	// load available offer
	 	$this->load_available_trade_offer();
	 	
	 	if($_REQUEST['show_report']){
	 		if($_REQUEST['export_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
			
	 		$this->generate_report();
	 	}
	 	
	 	$this->display('gpm/report.broadcast.trade_offer.tpl');
	 }
	 
	 private function load_available_trade_offer(){
	 	global $con, $sessioninfo, $smarty;
	 	
	 	// load available offer
	 	$offer_list = array();
	 	$con->sql_query("select * from gpm_broadcast_trade_offer where active=1 and status=1 order by date_to desc");
	 	while($r = $con->sql_fetchassoc()){
	 		$r['allowed_branch'] = unserialize($r['allowed_branch']);
	 		
	 		if(BRANCH_CODE != 'HQ'){
	 			if(!$r['allowed_branch'][$sessioninfo['branch_id']])	continue;	// only hq can view all
	 		}
	 		$offer_list[$r['id']] = $r;
	 	}
	 	$con->sql_freeresult();
	 	//print_r($offer_list);
	 	$smarty->assign('offer_list', $offer_list);
	 }
	 
	 private function generate_report(){
	 	global $con, $sessioninfo, $smarty;
	 	
	 	$offer_id = mi($_REQUEST['offer_id']);
	 	
	 	$err = array();
	 	
	 	if(!$offer_id)	$err[] = "Please select Trade Offer.";
	 
	 	if(!$err){
	 		// get header
 		 	$con->sql_query("select * from gpm_broadcast_trade_offer where id=$offer_id");
 		 	$form = $con->sql_fetchassoc();
 		 	$con->sql_freeresult();
 		 	
 		 	if($form){
 		 		$form['allowed_branch'] = unserialize($form['allowed_branch']);
 		 	}else	$err[] = "Invalid Trade Offer ID#$offer_id";
	 	}	
	 	if($err){
	 		$smarty->assign('err', $err);
	 		return false;
	 	}
	 	
	 	// get items
	 	$con->sql_query("select btoi.sku_item_id, si.sku_item_code, si.mcode, si.description
from gpm_broadcast_trade_offer_items btoi 
left join sku_items si on si.id=btoi.sku_item_id
where btoi.gpm_trade_offer_id=$offer_id order by mcode");
	 	while($r = $con->sql_fetchassoc()){
	 		$form['item_list'][$r['sku_item_id']] = $r;
	 	}
	 	$con->sql_freeresult();
	 	
	 	if($form['allowed_branch']){
	 		foreach($form['allowed_branch'] as $bid){	// loop each branch
	 			if(BRANCH_CODE != 'HQ' && $bid != $sessioninfo['branch_id'])	continue;
	 			
	 			// get summary by each branch
			 	$con->sql_query("select * from gpm_broadcast_trade_offer_summary where branch_id=$bid and gpm_trade_offer_id=$offer_id");
			 	while($r = $con->sql_fetchassoc()){
			 		$form['summary'][$bid] = $r;
			 	}
			 	$con->sql_freeresult();
			 	
			 	if($form['summary'][$bid]){
			 		// get summary items
				 	$con->sql_query("select sku_item_id, sum(qty) as qty
from gpm_broadcast_trade_offer_summary_items 
where branch_id=$bid and gpm_trade_offer_id=$offer_id
group by sku_item_id
order by qty desc");
					while($r = $con->sql_fetchassoc()){
				 		$form['summary'][$bid]['items'][] = $r;
				 	}
				 	$con->sql_freeresult();
			 	}
	 		}
	 	}
	 	
		//print_r($form);
		$smarty->assign('form', $form);
	 }
}

$TRADE_OFFER_REPORT = new TRADE_OFFER_REPORT('Broadcast Trade Offer Report');
?>
