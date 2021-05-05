<?php
// mkt0 can only be created in HQ, there is no branch_id in primary key
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MKT_CREATE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MKT_CREATE', BRANCH_CODE), "/index.php");

if (BRANCH_CODE!='HQ') js_redirect($LANG['MKT_NOT_RUNNING_FROM_HQ'], "/index.php");

$smarty->assign('PAGE_TITLE', 'MKT Create New Offers');

$con->sql_query("select id, code from branch where id>1 order by id");
$smarty->assign("branches", $con->sql_fetchrowset());


if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'view':
		case 'open':
		case 'new':
		    $id = intval($_REQUEST['id']);
		    if ($id)
			{
				$con->sql_query("select * from mkt0 where id = $id");
				$r = $con->sql_fetchrow();
				/// invalid id
				if (!$r) show_redir($_SERVER['PHP_SELF'], 'Sales Target', sprintf($LANG['MKT_INVALID_ID'],$id));
			/// owner check and status. if not owner or status not zero (cofirmed) switch to view only mode
				if ($_REQUEST['a'] == 'open' && ($r['user_id']!=$sessioninfo['id'] || $r['status']>0))
					show_redir("$_SERVER[PHP_SELF]?a=view&id=$id", 'Sales Target', sprintf($LANG['MKT_NO_PERMISSION_TO_EDIT'],$id));

				$r['branches'] = unserialize($r['branches']);
				$r['sales_targets'] = unserialize($r['sales_targets']);
				$r['expenses'] = unserialize($r['expenses']);
				$r['publish_dates'] = unserialize($r['publish_dates']);
				$r['attachments'] = unserialize($r['attachments']);
				if ($r['attachments']['file'])
				{
					foreach ($r['attachments']['file'] as $idx=>$fn)
					{
	        			if ($fn) $r['filepath'][$idx] = "mkt_attachments/$r[id]/$fn";
					}
				}
				$smarty->assign('form', $r);
				
				$b_code=ms(BRANCH_CODE);
				$q2=$con->sql_query("select * from branch where code=$b_code");
				$r2 = $con->sql_fetchrow($q2);
				// get image path
				$hurl = get_branch_file_url($r2['code'], $r2['ip']);
				$smarty->assign("image_path", $hurl);
				//echo"<pre>";print_r($hurl);echo"</pre>";
		    }
		    else
		    {
		        $smarty->assign('default_due_date',strtotime('+2 week'));
			}

			$smarty->display("mkt0.edit.tpl");
		    exit;

		case 'confirm':
		    $is_confirm = 1;
		case 'save':
///////////////////////////////////////////////////////////////////////////////////////////
			$form = $_REQUEST;
		    $errm = validate_data($form,$is_confirm);
			/*$form['offer_from'] = dmy_to_sqldate($_REQUEST['offer_from']);
			$form['offer_to'] = dmy_to_sqldate($_REQUEST['offer_to']);
			
			$mkt_ri=$con->sql_query("select * from mkt_review_items where (date>='$form[offer_from]' and date<='$form[offer_to]')");
			while($mri=$con->sql_fetchrow($mkt_ri)){
			    if(!$mri){
					echo "dsfds$mkt_ri[date]<br>";
				}
			}
			echo"<pre>";print_r($form);echo"</pre>";
			exit;*/
///////////////////////////////////////////////////////////////////////////////////////////
		    if (!$errm)
		    {
		        // make mkt0 actual
			    if ($is_confirm) $form['status'] = 1;
			    if (!$form['id'])
			    {
			        $form['user_id'] = $sessioninfo['id'];
			        $form['added'] = 'CURRENT_TIMESTAMP()';

					$con->sql_query("insert into mkt0 ".mysql_insert_by_field($form,array('branches','user_id','title','offer_from','offer_to','submit_due_date_0','submit_due_date_1','submit_due_date_2','submit_due_date_3','submit_due_date_4','added','status','expenses','sales_targets','publish_dates','attachments','remark')));
					$id = $con->sql_nextid();
             		
             		//$con->sql_query("update mkt0 set  where id=$id");
				}
				else
				{
				    $id = intval($form['id']);
				    $con->sql_query("update mkt0 set ".mysql_update_by_field($form,array('branches','title','offer_from','offer_to','submit_due_date_0','submit_due_date_1','submit_due_date_2','submit_due_date_3','submit_due_date_4','status','expenses','sales_targets','publish_dates','attachments','remark')) . " where id=$id");
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

			    // after save , return to front page
			    if ($is_confirm) {
					$startd = strtotime($form['offer_from']);
					$endd = strtotime($form['offer_to']);
					foreach ($_REQUEST['branches'] as $k=>$v){
						for ($i=$startd;$i<=$endd;$i+=86400){
			    			$d= date("Y-m-d",$i);
							$q1=$con->sql_query("select * from mkt_review_items where date='$d' and branch_id=$v");
							$r1 = $con->sql_fetchrow($q1);
							if($r1){
           						$con->sql_query("update mkt_review_items set promote_status=1 where date='$d' and branch_id=$v");
							}
							else{
								$con->sql_query("insert into mkt_review_items (date, branch_id, normal_forecast, sales_target, sales_achieve, promote_status) values ('$d','$v',0,0,0,'1')");
							}
						}
					}
					log_br($sessioninfo['id'], 'MKT', $id, "Sales Target template Created (ID#$id)");

				}
			    header("Location: /mkt0.php?t=save&id=$id");
		    }
		    else
		    {
		        $smarty->assign("form", $form);
		        $smarty->assign("errm", $errm);
				$smarty->display("mkt0.edit.tpl");

			}
			exit;
			
		case 'cancel':
		    $id = intval($_REQUEST['id']);
		    $con->sql_query("update mkt0 set active=0 where id=$id and user_id=$sessioninfo[id]");
			header("Location: /mkt0.php?t=cancel&id=$id");
			exit;

		case 'delete':
		    $id = intval($_REQUEST['id']);
		    $con->sql_query("delete from mkt0 where id=$id and user_id=$sessioninfo[id]");
			header("Location: /mkt0.php?t=delete&id=$id");
			exit;

		case 'ajax_load_mkt_list':
		    load_mkt_list();
		    $smarty->display("mkt0.home.list.tpl");
		    exit;

		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

$smarty->display('mkt0.home.tpl');

function load_mkt_list()
{
	global $con, $sessioninfo, $smarty, $depts;

	if (!$t) $t = intval($_REQUEST['t']);
	if ($sessioninfo['level']>=9999)
		$owner_check = "";
	else
		$owner_check = "(mkt0.user_id = $sessioninfo[id]) and";

	switch ($t)
	{
	    case 0:
	        $where = 'mkt0.id = ' . mi($_REQUEST['s']) . ' or mkt0.title like ' . ms('%'.$_REQUEST['s'].'%');
	        $_REQUEST['s'] = '';
	        break;

		case 1: // show saved
        	$where = "mkt0.active and mkt0.status = 0";
        	break;

		case 2: // show confirmed
		    $where = "mkt0.active and mkt0.status = 1";
		    break;

		case 3: // show rejected
		   $where = "mkt0.active = 0";
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

	$con->sql_query("select mkt0.*, user.u from mkt0 left join user on user_id = user.id where $owner_check $where order by last_update desc limit $start, $sz");

	$smarty->assign("list", $con->sql_fetchrowset());

}

function validate_data(&$form, $is_confirm)
{
	global $LANG,$con;
	$errm = array();

	if (!$form['branches'])
	{
	    $errm['top'][] = $LANG['MKT_NO_BRANCHES_SELECTED'];
	}
	if (!$form['title'])
	{
	    $errm['top'][] = $LANG['MKT_NO_TITLE'];
	}


	if (dmy_to_time($form['offer_from'])>dmy_to_time($form['offer_to']))
	{
	    $errm['top'][] = $LANG['MKT_INVALID_OFFER_DATE_FROM_TO'];
	}

    // replace date into validate mysql date format
    $form['submit_due_date_0'] = dmy_to_sqldate($form['submit_due_date_0']);
    $form['submit_due_date_1'] = dmy_to_sqldate($form['submit_due_date_1']);
    $form['submit_due_date_2'] = dmy_to_sqldate($form['submit_due_date_2']);
    $form['submit_due_date_3'] = dmy_to_sqldate($form['submit_due_date_3']);
    $form['submit_due_date_4'] = dmy_to_sqldate($form['submit_due_date_4']);

	$form['offer_from'] = dmy_to_sqldate($form['offer_from']);
	$form['offer_to'] = dmy_to_sqldate($form['offer_to']);

	$expenses = array();
    // exclude blank rows
	foreach($form['expenses']['material'] as $k=>$v)
	{
	    if ($v!='')
	    {
	        foreach(array_keys($form['expenses']) as $kk)
	        {
				$expenses[$kk][] = $form['expenses'][$kk][$k];
			}
		}
	}
	$form['expenses'] = $expenses;

	if (!$form['expenses'])
	{
	    $errm['top'][] = $LANG['MKT_NO_EXPENSES'];
	}


    // keep files
    if (!is_dir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments"))
    {
        mkdir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments",0777);
        mkdir("$_SERVER[DOCUMENT_ROOT]/mkt_attachments/tmp",0777);
        //chmod("$_SERVER[DOCUMENT_ROOT]/mkt_attachments");
	}

	
	
	foreach ($_FILES['files']['name'] as $idx => $fn)
	{
	    if ($fn)
	    {
			move_uploaded_file($_FILES['files']['tmp_name'][$idx], "$_SERVER[DOCUMENT_ROOT]/mkt_attachments/tmp/$fn");
			$form['attachments']['file'][$idx] = $fn;
			$form['filepath'][$idx] = "mkt_attachments/tmp/$fn";
      }
	}
	

	if (!$errm)
	{
	    // serialize fields
	    $form['branches'] = serialize($form['branches']);
	    $form['expenses'] = serialize($form['expenses']);
	    $form['publish_dates'] = serialize($form['publish_dates']);
	    $form['attachments'] = serialize($form['attachments']);
	}

	return $errm;
}
?>
