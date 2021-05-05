<?php
/*
REVISION HISTORY
================
2/29/2008 5:20:16 PM gary
- add searching function.

29/6/2009 5:56:30 PM yinsee
- allow XLS upload
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) js_redirect('Access Denied', "/index.php");

$smarty->assign('PAGE_TITLE', 'ARMS Request Tracker');

define('TRACKER_FILES', $_SERVER['DOCUMENT_ROOT']."/tracker_files");

$type = array (
	array("type" => "bugs"),
	array("type" => "enhance"),
	array("type" => "idea"),
	array("type" => "new"),
	array("type" => "cancelled"),
	array("type" => "nogroup", "approvals"=>0)
);
$smarty->assign("type", $type);

if ($_REQUEST['a']){
	switch ($_REQUEST['a']){
	
		case 'search':
			$search=$_REQUEST['s'];
			break;
	
		case 'delete_item':
			$id=mi($_REQUEST['item']);
			$con->sql_query("update tracker set type='cancelled' where id=$id");
			break;
		
		case 'save_moving':
			foreach($_REQUEST['sel_approvals'] as $k=>$v){
				$con->sql_query("update tracker set priority='$k' where id = $v");	
			}
			break;
			
		case 'ajax_sort_list':
			get_tracker_items();
			$smarty->display("admin.arms_tracker.items.tpl");
			exit;

		case 'verify':
		    $id = intval($_REQUEST['id']);
		    $con->sql_query("select user_id from tracker where id=$id");
		    $r=$con->sql_fetchrow();
		    if ($r[0]!=$sessioninfo['id'])
			{
				print "You are not owner";
			    exit;
		    }
		    
		    $con->sql_query("update tracker set verified_by = $sessioninfo[id], type='verified' where id = $id");
			print "OK";
			exit;
			
		case 'solve':
			$id = intval($_REQUEST['id']);
			$con->sql_query("update tracker set solved_by = $sessioninfo[id] where id = $id");
			print "OK";
			exit;


		case 'save_edit':
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
			$id=mi($_REQUEST['id']);
			$val=ms($_REQUEST['value']);
			$con->sql_query("update tracker set description=$val where id=$id");
			$con->sql_query("select description from tracker where id=$id");
			$r=$con->sql_fetchrow();
			print "$r[0]";
			exit;
			
		case 'save_all':
			if($_REQUEST['new_value'])
			{
				foreach ($_REQUEST['new_value'] as $k=>$v)
				{
					$no_item++;				
					$upd['user_id']= intval($sessioninfo['id']);
		   			$upd['type'] =$_REQUEST['new_type'][$k];
		   			$upd['added'] ='CURRENT_TIMESTAMP()';
		   			$upd['description'] = trim($v);	
		   			
			    	$con->sql_query("insert into tracker ".mysql_insert_by_field($upd));
					$cur_id=$con->sql_nextid();
					email_to_user($cur_id);
											
					$q1=$con->sql_query("select priority from tracker order by priority desc limit 1");
					$r=$con->sql_fetchrow($q1);	
					$priority=$r[0]+1;
					$con->sql_query("update tracker set priority='$priority' where id=$cur_id");

					// save screenshot attachments
					foreach ($_FILES['new_screenshot']['name'][$k] as $idx=>$nm)
					{
						if (preg_match("/\.(jpg|png|gif|xls|pdf)$/i", $nm))
						{
							// save this
							if (!is_dir(TRACKER_FILES))
							{
								mkdir(TRACKER_FILES,0777);
							}
							@mkdir(TRACKER_FILES."/$cur_id",0777);
							rename($_FILES['new_screenshot']['tmp_name'][$k][$idx], TRACKER_FILES."/$cur_id/$nm");
						}
					}
				}
			}			
			break;

		case 'add_pic':
			$cur_id = intval($_REQUEST['id']);
			if ($cur_id<=0) exit;
			
			// save screenshot attachments
			foreach ($_FILES['screenshot']['name'] as $idx=>$nm)
			{
				if (preg_match("/\.(jpg|png|gif|xls|pdf)$/i", $nm))
				{
					// save this
					if (!is_dir(TRACKER_FILES))
					{
						mkdir(TRACKER_FILES,0777);
					}
					@mkdir(TRACKER_FILES."/$cur_id",0777);
					rename($_FILES['screenshot']['tmp_name'][$idx], TRACKER_FILES."/$cur_id/$nm");
				}
			}
			$ret = '';
			foreach (glob(TRACKER_FILES."/$cur_id/*.*") as $f)
			{
				$ret .= '<a href="?a=all_attachment&id='.$cur_id.'" target=_blank>';
				if (preg_match('/\.xls$/',$f))
				$ret .= '<img src="/ui/icons/page_excel.png" border=0></a> ';
				elseif (preg_match('/\.pdf$/',$f))
				$ret .= '<img src="/ui/icons/page_white_acrobat.png" border=0></a> ';
				else
				$ret .= '<img src="/thumb.php?w=25&h=25&img='.str_replace("$_SERVER[DOCUMENT_ROOT]/",'',$f).'" border=0></a> ';
			}
			$ret = jsstring($ret);
			print "<script>window.parent.done_addpic($cur_id,'$ret');</script>";
			exit;
		case 'all_attachment':
			$cur_id = intval($_REQUEST['id']);
			if ($cur_id<=0) die("Invalid ID");
			print "<h1>Attachments of Tracker #$cur_id</h1>";
			foreach (glob(TRACKER_FILES."/$cur_id/*.*") as $f)
			{
				print '<h4>'.$f.'</h4>';
				if (preg_match('/\.(xls|pdf)$/',$f))
					print 'Download <a href="'.str_replace("$_SERVER[DOCUMENT_ROOT]/",'',$f).'">'.basename($f).'</a>';
				else
					print '<img src="'.str_replace("$_SERVER[DOCUMENT_ROOT]/",'',$f).'" border=0 ><hr>';
			}
			exit;
		case 'move_up':
			$p=$_REQUEST['priority']-1;
			$id=$_REQUEST['id'];
			$type=ms($_REQUEST['type']);
			
			$con->sql_query("update tracker set priority='$_REQUEST[priority]' where priority = $p");
			$con->sql_query("update tracker set priority='$p' where id = $id");	
			get_tracker_items();
			$smarty->display("admin.arms_tracker.items.tpl");		
			exit;

		case 'move_down':
			$p=$_REQUEST['priority']+1;
			$id=$_REQUEST['id'];
			$type=ms($_REQUEST['type']);
			
			$con->sql_query("update tracker set priority='$_REQUEST[priority]' where priority = $p");
			$con->sql_query("update tracker set priority='$p' where id = $id");	
			get_tracker_items();
			$smarty->display("admin.arms_tracker.items.tpl");		
			exit;
			
		case 'change_type':
			$type=ms($_REQUEST['pass_type']);
			$id=$_REQUEST['id'];	
			$con->sql_query("update tracker set type=$type where id = $id");	
			get_tracker_items();
			$smarty->display("admin.arms_tracker.items.tpl");		
			exit;
				
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;				
			
	}
}

get_tracker_items();
$smarty->display("admin.arms_tracker.tpl");
exit;

function get_tracker_items(){
	global $con, $sessioninfo, $smarty, $search;
	
	if($_REQUEST['type'] == '' || $_REQUEST['type']=='All'){
		$where=" and type not in ('verified', 'cancelled')";
	}
	else{
		$where="and type like '$_REQUEST[type]'";
		$smarty->assign("selected_type", $_REQUEST['type']);
	}
	
	if($search){
		$where.=" and (tracker.id like '$search%' or tracker.description like '%$search%') ";	
	}
	
	$con->sql_query("select tracker.*, user.u as u, u2.u as solved_by ,u3.u as verified_by 
from tracker 
left join user on user_id = user.id 
left join user u2 on solved_by = u2.id 
left join user u3 on verified_by = u3.id 
where tracker.active $where 
order by (tracker.verified_by>0), tracker.priority ");

	while($r=$con->sql_fetchrow())
	{
		if(!$r['verified_by']){
			$total++;
		}
		
		$r['files'] = glob(TRACKER_FILES."/$r[id]/*.*");
		$issues[]=$r;
	}
	//$total=count($total);
	$smarty->assign("total", $total);
	$smarty->assign("all_issues", $issues);
}

function email_to_user($id){
	
	return;
	include("include/class.phpmailer.php");
	
	global $con, $sessioninfo, $smarty;
	$q1=$con->sql_query("select tracker.*, user.u as creater
from tracker 
left join user on tracker.user_id = user.id
where tracker.id=$id");
	$r=$con->sql_fetchrow($q1);	

	//ini_set("display_errors",1);
	$mailer = new PHPMailer();
    $mailer->From = "noreply@localhost";
    $mailer->FromName = "ARMS Tracker";
	$mailer->Subject="[$r[type]] $r[description]";
	//$mailer->Host="mail.aneka.com.my";
	$mailer->Body="New Tracker : $r[description] ($r[type]), have been added by $r[creater] on $r[added].";
	$mailer->AddAddress("yinsee@wsatp.com");
	$mailer->AddAddress("garykoay@wsatp.com");
	$mailer->AddAddress("cslo@aneka.com.my");
	$mailer->AddAddress("sllee@aneka.com.my");
	
//	print "Sending";
	$send = $mailer->Send();
//	var_dump($send);
//	exit;
}

?>
