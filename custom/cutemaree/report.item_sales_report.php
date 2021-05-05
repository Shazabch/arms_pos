<?php
/*
5/15/2013 2:51 PM Andy
- Fix show cost problem.
- Add total row.

7/29/2015 6:05 PM Andy
- Fix sales qty wrong.
*/
include("../../include/common.php");
$maintenance->check(119);

//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");


class ITEM_SALES_REPORT extends Module{
	var $branches = array();
	var $branches_group = array();
	
	function __construct($title){
        global $con, $smarty, $sessioninfo, $config;
        
        // branches
        $q1 = $con->sql_query("select * from branch where active=1 and code<>'HQ' order by sequence, code");
		$this->branches = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("branches", $this->branches);
		
		// load branch group items
		$this->branches_group = array();
		$q1 = $con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 order by branch.sequence, branch.code");
		while($r = $con->sql_fetchassoc($q1)){
			if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
	        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		$con->sql_freeresult($q1);
		
		// load branch group header
		$q1 = $con->sql_query("select * from branch_group");
		while($r = $con->sql_fetchassoc($q1)){
			if(!$this->branches_group['items'][$r['id']]) continue;
            $this->branches_group['header'][$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("branches_group", $this->branches_group);
		//print_r($this->branches_group);
		
		if (!$_REQUEST['date_to']) $_REQUEST['date_to'] = date('Y-m-d');
		if (!$_REQUEST['date_from']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		
        parent::__construct($title);
    }
    
    function _default(){
    	global $con, $smarty, $sessioninfo, $config;
    	
    	if($_REQUEST['show_report']){
    		$this->load_report();
    		
    		if($_REQUEST['export_excel']){
			    include_once("include/excelwriter.php");
	            log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export ".$this->title);

				Header('Content-Type: application/msexcel');
				Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
				print ExcelWriter::GetHeader();
				$smarty->assign('no_header_footer', 1);
			}
    	}
    	
		$this->display("cutemaree/report.item_sales_report.tpl");
	}
	
	private function load_report(){
		global $con, $smarty, $sessioninfo, $config;
		
		$form = $_REQUEST;
		
		$report_title = array();
		$branch_id_list = array();
		
		if(preg_match("/^REGION_/", $form['branch_id'])){	// region
			$region = str_replace("REGION_", "", $form['branch_id']);
			$q1 = $con->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

			while($r = $con->sql_fetchassoc($q1)){
				if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
				$branch_id_list[] = $r['id'];
			}
			$con->sql_freeresult($q1);
			$report_title[] = "Region: ".$region;
		}elseif($form['branch_id']<0){ // is branch group
			$bgid = abs($form['branch_id']);
			$report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid]['code'];
			foreach($this->branches_group['items'][$bgid] as $bid=>$r){
				$branch_id_list[] = $bid;
			}
		}elseif($form['branch_id']>0){	// selected branch
			$branch_id_list = array($form['branch_id']);
			$con->sql_query("select code from branch where id=".mi($form['branch_id']));
			$report_title[] = "Branch: ".$con->sql_fetchfield(0);
			$con->sql_freeresult();
		}else{	// all branch
			$branch_id_list = array_keys($this->branches);
			$report_title[] = "Branch: All";
		}
		
		$filter = array();
		//print_r($form);
		
		if($form['filter_by'] == 'sku'){
			if($form['sku_code_list_2']){
				$report_title[] = "Selected SKU";
	    		$sku_item_code_list = explode(",",$form['sku_code_list_2']);
	    		if($sku_item_code_list){
	    			$sku_item_code_str = '';
	    			foreach($sku_item_code_list as $code){
	    				if($sku_item_code_str)	$sku_item_code_str.= ',';
	    				$sku_item_code_str .= ms($code);
	    			}
	    			$filter[] = "si.sku_item_code in ($sku_item_code_str)";
	    		}else	$err[] = "Please select at least 1 sku.";
			}else	$err[] = "Please select at least 1 sku.";
		}elseif($form['filter_by'] == 'cat'){
			if(!$form['all_category']){
				if(!$form['category_id'])	$err[] = "Please select category.";
				else{
					$cat_info = get_category_info($form['category_id']);
					if($cat_info){
						$filter[] = "p".$cat_info['level']."=".$form['category_id'];
						$report_title[] = "Category: ".$cat_info['description'];
					}else	$err[] = "Invalid Category.";
				}
			}else	$report_title[] = "Category: All";
		}else	$err[] = "Invalid Filter Type.";
		
		if(!$form['date_from'])	$err[] = "Invalid Date From.";
		if(!$form['date_to'])	$err[] = "Invalid Date To.";
		if(strtotime($form['date_to']) < strtotime($form['date_from']))	$err[] = "Date To cannot earlier than Date From.";
		
		if($sku_item_code_str){
			$category = array();
			$con->sql_query($sql = "select sku_item_code,description from sku_items where sku_item_code in ($sku_item_code_str)");
	        while($r = $con->sql_fetchassoc()){
	        	$category[] = $r;
	        }
	        $con->sql_freeresult();
			$smarty->assign('category',$category);
		}
		
		$show_cost = false;
		if(privilege('SHOW_COST') && $form['show_cost'])	$show_cost = true;
		else	unset($_REQUEST['show_cost']);
		$smarty->assign('show_cost', $show_cost);
		
		if($err){
			$smarty->assign('err', $err);
			return false;
		}
		
		$report_title[] = "Date: ".$form['date_from']." to ".$form['date_to'];
		
		
		
		$filter[] = "si.active=1";		

		$filter = join(' and ', $filter);
		
		$this->data = array();
		$this->total = array();
		
		$con_multi=new mysql_multi(); 
		
		// get HQ GRN
		$q_grn - $con_multi->sql_query("select grn_items.sku_item_id as sid, (if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)) as qty,
		(
		  if (grn_items.acc_cost is null, grn_items.cost, grn_items.acc_cost)
		  *
		  if (grn_items.acc_ctn is null and grn_items.acc_pcs is null,
		  	grn_items.ctn + grn_items.pcs / rcv_uom.fraction,
		  	grn_items.acc_ctn + grn_items.acc_pcs / rcv_uom.fraction
		  )
		) as cost, grr.rcv_date as dt, grn.grr_id, grn.is_future,
		gi.type
		from grn_items
		left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
		left join grn on grn_id=grn.id and grn_items.branch_id=grn.branch_id
		left join grr on grn.grr_id = grr.id and grn.branch_id = grr.branch_id
		left join grr_items gi on gi.id=grn.grr_item_id and gi.branch_id=grn.branch_id
		join sku_items si on si.id=grn_items.sku_item_id
		left join sku on sku.id=si.sku_id
		left join category_cache cc on cc.category_id=sku.category_id
		where grn_items.branch_id=1 and grr.active=1 and grn.approved=1 and grn.status=1 and grn.active=1 and grr.rcv_date between ".ms($form['date_from'])." and ".ms($form['date_to'])." and grr.vendor_id>0 and grn.is_ibt=0 and $filter order by grr.rcv_date, grr.id");
		while($r = $con_multi->sql_fetchassoc($q_grn)){
			if($r['qty']<=0)	continue;
			
			$sid = mi($r['sid']);
			
			$this->data[$sid]['grn'][$r['dt']]['qty'] += $r['qty'];
			$this->data[$sid]['grn'][$r['dt']]['cost'] += $r['cost'];
			
			$this->data[$sid]['grn_total']['qty'] += $r['qty'];
			
			$this->total['grn']['qty'] += $r['qty'];
			$this->total['grn']['cost'] += $r['cost'];
		}
		$con_multi->sql_freeresult($q_grn);
		
		// get branch sales
		$q_pos = $con_multi->sql_query("select cii.sku_item_id as sid,ci.ci_date,ci.ci_branch_id,
(cii.ctn *uom.fraction + cii.pcs) as qty,
((cii.cost_price/uom.fraction)*(cii.ctn *uom.fraction + cii.pcs)) as amount,
((cii.cost_price/uom.fraction)*(cii.ctn *uom.fraction + cii.pcs)) as cost_b4_discount,
(select trade_discount_code from sku_items_price_history siph where siph.branch_id=ci.ci_branch_id and siph.sku_item_id=cii.sku_item_id and siph.added<=ci.ci_date order by added desc limit 1) as trade_discount_code,sku.default_trade_discount_code,ci.discount_percent as sheet_discount_percent, cii.discount as item_discount_percent
from ci
left join ci_items cii on ci.id=cii.ci_id and ci.branch_id=cii.branch_id
left join uom on cii.uom_id=uom.id
join sku_items si on si.id=cii.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
where ci.ci_branch_id in (".join(',', $branch_id_list).") and ci.export_pos=1 and ci.ci_date between ".ms($form['date_from'])." and ".ms($form['date_to'])." and ci.active=1 and ci.export_pos=1 and ci.type='sales' and $filter");
		
		while($r = $con_multi->sql_fetchassoc()){
			$r['cost'] = $r['cost_b4_discount'];

			if($r['item_discount_percent']){    // deduct item discount first
                $r['item_discount_percent_arr'] = explode("+", $r['item_discount_percent']);
                if($r['item_discount_percent_arr'][0]){
					$r['cost'] = $r['cost'] * (1-($r['item_discount_percent_arr'][0]/100));
				}
				if($r['item_discount_percent_arr'][1]){
					$r['cost'] = $r['cost'] * (1-($r['item_discount_percent_arr'][1]/100));
				}
			}

			if($r['sheet_discount_percent']){
                $r['sheet_discount_percent_arr'] = explode("[+]", $r['sheet_discount_percent']);
                if($r['sheet_discount_percent_arr'][0]){
					$r['cost'] = $r['cost'] * (1-($r['sheet_discount_percent_arr'][0]/100));
				}
				if($r['sheet_discount_percent_arr'][1]){
					$r['cost'] = $r['cost'] * (1-($r['sheet_discount_percent_arr'][1]/100));
				}
			}

			$this->data[$r['sid']]['sales']['total']['qty'] += $r['qty'];
			$this->data[$r['sid']]['sales']['total']['amt'] += $r['amount'];
			$this->data[$r['sid']]['sales']['total']['cost'] += $r['cost'];
			
			if(!$this->data[$r['sid']]['grn']){
				$this->data[$r['sid']]['grn'] = array(array(0));
			}
			$this->total['sales']['qty'] += $r['qty'];
		}
		$con_multi->sql_freeresult();
		
		if($this->data){
			$item_per_query = 3;
			$total_item_count = count($this->data);
			for($i=0; $i<$total_item_count; $i+=$item_per_query){
				$sid_list = array_slice(array_keys($this->data), $i, $item_per_query);
				
				$y = date("Y", strtotime($form['date_to']));
				
				$sb_sql = "select sb.sku_item_id as sid, sb.qty,sb.cost 
				from stock_balance_b%s_".$y." sb
				where ".ms($form['date_to'])." between sb.from_date and sb.to_date and sb.sku_item_id in (".join(',', $sid_list).")";
				
				// get stock balance
				$con_multi->sql_query(sprintf($sb_sql, 1)); // HQ
				while($r = $con_multi->sql_fetchassoc()){
					$item_cost = round($r['qty']*$r['cost'],5);
					
					$this->data[$r['sid']]['stock_balance']['hq']['qty'] = $r['qty'];
					$this->data[$r['sid']]['stock_balance']['hq']['cost'] = $item_cost;
					
					$this->total['stock_balance']['hq']['qty'] += $r['qty'];
					$this->total['stock_balance']['hq']['cost'] += $item_cost;
				}
				$con_multi->sql_freeresult();
				
				// branch balance
				foreach($branch_id_list as $tmp_bid){
					$con_multi->sql_query(sprintf($sb_sql, $tmp_bid)); // HQ
					while($r = $con_multi->sql_fetchassoc()){
						$item_cost = round($r['qty']*$r['cost'], 5);
						$this->data[$r['sid']]['stock_balance']['branch']['qty'] += $r['qty'];
						$this->data[$r['sid']]['stock_balance']['branch']['cost'] += $item_cost;
						
						$this->total['stock_balance']['branch']['qty'] += $r['qty'];
						$this->total['stock_balance']['branch']['cost'] += $item_cost;
					}
					$con_multi->sql_freeresult();
				}
		
				// get sku item info		
				$con->sql_query("select id,sku_item_code,artno,mcode, description from sku_items where id in (".join(',', $sid_list).")");
				while($r = $con->sql_fetchassoc()){
					$this->data[$r['id']]['info'] = $r;
				}
				$con->sql_freeresult();
			}
			
			if($this->total['grn']['qty']>0 && $this->total['sales']['qty']){
				$this->total['sales']['per'] =round(($this->total['sales']['qty']/$this->total['grn']['qty'])*100,2);
			}
		}
		
		if($form['sort_by']){
			$this->sort_by = $form['sort_by'];
			$this->sort_order = $form['sort_order'] == 'desc' ? 'desc' : 'asc';
			
			uasort($this->data, array($this, 'sort_item'));
		}
		
		//print_r($this->data);
		
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		$smarty->assign('data', $this->data);
		$smarty->assign('total', $this->total);
	}
	
	private function sort_item($a, $b){
		
		if($a['info'][$this->sort_by] == $b['info'][$this->sort_by]) return 0;
		
		if($this->sort_order == 'desc'){
			return $a['info'][$this->sort_by] > $b['info'][$this->sort_by] ? -1 : 1;
		}else{
			return $a['info'][$this->sort_by] > $b['info'][$this->sort_by] ? 1 : -1;
		}
	}
}

$ITEM_SALES_REPORT = new ITEM_SALES_REPORT('Item Sales and Balance Report');
?>
