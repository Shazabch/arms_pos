<?php
/*
4/7/2011 10:29:35 AM Alex
- new report voucher module

4/20/2011 10:54:51 AM Alex
- change use counter name, will found duplicate key if use all branch and counter id

4/21/2011 12:03:58 PM Alex
- add check voucher had been cancelled after used 

6/27/2011 10:06:47 AM Andy
- Make all branch default sort by sequence, code.

10/11/2011 3:05:56 PM Alex
- Change filter of searching code

2/1/2012 2:35:20 PM Alex
- change filter of searching code to match all if over 12 digit

2/13/2012 11:08:53 AM Alex
- add checking on expired voucher

2/16/2012 7:04:22 PM Alex
- fix bugs check duplicate voucher

5/28/2012 6:23:32 PM Justin
- Fixed bugs of showing wrong status information.
- Fixed bugs of wrong counting for duplicated vouchers.
- Fixed bugs of missing one status information "Used before Valid Date".

5/30/2012 1:49:34 PM Justin
- Added missing filter to filter off those older version of voucher that causes wrong counting for duplication voucher.

7/5/2012 6:09:23 PM Justin
- Bug fixed to include 2 different types of messages while voucher has being cancelled before/after used.

8/23/2012 10:07 AM Justin
- Bug fixed on showing query error message while filter with voucher status.

12/11/2012 11:27 AM Justin
- Bug fixed on system calculated wrongly for the voucher amount and count.

2/21/2017 3:32 PM Justin
- Bug fixed on voucher value options is not showing as per voucher setup.

4/26/2017 11:10 AM Khausalya
- Enhanced changes from RM to use config setting. 

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

include("masterfile_voucher.include.php");
class REPORT_VOUCHER_DETAILS extends Module
{
     function __construct($title){
     	global $con, $smarty, $sessioninfo, $config;

		//default assign
        if (!$_REQUEST['to_date']) $_REQUEST['to_date'] = date('Y-m-d');
		if (!$_REQUEST['from_date']) $_REQUEST['from_date'] = date('Y-m-d', strtotime("-1 month"));

		//Branches
		$con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchrow()){
			$branches[$r['id']] = $r;
			$this->branches[$r['id']]=$r['code'];
		}
    
    	$smarty->assign("branches", $branches);
    
        $bid  = get_request_branch(true);
        $this->bid=$bid;
		$this->get_counter_name(false);
		
		$this->date_from = $_REQUEST['from_date'];
		$this->date_to = $_REQUEST['to_date'];

       	$smarty->assign('form',$_REQUEST);
		
		// select all voucher setup value
		$vs_list = array();
		$q1 = $con->sql_query('select id, voucher_value from mst_voucher_setup order by voucher_value');
		
		while($r = $con->sql_fetchrow($q1)){
			$vs_list[$r['voucher_value']] = $r['voucher_value'];
		}
		$con->sql_freeresult($q1);

		// need to check if customer never setup voucher before, load from config
		if(!$vs_list) $vs_list = $config['voucher_value_prefix'];
		
		$smarty->assign('vs_list',$vs_list);

      	parent::__construct($title);
    }

	function _default(){
		$this->display();
		exit;
	}

	function show_report(){

		$this->generate_report();

		$this->display();
	}

	function output_excel(){
	    global $smarty, $sessioninfo;
		
		$this->generate_report();

        	include_once("include/excelwriter.php");
	    	$smarty->assign('no_header_footer', true);
	    	$filename = "voucher_details_".time().".xls";
	    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Voucher Details Report To Excel($filename)");
	    	Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->display();
		print ExcelWriter::GetFooter();
	    exit;
	}

	function get_counter_name($ajax=true){
	    global $con,$smarty;

      $filter = array();
      if ($this->bid)	$where = "where p.branch_id=$this->bid";
		
      $sql="select p.id as counter_id, p.network_name
			from counter_settings p
			$where group by p.network_name order by p.network_name";
			
		$con->sql_query($sql);

		while($r = $con->sql_fetchrow())
		{
			$this->pos_counters[$r['network_name']] = $r;
		}
		$con->sql_freeresult();

		//if not ajax call
		if (!$ajax){
			$smarty->assign('counters',$this->pos_counters);
			return;
		}
		
		$options="<select name='counter_name' >";
		if ($this->pos_counters){

			if (count($this->pos_counters)>1)	$options .= "<option value='all'>- All -</option>";
			foreach ($this->pos_counters as $counter_name => $data){
				if ($_REQUEST['counter_name'] == $counter_name) $selected="selected";
				else  $selected="";
		
				$options .= "<option value='$counter_name' $selected >$data[network_name]</option>";
			}
		}else{
			$options .= "<option value=''>No Data</option>";
		}
		$options.="</select>";

		print $options;
	}

	function generate_report(){
	  global $con, $smarty, $sessioninfo, $LANG, $config;

		$form=$_REQUEST;

    $con_multi=new mysql_multi();
        //$con_multi = $con;
        
        //===============Report Title===========
		//branch
		if ($form['branch_id'] != 'all'){
			$filter[] = "pp.branch_id= $this->bid";
			$counter_filter = " and p.branch_id=$this->bid";
		}else
			$report_title_arr[] = "Branch: All";
       
        //counter
    if ($form['counter_name']){
			if ($form['counter_name'] != 'all'){
	  			$counter_sql="select p.id as counter_id
	      			from counter_settings p
	      			where p.network_name=".ms($form['counter_name']). $counter_filter;
	      			
 				$cs_id=$con->sql_query($counter_sql);
		        while ($cs=$con->sql_fetchassoc($cs_id)){
		          $counter_id_arr[]=$cs['counter_id'];  
		        }
		        $con->sql_freeresult($cs_id);
		        
		    	$filter[] = "pp.counter_id in (".join(",",$counter_id_arr).")";
			}else
		    $report_title_arr[] = "Counter: All";
		}else
		    $err[]=sprintf($LANG['VOU_MISS_DATA'],"counter");

        //Date
        $report_title_arr[] = "Date: $this->date_from to $this->date_to";

		//voucher amount
        if ($form['voucher_amount'] && $form['voucher_amount']!='all'){
			$filter[]="pp.amount=".ms($form['voucher_amount']);
	        $report_title_arr[] = "Voucher Amount: $form[voucher_amount]";
		}else
		    $report_title_arr[] = "Voucher Amount: All";
		
		//status
		if ($form['status'])    $report_title_arr[] = "Status ".ucfirst($form['status']);
		
		//search code
		if ($form['search_code'] != ""){
		    $code_length = strlen(trim($form['search_code']));
			// str_pad($form['search_code'],7,"0",STR_PAD_LEFT)
		    if ($code_length < 12)
                $filter[]="(pp.remark like ".ms(replace_special_char($form['search_code']).'%').")";
			else
			    $filter[]="pp.remark=".ms($form['search_code']);

	        $report_title_arr[] = "Code: $form[search_code]";
		}

		if ($err){
			$smarty->assign("err",$err);
			return;
		}

		$report_title=join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title_arr);
		$smarty->assign('report_title',$report_title);
        //====================================

		//================start filter===================
		$filter[]="pp.type='Voucher'";

		//date
		$from_date=ms($this->date_from);
		$to_date=ms($this->date_to);

		$filter[]= "pp.date between $from_date and $to_date";
        $filter[]= "pos.cancel_status=0 and pos.prune_status=0 and pp.adjust=0";


		if ($form['status'] == "normal"){
			$having="having voucher_code is not null and amount=voucher_value and LENGTH(remark) = 12 and pos_time between valid_from and valid_to";
		}elseif($form['status'] == "abnormal"){
			$having="having voucher_code is null or amount!=voucher_value or LENGTH(remark) != 12 or pos_time < valid_from or pos_time > valid_to";
		}

        if ($filter)    $where = "where ".join(" and ", $filter);

		//==============end filter====================

		$sql="select pp.*, sum(pp.amount) as amount, mv.voucher_value,mv.cancelled as cancel_date,mv.cancel_status as mv_cancel_status, pos.pos_time,pos.receipt_no,mv.code as voucher_code,mv.valid_from,mv.valid_to, branch.code as branch_code,cs.network_name, u.u as approved_by
			from pos_payment pp
			left join mst_voucher mv on mv.code = SUBSTRING(pp.remark,1,7) 
			left join pos on pp.branch_id=pos.branch_id and pp.counter_id=pos.counter_id and pp.pos_id=pos.id and pp.date=pos.date
			left join counter_settings cs on pp.branch_id=cs.branch_id and pp.counter_id=cs.id
			left join branch on pp.branch_id=branch.id
			left join user u on pp.approved_by=u.id
			$where
			group by pp.branch_id,pp.counter_id, pp.pos_id,pp.date,pos.receipt_no,pp.remark
			$having
			order by pp.date,receipt_no,voucher_code";

		$q1 = $con_multi->sql_query($sql);

		if ($con_multi->sql_numrows($q1)>0){
		    $n=0;
			while($r=$con_multi->sql_fetchassoc($q1)){
				// search and count the voucher used times
				if(strlen($r['remark']) != 12){
					$q2 = $con_multi->sql_query("select count(*) as count from pos_payment pp2 left join pos p2 on p2.id = pp2.pos_id and p2.branch_id = pp2.branch_id and p2.counter_id = pp2.counter_id and p2.date = pp2.date where pp2.type=".ms($r['type'])." and pp2.remark=".ms($r['remark'])." and p2.cancel_status=0 and pp2.changed=0");
				}else{
					$q2 = $con_multi->sql_query("select count(*) as count from pos_payment pp2 left join pos p2 on p2.id = pp2.pos_id and p2.branch_id = pp2.branch_id and p2.counter_id = pp2.counter_id and p2.date = pp2.date where pp2.type=".ms($r['type'])." and pp2.remark like concat(SUBSTRING(".ms($r['remark']).",1,7),'%') and p2.cancel_status=0 and length(pp2.remark) = 12 and pp2.changed=0");
					
				}
				$count_info = $con_multi->sql_fetchassoc($q2);
				$con_multi->sql_freeresult($q2);
				$r['used'] = $count_info['count'];
				$remark = $r['remark'];

				if($form['status'] == "normal" && $r['used'] != 1) continue;

				if (!$r['voucher_code'])
					$status = $LANG['VOU_ERR_NOT_EXIST'];
				elseif (strlen($remark)<12)
					$status = $LANG['VOU_ERR_INVALID_CODE'];
				elseif ($r['amount'] != $r['voucher_value'])
					$status = sprintf($LANG['VOU_ERR_AMOUNT_NOT_MATCH'],$config["arms_currency"]["symbol"] . " " .number_format($r['voucher_value']));
				elseif ($r['used'] >1)
					$status = $LANG['VOU_ERR_DUPLICATE'];
				elseif ($r['mv_cancel_status']){
					if(strtotime($r['cancel_date']) > strtotime($r['pos_time']))
						$status = $LANG['VOU_ERR_USED_AFTER_CANCEL'];
					else  $status = $LANG['VOU_ERR_USED_BEFORE_CANCEL'];
				}elseif ($r['valid_to'] > 0 && strtotime($r['pos_time']) > strtotime($r['valid_to']))
					$status = $LANG['VOU_ERR_AFTER_EXPIRED'];
				elseif ($r['valid_from'] > 0 && strtotime($r['pos_time']) < strtotime($r['valid_from']))
					$status = $LANG['VOU_ERR_BEFORE_VALID_DATE'];
				elseif ($r['valid_from'] == 0 || strtotime($r['pos_time']) < strtotime($r['valid_from'])) 
					$status = $LANG['VOU_ERR_BEFORE_ACTIVATED_DATE'];
				else $status = 'OK';

				if($status == "OK" && $form['status'] == "abnormal" && $r['used'] <= 1) continue;
				
				$branch_id = $r['branch_id'];
				$counter_id = $r['counter_id'];
				$pos_id = $r['pos_id'];

				$receipt_no = $r['receipt_no'];
				$date = $r['date'];

				$top_head[$branch_id]=$r['branch_code'];
				$mid_head[$branch_id][$counter_id]=$r['network_name'];

				//$detail[$branch_id][$counter_id][$date][$remark][$receipt_no]['remark'] = $r['remark'];
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['pos_id'] += $pos_id;
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['used'] += $r['used'];
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['amount'] += $r['amount'];
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['date'] = $date;
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['approved_by'] = $r['approved_by'];
				$detail[$branch_id][$counter_id][$date][$receipt_no][$remark]['status'] = $status;
			}
		}

		$smarty->assign('table', 1);
		$smarty->assign('top_head', $top_head);
		$smarty->assign('mid_head', $mid_head);
		$smarty->assign('detail', $detail);
	}
}

$report = new REPORT_VOUCHER_DETAILS('Voucher Details Report');

?>
