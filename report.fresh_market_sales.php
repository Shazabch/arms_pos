<?php
/*
2/11/2011 11:22:59 AM Andy
- Change sales report to use fresh market cost calculated by cron, if no fresh market cost then use grn cost.

6/24/2011 6:11:08 PM Andy
- Make all branch default sort by sequence, code.

2/25/2020 5:27 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
set_time_limit(0);
$maintenance->check(27);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FM_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FM_REPORT', BRANCH_CODE), "/index.php");

class FRESH_MARKET_SALES_REPORT extends Module{
    var $branch_id;
    var $can_select_branch = false;

	function __construct($title){
		global $con, $smarty, $sessioninfo, $config, $con_multi, $appCore;
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();
		
		if(!$_REQUEST['branch_id']) $_REQUEST['branch_id'] = $sessioninfo['branch_id']; // default selected branch
		
	    if(BRANCH_CODE=='HQ'){
	        $this->can_select_branch = true;
            $smarty->assign('can_select_branch', $this->can_select_branch);
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->date_from_key = date("Ymd", strtotime($this->date_from));
		$this->date_to_key = date("Ymd", strtotime($this->date_to));
		
        $this->cat_id  = mi($_REQUEST['cat_id']);
        $this->sku_type = $_REQUEST['sku_type'];
		parent::__construct($title);
	}

	function _default(){
	    global $con, $smarty;
	    
	    $this->init_load();

		if($_REQUEST['load_report']){
		    $this->load_report(true);
            if(isset($_REQUEST['output_excel'])){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export Daily Category Sales Report To Excel");

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
		}    
		
		$this->display();
	}
	
	private function init_load(){
		global $con, $smarty, $con_multi;

		// sku type
		$con_multi->sql_query("select * from sku_type order by code");
		$smarty->assign('sku_type', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		// branches
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group header
		$con_multi->sql_query("select * from branch_group",false,false);
		while($r = $con_multi->sql_fetchassoc()){
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con_multi->sql_freeresult();

		if($this->branches_group){
            // load branch group items
			$con_multi->sql_query("select bgi.*,branch.code,branch.description
			from branch_group_items bgi
			left join branch on bgi.branch_id=branch.id
			where branch.active=1
			order by branch.sequence,branch.code");
			while($r = $con_multi->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con_multi->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
		
		// stock check date list
		$smarty->assign('date_list', $this->load_date(true));
	}
	
	function load_date($sqlonly = false){
		global $con, $smarty, $con_multi;

		$branch_id = mi($this->branch_id);
		$date_list = array();
		$con_multi->sql_query("select distinct date from stock_check where branch_id=$branch_id and is_fresh_market=1 order by date desc");
		while($r = $con_multi->sql_fetchrow()){
			$date_list[] = $r[0];
		}
		$con_multi->sql_freeresult();
		
		if(!$sqlonly){
		    $smarty->assign('date_list', $date_list);
			$ret['html'] = $smarty->fetch('report.fresh_market_sales.date_list.tpl');
			$ret['ok'] = 1;
			print json_encode($ret);
			exit;
		}
		return $date_list;
	}
	
	private function generate_header_date_label(){
	    global $smarty;

        $d1 = strtotime($this->date_from);
		$d2 = strtotime($this->date_to);

		$date_cols = array();
		while($d1<=$d2)
		{
		    $temp = array('y'=>date('Y', $d1), 'm'=>mi(date('m', $d1)));
		    $key = date('Ymd', $d1);
	        $temp['d'] = date('d', $d1);

		    $date_cols[$key] = $temp;
			$d1 += 86400;
		}
		$this->date_cols = $date_cols;
		$smarty->assign('date_cols', $this->date_cols);
	}
	
	private function load_report($sqlonly = false){
	    global $con, $smarty, $con_multi, $sessioninfo;
		
		if(!$this->date_from || !$this->date_to)    $err[] = "No date from or date to.";
		elseif(strtotime($this->date_from)>strtotime($this->date_to))   $err[] = "Date to cannot early than or same to date from.";
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$this->generate_header_date_label();
		
		$report_title = array();
		$report_title[] = "Branch: ".get_branch_code($this->branch_id);
		$sku_type = ($_REQUEST['sku_type']) ? $_REQUEST['sku_type'] : "All";
		$report_title[] = "SKU Type: ".$sku_type;
		$report_title[] = "Date: ".$this->date_from." to ".$this->date_to;
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		
		if($this->cat_id){  // got category clicked
			$con_multi->sql_query("select * from category where id=$this->cat_id");
			$cat_info = $con_multi->sql_fetchassoc();
			$con_multi->sql_freeresult();

			if($cat_info['tree_str']!=''){  // generate category tree
			    $tree_str = $cat_info['tree_str'];
				$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
				if($temp){
                    $con_multi->sql_query("select id,description from category where id in $temp order by level");
                    while ($r = $con_multi->sql_fetchassoc()){
                        $cat_info['cat_tree_info'][] = $r;
					}
					$con_multi->sql_freeresult();
				}

			}

			$pf = "p".($cat_info['level']+1);
			$filter[] = "p".$cat_info['level']."=$this->cat_id";
			$common_fm_filter[]= "p".$cat_info['level']."=$this->cat_id";
			$uncat_name = $cat_info['description'];
		}else{  // no select category, show all
            $pf = "p1";
			$uncat_name = 'Un-categorized';
		}
		
		// get all child category info
	    $con_multi->sql_query("select c.id,c.description, if((select id from category c2 where c2.root_id=c.id limit 1)>0,1,0) as have_subcat
		from category c
		where c.root_id=$this->cat_id");
	    while($r=$con_multi->sql_fetchrow()){
	        $category[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		
		// construct filter
		$filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		if ($sessioninfo['level']<1000){
	        $filter[] = "cc.p2 in ($sessioninfo[department_ids])";
	        $common_fm_filter[]= "cc.p2 in ($sessioninfo[department_ids])";
		}
		if($this->sku_type){
			$filter[]="tbl.sku_type=".ms($this->sku_type);
	        $common_fm_filter[]= "sku.sku_type=".ms($this->sku_type);
		}
		$common_fm_filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";
		
		$params = array();
		$params['branch_id'] = $this->branch_id;
		$params['filter'] = $common_fm_filter;
		$params['pf'] = $pf;
		
		/*if($this->use_report_server)    $con_multi = new mysql_multi(); // connect to report server
		else    $con_multi = $con;*/
		
		$this->get_fresh_market_data($params);
		
		//if($this->use_report_server)    $con_multi->close_connection();
		
		// construct category table
		//print_r($this->data);
		if($this->data){
			foreach($this->data as $sku_id=>$r){
			    if($r['pos']){
					foreach($this->date_cols as $date_key=>$d){ // loop for all sales
						if(isset($r['pos'][$date_key])){
						    $pos = $r['pos'][$date_key];
						    $got_sc = mi($pos['got_sc']);
						    $pos_col = $got_sc ? 'pos' : 'no_sc_pos';
						    
						    $cost = $pos['fresh_market_cost'] ? $pos['fresh_market_cost'] : $pos['default_cost'];
						    
                            $this->cat_data[$r['cat_id']][$pos_col][$date_key]['amt'] += $pos['amt'];
                            $this->cat_data[$r['cat_id']][$pos_col][$date_key]['cost'] += $cost;
                            
                            // total row column
                            $this->cat_data['total'][$pos_col][$date_key]['amt'] += $pos['amt'];
                    		$this->cat_data['total'][$pos_col][$date_key]['cost'] += $cost;
                    		
                    		// category row total
                    		$this->cat_data[$r['cat_id']][$pos_col]['total']['amt'] += $pos['amt'];
                    		$this->cat_data[$r['cat_id']][$pos_col]['total']['cost'] += $cost;
                    		
                    		// total row total
                    		$this->cat_data['total'][$pos_col]['total']['amt'] += $pos['amt'];
                    		$this->cat_data['total'][$pos_col]['total']['cost'] += $cost;
                    		
                    		$this->cat_data['total']['total']['amt'] += $pos['amt'];
                    		$this->cat_data['total']['total']['cost'] += $cost;
						}
					}
				}
			}
		}
		
		//print_r($this->cat_data);
		$smarty->assign('cat_info', $cat_info);
		$smarty->assign('category', $category);
		//$smarty->assign('data', $this->data);
		$smarty->assign('cat_data', $this->cat_data);
		
		if(!$sqlonly){
            $this->display('report.fresh_market_sales.table.tpl');
		}
	}
	
	private function get_fresh_market_data($params){
	    global $con_multi, $con;

	    $common_fm_filter = $params['filter'];
	    $bid = $params['branch_id'];
	    $pf = $params['pf'];

        $data= array();
    	$default_sku_id_list = array();

        // get all fresh market sales
	    $sku_filter = array();
		$sku_filter = $common_fm_filter;
		$sku_filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		$sku_filter = join(' and ', $sku_filter);

		$sql = "select si.sku_id, date, sum(amount) as amt, sum(qty) as qty, sum(cost) as default_cost, $pf as cat_id, sum(fresh_market_cost) as fresh_market_cost
from sku_items_sales_cache_b".$bid." tbl
left join sku_items si on si.id=tbl.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where $sku_filter
group by si.sku_id, date";
        //print $sql;
        $con_multi->sql_query($sql);
		while($r = $con_multi->sql_fetchassoc()){
			$date_key = date("Ymd", strtotime($r['date']));

            //$data[$r['sku_id']]['pos'][$date_key]['qty'] += $r['qty'];
			$this->data[$r['sku_id']]['pos'][$date_key]['amt'] += $r['amt'];
			$this->data[$r['sku_id']]['pos'][$date_key]['default_cost'] += $r['default_cost'];
			$this->data[$r['sku_id']]['pos'][$date_key]['fresh_market_cost'] += $r['fresh_market_cost'];
			$this->data[$r['sku_id']]['cat_id'] = mi($r['cat_id']);
			
			//$this->data[$r['sku_id']]['pos']['total']['amt'] += $r['amt'];

			if(!in_array($r['sku_id'], $default_sku_id_list))	$default_sku_id_list[] = $r['sku_id'];
		}
		$con_multi->sql_freeresult();
		
		//print_r($sku_id_list);
		if(!$default_sku_id_list)	return false;   // only proceed if got fresh market sku
		
		$start = 0;
		$size = 500;
		while($sku_id_list = array_slice($default_sku_id_list, $start, $size)){
		    // get sku item id list
		    $sid_list = array();
		    $sid_str = '';
			$con_multi->sql_query("select id from sku_items where sku_id in (".join(',',$sku_id_list).")");
			while($r = $con_multi->sql_fetchassoc()){
                $sid_list[] = mi($r['id']);
			}
			$con_multi->sql_freeresult();
			if(!$sid_list)  continue;   // no sku item id
			$sid_str = join(',',$sid_list);
			
			// get stock check
			$sc_filter = array();
			$sc_filter = $common_fm_filter;
			$sc_filter[] = "sc.branch_id=$bid and sc.is_fresh_market=1";
			//$sc_filter[] = "sc.date in (".ms($this->date_from).", ".ms($this->date_to).")";
			$sc_filter[] = "si.sku_id in (".join(',', $sku_id_list).")";
			$sc_filter = join(' and ', $sc_filter);

			$sql = "select si.sku_id,min(sc.date) as first_sc_date,max(sc.date) as last_sc_date
from stock_check sc
left join sku_items si on si.sku_item_code=sc.sku_item_code
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where $sc_filter
group by si.sku_id";
			//print $sql;
	        $q_sc = $con_multi->sql_query($sql);
			while($r = $con_multi->sql_fetchassoc($q_sc)){
				$sku_id = mi($r['sku_id']);

				$r['first_sc_date_key'] = date("Ymd", strtotime($r['first_sc_date']));
				$r['last_sc_date_key'] = date("Ymd", strtotime($r['last_sc_date']));
				
				$this->data[$sku_id]['sc'] = $r;
			}
			$con_multi->sql_freeresult($q_sc);
			//print_r($this->data);exit;
			
			// no longer need to gen grn/pos/adj/gra, use cron calculated cost
			// get fresh market grn
			/*$grn_filter = array();
			$grn_filter = $common_fm_filter;
			$grn_filter[] = "grr.rcv_date between ".ms($this->date_from)." and ".ms($this->date_to);
			$grn_filter[] = "si.sku_id in (".join(',', $sku_id_list).")";
			$grn_filter = join(' and ', $grn_filter);

			$sql = "select sum(
	  if (gi.acc_cost is null, gi.cost, gi.acc_cost)
	  *
	  if (gi.acc_ctn is null and gi.acc_pcs is null,
	  	gi.ctn + gi.pcs / rcv_uom.fraction,
	  	gi.acc_ctn + gi.acc_pcs / rcv_uom.fraction
	  )
	) as total_cost, si.sku_id, grr.rcv_date as date
		from grn_items gi
		left join sku_items si on si.id=gi.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		left join uom rcv_uom on gi.uom_id=rcv_uom.id
		left join grn on gi.grn_id=grn.id and gi.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		where $grn_filter and gi.branch_id=$bid and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1
		group by si.sku_id,grr.rcv_date";
		    $q_grn = $con_multi->sql_query($sql);
		    while($r = $con_multi->sql_fetchassoc($q_grn)){
		        $date_key = date("Ymd", strtotime($r['date']));
				$this->data[$r['sku_id']]['grn'][$date_key] = $r;
				$this->data[$r['sku_id']]['grn']['total']['cost'] += $r['total_cost'];
			}
			$con_multi->sql_freeresult($q_grn);

			// get fresh market write-off
			$adj_filter = array();
			$adj_filter = $common_fm_filter;
			$adj_filter[] = "adj.adjustment_date between ".ms($this->date_from)." and ".ms($this->date_to);
			$adj_filter[] = "si.sku_id in (".join(',', $sku_id_list).")";
			$adj_filter = join(' and ', $adj_filter);

			$sql = "select si.sku_id,adj.adjustment_date as date, sum(qty*cost) as total_cost
	from adjustment_items adji
	left join adjustment adj on adj.branch_id=adji.branch_id and adj.id=adji.adjustment_id
	left join sku_items si on si.id=adji.sku_item_id
	left join sku on sku.id=si.sku_id
	left join category_cache cc on cc.category_id=sku.category_id
	where adji.branch_id=$bid and adj.active=1 and adj.status=1 and adj.approved=1 and $adj_filter
	group by si.sku_id,adj.adjustment_date";
			$q_adj = $con_multi->sql_query($sql);
		    while($r = $con_multi->sql_fetchassoc($q_adj)){
		        $date_key = date("Ymd", strtotime($r['date']));
				$this->data[$r['sku_id']]['adj'][$date_key] = $r;
				$this->data[$r['sku_id']]['adj']['total']['cost'] += $r['total_cost'];
			}
			$con_multi->sql_freeresult($q_adj);*/

            $start+=$size;
		}
		
		if($this->data){
            // loop to find each sku whether they got sc or no sc
			foreach($this->data as $sku_id=>$r){
			    // got sales
			    if($r['pos']){
					foreach($r['pos'] as $date_key=>$pos){
                        if($date_key=='total')  continue;   // skip total row
                        
                        $got_sc = false;
                        if(isset($r['sc'])){
							if($date_key>=$r['sc']['first_sc_date_key'] && $date_key<$r['sc']['last_sc_date_key'])  $got_sc = true;
						}
						
						// mark this date as got sc
						if($got_sc)	$this->data[$sku_id]['pos'][$date_key]['got_sc'] = 1;
					}
				}
			}
		}
	}
	
	function ajax_load_category(){
        $this->load_report();
	}
	
	function ajax_load_sku(){
	    global $con, $smarty, $sessioninfo, $config, $con_multi;

	    $this->generate_header_date_label();

        if (!$this->cat_id){    // showing uncategory sku
			$cat_info['description'] = 'Uncategorized';
   			$filter[] = "p0 is null";
   			$common_fm_filter[] = "p0 is null";
		}
		else{
   			$con_multi->sql_query("select id,level,description from category where id=$this->cat_id");
			$cat_info = $con_multi->sql_fetchrow();
			$con_multi->sql_freeresult();
			$pf = "p".($cat_info['level']+1);

			$filter[] = "p".$cat_info['level']."=$this->cat_id";
			$common_fm_filter[] = "p".$cat_info['level']."=$this->cat_id";

			if($sessioninfo['level']<9999){
                $filter[] = "p2 in ($sessioninfo[department_ids])";
                $common_fm_filter[] = "p2 in ($sessioninfo[department_ids])";
			}
		}

		$filter[] = "tbl.date between ".ms($this->date_from)." and ".ms($this->date_to);
		if($this->sku_type){
			$filter[] = "sku.sku_type=".ms($this->sku_type);
			$common_fm_filter[] = "sku.sku_type=".ms($this->sku_type);
		}
		$common_fm_filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";
        $filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";
        
		if($filter) $filter = "where ".join(' and ', $filter);

		$sql = "select tbl.sku_item_id, sum(tbl.qty) as qty, sum(tbl.amount) as amt, sum(tbl.cost) as cost, tbl.date as dt, sku_item_code,description, si.sku_id, sum(fresh_market_cost) as fresh_market_cost
			from sku_items_sales_cache_b".mi($this->branch_id)." tbl
			left join sku_items si on tbl.sku_item_id = si.id
			left join sku on si.sku_id = sku.id
			left join category_cache cc on sku.category_id = cc.category_id
			$filter
			group by tbl.sku_item_id, dt";
		
		//print $query;
		/*if($_REQUEST['use_report_server'])   $con_multi= new mysql_multi();  // use report server
		else	$con_multi = $con;*/

		//print $sql;return;
		$con_multi->sql_query($sql);

		while($r = $con_multi->sql_fetchrow()){
			$date_key = date("Ymd", strtotime($r['dt']));

			$this->sku_items_data[$r['sku_item_id']]['sku_item_code'] = $r['sku_item_code'];
			$this->sku_items_data[$r['sku_item_id']]['description'] = $r['description'];
			$this->sku_items_data[$r['sku_item_id']]['sku_id'] = $r['sku_id'];

	        //$this->sku_items_data[$r['sku_item_id']]['data'][$date_key]['qty'] += $r['qty'];
	        $this->sku_items_data[$r['sku_item_id']]['pos'][$date_key]['amt'] += $r['amt'];
	        $this->sku_items_data[$r['sku_item_id']]['pos'][$date_key]['cost'] += $r['fresh_market_cost'] ? $r['fresh_market_cost'] : $r['cost'];
		}
		$con_multi->sql_freeresult();

		if($this->sku_items_data){
		    $params = array();
		    $params['filter'] = $common_fm_filter;
		    $params['pf'] = $pf;
            $params['branch_id'] = mi($this->branch_id);
            
            $this->get_fresh_market_data($params);
            
            
            if($this->data){
                // loop to reconstruct tb
				foreach($this->sku_items_data as $sid=>$r){
					if(!$this->data[$r['sku_id']] || !$r['pos']){ // no fresh market data for this item or no sales
                        unset($this->sku_items_data[$sid]);   // remove item from original array
                        continue;   
					}
				}
			}
		}

		//print_r($this->data);
		if($this->sku_items_data){
			foreach($this->sku_items_data as $sid=>$sku_items){
			    $sku_id = $sku_items['sku_id'];
			    
			    if($sku_items['pos']){
                    foreach($sku_items['pos'] as $date_key=>$r){
                        $got_sc = $this->data[$sku_id]['pos'][$date_key]['got_sc'];
                        $cost = $r['cost'];
                        
				        $this->sku_items_data[$sid]['pos']['total']['amt'] += $r['amt'];
				        $this->sku_items_data[$sid]['pos']['total']['cost'] += $cost;
				        
				        if($got_sc) $this->sku_items_data[$sid]['pos'][$date_key]['got_sc'] = 1;
				        
				        $this->sku_items_total_data['total']['pos']['total']['amt'] += $r['amt'];
				        $this->sku_items_total_data['total']['pos']['total']['cost'] += $cost;
				        
				        $this->sku_items_total_data['total']['pos'][$date_key]['amt'] += $r['amt'];
				        $this->sku_items_total_data['total']['pos'][$date_key]['cost'] += $cost;
					}
				}
			}
		}

		//if($_REQUEST['use_report_server'])   $con_multi->close_connection();
		//print_r($this->sku_items_total_data);
        $smarty->assign('sku_items_data', $this->sku_items_data);
		$smarty->assign('sku_items_total_data', $this->sku_items_total_data);
        
        //print_r($this->sku_items_total_data);
		$this->display('report.fresh_market_sales.sku_table.tpl');
	}
}

$FRESH_MARKET_SALES_REPORT = new FRESH_MARKET_SALES_REPORT('Fresh Market Sales Report');
?>
