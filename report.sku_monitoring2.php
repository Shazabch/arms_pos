<?php
/*
6/28/2010 12:51:05 PM Andy
- Create New SKU Monitoring Report.

9/1/2010 2:40:05 PM Andy
- Change calculate holding cost base on bank interest rate.
- Add can use config to control start calculation holding cost after how many months.

9/14/2010 2:08:57 PM Andy
- Fix wrong bank interest capture.

9/17/2010 5:02:45 PM Andy
- Fix report show blank name for selected sku monitoring group.

9/27/2010 11:51:28 AM Andy
- Fix "mysql server has gone away" error message.

10/20/2010 10:20:37 AM Andy
- Add always try to insert related privilege when access this page.

11/16/2010 10:51:21 AM Andy
- Change report to use cache data.
- Add feature to let user choose whether use report server or not.

11/26/2010 11:07:21 AM Andy
- Separate items by table to reduce browser load speed. (can adjust by config)

12/27/2010 6:07:46 PM Andy
- Add few extra column "Proposal", some of it are editable and will be directly save once user finish edit.
- Those column and edited data will also show in export excel.
- Add checkbox to let user only show got proposal data SKU.

1/12/2011 6:08:07 PM Andy
- change column lastlogin to use lastlogin from table user_status.

6/13/2011 9:58:32 AM Andy
- Add "Actual Profit Group Amount".
- Change "Average Per Unit (Amt)" cost and selling formula.
- Remove "Profit & Sales Weighted average".

6/24/2011 6:32:10 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:40:43 PM Andy
- Change split() to use explode()

8/1/2011 5:37:03 PM Andy
- Add config to sku monitoring report.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

5/23/2014 2:05 PM Kuan Yeh
- amend output filename "sku_monioring_"

2/21/2020 5:58 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

*/
include("include/common.php");
set_time_limit(0);
$maintenance->check(23);
// aneka v654717
$con->sql_query("insert into privilege values('MST_SKU_MORN_GRP','Allow to access SKU Monitoring Group Master File.',1,0)",false,false);
$con->sql_query("insert into privilege values('MST_BANK_INTEREST','Allow to access Bank Interest Master File.',1,0)",false,false);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SKU', BRANCH_CODE), "/index.php");
if (!$config['enable_sku_monitoring2']) js_redirect($LANG['NEED_CONFIG'], "/index.php");

$months = array(1=>'Jan',	2=>'Feb',	3=>'Mar',	4=>'Apr',	5=>'May',	6=>'Jun',	7=>'Jul',	8=>'Aug',	9=>'Sep',	10=>'Oct',	11=>'Nov',	12=>'Dec');
$smarty->assign('months',$months);

class SKU_MONITORING extends Module{
	var $rows_per_table = 30;
	
	function __construct($title, $template=''){
        global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		if($config['sku_monitoring2_items_per_table']>0)	$this->rows_per_table = $config['sku_monitoring2_items_per_table'];
        $smarty->assign('rows_per_table', $this->rows_per_table);
        parent::__construct($title, $template);
	}
	
	function _default(){
		global $con, $smarty, $sessioninfo, $con_multi;
		
		$this->init_load();
		if($_REQUEST['load_report']){
            $this->load_report();
            
            if($_REQUEST['show_type']=='excel'){    // export excel
                include_once("include/excelwriter.php");
		    	$smarty->assign('no_header_footer', true);
		    	$filename = "sku_monitoring_".time().".xls";
		    	log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export SKU Monitoring To Excel($filename)");
		    	Header('Content-Type: application/msexcel');
	  			Header('Content-Disposition: attachment;filename='.$filename);

	  			print ExcelWriter::GetHeader();
	  			$this->display();
	  			print ExcelWriter::GetFooter();
	  		    exit;
			}
		}    
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'call_display', 'memory'=>memory_get_usage())));
		//if($con_multi)	$con_multi->sql_close();    // terminate connection with report server
		$this->display();
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'done_display', 'memory'=>memory_get_usage())));
	}
	
	private function init_load(){
        global $con, $smarty, $sessioninfo, $con_multi;

		// load branches list
		$con_multi->sql_query("select * from branch where active=1 order by sequence,code");
		while($r = $con_multi->sql_fetchrow()){
			$branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches', $branches);
		
		// load department list
		$con_multi->sql_query("select * from category where active=1 and level=2 and id in ($sessioninfo[department_ids]) order by description");
		while($r = $con_multi->sql_fetchrow()){
			$dept[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('dept', $dept);
		
		// load user list
		$con_multi->sql_query("select * from user where active=1 and id in (select distinct user_id from sku_monitoring_group) order by u");
		while($r = $con_multi->sql_fetchrow()){
			$user_list[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('user_list', $user_list);
		
		// load sku group
		$this->load_sku_monitoring_group(true);
		
	}
	
	function load_sku_monitoring_group($sqlonly = false){
        global $con, $smarty, $sessioninfo,$con_multi;
        
        $dept_id = mi($_REQUEST['dept_id']);
        $user_id = mi($_REQUEST['user_id']);
        
        if($dept_id)    $filter[] = "smg.dept_id=$dept_id";
        if($user_id)    $filter[] = "smg.user_id=$user_id";
        if($sessioninfo['level']<1100){
            $filter[] = "smg.allowed_user like ".ms('%:"'.$sessioninfo['id'].'";%');
		}  
        if($filter)	$filter = "where ".join(" and ", $filter);
        else    $filter ='';
        
        $sql = "select smg.*,c.description as dept_name,user.u as username
		from sku_monitoring_group smg
		left join category c on c.id=smg.dept_id
		left join user on user.id=smg.user_id
		$filter order by smg.dept_id,smg.user_id";

        $con_multi->sql_query($sql);
        
        while($r = $con_multi->sql_fetchassoc()){ // get group header
			$sku_m_group[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		if($sku_m_group){   // get batch
			foreach($sku_m_group as $group_id=>$r){
				if(!$group_id)  unset($sku_m_group[$group_id]);
				$group_id = mi($group_id);
				
				$con_multi->sql_query("select sku_monitoring_group_id, year, month, date, count(*) as sku_count from sku_monitoring_group_batch_items
				where sku_monitoring_group_id=$group_id
				group by sku_monitoring_group_id, year,month order by sku_monitoring_group_id,year,month");
				while($r2 = $con_multi->sql_fetchrow()){
					$sku_m_group[$group_id]['batch'][] = $r2;
				}
				$con_multi->sql_freeresult();
			}
		}
		//print_r($sku_m_group);
        $smarty->assign('sku_m_group', $sku_m_group);
        if(!$sqlonly)   $this->display("report.sku_monitoring2.sku_monitoring_group.tpl");
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $con_multi, $config;

		if(BRANCH_CODE=='HQ')	$branch_id_arr = $_REQUEST['branch_id'];    // only HQ can see all branches
		else    $branch_id_arr = array($sessioninfo['branch_id']);
		if(!$branch_id_arr) $err[] = "No branch Selected";
		
		list($sku_monitoring_group_id, $y, $m) = explode(",", $_REQUEST['sku_monitoring_group_id']);
		$this->date_from = $_REQUEST['date_from'];
		$this->branch_count = count($branch_id_arr);
		$this->only_show_proposal_sku = mi($_REQUEST['only_show_proposal_sku']);
		
		if(!$this->date_from||!$y||!$m) $err[] = "Invalid start monitoring date.";
		$this->date_from_key = date("Ym", strtotime($this->date_from));
		
		if(!$err){
            // create sku item id list
			$q_sid = $con_multi->sql_query("select si.*
			from sku_monitoring_group_batch_items smgbi
			left join sku_items si on si.id=smgbi.sku_item_id
			where sku_monitoring_group_id=".mi($sku_monitoring_group_id)." and smgbi.date=".ms($this->date_from));
			while($r = $con_multi->sql_fetchrow($q_sid)){
			    if(!$r['id'])   continue;
			    $sid = mi($r['id']);
			    
			    // get proposal data
				$q_proposal = $con_multi->sql_query("select * from sku_monitoring_group_batch_items_proposal smgbip where smgbip.sku_monitoring_group_id=$sku_monitoring_group_id and smgbip.year=$y and smgbip.month=$m and smgbip.sku_item_id=$sid");
				while($r2 = $con_multi->sql_fetchassoc($q_proposal)){
				    if($r2['p1_sales_qty'])
					$this->proposal_data[$r2['branch_id']][$r2['sku_item_id']]['p1_sales_qty'] = $r2['p1_sales_qty'];

					if($r2['p1_selling_price'])
					$this->proposal_data[$r2['branch_id']][$r2['sku_item_id']]['p1_selling_price'] = $r2['p1_selling_price'];

	                if($r2['p2_selling_price'])
					$this->proposal_data[$r2['branch_id']][$r2['sku_item_id']]['p2_selling_price'] = $r2['p2_selling_price'];
				}
				$con_multi->sql_freeresult($q_proposal);

				// only show those item got entered proposal data
				if($this->only_show_proposal_sku){
				    $skip_this = true;
	                foreach($branch_id_arr as $bid){    // loop branch array to get data from each branch
						if($this->proposal_data[$bid][$sid]){
	                        $skip_this = false;
	                        break;
						}
					}
					if($skip_this){
						continue;
					}
				}
			
				$sid_list[] = mi($r['id']);
				$this->sku_info[$r['id']] = $r;
			}
			$con_multi->sql_freeresult($q_sid);
			if(!$sid_list)  $err[] = "No Item in group.";
		}
		
		if($err){   // got error
			$smarty->assign('err', $err);
			return;
		}
		
		$con_multi->sql_query("select * from sku_monitoring_group where id=".mi($sku_monitoring_group_id));
		$sku_group_info = $con_multi->sql_fetchrow();
		$con_multi->sql_freeresult();
		
		$m2 = $m+1;
		$y2 = $y;
		if($m2>12){
			$m2 = 1;
			$y2++;
		}
		$this->repeat_date_from = $y2.'-'.$m2.'-1';
		/*if($_REQUEST['use_report_server'])	$con_multi= new mysql_multi();
		else	$con_multi = $con;*/
		
		foreach($sid_list as $sid){ // loop for each sku to get the next grn date, mark it as repeat
			// get grn
            $sql = "select distinct year(grr.rcv_date) as y, month(grr.rcv_date) as m
from grn_items gi
left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join po on po.po_no=gri.doc_no and gri.type='PO'
where grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and (po.po_no is null or po.is_ibt=0) and gi.sku_item_id=$sid and grr.rcv_date>=".ms($this->repeat_date_from)." order by y,m limit 1";
			$con_multi->sql_query($sql);
			$temp = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			if(!$temp)  $max_date_to = date('Y-m-d');
			else{
				$m2 = mi($temp['m'])-1;
				$y2 = mi($temp['y']);
				if($m2<1){
					$m2 = 12;
				    $y2--;
				}
				$max_date_to = $y2.'-'.$m2.'-'.days_of_month($m2, $y2);
			}
			$this->sku_info[$sid]['max_report_date'] = $max_date_to;
			$this->sku_info[$sid]['max_report_date_key'] = date("Ym", strtotime($max_date_to));
			if(!$this->date_to||strtotime($max_date_to)>strtotime($this->date_to))  $this->date_to = $max_date_to;
		}

		// load report data
		/*$con->sql_query("create table if not exists memory_monitor (
			id int primary key auto_increment,
			type char(20),
			memory int
		)");*/
		//$con->sql_query("truncate memory_monitor");
		
		
		foreach($branch_id_arr as $bid){    // loop branch array to get data from each branch
			$this->get_branch_data($bid, $sid_list);
		}
		//if($_REQUEST['use_report_server'])	$con_multi->close_connection();
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'done_get_data', 'memory'=>memory_get_usage())));
		// generate date label
		$max_y = mi(date("Y", strtotime($this->date_to)));
		$max_m = mi(date("m", strtotime($this->date_to)));
		$cur_y = mi(date("Y", strtotime($this->date_from)));
		$cur_m = mi(date("m", strtotime($this->date_from)));
		$mth_no = 0;
		$mth_count = 0;
		while($cur_y<=$max_y){
			if($cur_y==$max_y){
				while($cur_m<=$max_m){
				    $date_key = sprintf("%04d%02d", $cur_y, $cur_m);
					if($mth_count>=$config['sku_monitoring2_start_calc_interest_after_month'])	$mth_no++;
					$mth_count++;
					$this->date_label[$date_key] = array("y"=>$cur_y, "m"=>$cur_m, "mth_no"=>$mth_no);
					$cur_m++;
				}
				break;
			}
			$date_key = sprintf("%04d%02d", $cur_y, $cur_m);
			if($mth_count>=$config['sku_monitoring2_start_calc_interest_after_month'])	$mth_no++;
			$mth_count++;
			$this->date_label[$date_key] = array("y"=>$cur_y, "m"=>$cur_m, "mth_no"=>$mth_no);
			$cur_m++;
			
			if($cur_m>12){
                $cur_m = 1;
                $cur_y++;
			}
		}
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'b4_total_row', 'memory'=>memory_get_usage())));
		if($this->data){
			$this->generate_total_row_data();
		}else   unset($this->data); // no data
		
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'after_generate_total', 'memory'=>memory_get_usage())));
		//print_r($this->sku_info);
		//print_r($this->total);
		//print_r($this->data);
		//print_r($this->date_label);
		$branches = $smarty->get_template_vars('branches');
		$temp = 'Branch: ';
		$c = 0;
		foreach($branch_id_arr as $bid){
			$temp .= $branches[$bid]['code'];
			$c++;
			if($c<$this->branch_count)    $temp .= ', ';
		}
		$report_title[] = $temp;
		$report_title[] = "SKU Monitoring Group: ".$sku_group_info['group_name'];
		$report_title[] = "Date: ".$this->date_from." to ".$this->date_to;
		
		if($this->date_label){
            krsort($this->date_label);
            // get bank interest
            $this->get_bank_interest();
            //print_r($this->date_label);
		}
        //$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'after_sort_data', 'memory'=>memory_get_usage())));
		$smarty->assign('date_label', $this->date_label);
		$smarty->assign('sku_info', $this->sku_info);
		if($sessioninfo['u']=='wsatp'){
			//print_r($this->data);
		}
		$smarty->assign('data', $this->data);
		$smarty->assign('proposal_data', $this->proposal_data);
		$branch_id_arr[] = 'ibt';
		$smarty->assign('branch_id_arr', $branch_id_arr);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;',$report_title));
		
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'done_load_report', 'memory'=>memory_get_usage())));
	}
	
	private function get_branch_data($bid, $sid_list){
        global $con, $smarty, $sessioninfo, $con_multi;
        $date_from = date("Y-m-d", strtotime($this->date_from));

		foreach($sid_list as $sid){ // loop items to get details
		    $date_to = $this->sku_info[$sid]['max_report_date'];    // get date to
		    
		    // get branch last cost & selling
		    $con_multi->sql_query("select si.id,sic.grn_cost,ifnull(sip.price,si.selling_price) as price
from sku_items si
left join sku_items_cost sic on sic.branch_id=$bid and sic.sku_item_id=si.id
left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
where si.id=$sid");
			$branch_last = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
   			$this->data[$bid][$sid]['branch_last']['cost'] = $branch_last['grn_cost'];
            $this->data[$bid][$sid]['branch_last']['selling'] = $branch_last['price'];
            
            $report_cache_tbl = "sku_monitoring_2_report_cache_b".$bid;
            $ibt_cache_tbl = 'sku_monitoring_2_ibt_cache';
            $q_cache = $con_multi->sql_query("select * from $report_cache_tbl where sku_item_id=$sid and date between ".ms($date_from)." and ".ms($date_to));
            $cache_data = $con_multi->sql_fetchrowset($q_cache);
            $con_multi->sql_freeresult($q_cache);
            
            if($cache_data){
                foreach($cache_data as $r){
					$date_key = sprintf("%04d%02d", $r['year'], $r['month']);

					// GRN
					if($r['grn_qty'])	$this->data[$bid][$sid]['grn'][$date_key]['qty'] += mf($r['grn_qty']);
	                if($r['grn_total_cost'])	$this->data[$bid][$sid]['grn'][$date_key]['total_cost'] += mf($r['grn_total_cost']);

	                // opening
	                if($r['opening_qty'])	$this->data[$bid][$sid]['opening'][$date_key]['qty'] += mf($r['opening_qty']);
	            	if($r['opening_total_cost'])	$this->data[$bid][$sid]['opening'][$date_key]['total_cost'] += mf($r['opening_total_cost']);

	            	// stock check adj
	            	if($r['stock_check_adj_qty'])	$this->data[$bid][$sid]['stock_check_adj'][$date_key]['qty'] += mf($r['stock_check_adj_qty']);

	            	// POS
	            	if($r['pos_qty'])	$this->data[$bid][$sid]['pos'][$date_key]['qty'] += mf($r['pos_qty']);
	                if($r['pos_total_cost'])	$this->data[$bid][$sid]['pos'][$date_key]['cost'] += mf($r['pos_total_cost']);
	                if($r['pos_total_amt'])	$this->data[$bid][$sid]['pos'][$date_key]['amt'] += mf($r['pos_total_amt']);

	                // GRA
	                if($r['gra_qty'])	$this->data[$bid][$sid]['gra'][$date_key]['qty'] += mf($r['gra_qty']);

	                // ADJ
	                if($r['adj_qty'])	$this->data[$bid][$sid]['adj'][$date_key]['qty'] += mf($r['adj_qty']);

	                // DO
	                if($r['do_qty'])	$this->data[$bid][$sid]['do'][$date_key]['qty'] += mf($r['do_qty']);

	                // ibt adj
	                if($r['ibt_adj_qty'])	$this->data[$bid][$sid]['ibt_adj'][$date_key]['qty'] += mf($r['ibt_adj_qty']);

	                // variances
					if($r['variances_disc_qty'])	$this->data[$bid][$sid]['variances'][$date_key]['disc_qty'] += mf($r['variances_disc_qty']);
					if($r['variances_disc_amt'])	$this->data[$bid][$sid]['variances'][$date_key]['disc_amt'] += mf($r['variances_disc_amt']);
					if($r['variances_markup_qty'])	$this->data[$bid][$sid]['variances'][$date_key]['markup_qty'] += mf($r['variances_markup_qty']);
					if($r['variances_markup_amt'])	$this->data[$bid][$sid]['variances'][$date_key]['markup_amt'] += mf($r['variances_markup_amt']);
					if($r['variances_markdown_qty'])	$this->data[$bid][$sid]['variances'][$date_key]['markdown_qty'] += mf($r['variances_markdown_qty']);
					if($r['variances_markdown_amt'])	$this->data[$bid][$sid]['variances'][$date_key]['markdown_amt'] += mf($r['variances_markdown_amt']);

					//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'sku_monitoring2', 'memory'=>memory_get_usage())));
				}
			}
			unset($cache_data);
			
			// ibt
			$q_ibt_cache = $con_multi->sql_query("select * from $ibt_cache_tbl where sku_item_id=$sid and date between ".ms($date_from)." and ".ms($date_to)." and by_branch_id=$bid");
			$ibt_cache_data = $con_multi->sql_fetchrowset($q_ibt_cache);
			$con_multi->sql_freeresult($q_ibt_cache);
			if($ibt_cache_data){
                foreach($ibt_cache_data as $r){
				    $date_key = sprintf("%04d%02d", $r['year'], $r['month']);
				    $this->data['ibt'][$sid]['grn'][$date_key]['qty'] += mf($r['grn_qty']);
	                $this->data['ibt'][$sid]['grn'][$date_key]['total_cost'] += mf($r['grn_total_cost']);
				}
			}
			
			
			unset($ibt_cache_data);
			
			// get GRN data and ibt adj
			/*$sql = "select grr.rcv_date,po.is_ibt,
            sum(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*uom.fraction + gi.pcs, gi.acc_ctn*uom.fraction+gi.acc_pcs)) as qty,
            sum(
			  if(gi.acc_cost is null, gi.cost, gi.acc_cost)
			  *
			  if (gi.acc_ctn is null and gi.acc_pcs is null,
			  	gi.ctn + gi.pcs / uom.fraction,
			  	gi.acc_ctn + gi.acc_pcs / uom.fraction
			  )
			) as total_cost
from grn_items gi
left join grn on grn.id=gi.grn_id and grn.branch_id=gi.branch_id
left join grr_items gri on gri.id=grn.grr_item_id and gri.branch_id=grn.branch_id
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join uom on uom.id=gi.uom_id
left join po on po.po_no=gri.doc_no and gri.type='PO'
where grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and gi.branch_id=$bid and gi.sku_item_id=$sid and grr.rcv_date between ".ms($date_from)."  and ".ms($date_to)." group by grr.rcv_date,po.is_ibt";
			//print $sql."<br>";
            $con_multi->sql_query($sql);
            while($r = $con_multi->sql_fetchrow()){
                $date_key = date("Ym", strtotime($r['rcv_date']));
                if($r['is_ibt']){
                    $this->data['ibt'][$sid]['grn'][$date_key]['qty'] += mf($r['qty'])*-1;
                	$this->data['ibt'][$sid]['grn'][$date_key]['total_cost'] += mf($r['total_cost'])*-1;
				}
                $this->data[$bid][$sid]['grn'][$date_key]['qty'] += mf($r['qty']);
                $this->data[$bid][$sid]['grn'][$date_key]['total_cost'] += mf($r['total_cost']);
			}
            $con_multi->sql_freeresult();
			
			// find opening stock balance
			$sb_tbl = "stock_balance_b".$bid."_".date("Y", strtotime($date_from));
			$sql = "select if(from_date=".ms($date_from).",start_qty,qty) as qty, cost
			from $sb_tbl tbl
			where sku_item_id=$sid and ".ms($date_from)." between from_date and to_date limit 1";
			//if($sessioninfo['u']=='wsatp')  print $sql;
			$con_multi->sql_query($sql);
			$date_key = date("Ym", strtotime($date_from));
			$tmp = $con_multi->sql_fetchrow();
			if($tmp){
			    $total_cost = $tmp['qty']*$tmp['cost'];
                $this->data[$bid][$sid]['opening'][$date_key]['qty'] += mf($tmp['qty']);
            	$this->data[$bid][$sid]['opening'][$date_key]['total_cost'] += mf($total_cost);
            	unset($tmp);
			}
            
            $con_multi->sql_freeresult();

			// get stock check adjustment
			$sql = "select date, sum(sc.qty) as qty from
stock_check sc
left join sku_items si using(sku_item_code)
where si.id=$sid and sc.branch_id=$bid and sc.date between ".ms($date_from)." and ".ms($date_to)." group by date";
            $q_sc = $con_multi->sql_query($sql);
            while($r = $con_multi->sql_fetchrow($q_sc)){
                $date_key = date("Ym", strtotime($r['date']));
				$sb_date = date("Y-m-d", strtotime("-1 day", strtotime($r['date'])));
				$sb_year = date("Y", strtotime($sb_date));
				// find stock balance to get the adjustment figure
				$q_sb = $con_multi->sql_query("select qty from stock_balance_b".$bid."_".$sb_year." where sku_item_id=$sid and ".ms($sb_date)." between from_date and to_date limit 1");
				$sb_qty = $con_multi->sql_fetchfield(0);
				$con_multi->sql_freeresult($q_sb);
				// stock check - stock balance = stock check adjustment
				$this->data[$bid][$sid]['stock_check_adj'][$date_key]['qty'] += mf($r['qty']-$sb_qty);
			}
			$con_multi->sql_freeresult($q_sc);
			
			// find POS
			$sql = "select date,sum(amount) as amt, sum(cost) as cost, sum(qty) as qty from sku_items_sales_cache_b$bid
where sku_item_id=$sid and date between ".ms($date_from)." and ".ms($date_to)." group by date";
			$con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
                $date_key = date("Ym", strtotime($r['date']));
                $this->data[$bid][$sid]['pos'][$date_key]['qty'] += mf($r['qty']);
                $this->data[$bid][$sid]['pos'][$date_key]['cost'] += mf($r['cost']);
                $this->data[$bid][$sid]['pos'][$date_key]['amt'] += mf($r['amt']);
			}
			$con_multi->sql_freeresult();
			
			// GRA
			$sql = "select year(return_timestamp) as y, month(return_timestamp) as m,sum(qty) as qty
from gra_items gi
left join gra on gi.gra_id=gra.id and gi.branch_id=gra.branch_id
where gi.sku_item_id=$sid and gi.branch_id=$bid and gra.status=0 and gra.returned=1 and return_timestamp between ".ms($date_from)." and ".ms($date_to." 23:59:59")." group by y,m";
            $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
                //$date_key = date("Ym", strtotime($r['date']));
                $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
                $this->data[$bid][$sid]['gra'][$date_key]['qty'] += mf($r['qty']);
			}
			$con_multi->sql_freeresult();
			
			// ADJ
			$sql = "select year(adj.adjustment_date) as y, month(adj.adjustment_date) as m, sum(qty) as qty
from adjustment_items ai
left join adjustment adj on adj.id=ai.adjustment_id and adj.branch_id=ai.branch_id
where ai.branch_id=$bid and ai.sku_item_id=$sid and adj.active=1 and adj.approved=1 and adj.status=1 and adj.adjustment_date between ".ms($date_from)." and ".ms($date_to)." group by y,m";
            $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
                //$date_key = date("Ym", strtotime($r['date']));
                $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
                $this->data[$bid][$sid]['adj'][$date_key]['qty'] += mf($r['qty']);
			}
			$con_multi->sql_freeresult();
			
			// DO
			$sql = "select year(do_date) as y, month(do_date) as m, sum(di.ctn *uom.fraction+di.pcs) as qty
from do_items di
left join do on do.id=di.do_id and do.branch_id=di.branch_id
left join uom on di.uom_id=uom.id
where di.sku_item_id=$sid and di.branch_id=$bid and do.approved=1 and do.active=1 and do.checkout=1 and do.status=1 and do.do_date between ".ms($date_from)." and ".ms($date_to)." group by y,m";
            $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
                //$date_key = date("Ym", strtotime($r['date']));
                $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
                $this->data[$bid][$sid]['do'][$date_key]['qty'] += mf($r['qty']);
			}
			$con_multi->sql_freeresult();
			
			// IBT Adj
			$sql = "select year(po.po_date) as y, month(po.po_date) as m, sum(if(gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn*grn_uom.fraction + gi.pcs, gi.acc_ctn*grn_uom.fraction+gi.acc_pcs)) as grn_qty,
sum(pi.qty*po_uom.fraction+pi.qty_loose) as po_qty
from po
left join po_items pi on pi.po_id=po.id and pi.branch_id=po.branch_id
left join grr_items gri on gri.type='PO' and gri.doc_no=po.po_no
left join grr on grr.id=gri.grr_id and grr.branch_id=gri.branch_id
left join grn on grn.grr_item_id=gri.id and grn.branch_id=gri.branch_id
left join grn_items gi on gi.branch_id=grn.branch_id and gi.grn_id=grn.id and  gi.sku_item_id=pi.sku_item_id
left join uom grn_uom on grn_uom.id=gi.uom_id
left join uom po_uom on po_uom.id=pi.order_uom_id
where po.branch_id=$bid and po.active=1 and po.approved=1 and po.po_date between ".ms($date_from)." and ".ms($date_to)." and grr.active=1 and grn.active=1 and grn.status=1 and grn.approved=1 and pi.sku_item_id=$sid and gi.sku_item_id=$sid group by y,m having po_qty-grn_qty<>0";
            $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchrow()){
                //$date_key = date("Ym", strtotime($r['date']));
                $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
                $this->data[$bid][$sid]['ibt_adj'][$date_key]['qty'] += mf($r['po_qty']-$r['grn_qty']);
			}
			$con_multi->sql_freeresult();
			
			// variances
			$sql = "select year(pi.date) as y,month(pi.date) as m,pi.sku_item_id,pi.qty,pi.price,pi.discount
from pos_items pi
left join pos on pos.branch_id=pi.branch_id and pos.id=pi.pos_id and pos.counter_id=pi.counter_id and pos.date=pi.date
where pos.branch_id=$bid and pos.cancel_status=0 and pi.sku_item_id=$sid and pos.date between ".ms($date_from)." and ".ms($date_to)." and pi.qty>0";

            $con_multi->sql_query($sql);
            $hq_selling = round(mf($this->sku_info[$sid]['selling_price']),2);   // get HQ selling price to compare whether mark up or down
			while($r = $con_multi->sql_fetchrow()){
                //$date_key = date("Ym", strtotime($r['date']));
                $date_key = sprintf("%04d%02d", $r['y'], $r['m']);
                if($r['discount']){ // got discount
                    $this->data[$bid][$sid]['variances'][$date_key]['disc_qty'] += mf($r['qty']);
                    $this->data[$bid][$sid]['variances'][$date_key]['disc_amt'] += mf($r['price']-$r['discount']);
				}else{
					$single_pcs_selling_price = round(mf(($r['price']-$r['discount'])/$r['qty']),2);
					if($single_pcs_selling_price>$hq_selling){
                        $this->data[$bid][$sid]['variances'][$date_key]['markup_qty'] += mf($r['qty']);
                    	$this->data[$bid][$sid]['variances'][$date_key]['markup_amt'] += mf($r['price']-$r['discount'])-($hq_selling*$r['qty']);
					}elseif($single_pcs_selling_price<$hq_selling){
                        $this->data[$bid][$sid]['variances'][$date_key]['markdown_qty'] += mf($r['qty']);
                    	$this->data[$bid][$sid]['variances'][$date_key]['markdown_amt'] += ($hq_selling*$r['qty'])-mf($r['price']-$r['discount']);
					}
				}
                
			}
			$con_multi->sql_freeresult();*/
		}
		
		// make a query to server in order to prevent sql timeout by server.
		$con->sql_query("update user_status set lastlogin=now() where user_id=".$sessioninfo['id']);
	}
	
	private function generate_total_row_data(){
		global $con, $smarty, $sessioninfo;
        if($sessioninfo['u']=='wsatp'){
			//print_r($this->data);
		}
		if(!$this->data)    return;
		if(!$this->date_label)  return;

		// pre-calculate for opening, total in, total out, adjustment and closing (due to displaying month by desc)
		foreach($this->data as $bid=>$b_data){  // loop for each branch
			foreach($b_data as $sid=>$data){    // loop for each sku
			    $date_to = $this->sku_info[$sid]['max_report_date'];
			    $date_to_key = date("Ym", strtotime($date_to));
			    
			    $last_mth_closing = 0;
			    foreach($this->date_label as $date_key=>$d){    // loop for each month
			        if($date_key>$date_to_key)    continue;

					if($date_key==$this->date_from_key){  // is opening month
						$this->data[$bid][$sid]['opening']['total']['qty'] = $data['opening'][$date_key]['qty'];
						$this->data[$bid][$sid]['opening']['total']['total_cost'] = $data['opening'][$date_key]['total_cost'];
					}else{  // opening get from last month closing
                        //$this->data[$bid][$sid]['opening'][$date_key]['qty'] = $last_mth_closing;
                        //$this->data[$bid][$sid]['opening'][$date_key]['total_cost'] = $last_mth_closing_cost;
					}
						
					// monthly total in
					$this->data[$bid][$sid]['total_in'][$date_key]['qty'] = $this->data[$bid][$sid]['opening'][$date_key]['qty']+$data['grn'][$date_key]['qty'];
					$this->data[$bid][$sid]['total_in'][$date_key]['total_cost'] = $this->data[$bid][$sid]['opening'][$date_key]['total_cost']+$data['grn'][$date_key]['total_cost'];
					
					// find pcs cost
					$this_mth_qty = $this->data[$bid][$sid]['total_in'][$date_key]['qty'];
					$this_mth_total_cost = $this->data[$bid][$sid]['total_in'][$date_key]['total_cost'];
					$this_mth_pcs_cost = $this_mth_qty ? ($this_mth_total_cost/$this_mth_qty) : 0;
                    
                    // monthly total out
                    $this->data[$bid][$sid]['total_out'][$date_key]['qty'] = $data['pos'][$date_key]['qty']+$data['do'][$date_key]['qty']+$data['gra'][$date_key]['qty'];
                    // monthly total adj
                    $this->data[$bid][$sid]['total_adj'][$date_key]['qty'] = $data['adj'][$date_key]['qty']+$data['stock_check_adj'][$date_key]['qty'];
                    
                    // monthly closing qty
                    $this->data[$bid][$sid]['closing_qty'][$date_key]['qty'] = $this->data[$bid][$sid]['total_in'][$date_key]['qty']-$this->data[$bid][$sid]['total_out'][$date_key]['qty']+$this->data[$bid][$sid]['total_adj'][$date_key]['qty'];
                    $this->data[$bid][$sid]['closing_qty'][$date_key]['total_cost'] = $this->data[$bid][$sid]['closing_qty'][$date_key]['qty']*$this_mth_pcs_cost;
                    
                    $last_mth_closing = $this->data[$bid][$sid]['closing_qty'][$date_key]['qty'];
                    $last_mth_closing_cost = $this->data[$bid][$sid]['closing_qty'][$date_key]['total_cost'];
                    
                    // monthly total in
                    $this->total[$sid]['opening'][$date_key]['qty'] += $this->data[$bid][$sid]['opening'][$date_key]['qty'];
					$this->total[$sid]['opening'][$date_key]['total_cost'] += $this->data[$bid][$sid]['opening'][$date_key]['total_cost'];
				}
			}
		}
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'total_opening', 'memory'=>memory_get_usage())));
		if($sessioninfo['u']=='wsatp'){
			//print_r($this->data);
		}

		foreach($this->data as $bid=>$b_data){  // loop for each branch
			foreach($b_data as $sid=>$data){    // loop for each sku
			    $this->total[$sid]['branch_last']['cost'] += $data['branch_last']['cost'];
				$this->total[$sid]['branch_last']['selling'] += $data['branch_last']['selling'];
				
				if($data['grn']){
					foreach($data['grn'] as $date_key=>$r){
                        $this->total[$sid]['grn'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['grn'][$date_key]['total_cost'] += $r['total_cost'];
                        
                        $this->total[$sid]['grn']['total']['qty'] += $r['qty'];
						$this->total[$sid]['grn']['total']['total_cost'] += $r['total_cost'];
						
						$this->total['total']['grn'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['grn'][$date_key]['total_cost'] += $r['total_cost'];

                        $this->total['total']['grn']['total']['qty'] += $r['qty'];
						$this->total['total']['grn']['total']['total_cost'] += $r['total_cost'];

						$this->data[$bid][$sid]['grn']['total']['qty'] += $r['qty'];
						$this->data[$bid][$sid]['grn']['total']['total_cost'] += $r['total_cost'];
					}
				}
				if($data['opening']){
					$this->total[$sid]['opening']['total']['qty'] += $data['opening'][$this->date_from_key]['qty'];
					$this->total[$sid]['opening']['total']['total_cost'] += $data['opening'][$this->date_from_key]['total_cost'];
				}
				if($data['stock_check_adj']){
                    foreach($data['stock_check_adj'] as $date_key=>$r){
                        $this->total[$sid]['stock_check_adj'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['stock_check_adj']['total']['qty'] += $r['qty'];
                        
                        $this->total['total']['stock_check_adj'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['stock_check_adj']['total']['qty'] += $r['qty'];
                        
                        $this->data[$bid][$sid]['stock_check_adj']['total']['qty'] += $r['qty'];
					}
				}
				if($data['adj']){
                    foreach($data['adj'] as $date_key=>$r){
                        $this->total[$sid]['adj'][$date_key]['qty'] += $r['qty'];
						$this->total[$sid]['adj']['total']['qty'] += $r['qty'];
						
						$this->total['total']['adj'][$date_key]['qty'] += $r['qty'];
						$this->total['total']['adj']['total']['qty'] += $r['qty'];
						
						$this->data[$bid][$sid]['adj']['total']['qty'] += $r['qty'];
					}
				}
				
				if($data['pos']){
                    foreach($data['pos'] as $date_key=>$r){
                        $this->total[$sid]['pos'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['pos'][$date_key]['cost'] += $r['cost'];
                        $this->total[$sid]['pos'][$date_key]['amt'] += $r['amt'];
                        $this->total[$sid]['pos']['total']['qty'] += $r['qty'];
                        $this->total[$sid]['pos']['total']['cost'] += $r['cost'];
                        $this->total[$sid]['pos']['total']['amt'] += $r['amt'];
                        
                        $this->total['total']['pos'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['pos'][$date_key]['cost'] += $r['cost'];
                        $this->total['total']['pos'][$date_key]['amt'] += $r['amt'];
                        $this->total['total']['pos']['total']['qty'] += $r['qty'];
                        $this->total['total']['pos']['total']['cost'] += $r['cost'];
                        $this->total['total']['pos']['total']['amt'] += $r['amt'];
                        
                        $this->data[$bid][$sid]['pos']['total']['qty'] += $r['qty'];
                        $this->data[$bid][$sid]['pos']['total']['cost'] += $r['cost'];
                        $this->data[$bid][$sid]['pos']['total']['amt'] += $r['amt'];
					}
				}
				if($data['gra']){
                    foreach($data['gra'] as $date_key=>$r){
                        $this->total[$sid]['gra'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['gra']['total']['qty'] += $r['qty'];
                        
                        $this->total['total']['gra'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['gra']['total']['qty'] += $r['qty'];
                        
                        $this->data[$bid][$sid]['gra']['total']['qty'] += $r['qty'];
					}
				}
				if($data['do']){
                    foreach($data['do'] as $date_key=>$r){
                        $this->total[$sid]['do'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['do']['total']['qty'] += $r['qty'];
                        
                        $this->total['total']['do'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['do']['total']['qty'] += $r['qty'];
                        
                        $this->data[$bid][$sid]['do']['total']['qty'] += $r['qty'];
					}
				}
				if($data['ibt_adj']){
                    foreach($data['ibt_adj'] as $date_key=>$r){
                        $this->total[$sid]['ibt_adj'][$date_key]['qty'] += $r['qty'];
                        $this->total[$sid]['ibt_adj']['total']['qty'] += $r['qty'];
                        
                        $this->total['total']['ibt_adj'][$date_key]['qty'] += $r['qty'];
                        $this->total['total']['ibt_adj']['total']['qty'] += $r['qty'];
                        
                        $this->data[$bid][$sid]['ibt_adj']['total']['qty'] += $r['qty'];
					}
				}
				if($data['variances']){
				    foreach($data['variances'] as $date_key=>$r){
				        $this->total[$sid]['variances'][$date_key]['disc_qty'] += $r['disc_qty'];
	                    $this->total[$sid]['variances'][$date_key]['disc_amt'] += $r['disc_amt'];
	                    $this->total[$sid]['variances'][$date_key]['markup_qty'] += $r['markup_qty'];
	                    $this->total[$sid]['variances'][$date_key]['markup_amt'] += $r['markup_amt'];
	                    $this->total[$sid]['variances'][$date_key]['markdown_qty'] += $r['markdown_qty'];
	                    $this->total[$sid]['variances'][$date_key]['markdown_amt'] += $r['markdown_amt'];
	                    $this->total[$sid]['variances']['total']['disc_qty'] += $r['disc_qty'];
	                    $this->total[$sid]['variances']['total']['disc_amt'] += $r['disc_amt'];
	                    $this->total[$sid]['variances']['total']['markup_qty'] += $r['markup_qty'];
	                    $this->total[$sid]['variances']['total']['markup_amt'] += $r['markup_amt'];
	                    $this->total[$sid]['variances']['total']['markdown_qty'] += $r['markdown_qty'];
	                    $this->total[$sid]['variances']['total']['markdown_amt'] += $r['markdown_amt'];
	                    
	                    $this->total['total']['variances'][$date_key]['disc_qty'] += $r['disc_qty'];
	                    $this->total['total']['variances'][$date_key]['disc_amt'] += $r['disc_amt'];
	                    $this->total['total']['variances'][$date_key]['markup_qty'] += $r['markup_qty'];
	                    $this->total['total']['variances'][$date_key]['markup_amt'] += $r['markup_amt'];
	                    $this->total['total']['variances'][$date_key]['markdown_qty'] += $r['markdown_qty'];
	                    $this->total['total']['variances'][$date_key]['markdown_amt'] += $r['markdown_amt'];
	                    $this->total['total']['variances']['total']['disc_qty'] += $r['disc_qty'];
	                    $this->total['total']['variances']['total']['disc_amt'] += $r['disc_amt'];
	                    $this->total['total']['variances']['total']['markup_qty'] += $r['markup_qty'];
	                    $this->total['total']['variances']['total']['markup_amt'] += $r['markup_amt'];
	                    $this->total['total']['variances']['total']['markdown_qty'] += $r['markdown_qty'];
	                    $this->total['total']['variances']['total']['markdown_amt'] += $r['markdown_amt'];
	                    
	                    $this->data[$bid][$sid]['variances']['total']['disc_qty'] += $r['disc_qty'];
	                    $this->data[$bid][$sid]['variances']['total']['disc_amt'] += $r['disc_amt'];
	                    $this->data[$bid][$sid]['variances']['total']['markup_qty'] += $r['markup_qty'];
	                    $this->data[$bid][$sid]['variances']['total']['markup_amt'] += $r['markup_amt'];
	                    $this->data[$bid][$sid]['variances']['total']['markdown_qty'] += $r['markdown_qty'];
	                    $this->data[$bid][$sid]['variances']['total']['markdown_amt'] += $r['markdown_amt'];
	                    
				    }
				}
			}
		}
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'after_total_data', 'memory'=>memory_get_usage())));

		//print_r($this->total);
		if($this->total){
			foreach($this->total as $sid=>$r){
			    // get average cost and selling
                $this->total[$sid]['branch_last']['cost'] = $this->total[$sid]['branch_last']['cost']/$this->branch_count;
				$this->total[$sid]['branch_last']['selling'] = $this->total[$sid]['branch_last']['selling']/$this->branch_count;
				
				$this->total['total']['opening']['total']['qty'] += $r['opening'][$this->date_from_key]['qty'];
				$this->total['total']['opening']['total']['total_cost'] += $r['opening'][$this->date_from_key]['total_cost'];
				
				foreach($this->date_label as $date_key=>$d){
					$this->total['total']['opening'][$date_key]['qty'] += $r['opening'][$date_key]['qty'];
					$this->total['total']['opening'][$date_key]['total_cost'] += $r['opening'][$date_key]['total_cost'];
				}
			}
		}
		$smarty->assign('total', $this->total);
		//$con->sql_query("insert into memory_monitor ".mysql_insert_by_field(array('type'=>'after_total', 'memory'=>memory_get_usage())));
	}
	
	function ajax_load_batch_items_details(){
		global $con, $smarty, $con_multi;
		
		$smg_value = $_REQUEST['smg_value'];
		if(!$smg_value) die('Invalid Group');
		
		list($smg_id, $y, $m) = explode(",", $smg_value);

		$sql = "select smgbi.sku_item_id, si.sku_item_code,si.artno,si.description from
		sku_monitoring_group_batch_items smgbi
		left join sku_items si on si.id=smgbi.sku_item_id
		where smgbi.sku_monitoring_group_id=".mi($smg_id)." and year=".mi($y)." and month=".mi($m);
		$con_multi->sql_query($sql);
		$smarty->assign('item_list', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		$this->display('report.sku_monitoring2.batch_items.tpl');
	}
	
	private function get_bank_interest(){
		global $con, $smarty, $con_multi;
		
		$end_mth = reset($this->date_label);
		$start_mth = end($this->date_label);
		
		$from_date = $start_mth['y'].'-'.$start_mth['m'].'-1';
		$to_date = $end_mth['y'].'-'.$end_mth['m'].'-1';
		
		$con_multi->sql_query("select * from bank_interest where date <=".ms($to_date));
		while($r = $con_multi->sql_fetchassoc()){
		    $key = date("Ym", strtotime($r['date']));
			$this->bank_interest[$key] = $r;
		}
		$con_multi->sql_freeresult();
		
		if($this->bank_interest){
            foreach($this->date_label as $date_key=>$r){
				$this->date_label[$date_key]['interest_rate'] = $this->get_bank_interest_rate($date_key);
			}
		}
	}
	
	private function get_bank_interest_rate($date_key){
	    $selected = false;
	    $rate = 0;
	    
		foreach($this->bank_interest as $key=>$r){
			if($key<=$date_key) $rate = $r['interest_rate'];
			if($key>$date_key)  return $rate;
		}
		return $rate;
	}
	
	function ajax_update_proposal_data(){
		global $con, $con_multi;

		$allowed_colname = array('p1_sales_qty', 'p1_selling_price', 'p2_selling_price');

		$colname = trim($_REQUEST['colname']);
		$bid = mi($_REQUEST['bid']);
		$v = mf($_REQUEST['v']);
		$sid = mi($_REQUEST['sid']);
		$sku_monitoring_group_id = mi($_REQUEST['sku_monitoring_group_id']);
		$year = mi($_REQUEST['year']);
		$month = mi($_REQUEST['month']);

		if(!in_array($colname, $allowed_colname))  die('Invalid field name.');
		if(!$sku_monitoring_group_id || !$year || !$month)  die('Invalid batch group.');
		if(!$bid)   die('Invalid branch id');
		if(!$sid)   die('invalid sku item');

		$filter = "where sku_monitoring_group_id=$sku_monitoring_group_id and year=$year and month=$month and branch_id=$bid and sku_item_id=$sid";

		// select the row first
		$con_multi->sql_query("select * from sku_monitoring_group_batch_items_proposal $filter");
		$r = $con_multi->sql_fetchassoc();
		$con_multi->sql_freeresult();

		if($r){ // already got row
			$r[$colname] = $v;

			// check need delete or not
			$del_counter = 0;
			foreach($allowed_colname as $col){
				if(!$r[$col]) $del_counter++;
			}
			if($del_counter>=count($allowed_colname)){
				// need delete
				$con->sql_query("delete from sku_monitoring_group_batch_items_proposal $filter");
			}else{
				// update
				$con->sql_query("update sku_monitoring_group_batch_items_proposal set $colname=".ms($v)." $filter");
			}
		}else{  // new row
			if($v){ // got value
				$upd[$colname] = $v;
				$upd['sku_monitoring_group_id'] = $sku_monitoring_group_id;
				$upd['year'] = $year;
				$upd['month'] = $month;
				$upd['branch_id'] = $bid;
				$upd['sku_item_id'] = $sid;

				$con->sql_query("replace into sku_monitoring_group_batch_items_proposal ".mysql_insert_by_field($upd));
			}
		}

		print "OK";
	}
}

$SKU_MONITORING = new SKU_MONITORING("SKU Monitoring 2");
?>
