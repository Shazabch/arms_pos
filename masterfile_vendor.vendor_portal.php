<?php
/*
10/3/2012 4:39 PM Andy
- Change sales report profit to by "Date To".
- Add Bonus % based on year and month.

11/15/2012 12:03 PM Andy
- Add Report Profit other % (category and sku).
- Add Bonus other % (category only).

12/4/2012 11:08 AM Andy
- Add "Start Date".

12/17/2012 4:50 PM Andy
- Add can copy/paste report profit other %.
- Add can copy/paste repot profit whole table.
- Add checking to duplicated branch profit other %.
- Add can copy/paste bonus other %.
- Add can copy/paste bonus table.
- Add checking to duplicated branch bonus other %.
*/
include("include/common.php");
$maintenance->check(171);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$config['enable_vendor_portal']) js_redirect($LANG['NEED_CONFIG'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

class MASTERFILE_VENDOR_PORTAL extends Module{
	var $si_info = array();
	var $cat_info = array();
	
	function __construct($title, $template=''){
		parent::__construct($title, $template);
	}
	
	function _default(){
		$this->load_vendor_portal_info();
		
		$this->display();
	}
	
	private function load_vendor_portal_info(){
		global $con, $smarty, $sessioninfo, $config;
		
		$vid = mi($_REQUEST['vid']);
	
		if(!$vid)	$err[] = "Invalid Vendor ID";
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$con->sql_query("select v.id, v.code, v.description, v.active_vendor_portal, vpi.*
		from vendor v 
		left join vendor_portal_info vpi on vpi.vendor_id=v.id
		where v.id=$vid");
		$form = $con->sql_fetchassoc();
		$form['allowed_branches'] = unserialize($form['allowed_branches']);
		$form['sku_group_info'] = unserialize($form['sku_group_info']);
		$form['sales_report_profit'] = unserialize($form['sales_report_profit']);
		
		$con->sql_freeresult();
		
		if(!$form)	$err("Vendor information not found.");
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		// load branches list
		$branches_list = array();
		$con->sql_query("select id,code from branch where active=1 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$branches_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		// load sku group list
		$sku_group_list = array();
		$con->sql_query("select * from sku_group order by code,description");
		while($r = $con->sql_fetchassoc()){
			$sku_group_list[] = $r;
		}
		$con->sql_freeresult();
		
		// load debtor list
		$debtor_list = array();
		$con->sql_query("select id,code,description from debtor where active=1 order by code");
		while($r = $con->sql_fetchassoc()){
			$debtor_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		$cat_id_list = $sid_list = array();
		// get info by branch
		$form['branch_info'] = array();
		$con->sql_query("select * from vendor_portal_branch_info where vendor_id=$vid");
		while($r = $con->sql_fetchassoc()){
			$r['sales_report_profit_by_date'] = unserialize($r['sales_report_profit_by_date']);
			$r['sales_bonus_by_step'] = unserialize($r['sales_bonus_by_step']);
			
			if(!$r['sales_report_profit_by_date'])	$r['sales_report_profit_by_date'] = array();
			else{
				// store sid or cat_id for later to sql get their info
				foreach($r['sales_report_profit_by_date'] as $tmp_bid => $sales_report_profit_by_date_info){
					if($sales_report_profit_by_date_info['profit_per_by_type']){
						foreach($sales_report_profit_by_date_info['profit_per_by_type'] as $tmp_r){
							$v = mi($tmp_r['value']);
							if(!$v)	continue;
							
							if($tmp_r['type'] == 'SKU'){
								if(!in_array($v, $sid_list))	$sid_list[] = $v;
							}else{
								if(!in_array($v, $cat_id_list))	$cat_id_list[] = $v;
							}
						}
					}
				}
			}
			
			if(!$r['sales_bonus_by_step'])	$r['sales_bonus_by_step'] = array();
			else{
				// store sid or cat_id for later to sql get their info
				foreach($r['sales_bonus_by_step'] as $tmp_y => $tmp_m_list){
					foreach($tmp_m_list as $m => $tmp_row_list){
						foreach($tmp_row_list as $tmp_row_no => $tmp_row_info){
							if($tmp_row_info['bonus_per_by_type']){
								foreach($tmp_row_info['bonus_per_by_type'] as $tmp_type_row_no => $tmp_type_row_info){
									$v = mi($tmp_type_row_info['value']);
									if(!$v)	continue;
									
									if($tmp_type_row_info['type'] == 'SKU'){
										if(!in_array($v, $sid_list))	$sid_list[] = $v;
									}else{
										if(!in_array($v, $cat_id_list))	$cat_id_list[] = $v;
									}
								}
							}
						}
					}
				}
			}
			
			$form['branch_info'][$r['branch_id']] = $r;
		}
		$con->sql_freeresult();
		
		if($sid_list)	$this->get_si_info($sid_list);
		if($cat_id_list)	$this->get_cat_info($cat_id_list);
		
		//print_r($form);
		
		$smarty->assign('branches_list', $branches_list);
		$smarty->assign('sku_group_list', $sku_group_list);
		$smarty->assign('debtor_list', $debtor_list);
		$smarty->assign('si_info', $this->si_info);
		$smarty->assign('cat_info', $this->cat_info);
		$smarty->assign('form', $form);
	}
	
	function ajax_update_vendor_portal(){
		global $con, $smarty, $sessioninfo, $config, $LANG;
		
		//print_r($_REQUEST);
		//exit;
		
		$vid = mi($_REQUEST['vendor_id']);
		$active_vendor_portal = mi($_REQUEST['active_vendor_portal']);
		$start_date = $_REQUEST['start_date'];
		$allowed_branches = $_REQUEST['allowed_branches'];
		//$use_last_grn = mi($_REQUEST['use_last_grn']);
		$login_ticket = $_REQUEST['login_ticket'];
		$expire_date = $_REQUEST['expire_date'];
		$no_expire = $_REQUEST['no_expire'];
		$sku_group_info = $_REQUEST['sku_group_info'];
		$sales_report_profit = $_REQUEST['sales_report_profit'];
		$link_debtor_id = $_REQUEST['link_debtor_id'];
		$contact_email = $_REQUEST['contact_email'];
		$sales_report_profit_by_date = $_REQUEST['sales_report_profit_by_date'];
		$sales_bonus_by_step = $_REQUEST['sales_bonus_by_step'];
		
		$con->sql_query("select v.id, v.code, v.description, v.active_vendor_portal, vpi.vendor_id as vpi_vid, vpi.*
		from vendor v 
		left join vendor_portal_info vpi on vpi.vendor_id=v.id
		where v.id=$vid");
		$form = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$form)	die("Vendor information not found.");
		
		$upd = array();
		$upd['active_vendor_portal'] = $active_vendor_portal;
		$con->sql_query("update vendor set ".mysql_update_by_field($upd)." where id=$vid");
		
		// check duplicate login ticket
		foreach($login_ticket as $tmp_bid => $ticket){
			if($ticket){	// got ticket
				$con->sql_query("select vendor_id from vendor_portal_branch_info where branch_id=".mi($tmp_bid)." and login_ticket=".ms($ticket)." and vendor_id<>$vid");
				$dup_ticket = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($dup_ticket){
					die("Invalid ticket, Ticket $ticket already use for other vendor in same branch. please regenerate new ticket number.");
				}
			}
			
			// check email
			$email_list_str = trim($contact_email[$tmp_bid]);
			if($email_list_str){
				$email_list = explode(",", $email_list_str);
				foreach($email_list as $tmp_email){
					if (!preg_match(EMAIL_REGEX, trim($tmp_email))){
						die("Invalid email format ($tmp_email).");
					}
				}
			}
		}
		
		// check duplicate branch profit date to
		if($sales_report_profit_by_date){
			foreach($sales_report_profit_by_date as $tmp_bid => $tmp_bp_list){
				$profit_date_to_list = array();
				foreach($tmp_bp_list as $row_no => $tmp_bp_info){
					if(in_array($tmp_bp_info['date_to'], $profit_date_to_list)){
						die("Report profit date ".$tmp_bp_info['date_to']." duplicated in branch ".get_branch_code($tmp_bid));
					}
					$profit_date_to_list[] = $tmp_bp_info['date_to'];
					
					if($tmp_bp_info['profit_per_by_type']){	// got more %
						// check more % duplicate
						$type_value_list = array();
						foreach($tmp_bp_info['profit_per_by_type'] as $tmp_type_row_no => $profit_per_by_type){
							if(!$type_value_list[$profit_per_by_type['type']])	$type_value_list[$profit_per_by_type['type']] = array();
							
							if(in_array($profit_per_by_type['value'], $type_value_list[$profit_per_by_type['type']])){
								die("Report profit date ".$tmp_bp_info['date_to']." other % (".$profit_per_by_type['type'].") duplicated in branch ".get_branch_code($tmp_bid));	
							}
							
							$type_value_list[$profit_per_by_type['type']][] = $profit_per_by_type['value'];
						}
					}
				}
			}
		}
		
		// check duplicate bonus amount and sort the array as well
		if($sales_bonus_by_step){
			foreach($sales_bonus_by_step as $tmp_bid => $tmp_bp_list){
				// at the same time sort array
				uksort($sales_bonus_by_step[$tmp_bid], array($this, "sort_report_bonus_y"));	// sort by year
	
				foreach($sales_bonus_by_step[$tmp_bid] as $y => $m_bonus_list){
					uksort($sales_bonus_by_step[$tmp_bid][$y], array($this, "sort_report_bonus_m"));	// sort by month
					
					foreach($sales_bonus_by_step[$tmp_bid][$y] as $m => $bonus_list){
						uasort($sales_bonus_by_step[$tmp_bid][$y][$m], array($this, "sort_report_bonus_amt"));	// sort by amt
						
						if($this->bonus_amt_duplicated)	die("Amount ".$this->bonus_amt_duplicated." duplicated in branch ".get_branch_code($tmp_bid)." Year $y Month $m");
						
						foreach($bonus_list as $tmp_row_no => $bonus_data){
							if($bonus_data['bonus_per_by_type']){
								// check duplicated other %
								$type_value_list = array();
								foreach($bonus_data['bonus_per_by_type'] as $tmp_type_row_no => $bonus_per_by_type){
									if(!$type_value_list[$bonus_per_by_type['type']])	$type_value_list[$bonus_per_by_type['type']] = array();
							
									if(in_array($bonus_per_by_type['value'], $type_value_list[$bonus_per_by_type['type']])){
										die("Report bonus amount ".$bonus_data['amt_from']." other % (".$bonus_per_by_type['type'].") duplicated in branch ".get_branch_code($tmp_bid));	
									}
									
									$type_value_list[$bonus_per_by_type['type']][] = $bonus_per_by_type['value'];
								}
							}
						}						
					}
				}
			}
		}
		
		$time = date("Y-m-d H:i:s");
		
		$upd = array();
		$upd['last_update'] = $time;
		$upd['last_update_by'] = $sessioninfo['id'];
		//$upd['login_ticket'] = $login_ticket;
		//$upd['use_last_grn'] = $use_last_grn;
		//$upd['expire_date'] = $no_expire ? '9999-12-31' : $expire_date;
		$upd['allowed_branches'] = serialize($allowed_branches);
		$upd['sku_group_info'] = serialize($sku_group_info);
		//$upd['sales_report_profit'] = serialize($sales_report_profit);
		//$upd['link_debtor_id'] = $link_debtor_id;
		$upd['start_date'] = $start_date;
		
		// vendor_portal_info
		if($form['vpi_vid']){	// already hv vendor_portal_info
			$con->sql_query("update vendor_portal_info set ".mysql_update_by_field($upd)." where vendor_id=$vid");
		}else{	// need add new
			$upd['vendor_id'] = $vid;
			$upd['added'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("insert into vendor_portal_info ".mysql_insert_by_field($upd));
		}
		
		// vendor_portal_branch_info
		foreach($sku_group_info as $tmp_bid => $tmp_sgi){
			// get info by branch
			$con->sql_query("select * from vendor_portal_branch_info where vendor_id=$vid and branch_id=".mi($tmp_bid));
			$vpbi = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			$upd = array();
			$upd['active'] = mi($allowed_branches[$tmp_bid]) ? 1 : 0;
			$upd['login_ticket'] = $login_ticket[$tmp_bid];
			$upd['expire_date'] = $no_expire[$tmp_bid] ? '9999-12-31' : $expire_date[$tmp_bid];
			$upd['link_debtor_id'] = $link_debtor_id[$tmp_bid];
			$upd['last_update'] = $time;
			$upd['contact_email'] = $contact_email[$tmp_bid];
			
			// sales report profit
			$upd['sales_report_profit_by_date'] = array();
			
			if(is_array($sales_report_profit_by_date[$tmp_bid]) && $sales_report_profit_by_date[$tmp_bid]){
				usort($sales_report_profit_by_date[$tmp_bid], array($this, "sort_report_profit"));
				$upd['sales_report_profit_by_date'] = $sales_report_profit_by_date[$tmp_bid];
			}
			
			$upd['sales_report_profit_by_date'] = serialize($upd['sales_report_profit_by_date']);
			
			// bonus
			$upd['sales_bonus_by_step'] = $sales_bonus_by_step[$tmp_bid];
			$upd['sales_bonus_by_step'] = serialize($upd['sales_bonus_by_step']);
			
			if(!$vpbi){
				$upd['added'] = $time;
				$upd['branch_id'] = $tmp_bid;
				$upd['vendor_id'] = $vid;
				
				$con->sql_query("insert into vendor_portal_branch_info ".mysql_insert_by_field($upd));
			}else{
				$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($upd)." where vendor_id=$vid and branch_id=".mi($tmp_bid));
			}
		}
		
		log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Vendor Portal Info Updated: Vendor ID#'.$form['id'].", Vendor Code#$form[code]");
		
		$ret = array();
		$ret['ok'] = 1;
		print json_encode($ret);
	}
	
	function setup_sales_report_profit_by_date(){
		global $con;
		
		$updated = 0;
		$q1 = $con->sql_query("select vendor_id, sales_report_profit from vendor_portal_info order by vendor_id");
		while($r = $con->sql_fetchassoc($q1)){
			$r['sales_report_profit'] = unserialize($r['sales_report_profit']);
			
			if(!$r['sales_report_profit'])	continue;
			
			foreach($r['sales_report_profit'] as $tmp_bid => $tmp_profit_per){
				if(!$tmp_profit_per)	continue;
				
				$con->sql_query("select sales_report_profit_by_date from vendor_portal_branch_info where vendor_id=".mi($r['vendor_id'])." and branch_id=".mi($tmp_bid));
				$vpbi = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				
				if(!$vpbi)	continue;
				
				$new_bp = array();
				$new_bp['date_to'] = '2020-12-31';
				$new_bp['profit_per'] = $tmp_profit_per;
				
				$row_updated = false;
				$vpbi['sales_report_profit_by_date'] = unserialize($vpbi['sales_report_profit_by_date']);
				if(!$vpbi['sales_report_profit_by_date'])	$vpbi['sales_report_profit_by_date'] = array();
				else{
					foreach($vpbi['sales_report_profit_by_date'] as $row_no=>$tmp_bp_info){
						if($tmp_bp_info['date_to'] == $new_bp['date_to']){
							$vpbi['sales_report_profit_by_date'][$row_no] = $new_bp;
							$row_updated = true;
							break;					
						}
					}
				}
				
				if(!$row_updated)	$vpbi['sales_report_profit_by_date'][] = $new_bp;
				
				usort($vpbi['sales_report_profit_by_date'], array($this, "sort_report_profit"));
				
				$vpbi['sales_report_profit_by_date'] = serialize($vpbi['sales_report_profit_by_date']);
				
				$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($vpbi)." where vendor_id=".mi($r['vendor_id'])." and branch_id=".mi($tmp_bid));
				$updated++;
			}
		}
		$con->sql_freeresult($q1);
		
		print "$updated Updated.";
	}
	
	function sort_report_profit($a, $b){
		$col = 'date_to';
		
		if($a[$col] == $b[$col])	return 0;
		
		return ($a[$col] > $b[$col]) ? 1 : 0;
	}
	
	function sort_report_bonus_y($a, $b){
		if($a == $b)	return 0;
		
		return $a > $b ? 1 : 0;
	}
	
	function sort_report_bonus_m($a, $b){
		if($a == $b)	return 0;
		
		return $a > $b ? 1 : 0;
	}
	
	function sort_report_bonus_amt($a, $b){
		$col = 'amt_from';
		
		if($a[$col] == $b[$col]){
			$this->bonus_amt_duplicated = $a[$col];
			return 0;
		}	
		
		return ($a[$col] > $b[$col]) ? 1 : 0;
	}
	
	function ajax_add_report_profit_breakdown_per(){
		global $con, $smarty;
		
		$bid = mi($_REQUEST['bid']);
		$row_no = mi($_REQUEST['row_no']);
		$type = trim($_REQUEST['type']);
		$v = mi($_REQUEST['value']);
		$type_row_no = mi($_REQUEST['new_type_row_no']);
		
		if($type == 'SKU')	$this->get_si_info(array($v));
		else	$this->get_cat_info(array($v));
		
		$profit_per_by_type = array();
		$profit_per_by_type['type'] = $type;
		$profit_per_by_type['value'] = $v;
		
		$smarty->assign('si_info', $this->si_info);
		$smarty->assign('cat_info', $this->cat_info);
		$smarty->assign('bid', $bid);
		$smarty->assign('row_no', $row_no);
		$smarty->assign('type_row_no', $type_row_no);
		$smarty->assign('profit_per_by_type', $profit_per_by_type);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_vendor.vendor_portal.branch_profit_row.breakdown_percent_row.tpl');
		
		print json_encode($ret);
	}
	
	function ajax_add_branch_bonus_breakdown_per(){
		global $con, $smarty;
		
		$bid = mi($_REQUEST['bid']);
		$y = mi($_REQUEST['y']);
		$m = mi($_REQUEST['m']);
		$row_no = mi($_REQUEST['row_no']);
		$type = trim($_REQUEST['type']);
		$v = mi($_REQUEST['value']);
		$type_row_no = mi($_REQUEST['new_type_row_no']);
		
		if($type == 'SKU')	$this->get_si_info(array($v));
		else	$this->get_cat_info(array($v));
		
		$bonus_per_by_type = array();
		$bonus_per_by_type['type'] = $type;
		$bonus_per_by_type['value'] = $v;
		
		$smarty->assign('si_info', $this->si_info);
		$smarty->assign('cat_info', $this->cat_info);
		$smarty->assign('bid', $bid);
		$smarty->assign('y', $y);
		$smarty->assign('m', $m);
		$smarty->assign('row_no', $row_no);
		$smarty->assign('type_row_no', $type_row_no);
		$smarty->assign('bonus_per_by_type', $bonus_per_by_type);
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('masterfile_vendor.vendor_portal.branch_bonus_row.breakdown_percent_row.tpl');
		
		print json_encode($ret);
	}
		
	private function get_si_info($sid_list = array()){
		global $con;
		
		if(!$sid_list || !is_array($sid_list))	return;
		
		$sid_list_to_query = array();
		// filter out those alrdy got data
		foreach($sid_list as $sid){
			if(!isset($this->si_info[$sid]))	$sid_list_to_query[] = $sid;
		}
		unset($sid_list);
		
		if(!$sid_list_to_query)	return;
		
		$con->sql_query("select si.id as sid, si.mcode, si.artno, si.sku_item_code, si.description from sku_items si where si.id in (".join(',', $sid_list_to_query).")");
		while($r = $con->sql_fetchassoc()){
			$this->si_info[$r['sid']] = $r;
		}
		$con->sql_freeresult();
	}
	
	private function get_cat_info($cat_id_list = array()){
		global $con;
		
		if(!$cat_id_list || !is_array($cat_id_list))	return;
		
		$cat_id_list_to_query = array();
		// filter out those alrdy got data
		foreach($cat_id_list as $cat_id){
			if(!isset($this->cat_info[$cat_id]))	$cat_id_list_to_query[] = $cat_id;
		}
		unset($cat_id_list);
		
		if(!$cat_id_list_to_query)	return;
		
		$con->sql_query("select c.id as cat_id, c.description from category c where c.id in (".join(',', $cat_id_list_to_query).")");
		while($r = $con->sql_fetchassoc()){
			$this->cat_info[$r['cat_id']] = $r;
		}
		$con->sql_freeresult();
	}
	
	function ajax_copy_report_profit_more_percent(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		$bid = $form['bid'];
		$new_type_row_no = $form['new_type_row_no'];
		$row_no = $form['row_no'];
		
		$html = '';
		
		$smarty->assign('bid', $bid);
		$smarty->assign('row_no', $row_no);
		
		if($form['sales_report_profit_by_date']){
			foreach($form['sales_report_profit_by_date'] as $tmp_bid => $row_list){
				foreach($row_list as $tmp_row_no => $profit_data){
					if($profit_data['profit_per_by_type']){
						foreach($profit_data['profit_per_by_type'] as $tmp_new_type_row_no => $profit_per_by_type){
							$smarty->assign('type_row_no', $new_type_row_no);
							$new_type_row_no++;
							
							$smarty->assign('profit_per_by_type', $profit_per_by_type);
							
							if($profit_per_by_type['type'] == 'SKU'){
								$this->get_si_info(array($profit_per_by_type['value']));
							}else{
								$this->get_cat_info(array($profit_per_by_type['value']));
							}
							
							$smarty->assign('si_info', $this->si_info);
							$smarty->assign('cat_info', $this->cat_info);
							
							
							//print_r($profit_data);
							$html .= $smarty->fetch('masterfile_vendor.vendor_portal.branch_profit_row.breakdown_percent_row.tpl');
						}	
					}				
				}
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $html;
		
		print json_encode($ret);
	}
	
	function ajax_copy_report_profit(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$bid = $form['bid'];
		$new_row_no = $form['new_row_no'];
		$html = '';
		
		$smarty->assign('bid', $bid);
		$row_no_list = array();
		
		if($form['sales_report_profit_by_date']){
			foreach($form['sales_report_profit_by_date'] as $tmp_bid => $row_list){
				foreach($row_list as $tmp_row_no => $profit_data){
					if($profit_data['profit_per_by_type']){
						foreach($profit_data['profit_per_by_type'] as $tmp_new_type_row_no => $profit_per_by_type){						
							if($profit_per_by_type['type'] == 'SKU'){
								$this->get_si_info(array($profit_per_by_type['value']));
							}else{
								$this->get_cat_info(array($profit_per_by_type['value']));
							}
						}
						
						$smarty->assign('si_info', $this->si_info);
						$smarty->assign('cat_info', $this->cat_info);
					}
										
					$row_no_list[] = $new_row_no;
					$smarty->assign('row_no', $new_row_no);
					$new_row_no++;
					
					$smarty->assign('profit_data', $profit_data);
					$html .= $smarty->fetch('masterfile_vendor.vendor_portal.branch_profit_row.tpl');
				}
				
				
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['row_no_list'] = $row_no_list;
		$ret['html'] = $html;
		
		print json_encode($ret);
	}
	
	function ajax_copy_branch_bonus_more_percent(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$bid = $form['bid'];
		$row_no = $form['row_no'];
		$y = $form['y'];
		$m = $form['m'];
		$new_type_row_no = $form['new_type_row_no'];
		
		$smarty->assign('bid', $bid);
		$smarty->assign('y', $y);
		$smarty->assign('m', $m);
		$smarty->assign('row_no', $row_no);
		
		$html = '';
		
		if($form['sales_bonus_by_step']){
			foreach($form['sales_bonus_by_step'] as $tmp_bid => $b_data_list){	// loop for each branch data
				foreach($b_data_list as $tmp_y => $m_bonus_list){	// loop for each year list
					foreach($m_bonus_list as $tmp_m => $bonus_data_list){	// loop for each month list
						foreach($bonus_data_list as $tmp_row_no => $bonus_data){	// loop for each row
							if($bonus_data['bonus_per_by_type']){	// got other %
								foreach($bonus_data['bonus_per_by_type'] as $tmp_new_type_row_no => $bonus_per_by_type){	// loop for each other % row
								
									if($bonus_per_by_type['type'] == 'SKU'){
										$this->get_si_info(array($bonus_per_by_type['value']));
									}else{
										$this->get_cat_info(array($bonus_per_by_type['value']));
									}
									
									$smarty->assign('si_info', $this->si_info);
									$smarty->assign('cat_info', $this->cat_info);
						
									$smarty->assign('bonus_per_by_type', $bonus_per_by_type);
									$smarty->assign('type_row_no', $new_type_row_no);
									$new_type_row_no++;
									
									$html .= $smarty->fetch('masterfile_vendor.vendor_portal.branch_bonus_row.breakdown_percent_row.tpl');
								}
							}
						}
					}
				}
			}			
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $html;
		
		print json_encode($ret);
	}
	
	function ajax_copy_branch_bonus(){
		global $con, $smarty, $config, $sessioninfo;
		
		$form = $_REQUEST;
		//print_r($form);
		
		$bid = $form['bid'];
		$new_row_no = $form['new_row_no'];
		$y = $form['y'];
		$m = $form['m'];
		
		$smarty->assign('bid', $bid);
		$smarty->assign('y', $y);
		$smarty->assign('m', $m);
		
		$html = '';
		
		if($form['sales_bonus_by_step']){
			foreach($form['sales_bonus_by_step'] as $tmp_bid => $b_data_list){	// loop for each branch data
				foreach($b_data_list as $tmp_y => $m_bonus_list){	// loop for each year list
					foreach($m_bonus_list as $tmp_m => $bonus_data_list){	// loop for each month list
						foreach($bonus_data_list as $tmp_row_no => $bonus_data){	// loop for each row
							if($bonus_data['bonus_per_by_type']){	// got other %
								foreach($bonus_data['bonus_per_by_type'] as $tmp_new_type_row_no => $bonus_per_by_type){	// loop for each other % row
								
									if($bonus_per_by_type['type'] == 'SKU'){
										$this->get_si_info(array($bonus_per_by_type['value']));
									}else{
										$this->get_cat_info(array($bonus_per_by_type['value']));
									}
								}
							}
							
							$smarty->assign('si_info', $this->si_info);
							$smarty->assign('cat_info', $this->cat_info);
							
							$smarty->assign('row_no', $new_row_no);
							$new_row_no++;
							
							$smarty->assign('bonus_data', $bonus_data);
							$html .= $smarty->fetch('masterfile_vendor.vendor_portal.branch_bonus_row.tpl');
						}
					}
				}
			}			
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $html;
		
		print json_encode($ret);
	}
}

$MASTERFILE_VENDOR_PORTAL = new MASTERFILE_VENDOR_PORTAL('Masterfile Vendor Portal');

?>
