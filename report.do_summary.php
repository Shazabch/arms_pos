<?php

/*
1/18/2011 4:34:12 PM Justin
- Changed the year list to take from DO instead of pos.

6/24/2011 6:10:16 PM Andy
- Make all branch default sort by sequence, code.

10/31/2012 6:01 PM Justin
- Fixed bug of report show blank screen after show it.

5/27/2015 3:30 PM Justin
- Enhanced to have GST information.

2/26/2016 9:50 AM Qiu Ying
- Enhance to have show by
*/

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

$q1 = $con->sql_query("select distinct(year(do_date)) as year from do where active=1 order by year(do_date)");

while($r = $con->sql_fetchassoc($q1)){
	$do_year[]['year'] = $r['year'];
}
$con->sql_freeresult($q1);
$smarty->assign("do_year", $do_year);

class DOSummary extends Report
{
    private function run_report($bid_list)
	{
	    global $con, $smarty, $con_multi;

		//print_r($_REQUEST);

		$bid=join(",",$bid_list);
		$date_from=$this->date_from;
        $date_to=$this->date_to;
        $filter= $this->filter;
        $table=$this->table;
		$show_by=$this->show_by;
		
		if ($show_by == "printed_inv"){
			$filter2 = "and inv_printed = 1";
			$var = "sum(total_inv_amt) as amount, sum(inv_total_gst_amt) as gst_amount, sum(inv_total_gross_amt) gross_amount";
		}elseif ($show_by == "inv_amt"){
			$var = "sum(total_inv_amt) as amount, sum(inv_total_gst_amt) as gst_amount, sum(inv_total_gross_amt) gross_amount";
		}
		elseif ($show_by == "do_amt"){
			$var = "sum(total_amount) as amount, sum(do_total_gst_amt) as gst_amount, sum(do_total_gross_amt) gross_amount";
		}

		$sql = "select count(*) as qty, do_date, $var , do_type
				from do
				WHERE active = 1 and status = 1 and approved = 1
				and branch_id in ($bid)
				and do_date between $filter $filter2
				Group By do_date,do_type";

		//print $sql;exit;
		
		$is_under_gst = 0;
		$con_multi->sql_query($sql) or die(sql_error());
		if($con_multi->sql_numrows()>0){
		    while($t = $con_multi->sql_fetchrow()){
			    if($_REQUEST['view_type']=='day'){
					$date_key = date("Ymd", strtotime($t['do_date']));
					$table[$date_key]['row_label'] = $t['do_date'];
	                $table[$date_key]['date']=$t['do_date'];
				}else{
				    $date_key = date("Ym", strtotime($t['do_date']));
					$month = date("m", strtotime($t['do_date']));
	                $table[$date_key]['date']=$month;
				}
			    $table[$date_key][$t['do_type']]['qty'] +=  $t['qty'];
	            $table[$date_key][$t['do_type']]['amt'] += $t['amount'];
	            $table[$date_key][$t['do_type']]['gst_amt'] += $t['gst_amount'];
	            $table[$date_key][$t['do_type']]['gross_amt'] += $t['gross_amount'];

				if($t['gst_amount'] > 0) $is_under_gst = 1;
			}
			$con_multi->sql_freeresult();
		}
	
		$this->is_under_gst = $is_under_gst;
		$this->table = $table;
	//print_r($table);

	}

	function generate_report()
	{

		global $con, $smarty;

		$branch_id =get_request_branch(true);
		$branches_group = $this->branch_group;
	    $con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
	    
		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $report_header[] = "Branch: ".$branches[$branch_id]['code'];
		}else{
			if($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$report_header[] = "Branch Group: ".$branches_group['header'][$bgid]['code'];
				}
			}else{  // all branches
				foreach($branches as $b){
                    $bid_list[] = $b['id'];
				}
				$report_header[] = "Branch: All";
			}
		}
		


		$this->run_report($bid_list);

        $table = $this->table;

		$report_title = join("&nbsp;&nbsp;&nbsp;&nbsp;",$report_header);
        $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;View Type: ".ucwords($_REQUEST['view_type'])."&nbsp;&nbsp;&nbsp;&nbsp;Year: ".$_REQUEST['year'];
        
        if ($_REQUEST['view_type']=='day') $report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;Month: ".str_month($_REQUEST['month']);
		
		if ($_REQUEST['show_by']=='printed_inv') $show_by = "Printed Invoice Only";
		elseif ($_REQUEST['show_by']=='inv_amt') $show_by .= "Invoice Amount";
		elseif ($_REQUEST['show_by']=='do_amt') $show_by .= "DO Amount";
		
		$report_title .= "&nbsp;&nbsp;&nbsp;&nbsp;Show By: " . $show_by;

		$smarty->assign('report_title',$report_title);
        $smarty->assign('branch_name',$branch_name);
        $smarty->assign('table',$table);
        $smarty->assign('is_under_gst',$this->is_under_gst);
	}

	function process_form()
	{
	
        $this->load_branch_group();
		$m = mi($_REQUEST['month']);
		$y = mi($_REQUEST['year']);

		if ($_REQUEST['view_type']=="day")
		{
			$date_from = $y."-".$m."-1";
			$date_to = $y."-".$m."-".days_of_month($m, $y);
			$filter = ms($date_from)." and ".ms($date_to);
        }
        else
		{
			$date_from = $y."-1-1";
			$date_to = $y."-12-31";
			$filter = ms($date_from)." and ".ms($date_to);

			for($i = 1; $i<=12; $i++){
        	    $date_key = $y.sprintf('%02d', $i);
                $table[$date_key]['row_label'] = str_month($i).' '.$y;
			}
		}
		
		$show_by = $_REQUEST['show_by'];
		
        $this->table=$table;
		$this->date_from = $date_from;
		$this->date_to = $date_to;
	    $this->filter = $filter;
		$this->show_by = $show_by;
	}

	function ajax_show_branch()
	{
		global $con, $smarty, $con_multi;

	    //Get branch id

		$branch_id =get_request_branch(true);
		$branches_group = $this->load_branch_group();
	    $con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$test=array(123);
		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $report_header[] = "Branch: ".$branches[$branch_id]['code'];
		}else{
			if($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$report_header[] = "Branch Group: ".$branches_group['header'][$bgid]['code'];
				}
			}else{  // all branches
				foreach($branches as $b){
                    $bid_list[] = $b['id'];
				}
				$report_header[] = "Branch: All";
			}
		}

        $bid=join(",",$bid_list);
        
        //-->end get branch
        
        //Get date
        if ($_REQUEST['view_type']=="day"){
            if (!$_REQUEST['count']){
		    	$filter=ms($_REQUEST['date'])." and ".ms($_REQUEST['date']);
            }
            else{
				$y=$_REQUEST['year'];
				$m=$_REQUEST['month'];
				$date_from = $y."-".$m."-1";
				$date_to = $y."-".$m."-31";
				$filter = ms($date_from)." and ".ms($date_to);
            }
		}
        else{
        
            if (!$_REQUEST['count']){
	            $y=$_REQUEST['year'];
				$m=$_REQUEST['date'];
				$date_from = $y."-".$m."-1";
				$date_to = $y."-".$m."-31";
				$filter = ms($date_from)." and ".ms($date_to);
			}
			else{
                $y=$_REQUEST['year'];
				$date_from = $y."-1-1";
				$date_to = $y."-12-31";
				$filter = ms($date_from)." and ".ms($date_to);
			}
		}
        //-->end get date
		
		$show_by=$_REQUEST['show_by'];
		
		if ($show_by == "printed_inv"){
			$filter2 = "and inv_printed = 1";
			$var = "sum(total_inv_amt) as amount, sum(inv_total_gst_amt) as gst_amount, sum(inv_total_gross_amt) gross_amount";
		}elseif ($show_by == "inv_amt"){
			$var = "sum(total_inv_amt) as amount, sum(inv_total_gst_amt) as gst_amount, sum(inv_total_gross_amt) gross_amount";
		}
		elseif ($show_by == "do_amt"){
			$var = "sum(total_amount) as amount, sum(do_total_gst_amt) as gst_amount, sum(do_total_gross_amt) gross_amount";
		}

		//Get branch data
		$sql = "select branch_id, count(*) as qty, do_branch_id, do_date, $var , do_type
				from do
				WHERE active=1 and status=1 and approved=1
				and branch_id in ($bid)
				and do_date between $filter $filter2
				Group By branch_id,do_branch_id,do_type";

		//print $sql;exit;

		$is_under_gst = 0;
		$q1 = $con_multi->sql_query($sql) or die(sql_error());
    	if($con_multi->sql_numrows()>0){
		    while($t = $con_multi->sql_fetchrow($q1)){
		        $branch_key = $t['branch_id'];
		        $branch_name = get_branch_code($t['branch_id']);
		        
		        $do_branch_key = $t['do_branch_id'];
                $do_branch_name = get_branch_code($t['do_branch_id']);

            	if (empty($do_branch_name))	$do_branch_name = "OTHERS";

		       // $branch[$branch_key]['do_date'] = $t['do_date'];
		      //  $branch[$branch_key][$do_branch_name]=$t['do_type'];
		       // $branch[$branch_key]['row']=$t['row'];

                $branch[$branch_key]['branch_name'] = $branch_name;
		        //$branch[$branch_key][$do_branch_key][$t['do_type']]['amt'] += $t['amount'];
		        if ($branch[$branch_key][$do_branch_key]['do_branch_name']!=$do_branch_name)
		        $branch[$branch_key]['row']+=1;

				$branch[$branch_key][$do_branch_key]['do_branch_name']=$do_branch_name;

		        $do_branch[$do_branch_key][$branch_key]['amt'][$t['do_type']]+= $t['amount'];
		        $do_branch[$do_branch_key][$branch_key]['do_branch_name']=$do_branch_name;
		        $do_branch[$do_branch_key][$branch_key]['qty'][$t['do_type']]+= $t['qty'];
		        $do_branch[$do_branch_key]['do_branch_name']=$do_branch_name;

				$do_branch[$do_branch_key][$branch_key]['gst_amt'][$t['do_type']] += $t['gst_amount'];
	            $do_branch[$do_branch_key][$branch_key]['gross_amt'][$t['do_type']] += $t['gross_amount'];
				if($t['gst_amount'] > 0) $is_under_gst = 1;
			}
			//print_r($branch) ;
			//print_r($do_branch);

			//exit;
			
		//	print (count($branch['transfer'])) ;exit;
		}
		
		//-->end branch data
	    $smarty->assign('branch',$branch);
	    $smarty->assign('do_branch',$do_branch);
	    $smarty->assign('date',$_REQUEST['date']);
	    $smarty->assign('is_under_gst',$is_under_gst);
		$smarty->assign('at_least_one_under_gst',$_REQUEST['is_gst']);
		$smarty->display('report.do_summary.b_row.tpl');
	}

	function default_values(){
	}
}

$con_multi= new mysql_multi();
if(!$con_multi){
	die("Error: Fail to connect report server");
}

$DOSummary = new DOSummary('DO Summary By Day / Month');
$con_multi->close_connection();
?>
