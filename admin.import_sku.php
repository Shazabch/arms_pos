<?php
/*
2/19/2014 4:49 PM Justin
- Bug fixed on exclude to count those empty row as total rows.

7/11/2014 10:50 AM Justin
- Enhanced to have 5 category levels.
- Enhanced to add checking for new privilege "ALLOW_IMPORT_SKU".

8/18/2014 3:09 PM Justin
- Enhanced to check if mcode is existed, treat it as duplicated instead of parent and child.

10/2/2014 2:52 PM Justin
- Enhanced to have capture Parent & Child by Artno/Old Code.

10/3/2014 10:40 AM Justin
- Bug fixed on system always do search for parent and child.
- Bug fixed system will capture category is null when an item contains no line, department and category.

10/9/2014 2:47 PM Justin
- Enhanced to have checking on UOM (if found got check by insert parent & child and first item UOM not "EACH", then the rest will capture as error).
- Enhanced the duplicate check to include UOM.

3/17/2015 3:41 PM Andy
- Fix if description got comma will cause import wrong data.

3/23/2015 9:41 AM Justin
- Bug fixed on having category 2 will cause get zero category_id.

3/31/2015 6:34 PM Justin
- Enhanced to urlencode every column.

8/25/2015 10:31 AM Justin
- Enhanced to only check MCode to decide whether item is duplicate or not.

11/30/2015 3:38 PM Justin
- Enhanced to have import GST information by SKU.

12/4/2015 10:24 AM Justin
- Bug fixed on valid item will treat as invalid item amd unable to import.

12/16/2015 2:20 PM DingRen
- add new column sku type

12/18/2015 3:33 PM DingRen
- add second data sample
- enhance SKU type checking

1/28/2016 6:04 PM Justin
- Bug fixed on gst information will not capture if item set as TX or SR.

2/23/2016 9:35 AM Qiu Ying
- Increase memory limit and execution time
- Modify load gst information in ajax_import_sku

04/26/2016 14:30 Edwin
- Enhanced on add or update PO max and min qty if filled
- Added parent_arms_code, parent_mcode and parent_artno to check and assign parent-child sku_items

6/24/2016 10:08 AM Andy
- Remove the size & color logic for receipt description.

07/27/2016 11:00 Edwin
- Change coding structure

8/19/2016 4:04 PM Andy
- Enhanced artno parent checking.

9/9/2016 3:07 PM Andy
- Fixed check mprice error.

9/14/2016 12:18 PM Andy
- Fixed import mprice error.

9/19/2016 2:55 PM Andy
- Fixed vendor duplicate bugs.

10/25/2016 9:43 AM Andy
- Fixed input tax and output tax validation bugs.
- Enhanced to remove double quotes for product description and receipt description.

10/27/2016 3:15 PM Andy
- Fixed double quotes checking should check config.masterfile_disallow_double_quote

1/10/2017 4:30 PM Andy
- Remove inclusive tax column.

1/19/2017 5:01 PM Andy
- Fixed create vendor to prevent duplicate code sql error.

2/17/2017 1:28 PM Zhi Kai
- Change wording of 'Genaral' PO Reorder Min Qty & 'Gerenal' PO Reorder Max Qty to 'General'.

2/20/2017 4:52 PM Justin
- Enhanced to re-generate category tree once SKU have been imported.

6/14/2017 10:53 AM Andy
- Change to allow access "Import SKU" if user is system admin or got privilege ALLOW_IMPORT_SKU.

10/30/2017 9:48 AM Justin
- Bug fixed on empty rows will still able to import while import file has "error" column.

6/1/2018 2:38 PM Justin
- Enhanced to have Consignment Table, Consignment Price Type, Consignment Discount Rate.
- Enhanced to check Input and Output tax base on purchase and supply GST list.

7/30/2018 11:45 AM Justin
- Bug fixed on not showing any error message while there is no data from database and didn't provide the rate from import file.

8/1/2018 10:23 AM Andy
- Remove character "%" from sample consignment discount rate column.

9/20/2018 5:37 PM Andy
- Fixed if import chinese character it will show as "?".

10/15/2018 3:06 PM Andy
- Fixed if import chinese character it will show as "?".

10/23/2018 11:49 AM Justin
- Enhanced the module to compatible with new SKU Type.
- Enhanced the remark to load the SKU Type from database instead of hardcoded.

3/5/2019 4:24 PM Andy
- Enhanced to remove sku description, receipt_description and category description line break.

3/11/2019 10:17 AM Andy
- Changed to always fix all column line breaks instead of selected column.

6/3/2019 3:59 PM William
- Added new moq by csv.
- Added new checking to disable Moq value larger than Max value.
- Added new checking to disable Min value equal Max value or large than Max value.
- Fixed extra empty row when show result.

6/18/2019 9:25 AM Andy
- Fixed sample csv got extra empty line.
- Removed checking in row filter.

9/15/2020 9:53 AM William
- Enhanced to added new import column "Additional_description".

10/9/2020 4:24 PM Andy
- Enhanced to auto detect last column name for display in note.

10/15/2020 2:10 PM William
- Enhanced import column "Additional_description". always showing whether config "sku_enable_additional_description" active or not.

10/29/2020 5:45 PM William
- Enhance to show "download error file" link before success to import sku.
- Enhanced to skip duplicate artno checking when mcode not duplicate.

11/17/2020 5:12 PM Andy
- Enhanced to can choose UOM for Parent SKU, but limited to uom with fraction = 1.

12/28/2020 10:58 AM William
- Enhanced to add new import column "RSP" and "RSP Discount" to Import SKU module.

12/30/2020 4:13 PM William
- Bug fixed use_rsp not mark as value 1 when rsp_discount equal 0.

*/

//$db_default_connection = array("localhost", "root", "", "yy");
include("include/common.php");
include("masterfile_sa_commission.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if ($sessioninfo['level'] < 9999) js_redirect("Do not have permission to access.", "/index.php");
if (!privilege('ALLOW_IMPORT_SKU') && $sessioninfo['level'] < 9999) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'ALLOW_IMPORT_SKU', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 600);

class IMPORT_SKU extends Module{
	var $mprice_list, $input_gst_list, $output_gst_list, $branch_list;
	
	var $headers = array(
		'1' => array("mcode" => "MCode",
					 "link_code" => "Link Code/ Old Code",
					 "artno" => "Art No",
					 "description" => "Description",
					 "receipt_description" => "Receipt Description",
					 "uom" => "UOM",
					 "cost_price" => "Cost",
					 "selling_price" => "Selling Price",
					 "scale_type" => "Scale Type",
					 "wholesale1" => "Wholesale 1",
					 "line" => "Line",
					 "department" => "Department",
					 "category1" => "Category 1",
					 "category2" => "Category 2",
					 "category3" => "Category 3",
					 "category4" => "Category 4",
					 "category5" => "Category 5",
					 "brand" => "Brand",
					 "vendor" => "Vendor",
					 "color" => "Color",
					 "size" => "Size",
					 "input_tax" => "Input Tax",
					 "output_tax" => "Output Tax",
					 "sku_type" => "SKU Type",
					 "po_reorder_min_qty" => "General PO Reorder Min Qty",
					 "po_reorder_max_qty" => "General PO Reorder Max Qty",
					 "po_reorder_moq" => "General PO Reorder MOQ Qty",
					 "parent_arms_code" => "Parent Arms Code",
					 "parent_mcode" => "Parent MCode",
					 "parent_artno" => "Parent Artno",
					 "trade_discount_type" => "Consignment Table",
					 "trade_discount_code" => "Consignment Price Type",
					 "trade_discount_rate" => "Consignment Discount Rate",
					 "additional_description" => "Addition Description",
					 "rsp_price"=> "RSP",
					 "rsp_discount"=> "RSP Discount"
				)
	);
	
	var $sample = array(
		'1' => array(
			'sample_1' => array("203398116110", "57730", "20339811", "Sunset Bay Inf", "Sunset Bay Inf", "EACH", "20.50", "31.50", "0", "28.00", "Minimarket", "Girls", "Dress A", "Dress B", "Dress C", "Dress D", "Dress E", "Dresses", "Dresses Sdn. Bhd.", "Brown", "20", "", "", "OUTRIGHT", "5", "20", "20", "", "203398116110", "20339811", "", "", "", "Outright Item", "28.35", "10%"),
			'sample_2' => array("203398116111", "57731", "20339812", "Sunset Bay Inf", "Sunset Bay Inf", "EACH", "21.50", "32.50", "0", "29.00", "Minimarket", "Girls", "Dress A", "Dress B", "Dress C", "Dress D", "Dress E", "Dresses", "Dresses Sdn. Bhd.", "Brown", "20", "TX", "SR", "CONSIGN", "10", "50", "40", "", "203398116111", "20339812", "Brand", "B1", "20", "", "27.50", "5")
		)
	);
		
	var $col_align_right = array('cost_price', 'selling_price');
	var $col_align_center = array('uom', 'scale_type', 'wholesale1', 'color', 'size', 'inclusive_tax', 'input_tax', 'output_tax', 'sku_type', 'po_reorder_min_qty', 'po_reorder_max_qty', 'po_reorder_moq');
	
	function __construct($title){
		$this->init();
 		parent::__construct($title);
	}

	function _default(){
		$this->display();
	}
	
	function init(){
		global $con, $config, $smarty;
		
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		if (!is_dir("attachments/sku_import"))	check_and_create_dir("attachments/sku_import");
		
		//load sku_multiple_selling_price
		if($config['sku_multiple_selling_price']) {
			$this->mprice_list = $config['sku_multiple_selling_price'];
		}
		
		// load gst information
		$con->sql_query("select * from gst");
		while($g = $con->sql_fetchassoc()){
			if($g['type']=="supply") $this->output_gst_list[$g['code']] = $g;
			else $this->input_gst_list[$g['code']] = $g;
		}
		$con->sql_freeresult();
		
		//load branch information	
		$con->sql_query("select id,code from branch order by id");
		while($r = $con->sql_fetchassoc()){
			$this->branch_list[$r['id']] = $r;
		}
		$con->sql_freeresult();
		
		// from functions.php
		load_sku_type_list();
		
		$smarty->assign("sample_headers", $this->headers);
		$smarty->assign("sample", $this->sample);
		
		// Last column name
		$last_col_name = end($this->headers[1]);
		$smarty->assign("last_col_name", $last_col_name);
	}
	
	function download_sample_sku(){				
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_sku.csv");
		
		print join(",", array_values($this->headers[$_REQUEST['method']])) . "\r\n";
		foreach($this->sample[$_REQUEST['method']] as $sample) {
			$data = array();
			foreach($sample as $d) {
				$data[] = $d;
			}
			print join(",", $data) . "\r\n";
		}
	}
	
	function item_is_parent($line_index, $ins){
		global $parent_artno_list, $con;
		if(!$ins)	return false;
		
		// no parent info or parent mcode = mcode
		if((!$ins['parent_arms_code'] && !$ins['parent_mcode'] && !$ins['parent_artno']) || (($ins['mcode'] && $ins['parent_mcode'] == $ins['mcode']))){
			return true;
		}else{
			// got parent artno
			if($ins['parent_artno']){
				// got known parent artno, so this new item is not parent
				if(isset($parent_artno_list[$ins['parent_artno']])){
					if($parent_artno_list[$ins['parent_artno']] == $line_index){
						return true;
					}else{
						return false;
					}					
				}else{
					// don't have known parent artno, search from db
					$con->sql_query($q = "select id, artno from sku_items where artno=".ms($ins['parent_artno'])." and is_parent=1 order by id limit 1");
					$tmp = $con->sql_fetchassoc();
					$con->sql_freeresult();
					
					// found db have, so this new item also not parent
					if($tmp){
						//$parent_artno_list[$ins['parent_artno']] = $tmp['id'];
						return false;
					}
					
					// the first artno, it is a parent
					if($ins['artno'] == $ins['parent_artno']){
						$parent_artno_list[$ins['parent_artno']] = $line_index;
						return true;
					}else{
						return false;
					}					
				}
			}else{
				return false;
			}
		}
	}
	
	function show_result(){
		global $con, $smarty, $parent_artno_list, $config, $sessioninfo, $appCore;
		
		$form = $_REQUEST;
		$file = $_FILES['import_csv'];
		$f = fopen($file['tmp_name'], "rt");
		$line = fgetcsv($f);
		$column_skip_line_break = array();
		
		$mcode_list = $parent_artno_list = $artno_list = $trade_disc_list = array();
		$existed_items = $item_lists = $existing_parents = $mcode_input_list = $artno_input_list = array();
		
		//check and assign if multiple price column is present
		$mprice_error = $mprice_header = array();
		
		$column_skip_line_break[] = 33;
		for($i=count($this->headers[$form['method']]); $i<count($line); $i++) {
			$mprice_type = strtolower(trim($line[$i]));
			$mprice_header[] = strtolower($line[$i]);
			if(!in_array($mprice_type, $this->mprice_list) || $mprice_type == 'wholesale1') {
				$mprice_error[] = ms($line[$i]);
			}
		}
		if($mprice_error) {
			$err = "Multiple Selling Price: ".join(', ', $mprice_error)." Not Existed/has been Redeclared";
		}
		
		$error_list = array();
		if(!$err){
			$line_index = 0;
			while($r = fgetcsv($f)){
				$error = array();
				$result['ttl_row']++;
				$line_index++;
				
				//print_r($r);
				// fix all text that contains special character to convert into utf8
				foreach($r as $tmp_row => $val){
					// replace Microsoft Word version of single  and double quotations marks (“ ” ‘ ’) with  regular quotes (' and ")
					//$val = iconv('UTF-8', 'ASCII//TRANSLIT', trim($val));
					//$r[$tmp_row] = utf8_encode(trim($val));
					$val = replace_ms_quotes(trim($val));
					
					// Remove Line Break
					if(!in_array($tmp_row, $column_skip_line_break)){
						$val = trim($appCore->removeLineBreak($val));
					}
					$r[$tmp_row] = $val;
				}
				//print_r($r);exit;
				
				$ins = array();
				switch($form['method']) {
					case '1':
						$ins['mcode'] = trim(preg_replace("/[^A-Za-z0-9]/", "", $r[0]));
						$ins['mcode'] = strtoupper($ins['mcode']);
						$ins['link_code'] = $r[1];
						$ins['artno'] = $r[2];
						$ins['description'] = $r[3];
						if($config['masterfile_disallow_double_quote']){
							$ins['description'] = str_replace('"', "", $ins['description']);
						}						
						$ins['receipt_description'] = $r[4];
						if($config['masterfile_disallow_double_quote']){
							$ins['receipt_description'] = str_replace('"', "", $ins['receipt_description']);
						}						
						if(!$ins['receipt_description'])	$ins['receipt_description'] = $ins['description'];
						$ins['uom'] = strtoupper((trim($r[5])) ? trim($r[5]) : "EACH");
						$ins['cost_price'] = floatval(trim($r[6]));
						$ins['selling_price'] = floatval($r[7]);
						$ins['scale_type'] = trim($r[8]);
						$ins['wholesale1'] = trim($r[9]);
						$ins['line'] = $r[10];
						$ins['department'] = $r[11];
						$ins['category1'] = $r[12];
						$ins['category2'] = $r[13];
						$ins['category3'] = $r[14];
						$ins['category4'] = $r[15];
						$ins['category5'] = $r[16];
						$ins['brand'] = $r[17];
						$ins['vendor'] = $r[18];
						$ins['color'] = strtoupper(trim($r[19]));
						$ins['size'] = strtoupper(trim($r[20]));
						//$ins['inclusive_tax'] = "inherit";
						$ins['input_tax'] = strtoupper(trim($r[21]));
						$ins['output_tax'] = strtoupper(trim($r[22]));
						$ins['sku_type'] = strtoupper(trim($r[23]));
						if(!$ins['sku_type']) $ins['sku_type'] = "OUTRIGHT";
						$ins['po_reorder_min_qty'] = trim($r[24]);
						$ins['po_reorder_max_qty'] = trim($r[25]);
						$ins['po_reorder_moq'] = trim($r[26]);
						$ins['parent_arms_code'] = trim($r[27]);
						$ins['parent_mcode'] = trim(preg_replace("/[^A-Za-z0-9]/", "", $r[28]));
						$ins['parent_mcode'] = strtoupper($ins['parent_mcode']);
						$ins['parent_artno'] = trim($r[29]);
						$ins['trade_discount_type'] = strtolower(trim($r[30]));
						$ins['trade_discount_code'] = strtoupper(trim($r[31]));
						$ins['trade_discount_rate'] = round(floatval(trim($r[32])), 4);
						$ins['additional_description'] = $config['sku_enable_additional_description'] ? $r[33] : "";
						$ins['rsp_price'] = mf($r[34]);
						$ins['rsp_discount'] = $r[35];
						for($i=count($this->headers[$form['method']]); $i<count($line); $i++) {
							$mprice_type = strtolower($line[$i]);
							$ins[$mprice_type] = round(trim($r[$i]), 2);
						}
						//print_r($ins);exit;
						break;
					default:
						break;
				}
				
				//if(!$ins['mcode'] && !$ins['link_code'] && !$ins['artno']){
					//continue;
				//}
								
				if($ins['mcode'] || $ins['link_code'] || $ins['artno']) {
					$mcode_duplicate = false;
					if($ins['mcode']) {
						// check mcode from db for duplication
						$con->sql_query("select * from sku_items where mcode = ".ms($ins['mcode'])." limit 1");
						if($con->sql_numrows() > 0) {
							$mcode_duplicate = true;
							$error[] = 'Duplicate MCode';
						}else {
							// check mcode from import file for duplication
							if(!in_array($ins['mcode'], $mcode_list))	$mcode_list[] = $ins['mcode'];
							else{
								$mcode_duplicate = true;
								$error[] = 'Duplicate MCode';
							}
						}
						$con->sql_freeresult();
					}
					
					if($ins['artno'] && $mcode_duplicate){
						$con->sql_query("select * from sku_items where artno = ".ms($ins['artno'])." and description=".ms($ins['description'])." limit 1");
						if($con->sql_numrows() > 0) {
							$error[] = 'Duplicate Artno with same description';
						}else {
							// check mcode from import file for duplication
							$artno_with_desc = $ins['artno'].",".$ins['description'];
							if(!in_array($artno_with_desc, $artno_list))	$artno_list[] = $artno_with_desc;
							else	$error[] = 'Duplicate Artno with same description';
						}
						$con->sql_freeresult();
					}
				}else {
					$error[] = "Empty MCode, Link Code and Art No";
				}
				
				if($ins['parent_arms_code']) {
					$con->sql_query("select * from sku_items where sku_item_code = ".ms($ins['parent_arms_code'])." and is_parent = 1");
					if($con->sql_numrows() == 0) $error[] = 'Parent Arms Code Not Found';
					$con->sql_freeresult();
				}
				
				if($this->item_is_parent($line_index, $ins) && $ins['uom'] != "EACH"){
					// Need to check if uom fraction is one
					$uom_info = $this->get_uom_by_code($ins['uom']);
					if($uom_info){
						if($uom_info['fraction'] != 1){
							$error[] = "UOM Parent Item must be fraction = 1";
						}
					}
					//else{
					//	$error[] = "UOM Parent Item must be 'EACH'";
					//}
				}elseif(strlen($ins['uom']) > 6)	$error[] = "UOM Exceed 6 Characters";
				
				if(!$this->item_is_parent($line_index, $ins)){
					if($ins['parent_mcode']) {
						$filter = array();
						$filter[] = "mcode = ".ms($ins['parent_mcode']);
						$filter = join(' and ', $filter);
						
						$con->sql_query("select * from sku_items where ($filter) and is_parent = 1 order by id limit 1");
						if($con->sql_numrows() == 0){
							if(!in_array($ins['parent_mcode'], $mcode_list)){
								$error[] = 'Parent MCode or Artno Not Found';
							}
						}
						$con->sql_freeresult();
					}
					
					if(!$error && $ins['parent_artno']){
						if(!isset($parent_artno_list[$ins['parent_artno']])){
							$con->sql_query("select * from sku_items where artno=".ms($ins['parent_artno'])." and is_parent = 1 order by id limit 1");
							if($con->sql_numrows() == 0){
								$error[] = 'Parent MCode or Artno Not Found';
							}
							$con->sql_freeresult();
							
						}					
					}
				}
				
				// check uom error for parent sku item
				/*if((!$ins['parent_arms_code'] && !$ins['parent_mcode'] && !$ins['parent_artno'] && $ins['uom'] != "EACH") ||
				   (($ins['parent_mcode'] == $ins['mcode'] || $ins['parent_artno'] == $ins['artno']) && $ins['uom'] != "EACH")){
					$error[] = "UOM Parent Item must be 'EACH'";
				}elseif(strlen($ins['uom']) > 6)	$error[] = "UOM Exceed 6 Characters";*/
				
				
				
				//check sku type
				if(!in_array($ins['sku_type'], array("CONSIGNMENT","CONSIGN","OUTRIGHT","CONCESS","CONCESSION","CONCESSIONAIRE"))){
					$error[] = "Incorrect SKU Type";
				}else{
					if($ins['sku_type'] == "CONSIGNMENT") $ins['sku_type'] = "CONSIGN";
					elseif(in_array($ins['sku_type'], array("CONCESSION","CONCESSIONAIRE"))) $ins['sku_type'] = "CONCESS";
				}
				
				//check scale type
				if($ins['scale_type']>=0 && $ins['scale_type']<=2)	$ins['scale_type'] = mi($ins['scale_type']);
				elseif($ins['scale_type'] == "WEIGH" || $ins['scale_type'] == "WEIGHT") $ins['scale_type'] = 2;
				else	$error[] = "Incorrect Scale Type";
				
				//check input an doutput tax code
				if($ins['input_tax'] && !isset($this->input_gst_list[$ins['input_tax']]))		$error[] = "Incorrect Input Tax Code";
				if($ins['output_tax'] && !isset($this->output_gst_list[$ins['output_tax']]))		$error[] = "Incorrect Output Tax Code";
				
				/*if(!in_array($ins['inclusive_tax'], array("yes", "no", "inherit"))) {
					$error[] = "Inclusive Tax Value must be 'yes', 'no' or 'inherit'";
				}*/
				
				// check trade discount info when sku type is CONSIGN
				if($ins['sku_type'] == "CONSIGN"){
					$td_type_list = array("brand", "vendor");
					$td_code_list = $this->load_price_type_list();
					
					// check if trade discount type is empty or invalid (Brand or Vendor)
					$is_valid_tdt = false;
					if(!in_array($ins['trade_discount_type'], $td_type_list)) $error[] = "Invalid Trade Discount Type";
					else $is_valid_tdt = true;
					
					// check if trade discount code is empty or invalid (B1, B2 and ...)
					$is_valid_tdc = false;
					if(!$td_code_list[$ins['trade_discount_code']]) $error[] = "Invalid Trade Discount Code";
					else $is_valid_tdc = true;
					
					// check if trade discount rate is valid
					if($is_valid_tdt && $is_valid_tdc){
						$line_id = $this->get_cat_by_desc($ins['line'], 0, 1);
						
						// need to select default line if couldn't found
						if(!$line_id && !$ins['line']){
							$con->sql_query("select id from category where description = 'LINE' and level = 1 limit 1");
							$line_id = $con->sql_fetchfield(0);
							$con->sql_freeresult();	
						}
						
						$dept_id = $this->get_cat_by_desc($ins['department'], $line_id, 2);
						if(!$dept_id && !$ins['department']){
							$con->sql_query("select id from category where description = 'DEPARTMENT' and level = 2 and root_id=".mi($line_id)." limit 1");
							$dept_id = $con->sql_fetchfield(0);
							$con->sql_freeresult();
						}
						
						$line_id = mi($line_id); // possible is zero
						$dept_id = mi($dept_id); // possible is zero
						$filters = array();
						if($ins['trade_discount_type'] == "vendor"){
							$vendor_id = $this->get_vendor_by_desc($ins['vendor']);
							
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
							$brand_id = $this->get_brand_by_desc($ins['brand']);
							
							$filters[] = "brand_id = ".mi($brand_id);
							
							// check if the discount table type is BRAND and show errors if doesn't assign any brand on the csv
							if($brand_id){
								$arr_key_id = $brand_id;
							}else{ // must not allow user to leave brand empty when it is CONSIGN SKU
								if($ins['brand']) $arr_key_id = $ins['brand'];
								else $error[] = "Invalid Brand";
							}
							
						}
						
						// table selection
						$tbl_name = $ins['trade_discount_type']."_commission";
						$filters[] = "branch_id = ".mi($sessioninfo['branch_id'])." and department_id = ".mi($dept_id)." and skutype_code = ".ms($ins['trade_discount_code'])." and rate > 0";
						$q1 = $con->sql_query("select * from $tbl_name where ".join(" and ", $filters));
						$db_td_info = $con->sql_fetchassoc($q1);
						
						if($con->sql_numrows($q1) > 0){ // if system has the rate
							// if found user did not put discount rate, meanwhile use the one from system 
							if(!$ins['trade_discount_rate']) $ins['trade_discount_rate'] = $db_td_info['rate']; 
							
							if($db_td_info['rate'] > 0 && $ins['trade_discount_rate'] != $db_td_info['rate']) $error[] = "Trade Discount Rate [".mf($ins['trade_discount_rate'])."] is different with system [".mf($db_td_info['rate'])."]";
							elseif(!$ins['trade_discount_rate']) $error[] = "Invalid Trade Discount Rate";
						}elseif($ins['trade_discount_rate'] > 0 && isset($trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']]) && $trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']] != $ins['trade_discount_rate']){ // if having different rate but same disc type, dept_id, disc code within the CSV
							$error[] = "Contains multiple Trade Discount Rate";
						}elseif(!$trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']]){ // if rate couldn't be found from both system and array list, then prompt error
							if($ins['trade_discount_rate'] == 0) $error[] = "Invalid Trade Discount Rate";
						}elseif($con->sql_numrows($q1) == 0 && !$ins['trade_discount_rate']){ // no data from database and didn't provide any rate
							$error[] = "Invalid Trade Discount Rate";
						}
						$con->sql_freeresult($q1);
						
						if($ins['trade_discount_rate']){
							// get the cost using selling price deduct from trade disc rate
							$latest_cost = round(($ins['selling_price']*(100-$ins['trade_discount_rate']))/100, $config['global_cost_decimal_points']);
						
							// if found got assigned cost price but different with the calculated cost, prompt error
							if($ins['cost_price'] && $ins['cost_price'] != $latest_cost) $error[] = "Incorrect Cost [should be $latest_cost]";
						}
						
						if($ins['trade_discount_rate'] && !isset($trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']])){
							$trade_disc_list[$line_id][$dept_id][$arr_key_id][$ins['trade_discount_type']][$ins['trade_discount_code']] = $ins['trade_discount_rate'];
						}
					}
				}
				
				//check moq and min
				if(!$ins['po_reorder_min_qty'] && !$ins['po_reorder_max_qty'] && $ins['po_reorder_moq']){
					$error[] = "PO reorder qty max and min must fill if exist MOQ";
				}elseif($ins['po_reorder_min_qty'] && $ins['po_reorder_max_qty'] || $ins['po_reorder_moq']){
					if($ins['po_reorder_min_qty'] >= $ins['po_reorder_max_qty']){
						$error[] = "PO reorder qty max cannot less than min";
					}
					if($ins['po_reorder_moq'] > $ins['po_reorder_max_qty']){
						$error[] = "PO reorder qty max cannot less than MOQ";
					}
				}
				if($ins['po_reorder_min_qty'] || $ins['po_reorder_max_qty']){
					if(count($error)>0 && in_array("PO reorder qty max cannot less than min",$error)){
						
					}else{
						if($ins['po_reorder_min_qty'] >= $ins['po_reorder_max_qty']){
							$error[] = "PO reorder qty max cannot less than min";
						}
					}
				}
				
				//Check RSP
				if(!$ins['rsp_price'] && $ins['rsp_discount']){  
					$error[] = "Empty RSP";
				}
				if($ins['rsp_price'] && ($ins['rsp_discount'] || trim($ins['rsp_discount']) == 0)){
					$ins['selling_price'] = round(floatval($ins['rsp_price']), 2);
					
					if(trim($ins['rsp_discount']) != 0){
						$discount_list = explode("+", $ins['rsp_discount']);
						$invalid_discount = 0;
						if(count($discount_list) > 0){
							$selling_price = $ins['rsp_price'];
							foreach($discount_list as $key=>$discount_val){
								if(!preg_match("/^[0-9]+(\.?[0-9]{1,2})?%?$/", $discount_val))  $invalid_discount+= 1;
								$discount_value = strpos($discount_val, "%") ? ($selling_price*mf($discount_val)) / 100 : $discount_val;
								$selling_price -= $discount_value;
							}
							$ins['selling_price'] = round(floatval($selling_price), 2);
						}
						if($invalid_discount > 0)   $error[] = "Invalid RSP discount format";
					}
				}

				if($error)	$ins['error'] = join(', ', $error);
					
				$item_lists[] = $ins;
				
				if($ins['error']){
					$result['error_row']++;
					$error_list[] = $ins;
				}else				$result['import_row']++;
			}
		}
		
		$ret = array();
		if($item_lists){
			$header = $this->headers[$form['method']];
			if(count($mprice_header) > 0){
				$header = array_merge($header, $mprice_header);
			}
			if($result['error_row'] > 0)	$header[] = 'Error';
			
			$file_name = "sku_".time().".csv";
			
			$fp = fopen("attachments/sku_import/".$file_name, 'w');

			fputcsv($fp, array_values($header));
			foreach($item_lists as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/sku_import/".$file_name, 0777);
			
			$smarty->assign("result", $result);
			$smarty->assign("file_name", $file_name);
			$smarty->assign("item_header", array_values($header));
			$smarty->assign("item_lists", $item_lists);
			$smarty->assign("align_right", $this->col_align_right);
			$smarty->assign("align_center", $this->col_align_center);
		}else{
			if(!$err)	$err = $LANG['ADMIN_SKU_MIGRATION_NO_DATA'];
			$smarty->assign("errm", $err);
		}
		
		$error_link = '';
		if($error_list) {
			$header = $this->headers[$form['method']];
			if(count($mprice_header) > 0){
				$header = array_merge($header, $mprice_header);
			}
			$header[] = 'Error';
			
			$error_file = "invalid_sku_".time().".csv";
			$fp = fopen("attachments/sku_import/".$error_file, 'w');
			fputcsv($fp, array_values($header));
			
			foreach($error_list as $r){
				fputcsv($fp, $r);
			}
			fclose($fp);
			
			chmod("attachments/sku_import/".$error_file, 0777);
			$error_link = "attachments/sku_import/".$error_file;
		}
		
		$smarty->assign("error_link", $error_link);
		$smarty->assign("form", $form);
		$smarty->assign('method', $form['method']);
		$this->display();
	}

 	function ajax_import_sku(){
		global $con, $sessioninfo, $config;
		
		$form = $_REQUEST;
		if(!$form['file_name'] || !file_exists("attachments/sku_import/".$form['file_name'])){
			die("File no found.");
			exit;
		}
		
		$f = fopen("attachments/sku_import/".$form['file_name'], "rt");
		$line = fgetcsv($f);
		
		if(in_array('Error', $line)) {
			$error_index = array_search("Error", $line);
		}else{
			$error_index = count($line);
		}
		
		$default_line_id = $default_vendor_id = $num_row = 0;
		$ret = $error_list = $new_uom_list = $default_dept_id = $default_cat_id = array();
		$line_index = 0;
		while($r = fgetcsv($f)){
			$line_index++;
			//foreach($r as $tmp_row => $val){
				//$r[$tmp_row] = utf8_encode(trim($val));
			//}
			
			$ins = array();
			switch ($form['method']) {
				case '1':
					if(!$r[$error_index]){
						$ins['mcode'] = $r[0];
						$ins['link_code'] = $r[1];
						$ins['artno'] = $r[2];			
						$ins['description'] = $r[3];
						$ins['receipt_description'] = $r[4];
						$ins['uom'] = $r[5];
						$ins['cost_price'] = $r[6];
						$ins['selling_price'] = $r[7];
						$ins['scale_type'] = $r[8];
						$ins['wholesale1'] = $r[9];
						$ins['line'] = $r[10];
						$ins['department'] = $r[11];
						$ins['category1'] = $r[12];
						$ins['category2'] = $r[13];
						$ins['category3'] = $r[14];
						$ins['category4'] = $r[15];
						$ins['category5'] = $r[16];
						$ins['brand'] = $r[17];
						$ins['vendor'] = $r[18];
						$ins['color'] = $r[19];
						$ins['size'] = $r[20];
						//$ins['inclusive_tax'] = $r[21];
						$ins['inclusive_tax'] = 'inherit';
						$ins['input_tax'] = $r[21];
						$ins['output_tax'] = $r[22];
						$ins['sku_type'] = $r[23];
						$ins['po_reorder_qty_min'] = $r[24];
						$ins['po_reorder_qty_max'] = $r[25];
						$ins['po_reorder_moq'] = $r[26];
						$ins['parent_arms_code'] = $r[27];
						$ins['parent_mcode'] = $r[28];
						$ins['parent_artno'] = $r[29];
						$ins['trade_discount_type'] = $r[30];
						$ins['trade_discount_code'] = $r[31];
						$ins['trade_discount_rate'] = $r[32];
						$ins['additional_description'] = $config['sku_enable_additional_description'] ? $r[33] : "";
						$ins['rsp_price'] = $r[34];
						$ins['rsp_discount'] = $r[35];
						for($i=count($this->headers[$form['method']]); $i<count($line); $i++) {
							if($line[$i] != 'Error'){
								$mprice_type = strtolower($line[$i]);
								$ins[$mprice_type] = $r[$i];
							}	
						}
						//print_r($ins);exit;
						
						// create new UOM if db not found
						if(!$new_uom_list[$ins['uom']]){
							$con->sql_query("select * from uom where code = ".ms($ins['uom']));
							if($con->sql_numrows() > 0){
								$ins['uom_id'] = $con->sql_fetchfield(0);
							}else{
								$uom_ins = array();
								$uom_ins['code'] = $ins['uom'];
								$uom_ins['description'] = $ins['uom'];
								$uom_ins['fraction'] = 1;
								$uom_ins['active'] = 1;
								$con->sql_query("insert into uom ".mysql_insert_by_field($uom_ins));
								$new_uom_list[$ins['uom']] = $ins['uom_id'] = intval($con->sql_nextid());
							}
							$con->sql_freeresult();
						}else{
							$ins['uom_id'] = $new_uom_list[$ins['uom']];
						}
						
						//Get Line ID
						$ins['line_id'] = $this->get_cat_by_desc($ins['line'], 0, 1);
						
						//LINE
						// if found the line from csv is empty, insert default line
						if(!$ins['line_id']){
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
								$ins['line_id'] = $default_line_id;
							}else{
								$upd = array();
								$upd['level'] = 1;
								$upd['description'] = $ins['line'];
								$upd['active'] = 1;
								$upd['tree_str'] = '(0)';
								$upd['no_inventory'] = 'no';
								$upd['is_fresh_market'] = 'no';
								
								$con->sql_query("insert into category ".mysql_insert_by_field($upd));
								$ins['line_id'] = $con->sql_nextid();
								$con->sql_query("update category set code = ".mi($ins['line_id']).", department_id = ".mi($ins['line_id'])." where id = ".mi($ins['line_id'])." and level = 1");
							}
						}
						
						//get Department ID
						$ins['dept_id'] = $this->get_cat_by_desc($ins['department'], $ins['line_id'], 2);
						// DEPARTMENT
						// if found the department from csv is empty, insert default department
						if(!$ins['dept_id']){
							if(!$ins['department']){
								if(!$default_dept_id[$ins['line_id']]){
									$con->sql_query("select id from category where description = 'DEPARTMENT' and level = 2 and root_id=".$ins['line_id']." limit 1");
									$default_dept_id[$ins['line_id']] = $con->sql_fetchfield(0);
									$con->sql_freeresult();
									
									if(!$default_dept_id[$ins['line_id']]){
										$upd = array();
										$upd['root_id'] = $ins['line_id'];
										$upd['level'] = 2;
										$upd['description'] = 'DEPARTMENT';
										$upd['active'] = 1;
										$upd['tree_str'] = '(0)('.$ins['line_id'].')';
										$con->sql_query("insert into category ".mysql_insert_by_field($upd));
										$default_dept_id[$ins['line_id']] = $con->sql_nextid();
										$con->sql_query("update category set code = ".mi($default_dept_id[$ins['line_id']]).", department_id = ".mi($default_dept_id[$ins['line_id']])." where id = ".mi($default_dept_id[$ins['line_id']])." and level = 2");
									}
								}
								$ins['dept_id'] = $default_dept_id[$ins['line_id']];
							}else{
								// check and insert department
								$upd = array();
								$upd['root_id'] = $ins['line_id'];
								$upd['level'] = 2;
								$upd['description'] = $ins['department'];
								$upd['active'] = 1;
								$upd['tree_str'] = '(0)('.$ins['line_id'].')';
								$con->sql_query("insert into category ".mysql_insert_by_field($upd));
								$ins['dept_id'] = $con->sql_nextid();
								$con->sql_query("update category set code = ".mi($ins['dept_id']).", department_id=".mi($ins['dept_id'])." where id=".mi($ins['dept_id'])." and level = 2");
							}
						}
						
						// CATEGORY 1
						// if found the category from csv is empty, insert default category
						$ins['cat1_id'] = $this->get_cat_by_desc($ins['category1'], $ins['dept_id'], 3);
						if(!$ins['cat1_id']){
							if(!$ins['category1']){
								if(!$default_cat_id[$ins['line_id']][$ins['dept_id']]){
									$con->sql_query("select id from category where description = 'CATEGORY' and root_id = ".mi($ins['dept_id'])." and level = 3 limit 1");
									$default_cat_id[$ins['line_id']][$ins['dept_id']] = $con->sql_fetchfield(0);
									$con->sql_freeresult();
									
									if(!$default_cat_id[$ins['line_id']][$ins['dept_id']]){
										$upd = array();
										$upd['root_id'] = $ins['dept_id'];
										$upd['level'] = 3;
										$upd['description'] = 'CATEGORY';
										$upd['active'] = 1;
										$upd['department_id'] = $ins['dept_id'];
										$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')';
										$con->sql_query("replace into category ".mysql_insert_by_field($upd));
										$default_cat_id[$ins['line_id']][$ins['dept_id']] = $con->sql_nextid();
										$con->sql_query("update category set code = ".mi($default_cat_id[$ins['line_id']][$ins['dept_id']])." where id = ".mi($default_cat_id[$ins['line_id']][$ins['dept_id']])." and level = 3");
									}
								}
								$ins['cat1_id'] = $default_cat_id[$ins['line_id']][$ins['dept_id']];
							}else{
								$upd = array();
								$upd['root_id'] = $ins['dept_id'];
								$upd['level'] = 3;
								$upd['description'] = $ins['category1'];
								$upd['active'] = 1;
								$upd['department_id'] = $ins['dept_id'];
								$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')';
								$con->sql_query("insert into category ".mysql_insert_by_field($upd));
								$ins['cat1_id'] = $con->sql_nextid();
								$con->sql_query("update category set code = ".mi($ins['cat1_id'])." where id=".mi($ins['cat1_id'])." and level = 3");
							}
						}
						$ins['category_id'] = $ins['cat1_id'];
						
						// CATEGORY 2
						if($ins['category2']){
							$ins['cat2_id'] = $this->get_cat_by_desc($ins['category2'], $ins['cat1_id'], 4);
							if(!$ins['cat2_id']){
								$upd = array();
								$upd['root_id'] = $ins['cat1_id'];
								$upd['level'] = 4;
								$upd['description'] = $ins['category2'];
								$upd['active'] = 1;
								$upd['department_id'] = $ins['dept_id'];
								$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')('.$ins['cat1_id'].')';
								$con->sql_query("insert into category ".mysql_insert_by_field($upd));
								$ins['cat2_id'] = $con->sql_nextid();
								$con->sql_query("update category set code = ".mi($ins['cat2_id'])." where id=".mi($ins['cat2_id'])." and level = 4");
							}
							$ins['category_id'] = $ins['cat2_id'];
							
							// CATEGORY 3
							if($ins['category3']){
								$ins['cat3_id'] = $this->get_cat_by_desc($ins['category3'], $ins['cat2_id'], 5);
								if(!$ins['cat3_id']){
									$upd = array();
									$upd['root_id'] = $ins['cat2_id'];
									$upd['level'] = 5;
									$upd['description'] = $ins['category3'];
									$upd['active'] = 1;
									$upd['department_id'] = $ins['dept_id'];
									$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')('.$ins['cat1_id'].')('.$ins['cat2_id'].')';
									$con->sql_query("insert into category ".mysql_insert_by_field($upd));
									$ins['cat3_id'] = $con->sql_nextid();
									$con->sql_query("update category set code = ".mi($ins['cat3_id'])." where id=".mi($ins['cat3_id'])." and level = 5");
								}
								$ins['category_id'] = $ins['cat3_id'];
								
								// CATEGORY 4
								if($ins['category4']){
									$ins['cat4_id'] = $this->get_cat_by_desc($ins['category4'], $ins['cat3_id'], 6);
									if(!$ins['cat4_id']){
										$upd = array();
										$upd['root_id'] = $ins['cat3_id'];
										$upd['level'] = 6;
										$upd['description'] = $ins['category4'];
										$upd['active'] = 1;
										$upd['department_id'] = $ins['dept_id'];
										$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')('.$ins['cat1_id'].')('.$ins['cat2_id'].')('.$ins['cat3_id'].')';
										$con->sql_query("insert into category ".mysql_insert_by_field($upd));
										$ins['cat4_id'] = $con->sql_nextid();
										$con->sql_query("update category set code = ".mi($ins['cat4_id'])." where id=".mi($ins['cat4_id'])." and level = 6");
									}
									$ins['category_id'] = $ins['cat4_id'];
									
									// CATEGORY 5
									if($ins['category5']){
										// check and insert category
										$ins['cat5_id'] = $this->get_cat_by_desc($ins['category5'], $ins['cat4_id'], 7);
										if(!$ins['cat5_id']){
											$upd = array();
											$upd['root_id'] = $ins['cat4_id'];
											$upd['level'] = 7;
											$upd['description'] = trim($r[16]);
											$upd['active'] = 1;
											$upd['department_id'] = $ins['dept_id'];
											$upd['tree_str'] = '(0)('.$ins['line_id'].')('.$ins['dept_id'].')('.$ins['cat1_id'].')('.$ins['cat2_id'].')('.$ins['cat3_id'].')('.$ins['cat4_id'].')';
											$con->sql_query("insert into category ".mysql_insert_by_field($upd));
											$ins['cat5_id'] = $con->sql_nextid();
											$con->sql_query("update category set code = ".mi($ins['cat5_id'])." where id=".mi($ins['cat5_id'])." and level = 7");
										}
										$ins['category_id'] = $ins['cat5_id'];
									}
								}
							}
						}
						
						// check and insert brand
						if($ins['brand']){
							$ins['brand_id'] = $this->get_brand_by_desc($ins['brand']);
							if(!$ins['brand_id']){
								$upd = array();		
								//$upd['code'] = $ins['brand'];
								$upd['description'] = $ins['brand'];
								$upd['active'] = 1;
								$con->sql_query("insert into brand ".mysql_insert_by_field($upd));
								$ins['brand_id'] = $con->sql_nextid();
							}
						}else $ins['brand_id'] = 0;
						$ins['brand_id'] = mi($ins['brand_id']);
						
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
							$ins['vendor_id'] = $default_vendor_id;
						}else{
							$ins['vendor_id'] = $this->get_vendor_by_desc($ins['vendor']);
							if(!$ins['vendor_id']){
								//$con->sql_query("select max(id) from vendor");
								//$max_vendor_id = mi($con->sql_fetchfield(0))+1;
								//$con->sql_freeresult();
								$upd = array();		
								//$upd['code'] = $max_vendor_id;
								$upd['description'] = $ins['vendor'];
								$upd['active'] = 1;
								$con->sql_query("insert into vendor ".mysql_insert_by_field($upd,false,1));
								$ins['vendor_id'] = $con->sql_nextid();
								$con->sql_query_false("update vendor set code=".ms($ins['vendor_id'])." where id=".mi($ins['vendor_id']));
							}
						}
						$ins['vendor_id'] = mi($ins['vendor_id']);
						
						// insert or update trade discount info
						if($ins['sku_type'] == "CONSIGN"){
							$filters = array();
							if($ins['trade_discount_type'] == "vendor"){
								$filters[] = "vendor_id = ".mi($ins['vendor_id']);
								$upd_key = "vendor_id";
								$upd_val = $ins['vendor_id'];
							}else{
								$filters[] = "brand_id = ".mi($ins['brand_id']);
								$upd_key = "brand_id";
								$upd_val = $ins['brand_id'];
							}
							
							// table selection
							$tbl_name = $ins['trade_discount_type']."_commission";
							$filters[] = "branch_id = ".mi($sessioninfo['branch_id'])." and department_id = ".mi($ins['dept_id'])." and skutype_code = ".ms($ins['trade_discount_code']);
							$q1 = $con->sql_query("select * from $tbl_name where ".join(" and ", $filters));
							$db_td_info = $con->sql_fetchassoc($q1);
							
							if(!$db_td_info['rate']){ // need to insert the rate
								$comm_upd = array();
								$comm_upd['rate'] = $ins['trade_discount_rate'];
								
								if($con->sql_numrows($q1) > 0){ // do update
									$con->sql_query("update $tbl_name set ".mysql_update_by_field($comm_upd)." where ".join(" and ", $filters));
								}else{ // do insert
									$comm_upd[$upd_key] = $upd_val;
									$comm_upd['branch_id'] = $sessioninfo['branch_id'];
									$comm_upd['department_id'] = $ins['dept_id'];
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
							$prms['branch_id'] = $sessioninfo['branch_id'];
							$prms['department_id'] = $ins['dept_id'];
							$prms['skutype_code'] = $ins['trade_discount_code'];
							$prms['rate'] = $ins['trade_discount_rate'];
							
							$this->create_commission_history($prms);
						}
						
						$si_ins = array();
						//if((!$ins['parent_arms_code'] && !$ins['parent_mcode'] && !$ins['parent_artno']) || ($ins['parent_mcode'] == $ins['mcode'] || $ins['parent_artno'] == $ins['artno'])) {
						if($this->item_is_parent($line_index, $ins)){
							//parent sku item
							$sku_ins = array();
							$sku_ins['category_id'] = $ins['category_id'];
							$sku_ins['uom_id'] = $ins['uom_id'];
							$sku_ins['vendor_id'] = $ins['vendor_id'];
							$sku_ins['brand_id'] = $ins['brand_id'];
							$sku_ins['status'] = 1;
							$sku_ins['active'] = 1;
							$sku_ins['sku_type'] = $ins['sku_type'];
							$sku_ins['apply_branch_id'] = 1;
							$sku_ins['added'] = 'CURRENT_TIMESTAMP';
							if($ins['po_reorder_qty_min'] && $ins['po_reorder_qty_max'] || $ins['po_reorder_moq']) {
								$sku_ins['po_reorder_qty_min'] = $ins['po_reorder_qty_min'];
								$sku_ins['po_reorder_qty_max'] = $ins['po_reorder_qty_max'];
								$sku_ins['po_reorder_moq'] = $ins['po_reorder_moq'];
							}
							$sku_ins['scale_type'] = $ins['scale_type'];
							
							if($ins['sku_type'] == "CONSIGN"){
								$sku_ins['default_trade_discount_code'] = $ins['trade_discount_code'];
								if($ins['trade_discount_type'] == "brand") $sku_ins['trade_discount_type'] = 1;
								else $sku_ins['trade_discount_type'] = 2;
							}
						
							$con->sql_query("insert into sku ".mysql_insert_by_field($sku_ins));
							$ins['sku_id'] = intval($con->sql_nextid());
							$ins['sku_code'] = sprintf(ARMS_SKU_CODE_PREFIX, $ins['sku_id']);
							$con->sql_query("update sku set sku_code=".ms($ins['sku_code'])." where id=".$ins['sku_id']);
							
							$ins['sku_item_code'] = sprintf(ARMS_SKU_CODE_PREFIX, $ins['sku_id'])."0000";
							$si_ins['is_parent'] = 1;
							$si_ins['packing_uom_id'] = $ins['uom_id'];
							$si_ins['sku_item_code'] = $ins['sku_item_code'];
						}else{
							//child sku item
							if($ins['parent_arms_code']) {
								$con->sql_query("select sku_id from sku_items where sku_item_code = ".ms($ins['parent_arms_code'])." and is_parent = 1");
							}else{
								$done = 0;
								if($ins['parent_mcode']) {
									$con->sql_query("select sku_id from sku_items where mcode = ".ms($ins['parent_mcode'])." and is_parent = 1");
									if($con->sql_numrows() > 0) {
										$done = 1;
									}
								}
								
								if($ins['parent_artno'] && !$done) {
									$con->sql_freeresult();
									$con->sql_query("select sku_id from sku_items where artno = ".ms($ins['parent_artno'])." and is_parent = 1");									
								}
							}
							$parent_si = $tmp = array();
							$parent_si = $con->sql_fetchassoc();
							$con->sql_freeresult();
							$ins['sku_id'] = mi($parent_si['sku_id']);
							$con->sql_query("select max(sku_item_code) as sku_item_code from sku_items where sku_id =".$ins['sku_id']);
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							$si_ins['sku_item_code'] = $tmp['sku_item_code']+1;
							$si_ins['packing_uom_id'] = $ins['uom_id'];
						}
						
						$si_ins['sku_id'] = $ins['sku_id'];
						$si_ins['mcode'] = $ins['mcode'];
						$si_ins['link_code'] = $ins['link_code'];
						$si_ins['artno'] = $ins['artno'];
						$si_ins['description'] =  $ins['description'];
						$si_ins['receipt_description'] =  $ins['receipt_description'];
						$si_ins['hq_cost'] = $si_ins['cost_price'] = $ins['cost_price'];
						$si_ins['selling_price'] = $ins['selling_price'];
						$si_ins['active'] = 1;
						$si_ins['added'] = 'CURRENT_TIMESTAMP';
						$si_ins['color'] = $ins['color'];
						$si_ins['size'] = $ins['size'];
						$si_ins['inclusive_tax'] = $ins['inclusive_tax'];
						$si_ins['input_tax'] = $ins['input_tax'] ? $this->input_gst_list[$ins['input_tax']]['id'] : -1;
						$si_ins['output_tax'] = $ins['output_tax'] ? $this->output_gst_list[$ins['output_tax']]['id'] : -1;
						if($config['sku_enable_additional_description']){
							$additional_description_list = $additional_description = array();
							$additional_description_list = explode("\n", trim($ins['additional_description']));
							foreach($additional_description_list as $tmp_r=>$add_desc){
								if(!trim($add_desc)) continue;
								$additional_description[] = trim($add_desc);
							}
							if($additional_description) $si_ins['additional_description'] = serialize($additional_description);
							else $si_ins['additional_description'] = "";
						}
						
						if(($ins['rsp_discount'] || trim($ins['rsp_discount']) == 0) && $ins['rsp_price']){
							$si_ins['use_rsp'] = 1;
						}
						$si_ins['rsp_discount'] = $ins['rsp_discount'];
						$si_ins['rsp_price'] = $ins['rsp_price'];
						$con->sql_query("insert into sku_items ".mysql_insert_by_field($si_ins));
						$num = $con->sql_affectedrows();
						$num = 1;
						$ins['sku_item_id'] = $con->sql_nextid();
						
						if($ins['wholesale1'] > 0){
							foreach($this->branch_list as $bid => $b_info){
								$ws1_ins = array();
								$ws1_ins['branch_id'] = $bid;
								$ws1_ins['sku_item_id'] = $ins['sku_item_id'];
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
						
						for($i=count($this->headers[$form['method']]); $i<count($line); $i++) {
							$mprice_type = strtolower($line[$i]);
							if($line[$i] != 'Error' && $ins[$mprice_type] > 0) {
								
								foreach($this->branch_list as $bid => $b_info){
									$mp_ins = array();
									$mp_ins['branch_id'] = $bid;
									$mp_ins['sku_item_id'] = $ins['sku_item_id'];
									$mp_ins['type'] = $mprice_type;
									$mp_ins['last_update'] = "CURRENT_TIMESTAMP";
									$mp_ins['price'] = $ins[$mprice_type];
									$con->sql_query("insert into sku_items_mprice ".mysql_insert_by_field($mp_ins));
									
									unset($mp_ins['last_update']);
									$mp_ins['added'] = "CURRENT_TIMESTAMP";
									$mp_ins['user_id'] = 1;
									$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($mp_ins));
								}
							}
						}
						if ($num > 0)	$num_row++;
					}
					break;
				default:
					break;
			}
		}
		
		if ($num_row > 0) {	
			$ret['ok'] = 1;
		}else $ret['fail'] = 1;
		
		// did a category tree update for the new category
		build_category_cache();
		
		print json_encode($ret);
		log_br($sessioninfo['id'], "SKU_MIGRATION", 0, "SKU Migrated Successfully, Files Reference: ".$form['file_name']);
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

	/*function get_brand_by_code($brand_code){
		global $con;
		
		if(!$brand_code) return;
		
		$con->sql_query("select id from brand where code=".ms(substr($brand_code,0,6)));
		$brand_info = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		return $brand_info['id'];
	}

	function get_vendor_by_code($vendor_code){
		global $con;
		
		if(!$vendor_code) return;
		
		$con->sql_query("select * from vendor where code=".ms(substr($vendor_code,0,10)));
		$vendor_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $vendor_info['id'];
	}*/
	
	function get_brand_by_desc($brand_desc){
		global $con;
		
		if(!$brand_desc) return;
		
		$con->sql_query("select id from brand where description=".ms($brand_desc));
		$brand_info = $con->sql_fetchrow();
		$con->sql_freeresult();
		
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
	
	function get_uom_by_code($uom_code){
		global $con;
		
		$uom_code = trim($uom_code);
		if(!$uom_code) return;
		
		$con->sql_query("select * from uom where code=".ms($uom_code));
		$uom_info = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $uom_info;
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
}

$IMPORT_SKU = new IMPORT_SKU("Import SKU");
?>
