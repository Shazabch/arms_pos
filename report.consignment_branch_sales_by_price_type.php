<?php
/*
3/29/2011 5:30:50 PM Justin
- Renamed the report name.
- Modified the amount to round with 2 decimal points.
- Added row total and grand total by branches.

4/12/2011 11:29:08 AM Justin
- Added use custom to show report in different format.
  => Added "Region" field and sum up base on this field.
  => Created sorting for price type by ascending order.
  => Added the feature to show in different format while view in use/non custom format.
  
4/28/2011 4:07:34 PM Andy
- Change report generate script to fix rounding different with consignment invoice summary.

5/3/2011 6:20:34 PM Andy
- Fix report if group by region need to sort by region.

5/12/2011 2:50:31 PM Alex
- add use hq cost => load_report()

5/19/2011 10:21:30 AM Andy
- Change region array structure and its related module.

6/13/2011 3:22:14 PM Alex
- remove hq_cost => load_report()

6/17/2011 3:46:47 PM Andy
- Add show gross sales.
- Fix region total bugs.
- Fix custom region if untick "Group by Region" it will still group by region.

7/6/2011 12:25:08 PM Andy
- Change split() to use explode()

4/20/2012 10:04:32 AM Justin
- Added to use consignment invoice item's price type instead of using price_type_id to seek out price type code.

11/2/2012 11:49:00 PM Fithri
- enhance to show report by monthly

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('CON_VIEW_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'CON_VIEW_REPORT', BRANCH_CODE), "/index.php");
include_once('consignment.include.php');

class CONSIGNMENT_BRANCH_SALES_BY_PRICE_TYPE extends Module{
    var $branches_group = array();  // use to hold all branches group data
	var $branches = array();    // use to hold all branches data
	var $data = array();
	var $total = array();
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;
		$this->USE_CUSTOM = defined('USE_CUSTOM') ? 1 : 0;
		$smarty->assign("USE_CUSTOM", $this->USE_CUSTOM);
		$this->init_selection();

		parent::__construct($title);
	}
	
	private function init_selection(){
		global $con, $smarty, $sessioninfo;
		
        $this->branches_group = load_branch_group();
        $this->branches = load_branch();
        
        $smarty->assign('branches_group', $this->branches_group);
	}
	
	function _default(){
	    global $con, $smarty, $sessioninfo;
	    
	    if (!isset($_REQUEST['date_to'])) $_REQUEST['date_to'] = date('Y-m-d');
		if (!isset($_REQUEST['date_from'])) $_REQUEST['date_from'] = date('Y-m-d', strtotime("-1 month"));
		
		if($_REQUEST['load_report']){
			$this->load_report();
		}
		
	    $this->display();
	}
	
	private function load_report(){
	    global $con, $smarty, $sessioninfo, $config;
	    
	    $filter = array();
	    $date_from = trim($_REQUEST['date_from']);
	    $date_to = trim($_REQUEST['date_to']);
	    $branch_id = mi($_REQUEST['branch_id']);
	    $use_region = mi($_REQUEST['use_region']);
	    $group_by_date = isset($_REQUEST['group_by_date']);
	    
	    // checking parameters
		$bid_list = array();
		if($branch_id>0){   // selected single branch
            $bid_list[] = $branch_id;
            $report_header[] = "Branch: ".$this->branches[$branch_id]['code'];
		}else{
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					$bid_list[] = $r['id'];
				}
				$con->sql_freeresult($q1);
				$report_header[] = "Region: ".$Region;
			}elseif($branch_id<0){   // negative branch id is branch group
                $bgid = abs($branch_id);
				if(!$this->branches_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($this->branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
					}
					$report_header[] = "Branch Group: ".$this->branches_group['header'][$bgid]['code'];
				}
			}else{  // all branches
				foreach($this->branches as $b){
                    $bid_list[] = $b['id'];
				}
				$report_header[] = "Branch: All";
			}
		}

        if(!$date_from || !$date_to)    $err[] = "Please select date.";
	    elseif(strtotime($date_from)>strtotime($date_to))   $err[] = "Date to cannot early than date from";
        $report_header[] = "Date: $date_from to $date_to";
	    if($err){   // got error
			$smarty->assign('err', $err);
			return false;
		}

		if($group_by_date) {
			$order_type = "ci.ci_date,branch.region, branch.code";	
		} elseif($this->USE_CUSTOM && $config['masterfile_branch_region']){
			//$select = ", if(branch.region is not null and branch.region != '', branch.region, 'Others') as region";
			//$order_type = "if(branch.region is not null and branch.region != '', branch.region, 'Others'), tdt.code";
			$order_type = "branch.region, branch.code";
		}else{
			//$order_type = "branch.code, tdt.code";
			$order_type = "branch.code";
		}
		/*

		$sql = "select ci.discount_percent as sheet_disc_per, ci.ci_branch_id, cii.*, (cii.ctn*uom.fraction + cii.pcs) as qty,tdt.code as price_type, si.sku_item_code, si.description,uom.code as uom_code, uom.fraction as uom_fraction $select
from ci
left join ci_items cii on cii.branch_id=ci.branch_id and cii.ci_id=ci.id
left join sku_items si on si.id=cii.sku_item_id
left join uom on uom.id=cii.uom_id
left join trade_discount_type tdt on tdt.id=cii.price_type_id
left join branch on branch.id=ci.ci_branch_id
where ci.ci_branch_id in (".join(',', $bid_list).") and ci.ci_date between ".ms($date_from)." and ".ms($date_to)." and ci.type='sales' and ci.active=1 and ci.status=1 and ci.approved=1 and ci.export_pos=1
order by $order_type";
		$q1 = $con->sql_query($sql);
		$price_type_arr = array();
		//print $sql;
		while($r = $con->sql_fetchassoc($q1)){
		    $sheet_disc_per = array();
			$ci_bid = mi($r['ci_branch_id']);
			$price_type = trim($r['price_type']);
			$qty = $r['qty'];
			$amt = round($r['qty'] * $r['cost_price'], 2);
			
			// check item discount
			if($r['discount'])	$r['disc_arr'] = split("[+]", $r['discount']);
			if($r['disc_arr'][0]){   // got first discount
				$amt = $amt - round($amt*$r['disc_arr'][0]/100, 2);
			}
			if($r['disc_arr'][1]){   // got second discount
				$amt = $amt - round($amt*$r['disc_arr'][1]/100, 2);
			}
			
			// check sheet discount
			if($r['sheet_disc_per']){  // got sheet discount
            	$sheet_disc_per = split("[+]", $r['sheet_disc_per']);
			}

			if($sheet_disc_per[0]){   // got first discount
	            $disc_amt = round($amt*$sheet_disc_per[0]/100, 2);
	            $amt -= $disc_amt;
			}
			if($sheet_disc_per[1]){   // got first discount
	            $disc_amt = round($amt*$sheet_disc_per[1]/100, 2);
	            $amt -= $disc_amt;
			}
			
			if(!in_array($price_type, $price_type_arr)) $price_type_arr[] = $price_type;
			if($r['region']){
				$this->region_title[$ci_bid]['region'] = $r['region'];

				$this->region_row_total[$r['region']]['qty'] += $qty;
				$this->region_row_total[$r['region']]['amt'] += round($amt ,2);	

				$this->region_col_total[$r['region']][$price_type]['qty'] += $qty;
				$this->region_col_total[$r['region']][$price_type]['amt'] += round($amt, 2);
			}
			$this->data[$ci_bid][$price_type]['qty'] += $qty;
			$this->data[$ci_bid][$price_type]['amt'] += round($amt, 2);

			$this->row_total[$ci_bid]['qty'] += $qty;
			$this->row_total[$ci_bid]['amt'] += round($amt ,2);

			$this->col_total[$price_type]['qty'] += $qty;
			$this->col_total[$price_type]['amt'] += round($amt, 2);

			$this->grand_total['qty'] += $qty;
			$this->grand_total['amt'] += round($amt, 2);		
		}
		$con->sql_freeresult($q1);*/
		
		$price_type_arr = array();
		//$order_type = "branch.code";
		
		$sql = "select ci.*,branch.region,ci.discount_percent as sheet_disc_per
from ci
left join branch on branch.id=ci.ci_branch_id
where ci.ci_branch_id in (".join(',', $bid_list).") and ci.ci_date between ".ms($date_from)." and ".ms($date_to)." and ci.type='sales' and ci.active=1 and ci.status=1 and ci.approved=1 and ci.export_pos=1
order by $order_type";
		//print $sql;
		$q_ci = $con->sql_query($sql);
		while($ci = $con->sql_fetchassoc($q_ci)){
			$ci_bid = mi($ci['ci_branch_id']);
			$sheet_disc_per = array();
			$price_type_total = array();
			
			if($this->USE_CUSTOM && $group_by_date) {
				//$add += 17750;
				$ci_date = date('m-Y',mi(strtotime($ci['ci_date'])+$add));
			}
				
			// check sheet discount
			if($ci['sheet_disc_per']){  // got sheet discount
            	$sheet_disc_per = explode("+", $ci['sheet_disc_per']);
			}
			
			// get ci_items
			$q_cii = $con->sql_query("select cii.*, (cii.ctn*uom.fraction + cii.pcs) as qty,ifnull(cii.price_type, tdt.code) as price_type, si.sku_item_code, si.description,uom.code as uom_code, uom.fraction as uom_fraction
from ci_items cii
left join sku_items si on si.id=cii.sku_item_id
left join uom on uom.id=cii.uom_id
left join trade_discount_type tdt on tdt.id=cii.price_type_id
where cii.branch_id=".mi($ci['branch_id'])." and cii.ci_id=".mi($ci['id']));
			while($r = $con->sql_fetchassoc($q_cii)){
				$price_type = trim($r['price_type']);
				$qty = $r['qty'];
				$gross_amt = $amt = round($r['qty'] * $r['cost_price'], 2);
				
				// check item discount
				if($r['discount'])	$r['disc_arr'] = explode("+", $r['discount']);
				if($r['disc_arr'][0]){   // got first discount
					$amt = $amt - round($amt*$r['disc_arr'][0]/100, 2);
				}
				if($r['disc_arr'][1]){   // got second discount
					$amt = $amt - round($amt*$r['disc_arr'][1]/100, 2);
				}
				
				$price_type_total[$price_type]['qty'] += $qty;
				$price_type_total[$price_type]['amt'] += $amt;
				$price_type_total[$price_type]['gross_amt'] += $gross_amt;
			}
			$con->sql_freeresult($q_cii);
			
			if($price_type_total){
				foreach($price_type_total as $price_type=>$r){
					if(!in_array($price_type, $price_type_arr)) $price_type_arr[] = $price_type;
					
					$amt = $r['amt'];
					$qty = $r['qty'];
					$gross_amt = $r['gross_amt'];
					
					if($sheet_disc_per[0]){   // got first discount
			            $disc_amt = round($amt*$sheet_disc_per[0]/100, 2);
			            $amt -= $disc_amt;
					}
					if($sheet_disc_per[1]){   // got first discount
			            $disc_amt = round($amt*$sheet_disc_per[1]/100, 2);
			            $amt -= $disc_amt;
					}
					$price_type_total[$price_type] = $amt;
					
					if ($group_by_date) {
						if($ci['region'] && $this->USE_CUSTOM && $use_region){
							$this->region_title[$ci_date][$ci_bid]['region'] = $config['masterfile_branch_region'][$ci['region']]['name'];
			
							$this->region_row_total[$ci_date][$ci['region']]['qty'] += $qty;
							$this->region_row_total[$ci_date][$ci['region']]['amt'] += round($amt ,2);	
							$this->region_row_total[$ci_date][$ci['region']]['gross_amt'] += round($gross_amt ,2);
			
							$this->region_col_total[$ci_date][$ci['region']][$price_type]['qty'] += $qty;
							$this->region_col_total[$ci_date][$ci['region']][$price_type]['amt'] += round($amt, 2);
							$this->region_col_total[$ci_date][$ci['region']][$price_type]['gross_amt'] += round($gross_amt, 2);
						}
						$this->data[$ci_date][$ci_bid][$price_type]['qty'] += $qty;
						$this->data[$ci_date][$ci_bid][$price_type]['amt'] += round($amt, 2);
						$this->data[$ci_date][$ci_bid][$price_type]['gross_amt'] += round($gross_amt, 2);
				
						$this->row_total[$ci_date][$ci_bid]['qty'] += $qty;
						$this->row_total[$ci_date][$ci_bid]['amt'] += round($amt ,2);
						$this->row_total[$ci_date][$ci_bid]['gross_amt'] += round($gross_amt ,2);
						
						$this->col_total[$ci_date][$price_type]['qty'] += $qty;
						$this->col_total[$ci_date][$price_type]['amt'] += round($amt, 2);
						$this->col_total[$ci_date][$price_type]['gross_amt'] += round($gross_amt, 2);
			
						$this->grand_total[$ci_date]['qty'] += $qty;
						$this->grand_total[$ci_date]['amt'] += round($amt, 2);
						$this->grand_total[$ci_date]['gross_amt'] += round($gross_amt, 2);
						
						$this->data[$ci_date][$ci_bid]['region_code'] = $ci['region'];
					}
					 else {
						if($ci['region'] && $this->USE_CUSTOM && $use_region){
							$this->region_title[$ci_bid]['region'] = $config['masterfile_branch_region'][$ci['region']]['name'];
			
							$this->region_row_total[$ci['region']]['qty'] += $qty;
							$this->region_row_total[$ci['region']]['amt'] += round($amt ,2);	
							$this->region_row_total[$ci['region']]['gross_amt'] += round($gross_amt ,2);
			
							$this->region_col_total[$ci['region']][$price_type]['qty'] += $qty;
							$this->region_col_total[$ci['region']][$price_type]['amt'] += round($amt, 2);
							$this->region_col_total[$ci['region']][$price_type]['gross_amt'] += round($gross_amt, 2);
						}
						$this->data[$ci_bid][$price_type]['qty'] += $qty;
						$this->data[$ci_bid][$price_type]['amt'] += round($amt, 2);
						$this->data[$ci_bid][$price_type]['gross_amt'] += round($gross_amt, 2);
				
						$this->row_total[$ci_bid]['qty'] += $qty;
						$this->row_total[$ci_bid]['amt'] += round($amt ,2);
						$this->row_total[$ci_bid]['gross_amt'] += round($gross_amt ,2);
						
						$this->col_total[$price_type]['qty'] += $qty;
						$this->col_total[$price_type]['amt'] += round($amt, 2);
						$this->col_total[$price_type]['gross_amt'] += round($gross_amt, 2);
			
						$this->grand_total['qty'] += $qty;
						$this->grand_total['amt'] += round($amt, 2);
						$this->grand_total['gross_amt'] += round($gross_amt, 2);
						
						$this->data[$ci_bid]['region_code'] = $ci['region'];
					}
					
				}
			}			
		}
		$con->sql_freeresult($q_ci);

		/*
		echo '<pre>';
		print_r($this->data);
		echo '</pre>';
		*/
		
		$smarty->assign('data', $this->data);
		$smarty->assign('row_total', $this->row_total);
		$smarty->assign('col_total', $this->col_total);
		$smarty->assign('grand_total', $this->grand_total);
		$smarty->assign('group_by_date', $group_by_date);
		
		if($this->region_title && $use_region){
			//print_r($this->region_col_total);
			$smarty->assign('use_region', $use_region);
			$smarty->assign('region_title', $this->region_title);
			$smarty->assign('region_col_total', $this->region_col_total);
			$smarty->assign('region_row_total', $this->region_row_total);
		}

		if($price_type_arr) asort($price_type_arr);

		$smarty->assign('price_type_arr', $price_type_arr);
		$smarty->assign('report_header', join('&nbsp;&nbsp;&nbsp;&nbsp;', $report_header));
	}
}

$CONSIGNMENT_BRANCH_SALES_BY_PRICE_TYPE = new CONSIGNMENT_BRANCH_SALES_BY_PRICE_TYPE('Branch Sales by Price Type Report');
?>
