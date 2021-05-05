<?php
/*
1/24/2011 4:02:32 PM Alex
- change use report_server

3/31/2011 9:54:46 AM Justin
- Disabled the checking of filtering level of departments.
- Fixed the date format problem.

7/6/2011 2:38:16 PM Andy
- Change split() to use explode()
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(35);

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

$con->sql_query("select * from user where active=1 order by u");
while($r = $con->sql_fetchrow()){
	$user_list[$r['id']] = $r;
}
$con->sql_freeresult();
$smarty->assign('user_list', $user_list);

// set points type
$con->sql_query("select * from membership_points group by type order by type");
$smarty->assign('points_type', $con->sql_fetchrowset());

// set report fixed row display
$smarty->assign('report_row', 25);

class REDEMPTION_POINTS_HISTORY_REPORT extends Report{
	private function run_report($bid){
        global $con, $smarty,$sessioninfo;

        $from_date = $this->date_from;
		$to_date = $this->date_to;
		$user_id = $this->user_id;
		$point_type = $this->point_type;
		$key = 1;

		$filter[] = "date(mp.date) >= '".$from_date."' and date(mp.date) <= '".$to_date."'";
		
		if($bid) $filter[] = "mp.branch_id in (".$bid.")";
		//if($sessioninfo['level']<9999) $filter[] = "(c.department_id in ($sessioninfo[department_ids]) or c.department_id is null)";
		if($user_id) $filter[] = "mp.user_id = ".ms($user_id); 
		if($point_type) $filter[] = "mp.type = ".ms($point_type); 

		$con_multi = new mysql_multi();
		// stock sold query
		$sql = "select mp.*, date_format(mp.date, '%Y-%m-%d') as date, branch.code as branch_code, user.u as username
				from membership_points mp
				left join `user` on user.id = mp.user_id
				left join `branch` on branch.id = mp.branch_id
				where ".join(' and ', $filter)."
				order by mp.card_no, branch_code, date";
		//echo $sql;
		$mp = $con_multi->sql_query($sql);

		while($r = $con_multi->sql_fetchrow($mp)){
			$this->table[$key]['card_no'] = $r['card_no'];
			$this->table[$key]['branch_code'] = $r['branch_code'];
			$this->table[$key]['remark'] = $r['remark'];
			$this->table[$key]['date'] = $r['date'];
			$this->table[$key]['username'] = $r['username'];
			$this->table[$key]['type'] = $r['type'];
			$this->table[$key]['points'] = $r['points'];
			$this->total += $r['points'];
			$key++;
		}
		$con_multi->close_connection();
		//print_r($this->table);
	}
	
    function generate_report(){
		global $con, $smarty;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			$tbl_name[] = "sku_items_sales_cache_bg".$bg_id;
			$get_bg_code = $con->sql_query("select branch_group_items.branch_id, branch_group.code 
										    from branch_group 
										    join branch_group_items on branch_group.id = branch_group_items.branch_group_id 
										    where id = $bg_id");

			while($bg = $con->sql_fetchrow($get_bg_code)){
				$bid[] = $bg['branch_id'];
				$bg_code = $bg['code'];
			}

			$report_title[] = "Branch Group: ".$bg_code;
			$this->run_report(join(",",$bid));
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// is a particular branch
	            $this->run_report($bid);
	            $branch_code = BRANCH_CODE;
			}else{	// from HQ user
				if($bid==0){	// is all the branches
	                $report_title[] = 'Branch: All';
					$this->run_report('');
				}else{	// is a particular branch
		            $this->run_report($bid);
					$branch_code = get_branch_code($bid);
					$report_title[] = "Branch: ".$branch_code;
				}
			}
		}

		$report_title[] = "Date : ".$this->date_from." to ".$this->date_to;
		$con->sql_query("select u from user where id = ".mi($this->user_id));
		$username = ($_REQUEST['user_id']) ? $con->sql_fetchfield(0) : "All";
		$report_title[] = "user: ".$username;
		$point_type = ($this->point_type) ? ucwords($this->point_type) : "All";
        $report_title[] = "Type: ".$point_type;

        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('total', $this->total);
	}
	
	function process_form(){
	    global $con, $smarty;

        $this->date_from = $_REQUEST['date_from'];
        $this->date_to = $_REQUEST['date_to'];
        $this->user_id = $_REQUEST['user_id'];
        $this->point_type = $_REQUEST['point_type'];
        
		// call parent
		parent::process_form();
	}
}

$REDEMPTION_POINTS_HISTORY_REPORT = new REDEMPTION_POINTS_HISTORY_REPORT('Redemption Points History Report');
?>
