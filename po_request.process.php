<?
/*
Revision History
================
4/25/2007
- cancel with reason function

6/5/2008 3:10:06 PM yinsee
- join sku_items.packing_uom_id instead of sku.uom_id

7/6/2009 11:03 AM Andy
- add unserialize sales trend to show and copy sales trend to PO when generated

10/09/2009 09:34:36 AM edward
- fix error when no item selected

6/24/2011 5:13:42 PM Andy
- Make all branch default sort by sequence, code.

9/19/2011 10:47:24 AM Alex
- add multiple reject
- add log

3/27/2015 1:02 PM Andy
- Enhance to capture GST info when generate PO.

4/22/2015 12:10 PM Andy
- Fix to get latest cost when generate po.

6/11/2015 4:03 PM Justin
- Bug fixed on gst selling price is missing when generate new PO.

6/17/2015 11:13 AM Justin
- Enhanced to remove the extra GST validation.

01/27/2016 13:41 Edwin
- Bug fixed on Cost and Sell price show differently after PO request is approved.

02/02/2016 11:17 Edwin
- Bug fixed on PO cost price error when refresh

02/23/2016 17:35 Edwin
- Bug fixed on selling price incorrect when vendor is not under gst

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

06/22/2016 15:50 Edwin
- Show price before tax in S.S.P when vendor is not under GST

3/24/2017 11:22 AM Andy
- Enhanced to auto recalculate all PO Amount using reCalcatePOUsingOldMethod() when generate or update po.

3/27/2017 3:01 PM Andy
- Change reCalcatePOUsingOldMethod() to reCalcatePOAmt().
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('PO_FROM_REQUEST')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'PO_FROM_REQUEST', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules']) js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");

$branch_id = $sessioninfo['branch_id'];
$user_id = $sessioninfo['id'];

$smarty->assign("PAGE_TITLE", "Create PO from Request");

if (isset($_REQUEST['a']))
{
	switch($_REQUEST['a'])
	{
	    case 'reject'://cancel function

			$f=$_REQUEST;

  		    $delete_id = intval($f['delete_id']);
  		    
  		    $branch_id = intval($f['branch_id'])>0?$f['branch_id']:$branch_id;
  		    
  		    $reject_comment = ms($f['reject_comment']);

  		    $ref_id=$delete_id;
  		      		    
			$vid = $f['vendor_id'];

			if ($delete_id > 0){
				//single
				$del_str="id='$delete_id'";

				$con->sql_query("update po_request_items set approve_by='$user_id',reject_comment=$reject_comment,status='2',approve_time=NOW(),added=added where $del_str and branch_id='$branch_id'");

				$branch=get_branch_code($branch_id);
			}else{
				//multiple
				foreach ($f['sel'] as $item_arr){
					$item_ids[]=implode(",",array_keys($item_arr));
				}
				$delete_id=implode(",",$item_ids);

				//$del_str="id in ($delete_id)";

				$branch=array();
				foreach (array_keys($f['sel'][$vid]) as $sid)
				{
					$req_branch_id = $f['ext_branch_id'][$vid][$sid];

					$con->sql_query("update po_request_items set approve_by='$user_id',reject_comment=$reject_comment,status='2',approve_time=NOW(),added=added where id=$sid and branch_id='$req_branch_id'");

					$branch[]=get_branch_code($req_branch_id);
				}

				$branch=implode(",",$branch);
			}

			/*
			$con->sql_query("update po_request_items set approve_by='$user_id',reject_comment=$reject_comment,status='2',approve_time=NOW(),added=added where $del_str and branch_id='$branch_id'");
	  	    */
			//add log
			log_br($user_id, 'PO From Request', $ref_id, "Reject items in Branch ".$branch.", ID #($delete_id), Comment=".$reject_comment);
           
	        break;
    
		case 'show_items':
			$dept_id = intval($_REQUEST['department_id']);
			get_request_items("category.department_id = $dept_id");
			$smarty->display('po_request.process.items.tpl');
			exit;
		
		case 'create_po':
			$po = generate_po();
			header("Location: $_SERVER[PHP_SELF]?t=complete&po=".join(',',$po)."&department_id=".mi($_REQUEST['department_id']));
			exit;
			
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

// show department option
if ($sessioninfo['level'] < 9999)
{
	if (!$sessioninfo['departments'])
		$depts = "id in (0)";
	else
		$depts = "id in (" . join(",", array_keys($sessioninfo['departments'])) . ")";
}
else
{
	$depts = 1;
}
$con->sql_query("select id, description from category where active and level = 2 and $depts order by description");
$smarty->assign("dept", $con->sql_fetchrowset());

$con->sql_query("select id, code from branch order by sequence,code");
$smarty->assign("branches", $con->sql_fetchrowset());

$smarty->display("po_request.process.tpl");
//get_request_items();

function get_request_items($filter)
{
	global $con, $smarty, $branch_id, $sessioninfo;
	
	// HQ allow to create po for all requests
	if (BRANCH_CODE!='HQ') 
		$filter .= " and po_request_items.branch_id = $branch_id";
	elseif ($_REQUEST['branch_id']>0)
		$filter .= " and po_request_items.branch_id = ".mi($_REQUEST['branch_id']);
	
	$r1 = $con->sql_query(	"select po_request_items.*, sku_items.artno, po_request_items.sell as selling_price, po_request_items.cost as cost_price, sku_items.mcode, sku_items.sku_item_code, sku_items.description as sku, user.u, branch.code as branch, puom.code as packing_uom_code, uom.fraction, uom.code as uom_code
							from po_request_items
							left join sku_items on po_request_items.sku_item_id = sku_items.id
							left join sku on sku_items.sku_id = sku.id
							left join category on sku.category_id = category.id
							left join user on po_request_items.user_id = user.id
							left join branch on po_request_items.branch_id = branch.id
							left join uom puom on puom.id=sku_items.packing_uom_id
							left join uom on uom.id=po_request_items.uom_id
							where po_request_items.active=1 and po_request_items.status=1 and $filter");

	$by_vendor = array();
	while($r = $con->sql_fetchassoc($r1))
	{
		//print "<li> item = $r[sku_item_id] $r[sku] ";
		// find last PO from different vendors
		$r2 = $con->sql_query(	"select po_items.order_price, po_items.order_uom_id, po_items.order_uom_fraction, po_items.selling_price, po.added as po_added, po.vendor_id, po.po_no, po.id as po_id, vendor.description as vendor, uom.code as uom_code,uom.fraction
								from po_items
								left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id
								left join uom on po_items.order_uom_id = uom.id
								left join vendor on vendor.id = po.vendor_id and vendor.active=1
								where po.status=1 and po.approved and (po.branch_id=$branch_id or po.po_branch_id=$branch_id) and po_items.sku_item_id = $r[sku_item_id] and po.po_no is not null order by po.added desc");
	//
		if ($con->sql_numrows($r2)>0)
		{
			while($pi = $con->sql_fetchassoc($r2)){
				//print "<br />=>PO st:$pi[status] $pi[po_id]/$pi[po_no] #cost:$pi[order_price] vd:$pi[vendor] $pi[po_added]";
				
				$item = $r + $pi;

				if (!isset($by_vendor[$item['vendor']]))
				{
					$by_vendor[$item['vendor']]['id'] = $item['vendor_id'];
					$by_vendor[$item['vendor']]['name'] = $item['vendor'];
					$by_vendor[$item['vendor']]['branch_id'] = $branch_id;
				}
				if (isset($vitem[$item['vendor']][$item['id']])) continue;
				//print " <font color=red>picked</font>";
				$vitem[$item['vendor']][$item['id']] = 1;
				$item['sales_trend'] = unserialize($item['sales_trend']);
				
				$by_vendor[$item['vendor']]['items'][$item['id']] = $item;
				$by_vendor[$item['vendor']]['count']++;
				
			}
			$con->sql_freeresult($r2);
		}
		else
		{
			$r3 = $con->sql_query(	"select sku_items.*, sku.vendor_id, vendor.description as vendor, uom.code as uom_code,uom.fraction
									from sku_items
									left join sku on sku.id = sku_items.sku_id
									left join uom on sku_items.packing_uom_id = uom.id
									left join vendor on vendor.id = sku.vendor_id and vendor.active=1
									where sku_items.id = ".mi($r['sku_item_id'])." order by sku_items.added desc");

			while($p2 = $con->sql_fetchassoc($r3))
			{
				$item = $r + $p2;

				if (!isset($by_vendor[$item['vendor']]))
				{
					$by_vendor[$item['vendor']]['id'] = $item['vendor_id'];
					$by_vendor[$item['vendor']]['name'] = $item['vendor'];
				}
				if (isset($vitem[$item['vendor']][$item['id']])) continue;
				//print " <font color=red>picked</font>";
				$item['sales_trend'] = unserialize($item['sales_trend']);
				$vitem[$item['vendor']][$item['id']] = 1;
				$by_vendor[$item['vendor']]['items'][$item['id']] = $item;
				$by_vendor[$item['vendor']]['count']++;

			}
			$con->sql_freeresult($r3);
			/*if (!isset($by_vendor['-']))
			{
				$by_vendor['-']['id'] = 0;
				$by_vendor['-']['name'] = 'No Vendor';
			}
			$by_vendor['-']['items'][$r['id']] = $r;
			$by_vendor['-']['count']++;*/
		}
	}
	$con->sql_freeresult($r1);
	@ksort($by_vendor);
/*	
	
	
	distinct
	vendor.description as vendor, branch.code as branch, branch.report_prefix, vendor.id as vendor_id, po_request_items.*, sku_items.description as sku, sku_item_code, artno, mcode, user.u, category.description as category, category.department_id, po_items.order_price, po_items.selling_price, po_items.order_uom_id, po_items.order_uom_fraction, uom.code as order_uom, po.added as po_added, po.po_branch_id, po.po_no, po.id as po_id 
	from 
	po_request_items 
	left join sku_items on po_request_items.sku_item_id = sku_items.id
	left join sku on sku_items.sku_id = sku.id left join category on sku.category_id = category.id
	left join po_items on po_request_items.sku_item_id = po_items.sku_item_id and (po_items.branch_id = $branch_id or po_items.branch_id=1)
	left join po on po_items.po_id = po.id and po_items.branch_id = po.branch_id 
	left join vendor on vendor.id = po.vendor_id and vendor.active=1
	left join user on po_request_items.user_id = user.id
	left join uom on po_items.order_uom_id = uom.id
	left join branch on po_request_items.branch_id = branch.id
	where $filter and po_request_items.active=1
	order by po_request_items.added, po_added asc");

	$by_vendor = array();
	while($r=$con->sql_fetchrow())
	{
		if ($r['po_branch_id']>0)
		{
			print "<li> Skip ".$r['po_branch_id'];
			//print_r($r);
			if ($r['po_branch_id']!=$branch_id) continue;
		}
		if ($r['vendor_id']==0)
		{
			$r['vendor_id']=0;
			$r['vendor']='No Vendor';
		}
		if ($r['order_uom_fraction']==0) $r['order_uom_fraction']=1; 
		if (!isset($by_vendor[$r['vendor']]))
		{
			$by_vendor[$r['vendor']]['id'] = $r['vendor_id'];
			$by_vendor[$r['vendor']]['name'] = $r['vendor'];
		}
		$by_vendor[$r['vendor']]['items'][$r['id']] = $r;
		$by_vendor[$r['vendor']]['count']++;
	}
	ksort($by_vendor);
*/	
	//$smarty->assign("limit", $limit);

	$smarty->assign("by_vendor", $by_vendor);
}

function generate_po()
{
	global $con, $sessioninfo, $smarty, $branch_id, $config, $appCore;
	$counter = 0;
	$vid = $_REQUEST['vendor_id'];
	$deptid = $_REQUEST['department_id'];
	$po_date = date("Y-m-d");
	
	$po_array = array();
	$err = array();
	$f = $_REQUEST;
	//print_r($f);exit;
	
	if (empty($f['sel'][$vid]))
	{
		header("Location: $_SERVER[PHP_SELF]?alert=No item selected");
		exit;
	}

	if($config['enable_gst']){
		$prms = array();
		$prms['vendor_id'] = $vid;
		$prms['date'] = $po_date;
		$is_under_gst = check_gst_status($prms);
		
		if($is_under_gst){
			// check vendor gst
			$vendor_special_gst = get_vendor_special_gst_settings($vid);
		}
		
		$prms = array();
		$prms['branch_id'] = $branch_id;
		$prms['date'] = $po_date;
		$branch_is_under_gst = check_gst_status($prms);
	}
		
	$po_id_list = array();
	
	if (BRANCH_CODE=='HQ')
	{
		// group items by branch
		$branch_array = array();
		foreach (array_keys($f['sel'][$vid]) as $sid)
		{	
			$req_branch_id = $f['ext_branch_id'][$vid][$sid];
			$sku_item_id = $f['sku_item_id'][$vid][$sid];

			// build delivery branch array 
			if (!in_array($req_branch_id, $branch_array)) $branch_array[]=$req_branch_id;

			if (!isset($items[$sku_item_id]))
			{
				$selling_price = $f['selling_price'][$vid][$sid];
				$gst_selling_price = $f['gst_selling_price'][$vid][$sid];
				
				// add items
				$items[$sku_item_id]['po_id'] = $po_id;
				$items[$sku_item_id]['branch_id'] = $branch_id;
				$items[$sku_item_id]['user_id'] = $sessioninfo['id'];
				$items[$sku_item_id]['po_sheet_id'] = 0;
				$items[$sku_item_id]['sku_item_id'] = $f['sku_item_id'][$vid][$sid];
				$items[$sku_item_id]['artno_mcode'] = ($f['artno'][$vid][$sid] ? $f['artno'][$vid][$sid] : $f['mcode'][$vid][$sid]);
				$items[$sku_item_id]['selling_uom_fraction'] = 1;
				$items[$sku_item_id]['order_uom_id'] = $f['order_uom_id'][$vid][$sid];
				$items[$sku_item_id]['order_uom_fraction'] = $f['order_uom_fraction'][$vid][$sid];
				$items[$sku_item_id]['selling_price'] = $selling_price;
				$items[$sku_item_id]['order_price'] = $f['order_price'][$vid][$sid]*$f['order_uom_fraction'][$vid][$sid];
				$items[$sku_item_id]['sales_trend'] = serialize($f['sales_trend'][$vid][$sid]);
				
				if($config['enable_gst']){
					// get gst data
					get_po_items_gst_data($items[$sku_item_id], $sku_item_id, $is_under_gst, $branch_is_under_gst);
				}
			}
			
			$items[$sku_item_id]['qty_loose_allocation'][$req_branch_id] += $f['qty'][$vid][$sid];
			$items[$sku_item_id]['balance'][$req_branch_id] += $f['balance'][$vid][$sid];
			$sql_remove[$sku_item_id][] = "(id=$sid and branch_id=$req_branch_id)";
		}
		//print_r($items);exit;

		// iterate and save
		$deliver_to = sz($branch_array);
		
		$form = array();
		$form['branch_id'] = $branch_id;
		$form['user_id'] = $sessioninfo['id'];
		$form['vendor_id'] = $vid;
		$form['department_id'] = $deptid;
		$form['deliver_to'] = serialize($branch_array);
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['is_request'] = 1;
		$form['po_date'] = $po_date;
		$form['is_under_gst'] = $is_under_gst;
		
		foreach	($items as $k=>$item)
		{	
			if (($config['po_set_max_items'] && $counter % $config['po_set_max_items'] == 0) || $counter==0)
			{
				// create new PO header
				$con->sql_query("insert into po ".mysql_insert_by_field($form));
				$po_id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Create PO from PO Request (ID#$po_id)");
				$po_array[] = $sessioninfo['report_prefix'].sprintf("%05d",$po_id).'(DP)';
				
				$po_id_list[] = $po_id;
			}
			
			$item['po_id'] = $po_id;
			if($item['order_uom_fraction']>1){
				$item['qty_allocation'] = $item['qty_loose_allocation'];
				$item['qty_loose_allocation'] = 0;
			}
			if ($item['qty_allocation']) $item['qty_allocation']=serialize($item['qty_allocation']);
			$item['qty_loose_allocation']=serialize($item['qty_loose_allocation']);
			$item['balance']=serialize($item['balance']);
			$con->sql_query("insert into po_items ".mysql_insert_by_field($item));
			$po_item_id = $con->sql_nextid();
			$counter++;
			
			$con->sql_query("update po_request_items set active=0, po_item_id=$po_item_id, po_branch_id=$branch_id where " . join(" or ", $sql_remove[$k]));
		}
	}
	else
	{
		$form = array();
		$form['po_branch_id'] = $form['branch_id'] = $branch_id;
		$form['user_id'] = $sessioninfo['id'];
		$form['vendor_id'] = $vid;
		$form['department_id'] = $deptid;
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['is_request'] = 1;
		$form['po_date'] = $po_date;
		$form['is_under_gst'] = $is_under_gst;
		
		foreach (array_keys($f['sel'][$vid]) as $sid)
		{
			$sku_item_id = $f['sku_item_id'][$vid][$sid];
			
			if (($config['po_set_max_items'] && $counter % $config['po_set_max_items'] == 0) || $counter==0)
			{
				// create new PO header
				
				$con->sql_query("insert into po ".mysql_insert_by_field($form));
				$po_id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'PURCHASE ORDER', $po_id, "Create PO from PO Request (ID#$po_id)");
				$po_array[] = $sessioninfo['report_prefix'].sprintf("%05d",$po_id).'(DP)';
				
				$po_id_list[] = $po_id;
			}
			
			$req_branch_id = $f['ext_branch_id'][$vid][$sid];
			// add items
			$item['po_id'] = $po_id;
			$item['branch_id'] = $branch_id;
			$item['user_id'] = $sessioninfo['id'];
			$item['po_sheet_id'] = 0;
			$item['sku_item_id'] = $f['sku_item_id'][$vid][$sid];
			$item['artno_mcode'] = ($f['artno'][$vid][$sid] ? $f['artno'][$vid][$sid] : $f['mcode'][$vid][$sid]);
			$item['selling_uom_fraction'] = 1;
			$item['order_uom_id'] = $f['order_uom_id'][$vid][$sid];
			$item['order_uom_fraction'] = $f['order_uom_fraction'][$vid][$sid];
			$item['selling_price'] = $f['selling_price'][$vid][$sid];
			$item['order_price'] = $f['order_price'][$vid][$sid]*$f['order_uom_fraction'][$vid][$sid];
			$item['qty_loose'] = $f['qty'][$vid][$sid];
			$item['balance'] = $f['balance'][$vid][$sid];
			$item['sales_trend'] = serialize($f['sales_trend'][$vid][$sid]);
			
			if($item['order_uom_fraction']>1)
			{
				$item['qty'] = $item['qty_loose'];
				$item['qty_loose'] = 0;
			}
			
			if($config['enable_gst']){
				// get gst data
				get_po_items_gst_data($item, $sku_item_id, $is_under_gst, $branch_is_under_gst);
			}
			$con->sql_query("insert into po_items ".mysql_insert_by_field($item));
			$po_item_id = $con->sql_nextid();
			$counter++;
			$con->sql_query("update po_request_items set active=0, po_item_id=$po_item_id, po_branch_id=$branch_id where id=$sid and branch_id=$req_branch_id");
		}
	}
	
	if($po_id_list){
		foreach($po_id_list as $po_id){
			// recalculate po amount
			//$appCore->poManager->reCalcatePOUsingOldMethod($branch_id, $po_id);
			$appCore->poManager->reCalcatePOAmt($branch_id, $po_id);
		}
	}
	
	//$smarty->assign("err", $err);
	//$smarty->assign("po", $po_array);
	return $po_array;
}

function get_po_items_gst_data(&$item, $sku_item_id, $is_under_gst = 0, $branch_is_under_gst = 0){
	global $con, $config;
	
	$selling_price = $item['selling_price'];
		
	// get sku inclusive tax
	$is_sku_inclusive = get_sku_gst("inclusive_tax", $sku_item_id);
		
	// get sku original output gst
	$output_gst = get_sku_gst("output_tax", $sku_item_id);
	if($output_gst){
		if($is_sku_inclusive == 'yes'){
			// is inclusive tax
			// find the selling price before tax
			$gst_amt = round($selling_price / ($output_gst['rate']+100) * $output_gst['rate'], 2);
			$price_included_gst = $selling_price;
			$before_tax_price = $price_included_gst - $gst_amt;
		}else{
			// is exclusive tax
			$before_tax_price = $selling_price;
			$gst_amt = round($before_tax_price * $output_gst['rate'] / 100, 2);
			$price_included_gst = $before_tax_price + $gst_amt;
		}
		
		// selling price is always before tax
		$item['selling_price'] = $branch_is_under_gst ? $before_tax_price : $selling_price;
		// this is selling price included tax
		$item['gst_selling_price'] = $price_included_gst;
		
		if($branch_is_under_gst){
			$item['selling_gst_id'] = $output_gst['id'];
			$item['selling_gst_code'] = $output_gst['code'];
			$item['selling_gst_rate'] = $output_gst['rate'];
		}
	}
	
	// got gst
	if($is_under_gst){
		if($vendor_special_gst){
			$item['cost_gst_id'] = $vendor_special_gst['id'];
			$item['cost_gst_code'] = $vendor_special_gst['code'];
			$item['cost_gst_rate'] = $vendor_special_gst['rate'];
		}else{
			$input_gst = get_sku_gst("input_tax", $sku_item_id);
			if($input_gst){
				$item['cost_gst_id'] = $input_gst['id'];
				$item['cost_gst_code'] = $input_gst['code'];
				$item['cost_gst_rate'] = $input_gst['rate'];
			}
		}
	}
}
?>
