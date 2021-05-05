<?php
/*
7/5/2011 1:28:30 PM Andy
- Change split() to use explode()

10/28/2011 2:24:58 PM Andy
- Make compatible with BETA 139 and LINUX 119.  
- Added import for table pos_mix_match_usage, pos_delete_items, sn_info and pos_goods_return.

03/19/2012 9:33:00 AM Kee Kee
- Make compatible  with BETA 146 and LINUX 121.

05/18/2012 2:23:00 AM Kee Kee
- Make compatible  with LINUX 123 and beta 150 ver.

07/02/2012 5:52:00 PM Kee Kee
- Make compatible with and beta 153 ver

08/14/2013 2:14 PM Kee Kee
- Import Sales, and compatible with beta 154 to beta 203

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

27/2/2014 5:10PM Kee Kee
- compatible with Beta 217 & 218 version

3/25/2014 10:17 AM Justin
- Modified the wording from "Finalize" to "Finalise".
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('POS_IMPORT') && $sessioninfo['level']<9999)
	js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_IMPORT', BRANCH_CODE), "/index.php");	
include_once('include/sqlite.php');

$maintenance->check(99);

class IMPORT_POS_SALES extends Module{
	var $branch_id = 0;
	
    function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->branch_id = mi($sessioninfo['branch_id']);

		parent::__construct($title);
	}

	function _default(){
	    global $sessioninfo, $smarty;

	
	    if($_REQUEST['analyze']){
			$this->analyze_file();
		}elseif($_REQUEST['import_pos']){
			$this->import_pos();
		}
		
		
		$this->display();
	}
	
	private function analyze_file(){
		global $con, $smarty, $sessioninfo;
		
		//if($sessioninfo['u']=='wsatp')	die('here');
		
		// check file
	    $err = check_upload_file('pos_file', 'sql3');
	    
	    if(!$err){
			$f = $_FILES['pos_file'];
			$filename = $f['name'];
			$err = $this->get_import_header($date, $bid, $counter_id, $filename, $ret);
		}
	    if($err){   // got error			
			$smarty->assign('err', $err);
			return false;
		}
		//if($sessioninfo['u']=='wsatp')	die('here2');
		// open sqlite connection
		$sq3 = new sqlite_db($f['tmp_name']);
		// process file, but dont import
		$params = array();
		$params['branch_id'] = $bid;
		$params['date'] = $date;
		$params['counter_id'] = $counter_id;
		$this->process_arms_pos_file($sq3, $params, $err);
		//if($sessioninfo['u']=='wsatp')	die('here');
		if($err){   // got error
			$smarty->assign('err', $err);
			return false;
		}
		
		// move upload file to attachment folder
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments",0777);
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales",0777);
		if (!is_dir($_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales/temp"))	mkdir($_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales/temp",0777);
		$history_file = time().".".$f['name'];
		move_uploaded_file($f['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales/temp/$history_file");
		
		$data = $params['data'];
		$data['sales_info']['branch_code'] = $ret['branch_code'];
		$data['sales_info']['date'] = $date;
		$data['sales_info']['counter_name'] = $ret['counter_name'];
		$data['file_info'] = $f;
		$data['file_info']['history_filename'] = $history_file;
		//print_r($data);
		$smarty->assign('data', $data);
	}
	
	private function import_pos(){
		global $con, $smarty, $sessioninfo;
		
		//print_r($_REQUEST);
		
		// invalid file submit
		$history_filename = $_REQUEST['history_filename'];
		if(!$history_filename){
			$err[] = "Invalid File";
		}
		
		if(!$err){
            $filename = $_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales/temp/".$history_filename;
			if(!file_exists($filename)){
	            $err[] = "File not exists, please submit again";
			}else{
                $real_filename = substr($history_filename, strpos($history_filename, '.')+1);
                $err = $this->get_import_header($date, $bid, $counter_id, $real_filename, $ret);
			}
		}
		
		if($err){
            $smarty->assign('err', $err);
			return false;
		}
		$sq3 = new sqlite_db($filename);
		
		// process file
		$params = array();
		$params['branch_id'] = $bid;
		$params['date'] = $date;
		$params['counter_id'] = $counter_id;
		$this->process_arms_pos_file($sq3, $params, $err, true);
		
		if($err){
            $smarty->assign('err', $err);
			return false;
		}
		
		log_br($sessioninfo['id'], 'IMPORT POS SALES', '', "Import POS sales using $history_filename");
		
		// copy file
        copy($filename, $_SERVER['DOCUMENT_ROOT']."/attachments/import_pos_sales/".$history_filename);
        // delete file
        @unlink($filename);
		
		$import_data = $params['data'];
		$import_data['filename'] = $real_filename;
		$import_data['sales_info']['branch_code'] = $ret['branch_code'];
		$import_data['sales_info']['date'] = $date;
		$import_data['sales_info']['counter_name'] = $ret['counter_name'];
		
		$smarty->assign('import_data', $import_data);
	}
	
	private function get_import_header(&$date, &$bid, &$counter_id, $filename, &$ret = array()){
	    global $con, $sessioninfo, $config;
	    
	    $err = array();
        list($date, $bcode, $counter_name) = explode(",", $filename);

		// check date
		$date = date("Y-m-d", strtotime($date));
		if(date("Y", strtotime($date))<2007)    $err[] = "Invalid Date.";

		// check branch
		if(!$config['single_server_mode'] && $bcode!=BRANCH_CODE) $err[] = "You can only import your own branch POS.";
		else{
            if($bcode==BRANCH_CODE) $bid = mi($sessioninfo['branch_id']);
            else{
				$con->sql_query("select id from branch where code=".ms($bcode));
				$bid = mi($con->sql_fetchfield(0));
				$con->sql_freeresult();
			}
			if(!$bid)   $err[] = "Invalid Branch Code '$bcode'.";
		}    
		
		// check counter name
		$counter_name = str_replace('.sql3', '', $counter_name);
		if(!$err){
			$con->sql_query("select * from counter_settings where branch_id=$bid and network_name=".ms($counter_name));
			$counter_info = $con->sql_fetchassoc();
			$con->sql_freeresult();

			$counter_id = mi($counter_info['id']);
			if(!$counter_id)  $err[] = "Invalid Counter Name. ($counter_name)";
		}
		
		$ret['branch_code'] = $bcode;
		$ret['counter_name'] = $counter_name;
		return $err;
	}
	
	private function process_arms_pos_file(&$sq3, &$params, &$err, $is_import = false){
	    global $con;

	    $bid = mi($params['branch_id']);
		$date = $params['date'];
		$counter_id = mi($params['counter_id']);
		$data = array();
		if($sessioninfo['u']=='wsatp')	die('process_arms_pos_file');
		
		if(!$sq3 || !$bid || !$date || !$counter_id){
			$err[] = "Invalid parameters.";
			return false;
		}
		
	    $sq3->sql_query("select * from sqlite_master");
        $table_count = $sq3->sql_numrows();

		// check whether is empty db or not
		if ($table_count<=0)    $err[] = "Uploaded file is empty DB.";
		
		if($err)    return false;
		
		$this->prepare_db($sq3);
		
		$require_table = array('pos','pos_items','pos_payment','pos_drawer','pos_cash_domination','receipt_cancel','cash_history','pos_mix_match_usage','pos_delete_items','sn_info','pos_goods_return','pos_user_log','membership_promotion_mix_n_match_items','membership_promotion_items','pos_deposit','pos_deposit_status','pos_deposit_status_history','pos_member_point_adjustment');
		$fullfilled_tbl = array();
		
		$sq3->sql_query("select * from sqlite_master");
		while($r = $sq3->sql_fetchrow()){			
			if(in_array($r['name'], $require_table)){
                $fullfilled_tbl[$r['name']] = $r['name'];
			}
		}

		$sq3->sql_freeresult();

		// got some table missing
		if(count($fullfilled_tbl) < count($require_table)){
			$diff_tbl = array_diff($require_table, $fullfilled_tbl);
			foreach($diff_tbl as $tbl){
				$err[] = "Table '$tbl' is missing from data file.";
			}
			return false;
		}

		// check finalized or not
		$con->sql_query("select finalized from pos_finalized where branch_id=$bid and date=".ms($date));
		if($con->sql_numrows()>0 && $con->sql_fetchfield(0)==1){
			$err[] = "POS for date '$date' already finalised, please do unfinalise first.";
		}
		$sq3->die_on_error = false;
		
		if($is_import){
		    $del_filter = "where branch_id=$bid and date=".ms($date)." and counter_id=$counter_id";
		    $con->sql_query("delete from pos $del_filter");
		    $con->sql_query("delete from pos_items $del_filter");
		    $con->sql_query("delete from pos_payment $del_filter");
		    $con->sql_query("delete from pos_mix_match_usage $del_filter");
		    $con->sql_query("delete from pos_delete_items $del_filter");
		    $con->sql_query("delete from pos_drawer $del_filter");
		    $con->sql_query("delete from pos_cash_domination $del_filter");
		    $con->sql_query("delete from pos_receipt_cancel $del_filter");
		    $con->sql_query("delete from pos_cash_history $del_filter");
		    $con->sql_query("delete from sn_info $del_filter");
		    $con->sql_query("delete from pos_goods_return $del_filter");
		    $con->sql_query("delete from pos_deposit $del_filter");
		    $con->sql_query("delete from pos_deposit_status $del_filter");
		    $con->sql_query("delete from pos_user_log $del_filter");
			//$con->sql_query("delete from pos_member_point_adjusment $del_filter");
		    $con->sql_query("delete from membership_promotion_items $del_filter");
		    $con->sql_query("delete from membership_promotion_mix_n_match_items $del_filter");
		}

		// POS
		$q_pos = $sq3->sql_query("select * from pos order by id");
		if($q_pos){
            while($r = $sq3->sql_fetchrow($q_pos)){
			
				//update sales order which transaction 
				if($r['sales_order_id'] && $r['sales_order_branch_id'] && !$r['update_sales_order']){
					
					$sql = "select exported_to_pos from sales_order where branch_id=".ms($r['sales_order_branch_id'])." and id=".ms($r['sales_order_id'])." and exported_to_pos=1";
					$use_con->sql_query($sql);
								
					if($use_con->sql_numrows()>0)
					{
						$sq3->sql_query("update pos set update_sales_order=1 where id=".mi($r['id']));
					}
					else
					{
						$sql = "update sales_order set exported_to_pos=1 where branch_id=".ms($r['sales_order_branch_id'])." and id=".ms($r['update_sales_order']);
						
						$con->sql_query($sql);
						
						if($use_con->sql_affectedrows()<=0 || !$use_con->sql_affectedrows())
						{					
							$err['p'] = "Failed to update sales order status for pos(".$r['id'].")";							
						}
						else
						{
							$sq3->sql_query("update pos set update_sales_order=1 where id=".mi($r['id']));
						}
					}
				}
                $pos_id = mi($r['id']);
                
				if($is_import){
	                $pos = array();
	                $pos['branch_id'] = $bid;
	                $pos['date'] = $date;
	                $pos['counter_id'] = $counter_id;
	                $pos['id'] = $pos_id;
	                $pos['cashier_id'] = mi($r['cashier_id']);
	                $pos['start_time'] = date("Y-m-d H:i:s", $r['start_time']);
	                $pos['end_time'] = date("Y-m-d H:i:s", $r['end_time']);
	                $pos['pos_time'] = date("Y-m-d H:i:s", $r['pos_time']);
	                $pos['amount'] = mf($r['amount']);
	                $pos['amount_tender'] = mf($r['amount_tender']);
	                $pos['amount_change'] = mf($r['amount_change']);
	                $pos['member_no'] = $r['member_no'];
	                $pos['race'] = $r['race'];
	                $pos['receipt_no'] = $r['receipt_no'];
	                $pos['point'] = $r['point'];
	                $pos['cancel_status'] = mi($r['cancel_status']);
	                $pos['redeem_points'] = $r['redeem_points'];
	                $pos['receipt_remark'] = $r['receipt_remark'];
					$pos['prune_status'] = $r['prune_status'];
					$pos['receipt_sa'] = $r['receipt_sa'];
					$pos['receipt_ref_no'] = $r['receipt_ref_no'];				
					$pos['deposit'] = mi($r['deposit']);
					$pos['mix_n_match_point'] = $r['mix_n_match_point'];
					$pos['refund_by'] = $r['refund_by'];
					$pos['quota_used'] = $r['quota_used'];
					$pos['quota_over_by'] = $r['quota_over_by'];
					$pos['staff_approved_by'] = $r['staff_approved_by'];
					$pos['pos_more_info'] = $r['pos_more_info'];
					$pos['sales_order_id'] = $r['sales_order_id'];
					$pos['sales_order_branch_id'] = $r['sales_order_branch_id'];
	                $con->sql_query("replace into pos ".mysql_insert_by_field($pos));
				}
				// POS items
				$q_pi = $sq3->sql_query("select id,pos_id,item_id,sku_item_id,barcode,qty,price,discount,return_by,item_discount_by,mprice_type,trade_discount_code,redeem_points,remark,cb_code,cb_profit,cb_discount,cb_use_net,cb_net_bearing,open_price_by,item_sa,trade_in_by,is_return_policy,expired_return_policy,more_info,got_return_policy,sku_description,open_code_by,member_point,quota_used from pos_items where pos_id=$pos_id");
				if(!$q_pi){
                    $err['pi'] = "POS Items Error: ".$sq3->last_error;
				}else{
                    while($pi = $sq3->sql_fetchrow($q_pi)){
	                    if($is_import){
	                        $pos_items = array();
	                        $pos_items['branch_id'] = $bid;
			                $pos_items['date'] = $date;
			                $pos_items['counter_id'] = $counter_id;
			                $pos_items['pos_id'] = $pos_id;
			                $pos_items['id'] = mi($pi['id']);
			                $pos_items['item_id'] = mi($pi['item_id']);
			                $pos_items['sku_item_id'] = mi($pi['sku_item_id']);
			                $pos_items['barcode'] = $pi['barcode'];
	                        $pos_items['qty'] = mf($pi['qty']);
	                        $pos_items['price'] = mf($pi['price']);
	                        $pos_items['discount'] = $pi['discount'];
	                        $pos_items['return_by'] = $pi['return_by'];
	                        $pos_items['item_discount_by'] = $pi['item_discount_by'];
	                        $pos_items['mprice_type'] = $pi['mprice_type'];
	                        $pos_items['trade_discount_code'] = $pi['trade_discount_code'];
	                        $pos_items['redeem_points'] = $pi['redeem_points'];
							$pos_items['remark'] = $pi['remark'];
	                        $pos_items['cb_code'] = $pi['cb_code'];
	                        $pos_items['cb_profit'] = $pi['cb_profit'];
	                        $pos_items['cb_discount'] = $pi['cb_discount'];
	                        $pos_items['cb_use_net'] = $pi['cb_use_net'] ? 'yes' : 'no';
	                        $pos_items['cb_net_bearing'] = $pi['cb_net_bearing'];
							$pos_items['open_price_by'] = mi($pi['open_price_by']);
							$pos_items['item_sa'] = $pi['item_sa'];
							$pos_items['trade_in_by'] = $pi['trade_in_by'];
							$pos_items['is_return_policy'] = $pi['is_return_policy'];
							$pos_items['expired_return_policy'] = $pi['expired_return_policy'];
							$pos_items['more_info'] = $pi['more_info'];
							$pos_items['got_return_policy'] = $pi['got_return_policy'];
							$pos_items['sku_description'] = $pi['sku_description'];
							$pos_items['open_code_by'] = $pi['open_code_by'];
							$pos_items['member_point'] = $pi['member_point'];
							$pos_items['quota_used'] = $pi['quota_used'];

	                        $con->sql_query("replace into pos_items ".mysql_insert_by_field($pos_items));
	                    }
	                    $data['pos_items']['count']++;
					}
					$sq3->sql_freeresult($q_pi);
				}

				// pos payment
				$q_pp = $sq3->sql_query("select id,pos_id,type,remark,amount,approved_by,more_info,is_abnormal from pos_payment where pos_id=$pos_id");
				if(!$q_pp){
                    $err['pp'] = "POS Payment Error: ".$sq3->last_error;
				}else{
					if($is_import){
						while($pp = $sq3->sql_fetchrow($q_pp)){
							$pos_payment = array();
							$pos_payment['branch_id'] = $bid;
			                $pos_payment['date'] = $date;
			                $pos_payment['counter_id'] = $counter_id;
			                $pos_payment['pos_id'] = $pos_id;
			                $pos_payment['id'] = mi($pp['id']);
			                $pos_payment['type'] = $pp['type'];
			                $pos_payment['remark'] = $pp['remark'];
			                $pos_payment['amount'] = mf($pp['amount']);
			                $pos_payment['approved_by'] = mi($pp['approved_by']);
			                $pos_payment['more_info'] = $pp['more_info'];
			                $pos_payment['is_abnormal'] = $pp['is_abnormal'];
			                
			                $con->sql_query("replace into pos_payment ".mysql_insert_by_field($pos_payment));
						}
					}
					$sq3->sql_freeresult($q_pp);
				}
				
				// pos_mix_match_usage
				$q_mm = $sq3->sql_query("select pos_id,id,remark,amount,group_id,promo_id,more_info from pos_mix_match_usage where pos_id = ".$pos_id);
				//print "select pos_id,id,remark,amount,group_id,promo_id,more_info from pos_mix_match_usage where pos_id = ".$pos_id."<br/>";
				if(!$q_mm){					
                    $err['pmu'] = "POS Mix Match Usage Error: ".$sq3->last_error;
				}else{
					if($is_import){
						while ($p_mm= $sq3->sql_fetchrow($q_mm)){
							$pos_mm = array();
							$pos_mm['branch_id'] = $bid;
							$pos_mm['counter_id'] = $counter_id;
							$pos_mm['date'] = $date;
							$pos_mm['pos_id'] = $pos_id;
							$pos_mm['id'] = $p_mm['id'];
							$pos_mm['remark'] = $p_mm['remark'];
							$pos_mm['amount'] = mf($p_mm['amount']);
							$pos_mm['group_id'] = $p_mm['group_id'];
							$pos_mm['promo_id'] = $p_mm['promo_id'];
							$pos_mm['more_info'] = $p_mm['more_info'];
							
							$con->sql_query("replace into pos_mix_match_usage ".mysql_insert_by_field($pos_mm));			
						}
					}
				}
				$sq3->sql_freeresult($q_mm);
				
				//Sync pos_member_point_adjustment
				$q_pma = $sq3->sql_query("select * from pos_member_point_adjustment where pos_id = ".$pos_id);
				if(!$q_pma){
					$err['pms'] = "POS Mix Match Usage Error: ".$sq3->last_error;
				}
				else{
					while($pma = $sq3->sql_fetchrow())
					{
						$pos_pma = array();
						$pos_pma['pos_id'] = $pos_id;
						$pos_pma['counter_id'] = $counter_id;
						$pos_pma['branch_id'] = $bid;
						$pos_pma['date'] = $date;	
						$pos_pma['nric'] = $pma['nric'];
						$pos_pma['card_no'] = $pma['card_no'];
						$pos_pma['adjust_date'] = $pma['adjust_date'];
						$pos_pma['points'] = $pma['points'];
						$pos_pma['reason'] = $pma['reason'];
						$pos_pma['remark'] = $pma['remark'];
						$pos_pma['type'] = $pma['type'];
						$pos_pma['is_delivery'] = $pma['is_delivery'];
						$pos_pma['delivery_name'] = $pma['delivery_name'];
						$pos_pma['delivery_address'] = $pma['delivery_address'];
						$pos_pma['delivery_phone'] = $pma['delivery_phone'];
						$pos_pma['ref_receipt_ref_no'] = $pma['ref_receipt_ref_no'];
						$pos_pma['user_id'] = $pma['user_id'];		
						$con->sql_query("replace into pos_member_point_adjustment ".mysql_insert_by_field($pos_pma));
						$mb = $con->sql_query("select * from membership where card_no=".ms($pma['card_no']));
						if($con->sql_numrows($mb)>0){
							$mr = $con->sql_fetchrow($mb);
							if($mr['parent_nric']=="")
							{
								$mp["nric"] = $pma['nric'];
								$mp["card_no"] = $pma['card_no'];
							}
							else{
								$mb1 = $con->sql_query("select * from membership where nric=".ms($mr['parent_nric']));
								$principle_member = $con->sql_fetchrow($mb1);
								$mp["nric"] = $principle_member['nric'];
								$mp["card_no"] = $principle_member['card_no'];
							}
						}else{
							$mp["nric"] = $pma['nric'];
							$mp["card_no"] = $pma['card_no'];
						}
						$con->sql_freereuslt($mb);
						$mp["branch_id"] = $bid;
						$mp["date"] = $pma['adjust_date'];
						$mp["points"] = $pma["points"];
						$mp["remark"] = $pma['remark'];
						$mp["type"] = $pma['type'];
						$mp["user_id"] = $pma['user_id'];
						$mp["point_source"] = "counter";
						$con->sql_query("replace into membership_points ".mysql_insert_by_field($mp));
					}
				}
				$sq3->sql_freeresult($q_pma);
				
				// pos_delete_items
		        $q_d = $sq3->sql_query("select pos_id,id,sku_item_id,barcode,qty,price,delete_by from pos_delete_items where pos_id = ".$pos_id);
		        if(!$q_d){
                    $err['pp'] = "POS Delete Item Error: ".$sq3->last_error;
				}else{
					if($is_import){
						while ($p_di= $sq3->sql_fetchrow($q_d)){
							$pos_di = array();
							$pos_di['branch_id'] = $bid;
							$pos_di['counter_id'] = $counter_id;
							$pos_di['date'] = $date;
							$pos_di['pos_id'] = $pos_id;
							$pos_di['id'] = $p_di['id'];
							$pos_di['sku_item_id'] = $p_di['sku_item_id'];
							$pos_di['barcode'] = $p_di['barcode'];
							$pos_di['qty'] = $p_di['qty'];
							$pos_di['price'] = $p_di['price'];
							$pos_di['delete_by'] = $p_di['delete_by'];
							
							$con->sql_query("replace into pos_delete_items ".mysql_insert_by_field($pos_di));
						}
					}
				}
				$sq3->sql_freeresult($q_d);
				
				//Update pos deposit record				
				$q_pde = $sq3->sql_query("select * from pos_deposit where pos_id = ".$r['id']);
				if(!$q_pde){
					$err['pde'] = "POS Deposit Error: ".$sq3->last_error;
				}else{
					if($is_import){
						while ($pde = $sq3->sql_fetchrow($q_d)){
							$pos_pde = array();
							$pos_pde['pos_id'] = $pos_id;
							$pos_pde['counter_id'] = $counter_id;
							$pos_pde['branch_id'] = $bid;
							$pos_pde['date'] = $date;
							$pos_pde['pos_time'] = $pde['pos_time'];
							$pos_pde['receipt_no'] = $pde['receipt_no'];							
							$pos_pde['item_list'] = $pde['item_list'];
							$pos_pde['deposit_amount'] = $pde['deposit_amount'];
							$pos_pde['cashier_id'] = $pde['cashier_id'];
							$pos_pde['approved_by'] = $pde['approved_by'];
							
							$con->sql_query("replace into pos_deposit ".mysql_insert_by_field($pos_pde));
						}
					}
				}
				
				// record total pos count
				$data['pos']['count']++;
			}
			$sq3->sql_freeresult($q_pos);
		}else   $err['p'] = "POS Error: ".$sq3->last_error;  // got error
		
		// pos drawer
		$q_pd = $sq3->sql_query("select id,user_id,timestamp,ref_no,type from pos_drawer");
		if(!$q_pd){
            $err['pd'] = "POS Drawer Error: ".$sq3->last_error;  // got error
		}else{
		    if($is_import){
	            while($pd = $sq3->sql_fetchrow($q_pd)){
					$pos_drawer = array();
					$pos_drawer['branch_id'] = $bid;
	                $pos_drawer['date'] = $date;
	                $pos_drawer['counter_id'] = $counter_id;
	                $pos_drawer['id'] = mi($pd['id']);
	                $pos_drawer['user_id'] = mi($pd['user_id']);
	                $pos_drawer['timestamp'] = $pd['timestamp'];
	                $pos_drawer['ref_no'] = $pd['ref_no'];
	                $pos_drawer['type'] = $pd['type'];
	                $con->sql_query("replace into pos_drawer ".mysql_insert_by_field($pos_drawer));							
				}
			}
		}
		$sq3->sql_freeresult($q_pd);
		
		// POS Cash Domination
		$q_pcd = $sq3->sql_query("select id,user_id,data,clear_drawer,timestamp,curr_rate from pos_cash_domination");
		if(!$q_pcd){
            $err['pcd'] = "Cash Denomination Error: ".$sq3->last_error;  // got error
		}else{
		    if($is_import){
	            while($pcd = $sq3->sql_fetchrow($q_pcd)){
	                $pos_cd = array();
	                $pos_cd['branch_id'] = $bid;
	                $pos_cd['date'] = $date;
	                $pos_cd['counter_id'] = $counter_id;
	                $pos_cd['id'] = mi($pcd['id']);
	                $pos_cd['user_id'] = mi($pcd['user_id']);
	                $pos_cd['data'] = $pcd['data'];
	                $pos_cd['clear_drawer'] = $pcd['clear_drawer'];
	                $pos_cd['timestamp'] = $pcd['timestamp'];
	                $pos_cd['curr_rate'] = $pcd['curr_rate'];
					$pos_cd['ref_no'] = $pcd['ref_no'];
	                
	                $con->sql_query("replace into pos_cash_domination ".mysql_insert_by_field($pos_cd));
	            }
			}
		}
		$sq3->sql_freeresult($q_pcd);
		
		// Serial No
		$q_sn = $sq3->sql_query("select branch_id,counter_id,date,item_id,pos_id,sku_item_id,serial_no,nric,name,address,contact_no,email,warranty_expired,approved_by,active from sn_info");
		if(!$q_sn){
            $err['sn'] = "SN Info Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($psn = $sq3->sql_fetchrow($q_sn)){
					$sn = array();
					$sn['branch_id'] = $psn['branch_id'];
					$sn['date'] = $psn['date'];
					$sn['counter_id'] = $psn['counter_id'];
					$sn['item_id'] = $psn['item_id'];
					$sn['pos_id'] = $psn['pos_id'];
					$sn['sku_item_id'] = $psn['sku_item_id'];
					$sn['serial_no'] = $psn['serial_no'];
					$sn['nric'] = $psn['nric'];
					$sn['name'] = $psn['name'];
					$sn['address'] = $psn['address'];
					$sn['contact_no'] = $psn['contact_no'];
					$sn['email'] = $psn['email'];
					$sn['warranty_expired'] = $psn['warranty_expired'];
					$sn['approved_by'] = $psn['approved_by'];
					$sn['active'] = $psn['active'];
					
					$con->sql_query("replace into sn_info ".mysql_insert_by_field($sn));
					
					if($con->sql_affectedrows()>0){	
						$con->sql_query("update pos_items_sn set status = ".mi($psn['active']).", last_update = CURRENT_TIMESTAMP where located_branch_id = ".mi($psn['branch_id'])." and sku_item_id = ".mi($psn['sku_item_id'])." and serial_no = ".ms($psn['serial_no']));
					}
				}
			}
		}
		$sq3->sql_freeresult($q_sn);
		
		// Goods Return
		$q_gr = $sq3->sql_query("select counter_id,date,pos_id,item_id,return_counter_id,return_date,return_pos_id,return_item_id,return_receipt_no from pos_goods_return");
		if(!$q_sn){
            $err['gr'] = "POS Goods Return Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($pgr = $sq3->sql_fetchrow($q_gr)){
					$pos_gr = array();
					$pos_gr['branch_id'] = $bid;
					$pos_gr['counter_id'] = $counter_id;
					$pos_gr['date'] = $date;
					$pos_gr['pos_id'] = $pgr['pos_id'];
					$pos_gr['item_id'] = $pgr['item_id'];
					$pos_gr['return_counter_id'] = $pgr['return_counter_id'];
					$pos_gr['return_date'] = $pgr['return_date'];
					$pos_gr['return_pos_id'] = $pgr['return_pos_id'];
					$pos_gr['return_item_id'] = $pgr['return_item_id'];
					$pos_gr['return_receipt_no'] = $pgr['return_receipt_no'];
					
					// need to get the correct pos id for return
					$q_rp = $con->sql_query("select id from pos where branch_id=".$bid." and counter_id=".mi($pos_gr['return_counter_id'])." and date=".ms($pos_gr['return_date'])." and receipt_no=".mi($pos_gr['return_receipt_no']));
					$real_pos = $con->sql_fetchassoc($q_rp);
					$con->sql_freeresult();
					
					if(!$real_pos){	// cannot found the pos
						$warning[] = "Warning: pos_goods_return receipt no#$pos_gr[return_receipt_no] cannot found at server.\n";
						continue;
					}
					
					
					$con->sql_query("replace into pos_goods_return ".mysql_insert_by_field($pos_gr));
					/*if ($con->sql_affectedrows()>0){						
						// update S/N become inactive and available for next payment
						$q_sn = $con->sql_query("select * from sn_info where pos_id = ".mi($pos_gr['return_pos_id'])." and item_id = ".mi($pos_gr['return_item_id'])." and branch_id = ".mi($bid)." and counter_id = ".mi($pos_gr['return_counter_id'])." and date = ".ms($pos_gr['return_date']));
			
						// here is where we do update on serial no. from frontend database and backend server
						if ($con->sql_numrows($q_sn) > 0){
							$sn_item = $con->sql_fetchrow($q_sn);
			
							$con->sql_query("update pos_items_sn set status = 'Available' where located_branch_id = ".mi($bid)." and sku_item_id = ".mi($sn_item['sku_item_id'])." and serial_no = ".ms($sn_item['serial_no']));
							
							// update the S/N information become available from backend
							$con->sql_query("update sn_info set active=0 where pos_id = ".mi($pos_gr['return_pos_id'])." and item_id = ".mi($pos_gr['return_item_id'])." and branch_id = ".mi($bid)." and counter_id = ".mi($pos_gr['return_counter_id'])." and date = ".ms($pos_gr['return_date']));
						}
						$con->sql_freeresult($q_sn);
					}*/
				}
				
			}
		}
		$sq3->sql_freeresult($q_gr);
		
		// receipt cancel
		$q_rc = $sq3->sql_query("select id,receipt_no,cancelled_by,cancelled_time,verified_by from receipt_cancel");
		if(!$q_rc){
            $err['rc'] = "Receipt Cancel Error: ".$sq3->last_error;  // got error
		}else{
		    if($is_import){
	            while($rc = $sq3->sql_fetchrow($q_rc)){
	                $receipt_cancel = array();
	                $receipt_cancel['branch_id'] = $bid;
	                $receipt_cancel['date'] = $date;
	                $receipt_cancel['counter_id'] = $counter_id;
	                $receipt_cancel['id'] = mi($rc['id']);
	                $receipt_cancel['receipt_no'] = $rc['receipt_no'];
	                $receipt_cancel['cancelled_by'] = $rc['cancelled_by'];
	                $receipt_cancel['cancelled_time'] = date("Y-m-d H:i:s", $rc['cancelled_time']);
	                $receipt_cancel['verified_by'] = $rc['verified_by'];
	                
	                $con->sql_query("replace into pos_receipt_cancel ".mysql_insert_by_field($receipt_cancel));
	            }
			}
		}
		$sq3->sql_freeresult($q_rc);
		
		// cash_history
		$q_ch = $sq3->sql_query("select rowid,cashier_id,collect_by,type,amount,time,remark from cash_history");
		if(!$q_ch){
            $err['ch'] = "Cash History Error: ".$sq3->last_error;  // got error
		}else{
		    if($is_import){
	            while($ch = $sq3->sql_fetchrow($q_ch)){
	                $pos_ch = array();
	                $pos_ch['branch_id'] = $bid;
	                $pos_ch['date'] = $date;
	                $pos_ch['counter_id'] = $counter_id;
	                $pos_ch['id'] = mi($ch['rowid']);
	                $pos_ch['user_id'] = mi($ch['cashier_id']);
	                $pos_ch['collected_by'] = $ch['collect_by'];
	                $pos_ch['type'] = $ch['type'];
	                $pos_ch['oamount'] = $pos_ch['amount'] = $ch['amount'];
	                $pos_ch['timestamp'] = date("Y-m-d H:i:s", $ch['time']);
	                $pos_ch['remark'] = $ch['remark'];
	                $pos_ch['ref_no'] = $ch['ref_no'];
	                
	                $con->sql_query("replace into pos_cash_history ".mysql_insert_by_field($pos_ch));
	            }
			}
		}
		$sq3->sql_freeresult($q_ch);
		
		//Update POS Deposit Status
		$q_pds = $sq3->sql_query("select * from pos_deposit_status");
		if(!$q_pds){
			$err['pds'] = "Deposit Status Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($ds = $sq3->sql_fetchrow($q_pds))
				{
					$pds = array();					
					$pds['pos_id'] = $this->get_real_pos_id($bid,$counter_id,$ds['receipt_no'],$ds['date']);
					$pds['date'] = $ds['date'];
					$pds['branch_id'] = $bid;
					$pds['counter_id'] = $counter_id;
					$pds['receipt_no'] = $ds['receipt_no'];
					$pds['deposit_pos_id'] = $this->get_real_pos_id($ds['deposit_branch_id'],$ds['deposit_counter_id'],$ds['deposit_receipt_no'],$ds['deposit_date']);
					$pds['deposit_date'] = $ds['deposit_date'];
					$pds['deposit_branch_id'] = $ds['deposit_branch_id'];
					$pds['deposit_counter_id'] = $ds['deposit_counter_id'];
					$pds['deposit_receipt_no'] = $ds['deposit_receipt_no'];
					$pds['verified_by'] = $ds['verified_by'];
					$pds['status'] = $ds['status'];
					$pds['cancel_reason'] = $ds['cancel_reason'];
					$pds['cancel_date'] = $ds['cancel_date'];
					$pds['last_update'] = $ds['last_update'];		
					$con->sql_query("replace into pos_deposit_status ".mysql_insert_by_field($pds));
				}				
			}
		}
		$sq3->sql_freeresult($q_pds);
		
		//POS_DEPOSIT_STATUS_HISTORY
		$q_pdsh = $sq3->sql_query("select * from pos_deposit_status_history");
		if(!$q_pds){
			$err['pdsh'] = "Deposit Status History Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($ds = $sq3->sql_fetchrow($q_pds))
				{
					$pds = array();					
						
					$pds['branch_id'] = $bid;
					$pds['counter_id'] = $counter_id;
					$pds['pos_id'] = $this->get_real_pos_id($bid,$counter_id,$ds['receipt_no'],$ds['date']);
					$pds['pos_date'] = $pos_db;
					$pds['receipt_no'] = $r['receipt_no'];
					$pds['deposit_branch_id'] = $r['deposit_branch_id'];
					$pds['deposit_counter_id'] = $r['deposit_counter_id'];
					$pds['deposit_pos_id'] = $this->get_real_pos_id($ds['deposit_branch_id'],$ds['deposit_counter_id'],$ds['deposit_receipt_no'],$ds['deposit_date']);
					$pds['deposit_pos_date'] = $r['deposit_pos_date'];
					$pds['deposit_receipt_no'] = $r['deposit_receipt_no'];
					$pds['user_id'] = $r['user_id'];
					$pds['type'] = $r['type'];
					$pds['remark'] = $r['remark'];
					$pds['added'] = $r['pos_time'];
					$pds['cancel_date'] = $r['cancel_date'];		
					$pds['approved_by'] = $r['approved_by'];
					$con->sql_query("replace into pos_deposit_status_history ".mysql_insert_by_field($pds));
				}				
			}
		}
		$sq3->sql_freeresult($q_pds);
		
		//Update user log
		$q_ul = $sq3->sql_query("select rowid, * from pos_user_log");
		if(!$q_ul){
			$err['ul'] = "User Log Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
					while($ul = $sq3->sql_fetchrow($q_ul)){
						$pos_ul = array();
						$pos_ul['branch_id'] = $bid;
						$pos_ul['counter_id'] = $counter_id;							                
						$pos_ul['id'] = mi($ul['rowid']);
						$pos_ul['date'] = $date;
						$pos_ul['cashier_id'] = $ul['cashier_id'];
						$pos_ul['type'] = $ul['type'];
						$pos_ul['timestamp'] = $ul['timestamp'];
						$pos_ul['ref_no'] = $ul['ref_no'];
						
						$con->sql_query("replace into pos_user_log ".mysql_insert_by_field($pos_ul));
					}
	           }
		}
		
		$sq3->sql_freeresult($q_ul);
		
		//Promotion Membership data
		$q_pmi = $sq3->sql_query("select rowid,* from membership_promotion_items");
		if(!$q_pmi){
			$err['pmi'] = "Membership promotion items Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($pmi = $sq3->sql_fetchrow($q_pmi)){
					$pos_pmi = array();
					$pos_pmi['id'] = $pmi['rowid'];
					$pos_pmi['branch_id'] = $bid;
					$pos_pmi['counter_id'] = $counter_id;							                						
					$pos_pmi['card_no'] = $pmi['card_no'];
					$pos_pmi['promo_id'] = $pmi['promo_id'];
					$pos_pmi['pos_id'] = $pmi['pos_id'];
					$pos_pmi['sku_item_id'] = $pmi['sku_item_id'];
					$pos_pmi['qty'] = $pmi['qty'];
					$pos_pmi['date'] = $date;
					$pos_pmi['added'] = $pmi['added'];
					$con->sql_query("replace into membership_promotion_items ".mysql_insert_by_field($pos_pmi));
				}
	        }
		}
		
		$sq3->sql_freeresult($q_pmi);
		
		$q_pmmi = $sq3->sql_query("select rowid,* from membership_promotion_mix_n_match_items");
		if(!$q_pmmi){
			$err['pmmi'] = "Membership promotion mix and match items Error: ".$sq3->last_error;  // got error
		}else{
			if($is_import){
				while($pmmi = $sq3->sql_fetchrow($q_pmi)){
					$pos_pmmi = array();
					$pos_pmmi['branch_id'] = $pmmi['branch_id'];
					$pos_pmmi['card_no'] = $pmmi['card_no'];
					$pos_pmmi['promo_id'] = $pmmi['promo_id'];
					$pos_pmmi['real_promo_id'] = substr($pmmi['promo_id'], 0, -3);
					$pos_pmmi['group_id'] = $pmmi['group_id'];
					$pos_pmmi['pos_id'] = $pmmi['pos_id'];
					$pos_pmmi['counter_id'] = $pmmi['counter_id'];
					$pos_pmmi['qty'] = $pmmi['qty'];
					$pos_pmmi['amount'] = $pmmi['amount'];
					$pos_pmmi['date'] = $pmmi['date'];
					$pos_pmmi['added'] = $pmmi['added'];
					$pos_pmmi['used'] = $pmmi['used'];
					$pos_pmmi['id'] = $pmmi['rowid'];
					$con->sql_query("replace into membership_promotion_mix_n_match_items ".mysql_insert_by_field($pos_pmmi));
				}
	        }
		}
	
		$sq3->sql_freeresult($q_pmmi);
		
		
		if(!$err)	$data['success'] = 1;

		$params['data'] = $data;
	}
	
	function get_real_pos_id($bid,$cid,$receipt_no,$date,$pos_id=false)
	{
		global $con;
		$con->sql_query("select id from pos where branch_id=".mi($bid)." and counter_id=".mi($cid)." and date=".ms($date)." and receipt_no=".mi($receipt_no));
		$real_pos = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		return $real_pos['id'];
	}
	
	private function prepare_db(&$sq3){
		// POS
		$sq3->sql_query("create table if not exists
			pos (
				id integer primary key,
				counter_id integer,
				cashier_id integer,
				start_time integer,
				end_time integer,
				pos_time integer,
				amount double,
				amount_tender double,
				amount_change double,
				member_no char(16),
				race char(5),
				receipt_no integer,
				point integer,
				cancel_status integer(1) default 0,
				sync integer(1) default 0,
				redeem_points integer,
				total_cost double,
				receipt_remark text,
				prune_status integer(1) default 0,
				receipt_sa text
				)");
		$sq3->die_on_error = false;
		$sq3->sql_query("alter table pos add redeem_points integer");
		$sq3->sql_query("alter table pos add total_cost double");
		$sq3->sql_query("alter table pos add receipt_remark text");
		$sq3->sql_query("alter table pos add prune_status integer(1) default 0");
		$sq3->sql_query("alter table pos add receipt_sa text");
		$sq3->sql_query("alter table pos add mix_n_match_point text");		
		$sq3->sql_query("alter table pos add transaction_complete tinyint default 1");
		$sq3->sql_query("alter table pos add refund_by integer");
		$sq3->sql_query("alter table pos add quota_used double not null default 0");
		$sq3->sql_query("alter table pos add quota_over_by int default 0");
		$sq3->sql_query("alter table pos add staff_approved_by int default 0");
		$sq3->sql_query("alter table pos add pos_more_info text");
		$sq3->sql_query("alter table pos add sales_order_id int");
		$sq3->sql_query("alter table pos add sales_order_branch_id int");
		$sq3->sql_query("alter table pos add update_sales_order integer(1) default 0");
		$sq3->die_on_error = true;
		
		// POS ITEMS
		$sq3->sql_query("create table if not exists
			pos_items (
				id integer,
				pos_id integer,
				item_id integer,
				sku_item_id integer,
				barcode char(13),
				qty double,
				price double,
				discount double,
				return_by integer,
				item_discount_by integer,
				mprice_type char(10),
				trade_discount_code char(6),
				redeem_point integer,
				cost double,
				promotion_id integer,
				remark text,
				cb_code char(6),
				cb_profit double,
				cb_discount char(10),
				cb_use_net boolean default 0,
				cb_net_bearing double,
				open_price_by integer,
				item_sa text,
				primary key (pos_id,id)
				)");
		$sq3->die_on_error = false;
		$sq3->sql_query("alter table pos_items add redeem_points integer");
		$sq3->sql_query("alter table pos_items add cost double");
		$sq3->sql_query("alter table pos_items add promotion_id integer");
		$sq3->sql_query("alter table pos_items add remark text");
		$sq3->sql_query("alter table pos_items add cb_code char(6)");
		$sq3->sql_query("alter table pos_items add cb_profit double");
		$sq3->sql_query("alter table pos_items add cb_discount char(10)");
		$sq3->sql_query("alter table pos_items add cb_use_net boolean default 0");
		$sq3->sql_query("alter table pos_items add cb_net_bearing double");
		$sq3->sql_query("alter table pos_items add open_price_by integer");
		$sq3->sql_query("alter table pos_items add item_sa text");
		$sq3->sql_query("alter table pos_items add member_point text");
		$sq3->sql_query("alter table pos_items add temp_price boolean default 0");
		$sq3->sql_query("alter table pos_items add trade_in_by integer");
		$sq3->sql_query("alter table pos_items add is_return_policy integer default 0");
		$sq3->sql_query("alter table pos_items add expired_return_policy integer default 0");
		$sq3->sql_query("alter table pos_items add more_info text");
		$sq3->sql_query("alter table pos_items add got_return_policy boolean default 0");
		$sq3->die_on_error = true;
		
		// POS PAYMENT
		$sq3->sql_query("create table if not exists
			pos_payment (
				id integer,
				pos_id integer,
				type char(30),
				remark char(50),
				amount double,
				approved_by integer,
				more_info text,
				primary key (pos_id,id)
				)");
		$sq3->die_on_error = false;		
		$sq3->sql_query("alter table pos_payment add approved_by integer");
		$sq3->sql_query("alter table pos_payment add more_info text");
		$sq3->sql_query("alter table pos_payment add is_abnormal integer default 0");
		$sq3->die_on_error = true;
		
		// RECEIPT CANCEL
		$sq3->sql_query("create table if not exists
			receipt_cancel (
				id integer,
				receipt_no integer,
				cancelled_by integer,
				cancelled_time integer,
				verified_by integer,
				sync integer(1) default 0
				)");
		
		// CASH HISTORY
		$sq3->sql_query("create table if not exists
			cash_history (
				cashier_id integer,
				type char(10),
				amount double,
				time integer,
				sync integer(1) default 0,
				collect_by integer,
				remark char(16)
			)");
		$sq3->die_on_error = false;	
		$sq3->sql_query("alter table cash_history add collect_by integer");
        $sq3->sql_query("alter table cash_history add remark char(16)");
		$sq3->sql_query("alter table cash_history add ref_no char(30)");
		$sq3->die_on_error = true;
		
		// POS GOODS RETURN	
		$sq3->sql_query("create table if not exists
			pos_goods_return (
				counter_id integer,
				date date,
				pos_id integer,
				item_id integer,
				return_counter_id integer,
				return_date date,
				return_pos_id integer,
				return_item_id integer,
				sync integer(1) default 0,
				return_receipt_no integer,
				primary key (counter_id, date, pos_id, item_id)
			)");
		$sq3->die_on_error = false;	
		$sq3->sql_query("alter table pos_goods_return add return_receipt_no integer");
		$sq3->die_on_error = true;
		
		// POS DELETE ITEMS
		$sq3->sql_query("create table if not exists
			pos_delete_items (
				id integer,
				pos_id integer,
				sku_item_id integer,
				barcode char(13),
				qty double,
				price double,
				delete_by integer,
				primary key (pos_id,id)
				)");
		$sq3->die_on_error = false;
		$sq3->sql_query("alter table pos_delete_items add pos_id integer");
		$sq3->die_on_error = true;
		// SN INFO
		$sq3->sql_query("create table if not exists
			sn_info (
				pos_id integer,
				item_id integer,
				branch_id integer,
				date date,
				counter_id integer,
				sku_item_id integer,
				serial_no char(20),
				nric char(30),
				name char(100),
				address char(200),
				contact_no char(20),
				email char(50),
				warranty_expired date,
				active tinyint(1) default 1,
				approved_by integer,
				sync tinyint(1) default 0,
				primary key (pos_id, item_id, branch_id, date, counter_id, sku_item_id, serial_no, active)
				)");
		
		// POS MIX MATCH USAGE		
		$sq3->sql_query("create table if not exists pos_mix_match_usage(
				pos_id integer,
				id integer,
				remark char(50),
				amount double,
				group_id integer,
				promo_id integer,
				more_info text,
				primary key (pos_id, id)
				)");
		
		// POS DRAWER
		$sq3->sql_query("create table if not exists
			pos_drawer (
				id integer primary key,
				user_id integer,
				timestamp timestamp,
				sync integer(1) default 0
				)");
		$sq3->die_on_error = false;	
		$sq3->sql_query("alter table pos_drawer add ref_no char(30)");
		//add type column into pos_drawer
		$sq3->sql_query("alter table pos_drawer add type char(20) default \"POS\"");		
		$sq3->die_on_error = true;
		
		// POS CASH DOMINATION
		$sq3->sql_query("create table if not exists
			pos_cash_domination (
				id integer primary key,
				user_id integer,
				data text,
				timestamp timestamp default current_timestamp,
				sync integer(1) default 0,
				clear_drawer integer(1) default 0,
				curr_rate text
				)");
		$sq3->die_on_error = false;	
		$sq3->sql_query("alter table pos_cash_domination add ref_no char(30)");
		$sq3->die_on_error = true;
		
		//Add Pos Deposit and Pos Deposit Status Table, and Add deposit column into Pos
		$sq3->die_on_error = false;
		$sq3->sql_query("alter table pos add deposit integer(1) default 0");
		//$sq3->sql_query("alter table cash_history add more_info text");
		$sq3->die_on_error = true;
		
		//Create deposit table
		$sq3->sql_query("create table if not exists
				pos_deposit(
					id integer,
					pos_id integer,
					pos_time integer,
					receipt_no char(20),
					item_list text,
					deposit_amount double,
					cashier_id integer,
					approved_by integer,
					primary key(pos_id,id)
					)");
					
		//Create deposit_status table
		$sq3->sql_query("create table if not exists pos_deposit_status(
			id integer,
			branch_id integer,
			counter_id integer,
			pos_id integer,
			date date,
			receipt_no integer,
			deposit_branch_id integer,
			deposit_counter_id integer,
			deposit_pos_id integer,
			deposit_date date,
			deposit_receipt_no integer,
			verified_by integer,
			status integer,
			cancel_reason text,
			sync integer(1) default 0, primary key(id,deposit_branch_id,deposit_counter_id,deposit_pos_id,deposit_date,deposit_receipt_no))");
		$sq3->die_on_error = false;	
		$sq3->sql_query("alter table pos_deposit_status add cancel_date date");
		$sq3->sql_query("alter table pos_deposit_status add last_update timestamp default 0");
		$sq3->die_on_error = true;
		//Create deposit_status_history table
		$sq3->sql_query("create table if not exists pos_deposit_status_history(
			id integer,
			branch_id integer,
			counter_id integer,
			pos_id integer,
			pos_date date,
			pos_time timestamp,
			receipt_no integer,
			deposit_branch_id integer,
			deposit_counter_id integer,
			deposit_pos_id integer,
			deposit_pos_date date,
			deposit_receipt_no integer,
			user_id integer,
			type char(50),
			remark text,
			sync integer(1) default 0, primary key(id,branch_id,counter_id,pos_id,pos_date,receipt_no))");
		$sq3->die_on_error = false;
		$sq3->sql_query("alter table pos_deposit_status_history add cancel_date date");
		$sq3->sql_query("alter table pos_deposit_status_history add approved_by integer");
		$sq3->die_on_error = true;
		//Create POS_member_point_adjustment_table
		$sq3->sql_query("create table if not exists pos_member_point_adjustment(
			pos_id integer,
			counter_id integer,
			nric char(30),
			card_no char(30),
			adjust_date timestamp,
			points integer,
			reason text,
			remark text,
			type char(30),
			is_delivery integer default 0,
			delivery_name char(100),
			delivery_address char(100),
			delivery_phone char(50),
			ref_receipt_ref_no char(20),
			primary key(counter_id,pos_id,adjust_date,type)
		)");
		
		//Create record membership_promotion_limit
		$sq3->sql_query("create table if not exists membership_promotion_items (
			id integer, 
			branch_id integer, 
			card_no char(20), 
			promo_id integer, 
			pos_id integer,
			counter_id integer, 
			sku_item_id integer, 
			qty double default 0, 
			date date, 
			added timestamp default 0, 
			sync tinyint(1) default 0,
			PRIMARY KEY (id,branch_id,counter_id,date)
		)");
		
		$sq3->sql_query("create table if not exists membership_promotion_mix_n_match_items(
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
		$sq3->sql_freeresult();
		
		//Create pos user log table
		$sq3->sql_query("create table if not exists pos_user_log(
			id integer,
			counter_id integer,
			cashier_id integer,
			type char(20),
			timestamp timestamp,
			ref_no char(20),
			sync tinyint(1) default 0,
			primary key(id,counter_id)
		)");
		$sq3->sql_freeresult();
	}
}

$IMPORT_POS_SALES = new IMPORT_POS_SALES('Import POS Sales');
?>
