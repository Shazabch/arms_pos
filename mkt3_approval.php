<?
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT3_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_APPROVAL', BRANCH_CODE), "/index.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

$smarty->assign("PAGE_TITLE", "Brand and Item Offer Proposal");

$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT3_%'");
while ($r = $con->sql_fetchrow()){
	$mkt3_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt3_privilege", $mkt3_privilege);
//print_r($mkt3_privilege);


if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_mkt3':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
		    $id = intval($_REQUEST['id']);
		    $branch_id = intval($_REQUEST['branch_id']);
		    $dept_id = intval($_REQUEST['dept_id']);

			$form = array();
			load_header($form);

			$con->sql_query("select mkt3.*,bah.approvals
from mkt3
left join branch_approval_history bah on bah.id = mkt3.approval_history_id and bah.branch_id = mkt3.branch_id
where mkt0_id = $id and mkt3.branch_id=$branch_id and dept_id = $dept_id");
			$mkt3 = $con->sql_fetchrow();
			$mkt3['offers'] = unserialize($mkt3['offers']);
			$mkt3['brands'] = unserialize($mkt3['brands']);

			if (preg_match("/^\|$sessioninfo[id]\|/", $mkt3['approvals'])){
			    $form['is_approval'] = 1;
			}

			if ($mkt3['approval_history_id']>0){

				$con->sql_query("select i.timestamp, i.log, i.status, user.u
		from branch_approval_history_items i
		left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id left join user on i.user_id = user.id where h.ref_table = 'mkt3' and i.branch_id = $branch_id and i.approval_history_id = $mkt3[approval_history_id] order by i.timestamp");
				$smarty->assign("approval_history", $con->sql_fetchrowset());
			}

			$form = array_merge($mkt3,$form);

			// rebuild form array for offers and brands table if not empty
			if ($form['offers'])
			{
				$cols = array_keys($form['offers'][0]);

			    foreach ($cols as $col)
				{
					foreach ($form['offers'] as $r)
					{
						$offers[$col][] = $r[$col];
					}
				}
				$form['offers'] = $offers;

   			}

			if ($form['brands'])
			{
				$cols = array_keys($form['brands'][0]);
			    foreach ($cols as $col)
				{
					foreach ($form['brands'] as $r)
					{
						$brands[$col][] = $r[$col];
					}
				}
				$form['brands'] = $brands;

			}
			$form['approval_screen']=1;
			$smarty->assign("form", $form);
  			//echo"<pre>";print_r($form);echo"</pre>";
	        $smarty->display("mkt3.edit.tpl");
			exit;


		case 'approve':
			if (!privilege('MKT3_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_APPROVAL', BRANCH_CODE), "/mkt3.php?branch_id=$branch_id");
		case 'reject':
			if (!privilege('MKT3_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_APPROVAL', BRANCH_CODE), "/mkt3.php?branch_id=$branch_id");
		    if ($_REQUEST['a']=='approve')
				$status=1;
			elseif ($_REQUEST['a']=='reject')
				$status=2;

			$aid = intval($_REQUEST['approval_history_id']);
			$branch_id = intval($_REQUEST['branch_id']);
			$id = intval($_REQUEST['id']);
			$dept_id = intval($_REQUEST['dept_id']);
			$sz = ms($_REQUEST['reason']);
			$approvals = $_REQUEST['approvals'];
			$approved = 0;

			if ($status==1){
				// remove current approval
				$approvals = str_replace("|$sessioninfo[id]|","|",$approvals);
				if ($approvals == '|') $approved = 1;
			}

			// update approval records
			$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($aid, $branch_id, $sessioninfo[id], $status, $sz)");

			$con->sql_query("update branch_approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid and branch_id = $branch_id");

			$con->sql_query("update mkt3 set status=$status, approved=$approved where mkt0_id=$id and branch_id = $branch_id and dept_id=$dept_id");

			if ($approved)
				log_br($sessioninfo['id'], 'MKT', $id, "MKT3 Fully Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT3 Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT3 Rejected by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");

			header("Location: /mkt3_approval.php?t=confirm&id=$id");
		    exit;

		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
mkt3_approval_all();

function mkt3_approval_all()
{
	global $smarty, $LANG, $sessioninfo, $con;
	
   	$con->sql_query("select mkt0.*, mkt3.*, approvals, flow_approvals as org_approvals, branch.report_prefix as prefix, branch.code as branch_name, user.u as user_name, category.id as dept_id, category.description as dept_name
from mkt3
left join mkt0 on mkt0.id=mkt3.mkt0_id
left join branch_approval_history bah on mkt3.approval_history_id = bah.id and bah.branch_id=$sessioninfo[branch_id]
left join user on user.id=mkt3.user_id
left join branch on mkt3.branch_id = branch.id
left join category on category.id=mkt3.dept_id
where bah.approvals like '|$sessioninfo[id]|%' and mkt3.status <> 2 and mkt3.approved=0");
	while($list=$con->sql_fetchrow()){
		$mkt3[]=$list;
	}
	//echo"<pre>";print_r($mkt3);echo"</pre>";
    $smarty->assign("mkt3", $mkt3);
	$smarty->assign("PAGE_TITLE", "Brand and Item Offer Proposal");
   	$smarty->display("mkt3_approval.index.tpl");
}

function load_header(&$form){

	global $con, $LANG,$smarty;
    $id = intval($_REQUEST['id']);

	$branch_id=get_request_branch();
	$smarty->assign("branch_id", $branch_id);

    $dept_id = intval($_REQUEST['dept_id']);

    $con->sql_query("select * from mkt0 where id = $id");
	$header = $con->sql_fetchrow();
	/// invalid id
	if (!$header)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));
    $header['publish_dates'] = unserialize($header['publish_dates']);
	$header['attachments'] = unserialize($header['attachments']);
	if ($header['attachments']['file'])
	{
		foreach ($header['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $header['filepath'][$idx] = "/mkt_attachments/$header[id]/$fn";
		}
	}
	$con->sql_query("select description from category where id = $dept_id");
	$r = $con->sql_fetchrow();

	$header['branch_id'] = $branch_id;
	$header['dept_id'] = $dept_id;
	$header['department'] = $r['description'];

	$con->sql_query("select * from mkt2
where mkt0_id = $id and dept_id=$dept_id and branch_id=$branch_id");
	$mkt2 = $con->sql_fetchrow();

	$header['sales_target'] = $mkt2['sales_target'];
	$header['normal_target'] = $mkt2['normal_target'];

	// get minimum number of input needed from mkt settings
	$con->sql_query("select min_offer,max_offer,min_brand,max_brand from mkt_settings where branch_id = $branch_id and dept_id = $dept_id");
	$r = $con->sql_fetchrow();
    $header['limit'] = $r;
	$form = array_merge($header,$form);
}

?>
