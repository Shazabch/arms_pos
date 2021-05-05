<?php
// mk0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT1_VIEW') and !privilege('MKT1_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Branch Sales Target and Expenses');
$hqcon = connect_hq();
//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT1_%'");
while ($r = $con->sql_fetchrow()){
	$mkt1_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt1_privilege", $mkt1_privilege);
//print_r($mkt1_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id=get_request_branch();
if(!$branch_id){
	$branch_id=$sessioninfo['branch_id'];
}

$con->sql_query("select line.id as line_id, line.description as line, dept.id as dept_id, dept.description as dept from category dept left join category line on dept.root_id = line.id where dept.level=2 and dept.active and line.active order by line,dept");
$smarty->assign("departments", $con->sql_fetchrowset());

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'view':
		case 'open':
		    $id = intval($_REQUEST['id']);
		    //$branch_id = intval($_REQUEST['branch_id']);
		    if (!$id || !$branch_id)
			{
				header("Location: /mkt1.php");
				exit;
			}
			$form = load_header($id,$branch_id);
			$smarty->assign('form', $form);

			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);

			if ($form['approved']==1 || ($form['mkt1_status']!=0 && $form['mkt1_status']!=2) || ($form['mkt1_user_id']!=$sessioninfo['id'] && $form['mkt1_user_id']>0))
			{
			    $_REQUEST['a'] = 'view';
			}
			//echo"<pre>";print_r($hurl);echo"</pre>";
		    $smarty->display("mkt1.edit.tpl");
		    exit;

		case 'confirm':
			if (!privilege('MKT1_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_EDIT', BRANCH_CODE), "/mkt1.php?branch_id=$branch_id");
		    $is_confirm = 1;
		case 'save':
			if (!privilege('MKT1_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_EDIT', BRANCH_CODE), "/mkt1.php?branch_id=$branch_id");
			
			$form = $_REQUEST;
		    $errm = validate_data($form,$is_confirm);
            $last_approval = false;
            
			if (!$errm && $is_confirm)
			{
			    // if approval_history_id defined, just restore the approvals
			    if ($_REQUEST['approval_history_id']){

			        $con->sql_query("update approval_history set approvals = flow_approvals where id = ".mi($_REQUEST['approval_history_id'])."");
			        
                    $con->sql_query("select id,approvals from approval_history where id = ".mi($_REQUEST['approval_history_id'])."");
					$astat = $con->sql_fetchrow();
			        if ($astat[1] == '|') $last_approval = true;
				}
			    else
				{
					// create approval history
					$astat = check_and_create_approval('MKT1', $sessioninfo['branch_id'], 'mkt1','',$hqcon);
					if (!$astat)
					{
						$errm['top'][] = $LANG['MKT1_NO_APPROVAL_FLOW'];
					}
					else
					{
						$form['approval_history_id'] = $astat[0];
		       			if ($astat[1] == '|') $last_approval = true;
					}
				}
			}

		    if (!$errm)
		    {
			    $form['expenses'] = serialize($form['expenses']);
			    $form['dept_contribution'] = serialize($form['dept_contribution']);
			    $form['approved'] = 0;
			    
		        // make mkt1 actual
			    if ($is_confirm) $form['status'] = 1;
			    if ($last_approval) $form['approved'] = 1;
			    if (!$form['mkt0_id'])
			    {
			        $form['user_id'] = $sessioninfo['id'];
			        $form['mkt0_id'] = $form['id'];
			        $form['added'] = 'CURRENT_TIMESTAMP()';
					$con->sql_query("insert into mkt1 ".mysql_insert_by_field($form,array('mkt0_id','branch_id','user_id','normal_sales','normal_gp','promo_sales','promo_gp','added','status','approved','approval_history_id','dept_contribution','expenses','total_expenses')));
					$id = $con->sql_nextid();
				}
				else
				{
				    $id = intval($form['id']);
				    $branch_id = intval($form['branch_id']);
				    $con->sql_query("update mkt1 set ".mysql_update_by_field($form,array('normal_sales','normal_gp','promo_sales','promo_gp','status','approved','approval_history_id','dept_contribution','expenses','total_expenses')) . " where mkt0_id=$id and branch_id=$branch_id") or die(mysql_error());
				}

			    // after save , return to front page
			    if ($is_confirm)
				{
					log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Confirmed by Branch Manager (ID#$id)");
			    	header("Location: /mkt1.php?t=confirm&id=$form[id]&branch_id=$branch_id");
			    }
			    else
			    	header("Location: /mkt1.php?t=save&id=$form[id]&branch_id=$branch_id");
		    }
		    else
		    {
		        $hd = load_header($form['id'],$form['branch_id']);
		        $form = array_merge($hd,$form);
		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				$smarty->display("mkt1.edit.tpl");
			}
			exit;
			
		case 'approve':
			if (!privilege('MKT1_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_APPROVAL', BRANCH_CODE), "/mkt1.php?branch_id=$branch_id");
		case 'reject':
			if (!privilege('MKT1_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_APPROVAL', BRANCH_CODE), "/mkt1.php?branch_id=$branch_id");
		    if ($_REQUEST['a']=='approve')
				$status=1;
			elseif ($_REQUEST['a']=='reject')
				$status=2;

			$aid = intval($_REQUEST['approval_history_id']);
			//$branch_id = intval($_REQUEST['branch_id']);
			$id = intval($_REQUEST['id']);
			$sz = ms($_REQUEST['reason']);
			$approvals = $_REQUEST['approvals'];
			$approved = 0;

			if ($status==1)
			{
				// remove current approval
				$approvals = str_replace("|$sessioninfo[id]|","|",$approvals);
				if ($approvals == '|') $approved = 1;
			}

			// update approval records
			$con->sql_query("insert into approval_history_items (approval_history_id,  user_id, status, log) values ($aid, $sessioninfo[id], $status, $sz)");

			$con->sql_query("update approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid");

			$con->sql_query("update mkt1 set status=$status, approved=$approved where mkt0_id=$id and branch_id=$branch_id");

			if ($approved)
				log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Fully Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Rejected by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");

			header("Location: /mkt1.php?t=confirm&id=$id");
		    exit;

		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt1.home.list.tpl");
		    exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt1.home.tpl');

function load_mkt_list()
{
	global $con, $sessioninfo, $smarty, $depts;
	
	$branch_id=get_request_branch();
	if(!$branch_id){
		$branch_id=$sessioninfo['branch_id'];
	}
	$smarty->assign("branch_id", $branch_id);

	if (!$t) $t = intval($_REQUEST['t']);
	/*if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else*/
	//$owner_check = "((mkt1.user_id = $sessioninfo[id] or mkt1.user_id is null)  and (mkt1.branch_id = $branch_id)";
 	//$owner_check = "(mkt1.user_id is null or mkt1.user_id = $sessioninfo[id] or mkt0.user_id = $sessioninfo[id])  and ";

    $where = 'mkt0.active and mkt0.status and mkt0.branches like \'%"'.$branch_id.'"%\'';

	switch ($t)
	{
	    case 0:
	        $where .= ' and (mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%') . ')';
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved
        	$where .= " and (mkt1.status is null or (mkt1.status = 0 and mkt1.approved=0))";
        	break;

		case 2: // show waiting approval
		    $where .= " and mkt1.status = 1 and mkt1.approved=0";
		    break;

		case 3: // show rejected
		    $where .= " and mkt1.status = 2 and mkt1.approved=0";
		    break;

		case 4: // show approved
		    $where .= " and mkt1.approved = 1";
		    break;

	}

	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*) from mkt0 left join mkt1 on mkt0.id = mkt0_id and mkt1.branch_id = $branch_id where $owner_check $where");
	$r = $con->sql_fetchrow();
	$total = $r[0];
	if ($total > $sz)
	{
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++)
		{
			$pg .= "<option value=$i";
			if ($i == $start)
			{
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	}

	$con->sql_query("select mkt0.title,mkt0.id,mkt0.offer_from,mkt0.offer_to,mkt0.submit_due_date_0 as submit_due_date,mkt1.*, user.u, user2.u as create_u, bah.approvals from mkt0
left join mkt1 on mkt0.id = mkt0_id and mkt1.branch_id = $branch_id
left join user on mkt1.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
left join approval_history bah on bah.id = mkt1.approval_history_id
where $owner_check $where
order by mkt1.last_update desc, mkt0.last_update desc limit $start, $sz");
while($r=$con->sql_fetchrow()){
	$list[]=$r;
}
$smarty->assign("list", $list);
//echo"<pre>";print_r($list);echo"</pre>";
//$smarty->assign("list", $con->sql_fetchrowset());

}

function validate_data(&$form, $is_confirm)
{
	global $LANG;
	$errm = array();

	// todo: add checking code here

	return $errm;
}

function load_header($mkt0_id,$branch_id)
{
    global $con, $LANG, $smarty, $sessioninfo, $lines;
    $branch_id=get_request_branch();
	$smarty->assign('branch_id', $branch_id);
	
	$con->sql_query("select *,YEAR(offer_from) as from_year,MONTH(offer_from) as from_month,
YEAR(offer_to) as to_year,MONTH(offer_to) as to_month
from mkt0 where id = $mkt0_id");
	$form = $con->sql_fetchrow();
	/// invalid id
	if (!$form)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));

	/// owner check and status. if not owner or status not zero (cofirmed) switch to view only mode
	/*if ($_REQUEST['a'] == 'open' && ($form['user_id']!=$sessioninfo['id'] || $form['status']>0))
		show_redir("$_SERVER[PHP_SELF]?a=view&id=$id&branch_id=$branch_id", 'Sales Target', sprintf($LANG['MKT_NO_PERMISSION_TO_EDIT'],$id));*/

	$form['expenses'] = unserialize($form['expenses']);
	$form['publish_dates'] = unserialize($form['publish_dates']);
	$form['attachments'] = unserialize($form['attachments']);
	if ($form['attachments']['file'])
	{
		foreach ($form['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $form['filepath'][$idx] = "mkt_attachments/$form[id]/$fn";
		}
	}
	$form['branches'] = unserialize($form['branches']);
	if ($form['branches']){
		foreach ($form['branches'] as $idx=>$fn){
			if ($fn) $form['branches'][] =$fn ;
		}
	}
	// load branch settings and merge into array, if exist
	$con->sql_query("select mkt1.*, bah.approvals, mkt1.status as mkt1_status, mkt1.user_id as mkt1_user_id, branch.code as current_branch
from mkt1
left join branch on branch.id=mkt1.branch_id
left join approval_history bah on bah.id = mkt1.approval_history_id
where mkt1.mkt0_id = $mkt0_id and mkt1.branch_id=$branch_id");
	$mkt1=$con->sql_fetchrow();
	if ($mkt1)
	{
	    $mkt1['expenses'] = unserialize($mkt1['expenses']);
		$mkt1['dept_contribution'] = unserialize($mkt1['dept_contribution']);
		$form = array_merge($form, $mkt1);
	}
    // if viewer is current approval, set is_approval=1
	if (preg_match("/^\|$sessioninfo[id]\|/", $form['approvals']))
	{
	    $form['is_approval'] = 1;
	}
	
	if ($form['approval_history_id']>0)
	{
		$con->sql_query("select i.timestamp, i.log, i.status, user.u
from approval_history_items i
left join approval_history h on i.approval_history_id = h.id 
left join user on i.user_id = user.id
where h.ref_table = 'mkt1' and i.approval_history_id = $form[approval_history_id]
order by i.timestamp");
		$smarty->assign("approval_history", $con->sql_fetchrowset());
	}
	$q1=$con->sql_query("select sum(normal_forecast) as normal_total,sum(sales_target) as sales_total from mkt_review_items where date>='$form[offer_from]' and date<='$form[offer_to]' and branch_id=$branch_id");
	$r1 = $con->sql_fetchrow($q1);
	$amount=$r1;
	$smarty->assign("amount",$amount);	
	
	return $form;
}

?>
