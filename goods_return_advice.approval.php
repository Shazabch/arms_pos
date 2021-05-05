<?php
/*
7/31/2013 10:25 AM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

4/20/2015 11:41 AM Justin
- Enhanced to retrieve GST information.

5/8/2015 10:48 AM Justin
- Enhanced to have document date for invalid SKU.

21-Jan-2016 15:15 AM Edwin
- Fixed GRA approval screen show others branch gra

1/28/2016 11:09 AM Qiu Ying
- retrieve inclusive_tax and selling_price from gra_items in function "ajax_load_gra"

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

10/7/2016 11:46 AM Andy
- Fixed stucked approval redirect to wrong php.

05/08/2019 02:03 PM Liew
Enhanced to display Old Code 

5/20/2019 2:56 PM William
- Pickup report_prefix for enhance "GRA".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA_APPROVAL', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

include('goods_return_advice.include.php');

class GRA_APPROVAL extends Module{
	var $branch_id, $gra_id, $approval_status;
	
	function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty;		
		
		$this->approval_status = array(1 => "Approved", 2 => "Rejected", 4 => "Terminated");


		$this->branch_id = intval($_REQUEST['branch_id']);
		if ($this->branch_id ==''){
			$this->branch_id = $sessioninfo['branch_id'];
		}

		$this->gra_id = mi($_REQUEST['id']);		
		
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
			$doc_filter = ' and gra.id = '.mi($_REQUEST['id']).' and gra.branch_id = '.mi($_REQUEST['branch_id']).' ';
		}
		else $search_approval = $sessioninfo['id'];
		
		$con->sql_query("select gra.*, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name 
						from gra 
						left join branch_approval_history bah on gra.approval_history_id = bah.id and gra.branch_id = bah.branch_id 
						left join user on user.id=gra.user_id
						left join branch on gra.branch_id = branch.id 
						where gra.returned = 0 and bah.approvals like '%|$search_approval|%' and gra.status = 2 and gra.approved=0 $doc_filter and gra.branch_id = $sessioninfo[branch_id] order by gra.last_update");

	   	$smarty->assign("gra_list", $con->sql_fetchrowset());
	   	$con->sql_freeresult();
	}
	
	function ajax_load_gra(){
		global $con, $smarty, $branch_id;
		
		$gra=array();	
		$id = intval($_REQUEST['id']);
		$branch_id = intval($_REQUEST['branch_id']);
		$q1 = $con->sql_query("select gra.*, user.u, vendor.description as vendor,category.description as dept_code,
						branch.code as branch_code,branch.report_prefix as report_prefix
						from gra
						left join user on gra.user_id = user.id
						left join vendor on gra.vendor_id = vendor.id
						left join category on gra.dept_id = category.id
						left join branch on branch.id=gra.branch_id
						where gra.id = $id and branch_id = $branch_id");
		$gra = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$gra['misc_info'] = unserialize($gra['misc_info']);
		$gra['extra']= unserialize($gra['extra']);
		
		if($gra){
			if($gra['extra']){
				foreach ($gra['extra']['code'] as $idx=>$fn){
					$new[$idx]['code'] = $fn;
					$new[$idx]['description'] = $gra['extra']['description'][$idx];
					$new[$idx]['cost'] = $gra['extra']['cost'][$idx];
					$new[$idx]['qty'] = $gra['extra']['qty'][$idx];
					$new[$idx]['gst_id'] = $gra['extra']['gst_id'][$idx];
					$new[$idx]['gst_code'] = $gra['extra']['gst_code'][$idx];
					$new[$idx]['gst_rate'] = $gra['extra']['gst_rate'][$idx];
					$new[$idx]['doc_no'] = $gra['extra']['doc_no'][$idx];
					$new[$idx]['doc_date'] = $gra['extra']['doc_date'][$idx];
					$new[$idx]['reason'] = $gra['extra']['reason'][$idx];
				}
			}
			
			$q1 = $con->sql_query("select gra_items.*, sku_item_code, artno, mcode, link_code, sku_items.description as 
								   sku,if(sip.price, sip.price, sku_items.selling_price) as selling_price, 
								   sku_items.doc_allow_decimal,puom.code as packing_uom_code, gra_items.selling_price as sp, if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax
								   from gra_items 
								   left join sku_items on gra_items.sku_item_id = sku_items.id 
								   left join sku_items_price sip on sip.sku_item_id=gra_items.sku_item_id and sip.branch_id =gra_items.branch_id
								   left join sku on sku_items.sku_id=sku.id
								   left join category_cache cc on cc.category_id=sku.category_id
								   left join uom puom on puom.id=sku_items.packing_uom_id
								   where gra_id = $id and gra_items.branch_id = $branch_id");

			$gra['items'] = $con->sql_fetchrowset($q1);
			$con->sql_freeresult($q1);
		}

		$smarty->assign("new", $new);

		//echo"<pre>";print_r($new);echo"</pre>";
		//echo"<pre>";print_r($gra);echo"</pre>";
		$smarty->assign("form", $gra);
		$smarty->display("goods_return_advice.approval.view.tpl");	
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
		global $con, $sessioninfo, $LANG, $branch_id;
		
		 // save approval status (1 = approve, 2 = rejected. 3 = KIV, 4 = Terminate)
		$form=$_REQUEST;
	
		if ($form['a']=='terminate_approval') $approve=4;
		/*elseif ($form['a']=='reject_approval')
			$approve=2;
		elseif ($form['a']=='kiv_approval')
			$approve=3;*/
		elseif($form['a']=='save_approval'){
			$upd['status'] = 0;
			$approve=1;
		}

		// save approval status
		$comments=$form['comments'];
		
		if ($this->approval_on_behalf) {
			$comments .= " (by ".$this->approval_on_behalf['on_behalf_by_u']." on behalf of ".$this->approval_on_behalf['on_behalf_of_u'].")";
		}
		$comments=ms($comments);
		
		$aid=intval($form['approval_history_id']);
		$gra_id=intval($form['id']);
		$branch_id = intval($form['branch_id']);

		$approved = 0;
		if($aid > 0){
			if($approve==1){  // approve
				$params = array();
				$params['approve'] = 1;
				$params['user_id'] = $sessioninfo['id'];
				$params['id'] = $aid;
				$params['branch_id'] = $branch_id;
				$params['update_approval_flow'] = true;
				$is_last = check_is_last_approval_by_id($params, $con);
				if($is_last){ // found it is last approval
					$approved = 1;
					$status = 0;
				}else{ // maintain in approval flow
					$status = 2;
				}
			}elseif($approve == 4){ // terminate
				$status = 1;
			}

			if($is_last)  $set_approve = ", approved = 1";
        
			$con->sql_query("update gra set status=$status, approved=$approved, last_update=now() where id=$gra_id and branch_id=$branch_id");

			log_br($sessioninfo['id'], 'GRA', $gra_id, "Goods Return Advice Approval (ID#$gra_id, Status: ".$this->approval_status[$approve].")");

			// if this is not KIV.
			if($approve!=3){
				$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $approve, $comments)");
				
		        if($approve!=1){
					$con->sql_query("update branch_approval_history set status = $approve where id = $aid and branch_id = $branch_id");
		        }
				
				// send pm
				//$to[] = $owner;
				
				$tmp_status = 0;
				if($status == 1)	$tmp_status = 2;
				$to = get_pm_recipient_list2($gra_id, $aid, $tmp_status, 'approval', $branch_id, 'gra');
				$status_str = ($is_last || $approve != 1) ? $this->approval_status[$approve] : '';
				send_pm2($to, "Goods Return Advice Approval (ID#$gra_id) $status_str", "goods_return_advice.php?a=view&id=$gra_id&branch_id=$branch_id", array('module_name'=>'gra'));
			}
		}

		print "<script>alert('GRA#".$gra_id." ".$this->approval_status[$approve]."');</script>\n";
		
		if ($this->approval_on_behalf) {
			header("Location: /stucked_document_approvals.php?m=gra");
			exit;
		}
		
		$this->_default();
	}
}

$GRA_APPROVAL = new GRA_APPROVAL('GRA Approval');
?>
