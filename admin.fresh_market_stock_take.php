<?php
/*
9/2/2010 3:45:56 PM Andy
- Add can direct add stock take item under selected list.
- Add "Multiple Add"
- Clone selected branch, date, location and shelf to "Add New Stock".
- Print report add uom.
- Change condition filter for possible sku.
- add privilege checking "FM_STOCK_TAKE".

9/14/2010 2:00:05 PM Andy
- Add print stock count sheet when have config.

11/30/2010 5:35:15 PM Andy
- Add load all fresh market items when click "show possible items".
- Add filter for "show possible items" to filter out those already stock taked items.

6/8/2011 10:51:55 AM Andy
- Add artno column at stock take.

6/24/2011 3:02:46 PM Andy
- Make all branch default sort by sequence, code.

9/28/2011 10:55:45 AM Justin
- Modified the Ctn and Pcs round up to base on config set.
- Applied when get item list, pick up sku item's doc_allow_decimal.

9/19/2012 11:47:00 AM Fithri
- Add change batch for fresh market stock take

11/24/2016 10:35 AM Andy
- Fixed to auto zerolise child sku when import stock take.
- Fixed reset stock take, sku type din't auto reload.

2/24/2017 5:41 PM Andy
- Fixed Change Batch not to move those imported stock take.

5/4/2017 3:13 PM Justin
- Bug fixed on system showing SQL query error whenever exporting same date, location, shelf and sku item.

8/4/2017 16:38 PM Qiu Ying
- Bug fixed on currency symbol is not shown
- Bug fixed on remove "clear_all_assign" when printing

11/27/2017 3:10 PM Justin
- Enhanced to have auto fill zero feature.

3/27/2020 10:02 AM William
- Enhanced to insert id manually for stock_take_pre table that use auto increment.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('FM_STOCK_TAKE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'FM_STOCK_TAKE', BRANCH_CODE), "/index.php");

class FRESH_MARKET_STOCK_TAKE extends Module{
	var $branch_id;
	var $can_select_branch = false;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo, $config;
	    if(BRANCH_CODE=='HQ' && $config['single_server_mode']){
	        $this->can_select_branch = true;
            $smarty->assign('can_select_branch', $this->can_select_branch);
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		parent::__construct($title);
	}
	
    function _default(){
        if($this->can_select_branch)	$this->load_branches();
        $this->load_date_list($this->branch_id, true);
        if($_REQUEST['date'])   $this->load_location_list($this->branch_id, $_REQUEST['date'], true);
        if($_REQUEST['location'])   $this->load_shelf_list($this->branch_id, $_REQUEST['date'], $_REQUEST['location'], '', true);
        
        $this->init_selection();
		$this->display();
		
	}
	
	private function init_selection(){
		global $con, $smarty;
		
		$con->sql_query("select * from sku_type where active=1 order by code");
		$smarty->assign('sku_type', $con->sql_fetchrowset());
		$con->sql_freeresult();
	}
	
	function load_branches(){
		global $con, $smarty;
		
		$con->sql_query("select * from branch order by sequence, code");
		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}
		$smarty->assign('branches', $branches);
		return $branches;
	}
	
	function load_date_list($bid=0, $sqlonly = false){
		global $con, $smarty;
		
		$bid = mi($bid)>0 ? mi($bid) : mi($_REQUEST['branch_id']);
		$con->sql_query("select distinct(date) as d from stock_take_pre where branch_id=$bid and imported=0 and is_fresh_market=1");
		while($r = $con->sql_fetchassoc()){
		    if(!$sqlonly)   print "<option value='".$r['d']."'>".$r['d']."</option>";
			$date[] = $r;
		}
		
		$smarty->assign('date', $date);
		return $date;
	}
	
	function load_location_list($bid=0, $date='', $sqlonly = false){
        global $con, $smarty;

		$bid = mi($bid)>0 ? mi($bid) : mi($_REQUEST['branch_id']);
		if(!$date)  $date = $_REQUEST['date'];
		
		$con->sql_query("select distinct(location) as loc from stock_take_pre where branch_id=$bid and date=".ms($date)." and imported=0 and is_fresh_market=1");
		while($r = $con->sql_fetchassoc()){
		    if(!$sqlonly)   print "<option value='".$r['loc']."'>".$r['loc']."</option>";
			$loc[] = $r;
		}

		$smarty->assign('loc', $loc);
		return $loc;
	}
	
	function load_shelf_list($bid=0, $date='', $loc='', $loc2='', $sqlonly = false){
	    global $con, $smarty;
	    
        $bid = mi($bid)>0 ? mi($bid) : mi($_REQUEST['branch_id']);
		if(!$date)  $date = $_REQUEST['date'];
		if(!$loc)  $loc = $_REQUEST['loc'];
		
		$filter[] = "branch_id=$bid and date=".ms($date);
		if($loc&&$loc2) $filter[]= "location between ".ms($loc)." and ".ms($loc2);
		else    $filter[] = "location=".ms($loc);
		$filter = "where ".join(' and ', $filter);
		
		$con->sql_query("select distinct(shelf) as s from stock_take_pre $filter and imported=0 and is_fresh_market=1");
		while($r = $con->sql_fetchassoc()){
		    if(!$sqlonly)   print "<option value='".$r['s']."'>".$r['s']."</option>";
			$shelf[] = $r;
		}

		$smarty->assign('shelf', $shelf);
		return $loc;
	}
	
	function ajax_open(){
		global $con, $smarty;
		if($this->can_select_branch)	$this->load_branches();
		$this->display('admin.fresh_market_stock_take.open.tpl');
	}
	
	function ajax_add_stock_take_item(){
    	global $con, $smarty, $sessioninfo, $appCore;

		$upd = array();
    	$upd['branch_id'] = $this->branch_id;
    	$upd['date'] = trim($_REQUEST['date']);
    	$upd['location'] = trim($_REQUEST['location']);
    	$upd['shelf'] = trim($_REQUEST['shelf']);
    	$upd['qty'] = mf($_REQUEST['sku_autocomplete_qty']);
    	$upd['user_id'] = $sessioninfo['id'];
    	$upd['is_fresh_market'] = 1;
    	$sid_list = $_REQUEST['sid'];
    	
    	if(!$upd['date'])   die('Invalid Date.');
    	if(!$upd['location'])   die('Invalid Location.');
    	if(!$upd['shelf'])   die('Invalid Shelf.');
    	if(!$sid_list)    die('Invalid SKU');
    	
    	foreach($sid_list as $sid){
			$sid = mi($sid);
			$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($this->branch_id));
			$upd['sku_item_id'] = $sid;
			
			$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd));
	    	$id = $upd['id'];

	    	$sql = "select stp.*,si.description, si.sku_item_code, si.artno,uom.code as uom_code, si.doc_allow_decimal
			from stock_take_pre stp
			left join sku_items si on si.id=stp.sku_item_id
			left join uom on uom.id=si.packing_uom_id
			where stp.branch_id=".mi($upd['branch_id'])." and stp.id=$id";
			$con->sql_query($sql);
			$item = $con->sql_fetchrow();
			$smarty->assign('item', $item);
			$ret['html'] .= $smarty->fetch('admin.fresh_market_stock_take.open.item_row.tpl');
		}

		print json_encode($ret);
	}
	
	function ajax_load_stock_take_list(){
		global $con, $smarty;
		if($this->can_select_branch)	$this->load_branches();
		
		$branch_id = $this->branch_id;
		$date = trim($_REQUEST['date']);
		$loc = trim($_REQUEST['location']);
		$shelf = trim($_REQUEST['shelf']);
		$sku_type= trim($_REQUEST['sku_type']);
		
		$filter = array();
		$filter[] = "stp.branch_id=".mi($branch_id)." and stp.date=".ms($date)." and stp.location=".ms($loc)." and stp.shelf=".ms($shelf);
		$filter[] = "stp.imported=0 and stp.is_fresh_market=1";
		if($sku_type)   $filter[] = "sku.sku_type=".ms($sku_type);
		$filter = "where ".join(' and ', $filter);
		
		$sb_tbl = "stock_balance_b".mi($branch_id)."_".date('Y', strtotime($date));
		// check whether stock balance table exists
		$sb_exists = $con->sql_query("explain $sb_tbl",false,false);
		if($sb_exists){
			$add_left_join = "left join $sb_tbl sb on sb.sku_item_id=stp.sku_item_id and stp.date between sb.from_date and sb.to_date";
			$add_col = ",sb.qty as sb_qty";
		}
		
		$sql = "select stp.*, si.description, si.sku_item_code, si.artno,si.mcode,user.u,uom.code as uom_code, si.doc_allow_decimal $add_col
		from stock_take_pre stp
		left join sku_items si on si.id=stp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join uom on uom.id=si.packing_uom_id
		left join user on user.id=stp.user_id
		$add_left_join
		$filter order by stp.id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
		    $r['variances'] = $r['qty'] - $r['sb_qty'];
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('items', $items);
		$this->display('admin.fresh_market_stock_take.list.tpl');
	}
	
	function ajax_delete_item(){
		global $con, $smarty;
		
		$branch_id = mi($this->branch_id);
		$item_id = mi($_REQUEST['item_id']);
		
		$con->sql_query("delete from stock_take_pre where imported=0 and branch_id=$branch_id and id=$item_id and is_fresh_market=1");
		print "OK";
	}
	
	function ajax_swap_item(){
		global $con;
		
		$branch_id = mi($this->branch_id);
		$item_id = mi($_REQUEST['item_id']);
		$swap_with_item_id = mi($_REQUEST['swap_with_item_id']);
		
		$con->sql_query("update stock_take_pre set id=-1 where branch_id=$branch_id and id=$swap_with_item_id and imported=0 and is_fresh_market=1");
		if($con->sql_affectedrows()){
            $con->sql_query("update stock_take_pre set id=$swap_with_item_id where branch_id=$branch_id and id=$item_id and imported=0 and is_fresh_market=1");
            $con->sql_query("update stock_take_pre set id=$item_id where branch_id=$branch_id and id=-1 and imported=0 and is_fresh_market=1");
		}
	}
	
	function save_stock_list(){
		global $con, $sessioninfo;
		
		$branch_id = mi($this->branch_id);
		$date = $_REQUEST['date'];
		$loc = $_REQUEST['location'];
		$shelf = $_REQUEST['shelf'];
		$sku_type = $_REQUEST['sku_type'];
		$item_qty = $_REQUEST['qty'];
		
		if($item_qty){
			foreach($item_qty as $item_id=>$qty){
				$con->sql_query("update stock_take_pre set qty=".mf($qty)." where branch_id=$branch_id and id=".mi($item_id)." and imported=0 and is_fresh_market=1");
			}
		}
		log_br($sessioninfo['id'], 'FRESH MARKET STOCK TAKE', $branch_id, "Save Fresh Market Stock Take: Branch ID#$branch_id, Date#$date, Location#$loc, Shelf#$shelf, SKU Type#".($sku_type? $sku_type:'All'));
		header("Location: $_SERVER[PHP_SELF]?date=$date&location=$loc&shelf=$shelf&sku_type=$sku_type".($branch_id==$sessioninfo['branch_id']? '': "&branch_id=$branch_id"));
	}
	
	function delete_stock_list(){
	    global $con, $sessioninfo;
	    
        $branch_id = mi($this->branch_id);
		$date = $_REQUEST['date'];
		$loc = $_REQUEST['location'];
		$shelf = $_REQUEST['shelf'];
		$sku_type = $_REQUEST['sku_type'];
		$item_qty = $_REQUEST['qty'];

		if($item_qty){
			foreach($item_qty as $item_id=>$qty){
				$con->sql_query("delete from stock_take_pre where branch_id=$branch_id and id=".mi($item_id)." and imported=0 and is_fresh_market=1");
			}
		}
		log_br($sessioninfo['id'], 'FRESH MARKET STOCK TAKE', $branch_id, "Delete Fresh Market Stock Take: Branch ID#$branch_id, Date#$date, Location#$loc, Shelf#$shelf, SKU Type#".($sku_type? $sku_type:'All'));
		header("Location: $_SERVER[PHP_SELF]?msg=Stock Take Deleted");
	}
	
	function ajax_update_item_qty(){
		global $con;
		
		$branch_id = mi($this->branch_id);
		$item_id = mi($_REQUEST['item_id']);
		$qty = mf($_REQUEST['qty']);
		$con->sql_query("update stock_take_pre set qty=$qty where branch_id=$branch_id and id=$item_id and imported=0 and is_fresh_market=1");
		print "OK";
	}
	
	function print_report($rpt_type = ''){
        global $con, $smarty, $sessioninfo, $config;

        $branch_id = mi($this->branch_id);
        $sku_type = trim($_REQUEST['sku_type']);
        $date = trim($_REQUEST['date']);
        	
        $con->sql_query("select * from branch where id=$branch_id");
        if ($con->sql_numrows()>0){
          $branch = $con->sql_fetchrow();
          $smarty->assign("branch",$branch);
        }
        
        // check whether stock balance table exists
        $sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));
		$sb_exists = $con->sql_query("explain $sb_tbl",false,false);
		if($sb_exists){
			$add_left_join = "left join $sb_tbl sb on sb.sku_item_id=stp.sku_item_id and stp.date between sb.from_date and sb.to_date";
			$add_col = ",sb.qty as sb_qty";
		}
		
        if(!$rpt_type){ // print report
			$loc = trim($_REQUEST['location']);
			$shelf = trim($_REQUEST['shelf']);
			
			$filter[] = "stp.branch_id=$branch_id and stp.date=".ms($date)." and stp.location=".ms($loc)." and stp.shelf=".ms($shelf);
			$filter[] = "stp.imported=0 and stp.is_fresh_market=1";
			if($sku_type)   $filter[] = "sku.sku_type=".ms($sku_type);
			$filter = "where ".join(' and ', $filter);
			
			if(!$date||!$loc||!$shelf)  die("Please select Stock Take Date, Location & Shelf");
            
            $sql = "select stp.*,si.mcode,si.artno,si.link_code,si.description,si.selling_price,si.sku_item_code,user.u,uom.code as uom_code $add_col
			from stock_take_pre stp
			LEFT JOIN sku_items si on stp.sku_item_id=si.id
			LEFT JOIN user on user.id = stp.user_id
			left join uom on uom.id=si.packing_uom_id
			$add_left_join
			$filter order by stp.location, stp.shelf, stp.id";
			//print $sql;
		}else{  // print sheet
            $filter = array();
            $filter[]= "stp.date = ".ms($_REQUEST['date']);
            $filter[]= "stp.location between ".ms($_REQUEST['loc_from'])." and ".ms($_REQUEST['loc_to']);
            $filter[]= "stp.shelf between ".ms($_REQUEST['shelf_from'])." and ".ms($_REQUEST['shelf_to']);
            $filter[] = "stp.branch_id=$branch_id and stp.imported=0 and stp.is_fresh_market=1";
            
			if($_REQUEST['p_sku_type']){
                $filter[]= "sku.sku_type= ".ms($_REQUEST['p_sku_type']);
			    $sku_join="LEFT JOIN sku ON si.sku_id=sku.id";
			}

            //for stock take count sheet
			if ($config['stock_take_count_sheet']){
				if($_REQUEST['count_sheet']){
					$stock_count_sheet = $_REQUEST['count_sheet'];
				}else{
					die("Please enter stock count sheet no.");
				}
			}
			
			$filter = "where ".join(' and ', $filter);

            $sql = "select stp.*,si.mcode,si.artno,si.link_code,si.description,si.selling_price,si.sku_item_code,user.u,uom.code as uom_code
			from stock_take_pre stp
			LEFT JOIN sku_items si ON stp.sku_item_id=si.id
			LEFT JOIN user on user.id = stp.user_id
			left join uom on uom.id=si.packing_uom_id
			$sku_join
			$filter
			order by stp.location,stp.shelf,stp.id";
		}
		
		$q1 = $con->sql_query($sql) or die(mysql_error());
        while($r = $con->sql_fetchrow($q1)){
			$upd = array();
			$upd['date'] = $r['date'];
            $upd['branch_id'] = $r['branch_id'];
            $upd['sku_item_code']=$r['sku_item_code'];
            $this->get_cost_selling($upd);
            $r['selling_price'] = $upd['selling'];
            $table[$r['location']][$r['shelf']][] = $r;
            $user_create[$r['location']][$r['shelf']][$r['u']] = $r['u'];

		}
		$con->sql_freeresult($q1);

		//Get Login User Name
		$sql2="select u from user where id=".$sessioninfo['id'];
		$q2 = $con->sql_query($sql2) or die(mysql_error());
		$r = $con->sql_fetchrow($q2);
		$username=$r['u'];
		$con->sql_freeresult($q2);

		$item_per_page = $config['stock_take_print_item_row_per_page'] ? $config['stock_take_print_item_row_per_page'] : 22;
		if($table){
			foreach($table as $loc=>$shelf_list){
				foreach($shelf_list as $shelf=>$items){
				    //$smarty->clear_all_assign();
				    $smarty->assign("branch",$branch);
				    $smarty->assign("sheet", $rpt_type);
				    $rs = $user_create[$loc][$shelf];

				    $uc = array();
				    if($rs){
			            $uc[] = current($rs);   // get the first user
			            if(count($rs)>1){   // if more than 1 user
			                $uc[] = end($rs);   // get the last user
			            }
			        }
				    $smarty->assign("user_create", $uc);

                    $totalpage = ceil(count($items)/$item_per_page);
                    $form = array();
                    $form['date'] = $date;
                    $form['location'] = $loc;
                    $form['shelf'] = $shelf;
                    $smarty->assign('form', $form);

				    for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
				        $smarty->assign("PAGE_SIZE", $item_per_page);
						$smarty->assign("is_lastpage", ($page >= $totalpage));
				        $smarty->assign("page", "Page $page of $totalpage");
				        $smarty->assign("start_counter", $i);
				        $smarty->assign("items", array_slice($items,$i,$item_per_page));
						$smarty->assign("user_name",$username);

						if ($rpt_type && $config['stock_take_count_sheet']){
							$smarty->assign("stock_count_sheet",str_pad($stock_count_sheet,5,'0',STR_PAD_LEFT));
							$stock_count_sheet+=1;
						}
						
				        $smarty->display("admin.stock_take.print.tpl");
						$smarty->assign("skip_header",1);
				    }
				}
			}
		}else   print "No Data";
	}
	
	private function get_cost_selling(&$row)
    {
      	if (preg_match('/^M/', $row['sku_item_code'])) return;
      	global $con, $config;

      	if ($row['cost']==0)
      	{
      		// todo: use avg grn cost
      		$con->sql_query("select sku_item_code, description, cost_price, grn_cost, avg_cost, date from sku_items
      			left join sku_items_cost_history on (sku_item_id = sku_items.id and date < '$row[date]' and branch_id=$row[branch_id])
      			where sku_item_code = '$row[sku_item_code]'
      			order by date desc
      			limit 1");
      		$r = $con->sql_fetchrow();
      		$con->sql_freeresult();
      		if ($config['stock_take_cost']!='avg')
      		{
      			$row['cost'] = ($r['grn_cost']>0) ? $r['grn_cost'] : $r['cost_price'];
      		}
      		else
      		{
      			$row['cost'] = ($r['avg_cost']>0) ? $r['avg_cost'] : $r['cost_price'];
      		}
      		//if ($_REQUEST['a']!='update') print "Cost: $row[cost] ";
      	}

      	if ($row['selling']==0)
      	{
      		$con->sql_query("select sku_item_code, description, selling_price, price, sku_items_price_history.added from sku_items
      			left join sku_items_price_history on (sku_item_id = sku_items.id and sku_items_price_history.added < date_add('$row[date]', interval 1 day)  and branch_id=$row[branch_id])
      			where sku_item_code = '$row[sku_item_code]'
      			order by added desc
      			limit 1");
      		$r = $con->sql_fetchrow();
      		$con->sql_freeresult();
      		$row['selling'] = ($r['price'] != '') ? $r['price'] : $r['selling_price'];
      		//if ($_REQUEST['a']!='update') print "Selling: $row[selling] ";
      	}
    }
    
    function ajax_load_shelf_range(){
        $this->load_shelf_list($this->branch_id, $_REQUEST['date'], $_REQUEST['loc_from'], $_REQUEST['loc_to']);
	}
	
	function print_sheet(){
		$this->print_report('sheet');
	}
	
	function import_page(){
		global $con, $smarty, $sessioninfo;

		if($this->can_select_branch)	$this->load_branches();
		
        if($_REQUEST['t']||BRANCH_CODE!="HQ"){
			$bid = mi($this->branch_id);
			$date_data['import'] = $this->check_available_stock_take_date($bid, 0, true);
			$date_data['reset'] = $this->check_available_stock_take_date($bid, 1, true);
			$smarty->assign('date_data', $date_data);
		}

		$smarty->display("admin.fresh_market_stock_take.import_page.tpl");
	}
	
	function check_available_stock_take_date($bid = 0, $check_imported = -1, $sql_only = false)
    {
        global $con, $smarty;

		$imported = ($check_imported>-1) ? $check_imported : mi($_REQUEST['imported']);
		$branch_id = $bid>0 ? $bid : $this->branch_id;

		$filter[] = "stp.imported=$imported";
		$filter[] = "stp.branch_id=$branch_id";
		$filter[] = "stp.is_fresh_market=1";
		$filter = "where ".join(' and ', $filter);

        $con->sql_query("select distinct(date) from stock_take_pre stp $filter order by date desc");

        if(!$sql_only){
            $smarty->assign("available_date", $con->sql_fetchrowset());
            $smarty->assign("type", $imported);
            $smarty->display('admin.fresh_market_stock_take.import_page.stock_take_date.tpl');
		}else   return $con->sql_fetchrowset();
    }
    
    function check_available_stock_take_sku()
	{
        global $con, $smarty;

		$imported = mi($_REQUEST['imported']);
		$date = ms($_REQUEST['date']);
		$branch_id = $this->branch_id;

		$filter[] = "imported=$imported";
		$filter[] = "branch_id=$branch_id";
		$filter[] = "date=$date";
		$filter = "where ".join(' and ', $filter)." and stp.is_fresh_market=1";

		print "<b>SKU Type</b><select name='sku_type'>";

        $con->sql_query("select sku.sku_type from stock_take_pre stp
						left join sku_items si on stp.sku_item_id=si.id
						left join sku on si.sku_id=sku.id
						$filter group by sku.sku_type order by sku.sku_type");

		if ($con->sql_numrows()>1)
		    print "<option value=''>-- All --</option>";
		elseif ($con->sql_numrows()<1)
		    print "<option value=''>No Data</option>";

		while ($s=$con->sql_fetchrow()){
	    	print "<option value='".$s['sku_type']."'>".$s['sku_type']."</option>";
		}

		print "</select>";
	}
	
	function import_stock_take()
    {
        global $con, $smarty,$sessioninfo;

        $branch_id = $this->branch_id;
        $bcode = get_branch_code($branch_id);
        $date = $_REQUEST['stock_take_date'];
        $sku_type = $_REQUEST['sku_type'];
        $fill_zero_options = $_REQUEST['fill_zero_options'];
		$sku_item_id_list = array();

		// get items need to import
		$filter = array();
		$filter[] = "stp.branch_id=$branch_id and stp.date=".ms($date)." and stp.imported=0 and stp.is_fresh_market=1";
		if($sku_type){
			$filter[] = "sku.sku_type=".ms($sku_type);
			$st_filter = "and sku.sku_type=".ms($sku_type);
		}
		$filter = "where ".join(' and ', $filter);
		
        $rs = $con->sql_query("select stp.*,si.sku_item_code, si.sku_id, si.id as sku_item_id
		from stock_take_pre stp
        LEFT JOIN sku_items si on stp.sku_item_id = si.id
        left join sku on sku.id=si.sku_id
		$filter");

		// sum qty for same item
        while($r = $con->sql_fetchassoc($rs))
        {
            $key = mi($r['branch_id'])."_".date('Ymd',strtotime($r['date']))."_".mi($r['sku_item_id'])."_".strtoupper($r['location'])."_".strtoupper($r['shelf']);
            if(isset($data[$key]))  $data[$key]['qty'] += $r['qty'];
            else	$data[$key] = $r;
        }
		$con->sql_freeresult($rs);

		// start import stock take
		$user_data = array();
		
		// get current max item no
		$q1 = $con->sql_query("select max(item_no) from stock_check where branch_id=".mi($branch_id)." and date=".ms($date));
        $n = mi($con->sql_fetchfield(0));
        $con->sql_freeresult($q1);

        foreach($data as $val){
			$n++;
            $upd = array();
            $upd['sku_item_code']=$val['sku_item_code'];
            $upd['is_fresh_market']=$val['is_fresh_market'];
          	$upd['date'] = trim($val['date']);
          	$upd['branch_id'] = trim($val['branch_id']);
          	$upd['cost'] = "0";
          	$upd['selling'] = "0";
          	$this->get_cost_selling($upd);
          	$upd['item_no'] = $n;

          	if(!isset($user_data[$val['user_id']])){    // get username
                $r_user = $con->sql_query("select u from user where id=".mi($val['user_id']));
            	$u_r = $con->sql_fetchrow($r_user);
            	$user_data[$val['user_id']] = $u_r['u'];
			}

          	$upd['scanned_by'] = trim($user_data[$val['user_id']]);
          	$upd['location'] = trim($val['location']);
          	$upd['shelf_no'] = trim($val['shelf']);
          	$upd['qty'] = mf($val['qty']);

          	$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd)) or die(mysql_error());
			$sku_item_id_list[$val['sku_item_id']] = $val['sku_item_id'];
			
			// zerolise for child
			$q1 = $con->sql_query("select id,sku_item_code from sku_items where sku_id=".mi($val['sku_id'])." and sku_item_code<>".ms($val['sku_item_code']));
			while($child_sku = $con->sql_fetchassoc($q1)){
				$n++;
				$upd['sku_item_code'] = $child_sku['sku_item_code'];
				$upd['item_no'] = $n;
				$upd['qty'] = 0;
				
				$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
				$sku_item_id_list[$child_sku['id']] = $child_sku['id'];
			}
			$con->sql_freeresult($q1);
			
			if(count($sku_item_id_list)>=1000){
				$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")");
				$sku_item_id_list = array();
			}
        }

        // set item to imported
        $con->sql_query("update stock_take_pre stp
		left join sku_items si on si.id=stp.sku_item_id
		left join sku on sku.id=si.sku_id
		set stp.imported=1 $filter");
		
		// found out user tick auto fill zero
		if($fill_zero_options == "fill_zero"){
			$upd = array();
			$upd['branch_id'] = $branch_id;
			$upd['date'] = $date;
			
			$cost_params = array('cost');
				
			$q1 = $con->sql_query("select si.id, si.sku_item_code
					from sku_items si
					left join sku on sku.id = si.sku_id
					left join category_cache cc on cc.category_id=sku.category_id
					where si.sku_item_code not in (select distinct sc.sku_item_code from stock_check sc where sc.is_fresh_market=1 and sc.branch_id=$branch_id and sc.date=".ms($date).") $st_filter and (sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))
					order by si.sku_item_code");
			while($si = $con->sql_fetchassoc($q1)){
				$upd['sku_item_code'] = $si['sku_item_code'];
				$tmp = get_sku_item_cost_selling($branch_id,$si['id'],$date, $cost_params);
				$upd['cost'] = $tmp['cost'];
				
				$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
				
				$sku_item_id_list[$si['id']] = $si['id'];
				$n++;
				
				if(count($sku_item_id_list)>=1000){
					$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")");
					$sku_item_id_list = array();
				}
			}
			$con->sql_freeresult($q_si);
		}

		if($sku_item_id_list){
			for($i=0; $i<count($sku_item_id_list); $i+=1000){
                $con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
			}
		}

        log_br($sessioninfo['id'], 'FRESH MARKET STOCK TAKE', $branch_id, "Import Fresh Market Stock Take (Branch#".$branch_id.", Date#$date, Fill Zero Option: $fill_zero_options)");
        $msg = "$bcode ($date) Import Success, $n items imported.";
        header("Location: $_SERVER[PHP_SELF]?a=import_page&t=import&branch_id=$branch_id&msg=$msg");
        exit;
    }
    
    function reset_stock_take()
    {
        global $con, $smarty,$sessioninfo;

        $branch_id = $this->branch_id;
        $bcode = get_branch_code($branch_id);
        $date = $_REQUEST['stock_take_date'];
        $sku_type= $_REQUEST['sku_type'];
        
        $filter[] = "sc.branch_id=$branch_id and sc.date=".ms($date)." and sc.is_fresh_market=1";
        $filter2[] = "stp.branch_id=$branch_id and stp.date=".ms($date)." and stp.is_fresh_market=1";
        if($sku_type){
            $filter[] = "sku.sku_type=".ms($sku_type);
            $filter2[] = "sku.sku_type=".ms($sku_type);
		}   
        $filter = "where ".join(' and ', $filter);
        $filter2 = "where ".join(' and ', $filter2);
        
        $result = $con->sql_query("select distinct(si.id) as sku_items_id
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join stock_check sc on sc.sku_item_code = si.sku_item_code
		$filter") or die(mysql_error());
		$c = 0;
        while($r = $con->sql_fetchrow($result))
        {
            $sku_items_id[] = $r['sku_items_id'];
            if (count($sku_items_id)>1000)
			{
				// possible too many item...
	        	$con->sql_query("update sku_items_cost set changed = '1' where branch_id=$branch_id and sku_item_id in (".join(',',$sku_items_id).")") or die(mysql_error());
	        	$sku_items_id = array();
        	}
        	$c++;
        }
        if (count($sku_items_id)>0) $con->sql_query("update sku_items_cost set changed='1' where branch_id = $branch_id and sku_item_id in (".join(',',$sku_items_id).")");
        // delete from stock check
        $con->sql_query("delete sc.* from stock_check sc
		left join sku_items si on si.sku_item_code=sc.sku_item_code
		left join sku on sku.id=si.sku_id
		$filter") ;
		// update stock take pre to makr as not yet import
        $con->sql_query("update stock_take_pre stp
		left join sku_items si on si.id=stp.sku_item_id
		left join sku on sku.id=si.sku_id
		set stp.imported=0 $filter2");

        log_br($sessioninfo['id'], 'FRESH MARKET STOCK TAKE', $branch_id, "Reset Fresh Market Stock Take (Branch#".$branch_id.", Date#$_REQUEST[branch_date])");
        $msg = "$bcode ($date) Reset Success, $c items reset.";
        header("Location: $_SERVER[PHP_SELF]?a=import_page&t=reset&branch_id=$branch_id&imported=1&msg=$msg");
        exit;
    }
    
    function ajax_load_possible_item(){
		global $con, $smarty;
		
		$branch_id = $this->branch_id;
		$this->load_possible_item_by_condition(true);
		$this->display('admin.fresh_market_stock_take.open.possible_items.tpl');
	}
	
	function load_possible_item_by_condition($sqlonly = false){
		global $con, $smarty;
		
		/* condition
		    0 - all
		    1 - Got GRN since last stock take
		    2 - Got Sales since last stock take
		    3 - Got Write-off since last stock take
		*/
		$condition = mi($_REQUEST['condition']);
		$branch_id = mi($this->branch_id);
		$exclude_stock_taked_items = mi($_REQUEST['exclude_stock_taked_items']);
		$date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d');

		//$filter[] = "si2.active=1 and sc.is_fresh_market=1 and sc.branch_id=$branch_id";
		//$filter[] = "sc.date=(select sc2.date from stock_check sc2 where sc2.branch_id=sc.branch_id and sc2.sku_item_code=sc.sku_item_code and sc2.date<".ms($date)." order by sc2.date desc limit 1)";
		
		$filter[] = "(sku.is_fresh_market='yes' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='yes'))";
		$filter[] = "si.active=1 and si.is_parent=1";
		
		$filter = "where ".join(' and ', $filter);
		//if($having) $having = "having ".join(' and ', $having);
		//else    $having = '';
		$sql = "select si.id as sku_item_id, si.sku_item_code, si.artno, si.mcode, si.description, uom.code as uom_code, si.sku_id
		from sku_items si
		left join sku on sku.id=si.sku_id
		left join category_cache cc on sku.category_id = cc.category_id
		left join uom on uom.id=si.packing_uom_id
		$filter
		";
		
		/*$sql = "select si2.id as sku_item_id,si2.sku_item_code, si2.artno, si2.mcode, si2.description, uom.code as uom_code, sum(sc.qty) as sc_qty,sc.date as sc_date, si2.sku_id
from stock_check sc
left join sku_items si on si.sku_item_code=sc.sku_item_code
left join sku_items si2 on si2.sku_id=si.sku_id and si2.is_parent=1
left join uom on uom.id=si2.packing_uom_id
left join sku on sku.id=si2.sku_id
$filter
group by si2.sku_id";*/

		//print $sql;
		$q1 = $con->sql_query($sql);
		while($r = $con->sql_fetchassoc($q1)){
		    $sid = mi($r['sku_item_id']);
		    $sku_id = mi($r['sku_id']);
		    
		    // get last stock take
		    $con->sql_query("select stp.qty as sc_qty, stp.date as sc_date
			from stock_take_pre stp
			where stp.branch_id=$branch_id and stp.date<=".ms($date)." and stp.sku_item_id=$sid and stp.is_fresh_market=1 order by stp.date desc limit 1");
		    $temp = $con->sql_fetchassoc();
		    $con->sql_freeresult();
		    if($temp){
                $r['sc_qty'] = $temp['sc_qty'];
		    	$r['sc_date'] = $temp['sc_date'];
			}
			unset($temp);
		    
		    // exclude this item if this item already in this stock check
		    if($exclude_stock_taked_items && $r['sc_date']){
				if(strtotime($r['sc_date'])==strtotime($date))  continue;   // same date, exclude
			}

            $sc_date =$r['sc_date'];
            
		    if($condition){
		        $sql_chk = '';
				if($condition==1){  // Got GRN since last stock take
					$sql_chk = "select sum(if (gi.acc_ctn is null and gi.acc_pcs is null, gi.ctn *rcv_uom.fraction + gi.pcs, gi.acc_ctn *rcv_uom.fraction + gi.acc_pcs)) as rcv_qty
from grn_items gi
left join grn on grn.branch_id=gi.branch_id and grn.id=gi.grn_id
left join grr on grr.branch_id=grn.branch_id and grr.id=grn.grr_id
left join sku_items si on si.id=gi.sku_item_id
left join uom rcv_uom on rcv_uom.id=gi.uom_id
where si.sku_id=$sku_id and grn.branch_id=$branch_id and grn.active=1 and grn.status=1 and grn.approved=1 and grr.rcv_date between ".ms($sc_date)." and ".ms($date)." and grr.active=1
having rcv_qty>0";
				}elseif($condition==2){ // Got Sales since last stock take
					$sql_chk = "select sum(qty) as qty
from sku_items_sales_cache_b".$branch_id." tbl
left join sku_items si on si.id=tbl.sku_item_id
where si.sku_id=$sku_id and tbl.date between ".ms($sc_date)." and ".ms($date)."
having qty>0";
				}elseif($condition==3){ // Got Write-off since last stock take
					$sql_chk = "select sum(qty) as qty
from adjustment_items adji
left join adjustment adj on adj.branch_id=adji.branch_id and adj.id=adji.adjustment_id
left join sku_items si on si.id=adji.sku_item_id
where si.sku_id=$sku_id and adj.branch_id=$branch_id and adj.active=1 and adj.status=1 and adj.approved=1 and adj.adjustment_date between ".ms($sc_date)." and ".ms($date)."
having qty>0";
				}
				if($sql_chk){
					$q_chk = $con->sql_query($sql_chk);
					if($con->sql_numrows($q_chk)<=0){   // does not fit the condition
						$con->sql_freeresult($q_chk);
						continue;
					}
				}
			}
			$items[] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign('items', $items);
		if(!$sqlonly)	$this->display('admin.fresh_market_stock_take.open.possible_items.list.tpl');
		return $items;
	}
	
	function ajax_direct_add_stock_take_list_item(){
		global $con, $smarty, $sessioninfo, $appCore;
		
		$upd = array();
		$branch_id = $this->branch_id;
		$upd['branch_id'] = $branch_id;
		$upd['date'] = $_REQUEST['date'];
		$upd['location'] = trim($_REQUEST['location']);
		$upd['shelf'] = trim($_REQUEST['shelf']);
		$sid_list = $_REQUEST['sid_list'];
		$qty_list = $_REQUEST['qty_list'];
		$upd['is_fresh_market'] = 1;
		
		// check got all needed fields
		if(!$upd['branch_id']||!$upd['date']||!$upd['location']||!$upd['shelf']||!$sid_list)  die("Add item failed.");
		
		// check valid sku
		//$con->sql_query("select id from sku_items where id=$upd[sku_item_id]");
		//if(!$con->sql_numrows())    die("Invalid SKU.");
		//$con->sql_freeresult();
		
		$upd['user_id'] = $sessioninfo['id'];
		$new_id_list = array();
		foreach($sid_list as $k=>$sid){
			$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($branch_id));
			$upd['sku_item_id'] = mi($sid);
			$upd['qty'] = mf($qty_list[$k]);
			
			$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd));
			$new_id_list[] = mi($upd['id']);
		}
		if(!$new_id_list)   die('No item added.');
		
		$filter = "where stp.branch_id=$branch_id and stp.id in (".join(',',$new_id_list).")";
		// load the item to show
		$sb_tbl = "stock_balance_b".mi($branch_id)."_".date('Y', strtotime($upd['date']));
		// check whether stock balance table exists
		$sb_exists = $con->sql_query("explain $sb_tbl",false,false);
		if($sb_exists){
			$add_left_join = "left join $sb_tbl sb on sb.sku_item_id=stp.sku_item_id and stp.date between sb.from_date and sb.to_date";
			$add_col = ",sb.qty as sb_qty";
		}

		$sql = "select stp.*, si.description, si.sku_item_code, si.artno,si.mcode,user.u,uom.code as uom_code, si.doc_allow_decimal $add_col
		from stock_take_pre stp
		left join sku_items si on si.id=stp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join uom on uom.id=si.packing_uom_id
		left join user on user.id=stp.user_id
		$add_left_join
		$filter order by stp.id";
		
		$q1 = $con->sql_query($sql);
		while($item = $con->sql_fetchrow($q1)){
            $smarty->assign('item', $item);
			$ret['html'] .= $smarty->fetch('admin.fresh_market_stock_take.list.item_row.tpl');
		}
		$con->sql_freeresult($q1);
		print json_encode($ret);
	}
	
	function change_batch(){
		global $con;
		
		// change title
		$this->update_title($this->title .' - Change Batch');
		
		if($this->can_select_branch)	$this->load_branches();
		$dl = $this->ajax_load_date(true);
		
		$this->display('admin.fresh_market_stock_take.change_batch.tpl');
	}
	
	function ajax_load_date($sqlonly = false){
		global $con, $sessioninfo, $smarty;
		$branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		$con->sql_query("select distinct(date) from stock_take_pre where branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=1 order by date desc");
		
		$date_list = array();
		while($r = $con->sql_fetchrow()){
			$date_list[] = $r;
		}
		$con->sql_freeresult();
		
		if(!$sqlonly){
			print "<select name='dat' onchange='load_location(this.value)' size='10' style='width:100%;'>";
			foreach($date_list as $r){
				print "<option>".$r[0]."</option>";
			}
			print "</select>";
		}
		
		$smarty->assign('dat', $date_list);
		return $date_list;
	}
	
	function load_location($sqlonly = false)
	{
		global $con, $smarty,$sessioninfo;
		$date = $_REQUEST['d'];
		$branch_id = mi($this->branch_id);

		//get location
		$rs = $con->sql_query("select distinct(location) from stock_take_pre where date=".ms($date)." and branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=1 order by location");
		while ($r = $con->sql_fetchrow($rs))
		{
		    $loc[]=$r;
		}
		$con->sql_freeresult($rs);

		if(!$sqlonly){
			print "<select name=loc onchange=load_shelf(this.value) size=10 style='width:100%;'>";
			foreach($loc as $val)
			{
			  print "<option value=\"$val[location]\"";
			  if ($_REQUEST['location']  == $val['location'])
			  {
			       print "selected";
			  }
			  print ">".strtoupper($val['location'])."</option>";
			}
			print "</select>";
		}
		
		return $loc;
		//$smarty->assign("loc", $loc);
		//$smarty->display("admin.stock_take.loc.tpl");
	}
	
	function load_shelf($sqlonly = false)
	{
		global $con, $smarty,$sessioninfo;
		$location = $_REQUEST['loc'];
		$date = $_REQUEST['dat'];
		$branch_id = $this->branch_id;

		//get location
		$rs = $con->sql_query("select distinct(shelf) from stock_take_pre where location = ".ms($location)." and date = ".ms($date)." and branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=1 order by shelf");

		while ($r = $con->sql_fetchrow($rs))
		{
		    $shelf[]=$r;
		}
		$con->sql_freeresult($rs);

		if(!$sqlonly){
			print "<select name=shelf onchange=show_record() size=10 style='width:100%;'>";
			foreach($shelf as $val)
			{
			  print "<option value=\"$val[shelf]\"";
			  if($_REQUEST['shelf']  == $val['shelf'])
			  {
			      print "selected";
			  }
			  print ">".strtoupper($val['shelf'])."</option>";
			}
			print "</select>";
		}
		
		return $shelf;
	  //$smarty->assign("shelf", $shelf);

	  //$smarty->display("admin.stock_take.shelf.tpl");
	}
	
	function ajax_load_change_batch_popup(){
		global $con, $smarty, $sessioninfo;
		
		$branch_id = $this->branch_id;
		$date = trim($_REQUEST['date']);
		$loc = trim($_REQUEST['loc']);
		$shelf = trim($_REQUEST['shelf']);
		
		$filter = array();
		if(!$branch_id)	die("Invalid Branch");
		if(!$date)	die("Invalid Date");
		
		$filter[] = "branch_id=".mi($this->branch_id)." and date=".ms($date);
		$filter[] = "imported=0 and is_fresh_market=1";
		if($loc){
			$filter[] = "location=".ms($loc);
			if($shelf)	$filter[] = "shelf=".ms($shelf);
		}
		$filter = 'where '.join(' and ', $filter);
		
		$sql = "select count(*) from stock_take_pre $filter";
		$con->sql_query($sql);
		$item_count = mi($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		if($item_count<=0)	die("There is no item in this batch.");
		
		$this->data = array();
		$this->data['item_count'] = $item_count;
		$this->data['branch_id'] = $branch_id;
		$this->data['date'] = $date;
		$this->data['loc'] = $loc;
		$this->data['shelf'] = $shelf;
		$this->data['branch_code'] = get_branch_code($branch_id);
		$smarty->assign('data', $this->data);
		$this->display('admin.fresh_market_stock_take.change_batch.popup.tpl');
	}
	
	function batch_update(){
		global $con, $smarty, $sessioninfo;
		
		//print_r($_REQUEST);
		
		$bid = $this->branch_id;
		$o_date = trim($_REQUEST['o_date']);
		$o_loc = trim($_REQUEST['o_loc']);
		$o_shelf = trim($_REQUEST['o_shelf']);
		$n_date = trim($_REQUEST['n_date']);
		$n_loc = trim($_REQUEST['n_loc']);
		$n_shelf = trim($_REQUEST['n_shelf']);
		$keep_o_loc = mi($_REQUEST['keep_o_loc']);
		$keep_o_shelf = mi($_REQUEST['keep_o_shelf']);
		$err_msg = '';
		
		// check same old and new info
		if($o_date == $n_date && ($keep_o_loc || ($o_loc == $n_loc)) && ($keep_o_shelf || ($o_shelf == $n_shelf))){
			$err_msg = "All info are same, nothing to update.";
		}
		
		// check valid parameters
		if(!$err_msg){
			if(date("Y", strtotime($n_date))<2007)	$err_msg = "Date cannot less than year 2007";
			if(!$err_msg && !$keep_o_loc && !$n_loc)	$err_msg = "New location cannot be empty";
			if(!$err_msg && !$keep_o_shelf && !$n_shelf)	$err_msg = "New Shelf cannot be empty";
		}
		
		// check old item
		if(!$err_msg){
			$filter = array();
			$filter[] = "branch_id=$bid and date=".ms($o_date);
			$filter[] = "imported=0 and is_fresh_market=1";
			if($o_loc){
				$filter[] = "location=".ms($o_loc);
				if($o_shelf)	$filter[] = "shelf=".ms($o_shelf);
			}
			$filter = "where ".join(' and ', $filter);
			$sql = "select * from stock_take_pre $filter limit 1";
			//print $sql;
			$con->sql_query($sql);
			$tmp = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			if(!$tmp){
				$err_msg = "No item found for branch#".get_branch_code($bid).", Date#$o_date";
				if($o_loc){
					$err_msg .= ", Location#$o_loc";
					if($o_shelf)	$err_msg .= ", Shelf#$o_shelf";
				}
			}
		}
		
		// got error
		if($err_msg){	
			header("Location: $_SERVER[PHP_SELF]?a=change_batch&err_msg=".urlencode($err_msg));
			exit;
		}
		
		$subject= "Stock Take Branch:".get_branch_code($bid).", Date:$o_date";
		if($o_loc)	$subject .= ", Location:$o_loc";
		if($o_shelf)	$subject .= ", Shelf:$o_shelf";
		$subject .= " updated to ";
		
		$upd = array();
		if($o_date != $n_date){
			$upd['date'] = $n_date;
			$subject .= " Date:$n_date";
		}	
		if(!$keep_o_loc && $n_loc){
			$subject .= ($upd ? ',':'')." Location:$n_loc";
			$upd['location'] = $n_loc;
		}	
		if(!$keep_o_shelf && $n_shelf){
			$subject .= ($upd ? ',':'')." Shelf:$n_shelf";
			$upd['shelf'] = $n_shelf;
		}	
		
		$con->sql_query("update stock_take_pre set ".mysql_update_by_field($upd)." $filter");
		$affected = $con->sql_affectedrows();
		$subject .= "<br />$affected Items Updated.";
		$title = "Stock Take Updated";
		$redirect_url = $_SERVER['PHP_SELF']."?a=change_batch";
		
		log_br($sessioninfo['id'], 'Stock Take', $sessioninfo['id'], $subject);
		
		display_redir($redirect_url, $title, $subject);
	}
	
}

$FRESH_MARKET_STOCK_TAKE = new FRESH_MARKET_STOCK_TAKE('Fresh Market Stock Take');
?>
