<?php
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT6_VIEW') and !privilege('MKT6_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT6_VIEW / EDIT', BRANCH_CODE), "/index.php");

$smarty->assign('PAGE_TITLE', 'A&P Materials Review');

$con->sql_query("select * from user_privilege where user_id=$sessioninfo[id] and privilege_code like 'MKT6_%'");
while ($r = $con->sql_fetchrow()){
	$mkt6_privilege[$r['privilege_code']][$r['branch_id']]=1;
}
$smarty->assign("mkt6_privilege", $mkt6_privilege);

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt6.home.list.tpl");
		    exit;

  	    case 'view':
	    	$mkt0_id = intval($_REQUEST['mkt0_id']);
            $form = array();
            if (!$mkt0_id)
			{
				header("Location: /mkt6.php");
				exit;
			}
			load_header();
			$smarty->assign("branches", $mkt0['branches']);
			$smarty->assign("mkt0", $mkt0);

			$con->sql_query("select *, user.u as user from mkt6_approval left join user on user_id = user.id where mkt0_id=".ms($mkt0_id)." order by date desc");
			$smarty->assign("form",$con->sql_fetchrowset());
			$form=$con->sql_fetchrowset();
			$con->sql_query("select * from mkt6_attachment where mkt0_id=".ms($_REQUEST[mkt0_id])."");
			$mkt6=$con->sql_fetchrowset();
			$smarty->assign("mkt6_attach",$mkt6);
			$num_row=$con->sql_numrows();
			$smarty->assign("num_row", $num_row);
			
			$b_code=ms('HQ');
			$q2=$con->sql_query("select * from branch where code=$b_code");
			$r2 = $con->sql_fetchrow($q2);
			// get image path
			$hurl = get_branch_file_url($r2['code'], $r2['ip']);
			$smarty->assign("image_path", $hurl);

			$smarty->display('mkt6.edit.tpl');
		    exit;

		case 'save':
			if (!privilege('MKT6_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT6_EDIT', BRANCH_CODE), "/mkt6.php");
            // keep files
            if($_REQUEST['user_id']==$sessioninfo[id]){
    			if (!is_dir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments")){
        			mkdir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments",0777);
        			mkdir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments/tmp",0777);
				}
				$form=$_REQUEST;
                $form['date']='CURRENT_TIMESTAMP()';
                $id = intval($_REQUEST['mkt0_id']);

				foreach ($_FILES['files']['name'] as $idx => $fn){
	    			if ($fn && $_REQUEST['attachments'][$idx]){
						move_uploaded_file($_FILES['files']['tmp_name'][$idx], "$_SERVER[DOCUMENT_ROOT]/mkt_attachments/tmp/$fn");
						$form['file'] = $fn;
						$form['name'] = $_REQUEST['attachments'][$idx];
						$form['filepath'][$idx] = "mkt_attachments/tmp/$fn";
						$con->sql_query("insert into mkt6_attachment ".mysql_insert_by_field($form,array('file','name','date','mkt0_id'))) or die(mysql_error());

      				}
				}
				// handle file uploaded
				$filedir = "$_SERVER[DOCUMENT_ROOT]/mkt_attachments/$id";
				if (!is_dir($filedir)) mkdir($filedir,0777);
				foreach ($form['filepath'] as $idx=>$fn)
				{
				    $filename = basename($fn);
					// if file is in tmp, move to $filedir
					if (preg_match("/\/tmp/",$fn)) @rename($fn, "$filedir/$filename");
				}
			}
			break;

		case 'save_comment':
			if (!privilege('MKT6_EDIT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT6_EDIT', BRANCH_CODE), "/mkt6.php");
			$form=$_REQUEST;
			$form['user_id']=$sessioninfo[id];
			foreach(array_keys($form['attachment_id']) as $i){
				//echo "$i<br>";
				$insert_item = array();
				$insert_item['attachment_id'] = $form['attachment_id'][$i];
				$insert_item['user_id'] = $form['user_id'];
				$insert_item['mkt0_id'] = $form['mkt0_id'];
				$insert_item['date'] = 'CURRENT_TIMESTAMP()';
				$insert_item['comment'] = $form['comment'][$i];
				$insert_item['approval'] = $form['approval'][$i];
				if($form['approval'][$i]!=''){
					$con->sql_query("insert into mkt6_approval ".mysql_insert_by_field($insert_item)) or die(mysql_error());
				}
			}
			break;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}
$smarty->display('mkt6.home.tpl');

function load_mkt_list(){
	global $con, $sessioninfo, $smarty, $depts;

	if (!$t) $t = intval($_REQUEST['t']);
	$branch_id=get_request_branch();
	/*if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else
		$owner_check = "(mkt0.user_id = $sessioninfo[id] or ) and";*/

	//$where = 'mkt0.active and mkt0.status';
	
	//all dept in mkt5 completed approve only can show in mkt6
	$where = "mkt0.active and mkt0.status and mkt0.mkt5_status and mkt0.branches like '%$branch_id%'";
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
	/*$start = intval($_REQUEST['s']);
	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else
		$sz = 1;
	$con->sql_query("select count(*)
 from mkt0 where $owner_check $where");
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
left join user on user_id = user.id
where $owner_check $where
order by last_update desc");
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


function load_header(){
	global $con,$LANG,$mkt0,$sessioninfo;
	$mkt0_id = intval($_REQUEST['mkt0_id']);

	$con->sql_query("select * from mkt0 where id=$mkt0_id");
	$mkt0 = $con->sql_fetchrow();
	$mkt0['publish_dates'] = unserialize($mkt0['publish_dates']);
	$mkt0['branches'] = unserialize($mkt0['branches']);
	$mkt0['attachments'] = unserialize($mkt0['attachments']);
	if ($mkt0['attachments']['file'])
	{
		foreach ($mkt0['attachments']['file'] as $idx=>$fn)
		{
			if ($fn) $mkt0['filepath'][$idx] = "mkt_attachments/$mkt0[id]/$fn";
		}
	}
}

?>
