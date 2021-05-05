<?php
/*
6/1/2018 2:38 PM Justin
- Enhanced to have Consignment Table, Consignment Price Type, Consignment Discount Rate.
- Enhanced to check Input and Output tax base on purchase and supply GST list.

7/12/2018 1:50 PM Andy
- Fixed NRIC to check 20 chars only and only accept alphanumeric.

7/30/2018 11:45 AM Justin
- Bug fixed on not showing any error message while there is no data from database and didn't provide the rate from import file.
*/
define('TERMINAL', 1);

include_once('../config.php');
include_once('../default_config.php');
//$db_default_connection = array("localhost", "root", "", "yy");
include_once('../include/db.php');
include_once('../include/functions.php');
error_reporting (E_ALL ^ E_NOTICE);

if (!$con->db_connect_id) { die('cannot connect '.mysql_error()); }

$agrs = $_SERVER['argv'];
if(!defined('ARMS_SKU_CODE_PREFIX'))	define('ARMS_SKU_CODE_PREFIX', '28%06d');

switch($agrs[1]){
	case 'import_uom':	// php migration.php import_uom 1
		/*
			Export from ARMS
			select code,description, fraction from uom order by id
		*/
		import_uom($agrs[2]);
		exit;
	case 'import_vendor':	// php migration.php import_vendor 1
		/*
			Export from ARMS
			select code,description,address as address1,'' as address2,'' as address3,'' as address4,phone_1,phone_2,phone_3,contact_person,contact_email,term,gst_register_no,if(gst_start_date=0,'',gst_start_date) as gst_start_date,bank_account,company_no,credit_limit from vendor order by id
		*/
		import_vendor($agrs[2]);
		exit;	
	case 'import_brand':	// php migration.php import_brand 1
		/*
			Export from ARMS
			select b.code, b.description, bg.code as BRAND_GROUP_CODE, bg.description as BRAND_GROUP_DESCRIPTION
			from brand b
			left join brand_brgroup bbg on bbg.brand_id=b.id
			left join brgroup bg on bg.id=bbg.brgroup_id
			order by b.id
		*/
		import_brand($agrs[2]);
		exit;
	case 'import_debtor':	// php migration.php import_debtor 1
		/*
			Export from ARMS
			select code, description, company_no, term, credit_limit, phone_1, phone_2, phone_3 as fax, contact_person, contact_email, address as address1,'' as address2,'' as address3,'' as address4, area, debtor_mprice_type as default_mprice, if(gst_start_date=0,'',gst_start_date) as gst_start_date, gst_register_no from debtor
		*/
		import_debtor($agrs[2]);
		exit;
	case 'import_member': // php migration.php import_member is_run clear_all
		import_member($agrs[2], $agrs[3]);
		exit;
	case 'import_sku':	// php migration.php import_sku sku.csv
		/*
			select ifnull(si.mcode,'') as mcode, ifnull(si.link_code,'') as link_code, ifnull(si.artno,'') as artno, ifnull(si.description,'') as item_description, 
			ifnull(si.receipt_description,'') as receipt_description, uom.code as uom_code, ifnull(sic.grn_cost,si.cost_price) as cost, 
			ifnull(sip.price, si.selling_price) as selling_price, sku.scale_type, ifnull(simp.price,0) as wholesale1, 
			ifnull(cat1.description,'') as cat_line,ifnull(cat2.description,'') as cat_dept,
			ifnull(cat3.description,'') as cat_1, ifnull(cat4.description,'') as cat_2, ifnull(cat5.description,'') as cat_3, ifnull(cat6.description,'') as cat_4, ifnull(cat7.description,'') as cat_5,
			ifnull(brand.description,'') as brand_desc, ifnull(v.description,'') as vendor_desc,
			ifnull(si.color,'') as color, ifnull(si.size,'') as size, input_gst.code as input_tax_code, output_gst.code as output_tax_code, sku.sku_type,
			ifnull(sku.po_reorder_qty_min,0) as reorder_min_qty, ifnull(sku.po_reorder_qty_max,0) as reorder_max_qty, '' as parent_arms_code,
			if(si.is_parent=1,'',(select si2.mcode from sku_items si2 where si2.sku_id=si.sku_id and si2.is_parent=1 limit 1)) as parent_mcode,
			if(si.is_parent=1,'',(select si2.artno from sku_items si2 where si2.sku_id=si.sku_id and si2.is_parent=1 limit 1)) as parent_artno
			from sku_items si
			join sku on sku.id=si.sku_id
			left join uom on uom.id=si.packing_uom_id
			left join sku_items_cost sic on sic.branch_id=3 and sic.sku_item_id=si.id
			left join sku_items_price sip on sip.branch_id=3 and sip.sku_item_id=si.id
			left join sku_items_mprice simp on simp.branch_id=3 and simp.sku_item_id=si.id and simp.type='wholesale1'
			join category c on c.id=sku.category_id
			join category_cache cc on cc.category_id=c.id
			left join category cat1 on cat1.id=cc.p1
			left join category cat2 on cat2.id=cc.p2
			left join category cat3 on cat3.id=cc.p3
			left join category cat4 on cat4.id=cc.p4
			left join category cat5 on cat5.id=cc.p5
			left join category cat6 on cat6.id=cc.p6
			left join category cat7 on cat7.id=cc.p7
			left join brand on brand.id=sku.brand_id
			left join vendor v on v.id=sku.vendor_id
			left join gst input_gst on input_gst.id=if(if(si.input_tax<0,sku.mst_input_tax,si.input_tax)<0,cc.input_tax,if(si.input_tax<0,sku.mst_input_tax,si.input_tax))
			left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
			where cc.p1=2494
			order by si.sku_id, si.is_parent desc, si.id
			limit 100000,50000
			INTO OUTFILE '/tmp/sku_items3.csv'
			FIELDS TERMINATED BY ','
			ENCLOSED BY '"'
			LINES TERMINATED BY '\n'
			
			Default Path
			/var/lib/mysql-files/
		*/
		import_sku($agrs[2], $agrs[3]);	
		exit;
	case 'update_brand':	// php migration.php update_brand
		update_brand();
		exit;
	case 'update_sku_packing_uom':	// php migration.php update_sku_packing_uom 1
		update_sku_packing_uom($agrs[2]);
		exit;
	default:
	    print "Invalid Action.\n";
	    exit;	
}

function get_vendor_by_code($vendor_code){
	global $con;
	
	if(!$vendor_code) return;
	
	$con->sql_query("select * from vendor where code=".ms(substr($vendor_code,0,10)));
	$vendor_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $vendor_info['id'];
}

function get_uom_by_code($uom_code){
	global $con;
	
	if(!$uom_code) return;
	
	$con->sql_query("select * from uom where code=".ms(substr($uom_code,0,6)));
	$uom_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $uom_info['id'];
}

function get_brand_by_code($brand_code){
	global $con;
	
	if(!$brand_code) return;
	
	$con->sql_query("select * from brand where code=".ms(substr($brand_code,0,6)));
	$brand_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $brand_info['id'];
}

function get_brgroup_by_code($code){
	global $con;
	
	if(!$code) return;
	
	$con->sql_query("select * from brgroup where code=".ms(substr($code,0,6)));
	$info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $info['id'];
}

function get_debtor_by_code($code){
	global $con;
	
	if(!$code) return;
	
	$con->sql_query("select * from debtor where code=".ms(substr($code,0,10)));
	$info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $info['id'];
}

function get_cat_by_desc($cat_desc, $root_id, $level){
	global $con;
	
	if(!$cat_desc || !$level) return;
	
	if($root_id) $filter[] = "root_id = ".mi($root_id);
	if($filter) $filters = "and ".join(" and ", $filter);
	
	$con->sql_query("select id from category where description=".ms($cat_desc)." and level=".mi($level)." $filters limit 1");
	$cat_info = $con->sql_fetchrow();
	$con->sql_freeresult();
	
	return $cat_info['id'];
}

function get_brand_by_desc($brand_desc){
	global $con;

	if(!$brand_desc) return;

	$con->sql_query("select id from brand where description=".ms($brand_desc));
	$brand_info = $con->sql_fetchrow();
	$con->sql_freeresult();

	if(!$brand_info){
		$brand_code = substr($brand_desc,0,6);
		$con->sql_query("select id from brand where code=".ms($brand_code));
		$brand_info = $con->sql_fetchrow();
		$con->sql_freeresult();
	}	
		
	return $brand_info['id'];
}

function get_vendor_by_desc($vendor_desc){
	global $con;
	
	if(!$vendor_desc) return;
	
	$con->sql_query("select * from vendor where description=".ms($vendor_desc));
	$vendor_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $vendor_info['id'];
}

function get_member_by_nric($nric){
	global $con;
	
	if(!$nric) return;
	
	$con->sql_query("select * from membership where nric=".ms($nric));
	$member_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	return $member_info['nric'];
}

function import_uom($clean_all){
	global $con;

	$ttl_count = $imported_count = $invalid_count =0;
	$f = fopen("uom.csv","rt");
	$fp = fopen('invalid_uom.csv', 'w');
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	if($clean_all){
		$con->sql_query("truncate uom");
		$con->sql_query("replace into uom (id,code,description,fraction,active) values (1,'EACH','EACH',1,1)");
	} 
	
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = "";
		print "\r$ttl_count. . . .";
		
		$uom_id = 0;
		
		if(!trim($r[0])){
			$err_msg = "Code is empty";
		}
		
		$ins = array();
		//$ins['code'] = strtoupper(substr(trim($r[0]),0,6));
		$ins['code'] = strtoupper(trim($r[0]));
		if(strlen($ins['code'])>6)	$err_msg = "Code cannot exceed 6 characters";
		
		if($ins['code'] == 'EACH'){
			$err_msg = "EACH is a default uom and no need to import";
		}
		if(!$err_msg && $ins['code'])	$uom_id = get_uom_by_code($ins['code']);
		if(!$err_msg && $uom_id)	$err_msg = "duplicate code";
		
		if(!$err_msg){			
			$ins['description'] = trim($r[1]);
			$ins['fraction'] = trim($r[2]);
			$ins['active'] = 1;
			if($ins['fraction']<=0)	$err_msg = "Fraction cannot be zero or less";
		}
		
		if(!$err_msg){
			$con->sql_query("insert into uom ".mysql_insert_by_field($ins));
			print $ins['description']." imported.\n";
			$imported_count++;
		}else{
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}		
	}
	fclose($f);
	fclose($fp);
	
	print $imported_count." UOM(s) imported of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid uom: $invalid_count. plesae check invalid_uom.csv\n";
}

function import_vendor($clean_all=false){
	global $con;

	$ttl_count = $imported_count = $invalid_count = 0;
	$f = fopen("vendor.csv","rt");
	$fp = fopen('invalid_vendor.csv', 'w');
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	if($clean_all) $con->sql_query("truncate vendor");
	$code_list = array();
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = '';
		print "\r$ttl_count. . . .";
		
		if(!trim($r[0])){
			$err_msg = "Code is empty";
		}else{
			// check code from db for duplication
			$q1 = $con->sql_query("select * from vendor where code = ".ms(trim($r[0]))." limit 1");
			if($con->sql_numrows($q1) > 0) $err_msg = 'Duplicate Code';
			$con->sql_freeresult($q1);
			
			// check code from import file for duplication
			if(!in_array(trim($r[0]), $code_list))	$code_list[] = trim($r[0]);
			else {
				if(!in_array('Duplicate Code', $error))	$err_msg = 'Duplicate Code';
			}
		}
		
		if(!trim($r[1])){
			$err_msg = "Description is empty";
		}
		
		if(!trim($r[12]) && trim($r[13])){
			$err_msg = "GST Registration Number is empty";
		}
		
		if(trim($r[13])) {
			if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $r[13])) {
				$sdate = explode('-', $r[13]);
				if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$err_msg = 'Date Invalid';
			}else	$err_msg = 'Incorrect Date Format (Example: YYYY-MM-DD)';
		}

		if(!$err_msg){
			$vendor_id = get_vendor_by_code($r[0]);
			
			if(!$vendor_id){
				$ins = array();
				//$ins['id'] = trim($r[0]);
				$ins['code'] = trim($r[0]);
				$ins['description'] = trim($r[1]);
				$ins['company_no'] = trim($r[15]);
				$ins['address'] = trim($r[2]);
				if(trim($r[3]))	$ins['address'] .= "\n".trim($r[3]);
				if(trim($r[4]))	$ins['address'] .= "\n".trim($r[4]);
				if(trim($r[5]))	$ins['address'] .= "\n".trim($r[5]);
				$ins['active'] = 1;
				$ins['phone_1'] = str_replace("-", "", trim($r[6]));
				$ins['phone_2'] = str_replace("-", "", trim($r[7]));
				$ins['phone_3'] = str_replace("-", "", trim($r[8]));
				
				$ins['contact_person'] = trim($r[9]);
				$ins['contact_email'] = trim($r[10]);
				$ins['term'] = trim($r[11]);
				//$ins['credit_limit'] = trim($r[11]);
				$ins['gst_register_no'] = trim($r[12]);
				$ins['gst_start_date'] = trim($r[13]);
				if($ins['gst_register_no'] && !$ins['gst_start_date'])	$ins['gst_start_date'] = '2015-04-01';
				$ins['bank_account'] = trim($r[14]);
				$ins['credit_limit'] = mf($r[16]);
				if($ins['gst_register_no'])	$ins['gst_register'] = -1;
				else		$ins['gst_register'] = 0;
				$con->sql_query("insert into vendor ".mysql_insert_by_field($ins));
				$imported_count++;
			}else{
				$err_msg = "vendor code duplicate";;
			}
		}		
		
		if($err_msg){
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	
	fclose($f);
	fclose($fp);
	
	print "\n".$imported_count." vendor(s) imported of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid vendor: $invalid_count. please check invalid_vendor.csv\n";
}

function import_brand($clean_all=false){
	global $con;

	$ttl_count = $imported_count = $invalid_count = 0;
	$f = fopen("brand.csv","rt");
	$fp = fopen('invalid_brand.csv', 'w');
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	if($clean_all){
		$con->sql_query("truncate brand");
		$con->sql_query("truncate brgroup");
		$con->sql_query("truncate brand_brgroup");
	} 
	
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = "";
		print "\r$ttl_count. . . .";
		
		$ins = array();
		$ins['code'] = trim($r[0]);
		
		if(!trim($r[0])){
			$err_msg = "Code is empty";
		}else{
			$brand_id = get_brand_by_code($ins['code']);
			if($brand_id > 0)	$err_msg = "brand code duplicate";
		}	
		
		$ins['description'] = trim($r[1]);
		$ins['active'] = 1;
		
		if(!$err_msg){
			$con->sql_query("insert into brand ".mysql_insert_by_field($ins));
			$brand_id = $con->sql_nextid();
			$imported_count++;
			
			$brgroup = array();
			$brgroup['code'] = trim($r[2]);
			$brgroup['description'] = trim($r[3]);
			if($brgroup['code']){
				$brgroup_id = get_brgroup_by_code($brgroup['code']);
				
				if(!$brgroup_id){
					// group not exists
					$brgroup['active'] = 1;
					$con->sql_query("insert into brgroup ".mysql_insert_by_field($brgroup));
					$brgroup_id = $con->sql_nextid();
				}
				
				$brand_brgroup = array();
				$brand_brgroup['brand_id'] = $brand_id;
				$brand_brgroup['brgroup_id'] = $brgroup_id;
				$con->sql_query("replace into brand_brgroup ".mysql_insert_by_field($brand_brgroup));
			}
		}else{
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	fclose($f);
	fclose($fp);
	
	print $imported_count." Brands(s) imported of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid brand: $invalid_count. plesae check invalid_brand.csv\n";
}

function import_debtor($clean_all=false){
	global $con, $config;

	$ttl_count = $imported_count = $invalid_count = 0;
	$f = fopen("debtor.csv","rt");
	$fp = fopen("invalid_debtor.csv","w");
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	if($clean_all) $con->sql_query("truncate debtor");
	
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = '';
		
		$ins = array();
		$ins['code'] = trim($r[0]);
		
		if(!$ins['code']){
			$err_msg = "Code is empty";
		}else{
			$debtor_id = get_debtor_by_code($ins['code']);
			if($debtor_id > 0)	$err_msg = "code duplicate";
		}
		
		if(!$err_msg){
			$ins['active'] = 1;
			$ins['description'] = trim($r[1]);
			$ins['company_no'] = trim($r[2]);
			$ins['term'] = trim($r[3]);
			$ins['credit_limit'] = trim($r[4]);
			$ins['phone_1'] = str_replace("-", "", trim($r[5])); // phone
			$ins['phone_2'] = str_replace("-", "", trim($r[6])); // mobile
			$ins['phone_3'] = str_replace("-", "", trim($r[7])); // fax
			$ins['contact_person'] = trim($r[8]);
			$ins['contact_email'] = trim($r[9]);
			$ins['address'] = trim($r[10]);
			if(trim($r[11]))	$ins['address'] .= "\n".trim($r[11]);
			if(trim($r[12]))	$ins['address'] .= "\n".trim($r[12]);
			if(trim($r[13]))	$ins['address'] .= "\n".trim($r[13]);
			$ins['area'] = trim($r[14]);
			$ins['debtor_mprice_type'] = substr($r[15],0,10);
			$ins['gst_start_date'] = trim($r[16]);
			$ins['gst_register_no'] = trim($r[17]);
			
			if($ins['gst_register_no']){
				$ins['gst_start_date'] = date("Y-m-d", strtotime($ins['gst_start_date']));
				if(strtotime($ins['gst_start_date']) < strtotime($config['global_gst_start_date'])){
					$ins['gst_start_date'] = $config['global_gst_start_date'];
				}
			}else{
				$ins['gst_start_date'] = "";
			}
				
			$con->sql_query("insert into debtor ".mysql_insert_by_field($ins));
			$imported_count++;
		}else{
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	
	fclose($f);
	fclose($fp);
	
	print $imported_count." debtor(s) imported of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid debtor: $invalid_count. plesae check invalid_debtor.csv\n";
}

function import_member($is_run, $clean_all){
	global $con, $config;
	
	$ttl_count = $imported_count = $invalid_count = 0;
	$f = fopen("member.csv","rt");
	$fp = fopen("invalid_member.csv","w");
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	if($is_run && $clean_all){
		$con->sql_query("truncate membership");
		$con->sql_query("truncate membership_points");
		$con->sql_query("truncate membership_history");
		$con->sql_query("truncate card_nric");
		$con->sql_query("truncate tmp_membership_points_trigger");
	}
	
	if(!$is_run){
		$nric_list = array();
	}
	
	while($r = fgetcsv($f)){
		$ttl_count++;
        $err_msg = '';
		
		if(!trim($r[2])){
			$err_msg = "name is empty";
		}
		
		$card_no = trim($r[0]);
		
		if(!$err_msg){
			$nric = strtoupper(substr(preg_replace("/[^A-Za-z0-9]/", "", trim($r[3])),0,20));
			if(!$nric){
				$err_msg = "nric is empty";
			}
			
			if(!$err_msg){
				$existed_nric = get_member_by_nric($nric);
			
				if ($existed_nric){
					$err_msg = "nric already in database";
				}
				
				if(!$is_run){
					if(in_array($nric, $nric_list)){
						$err_msg = "nric duplicate";
					}
				}
				
				$issue_date = trim($r[11]);
				$next_expiry_date = trim($r[12]);
				
				// verify date
				if(!$err_msg){
					if(!$issue_date)	$err_msg = "Empty Verify Date";
					else{
						if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $issue_date)) {
							$sdate = explode('-', $issue_date);
							if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$err_msg = 'Verify Date Invalid';
							else{
								if($date_error = is_exceed_max_mysql_timestamp(strtotime($issue_date))){
									$err_msg = 'Verify Date cannot over '.$date_error['max_date'];
								}
							}
						}else	$err_msg = 'Incorrect Verify Date Format';
					}
				}
				
				// expiry date
				if(!$err_msg){
					if(!$next_expiry_date)	$err_msg = "Empty Expiry Date";
					else{
						if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $next_expiry_date)) {
							$sdate = explode('-', $next_expiry_date);
							if(!checkdate($sdate[1], $sdate[2], $sdate[0]))	$err_msg = 'Expiry Date Invalid';
							else{
								if($date_error = is_exceed_max_mysql_timestamp(strtotime($next_expiry_date))){
									$err_msg = 'Expiry Date cannot over '.$date_error['max_date'];
								}
							}
						}else	$err_msg = 'Incorrect Expiry Date Format';
					}
				}
				
				if(!$err_msg){
					if(strtotime($issue_date) > strtotime($next_expiry_date)){
						$err_msg = "Verify Date cannot over Expiry Date";
					}
				}
				
				if(!$err_msg){
					$q2 = $con->sql_query("select * from membership where card_no = ".ms($card_no)." limit 1");
					if($con->sql_numrows($q2) > 0) $err_msg = 'card no duplicate';
					$con->sql_freeresult($q2);
					
					if(!$err_msg){
						$member_type = trim($r[10]);
						
						if ($member_type){
							if (!array_key_exists($member_type,$config['membership_type']))
								$err_msg = "invalid member type";
						}else	$err_msg = 'member type is empty';
						
						if(!$err_msg){
							$ins = array();
							$ins['apply_branch_id'] = 1;
							$ins['card_no'] = $card_no;
							$ins['points'] = $r[1];
							$ins['name'] = trim($r[2]);
							$ins['nric'] = $nric;
							$ins['gender'] = trim($r[4]);
							$ins['race'] = trim($r[5]);
							$ins['dob'] = trim($r[6]);
							$ins['national'] = trim($r[7]);
							
							if(trim($r[8]) != "?" && strtolower(trim($r[8])) != "null") $ins['phone_3'] = str_replace("-", "", trim($r[8])); // mobile
							if(trim($r[9]) != "?" && strtolower(trim($r[9])) != "null") $ins['phone_1'] = str_replace("-", "", trim($r[9])); // phone
							
							$ins['member_type'] = $member_type;
							
							if(!$issue_date)	$issue_date = "2016-01-01";
							$ins['issue_date'] = date("Y-m-d", strtotime($issue_date));
							$ins['verified_date'] = date("Y-m-d", strtotime($issue_date));
							$ins['verified_by'] = 1;
							
							$ins['next_expiry_date'] = date("Y-m-d", strtotime($next_expiry_date));
							if(trim($r[13]) != "?" && strtolower(trim($r[13])) != "null") $ins['phone_2'] = str_replace("-", "", trim($r[13])); // fax
							if(trim($r[14]) != "?" && strtolower(trim($r[14])) != "null") $email = trim($r[14]);
							$ins['email'] = $email;
							$ins['address'] = trim($r[15]);
							if(trim($r[16]))	$ins['address'] .= "\n".trim($r[16]);
							if(trim($r[17]))	$ins['address'] .= "\n".trim($r[17]);
							if(trim($r[18]))	$ins['address'] .= "\n".trim($r[18]);
							$ins['city'] = str_replace(", ", "", trim($r[19]));
							$ins['state'] = trim($r[20]);
							$ins['postcode'] = trim($r[21]);
							
							if($is_run){
								$con->sql_query("insert into membership ".mysql_insert_by_field($ins));
							
								// membership renewal history
								//if (!empty($ins['card_no'])){
									$mh_ins = array();
									$mh_ins['nric'] = $ins['nric'];
									$mh_ins['card_no'] = $ins['card_no'];
									$mh_ins['branch_id'] = $ins['apply_branch_id'];
									$mh_ins['card_type'] = "N";
									$mh_ins['issue_date'] = $ins['issue_date'];
									$mh_ins['expiry_date'] = $ins['next_expiry_date'];
									$mh_ins['remark'] = "N";
									$mh_ins['user_id'] = 1;
									$mh_ins['added'] = 'CURRENT_TIMESTAMP';
							
									$con->sql_query("insert into membership_history ".mysql_insert_by_field($mh_ins, false, true));
								//}
								//print $ins['name']." imported.\n";
							}else{
								$nric_list[] = $nric;
							}
							
							
							if($ins['points']){
								if($is_run){
									$mp = array();
									$mp['nric'] = $ins['nric'];
									$mp['card_no'] = $ins['card_no'];
									$mp['branch_id'] = $ins['apply_branch_id'];
									$mp['date'] = date("Y-m-d");
									$mp['points'] = $ins['points'];
									$mp['type'] = 'ADJUST';
									$mp['user_id'] = 1;
									$mp['remark'] = $mp['point_source'] = 'MIGRATION';
									$con->sql_query("insert into membership_points ".mysql_insert_by_field($mp));
								}
								
							}
							$imported_count++;
						}
					}
				}
			}
		}
		
		if($err_msg){
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	
	fclose($f);
	fclose($fp);
		
	print $imported_count." member(s) imported of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid member: $invalid_count. plesae check invalid_member.csv\n";
}


function update_brand(){
	global $con;

	$ttl_count = $updated_count = $invalid_count = 0;
	$f = fopen("update_brand.csv","rt");
	$fp = fopen('invalid_update_brand.csv', 'w');
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = "";
		$brand_id = 0;
		
		$upd = array();
		$code = trim($r[0]);
		
		if(!$code){
			$err_msg = "Code is empty";
		}else{
			$brand_id = get_brand_by_code($code);
			if(!$brand_id)	$err_msg = "brand not found";
		}	
		
		$upd['description'] = trim($r[1]);
		
		
		if(!$err_msg && $brand_id){
			$con->sql_query("update brand set ".mysql_update_by_field($upd)." where id=$brand_id");
			
			$updated_count++;
		}else{
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	fclose($f);
	fclose($fp);
	
	print $updated_count." Brands(s) updated of total ".$ttl_count."\n";
	if($invalid_count >0)	print "Invalid brand: $invalid_count. plesae check invalid_update_brand.csv\n";
}

function update_sku_packing_uom($real_update = 0){
	global $con;

	$ttl_count = $updated_count = $invalid_count = $skip_count = 0;
	$f = fopen("update_sku_packing_uom.csv","rt");
	$fp = fopen('invalid_update_sku_packing_uom.csv', 'w');
	$line = fgetcsv($f);
	$line[] = "Error Message";
	fputcsv($fp, $line);
	
	$uom_code_list = array();
	
	$con->sql_query("select id,code from uom");
	while($r = $con->sql_fetchassoc()){
		$uom_code_list[strtoupper($r['code'])] = $r['id'];
	}
	$con->sql_freeresult();
	
	while($r = fgetcsv($f)){
		$ttl_count++;
		$err_msg = "";
		$uom_id = 0;
		print "\r$ttl_count. . . .";
		
		$upd = array();
		$mcode = trim($r[0]);
		$uom_code = strtoupper(trim($r[1]));
		$si = array();
		
		if(!$mcode)	$err_msg = "No MCode";
		if(!$err_msg && !$uom_code)	$err_msg = "No UOM Code";
		if(!$err_msg)	$uom_id = $uom_code_list[$uom_code];
		if(!$err_msg && !$uom_id)	$err_msg = "UOM Not Found";
		
		if(!$err_msg){
			$con->sql_query("select id,packing_uom_id,is_parent from sku_items where mcode=".ms($mcode)." order by id limit 1");
			$si = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$si)	$err_msg = "MCode Not Found";
			
			if(!$err_msg){
				if($si['packing_uom_id'] == $uom_id){	// uom id same, skip this sku
					$skip_count++;
					continue;
				}
				
				if($si['is_parent'] && $uom_id != 1)	$err_msg = "Parent Must use EACH";
			}
		}
		if(!$err_msg){
			if($real_update){
				$con->sql_query("update sku_items set packing_uom_id=".mi($uom_id).", lastupdate=lastupdate where id=".mi($si['id']));
			}
			$updated_count++;
		}else{
			$r[] = $err_msg;
			fputcsv($fp, $r);
			$invalid_count++;
		}
	}
	fclose($f);
	fclose($fp);
	
	print $updated_count." sku(s) updated of total ".$ttl_count.", Skip: $skip_count\n";
	if($invalid_count >0)	print "Invalid sku: $invalid_count. plesae check invalid_update_sku_packing_uom.csv\n";
}

function import_sku($filename, $real_import = false){
	global $con, $config;
	
	if(!$filename || !file_exists($filename))	die("File not exists.\n");
	
	// load gst information
	$q1 = $con->sql_query("select * from gst");
	$input_gst_list = $output_gst_list = $mprice_list = array();
	while($gst = $con->sql_fetchassoc($q1)){
		if($gst['type']=="supply") $output_gst_list[$gst['code']] = $gst;
		else $input_gst_list[$gst['code']] = $gst;
	}
	$con->sql_freeresult($q1);
	
	//load sku_multiple_selling_price
	if($config['sku_multiple_selling_price']) {
		$mprice_list = $config['sku_multiple_selling_price'];
	}
	
	$ttl_count = $imported_count = $error_count = 0;
	$f = fopen($filename,"rt");
	$line = fgetcsv($f);
	
	$mprice_error = $ins_mprice = array();
	$mprice_starting_column = 33;
	for($i=$mprice_starting_column ; $i<count($line); $i++) {
		$mprice_type = strtolower($line[$i]);
		if(!in_array($mprice_type, $mprice_list)) {
			$mprice_error[] = ms($line[$i]);
			continue;
		}
		if($mprice_type == 'wholesale1'){
			die("Cannot redeclar wholesale1.\n");
		}
		$ins_mprice[] = $mprice_type;
	}
	
	if($mprice_error) {
		die("Multiple selling price: ".join(', ', $mprice_error)." is not existed.\n");
	}
	
	$allowed_sku_type = array("CONSIGNMENT"=>'CONSIGN',"CONSIGN"=>'CONSIGN',"OUTRIGHT"=>'OUTRIGHT');
	$infile_parent_data = array();
	$default_line_id = $default_vendor_id = 0;
	$default_dept_id = $default_cat1_id = array();
			
	$error_header = array('mcode','link_code','artno','description','receipt_description','uom','cost_price','selling_price','scale_type','wholesale1','line','department','category1','category2','category3','category4','category5','brand','vendor','color','size','input_tax','output_tax','sku_type','po_reorder_qty_min','po_reorder_qty_max','parent_arms_code','parent_mcode','parent_artno','trade_discount_type','trade_discount_code','trade_discount_rate');
	$error_header = array_merge($error_header, $ins_mprice);
	$error_header[] = "error";
	$invalid_filename = 'invalid_'.$filename; 
	$fp = fopen($invalid_filename, 'w');
	fputcsv($fp, $error_header);
	
	$branches_list = array();	
	$con->sql_query("select id,code from branch order by id");
	while($r = $con->sql_fetchassoc()){
		$branches_list[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	while($r = fgetcsv($f)){
		$ins = array();
		
		// no mcode, linkcode, artno
		if(!trim($r[0]) && !trim($r[1]) && !trim($r[2])){
			if(trim($r[3]) || trim($r[4])){	// but got product description or receipt description
				$ins['error'] = "Empty MCode, Link Code and Art No";
			}else{
				continue;
			}			
		}
		$ttl_count++;
		print "\r$ttl_count. . . . .";
		
		// fix all text that contains special character to convert into utf8
		foreach($r as $tmp_row => $val){
			// replace Microsoft Word version of single  and double quotations marks (“ ” ‘ ’) with  regular quotes (' and ")
			$val = iconv('UTF-8', 'ASCII//TRANSLIT', trim($val));
			$r[$tmp_row] = utf8_encode(trim($val));
		}
		//print_r($r);exit;
		
		// reset variable
		$parent_si = array();
		$is_parent = 0;
		$vendor_id = $brand_id = 0;
		$cat_id = $cat1_id = $cat2_id = $cat3_id = $cat4_id = $cat5_id = 0;
		$line_id = $dept_id = 0;
		$sku_id = 0;
		
		$ins['mcode'] = preg_replace("/[^A-Za-z0-9]/", "", trim($r[0]));
		$ins['link_code'] = trim($r[1]);
		$ins['artno'] = trim($r[2]);			
		$ins['description'] = trim($r[3]);
		if($config['masterfile_disallow_double_quote']){
			$ins['description'] = str_replace('"', "", $ins['description']);
		}
		$ins['receipt_description'] = trim($r[4]);
		if($config['masterfile_disallow_double_quote']){
			$ins['receipt_description'] = str_replace('"', "", $ins['receipt_description']);
		}
		if(!$ins['receipt_description'])	$ins['receipt_description'] = $ins['description'];
		$ins['uom'] = strtoupper(trim($r[5]));
		if(!$ins['uom'] || $ins['uom'] == "PC" || $ins['uom'] == "PCS") $ins['uom'] = "EACH";
		$ins['cost_price'] = floatval(trim($r[6]));
		$ins['selling_price'] = floatval($r[7]);
		$ins['scale_type'] = trim($r[8]);
		$ins['wholesale1'] = trim($r[9]);
		$ins['line'] = trim($r[10]);
		$ins['department'] = trim($r[11]);
		$ins['category1'] = trim($r[12]);
		$ins['category2'] = trim($r[13]);
		$ins['category3'] = trim($r[14]);
		$ins['category4'] = trim($r[15]);
		$ins['category5'] = trim($r[16]);
		$ins['brand'] = strtoupper(trim($r[17]));
		$ins['vendor'] = strtoupper(trim($r[18]));
		$ins['color'] = strtoupper(trim($r[19]));
		$ins['size'] = strtoupper(trim($r[20]));
		$ins['inclusive_tax'] = 'inherit';
		$ins['input_tax'] = strtoupper(trim($r[21]));
		$ins['output_tax'] = strtoupper(trim($r[22]));
		$sku_type = strtoupper(trim($r[23]));
		if($sku_type==""){
			$ins['sku_type']="OUTRIGHT";
		}else{
			$ins['sku_type'] = $allowed_sku_type[$sku_type];
		}	
		//print $ins['uom']."\n";continue;
		$ins['po_reorder_qty_min'] = trim($r[24]);
		$ins['po_reorder_qty_max'] = trim($r[25]);
		$ins['parent_arms_code'] = trim($r[26]);
		$ins['parent_mcode'] = preg_replace("/[^A-Za-z0-9]/", "", trim($r[27]));
		$ins['parent_artno'] = trim($r[28]);
		$ins['trade_discount_type'] = strtolower(trim($r[29]));
		$ins['trade_discount_code'] = strtoupper(trim($r[30]));
		$ins['trade_discount_rate'] = round(floatval(trim($r[31])), 4);
		
		for($i=0; $i<count($ins_mprice); $i++) {
			$ins[$ins_mprice[$i]] = round(trim($r[$mprice_starting_column+$i]), 2);
		}
		
		// check for duplication
		if($ins['mcode']){
			// check from db for duplication
			$q1 = $con->sql_query("select id from sku_items where mcode = ".ms($ins['mcode'])." limit 1");
			if($con->sql_numrows($q1) > 0) $ins['error'] = 'mcode exists';
			$con->sql_freeresult($q1);
		}
		
		if(!$ins['error'] && strlen($ins['uom'])>6){
			$ins['error'] = "UOM Code cannot exceed 6 characters";
		}
		
		// check inclusive tax
		/*if(!$ins['error']){
			if(!$ins['inclusive_tax'])	$ins['inclusive_tax'] = 'inherit';
			elseif($ins['inclusive_tax'] != 'yes' && $ins['inclusive_tax'] != 'no'){
				$ins['error'] = 'wrong inclusive_tax';
			}
		}*/
		
		//check input an doutput tax code
		if(!$ins['error'] && $ins['input_tax'] && !isset($input_gst_list[$ins['input_tax']]))	$ins['error'] = "Incorrect Input Tax Code";
		if(!$ins['error'] && $ins['output_tax'] && !isset($output_gst_list[$ins['output_tax']])) $ins['error'] = "Incorrect Output Tax Code";
		
		if(!$ins['error']){
			// check parent
			if(!$ins['parent_arms_code'] && !$ins['parent_mcode'] && !$ins['parent_artno']){
				// this item is parent
				$is_parent = 1;
			}else{
				// check for existence of arms code based on parent arms code, mcode or artno
				if($ins['parent_arms_code']) {
					// got arms code, check arms code only
					$q2 = $con->sql_query("select id,sku_id from sku_items where sku_item_code = ".ms($ins['parent_arms_code'])." and is_parent = 1");
					$parent_si = $con->sql_fetchassoc($q2);
					if(!$parent_si) $ins['error'] = 'parent_arms_code not found';
					$con->sql_freeresult($q2);				
				}elseif($ins['parent_mcode'] || $ins['parent_artno']){
					if(($ins['parent_mcode'] && $ins['parent_mcode']==$ins['mcode'])){
						// this item is parent
						$is_parent = 1;
					}else{
						// no arms code, match either mcode or artno
						if($ins['parent_mcode']) {
							if(!$real_import && isset($infile_parent_data['parent_mcode'][$ins['parent_mcode']])){
								$parent_si = $infile_parent_data['parent_mcode'][$ins['parent_mcode']];
							}else{
								$q2 = $con->sql_query("select id,sku_id from sku_items where mcode = ".ms($ins['parent_mcode'])." and is_parent = 1 order by id limit 1");
								$parent_si = $con->sql_fetchassoc($q2);
								if(!$parent_si) {
									$ins['error'] = 'parent_mcode not found';
								}
								$con->sql_freeresult($q2);
							}
						}
						
						if(!$parent_si && $ins['parent_artno']) {
							if(!$real_import && isset($infile_parent_data['parent_artno'][$ins['parent_artno']])){
								$parent_si = $infile_parent_data['parent_artno'][$ins['parent_artno']];
							}else{
								$q2 = $con->sql_query("select id,sku_id from sku_items where artno = ".ms($ins['parent_artno'])." and is_parent = 1 order by id limit 1");
								$parent_si = $con->sql_fetchassoc($q2);
								$con->sql_freeresult($q2);
								
								if(!$parent_si) {
									if($ins['parent_artno']==$ins['artno']){
										// this item is parent
										$is_parent = 1;
									}else{
										$ins['error'] = 'parent_artno not found';
									}									
								}								
							}							
						}
					}
				}
				
				if($real_import && !$is_parent && !$parent_si['sku_id'] && !$ins['error']){
					$ins['error'] = "system cannot found sku_id";
				}
			}
			
			if(!$ins['error']){
				// check uom errror for parent
				if($is_parent && $ins['uom'] != "EACH") {
					$ins['error'] = 'parent uom must be EACH';
				}
			}
			
			if(!$ins['error']){
				// check sku type
				if(!$ins['sku_type']){
					$ins['error'] = 'invalid sku_type';
				}
			}
			
			// check trade discount info when sku type is CONSIGN
			if(!$ins['error'] && $ins['sku_type'] == "CONSIGN"){
				$td_type_list = array("brand", "vendor");
				$td_code_list = load_price_type_list();
				
				// check if trade discount type is empty or invalid (Brand or Vendor)
				$is_valid_tdt = false;
				if(!in_array($ins['trade_discount_type'], $td_type_list)) $ins['error'] = "Invalid Trade Discount Type";
				else $is_valid_tdt = true;
				
				// check if trade discount code is empty or invalid (B1, B2 and ...)
				$is_valid_tdc = false;
				if(!$ins['error'] && !$td_code_list[$ins['trade_discount_code']]) $ins['error'] = "Invalid Trade Discount Code";
				else $is_valid_tdc = true;
				
				// check if trade discount rate is valid
				if(!$ins['error'] && $is_valid_tdt && $is_valid_tdc){
					$line_id = get_cat_by_desc($ins['line'], 0, 1);
					
					// need to select default line if couldn't found
					if(!$line_id && !$ins['line']){
						$con->sql_query("select id from category where description = 'LINE' and level = 1 limit 1");
						$line_id = $con->sql_fetchfield(0);
						$con->sql_freeresult();	
					}
					
					$dept_id = get_cat_by_desc($ins['department'], $line_id, 2);
					if(!$dept_id && !$ins['department']){
						$con->sql_query("select id from category where description = 'DEPARTMENT' and level = 2 and root_id=".mi($line_id)." limit 1");
						$dept_id = $con->sql_fetchfield(0);
						$con->sql_freeresult();
					}
					
					$line_id = mi($line_id); // possible is zero
					$dept_id = mi($dept_id); // possible is zero
					$filters = array();
					if($ins['trade_discount_type'] == "vendor"){
						$vendor_id = get_vendor_by_desc($ins['vendor']);
						
						// if not found the vendor ID, try to search 
						if(!$vendor_id && !$ins['vendor']){
							$con->sql_query("select id from vendor where code = 'Vendor'");
							$vendor_id = $con->sql_fetchfield(0);
							$con->sql_freeresult();
						}
						
						$filters[] = "vendor_id = ".mi($vendor_id);
						if($vendor_id) $arr_key_id = $vendor_id;
						else $arr_key_id = $ins['vendor'];
					}else{
						$brand_id = get_brand_by_desc($ins['brand']);
						
						$filters[] = "brand_id = ".mi($brand_id);
						
						// check if the discount table type is BRAND and show errors if doesn't assign any brand on the csv
						if($brand_id){
							$arr_key_id = $brand_id;
						}else{ // must not allow user to leave brand empty when it is CONSIGN SKU
							if($ins['brand']) $arr_key_id = $ins['brand'];
							else $ins['error'] = "Invalid Brand";
						}
						
					}
					
					if(!$ins['error']){
						// table selection
						$tbl_name = $ins['trade_discount_type']."_commission";
						$filters[] = "branch_id = 1 and department_id = ".mi($dept_id)." and skutype_code = ".ms($ins['trade_discount_code'])." and rate > 0";
						$q1 = $con->sql_query("select * from $tbl_name where ".join(" and ", $filters));
						$db_td_info = $con->sql_fetchassoc($q1);
						
						if($con->sql_numrows($q1) > 0){ // if system has the rate
							// if found user did not put discount rate, meanwhile use the one from system 
							if(!$ins['trade_discount_rate']) $ins['trade_discount_rate'] = $db_td_info['rate']; 
							
							if($db_td_info['rate'] > 0 && $ins['trade_discount_rate'] != $db_td_info['rate']) $ins['error'] = "Trade Discount Rate [".mf($ins['trade_discount_rate'])."] is different with system [".mf($db_td_info['rate'])."]";
							elseif(!$ins['trade_discount_rate']) $ins['error'] = "Invalid Trade Discount Rate";
						}elseif($ins['trade_discount_rate'] > 0 && isset($trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']]) && $trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']] != $ins['trade_discount_rate']){ // if having different rate but same disc type, dept_id, disc code within the CSV
							$ins['error'] = "Contains multiple Trade Discount Rate";
						}elseif(!$trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']]){ // if rate couldn't be found from both system and array list, then prompt error
							if($ins['trade_discount_rate'] == 0) $ins['error'] = "Invalid Trade Discount Rate";
						}elseif($con->sql_numrows($q1) == 0 && !$ins['trade_discount_rate']){ // no data from database and didn't provide any rate
							$error[] = "Invalid Trade Discount Rate";
						}
						$con->sql_freeresult($q1);
						
						if($ins['trade_discount_rate']){
							// get the cost using selling price deduct from trade disc rate
							$latest_cost = round(($ins['selling_price']*(100-$ins['trade_discount_rate']))/100, $config['global_cost_decimal_points']);
						
							// if found got assigned cost price but different with the calculated cost, prompt error
							if(!$ins['error'] && $ins['cost_price'] && $ins['cost_price'] != $latest_cost) $ins['error'] = "Incorrect Cost [should be $latest_cost]";
						}
						
						if($ins['trade_discount_rate'] && !isset($trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']])){
							$trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']] = $ins['trade_discount_rate'];
						}
					}
				}
			}
		}
		
		if($ins['error']){
			$r[] = $ins['error'];
			fputcsv($fp, $r);
			$error_count++;
		}else{
			if($is_parent && !$real_import){
				if($ins['mcode'])	$infile_parent_data['parent_mcode'][$ins['mcode']] = $ins;
				if($ins['artno'])	$infile_parent_data['parent_artno'][$ins['artno']] = $ins;
			}
			
			// only continue if it is real import
			if(!$real_import)	continue;
			
			// get max sku code - no longer use
			/*$con->sql_query("select max(sku_code) from sku where sku_code like '28%'");
			$sku_code = mi($con->sql_fetchfield(0));
			$con->sql_freeresult();
			
			if(!$sku_code)	$sku_code = 28000000;*/
			
			$sku_ins = array();	
			$si_ins = array();

			// check uom for insert new or use back existing
			$uom_code = $ins['uom'];
			
			// when is valid and not EACH while add
			if(!$uom_code_list[$uom_code]){
				$q_uom = $con->sql_query("select id from uom where code = ".ms($uom_code));
				$tmp = $con->sql_fetchassoc();
				$con->sql_freeresult($q_uom);
				
				if(!$tmp){
					$uom_ins = array();
					$uom_ins['code'] = $uom_code;
					$uom_ins['description'] = $uom_code;
					$uom_ins['fraction'] = 1;
					$uom_ins['active'] = 1;
					$con->sql_query("insert into uom ".mysql_insert_by_field($uom_ins));
					$uom_code_list[$uom_code] = $uom_id = intval($con->sql_nextid());
				}else{
					$uom_id = $uom_code_list[$uom_code] = $tmp['id'];
				}
			}else{
				$uom_id = $uom_code_list[$uom_code];
			}
			
			if(!$uom_id) $uom_id = 1;
			
			// CATEGORY SEARCHING FROM HERE
			$line_id = get_cat_by_desc($ins['line'],0, 1);
			
			// LINE
			// if found the line from csv is empty, insert default line
			if(!$line_id){
				if(!$ins['line']){
					if(!$default_line_id){
						$con->sql_query("select id from category where description = 'LINE' and level = 1 limit 1");
						$default_line_id = mi($con->sql_fetchfield(0));
						$con->sql_freeresult();
						
						if(!$default_line_id){
							$upd = array();
							$upd['level'] = 1;
							$upd['description'] = 'LINE';
							$upd['active'] = 1;
							$upd['tree_str'] = '(0)';
							$upd['no_inventory'] = 'no';
							$upd['is_fresh_market'] = 'no';
							$con->sql_query("insert into category ".mysql_insert_by_field($upd));
							$default_line_id = $con->sql_nextid();
							$con->sql_query("update category set code = ".mi($default_line_id).", department_id = ".mi($default_line_id)." where id = ".mi($default_line_id)." and level = 1");
						}
					}
					$line_id = $default_line_id;
				}else{
					$upd = array();
					$upd['level'] = 1;
					$upd['description'] = $ins['line'];
					$upd['active'] = 1;
					$upd['tree_str'] = '(0)';
					$upd['no_inventory'] = 'no';
					$upd['is_fresh_market'] = 'no';
					
					$con->sql_query("insert into category ".mysql_insert_by_field($upd));
					$line_id = $con->sql_nextid();
					$con->sql_query("update category set code = ".mi($line_id).", department_id = ".mi($line_id)." where id = ".mi($line_id)." and level = 1");
				}
			}
			
			// DEPARTMENT
			$dept_id = get_cat_by_desc($ins['department'], $line_id, 2);
			if(!$dept_id){
				// if found the department from csv is empty, insert default department
				if(!$ins['department']){
					if(!$default_dept_id[$line_id]){
						$con->sql_query("select id from category where description = 'DEPARTMENT' and level = 2 and root_id=$line_id limit 1");
						$default_dept_id[$line_id] = $con->sql_fetchfield(0);
						$con->sql_freeresult();
						
						if(!$default_dept_id[$line_id]){
							$upd = array();
							$upd['root_id'] = $line_id;
							$upd['level'] = 2;
							$upd['description'] = 'DEPARTMENT';
							$upd['active'] = 1;
							$upd['tree_str'] = '(0)('.$line_id.')';
							$con->sql_query("insert into category ".mysql_insert_by_field($upd));
							$default_dept_id[$line_id] = $con->sql_nextid();
							$con->sql_query("update category set code = ".mi($default_dept_id[$line_id]).", department_id = ".mi($default_dept_id[$line_id])." where id = ".mi($default_dept_id[$line_id])." and level = 2");
						}
					}
					$dept_id = $default_dept_id[$line_id];
				}else{
					// check and insert department
					$upd = array();
					$upd['root_id'] = $line_id;
					$upd['level'] = 2;
					$upd['description'] = $ins['department'];
					$upd['active'] = 1;
					$upd['tree_str'] = '(0)('.$line_id.')';
					$con->sql_query("insert into category ".mysql_insert_by_field($upd));
					$dept_id = $con->sql_nextid();
					$con->sql_query("update category set code = ".mi($dept_id).", department_id=".mi($dept_id)." where id=".mi($dept_id)." and level = 2");
				}
			}
			
			// CATEGORY 1
			$cat1_id = get_cat_by_desc($ins['category1'], $dept_id, 3);
			// if found the category from csv is empty, insert default category
			if(!$cat1_id){
				if(!$ins['category1']){
					if(!$default_cat1_id[$line_id][$dept_id]){
						$con->sql_query("select id from category where description = 'CATEGORY' and root_id = ".mi($dept_id)." and level = 3 limit 1");
						$default_cat1_id[$line_id][$dept_id] = $con->sql_fetchfield(0);
						$con->sql_freeresult();
						
						if(!$default_cat1_id[$line_id][$dept_id]){
							$upd = array();
							$upd['root_id'] = $dept_id;
							$upd['level'] = 3;
							$upd['description'] = 'CATEGORY';
							$upd['active'] = 1;
							$upd['department_id'] = $dept_id;
							$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')';
							$con->sql_query("replace into category ".mysql_insert_by_field($upd));
							$default_cat1_id[$line_id][$dept_id] = $con->sql_nextid();
							$con->sql_query("update category set code = ".mi($default_cat1_id[$line_id][$dept_id])." where id = ".mi($default_cat1_id[$line_id][$dept_id])." and level = 3");
						}
					}
					$cat1_id = $default_cat1_id[$line_id][$dept_id];
				}else{
					$upd = array();
					$upd['root_id'] = $dept_id;
					$upd['level'] = 3;
					$upd['description'] = $ins['category1'];
					$upd['active'] = 1;
					$upd['department_id'] = $dept_id;
					$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')';
					$con->sql_query("insert into category ".mysql_insert_by_field($upd));
					$cat1_id = $con->sql_nextid();
					$con->sql_query("update category set code = ".mi($cat1_id)." where id=".mi($cat1_id)." and level = 3");
				}
			}
			
			$cat_id = $cat1_id;
			
			// CATEGORY 2
			// if found the category from csv is empty, insert default category
			if($ins['category2']){
				$cat2_id = get_cat_by_desc($ins['category2'], $cat1_id, 4);
				if(!$cat2_id){
					$upd = array();
					$upd['root_id'] = $cat1_id;
					$upd['level'] = 4;
					$upd['description'] = $ins['category2'];
					$upd['active'] = 1;
					$upd['department_id'] = $dept_id;
					$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')('.$cat1_id.')';
					$con->sql_query("insert into category ".mysql_insert_by_field($upd));
					$cat2_id = $con->sql_nextid();
					$con->sql_query("update category set code = ".mi($cat2_id)." where id=".mi($cat2_id)." and level = 4");
				}
				$cat_id = $cat2_id;
				
				// CATEGORY 3
				// if found the category from csv is empty, insert default category
				if($ins['category3']){
					$cat3_id = get_cat_by_desc($ins['category3'], $cat2_id, 5);
					if(!$cat3_id){
						$upd = array();
						$upd['root_id'] = $cat2_id;
						$upd['level'] = 5;
						$upd['description'] = $ins['category3'];
						$upd['active'] = 1;
						$upd['department_id'] = $dept_id;
						$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')('.$cat1_id.')('.$cat2_id.')';
						$con->sql_query("insert into category ".mysql_insert_by_field($upd));
						$cat3_id = $con->sql_nextid();
						$con->sql_query("update category set code = ".mi($cat3_id)." where id=".mi($cat3_id)." and level = 5");
					}
					$cat_id = $cat3_id;
					
					// CATEGORY 4
					// if found the category from csv is empty, insert default category
					if($ins['category4']){
						$cat4_id = get_cat_by_desc($ins['category4'], $cat3_id, 6);
						if(!$cat4_id){
							$upd = array();
							$upd['root_id'] = $cat3_id;
							$upd['level'] = 6;
							$upd['description'] = $ins['category4'];
							$upd['active'] = 1;
							$upd['department_id'] = $dept_id;
							$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')('.$cat1_id.')('.$cat2_id.')('.$cat3_id.')';
							$con->sql_query("insert into category ".mysql_insert_by_field($upd));
							$cat4_id = $con->sql_nextid();
							$con->sql_query("update category set code = ".mi($cat4_id)." where id=".mi($cat4_id)." and level = 6");
						}
						$cat_id = $cat4_id;
						
						// CATEGORY 5
						if($ins['category5']){
							// check and insert category
							$cat5_id = get_cat_by_desc($ins['category5'], $cat4_id, 7);
							if(!$cat5_id){
								$upd = array();
								$upd['root_id'] = $cat4_id;
								$upd['level'] = 7;
								$upd['description'] = trim($r[16]);
								$upd['active'] = 1;
								$upd['department_id'] = $dept_id;
								$upd['tree_str'] = '(0)('.$line_id.')('.$dept_id.')('.$cat1_id.')('.$cat2_id.')('.$cat3_id.')('.$cat4_id.')';
								$con->sql_query("insert into category ".mysql_insert_by_field($upd));
								$cat5_id = $con->sql_nextid();
								$con->sql_query("update category set code = ".mi($cat5_id)." where id=".mi($cat5_id)." and level = 7");
							}
							$cat_id = $cat5_id;
						}
					}
				}
			}
			
			// check and insert brand
			if($ins['brand']){
				$brand_id = get_brand_by_desc($ins['brand']);
				if(!$brand_id){
					$upd = array();		
					$upd['code'] = $ins['brand'];
					$upd['description'] = $ins['brand'];
					$upd['active'] = 1;
					$con->sql_query("insert into brand ".mysql_insert_by_field($upd));
					$brand_id = $con->sql_nextid();
				}
			}else $brand_id = 0;
			$brand_id = mi($brand_id);
			
			// check and insert vendor
			if(!$ins['vendor']){
				if(!$default_vendor_id){
					$con->sql_query("select id from vendor where code = 'Vendor'"); // check db is it still existed...
					$default_vendor_id = mi($con->sql_fetchfield(0));
					$con->sql_freeresult();
					if(!$default_vendor_id){
						$upd = array();
						$upd['code'] = 'Vendor';
						$upd['description'] = 'Vendor';
						$upd['active'] = 1;
						$con->sql_query("insert into vendor ".mysql_insert_by_field($upd));
						$default_vendor_id = $con->sql_nextid();
					}
				}
				$vendor_id = $default_vendor_id;
			}else{
				$vendor_id = get_vendor_by_desc($ins['vendor']);
				if(!$vendor_id){
					//$con->sql_query("select max(id) from vendor");
					//$max_vendor_id = mi($con->sql_fetchfield(0))+1;
					//$con->sql_freeresult();
					
					$upd = array();		
					//$upd['code'] = $ins['vendor'];
					//$upd['code'] = $max_vendor_id;
					$upd['description'] = $ins['vendor'];
					$upd['active'] = 1;
					$con->sql_query("insert into vendor ".mysql_insert_by_field($upd,false,1));
					$vendor_id = $con->sql_nextid();
					$con->sql_query_false("update vendor set code=".ms($vendor_id)." where id=".mi($vendor_id));
				}
			}
			$vendor_id = mi($vendor_id);
			
			// insert or update trade discount info
			if($ins['sku_type'] == "CONSIGN"){
				$filters = array();
				if($ins['trade_discount_type'] == "vendor"){
					$filters[] = "vendor_id = ".mi($vendor_id);
					$upd_key = "vendor_id";
					$upd_val = $vendor_id;
				}else{
					$filters[] = "brand_id = ".mi($brand_id);
					$upd_key = "brand_id";
					$upd_val = $brand_id;
				}
				
				// table selection
				$tbl_name = $ins['trade_discount_type']."_commission";
				$filters[] = "branch_id = 1 and department_id = ".mi($dept_id)." and skutype_code = ".ms($ins['trade_discount_code']);
				$q1 = $con->sql_query("select * from $tbl_name where ".join(" and ", $filters));
				$db_td_info = $con->sql_fetchassoc($q1);
				
				if(!$db_td_info['rate']){ // need to insert the rate
					$comm_upd = array();
					$comm_upd['rate'] = $ins['trade_discount_rate'];
					
					if($con->sql_numrows($q1) > 0){ // do update
						$con->sql_query("update $tbl_name set ".mysql_update_by_field($comm_upd)." where ".join(" and ", $filters));
					}else{ // do insert
						$comm_upd[$upd_key] = $upd_val;
						$comm_upd['branch_id'] = 1;
						$comm_upd['department_id'] = $dept_id;
						$comm_upd['skutype_code'] = $ins['trade_discount_code'];
						
						$con->sql_query("replace into $tbl_name ".mysql_insert_by_field($comm_upd));
					}

					// get the cost using selling price deduct from trade disc rate
					$latest_cost = round(($ins['selling_price']*(100-$ins['trade_discount_rate']))/100, $config['global_cost_decimal_points']);
					
					// if found doesn't have cost price, use the calculated cost price
					if(!$ins['cost_price'])	$ins['cost_price'] = $latest_cost;
					
				}
				
				// create commission history
				$prms = array();
				$prms['tbl_type'] = $ins['trade_discount_type'];
				$prms[$upd_key] = $upd_val;
				$prms['branch_id'] = 1;
				$prms['department_id'] = $dept_id;
				$prms['skutype_code'] = $ins['trade_discount_code'];
				$prms['rate'] = $ins['trade_discount_rate'];
				
				create_commission_history($prms);
			}
			
			if($is_parent){
				// insert as parent
				//$sku_code++;
				//$sku_ins['sku_code'] = $sku_code;
				$sku_ins['category_id'] = $cat_id;
				$sku_ins['uom_id'] = $uom_id;
				$sku_ins['vendor_id'] = $vendor_id;
				$sku_ins['brand_id'] = $brand_id;
				$sku_ins['status'] = 1;
				$sku_ins['active'] = 1;
				$sku_ins['sku_type'] = $ins['sku_type'];
				$sku_ins['apply_branch_id'] = 1;
				$sku_ins['added'] = 'CURRENT_TIMESTAMP';
				if($ins['po_reorder_qty_min'] && $ins['po_reorder_qty_max']) {
					$sku_ins['po_reorder_qty_min'] = $ins['po_reorder_qty_min'];
					$sku_ins['po_reorder_qty_max'] = $ins['po_reorder_qty_max'];
				}
		
				if($ins['scale_type'] == "WEIGH" || $ins['scale_type'] == "WEIGHT") $sku_ins['scale_type'] = 2;
				elseif($ins['scale_type'] >=0 && $ins['scale_type']<=2){
					$sku_ins['scale_type'] = mi($ins['scale_type']);
				}else{
					$sku_ins['scale_type'] = 0;
				}
				
				if($ins['sku_type'] == "CONSIGN"){
					$sku_ins['default_trade_discount_code'] = $ins['trade_discount_code'];
					if($ins['trade_discount_type'] == "brand") $sku_ins['trade_discount_type'] = 1;
					else $sku_ins['trade_discount_type'] = 2;
				}
		
				$con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
				$sku_id = intval($con->sql_nextid());
				$sku_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id);
				$con->sql_query("update sku set sku_code=".ms($sku_code)." where id=$sku_id");
				
				//$sku_item_code = $sku_code.'0000';
				$sku_item_code = sprintf(ARMS_SKU_CODE_PREFIX, $sku_id)."0000";
				$si_ins['is_parent'] = 1;
				$si_ins['packing_uom_id'] = 1;
				$si_ins['sku_item_code'] = $sku_item_code;
			}else{
				// insert as child
				$sku_id = mi($parent_si['sku_id']);
				
				$q2 = $con->sql_query("select max(sku_item_code) as sku_item_code from sku_items where sku_id = $sku_id");
				$tmp = $con->sql_fetchassoc($q2);
				$con->sql_freeresult($q2);
				$si_ins['sku_item_code'] = $tmp['sku_item_code']+1;
				$si_ins['packing_uom_id'] = $uom_id;
			}
			
			$si_ins['sku_id'] = $sku_id;
			$si_ins['mcode'] = $ins['mcode'];
			$si_ins['link_code'] = $ins['link_code'];
			$si_ins['artno'] = $ins['artno'];
			$si_ins['description'] =  $ins['description'];
			$si_ins['receipt_description'] =  $ins['receipt_description'];
			if(!$si_ins['receipt_description']) $si_ins['receipt_description'] = $ins['description'];
			
			$si_ins['hq_cost'] = $si_ins['cost_price'] = $ins['cost_price'];
			$si_ins['selling_price'] = $ins['selling_price'];
			$si_ins['active'] = 1;
			$si_ins['added'] = 'CURRENT_TIMESTAMP';
			$si_ins['color'] = $ins['color'];
			$si_ins['size'] = $ins['size'];
			$si_ins['inclusive_tax'] = $ins['inclusive_tax'];
			if($si_ins['inclusive_tax'] != "yes" && $si_ins['inclusive_tax'] != "no") $si_ins['inclusive_tax'] = "inherit";
			
			$input_tax = strtoupper($ins['input_tax']);
			$si_ins['input_tax'] = $input_gst_list[$input_tax]['id'];
			if(!$si_ins['input_tax']) $si_ins['input_tax'] = -1;
			
			$output_tax = strtoupper($ins['output_tax']);
			$si_ins['output_tax'] = $output_gst_list[$output_tax]['id'];
			if(!$si_ins['output_tax']) $si_ins['output_tax'] = -1;
			
			$con->sql_query("insert into sku_items ".mysql_insert_by_field($si_ins));
			$sid = $con->sql_nextid();
			
			if($ins['wholesale1'] > 0){
				foreach($branches_list as $bid => $b_info){
					$ws1_ins = array();
					$ws1_ins['branch_id'] = $bid;
					$ws1_ins['sku_item_id'] = $sid;
					$ws1_ins['type'] = "wholesale1";
					$ws1_ins['last_update'] = "CURRENT_TIMESTAMP";
					$ws1_ins['price'] = $ins['wholesale1'];
					
					$con->sql_query("insert into sku_items_mprice ".mysql_insert_by_field($ws1_ins));
					
					unset($ws1_ins['last_update']);
					$ws1_ins['added'] = "CURRENT_TIMESTAMP";
					$ws1_ins['user_id'] = 1;
					$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($ws1_ins));
				}
				
			}
			
			for($i=0; $i<count($ins_mprice); $i++) {
				if($ins[$ins_mprice[$i]] > 0) {
					foreach($branches_list as $bid => $b_info){
						$mp_ins = array();
						$mp_ins['branch_id'] = $bid;
						$mp_ins['sku_item_id'] = $sid;
						$mp_ins['type'] = $ins_mprice[$i];
						$mp_ins['last_update'] = "CURRENT_TIMESTAMP";
						$mp_ins['price'] = $ins[$ins_mprice[$i]];
						$con->sql_query("insert into sku_items_mprice ".mysql_insert_by_field($mp_ins));
						
						unset($mp_ins['last_update']);
						$mp_ins['added'] = "CURRENT_TIMESTAMP";
						$mp_ins['user_id'] = 1;
						$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($mp_ins));
					}
					
				}
			}
			$imported_count++;
		}
	}
	
	fclose($f);
	fclose($fp);
	
	// did a category tree update for the new category
	if($real_import) build_category_cache();
		
	print "\nTotal Rows: $ttl_count, Imported Rows: $imported_count, Error Rows: ".$error_count."\n";
	if($error_count > 0)	print "Invalid sku: $error_count. plesae check $invalid_filename\n";
}

function load_price_type_list(){
	global $con, $smarty;
	
	$q1 = $con->sql_query("select * from trade_discount_type order by code");
	
	while($r = $con->sql_fetchassoc($q1)){
		$r['code'] = strtoupper($r['code']);
		$pt_list[$r['code']] = $r;
	}
	$con->sql_freeresult($q1);

	return $pt_list;
}

function create_commission_history($data){
	global $con;
	
	$tbl_type = $data['tbl_type'];
	$branch_id = mi($data['branch_id']);
	$skutype_code = $data['skutype_code'];
	$department_id = mi($data['department_id']);
	$rate = $data['rate'];
	
	// switch between vendor and brand
	if($tbl_type == "vendor"){
		$type_id = "vendor_id";
		$type_val = mi($data['vendor_id']);
		$filter = "vendor_id = ".mi($type_val);
	}else{
		$type_id = "brand_id";
		$type_val = mi($data['brand_id']);
		$filter = "brand_id = ".mi($type_val);
	}
	
	if(!$tbl_type || !$branch_id || !$type_val || !$skutype_code || !$department_id) return;
	
	$today = date("Y-m-d");
	
	$tbl_name = $tbl_type."_commission_history";
	$q1 = $con->sql_query("select * from $tbl_name where branch_id=$branch_id and $filter and department_id=$department_id and skutype_code=".ms($skutype_code)." and date_from!=".ms($today)." and date_to='9999-12-31'");
	$tmp = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if($tmp){
		$tmp['date_to'] = date("Y-m-d", strtotime("-1 day", strtotime($today)));
		$con->sql_query("replace into $tbl_name ".mysql_insert_by_field($tmp));
	}
	
	$upd = array();
	$upd['branch_id'] = $branch_id;
	$upd[$type_id] = $type_val;
	$upd['skutype_code'] = $skutype_code;
	$upd['department_id'] = $department_id;
	$upd['rate'] = $rate;
	$upd['date_from'] = $today;
	$upd['date_to'] = '9999-12-31';
	$con->sql_query("replace into $tbl_name ".mysql_insert_by_field($upd));
}

?>
