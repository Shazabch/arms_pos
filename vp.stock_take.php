<?php
/*
1/9/2013 2:17:00 PM Fithri
- rewrite the code, change JS and PHP to object-oriented

1/16/2013 9:39:00 AM Fithri
- import button change to "confirm stock take"
- zerolise checkbox change to "auto fill zero quantity for unfilled items"
- add column to show items category (level 3)
- can sort by category or description
- add print button to print out the item list

1/17/2013 9:55:00 AM Fithri
- enhanced show level 4 category instead of level 3.

1/17/2013 9:55:00 AM Fithri
- add column avg cost and amount for report

1/18/2013 10:50 AM Justin
- Enhanced sorting for category to have sorting for SKU Description as well.

1/23/2013 10:00:00 AM Fithri
- log when import stock take, capture whether user got tick auto fill zero
- location use branch code, shelf use vendor code
- continue item_no numbering in stock_check table from previous stock take if same date
- add stock balance, variance column
- when confirm, hide the empty qty row first, then need to click again final confirm
- show cost

2/1/2013 5:18 PM Justin
- Enhanced to pickup doc_allow_decimal.

6/6/2013 2:42 PM Andy
- Change scanned_by to use link_username instead of link_user_id.
- Change when select max(item_no), no need to filter scanned_by.

3/27/2020 6:00 PM William
- Enhanced to insert id manually for stock_take_pre table that use auto increment.

4/2/2020 5:18 PM William
- Enhanced to capture print log to log_vp.
*/

ini_set("display_errors",0);
ini_set('memory_limit', '256M');
set_time_limit(0);
include("include/common.php");

session_start();
if (!$vp_login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
$maintenance->check(1);

class Stock_Take extends Module
{


	function __construct($title, $template='')
	{
		global $config, $smarty,$con, $vp_session;
		
		$this->default_location = BRANCH_CODE;
		$this->default_shelf = $vp_session['code'];
		
		$this->branch_id = $vp_session['branch_id'];
		$this->date = $_REQUEST['date'];
		$this->sort_by = ($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : 'category';
		$smarty->assign('config',$config);
		parent::__construct($title, $template);
	}
	
	function _default()
	{
		global $smarty,$con,$vp_session;
		
		if($this->date) {
			$table=$this->load_stp_data();
			$smarty->assign("flows", $table);
		}
		
		//get date
		$uid = $vp_session['vp']['link_user_id'];
		$rs = $con->sql_query("select distinct(date)
							from stock_take_pre
							where
							user_id = $uid
							and branch_id=$this->branch_id
							and location = '$this->default_location'
							and shelf = '$this->default_shelf'
							and imported = '0'
							and stock_take_pre.is_fresh_market=0
							order by date desc
							");
		while ($r = $con->sql_fetchassoc($rs))$dat[]=$r;
		$con->sql_freeresult($rs);

		$smarty->assign("dat", $dat);
		$smarty->display('vp.stock_take.tpl');
	}

	function load_stp_data()
	{
	
		global $con,$vp_session;
		
		$sql = "select
				si.id,
				si.mcode,
				si.sku_item_code,
				si.description,
				'1' as new_item,
				/*'0' as sb_qty,*/
				ifnull(sic.grn_cost,si.cost_price) as cost_price,
				c.description as category,
				si.doc_allow_decimal
				from sku_group_item sgi
				left join sku_items si on si.sku_item_code=sgi.sku_item_code
				left join sku on si.sku_id = sku.id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category c on c.id = cc.p4
				left join sku_items_cost sic on si.id = sic.sku_item_id and sic.branch_id=".mi($this->branch_id)."
				join sku_group_vp_date_control vpdc on vpdc.branch_id=sgi.branch_id and vpdc.sku_group_id=sgi.sku_group_id and vpdc.sku_item_id=si.id and curdate() between vpdc.from_date and vpdc.to_date
				where
				sgi.branch_id=".mi($vp_session['sku_group_bid'])."
				and sgi.sku_group_id=".mi($vp_session['sku_group_id'])
				;
		$q4=$con->sql_query($sql);
		$group_items = $con->sql_fetchrowset();
		
		foreach ($group_items as $gikey => $gidata) {
			if ($_REQUEST['new_stock_take'] == '1') $group_items[$gikey]['sb_qty'] = '';
			else $group_items[$gikey]['sb_qty'] = '0';
		}
		
		$con->sql_freeresult();
		
		if ($_REQUEST['new_stock_take'] == '1') {
			if($this->sort_by == 'category') usort($group_items,array($this,'sort_cat'));
			else usort($group_items,array($this,'sort_si'));
			return $group_items;
		}
		
		$date = explode('-',$this->date);
		$year = $date[0];
		
		$sql = "select
				stp.qty,
				stp.cost_price,
				stp.id,
				stp.sku_item_id,
				sku_items.mcode,
				sku_items.description,
				sku_items.sku_item_code,
				ifnull(sb.qty,0) as sb_qty,
				c.description as category,
				sku_items.doc_allow_decimal
				from stock_take_pre stp
				left join stock_balance_b".$this->branch_id."_$year sb on ((".ms($this->date)." - interval 1 day) between sb.from_date and sb.to_date) and stp.sku_item_id = sb.sku_item_id
				left join sku_items ON stp.sku_item_id=sku_items.id
				left join sku on sku_items.sku_id = sku.id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category c on c.id = cc.p4
				where stp.date =".ms($this->date)." 
				and stp.location = ".ms($this->default_location)."
				and stp.shelf =".ms($this->default_shelf)."
				and stp.branch_id=$this->branch_id
				and stp.imported = '0'
				and stp.is_fresh_market=0
				order by stp.id
				";//print $sql;
				
		$con->sql_query($sql);
		$table = $con->sql_fetchrowset();
		$con->sql_freeresult();

		if ($_REQUEST['a'] == 'print_report') {
			if($this->sort_by == 'category') usort($table,array($this,'sort_cat'));
			else usort($table,array($this,'sort_si'));
			return $table;
		}
		
		$curr_sku_item_id = array();
		
		if ($table) {
			foreach ($table as $tkey => $tvalue) {
				$curr_sku_item_id[] = $tvalue['sku_item_id'];
			}
		}
		
		foreach ($group_items as $gikey => $givalue) {
			if (!in_array($givalue['id'],$curr_sku_item_id)) {
				$givalue['new_item'] = 1;
				$table[] = $givalue;
			}
		}

		if($this->sort_by == 'category') usort($table,array($this,'sort_cat'));
		else usort($table,array($this,'sort_si'));
		return $table;
	}

	function load_table_data()
	{
		global $con, $smarty,$vp_session;

		$this->date = $_REQUEST['dat'];
		$table=$this->load_stp_data();
		$smarty->assign("flows", $table);
		
		if($_REQUEST['stock_take_date']) $std = $_REQUEST['stock_take_date'];
		else $std = (!$_REQUEST['new_stock_take']) ? $this->date : '';
		
		$smarty->assign("stock_take_date", $std);
		$smarty->assign("zerolize", $_REQUEST['zerolize']);
		if ($_REQUEST['new_stock_take']) $smarty->assign("new_stock_take_remark", 1);

		$smarty->display("vp.stock_take.table.tpl");
	}
	
	function sort_table()
	{
		global $con, $smarty,$vp_session;
		$qtys = $_REQUEST['qtys'];
		//echo"<pre>";print_r($qtys);echo"</pre>";
		$smarty->assign('currqtys', $qtys);
		$this->load_table_data();
	}
	
	function save_edit()
	{
		global $con, $smarty,$vp_session, $appCore;
		
		$qty = $_REQUEST['qtys'];
		$cost_price = $_REQUEST['cost_prices'];
		$uid = $vp_session['vp']['link_user_id'];
		
		if(!$qty) exit;
		
		if ($_REQUEST['is_import'] != '1' && $_REQUEST['new_stock_take'] == '1') {
			//check if date already existed
			$con->sql_query("select id from stock_take_pre stp
							where stp.branch_id=$vp_session[branch_id]
							and stp.date='$_REQUEST[stock_take_date]'
							and stp.user_id=$uid
							and stp.location='$this->default_location'
							and stp.shelf='$this->default_shelf'
							and stp.imported=0
							limit 1
							");
			if ($existed = $con->sql_fetchfield(0)) {
				header("location: vp.stock_take.php?existed=$_REQUEST[stock_take_date]");
				exit;
			}
		}

		if ($_REQUEST['is_import'] != '1') {
			foreach($qty as $key=>$q)
			{
				
				//if ($_REQUEST['zerolize'] && $q == '') $q = '0'; //dont want this anymore, zerolize checkbox only matters when importing to stock_check
				if ($q == '') {
					if ($_REQUEST['is_new_item'][$key] != '1') {
						$result = $con->sql_query("delete from stock_take_pre where id = ".ms($key)." and branch_id=$vp_session[branch_id] limit 1");
					}
					continue;
				}
				
				$cp=trim($cost_price[$key]);
				if ($cp=='')	$cp='null';
				else	$cp=ms($cp);
				
				if ($_REQUEST['is_new_item'][$key] == '1') {
					$id = $appCore->generateNewID("stock_take_pre", "branch_id=".mi($vp_session['branch_id']));
					$ins = array(
						'id'			=>  $id,
						'branch_id'		=>	$vp_session['branch_id'],
						'date'			=>	$_REQUEST['stock_take_date'],
						'location'		=>	$this->default_location,
						'shelf'			=>	$this->default_shelf,
						'user_id'		=>	$vp_session['vp']['link_user_id'],
						'sku_item_id'	=>	$key,
						'qty'			=>	$q,
						'cost_price'	=>	mf($_REQUEST['cost_prices'][$key]),
					);
					$con->sql_query("insert into stock_take_pre ".mysql_insert_by_field($ins));
				}
				else {
					$result = $con->sql_query("update stock_take_pre set qty = ".ms($q).", cost_price = ".$cp." where id = ".ms($key)." and branch_id=$vp_session[branch_id] limit 1");
				}
			}
			log_vp($vp_session['id'], "VP STOCK TAKE", 0, "Saved Stock Take - Date $_REQUEST[stock_take_date] , Branch $vp_session[branch_id]");
		}
		
		if ($_REQUEST['is_import'] == '1') {
		
			$qc = $con->sql_query("select
									stp.date as date,
									stp.branch_id as branch_id,
									si.sku_item_code,
									stp.location,
									stp.shelf as shelf_no,
									ifnull(sip.price,si.selling_price) as selling,
									qty,
									stp.cost_price as cost
									from stock_take_pre stp
									left join sku_items si on stp.sku_item_id = si.id
									left join sku_items_price sip using (branch_id,sku_item_id)
									where stp.branch_id=$vp_session[branch_id]
									and stp.date='$_REQUEST[stock_take_date]'
									and stp.user_id=$uid
									and stp.location='$this->default_location'
									and stp.shelf='$this->default_shelf'
									and stp.imported=0
								");
			
			$max_no = $con->sql_query("
									select
									max(cast(item_no as unsigned)) as max_item_no
									from stock_check
									where date='$_REQUEST[stock_take_date]'
									and branch_id='$vp_session[branch_id]'
									and location='$this->default_location'
									and shelf_no='$this->default_shelf'
									and is_fresh_market='0'"
									);
			$res_max_no = $con->sql_fetchassoc($max_no);
			$con->sql_freeresult($max_no);
			
			$ct = ($res_max_no['max_item_no']) ? $res_max_no['max_item_no'] : 0;
			
			while($rc = $con->sql_fetchassoc($qc)){
				$rc['item_no'] = ++$ct;
				$rc['scanned_by'] = $vp_session['vp']['link_username'];
				
				$con->sql_query("insert into stock_check ".mysql_insert_by_field($rc));
			}
			$con->sql_freeresult($qc);
			
			// if they tick zerolize, all items must insert into stock_check
			if ($_REQUEST['zerolize']) {
				$this->date = $_REQUEST['stock_take_date'];
				$items = $this->load_stp_data();
				
				$zero_sku_item_id = array();
				foreach ($items as $ikey => $ivalue) {
					if (!$ivalue['new_item']) continue;
					$zero_sku_item_id[] = $ivalue['id'];
				}
				
				if ($zero_sku_item_id) {
					
					$zero_sku_item_id_list = join(',',$zero_sku_item_id);
					
					$sql1 = $con->sql_query("
											select
											si.id,
											si.sku_item_code,
											ifnull(sip.price,si.selling_price) as selling
											from sku_items si
											left join sku_items_price sip on si.id = sip.sku_item_id and sip.branch_id = $vp_session[branch_id]
											where si.id in ($zero_sku_item_id_list)
											");
					
					while($rc1 = $con->sql_fetchassoc($sql1)){
					
						$rc1['date'] = $this->date;
						$rc1['branch_id'] = $vp_session['branch_id'];
						$rc1['scanned_by'] = $uid;
						$rc1['location'] = $this->default_location;
						$rc1['shelf_no'] = $this->default_shelf;
						$rc1['qty'] = 0;
						$rc1['cost'] = $cost_price[$rc1['id']];
						$rc1['item_no'] = ++$ct; //this counter will continue from the non zero counter above
						unset($rc1['id']);
						$con->sql_query("insert into stock_check ".mysql_insert_by_field($rc1));
						
					}
					$con->sql_freeresult();
					
					//set sku item price change
					$con->sql_query("update sku_items_cost
									set changed=1
									where
									branch_id=$vp_session[branch_id]
									and sku_item_id in ($zero_sku_item_id_list)
									");
				}
				
			}
			
			//set sku item price change
			$con->sql_query("update sku_items_cost
							set changed=1
							where
							branch_id=$vp_session[branch_id]
							and sku_item_id in (
								select sku_item_id
								from stock_take_pre
								where
								branch_id = $vp_session[branch_id]
								and date = '$_REQUEST[stock_take_date]'
								and location = '$this->default_location'
								and shelf='$this->default_shelf'
								and user_id = $uid
								and imported=0
								)
							");
			
			//set stock imported
			$con->sql_query("update stock_take_pre stp
							set stp.imported=1
							where stp.branch_id=$vp_session[branch_id]
							and stp.date='$_REQUEST[stock_take_date]'
							and stp.user_id=$uid
							and stp.location='$this->default_location'
							and stp.shelf='$this->default_shelf'
							and stp.imported=0
							");
							
			$zerolize = ($_REQUEST['zerolize']) ? '(Zerolize items)' : '';
			log_vp($vp_session['id'], "VP STOCK TAKE", 0, "Import New Stock Take - Date $_REQUEST[stock_take_date] , Branch $vp_session[branch_id] $zerolize");
			log_br($vp_session['vp']['link_user_id'], "VP STOCK TAKE", 0, "Import New Stock Take - Date $_REQUEST[stock_take_date] , Branch $vp_session[branch_id] $zerolize");
			
		}
		
		$redir = (($_REQUEST['is_import'] != '1')) ? "date=$_REQUEST[stock_take_date]&saved=1&" : "";
		header("location: vp.stock_take.php?".$redir."msg=1&sort_by=$this->sort_by");
		exit;
	}
	
	function sort_cat($a,$b) {
		if($a['category'] == $b['category']){
			return $this->sort_si($a, $b);
		}
		return ($a['category'] < $b['category']) ? -1 : 1;
	}
	
	function sort_si($a,$b) {
		if($a['description'] == $b['description'])	return 0;
		return ($a['description'] < $b['description']) ? -1 : 1;
	}
	
	function print_report() {
		
		global $config, $smarty,$con, $vp_session;
		
		$br = $con->sql_query("select code,description from branch where id=$vp_session[branch_id] limit 1");
		$branch = $con->sql_fetchassoc($br);
		$smarty->assign("branch",$branch);
		
		$items = $this->load_stp_data();
		
		//get cost and amount
		foreach ($items as $itkey => $itvalue) {
			$con->sql_query("select avg_cost from sku_items_cost_history where branch_id='$vp_session[branch_id]' and sku_item_id='$itvalue[sku_item_id]' and date <= '$this->date' order by date desc limit 1");
			$avg_cost = $con->sql_fetchfield(0);
			$items[$itkey]['avg_cost'] = ($avg_cost) ? $avg_cost : '';
			$items[$itkey]['amount'] = $avg_cost*$items[$itkey]['qty'];
		}
		
		$item_per_page = $config['stock_take_print_item_row_per_page'] ? $config['stock_take_print_item_row_per_page'] : 20;
		$item_per_page = 20;
		$totalpage = ceil(count($items)/$item_per_page);
	
		for ($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
			$smarty->assign("PAGE_SIZE", $item_per_page);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter", $i);
			$smarty->assign("st_date", $_REQUEST['date']);
			$smarty->assign("items", array_slice($items,$i,$item_per_page));
			$smarty->display("vp.stock_take.print.tpl");
			$smarty->assign("skip_header",1);
		}
		log_vp($vp_session['id'], "VP STOCK TAKE", 0, "Print Stock Take - Date $_REQUEST[date] , Branch $vp_session[branch_id]");
	}

}

$stock_take = new Stock_Take ('Stock Take');

?>
