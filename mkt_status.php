<?php
// mkt0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT5_VIEW') and !privilege('MKT5_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Overview of Offer and Discount Planner (HQ)');
$hqcon = connect_hq();
//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT5_%'");
while ($r = $con->sql_fetchrow()){
	$mkt5_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt5_privilege", $mkt5_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id =get_request_branch();

$b_code=ms('HQ');
$q2=$con->sql_query("select * from branch where code=$b_code");
$r2 = $con->sql_fetchrow($q2);
// get image path
$hurl = get_branch_file_url($r2['code'], $r2['ip']);
$smarty->assign("image_path", $hurl);

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'open_by_dept':
			load_form();
			$smarty->assign("form", $form);
		    $mkt4 = array();
			load_header($mkt4,$sessioninfo);
			$smarty->assign("branches", $mkt0['branches']);
			$con->sql_query("select * from branch where active order by id");
			$smarty->assign("db_branch", $con->sql_fetchrowset());
			$smarty->assign("mkt4", $mkt4);
			$smarty->assign("mkt0", $mkt0);
			$smarty->assign("check", $check);
		    $smarty->assign("errm", $errm);
			$smarty->display('mkt_status.edit.tpl');
		    exit;

  	    case 'view':
	    	$id = intval($_REQUEST['id']);
            $form = array();
            if (!$id)
			{
				header("Location: /mkt_status.php");
				exit;
			}

			$r1=$con->sql_query("select *, category.description as dept from mkt5 left join category on dept_id = category.id where mkt0_id=".ms($id)." order by  publish_date");
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
			//if($mkt5['status']==1){$_REQUEST['a']='view';$smarty->assign("a", 'view');}
			//else {$_REQUEST['a']='open';$smarty->assign("a", 'open');}

		    $mkt4 = array();
			load_header($mkt4,$sessioninfo);
			
			$smarty->assign("branches", $mkt0['branches']);

			$con->sql_query("select * from branch where active order by id");
			$smarty->assign("db_branch", $con->sql_fetchrowset());

			$smarty->assign("mkt4", $mkt4);
			$smarty->assign("mkt0", $mkt0);

			//print "<pre>";print_r($form);print "</pre>";
			$smarty->display('mkt_status.edit.tpl');
		    exit;
		    
		case 'confirm':
			if (!privilege('MKT5_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_EDIT', BRANCH_CODE), "/mkt5.php?branch_id=$branch_id");
			$form = $_REQUEST;
        	$id = intval($form['id']);
        	$dept_id = intval($form['dept_id']);
        	$ah_id = intval($form['approval_history_id']);
            $last_approval = false;
        	
 		    // if approval_history_id defined, just restore the approvals
		    if ($ah_id){
		        $con->sql_query("update approval_history set approvals = flow_approvals where id = ".mi($ah_id)."");
                $con->sql_query("select id,approvals from approval_history where id = ".mi($ah_id)."");
				$astat = $con->sql_fetchrow();
		        if ($astat[1] == '|') {
					$last_approval = true;
				}
			}
		    else{
				// create approval history
				$astat = check_and_create_approval('MKT5', $sessioninfo['branch_id'], 'mkt5','',$hqcon);
				if (!$astat){
					$errm['top'][] = $LANG['MKT5_NO_APPROVAL_FLOW'];
				}
				else{
					$ah_id = $astat[0];
	       			if ($astat[1] == '|') $last_approval = true;
				}
			}
			
			if (!$errm){
		    	$mda['user_id'] = intval($sessioninfo['id']);
		    	$mda['status'] = '1';
		    	$mda['added'] = 'CURRENT_TIMESTAMP()';
		    	$mda['approval_history_id']=$ah_id;
			    if ($last_approval) $mda['approved'] = 1;

				$con->sql_query("update mkt5_dept_approval set ".mysql_update_by_field($mda,array('status','approved','user_id','added','approval_history_id')) . " where dept_id=".mi($dept_id)." and mkt0_id=".mi($id)."");
				header("Location: /mkt5.php?t=confirm&id=$id");
			}
			else{
				$form=load_form();
				$smarty->assign("form", $form);
				$mkt4 = array();
				load_header($mkt4,$sessioninfo);
				$smarty->assign("branches", $mkt0['branches']);
				$con->sql_query("select * from branch where active order by id");
				$smarty->assign("db_branch", $con->sql_fetchrowset());
				$smarty->assign("mkt4", $mkt4);
				$smarty->assign("mkt0", $mkt0);
				$smarty->assign("check", $check);
		    	$smarty->assign("errm", $errm);
				$smarty->display('mkt_status.edit.tpl');
			}
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

			// update approval records
			$con->sql_query("insert into approval_history_items (approval_history_id, user_id, status, log) values ($aid, $sessioninfo[id], $status, $sz)");

			$con->sql_query("update approval_history set status=$status,approvals = ".ms($approvals)." where id = $aid");

			$con->sql_query("update mkt5_dept_approval set status=$status, approved=$approved where mkt0_id=$id and dept_id=$dept_id");

			if ($approved)
				log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Fully Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==1)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Approved by $sessioninfo[u] (ID#$id)");
			elseif ($status==2)
			    log_br($sessioninfo['id'], 'MKT', $id, "MKT5 Rejected by $sessioninfo[u] (ID#$id)");
			else
			    die("WTF?");

			header("Location: /mkt5.php?t=$_REQUEST[a]&id=$id");
		    exit;
		    
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt_status.home.tpl');

function load_mkt_list(){

	global $con, $sessioninfo, $smarty, $depts;

	if (!$t) $t = intval($_REQUEST['t']);
	if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else
		$owner_check = "(mkt0.user_id = $sessioninfo[id]) and";

	$where = 'mkt0.active and mkt0.status = 1';
	switch ($t)
	{
	    case 0:
	        $where .= ' and mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%');
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved
        	break;

	}

	// pagination
	$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$con->sql_query("select count(*) from mkt0 where $owner_check $where");
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

	$res=$con->sql_query("select mkt0.*,user.u from mkt0 left join user on user_id = user.id where $owner_check $where order by last_update desc limit $start, $sz");
	$list=array();
	while($r=$con->sql_fetchrow($res))
	{
	    $r['branches'] = unserialize($r['branches']);
     	$r['publish_dates'] = unserialize($r['publish_dates']);
		
		$con->sql_query("select branch_id, dept_id,branch.code as branch_code, dept.description as dept, status from mkt4 left join branch on branch_id = branch.id left join category dept on dept_id = dept.id where mkt0_id = $r[id]");
		
	    while($p=$con->sql_fetchrow())
	    {
	        $r['submitted'][$p['dept']]['id'] = $p['dept_id'];
			$r['submitted'][$p['dept']][$p['branch_code']] = $p['status'];
		}
		
		if ($r['submitted'])
		{
			// check each depart if all required branch submitted their MKT4, set 'complete' status
			foreach (array_keys($r['submitted']) as $dept)
			{
				$r['complete'][$dept] = 1;
				foreach($r['branches'] as $k=>$dummy)
				{
				    if (!$r['submitted'][$dept][$k])
					{
		    			$r['complete'][$dept] = 0;
						break;
					}
				}
			}
		    $list[] = $r;
	    }
	}
	$smarty->assign("list", $list);
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
			if ($fn) $mkt0['filepath'][$idx] = "mkt_attachments/$mkt0[id]/$fn";
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
}


function load_form(){

	global $con, $sessioninfo, $smarty, $LANG,$form,$check,$mkt5;

	$id = intval($_REQUEST['id']);
	$dept_id = intval($_REQUEST['dept_id']);

	$form = array();
	if (!$id){
		header("Location: /mkt_status.php");
		exit;
	}

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
	from approval_history_items i
	left join approval_history h on i.approval_history_id = h.id
	left join user on i.user_id = user.id
	where h.ref_table = 'mkt5' and i.approval_history_id = $check[approval_history_id] order by i.timestamp");
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
	return $form;
}
?>
