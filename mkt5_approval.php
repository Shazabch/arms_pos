<?
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT5_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_APPROVAL', BRANCH_CODE), "/index.php");

$approval_status = array(1 => "Approved", 2 => "Rejected", 3 => "KIV (Pending)", 4 => "Terminated");

$smarty->assign("PAGE_TITLE", "Branch Sales Target and Expenses Approval");

$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'mkt5_%'");
while ($r = $con->sql_fetchrow()){
	$mkt5_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt5_privilege", $mkt5_privilege);
//print_r($mkt5_privilege);


if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_mkt5':
			$id = intval($_REQUEST['id']);
			$dept_id=$_REQUEST['dept_id'];

    		$con->sql_query("select mkt5_dept_approval.*, bah.approvals
from mkt5_dept_approval
left join approval_history bah on bah.id = mkt5_dept_approval.approval_history_id
where dept_id=".mi($dept_id)." and mkt0_id=".mi($id)."");
    		$r = $con->sql_fetchrow();
			$check['status']=$r['status'];
			$check['approved']=$r['approved'];
			$check['dept_id']=$r['dept_id'];
			$check['approvals']=$r['approvals'];
			$check['approval_history_id']=$r['approval_history_id'];

			if (preg_match("/^\|$sessioninfo[id]\|/", $check['approvals'])){
			    $check['is_approval'] = 1;
			}

			if ($check['approval_history_id']>0){

				$con->sql_query("select i.timestamp, i.log, i.status, user.u
from branch_approval_history_items i
left join branch_approval_history h on i.approval_history_id = h.id and i.branch_id = h.branch_id
left join user on i.user_id = user.id
where h.ref_table = 'mkt5' and i.branch_id = $sessioninfo[branch_id] and i.approval_history_id = $check[approval_history_id] order by i.timestamp");
				$smarty->assign("approval_history", $con->sql_fetchrowset());
			}

			$r1=$con->sql_query("select mkt5.*,category.description as dept from mkt5 left join category on dept_id = category.id where dept_id=".mi($dept_id)." and mkt0_id=".mi($id)." order by  publish_date");
			//print "<pre>";print_r($r1);print "</pre>";
			while ($mkt5=$con->sql_fetchrow($r1))
			{
    			$mkt5['offers'] = unserialize($mkt5['offers']);
				$mkt5['brands'] = unserialize($mkt5['brands']);

				foreach ($mkt5['brands'] as $id=>$o)
				{
				    if ($o['check'])
				    {
				        $o['dept'] = $mkt5['dept'];
				        $o['publish_date'] = $mkt5['publish_date'];
				        $form[$mkt5['publish_date']][$id] = $o;
					}
				}

				foreach ($mkt5['offers'] as $id=>$o)
				{
				    if ($o['check'])
				    {
				        $o['dept'] = $mkt5['dept'];
				        $o['publish_date'] = $mkt5['publish_date'];
				        $form[$mkt5['publish_date']][$id] = $o;
					}
				}

			}
			$smarty->assign("form", $form);
		    $mkt4 = array();
			load_header($mkt4,$sessioninfo);
			$smarty->assign("branches", $mkt0['branches']);
			$con->sql_query("select * from branch where active order by id");
			$smarty->assign("db_branch", $con->sql_fetchrowset());
			$smarty->assign("mkt4", $mkt4);
			$smarty->assign("mkt0", $mkt0);
			$check['approval_screen']=1;
			$smarty->assign("check", $check);
		    $smarty->assign("errm", $errm);
			//print "<pre>";print_r($mkt4);print "</pre>";
			$smarty->display('mkt_status.edit.tpl');
			exit;

		case 'approve':
			if (!privilege('MKT5_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_APPROVAL', BRANCH_CODE), "/mkt5.php?branch_id=$branch_id");
		case 'reject':
			if (!privilege('MKT5_APPROVAL')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_APPROVAL', BRANCH_CODE), "/mkt5.php?branch_id=$branch_id");
			
		    if ($_REQUEST['a']=='approve')
				$status=1;
			elseif ($_REQUEST['a']=='reject')
				$status=2;

			$aid = intval($_REQUEST['approval_history_id']);
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
			
			//echo "a=$approvals<br>";
			//exit;
			// update approval records
			$con->sql_query("insert into approval_history_items (approval_history_id, user_id, status, log) values ($aid, $sessioninfo[id], $status, $sz)");

			$con->sql_query("update approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid");

			$con->sql_query("update mkt5_dept_approval set status=$status, approved=$approved where mkt0_id=$id and dept_id=$dept_id");
			
    		$con->sql_query("select * from mkt5_dept_approval where mkt0_id=".mi($id)."");
    		while ($r = $con->sql_fetchrow()){
	    		if($r['approved']){
					$mkt5_status=1;
				}
				else{
					$mkt5_status=0;
					break;
				}
			}

			$con->sql_query("update mkt0 set mkt5_status=$mkt5_status where id=$id");
			
			if ($approved)
				log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Fully Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Rejected by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");

			header("Location: /mkt5_approval.php?t=$_REQUEST[a]&id=$id&sdfsdf==$mkt5_status");
		    exit;


		
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    fail("<h1>Unhandled Request</h1>");
		    exit;
	}
}
	
mkt5_approval_all();

function mkt5_approval_all()
{
	global $smarty, $LANG, $sessioninfo, $con;
	
   	$con->sql_query("select mkt0.*, mkt5_dept_approval.*, approvals, flow_approvals as org_approvals, user.u as user_name, category.description as dept_name, category.id as dept_id
from mkt5_dept_approval
left join mkt0 on mkt0.id=mkt5_dept_approval.mkt0_id
left join approval_history bah on mkt5_dept_approval.approval_history_id = bah.id
left join user on user.id=mkt5_dept_approval.user_id
left join category on mkt5_dept_approval.dept_id = category.id
where bah.approvals like '|$sessioninfo[id]|%' and mkt5_dept_approval.status <> 2 and mkt5_dept_approval.approved=0");
	while($list=$con->sql_fetchrow()){
		$mkt5[]=$list;
	}
	//echo"<pre>";print_r($mkt5);echo"</pre>";
    $smarty->assign("mkt5", $mkt5);
	$smarty->assign("PAGE_TITLE", "Branch Sales Target and Expenses Approval");
   	$smarty->display("mkt5_approval.index.tpl");
}

function load_header(&$mkt4,&$sessioninfo)
{
	global $con,$LANG, $mkt4,$mkt0, $id, $dept_id;
	$id = intval($_REQUEST['id']);
	//$dept_id = intval($_REQUEST['dept_id']);

	$con->sql_query("select * from mkt0 where id=$id");
	$mkt0 = $con->sql_fetchrow();

	//$con->sql_query("select description from category where id = $dept_id");
	//$d = $con->sql_fetchrow();

	$mkt0['branch_id'] = $branch_id;
	//$mkt0['dept_id'] = $dept_id;
	$mkt0['department'] = $d['description'];

	$mkt0['publish_dates'] = unserialize($mkt0['publish_dates']);
	$mkt0['attachments'] = unserialize($mkt0['attachments']);
	if ($mkt0['attachments']['file'])
	{
		foreach ($mkt0['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $mkt0['filepath'][$idx] = "/mkt_attachments/$mkt0[id]/$fn";
		}
	}

	//echo "a=$sessioninfo[id]<br>b=$mkt0[user_id]<br>c=$mkt0[status]";
	/// invalid id
	if (!$mkt0) show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));
	/// owner check and status. if not owner or status not zero (cofirmed) switch to view only mode
	//if ($_REQUEST['a'] == 'open' && ($mkt0['user_id']!=$sessioninfo['id'] || !$mkt0['status']))
		//show_redir("$_SERVER[PHP_SELF]?a=view&id=$id", 'Sales Target', sprintf($LANG['MKT_NO_PERMISSION_TO_EDIT'],$id));
	$mkt0['branches'] = unserialize($mkt0['branches']);

	foreach ($mkt0['branches'] as $bid=>$dummy)
	{

		//$con->sql_query("select mkt4.*,mkt3.offers as m3_offers, mkt3.brands as m3_brands from mkt4 left join mkt3 on (mkt4.mkt0_id = mkt3.mkt0_id and mkt4.dept_id = mkt3.dept_id and mkt4.branch_id = mkt3.branch_id) left join branch on mkt4.branch_id = branch.id where mkt4.mkt0_id = $id and branch.code = '$bid'");
		$con->sql_query("select mkt4.* from mkt4 left join branch on mkt4.branch_id = branch.id where mkt4.mkt0_id = $id and branch.code = '$bid'");

		while($p=$con->sql_fetchrow())
		{

			$p['offers'] = unserialize($p['offers']);
			$p['brands'] = unserialize($p['brands']);

			foreach ($p['offers'] as $r)
			{
				$offers[$r['sku_item_id']][$bid] = $r;
				if (!isset($offers[$r['sku_item_id']]['sku_item_code']))
				{
					$offers[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
					$offers[$r['sku_item_id']]['description'] = $r['description'];
				}
			}

			foreach ($p['brands'] as $r)
			{
				$brands[$r['brand_id']][$bid] = $r;
				$brands[$r['brand_id']]['brand'] = $r['brand'];
			}
		}
	}
	$mkt4['offers']=$offers;
	$mkt4['brands']=$brands;
	//print"<pre>";
	//print_r($mkt4);
	//print"</pre><hr>";
}

?>
