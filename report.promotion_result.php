<?php

/*
10/28/2013 3:33 PM Fithri
- rewrite the code, get data from pos_items instead if sku_items_sales_cache_b?

11/29/2013 4:42 PM Fithri
- fix bug where qty wrong when select individual branch

12/17/2013 3:40 PM Fithri
- remove checking for prune_status

1/21/2014 3:44 PM Fithri
- allow to filter by currently active (running) promotion

2/2/2015 2:38 PMAndy
- Fix the report should minus the price with discount amount.

05/31/2016 16:00 Edwin
- Add "Mcode" and "Artno" in table result.

7/4/2016 11:28 AM Andy
- Fix year/month title when select current active promotion.
- Change to only load approved promotion when select current active promotion.

2/26/2020 10:20 AM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

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
	
	var $months = array(
		1=>'Jan',
		2=>'Feb',
		3=>'Mar',
		4=>'Apr',
		5=>'May',
		6=>'Jun',
		7=>'Jul',
		8=>'Aug',
		9=>'Sep',
		10=>'Oct',
		11=>'Nov',
		12=>'Dec'
	);

	var $months_desc = array(
		1=>'January',
		2=>'February',
		3=>'March',
		4=>'April',
		5=>'May',
		6=>'June',
		7=>'July',
		8=>'August',
		9=>'September',
		10=>'October',
		11=>'November',
		12=>'December'
	);

	function __construct($title){
		global $con,$smarty,$sessioninfo,$con_multi,$appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$smarty->assign('months', $this->months);
		
		$con_multi->sql_query("select id,code from branch");
		while($r = $con_multi->sql_fetchrow())
		{
			$branches[] = $r;
		}
		$smarty->assign("branch",$branches);
		$con_multi->sql_freeresult();

		$get_year = "select min(date_from) as min_date,max(date_to) as max_date from promotion";
		$con_multi->sql_query($get_year);
		$y = $con_multi->sql_fetchrow();
		$min_date = explode('-',$y['min_date']);
		$min_year = $min_date[0];
		
		$max_date = explode('-',$y['max_date']);
		$max_year = $max_date[0];
		
		$count_year = $max_year - $min_year;
		
		for($i=0; $i<=$count_year; $i++){
			$y = $min_year+$i;
			if($y < 2005) continue;
			$years[$i]['year'] = $y;
		}
		$smarty->assign("years", $years);
		$con_multi->sql_freeresult();
		
		//Get year and month
		if ($_REQUEST['year'] || $_REQUEST['month']) {
			$year=$_REQUEST['year'];
			$month=$_REQUEST['month'];
		} else {
			$year=2005;
			$month=1;
		}

		$this->begin_date = "$year-$month-1";
		$this->end_date = "$year-$month-".days_of_month($month, $year);
		$this->where = "where promotion.date_to >= ".ms($this->begin_date)." and promotion.date_from <= ".ms($this->end_date)." and promotion.promo_type='discount'";

		//filter query
		if ($_REQUEST['current_active']) {
			$this->where="where promotion.date_from <= ".ms(date('Y-m-d'))." and promotion.date_to >= ".ms(date('Y-m-d'))." and promotion.promo_type='discount' and promotion.active=1 and promotion.status=1 and promotion.approved=1";
			$_REQUEST['year'] = date("Y");
			$_REQUEST['month'] = mi(date("m"));
		}
		
		//get branch_id
		if (BRANCH_CODE != 'HQ' || !$_REQUEST['branch_id']) {
			$this->branch_id=$sessioninfo['branch_id'];
		} else {
			$this->branch_id=$_REQUEST['branch_id'];
		}

		$smarty->assign("PAGE_TITLE",$title);

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
		global $con, $smarty, $sessioninfo, $con_multi;

		$filt[] = $this->where;

		if($this->branch_id != "all")
		{
			$get_hq=$con_multi->sql_query("select id from branch where code='HQ'");
			$hq = $con_multi->sql_fetchrow($get_hq);
			$con_multi->sql_freeresult($get_hq);
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
	
		$promotion_ids = array();
		$branch_list = array();
		
		$sql2="select id, branch_id, promo_branch_id from promotion $filter";
		//print "<br /><br />$sql2<br />";
		$q1 = $con_multi->sql_query($sql2);
		while($r = $con_multi->sql_fetchassoc($q1)) {
			
			$promotion_ids[] = mi($r['id'])*1000 + mi($r['branch_id']);
			$promo_branch_id = unserialize($r['promo_branch_id']);
			
			if ($promo_branch_id){
				foreach ($promo_branch_id as $bid => $bcode) {
					if ($this->single_branch) {
						if ($bid != $this->branch_id) continue;
					}
					if (!in_array($bid,$branch_list)) {
						$branch_list[] = $bid;
						// var_dump($bid);
					}
				}
			}
		}
		$con_multi->sql_freeresult($q1);
		
		if ($_REQUEST['current_active']) {
			
			$this->begin_date = $this->end_date = '0000-00-00';
		
			$sql3="select min(date_from) as min_act, max(date_to) as max_act from promotion $filter";
			//print "<br /><br />$sql3<br />";
			$q3 = $con_multi->sql_query($sql3);
			$r3 = $con_multi->sql_fetchassoc($q3);
			if ($r3) {
				$this->begin_date = $r3['min_act'];
				$this->end_date = $r3['max_act'];
			}
			$con_multi->sql_freeresult($q3);
		}
		
		/*
		print '<pre>';
		print_r($promotion_ids);
		print '</pre>';
		*/
		
		$branch_filter = $this->single_branch ? 'pi.branch_id = '.mi($this->branch_id) : '1';
		
		$sql4 = "select
					pi.branch_id,
					pi.qty,
					pi.date,
					Year(pi.date) as year,
					Month(pi.date) as month,
					Day(pi.date) as day,
					(pi.price-pi.discount) as amount,
					pi.sku_item_id,
					si.sku_item_code,
					si.mcode,
					si.artno,
					si.description
					from pos_items pi
					left join pos on pi.branch_id = pos.branch_id and pi.counter_id = pos.counter_id and pi.date = pos.date and pi.pos_id = pos.id
					left join sku_items si on pi.sku_item_id = si.id
					where $branch_filter and pi.promotion_id in (".join(',',$promotion_ids).")
					and pi.date >= ".ms($this->begin_date)." and pi.date <= ".ms($this->end_date)."
					and pos.cancel_status = 0
				";
		//print "<br /><br />$sql4";
		
		$q4 = $con_multi->sql_query($sql4,false,false);
		while($rs = $con_multi->sql_fetchrow($q4)) {
		
			$sku_list[$rs['sku_item_id']]['arms'] = $rs['sku_item_code'];
			$sku_list[$rs['sku_item_id']]['mcode'] = $rs['mcode'];
			$sku_list[$rs['sku_item_id']]['artno'] = $rs['artno'];
			$sku_list[$rs['sku_item_id']]['desc'] = $rs['description'];
			
			$cost_price = get_sku_item_cost_selling(mi($rs['branch_id']), mi($rs['sku_item_id']), $rs['date'], array('cost'));
			$cp = floatval($rs['qty']) * floatval($cost_price['cost']);
			
			if ($this->single_branch) {
			
				if($rs['sku_item_id']) {
				
					$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['qty'] += $rs['qty'];
					$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['description'] = $rs['description'];
					$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['amount'] += $rs['amount'];
					$data[$rs['sku_item_id']][$rs['year']][$rs['month']][$rs['day']]['cost'] += $cp;
					
					$total[$rs['year']][$rs['month']][$rs['day']]['qty'] += $rs['qty'];
					$total[$rs['year']][$rs['month']][$rs['day']]['amount'] += $rs['amount'];
					$total[$rs['year']][$rs['month']][$rs['day']]['cost'] += $cp;
					
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
					$data[$rs['sku_item_id']][$rs['branch_id']]['description'] = $rs['description'];
					$data[$rs['sku_item_id']][$rs['branch_id']]['amount'] += $rs['amount'];
					$data[$rs['sku_item_id']][$rs['branch_id']]['cost'] += $cp;
					$data[$rs['sku_item_id']][$rs['branch_id']]['qty'] += $rs['qty'];
					
					$total[$rs['branch_id']]['amount'] += $rs['amount'];
					$total[$rs['branch_id']]['cost'] += $cp;
					$total[$rs['branch_id']]['qty'] += $rs['qty'];
				}
				
			}
			
		}
		$con_multi->sql_freeresult($q4);
		
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
			if ($branch_list) {
				$con_multi->sql_query("select id,code from branch where id in (".join(',',$branch_list).") order by sequence,code");
				while($r = $con_multi->sql_fetchrow()) $grp_branch_code[]= $r;
				$con_multi->sql_freeresult();
				$smarty->assign("branch_name",$grp_branch_code );
			}
		}
		
		/*
		print '<pre>';
		print_r($get_date);
		print '</pre>';
		*/
		
		$smarty->assign("branch_code",BRANCH_CODE );
		$smarty->assign("sku_list",$sku_list);
		$smarty->assign("data",$data);
		$smarty->assign("total",$total);
	}
	
	function load_title($sql = false)
	{
		global $con, $smarty, $con_multi;

		$filt[] = $this->where;

		if($this->branch_id != "all")
		{
			$get_hq=$con_multi->sql_query("select id from branch where code='HQ'");
			$hq = $con_multi->sql_fetchrow($get_hq);
			$con_multi->sql_freeresult($get_hq);
			$hq_id= $hq['id'];

			$filt[] = "promotion.branch_id in ($hq_id,$this->branch_id)";
			$filt[] = "promotion.promo_branch_id like '%i:$this->branch_id;%'";
		}

		$filter = join(' and ', $filt);
		$con_multi->sql_query("select distinct(title) from promotion $filter order by title");

 		while($r = $con_multi->sql_fetchrow())
		{
			$title[] = $r;
		}
		$con_multi->sql_freeresult();

		if(!$sql)
		{
			print "<select name=promo_title>";
			print "<option value=all>-- All --</option>";
			if ($title) {
				foreach($title as $val)
				{
					if($val['title'] !="") {
						print "<option value=\"$val[title]\"";
						if ($_REQUEST['promo_title'] == $val['title']) print "selected";
						print ">".$val['title']."</option>";
					}
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
		global $con,$smarty,$con_multi;
		$con_multi->sql_query("select distinct(title) from promotion $this->where order by title");

		while($r = $con_multi->sql_fetchrow())
		{
			$title[] = $r;
		}
		$con_multi->sql_freeresult();

		$smarty->assign("promo_title",$title);
	}
}

//$con_multi = new mysql_multi();
$report = new PromotionResult('Promotion Result');
//$con_multi->close_connection();
?>
