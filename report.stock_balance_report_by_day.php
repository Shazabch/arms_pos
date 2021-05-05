<?php
/*
3/12/2010 1:31:05 PM Andy
- Fix figure "show by branch" different with "show by sku"

5/6/2010 5:10:12 PM Alex
- Add HQ cost

8/13/2010 10:05:49 AM Andy
- Hide Item with "SKU without inventory".

1/3/2011 6:46:20 PM Alex
- fix bugs open previous year stock balance

2/23/2011 5:39:40 PM Andy
- Change create stock balance table query to use common function initial_branch_sb_table()

6/27/2011 9:58:38 AM Andy
- Make all branch default sort by sequence, code.

7/6/2011 2:44:37 PM Andy
- Change split() to use explode()

7/7/2011 11:00:50 AM Alex
- add default date

8/4/2011 12:25:28 PM Alex
- add selling price for opening, closing

8/8/2011 11:01:37 AM Alex
- add unit price

8/12/2011 5:47:21 PM Justin
- Added to filter must active for sku items.

8/15/2011 11:33:21 AM Justin
- Added filter "Blocked Item in PO" in stock balance by department report.
- Added filter "Status" for SKU.

4/12/2012 5:10:10 PM Andy
- Fix filter SKU status not working.

4/18/2012 4:59:09 PM Andy
- Fix report when choose "all" or "region" will show no data.

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.
*/

include("include/common.php");
include("include/excelwriter.php");
include("include/class.report.php");
//ini_set("display_error",1);
//$con = new sql_db('jwt-uni.dyndns.org','arms','4383659','armshq');
//$con = new sql_db('ws-hq.arms.com.my:4001','arms_slave','arms_slave','armshq');//$con = new sql_db('cwmhq.no-ip.org:4001','arms','sc440','armshq');
//$con = new sql_db('cutemaree.dyndns.org:4001','arms','990506','armshq',false);
//print_r($_REQUEST);
//print_r($con);

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

ini_set('memory_limit', '1024M');
set_time_limit(0);

if (!$_REQUEST['from']) $_REQUEST['from'] = date('Y-m-d');
$smarty->assign("PAGE_TITLE", "Stock Balance Report by Day");

init_load_data();

if(isset($_REQUEST['a'])){
	switch($_REQUEST['a'])
	{
	    case 'stock_balance':
	          $smarty->assign("table",1);
	          if(isset($_REQUEST['export_xls']))
	          {
	              export_excel();exit;
	          }
	          branch_stock_balance();
	      break;
	    default:
	    	branch_stock_balance();
	    exit;
	}
}

$smarty->display('report.stock_balance_report_by_day.tpl');

function init_load_data(){
	global $con, $smarty,$config,$branches_group,$branches;

    $con->sql_query("select distinct(code) from branch_group");
    while($r = $con->sql_fetchrow())
    {
        $branch_group_code[] = $r['code'];
    }

    //select sku_type
    $con->sql_query("select * from sku_type");
    $smarty->assign("sku_type", $con->sql_fetchrowset());

    // branches
    $q1 = $con->sql_query("select * from branch where active = '1' order by sequence,code");
    while($r = $con->sql_fetchassoc($q1)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	// load items
	$q1 = $con->sql_query("select bgi.*,branch.code,branch.description
						   from branch_group_items bgi
						   left join branch on bgi.branch_id=branch.id
						   where branch.active = '1' ",false,false);
	while($r = $con->sql_fetchassoc($q1)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$con->sql_freeresult($q1);
	
	// branch group
	// load header
	$q1 = $con->sql_query("select * from branch_group",false,false);
	while($r = $con->sql_fetchassoc($q1)){
		if(!$branches_group['items'][$r['id']]) continue;
	    $branches_group['header'][$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

    $smarty->assign('branches_group',$branches_group);
	$smarty->assign('branches',$branches);
}

function branch_stock_balance()
{
    global $con, $smarty,$config,$branches,$branches_group;

    if($_REQUEST['from'])
    {
		$brn_id =get_request_branch(true);
		// checking parameters
		$bid_list = array();
		$where = array();

		if($brn_id>0){   // selected single branch
			$bid_list[] = $brn_id;
			$br = $branches[$brn_id]['1'];
		}else{
			if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
				$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
				$q1 = $con->sql_query("select b.* from branch b where b.active = 1 and b.region = ".ms($region));

				while($r = $con->sql_fetchassoc($q1)){
					if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
					$bid_list[] = $r['id'];
				}

				$con->sql_freeresult($q1);
			}elseif($brn_id<0){   // negative branch id is branch group
				$bgid = abs($brn_id);
				if(!$branches_group['items'][$bgid])    $err[] = "Invalid Branch.";
				else{
					foreach($branches_group['items'][$bgid] as $bid=>$b){
						$bid_list[] = $bid;
						$br = $branches_group['header'][$bgid]['code'];
					}
				}
			}else{  // all branches
				foreach($branches as $b){
		              $bid_list[] = $b['id'];
				}
			}
		}

		$smarty->assign("br",$br);

		//sku type
		if($_REQUEST['sku_type']!=="all")
		{
		  $where[] = "sku.sku_type  = ".ms($_REQUEST['sku_type']);
		}

		/*
		//check whether is all of selected branch group id
		if($_REQUEST['branch_id']!=="all")
		{
		  if(count($bid_list)>1)
		  {
		      $code = " where branch_group.code = ".abs($_REQUEST['branch_id']);
		      //get branch group id
		      $con->sql_query("select branch_group_items.branch_id from branch_group LEFT JOIN branch_group_items on branch_group.id = branch_group_items.branch_group_id LEFT JOIN branch on branch.id = branch_group_items.branch_id".ms($code)." order by branch.sequence, branch.code") or die(mysql_error());
		  }
		}else
		{
		  //if all
		  $con->sql_query("select distinct(id) as branch_id from branch order by branch.sequence, branch.code") or die(mysql_error());
		}

		$r = $con->sql_fetchrowset();
		*/

		$blocked_po = trim($_REQUEST['blocked_po']);
		$status = trim($_REQUEST['status']);

		if($blocked_po){
			if($blocked_po=='yes'){
				$where[] = "si.block_list like ".ms("%i:$bid;s:2:\"on\";%");
			}elseif($blocked_po=='no'){
				$where[] = "(si.block_list not like ".ms("%i:$bid;s:2:\"on\";%")." or si.block_list is null)";
			}
		}
		
		if($status != "all") $where[] = "si.active = ".mi($status);
		
		if(!$_REQUEST['all_cat'])
		{
		  	$con->sql_query("select level,description from category where id = ".mi($_REQUEST['category_id']));
			$lv = $con->sql_fetchrow();
			$level = $lv['level'];
			$description = $lv['description'];
			$where[] = "p$level = ".mi($_REQUEST['category_id']);
		}
		$opening_date = date("Y-m-d",strtotime("-1 day",strtotime($_REQUEST['from'])));

		if($where) $filter = " and ".join(" and ", $where);
		
		$con_multi = new mysql_multi();

		foreach($bid_list as $val)
		{
		  //$branch_id =  $val['branch_id'];
			$branch_id =  $val;
			$year_opening = substr($opening_date, 0, 4);
			$year_closing = substr($_REQUEST['from'], 0, 4);
			$table_opening = "stock_balance_b".$branch_id."_".$year_opening;
			$table_closing = "stock_balance_b".$branch_id."_".$year_closing;

			// create table first
			initial_branch_sb_table(array('tbl'=>$table_opening));
			initial_branch_sb_table(array('tbl'=>$table_closing));
			
			/*$sql_check="create table if not exists $table_opening (
					  sku_item_id int not null,
					  from_date date,
					  to_date date,
					  qty double,
					  cost double,
					  avg_cost double,
					  is_latest tinyint(1),
					  index(sku_item_id),index(from_date),index(to_date),index(is_latest)
					  )";

			$con_multi->sql_query($sql_check) or die(mysql_error());

			$sql_check2="create table if not exists $table_closing (
					  sku_item_id int not null,
					  from_date date,
					  to_date date,
					  qty double,
					  cost double,
					  avg_cost double,
					  is_latest tinyint(1),
					  index(sku_item_id),index(from_date),index(to_date),index(is_latest)
					  )";

			$con_multi->sql_query($sql_check2) or die(mysql_error());*/

			//get branch code
			$con->sql_query("select distinct(code),description from branch where id = ".ms($branch_id));
			$rs = $con->sql_fetchrow();

			if ($_REQUEST['hq_cost']) $extrasql = ", sum(qty*hq_cost) as closing";

			if ($config['stock_balance_report_show_additional_selling']){
				//get selling price
				$cl_sell_sql=",sum(qty*(ifnull((select price from sku_items_price_history osh
						where osh.branch_id=$branch_id and osh.sku_item_id=si.id and osh.added <= ".ms($_REQUEST['from'])."
						order by osh.added desc limit 1),si.selling_price))) as cs_selling";
						
				$op_sell_sql=", sum(qty*(ifnull((select price from sku_items_price_history osh2
						where osh2.branch_id=$branch_id and osh2.sku_item_id=si.id and osh2.added <= ".ms($opening_date)."
						order by osh2.added desc limit 1),si.selling_price))) as cs_selling";
				
			}

			if($_REQUEST['rpt_type']=="branch"){
				$sub=$con_multi->sql_query("select 'close' as type,sum(qty) as total_qty, sum(qty*cost) as closing $extrasql $cl_sell_sql
							from ".$table_closing."
							left join sku_items si on ".$table_closing.".sku_item_id = si.id
							left join sku on si.sku_id = sku.id 
							left join category_cache using (category_id)
							where ".ms($_REQUEST['from'])." between from_date and to_date and ((sku.no_inventory='inherit' and category_cache.no_inventory='no') or sku.no_inventory='no') ".$filter."
							union
							select 'open' as type,sum(qty) as total_qty, sum(qty*cost) as closing $extrasql $op_sell_sql
							from ".$table_opening."
							left join sku_items si on ".$table_opening.".sku_item_id = si.id
							left join sku on si.sku_id = sku.id
							left join category_cache using (category_id)
							where ".ms($opening_date)." between from_date and to_date and ((sku.no_inventory='inherit' and category_cache.no_inventory='no') or sku.no_inventory='no') ".$filter);

				while($r = $con_multi->sql_fetchassoc($sub))
				{
					if($r['type']=="open")
					{
						$data['branch'][$rs['code']]['closing']['open'] = $r['closing'];
						$data['branch'][$rs['code']]['closing']['open_selling'] = $r['cs_selling'];
						$data['branch'][$rs['code']]['qty']['open'] = $r['total_qty'];
						$branch_open_total += intval($r['total_qty']);
					}else
					{
						$data['branch'][$rs['code']]['closing']['close'] = $r['closing'];
						$data['branch'][$rs['code']]['qty']['close'] = $r['total_qty'];
						$data['branch'][$rs['code']]['closing']['close_selling'] = $r['cs_selling'];
					}
					$data['branch'][$rs['code']]['description'] = $rs['description'];

					$smarty->assign("is_branch",1);
				}
				
				$con_multi->sql_freeresult($sub);
				
		  	}else{
				if($_REQUEST['sorting']!=="all"){
				  $sort = " order by ".$_REQUEST['sorting'].",sku_item_id";
				}else{
				  $sort = " order by sku_item_id";
				}

				if ($_REQUEST['hq_cost']) $extrasql = ", si.hq_cost as cost";

				if ($config['stock_balance_report_show_additional_selling']){
					//get selling price
					$cl_sell_sql=", (ifnull((select price from sku_items_price_history osh
							where osh.branch_id=$branch_id and osh.sku_item_id=si.id and osh.added <= ".ms($_REQUEST['from'])."
							order by osh.added desc limit 1),si.selling_price)) as cs_selling";
							
					$op_sell_sql=", (ifnull((select price from sku_items_price_history osh2
							where osh2.branch_id=$branch_id and osh2.sku_item_id=si.id and osh2.added <= ".ms($opening_date)."
							order by osh2.added desc limit 1),si.selling_price)) as cs_selling";
					
				}

				$sql = "select 'close' as type,tbl_c.*,si.sku_item_code as sku,si.description as sku_desc,si.mcode as sku_mcode,si.artno as sku_artno,si.link_code $extrasql $cl_sell_sql
									from ".$table_closing." tbl_c
									left join sku_items si on tbl_c.sku_item_id = si.id
									left join sku on si.sku_id = sku.id
									left join category_cache using (category_id)
									where ".ms($_REQUEST['from'])." between from_date and to_date and ((sku.no_inventory='inherit' and category_cache.no_inventory='no') or sku.no_inventory='no') ".$filter."
									union
									select 'open' as type,tbl_o.*,si.sku_item_code as sku,si.description as sku_desc,si.mcode as sku_mcode,si.artno as sku_artno,si.link_code $extrasql $op_sell_sql
									from ".$table_opening." tbl_o
									left join sku_items si on tbl_o.sku_item_id = si.id
									left join sku on si.sku_id = sku.id
									left join category_cache using (category_id)
									where ".ms($opening_date)." between from_date and to_date and ((sku.no_inventory='inherit' and category_cache.no_inventory='no') or sku.no_inventory='no') ".$filter.$sort;
									
				$main = $con_multi->sql_query($sql);

				$c = 0;
				while($r2 = $con_multi->sql_fetchassoc($main))
				{
					$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['description'] = $r2['sku_desc'];
					$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['mcode'] = $r2['sku_mcode'];
					$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['artno'] = $r2['sku_artno'];

		              //if($config['consignment_modules']) //reverse
					if(false)
					{
		                  //take consignment grn cost
							/*
		                  if($r2['type']=="open")
		                  {
		                      $sk_id = $con->sql_query("select * from sku_items_cost_history where branch_id = '1' and date <=".ms($opening_date)." and sku_item_id = ".ms($r2['sku_item_id'])." order by date desc limit 1");
		                      $rr2 = $con->sql_fetchrow($sk_id);

		                      if($rr2['grn_cost'])
		                      {
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['open']= $rr2['grn_cost'];
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['open']+= $r2['qty']*$rr2['grn_cost'];

		                      }else
		                      {
		                          $mas = $con->sql_query("select cost_price from sku_items where id = ".ms($r2['sku_item_id']));

		                          $rr3 = $con->sql_fetchrow($mas);
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['open']= $rr3['cost_price'];
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['open']+= $r2['qty']*$rr3['cost_price'];

		                      }

		                  }else
		                  {
		                      $sk_id = $con->sql_query("select * from sku_items_cost_history where branch_id = '1' and date <=".ms($_REQUEST['from'])." and sku_item_id = ".ms($r2['sku_item_id'])." order by date desc limit 1");
		                      $rr4 = $con->sql_fetchrow($sk_id);

		                      if($rr4['grn_cost'])
		                      {
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['close']= $rr4['grn_cost'];
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['close']+= $r2['qty']*$rr4['grn_cost'];

		                      }else
		                      {
		                          $mas = $con->sql_query("select cost_price from sku_items where id = ".ms($r2['sku_item_id']));

		                          $rr5 = $con->sql_fetchrow($mas);
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['close']= $rr5['cost_price'];
		                          $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['close']+= $r2['qty']*$rr5['cost_price'];

		                      }

		                  }

		                  //check quantity
		                  if($r2['type']=="open")
		                  {
		                      $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['qty']['open'] += intval($r2['qty']);
		                  }
		                  else
		                  {
		                      $data[$r2[$_REQUEST['sorting']]][$r2['sku']]['qty']['close'] += intval($r2['qty']);
		                  }*/
					}
					else
					{
					  // no consignment cost
						if($r2['type']=="open")
						{
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['open']= $r2['cost'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['open']+= $r2['qty']*$r2['cost'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['unit_selling']['open']= $r2['cs_selling'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['qty']['open'] += $r2['qty'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['open_selling'] += $r2['qty']*$r2['cs_selling'];
						}else
						{
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['grn_cost']['close']= $r2['cost'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['close']+= $r2['qty']*$r2['cost'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['unit_selling']['close']= $r2['cs_selling'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['qty']['close'] += $r2['qty'];
							$data[$r2[$_REQUEST['sorting']]][$r2['sku']]['closing']['close_selling'] += $r2['qty']*$r2['cs_selling'];
						}

					}
					$sku_open_total += intval($r2['qty']);
				}
				$con_multi->sql_freeresult($main);
			}
		}
    	$con_multi->close_connection();
    }
    if($data)
    {
        ksort($data);
    }

    if(!$description)
      $description = "All";

    if(preg_match("/^REGION_/", $_REQUEST['branch_id'])){
		$region = str_replace("REGION_", "", $_REQUEST['branch_id']);
		$str_branch = "Region: ".$region;
	}elseif($_REQUEST['branch_id']<0)
		$str_branch = "Branch Group: ".$br;
    else{
		if (empty($br)) $br="All";

		$str_branch = "Branch: ".$br;
	}
	
    $report_title[] = "Date: ".$_REQUEST['from'];
	$report_title[] = "Category: ".$description;
	$report_title[] = $str_branch;

    $smarty->assign("data",$data);
    $smarty->assign("branch_group",$branch_group_code);

	if($_REQUEST['blocked_po']){
		$report_title[] = "Blocked Item in PO: ".ucwords($_REQUEST['blocked_po']);
	}

	if(!$_REQUEST['status']) $status = "Inactive";
	elseif($_REQUEST['status'] == 1) $status = "Active";
	else $status = ucwords($_REQUEST['status']);

	$report_title[] = "Status: ".$status;

	$report_title[] = "SKU Type: ".$_REQUEST['sku_type'];
	
    $smarty->assign('report_title',join("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $report_title));
}

function export_excel()
{
    global $smarty,$sessioninfo;

    $filename = $_REQUEST['fn'];
    $smarty->assign("csv",1);
    $_REQUEST['v'](true);
    $excel = false;
  	//$file=basename(tempnam(getcwd(),'tmp'));
    $file = $_REQUEST['f'];
  	$excel=new ExcelWriter($file);
  	if($excel->fp==false) die($excel->error);
    log_br($sessioninfo['id'], 'REPORT_EXPORT', 0, "Export $_REQUEST[title] To Excel($_REQUEST[report_title])");
    fwrite($excel->fp, "<h1>".$smarty->get_template_vars("title")."</h1>");
  	$raw = $smarty->fetch("$filename.tpl");
  	$start=0;

  	foreach(explode("\n", $raw) as $line)
  	{
    		if (stristr($line, "<!-- end -->")) break;
    		if ($start)
    		{
    		    $line = preg_replace(
    				array('/\s*align=center/i', '/<img src="mods\/logo\/([^.]+)\.png"[^>]*>/i', '/<img[^>]+>/i', '/(onmouseover|onmouseout|onclick)="[^"]+"/i','/(onmouseover|onmouseout|onclick)=[^\s>]+/i'),
    				array('','\1','','',''),
    				$line);

    			fwrite($excel->fp, $line);
    		}
  	    if (stristr($line, "<!-- start -->")) $start=1;
  	}
    $excel->close();

    header("Content-type: application/ms-excel");
    header("Content-Disposition: attachment; filename=$file"."_".time().".xls");
    readfile($file);

    exit;
}

function load_branches(){
	global $con, $smarty, $config;

	// branches
	$q1 = $con->sql_query("select * from branch where active = '1' order by sequence,code");
	while($r = $con->sql_fetchassoc($q1)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['id'])) continue;
		$branches[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	// load items
	$q1 = $con->sql_query("select bgi.*,branch.code,branch.description
						   from branch_group_items bgi
						   left join branch on bgi.branch_id=branch.id
						   where branch.active = '1' ",false,false);
	while($r = $con->sql_fetchassoc($q1)){
		if($config['masterfile_branch_region'] && $r['code'] != "HQ" && !check_user_regions($r['branch_id'])) continue;
		$branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
		$branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$con->sql_freeresult($q1);

	// branch group
	// load header
	$q1 = $con->sql_query("select * from branch_group",false,false);
	while($r = $con->sql_fetchassoc($q1)){
		if(!$branches_group['items'][$r['id']]) continue;
		$branches_group['header'][$r['id']] = $r;
	}
	$con->sql_freeresult($q1);

	$smarty->assign('branches_group',$branches_group);
	$smarty->assign('branches',$branches);

}

?>
