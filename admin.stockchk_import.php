<?php
/* 
REVISION HISTORY
================
12/12/2007 1:57:54 PM gary
- add "having cost > 0" in get_cost (ignore grn with zero cost)

4/2/2008 2:19:24 PM 
standardize import format
- arms_code, mcode/link_code, count_by, location, shelf, item_no, qty, selling, cost


5/26/2008 2:04:31 PM yinsee
- use $config['stock_take_cost'] = 'grn'; // (grn or avg) to control costing

2/5/2009 5:16:48 PM yinsee
- add fill_zero option

5/7/2009 12:00 PM Jeff
- fix edit database delete bugs

22/7/2009 12:39:56 PM yinsee
- delete all qty from the same shelf+location when re-import

6/8/2009 5:56:20 PM yinsee
- stock take cost use grn as default 

20/8/2009 1:40:06 PM yinsee
- export PSPV handheld

9/3/2009 5:21:30 PM yinsee
- add stock check allow HQ for consingment

15/10/2009 3:42:37 PM yinsee
- set changed=1 when manually add item

21/10/2009 12:49:32 PM yinsee
- delete row in "edit"  will update inventory

3/26/2010 11:08:18 AM Andy
- Add new stock check import format (ARMS Code, Qty)

4/30/2010 5:20:20 PM Andy
- Add new stock check import format (Art No, Qty)

5/21/2010 11:35:17 AM yinsee
- enhance description of import type (CSV, arms_code)

10/8/2010 2:03:59 PM Andy
- Fix bugs when reimport stock check, those items get deleted but no insert new stock will not triggle system to update inventory.
- further fix sql error.

10/14/2010 12:29:46 PM Alex
- add branch and date filter while edit database

10/21/2010 4:28:38 PM Andy
- turn off clean up zero qty query.

11/2/2010 6:01:24 PM Alex
- Add checking on inserted data while save

6/24/2011 3:15:20 PM Andy
- Make all branch default sort by sequence, code.

7/5/2011 1:32:09 PM Andy
- Change split() to use explode()

10/20/2011 10:08:42 AM Andy
- Add checking and block user to import if sku item code not found.

2/17/2012 4:06:53 PM Alex
- add multiple delete records

4/3/2012 5:41:05 PM Alex
- add show artno when import with artno and qty

5/8/2012 6:10:53 PM Andy
- Fix stock check after delete record no recalculate cost and stock balance.

4/3/2013 11:31 AM Andy
- Remove the check code printing result.

4/11/2013 3:09 PM Andy
- Change after import stock take, it will get the sku item id and set changed=1 instead of using sub query.
- Add show what code is not found when import stock take.

8/23/2013 2:22 PM Justin
- Bug fixed on system will still store the stock check data even though the SKU Item Code or MCode/Link Code is invalid.

10/7/2013 3:54 PM Fithri
- Fixed bug when deleting / updating record : happen if SKU location or shelf contain comma (change separator from comma to #)

10/9/2013 5:29 PM Fithri
- improve php & html form for updating & deleting record
- add log function for stock check import

11/19/2013 5:18 PM Justin
- Enhanced to capture logs for total rows for delete and insert.

3/5/2014 10:48 AM Andy
- Fix and improve import stock take result page.

3/6/2014 3:43 PM Andy
- Limit max length for stock take input box.

4/29/2014 3:10 PM Justin
- Bug fixed on system will not skip those row from CSV file that contains empty SKU code.

3/16/2015 11:06 AM Justin
- Enhanced current format "CSV / ARGOS" to include description and selling price.
- Added new export format "CSV" (format is exactly the same as import format "CSV / ARGOS").

08/02/2016 11:00 Edwin
- optimized find_arms_code function

08/11/2016 16:30 Edwin
- change to new coding structure

10/24/2016 11:10 AM Andy
- Fix find_arms_code function bug.

11/11/2016 10:37 AM Andy
- Added new format (ARMS Code/MCode/Old Code, Qty).
- Enhanced find_arms_code() to able to pass in filter_fields.

3/10/2017 4:12 PM Justin
- Enhanced to check fresh market SKU, shows error message if user trying to import child fresh market item.
- Enhanced to auto zerolise child fresh market item when found the parent fresh market SKU is imported.

3/28/2017 10:53 AM Justin
- Bug fixed on qty always zero while import.

4/21/2017 8:58 AM Qiu Ying
- Bug fixed on showing Branch Selection when already in Branch

5/30/2017 10:12 AM Justin
- Bug fixed on sub sku items will always inserted.

7/31/2017 3:25 PM Justin
- Enhanced to have cost price checking when customer is using Average Cost with parent-child or Last GRN Cost with parent-child calculation.

8/9/2017 11:15 AM Justin
- Enhanced to have more options on the fill zero feature.
- Enhanced to check against import file to show error message while user did not use fill zero options and never stock take for whole SKU family.

2/10/2020 3:51 PM William
- Enhanced to capture log when users edit, add, export data.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999 && !privilege('POS_IMPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_IMPORT', BRANCH_CODE), "/index.php");

set_time_limit(0);
ini_set('memory_limit','256M');

class STOCK_CHECK_IMPORT extends MODULE{
    function __construct($title) {
        $this->init();
 		parent::__construct($title);
	}
    
    function _default() {
		$this->display();
	}
    
    function init() {
        global $config, $con, $smarty;
        
        $branch_list = array();
        if($config['consignment_module']) $filter = "and code <> 'HQ'";
        $con->sql_query("select code, id from branch where active=1 $filter");
        while($r = $con->sql_fetchassoc()) {
            $branch_list[$r['id']] = $r['code'];
        }
        $con->sql_freeresult();
        
        $form['cut_off_date'] = date('Y-m-d');
        $smarty->assign('branch_list', $branch_list);
        $smarty->assign('form', $form);
        
        $search_type = array("sku_item_code" => "SKU Item Code",
                             "shelf_no" => "Shelf No",
                             "location" => "Location",
                             "scanned_by" => "Scanned By");
        $smarty->assign('search_type', $search_type);
    }
    
    function import_data() {
        global $con, $smarty, $sessioninfo, $LANG;
        
		$form = $_REQUEST;
        $bid = intval($form['branch_id']);
		$err = $this->validate_data();
        
        $rows_deleted = $rows_inserted = 0;
        if($err){
            // got error
            $smarty->assign('err', $err);
            $this->display();
        }else{	// no error
            $item_no = 0;
            $q1 = $con->sql_query("select sc.*, si.id as sid, si.sku_id, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market 
                                  from tmpo_stock_check sc
                                  join sku_items si on si.sku_item_code=sc.sku_item_code
								  left join sku on sku.id = si.sku_id
								  left join category_cache cc on cc.category_id=sku.category_id");

            $sid_list = $sku_id_list = array();
            while($r = $con->sql_fetchassoc($q1)){
                $rows_deleted += $this->clear_shelf_data($bid, $form['cut_off_date'], $r, $succ);
                $sid = mi($r['sid']);
                $sku_id = mi($r['sku_id']);
				
				// need to store the sku list for fill parent usage
				if($form['fill_zero_options'] == "fill_parent") $sku_id_list[$sku_id] = $sku_id;
                
                if($form['import_type'] == 'artno_stock'){
                    $succ[] = $artnoarr[$r['sku_item_code']].", ".$r['qty'];
                }else{
                    $succ[] = $r['sku_item_code'].", ".$r['qty'] ;
                }
				
				$ins = array();
				$ins['branch_id'] = $r['branch_id'];
				$ins['date'] = $r['date'];
				$ins['sku_item_code'] = $r['sku_item_code'];
				$ins['scanned_by'] = $r['scanned_by'];
				$ins['location'] = $r['location'];
				$ins['shelf_no'] = $r['shelf_no'];
				$ins['selling'] = $r['selling'];
				$ins['cost'] = $r['cost'];
				$ins['qty'] = $r['qty'];
				if($r['is_fresh_market'] == "yes"){
					$ins['is_fresh_market'] = 1;
					$ins['fresh_market_updated'] = 0;
				}
				
				 if(!$r['item_no']){
                    $item_no++;
                    $ins['item_no'] = $item_no;
                }else $ins['item_no'] = $r['item_no'];
                
                if (!$con->sql_query("replace into stock_check ".mysql_insert_by_field($ins),false,false)){
                    $err[] = mysql_error();
                }else{
                    $rows_inserted++;
					
					if($r['is_fresh_market'] == "yes"){
						// check for fresh market child item, proceed to auto fill zero
						$q2 = $con->sql_query("select si.*
											   from sku_items si 
											   left join sku on sku.id = si.sku_id 
											   left join category_cache cc on cc.category_id=sku.category_id
											   where si.sku_id = ".mi($sku_id)." and si.is_parent = 0");

						while($fm = $con->sql_fetchrow($q2)){
							$ins = array();
							$ins['branch_id'] = $r['branch_id'];
							$ins['date'] = $r['date'];
							$ins['sku_item_code'] = $fm['sku_item_code'];
							$ins['scanned_by'] = $r['scanned_by'];
							$ins['location'] = $r['location'];
							$ins['shelf_no'] = $r['shelf_no'];
							$ins['is_fresh_market'] = 1;
							$ins['fresh_market_updated'] = 0;
							$ins['cost'] = 0;
							$ins['selling'] = 0;
							$ins['qty'] = 0;
							
							 // need to ensure if user is using their own item no and it is greater than the current auto increment item no
							if($custom_item_no && $custom_item_no > $item_no){
								$custom_item_no++;
								$ins['item_no'] = $custom_item_no;
							}else{
								$item_no++;
								$ins['item_no'] = $item_no;
							}
							
							$con->sql_query("replace into stock_check ".mysql_insert_by_field($ins));
							$sid_list[] = $fm['id'];
						}
						$con->sql_freeresult($q2);
					}
                }
                
                if($form['fill_zero_options'] == "no_fill"){
                    $sid_list[] = $sid;
                    if(count($sid_list)>1000){
                        $con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
                        $sid_list = array();
                    }
                }
            }
            $con->sql_freeresult($q1);
            
            if($sid_list){
                $con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
                $sid_list = array();
            }
                    
            if ($form['fill_zero_options'] == "fill_zero"){
                $con->sql_query("insert into stock_check (branch_id, date, sku_item_code) select $bid, ".ms($form['cut_off_date']).", sku_item_code from sku_items where sku_item_code not in (select distinct sku_item_code from stock_check where branch_id=".mi($bid)." and date=".ms($form['cut_off_date']).")  order by sku_item_code");
                $succ[] = "fill up zero qty ".$con->sql_affectedrows()." rows.";
                $con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$bid") or die(mysql_error());
            }elseif($form['fill_zero_options'] == "fill_parent"){
				if($sku_id_list){
					$upd = array();
	            	$upd['branch_id'] = $bid;
	            	$upd['date'] = $form['cut_off_date'];
					
	            	$cost_params = array('cost');
					for($i=0; $i<count($sku_id_list); $i+=1000){
						$sku_id_filter=join(',',array_slice($sku_id_list, $i, $i+1000));

						$q_si = $con->sql_query("select si.id, si.sku_item_code
												from sku_items si
												left join sku on sku.id = si.sku_id
												where si.sku_item_code not in 
												(select distinct sc.sku_item_code from stock_check sc
												where sc.is_fresh_market=0 and sc.branch_id=".mi($upd['branch_id'])." and sc.date=".ms($upd['date']).") and si.sku_id in (".$sku_id_filter.") 
												order by si.sku_item_code");

						while($si = $con->sql_fetchassoc($q_si)){
							$upd['sku_item_code'] = $si['sku_item_code'];
							//$tmp = get_sku_item_cost_selling($upd['branch_id'],$si['id'],$upd['date'], $cost_params);
							//$upd['cost'] = $tmp['cost'];
							
							$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
							
							//$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id=".mi($si['id']));
							$sku_item_id_list[$si['id']] = $si['id'];
							if(count($sku_item_id_list)>=1000){
								$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=".mi($upd['branch_id'])." and sku_item_id in (".join(',',$sku_item_id_list).")");
								$sku_item_id_list = array();
							}
						}
						$con->sql_freeresult($q_si);
					}
					
					// update again for other sku if got
					if($sku_item_id_list){
						for($i=0; $i<count($sku_item_id_list); $i+=1000){
							$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=".mi($upd['branch_id'])." and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
						}
					}
					$sku_item_id_list = array();
				}
			}
            
            $log_details = array("Branch ID#$bid",
                                 "Import Format#".strtoupper($form['import_type']),
                                 "Cut-off#".$form['cut_off_date']);
            if($form['import_type'] != 'atp'){
                $log_details[] = "Scan By#".$form['scanned_by'];
                $log_details[] = "Location#".$form['location'];
                $log_details[] = "Shelf No#".$form['shelf_no'];
            }
            $log_details[] = "Deleted Rows#".$rows_deleted;
            $log_details[] = "Inserted Rows#".$rows_inserted;
            if($form['fill_zero_options'] != "no_fill") $log_details[] = "#".$form['fill_zero_options'];
            if($form['allow_duplicate'])   $log_details[] = "#Allow Duplicate";
            log_br($sessioninfo['id'],'Stock Check','',"Import Stock Check, ".join(' ', $log_details));
            
            if($err)    $smarty->assign('err', $err);
            $smarty->assign('succ', $succ);
            $this->display();
        }
    }
    
    function find_arms_code($find, $filter_fields = array('sku_item_code','mcode','artno','link_code')) {
        global $con;
        $code = ms($find);
        
		$filter_or = array();
		foreach($filter_fields as $field){
			$filter_or[] = "$field=$code";
		}
		$filter_or = join(' or ', $filter_or);
		
        $con->sql_query("select sku_item_code from sku_items where $filter_or limit 1");
        if($con->sql_numrows() > 0){
            $sku_item_code = $con->sql_fetchfield(0);
        }
        $con->sql_freeresult();
        if($sku_item_code)	return $sku_item_code;
        
        if (strlen($find) == 13)      $find = substr($find, 0, 12);
        elseif (strlen($find) == 8)   $find = substr($find, 0, 7);
        else return false;
      
        return $this->find_arms_code($find, $filter_fields);
    }
    
    function get_cost_selling(&$row){
        if (preg_match('/^M/', $row['sku_item_code'])) return;
        
        global $con, $config;
            
        if ($row['cost'] == 0) {
            // todo: use avg grn cost
            $con->sql_query("select sku_item_code, description, cost_price, grn_cost, avg_cost, date from sku_items
                            left join sku_items_cost_history on (sku_item_id = sku_items.id and date < '$row[date]' and branch_id=$row[branch_id])
                            where sku_item_code = '$row[sku_item_code]'
                            order by date desc limit 1");
            $r = $con->sql_fetchrow();
            $con->sql_freeresult();
            
            if ($config['stock_take_cost']!='avg')  $row['cost'] = ($r['grn_cost']>0) ? $r['grn_cost'] : $r['cost_price'];
            else    $row['cost'] = ($r['avg_cost']>0) ? $r['avg_cost'] : $r['cost_price'];
        }
        
        if ($row['selling'] == 0) {
            $con->sql_query("select sku_item_code, description, selling_price, price, sku_items_price_history.added from sku_items
                            left join sku_items_price_history on (sku_item_id = sku_items.id and sku_items_price_history.added < date_add('$row[date]', interval 1 day)  and branch_id=$row[branch_id])
                            where sku_item_code = '$row[sku_item_code]'
                            order by added desc limit 1");
            $r = $con->sql_fetchrow();
            $con->sql_freeresult();
            
            $row['selling'] = ($r['price'] != '') ? $r['price'] : $r['selling_price'];
        }
    }
    
    function clear_shelf_data($bid,$cutoff,$cols, &$msg) {
        global $con;
        static $deleted_shelf = array();
        
        if (!isset($deleted_shelf[$cols['location']][$cols['shelf_no']])) {
            // update cost changed first
            $q1 = $con->sql_query("select distinct(si.id) as sid
                                  from sku_items si
                                  left join stock_check sc using(sku_item_code)
                                  where sc.branch_id=$bid and sc.date=".ms($cutoff)." and sc.location=".ms($cols['location'])." and sc.shelf_no=".ms($cols['shelf_no']));
            
            $sid_list = array();
            while($r = $con->sql_fetchrow($q1)){
                $sid_list[] = mi($r['sid']);
                if(count($sid_list)>1000){  // max 1000 items per query
                    $con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
                    $sid_list = array();
                }
            }
            $con->sql_freeresult($q1);
            if($sid_list){
                $con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (".join(',', $sid_list).")");
                $sid_list = array();
            }
            
            // delete old stock take items
            $deleted_shelf[$cols['location']][$cols['shelf_no']] = true;
            $con->sql_query("delete from stock_check where branch_id=$bid and date=".ms($cutoff)." and location=".ms($cols['location'])." and shelf_no=".ms($cols['shelf_no']));
            $rows_deleted = $con->sql_affectedrows();
            $msg[] = "clean up ".mi($rows_deleted)." rows from Location#$cols[location],Shelf#$cols[shelf_no]";
            return mi($rows_deleted);
        }
    }
    
    function export_data() {
        global $con, $sessioninfo;
        
        $form = $_REQUEST;
        
        $con->sql_query("select *, if(selling_price is null,price,selling_price) as selling, sku_items_cost.qty as balance
                        from sku_items
                        left join sku_items_price on sku_items_price.sku_item_id = sku_items.id and sku_items_price.branch_id=$sessioninfo[branch_id]
                        left join sku_items_cost on sku_items_cost.sku_item_id = sku_items.id and sku_items_cost.branch_id=$sessioninfo[branch_id]
                        order by id");
        
        if ($form['export_type'] == 'pspv'){
            header("Content-type: text/plain");
            header("Content-Disposition: attachment;filename=PSPV.TXT");
            
            while($r = $con->sql_fetchassoc()){
                $r['description'] = substr($r['description'],0,36);
                printf("%-13s%-15s%-36s%9.2f%10d\r\n",$r['sku_item_code'],$r['sku_item_code'],$r['description'],$r['selling'],$r['balance']);
                if ($r['mcode']) printf("%-13s%-15s%-36s%9.2f%10d\r\n",$r['mcode'],$r['sku_item_code'],$r['description'],$r['selling'],$r['balance']);
                if ($r['link_code']) printf("%-13s%-15s%-36s%9.2f%10d\r\n",$r['link_code'],$r['sku_item_code'],$r['description'],$r['selling'],$r['balance']);
            }
			log_br($sessioninfo['id'],'Stock Check','',"Export Stock Check, Branch ID#$sessioninfo[branch_id], Export to Format#PSPV(PSPV.TXT)");
        }elseif($form['export_type'] == 'csv'){
            header('Content-type: text/csv');
            header('Content-disposition: attachment;filename=stock_take.csv');
            header("Content-Transfer-Encoding: UTF-8");
            header('Pragma: no-cache');
            header("Expires: 0");
    
            $f = fopen("php://output", "w");
    
            while($r=$con->sql_fetchassoc()){
                if($r['mcode']) $sku_code = $r['mcode'];
                else $sku_code = $r['sku_item_code'];
                $row = array($sku_code, $r['balance'], $r['description'], $r['selling']);
                
                fputcsv($f, $row);
            }
            fclose($f);
			log_br($sessioninfo['id'],'Stock Check','',"Export Stock Check, Branch ID#$sessioninfo[branch_id], Export to Format#csv(stock_take.csv)");
        }
    }
    
    function ajax_check_code(){
        global $con;
        
        $con->sql_query("select id from sku_items where sku_item_code = ".ms($_REQUEST['code']));
        if ($con->sql_numrows()) print "OK";
        $con->sql_freeresult();
        exit;
    }
    
    function add_data(){
        global $con, $sessioninfo;
        $form = $_REQUEST;
        
         // make sure all code are valid
		$sku_item_id_list = array();
        foreach($form['sku_item_code'] as $idx=>$d){
            if(!$d)  print "Error: In House Code '$d' is empty";
            $con->sql_query("select id from sku_items where sku_item_code = ".ms($d));
            if ($con->sql_numrows() == 0){
                print "Error: In House Code '$d' is invalid";
                exit;
            }
			
			//get sku item id and store
			$tmp = $con->sql_fetchrow();
			$sku_items_id = mi($tmp[0]);
			$con->sql_freeresult();
			$sku_item_id_list[$d] = $sku_items_id;
        }   
        $bid = mi($form['branch_id']);
        $dt = $form['cut_off_date'];
        foreach($form['sku_item_code'] as $idx=>$d){
            $cols = array();
            $cols['branch_id'] = $bid;
            $cols['date'] = $dt;
            foreach(array('location','scanned_by','shelf_no','item_no','sku_item_code','selling','qty','cost') as $k){
                $cols[$k] = urldecode($form[$k][$idx]);
            }
            
            // check duplicate in database
            $check_exist = $con->sql_query("select *
                                           from stock_check
                                           where branch_id=$bid and date=".ms($dt)." and location=".ms($cols['location'])."and shelf_no=".ms($cols['shelf_no'])." and item_no=".ms($cols['item_no'])." and sku_item_code=".ms($cols['sku_item_code']));
                            
            if ($con->sql_numrows($check_exist) > 0){
                $exist_data[$idx] = "Duplicate data on: $cols[sku_item_code], $cols[location], $cols[shelf_no], $cols[item_no]";
            }
            $con->sql_freeresult($check_exist);
        
            //check duplicate on incoming data
            if ($store[$cols['sku_item_code']][$cols['location']][$cols['shelf_no']][$cols['item_no']])
                $exist_data[$idx] = "Duplicate data on: $cols[sku_item_code], $cols[location], $cols[shelf_no], $cols[item_no]";
            else
                $store[$cols['sku_item_code']][$cols['location']][$cols['shelf_no']][$cols['item_no']] = 1;
        }
        
        if ($exist_data){
            $msg = join("\n", $exist_data);
            print $msg;
            exit;
        }
        
        foreach($form['sku_item_code'] as $idx=>$d) {
            $cols = array();
            $cols['branch_id'] = $bid;
            $cols['date'] = $dt;
        
            foreach(array('location','scanned_by','shelf_no','item_no','sku_item_code','selling','qty','cost') as $k) {
                $cols[$k] = urldecode($form[$k][$idx]);
            }
            $this->get_cost_selling($cols);
			
            $con->sql_query("insert into stock_check ".mysql_insert_by_field($cols)) or die(mysql_error());
            $con->sql_query("update sku_items_cost left join sku_items on sku_item_id = sku_items.id set changed=1 where sku_item_code=".ms($cols['sku_item_code'])." and branch_id=$bid") or die(mysql_error());
			
			//get sku item id
			$sku_item_code = trim($cols['sku_item_code']);
			log_br($sessioninfo['id'], 'Stock Check', "$sku_item_id_list[$sku_item_code]", "Added New Stock Check, Branch ID#$bid, Date#$dt, ARMS Code#$cols[sku_item_code], Sku Item ID#$sku_item_id_list[$sku_item_code], Scan By#$cols[scanned_by], Location#$cols[location], Shelf No#$cols[shelf_no], Item No#$cols[item_no], Qty#$cols[qty], Selling#$cols[selling], Cost#$cols[cost]");
            $n++;
        }
        print "$n Record(s) added";
        exit;
    }
    
    function ajax_load_date(){
        global $con, $sessioninfo;
        $form = $_REQUEST;
		if($form["branch_id"]){
			$branch_id = $form["branch_id"];
		}else{
			$branch_id = $sessioninfo["branch_id"];
		}
        $con->sql_query("select distinct(date) as date from stock_check where branch_id=".$branch_id." order by date desc");
        
        if ($con->sql_numrows() > 0 ){
			while($r = $con->sql_fetchassoc()){
                print "<option value=".$r['date'].">".$r['date']."</option>";
			}
			$con->sql_freeresult($check_exist);
		}else   print "<option value=''>-- Please Select --</option>";            
    }
    
    function search_data(){
        global $con, $smarty;
        $form = $_REQUEST;
	    
        $item = array();
        $filter[] = "branch_id = ".$form['search_branch_id'];
        $filter[] = "date = ".ms($form['search_date']);
        if ($form['search_value']) $filter[] = $form['search_type']."= ".ms($form['search_value']);
        
        $filter = join(' and ',$filter);
        
        //calculate total items
        $item_limit = 100;
        $pg = mi($form['pg'] * $item_limit);
        
        $con->sql_query("select count(*) from stock_check where $filter") or die(mysql_error());
        $item_count = $con->sql_fetchfield(0);
        $con->sql_freeresult();
        
        if($item_count == 0){
            $smarty->assign("error", "-- No Data --");
            $smarty->display("admin.stockchk_import.table.tpl");
            return;
        }else{
            if($item_count > $item_limit){
                $page_num = ceil($item_count/$item_limit);
                $smarty->assign("page_num", $page_num);
            }
        }
        
        //retrieve data
        $con->sql_query("select date, branch_id, sku_item_code, location, shelf_no, item_no, scanned_by, qty, selling,cost
                        from stock_check
                        where $filter order by location, shelf_no, item_no limit $pg, $item_limit");
        
        while($r = $con->sql_fetchassoc()){
            $item[] = $r;
        }
        $con->sql_freeresult();
        
        $smarty->assign("item", $item);
        $smarty->assign("form", $form);
        $smarty->display("admin.stockchk_import.table.tpl");
    }
    
    function update_data(){
        global $con, $sessioninfo;
        $form = $_REQUEST;
        
        $bid = $form['branch_id'];
        $dt = $form['date'];
        $code = $form['sku_item_code'];
        $location = $form['location'];
        $shelf = $form['shelf_no'];
        $itemno = $form['item_no'];
        $field = $form['field'];
        $newvalue = trim($form['newvalue']);
        
        if ($newvalue == '' && ($field == 'selling' || $field == 'cost')) {
            $r['branch_id'] = $bid;
            $r['date'] = $dt;
            $r['sku_item_code'] = $code;
            $this->get_cost_selling($r);
            $newvalue = $r[$field];
        }
		
		//get value of stock_check
		$q1 = $con->sql_query("select $field from stock_check where branch_id=".mi($bid)." and date=".ms($dt)." and sku_item_code=".ms($code)." and location=".ms($location)." and shelf_no=".ms($shelf)." and item_no=".ms($itemno));
        $tmp = $con->sql_fetchrow($q1);
		$original_val = $tmp[0];
		$con->sql_freeresult($q1);
		
		//check need update or not
		if($original_val != $newvalue){
			$field_name = array("scanned_by"=>"Scanned By", "qty"=>"Qty", "selling"=>"Selling", "cost"=>"Cost");
			$con->sql_query("update stock_check set $field = ".ms($newvalue)." where branch_id=".mi($bid)." and date=".ms($dt)." and sku_item_code=".ms($code)." and location=".ms($location)." and shelf_no=".ms($shelf)." and item_no=".ms($itemno)) or die(mysql_error());
			if ($field=='cost' || $field=='qty') $con->sql_query("update sku_items_cost left join sku_items on sku_item_id = sku_items.id set changed=1 where sku_item_code=".ms($code)." and branch_id=".mi($bid)) or die(mysql_error());
			
			//get sku item id
			$q2 = $con->sql_query("select id from sku_items where sku_item_code =".ms($form['sku_item_code']));
			$tmp2 = $con->sql_fetchrow($q2);
			$sku_item_id = $tmp2[0];
			$con->sql_freeresult($q2);
			
			log_br($sessioninfo['id'], 'Stock Check', "$sku_item_id", "Update Stock Check, Branch ID#$form[branch_id], ARMS Code#$form[sku_item_code], Sku Item ID#$sku_item_id, Date#$form[date]， Edited fields: ($field_name[$field]:$original_val=>$newvalue)");
		}
        print $newvalue;
    }
    
    function delete_data(){
        global $con, $sessioninfo;
        
        $form = $_REQUEST;
        $si_code_list = array();
        foreach($form['branch_id'] as $ind => $data){
            $si_code_list[] = $form['sku_item_code'][$ind];
            
            $bid = mi($form['branch_id'][$ind]);
            $dt = $form['date'][$ind];
            $code = ms($form['sku_item_code'][$ind]);
            $location = ms($form['location'][$ind]);
            $shelf = ms($form['shelf_no'][$ind]);
            $itemno = ms($form['item_no'][$ind]);
            
            // delete stock take
            $con->sql_query("delete from stock_check where branch_id=$bid and date=".ms($dt)." and sku_item_code=$code and location=$location and shelf_no=$shelf and item_no=$itemno");
            
            // mark cost changed
            $con->sql_query("update sku_items_cost set changed=1 where branch_id=$bid and sku_item_id in (select id from sku_items where sku_item_code = $code)");
        }
		if($si_code_list)   log_br($sessioninfo['id'],'Stock Check','',"Delete Stock Check, Branch ID#$bid, Date#$dt, ARMS Code#".join(',', $si_code_list));
        $this->search_data();
    }
	
	function validate_data(){
		global $con, $LANG, $config, $smarty;

		$form = $_REQUEST;        
        $file = $_FILES['import_csv'];
        $f = fopen($file['tmp_name'], "rt");
        
        $allow_duplicate = mi($form['allow_duplicate']);
        $bid = intval($form['branch_id']);
        
        $delim = ",";
        
        if($form['import_type'] == 'pspv'){
            while($line = fgets($f)) {
                $data = preg_split('/\s+/', $line);
                for($i=0; $i<count($data); $i++) $data[$i] = trim($data[$i]);
                // skip lines with no sku_item_code
                if(!$data[0] && !$data[1]) continue;
                $lines[] = $data;
            }
        }else{
            while($data = fgetcsv($f, 5000, $delim))
            {
                for($i=0; $i<count($data); $i++) $data[$i] = trim($data[$i]);
                // skip lines with no sku_item_code
                if (!$data[0] && !$data[1]) continue;
                $lines[] = $data;
            }
        }
        fclose($f);
            
        $con->sql_query("create temporary table if not exists tmpo_stock_check
                        (branch_id int, date date, sku_item_code char(15), scanned_by char(15), location char(15), shelf_no char(15), item_no char(5), selling double, qty double, cost double, primary key (`date`,`branch_id`,`sku_item_code`,`location`,`shelf_no`,`item_no`))");
        
        $succ = $err = $sku_data = array();
		$custom_item_no = 0;
		$master_cost_error = false;
        foreach($lines as $data) {
            $cols = array();
            $cols['date'] = $form['cut_off_date'];
            $cols['branch_id'] = $bid;
            $find_code = trim($data[0]);	// default is search for 1st column
            
            if($form['import_type'] != "atp" && !$find_code) continue;
            
            switch($form['import_type']) {
                case 'atp':
                    if ($data[0] != '') $find_code = substr($data[0],0,12);
                    else $find_code = $data[1];                        
                    $cols['sku_item_code'] = $this->find_arms_code($find_code);
                    $cols['scanned_by'] = $data[2];
                    $cols['location'] = $data[3];
                    $cols['shelf_no'] = $data[4];
                    $cols['item_no'] = $data[5];
					if($custom_item_no < $cols['item_no']) $custom_item_no = $cols['item_no'];
                    $cols['qty'] = doubleval($data[6]);
                    $cols['selling'] = doubleval($data[7]);
                    $cols['cost'] = doubleval($data[8]);
                    break;
                case 'arms_stock':
                    $con->sql_query("select sku_item_code, selling_price from sku_items where sku_item_code=".ms($find_code)." limit 1");			
                    $cols['sku_item_code'] = $con->sql_fetchfield(0);
                    $con->sql_freeresult();
                    $cols['qty'] = doubleval($data[1]);
                    $cols['scanned_by'] = $form['scanned_by'];
                    $cols['location'] = $form['location'];
                    $cols['shelf_no'] = $form['shelf_no'];
                    $cols['selling'] = 0;
                    $cols['cost'] = 0;
                    break;
                case 'artno_stock':
                    $artno = trim(preg_replace('/\s+/',' ', $find_code));
                    $con->sql_query("select sku_item_code, selling_price from sku_items where artno=".ms($artno)." limit 1");
                    $cols['sku_item_code'] = $con->sql_fetchfield(0);
                    $con->sql_freeresult();
                    $artnoarr[$cols['sku_item_code']]=$artno;
                    $cols['qty'] = doubleval($data[1]);
                    $cols['scanned_by'] = $form['scanned_by'];
                    $cols['location'] = $form['location'];
                    $cols['shelf_no'] = $form['shelf_no'];
                    $cols['selling'] = 0;
                    $cols['cost'] = 0;
                    break;
				case 'arms_stock_2':
					$cols['sku_item_code'] = $this->find_arms_code($find_code, array('sku_item_code','mcode','link_code'));
                    $cols['qty'] = doubleval($data[1]);
                    $cols['scanned_by'] = $form['scanned_by'];
                    $cols['location'] = $form['location'];
                    $cols['shelf_no'] = $form['shelf_no'];
                    $cols['selling'] = 0;
                    $cols['cost'] = 0;
					break;
                default:
                    $cols['sku_item_code'] = $this->find_arms_code($find_code);
                    $cols['scanned_by'] = $form['scanned_by'];
                    $cols['location'] = $form['location'];
                    $cols['shelf_no'] = $form['shelf_no'];
                    $cols['qty'] = doubleval($data[1]);
                    $cols['selling'] = mf($data[3]);
                    $cols['cost'] = 0;
                    break;
            }
            
            if(!$cols['sku_item_code']){
                $err[] = $find_code." not found.";
                continue;
            }
            
            if(!$cols['scanned_by'] || !$cols['location'] || !$cols['shelf_no']){
                $err[] = "$find_code: Scanned By, Location or Shelf No is empty.";
                continue;
            }
			
			$q1 = $con->sql_query("select si.*, if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market,
								   u.fraction as uom_fraction, u.code as uom_code
								   from sku_items si 
								   left join sku on sku.id = si.sku_id 
								   left join category_cache cc on cc.category_id=sku.category_id
								   left join uom u on u.id=si.packing_uom_id
								   where si.sku_item_code = ".ms($cols['sku_item_code']));
								   
			$si_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			// check fresh market
			// found user trying to insert fresh market item with child item, skip the record and display error
			if(!$si_info['is_parent'] && $si_info['is_fresh_market'] == 'yes'){
				$err[] = sprintf($LANG['IMPORT_ST_INVALID_FM_ITEM'], $find_code);
				continue;
			}
			
			// means using C) Last GRN Cost with individual SKU calculation, use back default way to lookup cost and selling
			if((!$config['sku_use_avg_cost_as_last_cost'] && !$config['sku_update_cost_by_parent_child']) || !$config['stock_take_enable_check_cost']){
				$this->get_cost_selling($cols);
			}else{
				$csv_cost = $cols['cost']; // need the original cost price from 
				$his_info = array();
				$his_info['branch_id'] = $cols['branch_id'];
				$his_info['date'] = $cols['date'];
				$his_info['sku_item_code'] = $cols['sku_item_code'];
				$this->get_cost_selling($his_info);
				
				if(!$cols['cost']) $cols['cost'] = $his_info['cost'];
				if(!$cols['selling']) $cols['selling'] = $his_info['selling'];
			}
			
			// if found customer system setting is set as either below:
			// A) Average Cost with parent-child calculation
			// B) Last GRN Cost with parent-child calculation
			// need to check the cost whether got diference with system or not
			if($config['stock_take_enable_check_cost'] && ($config['sku_use_avg_cost_as_last_cost'] || $config['sku_update_cost_by_parent_child']) && $csv_cost != 0){
				$sku_id = $si_info['sku_id'];
				$new_unit_cost = round($csv_cost / $si_info['uom_fraction'], $config['global_cost_decimal_points']);

				// check against current imported SKU item whether have cost price difference with other shelf and location
				$q1 = $con->sql_query("select * from stock_check where branch_id=".mi($cols['branch_id'])." and date=".ms($cols['date'])." and sku_item_code=".ms($cols['sku_item_code'])." and (location!=".ms($cols['location'])." or shelf_no!=".ms($cols['shelf_no']).") and cost != ".mf($cols['cost'])." and cost != 0");
				
				$db_cost_variance = false;
				while($sc = $con->sql_fetchassoc($q1)){
					$sc_unit_cost = round($sc['cost'] / $si_info['uom_fraction'], $config['global_cost_decimal_points']);
					if($new_unit_cost != $sc_unit_cost){ // if cost price from other shift/location is different with current one
						$err[] = sprintf($LANG['IMPORT_ST_COST_INVALID'], $cols['sku_item_code'], $sc['location'], $sc['shelf_no'], $si_info['uom_code'], number_format($sc['cost'], $config['global_cost_decimal_points']), number_format($csv_cost, $config['global_cost_decimal_points']));
						$db_cost_variance = true;
						break;
					}
				}
				$con->sql_freeresult($q1);
				
				if($db_cost_variance == true) continue;
				
				if(isset($sku_data[$sku_id]) && $sku_data[$sku_id]['new_unit_cost'] != $new_unit_cost){ // found having different unit cost price from import file
					$err[] = sprintf($LANG['IMPORT_ST_COST_DIFF'], $cols['sku_item_code'], $si_info['uom_code'], number_format($csv_cost, $config['global_cost_decimal_points']), number_format($sku_data[$sku_id]['new_unit_cost'], $config['global_cost_decimal_points']));
					continue;
				}else $sku_data[$sku_id]['new_unit_cost'] = $new_unit_cost;
			}
			
            if (!$con->sql_query("insert into tmpo_stock_check ".mysql_insert_by_field($cols),false,false)) {
                // check duplicate status
                if(!$allow_duplicate){
                    $err[] = $cols['sku_item_code']." have duplicate entry.";
                }else {
                    // allow duplicate
                    // get the duplicate row info
                    $q_tmp = $con->sql_query("select * from tmpo_stock_check where branch_id=".mi($cols['branch_id'])." and date=".ms($cols['date'])." and sku_item_code=".ms($cols['sku_item_code'])." and location=".ms($cols['location'])." and shelf_no=".ms($cols['shelf_no'])." and item_no=".ms($cols['item_no']));
                    $tmp = $con->sql_fetchassoc($q_tmp);
                    $con->sql_freeresult($q_tmp);
                    
                    // check is it same selling price and cost?
                    if($tmp['selling'] != $cols['selling'] || $tmp['cost'] != $cols['cost']){
                        $err[] = "Item ".$cols['sku_item_code']." Duplicated with different selling price or cost.";
                    }else{
                        $q_tmp = $con->sql_query("update tmpo_stock_check set qty=qty+".mf($cols['qty'])." where branch_id=".mi($cols['branch_id'])." and date=".ms($cols['date'])." and sku_item_code=".ms($cols['sku_item_code'])." and location=".ms($cols['location'])." and shelf_no=".ms($cols['shelf_no'])." and item_no=".ms($cols['item_no']));
                    }
                }
            }
        }
        unset($lines);
		
		// if found customer system setting is set as either below:
		// A) Average Cost with parent-child calculation
		// B) Last GRN Cost with parent-child calculation
		// check if no error but user is not choosing fill zero option, need to check whether they have do stock take for all parent child
		if(!$err && $config['stock_take_enable_check_cost'] && ($config['sku_use_avg_cost_as_last_cost'] || $config['sku_update_cost_by_parent_child']) && $form['fill_zero_options'] == "no_fill"){
			$si_list = array();
			$file_path = "tmp/import_st_si_child.csv";
			if(file_exists($file_path)) unlink($file_path);
			$q1 = $con->sql_query("select tsc.*, si.sku_id, group_concat(distinct si.id) as grp_sid, 
								   if(sku.is_fresh_market='inherit', cc.is_fresh_market, sku.is_fresh_market) as is_fresh_market
								   from tmpo_stock_check tsc
								   left join sku_items si on si.sku_item_code = tsc.sku_item_code
								   left join sku on sku.id = si.sku_id
								   left join category_cache cc on cc.category_id=sku.category_id
								   where branch_id=".mi($bid)." and date=".ms($form['cut_off_date'])."
								   group by si.sku_id
								   having is_fresh_market = 'no'");
			
			while($r = $con->sql_fetchassoc($q1)){
				$q2 = $con->sql_query("select sku_item_code, artno, mcode, description from sku_items where id not in (".$r['grp_sid'].") and sku_id = ".mi($r['sku_id'])." and active=1");
				$si_count = $con->sql_numrows($q2);
				
				// means there are still have other SKU items which never did stock take, show error
				if($si_count > 0){
					$err[] = sprintf($LANG['IMPORT_ST_NO_PARENT_CHILD'], $r['sku_item_code']);

					while($r1 = $con->sql_fetchassoc($q2)){
						$si_list[] = $r1;
					}
				}
				$con->sql_freeresult($q2);
			}
			$con->sql_freeresult($q1);
			
			if($si_list){
				$fp = fopen($file_path, 'w');
				$header = array();
				$header[] = "SKU_ITEM_CODE";
				$header[] = "ARTNO";
				$header[] = "MCODE";
				$header[] = "DESCRIPTION";
				fputcsv($fp, $header);

				foreach($si_list as $arr=>$tmp){
					fputcsv($fp, $tmp);
				}
				fclose($fp);
				
				chmod($file_path, 0777);
				$smarty->assign("pc_file_path", $file_path);
			}
			
			unset($si_list, $header);
		}

		return $err;
	}
}

$STOCK_CHECK_IMPORT = new STOCK_CHECK_IMPORT("Stock Check Import");
?>
