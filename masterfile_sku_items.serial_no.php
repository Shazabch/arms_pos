<?php
/*
6/24/2011 4:52:21 PM Andy
- Make all branch default sort by sequence, code.

07/16/2013 05:25 PM Justin
- Enhanced to capture status by wording instead of numberic.

8/12/2013 1:45 PM Justin
- Enhanced to show user can change customer info while has privilege.

3/31/2014 4:26 PM Justin
- Bug fixed on showing invalid customer info from rejected DO while S/N is not sold.

4/8/2014 2:45 PM Justin
- Bug fixed on some customer info can't show out.

4/18/2014 2:54 PM Justin
- Enhanced to allow user search by Serial No.

5/30/2014 5:46 PM Justin
- Enhanced to have range insert for Serial No.

8/7/2014 9:19 AM Justin
- Enhanced to have remark column.

6/18/2015 11:30 AM Justin
- Enhanced to check and show error message if user trying to add existing S/N from other branch.

6/21/2016 4:41 PM Andy
- Fixed the item not show at HQ when filter all located branch.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/20/2019 10:10 AM William
- Pickup report_prefix for enhance "GRN".
*/
include("include/common.php");
//ini_set("display_errors",1);
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$sql = "select id,code from branch where active=1 order by sequence,code";

$con->sql_query($sql) or die(mysql_error());
while($r = $con->sql_fetchrow()){
	$branch_list[$r['id']]=$r;
}
$smarty->assign('branch_list',$branch_list);

$smarty->assign("sn_rows", 20);

if(BRANCH_CODE == 'HQ'){
	$branch_id = $_REQUEST['branch_id'];
}else{
	$branch_id = get_request_branch(true);
}

if($branch_id) $b_filter = "and pis.located_branch_id = ".mi($branch_id);
else $branch_id = $sessioninfo['branch_id'];


class SKU_items_Serial_No extends Module{
	var $page_size = 30;
	
    function _default(){
		global $con, $smarty;
	    $this->display();
	}
	
	function search(){
        global $con, $smarty, $sessioninfo;

		$total_rows=0;
        $form = $_REQUEST;
		if(BRANCH_CODE == 'HQ'){
			$branch_id = mi($_REQUEST['branch_id']);
		}else{
			$branch_id = $sessioninfo['branch_id'];
		}
		
		if(!$form['tab'] || $form['tab'] == 1){
			$filter[] = "pis.active = 1"; // is active S/N
			$form['tab'] = 1;
		}elseif($form['tab'] == 2){
			$filter[] = "pis.active = 0"; // is inactive S/N
		}elseif($form['tab'] == 3 && $form['str_search']){
			$filter[] = "pis.serial_no like ".ms("%".strtoupper(replace_special_char($form['str_search']))."%");
		}

		if($form['sku_item_id']) $filter[] = "pis.sku_item_id = ".ms($form['sku_item_id']);
		if($form['sn_filter']) $filter[] = "pis.serial_no like ".ms("%".replace_special_char($form['sn_filter'])."%");
		if($branch_id) $filter[] = "pis.located_branch_id = ".mi($branch_id);
		
		if($filter) $filter = join(" and ", $filter);

		$sql = $con->sql_query("select pis.*, abranch.code as apply_branch_code, lbranch.code as located_branch_code,
								si.sku_item_code, si.description, si.artno, si.mcode
								from pos_items_sn pis
								left join sku_items si on si.id = pis.sku_item_id
								left join branch abranch on abranch.id = pis.branch_id
								left join branch lbranch on lbranch.id = pis.located_branch_id
								where $filter
								order by si.sku_item_code, pis.added");
		while($r = $con->sql_fetchassoc($sql)){
			$r1 = array();
			$sid = $r['sku_item_id'];

			// found does not filter by SKU item
			if(!$form['sku_item_id'] && !$si_info[$sid]){
				$tmp = array();
				$tmp['id'] = $r['sku_item_id'];
				$tmp['sku_item_code'] = $r['sku_item_code'];
				$tmp['description'] = $r['description'];
				$tmp['artno'] = $r['artno'];
				$tmp['mcode'] = $r['mcode'];
				$si_info_list[$sid] = $tmp;
			}
			
			if($r['status'] == "Sold"){
				$sql1 = $con->sql_query("select sni.nric, sni.name, sni.address, sni.contact_no, sni.email, sni.warranty_expired
										 from sn_info sni
										 where sni.serial_no = ".ms($r['serial_no'])."
										 and sni.pos_id = ".mi($r['pos_id'])."
										 and sni.item_id = ".mi($r['pos_item_id'])."
										 and sni.branch_id = ".mi($r['pos_branch_id'])."
										 and sni.date = ".ms($r['date'])."
										 and sni.counter_id = ".mi($r['counter_id'])."
										 and sni.sku_item_id = ".mi($r['sku_item_id'])."
										 order by sni.date desc
										 limit 1");

				$r1 = $con->sql_fetchassoc($sql1);
				$con->sql_freeresult($sql1);
				if($r1){
					$r = array_merge($r, $r1);
					//$r['status'] = 'Sold';
				}
			}
			
			// get GRN no
			$sn_count = strlen($r['serial_no']);
			$grn_sn_search = 's:'.mi($sn_count).':"'.$r['serial_no'].'";';
			$sql2 = $con->sql_query("select *,branch.report_prefix from grn_items left join branch on grn_items.branch_id = branch.id where grn_items.branch_id = ".mi($r['located_branch_id'])." and sn_import like ".ms('%'.$grn_sn_search.'%'));
			$grn_info = $con->sql_fetchassoc($sql2);
			$con->sql_freeresult($sql2);

			if($grn_info['grn_id']) $r['grn_no'] = $grn_info['report_prefix'].str_pad($grn_info['grn_id'], 5, "0", STR_PAD_LEFT);
			
			$items[] = $r;
		}
		$con->sql_freeresult($sql);

		$smarty->assign('exception_list',$form['exc_list']);
		$smarty->assign('sku_items',$form['sku_item_code']);
		$smarty->assign('items',$items);
		$smarty->assign('si_info_list',$si_info_list);
		$smarty->assign('tab',$form['tab']);
		$smarty->assign('str_search',$form['str_search']);
		$smarty->display('masterfile_sku_items.serial_no.list.tpl');
	}

	function add(){
		global $con, $smarty, $sessioninfo, $branch_id;
		$form=$_REQUEST;

		$sku_item_id = $form['sku_item_id'];
		$branch_id = $form['branch_id'];
		$ins['sku_item_id'] = $form['sku_item_id'];
		if(!$branch_id) $branch_id = $sessioninfo['branch_id'];
		$ins['branch_id'] = $branch_id;
		$ins['remark'] = $form['remark'];
		$ins['located_branch_id'] = $branch_id;
		$ins['added'] = "CURRENT_TIMESTAMP";
		$ins['created_by'] = $sessioninfo['id'];

		if($form['tab'] == 2) $ins['active'] = 0;
		else $ins['active'] = 1;

		// delete existing record that is not being sold
		//$con->sql_query("delete from sku_items_serial_no where sku_item_id = ".ms($sku_item_id)." and status=0");

		$splt_serial_no = array();
		if($form['sn_choice'] == 1){
			$splt_serial_no = explode("\n", $form['serial_no_list']);
		}else{
			$sn_range = $form['sn_to']-$form['sn_from'];
			for($i=0; $i<=$sn_range; $i++){
				$splt_serial_no[] = $form['sn_from']+$i;
			}
		}
		
		for($i=0; $i<count($splt_serial_no); $i++){
			if(trim($splt_serial_no[$i])){ // proceed if not empty
				$err = $this->check_sn($sku_item_id, $splt_serial_no[$i], $branch_id, "");
				$his_ins = array();

				if(!$err){ // insert new row only when it is not existed
					$ins['serial_no'] = strtoupper($splt_serial_no[$i]);
					$sql = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($ins['sku_item_id'])." and serial_no = ".ms($ins['serial_no']));
					$sn_info = $con->sql_fetchrow($sql);
	
					if($con->sql_numrows($sql)==0){ // is new S/N
						$con->sql_query("insert into pos_items_sn ".mysql_insert_by_field($ins));
						$id = $con->sql_nextid();
						$remarks = "add";
						$his_ins['branch_id'] = $branch_id;
					}elseif($sn_info['located_branch_id'] != $ins['branch_id']){ // capture as error since located on other branch
						$sn_diff_branch[$splt_serial_no[$i]] = strtoupper($splt_serial_no[$i]);
						continue;
					}
					
					/*else{ // update current located branch ID
						$con->sql_query("update pos_items_sn set located_branch_id = ".mi($ins['branch_id']).", active = 1, last_update = CURRENT_TIMESTAMP where sku_item_id = ".mi($ins['sku_item_id'])." and serial_no = ".ms($ins['serial_no']));
						$id = $sn_info['id'];
						$remarks = "edit";
						$his_ins['branch_id'] = $sn_info['branch_id'];
					}*/

					// insert S/N history
					$his_ins['pisn_id'] = $id;
					$his_ins['sku_item_id'] = $ins['sku_item_id'];
					$his_ins['located_branch_id'] = $ins['branch_id'];
					$his_ins['serial_no'] = $ins['serial_no'];
					$his_ins['remark'] = $remarks;
					$his_ins['status'] = 'Available';
					$his_ins['active'] = $ins['active'];
					$his_ins['added'] = "CURRENT_TIMESTAMP";
					$his_ins['user_id'] = $sessioninfo['id'];

					$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));

					$his_ins['id'] = $id;
					$his_ins['apply_branch_code'] = get_branch_code($his_ins['branch_id']);
					$his_ins['located_branch_code'] = get_branch_code($ins['branch_id']);

					if($remarks == "edit"){
						$temp['upd_id'] = $id;
						$temp['upd_branch_id'] = $sn_info['branch_id'];
						$temp['is_update'] = 1;
						$his_ins['is_update'] = 1;
					}
					$his_ins['remark'] = $form['remark'];

					$smarty->assign("item", $his_ins);

					$temp['rowdata'] = $smarty->fetch("masterfile_sku_items.serial_no.list_row.tpl");

					$row_items[] = $temp;
				}else{
					if($err == 1) $sn_sold[$splt_serial_no[$i]] = strtoupper($splt_serial_no[$i]);
					elseif($err == 2) $existed_sn[$splt_serial_no[$i]] = strtoupper($splt_serial_no[$i]);
					//$exception_list[] = $splt_serial_no[$i];
				}
			}
		}
		if(count($sn_sold)>0) $row_items[]['sn_sold'] = join(", ", $sn_sold);
		if(count($existed_sn)>0) $row_items[]['existed_sn'] = join(", ", $existed_sn);
		if(count($sn_diff_branch)>0) $row_items[]['sn_diff_branch'] = join(", ", $sn_diff_branch);
		//$_REQUEST['exc_list'] = $exception_list;
		//$smarty->assign('exception_list',$form['exc_list']);
		//print_r($exception_list);

		print json_encode($row_items);
	}

	function save(){
		global $con, $smarty, $branch_id, $sessioninfo;
		$form=$_REQUEST;
		
		foreach($form['serial_no'] as $id=>$branch_id){
			foreach($branch_id as $bid=>$serial_no){
				if($serial_no){
					$serial_no = strtoupper($serial_no);
					$sid = $form['sid'][$id][$bid];

					if($form['status'][$id][$bid] == 'Available'){
						$err = $this->check_sn($sid, $serial_no, $form['sn_branch_id'][$id][$bid], $id);
						if(!$err){
							$upd = array();
							$upd['serial_no'] = $serial_no;
							$upd['remark'] = $form['remark'][$id][$bid];
							$con->sql_query("update pos_items_sn 
											 set ".mysql_update_by_field($upd)." 
											 where id = ".ms($id)." and branch_id = ".mi($bid)." and sku_item_id = ".ms($sid));
				
							if($con->sql_affectedrows()>0){
								// insert S/N history
								$his_ins['pisn_id'] = $id;
								$his_ins['branch_id'] = $bid;
								$his_ins['sku_item_id'] = $sid;
								$his_ins['located_branch_id'] = $form['sn_located_branch_id'][$id][$bid];
								$his_ins['serial_no'] = $upd['serial_no'];
								$his_ins['status'] = $form['status'][$id][$bid];
								$his_ins['active'] = $form['active'][$id][$bid];
								$his_ins['remark'] = "edit";
								$his_ins['added'] = "CURRENT_TIMESTAMP";
								$his_ins['user_id'] = $sessioninfo['id'];

								$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
							}
						}else $exception_list[$serial_no] = $serial_no;
					}elseif($form['status'][$id][$bid] == 'Sold' && privilege('MST_EDIT_SN_INFO')){
						// update remark only
						$con->sql_query("update pos_items_sn 
										 set remark = ".ms($form['remark'][$id][$bid])."
										 where id = ".ms($id)." and branch_id = ".mi($bid)." and sku_item_id = ".ms($sid));
					
						$upd = array();
						$upd['nric'] = $form['nric'][$id][$bid];
						$upd['name'] = $form['name'][$id][$bid];
						$upd['address'] = $form['address'][$id][$bid];
						$upd['contact_no'] = $form['contact_no'][$id][$bid];
						$upd['email'] = $form['email'][$id][$bid];
						$upd['warranty_expired'] = $form['warranty_expired'][$id][$bid];
						
						$q1 = $con->sql_query("update sn_info set ".mysql_update_by_field($upd)." where pos_id = ".mi($form['pos_id'][$id][$bid])." and branch_id = ".mi($form['pos_branch_id'][$id][$bid])." and item_id = ".mi($form['pos_item_id'][$id][$bid])." and date = ".ms($form['date'][$id][$bid])." and counter_id = ".mi($form['counter_id'][$id][$bid]));
						
						if($con->sql_affectedrows($q1) > 0){
							log_br($sessioninfo['id'], 'Serial Number', $sid, "Serial#$serial_no Updated: NRIC->$upd[nric], Name->$upd[name], Address->$upd[address], Contact No->$upd[contact_no], Email->$upd[email], Warranty Expired->$upd[warranty_expired]");
						}
					}
				}
			}
		}

		if(count($exception_list) > 0){
			print join(", ", $exception_list);
		}

		//$this->display();
	}

	function delete(){
		global $con, $sessioninfo;
		$form = $_REQUEST;

		$sql = $con->sql_query("select * from pos_items_sn pis where pis.id = ".mi($form['id'])." and pis.branch_id = ".mi($form['branch_id']));
		$pis = $con->sql_fetchrow($sql);

		if($con->sql_numrows($sql)>0){

			$con->sql_query("update pos_items_sn pis set active = ".mi($form['active'])." where pis.id = ".mi($form['id'])." and pis.branch_id = ".mi($form['branch_id']));

			// insert S/N history
			if($form['active'] == 1) $remarks = "activate";
			else $remarks = "deactivate";

			$his_ins['pisn_id'] = $form['id'];
			$his_ins['branch_id'] = $form['branch_id'];
			$his_ins['sku_item_id'] = $pis['sku_item_id'];
			$his_ins['located_branch_id'] = $pis['located_branch_id'];
			$his_ins['serial_no'] = strtoupper($pis['serial_no']);
			$his_ins['remark'] = $remarks;
			$his_ins['status'] = $pis['status'];
			$his_ins['active'] = $form['active'];
			$his_ins['added'] = "CURRENT_TIMESTAMP";
			$his_ins['user_id'] = $sessioninfo['id'];
			$con->sql_query("insert into pos_items_sn_history ".mysql_insert_by_field($his_ins));
		}
	}

	function check_sn($sid, $sn, $branch_id, $id){
		global $con;
		if($id) $filter = "and pis.id != ".mi($id);
		$q1 = $con->sql_query("select * from pos_items_sn pis where pis.sku_item_id = ".mi($sid)." and pis.located_branch_id = ".mi($branch_id)." $filter and pis.serial_no = ".ms($sn));
		$pis = $con->sql_fetchassoc($q1);

		if($con->sql_numrows($q1) > 0){
			if($pis['status'] == 'Available') return 2; // S/N is duplicated
			else return 1; // S/N that has been sold
		}
		$con->sql_freeresult($pis);
	}
}

$SKU_items_Serial_No = new SKU_items_Serial_No('Serial No Listing');
?>
