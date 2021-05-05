<?php
/*
- ./dump_csv.sh
(if you want to move all csv to a folder)
- mv *.csv /csv
- php xpos_migration.php import_all
*/
define('TERMINAL', 1);

// when testing, hide config.php and use own database connection
include_once('config.php');
//$db_default_connection = array("localhost", "root", "", "yy");
include_once('include/mysql.php');
include_once('include/db.php');

if (!$con->db_connect_id) { die('cannot connect '.mysql_error()); }

$agrs = $_SERVER['argv'];
$csv_folder_patch = 'yy-convertion/csv';    // choose csv files location

switch($agrs[1]){
	case 'import_membership':
	    import_membership();
	    exit;
	case 'import_vendor':
	    import_vendor();
	    exit;
	case 'import_department':
	    import_department();
	    exit;
	case 'import_brand':
	    import_brand();
	    exit;
	case 'import_sku':
	    import_sku();
	    exit;
	case 'import_stock_balance':
	    import_stock_balance();
	    exit;
	case 'import_all':
	    import_membership();
	    import_vendor();
	    import_brand();
	    import_department();
	    import_sku();
	    exit;
	default:
	    print "Invalid Action.\n";
	    exit;
}

function import_membership(){
	global $con, $csv_folder_patch;
	$f = fopen($csv_folder_patch."/mmem_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("truncate membership");
	
	$nric_count = 0;
	$row_count = 0;
	
	while($r = fgetcsv($f))
	{
	    $row_count ++;
		$upd = array();
		$upd['nric'] = trim(str_replace("-", "", $r[6]));
		if(!$upd['nric']){  // generate a tmp ic
            $nric_count++;
            $upd['nric'] = $nric_count;
		}
		$upd['name'] = trim($r[3]);
		$upd['gender'] = trim($r[14]);
		$upd['designation'] = trim($r[4]);
		$upd['dob'] = intval($r[8]);
		$upd['marital_status'] = trim($r[17]) == 'M' ? 1 : 0;
		$upd['national'] = 'Malaysia';
		$upd['address'] = $r[20]."\n".$r[21]."\n".$r[22]."\n".$r[23];
		$upd['postcode'] = trim($r[25]);
		$upd['phone_1'] = trim($r[28]);
		$upd['phone_2'] = trim($r[29]);
		$upd['email'] = trim($r[31]);
		$upd['occupation'] = trim($r[15]);
		$upd['card_no'] = $upd['nric'];
		if($r[33]){
		    $upd['issue_date'] = trim($r[33]);
            $upd['issue_date'] = substr($upd['issue_date'], 0, 4).'-'.substr($upd['issue_date'], 4, 2).'-'.substr($upd['issue_date'], 6, 2);
		}
		$upd['next_expiry_date'] = '2030-01-01';
		$upd['points'] = intval($r[10]);
		$upd['verified_by'] = 1;
		$upd['apply_branch_id'] = 1;
		$upd['verified_date'] = $upd['issue_date'];
		$upd['member_type'] = 'member1';
		$upd['points_update'] = date('Y-m-d');
		
		print "Importing Membership: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("replace into membership ".mysql_insert_by_field($upd));
		
	}
	fclose($f);
}

function import_vendor(){
    global $con, $csv_folder_patch;
	$f = fopen($csv_folder_patch."/msup_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("truncate vendor");
    $con->sql_query("create table if not exists yy_vendor (
		id int primary key,
		vendor_id int
	)");
	$con->sql_query("truncate yy_vendor");
	
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
		$upd = array();
		$upd['description'] = $r[1];
		$upd['active'] = 1;
		
		print "Importing Vendor: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("replace into vendor ".mysql_insert_by_field($upd));
		$vendor_id = intval($con->sql_nextid());
		
		$upd2 = array();
		$upd2['id'] = $r[0];
		$upd2['vendor_id'] = $vendor_id;
		$con->sql_query("replace into yy_vendor ".mysql_insert_by_field($upd2));
	}
	fclose($f);
}

function import_brand(){
    global $con, $csv_folder_patch;
	$f = fopen($csv_folder_patch."/mbrn_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("truncate brand");
    $con->sql_query("create table if not exists yy_brand (
		id int primary key,
		brand_id int
	)");
	$con->sql_query("truncate yy_brand");
	
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
		$upd = array();
		$upd['description'] = $r[1];
		$upd['active'] = 1;
		
		print "Importing Brand: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("replace into brand ".mysql_insert_by_field($upd));
		$brand_id = intval($con->sql_nextid());

		$upd2 = array();
		$upd2['id'] = $r[0];
		$upd2['brand_id'] = $brand_id;
		$con->sql_query("replace into yy_brand ".mysql_insert_by_field($upd2));

	}
	fclose($f);
}

function import_department(){
    global $con, $csv_folder_patch;
	$f = fopen($csv_folder_patch."/mcls_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("truncate category");
	$con->sql_query("create table if not exists yy_category (
		id int primary key,
		category_id int
	)");
	$con->sql_query("truncate yy_category");
	
    $upd = array();
    $upd['id'] = 1;
	$upd['level'] = 1;
	$upd['code'] = 1;
	$upd['description'] = 'SOFTLINE';
	$upd['active'] = 1;
	$upd['department_id'] = 1;
	$upd['tree_str'] = '(0)';
	$upd['no_inventory'] = 'no';
	$upd['is_fresh_market'] = 'no';
	$con->sql_query("insert into category ".mysql_insert_by_field($upd));

	$upd = array();
    $upd['id'] = 2;
    $upd['root_id'] = 1;
	$upd['level'] = 2;
	$upd['code'] = 2;
	$upd['description'] = 'OTHERS';
	$upd['active'] = 1;
	$upd['department_id'] = 2;
	$upd['tree_str'] = '(0)(1)';
	$con->sql_query("insert into category ".mysql_insert_by_field($upd));
	
	$upd = array();
    $upd['id'] = 3;
    $upd['root_id'] = 2;
	$upd['level'] = 3;
	$upd['code'] = 3;
	$upd['description'] = 'OTHERS';
	$upd['active'] = 1;
	$upd['department_id'] = 2;
	$upd['tree_str'] = '(0)(1)(2)';
	$con->sql_query("insert into category ".mysql_insert_by_field($upd));
	
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
		$upd = array();
        $upd['root_id'] = 1;
		$upd['level'] = 2;
		$upd['description'] = $r[1];
		$upd['active'] = 1;
		$upd['tree_str'] = '(0)(1)';

		print "Importing Department: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("replace into category ".mysql_insert_by_field($upd));
		$cat_id = intval($con->sql_nextid());
		$con->sql_query("update category set department_id=id where id=$cat_id");
		
		$upd2 = array();
		$upd2['id'] = intval($r[0]);
		$upd2['category_id'] = $cat_id;
		$con->sql_query("replace into yy_category ".mysql_insert_by_field($upd2));
	}
	fclose($f);
}

function import_sku(){
    global $con, $csv_folder_patch;
    import_yy_sku();
    import_real_sku();
	import_stock_balance();
}

function import_real_sku(){
    global $con, $csv_folder_patch;

	$con->sql_query("truncate sku");
    $con->sql_query("truncate sku_items");

    $q1 = $con->sql_query("select yy_sku.*,yy_brand.brand_id as real_brand_id
from yy_sku
left join yy_brand on yy_brand_id=yy_brand.id
order by item_code, item_code2");

    $last_item_code = '';
    $sku_code = 28000000;
    $sku_id = 0;
    $is_parent = 0;
    $row_count = 0;

    while($r = $con->sql_fetchassoc($q1)){
        $row_count++;
		$item_code = trim($r['item_code']);

		if($last_item_code!=$item_code){    // new sku group
		    $sku_code++;

			$sku = array();
			$sku['sku_code'] = $sku_code;
			$sku['category_id'] = intval($r['real_category_id']);
			$sku['uom_id'] = 1;
			$sku['brand_id'] = intval($r['real_brand_id']);
			$sku['status'] = 1;
			$sku['active'] = 1;
			$sku['sku_type'] = 'OUTRIGHT';
			$sku['apply_branch_id'] = 1;
			$con->sql_query("insert into sku ".mysql_insert_by_field($sku));
			$sku_id = intval($con->sql_nextid());
			$sku_item_code = $sku_code.'0000';
			$is_parent = 1;
		}
		$sku_item_code++;

		$sku_items = array();
		$sku_items['sku_id'] = $sku_id;
		$sku_items['sku_item_code'] = $sku_item_code;
		$sku_items['packing_uom_id'] = 1;
		$sku_items['link_code'] = trim($r['item_code']);
		$sku_items['description'] = trim($r['description']);
		$sku_items['selling_price'] = floatval($r['selling_price']);
		$sku_items['hq_cost'] = $sku_items['cost_price'] = floatval($r['cost']);
		$sku_items['active'] = 1;
		$sku_items['added'] = 'CURRENT_TIMESTAMP';
		$sku_items['artno'] = trim($r['item_code2']);
		$sku_items['is_parent'] = $is_parent;
		$sku_items['size'] = $r['size'];
		$sku_items['color'] = $r['color'];
		$con->sql_query("insert into sku_items ".mysql_insert_by_field($sku_items));

		print "Create SKU & SKU Items: Item $row_count, memory used: ".memory_get_usage()."\n";
		$last_item_code = $item_code;
		$is_parent = 0;
	}
	$con->sql_freeresult();
}

function import_yy_sku(){
    global $con, $csv_folder_patch;
    
    // create temp sku list
	$f = fopen($csv_folder_patch."/mitm_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("drop table yy_sku",false,false);
	$con->sql_query("create table if not exists yy_sku (
		id int primary key auto_increment,
		item_code char(50),
		item_code2 char(50),
		description text,
		yy_category_id int,
		yy_department_id int,
		yy_brand_id int,
		selling_price double,
		color char(10),
		size char(10),
		real_category_id int,
		cost double,
		index(item_code),
		index(yy_category_id, yy_department_id)
	)");
	$con->sql_query("truncate yy_sku");
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
		$upd = array();
        $upd['item_code'] = trim($r[1]);
		$upd['item_code2'] = trim($r[0]);
		$upd['description'] = trim($r[3]);
		$upd['yy_category_id'] = $r[10];
		$upd['yy_department_id'] = $r[14];
		$upd['yy_brand_id'] = $r[13];
        $upd['selling_price'] = $r[15];
        $upd['color'] = $r[31];
        $upd['size'] = $r[32];
        
		print "Extract SKU item from csv: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("replace into yy_sku ".mysql_insert_by_field($upd));
	}
	fclose($f);
	
	// create temp category data
	$f = fopen($csv_folder_patch."/mctg_tbl.csv","rt");
	$line = fgetcsv($f);
	$row_count = 0;
	$category = array();
	while($r = fgetcsv($f))
	{
	    $row_count ++;
        $category[intval($r[0])] = trim($r[1]);
		print "Extract category from csv: Item $row_count, memory used: ".memory_get_usage()."\n";
	}
	fclose($f);
	
	// create complete category table
	$con->sql_query("delete from category where level>=3 and id<>3");
	$q1 = $con->sql_query("select distinct yy_category_id,yy_department_id,yy_c.category_id as real_dept_id from yy_sku
left join yy_category yy_c on yy_c.id=yy_department_id order by real_dept_id");
    $row_count = 0;
	while($r = $con->sql_fetchrow($q1)){
		$yy_cat_id = intval($r['yy_category_id']);
		$dept_id = intval($r['real_dept_id']);
		if(!$yy_cat_id){
            $real_cat_id = 3;
		}else{
            if(!$dept_id)   $dept_id = 2;   // others

			$row_count ++;
			$upd = array();
	        $upd['root_id'] = $dept_id;
			$upd['level'] = 3;
			$upd['description'] = $category[$yy_cat_id];
			$upd['active'] = 1;
			$upd['tree_str'] = '(0)(1)('.$dept_id.')';
			if(!$upd['description'])    $upd['description'] = $yy_cat_id;

			print "Create temporary SKU and category: Item $row_count, memory used: ".memory_get_usage()."\n";
			$con->sql_query("replace into category ".mysql_insert_by_field($upd));
			$real_cat_id = intval($con->sql_nextid());
		}
		$con->sql_query("update yy_sku set real_category_id=$real_cat_id where yy_category_id=".intval($r['yy_category_id'])." and yy_department_id=".intval($r['yy_department_id']));
	}
	$con->sql_freeresult($q1);
	
	// insert cost
	$f = fopen($csv_folder_patch."/mgrd_tbl.csv","rt");
	$line = fgetcsv($f);
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
        $item_code = trim($r[3]);
        $cost = floatval($r[5]);
        if(!$cost)  continue;
		print "Get item cost from csv: Item $row_count, memory used: ".memory_get_usage()."\n";
		$con->sql_query("update yy_sku set cost=$cost where item_code=".ms($item_code));
	}
	fclose($f);
}

function import_stock_balance(){
     global $con, $csv_folder_patch;

    // get all GRN
	$f = fopen($csv_folder_patch."/mgrm_tbl.csv","rt");
	$line = fgetcsv($f);
	$con->sql_query("drop table yy_grn",false,false);
	$con->sql_query("create table if not exists yy_grn (
		sku_item_id int primary key,
		grn_qty int not null default 0,
		in_qty int not null default 0,
		out_qty int not null default 0
	)");
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
	    $artno = trim($r[3]);
	    if(!$artno) continue;   // invalid artno
	    
	    $q1 = $con->sql_query("select id from sku_items where artno=".ms($artno)." limit 1");
		if($con->sql_numrows($q1))  $sid = intval($con->sql_fetchfield(0));
		else    $sid = 0;
		$con->sql_freeresult($q1);
		
		if(!$sid)   continue; // no this sku
	    $upd = array();
	    $upd['sku_item_id'] = $sid;
	    $upd['grn_qty'] = intval($r[8]);
	    if(!$upd['grn_qty'])    continue;   // zero grn qty
	    
	    $con->sql_query("insert into yy_grn ".mysql_insert_by_field($upd)." on duplicate key update grn_qty=grn_qty+".$upd['grn_qty']);
        print "Extract GRN data from csv: Item $row_count, memory used: ".memory_get_usage()."\n";
	}
	fclose($f);
	
	// get from move qty
	$f = fopen($csv_folder_patch."/mdmv_tbl.csv","rt");
	$line = fgetcsv($f);
	$row_count = 0;
	while($r = fgetcsv($f))
	{
	    $row_count ++;
	    $artno = trim($r[1]);
	    if(!$artno) continue;   // invalid artno

	    $q1 = $con->sql_query("select id from sku_items where artno=".ms($artno)." limit 1");
		if($con->sql_numrows($q1))  $sid = intval($con->sql_fetchfield(0));
		else    $sid = 0;
		$con->sql_freeresult($q1);

		if(!$sid)   continue; // no this sku
	    $upd = array();
	    $upd['sku_item_id'] = $sid;
	    $upd['in_qty'] = intval($r[2]);
	    $upd['out_qty'] = intval($r[3]);
	    
	    if(!$upd['in_qty']&&!$upd['out_qty'])    continue;   // no in & out

	    $con->sql_query("insert into yy_grn ".mysql_insert_by_field($upd)." on duplicate key update
		in_qty=in_qty+".$upd['in_qty'].",
		out_qty=out_qty+".$upd['out_qty']);
        print "Extract qty out from csv: Item $row_count, memory used: ".memory_get_usage()."\n";
	}
	fclose($f);
	
	// insert as stock check
	$con->sql_query("truncate stock_check");
	$con->sql_query("truncate sku_items_cost");
	$q1 = $con->sql_query("select si.id as sku_item_id,(in_qty-out_qty) as sb, si.sku_item_code, si.sku_item_code,si.selling_price,si.cost_price
from sku_items si
left join yy_grn on si.id=yy_grn.sku_item_id");
    $row_count = 0;
	while($r = $con->sql_fetchassoc($q1)){
	    $row_count++;
	    
		$upd = array();
		$upd['date'] = '2010-08-01';
		$upd['branch_id'] = 1;
		$upd['sku_item_code'] = trim($r['sku_item_code']);
		$upd['location'] = $upd['shelf_no'] = $upd['item_no'] = '-';
		$upd['selling'] = $r['selling_price'];
		$upd['cost'] = $r['cost_price'];
		$upd['qty'] = $r['sb'];
		$con->sql_query("insert into stock_check ".mysql_insert_by_field($upd));
		
		$upd2 = array();
		$upd2['branch_id'] = $upd['branch_id'];
		$upd2['sku_item_id'] = $r['sku_item_id'];
		$upd2['date'] = $upd['date'];
		$upd2['qty'] = $upd['qty'];
		$upd2['grn_cost'] = $upd2['avg_cost'] = $upd['cost'];
		$upd2['changed'] = 1;
		$con->sql_query("insert into sku_items_cost ".mysql_insert_by_field($upd2));
		print "Creating stock check: Item $row_count, memory used: ".memory_get_usage()."\n";
	}
	$con->sql_freeresult($q1);
}

function mysql_insert_by_field($arr, $fields = false, $null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$newf[] = "`$f`";
		$v = $arr[$f];
		if ($ret != '') $ret .= ',';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= $v;
		else
			$ret .= ms($v,$null_if_empty);
	}

	$ret = '(' . join(",", $newf) . ') values (' . $ret . ')';
	return $ret;
}

function mysql_update_by_field($arr, $fields = false,$null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$v = $arr[$f];
		if ($ret != '') $ret .= ', ';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= "`$f` = " . $v;
		else
			$ret .= "`$f` = " . ms($v,$null_if_empty);
	}

//	$ret = '(' . join(",", $fields) . ') values (' . $ret . ')';
	return $ret;
}

function ms($str,$null_if_empty=0)
{
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}
?>
