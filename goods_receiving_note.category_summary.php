<?php
/*
gary 7/19/2007 4:19:59 PM
- add dropdown/filter sku_type and status.
- call sku_type from table sku_type in DB.

8/7/2007 5:06:38 PM yinsee
- move sel() function to common.php

10/31/2007 10:27:40 AM yinsee
- shorten the SQL to calculate total GRN items qty

3/19/2010 3:04:38 PM Andy
- Fix GRN Summary by Category always retrieve approved GRN bugs, it will not look at the status dropdown.
- Fix GRN Summary by Category if click show all branch, it will only count HQ GRN.
- Add note to let user know how system indicate the department.

10/27/2010 5:14:12 PM Alex
- add show cost privilege
- fix bugs on viewing un-categorized items

3/8/2011 4:59:18 PM Alex
- fix bugs on missing checking status and active

3/18/2011 5:52:47 PM Alex
- fix grn cost amount bugs

6/24/2011 4:08:45 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 11:20:21 AM Andy
- Change split() to use explode()

3/16/2015 1:43 PM Justin
- Enhanced the report to split out PHP and HTML codes.
- Enhanced to have GST information.

4/18/2015 10:11 AM Justin
- Bug fixed on GST amount calculate wrongly while it is not under GST.

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/24/2018 1:52 PM Justin
- Enhanced to show foreign currency.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

class GRN_SUMMARY_BY_CATEGORY extends Module{
	var $branch_id; // use to store user selected branch id
	var $branch_id_list = array(); // use to store all branch need to generate
	
    function __construct($title){
		global $con, $smarty, $sessioninfo, $config;

		$this->init_selection();
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
			if($this->branch_id)   $this->branch_id_list[] = $this->branch_id;
			else{
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
			}
		}else{
            $this->branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
		}
		
		$this->date_from = $_REQUEST['from'];
		$this->date_to = $_REQUEST['to'];
		$this->cat_id = mi($_REQUEST['cat_id']);
		$this->sku_type = $_REQUEST['sku_type'];
		$this->status = $_REQUEST['status'];
        
		parent::__construct($title);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    
	    if($_REQUEST['subm']){
			if(!$_REQUEST['is_itemise_export']){
				$this->generate_report();
				if(isset($_REQUEST['output_excel'])){
					include_once("include/excelwriter.php");
					log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Daily Category Sales from Cash/Credit Sales Report To Excel");

					Header('Content-Type: application/msexcel');
					Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
					print ExcelWriter::GetHeader();
					$smarty->assign('no_header_footer', 1);
				}
			}else{
				$this->export_itemise_info();
			}
		}
		
		
		$this->display();
	}
	
	private function init_selection(){
	    global $con, $smarty;
	    
        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		$q1 = $con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc($q1)){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('branches',$this->branches);

		//added sku type by gary
		$q1 = $con->sql_query("select UPPER(code) as code from sku_type where active=1 order by code");
		$smarty->assign("sku_type", $con->sql_fetchrowset($q1));
		$con->sql_freeresult($q1);
	}
	
	private function generate_report(){
		global $con, $smarty;
		
		$this->generate_category_data(true);
		
		$report_title = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$bcode[] = $this->branches[$bid]['code'];
			}
			$report_title[] = "Branch: ".join(', ', $bcode);
		}
		$report_title[] = "Date: ".$this->date_from." to ".$this->date_to;
		
		if($this->status != ''){
			if($this->status == 1) $status = "Approved";
			else $status = "Not Approved";
		}else $status = "All";
		$report_title[] = "Status: ".$status;

		if($this->sku_type){
			$sku_type = $this->sku_type;
		}else $sku_type = "All";
		$report_title[] = "SKU Type: ".$sku_type;

		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
	}
	
	function generate_category_data($sqlonly = false){
		global $con, $smarty, $sessioninfo,$config;
		
		if(!$this->branch_id_list)  return;
		
		// construct filter
		$filter = array();
		if($this->cat_id){ // got category clicked
			$con->sql_query("select * from category where id=$this->cat_id");
			$cat_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($cat_info['tree_str']!=''){  // generate category tree
			    $tree_str = $cat_info['tree_str'];
				$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
				if($temp){
                    $con->sql_query("select id,description from category where id in $temp order by level");
                    while ($r = $con->sql_fetchassoc()){
                        $cat_info['cat_tree_info'][] = $r;
					}
				}
				
			}
			
			$pf = "p".($cat_info['level']+1);
			$filter[] = "p".$cat_info['level']."=$this->cat_id";
			$uncat_name = $cat_info['description'];
		}else{  // no select category, show all
            $pf = "p1";
			$uncat_name = 'Un-categorized';
		}
		
		$filter[] = "grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = "grr.active=1 and grn.active=1 and grn.status=1";
		
		if($this->branch_id_list) $filter[] = "grn.branch_id in (".join(",", $this->branch_id_list).")";
		
		//added by gary 7/19/2007 4:09:56 PM
		if($this->status!=''){
			$filter[]="grn.approved=".mi($this->status);
		}
		
		//added by gary 7/19/2007 4:19:16 PM
		if($this->sku_type){
			$filter[]="sku.sku_type=".ms($this->sku_type);
		}

		// lock user allowed department
		if ($sessioninfo['level']<9999) $filter[] = "c.p2 in ($sessioninfo[department_ids])";
		if ($filter) $filter = join(' and ', $filter);

		$tb = array();

	    // get all child category info
	    $q1 = $con->sql_query("select c.id,c.description
							   from category c
							   where c.root_id=".mi($this->cat_id)." or c.id=".mi($this->cat_id));
	    while($r=$con->sql_fetchassoc($q1)){
	        $category[$r['id']] = $r['description'];
		}
		$con->sql_freeresult($q1);
	
		$sql = "select $pf as cat_id, grr.rcv_date as dt,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as sell,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
				(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
				(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
				if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost)) as cost,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*if(grn.is_under_gst = 1, grn_items.gst_selling_price, grn_items.selling_price)) as gst_sell,
				grr.currency_code, grr.currency_rate
				from grn_items
				left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
				left join sku_items on grn_items.sku_item_id = sku_items.id
				left join sku on sku_items.sku_id = sku.id
				left join category_cache c on sku.category_id = c.category_id
				where $filter
				group by $pf, dt
				having qty>0
				order by $pf,dt";

		// establish report server connection
		$grn_sql = $con->sql_query($sql);

		if ($con->sql_numrows($grn_sql)>0){
			while($r = $con->sql_fetchassoc($grn_sql)){
			    $date_key = date("Ymd", strtotime($r['dt']));
			    
				$tb[$r['cat_id']]['data'][$r['dt']]['sell'] += $r['sell'];
				if(!$r['currency_rate']) $r['currency_rate'] = 1;
				if($r['currency_code']) $tb[$r['cat_id']]['data'][$r['dt']]['have_fc'] = 1;
				$tb[$r['cat_id']]['data'][$r['dt']]['cost'] += $r['cost'] * $r['currency_rate'];
				$tb[$r['cat_id']]['data'][$r['dt']]['qty'] += $r['qty'];
				$tb[$r['cat_id']]['data'][$r['dt']]['gst_sell'] += $r['gst_sell'];
				
				$temp = array();
				$temp['y'] = date('Y', strtotime($r['dt']));
				$temp['m'] = date('m', strtotime($r['dt']));
				$temp['d'] = date('d', strtotime($r['dt']));

				$uq_cols[$r['dt']] = $temp;
			}
			$con->sql_freeresult($grn_sql);

			foreach (array_keys($tb) as $id){   // loop for each category to assign cat id and description
				$tb[$id]['id'] = $id;
			    if (!$category[$id]){   // unknow category id
			        $tb[$id]['have_subcat'] = false;
		            $tb[$id]['description'] = $uncat_name;
				}else{
					$tb[$id]['have_subcat'] = $this->check_have_subcat($id);
				    $tb[$id]['description'] = $category[$id];
			    }
			}
			ksort($uq_cols);
			reset($uq_cols);
		}
		
		
		// construct total table
		if($tb){
			$is_under_gst = 0;
			foreach($tb as $cat_id=>$cat){
				if($cat['data']){
					foreach($cat['data'] as $date_key=>$r){
					    $tb[$cat_id]['total']['sell'] += $r['sell'];
					    $tb[$cat_id]['total']['cost'] += $r['cost'];
					    $tb[$cat_id]['total']['qty'] += $r['qty'];
					    $tb[$cat_id]['total']['gst_sell'] += $r['gst_sell'];
					    if($r['have_fc']) $tb[$cat_id]['total']['have_fc'] = $r['have_fc'];
					    
                        $tb_total['data'][$date_key]['sell'] += $r['sell'];
				    	$tb_total['data'][$date_key]['cost'] += $r['cost'];
				    	$tb_total['data'][$date_key]['qty'] += $r['qty'];
				    	$tb_total['data'][$date_key]['gst_sell'] += $r['gst_sell'];
					    if($r['have_fc']) $tb_total['data'][$date_key]['have_fc'] = $r['have_fc'];
				    	$tb_total['data']['total']['sell'] += $r['sell'];
				    	$tb_total['data']['total']['cost'] += $r['cost'];
				    	$tb_total['data']['total']['qty'] += $r['qty'];
				    	$tb_total['data']['total']['gst_sell'] += $r['gst_sell'];
				    	if($r['have_fc']) $tb_total['data']['total']['have_fc'] = $r['have_fc'];
						
						if($tb[$cat_id]['total']['gst_sell']) $is_under_gst = 1;
					}
				}
			}
		}
	
	    $root_id = $this->cat_id;
		$root_per = isset($_REQUEST['root_per']) ? mf($_REQUEST['root_per']) : 100;
		
		$smarty->assign('root_per', $root_per);
		$smarty->assign('root_id', $root_id);
		$smarty->assign('cat_info', $cat_info);
		$smarty->assign('uq_cols', $uq_cols);
		$smarty->assign('tb', $tb);
		$smarty->assign('tb_total', $tb_total);
		$smarty->assign('is_under_gst', $is_under_gst);
		
		/*if(!$sqlonly){
            $this->display('goods_receiving_note.category_summary.table.tpl');
		}*/
	}
	
	private function check_have_subcat($id){
		global $con;
		$con->sql_query("select id from category where root_id=$id limit 1");
		$c = $con->sql_fetchrow();
		if ($c) return true;
		return false;
	}
	
	function ajax_load_category(){
        $this->generate_category_data();
	}
	
	function ajax_load_sku(){
	    global $con, $smarty, $sessioninfo, $config;
	    
	    if(!$this->branch_id_list)  die("No branch selected.");
	    
		if(!$this->branch_id_list)  return;
		
		// construct filter
		$filter = array();
		if($this->cat_id){ // got category clicked
			$con->sql_query("select * from category where id=$this->cat_id");
			$cat_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($cat_info['tree_str']!=''){  // generate category tree
			    $tree_str = $cat_info['tree_str'];
				$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
				if($temp){
                    $con->sql_query("select id,description from category where id in $temp order by level");
                    while ($r = $con->sql_fetchassoc()){
                        $cat_info['cat_tree_info'][] = $r;
					}
				}
				
			}
			
			$pf = "p".($cat_info['level']+1);
			$filter[] = "p".$cat_info['level']."=$this->cat_id";
			$uncat_name = $cat_info['description'];
		}else{  // no select category, show all
            $pf = "p1";
			$uncat_name = 'Un-categorized';
		}
		
		$filter[] = "grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to);
		$filter[] = "grr.active=1 and grn.active=1 and grn.status=1";
		
		if($this->branch_id_list) $filter[] = "grn.branch_id in (".join(",", $this->branch_id_list).")";
		
		//added by gary 7/19/2007 4:09:56 PM
		if($this->status!=''){
			$filter[]="grn.approved=".mi($this->status);
		}
		
		//added by gary 7/19/2007 4:19:16 PM
		if($this->sku_type){
			$filter[]="sku.sku_type=".ms($this->sku_type);
		}

		// lock user allowed department
		if ($sessioninfo['level']<9999) $filter[] = "c.p2 in ($sessioninfo[department_ids])";

		$tb = array();

		$q1 = $con->sql_query("select id,description from category where root_id = ".mi($this->cat_id)." or id=".mi($this->cat_id));
		while($r=$con->sql_fetchassoc($q1)){
			$category[$r['id']] = $r['description'];
		}
		$con->sql_freeresult($q1);
		
		if($filter) $filter = join(' and ', $filter);
	
		$sql = "select sku_items.id,sku_items.sku_item_code,sku_items.description, grr.rcv_date as dt,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as sell,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
				(grn_items.ctn  + (grn_items.pcs / rcv_uom.fraction)),
				(grn_items.acc_ctn + (grn_items.acc_pcs / rcv_uom.fraction))) *
				if (grn_items.acc_cost is null, grn_items.cost,grn_items.acc_cost))as cost,
				sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*if(grn.is_under_gst = 1, grn_items.gst_selling_price, grn_items.selling_price)) as gst_sell,
				grr.currency_code, grr.currency_rate
				from grn_items 
				left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
				left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
				left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
				left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id 
				left join sku_items on grn_items.sku_item_id = sku_items.id 
				left join sku on sku_items.sku_id = sku.id 
				left join category_cache c on sku.category_id = c.category_id 
				where $filter 
				group by sku_item_code,dt 
				having qty>0 
				order by sku_item_code,dt";

		$grn_sql = $con->sql_query($sql);
		
		while($r = $con->sql_fetchassoc($grn_sql)){
			$date_key = date("Ymd", strtotime($r['dt']));

			$tb[$r['sku_item_code']]['info']['sku_item_code'] = $r['sku_item_code'];
			$tb[$r['sku_item_code']]['info']['description'] = $r['description'];
	
			$tb[$r['sku_item_code']]['data'][$r['dt']]['qty'] = $r['qty'];
			$tb[$r['sku_item_code']]['data'][$r['dt']]['sell'] = $r['sell'];
			if(!$r['currency_rate']) $r['currency_rate'] = 1;
			if($r['currency_code']) $tb[$r['sku_item_code']]['data'][$r['dt']]['have_fc'] = 1;
			$tb[$r['sku_item_code']]['data'][$r['dt']]['cost'] = $r['cost'] * $r['currency_rate'];
			$tb[$r['sku_item_code']]['data'][$r['dt']]['gst_sell'] = $r['gst_sell'];

			$tb[$r['sku_item_code']]['total']['qty'] += $r['qty'];
			$tb[$r['sku_item_code']]['total']['sell'] += $r['sell'];
			$tb[$r['sku_item_code']]['total']['cost'] += $r['cost'] * $r['currency_rate'];
			$tb[$r['sku_item_code']]['total']['gst_sell'] += $r['gst_sell'];
			if($r['currency_code']) $tb[$r['sku_item_code']]['total']['have_fc'] = 1;

			$temp = array();
			$temp['y'] = date('Y', strtotime($r['dt']));
			$temp['m'] = date('m', strtotime($r['dt']));
			$temp['d'] = date('d', strtotime($r['dt']));

			$uq_cols[$r['dt']] = $temp;
		}
		$con->sql_freeresult($grn_sql);

		//print_r($tb);
		
		if($tb){
			$is_under_gst = 0;
			foreach($tb as $sid=>$sku_items){
			    if(!$sku_items['data'])  continue;
			    
			    foreach($sku_items['data'] as $date_key=>$r){
                    $tb[$sid]['data']['total']['qty'] += $r['qty'];
			        $tb[$sid]['data']['total']['sell'] += $r['sell'];
			        $tb[$sid]['data']['total']['cost'] += $r['cost'];
			        $tb[$sid]['data']['total']['gst_sell'] += $r['gst_sell'];
			        if($r['have_fc']) $tb[$sid]['data']['total']['have_fc'] = $r['have_fc'];
			    	$tb_total['data'][$date_key]['sell'] += $r['sell'];
			    	$tb_total['data'][$date_key]['cost'] += $r['cost'];
			    	$tb_total['data'][$date_key]['qty'] += $r['qty'];
			    	$tb_total['data'][$date_key]['gst_sell'] += $r['gst_sell'];
			        if($r['have_fc']) $tb_total['data'][$date_key]['have_fc'] = $r['have_fc'];
			    	$tb_total['data']['total']['sell'] += $r['sell'];
			    	$tb_total['data']['total']['cost'] += $r['cost'];
			    	$tb_total['data']['total']['qty'] += $r['qty'];
			    	$tb_total['data']['total']['gst_sell'] += $r['gst_sell'];
			        if($r['have_fc']) $tb_total['data']['total']['have_fc'] = $r['have_fc'];
					
					if($tb[$sid]['data']['total']['gst_sell']) $is_under_gst = 1;
				}
			}
		}

        $root_id = $this->cat_id;
		$root_per = isset($_REQUEST['root_per']) ? mf($_REQUEST['root_per']) : 100;

		$smarty->assign('root_per', $root_per);
		$smarty->assign('root_id', $root_id);
		$smarty->assign('is_itemise_export', $_REQUEST['is_itemise_export']);
		
		$smarty->assign('tb', $tb);
		$smarty->assign('uq_cols', $uq_cols);
		$smarty->assign('tb_total', $tb_total);
		$smarty->assign('is_under_gst', $is_under_gst);
		
		$this->display('goods_receiving_note.category_summary.sku_table.tpl');
	}
}

$GRN_SUMMARY_BY_CATEGORY = new GRN_SUMMARY_BY_CATEGORY('GRN Summary by Category Report');


?>
