<?php
// mk0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT3_VIEW') and !privilege('MKT3_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'Brand and Item Offer Proposal');

//added to permit user to login in wherever to modify the permitted branch -070507
$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT3_%'");
while ($r = $con->sql_fetchrow()){
	$mkt3_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt3_privilege", $mkt3_privilege);
//print_r($mkt1_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

$branch_id=get_request_branch();
/*
// get the allowed line and department for this user
$con->sql_query("select line.id as line_id, line.description as line, dept.id as dept_id, dept.description as dept from category dept left join category line on dept.root_id = line.id where dept.id in (".join(",",array_keys($sessioninfo['departments'])).") and dept.level=2 and dept.active and line.active order by line,dept");
$depts = array();
$lines = array();
while($r=$con->sql_fetchrow())
{
	$depts[] = $r;
	$lines[$r['line']]['id'] = $r['line_id'];
	$lines[$r['line']]['dept'][] = array('description'=>$r['dept'], 'id'=>$r['dept_id']);

}
while($r=$con->sql_fetchrow())
{
	$depts[] = $r;
	$lines[$r['line']]['id'] = $r['line_id'];
	$lines[$r['line']]['dept'][] = array('description'=>$r['dept'], 'id'=>$r['dept_id']);
}

$smarty->assign("departments", $depts);
$smarty->assign("lines", $lines);*/

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
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

			header("Location: /mkt3.php?t=confirm&id=$id");
		    exit;
		    
		    
	    case 'view':
		case 'open':
		    $id = intval($_REQUEST['id']);
		    $branch_id = intval($_REQUEST['branch_id']);
		    $dept_id = intval($_REQUEST['dept_id']);
			
			//to reduce the crashs between mkt3 owner on editing	
		    $con->sql_query("select * from mkt3 where mkt0_id=$id and branch_id=$branch_id and dept_id=$dept_id");
			$r1 = $con->sql_fetchrow();
		    if (!$r1)
		    {
				$con->sql_query("insert into mkt3 (branch_id, mkt0_id, dept_id, user_id) values ('$branch_id','$id','$dept_id','$sessioninfo[id]')");
			}
					    
		    if (!$id || !$branch_id)
			{
				header("Location: /mkt3.php?branch_id=$branch_id");
				exit;
			}
			$form = array();
			load_header($form);
			
			$con->sql_query("select mkt3.*,bah.approvals
from mkt3
left join branch_approval_history bah on bah.id = mkt3.approval_history_id and bah.branch_id = mkt3.branch_id
where mkt0_id = $id and mkt3.branch_id=$branch_id and dept_id = $dept_id");
			$mkt3 = $con->sql_fetchrow();
			$mkt3['offers'] = unserialize($mkt3['offers']);
			$mkt3['brands'] = unserialize($mkt3['brands']);

			//echo"<pre>";print_r($form);echo"</pre>";
			  // if viewer is current approval, set is_approval=1
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

			$smarty->assign("form", $form);
			
			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);
			
			//echo"<pre>";print_r($form);echo"</pre>";
			if ($form['m3_approved'] || ($form['m3_status']!=0 && $form['m3_status']!=2) || ($form['m3_user_id']!=$sessioninfo['id'] && $form['m3_user_id']>0))
			{
			    $_REQUEST['a'] = 'view';
			}
	        $smarty->display("mkt3.edit.tpl");
	        exit;
	        
		case 'confirm':
			if (!privilege('MKT3_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_EDIT', BRANCH_CODE), "/mkt3.php?branch_id=$branch_id");
		    $is_confirm=1;
		case 'save':
			if (!privilege('MKT3_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT3_EDIT', BRANCH_CODE), "/mkt3.php?branch_id=$branch_id");

		   	$form = $_REQUEST;
		    $errm = validate_data($form,$is_confirm);
            $last_approval = false;
            $brand=array();
            $offer=array();

			if (!$errm && $is_confirm){

			    // if approval_history_id defined, just restore the approvals
			    if ($_REQUEST['approval_history_id']){
			        $con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($_REQUEST['approval_history_id'])." and branch_id = ".mi($_REQUEST['branch_id']));
                    $con->sql_query("select id,approvals from branch_approval_history where id = ".mi($_REQUEST['approval_history_id'])." and branch_id = ".mi($_REQUEST['branch_id']));
					$astat = $con->sql_fetchrow();
			        if ($astat[1] == '|') {
						$last_approval = true;
					}
				}
			    else{
					// create approval history
					$astat = check_and_create_branch_approval('MKT3', $branch_id, 'mkt3');
					if (!$astat){
						$errm['top'][] = $LANG['MKT3_NO_APPROVAL_FLOW'];
					}
					else{
						$form['approval_history_id'] = $astat[0];
		       			if ($astat[1] == '|') $last_approval = true;
					}
				}
			}

		    if (!$errm)
		    {
			    $form['user_id'] = $sessioninfo['id'];
		        $form['mkt0_id'] = $form['id'];
		        $form['added'] = 'CURRENT_TIMESTAMP()';
			    $form['approved'] = 0;
		        
			    if ($is_confirm) $form['status'] = 1;
			    if ($last_approval) $form['approved'] = 1;

		        foreach ($form['offers']['sku_item_code'] as $i => $code)
		        {
		            // skip empty code
		            if ($code){
			            // save
			            foreach (array_keys($form['offers']) as $k){
						    $r[$k] = $form['offers'][$k][$i];
						}
						$offer[]=$r;
		            }
				}
		        $form['offers'] = serialize($offer);

		        foreach ($form['brands']['brand'] as $i => $code)
		        {
		            // skip empty code
		            if ($code){
			            // save
			            foreach (array_keys($form['brands']) as $k){
						    $r[$k] = $form['brands'][$k][$i];
						}
						$brand[]=$r;
		            }
				}
		        $form['brands'] = serialize($brand);

			    $id = intval($form['id']);
			    $branch_id = intval($form['branch_id']);
			    $dept_id = intval($form['dept_id']);
			    $con->sql_query("select * from mkt3 where mkt0_id=$id and branch_id=$branch_id and dept_id=$dept_id");
				$r1 = $con->sql_fetchrow();
			    if (!$r1)
			    {
					$con->sql_query("insert into mkt3 ".mysql_insert_by_field($form,array('mkt0_id','branch_id','dept_id','user_id','offers','brands','added','status','approved','approval_history_id'))) or die(mysql_error());
				}
				else{
				    //echo "DFDSF=$id=$branch_id=$dept_id";
				    $con->sql_query("update mkt3 set ".mysql_update_by_field($form,array('offers','brands','added','status','approved','approval_history_id')) . " where mkt0_id=$id and branch_id=$branch_id and dept_id=$dept_id") or die(mysql_error());
				}


			    // after save , return to front page
			    if ($is_confirm)
				{
					log_br($sessioninfo['id'], 'MKT', $form['id'], "MKT3 Confirmed (ID#form[$id], Branch #$branch_id, Dept: $form[dept_id])");
			    	header("Location: /mkt3.php?t=confirm&id=$form[id]&branch_id=$branch_id&sdfsdfdf=$form[approved]");
			    }
			    else
			    	header("Location: /mkt3.php?t=save&id=$form[id]&branch_id=$branch_id");
		    }
		    else
		    {
				load_header($form);

		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				$smarty->display("mkt3.edit.tpl");

			}
			exit;
			
		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt3.home.list.tpl");
		    exit;


		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt3.home.tpl');

function load_mkt_list(){
	global $con, $sessioninfo, $smarty, $depts;
	$branch_id=get_request_branch();
	$smarty->assign("branch_id", $branch_id);
	if (!$t) $t = intval($_REQUEST['t']);

	$owner_check = "";
	$owner_check = "((mkt1.user_id = $sessioninfo[id] and mkt1.branch_id = $branch_id) or ((mkt3.user_id=$sessioninfo[id]) or (mkt3.user_id=$sessioninfo[id] or mkt3.user_id is null)) or (mkt0.user_id=$sessioninfo[id])) and";

	$where = "mkt1.approved=1 and mkt2.status and mkt2.dept_id in (".join(",",array_keys($sessioninfo['departments'])).")  and mkt2.branch_id=$branch_id and mkt0.branches like '%$branch_id%'";

	switch ($t)
	{
	    case 0:
	        $where .= ' and (mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%') . ')';
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved
        	$where .= " and (mkt3.status is null or (mkt3.status=0 and mkt3.approved=0))";
        	break;

		case 2: // show waiting approval
		    $where .= " and mkt3.status = 1 and mkt3.approved=0";
		    break;

		case 3: // show rejected
		    $where .= " and mkt3.status = 2 and mkt3.approved=0";
		    break;

		case 4: // show approved
		    $where .= " and mkt3.approved = 1";
		    break;

	}
	

	$s = intval($_REQUEST['s']);
	$con->sql_query("select count(*),mkt0.id
from mkt0
left join mkt2 on mkt0.id = mkt2.mkt0_id
left join mkt1 on mkt2.mkt0_id = mkt1.mkt0_id and mkt1.branch_id = $branch_id
left join mkt3 on mkt2.mkt0_id = mkt3.mkt0_id and mkt3.branch_id= $branch_id and mkt2.dept_id = mkt3.dept_id
where $owner_check $where and mkt0.status and mkt0.active and mkt2.status and mkt2.branch_id=$branch_id
group by mkt0.id order by mkt2.last_update desc
");

	$i=0;
	$pg = "<b>Select </b> <select onchange=\"list_sel($t,this.value)\">";
	while($r = $con->sql_fetchrow()){
		$pg .= "<option value=$r[1]";
		if (!$s){
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
		$sz = 1;

	$con->sql_query("select count(distinct(mkt0.id))
from mkt2
left join mkt0 on mkt2.mkt0_id = mkt0.id
left join mkt1 on mkt2.mkt0_id = mkt1.mkt0_id and mkt1.branch_id = $branch_id
left join mkt3 on mkt2.mkt0_id = mkt3.mkt0_id and mkt3.branch_id= $branch_id and mkt2.dept_id = mkt3.dept_id where
$owner_check $where");

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

	$q1=$con->sql_query("select *
from mkt2
left join mkt0 on mkt2.mkt0_id = mkt0.id
left join mkt1 on mkt2.mkt0_id = mkt1.mkt0_id and mkt1.branch_id = $branch_id
left join mkt3 on mkt2.mkt0_id = mkt3.mkt0_id and mkt3.branch_id= $branch_id and mkt2.dept_id = mkt3.dept_id where
$owner_check $where group by mkt0.id order by mkt2.last_update desc limit $start, $sz");

while($r1=$con->sql_fetchrow($q1)){*/

$q2=$con->sql_query("select mkt0.*, mkt2.*, mkt3.*, mkt2.dept_id, category.description as dept, 	user.u, user2.u as create_u, bah.approvals
from mkt2
left join mkt0 on mkt2.mkt0_id = mkt0.id
left join mkt1 on mkt2.mkt0_id = mkt1.mkt0_id and mkt1.branch_id = $branch_id
left join mkt3 on mkt2.mkt0_id = mkt3.mkt0_id and mkt3.branch_id= $branch_id and mkt2.dept_id = mkt3.dept_id
left join user on mkt3.user_id = user.id
left join user user2 on mkt0.user_id = user2.id
left join branch_approval_history bah on bah.id = mkt3.approval_history_id and bah.branch_id = mkt3.branch_id
left join category on mkt2.dept_id = category.id where
$owner_check $where and mkt0.id=".mi($s)." order by mkt2.last_update desc");
while($r2=$con->sql_fetchrow($q2)){
	$temp[]=$r2;
}
$smarty->assign("list", $temp);
//echo"<pre>";print_r($temp);echo"</pre>";

}

function load_header(&$form){

	global $con, $LANG,$smarty;
	
    $id = intval($_REQUEST['id']);
	$branch_id=get_request_branch();
	$smarty->assign("branch_id", $branch_id);
    $dept_id = intval($_REQUEST['dept_id']);

	$con->sql_query("select id from mkt0 where id=$id");
	$mkt0 = $con->sql_fetchrow();
	/// invalid id
	if (!$mkt0)
		show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));

    $con->sql_query("select mkt0.*, mkt3.status as m3_status, mkt3.approved as m3_aproved, mkt3.user_id as m3_user_id, branch.code as current_branch
from mkt0
left join branch on branch.id=$branch_id
left join mkt3 on mkt3.mkt0_id=mkt0.id
where mkt0.id = $id and mkt3.dept_id=$dept_id and mkt3.branch_id=$branch_id");
	$header = $con->sql_fetchrow();
	
    $header['publish_dates'] = unserialize($header['publish_dates']);
	$header['attachments'] = unserialize($header['attachments']);
	if ($header['attachments']['file'])
	{
		foreach ($header['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $header['filepath'][$idx] = "mkt_attachments/$header[id]/$fn";
		}
	}
	$con->sql_query("select description from category where id = $dept_id");
	$r = $con->sql_fetchrow();

	$header['branch_id'] = $branch_id;
	$header['dept_id'] = $dept_id;
	$header['department'] = $r['description'];
	$header['branches'] = unserialize($header['branches']);
	
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


function validate_data(&$form, $is_confirm){

	global $LANG;
	$errm = array();

    // exclude blank rows from offer items proposal
	$offers = array();
	foreach($form['offers']['sku_item_code'] as $k=>$v)
	{
	    if ($v!='')
	    {
	        foreach(array_keys($form['offers']) as $kk)
	        {
				$offers[$kk][] = $form['offers'][$kk][$k];
			}
		}
	}
	$form['offers'] = $offers;

	if (!$form['offers'] && $form['limit']['min_offer']!=0)
	{
		$errm['offers'][] = $LANG['MKT3_NO_OFFERS'];
	}
	elseif (count($form['offers']['sku_item_code']) < $form['limit']['min_offer'])
	{
	    $errm['offers'][] = sprintf($LANG['MKT3_NEED_AT_LEST_X_OFFERS'], $form['limit']['min_offer']);
	}

    // exclude blank rows from brand proposal
	$brands = array();
	foreach($form['brands']['brand'] as $k=>$v)
	{
	    if ($v!='')
	    {
	        foreach(array_keys($form['brands']) as $kk)
	        {
				$brands[$kk][] = $form['brands'][$kk][$k];
			}
		}
	}
	$form['brands'] = $brands;

	if (!$form['brands'] && $form['limit']['min_brand']!=0)
	{
	    $errm['brands'][] = $LANG['MKT3_NO_BRANDS'];
	}
	elseif (count($form['brands']['brand']) < $form['limit']['min_brand'])
	{
	    $errm['brands'][] = sprintf($LANG['MKT3_NEED_AT_LEST_X_BRANDS'], $form['limit']['min_brand']);
	}

	return $errm;
}?>
