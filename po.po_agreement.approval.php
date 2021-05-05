<?php
/*
9/13/2012 5:28 PM Andy
- Fix some wrong wording.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/8/2013 4:44 PM Justin
- Bug fixed on approval status message.

7/31/2013 3:07 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_AGREEMENT_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_AGREEMENT_APPROVAL', BRANCH_CODE), "/index.php");

include('po.include.php');

class PURCHASE_AGREEMENT_APPROVAL extends Module{
	var $branch_id, $pa_id, $approval_status;
	
	function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty;		
		
		$this->approval_status = array(1 => "Approved", 2 => "Rejected", 4 => "Terminated");


		$this->branch_id = intval($_REQUEST['branch_id']);
		if ($this->branch_id ==''){
			$this->branch_id = $sessioninfo['branch_id'];
		}

		$this->pa_id = mi($_REQUEST['id']);		
		
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
		$this->show_approval_all();
		$this->display();	
	}
	
	function show_approval_all(){
		global $smarty, $LANG, $sessioninfo, $con;
	
		if ($this->approval_on_behalf) {
			$u = explode(',',$this->approval_on_behalf['on_behalf_of']);
			$search_approval = $u[0];
			$doc_filter = ' and pa.id = '.mi($_REQUEST['id']).' and pa.branch_id = '.mi($_REQUEST['branch_id']).' ';
		}
		else $search_approval = $sessioninfo['id'];
	
      $con->sql_query("select pa.date_from, pa.date_to, pa.branch_id, pa.id, pa.status , branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name 
	from purchase_agreement pa 
	left join branch_approval_history bah on pa.approval_history_id = bah.id and pa.branch_id=bah.branch_id 
	left join user on user.id=pa.user_id
	left join branch on pa.branch_id = branch.id 
	where (
(approvals like '|$search_approval|%' and approval_order_id=1) or
(approvals like '%|$search_approval|%' and approval_order_id in (2,3))
) and pa.branch_id = ".mi($sessioninfo['branch_id'])." and pa.active=1 and pa.status = 1 and pa.approved=0 $doc_filter order by pa.last_update");

	   	$smarty->assign("pa_list", $con->sql_fetchrowset());
	   	$con->sql_freeresult();
	}
	
	function ajax_load_pa(){
		global $con, $smarty, $branch_id;
		
		$form=array();	
		$id = $this->pa_id;
		
		$form = load_purchase_agreement_header($this->branch_id, $id);

		$con->sql_query("select u from user where id=".mi($form['user_id']));
		$r=$con->sql_fetchrow();
		$form['u']=$r[0];
	
	
		$form['approval_screen']=1;
		
		$item_list = load_purhcase_agreement_items_list($this->branch_id, $id);
		$smarty->assign('form', $form);
		$smarty->assign('item_list', $item_list);
		$smarty->display("po.po_agreement.setup.open.tpl");
			
	}
	
	function reject_approval()
	{
		$this->save_approval();
	}
	
	function terminate_approval()
	{
		$this->save_approval();
	}

	function save_approval(){
		global $con, $sessioninfo, $LANG, $approval_status, $branch_id;
		
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
		$pa_id=intval($form['id']);
		$branch_id = $this->branch_id;

		//$form = load_purchase_agreement_header($this->branch_id, $id);
		
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
        
			$con->sql_query("update purchase_agreement set status=$approve $set_active $set_approve , last_update=now() where id=$pa_id and branch_id=$branch_id");

			//$con->sql_query("update promotion set status=$approve where id=$promoid and branch_id=$branch_id");

			log_br($sessioninfo['id'], 'PURCHASE AGREEMENT', $pa_id, "Purchase Agreement Approval (ID#$pa_id, Status: ".$this->approval_status[$approve].")");

			// if this is not KIV.
			if ($approve!=3){
				$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $approve, $sz)");

				/*
				$con->sql_query("select flow_approvals, approvals, pa.user_id, notify_users 
				from branch_approval_history bah 
				left join purchase_agreement pa on bah.ref_id = pa.id and bah.branch_id = pa.branch_id 
				where bah.id = $aid and bah.branch_id = $branch_id");
				$r = $con->sql_fetchrow();

				$recipients = $r[3];
				$owner = $r[2];
				$flow_approvals = $r[0];
					
		       	$recipients = str_replace("|$sessioninfo[id]|", "|", $recipients);
		       	$to = preg_split("/\|/", $recipients);
				*/
				
		        if($approve!=1){
		          $con->sql_query("update branch_approval_history set status = $approve where id = $aid and branch_id = $branch_id");
		        }				
				
				// send pm
				//$to[] = $owner;
				$to = get_pm_recipient_list2($pa_id,$aid,$approve,'approval',$branch_id,'purchase_agreement');
				$status_str = ($is_last || $approve != 1) ? $this->approval_status[$approve] : '';
				send_pm2($to, "Purchase Agreement Approval (ID#$pa_id) $status_str", "po.po_agreement.setup.php?a=view&id=$pa_id&branch_id=$branch_id", array('module_name'=>'purchase_agreement'));
			}
		}

		print "<script>alert('Purchase Agreement ".$this->approval_status[$approve]."');</script>\n";
		
		if ($this->approval_on_behalf) {
			header("Location: /stucked_document_approvals.php?m=purchase_agreement");
			exit;
		}
		
		$this->_default();
	}
}

$PURCHASE_AGREEMENT_APPROVAL = new PURCHASE_AGREEMENT_APPROVAL ('Purchase Agreement Approval');
?>
