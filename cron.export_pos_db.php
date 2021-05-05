<?php
/*
8/16/2010 11:26:48 AM yinsee
- updated sync_promotion() -- fix duplicate key bug
- add discount_limit to users table

5/11/2011 10:30:47 AM Alex
- create exactly column same as frontend database
- add consignment bearing

5/31/2011 1:03:30 PM Justin
- Rename the "grn_batch_items" into "sku_batch_items".

7/6/2011 11:14:36 AM Andy
- Change split() to use explode()

7/14/2011 9:42:15 AM Andy
- Change ./sqlite3.sh to sqlite3 only.
- Add promo_type at promotion.
- Add checking for 'non_member_use_net' and 'member_use_net', same as how sync daemon does.
- Add create table 'membership_promotion_items' and 'promotion_mix_n_match_items'.
- Change export category to use passthru()
- Add sync_redemption()
- Add sync category description.

9/28/2011 6:41:52 PM Andy
- Fix escape branch code in shell command. 

10/10/2011 4:03:06 PM Andy
- Add sql_begin_transaction() and sql_commit() to improve generate speed.
- Make category sync until 10 level.

11/11/2011 4:23:21 PM Andy
- Make compatible with BETA 140, LINUX 120.

03/19/2012 10:55:00 AM Kee Kee
- Make compatible with BETA 149.

05/18/2012 10:55:00 AM Kee Kee
- Make compatible with BETA 151+150.

07/02/2012 4:56:00 AM Kee Kee
- Make compatible with BETA 153

10/31/2012 5:46 PM Kee Kee
- Make compatible with BETA 182 + Linux version 126

08/14/2013 12:07 PM Kee Kee
- Compatible with From Beta 183-203

02/27/2014 5:20 PM Kee Kee
- compatible with Beta 217 & 218 version

4/6/2016 3:32 PM Andy
- Change sqlite3.sh and sqlite3.exe to sqlite3.
- Change separator from ~~ to ~.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
define('TERMINAL',1);
include("include/common.php");
require("include/sqlite.php");
set_time_limit(0);
ini_set("memory_limit", "128M");

$db_path = 'db';

if (!is_dir($db_path)) mkdir($db_path,0777);
if (!is_dir($db_path."/ui")) mkdir($db_path."/ui",0777);

if (isset($config['use_mysqli']) || (version_compare(PHP_VERSION, '7.0.0', '>=')))
{
	$fetchfunc = 'mysqli_fetch_row';
}
else
{
	$fetchfunc = 'mysql_fetch_row';
}

$arg = $_SERVER['argv'];
$bcode = trim($arg[1]);

if($bcode)	$branch_filter = " and b.code=".ms($bcode);

$con->sql_query("select b.code, cs.branch_id, cs.network_name, cs.id as counter_id from counter_settings cs left join branch b on cs.branch_id = b.id where b.active=1 and cs.active=1 $branch_filter") or die(mysql_error());
while ($r=$con->sql_fetchrow())
{
	$code[$r['branch_id']] = strtolower($r['code']);
	$branch[$r['branch_id']][$r['counter_id']] = 1;
}

if(!$code){
	die("Invalid Branch\n");
}

foreach ($branch as $branch_id =>$b)
{
	print "Generating file for ".$code[$branch_id]."\n";
	
	foreach(glob($db_path."/*") as $f)
	{
		if (preg_match("/(\.sql3|pos_login_bg|pos_main_bg|pos_main_banner|\.table)/", $f)) unlink($f);
	}

	init_local_db();

	sync_settings();
	sync_img();
	sync_users();
	sync_promotion();
	sync_redemption();
	sync_sku_items();
	sync_category();
	sync_sku();
	sync_sku_group();
	sync_return_policy();
	sync_membership();
	sync_sales_agent();
	sync_sku_items_batch_price_price_change();
	sync_return_policy();
	sync_return_policy_setup();
	sync_coupon();
	sync_broadcast_message();
	sync_broadcast_trade_offer();
	
	passthru('cd '.dirname(__FILE__).'/db; tar cvfz '.escapeshellcmd($code[$branch_id]).'.tgz ui slides *.sql3');
	
	foreach(glob($db_path."/*") as $f)
	{
		if (preg_match("/(\.sql3|pos_login_bg|pos_main_bg|pos_main_banner|\.table)/", $f)) unlink($f);
	}

	foreach(glob($db_path."/ui/*") as $f)
	{
		unlink($f);
	}	
	
	foreach(glob($db_path."/slides/*.png") as $f)
	{
		unlink($f);
	}
}

function sync_membership()
{
	global $con, $fetchfunc, $db_path;
	
	$con->sql_query("select count(*) as count, max(nric) as nric from membership where card_no!='' or card_no is not null");
	$rcount = $con->sql_fetchrow();
	$no_of_row = $rcount['count'];
	print "Sync membership \n";
	print "No of Record: $no_of_row\n";

	$l = 20000;
	for($i=0;$i<$no_of_row;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		$rs = $con->sql_query("select nric,name,card_no,points,next_expiry_date,blocked_date,terminated_date,member_type,points_update,parent_nric,quota_balance,quota_last_update,staff_type
			from membership where card_no!='' or card_no is not null order by nric limit $i, $l") or die(mysql_error());

		$fout = fopen("$db_path/membership.table","wt");
		while($r = $fetchfunc($rs))
		{
		  
			if(trim($r['7'])==""){
				$r['7'] = "member1";
			}
			
		    $lastid = $r[0];
		    foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
		    fputs($fout,join("~",$r)."\n");
		}
		fclose($fout);

		passthru('sqlite3 -separator "~" db/membership.sql3 ".import '.$db_path.'/membership.table membership"');
		unlink("$db_path/membership.table");
		$con->sql_freeresult();
	}

	$con->sql_freeresult();

	//indexing
	print " - Add indexing\n";
	$sq3 = 	new sqlite_db('db/membership.sql3');
	$sq3->sql_query("create index if not exists card_no_idx on membership (card_no)");
	$sq3->sql_query("create index if not exists nric_idx on membership (nric)");
	$sq3->sql_query("create index if not exists member_type_idx on membership (member_type)");
	$sq3->sql_query("create index if not exists staff_type_idx on membership (staff_type)");
	$sq3->sql_close();
}

function sync_sku_items()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	
	$con->sql_query("select count(*) as count, max(id) as id from sku_items");
	$rcount = $con->sql_fetchrow();
	$no_of_row2 = $rcount['count'];
	
	print "Sync sku_items from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_row2\n";
	
	$l = 20000;
	for($i=0;$i<$no_of_row2;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		
		// the select sequence MUST match the table setup
		$rs = $con->sql_query("select si.id,
							si.sku_id,
							si.sku_item_code,
							si.packing_uom_id,
							si.mcode,
							si.link_code,
							si.receipt_description,
							if(sp.price is null,si.selling_price, sp.price) as selling_price,
							si.active,
							si.open_price,
							si.decimal_qty,
							uom.code as uom_code,
							if(uom.fraction is null,1,uom.fraction) as uom_fraction,
							if (trade_discount_code is null, default_trade_discount_code, trade_discount_code) as trade_discount_code,
							if (sku.trade_discount_type = 1,
							   (select rate from brand_commission bc where bc.department_id=category.department_id and bc.brand_id=sku.brand_id and bc.skutype_code=(if (trade_discount_code is null, default_trade_discount_code, trade_discount_code)) and bc.branch_id=$branch_id),
							   (if (sku.trade_discount_type = 2,
							       (select rate from vendor_commission vc where vc.department_id=category.department_id and vc.vendor_id=sku.vendor_id and vc.skutype_code=(if (trade_discount_code is null, default_trade_discount_code, trade_discount_code)) and vc.branch_id=$branch_id),
							        null)
							   ))  as brand_vendor_rate,
							sbi.batch_no,
							sbi.expired_date as batch_expired_date,
							si.category_disc_by_branch_inherit,
							si.category_point_by_branch_inherit,			
							si.cat_disc_inherit,
							si.category_point_inherit,
							si.is_parent,
							if(sp.last_update is null,si.lastupdate, sp.last_update) as priceChange_date,
							si.scale_type,
							si.bom_type,						
							'' as bom_detail,
							si.artno,
							si.additional_description,
							si.additional_description_print_at_counter,
							sku.is_bom
			from sku_items si
			left join sku on si.sku_id = sku.id
			left join category on sku.category_id=category.id
			left join sku_items_price sp on sp.sku_item_id = si.id and sp.branch_id = $branch_id
			left join uom on si.packing_uom_id = uom.id
			left join sku_batch_items sbi on si.id = sbi.sku_item_id and sbi.branch_id = $branch_id
			order by si.id limit $i,$l") or die(mysql_error());
		$fout = fopen("$db_path/sku_items.table","wt");
		while($r = $fetchfunc($rs))
		{
			if($is_bom && $r[29]){		
				
				$rs1 = $con->sql_query("select sku_item_id,selling_price,qty from bom_items where bom_id=".ms($r['0']));
				$j=0;
				if($con->sql_numrows($rs1)>0)
				{
					while($r2 = $fetchfunc($rs1))
					{
						$bom[$j]['sku_item_id'] = $r2['0'];
						$bom[$j]['selling_price'] = $r2['1'];
						$bom[$j]['qty'] = $r2['2'];
						$j++;
					}		
					$r['25'] = serialize($bom);
					unset($bom);
				}
				$con->sql_freeresult();
			}
		    $lastid = $r[0];
			unset($r[29]);
		    foreach($r as $k=>$v) { 
				if(preg_match("/\\n/i",$v)==true){
					$v = preg_replace("/\\n/i","",$v);
				}				
				
				if(preg_match("/\\n\\r/i",$v)==true){
					$v = preg_replace("/\\n\\r/i","",$v);
				}
				$r[$k] = str_replace("~","",$v); 			
			}

			fputs($fout,join("~",$r)."\n");
			unset($r,$k,$v);
		}
		fclose($fout);
	
		passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/sku_items.table sku_items"');
		unlink("$db_path/sku_items.table");
	
	
		$con->sql_freeresult($rs);

	}
		
	$con->sql_freeresult();
		
	// mprice -------------------------
	print "----- ".memory_get_usage()."\n";
	$con->sql_query("select count(*) as count from sku_items_mprice where branch_id=$branch_id");
	$rcount = $con->sql_fetchrow();
	$no_of_row = $rcount['count'];
	print "Sync mprice \n";
	print "No of Record: $no_of_row\n";
	
	$rs=$con->sql_query("select sku_item_id, type, price, trade_discount_code, last_update from sku_items_mprice where branch_id=$branch_id");
	$fout = fopen("$db_path/mprice.table","wt");
	while($r = $fetchfunc($rs))
	{
	    fputs($fout,join("~",$r)."\n");
	}
	fclose($fout);
	passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/mprice.table sku_items_mprice"');
	unlink("$db_path/mprice.table");	
	$con->sql_freeresult($rs);
	
	
	// qprice -------------------------
	print "----- ".memory_get_usage()."\n";
	$con->sql_query("select count(*) as count from sku_items_qprice where branch_id=$branch_id");
	$rcount = $con->sql_fetchrow();
	$no_of_row = $rcount['count'];
	print "Sync qprice \n";
	print "No of Record: $no_of_row\n";
	
	$rs=$con->sql_query("select sku_item_id, min_qty, price, last_update from sku_items_qprice where branch_id=$branch_id");
	$fout = fopen("$db_path/qprice.table","wt");
	while($r = $fetchfunc($rs))
	{
	    fputs($fout,join("~",$r)."\n");
	}
	fclose($fout);
	passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/qprice.table sku_items_qprice"');
	unlink("$db_path/qprice.table");
	$con->sql_freeresult($rs);
	
	print "----- ".memory_get_usage()."\n";
	$con->sql_query("select count(*) as count from sku_items_mqprice where branch_id=$branch_id");
	$rcount = $con->sql_fetchrow();
	$no_of_row = $rcount['count'];
	print "Sync mqprice \n";
	print "No of Record: $no_of_row\n";
	
	$rs=$con->sql_query("select sku_item_id, min_qty, price, last_update,type from sku_items_mqprice where branch_id=$branch_id and price>0");
	$fout = fopen("$db_path/mqprice.table","wt");
	while($r = $fetchfunc($rs))
	{
	    fputs($fout,join("~",$r)."\n");
	}
	fclose($fout);
	passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/mqprice.table sku_items_mqprice"');
	unlink("$db_path/qprice.table");
	$con->sql_freeresult($rs);
	
	//indexing
	$sq3 = 	new sqlite_db("db/sku_items.sql3");
	print " - Add indexing\n";
	
	$sq3->sql_query("create index if not exists sku_id_idx on sku_items (sku_id)");
	$sq3->sql_query("create index if not exists sku_item_code_idx on sku_items (sku_item_code)");
	$sq3->sql_query("create index if not exists mcode_idx on sku_items (mcode)");
	$sq3->sql_query("create index if not exists link_code_idx on sku_items (link_code)");
	//indexing sku items mprice table
	$sq3->sql_query("create index if not exists type_idx on sku_items_mprice (type)");
	$sq3->sql_close();
}

function sync_sku_items_batch_price_price_change()
{ 
  global $con, $counter_id, $branch_id, $db_path, $fetchfunc;

	$row_count = 0;
	$con->sql_query("select * from sku_items_future_price where approved=1");
	if($con->sql_numrows()>0)
	{
		while($r = $con->sql_fetchrow())
		{
			$branch = unserialize($r['effective_branches']);
			
			if($r['date']=="0000-00-00" && isset($branch[$branch_id])&& (!isset($branch[$branch_id]['cron_status']) || $branch[$branch_id]['cron_status']==0))
			{
				$batch['id'][] = $r['id'];
				$batch['branch_id'][] = $r['branch_id'];
				$batch_price[$row_count]['id'] = ($r['id']*1000+$r['branch_id']);
				$batch_price[$row_count]['date'] = $branch[$branch_id]['date'];
				$batch_price[$row_count]['time'] = sprintf("%02d:%02d:00",$branch[$branch_id]['hour'],$branch[$branch_id]['minute']);
				$batch_price[$row_count]['active'] = $r["active"];
				$batch_price[$row_count]['status'] = $r["status"];
				$batch_price[$row_count]['approved'] = $r["approved"];
				$batch_price[$row_count]['cron_status'] = ((isset($branch[$branch_id]['cron_status']) && $branch[$branch_id]['cron_status']==1)?1:0);
				$row_count++;			
			}
			elseif($r['date']!="0000-00-00" && isset($branch[$branch_id]) && $r['cron_status']==0)
			{	
				$batch['id'][] = $r['id'];
				$batch['branch_id'][] = $r['branch_id'];
				$batch_price[$row_count]['id'] = ($r['id']*1000+$r['branch_id']);
				$batch_price[$row_count]['date'] = $r['date'];
				$batch_price[$row_count]['time'] = sprintf("%02d:%02d:00",$r['hour'],$r['minute']);
				$batch_price[$row_count]['active'] = $r["active"];
				$batch_price[$row_count]['status'] = $r["status"];
				$batch_price[$row_count]['approved'] = $r["approved"];
				$batch_price[$row_count]['cron_status'] = $r["cron_status"];
				$row_count++;
			}
			else{
				continue;
			}
		}
	}
	
	$no_of_record =  $row_count;
	print "Sync Batch Price Change from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_record \n";
		
	$fout = fopen("$db_path/sku_items_future_price.table","wt");
	$start = microtime(true);
	foreach($batch_price as $r)
	{
		foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
	    fputs($fout,join("~",$r)."\n");
	}
	//printf("Update trigger done... %.3fs\n",microtime(true)-$start);
	fclose($fout);
	passthru('sqlite3 -separator "~" db/batch_price_change.sql3 ".import '.$db_path.'/sku_items_future_price.table sku_items_future_price"');
	unlink("sku_items_future_price.table");
  
	$con->sql_query("select count(*) as count
	from sku_items_future_price_items
	left join sku_items_future_price on sku_items_future_price_items.branch_id = sku_items_future_price.branch_id and sku_items_future_price_items.fp_id = sku_items_future_price.id 
	where sku_items_future_price.id in ('".join("','",$batch['id'])."') and sku_items_future_price.branch_id in ('".join("','",$batch['branch_id'])."')");
	$rcount = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	$no_of_row = $rcount['count'];
	print "Sync SKU items batch price change items \n";
	print "No of Record: $no_of_row\n";
	
	$rs=$con->sql_query("select sku_items_future_price_items.id,(sku_items_future_price.id*1000+sku_items_future_price.branch_id) as fp_id,sku_item_id,type, min_qty, future_selling_price from sku_items_future_price_items
	left join sku_items_future_price on sku_items_future_price_items.branch_id = sku_items_future_price.branch_id and sku_items_future_price_items.fp_id = sku_items_future_price.id 
	where sku_items_future_price.id in ('".join("','",$batch['id'])."') and sku_items_future_price.branch_id in ('".join("','",$batch['branch_id'])."')");
	$fout = fopen("$db_path/sku_items_batch_price_change_items.table","wt");
	while($r = $fetchfunc($rs))
	{
		foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
	  fputs($fout,join("~",$r)."\n");
	}
	fclose($fout);
	passthru('sqlite3 -separator "~" db/batch_price_change.sql3 ".import '.$db_path.'/sku_items_batch_price_change_items.table sku_items_future_price_items"');
	unlink("$db_path/sku_items_batch_price_change_items.table");
	$con->sql_freeresult($rs);

}

function sync_category()
{
	global $con, $counter_id, $branch_id, $db_path;
	
	print "sync 'category'...\n";
	//get category disc and point
	$c = $con->sql_query("select id,category_disc,category_point, tree_str,member_category_disc,description,category_disc_by_branch,category_point_by_branch,category_staff_disc_by_branch,active from category");
	
	if($con->sql_numrows()<=0)
	{
		print " - No new category\n";
		return;
	}
	$total_cat_count = $con->sql_numrows();

	$sq3 = 	new sqlite_db('db/sku_items.sql3');
	print ' - '.$con->sql_numrows()." category records retrieved\n";
	$n=0;
	
	$fout = fopen("$db_path/category.table","wt");
	while($r=$con->sql_fetchrow($c))
	{

		$t = $r['tree_str'];
		$pattern = '/\((\d+)\)/';
		preg_match_all($pattern, $t, $level);

	  	array_shift($level[1]);
	  	$level[1][] = $r['id'];
		
		for($i = 1; $i<=10; $i++){	// reset all level to zero
			$p = "d{$i}";
			$$p = 0;
			
			if(isset($level[1][$i-1])) $$p = intval($level[1][$i-1]);	// set 
		}
	
		$upd = array($r['id'], $r['category_disc'], $r['category_point'],mi($d1), mi($d2), mi($d3), mi($d4),mi($d5),mi($d6),mi($d7),mi($d8),mi($d9),mi($d10),mf($r['member_category_disc']), $r['description'],$r['category_disc_by_branch'],$r['category_point_by_branch'],$r['category_staff_disc_by_branch'],$r['active']);
		foreach($upd as $k=>$v) { $upd[$k] = str_replace("~","",$v); }
		fputs($fout,join("~",$upd)."\n");
			
		$n++;
		print "\r$n / $total_cat_count.....";
		//if ($n%1000==0) print ".";
	}
	print "\n";
	$sq3->sql_close();
	fclose($fout);
	passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/category.table category"');
	unlink("$db_path/qprice.table");
}

function sync_sku()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	
	$con->sql_query("select count(*) as count, max(id) as id from sku");
	$rcount = $con->sql_fetchrow();
	$no_of_row2 = $rcount['count'];
	
	print "Sync sku from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_row2\n";
	
	$l = 20000;
	for($i=0;$i<$no_of_row2;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		
		// the select sequence MUST match the table setup
		$rs = $con->sql_query("select 
			id, 
			category_id,
			brand_id,
			sku_type,
			have_sn,
			scale_type,
			vendor_id
			from sku order by id limit $i, $l") or die(mysql_error());
		$fout = fopen("$db_path/sku.table","wt");
		while($r = $fetchfunc($rs))
		{
		    $lastid = $r[0];
		    foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
		    fputs($fout,join("~",$r)."\n");
		}
		fclose($fout);
	
		passthru('sqlite3 -separator "~" db/sku_items.sql3 ".import '.$db_path.'/sku.table sku"');
		unlink("$db_path/sku.table");
	
	
		$con->sql_freeresult($rs);

	}
		
	$con->sql_freeresult();

}

function sync_sku_group()
{
 	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;

  $con->sql_query("select count(*) as count from sku_group_item where branch_id=".mi($branch_id));
	$rcount = $con->sql_fetchrow();
	$no_of_row2 = $rcount['count'];
  	
	print "sync 'SKU Group' from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_row2\n";
	
	//$sq3 = new sqlite_db("db/sku_group_item.sql3");
	
  $l = 20000;
	for($i=0;$i<$no_of_row2;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		$rs = $con->sql_query("select sku_group_id,branch_id,sku_item_code from sku_group_item where branch_id=".mi($branch_id)." limit $i, $l");
  	$fout = fopen("$db_path/sku_group_item.table","wt");
  	while($r = $fetchfunc($rs))
  	{
  	    foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
  	    fputs($fout,join("~",$r)."\n");
  	}
		fclose($fout);
	
		passthru('sqlite3 -separator "~" db/sku_group_item.sql3 ".import '.$db_path.'/sku_group_item.table sku_group_item"');
		unlink("$db_path/sku_group_item.table");
		$con->sql_freeresult($rs);	
	}
	$con->sql_freeresult();
}

function sync_redemption()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	
	print "sync 'Redemption' from ".$code[$branch_id]."\n";
	
	$sq3 = 	new sqlite_db('db/promotion.sql3');
	
	
	$rs = $con->sql_query("select * from membership_redemption_sku where branch_id = $branch_id ") or die(mysql());
	if ($con->sql_numrows()<=0)
	{
		print " - No Redemption\n";
		return;
	}
	print 'No of Record: '.$con->sql_numrows();
	$n=0;
	$fields = array('id','sku_item_id','point','cash','receipt_amount','timestamp','active');
	$fout = fopen("$db_path/membership_redemption_sku.table","wt");
	while($r=$con->sql_fetchrow($rs))
	{
		$upd = array($r['id'], $r['sku_item_id'], $r['point'], $r['cash'], $r['receipt_amount'], $r['timestamp'], $r['active']);
		foreach($upd as $k=>$v) { $upd[$k] = str_replace("~","",$v); }
		fputs($fout,join("~",$upd)."\n");
		// update the promotion
		//$sq3->sql_query("replace into membership_redemption_sku ".mysql_insert_by_field($r, $fields));
		
		$n++;
		if ($n%1000==0) print ".";
		
	}
	
	passthru('sqlite3 -separator "~" db/promotion.sql3 ".import '.$db_path.'/membership_redemption_sku.table membership_redemption_sku"');
	unlink("$db_path/membership_redemption_sku.table");
	$con->sql_freeresult();
	$sq3->sql_close();
	print " - `Redemption` table updated\n";	
}

function sync_img()
{
	global $con,$branch_id,$config;	
	print "sync 'pos img'...\n";
	$n = 0;
	$is_mysql_mode = true;
	$con->sql_query("select * from pos_settings where branch_id = $branch_id and setting_name = ".ms('pos_use_own_image')." and setting_value=1") or _die_(mysql_error());
	$use_own_branch = false;
	if($con->sql_numrows()>0)
	{
		$use_own_branch = true;
		$folder = "ui/pos_settings_display/pos";
		$imgs = array('branch_pos_login_bg','branch_pos_main_bg','branch_pos_main_banner','branch_pos_receipt_image');
	}else{
		$folder = "ui";
		$imgs = array('pos_login_bg','pos_main_bg','pos_main_banner','pos_receipt_image');
	}
	
	foreach($imgs as $img)
	{
		//if (!isset($config[$img])) { continue; } // skip unused image (not set in config)
		$con->sql_query("select setting_value as img_size from pos_settings where branch_id = $branch_id and setting_name = ".ms($img."_size")) or _die_(mysql_error());
		$image = $con->sql_fetchrow();
		$img1 = str_replace("branch_","",$img);			
		if (!file_exists($config[$img1]) || !(filesize($config[$img1]) == $image['img_size']))
		{			
			if($use_own_branch)
			{
				$url = "/".$folder."/$branch_id/$img";				
			}
			else{
				$url = "$folder/$img";			
			}
			
			print "downloading $url\n";
			$file = file_get_contents($url);
			if (preg_match('/html/',$file)) { 
				print "bad file\n"; 
				unset($config[$img1]);
				unlink("ui/$img.png"); 
				continue; 
			}
		 
		 if(!isset($config[$img1])) { 
				//$img = str_replace("/^branch_/","",$img);
				$config[$img1] = "ui/$img1.png"; 
			}
			file_put_contents("db/".$config[$img1],$file);
			$n++;
		}
	}
	
	if($use_own_branch)
	{
		$con->sql_query("select setting_value as img_list from pos_settings where setting_name=".ms("branch_pos_dual_screen_image")." and branch_id=".ms($branch_id));
		if($con->sql_numrows()<=0)
		{
			$con->sql_query("select setting_value as img_list from pos_settings where setting_name=".ms("pos_dual_screen_image")." and branch_id=".ms($branch_id));
			if($con->sql_numrows()>0)
			{
				$r = $con->sql_fetchrow();
				$img_list = unserialize($r['img_list']);
				$folder = "ui/dual_screen_images";
			}
		}else{
			$r = $con->sql_fetchrow();
			$img_list = unserialize($r['img_list']);
			$folder = "ui/pos_settings_display/pos/$branch_id/dual_screen_images";
		}
	}else{
		$con->sql_query("select setting_value as img_list from pos_settings where setting_name=".ms("pos_dual_screen_image")." and branch_id=".ms($branch_id));
		if($con->sql_numrows()>0)
		{
			$r = $con->sql_fetchrow();
			$img_list = unserialize($r['img_list']);
			$folder = "ui/dual_screen_images";
		}
	}
	
	$local_img = array();
	if(is_dir("db/slides")){
		foreach(glob("db/slides/*.*") as $ig)
		{		
			$ig =  str_replace("db/slides/","",$ig);
			if($ig == "Thumbs.db"){
				continue;
			}
			
			if(!in_array($ig,$img_list))
			{		
				unlink("db/slides/".$ig);
				continue;
			}
			$local_img[$ig] = 1;
		}		
	}else{		
		mkdir("db/slides","0755");
	}
	
	if(isset($img_list)){
		print "Sync Slide Images... ...\n";
		foreach($img_list as $i)
		{
			if(!isset($local_img[$i])){
				if($use_own_branch)
				{
					$url = $folder."/$i";				
				}
				else{
					$url = "$folder/$i";			
				}
				
				print "downloading image from $url\n";
				$file = file_get_contents($url);
				if (preg_match('/html/',$file)) { 
					print "bad slides image file\n"; 						
					continue; 
				}
				
				file_put_contents("db/slides/".$i,$file);
				$n++;
			}			
		}
		
	}
	
	if ($n>0) 
		print " - $n images downloaded\n";
	else
		print " - No new image\n";
	
}

function sync_promotion()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	
	print "sync 'promotion'...\n";
	// get new promotion
	// because same id can appear in 2 branch, we shift promo id by 1000 and append branch behind as the promo-id

	$con->sql_query("select count(*) as count from promotion where approved=1 and promo_branch_id like '%\"".strtoupper($code[$branch_id])."\"%' and date_to >= ".ms(date('Y-m-d'))) or _die_(mysql_error());
	$rcount = $con->sql_fetchrow();
	$no_of_row = $rcount['count'];	

	if ($no_of_row<=0)
	{
		print " - No new promotion items\n";
		return;
	}
	else
	{
		print "Sync promotion from ".$code[$branch_id]."\n";
		print "No of Record: $no_of_row\n";
	}
	
	$con->sql_freeresult();
	
	$l = 20000;
	for($i=0;$i<$no_of_row;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		
		$c = $con->sql_query("select p.id as promo_id, p.branch_id, (p.id*1000+p.branch_id) as id,p.title,p.date_from,p.date_to,p.time_from,p.time_to,p.approved,p.status,p.promo_branch_id,p.active, print_title_in_receipt,consignment_bearing, promo_type,category_point_inherit,category_point_inherit_data 
		from promotion p
			where p.approved=1 and p.promo_branch_id like '%\"".strtoupper($code[$branch_id])."\"%' and p.date_to >= ".ms(date('Y-m-d'))." limit $i, $l") or _die_(mysql_error());

		$fout = fopen("$db_path/promotion.table","wt");
		while($r = $fetchfunc($c))
		{
			$org_promo_id = $r[0];
			unset($r[0]);
			$org_branch_id = $r[1];
			unset($r[1]);

		    foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
		    fputs($fout,join("~",$r)."\n");
		    //$r[0] = $org_promo_id;
		    //$r[1] = $org_branch_id;
		    
        	$c2 = $con->sql_query("select id,
			(promo_id*1000+branch_id) as promo_id,
			sku_item_id,
			brand_id,
			member_disc_p,
			member_disc_a,
			non_member_disc_p,
			non_member_disc_a,
			member_min_item,
			non_member_min_item,
			category_id,
			member_qty_from, 
			member_qty_to,
			non_member_qty_from,
			non_member_qty_to, 
			1, 
			control_type, 
			member_limit,
			non_member_prof_p, 
			non_member_net_bear_p, 
			non_member_use_net, 
			non_member_trade_code, 
			member_prof_p, 
			member_net_bear_p, 
			member_use_net, 
			member_trade_code, 
			block_normal, 
			member_block_normal,
			allowed_member_type			
			from promotion_items where branch_id = $org_branch_id and promo_id = $org_promo_id") or _die_(mysql_error());

			$fout2 = fopen("$db_path/promotion_items.table","wt");
			while($r2 = $fetchfunc($c2))
			{
				// column 20 = non_member_use_net
				if ($r2[20] == 'yes')
			        $r2[20] = 1;
				else
				    $r2[20] = 0;
	
				// column 24 = member_use_net
			    if ($r2[24] == 'yes')
			        $r2[24] = 1;
				else
				    $r2[24] = 0;
			    
			    foreach($r2 as $k2=>$v2) { $r2[$k2] = str_replace("~","",$v2); }
			    fputs($fout2,join("~",$r2)."\n");
			}
			fclose($fout2);
			passthru('sqlite3 -separator "~" db/promotion.sql3 ".import '.$db_path.'/promotion_items.table promotion_items"');
			unlink("$db_path/promotion_items.table");
			
			$c3 = $con->sql_query("Select branch_id,
			id,
			(promo_id*1000+branch_id) as promo_id,
			group_id,
			sequence_num,
			user_id,
			disc_target_type,
			disc_target_value,
			disc_target_info,
			disc_condition,
			disc_by_type,
			disc_by_value,
			disc_limit,
			receipt_limit,
			disc_prefer_type,
			for_member,
			for_non_member,
			follow_sequence,
			item_remark,
			qty_from,
			receipt_description,
			disc_target_sku_type,
			disc_target_price_type,
			disc_target_price_range_from,
			disc_target_price_range_to,
			disc_by_qty,
			loop_limit,
			control_type,
			1,
			item_category_point_inherit_data,
			for_member_type,
			prompt_available	
			from promotion_mix_n_match_items
			Where branch_id=$org_branch_id and promo_id=$org_promo_id") or _die_(mysql_error());
	
			$fout3 = fopen("$db_path/promotion_mix_n_match_items.table","wt");
			while($r3 = $fetchfunc($c3))
			{
				foreach($r3 as $k3=>$v3) { $r3[$k3] = str_replace("~","",$v3); }
			    fputs($fout3,join("~",$r3)."\n");
			}
			fclose($fout3);
			passthru('sqlite3 -separator "~" db/promotion.sql3 ".import '.$db_path.'/promotion_mix_n_match_items.table promotion_mix_n_match_items"');
			unlink("$db_path/promotion_mix_n_match_items.table");
		}
		fclose($fout);
		passthru('sqlite3 -separator "~" db/promotion.sql3 ".import '.$db_path.'/promotion.table promotion"');
		unlink("$db_path/promotion.table");
		$con->sql_freeresult($c);
	}
}

function sync_settings()
{
	global $con, $counter_id, $branch_id,$hostname;
	print "sync 'settings' ...\n";
	
	$sq3 = 	new sqlite_db('db/settings.sql3');
	$sq3->sql_begin_transaction();
	
		//get latest pos settings
	$c = $con->sql_query("select setting_name,setting_value,last_update from pos_settings where branch_id = $branch_id") or _die_(mysql_error());
	
	if($con->sql_numrows($c)<=0)
	{
		print " - No new pos settings\n";
	}
	else
	{			
		$new_settings = array();
		while($r=$con->sql_fetchrow($c))
		{
			if(!isset($settings_value[$r['setting_name']]) || $settings_value[$r['setting_name']]==0 || strtotime($settings_value[$r['setting_name']]) < strtotime($r['last_update']))
			{
				$new_settings[] = array('setting_name'=>$r['setting_name'],'setting_value'=>$r['setting_value'],'last_update'=>$r['last_update']);
				// update the settings
				$new_setting_name[$r['setting_name']] = ms($r['setting_name']);
			}
		}
		
		print " - ".count($new_settings)." pos settings records retrieved\n";
		
		if($new_settings)
		{
			$sq3->sql_query("delete from pos_settings where setting_name in (".join(",",$new_setting_name).")");
			foreach($new_settings as $r1)
			{		
				$sq3->sql_query("replace into pos_settings ".mysql_insert_by_field($r1, explode(",","setting_name,setting_value,last_update")));
				$n++;
				if ($n%1000==0) print ".";
			}
		}
		$con->sql_freeresult($c);
		
		//Sync Down server's config	
		$server_config = false;
		$diff_time = 0;
		$sq3->sql_query("select * from pos_settings where setting_name = ".ms("server_config"));
		if($sq3->sql_numrows()>0)
		{
			$ret = $sq3->sql_fetchrow();	
			$diff_time = (time() - strtotime($ret['last_update']))/(60*60);
		}

		if($diff_time>=1 || $diff_time==0)
		{		
			$file = "counter_config.txt";
			$server_config = file_get_contents($file);
			if (unserialize($server_config)) 
			{ 
				$sq3->sql_query("delete from pos_settings where setting_name = ".ms("server_config"));
				$r['setting_name'] = "server_config";
				$r['setting_value'] = $server_config;
				$r['last_update'] = date("Y-m-d H:i:s");
				$sq3->sql_query("replace into pos_settings ".mysql_insert_by_field($r));
				$sq3->sql_freeresult();
			}
		}
		unset($settings_value,$new_settings,$r1);
	}
		
	//get latest branches
	$c = $con->sql_query("select id,code,receipt_header,receipt_footer,description from branch ") or _die_(mysql_error());
	
	if($con->sql_numrows()<=0)
	{
		print " - No new branch\n";
	}
	else
	{
	
		print " - ".$con->sql_numrows()." branch records retrieved\n";
		while($r=$con->sql_fetchrow($c))
		{
			// update the branch
			$sq3->sql_query("replace into branch ".mysql_insert_by_field($r, explode(",","id,code,receipt_header,receipt_footer,description")));
			$n++;
			if ($n%1000==0) print ".";
		}
		$con->sql_freeresult($c);
	}


	//get latest settings
	$c = $con->sql_query("select id,network_name,pos_settings,active from counter_settings
		where branch_id = $branch_id ") or _die_(mysql_error());
	
	if($con->sql_numrows($c)<=0)
	{
		print " - No new counter\n";
	}
	else
	{
		print " - ".$con->sql_numrows($c)." counter records retrieved\n";
		while($r=$con->sql_fetchrow($c))
		{
			// update the settings
			$sq3->sql_query("replace into counter_settings ".mysql_insert_by_field($r, explode(",","id,network_name,pos_settings,active")));
			$n++;
			if ($n%1000==0) print ".";
		}
		$con->sql_freeresult($c);
	}

	$sq3->sql_commit();
	$sq3->sql_close();
}

function sync_users()
{
	global $con, $counter_id, $branch_id;
	
	print "sync 'user'...\n";
	// get new SKU items or new price
	$c = $con->sql_query("select id,u,l,p,active, fullname,barcode,discount_limit,item_disc_only_allow_percent from user left join user_privilege on user_id = user.id and privilege_code='POS_LOGIN' and branch_id=$branch_id
			where privilege_code is not null and not template") or _die_(mysql_error());

	if ($con->sql_numrows()<=0)
	{
		print " - No new user\n";
		return;
	}

	$sq3 = 	new sqlite_db('db/users.sql3');
	$sq3->sql_begin_transaction();
	print ' - '.$con->sql_numrows()." records retrieved\n";
	$n=0;
	while($r=$con->sql_fetchrow($c))
	{
		// update the user
		$sq3->sql_query("replace into user ".mysql_insert_by_field($r, explode(",","id,u,l,p,active,fullname,barcode,discount_limit,item_disc_only_allow_percent")));
		
		// delete and reinsert all privilege
		$con->sql_query("select privilege_code from user_privilege where branch_id = $branch_id and user_id = $r[id] and allowed=1 and privilege_code like 'POS%'") or _die_(mysql_error());
		print " - $r[u] have ".$con->sql_numrows()." privilege\n";
		$sq3->sql_query("delete from user_privilege where user_id = $r[id]");
		while($pv = $con->sql_fetchrow())
		{
			$sq3->sql_query("insert into user_privilege (user_id,privilege_code) values ($r[id], ".ms($pv['privilege_code']).")");
		}
		
		$n++;
		if ($n%1000==0) print ".";
	}


	//print " - Vacumming db...\n";
	//$sq3->sql_query("vacuum");
	$sq3->sql_commit();
	$sq3->sql_close();
	print " - `users` table updated\n";
}

function sync_sales_agent()
{
	global $con, $counter_id, $branch_id;
	
	print "sync 'sales agent'...\n";
	// get new SKU items or new price
	$c = $con->sql_query("select sa.id,sa.code,sa.active from sa") or _die_(mysql_error());

	if ($con->sql_numrows()<=0)
	{
		print " - No new sales agent\n";
		return;
	}

	$sq3 = 	new sqlite_db('db/sales_agent.sql3');
	$sq3->sql_begin_transaction();
	print ' - '.$con->sql_numrows()." records retrieved\n";
	$n=0;
	while($r=$con->sql_fetchrow($c))
	{
		// update the sales_agent
		$sq3->sql_query("replace into sales_agent ".mysql_insert_by_field($r, explode(",","id,code,active")));
				
		$n++;
		if ($n%1000==0) print ".";
	}


	//print " - Vacumming db...\n";
	//$sq3->sql_query("vacuum");
	$sq3->sql_commit();
	$sq3->sql_close();
	print " - `users` table updated\n";
}

function sync_return_policy()
{
  global $con, $code, $config, $branch_id, $fetchfunc, $db_path;

  $con->sql_query("select count(*) as count from return_policy Where active=1");
	$rcount = $con->sql_fetchrow();
	$no_of_row2 = $rcount['count'];
  	
	print "sync 'Return Policy' from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_row2\n";
	
	//$sq3 = new sqlite_db("db/return_policy.sql3");
	
  $l = 20000;
	for($i=0;$i<$no_of_row2;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		$rs = $con->sql_query("Select id,branch_id,title,duration_condition,durations,expiry_durations,expiry_type,charges_condition,charges,receipt_remark,max_charges,active,last_update from return_policy Where active=1 limit $i, $l");
  	$fout = fopen("$db_path/return_policy.table","wt");
  	while($r = $fetchfunc($rs))
  	{
  	    foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
  	    fputs($fout,join("~",$r)."\n");
  	}
		fclose($fout);
	
		passthru('sqlite3 -separator "~" db/return_policy.sql3 ".import '.$db_path.'/return_policy.table return_policy"');
		unlink("$db_path/return_policy.table");
		$con->sql_freeresult($rs);	
	}
	$con->sql_freeresult();
}

function sync_return_policy_setup()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;

	$con->sql_query("select count(*) as count from return_policy_setup Where active=1 and status = 1 and branch_id=".mi($branch_id));
	$rcount = $con->sql_fetchrow();
	$no_of_row2 = $rcount['count'];
  	
	print "sync 'Return Policy Setup' from ".$code[$branch_id]."\n";
	print "No of Record: $no_of_row2\n";
	
	//$sq3 = new sqlite_db("db/return_policy.sql3");
	
	$l = 20000;
	for($i=0;$i<$no_of_row2;$i=$i+$l)
	{
		print "$i ".memory_get_usage()."\n";
		$rs = $con->sql_query("select id,branch_id,ref_id,ref_branch_id,type,setup,active,status,added,last_update from return_policy_setup where active=1 and status=1 and branch_id=".mi($branch_id)." limit $i, $l");
		$fout = fopen("$db_path/return_policy_setup.table","wt");
		while($r = $fetchfunc($rs))
		{
			foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
			fputs($fout,join("~",$r)."\n");
		}
		fclose($fout);
	
		passthru('sqlite3 -separator "~" db/return_policy.sql3 ".import '.$db_path.'/return_policy_setup.table return_policy_setup"');
		unlink("$db_path/return_policy_setup.table");
		$con->sql_freeresult($rs);	
	}
	$con->sql_freeresult();
}

function init_local_db()
{
	print "Initialize local db...\n";
	$sq3_settings =	new sqlite_db('db/settings.sql3');
	$sq3_settings->die_on_error = true;

	$sq3_settings->sql_query("create table if not exists branch (
		id integer, code char(6), description char(100), receipt_header text, receipt_footer text, primary key (id)
	)");
	$sq3_settings->sql_query("create table if not exists counter_settings (
		id integer, network_name char(32), active integer(1), pos_settings text, primary key (id)
	)");
	$sq3_settings->die_on_error = false;
	$sq3_settings->sql_query("alter table counter_settings add mprice_settings text");	
	$sq3_settings->die_on_error = true;

	$sq3_settings->sql_query("create table if not exists pos_settings (
		setting_name char(50), setting_value text, primary key (setting_name)
	)");
	
	$sq3_settings->die_on_error = false;	
	$sq3_settings->sql_query("alter table pos_settings add last_update timestampe default 0");
	$sq3_settings->die_on_error = true;
    $sq3_settings->sql_close();

	$sq3_sku_items = new sqlite_db('db/sku_items.sql3');
	$sq3_sku_items->die_on_error = true;
	$sq3_sku_items->sql_query("create table if not exists sku_items (
		id integer primary key, sku_id integer, sku_item_code char(12),
		packing_uom_id integer, mcode char(20), link_code char(20), receipt_description char(40),
		selling_price double,active integer, uom_code char(6), uom_fraction double
	)");
	$sq3_sku_items->die_on_error = false;
	$sq3_sku_items->sql_query("alter table sku_items add trade_discount_code char(6)");
	$sq3_sku_items->sql_query("alter table sku_items add brand_vendor_rate integer");   //Consignment Bearing
	$sq3_sku_items->sql_query("alter table sku_items add open_price boolean default 0");
	$sq3_sku_items->sql_query("alter table sku_items add decimal_qty tinyint(1) default 0");
	$sq3_sku_items->sql_query("alter table sku_items add batch_no char(20)");
	$sq3_sku_items->sql_query("alter table sku_items add batch_expired_date date");
	//For Category Discount by item and category point by item
	$sq3_sku_items->sql_query("alter table sku_items add category_disc_by_branch_inherit text");			
	$sq3_sku_items->sql_query("alter table sku_items add category_point_by_branch_inherit text");
	$sq3_sku_items->sql_query("alter table sku_items add cat_disc_inherit char(20)");
	$sq3_sku_items->sql_query("alter table sku_items add category_point_inherit char(20)");
	$sq3_sku_items->sql_query("alter table sku_items add is_parent tinyint(1)");
	$sq3_sku_items->sql_query("alter table sku_items add priceChange_date timestamp");
	$sq3_sku_items->sql_query("alter table sku_items add scale_type tinyint(1) default -1");
	$sq3_sku_items->sql_query("alter table sku_items add bom_type char(15)");
	$sq3_sku_items->sql_query("alter table sku_items add bom_detail text");
	$sq3_sku_items->sql_query("alter table sku_items add artno char(20)");
	$sq3_sku_items->sql_query("alter table sku_items add additional_description text");
	$sq3_sku_items->sql_query("alter table sku_items add additional_description_print_at_counter tinyint(1) default 0");
	
	//indexing sku items	- Alex
	$sq3_sku_items->sql_query("create index if not exists sku_id_idx on sku_items (sku_id)");
	$sq3_sku_items->sql_query("create index if not exists sku_item_code_idx on sku_items (sku_item_code)");
	$sq3_sku_items->sql_query("create index if not exists mcode_idx on sku_items (mcode)");
	$sq3_sku_items->sql_query("create index if not exists link_code_idx on sku_items (link_code)");
	$sq3_sku_items->die_on_error = true;

	// for multiple selling
	$sq3_sku_items->sql_query("create table if not exists sku_items_mprice (
		sku_item_id integer, type char(10), price double, trade_discount_code char(6), primary key (sku_item_id, type))");
	$sq3_sku_items->die_on_error = false;
	$sq3_sku_items->sql_query("alter table sku_items_mprice add last_update timestamp");
	$sq3_sku_items->die_on_error = true;
	$sq3_sku_items->sql_query("create table if not exists sku_items_qprice (
		sku_item_id integer, min_qty double, price double,last_update timestamp, primary key (sku_item_id, min_qty))");
		
	$sq3_sku_items->sql_query("create table if not exists sku_items_mqprice (
		sku_item_id integer, min_qty double,type char(50), price double, last_update timestamp, primary key (sku_item_id, min_qty,type))");

	//for sku
	$sq3_sku_items->sql_query("create table if not exists sku (id integer, category_id integer, primary key (id))");
	//for category point
	$sq3_sku_items->sql_query("create table if not exists category (
		id integer, 
		category_disc double, 
		category_point char(10),  
		p1 integer, 
		p2 integer, 
		p3 integer,
		p4 integer,
		p5 integer,
		p6 integer,
		p7 integer,
		p8 integer,
		p9 integer,
		p10 integer,
		member_category_disc double,
		description char(100),
		primary key (id))");

	$sq3_sku_items->die_on_error = false;
	$sq3_sku_items->sql_query("alter table sku add brand_id integer");
	$sq3_sku_items->sql_query("alter table sku add sku_type char(10)");
	$sq3_sku_items->sql_query("alter table sku add have_sn tinyint(1)");
	$sq3_sku_items->sql_query("alter table sku add vendor_id integer");
	$sq3_sku_items->sql_query("alter table sku add scale_type tinyint(1) default 0");

	$sq3_sku_items->sql_query("alter table category add member_category_disc double");
	$sq3_sku_items->sql_query("alter table category add description char(100)");
	
	$sq3_sku_items->sql_query("Alter table category add p4 integer");
	$sq3_sku_items->sql_query("Alter table category add p5 integer");
	$sq3_sku_items->sql_query("Alter table category add p6 integer");
	$sq3_sku_items->sql_query("Alter table category add p7 integer");
	$sq3_sku_items->sql_query("Alter table category add p8 integer");
	$sq3_sku_items->sql_query("Alter table category add p9 integer");
	$sq3_sku_items->sql_query("Alter table category add p10 integer");
	$sq3_sku_items->sql_query("Alter table category add category_disc_by_branch text");
	$sq3_sku_items->sql_query("Alter table category add category_point_by_branch text");
	$sq3_sku_items->sql_query("Alter table category add category_staff_disc_by_branch text");
	$sq3_sku_items->sql_query("Alter table category add active tinyint(1) default 0");

	//indexing sku, category	- Alex 
	$sq3_sku_items->sql_query("create index if not exists sku_category_idx on sku (category_id)");
	$sq3_sku_items->sql_query("create index if not exists category_p1x on category (p1)");
	$sq3_sku_items->sql_query("create index if not exists category_p2x on category (p2)");
	$sq3_sku_items->sql_query("create index if not exists category_p3x on category (p3)");
	$sq3_sku_items->sql_query("create index if not exists category_p4x on category (p4)");
	$sq3_sku_items->sql_query("create index if not exists category_p5x on category (p5)");
	$sq3_sku_items->sql_query("create index if not exists category_p6x on category (p6)");
	$sq3_sku_items->sql_query("create index if not exists category_p7x on category (p7)");
	$sq3_sku_items->sql_query("create index if not exists category_p8x on category (p8)");
	$sq3_sku_items->sql_query("create index if not exists category_p9x on category (p9)");
	$sq3_sku_items->sql_query("create index if not exists category_p10x on category (p10)");
	
	$sq3_sku_items->die_on_error = true;

	$sq3_sku_items->sql_close();

	$sq3_membership = new sqlite_db('db/membership.sql3');
	$sq3_membership->die_on_error = false;
//	$sq3_membership->sql_query("drop table membership");
	$sq3_membership->sql_query("create table if not exists membership (
		nric char(20) primary key, name char(80), card_no char(20), points integer default 0,
		next_expiry_date timestamp default 0 ,blocked_date timestamp default 0,
		terminated_date timestamp default 0
	)");

	$sq3_membership->sql_query("alter table membership add member_type char(15)");
	$sq3_membership->sql_query("alter table membership add points_update date");
	$sq3_membership->sql_query("alter table membership add parent_nric char(20)");
	$sq3_membership->sql_query("alter table membership add quota_balance double not null default 0");
	$sq3_membership->sql_query("alter table membership add quota_last_update timestamp");
	$sq3_membership->sql_query("alter table membership add staff_type char(20)");
	//indexing membership
	$sq3_membership->sql_query("create index if not exists card_no_idx on membership (card_no)");
	$sq3_membership->sql_query("create index if not exists nric_idx on membership (nric)");

	$sq3_membership->die_on_error = true;

	$sq3_membership->sql_close();

	$sq3_promotion = new sqlite_db('db/promotion.sql3');
	$sq3_promotion->die_on_error = true;
//	$sq3_promotion->sql_query("drop table promotion");
//	$sq3_promotion->sql_query("drop table promotion_items");
	$sq3_promotion->sql_query("create table if not exists promotion (
		id integer primary key, title char(200), date_from date, date_to date,
		time_from time, time_to time, approved integer, status integer, promo_branch_id text,
		active integer
	)");

    $sq3_promotion->die_on_error = false;
	$sq3_promotion->sql_query("alter table promotion add print_title_in_receipt tinyint(1) default 0");
	$sq3_promotion->sql_query("alter table promotion add category_point_inherit char(20) default 'inherit'");
	$sq3_promotion->sql_query("alter table promotion add category_point_inherit_data text");

	//Consignment Bearing
	$sq3_promotion->sql_query("alter table promotion add consignment_bearing boolean default 0");
	$sq3_promotion->sql_query("alter table promotion add promo_type char(20) not null default 'discount'");

	//indexing promotion
	$sq3_promotion->sql_query("create index if not exists promo_id on promotion (status,active)");
	$sq3_promotion->sql_query("create index if not exists promo_id on promotion (id)");
	
	$sq3_promotion->die_on_error = true;

	$sq3_promotion->sql_query("create table if not exists promotion_items (
		id integer primary key, promo_id integer, sku_item_id integer, brand_id integer,
		member_disc_p char(4), member_disc_a double, non_member_disc_p char(4), non_member_disc_a double,
		member_min_item integer, non_member_min_item integer, category_id integer,
		member_qty_from integer, member_qty_to integer, non_member_qty_from integer,non_member_qty_to integer
	)");

	$sq3_promotion->die_on_error = false;
	$sq3_promotion->sql_query("alter table promotion_items add download boolean default 0");
	$sq3_promotion->sql_query("alter table promotion_items add control_type tinyint(1) default 0");
    $sq3_promotion->sql_query("alter table promotion_items add member_limit integer");
    $sq3_promotion->sql_query("alter table promotion_items add block_normal boolean default 0");
    $sq3_promotion->sql_query("alter table promotion_items add member_block_normal boolean default 0");

 	//Consignment Bearing
	$sq3_promotion->sql_query("alter table promotion_items add non_member_prof_p double not null default '0'");
    $sq3_promotion->sql_query("alter table promotion_items add non_member_net_bear_p double not null default '0'");
    $sq3_promotion->sql_query("alter table promotion_items add non_member_use_net boolean default 0");
    $sq3_promotion->sql_query("alter table promotion_items add non_member_trade_code char(5)");
    $sq3_promotion->sql_query("alter table promotion_items add member_prof_p double not null default '0'");
    $sq3_promotion->sql_query("alter table promotion_items add member_net_bear_p double not null default '0'");
    $sq3_promotion->sql_query("alter table promotion_items add member_use_net boolean default 0");
    $sq3_promotion->sql_query("alter table promotion_items add member_trade_code char(5)");
    $sq3_promotion->sql_query("alter table promotion_items add allowed_member_type text");

	//indexing promotion items
	$sq3_promotion->sql_query("create index if not exists promo_id on promotion_items (promo_id)");
	$sq3_promotion->sql_query("create index if not exists sku_item_id on promotion_items (sku_item_id)");

    $sq3_promotion->die_on_error = true;
	$sq3_promotion->sql_query("create table if not exists promotion_mix_n_match_items(branch_id integer, id integer primary key,promo_id integer, group_id integer, sequence_num integer, user_id integer, disc_target_type char(20), disc_target_value char(20), disc_target_info text, disc_condition text, disc_by_type char(20), disc_by_value double, disc_limit integer, receipt_limit integer, disc_prefer_type boolean,for_member boolean default 0, for_non_member boolean default 0,follow_sequence boolean default 0, item_remark text)");
	$sq3_promotion->die_on_error = false;
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add qty_from integer");
	$sq3_promotion->sql_query("Alter table promotion_mix_n_match_items add receipt_description char(35)");
	$sq3_promotion->sql_query("Alter table promotion_mix_n_match_items add item_category_point_inherit_data text");
	$sq3_promotion->sql_query("Alter table promotion_mix_n_match_items add for_member_type text");
	
	// 7/13/2011 4:40:09 PM Andy
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add disc_target_sku_type char(10)");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add disc_target_price_type char(10)");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add disc_target_price_range_from double");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add disc_target_price_range_to double");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add disc_by_qty integer");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add loop_limit integer");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add control_type integer");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add download integer default 1");
	$sq3_promotion->sql_query("alter table promotion_mix_n_match_items add prompt_available integer default 1");

	//indexing promotion mix n match items
	$sq3_promotion->sql_query("create index if not exists promo_id on promotion_mix_n_match_items (promo_id)");
	$sq3_promotion->sql_query("create index if not exists sku_item_id on promotion_mix_n_match_items (sku_item_id)");
	$sq3_promotion->die_on_error = true;

	$sq3_promotion->sql_query("create table if not exists membership_redemption_sku (
		id integer, sku_item_id integer, point integer default 0, cash double default 0, receipt_amount double default 0,
		timestamp timestamp default 0, active boolean default 0,
		PRIMARY KEY (id,sku_item_id,point)
	)");

	$sq3_promotion->sql_query("create table if not exists membership_promotion_items (
		id integer, branch_id integer, card_no char(20), promo_id integer, pos_id integer,
		counter_id integer, sku_item_id integer, qty double default 0, date date, added timestamp default 0, sync tinyint(1) default 0,
		PRIMARY KEY (id,branch_id,counter_id,date)
	)");
	$sq3_promotion->sql_query("create table if not exists membership_promotion_mix_n_match_items(
				id integer,
				branch_id integer,
				card_no char(20) not null,
				real_promo_id integer,
				promo_id integer,
				group_id integer,
				pos_id integer,
				counter_id integer,
				qty double,
				amount double,
				date date,
				used tinyint(1),
				sync tinyint(1) default 0,
				added timestamp default 0,
				primary key(branch_id,date,counter_id,pos_id,id)
			)");
			
	// fix the problem due to sqlite cannot drop primary key, hv to recreate new table
	if(!$sq3_promotion->sql_affectedrows()){	// create table not success, alrdy hv table
		// check whether table structure still using old method
		$sq3_promotion->sql_query("select * from sqlite_master where tbl_name='membership_promotion_mix_n_match_items' and name='membership_promotion_mix_n_match_items' and sql like '%primary key(branch_id, id)%'");
		$is_old_structure = $sq3_promotion->sql_fetchrow();
		$sq3_promotion->sql_freeresult();
		
		if($is_old_structure){	// it is still using old method
			// create a new table
			$sq3_promotion->sql_query("create table if not exists membership_promotion_mix_n_match_items2(
					id integer,
					branch_id integer,
					card_no char(20) not null,
					real_promo_id integer,
					promo_id integer,
					group_id integer,
					pos_id integer,
					counter_id integer,
					qty double,
					amount double,
					date date,
					used tinyint(1),
					sync tinyint(1) default 0,
					added timestamp default 0,
					primary key(branch_id,date,counter_id,pos_id,id)
				)");
			// clone all data to new table
			$sq3_promotion->sql_query("insert into membership_promotion_mix_n_match_items2 select * from membership_promotion_mix_n_match_items");
			// drop the original table
			$sq3_promotion->sql_query("drop table membership_promotion_mix_n_match_items");
			// rename new table
			$sq3_promotion->sql_query("alter table membership_promotion_mix_n_match_items2 rename to membership_promotion_mix_n_match_items");
		}
	}
			
	$sq3_promotion->sql_close();
	unset($sq3_promotion);

	$sq3_user =	new sqlite_db('db/users.sql3');
	$sq3_user->die_on_error = true;
//	$sq3_user->sql_query("drop table user");
//	$sq3_user->sql_query("drop table user_privilege");
	$sq3_user->sql_query("create table if not exists user (
		id integer primary key, u char(50), l char(16), p char(64), active boolean, fullname char(100)
	)");
	$sq3_user->sql_query("create table if not exists user_privilege (
		user_id integer, privilege_code char(20), primary key (user_id, privilege_code)
	)");
	$sq3_user->die_on_error = false;
	$sq3_user->sql_query("alter table user add barcode char(12)");
	$sq3_user->sql_query("alter table user add discount_limit double");
	$sq3_user->sql_query("alter table user add allow_mprice text");
	$sq3_user->sql_query("alter table user add item_disc_only_allow_percent tinyint(1) default 0");
	$sq3_user->die_on_error = true;
    $sq3_user->sql_close();
    unset($sq3_user);
	
    //Create Sales Agent Database	
	$sq3_sales_agent = new sqlite_db("db/sales_agent.sql3");	
	$sq3_sales_agent->die_on_error = true;	
	$sq3_sales_agent->sql_query("create table if not exists sales_agent(
	id integer, code char(50) unique, active tinyint(1) default 1,primary key(id,code))");	
	$sq3_sales_agent->sql_query("create index if not exists sa_id_idx on sales_agent(id)");
	$sq3_sales_agent->sql_close();
	unset($sq3_sales_agent);
	
	//Create items_batch_price_change
	$sql3_batch_price_change = new sqlite_db("db/batch_price_change.sql3");
	$sql3_batch_price_change->die_on_error = true;
	$sql3_batch_price_change->sql_query("create table if not exists sku_items_future_price(id integer primary key,date date,time time,active tinyint(1), status tinyint(1), approved tinyint(1), cron_status tinyint(1))");
	$sql3_batch_price_change->sql_query("create table if not exists sku_items_future_price_items(id int(11) primary key, fp_id int(11),sku_item_id int(11),type varchar(30), min_qty tinyint(1), future_selling_price double)");
	$sql3_batch_price_change->sql_close();

	//Create Return Policy Table
	$sq3_return_policy = new sqlite_db("db/return_policy.sql3");
	$sq3_return_policy->die_on_error = true;
	$sq3_return_policy->sql_query("create table if not exists return_policy(id integer,
			branch_id integer,
			title varchar(100),
			duration_condition tinyint(1) default 1,
			durations text,
			expiry_durations tinyint(3) default 1,
			expiry_type	tinyint(1),
			charges_condition tinyint(1),
			charges	text,
			receipt_remark varchar(40),
			max_charges integer,
			active tinyint(1),
			last_update timestamp,primary key (id,branch_id))");

	$sq3_return_policy->sql_query("create index if not exists rp_id_idx on return_policy(id,branch_id)");

	//Create return policy setup
	$sq3_return_policy->sql_query("create table if not exists return_policy_setup(id integer,
			branch_id integer,
			ref_id integer,
			ref_branch_id integer,
			type tinyint(1),
			setup text,
			active tinyint(1),
			status tinyint(1),
			added timestamp,
			last_update timestamp, primary key(id,branch_id))");
	$sq3_return_policy->sql_query("create index if not exists ref_id_branch_id_type on return_policy_setup(branch_id, ref_id, type)");
	$sq3_return_policy->sql_close();
	unset($sq3_return_policy);
	$sq3_sku_group_items = new sqlite_db("db/sku_group_item.sql3");
	$sq3_sku_group_items->die_on_error = true;
	$sq3_sku_group_items->sql_query("create table if not exists sku_group_item(
			sku_group_id integer,
			branch_id integer,
			sku_item_code char(12),primary key(sku_group_id,branch_id,sku_item_code))");
	$sq3_sku_group_items->sql_query("create index if not exists sgid_bid_sicode on sku_group_item(sku_group_id,branch_id,sku_item_code)");
	$sq3_sku_group_items->sql_close();
	unset($sq3_sku_group_items);
	
	//Create coupon, broadcast message database
	$sq3_coupon = new sqlite_db("db/coupon.sql3");
	$sq3_coupon->die_on_error = true;
	$sq3_coupon->sql_query("create table if not exists coupon(
		id integer,
		branch_id integer,
		code char(7),
		dept_id	integer,
		brand_id integer,
		vendor_id integer,
		is_print integer,
		active tinyint(1),		
		last_update	timestamp,
		valid_from date,
		valid_to date,
		remark text,
		time_from time,
		time_to time,
		si_list text,
		min_qty integer,
		primary key(id,branch_id,code)
	)");
	$sq3_coupon->sql_close();
	unset($sq3_coupon);
	
	$sq3_broadcast_message = new sqlite_db("db/broadcast_msg.sql3");
	$sq3_broadcast_message->die_on_error = true;
	$sq3_broadcast_message->sql_query("create table if not exists gpm_broadcast_msg(
	id integer primary key,
	msg char(100),
	expire_timestamp timestamp,
	allowed_branch text,
	active tinyint(1),
	added timestamp,
	last_update timestamp)");	
	$sq3_broadcast_message->sql_freeresult();
	$sq3_broadcast_message->sql_query("create table if not exists gpm_broadcast_trade_offer(
	id integer primary key,	
	title char(100),
	date_from date,
	date_to	date,
	qualify_qty double,
	qualify_offer char(100),
	allowed_branch text,
	active tinyint(1),
	status tinyint(1),
	added timestamp,
	last_update timestamp
	)");
	
	$sq3_broadcast_message->sql_query("create table if not exists gpm_broadcast_trade_offer_items(
	id integer primary key,
	gpm_trade_offer_id integer,
	gpm_sku_id integer,
	mcode char(20),
	sku_item_id integer
	)");
	
	$sq3_broadcast_message->sql_query("create table if not exists gpm_broadcast_trade_offer_summary(
	branch_id integer,
	gpm_trade_offer_id integer,
	total_qualify_qty double,
	total_qualify_counter integer,
	last_update timestamp,
	primary key(branch_id,gpm_trade_offer_id)
	)");
	
	$sq3_broadcast_message->sql_close();
	unset($sq3_broadcast_message);
}

function sync_coupon()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	
	$con->sql_query("select count(*) as count from coupon") or _die_(mysql_error());
	$rcount = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	$no_of_row = $rcount['count'];
	print "Sync coupon \n";
	print "No of Record: $no_of_row\n";
	
	$l = 10000;
	if($no_of_row>0)
	{
		for($i=0;$i<$no_of_row;$i=$i+$l)
		{
			print "$i ".memory_get_usage()."\n";
			$rs = $con->sql_query("select (id*1000+branch_id) as id, branch_id, code, dept_id, brand_id, vendor_id, is_print, active, last_update, valid_from,valid_to,remark,time_from,time_to, si_list, min_qty from coupon order by id limit $i, $l") or _die_(mysql_error());
			$fout = fopen($db_path."/coupon.table","wt");
			while($r = $fetchfunc($rs))
			{				
				foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
				fputs($fout,join("~",$r)."\n");
			}
			fclose($fout);

			//passthru('./sqlite3.sh -separator ~~ db/coupon.sql3 ".import '.$db_path.'/coupon.table coupon"');
			//passthru('sqlite3.exe -separator ~~ db/coupon.sql3 ".import '.$db_path.'/coupon.table coupon"');
			passthru('sqlite3 -separator "~" db/coupon.sql3 ".import '.$db_path.'/coupon.table coupon"');
			unlink($db_path."/coupon.table");
			$con->sql_freeresult($rs);
		}
	}
}

function sync_broadcast_message()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	//Sync broadcast Message
	$con->sql_query("select count(*) as count from gpm_broadcast_msg where expire_timestamp >=".ms(date("Y-m-d H:i:s"))." order by id") or _die_(mysql_error());
	$rcount = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	$no_of_row = $rcount['count'];
	print "Sync Broadcast Message\n";
	print "No of Record: $no_of_row\n";
	$l = 10000;
	if($no_of_row>0)
	{
		for($i=0;$i<$no_of_row;$i=$i+$l)
		{
			print "$i ".memory_get_usage()."\n";
			$rs = $con->sql_query("select id,msg,expire_timestamp,allowed_branch,active,added,last_update from gpm_broadcast_msg where expire_timestamp >=".ms(date("Y-m-d H:i:s"))." order by id limit $i, $l") or _die_(mysql_error());
			$fout = fopen($db_path."/gpm_broadcast_msg.table","wt");
			while($r = $fetchfunc($rs))
			{				
				foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
				fputs($fout,join("~",$r)."\n");
			}
			fclose($fout);

			//passthru('./sqlite3.sh -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_msg.table gpm_broadcast_msg"');
			//passthru('sqlite3.exe -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_msg.table gpm_broadcast_msg"');
			passthru('sqlite3 -separator "~" db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_msg.table gpm_broadcast_msg"');
			unlink("$db_path/gpm_broadcast_msg.table");
			$con->sql_freeresult($rs);
		}
	}
}

function sync_broadcast_trade_offer()
{
	global $con, $code, $config, $branch_id, $fetchfunc, $db_path;
	//Sync gpm_broadcast_trade_offer	
	$con->sql_query("select count(*) as count from gpm_broadcast_trade_offer where ".ms(date("Y-m-d"))." between date_from and date_to order by id") or _die_(mysql_error());
	$rcount = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	$no_of_row = $rcount['count'];
	print "Sync Trade Offer\n";
	print "No of Record: $no_of_row\n";
	$l = 10000;
	if($no_of_row>0)
	{
		for($i=0;$i<$no_of_row;$i=$i+$l)
		{
			print "$i ".memory_get_usage()."\n";
			$rs = $con->sql_query("select id, title, date_from, date_to, qualify_qty, qualify_offer, allowed_branch, active, status, added, last_update from gpm_broadcast_trade_offer where ".ms(date("Y-m-d"))." between date_from and date_to order by id limit $i, $l") or _die_(mysql_error());
			$fout = fopen($db_path."/gpm_broadcast_trade_offer.table","wt");
			while($r = $fetchfunc($rs))
			{				
				$trade_offer_id[$r[0]] = 1;
				foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
				fputs($fout,join("~",$r)."\n");
			}
			fclose($fout);

			//passthru('./sqlite3.sh -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer.table gpm_broadcast_trade_offer"');
			//passthru('sqlite3.exe -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer.table gpm_broadcast_trade_offer"');
			passthru('sqlite3 -separator "~" db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer.table gpm_broadcast_trade_offer"');
			unlink("gpm_broadcast_trade_offer.table");
			$con->sql_freeresult($rs);
		}
	}
				
	if(isset($trade_offer_id))
	{
		$con->sql_query("select count(*) as count from gpm_broadcast_trade_offer_items where gpm_trade_offer_id in (".join(",",array_keys($trade_offer_id)).") order by id") or _die_(mysql_error());
		$rcount = $con->sql_fetchrow();
		$con->sql_freeresult();
	}
	else{
		$rcount['count'] = 0;
	}
	$no_of_row = $rcount['count'];
	print "Sync Trade Offer Items\n";
	print "No of Record: $no_of_row\n";
	$l = 10000;
	if($no_of_row>0)
	{
		for($i=0;$i<$no_of_row;$i=$i+$l)
		{
			print "$i ".memory_get_usage()."\n";
			$rs = $con->sql_query("select id,gpm_trade_offer_id,gpm_sku_id,mcode,sku_item_id from gpm_broadcast_trade_offer_items where gpm_trade_offer_id in (".join(",",array_keys($trade_offer_id)).") order by id limit $i, $l") or _die_(mysql_error());
			$fout = fopen($db_path."/gpm_broadcast_trade_offer_items.table","wt");
			while($r = $fetchfunc($rs))
			{								
				foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
				fputs($fout,join("~",$r)."\n");
			}
			fclose($fout);

			//passthru('./sqlite3.sh -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_items.table gpm_broadcast_trade_offer_items"');
			//passthru('sqlite3.exe -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_items.table gpm_broadcast_trade_offer_items"');
			passthru('sqlite3 -separator "~" db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_items.table gpm_broadcast_trade_offer_items"');
			unlink("gpm_broadcast_trade_offer_items.table");
			$con->sql_freeresult($rs);
		}
	}	
	
	//Sync Broadcast trade_offer_summary	
	if(isset($trade_offer_id))
	{
		$con->sql_query("select count(*) as count from gpm_broadcast_trade_offer_summary where gpm_trade_offer_id in (".join(",",array_keys($trade_offer_id)).")") or _die_(mysql_error());
		$rcount = $con->sql_fetchrow();
		$con->sql_freeresult();
	}
	else{
		$rcount['count'] = 0;
	}
	$no_of_row = $rcount['count'];
	print "Sync Trade Offer Summary\n";
	print "No of Record: $no_of_row\n";
	$l = 10000;
	if($no_of_row>0)
	{
		for($i=0;$i<$no_of_row;$i=$i+$l)
		{
			print "$i ".memory_get_usage()."\n";
			$rs = $con->sql_query("select branch_id,gpm_trade_offer_id,total_qualify_qty,total_qualify_counter,last_update from gpm_broadcast_trade_offer_summary where gpm_trade_offer_id in (".join(",",array_keys($trade_offer_id)).") limit $i, $l") or _die_(mysql_error());
			$fout = fopen("gpm_broadcast_trade_offer_summary.table","wt");
			while($r = $fetchfunc($rs))
			{								
				foreach($r as $k=>$v) { $r[$k] = str_replace("~","",$v); }
				fputs($fout,join("~",$r)."\n");
			}
			fclose($fout);

			//passthru('./sqlite3.sh -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_summary.table gpm_broadcast_trade_offer_summary"');
			//passthru('sqlite3.exe -separator ~~ db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_summary.table gpm_broadcast_trade_offer_summary"');
			passthru('sqlite3 -separator "~" db/broadcast_msg.sql3 ".import '.$db_path.'/gpm_broadcast_trade_offer_summary.table gpm_broadcast_trade_offer_summary"');
			unlink("gpm_broadcast_trade_offer_summary.table");
			$con->sql_freeresult($rs);
		}
	}
	
	if(isset($trade_offer_id)) unset($trade_offer_id);
}

?>
