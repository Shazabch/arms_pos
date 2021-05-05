<?php
/*
10/28/2011 3:26:31 PM Alex
- created

11/14/2011 3:26:50 PM Alex
- show by receipt no

2/6/2012 6:33:40 PM Alex
- Finalized counter collection, can view only.
- Match multiple, no automatch
- group all results by barcode and price
- change button like counter collection

4/10/2012 5:23:25 PM Alex
- add trade in data => refresh_data()

5/9/2013 1:40 PM Fithri
- bugfix - if got multiple same code is invalid, only replace 1 will cause the module to show all verified, but it is actually still got other un-verify

8/12/2013 10:22 AM Andy
- Fix php decimal bug by rounding selling price to 2 decimal.

9/6/2013 11:44 AM Justin
- Bug fixed on after round selling price by using PHP method, the selling price no longer shows out cents (e.g xx.70).

2/3/2015 3:24 PM Andy
- Enhance to able to change GST when verify invalid sku.

7/13/2016 5:25 PM Andy
- Fixed wrong gst amount when verify sku.

07/14/2016 16:30 Edwin
- Bug fixed on show 'verified' instead of 'not-matched' when sku item is empty but approved by is assigned.

12/19/2016 5:38 PM Andy
- Fixed a bug where special character will cause item cannot be verify.

12/20/2016 9:24 AM Andy
- Enhanced special characters checking.

10/12/2020 5:52 PM William
- Added new tax checking.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_VERIFY_SKU')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_VERIFY_SKU', BRANCH_CODE), "/index.php");

$maintenance->check(240);

class VERIFY_CODE extends Module{

    function __construct($title){
		global $con,$smarty;    

   		$con->sql_query("select * from branch where active=1 order by sequence,code");

		while($r = $con->sql_fetchassoc()){
			$branches[$r['id']] = $r;
		}

		$smarty->assign("branches", $branches);

		$this->branch_id=get_request_branch();
		if (!$_REQUEST['date_select'])	$_REQUEST['date_select'] = date("Y-m-d");		
		$this->date=$_REQUEST['date_select'];
		$this->dd =date("Y-m-d",strtotime("+1 day", strtotime($this->date)));
      	parent::__construct($title);
    }

	function _default(){
		$this->display();
		exit;
	}
	
	function refresh_data(){
		global $con,$smarty,$sessioninfo, $gst_list;
		
		if(!$gst_list)	construct_gst_list();
		
		$_REQUEST['a']="refresh_data";
		$filter[]="pos.cancel_status=0";
		$filter[]="pos.date=".ms($this->date);
		$filter[]="pos.branch_id=".mi($this->branch_id);
		$filter[]="(pi.open_code_by>0 or pi.sku_item_id=0 or (pi.trade_in_by>0 and pi.writeoff_by=0))";
		
		$where = " where ".join(" and ",$filter);

		$sql="select cs.network_name, pos.counter_id, pos.id as pos_id, pos.receipt_no, pos.pos_time, pi.id as pos_items_id, pi.barcode,pi.sku_item_id, pi.sku_description, 
			round(pi.price / pi.qty, 2) as selling_price, if (trade_in_by=0, 'Open Code', 'Trade In') as type, if (trade_in_by=0, ou.u, tu.u) as open_code_user,u_verify.u as verify_user,pi.verify_timestamp,
			si.sku_item_code, si.mcode, si.link_code, si.receipt_description, si.selling_price as org_selling_price,pi.trade_in_by,pi.writeoff_by,pi.more_info,pi.verify_code_by,
			pi.tax_code, pi.tax_indicator,pi.tax_amount,pi.tax_rate, pos.is_gst, pos.is_tax_registered
			from pos_items pi 
			left join pos on pi.pos_id=pos.id and pi.counter_id=pos.counter_id and pi.branch_id=pos.branch_id and pi.date=pos.date
			left join sku_items si on si.id=pi.sku_item_id
			left join user ou on ou.id = pi.open_code_by
			left join user tu on tu.id = pi.trade_in_by 
			left join user u_verify on u_verify.id = pi.verify_code_by
			left join counter_settings cs on cs.id = pos.counter_id and cs.branch_id=pos.branch_id
			$where
			order by pi.barcode,selling_price";
		//print $sql;
		$rid=$con->sql_query($sql);		
		if ($con->sql_numrows($rid) > 0){
			while($r=$con->sql_fetchassoc($rid)){
				$r['ori_barcode'] = $r['barcode'];
				//$r['barcode'] = str_replace('&', 'n', $r['barcode']);
				$r['barcode'] = str_replace(' ', '-', $r['barcode']); // Replaces all spaces with hyphens.
				$r['barcode'] = preg_replace('/[^A-Za-z0-9\-]/', '', $r['barcode']); // Removes special chars.

				// this item already write-off, no need verify
				if($r['trade_in_by'] && $r['writeoff_by'])	continue;
				
				$r['more_info'] = unserialize($r['more_info']);
				//$r['selling_price'] = round($r['selling_price'], 2);	// fix php decimal bug
				
				// got serial number	
				if($r['more_info']['trade_in']['serial_no']){
					if(!is_array($data[$r['barcode']][$r['selling_price']]['serial_list'])){
						$data[$r['barcode']][$r['selling_price']]['serial_list'] = array();
					}
					
					$serial_no = $r['more_info']['trade_in']['serial_no'];
					//print "s = $serial_no<br />";
					if(!$data[$r['barcode']][$r['selling_price']]['serial_list'][$serial_no]){
						$serial_data = array();
						$serial_data['serial_no'] = $serial_no;
						
						if($r['verify_code_by'] && $r['sku_item_id']){
							// check whether this serial number already created
							$con->sql_query("select branch_id,id from pos_items_sn where sku_item_id=".mi($r['sku_item_id'])." and serial_no=".ms($serial_no));
							$serial_data['pos_items_sn'] = $con->sql_fetchassoc();
							$con->sql_freeresult();
						}
						$data[$r['barcode']][$r['selling_price']]['serial_list'][$serial_no] = $serial_data;
					}
					
				}
				
				//calculate total transaction
				$counter_network_pos_receipt=$r['counter_id']."-".$r['network_name']."-".$r['pos_id']."-".$r['receipt_no'];
				$tmp_no_of_transaction[$r['barcode']][$r['selling_price']][$counter_network_pos_receipt]=1;
				
				// gst info
				if($r['tax_code'] || $r['more_info']['ori_gst_info']){
					// old 
					if($r['more_info']['ori_gst_info']){
						$old_tax_code = $r['more_info']['ori_gst_info']['tax_code'];
						$old_tax_indicator = $r['more_info']['ori_gst_info']['tax_indicator'];
						$old_tax_amount = $r['more_info']['ori_gst_info']['tax_amount'];
						$old_tax_rate = mf($r['more_info']['ori_gst_info']['tax_rate']);
						$old_before_tax_price = $r['more_info']['ori_gst_info']['before_tax_price'];
					}else{
						$old_tax_code = $r['tax_code'];
						$old_tax_indicator = $r['tax_indicator'];
						$old_tax_amount = $r['tax_amount'];
						$old_tax_rate = mf($r['tax_rate']);
						$old_before_tax_price = $r['before_tax_price'];
					}
					
					$gst_key = $old_tax_indicator."-".$old_tax_rate;
					//print $r['barcode']." = $gst_key<br>";
					$data[$r['barcode']][$r['selling_price']]['gst_info']['old'][$gst_key]['tax_indicator'] = $old_tax_indicator;
					$data[$r['barcode']][$r['selling_price']]['gst_info']['old'][$gst_key]['tax_rate'] = $old_tax_rate;
					$data[$r['barcode']][$r['selling_price']]['gst_info']['old'][$gst_key]['before_tax_price'] += $old_before_tax_price;
					$data[$r['barcode']][$r['selling_price']]['gst_info']['old'][$gst_key]['tax_amount'] += $old_tax_amount;
					$data[$r['barcode']][$r['selling_price']]['gst_info']['old'][$gst_key]['tax_code'] = $old_tax_code;
					
					if($r['sku_item_id']){
						$gst_key = $r['tax_indicator']."-".$r['tax_rate'];
						$data[$r['barcode']][$r['selling_price']]['gst_info']['new'][$gst_key]['tax_indicator'] = $r['tax_indicator'];
						$data[$r['barcode']][$r['selling_price']]['gst_info']['new'][$gst_key]['tax_rate'] = $r['tax_rate'];
						$data[$r['barcode']][$r['selling_price']]['gst_info']['new'][$gst_key]['before_tax_price'] += $r['before_tax_price'];
						$data[$r['barcode']][$r['selling_price']]['gst_info']['new'][$gst_key]['tax_amount'] += $r['tax_amount'];
						$data[$r['barcode']][$r['selling_price']]['gst_info']['new'][$gst_key]['tax_code'] = $r['tax_code'];
					}
				}
				
				// check duplicate
				if ($tmp_check_duplicate[$r['barcode']][$r['selling_price']]){
					if ($data[$r['barcode']][$r['selling_price']]['info']['verify_user'] != $r['verify_user']) {
						$data[$r['barcode']][$r['selling_price']]['info']['has_partially_verified'] = 1;
					}
					$tmp_check_duplicate[$r['barcode']][$r['selling_price']][$r['counter_id']][$r['pos_id']][$r['pos_items_id']]=1;
					continue;
				}
			
				if ($r['verify_code_by'] > 0 || $r['sku_item_id'] > 0){
					$latest_selling_price = get_sku_item_cost_selling($this->branch_id, $r['sku_item_id'], $this->dd, array("selling"));
					if ($latest_selling_price)	$r['org_selling_price']=$latest_selling_price['selling'];
//					$verified_barcode[$r['pos_id']][$r['pos_items_id']][$r['sku_item_id']]=$r['sku_item_id'];
				}else{
					//check the data had been search or not 
					if (!$invalid_barcodes[$r['barcode']]){
						$result=get_sku_items_details_by_barcode($r['barcode']);
						$latest_selling_price = get_sku_item_cost_selling($this->branch_id, $result[0]['sku_item_id'], $this->dd, array("selling"));
						if ($latest_selling_price){
							$result[0]['org_selling_price']=$latest_selling_price['selling'];
						}	

						$invalid_barcodes[$r['barcode']]['info']=$result;
					}else{
						$result=$invalid_barcodes[$r['barcode']]['info'];
					}
					
					// only automatch if found one only
					if (count($result)==1){	
						$r['sku_item_id']=$result[0]['sku_item_id'];
						$r['sku_item_code']=$result[0]['sku_item_code'];
						$r['mcode']=$result[0]['mcode'];
						$r['link_code']=$result[0]['link_code'];
						$r['receipt_description']=$result[0]['receipt_description'];
						$r['org_selling_price']=$result[0]['org_selling_price'];
					}
				}

				if(!$data[$r['barcode']][$r['selling_price']])
					$data[$r['barcode']][$r['selling_price']]['total']+=1;

				//print_r($r);
				if(!$r['sku_item_id']){
					$r['verify_user'] = '';
					$r['verify_timestamp'] = '';
				}
				
				
				$data[$r['barcode']][$r['selling_price']]['info']=$r;
				
				$tmp_check_duplicate[$r['barcode']][$r['selling_price']][$r['counter_id']][$r['pos_id']][$r['pos_items_id']]=1;
			}
			$con->sql_freeresult($rid);
			unset($rid,$r);		
			
			if($tmp_check_duplicate){
				foreach ($tmp_check_duplicate as $barc => $sother){
					foreach ($sother as $selling => $other){
						$data[$barc][$selling]['id']=serialize($other);
						
						$transactions_info=$tmp_no_of_transaction[$barc][$selling];
						$data[$barc][$selling]['transactions_total']=count($transactions_info);
						
						foreach ($transactions_info as $cnpr => $dummy){
							$arr=explode("-",$cnpr);					
							
							$transactions_info_arr['counter_id']=$arr[0];
							$transactions_info_arr['network_name']=$arr[1];
							$transactions_info_arr['pos_id']=$arr[2];
							$transactions_info_arr['receipt_no']=$arr[3];
							
							$data[$barc][$selling]['transactions_info'][]=$transactions_info_arr;
						}
					}
				}
			}	

			//if($sessioninfo['id'] == 1){
				//print_r($data);
			//}
			
/*
			//match barcode
			foreach($invalid_barcodes as $barcode =>$dummy){
				$result=get_sku_items_details_by_barcode($barcode);
				
				//get latest selling price
				if ($result){
					foreach($result as &$skudetails){
						$latest_selling_price = get_sku_item_cost_selling($this->branch_id, $skudetails['sku_item_id'], $this->dd, array("selling"));
						$skudetails['selling_price'] = $latest_selling_price['selling'];
					}
				}
				
				foreach ($data as $bc => &$posther){
					foreach ($posther as $selling_price => &$other){

						//$other['info']['multi_items']=count($result);

						if ($other['info']['sku_item_id'])	continue;
						
						//auto match first selection
						$other['info']['sku_item_id']=$result[0]['sku_item_id'];
						$other['info']['sku_item_code']=$result[0]['sku_item_code'];
						$other['info']['mcode']=$result[0]['mcode'];
						$other['info']['link_code']=$result[0]['link_code'];
						$other['info']['receipt_description']=$result[0]['receipt_description'];						
					}
				}
			}
			print '<pre>';
			print_r($data);
			print '</pre>';
			*/
			
			//if($sessioninfo['u'] == 'admin'){
				//print_r($data);
			//}
			$smarty->assign("invalid_items",$data);
		}
		$con->sql_freeresult($rid);
		$smarty->assign("table",true);
		$smarty->assign("view_only",false);
		
		//check if the pos had been finalize
		if (!$sessioninfo['privilege']['POS_VERIFY_SKU']){
			$smarty->assign("view_only",true);
		}else{
			$sql_finalize="select * from pos_finalized where branch_id=".mi($this->branch_id)." and date=".ms($this->date)." and finalized=1";
			$rid=$con->sql_query($sql_finalize);
			
			if ($con->sql_numrows($rid)>0){	
				$smarty->assign("view_only",true);
			}
		}

		$this->display();
	}
	
	function get_latest_selling_price($sku_item_id){
		global $con;
		$rid2=$con->sql_query("select price from sku_items_price_history
						where sku_item_id=".mi($sku_item_id)." and branch_id=".mi($this->branch_id)." and added<".ms($this->dd)." order by added desc limit 1");
		if ($con->sql_numrows($rid2)>0){
			while($r2=$con->sql_fetchassoc($rid2)){
				$selling_price=$r2['price'];
			}
		}
		$con->sql_freeresult($rid2);

		return $selling_price;
	}

	function ajax_get_sku_items(){
		global $con, $config, $sessioninfo;
		
		$form=$_REQUEST;
		
		$rid=$con->sql_query("select id, sku_item_code, artno, mcode, link_code, receipt_description, cost_price, selling_price from sku_items 
							where id=".mi($form['sku_item_id']));
							
		while($r=$con->sql_fetchassoc($rid)){
			$sku_items=$r;
		}
		$con->sql_freeresult($rid);
		
		$latest_selling_price = get_sku_item_cost_selling($this->branch_id, $form['sku_item_id'], $this->dd, array("cost","selling"));
		if ($latest_selling_price){
			if ($latest_selling_price['cost'])	$sku_items['cost_price']=$latest_selling_price['cost'];
			if ($latest_selling_price['selling'])	$sku_items['selling_price']=$latest_selling_price['selling'];		
		}
		
		if($config['enable_gst'] || $config['enable_tax']){
			$params = array();
			$params['date'] = $form['selected_date'];
			$params['branch_id'] = $sessioninfo['branch_id'];
		
			if($config['enable_gst']){
				$is_under_gst = check_gst_status($params);
			}elseif($config['enable_tax']){
				$is_under_gst = check_tax_status($params);
			}
			
			$sku_items['is_under_gst'] = mb($is_under_gst);
			
			if($sku_items['is_under_gst'] && $config['enable_gst']){
				// got gst
				$sku_original_output_gst = get_sku_gst("output_tax", $form['sku_item_id']);
				
				if($sku_original_output_gst){
					$sku_items['gst_info']['id'] = $sku_original_output_gst['id'];
					$sku_items['gst_info']['code'] = $sku_original_output_gst['code'];
					$sku_items['gst_info']['rate'] = $sku_original_output_gst['rate'];
					$sku_items['gst_info']['indicator_receipt'] = $sku_original_output_gst['indicator_receipt'];
				}
			}
		}
		
		print json_encode($sku_items);
	}
	
	function ajax_get_match_items(){
		global $con,$smarty,$config;

		$form=$_REQUEST;
 	
		$result=get_sku_items_details_by_barcode($form['barcode']);
		
		if ($result){
			foreach($result as $sku_items){
				
				$sku_item_id=$sku_items['id'];

				$latest_selling_price = get_sku_item_cost_selling($this->branch_id, $result[0]['sku_item_id'], $this->dd, array("cost","selling"));
				if ($latest_selling_price){
					if ($latest_selling_price['cost'])	$sku_items['cost_price']=$latest_selling_price['cost'];
					if ($latest_selling_price['selling'])	$sku_items['selling_price']=$latest_selling_price['selling'];		
				}

				$match_items[]=$sku_items;
			}
			$smarty->assign("match_items",$match_items);

			$output=$smarty->fetch("pos.invalid_sku.extra.tpl");
			print $output;
		}else{
			print "no";
		}
		$con->sql_freeresult($rid);
	}
	
	function update_pos(){
		global $con,$smarty,$LANG,$sessioninfo, $config;
		$form=$_REQUEST;

		//print_r($form);exit;
		$err = $this->verify_code($form['code']);
				
		if ($err)	$smarty->assign("err",$err);
		else{
			foreach ($form['code'] as  $row_id => $price_list){
				foreach ($price_list as $selling_price=>$sku_item_id){
					$pos_data=unserialize($form['pos_items_id'][$row_id][$selling_price]);
					
					
					$verify_timestamp = date("Y-m-d H:i:s");
					foreach ($pos_data as $counter_id => $ppother){
						foreach ($ppother as $pos_id => $pother){
							foreach ($pother as $pos_items_id => $other){
								$upd = array();
								$upd['sku_item_id']=$sku_item_id;
								$upd['verify_code_by']=mi($sessioninfo['id']);
								$upd['verify_timestamp'] = $verify_timestamp;
								
								// string for filter
								$pos_filter = "branch_id=".mi($this->branch_id)." and date=".ms($this->date)." and counter_id=".mi($counter_id)." and id=".mi($pos_id);
								$pi_filter = "branch_id=".mi($this->branch_id)." and date=".ms($this->date)." and counter_id=".mi($counter_id)." and pos_id=".mi($pos_id)." and id=".mi($pos_items_id);

								// select the pos
								$con->sql_query("select * from pos where $pos_filter");
								$pos = $con->sql_fetchassoc();
								$con->sql_freeresult();
									
								// got new gst changes
								$total_gst_amt_adj = 0;
								if((($config['enable_gst'] && $pos['is_gst']) || ($config['enable_tax'] && $pos['is_tax_registered']))&& $form['new_sku_gst_info'][$row_id]){
									// get the original pos items
									$con->sql_query("select * from pos_items where $pi_filter");
									$pi = $con->sql_fetchassoc();
									$pi['more_info'] = unserialize($pi['more_info']);
									$con->sql_freeresult();
								
									// get the new gst id
									$gst_id = mi($form['new_sku_gst_info'][$row_id]['gst_id']);
									// get gst data
									if($config['enable_gst']){
										$gst = get_gst_settings($gst_id);
									}elseif($config['enable_tax']){
										$gst = get_tax_settings($gst_id);
									}
									
									if($gst){
										// compare for changes
										if($gst['code'] != $pi['tax_code'] || $gst['indicator_receipt'] != $pi['tax_indicator'] || $gst['rate'] != $pi['tax_rate']){
											$upd['more_info'] = $pi['more_info'];
											$upd['more_info']['ori_gst_info']['tax_code'] = $pi['tax_code'];
											$upd['more_info']['ori_gst_info']['tax_indicator'] = $pi['tax_indicator'];
											$upd['more_info']['ori_gst_info']['tax_amount'] = $pi['tax_amount'];
											$upd['more_info']['ori_gst_info']['tax_rate'] = $pi['tax_rate'];
											$upd['more_info']['ori_gst_info']['before_tax_price'] = $pi['before_tax_price'];
											
											// got changes
											$upd['tax_code'] = $gst['code'];
											$upd['tax_indicator'] = $gst['indicator_receipt'];
											
											// got change rate, need to recalculate
											if($gst['rate'] != $pi['tax_rate']){
												$total_price_inc_gst = $pi['before_tax_price'] + $pi['tax_amount'];
												
												$new_tax_amt = round($total_price_inc_gst/(100+$gst['rate'])*$gst['rate'], 2);
												$new_before_tax_price = round($total_price_inc_gst - $new_tax_amt, 2);
												
												$upd['before_tax_price'] = $new_before_tax_price;
												$upd['tax_amount'] = $new_tax_amt;
												$upd['tax_rate'] = $gst['rate'];
												$total_gst_amt_adj = round($new_tax_amt - $pi['tax_amount'], 2);
											}
											
											$upd['more_info'] = serialize($upd['more_info']);
										}
									}
								}
								
								//print_r($upd);
								$sql = "update pos_items set ".mysql_update_by_field($upd)." where $pi_filter";
								$con->sql_query($sql);
								//print $sql."<br>";
								
								if((($config['enable_gst'] && $pos['is_gst']) || ($config['enable_tax'] && $pos['is_tax_registered'])) && $total_gst_amt_adj){
									$upd2 = array();
									$upd2['total_gst_amt'] = round($pos['total_gst_amt'] + $total_gst_amt_adj, 2);
									//print_r($upd2);
									$con->sql_query("update pos set ".mysql_update_by_field($upd2)." where $pos_filter");
								}
							}
						}
					}
				}
			}
			
			$suc[]=$LANG['VERIFY_UPDATE_SUC'];
			$smarty->assign("suc",$suc);
			
			unset($_REQUEST['code']);
		}

		//avoid refresh page, update POS again		
		header("Location: $_SERVER[PHP_SELF]?branch_id=".mi($this->branch_id)."&date_select=".$this->date."&a=refresh_data&success=1");
		//$this->refresh_data();
	}
	
	function ajax_check_code(){
		$form=$_REQUEST;	
		$err = $this->verify_code($form['code']);
		
		if ($err)	print $err[0];
		else	 	print "ok";
	}
	
	function verify_code($codes){
		global $LANG;

		if ($codes){
			foreach ($codes as  $row_id => $price_list){
				foreach($price_list as $price=>$sku_item_id){
					if (!$sku_item_id)	$err[]=$LANG['VERIFY_NO_ID'];
					break;
				}
				if ($err)	break;
			}
		}else{
			$err[]=$LANG['VERIFY_NO_CODE'];
		}
		return $err;
	}
	
	function ajax_generate_serial_num(){
		global $con, $smarty, $sessioninfo;
		
		$form = $_REQUEST;
		$form['pos_items_id_info'] = unserialize($form['pos_items_id_info']);
		
		$bid = $this->branch_id;
		$date = $this->date;
		$serial_num = $form['serial_num'];
		$sid = mi($form['sid']);
		
		$err = array();
		if($bid != $sessioninfo['branch_id'])	$err[] = "You cannot create other branch serial number.";
		if(!$date)	$err[] = "Invalid Date.";
		if(!$serial_num)	$err[] = "Invalid Serial Number.";
		if(!$sid)	$err[] = "Invalid SKU Item ID";
		
		if(!$err){
			$con->sql_query("select sku.have_sn
			from sku_items si
			left join sku on sku.id=si.sku_id
			where si.id=$sid");
			$sku = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if(!$sku)	$err[] = "SKU Not Found.";
			if($sku && !$sku['have_sn']){
				$err[] = "This SKU does not use Serial No. (Please change the setting at SKU Masterfile)";
			}
		}
		
		if($err){
			foreach($err as $e){
				print "$e\n";
			}
			exit;
		}
		
		//print_r($form);
		$upd = array();
		$upd['branch_id'] = $upd['located_branch_id'] = $bid;
		$upd['sku_item_id'] = $sid;
		$upd['serial_no'] = $serial_num;
		$upd['created_by'] = $sessioninfo['id'];
		$upd['last_update'] = $upd['added'] = 'CURRENT_TIMESTAMP';
		
		$ret = array();
		if($con->sql_query_false("insert into pos_items_sn ".mysql_insert_by_field($upd))){
			$new_id = $con->sql_nextid();
		}else{
			die("Create Serial Number#$serial_num for SKU Item ID#$sid failed");
		}
		
		if($new_id){
			log_br($sessioninfo['id'], 'Serial Number', "", "Generate Serial Number from Verify SKU Module. Branch ID#$bid, SKU Item ID#$sid, Serial#$serial_num");
		
			$ret['ok'] = 1;
		}
		
		print json_encode($ret);
	}
}

$VERIFY_CODE= new VERIFY_CODE("Verify Invalid SKU");

?>