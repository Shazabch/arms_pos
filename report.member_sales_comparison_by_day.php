<?php
/*
10/11/2010 12:24:09 PM Andy
- Add race filter for report.

1/25/2011 6:12:34 PM Alex
- change use report server
- fix date bugs

6/24/2011 6:15:02 PM Andy
- Make all branch default sort by sequence, code.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/18/2020 10:41 AM William
- Enhanced to change $con connection to use $con_multi.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_MEMBERSHIP', BRANCH_CODE), "/index.php");

class MemberSalesByDay extends Report
{
	function run_report($bid)
	{
	    global $con_multi;
	    
	    $filter = $this->filter;
		$tbl = 'member_sales_cache_b'.$bid;
		
	    $sql = "select tbl.date,if(tbl.card_no<>'','MEMBER','NON_MEMBER') as member,tbl.race,sum(tbl.transaction_count) as transaction_count,sum(tbl.amount) as amount
		from $tbl tbl
		where $filter
		group by date, member, race order by date asc";
		
		$con_multi->sql_query($sql,false,false);
		if($con_multi->sql_numrows()>0){
		    while($t = $con_multi->sql_fetchrow()){
				$this->table[$t['date']]['dmy']['date'] = date("d M Y", strtotime($t['date']));
				$this->table[$t['date']]['day'] = date("D", strtotime($t['date']));
				$this->table[$t['date']][$t['member']][$t['race']]['transaction_count'] += $t['transaction_count'];
				$this->table[$t['date']]['transaction_count']['total']+=$t['transaction_count'];
				$this->table[$t['date']]['transaction_count'][$t['member']]+=$t['transaction_count'];
				$this->table[$t['date']][$t['member']][$t['race']]['amount'] += $t['amount'];
				$this->table[$t['date']]['amount']['total']+=$t['amount'];
				$this->table[$t['date']]['amount'][$t['member']]+=$t['amount'];
			}
		}
		$con_multi->sql_freeresult();

	}
	
	function generate_report()
	{
		global $con, $smarty;

		/*$branch_group = $this->branch_group;

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = split("[,]",$_REQUEST['branch_id']);
			$tbl_name['member_sales_cache'] = "member_sales_cache_bg".$bg_id;
			$this->run_report($bg_id+10000,$tbl_name);
			$branch_name = $branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){
	                $branch_name = "All";
	                $q_b = $con->sql_query("select * from branch where id not in (select branch_id from branch_group_items)");
	                while($r = $con->sql_fetchrow($q_b)){
                        $tbl_name['member_sales_cache'] = "member_sales_cache_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
			            $this->label[$r['id']] = $r['code'];
					}
					if($branch_group['header']){
						foreach($branch_group['header'] as $bg_id=>$bg){
                            $tbl_name['member_sales_cache'] = "member_sales_cache_bg".$bg_id;
				            $this->run_report($bg_id+10000,$tbl_name);
				            $this->label[$bg_id+10000] = $bg['code'];
						}
					}
				}else{
	                $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_name = get_branch_code($bid);
				}
			}
		}*/
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}
		
		if($this->table)	ksort($this->table);
		
		$this->report_title[] = "Date: from ".$this->date_from." to ".$this->date_to;

    
    	$smarty->assign('report_title',join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table',$this->table);
		//$smarty->assign('branch_name',$branch_name);
		//$smarty->assign('day',$day);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo,$con_multi;
		// do my own form process
		
		// call parent
		parent::process_form();
		
		$branch_id = mi($_REQUEST['branch_id']);
		$this->branches_group = $this->load_branch_group();
		
		if(BRANCH_CODE=='HQ'){
			if(!$branch_id){ // show all
				$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
				while($r = $con_multi->sql_fetchrow()){
                    $this->branch_id_list[] = $r['id'];
				}
				$con_multi->sql_freeresult();
				$this->report_title[] = "Branch: All";
			}elseif($branch_id<0){  // branch group
			    $bgid = abs($branch_id);
                if($this->branches_group){
					foreach($this->branches_group['items'][$bgid] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
					$this->report_title[] = "Branch: ".$this->branches_group['header'][$bgid]['code'];
				}
			}else{  // single branch selected
                $this->branch_id_list = array($branch_id);
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}
		}else{  // branch can only view own data
			$this->branch_id_list = array($sessioninfo['branch_id']);
			$this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to) < strtotime($this->date_from)){
           $this->date_to = date("Y-m-d",$mtest);
           $_REQUEST['date_to'] = $this->date_to;
		}
		
		$filter = array();
		$filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);

		$this->race = trim($_REQUEST['race']);
		if($this->race){  // filter race
			$filter[] = "tbl.race=".ms($this->race);
			$this->report_title[]= "Race: ".$this->race_list[$this->race];
		}else{
			$this->report_title[]= "Race: All";
		}
		
		$filter = join(' and ',$filter);
		
		$this->filter = $filter;
	}
	
	function default_values()
	{
	    $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
	    $_REQUEST['date_to'] = date("Y-m-d");
	}
}
//$con_multi = new mysql_multi();
$report = new MemberSalesByDay('Member Sales Comparison by Day');
//$con_multi->close_connection();
?>
