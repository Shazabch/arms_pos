<?php
/*
1/19/2011 3:46:45 PM Alex
- change use report_server and fix date bugs

6/24/2011 6:12:35 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:33:11 PM Andy
- Change split() to use explode()

8/1/2012 3:03 PM Justin
- Fixed bug of showing sql error while filter by branch group.
- Fixed bug of report title.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

4/4/2017 4:12 PM Justin
- Bug fixed on system always pickup sales from first branch only when view at HQ and filter with "All" branches.
- Optimised the scripts to remove some of the redundant source codes.

2/18/2020 10:39 AM William
- Enhanced to change $con connection to use $con_multi.
*/
include("include/common.php");
include("include/class.report.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_MEMBERSHIP', BRANCH_CODE), "/index.php");

class MemberSalesByHour extends Report
{
	function run_report($bid, $tbl_name)
	{
	    global $con,$smarty, $con_multi;
	    
	    $filter = $this->filter;
	    
	    if($this->view_type=="day") $day=",day";

		$tbl = $tbl_name['member_sales_cache'];
		$sql="select year,month $day,hour,if(card_no <>'','MEMBER','NON_MEMBER') as member,sum(ifnull(transaction_count,0)) as transaction_count,sum(ifnull(amount,0)) as amount from $tbl where $filter group by year,month $day,hour,member order by date,hour";
    
		$q1 = $con_multi->sql_query($sql) or die(sql_error());

		if($con_multi->sql_numrows($q1)>0){
		    while($t = $con_multi->sql_fetchassoc($q1)){
				$lbl = sprintf("%04d%02d%02d", $t['year'], $t['month'], $t['day']);
				
				if($this->view_type=="day") $this->label[$lbl] = $t['day']." ".$this->months[$t['month']]." ".$t['year'];
				else $this->label[$lbl] = $this->months[$t['month']] ." " . $t['year'];
				
				$this->table[$lbl][$t['hour']][$t['member']]['transaction_count']+=$t['transaction_count'];
				$this->table[$lbl][$t['hour']][$t['member']]['amount']+=$t['amount'];
				$this->table[$lbl][$t['hour']]['total']['amount']+=$t['amount'];
				$this->table[$lbl][$t['hour']]['total']['transaction_count']+=$t['transaction_count'];
				$this->table[$lbl]['total'][$t['member']]['transaction_count']+=$t['transaction_count'];
				$this->table[$lbl]['total'][$t['member']]['amount']+=$t['amount'];
				$this->table[$lbl]['total']['total']['amount']+=$t['amount'];
				$this->table[$lbl]['total']['total']['transaction_count']+=$t['transaction_count'];

				if(!$this->min_hour || $this->min_hour > $t['hour']) $this->min_hour = $t['hour'];
				if(!$this->max_hour || $this->max_hour < $t['hour']) $this->max_hour = $t['hour'];
			}
		}
		$con_multi->sql_freeresult($q1);
	}
	
	function generate_report()
	{
		global $con, $smarty, $con_multi;
	    
		$branch_group = $this->branch_group;
		$this->min_hour = $this->max_hour = 0;

		if(strpos($this->branch_id,'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$this->branch_id);
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid=>$b){
					$tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
			}
			$report_title[] = "Branch Group: ".$branch_group['header'][$bg_id]['code'];
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){
	            $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $report_title[] = "Branch: ".BRANCH_CODE;
			}else{
				if($bid==0){
	                $report_title[] = "Branch: All";
	                $q_b = $con_multi->sql_query("select * from branch where active=1 order by sequence,code");
	                while($r = $con_multi->sql_fetchassoc($q_b)){
                        $tbl_name['member_sales_cache'] = "member_sales_cache_b".$r['id'];
			            $this->run_report($r['id'],$tbl_name);
			            //$this->label[$r['id']] = $r['code'];
					}
					$con_multi->sql_freeresult($q_b);
				}else{
	                $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$report_title[] = "Branch: ".get_branch_code($bid);
				}
			}
		}
		
		// construct min and max hours
		for($i=$this->min_hour;$i<=$this->max_hour;$i++){
			if($i<13) $hour[$i]=$i.":00AM";
			else{
				$h = $i-12;
				if($i=='24') $hour[$i]=$h.":00AM";
				else $hour[$i]=$h.":00PM";
			}
		}

		if($this->table) ksort($this->table);
		
		$report_title[] = "Date From: ".$this->date_from." To ".$this->date_to;
		$report_title[] = "View by: ".ucwords($this->view_type);
    
		$smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
		$smarty->assign("hour", $hour);
		$smarty->assign('label',$this->label);
		$smarty->assign('table',$this->table);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo;
		// do my own form process
		
		// call parent
		parent::process_form();

		$this->branch_id=$_REQUEST['branch_id'];
		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];
		$this->view_type = $_REQUEST['view_type'];

        if($this->view_type=="day"){
			$mtest =strtotime("+1 month",strtotime($this->date_from));
		}else{
			$mtest =strtotime("+1 year",strtotime($this->date_from));
		}
		
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to)< strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}

		$filter = array();
		$filter[] = "date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter = join(' and ', $filter);
		
		$this->filter = $filter;
	}	

	function default_values(){
	    $view_type = $_REQUEST['view_type'];
	    if($view_type=="day"){
			$_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		}else{
            $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
		}
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}

if($_REQUEST['view_type']=="day"){
	$byType = "by Day";
}else if($_REQUEST['view_type']=="month"){
    $byType = "by Month";
}

/*$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}*/
$report = new MemberSalesByHour('Hourly Member Sales Comparison '.$byType);
//$con_multi->close_connection();
?>
