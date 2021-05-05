<?php
/*
REVISION HISTORY
================
4/7/2008 12:05:51 PM gary
- selling and cost_price get the latest one b4 implode or explode.

7/9/2008 1:12:46 PM yinsee
- GRN amount calculation was very very wrong! 

7/14/2008 yinsee
- update inventory when explode/implode

29/9/2009 10:00:00 PM jeff
- change sku autocomplete to multiple add

10/25/2010 3:49:58 PM Justin
- Placed the update is_parent = 1 when adding new ARNS code from BOM editor.

11/1/2010 6:12:40 PM Justin
- Fixed the bug not to insert all the Bom Editor's SKU items become as parent but only insert once for the very first SKU item added.

6/24/2011 3:42:07 PM Andy
- Make all branch default sort by sequence, code.

7/19/2011 4:49PM yinsee
- fix grn/grr cost calculation bug

9/19/2012 10:50 AM Andy
- Add new BOM Type (Package). (Need Config)

9/26/2012 12:34 PM Justin
- Enhanced to insert/update new info which sent from BOM interface.

11/15/2012 3:19 PM Andy
- Added when implode will save the cost to adjustment.

12/18/2012 3:32 PM Justin
- Bug fixed on system did not show BOM Type while creating new bom item.

2/18/2013 5:48 PM Justin
- Bug fixed on system did not capture the latest selling price and cost while creating adjustment from implode/explode.

1/7/2015 4:40 PM Justin
- Enhanced to have GST calculation.

1/28/2015 3:08 PM Andy
- Change the bom item printing order to same as listing order.
- Change the selling price must always >0 except is open price.

3/24/2015 3:40 PM Andy
- Change to allow zero selling price if "allow selling foc" is ticked.

9/23/2015 4:02 PM DingRen
- fix BOM sku cat discount not inherit when create new item

5/12/2017 16:28 Qiu Ying
- Bug fixed on SKU receipt description corrupted if too long

7/13/2018 2:31 PM Andy
- Fixed bug where item in bom content will become missing if user delete it in other tab.
- Add check tmp version 354.

3/19/2019 1:37 PM Justin
- Enlarged the memory limit to 1024MB.

8/6/2019 2:18 PM Andy
- Enhanced to check and show error if BOM has reached the maximum of child sku. (max: 9999 sku).

11/7/2019 9:15 AM William
- Change active to default checked.
- Fixed bug artno, mcode field will disable on create new item when last edit item has implode.
- Pick up sku type description to show.
- Fixed "Reason" textarea will get wrong value when click submit and page return  warning.

11/13/2019 9:57 AM William
- Fixed bug "FOC" cannot save when add new BOM.

11/14/2019 1:24 PM William
- Fixed bug bom editor not checking config "sku_application_valid_mcode" when config has setting.
- Fixed bug mcode not checking config "sku_application_artno_allow_duplicate" when mcode duplicate.

1/8/2020 10:27 AM William
- Enhanced to insert id manually for adjustment tables that uses auto increment.

02/15/2021 05:28PM Rayleen
- Update marketplace required field - Weight, Lenght, Width, Height and Marketplace Description

02/16/2021 02:13PM Rayleen
- Check if "marketplace_description" field exists before updating
*/
include("include/common.php");
include("masterfile_sku_application.include.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
ini_set('memory_limit', '1024M');
$maintenance->check(241);
$maintenance->check(354, true);

$smarty->assign("PAGE_TITLE", "BOM Editor");

init_selection();

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
		case 'load_price_list':
	    	$form=$_REQUEST;
	    	get_latest_selling($sessioninfo['branch_id'], $form['sku_item_id'], false);
	    	$smarty->display("bom.refresh.price_list.tpl");			
			exit;

		case 'explode':
			$form=$_REQUEST;
			$bom_id=intval($form['bom_id']);
			$sku_id=intval($form['sku_bom']);
			
			if($bom_id && $form['qty_bom']>0){
			    $q1=$con->sql_query("select * from sku where id=$sku_id and is_bom");
			    $r1=$con->sql_fetchrow($q1);
			    
			    $q2=$con->sql_query("select * from sku_items where id=$bom_id");
			    $r2=$con->sql_fetchrow($q2);
			    
				//DEDUCT HAMPER USING ADJ
				$adj['id']=$appCore->generateNewID("adjustment", "branch_id=".mi($sessioninfo['branch_id']));
				$adj['branch_id']=$sessioninfo['branch_id'];
				$adj['user_id']=$sessioninfo['id'];
				$adj['adjustment_date']=date('Y-m-d');
				$adj['adjustment_type']="BOM/HAMPER EXPLODE (".$r2['sku_item_code'].")";
				$adj['added']='CURRENT_TIMESTAMP()';
				$adj['dept_id']=$r1['category_id'];
				$adj['status']=1;
				$adj['approved']=1;
				
				if($r2['mcode'])
				  $m_c = "\nMcode: ".$r2['mcode'];
				if($r2['artno'])
				  $a_n = "\nArtno: ".$r2['artno'];
				if($form['qty_bom'])
				  $e_qty = "\nQty: ".$form['qty_bom'];
				
				$adj['remark'] = $r2['description'].$m_c.$a_n.$e_qty;
				
				//INSERT ADJ
				$con->sql_query("insert into adjustment " . mysql_insert_by_field($adj, array('id', 'branch_id', 'user_id', 'adjustment_date', 'adjustment_type', 'added', 'dept_id','status','approved','remark')));
				$adj_items['id'] = $appCore->generateNewID("adjustment_items", "branch_id=".mi($adj['branch_id']));
	    		$adj_items['adjustment_id'] = $adj['id'];
	    		
		    	$adj_items['branch_id']=$adj['branch_id'];
		    	$adj_items['user_id']=$adj['user_id'];
		    	$adj_items['sku_item_id']=$bom_id;
		    	$adj_items['qty']=-($form['qty_bom']);
				$cost=get_latest_cost($adj['branch_id'],$adj['adjustment_date'], $r2['sku_item_code']);
				$adj_items['cost'] = $cost['latest_cost'];
				$selling_price=get_latest_selling($adj['branch_id'],$r2['id'],true);
				$adj_items['selling_price'] = $selling_price['latest_selling'];
				
				//INSERT ADJ_ITEMS		    	
				$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($adj_items, array('id', 'adjustment_id', 'branch_id', 'user_id','sku_item_id','qty','cost','selling_price')));	
				// FLAG THE INVENTORY CHANGE
				$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = $adj_items[sku_item_id] and branch_id = $adj_items[branch_id]");

				//ADD SKU ITEMS FROM HAMPER USING ADJ
			    $q3=$con->sql_query("select bom_items.*, sku_items.sku_item_code, sku_items.id as sku_item_id
from bom_items 
left join sku_items on sku_items.id=sku_item_id 
where bom_id=$bom_id");
			    while($r3 = $con->sql_fetchrow($q3)){
					$adj_items['id'] = $appCore->generateNewID("adjustment_items", "branch_id=".mi($adj['branch_id']));
			    	$adj_items['branch_id']=$adj['branch_id'];
			    	$adj_items['user_id']=$adj['user_id'];
			    	$adj_items['sku_item_id']=$r3['sku_item_id'];
			    	$adj_items['qty']=$r3['qty']*$form['qty_bom'];
					$cost=get_latest_cost($adj['branch_id'],$adj['adjustment_date'], $r3['sku_item_code']);
					$adj_items['cost'] = $cost['latest_cost'];
					$selling_price=get_latest_selling($adj['branch_id'],$r3['sku_item_id'],true);
					$adj_items['selling_price'] = $selling_price['latest_selling'];
		
					// INSERT ADJ_ITEMS		    	
					$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($adj_items, array('id', 'adjustment_id', 'branch_id', 'user_id','sku_item_id','qty','cost','selling_price')));		
					// FLAG THE INVENTORY CHANGE
					$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = $adj_items[sku_item_id] and branch_id = $adj_items[branch_id]");
				}
				header("Location: $_SERVER[PHP_SELF]?a=load_bom_details&sku_bom=$sku_id&t=$form[a]&bom_id=$bom_id&q=$form[qty_bom]");					
			}					
			exit;

		case 'implode':
			$form=$_REQUEST;
			$bom_id=intval($form['bom_id']);
			$sku_id=intval($form['sku_bom']);
			
			if($bom_id && $form['qty_bom']>0){
				//saving the update bom_items
				//echo"<pre>";print_r($form);echo"</pre>";
				//exit;
				save_bom_items();
				$con->sql_query("delete from bom_items where bom_id=$bom_id");				
				$q0 = $con->sql_query("select * from tmp_bom_items where bom_id=$bom_id and user_id=$sessioninfo[id] and edit_time=".ms($form['edit_time'])." order by id");	
				while($r0=$con->sql_fetchrow($q0)){
				    $r0['bom_id'] = $bom_id;
				    
					$con->sql_query("insert into bom_items " . mysql_insert_by_field($r0, array('bom_id', 'sku_item_id', 'user_id', 'qty', 'cost_price', 'selling_price')));		
				}
				$con->sql_query("delete from tmp_bom_items where user_id = $sessioninfo[id] and edit_time=".ms($form['edit_time']));
				
			    $q1=$con->sql_query("select * from sku where id=$sku_id and is_bom");
			    $r1=$con->sql_fetchrow($q1);
			    
			    $q2=$con->sql_query("select * from sku_items where id=$bom_id");
			    $r2=$con->sql_fetchrow($q2);
			  
				//DEDUCT HAMPER ITEMS USING ADJ		
				$adj['id']=$appCore->generateNewID("adjustment", "branch_id=".mi($sessioninfo['branch_id']));
				$adj['branch_id']=$sessioninfo['branch_id'];
				$adj['user_id']=$sessioninfo['id'];
				$adj['adjustment_date']=date('Y-m-d');
				$adj['adjustment_type']="BOM/HAMPER IMPLODE (".$r2['sku_item_code'].")";
				$adj['added']='CURRENT_TIMESTAMP()';
				$adj['dept_id']=$r1['category_id'];
				$adj['status']=1;
				$adj['approved']=1;
		
				if($r2['mcode'])
				  $m_c = "\nMcode: ".$r2['mcode'];
				if($r2['artno'])
				  $a_n = "\nArtno: ".$r2['artno'];
				if($form['qty_bom'])
				  $e_qty = "\nQty: ".$form['qty_bom'];
				
				$adj['remark'] = $r2['description'].$m_c.$a_n.$e_qty;
				
				//INSERT ADJ
				$con->sql_query("insert into adjustment " . mysql_insert_by_field($adj, array('id', 'branch_id', 'user_id', 'adjustment_date', 'adjustment_type', 'added', 'dept_id','status','approved','remark')));
	    		$adj_items['adjustment_id'] = $adj['id'];
				  		
			    $q3=$con->sql_query("select bom_items.*, sku_items.sku_item_code, sku_items.id as sku_item_id
from bom_items 
left join sku_items on sku_items.id=sku_item_id 
where bom_id=$bom_id");
			    while($r3 = $con->sql_fetchrow($q3)){
					$adj_items['id']=$appCore->generateNewID("adjustment_items", "branch_id=".mi($adj['branch_id']));
			    	$adj_items['branch_id']=$adj['branch_id'];
			    	$adj_items['user_id']=$adj['user_id'];
			    	$adj_items['sku_item_id']=$r3['sku_item_id'];
			    	$adj_items['qty']=-($r3['qty'])*$form['qty_bom'];
					$cost=get_latest_cost($adj['branch_id'],$adj['adjustment_date'],$r3['sku_item_code']);
					$adj_items['cost'] = $cost['latest_cost'];
					$selling_price=get_latest_selling($adj['branch_id'],$r3['sku_item_id'],true);
					$adj_items['selling_price'] = $selling_price['latest_selling'];
										
					$grr['grr_amount']+= ($r3['qty']*$cost['latest_cost'])*$form['qty_bom'];
					
					// INSERT ADJ_ITEMS		    	
					$con->sql_query("insert into adjustment_items " . mysql_insert_by_field($adj_items, array('id', 'adjustment_id', 'branch_id', 'user_id','sku_item_id','qty','cost','selling_price')));		
					// FLAG THE INVENTORY CHANGE
					$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = $adj_items[sku_item_id] and branch_id = $adj_items[branch_id]");
				}
				
				$grr['grr_amount'] += $r2['misc_cost']*$form['qty_bom'];
				
				//CREATE GRR for hamper
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grr", "branch_id = ".mi($sessioninfo['branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grr['id']=$new_id;
				$grr['branch_id']=$sessioninfo['branch_id'];
				$grr['user_id']=$sessioninfo['id'];
				$grr['rcv_by']=$grr['user_id'];
				$grr['rcv_date']=date("Y-m-d");
				$grr['added']='CURRENT_TIMESTAMP()';
				$grr['department_id']=$r1['category_id'];
				$grr['status']=1;
				$grr['grr_pcs']=$form['qty_bom'];
				$grr['grr_ctn']='';
				$grr['transport']='';
				//$grr['grr_amount']=$grr['grr_amount']+$r2['misc_cost'];				
				//print "gramount = ".$grr['grr_amount'];
				//INSERT GRR
				$con->sql_query("insert into grr " . mysql_insert_by_field($grr, array('id', 'branch_id', 'user_id', 'rcv_by', 'rcv_date', 'grr_ctn','grr_amount', 'added', 'grr_pcs', 'status','department_id') ));
				$grr_id = $con->sql_nextid();		

				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grr_items", "branch_id = ".mi($sessioninfo['branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");

				$grr_items['id']=$new_id;
				$grr_items['grr_id']=$grr_id;
				$grr_items['branch_id']=$sessioninfo['branch_id'];
				
			    $q3=$con->sql_query("select report_prefix from branch where id=$sessioninfo[branch_id]");
			    $r3=$con->sql_fetchrow($q3);
											
				$doc_no=$r3[0] . sprintf("%05d", $adj_items['adjustment_id']);
				$grr_items['doc_no']=$doc_no;
				$grr_items['type']='OTHER';
				$grr_items['amount']=$grr['grr_amount'];
				$grr_items['remark']='BOM Implode';
				$grr_items['pcs']=$form['qty_bom'];
				$grr_items['ctn']='';				
				$grr_items['grn_used']=1;
				
				//INSERT GRR_ITEMS						
				$con->sql_query("insert into grr_items " . mysql_insert_by_field($grr_items, array('id', 'branch_id', 'grr_id', 'doc_no', 'type', 'ctn','amount', 'remark', 'pcs', 'grn_used')));				
				$grr_items_id = $con->sql_nextid();	
				
				//CREATE GRN for hamper
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grn", "branch_id = ".mi($sessioninfo['branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grn['id']=$new_id;
				$grn['branch_id']=$sessioninfo['branch_id'];
				$grn['user_id']=$sessioninfo['id'];
				$grn['grr_id']=$grr_id;
				$grn['grr_item_id']=$grr_items_id;								
				$grn['amount']=$grr['grr_amount'];
				$grn['status']=1;
				$grn['approved']=1;
				$grn['added']='CURRENT_TIMESTAMP()';
				$grn['final_amount']=$grr['grr_amount'];
				$grn['department_id']=$r1['category_id'];
				$grn['acc_action'] = 'BOM Implode';
				
				//INSERT GRN
				$con->sql_query("insert into grn " . mysql_insert_by_field($grn, array('id', 'branch_id', 'user_id', 'grr_id', 'grr_item_id', 'amount','status', 'approved', 'added', 'final_amount','department_id','acc_action') ));				
				$grn_id = $con->sql_nextid();
				
				// call appCore to generate new ID
				unset($new_id);
				$new_id = $appCore->generateNewID("grn_items", "branch_id = ".mi($sessioninfo['branch_id']));
				
				if(!$new_id) die("Unable to generate new ID from appCore!");
				
				$grn_items['id']=$new_id;
				$grn_items['branch_id']=$sessioninfo['branch_id'];
				$grn_items['grn_id']=$grn_id;				
				$grn_items['sku_item_id']=$bom_id;
				$grn_items['artno_mcode']='';
				$grn_items['uom_id']=1;
				$grn_items['cost']=$grr['grr_amount']/$form['qty_bom'];
				$grn_items['ctn']='';
				$grn_items['pcs']=$form['qty_bom'];
				
				$q4=$con->sql_query("select if(sp.price is null, selling_price, sp.price) as selling 
from sku_items 
left join sku on sku.id=sku_items.sku_id 
left join sku_items_price sp on sku_items.id = sp.sku_item_id and sp.branch_id=$grn_items[branch_id] 
where sku_items.id=$bom_id");
				$r4 = $con->sql_fetchrow($q4);
				
				$grn_items['selling_uom_id']=1;
				$grn_items['selling_price']=$r4['selling'];
				
				//INSERT GRN_ITEMS			
				$con->sql_query("insert into grn_items " . mysql_insert_by_field($grn_items, array('id', 'branch_id', 'grn_id', 'sku_item_id', 'artno_mcode', 'uom_id','cost', 'ctn', 'pcs','selling_uom_id','selling_price')));
				// FLAG THE INVENTORY CHANGE
				$con->sql_query("update sku_items_cost set changed=1 where sku_item_id = $grn_items[sku_item_id] and branch_id = $grn_items[branch_id]");
				
				// update total_selling
			    $con->sql_query("select sum(if (grn_items.acc_ctn is null and grn_items.acc_pcs is null, grn_items.ctn *rcv_uom.fraction + grn_items.pcs, grn_items.acc_ctn *rcv_uom.fraction + grn_items.acc_pcs)/sell_uom.fraction*grn_items.selling_price) as sell
from grn_items
left join uom sell_uom on grn_items.selling_uom_id=sell_uom.id
left join uom rcv_uom on grn_items.uom_id=rcv_uom.id
where grn_id=$grn_id and branch_id=$grn[branch_id]") or die(mysql_error());
			    $t = $con->sql_fetchrow();
			    $t[0] = doubleval($t[0]); 
			
			    $con->sql_query("update grn set last_update=last_update,total_selling=$t[0] where id=$grn_id and branch_id=$grn[branch_id]");
				header("Location: $_SERVER[PHP_SELF]?a=load_bom_details&sku_bom=$sku_id&t=$form[a]&bom_id=$bom_id&q=$form[qty_bom]");
						
			}
			exit;

		case 'ajax_delete_row':
			save_bom_items();
			//echo"<pre>";print_r($_REQUEST);echo"</pre>";
	        $id = intval($_REQUEST['delete_id']);
	        $bom_id = intval($_REQUEST['bom_id']);
	        $user_id = intval($sessioninfo['id']);			
	        $con->sql_query("delete from tmp_bom_items where id=$id and bom_id=$bom_id and user_id=$user_id");		
			exit;	

		case 'confirm':
			$form=$_REQUEST;
			$bom_id=intval($form['bom_id']);
			save_bom_items();
			$errm = validate_data($form);
			if (!$errm){
				if($bom_id>0){
					$update = array();
					$update['description'] = $form['description'];
					$update['receipt_description'] = $form['receipt_description'];
					$update['selling_price'] = $form['selling_price'];
					$update['cost_price'] = $form['cost_price'];
					$update['misc_cost'] = $form['misc_cost'];
					$update['artno'] = $form['artno'];
					$update['mcode'] = $form['mcode'];
					$update['allow_selling_foc'] = $form['allow_selling_foc'];
					$update['selling_foc'] = $form['selling_foc'];
					$update['location'] = $form['location'];
					$update['block_list'] = serialize($form['block_list']);
					$update['open_price'] = $form['open_price'];
					$update['active'] = mi($form['active']);
					$update['non_returnable'] = $form['non_returnable'];
					$update['output_tax'] = $form['output_tax'];
					$update['inclusive_tax'] = $form['inclusive_tax'];
					$update['weight_kg'] = $form['weight_kg'];
					$update['width'] = $form['width'];
					$update['height'] = $form['height'];
					$update['length'] = $form['length'];
					if(isset($form['marketplace_description'])){
						$update['marketplace_description'] = $form['marketplace_description'];
					}

		    		if($config['sku_bom_additional_type']){
						$update['bom_type'] = $form['bom_type'];
						if(!$update['bom_type'])	$update['bom_type'] = 'normal';
					}
					
					if ($form['reason'] != ""){
						log_br($sessioninfo['id'], 'MASTERFILE_SKU_ACT', $bom_id, $form['reason']);
					}
					
					// category discount
					// inherit option
					$update['cat_disc_inherit'] = trim($form['cat_disc_inherit'][$bom_id]);
					
					// inherit value
					if($update['cat_disc_inherit']!='set'){
						$form['category_disc_by_branch_inherit'][$bom_id] = array();
					}
					$update['category_disc_by_branch_inherit'] = serialize($form['category_disc_by_branch_inherit'][$bom_id]);
							
					// category reward point
					// inherit option
					$update['category_point_inherit'] = trim($form['category_point_inherit'][$bom_id]);

					// inherit value
					if($update['category_point_inherit']!='set'){
						$form['category_point_by_branch_inherit'][$bom_id] = array();
					}
					
					$update['category_point_by_branch_inherit'] = serialize($form['category_point_by_branch_inherit'][$bom_id]);
					
					$con->sql_query("update sku_items set ".mysql_update_by_field($update)." where id=".mi($bom_id));
					
					$con->sql_query("delete from bom_items where bom_id=".mi($bom_id));
				}
				else{
				    $con->sql_query("select max(sku_item_code) from sku_items where sku_id = ".mi($form['sku_id']));
				    if ($r = $con->sql_fetchrow()){
				        $itemcode = $r[0];
					}
					if (!$itemcode){
						$itemcode = sprintf(ARMS_SKU_CODE_PREFIX, $form['sku_id'])."0000";
						$form['is_parent'] = 1;
					}
					else{
						$itemcode++;
						$form['is_parent'] = 0;
						
						// Check duplicate
						$con->sql_query("select id from sku_items where sku_item_code=".ms($itemcode));
						$code_exists = $con->sql_fetchassoc();
						$con->sql_freeresult();
						
						if($code_exists){
							$code_prefix = sprintf(ARMS_SKU_CODE_PREFIX, $form['sku_id']);
							// Try get max code using prefix
							$con->sql_query("select max(sku_item_code) as max_sku_item_code from sku_items where sku_item_code like ".ms($code_prefix."%"));
							$tmp = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							$itemcode = $tmp['max_sku_item_code']++;
							
							// Check duplicate
							$con->sql_query("select id from sku_items where sku_item_code=".ms($itemcode));
							$code_exists = $con->sql_fetchassoc();
							$con->sql_freeresult();
							
							if($code_exists){
								$errm['top'][] = "BOM has reached the maximum Child SKU, please create another BOM";	
							}
						}
					}
					
					if(!$errm){
						$form['sku_item_code']=$itemcode;
						$form['added'] = date("Y-m-d H:i:s");

						$upd = array();
						$upd['sku_id'] = $form['sku_id'];
						$upd['sku_item_code'] = $form['sku_item_code'];
						$upd['description'] = $form['description'];
						$upd['receipt_description'] = $form['receipt_description'];
						$upd['selling_price'] = $form['selling_price'];
						$upd['cost_price'] = $form['cost_price'];
						$upd['added'] = $form['added'];
						$upd['misc_cost'] = $form['misc_cost'];
						$upd['artno'] = $form['artno'];
						$upd['mcode'] = $form['mcode'];
						$upd['is_parent'] = $form['is_parent'];
						$upd['allow_selling_foc'] = $form['allow_selling_foc'];
						$upd['selling_foc'] = $form['selling_foc'];
						$upd['location'] = $form['location'];
						$upd['block_list'] = serialize($form['block_list']);
						$upd['open_price'] = $form['open_price'];
						$upd['active'] = mi($form['active']);
						$upd['non_returnable'] = $form['non_returnable'];
						$upd['output_tax'] = $form['output_tax'];
						$upd['inclusive_tax'] = $form['inclusive_tax'];
						$upd['weight_kg'] = $form['weight_kg'];
						$upd['width'] = $form['width'];
						$upd['height'] = $form['height'];
						$upd['length'] = $form['length'];
						if(isset($form['marketplace_description'])){
							$upd['marketplace_description'] = $form['marketplace_description'];
						}
						
						if($config['sku_bom_additional_type']){
							$upd['bom_type'] = $form['bom_type'];
							if(!$upd['bom_type'])	$upd['bom_type'] = 'normal';
						}
						
						// category discount
						// inherit option
						$upd['cat_disc_inherit'] = trim($form['cat_disc_inherit'][$bom_id]);
						
						// inherit value
						if($upd['cat_disc_inherit']!='set'){
							$form['category_disc_by_branch_inherit'][$bom_id] = array();
						}
						$upd['category_disc_by_branch_inherit'] = serialize($form['category_disc_by_branch_inherit'][$bom_id]);
								
						// category reward point
						// inherit option
						$upd['category_point_inherit'] = trim($form['category_point_inherit'][$bom_id]);
						
						// inherit value
						if($upd['category_point_inherit']!='set'){
							$form['category_point_by_branch_inherit'][$bom_id] = array();
						}
						
						$upd['category_point_by_branch_inherit'] = serialize($form['category_point_by_branch_inherit'][$bom_id]);
						
						$con->sql_query("insert into sku_items ".mysql_insert_by_field($upd));
						$bom_id = $con->sql_nextid();

						if ($form['reason'] != "" && $form['reason'] != $form['log_reason']){
							log_br($sessioninfo['id'], 'MASTERFILE_SKU_ACT', $bom_id, $form['reason']);
						}
					}
	       								
				}			    
			}
			
			if($errm){
				$smarty->assign("errm", $errm);
				load_bom($form);
				$reason = $_REQUEST['reason'];
				$_REQUEST['cat_disc_inherit'] = $_REQUEST['cat_disc_inherit'][$bom_id];
				$_REQUEST['category_point_by_branch_inherit'] = $_REQUEST['category_point_by_branch_inherit'][$bom_id];
				$_REQUEST['category_point_inherit'] = $_REQUEST['category_point_inherit'][$bom_id];
				$_REQUEST['category_disc_by_branch_inherit'] = $_REQUEST['category_disc_by_branch_inherit'][$bom_id];
				$_REQUEST['reason'] = get_reason($_REQUEST['bom_id']);
				$_REQUEST['reason']['log'] = $reason;
				$_REQUEST['sku_type_description'] = $form['sku_type_description'];
				$smarty->assign("form", $_REQUEST);
				$smarty->display("bom.new.tpl");	
			}else{
				//update items
				$q2 = $con->sql_query("select * from tmp_bom_items where bom_id=$form[bom_id] and user_id =$sessioninfo[id] and edit_time=".ms($form['edit_time'])." order by id");	
				while($r2=$con->sql_fetchrow($q2)){
				    $r2['bom_id'] = $bom_id;
				    
					$con->sql_query("insert into bom_items " . mysql_insert_by_field($r2, array('bom_id', 'sku_item_id', 'user_id', 'qty', 'cost_price', 'selling_price')));		
				}
				$con->sql_query("delete from tmp_bom_items where user_id = $sessioninfo[id] and edit_time=".ms($form['edit_time']));
				header("Location: $_SERVER[PHP_SELF]?a=load_bom_details&sku_bom=$form[sku_id]&t=bom_completed&bom_id=$bom_id");
			}
			exit;
		
		case 'load_bom_details':
	    	$form=$_REQUEST;
	    	//echo"<pre>";print_r($form);echo"</pre>";exit;
	    	load_bom($form);
			break;
	
	    case 'load_sku_bom_items':
	    	$form=$_REQUEST;
	    	load_bom($form);
	    	$smarty->display("bom.refresh.sku_bom_items.tpl");
	    	exit;
	    	
	    case 'ajax_add_item_row':
			$form=$_REQUEST;
			//echo"<pre>";print_r($form);echo"</pre>";
			save_bom_items();  	
	
			$duplicate = 0;
			foreach($_REQUEST['sku_code_list'] as $sku_item_id)
			{	    	
				$q1=$con->sql_query("select * from tmp_bom_items where bom_id = ".mi($_REQUEST['id'])." and user_id = ".mi($sessioninfo['id'])." and sku_item_id=$sku_item_id and edit_time=".ms($form['edit_time']));			
			    $r = $con->sql_fetchrow($q1);
				if ($con->sql_numrows($q1)>0) $duplicate++;
			}		
			if ($duplicate > 0 )
			{
				print ($LANG['SKU_ITEM_ALREADY_IN_BOM']);
				exit;
			}	    
			$arr = array();
			$con->sql_query("select count(*) as count from tmp_bom_items where bom_id = ".mi($_REQUEST['id'])." and user_id = ".mi($sessioninfo['id'])." and edit_time=".ms($form['edit_time']));
			$r = $con->sql_fetchrow();
			$count = mi($r['count'])+1;
			$arr = array();
			
			$si_info_list = array();
			foreach($_REQUEST['sku_code_list'] as $sku_item_id){
				$sku_item_id = mi($sku_item_id);
				
				$q1=$con->sql_query("select si.*, si.id as sku_item_id, sku.is_bom
				from sku_items si 
				left join sku on sku.id=si.sku_id
				where si.id=$sku_item_id");
				$r = $con->sql_fetchrow($q1);
				$con->sql_freeresult($q1);
				
				if($r['is_bom']) {
					if ($config['sku_bom_allow_add_normal_bom_sku']) {
						if ($r['bom_type'] != 'normal') {
							print "You cannot add this BOM SKU into BOM Content. Only normal BOM is allowed.";
							exit;
						}
					}
					else {
						print "You cannot add other BOM SKU into BOM Content.";
						exit;
					}
				}
				$si_info_list[] = $r;
			}
			
			if(!$si_info_list){
				print "There is no item to be add.";
				exit;
			}
			foreach($si_info_list as $r)
			{	    	
				$today=date('Y-m-d');			
				$cost=get_latest_cost($sessioninfo['branch_id'], $today, $r['sku_item_code']);
				$r = array_merge($r, $cost);
				$sell_price=get_latest_selling($sessioninfo['branch_id'],$r['sku_item_id'],true);
				$r = array_merge($r, $sell_price);
				//echo"<pre>";print_r($sell_price);echo"</pre>";
				$ret=add_temp_item($r, $form);
			    $smarty->assign("item", $r);
			    
				$rowdata = $smarty->fetch("bom.new.row.tpl");		

	    		$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);		
			}
			
			
			$smarty->assign("form", $form);
				    	
			header('Content-Type: text/xml');
	        print array_to_xml($arr);
	        
	    	exit;	
		case 'ajax_add_item':
			$form=$_REQUEST;
			//echo"<pre>";print_r($form);echo"</pre>";
			save_bom_items();  	
			$q1=$con->sql_query("select sku_items.*, id as sku_item_id from sku_items where id=$form[sku_item_id]");
			$r = $con->sql_fetchrow($q1);
			$today=date('Y-m-d');			
			$cost=get_latest_cost($sessioninfo['branch_id'], $today, $r['sku_item_code']);
			$r = array_merge($r, $cost);
			$sell_price=get_latest_selling($sessioninfo['branch_id'],$r['sku_item_id'],true);
			$r = array_merge($r, $sell_price);
			//echo"<pre>";print_r($sell_price);echo"</pre>";
			$ret=add_temp_item($r, $form);
			if ($ret==-1){
				fail($LANG['SKU_ITEM_ALREADY_IN_BOM']);			
			}						  			
		    $smarty->assign("item", $r);
		    
			$arr = array();
			$rowdata = $smarty->fetch("bom.new.row.tpl");		

	    	$arr[] = array("id" => $r['id'], "rowdata" => $rowdata);
			$smarty->assign("form", $form);
				    	
			header('Content-Type: text/xml');
	        print array_to_xml($arr);
			exit;
			
		case 'print_bom':
			print_bom();
			exit;
			
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
					
	}
}

$smarty->assign("form", $form);
$smarty->display("bom.new.tpl");
exit;

function load_bom(&$form){
	global $con, $con, $smarty, $sessioninfo, $appCore;
	
	$bom_id=intval($form['bom_id']);
	$sku_id=intval($form['sku_bom']);
	$user_id=$sessioninfo['id'];
	$gst_status = check_gst_status();
	if($form['edit_time']){
		$edit_time = $form['edit_time'];
	}
	else{		
		$edit_time = $appCore->generateEditTime();
	}
	
	//pick up sku type description to show
	$q1 = $con->sql_query("select sku_type.description as sku_type_description from sku left join sku_type on sku_type.code=sku.sku_type where id=$sku_id");
	$r1 = $con->sql_fetchrow($q1);
	$sku_type_description = $r1['sku_type_description'];
	$con->sql_freeresult($q1);
	
	//print_r($form);
	if(!$form['edit_time']){
		if($bom_id>0){
			$con->sql_query("select *, sku_id as sku_bom, si.id as bom_id, sku.brand_id, sku.category_id, si.active, si.block_list
	from sku_items si	
	left join sku on sku.id=si.sku_id 
	where si.sku_id=$sku_id and si.id=$bom_id");
			$form=$con->sql_fetchrow();
			if (!$form['active']) $form['reason'] = get_reason($form['bom_id']);
			
			$form['block_list'] = unserialize($form['block_list']);
			$form['category_disc_by_branch_inherit'] = unserialize($form['category_disc_by_branch_inherit']);
			$form['category_point_by_branch_inherit'] = unserialize($form['category_point_by_branch_inherit']);
			//$smarty->assign("form",$form);		
			$con->sql_query("delete from tmp_bom_items where user_id = $sessioninfo[id] and edit_time=".ms($edit_time));
			
			$q2 = $con->sql_query("select * from bom_items where bom_id=$bom_id order by id");	
			while($r2=$con->sql_fetchrow($q2)){				
				$r2['user_id'] = $sessioninfo['id'];
				$r2['bom_id'] = $bom_id;
				$r2['edit_time'] = $edit_time;
				
				$con->sql_query("insert into tmp_bom_items " . mysql_insert_by_field($r2, array('bom_id','sku_item_id', 'user_id', 'qty', 'cost_price', 'selling_price', 'edit_time')));
			}	

			$q3=$con->sql_query("select id from grn_items where sku_item_id=$bom_id limit 1");		
			$r3=$con->sql_fetchrow($q3);
			if($r3){
				$disabled_edit=1;
			}
			else{		
				$q4=$con->sql_query("select id from adjustment_items where sku_item_id=$bom_id limit 1");		
				$r4=$con->sql_fetchrow($q4);
				if($r4) $disabled_edit=1;				
			}
			if($disabled_edit) $form['disabled_edit']=1;
			//echo"<pre>";print_r($form);echo"</pre>";
		}else{
			if(!$form['bom_type']) $form['bom_type'] = "normal";
			$form['active'] =1;
			$form['disabled_edit']=0;
			if($gst_status){
				if($config['masterfile_bom_default_output_tax']) $output_tax_code = $config['masterfile_bom_default_output_tax'];
				else $output_tax_code = "SR";
				
				$q1 = $con->sql_query("select * from gst where code = ".ms($output_tax_code)." and type = 'supply'");
				$gst_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
		
				$form['output_tax'] = $gst_info['id'];
				$form['inclusive_tax'] = "inherit";
			}
		}
	}
	$form['edit_time'] = $edit_time;
	$form['sku_type_description'] = $sku_type_description;
	$q5=$con->sql_query("select si1.sku_item_code, si1.artno, si1.mcode, si1.description, si1.doc_allow_decimal,tbi.sku_item_id, tbi.id as id, tbi.qty as qty, tbi.cost_price as saved_cost, tbi.selling_price as saved_selling 
from tmp_bom_items tbi
left join sku_items si1 on si1.id=sku_item_id
where bom_id=$bom_id and user_id=$sessioninfo[id] and edit_time=".ms($edit_time)." order by tbi.id");
	while($r5=$con->sql_fetchrow($q5)){
		//if(!$disabled_edit){
		$today=date('Y-m-d');			
		$cost=get_latest_cost($sessioninfo['branch_id'], $today, $r5['sku_item_code']);
		$r5 = array_merge($r5, $cost);
		$sell_price=get_latest_selling($sessioninfo['branch_id'],$r5['sku_item_id'],true);
		$r5 = array_merge($r5, $sell_price);		
		//}	
		$bom_items[]=$r5;			
	}
	//print_r($form);
	$smarty->assign("bom_items",$bom_items);	
	load_arms_code($sku_id);
}

function get_latest_selling($bid, $sid, $is_add){
	global $con, $con, $smarty;
	$total_sell=0;
	$where='';
	$is_hq=1;
	
	if(BRANCH_CODE=='HQ'){
		$q0=$con->sql_query("select id from branch where active = 1 order by sequence, code");
		while($r0=$con->sql_fetchrow($q0)){
			$temp_id[$r0['id']]=$r0['id'];	
		}
		$where = " branch.id in (" . join(",", array_keys($temp_id)) . ")";
	}
	else{
		$where=" branch.id=$bid ";
		$is_hq=0;	
	}

	$q1=$con->sql_query("select if(sp.price is null, selling_price, sp.price) as latest_selling, branch.id as branch_id, sku_item_id
from sku_items 
left join sku on sku.id=sku_items.sku_id 
left join branch on $where
left join sku_items_price sp on sku_items.id = sp.sku_item_id and branch.id=sp.branch_id
where sku_items.id=$sid group by branch.id order by branch.sequence, branch.code");
	while($r1 = $con->sql_fetchrow($q1)){
		if($is_add){
			if($is_hq) $total_sell+=$r1['latest_selling'];	
			else $return['latest_selling']=$r1['latest_selling'];			
		}
		else $temp[$r1['branch_id']]=$r1['latest_selling'];						
	}
	//echo"<pre>";print_r($temp);echo"</pre>";
	if($is_add){
		if($is_hq){
			$no_results=$con->sql_numrows($q1);
			if($no_results)	$return['latest_selling']=round($total_sell/$no_results,2);		
		}
		return $return;
	}
	else{
		foreach($temp as $k=>$v){
			$q2=$con->sql_query("select report_prefix as branch from branch where id=$k");
			$r2 = $con->sql_fetchrow($q2);
			$price[$k]['latest_selling']=$v;
			$price[$k]['branch']=$r2['branch'];						
		}
		$smarty->assign("price",$price);
	}
}


function get_latest_cost($bid,$date,$sic){
	global $con;
	$where='';
	
	if(BRANCH_CODE!='HQ') $where=" grn_items.branch_id =$bid and ";
	
	//FROM GRN
	$con->sql_query("select round(if (grn_items.acc_cost is null,grn_items.cost,grn_items.acc_cost)/uom.fraction,3) as latest_cost
from grn_items
left join uom on uom_id = uom.id
left join sku_items on sku_item_id = sku_items.id
left join grn on grn_id = grn.id and grn_items.branch_id = grn.branch_id
left join grr on grr_id = grr.id and grn.branch_id = grr.branch_id
where $where grn.approved=1 and sku_item_code=".ms($sic)." and grr.rcv_date <= '$date' 
having latest_cost > 0
order by grr.rcv_date desc limit 1");
	$c = $con->sql_fetchrow();

	if(BRANCH_CODE!='HQ') $where=" po_items.branch_id =$bid and ";	
	//FROM PO
	if(!$c){
	 	$con->sql_query("select round(po_items.order_price/po_items.order_uom_fraction,3) as latest_cost
from po_items 
left join sku_items on sku_item_id = sku_items.id 
left join po on po_id = po.id and po.branch_id = po_items.branch_id 
where $where po.active=1 and po.approved=1 and sku_item_code=".ms($sic)." and po.po_date <= '$date' 
having latest_cost > 0
order by po.po_date desc limit 1");
 		$c = $con->sql_fetchrow();		
	}
	
	//FROM MASTER SKU
	if(!$c){
	 	$con->sql_query("select cost_price as latest_cost from sku_items where sku_item_code=".ms($sic));
		$c = $con->sql_fetchrow();	
	}
	return $c;
}

function add_temp_item(&$r, $form){
	global $con, $smarty, $sessioninfo, $branch_id;	
	
	$r['bom_id']=mi($_REQUEST['bom_id']);
	$r['user_id']=mi($sessioninfo['id']);
	$r['sku_item_id']=mi($r['sku_item_id']);
	$r['edit_time']=mi($form['edit_time']);

	$con->sql_query("select id from tmp_bom_items where bom_id = $r[bom_id] and sku_item_id= $r[sku_item_id] and user_id = $r[user_id] and edit_time=".ms($r['edit_time']));
	if ($con->sql_numrows() > 0){
	    return -1;
	}
	
    $con->sql_query("insert into tmp_bom_items " . mysql_insert_by_field($r, array('bom_id', 'user_id', 'sku_item_id', 'edit_time')));
 	$r['id'] = $con->sql_nextid();
 	return $r['id'];
}

function save_bom_items(){
	global $con, $smarty, $sessioninfo;
	$form=$_REQUEST;
	if($form['row_id']){
		foreach($form['row_id'] as $k=>$v){
			$update = array();
			$update['qty'] = $form['qty'][$k];
			if($form['a']=='implode'){
				$update['cost_price'] = $form['latest_cost'][$k];
				$update['selling_price'] = $form['latest_selling'][$k];
			}
					    
			$con->sql_query("update tmp_bom_items set " . mysql_update_by_field($update) . " where id=$k");	
		}	
	}
}


function validate_data(&$form){
	global $con, $LANG, $sessioninfo, $smarty, $config;

	$form['sku_id'] = intval($_REQUEST['sku_bom']);
	if ($form['sku_id'] == 0)
		$err['top'][] = $LANG['SKU_NOT_EXIST'];		
	
	if (!$form['description'])
		$err['top'][] = $LANG['SKU_INVALID_DESCRIPTION'];	
	
	if (!$form['receipt_description']){
		$err['top'][] = $LANG['SKU_INVALID_RECEIPT_DESCRIPTION'];
	}else{
		$ret_desc = check_receipt_desc_max_length($form['receipt_description']);
		if($ret_desc["err"]){
			$err['top'][] = $ret_desc["err"];
		}
	}
	/*
	if($config['sku_bom_show_mcode']){
		if (!$form['artno'] || !$form['mcode'])
			$artno_mcode_error=1;
	}
	else{
		if (!$form['artno'])
			$artno_mcode_error=1;
	}
	
	if($artno_mcode_error)
		$err['top'][] = $LANG['SKU_INVALID_ART_MCODE'];
	*/
	
	// check config of mcode 
	if($config['sku_bom_show_mcode']){
		if (!$config['sku_application_artno_allow_duplicate'] && $form['mcode'] != ''){
			$sku_rid=$con->sql_query("select concat('SKU ',sku_id) as id from sku_items where sku_id <> ".mi($form['sku_bom'])." and mcode = ".ms($form['mcode']));

			if($con->sql_numrows($sku_rid) > 0 ){
				$sku=$con->sql_fetchassoc($sku_rid);
				$err['top'][] = sprintf($LANG['SKU_MCODE_USED'],$form['mcode'],"in existing SKU. ($sku[id])");
			}else{ // check from sku_apply_items
				$sai_rid = $con->sql_query("select concat('SKU ',sku_id) as id from sku_apply_items sai 
										  left join sku on sai.sku_id = sku.id
										  where sai.is_new and (sku.status <> 4 and sku.active=0) and sai.sku_id <> ".mi($form['sku_bom'])." and (sai.mcode = ".ms($form['mcode'])." or sai.product_matrix like ".ms('%:"'.replace_special_char($form['mcode']).'";%').")");
				
				if($con->sql_numrows($sai_rid) > 0 ){
					$sku=$con->sql_fetchassoc($sai_rid);
					$err['top'][] = sprintf($LANG['SKU_MCODE_USED'],$form['mcode'],"in existing SKU. ($sku[id])");
				}
				$con->sql_freeresult($sai_rid);
			}
			$con->sql_freeresult($sku_rid);
		}
		
		if($form['mcode'] != ''){
			if (preg_match('/^[0-9]+$/',$form['mcode']) && in_array(strlen($form['mcode']), array(5,6,8,12,13))){
				// ok no problem
			}
			elseif (isset($config['sku_application_valid_mcode']) && preg_match($config['sku_application_valid_mcode'],$form['mcode'])){
				// ok no problem too
			}
			elseif($config['sku_artno_allow_specialchars']&& in_array(strlen($form['mcode']), array(5,6,8,12,13))){
				// ok no problem too
			}
			else{
				$err['top'][] = sprintf($LANG['SKU_MCODE_INVALID_FORMAT'], $form['mcode']);
			}
		}
	}
	
	$form['selling_foc'] = $form['allow_selling_foc'] ? mi($form['selling_foc']) : 0;
	if($form['selling_price'] <= 0 && $form['allow_selling_foc'])	$form['selling_foc'] = 1;
	
	if($form['selling_price'] <= 0 && !$form['open_price'] && !$form['allow_selling_foc']) $err['top'][] = $LANG['SKU_INVALID_SELLING_PRICE'];	
	
	if($form['cost_price']==0) $err['top'][] = $LANG['SKU_INVALID_COST_PRICE'];	
	
	if(!$form['active'] && !trim($form['reason'])) $err['top'][] = $LANG['SKU_EMPTY_REJECT_REASON'];
	/*
	if ($form['selling_price'] < $form['cost_price'])
		$err['top'][] = $LANG['SKU_SELLING_BELOW_COST'];
	*/
		
	/*if ($form['misc_cost']==0)
		$err['top'][] = $LANG['SKU_INVALID_MISC_COST'];
	*/
	if(!$form['qty']){
		$err['item'][] = $LANG['SKU_BOM_NO_ITEMS'];	
	}
	
	if(isset($form['qty'])){
		foreach ($form['qty'] as $k=>$v){
			if(!$v){
				$sid=$form['row_id'][$k];
				$q1=$con->sql_query("select sku_item_code from sku_items where id=$sid");
				$r1=$con->sql_fetchrow($q1);
				$err['item'][] = sprintf($LANG['SKU_BOM_ITEMS_INVALID_QTY'], $r1[0]);
			}
		}
	}


	return $err;
}

function init_selection(){
	global $con, $sessioninfo, $smarty, $config;

	// show department option
	$q1=$con->sql_query("select sku.id, brand.description as brand, cat.description as category, sku_items.id as sid
from sku 
left join sku_items on sku.id=sku_items.id 
left join brand on brand.id=brand_id 
left join category cat on cat.id=category_id 
where is_bom order by id desc");
	$counter=1;
	while($r1=$con->sql_fetchrow($q1)){
		if($counter==1){
			load_arms_code($r1['id']);
		}
		$sku_bom[]=$r1;
		$counter++;
	}
	
	$sku_id=intval($_REQUEST['sku_bom']);
	if($sku_id){
		$q2=$con->sql_query("select brand.description as brand, cat.description as category
from sku 
left join brand on brand.id=sku.brand_id 
left join category cat on cat.id=sku.category_id 
where is_bom and sku.id=$sku_id");
		$r2=$con->sql_fetchrow($q2);
		$default_description=$r2['category']." ".$r2['brand']." HAMPER";					
	}
	$smarty->assign("default_description",$default_description);	
	$smarty->assign("sku_bom",$sku_bom);
	
	$discount_inherit_options = array('inherit'=>'Inherit (Follow Category)', 'none'=>'No Discount', 'set'=>'Override Discount');
	$smarty->assign('discount_inherit_options', $discount_inherit_options);

	$category_point_inherit_options = array('inherit'=>'Inherit (Follow Category)', 'none'=>'No Point', 'set'=>'Override Reward Point');
	$smarty->assign('category_point_inherit_options', $category_point_inherit_options);
	load_branch_list();
	
	if($config['enable_gst']){
		$gst_status = check_gst_status();
		// load gst stuff
		$q1 = $con->sql_query("select * from gst where type = 'supply' and active=1");
		
		$output_tax_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			$output_tax_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		
		$smarty->assign("output_tax_list", $output_tax_list);
		
		$q1 = $con->sql_query("select * from gst_settings where setting_name = 'inclusive_tax'");
		$gst_settings = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$smarty->assign("mst_inclusive_tax", $gst_settings['setting_value']);
		$smarty->assign("gst_status", $gst_status);
	}
}

function load_arms_code($sku_id){
	global $con, $sessioninfo, $smarty;
		
	$q2=$con->sql_query("select sku_items.* from sku_items
left join sku on sku.id=sku_id
where sku.id=$sku_id 
order by sku_items.id desc 
");
	$bom=$con->sql_fetchrowset($q2);
	//echo"<pre>";print_r($bom);echo"</pre>";
	$smarty->assign("bom", $bom);
}

function print_bom()
{
	global $config, $con, $smarty, $sessioninfo;

 		$bid = $sessioninfo['branch_id'];
	
	$tpl = isset($config['bom_alt_print_template']) ? $config['bom_alt_print_template'] : 'bom.print.tpl';
	
	$con->sql_query("select * from branch where code=".ms(BRANCH_CODE));
	$smarty->assign("branch",$con->sql_fetchrow());
	
	$con->sql_query("select *, if (sp.price is null,sku_items.selling_price,sp.price) as selling_price from sku_items 
		left join sku_items_price as sp on id = sp.sku_item_id and sp.branch_id = $bid
		where id = ".mi($_REQUEST['bom_id']));
	$smarty->assign("form",$con->sql_fetchrow());

	$con->sql_query("select *, if (sp.price is null,sku_items.selling_price,sp.price) as price 
		from bom_items 
		left join sku_items on sku_item_id = sku_items.id 
		left join sku_items_price as sp on bom_items.sku_item_id = sp.sku_item_id and sp.branch_id = $bid
		where bom_id=".mi($_REQUEST['bom_id'])."
		order by bom_items.id");
	$smarty->assign("bom_items",$con->sql_fetchrowset());
	 //. mysql_insert_by_field($r0, array('bom_id', 'sku_item_id', 'user_id', 'qty', 'cost_price', 'selling_price')));
	//print_r($_REQUEST);
	$smarty->display($tpl);	
}

function get_reason($id){
	global $con;

	$rs10 = $con->sql_query("select log.*, user.u from log left join user on user.id = log.user_id where rid = ".mi($id)." and type = 'MASTERFILE_SKU_ACT' order by timestamp desc limit 1");

	$r = $con->sql_fetchrow($rs10);

	return $r;
}
?>
