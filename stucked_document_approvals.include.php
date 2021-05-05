<?php
/*
10/4/2016 10:56 AM Andy
- Fixed eform sql error.
*/
class STUCKED_DOCUMENT_APPROVALS extends Module{

	function __construct($title, $is_notification = false) {
		$this->init_selection();
		
		if ($is_notification) {
			$this->is_notification = true;
		}
		else {
			parent::__construct($title); //not for use in dashboard, proceed like usual
		}
	}
	
	function _default() {
		global $smarty;
		$smarty->assign("data", $this->get_approval_data());
		$this->display();
	}
	
	function reload_table() {
		global $smarty;
		$smarty->assign("data", $this->get_approval_data());
		$this->display('stucked_document_approvals.table.tpl');
	}
	
	function get_notification_data() {
		$notification = array();
		$data = $this->get_approval_data();
		foreach ($data as $d) {
			$notification[$d['name']]['count'] = isset($notification[$d['name']]) ? ++$notification[$d['name']]['count'] : 1;
			$notification[$d['name']]['desc'] = $d['module'];
		}
		return $notification;
	}
	
	function init_selection() {
		
		global $config, $sessioninfo, $smarty;
		
		$bid = mi($sessioninfo['branch_id']);
		$branch_filter = ($bid == 1) ? '1' : "t.branch_id = $bid";
		$sku_branch_filter = ($bid == 1) ? '1' : "t.apply_branch_id = $bid";
		
		if ($_REQUEST['m']) $this->type_filter = $_REQUEST['m'];
		if ($_REQUEST['a'] == 'reload_table') {
			if ($_REQUEST['type']) $this->type_filter = $_REQUEST['type'];
		}
		$this->checked_users = array();
		
		//////////// list of modules
		$this->modules = array(
			'adjustment' => array(
				'label' => 'Adjustment',
				'url' => 'adjustment.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'adjustment_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'do' => array(
				'label' => 'Delivery Order',
				'url' => 'do.php?a=view&id=%s&branch_id=%s&do_type=%s',
				'approval_url' => 'do_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			/*'eform_data' => array(
				'label' => 'E-Form',
				'url' => 'eform.php?a=open&did=%s&branch_id=%s',
				'approval_url' => '',
			),*/
			'future_price' => array(
				'label' => 'Batch Price Change',
				'url' => 'masterfile_sku_items.future_price.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'masterfile_sku_items.future_price_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'gra' => array(
				'label' => 'GRA',
				'url' => 'goods_return_advice.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'goods_return_advice.approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'grn' => array(
				'label' => 'GRN',
				'url' => 'goods_receiving_note.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'goods_receiving_note_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'membership_redemption' => array(
				'label' => 'Membership Redemption',
				'url' => 'membership.redemption_history.php?a=view&t=2&id=%s&branch_id=%s',
				'approval_url' => 'membership.redemption_history.php?a=view&t=1&do_verify=1&branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'po' => array(
				'label' => 'Purchase Order',
				'url' => 'po.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'po_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'promotion' => array(
				'label' => 'Promotion',
				'url' => 'promotion.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'promotion_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'purchase_agreement' => array(
				'label' => 'Purchase Agreement',
				'url' => 'po.po_agreement.setup.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'po.po_agreement.approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'sales_order' => array(
				'label' => 'Sales Order',
				'url' => 'sales_order.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'sales_order_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
			'sku' => array(
				'label' => 'SKU Application',
				'url' => 'masterfile_sku_application.php?a=view&id=%s',
				'approval_url' => 'masterfile_sku_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			),
		);
		
		if ($config['consignment_modules']) {
			$this->modules['ci'] = array(
				'label' => 'Consignment Invoice',
				'url' => 'consignment_invoice.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'consignment_invoice_approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			);
			$this->modules['cn'] = array(
				'label' => 'Credit Note',
				'url' => 'consignment.credit_note.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'consignment.credit_note.approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			);
			$this->modules['dn'] = array(
				'label' => 'Debit Note',
				'url' => 'consignment.debit_note.php?a=view&id=%s&branch_id=%s',
				'approval_url' => 'consignment.debit_note.approval.php?branch_id=%s&on_behalf_of=%s&on_behalf_by=%s&id=%s',
			);
		}
		//////////// list of modules
		
		ksort($this->modules);
		$smarty->assign('modules',$this->modules);
		
		//////////// list of SQLs
		$sqls = array();
		$sqls['adjustment'] = "select 'adjustment' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from adjustment t
		left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
		where $branch_filter and t.approved=0 and t.status = 1";


		if ($config['consignment_modules']) {
			
			$sqls['ci'] = "select 'ci' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
			from ci t
			left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
			where $branch_filter and t.approved=0 and t.status = 1";


			$sqls['cn'] = "select 'cn' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
			from cn t
			left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
			where $branch_filter and t.approved=0 and t.status = 1";


			$sqls['dn'] = "select 'dn' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
			from dn t
			left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
			where $branch_filter and t.approved=0 and t.status = 1";
			
		}


		$sqls['do'] = "select 'do' as m, t.id, t.branch_id, t.added, t.do_type, t.user_id, bah.approvals, bah.approval_order_id
		from do t
		left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
		where $branch_filter and t.approved=0 and t.status = 1 and t.active=1";


		if (!$config['consignment_modules']) {
			/*$sqls['eform_data'] = "select 'eform_data' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
			from eform_data t
			left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
			where $branch_filter and t.active=1 and t.status = 1 and t.approved=0";*/
		}


		$sqls['sku_items_future_price'] = "select 'future_price' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from sku_items_future_price t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
		where $branch_filter and t.active=1 and t.status = 1 and t.approved = 0";


		$sqls['gra'] = "select 'gra' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from gra t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id 
		where $branch_filter and t.returned = 0 and t.status = 2 and t.approved=0";


		$sqls['grn'] = "select 'grn' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from grn t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
		where $branch_filter and t.approved=0 and t.status = 1 and t.active = 1";


		$sqls['membership_redemption'] = "select 'membership_redemption' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from membership_redemption t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
		where $branch_filter and t.verified = 0 and t.status = 0";


		$sqls['po'] = "select 'po' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from po t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
		where $branch_filter and t.approved=0 and t.status = 1";


		$sqls['promotion'] = "select 'promotion' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from promotion t
		left join branch_approval_history bah on t.approval_history_id = bah.id and t.branch_id = bah.branch_id
		where $branch_filter and t.approved=0 and t.status = 1";


		$sqls['purchase_agreement'] = "select 'purchase_agreement' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from purchase_agreement t
		left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
		where $branch_filter and t.approved=0 and t.status = 1 and t.active=1";


		$sqls['sales_order'] = "select 'sales_order' as m, t.id, t.branch_id, t.added, t.user_id, bah.approvals, bah.approval_order_id
		from sales_order t
		left join branch_approval_history bah on t.approval_history_id = bah.id and bah.branch_id=t.branch_id
		where $branch_filter and t.approved=0 and t.status = 1";


		$sqls['sku'] = "select 'sku' as m, t.id, t.added, t.apply_by as user_id, ah.approvals, ah.approval_order_id
		from sku t
		left join approval_history ah on t.approval_history_id = ah.id
		where $sku_branch_filter and t.active = 0 and t.status <> 2";
		//////////// list of SQLs
		
		$this->sqls = $sqls;
		
	}
	
	function get_approval_data() {
		
		global $con, $sessioninfo;
		
		$data = array();
		$prefixes = $this->get_report_prefix();
		
		$approval_list = array();
		foreach ($this->sqls as $k => $sql) {
			
			if ($this->type_filter) {
				if ($k != $this->type_filter) continue;
			}
			
			$q1 = $con->sql_query($sql);
			//print $sql.'<br /><br />';
			
			while ($r1 = $con->sql_fetchassoc($q1)) {
				$r1['name'] = $k;
				if ($r1['approvals']) $approval_list[] = $r1;
			}
		}
		
		foreach ($approval_list as $r) {

			$is_stucked = false;
			$users = preg_split("/\|/", $r['approvals'], -1, PREG_SPLIT_NO_EMPTY);
			
			if ($users) {
				foreach ($users as $u) {
					if (!isset($this->checked_users[$u])) {
						$this->checked_users[$u] = $this->get_user($u);
					}
				}
				
				$inactive_user_list = array();
				
				if ($r['approval_order_id'] == 1) { //seq
					if ($this->checked_users[$users[0]]['active'] == '0') {
						foreach ($users as $u) {
							if ($this->checked_users[$u]['active'] == '0') $inactive_user_list[] = $u;
							else break;
						}
						$is_stucked = true;
					}
				}
				if ($r['approval_order_id'] == 2) { //all
					foreach ($users as $u) {
						if ($this->checked_users[$u]['active'] == '0') {
							$is_stucked = true;
							$inactive_user_list[] = $u;
						}
					}
				}
				if ($r['approval_order_id'] == 3) { //any
					$inactive_approvals = 0; //counter, to use for "anyone" type of approval
					foreach ($users as $u) {
						if ($this->checked_users[$u]['active'] == '0') {
							$inactive_user_list[] = $u;
							$inactive_approvals++;
						}
					}
					if ($inactive_approvals >= count($users)) {
						$is_stucked = true;
					}
				}
				
				if ($is_stucked) {
					
					$row = array();
					$mod = $r['m'];
					$doc_id = mi($r['id']);
					$branch_id = mi($r['branch_id']);
					$uid = mi($r['user_id']);
					$inactive_user = join('-',$inactive_user_list);
					
					switch ($mod) {
						case 'do':
						$url = sprintf($this->modules[$mod]['url'],$doc_id,$branch_id,$r['do_type']);
						break;
						
						case 'sku':
						$url = sprintf($this->modules[$mod]['url'],$doc_id);
						break;
						
						default:
						$url = sprintf($this->modules[$mod]['url'],$doc_id,$branch_id);
					}
					
					$row['name'] = $r['name'];
					$row['view_url'] = $url;
					$row['approval_url'] = sprintf($this->modules[$mod]['approval_url'],$branch_id,$inactive_user,$sessioninfo['id'],$doc_id);
					$row['module'] = strtoupper($this->modules[$mod]['label']);
					$row['doc_no'] = $prefixes[$branch_id].sprintf("%05d",$doc_id);
					$row['added'] = $r['added'];
					if (!$this->checked_users[$uid]) $this->checked_users[$uid] = $this->get_user($uid);
					$row['user'] = $this->checked_users[$uid]['u'];
					$row['approval_order_id'] = $r['approval_order_id'];
					$row['approvals'] = $this->approvals_id_to_string($r['approvals'],$r['approval_order_id']);
					$data[] = $row;
				}
			}
		}
		
		return $data;
	}
	
	function get_user($user_id) {
		global $con;
		$user_id = mi($user_id);
		$q5 = $con->sql_query("select u, active from user where id = $user_id limit 1"); //make everybody inactive, for testing
		return $con->sql_fetchassoc($q5);
	}
	
	function approvals_id_to_string($ids, $approval_order_id) {
		
		$z = array();
		$x = explode('|',trim($ids,'|'));
		foreach ($x as $y) {
		
			if (!isset($this->checked_users[$y])) {
				$this->checked_users[$y] = $this->get_user($y);
			}
		
			if ($this->checked_users[$y]['active'] == 0) {
				$z[] = '<span style="color:#CE0000"><b><strike>'.$this->checked_users[$y]['u'].'</strike></b></span>';
			}
			else {
				$z[] = '<b>'.$this->checked_users[$y]['u'].'</b>';
			}
		}
		
		switch ($approval_order_id) {
			case 1: $sep = '>'; break;
			case 2: $sep = '&'; break;
			case 3: $sep = '|'; break;
			default: $sep = '';
		}
		
		return join(" $sep ",$z);
	}
	
	function get_report_prefix() {
		global $con;
		$b = array();
		$q6 = $con->sql_query("select id, report_prefix from branch where active = 1");
		while ($r6 = $con->sql_fetchassoc($q6)) {
			$b[mi($r6['id'])] = $r6['report_prefix'];
		}
		return $b;
	}
	
}
