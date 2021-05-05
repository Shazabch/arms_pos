<?php
/*
1/3/2012 9:54:08 AM Andy
- Add size and color column.

1/31/2012 3:18:52 PM Andy
- Reconstruct report layout to show by category instead of SKU.
- Enhance report to able to on page load sub-category.

2/8/2012 2:14:22 PM Andy
- Remove color/size by row, change to show by matrix table.
- Add "Average" column.
- Add "Qty Matrix Table".

4/9/2012 5:26:23 PM Justin
- Added to accept and calculate multiple branches sales.

9/5/2012 3:30:35 PM Fithri
- Add price range filter

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

4/2/2014 11:02 AM Fithri
- add option to select all SKU / branches for some reports, require config 'allow_all_sku_branch_for_selected_reports' to be turned on
- change some words to use British english

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

10/2/2017 3:36 PM Justin
- Enhanced to call sales trend from skuManager.php. 

2/20/2020 5:55 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".
*/
include("include/common.php");
$maintenance->check(106);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('REPORTS_SALES')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'REPORTS_SALES', BRANCH_CODE), "/index.php");

class CATEGORY_BRAND_COLOR_SIZE_REPORT extends Module{
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
		global $con, $smarty, $sessioninfo, $con_multi, $appCore;
		
		if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

		$this->init_selection();
		if(BRANCH_CODE == 'HQ'){
			$this->branch_id = $_REQUEST['branch_id'];
		}else{
            $this->branch_id[] = mi($sessioninfo['branch_id']);
		}   
		
		$this->date_from = $_REQUEST['from'];
		$this->date_to = $_REQUEST['to'];
		
        $this->cat_id  = mi($_REQUEST['cat_id']);
        $this->sku_type = $_REQUEST['sku_type'];
                
		parent::__construct($title);
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo, $config, $con_multi;
	    
        if (!$_REQUEST['to']) $_REQUEST['to'] = date('Y-m-d');
		if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d', strtotime("-1 month"));
		
		// branch
		$con_multi->sql_query("select * from branch where active=1 and id>0 order by sequence,code");
		while($r = $con_multi->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('branches',$this->branches);
		
		if ($config['report_price_range']) $smarty->assign('price_range',$config['report_price_range']);
		
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
			order by branch.sequence, branch.code");
			while($r = $con_multi->sql_fetchassoc()){
		        $this->branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		        $this->branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
			}
			$con_multi->sql_freeresult();
		}
		$smarty->assign('branches_group',$this->branches_group);
		
		// sku type
		$con_multi->sql_query("select * from sku_type order by code");
		$smarty->assign('sku_type', $con_multi->sql_fetchrowset());
		$con_multi->sql_freeresult();
		
		// brand
		if($sessioninfo['brand_ids'])	$filter_brand = " and id in (".$sessioninfo['brand_ids'].")";
		$con_multi->sql_query("select * from brand where active=1 $filter_brand order by description");
		while($r = $con_multi->sql_fetchassoc()){
			$this->brands[$r['id']] = $r;
		}
		$con_multi->sql_freeresult();
		$smarty->assign('brands', $this->brands);
		$smarty->assign('brand_group', get_brand_group());
		
		// color
		$color_str = file_get_contents('color.txt');
		$colors = explode("\n", $color_str);
		if($colors){
			foreach($colors as $c){
				if($c = trim($c))	$this->colors[] = $c;
			}
		}
		//print_r($this->colors);
		$smarty->assign('colors', $this->colors);
		
		$size_str = file_get_contents('size.txt');
		$sizes = explode("\n", $size_str);
		if($sizes){
			foreach($sizes as $s){
				if($s = trim($s))	$this->sizes[] = $s;
			}
		}
		//print_r($this->sizes);
		$smarty->assign('sizes', $this->sizes);
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
	
	private function generate_report(){
		global $con, $config, $smarty, $sessioninfo, $con_multi, $config, $appCore;
		
		//print_r($_REQUEST);
		
		$bid_list = $this->branch_id;
		$sku_type = trim($_REQUEST['sku_type']);
		$date_from = trim($_REQUEST['from']);
		$date_to = trim($_REQUEST['to']);
		$cat_id = mi($_REQUEST['category_id']);
		$brand_ids = $_REQUEST['brand_id'];
		$brand_groups = $_REQUEST['brand_group'];
		$colors = $_REQUEST['color'];
		$sizes = $_REQUEST['size'];
		$price_range = $_REQUEST['price_range'];
		
		/*
		echo '<pre>';
		print_r($price_range);
		echo '</pre>';
		*/
		
		$report_title = array();
		
		$err = array();
		if(!$date_from || !$date_to)	$err[] = "Invalid Date.";
		else{
			if(strtotime($date_to)<strtotime($date_from))	$err[] = "Date to cannot early than date from.";
		}
		if((!is_array($bid_list) || !$bid_list) && BRANCH_CODE == "HQ")	$err[] = "Please select at least 1 branch";
		if((!is_array($brand_ids) || !$brand_ids) && (!is_array($brand_groups) || !$brand_groups))	$err[] = "Please select at least 1 brand";
		if(!is_array($colors) || !$colors)	$err[] = "Please select at least 1 color";
		if(!is_array($sizes) || !$sizes)	$err[] = "Please select at least 1 size";
		if((!is_array($price_range) || !$price_range) && $config['report_price_range'])	$err[] = "Please select at least 1 price range";
		if(!$cat_id && !$_REQUEST['all_category'])	$err[] = "Please select category.";
		
		if(!$err){
			// get category info
			$cat_info = get_category_info($cat_id);
			if(!$cat_info)	$err[] = "Invalid Category.";
			$cat_info['cat_tree_info'] = get_cat_tree_info($cat_id, $cat_info['tree_str']);
			
			// to fix if user direct click on link to load the report
			$_REQUEST['category'] = $cat_info['description'];
			$tt = '';
			if($cat_info['cat_tree_info']){
				foreach($cat_info['cat_tree_info'] as $tmp_cat){
					if($tt)	$tt .= " > ";
					$tt .= $tmp_cat['description'];
				}
			}
			
			if($tt)	$tt .= " > ";
			$tt .= $cat_info['description'];
			$_REQUEST['category_tree'] = $tt;
		}else{
			$smarty->assign('err', $err);
			return;
		}
		
		$con_multi->sql_query("select max(level) from category");
		$max_cat_lv = $con_multi->sql_fetchfield(0);
		$con_multi->sql_freeresult();
		
		$branch_code_list = array();
		foreach($bid_list as $row=>$bid){
			$branch_code_list[] = get_branch_code($bid);
		}
		
		$report_title[] = "Branch: ".join(",", $branch_code_list);
		$report_title[] = "SKU Type: ".($sku_type ? $sku_type : 'All');
		$report_title[] = "Date: $date_from to $date_to";
		$cat_info['description'] = $_REQUEST['all_category'] ? 'All' : $cat_info['description'];
		$report_title[] = "Category: ".$cat_info['description'];
		
		
		$filter = array();
		$filter[] = "s.date between ".ms($date_from)." and ".ms($date_to);
		
		foreach ($brand_groups as $bgid) {
			$tmp_bgids = process_brand_id('brandgroup'.$bgid);
			foreach ($tmp_bgids as $tmp_bgid) $brand_ids[] = $tmp_bgid;
		}
		
		$filter[] = "sku.brand_id in (".join(',', $brand_ids).")";
		if ($cat_id) $filter[] = "cc.p".$cat_info['level']."=$cat_id";
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
		
		//////////// price range ////////////
		if ($config['report_price_range']) {
			$str_pr = '';
			foreach ($price_range as $pr) {
				$pr1 = explode('-',$pr);
				$filter_pr_i[] .= 'round(s.amount/s.qty,2) >= '.$pr1[0];
				if ($pr1[1] != 'above') $filter_pr_i[] .= 'round(s.amount/s.qty,2) <= '.$pr1[1];
				$filter_pr[] = "(".join(' and ', $filter_pr_i).")";
				$filter_pr_i = array(); //reset
			}
			$str_pr = "(".join(' or ', $filter_pr).")";
			//print $str_pr;
			$filter[] = $str_pr;
			
			/*
			echo '<pre>';
			print_r($filter);
			echo '</pre>';
			*/
		}
		
		$filter[] = $_REQUEST['exclude_inactive_sku'] ? 'si.active=1' : '1';
		
		$filter = "where ".join(' and ', $filter);
		
		$this->data = array();
		$this->cat_info_list = array();
		foreach($bid_list as $row=>$bid){
			$sales_tbl = "sku_items_sales_cache_b".$bid;
			$next_cat_p = $max_cat_lv > $cat_info['level'] ? 'p'.($cat_info['level']+1) : 'p'.$max_cat_lv;
			
			$sql = "select s.*,sku.brand_id, cc.p3,si.size,si.color, 
					if(sku.is_fresh_market='inherit',cc.is_fresh_market,sku.is_fresh_market) as is_fresh_market, 
					$next_cat_p as next_cat_id, c.description as cname, sku.category_id as sku_cat_id
					from  $sales_tbl s
					left join sku_items si on si.id=s.sku_item_id
					left join sku on sku.id=si.sku_id
					left join category_cache cc on cc.category_id=sku.category_id
					left join category c on c.id=cc.$next_cat_p
					$filter";
			
			//print $sql."<br />";
			//print "$filter<br /><br />";
			//return;

			$con_multi= new mysql_multi();
			$q1 = $con_multi->sql_query($sql);
			
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
					$con_multi->sql_query("select id from category where root_id=$next_cat_id limit 1");
					$this->cat_info_list[$next_cat_id]['have_subcat'] = $con_multi->sql_numrows();
					$con_multi->sql_freeresult();
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
				$sales_trend['sales_trend'] = $appCore->skuManager->getSKUSalesTrend($bid, $sid);
				
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
		}
		
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
		/*
		print '<pre>';
		print_r($_REQUEST);
		print '</pre>';
		*/
	}
	
	function ajax_expand_sub(){
		global $con, $smarty;
		
		$this->generate_report();
		
		$this->display('report.category_brand_color_size.row.tpl');
	}
}

$CATEGORY_BRAND_COLOR_SIZE_REPORT = new CATEGORY_BRAND_COLOR_SIZE_REPORT('Sales Trend by Category + Brand for Colour/Size Report');
?>
