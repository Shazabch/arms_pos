<?php
/*
12/22/2009 10:01:45 AM Andy
- Fix after import stock take, system din't update stock balance

2/25/2010 2:50:00 PM Jeff
- touch up print report and checklist format

5/10/2010 12:06:33 PM Andy
- Remove an unuse sql which cause module blank screen.

5/11/2010 12:19:15 PM yinsee
- add STOCK TAKE privilege

5/12/2010 6:10:55 PM Andy
- add log for reset stock take.
- fix error log message for import stock take.
- add "Set quantity to zero for items not in stock take" at stock take import
- Split add/edit and import/reset stock take.

5/13/2010 2:56:48 PM Andy
- Add column "stock balance" and "variance".

5/18/2010 5:48:42 PM Andy
- Stock Take Printing now separate by shelf.

5/20/2010 3:20:38 PM Andy
- Add sorting for location and shelf range.
- Shelf range change to only show those between selected location.

5/21/2010 10:00:05 AM Andy
- Stock Take print checklist and print report add to allow system to use config to determine item row per page, default 22 rows.

6/8/2010 2:40:31 PM Alex
- Fix quatity bugs and shelf bugs

7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.

8/19/2010 3:33:21 PM Alex
- Add SKU type filter

8/24/2010 1:53:42 PM Alex
- Group by branch, date, location, shelf, items while save from stock_take_pre to stock_check

9/2/2010 3:45:11 PM Andy
- Print report add uom.

9/7/2010 4:55:18 PM Alex
- add sku scan at main page and stock count sheet no.

9/13/2010 5:23:24 PM Alex
- add $config['sku_type_outright'] control sku type stock while scanning and sorting item list same as frontend list

9/15/2010 12:48:14 PM Alex
- sort report stock check follow by scanned items sequence

8/19/2010 3:33:21 PM Alex
- Add SKU type filter

9/23/2010 11:13:48 AM Andy
- Add cost at stock take printing. (need config and privilege)
- Add reassign sessioninfo and config to smarty at print report function.

9/24/2010 4:55:06 PM Alex
- remove joining stock_balance temparory

10/15/2010 10:42:17 AM Alex
- remove sku type filter due to fill zero qty problem

3/15/2011 6:13:29 PM Alex
- add time checking to know maximum time used for importing and reseting
- fix joining table bugs while import or revert

6/8/2011 10:51:55 AM Andy
- Add artno column at stock take.

6/24/2011 3:14:26 PM Andy
- Make all branch default sort by sequence, code.

7/1/2011 4:59:46 PM Alex
- add show trade discount code for consignment items only

7/14/2011 12:44:31 PM Andy
- Add price type to stock take print sheet.

9/12/2011 12:06:11 PM Andy
- Add can change date,shelf and location for added stock take.
- Fix some bugs when logging user action.

9/27/2011 12:15:43 PM Justin
- Applied when get item list, pick up sku item's doc_allow_decimal.
- Fixed the sku type filter option is not working while filter for those added sku items or print report.
- Removed some of the codes that system doesn't use anymore.
- Fixed the artno cannot show up while multi add new sku items (add new stock).

12/2/2011 12:16:12 PM Justin
- Fixed the bugs where system could not delete sku item when doing stock take for sub branches.
- Fixed the qty calculation bugs.

12/29/2011 3:34:32 PM Justin
- Fixed the bugs where branch does not sort by sequence.

2/16/2012 6:01:15 PM Alex
- add checking sku_item_id is not empty while import items
- add new options of auto add zero on same SKU parent

2/20/2012 3:04:14 PM Alex
- simplify the script to use same sql
- add cost_price for stock_take_pre

3/22/2012 12:00:31 PM Alex
- while import, get latest cost if stock_take_pre cost is blank

4/5/2012 1:08:56 PM Andy
- Change auto fill zero stock take will save the cost instead of zero cost.

4/9/2012 11:54:17 AM Andy
- Fix import stock take does not recalculate stock balance bugs.

1/9/2013 5:53 PM Justin
- Enhanced to have new fill zero option "auto fill zero by categories".

3/28/2013 10:18 AM Justin
- Bug fixed auto fill zero for same parent is not working properly.

6/10/2013 2:44 PM Justin
- Bug fixed on system capture empty info while storing log.
- Enhanced to have SKU Type filter option while action is selected.

07/17/2013 12:08 PM Justin
- Enhanced to have vendor filter for fill zero for selected category.

2/14/2014 4:43 PM Justin
- Enhanced to calculate and show actual variance at the last item when an item have been insert multiple.

12/16/2015 3:18 PM DingRen
- add initial_branch_sb_table into loat_stp_data

08-Mar-2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

4/6/2016 10:32 AM Andy
- Enhanced to check if no item to import, will prompt error.

2/24/2017 5:41 PM Andy
- Fixed Change Batch not to move those imported stock take.

8/4/2017 16:38 PM Qiu Ying
- Bug fixed on remove "clear_all_assign" when printing

8/16/2017 10:03 AM Andy
- Cast item_no to integer when import.
- Increase memory limit to 1024M.

8/25/2017 9:54 AM Justin
- Enhanced to show error on another page when there is cost price variance while customer backend is using Average Cost or last cost with parent-child calculation.
- Enhanced to show error on another page when there is parent-child SKU family did not stock take at same date while choosing "no auto fill zero" option.

11/15/2017 3:51 PM Justin
- Enhanced not to auto zerolise for those fresh market items.

11/13/2018 10:00 AM Justin
- Enhanced to have new fill zero option "auto fill zero by brand".

3/26/2020 5:58 PM William
- Enhanced to insert id manually for stock_take_pre table that use auto increment.

5/13/2020 12:08 PM William
- Bug fixed on generate id and log.
*/
ini_set("display_errors",0);
set_time_limit(0);
include("include/common.php");
ini_set('memory_limit', '1024M');

session_start();
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('STOCK_TAKE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'STOCK_TAKE', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
$maintenance->check(1);
    
class Stock_Take extends Module
{
    function __construct($title, $template=''){
        global $config, $smarty,$con, $sessioninfo;
        
        if(BRANCH_CODE=='HQ' && $config['single_server_mode']){
            $this->can_select_branch = true;
            $smarty->assign('can_select_branch', 1);
            $this->branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		}else{
            $this->branch_id = $sessioninfo['branch_id'];
		}
		
		$this->sku_type = trim($_REQUEST['sku_type']);
		$this->shelf = $_REQUEST['shelf'];
		$this->location = $_REQUEST['location'];
		$this->date = $_REQUEST['date'];

		$con->sql_query("select * from sku_type order by code");
      	$sku_type = $con->sql_fetchrowset();
		$con->sql_freeresult();
		$smarty->assign('sku_type',$sku_type);

		$smarty->assign('config',$config);

		parent::__construct($title, $template);
	}
	
	private function load_branches(){
		global $con, $smarty;
		$con->sql_query("select * from branch order by sequence, code");
      	while ($r = $con->sql_fetchassoc()){
      	    $branches[]=$r;
      	}
      	$con->sql_freeresult();
      	$smarty->assign("branches", $branches);
      	return $branches;
	}
	
    function _default()
  	{
  		 global $smarty,$con,$sessioninfo;
        	  
//		 if($this->can_select_branch)   $branch_id = mi($_REQUEST['branch_id']);
//		 if(!$branch_id)    $branch_id = mi($sessioninfo['branch_id']);
		 if($this->date)
  		 { 
/* 
  		    $date=$_REQUEST['date'];

             $sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));

             $sql = "select stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,stock_take_pre.branch_id,sku_items.mcode,sku_items.artno,sku_items.description,sku_items.sku_item_code,user.u,sb.qty as sb_qty, sku_items.doc_allow_decimal,
					if (sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code
			 		 from stock_take_pre
			 		 LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
			 		 LEFT JOIN sku ON sku_items.sku_id=sku.id
					 LEFT JOIN user on user.id = stock_take_pre.user_id
					 left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
					 left join sku_items_price sp on sp.sku_item_id = sku_items.id and sp.branch_id = $branch_id
					 where stock_take_pre.date =".ms($_REQUEST['date'])." and
					 stock_take_pre.location = ".ms($_REQUEST['location'])." and
					 stock_take_pre.shelf =".ms($_REQUEST['shelf'])." and
					 stock_take_pre.branch_id=$branch_id and
					 stock_take_pre.imported = '0' and stock_take_pre.is_fresh_market=0 order by stock_take_pre.id";
			

             $con->sql_query($sql) or die(mysql_error());
             $table = $con->sql_fetchrowset();
	         $con->sql_freeresult();
*/	         
	         $table=$this->load_stp_data();
	         
             $smarty->assign("flows", $table);
             
             //get date
            $rs = $con->sql_query("select distinct(date) from stock_take_pre where branch_id=$this->branch_id and imported = '0' and stock_take_pre.is_fresh_market=0 order by date desc");
          	while ($r = $con->sql_fetchassoc($rs)){
          	    $dat[]=$r;
          	}
          	$con->sql_freeresult($rs);
          	
            $rs = $con->sql_query("select distinct(location) from stock_take_pre where date=".ms($this->date)." and branch_id=$this->branch_id and imported = '0' and stock_take_pre.is_fresh_market=0");
          	while ($r = $con->sql_fetchassoc($rs)){
          	    $loc[]=$r;
          	}
    		$con->sql_freeresult($rs);
    		
          	//get shelf
          	$rs = $con->sql_query("select distinct(shelf) from stock_take_pre where branch_id=$this->branch_id and date = ".ms($this->date)." and location= ".ms($this->location)." and imported = '0' and stock_take_pre.is_fresh_market=0 order by shelf");
          	while ($r = $con->sql_fetchassoc($rs)){
          	    $shelf[]=$r;
          	}
			$con->sql_freeresult($rs);
        }else{
            //get date
            $rs = $con->sql_query("select distinct(date) from stock_take_pre where branch_id=$this->branch_id and imported = '0' and stock_take_pre.is_fresh_market=0 order by date desc");
          	while ($r = $con->sql_fetchassoc($rs))
          	{
          	    $dat[]=$r;
          	}
          	$con->sql_freeresult($rs);
        }
        //check whether is HQ
        if(BRANCH_CODE=="HQ")
        {
            $this->load_branches();
        }else{
            /*$rs = $con->sql_query("select id from branch where code=".ms(BRANCH_CODE));
            $r = $con->sql_fetchrow($rs);
            $bran_id =  $r['id'];
            
            $rs2 = $con->sql_query("select distinct(date) from stock_take_pre where branch_id=".ms($sessioninfo['branch_id'])." and imported = '0'");
            while($r2 = $con->sql_fetchrow($rs2))
            {
                $bran_date[] = $r2['date'];
            }
            
            $smarty->assign("bran_date",$bran_date);*/
        }
    
        //load_shelf();

      	$smarty->assign("shelf", $shelf);
      	$smarty->assign("dat", $dat);
      	$smarty->assign("loc", $loc);
      	$smarty->assign("u", $sessioninfo['u']);
        $smarty->display('admin.stock_take.tpl');
  	}

	function load_stp_data(){
		global $con;
        $sb_tbl = "stock_balance_b".$this->branch_id."_".date("Y",strtotime($this->date));

        $prms = array();
        $prms['tbl'] = $sb_tbl;
        initial_branch_sb_table($prms);

		if ($this->sku_type != "") $sku_filter="sku.sku_type=".ms($this->sku_type)." and ";
		
		$sql = "select stp.branch_id,stp.date,stp.location,stp.shelf,stp.qty,stp.cost_price,stp.id,sku_items.mcode, sku_items.id as sku_item_id,
				sku_items.artno,sku_items.description,sku_items.sku_item_code,user.u,sb.qty as sb_qty, sku_items.doc_allow_decimal,
				if (sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code
				from stock_take_pre stp
				LEFT JOIN sku_items ON stp.sku_item_id=sku_items.id
				LEFT JOIN sku ON sku_items.sku_id=sku.id
				LEFT JOIN user on user.id = stp.user_id
				left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($this->date)." between sb.from_date and sb.to_date) or (".ms($this->date)." >= sb.from_date and sb.is_latest=1))
				left join sku_items_price sp on sp.sku_item_id = sku_items.id and sp.branch_id = ".mi($this->branch_id)."
				where stp.date =".ms($this->date)." and
				stp.location = ".ms($this->location)." and
				stp.shelf =".ms($this->shelf)." and
				stp.branch_id=".mi($this->branch_id)." and
				$sku_filter
				stp.imported = '0' and stp.is_fresh_market=0
				order by stp.id";
				
		$q1 = $con->sql_query($sql) or die(mysql_error());
		
		$si_list = $stp_qty = array();
		while($r = $con->sql_fetchassoc($q1)){
			$si_list[$r['sku_item_id']][$r['id']] = $r['id'];
			$stp_qty[$r['sku_item_id']] += $r['qty'];
			$table[$r['id']] = $r;
		}
        $con->sql_freeresult($q1);
		
		foreach($table as $row=>$r){
			$last_id = end($si_list[$r['sku_item_id']]);
			if($r['id'] == $last_id){
				$table[$r['id']]['variance'] = $stp_qty[$r['sku_item_id']] - $table[$r['id']]['sb_qty'];
			}else{
				$table[$r['id']]['variance'] = 0;
			}
			$table[$r['id']]['mid'] = $last_id;
		}
		
		return $table;
	}

	function open()
	{
	  	global $con,$smarty,$sessioninfo,$config;
	  	$id = intval($_REQUEST['id']);

	  	// open an existing data
	  	if($id>0){
	  		// load header
	  		$con->sql_query("select * from stock_take_pre where sku_item_id=".ms($id)." and branch_id = ".ms($sessioninfo['branch_id'])." and imported = '0' and stock_take_pre.is_fresh_market=0") or die(mysql_error());
	  		$form = $con->sql_fetchrow();
	  		if(!$form){
	  			print "Error: Invalid Sku ID";
	  			exit;
	  		}
	  	}

	  	if((BRANCH_CODE == "HQ") && $config['single_server_mode'])
	  	{
	        $con->sql_query("select code,id from branch order by sequence, code") or die(mysql_error());
	  		$smarty->assign('branches', $con->sql_fetchrowset());
	    }

	  	unset($_SESSION['scan_data']);

	  	$smarty->assign('form',$form);
		$smarty->assign('bid',$_REQUEST['branch_id']);
		$smarty->assign('dat',$_REQUEST['date']);
		$smarty->assign('loc',$_REQUEST['loc']);
		$smarty->assign('shelf',$_REQUEST['shelf']);
		//$smarty->assign('user',$sessioninfo['u']);
	  	$smarty->display('admin.stock_take.open.tpl');
	}
  
	function save()
	{
		global $con,$smarty,$sessioninfo,$config, $appCore, $LANG;

		$id = intval($_REQUEST['id']);

		$sku_id = $_REQUEST['sku_item_id'];

		//check code valid
		$con->sql_query("select * from sku_items where id = ".ms($sku_id));
		$f = $con->sql_fetchassoc();

		if(!$f)
		{
		  print "Invalid Code Entered";
		  exit;
		}
		
		/*if($config['monthly_closing'] && $config['monthly_closing_block_document_action']){
			$is_month_closed = $appCore->is_month_closed($_REQUEST['dat']);
			if($is_month_closed){
				print sprintf($LANG['BLOCK_MONTH_CLOSED_DOCUMENT'], "save");
				exit;
			}
		}*/

		if ($f['doc_allow_decimal']){
			$_REQUEST['qty2'] = round($_REQUEST['qty2'], $config['global_qty_decimal_points']);
		}else{
			$_REQUEST['qty2'] = mi($_REQUEST['qty2']);
		}
		$con->sql_freeresult();

		if($_REQUEST['bran'])
		{
		  $bran_id = $_REQUEST['bran'];
		}else
		{
		  $bran_id = $sessioninfo['branch_id'];
		}

		$upd = array();
		$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($bran_id));
		$upd['date'] = trim($_REQUEST['dat']);
		$upd['location'] = strtoupper(trim($_REQUEST['loc']));
		$upd['shelf'] = strtoupper(trim($_REQUEST['shelf']));
		$upd['qty'] = mf($_REQUEST['qty']);
		$upd['branch_id'] = trim($bran_id);
		$upd['user_id'] = trim($sessioninfo['id']);
		$upd['sku_item_id'] = trim($sku_id);
		$upd['imported'] = "0";
		// no error found, start update or insert
		  // new data, do insert
		$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd)) or die(mysql_error());
		$test_id = $upd['id'];
		$upd['test_id'] = $test_id;

		if($_REQUEST['ses_time'])
		{
		  $time = $_REQUEST['ses_time'];
		}else
		{
		  $time = time();
		}

		$get_id = $con->sql_query("select sku_item_code,description,artno, sku_items.doc_allow_decimal
								   from sku_items
								   where id = ".ms($_REQUEST['sku_item_id']));
		$f2 = $con->sql_fetchrow($get_id);
		$upd['sku_item_code'] = $f2['sku_item_code'];
		$upd['description'] = $f2['description'];
		$upd['artno'] = $f2['artno'];
		$upd['doc_allow_decimal'] = $f2['doc_allow_decimal'];
		
		$_SESSION['scan_data'][$time][] = $upd;

		//$smarty->assign("ses_time",$time);
		log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Save Record(Branch#".$bran_id.", SKU Item ID#$sku_id)");

		print $time;
	}
	
	// Alex
	function save2()
	{
		global $con,$smarty,$sessioninfo,$config, $appCore;
		$id = intval($_REQUEST['id']);

		$sku_id = $_REQUEST['sku_item_id2'];

		//check code valid
		$con->sql_query("select * from sku_items where id = ".ms($sku_id));
		$f = $con->sql_fetchassoc();
		
		// no error found, start update or insert
		if(!$f)
		{
		  print "Invalid Code Entered";
		  exit;
		}else{
			if ($f['doc_allow_decimal']){
				$_REQUEST['qty2'] = round($_REQUEST['qty2'], $config['global_qty_decimal_points']);
			}else{
				$_REQUEST['qty2'] = mi($_REQUEST['qty2']);
			}
			
			print "OK";
		}
		
		$con->sql_freeresult();

		if($_REQUEST['branch_id'])
		{
		  $bran_id = $_REQUEST['branch_id'];
		}else
		{
		  $bran_id = $sessioninfo['branch_id'];
		}

		$upd = array();
		$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($bran_id));
		$upd['date'] = trim($_REQUEST['date']);
		$upd['location'] = strtoupper(trim($_REQUEST['location']));
		$upd['shelf'] = strtoupper(trim($_REQUEST['shelf']));
		$upd['qty'] = mf($_REQUEST['qty2']);
		$upd['branch_id'] = trim($bran_id);
		$upd['user_id'] = trim($sessioninfo['id']);
		$upd['sku_item_id'] = trim($sku_id);
		$upd['imported'] = "0";

		  // new data, do insert
		$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd)) or die(mysql_error());
		$test_id = $upd['id'];

		//$smarty->assign("ses_time",$time);
		log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Save Record(Branch#".$bran_id.", SKU Item ID#$sku_id)");
	}
	
	//share using with two diff table
	function multi_save()
	{
		global $con,$smarty,$sessioninfo,$appCore;
		$id = intval($_REQUEST['id']);

		$upd = array();
		$upd['branch_id'] = trim($this->branch_id);
		$upd['date'] = trim($this->date);
		$upd['location'] = strtoupper(trim($this->location));
		$upd['shelf'] = strtoupper(trim($this->shelf));
		$upd['user_id'] = trim($sessioninfo['id']);

		if (!$_REQUEST['sid_list']){
			print "Please select at least one item.";
			exit;
		}

		if($_REQUEST['ses_time'])
		{
			$time = $_REQUEST['ses_time'];
		}else{
			$time = time();
		}

		foreach ($_REQUEST['sid_list'] as $sku_id){

			//check code valid
			$con->sql_query("select * from sku_items where id = ".ms($sku_id));
			$f = $con->sql_fetchrow();

			// no error found, start update or insert
			if(!$f)
			{
				print "Invalid Code.";
				exit;
			}else{
				$sku_item_code = $f['sku_item_code'];
				$description = $f['description'];
				$artno = $f['artno'];
				$doc_allow_decimal = $f['doc_allow_decimal'];
			}
			$upd['id'] = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($this->branch_id));
			$upd['sku_item_id'] = trim($sku_id);
			$upd['imported'] = "0";
			
			// new data, do insert
			$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($upd)) or die(mysql_error());
			$upd['test_id'] = $upd['id'];
			//$smarty->assign("ses_time",$time);
			log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Save Record(Branch#".mi($this->branch_id).", SKU Item ID#$sku_id)");

			$upd['sku_item_code'] = $sku_item_code;
			$upd['description'] = $description;
   			$upd['artno'] = $artno;
			$upd['doc_allow_decimal'] = $doc_allow_decimal;
   			$upd['qty'] = 0;

			if ($_REQUEST['table']=='f_a'){
				$_SESSION['scan_data'][$time][] = $upd;
			}
			unset($upd['test_id']);
			unset($upd['sku_item_code']);
			unset($upd['description']);
			unset($upd['artno']);
			unset($upd['doc_allow_decimal']);
			unset($upd['qty']);
		}
		unset($upd);
		
		if ($_REQUEST['table']=='f_a'){
			print $time;
		}
	}

	function reload_table($sqlonly = false)
	{
		global $con, $smarty,$sessioninfo;
		if($this->branch_id && $this->date && $this->location && $this->shelf){
/*
	        $sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));

	    	$sql = "select stock_take_pre.branch_id,stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,sku_items.mcode,sku_items.artno,sku_items.description,sku_items.sku_item_code,user.u,sb.qty as sb_qty,if (sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code, sku_items.doc_allow_decimal
			from stock_take_pre
			LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
			LEFT JOIN sku ON sku_items.sku_id=sku.id
			LEFT JOIN user on user.id = stock_take_pre.user_id
			left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
 		 	left join sku_items_price sp on sp.sku_item_id = sku_items.id and sp.branch_id = $branch_id
			where stock_take_pre.date =".ms($date)." and
				stock_take_pre.location = ".ms($location)." and
				stock_take_pre.shelf =".ms($shelf)." and
				stock_take_pre.branch_id=$branch_id and
				$sku_filter
				stock_take_pre.imported = '0'
				and stock_take_pre.is_fresh_market=0 order by stock_take_pre.id";

	    	$con->sql_query($sql) or die(mysql_error());
	    	
	    	$table = $con->sql_fetchrowset();
	    	$con->sql_freeresult();
*/
	        $table=$this->load_stp_data();
			
			$smarty->assign("user", $sessioninfo['u']);
	    	$smarty->assign("flows", $table);
 		}


		if (!$sqlonly) $smarty->display("admin.stock_take.table.tpl");
	}
  
	function load_location($sqlonly = false)
	{
		global $con, $smarty,$sessioninfo;
		$date = $_REQUEST['d'];
		$branch_id = mi($this->branch_id);

		//get location
		$rs = $con->sql_query("select distinct(location) from stock_take_pre where date=".ms($date)." and branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=0 order by location");
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
		$rs = $con->sql_query("select distinct(shelf) from stock_take_pre where location = ".ms($location)." and date = ".ms($date)." and branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=0 order by shelf");

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
  
	function load_table_data()
	{
		global $con, $smarty,$sessioninfo;

		$this->date = $_REQUEST['dat'];
		$this->location = $_REQUEST['loc'];
		$this->shelf = $_REQUEST['shelf'];
/*		$branch_id = mi($_REQUEST['branch_id']);
		$sku_type = trim($_REQUEST['sku_type']);
		$sku_filter = "";
		if ($sku_type != "") $sku_filter="sku.sku_type=".ms($sku_type)." and ";

		$sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));
		
		$sql = "select stp.branch_id,stp.date,stp.location,stp.shelf,stp.qty,stp.cost_price,stp.id,sku_items.mcode,sku_items.artno,sku_items.description,sku_items.sku_item_code,user.u,sb.qty as sb_qty,
if (sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code, sku_items.doc_allow_decimal
		from stock_take_pre stp
		LEFT JOIN sku_items ON stp.sku_item_id=sku_items.id
		LEFT JOIN sku ON sku_items.sku_id=sku.id
		LEFT JOIN user on user.id = stp.user_id
		left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
		left join sku_items_price sp on sp.sku_item_id = sku_items.id and sp.branch_id = $branch_id
		where stp.date =".ms($date)." and
		stp.location = ".ms($location)." and
		stp.shelf =".ms($shelf)." and
		stp.branch_id=$branch_id and
		$sku_filter
		stp.imported = '0'and
		stp.is_fresh_market=0
		order by stp.id";

		//print $sql;
		$con->sql_query($sql) or die(mysql_error());
		$table = $con->sql_fetchrowset();
		$con->sql_freeresult();
*/
		$table=$this->load_stp_data();

		$smarty->assign("user", $sessioninfo['u']);
		$smarty->assign("flows", $table);

		$smarty->display("admin.stock_take.table.tpl");
	}
      
	function save_edit()
	{
		global $con, $smarty,$sessioninfo;
		$qty = $_REQUEST['qtys'];
		$cost_price = $_REQUEST['cost_prices'];
		$is_import_page = $_REQUEST['is_import_page'];
		
		//$branch_id = mi($_REQUEST['branch_id']);

		if(!$qty)
		{
			exit;
		}

		foreach($qty as $key=>$q)
		{
			$cp=trim($cost_price[$key]);
			if ($cp=='')	$cp='null';
			else	$cp=ms($cp);
			$result = $con->sql_query("update stock_take_pre set qty = ".ms($q).", cost_price = ".$cp." where id = ".ms($key)." and branch_id=$this->branch_id");
		}
/*
		$sql = "select stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,stock_take_pre.branch_id,sku_items.mcode,sku_items.artno,sku_items.description,sku_items.sku_item_code 
			from stock_take_pre 
			LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id 
			where stock_take_pre.date =".ms($_REQUEST['date'])." and stock_take_pre.location = ".ms($_REQUEST['location'])." and stock_take_pre.shelf =".ms($_REQUEST['shelf'])." and stock_take_pre.branch_id=$branch_id and stock_take_pre.imported = '0' and stock_take_pre.is_fresh_market=0 order by stock_take_pre.id";

		$con->sql_query($sql) or die(mysql_error());
		$table = $con->sql_fetchrowset();
		$con->sql_freeresult();
*/		
		//get date
		/*$rs = $con->sql_query("select distinct(date) from stock_take_pre where branch_id = ".ms($sessioninfo['branch_id'])." and imported = '0'");
		while ($r = $con->sql_fetchrow($rs))
		{
		    $dat[]=$r;
		}
		//get location
		$rs = $con->sql_query("select distinct(location) from stock_take_pre where branch_id = ".ms($sessioninfo['branch_id'])." and imported = '0'");
		while ($r = $con->sql_fetchrow($rs))
		{
		    $loc[]=$r;
		}
		//get shelf
		$rs = $con->sql_query("select distinct(shelf) from stock_take_pre where branch_id = ".ms($sessioninfo['branch_id'])." and imported = '0'");
		while ($r = $con->sql_fetchrow($rs))
		{
		    $shelf[]=$r;
		}

		$smarty->assign("shelf", $shelf);
		$smarty->assign("loc", $loc);
		$smarty->assign("dat", $dat);
		$smarty->assign("flows", $table);*/
		if($is_import_page){ // saved from import stock take error page
			log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Save from Import page (Branch#".$this->branch_id.", Date#$this->date)");
			header("location: admin.stock_take.php?a=import_page");
		}else{
			log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Save Edit (Branch#".$this->branch_id.", Date#$this->date, Location#$this->location, Shelf#$this->shelf)");
			header("location: admin.stock_take.php?date=".$_REQUEST['date']."&location=".$_REQUEST['location']."&shelf=".$_REQUEST['shelf']."".($this->can_select_branch? "&branch_id=$this->branch_id" : '')."&msg=Data Successful Saved");
		}
		//$smarty->display('admin.stock_take.tpl');
		//print "<script>alert('Data Successful Updated')</script>";
	}
      
	function delete_record()
	{
		global $con, $smarty,$sessioninfo;

		$branch_id = mi($_REQUEST['branch_id']);
		$date = trim($_REQUEST['date']);
		$loc = trim($_REQUEST['location']);
		$shelf = trim($_REQUEST['shelf']);
		$sku_type = trim($_REQUEST['sku_type']);
		$sku_filter = "";
		if ($sku_type != "") $sku_filter="sku.sku_type=".ms($sku_type)." and ";

		$sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));

		$con->sql_query("delete from stock_take_pre where id=".ms($_REQUEST['d'])." and branch_id=$branch_id") or die(mysql_error());
		/*$sql = "select stock_take_pre.branch_id,stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,stock_take_pre.cost_price,sku_items.mcode,sku_items.artno,sku_items.description,sku_items.sku_item_code,user.u,sb.qty as sb_qty,
if (sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code
		from stock_take_pre
		LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
		LEFT JOIN sku ON sku_items.sku_id=sku.id
		left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
		left join sku_items_price sp on sp.sku_item_id = sku_items.id and sp.branch_id = $branch_id
		LEFT JOIN user on user.id = stock_take_pre.user_id
		where stock_take_pre.date=".ms($date)." and
		stock_take_pre.location=".ms($loc)." and
		stock_take_pre.shelf =".ms($shelf)." and
		stock_take_pre.branch_id=$branch_id and
		stock_take_pre.imported = '0' and
		$sku_filter
		stock_take_pre.is_fresh_market=0
		order by stock_take_pre.id";

		$con->sql_query($sql) or die(mysql_error());
		$table = $con->sql_fetchrowset();*/
		$table = $this->load_stp_data();

		$smarty->assign("user", $sessioninfo['u']);
		$smarty->assign("flows", $table);
		$con->sql_freeresult();

		log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['d'], "Delete Record (Branch#".$branch_id.", ID#$_REQUEST[d])");

		$smarty->display("admin.stock_take.table.tpl");
	}
      
	function load_add_armcode()
	{
		global $con, $smarty,$sessioninfo;
		$code = $_REQUEST['c'];
		
		$con->sql_query("select * from sku_items where sku_item_code = ".ms($code)." or mcode = ".ms($code)." or artno = ".ms($code));
		while($r = $con->sql_fetchrow())
		{
			$data[] = $r;
		}
		
		if(empty($data))
		{
			print "invalid";
			exit;
		}elseif(count($data)>1)
		{
		    $smarty->assign("arms_code",$data);
		    $smarty->display('arms_code.display.tpl');
		    exit;
		}
	}
	
	function ajax_get_item_info()
	{
    	global $con, $sessioninfo;
    	
    	$sku_item_id = mi($_REQUEST['sku_item_id']);
    	$branch_id = mi($sessioninfo['branch_id']);
    	// stock balance
    	/*$year = mi(date('Y'));
    	$sb_tbl = "stock_balance_b".$branch_id."_".$year;
    	$con->sql_query("select * from $sb_tbl where sku_item_id=$sku_item_id and is_latest=1") or die(mysql_error());*/
    	$con->sql_query("select * from sku_items_cost where sku_item_id=$sku_item_id and branch_id=$branch_id") or die(mysql_error());
    	$ret['sb'] = $con->sql_fetchrow();
     
    	// masterfile sku
    	//$con->sql_query("select * from sku_items where id=$sku_item_id") or die(mysql_error());
    	//$ret['sku_items'] = $con->sql_fetchrow();
    	
    	print json_encode($ret);
    }
    
    function load_desc()
    {
        global $con, $smarty;
        $code = $_REQUEST['c'];
        
        $con->sql_query("select description from sku_items where id = ".ms($code));
        $r = $con->sql_fetchrow();
       
      	print $r['description'];
    }
    
    function get_cost_selling(&$row)
    {
      	if (preg_match('/^M/', $row['sku_item_code'])) return;
      	global $con, $config;
      	
      	if ($row['cost']=='')
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
    
    /*function import_stock_take()
    {
        global $con, $smarty,$sessioninfo;
        
        $branch_id = mi($_REQUEST['b_id']);
		if ($branch_id ==0){
			$branch_id = $sessioninfo['branch_id'];
		}
		$sku_item_id_list = array();
		
        $rs = $con->sql_query("select stock_take_pre.sku_item_id,stock_take_pre.date,stock_take_pre.branch_id,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.user_id,sku_items.sku_item_code from stock_take_pre 
        LEFT JOIN sku_items on stock_take_pre.sku_item_id = sku_items.id where stock_take_pre.branch_id = ".mi($branch_id)." and stock_take_pre.date =".ms($_REQUEST['b_date'])." and stock_take_pre.imported = '0'");
        
        while($r = $con->sql_fetchrow($rs))
        {
           $data[]=$r;
        }
        
        $n = 0;
        foreach($data as $val)
        {
            $sku_item_id_list[$val['sku_item_id']] = $val['sku_item_id'];
            
            //check whether is exists
            $rts = $con->sql_query("select * from stock_check where date =".ms($val['date'])." and branch_id = ".ms($val['branch_id'])." and sku_item_code=".ms($val['sku_item_code'])." and location = ".ms($val['location'])." and shelf_no = ".ms($val['shelf']));
            
            $rt = $con->sql_fetchrow($rts);
            if($rt)
            {
                //update
                $q = $val['qty'] + $rt['qty'];
                $result = $con->sql_query("update stock_check set qty = ".ms($q)." where date = ".ms($val['date'])." and branch_id = ".ms($val['branch_id'])." and sku_item_code=".ms($val['sku_item_code'])." and location = ".ms($val['location'])." and shelf_no = ".ms($val['shelf'])) or die(mysql_error());
                
            }else
            {
                $upd = array();
                $upd['sku_item_code']=$val['sku_item_code'];
                
                $upd['selling']=$r['selling_price'];
                $upd['cost']=$r['cost_price'];
                
              	$upd['date'] = trim($val['date']);
              	$upd['branch_id'] = trim($val['branch_id']);
              
              	$upd['cost'] = "0";
              	$upd['selling'] = "0";
          
              	$this->get_cost_selling($upd);
              	$upd['item_no'] = $n;
              	//$upd['scanned_by'] = trim($sessioninfo['u']);
              	
              	$r_user = $con->sql_query("select u from user where id=".ms($val['user_id']));
                $u_r = $con->sql_fetchrow($r_user);
              	
              	$upd['scanned_by'] = trim($u_r['u']);
              	$upd['location'] = trim($val['location']);
              	$upd['shelf_no'] = trim($val['shelf']);
              	$upd['qty'] = trim($val['qty']);
        
              	$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd)) or die(mysql_error());
            }
            //$con->sql_query("delete from stock_take_pre where branch_id = ".mi($branch_id)." and date = ".ms($_REQUEST['b_date'])) or die(mysql_error());
            
            $con->sql_query("update stock_take_pre set imported = '1' where branch_id = ".mi($branch_id)." and date = ".ms($_REQUEST['b_date'])) or die(mysql_error());
            $n++;
        } 
        
		if($_REQUEST['fill_zero']){ // fill zero for items not in stock take
            $con->sql_query("insert into stock_check (branch_id, date, sku_item_code) select $branch_id, ".ms($_REQUEST['b_date']).", sku_item_code from sku_items where sku_item_code not in (select distinct sku_item_code from stock_check where branch_id=$branch_id and date=".ms($_REQUEST['b_date']).") order by sku_item_code");
            $n+= $con->sql_affectedrows();
			$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id") or die(mysql_error());
		}else{
            if($sku_item_id_list){
				for($i=0; $i<count($sku_item_id_list); $i+=1000){
	                $con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
				}
			}
		}

        log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Import Stock Take (Branch#".$branch_id.", Date#$_REQUEST[b_date])");
        print "$n items imported.";
    }

	function check_available_branch()
    {
        global $con, $smarty;

        if($_REQUEST['c']=='1')
            $imp = " and imported = '1'";
        else
            $imp = " and imported = '0'";

        $rs = $con->sql_query("select distinct(date) from stock_take_pre where branch_id =".ms($_REQUEST['b']).$imp);
        while($r = $con->sql_fetchrow($rs))
        {
           $b_date[]=$r['date'];
        }

        if(!$b_date)
        {
            print "";
            exit;
        }
        $smarty->assign("bran_date",$b_date);
        if($_REQUEST['c']=='1')
          $smarty->display('branch_date2.tpl');
        else
          $smarty->display('branch_date.tpl');
    }*/
    
    function code_validation()
    { 
        global $con, $smarty,$sessioninfo;
        $code = $_REQUEST['sku_code'];
        $con->sql_query("select * from sku_items where id = ".ms($code));
        $r = $con->sql_fetchrow();
        
        if($r)
        {
           print "got";
        }else
        {
            print "no";
        }
    }
    
    function print_report()
    {
        global $con, $smarty, $sessioninfo, $config;

        $branch_id = mi($_REQUEST['branch_id']);
        $con->sql_query("select * from branch where id=$branch_id");
        if ($con->sql_numrows()>0) 
        {
          $branch = $con->sql_fetchrow();
          $smarty->assign("branch",$branch);
        }
        
        $rpt_type = $_REQUEST['rpt_type'];
        

        
        if($rpt_type)
        {
            $filter = array();
            if($_REQUEST['dat']){
                $filter []= "stock_take_pre.date = ".ms($_REQUEST['dat']);
                $date = $_REQUEST['dat'];
			}else{
				die("Please select Stock Take Date");
			}

			//for stock take count sheet
			if ($config['stock_take_count_sheet']){
				if($_REQUEST['count_sheet']){
					$stock_count_sheet = $_REQUEST['count_sheet'];
				}else{
					die("Please enter stock count sheet no.");
				}
			}
		
            if($_REQUEST['dat']){
                $filter []= "stock_take_pre.date = ".ms($_REQUEST['dat']);
                $date = $_REQUEST['dat'];
			}else{
				die("Please select Stock Take Date");
			}
            
            if($_REQUEST['loc2']){
                $filter []= "stock_take_pre.location between ".ms($_REQUEST['loc2'])." and ".ms($_REQUEST['loc3']);
			}
              
            if($_REQUEST['shelf2']){
                $filter[]= "stock_take_pre.shelf between ".ms($_REQUEST['shelf2'])." and ".ms($_REQUEST['shelf3']);
			}

            if($_REQUEST['p_sku_type'] != "") $filter[]= "sku.sku_type= ".ms($_REQUEST['p_sku_type']);

			$filter[] = "stock_take_pre.branch_id=$branch_id and stock_take_pre.imported = '0' and stock_take_pre.is_fresh_market=0";

			$filter = "where ".join(' and ', $filter);
               
            $sql = "select stock_take_pre.branch_id,stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,stock_take_pre.cost_price,sku_items.mcode,sku_items.artno,sku_items.link_code,sku_items.description,sku_items.selling_price,sku_items.sku_item_code,user.u, uom.code as uom_code, if (sku.sku_type='CONSIGN',(ifnull(sip.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code
			from stock_take_pre
			LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
			LEFT JOIN sku on sku_items.sku_id=sku.id
			LEFT JOIN user on user.id = stock_take_pre.user_id
            left join uom on uom.id=sku_items.packing_uom_id
            left join sku_items_price sip on sip.branch_id=stock_take_pre.branch_id and sip.sku_item_id=stock_take_pre.sku_item_id
			$filter
			order by stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.id";

            /*$user_sql = "select distinct(user.u) from user left join stock_take_pre on stock_take_pre.user_id = user.id $filter";
			$con->sql_query($user_sql) or die(mysql_error());
			$rs = $con->sql_fetchrowset();*/
        }else
        {
            $date = $_REQUEST['date'];
	    	if($_REQUEST['sku_type'] != "") $sku_filter = "sku.sku_type=".ms($_REQUEST['sku_type'])." and ";

			if(!$date||!$_REQUEST['location']||!$_REQUEST['shelf'])  die("Please select Stock Take Date, Location & Shelf");

            $sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));
            $sql = "select stock_take_pre.branch_id,stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,stock_take_pre.cost_price,sku_items.mcode,sku_items.artno,sku_items.link_code,sku_items.description,sku_items.selling_price,sku_items.sku_item_code,user.u,sb.qty as sb_qty ,uom.code as uom_code, if (sku.sku_type='CONSIGN',(ifnull(sip.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code, sku_items.id as sku_item_id
			from stock_take_pre
			LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
			LEFT JOIN sku on sku_items.sku_id=sku.id
			LEFT JOIN user on user.id = stock_take_pre.user_id
			left join uom on uom.id=sku_items.packing_uom_id
			left join $sb_tbl sb on sb.sku_item_id=sku_items.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
			left join sku_items_price sip on sip.branch_id=stock_take_pre.branch_id and sip.sku_item_id=stock_take_pre.sku_item_id
			where stock_take_pre.date=".ms($_REQUEST['date'])." and
			stock_take_pre.location=".ms($_REQUEST['location'])." and
			stock_take_pre.shelf =".ms($_REQUEST['shelf'])." and
			stock_take_pre.branch_id=$branch_id and
			stock_take_pre.imported = '0' and
			$sku_filter
			stock_take_pre.is_fresh_market=0
			order by stock_take_pre.location,stock_take_pre.shelf, stock_take_pre.id";
			//print $sql;
/*

            $sql = "select stock_take_pre.branch_id,stock_take_pre.date,stock_take_pre.location,stock_take_pre.shelf,stock_take_pre.qty,stock_take_pre.id,sku_items.mcode,sku_items.artno,sku_items.link_code,sku_items.description,sku_items.selling_price,sku_items.sku_item_code,user.u,uom.code as uom_code
			from stock_take_pre
			LEFT JOIN sku_items ON stock_take_pre.sku_item_id=sku_items.id
            $sku_join
			LEFT JOIN user on user.id = stock_take_pre.user_id
			left join uom on uom.id=sku_items.packing_uom_id
			where stock_take_pre.date=".ms($_REQUEST['date'])." and
			stock_take_pre.location=".ms($_REQUEST['location'])." and
			stock_take_pre.shelf =".ms($_REQUEST['shelf'])." and
			stock_take_pre.branch_id=$branch_id and
			stock_take_pre.imported = '0' and
			$sku_filter
			stock_take_pre.is_fresh_market=0
			order by stock_take_pre.location,stock_take_pre.shelf, stock_take_pre.id";
*/

            /*$user_sql = "select distinct(user.u) from user left join stock_take_pre on stock_take_pre.user_id = user.id where stock_take_pre.branch_id=$branch_id and stock_take_pre.date=".ms($_SESSION['date'])." and stock_take_pre.location=".ms($_SESSION['location'])." and stock_take_pre.shelf = ".ms($_SESSION['shelf']);
           $con->sql_query($user_sql) or die(mysql_error());
           $rs = $con->sql_fetchrowset();*/
        }
  
        /*if($rs)
        {
            $user_create[] = $rs['0']['u'];
            if(count($rs)>1)
            {
                $lst = end($rs);
                $user_create[] = $lst['u'];
            }
        }*/
      
        $q1 = $con->sql_query($sql) or die(mysql_error());
		
		$si_list = $stp_qty = array();
        while($r = $con->sql_fetchassoc($q1)){
			$upd = array();
			$upd['date'] = $r['date'];
            $upd['branch_id'] = $r['branch_id'];
            $upd['sku_item_code']=$r['sku_item_code'];
            $upd['cost'] = $r['cost_price'];
            $this->get_cost_selling($upd);
            $r['selling_price'] = $upd['selling'];
            $r['cost'] = $upd['cost'];
            $table[$r['location']][$r['shelf']][] = $r;
            $user_create[$r['location']][$r['shelf']][$r['u']] = $r['u'];
			$si_list[$r['sku_item_id']][$r['id']] = $r['id'];
			$stp_qty[$r['sku_item_id']] += $r['qty'];
		}
		$con->sql_freeresult($q1);
		//print_r($table);
		
		foreach($table as $loc=>$shelf_list){
			foreach($shelf_list as $shelf=>$data){
				foreach($data as $tmp_row=>$r){
					$last_id = end($si_list[$r['sku_item_id']]);
					if($r['id'] == $last_id){
						$table[$loc][$shelf][$tmp_row]['variance'] = $stp_qty[$r['sku_item_id']] - $table[$loc][$shelf][$tmp_row]['sb_qty'];
					}else{
						$table[$loc][$shelf][$tmp_row]['variance'] = 0;
					}
					$table[$loc][$shelf][$tmp_row]['mid'] = $last_id;
				}
			}
		}

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
				    //$smarty->assign("sessioninfo",$sessioninfo);
				    //$smarty->assign("config", $config);
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
    
    function delete_allRecord()
    {
		global $con, $smarty, $sessioninfo;
		
		$sql = "delete from stock_take_pre where date=".ms($_REQUEST['date'])." and location=".ms($_REQUEST['location'])." and shelf = ".ms($_REQUEST['shelf'])." and branch_id=".mi($_REQUEST['branch_id'])." and imported = '0' and stock_take_pre.is_fresh_market=0";
		$con->sql_query($sql) or die(mysql_error());

		log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['date'], "Delete All Record (Branch#".$_REQUEST['branch_id'].", Date#$_REQUEST[date], Location#$_REQUEST[location] , Shelf#$_REQUEST[shelf])");

		$smarty->display("admin.stock_take.table.tpl");
		exit;
    }
    
    function validate_code()
    {
        global $con, $smarty, $sessioninfo;
        
        $code = $_REQUEST['code'];
        
		$filter_sku ="";
        if ($_REQUEST['sku_type']) $filter_sku = " and sku_type=".ms($_REQUEST['sku_type']);
        
        
        $sql = "select sku_items.id,sku_items.description,sku_items.selling_price, sku_items.doc_allow_decimal from sku_items
                left join sku on sku_items.sku_id=sku.id
				left join category_cache cc on cc.category_id=sku.category_id
				where (sku_items.sku_item_code = ".ms(substr($code,0,12))." or sku_items.mcode = ".ms($code)."
					or sku_items.mcode = ".ms(substr($code,0,12))." or sku_items.artno = ".ms($code)."
					or sku_items.link_code =".ms($code)." or sku_items.link_code = ".ms(substr($code,0,12)).")
					and sku_items.active=1 and (sku.is_fresh_market='no' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='no')) $filter_sku";
      
        $con->sql_query($sql) or die(mysql_error());
	      $row = $con->sql_fetchrow();
        $d['description'] = $row['description'];
        $d['id'] = $row['id'];
        $d['doc_allow_decimal'] = $row['doc_allow_decimal'];
        if($row)
  	    {
  	        //print $row['description'];
  	        print_r(json_encode($d));
        }
        else
        {
            print "no";
        }
    }
    
    function load_range()
    {
        global $con, $smarty, $sessioninfo;
        $date = $_REQUEST['d'];
        $branch_id = mi($_REQUEST['branch_id']);
        
        $rs = $con->sql_query("select distinct(location) from stock_take_pre where date=".ms($date)." and branch_id=$branch_id and imported =0 and stock_take_pre.is_fresh_market=0 order by location");
      	while ($r = $con->sql_fetchrow($rs))
      	{
      	    $loc[]=$r;
      	}
      	
      	$loc_selected = $loc[0]['location'];
      	$k = $con->sql_query("select distinct(shelf) from stock_take_pre where date=".ms($date)." and branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=0 and location=".ms($loc_selected)." order by shelf");
      
      	while ($r2 = $con->sql_fetchrow($k))
      	{
      	    $shelf[]=$r2;
      	}
        $smarty->assign('loc',$loc);
        $smarty->assign('shelf',$shelf);
        $smarty->display('admin.stock_take.range.tpl');
    }
    
    function load_scan_item()
    {
        global $con, $smarty;

        $smarty->assign("item_scan", $_SESSION['scan_data']);
        $smarty->display('admin.stock_take.scan_item.tpl');
    }
    
    function reset_session()
    {
        global $con, $smarty;
        unset($_SESSION['scan_data'][$_REQUEST['v']]);
        exit;
    }
    
    function delete_scan_item()
    {
        global $con, $smarty,$sessioninfo;
		
		$branch_id = ($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : $sessioninfo['branch_id'];
        $con->sql_query("delete from stock_take_pre where id = ".mi($_REQUEST['item_id'])." and branch_id=".mi($branch_id)) or die(mysql_error());
        
        unset($_SESSION['scan_data'][$_REQUEST['s_time']][$_REQUEST['key_id']]);
        $smarty->assign("sess_time",$_REQUEST['s_time']);
        $smarty->assign("item_scan",$_SESSION['scan_data']);
        $smarty->display('admin.stock_take.scan_item.tpl');
    }
    
    function upd_item_qty()
    {
        global $con, $smarty,$sessioninfo;
        
		$branch_id = ($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : $sessioninfo['branch_id'];
        $result = $con->sql_query("update stock_take_pre set qty = ".mi($_REQUEST['qtys'])." where id = ".mi($_REQUEST['item_id'])." and branch_id=".mi($branch_id)) or die(mysql_error());
        
        $_SESSION['scan_data'][$_REQUEST['s_time']][$_REQUEST['key_id']]['qty']=$_REQUEST['qtys'];
        
        //$smarty->assign("sess_time",$_REQUEST['s_time']);
        $smarty->assign("item_scan",$_SESSION['scan_data']);
        $smarty->display('admin.stock_take.scan_item.tpl');
    }
    
    function swap()
    {
        global $con, $smarty,$sessioninfo;
        //print_r($_REQUEST);
        $mode = $_REQUEST['mode'];
        $id = $_REQUEST['id'];
        $bran_id = mi($_REQUEST['branch_id']);
        
        if($mode =='up')
        {    
            $id_position = "and id < ".ms($id); 
            $order = " order by id desc";  
        }else
        {
            $id_position = "and id > ".ms($id);  
        }
        
        //get max_id
        $m_id = $con->sql_query("select MAX(id) as id from stock_take_pre where branch_id=".ms($bran_id)."".  $order) or die(mysql_error());
        
        $r2 = $con->sql_fetchrow($m_id);
        $max_id =  $r2['id'];

        $result = $con->sql_query("select * from stock_take_pre where branch_id=".ms($bran_id)." and date = ".ms($_REQUEST['date'])." and location = ".ms($_REQUEST['location'])." and shelf = ".ms($_REQUEST['shelf']).$id_position." and imported = '0' ".$order);
        
        $r = $con->sql_fetchrow($result);
        $next_id = $r['id'];  
        
        if(!$next_id)
        {
            $this->reload_table();
            exit;
        }
        $max = $max_id +1;

        $result = $con->sql_query("update stock_take_pre set id = ".ms($max)." where id = ".ms($next_id)." and branch_id=".ms($bran_id)) or die(mysql_error());
        $result = $con->sql_query("update stock_take_pre set id = ".ms($next_id)." where id = ".ms($id)." and branch_id=".ms($bran_id)) or die(mysql_error());
        $result = $con->sql_query("update stock_take_pre set id = ".ms($id)." where id = ".ms($max)." and branch_id=".ms($bran_id)) or die(mysql_error());
        $this->reload_table();
    }
    
    function revert_import()
    {
        global $con, $smarty,$sessioninfo;
        $result = $con->sql_query("select distinct(si.id) as sku_items_id from sku_items si left join stock_check sc on sc.sku_item_code = si.sku_item_code where sc.date =".ms($_REQUEST['branch_date'])." and sc.branch_id = ".ms($_REQUEST['branch'])) or die(mysql_error());

        while($r = $con->sql_fetchrow($result))
        {
            $sku_items_id[] = $r['sku_items_id'];
            if (count($sku_items_id)>1000)
			{
				// possible too many item...
	        	$con->sql_query("update sku_items_cost set changed = '1' where branch_id = ".ms($_REQUEST['branch'])." and sku_item_id in (".join(',',$sku_items_id).")") or die(mysql_error());
	        	$sku_items_id = array();
        	}
        	$c++;
        }
        if (count($sku_items_id)>0) $con->sql_query("update sku_items_cost set changed = '1' where branch_id = ".ms($_REQUEST['branch'])." and sku_item_id in (".join(',',$sku_items_id).") and stock_take_pre.is_fresh_market=0") or die(mysql_error());
        $con->sql_query("delete from stock_check where date = ".ms($_REQUEST['branch_date'])." and branch_id = ".ms($_REQUEST['branch'])." and stock_take_pre.is_fresh_market=0") ;
        $con->sql_query("update stock_take_pre set imported = '0' where branch_id = ".ms($_REQUEST['branch'])." and date=".ms($_REQUEST['branch_date']) ." and stock_take_pre.is_fresh_market=0");
        log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Reset Stock Take (Branch#".$branch_id.", Date#$_REQUEST[branch_date])");
        print "$c items reset.";
    }
    
    function import_page(){
		global $con, $smarty, $sessioninfo;
		
		if(BRANCH_CODE=="HQ")
        {
            $rs = $con->sql_query("select id,code,description from branch order by sequence, code");
          	while ($r = $con->sql_fetchrow($rs)){
          	    $branch[]=$r;
          	}
          	$smarty->assign("branch", $branch);
        }
        if($_REQUEST['t']||BRANCH_CODE!="HQ"){
			if(BRANCH_CODE=="HQ")   $bid = mi($_REQUEST['branch_id']);
			else    $bid = mi($sessioninfo['branch_id']);
			$date_data['import'] = $this->check_available_stock_take_date($bid, 0, true);
			$date_data['reset'] = $this->check_available_stock_take_date($bid, 1, true);
			$smarty->assign('date_data', $date_data);
		}
            
		$smarty->display("admin.stock_take.import_page.tpl");
	}
	
	function check_available_stock_take_date($bid = 0, $check_imported = -1, $sql_only = false)
    {
        global $con, $smarty;

		$imported = ($check_imported>-1) ? $check_imported : mi($_REQUEST['imported']);
		if ($imported == 0 ){
		    $im_re='import';
		}
		else{
		    $im_re='reset';
		}
		
		$branch_id = $bid>0 ? $bid : get_request_branch();
		
		$filter[] = "imported=$imported";
		$filter[] = "branch_id=$branch_id";
		$filter[] = "stp.is_fresh_market=0";
		$filter = "where ".join(' and ', $filter);

        $con->sql_query("select * from stock_take_pre stp $filter group by date order by date desc");
        
        if(!$sql_only){
            $smarty->assign("available_date", $con->sql_fetchrowset());
            $smarty->assign("type",$imported);
            $smarty->assign("im_re",$im_re);
			$smarty->display('admin.stock_take.import_page.stock_take_date.tpl');
		}else   return $con->sql_fetchrowset();
    }
/*
    function check_available_stock_take_sku()
	{
        global $con, $smarty;

		$imported = mi($_REQUEST['imported']);
		$date = ms($_REQUEST['date']);
		$branch_id = get_request_branch();
		$filter[] = "stp.branch_id=$branch_id";
		$filter[] = "stp.date=$date";
		$filter[] = "stp.is_fresh_market=0";

		if ($imported){

			$filter="where ".join(' and ',$filter);

			$sql="select sku.sku_type from stock_check stp
						left join sku_items si on stp.sku_item_code=si.sku_item_code
						left join sku on si.sku_id=sku.id
						$filter group by sku.sku_type order by sku.sku_type";

		}else{
		
			$filter[] = "imported=$imported";

			$filter="where ".join(' and ',$filter);

			$sql="select sku.sku_type from stock_take_pre stp
						left join sku_items si on stp.sku_item_id=si.id
						left join sku on si.sku_id=sku.id
						$filter group by sku.sku_type order by sku.sku_type";
		}
		
		print "<b>SKU Type</b><select name='sku_type'>";

        $con->sql_query($sql);
        
		if ($con->sql_numrows()>1)
		    print "<option value=''>All</option>";
		elseif ($con->sql_numrows()<1)
		    print "<option value=''>No Data</option>";

		while ($s=$con->sql_fetchrow()){
	    	print "<option value='".$s['sku_type']."'>".$s['sku_type']."</option>";
		}

		print "</select>";
	}
*/
    function import_stock_take()
    {
        global $con, $smarty,$sessioninfo,$appCore,$LANG,$config;

        $branch_id = get_request_branch();
        $bcode = get_branch_code($branch_id);
        $date = $_REQUEST['stock_take_date'];
        $sku_type = $_REQUEST['sku_type'];

		/*$join_sku="";
		$filter_sku="";    
		if ($sku_type != ""){
		    $join_sku="LEFT JOIN sku on sku_items.sku_id=sku.id";
		    $filter_sku= "and sku.sku_type=".ms($sku_type);
        }*/
		
		if($sku_type) $st_filter = " and sku.sku_type = ".ms($sku_type);

		$result = $this->import_st_data_validate();
		$data = $result['data'];
		$stock_take_id_list = $result['stock_take_id_list'];
		$sku_id_list = $result['sku_id_list'];
		$sku_item_id_list = $result['sku_item_id_list'];
		
		//check closed month
		/*if($config['monthly_closing']){
			$err = array();
			$is_month_closed = $appCore->is_month_closed($date);
			if($is_month_closed)  $err[] = sprintf($LANG['BLOCK_MONTH_CLOSED_DOCUMENT'], "import");
		}*/
		
		// means got cost variance problem, need to display a new page with those stock take items
		if($result['error']){
			if($result['cost_st_id_list']) $smarty->assign("cost_st_id_list", $result['cost_st_id_list']);
			if($result['pc_st_id_list']) $smarty->assign("pc_st_id_list", $result['pc_st_id_list']);
			$form = $_REQUEST;
			$form['date'] = $date;
			$form['branch_id'] = $branch_id;
			$smarty->assign("form", $form);
			$smarty->display("admin.stock_take.import_page.si_err_list.tpl");
			exit;
		}
        
		// start import stock take
		$user_data = array();
		
		// get current max item no
		$con->sql_query("select max(cast(item_no as unsigned)) from stock_check where branch_id=".mi($branch_id)." and date=".ms($date));
        $n = mi($con->sql_fetchfield(0));
        $con->sql_freeresult();

        //$con->sql_query("alter table stock_check disable keys");

		$imported_count = 0;
        foreach($data as $val)
        {
        	$n++;
            $upd = array();
            $upd['sku_item_code']=$val['sku_item_code'];
            $upd['is_fresh_market']=$val['is_fresh_market'];
          	$upd['date'] = trim($val['date']);
          	$upd['branch_id'] = trim($val['branch_id']);
          	$upd['cost'] = $val['cost_price'];
          	$upd['selling'] = "0";
          	$this->get_cost_selling($upd);
          	$upd['item_no'] = $n;
          	
          	if(!isset($user_data[$val['user_id']])){    // get username
                $r_user = $con->sql_query("select u from user where id=".mi($val['user_id']));
            	$u_r = $con->sql_fetchrow($r_user);
				$con->sql_freeresult($r_user);
            	$user_data[$val['user_id']] = $u_r['u'];
			}
          	
          	$upd['scanned_by'] = trim($user_data[$val['user_id']]);
          	$upd['location'] = trim($val['location']);
          	$upd['shelf_no'] = trim($val['shelf']);
          	$upd['qty'] = mf($val['qty']);

          	$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd)) or die(mysql_error());
            $imported_count++;
        }
		$user_data = $data = array();
        
		// set item to imported
        $con->sql_query("update stock_take_pre set imported = '1' where id in (".join(',',$stock_take_id_list).") and is_fresh_market=0 and branch_id = ".mi($branch_id)." and date = ".ms($date));
		$stock_take_id_list = array();

		
		if($sku_item_id_list){
			for($i=0; $i<count($sku_item_id_list); $i+=1000){
				$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
			}
		}
		$sku_item_id_list = array();
		
		switch ($_REQUEST['fill_zero_options']){
			case "no_fill":
	            /*if($sku_item_id_list){
					for($i=0; $i<count($sku_item_id_list); $i+=1000){
		                $con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
					}
				}*/
				break;
			case "fill_parent":
	            if($sku_id_list){
	            	$upd = array();
	            	$upd['branch_id'] = $branch_id;
	            	$upd['date'] = $date;
					
	            	$cost_params = array('cost');
					for($i=0; $i<count($sku_id_list); $i+=1000){
					
						$sku_id_filter=join(',',array_slice($sku_id_list, $i, $i+1000));

						$q_si = $con->sql_query("select si.id, si.sku_item_code
												 from sku_items si
												 left join sku on sku.id = si.sku_id
												 left join category_cache cc on cc.category_id=sku.category_id
												 where si.sku_item_code not in 
												 (select distinct sc.sku_item_code from stock_check sc
												 where sc.is_fresh_market=0 and sc.branch_id=$branch_id and sc.date=".ms($date).") and si.sku_id in (".$sku_id_filter.") $st_filter 
												 and (sku.is_fresh_market='no' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='no'))
												 order by si.sku_item_code");
						while($si = $con->sql_fetchassoc($q_si)){
							$upd['sku_item_code'] = $si['sku_item_code'];
							$tmp = get_sku_item_cost_selling($branch_id,$si['id'],$date, $cost_params);
							$upd['cost'] = $tmp['cost'];
							
							$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
							
							//$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id=".mi($si['id']));
							$sku_item_id_list[$si['id']] = $si['id'];
							if(count($sku_item_id_list)>=1000){
								$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")");
								$sku_item_id_list = array();
							}	
							$imported_count++;
							
							
						}
						$con->sql_freeresult($q_si);
									
						//insert stock check by using sku
	            		/*$con->sql_query("insert into stock_check (branch_id, date, sku_item_code, cost) 
							select $branch_id, ".ms($date).", sku_item_code, ifnull((select grn_cost from sku_items_cost_history sich where sich.branch_id=$branch_id and sich.sku_item_id=sku_items.id and sich.date<=".ms($date)." order by sich.date desc limit 1) , sku_items.cost_price) as cost
							from sku_items where sku_item_code not in 
								(select distinct sku_item_code from stock_check 
									where is_fresh_market=0 and branch_id=$branch_id and date=".ms($date).") and sku_id in (".$sku_id_filter.") order by sku_item_code");
						$imported_count+= $con->sql_affectedrows();
						
						//update change = 1
						$con->sql_query("update low_priority sku_items_cost set changed=1 
									where branch_id=$branch_id and sku_item_id in (select id from sku_items where sku_items.sku_id in (".$sku_id_filter."))");*/
					}
				}
				break;
			case "fill_zero":
				$upd = array();
            	$upd['branch_id'] = $branch_id;
            	$upd['date'] = $date;
            	
            	$cost_params = array('cost');
	            	
				$q_si = $con->sql_query("select si.id, si.sku_item_code
						from sku_items si
						left join sku on sku.id = si.sku_id
						left join category_cache cc on cc.category_id=sku.category_id
						where si.sku_item_code not in (select distinct sc.sku_item_code from stock_check sc where sc.is_fresh_market=0 and sc.branch_id=$branch_id and sc.date=".ms($date).") $st_filter and (sku.is_fresh_market='no' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='no'))
						order by si.sku_item_code");
				while($si = $con->sql_fetchassoc($q_si)){
					$upd['sku_item_code'] = $si['sku_item_code'];
					$tmp = get_sku_item_cost_selling($branch_id,$si['id'],$date, $cost_params);
					$upd['cost'] = $tmp['cost'];
					
					$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
					
					//$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id=".mi($si['id']));
					$sku_item_id_list[$si['id']] = $si['id'];
					if(count($sku_item_id_list)>=1000){
						$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")");
						$sku_item_id_list = array();
					}
					$imported_count++;
				}
				$con->sql_freeresult($q_si);
						
	            /*$con->sql_query("insert into stock_check (branch_id, date, sku_item_code, cost) select $branch_id, ".ms($date).", sku_item_code,ifnull((select grn_cost from sku_items_cost_history sich where sich.branch_id=$branch_id and sich.sku_item_id=sku_items.id and sich.date<=".ms($date)." order by sich.date desc limit 1) , sku_items.cost_price) as cost
				 from sku_items where sku_item_code not in (select distinct sku_item_code from stock_check where is_fresh_market=0 and branch_id=$branch_id and date=".ms($date).") order by sku_item_code");
	            $imported_count+= $con->sql_affectedrows();
				$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id") or die(mysql_error());*/
				break;
			case "fill_zero_by_category_vendor":
				$upd = array();
            	$upd['branch_id'] = $branch_id;
            	$upd['date'] = $date;
				$cost_params = array('cost');
                $extra_logs = array();
				
				if(!$_REQUEST['category_id_list'] && !$_REQUEST['vendor_id_list'] && !$_REQUEST['brand_id_list']) die("Action is not selected.");
			
				$filters = array();
				$filters[] = "si.sku_item_code not in (select distinct sku_item_code from stock_check where is_fresh_market = 0 and branch_id = ".mi($branch_id)." and date = ".ms($date).")";
				$filters[] = "(sku.is_fresh_market='no' or (sku.is_fresh_market='inherit' and cc.is_fresh_market='no'))";
				if($sku_type) $filters[] = "sku.sku_type = ".ms($sku_type);
				
				$cat_id_list = array();
				if($_REQUEST['category_id_list']){
					$cat_id_list = explode(",", $_REQUEST['category_id_list']);
					$extra_logs[] = " Category#".$_REQUEST['category_id_list'];
				}else $cat_id_list = array(0);
				
				if($_REQUEST['vendor_id_list']){
					$filters[] = "sku.vendor_id in (".$_REQUEST['vendor_id_list'].")";
					$extra_logs[] = " Vendor#".$_REQUEST['vendor_id_list'];
				}
				
				if($_REQUEST['brand_id_list']){
					$filters[] = " sku.brand_id in (".$_REQUEST['brand_id_list'].")";
					$extra_logs[] = " Brand#".$_REQUEST['brand_id_list'];
				}

				foreach($cat_id_list as $cid){
					$cat_info = array();
					if($cid > 0){ // found the user did provide category for filling zero
						$q1 = $con->sql_query("select id, level from category where id = ".mi($cid));
						$cat_info = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
					}else{ // it is filling zero by vendor and brand only
						$cat_info['level'] = 0;
						$cat_info['id'] = 0;
					}
						
					$cat_filter = " and cc.p".mi($cat_info['level'])." = ".mi($cat_info['id']);
                        
                    $q_si = $con->sql_query("select si.id, si.sku_item_code
											 from sku_items si
											 left join sku on sku.id = si.sku_id
											 left join category_cache cc on cc.category_id = sku.category_id
											 where ".join(" and ", $filters)." $cat_filter
											 order by sku_item_code");
    
					while($si = $con->sql_fetchassoc($q_si)){
						$upd['sku_item_code'] = $si['sku_item_code'];
						$tmp = array();
						$tmp = get_sku_item_cost_selling($branch_id,$si['id'],$date, $cost_params);
						$upd['cost'] = $tmp['cost'];
						
						$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
						
						$sku_item_id_list[$si['id']] = $si['id'];
						if(count($sku_item_id_list)>=1000){
							$con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")");
							$sku_item_id_list = array();
						}
						$imported_count++;
					}
					$con->sql_freeresult($q_si);
                }

				break;
		}

		// update again for other sku if got
		if($sku_item_id_list){
			for($i=0; $i<count($sku_item_id_list); $i+=1000){
                $con->sql_query("update low_priority sku_items_cost set changed=1 where branch_id=$branch_id and sku_item_id in (".join(',',array_slice($sku_item_id_list, $i, $i+1000)).")");
			}
		}
		$sku_item_id_list = array();
		
        //$con->sql_query("alter table stock_check enable keys");

		$time_end = microtime(true);

		$tt=$time_end-$time_start;

        if($extra_logs){
            $extra_log = ",".join(" / ", $extra_logs);
        }
        
        log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Import Stock Take (Branch#".$branch_id.", Date#$date, Fill Zero Option: $_REQUEST[fill_zero_options]".$extra_log.")");
        $msg = "$bcode ($date) Import Success, $imported_count items imported. Time used: $tt sec";
        header("Location: $_SERVER[PHP_SELF]?a=import_page&t=import&branch_id=$branch_id&msg=$msg");
        exit;
    }
    
    function reset_stock_take()
    {
        global $con, $smarty,$sessioninfo, $LANG, $appCore, $config;
        
        $branch_id = get_request_branch();
        $bcode = get_branch_code($branch_id);
        $date = $_REQUEST['stock_take_date'];
        $sku_type = $_REQUEST['sku_type'];
		$join_sku = "";
		$filter_sku = "";
		
		//check closed month
		/*if($config['monthly_closing']){
			$is_month_closed = $appCore->is_month_closed($date);
			if($is_month_closed){
				$err = sprintf($LANG['BLOCK_MONTH_CLOSED_DOCUMENT'], "reset");
				header("Location: $_SERVER[PHP_SELF]?a=import_page&t=reset&branch_id=$branch_id&imported=1&msg=$err");
				exit;
			}
		}*/

		if ($sku_type != ""){
		    $join_sku="LEFT JOIN sku on si.sku_id=sku.id";
		    $filter_sku= "and sku.sku_type=".ms($sku_type);
        }
		
        $time_start = microtime(true);

		$sql = "select distinct(si.id) as sku_items_id, sc.sku_item_code
				from sku_items si
				left join stock_check sc on sc.sku_item_code = si.sku_item_code
				$join_sku
				where sc.is_fresh_market=0 and sc.date =".ms($date)." and sc.branch_id = $branch_id $filter_sku";

        $result = $con->sql_query($sql) or die(mysql_error());
		
		$c = 0;
        //$con->sql_query("alter table stock_check disable keys");
        
        while($r = $con->sql_fetchrow($result))
        {
            $sku_items_id[$r['sku_items_id']] = $r['sku_items_id'];
/*
			if ($r['stp_id'])
			    $stp_id[$r['stp_id']]=$r['stp_id'];

			if ($r['sku_item_code'])
				$sc_code[$r['sku_item_code']]=ms($r['sku_item_code']);
*/
            if (count($sku_items_id)>1000)
			{
				// possible too many item...
	        	$con->sql_query("update sku_items_cost set changed = '1' where branch_id = $branch_id and sku_item_id in (".join(',',$sku_items_id).")") or die(mysql_error());
	        	$sku_items_id = array();
        	}
        	$c++;
        }
/*
        if ($sku_type!=''){
			$join_code = "sku_item_code in (".join(',',$sc_code).") and";
			$join_stock_id = "id in (".join(',',$stp_id).") and ";
		}
*/
        if (count($sku_items_id)>0)
			$con->sql_query("update sku_items_cost set changed = '1' where branch_id = $branch_id and sku_item_id in (".join(',',$sku_items_id).")");

        $con->sql_query("delete from stock_check where date = ".ms($date)." and branch_id = $branch_id and is_fresh_market=0") ;

    	$con->sql_query("update stock_take_pre set imported = '0' where is_fresh_market=0 and branch_id = $branch_id and date=".ms($date));

        //$con->sql_query("alter table stock_check enable keys");
        
   		$time_end = microtime(true);
		$tt=$time_end-$time_start;
		
        log_br($sessioninfo['id'], 'Stock Take', $_REQUEST['id'], "Reset Stock Take (Branch#".$branch_id.", Date#$date)");
        $msg = "$bcode ($date) Reset Success, $c items reset.  Time used: $tt sec";
        header("Location: $_SERVER[PHP_SELF]?a=import_page&t=reset&branch_id=$branch_id&imported=1&msg=$msg");
        exit;
    }
    
    function ajax_reload_shelf_range(){
        global $con, $smarty, $sessioninfo;
        
        $bid = mi($sessioninfo['branch_id']);
		$date = $_REQUEST['dat'];
		$loc2 = trim($_REQUEST['loc2']);
		$loc3 = trim($_REQUEST['loc3']);
		
		$sql = "select distinct(shelf) from stock_take_pre where branch_id=$bid and date=".ms($date)." and location between ".ms($loc2)." and ".ms($loc3)." and stock_take_pre.is_fresh_market=0 order by shelf";
		//print $sql;
		$con->sql_query($sql);
		$smarty->assign('shelf',$con->sql_fetchrowset());
        $smarty->display('admin.stock_take.range.shelf.tpl');
	}
	
	function ajax_load_date($sqlonly = false){
		global $con, $sessioninfo, $smarty;
		$branch_id = isset($_REQUEST['branch_id']) ? mi($_REQUEST['branch_id']) : $sessioninfo['branch_id'];
		$con->sql_query("select distinct(date) from stock_take_pre where branch_id=$branch_id and imported=0 and stock_take_pre.is_fresh_market=0 order by date desc");
		
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
	
	function change_batch(){
		global $con;
		
		// change title
		$this->update_title($this->title .' - Change Batch');
		
		if($this->can_select_branch)	$this->load_branches();
		$this->ajax_load_date(true);
		
		$this->display('admin.stock_take.change_batch.tpl');	
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
		$filter[] = "imported=0 and is_fresh_market=0";
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
		$this->display('admin.stock_take.change_batch.popup.tpl');
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
			$filter[] = "imported=0 and is_fresh_market=0";
			
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
	
	// if found customer system setting is set as either below:
	// A) Average Cost with parent-child calculation
	// B) Last GRN Cost with parent-child calculation
	function import_st_data_validate(){
		global $con, $smarty, $sessioninfo, $config, $LANG;

        $branch_id = get_request_branch();
        $bcode = get_branch_code($branch_id);
        $date = $_REQUEST['stock_take_date'];
        $sku_type = $_REQUEST['sku_type'];
		$fill_zero_options = $_REQUEST['fill_zero_options'];

		$ret = $sku_data = $si_list = array();
		$sb_tbl = "stock_balance_b".$branch_id."_".date("Y",strtotime($date));

        $time_start = microtime(true);
		// get items need to import
        $rs = $con->sql_query("select stp.*, si.sku_item_code, si.sku_id, si.id as sku_item_id, u.fraction as uom_fraction, sb.qty as sb_qty, si.doc_allow_decimal,
							   si.mcode, si.artno, si.description, user.u, u.code as uom_code,
							   if(sku.sku_type='CONSIGN',(ifnull(sp.trade_discount_code, sku.default_trade_discount_code)),'') as trade_discount_code
							   from stock_take_pre stp
							   left join sku_items si on stp.sku_item_id = si.id
							   left join sku on sku.id = si.sku_id
							   left join uom u on u.id=	si.packing_uom_id
							   left join user on user.id = stp.user_id
							   left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
							   left join sku_items_price sp on sp.sku_item_id = si.id and sp.branch_id = ".mi($branch_id)."
							   where stp.branch_id = ".mi($branch_id)." and stp.date =".ms($date)." and stp.imported = '0' and stp.is_fresh_market=0
							   order by stp.date,stp.location,stp.shelf,stp.id");

		$item_count = mi($con->sql_numrows($rs));
		if($item_count <=0){
			js_redirect("There is no item to import at this time.", "$_SERVER[PHP_SELF]?a=import_page&t=import&branch_id=$branch_id");
		}
		// sum qty for same branch,date,location,shelf,item
        while($r = $con->sql_fetchassoc($rs)){
        	if ($r['sku_item_id'] > 0){
	            //$key = mi($r['branch_id'])."_".date('Ymd',strtotime($r['date']))."_".mi($r['sku_item_id'])."_".trim($r['location'])."_".trim($r['shelf_no']);
	            $key = mi($r['branch_id'])."_".date('Ymd',strtotime($r['date']))."_".$r['location']."_".$r['shelf']."_".$r['cost_price']."_".mi($r['sku_item_id']);
	            if(isset($ret['data'][$key]))  $ret['data'][$key]['qty'] += $r['qty'];
	            else $ret['data'][$key] = $r;
				
				// need to check the cost whether got diference with system or not
				if($config['stock_take_enable_check_cost'] && ($config['sku_use_avg_cost_as_last_cost'] || $config['sku_update_cost_by_parent_child'])){
					if($r['cost_price'] != 0){
						$sku_id = $r['sku_id'];
						$new_unit_cost = round($r['cost_price'] / $r['uom_fraction'], $config['global_cost_decimal_points']);
						
						if(isset($sku_data[$sku_id]) && $sku_data[$sku_id]['new_unit_cost'] != $new_unit_cost){ // found having different unit cost price from import file
							$r['error'] = sprintf($LANG['ST_COST_INVALID'], $r['uom_code'], number_format($sku_data[$sku_id]['new_unit_cost'], $config['global_cost_decimal_points']));
							$ret['error'] = 1;
							$ret['cost_st_id_list'][$r['id']] = $r;
						}else $sku_data[$sku_id]['new_unit_cost'] = $new_unit_cost;
					}
					
					// if no fill zero, need to check whether have stock take all parent child
					if($fill_zero_options == "no_fill" && !isset($ret['sku_item_id_list'][$r['sku_item_id']])){
						$si_list[$r['sku_id']]['count']++;
					}
				}

				$ret['stock_take_id_list'][$r['id']]=$r['id'];
				$ret['sku_item_id_list'][$r['sku_item_id']] = $r['sku_item_id'];
				if($fill_zero_options == "fill_parent") $ret['sku_id_list'][$r['sku_id']] = $r['sku_id'];
            }
        }
		$con->sql_freeresult($rs);
		unset($sku_data);
		
		// need to check if user select "no auto fill zero" and didn't stock take the rest SKU from same family
		if($config['stock_take_enable_check_cost'] && ($config['sku_use_avg_cost_as_last_cost'] || $config['sku_update_cost_by_parent_child']) && $fill_zero_options == "no_fill" && $si_list){
			foreach($si_list as $tmp_sku_id=>$tmp){
				if($tmp['count'] == 0) continue;
				
				$q1 = $con->sql_query("select count(*) as db_si_count from sku_items where sku_id = ".mi($tmp_sku_id));
				$si_info = $con->sql_fetchassoc($q1);
				
				// compare and see if user did stock take for SKU family, otherwise show error msg
				if($si_info['db_si_count'] != $tmp['count']){
					$err_si_list = array();
					$file_path = "tmp/st_si_child.csv";
					if(file_exists($file_path)) unlink($file_path);
					$q1 = $con->sql_query("select stp.*, stp.id as st_id, si.sku_item_code, si.mcode, si.artno, si.description, user.u, si.is_parent, si.active
										   from sku_items si
										   left join sku on sku.id = si.sku_id
										   left join stock_take_pre stp on stp.sku_item_id = si.id and stp.branch_id = ".mi($branch_id)." and stp.date =".ms($date)." and stp.imported = '0' and stp.is_fresh_market=0
										   left join uom u on u.id=	si.packing_uom_id
										   left join user on user.id = stp.user_id
										   left join $sb_tbl sb on sb.sku_item_id=si.id and ((".ms($date)." between sb.from_date and sb.to_date) or (".ms($date)." >= sb.from_date and sb.is_latest=1))
										   left join sku_items_price sp on sp.sku_item_id = si.id and sp.branch_id = ".mi($branch_id)."
										   where si.sku_id = ".mi($tmp_sku_id)."
										   order by stp.date,stp.location,stp.shelf,stp.id");
					
					while($r = $con->sql_fetchassoc($q1)){
						if($r['is_parent']){
							$st_info['sku_item_code'] = $r['sku_item_code'];
							$st_info['mcode'] = $r['mcode'];
							$st_info['artno'] = $r['artno'];
							$st_info['description'] = $r['description'];
						}
						
						// if found it is not under stock take, need to capture the list
						if(!$r['st_id'] && $r['active']){
							$row = array();
							$row[] = $r['sku_item_code'];
							$row[] = $r['artno'];
							$row[] = $r['mcode'];
							$row[] = $r['description'];
							$err_si_list[] = $row;
						}
					}
					$con->sql_freeresult($q1);
					
					if($err_si_list){
						$fp = fopen($file_path, 'w');
						$header = array();
						$header[] = "SKU_ITEM_CODE";
						$header[] = "ARTNO";
						$header[] = "MCODE";
						$header[] = "DESCRIPTION";
						fputcsv($fp, $header);

						foreach($err_si_list as $arr=>$tmp){
							fputcsv($fp, $tmp);
						}
						fclose($fp);
						
						chmod($file_path, 0777);
						$smarty->assign("pc_file_path", $file_path);
					}
					
					$ret['pc_st_id_list'][$st_info['id']] = $st_info;
					$ret['error'] = 1;
				}
			}
		}
		unset($si_list, $header, $err_si_list);
		
		return $ret;
	}
}
$stock_take = new Stock_Take ('Stock Take');
?>
