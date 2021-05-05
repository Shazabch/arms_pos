<?
/*
+++++++++++++++++
REVISION HISTORY
+++++++++++++++++

gary 7/19/2007 4:50:36 PM
- add dropdown/filter sku_type and status.
- sku type call from table sku_type in DB.

8/7/2007 5:06:38 PM yinsee
- move sel() function to common.php

10/25/2010 12:21:45 PM Justin
- Added the total of item not in SKU row.
- Modified the grand total row to include the total of item not in SKU.
- Fixed ajax call the wrong hyperlink for showing SKU.
- Fixed various of bugs that causing not tally with GRA Listing and Department.
- Added the missing of status filter.

10/29/2010 6:06:07 PM Alex
- fix compare timestamp date bugs

11/1/2010 11:51:10 AM Alex
- change use templates when generate tables
- fix bugs display no data for sku if select all branch
- add show cost privilege

6/24/2011 4:18:03 PM Andy
- Make all branch default sort by sequence, code.

7/6/2011 11:46:12 AM Andy
- Change split() to use explode()

9/15/2011 3:45:43 PM Justin
- Fixed the bugs where GRA missing by branch if filter branch as "All".
- Modified the status as below:
  => Saved - for those saved in GRA.
  => Completed (Not Returned) - for those already printed out the checklsit and awaiting for return.
  => Returned - for those already confirmed to return.
  
4/18/2012 1:23:07 PM Alex
- fix mysql bugs error when show item with status returned

4/19/2012 9:48:59 AM Alex
- fix returned bugs while filtering => generate_sku_table()
- fix branch no check active status

7/19/2012 2:41 PM Andy
- Fix sql error when click on category link.

7/9/2013 5:53 PM Justin
- Enhanced to  have checking on approved = 1.

4/21/2015 3:11 PM Justin
- Enhanced to have GST information.

11/30/2015 9:43 PM DingRen
- when calculate amount_gst, gst and amount and change to decimal 2 for extra

02/29/2016 09:46 Edwin
- Bugs fixed on status filter in GRA summary

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/12/2016 13:30 Edwin
- Changed to new module

6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

8/2/2017 2:24 PM Justin
- Bug fixed on GST information will not show out when there is no valid GRA items but have items not in ARMS SKU.

10/24/2017 4:23 PM Justin
- Bug fixed on system will not trigger any GRA if selecting same date for both date from/to.

5/8/2018 1:16 PM Justin
- Enhanced to have foreign currency feature.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRA_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRA_REPORT', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

class GRA_SUMMARY_BY_CATEGORY extends Module{
    function __construct($title) {
        $this->init_selection();
        parent::__construct($title);
    }
    
    function init_selection() {
        global $con, $config, $smarty, $sessioninfo;
        
        $form = $branch_list = $sku_type_list = $status_list = array();
        
        //retrieve branch list
		$b = $con->sql_query("select id, code from branch where active = 1 order by sequence, code");
		while ($data = $con->sql_fetchassoc($b)) {
			$branch_list[$data['id']] = $data['code'];
		}
		$con->sql_freeresult($b);
		$smarty->assign('branch_list', $branch_list);
        
        //retrieve SKU type list
        $st = $con->sql_query("select code from sku_type where active=1 order by code");
        while ($data = $con->sql_fetchassoc($st)) {
            $sku_type_list[] = $data['code'];
        }
        $con->sql_freeresult($st);
        $smarty->assign('sku_type_list', $sku_type_list);
        
        //GRA Status list
        $status_list[1] = "Saved & Waiting Approval";
        $status_list[2] = "Approved";
        $status_list[3] = "Completed";
        $status_list[4] = "Un-checkout";
        $smarty->assign('status_list', $status_list);
        
        $form['date_from'] = date('Y-m-d', strtotime('-30 day', time()));
		$form['date_to'] = date('Y-m-d');
        $smarty->assign('form', $form);
    }
    
    function _default() {
        $this->display();
    }
    
    function show_report(){
        global $smarty;
		$this->form = $_REQUEST;
        
        $this->generate_report();
        
        $this->form['form_submit'] = 1;
		$smarty->assign('form',$this->form);
		$this->display();
    }
    
    function generate_report() {
        global $con, $smarty, $sessioninfo, $config;
        
        $filter = $err = array();
        
		if (strtotime($this->form['date_from']) > strtotime($this->form['date_to'])) {
			$err[] = 'Date From cannot be later than Date To';
		}
		
		if($err) {
			$smarty->assign('err', $err);
			return;
		}
		      
		if(BRANCH_CODE == 'HQ'){
			if($this->form['branch_id'])    $filter[] = "gra.branch_id = ".$this->form['branch_id'];
		}else{
			$filter[] = "gra.branch_id = ".$sessioninfo['branch_id'];
		}
        
        $filter[] = "gra.dept_id in (".join(",", array_keys($sessioninfo['departments'])).")";
		
		//$date_to = date("Y-m-d", strtotime('+1 day', strtotime($this->form['date_to'])));
		$date_to = $this->form['date_to']." 23:59:59";
		
        if($this->form['status']) {
            switch($this->form['status']) {
                case 1: // is saved and waiting approval
                    $filter[] = "gra.status in (0,2) and gra.approved = 0 and gra.returned = 0";
                    $filter[] = "gra.added between ".ms($this->form['date_from'])." and ".ms($date_to);
                    break;
                case 2: // is approved
                    $filter[] = "gra.status = 0 and gra.approved = 1 and gra.returned = 0";	
                    $filter[] = "gra.added between ".ms($this->form['date_from'])." and ".ms($date_to);
                    break;
                case 3: // is completed
                    $filter[] = "gra.status = 0 and gra.approved = 1 and gra.returned = 1";
                    $filter[] = "gra.return_timestamp between ".ms($this->form['date_from'])." and ".ms($date_to);
                    break;
				case 4: // is un-checkout
					$filter[] = "gra.status in (0,2) and gra.returned = 0";
					$filter[] = "gra.added between ".ms($this->form['date_from'])." and ".ms($date_to);
                    break;
            }
        }else {
            $filter[] = "((gra.status in (0,2) and gra.returned = 0 and gra.approved in (0,1) and gra.added between ".ms($this->form['date_from'])." and ".ms($date_to).") or (gra.status = 0 and gra.returned = 1 and gra.approved = 1 and gra.return_timestamp between ".ms($this->form['date_from'])." and ".ms($date_to)."))";
        }
        
        if($this->form['sku_type']) {
            $filter[] = "gra.sku_type = ".ms($this->form['sku_type']);
        }
        
        if($sessioninfo['level']<9999) $filter[] = "c.p2 in (".$sessioninfo['department_ids'].")";
        
		$root_id = intval($this->form['root_id']);
		
		if($root_id == 0)	$pf = "p1";
		else {
			$con->sql_query("select * from category where id = $root_id");
			$cat_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$pf = "p".($cat_info['level']+1);
			$filter[] = "p".($cat_info['level'])." = $root_id";
			
			if($cat_info['tree_str']!=''){  // generate category tree
			    $tree_str = $cat_info['tree_str'];
				$temp = str_replace(")(", ",",  str_replace("(0)", "", $tree_str));
				if($temp){
                    $con->sql_query("select id, description from category where id in $temp order by level");
                    while ($r = $con->sql_fetchassoc()){
                        $cat_info['cat_tree_info'][] = $r;
					}
				}
			}
		}
		
        if ($filter) $filter = "where ".join(' and ', $filter);
		
		$data = $existed_gra = array();
		$is_under_gst = $have_fc = 0;
		
		$con->sql_query("select id, description from category where root_id = $root_id or id = $root_id");
		while($r = $con->sql_fetchrow()) {
			$category[$r['id']] = $r['description'];
		}
	
		$q = $con->sql_query("select $pf as cat_id, sum(gi.qty) as qty, sum(gi.amount) as amt, sum(gi.gst) as gst, date(gra.return_timestamp) as dt, gra.extra, gra.id as gra_id, gra.branch_id, gra.is_under_gst, gra.sku_type, gra.currency_code, gra.currency_rate
							 from gra
							 left join gra_items gi on gra_id=gra.id and gi.branch_id=gra.branch_id
							 left join sku_items on gi.sku_item_id = sku_items.id
							 left join sku on sku_items.sku_id = sku.id
							 left join category_cache c on sku.category_id = c.category_id
							 $filter
							 group by $pf, dt, gra.id, gra.branch_id
							 order by $pf, dt") or die(mysql_error());
		
		while($r = $con->sql_fetchassoc($q)) {
			if(!$existed_gra[$r['gra_id']][$r['branch_id']]) {
				$extra = unserialize($r['extra']);
				if($extra) {
					$uq_cols[$r['dt']] = 1;
					foreach($extra['code'] as $idx=>$code){
						$qty = $extra['qty'][$idx];
						$cost = $extra['cost'][$idx];
						$extotal[$r['dt']]['extra_qty'] += $qty;
						$tb_extotal['extra_qty'] += $qty;
						$row_extra_amt = round($qty * $cost, 2);
						if($r['currency_code']) $row_extra_amt = round($row_extra_amt * $r['currency_rate'], 2);
						$extotal[$r['dt']]['extra_amt'] += $row_extra_amt;
						$tb_extotal['extra_amt'] += $row_extra_amt;
						
						// sum up gst amount if found is under gst
						if($r['is_under_gst']){
							$gst_rate = $extra['gst_rate'][$idx];
							
							$row_gst_amt = round($row_extra_amt * ((100+$gst_rate)/100),2);
							$row_extra_gst = $row_gst_amt-$row_extra_amt;
	
							$extotal[$r['dt']]['extra_gst'] += $row_extra_gst;
							$tb_extotal['extra_gst'] += $row_extra_gst;				
							$extotal[$r['dt']]['extra_gst_amt'] += $row_gst_amt;
							$tb_extotal['extra_gst_amt'] += $row_gst_amt;
							$is_under_gst = 1;
						}elseif($r['currency_code']){
							$extotal[$r['dt']]['extra_gst_amt'] += $row_extra_amt;
							$tb_extotal['extra_gst_amt'] += $row_extra_amt;
						}
					}
				}
				$existed_gra[$r['gra_id']][$r['branch_id']] = 1;
			}
			
			if(!$r['qty'])	continue;
			
			$cid = $r['cat_id'];
			$date = $r['dt'];
			
			$qty = $r['qty'];
			$amt = round($r['amt'], 2);
			$gst = round($r['gst'], 2);
			
			if($r['currency_code']){
				$amt = round($amt * $r['currency_rate'], 2);
				$have_fc = 1;
			}
			
			$data[$cid][$date]['amt'] += $amt;
			$data[$cid][$date]['qty'] += $qty;
			$data[$cid]['total']['amt'] += $amt;
			$data[$cid]['total']['qty'] += $qty;
	
			$tb_day[$date]['amt'] += $amt;
			$tb_day[$date]['qty'] += $qty;
			
			if($r['is_under_gst']) {
				// total gst amt
				$data[$cid][$date]['gst'] += $gst;
				$tb_day[$date]['gst'] += $gst;
				$data[$cid]['total']['gst'] += $gst;
				$is_under_gst = 1;
			}
			
			// total amt include gst
			$row_gst_amt = $amt + $gst;
			$data[$cid][$date]['gst_amt'] += $row_gst_amt;
			$tb_day[$date]['gst_amt'] += $row_gst_amt;
			$data[$cid]['total']['gst_amt'] += $row_gst_amt;
			
			$uq_cols[$r['dt']] = 1;
		}
		$con->sql_freeresult($q);
		
		foreach (array_keys($data) as $id) {
			$data[$id]['id'] = $id;
			if (!$category[$id]) {
				$data[$id]['have_subcat'] = false;
				$data[$id]['description'] = "Un-categorized";
			}
			else {
				$data[$id]['have_subcat'] = $this->check_subcat($id);
				$data[$id]['description'] = $category[$id];
			}
		}
		
		if($uq_cols) {
			ksort($uq_cols);
			reset($uq_cols);

			foreach($uq_cols as $dt => $dummy) {
				$temp = array();
				list($y, $m, $d) = explode("-", $dt);
				$temp['y'] = $y;
				$temp['m'] = $m;
				$temp['d'] = $d;
				$uq_cols[$dt] = $temp;
	
				//total per day
				$total_day[$dt]['qty'] += $tb_day[$dt]['qty'] + $extotal[$dt]['extra_qty'];
				$total_day[$dt]['amt'] += $tb_day[$dt]['amt'] + $extotal[$dt]['extra_amt'];
				$total_day[$dt]['gst'] += $tb_day[$dt]['gst'] + $extotal[$dt]['extra_gst'];
				$total_day[$dt]['gst_amt'] += $tb_day[$dt]['gst_amt'] + $extotal[$dt]['extra_gst_amt'];
				
				$final_total['qty'] += $tb_day[$dt]['qty'] + $extotal[$dt]['extra_qty'];
				$final_total['amt'] += $tb_day[$dt]['amt'] + $extotal[$dt]['extra_amt'];
				$final_total['gst'] += $tb_day[$dt]['gst'] + $extotal[$dt]['extra_gst'];
				$final_total['gst_amt'] += $tb_day[$dt]['gst_amt'] + $extotal[$dt]['extra_gst_amt'];
			}
		}
		
		$smarty->assign('data', $data);
		$smarty->assign('root_cat_info', $cat_info);
		$smarty->assign('uq_cols', $uq_cols);
		$smarty->assign('extotal', $extotal);
		$smarty->assign('tb_extotal', $tb_extotal);
		$smarty->assign('total_day', $total_day);
		$smarty->assign('final_total', $final_total);
		$smarty->assign('is_under_gst', $is_under_gst);
		$smarty->assign('have_fc', $have_fc);
    }
	
	function generate_sku_table() {
		global $con, $sessioninfo, $smarty;
		
		$form = $_REQUEST;
		$root_id = intval($form['root_id']);
		
		if ($root_id == 0)	$filter[] = "sku.category_id = 0";
		else {
			$con->sql_query("select id, level, description from category where id = $root_id");
			$root = $con->sql_fetchrow();
			$filter[] = "c.p".$root['level']." = $root_id";
		}
		
		if(BRANCH_CODE == 'HQ'){
			if($form['branch_id'])    $filter[] = "gi.branch_id = ".intval($form['branch_id']);
		}else{
			$filter[] = "gi.branch_id = ".$sessioninfo['branch_id'];
		}
		
		$filter[] = "gra.dept_id in (".join(",", array_keys($sessioninfo['departments'])).")";
	
		//$date_to = date("Y-m-d", strtotime('+1 day', strtotime($form['date_to'])));
		$date_to = $form['date_to']." 23:59:59";
		
		if($form['status']) {
            switch($form['status']) {
                case 1: // is saved and waiting approval
                    $filter[] = "gra.status in (0,2) and gra.approved = 0 and gra.returned = 0";
                    $filter[] = "gra.added between ".ms($form['date_from'])." and ".ms($date_to);
                    break;
                case 2: // is approved
                    $filter[] = "gra.status = 0 and gra.approved = 1 and gra.returned = 0";	
                    $filter[] = "gra.added between ".ms($form['date_from'])." and ".ms($date_to);
                    break;
                case 3: // is completed
                    $filter[] = "gra.status = 0 and gra.approved = 1 and gra.returned = 1";
                    $filter[] = "gra.return_timestamp between ".ms($form['date_from'])." and ".ms($date_to);
                    break;
				case 4: // is un-checkout
					$filter[] = "gra.status in (0,2) and gra.returned = 0";
                    $filter[] = "gra.added between ".ms($form['date_from'])." and ".ms($date_to);
                    break;
            }
        }else {
            $filter[] = "((gra.status in (0,2) and gra.returned = 0 and gra.approved in (0,1) and gra.added between ".ms($form['date_from'])." and ".ms($date_to).") or (gra.status = 0 and gra.returned = 1 and gra.approved = 1 and gra.return_timestamp between ".ms($form['date_from'])." and ".ms($date_to)."))";
        }
	
		// lock user allowed department
		if($sessioninfo['level']<9999)	$filter[] = "c.p2 in (".$sessioninfo['department_ids'].")";
		if($filter)	$filter = "where ".join(' and ', $filter);
	
		$tb = array();
		$is_under_gst = 0;
	
		$q1 = $con->sql_query("select si.sku_item_code, si.description, sum(gi.qty) as qty, sum(gi.amount) as amt, 
							   date(gra.return_timestamp) as dt, count(if(gi.batchno=0, gi.id, null)) as not_allow_checkout,
							   gra.is_under_gst, sum(gi.gst) as gst
							   from gra_items gi
							   left join gra on gi.gra_id=gra.id and gi.branch_id=gra.branch_id
							   left join sku_items si on gi.sku_item_id = si.id 
							   left join sku on si.sku_id = sku.id 
							   left join category_cache c on sku.category_id = c.category_id 
							   $filter 
							   group by sku_item_code, dt, gra.id, gra.branch_id
							   order by sku_item_code, dt") or die(mysql_error());
		
		while($t = $con->sql_fetchassoc($q1)) {
			$qty = $t['qty'];
			$amt = round($t['amt'], 2);
			$gst = round($t['gst'], 2);
			
			$tb[$t['sku_item_code']][$t['dt']]['qty'] += $qty;
			$tb[$t['sku_item_code']][$t['dt']]['amt'] += $amt;
	
			$tb[$t['sku_item_code']]['total']['qty'] += $qty;
			$tb[$t['sku_item_code']]['total']['amt'] += $amt;
			
			//total per day
			$total_day[$t['dt']]['qty'] += $qty;
			$total_day[$t['dt']]['amt'] += $amt;
	
			$final_total['qty'] += $qty;
			$final_total['amt'] += $amt;
			
			$tb[$t['sku_item_code']]['description'] = $t['description'];
			$uq_cols[$t['dt']] = 1;
				
			// total amt include gst
			if($t['is_under_gst']){
				$tb[$t['sku_item_code']][$t['dt']]['gst'] += $gst;
				$tb[$t['sku_item_code']]['total']['gst'] += $gst;
				$total_day[$t['dt']]['gst'] += $gst;
				$final_total['gst'] += $gst;
				$is_under_gst = 1;
			}
			
			$row_gst_amt = $amt + $gst;
			$tb[$t['sku_item_code']][$t['dt']]['gst_amt'] += $row_gst_amt;
			$tb[$t['sku_item_code']]['total']['gst_amt'] += $row_gst_amt;
			$total_day[$t['dt']]['gst_amt'] += $row_gst_amt;
			$final_total['gst_amt'] += $row_gst_amt;
		}
		$con->sql_freeresult($q1);
		
		if($uq_cols) {
			ksort($uq_cols);
			reset($uq_cols);
		
			foreach($uq_cols as $dt => $dummy) {
				$temp = array();
				list($y, $m, $d) = explode("-", $dt);
				$temp['y'] = $y;
				$temp['m'] = $m;
				$temp['d'] = $d;
				$uq_cols[$dt] = $temp;
			}
		}
		
		$smarty->assign('tb', $tb);
		$smarty->assign('uq_cols', $uq_cols);
		$smarty->assign('total_day', $total_day);
		$smarty->assign('final_total', $final_total);
		$smarty->assign('is_under_gst', $is_under_gst);
		$smarty->assign('category_name', $root['description']);
		$smarty->display('goods_return_advice.summary_by_category.sku.tpl');
	}
	
	function check_subcat($id) {
		global $con;
		$con->sql_query("select count(*) from category where root_id = $id");
		$c = $con->sql_fetchrow();
		if($c[0] > 0) return true;
		return false;
	}
}

$GRA_SUMMARY_BY_CATEGORY = new GRA_SUMMARY_BY_CATEGORY('GRA Summary by Category');
?>