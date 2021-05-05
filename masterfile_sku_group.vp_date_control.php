<?php
/*
12/14/2012 3:41 PM Justin
- Bug fixed on system allow overlap for "date from".

1/15/2012 2:51 PM Andy
- Change item to sort from oldest to latest, the latest item at bottom.

1/24/2013 5:01 PM Justin
- Enhanced to change the minimum year of "date from" from 2010 to 2001.

2/26/2013 2:31 PM Fithri
- check only shared user and owner can edit
- log_br when save

3/4/2015 11:51 AM Andy
- Enhanced the log when user save the data, record what branch id, sku group id and row count.

3/30/2015 4:44 PM Andy
- Enhanced to have checking on user submit/server received data to prevent data loss.
*/

include("include/common.php");
if(is_ajax()){
	if (!$login) die($LANG['YOU_HAVE_LOGGED_OUT']);
}else{
	if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
}

if (!$config['enable_vendor_portal']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$maintenance->check(169);

class MASTERFILE_SKU_GROUP_VP_DATE_CONTROL extends Module{

	function __construct($title, $template=''){
		parent::__construct($title, $template);
	}
	
	function _default(){
		
		$this->display();
	}
	
	function open(){
		global $con, $smarty, $sessioninfo, $LANG;
		
		$sku_group_bid = mi($_REQUEST['sku_group_bid']);
		$sku_group_id = mi($_REQUEST['sku_group_id']);
		
		$con->sql_query("select code from sku_group where sku_group_id = $sku_group_id and branch_id = $sku_group_bid and (user_id=".mi($sessioninfo['id'])." or share_with like '%\"".mi($sessioninfo['id'])."\"%') ");
		if (!($code = $con->sql_fetchfield(0))) {
			js_redirect("You are not allowed to edit this SKU group",'/masterfile_sku_group.php');
			exit;
		}
		
		// get sku group header
		$con->sql_query("select * from sku_group where branch_id=$sku_group_bid and sku_group_id=$sku_group_id");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$form)	js_redirect("Invalid SKU Group", "/index.php");
		
		// get item list
		$q_item = $con->sql_query($sql = "select sgi.branch_id,sgi.sku_group_id,si.id as sid, si.mcode, si.artno, si.sku_item_code,si.description
		from sku_group_item sgi
		join sku_items si using(sku_item_code)
		where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id
		order by sgi.added_timestamp");
		while($r = $con->sql_fetchassoc($q_item)){
			$form['item_list'][$r['sid']] = $r;
		}
		$con->sql_freeresult($q_item);
		
		if($form['item_list']){
			// get item list date control
			$q2 = $con->sql_query("select sgdc.* 
			from sku_group_vp_date_control sgdc
			where sgdc.branch_id=$sku_group_bid and sgdc.sku_group_id=$sku_group_id
			order by sgdc.from_date");
			while($r = $con->sql_fetchassoc($q2)){
				if(!$form['item_list'][$r['sku_item_id']])	continue;
				
				$form['item_list'][$r['sku_item_id']]['date_control'][] = $r;
			}
			$con->sql_freeresult($q2);
		}
		
		//print_r($form);
		
		$smarty->assign('form', $form);
		$smarty->display('masterfile_sku_group.vp_date_control.open.tpl');
	}
	
	function initial_generate_all_sku_group_active_date(){
		global $con;
		
		$row_added = 0;
		
		$q1 = $con->sql_query("select sgi.*, si.id as sid
from sku_group_item sgi
join sku_items si on si.sku_item_code=sgi.sku_item_code
order by branch_id, sku_group_id");
		while($r = $con->sql_fetchassoc($q1)){
			$q2 = $con->sql_query("select from_date from sku_group_vp_date_control where branch_id=".mi($r['branch_id'])." and sku_group_id=".mi($r['sku_group_id'])." and sku_item_id=".mi($r['sid']));
			$tmp = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			
			if($tmp)	continue;
			
			$upd = array();
			$upd['branch_id'] = $r['branch_id'];
			$upd['sku_group_id'] = $r['sku_group_id'];
			$upd['sku_item_id'] = $r['sid'];
			$upd['from_date'] = '2000-01-01';
			$upd['to_date'] = '2099-12-31';
			$con->sql_query("replace into sku_group_vp_date_control ".mysql_insert_by_field($upd));
			$row_added++;
		}
		$con->sql_freeresult($q1);
		
		print "$row_added row added.";
	}
	
	function ajax_update_vp_date_control(){
		global $con, $smarty, $sessioninfo;
		
		//print_r($_REQUEST);exit;
		
		$form = $_REQUEST;
		$sku_group_bid = mi($form['sku_group_bid']);
		$sku_group_id = mi($form['sku_group_id']);
		
		$data = array();
		
		// construct array and check for error
		$q1 = $con->sql_query("select sgi.*, si.id as sid, si.sku_item_code
		from sku_group_item sgi
		join sku_items si on si.sku_item_code=sgi.sku_item_code
		where sgi.branch_id=$sku_group_bid and sgi.sku_group_id=$sku_group_id");
		
		$date_item_count = 0;
		while($r = $con->sql_fetchassoc($q1)){
			$sid = $r['sid'];
			
			$data[$sid]['info']['sku_item_code'] = $r['sku_item_code'];
			
			if(!isset($data[$sid]['date_list']))	$data[$sid]['date_list'] = array();
			
			if(!$form['from_date'][$sid])	continue;
			
			foreach($form['from_date'][$sid] as $row_no => $from_date){
				$time_from_date = strtotime($from_date);
				$time_to_date = strtotime($form['to_date'][$sid][$row_no]);
				
				// wrong date format
				if(!$err && date("Y", $time_from_date)<2000)	$err[] = "Error on SKU (".$data[$sid]['info']['sku_item_code']."): Date From is invalid, cannot less than 2000-01-01";
				if(!$err && date("Y", $time_to_date)<2000)	$err[] = "Error on SKU (".$data[$sid]['info']['sku_item_code']."): Date To is invalid, cannot less than 2000-01-01";
				if(!$err && $time_to_date < $time_from_date)	$err[] = "Error on SKU (".$data[$sid]['info']['sku_item_code']."): Date To cannot earlier then Date From.";

				if($err){
					break;
				}
				$from_date = date("Y-m-d", $time_from_date);
				$to_date = date("Y-m-d", $time_to_date);

				$tmp = array();
				$tmp['from_date'] = $from_date;
				$tmp['to_date'] = $to_date;
				$data[$sid]['date_list'][] = $tmp;
				
				$date_item_count++;
			}		
			
			if(!$err && $data[$sid]['date_list']){
				// sort by from date
				usort($data[$sid]['date_list'], array($this, "sort_active_date_list"));
				$last_from = $last_to = 0;
				
				// check date range overlap
				foreach($data[$sid]['date_list'] as $r){
					if($last_from && $last_to){
						if((strtotime($r['to_date']) <= $last_to) || (strtotime($r['from_date']) >= $last_from && strtotime($r['from_date']) <= $last_to)){
							$err[] = "Error on SKU (".$data[$sid]['info']['sku_item_code']."): Date range cannot overlap.";
							break;
						}
					}
					$last_from = strtotime($r['from_date']);
					$last_to  = strtotime($r['to_date']);
				}
			}
			
			if($err)	break;
		}
		$con->sql_freeresult($q1);
		
		// check item different
		if(!$err && $form['date_item_count'] != $date_item_count){
			$err[] = "Item count different: Item in browser: ".mi($form['date_item_count']).", Item Received at server: ".mi($date_item_count);
		}
		
		// stop if found error
		if($err){
			print join("\n", $err);
			exit;
		}
		
		//print_r($data);
		$row_count = 0;
		
		if($data){
			// loop for each sku
			foreach($data as $sid => $si_info){
				$sid = mi($sid);
				
				if(!$sid)	continue;
				
				// delete first
				$con->sql_query("delete from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid");
				
				if($si_info['date_list']){
					// insert each date range
					foreach($si_info['date_list'] as $r){
						$r['branch_id'] = $sku_group_bid;
						$r['sku_group_id'] = $sku_group_id;
						$r['sku_item_id'] = $sid;
						
						$con->sql_query("replace into sku_group_vp_date_control ".mysql_insert_by_field($r));
						$row_count ++;
					}
				}
			}
		}
		log_br($sessioninfo['id'], 'SKU GROUP DATE', $sku_group_id, "Edit SKU Group date control (Group ID:$sku_group_id Branch ID:$sku_group_bid), Date row count submit: ".mi($form['date_item_count'])." Date row count received: $row_count, ");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	private function sort_active_date_list($a, $b){
		$from1 = strtotime($a['from_date']);
		$from2 = strtotime($b['from_date']);
		
		if($from1 == $from2)	return 0;
		return $from1 > $from2 ? 1 : 0;
	}
}

$MASTERFILE_SKU_GROUP_VP_DATE_CONTROL = new MASTERFILE_SKU_GROUP_VP_DATE_CONTROL('Vendor Portal SKU Group Item Date Control');

?>
