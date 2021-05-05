<?
/*
7/25/2007 2:29:58 PM - yinsee
- CODE is optional

6/24/2011 4:44:09 PM Andy
- Make all branch default sort by sequence, code.

12/13/2011 11:25:50 AM Andy
- Add when update brand discount table, system will also update all related SKU cost price.

5/2/2012 9:29:40 AM Andy
- Add can filter by "All".
- Add can export to CSV.

8/2/2012 10:32 AM Andy
- Add when update brand trade discount table system will auto keep a history.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

2/5/2014 11:39 AM Fithri
- add more / missing details in user log
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");
$maintenance->check(143);

$smarty->assign("PAGE_TITLE", "Brand Master File");

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
	    case 'ajax_reload_table':
	        load_table();
	        exit;
	        
		// load trade discount table
		case 'load_td':
		    $did = intval($_REQUEST['department_id']);
		    $con->sql_query("select branch_id, department_id, skutype_code, rate from brand_commission where brand_id = $id and department_id = $did");
		    $form['brand_id'] = $id;
		    $form['department_id'] = $did;
			$ff = array("brand_id", "department_id");
			while ($r = $con->sql_fetchrow())
			{
				$form['commission['.$r['skutype_code'].']['.$r['branch_id'].']'] = $r['rate'];
				$ff[] = 'commission['.$r['skutype_code'].']['.$r['branch_id'].']';
			}
			print_r($form);
			IRS_fill_form("f_d", $ff, $form, 'tdloaded()');
			exit;

		// save trade discount table
		case 'ad':
		    if (!privilege('MST_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRAND', BRANCH_CODE), "/index.php");

			$sql = '';
		    print_r($_REQUEST);
		    $id = intval($_REQUEST['brand_id']);
		    $did = intval($_REQUEST['department_id']);
			foreach ($_REQUEST['commission'] as $k => $f2)
			{
			    foreach ($f2 as $bid => $v)
			    {
					//if ($sql != '') $sql .= ",";
					//$sql .= "(" . mi($bid) . ", $id, $did, " . ms($k) . ", " . mf($v) . ")";
					
					$upd = array();
					$upd['branch_id'] = $bid;
					$upd['brand_id'] = $id;
					$upd['department_id'] = $did;
					$upd['skutype_code'] = $k;
					$upd['rate'] = mf($v);
					
					regen_sku_brand_commision_cost($upd);	// update sku item cost
					$con->sql_query("replace into brand_commission ".mysql_insert_by_field($upd));
					
					create_brand_commission_history($upd);	// store brand commission history
				}
			}
			//$con->sql_query("replace into brand_commission (branch_id, brand_id, department_id, skutype_code, rate) values $sql");
			log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand update trade discount table for ' . $form['code']);
			print "<script>parent.window.hidediv('ddiv');\nalert('$LANG[MSTVENDOR_TRADE_DISCOUNT_UPDATED]');</script>";
			exit;
			
			
		// new brand
		case 'a':
			if (!privilege('MST_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRAND', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("insert into brand " . mysql_insert_by_field($form, array('code', 'description')));
				$id = $con->sql_nextid();
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand create ' . $form['code']);
				//load_table();
				print "<script>parent.window.hidediv('ndiv');parent.window.reload_table(true);alert('$LANG[MSTBRAND_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
		// edit brand
		case 'e':
			if (!privilege('MST_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRAND', BRANCH_CODE), "/index.php");

			$con->sql_query("select * from brand where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid Brand ID: $id');</script>\n";
				exit;
			}
			$form = $con->sql_fetchrow();
			$ff = array("code", "description");
			IRS_fill_form("f_b", $ff, $form);
			exit;
		case 'v':
			if (!privilege('MST_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRAND', BRANCH_CODE), "/index.php");

			$con->sql_query("update brand set active = ".mb($_REQUEST['v'])." where id = $id");
			$con->sql_query("select code from brand where id = $id");
			$v1 = $con->sql_fetchassoc();
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand activate ' . $v1['code']);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand deactivate ' . $v1['code']);
			//load_table();
			print "<script>parent.window.reload_table(true);</script>";
			exit;
		// update brand
		case 'u':
			if (!privilege('MST_BRAND')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRAND', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				// store basic info
				$con->sql_query("update brand set code = ".ms($form['code']).", description = ".ms($form['description'])." where id = $id");
				if ($con->sql_affectedrows() > 0)
				{
					// code changed
					$changes = "";
					foreach (preg_split("/\|/", $form["changed_fields"]) as $ff)
					{
						// strip array
						$ff = preg_replace("/\[.*\]/", '', $ff);
						if ($ff != "") $uqf[$ff] = 1;
					}
					$changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

					log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand update information ' . $form['code'] . $changes);
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');\nparent.window.reload_table(true);\nalert('$LANG[MSTBRAND_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;
		case 'export_brand':
			export_brand();
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
	}

}


// limit department choices
if (privilege('MST_BRAND'))
	$con->sql_query("select id, description from category where level = 2 order by description");
else
{
	if (!$sessioninfo['departments'])
	    $depts = "(0)";
	else
		$depts = join(",", array_keys($sessioninfo['departments']));
	$con->sql_query("select id, description from category where level = 2 and id in ($depts) order by description");
}
$smarty->assign("department", $con->sql_fetchrowset());
$con->sql_query("select id,code from branch order by sequence,code");
$smarty->assign("branches", $con->sql_fetchrowset());
$con->sql_query("select id,code from trade_discount_type order by code");
$smarty->assign("skutype", $con->sql_fetchrowset());

load_table(true);
$smarty->display("masterfile_brand_index.tpl");


function load_table($sql_only = false)
{
	global $con, $smarty;
	
	/*
	if (isset($_REQUEST['search']))
	{
	    $opt = "where $_REQUEST[search] like ".ms($_REQUEST['value'].'%')." or $_REQUEST[search] like ".ms('% '.$_REQUEST['value'].'%');
	}
	else
	{
		$o = strval($_REQUEST['alphabate']);
		if ($o == '')
		{
			$opt = 'where active=0';
		}
		elseif ($o == 'others')
		{
			$opt = "where description < 'A' or description > 'Zz'";
		}elseif($o == 'all'){
			// all no need filter
		}
		else
		{
			$opt = "where description like " .ms($o.'%');
		}
	}
	*/
	
	$filter = array();
	if ($_REQUEST['desc']) {
		$filter[] = "description like ".ms('%'.$_REQUEST['desc'].'%');
	}
	if (isset($_REQUEST['status']) && $_REQUEST['status'] != '') {
		$filter[] = "active = ".mi($_REQUEST['status']);
	}
	if ($_REQUEST['starts_with']) {
		if ($_REQUEST['starts_with'] == 'others') $filter[] = "description < 'A' or description > 'Zz'";
		else $filter[] = "description like ".ms($_REQUEST['starts_with'].'%');
	}
	$filter_str = $filter ? 'where '.join(' and ', $filter) : '';
	
	$start_at = $_REQUEST['pg'] ? mi($_REQUEST['pg'])-1 : 0;
	$start_at = $start_at*20;
	
	$con->sql_query("select * from brand $filter_str order by description limit $start_at,20");
	$brands = $con->sql_fetchrowset();
	$smarty->assign("brands", $brands);
	
	$con->sql_query("select count(*) as c from brand $filter_str");
	$rows = $bcount = mi($con->sql_fetchfield(0));
	$smarty->assign("bcount", $bcount);
	
	$pagination = '';
	for ($p=1; $rows>0; $p++) {
		$selected = ($p == mi($_REQUEST['pg'])) ? 'selected' : '';
		$pagination .= "<option $selected value=\"$p\">$p</option>";
		$rows -= 20;
	}
	$smarty->assign("total_page", --$p);
	$smarty->assign("pagination", $pagination);
	
	$brand_list = array();
	foreach ($brands as $b) {
		$brand_list[mi($b['id'])] = true;
	}
	$brand_list_filter = $brand_list ? 'where brand_id in ('.join(',',array_keys($brand_list)).')' : '';
	$brand_count = array();
	$con->sql_query("select brand_id, count(brand_id) from sku $brand_list_filter group by brand_id");
	while ($r = $con->sql_fetchrow())
	{
	    $brand_count[$r[0]] = $r[1];
	}
	$smarty->assign("brand_count", $brand_count);
	if (!$sql_only) $smarty->display("masterfile_brand_table.tpl");
}


function validate_data(&$form)
{
	global $LANG, $con, $id;

	$errm = array();

//	if ($form['code'] == '')
//		$errm[] = $LANG['MSTBRAND_CODE_EMPTY'];
	
	$form['code'] = strtoupper($form['code']);
	
	// code is blank, then no check
	if ($form['code']!='')
	{
		// if old code != new code, check new code exists
		$con->sql_query("select * from brand where id <> $id and code = " . ms($form['code']));
		if ($con->sql_numrows() > 0)
		{
			$errm[] = sprintf($LANG['MSTBRAND_CODE_DUPLICATE'], $form['code']);
		}
	}
	
	if ($form['description'] == '')
		$errm[] = $LANG['MSTBRAND_DESCRIPTION_EMPTY'];

	$con->sql_query("select * from brand where id <> $id and description = " . ms($form['description']));
	if ($con->sql_numrows() > 0)
	{
		$errm[] = sprintf($LANG['MSTBRAND_DESCRIPTION_DUPLICATE'], $form['description']);
	}
	
	
	//break commission into array
	foreach ($form as $k=>$v)
	{
		if (preg_match('/^commission_(\S+)$/', $k, $m))
		{
			$form['commission'][$m[1]] = $v;
		}
	}
	print_r($form['commission']);
	return $errm;
}
					
function regen_sku_brand_commision_cost($params){
	global $con, $config;
	
	$bid = mi($params['branch_id']);
	$brand_id = mi($params['brand_id']);
	$dept_id = mi($params['department_id']);
	$skutype_code = trim($params['skutype_code']);
	$rate = mf($params['rate']);
	$force_update = mi($_REQUEST['force_update']);
	
	if(!$force_update){	// not force, check current rate first
		$con->sql_query("select rate 
		from brand_commission 
		where branch_id=$bid and brand_id=$brand_id and department_id=$dept_id and skutype_code=".ms($skutype_code));
		$current_rate = mf($con->sql_fetchfield(0));
		$con->sql_freeresult();
		
		if($rate == $current_rate)	return;	// no need update
	}
	
	$filter = array();
	$filter[] = "sku.trade_discount_type=1 and sku.sku_type='CONSIGN' and sku.default_trade_discount_code=".ms($skutype_code);
	$filter[] = "sku.apply_branch_id=$bid";
	$filter[] = "sku.brand_id=$brand_id";
	$filter[] = "c.department_id=$dept_id";
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select si.id,si.selling_price,si.cost_price
	from sku_items si
	left join sku on sku.id=si.sku_id
	left join category c on c.id=sku.category_id
	$filter";
	$q1 = $con->sql_query($sql);
	
	while($r = $con->sql_fetchassoc($q1)){
		$sid = mi($r['id']);
		
		// get the cost after update
		$latest_cost = round(($r['selling_price']*(100-$rate))/100, $config['global_cost_decimal_points']);
		
		// same cost, no need update
		if(!$force_update && $latest_cost == $r['cost_price'])	continue;	
		
		// update master cost
		$con->sql_query("update sku_items set cost_price=".mf($latest_cost)." where id=$sid");
		
		// update latest cost
		$con->sql_query("update sku_items_cost set changed=1 where sku_item_id=$sid");
	}
	$con->sql_freeresult($q1);
}

function export_brand(){
	global $con;
	$contents = array();
	
	$con->sql_query("select * from brand order by description");
	
	if($con->sql_numrows() > 0){
		$contents[] = "Code,Description\r\n";
	
		while($r=$con->sql_fetchrow()){
			$contents[] = "\"$r[code]\",\"$r[description]\"\r\n";
		}
		$con->sql_freeresult();
	
		$content = join("", $contents);
		header("Content-type: text/plain");
		header('Content-Disposition: attachment;filename=brand_list.CSV');
		print $content;
	}else{
		print "No data found.";
	}
	exit;
}

function create_brand_commission_history($data){
	global $con;
	
	$branch_id = mi($data['branch_id']);
	$brand_id = mi($data['brand_id']);
	$skutype_code = $data['skutype_code'];
	$department_id = mi($data['department_id']);
	$rate = $data['rate'];
	
	if(!$branch_id || !$brand_id || !$skutype_code || !$department_id)	die("Invalid parameter to create brand commission history");
	
	$today = date("Y-m-d");
	
	// check whetehr got data changed before
	$con->sql_query("select * from brand_commission_history where branch_id=$branch_id and brand_id=$brand_id and department_id=$department_id and skutype_code=".ms($skutype_code)." and date_from!=".ms($today)." and date_to='9999-12-31'");
	$tmp = $con->sql_fetchassoc();
	$con->sql_freeresult();
	
	if($tmp){
		$tmp['date_to'] = date("Y-m-d", strtotime("-1 day", strtotime($today)));
		$con->sql_query("replace into brand_commission_history ".mysql_insert_by_field($tmp));
	}
	
	$upd = array();
	$upd['branch_id'] = $branch_id;
	$upd['brand_id'] = $brand_id;
	$upd['skutype_code'] = $skutype_code;
	$upd['department_id'] = $department_id;
	$upd['rate'] = $rate;
	$upd['date_from'] = $today;
	$upd['date_to'] = '9999-12-31';
	$con->sql_query("replace into brand_commission_history ".mysql_insert_by_field($upd));
}
?>