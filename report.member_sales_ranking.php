<?php

/*
23/3/2009 4:45:00 PM Andy
- change card <-> nric retrieve method, using php calculate

1/25/2011 5:48:23 PM Alex
- change use report server
- fix date bugs

5/25/2011 5:44:43 PM Alex
- fix branches amount and transaction bugs

6/24/2011 6:15:44 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 12:40:04 PM Andy
- Change split() to use explode()

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

2/1/2019 2:38 PM Andy
- Fixed branch group cannot show data.
- Fixed incorrect column display when show all branch data.
- Optimise report performance.

2/15/2019 5:29 PM Andy
- Fixed Post Code and City filter not working.

2/18/2020 10:43 AM William
- Enhanced to change $con connection to use $con_multi.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_MEMBERSHIP', BRANCH_CODE), "/index.php");

class MemberSalesRanking extends Report
{
	function run_report($bid, $tbl_name)
	{
	    global $con_multi;
	    
	    $filter = $this->filter;
	    
	    //$card_nric = $this->card_nric;
	    //$member_info = $this->member_info;
	    
	    $tbl = $tbl_name['member_sales_cache'];
	    /*$sql="select year,month,pos.card_no,nric,membership.name, sum(transaction_count) as transaction,sum(amount) as amount from $tbl pos join card_nric using(card_no) left join membership using(nric) where $filter group by nric,year,month order by month asc,amount desc,transaction desc";*/
	    $sql = "select year,month,pos.card_no,sum(transaction_count) as transaction,sum(amount) as amount, m.nric, m.card_no as latest_card_no, m.name
		from $tbl pos 
		left join membership m on m.nric=
		(
		select m2.nric
		from membership m2
		left join membership_history mh on mh.nric=m2.nric
		where mh.card_no=pos.card_no
		order by m2.nric limit 1
		)
		where $filter group by card_no,year,month order by month asc,amount desc,transaction desc";
	    //print $sql; die();
		
		$q1 = $con_multi->sql_query($sql,false,false);

		if($con_multi->sql_numrows($q1)>0){
		    while($t = $con_multi->sql_fetchassoc($q1)){
		        $lbl = sprintf("%04d%02d", $t['year'], $t['month']);
				$this->label[$lbl] = $this->months[$t['month']] ." " . $t['year'];
				
				// Get member info
				/*if(!isset($this->card_nric[$t['card_no']])){
					$q_mh = $con_multi->sql_query("select distinct(mh.card_no) as card_no, nric, m.name,m.card_no as real_card_no
			from membership_history mh
			left join membership m using(nric)
			where mh.card_no=".ms($t['card_no'])." order by nric limit 1");
					$this->card_nric[$t['card_no']] = $con_multi->sql_fetchassoc($q_mh);
					$con_multi->sql_freeresult($q2);
				}*/
				
		        $nric = $t['nric'];

			    $this->table[$nric]['card_no'] = $t['latest_card_no'] ? $t['latest_card_no'] : $t['card_no'];	// put original card_no
			    //$this->table[$nric]['name']= $this->card_nric[$t['card_no']]['name'];
				$this->table[$nric]['nric']= $nric;
				$this->table[$nric]['name']= $t['name'];
				$this->table[$nric]['transaction'][$lbl]+=$t['transaction'];
				$this->table[$nric]['amount'][$lbl]+=$t['amount'];
				$this->table[$nric]['transaction'][$bid]+=$t['transaction'];
				$this->table[$nric]['amount'][$bid]+=$t['amount'];
				$this->table[$nric]['total']['transaction']+=$t['transaction'];
				$this->table[$nric]['total']['amount']+=$t['amount'];
			}
		}
		
		$con_multi->sql_freeresult($q1);
	}
	
	function sort_table($a,$b)
	{
		$order_type = $_REQUEST['order_type'];
		if($order_type=='top'){
            if ($a['total']['amount']==$b['total']['amount']) return 0;
        	return ($a['total']['amount']>$b['total']['amount']) ? -1 : 1;
		}else{
            if ($a['total']['amount']==$b['total']['amount']) return 0;
        	return ($a['total']['amount']<$b['total']['amount']) ? -1 : 1;
		}
	}

	function generate_report()
	{
		global $con, $smarty, $con_multi;
		
		//$con->sql_query("drop table if exists card_nric");
	    //$con->sql_query("create table if not exists card_nric engine=memory select distinct card_no, nric from membership_history where remark<>'CB'");
	    
	    /*$sql = "select distinct(mh.card_no) as card_no, nric, m.name,m.card_no as real_card_no
from membership_history mh
left join membership m using(nric)
where remark<>'CB' order by nric";
		$q_mh = $con->sql_query($sql);
		while($r = $con->sql_fetchrow($q_mh)){
			$card_nric[$r['card_no']] = $r['nric'];
			$member_info[$r['nric']]['name'] = $r['name'];
			$member_info[$r['nric']]['card_no'] = $r['real_card_no'];
		}
		
		$this->card_nric = $card_nric;
		$this->member_info = $member_info;*/
		$this->card_nric = array();
	    
	    $minimum_transaction = intval($_REQUEST['min_tran']);
	    $minimum_amount = doubleval($_REQUEST['min_amount']);
	    $max_transaction = intval($_REQUEST['max_tran']);
	    $max_amount = doubleval($_REQUEST['max_amount']);
	    $top_number = intval($_REQUEST['top_num']);
	    
		if($top_number > 10000 || $top_number < 1){
			$top_number = 10000;
		}

		$branch_group = $this->branch_group;
		$branches = $smarty->get_template_vars('branches');

		if(strpos($_REQUEST['branch_id'],'bg,')===0){   // is branch group
			list($dummy,$bg_id) = explode(",",$_REQUEST['branch_id']);
			//$tbl_name['member_sales_cache'] = "member_sales_cache_bg".$bg_id;
			//$this->run_report($bg_id+10000,$tbl_name);
			//$branch_name = $branch_group['header'][$bg_id]['code'];
			if($branch_group['items'][$bg_id]){
				foreach($branch_group['items'][$bg_id] as $bid => $b){
					$tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
					$this->run_report($bid,$tbl_name);
				}
			}
		}else{
            $bid  = get_request_branch(true);
			if (BRANCH_CODE != 'HQ'){	// login as branch, only show own branch
	            $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
	            $this->run_report($bid,$tbl_name);
	            $branch_name = BRANCH_CODE;
			}else{
				if($bid==0){	// Show all branch
	                $branch_name = "All";
					foreach($branches as $b){
						$tbl_name['member_sales_cache'] = "member_sales_cache_b".$b['id'];
			            $this->run_report($b['id'],$tbl_name);
					}
				}else{
	                $tbl_name['member_sales_cache'] = "member_sales_cache_b".$bid;
		            $this->run_report($bid,$tbl_name);
					$branch_name = get_branch_code($bid);
				}
			}
		}
		
		if($this->table){
			// Filter data
            foreach($this->table as $k => $r)
			{
				$a1=array();$a2=array();
				foreach($this->label as $lbl=>$dummy)
				{
					$a1[$lbl] = doubleval($this->table[$k]['amount'][$lbl]);
				}
				if (min($a1)<$minimum_amount) { unset($this->table[$k]); continue; }
				if($max_amount>0){
	                if (max($a1)>$max_amount) { unset($this->table[$k]); continue; }
				}
			    foreach($this->label as $lbl=>$dummy)
				{
					$a2[$lbl] = doubleval($this->table[$k]['transaction'][$lbl]);
				}
			    if (min($a2)<$minimum_transaction) unset($this->table[$k]);
			    if($max_transaction>0){
	                if (max($a2)>$max_transaction) { unset($this->table[$k]); continue; }
				}
			}
			
			// Sort Data
			usort($this->table, array($this,"sort_table"));
			
			if($this->table){
				// Clear those member out of Top / Bottom Rank
				foreach($this->table as $k => $r){
					if($k+1 > $top_number){
						unset($this->table[$k]);
					}
				}
			}
		}

        if($this->label)	ksort($this->label);
    
		//print_r($this->table);
		
   		$rpt_title[] = "Branch: $branch_name";
		$rpt_title[] = "Date: from ".$this->date_from." to ".$this->date_to;

		$report_title = join('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $rpt_title);
    	
    	$smarty->assign('report_title',$report_title);
    
		$smarty->assign('label',$this->label);
		$smarty->assign('table',$this->table);
		$smarty->assign('branch_name',$branch_name);
		$smarty->assign('minimum_transaction',$minimum_transaction);
		$smarty->assign('minimum_amount',$minimum_amount);
		$smarty->assign('top_number',$top_number);
	}
	
	function process_form()
	{
	    global $con,$smarty,$sessioninfo;
		// do my own form process
		
		// call parent
		parent::process_form();

		$this->date_from=$_REQUEST['date_from'];
		$this->date_to=$_REQUEST['date_to'];

		$mtest =strtotime("+1 year",strtotime($this->date_from));
		if (strtotime($this->date_to) > $mtest || strtotime($this->date_to) < strtotime($this->date_from)){
        	$this->date_to = date("Y-m-d",$mtest);
        	$_REQUEST['date_to'] = $this->date_to;
		}
		
		$filter[] = 'date between '.ms($this->date_from).' and '.ms($this->date_to);
	    $filter[] = "pos.card_no<>''";
	    if(trim($_REQUEST['post_code'])!=''){
			$filter[] = 'm.postcode='.ms(trim($_REQUEST['post_code']));
		}
		if(trim($_REQUEST['city'])!=''){
			$filter[] = 'm.city='.ms(trim($_REQUEST['city']));
		}
	    $filter = join(' and ' , $filter);
	    
	    $this->filter = $filter;
	}	

	function default_values()
	{
	    $view_type = $_REQUEST['view_type'];
	    if($view_type=="day"){
                $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 month"));
		}else{
            $_REQUEST['date_from'] = date("Y-m-d", strtotime("-1 year"));
		}
		$_REQUEST['date_to'] = date("Y-m-d");
	}
}
//$con_multi = new mysql_multi();
$report = new MemberSalesRanking('Member Sales Ranking');
//$con_multi->close_connection();
?>
