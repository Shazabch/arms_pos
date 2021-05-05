<?
/* 
REVISION HISTORY
================
10/2/2007 11:00:17 AM yinsee
- set lastupdate to CURRENT_TIMESTAMP when price changed (to trigger sku export for ATP)

12/12/2007 1:57:54 PM gary
- when get last cost for grn (in get_item_detail), only take from cost > 0

3/7/2008 4:10:26 PM yinsee
- fix price change popup bug

05/20/2008 11:15:50 AM - yinsee 
- only use discount code with percentage

1/5/2009 10:10:00 AM yinsee
- add qprice (multi qty price)

1/12/2009 5:01:11 PM yinsee
- show individual sku items if is-bom

2/2/2009 2:46:12 PM yinsee
- forgot to save user-id when price change 

3/2/2009 6:26:00 PM jeff
- filter in-active sku_items

23/2/2009 5:14:00 PM Andy
- add artno to show under arms code

1/28/2011 5:08:34 PM Alex
- add show qprice history

4/4/2011 2:18:43 PM Justin
- added the checking for trade discount code that cannot be 0 rate and it is only applied to all non consigment customers.
- added new feature to load default trade discount code when no list found from either vendor or brand table.

4/5/2011 12:27:28 PM Justin
- fixed the wrong checking for trade discount rate while login as sub branch. 

4/13/2011 12:11:27 PM Justin
- fixed the trade discount code that can allow to save while PWP with 0% rate.

5/6/2011 2:51:12 PM Justin
- Fixed the price type not update sku_items_price table.

5/20/2011 2:48:08 PM Andy
- Add region price type and selling price, included mprice.

6/24/2011 4:53:26 PM Andy
- Make all branch default sort by sequence, code.

9/15/2011 11:42:31 AM Andy
- Show mouse over branch description.

9/27/2011 4:28:46 PM Andy
- Change when update price if found no price type and got config "sku_always_show_trade_discount", will update it to master price type.

9/28/2011 10:29:16 AM Andy
- Fix trade discount dropdown cannot load default price type list if no trade discount rate is set.

10/24/2011 6:06:32 PM Andy
- Add checking to only show FOC checkbox if the SKU got allow FOC in masterfile.

10/25/2011 6:18:02 PM Andy
- Add show all price type when consignment mode.

2/27/2012 5:37:15 PM Andy
- Fix change region price bugs. 

3/27/2012 12:18:16 PM Andy
- Add show in-active branch if they got checked "inactive_change_price" in branch masterfile.
- Add can click on region title to change price by branch in region.

8/15/2012 11:29 AM Andy
- Add to get sku items uom and fraction.

8/15/2012 2:22 PM Andy
- Fix sql query bugs.

10/8/2012 1:58:00 PM Fithri
- SKU change price block bom type "package"

1/23/2013 5:28 PM Justin
- Enhanced to pickup additional selling price.

2/1/2013 11:55 AM Fithri
- add checkbox to enable update price
- tick to enable change price
- have a checkbox to tick whole row or column
- click into region change price, show a region price and its related control
- if change region price, all branches in region price will change

3/13/2013 3:12 PM Justin
- Bug fixed on capturing empty user ID for logs while update price.

3/15/2013 10:15 AM Fithri
- fix the column messed up & all merge together if too many brnach
- fix "check all" checkbox if dont have member type

5/16/2013 11:12 AM Fithri
- fix error updating trade discount even when the branch is not ticked to update

6/6/2013 2:06 PM Justin
- Enhanced to load/save multiple quantity by multiple price.

7/16/2014 1:17 PM Justin
- Enhanced to pickup master and latest cost for calculate GP purpose.

9/25/2014 2:39 PM Justin
- Enhanced to have GST calculation.

10/29/2014 9:46 AM Justin
- Enhanced to move the retrieve gst info function to functions.php
- Enhanced to add config checking while loading gst list.

2/17/2015 10:20 AM Justin
- Enhanced to simplify the process while taking GST info.

3/5/2015 11:54 AM Andy
- Fix check_gst_status to use 'check_only_need_active'.

3/6/2015 3:27 PM Andy
- Enhanced the modules to check when get sku/category gst, no need to check force zero rate.

3/11/2015 5:37 PM Andy
- Enhanced to store the checkbox FOC value into database.
- Enhanced to load FOC value from table.

4/3/2015 11:34 AM Justin
- Bug fixed on always display 100% GP when change price at sub branch.

13/2/2017 10:13 AM Zhi Kai
- Change the title of 'SKU Items Selling Price' to 'Change Selling Price'

11/17/2017 4:31 PM Justin
- Enhanced to have Scan barcode feature and allow user to choose an item when matches more than 1 result.

11/9/2018 10:38 AM Justin
- Enhanced to highlight the item row when it is called from SKU Change Price.

12/3/2019 9:00 AM William
- Fixed bug Change Selling Price multiple quantity price unable to delete.

12/4/2019 2:17 PM William
- Added checking for Change Selling Price "Selling Price" when change branch and update, system will show alert warning. 

11/13/2020 4:15 PM Andy
- Added "Recommended Selling Price" (RSP) feature.

*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if ($_REQUEST['a']!='history' && !privilege('MST_SKU_UPDATE_PRICE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_SKU_UPDATE_PRICE', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "Change Selling Price");

$maintenance->check(249);

// branch
$con->sql_query("select id, code, description from branch where (active=1 or (active=0 and inactive_change_price=1)) order by sequence,code");
while($r = $con->sql_fetchassoc()){
	$branches[$r['id']] = $r;
}
$con->sql_freeresult();
$smarty->assign("branch", $branches);

$con->sql_query("select code from trade_discount_type order by code");
$default_tdt = $con->sql_fetchrowset();

// gst settings
$gst_settings = check_gst_status(array('check_only_need_active'=>1));
$smarty->assign("gst_settings", $gst_settings);

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
		case 'history':
			// show cost change history for item/branch
			$id = intval($_REQUEST['id']);
			$branch_id = intval($_REQUEST['branch_id']);

			if ($_REQUEST['type']!='' && isset($config['sku_multiple_selling_price']))
			{
			    if ($_REQUEST['type'] == 'qprice'){
			        //qprice only
					$con->sql_query("select sku_items_qprice_history.*, user.u as user from sku_items_qprice_history left join user on user_id = user.id where sku_item_id = $id and branch_id = $branch_id order by added desc,min_qty") or die(mysql_error());
					while($r=$con->sql_fetchrow()){
					
					    if ($history[$r['added']]){
					        //same timestamp
					        $history[$r['added']][$r['min_qty']][$r['price']]['user']=$r['user'];
						}else{
							//different timestammp
						    if ($last_time){
							    $different=strtotime($last_time)-strtotime($r['added']);
							    
							    if ($different >=0 && $different <=10){
									//save new timestamp interval 10 seconds only
									$history[$last_time][$r['min_qty']][$r['price']]['user']=$r['user'];
							    }else{
                                    $history[$r['added']][$r['min_qty']][$r['price']]['user']=$r['user'];
                                    $last_time=$r['added'];
								}
							}else{
								$history[$r['added']][$r['min_qty']][$r['price']]['user']=$r['user'];
								$last_time=$r['added'];
							}
						}
						
						$history[$r['added']][$r['min_qty']][$r['price']]['sku_item_id'] = $r['sku_item_id'];
						$history[$r['added']][$r['min_qty']][$r['price']]['fp_id'] = $r['fp_id'];
						$history[$r['added']][$r['min_qty']][$r['price']]['fpi_id'] = $r['fpi_id'];
						$history[$r['added']][$r['min_qty']][$r['price']]['fp_branch_id'] = $r['fp_branch_id'];
					}

					$smarty->assign("qprice", 1);
				}else{
					//mprice only
	   				$type = ms($_REQUEST['type']);
					$con->sql_query("select sku_items_mprice_history.*, user.u as user from sku_items_mprice_history left join user on user_id = user.id where sku_item_id = $id and branch_id = $branch_id and type = $type order by added desc") or die(mysql_error());
                    $history=$con->sql_fetchrowset();
				}
			}else{
				$con->sql_query("select sku_items_price_history.*, user.u as user from sku_items_price_history left join user on user_id = user.id where sku_item_id = $id and branch_id = $branch_id order by added desc");
				
				$history=$con->sql_fetchrowset();
			}
			$smarty->assign("history", $history);
			$smarty->display("masterfile_sku_items_price.history.tpl");
			exit;
			
		case 'change_price':
			change_price();
			//do_find();
			break;
		
		case 'change_qprice':
			change_qprice();
			do_find();
			break;
			
		case 'find':
			do_find();
			break;
		case 'ajax_show_region_branch_price_type_selling':
			ajax_show_region_branch_price_type_selling();
			exit;
		case 'ajax_get_region_price_history':
			ajax_get_region_price_history();
			exit;
		case 'ajax_search_barcode_item':
			ajax_search_barcode_item();
			exit;
		default:
			print "<h1>Unhandled Request</h1>";
			print_r($_REQUEST);
			exit;
	}
}
$have_changes = false;
$smarty->display("masterfile_sku_items_price.tpl");
exit;

function load_price_type_list(){
	global $con, $smarty;
	
	$con->sql_query("select * from trade_discount_type order by code");
	$price_type_list = $con->sql_fetchrowset();
	$con->sql_freeresult();
	
	$smarty->assign('price_type_list', $price_type_list);
	return $price_type_list;
}

function do_find()
{
	global $con, $smarty, $config, $branches, $default_tdt;

	$sku_item_id = mi($_REQUEST['sku_item_id']);
	$show_by_region_code = trim($_REQUEST['show_by_region_code']);
	
	$con->sql_query("select department_id, vendor_id, brand_id, default_trade_discount_code, trade_discount_type, is_bom, 
					sku.category_id, sku.mst_output_tax, sku.mst_inclusive_tax
					from sku left 
					join category on category_id = category.id 
					where sku.id = (select sku_id from sku_items where sku_items.id=$sku_item_id)");
	$tdtype = $con->sql_fetchrow();
	
	$smarty->assign("default_trade_discount_code", $tdtype['default_trade_discount_code']);
	$smarty->assign("trade_discount_type", $tdtype['trade_discount_type']);
	
	//if($config['sku_use_region_price']){
		$price_type_list = load_price_type_list();
	//}
	
	$is_bom = $tdtype['is_bom'];
	if (!$is_bom)
		$seltype = "si.sku_id in (select sku_id from sku_items where id=$sku_item_id)";
	else
		$seltype = "si.id=$sku_item_id";
	
	$deptid = mi($tdtype['department_id']);
	
	if($config['masterfile_branch_enable_additional_sp']){
		// load branch additional selling price
		$q1 = $con->sql_query("select * from branch_additional_sp");
		
		while($r = $con->sql_fetchassoc($q1)){
			$branch_asp_list[$r['branch_id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("branch_asp_list", $branch_asp_list);
		
		$q1 = $con->sql_query("select * from branch_region_additional_sp");
		
		while($r = $con->sql_fetchassoc($q1)){
			$region_asp_list[$r['region_code']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("region_asp_list", $region_asp_list);
	}
	
	if($config['enable_gst']){
		// get GST info
		$sku_list = $output_tax_list = array();
		$cat_output_tax = get_category_gst("output_tax", $tdtype['category_id'], array('no_check_use_zero_rate'=>1));
		$cat_inclusive_tax = get_category_gst("inclusive_tax", $tdtype['category_id']);
		
		// load output tax list
		$q1 = $con->sql_query("select * from gst where active=1 and type = 'supply'");

		while($r = $con->sql_fetchassoc($q1)){
			$output_tax_list[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
	}
	
	if (BRANCH_CODE != 'HQ')
	{
		$branch_id = get_request_branch();
			
		if ($tdtype['trade_discount_type']==1)
		{
			/// use brand table
			$con->sql_query("select * from brand_commission where department_id=$deptid and branch_id = $branch_id and brand_id = ".mi($tdtype['brand_id'])) or die(mysql_error());
		}
		elseif ($tdtype['trade_discount_type']==2)
		{
			// use vendor table
			$con->sql_query("select * from vendor_commission where department_id=$deptid and branch_id = $branch_id and vendor_id = ".mi($tdtype['vendor_id'])) or die(mysql_error());
		}
		
		if($tdtype['trade_discount_type'] > 0 && $con->sql_numrows() == 0){ // check if found no trade discount, load default trade discount
			// use trade discount type table
			$con->sql_query("select code as skutype_code, 0 as rate from trade_discount_type order by code");
		}

		while($r=$con->sql_fetchrow())
		{
			//if ($r['rate']<=0) continue;
			$disc[$r['skutype_code']] = $r['rate'];
		}

		// if found no discount code for branch, load default
		if(!$disc){
			foreach($default_tdt as $arr1=>$tdt_list){ // set the branch to load default discount code
				$disc[$branch_id][$default_tdt[$arr1]['code']] = 0;
			}
		}

		$smarty->assign("discount_codes", $disc);
	
		$q1 = $con->sql_query("select si.id, si.sku_item_code, si.mcode, si.description, sip.trade_discount_code, si.active,
							 if (sip.price is null, si.selling_price, sip.price) as price, si.allow_selling_foc,
							 u.fraction as packing_uom_fraction, si.packing_uom_id, si.bom_type, si.output_tax, si.inclusive_tax,
							 if(sip.price is null, si.selling_foc, sip.selling_price_foc) as selling_price_foc,
							 si.use_rsp, si.rsp_price, if(sip.price is null, si.rsp_discount, sip.rsp_discount) as rsp_discount
							 from sku_items si
							 left join uom u on u.id = si.packing_uom_id
							 left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = $branch_id
							 where $seltype
							 order by si.sku_item_code");

		while($r = $con->sql_fetchassoc($q1)){
			// get multi selling price
			if (isset($config['sku_multiple_selling_price'])){
				$q2 = $con->sql_query("select type, price from sku_items_mprice where branch_id = ".mi($branch_id)." and sku_item_id=".mi($r['id']));
				while($r1=$con->sql_fetchassoc($q2)){
					$r['mprice'][$r1['type']]= $r1['price'];
				}
				$con->sql_freeresult($q2);
			}
			
			// get qprice
			if (isset($config['sku_multiple_quantity_price'])){
				// normal qprice
				$q2 = $con->sql_query("select min_qty, price from sku_items_qprice where branch_id = ".mi($branch_id)." and sku_item_id=".mi($r['id'])." order by min_qty");
				
				while($r1=$con->sql_fetchassoc($q2)){
					$r['min_qty'][$r1['min_qty']] = $r1['price'];
				}
				$con->sql_freeresult($q2);
				
				if(isset($config['sku_multiple_quantity_price'])){
					// qprice by member type
					$q2 = $con->sql_query("select * from sku_items_mqprice where sku_item_id = ".mi($r['id'])." and branch_id = ".mi($branch_id));

					while($r1=$con->sql_fetchassoc($q2)){
						//$items[$k]['min_qty'][$p['min_qty']] = 1;
						$r['mqprice'][$r1['min_qty']][$r1['type']][$r1['branch_id']] = $r1;
					}
					$con->sql_freeresult($q2);
				}
			}
			
			if ($is_bom && $r['bom_type'] == 'package') $r['is_bom_package'] = true;
			else $r['is_bom_package'] = false;
			
			// get latest cost
			$q2 = $con->sql_query("select * from sku_items_cost where sku_item_id = ".mi($r['id'])." and branch_id = ".mi($branch_id));
			
			while($tmp = $con->sql_fetchassoc($q2)){
				$r['cost'][$tmp['branch_id']] = $tmp['grn_cost'];
				$r['stock_bal'][$tmp['branch_id']] = $tmp['qty'];
			}
			$con->sql_freeresult($q2);

			if($config['enable_gst']){			
				$output_tax_info = get_sku_gst("output_tax", $r['id'], array('no_check_use_zero_rate'=>1));
				$r['gst_rate'] = $output_tax_info['rate'];
				$r['inclusive_tax'] = get_sku_gst("inclusive_tax", $r['id']);
			}
			
			$items[] = $r;
		}

		/*echo '<pre>';
		print_r($items);
		echo '</pre>';*/
		//print_r($items);
		$smarty->assign("items",$items);				
	}
	else	// IT IS HQ MODE
	{
		// get those branch hv no region
		if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
			$region_branch = load_region_branch_array(array('inactive_change_price'=>1));
		}
		
	    if($config['sku_always_show_trade_discount']){
			/*$con->sql_query("select tdt.*,tdt.code as skutype_code,branch.id as branch_id,btd.value as rate from trade_discount_type tdt join branch left join branch_trade_discount btd on btd.branch_id=branch.id and btd.trade_discount_id=tdt.id") or die(mysql_error());*/
			$con->sql_query("select tdt.*,tdt.code as skutype_code,branch.id as branch_id,btd.value as rate 
				from branch_trade_discount btd
				left join trade_discount_type tdt on btd.trade_discount_id=tdt.id 
				left join branch on branch.id=btd.branch_id");
		}elseif ($tdtype['trade_discount_type']==1)
		{
			/// use brand table
			$con->sql_query("select * from brand_commission where department_id=$deptid and brand_id = ".mi($tdtype['brand_id'])) or die(mysql_error());
		}
		elseif ($tdtype['trade_discount_type']==2)
		{
			// use vendor table
			$con->sql_query("select * from vendor_commission where department_id=$deptid and vendor_id = ".mi($tdtype['vendor_id'])) or die(mysql_error());
		}

		while($r=$con->sql_fetchrow())
		{
			//if ($r['rate']<=0) continue;
			$disc[$r['branch_id']][$r['skutype_code']] = $r['rate'];
		}

		// do checking for those branches that does not having any discount code
		//print_r($branches);
		foreach($branches as $arr=>$b){
			if(!$disc[$b['id']]){ // if found no discount code for branch, load default
				foreach($default_tdt as $arr1=>$tdt_list){ // set the branch to load default discount code
					$disc[$branches[$arr]['id']][$default_tdt[$arr1]['code']] = 0;
				}
			}
			
			if($config['consignment_modules']){	// consignment mode must show all price type
				foreach($default_tdt as $arr1=>$tdt_list){
					if(!isset($disc[$b['id']][$tdt_list['code']])){
						$disc[$b['id']][$tdt_list['code']] = 0;
					}
				}
			}
		}

		//print_r($disc[1]);
		$smarty->assign("discount_codes", $disc);
		
		$rs1 = $con->sql_query("select si.id, si.sku_item_code, si.artno, si.mcode, si.description, si.selling_price, si.active,
								si.allow_selling_foc, u.fraction as packing_uom_fraction, si.packing_uom_id, si.bom_type, si.cost_price,
								si.output_tax, si.inclusive_tax, si.selling_foc as default_selling_price_foc,
								si.use_rsp, si.rsp_price, si.rsp_discount
								from sku_items si
								left join uom u on u.id = si.packing_uom_id
								where $seltype
								order by si.sku_item_code");
			
		while($r=$con->sql_fetchassoc($rs1)){
			if ($is_bom && $r['bom_type'] == 'package') $r['is_bom_package'] = true;
			else $r['is_bom_package'] = false;
			
			$rs2 = $con->sql_query("select branch_id,price,trade_discount_code,selling_price_foc,rsp_discount from sku_items_price where sku_item_id=$r[id]");
			while($p=$con->sql_fetchrow($rs2))
			{
				$r['price'][$p['branch_id']] = $p['price'];
				$r['trade_discount_code'][$p['branch_id']] = $p['trade_discount_code'];
				$r['selling_price_foc'][$p['branch_id']] = $p['selling_price_foc'];
				$r['branch_rsp_discount'][$p['branch_id']] = $p['rsp_discount'];
			}

			if (isset($config['sku_multiple_selling_price']))
			{
				$rs3 = $con->sql_query("select sim.branch_id, sim.type, sim.price
										from sku_items_mprice sim
										where sim.sku_item_id=$r[id]");
				while($p=$con->sql_fetchrow($rs3))
				{
					$r['mprice'][$p['type']][$p['branch_id']] = $p['price'];
				}
			}
			
			if (isset($config['sku_multiple_quantity_price']))
			{
				// normal qprice
				$rs4 = $con->sql_query("select branch_id, min_qty, price from sku_items_qprice where sku_item_id=$r[id] order by min_qty");
				while($p=$con->sql_fetchassoc($rs4)){
					$r['min_qty'][$p['min_qty']] = 1;
					$r['qprice'][$p['min_qty']][$p['branch_id']] = $p;
				}
				$con->sql_freeresult($rs4);
				@ksort($r['min_qty']);
				
				if (isset($config['sku_multiple_quantity_mprice'])){
					// qprice by member type
					$rs5 = $con->sql_query("select * from sku_items_mqprice where sku_item_id = ".mi($r['id']));

					while($p=$con->sql_fetchassoc($rs5)){
						$r['min_qty'][$p['min_qty']] = 1;
						$r['mqprice'][$p['min_qty']][$p['type']][$p['branch_id']] = $p;
					}
					$con->sql_freeresult($rs5);
				}
			}
			
			//$region_branch['got_region'][$region_code]
			
			if(true){
				$region_branch_list = $region_branch['got_region'][$show_by_region_code];
				// show price by region
				if($config['sku_use_region_price'] && $config['masterfile_branch_region']){
					$region_price = array();
					$region_data = array();
					
					// get region price				
					$q_rp = $con->sql_query("select * from sku_items_rprice sirp
					where sirp.sku_item_id=".mi($r['id']));
					$region_price = array();
					while($rp = $con->sql_fetchassoc($q_rp)){
						$region_price[$rp['region_code']][$rp['mprice_type']] = $rp;
					}
					$con->sql_freeresult($q_rp);
					
					
					
					// check price type and selling price
					//print_r($region_branch);
					if($region_branch['got_region']){
						foreach($region_branch['got_region'] as $region_code=>$branch_list){
							$price_type_arr = array();
							$selling_price_arr = array();
							
							if(isset($region_price[$region_code]['normal']['trade_discount_code'])){
								$price_type_arr[] = $region_price[$region_code]['normal']['trade_discount_code'];
							}
							foreach($branch_list as $tmp_bid=>$b){
								// branch price type
								$tmp_price_type = $tdtype['default_trade_discount_code'];
								if(isset($r['trade_discount_code'][$tmp_bid])){
									$tmp_price_type = trim($r['trade_discount_code'][$tmp_bid]);
								}
								if(!in_array($tmp_price_type, $price_type_arr)){
									$price_type_arr[] = $tmp_price_type;
								}
								
								// branch selling price
								$tmp_selling_price = mf($r['selling_price']);
								if(isset($r['price'][$tmp_bid])){
									$tmp_selling_price = mf($r['price'][$tmp_bid]);
								}
								if(!in_array($tmp_selling_price, $selling_price_arr)){
									$selling_price_arr[] = $tmp_selling_price;
								}
								
								// mprice
								if (isset($config['sku_multiple_selling_price'])){
									foreach($config['sku_multiple_selling_price'] as $mprice_type){
										// get mprice selling
										$tmp_selling_price = mf($r['selling_price']);
										if(isset($r['mprice'][$mprice_type][$tmp_bid])){
											$tmp_selling_price = mf($r['mprice'][$mprice_type][$tmp_bid]);
										}
										
										// construct array
										if(!is_array($region_data[$region_code][$mprice_type]['selling_price_arr'])){
											$region_data[$region_code][$mprice_type]['selling_price_arr'] = array();
										}
										
										// store the different into array
										if(!in_array($tmp_selling_price, $region_data[$region_code][$mprice_type]['selling_price_arr'])){
											$region_data[$region_code][$mprice_type]['selling_price_arr'][] = $tmp_selling_price;
											$region_data[$region_code][$mprice_type]['selling_price_count'] = count($region_data[$region_code][$mprice_type]['selling_price_arr']);
										}
									}
								}
							}
							
							// check different discount percent
							foreach($price_type_list as $pt){
								$disc_per_arr = array();
								foreach($branch_list as $tmp_bid=>$b){
									$rate = trim($disc[$tmp_bid][$pt['code']]);
									if(!in_array($rate, $disc_per_arr))	$disc_per_arr[] = $rate;
									
									// same price type but different discount rate
									if(count($disc_per_arr)>1){
										$region_data[$region_code]['normal']['rate_count_got_diff'] = true;
										break;
									}	
								}
							}
							
							$region_data[$region_code]['normal']['price_type_arr'] = $price_type_arr;
							$region_data[$region_code]['normal']['price_type_count'] = count($price_type_arr);
							$region_data[$region_code]['normal']['selling_price_arr'] = $selling_price_arr;
							$region_data[$region_code]['normal']['selling_price_count'] = count($selling_price_arr);
						}
					}
					
					
					$r['region_price'] = $region_price;
					$r['region_data'] = $region_data;
				}
			}

			// get latest cost
			$q1 = $con->sql_query("select * from sku_items_cost where sku_item_id = ".mi($r['id']));
			
			while($tmp = $con->sql_fetchassoc($q1)){
				$r['cost'][$tmp['branch_id']] = $tmp['grn_cost'];
				$r['stock_bal'][$tmp['branch_id']] = $tmp['qty'];
			}
			$con->sql_freeresult($q1);
			
			if($config['enable_gst']){
				$output_tax_info = get_sku_gst("output_tax", $r['id'], array('no_check_use_zero_rate'=>1));
				$r['gst_rate'] = $output_tax_info['rate'];
				$r['inclusive_tax'] = get_sku_gst("inclusive_tax", $r['id']);
			}
			
			$items[] = $r;
		}
		$con->sql_freeresult($rs1);
		
		/*
		echo '<pre>';
		print_r($items);
		echo '</pre>';
		*/
		//print_r($items);
		
		$smarty->assign("items",$items);
		$smarty->assign('show_by_region_code', $show_by_region_code);
		if($show_by_region_code){
			$smarty->assign('branch', $region_branch_list);
		}
		
		if($config['sku_multiple_quantity_mprice'] && $config['sku_multiple_selling_price']){
			$sku_multiple_selling_price = array();
			$sku_multiple_selling_price[] = "member";
			foreach($config['sku_multiple_selling_price'] as $mtype){
				$sku_multiple_selling_price[] = $mtype;
			}

			$smarty->assign("sku_multiple_selling_price", $sku_multiple_selling_price);
		}
	}
}

// save selling price changes
function change_price(){

	global $con, $smarty, $config, $sessioninfo, $LANG, $have_changes;
	//print_r($_REQUEST);exit;
	if (BRANCH_CODE != 'HQ') $form['branch_id'] = get_request_branch();
	$form['user_id'] = $sessioninfo['id'];
	$form['source'] = 'SKU';
	$ret = array();
	$temp = array();
	$ro_update = ($_REQUEST['region_only_update']) ? true:false;
	$have_changes = false;

	$con->sql_query("select department_id, vendor_id, brand_id, default_trade_discount_code, trade_discount_type, is_bom from sku left join category on category_id = category.id where sku.id = (select sku_id from sku_items where sku_items.id=".mi($_REQUEST['sku_item_id']).")");
	$tdtype = $con->sql_fetchrow();
	$con->sql_freeresult();

	if($tdtype['trade_discount_type'] != 0 && !$config['sku_always_show_trade_discount']) $err = check_td_rate($tdtype);

	if($sessioninfo['branch_id']!= $_REQUEST['form_branch_id']){
		$err[] = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], 'Change Selling Price Module.');
	}

	if(count($err) == 0){
		foreach ($_REQUEST['price'] as $id=>$price){
			$form['code'] = $_REQUEST['code'][$id];
			$form['sku_item_id'] = $id;
			$sid = mi($id);
			
			if (BRANCH_CODE == 'HQ') 
			{
				foreach($price as $bid => $p)
				{
					$form['branch_id'] = $bid;  
					$form['price'] = $p;
					$form['trade_discount_code'] = $_REQUEST['trade_discount_code'][$id][$bid];
					$form['selling_price_foc'] = mi($_REQUEST['item_foc']['normal'][$id][$bid]);
					
					// RSP
					if($_REQUEST['use_rsp'][$id]){
						$form['rsp_discount'] = trim($_REQUEST['rsp_discount'][$id][$bid]);
					}
					
					if ($_REQUEST['item_edit']['normal'][$id][$bid]) update_price($form, $msg); //only update when the checkbox is ticked
					if (isset($config['sku_multiple_selling_price']))
					{
						foreach($config['sku_multiple_selling_price'] as $type)
						{
							$form['price'] = $_REQUEST['mprice'][$id][$type][$bid];
							$form['type'] = $type;
							if ($_REQUEST['item_edit'][$type][$id][$bid]) update_mprice($form, $msg); //only update when the checkbox is ticked
						}
					}
				}
				
				// update using region
				if($config['sku_use_region_price']){
					$region_price = ($_REQUEST['region_price']) ? $_REQUEST['region_price'] : $_REQUEST['ro_region_price'];
					$region_branch = load_region_branch_array(array('inactive_change_price'=>1));
					
					$region_trade_discount_code = ($_REQUEST['region_trade_discount_code']) ? $_REQUEST['region_trade_discount_code'] : $_REQUEST['ro_region_trade_discount_code'];
					
					if($region_trade_discount_code){
						//print_r($region_trade_discount_code);die();
						$region_list = $region_trade_discount_code[$sid];
						
						//foreach($region_trade_discount_code as $sid=>$region_list){
						//	$sid = mi($sid);
							if(!$sid)	continue;	// invalid sku item id
							
							foreach($region_list as $region_code=>$price_type){
								// normal selling price
								$selling_price = mf($region_price[$sid][$region_code]);
								$selling_price_foc = mi($_REQUEST['region_item_foc']['normal'][$sid][$region_code]);
								
								// select current price type and selling price
								$q_normal = $con->sql_query("select * from sku_items_rprice where region_code=".ms($region_code)." and sku_item_id=$sid and mprice_type='normal'");
								$rp = $con->sql_fetchassoc($q_normal);
								$con->sql_freeresult($q_normal);
								
								// need update - sku_items_rprice
								if(!$rp || ($rp['price'] != $selling_price || $rp['trade_discount_code'] != $price_type || $selling_price_foc != $rp['selling_price_foc'])){
									$upd = array();
									$upd['region_code'] = $region_code;
									$upd['sku_item_id'] = $sid;
									$upd['mprice_type'] = 'normal';
									$upd['price'] = $selling_price;
									$upd['trade_discount_code'] = $price_type;
									$upd['selling_price_foc'] = $selling_price_foc;
									
									if (!$ro_update) {
										if ($_REQUEST['reg_item_edit']['normal'][$id][$region_code]) {
											$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
											$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code], " . $region_code . ")", $selling_price);
											if($selling_price_foc)	$msg .= " , FOC: Yes";
											$have_changes = true;
										}
									}
									else {
										if ($_REQUEST['ro_reg_item_edit']['normal'][$id][$region_code]) {
											$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
											$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code], " . $region_code . ")", $selling_price);
											if($selling_price_foc)	$msg .= " , FOC: Yes";
											$have_changes = true;
										}
									}
									
									// update - sku_items_rprice_history
									$upd['date'] = date("Y-m-d");
									$upd['user_id'] = $sessioninfo['id'];
									
									if (!$ro_update) {
										if ($_REQUEST['reg_item_edit']['normal'][$id][$region_code]) {
											$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
										}
									}
									else {
										if ($_REQUEST['ro_reg_item_edit']['normal'][$id][$region_code]) {
											$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
										}
									}
								}
								
								if($region_branch['got_region'][$region_code]){
									// loop all branch in this region
									foreach($region_branch['got_region'][$region_code] as $bid=>$b){
										$form['branch_id'] = $bid;  
										$form['price'] = $selling_price;
										$form['trade_discount_code'] = $price_type;
										$form['selling_price_foc'] = $selling_price_foc;
						
										if ($_REQUEST['reg_item_edit']['normal'][$id][$region_code]) update_price($form, $msg);
									}
								}
							}
							
							// MPrice
							$region_mprice = ($_REQUEST['region_mprice']) ? $_REQUEST['region_mprice'] : $_REQUEST['ro_region_mprice'];
							if (isset($config['sku_multiple_selling_price']) && $region_mprice){
								// loop for each mprice
								foreach($config['sku_multiple_selling_price'] as $mprice_type){
									// loop for each region
									foreach($region_list as $region_code=>$price_type){
										$selling_price = mf($region_mprice[$sid][$mprice_type][$region_code]);
										
										$q_mp = $con->sql_query("select * from sku_items_rprice where region_code=".ms($region_code)." and sku_item_id=$sid and mprice_type=".ms($mprice_type));
										$rp = $con->sql_fetchassoc($q_mp);
										$con->sql_freeresult($q_mp);
										
										if(!$rp || $rp['price'] != $selling_price || $rp['trade_discount_code'] != $price_type){
											$upd = array();
											$upd['region_code'] = $region_code;
											$upd['sku_item_id'] = $sid;
											$upd['mprice_type'] = $mprice_type;
											$upd['price'] = $selling_price;
											$upd['trade_discount_code'] = $price_type;
											$upd['selling_price_foc'] = 0;
											
											if (!$ro_update) {
												if ($_REQUEST['reg_item_edit'][$mprice_type][$sid][$region_code]) {
													$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
													$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code], " . $region_code . ")", $selling_price);
													$have_changes = true;
												}
											}
											else {
												if ($_REQUEST['ro_reg_item_edit'][$mprice_type][$sid][$region_code]) {
													$con->sql_query("replace into sku_items_rprice ".mysql_insert_by_field($upd));
													$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code], " . $region_code . ")", $selling_price);
													$have_changes = true;
												}
											}
											
											// update - sku_items_rprice_history
											$upd['date'] = date("Y-m-d");
											$upd['user_id'] = $sessioninfo['id'];
											
											if (!$ro_update) {
												if ($_REQUEST['reg_item_edit'][$mprice_type][$sid][$region_code]) {
													$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
												}
											}
											else {
												if ($_REQUEST['ro_reg_item_edit'][$mprice_type][$sid][$region_code]) {
													$con->sql_query("replace into sku_items_rprice_history ".mysql_insert_by_field($upd));
												}
											}
										}
										
										if($region_branch['got_region'][$region_code]){
											// loop all branch in this region
											foreach($region_branch['got_region'][$region_code] as $bid=>$b){
												$form['branch_id'] = $bid;
												$form['trade_discount_code'] = $price_type;
												$form['price'] = $selling_price;
												$form['type'] = $mprice_type;
												
												if ($_REQUEST['reg_item_edit'][$mprice_type][$sid][$region_code]) update_mprice($form, $msg);
											}
										}
									}
								}
							}
						//}
					}
				}
			}
			else
			{//xxx
				$form['price'] = $price[$sessioninfo['branch_id']];
				$form['trade_discount_code'] = $_REQUEST['trade_discount_code'][$id][$sessioninfo['branch_id']]; 
				$form['selling_price_foc'] = mi($_REQUEST['item_foc']['normal'][$id][$sessioninfo['branch_id']]);
				
				// RSP
				if($_REQUEST['use_rsp'][$id]){
					$form['rsp_discount'] = trim($_REQUEST['rsp_discount'][$id][$sessioninfo['branch_id']]);
				}
				
				if ($_REQUEST['cb_inp_price']['normal'][$id][$sessioninfo['branch_id']]) {
					update_price($form, $msg);
				}
				
				if (isset($config['sku_multiple_selling_price']))
				{
					foreach($config['sku_multiple_selling_price'] as $type)
					{
						$form['price'] = $_REQUEST['mprice'][$id][$type][$sessioninfo['branch_id']];
						$form['type'] = $type;
						if ($_REQUEST['cb_inp_price'][$type][$id][$sessioninfo['branch_id']]) update_mprice($form, $msg);
					}
				}
			}
		}
	}
	
	if(count($err) > 0) $temp['err'] = join("\n", $err);
	elseif (!$have_changes) $temp['status_msg'] = '<ul><li>Nothing updated. Please tick at least one checkbox & change a price to update</li></ul>';
	elseif($msg) $temp['status_msg'] = "<ul>".$msg."</ul>";
  	$ret[] = $temp;
  	print json_encode($ret);
  	exit;
}

// save multiple qty selling 
function change_qprice()
{
	global $con, $smarty, $sessioninfo, $LANG;
	if (BRANCH_CODE != 'HQ') $form['branch_id'] = get_request_branch();
	$form['user_id'] = $sessioninfo['id'];
	
	if($sessioninfo['branch_id']!= $_REQUEST['form_branch_id']){
		js_redirect(sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], 'Change Selling Price Module.', BRANCH_CODE), "/masterfile_sku_items_price.php");
	}else{
		// normal qprice
		foreach ($_REQUEST['price'] as $id=>$price)
		{
			$form['code'] = $_REQUEST['code'][$id];
			$form['sku_item_id'] = $id;
			
			if (BRANCH_CODE == 'HQ') 
			{
				foreach($price as $bid => $p)
				{
					$form['min_qty'] = $_REQUEST['min_qty'][$id];
					$form['branch_id'] = $bid;  
					$form['price'] = $p;
					update_qprice($form, $msg);
				}
			}
			else
			{
				$form['min_qty'] = $_REQUEST['min_qty'][$id];
				$form['price'] = $price;
				update_qprice($form, $msg);
			}
		}
	}
	
	// qprice by member type
	if($_REQUEST['mqprice']){
		foreach ($_REQUEST['mqprice'] as $id=>$mqprice_list){
			foreach($mqprice_list as $mqprice_type=>$bid_price_list){
				$form['code'] = $_REQUEST['code'][$id];
				$form['sku_item_id'] = $id;
				$form['min_qty'] = $_REQUEST['min_qty'][$id];
				
				if (BRANCH_CODE == 'HQ') 
				{
					foreach($bid_price_list as $bid => $p)
					{
						$form['branch_id'] = $bid;
						$form['type'] = $mqprice_type;
						$form['price'] = $p;
						update_mqprice($form, $msg);
					}
				}
				else
				{
					$form['branch_id'] = $sessioninfo['branch_id'];
					$form['type'] = $mqprice_type;
					$form['price'] = $bid_price_list;
					update_mqprice($form, $msg);
				}
			}
		}
	}
	
	$smarty->assign('msg', $msg);
}

function update_qprice($form, &$msg)
{
	global $con, $LANG, $sessioninfo;

		$con->sql_query("delete from sku_items_qprice where sku_item_id = ".mi($form['sku_item_id'])." and branch_id = ".mi($form['branch_id']));
		
		$upd = $form;
		foreach ($form['min_qty'] as $idx=>$min_qty) 
		{
			$upd['min_qty'] = mf($min_qty);
			if ($min_qty<=0) continue;
			
			$upd['price'] = $form['price'][$idx];
			
			//branch_id, sku_item_id, added, price, cost, source
			$con->sql_query("insert into sku_items_qprice_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "min_qty", "price", "user_id")));
			
			$con->sql_query("insert into sku_items_qprice ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "min_qty", "price")));
		}
		
		$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $form[sku_item_id]");  
		
		// continue to show detail
		log_br($sessioninfo['id'], "MASTERFILE", 0, "Multi-Qty Price Change for $form[code] (Branch $form[branch_id])");

		// price updated
		$msg .= "<li> ".sprintf($LANG['SKU_QPRICE_UPDATED'], $form['code'] . " (" . get_branch_code($form['branch_id']) . ")");
}

function update_mqprice($form, &$msg)
{
	global $con, $LANG, $sessioninfo;

	$con->sql_query("delete from sku_items_mqprice where sku_item_id = ".mi($form['sku_item_id'])." and branch_id = ".mi($form['branch_id'])." and type = ".ms($form['type']));
	
	$upd = $form;
	foreach ($form['min_qty'] as $idx=>$min_qty) 
	{
		$upd['min_qty'] = mf($min_qty);
		$upd['price'] = $form['price'][$idx];
		
		if ($min_qty<=0 && !$upd['price']) continue;
		
		$upd['added'] = $upd['last_update'] = "CURRENT_TIMESTAMP";
		 
		//branch_id, sku_item_id, added, price, cost, source
		$con->sql_query("insert into sku_items_mqprice_history ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "min_qty", "price", "user_id", "type", "added")));
		
		$con->sql_query("insert into sku_items_mqprice ".mysql_insert_by_field($upd,array("branch_id", "sku_item_id", "min_qty", "price", "type", "last_update")));
	}
	
	$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $form[sku_item_id]");  
	
	// continue to show detail
	log_br($sessioninfo['id'], "MASTERFILE", 0, "Multi-Qty Price Change for $form[code] (Branch $form[branch_id])");

	// price updated
	$msg .= "<li> ".sprintf($LANG['SKU_QPRICE_UPDATED'], $form['code'] . " (" . get_branch_code($form['branch_id']) . ")");
}

function get_last_cost(&$form)
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

/*
form array must have below variable
branch_id, sku_item_id, price, trade_discount_code, user_id, code (sku_item_code)
*/
function update_price(&$form, &$msg)
{
	global $con, $LANG, $config, $have_changes, $sessioninfo;
	
//	print "$form[sku_item_id]=>$form[branch_id]=>$form[price]<br />";

// check if same price
	$con->sql_query("select if(sip.price is null,si.selling_price,sip.price) as price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as trade_discount_code, 
		sku.default_trade_discount_code,
		if(sip.price is null, si.selling_foc, sip.selling_price_foc) as selling_price_foc
		from sku_items si
		left join sku on si.sku_id = sku.id 
		left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($form['branch_id'])." 
		where si.id = ".mi($form['sku_item_id']));

	$t=$con->sql_fetchrow();
//	print_r($t);exit;

	// must hv price type
	if(!$form['trade_discount_code'] && $config['sku_always_show_trade_discount']){
		$form['trade_discount_code'] = $t['default_trade_discount_code'];	// use master
	}
	if ($form['price'] == $t['price'] && $form['trade_discount_code'] == $t['trade_discount_code'] && $form['selling_price_foc'] == $t['selling_price_foc']) return;
	
	// get last cost from GRN
	get_last_cost($form);
	
	//branch_id, sku_item_id, added, price, cost, source
	$con->sql_query("insert into sku_items_price_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code", "source", "user_id","selling_price_foc", "rsp_discount")));
	$con->sql_query("replace into sku_items_price ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "cost", "trade_discount_code","selling_price_foc", "rsp_discount")));
	$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $form[sku_item_id]");  
	
	// continue to show detail
	log_br($sessioninfo['id'], "MASTERFILE", 0, "Price Change for $form[code] to $form[price] (Discount: $form[trade_discount_code], Branch $form[branch_id]) FOC: ".($form['selling_price_foc']?'Yes':'No'));

	// price updated
	$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code], " . get_branch_code($form['branch_id']) . ")", $form['price']);
	if($form['selling_price_foc'])	$msg .= " , FOC: Yes";
	$have_changes = true;
}

function update_mprice(&$form, &$msg)
{
	global $con, $LANG, $have_changes, $sessioninfo;
	
//	print "$form[sku_item_id]=>$form[branch_id]=>$form[price]<br />";

// check if same price
	$type = ms($form['type']);
	$con->sql_query("select price, trade_discount_code from sku_items_mprice where type = $type and branch_id = ".mi($form['branch_id'])." and sku_item_id = ".mi($form['sku_item_id']));

	$t=$con->sql_fetchrow();
	//print "$form[sku_item_id]=>$form[branch_id]=>$form[price] >> $t[price] $t[selling_price]<br />";

	if ($form['price'] == $t['price'] && $form['trade_discount_code'] == $t['trade_discount_code']) return;
	
	//branch_id, sku_item_id, added, price, cost, source
	$con->sql_query("insert into sku_items_mprice_history ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "type", "price", "trade_discount_code", "user_id")));
	$con->sql_query("replace into sku_items_mprice ".mysql_insert_by_field($form,array("branch_id", "sku_item_id", "price", "trade_discount_code", "type")));
	$con->sql_query("update sku_items set lastupdate = CURRENT_TIMESTAMP where id = $form[sku_item_id]");
	
	// continue to show detail
	log_br($sessioninfo['id'], "MASTERFILE", 0, "Price Change for $form[code] to $form[price]  (Discount: $form[trade_discount_code] Type: $form[type], ".get_branch_code($form['branch_id']));

	// price updated
	$msg .= "<li> ".sprintf($LANG['SKU_PRICE_UPDATED'], $form['code'] . " (Discount: $form[trade_discount_code] Type: $form[type], " . get_branch_code($form['branch_id']) . ")", $form['price']);
	$have_changes = true;
}

function check_td_rate($tdtype){
	global $con, $config, $sessioninfo;

	foreach ($_REQUEST['price'] as $id=>$price){
		$err_branch = array();
		$form['code'] = $_REQUEST['code'][$id];
		$form['sku_item_id'] = $id;
		$con->sql_query("select sku_item_code from sku_items where id = ".mi($id));
		$si_info = $con->sql_fetchrow();
		$con->sql_freeresult();

		if (BRANCH_CODE == 'HQ'){
			foreach($price as $bid => $p){
				if (!$_REQUEST['item_edit']['normal'][$id][$bid]) continue;
				if(preg_match("{PWP$}", $_REQUEST['trade_discount_code'][$id][$bid])) continue;
				$form['branch_id'] = $bid;
				$form['trade_discount_code'] = $_REQUEST['trade_discount_code'][$id][$bid];

				if ($tdtype['trade_discount_type']==1){
					/// use brand table
					$con->sql_query("select rate from brand_commission where department_id=".mi($tdtype['department_id'])." and brand_id = ".mi($tdtype['brand_id'])." and branch_id = ".mi($bid)." and skutype_code = ".ms($form['trade_discount_code'])) or die(mysql_error());
				}elseif ($tdtype['trade_discount_type']==2){
					// use vendor table
					$con->sql_query("select rate from vendor_commission where department_id=".mi($tdtype['department_id'])." and vendor_id = ".mi($tdtype['vendor_id'])." and branch_id = ".mi($bid)." and skutype_code = ".ms($form['trade_discount_code'])) or die(mysql_error());
				}
				$item = $con->sql_fetchrow();
				$con->sql_freeresult();

				if(!$item['rate'] || $item['rate'] == 0){
					$err_branch[] = get_branch_code($bid);
				}
			}
		}else{
			$tdc = trim($_REQUEST['trade_discount_code'][$id][$sessioninfo['branch_id']]);
			if(preg_match("{PWP$}", $tdc)) continue;
			if ($tdtype['trade_discount_type']==1){
				/// use brand table
				$con->sql_query("select rate from brand_commission where department_id=".mi($tdtype['department_id'])." and branch_id = ".mi($sessioninfo['branch_id'])." and brand_id = ".mi($tdtype['brand_id'])." and skutype_code = ".ms($tdc)) or die(mysql_error());
			}elseif ($tdtype['trade_discount_type']==2){
				// use vendor table
				$con->sql_query("select rate from vendor_commission where department_id=".mi($tdtype['department_id'])." and branch_id = ".mi($sessioninfo['branch_id'])." and vendor_id = ".mi($tdtype['vendor_id'])." and skutype_code = ".ms($tdc)) or die(mysql_error());
			}
			$item = $con->sql_fetchrow();
			$con->sql_freeresult();

			if($item['rate'] == 0){
				$err_branch[] = get_branch_code($sessioninfo['branch_id']);
			}
		}

		if(count($err_branch) > 0) $err[] = "* SKU Item ".$si_info['sku_item_code']." for Branch ".join(", ", $err_branch)." is having 0 rate.";
	}

	return $err;
}

function ajax_show_region_branch_price_type_selling(){
	global $con, $smarty, $sessioninfo, $config;
	
	//print_r($_REQUEST);
	
	$sid = mi($_REQUEST['sku_item_id']);
	$region_code = trim($_REQUEST['region_code']);
	$mprice_type = trim($_REQUEST['mprice_type']);
	
	if(!$region_code || !$config['masterfile_branch_region'][$region_code])	die('Invalid Region');
	if(!$sid)	die('Invalid SKU');
	
	// get region branches (ignore HQ)
	$con->sql_query("select * from branch where id>1 and (active=1 or (active=0 and inactive_change_price=1)) and region=".ms($region_code)." order by sequence,code");
	$b_info = array();
	while($r = $con->sql_fetchassoc()){
		$b_info[$r['id']] = $r;
	}
	$con->sql_freeresult();
	
	// no branch under this region
	if(!$b_info)	die('No branch for this region');
	
	$show_price_type = false;
	
	// load sku item info
	$con->sql_query("select department_id, vendor_id, brand_id, default_trade_discount_code, trade_discount_type, is_bom 
	from sku 
	left join sku_items si on si.sku_id=sku.id
	left join category on category_id = category.id 
	where si.id=$sid");
	$sku_info = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	
	if($mprice_type=='normal'){
		$show_price_type = true;
		$price_type_list = load_price_type_list();
		
		$deptid = mi($sku_info['department_id']);
		
		foreach($b_info as $bid=>$b){
			$sql = "select if(sip.price is null, si.selling_price, sip.price) as selling_price, if(sip.price is null, sku.default_trade_discount_code, sip.trade_discount_code) as price_type,
			if(sip.price is null, si.selling_foc, sip.selling_price_foc) as selling_price_foc
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join sku_items_price sip on sip.branch_id=$bid and sip.sku_item_id=si.id
			where si.id=$sid";
			$con->sql_query($sql);
			while($r = $con->sql_fetchassoc()){
				$b_info[$bid] = array_merge($b, $r);
			}
			$con->sql_freeresult();
			
			// load discount rate
			if($config['sku_always_show_trade_discount']){
				$con->sql_query("select tdt.*,tdt.code as skutype_code,branch.id as branch_id,btd.value as rate 
				from branch
				left join branch_trade_discount btd on btd.branch_id=branch.id
				left join trade_discount_type tdt on btd.trade_discount_id=tdt.id
				where branch.id=$bid");
			}elseif ($sku_info['trade_discount_type']==1){
				/// use brand table
				$con->sql_query("select * from brand_commission where department_id=$deptid and branch_id = $bid and brand_id = ".mi($sku_info['brand_id']));
			}
			elseif ($sku_info['trade_discount_type']==2){
				// use vendor table
				$con->sql_query("select * from vendor_commission where department_id=$deptid and branch_id = $bid and vendor_id = ".mi($sku_info['vendor_id']));
			}
	
			if($sku_info['trade_discount_type'] > 0 && $con->sql_numrows() == 0){ // check if found no trade discount, load default trade discount
				// use trade discount type table
				$con->sql_query("select code as skutype_code, 0 as rate from trade_discount_type order by code");
			}
			
			$disc_rate = array();
			while($r=$con->sql_fetchassoc()){
				//if ($r['rate']<=0) continue;
				$disc_rate[$r['skutype_code']] = $r['rate'];
			}
			$con->sql_freeresult();
			$b_info[$bid]['disc_rate'] = $disc_rate;
			
		}
		//print_r($b_info);
	}else{	// mprice
		foreach($b_info as $bid=>$b){
			$sql = "select if(simp.price is null, si.selling_price, simp.price) as selling_price
			from sku_items si
			left join sku on sku.id=si.sku_id
			left join sku_items_mprice simp on simp.branch_id=$bid and simp.sku_item_id=si.id
			where si.id=$sid";
			$con->sql_query($sql);
			while($r = $con->sql_fetchassoc()){
				$b_info[$bid] = array_merge($b, $r);
			}
			$con->sql_freeresult();
		}
	}
	//print_r($sku_info);
	$smarty->assign('show_price_type', $show_price_type);
	$smarty->assign('b_info', $b_info);
	print "<h3>".$config['masterfile_branch_region'][$region_code]['name']." - $mprice_type</h3>";
	$smarty->display('masterfile_sku_items_price.region_branch_price.tpl');
}

function ajax_get_region_price_history(){
	global $con, $smarty, $sessioninfo, $config;
	
	$sid = mi($_REQUEST['sku_item_id']);
	$region_code = trim($_REQUEST['region_code']);
	$mprice_type = trim($_REQUEST['mprice_type']);
	
	$sql = "select sirph.*, user.u as username
	from sku_items_rprice_history sirph
	left join user on user.id=sirph.user_id
	where sirph.region_code=".ms($region_code)." and sirph.sku_item_id=$sid and sirph.mprice_type=".ms($mprice_type)."
	order by date";
	$q1 = $con->sql_query($sql);
	$rp_history = array();
	while($r = $con->sql_fetchassoc($q1)){
		$rp_history[] = $r;
	}
	$con->sql_freeresult($q1);
	
	//print_r($rp_history);
	$smarty->assign('rp_history', $rp_history);
	$smarty->display('masterfile_sku_items_price.mprice.region_price_history.tpl');
}

function ajax_search_barcode_item(){
	global $con, $LANG, $smarty;
	
	$form = $_REQUEST;
	$grn_barcode = $form['grn_barcode'];
	$grn_barcode_type = $form['grn_barcode_type'];
	$si_list = array();
	
	if($form['grn_barcode'] && !$form['grn_barcode_type']){ // search by GRN barcoder
		$si_info = get_grn_barcode_info($form['grn_barcode'], true);
	}else{
		switch($grn_barcode_type){
			case 1:	// arms code, mcode, link code
				if (strlen($grn_barcode) == 13){
					$grn_barcode2 = substr($grn_barcode,0,12);
					$in_str = ms($grn_barcode).','.ms($grn_barcode2);
					$q1 = $con->sql_query("select *, id as sku_item_id from sku_items where sku_item_code in ($in_str) or mcode in ($in_str) or artno in ($in_str) or link_code in ($in_str) order by sku_item_code");
				}
				else {
					$q1 = $con->sql_query("select *, id as sku_item_id from sku_items where sku_item_code=".ms($grn_barcode)." or mcode=".ms($grn_barcode)." or artno=".ms($grn_barcode)." or link_code=".ms($grn_barcode)." order by sku_item_code");
				}
				
				if($con->sql_numrows($q1) == 1){
					$si_info = $con->sql_fetchassoc($q1);
				}elseif($con->sql_numrows($q1) > 1){
					while($r = $con->sql_fetchassoc($q1)){
						$si_list[] = $r;
					}
				}
				$con->sql_freeresult($q1);
				
				break;
			default:
				$si_info['err'] = "Invalid GRN Barcode Type";	
				if ($print_error)	fail("Invalid GRN Barcode Type");
				break;
		}
	}
	
	if($si_info['sku_item_id']){ // found one result
		$ret = array();
		$ret['ok'] = 1;
		$ret['sku_item_id'] = $si_info['sku_item_id'];
		$ret['sku_item_code'] = $si_info['sku_item_code'];
		
		print json_encode($ret);
	}elseif(count($si_list) > 0){ // found more than one result, display item selection screen
		$smarty->assign("items", $si_list);
		$ret['ok'] = 1;
		$ret['html'] = $smarty->fetch("masterfile_sku_items_price.si_list.tpl");
		
		print json_encode($ret);
	}else{
		$si_info['err'] = sprintf($LANG['DO_INVALID_ITEM'], $grn_barcode);	
		fail($si_info['err']);
	}
}
?>