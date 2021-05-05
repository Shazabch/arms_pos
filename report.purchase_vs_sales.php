<?php
/*
1/14/2011 6:53:20 PM Justin
- Added report can expand by either Date or Vendor.

1/24/2011 5:29:54 PM Alex
- change use report_server

6/24/2011 6:26:28 PM Andy
- Make all branch default sort by sequence, code.

6/27/2011 4:24:21 PM Justin
- Modified the sum up of GRN with/without PO amount and count to base on sku item's department instead of GRN department.
- Applied switch calculations for GRN with/without PO Amount when it is/isn't GRN Future.
- Modified some of the fields that use the missing function "ms" and "mi".

7/6/2011 2:35:27 PM Andy
- Change split() to use explode()

11/1/2012 4:04 PM Justin
- Enhanced Use GRN filter to use new method to filter.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

5/9/2013 4:58 PM Fithri
- vendor sort by A-Z

08/02/2013 11:56 AM Justin
- Bug fixed on GRN count.

6/18/2014 9:53 AM Fithri
- report privilege & config checking is set to be the same as in menu (menu.tpl)

1/6/2015 1:58 PM Justin
- Bug fixed on GRN with/without PO count have been calculate wrongly.

11/16/2016  6:01 PM Andy
- Fixed total sales not tally when show expand details.

11/16/2017 11:08 AM Justin
- Enhanced to have "IBT GRN" column.
- Bug fixed on the GRN count will calculated wrongly if GRN having 2 department items.

4/30/2018 3:22 PM Andy
- Added Foreign Currency feature.

8/26/2019 2:27 PM Andy
- Fixed GRN Count sum incorrectly when filter by all branch.

2/20/2020 5:33 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/

include("include/common.php");

include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
//show vendor option
if ($sessioninfo['vendors']) $vd = "and id in (".join(",",array_keys($sessioninfo['vendors'])).")";
$q1 = $con_multi->sql_query("select id, description from vendor where active $vd order by description");
$smarty->assign("vendor1", $con_multi->sql_fetchrowset($q1));
$con_multi->sql_freeresult($q1);

//show owner option
$q1 = $con_multi->sql_query("select distinct(user.id) as id, user.u from po left join user on user_id = user.id group by id");
$smarty->assign("user", $con_multi->sql_fetchrowset($q1));
$con_multi->sql_freeresult($q1);

class PURCHASE_VS_SALES_REPORT extends Report{
	private function run(){
        global $smarty, $sessioninfo, $con_multi, $config;
		$bid = $this->bid;        
        $date_from = $this->date_from;
		$date_to = $this->date_to;
		$vendor = $this->vendor_id;
		$use_grn = $this->use_grn;
		$owner = $this->owner_id;
		
		if(BRANCH_CODE == 'HQ'){
			if($bid == ''){
				$bid = get_all_branch();
			}
		}else{
			if($bid == ''){
				$bid = get_request_branch();
			}
		}
		
		$msql_b[] = "grn.status = 1 and grn.active = 1 and grr.active = 1 and grr.rcv_date between ".ms($date_from)." and ".ms($date_to)." and grn.branch_id in ($bid) ";
		$dsql_b[] = "po.active = 1 and po.po_date between ".ms($date_from)." and ".ms($date_to)." and ((po.branch_id=1 and po.po_branch_id in ($bid)) or (po.po_branch_id=0 and po.branch_id in ($bid)))";
		
		if($vendor){
			$msql_b[] = "grn.vendor_id = $vendor";
			$dsql_b[] = "po.vendor_id = $vendor";
			$ssql_b = "and s.vendor_id = $vendor";
		}
		
		if($vendor && $use_grn){
			$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=sisc.sku_item_id and sisc.date between vsh.from_date and vsh.to_date and vsh.vendor_id=".mi($vendor);

			$ssql_b = "and vsh.vendor_id=".mi($vendor);
		}
		
		if($owner){
			$dsql_b[] = "po.user_id = $owner";
		}
		
		if ($sessioninfo['level']<9999){
			$msql_b[] = "grn.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
			$dsql_b[] = "po.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}

		$msql_b = join(' and ', $msql_b);
		$dsql_b = join(' and ', $dsql_b);

		$existed_grn = array();
		if(!$config['use_grn_future']){
			// grr amount
			$sql = "select grr.rcv_date, c.description, c.id,
					sum(if(grr_items.type = 'PO', (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_wpo_amt, 
					count(distinct if(grr_items.type = 'PO', grn.id, null)) grn_wpo_count, 
					sum(if(grr_items.type != 'PO', (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_wopo_amt, 
					count(distinct if(grr_items.type != 'PO', grn.id, null)) grn_wopo_count,
					sum(if(grn.is_ibt = 1, (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_ibt_amt, 
					count(if(grn.is_ibt = 1, grn.id, null)) grn_ibt_count
					from grn
					left join grn_items gi on gi.grn_id = grn.id and grn.branch_id=gi.branch_id
					left join uom  on gi.uom_id=uom.id
					left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id=grr_items.branch_id 
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join sku_items si on si.id = gi.sku_item_id
				    left join sku s on s.id = si.sku_id
				    left join category_cache cc using(category_id) 
				    left join category c on c.id = cc.p2
					where $msql_b
					group by c.id
					order by c.description";

			$q_grn = $con_multi->sql_query($sql);
			
			// setup and to be display on the templates
			while($r = $con_multi->sql_fetchassoc($q_grn)){
				$key = $r['id'];
				$table[$key]['id'] = $r['id'];
				$table[$key]['description'] = $r['description'];		
				$table[$key]['grn_wpo_amt'] += $r['grn_wpo_amt'];
				$table[$key]['grn_wpo_count'] += $r['grn_wpo_count'];
				$table[$key]['grn_wopo_amt'] += $r['grn_wopo_amt'];
				$table[$key]['grn_wopo_count'] += $r['grn_wopo_count'];
				$table[$key]['grn_ibt_amt'] += $r['grn_ibt_amt'];
				$table[$key]['grn_ibt_count'] += $r['grn_ibt_count'];
			}
		}else{
			$sql = "select grr.rcv_date, c.description, c.id, grn.grr_id, grn.branch_id, grn.id as grn_id,
					((if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost))*if(grr.currency_rate<0,1,grr.currency_rate)) as final_amount, grn.is_ibt
					from grn 
					left join grn_items gi on gi.grn_id = grn.id and grn.branch_id=gi.branch_id
					left join uom  on gi.uom_id=uom.id
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join sku_items si on si.id = gi.sku_item_id
				    left join sku s on s.id = si.sku_id
				    left join category_cache cc using(category_id) 
				    left join category c on c.id = cc.p2
					where $msql_b
					order by c.description";

			$q_grn = $con_multi->sql_query($sql);
		
			while($r = $con_multi->sql_fetchassoc($q_grn)){
				$sql2 = $con_multi->sql_query("select type, case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc from grr_items where grr_id = ".mi($r['grr_id'])." and branch_id = ".mi($r['branch_id'])." group by type_asc order by type_asc ASC limit 1");

				$grr_info = $con_multi->sql_fetchassoc($sql2);
				$con_multi->sql_freeresult($sql2);
				$grn_key = $r['branch_id'].'_'.$r['grn_id'];

				$key = $r['id'];
				$table[$key]['id'] = $r['id'];
				$table[$key]['description'] = $r['description'];
				if(!$r['is_ibt']){
					if($grr_info['type'] == "PO"){
						$table[$key]['grn_wpo_amt'] += $r['final_amount'];
						if(!$existed_grn[$key][$grn_key]){
							$table[$key]['grn_wpo_count'] += 1;
						}
					}else{
						$table[$key]['grn_wopo_amt'] += $r['final_amount'];
						if(!$existed_grn[$key][$grn_key]){
							$table[$key]['grn_wopo_count'] += 1;
						}
					}
				}else{
					$table[$key]['grn_ibt_amt'] += $r['final_amount'];
					if(!$existed_grn[$key][$grn_key]){
						$table[$key]['grn_ibt_count'] += 1;
					}
				}
				$existed_grn[$key][$grn_key] = true;
			}
		}
		$con_multi->sql_freeresult($q_grn);
		unset($existed_grn);
		
		$is_po_sql = "select c.id, c.description, sum(po.po_amount) as po_amt,
					  count(po.id) as po_count,
					  sum(if(po.delivered = 1, po.po_amount, 0)) as drv_po_amt,
					  count(if(po.delivered = 1, po.id, null)) as drv_po_count,
					  sum(if(po.delivered = 0, po.po_amount, 0)) as undrv_po_amt,
					  count(if(po.delivered = 0, po.id, null)) as undrv_po_count,
					  if(po.currency_rate<0,1,po.currency_rate) as currency_rate
					  from `po`
					  join `category` c on c.id = po.department_id
					  where $dsql_b
					  group by c.id, po.currency_rate
					  order by c.description";

		$q_ispo = $con_multi->sql_query($is_po_sql);
			
		while($r = $con_multi->sql_fetchassoc($q_ispo)){
			$key = $r['id'];
			$table[$key]['id'] = $r['id'];
			$table[$key]['description'] = $r['description'];
			
			$table[$key]['po_amt'] += $r['po_amt']*$r['currency_rate'];
			$table[$key]['po_count'] += $r['po_count'];
			$table[$key]['drv_po_amt'] += $r['drv_po_amt']*$r['currency_rate'];
			$table[$key]['drv_po_count'] += $r['drv_po_count'];
			$table[$key]['undrv_po_amt'] += $r['undrv_po_amt']*$r['currency_rate'];
			$table[$key]['undrv_po_count'] += $r['undrv_po_count'];
		}
		$con_multi->sql_freeresult($q_ispo);

		// get those PO that already expired based on po expired date
		$is_npo_sql = "select po.cancel_date, c.id, c.description, po.deliver_to,
					   (po.po_amount*if(po.currency_rate<0,1,po.currency_rate)) as po_amt,
					   if(po.delivered = 1,1,0) as delivered
					   from `po`
					   join `category` c on c.id = po.department_id
					   where $dsql_b
					   having delivered = 0
					   order by c.description";

		$q_isnpo = $con_multi->sql_query($is_npo_sql);

		while($is_npo = $con_multi->sql_fetchassoc($q_isnpo)){
			$key = $is_npo['id'];
			$table[$key]['id'] = $is_npo['id'];
			$table[$key]['description'] = $is_npo['description'];

			// check if deliver date is able to unserialize
			if($is_npo['deliver_to'] && unserialize($is_npo['deliver_to'])){
			    $expired_date = unserialize($is_npo['cancel_date']);
			    $is_npo['cancel_date'] = '';
				
				//loop until the last expired date
				$deliver_to = unserialize($is_npo['deliver_to']);
				foreach ($deliver_to as $v=>$k){
		    		$is_npo['expired_date'].= $expired_date[$k];
		    		list($day,$month,$year) = explode("/", $expired_date[$k]);
				}
				
				// set the day and month to become two digit in case found one digit only
				$day = str_pad($day, 2, "0", STR_PAD_LEFT);
		    	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	    		if(($year.$month.$day) < date(Ymd) && $is_npo['delivered'] == 0){
					$table[$key]['exp_po_amt'] += $is_npo['po_amt'];
					$table[$key]['exp_po_count'] += 1;
				}
			}

			if($is_npo['cancel_date']){
				// split the expired date due to some dates cannot be using date() function
				list($day,$month,$year) = explode("/", $is_npo['cancel_date']);
				$day = str_pad($day, 2, "0", STR_PAD_LEFT);
			   	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
				if(($year.$month.$day) <= date(Ymd) && $is_npo['delivered'] == 0){
					$table[$key]['exp_po_amt'] += $is_npo['po_amt'];
					$table[$key]['exp_po_count'] += 1;
				}
			}
		}
		$con_multi->sql_freeresult($q_isnpo);
	
		// split the grouped branch ID if can be splitted
		if(explode(",", $bid)){
			$sptd_bid = explode(",", $bid);
		}else{
			$sptd_bid = $bid;
		}

		// loop the branch IDs
		for($j=0; $j<count($sptd_bid); $j++){
			// sales cache
			$sales_sql = "select c.id, c.description, sisc.date as date, 
						  sum(sisc.amount) as selling
						  from `sku_items_sales_cache_b$sptd_bid[$j]` sisc 
						  join `sku_items` si on si.id = sisc.sku_item_id
						  join `sku` s on s.id = si.sku_id
						  join `category_cache` cc using(category_id) 
						  join `category` c on c.id = cc.p2
						  $use_grn_xtra_join
						  where sisc.date >= ".ms($date_from)." and sisc.date <= ".ms($date_to)."
						  $sku_item_id_list
						  $ssql_b
						  group by cc.p2
						  order by c.description";
			//echo $sales_sql."<br />";
			$q_cs = $con_multi->sql_query($sales_sql);

			while($s = $con_multi->sql_fetchassoc($q_cs)){
				$key = $s['id'];
				$table[$key]['id'] = $s['id'];
				$table[$key]['description'] = $s['description'];
				$table[$key]['sales_amt'] += $s['selling'];
			}
			$con_multi->sql_freeresult($q_cs);
		}
		
		// sort the record printing based on description by using customized function
		if($table){
			usort($table, array($this,"sort_desc"));
		}
		$this->table = $table;
		
		if($sessioninfo['id'] == 1){
			//print_r($this->table);
		}
	}
	
	private function sort_desc($a,$b){
		if (($a['description']==$b['description'])) return 0;
	    else return ($a['description']>$b['description']) ? 1:-1;
	}
	
    function generate_report(){
		global $con, $smarty;

		$this->run();
		
		$smarty->assign('table', $this->table);
	}
	
	function process_form(){
	    global $con, $smarty, $con_multi;

        $this->bid = $_REQUEST['branch_id'];
        $this->date_from = $_REQUEST['date_from'];
        if($_REQUEST['view_type'] == 'date'){
        	$end_date =date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
        }else{
			$end_date =date("Y-m-d",strtotime("+1 year -1 day",strtotime($this->date_from)));
		}
		if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
        if(strtotime($_REQUEST['date_from'])>strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date("Y-m-d",strtotime("-1 day",strtotime("+1 month",strtotime($this->date_from))));
		}
        $this->date_to = $_REQUEST['date_to'];
        $this->view_type = $_REQUEST['view_type'];
		$this->vendor_id = $_REQUEST['vendor_id'];
		$this->use_grn = $_REQUEST['use_grn'];
		$this->owner_id = $_REQUEST['owner_id'];
		
        $report_title[] = "Date: ".$_REQUEST['date_from']." to ".$_REQUEST['date_to'];
       	$con_multi->sql_query("select code from branch where id=".mi($this->bid));
       	$branch_code = ($_REQUEST['branch_id']) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
       	$report_title[] = "Branch: ".$branch_code;

       	$con_multi->sql_query("select description from vendor where id=".mi($_REQUEST['vendor_id']));
       	$vendor_code = ($_REQUEST['vendor_id']) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
       	$report_title[] = "Vendor: ".$vendor_code;		

		$use_grn = ($_REQUEST['use_grn']) ? "Yes" : "No";
		$report_title[] = "Use GRN: ".$use_grn;

       	$con_multi->sql_query("select u from user where id=".mi($_REQUEST['owner_id']));
       	$owner_code = ($_REQUEST['owner_id']) ? $con_multi->sql_fetchfield(0) : "All";
		$con_multi->sql_freeresult();
       	$report_title[] = "PO Owner: ".$owner_code;		

		$smarty->assign('date_from', $this->date_from);
		$smarty->assign('date_to', $this->date_to);
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));

		// set both 2 dates to store into the hidden field on template
		if (isset($_REQUEST['output_excel'])){
			$smarty->assign("print_excel", '1');
		}
		
		parent::process_form();
	}
	
	function ajax_show_date_details(){
		global $con_multi, $smarty, $sessioninfo, $config;
		
		$bid = $_REQUEST['branch_id'];
        $cid = $_REQUEST['dept_id'];
		$date_from = $_REQUEST['date_from'];
		$date_to = $_REQUEST['date_to'];
		$view_type = $_REQUEST['view_type'];
		$vendor = $_REQUEST['vendor_id'];
		$use_grn = $_REQUEST['use_grn'];
		$owner = $_REQUEST['owner_id'];

		if($view_type == 'date'){
			$grr_select_group = "rcv_date";
			$grr_order_by = "rcv_date";
			$po_select_group = "po_date";
			$po_order_by = "po_date";
			$sales_select_group = "date";
			$sales_order_by = "date";
		}else{
			$grr_select_group = "vendor_id";
			$grr_order_by = "vendor_desc";
			$po_select_group = "vendor_id";
			$po_order_by = "vendor_desc";
			$sales_select_group = "vendor.id";
			$sales_order_by = "vendor_desc";
		}

		if(BRANCH_CODE == 'HQ'){
			if($bid == ''){
				$bid = get_all_branch();
			}
		}else{
			if($bid == ''){
				$bid = get_request_branch();
			}
		}
		
		$msql_b[] = "grn.status = 1 and grn.active = 1 and grr.active = 1 and grr.rcv_date between ".ms($date_from)." and ".ms($date_to)." and grr.branch_id in ($bid)";
		$dsql_b[] = "po.active = 1 and po.po_date between ".ms($date_from)." and ".ms($date_to)." and ((po.branch_id=1 and po.po_branch_id in ($bid)) or (po.po_branch_id=0 and po.branch_id in ($bid)))";
		
		if($vendor){
			$msql_b[] = "grn.vendor_id = ".mi($vendor);
			$dsql_b[] = "po.vendor_id = ".mi($vendor);
			$ssql_b = "and s.vendor_id = ".mi($vendor);
		}
		
		if($use_grn){
			// find items that we receive by
			/*$sql = "select distinct(vsh.sku_item_id) as sku_item_id
					from vendor_sku_history vsh 
					join sku_items si on si.id = vsh.sku_item_id
					join sku s on s.id = si.sku_id
					join `category_cache` cc using(category_id) 
					join `category` c on c.id = cc.p2
					where vsh.added >= ".ms($date_from)." and vsh.added <= ".ms($date_to)." and c.id = ".mi($cid)."
					$ssql_b
					order by si.id";

			$sku_item_list = $con_multi->sql_query($sql);

			$sku_id_list = array();
			while($r = $con_multi->sql_fetchrow($sku_item_list)){
				$sku_id_list[] = $r['sku_item_id'];
			}*/
			$use_grn_xtra_join = "join vendor_sku_history_b".$bid." vsh on vsh.sku_item_id=sisc.sku_item_id and sisc.date between vsh.from_date and vsh.to_date and vsh.vendor_id=".mi($vendor);

			$ssql_b = "and vsh.vendor_id=".mi($vendor);
			$join_vendor ="left join vendor on vsh.vendor_id = vendor.id";
		}else{
			$join_vendor ="left join vendor on s.vendor_id = vendor.id";
		}

		if(count($sku_id_list)>0){
			$sku_item_id_list = "and si.id in (".join(",", $sku_id_list).")";
		}

		if($owner){
			$dsql_b[] = "po.user_id = $owner";
		}
		
		if ($sessioninfo['level']<9999){
			$msql_b[] = "grr.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")"; 
			$dsql_b[] = "po.department_id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
		}

		$msql_b = join(' and ', $msql_b);
		$dsql_b = join(' and ', $dsql_b);

		if(!$config['use_grn_future']){
			// grr amount
			$sql = "select grr.$grr_select_group, vendor.description as vendor_desc,
					sum(if(grr_items.type = 'PO' and grn.is_ibt != 1, (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_wpo_amt, 
					count(if(grr_items.type = 'PO' and grn.is_ibt != 1, grn.id, null)) grn_wpo_count, 
					sum(if(grr_items.type != 'PO' and grn.is_ibt != 1, (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_wopo_amt, 
					count(if(grr_items.type != 'PO' and grn.is_ibt != 1, grn.id, null)) grn_wopo_count,
					sum(if(grn.is_ibt = 1, (if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost)), 0)) AS grn_ibt_amt, 
					count(if(grn.is_ibt = 1, grn.id, null)) grn_ibt_count
					from grn
					left join grn_items gi on gi.grn_id = grn.id and grn.branch_id=gi.branch_id
					left join uom  on gi.uom_id=uom.id
					left join grr_items on grn.grr_item_id = grr_items.id and grn.branch_id=grr_items.branch_id 
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join sku_items si on si.id = gi.sku_item_id
				    left join sku s on s.id = si.sku_id
				    left join category_cache cc using(category_id) 
				    left join category c on c.id = cc.p2
					left join vendor on grr.vendor_id = vendor.id
					where c.id = ".mi($cid)." and $msql_b
					group by grr.$grr_select_group
					order by $grr_order_by";

			$q_grn = $con_multi->sql_query($sql);

			while($r = $con_multi->sql_fetchassoc($q_grn)){
				$key = $r[$grr_select_group];
				if(!trim($r[$grr_order_by])) $r[$grr_order_by] = "Untitled";
				$table[$key]['key_id'] = $key;
				$table[$key]['description'] = $r[$grr_order_by];
				$table[$key]['grn_wpo_amt'] += $r['grn_wpo_amt'];
				$table[$key]['grn_wpo_count'] += $r['grn_wpo_count'];
				$table[$key]['grn_wopo_amt'] += $r['grn_wopo_amt'];
				$table[$key]['grn_wopo_count'] += $r['grn_wopo_count'];
				$table[$key]['grn_ibt_amt'] += $r['grn_ibt_amt'];
				$table[$key]['grn_ibt_count'] += $r['grn_ibt_count'];
			}
		}else{
			$existed_grn = array();
			$sql = "select grr.$grr_select_group, vendor.description as vendor_desc,
					grn.grr_id, grn.branch_id, grn.id as grn_id,
					((if (gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn  + (gi.pcs / uom.fraction)), (gi.acc_ctn + (gi.acc_pcs / uom.fraction))) * if (gi.acc_cost is null, gi.cost, gi.acc_cost))*if(grr.currency_rate<0,1,grr.currency_rate)) as final_amount, grn.is_ibt
					from grn 
					left join grn_items gi on gi.grn_id = grn.id and grn.branch_id=gi.branch_id
					left join uom  on gi.uom_id=uom.id
					left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
					left join sku_items si on si.id = gi.sku_item_id
				    left join sku s on s.id = si.sku_id
				    left join category_cache cc using(category_id) 
				    left join category c on c.id = cc.p2
					left join vendor on grr.vendor_id = vendor.id
					where c.id = ".mi($cid)." and $msql_b
					order by $grr_order_by";

			$q_grn = $con_multi->sql_query($sql);
		
			while($r = $con_multi->sql_fetchassoc($q_grn)){
				$sql2 = $con_multi->sql_query("select type, case when type = 'PO' then 1 when type = 'INVOICE' then 2 when type = 'DO' then 3 else 4 end as type_asc from grr_items where grr_id = ".mi($r['grr_id'])." and branch_id = ".mi($r['branch_id'])." group by type_asc order by type_asc ASC limit 1");

				$grr_info = $con_multi->sql_fetchassoc($sql2);
				$con_multi->sql_freeresult($sql2);
				$grn_key = $r['branch_id'].'_'.$r['grn_id'];
				
				$key = $r[$grr_select_group];
				if(!trim($r[$grr_order_by])) $r[$grr_order_by] = "Untitled";
				$table[$key]['id'] = $key;
				$table[$key]['description'] = $r[$grr_order_by];
				
				if(!$r['is_ibt']){
					if($grr_info['type'] == "PO"){
						$table[$key]['grn_wpo_amt'] += $r['final_amount'];
						if(!$existed_grn[$grn_key]){
							$table[$key]['grn_wpo_count'] += 1;
						}
					}else{
						$table[$key]['grn_wopo_amt'] += $r['final_amount'];
						if(!$existed_grn[$grn_key]){
							$table[$key]['grn_wopo_count'] += 1;
						}
					}
				}else{
					$table[$key]['grn_ibt_amt'] += $r['final_amount'];
					if(!$existed_grn[$grn_key]){
						$table[$key]['grn_ibt_count'] += 1;
					}
				}
				
				$existed_grn[$grn_key] = true;
			}
		}
		$con_multi->sql_freeresult($q_grn);

		// setup and to be display on the templates
		$is_po_sql = "select po.$po_select_group, po.po_date, sum(po.po_amount) as po_amt,
					  count(po.id) as po_count, vendor.description as vendor_desc,
					  sum(if(po.delivered = 1, po.po_amount, 0)) as drv_po_amt,
					  count(if(po.delivered = 1, po.id, null)) as drv_po_count,
					  sum(if(po.delivered = 0, po.po_amount, 0)) as undrv_po_amt,
					  count(if(po.delivered = 0, po.id, null)) as undrv_po_count,
					  if(po.currency_rate<0,1,po.currency_rate) as currency_rate
					  from `po`
					  left join vendor on po.vendor_id = vendor.id
					  join `category` c on c.id = po.department_id
					  where c.id = ".mi($cid)." and $dsql_b
					  group by po.$po_select_group, po.currency_rate
					  order by $po_order_by";
		$q_ispo = $con_multi->sql_query($is_po_sql);
		
		while($r = $con_multi->sql_fetchassoc($q_ispo)){
			$key = $r[$po_select_group];
			if(!trim($r[$po_order_by])) $r[$po_order_by] = "Untitled";
			$table[$key]['key_id'] = $key;
			$table[$key]['description'] = $r[$po_order_by];
			
			$table[$key]['po_amt'] += $r['po_amt']*$r['currency_rate'];
			$table[$key]['po_count'] += $r['po_count'];
			$table[$key]['drv_po_amt'] += $r['drv_po_amt']*$r['currency_rate'];
			$table[$key]['drv_po_count'] += $r['drv_po_count'];
			$table[$key]['undrv_po_amt'] += $r['undrv_po_amt']*$r['currency_rate'];
			$table[$key]['undrv_po_count'] += $r['undrv_po_count'];
		}
		$con_multi->sql_freeresult($q_ispo);
		
		// get those PO that already expired based on po expired date
		$is_npo_sql = "select po.$po_select_group, po.id, po.cancel_date, po.deliver_to,
					   po.po_amount as po_amt, vendor.description as vendor_desc,
					   if(po.approved = 1 and po.delivered = 1,1,0) as delivered
					   from `po`
					   left join vendor on po.vendor_id = vendor.id
					   join `category` c on c.id = po.department_id
					   where c.id = ".mi($cid)." and $dsql_b
					   having delivered = 0";

		$q_isnpo = $con_multi->sql_query($is_npo_sql);

		while($is_npo = $con_multi->sql_fetchassoc($q_isnpo)){
			$key = $is_npo[$po_select_group];
			
			
			// check if deliver date is able to unserialize
			if(unserialize($is_npo['deliver_to'])){
			    $expired_date = unserialize($is_npo['cancel_date']);
			    $is_npo['cancel_date'] = '';
				
				//loop until the last expired date
				$deliver_to = unserialize($is_npo['deliver_to']);
				foreach ($deliver_to as $v=>$k){
		    		$is_npo['expired_date'].= $expired_date[$k];
		    		list($day,$month,$year) = explode("/", $expired_date[$k]);
				}
				
				// set the day and month to become two digit in case found one digit only
				$day = str_pad($day, 2, "0", STR_PAD_LEFT);
		    	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	    		if(($year.$month.$day) < date(Ymd) && $is_npo['delivered'] == 0){
	    			if(!trim($is_npo[$po_order_by])) $is_npo[$po_order_by] = "Untitled";
	    			$table[$key]['key_id'] = $key;
					$table[$key]['description'] = $is_npo[$po_order_by];
					$table[$key]['exp_po_amt'] += $is_npo['po_amt'];
					$table[$key]['exp_po_count'] += 1;
				}
			}
			if($is_npo['cancel_date']){
				// split the expired date due to some dates cannot be using date() function
				list($day,$month,$year) = explode("/", $is_npo['cancel_date']);
				$day = str_pad($day, 2, "0", STR_PAD_LEFT);
			   	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
				if(($year.$month.$day) <= date(Ymd) && $is_npo['delivered'] == 0){
					if(!trim($is_npo[$po_order_by])) $is_npo[$po_order_by] = "Untitled";
					$table[$key]['key_id'] = $key;
					$table[$key]['description'] = $is_npo[$po_order_by];
					$table[$key]['exp_po_amt'] += $is_npo['po_amt'];
					$table[$key]['exp_po_count'] += 1;
				}
			}
		}
		$con_multi->sql_freeresult($q_isnpo);
		
		// split the grouped branch ID if can be splitted
		if(explode(",", $bid)){
			$sptd_bid = explode(",", $bid);
		}else{
			$sptd_bid = $bid;
		}
		
		// loop the branch IDs
		for($j=0; $j<count($sptd_bid); $j++){
			// sales cache
			$sales_sql = "select c.id, c.description, $sales_select_group as key_id, sisc.date,
						  sum(sisc.amount) as selling, vendor.description as vendor_desc
						  from `sku_items_sales_cache_b$sptd_bid[$j]` sisc 
						  join `sku_items` si on si.id = sisc.sku_item_id
						  join `sku` s on s.id = si.sku_id
						  join `category_cache` cc using(category_id) 
						  join `category` c on c.id = cc.p2
						  $use_grn_xtra_join
						  $join_vendor
						  where sisc.date >= ".ms($date_from)." and sisc.date <= ".ms($date_to)." and c.id = ".mi($cid)."
						  $ssql_b
						  $sku_item_id_list
						  group by $sales_select_group
						  order by $sales_order_by";
			if($sessioninfo['id'] == 1){
				//print $sales_sql;
			}
			$q_cs = $con_multi->sql_query($sales_sql);

			while($s = $con_multi->sql_fetchassoc($q_cs)){
				$key = $s['key_id'];
				if(!trim($s[$sales_order_by])) $s[$sales_order_by] = "Untitled";
				$table[$key]['key_id'] = $key;
				$table[$key]['description'] = $s[$sales_order_by];
				$table[$key]['sales_amt'] += $s['selling'];
			}
		}
		$con_multi->sql_freeresult($q_cs);
		
		// loop the days that between date from and to
		if($view_type == 'date'){
			$days = (strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24);	
			for($i=0; $i<=$days; $i++){
				$key = date('Y-m-d', strtotime($date_from)+((60 * 60 * 24) * $i));
				$table[$key]['description'] = $key;
				$table[$key]['ttl_purchase_amt'] += $table[$key]['grn_ibt_amt'] + $table[$key]['grn_wpo_amt'] + $table[$key]['grn_wopo_amt'];
				$table[$key]['ttl_purchase_count'] += $table[$key]['grn_ibt_count'] + $table[$key]['grn_wpo_count'] + $table[$key]['grn_wopo_count'];
				$table[$key]['ttl_var'] += $table[$key]['sales_amt'] - $table[$key]['ttl_purchase_amt'];
				
				if($table[$key]['ttl_purchase_amt'] != 0 && $table[$key]['ttl_var'] != 0){	
					$table[$key]['ttl_perc_var'] += ($table[$key]['ttl_var'] / $table[$key]['ttl_purchase_amt']) * 100;
				}else{
					$table[$key]['ttl_perc_var'] += 0;
				}
			}
			ksort($table);
		}else{
			foreach($table as $key=>$dummy){
				$table[$key]['ttl_purchase_amt'] += $table[$key]['grn_ibt_amt'] + $table[$key]['grn_wpo_amt'] + $table[$key]['grn_wopo_amt'];
				$table[$key]['ttl_purchase_count'] += $table[$key]['grn_ibt_count'] + $table[$key]['grn_wpo_count'] + $table[$key]['grn_wopo_count'];
				$table[$key]['ttl_var'] += $table[$key]['sales_amt'] - $table[$key]['ttl_purchase_amt'];

				if($table[$key]['ttl_purchase_amt'] != 0 && $table[$key]['ttl_var'] != 0){	
					$table[$key]['ttl_perc_var'] += ($table[$key]['ttl_var'] / $table[$key]['ttl_purchase_amt']) * 100;
				}else{
					$table[$key]['ttl_perc_var'] += 0;
				}
			}
			if($table){
				usort($table, array($this,"sort_desc"));
			}
		}
		
		if($sessioninfo['id'] == 1){
			//print_r($table);
		}

		$smarty->assign('dept_id', $cid);
		$smarty->assign('table', $table);
		
		$smarty->display("report.purchase_vs_sales_detail.tpl");
	}
}

	// set get all branches if branch code is from HQ or empty
	function get_all_branch(){
		global $con, $config, $con_multi;
		
		$exc_sql = '';
		if ($config['sales_report_branches_exclude']) {
			$sales_report_branches_exclude = array();
			foreach($config['sales_report_branches_exclude'] as $exc_br) {
				$sales_report_branches_exclude[] = ms($exc_br);
			}
			$exc_sql = 'and code not in (' . join(',',$sales_report_branches_exclude) . ')';
		}

		$get_all_b = "select group_concat(id order by id) as branch_id from branch where active = 1  $exc_sql order by sequence,code";
		// print $get_all_b;
		$all_b = $con_multi->sql_query($get_all_b);
		
		while($branches = $con_multi->sql_fetchassoc($all_b)){
			$bid = $branches['branch_id'];
		}
		$con_multi->sql_freeresult($all_b);
		
		return $bid;
	}
//$con_multi = new mysql_multi();
$PURCHASE_VS_SALES_REPORT = new PURCHASE_VS_SALES_REPORT('Purchase vs Sales Report');
//$con_multi->close_connection();
?>
