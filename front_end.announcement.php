<?php
/*
=============================================================
Status 1 = draft, 2 = cancelled, 3 = confirmed, 4 = deleted
=============================================================

12/4/2020 1:42 PM Shane
- Added Branch Group and User filter
- Added Copy

12/7/2020 5:03 PM Shane
- Fixed Announcement List bug where announcement with status = 4 is showing at all branch
- Added checking when copy announcement, if logged in branch is not HQ and Announcement created is not from this branch, then cannot copy.

12/9/2020 7:55 PM Shane
- Added show_branch_group checking.

12/11/2020 3:57 PM Shane
- Change log type ANNOUNCEMENT to POS ANNOUNCEMENT

12/29/2020 2:26 PM Shane
- Fixed issue when save/confirm POS Announcement with error, system will change it to non-editable mode.

12/30/2020 11:29 PM Shane
- Fixed issue when save/confirm POS Announcement with error, the modification made is lost.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FRONTEND_ANNOUNCEMENT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ANNOUNCEMENT', BRANCH_CODE), "/index.php");

$maintenance->check(477);

//ini_set("display_errors", 1);

//die("Announcement is currently under maintenance, please come back later.");

class Announcement extends Module
{
	var $branch_id, $announcement_id, $branch, $branch_counter, $branch_counter_count, $branch_group_list, $branch_id_by_group, $max_counter_per_row, $show_branch_group;
	var $user_list = array();

	function __construct($title, $template='')
	{
		global $sessioninfo, $con, $smarty, $config, $appCore;
		$this->branch_id = intval($_REQUEST['branch_id']);
		$this->announcement_id = intval($_REQUEST['id']);
		
		if ($this->branch_id =='')
		{
			$this->branch_id = $sessioninfo['branch_id'];
		}

		$con->sql_query("select id, code from branch where active=1 order by sequence,code");

		while($r=$con->sql_fetchrow())
		{
			$this->branch[$r['id']] = $r;
		}

		$this->show_branch_group = $config['pos_announcement_show_branch_group'];
		//Branch Group
		$this->branch_group_list = array();
		$this->branch_id_by_group = array();
		if($this->show_branch_group){
			$group_list = $appCore->branchManager->getBranchGroupList(true);//true = get branches with no group as well
			foreach($group_list['group'] as $bgid => $gl){
				$this->branch_group_list[$bgid] = $gl;
				foreach($gl['itemList'] as $bid => $b){
					$this->branch_id_by_group[$bgid][$bid] = $b['code'];
				}
			}
		}

		//Branch Counter
		$this->branch_counter = array();
		$max_counter = 0;
		foreach($this->branch as $bid => $b){
			$rs = $con->sql_query("select * from counter_settings where active=1 and branch_id = $bid");
			$count = 0;
			while($rw = $con->sql_fetchrow($rs)){
				$this->branch_counter[$bid][$rw['id']] = $rw;
				$count++;
			}
			$this->branch_counter_count[$bid] = $count;
			if($count > $max_counter){
				$max_counter = $count;
			}
		}

		$this->max_counter_per_row = 8;

		$smarty->assign("branch", array_values($this->branch));
		$smarty->assign("branches", $this->branch);
		$smarty->assign("sessioninfo", $sessioninfo);
		$smarty->assign("max_counter", $max_counter);
		$smarty->assign("branch_counter", $this->branch_counter);
		$smarty->assign("branch_counter_count", $this->branch_counter_count);
		$smarty->assign("branch_group_list", $this->branch_group_list);
		$smarty->assign("max_counter_per_row", $this->max_counter_per_row);
		$smarty->assign("session_branch_id", $sessioninfo['branch_id']);
		$smarty->assign("show_branch_group", $this->show_branch_group);
		// $smarty->assign("branch_id_by_group", $this->branch_id_by_group);

		parent::__construct($title, $template='');
	}

	function delete()
	{
		global $sessioninfo, $con, $LANG, $smarty;

		$announcement_id = $this->announcement_id;
		$branch_id = $this->branch_id;
		
		if ($sessioninfo['level']<9999)
			$usrcheck=" and user_id=$sessioninfo[id]";

		$con->sql_begin_transaction();

		$con->sql_query("update pos_announcement
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=4, active=0
	where id=$announcement_id and branch_id=$branch_id $usrcheck");

		$updated = $con->sql_affectedrows();

		if ($updated>0){
			$smarty->assign("id", $announcement_id);
			$smarty->assign("type", "delete");
			log_br($sessioninfo['id'], 'POS ANNOUNCEMENT', $announcement_id, "Announcement Deleted (ID#$announcement_id)");
		}
		$con->sql_commit();

		if($updated>0){
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_DELETED'], $announcement_id)));
		}else{
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_NOT_DELETED'], $announcement_id)));
		}
		

	}

	function cancel()
	{
		global $sessioninfo, $con, $LANG, $smarty;

		$announcement_id = $this->announcement_id;
		$branch_id = $this->branch_id;
		
		if ($sessioninfo['level']<9999)
			$usrcheck=" and user_id=$sessioninfo[id]";

		$con->sql_begin_transaction();

		$con->sql_query("update pos_announcement
	set cancel_by=$sessioninfo[id], cancelled=CURRENT_TIMESTAMP(), last_update=last_update, status=2, active=0
	where id=$announcement_id and branch_id=$branch_id $usrcheck");

		$updated = $con->sql_affectedrows();

		if($updated>0){
			$smarty->assign("id", $announcement_id);
			$smarty->assign("type", "delete");
			log_br($sessioninfo['id'], 'POS ANNOUNCEMENT', $announcement_id, "Announcement Cancelled (ID#$announcement_id)");
		}
		$con->sql_commit();

		if($updated>0){
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_CANCELLED'], $announcement_id)));
		}else{
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_NOT_CANCELLED'], $announcement_id)));
		}
	}

	function confirm()
	{
		$this->save(($_REQUEST['a']=='confirm'));
	}

	function save($is_confirm = 0)
	{
		global $con, $smarty, $sessioninfo, $LANG, $appCore;

		$form = $_REQUEST;
		$announcement_id = $this->announcement_id;
		$branch_id = $this->branch_id;
		$status = ($is_confirm ? 3 : 1);//1 draft, 3 confirm

		//validate data

		$form['id'] = $announcement_id;
		$form['branch_id'] = $branch_id;
		$form['status'] = $status;

		if($branch_id==1){
			$announcement_branch_id = array();
			$announcement_counter_id = array();
			if($this->show_branch_group){
				$arbgid = $form['announcement_branch_group_id'];
				$announcement_branch_group_id = array();

				foreach($this->branch_group_list as $bgid => $tmp){
					if($arbgid && in_array($bgid,$arbgid)){
						$announcement_branch_group_id[$bgid] = $this->branch_group_list['group'][$bgid]['code'];
						//skip branch checking if has branch group
						foreach($this->branch_id_by_group[$bgid] as $bid => $tmp){
							$announcement_branch_id[$bid] = get_branch_code($bid);
							$announcement_counter_id[$bid] = array();
						}
					}else{
						$arr = json_decode($form['branch_id_by_group_'.$bgid],true);
						if($arr){
							foreach($arr as $bid => $b){
								$announcement_branch_id[$bid] = get_branch_code($bid);
								$announcement_counter_id[$bid] = ($b['all_counter_flag']?array():$b['counter_ids']);
							}
						}
					}
				}
			}else{
				$announcement_branch_group_id = '';
				foreach($this->branch as $bid => $b){
					if(in_array($bid,$form['announcement_branch_id'])){
						$announcement_branch_id[$bid] = $b['code'];
						if($form['announcement_all_counter_flag'] && in_array($bid,$form['announcement_all_counter_flag'])){
							$announcement_counter_id[$bid] = array();
						}else{
							$announcement_counter_id[$bid] = $form['announcement_counter_id_'.$bid];
						}
					}
				}
			}
		}else{
			$announcement_branch_group_id = '';
			$announcement_branch_id[$sessioninfo['branch_id']] = BRANCH_CODE;
			if($form['announcement_all_counter_flag']){
				$announcement_counter_id[$sessioninfo['branch_id']] = array();
			}else{
				$announcement_counter_id[$sessioninfo['branch_id']] = $form['announcement_counter_id'];
			}
		}

		if(!trim($form['title'])){
			$err['top'][]=$LANG['ANNOUNCEMENT_TITLE_EMPTY'];
		}

		if(!trim($form['content'])){
			$err['top'][]=$LANG['ANNOUNCEMENT_CONTENT_EMPTY'];
		}

		if($form['date_from']=='' || strtotime($form['date_from'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_from']))
		$err['top'][]=$LANG['ANNOUNCEMENT_INVALID_DATE_FROM'];

		if($form['date_to']=='' || strtotime($form['date_to'])<=0 || !preg_match("/^20\d{2}\-\d{1,2}\-\d{1,2}$/",$form['date_to']))
		$err['top'][]=$LANG['ANNOUNCEMENT_INVALID_DATE_TO'];

		if (strtotime($form['date_from']) > strtotime($form['date_to']))
		{
			$err['top'][]="Date From cannot greater than Date To";
		}

		if (!$form['allowed_day']) {
			$err['top'][]="Please select at least one Allowed Day";
		}
		
		if ($form['branch_id']==1) {
			if($this->show_branch_group){
				if(!$announcement_branch_group_id && !$announcement_branch_id && !$announcement_counter_id){
					$err['top'][]="Please select at least one branch group, branch or counter";
				}
			}else{
				if(!$announcement_branch_id && !$announcement_counter_id){
					$err['top'][]="Please select at least one branch or counter";
				}
			}
		}

		if ($form['branch_id']!=1 && !$form['announcement_all_counter_flag'] && !$form['announcement_counter_id']) {
			$err['top'][]="Please select at least one counter";
		}

		if($err){
			$smarty->assign("errm", $err);
			$_REQUEST['a'] = 'open';
			$this->open(false,true);
		}else{
			if(!$form['user_id']) $form['user_id']=$sessioninfo['id'];
			$upd = array();
			$upd['title'] = $form['title'];
			$upd['content'] = $form['content'];
			$upd['date_from'] = $form['date_from'];
			$upd['date_to'] = $form['date_to'];
			$upd['time_from'] = $form['time_from'];
			$upd['time_to'] = $form['time_to'];
			$upd['allowed_day'] = serialize($form['allowed_day']);
			if($announcement_branch_group_id){
				$upd['announcement_branch_group_id'] = serialize($announcement_branch_group_id);
			}else{
				$upd['announcement_branch_group_id'] = '';
			}
			
			$upd['announcement_branch_id'] = serialize($announcement_branch_id);
			$upd['announcement_counter_id'] = serialize($announcement_counter_id);
			if($form['announcement_user_id']){
				$upd['announcement_user_id'] = serialize($form['announcement_user_id']);
			}else{
				$upd['announcement_user_id'] = '';
			}
			$upd['status'] = $status;
			$upd['last_update'] = 'CURRENT_TIMESTAMP';

			// Begin Transaction
			$con->sql_begin_transaction();
			
			if($announcement_id){
				$con->sql_query("update pos_announcement set " . mysql_update_by_field($upd) . " where id=$form[id] and branch_id=$form[branch_id]");
				$title = "Announcement Update";
				$subject = "Announcement ID#".$form['id']." Update Successfully";
			}else{
				$upd['id'] = $appCore->generateNewID("pos_announcement", "branch_id=".mi($this->branch_id));
				$upd['branch_id'] = $form['branch_id'];
				$upd['user_id'] = $form['user_id'];
				$upd['added'] = 'CURRENT_TIMESTAMP';
				$con->sql_query("insert into pos_announcement " . mysql_insert_by_field($upd));
				$form['id'] = $upd['id'];
				$title = "Announcement Add";
				$subject = "Announcement ID#".$form['id']." Add Successfully";
			}
			unset($upd);
			log_br($sessioninfo['id'], 'POS ANNOUNCEMENT', $form['id'], "Announcement Saved (ID#".$form['id'].")");
			
			// Commit
			$con->sql_commit();
			
			$redirect_url = $_SERVER['PHP_SELF'];
			display_redir($redirect_url, $title, $subject);
		}
	}

	function view(){
		$this->open(true);
	}

	function open($readonly = false, $is_error = false)
	{
		global $smarty, $con, $sessioninfo, $LANG, $all_counter_info, $appCore;

		$form=$_REQUEST;
		$form['branch_id'] = $this->branch_id;

		if($form['branch_id']!=1 && $form['announcement_counter_id']){
			$tmp = $form['announcement_counter_id'];
			unset($form['announcement_counter_id']);
			$form['announcement_counter_id'][$sessioninfo['branch_id']] = $tmp;
		}
		if($this->announcement_id && !$is_error){
			$sql = "select * from pos_announcement where id = ".$this->announcement_id." and branch_id = ".$this->branch_id." limit 1";
			$con->sql_query($sql);
			$form = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$form['allowed_day'] = unserialize($form['allowed_day']);
			if($form['announcement_user_id']){
				$form['announcement_user_id'] = unserialize($form['announcement_user_id']);
			}

			if($form['branch_id']==1 && $form['announcement_branch_group_id']){
				$announcement_branch_group_id = unserialize($form['announcement_branch_group_id']);
				$form['announcement_branch_group_id'] = array_keys($announcement_branch_group_id);
			}else{
				$form['announcement_branch_group_id'] = '';
			}
			
			$announcement_branch_id = unserialize($form['announcement_branch_id']);
			$form['announcement_branch_id'] = array_keys($announcement_branch_id);
			
			$announcement_counter_id = unserialize($form['announcement_counter_id']);
			$form['announcement_counter_id'] = $announcement_counter_id;
			if($form['branch_id']==1 && $sessioninfo['branch_id']==1){
				$branch_info = array();
				foreach($announcement_branch_id as $bid => $bcode){
					$bgid = $appCore->branchManager->getBranchGroupId($bid);
					$branch_info[$bgid][$bid]['branch_id'] = $bid;
					$counter_ids = $announcement_counter_id[$bid];
					if($counter_ids){
						$branch_info[$bgid][$bid]['all_counter_flag'] = 0;
						$branch_info[$bgid][$bid]['counter_ids'] = $counter_ids;
					}else{
						$branch_info[$bgid][$bid]['all_counter_flag'] = 1;
					}
				}
				foreach($this->branch_group_list as $bgid => $tmp){
					if($branch_info[$bgid]){
						$form['branch_id_by_group_'.$bgid] = json_encode($branch_info[$bgid]);
					}else{
						$form['branch_id_by_group_'.$bgid] = '';
					}
				}
			}else{
				if($sessioninfo['branch_id']!=1){
					$this_branch = $sessioninfo['branch_id'];
				}else{
					$this_branch = $form['branch_id'];
				}
				if(!$announcement_counter_id[$this_branch]){
					$form['announcement_all_counter_flag'] = 1;
				}else{
					$form['announcement_all_counter_flag'] = 0;
				}
			}
		}

		$this->user_list = array();
		if($form['announcement_user_id']){
			foreach($form['announcement_user_id'] as $user_id){
				if(isset($this->user_list[$user_id]))	continue;
				$this->user_list[$user_id] = $appCore->userManager->getUser($user_id);
			}
		}
		
		$form['id'] = $this->announcement_id;
		$form['all_days'] = $this->get_day_of_week();
		$smarty->assign("form", $form);
		$smarty->assign("readonly", $readonly);
		$smarty->assign('user_list', $this->user_list);
		$this->display('front_end.announcement.new.tpl');
	}

	function ajax_load_announcement_list($t=0)
	{
		global $con, $sessioninfo, $smarty, $appCore;

		if(!$t) $t=intval($_REQUEST['t']);

		if (BRANCH_CODE!='HQ')
		{
			$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
			$r = $con->sql_fetchrow();
			$branch_group_info = $appCore->branchManager->getBranchGroupInfo($appCore->branchManager->getBranchGroupId($sessioninfo['branch_id']));
			$branch_group_code = $branch_group_info['code'];

			$branch_sql = "(pos_announcement.branch_id = ".$r['id']." or (pos_announcement.branch_id = 1 and (pos_announcement.announcement_branch_id like '%\"".BRANCH_CODE."\"%' or pos_announcement.announcement_branch_group_id like '%\"".$branch_group_code."\"%'))) and ";
		}

		switch($t){
			//search PO
			case 0:
				
				if (!$_REQUEST['search'] && !$_REQUEST['search_filter']) die('<br />&nbsp;&nbsp;Cannot search empty string<br /><br />');
				
				$search_filter = array();
				
				if ($_REQUEST['search']) {
					$search_filter[] = "(pos_announcement.id=".mi($_REQUEST['search'])." or pos_announcement.title like ". ms("%".replace_special_char($_REQUEST['search'])."%") . ")";
				}
				
				if ($_REQUEST['search_filter']) {
				
					$d = mi($_REQUEST['day_count']);
					$today = ms(date('Y-m-d'));
					
					switch ($_REQUEST['search_filter']) {
					
						case 'starting_in':
							$search_filter[] = " DATEDIFF(date_from,$today) = $d and pos_announcement.active=1 and pos_announcement.status=3";
						break;
						
						case 'ending_in':
							$search_filter[] = " DATEDIFF(date_to,$today) = $d and pos_announcement.active=1 and pos_announcement.status=3";
						break;
						
						case 'currently_active':
							$search_filter[] = " date_from <= $today and date_to >= $today and pos_announcement.active=1 and pos_announcement.status=3 ";
						break;
					}
					
				}
				
			    $where = $search_filter ? join(' and ',$search_filter) : ' 1 ';
			    break;

			// show cancelled/deleted
			case 2:
				$where="(pos_announcement.status=2 or pos_announcement.status=4) ";
				break;
			default:
		    	$where="pos_announcement.status=$t ";
		    	break;
		}
		
		// pagination
		$start = intval($_REQUEST['s']);
		if (isset($_REQUEST['sz']))
			$sz = intval($_REQUEST['sz']);
		else
			$sz = 25;
		$con->sql_query("select count(*) from pos_announcement where $branch_sql $where");
		$r = $con->sql_fetchrow();
		$total = $r[0];
		if ($total > $sz){
			if ($start > $total) $start = 0;
			// create pagination
			$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
			for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
				$pg .= "<option value=$i";
				if ($i == $start){
					$pg .= " selected";
				}
				$pg .= ">$p</option>";
			}
			$pg .= "</select>";
			$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
		}
		$q1=$con->sql_query("select user.u, pos_announcement.*
    from pos_announcement
		left join user on pos_announcement.user_id = user.id
		where $branch_sql $where order by last_update desc limit $start, $sz");
		while ($r1=$con->sql_fetchrow($q1)){
			$r1['announcement_branch_id'] = unserialize($r1['announcement_branch_id']);
			if ($r1['announcement_branch_id']){
				// $r1['str_announcement_branch_id_list'] = implode(",", array_keys($r1['announcement_branch_id']));
				$r1['announcement_branch_id'] = implode(", ",$r1['announcement_branch_id']);
			}
			$r1['created_at_branch'] = get_branch_code($r1['branch_id']);
			$announcement_list[]=$r1;
		}
		
		$smarty->assign('sessioninfo', $sessioninfo);
		$smarty->assign("announcement_list", $announcement_list);
		$smarty->display("front_end.announcement.list.tpl");

	}

	function get_day_of_week($long = true){
		if($long){
			$days = array(
				1 => 'Monday',
				2 => 'Tuesday',
				3 => 'Wednesday',
				4 => 'Thursday',
				5 => 'Friday',
				6 => 'Saturday',
				7 => 'Sunday'
			);
		}else{
			$days = array(
				1 => 'Mon',
				2 => 'Tue',
				3 => 'Wed',
				4 => 'Thu',
				5 => 'Fri',
				6 => 'Sat',
				7 => 'Sun'
			);
		}
		return $days;
	}

	function _default()
	{
		$this->display();
	}

	function ajax_show_branch_list(){
		global $con, $smarty;
		
		$branch_group_id = mi($_REQUEST['branch_group_id']);
		$view_mode = $_REQUEST['view_mode'];
		// $readonly = $_REQUEST['readonly'];

		$branch = array();
		$branch = $this->branch_id_by_group[$branch_group_id];

		$max_counter = 0;
		foreach($branch as $bid => $bcode){
			$rs = $con->sql_query("select max(x.num) as max_counter from (select count(y.id) as num from counter_settings y where y.active=1 and y.branch_id = $bid) x");
			$rw = $con->sql_fetchrow($rs);
			$con->sql_freeresult($rs);
			if($rw['max_counter'] > $max_counter){
				$max_counter = $rw['max_counter'];
			}
		}

		$smarty->assign('branch_list_by_group', $branch);
		$smarty->assign('max_counter_by_branch_group', $max_counter);
		$smarty->assign('view_mode',$view_mode);
		$smarty->assign('save_branch_group_id',$branch_group_id);

		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('front_end.announcement.open.branch.tpl');
		// die($ret['ok']);
		print json_encode($ret);
	}

	function ajax_add_user(){
		global $con, $smarty, $appCore;
		
		$user_id = mi($_REQUEST['user_id']);
		$user = $appCore->userManager->getUser($user_id);
		
		if(!$user){
			die('Invalid User ID');
		}
		
		$smarty->assign('user', $user);
		$smarty->assign('readonly', 0);
		$ret = array();
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch('front_end.announcement.open.user.tpl');
		
		print json_encode($ret);
	}

	function copy()
	{
		global $con, $smarty, $sessioninfo,$LANG, $appCore;

		$announcement_id = $this->announcement_id;
		$branch_id = $this->branch_id;

		if ($sessioninfo['branch_id'] != 1 && $sessioninfo['branch_id'] != $branch_id){
			header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_INVALID_COPY'])));
			exit;
		}

		$con->sql_begin_transaction();
		
		$con->sql_query("select * from pos_announcement where id=$announcement_id and branch_id=$branch_id ");
		$temp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$temp) display_redir($_SERVER['PHP_SELF'], "Announcement", "Announcement ID#$announcement_id cannot found.");

	 	$new_announcement_bid = $sessioninfo['branch_id'];
        $new_announcement_id = $appCore->generateNewID("pos_announcement", "branch_id=".$new_announcement_bid);
        $temp['id'] = $new_announcement_id;
		$temp['branch_id'] = $new_announcement_bid;
		$temp['user_id'] = $sessioninfo['id'];
		$temp['added'] = 'CURRENT_TIMESTAMP';
		$temp['status'] = 1;//draft
		$temp['last_update'] = 'CURRENT_TIMESTAMP';
		$con->sql_query("insert into pos_announcement ".mysql_insert_by_field($temp));
		
		log_br($sessioninfo['id'], 'POS ANNOUNCEMENT', $new_announcement_id, "Announcement Copy (ID#$announcement_id (".get_branch_code($branch_id).")-> ID#$new_announcement_id (".get_branch_code($new_announcement_bid)."))");
		
		$con->sql_commit();
		
		header("Location: $_SERVER[PHP_SELF]?msg=".urlencode(sprintf($LANG['ANNOUNCEMENT_COPY'], $announcement_id, get_branch_code($branch_id), $new_announcement_id, get_branch_code($new_announcement_bid))));
	}
}

$announcement = new Announcement ('POS Announcement');

?>
