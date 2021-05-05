<?php
/*
8/27/2008 5:23:13 PM  - andy
- add department selection and department + category in output

2008-9-8 3:30 PM - andy
- add show category checkbox + only display category description when checked

11/18/2008 6:35:31 PM - yinsee
- add markup % for export

05/25/2010 05:41:51 PM yinsee
- change the title to "Export SKU Items"
- rename output file to ARMS_SKU.TXT

5/10/2011 6:05:15 PM Alex
- change get latest trade_discount_code

6/24/2011 3:13:21 PM Andy
- Make all branch default sort by sequence, code.

3/16/2012 12:09:23 PM Andy
- Add Export to excel format.
- Add Vendor and Brand filter.

3/16/2012 4:42:23 PM Andy
- Add log_br when user export sku.

6/14/2012 06:08:00 PM Andy
- Fix SKU Export data too large cannot export

04/14/2016 16:25 Edwin
- Added new fields: uom code, fraction, input tax, output tax, inclusive tax, scale type, active, brand, vendor, parent arms code, parent artno, parent mcode in excel and txt.

05/04/2016 17:30 Edwin
- Bug fixed at items does not start a new line in txt.

8/24/2016 2:09 PM Andy
- Fixed to only export price change item when price change date got provide.
- Change to only export items when last update date is at least zero or real date.
- Enhanced to skip price change item if found all sku already exported by using last update.

1/6/2017 4:38 PM Andy
- Change Export Excel to Export CSV.

3/20/2017 13:01 PM Qiu Ying
- Enhanced to add product description column before receipt description

7/4/2017 9:00 AM Qiu Ying
- Enhanced to filter parent & child

11/14/2017 2:21 PM Andy
- Changq query method for parent child filter.

8/15/2019 4:04 PM William
- Remove department search filter.
- Enhanced to add can select by category search filter and add new export column "min","max","moq","notify username".

2/25/2020 2:52 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

9/15/2020 1:09 PM William
- Enhanced to add new export column "Additional Description".
*/ 
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('SKU_EXPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SKU_EXPORT', BRANCH_CODE), "/index.php");
if (!$con_multi)  $con_multi = $appCore->reportManager->connectReportServer();

$szs = array(10,20,20,20,20,200,40,10,10,10,10,10,10,10,10,10,10,20,10,20,50,10,10,10,20,20,20,20);
$szs_cat = array(10,20,20,20,20,200,40,10,10,10,10,30,30,10,10,10,10,10,10,20,10,20,50,10,10,10,20,20,20,20);
$smarty->assign("PAGE_TITLE", "Export SKU Items");

if (isset($_REQUEST['a']))
{
	switch ($_REQUEST['a'])
	{
		case 'setup':
			$con->sql_query("alter table branch add sku_export timestamp");
			print "Success";
			exit;
			
		case 'export':
			$row = 0;
			$branch_id = intval($_REQUEST['branch_id']);
			$price_markup = 1 + doubleval($_REQUEST['price_markup'])/100;
			$cost_markup = 1 + doubleval($_REQUEST['cost_markup'])/100;
			$export_type = trim($_REQUEST['export_type']);
			$vendor_id = mi($_REQUEST['vendor_id']);
			$brand_id = trim($_REQUEST['brand_id']);
			$input_tax = mi($_REQUEST['input_tax']);
			$output_tax = mi($_REQUEST['output_tax']);
			$inclusive_tax = trim($_REQUEST['inclusive_tax']);
			$sku_type = trim($_REQUEST['sku_type']);
			$scale_type = $_REQUEST['scale_type'];
			$active = $_REQUEST['active'];
			$parent_child = $_REQUEST['parent_child_filter'];
			
			$filter = array();
			
			if(!$export_type)	$export_type = 'txt';
			
			// export new SKU
			$lastupdate1 = trim($_REQUEST['items_last_update']);
			if($lastupdate1 === ""){
				
			}else{
				$load_sku = true;
				if (strtotime($lastupdate1)===false) {
					$lastupdate1 = "0";
					$already_get_all_items = true;
				}
			}
			
			$all_category = $_REQUEST['all_category'];
			if(!$all_category){
				$category_id = $_REQUEST['category_id'];
				$category_level = mi($_REQUEST['category_level']);
				if($category_id) {
					if($category_level == "all_1"){
						$con_multi->sql_query("select level from category where id=".mi($category_id));
						$cat_info = $con_multi->sql_fetchassoc();
						$con_multi->sql_freeresult();
						$category_level = mi($cat_info['level']);
					}
					$filter[] = "cc.p$category_level =".mi($category_id);
				}
			}
			
			if($vendor_id > 0) {
				$filter[] = "sku.vendor_id=$vendor_id";
			}
			
			if($brand_id !== '') {
				$filter[] = "sku.brand_id=".mi($brand_id);
			}
			
			if($input_tax > 0) {
				$filter[] = "input_gst.id=$input_tax";
			}
			
			if($output_tax > 0) {
				$filter[] = "output_gst.id=$output_tax";
			}
			
			if($inclusive_tax !== '') {
				$filter[] = "if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax))='$inclusive_tax'";
			}
			
			if($sku_type !== '' ) {
				$filter[] = "sku.sku_type='$sku_type'";
			}
			
			if($scale_type !== '') {
				$filter[] = "(sku.scale_type = $scale_type or sku_items.scale_type = $scale_type)";
			}
			
			if($active !== '') {
				$filter[] = "sku_items.active=$active";
			}
			
			if($parent_child !== ''){
				$parent_child_select = " , (select count(*) from sku_items si2 where si2.sku_id=sku_items.sku_id) as parent_child_count";
				
				if($parent_child){
					$having[] = "parent_child_count>1";
					
					/*$filter[] = "sku_items.sku_id in (select si.sku_id from sku_items si
					group by si.sku_id
					having count(*) > 1)";*/
				}else{
					$having[] = "parent_child_count=1";
					
					/*$filter[] = "sku_items.sku_id in (select si.sku_id from sku_items si
					group by si.sku_id
					having count(*) = 1)";*/
				}
			}
			
			$filter = ($filter?' and ':'').join(' and ',$filter);
			$having = $having ? "having ".join(' and ', $having) : "";
			
			$show_cat = false;
			if($_REQUEST['show_cat']=='on') {
				$show_cat = true;
                $s = $szs_cat;
			}else {
                $s = $szs;
			}
			$show_additional_desc = false;
			if($config['sku_enable_additional_description'] && $_REQUEST['show_additional_desc']=='on'){
				$show_additional_desc = true;
			}
			
			$items = array();
			$filename = 'sku_export'.time().'.txt';
			$fp = fopen($filename, 'w');
			
			// header
			$header_array = array('SKU ITEM ID','ARMS CODE','ARTNO','MCODE','BARCODE','PRODUCT DESCRIPTION', 'RECEIPT DESCRIPTION','SELLING PRICE','COST PRICE','PRICE TYPE','SKU TYPE');	
			if($show_cat) {
				$header_array = array_merge($header_array, array('DEPARTMENT','THIRD LEVEL OF CATEGORY'));
			}
			$header_array = array_merge($header_array, array('SKU / PRC','UOM CODE','FRACTION','INPUT TAX','OUTPUT TAX','SELLING PRICE INCLUSIVE TAX','SCALE TYPE','ACTIVE','BRAND','VENDOR','STOCK REORDER MIN QTY','STOCK REORDER MAX QTY','STOCK REORDER MOQ QTY','STOCK REORDER NOTIFY USER NAME','PARENT ARMS CODE','PARENT ARTNO','PARENT MCODE'));
			if($config['sku_enable_additional_description'] && $show_additional_desc){
				$header_array = array_merge($header_array, array('ADDITIONAL DESCRIPTION'));
			}
			if($export_type == 'csv'){
				fputcsv($fp, $header_array);
			}
			$got_item = false;
			$user_data = array();
			
			if($load_sku){
				$sql = "select sku_items.id, sku_item_code, artno, mcode, link_code, sku_items.description as 'sku_description', sku_items.po_reorder_qty_min, sku_items.po_reorder_qty_max, sku_items.po_reorder_moq, sku_items.po_reorder_notify_user_id, receipt_description, round(if (p.price is null, selling_price, p.price) * $price_markup, 2) as selling,
						round(if (c.grn_cost is null, cost_price, c.grn_cost) * $cost_markup, 2) as cost, if(p.price is null, sku.default_trade_discount_code, p.trade_discount_code) as disc_code, sku_type, sku_items.active, sku.po_reorder_qty_min as sku_prd_qty_min, sku.po_reorder_qty_max as sku_prd_qty_max, sku.po_reorder_moq as sku_prd_moq, sku.po_reorder_notify_user_id as sku_prd_notify_user_id, sku.po_reorder_qty_by_branch, sku.po_reorder_by_child,
						brand.description as brand, vendor.description as vendor, input_gst.code as input_tax_code, input_gst.rate as input_tax_rate, output_gst.code as output_tax_code, output_gst.rate as output_tax_rate,
						if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax,
						if(sku_items.scale_type = '-1', sku.scale_type, sku_items.scale_type) as scale_type, u.code as uom_code, u.fraction,
						if(sku_items.is_parent=1, sku_items.sku_item_code, (select p_sku.sku_item_code from sku_items p_sku where p_sku.sku_id=sku_items.sku_id and p_sku.is_parent=1)) as parent_arms_code,
						if(sku_items.is_parent=1, sku_items.artno, (select p_sku1.artno from sku_items p_sku1 where p_sku1.sku_id=sku_items.sku_id and p_sku1.is_parent=1)) as parent_artno,
						if(sku_items.is_parent=1, sku_items.mcode, (select p_sku2.mcode from sku_items p_sku2 where p_sku2.sku_id=sku_items.sku_id and p_sku2.is_parent=1)) as parent_mcode,
						cat2.description as p2, cat3.description as p3, lastupdate, sku_items.additional_description
						$parent_child_select
						from sku_items
						left join sku on sku_items.sku_id = sku.id
						left join sku_items_price p on p.sku_item_id = sku_items.id and p.branch_id = $branch_id
						left join sku_items_cost c on c.sku_item_id = sku_items.id and c.branch_id = $branch_id
						left join category on category.id=sku.category_id
						left join category_cache cc on cc.category_id =category.id
						left join category cat2 on cat2.id = cc.p2
						left join category cat3 on cat3.id = cc.p3
						left join uom u on u.id = sku_items.packing_uom_id
						left join brand on brand_id = brand.id
						left join vendor on vendor.id = vendor_id
						left join gst input_gst on input_gst.id=if(if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax)<0,cc.input_tax,if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax))
						left join gst output_gst on output_gst.id=if(if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax)<0,cc.output_tax,if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax))
						where (sku_items.lastupdate > '$lastupdate1' or sku.timestamp > '$lastupdate1') 
						$filter 
						$having
						order by sku_items.lastupdate";
				//if($sessioninfo['id'] == 1){
					//print $sql;exit;
				//}
				$q1 = $con_multi->sql_query($sql) or die(mysql_error());

				if ($con_multi->sql_numrows($q1)>0) {
					$got_item = true;
					
					while($r = $con_multi->sql_fetchassoc($q1)){
						$r['type1'] = 'SKU';
						
						$notify_user_id = 0;
						//get po reorder min, max, moq
						if($r["po_reorder_by_child"] == 1 || $r["po_reorder_qty_by_branch"]){
							$r["po_reorder_qty_by_branch"] = unserialize($r["po_reorder_qty_by_branch"]);
							if($r["po_reorder_qty_by_branch"]['max'][$branch_id]){
								$r['po_reorder_min'] = $r["po_reorder_qty_by_branch"]['min'][$branch_id];
								$r['po_reorder_max'] = $r["po_reorder_qty_by_branch"]['max'][$branch_id];
								$r['po_reorder_moq'] = $r["po_reorder_qty_by_branch"]['moq'][$branch_id];
								$notify_user_id = $r["po_reorder_qty_by_branch"]['notify_user_id'][$branch_id];
							}else{
								$r['po_reorder_min'] = $r["po_reorder_qty_min"];
								$r['po_reorder_max'] = $r["po_reorder_qty_max"];
								$r['po_reorder_moq'] = $r["po_reorder_moq"];
								$notify_user_id = $r["po_reorder_notify_user_id"];
							}
						}else{
							$r['po_reorder_min'] = $r["sku_prd_qty_min"];
							$r['po_reorder_max'] = $r["sku_prd_qty_max"];
							$r['po_reorder_moq'] = $r["sku_prd_moq"];
							$notify_user_id = $r["sku_prd_notify_user_id"];
						}
						
						if($notify_user_id > 0){
							if(!isset($user_data[$notify_user_id])){
								// Select the user
								$q_user = $con_multi->sql_query("select u from user where id=".mi($notify_user_id));
								$tmp = $con_multi->sql_fetchassoc($q_user);
								$con_multi->sql_freeresult($q_user);
								
								// Store into user_data
								$user_data[$notify_user_id] = $tmp;
							}else{
								// Use previous selected user_data
								$tmp = $user_data[$notify_user_id];
							}							
							
							$r['notify_username'] = $tmp['u'];
						}
						write_row($fp, $export_type, $r, $show_cat, $s, $show_additional_desc);
					}
				}
				$con_multi->sql_freeresult($q1);
			}
			

			// export price changes
			$lastupdate2 = $_REQUEST['price_last_update'];
			if (strtotime($lastupdate2)===false) {
				$lastupdate2 = "0";
			}
			
			if(!$already_get_all_items && $lastupdate2 > 0){
				$sql = "select sku_items.id, sku_item_code, artno, mcode, sku_items.po_reorder_qty_min, sku_items.po_reorder_qty_max, sku_items.po_reorder_moq,  sku_items.po_reorder_notify_user_id, link_code, sku_items.description as 'sku_description', receipt_description, round(if (p.price is null, selling_price, p.price) * $price_markup, 2) as selling,
						round(if (c.grn_cost is null, cost_price, c.grn_cost) * $cost_markup, 2) as cost, if(p.price is null, sku.default_trade_discount_code, p.trade_discount_code) as disc_code, sku_type, sku_items.active,sku.po_reorder_qty_min as sku_prd_qty_min, sku.po_reorder_qty_max as sku_prd_qty_max, sku.po_reorder_moq as sku_prd_moq, sku.po_reorder_notify_user_id as sku_prd_notify_user_id, sku.po_reorder_qty_by_branch, sku.po_reorder_by_child,
						brand.description as brand, vendor.description as vendor, input_gst.code as input_tax_code, input_gst.rate as input_tax_rate, output_gst.code as output_tax_code, output_gst.rate as output_tax_rate,
						if(if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)='inherit',cc.inclusive_tax,if(sku_items.inclusive_tax='inherit',sku.mst_inclusive_tax,sku_items.inclusive_tax)) as inclusive_tax,
						if(sku_items.scale_type = '-1', sku.scale_type, sku_items.scale_type) as scale_type, u.code as uom_code, u.fraction,
						if(sku_items.is_parent=1, sku_items.sku_item_code, (select p_sku.sku_item_code from sku_items p_sku where p_sku.sku_id=sku_items.sku_id and p_sku.is_parent=1)) as parent_arms_code,
						if(sku_items.is_parent=1, sku_items.artno, (select p_sku1.artno from sku_items p_sku1 where p_sku1.sku_id=sku_items.sku_id and p_sku1.is_parent=1)) as parent_artno,
						if(sku_items.is_parent=1, sku_items.mcode, (select p_sku2.mcode from sku_items p_sku2 where p_sku2.sku_id=sku_items.sku_id and p_sku2.is_parent=1)) as parent_mcode,
						cat2.description as p2, cat3.description as p3, p.last_update, sku_items.additional_description
						$parent_child_select
						from sku_items
						left join sku on sku_items.sku_id = sku.id
						left join sku_items_price p on p.sku_item_id = sku_items.id and p.branch_id = $branch_id
						left join sku_items_cost c on c.sku_item_id = sku_items.id and c.branch_id = $branch_id
						left join category on category.id=sku.category_id
						left join category_cache cc on cc.category_id =category.id
						left join category cat2 on cat2.id = cc.p2
						left join category cat3 on cat3.id = cc.p3
						left join uom u on u.id = sku_items.packing_uom_id
						left join brand on brand_id = brand.id
						left join vendor on vendor.id = vendor_id
						left join gst input_gst on input_gst.id=if(if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax)<0,cc.input_tax,if(sku_items.input_tax<0,sku.mst_input_tax,sku_items.input_tax))
						left join gst output_gst on output_gst.id=if(if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax)<0,cc.output_tax,if(sku_items.output_tax<0,sku.mst_output_tax,sku_items.output_tax))
						where p.last_update > '$lastupdate2' and p.sku_item_id = sku_items.id
						$filter
						$having
						order by p.last_update";
			
				$q2 = $con_multi->sql_query($sql) or die(mysql_error());
				
				if ($con_multi->sql_numrows($q2)>0) {
					$got_item = true;
					
					while($r = $con_multi->sql_fetchassoc($q2)){
						$r['type1'] = 'PRC';
						//get po reorder min, max, moq
						if($r["po_reorder_by_child"] == 1 || $r["po_reorder_qty_by_branch"]){
							$r["po_reorder_qty_by_branch"] = unserialize($r["po_reorder_qty_by_branch"]);
							if($r["po_reorder_qty_by_branch"]['max'][$branch_id]){
								$r['po_reorder_min'] = $r["po_reorder_qty_by_branch"]['min'][$branch_id];
								$r['po_reorder_max'] = $r["po_reorder_qty_by_branch"]['max'][$branch_id];
								$r['po_reorder_moq'] = $r["po_reorder_qty_by_branch"]['moq'][$branch_id];
								$notify_user_id = $r["po_reorder_qty_by_branch"]['notify_user_id'][$branch_id];
							}else{
								$r['po_reorder_min'] = $r["po_reorder_qty_min"];
								$r['po_reorder_max'] = $r["po_reorder_qty_max"];
								$r['po_reorder_moq'] = $r["po_reorder_moq"];
								$notify_user_id = $r["po_reorder_notify_user_id"];
							}
						}else{
							$r['po_reorder_min'] = $r["sku_prd_qty_min"];
							$r['po_reorder_max'] = $r["sku_prd_qty_max"];
							$r['po_reorder_moq'] = $r["sku_prd_moq"];
							$notify_user_id = $r["sku_prd_notify_user_id"];
						}
						
						if($notify_user_id > 0){
							if(!isset($user_data[$notify_user_id])){
								// Select the user
								$q_user = $con_multi->sql_query("select u from user where id=".mi($notify_user_id));
								$tmp = $con_multi->sql_fetchassoc($q_user);
								$con_multi->sql_freeresult($q_user);
								
								// Store into user_data
								$user_data[$notify_user_id] = $tmp;
							}else{
								// Use previous selected user_data
								$tmp = $user_data[$notify_user_id];
							}							
							
							$r['notify_username'] = $tmp['u'];
						}
						write_row($fp, $export_type, $r, $show_cat, $s, $show_additional_desc);
					}
				}
				$con_multi->sql_freeresult($q2);
			}
			
			
			fclose($fp);
			
			if (!$got_item) {
				print "<script>alert('No new items since last export');</script>";
			}else {
				log_br($sessioninfo['id'], 'SKU_EXPORT', 0, "Export Masterfile Sku.");
				
				$smarty->assign('show_cat', $show_cat);
				$smarty->assign('show_additional_desc', $show_additional_desc);
				$smarty->assign('export_type', $export_type);
				$smarty->assign('items', $items);
				$smarty->assign('s', $s);
				
				if($export_type=='excel') {
					include_once("include/excelwriter.php");
					Header('Content-Type: application/msexcel');
					Header('Content-Disposition: attachment;filename=ARMS_SKU.xls');
					print ExcelWriter::GetHeader();
					
					print "<table><tr>";
					foreach($header_array as $h){
						print "<th>$h</th>";
					}
					print "</tr>";
					print file_get_contents($filename);
					print "</table>";
				}elseif($export_type=='csv') {
					Header('Content-Type: application/msexcel');
					Header('Content-Disposition: attachment;filename=ARMS_SKU.csv');
					print file_get_contents($filename);
				}else {
					header("Content-type: text/plain");
					header('Content-Disposition: attachment;filename=ARMS_SKU.TXT');
					print file_get_contents($filename);
				}
			}
			unlink($filename);
			exit;
			
		default:
			print "<h1>Unhandled Request</h1>";
			print_r($_REQUEST);
			exit;
	}		
}

function write_row($fp, $export_type, $r, $show_cat = false, $pad_settings = array(), $show_additional_desc = false){
	global $config;
	$html = '';
	$arr == array();
	
	$active = (($r[active]=="1")?"YES":"NO");
	switch($r[scale_type]){
		case 0:
			$scale_type = "NO"; break;
		case 1:
			$scale_type = "FIXED PRICE"; break;
		case 2:
			$scale_type = "WEIGHTED"; break;
	}
	$additional_description = '';
	if($show_additional_desc && $config['sku_enable_additional_description']&& $r['additional_description']){
		$additional_description = @join("\n", unserialize($r['additional_description']));
	}
	
	if($export_type=='excel'){
		$html = "<tr>
			<td>$r[id]</td>
			<td>$r[sku_item_code]</td>
			<td>$r[artno]</td>
			<td>$r[mcode]</td>
			<td>$r[link_code]</td>
			<td>$r[sku_description]</td>
			<td>$r[receipt_description]</td>
			<td>$r[selling]</td>
			<td>$r[cost]</td>
			<td>$r[disc_code]</td>
			<td>$r[sku_type]</td>";
			if($show_cat){
				$html .= "<td>$r[p2]</td>
						<td>$r[p3]</td>";
			}
			$html .="<td>$r[type1]</td>
					<td>$r[uom_code]</td>
					<td>$r[fraction]</td>
					<td>$r[input_tax_code] ($r[input_tax_rate]%)</td>
					<td>$r[output_tax_code] ($r[output_tax_rate]%)</td>
					<td>".strtoupper($r['inclusive_tax'])."</td>
					<td>$scale_type</td>
					<td>$active</td>
					<td>$r[brand]</td>
					<td>$r[vendor]</td>
					<td>$r[po_reorder_min]</td>
					<td>$r[po_reorder_max]</td>
					<td>$r[po_reorder_moq]</td>
					<td>$r[notify_username]</td>
					<td>$r[parent_arms_code]</td>
					<td>$r[parent_artno]</td>
					<td>$r[parent_mcode]</td></tr>";
			if($show_additional_desc && $config['sku_enable_additional_description']) $html .= "<td>$additional_description</td>";
	}elseif($export_type=='csv'){
		$arr[] = $r['id'];
		$arr[] = $r['sku_item_code'];
		$arr[] = $r['artno'];
		$arr[] = $r['mcode'];
		$arr[] = $r['link_code'];
		$arr[] = $r['sku_description'];
		$arr[] = $r['receipt_description'];
		$arr[] = $r['selling'];
		$arr[] = $r['cost'];
		$arr[] = $r['disc_code'];
		$arr[] = $r['sku_type'];
		if($show_cat){
			$arr[] = $r['p2'];
			$arr[] = $r['p3'];
		}
		$arr[] = $r['type1'];
		$arr[] = $r['uom_code'];
		$arr[] = $r['fraction'];
		$arr[] = $r['input_tax_code']." ($r[input_tax_rate]%)";
		$arr[] = $r['output_tax_code']." ($r[output_tax_rate]%)";
		$arr[] = strtoupper($r['inclusive_tax']);
		$arr[] = $scale_type;
		$arr[] = $active;
		$arr[] = $r['brand'];
		$arr[] = $r['vendor'];
		$arr[] = $r['po_reorder_min'];
		$arr[] = $r['po_reorder_max'];
		$arr[] = $r['po_reorder_moq'];
		$arr[] = $r['notify_username'];
		$arr[] = $r['parent_arms_code'];
		$arr[] = $r['parent_artno'];
		$arr[] = $r['parent_mcode'];
		if($show_additional_desc && $config['sku_enable_additional_description'])  $arr[] = $additional_description;
	}else{
		$i = 0;
		$html .= str_pad($r['id'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['sku_item_code'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['artno'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['mcode'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['link_code'], $pad_settings[$i++], ' ');
		$html .= mb_str_pad($r['sku_description'], $pad_settings[$i++], ' ', 1);
		$html .= mb_str_pad($r['receipt_description'], $pad_settings[$i++], ' ', 1);
		$html .= str_pad($r['selling'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['cost'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['disc_code'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['sku_type'], $pad_settings[$i++], ' ');
		if($show_cat){
			$html .= str_pad($r['p2'], $pad_settings[$i++], ' ');
			$html .= str_pad($r['p3'], $pad_settings[$i++], ' ');
		}
		$html .= str_pad($r['type1'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['uom_code'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['fraction'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['input_tax_code']."($r[input_tax_rate]%)", $pad_settings[$i++], ' ');
		$html .= str_pad($r['output_tax_code']."($r[output_tax_rate]%)", $pad_settings[$i++], ' ');
		$html .= str_pad(strtoupper($r['inclusive_tax']), $pad_settings[$i++], ' ');
		$html .= str_pad($scale_type, $pad_settings[$i++], ' ');
		$html .= str_pad($active, $pad_settings[$i++], ' ');
		$html .= str_pad($r['brand'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['vendor'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['po_reorder_min'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['po_reorder_max'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['po_reorder_moq'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['notify_username'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['parent_arms_code'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['parent_artno'], $pad_settings[$i++], ' ');
		$html .= str_pad($r['parent_mcode'], $pad_settings[$i++], ' ');
		$html .= "
";
	}
	
	if($export_type =='csv'){
		fputcsv($fp, $arr);
	}else{
		fwrite($fp, $html);
	}
	
}

function mb_str_pad ($input, $pad_length, $pad_string, $pad_style, $encoding="UTF-8") { 
   return str_pad($input, strlen($input)-mb_strlen($input,$encoding)+$pad_length, $pad_string, $pad_style); 
}

// get lastupdate timestamps
if (BRANCH_CODE == 'HQ')
{
	$con_multi->sql_query("select id, code from branch order by sequence, code");
	$branches = array();
	while($r=$con_multi->sql_fetchrow())
	{
		$lastid = @file("sku_export.$r[id].lastid");
		$r['update1'] = $lastid[0];
		$r['update2'] = $lastid[1];
		$branches[] = $r;
	}
	$con_multi->sql_freeresult();
	$smarty->assign("branch", $branches);
}
else
{
	$lastid = @file("sku_export.$sessioninfo[branch_id].lastid");
	$smarty->assign("lastid", $lastid);
}

//get input and output tax code
if($config['enable_gst']){
	$q1 = $con_multi->sql_query("select * from gst where active=1");
	while($r = $con_multi->sql_fetchassoc($q1)){
		if($r['type'] == "purchase"){
			$input_tax_list[$r['id']] = $r;
		}else{
			$output_tax_list[$r['id']] = $r;
		}
	}
	$con_multi->sql_freeresult($q1);
	$smarty->assign("input_tax_list", $input_tax_list);
	$smarty->assign("output_tax_list", $output_tax_list);
}

// Get Department list
$con_multi->sql_query('select * from category where level=2') or die(mysql_error());
$smarty->assign('dept',$con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();
// End of get department list


// get vendor list
$con_multi->sql_query("select * from vendor order by description");
$smarty->assign('vendors',$con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

// get brand list
$con_multi->sql_query("select * from brand order by description");
$smarty->assign('brands',$con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

// get branch group
$con_multi->sql_query("select * from branch_group $where");
$branches_group['header'] = $con_multi->sql_fetchrowset();
$con_multi->sql_freeresult();

$con_multi->sql_query("select bgi.*,branch.code,branch.description from branch_group_items bgi left join branch on bgi.branch_id=branch.id $where2 order by branch.sequence, branch.code");
while($r = $con_multi->sql_fetchrow()) {
	$branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
	$branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
}
$con_multi->sql_freeresult();
$smarty->assign('branches_group',$branches_group);

// get SKU type
$con_multi->sql_query("select distinct sku_type from sku where active and sku_type <> ''");
$smarty->assign("sku_type", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

// get scale type
$scale_type_list = array(-1 => 'Inherit (Follow SKU)', 0 => 'No', 1 => 'Fixed Price', 2 => 'Weighted');
$smarty->assign('scale_type_list', $scale_type_list);

$_REQUEST['all_category'] = 1;
$smarty->display("admin.sku_export.tpl");
?>
