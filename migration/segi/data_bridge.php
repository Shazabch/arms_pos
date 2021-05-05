<?php
/*
10/4/2013 10:24 AM Andy
- Check do not update scale_type if scale_type is empty.
- Check if receipt description is empty, use product description.
- Check do not update selling price if selling price <= zero.

1/3/2014 4:59 PM Justin
- Bug fixed on batch price change always create new one instead of using existing due to limit 1 result unmatched.

2/23/2018 3:40 PM Andy
- Enhanced to check sku_obsolete and restore sku when sync sku.
*/

if(php_sapi_name() != 'cli'){
	die("The script must run in terminal.");
}

define('TERMINAL', 1);

@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
//print "Checking other process using ps x\n";

if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}
	
include_once('../../config.php');
include_once('../../include/mysql.php');
include_once('../../include/db.php');
include_once('../../include/functions.php');
error_reporting (E_ALL ^ E_NOTICE);

if (!$con->db_connect_id) { die('cannot connect '.mysql_error()); }

$agrs = $_SERVER['argv'];
array_shift($agrs); // remove php
$a = array_shift($agrs);	// first parameter is always action

// additional parameters
//while($mode = array_shift($agrs)){

//}

switch($a){
	case 'test':
		test();
		exit;
	case 'update_vendor_portal_master':
		update_vendor_portal_master();
		exit;
	case 'update_sap_uom_mapping':	// update uom by sap_uom.csv
		update_sap_uom_mapping();
		exit;
	//case 'update_sap_sku_mapping':
	//	update_sap_sku_mapping();
	//	exit;
	case 'update_sap_vendor_mapping':	// update by sap_vendor2.csv
		update_sap_vendor_mapping();
		exit;
	case 'update_sap_article_mapping':	// update by sap_article.csv
		update_sap_article_mapping();
		exit;
	case 'update_sap_category_mapping':	// update by sap_category.csv
		update_sap_category_mapping();
		exit;
	case 'update_sap_sku_master':	// update from table "POSDM_SAP_SKU_Master"
		update_sap_sku_master();
		exit;
	case 'update_vendor_portal_profit':	// update from table "POSDM_SKU_RJ_LICENSEE"
		// sales_report_profit_by_date
		update_vendor_portal_profit();	
		exit;
	case 'update_vendor_portal_bonus':	// update from table "POSDM_SKU_RJ_SALES_TIER"
		// sales_bonus_by_step
		update_vendor_portal_bonus();
		exit;
	case 'update_vendor_sku_group':	// update from table "POSDM_SKU_RJ_SKU_GROUP"
		update_vendor_sku_group();
		exit;
	case 'update_sap_sku_price': // update from POSDM_SKU_PRICE
		update_sap_sku_price();
		exit;
	case 'auto_map_new_vendor':
		auto_map_new_vendor();
		exit;
	case 'test_connect':
		test_connect();
		exit;
	default:
	    print "Invalid Action.\n";
	    exit;
}




$agrs = $_SERVER['argv'];

function connect_sap(){
	global $con, $con2;
		
	require_once('msdb.php');
}


function test(){
	print "Process start at ".date("Y-m-d H:i:s")."\n";
	
	auto_map_new_vendor();
	
	update_sap_sku_master();
	
	update_sap_sku_price();
	
	update_vendor_sku_group();
	
	update_vendor_portal_profit();
	
	update_vendor_portal_bonus();
}

// php data_bridge.php update_sap_uom_mapping
function update_sap_uom_mapping(){
	global $con;
	
	print "Update SAP UOM\n";
	
	$con->sql_query("create table if not exists sap_uom(
		arms_uom_id int,
		fraction double,
		sap_uom_code char(5),
		index(arms_uom_id),
		index(sap_uom_code),
		index(fraction)
	)");
	
	
	$file = 'sap_uom.csv';
	
	$f = fopen($file,"rt");
	
	if(!$file)	die("$file cannot be open\n");
	
	$con->sql_query("truncate sap_uom");
	
	$line = fgetcsv($f);	// skip first line, it is header
	$line = fgetcsv($f);	// skip second line, it is header as well
	
	$row_count = 0;
	
	while($r = fgetcsv($f)){
		$row_count++;
		print "\r$row_count. . . .";
		
		$arms_uom_id = mi($r[0]);
		$arms_uom_code = trim($r[1]);
		$desc = trim($r[2]);
		$fraction = mf($r[3]);
		$sap_uom_code = trim($r[4]);
		
		if(!$arms_uom_id)	continue;
		
		$upd = array();
		$upd['arms_uom_id'] = $arms_uom_id;
		$upd['fraction'] = $fraction;
		$upd['sap_uom_code'] = $sap_uom_code;
		
		$con->sql_query("insert into sap_uom ".mysql_insert_by_field($upd));
		
	}
	
	print "\nDone, $row_count row updated.\n";
}

function update_vendor_portal_master(){
	global $con, $con2;
	
	if(!$con2)	connect_sap();	// connect sap
	
	update_vendor_portal_profit();	// sales_report_profit_by_date
	
	update_vendor_portal_bonus();	// sales_bonus_by_step
	
	print "\nDone.\n";
}

/*function update_sap_sku_mapping(){
	global $con, $con2;
	
	print "\nStart update_sap_sku_mapping. . .\n";
	if(!$con2)	connect_sap();	// connect sap
	
	$con->sql_query("create table if not exists sap_sku_map(
		sku_item_id int primary key,
		sku_item_code char(12),
		articleid int,
		gtin char(30)
	)");
	
	$updated_row = 0;
	
	while($r = false){
		$upd = array();
		$upd['sku_item_id'] = 0;
		$upd['sku_item_code'] = 0;
		$upd['articleid'] = 0;
		$upd['gtin'] = 0;
		
		$con->sql_query("replace into sap_sku_map ".mysql_insert_by_field($upd));
		$updated_row++;
	}
	
	print "\nDone update_sap_sku_mapping, $updated_row row updated.\n";
}*/

// php data_bridge.php update_sap_vendor_mapping
function update_sap_vendor_mapping(){
	global $con;
	
	print "\nStart update_sap_vendor. . .\n";
	
	$con->sql_query("create table if not exists sap_vendor(
		arms_vendor_id int primary key,
		sap_vendor_id int unique
	)");
	
	
	$file = 'sap_vendor2.csv';
	
	$f = fopen($file,"rt");
	
	if(!$f)	die("$file cannot be open\n");
	
	$con->sql_query("truncate sap_vendor");
	
	$line = fgetcsv($f);	// skip first line, it is header
	
	$row_count = 0;
	
	while($r = fgetcsv($f)){
		$row_count++;
		print "\r$row_count. . . .";
		
		$arms_vendor_id = mi($r[4]);
		$sap_vendor_id = mi($r[0]);
		
		if(!$arms_vendor_id || !$sap_vendor_id)	continue;
		
		$upd = array();
		$upd['arms_vendor_id'] = $arms_vendor_id;
		$upd['sap_vendor_id'] = $sap_vendor_id;
		
		$con->sql_query("replace into sap_vendor ".mysql_insert_by_field($upd));
		
	}
	
	print "\nDone, $row_count row updated.\n";
}

// php data_bridge.php update_sap_sku_master
function update_sap_sku_master(){
	global $con, $con2;
	
	print "\nStart update_sap_sku_master. . .\n";
	if(!$con2)	connect_sap();	// connect sap

	// UOM list
	$uom_list = array();
	$con->sql_query("select * from uom order by id");
	while($r = $con->sql_fetchassoc()){
		$uom_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$ret = $con2->query("select * from POSDM_SAP_SKU_Master where CHANGE_FLAG=1 order by SKU_CODE, TIMESTAMP");	// order by sku_code (article id)
	
	$row_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){	
		$row_count++;
		print "\rRunning row $row_count. . .";
		
		$itemcode = '';
		
		// sap info
		$sap_article_id = mi($r['SKU_CODE']);
		$sap_vendor_id = mi($r['VENDOR_ID']);
		$sap_category_id = trim($r['CATEGORY_ID']);
		$group_non_returnable = mi($r['GROUP_NON_RETURNABLE']);
		
		$mcode = trim($r['GTIN_CODE']);
		$uom_id = mi($r['UOM_ID']);
		
		if(!isset($uom_list[$uom_id]))	$uom_id = 1;	// invalid uom id, make it as default EACH
		
		$cat_id = get_arms_category_id_from_sap_category_id($sap_category_id);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		// get from sku_items
		$con->sql_query("select * from sku_items where mcode=".ms($mcode));
		$curr_si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sku = array();
		$sku['category_id'] = $cat_id;
		$sku['vendor_id'] = $vendor_id;
		$sku['timestamp'] = $r['TIMESTAMP'];
		if(trim($r['SCALE_TYPE'])){
			$sku['scale_type'] = $r['SCALE_TYPE'];
		}
		if($group_non_returnable == 0 || $group_non_returnable == 1)	$sku['group_non_returnable'] = $group_non_returnable;
		
		$si = array();
		$si['mcode'] = $mcode;
		$si['description'] = trim($r['DESCRIPTION']);
		$si['receipt_description'] = trim($r['RECEIPT_DESCRIPTION']);
		if(!$si['receipt_description'])	$si['receipt_description'] = $si['description'];
		
		$si['lastupdate'] = 'CURRENT_TIMESTAMP';
		if(mf($r['SELLING_PRICE'])>0){
			$si['selling_price'] = mf($r['SELLING_PRICE']);
		}
		
		$si['cost_price'] = mf($r['COST_PRICE']);
		$si['decimal_qty'] = mi($r['DECIMAL_QTY']);
		$si['doc_allow_decimal'] = mi($r['DOC_ALLOW_DECIMAL']);
		$si['packing_uom_id'] = $uom_id;
		
		
		$sku_id = get_arms_sku_id_from_sap_article_id($sap_article_id);
		$si_id = 0;
		
		if($sku_id || $curr_si['sku_id']){
			if(!$sku_id && $curr_si['sku_id']){
				// create new sap article / arms sku id link
				$upd = array();
				$upd['sap_article_id'] = $sap_article_id;
				$upd['arms_sku_id'] = $curr_si['sku_id'];
				$con->sql_query("replace into sap_article ".mysql_insert_by_field($upd));
				
				$sku_id = $curr_si['sku_id'];
			}
			
			// get current sku
			$con->sql_query("select id from sku where id=$sku_id");
			$curr_sku = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$curr_sku){	// not found
				// get from sku_obsolete
				$con->sql_query("select * from sku_obsolete where id=$sku_id");
				$sku_obsolete = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if($sku_obsolete){	// need to insert back to sku
					/* Sample
					replace into sku
(id, sku_code, category_id, vendor_id, brand_id, status, active, remark, sku_type, apply_branch_id, trade_discount_type, timestamp, added, varieties, uom_id, is_bom, no_inventory, is_fresh_market, mst_input_tax, mst_output_tax, mst_inclusive_tax)
values
(45582, 28045582, 138, 0, 0, 1, 1, 'SAP INTEGRATION', 'OUTRIGHT', 1, 0, '2018-01-11 00:00:00', '2018-01-11 00:00:00', 1, 1, 0, 'inherit', 'inherit', -1, -1, 'inherit')
					*/
					$sku_obsolete['no_inventory'] = 'inherit';
					$sku_obsolete['is_fresh_market'] = 'inherit';
					$sku_obsolete['mst_inclusive_tax'] = 'inherit';
					$sku_obsolete['mst_input_tax'] = -1;
					$sku_obsolete['mst_output_tax'] = -1;
					$con->sql_query("replace into sku ".mysql_insert_by_field($sku_obsolete));
				}
			}
			
			// update sku
			$con->sql_query("update sku set ".mysql_update_by_field($sku)." where id=$sku_id");
			
			// get max sku item code
			
			$findcode = (28000000 + intval($sku_id)) . '%';
			$con->sql_query("select max(sku_item_code) as code from sku_items where sku_item_code like ".ms($findcode));
			$tmp  = $con->sql_fetchrow();
			$con->sql_freeresult();
			
			$max_code = mi($tmp['code']);
			
			if($max_code){
				$itemcode = $max_code+1;
			}else{
				$itemcode = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
			}
			
		}else{
			// create new sku
			$sku['added'] = $r['ADDED'];
			$sku['apply_branch_id'] = 1;
			$sku['active'] = 1;
			$sku['status'] = 1;
			$sku['brand_id'] = 0;
			$sku['sku_type'] = 'OUTRIGHT';
			$sku['remark'] = 'SAP INTEGRATION';
			
			$con->sql_query("insert into sku ".mysql_insert_by_field($sku));
			$sku_id = $con->sql_nextid();
			
			// create new sap article / arms sku id link
			$upd = array();
			$upd['sap_article_id'] = $sap_article_id;
			$upd['arms_sku_id'] = $sku_id;
			$con->sql_query("replace into sap_article ".mysql_insert_by_field($upd));
			
			$itemcode = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
		
			$upd = array();
			$upd['sku_code'] = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id);
			
			// update sku master record
			$con->sql_query("update sku set ".mysql_update_by_field($upd)." where id = $sku_id");			
		}
		
		$si['sku_id'] = $sku_id;
			
		if($curr_si){	// exists
			// update sku_items
			$con->sql_query("update sku_items set ".mysql_update_by_field($si)." where id=".mi($curr_si['id']));
			
			if($curr_si['is_parent'] && ($curr_si['sku_id'] != $si['sku_id'] || $si['packing_uom_id'] != 1))	check_and_renew_is_parent($curr_si['sku_id']);
			
			$si_id = $curr_si['id'];
			
			$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$si_id");
		}else{	// new
			// create new sku_items
			$si['added'] = $r['ADDED'];
			$si['sku_item_code'] = $itemcode;
			$itemcode++;
			
			$con->sql_query("insert into sku_items ".mysql_insert_by_field($si));
			$si_id = $con->sql_nextid();
			
			check_and_renew_is_parent($si['sku_id']);
		}
		
		
		$success = $con2->exec("update POSDM_SAP_SKU_Master set CHANGE_FLAG=0 where GTIN_CODE=".ms($r['GTIN_CODE']));
	}
	
	$ret->closeCursor();
		
	print "\nFinish update_sap_sku_master. $row_count item processed.\n";
}

function get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id){
	global $con;
	
	if(!$sap_vendor_id)	return 0;
	$con->sql_query("select arms_vendor_id from sap_vendor where sap_vendor_id=".ms($sap_vendor_id));
	$arms_vendor_id = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	return $arms_vendor_id;
}

function get_arms_sku_id_from_sap_article_id($sap_article_id){
	global $con;
	
	if(!$sap_article_id)	return 0;
	$con->sql_query("select arms_sku_id from sap_article where sap_article_id=".ms($sap_article_id));
	$arms_sku_id = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	return $arms_sku_id;
}

// php data_bridge.php update_sap_article_mapping
function update_sap_article_mapping(){
	global $con;
	
	print "\nStart update_sap_article_mapping. . .\n";
	
	$con->sql_query("create table if not exists sap_article(
		arms_sku_id int primary key,
		sap_article_id int unique
	)");
	
	
	$file = 'sap_article.csv';
	
	$f = fopen($file,"rt");
	
	if(!$file)	die("$file cannot be open\n");
	
	$con->sql_query("truncate sap_article");
	
	$line = fgetcsv($f);	// skip first line, it is header
	
	$row_count = 0;
	$failed_count = 0;
	
	while($r = fgetcsv($f)){
		$row_count++;
		print "\r$row_count. . . .";
		
		$sap_article_id = mi($r[1]);
		$mcode = trim($r[4]);
				
		if(!$sap_article_id || !$mcode)	continue;
		
		$con->sql_query("select sku_id from sku_items where mcode=".ms($mcode));
		$si = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$si){
			$failed_count++;
			continue;
		}	
		
		$upd = array();
		$upd['arms_sku_id'] = $si['sku_id'];
		$upd['sap_article_id'] = $sap_article_id;
		
		$con->sql_query("replace into sap_article ".mysql_insert_by_field($upd));
		
	}
	
	print "\nDone, $row_count row updated. $failed_count failed.\n";
}

function check_and_renew_is_parent($sku_id){
	global $con;
	
	if(!$sku_id)	return;
	
	// check whether this sku already have parent
	$con->sql_query("select si.*, uom.fraction
	from sku_items si
	left join uom on uom.id=si.packing_uom_id
	where si.is_parent=1 and sku_id=$sku_id");
	$si = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($si['packing_uom_id'] == 1 || $si['fraction'] == 1) return;	// no problem
		
	$con->sql_query("select si.id
	from sku_items si
	join uom on uom.id=si.packing_uom_id
	where sku_id=".mi($sku_id)." and uom.fraction=1
	order by si.packing_uom_id, si.sku_item_code limit 1");
	$si2 = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$con->sql_query("update sku_items set is_parent=0 where sku_id=$sku_id");
	
	if($si2){	
		$con->sql_query("update sku_items set is_parent=1 where id=".mi($si2['id'])." and sku_id=$sku_id");
		return;
	}
	
	// still not found, simple choose one as parent
	$con->sql_query("update sku_items set is_parent=1 where sku_id=$sku_id order by id limit 1");
}

// php data_bridge.php update_sap_category_mapping
function update_sap_category_mapping(){
	global $con;
	
	print "\nStart update_sap_article_mapping. . .\n";
	
	$con->sql_query("create table if not exists sap_category(
		arms_category_id int primary key,
		sap_category_id char(30) unique
	)");
	
	
	$file = 'sap_category.csv';
	
	$f = fopen($file,"rt");
	
	if(!$file)	die("$file cannot be open\n");
	
	$con->sql_query("truncate sap_category");
	
	$line = fgetcsv($f);	// skip first line, it is header
	
	$row_count = 0;
	$failed_count = 0;
	
	while($r = fgetcsv($f)){
		$row_count++;
		print "\r$row_count. . . .";
		
		$sap_category_id = trim($r[0]);
		$arms_category_id = mi($r[1]);
				
		if(!$sap_category_id || !$arms_category_id)	continue;
		
		$con->sql_query("select id from category where id=$arms_category_id");
		$cat = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$cat){
			$failed_count++;
			continue;
		}	
		
		$upd = array();
		$upd['arms_category_id'] = $arms_category_id;
		$upd['sap_category_id'] = $sap_category_id;
		
		$con->sql_query("replace into sap_category ".mysql_insert_by_field($upd));
		
	}
	
	print "\nDone, $row_count row updated. $failed_count failed.\n";
}

function get_arms_category_id_from_sap_category_id($sap_category_id){
	global $con;
	
	if(!$sap_category_id)	return 0;
	$con->sql_query("select arms_category_id from sap_category where sap_category_id=".ms($sap_category_id));
	$arms_category_id = mi($con->sql_fetchfield(0));
	$con->sql_freeresult();
	
	return $arms_category_id;
}

// php data_bridge.php update_sap_sku_price
function update_sap_sku_price(){
	global $con, $con2;
	
	print "\nStart update_sap_sku_price. . .\n";
	if(!$con2)	connect_sap();	// connect sap

	$branch_list = array();
	$con->sql_query("select id,code from branch");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$ret = $con2->query("select * from POSDM_SKU_PRICE where CHANGE_FLAG=1 order by ID");	// remember to order by branch id, vendor id, valid from
	
	$fp_bid = 1;
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count. . .";
		
		$sap_bid = mi(substr(trim($r['SITE']), -2));
		
		if(!$sap_bid || !isset($branch_list[$sap_bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $sap_bid.\n";
			continue;
		}
		
		$mcode = trim($r['GTIN']);
		if(!$mcode){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: GTIN is empty.\n";
			continue;
		}
		
		$sid = get_sku_item_id_by_gtin($mcode);
			
		if(!$sid){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
			continue;
		}
			
		$q1 = $con->sql_query("select si.*, 
							   ifnull(sip.trade_discount_code, sku.default_trade_discount_code) as trade_discount_code,
							   ifnull(sip.price, si.selling_price) as latest_selling_price,
							   ifnull(sic.grn_cost, si.cost_price) as latest_cost_price
							   from sku_items si
							   left join sku on sku.id=si.sku_id
							   left join sku_items_price sip on sip.branch_id=".mi($sap_bid)." and si.id=sip.sku_item_id
							   left join sku_items_cost sic on sic.branch_id=".mi($sap_bid)." and si.id=sic.sku_item_id
							   where si.id=$sid");
		$si_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		if(!$si_info){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid sku_item_id#$sid\n";
			continue;
		}
		
		$sap_min_qty = 0;
		$type = strtolower(trim($r['TYPE']));
		if($type == 'member1'){ // is member1
			$sap_member_type = "member1";
		}elseif($type == 'member2'){ // is member2
			$sap_member_type = "member2";
		}elseif($type == 'member3'){ // is member3
			$sap_member_type = "member3";
		}elseif($type == 'qprice'){	//qprice
			$sap_member_type = "qprice";
			$sap_min_qty = mf($r['MIN_QTY']);
			
			if($sap_min_qty<=0){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: qprice min_qty cannot less than or zero, min_qty#$sap_min_qty\n";
				continue;
			}
		}elseif($type == 'normal'){
			$sap_member_type = "normal";
		}else{
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid type#$type\n";
			continue;
		}
			
		/*$sap_min_qty = mf($r['MIN_QTY']);
		$sap_member_type = "";
		if(!$sap_min_qty){
			if($r['type'] == 1){ // is member1
				$sap_member_type = "member1";
			}elseif($r['type'] == 2){ // is member2
				$sap_member_type = "member2";
			}elseif($r['type'] == 3){ // is member3
				$sap_member_type = "member3";
			}else{
				$sap_member_type = "normal";
			}
		}else{
			$sap_member_type = "qprice";
		}*/
		
		$sap_hrs = mi($r['VALID_FROM_HOUR']);
		$sap_mins = mi($r['VALID_FROM_MIN']);
		$sap_date = $r['VALID_FROM_DATE'];
		$sap_full_date = date("Y-m-d H:i:s", strtotime("+$sap_hrs hour $sap_mins minute", strtotime($sap_date)));
		$sap_price = mf($r['AMOUNT']);
		$qprice_info_existed = array();
	
		if(strtotime($sap_full_date) <= time()){ // store into price, mprice or qprice
			$need_add = false;
			
			if($sap_member_type == "qprice"){ // store into qprice
				// check whether it is the latest row
				$con->sql_query("select * from sku_items_qprice where branch_id=$sap_bid and sku_item_id=$sid and min_qty=".ms($sap_min_qty));
				$curr_qprice = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$need_add = false;
				if(!$curr_qprice){	// no qprice is set
					$need_add = true;
				}else{
					// this row is latest then the last update qprice timestamp
					if(strtotime($curr_qprice['last_update'])<strtotime($sap_full_date)){
						if($curr_qprice['price'] != $sap_price){
							$need_add = true;
						}
					}
				}
				
				if($need_add){
					// insert into qprice table
					$ins = array();
					$ins['branch_id'] = $sap_bid;
					$ins['sku_item_id'] = $sid;
					$ins['min_qty'] = $sap_min_qty;
					$ins['price'] = $sap_price;
					$ins['last_update'] = $sap_full_date;
					
					$con->sql_query("replace into sku_items_qprice ".mysql_insert_by_field($ins));
					
					// insert into qprice history table
					$ins = array();
					$ins['branch_id'] = $sap_bid;
					$ins['sku_item_id'] = $sid;
					$ins['min_qty'] = $sap_min_qty;
					$ins['price'] = $sap_price;
					$ins['added'] = $sap_full_date;
					$ins['user_id'] = 1;
					
					$con->sql_query("replace into sku_items_qprice_history ".mysql_insert_by_field($ins));
					
					print "Updated qprice, array info:\n";
					print_r($ins);
				}else{
					print "\nRow ID ".$r['ID']." ignored: the price timestamp is too old, data time#$sap_full_date, current last update time#$curr_qprice[last_update] or price is same, price to update#$sap_price, current qprice $curr_qprice[price]\n";
				}
			}else{ // store into price or mprice
				if($sap_member_type != "normal"){ // is member price
					// check whether it is the latest row
					$con->sql_query("select * from sku_items_mprice where branch_id=$sap_bid and sku_item_id=$sid and type=".ms($sap_member_type));
					$curr_mprice = $con->sql_fetchassoc();
					$con->sql_freeresult();
				
					$need_add = false;
					if(!$curr_mprice){	// no qprice is set
						$need_add = true;
					}else{
						// this row is latest then the last update qprice timestamp
						if(strtotime($curr_mprice['last_update'])<strtotime($sap_full_date)){
							if($curr_mprice['price'] != $sap_price){
								$need_add = true;
							}
						}
					}
				
					if($need_add){
						// insert into mprice table
						$ins = array();
						$ins['branch_id'] = $sap_bid;
						$ins['sku_item_id'] = $sid;
						$ins['type'] = $sap_member_type;
						$ins['last_update'] = $sap_full_date;
						$ins['price'] = $sap_price;
						$ins['trade_discount_code'] = $si_info['trade_discount_code'];
						
						$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($ins));
						
						// insert into mprice history table
						$ins = array();
						$ins['branch_id'] = $sap_bid;
						$ins['sku_item_id'] = $sid;
						$ins['type'] = $sap_member_type;
						$ins['added'] = $sap_full_date;
						$ins['price'] = $sap_price;
						$ins['user_id'] = 1;
						$ins['trade_discount_code'] = $si_info['trade_discount_code'];
	
						$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($ins));
						
						print "Updated mprice, array info:\n";
						print_r($ins);
					}else{
						print "\nRow ID ".$r['ID']." ignored: the price timestamp is too old, data time#$sap_full_date, current last update time#$curr_mprice[last_update] or price is same, price to update#$sap_price, current mprice $curr_mprice[price]\n";
					}
				}else{ // is normal price
					// check whether it is the latest row
					$con->sql_query("select * from sku_items_price where branch_id=$sap_bid and sku_item_id=$sid");
					$curr_price = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					$need_add = false;
					if(!$curr_price){	// no qprice is set
						$need_add = true;
					}else{
						// this row is latest then the last update qprice timestamp
						if(strtotime($curr_price['last_update'])<strtotime($sap_full_date)){
							if($curr_price['price'] != $sap_price){
								$need_add = true;
							}
						}
					}
					
					if($need_add){
						// get latest cost
						/*$q1 = $con->sql_query("select grn_cost from sku_items_cost where sku_item_id = $sid and branch_id = ".mi($sap_bid));
						$tmp = $con->sql_fetchassoc($q1);
						$con->sql_freeresult();
						
						if($tmp){
							$cost = $tmp['grn_cost'];
						}else{
							$cost = $si_info['cost_price'];
						}*/
						
	
						// insert into price table
						$ins = array();
						$ins['branch_id'] = $sap_bid;
						$ins['sku_item_id'] = $sid;
						$ins['last_update'] = $sap_full_date;
						$ins['price'] = $sap_price;
						$ins['cost'] = $si_info['latest_cost_price'];
						$ins['trade_discount_code'] = $si_info['trade_discount_code'];
						
						$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($ins));
						
						// insert into price history table
						$ins = array();
						$ins['branch_id'] = $sap_bid;
						$ins['sku_item_id'] = $si_info['id'];
						$ins['added'] = $sap_full_date;
						$ins['price'] = $sap_price;
						$ins['cost'] = $si_info['latest_cost_price'];
						$ins['source'] = "SAP";
						$ins['user_id'] = 1;
						$ins['trade_discount_code'] = $si_info['trade_discount_code'];
	
						$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($ins));
						
						print "Updated normal price, array info:\n";
						print_r($ins);
					}else{
						print "\nRow ID ".$r['ID']." ignored: the price timestamp is too old, data time#$sap_full_date, current last update time#$curr_price[last_update] or price is same, price to update#$sap_price, current price $curr_price[price]\n";
					}
				}
			}
			
			if($need_add){
				$upd = array();
				$upd['lastupdate'] = "CURRENT_TIMESTAMP";
				$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($sid));
			}			
		}else{ // store into future price
			// manage future price
			$q1 = $con->sql_query("select * from sku_items_future_price where date = ".ms($sap_date)." and hour = ".mi($sap_hrs)." and minute = ".mi($sap_mins)." and active = 1 and status = 1 and approved = 1 and cron_status = 0 order by id");
			
			$fp_id = 0;
			if($con->sql_numrows($q1) > 0){
				while($r1 = $con->sql_fetchassoc($q1)){
					$effective_branches = unserialize($r1['effective_branches']);
					
					if(is_array($effective_branches) && $effective_branches[$sap_bid] && count($effective_branches)==1){
						//$effective_branches[$sap_bid] = $bid;
						$upd = array();
						//$upd['effective_branches'] = serialize($effective_branches);
						$upd['last_update'] = "CURRENT_TIMESTAMP";
		
						$con->sql_query("update sku_items_future_price set ".mysql_update_by_field($upd)." where branch_id=".mi($r1['branch_id'])." and id=".mi($r1['id']));
						$fp_id = $r1['id'];
						break;
					}
				}
			}
			$con->sql_freeresult($q1);
			
			if(!$fp_id){ // need to create new future price
				$ins = array();
				$ins['branch_id'] = $fp_bid;
				$ins['date'] = $sap_date;
				$ins['hour'] = $sap_hrs;
				$ins['minute'] = $sap_mins;
				$effective_branches = array();
				$effective_branches[$sap_bid] = $sap_bid;
				$ins['effective_branches'] = serialize($effective_branches);
				$ins['active'] = $ins['status'] = $ins['approved'] = 1;
				$ins['user_id'] = 1;
				$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("select max(id) from sku_items_future_price where branch_id = ".mi($fp_bid));
				$ins['id'] = $con->sql_fetchfield(0);
				$ins['id'] += 1;
				$con->sql_freeresult();
				
				$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
				$fp_id=$con->sql_nextid();
			}
			$con->sql_freeresult($q1);
			
			// manage future price items
			// search for old info from normal price, mprice and qprice
			//$sid = $si_info['id'];
			$old_info = array();
			if($sap_member_type == "qprice"){
				if(!$qprice_info_existed[$sid]){
					$q1 = $con->sql_query("select min_qty, price from sku_items_qprice where sku_item_id = ".mi($sid)." and branch_id = ".mi($sap_bid));

					while($r1 = $con->sql_fetchassoc($q1)){
						$old_info[$sap_bid][] = $r1;
					}
					$con->sql_freeresult($q1);
				}
			}elseif($sap_member_type == "normal"){
				$q1 = $con->sql_query("select price, cost, source, ref_id, user_id, trade_discount_code from sku_items_price_history where sku_item_id = ".mi($sid)." and branch_id = ".mi($sap_bid)." order by added desc limit 1");
				$price_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$old_info[$sap_bid] = $price_info;
			}else{
				$q1 = $con->sql_query("select type, price, user_id, trade_discount_code from sku_items_mprice_history where sku_item_id = ".mi($sid)." and branch_id = ".mi($sap_bid)." and type = ".ms($sap_member_type)." order by added desc limit 1");
				$mprice_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				$old_info[$sap_bid] = $mprice_info;
			}
			
			// insert items
			$ins = array();
			$ins['branch_id'] = $fp_bid;
			$ins['fp_id'] = $fp_id;
			$ins['sku_item_id'] = $sid;
			$ins['cost'] = $si_info['latest_cost_price'];
			$ins['selling_price'] = $si_info['latest_selling_price'];
			$ins['type'] = $sap_member_type;
			$ins['trade_discount_code'] = $si_info['trade_discount_code'];
			$ins['min_qty'] = $sap_min_qty;
			$ins['future_selling_price'] = $sap_price;
			$ins['old_info'] = serialize($old_info);
			
			$con->sql_query("select max(id) from sku_items_future_price_items where branch_id = ".mi($fp_bid));
			$ins['id'] = $con->sql_fetchfield(0);
			$ins['id'] += 1;
			$con->sql_freeresult();
			
			$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
			$item_id = $con->sql_nextid();
			
			print "Updated future price, array info:\n";
			print_r($ins);
		}
		
		print "Row ID $r[ID] mark as processed.\n";
		$success = $con2->exec("update POSDM_SKU_PRICE set CHANGE_FLAG=0 where ID=".ms($r['ID']));
	}
	
	$ret->closeCursor();
		
	print "\nFinish update_sap_sku_price. $row_count item(s) processed. $failed_count row failed.\n";
}

// php data_bridge.php update_vendor_sku_group > update_vendor_sku_group.log
function update_vendor_sku_group(){
	global $con, $con2;
	
	print "\nStart update_vendor_sku_group. . .\n";
	
	if(!$con2)	connect_sap();	// connect sap
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_SKU_GROUP where CHANGE_FLAG=1 order by ID");	// remember to order by branch id, vendor id, date_to
	
	$branch_list = array();
	$con->sql_query("select id,code from branch");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count, ID $r[ID]. . .";
		
		$bid = mi(substr(trim($r['BRANCH_CODE']), -2));
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$mcode = trim($r['GTIN_NO']);
		$valid_from = trim($r['VALID_FROM']);
		$valid_to = trim($r['VALID_TO']);
		$timestamp = trim($r['TIMESTAMP']);
		
		check_and_create_vendor_portal_info($bid, $vendor_id);
		
		$con->sql_query("select * from vendor_portal_info where vendor_id=$vendor_id");
		$vp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$vp){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: vendor_portal_info not found for vendor id#$vendor_id.\n";
			continue;
		}
		
		$vp['sku_group_info'] = unserialize($vp['sku_group_info']);
		if(!$vp['sku_group_info'] || !is_array($vp['sku_group_info']))	$vp['sku_group_info'] = array();
		
		if(!$vp['sku_group_info'][$bid]){	// this branch dun hv sku group
			// get vendor info
			$con->sql_query("select id,code,description from vendor where id=$vendor_id");
			$vendor_info = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			// create new sku_group
			// Insert into sku group
			$upd = array();
			$upd['branch_id'] = 1;
			$upd['user_id'] = 1;
			$upd['description'] = $r['SKU_CATEGORY'];
            $upd['added'] = $upd['last_update'] = $timestamp;
			$upd['share_with'] = array("48");	// user_id = 48 (Leong), must use as string
			
			$code_like = 'S'.trim($vendor_info['code']);
			
			$con->sql_query("select max(code) from sku_group where code like ".ms($code_like.'%'));
			$max_code = trim($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			if($max_code){
				$sku_group_code = str_replace('S', '' , $max_code);
				$sku_group_code++;
				$sku_group_code = 'S'.$sku_group_code;		
			}else{
				$sku_group_code = $code_like."1";
			}
			
			// check whether this group code exists
			$con->sql_query("select code from sku_group where code=".ms($sku_group_code));
			$code_exists = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($code_exists){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: sku group code '$sku_group_code' already exists, cannot be create.\n";
				continue;
			}
			
			$upd['code'] = $sku_group_code;
			$upd['share_with'] = serialize($upd['share_with']);
			
			$max_failed_attemp = 5;
			$failed_attemp = -1;
			
			do {
				$failed_attemp++;
				// get new promotion ID, to avoid replica bugs
				$con->sql_query("select max(sku_group_id) as max_id from sku_group where branch_id=".$upd['branch_id']);
				$max_id = mi($con->sql_fetchfield(0))+1;
				$con->sql_freeresult();

				$upd['sku_group_id'] = $max_id;
				$sql = "insert into sku_group " . mysql_insert_by_field($upd);
				$q_success = $con->sql_query($sql, false,false);
				
				/*if($failed_attemp<$max_failed_attemp){
					$q_success = $con->sql_query($sql, false,false);
				}else{
					$q_success = $con->sql_query($sql); // attemp insert more than 5 time, maybe is other error, stop unlimited loop
				}*/
				if($failed_attemp >= $max_failed_attemp && !$q_success){
					break;
				}
			} while (!$q_success);   // insert until success
			if(!$q_success){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: create new sku group failed. trying to create as $upd[sku_group_id]\n";
				continue;
			}
			
			$vp['sku_group_info'][$bid] = $upd['branch_id']."|".$upd['sku_group_id'];
			
			// update back to vendor_portal_info
			$upd = array();
			$upd['sku_group_info'] = serialize($vp['sku_group_info']);
			
			$con->sql_query("update vendor_portal_info set ".mysql_update_by_field($upd)." where vendor_id=$vendor_id");
		}
		
		list($sku_group_bid, $sku_group_id) = explode("|", $vp['sku_group_info'][$bid]);
		if(!$sku_group_bid || !$sku_group_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: cannot found sku group id, sku_group_bid#$sku_group_bid, sku_group_id#$sku_group_id\n";
			continue;
		}
		
		if($mcode){
			$params = array();
			$sid = get_sku_item_id_by_gtin($mcode, $params);
			
			if(!$sid){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
				continue;
			}
			$sku_item_code = $params['info']['sku_item_code'];
			
			// check whether this item alrdy exists in sku_group_item
			$con->sql_query("select * from sku_group_item where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_code=".ms($sku_item_code));
			$sgi = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$sgi){	// create new item
				$upd = array();
				$upd['branch_id'] = $sku_group_bid;
				$upd['sku_group_id'] = $sku_group_id;
				$upd['user_id'] = 1;
				$upd['sku_item_code'] = $sku_item_code;
				$upd['added_by'] = 1;
				$upd['added_timestamp'] = $timestamp;
				
				$con->sql_query("insert into sku_group_item ".mysql_insert_by_field($upd));
			}
			
			if($valid_from && $valid_to){
				// find the exactly same row
				$con->sql_query("select * from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid and from_date=".ms($valid_from)." and to_date=".ms($valid_to));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				if(!$tmp){
					// find the row got overlap
					$q1 = $con->sql_query("select * from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid and (".ms($valid_from)." between from_date and to_date or ".ms($valid_to)." between from_date and to_date or from_date between ".ms($valid_from)." and ".ms($valid_to)." or to_date between ".ms($valid_from)." and ".ms($valid_to).")");
					while($tmp = $con->sql_fetchassoc($q1)){
						$con->sql_query("delete from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid and from_date=".ms($tmp['from_date'])." and to_date=".ms($tmp['to_date']));
					}
					
					$con->sql_freeresult($q1);
					
					/*if($tmp){
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Date From#$valid_from, To#$valid_to got overlap.\n";
						continue;
					}*/
					
					$upd = array();
					$upd['branch_id'] = $sku_group_bid;
					$upd['sku_group_id'] = $sku_group_id;
					$upd['sku_item_id'] = $sid;
					$upd['from_date'] = $valid_from;
					$upd['to_date'] = $valid_to;
					
					$con->sql_query("insert into sku_group_vp_date_control ".mysql_insert_by_field($upd));
				}				
			}
		}
		
		print "\nID $r[ID] success\n";
		
		$success = $con2->exec("update POSDM_SKU_RJ_SKU_GROUP set CHANGE_FLAG=0 where ID=".ms($r['ID']));
	}
	
	$ret->closeCursor();
		
	print "\nFinish update_vendor_sku_group. $row_count item(s) processed. $failed_count row failed.\n";
	
	// check deleted data
	print "\nChecking Deleted data for POSDM_SKU_RJ_SKU_GROUP_DEL.\n";
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_SKU_GROUP_DEL where CHANGE_FLAG=1 order by ID");
	$row_count = 0;
	$failed_count = 0;

	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count, ID $r[ID]. . .";
		
		$bid = mi(substr(trim($r['BRANCH_CODE']), -2));
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$mcode = trim($r['GTIN_NO']);
		$valid_from = trim($r['VALID_FROM']);
		$valid_to = trim($r['VALID_TO']);
		$timestamp = trim($r['TIMESTAMP']);
		
		if(!$mcode && !$valid_from && !$valid_to){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: No MCode, Valid From/To.\n";
			continue;
		}
		
		$con->sql_query("select * from vendor_portal_info where vendor_id=$vendor_id");
		$vp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if(!$vp){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: vendor_portal_info not found for vendor id#$vendor_id.\n";
			continue;
		}
		
		$vp['sku_group_info'] = unserialize($vp['sku_group_info']);
		if(!$vp['sku_group_info'] || !is_array($vp['sku_group_info']) || !$vp['sku_group_info'][$bid]){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: vendor_portal_info not found for vendor id#$vendor_id.\n";
			continue;
		}
		
		list($sku_group_bid, $sku_group_id) = explode("|", $vp['sku_group_info'][$bid]);
		if(!$sku_group_bid || !$sku_group_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: cannot found sku group id, sku_group_bid#$sku_group_bid, sku_group_id#$sku_group_id\n";
			continue;
		}
		
		$params = array();
		$sid = get_sku_item_id_by_gtin($mcode, $params);
		
		if(!$sid){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
			continue;
		}
		$sku_item_code = $params['info']['sku_item_code'];
		
		$con->sql_query("select * from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid and from_date=".ms($valid_from)." and to_date=".ms($valid_to));
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($tmp){
			$con->sql_query("delete from sku_group_vp_date_control where branch_id=$sku_group_bid and sku_group_id=$sku_group_id and sku_item_id=$sid and from_date=".ms($valid_from)." and to_date=".ms($valid_to));
		}
		
		$success = $con2->exec("update POSDM_SKU_RJ_SKU_GROUP_DEL set CHANGE_FLAG=0 where ID=".ms($r['ID']));
	}
	print "\nFinish Deleted data for POSDM_SKU_RJ_SKU_GROUP_DEL. $row_count row processed, $failed_count row failed.\n";
}

// php data_bridge.php update_vendor_portal_profit
function update_vendor_portal_profit(){
	global $con, $con2;
	
	print "\nStart update_vendor_portal_profit. . .\n";
	
	function update_vendor_portal_profit_item($bid, $vendor_id, $item){
		global $con, $con2;
		
		if(!$bid || !$vendor_id || !$item)	return false;
		
		check_and_create_vendor_portal_info($bid, $vendor_id);
		
		print_r($item);
		
		$upd = array();
		$upd['sales_report_profit_by_date'] = $item;
		usort($upd['sales_report_profit_by_date'], "sort_report_profit");
		
		/*$upd = array();
		$upd['sales_report_profit_by_date'] = array();
		
		foreach($item as $r){
			$upd['sales_report_profit_by_date'][] = $r;
		}*/
		
		$upd['sales_report_profit_by_date'] = serialize($upd['sales_report_profit_by_date']);
		
		$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($upd)." where branch_id=$bid and vendor_id=$vendor_id");
	}
	
	if(!$con2)	connect_sap();	// connect sap
	
	$branch_list = array();
	$con->sql_query("select id,code from branch");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_LICENSEE where CHANGE_FLAG=1 order by SITE, SAP_VENDOR_CODE, VALID_TO, ID");	// remember to order by branch id, vendor id, date_to
	
	$sales_report_profit_by_date = array();
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count, ID $r[ID]. . .";
		
		$bid = mi(substr(trim($r['SITE']), -2));
		$replace_array_key = -1;
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$date_to = $r['VALID_TO'];	// valid to
		$rate_per = mf($r['RATES']);	// rate %
		$exceptional_rate = mf($r['EXC_RATES']);	// rate for exceptional condition
		$mcode = trim($r['GTIN_EAN']);
		
		//if($last_bid && $last_vendor_id && $last_bid != $bid && $last_vendor_id != $vendor_id && $sales_report_profit_by_date){
		//	update_vendor_portal_profit_item($last_bid, $last_vendor_id, $sales_report_profit_by_date);
		//	$sales_report_profit_by_date = array();
		//}
		
		$con->sql_query("select sales_report_profit_by_date from vendor_portal_branch_info where branch_id=$bid and vendor_id=$vendor_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sales_report_profit_by_date = unserialize($tmp['sales_report_profit_by_date']);
		if(!$sales_report_profit_by_date || !is_array($sales_report_profit_by_date))	$sales_report_profit_by_date = array();
		
		$upd = array();	
		if($sales_report_profit_by_date){
			foreach($sales_report_profit_by_date as $key=>$tmp){
				if($tmp['date_to'] == $date_to){
					$replace_array_key = $key;
					$upd = $tmp;
					break;
				}
			}
		}
		if($replace_array_key<0)	$replace_array_key = count($sales_report_profit_by_date);
		
		$upd['date_to'] = $date_to;	// valid to
		$upd['profit_per'] = $rate_per;	// rate %
		
		if($mcode){	// exceptional condition for SKU
			$sid = get_sku_item_id_by_gtin($mcode);
			if(!$sid){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
				continue;
			}
			
			$upd2 = array();
			$upd2['type'] = 'SKU';
			$upd2['value'] = $sid;
			$upd2['per'] = $exceptional_rate;
					
			$position = -1;
			if($upd['profit_per_by_type']){
				foreach($upd['profit_per_by_type'] as $tmp_position => $tmp){
					if($tmp['type'] == 'SKU' && $tmp['value'] == $sid){
						$position = $tmp_position;
						break;
					}
				}
			}else{
				$upd['profit_per_by_type'] = array();
			}
			
			if($position < 0){
				$position = count($upd['profit_per_by_type'])+1;
			}
			
			$upd['profit_per_by_type'][$position] = $upd2;
		}elseif($r['SKU_Cat7'] || $r['SKU_Cat6'] || $r['SKU_Cat5'] || $r['SKU_Cat4'] || $r['SKU_Cat3'] || $r['SKU_Cat2'] || $r['SKU_Cat1']){
			$cat_list = array('SKU_Cat7', 'SKU_Cat6', 'SKU_Cat5', 'SKU_Cat4', 'SKU_Cat3', 'SKU_Cat2', 'SKU_Cat1');
			$skip = false;
			foreach($cat_list as $sap_cat_col){
				$sap_cat_id = trim($r[$sap_cat_col]);
				
				if($sap_cat_id){
					$cat_id = get_arms_category_id_from_sap_category_id($sap_cat_id);
					if(!$cat_id){
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid SAP Category ID $sap_cat_id.\n";
						$skip = true;
						break;
					}
					
					$cat_info = get_category_info($cat_id);
			
					if($cat_info){
						$upd2 = array();
						$upd2['type'] = 'CATEGORY';
						$upd2['value'] = $cat_id;
						$upd2['per'] = $exceptional_rate;
												
						$position = -1;
						if($upd['profit_per_by_type']){
							foreach($upd['profit_per_by_type'] as $tmp_position => $tmp){
								if($tmp['type'] == 'CATEGORY' && $tmp['value'] == $cat_id){
									$position = $tmp_position;
									break;
								}
							}
						}else{
							$upd['profit_per_by_type'] = array();
						}
						
						if($position < 0){
							$position = count($upd['profit_per_by_type'])+1;
						}
			
						$upd['profit_per_by_type'][$position] = $upd2;
						break;
					}else{
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid ARMS Category ID $cat_id cannot get info.\n";
						$skip = true;
						break;
					}
				}
			}
			
			if($skip)	continue;
			
		}//else{
		//	$failed_count++;
		//	print "\nRow ID ".$r['ID']." failed: Invalid data.\n";
		//	continue;
		//}
		
		$sales_report_profit_by_date[$replace_array_key] = $upd;
		
		//$last_bid = $bid;
		//$last_vendor_id = $vendor_id;
		
		print "\nID $r[ID] success, array info:\n";
		update_vendor_portal_profit_item($bid, $vendor_id, $sales_report_profit_by_date);
		
		$success = $con2->exec("update POSDM_SKU_RJ_LICENSEE set CHANGE_FLAG=0 where ID=".ms($r['ID']));
	}
	$ret->closeCursor();
	
	/*if($last_bid && $last_vendor_id && $sales_report_profit_by_date){
		update_vendor_portal_profit_item($last_bid, $last_vendor_id, $sales_report_profit_by_date);
		$sales_report_profit_by_date = array();
	}*/
	
	print "\nFinish update_vendor_portal_profit. $row_count row processed, $failed_count row failed.\n";
	
	// check deleted data
	print "\nChecking Deleted data for sales_report_profit_by_date.\n";
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_LICENSEE_DEL where CHANGE_FLAG=1 order by SITE, SAP_VENDOR_CODE, VALID_TO, ID");	// remember to order by branch id, vendor id, date_to
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count, ID $r[ID]. . .";
		
		$bid = mi(substr(trim($r['SITE']), -2));
		
		$r['SKU_Cat7'] = trim($r['SKU_Cat7']);
		$r['SKU_Cat6'] = trim($r['SKU_Cat6']);
		$r['SKU_Cat5'] = trim($r['SKU_Cat5']);
		$r['SKU_Cat4'] = trim($r['SKU_Cat4']);
		$r['SKU_Cat3'] = trim($r['SKU_Cat3']);
		$r['SKU_Cat2'] = trim($r['SKU_Cat2']);
		$r['SKU_Cat1'] = trim($r['SKU_Cat1']);
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$date_to = $r['VALID_TO'];	// valid to
		$rate_per = mf($r['RATES']);	// rate %
		$exceptional_rate = mf($r['EXC_RATES']);	// rate for exceptional condition
		$mcode = trim($r['GTIN_EAN']);
		
		$con->sql_query("select sales_report_profit_by_date from vendor_portal_branch_info where branch_id=$bid and vendor_id=$vendor_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$sales_report_profit_by_date = unserialize($tmp['sales_report_profit_by_date']);
		if(!$sales_report_profit_by_date){
			$failed_count++;
			print "\nRow ID $r[ID], No data in sales_report_profit_by_date, Branch ID#$bid, Vendor_id#$vendor_id.\n";
			continue;
		}
		
		$replace_array_key = -1;
		$upd = array();
		foreach($sales_report_profit_by_date as $key => $tmp){
			if($tmp['date_to'] == $date_to){
				$replace_array_key = $key;
				$upd = $tmp;
			}
		}
		
		if($replace_array_key < 0){
			$failed_count++;
			print "\nRow ID $r[ID], Date To Not Found, Branch ID#$bid, Vendor_id#$vendor_id, Date To#$date_to.\n";
			continue;
		}
		
		if($mcode){	// exceptional condition for SKU
			print "Delete by GTIN $mcode.\n";
			$sid = get_sku_item_id_by_gtin($mcode);
			if(!$sid){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
				continue;
			}
			
			// check for exceptional type
			if(!$upd['profit_per_by_type']){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Data have no profit by type.\n";
				continue;
			}
			
			$got_update = false;
			foreach($upd['profit_per_by_type'] as $key=>$upd2){
				if($upd2['type'] == 'SKU' && $upd2['value'] == $sid){
					unset($upd['profit_per_by_type'][$key]);
					$got_update = true;
					break;
				}
			}
			
			if(!$got_update){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: profit by type data not match for SKU ITEM ID#$sid.\n";
				continue;
			}
			
			$sales_report_profit_by_date[$replace_array_key] = $upd;
		}elseif($r['SKU_Cat7'] || $r['SKU_Cat6'] || $r['SKU_Cat5'] || $r['SKU_Cat4'] || $r['SKU_Cat3'] || $r['SKU_Cat2'] || $r['SKU_Cat1']){
			print "Delete by category.\n";
			if($r['SKU_Cat7'])	print "Delete cat7: ".$r['SKU_Cat7']."\n";
			if($r['SKU_Cat6'])	print "Delete cat6: ".$r['SKU_Cat6']."\n";
			if($r['SKU_Cat5'])	print "Delete cat5: ".$r['SKU_Cat5']."\n";
			if($r['SKU_Cat4'])	print "Delete cat4: ".$r['SKU_Cat4']."\n";
			if($r['SKU_Cat3'])	print "Delete cat3: ".$r['SKU_Cat3']."\n";
			if($r['SKU_Cat2'])	print "Delete cat2: ".$r['SKU_Cat2']."\n";
			if($r['SKU_Cat1'])	print "Delete cat1: ".$r['SKU_Cat1']."\n";
			
			// check for exceptional type
			if(!$upd['profit_per_by_type']){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Data have no profit by type.\n";
				continue;
			}
			
			$cat_list = array('SKU_Cat7', 'SKU_Cat6', 'SKU_Cat5', 'SKU_Cat4', 'SKU_Cat3', 'SKU_Cat2', 'SKU_Cat1');
			$skip = false;
			foreach($cat_list as $sap_cat_col){
				$sap_cat_id = trim($r[$sap_cat_col]);
				
				if($sap_cat_id){
					$cat_id = get_arms_category_id_from_sap_category_id($sap_cat_id);
					if(!$cat_id){
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid SAP Category ID $sap_cat_id.\n";
						$skip = true;
						break;
					}
					
					$cat_info = get_category_info($cat_id);
			
					if($cat_info){
						$got_update = false;
						foreach($upd['profit_per_by_type'] as $key=>$upd2){
							if($upd2['type'] == 'CATEGORY' && $upd2['value'] == $cat_id){
								unset($upd['profit_per_by_type'][$key]);
								$got_update = true;
								break;
							}
						}
						
						if(!$got_update){
							$failed_count++;
							print "\nRow ID ".$r['ID']." failed: profit by type data not match for CATEGORY ID#$cat_id.\n";
							$skip = true;
							break;
						}
						break;
					}else{
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid ARMS Category ID $cat_id cannot get info.\n";
						$skip = true;
						break;
					}
				}
			}
			
			if($skip)	continue;
			
			$sales_report_profit_by_date[$replace_array_key] = $upd;
			
		}else{
			unset($sales_report_profit_by_date[$replace_array_key]);
		}
		
		$tmp = array();
		$tmp['sales_report_profit_by_date'] = serialize($sales_report_profit_by_date);
		$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($tmp)." where branch_id=$bid and vendor_id=$vendor_id");
		
		$success = $con2->exec("update POSDM_SKU_RJ_LICENSEE_DEL set CHANGE_FLAG=0 where ID=".ms($r['ID']));
		print "Done.\n";
	}
	$ret->closeCursor();
	
	print "\nFinish delete data for sales_report_profit_by_date. $row_count row processed, $failed_count row failed.\n";
}

// php data_bridge.php update_vendor_portal_bonus
function update_vendor_portal_bonus(){
	global $con, $con2;
	
	print "\nStart update_vendor_portal_bonus. . .\n";
	
	function update_vendor_portal_bonus_item($bid, $vendor_id, $item){
		global $con, $con2;
		
		if(!$bid || !$vendor_id || !$item)	return false;
		
		check_and_create_vendor_portal_info($bid, $vendor_id);
		
		print_r($item);
		
		$upd = array();
		$upd['sales_bonus_by_step'] = $item;
		
		if($upd['sales_bonus_by_step']){
			uksort($upd['sales_bonus_by_step'], "sort_report_bonus_y");	// sort by year
			
			foreach($upd['sales_bonus_by_step'] as $y => $m_bonus_list){
				uksort($upd['sales_bonus_by_step'][$y], "sort_report_bonus_m");	// sort by month
				
				foreach($upd['sales_bonus_by_step'][$y] as $m => $bonus_list){
					uasort($upd['sales_bonus_by_step'][$y][$m], "sort_report_bonus_amt");	// sort by amt
				}
			}
		}
		
			
		$upd['sales_bonus_by_step'] = serialize(convert_array_string_value($upd['sales_bonus_by_step']));
		
		$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($upd)." where branch_id=$bid and vendor_id=$vendor_id");
	}
	
	if(!$con2)	connect_sap();	// connect sap

	$branch_list = array();
	$con->sql_query("select id,code from branch");
	while($r = $con->sql_fetchassoc()){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_SALES_TIER where CHANGE_FLAG=1 order by SITE, SAP_VENDOR_CODE, ID");	// remember to order by branch id, vendor id, valid from
	
	$sales_bonus_by_step = array();
	
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count. . .";
		
		$replace_array_key = -1;
		$bid = mi(substr(trim($r['SITE']), -2));
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$valid_from = trim($r['VALID_FROM']);	// valid from
		$amt_from = round(mf($r['AMOUNT_FROM']),2);	// sales target from
		$bonus_per = mf($r['RATES']);	// rate %
		$exceptional_rate = mf($r['EXC_RATES']);	// rate for exceptional condition
		$mcode = trim($r['GTIN_EAN']);
		
		$y = mi(date("Y", strtotime($valid_from)));
		$m = mi(date("m", strtotime($valid_from)));
		
		$con->sql_query("select sales_bonus_by_step from vendor_portal_branch_info where branch_id=$bid and vendor_id=$vendor_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		$sales_bonus_by_step = unserialize($tmp['sales_bonus_by_step']);
		if(!$sales_bonus_by_step || !is_array($sales_bonus_by_step))	$sales_bonus_by_step = array();
		
		$upd = array();	
		if($sales_bonus_by_step[$y][$m]){
			foreach($sales_bonus_by_step[$y][$m] as $key=>$tmp){
				if($tmp['amt_from'] == $amt_from){
					$replace_array_key = $key;
					$upd = $tmp;
					break;
				}
			}
		}else	$sales_bonus_by_step[$y][$m] = array();
		if($replace_array_key<0)	$replace_array_key = count($sales_bonus_by_step[$y][$m]);
		
		//if($last_bid && $last_vendor_id && $last_bid != $bid && $last_vendor_id != $vendor_id && $sales_bonus_by_step){
		//	update_vendor_portal_bonus_item($last_bid, $last_vendor_id, $sales_bonus_by_step);
		//	$sales_bonus_by_step = array();
		//}
		
		//if(isset($sales_bonus_by_step[$y][$m])){
		//	$upd = $sales_bonus_by_step[$y][$m];
		//}else{
		//	$upd = array();
		//}
		
		$upd['amt_from'] = $amt_from;	// amt from
		$upd['bonus_per'] = $bonus_per;	// bonus %
		
		////// currently no bonus by item ////////
		/*if($mcode){	// exceptional condition for SKU
			$sid = get_sku_item_id_by_gtin($mcode);
			if(!$sid){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Invalid GTIN, $mcode.\n";
				continue;
			}
			
			$upd2 = array();
			$upd2['type'] = 'SKU';
			$upd2['value'] = $sid;
			$upd2['per'] = $exceptional_rate;
			
			if(!$upd['bonus_per_by_type'])	$upd['bonus_per_by_type'] = array();
			$position = count($upd['bonus_per_by_type'])+1;
			
			$position = -1;
			if($upd['bonus_per_by_type']){
				foreach($upd['bonus_per_by_type'] as $tmp_position as $tmp){
					if($tmp['type'] == 'SKU' && $tmp['value'] == $sid){
						$position = $tmp_position;
						break;
					}
				}
			}else{
				$upd['bonus_per_by_type'] = array();
			}
			
			if($position < 0){
				$position = count($upd['bonus_per_by_type'])+1;
			}
			
			$upd['bonus_per_by_type'][$position] = $upd2;
		}else*/
		
		if($r['SKU_CAT4'] || $r['SKU_CAT3'] || $r['SKU_CAT2'] || $r['SKU_CAT1']){	// exceptional condition for CATEGORY
			$cat_id = $r['cat5'];
			$cat_info = get_category_info($cat_id);
			
			if($cat_info){
				$upd2 = array();
				$upd2['type'] = 'CATEGORY';
				$upd2['value'] = $cat_id;
				$upd2['per'] = $exceptional_rate;
				
				if(!$upd['bonus_per_by_type'])	$upd['bonus_per_by_type'] = array();
				$position = count($upd['bonus_per_by_type'])+1;
				
				$upd['bonus_per_by_type'][$position] = $upd2;
			}
			
			$cat_list = array('SKU_CAT4', 'SKU_CAT3', 'SKU_CAT2', 'SKU_CAT1');
			$skip = false;
			foreach($cat_list as $sap_cat_col){
				$sap_cat_id = trim($r[$sap_cat_col]);
				
				if($sap_cat_id){
					$cat_id = get_arms_category_id_from_sap_category_id($sap_cat_id);
					if(!$cat_id){
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid SAP Category ID $sap_cat_id.\n";
						$skip = true;
						break;
					}
					
					$cat_info = get_category_info($cat_id);
			
					if($cat_info){
						$upd2 = array();
						$upd2['type'] = 'CATEGORY';
						$upd2['value'] = $cat_id;
						$upd2['per'] = $exceptional_rate;
																		
						$position = -1;
						if($upd['bonus_per_by_type']){
							foreach($upd['bonus_per_by_type'] as $tmp_position => $tmp){
								if($tmp['type'] == 'CATEGORY' && $tmp['value'] == $cat_id){
									$position = $tmp_position;
									break;
								}
							}
						}else{
							$upd['bonus_per_by_type'] = array();
						}
						
						if($position < 0){
							$position = count($upd['bonus_per_by_type'])+1;
						}
			
						$upd['bonus_per_by_type'][$position] = $upd2;
						break;
					}else{
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid ARMS Category ID $cat_id cannot get info.\n";
						$skip = true;
						break;
					}
				}
			}
			
			if($skip)	continue;
		}
		
		$sales_bonus_by_step[$y][$m][$replace_array_key] = $upd;
		
		//$last_bid = $bid;
		//$last_vendor_id = $vendor_id;
		
		print "\nID $r[ID] success, array info:\n";
		update_vendor_portal_bonus_item($bid, $vendor_id, $sales_bonus_by_step);
		
		$success = $con2->exec("update POSDM_SKU_RJ_SALES_TIER set CHANGE_FLAG=0 where ID=".ms($r['ID']));
		
		
	}
	
	$ret->closeCursor();
	
	//if($last_bid && $last_vendor_id && $sales_bonus_by_step){
	//	update_vendor_portal_bonus_item($last_bid, $last_vendor_id, $sales_bonus_by_step);
	//	$sales_bonus_by_step = array();
	//}
	
	print "\nFinish update_vendor_portal_bonus. $row_count row updated, $failed_count row failed.\n";
	
	// check deleted data
	print "\nChecking Deleted data for sales_bonus_by_step.\n";
	
	$ret = $con2->query("select * from POSDM_SKU_RJ_SALES_TIER_DEL where CHANGE_FLAG=1 order by SITE, SAP_VENDOR_CODE, ID");	// remember to order by branch id, date_to
	$row_count = 0;
	$failed_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){
		$row_count++;
		print "\rRunning row $row_count, ID $r[ID]. . .";
		
		$bid = mi(substr(trim($r['SITE']), -2));
		$r['SKU_CAT4'] = trim($r['SKU_CAT4']);
		$r['SKU_CAT3'] = trim($r['SKU_CAT3']);
		$r['SKU_CAT2'] = trim($r['SKU_CAT2']);
		$r['SKU_CAT1'] = trim($r['SKU_CAT1']);
		
		if(!$bid || !isset($branch_list[$bid])){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid Branch ID $bid.\n";
			continue;
		}
		
		$sap_vendor_id = mi($r['SAP_VENDOR_CODE']);
		$vendor_id = get_arms_vendor_id_from_sap_vendor_id($sap_vendor_id);	// convert sap vendor id to arms vendor id
		
		if(!$vendor_id){
			$failed_count++;
			print "\nRow ID ".$r['ID']." failed: Invalid SAP Vendor ID, $sap_vendor_id.\n";
			continue;
		}
		
		$valid_from = trim($r['VALID_FROM']);	// valid from
		$amt_from = round(mf($r['AMOUNT_FROM']),2);	// sales target from
		$bonus_per = mf($r['RATES']);	// rate %
		$exceptional_rate = mf($r['EXC_RATES']);	// rate for exceptional condition
		$mcode = trim($r['GTIN_EAN']);
		
		$y = mi(date("Y", strtotime($valid_from)));
		$m = mi(date("m", strtotime($valid_from)));
		
		$con->sql_query("select sales_bonus_by_step from vendor_portal_branch_info where branch_id=$bid and vendor_id=$vendor_id");
		$tmp = $con->sql_fetchassoc();
		$con->sql_freeresult();

		$sales_bonus_by_step = unserialize($tmp['sales_bonus_by_step']);
		if(!$sales_bonus_by_step){
			$failed_count++;
			print "\nRow ID $r[ID], No data in sales_bonus_by_step, Branch ID#$bid, Vendor_id#$vendor_id.\n";
			continue;
		}
		
		if(!isset($sales_bonus_by_step[$y][$m]) || !$sales_bonus_by_step[$y][$m]){
			$failed_count++;
			print "\nRow ID $r[ID], Year/Month Not Found, Branch ID#$bid, Vendor_id#$vendor_id, Year#$y, Month#$m.\n";
			continue;
		}
		
		$replace_array_key = -1;
		$upd = array();
		foreach($sales_bonus_by_step[$y][$m] as $key => $tmp){
			if($tmp['amt_from'] == $amt_from){
				$replace_array_key = $key;
				$upd = $tmp;
			}
		}
		
		if($replace_array_key < 0){
			$failed_count++;
			print "\nRow ID $r[ID], Amount From Not Found, Branch ID#$bid, Vendor_id#$vendor_id, Amt From#$amt_from.\n";
			continue;
		}
		
		if($r['SKU_CAT4'] || $r['SKU_CAT3'] || $r['SKU_CAT2'] || $r['SKU_CAT1']){	// exceptional condition for CATEGORY
			// check for exceptional type
			if(!$upd['bonus_per_by_type']){
				$failed_count++;
				print "\nRow ID ".$r['ID']." failed: Data have no bonus by type.\n";
				continue;
			}
			
			$cat_list = array('SKU_CAT4', 'SKU_CAT3', 'SKU_CAT2', 'SKU_CAT1');
			$skip = false;
			foreach($cat_list as $sap_cat_col){
				$sap_cat_id = trim($r[$sap_cat_col]);
				
				if($sap_cat_id){
					$cat_id = get_arms_category_id_from_sap_category_id($sap_cat_id);
					if(!$cat_id){
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid SAP Category ID $sap_cat_id.\n";
						$skip = true;
						break;
					}
					
					$cat_info = get_category_info($cat_id);
			
					if($cat_info){
						$got_update = false;
						foreach($upd['bonus_per_by_type'] as $key=>$upd2){
							if($upd2['type'] == 'CATEGORY' && $upd2['value'] == $cat_id){
								unset($upd['bonus_per_by_type'][$key]);
								$got_update = true;
								break;
							}
						}
						
						if(!$got_update){
							$failed_count++;
							print "\nRow ID ".$r['ID']." failed: bonus by type data not match for CATEGORY ID#$cat_id.\n";
							$skip = true;
							break;
						}
						break;
					}else{
						$failed_count++;
						print "\nRow ID ".$r['ID']." failed: Invalid ARMS Category ID $cat_id cannot get info.\n";
						$skip = true;
						break;
					}
				}
			}
			
			if($skip)	continue;
			
			$sales_bonus_by_step[$y][$m][$replace_array_key] = $upd;
		}else{
			unset($sales_bonus_by_step[$y][$m][$replace_array_key]);
			
			if(!$sales_bonus_by_step[$y][$m])	unset($sales_bonus_by_step[$y][$m]);
			if(!$sales_bonus_by_step[$y])	unset($sales_bonus_by_step[$y]);
		}
		
		$tmp = array();
		$tmp['sales_bonus_by_step'] = serialize($sales_bonus_by_step);
		$con->sql_query("update vendor_portal_branch_info set ".mysql_update_by_field($tmp)." where branch_id=$bid and vendor_id=$vendor_id");
		
		$success = $con2->exec("update POSDM_SKU_RJ_SALES_TIER_DEL set CHANGE_FLAG=0 where ID=".ms($r['ID']));
	}
	
	$ret->closeCursor();
	
	print "\nFinish delete data for sales_bonus_by_step. $row_count row processed, $failed_count row failed.\n";
}

function get_sku_item_id_by_gtin($mcode, &$params = array()){
	global $con;
	
	$mcode = trim($mcode);
	
	if(!$mcode)	return 0;
	
	$con->sql_query("select id,sku_item_code from sku_items where mcode=".ms($mcode)." order by id limit 1");
	$si = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	$params['info'] = $si;
	
	return mi($si['id']);
}

function check_and_create_vendor_portal_info($bid, $vendor_id){
	global $con;
	
	$bid = mi($bid);
	$vendor_id = mi($vendor_id);
	
	if(!$bid || !$vendor_id){
		print "\nVendor Portal Info cannot be check: Branch ID#$bid, Vendor ID#$vendor_id.\n";
	}
	
	// check vendor_portal
	$con->sql_query("select vendor_id from vendor_portal_info where vendor_id=$vendor_id");
	$vp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$vp){
		$upd = array();
		$upd['vendor_id'] = $vendor_id;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		$upd['last_update_by'] = 1;
		
		$con->sql_query("insert into vendor_portal_info ".mysql_insert_by_field($upd));
	}
	
	// check vendor_portal_branch_info
	$con->sql_query("select vendor_id from vendor_portal_branch_info where branch_id=$bid and vendor_id=$vendor_id");
	$vpb = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if(!$vpb){
		$upd = array();
		$upd['branch_id'] = $bid;
		$upd['vendor_id'] = $vendor_id;
		$upd['added'] = $upd['last_update'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into vendor_portal_branch_info ".mysql_insert_by_field($upd));
	}
}

function sort_report_profit($a, $b){
	$col = 'date_to';
	
	if($a[$col] == $b[$col])	return 0;
	
	return ($a[$col] > $b[$col]) ? 1 : 0;
}

function sort_report_bonus_y($a, $b){
	if($a == $b)	return 0;
	
	return $a > $b ? 1 : 0;
}

function sort_report_bonus_m($a, $b){
	if($a == $b)	return 0;
	
	return $a > $b ? 1 : 0;
}

function sort_report_bonus_amt($a, $b){
	$col = 'amt_from';
	
	if($a[$col] == $b[$col]){
		$this->bonus_amt_duplicated = $a[$col];
		return 0;
	}	
	
	return ($a[$col] > $b[$col]) ? 1 : 0;
}

function convert_array_string_value($arr){
	if(!$arr || !is_array($arr)) return '';
	
	foreach($arr as $key => $v){
		if(is_array($v)){
			$arr[$key] = convert_array_string_value($v);
		}else{
			$arr[$key] = strval($v);
		}
	}
	
	return $arr;
}

function test_connect(){
	global $con, $con2;
	
	if(!$con2)	connect_sap();	// connect sap
	
	$ret = $con2->query("select * FROM INFORMATION_SCHEMA.TABLES");
	
	$row_count = 0;
	
	while($r = $ret->fetch(PDO::FETCH_ASSOC)){	
		print_r($r);
	}
}

function auto_map_new_vendor(){
	global $con;
	
	$file = 'sap_new_vendor.csv';
	print "\nChecking New Vendor Mapping ($file). . .\n";
	
	$f = fopen($file,"rt");
	
	if(!$f){
		print "$file cannot be open\n";
		return false;
		//die("$file cannot be open\n");
	}	
		
	$line = fgetcsv($f);	// skip first line, it is header
	
	$row_count = 0;
	
	while($r = fgetcsv($f)){
		$row_count++;
		print "\r$row_count. . . .";
		
		$arms_vendor_id = mi($r[1]);
		$sap_vendor_id = mi($r[0]);
		
		if(!$arms_vendor_id || !$sap_vendor_id)	continue;
		
		$upd = array();
		$upd['arms_vendor_id'] = $arms_vendor_id;
		$upd['sap_vendor_id'] = $sap_vendor_id;
		
		$con->sql_query("replace into sap_vendor ".mysql_insert_by_field($upd));
		
	}
	
	print "\nDone, $row_count row updated.\n";
}
?>
