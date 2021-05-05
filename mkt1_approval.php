<?
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if(BRANCH_CODE !='HQ') js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'Access By HQ Only', BRANCH_CODE), "/index.php");
if (!privilege('MKT1_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT1_APPROVAL', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "Branch Sales Target and Expenses Approval");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT1_%'");
while ($r = $con->sql_fetchrow()){
	$mkt1_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt1_privilege", $mkt1_privilege);
//print_r($mkt1_privilege);



if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_mkt1':
			$id = intval($_REQUEST['id']);
			$branch_id=$_REQUEST['branch_id'];
			//echo "$branch_id<br>";
			$form = load_header($id,$branch_id);
			$form['branch_id']=$branch_id;
			$form['approval_screen']=1;
    		$smarty->assign("form", $form);
			//echo"<pre>";print_r($form);echo"</pre>";
			$smarty->display("mkt1.edit.tpl");
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
			$branch_id = intval($_REQUEST['branch_id']);
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
			$con->sql_query("insert into approval_history_items (approval_history_id, user_id, status, log) values ($aid, $sessioninfo[id], $status, $sz)");

			$con->sql_query("update approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid");

			$con->sql_query("update mkt1 set status=$status, approved=$approved where mkt0_id=$id and branch_id = $branch_id");

			if ($approved)
				log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Fully Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT1 Rejected by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");
			header("Location: /mkt1_approval.php?t=confirm&id=$id");
		    exit;


		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
mkt1_approval_all();

function mkt1_approval_all()
{
	global $smarty, $LANG, $sessioninfo, $con;
	
   	$con->sql_query("select mkt0.*, mkt1.*, approvals, flow_approvals as org_approvals, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name
from mkt1
left join mkt0 on mkt0.id=mkt1.mkt0_id
left join approval_history bah on mkt1.approval_history_id=bah.id
left join user on user.id=mkt1.user_id
left join branch on mkt1.branch_id = branch.id
where bah.approvals like '|$sessioninfo[id]|%' and mkt1.status <> 2 and mkt1.approved=0");
	while($list=$con->sql_fetchrow()){
		$mkt1[]=$list;
	}
	//echo"<pre>";print_r($mkt1);echo"</pre>";
    $smarty->assign("mkt1", $mkt1);
	$smarty->assign("PAGE_TITLE", "Branch Sales Target and Expenses Approval");
   	$smarty->display("mkt1_approval.index.tpl");
}

function load_header($mkt0_id,$branch_id)
{
    global $con, $LANG, $smarty, $sessioninfo;


	$con->sql_query("select * from mkt0 where id = $mkt0_id");
	$form = $con->sql_fetchrow();
	/// invalid id
	if (!$form)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));

	$form['expenses'] = unserialize($form['expenses']);
	$form['publish_dates'] = unserialize($form['publish_dates']);
	$form['attachments'] = unserialize($form['attachments']);
	if ($form['attachments']['file'])
	{
		foreach ($form['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $form['filepath'][$idx] = "/mkt_attachments/$form[id]/$fn";
		}
	}

	// load branch settings and merge into array, if exist
	$con->sql_query("select mkt1.*, bah.approvals, mkt1.status as mkt1_status, mkt1.user_id as mkt1_user_id
from mkt1
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
		$con->sql_query("select i.timestamp, i.log, i.status, user.u from approval_history_items i 
left join approval_history h on i.approval_history_id = h.id
left join user on i.user_id = user.id 
where h.ref_table = 'mkt1' and i.approval_history_id = $form[approval_history_id] order by i.timestamp");
		$smarty->assign("approval_history", $con->sql_fetchrowset());
	}
	return $form;
}

?>
