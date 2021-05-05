<?
/*
4/22/2009 12:47:41 PM yinsee
Tracker #20 : promotion approval flow control multiple approver 

12/3/2009 2:52 PM Jeff
- fix promotion terminated but still active

12/10/2009 10:03:05 AM Andy
- hide terminate make promotion become in-active, add update last_update

4/21/2010 1:23:54 PM Andy
- Fix promotion approval cannot see control type bugs

12/21/2010 6:03:18 PM Andy
- Add new promotion type (mix_and_match), default promotion type will be 'discount'.

1/3/2011 11:55:49 AM Alex
- add loading department from consignment_bearing

4/5/2011 4:58:31 PM Andy
- Move load_promo_header() and load_promo_items() to promotion.include, so both promotion.php and promotion.approval.php will call the same functions instead of own coding.

6/24/2011 5:20:20 PM Andy
- Make all branch default sort by sequence, code.

7/13/2011 5:00:00 PM Alex
- add trigger for load_consignment_bearning_dept() function

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/25/2013 5:04 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

5/26/2014 2:16 PM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PROMOTION_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POPROMOTION_APPROVAL', BRANCH_CODE), "/index.php");

include('promotion.include.php');

class PromotionApproval extends Module
{
	var $branch_id, $promo_id, $approval_status;
	var $control_type = array('No Control','Limit by day','Limit by period');
	
	function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty;		
		
		$this->approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

		$smarty->assign("PAGE_TITLE", "Promotion Approval");
		$smarty->assign("sessioninfo", $sessioninfo);

		$this->branch_id = intval($_REQUEST['branch_id']);
		if ($this->branch_id ==''){
			$this->branch_id = $sessioninfo['branch_id'];
		}

		$this->promo_id = mi($_REQUEST['id']);		
		$smarty->assign("control_type", $this->control_type);
		
		load_consignment_bearning_dept($this->branch_id, false);
		
		if ($_REQUEST['on_behalf_of'] && $_REQUEST['on_behalf_by']) {
			$con->sql_query("select group_concat(u separator ', ') as u from user where id in (".str_replace('-',',',$_REQUEST['on_behalf_of']).")");
			$on_behalf_of_u = $con->sql_fetchfield(0);
			$con->sql_query("select u from user where id = ".mi($_REQUEST['on_behalf_by'])." limit 1");
			$on_behalf_by_u = $con->sql_fetchfield(0);
			$approval_on_behalf = array(
				'on_behalf_of' => str_replace('-',',',$_REQUEST['on_behalf_of']),
				'on_behalf_by' => mi($_REQUEST['on_behalf_by']),
				'on_behalf_of_u' => $on_behalf_of_u,
				'on_behalf_by_u' => $on_behalf_by_u,
			);
		}
		else {
			$approval_on_behalf = false;
		}
		$smarty->assign('approval_on_behalf', $approval_on_behalf);
		$this->approval_on_behalf = $approval_on_behalf;
		
		parent::__construct($title, $template='');
	}


	
	function _default()
	{
		$this->do_approval_all();
		$this->display('promotion.approval.tpl');	
	}
	
	/*function load_promo_header()
	{
		global $con,$sessioninfo;
		$promo_id = $this->promo_id;
		$branch_id = $this->branch_id;
		
		$con->sql_query("select promotion.*, bah.approvals from promotion 
						left join branch_approval_history bah on bah.id=promotion.approval_history_id and bah.branch_id=promotion.branch_id
						where promotion.id = ".mi($promo_id)." and promotion.branch_id = ".mi($branch_id));
		$form = $con->sql_fetchrow();
		$form['promo_branch_id'] = unserialize($form['promo_branch_id']);
		
		if ($sessioninfo['level']>=9999)	// superuser approve and final
		{
			$form['is_approval'] = 1;
			$form['last_approver'] = 1;
		}
		else
		{
			if (preg_match("/\|$sessioninfo[id]\|/", $form['approvals']))
				$form['is_approval'] = 1;
			if (preg_match("/\|\d+\|$/", $form['approvals']))
				$form['last_approver'] = 1;
		} 
		
		return $form;
		
	}*/
	
	/*function load_promo_items($use_tmp=false)
	{
		global $con, $sessioninfo, $smarty;
	
	    //consignment bearing
		$con->sql_query("select distinct c.id,c.description from consignment_bearing_items cbi
		                left join consignment_bearing cb on cbi.consignment_bearing_id=cb.id and cbi.branch_id=cb.branch_id
		                left join category c on cb.dept_id=c.id
						where cb.branch_id=$this->branch_id
						order by c.description");
		$smarty->assign("departments", $con->sql_fetchrowset());

		$owner_filter='';	
	
		if($use_tmp){
			$table="tmp_promotion_items";
			$owner_filter=" and user_id=$sessioninfo[id] ";
		}
		else{
			$table="promotion_items";
		}

		$q1=$con->sql_query("select tpi.*, si.mcode, si.sku_item_code, if(sp.price is null, si.selling_price, sp.price) as selling_price, si.description, sc.grn_cost, sc.qty from $table tpi 
		left join sku_items si on sku_item_id = si.id
		left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = ".$this->branch_id."
		left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = tpi.branch_id
		where tpi.promo_id=".$this->promo_id." and tpi.branch_id=".$this->branch_id." $owner_filter 
		order by tpi.id") or die(mysql_error());
		$promo_items = $con->sql_fetchrowset();
		$smarty->assign("promo_item_count", count($promo_items));
		$smarty->assign("promotion_items",$promo_items);
		
		
		$con->sql_query("select * from promotion where branch_id = ".mi($this->branch_id)." and id = ".mi($this->promo_id));
		
		$promo = $con->sql_fetchrow();

		if ($promo_items)
		{
			foreach($promo_items as $pi)
			{
				$samepromos = $this->find_overlap_promo($promo,$pi['sku_item_id']);
				if ($samepromos)
				{
					foreach($samepromos as $r2)
					{
						$ditems[$pi['id']][] = $r2;
					}
				}
			}
		}
		$smarty->assign("ditems",$ditems);
		
		return $promo_items;
	}*/
	
	function ajax_load_promotion()
	{
		global $con, $smarty, $branch_id;
		$is_mix_and_match = false;
		
		$form=array();	
		$id=$this->promo_id;
		
		// check promotion type
		$con->sql_query("select promo_type from promotion where branch_id=".mi($this->branch_id)." and id=".mi($id));
		$promo_type = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		//$form=$this->load_promo_header();
		if($promo_type=='mix_and_match'){
		    // re-load mix n match data
			$form = load_mix_n_match_header($this->branch_id, $id);
			$is_mix_and_match = true;
		}else{
			$form = load_discount_promo_header($this->branch_id, $id);
		}
		$con->sql_query("select u from user where id=".mi($form['user_id']));
		$r=$con->sql_fetchrow();
		$form['u']=$r[0];
	
	
	    $con->sql_query("select id, code from branch where active=1 order by sequence,code");
		while($r=$con->sql_fetchrow())
		{
			$branch[] = $r;
			$branches[$r['id']] = $r;
		}

		$smarty->assign('branch', $branch);
		$smarty->assign('branches', $branches);
		
		$form['approval_screen']=1;
		
		if($is_mix_and_match){
            $items = load_mix_n_match_items_list($this->branch_id, $id);
			$smarty->assign('form', $form);
			$smarty->assign('items', $items);
			$smarty->display("promotion.mix_n_match.open.tpl");
		}else{
            //$this->load_promo_items();
            load_discount_promo_items($this->branch_id, $this->promo_id);
			$smarty->assign('form', $form);
			$smarty->assign("readonly", 1);
			$smarty->display("promotion.new.tpl");
		}
	}
	
	/*function find_overlap_promo($promo, $sku_item_id)
	{
		global $con;
		
		if (BRANCH_CODE != 'HQ') 
			$sql = " p.promo_branch_id like '%\"".BRANCH_CODE."\"%' ";
		else
			$sql = 1;
		
		$con->sql_query("select pi.*, p.status, p.approved, p.title, p.date_from, p.date_to, p.time_from, p.time_to, if(sp.price is null, si.selling_price, sp.price) as selling_price, sc.grn_cost, sc.qty from promotion_items pi 
			left join promotion p on p.id = pi.promo_id and p.branch_id = pi.branch_id 
			left join sku_items si on pi.sku_item_id = si.id 
			left join sku_items_price sp on si.id = sp.sku_item_id and sp.branch_id = pi.branch_id
			left join sku_items_cost sc on sc.sku_item_id = si.id and sc.branch_id = pi.branch_id 
			where $sql and (".ms($promo['date_from'])." between date_from and date_to or 
			".ms($promo['date_to'])." between date_from and date_to or date_from between ".ms($promo['date_from'])." and ".ms($promo['date_to']).") and 
			pi.sku_item_id = ".mi($sku_item_id)." and p.id <> ".mi($promo['id'])." and p.status <> 5 and p.status <> 4");
		return $con->sql_fetchrowset();
	}*/
	
	function reject_approval()
	{
		$this->save_approval();
	}
	
	function terminate_approval()
	{
		$this->save_approval();
	}

	function save_approval(){


		/*
		parse_str($_REQUEST['rejected_item_data'],$rejected_item_data);
		print '<pre>';
		print_r($_REQUEST);
		print_r($rejected_item_data);
		print '</pre>';
		die();
		*/
		
		global $con, $sessioninfo, $LANG, $approval_status, $branch_id, $config;
		 // save approval status (1 = approve, 2 = rejected. 3 = KIV, 4 = Terminate)
		$form=$_REQUEST;
	
		if ($form['a']=='terminate_approval')
			$approve=4;
		elseif ($form['a']=='reject_approval')
			$approve=2;
		elseif ($form['a']=='kiv_approval')
			$approve=3;
		else
			$approve=1;

		// save approval status
		$sz=$form['approve_comment'];
		
		if ($this->approval_on_behalf) {
			$sz .= " (by ".$this->approval_on_behalf['on_behalf_by_u']." on behalf of ".$this->approval_on_behalf['on_behalf_of_u'].")";
		}
		$sz = ms($sz);
		
		$aid=intval($form['approval_history_id']);
		$promoid=intval($form['id']);
		$branch_id = $this->branch_id;

		if($aid > 0)
		{
			/*if ($sessioninfo['level']<9999) // superadmin can approve anything
			{ 
				$con->sql_query("select approvals from branch_approval_history where id=$aid and branch_id=$branch_id");
				if ($app=$con->sql_fetchrow()){
					if (!strstr($app[0], "|$sessioninfo[id]|")){
						print "<script>alert('".sprintf($LANG['PROMO_NOT_APPROVAL'], $promoid)."');</script>\n";
						$this->_default();
						exit;
					}
				}
			}*/

      if($approve==1){  // approve
        $params = array();
    		$params['approve'] = 1;
    		$params['user_id'] = $sessioninfo['id'];
    		$params['id'] = $aid;
    		$params['branch_id'] = $branch_id;
    		$params['update_approval_flow'] = true;
        $is_last = check_is_last_approval_by_id($params, $con);
        if($is_last)  $approved = 1; 	
      }
      elseif($approve == 4){
            //$set_active = ", active = 0";
	    }
      
      
      if($is_last)  $set_approve = ", approved = 1";
        
			$con->sql_query("update promotion set status=$approve $set_active $set_approve , last_update=now() where id=$promoid and branch_id=$branch_id");

			//$con->sql_query("update promotion set status=$approve where id=$promoid and branch_id=$branch_id");

			log_br($sessioninfo['id'], 'PROMOTION', $promoid, "Promotion Approval (ID#$promoid, Status: $approval_status[$approve])");

			// if this is not KIV.
			if ($approve!=3){
				$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $approve, $sz)");

				$con->sql_query("select flow_approvals, approvals, promotion.user_id, notify_users from branch_approval_history left join promotion on branch_approval_history.ref_id = promotion.id and branch_approval_history.branch_id = promotion.branch_id where branch_approval_history.id = $aid and branch_approval_history.branch_id = $branch_id");
				$r = $con->sql_fetchrow();

				$recipients = $r[3];
				$po_owner = $r[2];
				$flow_approvals = $r[0];
					
		       	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
		       	$to = preg_split("/\|/", $recipients);
				
        if($approve!=1){
          $con->sql_query("update branch_approval_history set status = $approve where id = $aid and branch_id = $branch_id");
        }				
				
				// send pm
				//$to[] = $po_owner;
				$to = get_pm_recipient_list2($promoid,$aid,$approve,'approval',$branch_id,'promotion');
				$status_str = ($is_last || $approve != 1) ? $this->approval_status[$approve] : '';
				send_pm2($to, "Promotion Approval (ID#$promoid) $status_str", "promotion.php?a=view&id=$promoid&branch_id=$branch_id", array('module_name'=>'promotion'));
			}
		}
		
		//process rejected items here, only when clicked approve
		if (($approve == 1) && $config['promotion_approval_allow_reject_by_items']) {
		
			$con->sql_query("select promo_type from promotion where branch_id=$branch_id and id=$promoid limit 1");
			$promo_type = $con->sql_fetchfield(0);
			$con->sql_freeresult();
			$is_mix_and_match = ($promo_type == 'mix_and_match') ? true:false;
			//var_dump($is_mix_and_match);
			
			parse_str($_REQUEST['rejected_item_data'],$rejected_item_data);
			
			if ($is_mix_and_match) {
				
				if ($rejected_item_data['rejected_group_id']) {
				
					$cols = array();
					$s1 = $con->sql_query($abc='desc promotion');//print "$abc<br />";
					while ($r = $con->sql_fetchassoc($s1)) {
						if ($r['Field'] == 'id') continue;
						$cols[] = $r['Field'];
					}
					
					$field_list = join(',',$cols);
					$con->sql_query($abc="insert into promotion ($field_list) select $field_list from promotion where id = $promoid and branch_id = $branch_id");//print "$abc<br />";
					$new_promo_id = $con->sql_nextid();
					$con->sql_query($abc="update promotion set approved=0, status=0, approval_history_id=NULL, added=CURRENT_TIMESTAMP, last_update=CURRENT_TIMESTAMP where id = $new_promo_id and branch_id = $branch_id limit 1");//print "$abc<br />";
					
					$group_id_list = array_keys($rejected_item_data['rejected_group_id']);
					$new_gid = 1;
					$tmp_reason_data = array();
					foreach ($group_id_list as $gid) {
						$gid = mi($gid);
						$con->sql_query("update promotion_mix_n_match_items set promo_id = $new_promo_id, group_id = $new_gid where branch_id = $branch_id and promo_id = $promoid and group_id = $gid");
						$tmp_reason_data[$new_promo_id][$new_gid] = $rejected_item_data['rejected_group_reason'][$gid];
						$new_gid++;
					}
					
					//record the reject reason
					$s6 = $con->sql_query("select id, group_id, extra_info from promotion_mix_n_match_items where branch_id = $branch_id and promo_id = $new_promo_id");
					while ($r = $con->sql_fetchassoc($s6)) {
						if ($r) $ext_info = unserialize($r['extra_info']);
						$ext_info['reject_reason'] = $tmp_reason_data[$new_promo_id][$r['group_id']];
						$con->sql_query("update promotion_mix_n_match_items set extra_info = ".ms(serialize($ext_info))." where id = ".mi($r['id'])." and branch_id = $branch_id limit 1");
					}
					
					//need to re-arrange group order on the original promotion
					$gr = array();
					$s5 = $con->sql_query("select distinct group_id from promotion_mix_n_match_items where branch_id = $branch_id and promo_id = $promoid order by group_id");
					while ($r = $con->sql_fetchassoc($s5)) {
						$gr[] = mi($r['group_id']);
					}
					foreach ($gr as $new_seq => $old_seq) {
						$con->sql_query("update promotion_mix_n_match_items set group_id = $new_seq+1 where branch_id = $branch_id and promo_id = $promoid and group_id = $old_seq");
					}
				}
			}
			else {
				//take out rejected items & put'em in a new promo
				if ($rejected_item_data['rejected_item_id']) {
					
					$cols = array();
					$s1 = $con->sql_query($abc='desc promotion');//print "$abc<br />";
					while ($r = $con->sql_fetchassoc($s1)) {
						if ($r['Field'] == 'id') continue;
						$cols[] = $r['Field'];
					}
					
					$field_list = join(',',$cols);
					$con->sql_query($abc="insert into promotion ($field_list) select $field_list from promotion where id = $promoid and branch_id = $branch_id");//print "$abc<br />";
					$new_promo_id = $con->sql_nextid();
					$item_id_list = join(',',array_keys($rejected_item_data['rejected_item_id']));
					$con->sql_query($abc="update promotion_items set promo_id = $new_promo_id where id in ($item_id_list) and branch_id = $branch_id");//print "$abc<br />";
					
					foreach ($rejected_item_data['rejected_item_id'] as $row_id => $dummy) {
					
						$s2 = $con->sql_query("select extra_info from promotion_items where id = ".mi($row_id)." and branch_id = $branch_id limit 1");
						$r = $con->sql_fetchassoc($s2);
						if ($r) $ext_info = unserialize($r['extra_info']);
						$ext_info['reject_reason'] = $rejected_item_data['rejected_item_reason'][$row_id];
						$con->sql_query("update promotion_items set extra_info = ".ms(serialize($ext_info))." where id = ".mi($row_id)." and branch_id = $branch_id limit 1");
					}
					
					$con->sql_query($abc="update promotion set approved=0, status=0, approval_history_id=NULL, added=CURRENT_TIMESTAMP, last_update=CURRENT_TIMESTAMP where id = $new_promo_id and branch_id = $branch_id limit 1");//print "$abc<br />";
					
					/*
					//and send out email to the owner
					$s3 = $con->sql_query("select user_id, title, date_from, date_to from promotion where id = $promoid and branch_id = $branch_id limit 1");
					$r = $con->sql_fetchassoc($s3);
					$email_body = "The following item(s) has been rejected from <b>Promotion #$promoid - ".$r['title']."</b> ( ".$r['date_from']." - ".$r['date_to']." ) : <br /><br />";
					$owner_id = mi($r['user_id']);
					
					$email_body .= '<ul>';
					$s4 = $con->sql_query("select si.sku_item_code, si.description, pi.extra_info from promotion_items pi left join sku_items si on pi.sku_item_id = si.id where pi.promo_id = $new_promo_id and branch_id = $branch_id");
					while ($r = $con->sql_fetchassoc($s4)) {
						$r['extra_info'] = unserialize($r['extra_info']);
						$email_body .= '<li>'.$r['sku_item_code'].' - '.$r['description'].' <span style="color:red;"><b>( '.$r['extra_info']['reject_reason'].' )</b></span></li>';
					}
					$email_body .= '</ul><br /><br />';
					
					$email_body .= "The rejected item(s) has been placed under a new promotion ID <b>(Promotion#$new_promo_id)</b> with same details.<br /><br />Thank You!";
					
					include_once("include/class.phpmailer.php");
					$mailer = new PHPMailer(true);
					$mailer->FromName = "ARMS Notification";
					$mailer->Subject = "Rejected item(s) for Promotion#$promoid";
					$mailer->IsHTML(true);
					$email_address = get_user_info_by_colname($owner_id, "email");
					if($mailer->ValidateAddress($email_address)){
						$mailer->AddAddress($email_address);
						$mailer->Body = $email_body;
						$send_success = phpmailer_send($mailer, $mailer_info);
						$mailer->ClearAddresses();
					}
					*/
				}
			}
			
			if ($new_promo_id) {
			
				// and send out pm to the owner
				$s7 = $con->sql_query("select user_id from promotion where id = $promoid and branch_id = $branch_id limit 1");
				$r = $con->sql_fetchassoc($s7);
				$owner_id = mi($r['user_id']);
				
				$pm = array();
				$pm['branch_id'] = $sessioninfo['branch_id'];
				$pm['from_user_id'] = $sessioninfo['id'];
				$pm['to_user_id'] = $owner_id;
				$pm['msg'] = "Rejected Promotion Items (ID#$promoid)";
				$pm['url'] = ($is_mix_and_match) ? 'promotion.mix_n_match.php':'promotion.php';
				$pm['url'] .= "?a=open&id=$new_promo_id&branch_id=$branch_id";
				$pm['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into pm ".mysql_insert_by_field($pm));
			}
			
		}

		print "<script>alert('Promotion ".$this->approval_status[$approve]."');</script>\n";
		
		if ($this->approval_on_behalf) {
			header("Location: /stucked_document_approvals.php?m=promotion");
			exit;
		}
		
		$this->_default();
	}

	function do_approval_all(){
		global $smarty, $LANG, $sessioninfo, $con;
	
		/*if ($sessioninfo['level']<9999)    
			$usercheck = "approvals like '%|$sessioninfo[id]|%' and";
	   	$con->sql_query("select promotion.date_from, promotion.date_to, promotion.time_from, promotion.time_to, promotion.branch_id, promotion.id, promotion.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name 
	from promotion 
	left join branch_approval_history on promotion.approval_history_id = branch_approval_history.id and promotion.branch_id=branch_approval_history.branch_id 
	left join user on user.id=promotion.user_id
	left join branch on promotion.branch_id = branch.id 
	where promotion.branch_id = ".mi($sessioninfo['branch_id'])." and $usercheck promotion.status = 1 and promotion.approved=0 order by promotion.last_update");*/
	
		if ($this->approval_on_behalf) {
			$u = explode(',',$this->approval_on_behalf['on_behalf_of']);
			$search_approval = $u[0];
			$doc_filter = ' and promotion.id = '.mi($_REQUEST['id']).' and promotion.branch_id = '.mi($_REQUEST['branch_id']).' ';
		}
		else $search_approval = $sessioninfo['id'];
		
	
      $con->sql_query("select promotion.date_from, promotion.date_to, promotion.time_from, promotion.time_to, promotion.branch_id, promotion.id, promotion.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name 
	from promotion 
	left join branch_approval_history on promotion.approval_history_id = branch_approval_history.id and promotion.branch_id=branch_approval_history.branch_id 
	left join user on user.id=promotion.user_id
	left join branch on promotion.branch_id = branch.id 
	where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and promotion.branch_id = ".mi($sessioninfo['branch_id'])." and promotion.status = 1 and promotion.approved=0 $doc_filter order by promotion.last_update");

	   	$smarty->assign("promotion", $con->sql_fetchrowset());
	}
	
}

$promotion_approval = new PromotionApproval ('Promotion Approval');
?>
