<?php
/*
12/21/2010 5:03:03 PM Andy
- Fix if the file got error it still try to loop and get invalid resource error

6/24/2011 3:03:22 PM Andy
- Make all branch default sort by sequence, code.

9/20/2012 5:03 PM Drkoay
- add view_sample_by_type();
- add import_selling_by_type()
- add get_last_cost();

5/21/2013 5:34 PM Justin
- Bug fixed on system does not update sku_items after imported new selling price that causes counter cannot get new prices.

6/23/2012 1:03 AM Andy
- Enhance the import selling by branch to able to accept mcode, artno and linkcode.

07/31/2013 02:06 PM Justin
- Enhanced to add "No Auto Sync to Counters" feature.

11/8/2014 1:17 PM Justin
- Enhanced to have import items's GST info (input, output and inclusive tax).

1/30/2015 3:32 PM Justin
- Enhanced to have new feature that can import into batch price change.

3/23/2015 5:04 PM Justin
- Enhanced to allow user update GST info for SKU items without the need to update selling price.
- Added new sample that shows update GST info only.

3/27/2015 9:41 AM Justin
- Bug fixed gst info capture wrongly while config is not turned on.

3/27/2015 2:01 PM Justin
- Bug fixed on filter of SKU Items.

3/30/2015 5:01 PM Justin
- Enhanced to skip those prices that are current same with system.

1/9/2017 3:19 PM Andy
- Enhanced to not allow to update inclusive tax.

7/7/2017 5:39 PM Justin
- Bug fixed on Generate Batch Price Change will generate PHP errors when import the file from sample.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($sessioninfo['level']<9999) header("Location: /");
set_time_limit(0);

class IMPORT_SELLING_PRICE extends Module{
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
		$this->branches_code = array();
		$con->sql_query_false("select * from branch order by sequence, code", true);
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
			$this->branches_code[strtolower($r['code'])] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches', $this->branches);		
	}
	
	function view_sample(){
	    header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_selling.csv");
		
		if($_REQUEST['gst_only']){
			print "\"ARMS Code\",\"MCode\",\"Art No\",\"Link Code\",\"Input Tax\",\"Output Tax\"\n";
			print "\"280000010000\",\"95550\",\"cn100\",\"12345\",\"TX\",\"SR\"\n";
			print "\"280000020000\",\"95551\",\"cn101\",\"12346\",\"IM\",\"DS\"\n";
			print "\"280000030000\",\"95552\",\"cn102\",\"12347\",\"BL\",\"AJS\"\n";
		}else{
			print "\"ARMS Code\",\"MCode\",\"Art No\",\"Link Code\",\"Input Tax\",\"Output Tax\",\"Normal Price\",\"Member1\",\"Member2\"\n";
			print "\"280000010000\",\"95550\",\"cn100\",\"12345\",\"TX\",\"SR\",\"12.9\",\"10.9\",\"\"\n";
			print "\"280000020000\",\"95551\",\"cn101\",\"12346\",\"IM\",\"DS\",\"13.9\",\"\",\"\"\n";
			print "\"280000030000\",\"95552\",\"cn102\",\"12347\",\"BL\",\"AJS\",\"14.9\",\"13.9\",\"14\"\n";
		}
	}
	
	function import_selling(){
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
				$branches[$bid] = $bid;
			}
		}
		$mprice_starting_column = 7;
		
	    if(!$err){
            // no problem found, safe to read
            $f = $_FILES['import_csv'];
			$fp = fopen($f['tmp_name'], "r");
	        $header_line = fgetcsv($fp);	// get 1st header line
	        //print_r($header_line);
	        if(!$header_line)   $err[] = "The file contain no data.";   // no data found
	        else{
				// check header line
				if(count($header_line)<$mprice_starting_column){
					$err[] = "File format incorrect, must at least contain column ARMS Code,MCode,Art No.,Link Code, Input Tax, Output Tax and normal selling price.";
				}else{
				    if(count($header_line)>$mprice_starting_column){
                        if(!$config['sku_multiple_selling_price'] || !is_array($config['sku_multiple_selling_price'])){
                            $err[] = "SKU multiple selling price is not set";
						}else{
						    $mprice_list = array();
                            foreach($header_line as $key=>$colname){
		                        $colname = strtolower($colname);    // make it all lowercase

								if($key>=$mprice_starting_column){ // start checking mprice
									if(!in_array($colname, $config['sku_multiple_selling_price'])){
                                        $err[] = "Invalid MPrice Type: $colname";
									}else{
                                        $mprice_list[$key] = $colname;
									}
								}
							}
						}
					}
				}
			}
			if($err)    fclose($fp);
		}
	    if($err){   // got error found
			$smarty->assign('import_type', 'by_branch');
			$smarty->assign('err', $err);
			$this->display();
			exit;
		}
		
		//print_r($mprice_list);exit;
        $max_mprice_length = count($header_line);
        $total_affected = 0;
        $mprice_affected = 0;
		
		if($config['enable_gst']){
			$q1 = $con->sql_query("select * from gst");
			while($r = $con->sql_fetchassoc($q1)){
				$gst_list[$r['code']] = $r;
			}
			$con->sql_freeresult($q1);
		}
        
		$si_list = $dept_list = array();
		while (($data = fgetcsv($fp)) !== FALSE) {
			$sku_item_code = trim($data[0]);
			$mcode = trim($data[1]);
			$artno = trim($data[2]);
			$linkcode = trim($data[3]);
			$input_tax = trim($data[4]);
			$output_tax = trim($data[5]);
			//$inclusive_tax = trim($data[6]);
			$normal_selling = mf($data[6]);
			
			
			if(!$sku_item_code && !$mcode && !$artno && !$linkcode) continue;
			$filter = array();
			if($sku_item_code)	$filter[] = "si.sku_item_code=".ms($sku_item_code);
			if($mcode)	$filter[] = "si.mcode=".ms($mcode);
			if($artno)	$filter[] = "si.artno=".ms($artno);
			if($linkcode)	$filter[] = "si.link_code=".ms($linkcode);
			
			$filter = join(' and ', $filter);
			
			$q1 = $con->sql_query("select si.id, si.sku_item_code, si.selling_price, sku.default_trade_discount_code,
								   si.input_tax, si.output_tax, si.inclusive_tax, cc.p2 as dept_id, ifnull(sic.grn_cost, si.cost_price) as cost_price
								   from sku_items si
								   left join sku on sku.id=si.sku_id
								   left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($sessioninfo['branch_id'])."
								   left join category_cache cc on cc.category_id = sku.category_id
								   where $filter order by si.id limit 1");

			$master_item = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			$sid = mi($master_item['id']);
			if($config['enable_gst']) $gst_info = get_sku_gst("output_tax", $master_item['id']);
			
			if(!$sid){
				$msg['warning'][] = "sku_item_code#$sku_item_code, mcode#$mcode, artno#$artno, linkcode#$linkcode is invalid";
				continue;
			}

			if($_REQUEST['import_method'] == "selling"){ // straight apply change price
				$is_updated = false;
				foreach($branch_id_list as $bid){
					if($normal_selling>0){
						// normal selling price
						$q1 = $con->sql_query("select * from sku_items_price where branch_id=$bid and sku_item_id=$sid");
						$sip = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						// check and skip price change if found same price
						if($sip) $sp = $sip['price'];
						else $sp = $master_item['selling_price'];
						
						if($normal_selling != $sp){
							$new_sip = array();
							$new_sip['branch_id'] = $bid;
							$new_sip['sku_item_id'] = $sid;
							$new_sip['last_update'] = 'CURRENT_TIMESTAMP';
							$new_sip['price'] = $normal_selling;
							$new_sip['cost'] = $sip['cost'] ? $sip['cost'] : $master_item['cost_price'];
							$new_sip['trade_discount_code'] = $sip['trade_discount_code'] ? $sip['trade_discount_code'] : $master_item['default_trade_discount_code'];
							// sku items price
							$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($new_sip));
							
							// sku items price history
							unset($new_sip['last_update']);
							$new_sip['added'] = 'CURRENT_TIMESTAMP';
							$new_sip['source'] = 'IMPORT';
							$new_sip['user_id'] = $sessioninfo['id'];
							$con->sql_query("replace into sku_items_price_history ".mysql_insert_by_field($new_sip));
							$is_updated = true;
						}
					}
					
					for($i = $mprice_starting_column; $i <= $max_mprice_length; $i++){    // loop all mprice
						$mprice_type = $mprice_list[$i];
						$mprice_price = mf($data[$i]);
						
						if(!$mprice_type || $mprice_price<=0)   continue;   // no price or no type
						
						// select latest mprice
						$q1 = $con->sql_query("select * from sku_items_mprice where branch_id=$bid and sku_item_id=$sid and type=".ms($mprice_type));
						$simp = $con->sql_fetchassoc($q1);
						$con->sql_freeresult($q1);
						
						// check and skip price change if found same price
						if($simp) $sp = $simp['price'];
						else $sp = $master_item['selling_price'];
						
						if($mprice_price == $sp) continue;
						
						$new_simp = array();
						$new_simp['branch_id'] = $bid;
						$new_simp['sku_item_id'] = $sid;
						$new_simp['type'] = $mprice_type;
						$new_simp['last_update'] = 'CURRENT_TIMESTAMP';
						$new_simp['price'] = $mprice_price;
						$new_simp['trade_discount_code'] = $simp['trade_discount_code'] ? $simp['trade_discount_code'] : $master_item['default_trade_discount_code'];
						
						// sku items mprice
						$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($new_simp));
						
						// sku items mprice history
						unset($new_simp['last_update']);
						$new_simp['added'] = 'CURRENT_TIMESTAMP';
						$new_simp['user_id'] = $sessioninfo['id'];
						$con->sql_query("replace into sku_items_mprice_history ".mysql_insert_by_field($new_simp));
						$is_updated = true;
					}
					
				}
				$total_affected++;
			}else{ // import as batch price change
			
				// import normal selling price
				$q1 = $con->sql_query("select * from sku_items_price where branch_id=".mi($bid)." and sku_item_id=".mi($sid));
				$sip = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				// check and skip price change if found same price
				if($sip) $sp = $sip['price'];
				else $sp = $master_item['selling_price'];
				
				if($normal_selling != $sp){
					$item = array();
					$item['branch_id'] = $sessioninfo['branch_id'];
					$item['sku_item_id'] = $sid;
					$item['dept_id'] = $master_item['dept_id'];
					$item['cost'] = $master_item['cost_price'];
					$item['selling_price'] = $sip['price'];
					$item['type'] = "normal";
					$item['trade_discount_code'] = $sip['trade_discount_code'] ? $sip['trade_discount_code'] : $master_item['default_trade_discount_code'];
					$item['future_selling_price'] = $normal_selling;
					
					// if found having new GST from CSV, take from there
					if($config['enable_gst'] && $gst_list[$output_tax]['id'] > 0){
						if($gst_list[$output_tax]['id'] != $master_item['output_tax']){
							$gst_id = $item['gst_id'] = $gst_list[$output_tax]['id'];
							$gst_code = $item['gst_code'] = $gst_list[$output_tax]['code'];
							$gst_rate = $item['gst_rate'] = $gst_list[$output_tax]['rate'];
						}else{ // otherwise just take from current info from db
							$gst_id = $item['gst_id'] = $gst_info['id'];
							$gst_code = $item['gst_code'] = $gst_info['code'];
							$gst_rate = $item['gst_rate'] = $gst_info['rate'];
						}
					}

					// store into an array by department
					$si_list[$master_item['dept_id']][] = $item;
				}
				
				// import multiple selling price
				for($i = $mprice_starting_column; $i <= $max_mprice_length; $i++){    // loop all mprice
					$mprice_type = $mprice_list[$i];
					$mprice_price = mf($data[$i]);
					
					if(!$mprice_type || $mprice_price<=0)   continue;   // no price or no type
					
					// select latest mprice
					$q1 = $con->sql_query("select * from sku_items_mprice where branch_id=".mi($bid)." and sku_item_id=".mi($sid)." and type=".ms($mprice_type));
					$simp = $con->sql_fetchassoc($q1);
					$con->sql_freeresult($q1);
					
					// check and skip price change if found same price
					if($simp) $sp = $simp['price'];
					else $sp = $master_item['selling_price'];
					
					if($mprice_price == $sp) continue;
				
					$item = array();
					$item['branch_id'] = $sessioninfo['branch_id'];
					$item['sku_item_id'] = $sid;
					$item['dept_id'] = $master_item['dept_id'];
					$item['cost'] = $master_item['cost_price'];
					$item['selling_price'] = $simp['price'];
					$item['type'] = $mprice_type;
					$item['trade_discount_code'] = $simp['trade_discount_code'] ? $simp['trade_discount_code'] : $master_item['default_trade_discount_code'];
					$item['future_selling_price'] = $mprice_price;
					
					// if found having new GST from CSV, take from there
					if($config['enable_gst'] && $gst_id > 0){
						$item['gst_id'] = $gst_id;
						$item['gst_code'] = $gst_code;
						$item['gst_rate'] = $gst_rate;
					}
					
					// store into an array by department
					$si_list[$master_item['dept_id']][] = $item;
					$dept_list[$master_item['dept_id']] = $master_item['dept_id'];
				}
				$is_updated = false; // set to no since no updates to sku
			}
			
			if($config['enable_gst'] && (($input_tax && $master_item['input_tax'] != $gst_list[$input_tax]['id']) || ($output_tax && $master_item['output_tax'] != $gst_list[$output_tax]['id']))){
				$upd = array();
				if($gst_list[$input_tax]['id']) $upd['input_tax'] = $gst_list[$input_tax]['id'];
				if($gst_list[$output_tax]['id']) $upd['output_tax'] = $gst_list[$output_tax]['id'];
				//if($inclusive_tax) $upd['inclusive_tax'] = $inclusive_tax;
				$upd['lastupdate'] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($sid));
			}elseif($is_updated && !$_REQUEST['no_sync']){
				$con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id = ".mi($sid));
			}
		}
		
		if($_REQUEST['import_method'] == "batch_selling"){
			asort($dept_list);
			foreach($dept_list as $dept_id){
				usort($si_list[$dept_id], array($this, "sort_by_dept")); // sort by dept & sku
				for($i=0; $i <= count($si_list[$dept_id]); $i+=500){
					$item_list = array_slice($si_list[$dept_id], $i, 500);
					
					if($i==0 || $item_list >= 50){ // if found more than 50 items, always create new header
						// insert into batch price change
						$ins = array();
						$ins['branch_id'] = $sessioninfo['branch_id'];
						$ins['date'] = date("Y-m-d", time());
						$ins['effective_branches'] = serialize($branches);
						$ins['active'] = 1;
						$ins['status'] = $ins['approved'] = $ins['cron_status'] = $ins['approval_history_id'] = 0;
						$ins['user_id'] = $sessioninfo['id'];
						$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
						
						$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
						$new_fp_id = $con->sql_nextid();
					}
					
					// insert into batch price change items
					foreach($item_list as $r){
						$ins = array();
						$ins['fp_id'] = $new_fp_id;
						$ins['branch_id'] = $sessioninfo['branch_id'];
						$ins['sku_item_id'] = $r['sku_item_id'];
						$ins['cost'] = $r['cost'];
						$ins['selling_price'] = $r['selling_price'];
						$ins['type'] = $r['type'];
						$ins['trade_discount_code'] = $r['trade_discount_code'];
						$ins['future_selling_price'] = $r['future_selling_price'];
						$ins['gst_id'] = $r['gst_id'];
						$ins['gst_code'] = $r['gst_code'];
						$ins['gst_rate'] = $r['gst_rate'];
						
						$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
					}
					
					$fp_id_list[] = $new_fp_id;
				}
			}
			
			if($fp_id_list){
				log_br($sessioninfo['id'], 'SKU', '', "Import SKU Selling into Batch Price Change, ID created: ".join(",", $fp_id_list));
				$smarty->assign('fp_id_list', $fp_id_list);
			}
		}else{
			// move history file
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments",0777);
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/import_selling"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/import_selling",0777);
			$history_file = time().".".$f['name'];
			move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/import_selling/$history_file");
			log_br($sessioninfo['id'], 'SKU', '', "Import SKU Selling using $history_file, $total_affected items.");
			$smarty->assign('total_affected', $total_affected);
		}
		
		fclose($fp);    // close the file connection
		
		$smarty->assign('import_type', 'by_branch');
		$smarty->assign('import_method', $_REQUEST['import_method']);
		$smarty->assign('msg', $msg);
		$smarty->assign('import_success', 1);
		$this->display();
	}
	
	function view_sample_by_type(){
		global $con, $smarty, $sessioninfo, $config;
						
		header("Content-type: application/msexcel");
		header("Content-Disposition: attachment; filename=sample_import_selling_by_type.csv");
		print "\"ARMS Code\",\"MCode\",\"Art No\"";
		
		if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
			print ",\"HQ\"";
			
			$region_branch = load_region_branch_array(array('inactive_change_price'=>1));
			
			/*echo '<pre>';
			print_r($region_branch);
			echo '</pre>';
			echo '<pre>';
			print_r($config['masterfile_branch_region']);
			echo '</pre>';
			
			die();*/
			foreach($config['masterfile_branch_region'] as $region_code =>$rg){
				print ",\"".$rg['name']."\"";
			}
			
			foreach($region_branch['no_region'] as $region_code =>$b){
				print ",\"".$b['code']."\"";
			}
			print "\n";
			
			print "\"280121940000\",\"BBC020\",\"BBC020\"";
			print ",\"5.00\"";
			foreach($config['masterfile_branch_region'] as $region_code =>$rg){
				print ",\"5.00\"";
			}
			
			foreach($region_branch['no_region'] as $region_code =>$b){
				print ",\"5.00\"";
			}			
		}
		else{
			foreach($this->branches as $b){
				print ",\"".$b['code']."\"";
			}
			print "\n";
			print "\"281144810000\",\"\",\"TP1\"";
			foreach($this->branches as $k=>$b){
				print ",\"2.20\"";
				//print ",\"".$k.".00\"";
			}
		}
	}
	
	function import_selling_by_type(){
		global $con, $smarty, $sessioninfo, $config;
		
		// check file
	    $err = check_upload_file('import_csv', 'csv');
	    
	    // check price type
	    $price_type = $_REQUEST['price_type'];
	    if(!$price_type || !is_array($price_type)){
			$err[] = "Please select at least one price type.";
		}else{
			foreach($price_type as $key=>$t){				
                if(trim($t)==''){					
					$err[] = "Invalid price type";
					break;
				}
			}
		}
		$branch_starting_column = 3;
		
	    if(!$err){
			// no problem found, safe to read
            $f = $_FILES['import_csv'];
			$fp = fopen($f['tmp_name'], "r");
	        $header_line = fgetcsv($fp);	// get 1st header line
	        
	        if(!$header_line)   $err[] = "The file contain no data.";   // no data found
	        else{
				// check header line
				if(($header_line[0]!='ARMS Code') || ($header_line[1]!='MCode') || ($header_line[2]!='Art No')){
					$err[] = "File format incorrect, header must start with ARMS Code, MCode, Art No, Input Tax, Outpu Tax and Price Include GST follow by branch code";
				}
				else{
					if(count($header_line)<=$branch_starting_column){
						$err[] = "File format incorrect. Missing Branch code.";
					}
					else{
						$duplicate_check=array_count_values($header_line);
						
						foreach($duplicate_check as $k=>$c){
							if($c>1){
								$err[] = "Duplicate branch code ( ".$k." ) found.";
								break;
							}
						}
						
						if(!$err){
							$max_mprice_length = count($header_line);
							
							if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
								$valid_branches_code=array();
								$region_branch = load_region_branch_array(array('inactive_change_price'=>1));
								
								foreach($region_branch['got_region'] as $region_code=>$region){
									foreach($region as $b){
										$invalid_branches_code[strtolower($b['code'])]=$b;
									}
								}
								$valid_branches_code[]='hq';
								foreach($config['masterfile_branch_region'] as $region_code =>$rg){
									$valid_branches_code[]=strtolower($rg['name']);
								}
														
								foreach($region_branch['no_region'] as $region_code =>$b){
									$valid_branches_code[]=strtolower($b['code']);
								}
								
								for($i=$branch_starting_column;$i<$max_mprice_length;$i++){
									$branch=strtolower($header_line[$i]);
									
									if(!in_array($branch,$valid_branches_code)){
										if(isset($invalid_branches_code[$branch])){
											$err[] = "Invalid branch code ( ".$header_line[$i]." ) found. This branch is under region ".$config['masterfile_branch_region'][$invalid_branches_code[$branch]['region']]['name'].".";
										}
										else{
											$err[] = "Invalid branch code ( ".$header_line[$i]." ) found.";
										}
									}
								}								
							}
							else{
								for($i=$branch_starting_column;$i<$max_mprice_length;$i++){
									if(!isset($this->branches_code[strtolower($header_line[$i])])){
										$err[] = "Invalid branch code ( ".$header_line[$i]." ) found.";
									}
								}
							}
						}
					}
				}
			}
			if($err)    fclose($fp);	
		}
		if($err){   // got error found
			$smarty->assign('import_type', 'by_type');
			$smarty->assign('err', $err);
			$this->display();
			exit;
		}
		
		$con->sql_query("select * from trade_discount_type order by code");
		while($r = $con->sql_fetchassoc()){
			$price_type_list[]=strtoupper($r['code']);
		}		
		$con->sql_freeresult();

		if($config['enable_gst']){
			$q1 = $con->sql_query("select * from gst");
			while($r = $con->sql_fetchassoc($q1)){
				$gst_list[$r['code']] = $r;
			}
			$con->sql_freeresult($q1);
		}
		
        $total_affected = 0;
       
		$currenttime=date('Y-m-d h:i:s');
		$currentdate=date('Y-m-d');
		$row_count=2;
		while (($data = fgetcsv($fp)) !== FALSE) {
			$armscode=trim($data[0]);
			$mcode=trim($data[1]);
			$artno=trim($data[2]);
			//$input_tax = trim($data[3]);
			//$output_tax = trim($data[4]);
			//$inclusive_tax = trim($data[5]);
			
			if($armscode=="" && $mcode=="" && $artno=="") continue;
			
			$where="where si.active=1";
			if($armscode!="") $where.=" and `sku_item_code`=".ms($armscode);
			if($mcode!="") $where.=" and `mcode`=".ms($mcode);			
			if($artno!="") $where.=" and `artno`=".ms($artno);
					
			$sql="select si.*,s.default_trade_discount_code, cc.p2 as dept_id,
				  si.input_tax, si.output_tax, si.inclusive_tax, cc.p2 as dept_id, ifnull(sic.grn_cost, si.cost_price) as cost_price
				  from sku_items si 
				  left join sku s on si.sku_id=s.id 
				  left join sku_items_cost sic on sic.sku_item_id = si.id and sic.branch_id = ".mi($sessioninfo['branch_id'])."
				  left join category_cache cc on cc.category_id = s.category_id
				  ".$where;
			$si=$con->sql_query_false($sql, true);
			
			if($con->sql_numrows($si) == 0){
				$msg['warning'][] = "sku_item_code#$armscode, mcode#$mcode, artno#$artno is invalid";
				continue;
			}

			while($r = $con->sql_fetchassoc($si)){
				if($config['enable_gst']){
					$gst_info = get_sku_gst("output_tax", $r['id']);
					$gst_id = $gst_info['id'];
					$gst_code = $gst_info['code'];
					$gst_rate = $gst_info['rate'];
				}
				
				for($i=$branch_starting_column;$i<$max_mprice_length;$i++){
					$rprice=false;
					$region_code="";
					$branch_code=$header_line[$i];
					$branch=array();
					
					//skip empty column
					if($data[$i]=="") continue;
					
					$d=explode("/",$data[$i]);
					$price=floatval($d[0]);
					//$trade_discount_code=strtoupper($d[1]);
					$trade_discount_code=$r['default_trade_discount_code'];
					
					if($price<=0){
						$msg['warning'][] = "Invalid price found in row $row_count column ".$branch_code.". Item is skip from import.";
						continue;
					}
					
					if(trim(strtoupper($d[1]))!=""){
						if(in_array(strtoupper($d[1]),$price_type_list)){
							$trade_discount_code=strtoupper($d[1]);			
						}
						else{
							$msg['warning'][] = "Invalid trade discount code ( ".$d[1]." ) found in row $row_count column ".$branch_code.". Default trade discount code ( $trade_discount_code ) is used.";
						}
					}
					
					if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
						
						foreach($config['masterfile_branch_region'] as $r_code =>$rg){
							if(strtolower($rg['name'])==strtolower($branch_code)){
								$region_code=$r_code;
								break;
							}
						}
						
						if(isset($region_branch['got_region'][$region_code])){
							$rprice=true;
							foreach($region_branch['got_region'][$region_code] as $b){
								$branch[]=$b;
							}							
						}
						else{
							if(isset($this->branches_code[strtolower($branch_code)])) $branch[]=$this->branches_code[strtolower($branch_code)];
						}
					}
					else{
						if(isset($this->branches_code[strtolower($branch_code)])) $branch[]=$this->branches_code[strtolower($branch_code)];
					}
					
					//skip if no branches is found
					if(empty($branch)) continue;

					if($_REQUEST['import_method'] == "selling"){
						$is_updated = false;
						foreach($branch as $b){
							$from=array();
							$form['user_id'] = $sessioninfo['id'];
							$form['branch_id'] = $b['id'];
							$form['source'] = 'SKU';
							$form['sku_item_id'] = $r['id'];
							$form['code']=$r['sku_item_code'];			
							$form['price'] = $price;
							$form['trade_discount_code'] = $trade_discount_code;
							$this->get_last_cost($form);
							
							if(in_array('normal',$price_type)){
								if($this->check_sku_item_changes($form,"select price, trade_discount_code from sku_items_price where branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($form['sku_item_id']))){
									//echo "insert into sku_items_price_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id"))."<br/>";
									$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id")));
									$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code")));
									//$con->sql_query("update sku_items set lastupdate = ".ms($currenttime)." where id = $form[sku_item_id]");
									$total_affected++;
									$is_updated = true;
								}
							}						
							if($rprice){
								$upd = array();
								$upd['region_code'] = $region_code;
								$upd['sku_item_id'] = $r['id'];
								$upd['mprice_type'] = 'normal';
								$upd['price'] = $price;
								$upd['trade_discount_code'] = $trade_discount_code;
								
								if(in_array('normal',$price_type)){
									//check need update or not
									if($this->check_sku_item_changes($form,"select price, trade_discount_code from sku_items_rprice where region_code = ".ms($upd['region_code'])." and sku_item_id = ".mi($upd['sku_item_id'])." and mprice_type = ".ms($upd['mprice_type']))){		
										$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
										// update - sku_items_rprice_history
										$upd['date'] = $currentdate;
										$upd['user_id'] = $sessioninfo['id'];
										$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
										
										$total_affected++;
										$is_updated = true;
									}
								}
								
								if (isset($config['sku_multiple_selling_price'])){
									foreach($config['sku_multiple_selling_price'] as $mprice_type){
										if(in_array($mprice_type,$price_type)){
											$upd['mprice_type'] = $mprice_type;
											
											if($this->check_sku_item_changes($form,"select price, trade_discount_code from sku_items_rprice where region_code = ".ms($upd['region_code'])." and sku_item_id = ".mi($upd['sku_item_id'])." and mprice_type = ".ms($upd['mprice_type']))){	
											
												$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
										
												// update - sku_items_rprice_history
												$upd['date'] = $currentdate;
												$upd['user_id'] = $sessioninfo['id'];
												$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
												$total_affected++;
												$is_updated = true;
											}
										}
									}
								}							
							}else{						
								if($config['sku_multiple_selling_price']){							
									foreach($config['sku_multiple_selling_price'] as $mprice_type){
										if(in_array($mprice_type,$price_type)){
											$form['type'] = $mprice_type;										
											//check need update or not
											if($this->check_sku_item_changes($form,"select price, trade_discount_code from sku_items_mprice where type = ".ms($mprice_type)." and branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($form['sku_item_id']))){
												$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "type", "price", "trade_discount_code", "user_id")));
												$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "trade_discount_code", "type")));
												//$con->sql_query("update sku_items set lastupdate = ".ms($currenttime)." where id = $form[sku_item_id]");
												$total_affected++;
												$is_updated = true;
											}
										}
									}
								}
							}
						}//end foreach
					}else{ // import as batch price change
						foreach($branch as $b){
							if(in_array('normal',$price_type)){
								// import normal selling price
								$q1 = $con->sql_query("select * from sku_items_price where branch_id=".mi($b['id'])." and sku_item_id=".mi($r['id']));
								$sip = $con->sql_fetchassoc($q1);
								$con->sql_freeresult($q1);
								
								// check and skip price change if found same price
								if($sip) $sp = $sip['price'];
								else $sp = $r['selling_price'];
								
								if($price != $sp){
									$item = array();
									$item['branch_id'] = $sessioninfo['branch_id'];
									$item['sku_item_id'] = $r['id'];
									$item['dept_id'] = $r['dept_id'];
									$item['cost'] = $r['cost_price'];
									$item['selling_price'] = $sip['price'];
									$item['type'] = "normal";
									$item['trade_discount_code'] = $sip['trade_discount_code'] ? $sip['trade_discount_code'] : $r['default_trade_discount_code'];
									$item['future_selling_price'] = $price;
									
									// if found having new GST from CSV, take from there
									/*if($config['enable_gst'] && $gst_list[$output_tax]['id'] > 0){
										if($gst_list[$output_tax]['id'] != $r['output_tax']){
											$gst_id = $item['gst_id'] = $gst_list[$output_tax]['id'];
											$gst_code = $item['gst_code'] = $gst_list[$output_tax]['code'];
											$gst_rate = $item['gst_rate'] = $gst_list[$output_tax]['rate'];
										}else{ // otherwise just take from current info from db
											$gst_id = $item['gst_id'] = $gst_info['id'];
											$gst_code = $item['gst_code'] = $gst_info['code'];
											$gst_rate = $item['gst_rate'] = $gst_info['rate'];
										}
									}*/
									if($config['enable_gst'] && $gst_id > 0){
										$item['gst_id'] = $gst_id;
										$item['gst_code'] = $gst_code;
										$item['gst_rate'] = $gst_rate;
									}

									// store into an array by department and branch
									$si_list[$b['id']][$r['dept_id']][] = $item;
								}
							}
							
							// import multiple selling price
							if($config['sku_multiple_selling_price']){
								foreach($config['sku_multiple_selling_price'] as $mprice_type){
									if(in_array($mprice_type, $price_type)){   // loop all mprice
										$mprice_price = mf($price);
										
										if(!$mprice_type || $mprice_price<=0)   continue;   // no price or no type
										
										// select latest mprice
										$q1 = $con->sql_query("select * from sku_items_mprice where branch_id=".mi($b['id'])." and sku_item_id=".mi($r['id'])." and type=".ms($mprice_type));
										$simp = $con->sql_fetchassoc($q1);
										$con->sql_freeresult($q1);
										
										// check and skip price change if found same price
										if($simp) $sp = $simp['price'];
										else $sp = $r['selling_price'];
										
										if($mprice_price == $sp) continue;
									
										$item = array();
										$item['branch_id'] = $sessioninfo['branch_id'];
										$item['sku_item_id'] = $r['id'];
										$item['dept_id'] = $r['dept_id'];
										$item['cost'] = $r['cost_price'];
										$item['selling_price'] = $simp['price'];
										$item['type'] = $mprice_type;
										$item['trade_discount_code'] = $simp['trade_discount_code'] ? $simp['trade_discount_code'] : $r['default_trade_discount_code'];
										$item['future_selling_price'] = $mprice_price;
										
										// if found having new GST from CSV, take from there
										if($config['enable_gst'] && $gst_id > 0){
											$item['gst_id'] = $gst_id;
											$item['gst_code'] = $gst_code;
											$item['gst_rate'] = $gst_rate;
										}
										
										// store into an array by department
										$si_list[$b['id']][$r['dept_id']][] = $item;
										$dept_list[$b['id']][$r['dept_id']] = $r['dept_id'];
									}
								}
							}
						}
						$is_updated = false; // set to no since no updates to sku
					}
				}//end for
				
				/*if($config['enable_gst'] && ($si['input_tax'] != $gst_list[$input_tax]['id'] || $si['output_tax'] != $gst_list[$output_tax]['id'] || $si['inclusive_tax'] != $inclusive_tax)){
					$upd = array();
					$upd['input_tax'] = $gst_list[$input_tax]['id'];
					$upd['output_tax'] = $gst_list[$output_tax]['id'];
					$upd['inclusive_tax'] = $inclusive_tax;
					$upd['lastupdate'] = "CURRENT_TIMESTAMP";
					
					$con->sql_query("update sku_items set ".mysql_update_by_field($upd)." where id = ".mi($r['id']));
				}*/
				
				if($is_updated && !$_REQUEST['no_sync']){
					$con->sql_query("update sku_items set lastupdate=CURRENT_TIMESTAMP where id = ".mi($r['id']));
				}
			}//end while
			$con->sql_freeresult($si);
			$row_count++;
		}//end while
		fclose($fp);    // close the file connection
		
		if($_REQUEST['import_method'] == "batch_selling"){
			foreach($si_list as $bid=>$tmp_si_list){
				asort($dept_list[$bid]);
				// serialise the branch
				$branches = array();
				$branches[$bid] = $bid;
				foreach($dept_list[$bid] as $dept_id){
					usort($tmp_si_list[$dept_id], array($this, "sort_by_dept")); // sort by dept & sku
					for($i=0; $i <= count($tmp_si_list[$dept_id]); $i+=500){
						$item_list = array_slice($tmp_si_list[$dept_id], $i, 500);
						
						if($i==0 || $item_list >= 50){ // if found more than 50 items, always create new header
							// insert into batch price change
							$ins = array();
							$ins['branch_id'] = $sessioninfo['branch_id'];
							$ins['date'] = date("Y-m-d", time());
							$ins['effective_branches'] = serialize($branches);
							$ins['active'] = 1;
							$ins['status'] = $ins['approved'] = $ins['cron_status'] = $ins['approval_history_id'] = 0;
							$ins['user_id'] = $sessioninfo['id'];
							$ins['added'] = $ins['last_update'] = "CURRENT_TIMESTAMP";
							
							$con->sql_query("insert into sku_items_future_price ".mysql_insert_by_field($ins));
							$new_fp_id = $con->sql_nextid();
						}
						
						// insert into batch price change items
						foreach($item_list as $r){
							$ins = array();
							$ins['fp_id'] = $new_fp_id;
							$ins['branch_id'] = $sessioninfo['branch_id'];
							$ins['sku_item_id'] = $r['sku_item_id'];
							$ins['cost'] = $r['cost'];
							$ins['selling_price'] = $r['selling_price'];
							$ins['type'] = $r['type'];
							$ins['trade_discount_code'] = $r['trade_discount_code'];
							$ins['future_selling_price'] = $r['future_selling_price'];
							$ins['gst_id'] = $r['gst_id'];
							$ins['gst_code'] = $r['gst_code'];
							$ins['gst_rate'] = $r['gst_rate'];
							
							$con->sql_query("insert into sku_items_future_price_items ".mysql_insert_by_field($ins));
						}
						
						$fp_id_list[] = $new_fp_id;
					}
				}
			}
			
			if($fp_id_list){
				log_br($sessioninfo['id'], 'SKU', '', "Import SKU Selling into Batch Price Change, ID created: ".join(",", $fp_id_list));
				$smarty->assign('fp_id_list', $fp_id_list);
			}
		}else{
			// move history file
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments",0777);
			if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/import_selling_by_type"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/import_selling_by_type",0777);
			$history_file = time().".".$f['name'];
			move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/import_selling_by_type/$history_file");
			log_br($sessioninfo['id'], 'SKU', '', "Import SKU Selling by type using $history_file, $total_affected items.");
			$smarty->assign('total_affected', $total_affected);
		}
		
		$smarty->assign('import_type', 'by_type');
		$smarty->assign('import_method', $_REQUEST['import_method']);
		$smarty->assign('msg', $msg);
		$smarty->assign('import_success', 1);
		$this->display();
	}
	
	private function check_sku_item_changes($form,$sql){
		global $con;
		//check need update or not
		$con->sql_query($sql);
		$t=$con->sql_fetchrow();							
		if ($form['price'] != $t['price'] || $form['trade_discount_code'] != $t['trade_discount_code']){
			return true;
		}
		return false;
	}
	
	private function get_last_cost(&$form)
	{
		global $con;
		
		// todo: if cost 0, find last cost from GRN/PO
		$form['cost'] = 0;
		
		$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as cost
		from grn_items
		left join uom on uom_id = uom.id
		left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
		left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
		where grn_items.branch_id = $form[branch_id] and grn.approved and sku_item_id=".ms($form['sku_item_id'])." 
		having cost > 0
		order by grr.rcv_date desc limit 1");
		$c = $con->sql_fetchrow();
		//print "using GRN $c[0]";
		if ($c)
		{
			$form['cost'] = $c[0];
			$form['source'] = 'GRN';
		}
		
		if ($form['cost']==0)
		{
			$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as cost
			from po_items 
			left join po on po_id = po.id and po.branch_id = po.branch_id 
			where po.active and po.approved and po_items.branch_id = $form[branch_id] and sku_item_id=".ms($form['sku_item_id'])." 
			having cost > 0
			order by po.po_date desc limit 1");
			$c = $con->sql_fetchrow();
			//print "using PO $c[0]";
			if ($c)
			{
				$form['cost'] = $c[0];
				$form['source'] = 'PO';
			}
		}
		
		if ($form['cost']==0)
		{
			$con->sql_query("select cost_price from sku_items where id=".ms($form['sku_item_id']));
			$c = $con->sql_fetchrow();
			//print "using MASTER $c[0]";
			if ($c)
			{
				$form['cost'] = $c[0];
				$form['source'] = 'MASTER SKU';
			}
		}
	}
	
	private function sort_by_dept($a, $b) {
		$rdiff = $a['dept_id'] - $b['dept_id'];
		if ($rdiff) return $rdiff; 
		return $a['sku_item_id'] - $b['sku_item_id']; 
	}
}

$IMPORT_SELLING_PRICE = new IMPORT_SELLING_PRICE('Import Selling Price');
?>
