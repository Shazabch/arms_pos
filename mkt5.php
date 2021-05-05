<?php
// mkt0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT5_VIEW') and !privilege('MKT5_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Publishing Planner (HQ)');

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT5_%'");
while ($r = $con->sql_fetchrow()){
	$mkt5_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt5_privilege", $mkt5_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt5.home.list.tpl");
		    exit;

		case 'save':
			if (!privilege('MKT5_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT5_EDIT', BRANCH_CODE), "/mkt5.php?branch_id=$branch_id");
		    $form = $_REQUEST;

	  	    $form['offers'] = serialize($form['offers']);
		    $form['brands'] = serialize($form['brands']);
		    $form['publish_date']=$form['date_id'];
			$form['mkt0_id'] = $form['id'];
		    $form['added'] = 'CURRENT_TIMESTAMP()';
		    $form['status']=0;

		    //if($is_confirm==1)$form['status'] = 1;

			$con->sql_query("replace into mkt5 ".mysql_insert_by_field($form,array('publish_date','offers','brands','status','added','mkt0_id','dept_id'))) or die(mysql_error());

    		$form['user_id'] =$sessioninfo['id'];
    		$con->sql_query("select * from mkt5_dept_approval where dept_id=".mi($form[dept_id])." and mkt0_id=".mi($form[mkt0_id])."");
    		$r = $con->sql_fetchrow();
    		if(!$r){
				$con->sql_query("replace into mkt5_dept_approval ".mysql_insert_by_field($form,array('dept_id','mkt0_id','user_id','status'))) or die(mysql_error());
			}
			else{
				$con->sql_query("update mkt5_dept_approval set ".mysql_update_by_field($form,array('user_id','added','status')) . " where dept_id=".mi($form[dept_id])." and mkt0_id=".mi($form[mkt0_id])."");
			}

			if ($is_confirm)
			{
				log_br($sessioninfo['id'], 'MKT', $form['id'], "MKT5 Confirmed (ID#form[$id], Date #$form[date_id], Dept: $form[dept_id])");
			    header("Location: /mkt5.php?t=confirm&id=$form[id]");
			}
			else
			    header("Location: /mkt5.php?t=save&id=$form[id]");
			exit;
			    	
		case 'open':
  	    case 'view':
	    	$id = intval($_REQUEST['id']);
		    $dept_id = intval($_REQUEST['dept_id']);
		    $date_id=ms($_REQUEST['date_id']);

		    $where = array();
		    $where[] = "mkt5.mkt0_id = " . ms($id);
		    $where[] = "mkt5.dept_id = " . ms($dept_id);
		    $where[] = "publish_date = " . $date_id;
		    $where = join(" and ", $where);
		    
			$con->sql_query("select mkt5.*, mda.status as status, mda.approved as approved
from mkt5
left join mkt5_dept_approval mda on mda.dept_id=mkt5.dept_id and mkt5.mkt0_id=mda.mkt0_id
 where $where");
			$mkt5 = $con->sql_fetchrow();
    		$form['offers'] = unserialize($mkt5['offers']);
			$form['brands'] = unserialize($mkt5['brands']);
		    
		    if (!$id || !$dept_id || !$date_id)
			{
				header("Location: /mkt5.php");
				exit;
			}
		    
		    $mkt4 = array();
			load_header($mkt4,$sessioninfo);
			
			$smarty->assign("branches", $mkt0['branches']);

			$con->sql_query("select * from branch where active order by id");
			$smarty->assign("db_branch", $con->sql_fetchrowset());
			
			$smarty->assign("mkt4", $mkt4);
			$smarty->assign("mkt5", $mkt5);
			$smarty->assign("mkt0", $mkt0);
			$smarty->assign("form", $form);
			$smarty->assign("date_id", $_REQUEST['date_id']);
			
			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);
			
			//print "<pre>";print_r($mkt5);print "</pre>";
			if ($form['m3_approved'] || ($form['m3_status']!=0 && $form['m3_status']!=2) || ($form['m3_user_id']!=$sessioninfo['id'] && $form['m3_user_id']>0))
			{
			    $_REQUEST['a'] = 'view';
			}
			$smarty->display('mkt5.edit.tpl');
		    exit;
		    
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt5.home.tpl');

function load_mkt_list(){

	global $con, $sessioninfo, $smarty, $depts;

	if (!$t) $t = intval($_REQUEST['t']);
	/*if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else
		$owner_check = "(mkt0.user_id = $sessioninfo[id]) and";
	*/
	$branch_id=get_request_branch();
	$where = "mkt0.active and mkt0.status = 1 and mkt0.branches like '%$branch_id%'";
	switch ($t)
	{
	    case 0:
	        $where .= ' and mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%');
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved
        	$where2 = " and (mda.status is null or (mda.status = 0 and mda.approved=0))";
        	break;

		case 2: // show waiting approval
		    $where2 = " and mda.status = 1 and mda.approved=0";
		    break;

		case 3: // show rejected
		    $where2 = " and mda.status = 2 and mda.approved=0";
		    break;

		case 4: // show approved
		    $where2 = " and mda.approved = 1";
		    break;
	}
	
	
	if (isset($_REQUEST['s']))
		$s = intval($_REQUEST['s']);

	$con->sql_query("select count(*),mkt0.id
from mkt4
left join mkt0 on mkt4.mkt0_id = mkt0.id
left join mkt5_dept_approval mda on mda.dept_id = mkt4.dept_id and mda.mkt0_id=mkt4.mkt0_id
where $owner_check $where $where2
group by mkt0.id
order by mkt4.last_update desc");

	$i=0;
	$pg = "<b>Select </b> <select onchange=\"list_sel($t,this.value)\">";
	while($r = $con->sql_fetchrow()){
		$pg .= "<option value=$r[1]";
		if ($i==0 && !$s){
		    $s=$r[1];
			$pg .= " selected";
		}
		elseif($s==$r[1]){
			$pg .= " selected";
		}
		$title=sprintf("MKT%05d",$r[1]);
		$pg .= ">$title</option>";
		$i++;
	}
	$pg .= "</select>";
	$smarty->assign("total_mkt", $i);
	$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");

	// pagination
	/*$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 25;
	$res=$con->sql_query("select mkt0.*,user.u
from mkt0
left join user on mkt0.user_id = user.id
where $owner_check $where order by mkt0.last_update desc");
	$list=array();
	while($r=$con->sql_fetchrow($res))
	{
		$con->sql_query("select count(*)
	from mkt4
	left join branch on branch_id = branch.id
	left join mkt5_dept_approval mda on mda.dept_id = mkt4.dept_id and mda.mkt0_id=mkt4.mkt0_id
	left join branch_approval_history bah on bah.id = mda.approval_history_id
	left join category dept on mkt4.dept_id =dept.id
	where mkt4.mkt0_id ='$r[id]' $where2");
	}
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
	}*/

	$res=$con->sql_query("select mkt0.*,user.u
from mkt0
left join user on mkt0.user_id = user.id
where $owner_check $where and mkt0.id=".mi($s)." order by mkt0.last_update desc");
	$list=array();
	while($r=$con->sql_fetchrow($res))
	{
		$r['branches'] = unserialize($r['branches']);
     	$r['publish_dates'] = unserialize($r['publish_dates']);


		$con->sql_query("select mda.status as mda_status, mda.approved as mda_approved,  mda.approval_history_id as mda_approval_history_id, mkt4.*,branch.code as branch_code, dept.description as dept, bah.approvals as approvals
from mkt4
left join branch on branch_id = branch.id
left join mkt5_dept_approval mda on mda.dept_id = mkt4.dept_id and mda.mkt0_id=mkt4.mkt0_id
left join approval_history bah on bah.id = mda.approval_history_id
left join category dept on mkt4.dept_id =dept.id
where mkt4.mkt0_id ='$r[id]' $where2");
		
	    while($p=$con->sql_fetchrow()){
	        $r['submitted'][$p['dept']]['id'] = $p['dept_id'];
			$r['submitted'][$p['dept']][$p['branch_code']] = $p['status'];
		    $r['check'][$p['dept']]['mda_status'] = $p['mda_status'];
	 	    $r['check'][$p['dept']]['mda_approved'] = $p['mda_approved'];
	 	    $r['check'][$p['dept']]['mda_approval_history_id'] = $p['mda_approval_history_id'];
	 	    $r['app']['approvals']=$p['approvals'];
		}
		
		if ($r['submitted']){
			// check each depart if all required branch submitted their MKT4, set 'complete' status
			foreach (array_keys($r['submitted']) as $dept){
			
				$r['complete'][$dept] = 1;
				foreach($r['branches'] as $k=>$dummy){
				    if (!$r['submitted'][$dept][$k]){
		    			$r['complete'][$dept] = 0;
						break;
					}
				}
			}
		    $list[] = $r;
	    }
	}
	//print "<pre>";print_r($list);print "</pre>";
	$smarty->assign("list", $list);
}


function load_header(&$mkt4,&$sessioninfo)
{
	global $con,$LANG, $mkt4,$mkt5,$mkt0, $id, $dept_id;
	$id = intval($_REQUEST['id']);
	$dept_id = intval($_REQUEST['dept_id']);
	$date_id = ms($_REQUEST['date_id']);

	$con->sql_query("select * from mkt0 where id=$id");
	$mkt0 = $con->sql_fetchrow();

	$con->sql_query("select description from category where id = $dept_id");
	$d = $con->sql_fetchrow();

	$mkt0['branch_id'] = $branch_id;
	$mkt0['dept_id'] = $dept_id;
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

		$con->sql_query("select mkt4.*,mkt3.offers as m3_offers, mkt3.brands as m3_brands from mkt4 left join mkt3 on (mkt4.mkt0_id = mkt3.mkt0_id and mkt4.dept_id = mkt3.dept_id and mkt4.branch_id = mkt3.branch_id) left join branch on mkt4.branch_id = branch.id where mkt4.mkt0_id = $id and branch.code = '$bid' and mkt4.dept_id = $dept_id");
		while($p=$con->sql_fetchrow())
		{

			$p['offers'] = unserialize($p['offers']);
			$p['brands'] = unserialize($p['brands']);
			//print "<pre>";print_r($p);print "</pre>";
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
		$mkt4['offers']=$offers;
		$mkt4['brands']=$brands;
	}

}

?>
