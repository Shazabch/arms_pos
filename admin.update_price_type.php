<?php
/*
6/24/2011 3:16:41 PM Andy
- Make all branch default sort by sequence, code.

10/22/2018 4:43 PM Justin
- Enhanced to check CONCENSSIONAIRE sku type.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) header("Location: /");

class UPDATE_PRICE_TYPE extends Module{
	var $branches = array();
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

        if(!$_REQUEST['skip_init_load'])    $this->init_load(); // initial load
		parent::__construct($title);
	}
	
	function _default(){
	    global $con, $smarty, $sessioninfo;
	    
	    $this->display();
	}

	private function init_load(){
		global $con, $smarty;

		// load branches
		$this->branches = array();
		$con->sql_query_false("select * from branch order by sequence, code", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);
	}
	
	function view_sample(){
	    header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_update_price_type.csv");
		print "\"ARMS Code\",\"Price Type\"\n";
		print "\"280000010000\",\"N1\"\n";
		print "\"280000020000\",\"N2\"\n";
		print "\"280000030000\",\"N3\"\n";
	}
	
	function update_pt(){
	    global $con, $smarty, $sessioninfo, $config;
		
	    // check file
	    $err = check_upload_file('import_csv', 'csv');

	    // check branch id
	    $branch_id_list = $_REQUEST['branch_id'];
	    if(!$branch_id_list || !is_array($branch_id_list)){
			$err[] = "Please select at least one branch.";
		}else{
			foreach($branch_id_list as $key=>$bid){
                $branch_id_list[$key] = $bid = mi($bid);
                if(!$bid){
					$err[] = "Invalid Branch ID $bid";
					break;
				}
			}
		}
		
	    if(!$err){
            // no problem found, safe to read
            $f = $_FILES['import_csv'];
			$fp = fopen($f['tmp_name'], "r");
	        $header_line = fgetcsv($fp);	// get 1st header line
	        
	        if(!$header_line)   $err[] = "The file contain no data.";   // no data found
	        else{
				// check header line
				if(count($header_line)<2){
					$err[] = "File format incorrect, must at least contain ARMS Code and new price type.";
				}elseif(count($header_line)>2){
                    $err[] = "File format incorrect, must contain ARMS Code and new price type only.";
				}
			}
			if($err) fclose($fp);
		}
	    if($err){   // got error found
			$smarty->assign('err', $err);
			$this->display();
			exit;
		}
		
		//print_r($mprice_list);
        $max_mprice_length = count($header_line);
        $total_affected = 0;
        $mprice_affected = 0;

		$con->sql_query("select code from trade_discount_type");
		while($row = $con->sql_fetchrow()){
			$price_type[$row['code']] = 1;
		}
		$con->sql_freeresult();

		$con->sql_query("select id, code from branch");
		while($row = $con->sql_fetchrow()){
			$branches[$row['id']] = $row['code'];
		}
		$con->sql_freeresult();

		while (($data = fgetcsv($fp)) !== FALSE) {
			$sku_item_code = trim($data[0]);
			$new_price_type = trim($data[1]);

			if(!$sku_item_code) continue;
			
			$con->sql_query("select si.id, si.sku_item_code, si.selling_price, si.cost_price, sku.default_trade_discount_code, 
							 sku.sku_type, trade_discount_type, c.department_id, sku.brand_id, sku.vendor_id
							 from sku_items si
							 left join sku on sku.id=si.sku_id
							 left join category c on c.id = sku.category_id
							 where si.sku_item_code=".ms($sku_item_code));
			$master_item = $con->sql_fetchassoc();
			$con->sql_freeresult();
			$sid = mi($master_item['id']);
			
			if(!$sid){
				$msg['warning'][] = "$sku_item_code is invalid";
				continue;
			}

			// check if it is consignment or not
			if($master_item['sku_type'] == "OUTRIGHT"){
				$msg['warning'][] = "$sku_item_code is an outright";
				continue;
			}elseif($master_item['sku_type'] == "CONCESS"){
				$msg['warning'][] = "$sku_item_code is an concessionaire";
				continue;
			}elseif($master_item['sku_type'] == "CONSIGN" && !$price_type[$new_price_type]){ // cannot found follow price type
				$msg['warning'][] = "$sku_item_code update with invalid price type";
				continue;
			}

			// if found sku is not either from brand or vendor, terminate
			if($master_item['trade_discount_type'] == 0 || $master_item['trade_discount_type'] > 2){
				$msg['warning'][] = "$sku_item_code not using trade discount either vendor or brand table";
				continue;
			}

			foreach($branch_id_list as $bid){
				// check if either it is valid in vendor or brand commission or not (brand = 1, vendor = 2)
				if($master_item['trade_discount_type'] == 1){
					$con->sql_query("select rate from brand_commission where department_id=".mi($master_item['department_id'])." and branch_id = ".mi($bid)." and brand_id = ".mi($master_item['brand_id'])." and skutype_code = ".ms($new_price_type)) or die(mysql_error());
				}elseif($master_item['trade_discount_type'] == 2){
					$con->sql_query("select rate from vendor_commission where department_id=".mi($master_item['department_id'])." and branch_id = ".mi($bid)." and vendor_id = ".mi($master_item['vendor_id'])." and skutype_code = ".ms($new_price_type)) or die(mysql_error());
				}
				$rates = $con->sql_fetchassoc();
				$con->sql_freeresult();

				if($rates['rate'] == 0){
					$msg['warning'][] = "$sku_item_code with branch $branches[$bid] - $new_price_type is 0 rate";
					continue;
				}

                // normal selling price
				$con->sql_query("select * from sku_items_price where branch_id=$bid and sku_item_id=$sid");
				$sip = $con->sql_fetchassoc();
				$con->sql_freeresult();
				
				$new_sip = array();
				$new_sip['branch_id'] = $bid;
				$new_sip['sku_item_id'] = $sid;
				$new_sip['last_update'] = 'CURRENT_TIMESTAMP';
				$new_sip['price'] = $sip['price'] ? $sip['price'] : $master_item['selling_price'];
				$new_sip['cost'] = $sip['cost'] ? $sip['cost'] : $master_item['cost_price'];
				$new_sip['trade_discount_code'] = $new_price_type;
				// sku items price
				$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($new_sip));
				
				// sku items price history
				unset($new_sip['last_update']);
				$new_sip['added'] = 'CURRENT_TIMESTAMP';
				$new_sip['source'] = 'IMPORT';
				$new_sip['user_id'] = $sessioninfo['id'];
				$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($new_sip));
			}
			$total_affected++;
		}
		
		// move history file
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments",0777);
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/update_price_type"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/update_price_type",0777);
		$history_file = time().".".$f['name'];
		move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/update_price_type/$history_file");
		log_br($sessioninfo['id'], 'SKU', '', "Update SKU Price Type using $history_file, $total_affected items.");
		
		fclose($fp);    // close the file connection
		
		$smarty->assign('msg', $msg);
		$smarty->assign('import_success', 1);
		$smarty->assign('total_affected', $total_affected);
		$this->display();
	}
}

$UPDATE_PRICE_TYPE = new UPDATE_PRICE_TYPE('Update Price Type');
?>
