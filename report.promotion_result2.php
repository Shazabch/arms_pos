<?php

include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!$sessioninfo['privilege']['PROMOTION']) js_redirect(sprintf($LANG['NO_PRIVILEGE'],'PROMOTION',BRANCH_CODE), "/index.php");

class PromotionResult extends Module
{
	var $where;
	var $groupby;
	var $select;
	var $branch_id;
	var $months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');

	var $months_desc = array(1=>'January',	2=>'February',	3=>'March',	4=>'April',	5=>'May',	6=>'June',	7=>'July',	8=>'August',	9=>'September',	10=>'October',	11=>'November',	12=>'December');

	function __construct($title){
		global $con,$smarty,$sessioninfo;

		$smarty->assign('months', $this->months);

		$con->sql_query("select id,code from branch");
		while($r = $con->sql_fetchrow())
		{
			$branches[] = $r;
		}
		$smarty->assign("branch",$branches);
		$con->sql_freeresult();

		$get_year = "select min(date_from) as min_date,max(date_to) as max_date from promotion";

		$con->sql_query($get_year);
		$y = $con->sql_fetchrow();
		$min_date = explode('-',$y['min_date']);
		$min_year = $min_date[0];
;

		$max_date = explode('-',$y['max_date']);
		$max_year = $max_date[0];

		$count_year = $max_year - $min_year;

		for($i=0; $i<=$count_year; $i++){
			$y = $min_year+$i;
			if($y < 2005)	continue;
			//$years[$i][0] = $y;
			$years[$i]['year'] = $y;
		}

		$smarty->assign("years", $years);
		$con->sql_freeresult();
		
		//Get year and month
		if($_REQUEST['year'] || $_REQUEST['month']){
			$year=$_REQUEST['year'];
			$month=$_REQUEST['month'];
		}else{
			$year=1;
			$month=1;
		}

		$this->begin_date="$year-$month-1";
		$this->end_date="$year-$month-".days_of_month($month, $year);

		//get branch_id
		if (BRANCH_CODE != 'HQ' || !$_REQUEST['branch_id']){
			$this->branch_id=$sessioninfo['branch_id'];
		}else{
			$this->branch_id=$_REQUEST['branch_id'];
		}

		$smarty->assign("PAGE_TITLE",$title);
		
		//filter query
		$this->where="where promotion.date_to >= ".ms($this->begin_date)." and promotion.date_from <= ".ms($this->end_date)." and promotion.promo_type='discount'";

		parent::__construct($title);
	}

	function _default(){
		$this->default_value();
		$this->display();
		exit;
	}

	function show_report(){

		$this->generate_report();

	}

	function output_excel(){
		global $smarty, $sessioninfo;

		include_once("include/excelwriter.php");
		$smarty->assign('no_header_footer', true);
		$filename = "promotion_result_".time().".xls";
		log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Report Promotion Result To Excel($filename)");
		Header('Content-Type: application/msexcel');
		Header('Content-Disposition: attachment;filename='.$filename);

		print ExcelWriter::GetHeader();
		$this->show_report();
		print ExcelWriter::GetFooter();
		exit;
	}

	
	function generate_report()
	{
		global $con, $smarty, $sessioninfo;

		$filt[] = $this->where;

		if($this->branch_id != "all")
		{
			$get_hq=$con->sql_query("select id from branch where code='HQ'");
			$hq = $con->sql_fetchrow($get_hq);
			$hq_id= $hq['id'];

			$filt[] = "promotion.branch_id in ($hq_id,$this->branch_id)";
			$filt[] = "promotion.promo_branch_id like '%i:$this->branch_id;%'";
		}
		
		$this->where= join(' and ', $filt);
		
		//skip promo title filter
		$this->load_title(true);

		if($_REQUEST['promo_title'] != "all")
		{
			$filt[] = "promotion.title = ".ms($_REQUEST['promo_title']);
		}

		if ($filt)	$filter = join(' and ', $filt);

		if($this->branch_id != "all" || BRANCH_CODE != 'HQ')
		{
			$this->single_branch = true;
			$this->display_report($filter);
		}
		else
		{
			$this->single_branch = false;
			$this->display_report($filter);
		}

		if($this->branch_id != 'all')	$branch_code = get_branch_code($this->branch_id);
		$rpt_title[]="Year: $_REQUEST[year]";
		$rpt_title[]="Month: ".$this->months_desc[$_REQUEST['month']];
		$rpt_title[]="Branch: ".($this->branch_id != 'all' ? $branch_code : 'All');
		$rpt_title[]="Promotion Title: ".ucfirst($_REQUEST['promo_title']);

		$report_title=join("&nbsp;&nbsp;&nbsp;&nbsp;",$rpt_title);

		$smarty->assign("report_title",$report_title);

		$this-> display();
	}
	
	function display_report($filter) {
		global $con, $smarty, $sessioninfo, $con_multi;
	
		$min_date_time = strtotime($this->begin_date);
		$max_date_time = strtotime($this->end_date);
		
		$promotion_ids = array();
		$branch_tables = array();
		
		$sql2="select id,promo_branch_id from promotion $filter";
		// print "$sql2<br /><br />";
		$q1 = $con_multi->sql_query($sql2);
		while($r = $con_multi->sql_fetchassoc($q1)) {
			$promotion_ids[] = $r['id'];
			$promo_branch_id = unserialize($r['promo_branch_id']);
			
			if ($promo_branch_id){
				foreach ($promo_branch_id as $bid => $bcode) {
					if ($this->single_branch) {
						if ($bid != $this->branch_id) continue;
					}
					if (!in_array($bid,$branch_tables)) $branch_tables[] = $bid;
				}
			}
		}
		$con_multi->sql_freeresult($q1);
		
		if ($promotion_ids) {
			$promo_id_str = join(',',$promotion_ids);
			$sql3 = "select p.promo_branch_id, sku_item_id from promotion_items pi left join promotion p on pi.promo_id = p.id and pi.branch_id = p.branch_id where p.id in ($promo_id_str)";
			//print "$sql3<br /><br />";
			$q3 = $con_multi->sql_query($sql3);
			
			$sku_item_id_list_arr = array();
			while($r = $con_multi->sql_fetchassoc($q3)) {
			
				if ($r['sku_item_id']) {
					$for_branches = unserialize($r['promo_branch_id']);
					foreach ($for_branches as $fbid => $dummy) {
						if (in_array($fbid,$branch_tables)) {
							if (!in_array($r['sku_item_id'],$sku_item_id_list_arr[$fbid])) $sku_item_id_list_arr[$fbid][] = $r['sku_item_id'];
						}
					}
				}
			}
			$con_multi->sql_freeresult($q3);
			/*
			print '<pre>';
			print_r($sku_item_id_list_arr);
			print '</pre>';
			*/
			
			foreach ($branch_tables as $bid) {
				
				$sql4 = "
				select qty,Year(date) as year,Month(date) as month, Day(date) as day,amount,cost,sku_item_id,si.description,si.sku_item_code
				from sku_items_sales_cache_b".$bid." sisc
				left join sku_items si on sisc.sku_item_id = si.id
				where sku_item_id in (".join(',',$sku_item_id_list_arr[$bid]).") and date between ".ms($this->begin_date)." and ".ms($this->end_date);
				//print "$sql4<br /><br />";
				
				$q4 = $con_multi->sql_query($sql4,false,false);
				while($rs = $con_multi->sql_fetchrow($q4)) {
				
					$sku_list[$rs['sku_item_id']][$rs['sku_item_code']]= $rs['description'];
					
					if ($this->single_branch) {
					
						if($rs['sku_item_id']) {
							$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['qty'] += $rs['qty'];
							$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['description'] = $rs['description'];
							$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['amount'] += $rs['amount'];
							$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['cost'] += $rs['cost'];
							
							$total[$rs['year']][$rs['month']][$rs['day']]['qty'] += $rs['qty'];
							$total[$rs['year']][$rs['month']][$rs['day']]['amount'] += $rs['amount'];
							$total[$rs['year']][$rs['month']][$rs['day']]['cost'] += $rs['cost'];
							
							if (empty($check_date[$rs['year']][$rs['month']]['dd']))
							{
								$check_date[$rs['year']][$rs['month']]['dd'] = $rs['day'];
								$get_date[$rs['year']][$rs['month']][$rs['day']]=1;
							}
							elseif ($check_date[$rs['year']][$rs['month']]['dd'] > $rs['day'])
							{
								unset($get_date[$rs['year']][$rs['month']][$check_date[$rs['year']][$rs['month']]['dd']]);
								$get_date[$rs['year']][$rs['month']][$rs['day']]=1;
								$check_date[$rs['year']][$rs['month']]['dd'] = $rs['day'];
							}
							
							if(!$get_date[$rs['year']][$rs['month']]['end_month']){
								$get_date[$rs['year']][$rs['month']]['end_month'] = days_of_month($rs['month'],$rs['year']);
							}
							
						}
						
						$smarty->assign("single_branch","1");
					}
					
					else {
					
						if($rs['sku_item_id']) {
							$data[$rs['sku_item_id']][$bid]['description'] = $rs['description'];
							$data[$rs['sku_item_id']][$bid]['amount'] += $rs['amount'];
							$data[$rs['sku_item_id']][$bid]['cost'] += $rs['cost'];
							$data[$rs['sku_item_id']][$bid]['qty'] += $rs['qty'];
							
							$total[$bid]['amount'] += $rs['amount'];
							$total[$bid]['cost'] += $rs['cost'];
							$total[$bid]['qty'] += $rs['qty'];
						}
						
					}
					
				}
				
			}
			
			if ($this->single_branch) {
				if($get_date) {
					ksort($get_date);
					foreach($get_date as $year=>$v) {
						ksort($get_date[$year]);
						foreach($get_date[$year] as $month=>$v2) ksort($get_date[$year][$month]);
					}
				}
				$smarty->assign("get_date", $get_date);
			}
			else {
				$con_multi->sql_freeresult($q4);
				$con->sql_query("select id,code from branch where id in (".join(',',$branch_tables).") order by sequence,code");
				while($r = $con->sql_fetchrow()) $grp_branch_code[]= $r;
				$smarty->assign("branch_name",$grp_branch_code );
			}
		}
		
		/*
		print '<pre>';
		print_r($data);
		print '</pre>';
		*/
		
		$smarty->assign("branch_code",BRANCH_CODE );
		$smarty->assign("sku_list",$sku_list);
		$smarty->assign("data",$data);
		$smarty->assign("total",$total);
	}
	
	function load_title($sql = false)
	{
		global $con, $smarty;

		$filt[] = $this->where;

		if($this->branch_id != "all")
		{
			$get_hq=$con->sql_query("select id from branch where code='HQ'");
			$hq = $con->sql_fetchrow($get_hq);
			$hq_id= $hq['id'];

			$filt[] = "promotion.branch_id in ($hq_id,$this->branch_id)";
			$filt[] = "promotion.promo_branch_id like '%i:$this->branch_id;%'";
		}

		$filter = join(' and ', $filt);
		$con->sql_query("select distinct(title) from promotion $filter order by title");

 		while($r = $con->sql_fetchrow())
		{
			$title[] = $r;
		}

		if(!$sql)
		{
			print "<select name=promo_title>";
			print "<option value=all>-- All --</option>";
			foreach($title as $val)
			{
				if($val['title'] !="") {
					print "<option value=\"$val[title]\"";
					if ($_REQUEST['promo_title'] == $val['title']) print "selected";
					print ">".$val['title']."</option>";
				}
			}
			print "</select>";
		}
		else
		{
			$smarty->assign("promo_title",$title);
		}
 
	}
	
	function default_value(){
		global $con,$smarty;
		$con->sql_query("select distinct(title) from promotion $this->where order by title");

		while($r = $con->sql_fetchrow())
		{
			$title[] = $r;
		}

		$smarty->assign("promo_title",$title);
	}
}
$con_multi = new mysql_multi();
$report = new PromotionResult('Promotion Result');
$con_multi->close_connection();
?>
