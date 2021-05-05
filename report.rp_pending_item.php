<?php
/*
*/
include("include/common.php");

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(130);

class RETURN_POLICY_PENDING_ITEM extends Module{
	var $branches_group = array();  // use to hold all branches group data
	var $branches = array();    // use to hold all branches data
	var $branch_id; // use to store user selected branch id
	var $brands = array();
	var $colors = array();
	var $sizes = array();
	
	var $cat_id = 0;
	var $sku_type;
	var $data = array();
	var $total = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
            $this->branch_id = mi($sessioninfo['branch_id']);
		}   
		
		$this->date_from = $_REQUEST['from'];
		$this->date_to = $_REQUEST['to'];
		
        $this->cat_id  = mi($_REQUEST['cat_id']);
        $this->sku_type = $_REQUEST['sku_type'];
                
		parent::__construct($title);
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo;
	    
        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		// load branches
		$con->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		// load branch group
		$this->branches_group = $this->load_branch_group();
		
		// sku type
		$con->sql_query("select * from sku_type order by code");
		$smarty->assign('sku_type', $con->sql_fetchrowset());
		$con->sql_freeresult();
		
		// brand
		if($sessioninfo['brand_ids'])	$filter_brand = " and id in (".$sessioninfo['brand_ids'].")";
		$con->sql_query("select * from brand where active=1 $filter_brand order by description");
		while($r = $con->sql_fetchassoc()){
			$this->brands[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('brands', $this->brands);

		// vendor
		if($sessioninfo['vendor_ids'])	$filter_vd = " and id in (".$sessioninfo['vendor_ids'].")";
		$con->sql_query("select * from vendor where active=1 $filter_vd order by description");
		while($r = $con->sql_fetchassoc()){
			$this->vendors[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('vendors', $this->vendors);
	}
	
	function _default(){
	    global $sessioninfo, $smarty;
	    
	    if($_REQUEST['subm']){
			$this->generate_report();
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

	function show_report(){
		$this->process_form();
		$this->generate_report();
		$this->display();
	}

	private function run_report($bid){
        global $con, $smarty,$sessioninfo;

		$con_multi = new mysql_multi();
		if(!$con_multi){
	 		die("Error: Fail to connect report server");
		}

		$next_cat_p = $this->max_cat_lv > $this->cat_info['level'] ? 'p'.($this->cat_info['level']+1) : 'p'.$this->max_cat_lv;
		
		if($this->view_type == 1) $order = "si.sku_item_code";
		else $order = "c.description";

		$sql = $con->sql_query("select rpsc.*, si.sku_item_code, si.description, si.mcode, c.description as cname, 
								$next_cat_p as next_cat_id, sku.category_id as sku_cat_id
								from return_policy_sales_cache rpsc
								left join sku_items si on si.id = rpsc.sku_item_id
								left join sku on sku.id = si.sku_id
								left join branch b on b.id = rpsc.branch_id
								left join category_cache cc on cc.category_id=sku.category_id
								left join category c on c.id=cc.$next_cat_p
								where rpsc.branch_id = ".mi($bid)." and ".join(" and ", $this->filter)."
								order by $order");

		while($r = $con->sql_fetchassoc($sql)){
			if($this->view_type == 1){ // by sku
				$key = $r['sku_item_id'];
			}else{ // by category
				$next_cat_id = mi($r['next_cat_id']);
				$cat_id_key = $next_cat_id;
				
				if($next_cat_id && !isset($this->cat_info_list[$next_cat_id])){
					$this->cat_info_list[$next_cat_id]['description'] = $r['cname'];
					
					// check whether have sub-category
					$con->sql_query("select id from category where root_id=$next_cat_id limit 1");
					$this->cat_info_list[$next_cat_id]['have_subcat'] = $con->sql_numrows();
					$con->sql_freeresult();
				}else{
					// sku directly under this category
					if(!$next_cat_id && $r['sku_cat_id'] == $cat_id){
						$cat_id_key = $cat_id;
					}
				}
				$key = $cat_id_key;
			}

			$this->table[$key]['sku_item_code'] = $r['sku_item_code'];
			$this->table[$key]['description'] = trim($r['description']);
			$this->table[$key]['mcode'] = $r['mcode'];
			$this->table[$key]['cname'] = trim($r['cname']);
			$this->table[$key]['count'] += $r['count'];
			$this->table[$key]['refund'] += $r['refund'];
			$this->table[$key]['charges'] += $r['charges'];
			$this->table[$key]['expired_count'] += $r['expired_count'];
			
			$this->total['count'] += $r['count'];
			$this->total['refund'] += $r['refund'];
			$this->total['charges'] += $r['charges'];
			$this->total['expired_count'] += $r['expired_count'];
		}

		$con_multi->sql_freeresult($sql);
		$con_multi->close_connection();
	}
	
	function generate_report(){
		global $con, $smarty;

		$con->sql_query("select max(level) from category");
		$this->max_cat_lv = $con->sql_fetchfield(0);
		$con->sql_freeresult();

		$this->table = $this->cat_info_list = array();
		if($this->branch_id_list){
			foreach($this->branch_id_list as $bid){
				$this->run_report($bid);
			}
		}

		// set report fixed row display
		$smarty->assign('report_row', 25);
		
		$this->report_title[] = "Date: ".$this->date_from." to ".$this->date_to;

		$this->report_title[] = "SKU Type: ".($sku_type ? $sku_type : 'All');
		$this->report_title[] = "Category: ".$this->cat_info['description'];

		$brand_desc = ($this->brands[$this->brand_id]) ? $this->brands[$this->brand_id] : "All";
		$this->report_title[] = "Brand: ".$brand_desc;
		$vd_desc = ($this->vendors[$this->vendor_id]) ? $this->vendors[$this->vendor_id] : "All";
		$this->report_title[] = "Vendor: ".$vd_desc;

		if($this->view_type == 1) $view_desc = "SKU";
		else $view_desc = "Category";

		$this->report_title[] = "View By: ".$view_desc;
		
        $smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $this->report_title));
		$smarty->assign('table', $this->table);
		$smarty->assign('total', $this->total);
		$smarty->assign('category_tree', $this->category_tree);
		$smarty->assign('selected_cat_info', $this->cat_info);
		$smarty->assign('cat_info_list', $this->cat_info_list);
		if($_REQUEST['indent']){
			$smarty->assign('indent', $_REQUEST['indent']);
		}
	}
	
	/*private function generate_report(){
		global $con, $config, $smarty, $sessioninfo, $con_multi, $config;
		
		$con->sql_query("select max(level) from category");
		$max_cat_lv = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		$report_title[] = "Branch: ".get_branch_code($bid);
		$report_title[] = "SKU Type: ".($sku_type ? $sku_type : 'All');
		$report_title[] = "Date: $date_from to $date_to";
		$report_title[] = "Category: ".$cat_info['description'];
		
		if($err){
			$smarty->assign('err', $err);
			return;
		}
		
		$filter = array();
		$filter[] = "s.date between ".ms($date_from)." and ".ms($date_to);
		$filter[] = "sku.brand_id in (".join(',', $brand_ids).")";
		$filter[] = "cc.p".$cat_info['level']."=$cat_id";
		if($sku_type)	$filter[] = "sku.sku_type=".ms($sku_type);		
		
		///////////// size /////////////
		$filter_size = array();
		$tmp_size = array();
		foreach($sizes as $s){
			if($s == 'NOTSET'){
				$filter_size[] = "(si.size='' or si.size is null)";
				continue;
			}
			$tmp_size[] = ms($s);
		}
		if($tmp_size)	$filter_size[] = "si.size in (".join(',', $tmp_size).")";
		$filter[] = "(".join(' or ', $filter_size).")";
		
		//////////// color ////////////
		$filter_color = array();
		$tmp_color = array();
		foreach($colors as $c){
			if($c == 'NOTSET'){
				$filter_color[] = "(si.color='' or si.color is null)";
				continue;
			}
			$tmp_color[] = ms($c);
		}
		if($tmp_color)	$filter_color[] = "si.color in (".join(',', $tmp_color).")";
		$filter[] = "(".join(' or ', $filter_color).")";
		
		$filter = "where ".join(' and ', $filter);
		
		$sales_tbl = "sku_items_sales_cache_b".$bid;
		$next_cat_p = $max_cat_lv > $cat_info['level'] ? 'p'.($cat_info['level']+1) : 'p'.$max_cat_lv;
		
		$sql = "select s.*,sku.brand_id, cc.p3,si.size,si.color, if(sku.is_fresh_market='inherit',cc.is_fresh_market,sku.is_fresh_market) as is_fresh_market, $next_cat_p as next_cat_id, c.description as cname, sku.category_id as sku_cat_id
from  $sales_tbl s
left join sku_items si on si.id=s.sku_item_id
left join sku on sku.id=si.sku_id
left join category_cache cc on cc.category_id=sku.category_id
left join category c on c.id=cc.$next_cat_p
$filter";
		//print $sql;
		//return;

		$con_multi= new mysql_multi();
		$q1 = $con_multi->sql_query($sql);
		$this->data = array();
		$this->cat_info_list = array();
		
		while($r = $con_multi->sql_fetchassoc($q1)){
			$next_cat_id = mi($r['next_cat_id']);
			$sz = trim($r['size']);
			$clr = trim($r['color']);
			$sid = mi($r['sku_item_id']);
			$key = $sz."_".$clr;
			$cat_id_key = $next_cat_id;
			
			if(!$sz)	$sz = 'NOTSET';
			if(!$clr)	$clr = 'NOTSET';
			
			if($next_cat_id && !isset($this->cat_info_list[$next_cat_id])){
				$this->cat_info_list[$next_cat_id]['description'] = $r['cname'];
				
				// check whether have sub-category
				$con->sql_query("select id from category where root_id=$next_cat_id limit 1");
				$this->cat_info_list[$next_cat_id]['have_subcat'] = $con->sql_numrows();
				$con->sql_freeresult();
			}else{
				// sku directly under this category
				if(!$next_cat_id && $r['sku_cat_id'] == $cat_id){
					$cat_id_key = $cat_id;
				}
			}
			$this->data[$cat_id_key]['cname'] = trim($r['cname']);
			
			// amt
			$this->data[$cat_id_key]['amount'] += round($r['amount'], 2);
			
			// cost
			$cost = round(($r['is_fresh_market'] && $r['fresh_market_cost']) ? $r['fresh_market_cost'] : $r['cost'], 2);
			$this->data[$cat_id_key]['cost'] += round($cost, 2);
			
			// qty
			$this->data[$cat_id_key]['qty'] += $r['qty'];
			
			// get sales trend
			$sales_trend = get_sku_sales_trend($bid, $sid);
			
			$this->data[$cat_id_key]['sales_trend']['qty'][1] += $sales_trend['sales_trend']['qty'][1];
			$this->data[$cat_id_key]['sales_trend']['qty'][3] += $sales_trend['sales_trend']['qty'][3];
			$this->data[$cat_id_key]['sales_trend']['qty'][6] += $sales_trend['sales_trend']['qty'][6];
			$this->data[$cat_id_key]['sales_trend']['qty'][12] += $sales_trend['sales_trend']['qty'][12];
			
			// data by color/size
			$this->data[$cat_id_key]['by_color_size'][$clr][$sz]['qty'] += $r['qty'];
			
			if(!isset($this->data[$cat_id_key]['size_list']))	$this->data[$cat_id_key]['size_list'] = array();
			if(!isset($this->data[$cat_id_key]['size_list'][$sz]))	$this->data[$cat_id_key]['size_list'][$sz] = 1;
		}
		$con_multi->sql_freeresult($q1);
		
		// calculate GP
		if($this->data){
			foreach($this->data as $cat_id_key=>$r){
				$gp = $r['amount'] - $r['cost'];
				$gp_per = $r['amount'] ? $gp/$r['amount'] : 0;
				
				$this->data[$cat_id_key]['gp'] += $gp;
				$this->data[$cat_id_key]['gp_per'] = round($gp_per*100,2);
				
				// avg
				$this->data[$cat_id_key]['avg_amt'] = round($r['amount']/$r['qty'],2);
				$this->data[$cat_id_key]['avg_cost'] = round($r['cost']/$r['qty'],$config['global_cost_decimal_points']);
				$this->data[$cat_id_key]['avg_gp'] = $this->data[$cat_id_key]['avg_amt'] - $this->data[$cat_id_key]['avg_cost'];
				$this->data[$cat_id_key]['avg_gp_per'] = round($this->data[$cat_id_key]['avg_gp']/ $this->data[$cat_id_key]['avg_amt']*100,2);
				
				// total
				$this->total['qty'] += $r['qty'];
				$this->total['amount'] += $r['amount'];
				$this->total['cost'] += $r['cost'];
				$this->total['gp'] += $gp;
			
				// total sales trend
				$this->total['sales_trend']['qty'][1] += $r['sales_trend']['qty'][1];
				$this->total['sales_trend']['qty'][3] += $r['sales_trend']['qty'][3];
				$this->total['sales_trend']['qty'][6] += $r['sales_trend']['qty'][6];
				$this->total['sales_trend']['qty'][12] += $r['sales_trend']['qty'][12];	
			}
			$this->total['gp_per'] = round($this->total['gp']/$this->total['amount']*100,2);
			
			// total avg
			$this->total['avg_amt'] = round($this->total['amount']/$this->total['qty'],2);
			$this->total['avg_cost'] = round($this->total['cost']/$this->total['qty'],$config['global_cost_decimal_points']);
			$this->total['avg_gp'] = $this->total['avg_amt'] - $this->total['avg_cost'];
			$this->total['avg_gp_per'] = round($this->total['avg_gp']/$this->total['avg_amt']*100,2);
			
			ksort($this->data);
		}
		
		//print_r($this->cat_info_list);
		//print_r($this->data);
		$smarty->assign('selected_cat_info', $cat_info);
		$smarty->assign('data', $this->data);
		$smarty->assign('total', $this->total);
		$smarty->assign('cat_info_list', $this->cat_info_list);
		$smarty->assign('report_title', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_title));
		if($_REQUEST['indent']){
			$smarty->assign('indent', $_REQUEST['indent']);
		}
	}*/

	function process_form(){
	    global $con, $smarty;

		if(!$_REQUEST['date_from']){
			if($_REQUEST['date_to']) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month",strtotime($_REQUEST['date_to'])));
			else{
				$_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
				$_REQUEST['date_to'] = date('Y-m-d');
			}
		}
		if(!$_REQUEST['date_to'] || strtotime($_REQUEST['date_from']) > strtotime($_REQUEST['date_to'])){
			$_REQUEST['date_to'] = date('Y-m-d', strtotime("+1 month", strtotime($_REQUEST['date_from'])));
		}

		//if($_REQUEST['view_type'] == 1) $date_type = "month";
		//else $date_type = "year";

		// check if the date is more than 1 month/year
		$end_date =date("Y-m-d",strtotime("+1 month",strtotime($_REQUEST['date_from'])));
    	if(strtotime($_REQUEST['date_to'])>strtotime($end_date)) $_REQUEST['date_to'] = $end_date;
		
		$this->date_from = $_REQUEST['date_from'];
		$this->date_to = $_REQUEST['date_to'];
		$this->sku_type = trim($_REQUEST['sku_type']);
		$this->cat_id = mi($_REQUEST['category_id']);
		$this->brand_id = $_REQUEST['brand_id'];
		$this->vendor_id = $_REQUEST['vendor_id'];
		
		if($this->cat_id){
			// get category info
			$this->cat_info = get_category_info($this->cat_id);
			if(!$this->cat_info)	$err[] = "Invalid Category.";
			$this->cat_info['cat_tree_info'] = get_cat_tree_info($this->cat_id, $this->cat_info['tree_str']);
			
			// to fix if user direct click on link to load the report
			$_REQUEST['category'] = $this->cat_info['description'];

			$tt = '';
			if($this->cat_info['cat_tree_info']){
				foreach($this->cat_info['cat_tree_info'] as $tmp_cat){
					if($tt)	$tt .= " > ";
					$tt .= $tmp_cat['description'];
				}
			}
			
			if($tt)	$tt .= " > ";
			$tt .= $this->cat_info['description'];
			$this->category_tree = $_REQUEST['category_tree'] = $tt;
		}elseif($this->cat_id==="0"){
			$_REQUEST['all_category'] = true;
			$_REQUEST['category'] = "";
		}else{
			if(!$_REQUEST['all_category']) $error[] = "Please select category.";
		}
		
		$con->sql_query("select max(level) from category");
		$this->max_cat_lv = $con->sql_fetchfield(0);
		$con->sql_freeresult();
		
		if(BRANCH_CODE == 'HQ'){    // HQ mode
			$branch_id = mi($_REQUEST['branch_id']);
			$bgid = explode(",",$_REQUEST['branch_id']);
			if($bgid[1] || $branch_id<0){ // branch group selected
				if($this->branches_group){
					foreach($this->branches_group['items'][$bgid[1]] as $bid=>$b){
						$this->branch_id_list[] = $bid;
					}
				}
				$this->report_title[] = "Branch Group: ".$this->branches_group['header'][$bgid[1]]['code'];
			}elseif($branch_id){  // single branch selected
			    $this->branch_id_list[] = $branch_id;
                $this->report_title[] = "Branch: ".get_branch_code($branch_id);
			}   
			else{   // all branches selected
				foreach($this->branches as $bid=>$b){
                    $this->branch_id_list[] = $bid;
				}
				$this->report_title[] = "Branch: All";
			}
		}else{  // Branches mode
            //$branch_id = mi($sessioninfo['branch_id']);
            $this->branch_id_list[] = mi($sessioninfo['branch_id']);
            $this->report_title[] = "Branch: ".BRANCH_CODE;
		}

		$this->filter = array();
		$this->filter[] = "rpsc.date between ".ms($this->date_from)." and ".ms($this->date_to);
		//if($this->brand_id) $this->filter[] = "sku.brand_id = ".mi($this->brand_id);
		//if($this->vendor_id) $this->filter[] = "sku.vendor_id = ".mi($this->vendor_id);
		if($this->cat_id) $this->filter[] = "cc.p".mi($this->cat_info['level'])." = ".mi($this->cat_id);
		//if($this->sku_type) $this->filter[] = "sku.sku_type = ".ms($this->sku_type);	
		//parent::process_form();
		if($error){
			$smarty->assign("err", $error);
			$this->display();
			exit;
		}
	}
	
	function ajax_expand_sub(){
		global $con, $smarty;
		$this->process_form();
		$this->generate_report();
		$this->display('report.rp_pending_item.row.tpl');
	}
	
	function load_branch_group($id=0){
		global $con,$smarty;
	    if(isset($this->branch_group))  return $this->branch_group;
		$branch_group = array();
		
		// check whether select all or specified group
		if($id>0){
			$where = "where id=".mi($id);
			$where2 = "and bgi.branch_group_id=".mi($id);
		}
		// load header
		$con->sql_query("select * from branch_group $where",false,false);
		if($con->sql_numrows()<=0) return;
		while($r = $con->sql_fetchrow()){
            $branch_group['header'][$r['id']] = $r;
		}
		

		// load items
		$con->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id where branch.active=1 $where2 order by branch.sequence, branch.code",false,false);
		while($r = $con->sql_fetchrow()){
	        $branch_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	        $branch_group['have_group'][$r['branch_id']] = $r['branch_id'];
		}
		
		$this->branch_group = $branch_group;
		//print_r($this->branch_group);
		$smarty->assign('branch_group',$branch_group);
		$smarty->assign('branches_group',$branch_group);
		return $branch_group;
	}
}

$RETURN_POLICY_PENDING_ITEM = new RETURN_POLICY_PENDING_ITEM('Return Policy Pending Item Report');
?>
